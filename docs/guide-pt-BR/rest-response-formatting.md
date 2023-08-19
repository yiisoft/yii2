Formatando Respostas
===================

Ao manipular uma requisição da API RESTful, a aplicação normalmente realiza as seguintes etapas que estão relacionadas com a formatação da resposta:

1. Determinar diversos fatores que podem afetar o formato da resposta, tais como tipo de mídia, idioma, versão, etc. Este processo também é conhecido como [negociação de conteúdo (*content negotiation*)](https://en.wikipedia.org/wiki/Content_negotiation).
2. Converter objetos de recursos em arrays, como descrito na seção [Recursos](rest-resources.md). Isto é feito por [[yii\rest\Serializer]].
3. Converte arrays em uma string no formato como determinado pela etapa de negociação de conteúdo. Isto é feito pelos [[yii\web\ResponseFormatterInterface|formatadores de respostas]] registrados na propriedade [[yii\web\Response::formatters|formatters]] do [componente de aplicação](structure-application-components.md) `response`.


## Negociação de Conteúdo <span id="content-negotiation"></span>

O Yii suporta a negociação de conteúdo através do filtro [[yii\filters\ContentNegotiator]]. A classe base de controller API RESTful [[yii\rest\Controller]] está equipado com este filtro sob o nome de `contentNegotiator`. O filtro fornece negociação de formato de resposta, bem como negociação de idioma. Por exemplo, se uma requisição da API RESTful tiver o seguinte cabeçalho,

```
Accept: application/json; q=1.0, */*; q=0.1
```

ele obterá uma resposta em formato JSON, como o seguinte:

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

Por baixo dos panos, antes de uma ação do controlador API RESTful ser executada, o filtro [[yii\filters\ContentNegotiator]] verificará o `Accept` do cabeçalho HTTP na requisição e definirá o [[yii\web\Response::format|response format]] para `'json'`. Após a ação ser executada e retornar o objeto resultante de recursos ou coleção, [[yii\rest\Serializer]] converterá o resultado em um array. E finalmente, [[yii\web\JsonResponseFormatter]] irá serializar o array em uma string JSON e incluí-la no corpo da resposta.

Por padrão, APIs RESTful suportam tanto os formatos JSON quanto XML. Para suportar um novo formato, você deve configurar a propriedade [[yii\filters\ContentNegotiator::formats|formats]] do filtro `contentNegotiator` como mostrado no exemplo a seguir em suas classes do controlador da API:

```php
use yii\web\Response;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
    return $behaviors;
}
```


As chaves da propriedade `formats` são os tipos MIME suportados, enquanto os valores são os nomes de formato de resposta correspondentes que devem ser suportados em
 [[yii\web\Response::formatters]].


## Serializando Dados <span id="data-serializing"></span>

Como foi descrito acima, [[yii\rest\Serializer]] é a peça central responsável pela conversão de objetos de recursos ou coleções em arrays. Ele reconhece objetos que implementam a interface [[yii\base\ArrayableInterface]] bem como [[yii\data\DataProviderInterface]]. O primeiro é aplicado principalmente pelos objetos de recursos, enquanto o último se aplica mais a coleções de recursos.

Você pode configurar o serializador, definindo a propriedade [[yii\rest\Controller::serializer]] com um array de configuração.
Por exemplo, às vezes você pode querer ajudar a simplificar o trabalho de desenvolvimento do cliente, incluindo informações de paginação diretamente no corpo da resposta. Para fazê-lo, configure a propriedade [[yii\rest\Serializer::collectionEnvelope]] como a seguir:

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

Você pode, então, obter a seguinte resposta para a url `http://localhost/users`:

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

