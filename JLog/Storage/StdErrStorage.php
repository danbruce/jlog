<?php

namespace JLog\Storage;

/**
    @class JLog\Storage\StdErrStorage
    @brief The standard error "storage" mechanism (fancy error_log...)
 */
class StdErrStorage
    extends AbstractStorage
    implements StorageInterface
{
    public function write($string)
    {
        error_log($string);
    }
}

?>