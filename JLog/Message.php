<?php
/**
    @file JLog/Message.php
    @brief Contains the class definition of JLog::Message.
 */

/**
    @namespace JLog
    @brief The main JLog namespace.
 */
namespace JLog;

/**
    @class JLog::Message
    @brief An individual message to be logged.
 */
class Message
{
    /** A logging level indicating a fatal error has occurred. */
    const LEVEL_FATAL   = 0;
    /** A logging level indicating an error has occurred. */
    const LEVEL_ERROR   = -10;
    /** A logging level indicating a warning has been raised. */
    const LEVEL_WARNING = -20;
    /** A logging level representing a notice. */
    const LEVEL_NOTICE  = -30;
    /** A logging level for debugging purposes. */
    const LEVEL_DEBUG = -40;

    // the contents of the message
    private $_contents;

    /**
        Constructor for the class.
        @param mixed $item The item to the logged.
     */
    public function __construct($item)
    {
        $this->_contents = $item;
    }

    /**
        Returns a json representation of this message.
        @retval string The JSON representation of this message.
     */
    public function __toString()
    {
        // if we are logging an actual instance of an object, let's be a bit
        // more intelligent and actually check if this class has its own
        // __toString() method or if it can be serialized
        if (is_object($this->_contents)) {
            if (method_exists($this->_contents, '__toString')) {
                $this->_contents = $this->_contents->__toString();
            } else {
                $this->_contents = json_encode($this->_contents);
            }
        } else if (is_array($this->_contents)) {
            $this->_contents = json_encode($this->_contents);
        } else if (is_scalar($this->_contents)) {
            $this->_contents = (string)$this->_contents;
        }
        return $this->_contents;
    }
}

?>