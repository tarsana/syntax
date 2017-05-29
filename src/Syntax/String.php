<?php namespace Tarsana\Syntax;

/**
 * Represents a string.
 */
class StringSyntax extends Syntax {

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString()
    {
        return 'string';
    }

    /**
     * Checks if the provided string can be parsed as string.
     *
     * @param  string $text
     * @return array
     */
    public function checkParse($text)
    {
        return is_string($text) && $text !== '' ? [] : ["Unable to parse '{$text}' as '{$this}'"];
    }

    /**
     * Returns the provided string.
     *
     * @param  string $text
     * @return string
     */
    protected function doParse($text)
    {
        return $text;
    }

    /**
     * Checks if the provided argument can be dumped as string.
     *
     * @param  mixed $text
     * @return array
     */
    public function checkDump($text)
    {
        return is_string($text) ? [] : ["Unable to dump '{$text}' as '{$this}'"];
    }

    /**
     * Returns the provided string.
     *
     * @param  string $value
     * @return string
     */
    public function doDump($value)
    {
        return $value;
    }
}
