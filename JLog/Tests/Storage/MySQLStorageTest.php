<?php

namespace JLog\Tests\Storage;

use JLog\Tests\BaseTest,
    JLog\JLog,
    JLog\Storage\MySQLStorage;

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
        @return PHPUnit_Extensions_Database_DataSet_IDataSet
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
        $this->assertEquals(0, $this->getConnection()->getRowCount('testing_Transactions'));
        $this->assertEquals(0, $this->getConnection()->getRowCount('testing_Messages'));
        JLog::log($input);
        JLog::log($input);
        $this->assertEquals(1, $this->getConnection()->getRowCount('testing_Transactions'));
        $this->assertEquals(2, $this->getConnection()->getRowCount('testing_Messages'));
    }

    /**
        @expectedException \JLog\Exception
     */
    public function testInvalidPassword()
    {
        $settings = $this->_getMySQLSettings();
        $settings['groups'][0][0]['password'] = 'invalid password';
        JLog::init($settings);
    }

    public function testDuplicateTransactionId()
    {
        $storage = new MySQLStorage;
        $storage->setup($this->_getMySQLSettings()['groups'][0][0]);
        $transactionId = hash('sha256', uniqid());
        $this->assertTrue($storage->isValidTransactionId($transactionId));
        $this->assertTrue($storage->isValidTransactionId($transactionId));
        
        $storage = new MySQLStorage;
        $storage->setup($this->_getMySQLSettings()['groups'][0][0]);
        $this->assertFalse($storage->isValidTransactionId($transactionId));
    }
}