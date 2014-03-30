Fixtures
========

Fixtures are important part of testing. Their main purpose is to set up the environment in a fixed/known state
so that your tests are repeatable and run in an expected way. Yii provides a fixture framework that allows
you to define your fixtures precisely and use them easily.

A key concept in the Yii fixture framework is the so-called *fixture objects*. A fixture object represents
a particular aspect of a test environment and is an instance of [[yii\test\Fixture]] or its child class. For example,
you may use `UserFixture` to make sure the user DB table contains a fixed set of data. You load one or multiple
fixture objects before running a test and unload them when finishing.

A fixture may depend on other fixtures, specified via its [[yii\test\Fixture::depends]] property.
When a fixture is being loaded, the fixtures it depends on will be automatically loaded BEFORE the fixture;
and when the fixture is being unloaded, the dependent fixtures will be unloaded AFTER the fixture.


Defining a Fixture
------------------

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


The fixture data for an `ActiveFixture` fixture is usually provided in a file located at `FixturePath/data/TableName.php`,
where `FixturePath` stands for the directory containing the fixture class file, and `TableName`
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

In the above, we have shown how to define a fixture about a DB table. To define a fixture not related with DB
(e.g. a fixture about certain files and directories), you may extend from the more general base class
[[yii\test\Fixture]] and override the [[yii\test\Fixture::load()|load()]] and [[yii\test\Fixture::unload()|unload()]] methods.


Using Fixtures
--------------

If you are using [CodeCeption](http://codeception.com/) to test your code, you should consider using
the `yii2-codeception` extension which has the built-in support for loading and accessing fixtures.
If you are using other testing frameworks, you may use [[yii\test\FixtureTrait]] in your test cases
to achieve the same goal.

In the following we will describe how to write a `UserProfile` unit test class using `yii2-codeception`.

In your unit test class extending [[yii\codeception\DbTestCase]] or [[yii\codeception\TestCase]],
declare which fixtures you want to use in the [[yii\test\FixtureTrait::fixtures()|fixtures()]] method. For example,

```php
namespace app\tests\unit\models;

use yii\codeception\DbTestCase;
use app\tests\fixtures\UserProfileFixture;

class UserProfileTest extends DbTestCase
{
    public function fixtures()
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
fixtures that are applied to ALL or many test cases. An example is [[yii\test\InitDbFixture]] which does
two things:

* Perform some common initialization tasks by executing a script located at `@app/tests/fixtures/initdb.php`;
* Disable the database integrity check before loading other DB fixtures, and re-enable it after other DB fixtures are unloaded.

Using global fixtures is similar to using non-global ones. The only difference is that you declare these fixtures
in [[yii\codeception\TestCase::globalFixtures()]] instead of `fixtures()`. When a test case loads fixtures, it will
first load global fixtures and then non-global ones.

By default, [[yii\codeception\DbTestCase]] already declares `InitDbFixture` in its `globalFixtures()` method.
This means you only need to work with `@app/tests/fixtures/initdb.php` if you want to do some initialization work
before each test. You may otherwise simply focus on developing each individual test case and the corresponding fixtures.


Organizing Fixture Classes and Data Files
-----------------------------------------

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
> If you are extending for [[yii\mongodb\ActiveFixture]] for MongoDB fixtures, you should use collection names as the file names.

The similar hierarchy can be used to organize fixture class files. Instead of using `data` as the root directory, you may
want to use `fixtures` as the root directory to avoid conflict with the data files.


Summary
-------

In the above, we have described how to define and use fixtures. Below we summarize the typical workflow
of running unit tests related with DB:

1. Use `yii migrate` tool to upgrade your test database to the latest version;
2. Run a test case:
   - Load fixtures: clean up the relevant DB tables and populate them with fixture data;
   - Perform the actual test;
   - Unload fixtures.
3. Repeat Step 2 until all tests finish.

