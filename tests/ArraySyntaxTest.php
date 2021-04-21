<?php namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax as S;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\StringSyntax;

class ArraySyntaxTest extends TestCase {
  public function test_getters_and_setters() {
    $syntax = S\arr()
      ->separator('/')
      ->syntax(S\number());

    $this->assertEquals('/', $syntax->separator());
    $this->assertTrue($syntax->syntax() instanceof NumberSyntax);
  }

  public function test_parse() {
    $syntax = new ArraySyntax();
    $this->assertParse($syntax, [
      ['input' => 'foo,BAR,baZ', 'result' => ['foo', 'BAR', 'baZ']],
      ['input' => 'foo:BAR,baZ', 'result' => ['foo:BAR', 'baZ']],
      ['input' => 'foo,"bar","baz,lorem",ipsum",yo"', 'result' => ['foo', 'bar', 'baz,lorem', 'ipsum",yo"']],
    ]);

    $syntax = new ArraySyntax(S\string(), ':');
    $this->assertParse($syntax, [
      ['input' => 'foo,BAR,baZ', 'result' => ['foo,BAR,baZ']],
      ['input' => 'foo:BAR,baZ', 'result' => ['foo', 'BAR,baZ']],
    ]);

    $syntax = new ArraySyntax(S\number(), '--');
    $this->assertParse($syntax, [['input' => '5---6.5--20.4', 'result' => [5, -6.5, 20.4]]]);

    $syntax = new ArraySyntax(S\number());
    $this->assertParse($syntax, [
      ['input' => '5,-6.5,20.4', 'result' => [5, -6.5, 20.4]],
      [
        'input' => '5,nan,20.4',
        'errors' => [
          "Error while parsing '5,nan,20.4' as Array of (Number) separated by ',' at character 2: Unable to parse the item 'nan'",
          "Error while parsing 'nan' as Number at character 0: " . NumberSyntax::ERROR,
        ],
      ],
    ]);
  }

  public function test_dump() {
    $syntax = new ArraySyntax();
    $this->assertDump($syntax, [
      ['input' => ['foo', 'bar', 'baz'], 'result' => 'foo,bar,baz'],
      [
        'input' => 'foo',
        'errors' => ["Error while dumping some input as Array of (String) separated by ',': " . ArraySyntax::ERROR],
      ],
      [
        'input' => ['foo', 15, 'lorem'],
        'errors' => [
          "Error while dumping some input as Array of (String) separated by ',': Unable to dump item at key 1",
          'Error while dumping some input as String: ' . StringSyntax::ERROR,
        ],
      ],
    ]);
    $syntax = new ArraySyntax(S\string(), ':');
    $this->assertDump($syntax, [['input' => ['foo', 'bar', 'baz'], 'result' => 'foo:bar:baz']]);
  }
}
