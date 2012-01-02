<?php

class JLogFileTransaction extends JLogTransaction
{
    private $_rootFolder = null;
    private $_writePtr = 0;
    private $_logFile = null;

    public function __construct($details)
    {
        try {
            parent::__construct(
                $this->_generateNewID(
                    $details
                )
            );
        } catch (JLogException $e) {
            throw $e;
        }
    }

    private function _generateNewID($details)
    {
        if (is_array($details) && isset($details['rootFolder'])) {
                $this->_rootFolder = $details['rootFolder'];
        } else {
            throw new JLogException(
                'Root folder not specified for file storage method.'
            );
        }

        if (!file_exists($this->_rootFolder)) {
            if (!@mkdir($this->_rootFolder, 0755, true)) {
                die(
                    'Unable to create root logging folder '.
                    $this->_rootFolder
                );
            }
        }

        do {
            $trans_id = hash('sha256', uniqid('', true));
        } while (file_exists($this->_rootFolder.DIRECTORY_SEPARATOR.$trans_id));

        return $trans_id;
    }

    public function write($final = false)
    {
        try {
            if (!isset($this->_logFile)) {
                $this->_openAndLockFile(
                    $this->_rootFolder.DIRECTORY_SEPARATOR.$this->id
                );   
            }

            $logCount = count($this->log);
            while ($this->_writePtr < $logCount) {
                $toWrite = $this->log[$this->_writePtr]->__toString();
                $toWrite .= "\n";
                $success = fwrite($this->_logFile, $toWrite, strlen($toWrite));
                if (false === $success) {
                    throw new JLogException(
                        'Unable to write to log file'
                    );
                }
                $this->_writePtr++;
            }

            if ($final) {
                $this->_closeAndUnlockFile();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function _openAndLockFile($file)
    {

        $this->_logFile = fopen($file, 'a');
        if (false === $this->_logFile) {
            throw new JLogException(
                'Unable to open log file for writing.'.
                $file
            );
        }
        $success = flock($this->_logFile, LOCK_EX);
        if (false === $success) {
            throw new JLogException(
                'Unable to lock file for exclusive writing.'.
                $file
            );
        }
    }

    private function _closeAndUnlockFile()
    {
        if (false === flock($this->_logFile, LOCK_UN)) {
            throw new JLogException(
                'Unable to unlock log file.'
            );
        }
        if (false === fclose($this->_logFile)) {
            throw new JLogException(
                'Unable to close log file.'
            );            
        }
    }
}

?>