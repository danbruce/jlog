<?php

namespace JLog\Tests;

abstract class BaseTest
    extends \PHPUnit_Framework_TestCase
{
    public static function baseProvider()
    {
        return array(
            array(
                'Test message',
                array(
                    'level' => \JLog\Message::LEVEL_WARNING,
                    'contents' => 'Test message'
                )
            )
        );
    }

    public function setUp()
    {
        parent::setUp();
        \JLog\JLog::init();
    }

    public function tearDown()
    {
        parent::tearDown();
        \JLog\JLog::flush();
    }

    protected function _assertMessageMatches($expected, $message)
    {
        $this->assertTrue(isset($message['transaction']) && strlen($message['transaction']));
        $this->assertEquals($expected['level'], $message['level']);
        $this->assertEquals($expected['contents'], $message['contents']);
    }
}