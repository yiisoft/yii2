Быстрый старт
===========

Yii включает полноценный набор средств для упрощённой реализации [RESTful API](https://ru.wikipedia.org/wiki/REST).
В частности это следующие возможности:

* Быстрое создание прототипов с поддержкой распространенных API к [Active Record](db-active-record.md);
* Настройка формата ответа (JSON и XML реализованы по-умолчанию);
* Получение сериализованных объектов с нужной вам выборкой полей;
* Надлежащее форматирование данных и ошибок при их валидации;
* Поддержка [HATEOAS](http://en.wikipedia.org/wiki/HATEOAS);
* Эффективная маршрутизация с надлежащей проверкой HTTP методов;
* Встроенная поддержка методов `OPTIONS` и `HEAD`;
* Аутентификация и авторизация;
* HTTP кэширование и кэширование данных;
* Настройка ограничения для частоты запросов (Rate limiting);


Рассмотрим пример, как можно настроить Yii под RESTful API, приложив при этом минимум усилий.

Предположим, вы захотели RESTful API для данных по пользователям. Эти данные хранятся в базе данных и для работы с ними вами была ранее создана модель [[yii\db\ActiveRecord|ActiveRecord]]  (класс `app\models\User`).


## Создание контроллера <a name="creating-controller"></a>

Во-первых, создадим класс контроллера `app\controllers\UserController`:

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
}
```

Как видно из вышеприведённого кода, класс контроллера наследуется от [[yii\rest\ActiveController]]. 
Так же мы указали [[yii\rest\ActiveController::modelClass|modelClass]] как `app\models\User`, тем самым рассказав контроллеру,  
к какой модели ему необходимо обращаться для редактирования или выборки данных.


## Настройка URL правил <a name="configuring-url-rules"></a>

Для того, чтобы можно было обращаться к действиям контроллера, используя различные методы HTTP (GET, POST, HEAD и т.д.), необходимо настроить компонент `urlManager`. Настаивается он в конфигурационном файле приложения с помощью следующего кода:

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

## Пробуем <a name="trying-it-out"></a>

Вот так просто мы и создали RESTful API для доступа к данным `User`. Api нашего сервиса, сейчас включает в себя:

* `GET /users`: получение постранично списка всех пользователей;
* `HEAD /users`: получение метаданных листинга пользователей;
* `POST /users`: создание нового пользователя;
* `GET /users/123`: получение информации по конкретному пользователю с id равным 123;
* `HEAD /users/123`: получение метаданных по конкретному пользователю с id равным 123;
* `PATCH /users/123` и `PUT /users/123`: изменение информации по пользователю с id равным 123;
* `DELETE /users/123`: удаление пользователя с id равным 123;
* `OPTIONS /users`: получение поддерживаемых методов, по которым можно обратится к `/users`;
* `OPTIONS /users/123`: получение поддерживаемых методов, по которым можно обратится к `/users/123`.

> Информация: Yii автоматически сопоставляет имена контроллеров во множественном числе и url адреса.

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

> Подсказка: Вы также можете получить доступ к API, через веб-браузер, введя этот адрес `http://localhost/users`. Но в этом случае, скорее всего, вам потребуются некоторые плагины для браузера, чтобы передать определенные заголовки запросов.

Если внимательно посмотреть результат ответа, то можно обнаружить, что в
заголовках есть информация о суммарном подсчете, количество страниц и т.д.
Тут так же можно обнаружить ссылки на другие страницы, например как эта
`http://localhost/users?page=2`, перейдя по которой можно получить вторую страницу
данных по пользователям.

Используя `fields` и `expand` параметры в url адресе, можно также указать, какие поля должны быть включены в результат.
Например, перейдя по адресу `http://localhost/users?fields=id,email` мы получим информацию по пользователям, которая будет содержать только `id` и `email`.


> Информация: Вы наверное заметили, что при обращении по `http://localhost/users` мы получаем информацию с полями, которые нежелательно показывать,
> такие как `password_hash`, `auth_key`.
> Их скрыть очень просто. Для этого обратитесь к
> разделу [Форматирование ответа](rest-response-formatting.md).


## Резюме <a name="summary"></a>

Использования Yii в качестве RESTful API фреймворка, мы используем действия контроллеров, как различные методы API, с помощью которых происходит обращение к определённому ресурсу.

Ресурсы представлены в виде моделей данных, которые наследуются от класса [[yii\base\Model]].
Если необходима работа с базами данных (реляционные или NoSQL), то рекомендуется использовать [[yii\db\ActiveRecord|ActiveRecord]] в качестве модели.

Используйте [[yii\rest\UrlRule]] для настройки маршрутизации конечных url для API.

Хоть это не обязательно, но рекомендуется отделять RESTful APIs приложения от основного веб-приложения. Такое разделение легче обслуживается. 

