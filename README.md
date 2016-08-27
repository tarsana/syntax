# Tarsana Syntax

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8d370ef9-df1b-43c3-8073-9b17870659eb/small.png)](https://insight.sensiolabs.com/projects/8d370ef9-df1b-43c3-8073-9b17870659eb)

[![Build Status](https://travis-ci.org/tarsana/syntax.svg?branch=master)](https://travis-ci.org/tarsana/syntax)
[![Coverage Status](https://coveralls.io/repos/github/tarsana/syntax/badge.svg?branch=master)](https://coveralls.io/github/tarsana/syntax?branch=master)
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

  - [Parsing and Dumping Objects](#parsing-and-dumping-objects)

  - [Parsing and Dumping Syntaxes](#parsing-and-dumping-syntaxes) **Since 1.2.0**

  - [Using the Factory](#using-the-factory)

  - [Write Your Own Syntax Definition](#write-your-own-syntax-definition)

- [Next Steps](#next-steps)

- [Development Notes](#development-notes)

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
$repo = "{:,name,#stars}";
// a line consists of a first and last names, optional number of followers, and repos, separated by space. The repos are separated by ","
$line = "{ ,first_name,last_name,[#followers],[repos{$repo}[,]]}";
// a document is a list of lines separated by PHP_EOL
$document = "{$line}[".PHP_EOL."]";

// Now we make the syntax object
$documentSyntax = S::fromString($document);

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

In this guide, we will start with the basics then show how to use the `Factory` and `SyntaxSyntax` to do things faster.

## Parsing and Dumping Strings

```php
<?php
use Tarsana\Syntax\StringSyntax;

$string = new StringSyntax(); // any non empty string

$string->parse('Lorem ipsum dolor sit amet');
// 'Lorem ipsum dolor sit amet'
$string->parse('');
// Tarsana\Syntax\Exceptions\ParseException: Unable to parse '' as 'string'

$string->dump('Lorem ipsum dolor sit amet');
// 'Lorem ipsum dolor sit amet'
$string->dump('');
// ''

$stringWithDefaultValue = new StringSyntax('default value here'); // any string

$stringWithDefaultValue->parse('');
// 'default value here'
```

## Parsing and Dumping Numbers

```php
<?php
use Tarsana\Syntax\NumberSyntax;

$number = new NumberSyntax(); // any numeric value

$number->parse('58.9');
// 58.9

$number->parse('Lorem');
// Tarsana\Syntax\Exceptions\ParseException: Unable to parse 'Lorem' as 'number'
```

## Parsing and Dumping Booleans
```php
<?php
use Tarsana\Syntax\BooleanSyntax;

$boolean = new BooleanSyntax();

$boolean->parse('true');
// true
$boolean->parse('yes');
// true
$boolean->parse('y');
// true
$boolean->parse('TrUe'); // case insensitive
// true

$boolean->parse('false');
// false
$boolean->parse('no');
// false
$boolean->parse('N');
// false

$boolean->parse('Lorem');
// Tarsana\Syntax\Exceptions\ParseException: Unable to parse 'Lorem' as 'boolean'

$boolean->dump(true);
// 'true'
$boolean->dump(false);
// 'false'
$boolean->dump('Lorem');
// Tarsana\Syntax\Exceptions\DumpException: Unable to dump 'Lorem' as 'boolean'
```

## Parsing and Dumping Arrays

`ArraySyntax` represents an array of elements having the same syntax and separated by the same string. So an `ArraySyntax` is constructed using a `Syntax` (could be `NumberSyntax`, `StringSyntax` or any other) and a `separator`. It can also have a default value as 3rd argument of the constructor.

- if the `Syntax` argument is missing, an instance of `StringSyntax` is used by default.

- if the `separator` argument is missing, `','` is used by default.

```php
<?php
use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\NumberSyntax;

$strings = new ArraySyntax();

$strings->parse('aa:bb,cc,ss,089,true');
// ['aa:bb','cc','ss','089','true']
// Yeah, this is the same as explode(',', ....)

$strings->dump(['aa','bb','76']);
// 'aa,bb,76'
// Yeah, this is the same as implode(',', ....)

$vector = new ArraySyntax(new NumberSyntax());

$vector->parse('1,2,3,4,5');
// [1, 2, 3, 4, 5]

$matrix = new ArraySyntax($vector, PHP_EOL);

$matrix->parse(
'1,2,3
4,5,6,7
8,9,100');
// [ [1, 2, 3], [4, 5, 6, 7], [8, 9, 100] ]
```

## Parsing and Dumping Objects

`ObjectSyntax` represents an object in which every field can have its own syntax. It's defined by providing an associative array of fields and a `separator` (if missing, the separator by default is `':'`).

```php
<?php
use Tarsana\Syntax\ArraySyntax;
use Tarsana\Syntax\BooleanSyntax;
use Tarsana\Syntax\NumberSyntax;
use Tarsana\Syntax\ObjectSyntax;
use Tarsana\Syntax\StringSyntax;

$repository = new ObjectSyntax([
    'name' => new StringSyntax(),
    'is_private' => new BooleanSyntax(false),
    'forks' => new NumberSyntax(0),
    'stars' => new NumberSyntax(0)
]);
// is_private, forks and stars are optional fields
// because they have default values

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

$developer = new ObjectSyntax([
    'name' => new StringSyntax(),
    'followers' => new NumberSyntax(0),
    'repositories' => new ArraySyntax($repository, ',', [])
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

Now you know how to parse and dump basic types : `string`, `boolean`, `number`, `array` and `object`. But you may notice that writing code for complex syntaxes (object including arrays including objects ...) requires many complex lines of code. `SyntaxSyntax` was introduced to solve this issue. As the name shows, it's a `Syntax` that parses and dumps syntaxes, a meta syntax.

So instead of writing this:

```php
$personSyntax = new ObjectSyntax([
  'name' => new StringSyntax,
   'age' => new NumberSyntax,
   'vip' => new BooleanSyntax
  'friends' => new ArraySyntax(new StringSyntax)
]);
```

You simply write this

```php
$personSyntax = (new SyntaxSyntax)->parse('{name,#age,vip?,friends[]}');
```

### Grammar

The grammar of the syntax language is the following:

```
syntax  := string | number | boolean | array | object
number  := '#' string
boolean := string '?'
array   := syntax '[' array-separator ']'
object  := string '{' object-separator fields-separator fields '}'
         | string '{' fields '}'
fields  := syntax
         | syntax fields-separator fields
string           := [a-zA-Z-_]*
array-separator  := [^[]*
object-separator := [^,a-zA-Z0-9\[]+
fields-separator := ','
```

### Examples

```php
// ''
new StringSyntax;

// 'name'
(new StringSyntax)->description('name');

// '#'
new NumberSyntax;

// '#age'
(new NumberSyntax)->description('age');

// '?'
new BooleanSyntax;

// 'is-valid?'
(new BooleanSyntax)->description('is-valid');

// 'names[,]'
(new ArraySyntax(new StringSyntax))->description('names');

// '#numbers[|]'
(new ArraySyntax(new NumberSyntax, '|'))->description('numbers');

// '?[]'
new ArraySyntax(new BooleanSyntax);

// '{:,name,#age}'
new ObjectSyntax([
  'name' => new StringSyntax,
   'age' => new NumberSyntax
], ':')

// 'rectangle{position{|,#x,#y},#width,#height}'
(new ObjectSyntax([
  'position' => new ObjectSyntax([
    'x' => new NumberSyntax,
    'y' => new NumberSyntax
  ], '|'),
   'width' => new NumberSyntax,
  'height' => new NumberSyntax
], ':'))->description('rectangle')

// 'repo{name,#stars,contributers{|,name,email}[]}'
(new ObjectSyntax([
  'name'  => new StringSyntax,
  'stars' => new NumberSyntax,
  'contributers' => new ArraySyntax(new ObjectSyntax([
    'name'  => new StringSyntax,
    'email' => new StringSyntax
  ], '|')),
]))->description('repo')
```

## Using the Factory

The class `Tarsana\Syntax\Factory` provides some static methods to get ride of the `new` keyword when defining syntaxes. These methods are just aliases and have the same parameters as the constructors. In addition, it provides the method `fromString` which uses `SyntaxSyntax` to make a syntax from its string definition.

```php
<?php
use Tarsana\Syntax\Factory as S;

$syntax = S::string(); // $syntax = new StringSyntax;
$syntax = S::boolean() // $syntax = new BooleanSyntax;
$syntax = S::number(); // $syntax = new NumberSyntax;
$syntax = S::arr(...); // $syntax = new ArraySyntax(...);
$syntax = S::obj(...); // $syntax = new ObjectSyntax(...);
$syntax = S::syntax(); // $syntax = new SyntaxSyntax;

$syntax = S::fromString('...') // $syntax = (new SyntaxSyntax)->parse('....')
```

## Write Your Own Syntax Definition

To write your own custom syntax class, you just need to inherit from [`Syntax`](https://github.com/tarsana/syntax/blob/master/docs/Syntax.md) and implement the missing methods.

# Next Steps

- [X] Design a plain text language to define syntaxes.

- [ ] Define syntaxes using JSON or YAML files.

- [ ] Add more advanced syntaxes: Regular Expression, Date, ...

# Development Notes

- **version 1.0.0**: String, Number, Boolean, Array and Object syntaxes.

- **version 1.0.1**:

  - Tests coverage is now **100%**

  - Some small bugs of `ArraySyntax` and `ObjectSyntax` fixed.

- **version 1.1.0**:

  - `description` attribut added to `Syntax` to hold additional details.

- **version 1.2.0**:

  - `SyntaxSyntax` added.

  - `separator` and `itemSyntax` getters and setters added to `ArraySyntax`.

  - `separator` and `fields` getters and setters added to `ObjectSyntax`.

# Contributing

Please take a look at the code and see how other syntax classes are done and tested before fixing or creating a syntax. All feedbacks and pull requests are welcome :D
