引入第三方代码
=============================

有时，你可能会需要在 Yii 应用中使用第三方的代码。又或者是你想要在第三方系统中把 Yii 作为类库引用。在下面这个板块中，我们向你展示如何实现这些目标。


## 在 Yii 中使用第三方类库 <span id="using-libs-in-yii"></span>

要想在 Yii 应用中使用第三方类库，你主要需要确保这些库中的类文件都可以被正常导入或可以被自动加载。


### 使用 Composer 包 <span id="using-composer-packages"></span>

目前很多第三方的类库都以 [Composer](https://getcomposer.org/) 包的形式发布。你只需要以下两个简单的步骤即可安装他们：

1. 修改你应用的 `composer.json` 文件，并注明需要安装哪些 Composer 包。
2. 运行 `php composer.phar install` 安装这些包。

这些Composer 包内的类库，可以通过 Composer 的自动加载器实现自动加载。不过请确保你应用的
[入口脚本](structure-entry-scripts.md)包含以下几行用于加载 Composer 自动加载器的代码：

```php
// install Composer autoloader （安装 Composer 自动加载器）
require(__DIR__ . '/../vendor/autoload.php');

// include Yii class file （加载 Yii 的类文件）
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
```


### 使用下载的类库 <span id="using-downloaded-libs"></span>

若你的类库并未发布为一个 Composer 包，你可以参考以下安装说明来安装它。在大多数情况下，你需要预先下载一个发布文件，并把它解压缩到
`BasePath/vendor` 目录，这里的 `BasePath` 代指你应用程序自身的 [base path（主目录）](structure-applications.md#basePath)。

若该类库包含他自己的类自动加载器，你可以把它安装到你应用的[入口脚本](structure-entry-scripts.md)里。我们推荐你把它的安装代码置于
`Yii.php` 的导入之前，这样 Yii 的官方自动加载器可以拥有更高的优先级。

若一个类库并没有提供自动加载器，但是他的类库命名方式符合 [PSR-4](http://www.php-fig.org/psr/psr-4/) 标准，你可以使用 Yii 官方的自动加载器来自动加载这些类。你只需给他们的每个根命名空间声明一下[根路径别名](concept-aliases.md#defining-aliases)。比如，假设说你已经在目录 `vendor/foo/bar` 里安装了一个类库，且这些类库的根命名空间为 `xyz`。你可以把以下代码放入你的应用配置文件中：

```php
[
    'aliases' => [
        '@xyz' => '@vendor/foo/bar',
    ],
]
```

若以上情形都不符合，最可能是这些类库需要依赖于 PHP 的 include_path 配置，来正确定位并导入类文件。只需参考它的安装说明简单地配置一下 PHP 导入路径即可。

最悲催的情形是，该类库需要显式导入每个类文件，你可以使用以下方法按需导入相关类文件：

* 找出该库内包含哪些类。
* 在应用的[入口脚本](structure-entry-scripts.md)里的 `Yii::$classMap` 数组中列出这些类，和他们各自对应的文件路径。

举例来说，

```php
Yii::$classMap['Class1'] = 'path/to/Class1.php';
Yii::$classMap['Class2'] = 'path/to/Class2.php';
```


## 在第三方系统内使用 Yii <span id="using-yii-in-others"></span>

因为 Yii 提供了很多牛逼的功能，有时，你可能会想要使用它们中的一些功能用来支持开发或完善某些第三方的系统，比如：WordPress，Joomla，或是用其他 PHP 框架开发的应用程序。举两个例子吧，你可能会想念方便的 [[yii\helpers\ArrayHelper]] 类，或在第三方系统中使用
[Active Record](db-active-record.md) 活动记录功能。要实现这些目标，你只需两个步骤：安装 Yii，启动 Yii。

若这个第三方系统支持 Composer 管理他的依赖文件，你可以直接运行一下命令来安装 Yii：

```
php composer.phar require yiisoft/yii2-framework:*
php composer.phar install
```

不然的话，你可以[下载](http://www.yiiframework.com/download/) Yii 的发布包，并把它解压到对应系统的 `BasePath/vendor` 目录内。

之后，你需要修改该第三方应用的入口脚本，在开头位置添加 Yii 的引入代码：

```php
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$yiiConfig = require(__DIR__ . '/../config/yii/web.php');
new yii\web\Application($yiiConfig); // 千万别在这调用 run() 方法。（笑）
```

如你所见，这段代码与典型的 Yii 应用的[入口脚本](structure-entry-scripts.md)非常相似。唯一的不同之处在于在 Yii 应用创建成功之后，并不会紧接着调用 `run()` 方法。因为，`run()` 方法的调用会接管 HTTP 请求的处理流程。（译注：换言之，这就不是第三方系统而是 Yii 系统了，URL 规则也会跟着换成 Yii 的规则了）

与 Yii 应用中一样，你可以依据运行该第三方系统的环境，针对性地配置 Yii 应用实例。比如，为了使用[活动记录](db-active-record.md)功能，你需要先用该第三方系统的 DB 连接信息，配置 Yii 的 `db` 应用组件。

现在，你就可以使用 Yii 提供的绝大多数功能了。比如，创建 AR 类，并用它们来操作数据库。


## 配合使用 Yii 2 和 Yii 1 <span id="using-both-yii2-yii1"></span>

如果你之前使用 Yii 1，大概你也有正在运行的 Yii 1 应用吧。不必用 Yii 2 重写整个应用，你也可以通过增添对哪些
Yii 2 独占功能的支持来增强这个系统。下面我们就来详细描述一下具体的实现过程。

> 注意：Yii 2 需要 PHP 5.4+ 的版本。你需要确保你的服务器以及现有应用都可以支持 PHP 5.4。

首先，参考前文板块中给出的方法，在已有的应用中安装 Yii 2。

之后，如下修改 Yii 1 应用的入口脚步：

```php
// 导入下面会详细说明的定制 Yii 类文件。
require(__DIR__ . '/../components/Yii.php');

// Yii 2 应用的配置文件
$yii2Config = require(__DIR__ . '/../config/yii2/web.php');
new yii\web\Application($yii2Config); // Do NOT call run()

// Yii 1 应用的配置文件
$yii1Config = require(__DIR__ . '/../config/yii1/main.php');
Yii::createWebApplication($yii1Config)->run();
```

因为，Yii 1 和 Yii 2 都包含有 `Yii` 这个类，你应该创建一个定制版的 Yii 来把他们组合起来。上面的代码里包含了的这个定制版的 `Yii` 类，可以用以下代码创建出来：

```php
$yii2path = '/path/to/yii2';
require($yii2path . '/BaseYii.php'); // Yii 2.x

$yii1path = '/path/to/yii1';
require($yii1path . '/YiiBase.php'); // Yii 1.x

class Yii extends \yii\BaseYii
{
    // 复制粘贴 YiiBase (1.x) 文件中的代码于此
}

Yii::$classMap = include($yii2path . '/classes.php');

// 通过 Yii 1 注册 Yii2 的类自动加载器
Yii::registerAutoloader(['Yii', 'autoload']);
```

大功告成！此时，你可以在你代码的任意位置，调用 `Yii::$app` 以访问 Yii 2 的应用实例，而用
`Yii::app()` 则会返回 Yii 1 的应用实例：

```php
echo get_class(Yii::app()); // 输出 'CWebApplication'
echo get_class(Yii::$app);  // 输出 'yii\web\Application'
```
