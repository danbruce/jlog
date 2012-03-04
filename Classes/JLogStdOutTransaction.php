<?php
/**
 * @file Classes/JLogStdOutTransaction.php
 * @brief Implementation of the JLogStdOutTransaction class.
 */

/**
 * @class JLogStdOutTransaction
 * @brief A transaction for logging to the standard output stream.
 */
class JLogStdOutTransaction extends JLogStreamTransaction
{
    /**
     * Constructor for the class.
     * @throws JLogException Throws an exception if something went wrong.
     */
    public function __construct()
    {
        try {
            parent::__construct('STDOUT');
        } catch (JLogException $e) {
            throw $e;
        }
    }
}

?>