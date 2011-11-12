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