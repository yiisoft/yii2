入口脚本
=============

入口脚本是应用启动流程中的第一环，
一个应用（不管是网页应用还是控制台应用）只有一个入口脚本。
终端用户的请求通过入口脚本实例化应用并将请求转发到应用。

Web 应用的入口脚本必须放在终端用户能够访问的目录下，
通常命名为 `index.php`，
也可以使用 Web 服务器能定位到的其他名称。

控制台应用的入口脚本一般在应用根目录下命名为 `yii`（后缀为.php），
该文件需要有执行权限，
这样用户就能通过命令 `./yii <route> [arguments] [options]` 来运行控制台应用。

入口脚本主要完成以下工作：

* 定义全局常量；
* 注册 [Composer 自动加载器](https://getcomposer.org/doc/01-basic-usage.md#autoloading)；
* 包含 [[Yii]] 类文件；
* 加载应用配置；
* 创建一个[应用](structure-applications.md)实例并配置;
* 调用 [[yii\base\Application::run()]] 来处理请求。


## Web 应用 <span id="web-applications"></span>

以下是[基础应用模版](start-installation.md)入口脚本的代码：

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// 注册 Composer 自动加载器
require __DIR__ . '/../vendor/autoload.php';

// 包含 Yii 类文件
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// 加载应用配置
$config = require __DIR__ . '/../config/web.php';

// 创建、配置、运行一个应用
(new yii\web\Application($config))->run();
```


## 控制台应用 <span id="console-applications"></span>

以下是一个控制台应用的入口脚本：

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// 注册 Composer 自动加载器
require __DIR__ . '/vendor/autoload.php';

// 包含 Yii 类文件
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// 加载应用配置
$config = require __DIR__ . '/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```


## 定义常量 <span id="defining-constants"></span>

入口脚本是定义全局常量的最好地方，Yii 支持以下三个常量：

* `YII_DEBUG`：标识应用是否运行在调试模式。当在调试模式下，应用会保留更多日志信息，
  如果抛出异常，会显示详细的错误调用堆栈。
  因此，调试模式主要适合在开发阶段使用，`YII_DEBUG` 默认值为 false。
* `YII_ENV`：标识应用运行的环境，详情请查阅
  [配置](concept-configurations.md#environment-constants)章节。
  `YII_ENV` 默认值为 `'prod'`，表示应用运行在线上产品环境。
* `YII_ENABLE_ERROR_HANDLER`：标识是否启用 Yii 提供的错误处理，
  默认为 true。

当定义一个常量时，通常使用类似如下代码来定义：

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

上面的代码等同于：

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

显然第一段代码更加简洁易懂。

常量定义应该在入口脚本的开头，这样包含其他 PHP 文件时，
常量就能生效。
