<?php

use Tarsana\Syntax\Factory as S;

class NumberSyntaxTest extends PHPUnit_Framework_TestCase {

    public function test_parse() {
        $syntax = S::number();

        $this->assertTrue(12 === $syntax->parse('12'));
        $this->assertTrue(0.12 === $syntax->parse('0.12'));
        $this->assertTrue(-0.2 === $syntax->parse('-0.20'));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_wrong_number() {
        $syntax = S::number();
        $syntax->parse('t56');
    }

    public function test_dump() {
        $syntax = S::number();
        $this->assertTrue('12' === $syntax->dump(12));
        $this->assertTrue('0.23' === $syntax->dump(0.23));
        $this->assertTrue('-2' === $syntax->dump(-2));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_wrong_boolean() {
        $syntax = S::number();
        $this->assertFalse($syntax->canDump('nan'));
        $syntax->dump('nan');
    }

}
