Database Access Objects
===============

> Note: This section is under development.

Yii includes a database access layer built on top of PHP's [PDO](http://www.php.net/manual/en/book.pdo.php). The database access objects (DAO) interface provides a
uniform API, and solves some inconsistencies that exist between different database applications. Whereas Active Record provides database interactions through models, and the Query Builder assists in composing dynamic queries, DAO is a simple and efficient way to execute straight SQL on your database. You'll want to use DAO when the query to be run is expensive and/or no application models--and their corresponding business logic--are required.

By default, Yii supports the following DBMS:

- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/)
- [CUBRID](http://www.cubrid.org/): version 9.3 or higher. (Note that due to a [bug](http://jira.cubrid.org/browse/APIS-658) in
  the cubrid PDO extension, quoting of values will not work, so you need CUBRID 9.3 as the client as well as the server)
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): version 2008 or higher.


Configuration
-------------

To start interacting with a database (using DAO or otherwise), you need to configure the application's database 
connection component. The Data Source Name (DSN) configures to which database application and specific database the application should connect:

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
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

Please refer to the [PHP manual](http://www.php.net/manual/en/function.PDO-construct.php) for more details
on the format of the DSN string. Refer to [[yii\db\Connection]] for the full list of properties you can configure in the class.

Note that if you are connecting with a database via ODBC, you should configure the [[yii\db\Connection::driverName]]
property so that Yii knows the actual database type. For example,

```php
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

You may access the primary `db` connection via the expression `\Yii::$app->db`. You may also configure multiple
DB connections in a single application. Simply assign different IDs to them in the application configuration:

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

Now you can use both database connections at the same time as needed:

```php
$primaryConnection = \Yii::$app->db;
$secondaryConnection = \Yii::$app->secondDb;
```

If you don't want to define the connection as an [application component](structure-application-components.md), you can instantiate it directly:

```php
$connection = new \yii\db\Connection([
    'dsn' => $dsn,
    'username' => $username,
    'password' => $password,
]);
$connection->open();
```

> Tip: If you need to execute an SQL query immediately after establishing a connection (e.g., to set the timezone or character set), you can add the following to your application configuration file:
>
```php
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

Executing Basic SQL Queries
---------------------------

Once you have a database connection instance, you can execute SQL queries using [[yii\db\Command]].

### Running SELECT Queries

When the query to be executed returns a set of rows, you'll use `queryAll`:

```php
$command = $connection->createCommand('SELECT * FROM post');
$posts = $command->queryAll();
```

When the query to be executed only returns a single row, you'll use `queryOne`:

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=1');
$post = $command->queryOne();
```

When the query returns multiple rows but only one column, you'll use `queryColumn`:

```php
$command = $connection->createCommand('SELECT title FROM post');
$titles = $command->queryColumn();
```

When the query only returns a scalar value, you'll use `queryScalar`:

```php
$command = $connection->createCommand('SELECT COUNT(*) FROM post');
$postCount = $command->queryScalar();
```

### Running Queries That Don't Return Values

If SQL executed doesn't return any data--for example, INSERT, UPDATE, and DELETE, you can use command's `execute` method:

```php
$command = $connection->createCommand('UPDATE post SET status=1 WHERE id=1');
$command->execute();
```

Alternatively, you can use the dedicated `insert`, `update`, and `delete` methods. These methods will properly quote table and column names used in your query, and you only need to provide the necessary values:

[[Ought to put a link to the reference docs here.]]

```php
// INSERT
$connection->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// INSERT multiple rows at once
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

Quoting Table and Column Names <a name="quoting-table-and-column-names"></a>
------------------------------

To make column and table names safe to use in queries, you can have Yii properly quote them for you:

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

In the code above, `[[$column]]` will be converted to a properly quoted column name, while `{{table}}` will be converted to a properly quoted table name.

There's a special variant on this syntax specific to tablenames: `{{%Y}}` automatically appends the application's table prefix to the provided value, if a table prefix has been set:

```php
$sql = "SELECT COUNT([[$column]]) FROM {{%table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

The code above will result in selecting from `tbl_table`, if you have table prefix configured like so:

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

The alternative is to quote table and column names manually using [[yii\db\Connection::quoteTableName()]] and
[[yii\db\Connection::quoteColumnName()]]:

```php
$column = $connection->quoteColumnName($column);
$table = $connection->quoteTableName($table);
$sql = "SELECT COUNT($column) FROM $table";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

Using Prepared Statements
-------------------

To securely pass query parameters to your queries, you should make use of prepared statements. First, create a named placeholder in your query (using the syntax `:placeholder`). Then bind the placeholder to a variable and execute the query:

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
$command->bindValue(':id', $_GET['id']);
$post = $command->queryOne();
```

Another purpose for prepared statements (aside from improved security) is the ability to execute a query multiple times while preparing it only once:

```php
$command = $connection->createCommand('DELETE FROM post WHERE id=:id');
$command->bindParam(':id', $id);

$id = 1;
$command->execute();

$id = 2;
$command->execute();
```

Notice that you bind the placeholder to the variable before the execution, and then change the value of that variable before each subsequent execution (this is often done with loops). Executing queries in this manner can be vastly more efficient than running each query one at a time. 

Performing Transactions
-----------------------

When running multiple, related queries in a sequence, you may need to wrap them in a transaction to
protect your data's integrity. Transactions allow you to write a series of queries such that they'll all succeed or have no effect whatsoever. Yii provides a simple interface to work with transactions in simple
cases but also for advanced usage when you need to define isolation levels.

The following code shows a simple pattern that all code that uses transactional queries should follow:

```php
$transaction = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();
    $connection->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

The first line starts a new transaction using the [[yii\db\Connection::beginTransaction()|beginTransaction()]] method of the database connection
object. The transaction itself is represented by a [[yii\db\Transaction]] object stored in `$transaction`.
We wrap the execution of all queries in a try-catch block to be able to handle errors.
We call [[yii\db\Transaction::commit()|commit()]] on success to commit the transaction and
[[yii\db\Transaction::rollBack()|rollBack()]] in case of an error. This will revert the effect of all queries
that have been executed inside of the transaction.
`throw $e` is used to re-throw the exception in case we can not handle the error ourselves and delegate it
to some other code or the Yii error handler.

It is also possible to nest multiple transactions, if needed:

```php
// outer transaction
$transaction1 = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();

    // inner transaction
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

Note that your DBMS should have support for Savepoints for this to work as expected.
The above code will work for any DBMS but transactional safety is only guaranteed if
the underlying DBMS supports it.

Yii also supports setting [isolation levels] for your transactions.
When beginning a transaction it will run in the default isolation level set by your database system.
You can specifying an isolation level explicitly when starting a transaction:

```php
$transaction = $connection->beginTransaction(\yii\db\Transaction::REPEATABLE_READ);
```

Yii provides four constants for the most common isolation levels:

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - the weakest level, Dirty reads, Non-repeatable reads and Phantoms may occur.
- [[\yii\db\Transaction::READ_COMMITTED]] - avoid Dirty reads.
- [[\yii\db\Transaction::REPEATABLE_READ]] - avoid Dirty reads and Non-repeatable reads.
- [[\yii\db\Transaction::SERIALIZABLE]] - the strongest level, avoids all of the above named problems.

You may use the constants named above but you can also use a string that represents a valid syntax that can be
used in your DBMS following `SET TRANSACTION ISOLATION LEVEL`. For postgres this could be for example
`SERIALIZABLE READ ONLY DEFERRABLE`.

Note that some DBMS allow setting of the isolation level only for the whole connection so subsequent transactions
may get the same isolation level even if you did not specify any. When using this feature
you may need to set the isolation level for all transactions explicitly to avoid conflicting settings.
At the time of this writing affected DBMS are MSSQL and SQLite.

> Note: SQLite only supports two isolation levels, so you can only use `READ UNCOMMITTED` and `SERIALIZABLE`.
Usage of other levels will result in an exception being thrown.

> Note: PostgreSQL does not allow setting the isolation level before the transaction starts so you can not
specify the isolation level directly when starting the transaction.
You have to call [[yii\db\Transaction::setIsolationLevel()]] in this case after the transaction has started.

[isolation levels]: http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels


Replication and Read-Write Splitting
------------------------------------

Many DBMS support [database replication](http://en.wikipedia.org/wiki/Replication_(computing)#Database_replication)
to get better database availability and faster server response time. With database replication, data are replicated
from the so-called *master servers* to *slave servers*. All writes and updates must take place on the master servers,
while reads may take place on the slave servers.

To take advantage of database replication and achieve read-write splitting, you can configure a [[yii\db\Connection]]
component like the following:

```php
[
    'class' => 'yii\db\Connection',

    // configuration for the master
    'dsn' => 'dsn for master server',
    'username' => 'master',
    'password' => '',

    // common configuration for slaves
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // list of slave configurations
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

The above configuration specifies a setup with a single master and multiple slaves. One of the slaves will
be connected and used to perform read queries, while the master will be used to perform write queries.
Such read-write splitting is accomplished automatically with this configuration. For example,

```php
// create a Connection instance using the above configuration
$db = Yii::createObject($config);

// query against one of the slaves
$rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// query against the master
$db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Info: Queries performed by calling [[yii\db\Command::execute()]] are considered as write queries, while
  all other queries done through one of the "query" methods of [[yii\db\Command]] are read queries.
  You can get the currently active slave connection via `$db->slave`.

The `Connection` component supports load balancing and failover between slaves.
When performing a read query for the first time, the `Connection` component will randomly pick a slave and
try connecting to it. If the slave is found "dead", it will try another one. If none of the slaves is available,
it will connect to the master. By configuring a [[yii\db\Connection::serverStatusCache|server status cache]],
a "dead" server can be remembered so that it will not be tried again during a
[[yii\db\Connection::serverRetryInterval|certain period of time]].

> Info: In the above configuration, a connection timeout of 10 seconds is specified for every slave.
  This means if a slave cannot be reached in 10 seconds, it is considered as "dead". You can adjust this parameter
  based on your actual environment.


You can also configure multiple masters with multiple slaves. For example,


```php
[
    'class' => 'yii\db\Connection',

    // common configuration for masters
    'masterConfig' => [
        'username' => 'master',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // list of master configurations
    'masters' => [
        ['dsn' => 'dsn for master server 1'],
        ['dsn' => 'dsn for master server 2'],
    ],

    // common configuration for slaves
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // list of slave configurations
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

The above configuration specifies two masters and four slaves. The `Connection` component also supports
load balancing and failover between masters just as it does between slaves. A difference is that when none 
of the masters are available an exception will be thrown.

> Note: When you use the [[yii\db\Connection::masters|masters]] property to configure one or multiple
  masters, all other properties for specifying a database connection (e.g. `dsn`, `username`, `password`)
  with the `Connection` object itself will be ignored.


By default, transactions use the master connection. And within a transaction, all DB operations will use
the master connection. For example,

```php
// the transaction is started on the master connection
$transaction = $db->beginTransaction();

try {
    // both queries are performed against the master
    $rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
    $db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

If you want to start a transaction with the slave connection, you should explicitly do so, like the following:

```php
$transaction = $db->slave->beginTransaction();
```

Sometimes, you may want to force using the master connection to perform a read query. This can be achieved
with the `useMaster()` method:

```php
$rows = $db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```

You may also directly set `$db->enableSlaves` to be false to direct all queries to the master connection.


Working with database schema
----------------------------

### Getting schema information

You can get a [[yii\db\Schema]] instance like the following:

```php
$schema = $connection->getSchema();
```

It contains a set of methods allowing you to retrieve various information about the database:

```php
$tables = $schema->getTableNames();
```

For the full reference, check [[yii\db\Schema]].

### Modifying schema

Aside from basic SQL queries, [[yii\db\Command]] contains a set of methods allowing the modification of the database schema:

- createTable, renameTable, dropTable, truncateTable
- addColumn, renameColumn, dropColumn, alterColumn
- addPrimaryKey, dropPrimaryKey
- addForeignKey, dropForeignKey
- createIndex, dropIndex

These can be used as follows:

```php
// CREATE TABLE
$connection->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

For the full reference, check [[yii\db\Command]].
