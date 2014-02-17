<?php
/**
    @file JLog/JLogInterface.php
    @brief Contains the interface definition of JLog::JLogInterface.
 */

/**
    @namespace JLog
    @brief The main JLog namespace.
 */
namespace JLog;

/**
    @interface JLog::JLogInterface
    @brief The collection of public methods for the main JLog class.
 */
interface JLogInterface
{
    /**
        Initializes the logging system.
        @param string|array $settings An array of settings or a file path to load settings.
        @throws JLog::Exception Throws an exception if something went wrong.
     */
    public static function init($settings = array());

    /** 
        Flushes the logging system and performs any final cleanups on the underlying storage
        mechanisms.
    */
    public static function flush();

    /**
        Writes the first parameter to the log.
        @param mixed $item An object to be logged.
        @param int $level An optional parameter indicating the logging level of the
        the object. Defaults to JLog::Message::LEVEL_WARNING.
        @throws JLog::Exception Throws an exception if something went wrong.
     */
    public static function log($item, $level = JLog::LEVEL_WARNING);

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_FATAL
        @param mixed $item An object to be logged.
    */
    public static function fatal($item);

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_ERROR
        @param mixed $item An object to be logged.
    */
    public static function error($item);

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_WARNING
        @param mixed $item An object to be logged.
    */
    public static function warning($item);

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_NOTICE
        @param mixed $item An object to be logged.
    */
    public static function notice($item);

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_DEBUG
        @param mixed $item An object to be logged.
    */
    public static function debug($item);
}