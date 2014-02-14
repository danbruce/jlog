<?php

class SocketTest
    extends BaseTest
{    
    private static $_socket;
    public function setUp()
    {
        parent::setUp();
        JLogger::init('./Tests/settings/Socket.xml');
    }

    public function tearDown()
    {
        parent::tearDown();
        JLogger::close();
    }

    /**
     * @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        // JLogger::log($input);
        $this->assertTrue(true);
    }

    /**
     * @expectedException JLogException
     * @expectedExceptionMessage Invalid settings for socket logging.
     */
    public function testBrokenConfig()
    {
        JLogger::init('./Tests/settings/MissingPortSocket.xml');
    }
}