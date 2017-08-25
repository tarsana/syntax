<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Debugger;
use Tarsana\Syntax\Exceptions\DumpException;
use Tarsana\Syntax\Exceptions\Exception;
use Tarsana\Syntax\Exceptions\ParseException;
use Tarsana\Syntax\OptionalSyntax;
use Tarsana\Syntax\Syntax;

/**
 * Represents an couple of values with differeny syntaxes.
 */
class ObjectSyntax extends Syntax {

    const DEFAULT_SEPARATOR = ':';

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
                if ($field == 'syntax' && method_exists($syntax, 'syntax')) {
                    $syntax = $syntax->syntax();
                } else if ($syntax instanceof ObjectSyntax && array_key_exists($field, $syntax->fields)) {
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
        $result = [];
        $itemsLeft = false;
        try {
            while ($itemIndex < $itemsCount && $nameIndex < $namesCount) {
                $syntax = $this->fields[$names[$nameIndex]];
                $result[$names[$nameIndex]] = $syntax->parse($items[$itemIndex]);
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
                $result[$names[$nameIndex]] = $syntax->parse('');
                $nameIndex ++;
            }
        } catch (ParseException $e) {
            if ($itemIndex < $itemsCount)
                $error = "Unable to parse the item '{$items[$itemIndex]}' for field '{$names[$nameIndex]}'";
            else
                $error = "No item left for field '{$names[$nameIndex]}'";
            throw new ParseException($this, $text, $index + $e->position(), $error, $e);
        }

        if ($itemsLeft)
            throw new ParseException($this, $text, $index - $separatorLength,
                "Additional items with no corresponding fields");

        return (object) $result;
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
            throw new DumpException($this, $value, "Unable to dump the field '{$current}'", $e);
        }

        if ($missingField)
            throw new DumpException($this, $value, "Missing field '{$name}'");

        return Text::join($result, $this->separator);
    }

}
