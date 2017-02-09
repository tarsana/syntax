<?php

use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\ConstantSyntax;

class ConstantSyntaxTest extends PHPUnit_Framework_TestCase {

    public function test_parse() {
        $foo = S::constant('Foo');
        $this->assertEquals('Foo', $foo->parse('Foo'));

        $foo = S::constant('Foo', false);
        $this->assertEquals('Foo', $foo->parse('FoO'));
        $this->assertEquals('Foo', $foo->parse('foo'));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\Exception
     */
    public function test_parse_wrong_boolean() {
        new ConstantSyntax(123); // value should be a string !
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_wrong_boolean() {
        $foo = S::constant('foo');
        $syntax->parse('Foo'); // it's case sensitive
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_wrong_boolean() {
        $foo = S::constant('foo');
        $syntax->parse('bar');
    }

    public function test_dump() {
        $foo = S::constant('Foo');
        $this->assertEquals('Foo', $foo->parse('Foo'));

        $foo = S::constant('Foo', false);
        $this->assertEquals('Foo', $foo->parse('FoO'));
        $this->assertEquals('Foo', $foo->parse('foo'));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_wrong_boolean() {
        $syntax = S::constant('Foo');
        $this->assertFalse($syntax->canDump('bar'));
        $syntax->dump('bar');
    }

}
