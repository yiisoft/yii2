Yii 2.0 Public Preview - MongoDb Extension
==========================================

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
php composer.phar require yiisoft/yii2-mongo "*"
```

or add
```
"yiisoft/yii2-mongo": "*"
```
to the require section of your composer.json.


*Note: You might have to run `php composer.phar selfupdate`*


Usage & Documentation
---------------------

This extension adds [MongoDB](http://www.mongodb.org/) data storage support for the Yii2 framework.

Note: extension requires [MongoDB PHP Extension](http://us1.php.net/manual/en/book.mongo.php) version 1.3.0 or higher.

To use this extension, simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'mongo' => [
			'class' => '\yii\mongo\Connection',
			'dsn' => 'mongodb://developer:password@localhost:27017/mydatabase',
		],
	],
];
```

This extension provides ActiveRecord solution similar ot the [[\yii\db\ActiveRecord]].
To declare an ActiveRecord class you need to extend [[\yii\mongo\ActiveRecord]] and
implement the `collectionName` and 'attributes' methods:

```php
use yii\mongo\ActiveRecord;

class Customer extends ActiveRecord
{
	/**
	 * @return string the name of the index associated with this ActiveRecord class.
	 */
	public static function collectionName()
	{
		return 'customer';
	}

	/**
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		return ['name', 'email', 'address', 'status'];
	}
}
```

You can use [[\yii\data\ActiveDataProvider]] with the [[\yii\mongo\Query]] and [[\yii\mongo\ActiveQuery]]:

```php
use yii\data\ActiveDataProvider;
use yii\mongo\Query;

$query = new Query;
$query->from('customer')->where(['status' => 2]);
$provider = new ActiveDataProvider([
	'query' => $query,
	'pagination' => [
		'pageSize' => 10,
	]
]);
$models = $provider->getModels();
```

```php
use yii\data\ActiveDataProvider;
use app\models\Customer;

$provider = new ActiveDataProvider([
	'query' => Customer::find(),
	'pagination' => [
		'pageSize' => 10,
	]
]);
$models = $provider->getModels();
```

This extension supports [MongoGridFS](http://docs.mongodb.org/manual/core/gridfs/) via
classes at namespace "\yii\mongo\file".