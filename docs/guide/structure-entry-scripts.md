Entry Scripts
=============

Entry scripts are the first chain in the application bootstrapping process. An application (either
Web application or console application) has a single entry script. End users make requests to
an application by accessing its entry script.

Entry scripts for Web applications must be stored under Web accessible directories so that they
can be accessed by end users. They are often named as `index.php`, but can also be any other names,
provided Web servers can locate them. For example, the URL `http://hostname/index.php` will execute
the entry script `index.php`.

Entry scripts for console applications are usually stored under the [base path](structure-applications.md)
of applications. They are often named as `yii` (with the `.php` suffix) and should be made executable
so that users can run console applications with command `./yii <route> [arguments] [options]`.

Entry scripts mainly do the following work:

* Define global constants;
* Register [Composer autoloader](http://getcomposer.org/doc/01-basic-usage.md#autoloading);
* Include the [[Yii]] class file;
* Load application configuration;
* Create and configure an [application](structure-applications.md) instance;
* Call [[yii\base\Application::run()]] to process the incoming request.

The following is the code in the entry script for the [Basic Web Application Template](start-installation.md).

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// register Composer autoloader
require(__DIR__ . '/../vendor/autoload.php');

// include Yii class file
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// load application configuration
$config = require(__DIR__ . '/../config/web.php');

// create, configure and run application
(new yii\web\Application($config))->run();
```


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


The entry script is the bootstrap PHP script that handles user requests
initially. It is the only PHP script that end users can directly request to
execute.

In most cases, the entry script of a Yii application contains code that
is as simple as this:

~~~
[php]
// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// include Yii bootstrap file
require_once('path/to/yii/framework/yii.php');
// create application instance and run
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

The script first includes the Yii framework bootstrap file `yii.php`. It
then creates a Web application instance with the specified configuration
and runs it.

Debug Mode
----------

A Yii application can run in either debug or production mode, as determined by
the value of the constant `YII_DEBUG`. By default, this constant value is defined
as `false`, meaning production mode. To run in debug mode, define this
constant as `true` before including the `yii.php` file. Running the application
in debug mode is less efficient because it keeps many internal logs. On the
other hand, debug mode is also more helpful during the development stage
because it provides richer debugging information when an error occurs.
