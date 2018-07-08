Active Record
=============

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
* Sphinx: via [[yii\sphinx\ActiveRecord]], requires the `yii2-sphinx` extension
* ElasticSearch: via [[yii\elasticsearch\ActiveRecord]], requires the `yii2-elasticsearch` extension

Additionally, Yii also supports using Active Record with the following NoSQL databases:

* Redis 2.6.12 or later: via [[yii\redis\ActiveRecord]], requires the `yii2-redis` extension
* MongoDB 1.3.0 or later: via [[yii\mongodb\ActiveRecord]], requires the `yii2-mongodb` extension

In this tutorial, we will mainly describe the usage of Active Record for relational databases.
However, most content described here are also applicable to Active Record for NoSQL databases.


## Declaring Active Record Classes <span id="declaring-ar-classes"></span>

To get started, declare an Active Record class by extending [[yii\db\ActiveRecord]]. 

### Setting a table name

By default each Active Record class is associated with its database table.
The [[yii\db\ActiveRecord::tableName()|tableName()]] method returns the table name by converting the class name via [[yii\helpers\Inflector::camel2id()]].
You may override this method if the table is not named after this convention.

Also a default [[yii\db\Connection::$tablePrefix|tablePrefix]] can be applied. For example if
 [[yii\db\Connection::$tablePrefix|tablePrefix]] is `tbl_`, `Customer` becomes `tbl_customer` and `OrderItem` becomes `tbl_order_item`. 

If a table name is given as `{{%TableName}}`, then the percentage character `%` will be replaced with the table prefix. 
For example, `{{%post}}` becomes `{{tbl_post}}`. The brackets around the table name are used for
[quoting in an SQL query](db-dao.md#quoting-table-and-column-names).

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
        return '{{customer}}';
    }
}
```

### Active records are called "models"

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
            '__class' => \yii\db\Connection::class,
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
3. Call a [query method](db-query-builder.md#query-methods) to retrieve data in terms of Active Record instances.

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

// return all customers in an array indexed by customer IDs
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
  
The following code shows how these methods can be used:

```php
// returns a single customer whose ID is 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// returns customers whose ID is 100, 101, 123 or 124
// SELECT * FROM `customer` WHERE `id` IN (100, 101, 123, 124)
$customers = Customer::findAll([100, 101, 123, 124]);

// returns an active customer whose ID is 123
// SELECT * FROM `customer` WHERE `id` = 123 AND `status` = 1
$customer = Customer::findOne([
    'id' => 123,
    'status' => Customer::STATUS_ACTIVE,
]);

// returns all inactive customers
// SELECT * FROM `customer` WHERE `status` = 0
$customers = Customer::findAll([
    'status' => Customer::STATUS_INACTIVE,
]);
```

> Warning: If you need to pass user input to these methods, make sure the input value is scalar or in case of
> array condition, make sure the array structure can not be changed from the outside:
>
> ```php
> // yii\web\Controller ensures that $id is scalar
> public function actionView($id)
> {
>     $model = Post::findOne($id);
>     // ...
> }
>
> // explicitly specifying the column to search, passing a scalar or array here will always result in finding a single record
> $model = Post::findOne(['id' => Yii::$app->request->get('id')]);
>
> // do NOT use the following code! it is possible to inject an array condition to filter by arbitrary column values!
> $model = Post::findOne(Yii::$app->request->get('id'));
> ```


> Note: Neither [[yii\db\ActiveRecord::findOne()]] nor [[yii\db\ActiveQuery::one()]] will add `LIMIT 1` to 
  the generated SQL statement. If your query may return many rows of data, you should call `limit(1)` explicitly
  to improve the performance, e.g., `Customer::find()->limit(1)->one()`.

Besides using query building methods, you can also write raw SQLs to query data and populate the results into
Active Record objects. You can do so by calling the [[yii\db\ActiveRecord::findBySql()]] method:

```php
// returns all inactive customers
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

Do not call extra query building methods after calling [[yii\db\ActiveRecord::findBySql()|findBySql()]] as they
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
(to use camelCase, for example).


### Data Transformation <span id="data-transformation"></span>

It often happens that the data being entered and/or displayed are in a format which is different from the one used in
storing the data in a database. For example, in the database you are storing customers' birthdays as UNIX timestamps
(which is not a good design, though), while in most cases you would like to manipulate birthdays as strings in
the format of `'YYYY/MM/DD'`. To achieve this goal, you can define *data transformation* methods in the `Customer`
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

> Tip: The above example shows a generic way of transforming data in different formats. If you are working with
> date values, you may use [DateValidator](tutorial-core-validators.md#date) and [[yii\jui\DatePicker|DatePicker]],
> which is easier to use and more powerful.


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
    // $customer is a Customer object with the 'orders' relation populated
}
```


## Saving Data <span id="inserting-updating-data"></span>

Using Active Record, you can easily save data to the database by taking the following steps:

1. Prepare an Active Record instance
2. Assign new values to Active Record attributes
3. Call [[yii\db\ActiveRecord::save()]] to save the data into database.

For example,

```php
// insert a new row of data
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();

// update an existing row of data
$customer = Customer::findOne(123);
$customer->email = 'james@newexample.com';
$customer->save();
```

The [[yii\db\ActiveRecord::save()|save()]] method can either insert or update a row of data, depending on the state
of the Active Record instance. If the instance is newly created via the `new` operator, calling 
[[yii\db\ActiveRecord::save()|save()]] will cause insertion of a new row; If the instance is the result of a query method,
calling [[yii\db\ActiveRecord::save()|save()]] will update the row associated with the instance. 

You can differentiate the two states of an Active Record instance by checking its 
[[yii\db\ActiveRecord::isNewRecord|isNewRecord]] property value. This property is also used by 
[[yii\db\ActiveRecord::save()|save()]] internally as follows:

```php
public function save($runValidation = true, $attributeNames = null)
{
    if ($this->getIsNewRecord()) {
        return $this->insert($runValidation, $attributeNames);
    } else {
        return $this->update($runValidation, $attributeNames) !== false;
    }
}
```

> Tip: You can call [[yii\db\ActiveRecord::insert()|insert()]] or [[yii\db\ActiveRecord::update()|update()]]
  directly to insert or update a row.
  

### Data Validation <span id="data-validation"></span>

Because [[yii\db\ActiveRecord]] extends from [[yii\base\Model]], it shares the same [data validation](input-validation.md) feature.
You can declare validation rules by overriding the [[yii\db\ActiveRecord::rules()|rules()]] method and perform 
data validation by calling the [[yii\db\ActiveRecord::validate()|validate()]] method.

When you call [[yii\db\ActiveRecord::save()|save()]], by default it will call [[yii\db\ActiveRecord::validate()|validate()]]
automatically. Only when the validation passes, will it actually save the data; otherwise it will simply return `false`,
and you can check the [[yii\db\ActiveRecord::errors|errors]] property to retrieve the validation error messages.  

> Tip: If you are certain that your data do not need validation (e.g., the data comes from trustable sources),
  you can call `save(false)` to skip the validation.


### Massive Assignment <span id="massive-assignment"></span>

Like normal [models](structure-models.md), Active Record instances also enjoy the [massive assignment feature](structure-models.md#massive-assignment).
Using this feature, you can assign values to multiple attributes of an Active Record instance in a single PHP statement,
like shown below. Do remember that only [safe attributes](structure-models.md#safe-attributes) can be massively assigned, though.

```php
$values = [
    'name' => 'James',
    'email' => 'james@example.com',
];

$customer = new Customer();

$customer->attributes = $values;
$customer->save();
```


### Updating Counters <span id="updating-counters"></span>

It is a common task to increment or decrement a column in a database table. We call these columns "counter columns".
You can use [[yii\db\ActiveRecord::updateCounters()|updateCounters()]] to update one or multiple counter columns.
For example,

```php
$post = Post::findOne(100);

// UPDATE `post` SET `view_count` = `view_count` + 1 WHERE `id` = 100
$post->updateCounters(['view_count' => 1]);
```

> Note: If you use [[yii\db\ActiveRecord::save()]] to update a counter column, you may end up with inaccurate result,
  because it is likely the same counter is being saved by multiple requests which read and write the same counter value.


### Dirty Attributes <span id="dirty-attributes"></span>

When you call [[yii\db\ActiveRecord::save()|save()]] to save an Active Record instance, only *dirty attributes*
are being saved. An attribute is considered *dirty* if its value has been modified since it was loaded from DB or
saved to DB most recently. Note that data validation will be performed regardless if the Active Record 
instance has dirty attributes or not.

Active Record automatically maintains the list of dirty attributes. It does so by maintaining an older version of
the attribute values and comparing them with the latest one. You can call [[yii\db\ActiveRecord::getDirtyAttributes()]] 
to get the attributes that are currently dirty. You can also call [[yii\db\ActiveRecord::markAttributeDirty()]] 
to explicitly mark an attribute as dirty.

If you are interested in the attribute values prior to their most recent modification, you may call 
[[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] or [[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]].

> Note: The comparison of old and new values will be done using the `===` operator so a value will be considered dirty
> even if it has the same value but a different type. This is often the case when the model receives user input from
> HTML forms where every value is represented as a string.
> To ensure the correct type for e.g. integer values you may apply a [validation filter](input-validation.md#data-filtering):
> `['attributeName', 'filter', 'filter' => 'intval']`. This works with all the typecasting functions of PHP like
> [intval()](http://php.net/manual/en/function.intval.php), [floatval()](http://php.net/manual/en/function.floatval.php),
> [boolval](http://php.net/manual/en/function.boolval.php), etc...

### Default Attribute Values <span id="default-attribute-values"></span>

Some of your table columns may have default values defined in the database. Sometimes, you may want to pre-populate your
Web form for an Active Record instance with these default values. To avoid writing the same default values again,
you can call [[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]] to populate the DB-defined default values
into the corresponding Active Record attributes:

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz will be assigned the default value declared when defining the "xyz" column
```


### Attributes Typecasting <span id="attributes-typecasting"></span>

Being populated by query results, [[yii\db\ActiveRecord]] performs automatic typecast for its attribute values, using
information from [database table schema](db-dao.md#database-schema). This allows data retrieved from table column
declared as integer to be populated in ActiveRecord instance with PHP integer, boolean with boolean and so on.
However, typecasting mechanism has several limitations:

* Float values are not be converted and will be represented as strings, otherwise they may loose precision.
* Conversion of the integer values depends on the integer capacity of the operation system you use. In particular:
  values of column declared as 'unsigned integer' or 'big integer' will be converted to PHP integer only at 64-bit
  operation system, while on 32-bit ones - they will be represented as strings.

Note that attribute typecast is performed only during populating ActiveRecord instance from query result. There is no
automatic conversion for the values loaded from HTTP request or set directly via property access.
The table schema will also be used while preparing SQL statements for the ActiveRecord data saving, ensuring
values are bound to the query with correct type. However, ActiveRecord instance attribute values will not be
converted during saving process.

> Tip: you may use [[yii\behaviors\AttributeTypecastBehavior]] to facilitate attribute values typecasting
  on ActiveRecord validation or saving.
  
Since 2.0.14, Yii ActiveRecord supports complex data types, such as JSON or multidimensional arrays.

#### JSON in MySQL and PostgreSQL

After data population, the value from JSON column will be automatically decoded from JSON according to standard JSON
decoding rules.

To save attribute value to a JSON column, ActiveRecord will automatically create a [[yii\db\JsonExpression|JsonExpression]] object
that will be encoded to a JSON string on [QueryBuilder](db-query-builder.md) level.

#### Arrays in PostgreSQL

After data population, the value from Array column will be automatically decoded from PgSQL notation to an [[yii\db\ArrayExpression|ArrayExpression]]
object. It implements PHP `ArrayAccess` interface, so you can use it as an array, or call `->getValue()` to get the array itself.

To save attribute value to an array column, ActiveRecord will automatically create an [[yii\db\ArrayExpression|ArrayExpression]] object
that will be encoded by [QueryBuilder](db-query-builder.md) to an PgSQL string representation of array.

You can also use conditions for JSON columns:

```php
$query->andWhere(['=', 'json', new ArrayExpression(['foo' => 'bar'])
```

To learn more about expressions building system read the [Query Builder – Adding custom Conditions and Expressions](db-query-builder.md#adding-custom-conditions-and-expressions)
article.

### Updating Multiple Rows <span id="updating-multiple-rows"></span>

The methods described above all work on individual Active Record instances, causing inserting or updating of individual
table rows. To update multiple rows simultaneously, you should call [[yii\db\ActiveRecord::updateAll()|updateAll()]], instead,
which is a static method.

```php
// UPDATE `customer` SET `status` = 1 WHERE `email` LIKE `%@example.com%`
Customer::updateAll(['status' => Customer::STATUS_ACTIVE], ['like', 'email', '@example.com']);
```

Similarly, you can call [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] to update counter columns of
multiple rows at the same time.

```php
// UPDATE `customer` SET `age` = `age` + 1
Customer::updateAllCounters(['age' => 1]);
```


## Deleting Data <span id="deleting-data"></span>

To delete a single row of data, first retrieve the Active Record instance corresponding to that row and then call
the [[yii\db\ActiveRecord::delete()]] method.

```php
$customer = Customer::findOne(123);
$customer->delete();
```

You can call [[yii\db\ActiveRecord::deleteAll()]] to delete multiple or all rows of data. For example,

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> Note: Be very careful when calling [[yii\db\ActiveRecord::deleteAll()|deleteAll()]] because it may totally
  erase all data from your table if you make a mistake in specifying the condition.


## Active Record Life Cycles <span id="ar-life-cycles"></span>

It is important to understand the life cycles of Active Record when it is used for different purposes.
During each life cycle, a certain sequence of methods will be invoked, and you can override these methods
to get a chance to customize the life cycle. You can also respond to certain Active Record events triggered 
during a life cycle to inject your custom code. These events are especially useful when you are developing 
Active Record [behaviors](concept-behaviors.md) which need to customize Active Record life cycles.

In the following, we will summarize the various Active Record life cycles and the methods/events that are involved
in the life cycles.


### New Instance Life Cycle <span id="new-instance-life-cycle"></span>

When creating a new Active Record instance via the `new` operator, the following life cycle will happen:

1. Class constructor.
2. [[yii\db\ActiveRecord::init()|init()]]: triggers an [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] event.


### Querying Data Life Cycle <span id="querying-data-life-cycle"></span>

When querying data through one of the [querying methods](#querying-data), each newly populated Active Record will
undergo the following life cycle:

1. Class constructor.
2. [[yii\db\ActiveRecord::init()|init()]]: triggers an [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] event.
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: triggers an [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] event.


### Saving Data Life Cycle <span id="saving-data-life-cycle"></span>

When calling [[yii\db\ActiveRecord::save()|save()]] to insert or update an Active Record instance, the following
life cycle will happen:

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: triggers 
   an [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] event. If the method returns `false`
   or [[yii\base\ModelEvent::isValid]] is `false`, the rest of the steps will be skipped.
2. Performs data validation. If data validation fails, the steps after Step 3 will be skipped. 
3. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: triggers 
   an [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]] event.
4. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: triggers 
   an [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] 
   or [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]] event. If the method returns `false`
   or [[yii\base\ModelEvent::isValid]] is `false`, the rest of the steps will be skipped.
5. Performs the actual data insertion or updating.
6. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: triggers
   an [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] 
   or [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]] event.
   

### Deleting Data Life Cycle <span id="deleting-data-life-cycle"></span>

When calling [[yii\db\ActiveRecord::delete()|delete()]] to delete an Active Record instance, the following
life cycle will happen:

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: triggers
   an [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]] event. If the method returns `false`
   or [[yii\base\ModelEvent::isValid]] is `false`, the rest of the steps will be skipped.
2. Performs the actual data deletion.
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: triggers
   an [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]] event.


> Note: Calling any of the following methods will NOT initiate any of the above life cycles because they work on the
> database directly and not on a record basis:
>
> - [[yii\db\ActiveRecord::updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()]] 

### Refreshing Data Life Cycle <span id="refreshing-data-life-cycle"></span>

When calling [[yii\db\ActiveRecord::refresh()|refresh()]] to refresh an Active Record instance, the
[[yii\db\ActiveRecord::EVENT_AFTER_REFRESH|EVENT_AFTER_REFRESH]] event is triggered if refresh is successful and the method returns `true`.


## Working with Transactions <span id="transactional-operations"></span>

There are two ways of using [transactions](db-dao.md#performing-transactions) while working with Active Record. 

The first way is to explicitly enclose Active Record method calls in a transactional block, like shown below,

```php
$customer = Customer::findOne(123);

Customer::getDb()->transaction(function($db) use ($customer) {
    $customer->id = 200;
    $customer->save();
    // ...other DB operations...
});

// or alternatively

$transaction = Customer::getDb()->beginTransaction();
try {
    $customer->id = 200;
    $customer->save();
    // ...other DB operations...
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```

> Note: in the above code we have two catch-blocks for compatibility 
> with PHP 5.x and PHP 7.x. `\Exception` implements the [`\Throwable` interface](http://php.net/manual/en/class.throwable.php)
> since PHP 7.0, so you can skip the part with `\Exception` if your app uses only PHP 7.0 and higher.

The second way is to list the DB operations that require transactional support in the [[yii\db\ActiveRecord::transactions()]]
method. For example,

```php
class Customer extends ActiveRecord
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

The [[yii\db\ActiveRecord::transactions()]] method should return an array whose keys are [scenario](structure-models.md#scenarios)
names and values are the corresponding operations that should be enclosed within transactions. You should use the following
constants to refer to different DB operations:

* [[yii\db\ActiveRecord::OP_INSERT|OP_INSERT]]: insertion operation performed by [[yii\db\ActiveRecord::insert()|insert()]];
* [[yii\db\ActiveRecord::OP_UPDATE|OP_UPDATE]]: update operation performed by [[yii\db\ActiveRecord::update()|update()]];
* [[yii\db\ActiveRecord::OP_DELETE|OP_DELETE]]: deletion operation performed by [[yii\db\ActiveRecord::delete()|delete()]].

Use the `|` operators to concatenate the above constants to indicate multiple operations. You may also use the shortcut
constant [[yii\db\ActiveRecord::OP_ALL|OP_ALL]] to refer to all three operations above.

Transactions that are created using this method will be started before calling [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]
and will be committed after [[yii\db\ActiveRecord::afterSave()|afterSave()]] has run.

## Optimistic Locks <span id="optimistic-locks"></span>

Optimistic locking is a way to prevent conflicts that may occur when a single row of data is being
updated by multiple users. For example, both user A and user B are editing the same wiki article
at the same time. After user A saves his edits, user B clicks on the "Save" button in an attempt to
save his edits as well. Because user B was actually working on an outdated version of the article,
it would be desirable to have a way to prevent him from saving the article and show him some hint message.

Optimistic locking solves the above problem by using a column to record the version number of each row.
When a row is being saved with an outdated version number, a [[yii\db\StaleObjectException]] exception
will be thrown, which prevents the row from being saved. Optimistic locking is only supported when you
update or delete an existing row of data using [[yii\db\ActiveRecord::update()]] or [[yii\db\ActiveRecord::delete()]],
respectively.

To use optimistic locking,

1. Create a column in the DB table associated with the Active Record class to store the version number of each row.
   The column should be of big integer type (in MySQL it would be `BIGINT DEFAULT 0`).
2. Override the [[yii\db\ActiveRecord::optimisticLock()]] method to return the name of this column.
3. Implement [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] inside your model class to automatically parse its value from received requests.
4. In the Web form that takes user inputs, add a hidden field to store the current version number of the row being updated.
   Remove the version attribute from validation rules as [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] should handle it.
5. In the controller action that updates the row using Active Record, try and catch the [[yii\db\StaleObjectException]]
   exception. Implement necessary business logic (e.g. merging the changes, prompting staled data) to resolve the conflict.
   
For example, assume the version column is named as `version`. You can implement optimistic locking with the code like
the following.

```php
// ------ view code -------

use yii\helpers\Html;

// ...other input fields
echo Html::activeHiddenInput($model, 'version');


// ------ controller code -------

use yii\db\StaleObjectException;

public function actionUpdate($id)
{
    $model = $this->findModel($id);

    try {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    } catch (StaleObjectException $e) {
        // logic to resolve the conflict
    }
}

// ------ model code -------

use yii\behaviors\OptimisticLockBehavior;

public function behaviors()
{
    return [
        OptimisticLockBehavior::class,
    ];
}
```


## Working with Relational Data <span id="relational-data"></span>

Besides working with individual database tables, Active Record is also capable of bringing together related data,
making them readily accessible through the primary data. For example, the customer data is related with the order
data because one customer may have placed one or multiple orders. With appropriate declaration of this relation,
you'll be able to access a customer's order information using the expression `$customer->orders` which gives
back the customer's order information in terms of an array of `Order` Active Record instances.


### Declaring Relations <span id="declaring-relations"></span>

To work with relational data using Active Record, you first need to declare relations in Active Record classes.
The task is as simple as declaring a *relation method* for every interested relation, like the following,

```php
class Customer extends ActiveRecord
{
    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    // ...

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
```

In the above code, we have declared an `orders` relation for the `Customer` class, and a `customer` relation
for the `Order` class. 

Each relation method must be named as `getXyz`. We call `xyz` (the first letter is in lower case) the *relation name*.
Note that relation names are *case sensitive*.

While declaring a relation, you should specify the following information:

- the multiplicity of the relation: specified by calling either [[yii\db\ActiveRecord::hasMany()|hasMany()]]
  or [[yii\db\ActiveRecord::hasOne()|hasOne()]]. In the above example you may easily read in the relation 
  declarations that a customer has many orders while an order only has one customer.
- the name of the related Active Record class: specified as the first parameter to 
  either [[yii\db\ActiveRecord::hasMany()|hasMany()]] or [[yii\db\ActiveRecord::hasOne()|hasOne()]].
  A recommended practice is to use the `Xyz::class` syntax provided by PHP to get the class name string so that you can receive
  IDE auto-completion support as well as error detection using static analysis tools.
  Using the `::class` syntax does not trigger auto loading, so this does not violate the lazy loading approach.
- the link between the two types of data: specifies the column(s) through which the two types of data are related.
  The array values are the columns of the primary data (represented by the Active Record class that you are declaring
  relations), while the array keys are the columns of the related data.

  An easy rule to remember this is, as you see in the example above, you write the column that belongs to the related
  Active Record directly next to it. You see there that `customer_id` is a property of `Order` and `id` is a property
  of `Customer`.
  

### Accessing Relational Data <span id="accessing-relational-data"></span>

After declaring relations, you can access relational data through relation names. This is just like accessing
an object [property](concept-properties.md) defined by the relation method. For this reason, we call it *relation property*.
For example,

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
// $orders is an array of Order objects
$orders = $customer->orders;
```

> Info: When you declare a relation named `xyz` via a getter method `getXyz()`, you will be able to access
  `xyz` like an [object property](concept-properties.md). Note that the name is case sensitive.
  
If a relation is declared with [[yii\db\ActiveRecord::hasMany()|hasMany()]], accessing this relation property
will return an array of the related Active Record instances; if a relation is declared with 
[[yii\db\ActiveRecord::hasOne()|hasOne()]], accessing the relation property will return the related
Active Record instance or `null` if no related data is found.

When you access a relation property for the first time, a SQL statement will be executed, like shown in the
above example. If the same property is accessed again, the previous result will be returned without re-executing
the SQL statement. To force re-executing the SQL statement, you should unset the relation property
first: `unset($customer->orders)`.

> Note: While this concept looks similar to the [object property](concept-properties.md) feature, there is an
> important difference. For normal object properties the property value is of the same type as the defining getter method.
> A relation method however returns an [[yii\db\ActiveQuery]] instance, while accessing a relation property will either
> return a [[yii\db\ActiveRecord]] instance or an array of these.
> 
> ```php
> $customer->orders; // is an array of `Order` objects
> $customer->getOrders(); // returns an ActiveQuery instance
> ```
> 
> This is useful for creating customized queries, which is described in the next section.


### Dynamic Relational Query <span id="dynamic-relational-query"></span>

Because a relation method returns an instance of [[yii\db\ActiveQuery]], you can further build this query
using query building methods before performing DB query. For example,

```php
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getOrders()
    ->where(['>', 'subtotal', 200])
    ->orderBy('id')
    ->all();
```

Unlike accessing a relation property, each time you perform a dynamic relational query via a relation method, 
a SQL statement will be executed, even if the same dynamic relational query was performed before.

Sometimes you may even want to parametrize a relation declaration so that you can more easily perform
dynamic relational query. For example, you may declare a `bigOrders` relation as follows, 

```php
class Customer extends ActiveRecord
{
    public function getBigOrders($threshold = 100)
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])
            ->where('subtotal > :threshold', [':threshold' => $threshold])
            ->orderBy('id');
    }
}
```

Then you will be able to perform the following relational queries:

```php
// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getBigOrders(200)->all();

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 100 ORDER BY `id`
$orders = $customer->bigOrders;
```


### Relations via a Junction Table <span id="junction-table"></span>

In database modelling, when the multiplicity between two related tables is many-to-many, 
a [junction table](https://en.wikipedia.org/wiki/Junction_table) is usually introduced. For example, the `order`
table and the `item` table may be related via a junction table named `order_item`. One order will then correspond
to multiple order items, while one product item will also correspond to multiple order items.

When declaring such relations, you would call either [[yii\db\ActiveQuery::via()|via()]] or [[yii\db\ActiveQuery::viaTable()|viaTable()]]
to specify the junction table. The difference between [[yii\db\ActiveQuery::via()|via()]] and [[yii\db\ActiveQuery::viaTable()|viaTable()]]
is that the former specifies the junction table in terms of an existing relation name while the latter directly uses
the junction table. For example,

```php
class Order extends ActiveRecord
{
    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->viaTable('order_item', ['order_id' => 'id']);
    }
}
```

or alternatively,

```php
class Order extends ActiveRecord
{
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems');
    }
}
```

The usage of relations declared with a junction table is the same as that of normal relations. For example,

```php
// SELECT * FROM `order` WHERE `id` = 100
$order = Order::findOne(100);

// SELECT * FROM `order_item` WHERE `order_id` = 100
// SELECT * FROM `item` WHERE `item_id` IN (...)
// returns an array of Item objects
$items = $order->items;
```


### Chaining relation definitions via multiple tables <span id="multi-table-relations"></span>

Its further possible to define relations via multiple tables by chaining relation definitions using [[yii\db\ActiveQuery::via()|via()]].
Considering the examples above, we have classes `Customer`, `Order`, and `Item`.
We can add a relation to the `Customer` class that lists all items from all the orders they placed,
and name it `getPurchasedItems()`, the chaining of relations is show in the following code example:

```php
class Customer extends ActiveRecord
{
    // ...

    public function getPurchasedItems()
    {
        // customer's items, matching 'id' column of `Item` to 'item_id' in OrderItem
        return $this->hasMany(Item::class, ['id' => 'item_id'])
                    ->via('orderItems');
    }

    public function getOrderItems()
    {
        // customer's order items, matching 'id' column of `Order` to 'order_id' in OrderItem
        return $this->hasMany(OrderItem::class, ['order_id' => 'id'])
                    ->via('orders');
    }

    public function getOrders()
    {
        // same as above
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```


### Lazy Loading and Eager Loading <span id="lazy-eager-loading"></span>

In [Accessing Relational Data](#accessing-relational-data), we explained that you can access a relation property
of an Active Record instance like accessing a normal object property. A SQL statement will be executed only when
you access the relation property the first time. We call such relational data accessing method *lazy loading*.
For example,

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$orders = $customer->orders;

// no SQL executed
$orders2 = $customer->orders;
```

Lazy loading is very convenient to use. However, it may suffer from a performance issue when you need to access
the same relation property of multiple Active Record instances. Consider the following code example. How many 
SQL statements will be executed?

```php
// SELECT * FROM `customer` LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SELECT * FROM `order` WHERE `customer_id` = ...
    $orders = $customer->orders;
}
```

As you can see from the code comment above, there are 101 SQL statements being executed! This is because each
time you access the `orders` relation property of a different `Customer` object in the for-loop, a SQL statement 
will be executed.

To solve this performance problem, you can use the so-called *eager loading* approach as shown below,

```php
// SELECT * FROM `customer` LIMIT 100;
// SELECT * FROM `orders` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->with('orders')
    ->limit(100)
    ->all();

foreach ($customers as $customer) {
    // no SQL executed
    $orders = $customer->orders;
}
```

By calling [[yii\db\ActiveQuery::with()]], you instruct Active Record to bring back the orders for the first 100
customers in one single SQL statement. As a result, you reduce the number of the executed SQL statements from 101 to 2!

You can eagerly load one or multiple relations. You can even eagerly load *nested relations*. A nested relation is a relation
that is declared within a related Active Record class. For example, `Customer` is related with `Order` through the `orders`
relation, and `Order` is related with `Item` through the `items` relation. When querying for `Customer`, you can eagerly
load `items` using the nested relation notation `orders.items`. 

The following code shows different usage of [[yii\db\ActiveQuery::with()|with()]]. We assume the `Customer` class
has two relations `orders` and `country`, while the `Order` class has one relation `items`.

```php
// eager loading both "orders" and "country"
$customers = Customer::find()->with('orders', 'country')->all();
// equivalent to the array syntax below
$customers = Customer::find()->with(['orders', 'country'])->all();
// no SQL executed 
$orders= $customers[0]->orders;
// no SQL executed 
$country = $customers[0]->country;

// eager loading "orders" and the nested relation "orders.items"
$customers = Customer::find()->with('orders.items')->all();
// access the items of the first order of the first customer
// no SQL executed
$items = $customers[0]->orders[0]->items;
```

You can eagerly load deeply nested relations, such as `a.b.c.d`. All parent relations will be eagerly loaded.
That is, when you call [[yii\db\ActiveQuery::with()|with()]] using `a.b.c.d`, you will eagerly load
`a`, `a.b`, `a.b.c` and `a.b.c.d`.  

> Info: In general, when eagerly loading `N` relations among which `M` relations are defined with a 
  [junction table](#junction-table), a total number of `N+M+1` SQL statements will be executed.
  Note that a nested relation `a.b.c.d` counts as 4 relations.

When eagerly loading a relation, you can customize the corresponding relational query using an anonymous function.
For example,

```php
// find customers and bring back together their country and active orders
// SELECT * FROM `customer`
// SELECT * FROM `country` WHERE `id` IN (...)
// SELECT * FROM `order` WHERE `customer_id` IN (...) AND `status` = 1
$customers = Customer::find()->with([
    'country',
    'orders' => function ($query) {
        $query->andWhere(['status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

When customizing the relational query for a relation, you should specify the relation name as an array key
and use an anonymous function as the corresponding array value. The anonymous function will receive a `$query` parameter
which represents the [[yii\db\ActiveQuery]] object used to perform the relational query for the relation.
In the code example above, we are modifying the relational query by appending an additional condition about order status.

> Note: If you call [[yii\db\Query::select()|select()]] while eagerly loading relations, you have to make sure
> the columns referenced in the relation declarations are being selected. Otherwise, the related models may not 
> be loaded properly. For example,
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // $orders[0]->customer is always `null`. To fix the problem, you should do the following:
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### Joining with Relations <span id="joining-with-relations"></span>

> Note: The content described in this subsection is only applicable to relational databases, such as
  MySQL, PostgreSQL, etc.

The relational queries that we have described so far only reference the primary table columns when
querying for the primary data. In reality we often need to reference columns in the related tables. For example,
we may want to bring back the customers who have at least one active order. To solve this problem, we can
build a join query like the following:

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id`
// WHERE `order`.`status` = 1
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->select('customer.*')
    ->leftJoin('order', '`order`.`customer_id` = `customer`.`id`')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->with('orders')
    ->all();
```

> Note: It is important to disambiguate column names when building relational queries involving JOIN SQL statements.
  A common practice is to prefix column names with their corresponding table names.

However, a better approach is to exploit the existing relation declarations by calling [[yii\db\ActiveQuery::joinWith()]]:

```php
$customers = Customer::find()
    ->joinWith('orders')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->all();
```

Both approaches execute the same set of SQL statements. The latter approach is much cleaner and drier, though. 

By default, [[yii\db\ActiveQuery::joinWith()|joinWith()]] will use `LEFT JOIN` to join the primary table with the 
related table. You can specify a different join type (e.g. `RIGHT JOIN`) via its third parameter `$joinType`. If
the join type you want is `INNER JOIN`, you can simply call [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]], instead.

Calling [[yii\db\ActiveQuery::joinWith()|joinWith()]] will [eagerly load](#lazy-eager-loading) the related data by default.
If you do not want to bring in the related data, you can specify its second parameter `$eagerLoading` as `false`. 

> Note: Even when using [[yii\db\ActiveQuery::joinWith()|joinWith()]] or [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]]
  with eager loading enabled the related data will **not** be populated from the result of the `JOIN` query. So there's
  still an extra query for each joined relation as explained in the section on [eager loading](#lazy-eager-loading).

Like [[yii\db\ActiveQuery::with()|with()]], you can join with one or multiple relations; you may customize the relation
queries on-the-fly; you may join with nested relations; and you may mix the use of [[yii\db\ActiveQuery::with()|with()]]
and [[yii\db\ActiveQuery::joinWith()|joinWith()]]. For example,

```php
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->andWhere(['>', 'subtotal', 100]);
    },
])->with('country')
    ->all();
```

Sometimes when joining two tables, you may need to specify some extra conditions in the `ON` part of the JOIN query.
This can be done by calling the [[yii\db\ActiveQuery::onCondition()]] method like the following:

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id` AND `order`.`status` = 1 
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->onCondition(['order.status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

This above query brings back *all* customers, and for each customer it brings back all active orders.
Note that this differs from our earlier example which only brings back customers who have at least one active order.

> Info: When [[yii\db\ActiveQuery]] is specified with a condition via [[yii\db\ActiveQuery::onCondition()|onCondition()]],
  the condition will be put in the `ON` part if the query involves a JOIN query. If the query does not involve
  JOIN, the on-condition will be automatically appended to the `WHERE` part of the query.
  Thus it may only contain conditions including columns of the related table.

#### Relation table aliases <span id="relation-table-aliases"></span>

As noted before, when using JOIN in a query, we need to disambiguate column names. Therefor often an alias is
defined for a table. Setting an alias for the relational query would be possible by customizing the relation query in the following way:

```php
$query->joinWith([
    'orders' => function ($q) {
        $q->from(['o' => Order::tableName()]);
    },
])
```

This however looks very complicated and involves either hardcoding the related objects table name or calling `Order::tableName()`.
Since version 2.0.7, Yii provides a shortcut for this. You may now define and use the alias for the relation table like the following:

```php
// join the orders relation and sort the result by orders.id
$query->joinWith(['orders o'])->orderBy('o.id');
```

The above syntax works for simple relations. If you need an alias for an intermediate table when joining over
nested relations, e.g. `$query->joinWith(['orders.product'])`,
you need to nest the joinWith calls like in the following example:

```php
$query->joinWith(['orders o' => function($q) {
        $q->joinWith('product p');
    }])
    ->where('o.amount > 100');
```

### Inverse Relations <span id="inverse-relations"></span>

Relation declarations are often reciprocal between two Active Record classes. For example, `Customer` is related 
to `Order` via the `orders` relation, and `Order` is related back to `Customer` via the `customer` relation.

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
```

Now consider the following piece of code:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// SELECT * FROM `customer` WHERE `id` = 123
$customer2 = $order->customer;

// displays "not the same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

We would think `$customer` and `$customer2` are the same, but they are not! Actually they do contain the same
customer data, but they are different objects. When accessing `$order->customer`, an extra SQL statement
is executed to populate a new object `$customer2`.

To avoid the redundant execution of the last SQL statement in the above example, we should tell Yii that
`customer` is an *inverse relation* of `orders` by calling the [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] method
like shown below:

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

With this modified relation declaration, we will have:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// No SQL will be executed
$customer2 = $order->customer;

// displays "same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

> Note: Inverse relations cannot be defined for relations involving a [junction table](#junction-table).
  That is, if a relation is defined with [[yii\db\ActiveQuery::via()|via()]] or [[yii\db\ActiveQuery::viaTable()|viaTable()]],
  you should not call [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] further.


## Saving Relations <span id="saving-relations"></span>

When working with relational data, you often need to establish relationships between different data or destroy
existing relationships. This requires setting proper values for the columns that define the relations. Using Active Record,
you may end up writing the code like the following:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

// setting the attribute that defines the "customer" relation in Order
$order->customer_id = $customer->id;
$order->save();
```

Active Record provides the [[yii\db\ActiveRecord::link()|link()]] method that allows you to accomplish this task more nicely:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

$order->link('customer', $customer);
```

The [[yii\db\ActiveRecord::link()|link()]] method requires you to specify the relation name and the target Active Record
instance that the relationship should be established with. The method will modify the values of the attributes that
link two Active Record instances and save them to the database. In the above example, it will set the `customer_id`
attribute of the `Order` instance to be the value of the `id` attribute of the `Customer` instance and then save it
to the database.

> Note: You cannot link two newly created Active Record instances.

The benefit of using [[yii\db\ActiveRecord::link()|link()]] is even more obvious when a relation is defined via
a [junction table](#junction-table). For example, you may use the following code to link an `Order` instance
with an `Item` instance:

```php
$order->link('items', $item);
```

The above code will automatically insert a row in the `order_item` junction table to relate the order with the item.

> Info: The [[yii\db\ActiveRecord::link()|link()]] method will NOT perform any data validation while
  saving the affected Active Record instance. It is your responsibility to validate any input data before
  calling this method.

The opposite operation to [[yii\db\ActiveRecord::link()|link()]] is [[yii\db\ActiveRecord::unlink()|unlink()]]
which breaks an existing relationship between two Active Record instances. For example,

```php
$customer = Customer::find()->with('orders')->where(['id' => 123])->one();
$customer->unlink('orders', $customer->orders[0]);
```

By default, the [[yii\db\ActiveRecord::unlink()|unlink()]] method will set the foreign key value(s) that specify
the existing relationship to be `null`. You may, however, choose to delete the table row that contains the foreign key value
by passing the `$delete` parameter as `true` to the method.
 
When a junction table is involved in a relation, calling [[yii\db\ActiveRecord::unlink()|unlink()]] will cause
the foreign keys in the junction table to be cleared, or the deletion of the corresponding row in the junction table
if `$delete` is `true`.


## Cross-Database Relations <span id="cross-database-relations"></span> 

Active Record allows you to declare relations between Active Record classes that are powered by different databases.
The databases can be of different types (e.g. MySQL and PostgreSQL, or MS SQL and MongoDB), and they can run on 
different servers. You can use the same syntax to perform relational queries. For example,

```php
// Customer is associated with the "customer" table in a relational database (e.g. MySQL)
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // a customer has many comments
        return $this->hasMany(Comment::class, ['customer_id' => 'id']);
    }
}

// Comment is associated with the "comment" collection in a MongoDB database
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // a comment has one customer
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}

$customers = Customer::find()->with('comments')->all();
```

You can use most of the relational query features that have been described in this section. 
 
> Note: Usage of [[yii\db\ActiveQuery::joinWith()|joinWith()]] is limited to databases that allow cross-database JOIN queries.
  For this reason, you cannot use this method in the above example because MongoDB does not support JOIN.


## Customizing Query Classes <span id="customizing-query-classes"></span>

By default, all Active Record queries are supported by [[yii\db\ActiveQuery]]. To use a customized query class
in an Active Record class, you should override the [[yii\db\ActiveRecord::find()]] method and return an instance
of your customized query class. For example,

```php
// file Comment.php
namespace app\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}
```

Now whenever you are performing a query (e.g. `find()`, `findOne()`) or defining a relation (e.g. `hasOne()`)
with `Comment`, you will be calling an instance of `CommentQuery` instead of `ActiveQuery`.

You now have to define the `CommentQuery` class, which can be customized in many creative ways to improve your query building experience. For example,

```php
// file CommentQuery.php
namespace app\models;

use yii\db\ActiveQuery;

class CommentQuery extends ActiveQuery
{
    // conditions appended by default (can be skipped)
    public function init()
    {
        $this->andOnCondition(['deleted' => false]);
        parent::init();
    }

    // ... add customized query methods here ...

    public function active($state = true)
    {
        return $this->andOnCondition(['active' => $state]);
    }
}
```

> Note: Instead of calling [[yii\db\ActiveQuery::onCondition()|onCondition()]], you usually should call
  [[yii\db\ActiveQuery::andOnCondition()|andOnCondition()]] or [[yii\db\ActiveQuery::orOnCondition()|orOnCondition()]]
  to append additional conditions when defining new query building methods so that any existing conditions are not overwritten.

This allows you to write query building code like the following:

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

> Tip: In big projects, it is recommended that you use customized query classes to hold most query-related code
  so that the Active Record classes can be kept clean.

You can also use the new query building methods when defining relations about `Comment` or performing relational query:

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::class, ['customer_id' => 'id'])->active();
    }
}

$customers = Customer::find()->joinWith('activeComments')->all();

// or alternatively
class Customer extends \yii\db\ActiveRecord
{
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['customer_id' => 'id']);
    }
}

$customers = Customer::find()->joinWith([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

> Info: In Yii 1.1, there is a concept called *scope*. Scope is no longer directly supported in Yii 2.0,
  and you should use customized query classes and query methods to achieve the same goal.


## Selecting extra fields

When Active Record instance is populated from query results, its attributes are filled up by corresponding column
values from received data set.

You are able to fetch additional columns or values from query and store it inside the Active Record.
For example, assume we have a table named `room`, which contains information about rooms available in the hotel.
Each room stores information about its geometrical size using fields `length`, `width`, `height`.
Imagine we need to retrieve list of all available rooms with their volume in descendant order.
So you can not calculate volume using PHP, because we need to sort the records by its value, but you also want `volume`
to be displayed in the list.
To achieve the goal, you need to declare an extra field in your `Room` Active Record class, which will store `volume` value:

```php
class Room extends \yii\db\ActiveRecord
{
    public $volume;

    // ...
}
```

Then you need to compose a query, which calculates volume of the room and performs the sort:

```php
$rooms = Room::find()
    ->select([
        '{{room}}.*', // select all columns
        '([[length]] * [[width]] * [[height]]) AS volume', // calculate a volume
    ])
    ->orderBy('volume DESC') // apply sort
    ->all();

foreach ($rooms as $room) {
    echo $room->volume; // contains value calculated by SQL
}
```

Ability to select extra fields can be exceptionally useful for aggregation queries.
Assume you need to display a list of customers with the count of orders they have made.
First of all, you need to declare a `Customer` class with `orders` relation and extra field for count storage:

```php
class Customer extends \yii\db\ActiveRecord
{
    public $ordersCount;

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```

Then you can compose a query, which joins the orders and calculates their count:

```php
$customers = Customer::find()
    ->select([
        '{{customer}}.*', // select all customer fields
        'COUNT({{order}}.id) AS ordersCount' // calculate orders count
    ])
    ->joinWith('orders') // ensure table junction
    ->groupBy('{{customer}}.id') // group the result to ensure aggregation function works
    ->all();
```

A disadvantage of using this method would be that, if the information isn't loaded on the SQL query - it has to be calculated
separately. Thus, if you have found particular record via regular query without extra select statements, it
will be unable to return actual value for the extra field. Same will happen for the newly saved record.

```php
$room = new Room();
$room->length = 100;
$room->width = 50;
$room->height = 2;

$room->volume; // this value will be `null`, since it was not declared yet
```

Using the [[yii\db\BaseActiveRecord::__get()|__get()]] and [[yii\db\BaseActiveRecord::__set()|__set()]] magic methods
we can emulate the behavior of a property:

```php
class Room extends \yii\db\ActiveRecord
{
    private $_volume;
    
    public function setVolume($volume)
    {
        $this->_volume = (float) $volume;
    }
    
    public function getVolume()
    {
        if (empty($this->length) || empty($this->width) || empty($this->height)) {
            return null;
        }
        
        if ($this->_volume === null) {
            $this->setVolume(
                $this->length * $this->width * $this->height
            );
        }
        
        return $this->_volume;
    }

    // ...
}
```

When the select query doesn't provide the volume, the model will be able to calculate it automatically using
the attributes of the model.

You can calculate the aggregation fields as well using defined relations:

```php
class Customer extends \yii\db\ActiveRecord
{
    private $_ordersCount;

    public function setOrdersCount($count)
    {
        $this->_ordersCount = (int) $count;
    }

    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; // this avoid calling a query searching for null primary keys
        }

        if ($this->_ordersCount === null) {
            $this->setOrdersCount($this->getOrders()->count()); // calculate aggregation on demand from relation
        }

        return $this->_ordersCount;
    }

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```

With this code, in case 'ordersCount' is present in 'select' statement - `Customer::ordersCount` will be populated
by query results, otherwise it will be calculated on demand using `Customer::orders` relation.

This approach can be as well used for creation of the shortcuts for some relational data, especially for the aggregation.
For example:

```php
class Customer extends \yii\db\ActiveRecord
{
    /**
     * Defines read-only virtual property for aggregation data.
     */
    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; // this avoid calling a query searching for null primary keys
        }
        
        return empty($this->ordersAggregation) ? 0 : $this->ordersAggregation[0]['counted'];
    }

    /**
     * Declares normal 'orders' relation.
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

    /**
     * Declares new relation based on 'orders', which provides aggregation.
     */
    public function getOrdersAggregation()
    {
        return $this->getOrders()
            ->select(['customer_id', 'counted' => 'count(*)'])
            ->groupBy('customer_id')
            ->asArray(true);
    }

    // ...
}

foreach (Customer::find()->with('ordersAggregation')->all() as $customer) {
    echo $customer->ordersCount; // outputs aggregation data from relation without extra query due to eager loading
}

$customer = Customer::findOne($pk);
$customer->ordersCount; // output aggregation data from lazy loaded relation
```
