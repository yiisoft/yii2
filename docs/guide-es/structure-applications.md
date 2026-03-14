Aplicaciones
============

Las `Applications` (aplicaciones) son objetos que gobiernan la estructura total y el ciclo de vida de las aplicaciones
hechas en Yii.
Cada aplicación Yii contiene un objeto `Application` que es creado en el [script de entrada](structure-entry-scripts.md)
y es globalmente accesible a través de la expresión `\Yii::$app`.

> Info: Dependiendo del contexto, cuando decimos "una aplicación", puede significar tanto un objeto Application
  o un sistema desarrollado en Yii.

Hay dos tipos de aplicaciones: [[yii\web\Application|aplicaciones Web]] y
[[yii\console\Application|aplicaciones de consola]]. Como el nombre lo indica, la primera maneja principalmente
Web requests mientras que la última maneja requests (peticiones) de la línea de comandos.


## Configuraciones de las Aplicaciones <span id="application-configurations"></span>

Cuando un [script de entrada](structure-entry-scripts.md) crea una aplicación, cargará
una [configuración](concept-configurations.md) y la aplicará a la aplicación, como se muestra a continuación:

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// carga la configuración de la aplicación
$config = require __DIR__ . '/../config/web.php';

// instancia y configura la aplicación
(new yii\web\Application($config))->run();
```

Principalmente, las [configuraciones](concept-configurations.md) de una aplicación especifican
como inicializar las propiedades de un objeto `application`. Debido a que estas configuraciones
suelen ser complejas, son usualmente guardadas en [archivos de configuración](concept-configurations.md#configuration-files),
como en el archivo `web.php` del ejemplo anterior.


## Propiedades de la Aplicación <span id="application-properties"></span>

Hay muchas propiedades importantes en la aplicación que deberían configurarse en en la configuración de la aplicación.
Estas propiedades suelen describir el entorno en el cual la aplicación está corriendo.
Por ejemplo, las aplicaciones necesitan saber cómo cargar [controladores](structure-controllers.md),
dónde guardar archivos temporales, etc. A continuación, resumiremos esas propiedades.


### Propiedades Requeridas <span id="required-properties"></span>

En cualquier aplicación, debes configurar al menos dos propiedades: [[yii\base\Application::id|id]]
y [[yii\base\Application::basePath|basePath]].


#### [[yii\base\Application::id|id]] <span id="id"></span>

La propiedad [[yii\base\Application::id|id]] especifica un ID único que diferencia una aplicación de otras.
Es mayormente utilizada a nivel programación. A pesar de que no es un requerimiento, para una mejor interoperabilidad,
se recomienda utilizar sólo caracteres alfanuméricos.


#### [[yii\base\Application::basePath|basePath]] <span id="basePath"></span>

La propiedad [[yii\base\Application::basePath|basePath]] especifica el directorio raíz de una aplicación.
Es el directorio que alberga todos los archivos protegidos de un sistema. Bajo este directorio,
tendrás normalmente sub-directorios como `models`, `views`, `controllers`, que contienen el código fuente
correspondiente al patrón MVC.

Puedes configurar la propiedad [[yii\base\Application::basePath|basePath]] usando la ruta a un directorio
o un [alias](concept-aliases.md). En ambas formas, el directorio debe existir, o se lanzará una excepción.
La ruta será normalizada utilizando la función `realpath()`.

La propiedad [[yii\base\Application::basePath|basePath]] es utilizada a menudo derivando otras rutas
(ej. la ruta `runtime`). Por esta razón, un alias llamado `@app` está predefinido para representar esta ruta.
Rutas derivadas pueden ser entonces creadas a partir de este alias (ej. `@app/runtime` para referirse al directorio `runtime`).


### Propiedades Importantes <span id="important-properties"></span>

Las propiedades descritas en esta subsección a menudo necesita ser configurada porque difieren entre las
diferentes aplicaciones.


#### [[yii\base\Application::aliases|aliases]] <span id="aliases"></span>

Esta propiedad te permite definir un grupo de [alias](concept-aliases.md) en términos de un array (matriz).
Las claves del array son los nombres de los alias, y los valores su correspondiente definición.
Por ejemplo:

```php
[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
```

Esta propiedad está provista de tal manera que puedas definir alias en términos de configuraciones de la aplicación
en vez de llamadas al método [[Yii::setAlias()]].


#### [[yii\base\Application::bootstrap|bootstrap]] <span id="bootstrap"></span>

Esta es una propiedad importante. Te permite definir un array de los componentes que deben ejecutarse
durante el [[yii\base\Application::bootstrap()|proceso de `bootstrapping`]] de la aplicación.
Por ejemplo, si quieres personalizar las [reglas de URL](runtime-url-handling.md) de un [módulo](structure-modules.md),
podrías listar su ID como un elemento de este array.

Cada componente listado en esta propiedad puede ser especificado en cualquiera de los siguientes formatos:

- el ID de un componente como está especificado vía [`components`](#components).
- el ID de un módulo como está especificado vía [`modules`](#modules).
- un nombre de clase.
- un array de configuración.

Por ejemplo:

```php
[
    'bootstrap' => [
        // un ID de componente o de módulo
        'demo',

        // un nombre de clase
        'app\components\TrafficMonitor',

        // un array de configuración
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ]
    ],
]
```

Durante el proceso de `bootstrapping`, cada componente será instanciado. Si la clase del componente
implementa [[yii\base\BootstrapInterface]], también se llamará a su método [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]].

Otro ejemplo práctico se encuentra en la configuración del [Template de Aplicación Básica](start-installation.md),
donde los módulos `debug` y `gii` son configurados como componentes `bootstrap` cuando la aplicación está
corriendo en un entorno de desarrollo,

```php
if (YII_ENV_DEV) {
    // ajustes en la configuración del entorno 'dev' (desarrollo)
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

> Note: Agregar demasiados componentes `bootstrap` degradará la performance de tu aplicación debido a que
  por cada request, se necesita correr el mismo grupo de componentes. Por lo tanto, utiliza componentes `bootstrap` con criterio.


#### [[yii\web\Application::catchAll|catchAll]] <span id="catchAll"></span>

Esta propiedad está solamente soportada por [[yii\web\Application|aplicaciones Web]]. Especifica
la [acción de controlador](structure-controllers.md) que debería manejar todos los requests (peticiones) del usuario.
Es mayormente utilizada cuando una aplicación está en "modo de mantenimiento" y necesita que todas las peticiones
sean capturadas por una sola acción.

La configuración es un array cuyo primer elemento especifica la ruta de la acción.
El resto de los elementos del array (pares clave-valor) especifica los parámetros a ser enviados a la acción.
Por ejemplo:

```php
[
    'catchAll' => [
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```


#### [[yii\base\Application::components|components]] <span id="components"></span>

Esta es la propiedad más importante. Te permite registrar una lista de componentes llamados [componentes de aplicación](#structure-application-components.md)
que puedes utilizar en otras partes de tu aplicación. Por ejemplo:

```php
[
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
    ],
]
```

Cada componente de la aplicación es un par clave-valor del array. La clave representa el ID del componente,
mientras que el valor representa el nombre de la clase del componente o una [configuración](concept-configurations.md).

Puedes registrar cualquier componente en una aplicación, y el componente puede ser globalmente accedido utilizando
la expresión `\Yii::$app->ComponentID`.

Por favor, lee la sección [Componentes de la Aplicación](structure-application-components.md) para mayor detalle.


#### [[yii\base\Application::controllerMap|controllerMap]] <span id="controllerMap"></span>

Esta propiedad te permite mapear un ID de controlador a una clase de controlador arbitraria. Por defecto, Yii mapea
ID de controladores a clases de controladores basado en una [convención](#controllerNamespace) (ej. el ID `post` será mapeado
a `app\controllers\PostController`). Configurando esta propiedad, puedes saltear esa convención
para controladores específicos. En el siguiente ejemplo, `account` será mapeado a
`app\controllers\UserController`, mientras que `article` será mapeado a `app\controllers\PostController`.

```php
[
    'controllerMap' => [
        'account' => 'app\controllers\UserController',
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]
```

Las claves de este array representan los ID de los controladores, mientras que los valores representan
los nombres de clase de dichos controladores o una [configuración](concept-configurations.md).


#### [[yii\base\Application::controllerNamespace|controllerNamespace]] <span id="controllerNamespace"></span>

Esta propiedad especifica el `namespace` bajo el cual las clases de los controladores deben ser ubicados. Por defecto es
`app\controllers`. Si el ID es `post`, por convención el controlador correspondiente (sin
`namespace`) será `PostController`, y el nombre completo (cualificado) de la clase `app\controllers\PostController`.

Las clases de controladores pueden ser ubicados también en sub-directorios del directorio correspondiente a este `namespace`.
Por ejemplo, dado el ID de controlador `admin/post`, el nombre completo de la clase sería `app\controllers\admin\PostController`.

Es importante que el nombre completo de la clase del controlador sea [auto-cargable](concept-autoloading.md)
y el `namespace` actual de la clase coincida con este valor. De otro modo, recibirás
un error "Page Not Found" ("Página no Encontrada") cuando accedas a la aplicación.

En caso de que quieras romper con la convención cómo se comenta arriba, puedes configurar la propiedad [controllerMap](#controllerMap).


#### [[yii\base\Application::language|language]] <span id="language"></span>

Esta propiedad especifica el idioma en el cual la aplicación debería mostrar el contenido a los usuarios.
El valor por defecto de esta propiedad es `en`, referido a English. Deberías configurar esta propiedad
si tu aplicación necesita soporte multi-idioma.

El valor de esta propiedad determina varios aspectos de la [internacionalización](tutorial-i18n.md),
incluido la traducción de mensajes, formato de fecha y números, etc. Por ejemplo, el widget [[yii\jui\DatePicker]]
utilizará el valor de esta propiedad para determinar en qué idioma el calendario debe ser mostrado y cómo dar formato
a la fecha.

Se recomienda que especifiques el idioma en términos de una [Código de idioma IETF](https://es.wikipedia.org/wiki/Código_de_idioma_IETF).
Por ejemplo, `en` se refiere a English, mientras que `en-US` se refiere a English (United States).

Se pueden encontrar más detalles de este aspecto en la sección [Internacionalización](tutorial-i18n.md).


#### [[yii\base\Application::modules|modules]] <span id="modules"></span>

Esta propiedad especifica los [módulos](structure-modules.md) que contiene la aplicación.

Esta propiedad toma un array con los nombre de clases de los módulos o [configuraciones](concept-configurations.md) con las claves siendo
los IDs de los módulos. Por ejemplo:

```php
[
    'modules' => [
        // módulo "booking" especificado con la clase del módulo
        'booking' => 'app\modules\booking\BookingModule',

        // módulo "comment" especificado usando un array de configuración
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

Por favor consulta la sección [Módulos](structure-modules.md) para más detalles.


#### [[yii\base\Application::name|name]] <span id="name"></span>

Esta propiedad especifica el nombre de la aplicación que será mostrado a los usuarios. Al contrario de
[[yii\base\Application::id|id]], que debe tomar un valor único, el valor de esta propiedad existe principalmente
para propósito de visualización y no tiene porqué ser única.

No siempre necesitas configurar esta propiedad si en tu aplicación no va a ser utilizada.


#### [[yii\base\Application::params|params]] <span id="params"></span>

Esta propiedad especifica un array con parámetros accesibles desde cualquier lugar de tu aplicación.
En vez de usar números y cadenas fijas por todos lados en tu código, es una buena práctica definirlos como
parámetros de la aplicación en un solo lugar y luego utilizarlos donde los necesites. Por ejemplo, podrías definir el tamaño
de las imágenes en miniatura de la siguiente manera:

```php
[
    'params' => [
        'thumbnail.size' => [128, 128],
    ],
]
```

Entonces, cuando necesites acceder a esa configuración en tu aplicación, podrías hacerlo utilizando el código siguiente:

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

Más adelante, si decides cambiar el tamaño de las miniaturas, sólo necesitas modificarlo en la configuración de la aplicación
sin necesidad de tocar el código que lo utiliza.


#### [[yii\base\Application::sourceLanguage|sourceLanguage]] <span id="sourceLanguage"></span>

Esta propiedad especifica el idioma en el cual la aplicación está escrita. El valor por defecto es `'en-US'`,
referido a English (United States). Deberías configurar esta propiedad si el contenido de texto en tu código no está en inglés.

Como la propiedad [language](#language), deberías configurar esta propiedad siguiendo el [Código de idioma IETF](https://es.wikipedia.org/wiki/Código_de_idioma_IETF).
Por ejemplo, `en` se refiere a English, mientras que `en-US` se refiere a English (United States).

Puedes encontrar más detalles de esta propiedad en la sección [Internacionalización](tutorial-i18n.md).


#### [[yii\base\Application::timeZone|timeZone]] <span id="timeZone"></span>

Esta propiedad es provista como una forma alternativa de definir el `time zone` de PHP por defecto en tiempo de ejecución.
Configurando esta propiedad, escencialmente estás llamando a la función de PHP [date_default_timezone_set()](https://www.php.net/manual/es/function.date-default-timezone-set.php).
Por ejemplo:

```php
[
    'timeZone' => 'America/Los_Angeles',
]
```


#### [[yii\base\Application::version|version]] <span id="version"></span>

Esta propiedad especifica la versión de la aplicación. Es por defecto `'1.0'`. No hay total necesidad de configurarla
si tu no la usarás en tu código.


### Propiedades Útiles <span id="useful-properties"></span>

Las propiedades especificadas en esta sub-sección no son configuradas normalmente ya que sus valores por defecto
estipulan convenciones comunes. De cualquier modo, aún puedes configurarlas en caso de que quieras romper con la convención.


#### [[yii\base\Application::charset|charset]] <span id="charset"></span>

Esta propiedad especifica el `charset` que la aplicación utiliza. El valor por defecto es `'UTF-8'`, que debería ser mantenido
tal cual para la mayoría de las aplicaciones a menos que estés trabajando con sistemas legados que utilizan muchos datos no-unicode.


#### [[yii\base\Application::defaultRoute|defaultRoute]] <span id="defaultRoute"></span>

Esta propiedad especifica la [ruta](runtime-routing.md) que una aplicación debería utilizar si el `request`
no especifica una. La ruta puede consistir el ID de un sub-módulo, el ID de un controlador, y/o el ID de una acción.
Por ejemplo, `help`, `post/create`, `admin/post/create`. Si el ID de la acción no se especifica, tomará el valor por defecto
especificado en [[yii\base\Controller::defaultAction]].

Para [[yii\web\Application|aplicaciones Web]], el valor por defecto de esta propiedad es `'site'`, lo que significa que el
controlador `SiteController` y su acción por defecto serán usados. Como resultado, si accedes a la aplicación sin
especificar una ruta, mostrará el resultado de `app\controllers\SiteController::actionIndex()`.

Para [[yii\console\Application|aplicaciones de consola]], el valor por defecto es `'help'`, lo que significa que el comando
[[yii\console\controllers\HelpController::actionIndex()]] debería ser utilizado. Como resultado, si corres el comando `yii`
sin proveer ningún argumento, mostrará la información de ayuda.


#### [[yii\base\Application::extensions|extensions]] <span id="extensions"></span>

Esta propiedad especifica la lista de [extensiones](structure-extensions.md) que se encuentran instaladas y son utilizadas
por la aplicación.
Por defecto, tomará el array devuelto por el archivo `@vendor/yiisoft/extensions.php`. El archivo `extensions.php`
es generado y mantenido automáticamente cuando utilizas [Composer](https://getcomposer.org) para instalar extensiones.
Por lo tanto, en la mayoría de los casos no necesitas configurarla.

En el caso especial de que quieras mantener las extensiones a mano, puedes configurar la propiedad como se muestra a continuación:

```php
[
    'extensions' => [
        [
            'name' => 'nombre de la extensión',
            'version' => 'número de versión',
            'bootstrap' => 'BootstrapClassName',  // opcional, puede ser también un array de configuración
            'alias' => [  // opcional
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... más extensiones como las de arriba ...

    ],
]
```

Como puedes ver, la propiedad toma un array de especificaciones de extensiones. Cada extensión es especificada mediante un array
que consiste en los elementos `name` y `version`. Si una extensión necesita ser ejecutada durante el proceso de [`bootstrap`](runtime-bootstrapping.md),
un elemento `bootstrap` puede ser especificado con un nombre de clase o un array de [configuración](concept-configurations.md).
Una extensión también puede definir algunos [alias](concept-aliases.md).


#### [[yii\base\Application::layout|layout]] <span id="layout"></span>

Esta propiedad especifica el valor del `layout` por defecto que será utilizado al renderizar una [vista](structure-views.md).
El valor por defecto es `'main'`, y se refiere al archivo `main.php` bajo el [`layout path`](#layoutPath) definido.
Si tanto el [`layout path`](#layoutPath) y el [`view path`](#viewPath) están utilizando los valores por defecto,
el archivo `layout` puede ser representado con el alias `@app/views/layouts/main.php`.

Puedes configurar esta propiedad con el valor `false` si quieres desactivar el `layout` por defecto, aunque esto sería un
caso muy raro.


#### [[yii\base\Application::layoutPath|layoutPath]] <span id="layoutPath"></span>

Esta propiedad especifica el lugar por defecto donde deben buscarse los archivos `layout`. El valor por defecto
es el sub-directorio `layouts` bajo el [`view path`](#viewPath). Si el [`view path`](#viewPath) usa su valor por defecto,
el `layout path` puede ser representado con el alias `@app/views/layouts`.

Puedes configurarlo como un directorio o utilizar un [alias](concept-aliases.md).


#### [[yii\base\Application::runtimePath|runtimePath]] <span id="runtimePath"></span>

Esta propiedad especifica dónde serán guardados los archivos temporales, como archivos de log y de cache, pueden ser generados.
El valor por defecto de esta propiedad es el alias `@app/runtime`.

Puedes configurarlo como un directorio o utilizar un [alias](concept-aliases.md). Ten en cuenta que el
directorio debe tener permisos de escritura por el proceso que corre la aplicación. También este directorio debe estar protegido
de ser accedido por usuarios finales, ya que los archivos generados pueden tener información sensible.

Para simplificar el acceso a este directorio, Yii trae predefinido el alias `@runtime` para él.


#### [[yii\base\Application::viewPath|viewPath]] <span id="viewPath"></span>

Esta propiedad especifica dónde están ubicados los archivos de la vista. El valor por defecto de esta propiedad está
representado por el alias `@app/views`. Puedes configurarlo como un directorio o utilizar un [alias](concept-aliases.md).


#### [[yii\base\Application::vendorPath|vendorPath]] <span id="vendorPath"></span>

Esta propiedad especifica el directorio `vendor` que maneja [Composer](https://getcomposer.org). Contiene
todas las librerías de terceros utilizadas por tu aplicación, incluyendo el núcleo de Yii. Su valor por defecto
está representado por el alias `@app/vendor`.

Puedes configurarlo como un directorio o utilizar un [alias](concept-aliases.md). Cuando modificas esta propiedad,
asegúrate de ajustar la configuración de Composer en concordancia.

Para simplificar el acceso a esta ruta, Yii trae predefinido el alias `@vendor`.


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <span id="enableCoreCommands"></span>

Esta propiedad está sólo soportada por [[yii\console\Application|aplicaciones de consola]].
Especifica si los comandos de consola incluidos en Yii deberían estar habilitados o no.
Por defecto está definido como `true`.


## Eventos de la Aplicación <span id="application-events"></span>

Una aplicación dispara varios eventos durante su ciclo de vida al manejar un `request`. Puedes conectar
manejadores a dichos eventos en la configuración de la aplicación como se muestra a continuación:

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

El uso de la sintáxis `on nombreEvento` es descrita en la sección [Configuraciones](concept-configurations.md#configuration-format).

Alternativamente, puedes conectar manejadores de eventos durante el [`proceso de bootstrapping`](runtime-bootstrapping.md)
después de que la instancia de la aplicación es creada. Por ejemplo:

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <span id="beforeRequest"></span>

Este evento es disparado *before* (antes) de que la aplicación maneje el `request`. El nombre del evento es `beforeRequest`.

Cuando este evento es disparado, la instancia de la aplicación ha sido configurada e inicializada. Por lo tanto es un
buen lugar para insertar código personalizado vía el mecanismo de eventos para interceptar dicho manejo del `request`.
Por ejemplo, en el manejador del evento, podrías definir dinámicamente la propiedad [[yii\base\Application::language]]
basada en algunos parámetros.


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_AFTER_REQUEST]] <span id="afterRequest"></span>

Este evento es disparado *after* (después) de que una aplicación finaliza el manejo de un `request` pero *before* (antes) de enviar el `response` (respuesta).
El nombre del evento es `afterRequest`.

Cuando este evento es disparado, el manejo del `request` está finalizado y puedes aprovechar para realizar algún
post-proceso del mismo o personalizar el `response` (respuesta).

Ten en cuenta que el componente [[yii\web\Response|response]] también dispara algunos eventos mientras está enviando el contenido
a los usuarios finales. Estos eventos son disparados *after* (después) de este evento.


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_ACTION]] <span id="beforeAction"></span>

Este evento es disparado *before* (antes) de ejecutar cualquier [acción de controlador](structure-controllers.md).
El nombre de este evento es `beforeAction`.

El parámetro evento es una instancia de [[yii\base\ActionEvent]]. Un manejador de eventos puede definir
la propiedad [[yii\base\ActionEvent::isValid]] como `false` para detener la ejecución de una acción.
Por ejemplo:

```php
[
    'on beforeAction' => function ($event) {
        if (..alguna condición..) {
            $event->isValid = false;
        } else {
        }
    },
]
```

Ten en cuenta que el mismo evento `beforeAction` también es disparado por [módulos](structure-modules.md)
y [controladores)(structure-controllers.md). Los objectos aplicación son los primeros en disparar este evento,
seguidos por módulos (si los hubiera), y finalmente controladores. Si un manejador de eventos define [[yii\base\ActionEvent::isValid]]
como `false`, todos los eventos siguientes NO serán disparados.


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_AFTER_ACTION]] <span id="afterAction"></span>

Este evento es disparado *after* (después) de ejecutar cualquier [acción de controlador](structure-controllers.md).
El nombre de este evento es `afterAction`.

El parámetro evento es una instancia de [[yii\base\ActionEvent]]. A través de la
propiedad [[yii\base\ActionEvent::result]], un manejador de eventos puede acceder o modificar el resultado de una acción.
Por ejemplo:

```php
[
    'on afterAction' => function ($event) {
        if (..alguna condición...) {
            // modificar $event->result
        } else {
        }
    },
]
```

Ten en cuenta que el mismo evento `afterAction` también es disparado por [módulo](structure-modules.md)
y [controladores)(structure-controllers.md). Estos objetos disparan el evento en orden inverso
que los de `beforeAction`. Esto quiere decir que los controladores son los primeros en dispararlo,
seguido por módulos (si los hubiera), y finalmente aplicaciones.


## Ciclo de Vida de una Aplicación <span id="application-lifecycle"></span>

Cuando un [script de entrada](structure-entry-scripts.md) está siendo ejecutado para manejar un `request`,
una aplicación experimenta el siguiente ciclo de vida:

1. El script de entrada carga el array de configuración de la aplicación.
2. El script de entrada crea una nueva instancia de la aplicación:
  * Se llama a [[yii\base\Application::preInit()|preInit()]], que configura algunas propiedades
    de alta prioridad de la aplicación, como [[yii\base\Application::basePath|basePath]].
  * Registra el [[yii\base\Application::errorHandler|manejador de errores]].
  * Configura las propiedades de la aplicación.
  * Se llama a [[yii\base\Application::init()|init()]] con la subsiguiente llamada a
    [[yii\base\Application::bootstrap()|bootstrap()]] para correr componentes `bootstrap`.
3. El script de entrada llama a [[yii\base\Application::run()]] para correr la aplicación:
  * Dispara el evento [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]].
  * Maneja el `request`: lo resuelve en una [route (ruta)](runtime-routing.md) con los parámetros asociados;
    crea el módulo, controlador y objetos acción como se especifica en dicha ruta; y entonces ejecuta la acción.
  * Dispara el evento [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]].
  * Envía el `response` (respuesta) al usuario.
4. El script de entrada recibe el estado de salida de la aplicación y completa el proceso del `request`.
