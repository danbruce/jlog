<?php
/**
    @file JLog/JLog.php
    @brief Implemention of the JLog main class.
 */

/**
    @namespace JLog
    @brief The main JLog namespace.
 */
namespace JLog;

/**
    @class JLog
    @brief The main JLog class.
    @details This class handles configuration settings and contains the main logging methods to be
    called from the application.
 */
class Jlog
    implements JLogInterface
{
    // contains a pointer to the current transaction
    private $_currentTransaction;

    // the main instance
    private static $_instance;

    private static $_defaultSettings = array(
        'buffering' => false,
        'verbosity' => Message::LEVEL_DEBUG,
        'groups' => array(
            array(
                array('type' => 'stdout')
            )
        )
    );

    /**
        Initializes the JLog instance.
        @throws JLog\Exception Throws an exception if something went wrong.
     */
    public function __construct(array $settings)
    {
        // $this->_currentTransaction = new Transaction($settings);
    }

    private function _logItem(mixed $item, $level)
    {
        $this->_currentTransaction->log($item, $level);
    }

    /**
        Initializes the logging system.
        @return void
        @throws JLog\Exception Throws an exception if something went wrong.
     */
    public static function init($settings = array())
    {
        if (is_array($settings)) {
            $settings = self::_applySettingsFromArray($settings);
        } else if (is_string($settings)) {
            $settings = self::_applySettingsFromFilePath($settings);
        }
        self::$_instance = new self($settings);
    }

    // applies the settings from an array
    private static function _applySettingsFromArray(array $settings)
    {
        return array_merge_recursive(self::$_defaultSettings, $settings);
    }

    // applies the settings from a file path
    private static function _applySettingsFromFilePath(string $filePath)
    {
        $settings = json_decode(file_get_contents($filePath), true);
        return is_array($settings) ? $settings : array();
    }

    /**
        Writes the first parameter to the log.
        @param mixed $item An object to be logged.
        @param int $level An optional parameter indicating the logging level of the
        the object. Defaults to JLog\Message::LEVEL_WARNING.
        @return void
        @throws JLog\Exception Throws an exception if something went wrong.
     */
    public static function log(mixed $item, $level = Message::LEVEL_WARNING)
    {
        if (!isset(self::$_instance)) {
            throw new Exception('JLog must be initialized with a call to JLog::init()');
        }

        self::$_instance->_log($item, $level);
    }

    /** 
        Logs the passed value with the logging level JLog\Message::LEVEL_FATAL
        @param mixed $item An object to be logged.
        @return void
    */
    public static function fatal(mixed $item)
    {
        self::log($item, Message::LEVEL_FATAL);
    }

    /** 
        Logs the passed value with the logging level JLog\Message::LEVEL_ERROR
        @param mixed $item An object to be logged.
        @return void
    */
    public static function error(mixed $item)
    {
        self::log($item, Message::LEVEL_ERROR);
    }

    /** 
        Logs the passed value with the logging level JLog\Message::LEVEL_WARNING
        @param mixed $item An object to be logged.
        @return void
    */
    public static function warning(mixed $item)
    {
        self::log($item, Message::LEVEL_WARNING);
    }

    /** 
        Logs the passed value with the logging level JLog\Message::LEVEL_NOTICE
        @param mixed $item An object to be logged.
        @return void
    */
    public static function notice(mixed $item)
    {
        self::log($item, Message::LEVEL_NOTICE);
    }

    /** 
        Logs the passed value with the logging level JLog\Message::LEVEL_DEBUG
        @param mixed $item An object to be logged.
        @return void
    */
    public static function debug(mixed $item)
    {
        self::log($item, Message::LEVEL_DEBUG);
    }

    /** 
        Flushes the logging system (if buffering is enabled)
        @return void
    */
    public static function flush()
    {

    }
}