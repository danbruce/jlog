<?php

namespace JLog\Storage;

use JLog\Transaction;

/**
    @class JLog\Storage\AbstractStorage
    @brief An abstract class with empty functions for simple storage mechanism that don't require
    the complexity of the full JLog\Storage\StorageInterface
 */
abstract class AbstractStorage
    implements StorageInterface
{
    public function setup($settings) {}

    public function preWrite(Transaction $transaction) {}

    public abstract function write($string);

    public function postWrite(Transaction $transaction) {}

    public function beforeBufferedWrite(Transaction $transaction) {}

    public function afterBufferedWrite(Transaction $transaction) {}

    public function close() {}
}