<p align="center">
    <h1>Pipe</h1>
</p>
<p align="center">
    <a href="https://travis-ci.org/SerafimArts/Pipe"><img src="https://travis-ci.org/SerafimArts/Pipe.svg" alt="Travis CI" /></a>
    <a href="https://codeclimate.com/github/SerafimArts/Pipe/test_coverage"><img src="https://api.codeclimate.com/v1/badges/1fb35ca43960c5421349/test_coverage" /></a>
    <a href="https://codeclimate.com/github/SerafimArts/Pipe/maintainability"><img src="https://api.codeclimate.com/v1/badges/1fb35ca43960c5421349/maintainability" /></a>
</p>
<p align="center">
    <a href="https://packagist.org/packages/serafim/pipe"><img src="https://img.shields.io/badge/PHP-7.1+-6f4ca5.svg" alt="PHP 7.1+"></a>
    <a href="https://packagist.org/packages/serafim/pipe"><img src="https://poser.pugx.org/serafim/pipe/version" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/serafim/pipe"><img src="https://poser.pugx.org/serafim/pipe/downloads" alt="Total Downloads"></a>
    <a href="https://raw.githubusercontent.com/serafim/pipe/master/LICENSE.md"><img src="https://poser.pugx.org/SerafimArts/Pipe/license" alt="License MIT"></a>
</p>

Object-oriented pipe operator implementation based 
on [RFC Pipe Operator](https://wiki.php.net/rfc/pipe-operator).


## Installation

Library can be installed into any PHP application 
using `Composer` dependency manager.

```sh
$ composer require serafim/pipe
```

In order to access pipe library make sure to include `vendor/autoload.php` 
in your file.

```php
<?php

require __DIR__ . '/vendor/autoload.php';
```

## Usage

A common PHP OOP pattern is the use of method chaining, or what is 
also known as "Fluent Expressions". So named for the way one method 
flows into the next to form a conceptual hyper-expression.

However, when using the functional approach this can lead to reduced 
readability, polluted symbol tables, or static-analysis defying 
type inconsistency such as in the following example:

```php
<?php

$snakeCase = strtolower(
    preg_replace('/(.)(?=[A-Z])/u', '$1_', 
        preg_replace('/\s+/u', '', 
            ucwords('HelloWorld')
        )
    )
);
             
var_dump($snakeCase); // "hello_world"
```

The pipe library fixes this problem, allows you to 
chain the execution of pure functions:

```php
$snakeCase = pipe($camelCase)
    ->ucWords(_)
    ->pregReplace('/\s+/u', '', _)
    ->pregReplace('/(.)(?=[A-Z])/u', '$1_', _)
    ->strToLower(_)
    ->varDump;
```

All functions are available both in the form of **camelCase**, 
and in the form of a **snake_case**:
  
```php
pipe($value)->var_dump;
 
// same as
pipe($value)->varDump;
```

and

```php
pipe($value)->strtolower;
 
// same as
pipe($value)->strToLower;

// same as
pipe($value)->str_to_lower;
```

### Another Example

See: [https://wiki.php.net/rfc/pipe-operator#file_collection_example](https://wiki.php.net/rfc/pipe-operator#file_collection_example)

```php
<?php
$result = array_merge(
    $result,
    namespaced\func\get_file_arg(
        array_map(
            function ($x) use ($arg) {
                return $arg . '/' . $x;
            },
            array_filter(
                scandir($arg),
                function ($x) {
                    return $x !== '.' && $x !== '..';
                }
            )
        )
    )
);
```

With this library, the above could be easily rewritten as:

```php
<?php

$result = pipe($arg)
    ->scanDir($arg)
    ->arrayFilter(_, fn($x) => $x !== '.' && $x != '..')
    ->arrayMap(fn($x) => $arg . '/' . $x, _)
    ->use('namespaced\func')->getFileArg
    ->arrayMerge($result, _);
```


## Working With Value

To pass a value as an argument to a function, use the 
underscore (`_`) character:

```php
<?php

pipe('hello')
    ->strReplace('o', '', _)
    ->varDump; // "hell"
```

You can omit parentheses if only one argument is used:

```php
<?php

pipe('some')->isArray->varDump; // bool(false) 
```

To get the value, use one of the options:

```php
<?php
$pipe = pipe('hello')->strToUpper;

// Using pipe invocation
$result = $pipe(); // string("HELLO")
```
