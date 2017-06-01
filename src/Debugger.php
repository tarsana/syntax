<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;

class Debugger extends Syntax {

    protected static $level = 0;

    protected $syntax;

    public static function init()
    {
        self::$level = 0;
    }

    protected static function log(string $text)
    {
        echo str_repeat(' ', self::$level), $text, "\n";
    }

    public function __construct(Syntax $syntax)
    {
        if ($syntax instanceof ArraySyntax || $syntax instanceof OptionalSyntax) {
            $syntax->syntax(new Debugger($syntax->syntax()));
        }
        if ($syntax instanceof ObjectSyntax) {
            $syntax->fields(array_map(function($s) {
                return new Debugger($s);
            }, $syntax->fields()));
        }
        $this->syntax = $syntax;
    }

    public function syntax()
    {
        return $this->syntax;
    }

    public function parse(string $text)
    {
        self::log("Parsing '{$text}' as {$this->syntax}\n");
        self::$level ++;
        try {
            $result = $this->syntax->parse($text);
            self::$level --;
            if ($this->syntax instanceof OptionalSyntax) {
                self::log("Optional Success: " . $this->syntax->success());
            }
            self::log("Success: " . json_encode($result));
            return $result;
        } catch (ParseException $e) {
            self::$level --;
            self::log("Failure: " . $e->message());
            throw $e;
        }
    }

    public function dump($value) : string
    {
        self::log("Dumping '". json_encode($value) . "' as {$this->syntax}\n");
        self::$level ++;
        try {
            $result = $this->syntax->dump($value);
            self::$level --;
            self::log("Success: " . $result);
            return $result;
        } catch (DumpException $e) {
            self::$level --;
            self::log("Failure: " . $e->message());
            throw $e;
        }
    }

    public function __toString() : string
    {
        return "{$this->syntax}";
    }
}
