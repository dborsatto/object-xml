<?php

namespace DBorsatto\ObjectXml;

class Dumper
{
    /**
     * Returns a tree representation of the XML Node, useful for debugging.
     *
     * @param Node $node               The current Node
     * @param int  $indent             The amount of indentation spaces
     * @param int  $currentIndentation The current indentation level
     *
     * @return string
     */
    public function toTree(Node $node, int $indent = 4, int $currentIndentation = 0): string
    {
        $tree = \str_pad('', $currentIndentation, '-', \STR_PAD_LEFT).' '.$node->getName()."\n";

        foreach ($node->getChildren() as $child) {
            $tree .= $this->toTree($child, $indent, $currentIndentation + $indent);
        }

        return $tree;
    }

    /**
     * Returns an array representation of the given Node structure.
     *
     * @param Node $node
     *
     * @return array
     */
    public function toArray(Node $node): array
    {
        return [
            'name' => $node->getName(),
            'value' => $node->getValue(),
            'attributes' => $node->getAttributes(),
            'children' => \array_map(
                function (Node $child): array {
                    return $this->toArray($child);
                },
                $node->getChildren()->getNodes()
            ),
        ];
    }

    /**
     * Returns the XML representation of the given Node with its children.
     *
     * @param Node $node       The Node to be converted to string
     * @param bool $forceCdata Whether the XML content will be enclosed within CDATA tags
     *
     * @return string
     */
    public function toXml(Node $node, bool $forceCdata = false): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->appendChild(
            $this->convertToDOMElement($document, $node, $forceCdata)
        );
        $document->formatOutput = true;

        return $document->saveXML();
    }

    /**
     * Returns the DOM representation of the Node.
     *
     * @param \DOMDocument $document
     * @param Node $node       The Node to be converted to string
     * @param bool $forceCdata Whether the XML content will be enclosed within CDATA tags
     *
     * @return \DOMElement The DOM representation of the Node
     */
    private function convertToDOMElement(\DOMDocument $document, Node $node, bool $forceCdata): \DOMElement
    {
        if (!$node->getName()) {
            throw new \RuntimeException('Can not create an XML representation of a Node without a name');
        }

        $element = $document->createElement($node->getName());
        foreach ($node->getAttributes() as $name => $value) {
            $element->appendChild(new \DOMAttr($name, $value));
        }

        if (!$node->hasChildren()) {
            $element->appendChild(($node->getUseCdata() || $forceCdata)
                ? new \DOMCdataSection($node->getValue())
                : new \DOMText($node->getValue())
            );

            return $element;
        }

        foreach ($node->getChildren()->getNodes() as $child) {
            $element->appendChild(
                $this->convertToDOMElement($document, $child, $forceCdata)
            );
        }

        return $element;
    }
}
