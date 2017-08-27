<?php namespace Tarsana\Syntax\Exceptions;

use Tarsana\Syntax\Syntax;

class ParseException extends Exception {

    protected $position;

    public function __construct(Syntax $syntax, string $input, int $position, string $message, array $extra = [], $previous = null)
    {
        parent::__construct(
            $syntax,
            $input,
            "Error while parsing '{$input}' as {$syntax} at character {$position}: {$message}",
            $extra,
            $previous
        );
        $this->position = $position;
    }

    public function position() : int
    {
        return $this->position;
    }

}
