# Using Yii as a Micro-framework

Yii can be easily used without the features included in basic and advanced templates. In other words, Yii is already a micro-framework. It is not required to have the directory structure provided by templates to work with Yii.

This is especially handy when you do not need all the pre-defined template code like assets or views. One of such cases is building a JSON API. In the following sections will show how to do that.

## Installing Yii

Create a directory for your project files and change working directory to that path. Commands used in examples are Unix-based but similar commands exist in Windows.

```bash
mkdir micro-app
cd micro-app
```

> Note: A little bit of Composer knowledge is required to continue. If you don't know how to use composer yet, please take time to read [Composer Guide](https://getcomposer.org/doc/00-intro.md).

Create file `composer.json` under the `micro-app` directory using your favorite editor and add the following:

```json
{
    "require": {
        "yiisoft/yii2": "~3.0.0"
    }
}
```

Save the file and run the `composer install` command. This will install the framework with all its dependencies.

## Creating the Project Structure

After you have installed the framework, it's time to create an [entry point](structure-entry-scripts.md) for the application. Entry point is the very first file that will be executed when you try to open your application. For the security reasons, it is recommended to put the entrypoint file in a separate directory and make it a web root.

Create a `web` directory and put `index.php` inside with the following content:

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

Also create a file named `config.php` which will contain all application configuration:

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

Your project is now ready for coding. Although it's up to you to decide the project directory structure, as long as you observe namespaces.

## Creating the first Controller

Create a `controllers` directory and add a file `SiteController.php`, which is the default
controller that will handle a request with no path info.

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

If you want to use a different name for this controller you can change it and configure [[yii\base\Application::$defaultRoute]] accordingly.
For example, for a `DefaultController` that would be `'defaultRoute' => 'default/index'`.

At this point the project structure should look like this:

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


## Creating a REST API

In order to demonstrate the usage of our "micro framework", we will create a simple REST API for posts.

For this API to serve some data, we need a database first. Add the database connection configuration
to the application configuration:

```php
'components' => [
    'db' => [
        '__class' => yii\db\Connection::class,
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

To serve posts on our API, add the `PostController` in `controllers`:

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

At this point our API will provide the following URLs:

- `/index.php?r=post` - list all posts
- `/index.php?r=post/view&id=1` - show post with ID 1
- `/index.php?r=post/create` - create a post
- `/index.php?r=post/update&id=1` - update post with ID 1
- `/index.php?r=post/delete&id=1` - delete post with ID 1

Starting from Here you may want to look at the following guides to further develop your application:

- The API currently only understands urlencoded form data as input, to make it a real JSON API, you
  need to configure [[yii\web\JsonParser]].
- To make the URLs more friendly you need to configure routing.
  See [guide on REST routing](rest-routing.md) on how to do this.
- Please also refer to the [Looking Ahead](start-looking-ahead.md) section for further references.
