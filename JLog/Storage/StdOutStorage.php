<?php
/**
    @file JLog/Storage/StdOutStorage.php
    @brief Contains the class definition of JLog::Storage::StdOutStorage.
 */

/**
    @namespace JLog::Storage
    @brief A namespace containing all the storage mechanisms that JLog uses.
 */
namespace JLog\Storage;

/**
    @class JLog::Storage::StdOutStorage
    @brief The standard output "storage" mechanism (object wrapper around echo)
 */
class StdOutStorage
    extends AbstractStorage
    implements StorageInterface
{
    /**
        Echoes the string to stdout (along with a line break).
        @param string $string The string to be logged.
     */
    public function write($string)
    {
        echo $string.PHP_EOL;
    }
}

?>