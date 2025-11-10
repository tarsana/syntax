<?php

namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\ParseException;
use Tarsana\Syntax\Exceptions\DumpException;

/**
 * Represents a boolean.
 */
class BooleanSyntax extends Syntax
{
    public const TRUE_VALUES  = ['true', 'yes', 'y'];
    public const FALSE_VALUES = ['false', 'no', 'n'];

    public const PARSE_ERROR = 'Boolean value should be one of "yes", "no", "y", "n", "true", "false"';
    public const DUMP_ERROR  = 'Not a boolean';

    protected static $instance = null;

    /**
     * Returns the BooleanSyntax instance.
     *
     * @return Tarsana\Syntax\BooleanSyntax
     */
    public static function instance(): BooleanSyntax
    {
        if (self::$instance === null) {
            self::$instance = new BooleanSyntax();
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
     * @return bool
     *
     * @throws Tarsana\Syntax\Exceptions\ParseException
     */
    public function parse(string $text): bool
    {
        $lower = strtolower($text);
        if (! in_array($lower, array_merge(self::TRUE_VALUES, self::FALSE_VALUES))) {
            throw new ParseException($this, $text, 0, self::PARSE_ERROR);
        }
        return in_array($lower, self::TRUE_VALUES);
    }

    /**
     * Dumps the `$value` and returns "true" or "false".
     *
     * @param  bool $value
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    public function dump($value): string
    {
        if (! is_bool($value)) {
            throw new DumpException($this, $value, self::DUMP_ERROR);
        }

        return $value ? 'true' : 'false';
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'Boolean';
    }
}
