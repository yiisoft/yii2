依存注入コンテナ
================

依存注入 (DI) コンテナは、オブジェクトとそれが依存するすべてのオブジェクトを、インスタンス化し、設定する方法を知っているオブジェクトです。
なぜ DI コンテナが便利なのかは、[Martin Fowler の記事](http://martinfowler.com/articles/injection.html) の説明がわかりやすいでしょう。
ここでは、主に Yii の提供する DI コンテナの使用方法を説明します。


依存注入 <span id="dependency-injection"></span>
--------

Yii は [[yii\di\Container]] クラスを通して DI コンテナの機能を提供します。
これは、次の種類の依存注入をサポートしています:

* コンストラクタ・インジェクション
* メソッド・インジェクション
* セッター/プロパティ・インジェクション
* PHP コーラブル・インジェクション


### コンストラクタ・インジェクション <span id="constructor-injection"></span>

DI コンテナは、コンストラクタ引数の型ヒントの助けを借りて、コンストラクタ・インジェクションをサポートしています。
コンテナが新しいオブジェクトの作成に使用されるさい、そのオブジェクトがどういうクラスやインタフェイスに依存しているかを、型ヒントがコンテナに教えます。
コンテナは、依存するクラスやインタフェイスのインスタンスを取得して、
コンストラクタを通して、新しいオブジェクトにそれらを注入しようと試みます。たとえば

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


### メソッド・インジェクション <span id="method-injection"></span>

通常、クラスの依存はコンストラクタに渡されて、そのクラスの内部でライフサイクル全体にわたって利用可能になります。
メソッド・インジェクションを使うと、クラスのメソッドの一つだけに必要となる依存、例えば、コンストラクタに渡すことが不可能であったり、
大半のユース・ケースにおいてはオーバーヘッドが大きすぎるような依存を提供することが可能になります。

クラス・メソッドを次の例の `doSomething` メソッドのように定義することが出来ます。

```php
class MyClass extends \yii\base\Component
{
    public function __construct(/* 軽量の依存はここに */, $config = [])
    {
        // ...
    }

    public function doSomething($param1, \my\heavy\Dependency $something)
    {
        // $something を使って何かをする
    }
}
```

このメソッドを呼ぶためには、あなた自身で `\my\heavy\Dependency` のインスタンスを渡すか、または、次のように [[yii\di\Container::invoke()]] を使います。

```php
$obj = new MyClass(/*...*/);
Yii::$container->invoke([$obj, 'doSomething'], ['param1' => 42]); // $something は DI コンテナによって提供される
```

### セッター/プロパティ・インジェクション <span id="setter-and-property-injection"></span>

セッター/プロパティ・インジェクションは、[構成情報](concept-configurations.md) を通してサポートされます。
依存を登録するときや、新しいオブジェクトを作成するときに、対応するセッターまたはプロパティを通しての依存注入に使用される構成情報を、
コンテナに提供することが出来ます。
たとえば

```php
use yii\base\BaseObject;

class Foo extends BaseObject
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

> Info: [[yii\di\Container::get()]] メソッドは三番目のパラメータを、生成されるオブジェクトに適用されるべき構成情報配列として受け取ります。
  クラスが [[yii\base\Configurable]] インタフェイスを実装している場合 (例えば、クラスが [[yii\base\BaseObject]] である場合) には、
  この構成情報配列がクラスのコンストラクタの最後のパラメータとして渡されます。
  そうでない場合は、構成情報はオブジェクトが生成された *後で* 適用されることになります。


### PHP コーラブル・インジェクション <span id="php-callable-injection"></span>

この場合、コンテナは、登録された PHP のコーラブルを使用して、クラスの新しいインスタンスを構築します。
[[yii\di\Container::get()]] が呼ばれるたびに、対応するコーラブルが起動されます。
このコーラブルが、依存を解決し、新しく作成されたオブジェクトに適切に依存を注入する役目を果たします。
たとえば

```php
$container->set('Foo', function ($container, $params, $config) {
    $foo = new Foo(new Bar);
    // ... その他の初期化 ...
    return $foo;
});

$foo = $container->get('Foo');
```

新しいオブジェクトを構築するための複雑なロジックを隠蔽するために、スタティックなクラスメソッドをコーラブルとして使うことが出来ます。例えば、

```php
class FooBuilder
{
    public static function build($container, $params, $config)
    {
        $foo = new Foo(new Bar);
        // ... その他の初期化 ...
        return $foo;
    }
}

$container->set('Foo', ['app\helper\FooBuilder', 'build']);

$foo = $container->get('Foo');
```

このようにすれば、`Foo` クラスを構成しようとする人は、`Foo` がどのように構築されるかを気にする必要はもうなくなります。


依存を登録する <span id="registering-dependencies"></span>
--------------

[[yii\di\Container::set()]] を使って依存を登録することができます。登録には依存の名前だけでなく、依存の定義が必要です。
依存の名前は、クラス名、インタフェイス名、エイリアス名を指定することができます。
依存の定義には、クラス名、構成情報配列、PHPのコーラブルを指定できます。

```php
$container = new \yii\di\Container;

// クラス名そのままの登録。これは省略可能です。
$container->set('yii\db\Connection');

// インタフェイスの登録
// クラスがインタフェイスに依存する場合、対応するクラスが
// 依存オブジェクトとしてインスタンス化されます
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// エイリアス名の登録。$container->get('foo') を使って
// Connection のインスタンスを作成できます
$container->set('foo', 'yii\db\Connection');

// 構成情報をともなうクラスの登録。クラスが get() でインスタンス化
// されるとき構成情報が適用されます
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

// コンポーネント・インスタンスの登録
// $container->get('pageCache') は呼ばれるたびに毎回同じインスタンスを返します
$container->set('pageCache', new FileCache);
```

> Note: 依存の名前が対応する依存の定義と同じである場合は、
それを DI コンテナに登録する必要はありません。

`set()` を介して登録された依存は、依存が必要とされるたびにインスタンスを生成します。
[[yii\di\Container::setSingleton()]] を使うと、
単一のインスタンスしか生成しない依存を登録することができます:

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```


依存を解決する <span id="resolving-dependencies"></span>
--------------

依存を登録すると、新しいオブジェクトを作成するのに DI コンテナを使用することができます。
そして、コンテナが自動的に依存をインスタンス化し、新しく作成されたオブジェクトに注入して、
依存を解決します。依存の解決は再帰的に行われます。つまり、ある依存が他の依存を持っている場合、
それらの依存も自動的に解決されます。

[[yii\di\Container::get()|get()]] を使って、オブジェクトのインスタンスを作成または取得することができます。
このメソッドは依存の名前を引数として取りますが、依存の名前は、クラス名、インタフェイス名、あるいは、エイリアス名で指定できます。
依存の名前は、 [[yii\di\Container::set()|set()]] を介して登録されていることもあれば、
[[yii\di\Container::setSingleton()|setSingleton()]] を介して登録されていることもあります。
オプションで、クラスのコンストラクタのパラメータのリストや、[設定情報](concept-configurations.md) を渡して、新しく作成されるオブジェクトを構成することも出来ます。

たとえば、

```php
// "db" は事前に登録されたエイリアス名
$db = $container->get('db');

// これと同じ意味: $engine = new \app\components\SearchEngine($apiKey, $apiSecret, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey, $apiSecret], ['type' => 1]);
```

見えないところで、DIコンテナは、単に新しいオブジェクトを作成するよりもはるかに多くの作業を行います。
コンテナは、最初にクラスのコンストラクタを調査し、依存するクラスまたはインタフェイスの名前を見つけると、
自動的にそれらの依存を再帰的に解決します。

次のコードでより洗練された例を示します。`UserLister` クラスは `UserFinderInterface`
インタフェイスを実装するオブジェクトに依存します。`UserFinder` クラスはこのインタフェイスを実装していて、かつ、
`Connection` オブジェクトに依存します。これらのすべての依存は、クラスのコンストラクタのパラメータの型ヒントによって宣言されています。
依存の登録が適切にされていれば、DI コンテナは自動的にこれらの依存を解決し、単純に `get('userLister')`
を呼び出すだけで新しい `UserLister` インスタンスを作成できます。

```php
namespace app\models;

use yii\base\BaseObject;
use yii\db\Connection;
use yii\di\Container;

interface UserFinderInterface
{
    function findUser();
}

class UserFinder extends BaseObject implements UserFinderInterface
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

class UserLister extends BaseObject
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


実際の使用方法 <span id="practical-usage"></span>
--------------

あなたのアプリケーションの [エントリ・スクリプト](structure-entry-scripts.md) で `Yii.php` ファイルをインクルードするとき、
Yii は DI コンテナを作成します。この DI コンテナは [[Yii::$container]] を介してアクセス可能です。 [[Yii::createObject()]] を呼び出したとき、
このメソッドは実際にはコンテナの [[yii\di\Container::get()|get()]] メソッドを呼び出して新しいオブジェクトを作成します。
前述のとおり、DI コンテナは(もしあれば)自動的に依存を解決し、取得されたオブジェクトにそれらを注入します。
Yii は、新しいオブジェクトを作成するコアコードのほとんどにおいて [[Yii::createObject()]] を使用しています。このことは、
[[Yii::$container]] を操作することでグローバルにオブジェクトをカスタマイズすることができるということを意味しています。

例として、 [[yii\widgets\LinkPager]] のページ・ネーションボタンのデフォルト個数をグローバルにカスタマイズしてみましょう。

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

そして、次のコードでビューでウィジェットを使用すれば、`maxButtonCount` プロパティは、
クラスで定義されているデフォルト値 10 の代わりに 5 で初期化されます。

```php
echo \yii\widgets\LinkPager::widget();
```

ただし、DI コンテナを経由して設定された値を上書きすることは、まだ可能です:

```php
echo \yii\widgets\LinkPager::widget(['maxButtonCount' => 20]);
```

> Tip: ウィジェットの呼び出しで与えられたプロパティは常に DI コンテナが持つ定義を上書きします。
> たとえ、`'options' => ['id' => 'mypager']` のように配列を指定したとしても、
> それらは他のオプションとマージされるのでなく、他のオプションを置換えてしまいます。

もう一つの例は、DI コンテナの自動コンストラクタ・インジェクションの利点を活かすものです。
あなたのコントローラ・クラスが、ホテル予約サービスのような、いくつかの他のオブジェクトに依存するとします。
あなたは、コンストラクタのパラメータを通して依存を宣言して、DI コンテナにそれを解決させることができます。

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

あなたがブラウザからこのコントローラにアクセスすると、`BookingInterface` をインスタンス化できない、という不平を言う
エラーが表示されるでしょう。これは、この依存に対処する方法を DI コンテナに教える必要があるからです:

```php
\Yii::$container->set('app\components\BookingInterface', 'app\components\BookingService');
```

これで、あなたが再びコントローラにアクセスするときは、`app\components\BookingService`
のインスタンスが作成され、コントローラのコンストラクタに3番目のパラメータとして注入されるようになります。

高度な実際の使用方法 <span id="advanced-practical-usage"></span>
--------------------

API アプリケーションを開発していて、以下のクラスを持っているとします。

- `app\components\Request` クラス。`yii\web\Request` から拡張され、追加の機能を提供する。
- `app\components\Response` クラス。`yii\web\Response` から拡張。
  生成されるときに、`format` プロパティが `json` に設定されなければならない。
- `app\storage\FileStorage` および `app\storage\DocumentsReader` クラス。
  何らかのファイルストレージに配置されているドキュメントを操作するロジックを実装する。
  
  ```php
  class FileStorage
  {
      public function __construct($root) {
          // あれやこれや
      }
  }
  
  class DocumentsReader
  {
      public function __construct(FileStorage $fs) {
          // なんやかんや
      }
  }
  ```

[[yii\di\Container::setDefinitions()|setDefinitions()]] または [[yii\di\Container::setSingletons()|setSingletons()]] 
のメソッドに構成情報の配列を渡して、複数の定義を一度に構成することが可能です。
これらのメソッドは、構成情報配列を反復して、各アイテムに対し、
それぞれ [[yii\di\Container::set()|set()]] を呼び出します。

構成情報配列のフォーマットは、

 - `key`: クラス名、インタフェイス名、または、エイリアス名。
  このキーが [[yii\di\Container::set()|set()]] メソッドの最初の引数 `$class` として渡されます。
 - `value`: `$class` と関連づけられる定義。指定できる値は、[[yii\di\Container::set()|set()]] の `$definition`
  パラメータのドキュメントで説明されています。
  [[set()]] メソッドに二番目のパラメータ `$definition` として渡されます。

例として、上述の要求に従うように私たちのコンテナを構成しましょう。

```php
$container->setDefinitions([
    'yii\web\Request' => 'app\components\Request',
    'yii\web\Response' => [
        'class' => 'app\components\Response',
        'format' => 'json'
    ],
    'app\storage\DocumentsReader' => function ($container, $params, $config) {
        $fs = new app\storage\FileStorage('/var/tempfiles');
        return new app\storage\DocumentsReader($fs);
    }
]);

$reader = $container->get('app\storage\DocumentsReader); 
// 構成情報に書かれている依存とともに DocumentReader オブジェクトが生成されます
```

> Tip: バージョン 2.0.11 以降では、アプリケーションの構成情報を使って、宣言的なスタイルでコンテナを構成することが出来ます。
[構成情報](concept-configurations.md) のガイドの [アプリケーションの構成](concept-configurations.md#application-configurations)
のセクションを参照してください。

これで全部動きますが、`DocumentWriter` クラスを生成する必要がある場合には、`FileStorage` オブジェクトを生成する行をコピペすることになるでしょう。
もちろん、それが一番スマートな方法ではありません。

[依存を解決する](#resolving-dependencies) のセクションで説明したように、[[yii\di\Container::set()|set()]] と [[yii\di\Container::setSingleton()|setSingleton()]] は、
オプションで、第三の引数として依存のコンストラクタのパラメータを取ることが出来ます。
コンストラクタのパラメータを設定するために、以下の構成情報配列の形式を使うことが出来ます。

 - `key`: クラス名、インタフェイス名、または、エイリアス名。
  このキーが [[yii\di\Container::set()|set()]] メソッドの最初の引数 `$class` として渡されます。
 - `value`: 二つの要素を持つ配列。最初の要素は [[set()]] メソッドに二番目のパラメータ `$definition`
    として渡され、第二の要素が `$params` として渡されます。

では、私たちの例を修正しましょう。

```php
$container->setDefinitions([
    'tempFileStorage' => [ // 便利なようにエイリアスを作りました
        ['class' => 'app\storage\FileStorage'],
        ['/var/tempfiles'] // 何らかの構成ファイルから抽出することも可能
    ],
    'app\storage\DocumentsReader' => [
        ['class' => 'app\storage\DocumentsReader'],
        [Instance::of('tempFileStorage')]
    ],
    'app\storage\DocumentsWriter' => [
        ['class' => 'app\storage\DocumentsWriter'],
        [Instance::of('tempFileStorage')]
    ]
]);

$reader = $container->get('app\storage\DocumentsReader); 
// 前の例と全く同じオブジェクトが生成されます
```

`Instance::of('tempFileStorage')` という記法に気づいたことでしょう。
これは、[[yii\di\Container|Container]] が、`tempFileStorage` という名前で登録されている依存を黙示的に提供して、
`app\storage\DocumentsWriter` のコンストラクタの最初の引数として渡す、ということを意味しています。

> Note: [[yii\di\Container::setDefinitions()|setDefinitions()]] および [[yii\di\Container::setSingletons()|setSingletons()]]
  のメソッドは、バージョン 2.0.11 以降で利用できます。

構成情報の最適化にかかわるもう一つのステップは、いくつかの依存をシングルトンとして登録することです。
[[yii\di\Container::set()|set()]] を通じて登録された依存は、必要になるたびに、毎回インスタンス化されます。
しかし、ある種のクラスは実行時を通じて状態を変化させませんので、
アプリケーションのパフォーマンスを高めるためにシングルトンとして登録することが出来ます。

`app\storage\FileStorage` クラスが好例でしょう。これは単純な API によってファイル・システムに対する何らかの操作を実行するもの
(例えば `$fs->read()` や `$fs->write()`) ですが、これらの操作はクラスの内部状態を変化させないものです。
従って、このクラスのインスタンスを一度だけ生成して、それを複数回使用することが可能です。

```php
$container->setSingletons([
    'tempFileStorage' => [
        ['class' => 'app\storage\FileStorage'],
        ['/var/tempfiles']
    ],
]);

$container->setDefinitions([
    'app\storage\DocumentsReader' => [
        ['class' => 'app\storage\DocumentsReader'],
        [Instance::of('tempFileStorage')]
    ],
    'app\storage\DocumentsWriter' => [
        ['class' => 'app\storage\DocumentsWriter'],
        [Instance::of('tempFileStorage')]
    ]
]);

$reader = $container->get('app\storage\DocumentsReader');
```

いつ依存を登録するか <span id="when-to-register-dependencies"></span>
--------------------

依存は、新しいオブジェクトが作成されるとき必要とされるので、それらの登録は可能な限り早期に行われるべきです。
推奨されるプラクティスは以下のとおりです:

* あなたがアプリケーションの開発者である場合は、アプリケーションの構成情報を使って依存を登録することが出来ます。
  [構成情報](concept-configurations.md) のガイドの [アプリケーションの構成](concept-configurations.md#application-configurations)
  のセクションを読んでください。
* あなたが再配布可能な [エクステンション](structure-extensions.md) の開発者である場合は、エクステンションのブートストラップ・クラス内で
  依存を登録することができます。


まとめ <span id="summary"></span>
------

依存注入と [サービス・ロケータ](concept-service-locator.md) はともに、疎結合でよりテストしやすい方法でのソフトウェア構築を可能にする、
定番のデザインパターンです。
依存注入とサービス・ロケータへのより深い理解を得るために、 [Martin の記事](http://martinfowler.com/articles/injection.html)
を読むことを強くお勧めします。

Yii はその [サービス・ロケータ](concept-service-locator.md) を、依存注入 (DI) コンテナの上に実装しています。
サービス・ロケータは、新しいオブジェクトのインスタンスを作成しようとするとき、DI コンテナに呼び出しを転送します。
後者は、依存を、上で説明したように自動的に解決します。

