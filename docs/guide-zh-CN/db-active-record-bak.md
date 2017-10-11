Active Record
=============

[Active Record](http://zh.wikipedia.org/wiki/Active_Record) （活动记录，以下简称 AR）提供了一个面向对象的接口，
用以访问和操作数据库中的数据。一个 AR 类关联一张数据表，
每个 AR 对象对应表中的一行，对象的属性（即 AR 的特性Attribute）映射到数据行的对应列。
即一条活动记录（AR 对象）对应数据表的一行，AR 对象的属性则映射该行的相应列。
您可以直接以面向对象的方式来操纵数据表中的数据，


例如，假定 `Customer` AR 类关联着 `customer` 表，
且该类的 `name` 属性代表 `customer` 表的 `name` 列。
你可以写以下代码来哉 `customer` 表里插入一行新的记录:

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

对于 MySql，上面的代码和使用下面的原生 SQL 语句是等效的，但显然前者更直观，
更不易出错，并且面对不同的数据库系统（DBMS, Database Management System）时更不容易产生兼容性问题。

```php
$db->createCommand('INSERT INTO `customer` (`name`) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

下面是所有目前被 Yii 的 AR 功能所支持的数据库列表：

* MySQL 4.1 及以上: 通过 [[yii\db\ActiveRecord]] 支持
* PostgreSQL 7.3 及以上：通过 [[yii\db\ActiveRecord]] 支持
* SQLite 2 and 3: 通过 [[yii\db\ActiveRecord]] 支持
* Microsoft SQL Server 2008 及以上：通过 [[yii\db\ActiveRecord]] 支持
* Oracle: 通过 [[yii\db\ActiveRecord]] 支持
* CUBRID 9.3 及以上：通过 [[yii\db\ActiveRecord]] 支持 (提示， 由于 CUBRID PDO 扩展的 [bug](http://jira.cubrid.org/browse/APIS-658)，
  给变量加引用将不起作用，所以你得使用 CUBRID 9.3 客户端及服务端。
* Sphinx: 通过 [[yii\sphinx\ActiveRecord]] 支持, 依赖 `yii2-sphinx` 扩展
* ElasticSearch: 通过 [[yii\elasticsearch\ActiveRecord]] 支持, 依赖 `yii2-elasticsearch` 扩展

此外，Yii 的 AR 功能还支持以下 NoSQL 数据库：

* Redis 2.6.12 及以上: 通过 [[yii\redis\ActiveRecord]] 支持, 依赖 `yii2-redis` 扩展
* MongoDB 1.3.0 及以上: 通过 [[yii\mongodb\ActiveRecord]] 支持, 依赖 `yii2-mongodb` 扩展

在本教程中，我们会主要描述对关系型数据库的 AR 用法。
然而，绝大多数的内容在 NoSQL 的 AR 里同样适用。


## 声明 AR 类 <span id="declaring-ar-classes"></span>

要想声明一个 AR 类，你需要定义几个类 继承 [[yii\db\ActiveRecord]]. 

### 设置表的名称

默认的，每个 AR 类关联各自的数据库表。
经过 [[yii\helpers\Inflector::camel2id()]] 处理，[[yii\db\ActiveRecord::tableName()|tableName()]] 方法默认返回的表名称是通过类名转换来得。 
如果这个默认名称不正确，你得重写这个方法。

此外， [[yii\db\Connection::$tablePrefix|tablePrefix]] 表前缀也会起作用。 例如， 如果
 [[yii\db\Connection::$tablePrefix|tablePrefix]] 表前缀是 `tbl_`, `Customer` 的类名将转换成 `tbl_customer` 表名， `OrderItem` 转换成 `tbl_order_item`. 

如果你定义的表名是 `{{%TableName}}`, 百分比字符 `%` 会被替换成表前缀。
例如, `{{%post}}` 会变成 `{{tbl_post}}`。 表名两边的括号会被 [SQL 查询引用](db-dao.md#quoting-table-and-column-names) 处理。


下面的例子中，我们给 `customer` 数据库表定义叫 `Customer` 的 AR 类。

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @return string AR 类关联的数据库表名称
     */
    public static function tableName()
    {
        return '{{customer}}';
    }
}
```

### 将 AR 称为模型吧
AR 实例称为 [模型](structure-models.md)。因此, 我们通常将 AR 类
放在 `app\models` 命名空间下（或者其他保存模型的命名空间）。

因为 AR [[yii\db\ActiveRecord]] 继承了模型 [[yii\base\Model]], 它就拥有所有 [模型](structure-models.md) 特性，
比如说属性（attributes），检验规则（rules），数据序列化，等等。


## 建立数据库连接 <span id="db-connection"></span>

活动记录 AR 默认使用 `db` [组件](structure-application-components.md) 
作为连接器 [[yii\db\Connection|DB connection]] 访问和操作数据库数据。 
基于 [数据库访问](db-dao.md) 中的解释，你可以在系统配置中
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


## 查询数据 <span id="querying-data"></span>

定义 AR 类后，你可以从相应的数据库表中查询数据。
查询过程大致如下三个步骤：

1. 通过 [[yii\db\ActiveRecord::find()]] 方法创建一个新的查询生成器对象；
2. 使用 [查询生成器的构建方法](db-query-builder.md#building-queries) 来构建你的查询；
3. 调用 [查询生成器的查询方法](db-query-builder.md#query-methods) 来取出数据到 AR 实例中。

你瞅瞅, 是不是跟 [查询生成器](db-query-builder.md) 的步骤差不多。
唯一有区别的地方在于你用 [[yii\db\ActiveRecord::find()]] 去获得一个新的查询生成器对象，这个对象是 [[yii\db\ActiveQuery]]，
而不是使用 `new` 操作符创建一个查询生成器对象。

下面是一些栗子，介绍如何使用 AR 查询数据：

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

> 提示：由于 [[yii\db\ActiveQuery]] 继承 [[yii\db\Query]]，你可以使用 [查询生成器](db-query-builder.md) 章节里所描述的所有查询方法。


根据主键获取数据行是比较常见的操作，所以 Yii 
提供了两个快捷方法：

- [[yii\db\ActiveRecord::findOne()]]： 返回一个 AR 实例，填充于查询结果的第一行数据。
- [[yii\db\ActiveRecord::findAll()]]：返回一个 AR 实例的数据，填充于查询结果的全部数据。

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

> 提示：[[yii\db\ActiveRecord::findOne()]] 和 [[yii\db\ActiveQuery::one()]] 都不会添加 `LIMIT 1` 到
  生成的 SQL 语句中。如果你的查询会返回很多行的数据，
  你明确的应该加上 `limit(1)` 来提高性能，比如 `Customer::find()->limit(1)->one()`。

除了使用查询生成器的方法之外，你还可以书写原生的 SQL 语句来查询数据，并填充结果集到 AR 对象中。
通过使用 [[yii\db\ActiveRecord::findBySql()]] 方法:

```php
// 返回所有不活跃的客户
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

不要在 [[yii\db\ActiveRecord::findBySql()|findBySql()]] 方法后加其他查询方法了，
多余的查询方法都会被忽略。


## 访问数据 <span id="accessing-data"></span>

如上所述，从数据库返回的数据被填充到 AR 实例中，
查询结果的每一行对应于单个 AR 实例。
您可以通过 AR 实例的属性来访问列值，例如，

```php
// "id" 和 "email" 是 "customer" 表中的列名
$customer = Customer::findOne(123);
$id = $customer->id;
$email = $customer->email;
```

> 提示：AR 的属性以区分大小写的方式为相关联的表列命名的。
  Yii 会自动为关联表的每一列定义 AR 中的一个属性。
  您不应该重新声明任何属性。

由于 AR 的属性以表的列名命名，可能你会发现你正在编写像这样的 PHP 代码 :
 `$ customer-> first_name`，如果你的表的列名是使用下划线分隔的，那么属性名中的单词
以这种方式命名。 如果您担心代码风格一致性的问题，那么你应当重命名相应的表列名
（例如使用骆驼拼写法）。


### 数据转换 <span id="data-transformation"></span>

常常遇到，要输入或显示的数据是一种格式，而要将其存储在数据库中是另一种格式。
例如，在数据库中，您将客户的生日存储为 UNIX 时间戳（虽然这不是一个很好的设计），
而在大多数情况下，你想以字符串 `'YYYY/MM/DD'` 的格式处理生日数据。
为了实现这一目标，您可以在 `Customer` 中定义 *数据转换* 方法
定义 AR 类如下：

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

现在你的 PHP 代码中，你可以访问 `$ customer-> birthdayText`，
来以 `'YYYY/MM/DD'` 的格式输入和显示客户生日，而不是访问`$ customer-> birthday`。

> 提示：上述示例显示了以不同格式转换数据的通用方法。如果你正在使用
> 日期值，您可以使用 [DateValidator](tutorial-core-validators.md#date) 和 [[yii\jui\DatePicker|DatePicker]] 来操作，
> 这将更易用，更强大。


### 以数组形式获取数据 <span id="data-in-arrays"></span>

通过 AR 对象获取数据十分方便灵活，与此同时，当你需要返回大量的数据的时候，
这样的做法并不令人满意，因为这将导致大量内存占用。在这种情况下，您可以
在查询方法前调用 [[yii\db\ActiveQuery::asArray()|asArray()]] 方法，来获取 PHP 数组形式的结果：

```php
// 返回所有客户
// 每个客户返回一个关联数组
$customers = Customer::find()
    ->asArray()
    ->all();
```

> 提示：虽然这种方法可以节省内存并提高性能，但它更靠近较低的 DB 抽象层
  你将失去大部分的 AR 提供的功能。 一个非常重要的区别在于列值的数据类型。
  当您在 AR 实例中返回数据时，列值将根据实际列类型，自动类型转换；
  然而，当您以数组返回数据时，列值将为
  字符串（因为它们是没有处理过的 PDO 的结果），不管它们的实际列是什么类型。
   

### 批量获取数据 <span id="data-in-batches"></span>

在 [查询生成器](db-query-builder.md) 中，我们已经解释说可以使用 *批处理查询* 来最小化你的内存使用，
每当从数据库查询大量数据。你可以在 AR 中使用同样的技巧。例如，

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


## 保存数据 <span id="inserting-updating-data"></span>

使用 AR （活动记录），您可以通过以下步骤轻松地将数据保存到数据库：

1. 准备一个 AR 实例
2. 将新值赋给 AR 的属性
3. 调用 [[yii\db\ActiveRecord::save()]] 保存数据到数据库中。

举个栗子,

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

[[yii\db\ActiveRecord::save()|save()]] 方法可能插入或者更新表的记录，这取决于 AR 实例的状态。
如果实例通过 `new` 操作符实例化，调用 [[yii\db\ActiveRecord::save()|save()]] 方法将插入新记录；
如果实例是一个查询方法的结果，调用 [[yii\db\ActiveRecord::save()|save()]] 方法
将更新这个实例对应的表记录行。

你可以通过检查 AR 实例的 [[yii\db\ActiveRecord::isNewRecord|isNewRecord]] 属性值来区分这两个状态。
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

> 提示：你可以直接调用 [[yii\db\ActiveRecord::insert()|insert()]] 或者 [[yii\db\ActiveRecord::update()|update()]]
  方法来插入或更新一条记录。
  

### 数据验证 <span id="data-validation"></span>

因为 [[yii\db\ActiveRecord]] 继承于 [[yii\base\Model]]，它共享相同的 [输入验证](input-validation.md) 功能。
你可以通过重写 [[yii\db\ActiveRecord::rules()|rules()]] 方法声明验证规则并执行，
通过调用 [[yii\db\ActiveRecord::validate()|validate()]] 方法进行数据验证。
  
当你调用 [[yii\db\ActiveRecord::save()|save()]] 时，默认情况下会自动调用 [[yii\db\ActiveRecord::validate()|validate()]]。
只有当验证通过时，它才会真正地保存数据; 否则将简单地返回`false`，
您可以检查 [[yii\db\ActiveRecord::errors|errors]] 属性来获取验证过程的错误消息。

> 提示：如果你确定你的数据不需要验证（比如说数据来自可信的场景），
  你可以调用 `save(false)` 来跳过验证过程。


### 块赋值 <span id="massive-assignment"></span>

和普通的 [models](structure-models.md) 一样，你亦可以享受 AR 实例的 [块赋值](structure-models.md#massive-assignment) 特性。
使用此功能，您可以在单个 PHP 语句中，给 AR 实例的多个属性批量赋值，
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


### 更新计数 <span id="updating-counters"></span>

在数据库表中增加或减少一个字段的值是个常见的任务。我们将这些列称为“计数列”。
您可以使用 [[yii\db\ActiveRecord::updateCounters()|updateCounters()]] 更新一个或多个计数列。
例如，

```php
$post = Post::findOne(100);

// UPDATE `post` SET `view_count` = `view_count` + 1 WHERE `id` = 100
$post->updateCounters(['view_count' => 1]);
```

> 注：如果你使用 [[yii\db\ActiveRecord::save()]] 更新一个计数列，你最终将得到错误的结果，
  因为可能发生这种情况，多个请求间并发读写同一个计数列。


### 脏属性 <span id="dirty-attributes"></span>

当您调用 [[yii\db\ActiveRecord::save()|save()]] 保存 AR 实例时，只有 *脏属性*
被保存。如果一个属性的值已被修改，则会被认为是 *脏*，因为它是从 DB 加载出来的或者
刚刚保存到 DB 。请注意，无论如何 AR 都会执行数据验证
不管有没有脏属性。

AR 自动维护脏属性列表。 它保存所有属性的旧值，
并其与最新的属性值进行比较，就是酱紫个道理。你可以调用 [[yii\db\ActiveRecord::getDirtyAttributes()]] 
获取当前的脏属性。你也可以调用 [[yii\db\ActiveRecord::getDirtyAttributes()]] 
将属性显式标记为脏。

如果你有需要获取属性原先的值，你可以调用
[[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] 或者 [[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]].

> 注：属性新旧值的比较是用 `===` 操作符，所以一样的值但类型不同，
> 依然被认为是脏的。当模型从 HTML 表单接收用户输入时，通常会出现这种情况，
> 其中每个值都表示为一个字符串类型。
> 为了确保正确的类型，比如，整型需要用 [过滤验证器](input-validation.md#data-filtering)：
> `['attributeName', 'filter', 'filter' => 'intval']`。其他 PHP 类型转换函数一样适用，像
> [intval()](http://php.net/manual/en/function.intval.php)， [floatval()](http://php.net/manual/en/function.floatval.php)，
> [boolval](http://php.net/manual/en/function.boolval.php)， 等等

### 默认属性值 <span id="default-attribute-values"></span>

某些表列可能在数据库中定义了默认值。有时，你可能想预先填充
具有这些默认值的 AR 实例的 Web 表单。 为了避免再次写入相同的默认值，
您可以调用 [[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]] 来填充 DB 定义的默认值
进入相应的 AR 属性：

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz 将被 “zyz” 列定义的默认值赋值
```


### 属性类型转换 <span id="attributes-typecasting"></span>

在查询结果填充 [[yii\db\ActiveRecord]] 活动记录时，将自动对其属性值执行类型转换，基于
[数据库表模式](db-dao.md#database-schema) 中的信息。 这允许从数据表中获取数据，
声明为整型的，使用 PHP 整型填充 AR 实例，布尔值（boolean）的也用布尔值填充 AR 实例，等等。
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

> 提示： 你可以使用 [[yii\behaviors\AttributeTypecastBehavior]] 来简化属性的类型转换
  在 ActiveRecord 验证或者保存过程中。


### Updating Multiple Rows <span id="updating-multiple-rows"></span>

上述方法都可以用于单个 AR 实例，以插入或更新单条
表数据行。 要同时更新多个数据行，你应该调用 [[yii\db\ActiveRecord::updateAll()|updateAll()]]
这是一个静态方法。

```php
// UPDATE `customer` SET `status` = 1 WHERE `email` LIKE `%@example.com%`
Customer::updateAll(['status' => Customer::STATUS_ACTIVE], ['like', 'email', '@example.com']);
```

同样, 你可以调用 [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] 同时更新多条记录的计数列。


```php
// UPDATE `customer` SET `age` = `age` + 1
Customer::updateAllCounters(['age' => 1]);
```


## 删除数据 <span id="deleting-data"></span>

要删除单行数据，首先获取与该行对应的 AR 实例，然后调用
[[yii\db\ActiveRecord::delete()]] 方法。

```php
$customer = Customer::findOne(123);
$customer->delete();
```

你可以调用 [[yii\db\ActiveRecord::deleteAll()]] 方法删除多行甚至全部的数据。例如，

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> 提示：不要随意使用 [[yii\db\ActiveRecord::deleteAll()|deleteAll()]] 它真的会
  清空你表里的数据，因为你指不定啥时候犯二。


## AR 的生命周期 <span id="ar-life-cycles"></span>

当你实现各种功能的时候，会发现了解 AR 的生命周期很重要。
在每个生命周期中，一系列的方法将被调用执行，您可以重写这些方法
以定制你要的生命周期。您还可以响应触发某些 AR 事件
以便在生命周期中注入您的自定义代码。这些事件在开发 AR 的 [行为](concept-behaviors.md)时特别有用，
通过行为可以定制 AR 生命周期的 。

下面，我们将总结各种 AR 的生命周期，以及生命周期中
所涉及的各种方法、事件。


### 实例化生命周期 <span id="new-instance-life-cycle"></span>

当通过 `new` 操作符新建一个 AR 实例时，会发生以下生命周期：

1. 类的构造函数调用.
2. [[yii\db\ActiveRecord::init()|init()]]：触发 [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] 事件。


### 查询数据生命周期 <span id="querying-data-life-cycle"></span>

当通过 [查询方法](#querying-data) 查询数据时，每个新填充出来的 AR 实例
将发生下面的生命周期：

1. 类的构造函数调用.
2. [[yii\db\ActiveRecord::init()|init()]]：触发 [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] 事件。
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]：触发 [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] 事件。


### 保存数据生命周期 <span id="saving-data-life-cycle"></span>

当通过 [[yii\db\ActiveRecord::save()|save()]] 插入或更新 AR 实例时
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
   

### 删除数据生命周期 <span id="deleting-data-life-cycle"></span>

当通过 [[yii\db\ActiveRecord::delete()|delete()]] 删除 AR 实例时，
会发生以下生命周期：

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]：触发
   [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]] 事件。 如果这方法返回 `false` 
   或者 [[yii\base\ModelEvent::isValid]] 值为 `false`，接下来的步骤都会被跳过。
2. 执行真正的数据删除。
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]：触发
   [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]] 事件。


> 提示：调用以下方法则不会启动上述的任何生命周期，
> 因为这些方法直接操作数据库，而不是基于 AR 模型：
>
> - [[yii\db\ActiveRecord::updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()]] 

### 刷新数据生命周期 <span id="refreshing-data-life-cycle"></span>

当通过 [[yii\db\ActiveRecord::refresh()|refresh()]] 刷新 AR 实例时，
如刷新成功方法返回 `true`，那么 [[yii\db\ActiveRecord::EVENT_AFTER_REFRESH|EVENT_AFTER_REFRESH]] 事件将被触发。


## 事务操作 <span id="transactional-operations"></span>

AR 活动记录有两种方式来使用 [事务](db-dao.md#performing-transactions)。

第一种方法是在事务块中显式地包含 AR 的各个方法调用，如下所示，

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

> 提示：在上面的代码中，我们有两个catch块用于兼容
> PHP 5.x 和 PHP 7.x。 `\Exception` 继承于 [`\Throwable` interface](http://php.net/manual/en/class.throwable.php)
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

## 乐观锁 <span id="optimistic-locks"></span>

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

1. 在与 AR 类相关联的 DB 表中创建一个列，以存储每行的版本号。
   这个列应当是长整型（在 MySQL 中是  `BIGINT DEFAULT 0`）。
2. 重写 [[yii\db\ActiveRecord::optimisticLock()]] 方法返回这个列的命名。
3. 在用于用户填写的 Web 表单中，添加一个隐藏字段（hidden field）来存储正在更新的行的当前版本号。
   （AR 类中）版本号这个属性你要自行写进 rules() 方法并自己验证一下。
4. 在使用 AR 更新数据的控制器动作中，要捕获（try/catch） [[yii\db\StaleObjectException]] 异常。
   实现一些业务逻辑来解决冲突（例如合并更改，提示陈旧的数据等等）。
   
举个栗子，假定版本列被命名为 `version`。您可以使用下面的代码来实现乐观锁。


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
```


## 使用关联数据 <span id="relational-data"></span>

除了处理单个数据库表之外，AR 还可以将相关数据集中进来，
使其可以通过原始数据轻松访问。 例如，客户数据与订单数据相关
因为一个客户可能已经存放了一个或多个订单。这种关系通过适当的声明，
你可以使用 `$customer->orders` 表达式访问客户的订单信息
这表达式将返回包含 `Order` AR 实例的客户订单信息的数组。


### 声明关联关系 <span id="declaring-relations"></span>

你必须先在 AR 类中定义关联关系，才能使用 AR 的关联数据。
简单地为每个需要定义关联关系声明一个 *关联方法* 即可，如下所示，

```php
class Customer extends ActiveRecord
{
    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    // ...

    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
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
- 相关联 AR 类名：用来指定为 [[yii\db\ActiveRecord::hasMany()|hasMany()]] 或者 
  [[yii\db\ActiveRecord::hasOne()|hasOne()]] 方法的第一个参数。
  推荐的做法是调用 `Xyz::className()` 来获取类名称的字符串，以便您
  可以使用 IDE 的自动补全，以及让编译阶段的错误检测生效。
- 两组数据的关联列：用以指定两组数据相关的列（hasOne()/hasMany() 的第二个参数）。
  数组的值填的是主数据的列（当前要声明关联的 AR 类为主数据），
  而数组的键要填的是相关数据的列。

  一个简单的口诀，先附表的主键，后主表的主键。
  正如上面的例子，`customer_id` 是 `Order` 的属性，而 `id`是 `Customer` 的属性。
  （译者注：hasMany() 的第二个参数，这个数组键值顺序不要弄反了）
  

### 访问关联数据 <span id="accessing-relational-data"></span>

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

> 提示：当你通过 getter 方法 `getXyz()` 声明了一个叫 `xyz` 的关联属性，你就可以像
  [属性](concept-properties.md) 那样访问 `xyz`。注意这个命名是区分大小写的。
  
如果使用 [[yii\db\ActiveRecord::hasMany()|hasMany()]] 声明关联关系，则访问此关联属性
将返回相关的 AR 实例的数组; 
如果使用 [[yii\db\ActiveRecord::hasOne()|hasOne()]] 声明关联关系，访问此关联属性
将返回相关的 AR 实例，如果没有找到相关数据的话，则返回 `null`。

当你第一次访问关联属性时，将执行 SQL 语句获取数据，如
上面的例子所示。如果再次访问相同的属性，将返回先前的结果，而不会重新执行
SQL 语句。要强制重新执行 SQL 语句，你应该先 unset 这个关联属性，
如：`unset（$ customer-> orders）`。

> 提示：虽然这个概念跟 这个 [属性](concept-properties.md) 特性很像，
> 但是还是有一个很重要的区别。普通对象属性的属性值与其定义的 getter 方法的类型是相同的。
> 而关联方法返回的是一个 [[yii\db\ActiveQuery]] 活动查询生成器的实例。只有当访问关联属性的的时候，
> 才会返回 [[yii\db\ActiveRecord]] AR 实例，或者 AR 实例组成的数组。
> 
> ```php
> $customer->orders; // 获得 `Order` 对象的数组
> $customer->getOrders(); // 返回 ActiveQuery 类的实例
> ```
> 
> 这对于创建自定义查询很有用，下一节将对此进行描述。


### 动态关联查询 <span id="dynamic-relational-query"></span>

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
动态关系查询。例如，您可以声明一个 `bigOrders`  关联如下，

```php
class Customer extends ActiveRecord
{
    public function getBigOrders($threshold = 100) // 老司机的提醒：$threshold 参数一定一定要给个默认值
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])
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


### 中间关联表 <span id="junction-table"></span>

在数据库建模中，当两个关联表之间的对应关系是多对多时，
通常会引入一个 [连接表](https://en.wikipedia.org/wiki/Junction_table)。例如，`order` 表
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
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
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
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
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


### 延迟加载和即时加载（又称惰性加载与贪婪加载） <span id="lazy-eager-loading"></span>

在 [访问关联数据](#accessing-relational-data) 中，我们解释说可以像问正常的对象属性那样
访问 AR 实例的关联属性。SQL 语句仅在
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

延迟加载使用非常方便。但是，当你需要访问相同的具有多个 AR 实例的关联属性时，
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

通过调用 [[yii\db\ActiveQuery::with()]] 方法，你使 AR 在一条 SQL 语句里就返回了这 100 位客户的订单。
结果就是，你把要执行的 SQL 语句从 101 减少到 2 条！

你可以即时加载一个或多个关联。 你甚至可以即时加载 *嵌套关联* 。嵌套关联是一种
在相关的 AR 类中声明的关联。例如，`Customer` 通过 `orders` 关联属性 与 `Order` 相关联，
`Order` 与 `Item` 通过 `items` 关联属性相关联。 当查询 `Customer` 时，您可以即时加载
通过嵌套关联符 `orders.items` 关联的 `items`。

以下代码展示了 [[yii\db\ActiveQuery::with()|with()]] 的各种用法。我们假设 `Customer` 类
有两个关联 `orders` 和 `country` ，而 `Order` 类有一个关联 `items`。

```php
//  即时加载 "orders" and "country"
$customers = Customer::find()->with('orders', 'country')->all();
// 等同于使用数组语法 如下
$customers = Customer::find()->with(['orders', 'country'])->all();
// 没有任何的 SQL 执行
$orders= $customers[0]->orders;
// 没有任何的 SQL 执行
$country = $customers[0]->country;

// eager loading "orders" and the nested relation "orders.items"
$customers = Customer::find()->with('orders.items')->all();
// access the items of the first order of the first customer
// no SQL executed
$items = $customers[0]->orders[0]->items;
```

你也可以即时加载更深的嵌套关联，比如 `a.b.c.d`。所有的父关联都会被即时加载。
那就是, 当你调用 [[yii\db\ActiveQuery::with()|with()]] 来 with `a.b.c.d`, 你将即时加载
`a`, `a.b`, `a.b.c` and `a.b.c.d`。

> 提示：一般来说，当即时加载 `N` 个关联，另有 `M` 个关联
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

> 提示：如果你在即时加载的关联中调用 [[yii\db\Query::select()|select()]] 方法，你要确保
> 在关联声明中引用的列必须被 select。否则，相应的模型（Models）可能
> 无法加载。例如，
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // $orders[0]->customer 会一直是 `null`。你应该这样写，以解决这个问题：
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### 关联关系的 JOIN 查询 <span id="joining-with-relations"></span>

> 提示：这小节的内容仅仅适用于关系数据库，
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

> 提示：在构建涉及 JOIN SQL 语句的连接查询时，清除列名的歧义很重要。
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
如果你不需要那些关联数据，你可以指定它的第二个参数 $eagerLoading` 为 `false`。

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
港真，这与我们之前的例子不同，后者仅取出至少有一个活跃订单的客户。

> 提示：当通过 [[yii\db\ActiveQuery::onCondition()|onCondition()]] 修改 [[yii\db\ActiveQuery]] 时，
  如果查询涉及到 JOIN 查询，那么条件将被放在 `ON` 部分。如果查询不涉及
  JOIN ，条件将自动附加到查询的 `WHERE` 部分。
  因此，它可以只包含 包含了关联表的列 的条件。（译者注：意思是 onCondition() 中可以只写关联表的列，主表的列写不写都行）

#### Relation table aliases <span id="relation-table-aliases"></span>

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

### Inverse Relations <span id="inverse-relations"></span>

Relation declarations are often reciprocal between two Active Record classes. For example, `Customer` is related 
to `Order` via the `orders` relation, and `Order` is related back to `Customer` via the `customer` relation.

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
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
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->inverseOf('customer');
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
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
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
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
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
  [[yii\db\ActiveQuery::andOnCondition()|andOnCondition()]] or [[yii\db\ActiveQuery::orOnCondition()|orOnCondition()]] to append additional conditions when defining new query building methods so that any existing conditions are not overwritten.

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
        return $this->hasMany(Comment::className(), ['customer_id' => 'id'])->active();
    }
}

$customers = Customer::find()->joinWith('activeComments')->all();

// or alternatively
class Customer extends \yii\db\ActiveRecord
{
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
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
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
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
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
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
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
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
