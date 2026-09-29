Clase Auxiliar URL (URL Helper)
===============================

La clase auxiliar URL proporciona un conjunto de métodos estáticos para gestionar URLs.


## Obtener URLs comúnes <span id="getting-common-urls"></span>

Se pueden usar dos métodos para obtener URLs comunes: URL de inicio (home URL) y URL base (base URL) de la petición
(request) actual. Para obtener la URL de inicio se puede usar el siguiente código:

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

Si no se pasan parámetros, la URL generada es relativa. Se puede pasar `true`para obtener la URL absoluta del
esquema actual o especificar el esquema explícitamente (`https`, `http`).

Para obtener la URL base de la petición actual, se puede usar el siguiente código:

```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

El único parámetro del método funciona exactamente igual que para `Url::home()`.


## Creación de URLs <span id="creating-urls"></span>

Para crear una URL para una ruta determinada se puede usar `Url::toRoute()`. El método utiliza [[\yii\web\UrlManager]]
para crear la URL:

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```

Se puede especificar la ruta como una cadena de texto, ej. `site/index`. También se puede usar un array si se
quieren especificar parámetros para la URL que se esta generando. El formato del array debe ser:

```php
// genera: /index.php?r=site%2Findex&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

Si se quiere crear una URL con un enlace, se puede usar el formato de array con el parámetro `#`. Por ejemplo,

```php
// genera: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```

Una ruta puede ser absoluta o relativa. Una ruta absoluta tiene una barra al principio (ej. `/site/index`), mientras que una ruta relativa
no la tiene (ej. `site/index` o `index`). Una ruta relativa se convertirá en una ruta absoluta siguiendo las siguientes reglas:

- Si la ruta es una cadena vacía, se usará la [[\yii\web\Controller::route|route]] actual;
- Si la ruta no contiene barras (ej. `index`), se considerará que es el ID de una acción del controlador actual y
  se antepondrá con [[\yii\web\Controller::uniqueId]];
- Si la ruta no tiene barra inicial (ej. `site/index`), se considerará que es una ruta relativa del modulo actual y
  se le antepondrá el [[\yii\base\Module::uniqueId|uniqueId]] del modulo.

Desde la versión 2.0.2, puedes especificar una ruta en términos de [alias](concept-aliases.md). Si este es el caso,
el alias será convertido primero en la ruta real, la cual será entonces transformada en una ruta absoluta de acuerdo
a las reglas mostradas arriba.

A continuación se muestran varios ejemplos del uso de este método:

```php
// /index.php?r=site%2Findex
echo Url::toRoute('site/index');

// /index.php?r=site%2Findex&src=ref1#name
echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post%2Fedit&id=100     asume que el alias "@postEdit" se definió como "post/edit"
echo Url::toRoute(['@postEdit', 'id' => 100]);

// https://www.example.com/index.php?r=site%2Findex
echo Url::toRoute('site/index', true);

// https://www.example.com/index.php?r=site%2Findex
echo Url::toRoute('site/index', 'https');
```

El otro método `Url::to()` es muy similar a [[toRoute()]]. La única diferencia es que este método requiere que la ruta
especificada sea un array. Si se pasa una cadena de texto, se tratara como una URL.

El primer argumento puede ser:

- un array: se llamará a [[toRoute()]] para generar la URL. Por ejemplo: `['site/index']`,
  `['post/index', 'page' => 2]`. Se puede revisar [[toRoute()]] para obtener más detalles acerca de como especificar
  una ruta.
- una cadena que empiece por `@`: se tratará como un alias, y se devolverá la cadena correspondiente asociada a este
  alias.
- una cadena vacía: se devolverá la URL de la petición actual;
- una cadena de texto: se devolverá sin alteraciones.

Cuando se especifique `$schema` (tanto una cadena de text como `true`), se devolverá una URL con información del host
(obtenida mediante [[\yii\web\UrlManager::hostInfo]]). Si `$url` ya es una URL absoluta, su esquema se reemplazará con
el especificado.

A continuación se muestran algunos ejemplos de uso:

```php
// /index.php?r=site%2Findex
echo Url::to(['site/index']);

// /index.php?r=site%2Findex&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post%2Fedit&id=100     asume que el alias "@postEdit" se definió como "post/edit"
echo Url::to(['@postEdit', 'id' => 100]);

// the currently requested URL
echo Url::to();

// /images/logo.gif
echo Url::to('@web/images/logo.gif');

// images/logo.gif
echo Url::to('images/logo.gif');

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', true);

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', 'https');
```

Desde la versión 2.0.3, puedes utilizar [[yii\helpers\Url::current()]] para crear una URL a partir de la ruta
solicitada y los parámetros GET. Puedes modificar o eliminar algunos de los parámetros GET, o también agregar nuevos
pasando un parámetro `$params` al método. Por ejemplo,

```php
// asume que $_GET = ['id' => 123, 'src' => 'google'], la ruta actual es "post/view"

// /index.php?r=post%2Fview&id=123&src=google
echo Url::current();

// /index.php?r=post%2Fview&id=123
echo Url::current(['src' => null]);
// /index.php?r=post%2Fview&id=100&src=google
echo Url::current(['id' => 100]);
```


## Recordar URLs <span id="remember-urls"></span>

Hay casos en que se necesita recordar la URL y después usarla durante el procesamiento de una de las peticiones
secuenciales. Se puede logar de la siguiente manera:

```php
// Recuerda la URL actual
Url::remember();

// Recuerda la URL especificada. Revisar Url::to() para ver formatos de argumentos.
Url::remember(['product/view', 'id' => 42]);

// Recuerda la URL especificada con un nombre asignado
Url::remember(['product/view', 'id' => 42], 'product');
```

En la siguiente petición se puede obtener la URL memorizada de la siguiente manera:

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```

## Chequear URLs relativas <span id="checking-relative-urls"></span>

Para descubrir si una URL es relativa, es decir, que no contenga información del host, se puede utilizar el siguiente código:

```php
$isRelative = Url::isRelative('test/it');
```
