アクティブレコード
==================

> Note|注意: この節はまだ執筆中です。

[アクティブレコード](http://ja.wikipedia.org/wiki/Active_Record) は、データベースに保存されているデータにアクセスするために、オブジェクト指向のインタフェイスを提供するものです。
アクティブレコードクラスはデータベーステーブルと関連付けられて、アクティブレコードのインスタンスがそのテーブルの行に対応し、アクティブレコードのインスタンスの属性がその行のカラムの値を表現します。
生の SQL 文を書く代りに、アクティブレコードを使って、オブジェクト指向の流儀でデータベーステーブルのデータを操作することが出来ます。

例えば、`Customer` が `customer` テーブルに関連付けられたアクティブレコードクラスであり、`name` が `customer` テーブルのカラムであると仮定しましょう。
`customer` テーブルに新しい行を挿入するために次のコードを書くことが出来ます。

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

上記のコードは、次のように生の SQL 文を使うのと等価なものですが、生の SQL 文の方は、直感的でなく、間違いも生じやすく、また、DBMS の違いによる互換性の問題も生じ得ます。

```php
$db->createCommand('INSERT INTO customer (name) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

下記が、現在 Yii のアクティブレコードによってサポートされているデータベースのリストです。

* MySQL 4.1 以降: [[yii\db\ActiveRecord]] による。
* PostgreSQL 7.3 以降: [[yii\db\ActiveRecord]] による。
* SQLite 2 および 3: [[yii\db\ActiveRecord]] による。
* Microsoft SQL Server 2010 以降: [[yii\db\ActiveRecord]] による。
* Oracle: [[yii\db\ActiveRecord]] による。
* CUBRID 9.3 以降: [[yii\db\ActiveRecord]] による。(cubrid PDO 拡張の [バグ](http://jira.cubrid.org/browse/APIS-658)
  のために、値を引用符で囲む機能が動作しません。そのため、サーバだけでなくクライアントも CUBRID 9.3 が必要になります)
* Sphnix: [[yii\sphinx\ActiveRecord]] による。`yii2-sphinx` エクステンションが必要。
* ElasticSearch: [[yii\elasticsearch\ActiveRecord]] による。`yii2-elasticsearch` エクステンションが必要。
* Redis 2.6.12 以降: [[yii\redis\ActiveRecord]] による。`yii2-redis` エクステンションが必要。
* MongoDB 1.3.0 以降: [[yii\mongodb\ActiveRecord]] による。`yii2-mongodb` エクステンションが必要。

ご覧のように、Yii はリレーショナルデータベースだけでなく NoSQL データベースに対してもアクティブレコードのサポートを提供しています。
このチュートリアルでは、主としてリレーショナルデータベースのためのアクティブレコードの使用方法を説明します。
しかし、ここで説明するほとんどの内容は NoSQL データベースのためのアクティブレコードにも適用することが出来るものです。


アクティブレコードクラスを宣言する
----------------------------------

アクティブレコードクラスを宣言するためには、[[yii\db\ActiveRecord]] を拡張して、クラスと関連付けられるデータベーステーブルの名前を返す `tableName` メソッドを実装する必要があります。

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DELETED = 'deleted';
    
    /**
     * @return string アクティブレコードクラスと関連付けられるデータベーステーブルの名前
     */
    public static function tableName()
    {
        return 'customer';
    }
}
```


カラムのデータにアクセスする
----------------------------

アクティブレコードは、対応するデータベーステーブルの行の各カラムをアクティブレコードオブジェクトの属性に割り付けます。
属性は通常のオブジェクトのパブリックなプロパティと同様の振る舞いをします。
属性の名前は対応するから無名と同じであり、大文字と小文字を区別します。

カラムの値を読み出すために、次の構文を使用することが出来ます。

```php
// "id" と "email" は、$customer アクティブレコードオブジェクトと関連付けられたテーブルのカラム名
$id = $customer->id;
$email = $customer->email;
```

カラムの値を変更するためには、関連付けられたプロパティに新しい値を代入して、オブジェクトを保存します。

```php
$customer->email = 'jane@example.com';
$customer->save();
```


データベースに接続する
----------------------

アクティブレコードは、データベースとの間でデータを交換するために [[yii\db\Connection|DB 接続]] を使用します。
既定では、アクティブレコードは `db` [アプリケーションコンポーネント](structure-application-components.md) を接続として使用します。
[データベースの基礎](db-dao.md) で説明したように、次のようにして、アプリケーションの構成情報ファイルの中で `db` コンポーネントを構成することが出来ます。

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

アプリケーションの中で複数のデータベースを使っており、アクティブレコードクラスのために異なる DB 接続を使いたい場合は、[[yii\db\ActiveRecord::getDb()|getDb()]] メソッドをオーバーライドすることが出来ます。

```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        return \Yii::$app->db2;  // "db2" アプリケーションコンポーネントを使用
    }
}
```


データベースにデータを問い合わせる
----------------------------------

アクティブレコードは、DB クエリを構築してアクティブレコードインスタンスにデータを投入するために、二つの入力メソッドを提供しています。

 - [[yii\db\ActiveRecord::find()]]
 - [[yii\db\ActiveRecord::findBySql()]]

この二つのメソッドは [[yii\db\ActiveQuery]] のインスタンスを返します。
 [[yii\db\ActiveQuery]] は [[yii\db\Query]] を拡張したものであり、従って、[[yii\db\Query]] と同じ一連の柔軟かつ強力な DB クエリ構築メソッド、例えば、`where()`、`join()`、`orderBy()` 等を提供します。
下記の例は、いくつかの可能性を示すものです。

```php
// *アクティブ* な顧客を全て読み出して、その ID によって並べ替える
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// ID が 1 である一人の顧客を返す
$customer = Customer::find()
    ->where(['id' => 1])
    ->one();

// *アクティブ* な顧客の数を返す
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// 結果を顧客 ID によってインデックスする
$customers = Customer::find()->indexBy('id')->all();
// $customers 配列は顧客 ID によってインデックスされる

// 生の SQL 文を使って顧客を読み出す
$sql = 'SELECT * FROM customer';
$customers = Customer::findBySql($sql)->all();
```

> Tip: In the code above `Customer::STATUS_ACTIVE` is a constant defined in `Customer`. It is a good practice to
  use meaningful constant names rather than hardcoded strings or numbers in your code.


Two shortcut methods are provided to return Active Record instances matching a primary key value or a set of
column values: `findOne()` and `findAll()`. The former returns the first matching instance while the latter
returns all of them. For example,

```php
// to return a single customer whose ID is 1:
$customer = Customer::findOne(1);

// to return an *active* customer whose ID is 1:
$customer = Customer::findOne([
    'id' => 1,
    'status' => Customer::STATUS_ACTIVE,
]);

// to return customers whose ID is 1, 2 or 3:
$customers = Customer::findAll([1, 2, 3]);

// to return customers whose status is "deleted":
$customer = Customer::findAll([
    'status' => Customer::STATUS_DELETED,
]);
```

> Note: By default neither `findOne()` nor `one()` will add `LIMIT 1` to the query. This is fine and preferred
  if you know the query will return only one or a few rows of data (e.g. if you are querying with some primary keys).
  However, if the query may potentially return many rows of data, you should call `limit(1)` to improve the performance.
  For example, `Customer::find()->where(['status' => Customer::STATUS_ACTIVE])->limit(1)->one()`.


### Retrieving Data in Arrays

Sometimes when you are processing a large amount of data, you may want to use arrays to hold the data
retrieved from database to save memory. This can be done by calling `asArray()`:

```php
// to return customers in terms of arrays rather than `Customer` objects:
$customers = Customer::find()
    ->asArray()
    ->all();
// each element of $customers is an array of name-value pairs
```

Note that while this method saves memory and improves performance it is a step to a lower abstraction
layer and you will loose some features that the active record layer has.
Fetching data using asArray is nearly equal to running a normal query using the [query builder](db-dao.md).
When using asArray the result will be returned as a simple array with no typecasting performed 
so the result may contain string values for fields that are integer when accessed on the active record object.

### Retrieving Data in Batches

In [Query Builder](db-query-builder.md), we have explained that you may use *batch query* to minimize your memory
usage when querying a large amount of data from the database. You may use the same technique
in Active Record. For example,

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


Manipulating Data in Database
-----------------------------

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
    $this->status = 'active';
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


Joining with Relations
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
   the lock version of the recording being updated.
3. In the controller action that does the data updating, try to catch the [[\yii\db\StaleObjectException]]
   and implement necessary business logic (e.g. merging the changes, prompting stated data)
   to resolve the conflict.

Dirty Attributes
--------------

An attribute is considered dirty if its value was modified after the model was loaded from database or since the most recent data save. When saving record data by calling `save()`, `update()`, `insert()` etc. only dirty attributes are saved into the database. If there are no dirty attributes then there is nothing to be saved so no query will be issued at all.

See also
--------

- [Model](structure-models.md)
- [[yii\db\ActiveRecord]]
