Localizador de Servicios
========================

Un localizador de servicios es un objeto que sabe cómo proporcionar todo tipo de servicios (o componentes) que puede necesitar una aplicación. Dentro de un localizador de servicios, existe en cada componente como una única instancia, únicamente identificado por un ID. Se utiliza el ID para recuperar un componente desde el localizador de servicios.

En Yii, un localizador de servicio es simplemente una instancia de [[yii\di\ServiceLocator]], o de una clase hija.

El localizador de servicio más utilizado en Yii es el objeto *aplicación*, que se puede acceder a través de `\Yii::$app`. Los servicios que prestá son llamadas *componentes de la aplicación*, como los componentes `request`, `response`, and `urlManager`. Usted puede configurar estos componentes, o incluso cambiarlos por sus propias implementaciones fácilmente a través de la funcionalidad proporcionada por el localizador de servicios.

Además del objeto de aplicación, cada objeto módulo es también un localizador de servicios.

Para utilizar un localizador de servicios, el primer paso es registrar los componentes de la misma. Un componente se puede registrar a través de [[yii\di\ServiceLocator::set()]]. El código siguiente muestra diferentes maneras de registrarse componentes:

```php
use yii\di\ServiceLocator;
use yii\caching\FileCache;

$locator = new ServiceLocator;

// register "cache" using a class name that can be used to create a component
$locator->set('cache', 'yii\caching\ApcCache');

// register "db" using a configuration array that can be used to create a component
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// register "search" using an anonymous function that builds a component
$locator->set('search', function () {
    return new app\components\SolrService;
});

// register "pageCache" using a component
$locator->set('pageCache', new FileCache);
```

Una vez que el componente se ha registrado, usted puede acceder a él utilizando su ID, en una de las dos formas siguientes:

```php
$cache = $locator->get('cache');
// or alternatively
$cache = $locator->cache;
```

Como puede observarse, [[yii\di\ServiceLocator]] le permite acceder a un componente como una propiedad utilizando el ID de componente. Cuando acceda a un componente, por primera vez, [[yii\di\ServiceLocator]] utilizará la información de registro de componente para crear una nueva instancia del componente y devolverlo. Más tarde, si se accede de nuevo al componente, el localizador de servicio devolverá la misma instancia.

Usted puede utilizar [[yii\di\ServiceLocator::has()]] para comprobar si un ID de componente ya ha sido registrada. 
Si llama [[yii\di\ServiceLocator::get()]] con una identificación válida, se produce una excepción.

Debido a que los localizadores de servicios a menudo se crean con [configuraciones](concept-configurations.md), se proporciona una propiedad que puede escribir el nombre [[yii\di\ServiceLocator::setComponents()|components]]. Esto le permite configurar y registrar varios componentes a la vez. El siguiente código muestra un arreglo de configuración que se puede utilizar para configurar una aplicación, al mismo tiempo que el registro de la "db", "cache" y "buscar" componentes:

```php
return [
    // ...
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],
        'cache' => 'yii\caching\ApcCache',
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
];
```
