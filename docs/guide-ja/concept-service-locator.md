サービス・ロケータ
==================

サービス・ロケータは、アプリケーションが必要とする可能性のある各種のサービス (またはコンポーネント) を提供する方法を知っているオブジェクトです。
サービス・ロケータ内では、各コンポーネントは単一のインスタンスとして存在し、ID によって一意に識別されます。
あなたは、この ID を使用してサービス・ロケータからコンポーネントを取得できます。

Yii では、サービス・ロケータは単純に [[yii\di\ServiceLocator]] のインスタンス、またはその子クラスのインスタンスです。

Yii の中で最も一般的に使用されるサービス・ロケータは、`\Yii::$app` を通じてアクセスできる *アプリケーション*・オブジェクトです。
これが提供するサービスは、 *アプリケーション・コンポーネント* と呼ばれる `request` 、
`response`、 `urlManager` などのコンポーネントです。あなたはサービス・ロケータによって提供される機能を通じて、
簡単に、これらのコンポーネントを構成、あるいは独自の実装に置き換え、といったことができます。

アプリケーション・オブジェクトの他に、各モジュール・オブジェクトもまたサービス・ロケータです。モジュールは [ツリー走査](#tree-traversal) を実装しています。

サービス・ロケータを使用する最初のステップは、コンポーネントを登録することです。コンポーネントは、 [[yii\di\ServiceLocator::set()]]
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

いったんコンポーネントが登録されたら、次の二つの方法のいずれかで、その ID を使ってそれにアクセスすることができます:

```php
$cache = $locator->get('cache');
// または代りに
$cache = $locator->cache;
```

上記のように、[[yii\di\ServiceLocator]] を使うと、コンポーネント ID を使用して、プロパティのようにコンポーネントにアクセスすることができます。
あなたが最初にコンポーネントにアクセスしたとき、[[yii\di\ServiceLocator]] は
コンポーネントの登録情報を使用してコンポーネントの新しいインスタンスを作成し、
それを返します。後でそのコンポーネントが再度アクセスされた場合、サービス・ロケータは同じインスタンスを返します。

[[yii\di\ServiceLocator::has()]] を使って、コンポーネント ID がすでに登録されているかをチェックできます。
無効な ID で [[yii\di\ServiceLocator::get()]] を呼び出した場合、例外が投げられます。


サービス・ロケータは多くの場合、 [構成情報](concept-configurations.md) で作成されるため、
[[yii\di\ServiceLocator::setComponents()|components]] という名前の書き込み可能プロパティが提供されています。
これで一度に複数のコンポーネントを設定して登録することができます。
次のコードは、サービス・ロケータ (例えば [アプリケーション](structure-applications.md)) を
`db`、`cache`、`tz`、`search` コンポーネントとともに構成するための構成情報配列を示しています。

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
        'tz' => function() {
            return new \DateTimeZone(Yii::$app->formatter->defaultTimeZone);
        },
        'search' => function () {
            $solr = new app\components\SolrService('127.0.0.1');
            // ... その他の初期化 ...
            return $solr;
        },
    ],
];
```

上記において、`search` コンポーネントを構成する別の方法があります。
`SolrService` のインスタンスを構築する PHP コールバックを直接に書く代りに、
下記のように、そういうコールバックを返すスタティックなクラス・メソッドを使うことが出来ます。

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

## ツリー走査 <span id="tree-traversal"></span>

モジュールは任意にネストすることが出来ます。Yii アプリケーションは本質的にモジュールのツリーなのです。
これらのモジュールのそれぞれがサービス・ロケータである訳ですから、子がその親にアクセスできるようにするのは理にかなった事です。
これによって、モジュールは、ルートのサービス・ロケータを参照して `Yii::$app->get('db')` とする代りに、`$this->get('db')` とすることが出来ます。
また、開発者にモジュール内で構成をオーバーライドするオプションを提供できることも、この仕組の利点です。

モジュールからサービスを引き出そうとする全てのリクエストは、そのモジュールが要求に応じられない場合は、すべてその親に渡されます。

モジュール内のコンポーネントの構成情報は、親モジュール内のコンポーネントの構成情報とは決してマージされないことに注意して下さい。
サービス・ロケータ・パターンによって私たちは名前の付いたサービスを定義することが出来ますが、同じ名前のサービスが同じ構成パラメータを使用すると想定することは出来ません。
