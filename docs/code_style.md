Yii2 code standard
==================

This code standard is used for all the Yii2 core classes and can be applied to
your application in order to achieve consistency among your team. Also it will
help in case you want to opensource code.

PHP file formatting
-------------------

### General

- Do not end file with `?>` if it contains PHP code only.
- Do not use `<?`. Use `<?php` instead.
- Files should be encoded in UTF-8.
- Any file that contains PHP code should end with the extension `.php`.
- Do not add trailing spaces to the end of the lines.

#### Indentation

All code must be indented with tabs. That includes both PHP and JavaScript code.

#### Maximum Line Length

We're not strictly limiting maximum line length but sticking to 80 characters
where possible.

### PHP types

All PHP types and values should be used lowercase. That includes `true`, `false`,
`null` and `array`.

### Strings

- If string doesn't contain variables or single quotes, use single quotes.

~~~
$str = 'Like this.';
~~~

- If string contains single quotes you can use double quotes to avoid extra escaping.
- You can use the following forms of variable substitution:

~~~
$str1 = "Hello $username!";
$str2 = "Hello {$username}!";
~~~

The following is not permitted:

~~~
$str3 = "Hello ${username}!";
~~~

### String concatenation

Add spaces around dot when concatenating strings:

~~~
$name = 'Yii' . ' Framework';
~~~

When string is long format is the following:

~~~
$sql = "SELECT *"
     . "FROM `post` "
     . "WHERE `id` = 121 ";
~~~


### Numerically indexed arrays

- Do not use negative numbers as array indexes.

Use the following formatting when declaring array:

~~~
$arr = array(3, 14, 15, 'Yii', 'Framework');
~~~

If there are too many elements for a single line:

~~~
$arr = array(
	3, 14, 15,
	92, 6, $test,
	'Yii', 'Framework',
);
~~~

### Associative arrays

Use the following format for associative arrays:

~~~
$config = array(
	'name'  => 'Yii',
	'options' => array(
		'usePHP' => true,
	),
);
~~~

### Classes

- Classes should be named using `CamelCase`.
- The brace should always be written on the line underneath the class name.
- Every class must have a documentation block that conforms to the PHPDoc.
- All code in a class must be indented with a single tab.
- There should be only one class in a single PHP file.
- All classes should be namespaced.
- Class name should match file name. Class namespace should match directory structure.

~~~
/**
 * Documentation
 */
class MyClass extends \yii\Object implements MyInterface
{
	// code
}
~~~


### Class members and variables

- When declaring public class members specify `public` keyword explicitly.
- Variables should be declared at the top of the class before any method declarations.
- Private and protected variables should be named like `$_varName`.
- Public class members and standalone variables should be named using `$camelCase`
  with first letter lowercase.
- Use descriptive names. Variables such as `$i` and `$j` are better not to be used.

### Constants

Both class level constants and global constants should be named in uppercase. Words
are separated by underscore.

~~~
class User {
	const STATUS_ACTIVE = 1;
	const STATUS_BANNED = 2;
}
~~~

It's preferable to define class level constants rather than global ones.

### Functions and methods

- Functions and methods should be named using `camelCase` with first letter lowercase.
- Name should be descriptive by itself indicating the purpose of the function.
- Class methods should always declare visibility using `private`, `protected` and
  `public` modifiers. `var` is not allowed.
- Opening brace of a function should be on the line after the function declaration.

~~~
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
~~~

Use type hinting where possible:

~~~
public function __construct(CDbConnection $connection)
{
	$this->connection = $connection;
}
~~~

### Function and method calls

~~~
doIt(2, 3);

doIt(array(
	'a' => 'b',
));

doIt('a', array(
	'a' => 'b',
));
~~~

### Control statements

- Control statement condition must have single space before and after parenthesis.
- Operators inside of parenthesis should be separated by spaces.
- Opening brace is on the same line.
- Closing brace is on a new line.
- Always use braces for single line statements.

~~~
if ($event === null) {
	return new Event();
} elseif ($event instanceof CoolEvent) {
	return $event->instance();
} else {
	return null;
}

// the following is NOT allowed:
if(!$model)
	throw new Exception('test');
~~~


### Switch

Use the following formatting for switch:

~~~
switch ($this->phpType) {
	case 'string':
		$a = (string)$value;
		break;
	case 'integer':
	case 'int':
		$a = (integer)$value;
		break;
	case 'boolean':
		$a = (boolean)$value;
		break;
	default:
		$a = null;
}
~~~

### Code documentation

- Refer ot [phpDoc](http://phpdoc.org/) for documentation syntax.
- Code without documentation is not allowed.
- All class files must contain a "file-level" docblock at the top of each file
  and a "class-level" docblock immediately above each class.
- There is no need to use `@return` if method does return nothing.

#### File

~~~
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
~~~

#### Class

~~~
/**
 * Component is the base class that provides the *property*, *event* and *behavior* features.
 *
 * @include @yii/docs/base-Component.md
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends \yii\base\Object
~~~


#### Function / method

~~~
/**
 * Returns the list of attached event handlers for an event.
 * You may manipulate the returned [[Vector]] object by adding or removing handlers.
 * For example,
 *
 * ~~~
 * $component->getEventHandlers($eventName)->insertAt(0, $eventHandler);
 * ~~~
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
~~~

#### Comments

- One-line comments should be started with `//` and not `#`.
- One-line comment should be on its own line.

Yii application naming conventions
----------------------------------



Other library and framework standards
-------------------------------------

It's good to be consistent with other frameworks and libraries whose components
will be possibly used with Yii2. That's why when there are no objective reasons
to use different style we should use one that's common among most of the popular
libraries and frameworks.

That's not only about PHP but about JavaScript as well. Since we're using jQuery
a lot it's better to be consistent with its style as well.

Application style consistency is much more important than consistency with other frameworks and libraries.

- [Symfony 2](http://symfony.com/doc/current/contributing/code/standards.html)
- [Zend Framework 1](http://framework.zend.com/manual/en/coding-standard.coding-style.html)
- [Zend Framework 2](http://framework.zend.com/wiki/display/ZFDEV2/Coding+Standards)
- [Pear](http://pear.php.net/manual/en/standards.php)
- [jQuery](http://docs.jquery.com/JQuery_Core_Style_Guidelines)