<?php

class JLogger
{
    private static $_currentTransaction = null;
    private static $_written = false;

    public static function init($options = null)
    {
        try {

            // enforce a singleton on the current transaction
            if (isset(JLogger::$_currentTransaction)) {
                throw new JLogException(
                    'JLogger::init called more than once.'
                );
            }

            JLogger::$_currentTransaction = array();
            foreach (JLogSettings::$groups as $group) {
                $transactionGroup = array();
                foreach ($group as $storage) {
                    if (is_array($storage) && isset($storage['storage'])) {
                        try {
                            switch ($storage['storage']) {
                                case 'stderr' :
                                    array_push(
                                        $transactionGroup,
                                        new JLogStdErrTransaction($storage)
                                    );
                                    break;
                                case 'email' :
                                    array_push(
                                        $transactionGroup,
                                        new JLogEmailTransaction($storage)
                                    );
                                    break;                                                                                   
                                case 'mysql' :                                                                               
                                    array_push(                                                                              
                                        $transactionGroup,                                                                   
                                        new JLogMySQLTransaction($storage)                                                   
                                    );                                                                                       
                                    break;                                                                                   
                                case 'file' :                                                                                
                                    array_push(                                                                              
                                        $transactionGroup,                                                                   
                                        new JLogFileTransaction($storage)                                                    
                                    );                                                                                       
                                    break;                                                                                   
                                default :                                                                                    
                                    throw new JLogException(                                                                 
                                        'Unknown JLog storage method '.$storage                                              
                                    );                                                                                       
                            }                                                                                                
                        } catch (JLogException $e) {                                                                         
                            continue;                                                                                        
                        }                                                                                                    
                    }                                                                                                        
                }                                                                                                            
                if (count($transactionGroup)) {                                                                              
                    array_push(
                        JLogger::$_currentTransaction,
                        $transactionGroup
                    );                                            
                }                                                                                                            
            }                                                                                                                
                                                                                                                             
            if (count(JLogger::$_currentTransaction) < 1) {
                throw new JLogException(
                    'No working logging mechanism.'
                );
            }
        } catch (JLogException $e) {
            JLogger::dieWithError($e->getMessage());
        }
    }

    public static function log($ob, $l = JLogMessage::WARNING)
    {
        try {
            foreach (JLogger::$_currentTransaction as $group) {
                // for each transaction group we try to write to 
                // each of the storage methods listed in the group
                try {
                    foreach ($group as $trans) {
                        if (!$trans->functioning) continue;

                        $trans->log($ob, $l);
                    }
                    // if we managed to write to every storage
                    // without catching an exception then we're done
                    return;
                } catch(JLogException $e) {
                    // we caught an error with one of the storage
                    // methods in this group so we jump to the next
                    // group
                    $trans->functioning = false;
                    JLogger::error($e);
                    continue;
                }
            }
        } catch (JLogException $e) {
            JLogger::dieWithError($e->getMessage());
        }
    }

    public static function fatal($ob)
    {
        JLogger::log($ob, JLogMessage::FATAL);
    }

    public static function error($ob)
    {
        JLogger::log($ob, JLogMessage::ERROR);
    }

    public static function warning($ob)
    {
        JLogger::log($ob, JLogMessage::WARNING);
    }

    public static function notice($ob)
    {
        JLogger::log($ob, JLogMessage::NOTICE);
    }

    public static function close()
    {
        try {
            foreach (JLogger::$_currentTransaction as $group) {
                // for each transaction group we try to write to 
                // each of the storage methods listed in the group
                try {
                    foreach ($group as $trans) {
                        if (!$trans->functioning) continue;
                        
                        $trans->write(true);
                    }
                    // if we managed to write to every storage
                    // without catching an exception then we're done
                    return;
                } catch(JLogException $e) {
                    // we caught an error with one of the storage
                    // methods in this group so we jump to the next
                    // group
                    continue;
                }
            }
        } catch (JLogException $e) {
            JLogger::dieWithError($e->getMessage());
        }
    }

    public static function dieWithError($msg)
    {
        die('Caught exception with message: '.$msg."\n");
        exit();
    }
}

?>