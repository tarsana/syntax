<?php

namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\NumberSyntax;

class NumberSyntaxTest extends TestCase
{
    public function test_parse()
    {
        $this->assertParse(
            S::number(),
            [
            ['input' => '12', 'result' => 12],
            ['input' => '0.12', 'result' => 0.12],
            ['input' => '-0.20', 'result' => -0.2],
            ['input' => 't56', 'errors' => [
                'Error while parsing \'t56\' as Number at character 0: ' . NumberSyntax::ERROR
            ]],
            ]
        );
    }

    public function test_dump()
    {
        $this->assertDump(
            S::number(),
            [
            ['input' => 12, 'result' => '12'],
            ['input' => 0.12, 'result' => '0.12'],
            ['input' => -0.2, 'result' => '-0.2'],
            ['input' => 'nan', 'errors' => [
                'Error while dumping some input as Number: ' . NumberSyntax::ERROR
            ]],
            ]
        );
    }
}
