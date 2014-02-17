<?php

namespace JLog;

class StorageFactory
{

    const STDOUT_STORAGE = 'stdout';
    const STDERR_STORAGE = 'stderr';
    const FOLDER_STORAGE = 'folder';
    const EMAIL_STORAGE = 'email';
    const MYSQL_STORAGE = 'mysql';
    
    private static $_storageTypeClasses = array(
        self::STDOUT_STORAGE => 'JLog\Storage\StdOutStorage',
        self::STDERR_STORAGE => 'JLog\Storage\StdErrStorage',
        self::FOLDER_STORAGE => 'JLog\Storage\FolderStorage',
        self::EMAIL_STORAGE  => 'JLog\Storage\EmailStorage',
        self::MYSQL_STORAGE  => 'JLog\Storage\MySQLStorage'
    );

    public function getStorageFromString($string)
    {
        if (array_key_exists($string, self::$_storageTypeClasses)) {
            return new self::$_storageTypeClasses[$string];
        }
        throw new Exception('Unknown type: "'.$string.'" for storage.');
    }
}