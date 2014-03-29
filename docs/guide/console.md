Console applications
====================

Yii has full featured support of console. Console application structure in Yii is very similar to web application. It
consists of one or more [[yii\console\Controller]] (often referred to as commands). Each has one or more actions.

Usage
-----

You can execute controller action using the following syntax:

```
yii <route> [--option1=value1 --option2=value2 ... argument1 argument2 ...]
```

For example, [[yii\console\controllers\MigrateController::actionCreate()|MigrateController::actionCreate()]]
with [[yii\console\controllers\MigrateController::$migrationTable|MigrateController::$migrationTable]] set can
be called from command line like the following:

```
yii migrate/create --migrationTable=my_migration
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

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/config/console.php');

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);

```

This script is a part of your application so you're free to adjust it. The `YII_DEBUG` constant can be set `false` if you do
not want to see stack trace on error and want to improve overall performance. In both basic and advanced application
templates it is enabled to provide more developer-friendly environment.

Configuration
-------------

As can be seen in the code above, console application uses its own config files named `console.php`. In this file,
you should specify how to configure various application components and properties.

If your Web application and the console application share a lot of configurations, you may consider moving the common
part into a separate file, and include this file in both of the application configurations, just as what is done
in the "advanced" application template.

Sometimes, you may want to run a console command using an application configuration that is different from the one
specified in the entry script. For example, you may want to use the `yii migrate` command to upgrade your
test databases which are configured in each individual test suite. To do so, simply specify the custom application configuration
file via the `appconfig` option, like the following,

```
yii <route> --appconfig=path/to/config.php ...
```


Creating your own console commands
----------------------------------

### Console Controller and Action

A console command is defined as a controller class extending from [[yii\console\Controller]]. In the controller class,
you define one or several actions that correspond to the sub-commands of the command. Within each action, you write code
to implement certain tasks for that particular sub-command.

When running a command, you need to specify the route to the corresponding controller action. For example,
the route `migrate/create` specifies the sub-command corresponding to the
[[yii\console\controllers\MigrateController::actionCreate()|MigrateController::actionCreate()]] action method.
If a route does not contain an action ID, the default action will be executed.

### Options

By overriding the [[yii\console\Controller::options()]] method, you can specify options that are available
to a console command (controller/actionID). The method should return a list of public property names of the controller class.
When running a command, you may specify the value of an option using the syntax `--OptionName=OptionValue`.
This will assign `OptionValue` to the `OptionName` property of the controller class.

If the default value of an option is of array type, then if you set this option while running the command,
the option value will be converted into an array by splitting the input string by commas.

### Arguments

Besides options, a command can also receive arguments. The arguments will be passed as the parameters to the action
method corresponding to the requested sub-command. The first argument corresponds to the first parameter, the second
corresponds to the second, and so on. If there are not enough arguments are provided, the corresponding parameters
may take the declared default values, or if they do not have default value the command will exit with an error.

You may use `array` type hint to indicate that an argument should be treated as an array. The array will be generated
by splitting the input string by commas.

The follow examples show how to declare arguments:

```php
class ExampleController extends \yii\console\Controller
{
    // The command "yii example/create test" will call "actionCreate('test')"
    public function actionCreate($name) { ... }

    // The command "yii example/index city" will call "actionIndex('city', 'name')"
    // The command "yii example/index city id" will call "actionIndex('city', 'id')"
    public function actionIndex($category, $order = 'name') { ... }

    // The command "yii example/add test" will call "actionAdd(['test'])"
    // The command "yii example/add test1,test2" will call "actionAdd(['test1', 'test2'])"
    public function actionAdd(array $name) { ... }
}
```


### Exit Code

Using exit codes is the best practice of console application development. If a command returns `0` it means
everything is OK. If it is a number greater than zero, we have an error and the number returned is the error
code that may be interpreted to find out details about the error.
For example `1` could stand generally for an unknown error and all codes above are declared for specific cases
such as input errors, missing files, and so forth.

To have your console command return with an exit code you simply return an integer in the controller action
method:

```php
public function actionIndex()
{
    if (/* some problem */) {
        echo "A problem occured!\n";
        return 1;
    }
    // do something
    return 0;
}
```
