<?php

require __DIR__ . '/../vendor/autoload.php';

use Tarsana\Syntax\Factory as S;

// a repo is an object having a name (string) and stars (number), separated by ':'
$repo = "{name: string, stars:number}";
// a line consists of a first and last names, optional number of followers, and repos, separated by space. The repos are separated by ","
$line = "{first_name, last_name, followers: (number: 0), repos: ([{$repo}]:[]) | }";
// a document is a list of lines separated by PHP_EOL
$document = "[{$line}|" . PHP_EOL . "]";

// Now we make the syntax object
$documentSyntax = S::syntax()->parse($document);

// Then we can use the defined syntax to parse the document:
$developers = $documentSyntax->parse(trim(file_get_contents(__DIR__ . '/files/devs.txt')));

echo(json_encode($developers));
