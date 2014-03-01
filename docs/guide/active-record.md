Active Record
=============

Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
The premise behind Active Record is that an individual [[yii\db\ActiveRecord|ActiveRecord]] object is associated with a specific row in a database table. The object's attributes are mapped to the columns of the corresponding table. Referencing an Active Record attribute is equivalent to accessing
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

To declare an ActiveRecord class you need to extend [[yii\db\ActiveRecord]] and
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

ActiveRecord relies on a [[yii\db\Connection|DB connection]] to perform the underlying DB operations.
By default, ActiveRecord assumes that there is an application component named `db` which provides the needed
[[yii\db\Connection]] instance. Usually this component is configured in application configuration file:

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

Both methods return an [[yii\db\ActiveQuery]] instance, which extends [[yii\db\Query]], and thus supports the same set of flexible and powerful DB query methods. The following examples demonstrate some of the possibilities.

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

Batch query is also supported when working with Active Record. For example,

```php
// fetch 10 customers at a time
foreach (Customer::find()->batch(10) as $customers) {
	// $customers is an array of 10 or fewer Customer objects
}
// fetch 10 customers at a time and iterate them one by one
foreach (Customer::find()->each(10) as $customer) {
	// $customer is a Customer object
}
// batch query with eager loading
foreach (Customer::find()->with('orders')->each() as $customer) {
}
```

As explained in [Query Builder](query-builder.md), batch query is very useful when you are fetching
a large amount of data from database. It will keep your memory usage under a limit.


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
$customer = new Customer();
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

ActiveRecord inherits data validation and data input features from [[yii\base\Model]]. Data validation is called
automatically when `save()` is performed. If data validation fails, the saving operation will be cancelled.

For more details refer to the [Model](model.md) section of this guide.

Querying Relational Data
------------------------

You can use ActiveRecord to also query a table's relational data (i.e., selection of data from Table A can also pull
in related data from Table B). Thanks to ActiveRecord, the relational data returned can be accessed like a property
of the ActiveRecord object associated with the primary table.

For example, with an appropriate relation declaration, by accessing `$customer->orders` you may obtain
an array of `Order` objects which represent the orders placed by the specified customer.

To declare a relation, define a getter method which returns an [[yii\db\ActiveQuery]] object that has relation
information about the relation context and thus will only query for related records. For example,

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
Both methods take two parameters and return an [[yii\db\ActiveQuery]] object:

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

> Tip: If you access the expression `$customer->orders` again, it will not perform the second SQL query again.
The SQL query is only performed the first time when this expression is accessed. Any further
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

Remember that `hasMany()` returns an [[yii\db\ActiveQuery]] object which allows you to customize the query by
calling the methods of [[yii\db\ActiveQuery]].

With the above declaration, if you access `$customer->bigOrders`, it will only return the orders
whose subtotal is greater than 100. To specify a different threshold value, use the following code:

```php
$orders = $customer->getBigOrders(200)->all();
```

> Note: A relation method returns an instance of [[yii\db\ActiveQuery]]. If you access the relation like
an attribute (i.e. a class property), the return value will be the query result of the relation, which could be an instance of [[yii\db\ActiveRecord]],
an array of that, or null, depending the multiplicity of the relation. For example, `$customer->getOrders()` returns
an `ActiveQuery` instance, while `$customer->orders` returns an array of `Order` objects (or an empty array if
the query results in nothing).


Relations with Pivot Table
--------------------------

Sometimes, two tables are related together via an intermediary table called [pivot table][]. To declare such relations,
we can customize the [[yii\db\ActiveQuery]] object by calling its [[yii\db\ActiveQuery::via()|via()]] or
[[yii\db\ActiveQuery::viaTable()|viaTable()]] method.

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

The [[yii\db\ActiveQuery::via()|via()]] method is similar to [[yii\db\ActiveQuery::viaTable()|viaTable()]] except that
the first parameter of [[yii\db\ActiveQuery::via()|via()]] takes a relation name declared in the ActiveRecord class
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

[pivot table]: http://en.wikipedia.org/wiki/Pivot_table "Pivot table on Wikipedia"


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

To solve the above performance problem, you can use the so-called *eager loading* approach by calling [[yii\db\ActiveQuery::with()]]:

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

As you can see, only two SQL queries are needed for the same task!

> Info: In general, if you are eager loading `N` relations among which `M` relations are defined with `via()` or `viaTable()`,
> a total number of `1+M+N` SQL queries will be performed: one query to bring back the rows for the primary table, one for
> each of the `M` pivot tables corresponding to the `via()` or `viaTable()` calls, and one for each of the `N` related tables.

> Note: When you are customizing `select()` with eager loading, make sure you include the columns that link
> the related models. Otherwise, the related models will not be loaded. For example,

```php
$orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
// $orders[0]->customer is always null. To fix the problem, you should do the following:
$orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
```

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


Inverse Relations
-----------------

Relations can often be defined in pairs. For example, `Customer` may have a relation named `orders` while `Order` may have a relation
named `customer`:

```php
class Customer extends ActiveRecord
{
	....
	public function getOrders()
	{
		return $this->hasMany(Order::className, ['customer_id' => 'id']);
	}
}

class Order extends ActiveRecord
{
	....
	public function getCustomer()
	{
		return $this->hasOne(Customer::className, ['id' => 'customer_id']);
	}
}
```

If we perform the following query, we would find that the `customer` of an order is not the same customer object
that finds those orders, and accessing `customer->orders` will trigger one SQL execution while accessing
the `customer` of an order will trigger another SQL execution:

```php
// SELECT * FROM tbl_customer WHERE id=1
$customer = Customer::find(1);
// echoes "not equal"
// SELECT * FROM tbl_order WHERE customer_id=1
// SELECT * FROM tbl_customer WHERE id=1
if ($customer->orders[0]->customer === $customer) {
	echo 'equal';
} else {
	echo 'not equal';
}
```

To avoid the redundant execution of the last SQL statement, we could declare the inverse relations for the `customer`
and the `orders` relations by calling the [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] method, like the following:

```php
class Customer extends ActiveRecord
{
	....
	public function getOrders()
	{
		return $this->hasMany(Order::className, ['customer_id' => 'id'])->inverseOf('customer');
	}
}
```

Now if we execute the same query as shown above, we would get:

```php
// SELECT * FROM tbl_customer WHERE id=1
$customer = Customer::find(1);
// echoes "equal"
// SELECT * FROM tbl_order WHERE customer_id=1
if ($customer->orders[0]->customer === $customer) {
	echo 'equal';
} else {
	echo 'not equal';
}
```

In the above, we have shown how to use inverse relations in lazy loading. Inverse relations also apply in
eager loading:

```php
// SELECT * FROM tbl_customer
// SELECT * FROM tbl_order WHERE customer_id IN (1, 2, ...)
$customers = Customer::find()->with('orders')->all();
// echoes "equal"
if ($customers[0]->orders[0]->customer === $customers[0]) {
	echo 'equal';
} else {
	echo 'not equal';
}
```

> Note: Inverse relation cannot be defined with a relation that involves pivoting tables.
> That is, if your relation is defined with [[yii\db\ActiveQuery::via()|via()]] or [[yii\db\ActiveQuery::viaTable()|viaTable()]],
> you cannot call [[yii\db\ActiveQuery::inverseOf()]] further.


Joining with Relations
----------------------

When working with relational databases, a common task is to join multiple tables and apply various
query conditions and parameters to the JOIN SQL statement. Instead of calling [[yii\db\ActiveQuery::join()]]
explicitly to build up the JOIN query, you may reuse the existing relation definitions and call
[[yii\db\ActiveQuery::joinWith()]] to achieve this goal. For example,

```php
// find all orders and sort the orders by the customer id and the order id. also eager loading "customer"
$orders = Order::find()->joinWith('customer')->orderBy('tbl_customer.id, tbl_order.id')->all();
// find all orders that contain books, and eager loading "books"
$orders = Order::find()->innerJoinWith('books')->all();
```

In the above, the method [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] is a shortcut to [[yii\db\ActiveQuery::joinWith()|joinWith()]]
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

The difference between [[yii\db\ActiveQuery::joinWith()|joinWith()]] and [[yii\db\ActiveQuery::with()|with()]] is that
the former joins the tables for the primary model class and the related model classes to retrieve
the primary models, while the latter just queries against the table for the primary model class to
retrieve the primary models.

Because of this difference, you may apply query conditions that are only available to a JOIN SQL statement.
For example, you may filter the primary models by the conditions on the related models, like the example
above. You may also sort the primary models using columns from the related tables.

When using [[yii\db\ActiveQuery::joinWith()|joinWith()]], you are responsible to disambiguate column names.
In the above examples, we use `tbl_item.id` and `tbl_order.id` to disambiguate the `id` column references
because both of the order table and the item table contain a column named `id`.

By default, when you join with a relation, the relation will also be eagerly loaded. You may change this behavior
by passing the `$eagerLoading` parameter which specifies whether to eager load the specified relations.

And also by default, [[yii\db\ActiveQuery::joinWith()|joinWith()]] uses `LEFT JOIN` to join the related tables.
You may pass it with the `$joinType` parameter to customize the join type. As a shortcut to the `INNER JOIN` type,
you may use [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]].

Below are some more examples,

```php
// find all orders that contain books, but do not eager loading "books".
$orders = Order::find()->innerJoinWith('books', false)->all();
// which is equivalent to the above
$orders = Order::find()->joinWith('books', false, 'INNER JOIN')->all();
```

Sometimes when joining two tables, you may need to specify some extra condition in the ON part of the JOIN query.
This can be done by calling the [[yii\db\ActiveQuery::onCondition()]] method like the following:

```php
class User extends ActiveRecord
{
	public function getBooks()
	{
		return $this->hasMany(Item::className(), ['owner_id' => 'id'])->onCondition(['category_id' => 1]);
	}
}
```

In the above, the [[yii\db\ActiveRecord::hasMany()|hasMany()]] method returns an [[yii\db\ActiveQuery]] instance,
upon which [[yii\db\ActiveQuery::onCondition()|onCondition()]] is called
to specify that only items whose `category_id` is 1 should be returned.

When you perform query using [[yii\db\ActiveQuery::joinWith()|joinWith()]], the on-condition will be put in the ON part
of the corresponding JOIN query. For example,

```php
// SELECT tbl_user.* FROM tbl_user LEFT JOIN tbl_item ON tbl_item.owner_id=tbl_user.id AND category_id=1
// SELECT * FROM tbl_item WHERE owner_id IN (...) AND category_id=1
$users = User::find()->joinWith('books')->all();
```

Note that if you use eager loading via [[yii\db\ActiveQuery::with()]] or lazy loading, the on-condition will be put
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
$order = new Order();
$order->subtotal = 100;
$customer->link('orders', $order);
```

The [[yii\db\ActiveRecord::link()|link()]] call above will set the `customer_id` of the order to be the primary key
value of `$customer` and then call [[yii\db\ActiveRecord::save()|save()]] to save the order into database.


Life Cycles of an ActiveRecord Object
-------------------------------------

An ActiveRecord object undergoes different life cycles when it is used in different cases.
Subclasses or ActiveRecord behaviors may "inject" custom code in these life cycles through
method overriding and event handling mechanisms.

When instantiating a new ActiveRecord instance, we will have the following life cycles:

1. constructor
2. [[yii\db\ActiveRecord::init()|init()]]: will trigger an [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] event

When getting an ActiveRecord instance through the [[yii\db\ActiveRecord::find()|find()]] method, we will have the following life cycles:

1. constructor
2. [[yii\db\ActiveRecord::init()|init()]]: will trigger an [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] event
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] event

When calling [[yii\db\ActiveRecord::save()|save()]] to insert or update an ActiveRecord, we will have the following life cycles:

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: will trigger an [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] event
2. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]] event
3. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: will trigger an [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] or [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]] event
4. perform the actual data insertion or updating
5. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] or [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]] event

Finally when calling [[yii\db\ActiveRecord::delete()|delete()]] to delete an ActiveRecord, we will have the following life cycles:

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: will trigger an [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]] event
2. perform the actual data deletion
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]] event


Scopes
------

When you call [[yii\db\ActiveRecord::find()|find()]] or [[yii\db\ActiveRecord::findBySql()|findBySql()]], it returns an
[[yii\db\ActiveQuery|ActiveQuery]] instance.
You may call additional query methods, such as [[yii\db\ActiveQuery::where()|where()]], [[yii\db\ActiveQuery::orderBy()|orderBy()]],
to further specify the query conditions.

It is possible that you may want to call the same set of query methods in different places. If this is the case,
you should consider defining the so-called *scopes*. A scope is essentially a method defined in a custom query class that
calls a set of query methods to modify the query object. You can then use a scope like calling a normal query method.

Two steps are required to define a scope. First create a custom query class for your model and define the needed scope
methods in this class. For example, create a `CommentQuery` class for the `Comment` model and define the `active()`
scope method like the following:

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
3. Check [[yii\db\ActiveQuery]] methods that are very useful for modifying query conditions.

Second, override [[yii\db\ActiveRecord::createQuery()]] to use the custom query class instead of the regular [[yii\db\ActiveQuery|ActiveQuery]].
For the example above, you need to write the following code:

```php
namespace app\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
	public static function createQuery($config = [])
	{
		$config['modelClass'] = get_called_class();
		return new CommentQuery($config);
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
	public function getActiveComments()
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

### Default Scope

If you used Yii 1.1 before, you may know a concept called *default scope*. A default scope is a scope that
applies to ALL queries. You can define a default scope easily by overriding [[yii\db\ActiveRecord::createQuery()]]. For example,

```php
public static function createQuery($config = [])
{
	$config['modelClass'] = get_called_class();
	return (new ActiveQuery($config))->where(['deleted' => false]);
}
```

Note that all your queries should then not use [[yii\db\ActiveQuery::where()|where()]] but
[[yii\db\ActiveQuery::andWhere()|andWhere()]] and [[yii\db\ActiveQuery::orWhere()|orWhere()]]
to not override the default condition.


Transactional operations
------------------------

When a few DB operations are related and are executed

TODO: FIXME: WIP, TBD, https://github.com/yiisoft/yii2/issues/226

,
[[yii\db\ActiveRecord::afterSave()|afterSave()]], [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]] and/or [[yii\db\ActiveRecord::afterDelete()|afterDelete()]] life cycle methods. Developer may come
to the solution of overriding ActiveRecord [[yii\db\ActiveRecord::save()|save()]] method with database transaction wrapping or
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

Overriding [[yii\db\ActiveRecord::save()|save()]] method:

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
- [[yii\db\ActiveRecord]]
