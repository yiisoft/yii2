Entry Scripts
=============

Entry scripts are the first step in the application bootstrapping process. An application (either
Web application or console application) has a single entry script. End users make requests to
entry scripts which instantiate application instances and forward the requests to them.

Entry scripts for Web applications must be stored under Web accessible directories so that they
can be accessed by end users. They are often named as `index.php`, but can also use any other names,
provided Web servers can locate them.

Entry scripts for console applications are usually stored under the [base path](structure-applications.md)
of applications and are named as `yii` (with the `.php` suffix). They should be made executable
so that users can run console applications through the command `./yii <route> [arguments] [options]`.

Entry scripts mainly do the following work:

* Define global constants;
* Register [Composer autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading);
* Include the [[Yii]] class file;
* Load application configuration;
* Create and configure an [application](structure-applications.md) instance;
* Call [[yii\base\Application::run()]] to process the incoming request.


## Web Applications <span id="web-applications"></span>

The following is the code in the entry script for the [Basic Web Project Template](start-installation.md).

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// register Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// include Yii class file
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// load application configuration
$config = require __DIR__ . '/../config/web.php';

// create, configure and run application
(new yii\web\Application($config))->run();
```


## Console Applications <span id="console-applications"></span>

Similarly, the following is the code for the entry script of a console application:

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
defined('YII_ENV') or define('YII_ENV', 'dev');

// register Composer autoloader
require __DIR__ . '/vendor/autoload.php';

// include Yii class file
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// load application configuration
$config = require __DIR__ . '/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```


## Defining Constants <span id="defining-constants"></span>

Entry scripts are the best place for defining global constants. Yii supports the following three constants:

* `YII_DEBUG`: specifies whether the application is running in debug mode. When in debug mode, an application
  will keep more log information, and will reveal detailed error call stacks if exceptions are thrown. For this
  reason, debug mode should be used mainly during development. The default value of `YII_DEBUG` is `false`.
* `YII_ENV`: specifies which environment the application is running in. This will be described in
  more detail in the [Configurations](concept-configurations.md#environment-constants) section.
  The default value of `YII_ENV` is `'prod'`, meaning the application is running in production environment.
* `YII_ENABLE_ERROR_HANDLER`: specifies whether to enable the error handler provided by Yii. The default
  value of this constant is `true`.

When defining a constant, we often use the code like the following:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

which is equivalent to the following code:

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

Clearly the former is more succinct and easier to understand.

Constant definitions should be done at the very beginning of an entry script so that they can take effect
when other PHP files are being included.
