# Tarsana Syntax

[![Build Status](https://travis-ci.org/tarsana/syntax.svg?branch=master)](https://travis-ci.org/tarsana/syntax)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](http://opensource.org/licenses/MIT)

A tool to encode and decode strings based on data structure definitions.

# Table of Contents

- [Short Example](#short-example)

- [Installation](#installation)

- [API Documentation](#api-documentation)

- [Next Steps](#next-features)

# Short Example

Let's assume that you have the following text representing a list of developers where each line follow the syntax:

```
first-name last-name [number-of-followers] [repo-name:stars,repo-name:stars,...]
```

```
Tammy Flores  257 library:98,fast-remote:5,anyway:987
Rebecca Welch forever:76,oops:0
Walter Phillips 423
```

**Syntax** helps you to parse this document and convert it to manipulable objects easily.

Let's do it:

```php
<?php


use Tarsana\Syntax\Factory as S;

// Define the syntax of a repository part
$repoSyntax = S::obj([ // a repo is a composed object
    'name' => S::string(), // the name is a string
    'stars' => S::number() // stars is a number
], ':'); // fields are separated by a ':'

// Define the syntax of a line
$lineSyntax = S::obj([ // a developer is a composed object
    'first_name' => S::string(), // the first name field is a string
    'last_name' => S::string(), // the last name field is also a string
    'followers' => S::number(0), // the number of followers is a number
    // as it's optional, we give it a default value 0
    'repos' => S::arr(
        $repoSyntax, // this is an array of repositories 
        ',', // separated by ','
        [] // Optional and default value is empty array
    )
], ' '); // fields are separated with ' '

// Now the syntax of the whole document
$documentSyntax = S::arr($lineSyntax, PHP_EOL); // it's simply an array of lines separated by end-of-line characters.

// Then we can use the defined syntax to parse the document:
$developers = $documentSyntax->parse(trim(file_get_contents('path/to/the/file')));
```

`$developers` will contain the following:

```json
[
  {
    "first_name": "Tammy",
    "last_name": "Flores",
    "followers": 257,
    "repos": [
      {
        "name": "library",
        "stars": 98
      },
      {
        "name": "fast-remote",
        "stars": 5
      },
      {
        "name": "anyway",
        "stars": 987
      }
    ]
  },
  {
    "first_name": "Rebecca",
    "last_name": "Welch",
    "followers": 0,
    "repos": [
      {
        "name": "forever",
        "stars": 76
      },
      {
        "name": "oops",
        "stars": 0
      }
    ]
  },
  {
    "first_name": "Walter",
    "last_name": "Phillips",
    "followers": 423,
    "repos": []
  }
]
```

You modified `$developers` and want to save it back to the document following the same syntax ? You can do it:

```php
// ... manipulating $developers

file_put_contents('path/to/file', $documentSyntax->dump($developers));
```


# Installation

Install it using composer

```
composer require tarsana/syntax
```

# API Documentation

As you have seen in the example above, **Syntax** let's you define syntaxes and use them to convert text to objects and the inverse. 

## Syntax

The abstract class `Tarsana\Syntax\Syntax` defines the basic behavior of a syntax, it includes 5 abstract methods that should be implemented by every `Syntax`.

### Syntax::checkParse()

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

### Syntax::doParse()

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

### Syntax::checkDump()

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

### Syntax::doDump()

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

### Syntax::__toString()

```php
/**
 * Returns the string representation of the syntax.
 * 
 * @return string
 */
abstract public function __toString();
```
Used to generate errors. Should return a detailed description of the syntax.

