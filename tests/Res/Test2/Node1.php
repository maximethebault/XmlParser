<?php

namespace Maximethebault\XmlParser\Tests\Res\Test2;

use Maximethebault\XmlParser\XmlElement;

class Node1 extends XmlElement
{

    /**
     * @return string this element's tag name
     */
    public function getName() {
        return 'node1';
    }
}