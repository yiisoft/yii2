Extensions
==========

> Note: This section is under development.

Extensions are redistributable software packages that extend Yii by providing extra features. For example,
the [yii2-debug](tool-debugger.md) extension adds a handy debug toolbar to every page in your application
to help you more easily grasp how the pages are generated. You can install and use extensions in your
applications to accelerate your development process. You can also package your code in terms of extensions
to share with other people your great work.


## Getting Extensions

The easiest way of getting extensions is using [Composer](https://getcomposer.org/). The name "extension"
is also known as "package" in Composer's terminology. To do so, you will need to

1. install [Composer](https://getcomposer.org/), which you probably have already done when
   [installing Yii](start-installation.md).
2. [specify which repositories](https://getcomposer.org/doc/05-repositories.md#repository) you would like
   to get extensions from. In most cases you can skip this if you only want to install open source extensions
   hosted on [Packagist](https://packagist.org/) - the default and biggest Composer repository.
3. modify the `composer.json` file of your application and specify which extensions you want to use.
4. run `php composer.phar install` to install the specified extensions.

You usually only need to do Step 1 and 2 once. You may need to do Step 3 and 4 multiple times depending on your
evolving requirements for extensions.

By default, extensions installed by Composer are stored under the `BasePath/vendor` directory, where `BasePath`
refers to the application's [base path](structure-applications.md#basePath).

For example, to get the `yii2-imagine` extension maintained officially by Yii, modify your `composer.json` like the following:

```json
{
    // ...

    "require": {
        // ... other dependencies

        "yiisoft/yii2-imagine": "*"
    }
}
```

The installed extension will be located in the `vendor/yiisoft/yii2-imagine` directory.

> Info: All extensions officially maintained by Yii are named as `yiisoft/yii2-xyz`, where `xyz` varies for different
  extensions.


## Using Extensions

In order for your applications to use extensions, you will need to install Composer's class autoloader. This is
necessary such that PHP classes in extensions can be properly autoloaded when you reference them in the application code.
To do so, use the following lines in the [entry script](concept-entry-scripts.md) of your application:

```php
// install Composer's class autoloader
require(__DIR__ . '/../vendor/autoload.php');

// include Yii class file
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
```

> Info: If you are using the [basic application template](start-installation.md) or
[advanced application template](tutorial-advanced-app.md), you do not need to do anything because the application templates
already contain the above code.

Now you can enjoy using the installed extensions. For example, to use the `yii2-imagine` extension shown in the last
subsection, you can write code like the following in your application, where `Image` is the class provided by
the extension:

```php
use Yii;
use yii\imagine\Image;

// generate a thumbnail image
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```


## Creating Extensions

## Core Extensions

