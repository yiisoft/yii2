数据库访问 (DAO)
========

Yii 包含了一个建立在 PHP PDO 之上的数据访问层 (DAO). DAO为不同的数据库提供了一套统一的API. 其中```ActiveRecord``` 提供了数据库与模型(MVC 中的 M,Model) 的交互,```QueryBuilder``` 用于创建动态的查询语句. DAO提供了简单高效的SQL查询,可以用在与数据库交互的各个地方.

Yii 默认支持以下数据库 (DBMS):
- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/)
- [CUBRID](http://www.cubrid.org/): 版本 >= 9.3 . (由于PHP PDO 扩展的一个[bug](http://jira.cubrid.org/browse/APIS-658)  引用值会无效,所以你需要在 CUBRID的客户端和服务端都使用 9.3 )
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): 版本>=2005.

##配置

开始使用数据库首先需要配置数据库连接组件，通过添加 db 组件到应用配置实现（"基础的" Web 应用是 config/web.php），DSN( Data Source Name )是数据源名称，用于指定数据库信息.如下所示：

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase', // MySQL, MariaDB
            //'dsn' => 'sqlite:/path/to/database/file', // SQLite
            //'dsn' => 'pgsql:host=localhost;port=5432;dbname=mydatabase', // PostgreSQL
            //'dsn' => 'cubrid:dbname=demodb;host=localhost;port=33000', // CUBRID
            //'dsn' => 'sqlsrv:Server=localhost;Database=mydatabase', // MS SQL Server, sqlsrv driver
            //'dsn' => 'dblib:host=localhost;dbname=mydatabase', // MS SQL Server, dblib driver
            //'dsn' => 'mssql:host=localhost;dbname=mydatabase', // MS SQL Server, mssql driver
            //'dsn' => 'oci:dbname=//localhost:1521/mydatabase', // Oracle
            'username' => 'root', //数据库用户名
            'password' => '', //数据库密码
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

请参考PHP manual获取更多有关 DSN 格式信息。
配置连接组件后可以使用以下语法访问：

```$connection = \Yii::$app->db;```

请参考```[[yii\db\Connection]]```获取可配置的属性列表。
如果你想通过ODBC连接数据库，则需要配置[[yii\db\Connection::driverName]] 属性，例如:

```
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

注意:如果需要同时使用多个数据库可以定义 多个 连接组件：

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase', 
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'secondDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:/path/to/database/file', 
        ],
    ],
    // ...
];
```
在代码中通过以下方式使用:

```
$primaryConnection = \Yii::$app->db;
$secondaryConnection = \Yii::$app->secondDb;
```

如果不想定义数据库连接为全局[应用](structure-application-components.md)组件，可以在代码中直接初始化使用：

```
$connection = new \yii\db\Connection([
    'dsn' => $dsn,
     'username' => $username,
     'password' => $password,
]);
$connection->open();
```

>小提示：如果在创建了连接后需要执行额外的 SQL 查询，可以添加以下代码到应用配置文件：

```
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            // ...
            'on afterOpen' => function($event) {
                $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
            }
        ],
    ],
    // ...
];
```

##SQL 基础查询

一旦有了连接实例就可以通过[[yii\db\Command]]执行 SQL 查询。

###SELECT 查询
查询返回多行：

```
$command = $connection->createCommand('SELECT * FROM post');
$posts = $command->queryAll();
```
返回单行：
```
$command = $connection->createCommand('SELECT * FROM post WHERE id=1');
$post = $command->queryOne();
```

查询多行单值：
```
$command = $connection->createCommand('SELECT title FROM post');
$titles = $command->queryColumn();
```
查询标量值/计算值：

```
$command = $connection->createCommand('SELECT COUNT(*) FROM post');
$postCount = $command->queryScalar();
```

###UPDATE, INSERT, DELETE 更新、插入和删除等

如果执行 SQL 不返回任何数据可使用命令中的 execute 方法：

```
$command = $connection->createCommand('UPDATE post SET status=1 WHERE id=1');
$command->execute();
```
你可以使用`insert`,`update`,`delete` 方法，这些方法会根据参数生成合适的SQL并执行.

```php
// INSERT
$connection->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// INSERT 一次插入多行
$connection->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();

// UPDATE
$connection->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE
$connection->createCommand()->delete('user', 'status = 0')->execute();
```

###引用的表名和列名 <a name="quoting-table-and-column-names"></a>

大多数时间都使用以下语法来安全地引用表名和列名：

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```
以上代码`[[$column]]` 会转变为引用恰当的列名，而`{{table}}` 就转变为引用恰当的表名。
表名有个特殊的变量 {{%Y}} ，如果设置了表前缀使用该变体可以自动在表名前添加前缀：

```php
$sql = "SELECT COUNT([[$column]]) FROM {{%$table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

如果在配置文件如下设置了表前缀，以上代码将在 tbl_table 这个表查询结果：

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

手工引用表名和列名的另一个选择是使用[[yii\db\Connection::quoteTableName()]] 和 [[yii\db\Connection::quoteColumnName()]]：

```php
$column = $connection->quoteColumnName($column);
$table = $connection->quoteTableName($table);
$sql = "SELECT COUNT($column) FROM $table";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

###预处理语句

为安全传递查询参数可以使用预处理语句,首先应当使用`:placeholder`占位，再将变量绑定到对应占位符：

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
$command->bindValue(':id', $_GET['id']);
$post = $command->query();
```

另一种用法是准备一次预处理语句而执行多次查询：

```php
$command = $connection->createCommand('DELETE FROM post WHERE id=:id');
$command->bindParam(':id', $id);

$id = 1;
$command->execute();

$id = 2;
$command->execute();
```
>提示，在执行前绑定变量，然后在每个执行中改变变量的值（一般用在循环中）比较高效.

##事务

当你需要顺序执行多个相关的的`query`时，你可以把他们封装到一个事务中去保护数据一致性.Yii提供了一个简单的接口来实现事务操作.
如下执行 SQL 事务查询语句：

```php
$transaction = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();
     $connection->createCommand($sql2)->execute();
    // ... 执行其他 SQL 语句 ...
    $transaction->commit();
} catch(Exception $e) {
    $transaction->rollBack();
}
```
我们通过[[yii\db\Connection::beginTransaction()|beginTransaction()]]开始一个事务，通过`try catch` 捕获异常.当执行成功，通过[[yii\db\Transaction::commit()|commit()]]提交事务并结束，当发生异常失败通过[[yii\db\Transaction::rollBack()|rollBack()]]进行事务回滚.

如需要也可以嵌套多个事务：

```php
// 外部事务
$transaction1 = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();

    // 内部事务
    $transaction2 = $connection->beginTransaction();
    try {
        $connection->createCommand($sql2)->execute();
        $transaction2->commit();
    } catch (Exception $e) {
        $transaction2->rollBack();
    }

    $transaction1->commit();
} catch (Exception $e) {
    $transaction1->rollBack();
}
```
>注意你使用的数据库必须支持`Savepoints`才能正确地执行，以上代码在所有关系数据中都可以执行，但是只有支持`Savepoints`才能保证安全性。

Yii 也支持为事务设置隔离级别`isolation levels`，当执行事务时会使用数据库默认的隔离级别，你也可以为事物指定隔离级别.
Yii 提供了以下常量作为常用的隔离级别

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - 允许读取改变了的还未提交的数据,可能导致脏读、不可重复读和幻读
- [[\yii\db\Transaction::READ_COMMITTED]] -  允许并发事务提交之后读取，可以避免脏读，可能导致重复读和幻读。
- [[\yii\db\Transaction::REPEATABLE_READ]] - 对相同字段的多次读取结果一致，可导致幻读。
- [[\yii\db\Transaction::SERIALIZABLE]] - 完全服从ACID的原则，确保不发生脏读、不可重复读和幻读。

你可以使用以上常量或者使用一个string字符串命令，在对应数据库中执行该命令用以设置隔离级别，比如对于`postgres`有效的命令为`SERIALIZABLE READ ONLY DEFERRABLE`.

>注意:某些数据库只能针对连接来设置事务隔离级别，所以你必须要为连接明确制定隔离级别.目前受影响的数据库:`MSSQL SQLite`

>注意:SQLite 只支持两种事务隔离级别，所以你只能设置`READ UNCOMMITTED` 和 `SERIALIZABLE`.使用其他隔离级别会抛出异常.

>注意:PostgreSQL 不允许在事务开始前设置隔离级别，所以你不能在事务开始时指定隔离级别.你可以在事务开始之后调用[[yii\db\Transaction::setIsolationLevel()]] 来设置.

关于隔离级别[isolation levels]: http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels

##数据库复制和读写分离

很多数据库支持数据库复制 [database replication](http://en.wikipedia.org/wiki/Replication_(computing)#Database_replication)来提高可用性和响应速度. 在数据库复制中，数据总是从*主服务器* 到 *从服务器*. 所有的插入和更新等写操作在主服务器执行，而读操作在从服务器执行.

通过配置[[yii\db\Connection]]可以实现数据库复制和读写分离.

```php
[
    'class' => 'yii\db\Connection',

    // 配置主服务器
    'dsn' => 'dsn for master server',
    'username' => 'master',
    'password' => '',

    // 配置从服务器
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // 配置从服务器组
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```
以上的配置实现了一主多从的结构，从服务器用以执行读查询，主服务器执行写入查询，读写分离的功能由后台代码自动完成.调用者无须关心.例如：

```php
// 使用以上配置创建数据库连接对象
$db = Yii::createObject($config);

// 通过从服务器执行查询操作
$rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// 通过主服务器执行更新操作
$db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```
>注意:通过[[yii\db\Command::execute()]] 执行的查询被认为是写操作，所有使用[[yii\db\Command]]来执行的其他查询方法被认为是读操作.你可以通过`$db->slave`得到当前正在使用能够的从服务器.

`Connection`组件支持从服务器的负载均衡和故障转移，当第一次执行读查询时，会随即选择一个从服务器进行连接，如果连接失败则又选择另一个，如果所有从服务器都不可用，则会连接主服务器。你可以配置[[yii\db\Connection::serverStatusCache|server status cache]]来记住那些不能连接的从服务器，使Yii 在一段时间[[yii\db\Connection::serverRetryInterval].内不会重复尝试连接那些根本不可用的从服务器.

>注意:在上述配置中，每个从服务器连接超时时间被指定为10s. 如果在10s内不能连接，则被认为该服务器已经挂掉.你也可以自定义超时参数.

你也可以配置多主多从的结构，例如:

```php
[
    'class' => 'yii\db\Connection',

    // 配置主服务器
    'masterConfig' => [
        'username' => 'master',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // 配置主服务器组
    'masters' => [
        ['dsn' => 'dsn for master server 1'],
        ['dsn' => 'dsn for master server 2'],
    ],

    // 配置从服务器
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // 配置从服务器组
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```
上述配置制定了2个主服务器和4个从服务器.`Connection`组件也支持主服务器的负载均衡和故障转移，与从服务器不同的是，如果所有主服务器都不可用，则会抛出异常.

>注意:当你使用[[yii\db\Connection::masters|masters]]来配置一个或多个主服务器时，`Connection`中关于数据库连接的其他属性（例如：`dsn`, `username`, `password`）都会被忽略.

事务默认使用主服务器的连接，并且在事务执行中的所有操作都会使用主服务器的连接，例如:

```php
// 在主服务器连接上开始事务
$transaction = $db->beginTransaction();

try {
    // 所有的查询都在主服务器上执行
    $rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
    $db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

如果你想在从服务器上执行事务操作则必须要明确地指定，比如:

```php
$transaction = $db->slave->beginTransaction();
```

有时你想强制使用主服务器来执行读查询，你可以调用`seMaster()`方法.

```php
$rows = $db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```
你也可以设置`$db->enableSlaves` 为`false`来使所有查询都在主服务器上执行.

##操作数据库模式

###获得模式信息

你可以通过 [[yii\db\Schema]]实例来获取Schema信息:

```php
$schema = $connection->getSchema();
```

该实例包括一系列方法来检索数据库多方面的信息：

```php
$tables = $schema->getTableNames();
```
更多信息请参考[[yii\db\Schema]]

###修改模式

除了基础的 SQL 查询，[[yii\db\Command]]还包括一系列方法来修改数据库模式：

- 创建/重命名/删除/清空表
- 增加/重命名/删除/修改字段
- 增加/删除主键
- 增加/删除外键
- 创建/删除索引

使用示例:

```php
// 创建表
$connection->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```
完整参考请查看[[yii\db\Command]].
