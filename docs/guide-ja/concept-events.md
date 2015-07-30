イベント
======

イベントを使うと、既存のコードの特定の実行ポイントに、カスタムコードを挿入することができます。イベントにカスタムコードを添付すると、
イベントがトリガされたときにコードが自動的に実行されます。たとえば、メーラーオブジェクトがメッセージを正しく送信できたとき、
`messageSent` イベントをトリガするとします。もしメッセージの送信がうまく行ったことを知りたければ、単に `messageSent`
イベントにトラッキングコードを付与するだけで、それが可能になります。

Yiiはイベントをサポートするために、 [[yii\base\Component]] と呼ばれる基底クラスを導入してします。クラスがイベントをトリガする必要がある場合は、
[[yii\base\Component]] もしくはその子クラスを継承する必要があります。


イベントハンドラ <span id="event-handlers"></span>
--------------

イベントハンドラとは、関連するイベントがトリガされたときに実行される、 [PHP コールバック](http://www.php.net/manual/ja/language.types.callable.php)
です。次のコールバックのいずれも使用可能です:

- 文字列で指定されたグローバル PHP 関数 (括弧を除く)、例えば `'trim'`。
- オブジェクトとメソッド名文字列の配列で指定された、オブジェクトのメソッド (括弧を除く)、例えば `[$object, 'methodName']`。
- クラス名文字列とメソッド名文字列の配列で指定された、静的なクラスメソッド (括弧を除く)、例えば `['ClassName', 'methodName']`。
- 無名関数、例えば `function ($event) { ... }`。

イベントハンドラのシグネチャはこのようになります:

```php
function ($event) {
    // $event は yii\base\Event またはその子クラスのオブジェクト
}
```

`$event` パラメータを介して、イベントハンドラは発生したイベントに関して次の情報を得ることができます:

- [[yii\base\Event::name|イベント名]]
- [[yii\base\Event::sender|イベント送信元]]: `trigger()` メソッドを呼び出したオブジェクト
- [[yii\base\Event::data|カスタムデータ]]: イベントハンドラを接続するときに提供されたデータ (後述)


イベントハンドラのアタッチ <span id="attaching-event-handlers"></span>
------------------------

イベントハンドラは [[yii\base\Component::on()]] を呼び出すことでアタッチできます。たとえば:

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

また、 [構成情報](concept-configurations.md) を通じてイベントハンドラをアタッチすることもできます。詳細については
[構成情報](concept-configurations.md) の章を参照してください。

イベントハンドラをアタッチするとき、 [[yii\base\Component::on()]] の3番目のパラメータとして、付加的なデータを提供することができます。
そのデータは、イベントがトリガされてハンドラが呼び出されるときに、ハンドラ内で利用きます。たとえば:

```php
// 次のコードはイベントがトリガされたとき "abc" を表示します
// "on" に3番目の引数として渡されたデータを $event->data が保持しているからです
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
    echo $event->data;
}
```

イベントハンドラの順序
-------------------

ひとつのイベントには、ひとつだけでなく複数のハンドラをアタッチすることができます。イベントがトリガされると、アタッチされたハンドラは、
それらがイベントにアタッチされた順序どおりに呼び出されます。あるハンドラがその後に続くハンドラの呼び出しを停止する必要がある場合は、
`$event` パラメータの [[yii\base\Event::handled]] プロパティを true に設定します:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

デフォルトでは、新たに接続されたハンドラは、イベントの既存のハンドラのキューに追加されます。その結果、
イベントがトリガされたとき、そのハンドラは一番最後に呼び出されます。もし、そのハンドラが最初に呼び出されるよう、
ハンドラのキューの先頭に新しいハンドラを挿入したい場合は、[[yii\base\Component::on()]] を呼び出とき、4番目のパラメータ `$append` に false を渡します:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // ...
}, $data, false);
```

イベントのトリガー <span id="triggering-events"></span>
-----------------

イベントは、 [[yii\base\Component::trigger()]] メソッドを呼び出すことでトリガされます。このメソッドには **イベント名** が必須で、
オプションで、イベントハンドラに渡されるパラメータを記述したイベントオブジェクトを渡すこともできます。たとえば:

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

イベントをトリガするとき、イベントハンドラに追加情報を渡したいことがあります。たとえば、メーラーが `messageSent` イベントのハンドラに
メッセージ情報を渡して、ハンドラが送信されたメッセージの詳細を知ることができるようにしたいかもしれません。
これを行うために、 [[yii\base\Component::trigger()]] メソッドの2番目のパラメータとして、イベントオブジェクトを与えることができます。
イベントオブジェクトは [[yii\base\Event]] クラスあるいはその子クラスのインスタンスでなければなりません。たとえば:

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


イベントハンドラのデタッチ <span id="detaching-event-handlers"></span>
------------------------

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

イベントからすべてのハンドラを取り外すには、単純に、第 2 パラメータを指定せずに [[yii\base\Component::off()]] を呼び出します。

```php
$foo->off(Foo::EVENT_HELLO);
```


クラスレベル・イベントハンドラ <span id="class-level-event-handlers"></span>
--------------------------

ここまでの項では、 *インスタンスレベル* でのイベントにハンドラをアタッチする方法を説明してきました。
場合によっては、特定のインスタンスだけではなく、クラスのすべてのインスタンスがトリガした
イベントに応答したいことがあります。すべてのインスタンスにイベントハンドラをアタッチする代わりに、静的メソッド
[[yii\base\Event::on()]] を呼び出すことで、 *クラスレベル* でハンドラをアタッチすることができます。

たとえば、[アクティブレコード](db-active-record.md) オブジェクトは、データベースに新しいレコードを挿入するたびに、
[[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] イベントをトリガします。 *すべての*
[アクティブレコード](db-active-record.md) オブジェクトによって行われる挿入を追跡するには、次のコードが使えます：

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' が挿入されました');
});
```

[[yii\db\ActiveRecord|ActiveRecord]] またはその子クラスのいずれかが、 [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]
をトリガーするといつでも、このイベントハンドラが呼び出されます。ハンドラの中では、 `$event->sender` を通して、
イベントをトリガしたオブジェクトを取得することができます。

オブジェクトがイベントをトリガするときは、最初にインスタンスレベルのハンドラを呼び出し、続いてクラスレベルのハンドラとなります。

静的メソッド [[yii\base\Event::trigger()]] を呼び出すことによって、 *クラスレベル* でイベントをトリガすることができます。
クラスレベルでのイベントは、特定のオブジェクトに関連付けられていません。そのため、これはクラスレベルのイベントハンドラだけを
呼び出します。たとえば:

```php
use yii\base\Event;

Event::on(Foo::className(), Foo::EVENT_HELLO, function ($event) {
    echo $event->sender;  // "app\models\Foo" を表示
});

Event::trigger(Foo::className(), Foo::EVENT_HELLO);
```

この場合、`$event->sender` は、オブジェクトインスタンスではなく、イベントをトリガーするクラスの名前を指すことに注意してください。

> 注: クラスレベルのハンドラは、そのクラスのあらゆるインスタンス、またはあらゆる子クラスのインスタンスがトリガしたイベントに応答
  してしまうため、よく注意して使わなければなりません。 [[yii\base\Object]] のように、クラスが低レベルの基底クラスの場合は特にそうです。

クラスレベルのイベントハンドラを取り外すときは、 [[yii\base\Event::off()]] を呼び出します。たとえば:

```php
// $handler をデタッチ
Event::off(Foo::className(), Foo::EVENT_HELLO, $handler);

// Foo::EVENT_HELLO のすべてのハンドラをデタッチ
Event::off(Foo::className(), Foo::EVENT_HELLO);
```


グローバル・イベント <span id="global-events"></span>
-------------

Yiiは、実際に上記のイベントメカニズムに基づいたトリックである、いわゆる *グローバル・イベント* をサポートしています。
グローバル・イベントは、 [アプリケーション](structure-applications.md) インスタンス自身などの、グローバルにアクセス可能なシングルトンを必要とします。

グローバルイベントを作成するには、イベント送信者は、送信者の自前の `trigger()` メソッドを呼び出す代わりに、シングルトンの
`trigger()` メソッドを呼び出してイベントをトリガします。同じく、イベントハンドラも、シングルトンのイベントにアタッチされます。たとえば:

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // "app\components\Foo" を表示
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

グローバルイベントを使用する利点は、オブジェクトによってトリガされるイベントハンドラを設けたいとき、オブジェクトがなくてもいい
ということです。その代わりに、ハンドラのアタッチとイベントのトリガはともに、(アプリケーションのインスタンスなど) シングルトンを
介して行われます。

しかし、グローバルイベントの名前空間はあらゆる部分から共有されているので、ある種の名前空間 ("frontend.mail.sent"、"backend.mail.sent" など)
を導入するというような、賢いグローバルイベントの名前付けをする必要があります。

