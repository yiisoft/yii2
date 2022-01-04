Widgets
=======

Widgets are reusable building blocks used in [views](structure-views.md) to create complex and configurable user
interface elements in an object-oriented fashion. For example, a date picker widget may generate a fancy date picker
that allows users to pick a date as their input. All you need to do is just to insert the code in a view
like the following:

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget(['name' => 'date']) ?>
```

There are a good number of widgets bundled with Yii, such as [[yii\widgets\ActiveForm|active form]],
[[yii\widgets\Menu|menu]], [jQuery UI widgets](https://www.yiiframework.com/extension/yiisoft/yii2-jui), [Twitter Bootstrap widgets](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap).
In the following, we will introduce the basic knowledge about widgets. Please refer to the class API documentation
if you want to learn about the usage of a particular widget.


## Using Widgets <span id="using-widgets"></span>

Widgets are primarily used in [views](structure-views.md). You can call the [[yii\base\Widget::widget()]] method
to use a widget in a view. The method takes a [configuration](concept-configurations.md) array for initializing
the widget and returns the rendering result of the widget. For example, the following code inserts a date picker
widget which is configured to use the Russian language and keep the input in the `from_date` attribute of `$model`.

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget([
    'model' => $model,
    'attribute' => 'from_date',
    'language' => 'ru',
    'dateFormat' => 'php:Y-m-d',
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

> Note: Some widgets will use [output buffering](https://www.php.net/manual/en/book.outcontrol.php) to adjust the enclosed
> content when [[yii\base\Widget::end()]] is called. For this reason calling [[yii\base\Widget::begin()]] and
> [[yii\base\Widget::end()]] is expected to happen in the same view file.
> Not following this rule may result in unexpected output.


### Configuring global defaults

Global defaults for a widget type could be configured via DI container:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

See ["Practical Usage" section in Dependency Injection Container guide](concept-di-container.md#practical-usage) for
details.


## Creating Widgets <span id="creating-widgets"></span>

Widget can be created in either of two different ways depending on the requirement.

### 1: Utilizing `widget()` method

To create a widget, extend from [[yii\base\Widget]] and override the [[yii\base\Widget::init()]] and/or
[[yii\base\Widget::run()]] methods. Usually, the `init()` method should contain the code that initializes the widget
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


Sometimes, a widget may need to render a big chunk of content. While you can embed the content within the `run()`
method, a better approach is to put it in a [view](structure-views.md) and call [[yii\base\Widget::render()]] to
render it. For example,

```php
public function run()
{
    return $this->render('hello');
}
```

### 2: Utilizing `begin()` and `end()` methods

This is similar to above one with minor difference.
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

As you can see, PHP's output buffer is started in `init()` so that any output between the calls of `init()` and `run()`
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

    sample content that may contain one or more <strong>HTML</strong> <pre>tags</pre>

    If this content grows too big, use sub views

    For e.g.

    <?php echo $this->render('viewfile'); // Note: here render() method is of class \yii\base\View as this part of code is within view file and not in Widget class file ?>

<?php HelloWidget::end(); ?>
```

By default, views for a widget should be stored in files in the `WidgetPath/views` directory, where `WidgetPath`
stands for the directory containing the widget class file. Therefore, the above example will render the view file
`@app/components/views/hello.php`, assuming the widget class is located under `@app/components`. You may override
the [[yii\base\Widget::getViewPath()]] method to customize the directory containing the widget view files.


## Best Practices <span id="best-practices"></span>

Widgets are an object-oriented way of reusing view code.

When creating widgets, you should still follow the MVC pattern. In general, you should keep logic in widget
classes and keep presentation in [views](structure-views.md).

Widgets should be designed to be self-contained. That is, when using a widget, you should be able to just drop
it in a view without doing anything else. This could be tricky if a widget requires external resources, such as
CSS, JavaScript, images, etc. Fortunately, Yii provides the support for [asset bundles](structure-assets.md),
which can be utilized to solve the problem.

When a widget contains view code only, it is very similar to a [view](structure-views.md). In fact, in this case,
their only difference is that a widget is a redistributable class, while a view is just a plain PHP script
that you would prefer to keep within your application.

