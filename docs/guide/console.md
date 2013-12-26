Console applications
====================

Yii has full featured support of console. Console application structure in Yii is very similar to web application. It
consists of one or more [[\yii\console\Controller]] (often referred to as commands). Each has one or more actions.

Usage
-----

You can execute controller action using the following syntax:

```
yii <route> [--param1=value1 --param2 ...]
```

For example, `MigrationController::create` with `MigrationController::$migrationTable` set can be called from command
line like the following:

```
yii migreate/create --migrationTable=my_migration
```

In the above `yii` is console application entry script described below.

Entry script
------------

Console application entry script is typically called `yii`, located in your application root directory and contains
code like the following:

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

// fcgi doesn't have STDIN defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/yii/Yii.php');

$config = require(__DIR__ . '/config/console.php');

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);

```

This script is a part of your application so you're free to adjust it. There `YII_DEBUG` can be turned off if you do
not want to see stacktrace on error and want to improve overall performance. In both basic and advanced application
templates it is enabled to provide more developer-friendly environment.

Configuration
-------------

As can be seen in the code above, console application uses its own config files named `console.php` so you need to
configure database connection and the rest of the components you're going to use there in that file. If web and console
application configs have a lot in common it's a good idea to move matching parts into their own config files as it was
done in advanced application template.


Creating your own console commands
----------------------------------

### Controller

### Action

### Parameters

### Return codes

Using return codes is the best practice of console application development. If command returns `0` it means everything
is OK. If it is a number more than zero, we have an error and integer returned is the error code.