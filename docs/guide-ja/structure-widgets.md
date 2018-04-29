ウィジェット
============

ウィジェットは、[ビュー](structure-views.md) で使用される再利用可能な構成ブロックで、
複雑かつ構成可能なユーザ・インタフェイス要素をオブジェクト指向の流儀で作成するためのものです。
例えば、日付選択ウィジェットを使うと、入力として日付を選択することを可能にする素敵なデイト・ピッカーを生成することが出来ます。
このとき、あなたがしなければならないことは、次のようなコードをビューに挿入することだけです:

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget(['name' => 'date']) ?>
```

数多くのウィジェットが Yii にバンドルされています。
例えば、[[yii\widgets\ActiveForm|アクティブ・フォーム]] や、[[yii\widgets\Menu|メニュー]]、[jQuery UI ウィジェット](https://www.yiiframework.com/extension/yiisoft/yii2-jui)、[Twitter Bootstrap ウィジェット](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap) などです。
下記では、ウィジェットに関する基本的な知識の手引きをします。
特定のウィジェットの使い方について学ぶ必要がある場合は、クラス API ドキュメントを参照してください。


## ウィジェットを使う <span id="using-widgets"></span>

ウィジェットは主として [ビュー](structure-views.md) で使われます。
[[yii\base\Widget::widget()]] メソッドを呼んで、ビューでウィジェットを使います。
このメソッドは、ウィジェットを初期化するための [構成情報](concept-configurations.md) 配列を受け取り、ウィジェットのレンダリング結果を返します。
例えば、下記のコードは、日本語を使い、入力を `$model` の `from_date` 属性に保存するように構成された日付選択ウィジェットを挿入するものです。

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget([
    'model' => $model,
    'attribute' => 'from_date',
    'language' => 'ja',
    'dateFormat' => 'php:Y-m-d',
]) ?>
```

ウィジェットの中には、コンテントのブロックを受け取ることが出来るものもあります。
その場合、コンテントのブロックは [[yii\base\Widget::begin()]] と [[yii\base\Widget::end()]] の呼び出しで囲むようにしなければなりません。
例えば、次のコードは [[yii\widgets\ActiveForm]] ウィジェットを使ってログイン・フォームを生成するものです。
このウィジェットは、`begin()` と `end()` が呼ばれる場所で、それぞれ、開始と終了の `<form>` タグを生成します。
その間に置かれたものは全てそのままレンダリングされます。

```php
<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('ログイン') ?>
    </div>

<?php ActiveForm::end(); ?>
```

[[yii\base\Widget::widget()]] がウィジェットのレンダリング結果を返すのとは違って、[[yii\base\Widget::begin()]] メソッドがウィジェットのインスタンスを返すことに注意してください。
返されたウィジェットのインスタンスを使って、ウィジェットのコンテントを構築することが出来ます。

> Note: いくつかのウィジェットは、[[yii\base\Widget::end()]] が呼ばれるときに囲んだコンテンツを調整するため、
> [出力バッファリング](http://php.net/manual/ja/book.outcontrol.php) を使用します。
> この理由から、[[yii\base\Widget::begin()]] と [[yii\base\Widget::end()]] の呼び出しは、同じビュー・ファイルの中で発生するものと想定されています。
> この規則に従わない場合は、予期しない出力結果が生じ得ます。


### グローバルなデフォルトを構成する

あるタイプのウィジェットのグローバルなデフォルトを DI コンテナによって構成することが出来ます。

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

詳細については [依存注入コンテナのガイドの "実際の使いかた" のセクション](concept-di-container.md#practical-usage)
を参照してください。


## ウィジェットを作成する <span id="creating-widgets"></span>

ウィジェットを作成するためには、[[yii\base\Widget]] を拡張して、[[yii\base\Widget::init()]] および/または
[[yii\base\Widget::run()]] メソッドをオーバーライドします。
通常、`init()` メソッドはウィジェットのプロパティを初期化するコードを含むべきものであり、`run()` メソッドはウィジェットのレンダリング結果を生成するコードを含むべきものです。
レンダリング結果は、直接に "echo" しても、`run()` の返り値として文字列として返しても構いません。

次の例では、`HelloWidget` が `message` プロパティとして割り当てられたコンテントを HTML エンコードして表示します。
プロパティがセットされていない場合は、デフォルトとして "Hello World" を表示します。

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class HelloWidget extends Widget
{
    public $message;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Hello World';
        }
    }

    public function run()
    {
        return Html::encode($this->message);
    }
}
```

このウィジェットを使うために必要なことは、次のコードをビューに挿入するだけのことです。

```php
<?php
use app\components\HelloWidget;
?>
<?= HelloWidget::widget(['message' => 'おはよう']) ?>
```

下記は `HelloWidget` の変種で、`begin()` と `end()` の間に包まれたコンテントを受け取り、
それを HTML エンコードして表示するものです。

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class HelloWidget extends Widget
{
    public function init()
    {
        parent::init();
        ob_start();
    }

    public function run()
    {
        $content = ob_get_clean();
        return Html::encode($content);
    }
}
```

ご覧のように、`init()` の中で PHP の出力バッファが開始され、`init()` と `run()` の呼び出しの間の全ての出力がキャプチャされ、
`run()` の中で処理されて返されます。

> Info: [[yii\base\Widget::begin()]] を呼ぶと、ウィジェットの新しいインスタンスが作成され、
  ウィジェットのコンストラクタの最後で `init()` メソッドが呼ばれます。
  [[yii\base\Widget::end()]] を呼ぶと、`run()` メソッドが呼ばれて、その返り値が `end()` によって echo されます。

次のコードは、この `HelloWidget` の新しい変種をどのように使うかを示すものです:

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    ... タグを含みうるコンテント ...

<?php HelloWidget::end(); ?>
```

場合によっては、ウィジェットが大きな固まりのコンテントを表示する必要があるかもしれません。
コンテントを `run()` メソッドの中に埋め込むことも出来ますが、より良い方法は、コンテントを [ビュー](structure-views.md) の中に置いて、[[yii\base\Widget::render()]] を呼んでレンダリングすることです。
例えば、

```php
public function run()
{
    return $this->render('hello');
}
```

デフォルトでは、ウィジェット用のビューは `WidgetPath/views` ディレクトリの中のファイルに保存すべきものです。
ここで `WidgetPath` はウィジェットのクラス・ファイルを含むディレクトリを指します。
したがって、上記の例では、ウィジェット・クラスが `@app/components` に配置されていると仮定すると、`@app/components/views/hello.php` というビュー・ファイルがレンダリングされることになります。
[[yii\base\Widget::getViewPath()]] メソッドをオーバーライドして、ウィジェットのビュー・ファイルを含むディレクトリをカスタマイズすることが出来ます。


## ベスト・プラクティス <span id="best-practices"></span>

ウィジェットはビューのコードを再利用するためのオブジェクト指向の方法です。

ウィジェットを作成するときでも、MVC パターンに従うべきです。
一般的に言うと、ロジックはウィジェット・クラスに保持し、表現は [ビュー](structure-views.md) に保持すべきです。

ウィジェットは自己完結的に設計されるべきです。
言い換えると、ウィジェットを使うときに、他に何もしないでもビューに挿入することが出来るようにすべきです。
この要求は、ウィジェットが CSS、JavaScript、画像などの外部リソースを必要とする場合は、扱いにくい問題になり得ます。
幸いなことに、Yii はこの問題を解決するのに利用することが出来る [アセット・バンドル](structure-assets.md) のサポートを提供しています。

ウィジェットがビュー・コードだけを含む場合は、[ビュー](structure-views.md) と非常に似たものになります。
実際のところ、この場合、両者の唯一の違いは、ウィジェットが再配布可能なクラスである一方で、
ビューはアプリケーション内に保持することが望ましい素の PHP スクリプトである、というぐらいの事です。
