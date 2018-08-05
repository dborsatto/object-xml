<?php

declare(strict_types=1);

/**
 * This file is part of the dborsatto/object-xml package.
 *
 * @license   MIT
 */

namespace DBorsatto\ObjectXml\Tests;

use DBorsatto\ObjectXml\Node;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testProperties()
    {
        $node = new Node();

        $node->setName('test-name');
        $this->assertSame($node->getName(), 'test-name');

        $node->setValue('test-value');
        $this->assertSame($node->getValue(), 'test-value');

        $node->setAttributes([
            'test-attribute1' => 'aaa',
            'test-attribute2' => 'bbb',
        ]);
        $this->assertSame($node->hasAttribute('test-attribute1'), true);
        $this->assertSame(isset($node['test-attribute1']), true);

        $this->assertSame($node->hasAttribute('test-attribute3'), false);
        $node->setAttribute('test-attribute3', 'ccc');
        $this->assertSame($node->hasAttribute('test-attribute3'), true);
        $node->removeAttribute('test-attribute3');
        $this->assertSame($node->hasAttribute('test-attribute3'), false);

        $this->assertSame($node->getAttribute('test-attribute1'), 'aaa');
        $this->assertSame($node['test-attribute1'], 'aaa');
        $this->assertSame($node->getAttribute('test-attribute4', 'ddd'), 'ddd');

        $node['test-attribute5'] = 'eee';
        $this->assertSame($node->getAttributes(), [
            'test-attribute1' => 'aaa',
            'test-attribute2' => 'bbb',
            'test-attribute5' => 'eee',
        ]);
        unset($node['test-attribute5']);
        $this->assertSame($node->hasAttribute('test-attribute5'), false);

        $parent = new Node();
        $node->setParent($parent);
        $this->assertSame($node->getParent(), $parent);

        $this->assertSame($node->hasChildren(), false);
        $child = (new Node('child'))
                ->setValue('child-value');
        $node->addChild($child);
        $this->assertSame($node->hasChildren(), true);

        $this->assertSame(\count($node->getChildren()), 1);

        $this->assertSame($child, $node->getChildByName('child'));
        $this->assertSame($child, $node->child);

        $this->assertSame($node->getChildrenAsArray(), [
            'child' => 'child-value',
        ]);

        $node->addChildren([new Node('child2')]);
        $this->assertCount(2, $node->getChildren());

        $node->clearChildren();
        $this->assertCount(0, $node->getChildren());

        $node->setUseCdata(false);
        $this->assertFalse($node->getUseCdata());

        $node->setUseShortTag(true);
        $this->assertTrue($node->getUseShortTag());

        $node->setOptions([]);
        $this->assertSame($node->getOptions(), [
            'value' => '',
            'attributes' => [],
            'parent' => null,
            'use_cdata' => false,
            'use_short_tag' => true,
        ]);

        $child = Node::create('child')->setValue('child-value');
        $node->addChild($child);
        foreach ($node as $nodeChild) {
            $this->assertSame($child, $nodeChild);
        }
        $this->assertTrue(isset($node->child));

        $node->second = new Node('second');
        $this->assertCount(2, $node->getChildren());
        unset($node->second);
        $this->assertSame($node->getChildren(), [$child]);
    }
}
