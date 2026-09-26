ヘルパ
======

> Note: このセクションはまだ執筆中です。

Yii は、一般的なコーディングのタスク、例えば、文字列や配列の操作、HTML コードの生成などを手助けする多くのクラスを提供しています。
これらのヘルパ・クラスは `yii\helpers` 名前空間の下に編成されており、すべてスタティックなクラス
(すなわち、スタティックなプロパティとメソッドのみを含み、インスタンス化すべきでないクラス) です。

ヘルパ・クラスは、そのスタティックなメソッドの一つを直接に呼び出すことによって使用します。例えば、

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> Note: [ヘルパ・クラスをカスタマイズする](#customizing-helper-classes) ことをサポートするために、Yii はコア・ヘルパ・クラスのすべてを二つのクラスに分割しています。
> すなわち、基底クラス (例えば `BaseArrayHelper`) と具象クラス (例えば `ArrayHelper`) です。
> ヘルパを使うときは、具象クラスのみを使うべきであり、基底クラスは決して使ってはいけません。


コア・ヘルパ・クラス
--------------------

以下のコア・ヘルパ・クラスが Yii のリリースにおいて提供されています。

- [配列ヘルパ](helper-array.md)
- Console
- FileHelper
- FormatConverter
- [Html ヘルパ](helper-html.md)
- HtmlPurifier
- Imagine (yii2-imagine エクステンションによって提供)
- Inflector
- [Json](helper-json.md)
- Markdown
- StringHelper
- [Url ヘルパ](helper-url.md)
- VarDumper


ヘルパ・クラスをカスタマイズする <span id="customizing-helper-classes"></span>
------------------------------

コア・ヘルパ・クラス (例えば [[yii\helpers\ArrayHelper]]) をカスタマイズするためには、
そのヘルパに対応する基底クラス (例えば [[yii\helpers\BaseArrayHelper]]) を拡張するクラスを作成して、名前空間も含めて、
対応する具象クラス (例えば [[yii\helpers\ArrayHelper]]) と同じ名前を付けます。
このクラスが、フレームワークのオリジナルの実装を置き換えるものとしてセットアップされます。

次の例は、[[yii\helpers\ArrayHelper]] クラスの [[yii\helpers\ArrayHelper::merge()|merge()]]
メソッドをカスタマイズする方法を示すものです。

```php
<?php

namespace yii\helpers;

class ArrayHelper extends BaseArrayHelper
{
    public static function merge($a, $b)
    {
        // あなた独自の実装
    }
}
```

あなたのクラスを `ArrayHelper.php` という名前のファイルに保存します。このファイルはどこに置いても構いません。例えば、`@app/components` に置くことにしましょう。

次に、アプリケーションの [エントリ・スクリプト](structure-entry-scripts.md) で、
次のコード行を `yii.php` ファイルをインクルードする行の後に追加して、[Yii クラス・オートローダ](concept-autoloading.md) に、
フレームワークから本来のヘルパ・クラスをロードする代りに、あなたのカスタム・クラスをロードすべきことを教えます。

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

ヘルパ・クラスのカスタマイズは、ヘルパの既存の関数の振る舞いを変更したい場合にだけ役立つものであることに注意してください。
アプリケーションの中で使用する関数を追加したい場合は、
そのための独立したヘルパを作成する方が良いでしょう。
