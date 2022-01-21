イベント
========

イベントを使うと、既存のコードの特定の実行ポイントに、カスタム・コードを挿入することができます。イベントにカスタム・コードをアタッチすると、
イベントがトリガされたときにコードが自動的に実行されます。たとえば、メーラ・オブジェクトがメッセージを正しく送信できたとき、
`messageSent` イベントをトリガするとします。もしメッセージの送信がうまく行ったことを知りたければ、単に `messageSent`
イベントにトラッキング・コードを付与するだけで、それが可能になります。

Yiiはイベントをサポートするために、 [[yii\base\Component]] と呼ばれる基底クラスを導入してします。クラスがイベントをトリガする必要がある場合は、
[[yii\base\Component]] もしくはその子クラスを継承する必要があります。


イベント・ハンドラ <span id="event-handlers"></span>
------------------

イベント・ハンドラとは、アタッチされたイベントがトリガされたときに実行される [PHP コールバック](https://www.php.net/manual/ja/language.types.callable.php)
です。次のコールバックのいずれも使用可能です:

- 文字列で指定されたグローバル PHP 関数 (括弧を除く)、例えば `'trim'`。
- オブジェクトとメソッド名文字列の配列で指定された、オブジェクトのメソッド (括弧を除く)、例えば `[$object, 'methodName']`。
- クラス名文字列とメソッド名文字列の配列で指定された、静的なクラス・メソッド (括弧を除く)、例えば `['ClassName', 'methodName']`。
- 無名関数、例えば `function ($event) { ... }`。

イベント・ハンドラのシグネチャはこのようになります:

```php
function ($event) {
    // $event は yii\base\Event またはその子クラスのオブジェクト
}
```

`$event` パラメータを介して、イベント・ハンドラは発生したイベントに関して次の情報を得ることができます:

- [[yii\base\Event::name|イベント名]]
- [[yii\base\Event::sender|イベント送信元]]: `trigger()` メソッドが呼ばれたオブジェクト
- [[yii\base\Event::data|カスタム・データ]]: イベント・ハンドラをアタッチするときに提供されたデータ (次の項で説明します)


イベント・ハンドラをアタッチする <span id="attaching-event-handlers"></span>
--------------------------------

イベント・ハンドラは [[yii\base\Component::on()]] を呼び出すことでアタッチできます。たとえば:

```php
$foo = new Foo;

// このハンドラはグローバル関数です
$foo->on(Foo::EVENT_HELLO, 'function_name');

// このハンドラはオブジェクトのメソッドです
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// このハンドラは静的なクラスメソッドです
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// このハンドラは無名関数です
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // イベント処理ロジック
});
```

また、 [構成情報](concept-configurations.md) を通じてイベント・ハンドラをアタッチすることもできます。詳細については
[構成情報](concept-configurations.md) の章を参照してください。


イベント・ハンドラをアタッチするとき、 [[yii\base\Component::on()]] の3番目のパラメータとして、付加的なデータを提供することができます。
そのデータは、イベントがトリガされてハンドラが呼び出されるときに、ハンドラ内で利用きます。たとえば:

```php
// 次のコードはイベントがトリガされたとき "abc" を表示します
// "on" に3番目の引数として渡されたデータを $event->data が保持しているからです
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
    echo $event->data;
}
```

イベント・ハンドラの順序
------------------------

ひとつのイベントには、ひとつだけでなく複数のハンドラをアタッチすることができます。イベントがトリガされると、アタッチされたハンドラは、
それらがイベントにアタッチされた順序どおりに呼び出されます。あるハンドラがその後に続くハンドラの呼び出しを停止する必要がある場合は、
`$event` パラメータの [[yii\base\Event::handled]] プロパティを `true` に設定します:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

デフォルトでは、新たに接続されたハンドラは、イベントの既存のハンドラのキューに追加されます。その結果、
イベントがトリガされたとき、そのハンドラは一番最後に呼び出されます。もし、そのハンドラが最初に呼び出されるよう、
ハンドラのキューの先頭に新しいハンドラを挿入したい場合は、[[yii\base\Component::on()]] を呼び出すとき、4番目のパラメータ `$append` に `false` を渡します:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // ...
}, $data, false);
```

イベントをトリガする <span id="triggering-events"></span>
--------------------

イベントは、 [[yii\base\Component::trigger()]] メソッドを呼び出すことでトリガされます。このメソッドには **イベント名** が必須で、
オプションで、イベント・ハンドラに渡されるパラメータを記述したイベント・オブジェクトを渡すこともできます。たとえば:

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class Foo extends Component
{
    const EVENT_HELLO = 'hello';

    public function bar()
    {
        $this->trigger(self::EVENT_HELLO);
    }
}
```

上記のコードでは、すべての `bar()` の呼び出しは、 `hello` という名前のイベントをトリガします。

> Tip: イベント名を表すときはクラス定数を使用することをお勧めします。上記の例では、定数 `EVENT_HELLO` は
  `hello` イベントを表しています。このアプローチには 3 つの利点があります。まず、タイプミスを防ぐことができます。次に、IDE の自動補完サポートでイベントを
  認識できるようになります。第 3 に、クラスでどんなイベントがサポートされているかを表したいとき、定数の宣言をチェックするだけで済みます。

イベントをトリガするとき、イベント・ハンドラに追加情報を渡したいことがあります。たとえば、メーラーが `messageSent` イベントのハンドラに
メッセージ情報を渡して、ハンドラが送信されたメッセージの詳細を知ることができるようにしたいかもしれません。
これを行うために、 [[yii\base\Component::trigger()]] メソッドの2番目のパラメータとして、イベント・オブジェクトを与えることができます。
イベント・オブジェクトは [[yii\base\Event]] クラスあるいはその子クラスのインスタンスでなければなりません。
たとえば:

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class MessageEvent extends Event
{
    public $message;
}

class Mailer extends Component
{
    const EVENT_MESSAGE_SENT = 'messageSent';

    public function send($message)
    {
        // ... $message 送信 ...

        $event = new MessageEvent;
        $event->message = $message;
        $this->trigger(self::EVENT_MESSAGE_SENT, $event);
    }
}
```

[[yii\base\Component::trigger()]] メソッドが呼び出されたとき、この名前を付けられたイベントに
アタッチされたハンドラがすべて呼び出されます。


イベント・ハンドラをデタッチする <span id="detaching-event-handlers"></span>
--------------------------------

イベントからハンドラを取り外すには、 [[yii\base\Component::off()]] メソッドを呼び出します。たとえば:

```php
// このハンドラはグローバル関数です
$foo->off(Foo::EVENT_HELLO, 'function_name');

// このハンドラはオブジェクトのメソッドです
$foo->off(Foo::EVENT_HELLO, [$object, 'methodName']);

// このハンドラは静的なクラスメソッドです
$foo->off(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// このハンドラは無名関数です
$foo->off(Foo::EVENT_HELLO, $anonymousFunction);
```

一般的には、イベントにアタッチされたときどこかに保存してある場合を除き、無名関数を取り外そうとはしないでください。
上記の例は、無名関数は変数 `$anonymousFunction` として保存されていたものとしています。

イベントから *すべて* のハンドラを取り外すには、単純に、第 2 パラメータを指定せずに [[yii\base\Component::off()]] を呼び出します。

```php
$foo->off(Foo::EVENT_HELLO);
```


クラス・レベル・イベント・ハンドラ <span id="class-level-event-handlers"></span>
----------------------------------

ここまでの項では、*インスタンス・レベル* でのイベントにハンドラをアタッチする方法を説明してきました。
場合によっては、特定のインスタンスだけではなく、
クラスのすべてのインスタンスがトリガしたイベントに応答したいことがあります。
すべてのインスタンスにイベント・ハンドラをアタッチする代わりに、静的メソッド [[yii\base\Event::on()]] を呼び出すことで、
*クラス・レベル* でハンドラをアタッチすることができます。

たとえば、[アクティブ・レコード](db-active-record.md) オブジェクトは、データベースに新しいレコードを挿入するたびに、
[[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] イベントをトリガします。 *すべての*
[アクティブ・レコード](db-active-record.md) オブジェクトによって行われる挿入を追跡するには、次のコードが使えます：

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::debug(get_class($event->sender) . ' が挿入されました');
});
```

[[yii\db\ActiveRecord|ActiveRecord]] またはその子クラスのいずれかが、 [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]
をトリガするといつでも、このイベント・ハンドラが呼び出されます。ハンドラの中では、 `$event->sender` を通して、
イベントをトリガしたオブジェクトを取得することができます。

オブジェクトがイベントをトリガするときは、最初にインスタンス・レベルのハンドラを呼び出し、続いてクラス・レベルのハンドラとなります。

静的メソッド [[yii\base\Event::trigger()]] を呼び出すことによって、 *クラス・レベル* でイベントをトリガすることができます。
クラス・レベルでのイベントは、特定のオブジェクトに関連付けられていません。そのため、これはクラス・レベルのイベント・ハンドラだけを
呼び出します。たとえば:

```php
use yii\base\Event;

Event::on(Foo::class, Foo::EVENT_HELLO, function ($event) {
    var_dump($event->sender);  // "null" を表示
});

Event::trigger(Foo::class, Foo::EVENT_HELLO);
```

この場合、`$event->sender` は、オブジェクト・インスタンスではなく、`null` になることに注意してください。

> Note: クラス・レベルのハンドラは、そのクラスのあらゆるインスタンス、またはあらゆる子クラスのインスタンスがトリガしたイベントに応答
  してしまうため、よく注意して使わなければなりません。 [[yii\base\BaseObject]] のように、クラスが低レベルの基底クラスの場合は特にそうです。

クラス・レベルのイベント・ハンドラを取り外すときは、 [[yii\base\Event::off()]] を呼び出します。たとえば:

```php
// $handler をデタッチ
Event::off(Foo::class, Foo::EVENT_HELLO, $handler);

// Foo::EVENT_HELLO のすべてのハンドラをデタッチ
Event::off(Foo::class, Foo::EVENT_HELLO);
```


インタフェイスを使うイベント <span id="interface-level-event-handlers"></span>
------------------------------

イベントを扱うためには、もっと抽象的な方法もあります。
特定のイベントのために専用のインタフェイスを作っておき、必要な場合にいろいろなクラスでそれを実装するのです。

例えば、次のようなインタフェイスを作ります。

```php
namespace app\interfaces;

interface DanceEventInterface
{
    const EVENT_DANCE = 'dance';
}
```

そして、それを実装する二つのクラスを作ります。

```php
class Dog extends Component implements DanceEventInterface
{
    public function meetBuddy()
    {
        echo "ワン!";
        $this->trigger(DanceEventInterface::EVENT_DANCE);
    }
}

class Developer extends Component implements DanceEventInterface
{
    public function testsPassed()
    {
        echo "よっしゃ!";
        $this->trigger(DanceEventInterface::EVENT_DANCE);
    }
}
```

これらのクラスのどれかによってトリガされた `EVENT_DANCE` を扱うためには、インタフェイス・クラスの名前を最初の引数にして
[[yii\base\Event::on()|Event::on()]] を呼びます。

```php
Event::on('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, function ($event) {
    Yii::debug(get_class($event->sender) . ' が躍り上がって喜んだ。'); // 犬または開発者が躍り上がって喜んだことをログに記録。
});
```

これらのクラスのイベントをトリガすることも出来ます。

```php
// trigger event for Dog class
Event::trigger(Dog::class, DanceEventInterface::EVENT_DANCE);

// trigger event for Developer class
Event::trigger(Developer::class, DanceEventInterface::EVENT_DANCE);
```

ただし、このインタフェイスを実装する全クラスのイベントをトリガすることは出来ない、ということに注意して下さい。

```php
// これは動かない。このインタフェイスを実装するクラスのイベントはトリガされない。
Event::trigger('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```

イベント・ハンドラをデタッチするためには、[[yii\base\Event::off()|Event::off()]] を呼びます。例えば、

```php
// $handler をデタッチ
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, $handler);

// DanceEventInterface::EVENT_DANCE の全てのハンドラをデタッチ
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```


グローバル・イベント <span id="global-events"></span>
--------------------

Yiiは、いわゆる *グローバル・イベント* をサポートしています。これは、実際には、上記のイベント・メカニズムに基づいたトリックです。
グローバル・イベントは、 [アプリケーション](structure-applications.md) インスタンス自身などの、グローバルにアクセス可能なシングルトンを必要とします。

グローバル・イベントを作成するには、イベント送信者は、送信者の自前の `trigger()` メソッドを呼び出す代わりに、シングルトンの
`trigger()` メソッドを呼び出してイベントをトリガします。同じく、イベント・ハンドラも、シングルトンのイベントにアタッチされます。たとえば:

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // "app\components\Foo" を表示
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

グローバル・イベントを使用する利点は、オブジェクトによってトリガされるイベント・ハンドラを設けたいとき、オブジェクトがなくてもいい
ということです。その代わりに、ハンドラのアタッチとイベントのトリガはともに、(アプリケーションのインスタンスなど) シングルトンを
介して行われます。

しかし、グローバル・イベントの名前空間はあらゆる部分から共有されているので、ある種の名前空間 ("frontend.mail.sent"、"backend.mail.sent" など)
を導入するというような、賢いグローバル・イベントの名前付けをする必要があります。


ワイルドカード・イベント <span id="wildcard-events"></span>
------------------------

2.0.14 以降は、ワイルドカード・パターンに一致する複数のイベントに対してイベント・ハンドラを設定することが出来ます。
例えば、

```php
use Yii;

$foo = new Foo();

$foo->on('foo.event.*', function ($event) {
    // 'foo.event.' で始まる全てのイベントに対してトリガされる
    Yii::debug('trigger event: ' . $event->name);
});
```

クラス・レベル・イベントに対してもワイルドカード・パターンを用いることが出来ます。例えば、

```php
use yii\base\Event;
use Yii;

Event::on('app\models\*', 'before*', function ($event) {
    // 名前空間 'app\models' の全てのクラスで、名前が 'before' で始まる全てのイベントに対してトリガされる
    Yii::debug('trigger event: ' . $event->name . ' for class: ' . get_class($event->sender));
});
```

これを利用すると、以下のコードを使って、全てのアプリケーション・イベントを一つのハンドラでキャッチすることが出来ます。

```php
use yii\base\Event;
use Yii;

Event::on('*', '*', function ($event) {
    // 全てのクラスの全てのイベントに対してトリガされる
    Yii::debug('trigger event: ' . $event->name);
});
```

> Note: イベント・ハンドラにワイルドカードを使用する設定は、アプリケーションの性能を低下させ得ます。
  可能であれば避ける方が良いでしょう。

ワイルドカード・パターンで指定されたイベント・ハンドラをデタッチするためには、[[yii\base\Component::off()]] または [[yii\base\Event::off()]] の呼び出しにおいて、
同じパターンを使用しなければなりません。
イベント・ハンドラをデタッチする際にワイルドカードを指定すると、そのワイルドカードで指定されたハンドラだけがデタッチされることに留意して下さい。
通常のイベント名でアタッチされたハンドラは、パターンに合致する場合であっても、デタッチされません。例えば、

```php
use Yii;

$foo = new Foo();

// 通常のハンドラをアタッチする
$foo->on('event.hello', function ($event) {
    echo 'direct-handler'
});

// ワイルドカード・ハンドラをアタッチする
$foo->on('*', function ($event) {
    echo 'wildcard-handler'
});

// ワイルドカード・ハンドラをデタッチする!
$foo->off('*');

$foo->trigger('event.hello'); // 出力: 'direct-handler'
```
