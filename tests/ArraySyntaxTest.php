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

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function testParseWithWrongItem() {
        $syntax = S::arr(S::number());
        $syntax->parse('23,43,wrong,7');
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
    public function testDumpWrongArray() {
        $syntax = S::arr();
        $syntax->dump('nan');
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function testDumpWrongItem() {
        $syntax = S::arr(S::number());
        $syntax->dump([12, 32, 'wrong', 87]);
    }

}
