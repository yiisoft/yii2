Форматирование ответа
===================

При обработке RESTful API запросов приложение обычно выполняет следующие шаги, связанные с форматированием ответа:

1. Определяет различные факторы, которые могут повлиять на формат ответа, такие как media type, язык, версия и т.д.
   Этот процесс также известен как [согласование содержимого](http://en.wikipedia.org/wiki/Content_negotiation).
2. Конвертирует объекты ресурсов в массивы, как описано в секции [Ресурсы](rest-resources.md).
   Этим занимается [[yii\rest\Serializer]].
3. Конвертирует массивы в строки исходя из формата, определенного на этапе согласование содержимого. Это задача для
   [[yii\web\ResponseFormatterInterface|форматтера ответов]], регистрируемого с помощью компонента приложения
   [[yii\web\Response::formatters|response]].


## Согласование содержимого <span id="content-negotiation"></span>

Yii поддерживает согласование содержимого с помощью фильтра [[yii\filters\ContentNegotiator]]. Базовый класс
контроллера RESTful API - [[yii\rest\Controller]] - использует этот фильтр под именем `contentNegotiator`.
Фильтр обеспечивает соответствие формата ответа и определяет используемый язык. Например, если RESTful API запрос
содержит следующий заголовок:

```
Accept: application/json; q=1.0, */*; q=0.1
```

Он получит ответ в JSON-формате такого вида:

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

Под капотом происходит следующее: прежде, чем *действие* RESTful API контроллера будет выполнено, фильтр
[[yii\filters\ContentNegotiator]] проверит HTTP-заголовок `Accept` в запросе и установит, что
[[yii\web\Response::format|формат ответа]] должен быть в `'json'`. После того, как *действие* будет выполнено и вернет
итоговый объект ресурса или коллекцию, [[yii\rest\Serializer]] конвертирует результат в массив.
И, наконец, [[yii\web\JsonResponseFormatter]] сериализует массив в строку в формате JSON и включит ее в тело ответа.

По умолчанию, RESTful API поддерживает и JSON, и XML форматы. Для того, чтобы добавить поддержку нового формата,
вы должны установить свою конфигурацию для свойства [[yii\filters\ContentNegotiator::formats|formats]] у фильтра
`contentNegotiator`, например, с использованием поведения такого вида:

```php
use yii\web\Response;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
    return $behaviors;
}
```

Ключи свойства `formats` - это поддерживаемые MIME-типы, а их значения должны соответствовать именам
форматов ответа, которые установлены в [[yii\web\Response::formatters]].


## Сериализация данных <span id="data-serializing"></span>

Как уже описывалось выше, [[yii\rest\Serializer]] - это центральное место, отвечающее за конвертацию объектов ресурсов
или коллекций в массивы. Он реализует интерфейсы [[yii\base\ArrayableInterface]] и [[yii\data\DataProviderInterface]].
Для объектов ресурсов как правило реализуется интерфейс [[yii\base\ArrayableInterface]], а для коллекций -
[[yii\data\DataProviderInterface]].

Вы можете переконфигурировать сериализатор с помощью настройки свойства [[yii\rest\Controller::serializer]], используя
конфигурационный массив. Например, иногда вам может быть нужно помочь упростить разработку клиентской части
приложения с помощью добавления информации о пагинации непосредственно в тело ответа. Чтобы сделать это,
переконфигурируйте свойство [[yii\rest\Serializer::collectionEnvelope]] следующим образом:


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

Тогда вы можете получить следующий ответ на запрос `http://localhost/users`:

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
