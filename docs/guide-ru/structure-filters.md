Фильтры
=======

Фильтры это объекты, которые запускаются перед и/или после [действий контроллера](structure-controllers.md#actions). Например,
фильтр управления доступом может запускаться перед действиями, для того чтобы гарантировать, что запросившему их пользователю разрешен доступ к ним;
фильтр сжатия содержимого, может запускаться после действий, чтобы сжать содержимое ответа перед отправкой его конечному пользователю.

Фильтр может состоять из *пре-фильтра* (фильтрующая логика применяется *перед* действиями) и/или *пост-фильтра* (логика, применяемая *после* действий).

## Использование фильтров <a name="using-filters"></a>

Фильтры являются по существу особым видом [поведений](concept-behaviors.md). Поэтому, использование фильтров ничем не отличается от [использования поведений](concept-behaviors.md#attaching-behaviors). Вы можете объявлять фильтры в классе контроллера, путём перекрытия его [[yii\base\Controller::behaviors()|behaviors()]] метода, как на примере ниже:

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

По умолчанию, фильтры объявленные в классе контроллера, будут применяться ко *всем* действиям в этом контроллере.
Тем не менее, вы можете явно указать к каким действиям фильтр следует применять, настроив свойство [[yii\base\ActionFilter::only|only]]. В примере выше, фильтр `HttpCache` применяется только к действиям `index` и `view`. Вы можете также настроить свойство [[yii\base\ActionFilter::except|except]] чтобы указать действия, к которым фильтр не должен применяться.

Кроме контроллеров, можно также объявлять фильтры в [модуле](structure-modules.md) или в [приложении](structure-applications.md).
Когда вы объявляете их так, эти фильтры будут применяться ко *всем* действиям контроллеров, находящихся в этом модуле или приложении, пока вы не настроите свойства фильтров [[yii\base\ActionFilter::only|only]] и [[yii\base\ActionFilter::except|except]] как было описано выше.

> Примечание: Когда объявляете фильтры в модулях или приложениях, вам следует использовать [маршруты](structure-controllers.md#routes) вместо идентификаторов действий в свойствах [[yii\base\ActionFilter::only|only]] и [[yii\base\ActionFilter::except|except]]. Так как сами по себе, идентификаторы действий не могут полностью определять действия, в пределах области видимости модуля или приложения.

Когда несколько фильтров указываются для одного действия, они применяются согласно правил, описанных ниже:

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


## Создание фильтров <a name="creating-filters"></a>

При создании нового фильтра действия, нужно наследоваться от [[yii\base\ActionFilter]] и переопределить методы
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] и/или [[yii\base\ActionFilter::afterAction()|afterAction()]]. Предшествующий из них будет выполнен перед выполнением действия, а последующий после выполнения действия.
Возвращаемое значение [[yii\base\ActionFilter::beforeAction()|beforeAction()]] определяет, будет ли действие выполняться или нет. Если вернется *ложь*, то оставшиеся фильтры не будут применены и действие не будет выполнено.

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
        Yii::trace("Action '{$action->uniqueId}' spent $time second.");
        return parent::afterAction($action, $result);
    }
}
```


## Фильтры ядра <a name="core-filters"></a>

Yii предоставляет набор часто используемых фильтров, которые находятся в основном в пространстве имен `yii\filters`. Далее, вы будете кратко ознакомлены с ними.


### [[yii\filters\AccessControl|AccessControl]] <a name="access-control"></a>

Фильтр AccessControl обеспечивает простое управление доступом, основанное на на наборе правил [[yii\filters\AccessControl::rules|rules]].
В частности, перед тем как действие начнет выполнение, фильтр AccessControl будет проверять список указанных правил, пока не найдет первое из них, которое будет соответствовать текущему *контексту* переменных (таких как IP адрес пользователя, *статус пользователя (права)* и тд). Выбранное правило будет указывать разрешить или запретить выполнение запрошенного действия. Если ни одно из правил не подойдет, то доступ будет запрещен.

Следующий пример показывает, как авторизованным пользователям разрешен доступ к действиям  `create` и `update`, в то время как всем другим пользователям запрещен.

```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::className(),
            'only' => ['create', 'update'],
            'rules' => [
                // allow authenticated users
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // everything else is denied by default
            ],
        ],
    ];
}
```

Более подробно об управлении доступом, вы можете прочитать в разделе [Авторизация](security-authorization.md).


### Фильтр Методы Аутентификации<a name="auth-method-filters"></a>

Фильтр Методы Аутентификации используется для аутентификации пользователя различными методами, такими как
[HTTP Basic Auth](http://en.wikipedia.org/wiki/Basic_access_authentication), [OAuth 2](http://oauth.net/2/).
Классы данных фильтров находятся в пространстве имён `yii\filters\auth`.

Следующий пример показывает, как использовать [[yii\filters\auth\HttpBasicAuth]] для аутентификации пользователя с помощью токена доступа, основанного на методе Базовой HTTP аутентификации. Обратите внимание, для того чтобы это работало, ваш класс
 [[yii\web\User::identityClass|user identity class]] должен реализовывать метод [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]].

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

Фильтры методов Аутентификации, часто используются при реализации сервисов RESTful API. Более подробную информацию о технологии 
RESTful, смотрите в разделе [Authentication](rest-authentication.md).


### [[yii\filters\ContentNegotiator|ContentNegotiator]] <a name="content-negotiator"></a>

ContentNegotiator поддерживает согласование формата ответа и языка приложения. Он пытается определить формат ответа и/или язык, путем проверка параметров `GET` и `Accept` заголовка HTTP.

В примере ниже, ContentNegotiator сконфигурирован чтобы поддерживать форматы ответа JSON и XML, а также Английский (США) и Немецкий языки.

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

Часто требуется, чтобы форматы ответа и языки приложения, были определены как можно раньше в его [жизненном цикле](structure-applications.md#application-lifecycle). По этой причине, ContentNegotiator разработан так, что может использоваться как
[компонент предварительной загрузки](structure-applications.md#bootstrap), кроме того что он может использоваться как фильтр. Например, вы можете настроить его в [конфигурации приложения](structure-applications.md#application-configurations)
как показано ниже:

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

> Информация: В случае, если предпочтительный тип содержимого и язык, не могут быть определены из запроса, будут использованы первый формат и язык, описанные в [[formats]] и [[languages]].



### [[yii\filters\HttpCache|HttpCache]] <a name="http-cache"></a>

Фильтр HttpCache реализовывает кэширование на стороне клиента, используя HTTP заголовки `Last-Modified` и `Etag`.
Например,

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

Для более детальной информации об использовании HttpCache смотрите раздел [HTTP Кэширование](caching-http.md).


### [[yii\filters\PageCache|PageCache]] <a name="page-cache"></a>

Фильтр PageCache реализует кэширование целых страниц на стороне сервера. В следующем примере, PageCache применяется только в действии `index`, для кэширования всей страницы в течение не более чем 60 секунд или пока количество записей в таблице `post`
не изменится. Он также хранит различные версии страницы, в зависимости от выбранного языка приложения.

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

Для более деатльной информации об использовании PageCache, смотрите раздел [Кэширование страниц](caching-page.md).


### [[yii\filters\RateLimiter|RateLimiter]] <a name="rate-limiter"></a>

Ограничитель количества запросов в единицу времени *(RateLimiter)* реализует алгоритм ограничения запросов, основанный на [leaky bucket algorithm](http://en.wikipedia.org/wiki/Leaky_bucket).
В основном, он используется при создании RESTful API. Для более детальной информации об использовании данного фильтра, смотрите раздел [Ограничение запросов](rest-rate-limiting.md).


### [[yii\filters\VerbFilter|VerbFilter]] <a name="verb-filter"></a>

Фильтр по типу запроса *(VerbFilter)* проверяет разрешено ли запросам HTTP, выполнять затребованные ими действия. Если такого разрешения нет, то будет выброшено исключение HTTP 405. В следующем примере, в фильтре по типу запроса, указан обычный набор разрешенных методов запроса, при выполнения CRUD операций.

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

### [[yii\filters\Cors|Cors]] <a name="cors"></a>

Совместное использование разными источниками [CORS](https://developer.mozilla.org/fr/docs/HTTP/Access_control_CORS) - это механизм, который позволяет использовать различные ресурсы с Веб страницы (шрифты, скрипты, и т.д.) с различных доменов, а не только с тех, где эти ресурсы изначально расположены.
В частности, AJAX вызовы JavaScript могут использовать механизм XMLHttpRequest, хотя в противном случае, такие "междоменные" запросы  запрещены, из-за политики безопасности.
CORS определяет путь взаимодействия сервера и браузера, по которому определяется возможность делать междоменные запросы.

Фильтр [[yii\filters\Cors|Cors filter]] следует определять перед фильтрами Аутентификации / Авторизации, для того чтобы быть уверенными, что заголовки CORS будут всегда посланы.

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

Фильтрация Cors может быть настроена с помощью свойства `cors`.

* `cors['Origin']`: массив, используемый для определения источников. Может принимать значение `['*']` (все) или `['http://www.myserver.net', 'http://www.myotherserver.com']`. По умолчанию значение равно `['*']`.
* `cors['Access-Control-Request-Method']`: массив разрешенных типов запроса, таких как `['GET', 'OPTIONS', 'HEAD']`.  Значение по умолчанию `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`.
* `cors['Access-Control-Request-Headers']`: массив разрешенных заголовков. Может быть `['*']` то есть все заголовки, или один из указанных `['X-Request-With']`. Значение по умолчанию `['*']`.
* `cors['Access-Control-Allow-Credentials']`: определяет, может ли текущий запрос сделан с использованием авторизации. Может принимать значения `true`, `false` или `null` (не установлено). Значение по умолчанию `null`.
* `cors['Access-Control-Max-Age']`: определяет *срок жизни запроса, перед его началом*. По умолчанию `86400`.

Например, разрешим CORS для источника : `http://www.myserver.net` с методами `GET`, `HEAD` и `OPTIONS` :

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

Вы можете настроить заголовки CORS переопределения параметров по умолчанию *для каждого из действий.*

Например, добавление `Access-Control-Allow-Credentials` для действия  `login` может быть сделано так :

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
