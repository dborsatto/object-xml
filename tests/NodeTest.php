<?php

namespace DBorsatto\ObjectXml\Test;

use DBorsatto\ObjectXml\Node;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $node = Node::create();

        $node->setName('test-name');
        $this->assertEquals($node->getName(), 'test-name');

        $node->setValue('test-value');
        $this->assertEquals($node->getValue(), 'test-value');

        $node->setAttributes(array(
            'test-attribute1' => 'aaa',
            'test-attribute2' => 'bbb',
        ));
        $this->assertEquals($node->hasAttribute('test-attribute1'), true);
        $this->assertEquals(isset($node['test-attribute1']), true);

        $this->assertEquals($node->hasAttribute('test-attribute3'), false);
        $node->setAttribute('test-attribute3', 'ccc');
        $this->assertEquals($node->hasAttribute('test-attribute3'), true);
        $node->removeAttribute('test-attribute3');
        $this->assertEquals($node->hasAttribute('test-attribute3'), false);

        $this->assertEquals($node->getAttribute('test-attribute1'), 'aaa');
        $this->assertEquals($node['test-attribute1'], 'aaa');
        $this->assertEquals($node->getAttribute('test-attribute4', 'ddd'), 'ddd');

        $node['test-attribute5'] = 'eee';
        $this->assertEquals($node->getAttributes(), array(
            'test-attribute1' => 'aaa',
            'test-attribute2' => 'bbb',
            'test-attribute5' => 'eee',
        ));
        unset($node['test-attribute5']);
        $this->assertEquals($node->hasAttribute('test-attribute5'), false);

        $parent = Node::create();
        $node->setParent($parent);
        $this->assertEquals($node->getParent(), $parent);

        $this->assertEquals($node->hasChildren(), false);
        $child = Node::create('child')->setValue('child-value');
        $node->addChild($child);
        $this->assertEquals($node->hasChildren(), true);

        $this->assertEquals(count($node->getChildren()), 1);

        $this->assertEquals($child, $node->getChildByName('child'));
        $this->assertEquals($child, $node->child);

        $this->assertEquals($node->getChildrenAsArray(), array(
            'child' => 'child-value',
        ));

        $children = array(Node::create('child2'));
        $node->addChildren($children);
        $this->assertEquals(count($node->getChildren()), 2);

        $node->clearChildren();
        $this->assertEquals(count($node->getChildren()), 0);

        $node->setUseCdata(false);
        $this->assertEquals($node->getUseCdata(), false);

        $node->setUseShortTag(true);
        $this->assertEquals($node->getUseShortTag(), true);

        $node->setOptions(array());
        $this->assertEquals($node->getOptions(), array(
            'value' => '',
            'attributes' => array(),
            'parent' => null,
            'use_cdata' => false,
            'use_short_tag' => true,
        ));

        $child = Node::create('child')->setValue('child-value');
        $node->addChild($child);
        foreach ($node as $nodeChild) {
            $this->assertEquals($child, $nodeChild);
            $this->assertEquals($child->key(), 0);
        }
        $this->assertEquals(isset($node->child), true);

        $node->second = Node::create('second');
        $this->assertEquals(count($node->getChildren()), 2);
        unset($node->second);
        $this->assertEquals($node->getChildren(), array($child));
    }
}
