<?php

namespace Maximethebault\XmlParser\Tests\Res\Test2;

use Maximethebault\XmlParser\XmlElement;

class Page extends XmlElement
{
    public $children = array('node1' => array('cache_attr' => 'id', 'class' => 'Maximethebault\XmlParser\Tests\Res\Test2\Node1'),
                             'node2' => array('class' => 'Maximethebault\XmlParser\Tests\Res\Test2\Node2'));

    /**
     * @return string this element's tag name
     */
    public function getName() {
        return 'page';
    }
}