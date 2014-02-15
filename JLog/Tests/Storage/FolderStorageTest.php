<?php

namespace JLog\Tests\Storage;

use JLog\Tests\BaseTest,
    JLog\JLog;

class FolderStorageTest
    extends BaseTest
{    
    const ROOT_FOLDER = './JLog/Tests/report/folderTest/';

    private function _getFolderSettings()
    {
        return array(
            'groups' => array(
                array(
                    array('type' => 'folder', 'rootFolder' => self::ROOT_FOLDER)
                )
            )
        );
    }

    public function setUp()
    {
        parent::setUp();
        JLog::init($this->_getFolderSettings());   
    }

    public function tearDown()
    {
        parent::tearDown();
        // remove all files in the folder and unlink the folder
        $this->_emptyDir(self::ROOT_FOLDER);
    }

    /**
        @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        JLog::log($input);
        $contents = null;
        $logFiles = scandir(self::ROOT_FOLDER);
        foreach ($logFiles as $file) {
            if ($file === '.' || $file === '..') continue;

            $this->assertNull($contents); // ensure this loop only finds one file
            $contents = file_get_contents(self::ROOT_FOLDER.DIRECTORY_SEPARATOR.$file);
        }

        $this->assertNotNull($contents);
        $message = json_decode($contents, true);
        $this->assertNotNull($message);
        $this->_assertMessageMatches($expected, $message);
        JLog::log($input);
    }

    private function _emptyDir($dir) {
        $logFiles = scandir($dir);
        foreach ($logFiles as $file) {
            if ($file === '.' || $file === '..') continue;

            unlink($dir.DIRECTORY_SEPARATOR.$file);
        }
        rmdir($dir);
    }

    /**
        @expectedException        \JLog\Exception
        @expectedExceptionMessage Missing rootFolder attribute for folder storage.
     */
    public function testMissingRootFolder()
    {
        JLog::init(
            array(
                'groups' => array(
                    array(
                        array('type' => 'folder')
                    )
                )
            )
        );
    }

    /**
        @expectedException        \JLog\Exception
        @expectedExceptionMessage Unable to create root logging folder at /root/folderLogging.
     */
    public function testNonwriteableLocation()
    {
        JLog::init(
            array(
                'groups' => array(
                    array(
                        array('type' => 'folder', 'rootFolder' => '/root/folderLogging')
                    )
                )
            )
        );
    }
}