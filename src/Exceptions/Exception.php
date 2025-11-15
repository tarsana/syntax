<?php

namespace Tarsana\Syntax\Exceptions;

use Tarsana\Syntax\Syntax;

class Exception extends \Exception
{
    protected $syntax;
    protected $extra;

    public function __construct(Syntax $syntax, protected $input, string $message, array $extra, protected $previous)
    {
        $this->syntax = $syntax;
        $this->message = $message;
        $this->extra = $extra;
    }

    public function syntax(): Syntax
    {
        return $this->syntax;
    }

    public function input()
    {
        return $this->input;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function previous()
    {
        return $this->previous;
    }

    public function extra()
    {
        return $this->extra;
    }
}
