Widgets
=======

> Note: This section is under development.

Widgets are self-contained building blocks for your views, a way to combine complex logic, display, and functionality into a single component. A widget:

* May contain advanced PHP programming
* Is typically configurable
* Is often provided data to be displayed
* Returns HTML to be shown within the context of the view

There are a good number of widgets bundled with Yii, such as [active form](form.md),
breadcrumbs, menu, and [wrappers around bootstrap component framework](bootstrap-widgets.md). Additionally there are
extensions that provide more widgets, such as the official widget for [jQueryUI](http://www.jqueryui.com) components.

In order to use a widget, your view file would do the following:

```php
// Note that you have to "echo" the result to display it
echo \yii\widgets\Menu::widget(['items' => $items]);

// Passing an array to initialize the object properties
$form = \yii\widgets\ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... form inputs here ...
\yii\widgets\ActiveForm::end();
```

In the first example in the code above, the [[yii\base\Widget::widget()|widget()]] method is used to invoke a widget
that just outputs content. In the second example, [[yii\base\Widget::begin()|begin()]] and [[yii\base\Widget::end()|end()]]
are used for a
widget that wraps content between method calls with its own output. In case of the form this output is the `<form>` tag
with some properties set.

