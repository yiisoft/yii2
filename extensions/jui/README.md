JUI Extension for Yii 2
=======================

This is the JQuery UI extension for Yii 2. It encapsulates JQuery UI widgets as Yii widgets,
and makes using JQuery UI widgets in Yii applications extremely easy. For example, the following
single line of code in a view file would render a JQuery UI DatePicker widget:

```php
<?= yii\jui\DatePicker::widget(['name' => 'start']) ?>
```


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-jui "*"
```

or add

```
"yiisoft/yii2-jui": "*"
```

to the require section of your `composer.json` file.

