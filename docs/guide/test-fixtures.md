Fixtures
========

Fixtures are an important part of testing. Their main purpose is to set up the environment in a fixed/known state
so that your tests are repeatable and run in an expected way. Yii provides a fixture framework that allows
you to define your fixtures precisely and use them easily both when running your tests with Codeception
and independently.

A key concept in the Yii fixture framework is the so-called *fixture object*. A fixture object represents
a particular aspect of a test environment and is an instance of [[yii\test\Fixture]] or its child class. For example,
you may use `UserFixture` to make sure the user DB table contains a fixed set of data. You load one or multiple
fixture objects before running a test and unload them when finishing.

A fixture may depend on other fixtures, specified via its [[yii\test\Fixture::depends]] property.
When a fixture is being loaded, the fixtures it depends on will be automatically loaded BEFORE the fixture;
and when the fixture is being unloaded, the dependent fixtures will be unloaded AFTER the fixture.


## Defining a Fixture

To define a fixture, create a new class by extending [[yii\test\Fixture]] or [[yii\test\ActiveFixture]].
The former is best suited for general purpose fixtures, while the latter has enhanced features specifically
designed to work with database and ActiveRecord.

The following code defines a fixture about the `User` ActiveRecord and the corresponding user table.

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
}
```

> Tip: Each `ActiveFixture` is about preparing a DB table for testing purpose. You may specify the table
> by setting either the [[yii\test\ActiveFixture::tableName]] property or the [[yii\test\ActiveFixture::modelClass]]
> property. If the latter, the table name will be taken from the `ActiveRecord` class specified by `modelClass`.

> Note: [[yii\test\ActiveFixture]] is only suited for SQL databases. For NoSQL databases, Yii provides the following
> `ActiveFixture` classes:
>
> - Mongo DB: [[yii\mongodb\ActiveFixture]]
> - Elasticsearch: [[yii\elasticsearch\ActiveFixture]] (since version 2.0.2)


The fixture data for an `ActiveFixture` fixture is usually provided in a file located at `fixturepath/data/tablename.php`,
where `fixturepath` stands for the directory containing the fixture class file, and `tablename`
is the name of the table associated with the fixture. In the example above, the file should be
`@app/tests/fixtures/data/user.php`. The data file should return an array of data rows
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

As we described earlier, a fixture may depend on other fixtures. For example, a `UserProfileFixture` may need to depend on `UserFixture`
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

The dependency also ensures, that the fixtures are loaded and unloaded in a well-defined order. In the above example `UserFixture` will
always be loaded before `UserProfileFixture` to ensure all foreign key references exist and will be unloaded after `UserProfileFixture`
has been unloaded for the same reason.

In the above, we have shown how to define a fixture about a DB table. To define a fixture not related with DB
(e.g. a fixture about certain files and directories), you may extend from the more general base class
[[yii\test\Fixture]] and override the [[yii\test\Fixture::load()|load()]] and [[yii\test\Fixture::unload()|unload()]] methods.


## Using Fixtures

If you are using [Codeception](https://codeception.com/) to test your code, you can use the built-in support for loading
and accessing fixtures.

If you are using other testing frameworks, you may use [[yii\test\FixtureTrait]] in your
test cases to achieve the same goal.

In the following we will describe how to write a `UserProfile` unit test class using Codeception.

In your unit test class extending `\Codeception\Test\Unit` either declare fixtures you want to use in the
`_fixtures()` method or use `haveFixtures()` method of an actor directly. For example,

```php
namespace app\tests\unit\models;


use app\tests\fixtures\UserProfileFixture;

class UserProfileTest extends \Codeception\Test\Unit
{   
    public function _fixtures()
    {
        return [
            'profiles' => [
                'class' => UserProfileFixture::class,
                // fixture data located in tests/_data/user.php
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
        ];
    }

    // ...test methods...
}
```

The fixtures listed in the `_fixtures()` method will be automatically loaded before a test is executed. And as we
described before, when a fixture is being loaded, all its dependent fixtures will be automatically loaded first.
In the above example, because `UserProfileFixture` depends on `UserFixture`, when running any test method in the test
class, two fixtures will be loaded sequentially: `UserFixture` and `UserProfileFixture`.

When specifying fixtures for both `_fixtures()` and `haveFixtures()`, you may use either a class name
or a configuration array to refer to a fixture. The configuration array will let you customize
the fixture properties when the fixture is loaded.

You may also assign an alias to a fixture. In the above example, the `UserProfileFixture` is aliased as `profiles`.
In the test methods, you may then access a fixture object using its alias in `grabFixture()` method. For example,

```php
$profile = $I->grabFixture('profiles');
```

will return the `UserProfileFixture` object.

Because `UserProfileFixture` extends from `ActiveFixture`, you may further use the following syntax to access
the data provided by the fixture:

```php
// returns the UserProfile model corresponding to the data row aliased as 'user1'
$profile = $I->grabFixture('profiles', 'user1');
// traverse data in the fixture
foreach ($I->grabFixture('profiles') as $profile) ...
```

## Organizing Fixture Classes and Data Files

By default, fixture classes look for the corresponding data files under the `data` folder which is a sub-folder
of the folder containing the fixture class files. You can follow this convention when working with simple projects.
For big projects, chances are that you often need to switch different data files for the same fixture class for
different tests. We thus recommend that you organize the data files in a hierarchical way that is similar to
your class namespaces. For example,

```
# under folder tests\unit\fixtures

data\
    components\
        fixture_data_file1.php
        fixture_data_file2.php
        ...
        fixture_data_fileN.php
    models\
        fixture_data_file1.php
        fixture_data_file2.php
        ...
        fixture_data_fileN.php
# and so on
```

In this way you will avoid collision of fixture data files between tests and use them as you need.

> Note: In the example above fixture files are named only for example purpose. In real life you should name them
> according to which fixture class your fixture classes are extending from. For example, if you are extending
> from [[yii\test\ActiveFixture]] for DB fixtures, you should use DB table names as the fixture data file names;
> If you are extending from [[yii\mongodb\ActiveFixture]] for MongoDB fixtures, you should use collection names as the file names.

A similar hierarchy can be used to organize fixture class files. Instead of using `data` as the root directory, you may
want to use `fixtures` as the root directory to avoid conflict with the data files.

## Managing fixtures with `yii fixture`

Yii supports fixtures via the `yii fixture` command line tool. This tool supports:

* Loading fixtures to different storage such as: RDBMS, NoSQL, etc;
* Unloading fixtures in different ways (usually it is clearing storage);
* Auto-generating fixtures and populating it with random data.

### Fixtures data format

Lets assume we have fixtures data to load:

```
#users.php file under fixtures data path, by default @tests\unit\fixtures\data

return [
    [
        'name' => 'Chase',
        'login' => 'lmayert',
        'email' => 'strosin.vernice@jerde.com',
        'auth_key' => 'K3nF70it7tzNsHddEiq0BZ0i-OU8S3xV',
        'password' => '$2y$13$WSyE5hHsG1rWN2jV8LRHzubilrCLI5Ev/iK0r3jRuwQEs2ldRu.a2',
    ],
    [
        'name' => 'Celestine',
        'login' => 'napoleon69',
        'email' => 'aileen.barton@heaneyschumm.com',
        'auth_key' => 'dZlXsVnIDgIzFgX4EduAqkEPuphhOh9q',
        'password' => '$2y$13$kkgpvJ8lnjKo8RuoR30ay.RjDf15bMcHIF7Vz1zz/6viYG5xJExU6',
    ],
];
```
If we are using fixture that loads data into database then these rows will be applied to `users` table. If we are using nosql fixtures, for example `mongodb`
fixture, then this data will be applied to `users` mongodb collection. In order to learn about implementing various loading strategies and more, refer to official [documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixtures.md).
Above fixture example was auto-generated by `yii2-faker` extension, read more about it in these [section](#auto-generating-fixtures).
Fixture classes name should not be plural.

### Loading fixtures

Fixture classes should be suffixed by `Fixture`. By default, fixtures will be searched under `tests\unit\fixtures` namespace, you can
change this behavior with config or command options. You can exclude some fixtures due load or unload by specifying `-` before its name like `-User`.

To load fixture, run the following command:

> Note: Prior to loading data unload sequence is executed. Usually that results in cleaning up all the existing data inserted by previous fixture executions.

```
yii fixture/load <fixture_name>
```

The required `fixture_name` parameter specifies a fixture name which data will be loaded. You can load several fixtures at once.
Below are correct formats of this command:

```
// load `User` fixture
yii fixture/load User

// same as above, because default action of "fixture" command is "load"
yii fixture User

// load several fixtures
yii fixture "User, UserProfile"

// load all fixtures
yii fixture/load "*"

// same as above
yii fixture "*"

// load all fixtures except ones
yii fixture "*, -DoNotLoadThisOne"

// load fixtures, but search them in different namespace. By default namespace is: tests\unit\fixtures.
yii fixture User --namespace='alias\my\custom\namespace'

// load global fixture `some\name\space\CustomFixture` before other fixtures will be loaded.
// By default this option is set to `InitDbFixture` to disable/enable integrity checks. You can specify several
// global fixtures separated by comma.
yii fixture User --globalFixtures='some\name\space\Custom'
```

### Unloading fixtures

To unload fixture, run the following command:

```
// unload Users fixture, by default it will clear fixture storage (for example "users" table, or "users" collection if this is mongodb fixture).
yii fixture/unload User

// Unload several fixtures
yii fixture/unload "User, UserProfile"

// unload all fixtures
yii fixture/unload "*"

// unload all fixtures except ones
yii fixture/unload "*, -DoNotUnloadThisOne"

```

Same command options like: `namespace`, `globalFixtures` also can be applied to this command.

### Configure Command Globally

While command line options allow us to configure the fixture command
on-the-fly, sometimes we may want to configure the command once for all. For example, you can configure
different fixture path as follows:

```
'controllerMap' => [
    'fixture' => [
        'class' => 'yii\console\controllers\FixtureController',
        'namespace' => 'myalias\some\custom\namespace',
        'globalFixtures' => [
            'some\name\space\Foo',
            'other\name\space\Bar'
        ],
    ],
]
```

### Auto-generating fixtures

Yii also can auto-generate fixtures for you based on some template. You can generate your fixtures with different data on different languages and formats.
This feature is done by [Faker](https://github.com/fzaninotto/Faker) library and `yii2-faker` extension.
See extension [guide](https://github.com/yiisoft/yii2-faker) for more docs.

## Summary

In the above, we have described how to define and use fixtures. Below we summarize the typical workflow
of running DB related unit tests:

1. Use the `yii migrate` tool to upgrade your test database to the latest version;
2. Run a test case:
   - Load fixtures: clean up the relevant DB tables and populate them with fixture data;
   - Perform the actual test;
   - Unload fixtures.
3. Repeat Step 2 until all tests finish.
