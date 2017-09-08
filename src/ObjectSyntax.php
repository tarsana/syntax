<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\Exception;
use Tarsana\Syntax\Exceptions\ParseException;
use Tarsana\Syntax\OptionalSyntax;
use Tarsana\Syntax\Syntax;

/**
 * Represents an couple of values with different syntaxes.
 */
class ObjectSyntax extends Syntax {

    const DEFAULT_SEPARATOR = ':';
    const MISSING_FIELD     = 'missing-field';
    const INVALID_FIELD     = 'invalid-field';
    const ADDITIONAL_ITEMS  = 'additional-items';

    /**
     * The string that separates items of the object.
     *
     * @var string
     */
    protected $separator;

    /**
     * Associative array specifying the fields of the object.
     *
     * @var array
     */
    protected $fields;

    /**
     * Associative array specifying the parsed values of
     * different fields, with associated errors if any.
     * ['field' => {value: *, error: string|null}, ...]
     *
     * @var array
     */
    protected $values;

    /**
     * Creates a new instance of ObjectSyntax.
     *
     * @param array $fields Associative array specifying the fields of the object.
     * @param string $separator The string that separates items of the array.
     */
    public function __construct(array $fields, string $separator = null)
    {
        if (empty($fields))
            throw new \InvalidArgumentException('ObjectSyntax should have at least one field');
        if ($separator === null ||  $separator == '')
            $separator = self::DEFAULT_SEPARATOR;

        $this->fields = $fields;
        $this->separator = $separator;
        $this->values = [];
    }

    /**
     * Separator getter and setter.
     *
     * @param  string $value
     * @return mixed
     */
    public function separator($value = null)
    {
        if (null === $value) {
            return $this->separator;
        }
        $this->separator = $value;
        return $this;
    }

    /**
     * Fields getter and setter.
     *
     * @param  array $value
     * @return mixed
     */
    public function fields($value = null)
    {
        if (null === $value) {
            return $this->fields;
        }
        $this->fields = $value;
        return $this;
    }

    /**
     * Setter and getter of a specific field.
     *
     * @param  string $name
     * @param  Tarsana\Syntax\Syntax|null $value
     * @return Tarsana\Syntax\Syntax|self
     *
     * @throws InvalidArgumentException
     */
    public function field(string $name, Syntax $value = null)
    {
        if ($value === null) {
            $names = explode('.', $name);
            $syntax = $this;
            foreach ($names as $field) {
                while (method_exists($syntax, 'syntax')) {
                    $syntax = $syntax->syntax();
                }
                if ($syntax instanceof ObjectSyntax && array_key_exists($field, $syntax->fields)) {
                    $syntax = $syntax->fields[$field];
                } else {
                    throw new \InvalidArgumentException("field '{$name}' not found");
                }
            }
            return $syntax;
        }

        $this->fields[$name] = $value;
        return $this;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString() : string
    {
        $fields = [];
        foreach ($this->fields as $name => $syntax) {
            $fields[] = "{$name}: {$syntax}";
        }
        $fields = implode(', ', $fields);
        return "Object {{$fields}} separated by '{$this->separator}'";
    }

    /**
     * values getter.
     *
     * @param  array
     * @return mixed
     */
    public function values()
    {
        return $this->values;
    }

    /**
     * Clears the parsed values.
     *
     * @return void
     */
    protected function clearValues()
    {
        $this->values = [];
        foreach ($this->fields as $name => $syntax) {
            $this->values[$name] = (object) [
                'value'   => null,
                'error'   => static::MISSING_FIELD
            ];
        }
    }

    /**
     * Transforms a string to an object based
     * on the fields or throws a ParseException.
     *
     * @param  string $text the string to parse
     * @return object
     *
     * @throws Tarsana\Syntax\Exceptions\ParseException
     */
    public function parse(string $text) : \stdClass
    {
        $items = Text::split($text, $this->separator);
        $itemsCount = count($items);
        $itemIndex = 0;
        $names = array_keys($this->fields);
        $namesCount = count($names);
        $nameIndex = 0;
        $index = 0;
        $separatorLength = strlen($this->separator);
        $itemsLeft = false;
        $this->clearValues();
        try {
            while ($itemIndex < $itemsCount && $nameIndex < $namesCount) {
                $syntax = $this->fields[$names[$nameIndex]];
                $this->values[$names[$nameIndex]]->value = $syntax->parse($items[$itemIndex]);
                $this->values[$names[$nameIndex]]->error = null;
                $index += strlen($items[$itemIndex]) + $separatorLength;
                $nameIndex ++;
                $itemIndex ++;
                if ($syntax instanceof OptionalSyntax && !$syntax->success()) {
                    $itemIndex --;
                }
            }
            // if items are left
            if ($itemIndex < $itemsCount)
                $itemsLeft = true;
            // if fields are left
            while ($nameIndex < $namesCount) {
                $syntax = $this->fields[$names[$nameIndex]];
                $this->values[$names[$nameIndex]]->value = $syntax->parse('');
                $this->values[$names[$nameIndex]]->error = null;
                $nameIndex ++;
            }
        } catch (ParseException $e) {
            if ($itemIndex < $itemsCount) {
                $this->values[$names[$nameIndex]]->error = static::INVALID_FIELD;
                $error = "Unable to parse the item '{$items[$itemIndex]}' for field '{$names[$nameIndex]}'";
                $extra = [
                    'type'  => 'invalid-field',
                    'field' => $names[$nameIndex],
                    'text'  => $items[$itemIndex]
                ];
            } else {
                $this->values[$names[$nameIndex]]->error = static::MISSING_FIELD;
                $error = "No item left for field '{$names[$nameIndex]}'";
                $extra = [
                    'type'  => 'missing-field',
                    'field' => $names[$nameIndex],
                    'position' => $e->position()
                ];
            }
            throw new ParseException($this, $text, $index + $e->position(), $error, $extra, $e);
        }

        if ($itemsLeft) {
            $extra = [
                'type'  => 'additional-items',
                'items' => array_slice($items, $itemIndex)
            ];
            throw new ParseException($this, $text, $index - $separatorLength,
                "Additional items with no corresponding fields", $extra);
        }

        return (object) array_map(function(\stdClass $field) {
            return $field->value;
        }, $this->values);
    }

    /**
     * Transforms an object to a string based
     * on the fields or throws a DumpException.
     *
     * @param  mixed $value
     * @return string
     *
     * @throws Tarsana\Syntax\Exceptions\DumpException
     */
    public function dump($value) : string
    {
        $value = (array) $value;
        $result = [];
        $current = '';
        $missingField = false;
        try {
            foreach ($this->fields as $name => $syntax) {
                $current = $name;
                if (!array_key_exists($name, $value)) {
                    $missingField = true;
                    break;
                }
                $result[] = $syntax->dump($value[$name]);
            }
        } catch (DumpException $e) {
            throw new DumpException($this, $value, "Unable to dump the field '{$current}'", [], $e);
        }

        if ($missingField)
            throw new DumpException($this, $value, "Missing field '{$name}'");

        return Text::join($result, $this->separator);
    }

}
