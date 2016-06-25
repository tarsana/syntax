<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;

/**
 * Represents a boolean.
 */
class BooleanSyntax extends Syntax {

    /**
     * Returns the string representation of the syntax.
     * 
     * @return string
     */
    public function __toString()
    {
        return 'boolean';
    }

    /**
     * Checks if the provided string can be parsed as boolean.
     * 
     * @param  string $text
     * @return array
     */
    public function checkParse($text)
    {
        return in_array (
            strtolower(trim($text)), 
            ['true', 'yes', 'y', 'false', 'no', 'n']
        ) ? [] : ["Unable to parse '{$text}' as '{$this}'"];
    }

    /**
     * Transforms a string to boolean.
     * 
     * @param  string $text the string to parse
     * @return bool
     */
    protected function doParse($text)
    {
        if (in_array(strtolower(trim($text)), ['true', 'yes', 'y']))
            return true;
        return false;
    }

    /**
     * Checks if the provided argument can be dumped as boolean.
     * 
     * @param  mixed $value
     * @return array
     */
    public function checkDump($value)
    {
        return is_bool($value) ? [] : ["Unable to dump '{$value}' as '{$this}'"];
    }

    /**
     * Converts the given boolean to a string.
     * 
     * @param  bool $value the data to encode
     * @return string
     */
    protected function doDump($value)
    {
        return $value ? 'true' : 'false';
    }
    
}
