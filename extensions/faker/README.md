Faker Extension for Yii 2
=========================

This extension provides a [`Faker`](https://github.com/fzaninotto/Faker) fixture command for Yii 2.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-faker "*"
```

or add

```json
"yiisoft/yii2-faker": "*"
```

to the require section of your composer.json.


Usage
-----

To use this extension,  simply add the following code in your application configuration (console.php):

```php
'controllerMap' => [
    'fixture' => [
        'class' => 'yii\faker\FixtureController',
    ],
],
```

Define a `tests` alias in your console config. For example, for the `basic` application template, this should be added
to the `console.php` configuration: `Yii::setAlias('tests', __DIR__ . '/../tests');`
To start using this command you need to be familiar (read guide) with the [Faker](https://github.com/fzaninotto/Faker) library and
generate fixture template files, according to the given format:

```php
// users.php file under template path (by default @tests/unit/templates/fixtures)
/**
 * @var $faker \Faker\Generator
 * @var $index integer
 */
return [
    'name' => $faker->firstName,
    'phone' => $faker->phoneNumber,
    'city' => $faker->city,
    'password' => Yii::$app->getSecurity()->generatePasswordHash('password_' . $index),
    'auth_key' => Yii::$app->getSecurity()->generateRandomString(),
    'intro' => $faker->sentence(7, true),  // generate a sentence with 7 words
];
```

As you can see, the template file is just a regular PHP script. The script should return an array of key-value
pairs, where the keys represent the table column names and the values the corresponding value. When you run
the `fixture/generate` command, the script will be executed once for every data row being generated.
In this script, you can use the following two predefined variables:

* `$faker`: the Faker generator instance
* `$index`: the current fixture index. For example if user need to generate 3 fixtures for user table, it will be 0..2.

With such a template file, you can generate your fixtures using the commands like the following:

```
# generate fixtures for the users table based on users fixture template
php yii fixture/generate User

# also a short version of this command ("generate" action is default)
php yii fixture User

# to generate several fixture data files
php yii fixture User Profile Team
```

In the code above `users` is template name. After running this command, a new file with the same template name
will be created under the fixture path in the `@tests/unit/fixtures`) folder.

```
php yii fixture/generate-all
```

This command will generate fixtures for all template files that are stored under template path and 
store fixtures under fixtures path with file names same as templates names.
You can specify how many fixtures per file you need by the `--count` option. In the code below we generate
all fixtures and in each file there will be 3 rows (fixtures).

```
php yii fixture/generate-all --count=3
```
You can specify different options of this command:

```
# generate fixtures in russian language
php yii fixture/generate User --count=5 --language='ru_RU'

# read templates from the other path
php yii fixture/generate-all --templatePath='@app/path/to/my/custom/templates'

# generate fixtures into other directory.
php yii fixture/generate-all --fixtureDataPath='@tests/acceptance/fixtures/data'
```

You also can create your own data providers for custom tables fields, see [Faker](https://github.com/fzaninotto/Faker) library guide for more info;
After you created custom provider, for example:

```php
class Book extends \Faker\Provider\Base
{
    public function title($nbWords = 5)
    {
        $sentence = $this->generator->sentence($nbWords);
        return mb_substr($sentence, 0, mb_strlen($sentence) - 1);
    }

    public function ISBN()
    {
        return $this->generator->randomNumber(13);
    }

 }
```

You can use it by adding it to the `$providers` property of the current command. In your console.php config:

```php
'controllerMap' => [
    'fixture' => [
        'class' => 'yii\faker\FixtureController',
        'providers' => [
            'app\tests\unit\faker\providers\Book',
        ],
    ],
]
```
