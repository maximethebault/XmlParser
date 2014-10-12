<?php

namespace Maximethebault\XmlParser\Tests;

use Maximethebault\XmlParser\Tests\Res\Test1;
use Maximethebault\XmlParser\Tests\Res\Test2;
use Maximethebault\XmlParser\Tests\Res\Test3;
use Maximethebault\XmlParser\XmlDataParser;
use Maximethebault\XmlParser\XmlParserConfig;

class XmlDataParserTest extends \PHPUnit_Framework_TestCase
{
    private $config;

    public function __construct() {
        $this->config = new XmlParserConfig();
        $this->config->addXmlElementFolder(__DIR__ . '/Res/Test1/', 'Maximethebault\XmlParser\Tests\Res\Test1');
        $this->config->addXmlElementFolder(__DIR__ . '/Res/Test2/', 'Maximethebault\XmlParser\Tests\Res\Test2');
        $this->config->addXmlElementFolder(__DIR__ . '/Res/Test3/', 'Maximethebault\XmlParser\Tests\Res\Test3');
    }

    public function testBasicElementExtraction() {
        $parser = new XmlDataParser($this->config, new Test1\Pages());
        $root = $parser->parse('
        <pages>
        <page>
        <node1>Heya</node1>
        </page>
        <page>
        <node2>Heyb</node2>
        </page>
        </pages>
        ');
        $this->assertEquals('Heya', $root->page[0]->node1->data());
        $this->assertEquals('Heyb', $root->page[1]->node2->data());
    }

    public function testMutli() {
        $this->setExpectedException('Maximethebault\XmlParser\Exception\MultiParseException');
        $parser = new XmlDataParser($this->config, new Test2\Pages());
        $parser->parse('
        <pages>
        <page>
        <node1>Heya</node1>
        </page>
        <page>
        <node2>Heyb</node2>
        <node1>Heya</node1>
        <node2>Heyb</node2>
        <node1>Heya</node1>
        <node1>Heya</node1>
        </page>
        </pages>
        ');
    }

    public function testUnusedCache() {
        $parser = new XmlDataParser($this->config, new Test3\Pages());
        $root = $parser->parse('
        <pages>
        <page>
        <node1>Heya</node1>
        </page>
        <page>
        <node2>Heyb</node2>
        <node1>Heya</node1>
        <node2>Heyb</node2>
        <node1>Heya</node1>
        <node1>Heya</node1>
        </page>
        </pages>
        ');
        $this->assertEquals('Heya', $root->page[0]->node1[0]->data());
        $this->assertEquals('Heyb', $root->page[1]->node2[0]->data());
    }

    public function testUsedCache() {
        $parser = new XmlDataParser($this->config, new Test3\Pages());
        $root = $parser->parse('
        <pages>
        <page>
        <node1 id="1">Heya</node1>
        </page>
        <page>
        <node2>Heyb</node2>
        <node1 id="1">Heya</node1>
        <node2>Heyb2</node2>
        <node1 id="2">Heya</node1>
        <node1>Heya</node1>
        </page>
        </pages>
        ');
        // we check that node1 elements with the same ID have been merged
        $this->assertTrue($root->page[0]->node1[0] == $root->page[1]->node1[0]);
        // even though the duplicated elements have been merged, content of both hasn't been concated. The parser will retain the one with the greatest length.
        $this->assertEquals('Heya', $root->page[0]->node1[0]->data());
        $this->assertEquals('Heyb', $root->page[1]->node2[0]->data());
        $this->assertEquals('Heyb2', $root->page[1]->node2[1]->data());
    }
}