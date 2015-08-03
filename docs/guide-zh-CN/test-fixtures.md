Fixtures(夹具)
========

Fixtures 是测试的重要组成部分。 它们的主要目的是把（代码的运行）环境初始化为一个固定/已知的状态以确保测试的可重复性并且按照预期方式运行。 Yii 提供了一个 fixture 框架，这个框架允许我们能够精确地定义我们的 fixtures 并且很简单地使用它们。

Yii 的 Fixture 框架中一个关键的概念就是所谓的 *fixture对象*。 Fixture 对象代表测试环境的一个特殊方面，是 [[yii\test\Fixture]] 或其子类的一个实例 。 例如，
你可以使用 `UserFixture` 确保 user 数据库表包含一个固定的数据集合。在你运行一个测试之前加载一个或者多个 Fixture 对象，并且在完成测试的时候卸载他们。

一个 Fixture 可以依赖其他 Fixtures ，我们可以通过指定它的 [[yii\test\Fixture::depends]] 属性设置依赖关系。
当一个 Fixture 被加载的时候,其依赖的 Fixtures 将在它加载之前被自动加载；
并且这个 Fixture 将被卸载的时候，其依赖的 Fixtures 将在它卸载之后卸载。


定义一个 Fixture
------------------

通过创建一个继承自 [[yii\test\Fixture]] 或 [[yii\test\ActiveFixture]] 的类，（我们可以）定义一个 Fixture 。
前者适用于一般用途的 Fixtures ， 而后者则具有一些特别为配合数据库（DB）和活跃记录（AR）工作而设计的一些增强功能。

下面代码定义了一个关于 `User` 活跃记录和相应 user 表的 Fixture 。

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
}
```

> 技巧：每个 `ActiveFixture` 都会准备一张数据库表用于测试。我们可以通过设定 [[yii\test\ActiveFixture::tableName]] 或者 [[yii\test\ActiveFixture::modelClass]] 属性来指定表。 如果是后者，表的名字将从通过 `modelClass` 指定 `ActiveRecord` 类获得。

> 注意: [[yii\test\ActiveFixture]] 只适用于 SQL 数据库。对于 NoSQL 数据库， Yii 提供了以下`ActiveFixture`类：
>
> - Mongo DB: [[yii\mongodb\ActiveFixture]]
> - Elasticsearch: [[yii\elasticsearch\ActiveFixture]] (since version 2.0.2)


`ActiveFixture` Fixture 的 Fixture 数据通常由一个位于 `FixturePath/data/TableName.php` 的文件提供，
 `FixturePath` 代表包含 Fixture 类文件的目录，`TableName`
是与 Fixture 相关联的表名称。在上面例子当中， 这个（ Fixture 数据）文件应该是
 `@app/tests/fixtures/data/user.php` 。 这个数据文件应该返回一个将要插入user表数据行的数组。例如，

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

你可以给行指定一个别名，这样在后期的测试当中，你可以通过别名来引用这些行。在上面例子当中，
两行的别名分别是 `user1` 和 `user2` 。

同时， 你不必指定自增列的数据。当 Fixture 被加载时，Yii 自动将实际值填充到这些列。

> 技巧：你可以通过设定 [[yii\test\ActiveFixture::dataFile]] 属性定制数据文件位置。
> 你也可以通过重写 [[yii\test\ActiveFixture::getData()]] 方法来提供数据.

前面我们提到，一个Fixture可能依赖其它Fixtures。例如， `UserProfileFixture` 可能依赖 `UserFixture` 
因为 user profile 表包含一个指向 user 表的外键。
依赖可以通过 [[yii\test\Fixture::depends]] 属性指定，格式如下，

```php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserProfileFixture extends ActiveFixture
{
    public $modelClass = 'app\models\UserProfile';
    public $depends = ['app\tests\fixtures\UserFixture'];
}
```

依赖关系确保了这些 Fixtures 的加载和卸载按照预先定义的顺序进行。 在上面的例子， `UserFixture` 将在 `UserProfileFixture` 加载之前被加载，以确保所有的外键引用存在；并且在 `UserProfileFixture `卸载之后因同样原因卸载。

在上面例子当中，我们已经展示了如何定义一个和数据库表相关的 Fixture 。定义一个和数据库无关的 Fixture 
(例如。 一个和已经确定的文件和目录相关的 Fixture )， 你可以继承 [[yii\test\Fixture]] 这些普通的基类，并且重写  [[yii\test\Fixture::load()|load()]] 和 [[yii\test\Fixture::unload()|unload()]] 方法。


使用 Fixtures
--------------

如果你使用[CodeCeption](http://codeception.com/)测试你的代码，你可以考虑使用这些为加载和访问Fixtures提供内建支持的`yii2-codeception`扩展。

如果你想使用其他测试框架，你可以在你的测试用例当中使用[[yii\test\FixtureTrait]]达到同样的目的。

下面我们将描述如何用`yii2-codeception`编写一个`UserProfile`单元测试类。

单元测试类继承 [[yii\codeception\DbTestCase]] 或[[yii\codeception\TestCase]]，
在[[yii\test\FixtureTrait::fixtures()|fixtures()]] 方法中声明你想使用的Fixtures。 例如，

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

`fixtures()`方法列出的Fixtures在每一个测试用例的测试方法运行之前被自动加载，在每一个测试方法完成的 时候被卸载. 并且我们前面提到，当一个Fixture被加载的时候,所有他所依赖的Fixtures将首先被自动加载.在上面例子当中，因为
`UserProfileFixture`依赖`UserFixture`，当运行这个测试类中的任何一个测试方法，
两个Fixtures被自动加载： `UserFixture` 和 `UserProfileFixture`。

当在`fixtures()`方法中指定Fixtures的时候， 你可以使用一个类名称或者一个指向Fixture的数组引用配置. 当Fixture被加载的时候，此配置数组可以让你定制Fixture属性。

你也可以分配一个别名给Fixture。 在上面例子当中， `UserProfileFixture`的别名为 `profiles`。
在测试方法中, 你可能用他的别名来访问一个Fixture对象。例如， `$this->profiles`将返回`UserProfileFixture`对象。

因为`UserProfileFixture` 继承 `ActiveFixture`， 你可以进一步使用下面语法来访问通过这个Fixture提供的数据：

```php
// 返回数据行别名 'user1'
$row = $this->profiles['user1'];
// 返回UserProfile模型相应的数据行别名 'user1'
$profile = $this->profiles('user1');
// 遍历Fixture的每一个数据行
foreach ($this->profiles as $row) ...
```

> 信息： `$this->profiles`依然是 `UserProfileFixture` 类型。 上面的访问功能
> 通过PHP的魔术方法实现。


定义并使用全局 Fixtures
----------------------------------

以上描述的ixtures主要用于个别测试用例。在大多数情况下,你需要一些适用于所有或者许多测试用例的全局Fixtures。[[yii\test\InitDbFixture]]做两件事情的例子：

*通过执行位于`@app/tests/fixtures/initdb.php`的脚本，执行某些常见的初始化任务;
* 加载其他数据库Fixtures之前禁用该数据库完整性检查，并且在其他数据库Fixtures被卸载的时候重新启用该数据库。

像使用非全局Fixtures那样使用全局Fixtures。唯一的不同就是你在[[yii\codeception\TestCase::globalFixtures()]]声明那些Fixtures而不是在`fixtures()`中。当一个测试用例加载Fixtures的时候，它将首先加载全局Fixtures然后再加载非全局的Fixtures。

默认情况下， [[yii\codeception\DbTestCase]] 在他的`globalFixtures()`方法中已经声明了`InitDbFixture`。
这意味着，如果在测试之前你想做一些初始化的工作，你只需要围绕着`@app/tests/fixtures/initdb.php`编码。 否则，你可能只专注于开发每个单独的测试案例和相应的Fixtures.


组织 Fixture 类和数据文件
-----------------------------------------

默认情况下, Fixture类去一个包含Fixture类文件`data`目录的子目录中寻找相应数据文件。在简单的项目工作时，你可以遵循这个约定。
对于大项目, 机会是，您经常为同一个Fixture类的不同测试需要切换不同的数据文件。因为我们推荐你以类似于类命名空间的层次方式组织你的数据文件。 例如，

```
# tests\unit\fixtures 目录结构

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
# 等等
```

用这种方式你可以避免测试和使用需求之间的Fixture数据文件冲突。

> 注意： 上面的例子，Fixture文件的命名只是为了举例。 在实际开发中，你应该根据该Fixture类继
> 承的基类命名。例如，如果你的数据库Fixtures继承[[yii\test\ActiveFixture]]，
> 你应该用数据库表名作为Fixture文件名;
> 如果你的MongoDB Fixtures继承[[yii\mongodb\ActiveFixture]] ， 你应该用集合名作为Fixture文件名。

以蕾丝与层次结构的方式组织Fixtures类文件。 不用 `data`作根目录，用`fixtures`目录作为根目录来避免数据文件冲突。


总结
-------

> 注意： 此部分在开发环境下进行。

前面，我们已经描述了如何去定义和使用Fixtures。 接下来，我们总结了一套典型的单元测试与数据库结合的工作流程：

1. 使用 `yii migrate`工具升级你的测试数据库到最新的版本;
2. 运行测试用例：
   - 加载Fixtures：清理相关数据库表并且用Fixture数据填充他们;
   - 执行实际测试;
   - 卸载Fixtures。
3. 重复步骤2，直到所有的测试执行完毕。


**接下来清理**

管理 Fixtures
=================

> 注意: 此部分在开发环境下进行。
>
> 待办事项: 此教程可以和test-fixtures.md的上述部分合并。

Fixtures是测试的重要组成部分。他的主要目的是需要通过测试不同的用例数据来填充。把这些数据用到你的测试当中，测试会变得更加高效和有意义。

Yii 通过`yii fixture`这个命令行工具为Fixtures提供支持，这个工具支持：

* 加载Fixtures到不同的存储设备，例如: RDBMS， NoSQL， etc；
* 以不同的方式卸载Fixtures (通常他会清除存储设备)；
* 自动生成Fixtures并且用随机数据去填充他它。

Fixtures 格式
---------------

Fixtures是不同方法和配置的对象, 官方引用[documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixture.md) 。
让我们假设有Fixtures数据加载：

```
#Fixtures 数据目录的 users.php 文件，默认 @tests\unit\fixtures\data

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
如果我们使用Fixture将数据加载到数据库，那么这些行将被应用于 `users` 表。 如果我们使用nosql Fixtures，例如 `mongodb`
fixture，那么这些数据将应用于`users` mongodb 集合。 为了了解更多实现各种加载策略，访问官网 [documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixture.md)。
上面的Fixture案例是由`yii2-faker`扩展自动生成的， 在这里了解更多 [section](#auto-generating-fixtures).
Fixture类名不应该是复数形式。

加载 Fixtures
----------------

Fixture类应该以`Fixture`类作为后缀。默认的Fixtures能在`tests\unit\fixtures` 命名空间下被搜索到，你可以通过配置和命名行选项来更改这个行为，你能因为加载或者卸载指定它名字前面的`-`来排除一些Fixtures，像`-User`.

运行如下命令去加载Fixture：

```
yii fixture/load <fixture_name>
```
必需参数`fixture_name`指定一个将被加载数据的Fixture名字。 你可以同时加载多个Fixtures。
以下是这个命令的正确格式：

```
// load `User` fixture
yii fixture/load User

// same as above, because default action of "fixture" command is "load"
yii fixture User

// load several fixtures
yii fixture User UserProfile

// load all fixtures
yii fixture/load "*"

// same as above
yii fixture "*"

// load all fixtures except ones
yii fixture "*" -DoNotLoadThisOne

// load fixtures, but search them in different namespace. By default namespace is: tests\unit\fixtures.
yii fixture User --namespace='alias\my\custom\namespace'

// load global fixture `some\name\space\CustomFixture` before other fixtures will be loaded.
// By default this option is set to `InitDbFixture` to disable/enable integrity checks. You can specify several
// global fixtures separated by comma.
yii fixture User --globalFixtures='some\name\space\Custom'
```

卸载 Fixtures
------------------

运行如下命名去卸载 Fixtures：

```
// unload Users fixture, by default it will clear fixture storage (for example "users" table, or "users" collection if this is mongodb fixture).
yii fixture/unload User

// Unload several fixtures
yii fixture/unload User,UserProfile

// unload all fixtures
yii fixture/unload "*"

// unload all fixtures except ones
yii fixture/unload "*" -DoNotUnloadThisOne

```

同样的命名选项: `namespace`, `globalFixtures` 也可以应用于该命令。

全局命令配置
--------------------------
当命令行选项允许我们配置迁移命令，有时我们只想配置一次。例如，你可以按照如下配置迁移目录：

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

自动生成 fixtures
------------------------

Yii 还可以为你自动生成一些基于一些模板的Fixtures。 你能够以不同语言格式用不同的数据生成你的Fixtures.
这些特征由 [Faker](https://github.com/fzaninotto/Faker) 库和 `yii2-faker` 扩展完成。
关注 [guide](https://github.com/yiisoft/yii2-faker) 扩展获取更多的文档。