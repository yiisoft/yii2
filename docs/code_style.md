Yii2 core code style
====================


Proposals
---------

### Brackets

It's better to be consistent with brackets not to remember where should we use
newline and where not:

~~~
class MyClass
{
	public function myClassMethod()
	{
		if($x)
		{
			// do it
		}
		else
		{
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