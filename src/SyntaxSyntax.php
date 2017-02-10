<?php namespace Tarsana\Syntax;

use Tarsana\Functional as F;
use Tarsana\Syntax\Factory as S;

/**
 * This syntax generates a Syntax from a string.
 * It supports the following Synatxes:
 *     - StringSyntax   [a-zA-Z_-]*
 *     - NumberSyntax   #string
 *     - BooleanSyntax  string?
 *     - ArraySyntax    type[separator]
 *     - ObjectSyntax   string{separator,field1,field2,...}
 */
class SyntaxSyntax extends Syntax {

    /**
     * The default array separator.
     * @var string
     */
    protected $arraySeparator;

    /**
     * The default object separator.
     * @var string
     */
    protected $objectSeparator;

    /**
     * The object fields separator.
     * @var string
     */
    protected $fieldsSeparator;

    /**
     * Creates a new SyntaxSyntax instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->arraySeparator(',')
            ->objectSeparator(':')
            ->fieldsSeparator(',');
    }

    /**
     * arraySeparator getter and setter.
     *
     * @param  string
     * @return mixed
     */
    public function arraySeparator($value = null)
    {
        if (null === $value) {
            return $this->arraySeparator;
        }
        $this->arraySeparator = $value;
        return $this;
    }

    /**
     * objectSeparator getter and setter.
     *
     * @param  string
     * @return mixed
     */
    public function objectSeparator($value = null)
    {
        if (null === $value) {
            return $this->objectSeparator;
        }
        $this->objectSeparator = $value;
        return $this;
    }

    /**
     * fieldsSeparator getter and setter.
     *
     * @param  string
     * @return mixed
     */
    public function fieldsSeparator($value = null)
    {
        if (null === $value || $value == '') {
            return $this->fieldsSeparator;
        }
        $this->fieldsSeparator = F\head($value);
        return $this;
    }

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
        $txt = $text;
        if(F\head($txt) == '[' && F\last($txt) == ']')
            $txt = F\init(F\tail($txt));
        if($this->isString($txt)
            || $this->isNumber($txt)
            || $this->isBoolean($txt)
            || $this->isArray($txt)
            || $this->isObject($txt))
            return [];
        return ["Unable to parse '{$text}' as syntax"];
    }

    protected function isString ($text)
    {
        return F\test('/^[a-zA-Z-_]*$/', $text);
    }

    protected function parseString ($text)
    {
        $default = null;
        if(F\head($text) == '[' && F\last($text) == ']') {
            $text = F\tail(F\init($text));
            $default = '';
        }
        return S::string($default, $text);
    }

    protected function isNumber ($text)
    {
        return F\head($text) == '#' && $this->isString(F\tail($text));
    }

    protected function parseNumber ($text)
    {
        $default = null;
        if(F\head($text) == '[' && F\last($text) == ']') {
            $text = F\init(F\tail($text));
            $default = '';
        }
        return S::number($default, F\tail($text));
    }

    protected function isBoolean ($text)
    {
        return F\last($text) == '?' && $this->isString(F\init($text));
    }

    protected function parseBoolean ($text)
    {
        $default = null;
        if(F\head($text) == '[' && F\last($text) == ']') {
            $text = F\tail(F\init(F\tail($text)));
            $default = $text;
        }
        return S::constant($text, $text);
    }

    protected function isArray ($text)
    {
        $results = [];
        $count = preg_match_all('/^(.*)\[([^[]*)\]$/', $text, $results);
        if ($count < 1)
            return false;
        return 0 == count($this->checkParse($results[1][0]));
    }

    protected function parseArray ($text)
    {
        $default = null;
        if(F\head($text) == '[' && F\last($text) == ']') {
            $text = F\init(F\tail($text));
            $default = '';
        }
        $results = [];
        $count = preg_match_all('/^(.*)\[([^[]*)\]$/', $text, $results);
        if ($count < 1)
            return null;
        $type = $this->doParse($results[1][0]);
        $separator = $results[2][0];
        if (empty($separator))
            $separator = $this->arraySeparator;
        return S::arr($type, $separator, $default, $type->description());
    }

    protected function isObject ($text)
    {
        $results = [];
        $count = preg_match_all('/^([a-zA-Z_-]*)\{([^'.$this->fieldsSeparator.'a-zA-Z0-9\[]+)?'.$this->fieldsSeparator.'?(.*)\}$/', $text, $results);
        if ($count < 1) {
            return false;
        }
        $fields = trim($results[3][0]);
        if ($fields === '') {
            return true;
        }
        $fields = F\chunks('(){}[]""', $this->fieldsSeparator, $results[3][0]);
        foreach ($fields as $field) {
            $field = trim($field);
            if(F\head($field) == '[' && F\last($field) == ']') {
                $field = F\init(F\tail(trim($field)));
            }
            if (! $this->canParse($field))
                return false;
        }
        return true;
    }

    protected function parseObject ($text)
    {
        $default = null;
        if(F\head($text) == '[' && F\last($text) == ']') {
            $text = F\init(F\tail($text));
            $default = '';
        }
        $results = [];
        $count = preg_match_all('/^([a-zA-Z_-]*)\{([^,a-zA-Z0-9\[]+)?,?(.*)\}$/', $text, $results);
        if ($count < 1)
            return null;

        $fields = [];
        if (trim($results[3][0]) != '') {
            $fields = F\chunks('(){}[]""', $this->fieldsSeparator, $results[3][0]);
            $fields = F\reduce(function($results, $item){
                $item = $this->doParse(trim($item));
                $results[$item->description()] = $item;
                return $results;
            }, [], $fields);
        }

        $separator = $results[2][0];
        if(empty($separator))
            $separator = $this->objectSeparator;

        return S::obj($fields, $separator, $default, $results[1][0]);
    }

    /**
     * Transforms a string to syntax.
     *
     * @param  string $text the string to parse
     * @return Tarsana\Syntax\Syntax
     */
    protected function doParse($text)
    {
        $txt = $text;
        if(F\head($text) == '[' && F\last($text) == ']') {
            $txt = F\init(F\tail($text));
        }
        if ($this->isObject($txt))
            return $this->parseObject($text);
        if ($this->isArray($txt))
            return $this->parseArray($text);
        if ($this->isNumber($txt))
            return $this->parseNumber($text);
        if ($this->isBoolean($txt))
            return $this->parseBoolean($text);
        if ($this->isString($txt))
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
        $result = '';

        if ($value instanceof StringSyntax)
            $result = F\regReplace('/[^a-zA-Z-_]+/', '', $value->description());
        else if ($value instanceof NumberSyntax)
            $result = '#' . F\regReplace('/[^a-zA-Z-_]+/', '', $value->description());
        else if ($value instanceof BooleanSyntax)
            $result = F\regReplace('/[^a-zA-Z-_]+/', '', $value->description()) . '?';
        else if ($value instanceof ArraySyntax) {
            $item = $value->itemSyntax();
            $oldDescription = $item->description();
            $text = $this->dump($item->description($value->description()));
            $item->description($oldDescription);
            $result = "{$text}[{$value->separator()}]";
        }
        else if ($value instanceof ObjectSyntax) {
            $fields = [];
            foreach ($value->fields() as $name => $item) {
                $oldDescription = $item->description();
                $item->description($name);
                $fields[] = $this->dump($item);
                $item->description($oldDescription);
            }
            $fields = F\join($this->fieldsSeparator, $fields);
            $name = F\regReplace('/[^a-zA-Z-_]+/', '', $value->description());
            $result = "{$name}{{$value->separator()}{$this->fieldsSeparator}{$fields}}";
        }

        if (! $value->isRequired())
            $result = "[{$result}]";

        return $result;
    }
}
