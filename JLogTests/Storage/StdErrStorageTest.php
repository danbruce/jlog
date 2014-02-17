<?php

namespace JLogTestsStorage;

use JLogTests\BaseTest,
    JLog\JLog;

class StdErrStorageTest
    extends BaseTest
{
    const TEMP_LOG_FILE = './JLog/Tests/report/stderr.log';

    private function _getStdErrSettings()
    {
        return array(
            'groups' => array(
                array(
                    array('type' => 'stderr')
                )
            )
        );
    }

    public function setUp()
    {
        parent::setUp();
        ini_set('error_log', self::TEMP_LOG_FILE);
        JLog::init($this->_getStdErrSettings());
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
        JLog::log($input);
        $logFile = file_get_contents(self::TEMP_LOG_FILE);
        $jsonString = trim(substr($logFile, strpos($logFile, ']')+1)); // strip off the timestamp
        $message = json_decode($jsonString, true);
        $this->_assertMessageMatches($expected, $message);
    }
}