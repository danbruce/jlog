<?php

namespace JLog\Storage;

/**
    @class JLog\Storage\AbstractStorage
    @brief An abstract class with empty functions for simple storage mechanism that don't require
    the complexity of the full JLog\Storage\StorageInterface
 */
abstract class AbstractStorage
    implements StorageInterface
{
    public function setup($settings = null) {}

    public abstract function write($string);

    public function beforeBufferedWrite() {}

    public function afterBufferedWrite() {}

    public function close() {}
}