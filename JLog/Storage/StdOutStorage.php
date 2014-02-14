<?php

namespace JLog\Storage;

/**
    @class JLog\Storage\StdOutStorage
    @brief The standard output "storage" mechanism (fancy echo...)
 */
class StdOutStorage
    extends AbstractStorage
    implements StorageInterface
{
    public function write($string)
    {
        echo $string.PHP_EOL;
    }
}

?>