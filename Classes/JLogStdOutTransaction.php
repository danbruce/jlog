<?php

class JLogStdOutTransaction extends JLogStreamTransaction
{
    public function __construct($details)
    {
        try {
            parent::__construct('STDOUT');
        } catch (JLogException $e) {
            throw $e;
        }
    }
}

?>