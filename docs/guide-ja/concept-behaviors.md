ビヘイビア
=========

ビヘイビアは [[yii\base\Behavior]] 、あるいはその子クラスのインスタンスです。ビヘイビアは
[ミックスイン](http://en.wikipedia.org/wiki/Mixin) としても知られ、既存の [[yii\base\Component|component]] クラスの
機能を、クラスの継承を変更せずに拡張することができます。コンポーネントにビヘイビアをアタッチすると、その
コンポーネントにはビヘイビアのメソッドとプロパティが "注入" され、それらのメソッドとプロパティは、
コンポーネントクラス自体に定義されているかのようにアクセスできるようになります。また、ビヘイビアは、
コンポーネントによってトリガされた [イベント](concept-events.md) に応答することができるので、
ビヘイビアでコンポーネントの通常のコード実行をカスタマイズすることができます。


ビヘイビアの定義 <span id="defining-behaviors"></span>
------------------

ビヘイビアを定義するには、 [[yii\base\Behavior]] あるいは子クラスを継承するクラスを作成します。たとえば:

```php
namespace app\components;

use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public $prop1;

    private $_prop2;

    public function getProp2()
    {
        return $this->_prop2;
    }

    public function setProp2($value)
    {
        $this->_prop2 = $value;
    }

    public function foo()
    {
        // ...
    }
}
```

上のコードは、 `app\components\MyBehavior` という、2つのプロパティ -- `prop1` と `prop2` -- と
`foo()` メソッドを持つビヘイビアクラスを定義します。`prop2` プロパティは、 `getProp2()` getter メソッドと `setProp2()` setter メソッドで定義されることに着目してください。
[[yii\base\Behavior]] は [[yii\base\Object]] を継承しているので、getter と​​ setter による [プロパティ](concept-properties.md) 定義をサポートします。

このクラスはビヘイビアなので、コンポーネントにアタッチされると、そのコンポーネントは `prop1` と `prop2` プロパティ、それと `foo()` メソッドを持つようになります。

> Tip: ビヘイビア内から、[[yii\base\Behavior::owner]] プロパティを介して、ビヘイビアをアタッチしたコンポーネントにアクセスすることができます。

コンポーネントイベントのハンドリング
------------------

ビヘイビアが、アタッチされたコンポーネントがトリガするイベントに応答する必要がある場合は、
[[yii\base\Behavior::events()]] メソッドをオーバーライドしましょう。たとえば:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```

[[yii\base\Behavior::events()]] メソッドは、イベントとそれに対応するハンドラのリストを返します。
上の例では [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] イベントがあること、
そのハンドラ定義である `beforeValidate()` を宣言しています。イベントハンドラを指定するときは、以下の表記方法が使えます:

* ビヘイビアクラスのメソッド名を参照する文字列 (上の例など)
* オブジェクトまたはクラス名と文字列のメソッド名 (括弧なし) 例 `[$object, 'methodName']`
* 無名関数

イベントハンドラのシグネチャは次のようにしてください。`$event` はイベントのパラメータを参照します。イベントの詳細については
[イベント](concept-events.md) セクションを参照してください。

```php
function ($event) {
}
```

ビヘイビアのアタッチ <span id="attaching-behaviors"></span>
-------------------

[[yii\base\Component|コンポーネント]] へのビヘイビアのアタッチは、静的にも動的にも可能です。実際は、前者のほうがより一般的ですが。

ビヘイビアを静的にアタッチするには、ビヘイビアをアタッチしたいコンポーネントクラスの [[yii\base\Component::behaviors()|behaviors()]] メソッドをオーバーライドします。
[[yii\base\Component::behaviors()|behaviors()]] メソッドは、ビヘイビアの [構成](concept-configurations.md) のリストを返さなければなりません。
各ビヘイビアの構成内容は、ビヘイビアのクラス名でも、構成情報配列でもかまいません。

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // 無名ビヘイビア ビヘイビアクラス名のみ
            MyBehavior::className(),

            // 名前付きビヘイビア ビヘイビアクラス名のみ
            'myBehavior2' => MyBehavior::className(),

            // 無名ビヘイビア 構成情報配列
            [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // 名前付きビヘイビア 構成情報配列
            'myBehavior4' => [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

ビヘイビア構成に対応する配列のキーを指定することによって、ビヘイビアに名前を関連付けることができます。この場合、ビヘイビアは *名前付きビヘイビア* と呼ばれます。上の例では、2つの名前付きビヘイビア​​
`myBehavior2` と `myBehavior4` があります。ビヘイビアが名前と関連付けられていない場合は、 *無名ビヘイビア* と呼ばれます。


ビヘイビアを動的にアタッチするには、ビヘイビアをアタッチしようとしているコンポーネントの [[yii\base\Component::attachBehavior()]] メソッドを呼びます:

```php
use app\components\MyBehavior;

// ビヘイビアオブジェクトをアタッチ
$component->attachBehavior('myBehavior1', new MyBehavior);

// ビヘイビアクラスをアタッチ
$component->attachBehavior('myBehavior2', MyBehavior::className());

// 構成情報配列をアタッチ
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::className(),
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```
[[yii\base\Component::attachBehaviors()]] メソッドを使うと、いちどに複数のビヘイビアをアタッチできます:

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior,  // 名前付きビヘイビア
    MyBehavior::className(),          // 無名ビヘイビア
]);
```

次のように、 [構成情報](concept-configurations.md) を通じてビヘイビアをアタッチすることもできます:

```php
[
    'as myBehavior2' => MyBehavior::className(),

    'as myBehavior3' => [
        'class' => MyBehavior::className(),
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```

詳しくは [構成情報](concept-configurations.md#configuration-format) セクションを参照してください。

ビヘイビアの使用 <span id="using-behaviors"></span>
---------------

ビヘイビアを使用するには、まず上記の方法に従って [[yii\base\Component|コンポーネント]] にアタッチします。ビヘイビアがコンポーネントにアタッチされれば、その使用方法はシンプルです。

あなたは、アタッチされているコンポーネントを介して、ビヘイビアの *パブリック* メンバ変数、または getter や setter によって定義されたプロパティにアクセスすることができます:

```php
// "prop1" はビヘイビアクラス内で定義されたプロパティ
echo $component->prop1;
$component->prop1 = $value;
```

また同様に、ビヘイビアの *パブリック* メソッドも呼ぶことができます:

```php
// foo() はビヘイビアクラス内で定義されたパブリックメソッド
$component->foo();
```

ご覧のように、 `$component` は `prop1` と `foo()` を定義していないにもかかわらず、
アタッチされたビヘイビアによって、それらをコンポーネント定義の一部であるかのように使うことができるのです。

もし2つのビヘイビアが同じプロパティやメソッドを定義し、かつ両方とも同じコンポーネントにアタッチされている場合は、
プロパティやメソッドのアクセス時に、*最初に* コンポーネントにアタッチされたビヘイビアが優先されます。

ビヘイビアはコンポーネントにアタッチされるとき、名前と関連付けられているかもしれません。その場合、
その名前を使用してビヘイビアオブジェクトにアクセスすることができます:

```php
$behavior = $component->getBehavior('myBehavior');
```

また、コンポーネントにアタッチされた全てのビヘイビアを取得することもできます:

```php
$behaviors = $component->getBehaviors();
```


ビヘイビアのデタッチ <span id="detaching-behaviors"></span>
-------------------

ビヘイビアをデタッチするには、ビヘイビアに付けられた名前とともに [[yii\base\Component::detachBehavior()]] を呼び出します:

```php
$component->detachBehavior('myBehavior1');
```

*全ての* ビヘイビアをデタッチすることもできます:

```php
$component->detachBehaviors();
```


`TimestampBehavior` の利用 <span id="using-timestamp-behavior"></span>
-------------------------

しめくくりに、[[yii\behaviors\TimestampBehavior]] を見てみましょう。このビヘイビアは、
保存時 (つまり挿入や更新) に、[[yii\db\ActiveRecord|アクティブレコード]] モデルの
タイムスタンプ属性の自動更新をサポートします。

まず、使用しようと考えている [[yii\db\ActiveRecord|アクティブレコード]] クラスに、このビヘイビアをアタッチします:

```php
namespace app\models\User;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
}
```

上のビヘイビア構成は、レコードが:

* 挿入されるとき、ビヘイビアは現在のタイムスタンプを `created_at` と `updated_at` 属性に割り当てます
* 更新されるとき、ビヘイビアは現在のタイムスタンプを `updated_at` 属性に割り当てます

所定の位置にそのコードを使用すると、もし `User` オブジェクトを設け、それを保存しようとしたら、そこで、
`created_at` と `updated_at` が自動的に現在のタイムスタンプで埋められます。

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // 現在のタイムスタンプが表示される
```

[[yii\behaviors\TimestampBehavior|TimestampBehavior]] は、指定された属性に現在のタイムスタンプを割り当てて
それをデータベースに保存する、便利なメソッド [[yii\behaviors\TimestampBehavior::touch()|touch()]] を提供します。

```php
$user->touch('login_time');
```

ビヘイビアとトレイトの比較 <span id="comparison-with-traits"></span>
----------------------

ビヘイビアは、主となるクラスにそのプロパティやメソッドを「注入する」という点で [トレイト](http://www.php.net/traits)
に似ていますが、これらは多くの面で異なります。以下に説明するように、それらは互いに長所と短所を持っています。
それらは代替手段というよりも、むしろ相互補完関係のようなものです。


### ビヘイビアを使う理由 <span id="pros-for-behaviors"></span>

ビヘイビアは通常のクラスのように、継承をサポートしています。いっぽうトレイトは、
言語サポートされたコピー&ペーストとみなすことができます。トレイトは継承をサポートしません。

ビヘイビアは、コンポーネントクラスの変更を必要とせず、コンポーネントに動的にアタッチまたはデタッチすることが可能です。トレイトを使用するには、トレイトを使ってクラスのコードを書き換える必要があります。

ビヘイビアは構成可能ですがトレイトは不可能です。

ビヘイビアは、イベントに応答することで、コンポーネントのコード実行をカスタマイズできます。

同じコンポーネントにアタッチされた異なるビヘイビア間で名前の競合がある場合、その競合は自動的に、
先にコンポーネントにアタッチされたものを優先することで解消されます。
別のトレイトが起こした名前競合の場合、影響を受けるプロパティやメソッドの名前変更による、手動での解決が必要です。


### トレイトを使う理由 <span id="pros-for-traits"></span>

ビヘイビアは時間もメモリも食うオブジェクトなので、トレイトはビヘイビアよりはるかに効率的です。

トレイトは言語構造であるため、IDE との相性に優れています。

