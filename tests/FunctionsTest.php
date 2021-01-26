<?php namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax as S;
use Tarsana\Syntax\{ArraySyntax, BooleanSyntax, NumberSyntax, ObjectSyntax, OptionalSyntax, StringSyntax, SyntaxSyntax};

class FunctionsTest extends TestCase {
  public function test_string() {
    $syntax = S\string();
    $this->assertTrue($syntax instanceof StringSyntax);
  }

  public function test_boolean() {
    $syntax = S\boolean();
    $this->assertTrue($syntax instanceof BooleanSyntax);
  }

  public function test_number() {
    $syntax = S\number();
    $this->assertTrue($syntax instanceof NumberSyntax);
  }

  public function test_array() {
    $syntax = S\arr();
    $this->assertTrue($syntax instanceof ArraySyntax);
    $this->assertTrue($syntax->syntax() instanceof StringSyntax);
    $this->assertEquals(ArraySyntax::DEFAULT_SEPARATOR, $syntax->separator());

    $syntax = S\arr(S\number());
    $this->assertTrue($syntax instanceof ArraySyntax);
    $this->assertTrue($syntax->syntax() instanceof NumberSyntax);
    $this->assertEquals(ArraySyntax::DEFAULT_SEPARATOR, $syntax->separator());

    $syntax = S\arr(S\number(), '|');
    $this->assertTrue($syntax instanceof ArraySyntax);
    $this->assertTrue($syntax->syntax() instanceof NumberSyntax);
    $this->assertEquals('|', $syntax->separator());
  }

  public function test_object() {
    $syntax = S\object(['name' => S\string()]);
    $this->assertTrue($syntax instanceof ObjectSyntax);
    $this->assertTrue($syntax->field('name') instanceof StringSyntax);
    $this->assertEquals(ObjectSyntax::DEFAULT_SEPARATOR, $syntax->separator());
  }

  public function test_optional() {
    $syntax = S\optional(StringSyntax::instance(), 'None');
    $this->assertTrue($syntax instanceof OptionalSyntax);
    $this->assertTrue($syntax->syntax() instanceof StringSyntax);
    $this->assertEquals('None', $syntax->getDefault());
  }

  public function test_syntax() {
    $syntax = S\syntax();
    $this->assertTrue($syntax instanceof SyntaxSyntax);
  }
}
