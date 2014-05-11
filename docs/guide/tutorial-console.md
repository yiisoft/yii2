Console applications
====================

> Note: This section is under development.

Yii has full featured support for console applications, whose structure is very similar to a Yii web application. A console application
consists of one or more [[yii\console\Controller]] classes, which are often referred to as "commands" in the console environment. Each controller can also have one or more actions, just like web controllers.

Usage
-----

You execute a console controller action using the following syntax:

```
yii <route> [--option1=value1 --option2=value2 ... argument1 argument2 ...]
```

For example, the [[yii\console\controllers\MigrateController::actionCreate()|MigrateController::actionCreate()]]
with [[yii\console\controllers\MigrateController::$migrationTable|MigrateController::$migrationTable]] set can
be called from command line like so:

```
yii migrate/create --migrationTable=my_migration
```

In the above `yii` is the console application entry script described below.

Entry script
------------

The console application entry script is equivalent to the `index.php` bootstrap file used for the web application. The console entry script is typically called `yii`, and located in your application's root directory. The contents of the console application entry script contains
code like the following:

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
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

This script will be created as part of your application; you're free to edit it to suit your needs. The `YII_DEBUG` constant can be set `false` if you do
not want to see a stack trace on error, and/or if you want to improve the overall performance. In both basic and advanced application
templates, the console application entry script has debugging enabled to provide a more developer-friendly environment.

Configuration
-------------

As can be seen in the code above, the console application uses its own configuration file, named `console.php`. In this file
you should configure various application components and properties for the console application in particular.

If your web application and the console application share a lot of configuration parameters and values, you may consider moving the common
parts into a separate file, and including this file in both of the application configurations (web and console). You can see an example of this in the "advanced" application template.

Sometimes, you may want to run a console command using an application configuration that is different from the one
specified in the entry script. For example, you may want to use the `yii migrate` command to upgrade your
test databases, which are configured in each individual test suite. To do change the configuration dynamically, simply specify a custom application configuration
file via the `appconfig` option when executing the command:

```
yii <route> --appconfig=path/to/config.php ...
```


Creating your own console commands
----------------------------------

### Console Controller and Action

A console command is defined as a controller class extending from [[yii\console\Controller]]. In the controller class,
you define one or more actions that correspond to sub-commands of the controller. Within each action, you write code that implements the appropriate tasks for that particular sub-command.

When running a command, you need to specify the route to the  controller action. For example,
the route `migrate/create` invokes the sub-command that corresponds to the
[[yii\console\controllers\MigrateController::actionCreate()|MigrateController::actionCreate()]] action method.
If a route offered during execution does not contain an action ID, the default action will be executed (as with a web controller).

### Options

By overriding the [[yii\console\Controller::options()]] method, you can specify options that are available
to a console command (controller/actionID). The method should return a list of the controller class's public properties.
When running a command, you may specify the value of an option using the syntax `--OptionName=OptionValue`.
This will assign `OptionValue` to the `OptionName` property of the controller class.

If the default value of an option is of an array type and you set this option while running the command,
the option value will be converted into an array by splitting the input string on any commas.

### Arguments

Besides options, a command can also receive arguments. The arguments will be passed as the parameters to the action
method corresponding to the requested sub-command. The first argument corresponds to the first parameter, the second
corresponds to the second, and so on. If not enough arguments are provided when the command is called, the corresponding parameters
will take the declared default values, if defined. If no default value is set, and no value is provided at runtime, the command will exit with an error.

You may use the `array` type hint to indicate that an argument should be treated as an array. The array will be generated
by splitting the input string on commas.

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

Using exit codes is a best practice for console application development. Conventionally, a command returns `0` to indicate that
everything is OK. If the command returns a number greater than zero, that's considered to be indicative of an error. The number returned will be the error
code, potentially usable to find out details about the error.
For example `1` could stand generally for an unknown error and all codes above would be reserved for specific cases: input errors, missing files, and so forth.

To have your console command return an exit code, simply return an integer in the controller action
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
