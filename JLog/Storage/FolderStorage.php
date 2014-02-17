<?php
/**
    @file JLog/Storage/FolderStorage.php
    @brief Contains the class definition of JLog::Storage::FolderStorage.
 */

/**
    @namespace JLog::Storage
    @brief A namespace containing all the storage mechanisms that JLog uses.
 */
namespace JLog\Storage;

use JLog\Exception,
    JLog\Transaction;

/**
    @class JLog::Storage::FolderStorage
    @brief Stores log files in a specific folder using one file per transaction
 */
class FolderStorage
    extends AbstractStorage
    implements StorageInterface
{

    // the file pointer to the file
    private $_fp;
    // the path to the root folder
    private $_rootFolder;

    /**
        Returns false if the transaction id produces a file name collision (true otherwise).
        @param string $id A new transaction id.
        @retval boolean Returns false if the transaction id produces a file name collision (true
                otherwise).
     */
    public function isValidTransactionId($id)
    {
        return !file_exists($this->_rootFolder.DIRECTORY_SEPARATOR.$id);
    }

    /**
        Sets up the storage mechanism for the given settings.
        @param array $settings The settings to use for this storage.
        @throws Exception Throws an exception if the settings provide an invalid or missing
                rootFolder.
     */
    public function setup($settings)
    {
        parent::setup($settings);

        if (!isset($settings['rootFolder']) ||
            !is_string($settings['rootFolder']) ||
            strlen($settings['rootFolder']) < 1) {
            throw new Exception('Missing rootFolder attribute for folder storage.');
        }

        // if the root folder doesn't exist, attempt to create it
        // if we fail to create it, we throw an exception since we obviously
        // don't have write permission to that folder
        if (!file_exists($settings['rootFolder'])) {
            if (!@mkdir($settings['rootFolder'], 0755, true)) {
                throw new Exception(
                    'Unable to create root logging folder at '.$settings['rootFolder'].'.'
                );
            }
        }

        $this->_rootFolder = realpath($settings['rootFolder']);
    }

    /**
        Called before writing to the storage mechanism. Opens the file pointer (if necessary) and
        locks the file.
        @param Transaction $transaction The transaction of the write.
     */
    public function preWrite(Transaction $transaction)
    {
        parent::preWrite($transaction);
        // check if the file pointer exists...
        if (!isset($this->_fp)) {
            // and ensure we can open a pointer to the file
            $writeFilePath = $this->_rootFolder.DIRECTORY_SEPARATOR.$transaction->getId();
            $this->_fp = fopen($writeFilePath, 'w');
            if (false === $this->_fp) {
                throw new Exception('Unable to open file for writing: '.$writeFilePath);
            }
        }
        // lock the file for exclusive writing
        flock($this->_fp, LOCK_EX);
    }

    /**
        Performs a simple write to the log file.
        @param string $string The string to be logged.
     */
    public function write($string)
    {
        fwrite($this->_fp, $string.PHP_EOL);
    }

    /**
        Called after writing to the storage mechanism. Unlocks the file pointer.
        @param Transaction $transaction The transaction of the write.
     */
    public function postWrite(Transaction $transaction)
    {
        parent::postWrite($transaction);
        flock($this->_fp, LOCK_UN);
    }

    /**
        Called when the logging system is being flushed. Releases the file lock and closes the 
        pointer to the file.
     */
    public function close()
    {
        parent::close();
        if (isset($this->_fp)) {
            // just in case, unlock the file first
            flock($this->_fp, LOCK_UN);
            fclose($this->_fp);
        }
    }
}

?>