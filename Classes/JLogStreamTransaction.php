<?php
/**
 * @file Classes/JLogStreamTransaction.php
 * @brief Implementation of the JLogStreamTransaction class.
 */

/**
 * @class JLogStreamTransaction
 * @brief Abstract base class for all stream-based transactions.
 */
abstract class JLogStreamTransaction extends JLogTransaction
{
    // the current stream
    private $_stream = null;
    // the pointer to the last object we wrote from the log
    protected $_writePtr;

    /**
     * Constructor for the class.
     * @param string $streamType The stream to write out to. The only valid
     * values are "STDOUT" and "STDERR".
     * @throws JLogException Throws an exception if we cannot open the stream or
     * if the $streamType is invalid.
     */
    protected function __construct($streamType)
    {
        try {
            // use the stream type as the transaction ID
            parent::__construct($streamType);
            $this->_writePtr = 0;
            // open the correct stream for writing
            switch ($streamType) {
                case 'STDOUT' :
                    $this->_openStream('php://stdout');
                    return;
                case 'STDERR' :
                    $this->_openStream('php://stderr');
                    return;
                default :
                    throw new JLogException(
                        'Invalid stream type: '.$streamType
                    );
            }
        } catch (Exception $e) {
            // catch all exceptions and convert them to JLogException exceptions
            throw new JLogException($e->getMessage());
        }
    }

    // opens a stream for writing
    private function _openStream($streamString)
    {
        $this->_stream = fopen($streamString, 'w');
        if(false == $this->_stream) {
            throw new JLogException(
                'Unable to open stream for writing: '.$streamString
            );
        }
    }

    // closes the stream
    private function _closeStream()
    {
        fclose($this->_stream);
    }

    // no doc tags since we inherit this method from the parent class
    // see JLogTransaction::write for the doc tags
    public function write($final = false)
    {
        $logCount = count($this->log);
        while ($this->_writePtr < $logCount) {
            $toWrite = $this->log[$this->_writePtr]->__toString();
            if (false === fwrite($this->_stream, $toWrite, strlen($toWrite))) {
                throw new JLogException(
                    'Failed to write to stream transaction.'
                );
            }
            $this->_writePtr++;
        }
        if ($final) {
            $this->_closeStream();
        }
    }
}

?>