<?php

namespace DBorsatto\ObjectXml;

/**
 * Node class
 * This class is a representation of a XML node.
 *
 * @author Davide Borsatto <davide.borsatto@gmail.com>
 */
class Node implements \ArrayAccess, \Iterator
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
    protected $attributes = array();

    /**
     * The parent (if exists) of this node.
     *
     * @var Node|null
     */
    protected $parent = null;

    /**
     * The children nodes.
     *
     * @var Node[]
     */
    protected $children = array();

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
     * The current index of the iterator (need by the Iterator interface).
     *
     * @var int
     */
    private $iteratorPosition = 0;

    /**
     * The default options of the node.
     *
     * @var array
     */
    protected static $defaultOptions = array(
        'value' => '',
        'attributes' => array(),
        'parent' => null,
        'use_cdata' => false,
        'use_short_tag' => true,
    );

    /**
     * Contructor, it can set up the name and some options.
     *
     * @param string $name
     */
    public function __construct($name = '')
    {
        $this->setName($name);
    }

    /**
     * Returns a new instance, useful for fluent interfaces.
     *
     * @param string $name
     *
     * @return Node A new Node instance
     */
    static public function create($name = '')
    {
        return new self($name);
    }

    /**
     * Sets the name of the node.
     *
     * @param string $name
     *
     * @return Node The current Node instance, for fluent use
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the name of the node.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of the node.
     *
     * @param string $value
     *
     * @return Node The current Node instance, for fluent use
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Gets the value of the node.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns if the node has an attribute or not.
     *
     * @param mixed $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Returns the request attribute of the node.
     *
     * @param string $name
     * @param string $default
     *
     * @return string|number
     */
    public function getAttribute($name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : $default;
    }

    /**
     * Returns an array of attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets an attribute of the current node.
     *
     * @param string $name
     * @param string $value
     *
     * @return Node The current Node instance, for fluent use
     */
    public function setAttribute($name, $value)
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
     * @return Node The current Node instance, for fluent use
     */
    public function setAttributes(array $attributes, $overwrite = true)
    {
        $this->attributes = $overwrite ? $attributes : array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Removes an attribute.
     *
     * @param string $name
     *
     * @return Node The current Node instance, for fluent use
     */
    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * Returns the parent (if exists) of the current node.
     *
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent of the current node.
     *
     * @param Node $parent
     */
    public function setParent(Node $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Returns true if the node has at least one child.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * Returns the children of the current node.
     *
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns an array of filtered children.
     *
     * @param Closure $filter
     *
     * @return Node[]
     */
    public function filterChildren(\Closure $filter)
    {
        return array_values(array_filter($this->children, $filter));
    }

    /**
     * Filter the children by the tag name.
     *
     * @param string $name
     *
     * @return Node[]
     */
    public function getChildrenByName($name)
    {
        return $this->filterChildren(function (Node $node) use ($name) {
            return $node->getName() === $name;
        });
    }

    /**
     * Returns the first child with the selected name.
     *
     * @param string $name
     *
     * @return Node|null
     */
    public function getChildByName($name)
    {
        $children = $this->getChildrenByName($name);

        return count($children) === 0 ? null : $children[0];
    }

    /**
     * Returns an key => value representation of the Node's children.
     *
     * @return array
     */
    public function getChildrenAsArray()
    {
        $values = array();
        foreach ($this->getChildren() as $child) {
            $values[$child->getName()] = $child->getValue();
        }

        return $values;
    }

    /**
     * Adds a child to the current node.
     *
     * @param Node $node The child to add
     *
     * @return Node The current Node instance, for fluent use
     */
    public function addChild(Node $node)
    {
        $node->setParent($this);
        $this->children[] = $node;

        return $this;
    }

    /**
     * Adds an array of Node nodes.
     *
     * @param Node[] $nodes An array of Node instances
     *
     * @return Node The current Node instance, for fluent use
     */
    public function addChildren(array $nodes)
    {
        array_map(function (Node $child) {
            $this->addChild($child);
        }, $nodes);

        return $this;
    }

    /**
     * Removes all the children of the current node.
     *
     * @return Node The current Node instance, for fluent use
     */
    public function clearChildren()
    {
        array_map(function (Node $child) {
            $child->setParent(null);
        }, $this->getChildren());

        $this->children = array();

        return $this;
    }

    /**
     * If the node must print the cdata string while converted to XML.
     *
     * @param bool $useCdata
     *
     * @return Node The current Node instance, for fluent use
     */
    public function setUseCdata($useCdata)
    {
        $this->useCdata = (bool) $useCdata;

        return $this;
    }

    /**
     * Tells if the node will be rendered using the cdata tags.
     *
     * @return bool
     */
    public function getUseCdata()
    {
        return $this->useCdata;
    }

    /**
     * Sets if the short tag mode must be used in conversion to XML.
     *
     * @param bool $useShortTag
     *
     * @return Node The current Node instance, for fluent use
     */
    public function setUseShortTag($useShortTag)
    {
        $this->useShortTag = (bool) $useShortTag;

        return $this;
    }

    /**
     * Tells if the node will be rendered in the short form if it is empty.
     *
     * @return bool
     */
    public function getUseShortTag()
    {
        return $this->useShortTag;
    }

    /**
     * Sets an array of options.
     *
     * @param array $options
     *
     * @return Node The current Node instance, for fluent use
     */
    public function setOptions(array $options)
    {
        $options = array_merge(self::$defaultOptions, $options);

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
    public function getOptions()
    {
        return array(
            'value' => $this->value,
            'attributes' => $this->attributes,
            'parent' => $this->parent,
            'use_cdata' => $this->useCdata,
            'use_short_tag' => $this->useShortTag,
        );
    }

    /**
     * Returns the current child, needed by the iterator interface.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->valid() ? $this->children[$this->iteratorPosition] : null;
    }

    /**
     * Returns the currents index of the iterator, needed by the iterator interface.
     *
     * @return int
     */
    public function key()
    {
        return $this->iteratorPosition;
    }

    /**
     * Increases the index of the iterator, needed by the iterator interface.
     */
    public function next()
    {
        ++$this->iteratorPosition;
    }

    /**
     * Rewinds the index of the iterator, needed by the iterator interface.
     */
    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    /**
     * Tells whether the selected child exists or not, needed by the iterator interface.
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->children[$this->iteratorPosition]);
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
     * @param string $default
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
        return count($this->getChildrenByName($name)) > 0;
    }

    /**
     * Returns the first child with the selected name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getChildByName($name);
    }

    /**
     * Adds a child to the current node.
     *
     * @param string $name
     * @param Node   $node
     */
    public function __set($name, $node)
    {
        if ($node instanceof static) {
            $this->addChild($node->setName($name));
        }
    }

    /**
     * Removes the first child.
     *
     * @param string
     */
    public function __unset($name)
    {
        $this->children = $this->filterChildren(function (Node $node) use ($name) {
            return $node->getName() !== $name;
        });
    }
}
