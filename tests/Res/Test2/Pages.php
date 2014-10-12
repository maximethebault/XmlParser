<?php

namespace Maximethebault\XmlParser\Tests\Res\Test2;

use Maximethebault\XmlParser\XmlRootElement;

class Pages extends XmlRootElement
{
    public $children = array('page' => array('multi' => true, 'class' => 'Maximethebault\XmlParser\Tests\Res\Test2\Page'));

    /**
     * @return string this element's tag name
     */
    public function getName() {
        return 'pages';
    }
}