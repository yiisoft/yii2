Database Migration
==================

<<<<<<< HEAD
> Note: This section is under development.

Like source code, the structure of a database evolves as a database-driven application is developed and maintained. For example, during development, a new table may be added, or after the application goes live it may be discovered that an additional index is required. It is important to keep track of these structural database changes (called **migration**), just as changes to the source code is tracked using version control. If the source code and the database become out of sync, bugs will occur, or the whole application might break. For this reason, Yii provides a database migration
tool that can keep track of your database migration history, apply new migrations, or revert existing ones.

The following steps show how database migration is used by a team during development:
=======
During the course of developing and maintaining a database-driven application, the structure of the database
being used evolves just like the source code does. For example, during the development of an application, 
a new table may be found necessary; after the application is deployed to production, it may be discovered
that an index should be created to improve the query performance; and so on. Because a database structure change 
often requires some source code changes, Yii supports the so-called *database migration* feature that allows
you to keep track of database changes in terms of *database migrations* which are version-controlled together
with the source code.

The following steps show how database migration can be used by a team during development:
>>>>>>> yiichina/master

1. Tim creates a new migration (e.g. creates a new table, changes a column definition, etc.).
2. Tim commits the new migration into the source control system (e.g. Git, Mercurial).
3. Doug updates his repository from the source control system and receives the new migration.
<<<<<<< HEAD
4. Doug applies the migration to his local development database, thereby syncing his database to reflect the changes Tim made.

Yii supports database migration via the `yii migrate` command line tool. This tool supports:

* Creating new migrations
* Applying, reverting, and redoing migrations
* Showing migration history and new migrations

Creating Migrations
-------------------
=======
4. Doug applies the migration to his local development database, thereby synchronizing his database 
   to reflect the changes that Tim has made.

And the following steps show how to deploy a new release with database migrations to production:

1. Scott creates a release tag for the project repository that contains some new database migrations.
2. Scott updates the source code on the production server to the release tag.
3. Scott applies any accumulated database migrations to the production database.

Yii provides a set of migration command line tools that allow you to:

* create new migrations;
* apply migrations;
* revert migrations;
* re-apply migrations;
* show migration history and status.

All these tools are accessible through the command `yii migrate`. In this section we will describe in detail
how to accomplish various tasks using these tools. You may also get the usage of each tool via the help
command `yii help migrate`.

> Note: migrations could affect not only database schema but adjust existing data to fit new schema, create RBAC
  hierarcy or clean up cache.


## Creating Migrations <span id="creating-migrations"></span>
>>>>>>> yiichina/master

To create a new migration, run the following command:

```
yii migrate/create <name>
```

<<<<<<< HEAD
The required `name` parameter specifies a very brief description of the migration. For example, if the migration creates a new table named *news*, you'd use the command:
=======
The required `name` argument gives a brief description about the new migration. For example, if 
the migration is about creating a new table named *news*, you may use the name `create_news_table`
and run the following command:
>>>>>>> yiichina/master

```
yii migrate/create create_news_table
```

<<<<<<< HEAD
As you'll shortly see, the `name` parameter
is used as part of a PHP class name in the migration. Therefore, it should only contain letters,
digits and/or underscore characters.

The above command will create a new
file named `m101129_185401_create_news_table.php`. This file will be created within the `@app/migrations` directory. Initially, the migration file will be generated with the following code:

```php
class m101129_185401_create_news_table extends \yii\db\Migration
=======
> Note: Because the `name` argument will be used as part of the generated migration class name,
  it should only contain letters, digits, and/or underscore characters.

The above command will create a new PHP class file named `m150101_185401_create_news_table.php`
in the `@app/migrations` directory. The file contains the following code which mainly declares
a migration class `m150101_185401_create_news_table` with the skeleton code:

```php
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
>>>>>>> yiichina/master
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

<<<<<<< HEAD
Notice that the class name is the same as the file name, and follows the pattern
`m<timestamp>_<name>`, where:

* `<timestamp>` refers to the UTC timestamp (in the
format of `yymmdd_hhmmss`) when the migration is created,
* `<name>` is taken from the command's `name` parameter.

In the class, the `up()` method should contain the code implementing the actual database
migration. In other words, the `up()` method executes code that actually changes the database. The `down()` method may contain code that reverts the changes made by `up()`.

Sometimes, it is impossible for the `down()` to undo the database migration. For example, if the migration deletes
table rows or an entire table, that data cannot be recovered in the `down()` method. In such
cases, the migration is called irreversible, meaning the database cannot be rolled back to
a previous state. When a migration is irreversible, as in the above generated code, the `down()`
method returns `false` to indicate that the migration cannot be reverted.

As an example, let's show the migration for creating a news table.
=======
Each database migration is defined as a PHP class extending from [[yii\db\Migration]]. The migration
class name is automatically generated in the format of `m<YYMMDD_HHMMSS>_<Name>`, where

* `<YYMMDD_HHMMSS>` refers to the UTC datetime at which the migration creation command is executed.
* `<Name>` is the same as the value of the `name` argument that you provide to the command.

In the migration class, you are expected to write code in the `up()` method that makes changes to the database structure.
You may also want to write code in the `down()` method to revert the changes made by `up()`. The `up()` method is invoked
when you upgrade the database with this migration, while the `down()` method is invoked when you downgrade the database.
The following code shows how you may implement the migration class to create a `news` table: 
>>>>>>> yiichina/master

```php

use yii\db\Schema;
<<<<<<< HEAD

class m101129_185401_create_news_table extends \yii\db\Migration
=======
use yii\db\Migration;

class m150101_185401_create_news_table extends \yii\db\Migration
>>>>>>> yiichina/master
{
    public function up()
    {
        $this->createTable('news', [
<<<<<<< HEAD
            'id' => 'pk',
=======
            'id' => Schema::TYPE_PK,
>>>>>>> yiichina/master
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'content' => Schema::TYPE_TEXT,
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }

}
```

<<<<<<< HEAD
The base class [[\yii\db\Migration]] exposes a database connection via the `db`
property. You can use it for manipulating data and the schema of a database.

The column types used in this example are abstract types that will be replaced
by Yii with the corresponding types depending on your database management system.
You can use them to write database independent migrations.
For example `pk` will be replaced by `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`
for MySQL and `integer PRIMARY KEY AUTOINCREMENT NOT NULL` for sqlite.
See documentation of [[yii\db\QueryBuilder::getColumnType()]] for more details and a list
of available types. You may also use the constants defined in [[yii\db\Schema]] to
define column types.

> Note: You can add constraints and other custom table options at the end of the table description by
> specifying them as a simple string. For example, in the above migration, after the `content` attribute definition
> you can write `'CONSTRAINT ...'` or other custom options.


Transactional Migrations
------------------------

While performing complex DB migrations, we usually want to make sure that each
migration succeeds or fail as a whole so that the database maintains its
consistency and integrity. In order to achieve this goal, we can exploit
DB transactions. We use the special methods `safeUp` and `safeDown` for these purposes.
=======
> Info: Not all migrations are reversible. For example, if the `up()` method deletes a row of a table, you may
  not be able to recover this row in the `down()` method. Sometimes, you may be just too lazy to implement 
  the `down()`, because it is not very common to revert database migrations. In this case, you should return
  `false` in the `down()` method to indicate that the migration is not reversible.

The base migration class [[yii\db\Migration]] exposes a database connection via the [[yii\db\Migration::db|db]]
property. You can use it to manipulate the database schema using the methods as described in 
[Working with Database Schema](db-dao.md#working-with-database-schema-).

Rather than using physical types, when creating a table or column you should use *abstract types*
so that your migrations are independent of specific DBMS. The [[yii\db\Schema]] class defines
a set of constants to represent the supported abstract types. These constants are named in the format
of `TYPE_<Name>`. For example, `TYPE_PK` refers to auto-incremental primary key type; `TYPE_STRING`
refers to a string type. When a migration is applied to a particular database, the abstract types
will be translated into the corresponding physical types. In the case of MySQL, `TYPE_PK` will be turned
into `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, while `TYPE_STRING` becomes `varchar(255)`.

You can append additional constraints when using abstract types. In the above example, ` NOT NULL` is appended
to `Schema::TYPE_STRING` to specify that the column cannot be null.

> Info: The mapping between abstract types and physical types is specified by 
  the [[yii\db\QueryBuilder::$typeMap|$typeMap]] property in each concrete `QueryBuilder` class.


### Transactional Migrations <span id="transactional-migrations"></span>

While performing complex DB migrations, it is important to ensure each migration to either succeed or fail as a whole
so that the database can maintain integrity and consistency. To achieve this goal, it is recommended that you 
enclose the DB operations of each migration in a [transaction](db-dao.md#performing-transactions-).
 
An even easier way of implementing transactional migrations is to put migration code in the `safeUp()` and `safeDown()` 
methods. These two methods differ from `up()` and `down()` in that they are enclosed implicitly in a transaction.
As a result, if any operation in these methods fails, all prior operations will be rolled back automatically.

In the following example, besides creating the `news` table we also insert an initial row into this table.
>>>>>>> yiichina/master

```php

use yii\db\Schema;
<<<<<<< HEAD

class m101129_185401_create_news_table extends \yii\db\Migration
=======
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
>>>>>>> yiichina/master
{
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => 'pk',
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'content' => Schema::TYPE_TEXT,
        ]);
<<<<<<< HEAD

        $this->createTable('user', [
            'id' => 'pk',
            'login' => Schema::TYPE_STRING . ' NOT NULL',
            'password' => Schema::TYPE_STRING . ' NOT NULL',
=======
        
        $this->insert('news', [
            'title' => 'test 1',
            'content' => 'content 1',
>>>>>>> yiichina/master
        ]);
    }

    public function safeDown()
    {
<<<<<<< HEAD
        $this->dropTable('news');
        $this->dropTable('user');
    }

}
```

When your code uses more then one query it is recommended to use `safeUp` and `safeDown`.

> Note: Not all DBMS support transactions. And some DB queries cannot be put
> into a transaction. In this case, you will have to implement `up()` and
> `down()`, instead. In the case of MySQL, some SQL statements may cause
> [implicit commit](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html).


Applying Migrations
-------------------

To apply all available new migrations (i.e., make the local database up-to-date),
run the following command:
=======
        $this->delete('news', ['id' => 1]);
        $this->dropTable('news');
    }
}
```

Note that usually when you perform multiple DB operations in `safeUp()`, you should reverse their execution order 
in `safeDown()`. In the above example we first create the table and then insert a row in `safeUp()`; while
in `safeDown()` we first delete the row and then drop the table.

> Note: Not all DBMS support transactions. And some DB queries cannot be put into a transaction. For some examples,
  please refer to [implicit commit](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html). If this is the case,
  you should still implement `up()` and `down()`, instead.


### Database Accessing Methods <span id="db-accessing-methods"></span>

The base migration class [[yii\db\Migration]] provides a set of methods to let you access and manipulate databases.
You may find these methods are named similarly as the [DAO methods](db-dao.md) provided by the [[yii\db\Command]] class. 
For example, the [[yii\db\Migration::createTable()]] method allows you to create a new table, 
just like [[yii\db\Command::createTable()]] does. 

The benefit of using the methods provided by [[yii\db\Migration]] is that you do not need to explicitly 
create [[yii\db\Command]] instances and the execution of each method will automatically display useful messages 
telling you what database operations are done and how long they take.

Below is the list of all these database accessing methods:

* [[yii\db\Migration::execute()|execute()]]: executing a SQL statement
* [[yii\db\Migration::insert()|insert()]]: inserting a single row
* [[yii\db\Migration::batchInsert()|batchInsert()]]: inserting multiple rows
* [[yii\db\Migration::update()|update()]]: updating rows
* [[yii\db\Migration::delete()|delete()]]: deleting rows
* [[yii\db\Migration::createTable()|createTable()]]: creating a table
* [[yii\db\Migration::renameTable()|renameTable()]]: renaming a table
* [[yii\db\Migration::dropTable()|dropTable()]]: removing a table
* [[yii\db\Migration::truncateTable()|truncateTable()]]: removing all rows in a table
* [[yii\db\Migration::addColumn()|addColumn()]]: adding a column
* [[yii\db\Migration::renameColumn()|renameColumn()]]: renaming a column
* [[yii\db\Migration::dropColumn()|dropColumn()]]: removing a column
* [[yii\db\Migration::alterColumn()|alterColumn()]]: altering a column
* [[yii\db\Migration::addPrimaryKey()|addPrimaryKey()]]: adding a primary key
* [[yii\db\Migration::dropPrimaryKey()|dropPrimaryKey()]]: removing a primary key
* [[yii\db\Migration::addForeignKey()|addForeignKey()]]: adding a foreign key
* [[yii\db\Migration::dropForeignKey()|dropForeignKey()]]: removing a foreign key
* [[yii\db\Migration::createIndex()|createIndex()]]: creating an index
* [[yii\db\Migration::dropIndex()|dropIndex()]]: removing an index

> Info: [[yii\db\Migration]] does not provide a database query method. This is because you normally do not need
  to display extra message about retrieving data from a database. It is also because you can use the powerful
  [Query Builder](db-query-builder.md) to build and run complex queries.


## Applying Migrations <span id="applying-migrations"></span>

To upgrade a database to its latest structure, you should apply all available new migrations using the following command:
>>>>>>> yiichina/master

```
yii migrate
```

<<<<<<< HEAD
The command will show the list of all new migrations. If you confirm you want to apply
the migrations, it will run the `up()` method in every new migration class, one
after another, in the order of the timestamp value in the class name.

After applying a migration, the migration tool will keep a record in a database
table named `migration`. This allows the tool to identify which migrations
have been applied and which have not. If the `migration` table does not exist,
the tool will automatically create it in the database specified by the `db`
[application component](structure-application-components.md).

Sometimes, we may only want to apply one or a few new migrations. We can use the
following command:

```
yii migrate/up 3
```

This command will apply the next 3 new migrations. Changing the value 3 will allow
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

To revert the last migration step or several applied migrations, we can use the following
command:

```
yii migrate/down [step]
```

where the optional `step` parameter specifies how many migrations to be reverted
back. It defaults to 1, meaning only the last applied migration will be reverted back.

As we described before, not all migrations can be reverted. Trying to revert
such migrations will throw an exception and stop the entire reverting process.


Redoing Migrations
------------------

Redoing migrations means first reverting and then applying the specified migrations.
This can be done with the following command:

```
yii migrate/redo [step]
```

where the optional `step` parameter specifies how many migrations to be redone.
It defaults to 1, which means only the last migration will be redone.


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

The migration command comes with a few options that can be specified on the command
line:

* `interactive`: boolean, specifies whether to perform migrations in an
  interactive mode. Defaults to true, meaning the user will be prompted when
  performing a specific migration. You may set this to false so the
  migrations are performed as a background process.

* `migrationPath`: string, specifies the directory storing all migration class
  files. This must be specified in terms of a path alias, and the corresponding
  directory must exist. If not specified, it will use the `migrations`
  sub-directory under the application base path.

* `migrationTable`: string, specifies the name of the database table for storing
  migration history information. It defaults to `migration`. The table
  structure is `version varchar(255) primary key, apply_time integer`.

* `db`: string, specifies the ID of the database [application component](structure-application-components.md).
  Defaults to 'db'.

* `templateFile`: string, specifies the path of the file to be served as the code
  template for generating the migration classes. This must be specified in terms
  of a path alias (e.g. `application.migrations.template`). If not set, an
  internal template will be used. Inside the template, the token `{ClassName}`
  will be replaced with the actual migration class name.

To specify these options, execute the migrate command using the following format:

```
yii migrate/up --option1=value1 --option2=value2 ...
```
=======
This command will list all migrations that have not been applied so far. If you confirm that you want to apply
these migrations, it will run the `up()` or `safeUp()` method in every new migration class, one after another, 
in the order of their timestamp values. If any of the migrations fails, the command will quit without applying
the rest of the migrations.

For each migration that has been successfully applied, the command will insert a row into a database table named 
`migration` to record the successful application of the migration. This will allow the migration tool to identify
which migrations have been applied and which have not.

> Info: The migration tool will automatically create the `migration` table in the database specified by
  the [[yii\console\controllers\MigrateController::db|db]] option of the command. By default, the database
  is specified by the `db` [application component](structure-application-components.md).
  
Sometimes, you may only want to apply one or a few new migrations, instead of all available migrations.
You can do so by specifying the number of migrations that you want to apply when running the command.
For example, the following command will try to apply the next three available migrations:

```
yii migrate 3
```

You can also explicitly specify a particular migration to which the database should be migrated
by using the `migrate/to` command in one of the following formats:

```
yii migrate/to 150101_185401                      # using timestamp to specify the migration
yii migrate/to "2015-01-01 18:54:01"              # using a string that can be parsed by strtotime()
yii migrate/to m150101_185401_create_news_table   # using full name
yii migrate/to 1392853618                         # using UNIX timestamp
```

If there are any unapplied migrations earlier than the specified one, they will all be applied before the specified
migration is applied.

If the specified migration has already been applied before, any later applied migrations will be reverted.


## Reverting Migrations <span id="reverting-migrations"></span>

To revert (undo) one or multiple migrations that have been applied before, you can run the following command:

```
yii migrate/down     # revert the most recently applied migration
yii migrate/down 3   # revert the most 3 recently applied migrations
```

> Note: Not all migrations are reversible. Trying to revert such migrations will cause an error and stop the
  entire reverting process.


## Redoing Migrations <span id="redoing-migrations"></span>

Redoing migrations means first reverting the specified migrations and then applying again. This can be done
as follows:

```
yii migrate/redo        # redo the last applied migration 
yii migrate/redo 3      # redo the last 3 applied migrations
```

> Note: If a migration is not reversible, you will not be able to redo it.


## Listing Migrations <span id="listing-migrations"></span>

To list which migrations have been applied and which are not, you may use the following commands:

```
yii migrate/history     # showing the last 10 applied migrations
yii migrate/history 5   # showing the last 5 applied migrations
yii migrate/history all # showing all applied migrations

yii migrate/new         # showing the first 10 new migrations
yii migrate/new 5       # showing the first 5 new migrations
yii migrate/new all     # showing all new migrations
```


## Modifying Migration History <span id="modifying-migration-history"></span>

Instead of actually applying or reverting migrations, sometimes you may simply want to mark that your database
has been upgraded to a particular migration. This often happens when you manually change the database to a particular
state and you do not want the migration(s) for that change to be re-applied later. You can achieve this goal with
the following command:

```
yii migrate/mark 150101_185401                      # using timestamp to specify the migration
yii migrate/mark "2015-01-01 18:54:01"              # using a string that can be parsed by strtotime()
yii migrate/mark m150101_185401_create_news_table   # using full name
yii migrate/mark 1392853618                         # using UNIX timestamp
```

The command will modify the `migration` table by adding or deleting certain rows to indicate that the database
has been applied migrations to the specified one. No migrations will be applied or reverted by this command.


## Customizing Migrations <span id="customizing-migrations"></span>

There are several ways to customize the migration command.


### Using Command Line Options <span id="using-command-line-options"></span>

The migration command comes with a few command-line options that can be used to customize its behaviors:

* `interactive`: boolean (defaults to true), specifies whether to perform migrations in an interactive mode. 
  When this is true, the user will be prompted before the command performs certain actions.
  You may want to set this to false if the command is being used in a background process.

* `migrationPath`: string (defaults to `@app/migrations`), specifies the directory storing all migration 
  class files. This can be specified as either a directory path or a path [alias](concept-aliases.md). 
  Note that the directory must exist, or the command may trigger an error.

* `migrationTable`: string (defaults to `migration`), specifies the name of the database table for storing
  migration history information. The table will be automatically created by the command if it does not exist.
  You may also manually create it using the structure `version varchar(255) primary key, apply_time integer`.

* `db`: string (defaults to `db`), specifies the ID of the database [application component](structure-application-components.md).
  It represents the database that will be migrated using this command.

* `templateFile`: string (defaults to `@yii/views/migration.php`), specifies the path of the template file
  that is used for generating skeleton migration class files. This can be specified as either a file path
  or a path [alias](concept-aliases.md). The template file is a PHP script in which you can use a predefined variable
  named `$className` to get the migration class name.

The following example shows how you can use these options.
>>>>>>> yiichina/master

For example, if we want to migrate a `forum` module whose migration files
are located within the module's `migrations` directory, we can use the following
command:

```
<<<<<<< HEAD
yii migrate/up --migrationPath=@app/modules/forum/migrations
```


### Configure Command Globally

While command line options allow us to configure the migration command
on-the-fly, sometimes we may want to configure the command once for all.
For example, we may want to use a different table to store the migration history,
or we may want to use a customized migration template. We can do so by modifying
the console application's configuration file like the following,

```php
'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationTable' => 'my_custom_migrate_table',
    ],
]
```

Now if we run the `migrate` command, the above configurations will take effect
without requiring us to enter the command line options every time. Other command options
can be also configured this way.


### Migrating with Multiple Databases

By default, migrations will be applied to the database specified by the `db` [application component](structure-application-components.md).
You may change it by specifying the `--db` option, for example,
=======
# migrate the migrations in a forum module non-interactively
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```


### Configuring Command Globally <span id="configuring-command-globally"></span>

Instead of entering the same option values every time you run the migration command, you may configure it
once for all in the application configuration like shown below:

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationTable' => 'backend_migration',
        ],
    ],
];
```

With the above configuration, each time you run the migration command, the `backend_migration` table
will be used to record the migration history. You no longer need to specify it via the `migrationTable`
command-line option.


## Migrating Multiple Databases <span id="migrating-multiple-databases"></span>

By default, migrations are applied to the same database specified by the `db` [application component](structure-application-components.md).
If you want them to be applied to a different database, you may specify the `db` command-line option like shown below,
>>>>>>> yiichina/master

```
yii migrate --db=db2
```

<<<<<<< HEAD
The above command will apply *all* migrations found in the default migration path to the `db2` database.

If your application works with multiple databases, it is possible that some migrations should be applied
to one database while some others should be applied to another database. In this case, it is recommended that
you create a base migration class for each different database and override the [[yii\db\Migration::init()]]
method like the following,

```php
public function init()
{
    $this->db = 'db2';
    parent::init();
}
```

To create a migration that should be applied to a particular database, simply extend from the corresponding
base migration class. Now if you run the `yii migrate` command, each migration will be applied to its corresponding database.

> Info: Because each migration uses a hardcoded DB connection, the `--db` option of the `migrate` command will
  have no effect. Also note that the migration history will be stored in the default `db` database.

If you want to support changing the DB connection via the `--db` option, you may take the following alternative
approach to work with multiple databases.

For each database, create a migration path and save all corresponding migration classes there. To apply migrations,
run the command as follows,
=======
The above command will apply migrations to the `db2` database.

Sometimes it may happen that you want to apply *some* of the migrations to one database, while some others to another
database. To achieve this goal, when implementing a migration class you should explicitly specify the DB component
ID that the migration would use, like the following:

```php
use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function init()
    {
        $this->db = 'db2';
        parent::init();
    }
}
```

The above migration will be applied to `db2`, even if you specify a different database through the `db` command-line
option. Note that the migration history will still be recorded in the database specified by the `db` command-line option.

If you have multiple migrations that use the same database, it is recommended that you create a base migration class
with the above `init()` code. Then each migration class can extend from this base class.

> Tip: Besides setting the [[yii\db\Migration::db|db]] property, you can also operate on different databases
  by creating new database connections to them in your migration classes. You then use the [DAO methods](db-dao.md)
  with these connections to manipulate different databases.

Another strategy that you can take to migrate multiple databases is to keep migrations for different databases in
different migration paths. Then you can migrate these databases in separate commands like the following:
>>>>>>> yiichina/master

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

<<<<<<< HEAD
> Info: The above approach stores the migration history in different databases specified via the `--db` option.
=======
The first command will apply migrations in `@app/migrations/db1` to the `db1` database, the second command
will apply migrations in `@app/migrations/db2` to `db2`, and so on.
>>>>>>> yiichina/master
