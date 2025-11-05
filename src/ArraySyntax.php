<?php

namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;
use Tarsana\Syntax\Syntax;

/**
 * Represents an array of values with the same syntax.
 */
class ArraySyntax extends Syntax
{
    const DEFAULT_SEPARATOR = ',';
    const ERROR = 'Not an array';

    /**
     * The string that separates items of the array.
     *
     * @var string
     */
    protected $separator;

    /**
     * The syntax of each item of the array.
     *
     * @var Tarsana\Syntax\Syntax
     */
    protected $syntax;

    /**
     * Creates a new instance of ArraySyntax.
     */
    public function __construct(Syntax $syntax = null, string $separator = null)
    {
        if ($syntax === null) {
            $syntax = Factory::string();
        }
        if ($separator === null || $separator == '') {
            $separator = self::DEFAULT_SEPARATOR;
        }

        $this->syntax = $syntax;
        $this->separator = $separator;
    }

    /**
     * Item syntax getter and setter.
     *
     * @param  Tarsana\Syntax\Syntax $value
     * @return Tarsana\Syntax\Syntax
     */
    public function syntax(Syntax $value = null): Syntax
    {
        if (null === $value) {
            return $this->syntax;
        }
        $this->syntax = $value;
        return $this;
    }

    /**
     * Separator getter and setter.
     *
     * @param  string $value
     * @return self|string
     */
    public function separator(string $value = null)
    {
        if (null === $value) {
            return $this->separator;
        }
        $this->separator = $value;
        return $this;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "Array of ({$this->syntax}) separated by '{$this->separator}'";
    }

    /**
     * Transforms a string to array based on
     * the syntax or throws a ParseException.
     *
     * @param  string $text the string to parse
     * @return array
     *
     * @throws Tarsana\Syntax\Exceptions\ParseException
     */
    public function parse(string $text): array
    {
        $index = 0;
        $items = Text::split($text, $this->separator);
        $array = [];
        try {
            foreach ($items as $item) {
                $array[] = $this->syntax->parse($item);
                $index += strlen((string) $item) + 1;
            }
        } catch (ParseException $e) {
            $extra = [
                'type' => 'invalid-item',
                'item' => $item,
                'position' => $e->position()
            ];
            throw new ParseException(
                $this,
                $text,
                $index + $e->position(),
                "Unable to parse the item '{$item}'",
                $extra,
                $e
            );
        }

        return $array;
    }

    /**
     * Converts the given array to a string based
     * on the syntax or throws a DumpException.
     *
     * @param  array $values the data to encode
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    public function dump($values): string
    {
        if (! is_array($values)) {
            throw new DumpException($this, $values, self::ERROR);
        }
        $items = [];
        $index = 0;
        try {
            foreach ($values as $key => $value) {
                $index = $key;
                $items[] = $this->syntax->dump($value);
            }
        } catch (DumpException $e) {
            throw new DumpException($this, $values, "Unable to dump item at key {$index}", [], $e);
        }

        return Text::join($items, $this->separator);
    }
}
