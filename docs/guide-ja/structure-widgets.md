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

Some widgets can take a block of content which should be enclosed between the invocation of
[[yii\base\Widget::begin()]] and [[yii\base\Widget::end()]]. For example, the following code uses the
[[yii\widgets\ActiveForm]] widget to generate a login form. The widget will generate the opening and closing
`<form>` tags at the place where `begin()` and `end()` are called, respectively. Anything in between will be
rendered as is.

```php
<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>

<?php ActiveForm::end(); ?>
```

Note that unlike [[yii\base\Widget::widget()]] which returns the rendering result of a widget, the method
[[yii\base\Widget::begin()]] returns an instance of the widget which you can use to build the widget content.


## Creating Widgets <a name="creating-widgets"></a>

To create a widget, extend from [[yii\base\Widget]] and override the [[yii\base\Widget::init()]] and/or
[[yii\base\Widget::run()]] methods. Usually, the `init()` method should contain the code that normalizes the widget
properties, while the `run()` method should contain the code that generates the rendering result of the widget.
The rendering result may be directly "echoed" or returned as a string by `run()`.

In the following example, `HelloWidget` HTML-encodes and displays the content assigned to its `message` property.
If the property is not set, it will display "Hello World" by default.

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

To use this widget, simply insert the following code in a view:

```php
<?php
use app\components\HelloWidget;
?>
<?= HelloWidget::widget(['message' => 'Good morning']) ?>
```

Below is a variant of `HelloWidget` which takes the content enclosed within the `begin()` and `end()` calls,
HTML-encodes it and then displays it.

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

As you can see, PHP output buffer is started in `init()` so that any output between the calls of `init()` and `run()`
can be captured, processed and returned in `run()`.

> Info: When you call [[yii\base\Widget::begin()]], a new instance of the widget will be created and the `init()` method
  will be called at the end of the widget constructor. When you call [[yii\base\Widget::end()]], the `run()` method
  will be called whose return result will be echoed by `end()`.

The following code shows how to use this new variant of `HelloWidget`:

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    content that may contain <tag>'s

<?php HelloWidget::end(); ?>
```

Sometimes, a widget may need to render a big chunk of content. While you can embed the content within the `run()`
method, a better approach is to put it in a [view](structure-views.md) and call [[yii\base\Widget::render()]] to
render it. For example,

```php
public function run()
{
    return $this->render('hello');
}
```

By default, views for a widget should be stored in files in the `WidgetPath/views` directory, where `WidgetPath`
stands for the directory containing the widget class file. Therefore, the above example will render the view file
`@app/components/views/hello.php`, assuming the widget class is located under `@app/components`. You may override
the [[yii\base\Widget::getViewPath()]] method to customize the directory containing the widget view files.


## Best Practices <a name="best-practices"></a>

Widgets are an object-oriented way of reusing view code.

When creating widgets, you should still follow the MVC pattern. In general, you should keep logic in widget
classes and keep presentation in [views](structure-views.md).

Widgets should be designed to be self-contained. That is, when using a widget, you should be able to just drop
it in a view without doing anything else. This could be tricky if a widget requires external resources, such as
CSS, JavaScript, images, etc. Fortunately, Yii provides the support for [asset bundles](structure-asset-bundles.md),
which can be utilized to solve the problem.

When a widget contains view code only, it is very similar to a [view](structure-views.md). In fact, in this case,
their only difference is that a widget is a redistributable class, while a view is just a plain PHP script
that you would prefer to keep it within your application.
