Autoloading
===========

> Note: This chapter is under development.

All classes, interfaces and traits are loaded automatically at the moment they are used. There's no need to use `include` or `require`. It is true for Composer-loaded packages as well as Yii extensions.

Yii's autoloader works according to [PSR-4](https://github.com/php-fig/fig-standards/blob/master/proposed/psr-4-autoloader/psr-4-autoloader.md).
That means namespaces, classes, interfaces and traits must correspond to file system paths and file names accordinly, except for root namespace paths that are defined by an alias.

For example, if the standard alias `@app` refers to `/var/www/example.com/` then `\app\models\User` will be loaded from `/var/www/example.com/models/User.php`.

Custom aliases may be added using the following code:

```php
Yii::setAlias('@shared', realpath('~/src/shared'));
```

Additional autoloaders may be registered using PHP's standard `spl_autoload_register`.
