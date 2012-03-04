<?php
/**
 * @file Classes/JLogSettingsParser.php
 * @brief Implemention of the JLogSettingsParser class.
 */

/**
 * @class JLogSettingsParser
 * @brief An XML parser for reading in JLogSettings.
 */
class JLogSettingsParser
{
    // the PHP xml_parser 
    private $_parser;
    // the current array of groups
    private $_groupArray;

    // various tags used within the xml parsing
    private $_foundRootFolder;
    private $_currentTag;
    private $_currentGroup;
    private $_currentStorage;
    private $_currentAttribute;

    /**
     * Constructor for this class. This class should only be used by the
     * JLogSettings class for reading in the XML settings.
     */
    public function __construct()
    {
        $this->_groupArray = array();
        $this->_foundRootFolder = false;
        // setup the xml parser
        $this->_parser = xml_parser_create();
        xml_set_object($this->_parser, $this);
        xml_set_element_handler(
            $this->_parser,
            'beginElement',
            'endElement'
        );
        xml_set_character_data_handler(
            $this->_parser,
            'characters'
        );
    }

    /**
     * Returns the list of transaction groups that were parsed from the XML
     * @return array The array of transaction groups.
     */
    public function getGroups()
    {
        return $this->_groupArray;
    }

    /** 
     * Begins parsing the data.
     * @param string $data The XML data to be parsed.
     * @return void
     */
    public function parse($data)
    {
        if (0 == xml_parse($this->_parser, $data)) {
            die('Error parsing XML: '.
                xml_error_string(
                    xml_get_error_code($this->_parser)
                )
            );
        }
    }

    /**
     * Callback when an XML tag is opened. Should only be called by PHP.
     */
    public function beginElement($parser, $tag, $attributes)
    {
        $tag = strtolower(trim($tag));

        if (0 == strcmp($tag, 'settings')) {
            if(true === $this->_foundRootFolder) {
                throw new JLogException(
                    'Found a second settings tag.'
                );
            } else {
                $this->_foundRootFolder = true;
                return;
            }
        } else if (false === $this->_foundRootFolder) {
            throw new JLogException(
                'Missing root tag settings.'
            );
        }

        $this->_currentTag = $tag;

        switch ($tag) {
            case 'group' :
                $this->_currentGroup = array();
                return;
            case 'storage' :
                $this->_currentStorage = array(
                    'storage' => $attributes['TYPE']
                );
                return;
            case 'attribute' :
                $this->_currentAttribute = array();
                return;
            case 'name' :
            case 'value' :
                return;
            default :
                throw new JLogException(
                    'Invalid tag found: '.$tag
                );
        }
    }

    /**
     * Callback when an XML tag is closed. Should only be called by PHP.
     */
    public function endElement($parser, $tag)
    {
        $tag = strtolower(trim($tag));
        
        switch ($tag) {
            case 'group' :
                array_push($this->_groupArray, $this->_currentGroup);
                $this->_currentGroup = null;
                return;
            case 'storage' :
                array_push($this->_currentGroup, $this->_currentStorage);
                $this->_currentStorage = null;
                return;
            case 'attribute' :
                $this->_currentStorage[$this->_currentAttribute['name']] = 
                    $this->_currentAttribute['value'];
                $this->_currentAttribute = null;
                return;
            case 'name' :
            case 'value' :
                return;
        }
    }

    /**
     * Callback when CDATA is encountered. Should only be called by PHP.
     */
    public function characters($parser, $cdata)
    {
        $cdata = trim($cdata);
        if (strlen($cdata) < 1) return;

        switch ($this->_currentTag) {
            case 'name' :
            case 'value' :
                $this->_currentAttribute[$this->_currentTag] = $cdata;
                return;
        }
    }
}