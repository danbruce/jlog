<?php

namespace JLogTests;

use JLog\Message;

class MessageTest
    extends BaseTest
{
    /**
       @dataProvider baseProvider
    */
    public function testUsage($input, $expected)
    {
        $message = new Message($input);
        $this->assertEquals($expected['contents'], $message->__toString());

        $recursiveMessage = new Message($message);
        $this->assertEquals($expected['contents'], $recursiveMessage->__toString());

        $testObject = new \stdClass;
        $testObject->property = 'My value';
        $objectMessage = new Message($testObject);
        $this->assertEquals(json_encode($testObject), $objectMessage->__toString());

        $testArray = array(1,2,3,4);
        $objectMessage = new Message($testArray);
        $this->assertEquals(json_encode($testArray), $objectMessage->__toString());
    }
}