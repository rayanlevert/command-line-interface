# Dependency-free command line interface (CLI) handling arguments and easily personalizing output in the PHP userland

[![Packagist Version](https://img.shields.io/packagist/v/rayanlevert/command-line-interface)](https://packagist.org/packages/rayanlevert/command-line-interface)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/rayanlevert/command-line-interface)](https://packagist.org/packages/rayanlevert/command-line-interface)
[![codecov](https://codecov.io/gh/rayanlevert/command-line-interface/branch/main/graph/badge.svg)](https://codecov.io/gh/rayanlevert/command-line-interface)
[![Packagist](https://img.shields.io/packagist/dd/rayanlevert/command-line-interface)](https://packagist.org/packages/rayanlevert/command-line-interface/stats)

## **RayanLevert\Cli\Arguments\Argument** defines what is an Argument
An argument has a name and different options and can only be of type `integer`, `double` ou `string` (if the option `noValue` is used, it will be `bool`)

```php
new \RayanLevert\Cli\Arguments\Argument(string $name, array $options = [])
```

```
- description (string) Description of the argument
- defaultValue (float|int|string) Default value if the argument is not handled
- required (bool) If the argument must be present and parsed
- castTo (string) PHP type - If the argument has a type other than string, its value will be casted
- noValue (bool) If a prefixed argument doesn't need a value -> boolean cast
- prefix (string) Short prefix (-u)
- longPrefix (string) Long prefix (--user)
```

A `RayanLevert\Cli\Arguments\Exception` can be thrown if options are not compliant (see `__construct()`)

## **RayanLevert\Cli\Arguments** is a collection of Argument capable of parsing values from `argv` (array of strings)

```php
new \RayanLevert\Cli\Arguments(\RayanLevert\Cli\Arguments\Argument ...$oArguments)
```

> Required arguments must be declared first, before not required ones

### Recovery of parsed values is done via the method `parse(string ...$arguments): void`
> To parse arguments from the actual CLI, use `parse(...$argv)` (with declaring `global $argv;` if you are not in the global scope)

Associates each parsed value to its Argument in the collection

The parsed value of an argument is recoverable by `::get(string $nomArgument)`

By default, `NULL` is returned; `integer`, `float` or `string` can be returned if the argument has been parsed and option `castTo` has been set

- If castTo is `integer` or `float`, the value must be a numeric string (asserts with `is_numeric()`)
- Si castTo is `string` (by default), the value will be the one parsed

#### Implementation
```php
$oArguments = new Arguments(new Argument('arg1'));
$oArguments->get('arg1') // NULL

$oArguments = new Arguments(new Argument('arg1', ['defaultValue' => 'test']));
$oArguments->get('arg1') // test

$oArguments = new Arguments(new Argument('arg1', ['castTo' => 'float', 'defaultValue' => 14.3]));
$oArguments->get('arg1') // 14.3

$oArguments = new Arguments(new Argument('arg1', ['required' => true]));
$oArguments->parse(); // ParseException arg1 is required
$oArguments->parse('test'); // OK $oArguments->get('arg1') = test

// Parsing optional arguments
$oArguments = new Arguments(new Argument('arg1'), new Argument('arg2'));
$oArguments->parse('test1'); // $oArguments->get('arg1') = test1, $oArguments->get('arg1') = NULL
$oArguments->parse('test1', 'test2'); // $oArguments->get('arg1') = test1, $oArguments->get('arg1') = test2

// Parsing prefixed arguments
$oArguments = new Arguments(new Argument('arg1', ['prefix' => 'a', 'longPrefix' => 'arg']));
$oArguments->parse('-a=testValue'); // $oArguments->get('arg1') = testValue
$oArguments->parse('-a="test Value"'); // $oArguments->get('arg1') = test Value
$oArguments->parse('--arg=testValue'); // $oArguments->get('arg1') = testValue
$oArguments->parse('--arg="test Value"'); // $oArguments->get('arg1') = test Value
```

### Multiple methods are available

- Adds an argument - `set(Argument $oArgument): void`
- Removes one - `remove(string $argumentName): void`
- Returns the number of arguments `count(): int`
- Prints a clean display about the informations of arguments `printArguments(): void`
  ```
    Required arguments:
      arg1 (type: string)
      arg2 (type: integer)

    Optional arguments:
      arg3 --arg3=arg3 (type: integer)
      arg4 -arg4
  ```

# Personalizes the command line interface by changing the color and formatting displayed text

## **RayanLevert\Cli\Style** is a only having static methods class

3 enumerations are available to stylize the output:

- `RayanLevert\Cli\Style\Background`: Background colors
- `RayanLevert\Cli\Style\Foreground`: Text colors
- `RayanLevert\Cli\Style\Attributes`: Text attributes

2 main methods are used to display formatted text

```php
/**
 * Prints a string of a background color, text color and/or an attribute
*/
public static function inline(string $string, Background $bg = null, Foreground $fg = null, Attribute $at = null): void;

/**
 * Prints a string and breaks a line of a background color, text color and/or an attribute
*/
public static function outline(string $string, Background $bg = null, Foreground $fg = null, Attribute $at = null): void;
```

And one to return the formatted string instead of printing it
```php
public static function stylize(string $string, Background $bg = null, Foreground $fg = null, Attribute $at = null): string;
```

Other useful methods are available:

```php
/**
 * Prints a formatted string thanks to its tags of ANSI codes (Foreground, Background and Attribute)
 *
 * Useful is you want to use multiple styles in one single method call
 *
 * Tags to use are in the three enumerations thanks to the 'tryFromTag' method
*/
public static function tag(string $tag): void

====================================
｡◕‿◕｡ This is a title ｡◕‿◕｡
====================================
public static function title(string $title): void;

--- Flanked message ---\n
public static function flank(string $message, string $char = '-', int $length = 3): void;

  (◍•﹏•) Warning message\n
public static function warning(string $message): void; // colored text in yellow

  (◍•﹏•) Error message\n
public static function error(string $message): void; // colored text in red

public static function red(string $message): void; // displays a red colored text and breaks a line
public static function green(string $message): void; // displays a green colored text and breaks a line
public static function yellow(string $message): void; // displays a yellow colored text and breaks a line

// Displays according to a boolean status, a red or green text colored message and breaks a line
public static function outlineWithBool(bool $status, string $ifTrue, string $ifFalse, string $toPrecede = ''): void;

// Prints the details of an exception in red + its trace in white
public static function exception(\Exception $e, bool $withoutTrace = false): void;
```

## **RayanLevert\Cli\ProgressBar Displays progression output through a progress bar**

```php
/**
 * @param int $max Maximum value of iterations
 * @param int $numberOfSymbols Number of symbols added after each iteration
*/
$oProgressBar = new ProgressBar(int $max, int $numberOfSymbols = 50);

/**
 * @param string $title Title to add above the progress barTitre à ajouter au dessus de la barre de progrès
 * @param Style\Foreground $fg Text color
*/
$oProgressBar->setTitle(string $title = '', Style\Foreground $fg = Style\Foreground::BLUE);

/**
 * Starts the progress bar (or restarts it, if not breaks two lines)
*/
$oProgressBar->start();

/**
 * Advances the progress bar of `$toAdvance` iterations updating the progression
*/
$oProgressBar->advance(int $toAdvance = 1);

// Finishes the progress bar (advances to the max value)
$oProgressBar->finish();
```

### Simple implementation

```php
// 10 is the max value -> a new symbol every new iteration
$oProgressBar = new ProgressBar(10);
$oProgressBar->start('My progress bar');

// Advances to 1 each iteration
foreach (range(1, 10) as $range) {
    $oProgressBar->advance();
}

  My progress bar
  1 / 10 [#         ]
  2 / 10 [##        ]
  ...
  10 / 10 [##########]
```