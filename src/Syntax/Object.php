<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Syntax;
use Tarsana\Syntax\Exceptions\Exception;

/**
 * Represents an array of values with the same syntax.
 */
class ObjectSyntax extends Syntax {

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
     * @param string $default The default value.
     */
    public function __construct($fields = [], $separator = null, $default = null, $description = '')
    {
        if ($separator === null ||  $separator == '')
            $separator = ':';

        $this->fields = $fields;
        $this->separator = $separator;

        parent::__construct($default, $description);
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
     * @throws Tarsana\Syntax\Exceptions\Exception
     */
    public function field($name, Syntax $value = null)
    {
        if ($value === null) {
            if (! array_key_exists($name, $this->fields)) {
                throw new Exception(["No field with name {$name} is found"]);
            }
            return $this->fields[$name];
        }

        $this->fields[$name] = $value;
        return $this;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString()
    {
        $fields = [];
        foreach ($this->fields as $name => $syntax) {
            $fields[] = "{$name}: ({$syntax})";
        }
        $fields = implode(', ', $fields);
        return "object {{$fields}} separated by '{$this->separator}'";
    }

    /**
     * Checks if the provided string can be parsed as object using the fields syntaxes.
     *
     * @param  string $text
     * @return array
     */
    public function checkParse($text)
    {
        $errors = [];
        // If no fields to parse, just check if the text is empty
        if (empty($this->fields)) {
            return (trim($text) == '') ? [] : ['No fields but non empty string given'];
        }

        $items = explode($this->separator, $text);
        $fields = $this->fields;
        $names = array_keys($this->fields);
        $required = array_filter($names, function ($name) use ($fields) {
            return $fields[$name]->isRequired();
        });
        $itemsCount = count($items);
        $namesCount = count($names);
        $requiredCount = count($required);

        if ($itemsCount < $requiredCount) {
            $requiredString = implode(', ', $required);
            $itemsString = implode(', ', $items);
            $errors[] = "Some required fields are missing; required fields: {{$requiredString}}, items: {{$itemsString}}";
        } else if ($itemsCount > $namesCount) {
            $errors[] = "Too much items; {$namesCount} fields but got {$itemsCount} items !";
        } else {
            $itemIndex = 0;
            $nameIndex = 0;
            while ($nameIndex < $namesCount && $itemIndex < $itemsCount) {
                $err = $this->fields[$names[$nameIndex]]->checkParse($items[$itemIndex]);
                if (0 == count($err)) { // no error, move on to the next item
                    ++ $itemIndex;
                } else { // this item does not correspond to this field
                    if ($this->fields[$names[$nameIndex]]->isRequired()) {
                        // Ooops, the field is required !
                        $errors = array_merge($err, ["Unable to parse the required field '{$names[$nameIndex]}' !"]);
                        break;
                    }
                    // else: fine it's an optional field, we will match the same item with the next field.
                }
                ++ $nameIndex;
            }
            // No more items, so it remaining fields, they should be optional !
            while ($nameIndex < $namesCount) {
                if ($this->fields[$names[$nameIndex]]->isRequired()) {
                    $errors[] = "Missing required field '{$names[$nameIndex]}'";
                    break;
                }
                ++ $nameIndex;
            }

        }

        return $errors;
    }

    /**
     * Transforms a string to object based on the fields syntaxes.
     *
     * @param  string $text the string to parse
     * @return mixed
     */
    protected function doParse($text)
    {
        $result = new \stdClass;
        $items = explode($this->separator, $text);
        $index = 0;
        foreach ($this->fields as $name => $syntax) {
            if(isset($items[$index]) && 0 == count($syntax->checkParse($items[$index]))) {
                $result->{$name} = $syntax->doParse($items[$index]);
                ++ $index;
            } else {
                $result->{$name} = $syntax->getDefault();
            }
        }
        return $result;
    }

    /**
     * Checks if the provided argument can be dumped using the syntax.
     *
     * @param  mixed $value
     * @return array
     */
    public function checkDump($value)
    {
        $value = (array) $value;
        $errors = [];

        foreach ($this->fields as $name => $syntax) {
            if (!isset($value[$name])) {
                $errors = ["Missing field '{$name}'"];
                break;
            }
            $err = $syntax->checkDump($value[$name]);
            if (0 < count($err)) {
                $errors = array_merge($err, ["Unable to dump field '{$name}'"]);
                break;
            }
        }

        return $errors;
    }

    /**
     * Converts the given parameter to a string based on the fields syntaxes.
     *
     * @param  mixed $value the data to encode
     * @return string
     */
    protected function doDump($value)
    {
        $value = (array) $value;
        $fields = [];
        foreach ($this->fields as $name => $syntax) {
            $fields[] = $syntax->doDump($value[$name]);
        }
        return implode($this->separator, $fields);
    }

}