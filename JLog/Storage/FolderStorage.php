<?php

namespace JLog\Storage;

use JLog\Exception,
    JLog\Transaction;

/**
    @class JLog\Storage\FolderStorage
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

    public function setup($settings)
    {
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

    public function preWrite(Transaction $transaction)
    {
        parent::preWrite($transaction);
        if (isset($this->_fp)) {
            return;
        }

        $writeFilePath = $this->_rootFolder.DIRECTORY_SEPARATOR.$transaction->getId();
        $this->_fp = fopen($writeFilePath, 'w');
        if (false === $this->_fp) {
// @codeCoverageIgnoreStart
            throw new Exception('Unable to open file for writing: '.$writeFilePath);
        }
// @codeCoverageIgnoreEnd
    }

    public function write($string)
    {
        flock($this->_fp, LOCK_EX);
        fwrite($this->_fp, $string.PHP_EOL);
        flock($this->_fp, LOCK_UN);
    }

    public function close()
    {
        parent::close();
        if (isset($this->_fp)) {
            fclose($this->_fp);
        }
    }
}

?>