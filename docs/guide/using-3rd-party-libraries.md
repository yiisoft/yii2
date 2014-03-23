Using 3rd-Party Libraries
=========================

Yii is carefully designed so that third-party libraries can be
easily integrated to further extend Yii's functionalities.

TODO: namespaces and composer explanations

Using Yii in 3rd-Party Systems
------------------------------

Yii can also be used as a self-contained library to support developing and enhancing
existing 3rd-party systems, such as WordPress, Joomla, etc. To do so, include
the following code in the bootstrap code of the 3rd-party system:

```php
$yiiConfig = require(__DIR__ . '/../config/yii/web.php');
new yii\web\Application($yiiConfig); // No 'run()' invocation!
```

The above code is very similar to the bootstrap code used by a typical Yii application
except one thing: it does not call the `run()` method after creating the Web application
instance.

Now we can use most features offered by Yii when developing 3rd-party enhancements. For example,
we can use `Yii::$app` to access the application instance; we can use the database features
such as ActiveRecord; we can use the model and validation feature; and so on.

Using Yii2 with Yii1
--------------------

Yii2 can be used along with Yii1 at the same project.
Since Yii2 uses namespaced class names they will not conflict with any class from Yii1.
However there is single class, which name is used both in Yii1 and Yii2, it named 'Yii'.
In order to use both Yii1 and Yii2 you need to resolve this collision.
To do so you need to define your own 'Yii' class, which will combine content of 'Yii' from 1.x
and 'Yii' from 2.x.

When using composer you add the following to your composer.json in order to add both versions of yii to your project:

```json
"require": {
    "yiisoft/yii": "*",
    "yiisoft/yii2": "*",
},
```

Start from defining your own descendant of [[yii\BaseYii]]:

```php
$yii2path = '/path/to/yii2';
require($yii2path . '/BaseYii.php');

class Yii extends \yii\BaseYii
{
}

Yii::$classMap = include($yii2path . '/classes.php');
```

Now we have a class, which suites Yii2, but causes fatal errors for Yii1.
So, first of all, we need to include `YiiBase` of Yii1 source code to our 'Yii' class
definition file:

```php
$yii2path = '/path/to/yii2';
require($yii2path . '/BaseYii.php'); // Yii 2.x
$yii1path = '/path/to/yii1';
require($yii1path . '/YiiBase.php'); // Yii 1.x

class Yii extends \yii\BaseYii
{
}

Yii::$classMap = include($yii2path . '/classes.php');
```

Using this, defines all necessary constants and autoloader of Yii1.
Now we need to add all fields and methods from `YiiBase` of Yii1 to our 'Yii' class.
Unfortunally, there is no way to do so but copy-paste:

```php
$yii2path = '/path/to/yii2';
require($yii2path . '/BaseYii.php');
$yii1path = '/path/to/yii1';
require($yii1path . '/YiiBase.php');

class Yii extends \yii\BaseYii
{
    public static $classMap = [];
    public static $enableIncludePath = true;
    private static $_aliases = ['system'=>YII_PATH,'zii'=>YII_ZII_PATH];
    private static $_imports = [];
    private static $_includePaths;
    private static $_app;
    private static $_logger;

    public static function getVersion()
    {
        return '1.1.15-dev';
    }

    public static function createWebApplication($config=null)
    {
        return self::createApplication('CWebApplication',$config);
    }

    public static function app()
    {
        return self::$_app;
    }

    // Rest of \YiiBase internal code placed here
    ...
}

Yii::$classMap = include($yii2path . '/classes.php');
Yii::registerAutoloader(['Yii', 'autoload']); // Register Yii2 autoloader via Yii1
```

Note: while copying methods you should NOT copy method "autoload()"!
Also you may avoid copying "log()", "trace()", "beginProfile()", "endProfile()"
in case you want to use Yii2 logging instead of Yii1 one.

Now we have 'Yii' class, which suites both Yii 1.x and Yii 2.x.
So bootstrap code used by your application will looks like following:

```php
require(__DIR__ . '/../components/my/Yii.php'); // include created 'Yii' class

$yii2Config = require(__DIR__ . '/../config/yii2/web.php');
new yii\web\Application($yii2Config); // create Yii 2.x application

$yii1Config = require(__DIR__ . '/../config/yii1/main.php');
Yii::createWebApplication($yii1Config)->run(); // create Yii 1.x application
```

Then in any part of your program ```Yii::$app``` refers to Yii 2.x application,
while ```Yii::app()``` refers to Yii 1.x application:

```php
echo get_class(Yii::app()); // outputs 'CWebApplication'
echo get_class(Yii::$app); // outputs 'yii\web\Application'
```
