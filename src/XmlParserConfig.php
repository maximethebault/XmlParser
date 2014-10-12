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
     * The Psr4-compliant class autoloader
     *
     * @var Psr4Autoloader
     */
    private $_autoloader;
    /**
     * Whether to enable case folding (the parser will convert all tag names to lowercase before comparison)
     *
     * @var bool
     */
    private $_caseFolding = true;

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
    public function getCaseFolding() {
        return $this->_caseFolding;
    }

    /**
     * Whether to enable case folding (the parser will convert all tag names to lowercase before comparison)
     *
     * @param boolean $caseFolding
     */
    public function setCaseFolding($caseFolding) {
        $this->_caseFolding = $caseFolding;
    }
} 