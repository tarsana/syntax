<?php namespace Tarsana\Syntax;

use Tarsana\Functional as F;
use Tarsana\Syntax\Factory as S;

/**
 * This syntax generates a Syntax from a string.
 * It supports the following Synatxes:
 *     - StringSyntax   [a-zA-Z_-]+
 *     - NumberSyntax   #string
 *     - BooleanSyntax  string?
 *     - ArraySyntax    type[separator]
 *     - ObjectSyntax   string{separator,field1,field2,...}
 */
class SyntaxSyntax extends Syntax {

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString()
    {
        return 'syntax';
    }

    /**
     * Checks if the provided string can be parsed as syntax.
     *
     * @param  string $text
     * @return array
     */
    public function checkParse($text)
    {
        if($this->isString($text)
            || $this->isNumber($text)
            || $this->isBoolean($text)
            || $this->isArray($text)
            || $this->isObject($text))
            return [];
        return ["Unable to parse '{$text}' as syntax"];
    }

    protected function isString ($text)
    {
        return F\test('/^[a-zA-Z-_]*$/', $text);
    }

    protected function parseString ($text)
    {
        return S::string(null, $text);
    }

    protected function isNumber ($text)
    {
        return F\head($text) == '#' && $this->isString(F\tail($text));
    }

    protected function parseNumber ($text)
    {
        return S::number(null, F\tail($text));
    }

    protected function isBoolean ($text)
    {
        return F\last($text) == '?' && $this->isString(F\init($text));
    }

    protected function parseBoolean ($text)
    {
        return S::boolean(null, F\init($text));
    }

    protected function isArray ($text)
    {
        $results = [];
        $count = preg_match_all('/^([^\[]*)\[(.*)\]$/', $text, $results);
        if ($count < 1)
            return false;
        return 0 == count($this->checkParse($results[1][0]));
    }

    protected function parseArray ($text)
    {
        $results = [];
        $count = preg_match_all('/^([^\[]*)\[(.*)\]$/', $text, $results);
        if ($count < 1)
            return null;
        $type = $this->doParse($results[1][0]);
        $separator = $results[2][0];
        if (empty($separator))
            $separator = ',';
        return S::arr($type, $separator, null, $type->description());
    }

    protected function isObject ($text)
    {
        $results = [];
        $count = preg_match_all('/^([a-zA-Z_-]*)\{([^,]+),(.+)\}$/', $text, $results);
        if ($count < 1)
            return false;
        $fields = [];
        preg_match_all('/[^,]+/', $results[3][0], $fields);
        $fields = $fields[0];
        foreach ($fields as $field) {
            if (0 < count($this->checkParse(trim($field))))
                return false;
        }
        return true;
    }

    protected function parseObject ($text)
    {
        $results = [];
        $count = preg_match_all('/^([a-zA-Z_-]*)\{([^,]+),(.+)\}$/', $text, $results);
        if ($count < 1)
            return null;
        $fields = [];
        preg_match_all('/[^,]+/', $results[3][0], $fields);

        $fields = F\reduce(function($results, $item){
            $item = $this->doParse(trim($item));
            $results[$item->description()] = $item;
            return $results;
        }, [], $fields[0]);

        return S::obj($fields, $results[2][0], null, $results[1][0]);
    }

    /**
     * Transforms a string to syntax.
     *
     * @param  string $text the string to parse
     * @return Tarsana\Syntax\Syntax
     */
    protected function doParse($text)
    {
        if ($this->isObject($text))
            return $this->parseObject($text);
        if ($this->isArray($text))
            return $this->parseArray($text);
        if ($this->isNumber($text))
            return $this->parseNumber($text);
        if ($this->isBoolean($text))
            return $this->parseBoolean($text);
        if ($this->isString($text))
            return $this->parseString($text);
        return null;
    }

    /**
     * Checks if the provided argument can be dumped as syntax.
     *
     * @param  mixed $value
     * @return array
     */
    public function checkDump($value)
    {
        if ($value instanceof StringSyntax ||
            $value instanceof NumberSyntax ||
            $value instanceof BooleanSyntax ||
            $value instanceof ArraySyntax ||
            $value instanceof ObjectSyntax
        )
            return [];
        return ["Unable to dump '{$value}' as syntax"];
    }

    /**
     * Converts the given syntax to a string.
     *
     * @param  Tarsana\Syntax\Syntax $value the data to encode
     * @return string
     */
    protected function doDump($value)
    {
        if ($value instanceof StringSyntax)
            return F\regReplace('/[^a-zA-Z-_]+/', '', $value->description());
        if ($value instanceof NumberSyntax)
            return '#' . F\regReplace('/[^a-zA-Z-_]+/', '', $value->description());
        if ($value instanceof BooleanSyntax)
            return F\regReplace('/[^a-zA-Z-_]+/', '', $value->description()) . '?';
        if ($value instanceof ArraySyntax) {
            $item = $value->itemSyntax();
            $oldDescription = $item->description();
            $text = $this->dump($item->description($value->description()));
            $item->description($oldDescription);
            return "{$text}[{$value->separator()}]";
        }
        if ($value instanceof ObjectSyntax) {
            $fields = [];
            foreach ($value->fields() as $name => $item) {
                $oldDescription = $item->description();
                $item->description($name);
                $fields[] = $this->dump($item);
                $item->description($oldDescription);
            }
            $fields = F\join(',', $fields);
            $name = F\regReplace('/[^a-zA-Z-_]+/', '', $value->description());
            return "{$name}{{$value->separator()},{$fields}}";
        }
    }
}
