<?php

namespace Tarsana\Syntax\UnitTests;

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\OptionalSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\SyntaxSyntax;

class FactoryTest extends TestCase
{
    public function test_string(): void
    {
        $syntax = S::string();
        $this->assertTrue($syntax instanceof StringSyntax);
    }

    public function test_boolean(): void
    {
        $syntax = S::boolean();
        $this->assertTrue($syntax instanceof BooleanSyntax);
    }

    public function test_number(): void
    {
        $syntax = S::number();
        $this->assertTrue($syntax instanceof NumberSyntax);
    }

    public function test_array(): void
    {
        $syntax = S::array();
        $this->assertTrue($syntax instanceof ArraySyntax);
        $this->assertTrue($syntax->syntax() instanceof StringSyntax);
        $this->assertEqualsCompat(ArraySyntax::DEFAULT_SEPARATOR, $syntax->separator());

        $syntax = S::array(S::number());
        $this->assertTrue($syntax instanceof ArraySyntax);
        $this->assertTrue($syntax->syntax() instanceof NumberSyntax);
        $this->assertEqualsCompat(ArraySyntax::DEFAULT_SEPARATOR, $syntax->separator());

        $syntax = S::array(S::number(), '|');
        $this->assertTrue($syntax instanceof ArraySyntax);
        $this->assertTrue($syntax->syntax() instanceof NumberSyntax);
        $this->assertEqualsCompat('|', $syntax->separator());
    }

    public function test_object(): void
    {
        $syntax = S::object(['name' => S::string()]);
        $this->assertTrue($syntax instanceof ObjectSyntax);
        $this->assertTrue($syntax->field('name') instanceof StringSyntax);
        $this->assertEqualsCompat(ObjectSyntax::DEFAULT_SEPARATOR, $syntax->separator());
    }

    public function test_optional(): void
    {
        $syntax = S::optional(StringSyntax::instance(), 'None');
        $this->assertTrue($syntax instanceof OptionalSyntax);
        $this->assertTrue($syntax->syntax() instanceof StringSyntax);
        $this->assertEqualsCompat('None', $syntax->getDefault());
    }

    public function test_syntax(): void
    {
        $syntax = S::syntax();
        $this->assertTrue($syntax instanceof SyntaxSyntax);
    }
}
