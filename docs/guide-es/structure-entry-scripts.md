Scripts de Entrada
==================

Los scripts de entrada son el primer eslabón en el proceso de arranque de la aplicación. Una aplicación (ya sea una 
aplicación Web o una aplicación de consola) tiene un único script de entrada. Los usuarios finales hacen peticiones al 
script de entrada que instancia instancias de aplicación y remite la petición a estos.

Los scripts de entrada para aplicaciones Web tiene que estar alojado bajo niveles de directorios accesibles para la Web 
de manera que puedan ser accesibles para los usuarios finales. Normalmente se nombra como `index.php`, pero también se 
pueden usar cualquier otro nombre, los servidores Web proporcionados pueden localizarlo.

El script de entrada para aplicaciones de consola normalmente está alojado bajo la 
[ruta base](structure-applications.md) de las aplicaciones y es nombrado como `yii` (con el sufijo `.php`). Estos 
deberían ser ejecutables para que los usuarios puedan ejecutar las aplicaciones de consola a través del comando 
`./yii <ruta> [argumentos] [opciones]`.

El script de entrada principalmente hace los siguientes trabajos:

* Definir las constantes globales;
* Registrar el [cargador automático de Composer](https://getcomposer.org/doc/01-basic-usage.md#autoloading);
* Incluir el archivo de clase [[Yii]];
* Cargar la configuración de la aplicación;
* Crear y configurar una instancia de [aplicación](structure-applications.md);
* Llamar a [[yii\base\Application::run()]] para procesar la petición entrante.

## Aplicaciones Web <span id="web-applications"></span>

El siguiente código es el script de entrada para la [Plantilla de Aplicación web Básica](start-installation.md).

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// registrar el cargador automático de Composer
require __DIR__ . '/../vendor/autoload.php';

// incluir el fichero de clase Yii
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// cargar la configuración de la aplicación
$config = require __DIR__ . '/../config/web.php';

// crear, configurar y ejecutar la aplicación
(new yii\web\Application($config))->run();
```

## Aplicaciones de consola <span id="console-applications"></span>

De la misma manera, el siguiente código es el script de entrada para la [aplicación de consola](tutorial-console.md):

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

// registrar el cargador automático de Composer
require __DIR__ . '/vendor/autoload.php';

// incluir el fichero de clase Yii
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// cargar la configuración de la aplicación
$config = require __DIR__ . '/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```

## Definición de Constantes <span id="defining-constants"></span>

El script de entrada es el mejor lugar para definir constantes globales. Yii soporta las siguientes tres constantes:

* `YII_DEBUG`: especifica si la aplicación se está ejecutando en modo depuración. Cuando esta en modo depuración, una 
aplicación mantendrá más información de registro, y revelará detalladas pilas de errores si se lanza una excepción. Por 
esta razón, el modo depuración debería ser usado principalmente durante el desarrollo. El valor por defecto de 
'YII_DEBUG' es falso.
* `YII_ENV`: especifica en que entorno se esta ejecutando la aplicación. Se puede encontrar una descripción más 
detallada en la sección [Configuraciones](concept-configurations.md#environment-constants).
El Valor por defecto de `YII_ENV` es `'prod'`, que significa que la aplicación se esta ejecutando en el entorno de 
producción.
* `YII_ENABLE_ERROR_HANDLER`: especifica si se habilita el gestor de errores proporcionado por Yii. El valor 
predeterminado de esta constante es verdadero.

Cuando se define una constante, a menudo se usa código como el siguiente:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

que es equivalente al siguiente código:

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

Claramente el primero es más breve y fácil de entender.

La definición de constantes debería hacerse al principio del script de entrada para que pueda tener efecto cuando se 
incluyan otros archivos PHP.
