<?php namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\StringSyntax;

class StringSyntaxTest extends TestCase {

    public function test_parse() {
        $this->assertParse(S::string(), [
            ['input' => 'Lorem ?~\| ipsum/,: dolor .', 'result' => 'Lorem ?~\| ipsum/,: dolor .'],
            ['input' => '', 'errors' => [
                "Error while parsing '' as String at character 0: " . StringSyntax::NO_EMPTY
            ]]
        ]);
    }

    public function test_dump() {
        $this->assertDump(S::string(), [
            ['input' => 'Lorem ?~\| ipsum/,: dolor .', 'result' => 'Lorem ?~\| ipsum/,: dolor .'],
            ['input' => '', 'result' => ''],
            ['input' => 15, 'errors' => [
                'Error while dumping some input as String: ' . StringSyntax::ERROR
            ]]
        ]);
    }

}
