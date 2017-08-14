コンポーネント
==========

コンポーネントは、Yiiアプリケーションの主要な構成ブロックです。コンポーネントは [[yii\base\Component]] 、
またはその派生クラスのインスタンスです。コンポーネントが他のクラスに提供する主な機能は次の 3 つです:

* [プロパティ](concept-properties.md)
* [イベント](concept-events.md)
* [ビヘイビア](concept-behaviors.md)

個々にでも、組み合わせでも、これらの機能は Yii のクラスのカスタマイズ性と使いやすさをとても高めてくれます。たとえば、[[yii\jui\DatePicker|日付選択]] を行うユーザインターフェース·コンポーネントは、
対話型の日付選択UIを生成するとき、[ビュー](structure-views.md) で次のように使用することができます:

```php
use yii\jui\DatePicker;

echo DatePicker::widget([
    'language' => 'ja',
    'name'  => 'country',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]);
```

クラスが [[yii\base\Component]] を継承しているおかげで、ウィジェットのプロパティは簡単に記述できます。

コンポーネントは非常に強力ですが、 [イベント](concept-events.md) と [ビヘイビア](concept-behaviors.md) をサポートするため、
余分にメモリとCPU時間を要し、通常のオブジェクトよりも少し重くなります。
あなたのコンポーネントがこれら2つの機能を必要としない場合、[[yii\base\Component]] の代わりに、 [[yii\base\BaseObject]] からコンポーネントクラスを派生することを検討してもよいでしょう。
そうすることで、あなたのコンポーネントは、 [プロパティ](concept-properties.md) のサポートが維持されたまま、通常のPHPオブジェクトのように効率的になります。

[[yii\base\Component]] または [[yii\base\BaseObject]] からクラスを派生するときは、次の規約に従うことが推奨されます:

- コンストラクタをオーバーライドする場合は、コンストラクタの *最後の* パラメータとして `$config` パラメータを指定し、親のコンストラクタにこのパラメータを渡すこと。
- 自分がオーバーライドしたコンストラクタの *最後で* 、必ず親クラスのコンストラクタを呼び出すこと。
- [[yii\base\BaseObject::init()]] メソッドをオーバーライドする場合は、自分の `init()` メソッドの *最初に* 、必ず `init()` の親実装を呼び出すようにすること。

例:

```php
<?php

namespace yii\components\MyClass;

use yii\base\BaseObject;

class MyClass extends BaseObject
{
    public $prop1;
    public $prop2;

    public function __construct($param1, $param2, $config = [])
    {
        // ... 構成前の初期化

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... 構成後の初期化
    }
}
```
このガイドラインに従うことで、あなたのコンポーネントは生成時に [コンフィグ可能](concept-configurations.md) になります。例:

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// とする代わりに
$component = \Yii::createObject([
    'class' => MyClass::className(),
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> Note: [[Yii::createObject()]] を呼び出すアプローチは複雑に見えますが、より強力です。というのも、それが [依存性注入コンテナ](concept-di-container.md) 上に実装されているからです。
  

[[yii\base\BaseObject]] クラスには、次のオブジェクトライフサイクルが適用されます:

1. コンストラクタ内の事前初期化。ここでデフォルトのプロパティ値を設定することができます。
2. `$config` によるオブジェクトの構成。構成情報は、コンストラクタ内で設定されたデフォルト値を上書きすることがあります。
3. [[yii\base\BaseObject::init()|init()]] 内の事後初期化。サニティ・チェックやプロパティの正規化を行いたいときは、このメソッドをオーバーライドします。
4. オブジェクトのメソッド呼び出し。

最初の 3 つのステップは、すべて、オブジェクトのコンストラクタ内で発生します。これは、あなたがクラスインスタンス (つまり、オブジェクト) を得たときには、
すでにそのオブジェクトが適切な、信頼性の高い状態に初期化されていることを意味します。

