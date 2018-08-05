<?php

namespace DBorsatto\ObjectXml;

class Parser
{
    /**
     * Parses an XML string.
     *
     * @param string $source
     *
     * @return Node
     */
    public function fromString(string $source): Node
    {
        return $this->parseDom($source);
    }

    /**
     * Parses an XML file.
     *
     * @param string $path
     *
     * @return Node
     */
    public function fromFile($path): Node
    {
        if (!\is_file($path)) {
            throw new \InvalidArgumentException('The given path is not a valid file');
        }

        return $this->parseDom(\file_get_contents($path));
    }

    private function parseDom(string $xml)
    {
        $document = new \DOMDocument();
        $document->loadXML($xml);

        $root = $document->childNodes[0];

        ini_set('xdebug.var_display_max_depth', 10);
        ini_set('xdebug.var_display_max_children', 512);
        ini_set('xdebug.var_display_max_data', 2048);
        return $this->convertToNode($root);
    }

    private function convertToNode(\DOMNode $domNode): Node
    {
        $node = new Node($domNode->nodeName);

        foreach ($domNode->attributes ?: [] as $attribute) {
            $node->setAttribute($attribute->name, $attribute->value);
        }

        foreach ($domNode->childNodes ?: [] as $child) {
            if ($child->nodeName === 'xsd:element') {
                var_dump($child);
            }
            switch (get_class($child)) {
                case \DOMCdataSection::class:
                    $node->setUseCdata(true);
                    $node->setValue($child->textContent);
                    break;
                case \DOMText::class:
                    $node->setValue($child->textContent);
                    break;
                case \DOMElement::class:
                    $node->addChild($this->convertToNode($child));
                    break;
                default:
//                    var_dump($child);
            }
//            var_dump($child->nodeName, get_class($child));
//            $this->convertToNode($child);
//            $node->addChild($this->convertToNode($child))
        }

        return $node;
    }

    /**
     * Converts an XML string into a Node object structure.
     *
     * @param string $data
     *
     * @return Node
     */
    private function parse(string $data): Node
    {
        $root = new Node('root');
        $currentLevel = 1;
        $currentNode = $root;
        $stack = [1 => $root];

        foreach ($this->parseIntoStructure($data) as $element) {
            if ('close' === $element['type']) {
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
            if ($level > $currentLevel) {
                $stack[$level] = $currentNode;
            }
            $stack[$level]->addChild($node);
            $currentNode = $node;
            $currentLevel = $element['level'];
        }

        return $root->getChildren()
            ->first()
            ->setParent(null);
    }

    /**
     * Parses XML using native PHP functions.
     *
     * @param string $data The string to be parsed
     *
     * @return array
     */
    private function parseIntoStructure(string $data): array
    {
        $xmlValues = [];
        $parser = \xml_parser_create('');
        \xml_parser_set_option($parser, \XML_OPTION_TARGET_ENCODING, 'UTF-8');
        \xml_parser_set_option($parser, \XML_OPTION_CASE_FOLDING, 0);
        \xml_parser_set_option($parser, \XML_OPTION_SKIP_WHITE, 1);
        \xml_parse_into_struct($parser, $data, $xmlValues);

        $error = \xml_get_error_code($parser);
        if ($error !== \XML_ERROR_NONE) {
            throw new \RuntimeException('The XML parser return an error with message: '.\xml_error_string($error));
        }

        \xml_parser_free($parser);

        return $xmlValues;
    }
}
