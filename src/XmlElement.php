<?php

namespace Maximethebault\XmlParser;

/**
 * Class XmlRootElement
 *
 * Represents any kind of XML elment.
 *
 * @package Maximethebault\XmlParser
 */
abstract class XmlElement
{
    /**
     * To be overriden - Used to define the possible children of the current XML element
     * One set of children = one entry in the array.
     * Each entry must look like this:
     * tagName => options
     *
     * ... where options is an array that can have the following elements:
     *      - multi: whether the current XmlElement is supposed to return more than one children named tagName. If true, the accessor will return an array instead of an XmlElement object. Default: false.
     *      - accessor: defines a custom name for the getter, instead of using the tagName
     *      - cache_attr: useful when some XML is duplicated at several places in the file, and each one of the duplicata has got complementary informations over the others.
     *                    In the end, the parser will hold an unique object that gathers all of these complementary informations.
     *                    The value of this option must be the name of the attribute holding the unique identifier.
     *                    The cache's scope is limited to the XmlElement defining the cache_attr option and its children.
     *      - class: class name, useful if namespaced
     *
     * Example :
     *
     * Consider the XML file:
     * <root>
     *  <node1>
     *   <node2>
     *    <node3 id="1" attr1="1"></node>
     *   </node2>
     *   <node4>
     *    <node3 id="1" attr2="2">
     *     <additionalData>Hello</additionalData>
     *    </node>
     *   </node4>
     *  </node1>
     *  <!-- new node1-->
     *  <node1>
     *   <node2>
     *    <node3 id="1" attr1="1"></node>
     *   </node2>
     *   <node4>
     *    <node3 id="1" attr2="2">
     *     <additionalData>Hello</additionalData>
     *    </node>
     *   </node4>
     *  </node1>
     * </root>
     *
     * Consider the configuration:
     * root: children('node1' => array('multi' => true, 'accessor' => 'node1s'))
     * node1: children('node2',
     *                 'node4',
     *                 'node3' => array('multi' => true, 'cache_attr' => 'id'))// (cache's scope for node3 will be limited to inner of each node1's node)
     * node2: children('node3')
     * node3: children('additionalData')
     * node4: children('node3')
     *
     * Assertions :
     * $root->node1s[0]->node2->node3 == $root->node1s[0]->node4->node3
     * $root->node1s[0]->node2->node3 != $root->node1s[1]->node2->node3// (because of cache's scope)
     * $root->node1s[0]->node2->node3->additionalData->data() == "Hello"
     * $root->node1s[0]->node2->node3->attrs('attr2') == "2"
     *
     *
     * @var array
     */
    protected $children = array();
    /**
     * The object that is currently being parsed
     *
     * @var XmlElement
     */
    protected $_parsingObject;
    /**
     * Used as cache by the 'isCached' function. It's a cache for the cache!
     *
     * Reminder on the cache feature purpose:
     * Some XML tags can sometimes be duplicated at several places in the file.
     * If this is set to true, we'll scan the parent tree until we find a XmlElement which has this kind of element for children, and which has a 'cache_attr' on it.
     * Then, instead of creating a new element, we'll reuse an older one if available, and we'll complete it.
     *
     * @var array
     *
     * @see isCached
     * @see $children\options\cache_attr
     */
    private $_isCachedCache;
    /**
     * Holds the XmlElement parsed so far
     *
     * @var array
     */
    private $_children;
    /**
     * Holds the cached XmlElement
     *
     * @var array
     */
    private $_cachedElements;
    /**
     * Holds the parent, or null if it's the root element
     *
     * @var XmlElement
     */
    private $_parent;
    /**
     * Holds the XmlElement attributes, if any
     *
     * @var array
     */
    private $_attrs;
    /**
     * Holds the XmlElement data, if any.
     * Usually a string, but becomes an array when we step into a duplicate element.
     * When we step out of it, it becomes a string again, therefore, after the parser has been executed, it will always be a string.
     *
     * @var mixed
     */
    private $_data;

    public function __construct($parent, $attrs) {
        $this->_parent = $parent;
        $this->_attrs = $attrs;
        $this->_data = '';
        $this->_opened = false;
        $this->_isCachedCache = array();
        $this->initChildren();
    }

    /**
     * Magic getter for easily accessing the children
     *
     * @param $name string name of the called getter
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get($name) {
        if(array_key_exists($name, $this->_children)) {
            return $this->_children[$name];
        }
        throw new \Exception('Undefined getter ' . $name . '!');
    }

    /**
     * Does a getter exist for that name?
     *
     * @param $name name of the called getter
     *
     * @return bool
     */
    public function __isset($name) {
        return array_key_exists($name, $this->_children);
    }

    /**
     * Returns the XmlElement's attributes
     *
     * @return array
     */
    public function attrs() {
        return $this->_attrs;
    }

    /**
     * Returns the XmlElement's data
     *
     * @return string
     */
    public function data() {
        return $this->_data;
    }

    /**
     * @return string this element's tag name
     */
    abstract public function getName();

    /**
     * Called by the parser whenever a tag is opened at this or a deeper depth in the XML tree
     *
     * @param $parser  resource the XML parser resource
     * @param $tagName string the tag name of the opened XML tag
     * @param $attrs   array  the attributes of the opened XML tag
     *
     * @throws Exception\ParseException
     */
    protected function tagOpen($parser, $tagName, $attrs) {
        if($this->_parsingObject) {
            $this->_parsingObject->tagOpen($parser, $tagName, $attrs);
        }
        else {
            foreach($this->children as $relationName => $relationOptions) {
                if($relationName == $tagName) {
                    if($this->isCached($tagName)) {
                        $this->_parsingObject = & $this->getCachedElement($tagName, $attrs);
                    }
                    if(!$this->_parsingObject) {
                        $objectName = $relationOptions['class'];
                        $this->_parsingObject = new $objectName($this, $attrs);
                    }
                    else {
                        $this->_parsingObject->openDuplicata($this, $attrs);
                    }
                    return;
                }
            }
            throw new Exception\ParseException(xml_get_current_line_number($parser), 'Unexpected tag "' . $tagName . '" at ' . $this->getName());
        }
    }

    /**
     * Called by the parser whenever a tag at this or a deeper depth in the XML tree has got data
     *
     * @param $parser  resource the XML parser resource
     * @param $data    string the data of the tag
     *
     * @throws Exception\ParseException
     */
    protected function tagData($parser, $data) {
        if($this->_parsingObject) {
            $this->_parsingObject->tagData($parser, $data);
        }
        else {
            if(is_array($this->_data)) {
                $this->_data[1] .= $data;
            }
            else {
                $this->_data .= $data;
            }
        }
    }

    /**
     * Called by the parser whenever a tag is closed at this or a deeper depth in the XML tree
     *
     * @param $parser  resource the XML parser resource
     * @param $tagName string the tag name of the closed XML tag
     *
     * @throws Exception\ParseException
     */
    protected function tagClosed($parser, $tagName) {
        if($this->_parsingObject) {
            $this->_parsingObject->tagClosed($parser, $tagName);
        }
        else {
            if($tagName == $this->getName()) {
                if($this->_parent) {
                    $this->_parent->childClosed($parser, $this->getName());
                }
            }
            else {
                throw new Exception\ParseException(xml_get_current_line_number($parser), 'Met closing tag "' . $tagName . '", was expecting ' . $this->getName());
            }
        }
    }

    /**
     * Takes care of the closing of a child element
     *
     * @param $parser  resource the XML parser resource
     * @param $tagName string the tag name of the children XML tag that is being closed
     *
     * @throws Exception\MultiParseException
     */
    protected function childClosed($parser, $tagName) {
        foreach($this->children as $relationName => $relationOptions) {
            if($relationName == $tagName) {
                if(!$relationOptions['multi'] && $this->_children[$relationName] !== null) {
                    throw new Exception\MultiParseException(xml_get_current_line_number($parser), 'Expected only one child of type "' . $tagName . '" at ' . $this->getName() . ' because multi = false. You vile liar!');
                }
                elseif($relationOptions['multi']) {
                    $this->_children[$relationName][] = $this->_parsingObject;
                }
                else {
                    $this->_children[$relationName] = $this->_parsingObject;
                }

                if($this->isCached($tagName)) {
                    $this->_parsingObject->closeDuplicate();
                    // in this case, $this->_parsingObject is a reference.
                    // if we set it to null, we'd erase our work. That's definitely not what we want!
                    // we need to unset it first.
                    unset($this->_parsingObject);
                    $this->_parsingObject = null;
                }
                else {
                    $this->_parsingObject = null;
                }
            }
        }
    }

    /**
     * Normalize the configuration & initialize _children array.
     *
     * @see $_children
     */
    private function initChildren() {
        $normalizedChildren = array();
        foreach($this->children as $relationName => $relationOptions) {
            // we normalize the children configuration and set the default value whenever possible
            if(!is_array($relationOptions)) {
                $relationName = $relationOptions;
                $relationOptions = array('multi' => false, 'accessor' => $relationName, 'cache_attr' => null, 'class' => ucfirst($relationName));
            }
            else {
                if(!array_key_exists('accessor', $relationOptions)) {
                    $relationOptions['accessor'] = $relationName;
                }
                if(!array_key_exists('multi', $relationOptions)) {
                    $relationOptions['multi'] = false;
                }
                if(!array_key_exists('cache_attr', $relationOptions)) {
                    $relationOptions['cache_attr'] = null;
                }
                if(!array_key_exists('class', $relationOptions)) {
                    $relationOptions['class'] = ucfirst($relationName);
                }
            }
            // we initialize the array that will contain the children objects
            $this->_children[$relationOptions['accessor']] = $relationOptions['multi'] ? array() : null;
            if($relationOptions['cache_attr'] !== null) {
                $this->_cachedElements[$relationName] = array();
            }
            $normalizedChildren[$relationName] = $relationOptions;
        }
        $this->children = $normalizedChildren;
    }

    /**
     * Checks if the given element is cached along the parent tree
     *
     * @param $tagName string the tagName of the element we want to check
     *
     * @return bool
     */
    private function isCached($tagName) {
        if(array_key_exists($tagName, $this->_isCachedCache)) {
            return $this->_isCachedCache[$tagName];
        }
        $isCached = false;
        foreach($this->children as $relationName => $relationOptions) {
            if($relationName == $tagName && $relationOptions['cache_attr'] !== null) {
                $isCached = true;
            }
        }
        if(!$isCached) {
            if($this->_parent) {
                $isCached = $this->_parent->isCached($tagName);
            }
        }
        $this->_isCachedCache[$tagName] = $isCached;
        return $isCached;
    }

    /**
     * Returns the cached element that matches the given parameters
     *
     * @param $tagName string tag name of the element
     * @param $attrs   array the array of the element's attributes
     *
     * @return XmlElement the XmlElement that was cached or null if element hasn't been cached yet
     *
     * @throws \Exception
     */
    private function &getCachedElement($tagName, $attrs) {
        if(!$this->isCached($tagName)) {
            throw new \Exception('Library internal usage error: should check if an element is cached before applying getCachedElement for ' . $tagName . '.');
        }
        foreach($this->children as $relationName => $relationOptions) {
            if($relationName == $tagName && $relationOptions['cache_attr'] !== null) {
                if(!array_key_exists($relationOptions['cache_attr'], $attrs)) {
                    // the element can't be cached (required attribute is missing)
                    // => we won't index it in the cachedElements array, but we return a reference anyway, as this method is supposed to, because we don't want to break the logic depending on that method
                    $garbage = null;
                    return $garbage;
                }
                if(!array_key_exists($attrs[$relationOptions['cache_attr']], $this->_cachedElements[$relationName])) {
                    //the element hasn't been cached yet
                    $this->_cachedElements[$relationName][$attrs[$relationOptions['cache_attr']]] = null;
                }
                return $this->_cachedElements[$relationName][$attrs[$relationOptions['cache_attr']]];
            }
        }
        if(!$this->_parent) {
            throw new \Exception('Library internal illogical state: isCached = true but couldn\'t find a cached version for ' . $tagName . '.');
        }
        $parentCache = & $this->_parent->getCachedElement($tagName, $attrs);
        return $parentCache;
    }

    /**
     * Called whenever a duplicate element of this former one is opened
     *
     * When this method is called, $this is the former opened element
     *
     * @param $parent XmlElement the parent of the new duplicate element
     * @param $attrs  array the attribute array of the new duplicate element
     */
    private function openDuplicata($parent, $attrs) {
        $this->_parent = $parent;
        $this->mergeAttrs($attrs);
        $this->_data = array($this->_data, '');
    }

    /**
     * Merge the attributes of a duplicated element
     *
     * @param $attrs array the attributes we want to merge with the one of the current object
     */
    private function mergeAttrs($attrs) {
        $this->_attrs = array_merge($this->_attrs, $attrs);
    }

    /**
     * Called whenever a cached element is closed
     */
    private function closeDuplicate() {
        if(is_array($this->_data)) {
            if(strlen(trim($this->_data[0])) > strlen(trim($this->_data[1]))) {
                $this->_data = $this->_data[0];
            }
            else {
                $this->_data = $this->_data[1];
            }
        }
    }
}