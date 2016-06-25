<?php namespace Tarsana\Syntax\Exceptions;

class Exception extends \Exception {
    public function __construct($errors) {
        parent::__construct(implode(PHP_EOL, $errors));
    }
}
