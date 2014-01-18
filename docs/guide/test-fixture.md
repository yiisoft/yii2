Fixtures
========

Fixtures are important part of testing. Their main purpose is to set up the environment in a fixed/known state
so that your tests are repeatable and run in an expected way. Yii provides a fixture framework that allows
you to define your fixtures precisely and use them easily.

A key concept in the Yii fixture framework is the so-called *fixture objects*. A fixture object is an instance
of [[yii\test\Fixture]] or its child class. It represents a particular aspect of a test environment. For example,
you may define `UserFixture` to create the user table and populate it with some known data. You load one or multiple
fixture objects before running a test and unload them when finishing.

A fixture may depend on other fixtures, specified via its [[yii\test\Fixture::depends]] property.
When a fixture is being loaded, the fixtures it depends on will be automatically loaded BEFORE the fixture;
and when the fixture is being unloaded, the dependent fixtures will be unloaded AFTER the fixture.


Defining a Fixture
------------------

To define a fixture, create a new class by extending [[yii\test\Fixture]] or [[yii\test\ActiveFixture]].
The former is best suited for general purpose fixtures, while the latter has enhanced features specifically
designed to work with database and ActiveRecord.

If you extend from [[yii\test\Fixture]], you should normally override the [[yii\test\Fixture::load()]] method
with your custom code of setting up the test environment (e.g. creating specific directories or files).
In the following, we will mainly describe how to define a database fixture by extending [[yii\test\ActiveFixture]].

Each `ActiveFixture` is about preparing a DB table for testing purpose. You may specify the table
by setting either the [[yii\test\ActiveFixture::tableName]] property or the [[yii\test\ActiveFixture::modelClass]]
property. If the latter, the table name will be taken from the `ActiveRecord` class specified by `modelClass`.

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
	public $modelClass = 'app\models\User';
}
```

Next, you should override [[yii\test\ActiveFixture::loadSchema()]] to create the table. You may wonder why we need
to create the table when loading a fixture and why we do not work with a database which already has the table. This
is because preparing a complete test database is often very time consuming and in most test cases, only a very tiny part
of the database is touched. So the idea here is to create the table only when it is needed by the test.

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
	public $modelClass = 'app\models\User';

	protected function loadSchema()
	{
		$this->createTable('tbl_user', [
			'username' => 'string not null',
			'email' => 'string not null',
			...
		]);
	}
}
```

Lastly, you should provide the fixture data in a file located at `FixturePath/data/TableName.php`,
where `FixturePath` stands for the directory containing the fixture class file, and `TableName`
is the name of the table associated with the fixture. In the example above, the file should be
`@app/tests/fixtures/data/tbl_user.php`. The data file should return an array of data rows
to be inserted into the user table. For example,

```php
<?php
return [
	'user1' => [
		'username' => 'lmayert',
		'email' => 'strosin.vernice@jerde.com',
		'auth_key' => 'K3nF70it7tzNsHddEiq0BZ0i-OU8S3xV',
		'password' => '$2y$13$WSyE5hHsG1rWN2jV8LRHzubilrCLI5Ev/iK0r3jRuwQEs2ldRu.a2',
	],
	'user2' => [
		'username' => 'napoleon69',
		'email' => 'aileen.barton@heaneyschumm.com',
		'auth_key' => 'dZlXsVnIDgIzFgX4EduAqkEPuphhOh9q',
		'password' => '$2y$13$kkgpvJ8lnjKo8RuoR30ay.RjDf15bMcHIF7Vz1zz/6viYG5xJExU6',
	],
];
```

You may give an alias to a row so that later in your test, you may refer to the row via the alias. In the above example,
the two rows are aliased as `user1` and `user2`, respectively.

Also, you do not need to specify the data for auto-incremental columns. Yii will automatically fill the actual
values into the rows when the fixture is being loaded.

> Tip: You may customize the location of the data file by setting the [[yii\test\ActiveFixture::dataFile]] property.
> You may also override [[yii\test\ActiveFixture::getData()]] to provide the data.

As we described earlier, a fixture may depend on other fixtures. For example, `UserProfileFixture` depends on `UserFixture`
because the user profile table contains a foreign key pointing to the user table.
The dependency is specified via the [[yii\test\Fixture::depends]] property, like the following,

```php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserProfileFixture extends ActiveFixture
{
	public $modelClass = 'app\models\UserProfile';
	public $depends = ['app\tests\fixtures\UserFixture'];
}
```

Note that in the above, besides `app\tests\fixtures\UserFixture`, the dependency of `UserProfileFixture` also includes
`yii\test\DbFixture`. This is required by all `ActiveFixture` classes which set `yii\test\DbFixture` as the default value
of the `depends` property. The `DbFixture` class is responsible for toggling database integrity check and executing
an initialization script. Without `DbFixture`, you may not be able to freely inserting or deleting rows in a table
due to various DB constraints.


Using Fixtures
--------------

Yii provides [[yii\test\FixtureTrait]] which can be plugged into your test classes to let you easily load and access
fixtures. More often you would develop your test cases by using the `yii2-codeception` extension
which uses [[yii\test\FixtureTrait]] and has the built-in support for the loading and accessing fixtures.
In the following we will describe how to write a `UserProfile` unit test class using `yii2-codeception`.

In your unit test class extending [[yii\codeception\DbTestCase]] (or [[yii\codeception\TestCase]] if you are NOT
testing DB-related features), declare which fixtures you want to use in the [[yii\testFixtureTrait::fixtures()|fixtures()]] method.
For example,

```php
namespace app\tests\unit\models;

use yii\codeception\DbTestCase;
use app\tests\fixtures\UserProfileFixture;

class UserProfileTest extends DbTestCase
{
	protected function fixtures()
	{
		return [
			'profiles' => UserProfileFixture::className(),
		];
	}

	// ...test methods...
}
```

The fixtures listed in the `fixtures()` method will be automatically loaded before running every test method
in the test case and unloaded after finishing every test method. And as we described before, when a fixture is
being loaded, all its dependent fixtures will be automatically loaded first. In the above example, because
`UserProfileFixture` depends on `UserFixture`, when running any test method in the test class,
two fixtures will be loaded sequentially: `UserFixture` and `UserProfileFixture`.

When specifying fixtures in `fixtures()`, you may use either a class name or a configuration array to refer to
a fixture. The configuration array will let you customize the fixture properties when the fixture is loaded.

You may also assign an alias to a fixture. In the above example, the `UserProfileFixture` is aliased as `profiles`.
In the test methods, you may then access a fixture object using its alias. For example, `$this->profiles` will
return the `UserProfileFixture` object.

Because `UserProfileFixture` extends from `ActiveFixture`, you may further use the following syntax to access
the data provided by the fixture:

```php
// returns the data row aliased as 'user1'
$row = $this->profiles['user1'];
// returns the UserProfile model corresponding to the data row aliased as 'user1'
$profile = $this->profiles('user1');
// traverse every data row in the fixture
foreach ($this->profiles as $row) ...
```

> Info: `$this->profiles` is still of `UserProfileFixture` type. The above access features are implemented
> through PHP magic methods.


Defining and Using Global Fixtures
----------------------------------

The fixtures described above are mainly used by individual test cases. In most cases, you also need some global
fixtures that are applied to ALL or many test cases. An example is [[yii\test\InitDbFixture]] which is used to
set up a skeleton test database and toggle database integrity checks when applying other DB fixtures.
This fixture will try to execute a script located at `@app/tests/fixtures/initdb.php`. In this script, you may,
for example, load a basic DB dump containing the minimal set of tables, etc.

Using global fixtures is similar to using non-global ones. The only difference is that you declare these fixtures
in [[yii\codeception\TestCase::globalFixtures()]] instead of `fixtures()`. When a test case load fixtures, it will
first load global fixtures and then non-global ones.

By default, [[yii\codeception\DbTestCase]] already declares `InitDbFixture` in its `globalFixtures()` method.
