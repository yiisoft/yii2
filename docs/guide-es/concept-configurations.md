Configuración
==============

Las configuraciones se utilizan ampliamente en Yii al crear nuevos objetos o inicializar los objetos existentes. Las configuraciones por lo general incluyen el nombre de la clase del objeto que se está creando, y una lista de los valores iniciales que deberían ser asignadas a las del [propiedades](concept-properties.md) objeto. Las configuraciones también pueden incluir una lista de manipuladores que deban imponerse a del objeto [eventos](concept-events.md) y/o una lista de [comportamientos](concept-behaviors.md) que también ha de atribuirse al objeto.

A continuación, una configuración que se utiliza para crear e inicializar una conexión a base de datos:

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
El método [[Yii::createObject()]] toma una matriz de configuración como su argumento, y crea un objeto intanciando la clase llamada en la configuración. Cuando se crea una instancia del objeto, el resto de la configuración se utilizará para inicializar las propiedades del objeto, controladores de eventos y comportamientos.

Si usted ya tiene un objeto, puede usar [[Yii::configure()]] para inicializar las propiedades del objeto con una matriz de configuración:

```php
Yii::configure($object, $config);
```

Tenga en cuenta que en este caso, la matriz de configuración no debe contener un elemento `class`.

## Formato de Configuración <span id="configuration-format"></span>

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
* Los elementos `propertyName` especifica los valores iniciales de la propiedad con nombre. Las claves son los nombres de las propiedades y los valores son los valores iniciales correspondientes. Sólo los miembros de variables públicas y [propiedades](concept-properties.md) definidas por getters/setters se pueden configurar.
* Los elementos `on eventName` especifican qué manipuladores deberán adjuntarse al del objeto [eventos](concept-events.md). Observe que las claves de matriz se forman prefijando nombres de eventos con `on`. Por favor, consulte la sección [Eventos](concept-events.md) para los formatos de controlador de eventos compatibles.
* Los elementos `as behaviorName` especifican qué [comportamientos](concept-behaviors.md) deben adjuntarse al objeto. Observe que las claves de matriz se forman prefijando nombres de comportamiento con `as`; el valor, `$behaviorConfig`, representa la configuración para la creación de un comportamiento, como una configuración normal descrita aquí.

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


## Usando Configuraciones <span id="using-configurations"></span>

Las configuraciones se utilizan en muchos lugares en Yii. Al comienzo de esta sección, hemos demostrado cómo crear un objeto según una configuración mediante el uso de [[Yii::createObject()]]. En este apartado, vamos a describir configuraciones de aplicaciones y configuraciones widget - dos principales usos de configuraciones.


### Configuraciones de aplicación <span id="application-configurations"></span>

Configuración para una [aplicación](structure-applications.md) es probablemente una de las configuraciones más complejas. Esto se debe a que la clase [[yii\web\Application|aplicación]] tiene un montón de propiedades y eventos configurables. Más importante aún, su propiedad [[yii\web\Application::components|componentes]] que puede recibir una gran variedad de configuraciones para crear componentes que se registran a través de la aplicación. Lo siguiente es un resumen del archivo de configuración de la aplicación para la [plantilla básica de la aplicación](start-installation.md).

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
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

La configuración no tiene una clave `class`. Esto es porque se utiliza como sigue en un [script de entrada](structure-entry-scripts.md), donde ya se le da el nombre de la clase,

```php
(new yii\web\Application($config))->run();
```

Para más detalles sobre la configuración de la propiedad `components` de una aplicación se puede encontrar en la sección [Aplicación](structure-applications.md) y la sección [Localizador de Servicio](concept-service-locator.md).


### Configuración Widget <span id="widget-configurations"></span>

Cuando se utiliza [widgets](structure-widgets.md), a menudo es necesario utilizar las configuraciones para personalizar las propiedades de widgets. Tanto los metodos [[yii\base\Widget::widget()]] y [[yii\base\Widget::begin()]] pueden usarse para crear un widget. Toman un arreglo de configuración, como el siguiente,

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

El código anterior crea un widget `Menu` e inicializa su propiedad `activeItems` en falsa. La propiedad `items` también se configura con elementos de menú que se muestran.

Tenga en cuenta que debido a que el nombre de la clase ya está dado, la matriz de configuración no deben tener la clave `class`.


## Archivos de Configuración <span id="configuration-files"></span>

Cuando una configuración es muy compleja, una práctica común es almacenarla en uno o múltiples archivos PHP, conocidos como *archivos de configuración*. Un archivo de configuración devuelve un array de PHP que representa la configuración. Por ejemplo, es posible mantener una configuración de la aplicación en un archivo llamado `web.php`, como el siguiente,

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'components' => require __DIR__ . '/components.php',
];
```

Debido a que la configuración `componentes` es compleja también, se guarda en un archivo separado llamado `components.php` y "requerir" este archivo en `web.php` como se muestra arriba. El contenido de `components.php` es el siguiente,

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

Para obtener una configuración almacenada en un archivo de configuración, simplemente "requerir" este, como el siguiente:

```php
$config = require 'path/to/web.php';
(new yii\web\Application($config))->run();
```


## Configuraciones por Defecto <span id="default-configurations"></span>

El método [[Yii::createObject()]] es implementado en base a [contenedor de inyección de dependencia](concept-di-container.md). Le permite especificar un conjunto de los llamados *configuraciones predeterminadas* que se aplicarán a todos los casos de las clases especificadas cuando se crean utilizando [[Yii::createObject()]]. Las configuraciones por defecto se puede especificar llamando `Yii::$container->set()` en el código [bootstrapping](runtime-bootstrapping.md).

Por ejemplo, si desea personalizar [[yii\widgets\LinkPager]] para que TODO enlace de búsqueda muestre como máximo 5 botones de página (el valor por defecto es 10), puede utilizar el siguiente código para lograr este objetivo,

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```

Sin utilizar las configuraciones predeterminadas, usted tendría que configurar `maxButtonCount` en cada lugar en el que utiliza enlace paginador.

## Constantes de Entorno <span id="environment-constants"></span>

Las configuraciones a menudo varían de acuerdo al entorno en que se ejecuta una aplicación. Por ejemplo, en el entorno de desarrollo, es posible que desee utilizar una base de datos llamada `mydb_dev`, mientras que en servidor de producción es posible que desee utilizar la base de datos `mydb_prod`. Para facilitar la conmutación de entornos, Yii proporciona una constante llamado `YII_ENV` que se puede definir en el [script de entrada](structure-entry-scripts.md) de su aplicación. Por ejemplo,

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

Usted puede definir `YII_ENV` como uno de los valores siguientes:

- `prod`: entorno de producción. La constante `YII_ENV_PROD` evaluará como verdadero. 
Este es el valor por defecto de `YII_ENV` si no esta definida.
- `dev`: entorno de desarrollo. La constante `YII_ENV_DEV` evaluará como verdadero.
- `test`: entorno de pruebas. La constante `YII_ENV_TEST` evaluará como verdadero.

Con estas constantes de entorno, puede especificar sus configuraciones condicionales basado en el entorno actual. Por ejemplo, la configuración de la aplicación puede contener el siguiente código para permitir que el [depurador y barra de herramientas de depuración](tool-debugger.md) en el entorno de desarrollo.

```php
$config = [...];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
