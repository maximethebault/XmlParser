<?php

namespace Maximethebault\XmlParser\Tests\Res\Test3;

use Maximethebault\XmlParser\XmlElement;

class Node2 extends XmlElement
{

    /**
     * @return string this element's tag name
     */
    public function getName() {
        return 'node2';
    }
}