アプリケーション
================

アプリケーションは Yii アプリケーションシステム全体の構造とライフサイクルを統制するオブジェクトです。
全ての Yii アプリケーションシステムは、それぞれ、[エントリスクリプト](structure-entry-scripts.md) において作成され、`\Yii::$app` という式でグローバルにアクセス可能な、単一のアプリケーションオブジェクトを持ちます。

> Info|情報: ガイドの中で「アプリケーション」という言葉は、文脈に応じて、
アプリケーションオブジェクトを意味したり、アプリケーションシステムを意味したりします。

二種類のアプリケーションがあります: すなわち、[[yii\web\Application|ウェブアプリケーション]] と [[yii\console\Application|コンソールアプリケーション]] です。
名前が示すように、前者は主にウェブのリクエストを処理し、後者はコンソールコマンドのリクエストを処理します。


## アプリケーションのコンフィギュレーション<a name="application-configurations"></a>

[エントリスクリプト](structure-entry-scripts.md) は、アプリケーションを作成するときに、
下記のように、[コンフィギュレーション](concept-configurations.md) を読み込んで、それをアプリケーションに適用します:

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// アプリケーションのコンフィギュレーションを読み込む
$config = require(__DIR__ . '/../config/web.php');

// アプリケーションのインスタンスを作成し、コンフィギュレーションを適用する
(new yii\web\Application($config))->run();
```

通常の [コンフィギュレーション](concept-configurations.md) と同じように、アプリケーションのコンフィギュレーションは、アプリケーションオブジェクトのプロパティをどのように初期化するかを規定するものです。
アプリケーションのコンフィギュレーションは、たいていは非常に複雑なものですから、通常は、上記の例の `web.php` ファイルのように、[コンフィギュレーションファイル](concept-configurations.md#configuration-files) に保管されます。


## アプリケーションのプロパティ<a name="application-properties"></a>

アプリケーションのコンフィギュレーションで構成すべき重要なアプリケーションのプロパティは数多くあります。
それらのプロパティの典型的なものは、アプリケーションが走る環境を記述するものです。
例えば、アプリケーションは、どのようにして [コントローラ](structure-controllers.md) をロードするか、また、どこにテンポラリファイルを保存するかなどを知らなければなりません。
以下において、それらのプロパティを要約します。


### 必須のプロパティ<a name="required-properties"></a>

どのアプリケーションでも、最低二つのプロパティは構成しなければなりません:
すなわち、[[yii\base\Application::id|id]] と [[yii\base\Application::basePath|basePath]] です。


#### [[yii\base\Application::id|id]] <a name="id"></a>

[[yii\base\Application::id|id]] プロパティは、アプリケーションを他のアプリケーションから区別するユニークな ID を規定します。
このプロパティは主としてプログラム的に使われます。
必須ではありませんが、最良の相互運用性を確保するために、アプリケーション ID を規定するときに英数字だけを使うことが推奨されます。


#### [[yii\base\Application::basePath|basePath]] <a name="basePath"></a>

[[yii\base\Application::basePath|basePath]] プロパティは、アプリケーションのルートディレクトリを規定します。
これは、アプリケーションシステムの全ての保護されたソースコードを収容するディレクトリです。
通常、このディレクトリの下に、MVC パターンに対応するソースコードを収容した `models`、`views`、`controllers` などのサブディレクトリがあります。

[[yii\base\Application::basePath|basePath]] プロパティの構成には、ディレクトリパスを使っても、[パスエイリアス](concept-aliases.md) を使っても構いません。
どちらの形式においても、対応するディレクトリが存在しなければなりません。
さもなくば、例外が投げられます。
パスは `realpath()` 関数を呼び出して正規化されます。

[[yii\base\Application::basePath|basePath]] プロパティは、しばしば、他の重要なパス (例えば、runtime のパス) を派生させるために使われます。
このため、`basePath` を示す `@app` というパスエイリアスが、あらかじめ定義されています。
その結果、派生的なパスはこのエイリアスを使って形成することが出来ます
(例えば、runtime ディレクトリを示す `@app/runtime` など)。


### 重要なプロパティ<a name="important-properties"></a>

この項で説明するプロパティは、アプリケーションが異なるごとに異なってくるものであるため、たいてい、構成する必要が生じます。


#### [[yii\base\Application::aliases|aliases]] <a name="aliases"></a>

このプロパティを使って、配列形式で一連の [エイリアス](concept-aliases.md) を定義することが出来ます。
配列のキーがエイリアスの名前であり、配列の値が対応するパスの定義です。
例えば、

```php
[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
```

このプロパティが提供されているのは、[[Yii::setAlias()]] メソッドを呼び出す代りに、アプリケーションのコンフィギュレーションを使ってエイリアスを定義することが出来るようにするためです。


#### [[yii\base\Application::bootstrap|bootstrap]] <a name="bootstrap"></a>

これは非常に有用なプロパティです。
これによって、アプリケーションの [[yii\base\Application::bootstrap()|ブートストラップの過程]] において走らせるべきコンポーネントを配列として規定することが出来ます。
例えば、ある [モジュール](structure-modules.md) に [URL 規則](runtime-url-handling.md) をカスタマイズさせたいときに、モジュールの ID をこのプロパティの要素として挙げることが出来ます。

このプロパティに挙げるコンポーネントは、それぞれ、以下の形式のいずれかによって規定することが出来ます:

- [components](#components) によって規定されるアプリケーションコンポーネントの ID。
- [modules](#modules) によって規定されるモジュールの ID。
- クラス名。
- コンフィギュレーション配列。
- コンポーネントを作成して返す無名関数。

例えば、

```php
[
    'bootstrap' => [
        // アプリケーションコンポーネント ID、または、モジュール ID
        'demo',

        // クラス名
        'app\components\Profiler',

        // コンフィギュレーション配列
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ],

        // 無名関数
        function () {
            return new app\components\Profiler();
        }
    ],
]
```

> Info|情報: モジュール ID と同じ ID のアプリケーションコンポーネントがある場合は、ブートストラップの過程ではアプリケーションコンポーネントが使われます。
  代りにモジュールを使いたいときは、次のように、無名関数を使って指定することが出来ます:
>```php
[
    function () {
        return Yii::$app->getModule('user');
    },
]
```

ブートストラップの過程で、各コンポーネントのインスタンスが作成されます。
そして、コンポーネントクラスが [[yii\base\BootstrapInterface]] を実装している場合は、その [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] メソッドも呼び出されます。

もう一つの実用的な例が [ベーシックアプリケーションテンプレート](start-installation.md) のアプリケーションのコンフィギュレーションの中にあります。
そこでは、アプリケーションが開発環境で走るときには `debug` モジュールと `gii` モジュールがブートストラップコンポーネントとして構成されています。

```php
if (YII_ENV_DEV) {
    // 'dev' 環境のためのコンフィギュレーションの調整
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

> Note|注意: あまり多くのコンポーネントを `bootstrap` に置くと、アプリケーションのパフォーマンスを劣化させます。
  なぜなら、リクエストごとに同じ一連のコンポーネントを走らせなければならないからです。
  ですから、ブートストラップコンポーネントは賢く使ってください。


#### [[yii\web\Application::catchAll|catchAll]] <a name="catchAll"></a>

このプロパティは [[yii\web\Application|ウェブアプリケーション]] においてのみサポートされます。
これは、全てのユーザリクエストを処理すべき [コントローラアクション](structure-controllers.md) を規定します。
これは主としてアプリケーションがメンテナンスモードにあって、入ってくる全てのリクエストを単一のアクションで処理する必要があるときに使われます。

コンフィギュレーションは配列の形を取り、最初の要素はアクションのルートを指定します。
そして、配列の残りの要素 (キー・値のペア) は、アクションに渡されるパラメータを指定します。
例えば、

```php
[
    'catchAll' => [
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```


#### [[yii\base\Application::components|components]] <a name="components"></a>

This is the single most important property. It allows you to register a list of named components
called [application components](structure-application-components.md) that you can use in other places. For example,

```php
[
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
    ],
]
```

Each application component is specified as a key-value pair in the array. The key represents the component ID,
while the value represents the component class name or [configuration](concept-configurations.md).

You can register any component with an application, and the component can later be accessed globally
using the expression `\Yii::$app->ComponentID`.

Please read the [Application Components](structure-application-components.md) section for details.


#### [[yii\base\Application::controllerMap|controllerMap]] <a name="controllerMap"></a>

This property allows you to map a controller ID to an arbitrary controller class. By default, Yii maps
controller IDs to controller classes based on a [convention](#controllerNamespace) (e.g. the ID `post` would be mapped
to `app\controllers\PostController`). By configuring this property, you can break the convention for
specific controllers. In the following example, `account` will be mapped to
`app\controllers\UserController`, while `article` will be mapped to `app\controllers\PostController`.

```php
[
    'controllerMap' => [
        [
            'account' => 'app\controllers\UserController',
            'article' => [
                'class' => 'app\controllers\PostController',
                'enableCsrfValidation' => false,
            ],
        ],
    ],
]
```

The array keys of this property represent the controller IDs, while the array values represent the corresponding
controller class names or [configurations](concept-configurations.md).


#### [[yii\base\Application::controllerNamespace|controllerNamespace]] <a name="controllerNamespace"></a>

This property specifies the default namespace under which controller classes should be located. It defaults to
`app\controllers`. If a controller ID is `post`, by convention the corresponding controller class name (without
namespace) would be `PostController`, and the fully qualified class name would be `app\controllers\PostController`.

Controller classes may also be located under sub-directories of the directory corresponding to this namespace.
For example, given a controller ID `admin/post`, the corresponding fully qualified controller class would
be `app\controllers\admin\PostController`.

It is important that the fully qualified controller classes should be [autoloadable](concept-autoloading.md)
and the actual namespace of your controller classes match the value of this property. Otherwise,
you will receive "Page Not Found" error when accessing the application.

In case you want to break the convention as described above, you may configure the [controllerMap](#controllerMap)
property.


#### [[yii\base\Application::language|language]] <a name="language"></a>

This property specifies the language in which the application should display content to end users.
The default value of this property is `en`, meaning English. You should configure this property
if your application needs to support multiple languages.

The value of this property determines various [internationalization](tutorial-i18n.md) aspects,
including message translation, date formatting, number formatting, etc. For example, the [[yii\jui\DatePicker]] widget
will use this property value by default to determine in which language the calendar should be displayed and how
should the date be formatted.

It is recommended that you specify a language in terms of an [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag).
For example, `en` stands for English, while `en-US` stands for English (United States).

More details about this property can be found in the [Internationalization](tutorial-i18n.md) section.


#### [[yii\base\Application::modules|modules]] <a name="modules"></a>

This property specifies the [modules](structure-modules.md) that the application contains.

The property takes an array of module classes or [configurations](concept-configurations.md) with the array keys
being the module IDs. For example,

```php
[
    'modules' => [
        // a "booking" module specified with the module class
        'booking' => 'app\modules\booking\BookingModule',

        // a "comment" module specified with a configuration array
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

Please refer to the [Modules](structure-modules.md) section for more details.


#### [[yii\base\Application::name|name]] <a name="name"></a>

This property specifies the application name that may be displayed to end users. Unlike the
[[yii\base\Application::id|id]] property which should take a unique value, the value of this property is mainly for
display purpose and does not need to be unique.

You do not always need to configure this property if none of your code is using it.


#### [[yii\base\Application::params|params]] <a name="params"></a>

This property specifies an array of globally accessible application parameters. Instead of using hardcoded
numbers and strings everywhere in your code, it is a good practice to define them as application parameters
in a single place and use the parameters in places where needed. For example, you may define the thumbnail
image size as a parameter like the following:

```php
[
    'params' => [
        'thumbnail.size' => [128, 128],
    ],
]
```

Then in your code where you need to use the size value, you can simply use the code like the following:

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

Later if you decide to change the thumbnail size, you only need to modify it in the application configuration
without touching any dependent code.


#### [[yii\base\Application::sourceLanguage|sourceLanguage]] <a name="sourceLanguage"></a>

This property specifies the language that the application code is written in. The default value is `'en-US'`,
meaning English (United States). You should configure this property if the text content in your code is not in English.

Like the [language](#language) property, you should configure this property in terms of
an [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands for English,
while `en-US` stands for English (United States).

More details about this property can be found in the [Internationalization](tutorial-i18n.md) section.


#### [[yii\base\Application::timeZone|timeZone]] <a name="timeZone"></a>

This property is provided as an alternative way of setting the default time zone of PHP runtime.
By configuring this property, you are essentially calling the PHP function
[date_default_timezone_set()](http://php.net/manual/en/function.date-default-timezone-set.php). For example,

```php
[
    'timeZone' => 'America/Los_Angeles',
]
```


#### [[yii\base\Application::version|version]] <a name="version"></a>

This property specifies the version of the application. It defaults to `'1.0'`. You do not always need to configure
this property if none of your code is using it.


### Useful Properties <a name="useful-properties"></a>

The properties described in this subsection are not commonly configured because their default values
stipulate common conventions. However, you may still configure them in case you want to break the conventions.


#### [[yii\base\Application::charset|charset]] <a name="charset"></a>

This property specifies the charset that the application uses. The default value is `'UTF-8'` which should
be kept as is for most applications unless you are working with some legacy systems that use a lot of non-unicode data.


#### [[yii\base\Application::defaultRoute|defaultRoute]] <a name="defaultRoute"></a>

This property specifies the [route](runtime-routing.md) that an application should use when a request
does not specify one. The route may consist of child module ID, controller ID, and/or action ID.
For example, `help`, `post/create`, `admin/post/create`. If action ID is not given, it will take the default
value as specified in [[yii\base\Controller::defaultAction]].

For [[yii\web\Application|Web applications]], the default value of this property is `'site'`, which means
the `SiteController` controller and its default action should be used. As a result, if you access
the application without specifying a route, it will show the result of `app\controllers\SiteController::actionIndex()`.

For [[yii\console\Application|console applications]], the default value is `'help'`, which means the core command
[[yii\console\controllers\HelpController::actionIndex()]] should be used. As a result, if you run the command `yii`
without providing any arguments, it will display the help information.


#### [[yii\base\Application::extensions|extensions]] <a name="extensions"></a>

This property specifies the list of [extensions](structure-extensions.md) that are installed and used by the application.
By default, it will take the array returned by the file `@vendor/yiisoft/extensions.php`. The `extensions.php` file
is generated and maintained automatically when you use [Composer](http://getcomposer.org) to install extensions.
So in most cases, you do not need to configure this property.

In the special case when you want to maintain extensions manually, you may configure this property like the following:

```php
[
    'extensions' => [
        [
            'name' => 'extension name',
            'version' => 'version number',
            'bootstrap' => 'BootstrapClassName',  // optional, may also be a configuration array
            'alias' => [  // optional
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... more extensions like the above ...

    ],
]
```

As you can see, the property takes an array of extension specifications. Each extension is specified with an array
consisting of `name` and `version` elements. If an extension needs to run during the [bootstrap](runtime-bootstrapping.md)
process, a `bootstrap` element may be specified with a bootstrapping class name or a [configuration](concept-configurations.md)
array. An extension may also define a few [aliases](concept-aliases.md).


#### [[yii\base\Application::layout|layout]] <a name="layout"></a>

This property specifies the name of the default layout that should be used when rendering a [view](structure-views.md).
The default value is `'main'`, meaning the layout file `main.php` under the [layout path](#layoutPath) should be used.
If both of the [layout path](#layoutPath) and the [view path](#viewPath) are taking the default values,
the default layout file can be represented as the path alias `@app/views/layouts/main.php`.

You may configure this property to be `false` if you want to disable layout by default, although this is very rare.


#### [[yii\base\Application::layoutPath|layoutPath]] <a name="layoutPath"></a>

This property specifies the path where layout files should be looked for. The default value is
the `layouts` sub-directory under the [view path](#viewPath). If the [view path](#viewPath) is taking
its default value, the default layout path can be represented as the path alias `@app/views/layouts`.

You may configure it as a directory or a path [alias](concept-aliases.md).


#### [[yii\base\Application::runtimePath|runtimePath]] <a name="runtimePath"></a>

This property specifies the path where temporary files, such as log files, cache files, can be generated.
The default value is the directory represented by the alias `@app/runtime`.

You may configure it as a directory or a path [alias](concept-aliases.md). Note that the runtime path must
be writable by the process running the application. And the path should be protected from being accessed
by end users because the temporary files under it may contain sensitive information.

To simplify accessing to this path, Yii has predefined a path alias named `@runtime` for it.


#### [[yii\base\Application::viewPath|viewPath]] <a name="viewPath"></a>

This property specifies the root directory where view files are located. The default value is the directory
represented by the alias `@app/views`. You may configure it as a directory or a path [alias](concept-aliases.md).


#### [[yii\base\Application::vendorPath|vendorPath]] <a name="vendorPath"></a>

This property specifies the vendor directory managed by [Composer](http://getcomposer.org). It contains
all third party libraries used by your application, including the Yii framework. The default value is
the directory represented by the alias `@app/vendor`.

You may configure this property as a directory or a path [alias](concept-aliases.md). When you modify
this property, make sure you also adjust the Composer configuration accordingly.

To simplify accessing to this path, Yii has predefined a path alias named `@vendor` for it.


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <a name="enableCoreCommands"></a>

This property is supported by [[yii\console\Application|console applications]] only. It specifies
whether the core commands included in the Yii release should be enabled. The default value is `true`.


## Application Events <a name="application-events"></a>

An application triggers several events during the lifecycle of handling an request. You may attach event
handlers to these events in application configurations like the following,

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

The use of the `on eventName` syntax is described in the [Configurations](concept-configurations.md#configuration-format)
section.

Alternatively, you may attach event handlers during the [bootstrapping process](runtime-bootstrapping.md) process
after the application instance is created. For example,

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <a name="beforeRequest"></a>

This event is triggered *before* an application handles a request. The actual event name is `beforeRequest`.

When this event is triggered, the application instance has been configured and initialized. So it is a good place
to insert your custom code via the event mechanism to intercept the request handling process. For example,
in the event handler, you may dynamically set the [[yii\base\Application::language]] property based on some parameters.


### [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] <a name="afterRequest"></a>

This event is triggered *after* an application finishes handling a request but *before* sending the response.
The actual event name is `afterRequest`.

When this event is triggered, the request handling is completed and you may take this chance to do some postprocessing
of the request or customize the response.

Note that the [[yii\web\Response|response]] component also triggers some events while it is sending out
response content to end users. Those events are triggered *after* this event.


### [[yii\base\Application::EVENT_BEFORE_ACTION|EVENT_BEFORE_ACTION]] <a name="beforeAction"></a>

This event is triggered *before* running every [controller action](structure-controllers.md).
The actual event name is `beforeAction`.

The event parameter is an instance of [[yii\base\ActionEvent]]. An event handler may set
the [[yii\base\ActionEvent::isValid]] property to be `false` to stop running the action.
For example,

```php
[
    'on beforeAction' => function ($event) {
        if (some condition) {
            $event->isValid = false;
        } else {
        }
    },
]
```

Note that the same `beforeAction` event is also triggered by [modules](structure-modules.md)
and [controllers](structure-controllers.md). Application objects are the first ones
triggering this event, followed by modules (if any), and finally controllers. If an event handler
sets [[yii\base\ActionEvent::isValid]] to be `false`, all the following events will NOT be triggered.


### [[yii\base\Application::EVENT_AFTER_ACTION|EVENT_AFTER_ACTION]] <a name="afterAction"></a>

This event is triggered *after* running every [controller action](structure-controllers.md).
The actual event name is `afterAction`.

The event parameter is an instance of [[yii\base\ActionEvent]]. Through
the [[yii\base\ActionEvent::result]] property, an event handler may access or modify the action result.
For example,

```php
[
    'on afterAction' => function ($event) {
        if (some condition) {
            // modify $event->result
        } else {
        }
    },
]
```

Note that the same `afterAction` event is also triggered by [modules](structure-modules.md)
and [controllers](structure-controllers.md). These objects trigger this event in the reverse order
as for that of `beforeAction`. That is, controllers are the first objects triggering this event,
followed by modules (if any), and finally applications.


## Application Lifecycle <a name="application-lifecycle"></a>

When an [entry script](structure-entry-scripts.md) is being executed to handle a request,
an application will undergo the following lifecycle:

1. The entry script loads the application configuration as an array.
2. The entry script creates a new instance of the application:
  * [[yii\base\Application::preInit()|preInit()]] is called, which configures some high priority
    application properties, such as [[yii\base\Application::basePath|basePath]].
  * Register the [[yii\base\Application::errorHandler|error handler]].
  * Configure application properties.
  * [[yii\base\Application::init()|init()]] is called which further calls
    [[yii\base\Application::bootstrap()|bootstrap()]] to run bootstrapping components.
3. The entry script calls [[yii\base\Application::run()]] to run the application:
  * Trigger the [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] event.
  * Handle the request: resolve the request into a [route](runtime-routing.md) and the associated parameters;
    create the module, controller and action objects as specified by the route; and run the action.
  * Trigger the [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] event.
  * Send response to the end user.
4. The entry script receives the exit status from the application and completes the request processing.
