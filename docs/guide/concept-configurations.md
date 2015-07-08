Configurations
==============

Configurations are widely used in Yii when creating new objects or initializing existing objects.
Configurations usually include the class name of the object being created, and a list of initial values
that should be assigned to the object's [properties](concept-properties.md). Configurations may also include a list of
handlers that should be attached to the object's [events](concept-events.md) and/or a list of
[behaviors](concept-behaviors.md) that should also be attached to the object.

In the following, a configuration is used to create and initialize a database connection:

```php
$config = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];

$db = Yii::createObject($config);
```

The [[Yii::createObject()]] method takes a configuration array as its argument, and creates an object by instantiating
the class named in the configuration. When the object is instantiated, the rest of the configuration
will be used to initialize the object's properties, event handlers, and behaviors.

If you already have an object, you may use [[Yii::configure()]] to initialize the object's properties with
a configuration array:

```php
Yii::configure($object, $config);
```

Note that, in this case, the configuration array should not contain a `class` element.


## Configuration Format <span id="configuration-format"></span>

The format of a configuration can be formally described as:

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    'on eventName' => $eventHandler,
    'as behaviorName' => $behaviorConfig,
]
```

where

* The `class` element specifies a fully qualified class name for the object being created.
* The `propertyName` elements specify the initial values for the named property. The keys are the property names, and the
  values are the corresponding initial values. Only public member variables and [properties](concept-properties.md)
  defined by getters/setters can be configured.
* The `on eventName` elements specify what handlers should be attached to the object's [events](concept-events.md).
  Notice that the array keys are formed by prefixing event names with `on `. Please refer to
  the [Events](concept-events.md) section for supported event handler formats.
* The `as behaviorName` elements specify what [behaviors](concept-behaviors.md) should be attached to the object.
  Notice that the array keys are formed by prefixing behavior names with `as `; the value, `$behaviorConfig`, represents
  the configuration for creating a behavior, like a normal configuration  described here.

Below is an example showing a configuration with initial property values, event handlers, and behaviors:

```php
[
    'class' => 'app\components\SearchEngine',
    'apiKey' => 'xxxxxxxx',
    'on search' => function ($event) {
        Yii::info("Keyword searched: " . $event->keyword);
    },
    'as indexer' => [
        'class' => 'app\components\IndexerBehavior',
        // ... property init values ...
    ],
]
```


## Using Configurations <span id="using-configurations"></span>

Configurations are used in many places in Yii. At the beginning of this section, we have shown how to 
create an object according to a configuration by using [[Yii::createObject()]]. In this subsection, we will
describe application configurations and widget configurations - two major usages of configurations.


### Application Configurations <span id="application-configurations"></span>

The configuration for an [application](structure-applications.md) is probably one of the most complex arrays in Yii.
This is because the [[yii\web\Application|application]] class has a lot of configurable properties and events.
More importantly, its [[yii\web\Application::components|components]] property can receive an array of configurations
for creating components that are registered through the application. The following is an abstract from the application
configuration file for the [Basic Project Template](start-installation.md).

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
        'log' => [
            'class' => 'yii\log\Dispatcher',
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=stay2',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];
```

The configuration does not have a `class` key. This is because it is used as follows in
an [entry script](structure-entry-scripts.md), where the class name is already given,

```php
(new yii\web\Application($config))->run();
```

More details about configuring the `components` property of an application can be found
in the [Applications](structure-applications.md) section and the [Service Locator](concept-service-locator.md) section.


### Widget Configurations <span id="widget-configurations"></span>

When using [widgets](structure-widgets.md), you often need to use configurations to customize the widget properties.
Both of the [[yii\base\Widget::widget()]] and [[yii\base\Widget::begin()]] methods can be used to create
a widget. They take a configuration array, like the following,

```php
use yii\widgets\Menu;

echo Menu::widget([
    'activateItems' => false,
    'items' => [
        ['label' => 'Home', 'url' => ['site/index']],
        ['label' => 'Products', 'url' => ['product/index']],
        ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
    ],
]);
```

The above code creates a `Menu` widget and initializes its `activateItems` property to be false.
The `items` property is also configured with menu items to be displayed.

Note that because the class name is already given, the configuration array should NOT have the `class` key.


## Configuration Files <span id="configuration-files"></span>

When a configuration is very complex, a common practice is to store it in one or multiple PHP files, known as
*configuration files*. A configuration file returns a PHP array representing the configuration.
For example, you may keep an application configuration in a file named `web.php`, like the following,

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'components' => require(__DIR__ . '/components.php'),
];
```

Because the `components` configuration is complex too, you store it in a separate file called `components.php`
and "require" this file in `web.php` as shown above. The content of `components.php` is as follows,

```php
return [
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
    ],
    'log' => [
        'class' => 'yii\log\Dispatcher',
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
            ],
        ],
    ],
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=stay2',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ],
];
```

To get a configuration stored in a configuration file, simply "require" it, like the following:

```php
$config = require('path/to/web.php');
(new yii\web\Application($config))->run();
```


## Default Configurations <span id="default-configurations"></span>

The [[Yii::createObject()]] method is implemented based on a [dependency injection container](concept-di-container.md).
It allows you to specify a set of the so-called *default configurations* which will be applied to ALL instances of
the specified classes when they are being created using [[Yii::createObject()]]. The default configurations
can be specified by calling `Yii::$container->set()` in the [bootstrapping](runtime-bootstrapping.md) code.

For example, if you want to customize [[yii\widgets\LinkPager]] so that ALL link pagers will show at most 5 page buttons
(the default value is 10), you may use the following code to achieve this goal,

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```

Without using default configurations, you would have to configure `maxButtonCount` in every place where you use
link pagers.


## Environment Constants <span id="environment-constants"></span>

Configurations often vary according to the environment in which an application runs. For example,
in development environment, you may want to use a database named `mydb_dev`, while on production server
you may want to use the `mydb_prod` database. To facilitate switching environments, Yii provides a constant
named `YII_ENV` that you may define in the [entry script](structure-entry-scripts.md) of your application.
For example,

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

You may define `YII_ENV` as one of the following values:

- `prod`: production environment. The constant `YII_ENV_PROD` will evaluate as true.
  This is the default value of `YII_ENV` if you do not define it.
- `dev`: development environment. The constant `YII_ENV_DEV` will evaluate as true.
- `test`: testing environment. The constant `YII_ENV_TEST` will evaluate as true.

With these environment constants, you may specify your configurations conditionally based on
the current environment. For example, your application configuration may contain the following
code to enable the [debug toolbar and debugger](tool-debugger.md) in development environment.

```php
$config = [...];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
