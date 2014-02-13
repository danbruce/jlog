<?php
/**
 * @file Classes/JLogStdErrTransaction.php
 * @brief Implementation of the JLogStdErrTransaction class.
 */

/**
 * @class JLogStdErrTransaction
 * @brief A transaction for logging to the standard error stream.
 */
class JLogStdErrTransaction extends JLogStreamTransaction
{
    /**
     * Constructor for the class.
     * @throws JLogException Throws an exception if something went wrong.
     */
    public function __construct()
    {
        try {
            parent::__construct('STDERR');
        } catch (JLogException $e) {
            throw $e;
        }
    }

    public function write($final = false)
    {
        $logCount = count($this->log);
        while ($this->_writePtr < $logCount) {
            $toWrite = $this->log[$this->_writePtr++]->__toString();
            error_log($toWrite);
        }
    }
}

?>