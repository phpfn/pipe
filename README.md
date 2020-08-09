# Pipe

Object-oriented pipe operator implementation based 
on [RFC Pipe Operator](https://wiki.php.net/rfc/pipe-operator).

## Installation

Library can be installed into any PHP application:
- Using [`Composer`](https://getcomposer.org/) dependency manager 
- [The Force](https://www.youtube.com/watch?v=o2we_B6hDrY) for the Jedi Developers

```sh
$ composer require phpfn/pipe
```

In order to access library make sure to include `vendor/autoload.php` 
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
pipe('Hello World')
    ->ucwords(_)
    ->preg_replace('/\s+/u', '', _)
    ->preg_replace('/(.)(?=[A-Z])/u', '$1_', _)
    ->strtolower(_)
    ->var_dump;
//
// string(11) "hello_world"
//
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
    ->array_filter(_, fn($x): bool => $x !== '.' && $x !== '..')
    ->array_map(fn($x): string => $arg . '/' . $x, _)
    ->use('namespaced\func')->get_file_arg
    ->array_merge($result, _);
```

## Working With Value

To pass a value as an argument to a function, use the 
underscore (`_`) character:

```php
<?php

pipe('hello')
    ->str_replace('o', '', _)
    ->var_dump; // "hell"
```

You can omit parentheses if only one argument is used:

```php
<?php

pipe('some')
    ->is_array
    ->var_dump; // bool(false) 
```

To get the value, use one of the options:

```php
<?php
$context = pipe('hello')->strtoupper;

var_dump($context);
// object(Fun\Pipe\Pipe)#8 (1) { ... } 

var_dump($context());
// string(5) "HELLO"
```

## Working With Namespace

Let's take a simple example of such code:

```php
namespace {
    function foo() { return __FUNCTION__; }
}

namespace Example {
    function foo() { return __FUNCTION__; }
}
```

Let's try to manage the namespace:

```php
$context = pipe()->use('Example')->foo;

echo $context(); // 'Example\\foo'

$context = $context->foo;

echo $context(); // 'foo'
```

Please note that the `use` function applies only to the subsequent function, 
all further operations performed in the current context:

```php
pipe()
    ->use('Some\\Namespace')->foo // Call "\Some\Namespace\foo()"
    ->foo // Call "\foo()"
;
```

In order to perform several operations in another namespace, use an anonymous 
function as the second `use` argument.

```php
pipe()
    ->use('Some\\Namespace', fn($pipe) => 
        $pipe
            ->a // Call "\Some\Namespace\a()"
            ->b // Call "\Some\Namespace\b()"
    )
    ->a // Call "a()"
;
```

> Note that the behavior of the `->use()` method differs depending on whether 
> the second argument is passed.
