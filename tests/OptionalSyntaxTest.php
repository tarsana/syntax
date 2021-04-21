<?php namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax as S;
use Tarsana\Syntax\NumberSyntax;

class OptionalSyntaxTest extends TestCase {
  public function test_getters_and_setters() {
    $syntax = S\optional(S\string(), 'None')
      ->setDefault('...')
      ->syntax(S\number());

    $this->assertEquals('...', $syntax->getDefault());
    $this->assertTrue($syntax->syntax() instanceof NumberSyntax);
  }

  public function test_parse() {
    $this->assertParse(S\optional(S\number(), 15), [
      ['input' => '12', 'result' => 12],
      ['input' => 't56', 'result' => 15],
    ]);
  }

  public function test_dump() {
    $this->assertDump(S\optional(S\number(), 15), [
      ['input' => 12, 'result' => '12'],
      ['input' => 'nan', 'errors' => ['Error while dumping some input as Number: ' . NumberSyntax::ERROR]],
    ]);
  }
}
