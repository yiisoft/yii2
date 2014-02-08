Active Record
=============

Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
The premise behind Active Record is that an individual [[yii\db\ActiveRecord]] object is associated with a specific row in a database table. The object's attributes are mapped to the columns of the corresponding table. Referencing an Active Record attribute is equivalent to accessing
the corresponding table column for that record.

As an example, say that the `Customer` ActiveRecord class is associated with the
`tbl_customer` table. This would mean that the class's `name` attribute is automatically mapped to the `name` column in `tbl_customer`.
Thanks to Active Record, assuming the variable `$customer` is an object of type `Customer`, to get the value of the `name` column for the table row, you can use the expression `$customer->name`. In this example, Active Record is providing an object-oriented interface for accessing data stored in the database. But Active Record provides much more functionality than this.

With Active Record, instead of writing raw SQL statements to perform database queries, you can call intuitive methods to achieve the same goals. For example, calling [[yii\db\ActiveRecord::save()|save()]] would perform an INSERT or UPDATE query, creating or updating a row in the associated table of the ActiveRecord class:

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();  // a new row is inserted into tbl_customer
```


Declaring ActiveRecord Classes
------------------------------

To declare an ActiveRecord class you need to extend [[\yii\db\ActiveRecord]] and
implement the `tableName` method:

```php
use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
	/**
	 * @return string the name of the table associated with this ActiveRecord class.
	 */
	public static function tableName()
	{
		return 'tbl_customer';
	}
}
```

The `tableName` method only has to return the name of the database table associated with the class.

Class instances are obtained in one of two ways:

* Using the `new` operator to create a new, empty object
* Using a method to fetch an existing record (or records) from the database

Connecting to the Database
----------------------

ActiveRecord relies on a [[Connection|DB connection]] to perform the underlying DB operations.
By default, ActiveRecord assumes that there is an application component named `db` which provides the needed
[[Connection]] instance. Usually this component is configured in application configuration file:

```php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=testdb',
			'username' => 'demo',
			'password' => 'demo',
		],
	],
];
```

Please read the [Database basics](database-basics.md) section to learn more on how to configure and use database connections.

Querying Data from the Database
---------------------------

There are two ActiveRecord methods for querying data from database:

 - [[yii\db\ActiveRecord::find()]]
 - [[yii\db\ActiveRecord::findBySql()]]

Both methods return an [[ActiveQuery]] instance, which extends [[Query]], and thus supports the same set of flexible and powerful DB query methods. The following examples demonstrate some of the possibilities.

```php
// to retrieve all *active* customers and order them by their ID:
$customers = Customer::find()
	->where(['status' => $active])
	->orderBy('id')
	->all();

// to return a single customer whose ID is 1:
$customer = Customer::find(1);

// the above code is equivalent to the following:
$customer = Customer::find()
	->where(['id' => 1])
	->one();

// to retrieve customers using a raw SQL statement:
$sql = 'SELECT * FROM tbl_customer';
$customers = Customer::findBySql($sql)->all();

// to return the number of *active* customers:
$count = Customer::find()
	->where(['status' => $active])
	->count();

// to return customers in terms of arrays rather than `Customer` objects:
$customers = Customer::find()
	->asArray()
	->all();
// each element of $customers is an array of name-value pairs

// to index the result by customer IDs:
$customers = Customer::find()->indexBy('id')->all();
// $customers array is indexed by customer IDs
```


Accessing Column Data
---------------------

ActiveRecord maps each column of the corresponding database table row to an attribute in the ActiveRecord
object. The attribute behaves like any regular object public property. The attribute's name will be the same as the corresponding column name, and is case-sensitive.

To read the value of a column, you can use the following syntax:

```php
// "id" and "email" are the names of columns in the table associated with $customer ActiveRecord object
$id = $customer->id;
$email = $customer->email;
```

To change the value of a column, assign a new value to the associated property and save the object:

```
$customer->email = 'jane@example.com';
$customer->save();
```

Manipulating Data in the Database
-----------------------------

ActiveRecord provides the following methods to insert, update and delete data in the database:

- [[yii\db\ActiveRecord::save()|save()]]
- [[yii\db\ActiveRecord::insert()|insert()]]
- [[yii\db\ActiveRecord::update()|update()]]
- [[yii\db\ActiveRecord::delete()|delete()]]
- [[yii\db\ActiveRecord::updateCounters()|updateCounters()]]
- [[yii\db\ActiveRecord::updateAll()|updateAll()]]
- [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]]
- [[yii\db\ActiveRecord::deleteAll()|deleteAll()]]

Note that [[yii\db\ActiveRecord::updateAll()|updateAll()]], [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] and [[yii\db\ActiveRecord::deleteAll()|deleteAll()]] are static methods that apply to the whole database table. The other methods only apply to the row associated with the ActiveRecord object through which the method is being called.

```php
// to insert a new customer record
$customer = new Customer;
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();  // equivalent to $customer->insert();

// to update an existing customer record
$customer = Customer::find($id);
$customer->email = 'james@example.com';
$customer->save();  // equivalent to $customer->update();

// to delete an existing customer record
$customer = Customer::find($id);
$customer->delete();

// to increment the age of ALL customers by 1
Customer::updateAllCounters(['age' => 1]);
```

> Info: The `save()` method will either perform an `INSERT` or `UPDATE` SQL statement, depending
  on whether the ActiveRecord being saved is new or not by checking `ActiveRecord::isNewRecord`.


Data Input and Validation
-------------------------

ActiveRecord inherits data validation and data input features from [[\yii\base\Model]]. Data validation is called
automatically when `save()` is performed. If data validation fails, the saving operation will be cancelled.

For more details refer to the [Model](model.md) section of this guide.

Querying Relational Data
------------------------

You can use ActiveRecord to also query a table's relational data (i.e., selection of data from Table A can also pull in related data from Table B). Thanks to ActiveRecord, the relational data returned can be accessed like a property of the ActiveRecord object associated with the primary table.

For example, with an appropriate relation declaration, by accessing `$customer->orders` you may obtain
an array of `Order` objects which represent the orders placed by the specified customer.

To declare a relation, define a getter method which returns an [[ActiveRelation]] object. For example,

```php
class Customer extends \yii\db\ActiveRecord
{
	public function getOrders()
	{
		// Customer has_many Order via Order.customer_id -> id
		return $this->hasMany(Order::className(), ['customer_id' => 'id']);
	}
}

class Order extends \yii\db\ActiveRecord
{
	// Order has_one Customer via Customer.id -> customer_id
	public function getCustomer()
	{
		return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
	}
}
```

The methods [[yii\db\ActiveRecord::hasMany()]] and [[yii\db\ActiveRecord::hasOne()]] used in the above
are used to model the many-one relationship and one-one relationship in a relational database.
For example, a customer has many orders, and an order has one customer.
Both methods take two parameters and return an [[ActiveRelation]] object:

 - `$class`: the name of the class of the related model(s). This should be a fully qualified class name.
 - `$link`: the association between columns from the two tables. This should be given as an array.
   The keys of the array are the names of the columns from the table associated with `$class`,
   while the values of the array are the names of the columns from the declaring class.
   It is a good practice to define relationships based on table foreign keys.

After declaring relations, getting relational data is as easy as accessing a component property
that is defined by the corresponding getter method:

```php
// get the orders of a customer
$customer = Customer::find(1);
$orders = $customer->orders;  // $orders is an array of Order objects
```

Behind the scene, the above code executes the following two SQL queries, one for each line of code:

```sql
SELECT * FROM tbl_customer WHERE id=1;
SELECT * FROM tbl_order WHERE customer_id=1;
```

> Tip: If you access the expression `$customer->orders` again, will it perform the second SQL query again?
Nope. The SQL query is only performed the first time when this expression is accessed. Any further
accesses will only return the previously fetched results that are cached internally. If you want to re-query
the relational data, simply unset the existing one first: `unset($customer->orders);`.

Sometimes, you may want to pass parameters to a relational query. For example, instead of returning
all orders of a customer, you may want to return only big orders whose subtotal exceeds a specified amount.
To do so, declare a `bigOrders` relation with the following getter method:

```php
class Customer extends \yii\db\ActiveRecord
{
	public function getBigOrders($threshold = 100)
	{
		return $this->hasMany(Order::className(), ['customer_id' => 'id'])
			->where('subtotal > :threshold', [':threshold' => $threshold])
			->orderBy('id');
	}
}
```

Remember that `hasMany()` returns an [[ActiveRelation]] object which extends from [[ActiveQuery]]
and thus supports the same set of querying methods as [[ActiveQuery]].

With the above declaration, if you access `$customer->bigOrders`, it will only return the orders
whose subtotal is greater than 100. To specify a different threshold value, use the following code:

```php
$orders = $customer->getBigOrders(200)->all();
```


Relations with Pivot Table
--------------------------

Sometimes, two tables are related together via an intermediary table called
[pivot table](http://en.wikipedia.org/wiki/Pivot_table). To declare such relations, we can customize
the [[ActiveRelation]] object by calling its [[ActiveRelation::via()]] or [[ActiveRelation::viaTable()]]
method.

For example, if table `tbl_order` and table `tbl_item` are related via pivot table `tbl_order_item`,
we can declare the `items` relation in the `Order` class like the following:

```php
class Order extends \yii\db\ActiveRecord
{
	public function getItems()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->viaTable('tbl_order_item', ['order_id' => 'id']);
	}
}
```

[[ActiveRelation::via()]] method is similar to [[ActiveRelation::viaTable()]] except that
the first parameter of [[ActiveRelation::via()]] takes a relation name declared in the ActiveRecord class
instead of the pivot table name. For example, the above `items` relation can be equivalently declared as follows:

```php
class Order extends \yii\db\ActiveRecord
{
	public function getOrderItems()
	{
		return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
	}

	public function getItems()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems');
	}
}
```


Lazy and Eager Loading
----------------------

As described earlier, when you access the related objects the first time, ActiveRecord will perform a DB query
to retrieve the corresponding data and populate it into the related objects. No query will be performed
if you access the same related objects again. We call this *lazy loading*. For example,

```php
// SQL executed: SELECT * FROM tbl_customer WHERE id=1
$customer = Customer::find(1);
// SQL executed: SELECT * FROM tbl_order WHERE customer_id=1
$orders = $customer->orders;
// no SQL executed
$orders2 = $customer->orders;
```

Lazy loading is very convenient to use. However, it may suffer from a performance issue in the following scenario:

```php
// SQL executed: SELECT * FROM tbl_customer LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
	// SQL executed: SELECT * FROM tbl_order WHERE customer_id=...
	$orders = $customer->orders;
	// ...handle $orders...
}
```

How many SQL queries will be performed in the above code, assuming there are more than 100 customers in
the database? 101! The first SQL query brings back 100 customers. Then for each customer, a SQL query
is performed to bring back the orders of that customer.

To solve the above performance problem, you can use the so-called *eager loading* approach by calling [[ActiveQuery::with()]]:

```php
// SQL executed: SELECT * FROM tbl_customer LIMIT 100;
//               SELECT * FROM tbl_orders WHERE customer_id IN (1,2,...)
$customers = Customer::find()->limit(100)
	->with('orders')->all();

foreach ($customers as $customer) {
	// no SQL executed
	$orders = $customer->orders;
	// ...handle $orders...
}
```

As you can see, only two SQL queries are needed for the same task.


Sometimes, you may want to customize the relational queries on the fly. This can be
done for both lazy loading and eager loading. For example,

```php
$customer = Customer::find(1);
// lazy loading: SELECT * FROM tbl_order WHERE customer_id=1 AND subtotal>100
$orders = $customer->getOrders()->where('subtotal>100')->all();

// eager loading: SELECT * FROM tbl_customer LIMIT 100
//                SELECT * FROM tbl_order WHERE customer_id IN (1,2,...) AND subtotal>100
$customers = Customer::find()->limit(100)->with([
	'orders' => function($query) {
		$query->andWhere('subtotal>100');
	},
])->all();
```


Joining with Relations
----------------------

When working with relational databases, a common task is to join multiple tables and apply various
query conditions and parameters to the JOIN SQL statement. Instead of calling [[ActiveQuery::join()]]
explicitly to build up the JOIN query, you may reuse the existing relation definitions and call
[[ActiveQuery::joinWith()]] to achieve this goal. For example,

```php
// find all orders and sort the orders by the customer id and the order id. also eager loading "customer"
$orders = Order::find()->joinWith('customer')->orderBy('tbl_customer.id, tbl_order.id')->all();
// find all orders that contain books, and eager loading "books"
$orders = Order::find()->innerJoinWith('books')->all();
```

In the above, the method [[ActiveQuery::innerJoinWith()|innerJoinWith()]] is a shortcut to [[ActiveQuery::joinWith()|joinWith()]]
with the join type set as `INNER JOIN`.

You may join with one or multiple relations; you may apply query conditions to the relations on-the-fly;
and you may also join with sub-relations. For example,

```php
// join with multiple relations
// find out the orders that contain books and are placed by customers who registered within the past 24 hours
$orders = Order::find()->innerJoinWith([
	'books',
	'customer' => function ($query) {
		$query->where('tbl_customer.created_at > ' . (time() - 24 * 3600));
	}
])->all();
// join with sub-relations: join with books and books' authors
$orders = Order::find()->joinWith('books.author')->all();
```

Behind the scene, Yii will first execute a JOIN SQL statement to bring back the primary models
satisfying the conditions applied to the JOIN SQL. It will then execute a query for each relation
and populate the corresponding related records.

The difference between [[ActiveQuery::joinWith()|joinWith()]] and [[ActiveQuery::with()|with()]] is that
the former joins the tables for the primary model class and the related model classes to retrieve
the primary models, while the latter just queries against the table for the primary model class to
retrieve the primary models.

Because of this difference, you may apply query conditions that are only available to a JOIN SQL statement.
For example, you may filter the primary models by the conditions on the related models, like the example
above. You may also sort the primary models using columns from the related tables.

When using [[ActiveQuery::joinWith()|joinWith()]], you are responsible to disambiguate column names.
In the above examples, we use `tbl_item.id` and `tbl_order.id` to disambiguate the `id` column references
because both of the order table and the item table contain a column named `id`.

By default, when you join with a relation, the relation will also be eagerly loaded. You may change this behavior
by passing the `$eagerLoading` parameter which specifies whether to eager load the specified relations.

And also by default, [[ActiveQuery::joinWith()|joinWith()]] uses `LEFT JOIN` to join the related tables.
You may pass it with the `$joinType` parameter to customize the join type. As a shortcut to the `INNER JOIN` type,
you may use [[ActiveQuery::innerJoinWith()|innerJoinWith()]].

Below are some more examples,

```php
// find all orders that contain books, but do not eager loading "books".
$orders = Order::find()->innerJoinWith('books', false)->all();
// which is equivalent to the above
$orders = Order::find()->joinWith('books', false, 'INNER JOIN')->all();
```

Sometimes when joining two tables, you may need to specify some extra condition in the ON part of the JOIN query.
This can be done by calling the [[\yii\db\ActiveRelation::onCondition()]] method like the following:

```php
class User extends ActiveRecord
{
	public function getBooks()
	{
		return $this->hasMany(Item::className(), ['owner_id' => 'id'])->onCondition(['category_id' => 1]);
	}
}
```

In the above, the `hasMany()` method returns an `ActiveRelation` instance, upon which `onCondition()` is called
to specify that only items whose `category_id` is 1 should be returned.

When you perform query using [[ActiveQuery::joinWith()|joinWith()]], the on-condition will be put in the ON part
of the corresponding JOIN query. For example,

```php
// SELECT tbl_user.* FROM tbl_user LEFT JOIN tbl_item ON tbl_item.owner_id=tbl_user.id AND category_id=1
// SELECT * FROM tbl_item WHERE owner_id IN (...) AND category_id=1
$users = User::find()->joinWith('books')->all();
```

Note that if you use eager loading via [[ActiveQuery::with()]] or lazy loading, the on-condition will be put
in the WHERE part of the corresponding SQL statement, because there is no JOIN query involved. For example,

```php
// SELECT * FROM tbl_user WHERE id=10
$user = User::find(10);
// SELECT * FROM tbl_item WHERE owner_id=10 AND category_id=1
$books = $user->books;
```


Working with Relationships
--------------------------

ActiveRecord provides the following two methods for establishing and breaking a
relationship between two ActiveRecord objects:

- [[yii\db\ActiveRecord::link()|link()]]
- [[yii\db\ActiveRecord::unlink()|unlink()]]

For example, given a customer and a new order, we can use the following code to make the
order owned by the customer:

```php
$customer = Customer::find(1);
$order = new Order;
$order->subtotal = 100;
$customer->link('orders', $order);
```

The [[link()]] call above will set the `customer_id` of the order to be the primary key
value of `$customer` and then call [[save()]] to save the order into database.


Life Cycles of an ActiveRecord Object
-------------------------------------

An ActiveRecord object undergoes different life cycles when it is used in different cases.
Subclasses or ActiveRecord behaviors may "inject" custom code in these life cycles through
method overriding and event handling mechanisms.

When instantiating a new ActiveRecord instance, we will have the following life cycles:

1. constructor
2. [[init()]]: will trigger an [[EVENT_INIT]] event

When getting an ActiveRecord instance through the [[find()]] method, we will have the following life cycles:

1. constructor
2. [[init()]]: will trigger an [[EVENT_INIT]] event
3. [[afterFind()]]: will trigger an [[EVENT_AFTER_FIND]] event

When calling [[save()]] to insert or update an ActiveRecord, we will have the following life cycles:

1. [[beforeValidate()]]: will trigger an [[EVENT_BEFORE_VALIDATE]] event
2. [[afterValidate()]]: will trigger an [[EVENT_AFTER_VALIDATE]] event
3. [[beforeSave()]]: will trigger an [[EVENT_BEFORE_INSERT]] or [[EVENT_BEFORE_UPDATE]] event
4. perform the actual data insertion or updating
5. [[afterSave()]]: will trigger an [[EVENT_AFTER_INSERT]] or [[EVENT_AFTER_UPDATE]] event

Finally when calling [[delete()]] to delete an ActiveRecord, we will have the following life cycles:

1. [[beforeDelete()]]: will trigger an [[EVENT_BEFORE_DELETE]] event
2. perform the actual data deletion
3. [[afterDelete()]]: will trigger an [[EVENT_AFTER_DELETE]] event


Custom scopes
-------------

When [[find()]] or [[findBySql()]] Active Record method is being called without parameters it returns an [[ActiveQuery]]
instance. This object holds all the parameters and conditions for a future query and also allows you to customize these
using a set of methods that are called scopes. By default there is a good set of such methods some of which we've
already used above: `where`, `orderBy`, `limit` etc.

In many cases it is convenient to wrap extra conditions into custom scope methods. In order to do so you need two things.
First is creating a custom query class for your model. For example, a `Comment` may have a `CommentQuery`:

```php
namespace app\models;

use yii\db\ActiveQuery;

class CommentQuery extends ActiveQuery
{
	public function active($state = true)
	{
		$this->andWhere(['active' => $state]);
		return $this;
	}
}
```

Important points are:

1. Class should extend from `yii\db\ActiveQuery` (or another `ActiveQuery` such as `yii\mongodb\ActiveQuery`).
2. A method should be `public` and should return `$this` in order to allow method chaining. It may accept parameters.
3. Check `ActiveQuery` methods that are very useful for modifying query conditions.

The second step is to use `CommentQuery` instead of regular `ActiveQuery` for `Comment` model:

```
namespace app\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
	public static function createQuery()
	{
		return new CommentQuery(['modelClass' => get_called_class()]);
	}
}
```

That's it. Now you can use your custom scope methods:

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

You can also use scopes when defining relations. For example,

```php
class Post extends \yii\db\ActiveRecord
{
	public function getComments()
	{
		return $this->hasMany(Comment::className(), ['post_id' => 'id'])->active();

	}
}
```

Or use the scopes on-the-fly when performing relational query:

```php
$posts = Post::find()->with([
	'comments' => function($q) {
		$q->active();
	}
])->all();
```

### Making it IDE-friendly

In order to make most modern IDE autocomplete happy you need to override return types for some methods of both model
and query like the following:

```php
/**
 * @method \app\models\CommentQuery|static|null find($q = null) static
 * @method \app\models\CommentQuery findBySql($sql, $params = []) static
 */
class Comment extends ActiveRecord
{
	// ...
}
```

```php
/**
 * @method \app\models\Comment|array|null one($db = null)
 * @method \app\models\Comment[]|array all($db = null)
 */
class CommentQuery extends ActiveQuery
{
	// ...
}
```

Transactional operations
------------------------


When a few DB operations are related and are executed

TODO: FIXME: WIP, TBD, https://github.com/yiisoft/yii2/issues/226

,
[[afterSave()]], [[beforeDelete()]] and/or [[afterDelete()]] life cycle methods. Developer may come
to the solution of overriding ActiveRecord [[save()]] method with database transaction wrapping or
even using transaction in controller action, which is strictly speaking doesn't seem to be a good
practice (recall "skinny-controller / fat-model" fundamental rule).

Here these ways are (**DO NOT** use them unless you're sure what you are actually doing). Models:

```php
class Feature extends \yii\db\ActiveRecord
{
	// ...

	public function getProduct()
	{
		return $this->hasOne(Product::className(), ['product_id' => 'id']);
	}
}

class Product extends \yii\db\ActiveRecord
{
	// ...

	public function getFeatures()
	{
		return $this->hasMany(Feature::className(), ['id' => 'product_id']);
	}
}
```

Overriding [[save()]] method:

```php

class ProductController extends \yii\web\Controller
{
	public function actionCreate()
	{
		// FIXME: TODO: WIP, TBD
	}
}
```

Using transactions within controller layer:

```php
class ProductController extends \yii\web\Controller
{
	public function actionCreate()
	{
		// FIXME: TODO: WIP, TBD
	}
}
```

Instead of using these fragile methods you should consider using atomic scenarios and operations feature.

```php
class Feature extends \yii\db\ActiveRecord
{
	// ...

	public function getProduct()
	{
		return $this->hasOne(Product::className(), ['product_id' => 'id']);
	}

	public function scenarios()
	{
		return [
			'userCreates' => [
				'attributes' => ['name', 'value'],
				'atomic' => [self::OP_INSERT],
			],
		];
	}
}

class Product extends \yii\db\ActiveRecord
{
	// ...

	public function getFeatures()
	{
		return $this->hasMany(Feature::className(), ['id' => 'product_id']);
	}

	public function scenarios()
	{
		return [
			'userCreates' => [
				'attributes' => ['title', 'price'],
				'atomic' => [self::OP_INSERT],
			],
		];
	}

	public function afterValidate()
	{
		parent::afterValidate();
		// FIXME: TODO: WIP, TBD
	}

	public function afterSave($insert)
	{
		parent::afterSave($insert);
		if ($this->getScenario() === 'userCreates') {
			// FIXME: TODO: WIP, TBD
		}
	}
}
```

Controller is very thin and neat:

```php
class ProductController extends \yii\web\Controller
{
	public function actionCreate()
	{
		// FIXME: TODO: WIP, TBD
	}
}
```

Optimistic Locks
----------------

TODO

Dirty Attributes
----------------

TODO

See also
--------

- [Model](model.md)
- [[\yii\db\ActiveRecord]]
