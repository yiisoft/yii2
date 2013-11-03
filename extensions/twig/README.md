Twig Extension for Yii 2
========================

This extension provides a `ViewRender` that would allow you to use Twig view template engine.

To use this extension, simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'view' => [
			'renderers' => [
				'tpl' => [
					'class' => 'yii\twig\ViewRenderer',
					//'cachePath' => '@runtime/Twig/cache',
					//'options' => [], /*  Array of twig options */
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
php composer.phar require yiisoft/yii2-twig "*"
```

or add

```
"yiisoft/yii2-twig": "*"
```

to the require section of your composer.json.
