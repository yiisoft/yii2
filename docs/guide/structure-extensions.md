Extensions
==========

Extensions are redistributable software packages specifically designed to be used in Yii applications and provide
ready-to-use features. For example, the [yiisoft/yii2-debug](tool-debugger.md) extension adds a handy debug toolbar
at the bottom of every page in your application to help you more easily grasp how the pages are generated. You can
use extensions to accelerate your development process. You can also package your code as extensions to share with
other people your great work.

> Info: We use the term "extension" to refer to Yii-specific software packages. For general purpose software packages
  that can be used without Yii, we will refer to them using the term "package" or "library".


## Using Extensions

To use an extension, you need to install it first. Most extensions are distributed as [Composer](https://getcomposer.org/)
packages which can be installed by taking the following two simple steps:

1. modify the `composer.json` file of your application and specify which extensions (Composer packages) you want to install.
2. run `php composer.phar install` to install the specified extensions.

You may need to install [Composer](https://getcomposer.org/) if you do not have it. Composer is a dependency
manager. This means when installing a package, it will install all its dependent packages automatically.

By default, Composer installs packages registered on [Packagist](https://packagist.org/) - the biggest repository
for open source Composer packages. You can look for extensions on Packagist. You may also
[create your own repository](https://getcomposer.org/doc/05-repositories.md#repository) and configure Composer
to use it. This is useful if you are developing closed open extensions and want to share within your projects.

Extensions installed by Composer are stored in the `BasePath/vendor` directory, where `BasePath` refers to the
application's [base path](structure-applications.md#basePath).

For example, to install the `yiisoft/yii2-imagine` extension, modify your `composer.json` like the following:

```json
{
    // ...

    "require": {
        // ... other dependencies

        "yiisoft/yii2-imagine": "*"
    }
}
```

After the installation, you should see the directory `yiisoft/yii2-imagine` under `BasePath/vendor`. You should
also see another directory `imagine/imagine` which contains the installed dependent package.

> Info: The `yiisoft/yii2-imagine` is a core extension developed and maintained by the Yii developer team. All
  core extensions are hosted on [Packagist](https://packagist.org/) and named like `yiisoft/yii2-xyz`, where `xyz`
  varies for different extensions.

Now you can use the installed extensions like they are part of your application. The following example shows
how you can use the `yii\imagine\Image` class provided by the `yiisoft/yii2-imagine` extension:

```php
use Yii;
use yii\imagine\Image;

// generate a thumbnail image
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> Info: Extension classes are autoloaded by the [Yii class autoloader](concept-autoloading.md).


## Creating Extensions

An extension can contain any code you like, such as a helper class, a widget, a module, etc.

You may consider creating an extension when you feel the need to redistribute some of your great code so that
they can be easily reused by other people or in your other projects.

It is recommended that you create an extension in terms of a [Composer package](https://getcomposer.org/) so that
it can be more easily installed and used by other users, liked described in the last subsection.


### Basic Steps

Below are the basic steps you may follow to create an extension.

1. Create a project for your extension and host it on a VCS repository, such as [github.com](https://github.com).
   Development and maintenance work about the extension should be done on this repository.
2. Under the root directory of the project, create a file named `composer.json` as required by Composer. Please
   refer to the next subsection for more details.
3. Register your extension with a Composer repository so that other users can find and install your extension.
   If you are creating an open source extension, you can register it with [Packagist](https://packagist.org/);
   If you are creating a private extension for internal use, you may register it with
   [your own repository](https://getcomposer.org/doc/05-repositories.md#hosting-your-own).


### `composer.json`

Each Composer package must have a `composer.json` file in its root directory. The file contains the metadata about
the package. You may find complete specification about this file in the [Composer Manual](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup).
You may also refer to the following example which is the `composer.json` file for the `yiisoft/yii2-imagine` extension:

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

> Info: It is important that you specify the package type as `yii2-extension` so that the package can
  be recognized as a Yii extension when being installed.


### Package Names

Each extension, when released as a Composer package, should have a package name which uniquely identifies itself
among all other packages. The format of package names is `vendorName/projectName`. For example, in the package name
`yiisoft/yii2-imagine`, the vendor name and the project name are `yiisoft` and `yii2-imagine`, respectively.

Do NOT use `yiisoft` as vendor name as this is reserved for use by the Yii core code.

Also, to easily tell from package name whether a package is a Yii extension, we recommend you prefix `yii2-`
to the project name.


### Namespaces

To avoid name collisions and make the classes in your extension autoloadable, you should use namespaces and
name the classes in your extension by following the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/) or
[PSR-0 standard](http://www.php-fig.org/psr/psr-0/).

You class namespaces should start with `vendorName\extensionName`, where `extensionName` is similar to the project name
in the package name except that it should not contain the `yii2-` prefix. For example, for the `yiisoft/yii2-imagine`
extension, we use `yii\imagine` as the namespace its classes.

Do not use `yii`, `yii2` or `yiisoft` as vendor name. These names are reserved for use by the Yii core code.


### Class Autoloading

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

When the extension is installed in an application, Yii will create an [alias](concept-aliases.md#extension-aliases)
for each listed root namespace. The alias will refer to the directory corresponding to the root namespace.
For example, the above `autoload` declaration will correspond to an alias named `@yii/imagine`.


### Bootstrapping Classes

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
and call its [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] during the bootstrapping process for
every request.


## Installing Extensions Manually

In some rare occasions, you may want to install some or all extensions manually, rather than relying on Composer.
To do so, you should

1. download the extension archive files and unpack them in the `vendor` directory.
2. install the class autoloaders provided by the extensions, if any.
3. download and install all dependent extensions as instructed.

If an extension does not have a class autoloader but follows the
[PSR-4 standard](https://github.com/php-fig/fig-standards/blob/master/proposed/psr-4-autoloader/psr-4-autoloader.md),
you may use the class autoloader provided by Yii to autoload the extension classes. All you need to do is just to
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


## Core Extensions


## Best Practices

