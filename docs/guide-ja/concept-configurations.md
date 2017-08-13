構成情報
==============

新しいオブジェクトを作成したり、既存のオブジェクトを初期化するとき、Yiiでは構成情報が広く使用されています。
構成情報は通常、作成されるオブジェクトのクラス名、およびオブジェクトの [プロパティ](concept-properties.md)
に割り当てられる初期値のリストを含みます。構成情報は、オブジェクトの [イベント](concept-events.md) にアタッチされるハンドラのリストや、オブジェクトにアタッチされる
[ビヘイビア](concept-behaviors.md) のリストを含むこともできます。

以下では、データベース接続を作成して初期化するために、構成情報が使用されています:

```php
$config = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];

$db = Yii::createObject($config);
```
[[Yii::createObject()]] メソッドは引数に構成情報の配列を受け取り、構成情報で名前指定されたクラスをインスタンス化してオブジェクトを作成します。
オブジェクトがインスタンス化されるとき、その他の設定は、
オブジェクトのプロパティ、イベントハンドラ、およびビヘイビアを初期化するのに使われます。

すでにオブジェクトがある場合は、構成情報配列でオブジェクトのプロパティを初期化するのに [[Yii::configure()]] を使用することができます:

```php
Yii::configure($object, $config);
```

なお、この場合には、構成情報配列に `class` 要素を含んではいけません。


## 構成情報の形式 <span id="configuration-format"></span>

構成情報の形式は、フォーマルには次のように説明できます:

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    'on eventName' => $eventHandler,
    'as behaviorName' => $behaviorConfig,
]
```

ここで

* `class` 要素は、作成されるオブジェクトの完全修飾クラス名を指定します。
* `propertyName` 要素は、名前で指定されたプロパティの初期値を指定します。キーはプロパティ名で、値はそれに対応する初期値です。
  パブリックメンバ変数と getter/setter によって定義されている [プロパティ](concept-properties.md) のみを設定することができます。
* `on eventName` 要素は、どのようなハンドラがオブジェクトの [イベント](concept-events.md) にアタッチされるかを指定します。
  配列のキーが `on` に続けてイベント名という書式になることに注意してください。サポートされているイベントハンドラの形式については、
  [イベント](concept-events.md) のセクションを参照してください。
* `as behaviorName` 要素は、どのような [ビヘイビア](concept-behaviors.md) がオブジェクトにアタッチされるかを指定します。
  配列のキーが `as` に続けてビヘイビア名という書式になり、 `$behaviorConfig` で示される値が、ここで説明する一般的な構成情報のような、
  ビヘイビアを作成するための構成情報になることに注意してください。

下記は、初期プロパティ値、イベントハンドラ、およびビヘイビアでの構成を示した例です:

```php
[
    'class' => 'app\components\SearchEngine',
    'apiKey' => 'xxxxxxxx',
    'on search' => function ($event) {
        Yii::info("Keyword searched: " . $event->keyword);
    },
    'as indexer' => [
        'class' => 'app\components\IndexerBehavior',
        // ... プロパティ初期値 ...
    ],
]
```


## 構成情報の使用 <span id="using-configurations"></span>

構成情報は Yii の多くの場所で使用されています。このセクションの冒頭では、 [[Yii::createObject()]]
を使って、構成情報に応じてオブジェクトを作成する方法を示しました。このサブセクションでは、
アプリケーションの構成とウィジェットの構成という、2つの主要な構成情報の用途を説明します。


### アプリケーションの構成 <span id="application-configurations"></span>

[アプリケーション](structure-applications.md) の構成は、おそらく Yii の中で最も複雑な配列のひとつです。
それは [[yii\web\Application|アプリケーション]] クラスが、設定可能なプロパティとイベントを数多く持つためです。
さらに重要なことは、その [[yii\web\Application::components|components]] プロパティが、アプリケーションに登録されている
コンポーネント生成用の構成情報配列を受け取ることができることです。以下は、 [ベーシックプロジェクトテンプレート](start-basic.md)
のアプリケーション構成ファイルの概要です。

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
        'log' => [
            'class' => 'yii\log\Dispatcher',
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=stay2',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];
```

この構成情報には、 `class` キーがありません。それは、[エントリスクリプト](structure-entry-scripts.md) で以下のように、
クラス名が既に与えられて使用されているためです。

```php
(new yii\web\Application($config))->run();
```

アプリケーションの `components` プロパティ構成の詳細については、 [アプリケーション](structure-applications.md) セクションと [サービスロケータ](concept-service-locator.md) セクションにあります。

バージョン 2.0.11 以降では、アプリケーション構成で `container` プロパティを使って [依存注入コンテナ](concept-di-container.md) を構成することがサポートされています。

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'container' => [
        'definitions' => [
            'yii\widgets\LinkPager' => ['maxButtonCount' => 5]
        ],
        'singletons' => [
            // 依存注入コンテナのシングルトンの構成
        ]
    ]
];
```

`definitions` と `singletons` の構成情報配列に使用できる値とその実例についてさらに知るためには、
[依存注入コンテナ](concept-di-container.md)の記事の [高度な実際の使用方法](concept-di-container.md#advanced-practical-usage) の節を読んでください。

### ウィジェットの構成 <span id="widget-configurations"></span>

[ウィジェット](structure-widgets.md) を使用するときは、多くの場合、ウィジェットのプロパティをカスタマイズするために、構成情報を使用する必要があります。
[[yii\base\Widget::widget()]] と [[yii\base\Widget::begin()]] の両メソッドを使って、ウィジェットを作成できます。それらは、以下のような構成情報配列を取ります。

```php
use yii\widgets\Menu;

echo Menu::widget([
    'activateItems' => false,
    'items' => [
        ['label' => 'ホーム', 'url' => ['site/index']],
        ['label' => '製品', 'url' => ['product/index']],
        ['label' => 'ログイン', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
    ],
]);
```

上記のコードは、 `Menu` ウィジェットを作成し、その `activateItems` プロパティが `false` になるよう初期化します。
`items` プロパティも、表示されるメニュー項目で構成されます。

クラス名がすでに与えられているので、構成情報配列が `class` キーを持つべきではないことに注意してください。


## 構成情報ファイル <span id="configuration-files"></span>

構成情報がとても複雑になる場合、一般的な方法は、 *構成情報ファイル* と呼ばれる、ひとつまたは複数の PHP ファイルにそれを格納することです。
構成情報ファイルは、構成情報を表す PHP 配列を return します。
たとえば、次のように、 `web.php` と名づけたファイルにアプリケーション構成を保持することができます。

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'components' => require __DIR__ . '/components.php',
];
```
`components` の構成もまた複雑になるため、上記のように、 `components.php` と呼ぶ別のファイルにそれを格納し `web.php` でそのファイルを "require" しています。
この `components.php` の内容は、次のようになっています。

```php
return [
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
    ],
    'log' => [
        'class' => 'yii\log\Dispatcher',
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
            ],
        ],
    ],
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=stay2',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ],
];
```

構成情報ファイルに格納されている構成情報を取得するには、以下のように、それを "require" するだけです:

```php
$config = require 'path/to/web.php';
(new yii\web\Application($config))->run();
```


## デフォルト設定 <span id="default-configurations"></span>

[[Yii::createObject()]] メソッドは、 [依存性注入コンテナ](concept-di-container.md) をベースに実装されています。
そのため、指定されたクラスが [[Yii::createObject()]] を使用して作成されるとき、そのすべてのインスタンスに適用される、
いわゆる *デフォルト設定* のセットを指定することができます。デフォルト設定は、
[ブートストラップ](runtime-bootstrapping.md) コード内の `Yii::$container->set()` を呼び出すことで指定することができます。

たとえばあなたが、すべてのリンクページャーが最大で5つのページボタン (デフォルト値は10) を伴って表示されるよう
[[yii\widgets\LinkPager]] をカスタマイズしたいとき、その目標を達成するには次のコードを使用することができます。

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```

デフォルト設定を使用しなければ、あなたは、リンクページャーを使うすべての箇所で `maxButtonCount` を設定しなければなりません。


## 環境定数 <span id="environment-constants"></span>

構成情報は、多くの場合、アプリケーションが実行される環境に応じて変化します。たとえば、
開発環境では `mydb_dev` という名前のデータベースを使用し、本番サーバー上では `mydb_prod` データベースを
使用したいかもしれません。環境の切り替えを容易にするために、Yii は、あなたのアプリケーションの
[エントリスクリプト](structure-entry-scripts.md) で定義可能な `YII_ENV` という名前の定数を提供します。
たとえば:

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

`YII_ENV` を次のいずれかの値と定義することができます:

- `prod`: 本番環境。定数 `YII_ENV_PROD` が `true` と評価されます。
  とくに定義しない場合、これが `YII_ENV` のデフォルト値です。
- `dev`: 開発環境。定数 `YII_ENV_DEV` が `true` と評価されます。
- `test`: テスト環境。定数 `YII_ENV_TEST` が `true` と評価されます。

これらの環境定数を使用すると、現在の環境に基づいて条件付きで構成情報を指定することもできます。
たとえば、アプリケーション構成情報には、開発環境での [デバッグツールバーとデバッガ](tool-debugger.md)
を有効にするために、次のコードを含むことができます。

```php
$config = [...];

if (YII_ENV_DEV) {
    // 'dev' 環境用に構成情報を調整
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
