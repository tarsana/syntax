<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\StringSyntax;
use Tarsana\Syntax\ArraySyntax;

class Factory {

    public static function string($default = null)
    {
        return new StringSyntax($default);
    }

    public static function boolean($default = null)
    {
        return new BooleanSyntax($default);
    }

    public static function number($default = null)
    {
        return new NumberSyntax($default);
    }
    
    public static function arr($syntax = null, $separator = null, $default = null)
    {
        return new ArraySyntax($syntax, $separator, $default);
    }
    
    public static function obj($separator = null, $default = null)
    {
        return new ObjectSyntax($separator, $default);
    }
}
