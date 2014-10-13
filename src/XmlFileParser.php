<?php

namespace Maximethebault\XmlParser;

/**
 * Class XmlParser
 *
 * Parses a XML file
 */
class XmlFileParser extends XmlDataParser
{
    /**
     * Path to the XML file
     *
     * @var string
     */
    private $_filePath;

    /**
     * @param string          $filePath        path to the XML file
     * @param XmlParserConfig $xmlParserConfig the XML Parser config object
     * @param  XmlRootElement $rootObject      the rpot element
     */
    public function __construct($filePath, $xmlParserConfig, $rootObject) {
        parent::__construct($xmlParserConfig, $rootObject);
        $this->_filePath = $filePath;
    }

    public function parseFile() {
        if(!file_exists($this->_filePath)) {
            throw new Exception\FileNotFoundException('XML file on input of XmlFileDataParser not found : ' . $this->_filePath . '.');
        }
        return parent::parse(file_get_contents($this->_filePath));
    }
}