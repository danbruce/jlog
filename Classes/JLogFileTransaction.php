<?php
/**
 * @file Classes/JLogFileTransaction.php
 * @brief Implemention of the JLogFileTransaction class.
 */

/**
 * @class JLogFileTransaction
 * @brief A transaction for writing the log to flat files.
 */
class JLogFileTransaction extends JLogTransaction
{
    // the folder we plan to write into
    private $_rootFolder = null;
    // the pointer in the log array where we last wrote
    private $_writePtr = 0;
    // the file we are writing to in this transaction
    private $_logFile = null;

    /**
     * Constructor for the class. Takes the $details array as an argument.
     * @param array $details The details of the file to write to.
     * @throws JLogException Throws an exception if we can't write to the file.
     */
    public function __construct($details)
    {
        try {
            // generate a new transaction id and pass it to the parent class
            parent::__construct(
                $this->_generateNewID(
                    $details
                )
            );
        } catch (JLogException $e) {
            throw $e;
        }
    }

    // generates the id and ensures the file can be written to
    private function _generateNewID($details)
    {
        // make sure we have a root folder in the details array
        if (is_array($details) && isset($details['rootFolder'])) {
                $this->_rootFolder = $details['rootFolder'];
        } else {
            throw new JLogException(
                'Root folder not specified for file storage method.'
            );
        }

        // if the root folder doesn't exist, attempt to create it
        // if we fail to create it, we throw an exception since we obviously
        // don't have write permission to that folder
        if (!file_exists($this->_rootFolder)) {
            if (!@mkdir($this->_rootFolder, 0755, true)) {
                throw new JLogException(
                    'Unable to create root logging folder.'
                );
            }
        }

        // loop until we have a unique file name
        do {
            $trans_id = hash('sha256', uniqid('', true));
        } while (file_exists($this->_rootFolder.DIRECTORY_SEPARATOR.$trans_id));

        return $trans_id;
    }

    /**
     * Writes the log to the file. The file has a write lock associated with it
     * until we do our final write.
     * @param bool $final Indicates whether this will be the final write to the
     * file. If true, then we also close the file resource.
     * @return void
     * @throws JLogException Throws an exception if something went wrong.
     */
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
        } catch (JLogException $e) {
            throw $e;
        }
    }

    // opens the log file and locks it for writing
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

    // removes the write lock and closes the file pointer
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