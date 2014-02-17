<?php
/**
    @file JLog/Transaction.php
    @brief Implementation of the JLog\Transaction class.
 */

namespace JLog;

use JLog\Storage\StdOutStorage;

/**
    @class JLogTransaction
    @brief Abstract base class for all transactions.
 */
class Transaction
{
    // a unique transaction id
    private $_id;
    // the transaction settings
    private $_settings;
    // the list of storage groups
    private $_groups;

    /**
        Constructor for the class.
        @param mixed $id The unique identifier for this transaction.
     */
    public function __construct($settings, $storageFactory = null)
    {
        $this->_settings = $settings;
        if (!isset($storageFactory)) {
            $storageFactory = new StorageFactory;
        }
        $this->_groups = $this->_buildGroupsFromSettings($storageFactory);
    }

    // builds the list of storage groups from the settings
    private function _buildGroupsFromSettings(StorageFactory $factory)
    {
        $groups = array();
        foreach ($this->_settings['groups'] as $settingsGroup) {
            $group = array();
            foreach ($settingsGroup as $settingsStorage) {
                if (!isset($settingsStorage['type']) || strlen($settingsStorage['type']) < 1) {
                    throw new Exception('Missing type for storage.');
                }
                $storage = $factory->getStorageFromString(
                    strtolower(trim($settingsStorage['type']))
                );
                $storage->setup($settingsStorage);
                $group[] = $storage;
            }
            $groups[] = $group;
        }
        return $groups;
    }

    // generates a new transaction ID
    private function _generateNewId()
    {
        do {
            $this->_id = hash('sha256', uniqid());
            $idIsValid = true;
            foreach ($this->_groups as $group) {
                foreach ($group as $storage) {
                    if (false === $storage->isValidTransactionId($this->_id)) {
                        $idIsValid = false;
                        break 2;
                    }
                }
            }
        } while (false === $idIsValid);
    }

    public function getId()
    {
        if (!isset($this->_id)) {
            $this->_generateNewId();
        }
        return $this->_id;
    }

    /**
        Logs the object $item onto this transaction at the specified $level.
        @param mixed $item The item to be logged.
        @param int $level The logging level.
        @return void
     */
    public final function log($item, $level)
    {
        if (!isset($this->_id)) {
            $this->_generateNewId();
        }

        $message = new Message($item);
        $fullMessage = json_encode($this->_constructFullMessage($message, $level));
        foreach ($this->_groups as $group) {
            foreach ($group as $storage) {
                $this->_writeStorage($storage, $fullMessage);
            }
        }
    }

    private function _writeStorage($storage, $message)
    {
        $storage->preWrite($this);
        $storage->write($message);
        $storage->postWrite($this);
    }

    // constructs the _fullMessage array from the environment
    // @todo SECRET SAUCE GOES HERE
    private function _constructFullMessage(Message $message, $level)
    {
        return array(
            'transaction' => $this->_id,
            'level'       => $level,
            'contents'    => trim($message->__toString())
        );
    }
    
    /**
        Removes all objects currently in this transaction's log.
        @return void
     */
    public final function flush()
    {
        foreach ($this->_groups as $group) {
            foreach ($group as $storage) {
                $storage->close();
            }
        }
    }
}

?>