<?php namespace Tarsana\Syntax\Result;

class Success extends Result {

    /**
     * The result of the parse operation.
     *
     * @var mixed
     */
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }
}
