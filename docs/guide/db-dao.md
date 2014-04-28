Database basics
===============

Yii has a database access layer built on top of PHP's [PDO](http://www.php.net/manual/en/book.pdo.php). It provides
uniform API and solves some inconsistencies between different DBMS. By default Yii supports the following DBMS:

- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/)
- [CUBRID](http://www.cubrid.org/): version 9.1.0 or higher.
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): version 2012 or above is required if you
  want to use LIMIT/OFFSET.


Configuration
-------------

In order to start using database you need to configure database connection component first by adding `db` component
to application configuration (for "basic" web application it's `config/web.php`) like the following:

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

There is a peculiarity when you want to work with the database through the `ODBC` layer. When using `ODBC`,
connection `DSN` doesn't indicate uniquely what database type is being used. That's why you have to override
`driverName` property of [[yii\db\Connection]] class to disambiguate that:

```php
'db' => [
	'class' => 'yii\db\Connection',
	'driverName' => 'mysql',
	'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
	'username' => 'root',
	'password' => '',
],
```

Please refer to the [PHP manual](http://www.php.net/manual/en/function.PDO-construct.php) for more details
on the format of the DSN string.

After the connection component is configured you can access it using the following syntax:

```php
$connection = \Yii::$app->db;
```

You can refer to [[yii\db\Connection]] for a list of properties you can configure. Also note that you can define more
than one connection component and use both at the same time if needed:

```php
$primaryConnection = \Yii::$app->db;
$secondaryConnection = \Yii::$app->secondDb;
```

If you don't want to define the connection as an application component you can instantiate it directly:

```php
$connection = new \yii\db\Connection([
    'dsn' => $dsn,
     'username' => $username,
     'password' => $password,
]);
$connection->open();
```


> **Tip**: if you need to execute additional SQL queries right after establishing a connection you can add the
> following to your application configuration file:
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

Basic SQL queries
-----------------

Once you have a connection instance you can execute SQL queries using [[yii\db\Command]].

### SELECT

When query returns a set of rows:

```php
$command = $connection->createCommand('SELECT * FROM post');
$posts = $command->queryAll();
```

When only a single row is returned:

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=1');
$post = $command->queryOne();
```

When there are multiple values from the same column:

```php
$command = $connection->createCommand('SELECT title FROM post');
$titles = $command->queryColumn();
```

When there's a scalar value:

```php
$command = $connection->createCommand('SELECT COUNT(*) FROM post');
$postCount = $command->queryScalar();
```

### UPDATE, INSERT, DELETE etc.

If SQL executed doesn't return any data you can use command's `execute` method:

```php
$command = $connection->createCommand('UPDATE post SET status=1 WHERE id=1');
$command->execute();
```

Alternatively the following syntax that takes care of proper table and column names quoting is possible:

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

Quoting table and column names
------------------------------

Most of the time you would use the following syntax for quoting table and column names:

```php
$sql = "SELECT COUNT([[$column]]) FROM {{$table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

In the code above `[[X]]` will be converted to properly quoted column name while `{{Y}}` will be converted to properly
quoted table name.

For table names there's a special variant `{{%Y}}` that allows you to automatically appending table prefix if it is set:

```php
$sql = "SELECT COUNT([[$column]]) FROM {{%$table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

The code above will result in selecting from `tbl_table` if you have table prefix configured like the following in your
config file:

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

Prepared statements
-------------------

In order to securely pass query parameters you can use prepared statements:

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
$command->bindValue(':id', $_GET['id']);
$post = $command->query();
```

Another usage is performing a query multiple times while preparing it only once:

```php
$command = $connection->createCommand('DELETE FROM post WHERE id=:id');
$command->bindParam(':id', $id);

$id = 1;
$command->execute();

$id = 2;
$command->execute();
```

Transactions
------------

You can perform transactional SQL queries like the following:

```php
$transaction = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();
     $connection->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
    $transaction->commit();
} catch(Exception $e) {
    $transaction->rollBack();
}
```

You can also nest multiple transactions, if needed:

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

For the full reference check [[yii\db\Schema]].

### Modifying schema

Aside from basic SQL queries [[yii\db\Command]] contains a set of methods allowing to modify database schema:

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

For the full reference check [[yii\db\Command]].
