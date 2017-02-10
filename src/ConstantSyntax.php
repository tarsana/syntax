<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\Exception;

/**
 * Represents a constant.
 */
class ConstantSyntax extends Syntax {

    /**
     * The constant value.
     *
     * @var string
     */
    protected $value;

    /**
     * Is the constant case sensitive.
     *
     * @var bool
     */
    protected $caseSensitive;

    /**
     * Create an instance of Constant Syntax.
     *
     * @param string $value
     */
    public function __construct($value, $caseSensitive = true, $description = null)
    {
        if (! is_string($value))
            throw new Exception(["The constant value should be a string !"]);
        $this->value = $value;
        $this->caseSensitive = $caseSensitive;
        parent::__construct(null, $description);
    }

    /**
     * caseSensitive getter/setter.
     *
     * @param  bool $value
     * @return self|bool
     */
    public function caseSensitive($value = null)
    {
        if (null === $value)
            return $this->caseSensitive;

        $this->caseSensitive = $value;
        return $this;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString()
    {
        return "constant({$this->value})";
    }

    /**
     * Checks if the provided string can be parsed as the constant.
     *
     * @param  string $text
     * @return array
     */
    public function checkParse($text)
    {
        if ($this->caseSensitive) {
            return $text === $this->value ? [] : ["Unable to parse '{$text}' as '{$this}'"];
        }

        return strtolower($text) === strtolower($this->value) ? [] : ["Unable to parse '{$text}' as '{$this}'"];
    }

    /**
     * Returns the constant value.
     *
     * @param  string $text
     * @return string
     */
    protected function doParse($text)
    {
        return $this->value;
    }

    /**
     * Checks if the provided argument can be dumped as the constant.
     *
     * @param  mixed $text
     * @return array
     */
    public function checkDump($text)
    {
        return $this->checkParse($text);
    }

    /**
     * Returns the constant value.
     *
     * @param  string $value
     * @return string
     */
    public function doDump($value)
    {
        return $this->value;
    }
}