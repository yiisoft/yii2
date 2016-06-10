サービスロケータ
================

サービスロケータは、アプリケーションが必要とする可能性のある各種のサービス (またはコンポーネント) を提供する方法を知っているオブジェクトです。
サービスロケータ内では、各コンポーネントは単一のインスタンスとして存在し、ID によって一意に識別されます。
あなたは、この ID を使用してサービスロケータからコンポーネントを取得できます。

Yii では、サービスロケータは単純に [[yii\di\ServiceLocator]] のインスタンス、または子クラスのインスタンスです。

Yii の中で最も一般的に使用されるサービスロケータは、`\Yii::$app` を通じてアクセスできる *アプリケーション* オブジェクトです。これが提供するサービスは、 *アプリケーションコンポーネント* と呼ばれる `request` 、
`response`、 `urlManager` などのコンポーネントです。あなたはサービスロケータによって提供される機能を通じて、
簡単に、これらのコンポーネントを構成、あるいは独自の実装に置き換え、といったことができます。

アプリケーションオブジェクトの他に、各モジュールオブジェクトもまたサービスロケータです。

サービスロケータを使用する最初のステップは、コンポーネントを登録することです。コンポーネントは、 [[yii\di\ServiceLocator::set()]]
を通じて登録することができます。次のコードは、コンポーネントを登録するさまざまな方法を示しています。

```php
use yii\di\ServiceLocator;
use yii\caching\FileCache;

$locator = new ServiceLocator;

// コンポーネントの作成に使われるクラス名を使用して "cache" を登録
$locator->set('cache', 'yii\caching\ApcCache');

// コンポーネントの作成に使われる構成情報配列を使用して "db" を登録
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// コンポーネントを構築する匿名関数を使って "search" を登録
$locator->set('search', function () {
    return new app\components\SolrService;
});

// コンポーネントを使って "pageCache" を登録
$locator->set('pageCache', new FileCache);
```

いったんコンポーネントが登録されたら、次の 2 つの方法のいずれかで、その ID を使ってそれにアクセスすることができます:

```php
$cache = $locator->get('cache');
// または代りに
$cache = $locator->cache;
```

上記のように、 [[yii\di\ServiceLocator]] を使うと、コンポーネント ID を使用して、プロパティのようにコンポーネントにアクセスすることができます。
あなたが最初にコンポーネントにアクセスしたとき、 [[yii\di\ServiceLocator]] はコンポーネントの登録情報を使用してコンポーネントの新しいインスタンスを作成し、
それを返します。後でそのコンポーネントが再度アクセスされた場合、サービスロケータは同じインスタンスを返します。

[[yii\di\ServiceLocator::has()]] を使って、コンポーネント ID がすでに登録されているかをチェックできます。
無効な ID で [[yii\di\ServiceLocator::get()]] を呼び出した場合、例外がスローされます。

サービスロケータは多くの場合、 [構成情報](concept-configurations.md) で作成されるため、
[[yii\di\ServiceLocator::setComponents()|components]] という名前の書き込み可能プロパティが提供されています。
これで一度に複数のコンポーネントを設定して登録することができます。
次のコードは、サービスロケータ (例えば [アプリケーション](structure-applications.md)) を "db"、"cache"、"search" コンポーネントとともに構成するための構成情報配列を示しています。

```php
return [
    // ...
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],
        'cache' => 'yii\caching\ApcCache',
        'search' => function () {
            $solr = new app\components\SolrService('127.0.0.1');
            // ... その他の初期化 ...
            return $solr;
        },
    ],
];
```

上記において、"search" コンポーネントを構成する別の方法があります。
`SolrService` のインスタンスを構築する PHP コールバックを直接に書く代りに、下記のように、そういうコールバックを返すスタティックなクラスメソッドを使うことが出来ます。

```php
class SolrServiceBuilder
{
    public static function build($ip)
    {
        return function () use ($ip) {
            $solr = new app\components\SolrService($ip);
            // ... その他の初期化 ...
            return $solr;
        };
    }
}

return [
    // ...
    'components' => [
        // ...
        'search' => SolrServiceBuilder::build('127.0.0.1'),
    ],
];
```

この方法は、Yii に属さないサードパーティのライブラリをカプセル化する Yii コンポーネントをリリースしようとする場合に、特に推奨される代替手法です。
上で示されているようなスタティックなメソッドを使ってサードパーティのオブジェクトを構築する複雑なロジックを表現します。
そうすれば、あなたのコンポーネントのユーザは、コンポーネントを構成するスタティックなメソッドを呼ぶ必要があるだけになります。
