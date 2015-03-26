<!--Database Migration-->
Миграции Баз Данных
==================

<!--
During the course of developing and maintaining a database-driven application, the structure of the database
being used evolves just like the source code does. For example, during the development of an application, 
a new table may be found necessary; after the application is deployed to production, it may be discovered
that an index should be created to improve the query performance; and so on. Because a database structure change 
often requires some source code changes, Yii supports the so-called *database migration* feature that allows
you to keep track of database changes in terms of *database migrations* which are version-controlled together
with the source code.
-->
В ходе разработки и ведения баз данных приложений, которые управляют данными, структуры используемых баз данных развиваются как и исходный код приложений. Например, при разработке приложения, в будущем может оказаться необходимой новая таблица; уже после того, как приложение будет развернуто в рабочем режиме (продакшене), также может быть обнаружено, что для повышения производительности запросов должен быть создан определённый индекс; и так далее.
В связи с тем, что изменение структуры базы данных часто требует изменение исходного кода, yii поддерживает так
называемую возможность *миграции баз данных*, которая позволяет отслеживать изменения в базах данных при помощи терминов *миграции баз данных*, которые являются системой контроля версий вместе с исходным кодом.

<!--The following steps show how database migration can be used by a team during development:-->
Следующие шаги показывают, как миграции базы данных могут быть использованны командой разработчиков в процессе разработки:

<!--
1. Tim creates a new migration (e.g. creates a new table, changes a column definition, etc.).
2. Tim commits the new migration into the source control system (e.g. Git, Mercurial).
3. Doug updates his repository from the source control system and receives the new migration.
4. Doug applies the migration to his local development database, thereby synchronizing his database 
   to reflect the changes that Tim has made.
-->
1. Илья создает новую миграцию (например, создается новая таблица или изменяется определение столбца и т.п.).
2. Илья фиксирует новую миграцию в системе управления версиями (например, в Git, Mercurial).
3. Алексей обновляет свой репозиторий из системы контроля версий и получает новую миграцию.
4. Алексей применяет миграцию к своей локальной базе данных, тем самым синхронизируя свою базу данных, для того чтобы отразить изменения, которые сделал Илья.

<!--And the following steps show how to deploy a new release with database migrations to production:-->
А следующие шаги показывают, как развернуть новый релиз с миграциями баз данных в рабочем режиме (продакшена):

<!--
1. Scott creates a release tag for the project repository that contains some new database migrations.
2. Scott updates the source code on the production server to the release tag.
3. Scott applies any accumulated database migrations to the production database.
-->
1. Сергей создаёт новую версию проекта репозитория, которая содержит некоторые новые миграции баз данных.
2. Сергей обновляет исходный код на рабочем сервере до новой версии.
3. Сергей применяет любую из накопленных миграций баз данных в рабочую базу данных.

<!--Yii provides a set of migration command line tools that allow you to:-->
Yii предоставляет набор инструментов для миграций из командной строки, которые позволяют:

<!--
* create new migrations;
* apply migrations;
* revert migrations;
* re-apply migrations;
* show migration history and status.
-->
* создавать новые миграции;
* применять миграции;
* отменять миграции;
* применять миграции повторно;
* показывать историю и статус миграций;

<!--
All these tools are accessible through the command `yii migrate`. In this section we will describe in detail
how to accomplish various tasks using these tools. You may also get the usage of each tool via the help
command `yii help migrate`.
-->
Все эти инструменты доступны через команду `yii migrate`. В этом разделе мы опишем подробно, как выполнять различные задачи, используя эти инструменты. Вы также можете сами посмотреть как использовать каждый отдельный инструмент при помощи команды `yii help migrate`.

## Создание миграций <span id="creating-migrations"></span>
<!-- Creating Migrations -->
<!--To create a new migration, run the following command:-->

Чтобы создать новую миграцию, выполните следующую команду:

```
yii migrate/create <name>
```
<!--
The required `name` argument gives a brief description about the new migration. For example, if 
the migration is about creating a new table named *news*, you may use the name `create_news_table`
and run the following command:
-->
Требуемый аргумент `name` даёт краткое описание новой миграции. Например, если миграция о создании новой таблицы с именем *news*, Вы можете использовать имя `create_news_table` и выполнить следующую команду:

```
yii migrate/create create_news_table
```

<!--
> Note: Because the `name` argument will be used as part of the generated migration class name,
  it should only contain letters, digits, and/or underscore characters.
-->
> Примечание: Поскольку аргумент `name` будет использован как часть имени класса создавамой миграции, он должен содержать только буквы, цифры и/или символы подчеркивания.

<!--
The above command will create a new PHP class file named `m150101_185401_create_news_table.php`
in the `@app/migrations` directory. The file contains the following code which mainly declares
a migration class `m150101_185401_create_news_table` with the skeleton code:
-->
Приведенная выше команда создаст новый PHP класс с именем файла `m150101_185401_create_news_table.php` в директории `@app/migrations`. Файл содержит следующий код, который главным образом декларирует класс миграции `m150101_185401_create_news_table` с следующим каркасом кода:

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

<!--
Each database migration is defined as a PHP class extending from [[yii\db\Migration]]. The migration
class name is automatically generated in the format of `m<YYMMDD_HHMMSS>_<Name>`, where
-->
Каждая миграция базы данных определяется как PHP класс расширяющийся от [[yii\db\Migration]]. Имя класса миграции автоматически создается в формате `m<YYMMDD_HHMMSS>_<Name>` (`m<ГодМесяцДень_ЧасыМинутыСекунды>_<Имя>`), где

<!--
* `<YYMMDD_HHMMSS>` refers to the UTC datetime at which the migration creation command is executed.
* `<Name>` is the same as the value of the `name` argument that you provide to the command.
-->
* `<YYMMDD_HHMMSS>` относится к UTC дате-времени при котором команда создания миграции была выполнена.
* `<Name>` это тоже самое значение аргумента `name` которое вы прописываете в команду.

<!--
In the migration class, you are expected to write code in the `up()` method that makes changes to the database structure.
You may also want to write code in the `down()` method to revert the changes made by `up()`. The `up` method is invoked
when you upgrade the database with this migration, while the `down()` method is invoked when you downgrade the database.
The following code shows how you may implement the migration class to create a `news` table: 
-->
В классе миграции, Вы должны прописать код в методе `up()` когда делаете изменения в структуре базы данных. 
Вы также можете написать код в методе `down()`, чтобы отменить сделанные `up()` изменения. Метод `up` вызывается для обновления базы данных с помощью данной миграции, а метод `down()` вызывается для отката изменений базы данных.
Следующий код показывает как можно реализовать класс миграции, чтобы создать таблицу `news`:

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

<!--
> Info: Not all migrations are reversible. For example, if the `up()` method deletes a row of a table, you may
  not be able to recover this row in the `down()` method. Sometimes, you may be just too lazy to implement 
  the `down()`, because it is not very common to revert database migrations. In this case, you should return
  `false` in the `down()` method to indicate that the migration is not reversible.
-->
> Для справки: Не все миграции являются обратимыми. Например, если метод `up()` удаляет строку из таблицы, возможно что у вас уже не будет возможности вернуть эту строку методом `down()`. Иногда Вам может быть просто слишком лень реализовывать метод `down()`, в связи с тем, что это не очень распространенно - откатывать миграции базы данных. В этом случае вы должны в методе `down()` вернуть `false`, чтобы указать, что миграция не является обратимой.

<!--
The base migration class [[yii\db\Migration]] exposes a database connection via the [[yii\db\Migration::db|db]]
property. You can use it to manipulate the database schema using the methods as described in 
[Working with Database Schema](db-dao.md#database-schema).
-->
Базовый класс миграций [[yii\db\Migration]] предоставляет подключение к базе данных через свойство [[yii\db\Migration::db|db]]. Вы можете использовать его для манипулирования схемой базы данных используя методы описанные в [работе со схемой базы данных](db-dao.md#database-schema).

<!--
Rather than using physical types, when creating a table or column you should use *abstract types*
so that your migrations are independent of specific DBMS. The [[yii\db\Schema]] class defines
a set of constants to represent the supported abstract types. These constants are named in the format
of `TYPE_<Name>`. For example, `TYPE_PK` refers to auto-incremental primary key type; `TYPE_STRING`
refers to a string type. When a migration is applied to a particular database, the abstract types
will be translated into the corresponding physical types. In the case of MySQL, `TYPE_PK` will be turned
into `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, while `TYPE_STRING` becomes `varchar(255)`.
-->
Вместо использования физических типов данных, при создании таблицы или столбца, следует использовать *абстрактные типы*
для того, чтобы ваша миграция являлась независимой от конкретной СУБД. Класс [[yii\db\Schema]] определяет набор констант для предоставления поддержки абстрактных типов. Эти константы называются в следующем формате `TYPE_<Name>`. Например,
`TYPE_PK` относится к типу автоинкремента (AUTO_INCREMENT) первичного ключа;
`TYPE_STRING` относится к строковому типу.
Когда миграция применяется к конкретной базе данных, абстрактные типы будут переведены в соответствующие физические типы.
В случае с MySQL, `TYPE_PK` будет превращено в `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, а `TYPE_STRING` станет `varchar(255)`.

<!--
You can append additional constraints when using abstract types. In the above example, ` NOT NULL` is appended
to `Schema::TYPE_STRING` to specify that the column cannot be null.
-->
Вы можете добавить дополнительные ограничения при использовании абстрактных типов. В приведенном выше примере, ` NOT NULL` добавляется к `Schema::TYPE_STRING` чтобы указать, что столбец не может быть NULL.

<!--
> Info: The mapping between abstract types and physical types is specified by 
  the [[yii\db\QueryBuilder::$typeMap|$typeMap]] property in each concrete `QueryBuilder` class.
-->
> Для справки: Сопоставление абстрактных типов и физических типов определяется свойством [[yii\db\QueryBuilder::$typeMap|$typeMap]] в каждом конкретном `QueryBuilder` классе.

### Транзакции Миграций <span id="transactional-migrations"></span>
<!-- Transactional Migrations -->
<!--
While performing complex DB migrations, it is important to ensure each migration to either succeed or fail as a whole
so that the database can maintain integrity and consistency. To achieve this goal, it is recommended that you 
enclose the DB operations of each migration in a [transaction](db-dao.md#performing-transactions).
 
An even easier way of implementing transactional migrations is to put migration code in the `safeUp()` and `safeDown()` 
methods. These two methods differ from `up()` and `down()` in that they are enclosed implicitly in a transaction.
As a result, if any operation in these methods fails, all prior operations will be rolled back automatically.
-->

При выполнении сложных миграций баз данных, важно обеспечить каждую миграцию либо успехом, либо ошибкой, в целом так, чтобы база данных могла поддерживать целостность и непротиворечивость. Для достижения данной цели рекомендуется, заключить операции каждой миграции базы данных в [транзакции](db-dao.md#performing-transactions).

Самый простой способ реализации транзакций миграций это прописать код миграций в методы `safeUp()` и `safeDown()`. Эти два метода отличаются от методов `up()` и `down()` тем, что они неявно заключены в транзакции. В результате, если какая-либо операция в этих методах не удается, все предыдущие операции будут отменены автоматически.

<!--
In the following example, besides creating the `news` table we also insert an initial row into this table.
-->
В следующем примере, помимо создания таблицы `news` мы также вставляем в этой таблице начальную строку.

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
<!--
Note that usually when you perform multiple DB operations in `safeUp()`, you should reverse their execution order 
in `safeDown()`. In the above example we first create the table and then insert a row in `safeUp()`; while
in `safeDown()` we first delete the row and then drop the table.
-->
Обратите внимание, что обычно при выполнении нескольких операций в базе данных при помощи метода `safeUp()`, вы должны реализовать обратный порядок исполнения в методе `safeDown()`. В приведенном выше примере мы сначала создали таблицу, а затем вставили строку в `safeUp()`; а в `safeDown()` мы сначала удаляем строку и затем удаляем таблицу.

<!--
> Note: Not all DBMS support transactions. And some DB queries cannot be put into a transaction. For some examples,
  please refer to [implicit commit](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html). If this is the case,
  you should still implement `up()` and `down()`, instead.
-->
> Примечание: Не все СУБД поддерживают транзакции. И некоторые запросы к базам данных не могут быть введены в транзакции. Для различных примеров, пожалуйста, обратитесь к [негласным обязательствам](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html). В этом случае вместо этих методов вы должны реализовать методы `up()` и `down()`.

### Методы доступа к базе данных <span id="db-accessing-methods"></span>
<!-- Database Accessing Methods -->
<!--
The base migration class [[yii\db\Migration]] provides a set of methods to let you access and manipulate databases.
You may find these methods are named similarly as the [DAO methods](db-dao.md) provided by the [[yii\db\Command]] class. 
For example, the [[yii\db\Migration::createTable()]] method allows you to create a new table, 
just like [[yii\db\Command::createTable()]] does.
-->
Базовый класс миграции [[yii\db\Migration]] предоставляет набор методов, которые позволяют Вам получить доступ и управлять базами данных. Вы можете найти эти методы, их названия аналогичны [методам DAO](db-dao.md), предоставленным в классе [[yii\db\Command]].
Например, метод [[yii\db\Migration::createTable()]] позволяет создать новую таблицу, подобно методу [[yii\db\Command::createTable()]].

<!--
The benefit of using the methods provided by [[yii\db\Migration]] is that you do not need to explicitly 
create [[yii\db\Command]] instances and the execution of each method will automatically display useful messages 
telling you what database operations are done and how long they take.
-->
Преимущество методов, описанных при помощи [[yii\db\Migration]] заключается в том, что Вам не нужно явно создавать экземпляр/копию [[yii\db\Command]] и исполнение каждого метода будет автоматически отображать полезные сообщения
говорящие вам, что операции с базой данных выполняются и сколько они идут.

<!--
Below is the list of all these database accessing methods:
-->
Ниже представлен список всех этих методов доступа к базам данных:

<!--
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
-->

* [[yii\db\Migration::execute()|execute()]]: выполнение SQL инструкции
* [[yii\db\Migration::insert()|insert()]]: вставка одной строки
* [[yii\db\Migration::batchInsert()|batchInsert()]]: вставка нескольких строк
* [[yii\db\Migration::update()|update()]]: обновление строк
* [[yii\db\Migration::delete()|delete()]]: удаление строк
* [[yii\db\Migration::createTable()|createTable()]]: создание таблицы
* [[yii\db\Migration::renameTable()|renameTable()]]: переименование таблицы
* [[yii\db\Migration::dropTable()|dropTable()]]: удаление таблицы
* [[yii\db\Migration::truncateTable()|truncateTable()]]: удаление всех строк в таблице
* [[yii\db\Migration::addColumn()|addColumn()]]: добавление столбца
* [[yii\db\Migration::renameColumn()|renameColumn()]]: переименование столбца
* [[yii\db\Migration::dropColumn()|dropColumn()]]: удаление столбца
* [[yii\db\Migration::alterColumn()|alterColumn()]]: изменения столбца
* [[yii\db\Migration::addPrimaryKey()|addPrimaryKey()]]: добавление первичного ключа
* [[yii\db\Migration::dropPrimaryKey()|dropPrimaryKey()]]: удаление первичного ключа
* [[yii\db\Migration::addForeignKey()|addForeignKey()]]: добавление внешнего ключа
* [[yii\db\Migration::dropForeignKey()|dropForeignKey()]]: удаление внешнего ключа
* [[yii\db\Migration::createIndex()|createIndex()]]: создание индекса
* [[yii\db\Migration::dropIndex()|dropIndex()]]: удаление индекса

<!--
> Info: [[yii\db\Migration]] does not provide a database query method. This is because you normally do not need
  to display extra message about retrieving data from a database. It is also because you can use the powerful
  [Query Builder](db-query-builder.md) to build and run complex queries.
-->
> Примечание: [[yii\db\Migration]] не предоставляет методы запросов к базе данных. Это потому, что обычно не требуется отображать дополнительные сообщения об извлечении данных из базы данных. Это также, потому, что можно использовать более мощный [Построитель Запросов](db-query-builder.md) для построения и выполнения сложных запросов.

## Применение Миграций <span id="applying-migrations"></span>
<!-- Applying Migrations -->
<!--
To upgrade a database to its latest structure, you should apply all available new migrations using the following command:
-->
Для обновления базы данных до последней структуры, Вы должны применить все новые миграции, используя следующую команду:

```
yii migrate
```

<!--
This command will list all migrations that have not been applied so far. If you confirm that you want to apply
these migrations, it will run the `up()` or `safeUp()` method in every new migration class, one after another, 
in the order of their timestamp values. If any of the migrations fails, the command will quit without applying
the rest of the migrations.
-->
Эта команда выведет список всех миграций, которые не применялись до сих пор. Если Вы подтвердите, что Вы хотите применить эти миграций, то этим самым запустите метод `up()` или `safeUp()` в каждом новом классе миграции, один за другим, в порядке их временного значения timestamp.

<!--
For each migration that has been successfully applied, the command will insert a row into a database table named 
`migration` to record the successful application of the migration. This will allow the migration tool to identify
which migrations have been applied and which have not.
-->
Для каждой миграции которая была успешно проведена, эта команда будет вставлять строку в таблицу базы данных с именем
`migration` записав успешное проведение миграции. Это позволяет инструменту миграции выявлять какие миграции были применены, а какие - нет.

<!--
> Info: The migration tool will automatically create the `migration` table in the database specified by
  the [[yii\console\controllers\MigrateController::db|db]] option of the command. By default, the database
  is specified by the `db` [application component](structure-application-components.md).
-->
> Примечание: Инструмент миграции автоматически создаст таблицу `migration` в базе данных указанной в параметре [[yii\console\controllers\MigrateController::db|db]]. По умолчанию база данных определяется как [компонент приложения](structure-application-components.md) `db`.

<!--
Sometimes, you may only want to apply one or a few new migrations, instead of all available migrations.
You can do so by specifying the number of migrations that you want to apply when running the command.
For example, the following command will try to apply the next three available migrations:
-->
Иногда, необходимо применить одну или несколько новых миграций, вместо всех доступных миграций. Это возможно сделать, указав, при выполнении команды, количество миграций, которые необходимо применить. Например, следующая команда будет пытаться применить следующие три доступные миграции:

```
yii migrate 3
```

<!--
You can also explicitly specify a particular migration to which the database should be migrated
by using the `migrate/to` command in one of the following formats:
-->
Также можно явно указать конкретную миграцию, которая должна быть применена к базе данных, это можно сделать при помощи команды `migrate/to` в одном из следующих форматов:

<!--
```
yii migrate/to 150101_185401                      # using timestamp to specify the migration
yii migrate/to "2015-01-01 18:54:01"              # using a string that can be parsed by strtotime()
yii migrate/to m150101_185401_create_news_table   # using full name
yii migrate/to 1392853618                         # using UNIX timestamp
```
-->

```
yii migrate/to 150101_185401                      # используя временную метку определяющую миграцию
yii migrate/to "2015-01-01 18:54:01"              # используя строку, которая может быть получена путем использования функции strtotime()
yii migrate/to m150101_185401_create_news_table   # используя полное имя
yii migrate/to 1392853618                         # используя временную метку UNIX
```

<!--
If there are any unapplied migrations earlier than the specified one, they will all be applied before the specified
migration is applied.

If the specified migration has already been applied before, any later applied migrations will be reverted.
-->
Если раньше имелись какие-либо не применённые миграции, до указанной конкретной миграции, то все они будут применены до данной миграции.
А если указанная миграция уже применялась ранее, то любые более поздние версии данной прикладной миграции будут отменены.

## Отмена Миграций <span id="reverting-migrations"></span>
<!-- Reverting Migrations -->
<!--
To revert (undo) one or multiple migrations that have been applied before, you can run the following command:
-->
Чтобы отменить (откатить) одну или несколько миграций, которые применялись ранее, нужно запустить следующую команду:

<!--
```
yii migrate/down     # revert the most recently applied migration
yii migrate/down 3   # revert the most 3 recently applied migrations
```
-->

```
yii migrate/down     # отменяет самую последнюю применёную миграцию
yii migrate/down 3   # отменяет 3 последних применённых миграции
```

<!--
> Note: Not all migrations are reversible. Trying to revert such migrations will cause an error and stop the
  entire reverting process.
-->

> Примечание: Не все миграции являются обратимыми. При попытке отката таких миграций произойдёт ошибка и остановится весь процесс отката.

## Перезагрузка Миграций <span id="redoing-migrations"></span>
<!-- Redoing Migrations -->
<!--
Redoing migrations means first reverting the specified migrations and then applying again. This can be done
as follows:
-->
Под перезагрузкой миграций подразумевается, сначала последовательный откат определённых миграций, а потом применение их снова. Это может быть сделано следующим образом:

```
yii migrate/redo        # перезагрузить последнюю применённую миграцию
yii migrate/redo 3      # перезагрузить 3 последние применённые миграции
```

<!--
> Note: If a migration is not reversible, you will not be able to redo it.
-->
> Примечание: Если миграция не является обратимой, Вы не сможете её перезагрузить.


## Список Миграций <span id="listing-migrations"></span>
<!-- Listing Migrations -->
<!--
To list which migrations have been applied and which are not, you may use the following commands:
 -->
Чтобы посмотреть какие миграции были применены, а какие нет, используйте следующие команды:

<!--
```
yii migrate/history     # showing the last 10 applied migrations
yii migrate/history 5   # showing the last 5 applied migrations
yii migrate/history all # showing all applied migrations

yii migrate/new         # showing the first 10 new migrations
yii migrate/new 5       # showing the first 5 new migrations
yii migrate/new all     # showing all new migrations
```
-->

```
yii migrate/history     # показать последних 10 применённых миграций
yii migrate/history 5   # показать последних 5 применённых миграций
yii migrate/history all # показать все применённые миграции

yii migrate/new         # показать первых 10 новых миграций
yii migrate/new 5       # показать первых 5 новых миграций
yii migrate/new all     # показать все новые миграции
```

## Изменение Истории Миграций <span id="modifying-migration-history"></span>
<!-- Modifying Migration History -->
<!--
Instead of actually applying or reverting migrations, sometimes you may simply want to mark that your database
has been upgraded to a particular migration. This often happens when you manually change the database to a particular
state and you do not want the migration(s) for that change to be re-applied later. You can achieve this goal with
the following command:
-->
Вместо применения или отката миграций, есть возможность просто <b>отметить</b>, что база данных была обновлена до определенной миграции. Это часто используется при ручном изменении базы данных в конкретное состояние и Вам не нужно применять миграции для того, чтобы это изменение было повторно применено позже. Этой цели можно добиться с помощью следующей команды:

```
yii migrate/mark 150101_185401                      # используя временную метку определённой миграции
yii migrate/mark "2015-01-01 18:54:01"              # используя строку, которая может быть получена путем использования функции strtotime()
yii migrate/mark m150101_185401_create_news_table   # используя полное имя
yii migrate/mark 1392853618                         # используя временную метку UNIX
```

<!--
The command will modify the `migration` table by adding or deleting certain rows to indicate that the database
has been applied migrations to the specified one. No migrations will be applied or reverted by this command.
-->
Эта команда изменит таблицу `migration` добавив или удалив определенные строки, тем самым указав, что к базе данных была применена указанная миграция. Никаких миграций не будет применяться или отменяться по этой команде.

## Настройка Миграций <span id="customizing-migrations"></span>
<!--Customizing Migrations-->
<!--There are several ways to customize the migration command.-->

Есть несколько способов настроить команду миграции.

### Используя Параметры Командной Строки<span id="using-command-line-options"></span>
<!-- Using Command Line Options -->
<!--The migration command comes with a few command-line options that can be used to customize its behaviors:-->

В команду миграций входит несколько параметров командной строки, которые могут использоваться, для того, чтобы настроить поведение миграции:

<!--
* `interactive`: boolean (defaults to true), specifies whether to perform migrations in an interactive mode. 
  When this is true, the user will be prompted before the command performs certain actions.
  You may want to set this to false if the command is being used in a background process.
-->
* `interactive`: логический тип - boolean (по умолчанию true). Указывает, следует ли выполнять миграцию в интерактивном режиме. Если это значение является - true, то пользователю будет выдан запрос, перед выполнением командой определенных действий. Вы можете установить это значение в false если команда используется в фоновом режиме.

<!--
* `migrationPath`: string (defaults to `@app/migrations`), specifies the directory storing all migration 
  class files. This can be specified as either a directory path or a path [alias](concept-aliases.md). 
  Note that the directory must exist, or the command may trigger an error.
-->
* `migrationPath`: строка - string (по умолчанию `@app/migrations`). Указывает каталог для хранения всех файлов классов миграций. Этот параметр может быть определён либо как путь до директории, либо как [псевдоним](concept-aliases.md) пути. Обратите внимание, что данный каталог должен существовать, иначе команда будет выдавать ошибку.

<!--
* `migrationTable`: string (defaults to `migration`), specifies the name of the database table for storing
  migration history information. The table will be automatically created by the command if it does not exist.
  You may also manually create it using the structure `version varchar(255) primary key, apply_time integer`.
-->
* `migrationTable`: строка - string (по умолчанию `migration`). Определяет имя таблицы в базе данных в которой хранится информация о истории миграций. Эта таблица будет автоматически создана командой миграции, если её не существует. Вы также можете создать её вручную, используя структуру `version varchar(255) primary key, apply_time integer`.

<!--
* `db`: string (defaults to `db`), specifies the ID of the database [application component](structure-application-components.md).
  It represents the database that will be migrated using this command.
-->
* `db`: строка - string (по умолчанию `db`). Определяет ID базы данных [компонента приложения](structure-application-components.md).
Этот параметр представляет собой базу данных, которая подвергается миграциям при помощи команды миграций.

<!--
* `templateFile`: string (defaults to `@yii/views/migration.php`), specifies the path of the template file
  that is used for generating skeleton migration class files. This can be specified as either a file path
  or a path [alias](concept-aliases.md). The template file is a PHP script in which you can use a predefined variable
  named `$className` to get the migration class name.
-->
* `templateFile`: строка - string (по умолчанию `@yii/views/migration.php`). Указывает путь до файла шаблона, который используется для формирования скелета класса файлов миграции. Этот параметр может быть определён либо как путь до файла, либо как [псевдоним](concept-aliases.md) пути. Файл шаблона - это PHP скрипт, в котором можно использовать предопределенную переменную с именем `$className` для того, чтобы получить имя класса миграции.

<!--
The following example shows how you can use these options.
-->
В следующем примере показано, как можно использовать эти параметры.

<!--
For example, if we want to migrate a `forum` module whose migration files
are located within the module's `migrations` directory, we can use the following
command:
-->
Например, если мы хотим перенести модуль `forum`, чьи файлы миграций
расположены в каталоге `migrations` данного модуля, для этого мы можем использовать следующую команду:

```
# неинтерактивная миграция модуля форума
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```

### Глобальная Настройка Комманд <span id="configuring-command-globally"></span>
<!-- Configuring Command Globally -->
<!--
Instead of entering the same option values every time you run the migration command, you may configure it
once for all in the application configuration like shown below:
-->
Вместо того, чтобы каждый раз вводить одинаковые значения параметров миграции, когда вы запускаете команду миграции, можно настроить её раз и навсегда в конфигурации приложения, как показано ниже:

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

<!--
With the above configuration, each time you run the migration command, the `backend_migration` table
will be used to record the migration history. You no longer need to specify it via the `migrationTable`
command-line option.
-->
С приведённой выше конфигурацией, каждый раз при запуске команды миграции, таблица `backend_migration` будет использованна для записи истории миграций. И Вам больше не нужно указывать её через параметр `migrationTable` в командной строке.

## Миграции в Несколько Баз Данных <span id="migrating-multiple-databases"></span>
<!--Migrating Multiple Databases-->
<!--
By default, migrations are applied to the same database specified by the `db` [application component](structure-application-components.md).
If you want them to be applied to a different database, you may specify the `db` command-line option like shown below,
-->
По умолчанию, миграции применяются для базы данных, указанной в `db` [компоненте приложения](structure-application-components.md).
Если Вы хотите применить миграцию к другой базе данных, Вы можете определить параметр `db` в командной строке как показано ниже,

```
yii migrate --db=db2
```

<!--
The above command will apply migrations to the `db2` database.
-->
Приведенная выше команда применит миграции к базе данных `db2`.

<!--
Sometimes it may happen that you want to apply *some* of the migrations to one database, while some others to another
database. To achieve this goal, when implementing a migration class you should explicitly specify the DB component
ID that the migration would use, like the following:
-->
Иногда может случиться так, что Вы захотите применить *некоторые* из миграций к одной базе данных, а некоторые другие к другой базе данных. Для достижения этой цели, при реализации класса миграции, необходимо явно указать идентификатор ID компонента базы данных, который миграция будет использовать, следующим образом:

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

<!--
The above migration will be applied to `db2`, even if you specify a different database through the `db` command-line
option. Note that the migration history will still be recorded in the database specified by the `db` command-line option.
-->
Вышеуказанная миграция будет применена к `db2` даже если указать другую базу данных через параметр `db` командной строки. Обратите внимание, что история миграций в этом случае будет записана в базу данных, указанную в параметре `db` командной строки.

<!--
If you have multiple migrations that use the same database, it is recommended that you create a base migration class
with the above `init()` code. Then each migration class can extend from this base class.
-->
Если у вас есть несколько миграций, которые используют ту же другую базу данных, то рекомендуется создать базовый класс миграций выше кода `init()`. Затем каждый класс миграции может расширяться от этого базового класса.

<!--
> Tip: Besides setting the [[yii\db\Migration::db|db]] property, you can also operate on different databases
  by creating new database connections to them in your migration classes. You then use the [DAO methods](db-dao.md)
  with these connections to manipulate different databases.
-->
> Совет: Кроме установки свойства [[yii\db\Migration::db|db]], Вы также можете работать с разными базами данных путем создания нового соединения с конкретной базой данных в классе Вашей миграции. Можно использовать [DAO методы](db-dao.md) с этими соединениями для манипулирования различными базами данных.

<!--
Another strategy that you can take to migrate multiple databases is to keep migrations for different databases in
different migration paths. Then you can migrate these databases in separate commands like the following:
-->
Другая стратегия, которую Вы можете выбрать, чтобы перенести (мигрировать) несколько баз данных - это сохранить миграции различных баз данных в разные директории. Затем вы можете перенести эти базы данных в нужные базы следующими командами:

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

<!--
The first command will apply migrations in `@app/migrations/db1` to the `db1` database, the second command
will apply migrations in `@app/migrations/db2` to `db2`, and so on.
-->
Первая команда применит миграции в директории `@app/migrations/db1` к базе данных `db1`, а вторая команда применит миграции в директории `@app/migrations/db2` к базе данных `db2` и так далее.
