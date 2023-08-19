Yii 2 Core Framework Code Style
===============================

The following code style is used for Yii 2.x core and official extensions development. If you want to pull-request code
into the core, consider using it. We aren't forcing you to use this code style for your application. Feel free to choose
what suits you better.

You can get a config for CodeSniffer here: https://github.com/yiisoft/yii2-coding-standards

## 1. Overview

Overall we're using [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
compatible style so everything that applies to
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) is applied to our code
style as well.

- Files MUST use either `<?php` or `<?=` tags.
- There should be a newline at the end of file.
- Files MUST use only UTF-8 without BOM for PHP code.
- Code MUST use 4 spaces for indenting, not tabs.
- Class names MUST be declared in `StudlyCaps`.
- Class constants MUST be declared in all upper case with underscore separators.
- Method names MUST be declared in `camelCase`.
- Property names MUST be declared in `camelCase`.
- Property names MUST start with an initial underscore if they are private.
- Always use `elseif` instead of `else if`.

## 2. Files

### 2.1. PHP Tags

- PHP code MUST use `<?php ?>` or `<?=` tags; it MUST NOT use the other tag variations such as `<?`.
- In case file contains PHP only it should not have trailing `?>`.
- Do not add trailing spaces to the end of the lines.
- Any file that contains PHP code should end with the extension `.php`.

### 2.2. Character Encoding

PHP code MUST use only UTF-8 without BOM.

## 3. Class Names

Class names MUST be declared in `StudlyCaps`. For example, `Controller`, `Model`.

## 4. Classes

The term "class" refers to all classes and interfaces here.

- Classes should be named using `StudlyCase`.
- The brace should always be written on the line underneath the class name.
- Every class must have a documentation block that conforms to the PHPDoc.
- All code in a class must be indented with 4 spaces.
- There should be only one class in a single PHP file.
- All classes should be namespaced.
- Class name should match file name. Class namespace should match directory structure.

```php
/**
 * Documentation
 */
class MyClass extends \yii\base\BaseObject implements MyInterface
{
    // code
}
```

### 4.1. Constants

Class constants MUST be declared in all upper case with underscore separators.
For example:

```php
<?php
class Foo
{
    const VERSION = '1.0';
    const DATE_APPROVED = '2012-06-01';
}
```
### 4.2. Properties

- When declaring public class members specify `public` keyword explicitly.
- Public and protected variables should be declared at the top of the class before any method declarations.
  Private variables should also be declared at the top of the class but may be added right before the methods
  that are dealing with them in cases where they are only related to a small subset of the class methods.
- The order of property declaration in a class should be ascending based on their visibility: from public over protected to private.
- There are no strict rules for ordering properties that have the same visibility.
- For better readability there should be no blank lines between property declarations and two blank lines
  between property and method declaration sections. One blank line should be added between the different visibility groups.
- Private variables should be named like `$_varName`.
- Public class members and standalone variables should be named using `$camelCase`
  with first letter lowercase.
- Use descriptive names. Variables such as `$i` and `$j` are better not to be used.

For example:

```php
<?php
class Foo
{
    public $publicProp1;
    public $publicProp2;

    protected $protectedProp;

    private $_privateProp;


    public function someMethod()
    {
        // ...
    }
}
```

### 4.3. Methods

- Functions and methods should be named using `camelCase` with first letter lowercase.
- Name should be descriptive by itself indicating the purpose of the function.
- Class methods should always declare visibility using `private`, `protected` and
  `public` modifiers. `var` is not allowed.
- Opening brace of a function should be on the line after the function declaration.

```php
/**
 * Documentation
 */
class Foo
{
    /**
     * Documentation
     */
    public function bar()
    {
        // code
        return $value;
    }
}
```

### 4.4 PHPDoc blocks

 - `@param`, `@var`, `@property` and `@return` must declare types as `bool`, `int`, `string`, `array` or `null`.
   You can use a class names as well such as `Model` or `ActiveRecord`.
 - For a typed arrays use `ClassName[]`.
 - The first line of the PHPDoc must describe the purpose of the method.
 - If method checks something (`isActive`, `hasClass`, etc) the first line should start with `Checks whether`.
 - `@return` should explicitly describe what exactly will be returned.

```php
/**
 * Checks whether the IP is in subnet range
 *
 * @param string $ip an IPv4 or IPv6 address
 * @param int $cidr the CIDR length
 * @param string $range subnet in CIDR format e.g. `10.0.0.0/8` or `2001:af::/64`
 * @return bool whether the IP is in subnet range
 */
 private function inRange($ip, $cidr, $range)
 {
   // ...
 }
```

### 4.5 Constructors

- `__construct` should be used instead of PHP 4 style constructors.

## 5 PHP

### 5.1 Types

- All PHP types and values should be used lowercase. That includes `true`, `false`, `null` and `array`.

Changing type of an existing variable is considered as a bad practice. Try not to write such code unless it is really necessary.


```php
public function save(Transaction $transaction, $argument2 = 100)
{
    $transaction = new Connection; // bad
    $argument2 = 200; // good
}
```

### 5.2 Strings

- If string doesn't contain variables or single quotes, use single quotes.

```php
$str = 'Like this.';
```

- If string contains single quotes you can use double quotes to avoid extra escaping.

#### Variable substitution

```php
$str1 = "Hello $username!";
$str2 = "Hello {$username}!";
```

The following is not permitted:

```php
$str3 = "Hello ${username}!";
```

#### Concatenation

Add spaces around dot when concatenating strings:

```php
$name = 'Yii' . ' Framework';
```

When string is long format is the following:

```php
$sql = "SELECT *"
    . "FROM `post` "
    . "WHERE `id` = 121 ";
```

### 5.3 arrays

For arrays we're using PHP 5.4 short array syntax.

#### Numerically indexed

- Do not use negative numbers as array indexes.

Use the following formatting when declaring array:

```php
$arr = [3, 14, 15, 'Yii', 'Framework'];
```

If there are too many elements for a single line:

```php
$arr = [
    3, 14, 15,
    92, 6, $test,
    'Yii', 'Framework',
];
```

#### Associative

Use the following format for associative arrays:

```php
$config = [
    'name' => 'Yii',
    'options' => ['usePHP' => true],
];
```

### 5.4 control statements

- Control statement condition must have single space before and after parenthesis.
- Operators inside of parenthesis should be separated by spaces.
- Opening brace is on the same line.
- Closing brace is on a new line.
- Always use braces for single line statements.

```php
if ($event === null) {
    return new Event();
}
if ($event instanceof CoolEvent) {
    return $event->instance();
}
return null;


// the following is NOT allowed:
if (!$model && null === $event)
    throw new Exception('test');
```

Prefer avoiding `else` after `return` where it makes sense.
Use [guard conditions](https://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html).

```php
$result = $this->getResult();
if (empty($result)) {
    return true;
} else {
    // process result
}
```

is better as

```php
$result = $this->getResult();
if (empty($result)) {
   return true;
}

// process result
```

#### switch

Use the following formatting for switch:

```php
switch ($this->phpType) {
    case 'string':
        $a = (string) $value;
        break;
    case 'integer':
    case 'int':
        $a = (int) $value;
        break;
    case 'boolean':
        $a = (bool) $value;
        break;
    default:
        $a = null;
}
```

### 5.5 function calls

```php
doIt(2, 3);

doIt(['a' => 'b']);

doIt('a', [
    'a' => 'b',
    'c' => 'd',
]);
```

### 5.6 Anonymous functions (lambda) declarations

Note space between `function`/`use` tokens and open parenthesis:

```php
// good
$n = 100;
$sum = array_reduce($numbers, function ($r, $x) use ($n) {
    $this->doMagic();
    $r += $x * $n;
    return $r;
});

// bad
$n = 100;
$mul = array_reduce($numbers, function($r, $x) use($n) {
    $this->doMagic();
    $r *= $x * $n;
    return $r;
});
```

Documentation
-------------

- Refer to [phpDoc](https://phpdoc.org/) for documentation syntax.
- Code without documentation is not allowed.
- All class files must contain a "file-level" docblock at the top of each file
  and a "class-level" docblock immediately above each class.
- There is no need to use `@return` if method does return nothing.
- All virtual properties in classes that extend from `yii\base\BaseObject`
  are documented with an `@property` tag in the class doc block.
  These annotations are automatically generated from the `@return` or `@param`
  tag in the corresponding getter or setter by running `./build php-doc` in the build directory.
  You may add an `@property` tag
  to the getter or setter to explicitly give a documentation message for the property
  introduced by these methods when description differs from what is stated
  in `@return`. Here is an example:

  ```php
    <?php
    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     * ...
     */
    public function getErrors($attribute = null)
  ```

#### File

```php
<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */
```

#### Class

```php
/**
 * Component is the base class that provides the *property*, *event* and *behavior* features.
 *
 * @include @yii/docs/base-Component.md
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends \yii\base\BaseObject
```


#### Function / method

```php
/**
 * Returns the list of attached event handlers for an event.
 * You may manipulate the returned [[Vector]] object by adding or removing handlers.
 * For example,
 *
 * ```
 * $component->getEventHandlers($eventName)->insertAt(0, $eventHandler);
 * ```
 *
 * @param string $name the event name
 * @return Vector list of attached event handlers for the event
 * @throws Exception if the event is not defined
 */
public function getEventHandlers($name)
{
    if (!isset($this->_e[$name])) {
        $this->_e[$name] = new Vector;
    }
    $this->ensureBehaviors();
    return $this->_e[$name];
}
```

#### Markdown

As you can see in the examples above we use markdown to format the phpDoc comments.

There is additional syntax for cross linking between classes, methods and properties in the documentation:

- `[[canSetProperty]]` will create a link to the `canSetProperty` method or property of the same class.
- `[[Component::canSetProperty]]` will create a link to `canSetProperty` method of the class `Component` in the same namespace.
- `[[yii\base\Component::canSetProperty]]` will create a link to `canSetProperty` method of the class `Component` in namespace `yii\base`.
- `[[Component]]` will create a link to the `Component` class in the same namespace. Adding namespace to the class name is also possible here.

To give one of the above mentioned links another label than the class or method name you can use the syntax shown in the following example:

```
... as displayed in the [[header|header cell]].
```

The part before the | is the method, property or class reference while the part after | is the link label.

It is also possible to link to the Guide using the following syntax:

```markdown
[link to guide](guide:file-name.md)
[link to guide](guide:file-name.md#subsection)
```


#### Comments

- One-line comments should be started with `//` and not `#`.
- One-line comment should be on its own line.

Additional rules
----------------

### `=== []` vs `empty()`

Use `empty()` where possible.

### multiple return points

Return early when conditions nesting starts to get cluttered. If the method is short it doesn't matter.

### `self` vs. `static`

Always use `static` except the following cases:

- accessing constants MUST be done via `self`: `self::MY_CONSTANT`
- accessing private static properties MUST be done via `self`: `self::$_events`
- It is allowed to use `self` for method calls where it makes sense such as recursive call to current implementation instead of extending classes implementation.

### value for "don't do something"

Properties allowing to configure component not to do something should accept value of `false`. `null`, `''`, or `[]` should not be assumed as such.

### Directory/namespace names

- use lower case
- use plural form for nouns which represent objects (e.g. validators)
- use singular form for names representing relevant functionality/features (e.g. web)
- prefer single word namespaces
- if single word isn't suitable, use camelCase

