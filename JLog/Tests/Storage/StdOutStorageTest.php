<?php

namespace JLog\Tests\Storage;

use JLog\Tests\BaseTest,
    JLog\JLog;

class StdOutStorageTest
    extends BaseTest
{
    /**
        @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        ob_start();
        JLog::log($input);
        $message = json_decode(ob_get_clean(), 1);
        $this->_assertMessageMatches($expected, $message);
    }
}