依存性注入コンテナ
==============================

依存性注入 (DI) コンテナは、オブジェクトとそのすべての依存オブジェクトを、インスタンス化し、設定する方法を知っているオブジェクトです。
なぜ DI コンテナが便利なのかは、[Martin の記事](http://martinfowler.com/articles/injection.html) の説明がわかりやすいでしょう。
ここでは、主に Yii の提供する DI コンテナの使用方法を説明します。


依存性注入 <span id="dependency-injection"></span>
--------------------

Yii は [[yii\di\Container]] クラスを通して DI コンテナの機能を提供します。これは、次の種類の依存性注入をサポートしています:

* コンストラクタ·インジェクション
* セッター/プロパティ·インジェクション
* PHP コーラブル·インジェクション


### コンストラクタ·インジェクション <span id="constructor-injection"></span>

DI コンテナは、コンストラクタパラメータの型ヒントの助けを借りた、コンストラクタ·インジェクションをサポートしています。
型ヒントは、クラスやインタフェースが新しいオブジェクトの作成で使用されるさい、どれが依存であるのかということをコンテナに教えます。
コンテナは、依存クラスやインタフェースのインスタンスを取得し、コンストラクタを通して、新しいオブジェクトにそれらの注入を試みます。
たとえば

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// これは下記と等価:
$bar = new Bar;
$foo = new Foo($bar);
```


### セッター/プロパティ·インジェクション <span id="setter-and-property-injection"></span>

セッター/プロパティ·インジェクションは、[構成情報](concept-configurations.md) を通してサポートされます。
依存関係を登録するときや、新しいオブジェクトを作成するとき、コンテナが使用する構成情報を提供することができ、
それに対応するセッターまたはプロパティを通じて依存関係が注入されます。たとえば

```php
use yii\base\Object;

class Foo extends Object
{
    public $bar;

    private $_qux;

    public function getQux()
    {
        return $this->_qux;
    }

    public function setQux(Qux $qux)
    {
        $this->_qux = $qux;
    }
}

$container->get('Foo', [], [
    'bar' => $container->get('Bar'),
    'qux' => $container->get('Qux'),
]);
```


### PHP コーラブル・インジェクション <span id="php-callable-injection"></span>

この場合、コンテナは、登録された PHP のコーラブルオブジェクトを使用し、クラスの新しいインスタンスを構築します。
コーラブルは、依存関係を解決し、新しく作成されたオブジェクトに適切にそれらを注入する責任があります。たとえば

```php
$container->set('Foo', function () {
    return new Foo(new Bar);
});

$foo = $container->get('Foo');
```


依存関係の登録 <span id="registering-dependencies"></span>
------------------------

あなたは、[[yii\di\Container::set()]] 使って依存関係を登録することができます。登録には依存関係の名前だけでなく、
依存関係の定義が必要です。依存関係の名前は、クラス名、インタフェース名、エイリアス名を指定することができます。
依存関係の定義には、クラス名、構成情報配列、PHPのコーラブルを指定できます。

```php
$container = new \yii\di\Container;

// クラス名そのまま。これはなくてもかまいません。
$container->set('yii\db\Connection');

// インターフェースの登録
// クラスがインターフェースに依存する場合、対応するクラスが依存オブジェクトとしてインスタンス化されます
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// エイリアス名の登録。$container->get('foo') を使って Connection のインスタンスを作成できます
$container->set('foo', 'yii\db\Connection');

// 構成情報をともなうクラスの登録。クラスが get() でインスタンス化されるとき構成情報が適用されます
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// クラスの構成情報をともなうエイリアス名の登録
// この場合、クラスを指定する "class" 要素が必要です
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// PHP コーラブルの登録
// このコーラブルは $container->get('db') が呼ばれるたびに実行されます
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});

// コンポーネントインスタンスの登録
// $container->get('pageCache') は呼ばれるたびに毎回同じインスタンスを返します
$container->set('pageCache', new FileCache);
```

> 補足: 依存関係名が、対応する依存関係の定義と同じである場合は、それを DI コンテナに登録する必要はありません。

`set()` を介して登録された依存性は、依存性が必要とされるたびにインスタンスを生成します。
[[yii\di\Container::setSingleton()]] を使うと、単一のインスタンスをひとつだけ生成する依存関係を登録することができます:

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```


依存関係の解決 <span id="resolving-dependencies"></span>
----------------------

依存関係を登録すると、新しいオブジェクトを作成するのに DI コンテナを使用することができ、
コンテナが自動的に、依存性をインスタンス化して新しく作成されたオブジェクトに注入することで、
依存関係を解決します。依存関係の解決は再帰的、つまり、ある依存性が他の依存関係を持っている場合、
それらの依存関係も自動的に解決されます。

[[yii\di\Container::get()]] を使って、新しいオブジェクトを作成することができます。
このメソッドは、クラス名、インタフェース名、エイリアス名で指定できる依存関係の名前を受け取ります。
依存関係名は、 `set()` や `setSingleton()` を介して登録されていたりされていなかったりする
可能性があります。オプションで、クラスのコンストラクタのパラメータのリストや、新しく作成された
オブジェクトを設定するための [設定情報](concept-configurations.md) を渡すことができます。
たとえば

```php
// "db" は事前に登録されたエイリアス名
$db = $container->get('db');

// これと同じ意味: $engine = new \app\components\SearchEngine($apiKey, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey], ['type' => 1]);
```

見えないところで、DIコンテナは、単に新しいオブジェクトを作成するよりもはるかに多くの作業を行います。
コンテナは、最初の依存クラスまたはインタフェースの名前を見つけるために、クラスのコンストラクタを検査し、
自動的にそれらの依存関係を再帰で解決します。

次のコードでより洗練された例を示します。 `UserLister` クラスは `UserFinderInterface`
インタフェースを実装するオブジェクトに依存します。 `UserFinder` クラスはこのインターフェイスを実装していて、かつ、
`Connection` オブジェクトに依存します。これらのすべての依存関係は、クラスのコンストラクタのパラメータのタイプヒンティングで宣言されています。
プロパティ依存性の登録をすれば、DI コンテナは自動的にこれらの依存関係を解決し、単純に `get('userLister')`
を呼び出すだけで新しい `UserLister` インスタンスを作成できます。

```php
namespace app\models;

use yii\base\Object;
use yii\db\Connection;
use yii\di\Container;

interface UserFinderInterface
{
    function findUser();
}

class UserFinder extends Object implements UserFinderInterface
{
    public $db;

    public function __construct(Connection $db, $config = [])
    {
        $this->db = $db;
        parent::__construct($config);
    }

    public function findUser()
    {
    }
}

class UserLister extends Object
{
    public $finder;

    public function __construct(UserFinderInterface $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }
}

$container = new Container;
$container->set('yii\db\Connection', [
    'dsn' => '...',
]);
$container->set('app\models\UserFinderInterface', [
    'class' => 'app\models\UserFinder',
]);
$container->set('userLister', 'app\models\UserLister');

$lister = $container->get('userLister');

// と、いうのはこれと同じ:

$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```


実際の使いかた <span id="practical-usage"></span>
---------------

あなたのアプリケーションの [エントリスクリプト](structure-entry-scripts.md) で `Yii.php` ファイルをインクルードするとき、
Yii は DI コンテナを作成します。この DI コンテナは [[Yii::$container]] を介してアクセス可能です。 [[Yii::createObject()]] を呼び出したとき、
このメソッドは実際には、新しいオブジェクトを作成ために、コンテナの [[yii\di\Container::get()|get()]] メソッドを呼び出しています。
前述のとおり、DI コンテナは(もしあれば)自動的に依存関係を解決し、新しく作成されたオブジェクトにそれらを注入します。
Yii は、新しいオブジェクトを作成するさいそのコアコードのほとんどで [[Yii::createObject()]] を使用しているため、これは、
[[Yii::$container]] を扱えばグローバルにオブジェクトをカスタマイズすることができることを意味しています。

たとえば、 [[yii\widgets\LinkPager]] のページネーションボタンのデフォルト個数をグローバルにカスタマイズすることができます:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

次のコードでビューでウィジェットを使用すれば、 `maxButtonCount` プロパティは、
クラスで定義されているデフォルト値 10 の代わりに 5 で初期化されます。

```php
echo \yii\widgets\LinkPager::widget();
```

DIコンテナを経由して設定された値は、こうやって、まだまだ上書きすることができます:

```php
echo \yii\widgets\LinkPager::widget(['maxButtonCount' => 20]);
```

DI コンテナの自動コンストラクタ・インジェクションの利点を活かす別の例です。
あなたのコントローラクラスが、ホテル予約サービスのような、いくつかの他のオブジェクトに依存するとします。
あなたは、コンストラクタパラメータを通して依存関係を宣言して、DI コンテナにあなたの課題を解決させることができます。

```php
namespace app\controllers;

use yii\web\Controller;
use app\components\BookingInterface;

class HotelController extends Controller
{
    protected $bookingService;

    public function __construct($id, $module, BookingInterface $bookingService, $config = [])
    {
        $this->bookingService = $bookingService;
        parent::__construct($id, $module, $config);
    }
}
```

あなたがブラウザからこのコントローラにアクセスすると、 `BookingInterface` をインスタンス化できませんという
不具合報告エラーが表示されるでしょう。これは、この依存関係に対処する方法を DI コンテナに教える必要があるからです:

```php
\Yii::$container->set('app\components\BookingInterface', 'app\components\BookingService');
```

これで、あなたが再びコントローラにアクセスするときは、 `app\components\BookingService`
のインスタンスが作成され、コントローラのコンストラクタに3番目のパラメータとして注入されるようになります。


依存関係を登録するときに <span id="when-to-register-dependencies"></span>
-----------------------------

依存関係は、新しいオブジェクトが作成されるとき必要とされるので、それらの登録は可能な限り早期に行われるべきです。
推奨プラクティス以下のとおりです:

* あなたがアプリケーションの開発者である場合、アプリケーションの [エントリスクリプト](structure-entry-scripts.md) 内、
  またはエントリスクリプトにインクルードされるスクリプト内で、依存関係を登録することができます。
* あなたが再配布可能な [エクステンション](structure-extensions.md) の開発者である場合は、エクステンションのブートストラップクラス内で
  依存関係を登録することができます。


まとめ <span id="summary"></span>
-------

依存性注入と [サービスロケータ](concept-service-locator.md) はともに、疎結合でよりテストしやすい方法でのソフトウェア構築を可能にする、
定番のデザインパターンです。依存性注入とサービスロケータへのより深い理解を得るために、 [Martin の記事](http://martinfowler.com/articles/injection.html)
を読むことを強くお勧めします。

Yiiはその [サービスロケータ](concept-service-locator.md) を、依存性注入(DI)コンテナの上に実装しています。
サービスロケータは、新しいオブジェクトのインスタンスを作成しようとしたとき、DI コンテナに呼び出しを転送します。
後者は、依存関係を、上で説明したように自動的に解決します。

