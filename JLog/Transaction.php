<?php
/**
    @file JLog/Transaction.php
    @brief Contains the class definition of JLog::Transaction.
 */

/**
    @namespace JLog
    @brief The main JLog namespace.
 */
namespace JLog;

/**
    @class JLog::Transaction
    @brief A transaction represents a single web request.
    @details All items that are logged within a single request belong to the same transaction. A new
    transaction is created each time JLog::JLog::init() is called.
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
        @param array $settings The settings for this transaction.
        @param StorageFactory|null $storageFactory (optional) An optional storage factory to use.
        An instance of JLog::StorageFactory will be used if not provided.
        @throws Exception Throws an exception if the settings are invalid.
     */
    public function __construct(array $settings, $storageFactory = null)
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

    /**
        Returns the unique transaction ID.
        @retval string The unique transaction ID.
     */
    public function getId()
    {
        if (!isset($this->_id)) {
            // generate a new ID if none is present yet
            $this->_generateNewId();
        }
        return $this->_id;
    }

    /**
        Logs the object $item onto this transaction at the specified $level.
        @param mixed $item The item to be logged.
        @param int $level The logging level.
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

    // constructs the full message array to be logged
    private function _constructFullMessage(Message $message, $level)
    {
        return array(
            'transaction' => $this->_id,
            'level'       => $level,
            'contents'    => trim($message->__toString())
        );
    }

    // performs the actual write to a storage mechanism
    private function _writeStorage($storage, $message)
    {
        $storage->preWrite($this);
        $storage->write($message);
        $storage->postWrite($this);
    }
    
    /**
        Notifies all underlying storage mechanisms to flush their contents and close. 
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