Class Autoloading
=================

> Note: This chapter is under development.

Yii relies on the [class autoloading mechanism](http://www.php.net/manual/en/language.oop5.autoload.php)
to locate and include required class files. A class file will be automatically included by a class autoloader
when the corresponding class is referenced for the first time during the code execution. Because only
the necessary files are included and parsed, it improves the application performance.

Yii comes with a high-performance class autoloader which is installed when you include the `framework/Yii.php` file
in your [entry script](structure-entry-scripts.md). The autoloader is compliant to the
[PSR-4 standard](https://github.com/php-fig/fig-standards/blob/master/proposed/psr-4-autoloader/psr-4-autoloader.md).

Below is a list of the rules that you should follow if you want to use the Yii class autoloader to autoload
your class files.

*
It has the benefit that a class file is included onl


All classes, interfaces and traits are loaded automatically at the moment they are used.
There's no need to use `include` or `require`. It is true for Composer-loaded packages as well as Yii extensions.

Yii's autoloader works according to the [PSR-4 standard](https://github.com/php-fig/fig-standards/blob/master/proposed/psr-4-autoloader/psr-4-autoloader.md).
That means namespaces, classes, interfaces and traits must correspond to file system paths and file names accordingly,
except for root namespace paths that are defined by an alias.

For example, if the standard alias `@app` refers to `/var/www/example.com/` then `\app\models\User` will be loaded from `/var/www/example.com/models/User.php`.

Custom aliases may be added using the following code:

```php
Yii::setAlias('@shared', realpath('~/src/shared'));
```

Additional autoloaders may be registered using PHP's standard `spl_autoload_register`.
