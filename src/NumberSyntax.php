<?php namespace Tarsana\Syntax;

/**
 * Represents a number.
 */
class NumberSyntax extends Syntax {

    /**
     * Returns the string representation of the syntax.
     * 
     * @return string
     */
    public function __toString()
    {
        return 'number';
    }

    /**
     * Checks if the provided string can be parsed as number.
     * 
     * @param  string $text
     * @return array
     */
    public function checkParse($text)
    {
        return is_numeric($text) ? [] : ["Unable to parse '{$text}' as '{$this}'"];
    }

    /**
     * Transforms a string to number.
     * 
     * @param  string $text the string to parse
     * @return int|float
     */
    protected function doParse($text)
    {
        return $text + 0;
    }

    /**
     * Checks if the provided argument can be dumped as a number.
     * 
     * @param  mixed $value
     * @return array
     */
    public function checkDump($value)
    {
        return is_numeric($value) ? [] : ["Unable to dump '{$value}' as '{$this}'"];
    }

    /**
     * Converts the given number to a numeric string.
     * 
     * @param  int|float $value the data to encode
     * @return string
     */
    protected function doDump($value)
    {
        return "{$value}";
    }

}
