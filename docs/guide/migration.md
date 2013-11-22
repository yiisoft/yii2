Database Migration
==================

Like source code, the structure of a database is evolving as we develop and maintain
a database-driven application. For example, during development, we may want to
add a new table; or after the application is put into production, we may realize
the need of adding an index on a column. It is important to keep track of these
structural database changes (called **migration**) like we do with our source
code. If the source code and the database are out of sync, it is very likely
the whole system may break. For this reason, Yii provides a database migration
tool that can keep track of database migration history, apply new migrations,
or revert existing ones.

The following steps show how we can use database migration during development:

1. Tim creates a new migration (e.g. create a new table)
2. Tim commits the new migration into source control system (e.g. GIT, Mercurial)
3. Doug updates from source control system and receives the new migration
4. Doug applies the migration to his local development database


Yii supports database migration via the `yii migrate` command line tool. This
tool supports creating new migrations, applying/reverting/redoing migrations, and
showing migration history and new migrations.

Creating Migrations
-------------------

To create a new migration (e.g. create a news table), we run the following command:

```
yii migrate/create <name>
```

The required `name` parameter specifies a very brief description of the migration
(e.g. `create_news_table`). As we will show in the following, the `name` parameter
is used as part of a PHP class name. Therefore, it should only contain letters,
digits and/or underscore characters.

```
yii migrate/create create_news_table
```

The above command will create under the `protected/migrations` directory a new
file named `m101129_185401_create_news_table.php` which contains the following
initial code:

```php
class m101129_185401_create_news_table extends \yii\db\Migration
{
	public function up()
	{
	}

	public function down()
	{
		echo "m101129_185401_create_news_table cannot be reverted.\n";
		return false;
	}
}
```

Notice that the class name is the same as the file name which is of the pattern
`m<timestamp>_<name>`, where `<timestamp>` refers to the UTC timestamp (in the
format of `yymmdd_hhmmss`) when the migration is created, and `<name>` is taken
from the command's `name` parameter.

The `up()` method should contain the code implementing the actual database
migration, while the `down()` method may contain the code reverting what is
done in `up()`.

Sometimes, it is impossible to implement `down()`. For example, if we delete
table rows in `up()`, we will not be able to recover them in `down()`. In this
case, the migration is called irreversible, meaning we cannot roll back to
a previous state of the database. In the above generated code, the `down()`
method returns `false` to indicate that the migration cannot be reverted.

As an example, let's show the migration about creating a news table.

```php
class m101129_185401_create_news_table extends \yii\db\Migration
{
	public function up()
	{
		$this->db->createCommand()->createTable('tbl_news', [
			'id' => 'pk',
			'title' => 'string(128) NOT NULL',
			'content' => 'text',
		])->execute();
	}

	public function down()
	{
		$this->db->createCommand()->dropTable('tbl_news')->execute();
	}
}
```

The base class [\yii\db\Migration] exposes a database connection via `db`
property. You can use it for manipulating data and schema of a database.

The column types used in this example are abstract types that will be replaced
by Yii with the corresponding types depended on your database management system.
You can use them to write database independent migrations.
For example `pk` will be replaced by `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`
for MySQL and `integer PRIMARY KEY AUTOINCREMENT NOT NULL` for sqlite.
See documentation of [[QueryBuilder::getColumnType()]] for more details and a list
of available types. You may also use the constants defined in [[\yii\db\Schema]] to
define column types.


Transactional Migrations
------------------------

While performing complex DB migrations, we usually want to make sure that each
migration succeed or fail as a whole so that the database maintains the
consistency and integrity. In order to achieve this goal, we can exploit
DB transactions.

We could explicitly start a DB transaction and enclose the rest of the DB-related
code within the transaction, like the following:

```php
class m101129_185401_create_news_table extends \yii\db\Migration
{
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
		try
		{
			$this->db->createCommand()->createTable('tbl_news', [
				'id' => 'pk',
				'title' => 'string NOT NULL',
				'content' => 'text',
			])->execute();
			$transaction->commit();
		}
		catch(Exception $e)
		{
			echo "Exception: ".$e->getMessage()."\n";
			$transaction->rollback();
			return false;
		}
	}

	// ...similar code for down()
}
```

> Note: Not all DBMS support transactions. And some DB queries cannot be put
> into a transaction. In this case, you will have to implement `up()` and
> `down()`, instead. And for MySQL, some SQL statements may cause
> [implicit commit](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html).


Applying Migrations
-------------------

To apply all available new migrations (i.e., make the local database up-to-date),
run the following command:

```
yii migrate
```

The command will show the list of all new migrations. If you confirm to apply
the migrations, it will run the `up()` method in every new migration class, one
after another, in the order of the timestamp value in the class name.

After applying a migration, the migration tool will keep a record in a database
table named `tbl_migration`. This allows the tool to identify which migrations
have been applied and which are not. If the `tbl_migration` table does not exist,
the tool will automatically create it in the database specified by the `db`
application component.

Sometimes, we may only want to apply one or a few new migrations. We can use the
following command:

```
yii migrate/up 3
```

This command will apply the 3 new migrations. Changing the value 3 will allow
us to change the number of migrations to be applied.

We can also migrate the database to a specific version with the following command:

```
yii migrate/to 101129_185401
```

That is, we use the timestamp part of a migration name to specify the version
that we want to migrate the database to. If there are multiple migrations between
the last applied migration and the specified migration, all these migrations
will be applied. If the specified migration has been applied before, then all
migrations applied after it will be reverted (to be described in the next section).


Reverting Migrations
--------------------

To revert the last one or several applied migrations, we can use the following
command:

```
yii migrate/down [step]
```

where the optional `step` parameter specifies how many migrations to be reverted
back. It defaults to 1, meaning reverting back the last applied migration.

As we described before, not all migrations can be reverted. Trying to revert
such migrations will throw an exception and stop the whole reverting process.


Redoing Migrations
------------------

Redoing migrations means first reverting and then applying the specified migrations.
This can be done with the following command:

```
yii migrate/redo [step]
```

where the optional `step` parameter specifies how many migrations to be redone.
It defaults to 1, meaning redoing the last migration.


Showing Migration Information
-----------------------------

Besides applying and reverting migrations, the migration tool can also display
the migration history and the new migrations to be applied.

```
yii migrate/history [limit]
yii migrate/new [limit]
```

where the optional parameter `limit` specifies the number of migrations to be
displayed. If `limit` is not specified, all available migrations will be displayed.

The first command shows the migrations that have been applied, while the second
command shows the migrations that have not been applied.


Modifying Migration History
---------------------------

Sometimes, we may want to modify the migration history to a specific migration
version without actually applying or reverting the relevant migrations. This
often happens when developing a new migration. We can use the following command
to achieve this goal.

```
yii migrate/mark 101129_185401
```

This command is very similar to `yii migrate/to` command, except that it only
modifies the migration history table to the specified version without applying
or reverting the migrations.


Customizing Migration Command
-----------------------------

There are several ways to customize the migration command.

### Use Command Line Options

The migration command comes with four options that can be specified in command
line:

* `interactive`: boolean, specifies whether to perform migrations in an
  interactive mode. Defaults to true, meaning the user will be prompted when
  performing a specific migration. You may set this to false should the
  migrations be done in a background process.

* `migrationPath`: string, specifies the directory storing all migration class
  files. This must be specified in terms of a path alias, and the corresponding
  directory must exist. If not specified, it will use the `migrations`
  sub-directory under the application base path.

* `migrationTable`: string, specifies the name of the database table for storing
  migration history information. It defaults to `tbl_migration`. The table
  structure is `version varchar(255) primary key, apply_time integer`.

* `connectionID`: string, specifies the ID of the database application component.
  Defaults to 'db'.

* `templateFile`: string, specifies the path of the file to be served as the code
  template for generating the migration classes. This must be specified in terms
  of a path alias (e.g. `application.migrations.template`). If not set, an
  internal template will be used. Inside the template, the token `{ClassName}`
  will be replaced with the actual migration class name.

To specify these options, execute the migrate command using the following format

```
yii migrate/up --option1=value1 --option2=value2 ...
```

For example, if we want to migrate for a `forum` module whose migration files
are located within the module's `migrations` directory, we can use the following
command:

```
yii migrate/up --migrationPath=ext.forum.migrations
```


### Configure Command Globally

While command line options allow us to configure the migration command
on-the-fly, sometimes we may want to configure the command once for all.
For example, we may want to use a different table to store the migration history,
or we may want to use a customized migration template. We can do so by modifying
the console application's configuration file like the following,

```php
TBD
```

Now if we run the `migrate` command, the above configurations will take effect
without requiring us to enter the command line options every time.
