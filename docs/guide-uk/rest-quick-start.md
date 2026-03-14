Швидкий старт
===========

Yii включає повноцінний набір засобів для спрощеної реалізації [RESTful API](https://ru.wikipedia.org/wiki/REST).
Зокрема, це такі можливості:

* Швидке створення прототипів за допомогою поширених API до [Active Record](db-active-record.md);
* Налаштування формату відповіді (JSON та XML реалізовані за замовчуванням);
* Отримання серіалізованих об'єктів із необхідною вам вибіркою полів;
* Належне форматування даних та помилок при їх валідації;
* Колекція пагінацій, фільтрів та сортувань;
* Підтримка [HATEOAS](https://uk.wikipedia.org/wiki/HATEOAS);
* Ефективна маршрутизація з належною перевіркою методів HTTP;
* Вбудована підтримка методів `OPTIONS` та `HEAD`;
* Аутентифікація та авторизація;
* HTTP кешування та кешування даних;
* Налаштування обмеження для частоти запитів ([Rate limiting](rest-rate-limiting.md));


Розглянемо приклад, як можна налаштувати Yii під RESTful API, доклавши при цьому мінімум зусиль.

Припустимо, ви захотіли RESTful API для даних по користувачам. Ці дані зберігаються в базі даних та для роботи з ними
вами була раніше створена модель [[yii\db\ActiveRecord|ActiveRecord]]  (клас `app\models\User`).


## Створення контролера <span id="creating-controller"></span>

По-перше, створимо клас контролера `app\controllers\UserController`:

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
}
```

Клас контролера успадковується від [[yii\rest\ActiveController]]. Ми задали [[yii\rest\ActiveController::modelClass|modelClass]]
як `app\models\User`, цим вказавши контролеру, до якої моделі йому необхідно звертатися для редагування чи
вибірки даних.


## Налаштування правил URL <span id="configuring-url-rules"></span>

Далі змінимо налаштування компонента `urlManager` у конфігурації додатку:

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

Установи вище додають правило для контролера `user`, яке надає доступ до даних користувача через красиві URL та логічні методи запитів HTTP.


## Увімкнення JSON на прийом даних<span id="enabling-json-input"></span>

Для того, щоб API міг приймати дані у форматі JSON, налаштуйтє [[yii\web\Request::$parsers|parsers]] властивість у компонента `request` [application component](structure-application-components.md) на використання [[yii\web\JsonParser]] JSON даних на вході:

```php
'request' => [
    'parsers' => [
        'application/json' => 'yii\web\JsonParser',
    ]
]
```

> Note: Конфігурація, наведена вище, необов'язкова. Без наведеної вище конфігурації, API зможе визначити лише
  `application/x-www-form-urlencoded` и `multipart/form-data` формати.


## Пробуємо <span id="trying-it-out"></span>

Ось так просто ми створили RESTful API для доступу до даних користувача. API нашого сервісу зараз включає в себе:

* `GET /users`: отримання посторінкового списку всіх користувачів;
* `HEAD /users`: отримання метаданих лістингу користувачів;
* `POST /users`: створення нового користувача;
* `GET /users/123`: отримання інформації щодо конкретного користувача з id рівним 123;
* `HEAD /users/123`: отримання метаданих за конкретним користувачем з id рівним 123;
* `PATCH /users/123` та `PUT /users/123`: редагування інформації щодо користувача з id рівним 123;
* `DELETE /users/123`: видалення користувача з id рівним 123;
* `OPTIONS /users`: отримання підтримуваних методів, за якими можна звернутися до `/users`;
* `OPTIONS /users/123`: отримання підтримуваних методів, за якими можна звернутися до `/users/123`.

Пробуємо отримати відповіді по API використовуючи `curl`: 

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

Спробуйте змінити заголовок допустимого формату ресурсу на `application/xml` і у відповідь ви отримаєте результат у форматі XML:

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

> Tip: Ви можете отримати доступ до API через веб-браузер, ввівши адресу `http://localhost/users`. Але в цьому випадку
для передачі певних заголовків вам, швидше за все, потрібні додаткові плагіни для браузера.

Якщо уважно подивитися результат відповіді, то можна виявити, що в заголовках є інформація про загальну кількість записів,
кількості сторінок і т. д. Тут також можна виявити посилання на інші сторінки, як, наприклад,
`http://localhost/users?page=2`. Перейшовши по ній, можна отримати другу сторінку даних користувачів.

Використовуючи параметри `fields` та `expand` в URL, можна вказати, які поля мають бути включені до результату. Наприклад,
за адресою `http://localhost/users?fields=id,email` ми отримаємо інформацію щодо користувачів, яка міститиме
тільки `id` та `email`.

> Info: Ви, напевно, помітили, що при зверненні до `http://localhost/users` ми отримуємо інформацію з полями, 
> які небажано показувати, такими як `password_hash` та `auth_key`. Ви можете і повинні видалити ці поля, як описано у 
> розділі «[Ресурси](rest-resources.md)».

Додатково ви можете відсортувати колекції як `http://localhost/users?sort=email` або
`http://localhost/users?sort=-email`. Фільтрування колекцій як `http://localhost/users?filter[id]=10` або
`http://localhost/users?filter[email][like]=gmail.com` можлива при використанні
фільтрів даних. Докладніше у розділі [Resources](rest-resources.md#filtering-collections).

## Резюме <span id="summary"></span>

Використовуючи Yii, як RESTful API фреймворк, ми реалізуємо точки входу API як дії контролерів.
Контролер використовується для організації дій, що належать до певного типу ресурсу.

Ресурси представлені як моделі даних, які успадковуються від класу [[yii\base\Model]].
Якщо потрібна робота з базами даних (як із реляційними, так і з NoSQL), рекомендується використовувати для представлення
ресурсів [[yii\db\ActiveRecord|ActiveRecord]].

Ви можете використовувати [[yii\rest\UrlRule]] для спрощення маршрутизації точок входу API.

Хоча це не обов'язково, рекомендується відокремлювати RESTful APIs додаток від основного веб-додатку. Такий поділ
легше підтримувати.
