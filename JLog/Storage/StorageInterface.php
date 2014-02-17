<?php
/**
    @file JLog/Storage/StorageInterface.php
    @brief Contains the interface definition of JLog::Storage::StorageInterface.
 */

/**
    @namespace JLog::Storage
    @brief A namespace containing all the storage mechanisms that JLog uses.
 */
namespace JLog\Storage;

use JLog\Transaction;

/**
    @interface JLog::Storage::StorageInterface
    @brief All storage mechanisms must implement this interface.
 */
interface StorageInterface
{
    /**
        Returns whether or not a transaction ID is valid for this storage mechanism.
        @param string $id A new transaction id.
        @retval boolean Returns true if the new transaction is valid for this storage mechanism and
                false otherwise.
     */
    public function isValidTransactionId($id);

    /**
        Sets up the storage mechanism for the given settings.
        @param array $settings The settings to use for this storage.
        @throws Exception Throws an exception if the settings are invalid.
     */
    public function setup($settings);

    /**
        Called before writing to the storage mechanism.
        @param Transaction $transaction The transaction of the write.
     */
    public function preWrite(Transaction $transaction);

    /**
        Called to perform the actual writing to the storage mechanism.
        @param string $string The string to be logged.
     */
    public function write($string);

    /**
        Called after writing to the storage mechanism.
        @param Transaction $transaction The transaction of the write.
     */
    public function postWrite(Transaction $transaction);

    /**
        Called when the logging system is being flushed.
     */
    public function close();
}