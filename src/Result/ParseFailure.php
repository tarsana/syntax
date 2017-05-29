<?php namespace Tarsana\Syntax\Result;

class ParseFailure extends Failure {

    protected $position;

    public function __construct(Syntax $syntax, mixed $input, string $error, int $position)
    {
        parent::__construct($syntax, $input, $error);
        $this->position = $position;
    }

    public function position() : int
    {
        return $this->position;
    }

    public function __toString() : string {
        return "Parse Failed: Unable to parse '{$this->input()}' as {$this->syntax()}; {$this->error()} at position {$this->position()}";
    }

}
