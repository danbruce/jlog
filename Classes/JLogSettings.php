<?php

class JLogSettings
{
    public static $WriteImmediately = true;

    public static $groups = array(
        // first group
        array(
            array(
                'storage' => 'mysql',
                'host' => 'localhost',
                'database' => 'JLog',
                'username' => 'example',
                'password' => 'password',
                'tablePrefix' => 'JLog_',
            ),
            array(
                'storage' => 'file',
                'rootFolder' => '/tmp/jlog'
            ),
            array(
                'storage' => 'email',
                'to' => 'toExample@example.com',
                'from' => 'fromExample@example.com',
                'subject' => 'JLog Message'
            )
        ),
        // final group
        array(
            array(
                'storage' => 'stderr'
            )
        )
    );
}

?>