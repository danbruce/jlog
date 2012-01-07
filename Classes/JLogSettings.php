<?php

class JLogSettings
{
    public static $defaultSettingsFile = '../local/JLogSettings.xml';

    public static $WriteImmediately = true;

    public static $groups = array();

    private static $_currentTag;

    public static function readSettingsFile($file)
    {
        try {
            $file = realpath($file);

            if (false === file_exists($file)) {
                throw new JLogException(
                    'Unable to find settings file.'
                );
            }
            if (false === is_readable($file)) {
                throw new JLogException(
                    'Unable to read settings file.'
                );   
            }

            if (false === ($contents = file_get_contents($file))) {
                throw new JLogException(
                    'Unable to read contents of settings file.'
                );
            }
            $parser = new JLogSettingsParser();
            $parser->parse($contents);
            JLogSettings::$groups = $parser->getGroups();
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function _parseXMLSettings($xml)
    {
        if (false === isset($xml->group)) {
            throw new JLogException(
                'Invalid settings xml.'
            );
        }
        echo '<pre>';
        foreach ($xml->group as $group) {
            $newGroup = array();
            if (false === isset($group->storage)) {
                throw new JLogException(
                    'Invalid settings xml.'
                );
            }
            foreach ($group->storage as $storage) {
                $storage = JLogSettings::_fetchStorageObject($storage);
                array_push($newGroup, $storage);
            }
            array_push(JLogSettings::$groups, $newGroup);
        }
    }

    private static function _fetchStorageObject($storage)
    {
        $newStorage = array(
            'storage' => $storage->attributes()->type
        );
        foreach ($storage->attribute as $attr) {
            var_dump($attr->name); var_dump($attr->value); echo "\n";

            $newStorage[$attr->name] = $attr->value;
        }
        print_r($newStorage); echo "\n\n";
        return $newStorage;
    }

    /*public static $groups = array(
        // first group
        array(
            array(
                'storage' => 'mysql',
                'host' => 'localhost',
                'database' => 'JLog',
                'username' => 'example',
                'password' => 'password',
                'tablePrefix' => 'JLog_',
            ),
            array(
                'storage' => 'file',
                'rootFolder' => '/tmp/jlog'
            ),
            array(
                'storage' => 'email',
                'to' => 'toExample@example.com',
                'from' => 'fromExample@example.com',
                'subject' => 'JLog Message'
            )
        ),
        // final group
        array(
            array(
                'storage' => 'stderr'
            )
        )
    );*/
}

?>