Extensiones
===========

Las extensiones son paquetes de software redistribuibles diseñados especialmente para ser usados en aplicaciones Yii y
proporcionar características listas para ser usadas. Por ejemplo, la extensión [yiisoft/yii2-debug](tool-debugger.md)
añade una practica barra de herramientas de depuración (debug toolbar) al final de cada página de la aplicación para
ayudar a comprender más fácilmente como se han generado las páginas. Se pueden usar extensiones para acelerar el
proceso de desarrollo. También se puede empaquetar código propio para compartir nuestro trabajo con otra gente.

> Info: Usamos el termino "extensión" para referirnos a los paquetes específicos de software Yii. Para
  propósitos generales los paquetes de software pueden usarse sin Yii, nos referiremos a ellos usando los términos
  "paquetes" (package) o "librerías" (library).

## Uso de Extensiones <span id="using-extensions"></span>

Para usar una extension, primero tenemos que instalarla. La mayoría de extensiones se usan como paquetes
[Composer](https://getcomposer.org/) que se pueden instalar mediante los dos simples siguientes pasos:

1. modificar el archivo `composer.json` de la aplicación y especificar que extensiones (paquetes Composer) se quieren
   instalar.
2. ejecutar `composer install` para instalar las extensiones especificadas.

Hay que tener en cuenta que es necesaria la instalación de [Composer](https://getcomposer.org/) si no la tenemos
instalada.

De forma predeterminada, Composer instala los paquetes registrados en [Packagist](https://packagist.org/) que es el
repositorio más grande de paquetes Composer de código abierto (open source). Se pueden buscar extensiones en
Packagist. También se puede crear un [repositorio propio](https://getcomposer.org/doc/05-repositories.md#repository) y
configurar Composer para que lo use. Esto es práctico cuando se desarrollan extensiones privadas que se quieran
compartir a través de otros proyectos.

Las extensiones instaladas por Composer se almacenan en el directorio `BasePath/vendor`, donde `BasePath` hace
referencia a la [ruta base (base path)](structure-applications.md#basePath) de la aplicación. Ya que Composer es un
gestor de dependencias, cuando se instala un paquete, también se instalarán todos los paquetes de los que dependa.

Por ejemplo, para instalar la extensión `yiisoft/yii2-imagine`, modificamos el archivo `composer.json` como se muestra
a continuación:

```json
{
    // ...

    "require": {
        // ... otras dependencias

        "yiisoft/yii2-imagine": "~2.0.0"
    }
}
```

Después de la instalación, debemos encontrar el directorio `yiisoft/yii2-imagine` dentro del directorio
`BasePath/vendor`. También debemos encontrar el directorio `imagine/imagine` que contiene sus paquetes dependientes
instalados.

> Info: La extensión `yiisoft/yii2-imagine` es una extensión del núcleo (core) desarrollada y mantenida por el
  equipo de desarrollo de Yii. Todas las extensiones del núcleo se hospedan en [Packagist](https://packagist.org/) y
  son nombradas como `yiisoft/yii2-xyz`, donde `zyz` varia según la extensión.

Ahora ya podemos usar las extensiones instaladas como si fueran parte de nuestra aplicación. El siguiente ejemplo
muestra como se puede usar la clase `yii\imagine\Image` proporcionada por la extensión `yiisoft/yii2-imagine`:

```php
use Yii;
use yii\imagine\Image;

// genera una miniatura (thumbnail) de la imagen
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> Info: Las clases de extensiones se cargan automáticamente gracias a
  [autocarga de clases de Yii](concept-autoloading.md).

### Instalación Manual de Extensiones <span id="installing-extensions-manually"></span>

En algunas ocasiones excepcionales es posible que tengamos que instalar alguna o todas las extensiones manualmente, en lugar de utilizar Composer. Para lograrlo, debemos:


1. descargar los archivos de la extensión y descomprimirlos en la carpeta `vendor`.
2. instalar la clase de autocarga proporcionada por las extensiones, si existe.
3. descargar e instalar todas las extensiones dependientes como siguiendo estas mismas instrucciones.

Si una extensión no proporciona clase de autocarga pero sigue el estándar
[PSR-4](https://www.php-fig.org/psr/psr-4/),  se puede usar la clase de autocarga proporcionada por Yii para cargar
automáticamente las clases de las extensiones. Todo lo que se tiene que hacer es declarar un
[alias de raíz (root)](concept-aliases.md#defining-aliases)  para las extensiones del directorio raíz. Por ejemplo,
asumiendo que tenemos instalada una extensión en el directorio `vendor/mycompany/myext`, y las clases de extensión se
encuentran en el namespace `myext`, entonces podemos incluir el siguiente código en nuestra configuración de
aplicación:

```php
[
    'aliases' => [
        '@myext' => '@vendor/mycompany/myext',
    ],
]
```

## Creación de Extensiones <span id="creating-extensions"></span>

Podemos considerar la creación de una extensión cuando tengamos la necesidad de compartir nuestro código. Cada
extensión puede contener el código que se desee, puede ser una clase de ayuda (helper class), un widget, un módulo,
etc.

Se recomienda crear una extensión como [paquetes de Composer](https://getcomposer.org/) para que sea se pueda
instalarse más fácilmente por los otros usuarios, como se ha descrito en la anterior subsección.

Más adelante se encuentran los pasos básicos que deben seguirse para crear una extensión como paquete Composer.

1. Crear un proyecto para la extensión y alojarlo en un repositorio con VCS (Sistema de Control de Versiones), como
   puede ser [github.com](https://github.com). El trabajo de desarrollo y el mantenimiento debe efectuarse en este
   repositorio.
2. En el directorio raíz del repositorio debe encontrarse el archivo `composer.json` que es requerido por Composer. Se
   pueden encontrar más detalles en la siguiente subsección.
3. Registrar la extensión en un repositorio de Composer como puede ser [Packagist](https://packagist.org/), para que
   los otros usuarios puedan encontrarlo e instalarla mediante Composer.

### `composer.json` <span id="composer-json"></span>

Cada paquete de Composer tiene que tener un archivo `composer.json` en su directorio raíz. El archivo contiene los
metadatos relacionados con el paquete. Se pueden encontrar especificaciones completas acerca de este fichero en el
[Manual de Composer](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup). El siguiente ejemplo
muestra el archivo `composer.json` para la extensión `yiisoft/yii2-imagine`:

```json
{
    // nombre del paquete
    "name": "yiisoft/yii2-imagine",

    // tipo de paquete
    "type": "yii2-extension",

    "description": "The Imagine integration for the Yii framework",
    "keywords": ["yii2", "imagine", "image", "helper"],
    "license": "BSD-3-Clause",
    "support": {
        "issues": "https://github.com/yiisoft/yii2/issues?labels=ext%3Aimagine",
        "forum": "https://forum.yiiframework.com/",
        "wiki": "https://www.yiiframework.com/wiki/",
        "irc": "ircs://irc.libera.chat:6697/yii",
        "source": "https://github.com/yiisoft/yii2"
    },
    "authors": [
        {
            "name": "Antonio Ramirez",
            "email": "amigo.cobos@gmail.com"
        }
    ],

    // dependencias del paquete
    "require": {
        "yiisoft/yii2": "~2.0.0",
        "imagine/imagine": "v0.5.0"
    },

    // especificaciones de la autocarga de clases
    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```

#### Nombre del Paquete<span id="package-name"></span>

Cada paquete Composer debe tener un nombre de paquete que identifique de entre todos los otros paquetes. El formato
del nombre del paquete es `nombreProveedor/nombreProyecto`. Por ejemplo, el nombre de paquete `yiisoft/yii2-imagine`,
el nombre del proveedor es `yiisoft` y el nombre del proyecto es `yii2-imagine`.

NO se puede usar el nombre de proveedor `yiisoft` ya que está reservado para el usarse para el código del núcleo
(core) de Yii.

Recomendamos usar el prefijo `yii2-` al nombre del proyecto para paquetes que representen extensiones de Yii 2, por
ejemplo, `minombre/yii2-miwidget`. Esto permite ver a los usuarios más fácilmente si un paquete es una extensión de
Yii 2.

#### Tipo de Paquete <span id="package-type"></span>

Es importante que se especifique el tipo del paquete de la extensión como `yii2-extension` para que el paquete pueda
ser reconocido como una extensión de Yii cuando se esté instalando.

Cuando un usuario ejecuta `composer install` para instalar una extensión, el archivo `vendor/yiisoft/extensions.php`
se actualizará automáticamente para incluir la información acerca de la nueva extensión. Desde este archivo, las
aplicaciones Yii pueden saber que extensiones están instaladas. (se puede acceder a esta información mediante
[[yii\base\Application::extensions]]).

#### Dependencias <span id="dependencies"></span>

La extensión depende de Yii (por supuesto). Por ello se debe añadir (`yiisoft/yii2`) a la lista en la entrada
`required` del archivo `composer.json`. Si la extensión también depende de otras extensiones o de terceras
(third-party) librerías, también se deberán listar. Debemos asegurarnos de anotar las restricciones de versión
apropiadas (ej. `1.*`, `@stable`) para cada paquete dependiente. Se deben usar dependencias estables en versiones
estables de nuestras extensiones.

La mayoría de paquetes JavaScript/CSS se gestionan usando [Bower](https://bower.io/) y/o [NPM](https://www.npmjs.com/),
en lugar de Composer. Yii utiliza el [Composer asset plugin](https://github.com/fxpio/composer-asset-plugin)
 para habilitar la gestión de estos tipos de paquetes a través de Composer. Si la extensión depende de un paquete
 Bower, se puede, simplemente, añadir la dependencia de el archivo `composer.json` como se muestra a continuación:

```json
{
    // dependencias del paquete
    "require": {
        "bower-asset/jquery": ">=1.11.*"
    }
}
```

El código anterior declara que tela extensión depende del paquete Bower `jquery`. En general, se puede usar
`bower-asset/NombrePaquete` para referirse al paquete en `composer.json`, y usar `npm-asset/NombrePaquete` para
referirse a paquetes NPM. Cuando Composer instala un paquete Bower o NPM, de forma predeterminada los contenidos de
los paquetes se instalarán en `@vendor/bower/NombrePaquete` y `@vendor/npm/Packages` respectivamente. Podemos hacer
referencia a estos dos directorios usando los alias `@bower/NombrePaquete` and `@npm/NombrePaquete`.

Para obtener más detalles acerca de la gestión de assets, puede hacerse referencia a la sección
[Assets](structure-assets.md#bower-npm-assets).

#### Autocarga de Clases <span id="class-autoloading"></span>

Para que se aplique la autocarga a clases propias mediante la autocarga de clases de Yii o la autocarga de clases de
Composer, debemos especificar la entrada `autoload` en el archivo `composer.json` como se puede ver a continuación:

```json
{
    // ....

    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```

Se pueden añadir una o más namespaces raíz y sus correspondientes rutas de archivo.

Cuando se instala la extensión en una aplicación, Yii creara un [alias](concept-aliases.md#extension-aliases) para
todos los namespaces raíz, que harán referencia al directorio correspondiente del namespace. Por ejemplo, la anterior
declaración `autoload` corresponderá a un alias llamado `@yii/imagine`.

### Prácticas Recomendadas <span id="recommended-practices"></span>

Dado que las extensiones están destinadas a ser utilizadas por otras personas, a menudo es necesario hacer un esfuerzo
extra durante el desarrollo. A continuación presentaremos algunas practicas comunes y recomendadas para la creación de
extensiones de alta calidad.

#### Namespaces <span id="namespaces"></span>

Para evitar colisiones de nombres y permitir que las clases usen la autocarga en extensiones propias, se deben usar
namespaces y nombres de clase siguiendo el [estándar PSR-4](https://www.php-fig.org/psr/psr-4/) o el
[estándar PSR-0](https://www.php-fig.org/psr/psr-0/).

Los namespaces de clases propias deben empezar por `NombreProveedor\NombreExtension` donde `NombreExtension` es
similar al nombre del paquete pero este no debe contener el prefijo `yii2-`. Por ejemplo, para la extensión
`yiisoft/yii2-imagine`, usamos `yii\imagine` como namespace para sus clases.

No se puede usar `yii`, `yii2` o `yiisoft` como nombre de proveedor. Estos nombres están reservados para usarse en el
código del núcleo de Yii.

#### Clases de Bootstrapping <span id="bootstrapping-classes"></span>

A veces, se puede querer que nuestras extensiones ejecuten algo de código durante el
[proceso de bootstrapping](runtime-bootstrapping.md) de una aplicación. Por ejemplo, queremos que nuestra extensión
responda a un evento `beginRequest` de la aplicación para ajustar alguna configuración de entorno. Aunque podemos
indicar a los usuarios de la extensión que añadan nuestro gestor de eventos para que capture `beginRequest`, es mejor
hacerlo automáticamente.

Para llevarlo a cabo, podemos crear una *clase de bootstrpping* para implementar [[yii\base\BootstrapInterface]]. Por
ejemplo,

```php
namespace myname\mywidget;

use yii\base\BootstrapInterface;
use yii\base\Application;

class MyBootstrapClass implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
             // do something here
        });
    }
}
```

Entonces se tiene que añadir esta clase en la lista del archivo `composer.json` de la extensión propia como se muestra
a continuación,

```json
{
    // ...

    "extra": {
        "bootstrap": "myname\\mywidget\\MyBootstrapClass"
    }
}
```

Cuando se instala la extensión en la aplicación, Yii automáticamente instancia la clase de bootstrapping y llama a su
método [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] durante el proceso de bootstrapping para cada petición.

#### Trabajar con Bases de Datos<span id="working-with-databases"></span>

Puede darse el caso en que la extensión necesite acceso a bases de datos. No se debe asumir que las aplicaciones que
usen la extensión siempre usarán `Yii::$db` como conexión de BBDD. Se debe crear una propiedad `db` para las clases
que requieran acceso a BBDD. La propiedad permitirá a los usuarios de nuestra extensión elegir que conexión quieren
que use nuestra extensión. Como ejemplo, se puede hacer referencia a la clase [[yii\caching\DbCache]] y observar como
declara y utiliza la propiedad `db`.

Si nuestra extensión necesita crear tablas especificas en la BBDD o hacer cambios en el esquema de la BBDD, debemos:

- proporcionar [migraciones](db-migrations.md) para manipular el esquema de la BBDD, en lugar de utilizar archivos con
  sentencias SQL;
- intentar hacer las migraciones aplicables a varios Sistemas de Gestión de BBDD;
- evitar usar [Active Record](db-active-record.md) en las migraciones.

#### Uso de Assets <span id="using-assets"></span>

Si nuestra aplicación es un widget o un módulo, hay posibilidades de que requiera [assets](structure-assets.md) para
poder funcionar. Por ejemplo, un modulo puede mostrar algunas páginas de que contengan archivos JavaScript y/o CSS.
Debido a que los archivos de las extensiones se encuentran en la misma ubicación y no son accesibles por la Web cuando
se instalan en una aplicación, hay dos maneras de hacer los assets accesibles vía Web:

- pedir a los usuarios que copien manualmente los archivos assets en un directorio público de la Web.
- declarar un [asset bundle](structure-assets.md) dejar que el mecanismo de publicación se encargue automáticamente de
  copiar los archivos que se encuentren en el asset bundle a un directorio Web público.

Recomendamos el uso de la segunda propuesta para que la extensión sea más fácil de usar para usuarios. Se puede hacer
referencia a la sección [Assets](structure-assets.md) para encontrar más detalles acerca de como trabajar con ellos.

#### Internacionalización y Localización <span id="i18n-l10n"></span>

Puede que las extensiones propias se usen en aplicaciones que den soporte a diferentes idiomas! Por ello, si nuestra
extensión muestra contenido a los usuarios finales, se debe intentar [internacionalizar y localizar](tutorial-i18n.md)
la extensión. En particular,

- Si la extensión muestra mensajes destinados a usuarios finales, los mensajes deben mostrarse usando `Yii::t()` para
  que puedan ser traducidos. Los mensajes dirigidos a desarrolladores (como mensajes de excepciones internas) no
  necesitan ser traducidos.
- Si la extensión muestra números, fechas, etc., deben ser formateados usando [[yii\i18n\Formatter]] siguiendo las
  reglas de formato adecuadas.

Se pueden encontrar más detalles en la sección [internacionalización](tutorial-i18n.md).

#### Testing <span id="testing"></span>

Para conseguir que las aplicaciones propias se ejecuten sin problemas y no causen problemas a otros usuarios, deben
ejecutarse test a las extensiones antes de ser publicadas al público.

Se recomienda crear varios casos de prueba (test cases) para probar el código de nuestra extensión en lugar de
ejecutar pruebas manuales. Cada vez que se vaya a lanzar una nueva versión, simplemente podemos ejecutar estos casos
de prueba para asegurarnos de que todo está correcto. Yii proporciona soporte para testing que puede ayudar a escribir
pruebas unitarias (unit tests), pruebas de aceptación (acceptance tests) y pruebas de funcionalidad
(functionality tests), más fácilmente. Se pueden encontrar más detalles en la sección [Testing](test-overview.md).

#### Versiones <span id="versioning"></span>

Se debe asignar un número de versión cada vez que se lance una nueva distribución. (ej. `1.0.1`). Recomendamos
seguir la práctica [Versionamiento Semántico](https://semver.org/lang/es/) para determinar que números se deben usar.

#### Lanzamientos <span id="releasing"></span>

Para dar a conocer nuestra extensión a terceras personas, debemos lanzara al público.

Si es la primera vez que se realiza un lanzamiento de una extensión, debemos registrarla en un repositorio Composer
como puede ser [Packagist](https://packagist.org/). Después de estos, todo lo que tenemos que hacer es crear una
etiqueta (tag) (ej. `v1.0.1`) en un repositorio con VCS (Sistema de Control de Versiones) y notificarle al
repositorio Composer el nuevo lanzamiento. Entonces la gente podrá encontrar el nuevo lanzamiento y instalar o
actualizar la extensión a mediante el repositorio Composer.

En los lanzamientos de una extensión, además de archivos de código, también se debe considerar la inclusión los puntos
mencionados a continuación para facilitar a otra gente el uso de nuestra extensión:

* Un archivo léame (readme) en el directorio raíz: describe que hace la extensión y como instalarla y utilizarla.
  Recomendamos que se escriba en formato [Markdown](https://daringfireball.net/projects/markdown/) y llamarlo
  `readme.md`.
* Un archivo de registro de cambios (changelog) en el directorio raíz: enumera que cambios se realizan en cada
  lanzamiento. El archivo puede escribirse en formato Markdown y llamarlo `changelog.md`.
* Un archivo de actualización (upgrade) en el directorio raíz: da instrucciones de como actualizar desde lanzamientos
  antiguos de la extensión. El archivo puede escribirse en formato Markdown y llamarlo `upgrade.md`.
* Tutoriales, demostraciones, capturas de pantalla, etc: son necesarios si nuestra extensión proporciona muchas
  características que no pueden ser detalladas completamente en el archivo `readme`.
* Documentación de API: el código debe documentarse debidamente para que otras personas puedan leerlo y entenderlo
  fácilmente. Más información acerca de documentación de código en
  [archivo de Objetos de clase](https://github.com/yiisoft/yii2/blob/master/framework/base/BaseObject.php)

> Info: Los comentarios de código pueden ser escritos en formato Markdown. La extensión `yiisoft/yii2-apidoc`
  proporciona una herramienta para generar buena documentación de API basándose en los comentarios del código.

> Info: Aunque no es un requerimiento, se recomienda que la extensión se adhiera a ciertos estilos de
  codificación. Se puede hacer referencia a
  [estilo de código del núcleo del framework](https://github.com/yiisoft/yii2/blob/master/docs/internals/core-code-style.md) para
  obtener más detalles.

## Extensiones del Núcleo <span id="core-extensions"></span>

Yii proporciona las siguientes extensiones del núcleo que son desarrolladas y mantenidas por el equipo de desarrollo
de Yii. Todas ellas están registradas en [Packagist](https://packagist.org/) y pueden ser instaladas fácilmente como
se describe en la subsección [Uso de Extensiones](#using-extensions)

- [yiisoft/yii2-apidoc](https://github.com/yiisoft/yii2-apidoc):proporciona un generador de documentación de APIs
  extensible y de de alto rendimiento.
- [yiisoft/yii2-authclient](https://github.com/yiisoft/yii2-authclient):proporciona un conjunto de clientes de
  autorización tales como el cliente OAuth2 de Facebook, el cliente GitHub OAuth2.
- [yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2-bootstrap): proporciona un conjunto de widgets que
  encapsulan los componentes y plugins de [Bootstrap](https://getbootstrap.com/).
- [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug): proporciona soporte de depuración para aplicaciones
  Yii. Cuando se usa esta extensión, aparece una barra de herramientas de depuración en la parte inferior de cada
  página. La extensión también proporciona un conjunto de páginas para mostrar información detallada de depuración.
- [yiisoft/yii2-elasticsearch](https://github.com/yiisoft/yii2-elasticsearch): proporciona soporte para usar
  [Elasticsearch](https://www.elastic.co/). Incluye soporte básico para realizar consultas/búsquedas y también
  implementa patrones de [Active Record](db-active-record.md) que permiten y permite guardar los `active records` en
  Elasticsearch.
- [yiisoft/yii2-faker](https://github.com/yiisoft/yii2-faker): proporciona soporte para usar
  [Faker](https://github.com/fzaninotto/Faker) y generar datos automáticamente.
- [yiisoft/yii2-gii](https://github.com/yiisoft/yii2-gii): proporciona un generador de código basado den Web altamente
  extensible y que puede usarse para generar modelos, formularios, módulos, CRUD, etc. rápidamente.
- [yiisoft/yii2-httpclient](https://github.com/yiisoft/yii2-httpclient):
  provides an HTTP client.
- [yiisoft/yii2-imagine](https://github.com/yiisoft/yii2-imagine): proporciona funciones comunes de manipulación de
  imágenes basadas en [Imagine](https://imagine.readthedocs.org/).
- [yiisoft/yii2-jui](https://github.com/yiisoft/yii2-jui): proporciona un conjunto de widgets que encapsulan las
  iteraciones y widgets de [JQuery UI](https://jqueryui.com/).
- [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb): proporciona soporte para utilizar
  [MongoDB](https://www.mongodb.com/). incluye características como consultas básicas, Active Record, migraciones,
  caching, generación de código, etc.
- [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis): proporciona soporte para utilizar
  [redis](https://redis.io/). incluye características como consultas básicas, Active Record, caching, etc.
- [yiisoft/yii2-smarty](https://github.com/yiisoft/yii2-smarty): proporciona un motor de plantillas basado en
  [Smarty](https://www.smarty.net/).
- [yiisoft/yii2-sphinx](https://github.com/yiisoft/yii2-sphinx): proporciona soporte para utilizar
  [Sphinx](https://sphinxsearch.com). incluye características como consultas básicas, Active Record, code generation,
  etc.
- [yiisoft/yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer): proporciona características de envío de
  correos electrónicos basadas en [swiftmailer](https://swiftmailer.symfony.com/).
- [yiisoft/yii2-twig](https://github.com/yiisoft/yii2-twig): proporciona un motor de plantillas basado en
  [Twig](https://twig.symfony.com/).
