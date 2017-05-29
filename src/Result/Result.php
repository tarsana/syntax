<?php namespace Tarsana\Syntax\Result;

abstract class Result {

    public function isSuccess() {
        return $this instanceof Success;
    }

    public function isFailure() {
        return $this instanceof Failure;
    }

    abstract public function value();

}
