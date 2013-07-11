Yii 2.0 Public Preview - Smarty View Renderer
======================

Thank you for choosing Yii - a high-performance component-based PHP framework.

If you are looking for a production-ready PHP framework, please use
[Yii v1.1](https://github.com/yiisoft/yii).

Yii 2.0 is still under heavy development. We may make significant changes
without prior notices. **Yii 2.0 is not ready for production use yet.**

[![Build Status](https://secure.travis-ci.org/yiisoft/yii2.png)](http://travis-ci.org/yiisoft/yii2)

This is the yii2-smarty extension.

Installation
----------------
The prefered way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
php composer.phar require yiisoft/yii2-smarty *
```

or add
```json
"yiisoft/yii2-smarty": "*"
```
to the require section of your composer.json.


*Note: You might have to run `php composer.phar selfupdate`*


Usage & Documentation
-----------

This extension has to be registered prior to usage.
To do this add the following to the $rendereres property of the view instance you want to use this with:

Example: 
```php
<?php
// config.php
return array(
	//....
	'components' => array(
		'view' => array(
			'class' => 'yii\base\View',
			'viewRenderers' => array(
				'tpl' => array(
					'class' => 'yii\smarty\ViewRenderer',
					//'cachePath' => '@runtime/Smarty/cache',
				),
			),
		),
	),
);
```

For further instructions refer to the related section in the guide.




