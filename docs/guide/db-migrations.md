Database Migration
==================

> Note: This section is under development.

Like source code, the structure of a database evolves as a database-driven application is developed and maintained. For example, during development, a new table may be added, or after the application goes live it may be discovered that an additional index is required. It is important to keep track of these structural database changes (called **migration**), just as changes to the source code is tracked using version control. If the source code and the database become out of sync, bugs will occur, or the whole application might break. For this reason, Yii provides a database migration
tool that can keep track of your database migration history, apply new migrations, or revert existing ones.

The following steps show how database migration is used by a team during development:

1. Tim creates a new migration (e.g. creates a new table, changes a column definition, etc.).
2. Tim commits the new migration into the source control system (e.g. Git, Mercurial).
3. Doug updates his repository from the source control system and receives the new migration.
4. Doug applies the migration to his local development database, thereby syncing his database to reflect the changes Tim made.

Yii supports database migration via the `yii migrate` command line tool. This tool supports:

* Creating new migrations
* Applying, reverting, and redoing migrations
* Showing migration history and new migrations

Creating Migrations
-------------------

To create a new migration, run the following command:

```
yii migrate/create <name>
```

The required `name` parameter specifies a very brief description of the migration. For example, if the migration creates a new table named *news*, you'd use the command:

```
yii migrate/create create_news_table
```

As you'll shortly see, the `name` parameter
is used as part of a PHP class name in the migration. Therefore, it should only contain letters,
digits and/or underscore characters.

The above command will create a new
file named `m101129_185401_create_news_table.php`. This file will be created within the `@app/migrations` directory. Initially, the migration file will be generated with the following code:

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

```php

use yii\db\Schema;

class m101129_185401_create_news_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => 'pk',
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

```php

use yii\db\Schema;

class m101129_185401_create_news_table extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => 'pk',
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'content' => Schema::TYPE_TEXT,
        ]);

        $this->createTable('user', [
            'id' => 'pk',
            'login' => Schema::TYPE_STRING . ' NOT NULL',
            'password' => Schema::TYPE_STRING . ' NOT NULL',
        ]);
    }

    public function safeDown()
    {
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

```
yii migrate
```

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
