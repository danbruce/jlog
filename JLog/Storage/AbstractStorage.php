<?php
/**
    @file JLog/Storage/AbstractStorage.php
    @brief Contains the class definition of JLog::Storage::AbstractStorage.
 */

/**
    @namespace JLog::Storage
    @brief A namespace containing all the storage mechanisms that JLog uses.
 */
namespace JLog\Storage;

use JLog\Transaction;

/**
    @class JLog::Storage::AbstractStorage
    @brief An abstract class with empty functions for simple storage mechanisms that don't require
    the complexity of the full JLog::Storage::StorageInterface
 */
abstract class AbstractStorage
    implements StorageInterface
{
    /**
        Returns whether or not a transaction ID is valid for this storage mechanism.
        @param string $id A new transaction id.
        @retval boolean Returns true if the new transaction is valid for this storage mechanism and
                false otherwise.
     */
    public function isValidTransactionId($id)
    {
        return true;
    }

    /**
        Sets up the storage mechanism for the given settings.
        @param array $settings The settings to use for this storage.
     */
    public function setup($settings) {}

    /**
        Called before writing to the storage mechanism.
        @param Transaction $transaction The transaction of the write.
     */
    public function preWrite(Transaction $transaction) {}

    /**
        Called to perform the actual writing to the storage mechanism.
        @param string $string The string to be logged.
     */
    public abstract function write($string);

    /**
        Called after writing to the storage mechanism.
        @param Transaction $transaction The transaction of the write.
     */
    public function postWrite(Transaction $transaction) {}

    /**
        Called when the logging system is being flushed.
     */
    public function close() {}
}