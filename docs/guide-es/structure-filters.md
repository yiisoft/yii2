Filtros
=======

Los Filtros (filters) son objetos que se ejecutan antes y/o después de las 
[acciones de controlador](structure-controllers.md#actions). Por ejemplo, un filtro de control de acceso puede 
ejecutarse antes de las acciones para asegurar que un usuario final tiene permitido acceder a estas; un filtro de 
compresión de contenido puede ejecutarse después de las acciones para comprimir el contenido de la respuesta antes de 
ser enviado al usuario final.

Un filtro puede consistir en un pre-filtro (lógica de filtrado aplicada *antes* de las acciones) y/o un post-filtro 
(lógica de filtro aplicada *después* de las acciones).

## Uso de Filtros <span id="using-filters"></span>

Los filtros son esencialmente un tipo especial de [comportamientos (behaviors)](concept-behaviors.md).
Por lo tanto, usar filtros es lo mismo que [uso de comportamientos](concept-behaviors.md#attaching-behaviors). Se 
pueden declarar los filtros en una clase controlador sobrescribiendo el método 
[[yii\base\Controller::behaviors()|behaviors()]] como en el siguiente ejemplo:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index', 'view'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

Por defecto, los filtros declarados en una clase controlador, serán aplicados en *todas* las acciones de este 
controlador. Sin embargo, se puede especificar explícitamente en que acciones serán aplicadas configurando la 
propiedad [[yii\base\ActionFilter::only|only]]. En el anterior ejemplo, el filtro 'HttpCache' solo se aplica a las 
acciones 'index' y 'view'. También se puede configurar la propiedad [[yii\base\ActionFilter::except|except]] para 
prevenir que ciertas acciones sean filtradas.

Además de en los controladores, se pueden declarar filtros en [módulos](structure-modules.md) o 
[aplicaciones](structure-applications.md).
Una vez hecho, los filtros serán aplicados a *todas* las acciones de controlador que pertenezcan a ese modulo o 
aplicación, a menos que las propiedades [[yii\base\ActionFilter::only|only]] y [[yii\base\ActionFilter::except|except]]
 sean configuradas como se ha descrito anteriormente.

> Nota: Cuando se declaran filtros en módulos o aplicaciones, deben usarse [rutas](structure-controllers.md#routes) en 
  lugar de IDs de acciones en las propiedades [[yii\base\ActionFilter::only|only]] y 
  [[yii\base\ActionFilter::except|except]]. Esto es debido a que los IDs de acciones no pueden especificar acciones 
  dentro del ámbito de un modulo o una aplicación por si mismos.

Cuando se configuran múltiples filtros para una misma acción, se aplican de acuerdo a las siguientes reglas:

* Pre-filtrado
    - Aplica filtros declarados en la aplicación en orden de aparición en `behaviors()`.
    - Aplica filtros declarados en el modulo en orden de aparición en `behaviors()`.
    - Aplica filtros declarados en el controlador en orden de aparición en `behaviors()`.
    - Si hay algún filtro que cancele la ejecución de la acción, los filtros(tanto pre-filtros como post-filtros) 
      posteriores a este no serán aplicados.
* Ejecución de la acción si pasa el pre-filtro.
* Post-filtrado
    - Aplica los filtros declarados en el controlador en el controlador en orden inverso al de aparición en 
      `behaviors()`.
    - Aplica los filtros declarados en el modulo en orden inverso al de aparición en `behaviors()`.
    - Aplica los filtros declarados en la aplicación en orden inverso al de aparición en `behaviors()`.

##Creación de Filtros <span id="creating-filters"></span>

Para crear un nuevo filtro de acción, hay que extender a [[yii\base\ActionFilter]] y sobrescribir los métodos 
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] y/o [[yii\base\ActionFilter::afterAction()|afterAction()]]. 
El primero será ejecutado antes de la acción mientras que el segundo lo hará una vez ejecutada la acción.
El valor devuelto por [[yii\base\ActionFilter::beforeAction()|beforeAction()]] determina si una acción debe ejecutarse 
o no. Si el valor es falso, los filtros posteriores a este serán omitidos y la acción no será ejecutada.

El siguiente ejemplo muestra un filtro que registra el tiempo de ejecución de una acción:

```php
namespace app\components;

use Yii;
use yii\base\ActionFilter;

class ActionTimeFilter extends ActionFilter
{
    private $_startTime;

    public function beforeAction($action)
    {
        $this->_startTime = microtime(true);
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        $time = microtime(true) - $this->_startTime;
        Yii::trace("Action '{$action->uniqueId}' spent $time second.");
        return parent::afterAction($action, $result);
    }
}
```

## Filtros del Núcleo <span id="core-filters"></span>

Yii proporciona una serie de filtros de uso general, que se encuentran principalmente en `yii\filters` namespace. En 
adelante introduciremos estos filtros brevemente.

### [[yii\filters\AccessControl|AccessControl]] <span id="access-control"></span>

AccessControl proporciona control de acceso simple basado en un conjunto de [[yii\filters\AccessControl::rules|rules]].
En concreto, antes de ejecutar una acción, AccessControl examinará la lista de reglas y encontrará la primera que 
concuerde con las actuales variables de contexto(tales como dirección IP de usuario, estado de inicio de sesión del 
usuario, etc.). La regla que concuerde dictara si se permite o deniega la ejecución de la acción solicitada. Si 
ninguna regla concuerda, el acceso será denegado.

El siguiente ejemplo muestra como habilitar el acceso a los usuarios autenticados a las acciones 'create' y 'update' 
mientras deniega a todos los otros usuarios el acceso a estas dos acciones.


```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::className(),
            'only' => ['create', 'update'],
            'rules' => [
                // permitido para usuarios autenticados
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // todo lo demás se deniega por defecto
            ],
        ],
    ];
}
```

Para conocer más detalles acerca del control de acceso en general, refiérase a la sección de 
[Autorización](security-authorization.md)

### Filtros del Método de Autenticación <span id="auth-method-filters"></span>

Los filtros del método de autenticación se usan para autenticar a un usuario utilizando varios métodos, tales como la 
[Autenticación de acceso básico HTTP](http://es.wikipedia.org/wiki/Autenticaci%C3%B3n_de_acceso_b%C3%A1sica), 
[Oauth 2](http://oauth.net/2/). Estas clases de filtros se encuentran en el espacio de nombres `yii\filters\auth`.

El siguiente ejemplo muestra como usar [[yii\filters\auth\HttpBasicAuth]] para autenticar un usuario usando un token 
de acceso basado en el método de Autenticación de acceso básico HTTP. Tenga en cuenta que para que esto funcione, la 
clase [[yii\web\User::identityClass|user identity class]] debe implementar el método 
[[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]].

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    return [
        'basicAuth' => [
            'class' => HttpBasicAuth::className(),
        ],
    ];
}
```

Los filtros del método de autenticación se usan a menudo para implementar APIs RESTful. Para más detalles, por favor 
refiérase a la sección [Autenticación RESTful](rest-authentication.md).

[[yii\filters\ContentNegotiator|ContentNegotiator]] 
El filtro ContentNegotiator da soporte a la negociación del formato de respuesta y a la negociación del idioma de la 
aplicación. Este determinara el formato de respuesta y/o el idioma examinando los parámetros 'GET' y 'Accept' del 
encabezado HTTP.

En el siguiente ejemplo, el filtro ContentNegotiator se configura para soportar los formatos de respuesta 'JSON' y 
'XML', y los idiomas Ingles(Estados Unidos) y Alemán.

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

public function behaviors()
{
    return [
        [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ];
}
```

Los formatos de respuesta y los idiomas a menudo precisan ser determinados mucho antes durante el 
[ciclo de vida de la aplicación](structure-applications.md#application-lifecycle). Por esta razón, ContentNegotiator 
esta diseñado de tal manera que se pueda usar como componente de [bootstrapping](structure-applications.md#bootstrap) 
así como de filtro. Por ejemplo, ContentNegotiator se puede configurar en la configuración de la aplicación como en el 
siguiente ejemplo:

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

[
    'bootstrap' => [
        [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ],
];
```

> Información: En el caso que el tipo preferido de contenido y el idioma no puedan ser determinados por una petición, 
  será utilizando el primer elemento de formato e idioma de la lista [[formats]] y [[lenguages]].


### [[yii\filters\HttpCache|HttpCache]] <span id="http-cache"></span>

HttpCache implementa un almacenamiento caché del lado del cliente utilizando las cabeceras HTTP 'Last-Modified' y 
'Etag'. Por ejemplo:

```php
use yii\filters\HttpCache;

public function behaviors()
{
    return [
        [
            'class' => HttpCache::className(),
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

Para conocer más detalles acerca de HttpCache refiérase a la sección [almacenamiento caché HTTP](caching-http.md).

### [[yii\filters\PageCache|PageCache]] <span id="page-cache"></span>

PageCache implementa una caché por parte del servidor de paginas enteras. En el siguiente ejemplo, se aplica PageCache 
a la acción 'index' para generar una cache de la pagina entera durante 60 segundos como máximo o hasta que el contador 
de entradas de la tabla 'post' varíe. También se encarga de almacenar diferentes versiones de la pagina dependiendo 
del idioma de la aplicación seleccionado.

```php
use yii\filters\PageCache;
use yii\caching\DbDependency;

public function behaviors()
{
    return [
        'pageCache' => [
            'class' => PageCache::className(),
            'only' => ['index'],
            'duration' => 60,
            'dependency' => [
                'class' => DbDependency::className(),
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
            'variations' => [
                \Yii::$app->language,
            ]
        ],
    ];
}
```

Por favor refiérase a [Caché de Páginas](caching-page.md) para obtener más detalles acerca de como usar PageCache.

### [[yii\filters\RateLimiter|RateLimiter]] <span id="rate-limiter"></span>

RateLimiter implementa un algoritmo de para limitar la tasa de descarga basándose en 
(leaky bucket algorithm)[http://en.wikipedia.org/wiki/Leaky_bucket]. Este se utiliza sobre todo en la implementación 
de APIs RESTful. Por favor, refiérase a la sección (limite de tasa)[rest-rate-limiting.md] para obtener más detalles 
acerca de el uso de este filtro.

### [[yii\filters\VerbFilter|VerbFilter]] <span id="verb-filter"></span>

VerbFilter comprueba que los métodos de las peticiones HTTP estén permitidas para las acciones solicitadas. Si no 
están permitidas, lanzara una excepción de tipo HTTP 405. En el siguiente ejemplo, se declara VerbFilter para 
especificar el conjunto típico métodos de petición permitidos para acciones CRUD.

```php
use yii\filters\VerbFilter;

public function behaviors()
{
    return [
        'verbs' => [
            'class' => VerbFilter::className(),
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['get', 'post'],
                'update' => ['get', 'put', 'post'],
                'delete' => ['post', 'delete'],
            ],
        ],
    ];
}
```

### [[yii\filters\Cors|Cors]] <span id="cors"></span>

(CORS)[https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS] es un mecanismo que permite a diferentes 
recursos (por ejemplo: fuentes, JavaScript, etc) de una pagina Web ser solicitados por otro dominio diferente al 
dominio que esta haciendo la petición. En particular las llamadas AJAX de JavaScript pueden utilizar el mecanismo 
XMLHttpRequest. De otro modo esta petición de dominio cruzado seria prohibida por los navegadores Web, por la misma 
pollita de seguridad de origen. CORS establece la manera en que el navegador y el servidor pueden interaccionar para 
determinar si se permite o no la petición de dominio cruzado. El filtro [[yii\filters\Cors|Cors filter]] puede ser 
definido antes de los filtros Autenticación / Autorización para asegurar que las cabeceras de CORS siempre serán 
enviadas.

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::className(),
        ],
    ], parent::behaviors());
}
```

El filtrado CORS puede ser ajustado utilizando la propiedad 'cors'.

* `cors['Origin']`: array utilizado para definir los orígenes permitidos. Puede ser `['*']` (everyone) o 
  `['http://www.myserver.net', 'http://www.myotherserver.com']`. Por defecto `['*']`.
* `cors['Access-Control-Request-Method']`: array de los verbos permitidos como `['GET', 'OPTIONS', 'HEAD']`.  Por 
  defecto `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`.
* `cors['Access-Control-Request-Headers']`: array de las cabeceras permitidas. Puede ser `['*']` todas las cabeceras o 
  algunas especificas `['X-Request-With']`. Por defecto `['*']`.
* `cors['Access-Control-Allow-Credentials']`: define si la petición actual puede hacer uso de credenciales. Puede ser 
  `true`, `false` o `null` (not set). Por defecto `null`.
* `cors['Access-Control-Max-Age']`: define el tiempo de vida del la petición pref-flight. Por defecto `86400`. Por 
  ejemplo, habilitar CORS para el origen: `http://www.myserver.net` con métodos `GET`, `HEAD` y `OPTIONS`:

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
        ],
    ], parent::behaviors());
}
```

Se pueden ajustar las cabeceras de CORS sobrescribiendo los parámetros por defecto de una acción. Por ejemplo añadir 
`Access-Control-Allow-Credentials` a la acción 'login', se podría hacer así:

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
            'actions' => [
                'login' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ]
        ],
    ], parent::behaviors());
}
```
