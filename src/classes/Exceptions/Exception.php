<?php namespace Tarsana\Syntax\Exceptions;

use Tarsana\Syntax\Syntax;

class Exception extends \Exception {
  protected $syntax;
  protected $input;
  protected $extra;
  protected $previous;

  public function __construct(Syntax $syntax, $input, string $message, array $extra, $previous) {
    $this->syntax = $syntax;
    $this->input = $input;
    $this->message = $message;
    $this->extra = $extra;
    $this->previous = $previous;
  }

  public function syntax(): Syntax {
    return $this->syntax;
  }

  public function input() {
    return $this->input;
  }

  public function message(): string {
    return $this->message;
  }

  public function previous() {
    return $this->previous;
  }

  public function extra() {
    return $this->extra;
  }
}
