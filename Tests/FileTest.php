<?php

class FileTest
    extends BaseTest
{    

    const ROOT_FOLDER = './Tests/report/fileTest/';

    public function setUp()
    {
        parent::setUp();
        JLogger::init('./Tests/settings/File.xml');
    }

    public function tearDown()
    {
        parent::tearDown();
        // remove all files in the folder and unlink the folder
        $this->_emptyDir(self::ROOT_FOLDER);
    }

    /**
     * @dataProvider baseProvider
     */
    public function testBasicUsage($input, $expected)
    {
        JLogger::log($input);
        
        $contents = null;
        $logFiles = scandir(self::ROOT_FOLDER);
        foreach ($logFiles as $file) {
            if ($file === '.' || $file === '..') continue;

            $this->assertNull($contents); // ensure this loop only finds one file
            $contents = file_get_contents(self::ROOT_FOLDER.DIRECTORY_SEPARATOR.$file);
        }

        $message = json_decode($contents, true);
        $this->assertNotNull($message);
        $this->_assertMessageMatches($expected, $message, $file);
    }

    private function _emptyDir($dir) {
        $logFiles = scandir($dir);
        foreach ($logFiles as $file) {
            if ($file === '.' || $file === '..') continue;

            unlink($dir.DIRECTORY_SEPARATOR.$file);
        }
        rmdir($dir);
    } 
}