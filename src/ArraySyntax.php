<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Syntax;

/**
 * Represents an array of values with the same syntax.
 */
class ArraySyntax extends Syntax {

    /**
     * The string that separates items of the array.
     *
     * @var string
     */
    protected $separator;

    /**
     * The syntax of each item of the array.
     *
     * @var \Tarsana\Syntax\Syntax
     */
    protected $itemSyntax;

    /**
     * Creates a new instance of ArraySyntax.
     *
     * @param \Tarsana\Syntax\Syntax $syntax The syntax of each item of the array.
     * @param string $separator The string that separates items of the array.
     * @param string $default The default value.
     */
    public function __construct(Syntax $syntax = null, $separator = null, $default = null, $description = '')
    {
        if($syntax === null)
            $syntax = Factory::string();
        if ($separator === null || $separator == '')
            $separator = ',';

        $this->itemSyntax = $syntax;
        $this->separator = $separator;

        parent::__construct($default, $description);
    }

    /**
     * Item syntax getter and setter.
     *
     * @param  \Tarsana\Syntax\Syntax $value
     * @return mixed
     */
    public function itemSyntax(Syntax $value = null)
    {
        if (null === $value) {
            return $this->itemSyntax;
        }
        $this->itemSyntax = $value;
        return $this;
    }

    /**
     * Separator getter and setter.
     *
     * @param  string $value
     * @return mixed
     */
    public function separator($value = null)
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
    public function __toString()
    {
        return "array of ({$this->itemSyntax}) separated by '{$this->separator}'";
    }

    /**
     * Checks if the provided string can be
     * parsed as array based on the syntax.
     *
     * @param  string $text
     * @return array
      */
    public function checkParse($text)
    {
        $syntax = $this->itemSyntax;

        $errors = array_reduce(
            explode($this->separator, $text),
            function ($result, $item) use ($syntax) {
                return array_merge($result, $syntax->checkParse($item));
            },
            []);

        if (0 == count($errors))
            return [];

        return array_merge($errors, ["Unable to parse '{$text}' as '{$this}'"]);
    }

    /**
     * Transforms a string to array based on the syntax.
     *
     * @param  string $text the string to parse
     * @return mixed
     */
    protected function doParse($text)
    {
        $syntax = $this->itemSyntax;

        return array_map(function ($item) use ($syntax) {
            return $syntax->parse($item);
        }, explode($this->separator, $text));
    }

    /**
     * Checks if the provided argument can be dumped as array using the syntax.
     *
     * @param  mixed $value
     * @return array
     */
    public function checkDump($value)
    {
        if (! is_array($value))
            return ["Unable to dump '{$value}' as '{$this}'"];

        $syntax = $this->itemSyntax;
        $errors = array_reduce(
            $value,
            function ($result, $item) use ($syntax) {
                return array_merge($result, $syntax->checkDump($item));
            },
            []);

        if (0 == count($errors))
            return [];

        return array_merge($errors, ["Unable to dump as '{$this}'"]);
    }

    /**
     * Converts the given array to a string based on the syntax.
     *
     * @param  array $value the data to encode
     * @return string
     */
    protected function doDump($value)
    {
        $syntax = $this->itemSyntax;

        return implode($this->separator, array_map(function ($item) use ($syntax) {
            return $syntax->dump($item);
        }, $value));
    }

}
