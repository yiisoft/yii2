Faker Extension for Yii 2
===============================

This extension provides a `Faker` fixture command for Yii 2.

To use this extension,  simply add the following code in your application configuration (console.php):

```php
'controllerMap'	=>  [
	'faker:fixture' =>	[
	'class'	    =>	'yii\faker\FixtureController',
	],
],
```
To start using this command you need to be familiar (read guide) for the Faker library and
generate fixtures template files, according to the given format:

```php
#users.php file under template path (by default ```@tests/unit/fixtures/templates```)
return [
	'table_column0'	=>	'faker_formatter',
	...
	'table_columnN'	=>	'other_faker_formatter
];
```

After you prepared needed templates for tables you can simply generate your fixtures via command

```php
#generate fixtures for the users table based on users fixture template
php yii faker:fixture/generate users
```

In the code above "users" is template name, after this command run, new file named same as template
will be created under the fixtures path (by default ```@tests/unit/fixtures```) folder.
You can generate fixtures for all templates by specifying keyword "all_fixtures"

```php
php yii faker:fixture/generate all_fixtures
```

This command will generate fixtures for all template files that are stored under template path and 
store fixtures under fixtures path with file names same as templates names.
You can specify how many fixtures per file you need by the second parameter. In the code below we generate
all fixtures and in each file there will be 3 rows (fixtures).

```php
php yii faker:fixture/generate all_fixtures 3
```
You can specify different options of this command:

```php
#generate fixtures in russian languge
php yii faker:fixture/generate users 5 --language='ru_RU'

#read templates from the other path
php yii faker:fixture/generate all_fixtures --templatePath='@app/path/to/my/custom/templates'

#generate fixtures into other folders, but be sure that this folders exists or you will get notice about that.
php yii faker:fixture/generate all_fixtures --fixturesPath='@tests/unit/fixtures/subfolder1/subfolder2/subfolder3'
```

You also can create your own data providers for custom tables fields, see Faker library guide for more info (https://github.com/fzaninotto/Faker);
After you created custom provider, for example:

```php
class Book extends \Faker\Provider\Base
{
	public function title($nbWords = 5)
	{
		$sentence = $this->generator->sentence($nbWords);
		return substr($sentence, 0, strlen($sentence) - 1);
	}

	public function ISBN()
	{
		return $this->generator->randomNumber(13);
	}

 }
```

You can use it by adding it to the $providers property of the current command. In your console.php config:

```php
'controllerMap'	=>	[
	'faker:fixture'		=>	[
		'class'			=>	'yii\faker\FixtureController',
		'providers'		=>	[
			'app\tests\unit\faker\providers\Book',
		],
	],
]
```

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-faker "*"
```

or add

```json
"yiisoft/yii2-faker": "*"
```

to the require section of your composer.json.
