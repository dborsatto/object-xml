<?php

namespace DBorsatto\ObjectXml\Tests;

use DBorsatto\ObjectXml\Dumper;
use DBorsatto\ObjectXml\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public function testParseString()
    {
        $node = (new Parser())
            ->fromFile(__DIR__.'/Resources/schema.xml');

//        var_dump($node);
//        echo((new Dumper())
//            ->toXml($node));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPath()
    {
        (new Parser())
            ->fromFile('/dev/null');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidString()
    {
        (new Parser())
            ->fromString('invalid<xml=text');
    }
}
