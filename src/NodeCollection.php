<?php

namespace DBorsatto\ObjectXml;

class NodeCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * NodeCollection constructor.
     *
     * @param Node[] $nodes
     */
    public function __construct(array $nodes = [])
    {
        $this->setNodes($nodes);
    }

    /**
     * @param Node[] $nodes
     *
     * @return NodeCollection
     */
    public function setNodes(array $nodes): self
    {
        $this->nodes = [];
        \array_map(function (Node $node): void {
            $this->addNode($node);
        }, $nodes);

        return $this;
    }

    /**
     * @param Node $node
     *
     * @return NodeCollection
     */
    public function addNode(Node $node): self
    {
        $this->nodes[] = $node;

        return $this;
    }

    public function addNodes(array $nodes): self
    {
        \array_map(function (self $child): void {
            $this->addNode($child);
        }, $nodes);

        return $this;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function filter(\Closure $filter): NodeCollection
    {
        return new self(\array_filter($this->nodes, $filter));
    }

    /**
     * @return Node|null
     */
    public function first(): ?Node
    {
        return $this->nodes ? $this->nodes[0] : null;
    }

    /**
     * @return Node|null
     */
    public function last(): ?Node
    {
        return $this->nodes ? \end(\array_values($this->nodes)) : null;
    }

    /**
     * @return Node[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->nodes[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->nodes[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!is_int($offset) || $offset < 0) {
            throw new \BadMethodCallException(\sprintf(
                'NodeCollection works as a list of nodes, trying to set Node using non positive integer offset "%s".',
                $offset
            ));
        }

        if (!$value instanceof Node) {
            throw new \InvalidArgumentException(
                'Trying to set a value which is not an instance of a Node object.'
            );
        }

        \array_splice($this->nodes, $offset, 0, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        if (!is_int($offset)) {
            throw new \BadMethodCallException(\sprintf(
                'NodeCollection works as a list of nodes, trying to unset Node using non-integer offset "%s"',
                $offset
            ));
        }

        unset($this->nodes[$offset]);
        $this->nodes = array_values($this->nodes);
    }
}
