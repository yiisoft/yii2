Database Migration
==================

> Note: This section is under development.

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
You may also want to write code in the `down()` method to revert the changes made by `up()`. The `up` method is invoked
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
[Working with Database Schema](db-dao.md#database-schema).

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
enclose the DB operations of each migration in a [transaction](db-dao.md#performing-transactions).
 
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
            'id' => 'pk',
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'content' => Schema::TYPE_TEXT,
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

For example, if we want to migrate a `forum` module whose migration files
are located within the module's `migrations` directory, we can use the following
command:

```
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

```
yii migrate --db=db2
```

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

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

> Info: The above approach stores the migration history in different databases specified via the `--db` option.
