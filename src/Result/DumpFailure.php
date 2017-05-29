<?php namespace Tarsana\Syntax\Result;

class DumpFailure extends Failure {
    public function __toString() : string {
        return "Dump Failed: Unable to dump the input as {$this->syntax()}; {$this->error}";
    }
}
