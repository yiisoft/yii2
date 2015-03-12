扩展
==========

扩展是可再发行的软件包，专门设计用在Yii应用中，并具有可随时调用的特点。例如，
在应用的每个页面底部为 [yiisoft/yii2-debug](tool-debugger.md) 扩展增加一个方便调试的工具栏
这会使得你更加容易掌握这些页面是如何生成的。你可以运用扩展模块来加速你的开发进程你也可以将代码打包成扩展文件形式，
来与其他人共享你的工作成果.

> 提示信息: 我们用术语 "extension" 来特指 Yii 特定软件包。对于那些无需 Yii 就能使用的通用软件包
  我们会用”包”或”库”这样的术语来表示它们。


## 运用扩展 <a name="using-extensions"></a>

为了使用扩展，你首先需要安装它。大部分扩展文件会用 [Composer](https://getcomposer.org/)
包来部署，Composer 包可以用以下两个简单的步骤安装：

1. 移除你应用中的 `composer.json` 文件，并指定你要安装的扩展文件 (Composer packages)。
2. 运行 `composer install` 来安装指定的扩展文件。

提示：如果没有的话你需要安装 [Composer](https://getcomposer.org/)。

默认情况下，Composer 安装包注册在 [Packagist](https://packagist.org/) - 最大的开源
Composer 包库。你可以对照着看一下 Packagist 扩展。You may also
[create your own repository](https://getcomposer.org/doc/05-repositories.md#repository) and configure Composer
to use it. This is useful if you are developing closed open extensions and want to share within your projects.

Extensions installed by Composer are stored in the `BasePath/vendor` directory, where `BasePath` refers to the
application's [base path](structure-applications.md#basePath).  Because Composer is a dependency manager, 如果
安装一个包，也要安装它所有的附属包。

例如，为了安装 `yiisoft/yii2-imagine` 扩展，按如下方式修改 `composer.json` 文件：

```json
{
    // ...

    "require": {
        // ... other dependencies

        "yiisoft/yii2-imagine": "*"
    }
}
```

安装完成后，需要查看 `BasePath/vendor` 下的 `yiisoft/yii2-imagine` 目录。同时需要查看
包含安装附属包的 `imagine/imagine` 目录。

> 提示信息：`yiisoft/yii2-imagine` 是由 Yii 团队开发和维护的一个核心扩展。All
  core extensions are hosted on [Packagist](https://packagist.org/) and named like `yiisoft/yii2-xyz`, where `xyz`
  varies for different extensions.

Now you can use the installed extensions like they are part of your application. 下面的例子展示了
如何运用由 `yiisoft/yii2-imagine` 扩展提供的 `yii\imagine\Image` 类：

```php
use Yii;
use yii\imagine\Image;

// generate a thumbnail image
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> 提示信息：扩展类由 [Yii class autoloader](concept-autoloading.md) 自动加载。


### 手动安装扩展 <a name="installing-extensions-manually"></a>

某些特殊情况下，可能需要手动安装部分或全部扩展，而不是依靠 Composer。
方法如下

1. 下载扩展存档文件并解压到 `vendor` 目录。
2. 如果有的话，安装由扩展提供的类自动加载器。
3. 下载并按照说明安装所有相关扩展文件。

如果一个扩展没有一个自动载入的类但是遵循 [PSR-4 standard](http://www.php-fig.org/psr/psr-4/),
你可以用yii提供的自动载入类去载入这个扩展类。All you need to do is just to
declare a [root alias](concept-aliases.md#defining-aliases) for the extension root directory. For example,
assuming you have installed an extension in the directory `vendor/mycompany/myext`, and the extension classes
are under the `myext` namespace, then you can include the following code in your application configuration:

```php
[
    'aliases' => [
        '@myext' => '@vendor/mycompany/myext',
    ],
]
```


## 创建扩展 <a name="creating-extensions"></a>

如果你想和别人共享自己的代码，可以考虑创建一个扩展。
这个扩展能够容纳任何代码，例如一个助手类，a widget, a module, etc.

It is recommended that you create an extension in terms of a [Composer package](https://getcomposer.org/) so that
it can be more easily installed and used by other users, liked described in the last subsection.

按照下边步骤为 Composer 包创建一个扩展。

1. Create a project for your extension and host it on a VCS repository, such as [github.com](https://github.com).
   The development and maintenance work about the extension should be done on this repository.
2. Under the root directory of the project, create a file named `composer.json` as required by Composer. Please
   refer to the next subsection for more details.
3. Register your extension with a Composer repository, such as [Packagist](https://packagist.org/), so that
   other users can find and install your extension using Composer.


### `composer.json` <a name="composer-json"></a>

每一个 Composer package 的根目录包都必须包含一个 `composer.json` 文件。The file contains the metadata about
the package. 可以在 [Composer Manual](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup) 手册内找到有关该文件完整的说明。
下边例子展示了 `composer.json` 文件的 `yiisoft/yii2-imagine` 扩展：

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
        "forum": "http://www.yiiframework.com/forum/",
        "wiki": "http://www.yiiframework.com/wiki/",
        "irc": "irc://irc.freenode.net/yii",
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
        "yiisoft/yii2": "*",
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


#### Package Name <a name="package-name"></a>

每一个 Composer 包的名称中都需要包含一个唯一标识，以便容易识别。
The format of package names is `vendorName/projectName`. 例如，in the package name `yiisoft/yii2-imagine`,
the vendor name and the project name are `yiisoft` and `yii2-imagine`, respectively.

Do NOT use `yiisoft` as vendor name as it is reserved for use by the Yii core code.

We recommend you prefix `yii2-` to the project name for packages representing Yii 2 extensions, for example,
`myname/yii2-mywidget`. This will allow users to more easily tell whether a package is a Yii 2 extension.


#### Package Type <a name="package-type"></a>

It is important that you specify the package type of your extension as `yii2-extension` so that the package can
be recognized as a Yii extension when being installed.

当用户运行 `composer install` 来安装扩展，`vendor/yiisoft/extensions.php`文件
将被自动更新，包括对新扩展。从这个文件中，可以知道 Yii 应用
安装了哪些扩展 (the information can be accessed via [[yii\base\Application::extensions]].


#### Dependencies <a name="dependencies"></a>

您的扩展依赖于 Yii (of course)。So you should list it (`yiisoft/yii2`) in the `require` entry in `composer.json`.
如果您的扩展依赖于其它扩展或者第三方库，你也要将它们罗列出来。
Make sure you also list appropriate version constraints (e.g. `1.*`, `@stable`) for each dependent package. Use stable
dependencies when your extension is released in a stable version.

Most JavaScript/CSS packages are managed using [Bower](http://bower.io/) and/or [NPM](https://www.npmjs.org/),
instead of Composer. Yii uses the [Composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin)
to enable managing these kinds of packages through Composer. If your extension depends on a Bower package, you can
simply list the dependency in `composer.json` like the following:

```json
{
    // package dependencies
    "require": {
        "bower-asset/jquery": ">=1.11.*"
    }
}
```

上面的代码中指出此扩展依赖于 `jquery` Bower package。一般来说，you can use
`bower-asset/PackageName` to refer to a Bower package in `composer.json`, and use `npm-asset/PackageName`
to refer to a NPM package. When Composer installs a Bower or NPM package, by default the package content will be
installed under the `@vendor/bower/PackageName` and `@vendor/npm/Packages` directories, respectively.
These two directories can also be referred to using the shorter aliases `@bower/PackageName` and `@npm/PackageName`.

更多关于资源管理的细节，请参考 [Assets](structure-assets.md#bower-npm-assets) 部分。


#### Class Autoloading <a name="class-autoloading"></a>

In order for your classes to be autoloaded by the Yii class autoloader or the Composer class autoloader,
you should specify the `autoload` entry in the `composer.json` file, 如下：

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

You may list one or multiple root namespaces and their corresponding file paths.

当在一个应用中安装一个扩展时，Yii will create for each listed root namespace
an [alias](concept-aliases.md#extension-aliases) that refers to the directory corresponding to the namespace.
例如，the above `autoload` declaration will correspond to an alias named `@yii/imagine`.


### Recommended Practices <a name="recommended-practices"></a>

Because extensions are meant to be used by other people, you often need to take extra development effort. Below
we introduce some common and recommended practices in creating high quality extensions.


#### 命名空间 <a name="namespaces"></a>

为了避免名字冲突和使得扩展里的类能自动加载，you should use namespaces and
name the classes in your extension by following the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/) or
[PSR-0 standard](http://www.php-fig.org/psr/psr-0/).

You class namespaces should start with `vendorName\extensionName`, where `extensionName` is similar to the project name
in the package name except that it should not contain the `yii2-` prefix. For example, for the `yiisoft/yii2-imagine`
extension, we use `yii\imagine` as the namespace its classes.

不要将 `yii`, `yii2` or `yiisoft` 作为 vendor 名称。这些名称是保留给 Yii 核心代码使用的。


#### Bootstrapping Classes <a name="bootstrapping-classes"></a>

某些时候，you may want your extension to execute some code during the [bootstrapping process](runtime-bootstrapping.md)
stage of an application. 例如，your extension may want to respond to the application's `beginRequest` event
to adjust some environment settings. While you can instruct users of the extension to explicitly attach your event
handler in the extension to the `beginRequest` event, 最好能够将这个过程自动化。

为了实现这一目标，you can create a so-called *bootstrapping class* by implementing [[yii\base\BootstrapInterface]].
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

You then list this class in the `composer.json` file of your extension like follows,

```json
{
    // ...

    "extra": {
        "bootstrap": "myname\\mywidget\\MyBootstrapClass"
    }
}
```

当在一个应用中安装一个扩展时，Yii will automatically instantiate the bootstrapping class
and call its [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] method during the bootstrapping process for
every request.


#### Working with Databases <a name="working-with-databases"></a>

你的扩展可能需要访问数据库。Do not assume that the applications that use your extension will always
use `Yii::$db` as the DB connection. 反而，you should declare a `db` property for the classes that require DB access.
The property will allow users of your extension to customize which DB connection they would like your extension to use.
举个例子，you may refer to the [[yii\caching\DbCache]] class and see how it declares and uses the `db` property.

如果你的扩展需要创建一个详细的 DB tables 或者 要改变 DB schema，需要

- 提供 [migrations](db-migrations.md) 来操控 DB schema，而不是使用普通的 SQL 文件；
- 尝试让迁移能够适应不同的 DBMS；
- 避免使用 migrations 中的 [Active Record](db-active-record.md)。


#### Using Assets <a name="using-assets"></a>

如果你的扩展是 a widget 或者 a module，chances are that it may require some [assets](structure-assets.md) to work.
例如，可能会显示一些包含 images, JavaScript, and CSS 的页面。Because the files of an
extension are all under the same directory which is not Web accessible when installed in an application, you have
two choices to make the asset files directly accessible via Web:

- ask users of the extension to manually copy the asset files to a specific Web-accessible folder;
- declare an [asset bundle](structure-assets.md) and rely on the asset publishing mechanism to automatically
  copy the files listed in the asset bundle to a Web-accessible folder.

We recommend you use the second approach so that your extension can be more easily used by other people.
Please refer to the [Assets](structure-assets.md) section for more details about how to work with assets in general.


#### Internationalization and Localization <a name="i18n-l10n"></a>

Your extension may be used by applications supporting different languages! 因此，if your extension displays
content to end users, you should try to [internationalize and localize](tutorial-i18n.md) it. In particular,

- If the extension displays messages intended for end users, the messages should be wrapped into `Yii::t()`
  so that they can be translated. Messages meant for developers (such as internal exception messages) do not need
  to be translated.
- If the extension displays numbers, dates, etc., they should be formatted using [[yii\i18n\Formatter]] with
  appropriate formatting rules.

了解更多细节，请参考 [Internationalization](tutorial-i18n.md) 部分的内容。


#### 测试 <a name="testing"></a>

如果你想要让你的扩展没有后顾之忧地完美运行。为了达到这个目的，你应该
在发布扩展前先测试它。

It is recommended that you create various test cases to cover your extension code rather than relying on manual tests.
每次在你发布新版本的扩展之前，你应该简单地运行这些测试案例
everything is in good shape. Yii 提供了测试支持，可以帮你更容易写单元测试，
验收测试和功能测试。更多详细内容，请参考 [Testing](test-overview.md) 部分。


#### Versioning <a name="versioning"></a>

你应该给每个扩展的发布配置一个版本号 (e.g. `1.0.1`)。We recommend you follow the
[semantic versioning](http://semver.org) practice when determining what version numbers should be used.


#### Releasing <a name="releasing"></a>

为了让其他人知道你的版本，你需要将它公之于众。

如果这是你第一次发布一个扩展，你应该先在 Composer 库里注册，比如
[Packagist](https://packagist.org/)。 After that, all you need to do is simply creating a release tag (e.g. `v1.0.1`)
on the VCS repository of your extension and notify the Composer repository about the new release. People will
then be able to find the new release, and install or update the extension through the Composer repository.

在你扩展的版本中，除了代码文件外你还需要注意以下几点
来帮助其它人了解和使用你的扩展：

* 在包根目录里的 readme 文件：它描述了你的扩展功能以及如何安装和使用.
  We recommend you write it in [Markdown](http://daringfireball.net/projects/markdown/) format and name the file
  as `readme.md`.
* 在包根目录中更新日志文件：它列出了每个版本所做的变化。文件
  可以被写成 Markdown 格式并命名为 `changelog.md`。
* 在包根目录下升级文件：它提供了有关如何从旧版本升级
  的说明。该文件可以被写成 Markdown 格式并命名为 `upgrade.md`。
* 教程，demos, screenshots, etc.: these are needed if your extension provides many features that cannot be
  fully covered in the readme file.
* API documentation:你的代码应该好好地记录下来。
  你可以参考 [Object class file](https://github.com/yiisoft/yii2/blob/master/framework/base/Object.php)
  来学习如何记录代码。

> 提示信息：你代码中的注释可以写成 Markdown 格式。`yiisoft/yii2-apidoc` 扩展为你提供了一
  个基于代码注释生成的完美 API 文档工具。

> 提示信息：虽然不是必须的，我们仍建议你的应保持统一的代码风格。可以
  参考 [core framework code style](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style)。


## Core Extensions <a name="core-extensions"></a>

Yii 提供了以下核心扩展，这些扩展是由 Yii 开发团队维护和开发。它们都是在
[Packagist](https://packagist.org/) 注册的并且可以很容易的被安装在
[Using Extensions](#using-extensions) 部分中。

- [yiisoft/yii2-apidoc](https://github.com/yiisoft/yii2-apidoc):
  提供一个可扩展并且是高性能的 API 文档生成器。It is also used to generate the core
  framework API documentation.
- [yiisoft/yii2-authclient](https://github.com/yiisoft/yii2-authclient):
  provides a set of commonly used auth clients, such as Facebook OAuth2 client, GitHub OAuth2 client.
- [yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2-bootstrap):
  provides a set of widgets that encapsulate the [Bootstrap](http://getbootstrap.com/) components and plugins.
- [yiisoft/yii2-codeception](https://github.com/yiisoft/yii2-codeception):
  provides testing support based on [Codeception](http://codeception.com/).
- [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug):
  provides debugging support for Yii applications. When this extension is used, a debugger toolbar will appear
  at the bottom of every page. The extension also provides a set of standalone pages to display more detailed
  debug information.
- [yiisoft/yii2-elasticsearch](https://github.com/yiisoft/yii2-elasticsearch):
  provides the support for using [Elasticsearch](http://www.elasticsearch.org/). It includes basic querying/search
  support and also implements the [Active Record](db-active-record.md) pattern that allows you to store active records
  in Elasticsearch.
- [yiisoft/yii2-faker](https://github.com/yiisoft/yii2-faker):
  provides the support for using [Faker](https://github.com/fzaninotto/Faker) to generate fake data for you.
- [yiisoft/yii2-gii](https://github.com/yiisoft/yii2-gii):
  provides a Web-based code generator that is highly extensible and can be used to quickly generate models,
  forms, modules, CRUD, etc.
- [yiisoft/yii2-imagine](https://github.com/yiisoft/yii2-imagine):
  provides commonly used image manipulation functions based on [Imagine](http://imagine.readthedocs.org/).
- [yiisoft/yii2-jui](https://github.com/yiisoft/yii2-jui):
  provides a set of widgets that encapsulate the [JQuery UI](http://jqueryui.com/) interactions and widgets.
- [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb):
  provides the support for using [MongoDB](http://www.mongodb.org/). It includes features such as basic query,
  Active Record, migrations, caching, code generation, etc.
- [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis):
  provides the support for using [redis](http://redis.io/). It includes features such as basic query,
  Active Record, caching, etc.
- [yiisoft/yii2-smarty](https://github.com/yiisoft/yii2-smarty):
  provides a template engine based on [Smarty](http://www.smarty.net/).
- [yiisoft/yii2-sphinx](https://github.com/yiisoft/yii2-sphinx):
  provides the support for using [Sphinx](http://sphinxsearch.com). It includes features such as basic query,
  Active Record, code generation, etc.
- [yiisoft/yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer):
  provides email sending features based on [swiftmailer](http://swiftmailer.org/).
- [yiisoft/yii2-twig](https://github.com/yiisoft/yii2-twig):
  provides a template engine based on [Twig](http://twig.sensiolabs.org/).
