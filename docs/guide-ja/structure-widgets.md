ウィジェット
============

ウィジェットは、[ビュー](structure-views.md) で使用される再利用可能な構成ブロックで、
複雑かつコンフィギュレーション可能なユーザインタフェイス要素をオブジェクト指向のやり方で作成するためのものです。
例えば、日付選択ウィジェットを使うと、入力として日付を選択することを可能にする素敵なデイトピッカーを生成することが出来ます。
このとき、あなたがしなければならないことは、次のようなコードをビューに挿入することだけです:

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget(['name' => 'date']) ?>
```

数多くのウィジェットが Yii にバンドルされています。例えば、[[yii\widgets\ActiveForm|アクティブフォーム]] や、
[[yii\widgets\Menu|メニュー]]、[jQuery UI ウィジェット](widget-jui.md)、[Twitter Bootstrap ウィジェット](widget-bootstrap.md) などです。
下記では、ウィジェットに関する基本的な知識の手引きをします。
特定のウィジェットの使い方について学ぶ必要がある場合は、クラス API ドキュメントを参照してください。


## ウィジェットを使う <a name="using-widgets"></a>

ウィジェットは主として [ビュー](structure-views.md) で使われます。
ビューでウィジェットを使うためには、[[yii\base\Widget::widget()]] メソッドを使うことが出来ます。
このメソッドは、ウィジェットを初期化するための [コンフィギュレーション](concept-configurations.md) 配列を受け取り、ウィジェットのレンダリング結果を返します。
例えば、下記のコードは、日本語を使い、入力を `$model` の `from_date`
属性に保存するように構成された日付選択ウィジェットを挿入するものです。

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget([
    'model' => $model,
    'attribute' => 'from_date',
    'language' => 'ja',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]) ?>
```

ウィジェットの中には、コンテントのブロックを受け取ることが出来るものもあります。その場合、コンテントのブロックは
[[yii\base\Widget::begin()]] と [[yii\base\Widget::end()]] の呼び出しの間に包むようにしなければなりません。
例えば、次のコードは [[yii\widgets\ActiveForm]] ウィジェットを使ってログインフォームを生成するものです。
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

[[yii\base\Widget::widget()]] がウィジェットのレンダリング結果を返すのとは違って、[[yii\base\Widget::begin()]] メソッドが
ウィジェットのインスタンスを返すことに注意してください。返されたウィジェットのインスタンスを使って、ウィジェットのコンテントを
構築することが出来ます。


## ウィジェットを作成する <a name="creating-widgets"></a>

ウィジェットを作成するためには、[[yii\base\Widget]] を拡張して、[[yii\base\Widget::init()]] および/または [[yii\base\Widget::run()]]
メソッドをオーバーライドします。通常、`init()` メソッドはウィジェットのプロパティを正規化するコードを含むべきものであり、
`run()` メソッドはウィジェットのレンダリング結果を生成するコードを含むべきものです。レンダリング結果は、直接に "echo"
しても、`run()` の返り値として文字列として返しても構いません。

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

下記は `HelloWidget` の変種で、`begin()` と `end()` の間に包まれたコンテントを受け取り、それを
HTML エンコードして表示するものです。

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

> Info|情報: [[yii\base\Widget::begin()]] を呼ぶと、ウィジェットの新しいインスタンスが作成され、ウィジェットのコンストラクタの
  最後で `init()` メソッドが呼ばれます。[[yii\base\Widget::end()]] を呼ぶと、`run()` メソッドが呼ばれて、その返り値が `end()`
  によって echo されます。

次のコードは、この `HelloWidget` の新しい変種をどのように使うかを示すものです:

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    ... タグを含みうるコンテント ...

<?php HelloWidget::end(); ?>
```

場合によっては、ウィジェットが大きな固まりのコンテントを表示する必要があるかもしれません。コンテントを `run()`
メソッドの中に埋め込むことも出来ますが、より良い方法は、コンテントを [ビュー](structure-views.md) の中に置いて、
[[yii\base\Widget::render()]] を呼んでレンダリングすることです。例えば、

```php
public function run()
{
    return $this->render('hello');
}
```

既定では、ウィジェット用のビューは `WidgetPath/views` ディレクトリの中のファイルに保存すべきものです。ここで
`WidgetPath` はウィジェットのクラスファイルを含むディレクトリを指します。したがって、上記の例では、ウィジェットクラスが
`@app/components` に配置されていると仮定すると、`@app/components/views/hello.php` というビューファイルがレンダリングされる
ことになります。[[yii\base\Widget::getViewPath()]] メソッドをオーバーライドして、ウィジェットのビューファイルを含むディレクトリを
カスタマイズすることが出来ます。


## 最善の慣行 <a name="best-practices"></a>

ウィジェットはビューのコードを再利用するためのオブジェクト指向の方法です。

When creating widgets, you should still follow the MVC pattern. In general, you should keep logic in widget
classes and keep presentation in [views](structure-views.md).

Widgets should be designed to be self-contained. That is, when using a widget, you should be able to just drop
it in a view without doing anything else. This could be tricky if a widget requires external resources, such as
CSS, JavaScript, images, etc. Fortunately, Yii provides the support for [asset bundles](structure-asset-bundles.md),
which can be utilized to solve the problem.

When a widget contains view code only, it is very similar to a [view](structure-views.md). In fact, in this case,
their only difference is that a widget is a redistributable class, while a view is just a plain PHP script
that you would prefer to keep it within your application.
