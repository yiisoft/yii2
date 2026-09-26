Assets
======

Un asset en Yii es un archivo al que se puede hacer referencia en una página Web. Puede ser un archivo CSS, un archivo
JavaScript, una imagen o un archivo de video, etc. Los assets se encuentran en los directorios públicos de la web y se
sirven directamente por los servidores Web.

A menudo es preferible gestionar los assets mediante programación. Por ejemplo, cuando se usa el widget
[[yii\jui\DatePicker]] en una página, éste incluirá automáticamente los archivos CSS y JavaScript requeridos, en vez
de tener que buscar los archivos e incluirlos manualmente. Y cuando se actualice el widget a una nueva versión, ésta
usará de forma automática la nueva versión de los archivos asset.
En este tutorial, se describirá la poderosa capacidad que proporciona la gestión de assets en Yii.

## Asset Bundles <span id="asset-bundles"></span>

Yii gestiona los assets en unidades de *asset bundle*. Un asset bundle es simplemente un conjunto de assets
localizados en un directorio. Cuando se registra un asset bundle en una [vista](structure-views.md), éste incluirá los
archivos CSS y JavaScript del bundle en la página Web renderizada.

## Definición de Asset Bundles <span id="defining-asset-bundles"></span>

Los asset bundles son descritos como clases PHP que extienden a [[yii\web\AssetBundle]]. El nombre del bundle es
simplemente su correspondiente nombre de la classe PHP que debe ser [autocargable](concept-autoloading.md). En una
clase asset bundle, lo más habitual es especificar donde se encuentran los archivos asset, que archivos CSS y
JavaScript contiene el bundle, y como depende este bundle de otros bundles.

El siguiente código define el asset bundle principal que se usa en
[la plantilla de aplicación básica](start-installation.md):

```php
<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

La anterior clase `AppAsset` especifica que los archivos asset se encuentran en el directorio `@webroot` que
corresponde a la URL `@web`; el bundle contiene un único archivo CSS `css/site.css` y ningún archivo JavaScript;
el bundle depende de otros dos bundles: [[yii\web\YiiAsset]] y [[yii\bootstrap\BootstrapAsset]].
A continuación se explicarán más detalladamente las propiedades del [[yii\web\AssetBundle]]:

* [[yii\web\AssetBundle::sourcePath|sourcePath]]: especifica el directorio raíz que contiene los archivos asset en el
  bundle.  Si no, se deben especificar las propiedades [[yii\web\AssetBundle::basePath|basePath]] y
  [[yii\web\AssetBundle::baseUrl|baseUrl]], en su lugar. Se pueden usar [alias de ruta](concept-aliases.md).
* [[yii\web\AssetBundle::basePath|basePath]]: especifica el directorio Web público que contiene los archivos assets de
  este bundle. Cuando se especifica la propiedad [[yii\web\AssetBundle::sourcePath|sourcePath]], el [gestor de
  assets](#asset-manager) publicará los assets de este bundle en un directorio  Web público  y sobrescribirá la
  propiedad en consecuencia. Se debe establecer esta propiedad si los archivos asset ya se encuentran en un directorio
  Web público y no necesitan ser publicados. Se pueden usar [alias de ruta](concept-aliases.md).
* [[yii\web\AssetBundle::baseUrl|baseUrl]]: especifica la URL correspondiente al directorio
  [[yii\web\AssetBundle::basePath|basePath]]. Como en [[yii\web\AssetBundle::basePath|basePath]], si se especifica la
  propiedad [[yii\web\AssetBundle::sourcePath|sourcePath]], el [gestor de assets](#asset-manager) publicara los assets
  y sobrescribirá esta propiedad en consecuencia. Se pueden usar [alias de ruta](concept-aliases.md).
* [[yii\web\AssetBundle::js|js]]: un array lista los archivos JavaScript que contiene este bundle. Tenga en cuenta que
  solo deben usarse las barras invertidas "/" como separadores de directorios. Cada archivo Javascript se puede
  especificar en uno de los siguientes formatos:
    - una ruta relativa que represente un archivo local JavaScript (ej. `js/main.js`). La ruta actual del fichero
      se puede determinar anteponiendo [[yii\web\AssetManager::basePath]] a la ruta relativa, y la URL actual de un
      archivo puede ser determinada anteponiendo [[yii\web\AssetManager::baseUrl]] a la ruta relativa.
    - una URL absoluta que represente un archivo JavaScript externo. Por ejemplo,
    `https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` o
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
* [[yii\web\AssetBundle::css|css]]: un array que lista los archivos CSS que contiene este bundle. El formato de este
  array es el mismo que el de [[yii\web\AssetBundle::js|js]].
* [[yii\web\AssetBundle::depends|depends]]: un array que lista los nombres de los asset bundles de los que depende este
  asset bundle (para explicarlo brevemente).
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: especifica las opciones que se enviarán al método
  [[yii\web\View::registerJsFile()]] cuando se le llame para registrar *todos* los archivos JavaScript de este bundle.
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: especifica las opciones que se enviarán al método
  [[yii\web\View::registerCssFile()]] cuando se le llame para registrar *todos* los archivos CSS de este bundle.
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: especifica las opciones que se enviarán al método
  [[yii\web\AssetManager::publish()]] cuando se le llame para publicar los archivos de los assets fuente a un
  directorio Web.
Solo se usa si se especifica la propiedad [[yii\web\AssetBundle::sourcePath|sourcePath]].

### Ubicación de los Assets <span id="asset-locations"></span>

Según la localización de los assets, se pueden clasificar como:

* assets fuente (source assets): los assets se encuentran junto con el código fuente PHP, al que no se puede acceder
  directamente a través de la Web. Para usar los assets fuente en una página, deben ser copiados en un directorio
  público y transformados en los llamados assets publicados. El proceso se llama *publicación de assets* que será
  descrito a continuación.
* assets publicados (published assets): los archivos assets se encuentran en el directorio Web y son accesibles vía Web.
* assets externos (external assets): los archivos assets se encuentran en un servidor Web diferente al de la aplicación.

Cuando se define una clase asset bundle, si se especifica la propiedad [[yii\web\AssetBundle::sourcePath|sourcePath]],
significa que cualquier asset listado que use rutas relativas será considerado como un asset fuente. Si no se
especifica la propiedad, significa que los assets son assets publicados (se deben especificar
[[yii\web\AssetBundle::basePath|basePath]] y
[[yii\web\AssetBundle::baseUrl|baseUrl]] para hacerle saber a Yii dónde se encuentran.)

Se recomienda ubicar los assets que correspondan a la aplicación en un directorio Web para evitar publicaciones de
assets innecesarias. Por esto en el anterior ejemplo `AppAsset` especifica [[yii\web\AssetBundle::basePath|basePath]]
en vez de [[yii\web\AssetBundle::sourcePath|sourcePath]].

Para las [extensiones](structure-extensions.md), por el hecho de que sus assets se encuentran junto con el código
fuente, en directorios que no son accesibles para la Web, se tiene que especificar la propiedad
[[yii\web\AssetBundle::sourcePath|sourcePath]] cuando se definan clases asset bundle para ellas.

> Note: No se debe usar `@webroot/assets` como [[yii\web\AssetBundle::sourcePath|source path]]. Este directorio se usa
  por defecto por el [[yii\web\AssetManager|asset manager]] para guardar los archivos asset publicados temporalmente y
  pueden ser eliminados.

### Dependencias de los Asset <span id="asset-dependencies"></span>

Cuando se incluyen múltiples archivos CSS o JavaScript en una página Web, tienen que cumplir ciertas órdenes para
evitar problemas de sobrescritura. Por ejemplo, si se usa un widget jQuery UI en una página Web, tenemos que
asegurarnos de que el archivo JavaScript jQuery se incluya antes que el archivo JavaScript jQuery UI. A esto se le
llama ordenar las dependencias entre archivos.

Las dependencias de los assets se especifican principalmente a través de la propiedad [[yii\AssetBundle::depends]].
En el ejemplo `AppAsset`, el asset bundle depende de otros dos asset bundles [[yii\web\YiiAsset]] y
[[yii\bootstrap\BootstrapAsset]], que significa que los archivos CSS y JavaScript en `AppAsset` se incluirán *después*
que los archivos de los dos bundles dependientes.

Las dependencias son transitivas. Esto significa, que si un bundle A depende de un bundle B que depende de C, A
dependerá de C, también.

### Opciones de los Assets <span id="asset-options"></span>

Se pueden especificar las propiedades [[yii\web\AssetBundle::cssOptions|cssOptions]] y
[[yii\web\AssetBundle::jsOptions|jsOptions]] para personalizar la forma en que los archivos CSS y JavaScript serán
incluidos en una página. Los valores de estas propiedades serán enviadas a los métodos
[[yii\web\View::registerCssFile()]] y [[yii\web\View::registerJsFile()]], respectivamente cuando las
[vistas](structure-views.md) los llamen para incluir los archivos CSS y JavaScript.

> Note: Las opciones que se especifican en una clase bundle se aplican a *todos* los archivos CSS/JavaScript de un
  bundle. Si se quiere usar diferentes opciones para diferentes archivos, se deben crear assets bundles separados y
  usar un conjunto de opciones para cada bundle.

Por ejemplo, para incluir una archivo CSS condicionalmente para navegadores que como IE9 o anteriores, se puede usar la
 siguiente opción:

```php
public $cssOptions = ['condition' => 'lte IE9'];
```

Esto provoca que un archivo CSS dentro de un bundle sea incluido usando los siguientes tags HTML:

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

Para envolver el tag del enlace con `<noscript>` se puede usar el siguiente código:

```php
public $cssOptions = ['noscript' => true];
```

Para incluir un archivo JavaScript en la sección cabecera (head) de una página (por defecto, los archivos JavaScript se
 incluyen al final de la sección cuerpo(body)), se puede usar el siguiente código:

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```

Por defecto, cuando un asset bundle está siendo publicado, todos los contenidos del directorio especificado por [[yii\web\AssetBundle::sourcePath]]
serán publicados. Puedes personalizar este comportamiento configurando la propiedad [[yii\web\AssetBundle::publishOptions|publishOptions]]. Por
ejemplo, públicar solo uno o unos pocos subdirectorios de [[yii\web\AssetBundle::sourcePath]], puedes hacerlo de la siguiente manera en la clase
asset bundle:

```php
<?php
namespace app\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@bower/font-awesome';
    public $css = [
        'css/font-awesome.min.css',
    ];

    public function init()
    {
        parent::init();
        $this->publishOptions['beforeCopy'] = function ($from, $to) {
            $dirname = basename(dirname($from));
            return $dirname === 'fonts' || $dirname === 'css';
        };
    }
}
```

El ejemplo anterior define un asset bundle para el ["fontawesome" package](https://fontawesome.com/). Especificando
la opción de publicación `beforeCopy`, solo los subdirectorios `fonts` y `css` serán publicados.

### Bower y NPM Assets <span id="bower-npm-assets"></span>

La mayoría de paquetes JavaScript/CSS se gestionan con [Bower](https://bower.io/) y/o [NPM](https://www.npmjs.com/).
Si tu aplicación o extensión usa estos paquetes, se recomienda seguir los siguientes pasos para gestionar los assets en
 la librería:

1. Modificar el archivo `composer.json` de tu aplicación o extensión e introducir el paquete en la lista `require`.
   Se debe usar `bower-asset/PackageName` (para paquetes Bower) o `npm-asset/PackageName` (para paquetes NPM) para
   referenciar la librería.
2. Crear una clase asset bundle y listar los archivos JavaScript/CSS que se planea usar en la aplicación o extensión.
   Se debe especificar la propiedad [[yii\web\AssetBundle::sourcePath|sourcePath]] como `@bower\PackageName` o
   `@npm\PackageName`. Esto se debe a que Composer instalará el paquete Bower o NPM en el correspondiente directorio de
    este alias.

> Note: Algunos paquetes pueden distribuir sus archivos en subdirectorios. Si es el caso, se debe especificar el
  subdirectorio como valor del [[yii\web\AssetBundle::sourcePath|sourcePath]]. Por ejemplo, [[yii\web\JqueryAsset]]
  usa `@bower/jquery/dist` en vez de `@bower/jquery`.

## Uso de Asset Bundles <span id="using-asset-bundles"></span>

Para usar un asset bundle, debe registrarse con una [vista](structure-views.md) llamando al método
[[yii\web\AssetBundle::register()]]. Por ejemplo, en plantilla de vista se puede registrar un asset bundle como en el
siguiente ejemplo:

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this representa el objeto vista
```

> Info: El método [[yii\web\AssetBundle::register()]] devuelve un objeto asset bundle que contiene la
  información acerca de los assets publicados, tales como [[yii\web\AssetBundle::basePath|basePath]] o
  [[yii\web\AssetBundle::baseUrl|baseUrl]].

Si se registra un asset bundle en otro lugar, se debe proporcionar la vista necesaria al objeto. Por ejemplo, para
registrar un asset bundle en una clase [widget](structure-widgets.md), se puede obtener el objeto vista mediante
`$this->view`.

Cuando se registra un asset bundle con una vista, por detrás, Yii registrará todos sus asset bundles dependientes.
Y si un asset bundle se encuentra en un directorio inaccesible por la Web, éste será publicado a un directorio Web
público. Después cuando la vista renderice una página, se generarán las etiquetas (tags) `<link>` y `<script>`  para
los archivos CSS y JavaScript listados en los bundles registrados. El orden de estas etiquetas será determinado por
las dependencias entre los bundles registrados y los otros assets listados en las propiedades
[[yii\web\AssetBundle::css]] y [[yii\web\AssetBundle::js]].

### Personalización de Asset Bundles <span id="customizing-asset-bundles"></span>

Yii gestiona los asset bundles a través de un componente de aplicación llamado `assetManager` que está implementado
por [[yii\web\AssetManager]]. Configurando la propiedad [[yii\web\AssetManager::bundles]], se puede personalizar el
comportamiento (behavior) de un asset bundle. Por ejemplo, de forma predeterminada, el asset bundle [[yii\web\Jquery]]
, utiliza el archivo `jquery.js` desde el paquete Bower instalado. Para mejorar la disponibilidad y el rendimiento se
puede querer usar la versión alojada por Google. Ésta puede ser obtenida configurando `assetManager` en la
configuración de la aplicación como en el siguiente ejemplo:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // no publicar el bundle
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
    ],
];
```

Del mismo modo, se pueden configurar múltiples asset bundles a través de [[yii\web\AssetManager::bundles]]. Las claves
del array deben ser los nombres de clase (sin la primera barra invertida) de los asset bundles, y los valores del array
 deben ser las correspondientes [configuraciones de arrays](concept-configurations.md).

> Tip: Se puede elegir condicionalmente que assets se van a usar en un asset bundle. El siguiente ejemplo
muestra como usar `jquery.js` en el entorno de desarrollo y `jquery.min.js` en los otros casos:
>
> ```php
> 'yii\web\JqueryAsset' => [
>     'js' => [
>         YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
>     ]
> ],
> ```

Se puede deshabilitar uno o más asset bundles asociando `false` a los nombres de los asset bundles que se quieran
deshabilitar. Cuando se registra un asset bundle deshabilitado con una vista, ninguno de sus bundles dependientes será
registrado, y la vista tampoco incluirá ningún asset del bundle en la página que se renderice.
Por ejemplo, para deshabilitar [[yii\web\JqueryAsset]], se puede usar la siguiente configuración:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => false,
            ],
        ],
    ],
];
```

Además se pueden deshabilitar *todos* los asset bundles asignando `false` a [[yii\web\AssetManager::bundles]].

### Mapeo de Assets (Asset Mapping) <span id="asset-mapping"></span>

A veces se puede querer "arreglar" rutas de archivos incorrectos/incompatibles usadas en múltiples asset bundles.
Por ejemplo, el bundle A usa `jquery.min.js` con versión 1.11.1, y el bundle B usa `jquery.js` con versión 2.11.1.
Mientras que se puede solucionar el problema personalizando cada bundle, una forma más fácil, es usar la
característica *asset map* para mapear los assets incorrectos a los deseados. Para hacerlo, se tiene que configurar la
propiedad [[yii\web\AssetManager::assetMap]] como en el siguiente ejemplo:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'assetMap' => [
                'jquery.js' => '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ],
        ],
    ],
];
```

Las claves de [[yii\web\AssetManager::assetMap|assetmMap]] son los nombres de los assets que se quieren corregir,
y los valores son las rutas de los assets deseados. Cuando se registra un asset bundle con una vista, cada archivo de
asset relativo de [[yii\web\AssetBundle::css|css]] y [[yii\web\AssetBundle::js|js]] serán contrastados con este mapa.
Si se detecta que alguna de estas claves es la última parte de un archivo asset (prefijado con
[[yii\web\AssetBundle::sourcePath]], si esta disponible), el correspondiente valor reemplazará el asset y será
registrado con la vista.
Por ejemplo, un archivo asset `mi/ruta/a/jquery.js` concuerda con la clave `jquery.js`.

> Note: Sólo los assets especificados usando rutas relativas están sujetos al mapeo de assets. Y las rutas de los
assets destino deben ser tanto URLs absolutas o rutas relativas a [[yii\web\AssetManager::basePath]].

### Publicación de Asset <span id="asset-publishing"></span>

Como se ha comentado anteriormente, si un asset bundle se encuentra en un directorio que no es accesible por la Web,
este asset será copiado a un directorio Web cuando se registre el bundle con una vista. Este proceso se llama
*publicación de assets*, y se efectúa automáticamente por el [[yii\web\AssetManager|asset manager]].

De forma predeterminada, los assets se publican en el directorio `@webroot/assets` cuando corresponden a la URL
`@web\assets`. Se puede personalizar esta ubicación configurando las propiedades
[[yii\web\AssetManager::basePath|basePath]] y [[yii\web\AssetManager::baseUrl|baseUrl]].

En lugar de publicar los assets copiando archivos, se puede considerar usar enlaces simbólicos, si tu
SO (sistema operativo) y servidor Web lo permiten. Esta característica se puede habilitar estableciendo el valor de
[[yii\web\AssetManager::linkAssets|linkAssets]] en `true`.

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
        ],
    ],
];
```

Con la anterior configuración, el gestor de assets creará un enlace simbólico a la ruta de origen del asset bundle
cuando éste sea publicado. Esto es más rápido que copiar archivos y también asegura que siempre estén actualizados.

## Los Asset Bundles más Comunes <span id="common-asset-bundles"></span>

El código del núcleo de Yii tiene definidos varios asset bundles. Entre ellos, los siguientes bundles son los más
usados y pueden referenciarse en códigos de aplicaciones o extensiones.

- [[yii\web\YiiAsset]]: Principalmente incluye el archivo `yii.js` que implementa un mecanismo de organización de
  código JavaScript en los módulos. También proporciona soporte especial para los atributos `data-method` y
  `data-confirm` y otras característica útiles.
- [[yii\web\JqueryAsset]]: Incluye el archivo `jquery.js` desde el paquete Bower jQuery.
- [[yii\bootstrap\BootstrapAsset]]: Incluye el archivo CSS desde el framework Twitter Bootstrap.
- [[yii\bootstrap\BootstrapPluginAsset]]: Incluye el archivo JavaScript desde el framework Twitter Bootstrap para dar
  soporte a los plugins JavaScript de Bootstrap.
- [[yii\jui\JuiAsset]]: Incluye los archivos CSS y JavaScript desde la librería jQuery UI.

Si el código depende de jQuery, jQuery UI o Bootstrap, se pueden usar estos asset bundles predefinidos en lugar de
crear versiones propias. Si la configuración predeterminada de estos bundles no satisface las necesidades, se puede
personalizar como se describe en la subsección [Personalización de Asset Bundles](#customizing-asset-bundles).

## Conversión de Assets <span id="asset-conversion"></span>

En lugar de escribir código CSS y/o JavaScript directamente, los desarrolladores a menudo escriben código usando una
sintaxis extendida y usan herramientas especiales para convertirlos en CSS/JavaScript. Por ejemplo, para código CSS se
puede usar [LESS](https://lesscss.org) o [SCSS](https://sass-lang.com/); y para JavaScript se puede usar
[TypeScript](https://www.typescriptlang.org/).

Se pueden listar los archivos asset con sintaxis extendida (extended syntax) en [[yii\web\AssetBundle::css|css]] y
[[yii\web\AssetBundle::js|js]] en un asset bundle. Por ejemplo:

```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.less',
    ];
    public $js = [
        'js/site.ts',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

Cuando se registra uno de estos asset bundles en una vista, el [[yii\web\AssetManager|asset manager]] ejecutará
automáticamente las herramientas pre-procesadoras para convertir los assets de sintaxis extendidas reconocidas en
CSS/JavaScript. Cuando la vista renderice finalmente una página, se incluirán los archivos CSS/JavaScript
en la página, en lugar de los assets originales en sintaxis extendidas.

Yii usa las extensiones de archivo para identificar que sintaxis extendida se está usando. De forma predeterminada se
reconocen las siguientes sintaxis y extensiones de archivo.

- [LESS](https://lesscss.org/): `.less`
- [SCSS](https://sass-lang.com/): `.scss`
- [Stylus](https://stylus-lang.com/): `.styl`
- [CoffeeScript](https://coffeescript.org/): `.coffee`
- [TypeScript](https://www.typescriptlang.org/): `.ts`

Yii se basa en las herramientas pre-procesadoras instalada para convertir los assets. Por ejemplo, para usar
[LESS](https://lesscss.org/) se debe instalar el comando pre-procesador `lessc`.

Se pueden personalizar los comandos de los pre-procesadores y las sintaxis extendidas soportadas configurando
[[yii\web\AssetManager::converter]] como en el siguiente ejemplo:

```php
return [
    'components' => [
        'assetManager' => [
            'converter' => [
                'class' => 'yii\web\AssetConverter',
                'commands' => [
                    'less' => ['css', 'lessc {from} {to} --no-color'],
                    'ts' => ['js', 'tsc --out {to} {from}'],
                ],
            ],
        ],
    ],
];
```

En el anterior ejemplo se especifican las sintaxis extendidas soportadas a través de la propiedad
[[yii\web\AssetConverter::commands]]. Las claves del array son los nombres de extensión de archivo (sin el punto), y
los valores del array las extensiones de archivo resultantes y los comandos para realizar la conversión de assets.
Los tokens `{from}` y `{to}` en los comandos se reemplazarán por las rutas de origen de los archivos asset y las rutas
de destino de los archivos asset.

> Info: Hay otras maneras de trabajar con las assets de sintaxis extendidas, además de la descrita
  anteriormente. Por ejemplo, se pueden usar herramientas generadoras tales como [grunt](https://gruntjs.com/) para
  monitorear y convertir automáticamente los assets de sintaxis extendidas. En este caso, se deben listar los archivos
  CSS/JavaScript resultantes en lugar de los archivos de originales.

## Combinación y Compresión de Assets <span id="combining-compressing-assets"></span>

Una página web puede incluir muchos archivos CSS y/o JavaScript. Para reducir el número de peticiones (requests)
HTTP y el tamaño total de descarga de estos archivos, una práctica común es combinar y comprimir uno o
varios archivos, y después incluir los archivos comprimidos en las páginas Web.

>Información: La combinación y compresión de assets es habitualmente necesario cuando una aplicación se encuentra en
modo de producción. En modo de desarrollo, es más conveniente usar los archivos CSS/JavaScript originales por temas
relacionados con el debugging.

En el siguiente ejemplo, se muestra una propuesta para combinar y comprimir archivos asset sin necesidad de modificar
el código de la aplicación.

1. Buscar todos los asset bundles en la aplicación que se quieran combinar y comprimir.
2. Dividir estos bundles en uno o más grupos. Tenga en cuenta que cada bundle solo puede pertenecer a un único grupo.
3. Combina/Comprime los archivos CSS de cada grupo en un único archivo. Hace lo mismo para los archivos JavaScript.
4. Define un nuevo asset bundle para cada grupo:
    * Establece las propiedades [[yii\web\AssetBundle::css|css]] y [[yii\web\AssetBundle::js|js]] para que sean los
      archivos CSS y JavaScript combinados, respectivamente.
    * Personaliza los asset bundles en cada grupo configurando sus propiedades [[yii\web\AssetBundle::css|css]] y
      [[yii\web\AssetBundle::js|js]] para que sean el nuevo asset bundle creado para el grupo.

Usando este propuesta, cuando se registre un asset bundle en una vista, se genera un registro automático del nuevo
asset bundle para el grupo al que pertenece el bundle original. Y como resultado, los archivos combinados/comprimidos
se incluyen en la página, en lugar de los originales.

### Un Example <span id="example"></span>

Vamos a usar un ejemplo para explicar la propuesta anterior.

Asumiendo que la aplicación tenga dos páginas X e Y. La página X utiliza el asset bundle A, B y C mientras que la
página Y usa los asset bundles B, C y D.

Hay dos maneras de dividir estos asset bundles. Uno es usar un único grupo que incluye todos los asset bundles,
el otro es poner (A, B y C) en el Grupo X, y (B, C, D) en el grupo Y. ¿Cuál es mejor? El primero tiene la ventaja
de que las dos páginas comparten los mismos archivos CSS y JavaScript combinados, que producen una caché HTTP más
efectiva. Por otra parte, por el hecho de que un único grupo contenga todos los bundles, los archivos JavaScript serán
más grandes y por tanto incrementan el tiempo de transmisión del archivo inicial. En este ejemplo, se usará la primera
opción, ej., usar un único grupo que contenga todos los bundles.

> Info: Dividiendo los asset bundles en grupos no es una tarea trivial. Normalmente requiere un análisis de los
  datos del tráfico real de varios assets en diferentes páginas. Al principio, se puede empezar con un
  único grupo para simplificar.

Se pueden usar herramientas existentes (ej. [Closure Compiler](https://developers.google.com/closure/compiler/),
[YUI Compressor](https://github.com/yui/yuicompressor/)) para combinar y comprimir todos los bundles. Hay que tener en
cuenta que los archivos deben ser combinados en el orden que satisfaga las dependencias entre los bundles.
Por ejemplo, si el Bundle A depende del B que depende a su vez de C y D, entonces, se deben listar los archivos asset
empezando por C y D, seguidos por B y finalmente A.

Después de combinar y comprimir obtendremos un archivo CSS y un archivo JavaScript. Supongamos que se llaman
`all-xyz.css` y `all-xyz.js`, donde `xyz` representa un timestamp o un hash que se usa para generar un nombre de
archivo único para evitar problemas con la caché HTTP.

Ahora estamos en el último paso. Configurar el [[yii\web\AssetManager|asset manager]] como en el siguiente ejemplo en
la configuración de la aplicación:

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => [
                'all' => [
                    'class' => 'yii\web\AssetBundle',
                    'basePath' => '@webroot/assets',
                    'baseUrl' => '@web/assets',
                    'css' => ['all-xyz.css'],
                    'js' => ['all-xyz.js'],
                ],
                'A' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'B' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'C' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'D' => ['css' => [], 'js' => [], 'depends' => ['all']],
            ],
        ],
    ],
];
```

Como se ha explicado en la subsección [Personalización de Asset Bundles](#customizing-asset-bundles), la anterior
configuración modifica el comportamiento predeterminado de cada bundle. En particular, el Bundle A, B, C y D ya no
tendrán ningún archivo asset. Ahora todos dependen del bundle `all` que contiene los archivos combinados `all-xyz.css`
y `all-xyz.js`. Por consiguiente, para la Página X, en lugar de incluir los archivos originales desde los bundles A, B
y C, solo se incluirán los dos archivos combinados; pasa lo mismo con la Página Y.

Hay un último truco para hacer que el enfoque anterior se adapte mejor. En lugar de modificar directamente el archivo
de configuración de la aplicación, se puede poner el array del personalización del bundle en un archivo separado y que
se incluya condicionalmente este archivo en la configuración de la aplicación. Por ejemplo:

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require __DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php'),
        ],
    ],
];
```

Es decir, el array de configuración del asset bundle se guarda en `asset-prod.php` para el modo de producción, y
`assets-del.php` para los otros modos.

### Uso del Comando `asset` <span id="using-asset-command"></span>

Yii proporciona un comando de consola llamado `asset` para automatizar el enfoque descrito.

Para usar este comando, primero se debe crear un archivo de configuración para describir que asset bundle se deben
combinar y cómo se deben agrupar. Se puede usar el sub-comando `asset/template` para generar una plantilla primero y
después modificarla para que se adapte a nuestras necesidades.

```
yii asset/template assets.php
```

El comando genera un archivo llamado `assets.php` en el directorio actual. El contenido de este archivo es similar al
siguiente código:

```php
<?php
/**
 * Configuration file for the "yii asset" console command.
 * Note that in the console environment, some path aliases like '@webroot' and '@web' may not exist.
 * Please define these missing path aliases.
 */
return [
    // Ajustar comando/callback para comprimir los ficheros JavaScript:
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // Ajustar comando/callback para comprimir los ficheros CSS:
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // La lista de assets bundles para comprimir:
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Asset bundle para la salida de compresión:
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Configuración del Asset manager:
    'assetManager' => [
    ],
];
```

Se debe modificar este archivo para especificar que bundles plantea combinar en la opción `bundles`. En la opción
`targets` se debe especificar como se deben dividir entre los grupos. Se puede especificar uno o más grupos,
como se ha comentado.

> Note: Debido a que los alias `@webroot` y `@web` no están disponibles en la aplicación de consola, se deben definir
  explícitamente en la configuración.

Los archivos JavaScript se combinan, comprimen y guardan en `js/all-{hash}.js` donde {hash} se reemplaza con el hash
del archivo resultante.

Las opciones `jsCompressor` y `cssCompressor` especifican los comandos de consola o llamadas PHP (PHP callbacks) para
realizar la combinación/compresión de JavaScript y CSS. De forma predeterminada Yii usa
[Closure Compiler](https://developers.google.com/closure/compiler/) para combinar los archivos JavaScript y
[YUI Compressor](https://github.com/yui/yuicompressor/) para combinar archivos CSS. Se deben instalar las herramientas
manualmente o ajustar sus configuraciones para usar nuestras favoritas.

Con el archivo de configuración, se puede ejecutar el comando `asset` para combinar y comprimir los archivos asset y
después generar un nuevo archivo de configuración de asset bundles `asset-prod.php`:

```
yii asset assets.php config/assets-prod.php
```

El archivo de configuración generado se puede incluir en la configuración de la aplicación, como se ha descrito en la
anterior subsección.

> Info: Usar el comando `asset` no es la única opción de automatizar el proceso de combinación y compresión.
  Se puede usar la excelente herramienta de ejecución de tareas [grunt](https://gruntjs.com/) para lograr el mismo
  objetivo.
