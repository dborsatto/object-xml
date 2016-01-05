<?php

namespace DBorsatto\ObjectXml\Test;

use DBorsatto\ObjectXml\Manager;
use DBorsatto\ObjectXml\Node;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testManager()
    {
        $filePath = __DIR__.'/Resources/input-example.xml';
        $manager = new Manager();
        $node = $manager->parseFile($filePath);
        $this->assertEquals($manager->toString($node, true, true), file_get_contents($filePath));

        $manager->setIndentationSpaces(2);
        $this->assertEquals($manager->getIndentationSpaces(), 2);

        $filePath = __DIR__.'/Resources/output-example.xml';
        $node = Node::create('node');
        $child1 = Node::create('child1')->setUseShortTag(true);
        $child2 = Node::create('child2')->setUseShortTag(false);
        $child3 = Node::create('child3')->setUseShortTag(false)->setUseCdata(false)->setValue('child3-value');
        $child4 = Node::create('child4')->setUseShortTag(false)->setUseCdata(true);
        $node->addChildren(array($child1, $child2, $child3, $child4));
        $this->assertEquals($manager->toString($node), file_get_contents($filePath));

        $filePath = __DIR__.'/Resources/output-tree.txt';
        $child1->addChild(Node::create('second-level-child'));
        $this->assertEquals($manager->toTree($node), file_get_contents($filePath));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidPath()
    {
        $manager = new Manager();
        $node = $manager->parseFile('/dev/null');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidString()
    {
        $manager = new Manager();
        $node = $manager->parseString('invalid<xml=text');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidNode()
    {
        $node = Node::create('');
        $manager = new Manager();
        $manager->toString($node);
    }
}
