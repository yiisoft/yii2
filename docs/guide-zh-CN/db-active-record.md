Active Record
=============

> 注意：该章节还在开发中。

[Active Record](http://zh.wikipedia.org/wiki/Active_Record) （活动记录，以下简称AR）提供了一个面向对象的接口，
用以访问数据库中的数据。一个 AR 类关联一张数据表，
每个 AR 对象对应表中的一行，对象的属性（即 AR 的特性Attribute）映射到数据行的对应列。
一条活动记录（AR对象）对应数据表的一行，AR对象的属性则映射该行的相应列。
您可以直接以面向对象的方式来操纵数据表中的数据，妈妈再不用担心我需要写原生 SQL 语句啦。

例如，假定 `Customer` AR 类关联着 `customer` 表，且该类的 `name` 属性代表 `customer` 表的 `name` 列。
你可以写以下代码来哉 `customer` 表里插入一行新的记录:

用 AR 而不是原生的 SQL 语句去执行数据库查询，可以调用直观方法来实现相同目标。如，调用 [[yii\db\ActiveRecord::save()|save()]] 方法将执行插入或更新轮询，将在该 AR 类关联的数据表新建或更新一行数据：

```php
$customer = new Customer();
$customer->name = '李狗蛋';
$customer->save();  // 一行新数据插入 customer 表
```

上面的代码和使用下面的原生 SQL 语句是等效的，但显然前者更直观，
更不易出错，并且面对不同的数据库系统（DBMS, Database Management System）时更不容易产生兼容性问题。

```php
$db->createCommand('INSERT INTO customer (name) VALUES (:name)', [
    ':name' => '李狗蛋',
])->execute();
```

下面是所有目前被 Yii 的 AR 功能所支持的数据库列表：

* MySQL 4.1 及以上：通过 [[yii\db\ActiveRecord]]
* PostgreSQL 7.3 及以上：通过 [[yii\db\ActiveRecord]]
* SQLite 2 和 3：通过 [[yii\db\ActiveRecord]]
* Microsoft SQL Server 2010 及以上：通过 [[yii\db\ActiveRecord]]
* Oracle: 通过 [[yii\db\ActiveRecord]]
* CUBRID 9.1 及以上：通过 [[yii\db\ActiveRecord]]
* Sphinx：通过 [[yii\sphinx\ActiveRecord]]，需求 `yii2-sphinx` 扩展
* ElasticSearch：通过 [[yii\elasticsearch\ActiveRecord]]，需求 `yii2-elasticsearch` 扩展
* Redis 2.6.12 及以上：通过 [[yii\redis\ActiveRecord]]，需求 `yii2-redis` 扩展
* MongoDB 1.3.0 及以上：通过 [[yii\mongodb\ActiveRecord]]，需求 `yii2-mongodb` 扩展

如你所见，Yii 不仅提供了对关系型数据库的 AR 支持，还提供了 NoSQL 数据库的支持。
在这个教程中，我们会主要描述对关系型数据库的 AR 用法。
然而，绝大多数的内容在 NoSQL 的 AR 里同样适用。

声明 AR 类
------------------------------

要想声明一个 AR 类，你需要扩展 [[yii\db\ActiveRecord]] 基类，
并实现 `tableName` 方法，返回与之相关联的的数据表的名称：

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    /**
     * @return string 返回该AR类关联的数据表名
     */
    public static function tableName()
    {
        return 'customer';
    }
}
```

访问列数据
---------------------

AR 把相应数据行的每一个字段映射为 AR 对象的一个个特性变量（Attribute）
一个特性就好像一个普通对象的公共属性一样（public property）。
特性变量的名称和对应字段的名称是一样的，且大小姓名。

使用以下语法读取列的值：

```php
// "id" 和 "mail" 是 $customer 对象所关联的数据表的对应字段名
$id = $customer->id;
$email = $customer->email;
```

要改变列值，只要给关联属性赋新值并保存对象即可：

```php
$customer->email = '哪吒@example.com';
$customer->save();
```


建立数据库连接
----------------------

AR 用一个 [[yii\db\Connection|DB connection]] 对象与数据库交换数据。
默认的，它使用 `db` 组件作为其连接对象。详见[数据库基础](database-basics.md)章节，
你可以在应用程序配置文件中设置下 `db` 组件，就像这样，


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

如果在你的应用中应用了不止一个数据库，且你需要给你的 AR 类使用不同的数据库链接（DB connection）
，你可以覆盖掉 [[yii\db\ActiveRecord::getDb()|getDb()]] 方法：

```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        return \Yii::$app->db2;  // 使用名为 "db2" 的应用组件
    }
}
```

查询数据
---------------------------

AR 提供了两种方法来构建 DB 查询并向 AR 实例里填充数据：

 - [[yii\db\ActiveRecord::find()]]
 - [[yii\db\ActiveRecord::findBySql()]]

以上两个方法都会返回 [[yii\db\ActiveQuery]] 实例，该类继承自[[yii\db\Query]]，
因此，他们都支持同一套灵活且强大的 DB 查询方法，如 `where()`，`join()`，`orderBy()`，等等。 
下面的这些案例展示了一些可能的玩法：

```php
// 取回所有活跃客户(状态为 *active* 的客户）并以他们的 ID 排序：
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// 返回ID为1的客户：
$customer = Customer::find()
    ->where(['id' => 1])
    ->one();

// 取回活跃客户的数量：
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// 以客户ID索引结果集：
$customers = Customer::find()->indexBy('id')->all();
// $customers 数组以 ID 为索引

// 用原生 SQL 语句检索客户：
$sql = 'SELECT * FROM customer';
$customers = Customer::findBySql($sql)->all();
```

> 小技巧：在上面的代码中，`Customer::STATUS_ACTIVE` 是一个在 `Customer` 类里定义的常量。（译注：这种常量的值一般都是tinyint）相较于直接在代码中写死字符串或数字，使用一个更有意义的常量名称是一种更好的编程习惯。

有两个快捷方法：`findOne` 和 `findAll()` 用来返回一个或者一组`ActiveRecord`实例。前者返回第一个匹配到的实例，后者返回所有。
例如：

```php
// 返回 id 为 1 的客户
$customer = Customer::findOne(1);

// 返回 id 为 1 且状态为 *active* 的客户
$customer = Customer::findOne([
    'id' => 1,
    'status' => Customer::STATUS_ACTIVE,
]);

// 返回id为1、2、3的一组客户
$customers = Customer::findAll([1, 2, 3]);

// 返回所有状态为 "deleted" 的客户
$customer = Customer::findAll([
    'status' => Customer::STATUS_DELETED,
]);
```

### 以数组形式获取数据

有时候，我们需要处理很大量的数据，这时可能需要用一个数组来存储取到的数据，
从而节省内存。你可以用 `asArray()` 函数做到这一点：

```php
// 以数组而不是对象形式取回客户信息：
$customers = Customer::find()
    ->asArray()
    ->all();
// $customers 的每个元素都是键值对数组
```


### 批量获取数据

在 [Query Builder（查询构造器）](query-builder.md) 里，我们已经解释了当需要从数据库中查询大量数据时，你可以用 *batch query（批量查询）*来限制内存的占用。
你可能也想在 AR 里使用相同的技巧，比如这样……

```php
// 一次提取 10 个客户信息
foreach (Customer::find()->batch(10) as $customers) {
    // $customers 是 10 个或更少的客户对象的数组
}
// 一次提取 10 个客户并一个一个地遍历处理
foreach (Customer::find()->each(10) as $customer) {
    // $customer 是一个 ”Customer“ 对象
}
// 贪婪加载模式的批处理查询
foreach (Customer::find()->with('orders')->each() as $customer) {
}
```


操作数据
-----------------------------

AR 提供以下方法插入、更新和删除与 AR 对象关联的那张表中的某一行：

- [[yii\db\ActiveRecord::save()|save()]]
- [[yii\db\ActiveRecord::insert()|insert()]]
- [[yii\db\ActiveRecord::update()|update()]]
- [[yii\db\ActiveRecord::delete()|delete()]]

AR 同时提供了一下静态方法，可以应用在与某 AR 类所关联的整张表上。
用这些方法的时候千万要小心，因为他们作用于整张表！
比如，`deleteAll()`  会删除掉表里**所有**的记录。

- [[yii\db\ActiveRecord::updateCounters()|updateCounters()]]
- [[yii\db\ActiveRecord::updateAll()|updateAll()]]
- [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]]
- [[yii\db\ActiveRecord::deleteAll()|deleteAll()]]


下面的这些例子里，详细展现了如何使用这些方法：

```php
// 插入新客户的记录
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();  // 等同于 $customer->insert();

// 更新现有客户记录
$customer = Customer::findOne($id);
$customer->email = 'james@example.com';
$customer->save();  // 等同于 $customer->update();

// 删除已有客户记录
$customer = Customer::findOne($id);
$customer->delete();

// 删除多个年龄大于20，性别为男（Male）的客户记录
Customer::deleteAll('age > :age AND gender = :gender', [':age' => 20, ':gender' => 'M']);

// 所有客户的age（年龄）字段加1：
Customer::updateAllCounters(['age' => 1]);
```

> 须知：`save()` 方法会调用 `insert()` 和 `update()` 中的一个，
> 用哪个取决于当前 AR 对象是不是新对象（在函数内部，他会检查 [[yii\db\ActiveRecord::isNewRecord]] 的值）。
> 若 AR 对象是由 `new` 操作符 初始化出来的，`save()` 方法会在表里*插入*一条数据；
> 如果一个 AR 是由 `find()` 方法获取来的，
> 则 `save()` 会*更新*表里的对应行记录。


### 数据输入与有效性验证

由于AR继承自[[yii\base\Model]]，所以它同样也支持[Model](model.md)的数据输入、验证等特性。例如，你可以声明一个rules方法用来覆盖掉[[yii\base\Model::rules()|rules()]]里的；你也可以给AR实例批量赋值；你也可以通过调用[[yii\base\Model::validate()|validate()]]执行数据验证。

当你调用 `save()`、`insert()`、`update()` 这三个方法时，会自动调用[[yii\base\Model::validate()|validate()]]方法。如果验证失败，数据将不会保存进数据库。

下面的例子演示了如何使用AR 获取/验证用户输入的数据并将他们保存进数据库：

```php
// 新建一条记录
$model = new Customer;
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    // 获取用户输入的数据，验证并保存
}

// 更新主键为$id的AR
$model = Customer::findOne($id);
if ($model === null) {
    throw new NotFoundHttpException;
}
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    // 获取用户输入的数据，验证并保存
}
```


### 读取默认值

你的表列也许定义了默认值。有时候，你可能需要在使用web表单的时候给AR预设一些值。如果你需要这样做，可以在显示表单内容前通过调用`loadDefaultValues()`方法来实现：
```php
$customer = new Customer();
$customer->loadDefaultValues();
// ... 渲染 $customer 的 HTML 表单 ...
```


AR的生命周期
-----------------------------

理解AR的生命周期对于你操作数据库非常重要。生命周期通常都会有些典型的事件存在。对于开发AR的behaviors来说非常有用。

当你实例化一个新的AR对象时，我们将获得如下的生命周期：

1. constructor
2. [[yii\db\ActiveRecord::init()|init()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] 事件

当你通过 [[yii\db\ActiveRecord::find()|find()]] 方法查询数据时，每个AR实例都将有以下生命周期：

1. constructor
2. [[yii\db\ActiveRecord::init()|init()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] 事件
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] 事件

当通过 [[yii\db\ActiveRecord::save()|save()]] 方法写入或者更新数据时, 我们将获得如下生命周期：

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] 事件
2. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]] 事件
3. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] 或 [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]] 事件
4. 执行实际的数据写入或更新
5. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] 或 [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]] 事件

最后，当调用 [[yii\db\ActiveRecord::delete()|delete()]] 删除数据时, 我们将获得如下生命周期：

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]] 事件
2. 执行实际的数据删除
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: 会触发一个 [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]] 事件


查询关联的数据
-----------------------------

使用 AR 方法也可以查询数据表的关联数据（如，选出表A的数据可以拉出表B的关联数据）。
有了 AR，
返回的关联数据连接就像连接关联主表的 AR 对象的属性一样。

建立关联关系后，通过 `$customer->orders` 可以获取
一个 `Order` 对象的数组，该数组代表当前客户对象的订单集。

定义关联关系使用一个可以返回 [[yii\db\ActiveQuery]] 对象的 getter 方法，
[[yii\db\ActiveQuery]]对象有关联上下文的相关信息，因此可以只查询关联数据。

例如：

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        // 客户和订单通过 Order.customer_id -> id 关联建立一对多关系
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends \yii\db\ActiveRecord
{
    // 订单和客户通过 Customer.id -> customer_id 关联建立一对一关系
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

以上使用了 [[yii\db\ActiveRecord::hasMany()]] 和 [[yii\db\ActiveRecord::hasOne()]] 方法。
以上两例分别是关联数据多对一关系和一对一关系的建模范例。
如，一个客户有很多订单，一个订单只归属一个客户。
两个方法都有两个参数并返回 [[yii\db\ActiveQuery]] 对象。

 - `$class`：关联模型类名，它必须是一个完全合格的类名。
 - `$link`: 两个表的关联列，应为键值对数组的形式。
   数组的键是 `$class` 关联表的列名，
   而数组值是关联类 $class 的列名。
   基于表外键定义关联关系是最佳方法。

建立关联关系后，获取关联数据和获取组件属性一样简单，
执行以下相应getter方法即可：

```php
// 取得客户的订单
$customer = Customer::findOne(1);
$orders = $customer->orders; // $orders 是 Order 对象数组
```

以上代码实际执行了以下两条 SQL 语句：

```sql
SELECT * FROM customer WHERE id=1;
SELECT * FROM order WHERE customer_id=1;
```

> 提示:再次用表达式 `$customer->orders`将不会执行第二次 SQL 查询，
SQL 查询只在该表达式第一次使用时执行。
数据库访问只返回缓存在内部前一次取回的结果集，如果你想查询新的
关联数据，先要注销现有结果集：`unset($customer->orders);`。

有时候需要在关联查询中传递参数，如不需要返回客户全部订单，
只需要返回购买金额超过设定值的大订单，
通过以下getter方法声明一个关联数据 `bigOrders` ：

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

`hasMany()` 返回 [[yii\db\ActiveQuery]] 对象，该对象允许你通过
[[yii\db\ActiveQuery]] 方法定制查询。

如上声明后，执行`$customer->bigOrders` 就返回
总额大于100的订单。使用以下代码更改设定值：

```php
$orders = $customer->getBigOrders(200)->all();
```

>注意：关联查询返回的是 [[yii\db\ActiveQuery]] 的实例，如果像特性（如类属性）那样连接关联数据，
返回的结果是关联查询的结果，即 [[yii\db\ActiveRecord]] 的实例，
或者是数组，或者是 null ，取决于关联关系的多样性。如，`$customer->getOrders()` 返回
`ActiveQuery` 实例，而 `$customer->orders` 返回`Order` 对象数组
（如果查询结果为空则返回空数组）。


中间关联表
-----------------

有时，两个表通过中间表关联，定义这样的关联关系， 可以通过调用 [[yii\db\ActiveQuery::via()|via()]] 方法或 [[yii\db\ActiveQuery::viaTable()|viaTable()]] 方法来定制 [[yii\db\ActiveQuery]] 对象 。

举例而言，如果 `order` 表和 `item` 表通过中间表 `order_item` 关联起来， 可以在 `Order` 类声明 `items` 关联关系取代中间表：

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

两个方法是相似的，除了 [[yii\db\ActiveQuery::via()|via()]] 方法的第一个参数是使用 AR 类中定义的关联名。 以上方法取代了中间表，等价于：

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

延迟加载和即时加载（又称惰性加载与贪婪加载）
-----------------

如前所述，当你第一次连接关联对象时， AR 将执行一个数据库查询 来检索请求数据并填充到关联对象的相应属性。 如果再次连接相同的关联对象，不再执行任何查询语句，这种数据库查询的执行方法称为“延迟加载”。如：

```php
// SQL executed: SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// SQL executed: SELECT * FROM order WHERE customer_id=1
$orders = $customer->orders;
// 没有 SQL 语句被执行
$orders2 = $customer->orders; //取回上次查询的缓存数据
```

延迟加载非常实用，但是，在以下场景中使用延迟加载会遭遇性能问题：

```php
// SQL executed: SELECT * FROM customer LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SQL executed: SELECT * FROM order WHERE customer_id=...
    $orders = $customer->orders;
    // ...处理 $orders...
}
```

假设数据库查出的客户超过100个，以上代码将执行多少条 SQL 语句？ 101 条！第一条 SQL 查询语句取回100个客户，然后， 每个客户要执行一条 SQL 查询语句以取回该客户的所有订单。

为解决以上性能问题，可以通过调用 [[yii\db\ActiveQuery::with()]] 方法使用即时加载解决。

```php
// SQL executed: SELECT * FROM customer LIMIT 100;
//               SELECT * FROM orders WHERE customer_id IN (1,2,...)
$customers = Customer::find()->limit(100)
    ->with('orders')->all();

foreach ($customers as $customer) {
    // 没有 SQL 语句被执行
    $orders = $customer->orders;
    // ...处理 $orders...
}
```

如你所见，同样的任务只需要两个 SQL 语句。
>须知：通常，即时加载 N 个关联关系而通过 via() 或者 viaTable() 定义了 M 个关联关系， 将有 1+M+N 条 SQL 查询语句被执行：一个查询取回主表行数， 一个查询给每一个 (M) 中间表，一个查询给每个 (N) 关联表。
注意:当用即时加载定制 select() 时，确保连接 到关联模型的列都被包括了，否则，关联模型不会载入。如：

```php
$orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
// $orders[0]->customer 总是空的，使用以下代码解决这个问题：
$orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
```

有时候，你想自由的自定义关联查询，延迟加载和即时加载都可以实现，如：

```php
$customer = Customer::findOne(1);
// 延迟加载: SELECT * FROM order WHERE customer_id=1 AND subtotal>100
$orders = $customer->getOrders()->where('subtotal>100')->all();

// 即时加载: SELECT * FROM customer LIMIT 100
//          SELECT * FROM order WHERE customer_id IN (1,2,...) AND subtotal>100
$customers = Customer::find()->limit(100)->with([
    'orders' => function($query) {
        $query->andWhere('subtotal>100');
    },
])->all();
```

逆关系
-----------------

关联关系通常成对定义，如：Customer 可以有个名为 orders 关联项， 而 Order 也有个名为customer 的关联项：

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

如果我们执行以下查询，可以发现订单的 customer 和 找到这些订单的客户对象并不是同一个。连接 customer->orders 将触发一条 SQL 语句 而连接一个订单的 customer 将触发另一条 SQL 语句。

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// 输出 "不相同"
// SELECT * FROM order WHERE customer_id=1
// SELECT * FROM customer WHERE id=1
if ($customer->orders[0]->customer === $customer) {
    echo '相同';
} else {
    echo '不相同';
}
```

为避免多余执行的后一条语句，我们可以为 customer或 orders 关联关系定义相反的关联关系，通过调用 [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] 方法可以实现。

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

现在我们同样执行上面的查询，我们将得到：

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// 输出相同
// SELECT * FROM order WHERE customer_id=1
if ($customer->orders[0]->customer === $customer) {
    echo '相同';
} else {
    echo '不相同';
}
```

以上我们展示了如何在延迟加载中使用相对关联关系， 相对关系也可以用在即时加载中：

```php
// SELECT * FROM customer
// SELECT * FROM order WHERE customer_id IN (1, 2, ...)
$customers = Customer::find()->with('orders')->all();
// 输出相同
if ($customers[0]->orders[0]->customer === $customers[0]) {
    echo '相同';
} else {
    echo '不相同';
}
```

>注意:相对关系不能在包含中间表的关联关系中定义。 即是，如果你的关系是通过[[yii\db\ActiveQuery::via()|via()]] 或 [[yii\db\ActiveQuery::viaTable()|viaTable()]]方法定义的， 就不能调用[[yii\db\ActiveQuery::inverseOf()]]方法了。


 JOIN 类型关联查询
----------------------

使用关系数据库时，普遍要做的是连接多个表并明确地运用各种 JOIN 查询。
JOIN SQL语句的查询条件和参数，使用 [[yii\db\ActiveQuery::joinWith()]]
可以重用已定义关系并调用
而不是使用 [[yii\db\ActiveQuery::join()]] 来实现目标。

```php
// 查找所有订单并以客户 ID 和订单 ID 排序，并贪婪加载 "customer" 表
$orders = Order::find()->joinWith('customer')->orderBy('customer.id, order.id')->all();
// 查找包括书籍的所有订单，并以 `INNER JOIN` 的连接方式即时加载 "books" 表
$orders = Order::find()->innerJoinWith('books')->all();
```

以上，方法 [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] 是访问 `INNER JOIN` 类型的  [[yii\db\ActiveQuery::joinWith()|joinWith()]] 的快捷方式。

可以连接一个或多个关联关系，可以自由使用查询条件到关联查询，
也可以嵌套连接关联查询。如：

```php
// 连接多重关系
// 找出24小时内注册客户包含书籍的订单
$orders = Order::find()->innerJoinWith([
    'books',
    'customer' => function ($query) {
        $query->where('customer.created_at > ' . (time() - 24 * 3600));
    }
])->all();
// 连接嵌套关系：连接 books 表及其 author 列
$orders = Order::find()->joinWith('books.author')->all();
```

代码背后， Yii 先执行一条 JOIN SQL 语句把满足 JOIN SQL 语句查询条件的主要模型查出，
然后为每个关系执行一条查询语句，
bing填充相应的关联记录。

[[yii\db\ActiveQuery::joinWith()|joinWith()]] 和  [[yii\db\ActiveQuery::with()|with()]] 的区别是
前者连接主模型类和关联模型类的数据表来检索主模型，
而后者只查询和检索主模型类。
检索主模型

由于这个区别，你可以应用只针对一条 JOIN SQL 语句起效的查询条件。
如，通过关联模型的查询条件过滤主模型，如前例，
可以使用关联表的列来挑选主模型数据，

当使用 [[yii\db\ActiveQuery::joinWith()|joinWith()]] 方法时可以响应没有歧义的列名。
In the above examples, we use `item.id` and `order.id` to disambiguate the `id` column references
因为订单表和项目表都包括 `id` 列。

当连接关联关系时，关联关系默认使用即时加载。你可以
通过传参数 `$eagerLoading` 来决定在指定关联查询中是否使用即时加载。

默认 [[yii\db\ActiveQuery::joinWith()|joinWith()]] 使用左连接来连接关联表。
你也可以传 `$joinType` 参数来定制连接类型。
你也可以使用 [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]]。

以下是 `INNER JOIN` 的简短例子：

```php
// 查找包括书籍的所有订单，但 "books" 表不使用即时加载
$orders = Order::find()->innerJoinWith('books', false)->all();
// 等价于：
$orders = Order::find()->joinWith('books', false, 'INNER JOIN')->all();
```

有时连接两个表时，需要在关联查询的 ON 部分指定额外条件。
这可以通过调用 [[yii\db\ActiveQuery::onCondition()]] 方法实现：

```php
class User extends ActiveRecord
{
    public function getBooks()
    {
        return $this->hasMany(Item::className(), ['owner_id' => 'id'])->onCondition(['category_id' => 1]);
    }
}
```

在上面， [[yii\db\ActiveRecord::hasMany()|hasMany()]] 方法回传了一个 [[yii\db\ActiveQuery]] 对象，
当你用 [[yii\db\ActiveQuery::joinWith()|joinWith()]] 执行一条查询时，取决于正被调用的是哪个 [[yii\db\ActiveQuery::onCondition()|onCondition()]]，
返回 `category_id` 为 1 的 items 

当你用 [[yii\db\ActiveQuery::joinWith()|joinWith()]] 进行一次查询时，“on-condition”条件会被放置在相应查询语句的 ON 部分，
如：

```php
// SELECT user.* FROM user LEFT JOIN item ON item.owner_id=user.id AND category_id=1
// SELECT * FROM item WHERE owner_id IN (...) AND category_id=1
$users = User::find()->joinWith('books')->all();
```

注意：如果通过 [[yii\db\ActiveQuery::with()]] 进行贪婪加载或使用惰性加载的话，则 on 条件会被放置在对应 SQL语句的 `WHERE` 部分。
因为，此时此处并没有发生 JOIN 查询。比如：

```php
// SELECT * FROM user WHERE id=10
$user = User::findOne(10);
// SELECT * FROM item WHERE owner_id=10 AND category_id=1
$books = $user->books;
```


关联表操作
-----------------

AR 提供了下面两个方法用来建立和解除两个关联对象之间的关系：

* [[yii\db\ActiveRecord::link()|link()]]
* [[yii\db\ActiveRecord::unlink()|unlink()]]

例如，给定一个customer和order对象，我们可以通过下面的代码使得customer对象拥有order对象：

```php
$customer = Customer::findOne(1);
$order = new Order();
$order->subtotal = 100;
$customer->link('orders', $order);
```

[[yii\db\ActiveRecord::link()|link()]] 调用上述将设置 customer_id 的顺序是 $customer 的主键值，然后调用 [[yii\db\ActiveRecord::save()|save()]] 要将顺序保存到数据库中。


作用域
--------------

当你调用[[yii\db\ActiveRecord::find()|find()]] 或 [[yii\db\ActiveRecord::findBySql()|findBySql()]]方法时，将会返回一个[[yii\db\ActiveQuery|ActiveQuery]]实例。之后，你可以调用其他查询方法，如 [[yii\db\ActiveQuery::where()|where()]]，[[yii\db\ActiveQuery::orderBy()|orderBy()]], 进一步的指定查询条件。

有时候你可能需要在不同的地方使用相同的查询方法。如果出现这种情况，你应该考虑定义所谓的作用域。作用域是本质上要求一组的查询方法来修改查询对象的自定义查询类中定义的方法。 之后你就可以像使用普通方法一样使用作用域。

只需两步即可定义一个作用域。首先给你的model创建一个自定义的查询类，在此类中定义的所需的范围方法。例如，给Comment模型创建一个 CommentQuery类，然后在CommentQuery类中定义一个active()的方法为作用域，像下面的代码：

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

重点:

1. 类必须继承 yii\db\ActiveQuery (或者是其他的 ActiveQuery ，比如 yii\mongodb\ActiveQuery)。
2. 必须是一个public类型的方法且必须返回 $this 实现链式操作。可以传入参数。
3. 检查 [[yii\db\ActiveQuery]] 对于修改查询条件是非常有用的方法。

其次，覆盖[[yii\db\ActiveRecord::find()]] 方法使其返回自定义的查询对象而不是常规的[[yii\db\ActiveQuery|ActiveQuery]]。对于上述例子，你需要编写如下代码：

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

就这样，现在你可以使用自定义的作用域方法了：

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

你也能在定义的关联里使用作用域方法，比如：

```php
class Post extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::className(), ['post_id' => 'id'])->active();

    }
}
```

或者在执行关联查询的时候使用（on-the-fly 是啥？）：

```php
$posts = Post::find()->with([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

### 默认作用域

如果你之前用过 Yii 1.1 就应该知道默认作用域的概念。一个默认的作用域可以作用于所有查询。你可以很容易的通过重写[[yii\db\ActiveRecord::find()]]方法来定义一个默认作用域，例如：

```php
public static function find()
{
    return parent::find()->where(['deleted' => false]);
}
```

注意，你之后所有的查询都不能用 [[yii\db\ActiveQuery::where()|where()]]，但是可以用 [[yii\db\ActiveQuery::andWhere()|andWhere()]] 和 [[yii\db\ActiveQuery::orWhere()|orWhere()]]，他们不会覆盖掉默认作用域。（译注：如果你要使用默认作用域，就不能在 xxx::find()后使用where()方法，你必须使用andXXX()或者orXXX()系的方法，否则默认作用域不会起效果，至于原因，打开where()方法的代码一看便知）


事务操作
------------------

当执行几个相关联的数据库操作的时候

TODO: FIXME: WIP, TBD, [https://github.com/yiisoft/yii2/issues/226](https://github.com/yiisoft/yii2/issues/226)

, [[yii\db\ActiveRecord::afterSave()|afterSave()]], [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]] and/or [[yii\db\ActiveRecord::afterDelete()|afterDelete()]] 生命周期周期方法(life cycle methods 我觉得这句翻译成“模板方法”会不会更好点？)。开发者可以通过重写[[yii\db\ActiveRecord::save()|save()]]方法然后在控制器里使用事务操作，严格地说是似乎不是一个好的做法 （召回"瘦控制器 / 肥模型"基本规则）。

这些方法在这里(如果你不明白自己实际在干什么，请不要使用他们)，Models：

```php
class Feature extends \yii\db\ActiveRecord
{
    // ...

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}

class Product extends \yii\db\ActiveRecord
{
    // ...

    public function getFeatures()
    {
        return $this->hasMany(Feature::className(), ['product_id' => 'id']);
    }
}
```

重写 [[yii\db\ActiveRecord::save()|save()]] 方法：

```php
class ProductController extends \yii\web\Controller
{
    public function actionCreate()
    {
        // FIXME: TODO: WIP, TBD
    }
}
```
(译注：我觉得上面应该是原手册里的bug)

在控制器层使用事务：

```php
class ProductController extends \yii\web\Controller
{
    public function actionCreate()
    {
        // FIXME: TODO: WIP, TBD
    }
}
```

作为这些脆弱方法的替代，你应该使用原子操作方案特性。

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

Controller里的代码将变得很简洁：

```php
class ProductController extends \yii\web\Controller
{
    public function actionCreate()
    {
        // FIXME: TODO: WIP, TBD
    }
}
```

控制器非常简洁：

```php
class ProductController extends \yii\web\Controller
{
    public function actionCreate()
    {
        // FIXME: TODO: WIP, TBD
    }
}
```

乐观锁（Optimistic Locks）
-------------------

TODO

被污染属性
-------------------
当你调用[[yii\db\ActiveRecord::save()|save()]]用于保存活动记录(Active Record)实例时,只有被污染的属性才会被保存。一个属性是否认定为被污染取决于它的值自从最后一次从数据库加载或者最近一次保存到数据库后到现在是否被修改过。注意:无论活动记录(Active Record)是否有被污染属性，数据验证始终会执行。

活动记录(Active Record)会自动维护一个污染数据列表。它的工作方式是通过维护一个较旧属性值版本，并且将它们与最新的进行比较。你可以通过调用[[yii\db\ActiveRecord::getDirtyAttributes()]]来获取当前的污染属性。你也可以调用[[yii\db\ActiveRecord::markAttributeDirty()]]来显示的标记一个属性为污染属性。

如果你对最近一次修改前的属性值感兴趣，你可以调用[[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] 或 [[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]]。

另见
-------------------

- [模型（Model）](model.md)
- [[yii\db\ActiveRecord]]
