活动记录（Active Record）
======================

[Active Record](https://zh.wikipedia.org/wiki/%E4%B8%BB%E5%8A%A8%E8%AE%B0%E5%BD%95) 提供了一个面向对象的接口，
用以访问和操作数据库中的数据。Active Record 类与数据库表关联，
Active Record 实例对应于该表的一行，
Active Record 实例的*属性*表示该行中特定列的值。
您可以访问 Active Record 属性并调用 Active Record 方法来访问和操作存储在数据库表中的数据，
而不用编写原始 SQL 语句。

例如，假定 `Customer` Active Record 类关联着 `customer` 表，
且该类的 `name` 属性代表 `customer` 表的 `name` 列。
你可以写以下代码来哉 `customer` 表里插入一行新的记录：

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

对于 MySQL，上面的代码和使用下面的原生 SQL 语句是等效的，但显然前者更直观，
更不易出错，并且面对不同的数据库系统（DBMS, Database Management System）时更不容易产生兼容性问题。

```php
$db->createCommand('INSERT INTO `customer` (`name`) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

Yii 为以下关系数据库提供 Active Record 支持：

* MySQL 4.1 及以上：通过 [[yii\db\ActiveRecord]] 支持
* PostgreSQL 7.3 及以上：通过 [[yii\db\ActiveRecord]] 支持
* SQLite 2 and 3：通过 [[yii\db\ActiveRecord]] 支持
* Microsoft SQL Server 2008 及以上：通过 [[yii\db\ActiveRecord]] 支持
* Oracle：通过 [[yii\db\ActiveRecord]] 支持
* CUBRID 9.3 及以上：通过 [[yii\db\ActiveRecord]] 支持 (提示， 由于 CUBRID PDO 扩展的 [bug](http://jira.cubrid.org/browse/APIS-658)，
  给变量加引用将不起作用，所以你得使用 CUBRID 9.3 客户端及服务端。
* Sphinx：通过 [[yii\sphinx\ActiveRecord]] 支持，依赖 `yii2-sphinx` 扩展
* ElasticSearch：通过 [[yii\elasticsearch\ActiveRecord]] 支持, 依赖 `yii2-elasticsearch` 扩展

此外，Yii 的 Active Record 功能还支持以下 NoSQL 数据库：

* Redis 2.6.12 及以上：通过 [[yii\redis\ActiveRecord]] 支持，依赖 `yii2-redis` 扩展
* MongoDB 1.3.0 及以上：通过 [[yii\mongodb\ActiveRecord]] 支持，依赖 `yii2-mongodb` 扩展

在本教程中，我们会主要描述对关系型数据库的 Active Record 用法。
然而，绝大多数的内容在 NoSQL 的 Active Record 里同样适用。


## 声明 Active Record 类（Declaring Active Record Classes） <span id="declaring-ar-classes"></span>

要想声明一个 Active Record 类，你需要声明该类继承 [[yii\db\ActiveRecord]]。

### 设置表的名称（Setting a table name）

默认的，每个 Active Record 类关联各自的数据库表。
经过 [[yii\helpers\Inflector::camel2id()]] 处理，[[yii\db\ActiveRecord::tableName()|tableName()]] 方法默认返回的表名称是通过类名转换来得。 
如果这个默认名称不正确，你得重写这个方法。

此外，[[yii\db\Connection::$tablePrefix|tablePrefix]] 表前缀也会起作用。例如，如果
[[yii\db\Connection::$tablePrefix|tablePrefix]] 表前缀是 `tbl_`，`Customer` 的类名将转换成 `tbl_customer` 表名，`OrderItem` 转换成 `tbl_order_item`。

如果你定义的表名是 `{{%TableName}}`，百分比字符 `%` 会被替换成表前缀。
例如，`{{%post}}` 会变成 `{{tbl_post}}`。表名两边的括号会被 [SQL 查询引用](db-dao.md#quoting-table-and-column-names) 处理。


下面的例子中，我们给 `customer` 数据库表定义叫 `Customer` 的 Active Record 类。

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @return string Active Record 类关联的数据库表名称
     */
    public static function tableName()
    {
        return '{{customer}}';
    }
}
```

### 将 Active Record 称为模型（Active records are called "models"）

Active Record 实例称为[模型](structure-models.md)。因此, 我们通常将 Active Record 类
放在 `app\models` 命名空间下（或者其他保存模型的命名空间）。

因为 [[yii\db\ActiveRecord]] 继承了模型 [[yii\base\Model]]，它就拥有所有[模型](structure-models.md)特性，
比如说属性（attributes），验证规则（rules），数据序列化（data serialization），等等。


## 建立数据库连接（Connecting to Databases） <span id="db-connection"></span>

活动记录 Active Record 默认使用 `db` [组件](structure-application-components.md) 
作为连接器 [[yii\db\Connection|DB connection]] 访问和操作数据库数据。 
基于[数据库访问](db-dao.md)中的解释，你可以在系统配置中
这样配置 `db` 组件。

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

如果你要用不同的数据库连接，而不仅仅是 `db` 组件，
你可以重写 [[yii\db\ActiveRecord::getDb()|getDb()]] 方法。

```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        // 使用 "db2" 组件
        return \Yii::$app->db2;  
    }
}
```


## 查询数据（Querying Data） <span id="querying-data"></span>

定义 Active Record 类后，你可以从相应的数据库表中查询数据。
查询过程大致如下三个步骤：

1. 通过 [[yii\db\ActiveRecord::find()]] 方法创建一个新的查询生成器对象；
2. 使用[查询生成器的构建方法](db-query-builder.md#building-queries)来构建你的查询；
3. 调用[查询生成器的查询方法](db-query-builder.md#query-methods)来取出数据到 Active Record 实例中。

正如你看到的，是不是跟[查询生成器](db-query-builder.md)的步骤差不多。
唯一有区别的地方在于你用 [[yii\db\ActiveRecord::find()]] 去获得一个新的查询生成器对象，这个对象是 [[yii\db\ActiveQuery]]，
而不是使用 `new` 操作符创建一个查询生成器对象。

下面是一些例子，介绍如何使用 Active Query 查询数据：

```php
// 返回 ID 为 123 的客户：
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::find()
    ->where(['id' => 123])
    ->one();

// 取回所有活跃客户并以他们的 ID 排序：
// SELECT * FROM `customer` WHERE `status` = 1 ORDER BY `id`
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// 取回活跃客户的数量：
// SELECT COUNT(*) FROM `customer` WHERE `status` = 1
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// 以客户 ID 索引结果集：
// SELECT * FROM `customer`
$customers = Customer::find()
    ->indexBy('id')
    ->all();
```

上述代码中，`$customer` 是个 `Customer` 对象，而 `$customers` 是个以 `Customer` 对象为元素的数组。
它们两都是以 `customer` 表中取回的数据结果集填充的。

> Tip: 由于 [[yii\db\ActiveQuery]] 继承 [[yii\db\Query]]，
  你可以使用 [Query Builder](db-query-builder.md) 章节里所描述的*所有*查询方法。

根据主键获取数据行是比较常见的操作，所以 Yii 
提供了两个快捷方法：

- [[yii\db\ActiveRecord::findOne()]]：返回一个 Active Record 实例，填充于查询结果的第一行数据。
- [[yii\db\ActiveRecord::findAll()]]：返回一个 Active Record 实例的数据，填充于查询结果的全部数据。

这两个方法的传参格式如下：

- 标量值：这个值会当作主键去查询。
  Yii 会通过读取数据库模式信息来识别主键列。
- 标量值的数组：这数组里的值都当作要查询的主键的值。
- 关联数组：键值是表的列名，元素值是相应的要查询的条件值。
  可以到 [哈希格式](db-query-builder.md#hash-format) 查看更多信息。
  
如下代码描述如何使用这些方法：

```php
// 返回 id 为 123 的客户 
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// 返回 id 是 100, 101, 123, 124 的客户
// SELECT * FROM `customer` WHERE `id` IN (100, 101, 123, 124)
$customers = Customer::findAll([100, 101, 123, 124]);

// 返回 id 是 123 的活跃客户
// SELECT * FROM `customer` WHERE `id` = 123 AND `status` = 1
$customer = Customer::findOne([
    'id' => 123,
    'status' => Customer::STATUS_ACTIVE,
]);

// 返回所有不活跃的客户
// SELECT * FROM `customer` WHERE `status` = 0
$customers = Customer::findAll([
    'status' => Customer::STATUS_INACTIVE,
]);
```

> Warning: 如果你需要将用户输入传递给这些方法，请确保输入值是标量或者是
> 数组条件，确保数组结构不能被外部所改变：
>
> ```php
> // yii\web\Controller 确保了 $id 是标量
> public function actionView($id)
> {
>     $model = Post::findOne($id);
>     // ...
> }
>
> // 明确了指定要搜索的列，在此处传递标量或数组将始终只是查找出单个记录而已
> $model = Post::findOne(['id' => Yii::$app->request->get('id')]);
>
> // 不要使用下面的代码！可以注入一个数组条件来匹配任意列的值！
> $model = Post::findOne(Yii::$app->request->get('id'));
> ```


> Tip: [[yii\db\ActiveRecord::findOne()]] 和 [[yii\db\ActiveQuery::one()]] 都不会添加 `LIMIT 1` 到
  生成的 SQL 语句中。如果你的查询会返回很多行的数据，
  你明确的应该加上 `limit(1)` 来提高性能，比如 `Customer::find()->limit(1)->one()`。

除了使用查询生成器的方法之外，你还可以书写原生的 SQL 语句来查询数据，并填充结果集到 Active Record 对象中。
通过使用 [[yii\db\ActiveRecord::findBySql()]] 方法:

```php
// 返回所有不活跃的客户
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

不要在 [[yii\db\ActiveRecord::findBySql()|findBySql()]] 方法后加其他查询方法了，
多余的查询方法都会被忽略。


## 访问数据（Accessing Data） <span id="accessing-data"></span>

如上所述，从数据库返回的数据被填充到 Active Record 实例中，
查询结果的每一行对应于单个 Active Record 实例。
您可以通过 Active Record 实例的属性来访问列值，例如，

```php
// "id" 和 "email" 是 "customer" 表中的列名
$customer = Customer::findOne(123);
$id = $customer->id;
$email = $customer->email;
```

> Tip: Active Record 的属性以区分大小写的方式为相关联的表列命名的。
  Yii 会自动为关联表的每一列定义 Active Record 中的一个属性。
  您不应该重新声明任何属性。

由于 Active Record 的属性以表的列名命名，可能你会发现你正在编写像这样的 PHP 代码：
`$customer->first_name`，如果你的表的列名是使用下划线分隔的，那么属性名中的单词
以这种方式命名。 如果您担心代码风格一致性的问题，那么你应当重命名相应的表列名
（例如使用骆驼拼写法）。


### 数据转换（Data Transformation） <span id="data-transformation"></span>

常常遇到，要输入或显示的数据是一种格式，而要将其存储在数据库中是另一种格式。
例如，在数据库中，您将客户的生日存储为 UNIX 时间戳（虽然这不是一个很好的设计），
而在大多数情况下，你想以字符串 `'YYYY/MM/DD'` 的格式处理生日数据。
为了实现这一目标，您可以在 `Customer` 中定义 *数据转换* 方法
定义 Active Record 类如下：

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

现在你的 PHP 代码中，你可以访问 `$customer->birthdayText`，
来以 `'YYYY/MM/DD'` 的格式输入和显示客户生日，而不是访问 `$customer->birthday`。

> Tip: 上述示例显示了以不同格式转换数据的通用方法。如果你正在使用
> 日期值，您可以使用 [DateValidator](tutorial-core-validators.md#date) 和 [[yii\jui\DatePicker|DatePicker]] 来操作，
> 这将更易用，更强大。


### 以数组形式获取数据（Retrieving Data in Arrays） <span id="data-in-arrays"></span>

通过 Active Record 对象获取数据十分方便灵活，与此同时，当你需要返回大量的数据的时候，
这样的做法并不令人满意，因为这将导致大量内存占用。在这种情况下，您可以
在查询方法前调用 [[yii\db\ActiveQuery::asArray()|asArray()]] 方法，来获取 PHP 数组形式的结果：

```php
// 返回所有客户
// 每个客户返回一个关联数组
$customers = Customer::find()
    ->asArray()
    ->all();
```

> Tip: 虽然这种方法可以节省内存并提高性能，但它更靠近较低的 DB 抽象层
  你将失去大部分的 Active Record 提供的功能。 一个非常重要的区别在于列值的数据类型。
  当您在 Active Record 实例中返回数据时，列值将根据实际列类型，自动类型转换；
  然而，当您以数组返回数据时，列值将为
  字符串（因为它们是没有处理过的 PDO 的结果），不管它们的实际列是什么类型。
   

### 批量获取数据（Retrieving Data in Batches） <span id="data-in-batches"></span>

在 [查询生成器](db-query-builder.md) 中，我们已经解释说可以使用 *批处理查询* 来最小化你的内存使用，
每当从数据库查询大量数据。你可以在 Active Record 中使用同样的技巧。例如，

```php
// 每次获取 10 条客户数据
foreach (Customer::find()->batch(10) as $customers) {
    // $customers 是个最多拥有 10 条数据的数组
}

// 每次获取 10 条客户数据，然后一条一条迭代它们
foreach (Customer::find()->each(10) as $customer) {
    // $customer 是个 `Customer` 对象
}

// 贪婪加载模式的批处理查询
foreach (Customer::find()->with('orders')->each() as $customer) {
    // $customer 是个 `Customer` 对象，并附带关联的 `'orders'`
}
```


## 保存数据（Saving Data） <span id="inserting-updating-data"></span>

使用 Active Record，您可以通过以下步骤轻松地将数据保存到数据库：

1. 准备一个 Active Record 实例
2. 将新值赋给 Active Record 的属性
3. 调用 [[yii\db\ActiveRecord::save()]] 保存数据到数据库中。

例如，

```php
// 插入新记录
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();

// 更新已存在的记录
$customer = Customer::findOne(123);
$customer->email = 'james@newexample.com';
$customer->save();
```

[[yii\db\ActiveRecord::save()|save()]] 方法可能插入或者更新表的记录，这取决于 Active Record 实例的状态。
如果实例通过 `new` 操作符实例化，调用 [[yii\db\ActiveRecord::save()|save()]] 方法将插入新记录；
如果实例是一个查询方法的结果，调用 [[yii\db\ActiveRecord::save()|save()]] 方法
将更新这个实例对应的表记录行。

你可以通过检查 Active Record 实例的 [[yii\db\ActiveRecord::isNewRecord|isNewRecord]] 属性值来区分这两个状态。
此属性也被使用在 [[yii\db\ActiveRecord::save()|save()]] 方法内部，
代码如下：

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

> Tip: 你可以直接调用 [[yii\db\ActiveRecord::insert()|insert()]] 或者 [[yii\db\ActiveRecord::update()|update()]]
  方法来插入或更新一条记录。
  

### 数据验证（Data Validation） <span id="data-validation"></span>

因为 [[yii\db\ActiveRecord]] 继承于 [[yii\base\Model]]，它共享相同的 [输入验证](input-validation.md) 功能。
你可以通过重写 [[yii\db\ActiveRecord::rules()|rules()]] 方法声明验证规则并执行，
通过调用 [[yii\db\ActiveRecord::validate()|validate()]] 方法进行数据验证。
  
当你调用 [[yii\db\ActiveRecord::save()|save()]] 时，默认情况下会自动调用 [[yii\db\ActiveRecord::validate()|validate()]]。
只有当验证通过时，它才会真正地保存数据; 否则将简单地返回 `false`，
您可以检查 [[yii\db\ActiveRecord::errors|errors]] 属性来获取验证过程的错误消息。

> Tip: 如果你确定你的数据不需要验证（比如说数据来自可信的场景），
  你可以调用 `save(false)` 来跳过验证过程。


### 块赋值（Massive Assignment） <span id="massive-assignment"></span>

和普通的 [模型](structure-models.md) 一样，你亦可以享受 Active Record 实例的 [块赋值](structure-models.md#massive-assignment) 特性。
使用此功能，您可以在单个 PHP 语句中，给 Active Record 实例的多个属性批量赋值，
如下所示。 记住，只有 [安全属性](structure-models.md#safe-attributes) 才可以批量赋值。

```php
$values = [
    'name' => 'James',
    'email' => 'james@example.com',
];

$customer = new Customer();

$customer->attributes = $values;
$customer->save();
```


### 更新计数（Updating Counters） <span id="updating-counters"></span>

在数据库表中增加或减少一个字段的值是个常见的任务。我们将这些列称为“计数列”。
您可以使用 [[yii\db\ActiveRecord::updateCounters()|updateCounters()]] 更新一个或多个计数列。
例如，

```php
$post = Post::findOne(100);

// UPDATE `post` SET `view_count` = `view_count` + 1 WHERE `id` = 100
$post->updateCounters(['view_count' => 1]);
```

> Note: 如果你使用 [[yii\db\ActiveRecord::save()]] 更新一个计数列，你最终将得到错误的结果，
  因为可能发生这种情况，多个请求间并发读写同一个计数列。


### 脏属性（Dirty Attributes） <span id="dirty-attributes"></span>

当您调用 [[yii\db\ActiveRecord::save()|save()]] 保存 Active Record 实例时，只有 *脏属性*
被保存。如果一个属性的值已被修改，则会被认为是 *脏*，因为它是从 DB 加载出来的或者
刚刚保存到 DB 。请注意，无论如何 Active Record 都会执行数据验证
不管有没有脏属性。

Active Record 自动维护脏属性列表。 它保存所有属性的旧值，
并其与最新的属性值进行比较，就是酱紫个道理。你可以调用 [[yii\db\ActiveRecord::getDirtyAttributes()]] 
获取当前的脏属性。你也可以调用 [[yii\db\ActiveRecord::markAttributeDirty()]] 
将属性显式标记为脏。

如果你有需要获取属性原先的值，你可以调用
[[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] 或者 [[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]]。

> 注：属性新旧值的比较是用 `===` 操作符，所以一样的值但类型不同，
> 依然被认为是脏的。当模型从 HTML 表单接收用户输入时，通常会出现这种情况，
> 其中每个值都表示为一个字符串类型。
> 为了确保正确的类型，比如，整型需要用[过滤验证器](input-validation.md#data-filtering)：
> `['attributeName', 'filter', 'filter' => 'intval']`。其他 PHP 类型转换函数一样适用，像
> [intval()](https://www.php.net/manual/zh/function.intval.php)， [floatval()](https://www.php.net/manual/zh/function.floatval.php)，
> [boolval](https://www.php.net/manual/zh/function.boolval.php)，等等

### 默认属性值（Default Attribute Values） <span id="default-attribute-values"></span>

某些表列可能在数据库中定义了默认值。有时，你可能想预先填充
具有这些默认值的 Active Record 实例的 Web 表单。 为了避免再次写入相同的默认值，
您可以调用 [[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]] 来填充 DB 定义的默认值
进入相应的 Active Record 属性：

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz 将被 “zyz” 列定义的默认值赋值
```


### 属性类型转换（Attributes Typecasting） <span id="attributes-typecasting"></span>

在查询结果填充 [[yii\db\ActiveRecord]] 时，将自动对其属性值执行类型转换，基于
[数据库表模式](db-dao.md#database-schema) 中的信息。 这允许从数据表中获取数据，
声明为整型的，使用 PHP 整型填充 ActiveRecord 实例，布尔值（boolean）的也用布尔值填充，等等。
但是，类型转换机制有几个限制：

* 浮点值不被转换，并且将被表示为字符串，否则它们可能会使精度降低。
* 整型值的转换取决于您使用的操作系统的整数容量。尤其是：
  声明为“无符号整型”或“大整型”的列的值将仅转换为 64 位操作系统的 PHP 整型，
  而在 32 位操作系统中 - 它们将被表示为字符串。

值得注意的是，只有在从查询结果填充 ActiveRecord 实例时才执行属性类型转换。
而从 HTTP 请求加载的值或直接通过属性访问赋值的，没有自动转换。
在准备用于在 ActiveRecord 保存时，准备 SQL 语句还使用了表模式，以确保查询时
值绑定到具有正确类型的。但是，ActiveRecord 实例的属性值不会
在保存过程中转换。

> Tip: 你可以使用 [[yii\behaviors\AttributeTypecastBehavior]] 来简化属性的类型转换
  在 ActiveRecord 验证或者保存过程中。
  
从 2.0.14 开始，Yii ActiveRecord 支持了更多的复杂数据类型，例如 JSON 或多维数组。

#### MySQL 和 PostgreSQL 中的 JSON（JSON in MySQL and PostgreSQL）

数据填充后，基于 JSON 标准解码规则，
来自 JSON 列的值将自动解码。

另一方面，为了将属性值保存到 JSON 列中，ActiveRecord 会自动创建一个 [[yii\db\JsonExpression|JsonExpression]] 对象，
这对象将在 [QueryBuilder](db-query-builder.md) 层被编码成 JSON 字符串。

#### PostgreSQL 中的数组（Arrays in PostgreSQL）

数据填充后，来自 Array 列的值将自动从 PgSQL 的编码值解码为 一个 [[yii\db\ArrayExpression|ArrayExpression]]
对象。它继承于 PHP 的 `ArrayAccess` 接口，所以你可以把它当作一个数组用，或者调用 `->getValue()` 来获取数组本身。

另一方面，为了将属性值保存到数组列，ActiveRecord 会自动创建一个 [[yii\db\ArrayExpression|ArrayExpression]] 对象，
这对象将在 [QueryBuilder](db-query-builder.md) 中被编码成数组的 PgSQL 字符串表达式。

你还可以这样使用 JSON 列的条件：

```php
$query->andWhere(['=', 'json', new ArrayExpression(['foo' => 'bar'])
```

要详细了解表达式构建系统，可以访问 [Query Builder – 增加自定义条件和语句](db-query-builder.md#adding-custom-conditions-and-expressions)
文章。

### 更新多个数据行（Updating Multiple Rows） <span id="updating-multiple-rows"></span>

上述方法都可以用于单个 Active Record 实例，以插入或更新单条
表数据行。 要同时更新多个数据行，你应该调用 [[yii\db\ActiveRecord::updateAll()|updateAll()]]
这是一个静态方法。

```php
// UPDATE `customer` SET `status` = 1 WHERE `email` LIKE `%@example.com%`
Customer::updateAll(['status' => Customer::STATUS_ACTIVE], ['like', 'email', '@example.com']);
```

同样，你可以调用 [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] 同时更新多条记录的计数列。


```php
// UPDATE `customer` SET `age` = `age` + 1
Customer::updateAllCounters(['age' => 1]);
```


## 删除数据（Deleting Data） <span id="deleting-data"></span>

要删除单行数据，首先获取与该行对应的 Active Record 实例，然后调用
[[yii\db\ActiveRecord::delete()]] 方法。

```php
$customer = Customer::findOne(123);
$customer->delete();
```

你可以调用 [[yii\db\ActiveRecord::deleteAll()]] 方法删除多行甚至全部的数据。例如，

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> Tip: 调用 [[yii\db\ActiveRecord::deleteAll()|deleteAll()]] 时要非常小心，因为如果在指定条件时出错，
  它可能会完全擦除表中的所有数据。


## Active Record 的生命周期（Active Record Life Cycles） <span id="ar-life-cycles"></span>

当你实现各种功能的时候，会发现了解 Active Record 的生命周期很重要。
在每个生命周期中，一系列的方法将被调用执行，您可以重写这些方法
以定制你要的生命周期。您还可以响应触发某些 Active Record 事件
以便在生命周期中注入您的自定义代码。这些事件在开发 Active Record 的 [行为](concept-behaviors.md)时特别有用，
通过行为可以定制 Active Record 生命周期的 。

下面，我们将总结各种 Active Record 的生命周期，以及生命周期中
所涉及的各种方法、事件。


### 实例化生命周期（New Instance Life Cycle） <span id="new-instance-life-cycle"></span>

当通过 `new` 操作符新建一个 Active Record 实例时，会发生以下生命周期：

1. 类的构造函数调用.
2. [[yii\db\ActiveRecord::init()|init()]]：触发 [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] 事件。


### 查询数据生命周期（Querying Data Life Cycle） <span id="querying-data-life-cycle"></span>

当通过 [查询方法](#querying-data) 查询数据时，每个新填充出来的 Active Record 实例
将发生下面的生命周期：

1. 类的构造函数调用。
2. [[yii\db\ActiveRecord::init()|init()]]：触发 [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] 事件。
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]：触发 [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] 事件。


### 保存数据生命周期（Saving Data Life Cycle） <span id="saving-data-life-cycle"></span>

当通过 [[yii\db\ActiveRecord::save()|save()]] 插入或更新 Active Record 实例时
会发生以下生命周期：

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]：触发 
   [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] 事件。如果这方法返回 `false` 
   或者 [[yii\base\ModelEvent::isValid]] 值为 `false`，接下来的步骤都会被跳过。
2. 执行数据验证。如果数据验证失败，步骤 3 之后的步骤将被跳过。
3. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]：触发
   [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]] 事件。
4. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]：触发
   [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] 
   或者 [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]] 事件。 如果这方法返回 `false` 
   或者 [[yii\base\ModelEvent::isValid]] 值为 `false`，接下来的步骤都会被跳过。
5. 执行真正的数据插入或者更新。
6. [[yii\db\ActiveRecord::afterSave()|afterSave()]]：触发
   [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] 
   或者 [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]] 事件。
   

### 删除数据生命周期（Deleting Data Life Cycle） <span id="deleting-data-life-cycle"></span>

当通过 [[yii\db\ActiveRecord::delete()|delete()]] 删除 Active Record 实例时，
会发生以下生命周期：

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]：触发
   [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]] 事件。 如果这方法返回 `false` 
   或者 [[yii\base\ModelEvent::isValid]] 值为 `false`，接下来的步骤都会被跳过。
2. 执行真正的数据删除。
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]：触发
   [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]] 事件。


> Tip: 调用以下方法则不会启动上述的任何生命周期，
> 因为这些方法直接操作数据库，而不是基于 Active Record 模型：
>
> - [[yii\db\ActiveRecord::updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()]] 

### 刷新数据生命周期（Refreshing Data Life Cycle） <span id="refreshing-data-life-cycle"></span>

当通过 [[yii\db\ActiveRecord::refresh()|refresh()]] 刷新 Active Record 实例时，
如刷新成功方法返回 `true`，那么 [[yii\db\ActiveRecord::EVENT_AFTER_REFRESH|EVENT_AFTER_REFRESH]] 事件将被触发。


## 事务操作（Working with Transactions） <span id="transactional-operations"></span>

Active Record 有两种方式来使用[事务](db-dao.md#performing-transactions)。

第一种方法是在事务块中显式地包含 Active Record 的各个方法调用，如下所示，

```php
$customer = Customer::findOne(123);

Customer::getDb()->transaction(function($db) use ($customer) {
    $customer->id = 200;
    $customer->save();
    // ...其他 DB 操作...
});

// 或者

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

> Tip: 在上面的代码中，我们有两个catch块用于兼容
> PHP 5.x 和 PHP 7.x。 `\Exception` 继承于 [`\Throwable` interface](https://www.php.net/manual/zh/class.throwable.php)
> 由于 PHP 7.0 的改动，如果您的应用程序仅使用 PHP 7.0 及更高版本，您可以跳过 `\Exception` 部分。

第二种方法是在 [[yii\db\ActiveRecord::transactions()]] 方法中列出需要事务支持的 DB 操作。 
例如，

```php
class Customer extends ActiveRecord
{
    public function transactions()
    {
        return [
            'admin' => self::OP_INSERT,
            'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            // 上面等价于：
            // 'api' => self::OP_ALL,
        ];
    }
}
```

[[yii\db\ActiveRecord::transactions()]] 方法应当返回以 [场景](structure-models.md#scenarios) 为键、
以需要放到事务中的 DB 操作为值的数组。以下的常量
可以表示相应的 DB 操作：

* [[yii\db\ActiveRecord::OP_INSERT|OP_INSERT]]：插入操作用于执行 [[yii\db\ActiveRecord::insert()|insert()]]；
* [[yii\db\ActiveRecord::OP_UPDATE|OP_UPDATE]]：更新操作用于执行 [[yii\db\ActiveRecord::update()|update()]]；
* [[yii\db\ActiveRecord::OP_DELETE|OP_DELETE]]：删除操作用于执行 [[yii\db\ActiveRecord::delete()|delete()]]。

使用 `|` 运算符连接上述常量来表明多个操作。您也可以使用
快捷常量 [[yii\db\ActiveRecord::OP_ALL|OP_ALL]] 来指代上述所有的三个操作。

这个事务方法的原理是：相应的事务在调用 [[yii\db\ActiveRecord::beforeSave()|beforeSave()]] 方法时开启，
在调用 [[yii\db\ActiveRecord::afterSave()|afterSave()]] 方法时被提交。

## 乐观锁（Optimistic Locks） <span id="optimistic-locks"></span>

乐观锁是一种防止此冲突的方法：一行数据
同时被多个用户更新。例如，同一时间内，用户 A 和用户 B 都在编辑
相同的 wiki 文章。用户 A 保存他的编辑后，用户 B 也点击“保存”按钮来
保存他的编辑。实际上，用户 B 正在处理的是过时版本的文章，
因此最好是，想办法阻止他保存文章并向他提示一些信息。

乐观锁通过使用一个字段来记录每行的版本号来解决上述问题。
当使用过时的版本号保存一行数据时，[[yii\db\StaleObjectException]] 异常
将被抛出，这阻止了该行的保存。乐观锁只支持更新 [[yii\db\ActiveRecord::update()]] 
或者删除 [[yii\db\ActiveRecord::delete()]]
已经存在的单条数据行。

使用乐观锁的步骤，

1. 在与 Active Record 类相关联的 DB 表中创建一个列，以存储每行的版本号。
   这个列应当是长整型（在 MySQL 中是  `BIGINT DEFAULT 0`）。
2. 重写 [[yii\db\ActiveRecord::optimisticLock()]] 方法返回这个列的命名。
3. 在你的 Model 类里实现 [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] 行为（注：这个行为类在 2.0.16 版本加入），以便从请求参数里自动解析这个列的值。
   然后从验证规则中删除 version 属性，因为 [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] 已经处理它了.
4. 在用于用户填写的 Web 表单中，添加一个隐藏字段（hidden field）来存储正在更新的行的当前版本号。
5. 在使用 Active Record 更新数据的控制器动作中，要捕获（try/catch） [[yii\db\StaleObjectException]] 异常。
   实现一些业务逻辑来解决冲突（例如合并更改，提示陈旧的数据等等）。
   
例如，假定版本列被命名为 `version`。您可以使用下面的代码来实现乐观锁。


```php
// ------ 视图层代码 -------

use yii\helpers\Html;

// ...其他输入栏
echo Html::activeHiddenInput($model, 'version');


// ------ 控制器代码 -------

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
        // 解决冲突的代码
    }
}

// ------ Model 代码 -------

use yii\behaviors\OptimisticLockBehavior;

public function behaviors()
{
    return [
        OptimisticLockBehavior::class,
    ];
}

public function optimisticLock()
{
    return 'version';
}

```
> Note: 因为 [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] 仅仅在保存记录的时候被确认，
> 如果用户提交的有效版本号被直接解析 ：[[\yii\web\Request::getBodyParam()|getBodyParam()]]，
> 那么你的 Model 将扩展成这样：触发在步骤 3 中子类的行为，与此同时，调用步骤 2 中的父类的定义，
> 这样你在把 Model 绑定到负责接收用户输入的控制器的同时，有一个专门用于内部逻辑处理的实例，
> 或者，您可以通过配置其 [[\yii\behaviors\OptimisticLockBehavior::$value|value]] 的属性来实现自己的逻辑。（注：这一堆都是在解释 Behaviors 的原理）


## 使用关联数据（Working with Relational Data） <span id="relational-data"></span>

除了处理单个数据库表之外，Active Record 还可以将相关数据集中进来，
使其可以通过原始数据轻松访问。 例如，客户数据与订单数据相关
因为一个客户可能已经存放了一个或多个订单。这种关系通过适当的声明，
你可以使用 `$customer->orders` 表达式访问客户的订单信息
这表达式将返回包含 `Order` Active Record 实例的客户订单信息的数组。


### 声明关联关系（Declaring Relations） <span id="declaring-relations"></span>

你必须先在 Active Record 类中定义关联关系，才能使用 Active Record 的关联数据。
简单地为每个需要定义关联关系声明一个 *关联方法* 即可，如下所示，

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

上述的代码中，我们为 `Customer` 类声明了一个 `orders` 关联，
和为 `Order` 声明了一个 `customer` 关联。

每个关联方法必须这样命名：`getXyz`。然后我们通过 `xyz`（首字母小写）调用这个关联名。
请注意关联名是大小写敏感的。

当声明一个关联关系的时候，必须指定好以下的信息：

- 关联的对应关系：通过调用 [[yii\db\ActiveRecord::hasMany()|hasMany()]]
  或者 [[yii\db\ActiveRecord::hasOne()|hasOne()]] 指定。在上面的例子中，您可以很容易看出这样的关联声明：
  一个客户可以有很多订单，而每个订单只有一个客户。
- 相关联 Active Record 类名：用来指定为 [[yii\db\ActiveRecord::hasMany()|hasMany()]] 或者 
  [[yii\db\ActiveRecord::hasOne()|hasOne()]] 方法的第一个参数。
  推荐的做法是调用 `Xyz::class` 来获取类名称的字符串，以便您
  可以使用 IDE 的自动补全，以及让编译阶段的错误检测生效。
- 两组数据的关联列：用以指定两组数据相关的列（hasOne()/hasMany() 的第二个参数）。
  数组的值填的是主数据的列（当前要声明关联的 Active Record 类为主数据），
  而数组的键要填的是相关数据的列。

  一个简单的口诀，先附表的主键，后主表的主键。
  正如上面的例子，`customer_id` 是 `Order` 的属性，而 `id`是 `Customer` 的属性。
  （译者注：hasMany() 的第二个参数，这个数组键值顺序不要弄反了）
  

### 访问关联数据（Accessing Relational Data） <span id="accessing-relational-data"></span>

定义了关联关系后，你就可以通过关联名访问相应的关联数据了。就像
访问一个由关联方法定义的对象一样，具体概念请查看 [属性](concept-properties.md)。
因此，现在我们可以称它为 *关联属性* 了。

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
// $orders 是由 Order 类组成的数组
$orders = $customer->orders;
```

> Tip: 当你通过 getter 方法 `getXyz()` 声明了一个叫 `xyz` 的关联属性，你就可以像
  [属性](concept-properties.md) 那样访问 `xyz`。注意这个命名是区分大小写的。
  
如果使用 [[yii\db\ActiveRecord::hasMany()|hasMany()]] 声明关联关系，则访问此关联属性
将返回相关的 Active Record 实例的数组；
如果使用 [[yii\db\ActiveRecord::hasOne()|hasOne()]] 声明关联关系，访问此关联属性
将返回相关的 Active Record 实例，如果没有找到相关数据的话，则返回 `null`。

当你第一次访问关联属性时，将执行 SQL 语句获取数据，如
上面的例子所示。如果再次访问相同的属性，将返回先前的结果，而不会重新执行
SQL 语句。要强制重新执行 SQL 语句，你应该先 unset 这个关联属性，
如：`unset（$ customer-> orders）`。

> Tip: 虽然这个概念跟 这个 [属性](concept-properties.md) 特性很像，
> 但是还是有一个很重要的区别。普通对象属性的属性值与其定义的 getter 方法的类型是相同的。
> 而关联方法返回的是一个 [[yii\db\ActiveQuery]] 活动查询生成器的实例。只有当访问关联属性的的时候，
> 才会返回 [[yii\db\ActiveRecord]] Active Record 实例，或者 Active Record 实例组成的数组。
> 
> ```php
> $customer->orders; // 获得 `Order` 对象的数组
> $customer->getOrders(); // 返回 ActiveQuery 类的实例
> ```
> 
> 这对于创建自定义查询很有用，下一节将对此进行描述。


### 动态关联查询（Dynamic Relational Query） <span id="dynamic-relational-query"></span>

由于关联方法返回 [[yii\db\ActiveQuery]] 的实例，因此你可以在执行 DB 查询之前，
使用查询构建方法进一步构建此查询。例如，

```php
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getOrders()
    ->where(['>', 'subtotal', 200])
    ->orderBy('id')
    ->all();
```

与访问关联属性不同，每次通过关联方法执行动态关联查询时，
都会执行 SQL 语句，即使你之前执行过相同的动态关联查询。

有时你可能需要给你的关联声明传递参数，以便您能更方便地执行
动态关系查询。例如，您可以声明一个 `bigOrders` 关联如下，

```php
class Customer extends ActiveRecord
{
    public function getBigOrders($threshold = 100) // 老司机的提醒：$threshold 参数一定一定要给个默认值
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])
            ->where('subtotal > :threshold', [':threshold' => $threshold])
            ->orderBy('id');
    }
}
```

然后你就可以执行以下关联查询：

```php
// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getBigOrders(200)->all();

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 100 ORDER BY `id`
$orders = $customer->bigOrders;
```


### 中间关联表（Relations via a Junction Table） <span id="junction-table"></span>

在数据库建模中，当两个关联表之间的对应关系是多对多时，
通常会引入一个[连接表](https://zh.wikipedia.org/wiki/%E5%85%B3%E8%81%94%E5%AE%9E%E4%BD%93)。例如，`order` 表
和 `item` 表可以通过名为 `order_item` 的连接表相关联。一个 order 将关联多个 order items，
而一个 order item 也会关联到多个 orders。

当声明这种表关联后，您可以调用 [[yii\db\ActiveQuery::via()|via()]] 或 [[yii\db\ActiveQuery::viaTable()|viaTable()]]
指明连接表。[[yii\db\ActiveQuery::via()|via()]] 和 [[yii\db\ActiveQuery::viaTable()|viaTable()]] 之间的区别是
前者是根据现有的关联名称来指定连接表，而后者直接使用
连接表。例如，

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

或者,

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

使用连接表声明的关联和正常声明的关联是等同的，例如，

```php
// SELECT * FROM `order` WHERE `id` = 100
$order = Order::findOne(100);

// SELECT * FROM `order_item` WHERE `order_id` = 100
// SELECT * FROM `item` WHERE `item_id` IN (...)
// 返回 Item 类组成的数组
$items = $order->items;
```


### 通过多个表来连接关联声明（Chaining relation definitions via multiple tables） <span id="multi-table-relations"></span>

通过使用 [[yii\db\ActiveQuery::via()|via()]] 方法，它还可以通过多个表来定义关联声明。
再考虑考虑上面的例子，我们有 `Customer`，`Order` 和 `Item` 类。
我们可以添加一个关联关系到 `Customer` 类，这个关联可以列出了 `Customer`（客户） 的订单下放置的所有 `Item`（商品），
这个关联命名为 `getPurchasedItems()`，关联声明如下代码示例所示：

```php
class Customer extends ActiveRecord
{
    // ...

    public function getPurchasedItems()
    {
        // 客户的商品，将 Item 中的 'id' 列与 OrderItem 中的 'item_id' 相匹配
        return $this->hasMany(Item::class, ['id' => 'item_id'])
                    ->via('orderItems');
    }

    public function getOrderItems()
    {
        // 客户订单中的商品，将 `Order` 的 'id' 列和 OrderItem 的 'order_id' 列相匹配
        return $this->hasMany(OrderItem::class, ['order_id' => 'id'])
                    ->via('orders');
    }

    public function getOrders()
    {
        // 见上述列子
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```


### 延迟加载和即时加载（Lazy Loading and Eager Loading） <span id="lazy-eager-loading"></span>

在 [访问关联数据](#accessing-relational-data) 中，我们解释说可以像问正常的对象属性那样
访问 Active Record 实例的关联属性。SQL 语句仅在
你第一次访问关联属性时执行。我们称这种关联数据访问方法为 *延迟加载*。
例如，

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$orders = $customer->orders;

// 没有 SQL 语句被执行
$orders2 = $customer->orders;
```

延迟加载使用非常方便。但是，当你需要访问相同的具有多个 Active Record 实例的关联属性时，
可能会遇到性能问题。请思考一下以下代码示例。
有多少 SQL 语句会被执行？

```php
// SELECT * FROM `customer` LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SELECT * FROM `order` WHERE `customer_id` = ...
    $orders = $customer->orders;
}
```

你瞅瞅，上面的代码会产生 101 次 SQL 查询！
这是因为每次你访问 for 循环中不同的 `Customer` 对象的 `orders` 关联属性时，SQL 语句
都会被执行一次。

为了解决上述的性能问题，你可以使用所谓的 *即时加载*，如下所示，

```php
// SELECT * FROM `customer` LIMIT 100;
// SELECT * FROM `orders` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->with('orders')
    ->limit(100)
    ->all();

foreach ($customers as $customer) {
    // 没有任何的 SQL 执行
    $orders = $customer->orders;
}
```

通过调用 [[yii\db\ActiveQuery::with()]] 方法，你使 Active Record 在一条 SQL 语句里就返回了这 100 位客户的订单。
结果就是，你把要执行的 SQL 语句从 101 减少到 2 条！

你可以即时加载一个或多个关联。 你甚至可以即时加载 *嵌套关联* 。嵌套关联是一种
在相关的 Active Record 类中声明的关联。例如，`Customer` 通过 `orders` 关联属性 与 `Order` 相关联，
`Order` 与 `Item` 通过 `items` 关联属性相关联。 当查询 `Customer` 时，您可以即时加载
通过嵌套关联符 `orders.items` 关联的 `items`。

以下代码展示了 [[yii\db\ActiveQuery::with()|with()]] 的各种用法。我们假设 `Customer` 类
有两个关联 `orders` 和 `country`，而 `Order` 类有一个关联 `items`。

```php
//  即时加载 "orders" and "country"
$customers = Customer::find()->with('orders', 'country')->all();
// 等同于使用数组语法 如下
$customers = Customer::find()->with(['orders', 'country'])->all();
// 没有任何的 SQL 执行
$orders= $customers[0]->orders;
// 没有任何的 SQL 执行
$country = $customers[0]->country;

// 即时加载“订单”和嵌套关系“orders.items”
$customers = Customer::find()->with('orders.items')->all();
// 访问第一个客户的第一个订单中的商品
// 没有 SQL 查询执行
$items = $customers[0]->orders[0]->items;
```

你也可以即时加载更深的嵌套关联，比如 `a.b.c.d`。所有的父关联都会被即时加载。
那就是, 当你调用 [[yii\db\ActiveQuery::with()|with()]] 来 with `a.b.c.d`, 你将即时加载
`a`, `a.b`, `a.b.c` and `a.b.c.d`。

> Tip: 一般来说，当即时加载 `N` 个关联，另有 `M` 个关联
  通过 [连接表](#junction-table) 声明，则会有 `N+M+1` 条 SQL 语句被执行。
  请注意这样的的嵌套关联 `a.b.c.d` 算四个关联。

当即时加载一个关联，你可以通过匿名函数自定义相应的关联查询。
例如，

```php
// 查找所有客户，并带上他们国家和活跃订单
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

自定义关联查询时，应该将关联名称指定为数组的键
并使用匿名函数作为相应的数组的值。匿名函数将接受一个 `$query` 参数
它用于表示这个自定义的关联执行关联查询的 [[yii\db\ActiveQuery]] 对象。
在上面的代码示例中，我们通过附加一个关于订单状态的附加条件来修改关联查询。

> Tip: 如果你在即时加载的关联中调用 [[yii\db\Query::select()|select()]] 方法，你要确保
> 在关联声明中引用的列必须被 select。否则，相应的模型（Models）可能
> 无法加载。例如，
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // $orders[0]->customer 会一直是 `null`。你应该这样写，以解决这个问题：
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### 关联关系的 JOIN 查询（Joining with Relations） <span id="joining-with-relations"></span>

> Tip: 这小节的内容仅仅适用于关系数据库，
  比如 MySQL，PostgreSQL 等等。

到目前为止，我们所介绍的关联查询，仅仅是使用主表列
去查询主表数据。实际应用中，我们经常需要在关联表中使用这些列。例如，
我们可能要取出至少有一个活跃订单的客户。为了解决这个问题，我们可以
构建一个 join 查询，如下所示：

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

> Tip: 在构建涉及 JOIN SQL 语句的连接查询时，清除列名的歧义很重要。
  通常的做法是将表名称作为前缀加到对应的列名称前。

但是，更好的方法是通过调用 [[yii\db\ActiveQuery::joinWith()]] 来利用已存在的关联声明：

```php
$customers = Customer::find()
    ->joinWith('orders')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->all();
```

两种方法都执行相同的 SQL 语句集。然而，后一种方法更干净、简洁。

默认的，[[yii\db\ActiveQuery::joinWith()|joinWith()]] 会使用 `LEFT JOIN` 去连接主表和关联表。
你可以通过 `$joinType` 参数指定不同的连接类型（比如 `RIGHT JOIN`）。
如果你想要的连接类型是 `INNER JOIN`，你可以直接用 [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] 方法代替。

调用 [[yii\db\ActiveQuery::joinWith()|joinWith()]] 方法会默认 [即时加载](#lazy-eager-loading) 相应的关联数据。
如果你不需要那些关联数据，你可以指定它的第二个参数 `$eagerLoading` 为 `false`。

> Note: 即使在启用即时加载的情况下使用 [[yii\db\ActiveQuery::joinWith()|joinWith()]] 或 [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]]，
  相应的关联数据也**不会**从这个 `JOIN` 查询的结果中填充。 
  因此，每个连接关系还有一个额外的查询，正如[即时加载](#lazy-eager-loading)部分所述。

和 [[yii\db\ActiveQuery::with()|with()]] 一样，你可以 join 多个关联表；你可以动态的自定义
你的关联查询；你可以使用嵌套关联进行 join。你也可以将 [[yii\db\ActiveQuery::with()|with()]]
和 [[yii\db\ActiveQuery::joinWith()|joinWith()]] 组合起来使用。例如：

```php
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->andWhere(['>', 'subtotal', 100]);
    },
])->with('country')
    ->all();
```

有时，当连接两个表时，你可能需要在 JOIN 查询的 `ON` 部分中指定一些额外的条件。
这可以通过调用 [[yii\db\ActiveQuery::onCondition()]] 方法来完成，如下所示：

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

以上查询取出 *所有* 客户，并为每个客户取回所有活跃订单。
请注意，这与我们之前的例子不同，后者仅取出至少有一个活跃订单的客户。

> Tip: 当通过 [[yii\db\ActiveQuery::onCondition()|onCondition()]] 修改 [[yii\db\ActiveQuery]] 时，
  如果查询涉及到 JOIN 查询，那么条件将被放在 `ON` 部分。如果查询不涉及
  JOIN ，条件将自动附加到查询的 `WHERE` 部分。
  因此，它可以只包含 包含了关联表的列 的条件。（译者注：意思是 onCondition() 中可以只写关联表的列，主表的列写不写都行）

#### 关联表别名（Relation table aliases） <span id="relation-table-aliases"></span>

如前所述，当在查询中使用 JOIN 时，我们需要消除列名的歧义。因此通常为一张表定义
一个别名。可以通过以下列方式自定义关联查询来设置关联查询的别名：

```php
$query->joinWith([
    'orders' => function ($q) {
        $q->from(['o' => Order::tableName()]);
    },
])
```

然而，这看起来很复杂和耦合，不管是对表名使用硬编码或是调用 `Order::tableName()`。
从 2.0.7 版本起，Yii 为此提供了一个快捷方法。您现在可以定义和使用关联表的别名，如下所示：

```php
// 连接 `orders` 关联表并根据 `orders.id` 排序
$query->joinWith(['orders o'])->orderBy('o.id');
```

上述语法适用于简单的关联。如果在 join 嵌套关联时，
需要用到中间表的别名，例如 `$query->joinWith(['orders.product'])`，
你需要嵌套 joinWith 调用，如下例所示：

```php
$query->joinWith(['orders o' => function($q) {
        $q->joinWith('product p');
    }])
    ->where('o.amount > 100');
```

### 反向关联（Inverse Relations） <span id="inverse-relations"></span>

两个 Active Record 类之间的关联声明往往是相互关联的。例如，`Customer` 是
通过 `orders` 关联到 `Order` ，而`Order` 通过 `customer` 又关联回到了 `Customer`。

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

现在考虑下面的一段代码：

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// SELECT * FROM `customer` WHERE `id` = 123
$customer2 = $order->customer;

// 显示 "not the same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

我们原本认为 `$customer` 和 `$customer2` 是一样的，但不是！其实他们确实包含相同的
客户数据，但它们是不同的对象。 访问 `$order->customer` 时，需要执行额外的 SQL 语句，
以填充出一个新对象 `$customer2`。

为了避免上述例子中最后一个 SQL 语句被冗余执行，我们应该告诉 Yii 
`customer` 是 `orders` 的 *反向关联*，可以通过调用 [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] 方法声明，
如下所示：

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

这样修改关联声明后：

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// No SQL will be executed
$customer2 = $order->customer;

// 输出 "same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

> Note: 反向关联不能用在有 [连接表](#junction-table) 关联声明中。
  也就是说，如果一个关联关系通过 [[yii\db\ActiveQuery::via()|via()]] 或 [[yii\db\ActiveQuery::viaTable()|viaTable()]] 声明，
  你就不能再调用 [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] 了。


## 保存关联数据（Saving Relations） <span id="saving-relations"></span>

在使用关联数据时，您经常需要建立不同数据之间的关联或销毁
现有关联。这需要为定义的关联的列设置正确的值。通过使用 Active Record，
你就可以编写如下代码：

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

// 为 Order 设置属性以定义与 "customer" 的关联关系
$order->customer_id = $customer->id;
$order->save();
```

Active Record 提供了 [[yii\db\ActiveRecord::link()|link()]] 方法，可以更好地完成此任务：

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

$order->link('customer', $customer);
```

[[yii\db\ActiveRecord::link()|link()]] 方法需要指定关联名
和要建立关联的目标 Active Record 实例。该方法将修改属性的值
以连接两个 Active Record 实例，并将其保存到数据库。在上面的例子中，它将设置 `Order` 实例的 `customer_id` 属性
为 `Customer` 实例的 `id` 属性的值，然后保存
到数据库。

> Note: 你不能关联两个新的 Active Record 实例。

使用 [[yii\db\ActiveRecord::link()|link()]] 的好处在通过 [junction table](#junction-table) 定义关系时更加明显。
例如，你可以使用以下代码关联 `Order` 实例
和 `Item` 实例：

```php
$order->link('items', $item);
```

上述代码会自动在 `order_item` 关联表中插入一行，以关联 order 和 item 这两个数据记录。

> Info: [[yii\db\ActiveRecord::link()|link()]] 方法在保存相应的 Active Record 实例时，
  将不会执行任何数据验证。在调用此方法之前，
  您应当验证所有的输入数据。

[[yii\db\ActiveRecord::link()|link()]] 方法的反向操作是 [[yii\db\ActiveRecord::unlink()|unlink()]] 方法，
这将可以断掉两个 Active Record 实例间的已经存在了的关联关系。例如，

```php
$customer = Customer::find()->with('orders')->where(['id' => 123])->one();
$customer->unlink('orders', $customer->orders[0]);
```

默认情况下，[[yii\db\ActiveRecord::unlink()|unlink()]] 方法将设置指定的外键值，
以把现有的关联指定为 `null`。此外，你可以选择通过将 `$delete` 参数设置为`true` 传递给方法，
删除包含此外键值的表记录行。
 
当关联关系中有连接表时，调用 [[yii\db\ActiveRecord::unlink()|unlink()]] 时，
如果 `$delete` 参数是 `true` 的话，将导致
连接表中的外键或相应的行被删除。


## 跨数据库关联（Cross-Database Relations） <span id="cross-database-relations"></span> 

Active Record 允许您在不同数据库驱动的 Active Record 类之间声明关联关系。
这些数据库可以是不同的类型（例如 MySQL 和 PostgreSQL ，或是 MS SQL 和 MongoDB），它们也可以运行在
不同的服务器上。你可以使用相同的语法来执行关联查询。例如，

```php
// Customer 对应的表是关系数据库中（比如 MySQL）的 "customer" 表
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // 一个 customer 有很多条评论（comments）
        return $this->hasMany(Comment::class, ['customer_id' => 'id']);
    }
}

// Comment 对应的是 MongoDB 数据库中的  "comment" 集合（译者注：MongoDB 中的集合相当于 MySQL 中的表）
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // 一条评论对应一位 customer
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}

$customers = Customer::find()->with('comments')->all();
```

本节中描述的大多数关联查询功能，你都可以抄一抄。
 
> Note: [[yii\db\ActiveQuery::joinWith()|joinWith()]] 这个功能限制于某些数据库是否支持跨数据库 JOIN 查询。
  因此，你再上述的代码里就不能用此方法了，因为 MongoDB 不支持 JOIN 查询。


## 自定义查询类（Customizing Query Classes） <span id="customizing-query-classes"></span>

默认情况下，[[yii\db\ActiveQuery]] 支持所有 Active Record 查询。要在 Active Record 类中使用自定义的查询类，
您应该重写 [[yii\db\ActiveRecord::find()]] 方法并返回一个你自定义查询类的实例。 
例如，

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

现在，对于 `Comment` 类，不管你执行查询（比如 `find()`、`findOne()`），还是定义一个关联（比如 `hasOne()`），
你都将调用到 `CommentQuery` 实例，而不再是 `ActiveQuery` 实例。

现在你可以定义 `CommentQuery` 类了，发挥你的奇技淫巧，以改善查询构建体验。例如，

```php
// file CommentQuery.php
namespace app\models;

use yii\db\ActiveQuery;

class CommentQuery extends ActiveQuery
{
    // 默认加上一些条件（可以跳过）
    public function init()
    {
        $this->andOnCondition(['deleted' => false]);
        parent::init();
    }

    // ... 在这里加上自定义的查询方法 ...

    public function active($state = true)
    {
        return $this->andOnCondition(['active' => $state]);
    }
}
```

> Note: 作为 [[yii\db\ActiveQuery::onCondition()|onCondition()]] 方法的替代方案，你应当调用
  [[yii\db\ActiveQuery::andOnCondition()|andOnCondition()]] 或 [[yii\db\ActiveQuery::orOnCondition()|orOnCondition()]] 方法来附加新增的条件，
  不然在一个新定义的查询方法，已存在的条件可能会被覆盖。

然后你就可以先下面这样构建你的查询了：

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

> Tip: 在大型项目中，建议您使用自定义查询类来容纳大多数与查询相关的代码，
  以使 Active Record 类保持简洁。

您还可以在 `Comment` 关联关系的定义中或在执行关联查询时，使用刚刚新建查询构建方法：

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::class, ['customer_id' => 'id'])->active();
    }
}

$customers = Customer::find()->joinWith('activeComments')->all();

// 或者这样
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

> Tip: 在 Yii 1.1 中，有个概念叫做 *命名范围*。命名范围在 Yii 2.0 中不再支持，
  你依然可以使用自定义查询类、查询方法来达到一样的效果。


## 选择额外的字段（Selecting extra fields）

当 Active Record 实例从查询结果中填充时，从数据结果集中，
其属性的值将被相应的列填充。

你可以从查询中获取其他列或值，并将其存储在 Active Record 活动记录中。
例如，假设我们有一个名为 `room` 的表，其中包含有关酒店可用房间的信息。
每个房间使用字段 `length`，`width`，`height` 存储有关其空间大小的信息。
想象一下，我们需要检索出所有可用房间的列表，并按照体积大小倒序排列。
你不可能使用 PHP 来计算体积，但是，由于我们需要按照它的值对这些记录进行排序，你依然需要 `volume` （体积）
来显示在这个列表中。
为了达到这个目标，你需要在你的 `Room` 活动记录类中声明一个额外的字段，它将存储 `volume` 的值：

```php
class Room extends \yii\db\ActiveRecord
{
    public $volume;

    // ...
}
```

然后，你需要撰写一个查询，它可以计算房间的大小并执行排序：

```php
$rooms = Room::find()
    ->select([
        '{{room}}.*', // select all columns
        '([[length]] * [[width]] * [[height]]) AS volume', // 计算体积
    ])
    ->orderBy('volume DESC') // 使用排序
    ->all();

foreach ($rooms as $room) {
    echo $room->volume; // 包含了由 SQL 计算出的值
}
```

额外字段的特性对于聚合查询非常有用。
假设您需要显示一系列客户的订单数量。
首先，您需要使用 `orders` 关系声明一个 `Customer` 类，并指定额外字段来存储 count 结果：

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

然后你可以编写一个查询来 JOIN 订单表，并计算订单的总数：

```php
$customers = Customer::find()
    ->select([
        '{{customer}}.*', // select customer 表所有的字段
        'COUNT({{order}}.id) AS ordersCount' // 计算订单总数
    ])
    ->joinWith('orders') // 连接表
    ->groupBy('{{customer}}.id') // 分组查询，以确保聚合函数生效
    ->all();
```

使用此方法的一个缺点是，如果数据不是从 SQL 查询上加载的，它必须再单独计算一遍。
因此，如果你通过常规查询获取个别的数据记录时，它没有额外的 select 语句，那么它
将无法返回额外字段的实际值。新保存的记录一样会发生这种情。

```php
$room = new Room();
$room->length = 100;
$room->width = 50;
$room->height = 2;

$room->volume; // 为 `null`, 因为它没有被声明（赋值）
```

通过 [[yii\db\BaseActiveRecord::__get()|__get()]] 和 [[yii\db\BaseActiveRecord::__set()|__set()]] 魔术方法
我们可以将属性赋予行为特性：

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

当 select 查询不提供 volume 体积时，这模型将能够自动计算体积的值出来，
当访问模型的属性的时候。

当定义关联关系的时候，你也可以计算聚合字段：

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
            return null; // 这样可以避免调用空主键进行查询
        }

        if ($this->_ordersCount === null) {
            $this->setOrdersCount($this->getOrders()->count()); // 根据关联关系按需计算聚合字段
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

使用此代码，如果 'select' 语句中存在 'ordersCount' - 它会从查询结果集获取数据填充 `Customer::ordersCount` 属性，
否则它会在被访问的时候，使用 `Customer::orders` 关联按需计算。

这种方法也适用于创建一些关联数据的快捷访问方式，特别是对于聚合。
例如：

```php
class Customer extends \yii\db\ActiveRecord
{
    /**
     * 为聚合数据定义一个只读的虚拟属性
     */
    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; //  这样可以避免调用空主键进行查询
        }
        
        return empty($this->ordersAggregation) ? 0 : $this->ordersAggregation[0]['counted'];
    }

    /**
     * 声明一个常规的 'orders' 关联
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

    /**
     * 基于 'orders' 关联，声明一个用于查询聚合的新关联
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
    echo $customer->ordersCount; // 输出关联的聚合数据，而不需要额外的查询，因为我们用了即时加载
}

$customer = Customer::findOne($pk);
$customer->ordersCount; // 从延迟加载的关联中，输出聚合数据
```
