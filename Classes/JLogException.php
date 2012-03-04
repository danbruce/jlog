<?php
/**
 * @file Classes/JLogException.php
 * @brief Implementation of the JLogException class.
 */

/**
 * @class JLogException
 * @extends Exception
 * @brief A subclass of PHP's Exception class.
 */
class JLogException extends Exception
{
    /**
     * Converts this exception to a string by using PHP's standard exception
     * string.
     * @return string This exception in string representation.
     */
    public function __toString()
    {
        return parent::__toString();
    }
}

?>