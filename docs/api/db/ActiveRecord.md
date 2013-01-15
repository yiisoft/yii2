ActiveRecord implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
An ActiveRecord object is associated with a row in a database table. For example, a `Customer` object
is associated with a row in the `tbl_customer` table. Instead of writing raw SQL statements to access
the data in the table, one can call intuitive methods available in the corresponding ActiveRecord class
to achieve the same goals. For example, calling [[save()]] would insert or update a row
in the underlying table.


### Declaring ActiveRecord Classes

An ActiveRecord class is declared by extending [[\yii\db\ActiveRecord]]. It typically requires the following
minimal code:

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

ActiveRecord relies on a [[Connection|DB connection]] to perform DB-related operations. By default,
it assumes that an application component named `db` gives the needed [[Connection]] instance
which serves as the DB connection. The following application configuration shows an example:

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


### Retrieving Data from Database

ActiveRecord provides three methods for data retrieval purpose:

- [[find()]]
- [[findBySql()]]
- [[count()]]

They all return an [[ActiveQuery]] instance. Coupled with the various customization and query methods
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
$count = Customer::count()
	->where(array('status' => $active))
	->value();

// to return customers in terms of arrays rather than `Customer` objects:
$customers = Customer::find()->asArray()->all();
// each $customers element is an array of name-value pairs

// to index the result by customer IDs:
$customers = Customer::find()->indexBy('id')->all();
// $customers array is indexed by customer IDs
~~~


### Accessing Column Data

ActiveRecord maps each column in the associated row of database table to an *attribute* in the ActiveRecord
object. An attribute is like a regular object property whose name is the same as the corresponding column
name and is case sensitive.

To read the value of a column, we can use the following expression:

~~~
// "id" is the name of a column in the table associated with $customer ActiveRecord object
$id = $customer->id;
// or alternatively,
$id = $customer->getAttribute('id');
~~~

And through the [[attributes]] property, we can get all column values:

~~~
$values = $customer->attributes;
~~~


### Persisting Data to Database

ActiveRecord provides the following methods to support data insertion, updating and deletion:

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


### Retrieving Relational Data

ActiveRecord supports foreign key relationships by exposing them via component properties. For example,
with appropriate declaration, the expression `$customer->orders` can return an array of `Order` objects
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

Within the getter methods, we call [[hasMany()]] or [[hasOne()]] to create a new [[ActiveRelation]] object.
The [[hasMany()]] method declares a one-many relationship. For example, a customer has many orders.
And the [[hasOne()]] method declares a many-one or one-one relationship. For example, an order has one customer.
Both methods take two parameters:

- `$class`: the class name of the related models. If the class name is not namespaced, it will take
  the same namespace as the declaring class.
- `$link`: the association between columns from two tables. This should be given as an array.
  The keys of the array are the names of the columns from the table associated with `$class`,
  while the values of the array the names of the columns from the declaring class.

Retrieving relational data is now as easy as accessing a component property. Remember that a component
property is defined by the existence of a getter method. The The following example
shows how to get the orders of a customer, and how to get the customer of the first order.

~~~
$customer = Customer::find($id);
$orders = $customer->orders;  // $orders is an array of Order objects
$customer2 = $orders[0]->customer;  // $customer == $customer2
~~~

Because [[ActiveRelation]] extends from [[ActiveQuery]], it has the same query customization methods,
which allows us to customize the query for retrieving the related objects. For example, we may declare a `bigOrder`
relationship which returns orders whose subtotal exceeds certain amount:

~~~
class Customer extends \yii\db\ActiveRecord
{
	public function getBigOrders()
	{
		return $this->hasMany('Order', array('customer_id' => 'id'))
			->where('subtotal > 100')
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

Method [[ActiveRelation::via()]] is similar to [[ActiveRelation::viaTable()]] except that
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


When we access the related objects the first time, behind the scene ActiveRecord will perform a DB query
to retrieve the corresponding data and populate them into the related objects. No query will be perform
if we access again the same related objects. We call this *lazy loading*. For example,

~~~
// SQL executed: SELECT * FROM tbl_customer WHERE id=1
$customer = Customer::find(1);
// SQL executed: SELECT * FROM tbl_order WHERE customer_id=1
$orders = $customer->orders;
// no SQL executed
$orders2 = $customer->orders;
~~~


Lazy loading is convenient to use. However, it may suffer from performance issue in the following scenario:

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

To solve the above performance problem, we can use the so-called *eager loading* by calling [[ActiveQuery::with()]]:

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

As we can see, only two SQL queries are needed for the same task.


Sometimes, we may want to customize the relational queries on the fly. This can be done for both
lazy loading and eager loading. For example,

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


### Maintaining Relationships

ActiveRecord provides the following two methods for establishing and breaking relationship
between two ActiveRecord objects:

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

// todo