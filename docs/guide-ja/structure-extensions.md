エクステンション
================

エクステンションは、Yii のアプリケーションで使われることに限定して設計され、そのまますぐに使える機能を提供する
再配布可能なソフトウェアパッケージです。例えば、[yiisoft/yii2-debug](tool-debugger.md) エクステンションは、
あなたのアプリケーションにおいて、全てのページの末尾に便利なデバッグツールバーを追加して、ページが生成される過程を
より容易に把握できるように手助けしてくれます。エクステンションを使うと、あなたの開発プロセスを加速することが出来ます。
また、あなたのコードをエクステンションとしてパッケージ化すると、あなたの優れた仕事を他の人たちと共有することが出来ます。

> Info|情報: 「エクステンション」という用語は Yii に限定されたソフトウェアパッケージを指すものとして使用します。
  Yii がなくても使用できる汎用のソフトウェアパッケージを指すためには、「パッケージ」または「ライブラリ」という用語を使うことにします。

## エクステンションを使う <a name="using-extensions"></a>

エクステンションを使うためには、先ずはそれをインストールする必要があります。ほとんどのエクステンションは [Composer](https://getcomposer.org/)
のパッケージとして配布されていて、次の二つの簡単なステップをふめばインストールすることが出来ます:

1. アプリケーションの `composer.json` ファイルを修正して、どのエクステンション (Composer パッケージ) をインストールしたいかを指定する。
2. `composer install` コマンドを走らせて指定したエクステンションをインストールする。

[Composer](https://getcomposer.org/) を持っていない場合は、それをインストールする必要があることに注意してください。

既定では、Composer は [Packagist](https://packagist.org/) に登録されたパッケージをインストールします。Packagist は
オープンソース Composer パッケージの最大のレポジトリであり、そこでエクステンションを探すことが出来ます。
また、[自分自身のレポジトリを作成](https://getcomposer.org/doc/05-repositories.md#repository) して、それを使うように
Composer を構成することも出来ます。これは、あなたがプライベートなエクステンションを開発していて、
それを自分のプロジェクト間でのみ共有したい場合に役に立つ方法です。

Composer によってインストールされるエクステンションは `BasePath/vendor` ディレクトリに保存されます。ここで `BasePath`
は、アプリケーションの [ベースパス](structure-applications.md#basePath) を指します。Composer は依存関係を管理するものですから、
パッケージをインストールするときには、それが依存している全てのパッケージをも同時にインストールします。

例えば、`yiisoft/yii2-imagine` エクステンションをインストールするためには、あなたの `composer.json` を次のように修正します:

```json
{
    // ...

    "require": {
        // ... 他の依存パッケージ

        "yiisoft/yii2-imagine": "*"
    }
}
```

インストール完了後には、`BasePath/vendor` の下に `yiisoft/yii2-imagine` ディレクトリが作られている筈です。それと同時に、
`imagine/imagine` という別のディレクトリも作られて、依存するパッケージがそこにインストールされている筈です。

> Info|情報: `yiisoft/yii2-imagine` は Yii 開発チームによって開発され保守されるコアエクステンションの一つです。
  全てのコアエクステンションは [Packagist](https://packagist.org/) でホストされ、`yiisoft/yii2-xyz` のように名付けられます。
  ここで `xyz` はエクステンションによってさまざまに変ります。

これであなたはインストールされたエクステンションをあなたのアプリケーションの一部であるかのように使うことが出来ます。
次の例は、`yiisoft/yii2-imagine` エクステンションによって提供される `yii\imagine\Image` クラスをどのようにして使うことが
出来るかを示すものです:

```php
use Yii;
use yii\imagine\Image;

// サムネール画像を生成する
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> Info|情報: エクステンションのクラスは [Yii クラスオートローダ](concept-autoloading.md) によってオートロードされます。


### エクステンションを手作業でインストールする <a name="installing-extensions-manually"></a>

あまり無いことですが、いくつかまたは全てのエクステンションを Composer に頼らずに手作業でインストールしたい場合があるかもしれません。
そうするためには、次のようにしなければなりません:

1. エクステンションのアーカイブファイルをダウンロードして、`vendor` ディレクトリに解凍する。
2. もし有れば、エクステンションによって提供されているクラスオートローダをインストールする。
3. 指示に従って、依存するエクステンションを全てダウンロードしインストールする。

エクステンションがクラスオートローダを持っていなくても、[PSR-4 標準](http://www.php-fig.org/psr/psr-4/) に従っている場合は、
Yii によって提供されているクラスオートローダを使ってエクステンションのクラスをオートロードすることが出来ます。必要なことは、
エクステンションのルートディレクトリのための [ルートエイリアス](concept-aliases.md#defining-aliases) を宣言することだけです。
例えば、エクステンションを `vendor/mycompany/myext` というディレクトリにインストールしたと仮定します。そして、エクステンションの
クラスは `myext` 名前空間の下にあるとします。その場合、アプリケーションのコンフィギュレーションに下記のコードを含めれば良いのです:

```php
[
    'aliases' => [
        '@myext' => '@vendor/mycompany/myext',
    ],
]
```


## エクステンションを作成する <a name="creating-extensions"></a>

あなたの優れたコードを他の人々と共有する必要があると感じたときは、エクステンションを作成することを考慮するのが良いでしょう。
エクステンションは、ヘルパークラス、ウィジェット、モジュールなど、どのようなコードでも含むことが出来ます。

エクステンションは、[Composer パッケージ](https://getcomposer.org/) の形式で作成することが推奨されます。そうすれば、
直前の項で説明したように、いっそう容易に他のユーザによってインストールされ、使用されることが出来ます。

以下は、エクステンションを Composer のパッケージとして作成するために従う基本的なステップです。

1. Create a project for your extension and host it on a VCS repository, such as [github.com](https://github.com).
   The development and maintenance work about the extension should be done on this repository.
2. Under the root directory of the project, create a file named `composer.json` as required by Composer. Please
   refer to the next subsection for more details.
3. Register your extension with a Composer repository, such as [Packagist](https://packagist.org/), so that
   other users can find and install your extension using Composer.


### `composer.json` <a name="composer-json"></a>

Each Composer package must have a `composer.json` file in its root directory. The file contains the metadata about
the package. You may find complete specification about this file in the [Composer Manual](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup).
The following example shows the `composer.json` file for the `yiisoft/yii2-imagine` extension:

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

Each Composer package should have a package name which uniquely identifies the package among all others.
The format of package names is `vendorName/projectName`. For example, in the package name `yiisoft/yii2-imagine`,
the vendor name and the project name are `yiisoft` and `yii2-imagine`, respectively.

Do NOT use `yiisoft` as vendor name as it is reserved for use by the Yii core code.

We recommend you prefix `yii2-` to the project name for packages representing Yii 2 extensions, for example,
`myname/yii2-mywidget`. This will allow users to more easily tell whether a package is a Yii 2 extension.


#### Package Type <a name="package-type"></a>

It is important that you specify the package type of your extension as `yii2-extension` so that the package can
be recognized as a Yii extension when being installed.

When a user runs `composer install` to install an extension, the file `vendor/yiisoft/extensions.php`
will be automatically updated to include the information about the new extension. From this file, Yii applications
can know which extensions are installed (the information can be accessed via [[yii\base\Application::extensions]].


#### Dependencies <a name="dependencies"></a>

Your extension depends on Yii (of course). So you should list it (`yiisoft/yii2`) in the `require` entry in `composer.json`.
If your extension also depends on other extensions or third-party libraries, you should list them as well.
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

The above code states that the extension depends on the `jquery` Bower package. In general, you can use
`bower-asset/PackageName` to refer to a Bower package in `composer.json`, and use `npm-asset/PackageName`
to refer to a NPM package. When Composer installs a Bower or NPM package, by default the package content will be
installed under the `@vendor/bower/PackageName` and `@vendor/npm/Packages` directories, respectively.
These two directories can also be referred to using the shorter aliases `@bower/PackageName` and `@npm/PackageName`.

For more details about asset management, please refer to the [Assets](structure-assets.md#bower-npm-assets) section.


#### Class Autoloading <a name="class-autoloading"></a>

In order for your classes to be autoloaded by the Yii class autoloader or the Composer class autoloader,
you should specify the `autoload` entry in the `composer.json` file, like shown below:

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

When the extension is installed in an application, Yii will create for each listed root namespace
an [alias](concept-aliases.md#extension-aliases) that refers to the directory corresponding to the namespace.
For example, the above `autoload` declaration will correspond to an alias named `@yii/imagine`.


### Recommended Practices <a name="recommended-practices"></a>

Because extensions are meant to be used by other people, you often need to take extra development effort. Below
we introduce some common and recommended practices in creating high quality extensions.


#### Namespaces <a name="namespaces"></a>

To avoid name collisions and make the classes in your extension autoloadable, you should use namespaces and
name the classes in your extension by following the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/) or
[PSR-0 standard](http://www.php-fig.org/psr/psr-0/).

Your class namespaces should start with `vendorName\extensionName`, where `extensionName` is similar to the project name
in the package name except that it should not contain the `yii2-` prefix. For example, for the `yiisoft/yii2-imagine`
extension, we use `yii\imagine` as the namespace its classes.

Do not use `yii`, `yii2` or `yiisoft` as vendor name. These names are reserved for use by the Yii core code.


#### Bootstrapping Classes <a name="bootstrapping-classes"></a>

Sometimes, you may want your extension to execute some code during the [bootstrapping process](runtime-bootstrapping.md)
stage of an application. For example, your extension may want to respond to the application's `beginRequest` event
to adjust some environment settings. While you can instruct users of the extension to explicitly attach your event
handler in the extension to the `beginRequest` event, a better way is to do this automatically.

To achieve this goal, you can create a so-called *bootstrapping class* by implementing [[yii\base\BootstrapInterface]].
For example,

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

When the extension is installed in an application, Yii will automatically instantiate the bootstrapping class
and call its [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] method during the bootstrapping process for
every request.


#### Working with Databases <a name="working-with-databases"></a>

Your extension may need to access databases. Do not assume that the applications that use your extension will always
use `Yii::$db` as the DB connection. Instead, you should declare a `db` property for the classes that require DB access.
The property will allow users of your extension to customize which DB connection they would like your extension to use.
As an example, you may refer to the [[yii\caching\DbCache]] class and see how it declares and uses the `db` property.

If your extension needs to create specific DB tables or make changes to DB schema, you should

- provide [migrations](db-migrations.md) to manipulate DB schema, rather than using plain SQL files;
- try to make the migrations applicable to different DBMS;
- avoid using [Active Record](db-active-record.md) in the migrations.


#### Using Assets <a name="using-assets"></a>

If your extension is a widget or a module, chances are that it may require some [assets](structure-assets.md) to work.
For example, a module may display some pages which contain images, JavaScript, and CSS. Because the files of an
extension are all under the same directory which is not Web accessible when installed in an application, you have
two choices to make the asset files directly accessible via Web:

- ask users of the extension to manually copy the asset files to a specific Web-accessible folder;
- declare an [asset bundle](structure-assets.md) and rely on the asset publishing mechanism to automatically
  copy the files listed in the asset bundle to a Web-accessible folder.

We recommend you use the second approach so that your extension can be more easily used by other people.
Please refer to the [Assets](structure-assets.md) section for more details about how to work with assets in general.


#### Internationalization and Localization <a name="i18n-l10n"></a>

Your extension may be used by applications supporting different languages! Therefore, if your extension displays
content to end users, you should try to [internationalize and localize](tutorial-i18n.md) it. In particular,

- If the extension displays messages intended for end users, the messages should be wrapped into `Yii::t()`
  so that they can be translated. Messages meant for developers (such as internal exception messages) do not need
  to be translated.
- If the extension displays numbers, dates, etc., they should be formatted using [[yii\i18n\Formatter]] with
  appropriate formatting rules.

For more details, please refer to the [Internationalization](tutorial-i18n.md) section.


#### Testing <a name="testing"></a>

You want your extension to run flawlessly without bringing problems to other people. To reach this goal, you should
test your extension before releasing it to public.

It is recommended that you create various test cases to cover your extension code rather than relying on manual tests.
Each time before you release a new version of your extension, you may simply run these test cases to make sure
everything is in good shape. Yii provides testing support, which can help you to more easily write unit tests,
acceptance tests and functionality tests. For more details, please refer to the [Testing](test-overview.md) section.


#### Versioning <a name="versioning"></a>

You should give each release of your extension a version number (e.g. `1.0.1`). We recommend you follow the
[semantic versioning](http://semver.org) practice when determining what version numbers should be used.


#### Releasing <a name="releasing"></a>

To let other people know your extension, you need to release it to public.

If it is the first time you release an extension, you should register it on a Composer repository, such as
[Packagist](https://packagist.org/). After that, all you need to do is simply creating a release tag (e.g. `v1.0.1`)
on the VCS repository of your extension and notify the Composer repository about the new release. People will
then be able to find the new release, and install or update the extension through the Composer repository.

In the releases of your extension, besides code files you should also consider including the followings to
help other people learn about and use your extension:

* A readme file in the package root directory: it describes what your extension does and how to install and use it.
  We recommend you write it in [Markdown](http://daringfireball.net/projects/markdown/) format and name the file
  as `readme.md`.
* A changelog file in the package root directory: it lists what changes are made in each release. The file
  may be written in Markdown format and named as `changelog.md`.
* An upgrade file in the package root directory: it gives the instructions on how to upgrade from older releases
  of the extension. The file may be written in Markdown format and named as `upgrade.md`.
* Tutorials, demos, screenshots, etc.: these are needed if your extension provides many features that cannot be
  fully covered in the readme file.
* API documentation: your code should be well documented to allow other people more easily read and understand it.
  You may refer to the [Object class file](https://github.com/yiisoft/yii2/blob/master/framework/base/Object.php)
  to learn how to document your code.

> Info: Your code comments can be written in Markdown format. The `yiisoft/yii2-apidoc` extension provides a tool
  for you to generate pretty API documentation based on your code comments.

> Info: While not a requirement, we suggest your extension adhere to certain coding styles. You may refer to
  the [core framework code style](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style).


## Core Extensions <a name="core-extensions"></a>

Yii provides the following core extensions that are developed and maintained by the Yii developer team. They are all
registered on [Packagist](https://packagist.org/) and can be easily installed as described in the
[Using Extensions](#using-extensions) subsection.

- [yiisoft/yii2-apidoc](https://github.com/yiisoft/yii2-apidoc):
  provides an extensible and high-performance API documentation generator. It is also used to generate the core
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
