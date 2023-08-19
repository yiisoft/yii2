Fixtures
========

Fixtures 是测试中非常重要的一部分。他们的主要目的是建立一个固定/已知的环境状态以确保
测试可重复并且按照预期方式运行。Yii 提供一个简单可用的 Fixure 框架
允许你精确的定义你的 Fixtures 。

Yii 的 Fixture 框架的核心概念称之为 *fixture object* 。一个 Fixture object 代表
一个测试环境的某个特定方面，它是 [[yii\test\Fixture]] 或者其子类的实例。
比如，你可以使用 `UserFixture` 来确保用户DB表包含固定的数据。
你在运行一个测试之前加载一个或者多个 fixture object，并在结束后卸载他们。

一个 Fixture 可能依赖于其他的 Fixtures ，通过它的 [[yii\test\Fixture::depends]] 来指定。
当一个 Fixture 被加载前，它依赖的 Fixture 会被自动的加载；同样，当某个 Fixture 被卸载后，
它依赖的 Fixtures 也会被自动的卸载。


定义一个 Fixture
------------------

为了定义一个 Fixture，你需要创建一个新的 class 继承自 [[yii\test\Fixture]] 
或者 [[yii\test\ActiveFixture]] 。前一个类对于一般用途的 Fixture 比较适合，
而后者则有一些增强功能专用于与数据库和 ActiveRecord 一起协作。

下面的代码定义一个关于 `User` ActiveRecord 和相关的用户表的 Fixture：

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
}
```

> Tip: 每个 `ActiveFixture` 都会准备一个 DB 表用来测试。你可以通过设置 [[yii\test\ActiveFixture::tableName]] 
> 或 [[yii\test\ActiveFixture::modelClass]] 属性来指定具体的表。如果是后者，
> 表名会从 `modleClass` 指定的 `ActiveRecord` 中获取。

> Note: [[yii\test\ActiveFixture]] 仅限于 SQL 数据库，对于 NoSQL 数据库，
> Yii 提供以下 `ActiveFixture` 类：
>
> - Mongo DB：[[yii\mongodb\ActiveFixture]]
> - Elasticsearch：[[yii\elasticsearch\ActiveFixture]]（从版本 2.0.2 开始）


提供给 `ActiveFixture` 的 fixture data 通常放在一个路径为 `FixturePath/data/TableName.php` 的文件中，
其中 `FixturePath` 代表 Fixture 类所在的路径， 
`TableName` 则是和 Fixture 关联的表。在以上的例子中，
这个文件应该是 `@app/tests/fixtures/data/user.php` 。
data 文件返回一个包含要被插入用户表中的数据文件，比如：

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

你可以给某行指定一个 alias 别名，这样在你以后的测试中，你可以通过别名来确定某行。
在上面的例子中，这两行指定别名为 `user1` 和 `user2`。

同样，你不需要特别的为自动增长（auto-incremental）的列指定数据，
Yii 将会在 Fixture 被加载时自动的填充正确的列值到这些行中。

> Tip: 你可以通过设置 [[yii\test\ActiveFixture::dataFile]] 属性来自定义 data 文件的位置。
> 同样，你可以重写 [[yii\test\ActiveFixture::getData()]] 来提供数据。

如之前所述，一个 Fixture 可以依赖于其他的 Fixture 。比如一个 `UserProfileFixture` 可能需要依赖于 `UserFixture`，
因为 user profile 表包括一个指向 user 表的外键。那么，
这个依赖关系可以通过 [[yii\test\Fixture::depends]] 属性来指定，比如如下：

```php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserProfileFixture extends ActiveFixture
{
    public $modelClass = 'app\models\UserProfile';
    public $depends = ['app\tests\fixtures\UserFixture'];
}
```

依赖关系确保所有的 Fixtures 能够以正常的顺序被加载和卸载。在以上的例子中，
为确保外键存在， `UserFixture` 会在 `UserProfileFixture` 之前加载，
同样，也会在其卸载后同步卸载。

在上面，我们展示了如何定义一个 DB 表的 Fixture 。为了定义一个与 DB 无关的 Fixture 
（比如一个fixture关于文件和路径的），你可以从一个更通用的基类 [[yii\test\Fixture]] 继承，
并重写 [[yii\test\Fixture::load()|load()]] 和 [[yii\test\Fixture::unload()|unload()]] 方法。


使用 Fixtures
--------------

如果你使用 [CodeCeption](https://codeception.com/) 作为你的 Yii 代码测试框架，
你需要考虑使用 `yii2-codeception` 扩展，这个扩展包含内置的机制来支持加载和访问 Fixtures。
如果你使用其他的测试框架，为了达到加载和访问 Fixture 的目的，
你需要在你的测试用例中使用 [[yii\test\FixtureTrait]]。

在以下示例中，我们会展示如何通过 `yii2-codeception` 写一个 `UserProfile` 单元来测试某个 class。

在一个继承自 [[yii\codeception\DbTestCase]] 或者 [[yii\codeception\TestCase]] 的单元测试类中，
你可以在 [[yii\test\FixtureTrait::fixtures()|fixtures()]] 方法中声明你希望使用哪个 Fixture。比如：

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

在测试用例的每个测试方法运行前 `fixtures()` 方法列表返回的 Fixture 会被自动的加载，
并在结束后自动的卸载。同样，如前面所述，当一个 Fixture 被加载之前，
所有它依赖的 Fixture 也会被自动的加载。在上面的例子中，因为 `UserProfileFixture` 
依赖于 `UserFixtrue`，当运行测试类中的任意测试方法时，两个 Fixture，`UserFixture` 和 `UserProfileFixture` 会被依序加载。

当我们通过 `fixtures()` 方法指定需要加载的 Fixture 时，我们既可以使用一个类名，
也可以使用一个配置数组。
配置数组可以让你自定义加载的 fixture 的属性名。

你同样可以给一个 Fixture 指定一个别名（alias），在上面的例子中，`UserProfileFixture` 的别名为 `profiles` 。
在测试方法中，你可以通过别名来访问一个 Fixture 对象。比如，

```php
$profile = $I->grabFixture('profiles', 'user1');
```

将会返回 `UserProfileFixture` 对象。

因为 `UserProfileFixture` 从 `ActiveFixture` 处继承，
在后面，你可以通过如下的语法形式来访问 Fixture 提供的数据：

```php
// 返回对应于别名为“user1”的数据行的 UserProfile 模型
$profile = $I->grabFixture('profiles', 'user1');
// 遍历 fixture 中的数据
foreach ($I->grabFixture('profiles') as $profile) ...
```

组织 Fixture 类和相关的数据文件
-----------------------------------------

默认情况下，Fixture 类会在其所在的目录下面的 `data` 子目录寻找相关的数据文件。
在一些简单的项目中，你可以遵循此范例。对于一些大项目，
您可能经常为同一个 Fixture 类的不同测试而切换不同的数据文件。
在这种情况下，我们推荐你按照一种类似于命名空间的方式有层次地组织你的数据文件，比如：

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

这样，你就可以避免在测试用例之间产生冲突，并根据你的需要使用它们。

> Note: 在以上的例子中，Fixture 文件只用于示例目的。在真实的环境下，你需要根据你的 Fixture 类继承的基类来决定它们的命名。
> 比如，如果你从 [[yii\test\ActiveFixture]] 继承了一个 DB Fixture，
> 你需要用数据库表名字作为 Fixture 的数据文件名；如果你从 [[yii\mongodb\ActiveFixture]] 继承了一个 MongoDB Fixture，
> 你需要使用 collection 名作为文件名。

组织 Fixuture 类名的方式同样可以使用前述的层次组织法，但是，为了避免跟数据文件产生冲突，
你需要用 `fixtures` 作为根目录而非 `data`。

## 使用 `yii fixture` 来管理 fixtures

Yii 通过 `yii fixture` 命令行工具来支持 fixtures 操作. 这个工具支持:

* 将 fixtures 装载到不同的存储设备，例如：RDBMS, NoSQL 等;
* 以不同方式卸载 fixtures（通常是清理存储）;
* 自动生成 fixtures 并用随机数据填充。

### Fixtures 数据格式

让我们假设我们有要加载的 fixtures 数据：

```
#users.php 文件在 fixtures 下的数据路径, 默认为 @tests\unit\fixtures\data

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
如果我们使用 fixture 将数据加载到数据库中，那么这些行将作用到 `users` 表。 如果我们使用的是 nosql 类型的 fixtures，例如 `mongodb` fixture然后这个数据将应用于 `users` mongodb 集合。 要了解有关实现各种加载策略的信息，请参阅官方[文档](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixtures.md)。

上面的 fixture 示例是由 `yii2-faker` 扩展自动生成的，在这些[章节](#auto-generating-fixtures)中可以了解更多相关内容。
Fixture 类名称不应为复数。

### 加载 Fixtures

Fixture 类应该以 `Fixture` 类作为后缀。默认的 Fixtures 能在 `tests\unit\fixtures` 命名空间下被搜索到，
你可以通过配置和命名行选项来更改这个行为，你可以通过加载或者卸载指定它名字前面的 `-` 来排除一些 Fixtures，像 `-User`。

运行如下命令去加载 Fixture：

> Note: 卸载数据事件优先执行，在加载数据之前。通常，这将会清除先前的 fixture 所插入的所有现有数据。

```
yii fixture/load <fixture_name>
```

必需参数 `fixture_name` 指定一个将被加载数据的 Fixture 名字。 你可以同时加载多个 Fixtures 。
以下是这个命令的正确格式：

```
// 加载 `User` fixture
yii fixture/load User

// 与上面相同，因为 “fixture” 命令的默认动作是“加载”
yii fixture User

// 加载几个 fixtures
yii fixture User UserProfile

// 加载所有 fixtures
yii fixture/load "*"

// 与上面相同
yii fixture "*"

// 加载所有 fixtures 除了这些
yii fixture "*" -DoNotLoadThisOne

// 加载 fixtures, 但在不同的命名空间中搜索它们。默认命名空间是：tests\unit\fixtures。
yii fixture User --namespace='alias\my\custom\namespace'

// 加载全局 fixture `some\name\space\CustomFixture` 在加载其他 fixtures 之前.
// 默认情况下，此选项设置为 `InitDbFixture` 以禁用/启用完整性检查。您可以用用逗号分离来
// 指定几个全局 fixtures。
yii fixture User --globalFixtures='some\name\space\Custom'
```

### 卸载 Fixtures

运行如下命名去卸载 Fixtures：

```
// 卸载 User fixture，默认情况下它将清除 fixture 存储（例如“用户”表，或“用户”集合如果这是 mongodb fixture）。
yii fixture/unload User

// 卸载几个 fixtures
yii fixture/unload User,UserProfile

// 卸载所有 fixtures
yii fixture/unload "*"

// 卸载所有 fixtures 除了这些
yii fixture/unload "*" -DoNotUnloadThisOne

```

同样的命名选项：`namespace`，`globalFixtures` 也可以应用于该命令。

### 全局命令配置

当命令行选项允许我们配置迁移命令，
有时我们只想配置一次。例如，
你可以按照如下配置迁移目录：

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

### 自动生成 fixtures

Yii 还可以为你自动生成一些基于一些模板的 Fixtures。 你能够以不同语言格式用不同的数据生成你的 Fixtures。
这些特征由 [Faker](https://github.com/fzaninotto/Faker) 库和 `yii2-faker` 扩展完成。
关注 [guide](https://github.com/yiisoft/yii2-faker) 扩展获取更多的文档。

## 总结

在上面，我们描述了如何定义和使用 Fixture，在下面，我们将总结出一个
标准地运行与 DB 有关的单元测试的规范工作流程：

1. 使用 `yii migrate` 工具来让你的测试数据库更新到最新的版本；
2. 运行一个测试：
   - 加载 Fixture：清空所有的相关表结构，并用 Fixture 数据填充
   - 执行真实的测试用例
   - 卸载 Fixture
3. 重复步骤 2 直到所有的测试结束。
