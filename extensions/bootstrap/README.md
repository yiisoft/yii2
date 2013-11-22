Twitter Bootstrap Extension for Yii 2
=====================================

This is the Twitter Bootstrap extension for Yii 2. It encapsulates Bootstrap components
and plugins in terms of Yii widgets, and thus makes using Bootstrap components/plugins
in Yii applications extremely easy. For example, the following
single line of code in a view file would render a Bootstrap Progress plugin:

```php
<?= yii\bootstrap\Progress::widget(['percent' => 60, 'label' => 'test']) ?>
```


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-bootstrap "*"
```

or add

```
"yiisoft/yii2-bootstrap": "*"
```

to the require section of your `composer.json` file.

