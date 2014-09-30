应用组件
======================

应用主体是 [服务定位器](concept-service-locator.md)，它部署一组提供各种不同功能的 *应用组件* 来处理请求。
例如，`urlManager`组件负责处理网页请求路由到对应的控制器。`db`组件提供数据库相关服务等等。

在同一个应用中，每个应用组件都有一个独一无二的 ID 用来区分其他应用组件，你可以通过如下表达式访问应用组件。
Each application component has an ID that uniquely identifies itself among other application components
in the same application. You can access an application component through the expression

```php
\Yii::$app->componentID
```

例如，可以使用 `\Yii::$app->db` 来获取到已注册到应用的 [[yii\db\Connection|DB connection]]，
使用 `\Yii::$app->cache` 来获取到已注册到应用的 [[yii\caching\Cache|primary cache]]。 
and `\Yii::$app->cache` to get the [[yii\caching\Cache|primary cache]] registered with the application.
For example, you can use `\Yii::$app->db` to get the [[yii\db\Connection|DB connection]],
and `\Yii::$app->cache` to get the [[yii\caching\Cache|primary cache]] registered with the application.

第一次使用以上表达式时候会创建应用组件实例，后续再访问会返回此实例，无需再次创建。
An application component is created the first time it is accessed through the above expression. Any
further accesses will return the same component instance.

应用组件可以是任意对象，可以在 [应用主体配置](structure-applications.md#application-configurations) 配置 [[yii\base\Application::components]] 属性 .
例如：
Application components can be any objects. You can register them by configuring
the [[yii\base\Application::components]] property in [application configurations](structure-applications.md#application-configurations).
For example,

```php
[
    'components' => [
        // 使用类名注册 "cache" 组件
        'cache' => 'yii\caching\ApcCache',

        // 使用配置数组注册 "db" 组件
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // 使用函数注册"search" 组件
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> 补充：请谨慎注册太多应用组件，应用组件就像全局变量，使用太多可能加大测试和维护的难度。
  一般情况下可以在需要时再创建本地组件。
> Info: While you can register as many application components as you want, you should do this judiciously.
  Application components are like global variables. Using too many application components can potentially
  make your code harder to test and maintain. In many cases, you can simply create a local component
  and use it when needed.


## 引导启动组件 <a name="bootstrapping-components"></a>
## Bootstrapping Components <a name="bootstrapping-components"></a>

上面提到一个应用组件只会在第一次访问时实例化，如果处理请求过程没有访问的话就不实例化。
有时你想在每个请求处理过程都实例化某个组件即便它不会被访问，
可以将该组件ID加入到应用主体的 [[yii\base\Application::bootstrap|bootstrap]] 属性中。
As mentioned above, an application component will only be instantiated when it is being accessed the first time.
If it is not accessed at all during a request, it will not be instantiated. Sometimes, however, you may want
to instantiate an application component for every request, even if it is not explicitly accessed.
To do so, you may list its ID in the [[yii\base\Application::bootstrap|bootstrap]] property of the application.

例如, 如下的应用主体配置保证了 `log` 组件一直被加载。
For example, the following application configuration makes sure the `log` component is always loaded:

```php
[
    'bootstrap' => [
        'log',
    ],
    'components' => [
        'log' => [
            // "log" 组件的配置
        ],
    ],
]
```


## 核心应用组件 <a name="core-application-components"></a>
## Core Application Components <a name="core-application-components"></a>

Yii 定义了一组固定ID和默认配置的 *核心* 组件，例如 [[yii\web\Application::request|request]] 组件用来收集用户请求并解析 [路由](runtime-routing.md)；
[[yii\base\Application::db|db]] 代表一个可以执行数据库操作的数据库连接。
通过这些组件，Yii应用主体能处理用户请求。
Yii defines a set of *core* application components with fixed IDs and default configurations. For example,
the [[yii\web\Application::request|request]] component is used to collect information about
a user request and resolve it into a [route](runtime-routing.md); the [[yii\base\Application::db|db]]
component represents a database connection through which you can perform database queries.
It is with help of these core application components that Yii applications are able to handle user requests.

下面是预定义的核心应用组件列表，可以和普通应用组件一样配置和自定义它们。
当你配置一个核心组件，不指定它的类名的话就会使用Yii默认指定的类。
Below is the list of the predefined core application components. You may configure and customize them
like you do with normal application components. When you are configuring a core application component,
if you do not specify its class, the default one will be used.

* [[yii\web\AssetManager|assetManager]]: 管理资源包和资源发布，详情请参考 [管理资源](output-assets.md) 一节。
* [[yii\db\Connection|db]]: 代表一个可以执行数据库操作的数据库连接，
  注意配置该组件时必须指定组件类名和其他相关组件属性，如[[yii\db\Connection::dsn]]。
  详情请参考 [数据访问对象](db-dao.md) 一节。
* [[yii\base\Application::errorHandler|errorHandler]]: 处理 PHP 错误和异常，
  详情请参考 [错误处理](tutorial-handling-errors.md) 一节。
* [[yii\i18n\Formatter|formatter]]: 格式化输出显示给终端用户的数据，例如数字可能要带分隔符，
  日期使用长格式。详情请参考 [格式化输出数据](output-formatting.md) 一节。
* [[yii\i18n\I18N|i18n]]: 支持信息翻译和格式化。详情请参考 [国际化](tutorial-i18n.md) 一节。
* [[yii\log\Dispatcher|log]]: 管理日志对象。详情请参考 [日志](tutorial-logging.md) 一节。
* [[yii\swiftmailer\Mailer|mail]]: 支持生成邮件结构并发送，详情请参考 [邮件](tutorial-mailing.md) 一节。
* [[yii\base\Application::response|response]]: 代表发送给用户的响应，
  详情请参考 [响应](runtime-responses.md) 一节。
* [[yii\base\Application::request|request]]: 代表从终端用户处接收到的请求，
  详情请参考 [请求](runtime-requests.md) 一节。
* [[yii\web\Session|session]]: 代表会话信息，仅在[[yii\web\Application|Web applications]] 网页应用中可用，
  详情请参考 [Sessions (会话) and Cookies](runtime-sessions-cookies.md) 一节。
* [[yii\web\UrlManager|urlManager]]: 支持URL地址解析和创建，
  详情请参考 [URL 解析和生成](runtime-url-handling.md) 一节。
* [[yii\web\User|user]]: 代表认证登录用户信息，仅在[[yii\web\Application|Web applications]] 网页应用中可用，
  详情请参考 [认证](security-authentication.md) 一节。
* [[yii\web\View|view]]: 支持渲染视图，详情请参考 [Views](structure-views.md) 一节。
