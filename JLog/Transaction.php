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
    // a buffer of items to be logged
    private $_log;
    // the transaction settings
    private $_settings;
    // the list of storage groups
    private $_groups;

    private static $_storageTypeClasses = array(
        'stdout' => 'JLog\Storage\StdOutStorage',
        'stderr' => 'JLog\Storage\StdErrStorage',
        'folder' => 'JLog\Storage\FolderStorage',
        'email' => 'JLog\Storage\EmailStorage'
    );

    /**
        Constructor for the class.
        @param mixed $id The unique identifier for this transaction.
     */
    public function __construct($settings)
    {
        $this->_log = array();
        $this->_settings = $settings;
        $this->_groups = $this->_buildGroupsFromSettings();
    }

    // builds the list of storage groups from the settings
    private function _buildGroupsFromSettings()
    {
        $groups = array();
        foreach ($this->_settings['groups'] as $settingsGroup) {
            $group = array();
            foreach ($settingsGroup as $settingsStorage) {
                if (!isset($settingsStorage['type']) || strlen($settingsStorage['type']) < 1) {
                    throw new Exception('Missing type for storage.');
                }
                if (!array_key_exists($settingsStorage['type'], self::$_storageTypeClasses)) {
                    throw new Exception('Unknown type: '.$settingsStorage['type'].' for storage.');
                }
                $storage = new self::$_storageTypeClasses[$settingsStorage['type']];
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
        $this->_id = hash('sha256', uniqid());
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

        if ($this->_settings['buffer']) {
            $this->_log[] = $fullMessage;
            return;
        }

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
                if (count($this->_log)) {
                    $storage->beforeBufferedWrite($this);
                    foreach ($this->_log as $message) {
                        $this->_writeStorage($storage, $message);
                    }
                    $storage->afterBufferedWrite($this);
                }
                $storage->close();
            }
        }
    }
}

?>