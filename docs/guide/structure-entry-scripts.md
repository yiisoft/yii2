Entry Scripts
=============

> Note: This section is under development.

Configuring options in the bootstrap file
-----------------------------------------

For each application in Yii there is at least one bootstrap file: a PHP script through which all requests are handled. For web applications, the bootstrap file is  typically `index.php`; for
console applications, the bootstrap file is `yii`. Both bootstrap files perform nearly the same job:

1. Setting common constants.
2. Including the Yii framework itself.
3. Including [Composer autoloader](http://getcomposer.org/doc/01-basic-usage.md#autoloading).
4. Reading the configuration file into `$config`.
5. Creating a new application instance, configured via `$config`, and running that instance.

Like any resource in your Yii application, the bootstrap file can be edited to fit your needs. A typical change is to the value of `YII_DEBUG`. This constant should be `true` during development, but always `false` on production sites.

The default bootstrap structure sets `YII_DEBUG` to `false` if not defined:

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

During development, you can change this to `true`:

```php
define('YII_DEBUG', true); // Development only
defined('YII_DEBUG') or define('YII_DEBUG', false);
```
