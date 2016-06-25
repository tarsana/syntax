<?php

use Tarsana\Syntax\Factory as S;

class StringSyntaxTest extends PHPUnit_Framework_TestCase {
    
    public function testParse() {
        $syntax = S::string();
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->parse($input));
    }

    public function testDump() {
        $syntax = S::string();
        $input = 'Lorem ?~\| ipsum/,: dolor .';
        $this->assertEquals($input, $syntax->dump($input));
    }

}
