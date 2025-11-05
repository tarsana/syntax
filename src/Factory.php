<?php

namespace Tarsana\Syntax;

class Factory
{
    public static function string(): StringSyntax
    {
        return StringSyntax::instance();
    }

    public static function boolean(): BooleanSyntax
    {
        return BooleanSyntax::instance();
    }

    public static function number(): NumberSyntax
    {
        return NumberSyntax::instance();
    }

    public static function array(Syntax $syntax = null, string $separator = null): ArraySyntax
    {
        return new ArraySyntax($syntax, $separator);
    }

    public static function object(array $fields, string $separator = null): ObjectSyntax
    {
        return new ObjectSyntax($fields, $separator);
    }

    public static function optional(Syntax $syntax, $default): OptionalSyntax
    {
        return new OptionalSyntax($syntax, $default);
    }

    public static function syntax(): SyntaxSyntax
    {
        return SyntaxSyntax::instance();
    }
}
