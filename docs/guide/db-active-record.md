Active Record
=============

> Note: This section is under development.

[Active Record](http://en.wikipedia.org/wiki/Active_record_pattern) provides an object-oriented interface
for accessing and manipulating data stored in databases. An Active Record class is associated with a database table,
an Active Record instance corresponds to a row of that table, and an *attribute* of an Active Record
instance represents the value of a particular column in that row. Instead of writing raw SQL statements,
you would access Active Record attributes and call Active Record methods to access and manipulate the data stored 
in database tables.

For example, assume `Customer` is an Active Record class which is associated with the `customer` table
and `name` is a column of the `customer` table. You can write the following code to insert a new
row into the `customer` table:

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

The above code is equivalent to using the following raw SQL statement for MySQL, which is less
intuitive, more error prone, and may even have compatibility problems if you are using a different kind of database:

```php
$db->createCommand('INSERT INTO `customer` (`name`) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

Yii provides the Active Record support for the following relational databases:

* MySQL 4.1 or later: via [[yii\db\ActiveRecord]]
* PostgreSQL 7.3 or later: via [[yii\db\ActiveRecord]]
* SQLite 2 and 3: via [[yii\db\ActiveRecord]]
* Microsoft SQL Server 2008 or later: via [[yii\db\ActiveRecord]]
* Oracle: via [[yii\db\ActiveRecord]]
* CUBRID 9.3 or later: via [[yii\db\ActiveRecord]] (Note that due to a [bug](http://jira.cubrid.org/browse/APIS-658) in
  the cubrid PDO extension, quoting of values will not work, so you need CUBRID 9.3 as the client as well as the server)
* Sphnix: via [[yii\sphinx\ActiveRecord]], requires the `yii2-sphinx` extension
* ElasticSearch: via [[yii\elasticsearch\ActiveRecord]], requires the `yii2-elasticsearch` extension

Additionally, Yii also supports using Active Record with the following NoSQL databases:

* Redis 2.6.12 or later: via [[yii\redis\ActiveRecord]], requires the `yii2-redis` extension
* MongoDB 1.3.0 or later: via [[yii\mongodb\ActiveRecord]], requires the `yii2-mongodb` extension

In this tutorial, we will mainly describe the usage of Active Record for relational databases.
However, most content described here are also applicable to Active Record for NoSQL databases.


## Declaring Active Record Classes <span id="declaring-ar-classes"></span>

To get started, declare an Active Record class by extending [[yii\db\ActiveRecord]]. Because each Active Record
class is associated with a database table, in this class you should override the [[yii\db\ActiveRecord::tableName()|tableName()]]
method to specify which table the class is associated with.

In the following example, we declare an Active Record class named `Customer` for the `customer` database table.

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return 'customer';
    }
}
```

Active Record instances are considered as [models](structure-models.md). For this reason, we usually put Active Record
classes under the `app\models` namespace (or other namespaces for keeping model classes). 

Because [[yii\db\ActiveRecord]] extends from [[yii\base\Model]], it inherits *all* [model](structure-models.md) features,
such as attributes, validation rules, data serialization, etc.


## Connecting to Databases <span id="db-connection"></span>

By default, Active Record uses the `db` [application component](structure-application-components.md) 
as the [[yii\db\Connection|DB connection]] to access and manipulate the database data. As explained in 
[Database Access Objects](db-dao.md), you can configure the `db` component in the application configuration like shown
below,

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

If you want to use a different database connection other than the `db` component, you should override 
the [[yii\db\ActiveRecord::getDb()|getDb()]] method:

```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        // use the "db2" application component
        return \Yii::$app->db2;  
    }
}
```


## Querying Data <span id="querying-data"></span>

After declaring an Active Record class, you can use it to query data from the corresponding database table.
The process usually takes the following three steps:

1. Create a new query object by calling the [[yii\db\ActiveRecord::find()]] method;
2. Build the query object by calling [query building methods](db-query-builder.md#building-queries);
3. Call a [query method](db-query-builder.md#query-methods) to retrieve data in terms ofActive Record instances.

As you can see, this is very similar to the procedure with [query builder](db-query-builder.md). The only difference
is that instead of using the `new` operator to create a query object, you call [[yii\db\ActiveRecord::find()]]
to return a new query object which is of class [[yii\db\ActiveQuery]].

Below are some examples showing how to use Active Query to query data:

```php
// return a single customer whose ID is 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::find()
    ->where(['id' => 123])
    ->one();

// return all active customers and order them by their IDs
// SELECT * FROM `customer` WHERE `status` = 1 ORDER BY `id`
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// return the number of active customers
// SELECT COUNT(*) FROM `customer` WHERE `status` = 1
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// return all active customers in an array indexed by customer IDs
// SELECT * FROM `customer`
$customers = Customer::find()
    ->indexBy('id')
    ->all();
```

In the above, `$customer` is a `Customer` object while `$customers` is an array of `Customer` objects. They are
all populated with the data retrieved from the `customer` table.

> Info: Because [[yii\db\ActiveQuery]] extends from [[yii\db\Query]], you can use *all* query building methods and
  query methods as described in the Section [Query Builder](db-query-builder.md).

Because it is a common task to query by primary key values or a set of column values, Yii provides two shortcut
methods for this purpose:

- [[yii\db\ActiveRecord::findOne()]]: returns a single Active Record instance populated with the first row of the query result.
- [[yii\db\ActiveRecord::findAll()]]: returns an array of Active Record instances populated with *all* query result.

Both methods can take one of the following parameter formats:

- a scalar value: the value is treated as the desired primary key value to be looked for. Yii will determine 
  automatically which column is the primary key column by reading database schema information.
- an array of scalar values: the array is treated as the desired primary key values to be looked for.
- an associative array: the keys are column names and the values are the corresponding desired column values to 
  be looked for. Please refer to [Hash Format](db-query-builder.md#hash-format) for more details.
  
The following code shows how theses methods can be used:

```php
// returns a single customer whose ID is 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// returns customers whose ID is 100, 101, 123 or 124
// SELECT * FROM `customer` WHERE `id` IN (100, 101, 123, 124)
$customers = Customer::findAll([1, 2, 3]);

// returns an active customer whose ID is 123
// SELECT * FROM `customer` WHERE `id` = 123 AND `status` = 1
$customer = Customer::findOne([
    'id' => 123,
    'status' => Customer::STATUS_ACTIVE,
]);

// returns all inactive customers
// SELECT * FROM `customer` WHERE `status` = 0
$customer = Customer::findAll([
    'status' => Customer::STATUS_INACTIVE,
]);
```

> Note: Neither [[yii\db\ActiveRecord::findOne()]] nor [[yii\db\ActiveQuery::one()]] will add `LIMIT 1` to 
  the generated SQL statement. If your query may return many rows of data, you should call `limit(1)` explicitly
  to improve the performance, e.g., `Customer::find()->limit(1)->one()`.

Besides using query building methods, you can also write raw SQLs to query data and populate the results into
Active Record objects. You can do so by calling the [[yii\db\ActiveRecord::queryBySql()]] method:

```php
// returns all inactive customers
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

Do not call extra query building methods after calling [[yii\db\ActiveRecord::queryBySql()|queryBySql()]] as they
will be ignored.


## Accessing Data <span id="accessing-data"></span>

As aforementioned, the data brought back from the database are populated into Active Record instances, and
each row of the query result corresponds to a single Active Record instance. You can access the column values
by accessing the attributes of the Active Record instances, for example,

```php
// "id" and "email" are the names of columns in the "customer" table
$customer = Customer::findOne(123);
$id = $customer->id;
$email = $customer->email;
```

> Note: The Active Record attributes are named after the associated table columns in a case-sensitive manner.
  Yii automatically defines an attribute in Active Record for every column of the associated table.
  You should NOT redeclare any of the attributes. 

Because Active Record attributes are named after table columns, you may find you are writing PHP code like
`$customer->first_name`, which uses underscores to separate words in attribute names if your table columns are
named in this way. If you are concerned about code style consistency, you should rename your table columns accordingly
(to use camelCase, for example.)


### Data Transformation <span id="data-transformation"></span>

It often happens that the data being entered and/or displayed are in a different format from the one used in
storing the data in a database. For example, in the database you are storing customers' birthdays as UNIX timestamps
(which is not a good design, though), while in most cases you would like to manipulate birthdays as strings in
the format of `'YYYY/MM/DD'`. To achieve this goal, you can define data transformation methods in the `Customer`
Active Record class like the following:

```php
class Customer extends ActiveRecord
{
    // ...

    public function getBirthdayText()
    {
        return date('Y/m/d', $this->birthday);
    }
    
    public function setBirthdayText($value)
    {
        $this->birthday = strtotime($value);
    }
}
```

Now in your PHP code, instead of accessing `$customer->birthday`, you would access `$customer->birthdayText`, which
will allow you to input and display customer birthdays in the format of `'YYYY/MM/DD'`.


### Retrieving Data in Arrays <span id="data-in-arrays"></span>

While retrieving data in terms of Active Record objects is convenient and flexible, it is not always desirable
when you have to bring back a large amount of data due to the big memory footprint. In this case, you can retrieve
data using PHP arrays by calling [[yii\db\ActiveQuery::asArray()|asArray()]] before executing a query method:

```php
// return all customers
// each customer is returned as an associative array
$customers = Customer::find()
    ->asArray()
    ->all();
```

> Note: While this method saves memory and improves performance, it is closer to the lower DB abstraction layer
  and you will lose most of the Active Record features. A very important distinction lies in the data type of
  the column values. When you return data in Active Record instances, column values will be automatically typecast
  according to the actual column types; on the other hand when you return data in arrays, column values will be
  strings (since they are the result of PDO without any processing), regardless their actual column types.
   

### Retrieving Data in Batches <span id="data-in-batches"></span>

In [Query Builder](db-query-builder.md), we have explained that you may use *batch query* to minimize your memory
usage when querying a large amount of data from the database. You may use the same technique in Active Record. For example,

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
    // $customer is a Customer object
}
```


## Manipulating Data in Database

Active Record provides the following methods to insert, update and delete a single row in a table associated with
a single Active Record instance:

- [[yii\db\ActiveRecord::save()|save()]]
- [[yii\db\ActiveRecord::insert()|insert()]]
- [[yii\db\ActiveRecord::update()|update()]]
- [[yii\db\ActiveRecord::delete()|delete()]]

Active Record also provides the following static methods that apply to a whole table associated with
an Active Record class. Be extremely careful when using these methods as they affect the whole table.
For example, `deleteAll()` will delete ALL rows in the table.

- [[yii\db\ActiveRecord::updateCounters()|updateCounters()]]
- [[yii\db\ActiveRecord::updateAll()|updateAll()]]
- [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]]
- [[yii\db\ActiveRecord::deleteAll()|deleteAll()]]


The following examples show how to use these methods:

```php
// to insert a new customer record
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();  // equivalent to $customer->insert();

// to update an existing customer record
$customer = Customer::findOne($id);
$customer->email = 'james@example.com';
$customer->save();  // equivalent to $customer->update();

// to delete an existing customer record
$customer = Customer::findOne($id);
$customer->delete();

// to delete several customers
Customer::deleteAll('age > :age AND gender = :gender', [':age' => 20, ':gender' => 'M']);

// to increment the age of ALL customers by 1
Customer::updateAllCounters(['age' => 1]);
```

> Info: The `save()` method will call either `insert()` or `update()`, depending on whether
  the Active Record instance is new or not (internally it will check the value of [[yii\db\ActiveRecord::isNewRecord]]).
  If an Active Record is instantiated via the `new` operator, calling `save()` will
  insert a row in the table; calling `save()` on an active record fetched from the database will update the corresponding
  row in the table.


### Data Input and Validation

Because Active Record extends from [[yii\base\Model]], it supports the same data input and validation features
as described in [Model](structure-models.md). For example, you may declare validation rules by overwriting the
[[yii\base\Model::rules()|rules()]] method; you may massively assign user input data to an Active Record instance;
and you may call [[yii\base\Model::validate()|validate()]] to trigger data validation.

When you call `save()`, `insert()` or `update()`, these methods will automatically call [[yii\base\Model::validate()|validate()]].
If the validation fails, the corresponding data saving operation will be cancelled.

The following example shows how to use an Active Record to collect/validate user input and save them into the database:

```php
// creating a new record
$model = new Customer;
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    // the user input has been collected, validated and saved
}

// updating a record whose primary key is $id
$model = Customer::findOne($id);
if ($model === null) {
    throw new NotFoundHttpException;
}
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    // the user input has been collected, validated and saved
}
```


### Loading Default Values

Your table columns may be defined with default values. Sometimes, you may want to pre-populate your
Web form for an Active Record with these values. To do so, call the
[[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]] method before rendering the form:

```php
$customer = new Customer();
$customer->loadDefaultValues();
// ... render HTML form for $customer ...
```

If you want to set some initial values for the attributes yourself you can override the `init()` method
of the active record class and set the values there. For example to set the default value for the `status` attribute:

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_ACTIVE;
}
```

Active Record Life Cycles
-------------------------

It is important to understand the life cycles of Active Record when it is used to manipulate data in database.
These life cycles are typically associated with corresponding events which allow you to inject code
to intercept or respond to these events. They are especially useful for developing Active Record [behaviors](concept-behaviors.md).

When instantiating a new Active Record instance, we will have the following life cycles:

1. constructor
2. [[yii\db\ActiveRecord::init()|init()]]: will trigger an [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] event

When querying data through the [[yii\db\ActiveRecord::find()|find()]] method, we will have the following life cycles
for EVERY newly populated Active Record instance:

1. constructor
2. [[yii\db\ActiveRecord::init()|init()]]: will trigger an [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] event
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] event

When calling [[yii\db\ActiveRecord::save()|save()]] to insert or update an ActiveRecord, we will have
the following life cycles:

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: will trigger an [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] event
2. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]] event
3. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: will trigger an [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] or [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]] event
4. perform the actual data insertion or updating
5. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] or [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]] event

And finally, when calling [[yii\db\ActiveRecord::delete()|delete()]] to delete an ActiveRecord, we will have
the following life cycles:

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: will trigger an [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]] event
2. perform the actual data deletion
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: will trigger an [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]] event


Working with Relational Data
----------------------------

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
    public function getCustomer()
    {
        // Order has_one Customer via Customer.id -> customer_id
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
$customer = Customer::findOne(1);
$orders = $customer->orders;  // $orders is an array of Order objects
```

Behind the scenes, the above code executes the following two SQL queries, one for each line of code:

```sql
SELECT * FROM customer WHERE id=1;
SELECT * FROM order WHERE customer_id=1;
```

> Tip: If you access the expression `$customer->orders` again, it will not perform the second SQL query again.
The SQL query is only performed the first time when this expression is accessed. Any further
accesses will only return the previously fetched results that are cached internally. If you want to re-query
the relational data, simply unset the existing expression first: `unset($customer->orders);`.

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
an array of that, or null, depending on the multiplicity of the relation. For example, `$customer->getOrders()` returns
an `ActiveQuery` instance, while `$customer->orders` returns an array of `Order` objects (or an empty array if
the query results in nothing).


Relations with Junction Table
-----------------------------

Sometimes, two tables are related together via an intermediary table called a [junction table][]. To declare such relations,
we can customize the [[yii\db\ActiveQuery]] object by calling its [[yii\db\ActiveQuery::via()|via()]] or
[[yii\db\ActiveQuery::viaTable()|viaTable()]] method.

For example, if table `order` and table `item` are related via the junction table `order_item`,
we can declare the `items` relation in the `Order` class like the following:

```php
class Order extends \yii\db\ActiveRecord
{
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaTable('order_item', ['order_id' => 'id']);
    }
}
```

The [[yii\db\ActiveQuery::via()|via()]] method is similar to [[yii\db\ActiveQuery::viaTable()|viaTable()]] except that
the first parameter of [[yii\db\ActiveQuery::via()|via()]] takes a relation name declared in the ActiveRecord class
instead of the junction table name. For example, the above `items` relation can be equivalently declared as follows:

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

[junction table]: https://en.wikipedia.org/wiki/Junction_table "Junction table on Wikipedia"


Lazy and Eager Loading
----------------------

As described earlier, when you access the related objects for the first time, ActiveRecord will perform a DB query
to retrieve the corresponding data and populate it into the related objects. No query will be performed
if you access the same related objects again. We call this *lazy loading*. For example,

```php
// SQL executed: SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// SQL executed: SELECT * FROM order WHERE customer_id=1
$orders = $customer->orders;
// no SQL executed
$orders2 = $customer->orders;
```

Lazy loading is very convenient to use. However, it may suffer from a performance issue in the following scenario:

```php
// SQL executed: SELECT * FROM customer LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SQL executed: SELECT * FROM order WHERE customer_id=...
    $orders = $customer->orders;
    // ...handle $orders...
}
```

How many SQL queries will be performed in the above code, assuming there are more than 100 customers in
the database? 101! The first SQL query brings back 100 customers. Then for each customer, a SQL query
is performed to bring back the orders of that customer.

To solve the above performance problem, you can use the so-called *eager loading* approach by calling [[yii\db\ActiveQuery::with()]]:

```php
// SQL executed: SELECT * FROM customer LIMIT 100;
//               SELECT * FROM orders WHERE customer_id IN (1,2,...)
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
> each of the `M` junction tables corresponding to the `via()` or `viaTable()` calls, and one for each of the `N` related tables.

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
$customer = Customer::findOne(1);
// lazy loading: SELECT * FROM order WHERE customer_id=1 AND subtotal>100
$orders = $customer->getOrders()->where('subtotal>100')->all();

// eager loading: SELECT * FROM customer LIMIT 100
//                SELECT * FROM order WHERE customer_id IN (1,2,...) AND subtotal>100
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
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    ....
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

If we perform the following query, we would find that the `customer` of an order is not the same customer object
that finds those orders, and accessing `customer->orders` will trigger one SQL execution while accessing
the `customer` of an order will trigger another SQL execution:

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// echoes "not equal"
// SELECT * FROM order WHERE customer_id=1
// SELECT * FROM customer WHERE id=1
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
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

Now if we execute the same query as shown above, we would get:

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// echoes "equal"
// SELECT * FROM order WHERE customer_id=1
if ($customer->orders[0]->customer === $customer) {
    echo 'equal';
} else {
    echo 'not equal';
}
```

In the above, we have shown how to use inverse relations in lazy loading. Inverse relations also apply in
eager loading:

```php
// SELECT * FROM customer
// SELECT * FROM order WHERE customer_id IN (1, 2, ...)
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


Joining with Relations <span id="joining-with-relations"></span>
----------------------

When working with relational databases, a common task is to join multiple tables and apply various
query conditions and parameters to the JOIN SQL statement. Instead of calling [[yii\db\ActiveQuery::join()]]
explicitly to build up the JOIN query, you may reuse the existing relation definitions and call
[[yii\db\ActiveQuery::joinWith()]] to achieve this goal. For example,

```php
// find all orders and sort the orders by the customer id and the order id. also eager loading "customer"
$orders = Order::find()->joinWith('customer')->orderBy('customer.id, order.id')->all();
// find all orders that contain books, and eager loading "books"
$orders = Order::find()->innerJoinWith('books')->all();
```

In the above, the method [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] is a shortcut to [[yii\db\ActiveQuery::joinWith()|joinWith()]]
with the join type set as `INNER JOIN`.

You may join with one or multiple relations; you may apply query conditions to the relations on-the-fly;
and you may also join with sub-relations. For example,

```php
// join with multiple relations
// find the orders that contain books and were placed by customers who registered within the past 24 hours
$orders = Order::find()->innerJoinWith([
    'books',
    'customer' => function ($query) {
        $query->where('customer.created_at > ' . (time() - 24 * 3600));
    }
])->all();
// join with sub-relations: join with books and books' authors
$orders = Order::find()->joinWith('books.author')->all();
```

Behind the scenes, Yii will first execute a JOIN SQL statement to bring back the primary models
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
In the above examples, we use `item.id` and `order.id` to disambiguate the `id` column references
because both of the order table and the item table contain a column named `id`.

By default, when you join with a relation, the relation will also be eagerly loaded. You may change this behavior
by passing the `$eagerLoading` parameter which specifies whether to eager load the specified relations.

And also by default, [[yii\db\ActiveQuery::joinWith()|joinWith()]] uses `LEFT JOIN` to join the related tables.
You may pass it with the `$joinType` parameter to customize the join type. As a shortcut to the `INNER JOIN` type,
you may use [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]].

Below are some more examples,

```php
// find all orders that contain books, but do not eager load "books".
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

When you perform a query using [[yii\db\ActiveQuery::joinWith()|joinWith()]], the ON condition will be put in the ON part
of the corresponding JOIN query. For example,

```php
// SELECT user.* FROM user LEFT JOIN item ON item.owner_id=user.id AND category_id=1
// SELECT * FROM item WHERE owner_id IN (...) AND category_id=1
$users = User::find()->joinWith('books')->all();
```

Note that if you use eager loading via [[yii\db\ActiveQuery::with()]] or lazy loading, the on-condition will be put
in the WHERE part of the corresponding SQL statement, because there is no JOIN query involved. For example,

```php
// SELECT * FROM user WHERE id=10
$user = User::findOne(10);
// SELECT * FROM item WHERE owner_id=10 AND category_id=1
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
$customer = Customer::findOne(1);
$order = new Order();
$order->subtotal = 100;
$customer->link('orders', $order);
```

The [[yii\db\ActiveRecord::link()|link()]] call above will set the `customer_id` of the order to be the primary key
value of `$customer` and then call [[yii\db\ActiveRecord::save()|save()]] to save the order into the database.


Cross-DBMS Relations
--------------------

ActiveRecord allows you to establish relationships between entities from different DBMS. For example: between a relational database table and MongoDB collection. Such a relation does not require any special code:

```php
// Relational database Active Record
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // Customer, stored in relational database, has many Comments, stored in MongoDB collection:
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
    }
}

// MongoDb Active Record
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // Comment, stored in MongoDB collection, has one Customer, stored in relational database:
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

All Active Record features like eager and lazy loading, establishing and breaking a relationship and so on, are
available for cross-DBMS relations.

> Note: do not forget Active Record solutions for different DBMS may have specific methods and features, which may not be
  applied for cross-DBMS relations. For example: usage of [[yii\db\ActiveQuery::joinWith()]] will obviously not work with
  relation to the MongoDB collection.


Scopes
------

When you call [[yii\db\ActiveRecord::find()|find()]] or [[yii\db\ActiveRecord::findBySql()|findBySql()]], it returns an
[[yii\db\ActiveQuery|ActiveQuery]] instance.
You may call additional query methods, such as [[yii\db\ActiveQuery::where()|where()]], [[yii\db\ActiveQuery::orderBy()|orderBy()]],
to further specify the query conditions.

It is possible that you may want to call the same set of query methods in different places. If this is the case,
you should consider defining the so-called *scopes*. A scope is essentially a method defined in a custom query class that calls a set of query methods to modify the query object. You can then use a scope instead of calling a normal query method.

Two steps are required to define a scope. First, create a custom query class for your model and define the needed scope
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

Second, override [[yii\db\ActiveRecord::find()]] to use the custom query class instead of the regular [[yii\db\ActiveQuery|ActiveQuery]].
For the example above, you need to write the following code:

```php
namespace app\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    /**
     * @inheritdoc
     * @return CommentQuery
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
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

Or use the scopes on-the-fly when performing a relational query:

```php
$posts = Post::find()->with([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

### Default Scope

If you used Yii 1.1 before, you may know a concept called *default scope*. A default scope is a scope that
applies to ALL queries. You can define a default scope easily by overriding [[yii\db\ActiveRecord::find()]]. For example,

```php
public static function find()
{
    return parent::find()->where(['deleted' => false]);
}
```

Note that all your queries should then not use [[yii\db\ActiveQuery::where()|where()]] but
[[yii\db\ActiveQuery::andWhere()|andWhere()]] and [[yii\db\ActiveQuery::orWhere()|orWhere()]]
to not override the default condition.


Transactional operations
---------------------

There are two ways of dealing with transactions while working with Active Record. First way is doing everything manually
as described in the "transactions" section of "[Database basics](db-dao.md)". Another way is to implement the
`transactions` method where you can specify which operations are to be wrapped into transactions on a per model scenario:

```php
class Post extends \yii\db\ActiveRecord
{
    public function transactions()
    {
        return [
            'admin' => self::OP_INSERT,
            'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            // the above is equivalent to the following:
            // 'api' => self::OP_ALL,
        ];
    }
}
```

In the above `admin` and `api` are model scenarios and the constants starting with `OP_` are operations that should
be wrapped in transactions for these scenarios. Supported operations are `OP_INSERT`, `OP_UPDATE` and `OP_DELETE`.
`OP_ALL` stands for all three.

Such automatic transactions are especially useful if you're doing additional database changes in `beforeSave`,
`afterSave`, `beforeDelete`, `afterDelete` and want to be sure that both succeeded before they are saved.

Optimistic Locks
--------------

Optimistic locking allows multiple users to access the same record for edits and avoids
potential conflicts. For example, when a user attempts to save the record upon some staled data
(because another user has modified the data), a [[\yii\db\StaleObjectException]] exception will be thrown,
and the update or deletion is skipped.

Optimistic locking is only supported by `update()` and `delete()` methods and isn't used by default.

To use Optimistic locking:

1. Create a column to store the version number of each row. The column type should be `BIGINT DEFAULT 0`.
   Override the `optimisticLock()` method to return the name of this column.
2. In the Web form that collects the user input, add a hidden field that stores
   the lock version of the record being updated.
3. In the controller action that does the data updating, try to catch the [[\yii\db\StaleObjectException]]
   and implement necessary business logic (e.g. merging the changes, prompting staled data)
   to resolve the conflict.

Dirty Attributes
--------------

An attribute is considered dirty if its value was modified after the model was loaded from database or since the most recent data save. When saving record data by calling `save()`, `update()`, `insert()` etc. only dirty attributes are saved into the database. If there are no dirty attributes then there is nothing to be saved so no query will be issued at all.

See also
--------

- [Model](structure-models.md)
- [[yii\db\ActiveRecord]]
