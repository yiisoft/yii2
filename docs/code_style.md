Yii2 core code style
====================

### Brackets

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


Proposals
---------

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