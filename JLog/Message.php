<?php
/**
    @file JLog/Message.php
    @brief Implemention of the JLog\Message class.
 */

namespace JLog;

/**
    @class Message
    @brief An individual message to be logged.
    @details Each time an object is logged, we wrap that object inside a
    JLog\Message.
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
        @param mixed $c The item to the logged.
     */
    public function __construct($item)
    {
        $this->_contents = json_encode($item);
    }

    /**
     * Returns a json representation of this message.
     * @return string The JSON representation of this message.
     */
    public function __toString()
    {
        return $this->_contents;
    }
}

?>