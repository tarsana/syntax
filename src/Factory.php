<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\ConstantSyntax;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\SyntaxSyntax;

class Factory {

    public static function string($default = null, $description = '')
    {
        return new StringSyntax($default, $description);
    }

    public static function boolean($default = null, $description = '')
    {
        return new BooleanSyntax($default, $description);
    }

    public static function number($default = null, $description = '')
    {
        return new NumberSyntax($default, $description);
    }

    public static function arr($syntax = null, $separator = null, $default = null, $description = '')
    {
        return new ArraySyntax($syntax, $separator, $default, $description);
    }

    public static function obj($fields = [], $separator = null, $default = null, $description = '')
    {
        return new ObjectSyntax($fields, $separator, $default, $description);
    }

    public static function syntax($default = null, $description = '')
    {
        return new SyntaxSyntax($default, $description);
    }

    public static function fromString($text)
    {
        return (new SyntaxSyntax)->parse($text);
    }

    public static function constant($value, $caseSensitive = true, $description = null)
    {
        return new ConstantSyntax($value, $caseSensitive, $description);
    }
}
