Фильтры
=======

Фильтры — это объекты, которые могут запускаться как перед так и после [действий контроллера](structure-controllers.md#actions).
Например, фильтр управления доступом может запускаться перед действиями удостовериться, что запросившему их пользователю
разрешен доступ; фильтр сжатия содержимого может запускаться после действий для сжатия содержимого ответа перед отправкой
его конечному пользователю.

Фильтр может состоять из *пре-фильтра* (фильтрующая логика применяется *перед* действиями) и/или
*пост-фильтра* (логика, применяемая *после* действий).

## Использование фильтров <span id="using-filters"></span>

Фильтры являются особым видом [поведений](concept-behaviors.md). Их использование ничем не отличается от
[использования поведений](concept-behaviors.md#attaching-behaviors). Вы можете объявлять фильтры в классе контроллера
путём перекрытия метода [[yii\base\Controller::behaviors()|behaviors()]]:

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

По умолчанию фильтры, объявленные в классе контроллера, будут применяться ко *всем* его действиям. Тем не менее, вы можете
явно указать и конкретные действия задав свойство [[yii\base\ActionFilter::only|only]]. В примере выше фильтр `HttpCache`
применяется только к действиям `index` и `view`. Вы можете настроить свойство [[yii\base\ActionFilter::except|except]]
чтобы указать действия, к которым фильтр применяться не должен.

Кроме контроллеров, можно объявлять фильтры в [модуле](structure-modules.md) или в [приложении](structure-applications.md).
В этом случае они применяются ко *всем* действиям контроллеров, находящихся в этом модуле или приложении если не заданы
свойства [[yii\base\ActionFilter::only|only]] и [[yii\base\ActionFilter::except|except]] как было описано выше.

> Note: При объявлении фильтров в модулях или приложениях, следует использовать [маршруты](structure-controllers.md#routes)
  вместо идентификаторов действий в свойствах [[yii\base\ActionFilter::only|only]] и [[yii\base\ActionFilter::except|except]]
  так как сами по себе, идентификаторы действий не могут полностью идентифицировать действие в контексте модуля или приложения.

Когда несколько фильтров указываются для одного действия, они применяются согласно следующим правилам:

* Пре-фильтрация
    - Применяются фильтры, объявленные в приложении в том порядке, в котором они перечислены в `behaviors()`.
    - Применяются фильтры, объявленные в модуле в том порядке, в котором они перечислены в `behaviors()`.
    - Применяются фильтры, объявленные в контроллере в том порядке, в котором они перечислены в `behaviors()`.
    - Если, какой-либо из фильтров отменяет выполнение действия, оставшиеся фильтры (как пре-фильтры, так и пост-фильтры) не будут применены.
* Выполняется действие, если оно прошло пре-фильтрацию.
* Пост-фильтрация
    - Применяются фильтры объявленные в контроллере, в порядке обратном, перечисленному в `behaviors()`.
    - Применяются фильтры объявленные в модуле, в порядке обратном, перечисленному в `behaviors()`.
    - Применяются фильтры объявленные в приложении, в порядке обратном, перечисленному в `behaviors()`.


## Создание фильтров <span id="creating-filters"></span>

При создании нового фильтра действия, необходимо наследоваться от [[yii\base\ActionFilter]] и переопределить методы
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] и/или [[yii\base\ActionFilter::afterAction()|afterAction()]].
Первый из них будет вызван перед выполнением действия, а второй после. Возвращаемое
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] значение определяет, будет ли действие выполняться или нет.
Если вернётся `false`, то оставшиеся фильтры не будут применены и действие выполнено не будет.

Пример ниже показывает фильтр, который выводит время выполнения действия:

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
        Yii::debug("Action '{$action->uniqueId}' spent $time second.");
        return parent::afterAction($action, $result);
    }
}
```


## Стандартные фильтры <span id="core-filters"></span>

Yii предоставляет набор часто используемых фильтров, которые находятся, в основном, в пространстве имен `yii\filters`.
Далее вы будете кратко ознакомлены с ними.


### [[yii\filters\AccessControl|AccessControl]] <span id="access-control"></span>

Фильтр `AccessControl` обеспечивает простое управление доступом, основанное на наборе правил [[yii\filters\AccessControl::rules|rules]].
В частности, перед тем как действие начинает выполнение, фильтр `AccessControl` проверяет список указанных правил, пока не
найдёт соответствующее текущему контексту переменных (таких как IP адрес пользователя, статус аутентификации и так далее).
Найденное правило указывает, разрешить или запретить выполнение запрошенного действия. Если ни одно из правил не подходит,
то доступ будет запрещён.

В следующем примере авторизованным пользователям разрешен доступ к действиям `create` и `update`, в то время как всем
другим пользователям доступ запрещён.

```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'only' => ['create', 'update'],
            'rules' => [
                // разрешаем аутентифицированным пользователям
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // всё остальное по умолчанию запрещено
            ],
        ],
    ];
}
```

Более подробно об управлении доступом вы можете прочитать в разделе [Авторизация](security-authorization.md).


### Фильтр метода аутентификации<span id="auth-method-filters"></span>

Фильтр метода аутентификации используется для аутентификации пользователя различными способами, такими как
[HTTP Basic Auth](https://en.wikipedia.org/wiki/Basic_access_authentication), [OAuth 2](https://oauth.net/2/).
Классы данных фильтров находятся в пространстве имён `yii\filters\auth`.

Следующий пример показывает, как использовать [[yii\filters\auth\HttpBasicAuth]] для аутентификации пользователя с помощью
токена доступа, основанного на методе basic HTTP auth. Обратите внимание, что для того чтобы это работало, ваш класс
[[yii\web\User::identityClass|user identity class]] должен реализовывать метод
[[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]].

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    return [
        'basicAuth' => [
            'class' => HttpBasicAuth::class,
        ],
    ];
}
```

Фильтры метода аутентификации часто используются при реализации RESTful API. Более подробную информацию о технологии 
RESTful, смотрите в разделе [Authentication](rest-authentication.md).


### [[yii\filters\ContentNegotiator|ContentNegotiator]] <span id="content-negotiator"></span>

ContentNegotiator поддерживает согласование формата ответа и языка приложения. Он пытается определить формат ответа
и/или язык, путём проверки `GET` параметров и HTTP заголовка `Accept`.

В примере ниже, ContentNegotiator сконфигурирован чтобы поддерживать форматы ответа JSON и XML, а также Английский (США)
и Немецкий языки.

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

public function behaviors()
{
    return [
        [
            'class' => ContentNegotiator::class,
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

Часто требуется, чтобы форматы ответа и языки приложения были определены как можно раньше в его
[жизненном цикле](structure-applications.md#application-lifecycle). По этой причине, ContentNegotiator разработан так, что
помимо фильтра может использоваться как [компонент предварительной загрузки](structure-applications.md#bootstrap). Например,
вы можете настроить его в [конфигурации приложения](structure-applications.md#application-configurations):

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

[
    'bootstrap' => [
        [
            'class' => ContentNegotiator::class,
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

> Info: В случае, если предпочтительный тип содержимого и язык не могут быть определены из запроса, будут
  использованы первый формат и язык, описанные в [[formats]] и [[languages]].



### [[yii\filters\HttpCache|HttpCache]] <span id="http-cache"></span>

Фильтр HttpCache реализовывает кэширование на стороне клиента, используя HTTP заголовки `Last-Modified` и `Etag`:

```php
use yii\filters\HttpCache;

public function behaviors()
{
    return [
        [
            'class' => HttpCache::class,
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

Подробнее об использовании HttpCache можно прочитать в разделе [HTTP Кэширование](caching-http.md).


### [[yii\filters\PageCache|PageCache]] <span id="page-cache"></span>

Фильтр PageCache реализует кэширование целых страниц на стороне сервера. В следующем примере PageCache применяется только
в действии `index` для кэширования всей страницы в течение не более чем 60 секунд или пока количество записей в таблице `post`
не изменится. Он также хранит различные версии страницы в зависимости от выбранного языка приложения.

```php
use yii\filters\PageCache;
use yii\caching\DbDependency;

public function behaviors()
{
    return [
        'pageCache' => [
            'class' => PageCache::class,
            'only' => ['index'],
            'duration' => 60,
            'dependency' => [
                'class' => DbDependency::class,
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
            'variations' => [
                \Yii::$app->language,
            ]
        ],
    ];
}
```

Подробнее об использовании PageCache читайте в разделе [Кэширование страниц](caching-page.md).


### [[yii\filters\RateLimiter|RateLimiter]] <span id="rate-limiter"></span>

Ограничитель количества запросов в единицу времени *(RateLimiter)* реализует алгоритм ограничения запросов, основанный на
[алгоритме leaky bucket](https://en.wikipedia.org/wiki/Leaky_bucket). В основном, он используется при создании RESTful API.
Подробнее об использовании данного фильтра можно прочитать в разделе [Ограничение запросов](rest-rate-limiting.md).


### [[yii\filters\VerbFilter|VerbFilter]] <span id="verb-filter"></span>

Фильтр по типу запроса *(VerbFilter)* проверяет, разрешено ли запросам HTTP выполнять затребованные ими действия.
Если нет, то будет выброшено исключение HTTP с кодом 405. В следующем примере в фильтре по типу запроса указан обычный
набор разрешённых методов запроса при выполнении CRUD операций.

```php
use yii\filters\VerbFilter;

public function behaviors()
{
    return [
        'verbs' => [
            'class' => VerbFilter::class,
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

Совместное использование разными источниками [CORS](https://developer.mozilla.org/ru/docs/Web/HTTP/CORS)
- это механизм, который позволяет использовать различные ресурсы (шрифты, скрипты, и т.д.) с отличных от основного сайта
доменов. В частности, AJAX вызовы JavaScript могут использовать механизм XMLHttpRequest. В противном случае, такие
"междоменные" запросы были бы запрещены из-за политики безопасности same origin. CORS задаёт способ взаимодействия
сервера и браузера, определяющий возможность делать междоменные запросы.

Фильтр [[yii\filters\Cors|Cors filter]] следует определять перед фильтрами Аутентификации / Авторизации, для того чтобы
быть уверенными, что заголовки CORS будут всегда посланы.

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
        ],
    ], parent::behaviors());
}
```

Если вам необходимо добавить CORS-фильтрацию к [[yii\rest\ActiveController]] в вашем API, обратитесь к разделу
[Контроллеры](rest-controllers.md#cors).

Фильтрация Cors может быть настроена с помощью свойства [[yii\filters\Cors::$cors|$cors]].

* `cors['Origin']`: массив, используемый для определения источников. Может принимать значение `['*']` (все) или
  `['https://www.myserver.net', 'https://www.myotherserver.com']`. По умолчанию значение равно `['*']`.
* `cors['Access-Control-Request-Method']`: массив разрешенных типов запроса, таких как `['GET', 'OPTIONS', 'HEAD']`.
  Значение по умолчанию `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`.
* `cors['Access-Control-Request-Headers']`: массив разрешенных заголовков. Может быть `['*']` то есть все заголовки или
  один из указанных `['X-Request-With']`. Значение по умолчанию `['*']`.
* `cors['Access-Control-Allow-Credentials']`: определяет, может ли текущий запрос быть сделан с использованием авторизации.
  Может принимать значения `true`, `false` или `null` (не установлено). Значение по умолчанию `null`.
* `cors['Access-Control-Max-Age']`: определяет *срок жизни запроса, перед его началом*. По умолчанию `86400`.

Например, разрешим CORS для источника : `https://www.myserver.net` с методами `GET`, `HEAD` и `OPTIONS` :

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
        ],
    ], parent::behaviors());
}
```

Вы можете настроить заголовки CORS переопределения параметров по умолчанию *для каждого из действий.*

Например, добавление `Access-Control-Allow-Credentials` для действия  `login` может быть сделано так :

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://www.myserver.net'],
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
