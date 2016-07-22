<?php

use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\Factory as S;

class ArraySyntaxTest extends PHPUnit_Framework_TestCase {

    public function test_getters_and_setters()
    {
        $syntax = S::arr()
            ->separator('/')
            ->itemSyntax(S::number());

        $this->assertEquals('/', $syntax->separator());
        $this->assertTrue($syntax->itemSyntax() instanceof NumberSyntax);
    }

    public function test_parse() {
        $syntax = S::arr();

        $this->assertEquals(['foo', 'bar', 'baz'], $syntax->parse('foo,bar,baz'));
        $this->assertEquals(['foo:bar:baz'], $syntax->parse('foo:bar:baz'));
    }

    public function test_parse_with_separator() {
        $syntax = S::arr(S::string(), ':');
        $this->assertEquals(['foo', 'bar', 'baz'], $syntax->parse('foo:bar:baz'));
    }

    public function test_parse_list_of_numbers() {
        $syntax = S::arr(S::number());
        $this->assertEquals([1, 2.2, 0], $syntax->parse('1,2.2,0'));

        $syntax = S::arr(S::number(), '|');
        $this->assertEquals([1, 2.2, 0], $syntax->parse('1|2.2|0'));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_with_wrong_item() {
        $syntax = S::arr(S::number());
        $syntax->parse('23,43,wrong,7');
    }

    public function test_dump() {
        $syntax = S::arr();
        $this->assertEquals('foo,bar,baz', $syntax->dump(['foo', 'bar', 'baz']));

        $syntax = S::arr(S::string(), ':');
        $this->assertEquals('foo,bar:baz', $syntax->dump(['foo,bar', 'baz']));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_wrong_array() {
        $syntax = S::arr();
        $syntax->dump('nan');
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_wrong_item() {
        $syntax = S::arr(S::number());
        $this->assertFalse($syntax->canDump([12, 32, 'wrong', 87]));
        $syntax->dump([12, 32, 'wrong', 87]);
    }

}
