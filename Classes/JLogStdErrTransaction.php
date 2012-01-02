<?php

class JLogStdErrTransaction extends JLogStreamTransaction
{
    public function __construct($details)
    {
        try {
            parent::__construct('STDERR');
        } catch (JLogException $e) {
            throw $e;
        }
    }
}

?>