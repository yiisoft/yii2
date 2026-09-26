Autocarga de clases
===================

Yii depende del [mecanismo de autocarga de clases](https://www.php.net/manual/es/language.oop5.autoload.php) para localizar
e incluir los archivos de las clases requiridas. Proporciona un cargador de clases de alto rendimiento que cumple con el
[estandard PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md).
El cargador se instala cuando incluyes el archivo `Yii.php`.

> Note: Para simplificar la descripción, en esta sección sólo hablaremos de la carga automática de clases. Sin embargo,
  ten en cuenta que el contenido que describimos aquí también se aplica a la autocarga de interfaces y rasgos (Traits).


Usando el Autocargador de Yii <span id="using-yii-autoloader"></span>
-----------------------------

Para utilizar el cargador automático de clases de Yii, deberías seguir dos reglas básicas cuando desarrolles y nombres tus
clases:

* Cada clase debe estar bajo un espacio de nombre (namespace). Por ejemplo `foo\bar\MyClass`.
* Cada clase debe estar guardada en un archivo individual cuya ruta está determinada por el siguiente algoritmo:

```php
// $className es un nombre completo de clase con las iniciales barras invertidas.
$classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php');
```

Por ejemplo, si el nombre de una clase es `foo\bar\MyClass`, el [alias](concept-aliases.md) la correspondiente ruta de
archivo de la clase sería `@foo/bar/MyClass.php`. Para que este sea capaz de ser resuelto como una ruta de archivo, ya sea
`@foo` o `@foo/bar` debe ser un [alias de raíz](concept-aliases.md#defining-aliases) (root alias).

Cuando utilizas la [Plantilla de Aplicación Básica](start-installation.md), puede que pongas tus clases bajo el nivel superior
de espacio de nombres `app` para que de esta manera pueda ser automáticamente cargado por Yii sin tener la necesidad de
definir un nuevo alias. Esto es porque `@app` es un [alias predefinido](concept-aliases.md#predefined-aliases), y el
nombre de una clase tal como `app\components\MyClass` puede ser resuelto en el archivo de la clase `AppBasePath/components/MyClass.php`,
de acuerdo con el algoritmo previamente descrito.

En la [Plantilla de Aplicación Avanzada](tutorial-advanced-app.md), cada nivel tiene su propio alias. Por ejemplo, el nivel
`front-end` tiene un alias de raíz `@frontend` mientras que el nivel `back-end` tiene `@backend`. Como resultado, es posible
poner las clases `front-end` bajo el espacio de nombres `frontend` mientras que las clases `back-end` pueden hacerlo bajo
`backend`. Esto permitirá que estas clases sean automaticamente cargadas por el autocargador de Yii.


Mapa de Clases <span id="class-map"></span>
--------------

El autocargador de clases de Yii soporta el *mapa de clases*, que mapea nombres de clases to sus correpondientes rutas de
archvios. Cuando el autocargador esta cargando una clase, primero chequeará si la clase se encuentra en el mapa. Si es así,
el correspondiente archivo será incluido directamente sin más comprobación. Esto hace que la clase se cargue muy rápidamente.
De hecho, todas las clases de Yii son autocargadas de esta manera.

Puedes añadir una clase al mapa de clases `Yii::$classMap` de la siguiente forma,

```php
Yii::$classMap['foo\bar\MyClass'] = 'path/to/MyClass.php';
```

[Alias](concept-aliases.md) puede ser usado para especificar la ruta de archivos de clases. Deberías iniciar el mapeo de
clases en el proceso [bootstrapping](runtime-bootstrapping.md) de la aplicación para que de esta manera el mapa esté listo
antes de que tus clases sean usadas.


Usando otros Autocargadores <span id="using-other-autoloaders"></span>
---------------------------

Debido a que Yii incluye Composer como un gestor de dependencias y extensions, es recomendado que también instales el
autocargador de Composer. Si estás usando alguna librería externa que requiere sus autocargadores, también deberías
instalarlos.

Cuando se utiliza el cargador de clases automático de Yii conjuntamente con otros autocargadores, deberías incluir el
archivo `Yii.php` *después* de que todos los demás autocargadores se hayan instalado. Esto hará que el autocargador de
Yii sea el primero en responder a cualquier petición de carga automática de clases. Por ejemplo, el siguiente código ha
sido extraido del [script de entrada](structure-entry-scripts.md) de la [Plantilla de Aplicación Básica](start-installation.md).
La primera línea instala el autocargador de Composer, mientras que la segunda línea instala el autocargador de Yii.

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```

Puedes usar el autocargador de Composer sin el autocargador de Yii. Sin embargo, al hacerlo, la eficacia de la carga de
tus clases puede que se degrade, y además deberías seguir las reglas establecidas por Composer para que tus clases pudieran
ser autocargables.

> Note: Si no deseas utilizar el autocargador de Yii, tendrás que crear tu propia versión del archivo `Yii.php` e
  incluirlo en tu [script de entrada](structure-entry-scripts.md).


Carga Automática de Clases de Extensiones <span id="autoloading-extension-classes"></span>
-----------------------------------------

El autocargador de Yii es capaz de autocargar clases de [extensiones](structure-extensions.md). El único requirimiento es
que la extensión especifique correctamente la sección de `autoload` (autocarga) en su archivo `composer.json`. Por favor,
consulta la [documentación de Composer](https://getcomposer.org/doc/04-schema.md#autoload) para más detalles acerca de la
especificación `autoload`.

En el caso de que no quieras usar el autocargador de Yii, el autocargador de Composer podría cargar las clases de extensiones
por tí.
