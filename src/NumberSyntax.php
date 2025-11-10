<?php

namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;

/**
 * Represents a string.
 */
class NumberSyntax extends Syntax
{
    public const ERROR = 'Not a numeric value';

    protected static $instance = null;

    /**
     * Returns the NumberSyntax instance.
     *
     * @return Tarsana\Syntax\NumberSyntax
     */
    public static function instance(): NumberSyntax
    {
        if (self::$instance === null) {
            self::$instance = new NumberSyntax();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    /**
     * Parses the `$text` and returns the
     * result or throws a `ParseException`.
     *
     * @param  string $text
     * @return float
     *
     * @throws Tarsana\Syntax\Exceptions\ParseException
     */
    public function parse(string $text)
    {
        if (! is_numeric($text)) {
            throw new ParseException($this, $text, 0, self::ERROR);
        }
        return $text + 0;
    }

    /**
     * Dumps the `$value` and returns the
     * result or throws a `DumpException`.
     *
     * @param  int|float $value
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    public function dump($value): string
    {
        if (! is_numeric($value)) {
            throw new DumpException($this, $value, self::ERROR);
        }

        return '' . $value;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'Number';
    }
}
