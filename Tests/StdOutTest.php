<?php

class StdOutTest
    extends BaseTest
{
    public function setUp()
    {
        parent::setUp();
        JLogger::init('./Tests/settings/StdOut.xml');
    }

    /**
     * @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        ob_start();
        JLogger::log($input);
        $message = json_decode(ob_get_clean(), 1);
        $this->_assertMessageMatches($expected, $message, 'STDOUT');
    }
}