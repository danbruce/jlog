<?php

namespace JLog\Tests;

use JLog\Exception,
    JLog\JLog,
    JLog\Transaction,
    JLog\Message;

class JLogTest
    extends \PHPUnit_Framework_TestCase
{
    public function testBasicInit()
    {
        JLog::init();
        JLog::flush();
        $this->assertTrue(true);
    }

    public function testInitFromFile()
    {
        JLog::init('./JLog/Tests/stdout_file_example.json');
        JLog::flush();
        $this->assertTrue(true);
    }

    /**
        @expectedException JLog\Exception
        @expectedExceptionMessage JLog must be initialized with a call to JLog::init()
     */
    public function testDoubleFlush()
    {
        JLog::flush();
    }

    /**
        @expectedException JLog\Exception
        @expectedExceptionMessage JLog must be initialized with a call to JLog::init()
     */
    public function testUnitializedLog()
    {
        JLog::log('should fail!');
    }

    public function testAllLevels()
    {
        $message = 'Test message';
        ob_start();
        JLog::init();
        JLog::fatal($message);
        JLog::error($message);
        JLog::warning($message);
        JLog::notice($message);
        JLog::debug($message);
        $output = ob_get_clean();
        $contents = array_filter(
            array_map('trim', explode(PHP_EOL, $output)),
            'strlen'
        );
        $contentsCount = count($contents);
        $this->assertEquals(5, $contentsCount);

        $levels = array(
            Message::LEVEL_FATAL, Message::LEVEL_ERROR, Message::LEVEL_WARNING,
            Message::LEVEL_NOTICE, Message::LEVEL_DEBUG
        );
        for ($i = 0; $i < $contentsCount; $i++) {
            $content = json_decode($contents[$i], true);
            $this->assertEquals($message, $content['contents']);
            $this->assertEquals($levels[$i], $content['level']);
        }
    }

    /**
        @expectedException JLog\Exception
        @expectedExceptionMessage Missing type for storage.
     */
    public function testMissingType()
    {
        $settings = array(
            'groups' => array(
                array(
                    array('type' => 'stdout'),
                    array()
                )
            )
        );
        JLog::init($settings);
    }

    /**
        @expectedException JLog\Exception
        @expectedExceptionMessage Unknown type: unknown for storage.
     */
    public function testUnknownType()
    {
        $settings = array(
            'groups' => array(
                array(
                    array('type' => 'unknown')
                )
            )
        );
        JLog::init($settings);
    }

    public function testTransactionGetId()
    {
        $settings = array(
            'groups' => array(
                array(
                    array('type' => 'stdout')
                )
            )
        );
        $t = new Transaction($settings);
        $this->assertEquals($t->getId(), $t->getId());
    }
}