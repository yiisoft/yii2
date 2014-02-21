Redis Cache, Session and ActiveRecord for Yii 2
===============================================

This extension provides the [redis](http://redis.io/) key-value store support for the Yii2 framework.
It includes a `Cache` and `Session` storage handler and implents the `ActiveRecord` pattern that allows
you to store active records in redis.

To use this extension, you have to configure the Connection class in your application configuration:

```php
return [
	//....
	'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
	]
];
```

Requirements
------------

At least redis version 2.6.12 is required for all components to work properly.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-redis "*"
```

or add

```json
"yiisoft/yii2-redis": "*"
```

to the require section of your composer.json.


Using the Cache component
-------------------------

To use the `Cache` component, in addition to configuring the connection as described above,
you also have to configure the `cache` component to be `yii\redis\Cache`:

```php
return [
	//....
	'components' => [
		// ...
		'cache' => [
			'class' => 'yii\redis\Cache',
		],
	]
];
```

If you only use the redis cache, you can also configure the parameters of the connection within the
cache component (no connection application component needs to be configured in this case):

```php
return [
	//....
	'components' => [
		// ...
		'cache' => [
			'class' => 'yii\redis\Cache',
			'redis' => [
				'hostname' => 'localhost',
				'port' => 6379,
				'database' => 0,
			],
		],
	]
];
```

Using the Session component
---------------------------

To use the `Session` component, in addtition to configuring the connection as described above,
you also have to configure the `session` component to be `yii\redis\Session`:

```php
return [
	//....
	'components' => [
		// ...
		'session' => [
			'class' => 'yii\redis\Session',
		],
	]
];
```

If you only use the redis session, you can also configure the parameters of the connection within the
cache component (no connection application component needs to be configured in this case):

```php
return [
	//....
	'components' => [
		// ...
		'session' => [
			'class' => 'yii\redis\Session',
			'redis' => [
				'hostname' => 'localhost',
				'port' => 6379,
				'database' => 0,
			],
		],
	]
];
```


Using the redis ActiveRecord
----------------------------

For general information on how to use yii's ActiveRecord please refer to the [guide](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md).

For defining a redis ActiveRecord class your record class needs to extend from `yii\redis\ActiveRecord` and
implement at least the `attributes()` method to define the attributes of the record.
A primary key can be defined via [[primaryKey()]] which defaults to `id` if not specified.
The primaryKey needs to be part of the attributes so make sure you have an `id` attribute defined if you do
not specify your own primary key.

The following is an example model called `Customer`:

```php
class Customer extends \yii\redis\ActiveRecord
{
	/**
	 * @return array the list of attributes for this record
	 */
	public function attributes()
	{
		return ['id', 'name', 'address', 'registration_date'];
	}

	/**
	 * @return ActiveQuery defines a relation to the Order record (can be in other database, e.g. elasticsearch or sql)
	 */
	public function getOrders()
	{
		return $this->hasMany(Order::className(), ['customer_id' => 'id']);
	}

	/**
	 * Defines a scope that modifies the `$query` to return only active(status = 1) customers
	 */
	public static function active($query)
	{
		$query->andWhere(array('status' => 1));
	}
}
```

The general usage of redis ActiveRecord is very similar to the database ActiveRecord as described in the
[guide](https://github.com/yiisoft/yii2/blob/master/docs/guide/active-record.md).
It supports the same interface and features except the following limitations:

- As redis does not support SQL the query API is limited to the following methods:
  `where()`, `limit()`, `offset()`, `orderBy()` and `indexBy()`.
  (orderBy() is not yet implemented: [#1305](https://github.com/yiisoft/yii2/issues/1305))
- `via`-relations can not be defined via a table as there are not tables in redis. You can only define relations via other records.

It is also possible to define relations from redis ActiveRecords to normal ActiveRecord classes and vice versa.

Usage example:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id will automatically be incremented if not set explicitly

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```
