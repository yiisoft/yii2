Yii 2.0 Public Preview - SwiftMailer Mail Solution
==================================================

Thank you for choosing Yii - a high-performance component-based PHP framework.

If you are looking for a production-ready PHP framework, please use
[Yii v1.1](https://github.com/yiisoft/yii).

Yii 2.0 is still under heavy development. We may make significant changes
without prior notices. **Yii 2.0 is not ready for production use yet.**

[![Build Status](https://secure.travis-ci.org/yiisoft/yii2.png)](http://travis-ci.org/yiisoft/yii2)

This is the yii2-swiftmailer extension.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
php composer.phar require yiisoft/yii2-swiftmailer "*"
```

or add
```json
"yiisoft/yii2-swiftmailer": "*"
```
to the require section of your composer.json.


*Note: You might have to run `php composer.phar selfupdate`*


Usage & Documentation
---------------------

This extension has to be registered prior to usage.
To enable this view renderer add it to the $rendereres property of your view object.

Example:

```php
<?php
// config.php
return [
	//....
	'components' => [
		'mail' => [
			'class' => 'yii\swiftmailer\Mailer',
		],
	],
];
```

For further instructions refer to the related section in the yii guide.
