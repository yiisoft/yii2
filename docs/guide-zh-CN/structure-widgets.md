小部件
Widgets
=======

小部件是在 [视图](structure-views.md) 中使用的可重用单元，使用面向对象方式创建复杂和可配置用户界面单元。
例如，日期选择器小部件可生成一个精致的允许用户选择日期的日期选择器，
你只需要在视图中插入如下代码：
like the following:
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

Yii提供许多优秀的小部件，比如[[yii\widgets\ActiveForm|active form]], [yii\widgets\Menu|menu]], 
[jQuery UI widgets](widget-jui.md), [Twitter Bootstrap widgets](widget-bootstrap.md)。
接下来介绍小部件的基本知识，如果你想了解某个小部件请参考对应的类API文档。
There are a good number of widgets bundled with Yii, such as [[yii\widgets\ActiveForm|active form]],
[[yii\widgets\Menu|menu]], [jQuery UI widgets](widget-jui.md), [Twitter Bootstrap widgets](widget-bootstrap.md).
In the following, we will introduce the basic knowledge about widgets. Please refer to the class API documentation
if you want to learn about the usage of a particular widget.


## 使用小部件 <a name="using-widgets"></a>
## Using Widgets <a name="using-widgets"></a>

小部件基本上在[views](structure-views.md)中使用，在视图中可调用 [[yii\base\Widget::widget()]] 方法使用小部件。
该方法使用 [配置](concept-configurations.md) 数组初始化小部件并返回小部件渲染后的结果。
例如如下代码插入一个日期选择器小部件，它配置为使用俄罗斯语，输入框内容为`$model`的`from_date`属性值。
Widgets are primarily used in [views](structure-views.md). You can call the [[yii\base\Widget::widget()]] method
to use a widget in a view. The method takes a [configuration](concept-configurations.md) array for initializing
the widget and returns the rendering result of the widget. For example, the following code inserts a date picker
widget which is configured to use Russian language and keep the input in the `from_date` attribute of `$model`.

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget([
    'model' => $model,
    'attribute' => 'from_date',
    'language' => 'ru',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]) ?>
```

一些小部件可在[[yii\base\Widget::begin()]] 和 [[yii\base\Widget::end()]] 调用中使用数据内容。Some widgets can take a block of content which should be enclosed between the invocation of
例如如下代码使用[[yii\widgets\ActiveForm]]小部件生成一个登录表单，
小部件会在`begin()` 和0 `end()`执行处分别生成`<form>`的开始标签和结束标签，中间的任何代码也会被渲染。
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

注意和调用 [[yii\base\Widget::widget()]] 返回渲染结果不同，
调用 [[yii\base\Widget::begin()]] 方法返回一个可组建小部件内容的小部件实例。
Note that unlike [[yii\base\Widget::widget()]] which returns the rendering result of a widget, the method
[[yii\base\Widget::begin()]] returns an instance of the widget which you can use to build the widget content.


## 创建小部件 <a name="creating-widgets"></a>
## Creating Widgets <a name="creating-widgets"></a>

继承 [[yii\base\Widget]] 类并覆盖 [[yii\base\Widget::init()]] 和/或
[[yii\base\Widget::run()]] 方法可创建小部件。通常`init()` 方法处理小部件属性， 
`run()` 方法包含小部件生成渲染结果的代码。
渲染结果可在`run()`方法中直接"echoed"输出或以字符串返回。
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

使用这个小部件只需在视图中简单使用如下代码:
To use this widget, simply insert the following code in a view:

```php
<?php
use app\components\HelloWidget;
?>
<?= HelloWidget::widget(['message' => 'Good morning']) ?>
```

以下是另一种可在`begin()` 和 `end()`调用中使用的`HelloWidget`，HTML编码内容然后显示。
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

如上所示，PHP输出缓冲在`init()`启动，所有在`init()` 和 `run()`方法之间的输出内容都会被获取，并在`run()`处理和返回。
As you can see, PHP output buffer is started in `init()` so that any output between the calls of `init()` and `run()`
can be captured, processed and returned in `run()`.

> 补充: 当你调用 [[yii\base\Widget::begin()]] 时会创建一个新的小部件实例并在构造结束时调用`init()`方法，
  在`end()`时会调用`run()`方法并输出返回结果。
> Info: When you call [[yii\base\Widget::begin()]], a new instance of the widget will be created and the `init()` method
  will be called at the end of the widget constructor. When you call [[yii\base\Widget::end()]], the `run()` method
  will be called whose return result will be echoed by `end()`.

如下代码显示如何使用这种 `HelloWidget`:

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    content that may contain <tag>'s

<?php HelloWidget::end(); ?>
```

有时小部件需要渲染很多内容，一种更好的办法是将内容放入一个[视图](structure-views.md)文件，
然后调用[[yii\base\Widget::render()]]方法渲染该视图文件，例如：
Sometimes, a widget may need to render a big chunk of content. While you can embed the content within the `run()`
method, a better approach is to put it in a [view](structure-views.md) and call [[yii\base\Widget::render()]] to
render it. For example,

```php
public function run()
{
    return $this->render('hello');
}
```

小部件的视图文件默认存储在`WidgetPath/views`目录，`WidgetPath`代表小部件类文件所在的目录。
假如上述示例小部件类文件在`@app/components`下，会渲染`@app/components/views/hello.php`视图文件。 You may override
可以覆盖[[yii\base\Widget::getViewPath()]]方法自定义视图文件所在路径。
By default, views for a widget should be stored in files in the `WidgetPath/views` directory, where `WidgetPath`
stands for the directory containing the widget class file. Therefore, the above example will render the view file
`@app/components/views/hello.php`, assuming the widget class is located under `@app/components`. You may override
the [[yii\base\Widget::getViewPath()]] method to customize the directory containing the widget view files.


## 最佳实践 <a name="best-practices"></a>
## Best Practices <a name="best-practices"></a>

小部件是面向对象方式来重用视图代码。
Widgets are an object-oriented way of reusing view code.

创建小部件时仍需要遵循MVC模式，通常逻辑代码在小部件类，展示内容在[视图](structure-views.md)中。
When creating widgets, you should still follow the MVC pattern. In general, you should keep logic in widget
classes and keep presentation in [views](structure-views.md).

小部件设计时应是独立的，也就是说使用一个小部件时候，可以直接丢弃它而不需要额外的处理。
但是当小部件需要外部资源如CSS, JavaScript, 图片等会比较棘手，
幸运的时候Yii提供 [资源包](structure-asset-bundles.md) 来解决这个问题。
which can be utilized to solve the problem.
Widgets should be designed to be self-contained. That is, when using a widget, you should be able to just drop
it in a view without doing anything else. This could be tricky if a widget requires external resources, such as
CSS, JavaScript, images, etc. Fortunately, Yii provides the support for [asset bundles](structure-asset-bundles.md),
which can be utilized to solve the problem.

当一个小部件只包含视图代码，它和[视图](structure-views.md)很相似，
实际上，在这种情况下，唯一的区别是小部件是可以重用类，视图只是应用中使用的普通PHP脚本。
When a widget contains view code only, it is very similar to a [view](structure-views.md). In fact, in this case,
their only difference is that a widget is a redistributable class, while a view is just a plain PHP script
that you would prefer to keep it within your application.
