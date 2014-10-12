<?php

namespace Maximethebault\XmlParser;

/**
 * Class XmlRootElement
 *
 * Represents a XML root element.
 *
 * @package Maximethebault\XmlParser
 */
abstract class XmlRootElement extends XmlElement
{
    /**
     * The XML parsing configuration to be used
     *
     * @var XmlParserConfig
     */
    private $_xmlParserConfig;
    /**
     * Whether the tag for the root element has been opened
     *
     * @var bool
     */
    private $_opened;

    public function __construct() {
        parent::__construct(null, null);
        $this->_opened = false;
    }

    /**
     * Parses a XML string into PHP objects
     *
     * @param $xmlParserConfig XmlParserConfig the XML parsing configuration to be used
     * @param $xmlData         string the XML input string
     *
     * @throws Exception\ParseException
     * @return XmlElement the root element that was parsed, which gives access to the whole document thanks to the children accessors
     */
    public function parseXml($xmlParserConfig, $xmlData) {
        $this->_xmlParserConfig = $xmlParserConfig;
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($parser, 'tagOpen', 'tagClosed');
        xml_set_character_data_handler($parser, 'tagData');

        if(!xml_parse($parser, $xmlData)) {
            throw new Exception\ParseException(xml_get_current_line_number($parser), xml_error_string(xml_get_error_code($parser)));
        }

        xml_parser_free($parser);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function tagOpen($parser, $tagName, $attrs) {
        $tagName = $this->convertStrWithCaseStrategy($tagName);
        if($this->_parsingObject) {
            $this->_parsingObject->tagOpen($parser, $tagName, $attrs);
        }
        else {
            if($tagName == $this->getName()) {
                if($this->_opened) {
                    throw new Exception\ParseException(xml_get_current_line_number($parser), 'Root tag "' . $this->getName() . '" is opened twice...');
                }
                $this->_opened = true;
            }
            else {
                parent::tagOpen($parser, $tagName, $attrs);
            }
        }
    }

    /**
     * Converts a string with respect to the configuration
     *
     * @param $str string the input string
     *
     * @return string the output string, converted accordingly
     */
    private function convertStrWithCaseStrategy($str) {
        if($this->_xmlParserConfig->getCaseStrategy() === XmlParserConfig::XML_PARSER_CASE_LOWER) {
            return strtolower($str);
        }
        elseif($this->_xmlParserConfig->getCaseStrategy() === XmlParserConfig::XML_PARSER_CASE_UNTOUCHED) {
            return $str;
        }
        elseif($this->_xmlParserConfig->getCaseStrategy() === XmlParserConfig::XML_PARSER_CASE_UPPER) {
            return strtoupper($str);
        }
    }
}