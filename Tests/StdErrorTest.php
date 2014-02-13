<?php

class StdErrorTest
    extends BaseTest
{
    const TEMP_LOG_FILE = './Tests/report/stderr.log';

    public function setUp()
    {
        parent::setUp();
        ini_set('error_log', self::TEMP_LOG_FILE);
        JLogger::init('./Tests/settings/StdErr.xml');
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink(self::TEMP_LOG_FILE);
    }

    /**
     * @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        JLogger::log($input);
        $logFile = file_get_contents(self::TEMP_LOG_FILE);
        $jsonString = trim(substr($logFile, strpos($logFile, ']')+1)); // strip off the timestamp
        $message = json_decode($jsonString, true);
        $this->_assertMessageMatches($expected, $message, 'STDERR');
    }
}