Быстрый старт
===========

Yii включает полноценный набор средств для упрощённой реализации [RESTful API](https://ru.wikipedia.org/wiki/REST).
В частности это следующие возможности:

* Быстрое создание прототипов с поддержкой распространенных API к [Active Record](db-active-record.md);
* Настройка формата ответа (JSON и XML реализованы по умолчанию);
* Получение сериализованных объектов с нужной вам выборкой полей;
* Надлежащее форматирование данных и ошибок при их валидации;
* Коллекция пагинаций, фильтров и сортировок;
* Поддержка [HATEOAS](https://ru.wikipedia.org/wiki/HATEOAS);
* Эффективная маршрутизация с надлежащей проверкой HTTP методов;
* Встроенная поддержка методов `OPTIONS` и `HEAD`;
* Аутентификация и авторизация;
* HTTP кэширование и кэширование данных;
* Настройка ограничения для частоты запросов (Rate limiting);


Рассмотрим пример, как можно настроить Yii под RESTful API, приложив при этом минимум усилий.

Предположим, вы захотели RESTful API для данных по пользователям. Эти данные хранятся в базе данных и для работы с ними
вами была ранее создана модель [[yii\db\ActiveRecord|ActiveRecord]]  (класс `app\models\User`).


## Создание контроллера <span id="creating-controller"></span>

Во-первых, создадим класс контроллера `app\controllers\UserController`:

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
}
```

Класс контроллера наследуется от [[yii\rest\ActiveController]]. Мы задали [[yii\rest\ActiveController::modelClass|modelClass]]
как `app\models\User`, тем самым указав контроллеру, к какой модели ему необходимо обращаться для редактирования или
выборки данных.


## Настройка правил URL <span id="configuring-url-rules"></span>

Далее изменим настройки компонента `urlManager` в конфигурации приложения:

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
    ],
]
```

Настройки выше добавляют правило для контроллера `user`, которое предоставляет доступ к данным пользователя через красивые
URL и логичные глаголы HTTP.


## Включение JSON на прием данных<span id="enabling-json-input"></span>

Для того чтобы API мог принимать данные в формате JSON, сконфигурируйте [[yii\web\Request::$parsers|parsers]] свойство у компонента `request` [application component](structure-application-components.md) на использование [[yii\web\JsonParser]] JSON данных на входе:

```php
'request' => [
    'parsers' => [
        'application/json' => 'yii\web\JsonParser',
    ]
]
```

> Note: Конфигурация, приведенная выше необязательна. Без приведенной выше конфигурации, API сможет определить только
  `application/x-www-form-urlencoded` и `multipart/form-data` форматы.


## Пробуем <span id="trying-it-out"></span>

Вот так просто мы и создали RESTful API для доступа к данным пользователя. API нашего сервиса сейчас включает в себя:

* `GET /users`: получение постранично списка всех пользователей;
* `HEAD /users`: получение метаданных листинга пользователей;
* `POST /users`: создание нового пользователя;
* `GET /users/123`: получение информации по конкретному пользователю с id равным 123;
* `HEAD /users/123`: получение метаданных по конкретному пользователю с id равным 123;
* `PATCH /users/123` и `PUT /users/123`: изменение информации по пользователю с id равным 123;
* `DELETE /users/123`: удаление пользователя с id равным 123;
* `OPTIONS /users`: получение поддерживаемых методов, по которым можно обратится к `/users`;
* `OPTIONS /users/123`: получение поддерживаемых методов, по которым можно обратится к `/users/123`.

Пробуем получить ответы по API используя `curl`: 

```
$ curl -i -H "Accept:application/json" "http://localhost/users"

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

Попробуйте изменить заголовок допустимого формата ресурса на `application/xml`
и в ответ вы получите результат в формате XML:

```
$ curl -i -H "Accept:application/xml" "http://localhost/users"

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
Content-Type: application/xml

<?xml version="1.0" encoding="UTF-8"?>
<response>
    <item>
        <id>1</id>
        ...
    </item>
    <item>
        <id>2</id>
        ...
    </item>
    ...
</response>
```

> Tip: Вы можете получить доступ к API через веб-браузер, введя адрес `http://localhost/users`. Но в этом случае
  для передачи определённых заголовков вам, скорее всего, потребуются дополнительные плагины для браузера.

Если внимательно посмотреть результат ответа, то можно обнаружить, что в заголовках есть информация об общем числе записей,
количестве страниц и т. д. Тут так же можно обнаружить ссылки на другие страницы, как, например,
`http://localhost/users?page=2`. Перейдя по ней можно получить вторую страницу данных пользователей.

Используя параметры `fields` и `expand` в URL, можно указать, какие поля должны быть включены в результат. Например,
по адресу `http://localhost/users?fields=id,email` мы получим информацию по пользователям, которая будет содержать
только `id` и `email`.


> Info: Вы наверное заметили, что при обращении к `http://localhost/users` мы получаем информацию с полями,
> которые нежелательно показывать, такими как `password_hash` и `auth_key`. Вы можете и должны удалить эти поля как
> описано в разделе «[Ресурсы](rest-resources.md)».

Дополнительно, вы можете отсортировать коллекции как `http://localhost/users?sort=email` или
`http://localhost/users?sort=-email`. Фильтрация коллекций как `http://localhost/users?filter[id]=10` или
`http://localhost/users?filter[email][like]=gmail.com` возможна при использовании
фильтров данных. Подробнее в секции [Resources](rest-resources.md#filtering-collections).

## Резюме <span id="summary"></span>

Используя Yii в качестве RESTful API фреймворка, мы реализуем точки входа API как действия контроллеров.
Контроллер используется для организации действий, которые относятся к определённому типу ресурса.

Ресурсы представлены в виде моделей данных, которые наследуются от класса [[yii\base\Model]].
Если необходима работа с базами данных (как с реляционными, так и с NoSQL), рекомендуется использовать для представления
ресурсов [[yii\db\ActiveRecord|ActiveRecord]].

Вы можете использовать [[yii\rest\UrlRule]] для упрощения маршрутизации точек входа API.

Хоть это не обязательно, рекомендуется отделять RESTful APIs приложение от основного веб-приложения. Такое разделение
легче обслуживается.
