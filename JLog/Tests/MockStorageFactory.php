<?php

namespace JLog\Tests;

use JLog\StorageFactory;

class MockStorageFactory
    extends StorageFactory
{
    const CONFIG_FAILS_FIRST_TRANSACTION_ID = 1;

    private $_config;

    private $_storageCalls = 0;

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function getStorageFromString($string)
    {
        switch ($this->_config) {
            case self::CONFIG_FAILS_FIRST_TRANSACTION_ID :
                return new MockStorageFailsFirstTransaction;
            default :
                return parent::getStorageFromString($string);
        }
    }
}