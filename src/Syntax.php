<?php

namespace Tarsana\Syntax;

/**
 * All syntaxes should inherit from this class.
 */
abstract class Syntax implements \Stringable
{
    /**
     * Parses the `$text` and returns the
     * result or throws a `ParseException`.
     *
     * @param  string $text
     * @return mixed
     *
     * @throws Tarsana\Syntax\Exceptions\ParseException
     */
    abstract public function parse(string $text);

    /**
     * Dumps the `$value` and returns the
     * result or throws a `DumpException`.
     *
     * @param  mixed $value
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    abstract public function dump($value): string;

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    abstract public function __toString(): string;
}
