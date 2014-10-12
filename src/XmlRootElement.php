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
    public function __construct() {
        parent::__construct(null, null);
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
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, $xmlParserConfig->getCaseFolding());
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
        try {
            if($this->_parsingObject) {
                $this->_parsingObject->tagOpen($parser, $tagName, $attrs);
            }
            else {
                if($tagName == $this->getName()) {
                    $this->_parsingObject = $this;
                }
                else {
                    throw new \Exception('Unexpected tag "' . $tagName . '" at root');
                }
            }
        }
        catch(\Exception $e) {
            throw new Exception\ParseException(xml_get_current_line_number($parser), $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tagData($parser, $data) {
        $this->tagData($parser, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function tagClosed($parser, $tagName) {
        try {
            $this->tagClosed($parser, $tagName);
        }
        catch(\Exception $e) {
            throw new Exception\ParseException(xml_get_current_line_number($parser), $e->getMessage(), 0, $e);
        }
    }
}