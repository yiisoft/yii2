应用对象
======================

应用是[服务定位器](concept-service-locators.md)。它们维护一个集合被称为**应用组件**为处理请求提供不同的服务。例如，`urlManager` 组件负责转发 Web 请求至相应的控制器；`db` 组件提供数据库相关服务，等等。

同一个应用中的每个组件都有一个唯一 ID 用来与其它组件作区分。你可以通过表达式访问一个应用组件：

```php
\Yii::$app->componentID
```

例如，你可以使用 `\Yii::$app->db` 去获取 [[yii\db\Connection|数据库连接（DB connection），使用`\Yii::$app->cache` 去获取应用注册的[[yii\caching\Cache|主缓存]]。

一个应用组件在使用上述表达式第一次访问的时候创建。其后任何访问都将返回同样的组件实例。

应用组件可以是任何对象。你可以通过[应用配置](structure-applications.md#application-configurations)的 [yii\base\Application::components]] 属性注册它们。例如：

```php
[
    'components' => [
        // 使用一个类名去注册“缓存”组件
        'cache' => 'yii\caching\ApcCache',

        // 使用配置数组注册“db”组件
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // 使用匿名函数注册搜索组件
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> 补充：尽管你可以随心所欲注册任何数量的组件，但应该明智去做。应用组件就像全局变量。使用太多应用组件可能潜在的会让代码难以测试和维护。许多情况下，你可以简单创建一个本地组件并在需要时使用。


## 引导组件 <a name="bootstrapping-components"></a>

如上所述，应用组件只在第一次被访问时实例化。如果在请求期间没有访问组件，它将根本不会实例化。有时候，无论怎样你可能都想为每个请求实例化组件，即便它没有被访问。你可以在应用的 [[yii\base\Application::bootstrap|引导（bootstrap）]] 属性罗列组件 ID 来做到这点。

例如，下述应用配置代码确保了 `log` 组件在任何情况下都载入：

```php
[
    'bootstrap' => [
        // 将 log 组件 ID 加入引导让它始终载入
        'log',
    ],
    'components' => [
        'log' => [
            // 配置 log 组件
        ],
    ],
]
```


## 核心应用组件 <a name="core-application-components"></a>

Yii 定义了一系列拥有固定 ID 和默认配置的**核心**应用组件。例如：[[yii\web\Application::request|请求（request）]]组件被用来收集用户请求数据并解析请求至[路由](runtime-routing.md)；[[yii\base\Application::db|数据库（db）]]组件代表一个数据库连接，你可以通过它执行数据库查询。Yii 有赖于这些核心组件的支持去处理用户请求。

下面是预定义的核心组件列表。你可以像配置普通应用组件一样配置和自定义它们。当你配置一个核心组件时，如果没有指定类名，默认类名将被使用。

* [[yii\web\AssetManager|资源管理器（assetManager）]]：管理静态资源包和静态资源发布。请参考[资源管理](output-assets.md)章节了解详细内容。
* [[yii\db\Connection|数据库（db）]]：代表一个数据库连接，你可以通过它执行数据库查询。请注意，当你配置这个组件时，必须和本组件的其它属性一样去配置组建类，诸如 [[yii\db\Connection::dsn]]。请参考[数据访问对象](db-dao.md)章节了解详细内容。
* [[yii\base\Application::errorHandler|错误处理器（errorHandler）]]：处理 PHP 错误和异常。请参考[错误处理](tutorial-handling-errors.md)章节了解详细内容。
* [[yii\base\Formatter|格式化器（formatter）]]：格式化向最终用户显示的数据。例如，数字可能会想显示千位分隔符，日期可能会想要长格式。请参考[数据格式化](output-formatting.md)章节了解详细内容。
* [[yii\i18n\I18N|国际化（i18n）]]：支持信息翻译转换和格式化。请参考[国际化](tutorial-i18n.md)章节了解详细内容。
* [[yii\log\Dispatcher|日志（log）]]：管理日志目标。请参考[日志](tutorial-logging.md)章节了解详细内容。
* [[yii\swiftmailer\Mailer|邮件（mail）]]：支持邮件接收和发送。请参考[邮件](tutorial-mailing.md)章节了解详细内容。
* [[yii\base\Application::response|响应（response）]]：代表将要发送给最终用户的响应内容。请参考[响应](runtime-responses.md)章节了解详细内容。
* [[yii\base\Application::request|请求（request）]]：代表从最终用户那收到的请求。请参考[请求](runtime-requests.md)章节了解详细内容。
* [[yii\web\Session|会话（session）]]：代表会话信息。这个组件只在 [[yii\web\Application|Web 应用]]中可用。请参考[会话和 Cookies](runtime-sessions-and-cookies.md) 章节了解详细内容。
* [[yii\web\UrlManager|URL 管理（urlManager）]]：支持 URL 的解析和创建。请参考 [URL 解析和生成](runtime-url-handling.md)章节了解详细内容。
* [[yii\web\User|用户（user）]]：代表用户认证信息。这个组件只在 [[yii\web\Application|Web 应用]]中可用。请参考[认证](security-authentication.md)章节了解详细内容。
* [[yii\web\View|视图（view）]]：支持视图渲染。请参考[视图](structure-views.md)章节了解详细内容。
