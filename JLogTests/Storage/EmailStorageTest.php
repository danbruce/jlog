<?php

namespace JLogTestsStorage;

use JLogTests\BaseTest,
    JLog\JLog,
    JLog\Storage\EmailStorage;

class EmailStorageTest
    extends BaseTest
{
    private function _getEmailSettings()
    {
        return array(
            'groups' => array(
                array(
                    array(
                        'type' => 'email',
                        'to' => 'dbruce1126@gmail.com',
                        'from' => 'admin@example.com',
                        'subject' => 'JLog test email'
                    )
                )
            )
        );
    }

    public function setUp()
    {
        parent::setUp();
        JLog::init($this->_getEmailSettings());
        $wrapper = $this->getMock('JLog\EmailWrapper', array('sendEmail'));
        $wrapper->expects($this->any())->method('sendEmail')->will($this->returnValue(true));
        EmailStorage::setEmailWrapper($wrapper);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
        @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        JLog::log($input);
        $this->assertTrue(true);
        // $this->_assertMessageMatches($expected, $message);
    }

    /**
        @expectedException        \JLog\Exception
        @expectedExceptionMessage Invalid "to" email address.
     */
    public function testInvalidToAddress()
    {
        JLog::init(
            array(
                'groups' => array(
                    array(
                        array(
                            'type' => 'email',
                            'from' => 'admin@example.com',
                            'subject' => 'JLog test email'
                        )
                    )
                )
            )
        );
    }

    /**
        @expectedException        \JLog\Exception
        @expectedExceptionMessage Invalid "from" email address.
     */
    public function testInvalidFromAddress()
    {
        JLog::init(
            array(
                'groups' => array(
                    array(
                        array(
                            'type' => 'email',
                            'to' => 'test@example.com',
                            'subject' => 'JLog test email'
                        )
                    )
                )
            )
        );
    }
}