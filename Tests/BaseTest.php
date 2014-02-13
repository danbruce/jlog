<?php

abstract class BaseTest
    extends PHPUnit_Framework_TestCase
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

    protected function _assertMessageMatches($expected, $message, $transactionType)
    {
        $this->assertEquals($transactionType, $message['transaction']);
        $this->assertEquals($expected['level'], $message['level']);
        $this->assertEquals($expected['contents'], $message['contents']);
    }
}