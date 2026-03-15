プロパティ
==========

PHPでは、クラスのメンバ変数は *プロパティ* とも呼ばれます。
これらの変数は、クラス定義の一部で、クラスのインスタンスの状態を表すために(すなわち、クラスのあるインスタンスを別のものと区別するために) 使用されます。
現実には、特別な方法でこのプロパティの読み書きを扱いたい場合がよくあります。
たとえば、`label` プロパティに割り当てられる文字列が常にトリミングされるようにしたい、など。
その仕事を成し遂げるために、あなたは次のようなコードを使おうと思えば使うことも出来ます。

```php
$object->label = trim($label);
```

上記のコードの欠点は、`label` プロパティを設定するすべてのコードで、`trim()` を呼び出す必要があるということです。
もし将来的に、`label` プロパティに、最初の文字を大文字にしなければならない、といった新たな要件が発生したら、
`label` に値を代入するすべてのコードを変更しなければなりません。
コードの繰り返しはバグを誘発するので、可能な限り避けたいところです。

この問題を解決するために、Yii は *getter* メソッドと *setter* メソッドをベースにしたプロパティ定義をサポートする、
[[yii\base\BaseObject]] 基底クラスを提供しています。
クラスがその機能を必要とするなら、[[yii\base\BaseObject]] またはその子クラスを継承しましょう。

> Note: Yiiのフレームワークのほぼすべてのコア・クラスは、 [[yii\base\BaseObject]] またはその子クラスを継承しています。
  これは、コア・クラスに getter または setter があれば、それをプロパティのように使用できることを意味します。

getter メソッドは、名前が `get` で始まるメソッドで、setter メソッドは、`set` で始まるメソッドです。
`get` または `set` 接頭辞の後の名前で、プロパティ名を定義します。次のコードに示すように、たとえば、`getLabel()` という getter と `setLabel()` という setter は、
`label` という名前のプロパティを定義します:

```php
namespace app\components;

use yii\base\BaseObject;

class Foo extends BaseObject
{
    private $_label;

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($value)
    {
        $this->_label = trim($value);
    }
}
```

詳しく言うと、getter および setter メソッドは、この場合には、内部的に `_label` と名付けられた private な属性を参照する
`label` というプロパティを作っています。

getter と setter によって定義されたプロパティは、クラスのメンバ変数のように使用することができます。主な違いは、
それらのプロパティが読み取りアクセスされるときは、対応する getter メソッドが呼び出されることであり、プロパティに値が割り当てられるときには、
対応する setter メソッドが呼び出されるということです。例えば、

```php
// $label = $object->getLabel(); と同じ
$label = $object->label;

// $object->setLabel('abc'); と同じ
$object->label = 'abc';
```

setter なしの getter で定義されたプロパティは、 *読み取り専用* です。そのようなプロパティに値を代入しようとすると、
[[yii\base\InvalidCallException|InvalidCallException]] が発生します。同様に、getter なしの setter で定義されたプロパティは、
*書き込み専用* で、そのようなプロパティを読み取りしようとしても、例外が発生します。
書き込み専用のプロパティを持つのは一般的ではありませんが。

getter と setter で定義されたプロパティには、いくつかの特別なルールと制限があります:

* この種のプロパティでは、名前の *大文字と小文字を区別しません* 。たとえば、 `$object->label` と `$object->Label` は同じです。
  これは、PHP のメソッド名が大文字と小文字を区別しないためです。
* この種のプロパティの名前と、クラスのメンバ変数の名前とが同じである場合、後者が優先されます。
  たとえば、上記の `Foo` クラスがメンバ変数 `label` を持っている場合は、`$object->label = 'abc'`
  という代入は *メンバ変数の* `label` に作用することになります。その行から `setLabel()` setter メソッドは呼び出されません。
* これらのプロパティは可視性をサポートしていません。プロパティが public、protected、private であるかどうかを、getter または setter メソッドの定義によって決めることは出来ません。
* プロパティは、 *静的でない* getter および setter によってのみ定義することが出来ます。静的なメソッドは同様には扱われません。
* 通常の `property_exists()` の呼び出しでは、マジック・プロパティが存在するかどうかを知ることは出来ません。
  それぞれ、[[yii\base\BaseObject::canGetProperty()|canGetProperty()]] または [[yii\base\BaseObject::canSetProperty()|canSetProperty()]] を呼び出さなければなりません。

このガイドの冒頭で説明した問題に戻ると、`label` に値が代入されているあらゆる箇所で `trim()` を呼ぶのではなく、
`setLabel()` という setter の内部だけで `trim()` を呼べば済むようになります。
さらに、新しい要求でラベルの先頭を大文字にする必要が発生しても、他のいっさいのコードに触れることなく、
すぐに `setLabel()` メソッドを変更することができます。一箇所の変更は、すべての `label` への代入に普遍的に作用します。
