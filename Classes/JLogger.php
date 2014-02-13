<?php
/**
 * @file Classes/JLogger.php
 * @brief Implemention of the JLogger class.
 */

/**
 * @class JLogger
 * @brief Main logging class. The main static functions used for logging are in
 * this class.
 */
abstract class JLogger
{
    // singleton reference to the current transaction
    private static $_currentTransaction = null;

    /**
     * Initializes the logging system.
     * @return void
     * @throws JLogException Throws an exception if something went wrong.
     */
    public static function init($settingsFile = false)
    {
        try {
            if (false == $settingsFile) $settingsFile = JLogSettings::$defaultSettingsFile;
            JLogSettings::readSettingsFile($settingsFile);

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
                                case 'stdout' :
                                    array_push(
                                        $transactionGroup,
                                        new JLogStdOutTransaction($storage)
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
                                case 'socket' :
                                    array_push(
                                        $transactionGroup,
                                        new JLogSocketTransaction($storage)
                                    );
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

    /**
     * Writes the first parameter to the log.
     * @param mixed $ob An object to be logged.
     * @param int $l An optional parameter indicating the logging level of the
     * the object. Defaults to JLogMessage::WARNING.
     * @return void
     * @throws JLogException Throws an exception if something went wrong.
     */
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

    /** 
     * Logs the object $ob with the logging level JLogMessage::FATAL
     * @param mixed $ob An object to be logged.
     * @return void
     */
    public static function fatal($ob)
    {
        JLogger::log($ob, JLogMessage::FATAL);
    }

    /** 
     * Logs the object $ob with the logging level JLogMessage::ERROR
     * @param mixed $ob An object to be logged.
     * @return void
     */
    public static function error($ob)
    {
        JLogger::log($ob, JLogMessage::ERROR);
    }

    /** 
     * Logs the object $ob with the logging level JLogMessage::WARNING
     * @param mixed $ob An object to be logged.
     * @return void
     */
    public static function warning($ob)
    {
        JLogger::log($ob, JLogMessage::WARNING);
    }

    /** 
     * Logs the object $ob with the logging level JLogMessage::NOTICE
     * @param mixed $ob An object to be logged.
     * @return void
     */
    public static function notice($ob)
    {
        JLogger::log($ob, JLogMessage::NOTICE);
    }

    /**
     * Clean shutdown of the logging system. Ensures all transactions are fully
     * written to their output sinks.
     * @return void
     * @throws JLogException Throws an exception if something went wrong
     */
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

    /**
     * Convenience method for exiting the script with an error string.
     * @param string $msg The error message
     * @return void
     */
    public static function dieWithError($msg)
    {
        die('Caught exception with message: '.$msg."\n");
    }
}

?>