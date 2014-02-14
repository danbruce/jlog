<?php

namespace Tests;

abstract class BaseTest
    extends \PHPUnit_Framework_TestCase
{
    public function baseProvider()
    {
        return array(
            array(
                'Test message',
                array(
                    'level' => -20,
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

    protected function _assertMessageMatches($expected, $message, $transactionType)
    {
        $this->assertEquals($transactionType, $message['transaction']);
        $this->assertEquals($expected['level'], $message['level']);
        $this->assertEquals($expected['contents'], $message['contents']);
    }
}