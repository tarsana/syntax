<?php

namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\NumberSyntax;

class OptionalSyntaxTest extends TestCase
{
    public function test_getters_and_setters(): void
    {
        $syntax = S::optional(S::string(), 'None')
            ->setDefault('...')
            ->syntax(S::number());

        $this->assertEqualsCompat('...', $syntax->getDefault());
        $this->assertTrue($syntax->syntax() instanceof NumberSyntax);
    }

    public function test_parse(): void
    {
        $this->assertParse(
            S::optional(S::number(), 15),
            [
            ['input' => '12', 'result' => 12],
            ['input' => 't56', 'result' => 15]
            ]
        );
    }

    public function test_dump(): void
    {
        $this->assertDump(
            S::optional(S::number(), 15),
            [
            ['input' => 12, 'result' => '12'],
            ['input' => 'nan', 'errors' => [
                'Error while dumping some input as Number: ' . NumberSyntax::ERROR
            ]],
            ]
        );
    }
}
