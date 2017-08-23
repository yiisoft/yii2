# Using Yii as a Micro-framework

Yii can be easily used without features included in basic and advanced templates. In other words, Yii is already a micro-framework. It is not required to have the directory structure provided by templates to work with Yii.

This is especially handy when you do not need all the pre-defined template code like assets or views. One of such cases is building a JSON API. In the following sections will show how to do that.

## Create Project

Create a directory for your project files and change working directory to that path. Commands used in examples are Unix-based but similar commands exist in Windows.

```bash
mkdir micro-app
cd micro-app
```

> Note: Composer knowledge is required to continue. If you don't know how to use composer yet, please take time to read [Composer Guide](https://getcomposer.org/doc/00-intro.md).

Create file `composer.json` under the `micro-app` directory using your favorite editor and add the following:

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

Save the file and run the `composer install` command. This will install the framework with all its dependencies. Alternatively you can skip the manual creation of the `composer.json` file and just run the command `composer require yiisoft/yii2`. This will create the `composer.json` file as well as install the dependencies.

## Create Project Structure

After you have installed the framework, it's time to create an entry point for the application. Entry point is the very first file that will be executed when you try to open your application. For the security reasons, it is recommended to put the entrypoint file in a separate directory and make it a web root. 

Create a `web` directory and put `index.php` inside with the following content:

```php 
<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '../vendor/autoload.php');
require(__DIR__ . '../vendor/yiisoft/yii2/Yii.php');

// you may put this into a separate file
$config = [
    'id' => 'micro-app',
    'basePath' => __DIR__,
    'controllerNamespace' => 'micro\controllers', 
    'aliases' => [
        '@micro' => dirname(__DIR__),
    ]
];

(new yii\web\Application($config))->run();
```

> Note: Config can be moved to a separate file located in a separate directory.

Your project is ready for coding. Although it's up to you to decide the project directory structure, as long as you observe namespaces.

## Create Necessary Directories

In order to demonstrate the usage of our "micro framework", we will create simple REST API for posts. 

Create directories `models` and `controllers` respectively. 

> Note: In our example, we have changed default application namespace `app` to a `micro` to demonstrate that you are not tied to that name (in case you thought you were), then adjusted [controllers namespace](http://www.yiiframework.com/doc-2.0/yii-base-application.html#$controllerNamespace-detail) and set the correct alias.

Create `Post.php` file in the `models` directory. This is the code for the model.

```php
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

Create `ApiController` in `controllers`.

```php
namespace micro\resources;

use yii\rest\ActiveController;

class ApiController extends ActiveController
{
    public $modelClass = 'micro\models\Post';
}
```

So the project structure should look like this:

```
micro-app/
├── composer.json
├── web
    └── index.php
├── models
    └── Post.php
└── resources
    └── ApiController.php
```

Now you can use the application on REST API calls just like you would for any application made with Yii.
