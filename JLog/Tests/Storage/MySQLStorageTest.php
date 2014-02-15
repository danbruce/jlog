<?php

namespace JLog\Tests\Storage;

use JLog\Tests\BaseTest,
    JLog\JLog;

class MySQLStorageTest
    extends \PHPUnit_Extensions_Database_TestCase
{

    private static $_connection;
    private static $_dataSet;

    private function _getMySQLSettings()
    {
        return array(
            'groups' => array(
                array(
                    array(
                        'type' => 'mysql',
                        'host' => 'localhost',
                        'database' => 'jlog_test',
                        'username' => 'root',
                        'password' => 'password',
                        'tablePrefix' => 'testing_'
                    )
                )
            )
        );
    }

    public function getConnection()
    {
        if (isset(self::$_connection)) return self::$_connection;

        $settings = $this->_getMySQLSettings()['groups'][0][0];
        $pdoString  = 'mysql:dbname='.$settings['database'].';';
        $pdoString .= 'host='.$settings['host'];
        $pdo = new \PDO($pdoString, $settings['username'], $settings['password']);
        self::$_connection = $this->createDefaultDBConnection($pdo);
        return self::$_connection;
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        if (isset(self::$_dataSet)) return self::$_dataSet;

        self::$_dataSet = $this->createFlatXMLDataSet('./JLog/Tests/empty_database.xml');
        return self::$_dataSet;
    }

    public function setUp()
    {
        parent::setUp();
        JLog::init($this->_getMySQLSettings());
    }

    public function tearDown()
    {
        parent::tearDown();
        JLog::flush();
    }

    public function baseProvider()
    {
        return BaseTest::baseProvider();
    }

    /**
        @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        JLog::log($input);
        $this->assertTrue(true);
        /*
        $logFile = file_get_contents(self::TEMP_LOG_FILE);
        $jsonString = trim(substr($logFile, strpos($logFile, ']')+1)); // strip off the timestamp
        $message = json_decode($jsonString, true);
        $this->_assertMessageMatches($expected, $message);
        */
    }

    /**
        @expectedException        \JLog\Exception
     */
    public function testInvalidPassword()
    {
        $settings = $this->_getMySQLSettings();
        $settings['groups'][0][0]['password'] = 'invalid password';
        JLog::init($settings);
    }
}