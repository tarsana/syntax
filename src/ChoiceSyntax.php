<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Syntax;
use Tarsana\Functional as F;

/**
 * Represents a string which can be only one of specified values.
 */
class ChoiceSyntax extends Syntax {

    /**
     * The possible values.
     *
     * @var string
     */
    protected $values;

    /**
     * Creates a new instance of ChoiceSyntax.
     *
     * @param array  $values
     * @param string $default
     */
    public function __construct($values, $default = null, $description = '')
    {
        $this->values = $values;
        parent::__construct($default, $description);
    }

    /**
     * Values getter and setter.
     *
     * @param  string $value
     * @return mixed
     */
    public function values($value = null)
    {
        if (null === $value) {
            return $this->values;
        }
        $this->values = $value;
        return $this;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString()
    {
        $values = F\join('|', $this->values);
        return "one value of '{$values}'";
    }

    /**
     * Checks if the provided string can be
     * parsed as choice based on the values.
     *
     * @param  string $text
     * @return array
      */
    public function checkParse($text)
    {
        return (in_array($text, $this->values)) ?
            [] :
            ["Unable to parse '{$text}' as '{$this}'"];
    }

    /**
     * Returns the given string.
     *
     * @param  string $text
     * @return mixed
     */
    protected function doParse($text)
    {
        return $text;
    }

    /**
     * Checks if the provided value is among the choice possible values.
     *
     * @param  mixed $value
     * @return array
     */
    public function checkDump($value)
    {
        if (! is_string($value) || !in_array($value, $this->values))
            return ["Unable to dump '{$value}' as '{$this}'"];
        return [];
    }

    /**
     * Returns the given string.
     *
     * @param  array $value
     * @return string
     */
    protected function doDump($value)
    {
        return $value;
    }

}
