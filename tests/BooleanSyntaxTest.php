<?php

namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\UnitTests\TestCase;

class BooleanSyntaxTest extends TestCase
{
    public function test_parse(): void
    {
        $this->assertParse(
            S::boolean(),
            [
            ['input' => ['true', 'yes', 'TRue', 'yeS', 'Y'], 'result' => true],
            ['input' => ['false', 'no', 'No', 'N'], 'result' => false],
            ['input'  => 'wrong boolean', 'errors' => [
                "Error while parsing 'wrong boolean' as Boolean at character 0: " . BooleanSyntax::PARSE_ERROR
            ]]
            ]
        );
    }

    public function test_dump(): void
    {
        $this->assertDump(
            S::boolean(),
            [
            ['input'  => true, 'result' => 'true'],
            ['input'   => false, 'result' => 'false'],
            ['input'   => [15, 'string'], 'errors'  => [
                'Error while dumping some input as Boolean: ' . BooleanSyntax::DUMP_ERROR
            ]]
            ]
        );
    }
}
