<?php
/**
    @file JLog/Storage/StdErrStorage.php
    @brief Contains the class definition of JLog::Storage::StdErrStorage.
 */

/**
    @namespace JLog::Storage
    @brief A namespace containing all the storage mechanisms that JLog uses.
 */
namespace JLog\Storage;

/**
    @class JLog::Storage::StdErrStorage
    @brief The standard error "storage" mechanism (object wrapper around error_log()).
 */
class StdErrStorage
    extends AbstractStorage
    implements StorageInterface
{
    /**
        Passes the message to the error_log().
        @param string $string The string to be logged.
     */
    public function write($string)
    {
        error_log($string);
    }
}

?>