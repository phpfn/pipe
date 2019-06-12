# Pipe

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
$camelCase = 'HelloWorld';

$x = ucwords($camelCase);
$x = preg_replace('/\s+/u', '', $x);
$x = preg_replace('/(.)(?=[A-Z])/u', '$1_', $x);
$snakeCase = strtolower($x)
             
var_dump($snakeCase); // "hello_world"
```

The pipe library fixes this problem, allows you to 
chain the execution of pure functions:

```php
$snakeCase = pipe($camelCase)
    ->ucwords(_)
    ->pregReplace('/\s+/u', '', _)
    ->pregReplace('/(.)(?=[A-Z])/u', '$1_', _)
    ->strToLower(_)
    ->varDump;
```

> Note: All functions are available both in the 
  form of **camelCase**, and in the form of a **snake_case**.


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

// Using "value" property
$result = $pipe->value; // string("HELLO")

// Using pipe invocation
$result = $pipe(); // string("HELLO")
```
