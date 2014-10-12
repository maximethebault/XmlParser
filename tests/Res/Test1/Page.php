<?php

namespace Maximethebault\XmlParser\Tests\Res\Test1;

use Maximethebault\XmlParser\XmlElement;

class Page extends XmlElement
{
    public $children = array('node1' => array('class' => 'Maximethebault\XmlParser\Tests\Res\Test1\Node1'),
                             'node2' => array('class' => 'Maximethebault\XmlParser\Tests\Res\Test1\Node2'));

    /**
     * @return string this element's tag name
     */
    public function getName() {
        return 'page';
    }
}