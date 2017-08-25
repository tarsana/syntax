<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\ParseException;
use Tarsana\Syntax\Factory as S;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\OptionalSyntax;
use Tarsana\Syntax\StringSyntax;

/**
 * This syntax generates a Syntax from a string.
 */
class SyntaxSyntax extends Syntax {

    protected static $instance = null;

    protected $objectSyntax;
    protected $optionalSyntax;
    protected $arraySyntax;

    public static function instance() : SyntaxSyntax
    {
        if (self::$instance === null)
            self::$instance = new SyntaxSyntax;
        return self::$instance;
    }


    private function __construct() {
        // (type:default)
        $this->optionalSyntax = S::object([
            'type'    => S::string(),
            'default' => S::string()
        ]);

        // [type|separator]
        $this->arraySyntax = S::object([
            'type' => S::optional(S::string(), 'string'),
            'separator' => S::optional(S::string(), ArraySyntax::DEFAULT_SEPARATOR)
        ])->separator('|');

        // {name:type, name:type, ...|separator}
        $this->objectSyntax = S::object([
            'fields' => S::array(S::object([
                'name' => S::string(),
                'type' => S::optional(S::string(), 'string')
            ])),
            'separator' => S::optional(S::string(), ObjectSyntax::DEFAULT_SEPARATOR)
        ])->separator('|');
    }

    public function __toString() : string
    {
        return 'Syntax';
    }

    public function parse(string $text) : Syntax
    {
        $text = trim($text);

        if ($text === '' || $text === 'string')
            return S::string();

        if ($text === 'number')
            return S::number();

        if ($text === 'boolean')
            return S::boolean();

        $length = strlen($text);
        if ($length >= 2) {
            $wrappers = substr($text, 0, 1) . substr($text, -1);
            $inner    = substr($text, 1, strlen($text) - 2);

            if ($wrappers == '[]')
                return $this->parseArray($inner);

            if ($wrappers == '()')
                return $this->parseOptional($inner);

            if ($wrappers == '{}')
                return $this->parseObject($inner);
        }

        throw new ParseException($this, $text, 0, "Invalid syntax");
    }

    public function decode(string $value)
    {
        return json_decode($value);
    }

    public function encode($value) : string
    {
        return json_encode($value);
    }

    protected function parseOptional(string $text) : OptionalSyntax
    {
        $optional = $this->optionalSyntax->parse($text);

        if ($optional->type == 'string')
            $optional->default = "\"{$optional->default}\"";

        return S::optional(
            $this->parse($optional->type),
            $this->decode($optional->default)
        );
    }

    protected function parseArray(string $text) : ArraySyntax
    {
        $array = $this->arraySyntax->parse($text);

        return S::array($this->parse($array->type), $array->separator);
    }

    protected function parseObject(string $text) : ObjectSyntax
    {
        $object = $this->objectSyntax->parse($text);

        $fields = [];
        foreach ($object->fields as $field) {
            $fields[trim($field->name)] = $this->parse($field->type);
        }

        return S::object($fields, $object->separator);
    }

    public function dump($value) : string
    {
        if (! ($value instanceof Syntax))
            throw new DumpException($this, $value, "Not a syntax");

        if ($value instanceof StringSyntax)
            return 'string';

        if ($value instanceof NumberSyntax)
            return 'number';

        if ($value instanceof BooleanSyntax)
            return 'boolean';

        if ($value instanceof ArraySyntax) {
            $array = [
                'type' => $this->dump($value->syntax()),
                'separator' => $value->separator()
            ];
            return '['.$this->arraySyntax->dump($array).']';
        }

        if ($value instanceof OptionalSyntax) {
            $optional = [
                'type' => $this->dump($value->syntax()),
                'default' => $this->encode($value->getDefault())
            ];
            return '('.$this->optionalSyntax->dump($optional).')';
        }

        if ($value instanceof ObjectSyntax) {
            $fields = [];
            foreach($value->fields() as $name => $syntax) {
                $fields[] = (object) [
                    'name' => $name,
                    'type' => $this->dump($syntax)
                ];
            }
            $object = ['fields' => $fields, 'separator' => $value->separator()];

            return '{'.$this->objectSyntax->dump($object).'}';
        }

        throw new DumpException($this, $value, "Unknown syntax");
    }
}
