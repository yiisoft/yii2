Using Yii as Micro-framework
=======================================
Yii can easily be used without "whistles and bells" that comes with templates. In other words, Yii already is a micro-framework already in framework overhead and minimal code to write. You don't need all the folder structure provided by templates to work with Yii.

This is handy when someone does not need all the good things that comes with templates like assets, or views and its associated stuffs as in the case of JSON APIs web applications. This section will show you how to that.


Create Project
----------------------------------------
Create the folder that your project files are going to reside. In this case we will call it `micro-app`. So create it and change working directory to that path. Commands used here are Unix-based but equivalent commands exist in Windows.
```bash
    #create project directory
    mkdir micro-app
    cd micro-app
```

Now that we are in project directory, we can start working on it. First thing is to modify the `composer.json` file. If you don't know yet how to install composer or using it, please take time to read [Composer Guide](https://getcomposer.org/doc/00-intro.md). Assuming you know how to use composer, create file `composer.json` under `micro-app` using your favorite editor and add the following:

```json
{
    "require": {
        "yiisoft/yii2": "~2.0.0"
    }
}
```
After saving the file, go back to the your favorite command shell and run command `composer install`. This will install the framework with all its dependencies. Alternatively you can skip the manual creation of the `composer.json` file and just run the command `composer require yiisoft/yii2`. This will create the `composer.json` file as well as install the dependencies.

Create Project Structure
----------------------------------------
After you have installed the framework, its time to bootstrap it and create basic structure of your project. The bootstrap file can be anywhere you wish but for simplicity, we will put at the root of directory. Create `index.php` inside `micro-app` which will act as bootstrap. inside it put the following code
```php 
<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

// you may put this into a separate file
$config = [
    'id' => 'micro-app',
    'basePath' => __DIR__,
    'controllerNamespace' => 'app\resources', 
];

(new yii\web\Application($config))->run();
```

>Note that configurations can be in different file or even directory. 

Your project is ready for coding. Although its up to you to decide the project directory structure,as long as you observe namespaces, I suggest you follow closely to what is in Yii templates as pertaining to MVC.

Create Necessary Directories
------------
Now to demonstrate the use of our "micro framework" we will create simple Rest API for Posts. Create directories `models` and `resources` respectively. I have used `resources` instead of `controllers` to just demonstrate that you are not tied to exact naming used in templates (in case you thought you were). 

>Note that you can easily set what is your [Controllers Namespace](http://www.yiiframework.com/doc-2.0/yii-base-application.html#$controllerNamespace-detail) and much more by setting them in [application configuration](http://www.yiiframework.com/doc-2.0/yii-web-application.html).

Create file `Post.php` in `models` directory. This is the code for the model.
```php
namespace app\models;

use yii\db\ActiveRecord;

class Post extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{post}}';
    }
}
```
Also add `ApiController` in `resources`.

```php
namespace app\resources;

use yii\rest\ActiveController;

class ApiController extends ActiveController
{
    public $modelClass = 'app\models\Post';
}
```

so the project structure should look like this
```
micro-app/
├── composer.json
├── index.php
├── models
    └── Post.php
└── resources
    └── ApiController.php
```

Now you can use the application on REST api calls just like you would for any app made with Yii.
