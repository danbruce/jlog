<?php
/**
 * @file Classes/JLogStdOutTransaction.php
 * @brief Implementation of the JLogStdOutTransaction class.
 */

/**
 * @class JLogStdOutTransaction
 * @brief A transaction for logging to the standard output stream.
 */
class JLogStdOutTransaction
    extends JLogStreamTransaction
{
    /**
     * Constructor for the class.
     * @throws JLogException Throws an exception if something went wrong.
     */
    public function __construct()
    {
        parent::__construct('STDOUT');
    }

    public function write($final = false)
    {
        $logCount = count($this->log);
        while ($this->_writePtr < $logCount) {
            $toWrite = $this->log[$this->_writePtr++]->__toString();
            echo $toWrite.PHP_EOL;
        }
    }
}

?>