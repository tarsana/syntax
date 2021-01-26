<?php namespace Tarsana\Syntax\Exceptions;

use Tarsana\Syntax\Syntax;

class DumpException extends Exception {
  public function __construct(Syntax $syntax, $input, string $message, array $extra = [], $previous = null) {
    parent::__construct($syntax, $input, "Error while dumping some input as {$syntax}: {$message}", $extra, $previous);
  }
}
