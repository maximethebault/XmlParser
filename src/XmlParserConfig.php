<?php

namespace Maximethebault\XmlParser;

/**
 * Class XmlParserConfig
 *
 * Config for the XML Parser
 *
 * @package Maximethebault\XmlParser
 */
class XmlParserConfig
{
    /**
     * Element's tag name will be converted to lowercase
     */
    const XML_PARSER_CASE_LOWER = 0;
    /**
     * Element's tag name will stay untouched
     */
    const XML_PARSER_CASE_UNTOUCHED = 1;
    /**
     * Element's tag name will be converted to uppercase
     */
    const XML_PARSER_CASE_UPPER = 2;
    /**
     * The Psr4-compliant class autoloader
     *
     * @var Psr4Autoloader
     */
    private $_autoloader;
    /**
     * The case strategy to adopt for tag names
     *
     * @var bool
     */
    private $_caseStrategy = self::XML_PARSER_CASE_UNTOUCHED;

    public function __construct() {
        $this->_autoloader = new Psr4Autoloader();
        $this->_autoloader->register();
    }

    /**
     * The parser needs to know the structure of the XML file it will parse.
     * To do that, you'll need to create classes that inherits from XmlElement. One class = one element.
     * Each class defines an element and its settings, e.g. the children elements it can contain.
     *
     * The XmlParser takes care of requiring/including the right class when needed,
     * providing that you give it the folder(s) containing all these classes. That's the purpose of this method.
     *
     * @param $directory string directory where to look for the XmlElement classes
     * @param $namespace string namespace of the XmlElement classes. If the folder you submit contains subfolders, namespace should follow the directory structure (i.e., should be PSR-4 compliant).
     */
    public function addXmlElementFolder($directory, $namespace = '\\') {
        $this->_autoloader->addNamespace($namespace, $directory);
    }

    /**
     * Get current configuration for case folding
     *
     * @return boolean
     */
    public function getCaseStrategy() {
        return $this->_caseStrategy;
    }

    /**
     * The case strategy to adopt for tag names
     *
     * @param boolean $caseStrategy
     *
     * @throws \Exception
     */
    public function setCaseStrategy($caseStrategy) {
        if($caseStrategy < 0 || $caseStrategy > 3) {
            throw new \Exception('Wrong constant used for caseStrategy');
        }
        $this->_caseStrategy = $caseStrategy;
    }
} 