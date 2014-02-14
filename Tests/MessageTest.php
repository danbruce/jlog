<?php

namespace Tests;

use JLog\Message;

class MessageTest
    extends BaseTest
{

    private function _getDefaultItem()
    {
        return 'Test message';
    }
    
    public function testConstructor()
    {
        $item = $this->_getDefaultItem();
        $message = new Message($item);
        $this->assertTrue(true);
    }
}