数据库迁移
==================

> 注意：这部分正在开发中。

如源代码，数据库结构演变成为一个数据库驱动程序来进行开发与维护。例如，在开发过程中，可以添加一个新表，or after the application goes live it may be discovered that an additional index is required. It is important to keep track of these structural database changes (called **migration**), just as changes to the source code is tracked using version control. If the source code and the database become out of sync, bugs will occur, or the whole application might break. For this reason, Yii provides a database migration
tool that can keep track of your database migration history, apply new migrations, or revert existing ones.

下列步骤显示了一个团队在开发过程中如何进行 database migration：

1. Tim 创建了一个新的 migration (如创建一个新表，更改一个列定义，等)。
2. Tim 向版本控制系统提交了一个新的 migration (如 Git, Mercurial)。
3. Doug 从版本控制系统中更新了自己的资料库并接收新的 migration。
4. Doug 将 migration 应用到自己本地开发的数据库，从而同步他的数据库来反映 Tim 所做的更改。

Yii 通过 `yii migrate` 命令行工具来支持 database migration。此工具支持：

* 创建新的 migrations
* Applying, reverting, and redoing migrations
* Showing migration history and new migrations

Creating Migrations
-------------------

如果想要创建一个新的 migration，运行以下命令：

```
yii migrate/create <name>
```

The required `name` parameter specifies a very brief description of the migration. 例如，如果 migration 创建一个名为 *news* 的新表，应该使用如下命令：

```
yii migrate/create create_news_table
```

As you'll shortly see, the `name` parameter
is used as part of a PHP class name in the migration. 因此，应该只包含字母，
数字和/或下划线。

上面的命令将创建一个
名为 `m101129_185401_create_news_table.php` 的新文件。该文件将被创建在 `@app/migrations` 目录中。起初，migration 文件用下面代码来生成：

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

注意和类名相同的文件名，并且遵循
`m<timestamp>_<name>` 模式，where:

* `<timestamp>` refers to the UTC timestamp (in the
format of `yymmdd_hhmmss`) when the migration is created,
* `<name>` is taken from the command's `name` parameter.

In the class, the `up()` method should contain the code implementing the actual database
migration. In other words, the `up()` method executes code that actually changes the database. The `down()` method may contain code that reverts the changes made by `up()`.

Sometimes, it is impossible for the `down()` to undo the database migration. 例如，if the migration deletes
table rows or an entire table, that data cannot be recovered in the `down()` method. In such
cases, the migration is called irreversible, meaning the database cannot be rolled back to
a previous state. When a migration is irreversible, as in the above generated code, the `down()`
method returns `false` to indicate that the migration cannot be reverted.

作为一个例子，让我们来展示 migration 是如何创建一个新表的。

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

基类 [[\yii\db\Migration]] 通过 `db` 属性
展示数据库的链接。You can use it for manipulating data and the schema of a database.

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

当进行复杂的 DB migrations 时，we usually want to make sure that each
migration succeeds or fail as a whole so that the database maintains its
consistency and integrity. 为了实现这一目标，可以应用
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

为了应用所有的可用的新 migrations (i.e., make the local database up-to-date),
运行如下命令：

```
yii migrate
```

该命令将显示所有的新的 migrations 列表。如果你确定想要应用
migrations，可以在每一个新的 migration 类中运行 `up()` 方法，一个
接一个，按照类名中的 timestamp 值的顺序。

应用 migration 之后，migration 工具将会在 `migration` 表中
做一个记录。This allows the tool to identify which migrations
have been applied and which have not. If the `migration` table does not exist,
the tool will automatically create it in the database specified by the `db`
[application component](structure-application-components.md).

有时，我们可能会需要应用一个或几个新 migrations。可以使用
如下命令：

```
yii migrate/up 3
```

This command will apply the next 3 new migrations. Changing the value 3 will allow
us to change the number of migrations to be applied.

可以使用如下命令将数据库迁移成特定版本：

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

To revert the last migration step or several applied migrations, 可以使用如下
命令：

```
yii migrate/down [step]
```

where the optional `step` parameter specifies how many migrations to be reverted
back. It defaults to 1, meaning only the last applied migration will be reverted back.

正如我们之前所描述，不是所有的 migrations 可以被恢复。Trying to revert
such migrations will throw an exception and stop the entire reverting process.


Redoing Migrations
------------------

Redoing migrations means first reverting and then applying the specified migrations.
可以用如下命令来完成：

```
yii migrate/redo [step]
```

where the optional `step` parameter specifies how many migrations to be redone.
It defaults to 1, which means only the last migration will be redone.


Showing Migration Information
-----------------------------

除了应用和恢复 migrations，migration 工具也可以显示
历史 migration 和要应用的新的 migrations。

```
yii migrate/history [limit]
yii migrate/new [limit]
```

where the optional parameter `limit` specifies the number of migrations to be
displayed. If `limit` is not specified, all available migrations will be displayed.

第一个命令是显示已经应用的 migrations，第二个命令
显示还未应用的 migrations 。


Modifying Migration History
---------------------------

Sometimes, we may want to modify the migration history to a specific migration
version without actually applying or reverting the relevant migrations. This
often happens when developing a new migration. 我们可以使用如下命令
来实现这一目标。

```
yii migrate/mark 101129_185401
```

此命令与 `yii migrate/to` 命令非常相似，except that it only
modifies the migration history table to the specified version without applying
or reverting the migrations.


Customizing Migration Command
-----------------------------

有几种制定 migration 的命令。

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

例如，if we want to migrate a `forum` module whose migration files
are located within the module's `migrations` directory, 我们可以使用如下
命令：

```
yii migrate/up --migrationPath=@app/modules/forum/migrations
```


### Configure Command Globally

While command line options allow us to configure the migration command
on-the-fly, 有时我们可能需要通过配置命令一劳永逸。
例如，我们可能需要使用不同的表来存储历史 migrations，
或者我们可能需要使用自定义的 migrations 模板。We can do so by modifying
the console application's configuration file like the following,

```php
'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationTable' => 'my_custom_migrate_table',
    ],
]
```

现在如果我们运行 `migrate` 命令，不需要每次都进入命令行
上面的配置也将生效。其它命令选项
也可以用这种方法进行配置。


### Migrating with Multiple Databases

By default, migrations will be applied to the database specified by the `db` [application component](structure-application-components.md).
You may change it by specifying the `--db` option, 例如，

```
yii migrate --db=db2
```

The above command will apply *all* migrations found in the default migration path to the `db2` database.

如果你的应用程序使用多个数据库，it is possible that some migrations should be applied
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
base migration class. 现在如果你运行 `yii migrate` 命令，每一个 migration 将被应用到其相应的数据库中。

> 注意：Because each migration uses a hardcoded DB connection, the `--db` option of the `migrate` command will
  have no effect. Also note that the migration history will be stored in the default `db` database.

如果你想通过 `--db` 选项更改 DB 链接，你可以采用如下方法
使多个数据库一起工作。

对于每个数据库，添加一个迁移路径，在这里保存所有相关的迁移类。为了应用 migrations，
运行如下命令，

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

> 注意：The above approach stores the migration history in different databases specified via the `--db` option.
