<?php

use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\ConstantSyntax;

class ConstantSyntaxTest extends PHPUnit_Framework_TestCase {

    public function test_parse() {
        $foo = S::constant('Foo');
        $this->assertEquals('Foo', $foo->parse('Foo'));
        $this->assertTrue($foo->caseSensitive());

        $foo->caseSensitive(false);
        $this->assertEquals('Foo', $foo->parse('FoO'));
        $this->assertFalse($foo->caseSensitive());

        $foo = S::constant('Foo', false);
        $this->assertEquals('Foo', $foo->parse('FoO'));
        $this->assertEquals('Foo', $foo->parse('foo'));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\Exception
     */
    public function test_constructor_wrong_argument() {
        new ConstantSyntax(123); // value should be a string !
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_wrong_case() {
        $foo = S::constant('foo');
        $foo->parse('Foo'); // it's case sensitive
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_wrong_value() {
        $foo = S::constant('foo');
        $foo->parse('bar');
    }

    public function test_dump() {
        $foo = S::constant('Foo');
        $this->assertEquals('Foo', $foo->dump('Foo'));

        $foo = S::constant('Foo', false);
        $this->assertEquals('Foo', $foo->dump('FoO'));
        $this->assertEquals('Foo', $foo->dump('foo'));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_wrong_value() {
        $syntax = S::constant('Foo');
        $this->assertFalse($syntax->canDump('bar'));
        $syntax->dump('bar');
    }

}
