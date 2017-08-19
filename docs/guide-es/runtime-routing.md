Enrutamiento y Creación de URLS
===============================

Cuando una aplicación Yii empieza a procesar una URL solicitada, lo primero que hace es convertir la URL en una 
[ruta](structure-controllers.md#routes). Luego se usa la ruta para instanciar la 
[acción de controlador](structure-controllers.md) correspondiente para gestionar la petición. A este proceso se 
le llama *enrutamiento*.

El proceso inverso se llama *creación de URLs*, y crea una URL a partir de una ruta dada y unos parámetros de consulta (query) asociados. Cuando posteriormente se solicita la URL creada, el proceso de enrutamiento puede resolverla y 
convertirla en la ruta original con los parámetros asociados.

La principal pieza encargada del enrutamiento y de la creación de URLs es [[yii\web\UrlManager|URL manager]], que se 
registra como el componente de aplicación `urlManager`. El [[yii\web\UrlManager|URL manager]] proporciona el método 
[[yii\web\UrlManager::parseRequest()|parseRequest()]] para convertir una petición entrante en una ruta y sus 
parámetros asociados y el método [[yii\web\UrlManager::createUrl()|createUrl()]] para crear una URL a partir de una 
ruta dada y sus parámetros asociados.

Configurando el componente `urlManager` en la configuración de la aplicación, se puede dotar a la aplicación de 
reconocimiento arbitrario de formatos de URL sin modificar el código de la aplicación existente.  Por ejemplo, se 
puede usar el siguiente código para crear una URL para la acción `post/view`:

```php
use yii\helpers\Url;

// Url::to() llama a UrlManager::createUrl() para crear una URL
$url = Url::to(['post/view', 'id' => 100]);
```

Dependiendo de la configuración de `urlManager`, la URL generada puede asemejarse a alguno de los siguientes (u otro) 
formato. Y si la URL creada se solicita posteriormente, se seguirá convirtiendo en la ruta original y los valores de 
los parámetros.

```
/index.php?r=post/view&id=100
/index.php/post/100
/posts/100
```


## Formatos de URL <span id="url-formats"></span>

El [[yii\web\UrlManager|URL manager]] soporta dos formatos de URL: el formato predeterminado de URL y el formato URL 
amigable (pretty URL).

El formato de URL predeterminado utiliza un parámetro de consulta llamado `r` para representar la ruta y los 
parámetros normales de la petición para representar los parámetros asociados con la ruta. Por ejemplo, la URL 
`/index.php?r=post/view&id=100` representa la ruta `post/view` y 100 es el valor del parámetro `id` de la consulta. 
El formato predeterminado de URL no requiere ningún tipo de configuración para [[yii\web\UrlManager|URL manager]] y 
funciona en cualquier configuración de servidor Web.

El formato de URL amigable utiliza la ruta adicional a continuación del nombre del script de entrada (entry script) 
para representar la ruta y los parámetros de consulta. Por ejemplo, La ruta en la URL `/index.php/post/100` es 
`/post/100` que puede representar la ruta `post/view` y el parámetro de consulta `id` 100 con una 
[[yii\web\UrlManager::rules|URL rule]] apropiada. Para poder utilizar el formato de URL amigable, se tendrán que 
diseñar una serie de [[yii\web\UrlManager::rules|URL rules]] de acuerdo con el requerimiento actual acerca de como 
deben mostrarse las URLs.

Se puede cambiar entre los dos formatos de URL conmutando la propiedad 
[[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] del [[yii\web\UrlManager|URL manager]] sin cambiar ningún 
otro código de aplicación.

## Enrutamiento <span id="routing"></span>

El Enrutamiento involucra dos pasos. El primero, la petición (request) entrante se convierte en una ruta y sus 
parámetros de consulta asociados. En el segundo paso, se crea la correspondiente 
[acción de controlador](structure-controllers.md) para la ruta convertida para que gestione la petición.

Cuando se usa el formato predefinido de URL, convertir una petición en una ruta es tan simple como obtener los valores 
del parámetro de consulta `GET` llamado `r`.

Cuando se usa el formato de URL amigable, el [[yii\web\UrlManager|URL manager]] examinará las 
[[yii\web\UrlManager::rules|URL rules]] registradas para encontrar alguna que pueda convertir la petición en una ruta. 
Si no se encuentra tal regla, se lanzará una excepción de tipo [[yii\web\NotFoundHttpException]].

Una vez que la petición se ha convertido en una ruta, es el momento de crear la acción de controlador identificada 
por la ruta. La ruta se desglosa en múltiples partes a partir de las barras que contenga. Por ejemplo, `site/index` 
será desglosado en `site` e `index`. Cada parte is un ID que puede hacer referencia a un modulo, un controlador o una 
acción. Empezando por la primera parte de la ruta, la aplicación, sigue los siguientes pasos para generar 
(si los hay), controladores y acciones:

1. Establece la aplicación como el modulo actual.
2. Comprueba si el [[yii\base\Module::controllerMap|controller map]] del modulo actual contiene un ID actual. Si lo 
   tiene, se creará un objeto controlador de acuerdo con la configuración del controlador encontrado en el mapa, y 
   se seguirá el Paso 5 para gestionar la parte restante de la ruta. 
3. Comprueba si el ID hace referencia a un modulo listado en la propiedad [[yii\base\Module::modules|modules]] del 
   módulo actual. Si está listado, se crea un modulo de acuerdo con la configuración encontrada en el listado de 
   módulos, y se seguirá el Paso 2 para gestionar la siguiente parte de la ruta bajo el contexto de la creación de un 
   nuevo módulo.
4. Trata el ID como si se tratara de un ID de controlador y crea un objeto controlador. Sigue el siguiente paso con la    parte restante de la ruta.
5. El controlador busca el ID en su [[yii\base\Controller::actions()|action map]]. Si lo encuentra, crea una acción de    acuerdo con la configuración encontrada en el mapa. De otra forma, el controlador intenta crear una acción en linea    definida por un método de acción correspondiente al ID actual.

Si ocurre algún error entre alguno de los pasos anteriores, se lanzará una excepción de tipo 
[[yii\web\NotFoundHttpException]], indicando el fallo de proceso de enrutamiento.

### Ruta Predeterminada <span id="default-route"></span>

Cuando una petición se convierte en una ruta vacía, se usa la llamada *ruta predeterminada*. Por defecto, la ruta 
predeterminada es `site/index`, que hace referencia a la acción `index` del controlador `site`. Se puede personalizar 
configurando la propiedad [[yii\web\Application::defaultRoute|defaultRoute]] de la aplicación en la configuración de 
aplicación como en el siguiente ejemplo:

```php
[
    // ...
    'defaultRoute' => 'main/index',
];
```


### Ruta `catchAll` <span id="catchall-route"></span>

A veces, se puede querer poner la aplicación Web en modo de mantenimiento temporalmente y mostrar la misma pagina de 
información para todas las peticiones. Hay varias maneras de lograr este objetivo. Pero una de las maneras más simples 
es configurando la propiedad [[yii\web\Application::catchAll]] como en el siguiente ejemplo de configuración de 
aplicación:

```php
[
    // ...
	'catchAll' => ['site/offline'],
];
```

Con la anterior configuración, se usar la acción `site/offline` para gestionar todas las peticiones entrantes.

La propiedad `catchAll` debe tener un array cuyo primer elemento especifique una ruta, y el resto de elementos 
(pares nombre-valor) especifiquen los parámetros [ligados a la acción](structure-controllers.md#action-parameters).

## Creación de URLs <span id="creating-urls"></span>

Yii proporciona un método auxiliar (helper method) [[yii\helpers\Url::to()]] para crear varios tipos de URLs a partir 
de las rutas dadas y sus parámetros de consulta asociados. Por ejemplo, 

```php
use yii\helpers\Url;

// crea una URL para la ruta: /index.php?r=post/index
echo Url::to(['post/index']);

// crea una URL para la ruta con parámetros: /index.php?r=post/view&id=100
echo Url::to(['post/view', 'id' => 100]);

// crea una URL interna: /index.php?r=post/view&id=100#contentecho 
Url::to(['post/view', 'id' => 100, '#' => 'content']);

// crea una URL absoluta: http://www.example.com/index.php?r=post/index
echo Url::to(['post/index'], true);

// crea una URL absoluta usando el esquema https: https://www.example.com/index.php?r=post/index
echo Url::to(['post/index'], 'https');
```

Hay que tener en cuenta que en el anterior ejemplo, asumimos que se está usando el formato de URL predeterminado. 
Si habilita el formato de URL amigable, las URLs creadas serán diferentes, de acuerdo con las 
[[yii\web\UrlManager::rules|URL rules]] que se usen.

La ruta que se pasa al método [[yii\helpers\Url::to()]] es context sensitive. Esto quiere decir que puede ser una ruta 
*relativa* o una ruta *absoluta* que serán tipificadas de acuerdo con las siguientes reglas:

- Si una ruta es una cadena vacía, se usará la [[yii\web\Controller::route|route]] solicitada actualmente. 
- Si la ruta no contiene ninguna barra `/`, se considerará que se trata de un ID de acción del controlador actual y se 
  le antepondrá el valor [[\yii\web\Controller::uniqueId|uniqueId]] del controlador actual. 
- Si la ruta no tiene barra inicial, se considerará que se trata de una ruta relativa al modulo actual y se le 
  antepondrá el valor [[\yii\base\Module::uniqueId|uniqueId]] del modulo actual.

Por ejemplo, asumiendo que el modulo actual es `admin` y el controlador actual es `post`,

```php
use yii\helpers\Url;

// la ruta solicitada: /index.php?r=admin/post/index
echo Url::to(['']);

// una ruta relativa solo con ID de acción: /index.php?r=admin/post/index
echo Url::to(['index']);

// una ruta relativa: /index.php?r=admin/post/index
echo Url::to(['post/index']);

// una ruta absoluta: /index.php?r=post/index
echo Url::to(['/post/index']);
```

El método [[yii\helpers\Url::to()]] se implementa llamando a los métodos 
[[yii\web\UrlManager::createUrl()|createUrl()]] y [[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]] del 
[[yii\web\UrlManager|URL manager]]. En las próximas sub-secciones, explicaremos como configurar el 
[[yii\web\UrlManager|URL manager]] para personalizar el formato de las URLs generadas.

El método [[yii\helpers\Url::to()]] también soporta la creación de URLs NO relacionadas con rutas particulares. 
En lugar de pasar un array como su primer paramento, se debe pasar una cadena de texto. Por ejemplo,

```php
use yii\helpers\Url;

// la URL solicitada actualmente: /index.php?r=admin/post/index
echo Url::to();

// una URL con alias: http://example.comYii::setAlias('@example', 'http://example.com/');
echo Url::to('@example');

// una URL absoluta: http://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);```
```

Además del método `to()`, la clase auxiliar [[yii\helpers\Url]] también proporciona algunos otros métodos de creación 
de URLs. Por ejemplo,

```php
use yii\helpers\Url;

// URL de la página inicial: /index.php?r=site/index
echo Url::home();

// la URL base, útil si la aplicación se desarrolla en una sub-carpeta de la carpeta raíz (root) Web
echo Url::base();

// la URL canónica de la actual URL solicitada// visitar https://en.wikipedia.org/wiki/Canonical_link_element
echo Url::canonical();

// recuerda la actual URL solicitada y la recupera más tarde requestsUrl::remember();
echo Url::previous();
```


## Uso de URLs Amigables <span id="using-pretty-urls"></span>

Para utilizar URLs amigables, hay que configurar el componente `ulrManager` en la configuración de la aplicación como 
en el siguiente ejemplo:

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

La propiedad [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] es obligatoria ya que alterna el formato de URL 
amigable. El resto de propiedades son opcionales. Sin embargo, la anterior configuración es la más común.

* [[yii\web\UrlManager::showScriptName|showScriptName]]: esta propiedad determina si el script de entrada debe ser 
  incluido en las URLs generadas. Por ejemplo, en lugar de crear una URL `/index.php/post/100`, estableciendo la 
  propiedad con valor `true`, la URL que se generará sera `/post/100`.
* [[yii\web\UrlManager::enableStrictParsing|enableStrictParsing]]: esta propiedad determina si se habilita la 
  conversión de petición estricta, si se habilita, la URL solicitada tiene que encajar al menos con uno de las 
  [[yii\web\UrlManager::rules|rules]] para poder ser tratada como una petición valida, o se lanzará una 
  [[yii\web\NotFoundHttpException]]. Si la conversión estricta esta deshabilitada, cuando ninguna de las 
  [[yii\web\UrlManager::rules|rules]] coincida con la URL solicitada, la parte de información de la URL se tratará 
  como si fuera la ruta solicitada.
* [[yii\web\UrlManager::rules|rules]]: esta propiedad contiene una lista de las reglas que especifican como convertir 
  y crear URLs. Esta es la propiedad principal con la que se debe trabajar para crear URLs que satisfagan el formato 
  de un requerimiento particular de la aplicación.

> Note: Para ocultar el nombre del script de entrada en las URLs generadas, además de establecer el 
  [[yii\web\UrlManager::showScriptName|showScriptName]] a falso, puede ser necesaria la configuración del servidor Web 
  para que identifique correctamente que script PHP debe ejecutarse cuando se solicita una URL que no lo especifique. 
  Si se usa el servidor Web Apache, se puede utilizar la configuración recomendada descrita en la sección de 
  [Instalación](start-installation.md#recommended-apache-configuration).


### Reglas de URL <span id="url-rules"></span>

Una regla de URL es una instancia de [[yii\web\UrlRule]] o de una clase hija. Cada URL consiste en un patrón utilizado 
para cotejar la parte de información de ruta de las URLs, una ruta, y algunos parámetros de consulta. Una URL puede 
usarse para convertir una petición si su patrón coincide con la URL solicitada y una regla de URL pude usarse para 
crear una URL si su ruta y sus nombres de parámetros coinciden con los que se hayan dado.

Cuando el formato de URL amigables está habilitado, el [[yii\web\UrlManager|URL manager]] utiliza las reglas de URL 
declaradas en su propiedad [[yii\web\UrlManager::rules|rules]] para convertir las peticiones entrantes y crear URLs. 
En particular, para convertir una petición entrante, el [[yii\web\UrlManager|URL manager]] examina las reglas en el 
orden que se han declarado y busca la *primera* regla que coincida con la URL solicitada. La regla que coincide es la 
que se usa para convertir la URL en una ruta y sus parámetros asociados. De igual modo, para crear una URL, el 
[[yii\web\UrlManager|URL manager]] busca la primera regla que coincida con la ruta dad y los parámetros y la utiliza 
para crear una URL.

Se pueden configurar las [[yii\web\UrlManager::rules]] como un array con claves, siendo los patrones y las reglas sus 
correspondientes rutas. Cada pareja patrón-ruta construye una regla de URL. Por ejemplo, la siguiente configuración de 
configuración de [[yii\web\UrlManager::rules|rules]] declara dos reglas de URL. La primera regla coincide con una URL 
`posts` y la mapea a la ruta `post/index`. La segunda regla coincide con una URL que coincida con la expresión regular 
`post/(\d+)` y la mapea a la ruta `post/view` y el parámetro llamado `id`.

```php
[
    'posts' => 'post/index', 
    'post/<id:\d+>' => 'post/view',
]
```

> Información; El patrón en una regla se usa para encontrar coincidencias en la parte de información de la URL. 
  Por ejemplo, la parte de información de la ruta `/index.php/post/100?source=ad` es `post/100` 
  (la primera barra y la ultima son ignoradas) que coincide con el patrón `post/(\d+)`.

Entre la declaración de reglas de URL como pares de patrón-ruta, también se pueden declarar como arrays de 
configuración. Cada array de configuración se usa para configurar un único objeto de tipo regla de URL. Este proceso 
se necesita a menudo cuando se quieren configurar otras propiedades de la regla de URL. Por ejemplo,

```php
[
    // ... otras reglas de URL ...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

De forma predeterminada si no se especifica la opción `class` en la configuración de una regla, se utilizará la clase 
predeterminada [[yii\web\UrlRule]].


### Parameters Asociativos <span id="named-parameters"></span>

Una regla de URL puede asociarse a una determinado grupo de parámetros de consulta que se hayan sido especificados en 
el patrón con el formato `<ParamName:RegExp>`, donde `ParamName` especifica el nombre del parámetro y `RegExp` es una 
expresión regular opcional que se usa para encontrar los valores de los parámetros. Si no se especifica `RegExp` 
significa que el parámetro debe ser una cadena de texto sin ninguna barra.

> Note: Solo se pueden especificar expresiones regulares para los parámetros. La parte restante del patrón se 
  considera texto plano.

Cuando se usa una regla para convertir una URL, esta rellenara los parámetros asociados con los valores que coincidan 
con las partes correspondientes de la URL, y estos parámetros serán accesibles posteriormente mediante `$_GET` por el 
componente de aplicación `request`. Cuando se usa una regla para crear una URL, esta obtendrá los valores de los 
parámetros proporcionados y los insertara donde se hayan declarado los parámetros.

Vamos a utilizar algunos ejemplos para ilustrar como funcionan los parámetros asociativos. Asumiendo que hemos 
declarado las siguientes tres URLs:

```php
[
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
    'posts/<year:\d{4}>/<category>' => 'post/index',
]
```

Cuando se usen las reglas para convertir URLs:

- `/index.php/posts` se convierte en la ruta `post/index` usando la primera regla;
- `/index.php/posts/2014/php` se convierte en la ruta `post/index`, el parámetro `year` cuyo valor es 2014 y el 
  parámetro `category` cuyo valor es `php` usando la tercera regla;
- `/index.php/post/100` se convierte en la ruta `post/view` y el parámetro `id` cuyo valor es 100 usando la segunda 
  regla;
- `/index.php/posts/php` provocara una [[yii\web\NotFoundHttpException]] cuando 
  [[yii\web\UrlManager::enableStrictParsing]] sea `true`, ya que no coincide ninguno de los parámetros . Si 
  [[yii\web\UrlManager::enableStrictParsing]] es `false` (valor predeterminado), se devolverá como ruta la parte de 
  información `posts/php`.

Y cuando las se usen las reglas para crear URLs:

- `Url::to(['post/index'])` genera `/index.php/posts` usando la primera regla;
- `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` genera `/index.php/posts/2014/php` usando la tercera 
  regla;
- `Url::to(['post/view', 'id' => 100])` genera `/index.php/post/100` usando la segunda regla;
- `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` genera `/index.php/post/100?source=ad` usando la segunda 
   regla. Debido a que el parámetro `source` no se especifica en la regla, se añade como un parámetro de consulta en 
   la URL generada.
- `Url::to(['post/index', 'category' => 'php'])` genera `/index.php/post/index?category=php` no usa ninguna de las 
   reglas. Hay que tener en cuenta que si no se aplica ninguna de las reglas, la URL se genera simplemente añadiendo 
   la parte de información de la ruta y todos los parámetros como parte de la consulta.


### Parametrización de Rutas <span id="parameterizing-routes"></span>

Se pueden incrustar nombres de parámetros en la ruta de una regla de URL. Esto permite a la regla de URL poder ser 
usada para que coincida con varias rutas. Por ejemplo, la siguiente regla incrusta los parámetros `controller` y 
`action` en las rutas.

```php
[
    '<controller:(post|comment)>/<id:\d+>/<action:(create|update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/view',
    '<controller:(post|comment)>s' => '<controller>/index',
]
```

Para convertir una URL `index.php/comment/100/create`, se aplicará la primera regla, que establece el parámetro 
`controller` a `comment` y el parámetro `action` a `create`. Por lo tanto la ruta `<controller>/<action>` se resuelve 
como `comment/create`.

Del mismo modo, para crear una URL para una ruta `comment/index`, se aplicará la tercera regla, que crea una URL 
`/index.php/comments`.

> Info: Mediante la parametrización de rutas es posible reducir el numero de reglas de URL e incrementar 
  significativamente el rendimiento del [[yii\web\UrlManager|URL manager]].

De forma predeterminada, todos los parámetros declarados en una regla son requeridos. Si una URL solicitada no 
contiene un parámetro en particular, o si se esta creando una URL sin un parámetro en particular, la regla no se 
aplicará. Para establecer algunos parámetros como opcionales, se puede configurar la propiedad de 
[[yii\web\UrlRule::defaults|defaults]] de una regla. Los parámetros listados en esta propiedad son opcionales y se 
usarán los parámetros especificados cuando estos no se proporcionen.

En la siguiente declaración de reglas, los parámetros `page` y `tag` son opcionales y cuando no se proporcionen, se 
usarán los valores 1 y cadena vacía respectivamente.

```php
[
    // ... otras reglas ...
    [
        'pattern' => 'posts/<page:\d+>/<tag>',
        'route' => 'post/index',
        'defaults' => ['page' => 1, 'tag' => ''],
    ],
]
```

La regla anterior puede usarse para convertir o crear cualquiera de las siguientes URLs:

* `/index.php/posts`: `page` es 1, `tag` es ''.
* `/index.php/posts/2`: `page` es 2, `tag` es ''.
* `/index.php/posts/2/news`: `page` es 2, `tag` es `'news'`.
* `/index.php/posts/news`: `page` es 1, `tag` es `'news'`.

Sin usar ningún parámetro opcional, se tendrían que crear 4 reglas para lograr el mismo resultado.


### Reglas con Nombres de Servidor <span id="rules-with-server-names"></span>

Es posible incluir nombres de servidores Web en los parámetros de las URLs. Esto es practico principalmente cuando una 
aplicación debe tener distintos comportamientos paro diferentes nombres de servidores Web. Por ejemplo, las siguientes 
reglas convertirán la URL `http://admin.example.com/login` en la ruta `admin/user/login` y 
`http://www.example.com/login` en `site/login`.

```php
[
    'http://admin.example.com/login' => 'admin/user/login',
    'http://www.example.com/login' => 'site/login',
]
```

También se pueden incrustar parámetros en los nombres de servidor para extraer información dinámica de ellas. Por 
ejemplo, la siguiente regla convertirá la URL `http://en.example.com/posts` en la ruta `post/index` y el parámetro 
`language=en`.

```php
[
    'http://<language:\w+>.example.com/posts' => 'post/index',
]
```

> Note: Las reglas con nombres de servidor NO deben incluir el subdirectorio del script de entrada (entry script) en 
  sus patrones. Por ejemplo, is la aplicación se encuentra en `http://www.example.com/sandbox/blog`, entonces se debe 
  usar el patrón `http://www.example.com/posts` en lugar de `http://www.example.com/sandbox/blog/posts`. Esto 
  permitirá que la aplicación se pueda desarrollar en cualquier directorio sin la necesidad de cambiar el código de la 
  aplicación.

### Sufijos de URL <span id="url-suffixes"></span>

Se puede querer añadir sufijos a las URLs para varios propósitos. Por ejemplo, se puede añadir `.html`a las URLs para 
que parezcan URLs para paginas HTML estáticas; también se puede querer añadir `.json` a las URLs para indicar el tipo 
de contenido que se espera encontrar en una respuesta (response). Se puede lograr este objetivo configurando la 
propiedad [[yii\web\UrlManager::suffix]] como en el siguiente ejemplo de configuración de aplicación:

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

La configuración anterior permitirá al [[yii\web\UrlManager|URL manager]] reconocer las URLs solicitadas y a su vez 
crear URLs con el sufijo `.html`.

> Tip: Se puede establecer `/` como el prefijo de URL para que las URLs finalicen con una barra.

> Note: Cuando se configura un sufijo de URL, si una URL solicitada no tiene el sufijo, se considerará como una URL 
  desconocida. Esta es una practica recomendada para SEO (optimización en motores de búsqueda).

A veces, se pueden querer usar sufijos diferentes para URLs diferentes. Esto se puede conseguir configurando la 
propiedad [[yii\web\UrlRule::suffix|suffix]] de una regla de URL individual. Cuando una regla de URL tiene la 
propiedad establecida, anulará el sufijo estableciendo a nivel de [[yii\web\UrlManager|URL manager]]. Por ejemplo, la 
siguiente configuración contiene una regla de URL personalizada que usa el sufijo `.json` en lugar del sufijo global 
`.html`.

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.json',
                ],
            ],
        ],
    ],
]
```


### Métodos HTTP <span id="http-methods"></span>

Cuando se implementan APIs RESTful, normalmente se necesita que ciertas URLs se conviertan en otras de acuerdo con el 
método HTTP que se esté usando. Esto se puede hacer fácilmente prefijando los métodos HTTP soportados como los 
patrones de las reglas. Si una regla soporta múltiples métodos HTTP, hay que separar los nombres de los métodos con 
comas. Por ejemplo, la siguiente regla usa el mismo patrón `post/<id:\d+>` para dar soporte a diferentes métodos HTTP. 
Una petición para `PUT post/100` se convertirá en `post/create`, mientras que una petición `GET post/100` se 
convertirá en `post/view`.

```php
[
    'PUT,POST post/<id:\d+>' => 'post/create',
    'DELETE post/<id:\d+>' => 'post/delete',
    'post/<id:\d+>' => 'post/view',
]
```

> Note: Si una regla de URL contiene algún método HTTP en su patrón, la regla solo se usará para aplicar conversiones. 
  Se omitirá cuando se llame a [[yii\web\UrlManager|URL manager]] para crear URLs.

> Tip: Para simplificar el enrutamiento en APIs RESTful, Yii proporciona una clase de reglas de URL 
  [[yii\rest\UrlRule]] especial que es bastante eficiente y soporta ciertas características como pluralización de IDs 
  de controladores. Para conocer más detalles, se puede visitar la sección [Enrutamiento](rest-routing.md) acerca de 
  el desarrollo de APIs RESTful.


### Personalización de Reglas <span id="customizing-rules"></span>

En los anteriores ejemplos, las reglas de URL se han declarado principalmente en términos de pares de patrón-ruta. 
Este es un método de acceso directo que se usa a menudo. En algunos escenarios, se puede querer personalizar la regla 
de URL configurando sus otras propiedades, tales como [[yii\web\UrlRule::suffix]]. Esto se puede hacer usando una 
array completo de configuración para especificar una regla. El siguiente ejemplo se ha extraído de la subsección 
[Sufijos de URL](#url-suffixes).

```php
[
    // ... otras reglas de URL ...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

> Info: De forma predeterminada si no se especifica una opción `class` para una configuración de regla, se 
  usará la clase predeterminada [[yii\web\UrlRule]].


### Adición de Reglas Dinámicamente <span id="adding-rules"></span>

Las reglas de URL se pueden añadir dinámicamente en el [[yii\web\UrlManager|URL manager]]. A menudo se necesita por 
[módulos](structure-modules.md) redistribubles que se encargan de gestionar sus propias reglas de URL. Para que las 
reglas añadidas dinámicamente tenga efecto durante el proceso de enrutamiento, se deben añadir durante la etapa 
[bootstrapping](runtime-bootstrapping.md). Para los módulos, esto significa que deben implementar 
[[yii\base\BootstrapInterface]] y añadir las reglas en el método 
[[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] como en el siguiente ejemplo:

```php
public function bootstrap($app)
{
    $app->getUrlManager()->addRules([
        // declaraciones de reglas aquí
    ], false);
}
```

Hay que tener en cuenta se deben añadir estos módulos en [[yii\web\Application::bootstrap]] para que puedan participar 
en el proceso de [bootstrapping](runtime-bootstrapping.md)

### Creación de Clases de Reglas <span id="creating-rules"></span>

A pesar del hecho de que de forma predeterminada la clase [[yii\web\UrlRule]] lo suficientemente flexible para la 
mayoría de proyectos, hay situaciones en las que se tiene que crear una clase de reglas propia. Por ejemplo, en un 
sitio Web de un concesionario de coches, se puede querer dar soporte a las URL con el siguiente formato 
`/Manufacturer/Model`, donde tanto `Manufacturer` como `Model` tengan que coincidir con algún dato almacenado una 
tabla de la base de datos. De forma predeterminada, la clase regla no puede gestionar estas reglas ya que se base en 
patrones estáticos declarados.

Podemos crear la siguiente clase de reglas de URL para solucionar el problema.

```php
namespace app\components;

use yii\web\UrlRuleInterface;
use yii\base\BaseObject;

class CarUrlRule extends BaseObject implements UrlRuleInterface
{

    public function createUrl($manager, $route, $params)
    {
        if ($route === 'car/index') {
            if (isset($params['manufacturer'], $params['model'])) {
                return $params['manufacturer'] . '/' . $params['model'];
            } elseif (isset($params['manufacturer'])) {
                return $params['manufacturer'];
            }
        }
        return false;  // no se aplica esta regla
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            // comprueba $matches[1] y $matches[3] para ver
            // si coincide con un *manufacturer* y un *model* en la base de datos
            // Si coinciden, establece $params['manufacturer'] y/o $params['model']
            // y devuelve ['car/index', $params]
        }
        return false;  // no se aplica la regla
    }
}
```

Y usa la nueva clase de regla en la configuración de [[yii\web\UrlManager::rules]]:

```php
[
    // ... otras reglas ...
    
    [
        'class' => 'app\components\CarUrlRule', 
        // ... configura otras propiedades ...
    ],
]
```

## Consideración del Rendimiento <span id="performance-consideration"></span>

Cuando se desarrolla una aplicación Web compleja, es importante optimizar las reglas de URL para que tarden el mínimo 
tiempo posible en convertir las peticiones y crear URLs.

Usando rutas parametrizadas se puede reducir el numero de reglas de URL que a su vez significa una mejor en el 
rendimiento.

Cuando se convierten o crean URLs, el [[yii\web\UrlManager|URL manager]] examina las reglas de URL en el orden en que 
han sido declaradas. Por lo tanto, se debe tener en cuenta el orden de las reglas de URL y anteponer las reglas más 
especificas y/o las que se usen más a menudo.

Si algunas URLs comparten el mismo prefijo en sus patrones o rutas, se puede considerar usar [[yii\web\GroupUrlRule]] 
ya que puede ser más eficiente al ser examinado por [[yii\web\UrlManager|URL manager]] como un grupo. Este suele ser 
el caso cuando una aplicación se compone por módulos, y cada uno tiene su propio conjunto de reglas con un ID de 
módulo para sus prefijos más comunes.
