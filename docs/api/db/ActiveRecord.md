ActiveRecord implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
The idea is that an ActiveRecord object is associated with a row in a database table
so object properties are mapped to colums of the corresponding database row.
For example, a `Customer` object is associated with a row in the `tbl_customer`
table. Instead of writing raw SQL statements to access the data in the table,
you can call intuitive methods available in the corresponding ActiveRecord class
to achieve the same goals. For example, calling [[save()]] would insert or update a row
in the underlying table:

~~~
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
~~~


### Declaring ActiveRecord Classes

To declare an ActiveRecord class you need to extend [[\yii\db\ActiveRecord]] and
implement `tableName` method like the following:

~~~
class Customer extends \yii\db\ActiveRecord
{
	/**
	 * @return string the name of the table associated with this ActiveRecord class.
	 */
	public static function tableName()
	{
		return 'tbl_customer';
	}
}
~~~

### Connecting to Database

ActiveRecord relies on a [[Connection|DB connection]]. By default, it assumes that
there is an application component named `db` that gives the needed [[Connection]]
instance which serves as the DB connection. Usually this component is configured
via application configuration like the following:

~~~
return array(
	'components' => array(
		'db' => array(
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=testdb',
			'username' => 'demo',
			'password' => 'demo',
			// turn on schema caching to improve performance
			// 'schemaCacheDuration' => 3600,
		),
	),
);
~~~


### Getting Data from Database

There are two ActiveRecord methods for getting data:

- [[find()]]
- [[findBySql()]]

They both return an [[ActiveQuery]] instance. Coupled with the various customization and query methods
provided by [[ActiveQuery]], ActiveRecord supports very flexible and powerful data retrieval approaches.

The followings are some examples,

~~~
// to retrieve all *active* customers and order them by their ID:
$customers = Customer::find()
	->where(array('status' => $active))
	->orderBy('id')
	->all();

// to return a single customer whose ID is 1:
$customer = Customer::find()
	->where(array('id' => 1))
	->one();

// or use the following shortcut approach:
$customer = Customer::find(1);

// to retrieve customers using a raw SQL statement:
$sql = 'SELECT * FROM tbl_customer';
$customers = Customer::findBySql($sql)->all();

// to return the number of *active* customers:
$count = Customer::find()
	->where(array('status' => $active))
	->count();

// to return customers in terms of arrays rather than `Customer` objects:
$customers = Customer::find()->asArray()->all();
// each $customers element is an array of name-value pairs

// to index the result by customer IDs:
$customers = Customer::find()->indexBy('id')->all();
// $customers array is indexed by customer IDs
~~~


### Accessing Column Data

ActiveRecord maps each column of the corresponding database table row to an *attribute* in the ActiveRecord
object. An attribute is like a regular object property whose name is the same as the corresponding column
name and is case sensitive.

To read the value of a column, we can use the following expression:

~~~
// "id" is the name of a column in the table associated with $customer ActiveRecord object
$id = $customer->id;
// or alternatively,
$id = $customer->getAttribute('id');
~~~

We can get all column values through the [[attributes]] property:

~~~
$values = $customer->attributes;
~~~


### Persisting Data to Database

ActiveRecord provides the following methods to insert, update and delete data:

- [[save()]]
- [[insert()]]
- [[update()]]
- [[delete()]]
- [[updateCounters()]]
- [[updateAll()]]
- [[updateAllCounters()]]
- [[deleteAll()]]

Note that [[updateAll()]], [[updateAllCounters()]] and [[deleteAll()]] apply to the whole database
table, while the rest of the methods only apply to the row associated with the ActiveRecord object.

The followings are some examples:

~~~
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

// to increment the age of all customers by 1
Customer::updateAllCounters(array('age' => 1));
~~~


### Getting Relational Data

Using ActiveRecord you can expose relationships as properties. For example,
with an appropriate declaration, `$customer->orders` can return an array of `Order` objects
which represent the orders placed by the specified customer.

To declare a relationship, define a getter method which returns an [[ActiveRelation]] object. For example,

~~~
class Customer extends \yii\db\ActiveRecord
{
	public function getOrders()
	{
		return $this->hasMany('Order', array('customer_id' => 'id'));
	}
}

class Order extends \yii\db\ActiveRecord
{
	public function getCustomer()
	{
		return $this->hasOne('Customer', array('id' => 'customer_id'));
	}
}
~~~

Within the getter methods above, we call [[hasMany()]] or [[hasOne()]] methods to
create a new [[ActiveRelation]] object. The [[hasMany()]] method declares
a one-many relationship. For example, a customer has many orders. And the [[hasOne()]]
method declares a many-one or one-one relationship. For example, an order has one customer.
Both methods take two parameters:

- `$class`: the name of the class related models should use. If specified without
  a namespace, the namespace will be taken from the declaring class.
- `$link`: the association between columns from two tables. This should be given as an array.
  The keys of the array are the names of the columns from the table associated with `$class`,
  while the values of the array are the names of the columns from the declaring class.
  It is a good practice to define relationships based on table foreign keys.

After declaring relationships getting relational data is as easy as accessing
a component property that is defined by the getter method:

~~~
// the orders of a customer
$customer = Customer::find($id);
$orders = $customer->orders;  // $orders is an array of Order objects

// the customer of the first order
$customer2 = $orders[0]->customer;  // $customer == $customer2
~~~

Because [[ActiveRelation]] extends from [[ActiveQuery]], it has the same query building methods,
which allows us to customize the query for retrieving the related objects.
For example, we may declare a `bigOrders` relationship which returns orders whose
subtotal exceeds certain amount:

~~~
class Customer extends \yii\db\ActiveRecord
{
	public function getBigOrders($threshold = 100)
	{
		return $this->hasMany('Order', array('customer_id' => 'id'))
			->where('subtotal > :threshold', array(':threshold' => $threshold))
			->orderBy('id');
	}
}
~~~


Sometimes, two tables are related together via an intermediary table called
[pivot table](http://en.wikipedia.org/wiki/Pivot_table). To declare such relationships, we can customize
the [[ActiveRelation]] object by calling its [[ActiveRelation::via()]] or [[ActiveRelation::viaTable()]]
method.

For example, if table `tbl_order` and table `tbl_item` are related via pivot table `tbl_order_item`,
we can declare the `items` relation in the `Order` class like the following:

~~~
class Order extends \yii\db\ActiveRecord
{
	public function getItems()
	{
		return $this->hasMany('Item', array('id' => 'item_id'))
			->viaTable('tbl_order_item', array('order_id' => 'id'));
	}
}
~~~

[[ActiveRelation::via()]] method is similar to [[ActiveRelation::viaTable()]] except that
the first parameter of [[ActiveRelation::via()]] takes a relation name declared in the ActiveRecord class.
For example, the above `items` relation can be equivalently declared as follows:

~~~
class Order extends \yii\db\ActiveRecord
{
	public function getOrderItems()
	{
		return $this->hasMany('OrderItem', array('order_id' => 'id'));
	}

	public function getItems()
	{
		return $this->hasMany('Item', array('id' => 'item_id'))
			->via('orderItems');
	}
}
~~~


When you access the related objects the first time, behind the scene ActiveRecord performs a DB query
to retrieve the corresponding data and populate it into the related objects. No query will be performed
if you access the same related objects again. We call this *lazy loading*. For example,

~~~
// SQL executed: SELECT * FROM tbl_customer WHERE id=1
$customer = Customer::find(1);
// SQL executed: SELECT * FROM tbl_order WHERE customer_id=1
$orders = $customer->orders;
// no SQL executed
$orders2 = $customer->orders;
~~~


Lazy loading is very convenient to use. However, it may suffer from performance
issue in the following scenario:

~~~
// SQL executed: SELECT * FROM tbl_customer LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
	// SQL executed: SELECT * FROM tbl_order WHERE customer_id=...
	$orders = $customer->orders;
	// ...handle $orders...
}
~~~

How many SQL queries will be performed in the above code, assuming there are more than 100 customers in
the database? 101! The first SQL query brings back 100 customers. Then for each customer, a SQL query
is performed to bring back the customer's orders.

To solve the above performance problem, you can use the so-called *eager loading* by calling [[ActiveQuery::with()]]:

~~~
// SQL executed: SELECT * FROM tbl_customer LIMIT 100
//               SELECT * FROM tbl_orders WHERE customer_id IN (1,2,...)
$customers = Customer::find()->limit(100)
	->with('orders')->all();

foreach ($customers as $customer) {
	// no SQL executed
	$orders = $customer->orders;
	// ...handle $orders...
}
~~~

As you can see, only two SQL queries are needed for the same task.


Sometimes, you may want to customize the relational queries on the fly. It can be
done for both lazy loading and eager loading. For example,

~~~
$customer = Customer::find(1);
// lazy loading: SELECT * FROM tbl_order WHERE customer_id=1 AND subtotal>100
$orders = $customer->getOrders()->where('subtotal>100')->all();

// eager loading: SELECT * FROM tbl_customer LIMIT 10
                  SELECT * FROM tbl_order WHERE customer_id IN (1,2,...) AND subtotal>100
$customers = Customer::find()->limit(100)->with(array(
	'orders' => function($query) {
		$query->andWhere('subtotal>100');
	},
))->all();
~~~


### Working with Relationships

ActiveRecord provides the following two methods for establishing and breaking a
relationship between two ActiveRecord objects:

- [[link()]]
- [[unlink()]]

For example, given a customer and a new order, we can use the following code to make the
order owned by the customer:

~~~
$customer = Customer::find(1);
$order = new Order;
$order->subtotal = 100;
$customer->link('orders', $order);
~~~

The [[link()]] call above will set the `customer_id` of the order to be the primary key
value of `$customer` and then call [[save()]] to save the order into database.


### Data Input and Validation

TBD


### Life Cycles of an ActiveRecord Object

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


### Scopes

A scope is a method that customizes a given [[ActiveQuery]] object. Scope methods are defined
in the ActiveRecord classes. They can be invoked through the [[ActiveQuery]] object that is created
via [[find()]] or [[findBySql()]]. The following is an example:

~~~
class Customer extends \yii\db\ActiveRecord
{
	// ...

	/**
	 * @param ActiveQuery $query
	 */
	public static function active($query)
	{
		$query->andWhere('status = 1');
	}
}

$customers = Customer::find()->active()->all();
~~~

In the above, the `active()` method is defined in `Customer` while we are calling it
through `ActiveQuery` returned by `Customer::find()`.

Scopes can be parameterized. For example, we can define and use the following `olderThan` scope:

~~~
class Customer extends \yii\db\ActiveRecord
{
	// ...

	/**
	 * @param ActiveQuery $query
	 * @param integer $age
	 */
	public static function olderThan($query, $age = 30)
	{
		$query->andWhere('age > :age', array(':age' => $age));
	}
}

$customers = Customer::find()->olderThan(50)->all();
~~~

The parameters should follow after the `$query` parameter when defining the scope method, and they
can take default values like shown above.

### Atomic operations and scenarios

TBD
