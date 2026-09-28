Alias
=====

Loa alias son utilizados para representar rutas o URLs de manera que no tengas que escribir explícitamente rutas absolutas o URLs en tu
proyecto. Un alias debe comenzar con el signo `@` para ser diferenciado de una ruta normal de archivo y de URLs. Los alias definidos
sin el `@` del principio, serán prefijados con el signo `@`.

Yii trae disponibles varios alias predefinidos. Por ejemplo, el alias `@yii` representa la ruta de instalación del
framework Yii; `@web` representa la URL base para la aplicación Web ejecutándose.

Definir Alias <span id="defining-aliases"></span>
-------------

Para definir un alias puedes llamar a [[Yii::setAlias()]] para una determinada ruta de archivo o URL. Por ejemplo,

```php
// un alias de una ruta de archivos
Yii::setAlias('@foo', '/path/to/foo');

// una alias de un URL
Yii::setAlias('@bar', 'https://www.example.com');
```

> Note: Una ruta de archivo o URL en alias NO debe necesariamente referirse a un archivo o recurso existente.

Dado un alias, puedes derivar un nuevo alias (sin necesidad de llamar [[Yii::setAlias()]]) anexando una barra diagonal `/`
seguida por uno o varios segmentos de la ruta. Llamamos los alias definidos a través de [[Yii::setAlias()]]
*alias de raíz* (root alias), mientras que los alias derivados de ellos *alias derivados* (derived aliases). Por ejemplo,
`@foo` es un alias de raíz, mientras que `@foo/bar/file.php` es un alias derivado.

Puedes definir un alias usando otro alias (ya sea un alias de raíz o derivado):

```php
Yii::setAlias('@foobar', '@foo/bar');
```

Los alias de raíz están usualmente definidos durante la etapa [bootstrapping](runtime-bootstrapping.md) de la aplicación.
Por ejemplo, puedes llamar a [[Yii::setAlias()]] en el [script de entrada](structure-entry-scripts.md).
Por conveniencia, [Application](structure-applications.md) provee una propiedad modificable llamada `aliases` que puedes
configurar en la [configuración](concept-configurations.md) de la aplicación, como por ejemplo,

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/path/to/foo',
        '@bar' => 'https://www.example.com',
    ],
];
```


Resolución de Alias <span id="resolving-aliases"></span>
-------------------

Puedes llamar [[Yii::getAlias()]] para resolver un alias de raíz en la ruta o URL que representa. El mismo método puede
además resolver un alias derivado en su correspondiente ruta de archivo o URL. Por ejemplo,

```php
echo Yii::getAlias('@foo');               // muestra: /path/to/foo
echo Yii::getAlias('@bar');               // muestra: https://www.example.com
echo Yii::getAlias('@foo/bar/file.php');  // muestra: /path/to/foo/bar/file.php
```

La ruta de archivo/URL representado por un alias derivado está determinado por la sustitución de la parte de su alias raíz
con su correspondiente ruta/Url en el alias derivado.

> Note: El método [[Yii::getAlias()]] no comprueba si la ruta/URL resultante hacer referencia a un archivo o recurso existente.


Un alias de raíz puede contener carácteres `/`. El método [[Yii::getAlias()]] es lo suficientemente inteligente para saber
qué parte de un alias es un alias de raíz y por lo tanto determinar correctamente la correspondiente ruta de archivo o URL.
Por ejemplo,

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
Yii::getAlias('@foo/test/file.php');  // muestra: /path/to/foo/test/file.php
Yii::getAlias('@foo/bar/file.php');   // muestra: /path2/bar/file.php
```

Si `@foo/bar` no está definido como un alias de raíz, la última declaración mostraría `/path/to/foo/bar/file.php`.


Usando Alias <span id="using-aliases"></span>
------------

Los alias son utilizados en muchos lugares en Yii sin necesidad de llamar [[Yii::getAlias()]] para convertirlos
en rutas/URLs. Por ejemplo, [[yii\caching\FileCache::cachePath]] puede aceptar tanto una ruta de archivo como un alias
que represente la ruta de archivo, gracias al prefijo `@` el cual permite diferenciar una ruta de archivo
de un alias.

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

Por favor, presta atención a la documentación API para ver si una propiedad o el parámetro de un método soporta alias.


Alias Predefinidos <span id="predefined-aliases"></span>
------------------

Yii predefine un conjunto de alias para aliviar la necesidad de hacer referencia a rutas de archivo o URLs que son
utilizadas regularmente. La siguiente es la lista de alias predefinidos por Yii:

- `@yii`: el directorio donde el archivo `BaseYii.php` se encuentra (también llamado el directorio del framework).
- `@app`: la [[yii\base\Application::basePath|ruta base]] de la aplicación que se está ejecutando actualmente.
- `@runtime`: la [[yii\base\Application::runtimePath|ruta de ejecución]] de la aplicación en ejecución. Por defecto `@app/runtime`.
- `@webroot`: el directorio raíz Web de la aplicación Web se está ejecutando actualmente.
- `@web`: la URL base de la aplicación web se ejecuta actualmente. Tiene el mismo valor que [[yii\web\Request::baseUrl]].
- `@vendor`: el [[yii\base\Application::vendorPath|directorio vendor de Composer]]. Por defecto `@app/vendor`.
- `@bower`, el directorio raíz que contiene [paquetes bower](https://bower.io/). Por defecto `@vendor/bower`.
- `@npm`, el directorio raíz que contiene [paquetes npm](https://www.npmjs.com/). Por defecto `@vendor/npm`.

El alias `@yii` se define cuando incluyes el archivo `Yii.php` en tu [script de entrada](structure-entry-scripts.md),
mientras que el resto de los alias están definidos en el constructor de la aplicación cuando se aplica la
[configuración](concept-configurations.md) de la aplicación.


Alias en Extensiones <span id="extension-aliases"></span>
--------------------

Un alias se define automáticamente por cada [extensión](structure-extensions.md) que ha sido instalada a través de Composer.
El alias es nombrado tras el `namespace` de raíz de la extensión instalada tal y como está declarada en su archivo `composer.json`,
y representa el directorio raíz de la extensión. Por ejemplo, si instalas la extensión `yiisoft/yii2-jui`, tendrás
automáticamente definido el alias `@yii/jui` durante la etapa [bootstrapping](runtime-bootstrapping.md) de la aplicación:

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```
