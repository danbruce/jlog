<?php
/**
 * @file Classes/JLogStreamTransaction.php
 * @brief Implementation of the JLogStreamTransaction class.
 */

/**
 * @class JLogStreamTransaction
 * @brief Abstract base class for all stream-based transactions.
 */
abstract class JLogStreamTransaction extends JLogTransaction
{
    // the pointer to the last object we wrote from the log
    protected $_writePtr;

    /**
     * Constructor for the class.
     * @param string $streamType The stream to write out to. The only valid
     * values are "STDOUT" and "STDERR".
     * @throws JLogException Throws an exception if we cannot open the stream or
     * if the $streamType is invalid.
     */
    protected function __construct($streamType)
    {
        try {
            // use the stream type as the transaction ID
            parent::__construct($streamType);
            $this->_writePtr = 0;
        } catch (Exception $e) {
            // catch all exceptions and convert them to JLogException exceptions
// @codeCoverageIgnoreStart
            throw new JLogException($e->getMessage());
        }
// @codeCoverageIgnoreEnd
    }
}

?>