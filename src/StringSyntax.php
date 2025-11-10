<?php

namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;

/**
 * Represents a string.
 */
class StringSyntax extends Syntax
{
    public const ERROR = 'Not a string value';
    public const NO_EMPTY = 'String should not be empty';

    protected static $instance = null;

    /**
     * Returns the StringSyntax instance.
     *
     * @return Tarsana\Syntax\StringSyntax
     */
    public static function instance(): StringSyntax
    {
        if (self::$instance === null) {
            self::$instance = new StringSyntax();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    /**
     * Returns the given string.
     *
     * @param  string $text
     * @return string
     */
    public function parse(string $text): string
    {
        if ($text === '') {
            throw new ParseException($this, $text, 0, self::NO_EMPTY);
        }
        return $text;
    }

    /**
     * Returns the given string.
     *
     * @param  string $value
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    public function dump($value): string
    {
        if (! is_string($value)) {
            throw new DumpException($this, $value, self::ERROR);
        }
        return $value;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'String';
    }
}
