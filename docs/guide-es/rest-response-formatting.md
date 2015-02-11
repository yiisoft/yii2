Formato de Respuesta
====================

Cuando se maneja una petición al API RESTful, una aplicación realiza usualmente los siguientes pasos que están relacionados
con el formato de la respuesta:

1. Determinar varios factores que pueden afectar al formato de la respuesta, como son el tipo de medio, lenguaje, versión, etc.
   Este proceso es también conocido como [negociación de contenido (content negotiation)](http://en.wikipedia.org/wiki/Content_negotiation).
2. La conversión de objetos recurso en arrays, como está descrito en la sección [Recursos (Resources)](rest-resources.md).
   Esto es realizado por la clase [[yii\rest\Serializer]].
3. La conversión de arrays en cadenas con el formato determinado por el paso de negociación de contenido. Esto es
   realizado por los [[yii\web\ResponseFormatterInterface|response formatters]] registrados con el
   componente de la aplicación [[yii\web\Response::formatters|response]].


## Negociación de contenido (Content Negotiation) <span id="content-negotiation"></span>

Yii soporta la negociación de contenido a través del filtro [[yii\filters\ContentNegotiator]]. La clase de controlador base
del API RESTful [[yii\rest\Controller]] está equipada con este filtro bajo el nombre `contentNegotiator`.
El filtro provee tanto un formato de respuesta de negociación como una negociación de lenguaje. Por ejemplo, si la petición API RESTful
contiene la siguiente cabecera,

```
Accept: application/json; q=1.0, */*; q=0.1
```

puede obtener una respuesta en formato JSON, como a continuación:

```
$ curl -i -H "Accept: application/json; q=1.0, */*; q=0.1" "http://localhost/users"

HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

[
    {
        "id": 1,
        ...
    },
    {
        "id": 2,
        ...
    },
    ...
]
```

Detrás de escena, antes de que sea ejecutada una acción del controlador del API RESTful, el filtro [[yii\filters\ContentNegotiator]]
comprobará la cabecera HTTP `Accept` de la petición y definirá el [[yii\web\Response::format|response format]]
como `'json'`. Después de que la acción sea ejecutada y devuelva el objeto recurso o la colección resultante,
[[yii\rest\Serializer]] convertirá el resultado en un array. Y finalmente, [[yii\web\JsonResponseFormatter]]
serializará el array en una cadena JSON incluyéndola en el cuerpo de la respuesta.

Por defecto, el API RESTful soporta tanto el formato JSON como el XML. Para soportar un nuevo formato, debes configurar
la propiedad [[yii\filters\ContentNegotiator::formats|formats]] del filtro `contentNegotiator` tal y como sigue,
en las clases del controlador del API:

```php
use yii\web\Response;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
    return $behaviors;
}
```

Las claves de la propiedad `formats` son los tipos MIME soportados, mientras que los valores son los nombres de formato de respuesta correspondientes,
los cuales deben ser soportados en [[yii\web\Response::formatters]].


## Serialización de Datos <span id="data-serializing"></span>

Como hemos descrito antes, [[yii\rest\Serializer]] es la pieza central responsable de convertir
objetos recurso o colecciones en arrays. Reconoce objetos tanto implementando [[yii\base\ArrayableInterface]]
como [[yii\data\DataProviderInterface]]. El primer formateador es implementado principalmente para objetos recursos,
mientras que el segundo para recursos collección.

Puedes configurar el serializador definiendo la propiedad [[yii\rest\Controller::serializer]] con un array de configuración.
Por ejemplo, a veces puedes querer ayudar a simplificar el trabajo de desarrollo del cliente incluyendo información de la paginación
directamente en el cuerpo de la respuesta. Para hacer esto, configura la propiedad [[yii\rest\Serializer::collectionEnvelope]]
como sigue:

```php
use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
}
```

Puedes obtener la respuesta que sigue para la petición `http://localhost/users`:

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "items": [
        {
            "id": 1,
            ...
        },
        {
            "id": 2,
            ...
        },
        ...
    ],
    "_links": {
        "self": {
            "href": "http://localhost/users?page=1"
        },
        "next": {
            "href": "http://localhost/users?page=2"
        },
        "last": {
            "href": "http://localhost/users?page=50"
        }
    },
    "_meta": {
        "totalCount": 1000,
        "pageCount": 50,
        "currentPage": 1,
        "perPage": 20
    }
}
```
