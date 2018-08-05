<?php

declare(strict_types=1);

/**
 * This file is part of the dborsatto/object-xml package.
 *
 * @license   MIT
 */

namespace DBorsatto\ObjectXml;

/**
 * Node class
 * This class is a representation of a XML node.
 */
class Node implements \ArrayAccess, \IteratorAggregate
{
    /**
     * The name of the node.
     *
     * @var string
     */
    protected $name = '';

    /**
     * The value.
     *
     * @var string
     */
    protected $value = '';

    /**
     * The attributes of the node, in a key => value form.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The parent (if exists) of this node.
     *
     * @var Node|null
     */
    protected $parent;

    /**
     * The children nodes.
     *
     * @var NodeCollection
     */
    protected $children;

    /**
     * If the node must print the cdata string while converted to XML.
     *
     * @var bool
     */
    protected $useCdata = false;

    /**
     * If the node must be printed in the short mode (<node />) instead of the default (<node></node>).
     *
     * @var bool
     */
    protected $useShortTag = true;

    /**
     * The default options of the node.
     *
     * @var array
     */
    protected static $defaultOptions = [
        'value' => '',
        'attributes' => [],
        'parent' => null,
        'use_cdata' => false,
        'use_short_tag' => true,
    ];

    /**
     * Node constructor.
     *
     * @param string $name
     */
    public function __construct(string $name = '')
    {
        $this->children = new NodeCollection();
        $this->setName($name);
    }

    /**
     * Returns a new instance, useful for fluent interfaces.
     *
     * @param string $name
     *
     * @return Node A new Node instance
     */
    public static function create(string $name = ''): self
    {
        return new self($name);
    }

    /**
     * Sets the name of the node.
     *
     * @param string $name
     *
     * @return Node
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the name of the node.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the value of the node.
     *
     * @param string $value
     *
     * @return Node
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Gets the value of the node.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Returns if the node has an attribute or not.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return \array_key_exists($name, $this->attributes);
    }

    /**
     * Returns the request attribute of the node.
     *
     * @param string $name
     * @param string $default
     *
     * @return string|number
     */
    public function getAttribute(string $name, string $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : $default;
    }

    /**
     * Returns an array of attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Sets an attribute of the current node.
     *
     * @param string $name
     * @param string $value
     *
     * @return Node
     */
    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Sets an array of attributes.
     *
     * @param array $attributes An array with new attributes if key => value form
     * @param bool  $overwrite  Whether to overwrite or merge the previous attributes
     *
     * @return Node
     */
    public function setAttributes(array $attributes, bool $overwrite = true): self
    {
        $this->attributes = $overwrite ? $attributes : \array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Removes an attribute.
     *
     * @param string $name
     *
     * @return Node
     */
    public function removeAttribute(string $name): self
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * Returns the parent (if exists) of the current node.
     *
     * @return self|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * Sets the parent of the current node.
     *
     * @param self|null $parent
     *
     * @return self
     */
    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Returns true if the node has at least one child.
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return (bool) \count($this->children);
    }

    /**
     * Returns the children of the current node.
     *
     * @return NodeCollection
     */
    public function getChildren(): NodeCollection
    {
        return $this->children;
    }

    /**
     * Returns an array of filtered children.
     *
     * @param \Closure $filter
     *
     * @return NodeCollection
     */
    public function filterChildren(\Closure $filter): NodeCollection
    {
        return $this->children->filter($filter);
    }

    /**
     * Filter the children by the tag name.
     *
     * @param string $name
     *
     * @return NodeCollection
     */
    public function getChildrenByName(string $name): NodeCollection
    {
        return $this->children->filter(function (self $node) use ($name): bool {
            return $node->getName() === $name;
        });
    }

    /**
     * Returns the first child with the selected name.
     *
     * @param string $name
     *
     * @return self|null
     */
    public function getChildByName(string $name): ?self
    {
        return $this->getChildrenByName($name)->first();
    }

    /**
     * Returns an key => value representation of the Node's children.
     *
     * @return string[]
     */
    public function getChildrenAsArray(): array
    {
        $values = [];
        foreach ($this->children as $child) {
            $values[$child->getName()] = $child->getValue();
        }

        return $values;
    }

    /**
     * Adds a child to the current node.
     *
     * @param self $node The child to add
     *
     * @return self
     */
    public function addChild(self $node): self
    {
        $node->setParent(null); // @TODO
        $this->children->addNode($node);

        return $this;
    }

    /**
     * Adds an array of Node nodes.
     *
     * @param self[] $nodes An array of Node instances
     *
     * @return self
     */
    public function addChildren(array $nodes): self
    {
        \array_map(function (self $child): void {
            $this->addChild($child);
        }, $nodes);

        return $this;
    }

    /**
     * Removes all the children of the current node.
     *
     * @return Node
     */
    public function clearChildren(): self
    {
        \array_map(function (self $child): void {
            $child->setParent(null);
        }, $this->getChildren()->getNodes());

        $this->children = new NodeCollection();

        return $this;
    }

    /**
     * If the node must print the cdata string while converted to XML.
     *
     * @param bool $useCdata
     *
     * @return Node
     */
    public function setUseCdata(bool $useCdata): self
    {
        $this->useCdata = (bool) $useCdata;

        return $this;
    }

    /**
     * Tells if the node will be rendered using the cdata tags.
     *
     * @return bool
     */
    public function getUseCdata(): bool
    {
        return $this->useCdata;
    }

    /**
     * Sets if the short tag mode must be used in conversion to XML.
     *
     * @param bool $useShortTag
     *
     * @return Node
     */
    public function setUseShortTag(bool $useShortTag): self
    {
        $this->useShortTag = (bool) $useShortTag;

        return $this;
    }

    /**
     * Tells if the node will be rendered in the short form if it is empty.
     *
     * @return bool
     */
    public function getUseShortTag(): bool
    {
        return $this->useShortTag;
    }

    /**
     * Sets an array of options.
     *
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options): self
    {
        $options = \array_merge(self::$defaultOptions, $options);

        return $this
            ->setValue($options['value'])
            ->setAttributes($options['attributes'])
            ->setParent($options['parent'])
            ->setUseCdata($options['use_cdata'])
            ->setUseShortTag($options['use_short_tag']);
    }

    /**
     * Returns an array of current options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'value' => $this->value,
            'attributes' => $this->attributes,
            'parent' => $this->parent,
            'use_cdata' => $this->useCdata,
            'use_short_tag' => $this->useShortTag,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->children->getIterator();
    }

    /**
     * Returns if the node has an attribute or not.
     *
     * @param mixed $name
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->hasAttribute($name);
    }

    /**
     * Returns the request attribute of the node.
     *
     * @param string $name
     *
     * @return string
     */
    public function offsetGet($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Sets an attribute of the current node.
     *
     * @param string $name
     * @param string $value
     */
    public function offsetSet($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * Removes an attribute.
     *
     * @param string $name
     */
    public function offsetUnset($name)
    {
        $this->removeAttribute($name);
    }

    /**
     * Tells whether the searched child exists or not.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return \count($this->getChildrenByName($name)) > 0;
    }

    /**
     * Returns the children with the selected name.
     *
     * @param string $name
     *
     * @return NodeCollection
     */
    public function __get($name)
    {
        return $this->getChildrenByName($name);
    }

    /**
     * Adds a child to the current node.
     *
     * @param string $name
     * @param Node   $node
     */
    public function __set($name, $node)
    {
        if ($node instanceof self) {
            $this->children->addNode($node->setName($name));
        }
    }

    /**
     * Removes the children with the given name.
     *
     * @param string $name
     */
    public function __unset($name)
    {
        $this->children = $this->children->filter(function (Node $node) use ($name): bool {
            return $node->getName() !== $name;
        });
    }
}
