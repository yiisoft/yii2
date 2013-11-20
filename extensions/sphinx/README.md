Yii 2.0 Public Preview - Sphinx Extension
=========================================

Thank you for choosing Yii - a high-performance component-based PHP framework.

If you are looking for a production-ready PHP framework, please use
[Yii v1.1](https://github.com/yiisoft/yii).

Yii 2.0 is still under heavy development. We may make significant changes
without prior notices. **Yii 2.0 is not ready for production use yet.**

[![Build Status](https://secure.travis-ci.org/yiisoft/yii2.png)](http://travis-ci.org/yiisoft/yii2)

This is the yii2-sphinx extension.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
php composer.phar require yiisoft/yii2-sphinx "*"
```

or add
```
"yiisoft/yii2-sphinx": "*"
```
to the require section of your composer.json.


*Note: You might have to run `php composer.phar selfupdate`*


Usage & Documentation
---------------------

This extension adds [Sphinx](http://sphinxsearch.com/docs) full text search engine extension for the Yii framework.
This extension interact with Sphinx search daemon using MySQL protocol and [SphinxQL](http://sphinxsearch.com/docs/current.html#sphinxql) query language.
In order to setup Sphinx "searchd" to support MySQL protocol following configuration should be added:
```
searchd
{
	listen = localhost:9306:mysql41
	...
}
```

This extension supports all Sphinx features including [Runtime Indexes](http://sphinxsearch.com/docs/current.html#rt-indexes).
Since this extension uses MySQL protocol to access Sphinx, it shares base approach and much code from the
regular "yii\db" package.

To use this extension, simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'sphinx' => [
			'class' => 'yii\sphinx\Connection',
			'dsn' => 'mysql:host=127.0.0.1;port=9306;',
			'username' => '',
			'password' => '',
		],
	],
];
```