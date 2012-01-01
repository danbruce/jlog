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
                'rootFolder' => '/home/danny/src/jlog/log'
            )
        ),
        // second group
        array(
            array(
                'storage' => 'email',
                'to' => 'dbruce1126@gmail.com',
                'from' => 'jlog@domain.com'
            )
        ),
        // third group
        array(
            array(
                'storage' => 'stderr'
            )
        )
    );
}

?>