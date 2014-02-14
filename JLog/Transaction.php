<?php
/**
    @file JLog/Transaction.php
    @brief Implementation of the JLog\Transaction class.
 */


namespace JLog;
/**
    @class JLogTransaction
    @brief Abstract base class for all transactions.
 */
class Transaction
{
    // a unique transaction id
    private $_id;
    // a buffer of items to be logged
    private $_log;

    /**
        Constructor for the class.
        @param mixed $id The unique identifier for this transaction.
     */
    protected function __construct($id)
    {
        $this->_id = $id;
        $this->_log = array();
    }

    /**
        Logs the object $item onto this transaction at the specified $level.
        @param mixed $item The item to be logged.
        @param int $level The logging level.
        @return void
        @todo FINISH THIS
     */
    public final function log($item, $level)
    {
        $message = new Message($item);
        $fullMessage = $this->_constructFullMessage($message, $level);
    }

    // constructs the _fullMessage array from the environment
    // @todo SECRET SAUCE GOES HERE
    private function _constructFullMessage(Message $message, $level)
    {
        return array(
            'transaction' => $this->_id,
            'level'       => $level,
            'contents'    => $message->__toString()
        );
    }
    
    /**
        Removes all objects currently in this transaction's log.
        @return void
        @todo FINISH THIS
     */
    public final function flush()
    {
    
    }
}

?>