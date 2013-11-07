Gii Extension for Yii 2
========================

This extension provides a Web-based code generator, called Gii, for Yii 2 applications.
You can use Gii to quickly generate models, forms, modules, CRUD, etc.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-gii "*"
```

or add

```
"yiisoft/yii2-gii": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
	'modules' => [
		'gii' => 'yii\gii\Module',
		...
	],
	...
];
```

You can then access Gii through the following URL:

```
http://localhost/path/to/index.php?r=gii
```
