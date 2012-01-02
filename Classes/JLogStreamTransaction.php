<?php

abstract class JLogStreamTransaction extends JLogTransaction
{
    public $stream = null;
    private $_writePtr;

    protected function __construct($streamType)
    {
        try {
            parent::__construct($streamType);
            $this->_writePtr = 0;
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
            throw $e;
        }
    }

    private function _openStream($streamString)
    {
        $this->stream = fopen($streamString, 'w');
        if(false == $this->stream) {
            throw new JLogException(
                'Unable to open stream for writing: '.$streamString
            );
        }
    }

    private function _closeStream()
    {
        fclose($this->stream);
    }

    public function write($final = false)
    {
        $logCount = count($this->log);
        while ($this->_writePtr < $logCount) {
            $toWrite = $this->log[$this->_writePtr]->__toString();
            if (false === fwrite($this->stream, $toWrite, strlen($toWrite))) {
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