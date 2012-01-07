<?php

class JLogSettingsParser
{
    private $_parser;
    private $_groupArray;

    private $_foundRootFolder;
    private $_currentTag;
    private $_currentGroup;
    private $_currentStorage;
    private $_currentAttribute;

    public function __construct()
    {
        $this->_groupArray = array();
        $this->_foundRootFolder = false;
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

    public function getGroups()
    {
        return $this->_groupArray;
    }

    public function parse($data)
    {
        xml_parse($this->_parser, $data);
    }

    public function beginElement($parser, $tag, $attributes)
    {
        $tag = strtolower(trim($tag));

        if (0 == strcmp($tag,'settings')) {
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