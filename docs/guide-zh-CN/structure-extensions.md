扩展
==========

扩展是专门设计的在 Yii 应用中随时可拿来使用的，
并可重发布的软件包。例如， [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug)
扩展在你的应用的每个页面底部添加一个方便用于调试的工具栏，
帮助你简单地抓取页面生成的情况。
你可以使用扩展来加速你的开发过程。

> Info: 本文中我们使用的术语 "扩展" 特指 Yii 软件包。而用术语
  "软件包" 和 "库" 指代非 Yii 专用的通常意义上的软件包。


## 使用扩展 <span id="using-extensions"></span>

要使用扩展，你要先安装它。大多数扩展以 [Composer](https://getcomposer.org/) 软件包的形式发布，
这样的扩展可采取下述两个步骤来安装：

1. 修改你的应用的 `composer.json` 文件，指明你要安装的是哪个扩展 （Composer 软件包）。
2. 运行 `composer install` 来安装指定的扩展。

注意如果你还没有安装 [Composer](https://getcomposer.org/) ，你需要先安装。

默认情况，Composer安装的是在 [Packagist](https://packagist.org/) 中
注册的软件包 - 最大的开源 Composer 代码库。你可以在 Packageist 中查找扩展。
你也可以 [创建你自己的代码库](https://getcomposer.org/doc/05-repositories.md#repository) 然后配置 Composer 来使用它。
如果是在开发私有的扩展，并且想只在你的其他工程中共享时，这样做是很有用的。

通过 Composer 安装的扩展会存放在 `BasePath/vendor` 目录下，这里的 `BasePath` 
指你的应用的 [base path](structure-applications.md#basePath)。因为 Composer 还是一个依赖管理器，当它安装一个包时，
也将安装这个包所依赖的所有软件包。

例如想安装 `yiisoft/yii2-imagine` 扩展，可按如下示例修改你的 `composer.json` 文件：

```json
{
    // ...

    "require": {
        // ... other dependencies

        "yiisoft/yii2-imagine": "~2.0.0"
    }
}
```

安装完成后，你应该能在 `BasePath/vendor` 目录下见到 `yiisoft/yii2-imagine` 目录。你也应该
见到另一个 `imagine/imagine` 目录，在其中安装了所依赖的包。

> Info: `yiisoft/yii2-imagine` 是 Yii 由开发团队维护一个核心扩展，
  所有核心扩展均由 [Packagist](https://packagist.org/) 集中管理，命名为
  `yiisoft/yii2-xyz`，其中的 `xyz`， 不同扩展有不同名称。

现在你可以使用安装好的扩展了，好比是应用的一部分。如下示例展示了如何使用 `yiisoft/yii2-imagine` 扩展
提供的 `yii\imagine\Image` 类：

```php
use Yii;
use yii\imagine\Image;

// generate a thumbnail image
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> Info: 扩展类由 [Yii class autoloader](concept-autoloading.md) 自动加载。


### 手动安装扩展 <span id="installing-extensions-manually"></span>

在极少情况下，你可能需要手动安装一部分或者全部扩展，而不是依赖 Composer。
想做到这一点，你应当：

1. 下载扩展压缩文件，解压到 `vendor` 目录。
2. 如果有，则安装扩展提供的自动加载器。
3. 按指导说明下载和安装所有依赖的扩展。

如果扩展没有提供类的自动加载器，但也遵循了 [PSR-4 standard](https://www.php-fig.org/psr/psr-4/) 
标准，那么你可以使用 Yii 提供的类自动加载器来加载扩展类。
你需要做的仅仅是为扩展的根目录声明一个 [root alias](concept-aliases.md#defining-aliases)。
例如，假设在 `vendor/mycompany/myext` 目录中安装了一个扩展，并且扩展类的命名空间为 `myext` ，
那么你可以在应用配置文件中包含如下代码：

```php
[
    'aliases' => [
        '@myext' => '@vendor/mycompany/myext',
    ],
]
```


## 创建扩展 <span id="creating-extensions"></span>

在你需要将你的杰作分享给其他人的时候，你可能会考虑创建一个扩展。
扩展可包括任何你喜欢的代码，例如助手类、挂件、模块，等等。

建议你按照 [Composer package](https://getcomposer.org/) 的条款创建扩展，以便其他人更容易安装和使用。
就像上面的章节讲述的那样。

以下是将扩展创建为一个 Composer 软件包的需遵循的基本步骤。

1. 为你的扩展建一个工程，并将它存放在版本控制代码库中，例如 [github.com](https://github.com) 。
   扩展的开发和维护都应该在这个代码库中进行。
2. 在工程的根目录下，建一个 Composer 所需的名为 `composer.json` 的文件。
   详情请参考后面的章节。  
3. 在一个 Composer 代码库中注册你的扩展，比如在 [Packagist](https://packagist.org/) 中，以便其他
   用户能找到以及用 Composer 安装你的扩展。


### `composer.json` <span id="composer-json"></span>

每个 Composer 软件包在根目录都必须有一个 `composer.json` 文件。该文件包含软件包的元数据。
你可以在 [Composer手册](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup) 中找到完整关于该文件的规格。
以下例子展示了 `yiisoft/yii2-imagine` 扩展的 `composer.json` 文件。

```json
{
    // package name
    "name": "yiisoft/yii2-imagine",

    // package type
    "type": "yii2-extension",

    "description": "The Imagine integration for the Yii framework",
    "keywords": ["yii2", "imagine", "image", "helper"],
    "license": "BSD-3-Clause",
    "support": {
        "issues": "https://github.com/yiisoft/yii2/issues?labels=ext%3Aimagine",
        "forum": "https://forum.yiiframework.com/",
        "wiki": "https://www.yiiframework.com/wiki/",
        "irc": "ircs://irc.libera.chat:6697/yii",
        "source": "https://github.com/yiisoft/yii2"
    },
    "authors": [
        {
            "name": "Antonio Ramirez",
            "email": "amigo.cobos@gmail.com"
        }
    ],

    // package dependencies
    "require": {
        "yiisoft/yii2": "~2.0.0",
        "imagine/imagine": "v0.5.0"
    },

    // class autoloading specs
    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```


#### 包名 <span id="package-name"></span>

每个 Composer 软件包都应当有一个唯一的包名以便能从其他的软件包中识别出来。
包名的格式为 `vendorName/projectName` 。例如在包名 `yiisoft/yii2-imagine` 中，vendor 名和 project 名分别是
 `yiisoft` 和 `yii2-imagine` 。

不要用 `yiisoft` 作为你的 vendor 名，由于它被 Yii 的核心代码预留使用了。

我们推荐你用 `yii2-` 作为你的包名的前缀，表示它是 Yii 2 的扩展，例如，`myname/yii2-mywidget`。
这更便于用户辨别是否是 Yii 2 的扩展。


#### 包类型 <span id="package-type"></span>

将你的扩展指明为 `yii2-extension` 类型很重要，以便安装的时候
能被识别出是一个 Yii 扩展。

当用户运行 `composer install` 安装一个扩展时， `vendor/yiisoft/extensions.php` 
文件会被自动更新使之包含新扩展的信息。从该文件中， Yii 应用程序就能知道安装了
哪些扩展 (这些信息可通过 [[yii\base\Application::extensions]] 访问)。


#### 依赖 <span id="dependencies"></span>

你的扩展依赖于 Yii （理所当然）。因此你应当在 `composer.json` 文件中列出它
(`yiisoft/yii2`)。如果你的扩展还依赖其他的扩展或者是第三方库，你也要一并列出来。
确定你也为每一个依赖的包列出了适当的版本约束条件 (比如 `1.*`, `@stable`) 。
当你发布一个稳定版本时，你所依赖的包也应当使用稳定版本。

大多数 JavaScript/CSS 包是用 [Bower](https://bower.io/) 来管理的，而非 Composer。你可使用 
[Composer asset 插件](https://github.com/fxpio/composer-asset-plugin) 使之可以
通过 Composer 来管理这类包。如果你的扩展依赖 Bower 软件包，你可以如下例所示那样简单地
在 `composer.json` 文件的依赖中列出它。

```json
{
    // package dependencies
    "require": {
        "bower-asset/jquery": ">=1.11.*"
    }
}
```

上述代码表明该扩展依赖于 `jquery` Bower 包。一般来说，你可以在 `composer.json` 
中用 `bower-asset/PackageName` 指定 Bower 包，用 `npm-asset/PackageName` 指定 NPM 包。
当 Compower 安装 Bower 和 NPM 软件包时，包的内容默认会分别安装到 `@vendor/bower/PackageName`
和 `@vendor/npm/Packages` 下。这两个目录还可以分别用 `@bower/PackageName` 
和 `@npm/PackageName` 别名指向。

关于 asset 管理的详细情况，请参照 [Assets](structure-assets.md#bower-npm-assets) 章节。


#### 类的自动加载 <span id="class-autoloading"></span>

为使你的类能够被 Yii 的类自动加载器或者 Composer 的类自动加载器自动加载，你应当在
 `composer.json` 中指定 `autoload` 条目，如下所示：

```json
{
    // ....

    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```

你可以列出一个或者多个根命名空间和它们的文件目录。

当扩展安装到应用中后，Yii 将为每个所列出根命名空间创建一个
[别名](concept-aliases.md#extension-aliases) 指向命名空间对应的目录。
例如，上述的 `autoload` 条目声明将对应于别名 `@yii/imagine`。


### 推荐的做法 <span id="recommended-practices"></span>

扩展意味着会被其他人使用，你在开发中通常需要额外的付出。
下面我们介绍一些通用的及推荐的做法，以创建高品质的扩展。


#### 命名空间 <span id="namespaces"></span>

为避免冲突以及使你的扩展中的类能被自动加载，你的类应当使用命名空间，
并使类的命名符合 [PSR-4 standard](https://www.php-fig.org/psr/psr-4/) 或者
[PSR-0 standard](https://www.php-fig.org/psr/psr-0/) 标准。

你的类的命名空间应以 `vendorName\extensionName` 起始，其中 `extensionName` 
和项目名相同，除了它没有 `yii2-` 前缀外。例如，对 `yiisoft/yii2-imagine` 扩展
来说，我们用 `yii\imagine` 作为它的类的命名空间。

不要使用 `yii`、`yii2` 或者 `yiisoft` 作为你的 vendor 名。这些名称已由 Yii 内核代码预留使用了。


#### 类的自举引导 <span id="bootstrapping-classes"></span>

有时候，你可能想让你的扩展在应用的 [自举过程](runtime-bootstrapping.md) 中执行一些代码。
例如，你的扩展可能想响应应用的 `beginRequest` 事件，做一些环境的设置工作。
虽然你可以指导扩展的使用者显式地将你的扩展中的事件句柄附加（绑定）到 `beginRequest` 事件，
但是更好的方法是自动完成。

为实现该目标，你可以创建一个所谓 *bootstrapping class* （自举类）实现 [[yii\base\BootstrapInterface]] 接口。
例如，

```php
namespace myname\mywidget;

use yii\base\BootstrapInterface;
use yii\base\Application;

class MyBootstrapClass implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
             // do something here
        });
    }
}
```

然后你将这个类在 `composer.json` 文件中列出来，如下所示，

```json
{
    // ...

    "extra": {
        "bootstrap": "myname\\mywidget\\MyBootstrapClass"
    }
}
```

当这个扩展安装到应用后，Yii 将在每一个请求的自举过程中
自动实例化自举类并调用其 [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] 
方法。


#### 操作数据库 <span id="working-with-databases"></span>

你的扩展可能要存取数据库。不要假设使用你的扩展的应用总是用 
`Yii::$db` 作为数据库连接。你应当在需要访问数据库的类中申明一个 `db` 属性。
这个属性允许你的扩展的用户可定制你的扩展使用哪个 DB 连接。例如，
你可以参考 [[yii\caching\DbCache]] 类看一下它是如何申明和使用 `db` 属性的。

如果你的扩展需要创建特定的数据库表，或者修改数据库结构，你应当

- 提供 [数据迁移](db-migrations.md) 来操作数据库的结构修改，而不是使用SQL文本文件；
- 尽量使迁移文件适用于不同的 DBMS；
- 在迁移文件中避免使用 [Active Record](db-active-record.md)。


#### 使用 Assets <span id="using-assets"></span>

如果你的扩展是挂件或者模块类型，它有可能需要使用一些 [assets](structure-assets.md) 。
例如，一个模块可能要显示一些包含图片，JavaScript 和 CSS 的页面。因为扩展的文件
都是放在同一个目录之下，安装之后 Web 无法读取，你有两个选择使得这些 asset 文件目录
可以通过 Web 读取：

- 让扩展的用户手动将这些 asset 文件拷贝到特定的 Web 可以读取的文件夹；
- 申明一个 [asset bundle](structure-assets.md) 并依靠 asset 发布机制自动将这些文件（asset bundle 中列出的文件）
  拷贝到 Web 可读的文件夹。

我们推荐你使用第二种方法，以便其他人能更容易使用你的扩展。
更详细的关于如何处理 assets ，请参照 [Assets](structure-assets.md) 章节。


#### 国际化和本地化 <span id="i18n-l10n"></span>

你的扩展可能会在支持不同语言的应用中使用！因此，如果你的扩展要显示内容给终端用户，
你应当试着实现 [国际化和本地化](tutorial-i18n.md)，特别地，

- 如果扩展为终端用户显示信息，这些信息应该用 `Yii::t()` 
  包装起来，以便可以进行翻译。
  只给开发者参考的信息（如内部异常信息）不需要做翻译。
- 如果扩展显示数字、日期等，你应该用 [[yii\i18n\Formatter]] 
  中适当的格式化规则做格式化处理。

更详细的信息，请参照 [Internationalization](tutorial-i18n.md) 章节。


#### 测试 <span id="testing"></span>

你一定想让你的扩展可以无暇地运行而不会给其他人带来问题和麻烦。为达到这个目的，
你应当在公开发布前做测试。

推荐你创建测试用例，做全面覆盖的测试你的扩展，而不只是依赖于手动测试。
每次发布新版本前，你只要简单地运行这些测试用例确保一切完好。
Yii 提供了测试支持，使你更容易写单元测试、验收测试和功能测试。
详情请参照 [Testing](test-overview.md) 章节。


#### 版本控制 <span id="versioning"></span>

你应该为每一个扩展定一个版本号（如 `1.0.1`）。我们推荐你命名版本号时参照
[semantic versioning](https://semver.org) 决定用什么样的版本号。


#### 发布 <span id="releasing"></span>

为使其他人知道你的扩展，你应该公开发布。

如果你首次发布一个扩展，你应该在 Composer 代码库中注册它，例如
[Packagist](https://packagist.org/)。之后，你所需要做的仅仅是在
版本管理库中创建一个 tag （如`v1.0.1`），然后通知 Composer 代码库。
其他人就能查找到这个新的发布了，并可通过 Composer 代码库安装和更新该扩展。

在发布你的扩展时，除了代码文件，你还应该考虑包含如下内容
帮助其他人了解和使用你的扩展：

* 根目录下的 readme 文件：它描述你的扩展是干什么的以及如何安装和使用。
  我们推荐你用 [Markdown](https://daringfireball.net/projects/markdown/) 的格式
  来写并将文件命名为 `readme.md`。
* 根目录下的修改日志文件：它列举每个版本的发布做了哪些更改。该文件可以用 Markdown 根式
  编写并命名为 `changelog.md`。
* 根目录下的升级文件：它给出如何从其他就版本升级该扩展的指导。该文件可以用 Markdown 根式
  编写并命名为 `changelog.md`。
* 入门指南、演示代码、截屏图示等：如果你的扩展提供了许多功能，在 readme 文件中不能完整
  描述时，就要用到这些文件。
* API 文档：你的代码应当做好文档，让其他人更容易阅读和理解。
  你可以参照 [Object class file](https://github.com/yiisoft/yii2/blob/master/framework/base/Object.php)
  学习如何为你的代码做文档。

> Info: 你的代码注释可以写成 Markdown 格式。`yiisoft/yii2-apidoc` 扩展为你提供了一个从你的
  代码注释生成漂亮的 API 文档。

> Info: 虽然不做要求，我们还是建议你的扩展遵循某个编码规范。
  你可以参照 [core framework code style](https://github.com/yiisoft/yii2/blob/master/docs/internals/core-code-style.md)。


## 核心扩展 <span id="core-extensions"></span>

Yii 提供了下列核心扩展，由 Yii 开发团队开发和维护。这些扩展全都在
[Packagist](https://packagist.org/) 中注册，并像 [Using Extensions](#using-extensions) 章节描述
的那样容易安装。

- [yiisoft/yii2-apidoc](https://github.com/yiisoft/yii2-apidoc)：
  提供了一个可扩展的、高效的 API 文档生成器。核心框架的 API 
  文档也是用它生成的。
- [yiisoft/yii2-authclient](https://github.com/yiisoft/yii2-authclient)：
  提供了一套常用的认证客户端，例如 Facebook OAuth2 客户端、GitHub OAuth2 客户端。
- [yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2-bootstrap)：
  提供了一套挂件，封装了 [Bootstrap](https://getbootstrap.com/) 的组件和插件。
- [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug)：
  提供了对 Yii 应用的调试支持。当使用该扩展是，
  在每个页面的底部将显示一个调试工具条。
  该扩展还提供了一个独立的页面，以显示更详细的调试信息。
- [yiisoft/yii2-elasticsearch](https://github.com/yiisoft/yii2-elasticsearch)：
  提供对 [Elasticsearch](https://www.elastic.co/) 的使用支持。它包含基本的查询/搜索支持，
  并实现了 [Active Record](db-active-record.md) 模式让你可以将活动记录
  存储在 Elasticsearch 中。
- [yiisoft/yii2-faker](https://github.com/yiisoft/yii2-faker)：
  提供了使用 [Faker](https://github.com/fzaninotto/Faker) 的支持，为你生成模拟数据。
- [yiisoft/yii2-gii](https://github.com/yiisoft/yii2-gii)：
  提供了一个基于页面的代码生成器，具有高可扩展性，并能用来快速生成模型、
  表单、模块、CRUD 等。
- [yiisoft/yii2-httpclient](https://github.com/yiisoft/yii2-httpclient)：
  提供 HTTP 客户端。
- [yiisoft/yii2-imagine](https://github.com/yiisoft/yii2-imagine)：
  提供了基于 [Imagine](https://imagine.readthedocs.org/) 的常用图像处理功能。
- [yiisoft/yii2-jui](https://github.com/yiisoft/yii2-jui)：
  提供了一套封装 [JQuery UI](https://jqueryui.com/) 的挂件以及它们的交互。
- [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb)：
  提供了对 [MongoDB](https://www.mongodb.com/) 的使用支持。它包含基本
  的查询、活动记录、数据迁移、缓存、代码生成等特性。
- [yiisoft/yii2-queue](https://www.yiiframework.com/extension/yiisoft/yii2-queue)：
  通过队列异步提供运行任务的支持。
  它支持基于 DB，Redis，RabbitMQ，AMQP，Beanstalk 和 Gearman 的队列。
- [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis)：
  提供了对 [redis](https://redis.io/) 的使用支持。它包含基本的
  查询、活动记录、缓存等特性。
- [yiisoft/yii2-shell](https://www.yiiframework.com/extension/yiisoft/yii2-shell)：
  提供基于 [psysh](https://psysh.org/) 的交互式 shell。
- [yiisoft/yii2-smarty](https://github.com/yiisoft/yii2-smarty)：
  提供了一个基于 [Smarty](https://www.smarty.net/) 的模板引擎。
- [yiisoft/yii2-sphinx](https://github.com/yiisoft/yii2-sphinx)：
  提供了对 [Sphinx](https://sphinxsearch.com) 的使用支持。它包含基本的
  查询、活动记录、代码生成等特性。
- [yiisoft/yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer)：
  提供了基于 [swiftmailer](https://swiftmailer.symfony.com/) 的邮件发送功能。
- [yiisoft/yii2-twig](https://github.com/yiisoft/yii2-twig)：
  提供了一个基于 [Twig](https://twig.symfony.com/) 的模板引擎。

以下官方扩展适用于 Yii 2.1 及以上版本。
您不需要为 Yii 2.0 安装它们，因为它们包含在核心框架中。

- [yiisoft/yii2-captcha](https://www.yiiframework.com/extension/yiisoft/yii2-captcha)：
  提供 CAPTCHA。
- [yiisoft/yii2-jquery](https://www.yiiframework.com/extension/yiisoft/yii2-jquery)：
  为 [jQuery](https://jquery.com/) 提供支持。
- [yiisoft/yii2-maskedinput](https://www.yiiframework.com/extension/yiisoft/yii2-maskedinput)：
  提供基于 [jQuery Input Mask plugin](https://robinherbots.github.io/Inputmask/) 的格式化输入小部件。
- [yiisoft/yii2-mssql](https://www.yiiframework.com/extension/yiisoft/yii2-mssql)：
  提供对使用 [MSSQL](https://www.microsoft.com/sql-server/) 的支持。
- [yiisoft/yii2-oracle](https://www.yiiframework.com/extension/yiisoft/yii2-oracle)：
  提供对使用 [Oracle](https://www.oracle.com/) 的支持。
- [yiisoft/yii2-rest](https://www.yiiframework.com/extension/yiisoft/yii2-rest)：
  提供对 REST API 的支持。
