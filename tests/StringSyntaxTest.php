<?php

use Tarsana\Syntax\Factory as S;

class StringSyntaxTest extends PHPUnit_Framework_TestCase {
    
    public function testParse() {
        $syntax = S::string();
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->parse($input));
    }

    public function testParseWithDefault() {
        $syntax = S::string('default value');
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->parse($input));

        $this->assertEquals('default value', $syntax->parse(''));
    }

    public function testParseWithDefaultAndDescription() {
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
    public function testParseEmptyString() {
        S::string()->parse('');
    }

    public function testDump() {
        $syntax = S::string();
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->dump($input));
    }

}
