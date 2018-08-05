<?php

declare(strict_types=1);

/**
 * This file is part of the dborsatto/object-xml package.
 *
 * @license   MIT
 */

namespace DBorsatto\ObjectXml\Tests;

use DBorsatto\ObjectXml\Manager;
use DBorsatto\ObjectXml\Node;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testManager()
    {
        $filePath = __DIR__.'/Resources/input-example.xml';
        $manager = new Manager();
        $node = $manager->parseFile($filePath);
        $this->assertSame($manager->toString($node, true), \file_get_contents($filePath));
        $expectedArray = include __DIR__.'/Resources/output-array.php';
        $this->assertSame($expectedArray, $manager->toArray($node));

        $manager->setIndentationSpaces(2);
        $this->assertSame($manager->getIndentationSpaces(), 2);

        $filePath = __DIR__.'/Resources/output-example.xml';
        $node = new Node('node');
        $child1 = (new Node('child1'))->setUseShortTag(true);
        $node->addChildren([
            $child1,
            (new Node('child2'))->setUseShortTag(false),
            (new Node('child3'))->setUseShortTag(false)->setUseCdata(false)->setValue('child3-value'),
            (new Node('child4'))->setUseShortTag(false)->setUseCdata(true),
        ]);
        $this->assertSame($manager->toString($node), \file_get_contents($filePath));

        $filePath = __DIR__.'/Resources/output-tree.txt';
        $child1->addChild(new Node('second-level-child'));
        $this->assertSame($manager->toTree($node), \file_get_contents($filePath));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidNode()
    {
        (new Manager())
            ->toString(new Node());
    }
}
