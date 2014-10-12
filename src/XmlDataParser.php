<?php

namespace Maximethebault\XmlParser;

/**
 * Class XmlDataParser
 *
 * Parses a XML string
 */
class XmlDataParser
{
    /**
     * The XML parsing configuration to be used
     *
     * @var XmlParserConfig
     */
    private $_xmlParserConfig;
    /**
     * Holds the root element for this parser
     *
     * @var XmlRootElement
     */
    private $_rootObject;

    /**
     * Initializes the parser
     *
     * @param $xmlParserConfig XmlParserConfig the XML parsing configuration to be used
     * @param $rootObject      XmlRootElement the root element that will be filled with the XML structure & data
     */
    public function __construct($xmlParserConfig, $rootObject) {
        $this->_xmlParserConfig = $xmlParserConfig;
        $this->_rootObject = $rootObject;
    }

    /**
     * Parse the given XML data
     *
     * @param $xmlData string the XML data to parse
     *
     * @return XmlRootElement the root element filled with the XML structure & data
     */
    public function parse($xmlData) {
        return $this->_rootObject->parseXml($this->_xmlParserConfig, $xmlData);
    }
}