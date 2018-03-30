# 使用 Yii 作为微框架

Yii 可以轻松使用，而不需要基本和高级模板中包含的功能。换句话说，Yii 已经是一个微框架。不需要由模板提供的目录结构与 Yii 一起工作。

当你不需要像 assets 或视图一样的所有预定义模板代码时，这一点特别方便。 其中一种情况是构建 JSON API。 在下面的部分将展示如何做到这一点。

## 安装 Yii

为您的项目创建一个目录并将工作目录更改为该路径。示例中使用的命令是基于 Unix 的，但在 Windows 中也存在类似的命令。

```bash
mkdir micro-app
cd micro-app
```

> Note: A little bit of Composer knowledge is required to continue. If you don't know how to use composer yet, please take time to read [Composer Guide](https://getcomposer.org/doc/00-intro.md).

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

> Info: Even though the configuration could be kept in the `index.php` file it is recommended
> to have it separately. This way it can be used for console application also as it is shown below.

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

If you have not set up the web server yet, you may want to take a look at [web server configuration file examples](start-installation.md#configuring-web-servers).
Another options is to use the `yii serve` command which will use the PHP build-in web server. You can run
it from the `micro-app/` directory via:

    vendor/bin/yii serve --docroot=./web

Opening the application URL in a browser should now print "Hello World!" which has been returned in the `SiteController::actionIndex()`.

> Info: In our example, we have changed default application namespace `app` to `micro` to demonstrate
> that you are not tied to that name (in case you thought you were), then adjusted
> [[yii\base\Application::$controllerNamespace|controllers namespace]] and set the correct alias.


## 创建一个 REST API

为了演示我们的“微框架”的用法，我们将为 posts 创建一个简单的 REST API。

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

> Info: We use an sqlite database here for simplicity. Please refer to the [Database guide](db-dao.md) for more options.

Next we create a [database migration](db-migrations.md) to create a post table.
Make sure you have a separate configuration file as explained above, we need it to run the console commands below.
Running the following commands will
create a database migration file and apply the migration to the database:

    vendor/bin/yii migrate/create --appconfig=config.php create_post_table --fields="title:string,body:text"
    vendor/bin/yii migrate/up --appconfig=config.php

Create directory `models` and a `Post.php` file in that directory. This is the code for the model:

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

> Info: The model created here is an ActiveRecord class, which represents the data from the `post` table.
> Please refer to the [active record guide](db-active-record.md) for more information.

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

- `/index.php?r=post` - list all posts
- `/index.php?r=post/view&id=1` - show post with ID 1
- `/index.php?r=post/create` - create a post
- `/index.php?r=post/update&id=1` - update post with ID 1
- `/index.php?r=post/delete&id=1` - delete post with ID 1

从这里开始，您可能需要查看以下指南以进一步开发您的应用程序：

- The API currently only understands urlencoded form data as input, to make it a real JSON API, you
  need to configure [[yii\web\JsonParser]].
- 为了使 URL 更加友好，您需要配置路由。
  请参阅 [关于REST路由的指南](rest-routing.md) 了解如何执行此操作。
- 请参阅 [预览](start-looking-ahead.md) 部分获取更多参考。
