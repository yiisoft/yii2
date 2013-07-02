Database basics
===============

Yii has a database access layer built on top of PHP's [PDO](http://www.php.net/manual/en/ref.pdo.php). It provides
uniform API and solves some inconsistencies between different DBMS. By default Yii supports MySQL, SQLite, PostgreSQL,
Oracle and MSSQL.

Configuration
-------------

In order to start using database you need to configure database connection component first by adding `db` component
to application configuration (for "basic" web application it's `config/web.php`) like the following:

```php
return array(
	// ...
	'components' => array(
		// ...
		'db' => array(
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=mydatabase',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),
	),
	// ...
);
```

After component is configured you can access using the following syntax:

```php
$connection = \Yii::$app->db;
```

You can refer to [[\yii\db\Connection]] for a list of properties you can configure. Also note that you can define more
than one connection components and use both at the same time if needed:

```php
$primaryConnection = \Yii::$app->db;
$secondaryConnection = \Yii::$app->secondDb;
```

If you don't want to define connection as application component you can instantiate it directly:

```php
$connection = new \yii\db\Connection(array(
	'dsn' => $dsn,
 	'username' => $username,
 	'password' => $password,
));
$connection->open();
```

Basic SQL queries
-----------------

Once you have a connection instance you can execute SQL queries using [[\yii\db\Command]].

### SELECT

When query returns a set of rows:

```php
$command = $connection->createCommand('SELECT * FROM tbl_post');
$posts = $command->queryAll();
```

When only a single row is returned:

```php
$command = $connection->createCommand('SELECT * FROM tbl_post LIMIT 1');
$post = $command->query();
```

When there are multiple values from the same column:

```php
$command = $connection->createCommand('SELECT title FROM tbl_post');
$titles = $command->queryColumn();
```

When there's a scalar value:

```php
$command = $connection->createCommand('SELECT COUNT(*) FROM tbl_post');
$postCount = $command->queryScalar();
```

### UPDATE, INSERT, DELETE etc.

If SQL executed doesn't return any data you can use command's `execute` method:

```php
$command = $connection->createCommand('UPDATE tbl_post SET status=1');
$command->execute();
```

Alternatively the following syntax is possible:

```php
// INSERT
$connection->createCommand()->insert('tbl_user', array(
	'name' => 'Sam',
	'age' => 30,
))->execute();

// INSERT multiple rows at once
$connection->createCommand()->batchInsert('tbl_user', array('name', 'age'), array(
	array('Tom', 30),
	array('Jane', 20),
	array('Linda', 25),
))->execute();

// UPDATE
$connection->createCommand()->update('tbl_user', array(
	'status' => 1,
), 'age > 30')->execute();

// DELETE
$connection->createCommand()->delete('tbl_user', 'status = 0')->execute();
```


Prepared statements
-------------------

In order to securely pass query parameters you can use prepared statements:

```php
$command = $connection->createCommand('SELECT * FROM tbl_post WHERE id=:id');
$command->bindValue(':id', $_GET['id']);
$post = $command->query();
```

Another usage is performing a query multiple times while preparing it only once:

```php
$command = $connection->createCommand('DELETE FROM tbl_post WHERE id=:id');
$command->bindParam(':id', $id);

$id = 1;
$command->execute();

$id = 2;
$command->execute();
```

Transactions
------------

If the underlying DBMS supports transactions, you can perform transactional SQL queries like the following:

```php
$transaction = $connection->beginTransaction();
try {
	$connection->createCommand($sql1)->execute();
 	$connection->createCommand($sql2)->execute();
	// ... executing other SQL statements ...
	$transaction->commit();
} catch(Exception $e) {
	$transaction->rollback();
}
```

Working with database schema
----------------------------

### Getting schema information

You can get a [[\yii\db\Schema]] instance like the following:

```php
$schema = $connection->getSchema();
```

It contains a set of methods allowing you to retrieve various information about the database:

```php
$tables = $schema->getTableNames();
```

For the full reference check [[\yii\db\Schema]].

### Modifying schema

Aside from basic SQL queries [[\yii\db\Command]] contains a set of methods allowing to modify database schema:

- createTable, renameTable, dropTable, truncateTable
- addColumn, renameColumn, dropColumn, alterColumn
- addPrimaryKey, dropPrimaryKey
- addForeignKey, dropForeignKey
- createIndex, dropIndex

These can be used as follows:

```php
// UPDATE
$connection->createCommand()->createTable('tbl_post', array(
	'id' => 'pk',
	'title' => 'string',
	'text' => 'text',
);
```

For the full reference check [[\yii\db\Command]].
