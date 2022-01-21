过滤器
=======

过滤器是 [控制器动作](structure-controllers.md#actions) 执行之前或之后执行的对象。
例如访问控制过滤器可在动作执行之前来控制特殊终端用户是否有权限执行动作，
内容压缩过滤器可在动作执行之后发给终端用户之前压缩响应内容。

过滤器可包含预过滤（过滤逻辑在动作*之前*）或后过滤（过滤逻辑在动作*之后*），
也可同时包含两者。


## 使用过滤器 <span id="using-filters"></span>

过滤器本质上是一类特殊的 [行为](concept-behaviors.md)，
所以使用过滤器和 [使用行为](concept-behaviors.md#attaching-behaviors)一样。
可以在控制器类中覆盖它的 [[yii\base\Controller::behaviors()|behaviors()]] 方法来声明过滤器，如下所示：

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

控制器类的过滤器默认应用到该类的 *所有* 动作，
你可以配置 [[yii\base\ActionFilter::only|only]] 属性明确指定控制器应用到哪些动作。
在上述例子中，`HttpCache` 过滤器只应用到 `index` 和 `view` 动作。
也可以配置 [[yii\base\ActionFilter::except|except]] 属性
使一些动作不执行过滤器。

除了控制器外，可在 [模块](structure-modules.md)或[应用主体](structure-applications.md) 中申明过滤器。
申明之后，过滤器会应用到所属该模块或应用主体的 *所有* 控制器动作，
除非像上述一样配置过滤器的 [[yii\base\ActionFilter::only|only]] 
和 [[yii\base\ActionFilter::except|except]] 属性。

> Note: 在模块或应用主体中申明过滤器，在[[yii\base\ActionFilter::only|only]] 和 [[yii\base\ActionFilter::except|except]]
  属性中使用[路由](structure-controllers.md#routes) 代替动作 ID，
  因为在模块或应用主体中只用动作ID并不能唯一指定到具体动作。

当一个动作有多个过滤器时，根据以下规则先后执行：

* 预过滤
    - 按顺序执行应用主体中 `behaviors()` 列出的过滤器。
    - 按顺序执行模块中 `behaviors()` 列出的过滤器。
    - 按顺序执行控制器中 `behaviors()` 列出的过滤器。
    - 如果任意过滤器终止动作执行，
      后面的过滤器（包括预过滤和后过滤）不再执行。
* 成功通过预过滤后执行动作。
* 后过滤
    - 倒序执行控制器中 `behaviors()` 列出的过滤器。
    - 倒序执行模块中 `behaviors()` 列出的过滤器。
    - 倒序执行应用主体中 `behaviors()` 列出的过滤器。


## 创建过滤器 <span id="creating-filters"></span>

继承 [[yii\base\ActionFilter]] 类并覆盖
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] 或 [[yii\base\ActionFilter::afterAction()|afterAction()]]
方法来创建动作的过滤器，前者在动作执行之前执行，后者在动作执行之后执行。
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] 返回值决定动作是否应该执行，
如果为 false，之后的过滤器和动作不会继续执行。

下面的例子申明一个记录动作执行时间日志的过滤器。

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


## 核心过滤器 <span id="core-filters"></span>

Yii 提供了一组常用过滤器，在 `yii\filters` 命名空间下，
接下来我们简要介绍这些过滤器。


### [[yii\filters\AccessControl|AccessControl]] <span id="access-control"></span>

AccessControl 提供基于 [[yii\filters\AccessControl::rules|rules]] 规则的访问控制。
特别是在动作执行之前，访问控制会检测所有规则
并找到第一个符合上下文的变量（比如用户 IP 地址、登录状态等等）的规则，
来决定允许还是拒绝请求动作的执行，
如果没有规则符合，访问就会被拒绝。

如下示例表示表示允许已认证用户访问 `create` 和 `update` 动作，
拒绝其他用户访问这两个动作。

```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'only' => ['create', 'update'],
            'rules' => [
                // 允许认证用户
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // 默认禁止其他用户
            ],
        ],
    ];
}
```

更多关于访问控制的详情请参阅 [授权](security-authorization.md) 一节。


### 认证方法过滤器 <span id="auth-method-filters"></span>

认证方法过滤器通过 [HTTP Basic Auth](http://en.wikipedia.org/wiki/Basic_access_authentication)
或 [OAuth 2](http://oauth.net/2/)
来认证一个用户，认证方法过滤器类在 `yii\filters\auth` 命名空间下。

如下示例表示可使用 [[yii\filters\auth\HttpBasicAuth]] 来认证一个用户，
它使用基于 HTTP 基础认证方法的令牌。
注意为了可运行，[[yii\web\User::identityClass|user identity class]] 类必须
实现 [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]] 方法。

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

认证方法过滤器通常在实现 RESTful API中使用，
更多关于访问控制的详情请参阅 RESTful [认证](rest-authentication.md) 一节。


### [[yii\filters\ContentNegotiator|ContentNegotiator]] <span id="content-negotiator"></span>

ContentNegotiator 支持响应内容格式处理和语言处理。
通过检查 `GET` 参数和 `Accept` HTTP 头部来决定响应内容格式和语言。

如下示例，配置 ContentNegotiator 支持 JSON 和 XML
响应格式和英语（美国）和德语。

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

在[应用主体生命周期](structure-applications.md#application-lifecycle)过程中检测响应格式和语言简单很多，
因此 ContentNegotiator 设计可被
[引导启动组件](structure-applications.md#bootstrap)调用的过滤器。
如下例所示可以将它配置在
[应用主体配置](structure-applications.md#application-configurations)。

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

> Info: 如果请求中没有检测到内容格式和语言，
  使用 [[formats]] 和 [[languages]] 第一个配置项。



### [[yii\filters\HttpCache|HttpCache]] <span id="http-cache"></span>

HttpCache 利用 `Last-Modified` 和 `Etag` HTTP 头实现客户端缓存。
例如：

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

更多关于使用 HttpCache 详情请参阅 [HTTP 缓存](caching-http.md) 一节。


### [[yii\filters\PageCache|PageCache]] <span id="page-cache"></span>

PageCache 实现服务器端整个页面的缓存。如下示例所示，PageCache 应用在 `index` 动作，
缓存整个页面 60 秒或 `post` 表的记录数发生变化。
它也会根据不同应用语言保存不同的页面版本。

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

更多关于使用 PageCache 详情请参阅 [页面缓存](caching-page.md) 一节。


### [[yii\filters\RateLimiter|RateLimiter]] <span id="rate-limiter"></span>

RateLimiter 根据 [漏桶算法](http://en.wikipedia.org/wiki/Leaky_bucket) 来实现速率限制。
主要用在实现 RESTful APIs，更多关于该过滤器详情请参阅
[Rate Limiting](rest-rate-limiting.md) 一节。


### [[yii\filters\VerbFilter|VerbFilter]] <span id="verb-filter"></span>

VerbFilter 检查请求动作的 HTTP 请求方式是否允许执行，
如果不允许，会抛出 HTTP 405异常。
如下示例，VerbFilter 指定 CRUD 动作所允许的请求方式。

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

跨域资源共享 [CORS](https://developer.mozilla.org/fr/docs/HTTP/Access_control_CORS) 
机制允许一个网页的许多资源（例如字体、JavaScript等）
这些资源可以通过其他域名访问获取。
特别是 JavaScript 的 AJAX 调用可使用 XMLHttpRequest 机制，
由于同源安全策略该跨域请求会被网页浏览器禁止。CORS 定义浏览器和服务器交互时哪些跨域请求允许和禁止。

[[yii\filters\Cors|Cors filter]] 应在授权/认证过滤器之前定义，
以保证 CORS 头部被发送。

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

如果要将CORS过滤器添加到你的 API 中的 [[yii\rest\ActiveController]] 类，
还要检查 [REST Controllers](rest-controllers.md#cors) 中的部分。

CROS过滤器可以通过 [[yii\filters\Cors::$cors|$cors]] 属性进行调整。

* `cors['Origin']`：定义允许来源的数组，可为 `['*']`（任何用户）或 `['http://www.myserver.net', 'http://www.myotherserver.com']`。 默认为 `['*']`。
* `cors['Access-Control-Request-Method']`：允许动作数组如 `['GET', 'OPTIONS', 'HEAD']`。默认为 `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`。
* `cors['Access-Control-Request-Headers']`：允许请求头部数组，可为 `['*']` 所有类型头部 或 `['X-Request-With']` 指定类型头部。默认为 `['*']`。
* `cors['Access-Control-Allow-Credentials']`：定义当前请求是否使用证书，可为 `true`，`false` 或 `null`（不设置）。默认为 `null`。
* `cors['Access-Control-Max-Age']`: 定义请求的有效时间，默认为 `86400`。

例如，允许来源为 `http://www.myserver.net` 和方式为 `GET`，`HEAD` 和 `OPTIONS` 的 CORS 如下：

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
        ],
    ], parent::behaviors());
}
```

可以覆盖默认参数为每个动作调整 CORS 头部。例如，为 `login` 动作
增加 `Access-Control-Allow-Credentials` 参数如下所示：

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
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
