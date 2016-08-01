<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;

/**
 * An abstract syntax. All syntaxes inherit from this class.
 */
abstract class Syntax {

    /**
     * The description.
     *
     * @var string
     */
    protected $description;

    /**
     * The default value.
     *
     * @var mixed
     */
    protected $default;

    /**
     * Creates an instance of the syntax.
     *
     * @param mixed $default
     */
    public function __construct($default = null, $description = '')
    {
        $this->default = $default;
        $this->description = $description;
    }

    /**
     * Default getter.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Description getter and setter.
     *
     * @param  string $value
     * @return mixed
     */
    public function description($value = null)
    {
        if (null === $value) {
            return $this->description;
        }
        $this->description = $value;
        return $this;
    }

    /**
     * Tells if the syntax is required (doesn't have default value).
     *
     * @return bool
     */
    public function isRequired()
    {
        return (null === $this->default);
    }

    /**
     * Tells if a text can be parsed using this syntax.
     *
     * @param  string $text
     * @return bool
     */
    public function canParse($text)
    {
        if (! $this->isRequired())
            return true;
        return 0 == count($this->checkParse($text));
    }

    /**
     * Tells if a value can be dumped using this syntax.
     *
     * @param  mixed $value
     * @return bool
     */
    public function canDump($value)
    {
        return 0 == count($this->checkDump($value));
    }

    /**
     * Checks and converts a string to data using the syntax.
     * if the string can't be parsed; the default value is
     * returned if defined or a ParseException is thrown.
     *
     * @param  string $text the string to parse
     * @return mixed
     *
     * @throws Tarsana\Syntax\Exceptions\ParseException
     */
    public function parse($text)
    {
        $errors = $this->checkParse($text);
        if(0 == count($errors))
            return $this->doParse($text);

        if (null !== $this->default)
            return $this->default;

        throw new ParseException($errors);
    }

    /**
     * Checks and converts the given parameter to a string based on the syntax,
     * or throws a DumpException if the value can't be dumped
     *
     * @param  mixed $value the data to encode
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    public function dump($value)
    {
        $errors = $this->checkDump($value);
        if(0 == count($errors))
            return $this->doDump($value);

        throw new DumpException($errors);
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    abstract public function __toString();

    /**
     * Checks if the provided string can be parsed using the
     * syntax and returns an array of parsing errors if any.
     *
     * @param  string $text
     * @return array
     */
    abstract public function checkParse($text);

    /**
     * Transforms a string to data based on the syntax.
     *
     * @param  string $text the string to parse
     * @return mixed
     */
    abstract protected function doParse($text);

    /**
     * Checks if the provided argument can be dumped using the
     * syntax, and returns an array of dumping errors if any.
     *
     * @param  mixed $value
     * @return array
     */
    abstract public function checkDump($value);

    /**
     * Converts the given parameter to a string based on the syntax.
     *
     * @param  mixed $value the data to encode
     * @return string
     */
    abstract protected function doDump($value);

}
