MongoDb Extension for Yii 2
===========================

This extension provides the [MongoDB](http://www.mongodb.org/) integration for the Yii2 framework.


Installation
------------

This extension requires [MongoDB PHP Extension](http://us1.php.net/manual/en/book.mongo.php) version 1.3.0 or higher.

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-mongodb "*"
```

or add

```
"yiisoft/yii2-mongodb": "*"
```

to the require section of your composer.json.


Usage & Documentation
---------------------

To use this extension, simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'mongodb' => [
			'class' => '\yii\mongodb\Connection',
			'dsn' => 'mongodb://developer:password@localhost:27017/mydatabase',
		],
	],
];
```

This extension provides ActiveRecord solution similar ot the [[\yii\db\ActiveRecord]].
To declare an ActiveRecord class you need to extend [[\yii\mongodb\ActiveRecord]] and
implement the `collectionName` and 'attributes' methods:

```php
use yii\mongodb\ActiveRecord;

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
		return ['_id', 'name', 'email', 'address', 'status'];
	}
}
```

Note: collection primary key name ('_id') should be always explicitly setup as an attribute.

You can use [[\yii\data\ActiveDataProvider]] with [[\yii\mongodb\Query]] and [[\yii\mongodb\ActiveQuery]]:

```php
use yii\data\ActiveDataProvider;
use yii\mongodb\Query;

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
classes under namespace "\yii\mongodb\file".

This extension supports logging and profiling, however log messages does not contain
actual text of the performed queries, they contains only a “close approximation” of it
composed on the values which can be extracted from PHP Mongo extension classes.
If you need to see actual query text, you should use specific tools for that.