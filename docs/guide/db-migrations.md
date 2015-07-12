Database Migration
==================

During the course of developing and maintaining a database-driven application, the structure of the database
being used evolves just like the source code does. For example, during the development of an application, 
a new table may be found necessary; after the application is deployed to production, it may be discovered
that an index should be created to improve the query performance; and so on. Because a database structure change 
often requires some source code changes, Yii supports the so-called *database migration* feature that allows
you to keep track of database changes in terms of *database migrations* which are version-controlled together
with the source code.

The following steps show how database migration can be used by a team during development:

1. Tim creates a new migration (e.g. creates a new table, changes a column definition, etc.).
2. Tim commits the new migration into the source control system (e.g. Git, Mercurial).
3. Doug updates his repository from the source control system and receives the new migration.
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

To create a new migration, run the following command:

```
yii migrate/create <name>
```

The required `name` argument gives a brief description about the new migration. For example, if 
the migration is about creating a new table named *news*, you may use the name `create_news_table`
and run the following command:

```
yii migrate/create create_news_table
```

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

Each database migration is defined as a PHP class extending from [[yii\db\Migration]]. The migration
class name is automatically generated in the format of `m<YYMMDD_HHMMSS>_<Name>`, where

* `<YYMMDD_HHMMSS>` refers to the UTC datetime at which the migration creation command is executed.
* `<Name>` is the same as the value of the `name` argument that you provide to the command.

In the migration class, you are expected to write code in the `up()` method that makes changes to the database structure.
You may also want to write code in the `down()` method to revert the changes made by `up()`. The `up()` method is invoked
when you upgrade the database with this migration, while the `down()` method is invoked when you downgrade the database.
The following code shows how you may implement the migration class to create a `news` table: 

```php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => Schema::TYPE_PK,
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
  
Since 2.0.5 schema builder which provides more convenient way defining column schema was introduced so migration above
could be written like the following:

```php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => Schema::primaryKey(),
            'title' => Schema::string()->notNull(),
            'content' => Schema::text(),
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }

}
```


### Transactional Migrations <span id="transactional-migrations"></span>

While performing complex DB migrations, it is important to ensure each migration to either succeed or fail as a whole
so that the database can maintain integrity and consistency. To achieve this goal, it is recommended that you 
enclose the DB operations of each migration in a [transaction](db-dao.md#performing-transactions-).
 
An even easier way of implementing transactional migrations is to put migration code in the `safeUp()` and `safeDown()` 
methods. These two methods differ from `up()` and `down()` in that they are enclosed implicitly in a transaction.
As a result, if any operation in these methods fails, all prior operations will be rolled back automatically.

In the following example, besides creating the `news` table we also insert an initial row into this table.

```php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => Schema::primaryKey(),,
            'title' => Schema::string()->notNull(),
            'content' => Schema::text(),
        ]);
        
        $this->insert('news', [
            'title' => 'test 1',
            'content' => 'content 1',
        ]);
    }

    public function safeDown()
    {
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

```
yii migrate
```

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

For example, if we want to migrate a `forum` module whose migration files
are located within the module's `migrations` directory, we can use the following
command:

```
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

```
yii migrate --db=db2
```

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

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

The first command will apply migrations in `@app/migrations/db1` to the `db1` database, the second command
will apply migrations in `@app/migrations/db2` to `db2`, and so on.
