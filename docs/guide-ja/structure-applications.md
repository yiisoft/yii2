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
既定では、Yii は [規約](#controllerNamespace) に基づいてコントローラ ID をコントローラクラスに割り付けます
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

このプロパティは、リクエストがルートを指定していないときにアプリケーションが使用すべき [route](runtime-routing.md) を規定します。
ルートは、チャイルドモジュール ID、コントローラ ID、および/または アクション ID を構成要素とすることが出来ます。
例えば、`help`、`post/create`、`admin/post/create` などです。
アクション ID が与えられていない場合は、[[yii\base\Controller::defaultAction]] で規定されるデフォルト値を取ります。

[[yii\web\Application|ウェブアプリケーション]] では、このプロパティのデフォルト値は `'site'` であり、
その意味するところは、`SiteController` コントローラとそのデフォルトアクションが使用されるべきである、ということです。
結果として、ルートを指定せずにアプリケーションにアクセスすると、`app\controllers\SiteController::actionIndex()` の結果が表示されます。

[[yii\console\Application|コンソールアプリケーション]] では、デフォルト値は `'help'` であり、コアコマンドの [[yii\console\controllers\HelpController::actionIndex()]] が使用されるべきであるという意味です。
結果として、引数を与えずに `yii` というコマンドを走らせると、ヘルプ情報が表示されることになります。


#### [[yii\base\Application::extensions|extensions]] <a name="extensions"></a>

このプロパティは、アプリケーションにインストールされて使われる [エクステンション](structure-extensions.md) を規定するリストです。
デフォルトでは、`@vendor/yiisoft/extensions.php` というファイルによって返される配列を取ります。
`extensions.php` は、[Composer](http://getcomposer.org) を使ってエクステンションをインストールすると、自動的に生成され保守されます。
ですから、たいていの場合、このプロパティをあなたが構成する必要はありません。

エクステンションを手作業で保守したいという特殊なケースにおいては、次のようにしてこのプロパティを構成することが出来ます:

```php
[
    'extensions' => [
        [
            'name' => 'extension name',
            'version' => 'version number',
            'bootstrap' => 'BootstrapClassName',  // オプション、コンフィギュレーション配列でもよい
            'alias' => [  // optional
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... 上記と同じように、更にエクステンションを構成 ...

    ],
]
```

見て分かるように、このプロパティはエクステンションの仕様を示す配列を取ります。
それぞれのエクステンションは、`name` と `version` の要素を含む配列によって規定されます。
エクステンションが [ブートストラップ](runtime-bootstrapping.md) の過程で走る必要がある場合には、
`bootstrap` 要素をブートストラップのクラス名または [コンフィギュレーション](concept-configurations.md) 配列によって規定することが出来ます。
また、エクステンションはいくつかの [エイリアス](concept-aliases.md) を定義することも出来ます。


#### [[yii\base\Application::layout|layout]] <a name="layout"></a>

このプロパティは、[ビュー](structure-views.md) をレンダリングするときに使われるべきデフォルトのレイアウトを規定します。
デフォルト値は `'main'` であり、[レイアウトパス](#layoutPath) の下にある `main.php` というファイルが使われるべき事を意味します。
[レイアウトパス](#layoutPath) と [ビューパス](#viewPath) の両方がデフォルト値を取る場合、
デフォルトのレイアウトファイルは `@app/views/layouts/main.php` というパスエイリアスとして表すことが出来ます。

滅多には無いことですが、レイアウトをデフォルトで無効にしたい場合は、このプロパティを `false` として構成することが出来ます。


#### [[yii\base\Application::layoutPath|layoutPath]] <a name="layoutPath"></a>

このプロパティは、レイアウトファイルが捜されるべきパスを規定します。
デフォルト値は、[ビューパス](#viewPath) の下の `layouts` サブディレクトリです。
[ビューパス](#viewPath) がデフォルト値を取る場合、デフォルトのレイアウトパスは `@app/views/layouts` というパスエイリアスとして表すことが出来ます。

このプロパティはディレクトリまたはパス [エイリアス](concept-aliases.md) として構成することが出来ます。


#### [[yii\base\Application::runtimePath|runtimePath]] <a name="runtimePath"></a>

このプロパティは、ログファイルやキャッシュファイルなどの一時的ファイルを生成することが出来るパスを規定します。
デフォルト値は、`@app/runtime` というエイリアスで表現されるディレクトリです。

このプロパティはディレクトリまたはパス [エイリアス](concept-aliases.md) として構成することが出来ます。
ランタイムパスは、アプリケーションを走らせているプロセスによって書き込みが可能なものでなければならないことに注意してください。
そして、この下にある一時的ファイルは秘匿を要する情報を含みうるものですので、ランタイムパスはエンドユーザによるアクセスから保護されるべきです。

このパスに簡単にアクセスできるように、Yii は `@runtime` というパスエイリアスを事前に定義しています。


#### [[yii\base\Application::viewPath|viewPath]] <a name="viewPath"></a>

このプロパティはビューファイルが配置されるルートディレクトリを規定します。
デフォルト値は、`@app/views` というエイリアスで表現されるディレクトリです。
このプロパティはディレクトリまたはパス [エイリアス](concept-aliases.md) として構成することが出来ます。


#### [[yii\base\Application::vendorPath|vendorPath]] <a name="vendorPath"></a>

このプロパティは、[Composer](http://getcomposer.org) によって管理される vendor ディレクトリを規定します。
Yii フレームワークを含めて、あなたのアプリケーションによって使われる全てのサードパーティライブラリを格納するディレクトリです。
デフォルト値は、`@app/vendor` というエイリアスで表現されるディレクトリです。

このプロパティはディレクトリまたはパス [エイリアス](concept-aliases.md) として構成することが出来ます。
このプロパティを修正するときは、必ず、Composer の構成もそれに合せて調整してください。

このパスに簡単にアクセスできるように、Yii は `@vendor` というパスエイリアスを事前に定義しています。


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <a name="enableCoreCommands"></a>

このプロパティは [[yii\console\Application|コンソールアプリケーション]] においてのみサポートされています。
Yii リリースに含まれているコアコマンドを有効にすべきか否かを規定します。デフォルト値は `true` です。


## アプリケーションのイベント<a name="application-events"></a>

アプリケーションはリクエストを処理するライフサイクルの中でいくつかのイベントをトリガします。
これらのイベントに対して、下記のようにして、アプリケーションのコンフィギュレーションの中でイベントハンドラを取り付けることが出来ます。

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

`on eventName` という構文の使い方については、[コンフィギュレーション](concept-configurations.md#configuration-format) の節で説明されています。

別の方法として、アプリケーションのインスタンスが生成された後、[ブートストラップの過程](runtime-bootstrapping.md) の中でイベントハンドラを取り付けることも出来ます。
例えば、

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <a name="beforeRequest"></a>

このイベントは、アプリケーションがリクエストを処理する *前* にトリガされます。
実際のイベント名は `beforeRequest` です。

このイベントがトリガされるときには、アプリケーションのインスタンスは既に構成されて初期化されています。
ですから、イベントメカニズムを使って、リクエスト処理のプロセスに干渉するカスタムコードを挿入するのには、ちょうど良い場所です。
例えば、このイベントハンドラの中で、何らかのパラメータに基づいて [[yii\base\Application::language]] プロパティを動的にセットすることが出来ます。


### [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] <a name="afterRequest"></a>

このイベントは、アプリケーションがリクエストの処理を完了した *後*、レスポンスを送信する *前* にトリガされます。
実際のイベント名は `afterRequest` です。

このイベントがトリガされるときにはリクエストの処理は完了していますので、この機をとらえて、リクエストに対する何らかの後処理をしたり、レスポンスをカスタマイズしたりすることが出来ます。

[[yii\web\Response|response]] コンポーネントも、エンドユーザにレスポンスのコンテンツを送出する間にいくつかのイベントをトリガすることに注意してください。
それらのイベントは、このイベントの *後* にトリガされます。


### [[yii\base\Application::EVENT_BEFORE_ACTION|EVENT_BEFORE_ACTION]] <a name="beforeAction"></a>

このイベントは、[コントローラアクション](structure-controllers.md) を走らせる *前* に毎回トリガされます。
実際のイベント名は `beforeAction` です。

イベントのパラメータは [[yii\base\ActionEvent]] のインスタンスです。
イベントハンドラは、[[yii\base\ActionEvent::isValid]] プロパティを `false` にセットして、アクションが走るのを止めることが出来ます。
例えば、

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

同じ `beforeAction` イベントが、[モジュール](structure-modules.md) と [コントローラ](structure-controllers.md)
からもトリガされることに注意してください。
アプリケーションオブジェクトが最初にこのイベントをトリガし、次に (もし有れば) モジュールが、そして最後にコントローラがこのイベントをトリガします。
イベントハンドラが [[yii\base\ActionEvent::isValid]] を `false` にセットすると、後続のイベントはトリガされません。


### [[yii\base\Application::EVENT_AFTER_ACTION|EVENT_AFTER_ACTION]] <a name="afterAction"></a>

このイベントは、[コントローラアクション](structure-controllers.md) が走った *後* に毎回トリガされます。
実際のイベント名は `afterAction` です。

イベントのパラメータは [[yii\base\ActionEvent]] のインスタンスです。
[[yii\base\ActionEvent::result]] プロパティを通じて、イベントハンドラはアクションの結果にアクセスしたり、またはアクションの結果を修正したり出来ます。
例えば、

```php
[
    'on afterAction' => function ($event) {
        if (some condition) {
            // $event->result を修正する
        } else {
        }
    },
]
```

同じ `afterAction` イベントが、[モジュール](structure-modules.md) と [コントローラ](structure-controllers.md)
からもトリガされることに注意してください。
これらのオブジェクトは、`beforeAction` の場合とは逆の順でイベントをトリガします。
すなわち、コントローラオブジェクトが最初にこのイベントをトリガし、次に (もし有れば) モジュールが、そして最後にアプリケーションがこのイベントをトリガします。


## アプリケーションのライフサイクル<a name="application-lifecycle"></a>

[エントリスクリプト](structure-entry-scripts.md) が実行されて、リクエストが処理されるとき、
アプリケーションは次のようなライフサイクルを経ます:

1. エントリスクリプトがアプリケーションのコンフィギュレーションを配列として読み出す。
2. エントリスクリプトがアプリケーションの新しいインスタンスを作成する:
  * [[yii\base\Application::preInit()|preInit()]] が呼び出されて、[[yii\base\Application::basePath|basePath]]
    のような、優先度の高いアプリケーションプロパティを構成する。
  * [[yii\base\Application::errorHandler|エラーハンドラ]] を登録する。
  * アプリケーションのプロパティを構成する。
  * [[yii\base\Application::init()|init()]] が呼ばれ、そこから更に、ブートストラップコンポーネントを
    走らせるために、[[yii\base\Application::bootstrap()|bootstrap()]] が呼ばれる。
3. エントリスクリプトが [[yii\base\Application::run()]] を呼んで、アプリケーションを走らせる:
  * [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] イベントをトリガする。
  * リクエストを処理する: リクエストを [ルート](runtime-routing.md) とそれに結び付くパラメータとして解決する;
    ルートによって指定されたモジュール、コントローラ、および、アクションを作成する; そしてアクションを走らせる。
  * [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] イベントをトリガする。
  * エンドユーザにレスポンスを送信する。
4. エントリスクリプトがアプリケーションから終了ステータスを受け取り、リクエストの処理を完了する。
