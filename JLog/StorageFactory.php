<?php
/**
    @file JLog/StorageFactory.php
    @brief Contains the class definition of JLog::StorageFactory.
 */

/**
    @namespace JLog
    @brief The main JLog namespace.
 */
namespace JLog;

/**
    @class JLog::StorageFactory
    @brief A factory for producing objects 
 */
class StorageFactory
{
    /** The magic string for stdout storage type. */
    const STDOUT_STORAGE = 'stdout';
    /** The magic string for stderr storage type. */
    const STDERR_STORAGE = 'stderr';
    /** The magic string for folder storage type. */
    const FOLDER_STORAGE = 'folder';
    /** The magic string for email storage type. */
    const EMAIL_STORAGE = 'email';
    /** The magic string for MySQL storage type. */
    const MYSQL_STORAGE = 'mysql';
    
    // a map that sends magic strings to class types
    private static $_storageTypeClasses = array(
        self::STDOUT_STORAGE => 'JLog\Storage\StdOutStorage',
        self::STDERR_STORAGE => 'JLog\Storage\StdErrStorage',
        self::FOLDER_STORAGE => 'JLog\Storage\FolderStorage',
        self::EMAIL_STORAGE  => 'JLog\Storage\EmailStorage',
        self::MYSQL_STORAGE  => 'JLog\Storage\MySQLStorage'
    );

    /**
        Returns the appropriate storage object from the magic string.
        @param string $string The magic string for the object.
        @retval JLog::Storage::StorageInterface Returns the appropriate objects that conforms to the
        JLog::Storage::StorageInterface interface.
        @throws Exception Throws an exception if the magic string is invalid.
     */
    public function getStorageFromString($string)
    {
        if (array_key_exists($string, self::$_storageTypeClasses)) {
            return new self::$_storageTypeClasses[$string];
        }
        throw new Exception('Unknown type: "'.$string.'" for storage.');
    }
}