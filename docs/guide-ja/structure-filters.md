フィルタ
========

フィルタは、[コントローラアクション](structure-controllers.md#actions) の前 および/または 後に走るオブジェクトです。
例えば、アクセスコントロールフィルタはアクションの前に走って、アクションが特定のエンドユーザだけにアクセスを許可するものであることを保証します。
また、コンテンツ圧縮フィルタはアクションの後に走って、レスポンスのコンテンツをエンドユーザに送出する前に圧縮します。

フィルタは、前フィルタ (アクションの *前* に適用されるフィルタのロジック) および/または 後フィルタ (アクションの *後*
に適用されるフィルタ) から構成されます。


## フィルタを使用する <a name="using-filters"></a>

フィルタは、本質的には特別な種類の [ビヘイビア](concept-behaviors.md) です。したがって、フィルタを使うことは
[ビヘイビアを使う](concept-behaviors.md#attaching-behaviors) ことと同じです。下記のように、
[[yii\base\Controller::behaviors()|behaviors()]] メソッドをオーバーライドすることによって、コントローラの中で
フィルタを宣言することが出来ます:

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

既定では、コントローラクラスの中で宣言されたフィルタは、そのコントローラの *全て* のアクションに適用されます。
しかし、[[yii\base\ActionFilter::only|only]] プロパティを構成することによって、フィルタがどのアクションに適用されるべきかを
明示的に指定することも出来ます。上記の例では、 `HttpCache` フィルタは、`index` と `view` のアクションに対してのみ適用されています。
また、[[yii\base\ActionFilter::except|except]] プロパティを構成して、いくつかのアクションをフィルタされないように除外することも可能です。

コントローラのほかに、[モジュール](structure-modules.md) または [アプリケーション](structure-applications.md)
でもフィルタを宣言することが出来ます。
そのようにした場合、[[yii\base\ActionFilter::only|only]] と [[yii\base\ActionFilter::except|except]] のプロパティを
上で説明したように構成しない限り、そのフィルタは、モジュールまたはアプリケーションに属する *全て* のコントローラアクションに適用されます。

> Note|注意: モジュールやアプリケーションでフィルタを宣言する場合、[[yii\base\ActionFilter::only|only]] と
  [[yii\base\ActionFilter::except|except]] のプロパティでは、アクション ID ではなく、[ルート](structure-controllers.md#routes)
  を使うべきです。なぜなら、モジュールやアプリケーションのスコープでは、アクション ID だけでは完全にアクションを指定することが
  出来ないからです。

一つのアクションに複数のフィルタが構成されている場合、フィルタは下記で説明されている規則に従って適用されます。

* 前フィルタ
    - アプリケーションで宣言されたフィルタを `behaviors()` にリストされた順に適用する。
    - モジュールで宣言されたフィルタを `behaviors()` にリストされた順に適用する。
    - コントローラで宣言されたフィルタを `behaviors()` にリストされた順に適用する。
    - フィルタのどれかがアクションをキャンセルすると、そのフィルタの後のフィルタ (前フィルタと後フィルタの両方) は適用されない。
* 前フィルタを通過したら、アクションを走らせる。
* 後フィルタ
    - コントローラで宣言されたフィルタを `behaviors()` にリストされた逆順で適用する。
    - モジュールで宣言されたフィルタを `behaviors()` にリストされた逆順で適用する。
    - アプリケーションで宣言されたフィルタを `behaviors()` にリストされた逆順で適用する。


## フィルタを作成する <a name="creating-filters"></a>

新しいアクションフィルタを作成するためには、[[yii\base\ActionFilter]] を拡張して、
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] および/または [[yii\base\ActionFilter::afterAction()|afterAction()]]
メソッドをオーバーライドします。前者はアクションが走る前に実行され、後者は走った後に実行されます。
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] の返り値が、アクションが実行されるべきか否かを決定します。
返り値が false である場合、このフィルタの後に続くフィルタはスキップされ、アクションは実行を中止されます。

次の例は、アクションの実行時間をログに記録するフィルタを示すものです:

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
        Yii::trace("アクション '{$action->uniqueId}' は $time 秒を消費。");
        return parent::afterAction($action, $result);
    }
}
```


## コアのフィルタ <a name="core-filters"></a>

Yii はよく使われる一連のフィルタを提供しており、それらは、主として `yii\filters` 名前空間の下にあります。以下では、
それらのフィルタを簡単に紹介します。


### [[yii\filters\AccessControl|AccessControl]] <a name="access-control"></a>

AccessControl provides simple access control based on a set of [[yii\filters\AccessControl::rules|rules]].
In particular, before an action is executed, AccessControl will examine the listed rules and find the first one
that matches the current context variables (such as user IP address, user login status, etc.) The matching
rule will dictate whether to allow or deny the execution of the requested action. If no rule matches, the access
will be denied.

The following example shows how to allow authenticated users to access the `create` and `update` actions
while denying all other users from accessing these two actions.

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

For more details about access control in general, please refer to the [Authorization](security-authorization.md) section.


### Authentication Method Filters <a name="auth-method-filters"></a>

Authentication method filters are used to authenticate a user based using various methods, such as
[HTTP Basic Auth](http://en.wikipedia.org/wiki/Basic_access_authentication), [OAuth 2](http://oauth.net/2/).
These filter classes are all under the `yii\filters\auth` namespace.

The following example shows how you can use [[yii\filters\auth\HttpBasicAuth]] to authenticate a user using
an access token based on HTTP Basic Auth method. Note that in order for this to work, your
[[yii\web\User::identityClass|user identity class]] must implement the [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]
method.

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

Authentication method filters are commonly used in implementing RESTful APIs. For more details, please refer to the
RESTful [Authentication](rest-authentication.md) section.


### [[yii\filters\ContentNegotiator|ContentNegotiator]] <a name="content-negotiator"></a>

ContentNegotiator supports response format negotiation and application language negotiation. It will try to
determine the response format and/or language by examining `GET` parameters and `Accept` HTTP header.

In the following example, ContentNegotiator is configured to support JSON and XML response formats, and
English (United States) and German languages.

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

Response formats and languages often need to be determined much earlier during
the [application lifecycle](structure-applications.md#application-lifecycle). For this reason, ContentNegotiator
is designed in a way such that it can also be used as a [bootstrapping component](structure-applications.md#bootstrap)
besides filter. For example, you may configure it in the [application configuration](structure-applications.md#application-configurations)
like the following:

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

> Info: In case the preferred content type and language cannot be determined from a request, the first format and
  language listed in [[formats]] and [[languages]] will be used.



### [[yii\filters\HttpCache|HttpCache]] <a name="http-cache"></a>

HttpCache implements client-side caching by utilizing the `Last-Modified` and `Etag` HTTP headers.
For example,

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

Please refer to the [HTTP Caching](caching-http.md) section for more details about using HttpCache.


### [[yii\filters\PageCache|PageCache]] <a name="page-cache"></a>

PageCache implements server-side caching of whole pages. In the following example, PageCache is applied
to the `index` action to cache the whole page for maximum 60 seconds or until the count of entries in the `post`
table changes. It also stores different versions of the page depending on the chosen application language.

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

Please refer to the [Page Caching](caching-page.md) section for more details about using PageCache.


### [[yii\filters\RateLimiter|RateLimiter]] <a name="rate-limiter"></a>

RateLimiter implements a rate limiting algorithm based on the [leaky bucket algorithm](http://en.wikipedia.org/wiki/Leaky_bucket).
It is primarily used in implementing RESTful APIs. Please refer to the [Rate Limiting](rest-rate-limiting.md) section
for details about using this filter.


### [[yii\filters\VerbFilter|VerbFilter]] <a name="verb-filter"></a>

VerbFilter checks if the HTTP request methods are allowed by the requested actions. If not allowed, it will
throw an HTTP 405 exception. In the following example, VerbFilter is declared to specify a typical set of allowed
request methods for CRUD actions.

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

Cross-origin resource sharing [CORS](https://developer.mozilla.org/fr/docs/HTTP/Access_control_CORS) is a mechanism that allows many resources (e.g. fonts, JavaScript, etc.)
on a Web page to be requested from another domain outside the domain the resource originated from.
In particular, JavaScript's AJAX calls can use the XMLHttpRequest mechanism. Such "cross-domain" requests would
otherwise be forbidden by Web browsers, per the same origin security policy.
CORS defines a way in which the browser and the server can interact to determine whether or not to allow the cross-origin request.

The [[yii\filters\Cors|Cors filter]] should be defined before Authentication / Authorization filters to make sure the CORS headers
will always be sent.

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

The Cors filtering could be tuned using the `cors` property.

* `cors['Origin']`: array used to define allowed origins. Can be `['*']` (everyone) or `['http://www.myserver.net', 'http://www.myotherserver.com']`. Default to `['*']`.
* `cors['Access-Control-Request-Method']`: array of allowed verbs like `['GET', 'OPTIONS', 'HEAD']`.  Default to `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`.
* `cors['Access-Control-Request-Headers']`: array of allowed headers. Can be `['*']` all headers or specific ones `['X-Request-With']`. Default to `['*']`.
* `cors['Access-Control-Allow-Credentials']`: define if current request can be made using credentials. Can be `true`, `false` or `null` (not set). Default to `null`.
* `cors['Access-Control-Max-Age']`: define lifetime of pre-flight request. Default to `86400`.

For example, allowing CORS for origin : `http://www.myserver.net` with method `GET`, `HEAD` and `OPTIONS` :

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

You may tune the CORS headers by overriding default parameters on a per action basis.
For example adding the `Access-Control-Allow-Credentials` for the `login` action could be done like this :

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
