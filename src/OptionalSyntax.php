<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\ParseException;
use Tarsana\Syntax\Syntax;

/**
 * Represents a syntax with a default value.
 */
class OptionalSyntax extends Syntax {

    /**
     * The syntax.
     *
     * @var Tarsana\Syntax\Syntax
     */
    protected $syntax;

    /**
     * The default value.
     *
     * @var mixed
     */
    protected $default;

    /**
     * Tells if the last parse operation
     * was successful using the syntax.
     *
     * @var bool
     */
    protected $success;

    /**
     * Creates a new instance of OptionalSyntax.
     *
     * @param Tarsana\Syntax\Syntax $syntax
     * @param mixed $default
     */
    public function __construct(Syntax $syntax, $default)
    {
        $this->syntax  = $syntax;
        $this->default = $default;
    }

    /**
     * Syntax getter and setter.
     *
     * @param  Tarsana\Syntax\Syntax $value
     * @return Tarsana\Syntax\Syntax
     */
    public function syntax(Syntax $value = null) : Syntax
    {
        if (null === $value) {
            return $this->syntax;
        }
        $this->syntax = $value;
        return $this;
    }

    /**
     * Default value getter.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Default value setter.
     *
     * @param  mixed $value
     * @return self
     */
    public function setDefault($value)
    {
        $this->default = $value;
        return $this;
    }

    /**
     * Tells if the last parse operation
     * was successful using the syntax.
     *
     * @return  bool
     */
    public function success() {
        return $this->success;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString() : string
    {
        return "Optional {$this->syntax}";
    }

    /**
     * Tries to parse the string using the syntax and return
     * the result. If the parse failed, `success` flag is
     * set to false and the default value is returned.
     *
     * @param  string $text the string to parse
     * @return mixed
     */
    public function parse(string $text)
    {
        try {
            $result = $this->syntax->parse($text);
            $this->success = true;
        } catch (ParseException $e) {
            $result = $this->default;
            $this->success = false;
        }

        return $result;
    }

    /**
     * Simply calls the `dump` method of the syntax.
     *
     * @param  mixed $value
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    public function dump($value) : string
    {
        return $this->syntax->dump($value);
    }

}
