Yii2 code standard
==================

This code standard is used for all the Yii2 core classes and can be applied to
your application in order to achieve consistency among your team. Also it will
help in case you want to opensource code.


PHP file formatting
-------------------

### General

- Do not use `?>` for files containing PHP code only.
- Files should be encoded in UTF-8.

### Indentation

All major PHP frameworks and libraries are using spaces. Common number is four:

- Symfony 2: four spaces
- Zend Framework 1: four spaces
- Zend Framework 2: four spaces
- Pear: four spaces


### Maximum Line Length

We're not strictly limiting maximum line length but sticking to 80 characters
where possible.

### Line Termination



### Brackets

It's better to be consistent with brackets not to remember where should we use
newline and where not:

~~~
class MyClass
{
	public function myClassMethod()
	{
		if($x) {
			// do it
		} else {
			// some code
		}
	}
}
~~~

Use brackets even for one line `if`s.

> I chose to use the style as shown in Component.php because I want to make the
> curly brackets consistent with array brackets regarding newlines. Similar coding
> style is also used in Symfony 2.

### Use type hinting like

~~~
public function __construct(CDbConnection $connection)
{
	$this->connection = $connection;
}
~~~

instead of

~~~
public function __construct($connection)
{
	$this->connection = $connection;
}
~~~

### Other library and framework standards

It's good to be consistent with other frameworks and libraries whose components
will be possibly used with Yii2. That's why when there are no objective reasons
to use different style we should use one that's common among most of the popular
libraries and frameworks.

That's not only about PHP but about JavaScript as well. Since we're using jQuery
a lot it's better to be consistent with its style as well.

- [Symfony 2](http://symfony.com/doc/current/contributing/code/standards.html)
- [Zend Framework 1](http://framework.zend.com/manual/en/coding-standard.coding-style.html)
- [Zend Framework 2](http://framework.zend.com/wiki/display/ZFDEV2/Coding+Standards)
- [Pear](http://pear.php.net/manual/en/standards.php)
- [jQuery](http://docs.jquery.com/JQuery_Core_Style_Guidelines)