<?php namespace Tarsana\Syntax\Result;

use Tarsana\Syntax\Syntax;

abstract class Failure extends Result {

    protected $syntax;

    protected $input;

    protected $error;

    public function __construct(Syntax $syntax, mixed $input, string $error)
    {
        $this->syntax = $syntax;
        $this->input = $input;
        $this->error = $error;
    }

    public function value()
    {
        throw new \Exception($this->__toString());
    }

    public function syntax() : Syntax
    {
        return $this->syntax;
    }

    public function input() : mixed
    {
        return $this->input;
    }

    public function error() : string
    {
        return $this->error;
    }

    abstract public function __toString() : string;
}
