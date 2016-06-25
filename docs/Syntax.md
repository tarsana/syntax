# Tarsana\Syntax\Syntax

The abstract class `Tarsana\Syntax\Syntax` defines the basic behavior of a syntax, it includes 5 abstract methods that must be implemented by every syntax class.

## Syntax::checkParse()

```php
/**
 * Checks if the provided string can be parsed using the 
 * syntax and returns an array of parsing errors if any.
 * 
 * @param  string $text
 * @return array
 */
abstract public function checkParse($text);
```

The returned array is simply an array of strings. An empty array means that the given `$text` can be parsed using the syntax.

## Syntax::doParse()

```php
/**
 * Transforms a string to data based on the syntax.
 * 
 * @param  string $text the string to parse
 * @return mixed
 */
abstract protected function doParse($text);
```

This method assumes that `$text` has been already checked and can be parsed. It simply parses it and returns the result.

## Syntax::checkDump()

```php
/**
 * Checks if the provided argument can be dumped using the 
 * syntax, and returns an array of dumping errors if any.
 * 
 * @param  mixed $value
 * @return array
 */
abstract public function checkDump($value);
```
Similar to `checkParse()`.

## Syntax::doDump()

```php
/**
 * Converts the given parameter to a string based on the syntax.
 * 
 * @param  mixed $value the data to encode
 * @return string
 */
abstract protected function doDump($value);
```
Similar to `doParse()`.

## Syntax::__toString()

```php
/**
 * Returns the string representation of the syntax.
 * 
 * @return string
 */
abstract public function __toString();
```
Used to generate errors. Should return a detailed description of the syntax.

## Syntax::parse()

```php
/**
 * Checks and converts a string to data using the syntax.
 * if the string can't be parsed; the default value is
 * returned if defined or a ParseException is thrown.
 * 
 * @param  string $text the string to parse
 * @return mixed
 * 
 * @throws Tarsana\Syntax\Exceptions\ParseException
 */
public function parse($text)
```

## Syntax::dump()

```php
/**
 * Checks and converts the given parameter to a string based on the syntax, 
 * or throws a DumpException if the value can't be dumped
 * 
 * @param  mixed $value the data to encode
 * @return string
 * 
 * @throws Tarsana\Syntax\Exceptions\DumpException
 */
public function dump($value)
```

