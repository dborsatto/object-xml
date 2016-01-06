<?php

namespace DBorsatto\ObjectXml;

/**
 * Manager class
 * This class works as a manager for the ObjextXml library.
 *
 * @author Davide Borsatto <davide.borsatto@gmail.com>
 */
class Manager
{
    /**
     * The number of indentation spaces to be used the outputting XML.
     *
     * @var int
     */
    private $indentationSpaces = 4;

    /**
     * Parses an XML string.
     *
     * @param string $source The XML string
     *
     * @return Node The Node representation of the given XML
     */
    public function parseString($source)
    {
        return $this->xmlToObject($source);
    }

    /**
     * Parses an XML file.
     *
     * @param string $path The file path
     *
     * @return Node
     */
    public function parseFile($path)
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException('The given path is not a valid file');
        }

        return $this->parseString(file_get_contents($path));
    }

    /**
     * Converts an XML string into an object struct.
     *
     * @param string $data The XML string
     *
     * @return esXml The parsed XML
     */
    private function xmlToObject($data)
    {
        $root = new Node();
        $root->setName('root');
        $actualLevel = 1;
        $actualNode = $root;
        $stack = array();
        $stack[1] = $root;

        foreach ($this->parseIntoStruct($data) as $element) {
            if ($element['type'] === 'close') {
                continue;
            }

            $node = new Node();
            $node->setName($element['tag']);

            if (isset($element['attributes'])) {
                $node->setAttributes($element['attributes']);
            }
            if (isset($element['value'])) {
                $node->setValue($element['value']);
            }

            $level = $element['level'];
            if ($level > $actualLevel) {
                $stack[$level] = $actualNode;
            }
            $stack[$level]->addChild($node);
            $actualNode = $node;
            $actualLevel = $element['level'];
        }
        $children = $root->getChildren();
        unset($root);

        return $children[0]->setParent(null);
    }

    /**
     * Parses XML using native php functions.
     *
     * @param string $data The string to be parsed
     *
     * @return array The parsed XML
     */
    private function parseIntoStruct($data)
    {
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $data, $xmlValues);

        if ($error = xml_get_error_code($parser) !== XML_ERROR_NONE) {
            throw new \RuntimeException('The XML parser return an error with message: '.xml_error_string($error));
        }
        xml_parser_free($parser);

        return $xmlValues;
    }

    /**
     * Sets the current indentation spaces policy.
     *
     * @param int $indentationSpaces
     *
     * @return Manager
     */
    public function setIndentationSpaces($indentationSpaces)
    {
        $this->indentationSpaces = $indentationSpaces;

        return $this;
    }

    /**
     * Returns the current indentation spaces policy.
     *
     * @return int
     */
    public function getIndentationSpaces()
    {
        return $this->indentationSpaces;
    }

    /**
     * Returns the XML rappresentation of the given Node with its children.
     *
     * @param Node $node       The Node to be converted to string
     * @param bool $header     Whether to add the XML header
     * @param bool $forceCdata Whether the XML content will be enclosed within CDATA tags
     *
     * @return string The XML representation
     */
    public function toString(Node $node, $header = true, $forceCdata = false)
    {
        $xml = $header ? '<?xml version="1.0" encoding="UTF-8"?>'."\n" : '';

        return $xml.$this->toStringRecursive($node, 0, $forceCdata);
    }

    /**
     * Returns the XML rappresentation of the Node.
     *
     * @param Node $node               The Node to be converted to string
     * @param int  $currentIndentation The current indentation level
     * @param bool $forceCdata         Whether the XML content will be enclosed within CDATA tags
     *
     * @return string The XML rappresentation of the Node
     */
    private function toStringRecursive(Node $node, $currentIndentation, $forceCdata)
    {
        if (!$node->getName()) {
            throw new \RuntimeException('Can not create an XML representation of a Node without a name');
        }

        $xml = str_pad('', $currentIndentation, ' ', STR_PAD_LEFT);

        $xml .= '<'.$node->getName();
        foreach ($node->getAttributes() as $key => $value) {
            $xml .= ' '.$key.'="'.$value.'"';
        }

        if (!$node->hasChildren()) {
            // The Node has no children, so only content needs to be displayed
            if ($node->getValue()) {
                // The Node has content to be displayed
                $xml .= '>';
                if ($node->getUseCdata() or $forceCdata) {
                    $xml .= '<![CDATA['.$node->getValue().']]>';
                } else {
                    $xml .= $node->getValue();
                }
                $xml .= '</'.$node->getName().'>';
            } elseif ($node->getUseShortTag()) {
                // The Node has no content in it, and short-tag is enabled
                $xml .= ' />';
            } else {
                // The Node has no content, and short-tag is not enabled
                $xml .= '>';
                if ($node->getUseCdata() or $forceCdata) {
                    $xml .= '<![CDATA[]]>';
                }
                $xml .= '</'.$node->getName().'>';
            }
        } else {
            // The Node has children, so they must be displayed before closing the XML tag
            $xml .= '>'."\n";

            foreach ($node->getChildren() as $child) {
                $xml .= $this->toStringRecursive($child, $currentIndentation + $this->getIndentationSpaces(), $forceCdata);
            }

            $xml .= str_pad('', $currentIndentation, ' ', STR_PAD_LEFT).'</'.$node->getName().'>';
        }

        return $xml."\n";
    }

    /**
     * Returns a tree representation of the XML Node, useful for debugging.
     *
     * @param Node $node               The current Node
     * @param int  $currentIndentation The current indentation level
     *
     * @return string
     */
    public function toTree(Node $node, $currentIndentation = 0)
    {
        if ($currentIndentation === 0) {
            $tree = $node->getName()."\n";
        } else {
            $tree = str_pad('', $currentIndentation, '-', STR_PAD_LEFT).' '.$node->getName()."\n";
        }

        foreach ($node->getChildren() as $child) {
            $tree .= $this->toTree($child, $currentIndentation + $this->getIndentationSpaces());
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
    public function toArray(Node $node)
    {
        return array(
            'name'=> $node->getName(),
            'value' => $node->getValue(),
            'attributes' => $node->getAttributes(),
            'children' => array_map(function (Node $child) {
                return $this->toArray($child);
            }, $node->getChildren()),
        );
    }
}
