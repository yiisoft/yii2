控制台命令
==========

除了用于构建 Web 应用程序的丰富功能，Yii 中也有一个拥有丰富功能的控制台，它们主要用于创建网站后台处理的任务。

控制台应用程序的结构非常类似于 Yii 的一个 Web 应用程序。它由一个或多个 [[yii\console\Controller]] 类组成，它们在控制台环境下通常被称为“命令”。每个控制器还可以有一个或多个动作，就像 web 控制器。

两个项目模板（基础模版和高级模版）都有自己的控制台应用程序。你可以通过运行 `yii` 脚本，在位于仓库的基本目录中运行它。
当你不带任何参数来运行它时，会给你一些可用的命令列表：

![Running ./yii command for help output](images/tutorial-console-help.png)

正如你在截图中看到，Yii 中已经定义了一组默认情况下可用的命令：

- [[yii\console\controllers\AssetController|AssetController]] - 允许合并和压缩你的 JavaScript 和 CSS 文件。你也可以在 [资源 - 使用 asset 命令](structure-assets.md#using-the-asset-command) 一节获取等多信息。
- [[yii\console\controllers\CacheController|CacheController]] - 清除应用程序缓存。
- [[yii\console\controllers\FixtureController|FixtureController]] - 管理用于单元测试 fixture 的加载和卸载。
这个命令的更多细节在 [Testing Section about Fixtures](test-fixtures.md#managing-fixtures).
- [[yii\console\controllers\HelpController|HelpController]] - 提供有关控制台命令的帮助信息，这是默认的命令并会打印上面截图所示的输出。
- [[yii\console\controllers\MessageController|MessageController]] - 从源文件提取翻译信息。
  要了解更多关于这个命令的用法，请参阅 [I18N 章节](tutorial-i18n.md#message-command).
- [[yii\console\controllers\MigrateController|MigrateController]] - 管理应用程序数据库迁移。
  在 [数据库迁移章节](db-migrations.md) 可获取更多信息。


用法 <span id="usage"></span>
-----

你可以使用以下语法来执行控制台控制器操作：

```
yii <route> [--option1=value1 --option2=value2 ... argument1 argument2 ...]
```

以上，`<route>` 指的是控制器动作的路由。选项将填充类属性，参数是动作方法的参数。

例如，将 [[yii\console\controllers\MigrateController::actionUp()|MigrateController::actionUp()]]
限制 5 个数据库迁移并将 [[yii\console\controllers\MigrateController::$migrationTable|MigrateController::$migrationTable]] 设置为 `migrations` 应该这样调用：

```
yii migrate/up 5 --migrationTable=migrations
```

> **注意**: 当在控制台使用 `*` 时, 不要忘记像 `"*"` 一样用引号来引起来，为了防止在 shell 中执行命令时被当成当前目录下的所有文件名。


入口脚本 <span id="entry-script"></span>
----------------

控制台应用程序的入口脚本相当于用于 Web 应用程序的 `index.php` 入口文件。
控制台入口脚本通常被称为 `yii`，位于应用程序的根目录。它包含了类似下面的代码：

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/config/console.php');

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```

该脚本将被创建为你应用程序中的一部分；你可以根据你的需求来修改它。如果你不需要记录错误信息或者希望提高整体性能，`YII_DEBUG` 常数应定义为 `false`。
在基本的和高级的两个应用程序模板中，控制台应用程序的入口脚本在默认情况下会启用调试模式，以提供给开发者更好的环境。


配置 <span id="configuration"></span>
-----

在上面的代码中可以看到，控制台应用程序使用它自己的配置文件，名为 `console.php` 。在该文件里你可以给控制台配置各种
[应用组件](structure-application-components.md) 和属性。

如果你的 web 应用程序和控制台应用程序共享大量的配置参数和值，你可以考虑把这些值放在一个单独的文件中，该文件中包括（ web 和控制台）应用程序配置。
你可以在“高级”项目模板中看到一个例子。

> 提示：有时，你可能需要使用一个与在入口脚本中指定的应用程序配置不同的控制台命令。例如，你可能想使用 `yii migrate`
> 命令来升级你的测试数据库，它被配置在每个测试套件。要动态地更改配置，只需指定一个自定义应用程序的配置文件，通过 `appconfig`选项来执行命令：
> 
> ```
> yii <route> --appconfig=path/to/config.php ...
> ```


创建你自己的控制台命令 <span id="create-command"></span>
----------------------

### 控制台的控制器和行为

一个控制台命令继承自 [[yii\console\Controller]] 控制器类。
在控制器类中，定义一个或多个与控制器的子命令相对应的动作。在每一个动作中，编写你的代码实现特定的子命令的适当的任务。

当你运行一个命令时，你需要指定一个控制器的路由。例如，路由 `migrate/create` 调用子命令对应的[[yii\console\controllers\MigrateController::actionCreate()|MigrateController::actionCreate()]] 动作方法。
如果在执行过程中提供的路由不包含路由 ID ，将执行默认操作（如 web 控制器）。

### 选项

通过覆盖在 [[yii\console\Controller::options()]] 中的方法，你可以指定可用于控制台命令（controller/actionID）选项。这个方法应该返回控制器类的公共属性的列表。
当运行一个命令，你可以指定使用语法 `--OptionName=OptionValue` 选项的值。
这将分配 `OptionValue` 到控制器类的 `OptionName` 属性。

If the default value of an option is of an array type and you set this option while running the command,
the option value will be converted into an array by splitting the input string on any commas.

### 参数

除了选项，命令还可以接收参数。参数将传递给请求的子命令对应的操作方法。第一个参数对应第一个参数，第二个参数对应第二个参数，依次类推。
命令被调用时，如果没有足够的参数，如果有定义默认值的情况下，则相应的参数将采取默认声明的值；如果没有设置默认值，并且在运行时没有提供任何值，该命令将以一个错误退出。

你可以使用 `array` 类型提示来指示一个参数应该被视为一个数组。该数组通过拆分输入字符串的逗号来生成。

下面的示例演示如何声明参数：

```php
class ExampleController extends \yii\console\Controller
{
    // 命令 "yii example/create test" 会调用 "actionCreate('test')"
    public function actionCreate($name) { ... }

    // 命令 "yii example/index city" 会调用 "actionIndex('city', 'name')"
    // 命令 "yii example/index city id" 会调用 "actionIndex('city', 'id')"
    public function actionIndex($category, $order = 'name') { ... }

    // 命令 "yii example/add test" 会调用 "actionAdd(['test'])"
    // 命令 "yii example/add test1,test2" 会调用 "actionAdd(['test1', 'test2'])"
    public function actionAdd(array $name) { ... }
}
```


### 退出代码

使用退出代码是控制台应用程序开发的最佳做法。通常，执行成功的命令会返回 `0`。如果命令返回一个非零数字，会认为出现错误。
该返回的数字作为出错代码，用以了解错误的详细信息。例如 `1` 可能代表一个未知的错误，所有的代码都将保留在特定的情况下：输入错误，丢失的文件等等。

要让控制台命令返回一个退出代码，只需在控制器操作方法中返回一个整数：

```php
public function actionIndex()
{
    if (/* some problem */) {
        echo "A problem occured!\n";
        return 1;
    }
    // do something
    return 0;
}
```

你可以使用一些预定义的常数：

- `Controller::EXIT_CODE_NORMAL` 值为 `0`;
- `Controller::EXIT_CODE_ERROR` 值为 `1`.

为控制器定义有意义的常量，以防有更多的错误代码类型，这会是一个很好的实践。

### 格式和颜色

Yii 支持格式化输出，如果终端运行命令不支持的话则会自动退化为非格式化输出。

要输出格式的字符串很简单。以下展示了如何输出一些加粗的文字：

```php
$this->stdout("Hello?\n", Console::BOLD);
```

如果你需要建立字符串动态结合的多种样式，最好使用 `ansiFormat` ：

```php
$name = $this->ansiFormat('Alex', Console::FG_YELLOW);
echo "Hello, my name is $name.";
```
