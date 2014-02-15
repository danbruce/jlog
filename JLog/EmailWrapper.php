<?php

namespace JLog;


class EmailWrapper
{
    /**
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