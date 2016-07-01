# Tarsana Syntax

[![Build Status](https://travis-ci.org/tarsana/syntax.svg?branch=master)](https://travis-ci.org/tarsana/syntax)
[![Coverage Status](https://coveralls.io/repos/github/tarsana/syntax/badge.svg?branch=master)](https://coveralls.io/github/tarsana/syntax?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8d370ef9-df1b-43c3-8073-9b17870659eb/small.png)](https://insight.sensiolabs.com/projects/8d370ef9-df1b-43c3-8073-9b17870659eb)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](http://opensource.org/licenses/MIT)

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

# Step by Step Guide

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

## Using the Factory

The class `Tarsana\Syntax\Factory` provides some static methods to get ride of the `new` keyword when defining syntaxes. These methods are just aliases and have the same parameters as the constructors.

```php
<?php
use Tarsana\Syntax\Factory as S;

$syntax = S::string(); // $syntax = new StringSyntax();
$syntax = S::boolean(); // $syntax = new BooleanSyntax();
$syntax = S::number(); // $syntax = new NumberSyntax();
$syntax = S::arr(...); // $syntax = new ArraySyntax(...);
$syntax = S::obj(...); // $syntax = new ObjectSyntax(...);
```

## Write Your Own Syntax Definition

To write your own custom syntax class, you just need to inherit from [`Syntax`](https://github.com/tarsana/syntax/blob/master/docs/Syntax.md) and implement the missing methods.

# Next Steps

- Define syntaxes using JSON or YAML files.

- Design a plain text language to define syntaxes.

- Add more advanced syntaxes: Regular Expression, Date, ...

# Development Notes

- **version 1.0.0**: String, Number, Boolean, Array and Object syntaxes.

- **version 1.0.1**: 

  - Tests coverage is now **100%**

  - Some small bugs of `ArraySyntax` and `ObjectSyntax` fixed.

# Contributing

Please take a look at the code and see how other syntax classes are done and tested before fixing or creating a syntax. All feedbacks and pull requests are welcome :D
