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
                'to' => 'dbruce1126@gmail.com',
                'from' => 'rnd@mediamiser.com',
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