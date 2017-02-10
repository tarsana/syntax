<?php namespace Tarsana\Syntax;

use Tarsana\Syntax\Exceptions\Exception;
use Tarsana\Syntax\Exceptions\ParseException;

/**
 * Represents one of a list of given syntaxes.
 */
class ChoiceSyntax extends Syntax {

    /**
     * Associative array specifying the possible
     * syntaxes, giving a name to each one.
     *
     * @var array
     */
    protected $options;

    /**
     * The selected option name.
     *
     * @var string
     */
    protected $selected;

    /**
     * Creates a new instance of ChoiceSyntax.
     *
     * @param array $fields
     * @param string $default
     * @param string $description
     */
    public function __construct($options = [], $default = null, $description = '')
    {
        $this->options = $options;
        $this->selected = null;
        parent::__construct($default, $description);
    }

    /**
     * options getter and setter.
     *
     * @param  array $value
     * @return mixed
     */
    public function options($value = null)
    {
        if (null === $value) {
            return $this->options;
        }
        $this->options = $value;
        return $this;
    }

    /**
     * Setter and getter of a specific option.
     *
     * @param  string $name
     * @param  Tarsana\Syntax\Syntax|null $value
     * @return Tarsana\Syntax\Syntax|self
     * @throws Tarsana\Syntax\Exceptions\Exception
     */
    public function option($name, Syntax $value = null)
    {
        if ($value === null) {
            if (! array_key_exists($name, $this->options)) {
                throw new Exception(["No option with name {$name} is found"]);
            }
            return $this->options[$name];
        }

        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Returns the string representation of the syntax.
     *
     * @return string
     */
    public function __toString()
    {
        $options = $this->optionsAsString();
        return "choice between {$options}";
    }

    /**
     * Returns a string representing the options corresponding to the given names.
     *
     * @param  array $names
     * @return string
     */
    protected function optionsAsString($names = null) {
        if (null === $names) {
            $names = array_keys($this->options);
        }
        $strings = [];
        foreach ($names as $name) {
            $strings[] = "{$name} ({$this->option($name)})";
        }
        return implode(', ', $strings);
    }

    /**
     * Checks if the provided string can be parsed as one of the options.
     *
     * @param  string $text
     * @return array
     */
    public function checkParse($text)
    {
        $passing = [];
        foreach ($this->options as $name => $syntax) {
            if ($syntax->canParse($text)) {
                $passing[] = $name;
            }
        }

        if (count($passing) == 0) {
            $options = $this->optionsAsString();
            return ["Unable to parse '{$text}' as any of {$options}"];
        }

        if (count($passing) > 1) {
            $options = $this->optionsAsString($passing);
            return ["Unable to choose an option: '{$text}' can be parsed as many options:  {$options}"];
        }

        // Setting the selected option to be used by doParse()
        // as parse() is calling checkParse() then doParse() immediately after
        $this->selected = $passing[0];

        return [];
    }

    /**
     * Parses a string using the first possible option syntax.
     * Assumes that only one option can parse the string.
     *
     * @param  string $text the string to parse
     * @return mixed
     */
    protected function doParse($text)
    {
        if ($this->selected === null) {
            throw new ParseException(["ChoiceSyntax: doParse() called having no selected option"]);
        }

        $result = $this->option($this->selected)->parse($text);
        $this->selected = null;
        return $result;
    }

    /**
     * Checks if the provided argument can be dumped using one of the options.
     *
     * @param  mixed $value
     * @return array
     */
    public function checkDump($value)
    {
        $passing = [];
        foreach ($this->options as $name => $syntax) {
            if ($syntax->canDump($value)) {
                $passing[] = $name;
            }
        }

        if (count($passing) == 0) {
            $options = $this->optionsAsString();
            return ["Unable to dump the given value using any of {$options}"];
        }

        if (count($passing) > 1) {
            $options = $this->optionsAsString($passing);
            return ["Unable to choose an option: many options can dump the given value:  {$options}"];
        }

        // Setting the selected option to be used by doDump()
        $this->selected = $passing[0];

        return [];
    }

    /**
     * Converts the given parameter to a string based on the first possible option.
     *
     * @param  mixed $value the data to encode
     * @return string
     */
    protected function doDump($value)
    {
        if ($this->selected === null) {
            throw new DumpException(["ChoiceSyntax: doDump() called having no selected option"]);
        }

        $result = $this->option($this->selected)->dump($value);
        $this->selected = null;
        return $result;
    }

}
