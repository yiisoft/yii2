Smarty Extension for Yii 2
==========================

This extension provides a `ViewRender` that would allow you to use Smarty view template engine.

To use this extension, simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'view' => [
			'renderers' => [
				'tpl' => [
					'class' => 'yii\smarty\ViewRenderer',
					//'cachePath' => '@runtime/Smarty/cache',
				],
			],
		],
	],
];
```

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-smarty "*"
```

or add

```json
"yiisoft/yii2-smarty": "*"
```

to the require section of your composer.json.
