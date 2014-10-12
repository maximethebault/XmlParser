<?php

namespace Maximethebault\XmlParser\Tests\Res\Test3;

use Maximethebault\XmlParser\XmlElement;

class Page extends XmlElement
{
    public $children = array('node1' => array('multi' => true, 'class' => 'Maximethebault\XmlParser\Tests\Res\Test3\Node1'),
                             'node2' => array('multi' => true, 'class' => 'Maximethebault\XmlParser\Tests\Res\Test3\Node2'));

    /**
     * @return string this element's tag name
     */
    public function getName() {
        return 'page';
    }
}