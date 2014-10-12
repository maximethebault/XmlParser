<?php

namespace Maximethebault\XmlParser;

/**
 * Class XmlParser
 *
 * Parses a XML file
 */
class XmlFileDataParser extends XmlDataParser
{
    /**
     * Path to the XML file
     *
     * @var string
     */
    private $_filePath;

    public function __construct($filePath, $rootObject) {
        parent::__construct($rootObject);
        $this->_filePath = $filePath;
    }

    public function parse() {
        if(!file_exists($this->_filePath)) {
            throw new Exception\FileNotFoundException('XML file on input of XmlFileDataParser not found : ' . $this->_filePath . '.');
        }
        return parent::parse(file_get_contents($this->_filePath));
    }
}