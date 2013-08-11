Yii 2.0 Public Preview - JUI Extension
======================

Thank you for choosing Yii - a high-performance component-based PHP framework.

If you are looking for a production-ready PHP framework, please use
[Yii v1.1](https://github.com/yiisoft/yii).

Yii 2.0 is still under heavy development. We may make significant changes
without prior notices. **Yii 2.0 is not ready for production use yet.**

[![Build Status](https://secure.travis-ci.org/yiisoft/yii2.png)](http://travis-ci.org/yiisoft/yii2)

This is the yii2-jui extension.

Installation
----------------
The preferred way to install this extension is [composer](http://getcomposer.org/download/).

Either run
```
php composer.phar require yiisoft/yii2-jui*
```

or add
```
"yiisoft/yii2-jui": "*"
```
to the require section of your composer.json.


*Note: You might have to run `php composer.phar selfupdate`*


Usage & Documentation
-----------

This extension provides multiple widgets to work with jquery.ui, as well as a set of compatible jquery.ui files.

You can use these widgets in your view files after you have registered the corresponding assets.

Example:
-----------
```php
echo ProgressBar::widget(array(
	'clientOptions' => array(
		'value' => 75,
	),
));

For further instructions refer to the guide (once it is finished)




