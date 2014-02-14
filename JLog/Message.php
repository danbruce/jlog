<?php
/**
 * @file JLog/Message.php
 * @brief Implemention of the JLog\Message class.
 */

namespace JLog;

/**
 * @class Message
 * @brief An individual message to be logged.
 * @details Each time an object is logged, we wrap that object inside a
 * JLogMessage. Additional environment information is capture by this class and
 * included with the original object to be logged.
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

    /** The transaction ID associated with this message. */
    public $transaction;
    /** The contents of the message. This variable holds the object that is
     *  passed into the constructor. */
    public $contents;
    /** The error level of the message. */
    public $errorLevel;
    // an array to cache the full internal representation of the message
    // a JSON representation of this array is what we return when we are asked
    // to serialize this message object
    private $_fullMessage;

    /**
     * Constructor for the class.
     * @param JLogTransaction $t The transaction for this message.
     * @param mixed $c The object to the logged.
     * @param int $l The logging level. Defaults to JLogMessage::WARNING
     */
    public function __construct($t, $c, $l = JLogMessage::WARNING)
    {
        $this->transaction = $t->id;
        $this->contents = $c;
        $this->errorLevel = $l;
        $this->_fullMessage = $this->_constructFullMessage();
    }

    // constructs the _fullMessage array from the environment
    // @todo SECRET SAUCE GOES HERE
    private function _constructFullMessage()
    {
        // setup the array that will be returned eventually
        $ret = array(
            'transaction' => $this->transaction,
            'level'       => $this->errorLevel,
            'contents'    => $this->contents,
        );

        // if we are logging an actual instance of an object, let's be a bit
        // more intelligent and actually check if this class has its own
        // __toString() method or if it can be serialized
        if (is_object($ret['contents'])) {
            if (method_exists($ret['contents'], '__toString')) {
                $ret['contents'] = $ret['contents']->__toString();
            } else {
                $ret['contents'] = serialize($ret['contents']);
            }
        }

        return $ret;
    }

    /**
     * Returns a json representation of this message.
     * @return string The JSON representation of this message.
     */
    public function __toString()
    {
        return json_encode($this->_fullMessage);
    }
}

?>