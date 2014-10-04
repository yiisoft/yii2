Configuración
==============

Las configuraciones se utilizan ampliamente en Yii al crear nuevos objetos o inicializar los objetos existentes. Las configuraciones por lo general incluyen el nombre de la clase del objeto que se está creando, y una lista de los valores iniciales que debería ser asignada al del objeto [propiedades](concept-properties.md). Las configuraciones también pueden incluir una lista de manipuladores que deban imponerse a del objeto [eventos](concept-events.md) y/o una lista de [conductas](concept-behaviors.md) que también ha de atribuirse al objeto.

A continuación, una configuración que se utiliza para crear e inicializar una conexión de base de datos:

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

El [[Yii::createObject()]] método toma una matriz de configuración como su argumento, y crea un objeto creando instanciando la clase llamada en la configuración. Cuando se crea una instancia del objeto, el resto de la configuración se utilizará para inicializar las propiedades del objeto, controladores de eventos y comportamientos.


Si usted ya tiene un objeto, puede usar [[Yii::configure()]] para inicializar las propiedades del objeto con una matriz de configuración:

```php
Yii::configure($object, $config);
```

Tenga en cuenta que, en este caso, la matriz de configuración no debe contener un elemento `class`.

## Formato de Configuración <a name="configuration-format"></a>

El formato de una configuración se puede describir formalmente como:

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    'on eventName' => $eventHandler,
    'as behaviorName' => $behaviorConfig,
]
```

donde

* El elemento `class` especifica un nombre de clase completo para el objeto que se está creando.
* Los elementos `propertyName` especifica los valores iniciales de la propiedad con nombre. Las claves son los nombres de las propiedades y los valores son los valores iniciales correspondientes. Sólo las variables miembro públicas y [propiedades](concept-properties.md) definido por getters/setters se pueden configurar.
* Los elementos `on eventName` especifican qué manejadores deberán adjuntarse al del objeto [eventos](concept-events.md). Observe que las claves de matriz se forman prefijando nombres de eventos con `on`. Por favor, consulte el [Eventos] sección(concept-events.md) para los formatos de controlador de eventos compatibles.
* Los elementos `as behaviorName` especifican qué [conductas](concept-behaviors.md) deben adjuntarse al objeto. Observe que las claves de matriz se forman prefijando nombres de comportamiento con `as`; el valor, `$ behaviorConfig`, representa la configuración para la creación de un comportamiento, como una configuración normal se describe aquí.

A continuación se muestra un ejemplo de una configuración con los valores de propiedad iniciales, controladores de eventos y comportamientos:

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


## Usando Configuraciones <a name="using-configurations"></a>

Las configuraciones se utilizan en muchos lugares en Yii. Al comienzo de esta sección, hemos demostrado cómo crear un objeto según una configuración mediante el uso de [[Yii::CreateObject()]]. En este apartado, vamos a describir configuraciones de aplicaciones y configuraciones widget - dos principales usos de configuraciones.


### Application Configurations <a name="application-configurations"></a>

Configuration for an [application](structure-applications.md) is probably one of the most complex configurations.
This is because the [[yii\web\Application|application]] class has a lot of configurable properties and events.
More importantly, its [[yii\web\Application::components|components]] property can receive an array of configurations
for creating components that are registered through the application. The following is an abstract from the application
configuration file for the [basic application template](start-basic.md).

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

For more details about configuring the `components` property of an application can be found
in the [Applications](structure-applications.md) section and the [Service Locator](concept-service-locator.md) section.


### Widget Configurations <a name="widget-configurations"></a>

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

The above code creates a `Menu` widget and initializes its `activeItems` property to be false.
The `items` property is also configured with menu items to be displayed.

Note that because the class name is already given, the configuration array should NOT have the `class` key.


## Configuration Files <a name="configuration-files"></a>

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


## Default Configurations <a name="default-configurations"></a>

The [[Yii::createObject()]] method is implemented based on a [dependency injection container](concept-di-container.md).
It allows you to specify a set of the so-called *default configurations* which will be applied to ANY instances of
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


## Environment Constants <a name="environment-constants"></a>

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
