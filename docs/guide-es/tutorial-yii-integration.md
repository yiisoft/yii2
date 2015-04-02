Trabajando con código de terceros
=================================

De tiempo en tiempo, puede necesitar usar algún código de terceros en sus aplicaciones Yii. O puedes querer usar Yii como una librería en otros sistemas de terceros. En esta sección, te enseñaremos cómo conseguir estos objetivos.


## Usando librerías de terceros en Yii <span id="using-libs-in-yii"></span>

Para usar una librería en una aplicación Yii, primeramente debes de asegurarte que las clases een la librería son incluidas adecuadamente o pueden ser cargadas de forma automática.


### Usando Paquetes de Composer <span id="using-composer-packages"></span>

Muchas librerías de terceros son liberadas en términos de paquetes [Composer](https://getcomposer.org/).
Puedes instalar este tipo de librerias siguiendo dos sencillos pasos:

1. modificar el fichero `composer.json` de tu aplicación y especificar que paquetes Composer quieres instalar.
2. ejecuta `composer install` para instalar los paquetes específicados.

Las clases en los paquetes Composer instalados pueden ser autocargados usando el cargador automatizado de Composer autoloader. Asegúrate que el fichero [script de entrada](structure-entry-scripts.md) de tu aplicación contiene las siguientes líneas para instalar el cargador automático de Composer:

```php
// instalar el cargador automático de  Composer
require(__DIR__ . '/../vendor/autoload.php');

// incluir rl fichero de la clase Yii
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
```


### Usando librerías Descargadas <span id="using-downloaded-libs"></span>

Si la librería no es liberada como un paquete de Composer, debes de seguir sus instrucciones de instalación para instalarla.
En muchos casos, puedes necesitar descargar manualmente el fichero de la versión y desempaquetarlo en el directorio `BasePath/vendor` , donde `BasePath` representa el [camino base (base path)](structure-applications.md#basePath) de tu aplicación.

Si la librería lleva su propio cargador automático (autoloader), puedes instalarlo en [script de entrada](structure-entry-scripts.md) de tu aplicación. Es recomendable que la instalación se  termine antes de incluir el fichero `Yii.php` de forma que el cargador automático tenga precedencia al cargar de forma automática las clases.

Si la librería no provee un cargador automático de clases, pero la denominación de sus clases sigue el [PSR-4](http://www.php-fig.org/psr/psr-4/), puedes usar el cargador automático de Yii para cargar de forma automática las clases. Todo lo que necesitas es declarar un [alias raiz](concept-aliases.md#defining-aliases) para cada espacio de nombres (namespace) raiz usado en sus clases. Por ejemplo, asume que has instalado una librería en el directorio `vendor/foo/bar`, y que las clases de la librería están bajo el espacio de nombres raiz `xyz`. Puedes incluir el siguiente código en la configuración de tu aplicación:

```php
[
    'aliases' => [
        '@xyz' => '@vendor/foo/bar',
    ],
]
```

Si ninguno de lo anterior es el caso, estaría bien que la librería dependa del camino de inclusión (include path) de configuración de PHP para localizar correctamente e incluir los ficheros  de las clases. Simplemente siguiendo estas instrucciones de cómo configurar el camino de inclusión de PHP.

En el caso más grave en el que la librería necesite incluir cada uno de sus ficheros de clases, puedes usar el siguiente método para incluir las clases según se pidan:

* Identificar que clases contiene la librería.
* Listar las clases y el camino a los ficheros correspondientes en `Yii::$classMap`  en el script de entrada [script de entrada](structure-entry-scripts.md) de la aplicación. Por ejemplo,
```php
Yii::$classMap['Class1'] = 'path/to/Class1.php';
Yii::$classMap['Class2'] = 'path/to/Class2.php';
```


## Usando Yii en Sistemas de Terceros <span id="using-yii-in-others"></span>

Debido a que Yii provee muchas posibilidades excelentes, a veces puedes querer usar alguna de sus características para permitir el desarrollo o mejora de sistemas de terceros, como es WordPress, Joomla, o aplicaciones desarrolladas usando otros frameworks de PHP. Por ejemplo, puedes queres usar la clase [[yii\helpers\ArrayHelper]]  o usar la característica [Active Record](db-active-record.md) en un sistema de terceros. Para lograr este objetivo, principalmente necesitas realizar dos pasos: instalar Yii , e iniciar  Yii.

Si el sistema de terceros usa Composer para manejar sus dependencias, simplemente ejecuta estos comandos para instalar Yii:

```
composer require "yiisoft/yii2:*"
composer install
```

En otro caso, puedes [descargar](http://www.yiiframework.com/download/) el fichero de la edición de Yii y desempaquetarla en el directorio `BasePath/vendor`.

Después, debes de modificar el script de entrada de sistema de terceros para incluir el siguiente código al principio:

```php
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$yiiConfig = require(__DIR__ . '/../config/yii/web.php');
new yii\web\Application($yiiConfig); // No ejecutes run() aquí
```

Como puedes ver, el código anterior es muy similar al que puedes ver en [script de entrada](structure-entry-scripts.md) de una aplicación típica. La única diferencia es que después de que se crea la instancia de la aplicación, el método `run()` no es llamado. Esto es así porque llamando a `run()`, Yii se haría cargo del control del flujo de trabajo del manejo de las peticiones, lo cual no es necesario en este caso por estar ya es manejado por la aplicación existente.

Como en una aplicación Yii, debes configurar la instancia de la aplicación basándose en el entorno que se está ejecutando del sistema de terceros. Por ejemplo, para usar la característica [Active Record](db-active-record.md) , necesitas configurar `db` [componente de la aplicación](structure-application-components.md) con los parámetros de la conexión de base de datos usados por el sistema de terceros.

Ahora puedes usar muchas características provistas por Yii. Por ejemplo, puedes crear clases Active Record y usarlas para trabajar con bases de datos.


## Usando Yii 2 con Yii 1 <span id="using-both-yii2-yii1"></span>

Si estaba usando Yii 1 previamente, es como si tuvieras una aplicación Yii 1 funcionando. En vez de reescribir toda la aplicación en Yii 2, puedes solamente mejorarla usando alguna de las características sólo disponibles en Yii 2.

Esto se puede lograr tal y como se describe abajo.

> Nota: Yii 2 requiere PHP 5.4 o superior. Debes de estar seguro que tanto tu servidor como la aplicación existente lo soportan.

Primero, instala Yii 2 en tu aplicación siguiendo las instrucciones descritas en la [última subsección](#using-yii-in-others).

Segundo,modifica el script de entrada de la aplicación como sigue,

```php
// incluir la clase Yii personalizada descrita debajo
require(__DIR__ . '/../components/Yii.php');

// configuración para la aplicación Yii 2
$yii2Config = require(__DIR__ . '/../config/yii2/web.php');
new yii\web\Application($yii2Config); // No llamar a run()

// configuración para la aplicación Yii 1
$yii1Config = require(__DIR__ . '/../config/yii1/main.php');
Yii::createWebApplication($yii1Config)->run();
```

Debido a que ambos Yii 1 y Yii 2 tiene la clase `Yii` , debes crear una versión personalizada para combinarlas.

El código anterior incluye el fichero con la clase `Yii` personalizada, que tiene que ser creada como sigue.

```php
$yii2path = '/path/to/yii2';
require($yii2path . '/BaseYii.php'); // Yii 2.x

$yii1path = '/path/to/yii1';
require($yii1path . '/YiiBase.php'); // Yii 1.x

class Yii extends \yii\BaseYii
{
    // copy-paste the code from YiiBase (1.x) here
}

Yii::$classMap = include($yii2path . '/classes.php');
// registrar el autoloader de Yii2 autoloader via Yii1
Yii::registerAutoloader(['Yii', 'autoload']);
// crear el contenedor de inyección de dependencia
Yii::$container = new yii\di\Container;
```

¡Esto es todo!. Ahora, en cualquier parte de tu código, puedes usar `Yii::$app` para acceder a la instancia de la aplicación de Yii 2, mientras `Yii::app()` proporciona la instancia de la aplicación de  Yii 1 :

```php
echo get_class(Yii::app()); // genera 'CWebApplication'
echo get_class(Yii::$app);  // genera 'yii\web\Application'
```
