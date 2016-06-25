<?php

use Tarsana\Syntax\Factory as S;

class ArraySyntaxTest extends PHPUnit_Framework_TestCase {
    
    public function testParse() {
        $syntax = S::arr();

        $this->assertEquals(['foo', 'bar', 'baz'], $syntax->parse('foo,bar,baz'));
        $this->assertEquals(['foo:bar:baz'], $syntax->parse('foo:bar:baz'));
    }

    public function testParseWithSeparator() {
        $syntax = S::arr(S::string(), ':');
        $this->assertEquals(['foo', 'bar', 'baz'], $syntax->parse('foo:bar:baz'));
    }

    public function testParseListOfNumbers() {
        $syntax = S::arr(S::number());
        $this->assertEquals([1, 2.2, 0], $syntax->parse('1,2.2,0'));

        $syntax = S::arr(S::number(), '|');
        $this->assertEquals([1, 2.2, 0], $syntax->parse('1|2.2|0'));
    }

    public function testDump() {
        $syntax = S::arr();
        $this->assertEquals('foo,bar,baz', $syntax->dump(['foo', 'bar', 'baz']));

        $syntax = S::arr(S::string(), ':');
        $this->assertEquals('foo,bar:baz', $syntax->dump(['foo,bar', 'baz']));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function testDumpWrongBoolean() {
        $syntax = S::arr();
        $syntax->dump('nan');
    }

}
