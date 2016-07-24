<?php

use Tarsana\Syntax\Factory as S;

class StringSyntaxTest extends PHPUnit_Framework_TestCase {

    public function test_parse() {
        $syntax = S::string();
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->parse($input));
    }

    public function test_parse_with_default() {
        $syntax = S::string('default value');
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->parse($input));

        $this->assertEquals('default value', $syntax->parse(''));
    }

    public function test_parse_with_default_and_description() {
        $syntax = S::string('default value', 'demo');
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->parse($input));
        $this->assertEquals('default value', $syntax->parse(''));
        $this->assertEquals('demo', $syntax->description());
        $syntax->description('changed');
        $this->assertEquals('changed', $syntax->description());
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_empty_string() {
        S::string()->parse('');
    }

    public function test_dump() {
        $syntax = S::string();
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->dump($input));
    }

}
