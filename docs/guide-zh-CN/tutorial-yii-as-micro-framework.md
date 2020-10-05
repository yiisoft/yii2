# 使用 Yii 作为微框架

Yii 可以轻松使用，而不需要基本和高级模板中包含的功能。换句话说，Yii 已经是一个微框架。不需要由模板提供的目录结构与 Yii 一起工作。

当你不需要像 assets 或视图一样的所有预定义模板代码时，这一点特别方便。 其中一种情况是构建 JSON API。 在下面的部分将展示如何做到这一点。

## 安装 Yii

为您的项目创建一个目录并将工作目录更改为该路径。示例中使用的命令是基于 Unix 的，但在 Windows 中也存在类似的命令。

```bash
mkdir micro-app
cd micro-app
```

> Note: 需要一些 Composer 的知识才能继续。如果您还不知道如何使用 composer，请花些时间阅读 [Composer 指南](https://getcomposer.org/doc/00-intro.md)。

使用您最喜爱的编辑器在 `micro-app` 目录下创建 `composer.json` 文件并添加以下内容：

```json
{
    "require": {
        "yiisoft/yii2": "~2.0.0"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    ]
}
```

保存文件并运行 `composer install` 命令。这将安装框架及其所有依赖项。

## 创建项目结构

安装框架之后，需要为此应用程序创建一个 [入口点](structure-entry-scripts.md)。入口点是您尝试打开应用程序时将执行的第一个文件。 出于安全原因，建议将入口点文件放在一个单独的目录中，并将其设置为Web根目录。

创建一个 `web` 目录并将 `index.php` 放入其中，内容如下：

```php 
<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require __DIR__ . '/../config.php';
(new yii\web\Application($config))->run();
```

还要创建一个名为 `config.php `的文件，它将包含所有的应用程序配置：

```php
<?php
return [
    'id' => 'micro-app',
    // the basePath of the application will be the `micro-app` directory
    'basePath' => __DIR__,
    // this is where the application will find all controllers
    'controllerNamespace' => 'micro\controllers',
    // set an alias to enable autoloading of classes from the 'micro' namespace
    'aliases' => [
        '@micro' => __DIR__,
    ],
];
```

> Info: 尽管配置可以保存在 `index.php` 文件中，建议单独使用它。
> 这样它也可以用于控制台应用程序，如下所示。

您的项目现在已经准备进行编码了。尽管由您决定项目目录结构，只要您遵守命名空间即可。

## 创建第一个控制器

创建一个 `controllers` 目录并添加一个文件 `SiteController.php`，这是默认的
控制器将处理没有路径信息的请求。

```php
<?php

namespace micro\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return 'Hello World!';
    }
}
```

如果您想为此控制器使用不同的名称，则可以配置 [[yii\base\Application::$defaultRoute]] 进行更改。
例如，对于 `DefaultController` 将会是 `'defaultRoute' => 'default/index'`。

在这一点上，项目结构应该如下所示：

```
micro-app/
├── composer.json
├── config.php
├── web/
    └── index.php
└── controllers/
    └── SiteController.php
```

如果您尚未设置 Web 服务器，则可能需要查看[Web服务器配置文件示例](start-installation.md#configuring-web-servers)。
另一种选择是使用 `yii serve` 命令，它将使用 PHP 内置 web 服务器。
您可以通过以下方式从 `micro-app /` 目录运行它：

    vendor/bin/yii serve --docroot=./web

在浏览器中打开应用程序URL现在应该打印出“Hello World！”，它已经在 `SiteController::actionIndex()` 中返回。

> Info: 在我们的示例中，我们已将默认应用程序名称空间 `app` 更改为 `micro`，
> 以表明您不受此名称的限制（如果您是这样认为），
> 然后调整 [[yii\base\Application::$controllerNamespace|controllers namespace]] 并设置正确的别名。


## 创建一个 REST API

为了演示我们的“微框架”的用法，我们将为帖子创建一个简单的 REST API。

为了这个 API 来操作一些数据，我们首先需要一个数据库。 添加数据库连接配置
到应用程序配置：

```php
'components' => [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'sqlite:@micro/database.sqlite',
    ],
],
```

> Info: 为了简单，我们在这里使用了一个 sqlite 数据库 请参阅[数据库指南](db-dao.md) 以获取更多选项。

接下来我们创建一个[数据库迁移](db-migrations.md)来创建一个帖子表。
确保你有一个单独的配置文件，如上所述，我们需要它来运行下面的控制台命令。
运行以下命令将创建数据库迁移文件
并将迁移应用到数据库：

    vendor/bin/yii migrate/create --appconfig=config.php create_post_table --fields="title:string,body:text"
    vendor/bin/yii migrate/up --appconfig=config.php

在该目录中创建目录 `models` 和 `Post.php` 文件。以下是模型的代码：

```php
<?php

namespace micro\models;

use yii\db\ActiveRecord;

class Post extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{post}}';
    }
}
```

> Info: 这里创建的模型是一个 ActiveRecord 类，它代表 `post` 表中的数据。
> 有关更多信息，请参阅[活动记录指南](db-active-record.md)。

我们要通过 API 发布帖子，请在 `controllers` 中添加 `PostController`：

```php
<?php

namespace micro\controllers;

use yii\rest\ActiveController;

class PostController extends ActiveController
{
    public $modelClass = 'micro\models\Post';

    public function behaviors()
    {
        // remove rateLimiter which requires an authenticated user to work
        $behaviors = parent::behaviors();
        unset($behaviors['rateLimiter']);
        return $behaviors;
    }
}
```

此时我们的 API 将提供以下 URL ：

- `/index.php?r=post` - 列出所有帖子
- `/index.php?r=post/view&id=1` - 显示 ID 为 1 的帖子
- `/index.php?r=post/create` - 创建一个帖子
- `/index.php?r=post/update&id=1` - 更新 ID 为 1 的帖子
- `/index.php?r=post/delete&id=1` - 删除 ID 为 1 的帖子

从这里开始，您可能需要查看以下指南以进一步开发您的应用程序：

- 该 API 目前仅将 urlencoded 表单数据理解为输入，若使其成为真正的 JSON API，
  您需要配置 [[yii\web\JsonParser]]。
- 为了使 URL 更加友好，您需要配置路由。
  请参阅 [关于REST路由的指南](rest-routing.md) 了解如何执行此操作。
- 请参阅 [预览](start-looking-ahead.md) 部分获取更多参考。
