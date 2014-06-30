Extensions
==========

> Note: This section is under development.

Extensions are redistributable software packages specifically designed to be used in Yii applications and provide
ready-to-use features. For example, the [yiisoft/yii2-debug](tool-debugger.md) extension adds a handy debug toolbar
at the bottom of every page in your application to help you more easily grasp how the pages are generated. You can
use extensions to accelerate your development process. You can also package your code as extensions to share with
other people your great work.

> Info: We use the term "extension" to refer to Yii-specific software packages. For general purpose software packages
  that can be used without Yii, we will refer to them using the term "package".


## Using Extensions

To use an extension, you need to install it first. Most extensions are distributed as [Composer](https://getcomposer.org/)
packages, and you can take the following two simple steps to install such an extension:

1. modify the `composer.json` file of your application and specify which extensions (Composer packages) you want to install.
2. run `php composer.phar install` to install the specified extensions.

You may need to install [Composer](https://getcomposer.org/) if you do not have it. Composer is a dependency
manager. This means when installing a package, it will install all its dependent packages automatically.

> Info: By default, Composer installs packages registered on [Packagist](https://packagist.org/) - the biggest repository
  for open source Composer packages. You may also [create your own repository](https://getcomposer.org/doc/05-repositories.md#repository)
  and configure Composer to use it. This is useful if you are developing closed open extensions and want to share
  within your projects.

Extensions installed by Composer are stored under the `BasePath/vendor` directory, where `BasePath` refers to the
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

> Info: Extension classes are autoloaded using the [Yii class autoloader](concept-autoloading.md). Yii automatically
  creates [aliases](concept-aliases.md#extension-aliases) for the root namespaces declared by the extensions.

Also make sure in the [application configuration](structure-applications.md#application-configurations), you have
configured `extension


## Creating Extensions

You may consider creating an extension when you feel the need to redistribute some of your great code so that
they can be easily reused by other people or in your other projects.

An extension can contain any code you like, such as a helper class, a widget, a module, etc.

It is recommended that you create an extension in terms of a Composer package so that it can be more easily
used elsewhere, liked described in the last subsection. Below are the steps you may follow to create an extension.

1. Put all the files you plan to include in the extension in a single directory. The directory should contain
   no other irrelevant files. For simplicity, let's call this directory the extension's *root directory*.
2. Create a `composer.json` file directly under the root directory. The file is required by Composer, which describes
   the metadata about your extension. Please refer to the [Composer Manual](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup)
   for more details about the file format.
3. Create a VCS (version control system) repository to host the extension files. Any future development
   and maintenance work about the extension should be done on this repository.
4. Register your extension with a Composer repository so that other users can find and install your extension.
   If you are creating an open source extension, you can register it with [Packagist](https://packagist.org/);
   If you are creating a private extension for internal use, you may register it with
   [your own repository](https://getcomposer.org/doc/05-repositories.md#hosting-your-own).

As an example, you may refer to the [yiisoft/yii2-bootstrap](widget-bootstrap) extension which provides a set of
widgets encapsulating the Twitter Bootstrap plugins. The extension is hosted on [GitHub](https://github.com/yiisoft/yii2-bootstrap)
and registered with [Packagist](https://packagist.org/packages/yiisoft/yii2-bootstrap). Below is the content
of its `composer.json` file (some unimportant content is removed for simplicity):

```json
{
    "name": "yiisoft/yii2-bootstrap",
    "description": "The Twitter Bootstrap extension for the Yii framework",
    "keywords": ["yii2", "bootstrap"],
    "type": "yii2-extension",
    "license": "BSD-3-Clause",
    "require": {
        "yiisoft/yii2": "*",
        "twbs/bootstrap": "3.1.* | 3.0.*"
    },
    "autoload": {
        "psr-4": {
            "yii\\bootstrap\\": ""
        }
    }
}
```


### Yii2 Extensions

When creating a Composer package, you may specify the package type to be `yii2-extension`, like shown in the example
in the last subsection. This is recommended if your package is an extension that is specifically designed to be
used in Yii applications. By using `yii2-extension` as the package type, your package can get the following extra benefits:


For example, if your package contains a Yii [widget](structure-widgets.md), it is most likely
that the package


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

