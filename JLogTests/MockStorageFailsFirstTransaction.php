<?php

namespace JLogTests;

use JLog\Storage\StdoutStorage,
    JLog\Storage\StorageInterface;

class MockStorageFailsFirstTransaction
    extends StdoutStorage
    implements StorageInterface
{
    
    private $_transactionCalls = 0;

    public function isValidTransactionId($id)
    {
        return ++$this->_transactionCalls > 1;
    }
}