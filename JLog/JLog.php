<?php
/**
    @file JLog/JLog.php
    @brief Contains the class definition of JLog::JLog.
 */

/**
    @namespace JLog
    @brief The main JLog namespace.
 */
namespace JLog;

/**
    @class JLog::JLog
    @brief The main JLog class (entry point to main logging functions).
 */
class JLog
    implements JLogInterface
{
    // contains a pointer to the current transaction
    private $_currentTransaction;

    // the main instance
    private static $_instance;

    // an array of default settings (for anything that is missing in the provided settings)
    private static $_defaultSettings = array(
        'buffer' => false, // do not buffer messages by default
        'verbosity' => Message::LEVEL_DEBUG, // log all messages at any verbosity
        'groups' => array(
            array(
                array('type' => 'stdout') // use a single stdout logger
            )
        )
    );

    /**
        Initializes the JLog instance.
        @param array $settings An array of settings to be used.
        @throws JLog::Exception Throws an exception if something went wrong.
     */
    public function __construct(array $settings)
    {
        $this->_currentTransaction = new Transaction($settings);
    }

    // logs the actual item to the transaction
    private function _log($item, $level)
    {
        $this->_currentTransaction->log($item, $level);
    }

    // flushes the transaction
    private function _flush()
    {
        $this->_currentTransaction->flush();
    }

    /**
        Initializes the logging system.
        @param string|array $settings An array of settings or a file path to load settings.
        @throws JLog::Exception Throws an exception if something went wrong.
     */
    public static function init($settings = array())
    {
        if (is_array($settings)) {
            // apply the settings from the passed array
            $settings = self::_applySettingsFromArray($settings);
        } else if (is_string($settings)) {
            // find the file by the path and apply the settings from the file's contents
            $settings = self::_applySettingsFromFilePath($settings);
        }
        // setup the new instance
        self::$_instance = new self($settings);
    }

    /** 
        Flushes the logging system and performs any final cleanups on the underlying storage
        mechanisms.
    */
    public static function flush()
    {
        if (!isset(self::$_instance)) {
            throw new Exception('JLog must be initialized with a call to JLog::init()');
        }

        self::$_instance->_flush();
        self::$_instance = null;
    }

    /**
        Writes the first parameter to the log.
        @param mixed $item An object to be logged.
        @param int $level An optional parameter indicating the logging level of the
        the object. Defaults to JLog::Message::LEVEL_WARNING.
        @throws JLog::Exception Throws an exception if something went wrong.
     */
    public static function log($item, $level = Message::LEVEL_WARNING)
    {
        if (!isset(self::$_instance)) {
            throw new Exception('JLog must be initialized with a call to JLog::init()');
        }

        self::$_instance->_log($item, $level);
    }

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_FATAL
        @param mixed $item An object to be logged.
    */
    public static function fatal($item)
    {
        self::log($item, Message::LEVEL_FATAL);
    }

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_ERROR
        @param mixed $item An object to be logged.
    */
    public static function error($item)
    {
        self::log($item, Message::LEVEL_ERROR);
    }

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_WARNING
        @param mixed $item An object to be logged.
    */
    public static function warning($item)
    {
        self::log($item, Message::LEVEL_WARNING);
    }

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_NOTICE
        @param mixed $item An object to be logged.
    */
    public static function notice($item)
    {
        self::log($item, Message::LEVEL_NOTICE);
    }

    /** 
        Logs the passed value with the logging level JLog::Message::LEVEL_DEBUG
        @param mixed $item An object to be logged.
    */
    public static function debug($item)
    {
        self::log($item, Message::LEVEL_DEBUG);
    }

    // applies the settings from an array
    private static function _applySettingsFromArray(array $settings)
    {
        // if no groups are specified, we use the default groups
        // if some groups are specified, we overwrite the defaults
        $groups = isset($settings['groups']) && is_array($settings['groups']) ?
            $settings['groups'] : array();
        // merge the default settings and specified settings
        $toReturn = array_merge_recursive(self::$_defaultSettings, $settings);
        // ensure the groups are non-empty (if they are, use the defaults)
        $toReturn['groups'] = count($groups) ? $groups : self::$_defaultSettings['groups'];
        return $toReturn;
    }

    // applies the settings from a file path
    private static function _applySettingsFromFilePath($filePath)
    {
        $settings = json_decode(file_get_contents($filePath), true);
        // if we have a valid array, use the other helper method to setup the settings
        // otherwise return the defaults
        return is_array($settings) ?
            self::_applySettingsFromArray($settings) : self::$_defaultSettings;
    }
}