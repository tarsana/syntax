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
