Módulos
=======
Los módulos son unidades de software independientes que consisten en [modelos](structure-models.md), 
[vistas](structure-views.md), [controladores](structure-controllers.md), y otros componentes de apoyo. Los usuarios 
finales pueden acceder a los controladores de un módulo cuando éste está instalado en la 
[aplicación](structure-applications.md). Por éstas razones, los módulos a menudo se considerados como 
mini-aplicaciones. Los módulos difieren de las [aplicaciones](structure-applications.md) en que los módulos no pueden 
ser desplegados solos y tienen que residir dentro de aplicaciones.

## Creación de Módulos<span id="creating-modules"></span>

Un módulo está organizado de tal manera que contiene un directorio llamado [[yii\base\Module::basePath|base path]] del 
módulo. Dentro de este directorio, hay subdirectorios tales como 'controllers', 'models', 'views', que contienen 
controladores, modelos, vistas y otro código, exactamente como una aplicación. El siguiente ejemplo muestra el 
contenido dentro de un módulo:

```
forum/
    Module.php                   archivo clase módulo
    controllers/                 contiene archivos de la clase controlador
        DefaultController.php    archivo clase controlador por defecto
    models/                      contiene los archivos de clase modelo
    views/                       contiene las vistas de controlador y los archivos de diseño
        layouts/                 contiene los archivos de diseño de las vistas
        default/                 contiene los archivos de vista del DefaultController
            index.php            archivo de vista del index
```

### Clases Módulo <span id="module-classes"></span>

Cada módulo debe tener una única clase módulo que extiende a [[yii\base\Module]]. La clase debe encontrarse 
directamente debajo del [[yii\base\Module::basePath|base path]] y debe ser [autocargable](concept-autoloading.md). 
Cuando se está accediendo a un módulo, se creará una única instancia de la clase módulo correspondiente. Como en las 
[instancias de aplicación](structure-applications.md), las instancias de módulo se utilizan para compartir datos y 
componentes de código dentro de los módulos.

El siguiente ejemplo muestra como podría ser una clase módulo.

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->params['foo'] = 'bar';
        // ...  otro código de inicialización ...
    }
}
```

Si el método 'init()' contiene mucho código de inicialización de las propiedades del módulo, también se puede guardar 
en términos de configuración y cargarlo con el siguiente código ‘init()’:

```php
public function init()
{
    parent::init();
    // inicializa el módulo con la configuración cargada desde config.php
    \Yii::configure($this, require __DIR__ . '/config.php');
}
```

donde el archivo de configuración ‘config.php’ puede contener el siguiente contenido, similar al de 
[configuraciones de aplicación](structure-applications.md#application-configurations).

```php
<?php
return [
    'components' => [
        // lista de configuraciones de componente
    ],
    'params' => [
        // lista de parámetros
    ],
];
```

### Controladores en Módulos <span id="controllers-in-modules"></span>

Cuando se crean controladores en un modelo, una convención es poner las clases controlador debajo del sub-espacio de 
nombres de ‘controllers’ del espacio de nombres de la clase módulo. Esto también significa que los archivos de la 
clase controlador deben ponerse en el directorio ‘controllers’ dentro del [[yii\base\Module::basePath|base path]] del 
módulo. Por ejemplo, para crear un controlador ‘post’ en el módulo ‘forum’ mostrado en la última subdivisión, se debe 
declarar la clase controlador de la siguiente manera:

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

Se puede personalizar el espacio de nombres de las clases controlador configurando la propiedad 
[[yii\base\Module::controllerNamespace]]. En el caso que alguno de los controladores esté fuera del espacio de 
nombres, se puede hacer accesible configurando la propiedad [[yii\base\Module::controllerMap]], similar a 
[como se hace en una aplicación](structure-applications.md#controller-map).

### Vistas en Módulos <span id="views-in-modules"></span>

Las vistas en un módulo deben alojarse en el directorio ‘views’ dentro del módulo del 
[[yii\base\Module::basePath|base path]]. Las vistas renderizadas por un controlador en el módulo, deben alojarse en el 
directorio ‘views/ControllerID’, donde el ‘ControllerID’ hace referencia al 
[ID del controlador](structure-controllers.md#routes). Por ejemplo, si la clase controlador es ‘PostController’, el 
directorio sería ‘views/post’ dentro del [[yii\base\Module::basePath|base path]] del módulo.

Un modulo puede especificar un [layout](structure-views.md#layouts) que se aplica a las vistas renderizadas por los 
controladores del módulo. El layout debe alojarse en el directorio ‘views/layouts’ por defecto, y se puede configurar 
la propiedad [[yii\base\Module::layout]] para apuntar al nombre del layout. Si no se configura la propiedad ‘layout’, 
se usar el layout de la aplicación.

## Uso de los Módulos <span id="using-modules"></span>

Para usar un módulo en una aplicación, simplemente se tiene que configurar la aplicación añadiendo el módulo en la 
propiedad [[yii\base\Application::modules|modules]] de la aplicación. El siguiente ejemplo de la 
[configuración de la aplicación](structure-applications.md#application-configurations) usa el modelo ‘forum’:

```php
[
    'modules' => [
        'forum' => [
            'class' => 'app\modules\forum\Module',
            // ... otras configuraciones para el módulo ...
        ],
    ],
]
```

La propiedad [[yii\base\Application::modules|modules]] contiene un array de configuraciones de módulo.  Cada clave del 
array representa un *ID de módulo* que identifica de forma única el módulo de entre todos los módulos de la 
aplicación, y el correspondiente valor del array es la [configuración](concept-configurations.md) para crear el módulo.

### Rutas <span id="routes"></span>

De Igual manera que el acceso a los controladores en una aplicación, las [rutas](structure-controllers.md#routes) se 
utiliza para dirigirse a los controladores en un módulo. Una ruta para un controlador dentro de un módulo debe empezar 
con el ID del módulo seguido por el ID del controlador y el ID de la acción. Por ejemplo, si una aplicación usa un 
módulo llamado ‘forum’, la ruta ‘forum/post/index’ representaría la acción ‘index’ del controlador ‘post’ en el 
módulo. Si la ruta sólo contiene el ID del módulo, entonces la propiedad [[yii\base\Module::defaultRoute]] que por 
defecto es ‘default’, determinara que controlador/acción debe usarse. Esto significa que la ruta ‘forum’ representaría 
el controlador ‘default’ en el módulo ‘forum’.

### Acceder a los Módulos <span id="accessing-modules"></span>

Dentro de un módulo, se puede necesitar obtener la instancia de la [clase módulo](#module-classes) para poder acceder 
al ID del módulo, componentes del módulo, etc. Se puede hacer usando la siguiente declaración:

```php
$module = MyModuleClass::getInstance();
```

Dónde ‘MyModuleClass’ hace referencia al nombre de la clase módulo en la que estemos interesados. El método 
‘getInstance()’ devolverá la instancia actualmente solicitada de la clase módulo. Si no se solicita el módulo, el 
método devolverá nulo. Hay que tener en cuenta que si se crea una nueva instancia del módulo, esta será diferente a la 
creada por Yii en respuesta a la solicitud.

> Info: Cuando se desarrolla un módulo, no se debe dar por sentado que el módulo usará un ID fijo. Esto se debe 
  a que un módulo puede asociarse a un ID arbitrario cuando se usa en una aplicación o dentro de otro módulo. Para 
  obtener el ID del módulo, primero se debe usar el código del anterior ejemplo para obtener la instancia y luego el 
  ID mediante ‘$modeule->id’.

También se puede acceder a la instancia de un módulo usando las siguientes declaraciones:

```php
// obtiene el modulo hijo cuyo ID es “forum”
$module = \Yii::$app->getModule('forum');

// obtiene el módulo al que pertenece la petición actual
$module = \Yii::$app->controller->module;
```

El primer ejemplo sólo es útil cuando conocemos el ID del módulo, mientras que el segundo es mejor usarlo cuando 
conocemos los controladores que se están solicitando.

Una vez obtenida la instancia del módulo, se puede acceder a parámetros o componentes registrados con el módulo. Por 
ejemplo:

```php
$maxPostCount = $module->params['maxPostCount'];
```

### Bootstrapping Módulos <span id="bootstrapping-modules"></span>

Puede darse el caso en que necesitemos que un módulo se ejecute en cada petición. El módulo [[yii\debug\Module|debug]] 
es un ejemplo. Para hacerlo, tenemos que listar los IDs de los módulos en la propiedad 
[[yii\base\Application::bootstrap|bootstrap]] de la aplicación.

Por ejemplo, la siguiente configuración de aplicación se asegura de que el módulo ‘debug’ siempre se cargue:

```php
[
    'bootstrap' => [
        'debug',
    ],

    'modules' => [
        'debug' => 'yii\debug\Module',
    ],
]
```

## Módulos anidados <span id="nested-modules"></span>

Los módulos pueden ser anidados sin límite de niveles. Es decir, un módulo puede contener un módulo y éste a la vez 
contener otro módulo. Nombramos *padre* al primero mientras que al segundo lo nombramos *hijo*. Los módulos hijo se 
tienen que declarar en la propiedad [[yii\base\Module::modules|modules]] de sus módulos padre. Por ejemplo:

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                // debe considerarse usar un nombre de espacios más corto!
                'class' => 'app\modules\forum\modules\admin\Module',
            ],
        ];
    }
}
```

En un controlador dentro de un módulo anidado, la ruta debe incluir el ID de todos los módulos antecesores. Por 
ejemplo, la ruta ‘forum/admin/dashboard/index’ representa la acción ‘index’ del controlador ‘dashboard’ en el módulo 
‘admin’ que es el módulo hijo del módulo ‘forum’. 

> Info: El método [[yii\base\Module::getModule()|getModule()]] sólo devuelve el módulo hijo que pertenece 
directamente a su padre. La propiedad [[yii\base\Application::loadedModules]] contiene una lista de los módulos 
cargados, incluyendo los hijos directos y los anidados, indexados por sus nombres de clase.

## Mejores Prácticas <span id="best-practices"></span>

Es mejor usar los módulos en grandes aplicaciones en las que sus funcionalidades puedan ser divididas en diferentes 
grupos, cada uno compuesto por funcionalidades directamente relacionadas. Cada grupo de funcionalidades se puede 
desarrollar como un módulo que puede ser desarrollado y mantenido por un programador o equipo específico.

Los módulos también son una buena manera de reutilizar código a nivel de grupo de funcionalidades. Algunas 
funcionalidades de uso común, tales como la gestión de usuarios o la gestión de comentarios, pueden ser desarrollados 
como módulos para que puedan ser fácilmente reutilizados en futuros proyectos.
