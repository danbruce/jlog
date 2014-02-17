<?php
/**
    @file JLog/autoload.php
    @brief Contains the JLog namespace autoloader.
    @codeCoverageIgnore
 */
spl_autoload_register(function ($className) {
    $file = __DIR__.'/../'.str_replace('\\', DIRECTORY_SEPARATOR, $className).'.php';

    if (!file_exists($file)) {
        return false;
    } else {
        require_once $file;
        return true;
    }
});