数据库访问对象（Database Access Objects）
=====================================

Yii 包含了一个建立在 PHP PDO 之上的数据访问层 (DAO)。DAO为不同的数据库提供了一套统一的API。
其中 `ActiveRecord` 提供了数据库与模型(MVC 中的 M,Model) 的交互，`QueryBuilder` 用于创建动态的查询语句。
DAO提供了简单高效的SQL查询，可以用在与数据库交互的各个地方.

使用 Yii DAO 时，你主要需要处理纯 SQL 语句和 PHP 数组。因此，这是访问数据库最高效的方法。
然而，因为不同数据库之间的 SQL 语法往往是不同的，
使用 Yii DAO 也意味着你必须花些额外的工夫来创建一个”数据库无关“的应用。

Yii DAO 支持下列现成的数据库：

- [MySQL](https://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](https://sqlite.org/)
- [PostgreSQL](https://www.postgresql.org/)：版本 8.4 或更高
- [CUBRID](https://www.cubrid.org/)：版本 9.3 或更高。
- [Oracle](https://www.oracle.com/database/)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx)：版本 2008 或更高。

> Info: 在Yii 2.1及更高版本中，DAO 支持 CUBRID，Oracle 和 MSSQL
  不再作为框架的内置核心组件提供。它们必须作为独离的 [扩展](structure-extensions.md) 安装。
  [yiisoft/yii2-oracle](https://www.yiiframework.com/extension/yiisoft/yii2-oracle) 和
  [yiisoft/yii2-mssql](https://www.yiiframework.com/extension/yiisoft/yii2-mssql) 都属于
  [官方扩展](https://www.yiiframework.com/extensions/official)。

> Note: 供 PHP 7 使用的新版 pdo_oci 扩展目前仅发布了源代码，如果你想编译使用请参照 
  [社区用户提供的编译安装指引](https://github.com/yiisoft/yii2/issues/10975#issuecomment-248479268)。
  或者你也可以在你的应用中使用 [PDO模拟层](https://github.com/taq/pdooci)。

## 创建数据库连接（Creating DB Connections） <span id="creating-db-connections"></span>

想要访问数据库，你首先需要通过创建一个 [[yii\db\Connection]] 实例来与之建立连接。

```php
$db = new yii\db\Connection([
    'dsn' => 'mysql:host=localhost;dbname=example',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```

因为数据库连接经常需要在多个地方使用到，
一个常见的做法是以[应用组件](structure-application-components.md)的方式来配置它，如下:

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=example',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

之后你就可以通过语句 `Yii::$app->db` 来使用数据库连接了。

> Tip: 如果你的应用需要访问多个数据库，你可以配置多个 DB 应用组件。

配置数据库连接时， 你应该总是通过 [[yii\db\Connection::dsn|dsn]] 属性来指明它的数据源名称 (DSN) 。
不同的数据库有着不同的 DSN 格式。
请参考 [PHP manual](https://www.php.net/manual/zh/pdo.construct.php) 来获得更多细节。下面是一些例子：
 
* MySQL, MariaDB: `mysql:host=localhost;dbname=mydatabase`
* SQLite: `sqlite:/path/to/database/file`
* PostgreSQL: `pgsql:host=localhost;port=5432;dbname=mydatabase`
* CUBRID: `cubrid:dbname=demodb;host=localhost;port=33000`
* MS SQL Server (via sqlsrv driver): `sqlsrv:Server=localhost;Database=mydatabase`
* MS SQL Server (via dblib driver): `dblib:host=localhost;dbname=mydatabase`
* MS SQL Server (via mssql driver): `mssql:host=localhost;dbname=mydatabase`
* Oracle: `oci:dbname=//localhost:1521/mydatabase`

请注意，如果你是通过 ODBC 来连接数据库，你应该配置 [[yii\db\Connection::driverName]] 属性，
以便 Yii 能够知道实际的数据库种类。例如：

```php
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

除了 [[yii\db\Connection::dsn|dsn]] 属性，
你常常需要配置 [[yii\db\Connection::username|username]] 和 [[yii\db\Connection::password|password]]。请参考 [[yii\db\Connection]] 来获取完整的可配置属性列表。

> Info: 当你实例化一个 DB Connection 时，直到你第一次执行 SQL 或者你明确地调用 [[yii\db\Connection::open()|open()]] 方法时，
  才建立起实际的数据库连接。

> Tip: 有时你可能想要在建立起数据库连接时立即执行一些语句来初始化一些环境变量 (比如设置时区或者字符集),
> 你可以通过为数据库连接的 [[yii\db\Connection::EVENT_AFTER_OPEN|afterOpen]] 
> 事件注册一个事件处理器来达到目的。
> 你可以像这样直接在应用配置中注册处理器：
>
> ```php
> 'db' => [
>     // ...
>     'on afterOpen' => function($event) {
>         // $event->sender refers to the DB connection
>         $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
>     }
> ],
> ```


## 执行 SQL 查询（Executing SQL Queries） <span id="executing-sql-queries"></span>

一旦你拥有了 DB Connection 实例，你可以按照下列步骤来执行 SQL 查询：
 
1. 使用纯SQL查询来创建出 [[yii\db\Command]]；
2. 绑定参数 (可选的)；
3. 调用 [[yii\db\Command]] 里 SQL 执行方法中的一个。

下列例子展示了几种不同的从数据库取得数据的方法：
 
```php
// 返回多行. 每行都是列名和值的关联数组.
// 如果该查询没有结果则返回空数组
$posts = Yii::$app->db->createCommand('SELECT * FROM post')
            ->queryAll();

// 返回一行 (第一行)
// 如果该查询没有结果则返回 false
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=1')
           ->queryOne();

// 返回一列 (第一列)
// 如果该查询没有结果则返回空数组
$titles = Yii::$app->db->createCommand('SELECT title FROM post')
             ->queryColumn();

// 返回一个标量值
// 如果该查询没有结果则返回 false
$count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM post')
             ->queryScalar();
```

> Note: 为了保持精度，即使对应的数据库列类型为数值型，
> 所有从数据库取得的数据都被表现为字符串。


### 绑定参数（Binding Parameters） <span id="binding-parameters"></span>

当使用带参数的 SQL 来创建数据库命令时，
你几乎总是应该使用绑定参数的方法来防止 SQL 注入攻击，例如：

```php
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValue(':id', $_GET['id'])
           ->bindValue(':status', 1)
           ->queryOne();
```

在 SQL 语句中，你可以嵌入一个或多个参数占位符(例如，上述例子中的 `:id` )。 
一个参数占位符应该是以冒号开头的字符串。
之后你可以调用下面绑定参数的方法来绑定参数值：

* [[yii\db\Command::bindValue()|bindValue()]]：绑定一个参数值
* [[yii\db\Command::bindValues()|bindValues()]]：在一次调用中绑定多个参数值
* [[yii\db\Command::bindParam()|bindParam()]]：与 [[yii\db\Command::bindValue()|bindValue()]]
  相似，但是也支持绑定参数引用。

下面的例子展示了几个可供选择的绑定参数的方法：

```php
$params = [':id' => $_GET['id'], ':status' => 1];

$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValues($params)
           ->queryOne();
           
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status', $params)
           ->queryOne();
```

绑定参数是通过 [预处理语句](https://www.php.net/manual/zh/mysqli.quickstart.prepared-statements.php) 实现的。
除了防止 SQL 注入攻击，它也可以通过一次预处理 SQL 语句，
使用不同参数多次执行，来提升性能。例如：

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id');

$post1 = $command->bindValue(':id', 1)->queryOne();
$post2 = $command->bindValue(':id', 2)->queryOne();
// ...
```

因为 [[yii\db\Command::bindParam()|bindParam()]] 支持通过引用来绑定参数，
上述代码也可以像下面这样写：

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id')
              ->bindParam(':id', $id);

$id = 1;
$post1 = $command->queryOne();

$id = 2;
$post2 = $command->queryOne();
// ...
```

请注意，在执行语句前你将占位符绑定到 `$id` 变量，
然后在之后的每次执行前改变变量的值（这通常是用循环来完成的）。
以这种方式执行查询比为每个不同的参数值执行一次新的查询要高效得多得多。

> Info: 参数绑定仅用于需要将值插入到包含普通SQL的字符串中的地方。
> 在 [Query Builder](db-query-builder.md) 和 [Active Record](db-active-record.md)
> 等更高抽象层的许多地方，您经常会指定一组将被转换为 SQL 的值。
> 在这些地方，参数绑定是由 Yii 内部完成的，因此不需要手动指定参数。


### 执行非查询语句（Executing Non-SELECT Queries） <span id="non-select-queries"></span>

上面部分中介绍的 `queryXyz()` 方法都处理的是从数据库返回数据的查询语句。对于那些不取回数据的语句，
你应该调用的是 [[yii\db\Command::execute()]] 方法。例如，

```php
Yii::$app->db->createCommand('UPDATE post SET status=1 WHERE id=1')
   ->execute();
```

[[yii\db\Command::execute()]] 方法返回执行 SQL 所影响到的行数。

对于 INSERT, UPDATE 和 DELETE 语句，不再需要写纯SQL语句了， 
你可以直接调用 [[yii\db\Command::insert()|insert()]]、[[yii\db\Command::update()|update()]]、[[yii\db\Command::delete()|delete()]]，
来构建相应的 SQL 语句。这些方法将正确地引用表和列名称以及绑定参数值。例如,

```php
// INSERT (table name, column values)
Yii::$app->db->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// UPDATE (table name, column values, condition)
Yii::$app->db->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE (table name, condition)
Yii::$app->db->createCommand()->delete('user', 'status = 0')->execute();
```

你也可以调用 [[yii\db\Command::batchInsert()|batchInsert()]] 来一次插入多行，
这比一次插入一行要高效得多：

```php
// table name, column names, column values
Yii::$app->db->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();
```

另一个有用的方法是 [[yii\db\Command::upsert()|upsert()]]。Upsert 是一种原子操作，如果它们不存在（匹配唯一约束），则将行插入到数据库表中，
或者在它们执行时更新它们：

```php
Yii::$app->db->createCommand()->upsert('pages', [
    'name' => 'Front page',
    'url' => 'https://example.com/', // url is unique
    'visits' => 0,
], [
    'visits' => new \yii\db\Expression('visits + 1'),
], $params)->execute();
```

上面的代码将插入一个新的页面记录或自动增加访问计数器。

请注意，上述的方法只是构建出语句，
你总是需要调用 [[yii\db\Command::execute()|execute()]] 来真正地执行它们。


## 引用表和列名称（Quoting Table and Column Names） <span id="quoting-table-and-column-names"></span>

当写与数据库无关的代码时，正确地引用表和列名称总是一件头疼的事，
因为不同的数据库有不同的名称引用规则，为了克服这个问题，
你可以使用下面由 Yii 提出的引用语法。

* `[[column name]]`: 使用两对方括号来将列名括起来; 
* `{{table name}}`: 使用两对大括号来将表名括起来。

Yii DAO 将自动地根据数据库的具体语法来将这些结构转化为对应的
被引用的列或者表名称。
例如，

```php
// 在 MySQL 中执行该 SQL : SELECT COUNT(`id`) FROM `employee`
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{employee}}")
            ->queryScalar();
```


### 使用表前缀（Using Table Prefix） <span id="using-table-prefix"></span>

如果你的数据库表名大多都拥有一个共同的前缀，
你可以使用 Yii DAO 所提供的表前缀功能。

首先，通过应用配置中的 [[yii\db\Connection::tablePrefix]] 属性来指定表前缀：

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            // ...
            'tablePrefix' => 'tbl_',
        ],
    ],
];
```

接着在你的代码中，当你需要涉及到一张表名中包含该前缀的表时，
应使用语法 `{{%table_name}}`。百分号将被自动地替换为你在配置 DB 组件时指定的表前缀。
例如，

```php
// 在 MySQL 中执行该 SQL: SELECT COUNT(`id`) FROM `tbl_employee`
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{%employee}}")
            ->queryScalar();
```


## 执行事务（Performing Transactions） <span id="performing-transactions"></span>

当顺序地执行多个相关的语句时， 你或许需要将它们包在一个事务中来保证数据库的完整性和一致性。
如果这些语句中的任何一个失败了，
数据库将回滚到这些语句执行前的状态。
 
下面的代码展示了一个使用事务的典型方法：

```php
Yii::$app->db->transaction(function($db) {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
});
```

上述代码等价于下面的代码，但是下面的代码给予了你对于错误处理代码的更多掌控：

```php
$db = Yii::$app->db;
$transaction = $db->beginTransaction();
try {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
    
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```

通过调用 [[yii\db\Connection::beginTransaction()|beginTransaction()]] 方法，
一个新事务开始了。
事务被表示为一个存储在 `$transaction` 变量中的 [[yii\db\Transaction]] 对象。
然后，被执行的语句都被包含在一个 `try...catch...` 块中。
如果所有的语句都被成功地执行了，
[[yii\db\Transaction::commit()|commit()]] 将被调用来提交这个事务。
否则， 如果异常被触发并被捕获，
[[yii\db\Transaction::rollBack()|rollBack()]] 方法将被调用，
来回滚事务中失败语句之前所有语句所造成的改变。
 `throw $e` 将重新抛出该异常，
就好像我们没有捕获它一样，
因此正常的错误处理程序将处理它。

### 指定隔离级别（Specifying Isolation Levels） <span id="specifying-isolation-levels"></span>

Yii 也支持为你的事务设置[隔离级别]。默认情况下，当我们开启一个新事务，
它将使用你的数据库所设定的隔离级别。你也可以向下面这样重载默认的隔离级别，

```php
$isolationLevel = \yii\db\Transaction::REPEATABLE_READ;

Yii::$app->db->transaction(function ($db) {
    ....
}, $isolationLevel);
 
// or alternatively

$transaction = Yii::$app->db->beginTransaction($isolationLevel);
```

Yii 为四个最常用的隔离级别提供了常量：

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - 最弱的隔离级别，脏读、不可重复读以及幻读都可能发生。
- [[\yii\db\Transaction::READ_COMMITTED]] - 避免了脏读。
- [[\yii\db\Transaction::REPEATABLE_READ]] - 避免了脏读和不可重复读。
- [[\yii\db\Transaction::SERIALIZABLE]] - 最强的隔离级别， 避免了上述所有的问题。

除了使用上述的常量来指定隔离级别，你还可以使用你的数据库所支持的具有有效语法的字符串。
比如，在 PostgreSQL 中，你可以使用 `SERIALIZABLE READ ONLY DEFERRABLE`。 

请注意，一些数据库只允许为整个连接设置隔离级别，
即使你之后什么也没指定，后来的事务都将获得与之前相同的隔离级别。
使用此功能时，你需要为所有的事务明确地设置隔离级别来避免冲突的设置。
在本文写作之时，只有 MSSQL 和 SQLite 受这些限制的影响。

> Note: SQLite 只支持两种隔离级别，所以你只能使用 `READ UNCOMMITTED` 和 `SERIALIZABLE`。
使用其他级别将导致异常的抛出。

> Note: PostgreSQL 不支持在事务开启前设定隔离级别，
因此，你不能在开启事务时直接指定隔离级别。
你必须在事务开始后再调用 [[yii\db\Transaction::setIsolationLevel()]]。

[隔离级别]: https://zh.wikipedia.org/wiki/%E4%BA%8B%E5%8B%99%E9%9A%94%E9%9B%A2#.E9.9A.94.E7.A6.BB.E7.BA.A7.E5.88.AB


### 嵌套事务（Nesting Transactions） <span id="nesting-transactions"></span>

如果你的数据库支持保存点，你可以像下面这样嵌套多个事务：

```php
Yii::$app->db->transaction(function ($db) {
    // outer transaction
    
    $db->transaction(function ($db) {
        // inner transaction
    });
});
```

或者，

```php
$db = Yii::$app->db;
$outerTransaction = $db->beginTransaction();
try {
    $db->createCommand($sql1)->execute();

    $innerTransaction = $db->beginTransaction();
    try {
        $db->createCommand($sql2)->execute();
        $innerTransaction->commit();
    } catch (\Exception $e) {
        $innerTransaction->rollBack();
        throw $e;
    } catch (\Throwable $e) {
        $innerTransaction->rollBack();
        throw $e;
    }

    $outerTransaction->commit();
} catch (\Exception $e) {
    $outerTransaction->rollBack();
    throw $e;
} catch (\Throwable $e) {
    $outerTransaction->rollBack();
    throw $e;
}
```


## 复制和读写分离（Replication and Read-Write Splitting） <span id="read-write-splitting"></span>

许多数据库支持[数据库复制](https://en.wikipedia.org/wiki/Replication_(computing)#Database_replication)来获得更好的数据库可用性，
以及更快的服务器响应时间。通过数据库复制功能，
数据从所谓的主服务器被复制到从服务器。所有的写和更新必须发生在主服务器上，
而读可以发生在从服务器上。

为了利用数据库复制并且完成读写分离，
你可以按照下面的方法来配置 [[yii\db\Connection]] 组件：

```php
[
    'class' => 'yii\db\Connection',

    // 主库的配置
    'dsn' => 'dsn for master server',
    'username' => 'master',
    'password' => '',

    // 从库的通用配置
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // 使用一个更小的连接超时
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // 从库的配置列表
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

上述的配置指定了一主多从的设置。
这些从库其中之一将被建立起连接并执行读操作，而主库将被用来执行写操作。
这样的读写分离将通过上述配置自动地完成。比如，

```php
// 使用上述配置来创建一个 Connection 实例
Yii::$app->db = Yii::createObject($config);

// 在从库中的一个上执行语句
$rows = Yii::$app->db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// 在主库上执行语句
Yii::$app->db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Info: 通过调用 [[yii\db\Command::execute()]] 来执行的语句都被视为写操作，
  而其他所有通过调用 [[yii\db\Command]] 中任一 "query" 方法来执行的语句都被视为读操作。
  你可以通过 `Yii::$app->db->slave` 来获取当前有效的从库连接。

`Connection` 组件支持从库间的负载均衡和失效备援，
当第一次执行读操作时，`Connection` 组件将随机地挑选出一个从库并尝试与之建立连接，
如果这个从库被发现为”挂掉的“，将尝试连接另一个从库。
如果没有一个从库是连接得上的，那么将试着连接到主库上。
通过配置 [[yii\db\Connection::serverStatusCache|server status cache]]，
一个“挂掉的”服务器将会被记住，因此，在一个 yii\db\Connection::serverRetryInterval 内将不再试着连接该服务器。

> Info: 在上面的配置中，
  每个从库都共同地指定了 10 秒的连接超时时间，这意味着，如果一个从库在 10 秒内不能被连接上，它将被视为“挂掉的”。
  你可以根据你的实际环境来调整该参数。


你也可以配置多主多从。例如，


```php
[
    'class' => 'yii\db\Connection',

    // 主库通用的配置
    'masterConfig' => [
        'username' => 'master',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // 主库配置列表
    'masters' => [
        ['dsn' => 'dsn for master server 1'],
        ['dsn' => 'dsn for master server 2'],
    ],

    // 从库的通用配置
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // 从库配置列表
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

上述配置指定了两个主库和两个从库。 
`Connection` 组件在主库之间，也支持如从库间般的负载均衡和失效备援。
唯一的差别是，如果没有主库可用，将抛出一个异常。

> Note: 当你使用 [[yii\db\Connection::masters|masters]] 属性来配置一个或多个主库时，
  所有其他指定数据库连接的属性 (例如 `dsn`, `username`, `password`) 
  与 `Connection` 对象本身将被忽略。


默认情况下，事务使用主库连接，
一个事务内，所有的数据库操作都将使用主库连接，例如，

```php
$db = Yii::$app->db;
// 在主库上启动事务
$transaction = $db->beginTransaction();

try {
    // 两个语句都是在主库上执行的
    $rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
    $db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```

如果你想在从库上开启事务，你应该明确地像下面这样做：

```php
$transaction = Yii::$app->db->slave->beginTransaction();
```

有时，你或许想要强制使用主库来执行读查询。
这可以通过 `useMaster()` 方法来完成：

```php
$rows = Yii::$app->db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```

你也可以明确地将 `Yii::$app->db->enableSlaves` 设置为 false 来将所有的读操作指向主库连接。


## 操纵数据库模式（Working with Database Schema） <span id="database-schema"></span>

Yii DAO 提供了一套完整的方法来让你操纵数据库模式，
如创建表、从表中删除一列，等等。这些方法罗列如下：

* [[yii\db\Command::createTable()|createTable()]]：创建一张表
* [[yii\db\Command::renameTable()|renameTable()]]：重命名一张表
* [[yii\db\Command::dropTable()|dropTable()]]：删除一张表
* [[yii\db\Command::truncateTable()|truncateTable()]]：删除一张表中的所有行
* [[yii\db\Command::addColumn()|addColumn()]]：增加一列
* [[yii\db\Command::renameColumn()|renameColumn()]]：重命名一列
* [[yii\db\Command::dropColumn()|dropColumn()]]：删除一列
* [[yii\db\Command::alterColumn()|alterColumn()]]：修改一列
* [[yii\db\Command::addPrimaryKey()|addPrimaryKey()]]：增加主键
* [[yii\db\Command::dropPrimaryKey()|dropPrimaryKey()]]：删除主键
* [[yii\db\Command::addForeignKey()|addForeignKey()]]：增加一个外键
* [[yii\db\Command::dropForeignKey()|dropForeignKey()]]：删除一个外键
* [[yii\db\Command::createIndex()|createIndex()]]：增加一个索引
* [[yii\db\Command::dropIndex()|dropIndex()]]：删除一个索引

这些方法可以如下地使用：

```php
// CREATE TABLE
Yii::$app->db->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

上面的数组描述要创建的列的名称和类型。
对于列的类型， Yii 提供了一套抽象数据类型来允许你定义出数据库无关的模式。
这些将根据表所在数据库的种类，被转换为特定的类型定义。
请参考 [[yii\db\Command::createTable()|createTable()]]-method 的 API 文档来获取更多信息。

除了改变数据库模式，
你也可以通过 DB Connection 的 [[yii\db\Connection::getTableSchema()|getTableSchema()]] 方法来检索某张表的定义信息。例如，

```php
$table = Yii::$app->db->getTableSchema('post');
```

该方法返回一个 [[yii\db\TableSchema]] 对象，
它包含了表中的列、主键、外键，等等的信息。
所有的这些信息主要被 [query builder](db-query-builder.md) 和 [active record](db-active-record.md) 所使用，来帮助你写出数据库无关的代码。
