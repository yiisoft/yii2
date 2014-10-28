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

これが唯一最重要なプロパティです。これによって、[アプリケーションコンポーネント](structure-application-components.md) と呼ばれる一連の名前付きのコンポーネントを登録して、それらを他の場所で使うことが出来るようになります。
例えば、

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

全てのアプリケーションコンポーネントは、それぞれ、配列の中で「キー・値」のペアとして規定されます。
キーはコンポーネントの ID を示し、値はコンポーネントのクラス名または [コンフィギュレーション](concept-configurations.md) を示します。

どのようなコンポーネントでもアプリケーションとともに登録することが出来ます。
そして登録されたコンポーネントは、後で、`\Yii::$app->ComponentID` という式を使ってグローバルにアクセスすることが出来ます。

詳細は [アプリケーションコンポーネント](structure-application-components.md) の節を呼んでください。


#### [[yii\base\Application::controllerMap|controllerMap]] <a name="controllerMap"></a>

このプロパティは、コントローラ ID を任意のコントローラクラスに割り付けることを可能にするものです。
既定では、Yii は [規約](#controllerNamespace) に基いてコントローラ ID をコントローラクラスに割り付けます
(例えば、`post` という ID は `app\controllers\PostController` に割り付けられます)。
このプロパティを構成することによって、特定のコントローラに対する規約を破ることが出来ます。
下記の例では、`account` は `app\controllers\UserController` に割り付けられ、
`article` は `app\controllers\PostController` に割り付けられることになります。

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

このプロパティの配列のキーはコントローラ ID を表し、配列の値は対応するコントローラクラスの名前または [コンフィギュレーション](concept-configurations.md) を表します。


#### [[yii\base\Application::controllerNamespace|controllerNamespace]] <a name="controllerNamespace"></a>

このプロパティは、コントローラクラスが配置されるべき既定の名前空間を指定するものです。
デフォルト値は `app\controllers` です。
コントローラ ID が `post` である場合、規約によって対応するコントローラの (名前空間を略した) クラス名は `PostController` となり、
完全修飾クラス名は `app\controllers\PostController` となります。

コントローラクラスは、この名前空間に対応するディレクトリのサブディレクトリに配置されても構いません。
例えば、コントローラ ID として `admin/post` を仮定すると、対応するコントローラの完全修飾クラス名は `app\controllers\admin\PostController` となります。

完全修飾のコントローラクラスが [オートロード可能](concept-autoloading.md) でなければならず、
コントローラクラスの実際の名前空間がこのプロパティと合致していなければならない、
ということは非常に重要なことです。
そうでないと、アプリケーションにアクセスしたときに "ページがみつかりません" というエラーを受け取ることになります。

上述の規約を破りたい場合は、[controllerMap](#controllerMap) プロパティを構成することが出来ます。


#### [[yii\base\Application::language|language]] <a name="language"></a>

このプロパティは、アプリケーションがコンテンツをエンドユーザに表示するときに使うべき言語を規定します。
このプロパティのデフォルト値は `en` であり、英語を意味します。
アプリケーションが多言語をサポートする必要があるときは、このプロパティを構成すべきです。

このプロパティの値が、メッセージの翻訳、日付の書式、数字の書式などを含めて、[国際化](tutorial-i18n.md) のさまざまな側面を決定します。
例えば、[[yii\jui\DatePicker]] ウィジェットは、どの言語でカレンダーを表示すべきか、そして日付をどのように書式設定すべきかを、既定では、このプロパティを使用して決定します。

言語を指定するのには、[IETF 言語タグ](http://ja.wikipedia.org/wiki/IETF%E8%A8%80%E8%AA%9E%E3%82%BF%E3%82%B0) に従うことが推奨されます。
例えば、`en` は英語を意味し、`en-US` はアメリカ合衆国の英語を意味します。

このプロパティに関する更なる詳細は [国際化](tutorial-i18n.md) の節で読むことが出来ます。


#### [[yii\base\Application::modules|modules]] <a name="modules"></a>

このプロパティはアプリケーションが含む [モジュール](structure-modules.md) を規定します。

このプロパティは、モジュールのクラスまたは [コンフィギュレーション](concept-configurations.md) の配列であり、
その配列のキーはモジュールの ID です。例えば、

```php
[
    'modules' => [
        // モジュールクラスで規定された "booking" モジュール
        'booking' => 'app\modules\booking\BookingModule',

        // コンフィギュレーション配列で規定された "comment" モジュール
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

詳細は [モジュール](structure-modules.md) の節を参照してください。


#### [[yii\base\Application::name|name]] <a name="name"></a>

このプロパティはアプリケーション名を規定します。これは、エンドユーザに対して表示されるかも知れません。
[[yii\base\Application::id|id]] プロパティがユニークな値でなければならないのとは違って、このプロパティの値は
主として表示目的であり、ユニークである必要はありません。

コードで使わないのであれば、このプロパティを構成する必要はありません。


#### [[yii\base\Application::params|params]] <a name="params"></a>

このプロパティは、グローバルにアクセス可能なアプリケーションパラメータの配列を規定します。
コードの中のいたる処でハードコードされた数値や文字列を使う代りに、それらをアプリケーションパラメータとして
一ヶ所で定義し、必要な場所ではそのパラメータを使うというのが良い慣行です。
例えば、次のように、サムネール画像のサイズをパラメータとして定義することが出来ます:

```php
[
    'params' => [
        'thumbnail.size' => [128, 128],
    ],
]
```

そして、このサイズの値を使う必要があるコードにおいては、ただ単に下記のようなコードを使うことが出来ます:

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

後でサムネールのサイズを変更すると決めたときは、アプリケーションのコンフィギュレーションにおいてのみサイズを修正すればよく、
これに依存するコードには少しも触れる必要がありません。


#### [[yii\base\Application::sourceLanguage|sourceLanguage]] <a name="sourceLanguage"></a>

このプロパティはアプリケーションコードが書かれている言語を規定します。デフォルト値は`'en-US'`、アメリカ合衆国の英語です。
あなたのコードのテキスト内容が英語以外で書かれているときは、このプロパティを構成すべきです。

[language](#language) プロパティと同様に、このプロパティは [IETF 言語タグ](http://ja.wikipedia.org/wiki/IETF%E8%A8%80%E8%AA%9E%E3%82%BF%E3%82%B0) に従って構成すべきです。
例えば、`en` は英語を意味し、`en-US` はアメリカ合衆国の英語を意味します。

このプロパティに関する更なる詳細は [国際化](tutorial-i18n.md) の節で読むことが出来ます。


#### [[yii\base\Application::timeZone|timeZone]] <a name="timeZone"></a>

このプロパティは、PHP ランタイムのデフォルトタイムゾーンを設定する代替手段として提供されています。
このプロパティを構成すると、本質的には PHP 関数 [date_default_timezone_set()](http://php.net/manual/ja/function.date-default-timezone-set.php) を呼ぶことになります。
例えば、

```php
[
    'timeZone' => 'Asia/Tokyo',
]
```


#### [[yii\base\Application::version|version]] <a name="version"></a>

このプロパティはアプリケーションのバージョンを規定します。デフォルト値は `'1.0'` です。
コードの中で使わないのであれば、必ずしも構成する必要はありません。


### 有用なプロパティ <a name="useful-properties"></a>

この項で説明されるプロパティは通常は構成されません。というのは、そのデフォルト値が通常の規約を規定するものだからです。
しかしながら、規約を破る必要がある場合には、これらのプロパティを構成することが出来ます。


#### [[yii\base\Application::charset|charset]] <a name="charset"></a>

このプロパティはアプリケーションが使う文字セットを規定します。デフォルト値は `'UTF-8'` であり、
あなたのアプリケーションが多数の非ユニコードデータを使うレガシーシステムと連携するのでなければ、
そのままにしておくべきです。


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
