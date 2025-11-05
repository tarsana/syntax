<?php

require __DIR__ . '/../vendor/autoload.php';

use Tarsana\Syntax\Factory as S;

// Define the syntax of a repository part
$repoSyntax = S::object([ // a repo is a composed object
    'name' => S::string(), // the name is a string
    'stars' => S::number() // stars is a number
], ':'); // fields are separated by a ':'

// Define the syntax of a line
$lineSyntax = S::object([ // a developer is a composed object
    'first_name' => S::string(), // the first name field is a string
    'last_name' => S::string(), // the last name field is also a string
    'followers' => S::optional(S::number(), 0), // the number of followers is a number
    // as it's optional, we give it a default value 0
    'repos' => S::optional(S::array(
        $repoSyntax, // this is an array of repositories
        ',' // separated by ','
    ), []) // Optional and default value is empty array
], ' '); // fields are separated with ' '

// Now the syntax of the whole document
$documentSyntax = S::array($lineSyntax, PHP_EOL); // it's simply an array of lines separated by end-of-line characters.

// Then we can use the defined syntax to parse the document:
$developers = $documentSyntax->parse(trim(file_get_contents(__DIR__ . '/files/devs.txt')));

echo(json_encode($developers));
