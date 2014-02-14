<?php

class MySQLTest
    extends PHPUnit_Extensions_Database_TestCase
{

    private $_db;

    private static $_connection;
    private static $_dataSet;

    public function baseProvider()
    {
        return array(
            array(
                'Test message',
                array(
                    'level' => -20,
                    'contents' => 'Test message'
                )
            ),
            array(
                array('a', 'b', 'c'),
                array(
                    'level' => -20,
                    'contents' => array('a', 'b', 'c')
                )
            )
        );
    }

    public function setUp()
    {
        $this->_db = array(
            'host' => 'localhost',
            'database' => 'jlog_test',
            'username' => 'root',
            'password' => 'password'
        );
        parent::setUp();
        JLogger::init('./Tests/settings/MySQL.xml');
    }

    public function getConnection()
    {
        if (isset(self::$_connection)) return self::$_connection;

        $pdoString  = 'mysql:dbname='.$this->_db['database'].';';
        $pdoString .= 'host='.$this->_db['host'];
        $pdo = new PDO($pdoString, $this->_db['username'], $this->_db['password']);
        self::$_connection = $this->createDefaultDBConnection($pdo);
        return self::$_connection;
    }

    public function getDataSet()
    {
        if (isset(self::$_dataSet)) return self::$_dataSet;

        self::$_dataSet = $this->createFlatXMLDataSet('./Tests/empty_database.xml');
        return self::$_dataSet;
    }

    /**
     * @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        $this->assertEquals(0, $this->getConnection()->getRowCount('testing_Transactions'));
        $this->assertEquals(0, $this->getConnection()->getRowCount('testing_Messages'));
        JLogger::log($input);
        JLogger::log($input);
        JLogger::close();
        $this->assertEquals(1, $this->getConnection()->getRowCount('testing_Transactions'));
        $this->assertEquals(2, $this->getConnection()->getRowCount('testing_Messages'));
    }

    /**
     * @expectedException PDOException
     */
    public function testBrokenConfig()
    {
        JLogger::init('./Tests/settings/InvalidMySQL.xml');
    }
}