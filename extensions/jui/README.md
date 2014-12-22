JUI Extension for Yii 2
=======================

This is the JQuery UI extension for Yii 2. It encapsulates [JQuery UI widgets](http://jqueryui.com/) as Yii widgets,
and makes using JQuery UI widgets in Yii applications extremely easy. For example, the following
single line of code in a view file would render a [JQuery UI DatePicker](http://api.jqueryui.com/datepicker/) widget:

```php
<?= yii\jui\DatePicker::widget(['name' => 'attributeName']) ?>
```

Configuring the Jquery UI options should be done using the clientOptions attribute:

```php
<?= yii\jui\DatePicker::widget(['name' => 'attributeName', 'clientOptions' => ['defaultDate' => '2014-01-01']]) ?>
```

If you want to use the JUI widget in an ActiveForm, it can be done like this:

```php
<?= $form->field($model,'attributeName')->widget(DatePicker::className(),['clientOptions' => ['defaultDate' => '2014-01-01']]) ?>
```


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-jui "*"
```

or add

```
"yiisoft/yii2-jui": "*"
```

to the require section of your `composer.json` file.
