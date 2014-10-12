<?php

namespace Maximethebault\XmlParser\Tests\Res\Test3;

use Maximethebault\XmlParser\XmlRootElement;

class Pages extends XmlRootElement
{
    public $children = array('page'  => array('multi' => true, 'class' => 'Maximethebault\XmlParser\Tests\Res\Test3\Page'),
                             'node1' => array('cache_attr' => 'id', 'class' => 'Maximethebault\XmlParser\Tests\Res\Test3\Node1'),);

    /**
     * @return string this element's tag name
     */
    public function getName() {
        return 'pages';
    }
}