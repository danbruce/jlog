<?php
/**
 * @file Classes/JLogSettings.php
 * @brief Implemention of the JLogSettings class.
 */

/**
 * @class JLogSettings
 * @brief Holds the global settings parsed from the XML settings file.
 */
class JLogSettings
{
    /** A path to the default settings file. */
    public static $defaultSettingsFile = 'JLogSettings.xml';

    /** A flag to indicate whether we should write to the log immediately or to
        let the logged objects be buffered. */
    public static $WriteImmediately = true;

    /** The various transaction groups we read from the settings file. */
    public static $groups;

    /**
     * Initializes the settings by reading from the specified file.
     * @param string $file The file to be parsed.
     * @return void
     * @throws JLogException throws an exception if something went wrong with
     * the parsing.
     */
    public static function readSettingsFile($file)
    {
        try {
            // get the full path to the file
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

    /*
     ***************************************************
     * I suspect these functions are no longer needed. *
     ***************************************************

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
    */
}

?>