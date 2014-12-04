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

小提示：如果在创建了连接后需要执行额外的 SQL 查询，可以添加以下代码到应用配置文件：

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

SQL 基础查询

一旦有了连接实例就可以通过[[yii\db\Command]]执行 SQL 查询。

SELECT 查询

查询返回多行：

$command = $connection->createCommand('SELECT * FROM post');
$posts = $command->queryAll();
返回单行：

$command = $connection->createCommand('SELECT * FROM post WHERE id=1');
$post = $command->queryOne();
查询多列值：

$command = $connection->createCommand('SELECT title FROM post');
$titles = $command->queryColumn();
查询标量值/计算值：

$command = $connection->createCommand('SELECT COUNT(*) FROM post');
$postCount = $command->queryScalar();
UPDATE, INSERT, DELETE 更新、插入和删除等

如果执行 SQL 不返回任何数据可使用命令中的 execute 方法：

$command = $connection->createCommand('UPDATE post SET status=1 WHERE id=1');
$command->execute();
选择以下考虑到引用了恰当表名和列名的语法是可能的：

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
引用的表名和列名

大多数时间都使用以下语法来引用表名和列名：

$sql = "SELECT COUNT([[$column]]) FROM {{$table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
In the code above [[X]] will be converted to properly quoted column name while {{Y}} will be converted to properly 以上代码[[X]] 会转变为引用恰当的列名，而{{Y}} 就转变为引用恰当的表名。

表名有个专用的变体 {{%Y}} ，如果设置了表前缀使用该变体可以自动在表名前添加前缀：

$sql = "SELECT COUNT([[$column]]) FROM {{%$table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
如果在配置文件如下设置了表前缀，以上代码将在 tbl_table 这个表查询结果：

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
手工引用表名和列名的另一个选择是使用[[yii\db\Connection::quoteTableName()]] 和 [[yii\db\Connection::quoteColumnName()]]：

$column = $connection->quoteColumnName($column);
$table = $connection->quoteTableName($table);
$sql = "SELECT COUNT($column) FROM $table";
$rowCount = $connection->createCommand($sql)->queryScalar();
预处理语句

为安全传递查询参数可以使用预处理语句：

$command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
$command->bindValue(':id', $_GET['id']);
$post = $command->query();
另一种用法是准备一次预处理语句而执行多次查询：

$command = $connection->createCommand('DELETE FROM post WHERE id=:id');
$command->bindParam(':id', $id);

$id = 1;
$command->execute();

$id = 2;
$command->execute();
事务

如下执行 SQL 事务查询语句：

$transaction = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();
     $connection->createCommand($sql2)->execute();
    // ... 执行其他 SQL 语句 ...
    $transaction->commit();
} catch(Exception $e) {
    $transaction->rollBack();
}
如需要也可以嵌套多个事务：

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
操作数据库模式

获得模式信息

如下获得[[yii\db\Schema]]实例：

$schema = $connection->getSchema();
该实例包括一系列方法来检索数据库多方面的信息：

$tables = $schema->getTableNames();
完整参考请核对[[yii\db\Schema]]。

修改模式

除了基础的 SQL 查询，[[yii\db\Command]]还包括一系列方法来修改数据库模式：

createTable, renameTable, dropTable, truncateTable
addColumn, renameColumn, dropColumn, alterColumn
addPrimaryKey, dropPrimaryKey
addForeignKey, dropForeignKey
createIndex, dropIndex
如下使用它们：

// 新建表
$connection->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
完整参考请核对 [[yii\db\Command]].
