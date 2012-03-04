<?php
/**
 * @file Classes/JLogStdErrTransaction.php
 * @brief Implementation of the JLogStdErrTransaction class.
 */

/**
 * @class JLogStdErrTransaction
 * @brief A transaction for logging to the standard error stream.
 */
class JLogStdErrTransaction extends JLogStreamTransaction
{
    /**
     * Constructor for the class.
     * @throws JLogException Throws an exception if something went wrong.
     */
    public function __construct()
    {
        try {
            parent::__construct('STDERR');
        } catch (JLogException $e) {
            throw $e;
        }
    }
}

?>