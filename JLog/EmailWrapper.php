<?php
/**
    @file JLog/EmailWrapper.php
    @brief Contains the class definition of JLog::EmailWrapper.
 */

/**
    @namespace JLog
    @brief The main JLog namespace.
 */
namespace JLog;

/**
    @class JLog::EmailWrapper
    @brief An object wrapper around the PHP mail() function.
 */
class EmailWrapper
{
    /**
        @param string $to The "to" email address.
        @param string $from The "from" email address.
        @param string $subject The email subject.
        @param string $body The email body.
        @retval boolean Returns true on success and false on failure.
        @codeCoverageIgnore
     */
    public function sendEmail($to, $from, $subject, $body)
    {
        $headers = sprintf(
            'From: %s'.PHP_EOL.'X-Mailer: PHP/'.phpversion(),
            $from
        );
        return @mail($to, $subject, $body, $headers);
    }
}