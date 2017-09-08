# Tarsana Syntax

[![Build Status](https://travis-ci.org/tarsana/syntax.svg?branch=master)](https://travis-ci.org/tarsana/syntax)
[![Coverage Status](https://coveralls.io/repos/github/tarsana/syntax/badge.svg?branch=master)](https://coveralls.io/github/tarsana/syntax?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8d370ef9-df1b-43c3-8073-9b17870659eb/mini.png)](https://insight.sensiolabs.com/projects/8d370ef9-df1b-43c3-8073-9b17870659eb)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](https://github.com/tarsana/syntax/blob/master/LICENSE)

A tool to encode and decode strings based on flexible and composable syntax definitions.

# Table of Contents

- [Quick Example](#quick-example)

- [Installation](#installation)

- [Step by Step Guide](#step-by-step-guide)

  - [Parsing and Dumping Strings](#parsing-and-dumping-strings)

  - [Parsing and Dumping Numbers](#parsing-and-dumping-numbers)

  - [Parsing and Dumping Booleans](#parsing-and-dumping-booleans)

  - [Parsing and Dumping Arrays](#parsing-and-dumping-arrays)

  - [Parsing and Dumping Optional Syntaxes](#parsing-and-dumping-optional-syntaxes) **Since version 2.0**
  - [Parsing and Dumping Objects](#parsing-and-dumping-objects)

  - [Parsing and Dumping Syntaxes](#parsing-and-dumping-syntaxes) **Since version 1.2.0**

- [Development Notes & Next Steps](#development-notes--next-steps)

- [Contributing](#contributing)

# Quick Example

**Warning**: This is just a teaser so if the code seems confusing don't worry, you will understand it after reading the [Step by Step Guide](#step-by-step-guide).

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
require __DIR__ . '/../vendor/autoload.php';

use Tarsana\Syntax\Factory as S;

// a repo is an object having a name (string) and stars (number), separated by ':'
$repo = "{name: string, stars:number}";
// a line consists of a first and last names, optional number of followers, and repos, separated by space. The repos are separated by ","
$line = "{first_name, last_name, followers: (number: 0), repos: ([{$repo}]:[]) | }";
// a document is a list of lines separated by PHP_EOL
$document = "[{$line}|".PHP_EOL."]";

// Now we make the syntax object
$documentSyntax = S::syntax()->parse($document);

// Then we can use the defined syntax to parse the document:
$developers = $documentSyntax->parse(trim(file_get_contents(__DIR__ . '/files/devs.txt')));
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
    "followers": "",
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
    "repos": ""
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

# Step by Step Guide

The class `Tarsana\Syntax\Factory` provides useful static methods to create syntaxes. In this guide, we will start with the basics then show how to use `SyntaxSyntax` to do things faster.

## Parsing and Dumping Strings

```php
<?php
use Tarsana\Syntax\Factory as S;

$string = S::string(); // instance of Tarsana\Syntax\StringSyntax

$string->parse('Lorem ipsum dolor sit amet');
//=> 'Lorem ipsum dolor sit amet'

$string->parse('');
//  Tarsana\Syntax\Exceptions\ParseException: Error while parsing '' as String at character 0: String should not be empty

$string->dump('Lorem ipsum dolor sit amet');
//=> 'Lorem ipsum dolor sit amet'
$string->dump('');
//=> ''
```

## Parsing and Dumping Numbers

```php
<?php
use Tarsana\Syntax\Factory as S;

$number = S::number(); // instance of Tarsana\Syntax\NumberSyntax

$number->parse('58.9'); //=> 58.9

$number->parse('Lorem12');
//  Tarsana\Syntax\Exceptions\ParseException: Error while parsing 'Lorem' as Number at character 0: Not a numeric value
```

## Parsing and Dumping Booleans
```php
<?php
use Tarsana\Syntax\Factory as S;

$boolean = S::boolean(); // instance of Tarsana\Syntax\BooleanSyntax

$boolean->parse('true'); //=> true
$boolean->parse('yes'); //=> true
$boolean->parse('y'); //=> true
$boolean->parse('TrUe'); //=> true (case insensitive)
$boolean->parse('false'); //=> false
$boolean->parse('no'); //=> false
$boolean->parse('N'); //=> false

$boolean->parse('Lorem');
// Tarsana\Syntax\Exceptions\ParseException: Error while parsing 'Lorem' as Boolean at character 0: Boolean value should be one of "yes", "no", "y", "n", "true", "false"

$boolean->dump(true); //=> 'true'
$boolean->dump(false); //=> 'false'

$boolean->dump('Lorem');
// Tarsana\Syntax\Exceptions\DumpException: Error while dumping some input as Boolean: Not a boolean
```

## Parsing and Dumping Arrays

`Tarsana\Syntax\ArraySyntax` represents an array of elements having the same syntax and separated by the same string. So an `ArraySyntax` is constructed using a `Syntax` (could be `NumberSyntax`, `StringSyntax` or any other) and a `separator`.

- if the `Syntax` argument is missing, an instance of `StringSyntax` is used by default.

- if the `separator` argument is missing, `','` is used by default.

```php
<?php
use Tarsana\Syntax\Factory as S;

$strings = S::array();

$strings->parse('aa:bb,cc,"ss,089",true');
//=> ['aa:bb','cc','ss,089','true']
// Note that we can use "..." to escape the separator

$strings->dump(['aa','bb,cc','76']);
//=> 'aa,"bb,cc",76'
// Yeah, it's smart enough to auto-escape items containing the separator

$vector = S::array(S::number());

$vector->parse('1,2,3,4,5');
//=> [1, 2, 3, 4, 5]

$matrix = S::array($vector, PHP_EOL);

$matrix->parse(
'1,2,3
4,5,6,7
8,9,100');
//=> [ [1, 2, 3], [4, 5, 6, 7], [8, 9, 100] ]
```

## Parsing and Dumping Optional Syntaxes

`Tarsana\Syntax\Optional` represents an optional syntax. Given a syntax and a static default value; it will try to parse inputs using the syntax and return the default value when in case of failure.

```php
<?php
use Tarsana\Syntax\Factory as S;

$optionalNumber = S::optional(S::number(), 10);

$optionalNumber->parse(15); //=> 15
$optionalNumber->success(); //=> true

$optionalNumber->parse('Yo'); //=> 10 (the default value)
$optionalNumber->success(); //=> false
```

## Parsing and Dumping Objects

`Tarsana\Syntax\ObjectSyntax` represents an object in which every field can have its own syntax. It's defined by providing an associative array of fields and a `separator` (if missing, the separator by default is `':'`).

```php
<?php
use Tarsana\Syntax\Factory as S;

$repository = S::object([
    'name' => S::string(),
    'is_private' => S::optional(S::boolean(), false),
    'forks' => S::optional(S::number(), 0),
    'stars' => S::optional(S::number(), 0)
]);

$repository->parse('tarsana/syntax');
// an stdClass as below
// {
//  name: 'tarsana/syntax',
//  is_private: false,
//  forks: 0,
//  stars: 0
// }

$repository->parse('tarsana/syntax:5');
// {
//  name: 'tarsana/syntax',
//  is_private: false,
//  forks: 5,
//  stars: 0
// }

$repository->parse('tarsana/syntax:yes:7');
// {
//  name: 'tarsana/syntax',
//  is_private: true,
//  forks: 7,
//  stars: 0
// }

$data = (object) [
    'name' => 'foo/bar',
    'is_private' => false,
    'forks' => 9,
    'stars' => 3
];

$repository->dump($data);
// 'foo/bar:false:9:3'

$developer = S::object([
    'name' => S::string(),
    'followers' => S::optional(S::number(), 0),
    'repositories' => S::optional(S::array($repository), [])
], ' ');

$developer->parse('Amine');
// {
//  name: 'Amine',
//  followers: 0,
//  repositories: []
// }

$developer->parse('Amine tarsana/syntax,webNeat/lumen-generators:16:57');
// {
//  name: 'Amine',
//  followers: 0,
//  repositories: [
//      { name: 'tarsana/syntax', is_private: false, forks: 0, stars: 0 },
//      { name: 'webNeat/lumen-generators', is_private: false, forks: 16, stars: 57 }
//  ]
// }
```

## Parsing and Dumping Syntaxes

Now you know how to parse and dump basic types : `string`, `boolean`, `number`, `array`, `optional` and `object`. But you may notice that writing code for complex syntaxes (object including arrays including objects ...) requires many complex lines of code. `SyntaxSyntax` was introduced to solve this issue. As the name shows, it's a `Syntax` that parses and dumps syntaxes, a meta syntax!

So instead of writing this:

```php
$personSyntax = S::object([
  'name' => S::string(),
   'age' => S::number(),
   'vip' => S::boolean(),
  'friends' => S::array()
]);
```

You simply write this

```php
$personSyntax = S::syntax()->parse('{name, age:number, vip:boolean, friends:[]}');
```

### Rules

- `S::string()` is `string`.
- `S::number()` is `number`.
- `S::boolean()` is `boolean`.
- `S::syntax()` is `syntax`.
- `S::optional($type, $default)` is `(type:default)` where `type` is the string corresponding to `$type` and `default` is `json_encode($default)`.
- `S::array($type, $separator)` is `[type|separator]` where`type` is the string corresponding to `$type` and `separator` is the same as `$separator`. If the separator is omitted (ie. `[type]`); the default value is `,`.
t)`.
- `S::object(['name1' => $type1, 'name2' => $type2], $separator)` is `{name1:type1, name2:type2 |separator]` . If the separator is missing the default value is `:`.

### Examples

```php
// '{name: string, age: number}'
S::object([
  'name' => S::string(),
   'age' => S::number()
])

// '{position: {x: number, y: number |"|"}, width:number, height:number}'
S::obejct([
  'position' => S::object([
    'x' => S::number(),
    'y' => S::number()
  ], '|'),
   'width' => S::number(),
  'height' => S::number()
])

// '{name, stars:number, contributers: [{name, email|-}]}'
S::object([
  'name'  => S::string(),
  'stars' => S::number(),
  'contributers' => S::array(S::object([
    'name'  => S::string(),
    'email' => S::string()
  ], '-'))
])
```

# Development Notes & Next Steps

- **version 2.1.0**
  - `syntax` added to the string representation of a syntax and corresponds to the `S::syntax()` instance.

- **version 2.0.0**

  - Separators and default values can be specified when creating syntax from string.
  - Escaping separators is now possible.
  - `OptionalSyntax` added.
  - Attributes `default` and `description` removed from `Syntax` class.
  - Upgraded to PHPUnit 6 and PHP 7.
  - No dependencies.
  - Detailed Exceptions with position of errors.
  - Better `Factory` methods.

- **version 1.2.1**:

  - `tarsana/functional` dependency updated

  - couple of bug fixes

- **version 1.2.0**:

  - `SyntaxSyntax` added.

  - `separator` and `itemSyntax` getters and setters added to `ArraySyntax`.

  - `separator` and `fields` getters and setters added to `ObjectSyntax`.

- **version 1.1.0**:

  - `description` attribut added to `Syntax` to hold additional details.

- **version 1.0.1**:

  - Tests coverage is now **100%**

  - Some small bugs of `ArraySyntax` and `ObjectSyntax` fixed.

- **version 1.0.0**: String, Number, Boolean, Array and Object syntaxes.

# Contributing

Please take a look at the code and see how other syntax classes are done and tested before fixing or creating a syntax. All feedbacks and pull requests are welcome :D
