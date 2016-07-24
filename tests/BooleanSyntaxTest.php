<?php

use Tarsana\Syntax\Factory as S;

class BooleanSyntaxTest extends PHPUnit_Framework_TestCase {

    public function test_parse() {
        $syntax = S::boolean();
        $trueInputs = ['true', 'yes', 'TRue', 'yeS', 'Y'];
        $falseInputs = ['false', 'no', 'No', 'N'];
        foreach ($trueInputs as $input) {
            $this->assertTrue($syntax->parse($input));
        }
        foreach ($falseInputs as $input) {
            $this->assertFalse($syntax->parse($input));
        }
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\ParseException
     */
    public function test_parse_wrong_boolean() {
        $syntax = S::boolean();
        $syntax->parse('wrong boolean');
    }

    public function test_dump() {
        $syntax = S::boolean();
        $this->assertEquals('true', $syntax->dump(true));
        $this->assertEquals('false', $syntax->dump(false));
    }

    /**
     * @expectedException Tarsana\Syntax\Exceptions\DumpException
     */
    public function test_dump_wrong_boolean() {
        $syntax = S::boolean();
        $this->assertFalse($syntax->canDump('boolean'));
        $syntax->dump('boolean');
    }

}
