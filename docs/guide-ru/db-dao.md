Объекты доступа к данным (DAO)
==============================

Построенные поверх [PDO](https://www.php.net/manual/ru/book.pdo.php), Yii DAO (объекты доступа к данным) обеспечивают
объектно-ориентированный API для доступа к реляционным базам данных. Это основа для других, более продвинутых, методов
доступа к базам данных, включая [построитель запросов](db-query-builder.md) и [active record](db-active-record.md).

При использовании Yii DAO вы в основном будете использовать чистый SQL и массивы PHP. Как результат, это самый
эффективный способ доступа к базам данных. Тем не менее, так как синтаксис SQL может отличаться для разных баз данных,
используя Yii DAO вам нужно будет приложить дополнительные усилия, чтобы сделать приложение не зависящим от конкретной
базы данных.

Yii DAO из коробки поддерживает следующие базы данных:

- [MySQL](https://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](https://sqlite.org/)
- [PostgreSQL](https://www.postgresql.org/): версии 8.4 или выше.
- [CUBRID](https://www.cubrid.org/): версии 9.3 или выше.
- [Oracle](https://www.oracle.com/database/)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): версии 2008 или выше.


> Note: Новая версия pdo_oci для PHP 7 на данный момент существует только в форме исходного кода. Используйте
  [инструкции сообщества по компиляции](https://github.com/yiisoft/yii2/issues/10975#issuecomment-248479268).

## Создание подключения к базе данных <span id="creating-db-connections"></span>

Для доступа к базе данных, вы сначала должны подключится к ней, создав экземпляр класса [[yii\db\Connection]]:

```php
$db = new yii\db\Connection([
    'dsn' => 'mysql:host=localhost;dbname=example',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```

Так как подключение к БД часто нужно в нескольких местах, распространённой практикой является его настройка как
[компонента приложения](structure-application-components.md):

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=example',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

Теперь вы можете получить доступ к подключению к БД с помощью выражения `Yii::$app->db`.

> Tip: Вы можете настроить несколько компонентов подключения, если в вашем приложении используется несколько баз данных.

При настройке подключения, вы должны обязательно указывать Имя Источника Данных (DSN) через параметр [[yii\db\Connection::dsn|dsn]].
Формат DSN отличается для разных баз данных. Дополнительное описание смотрите в [справочнике PHP](https://www.php.net/manual/ru/pdo.construct.php).
Ниже представлены несколько примеров:
 
* MySQL, MariaDB: `mysql:host=localhost;dbname=mydatabase`
* SQLite: `sqlite:/path/to/database/file`
* PostgreSQL: `pgsql:host=localhost;port=5432;dbname=mydatabase`
* CUBRID: `cubrid:dbname=demodb;host=localhost;port=33000`
* MS SQL Server (via sqlsrv driver): `sqlsrv:Server=localhost;Database=mydatabase`
* MS SQL Server (via dblib driver): `dblib:host=localhost;dbname=mydatabase`
* MS SQL Server (via mssql driver): `mssql:host=localhost;dbname=mydatabase`
* Oracle: `oci:dbname=//localhost:1521/mydatabase`

Заметьте, что если вы подключаетесь к базе данных через ODBC, вам необходимо указать свойство [[yii\db\Connection::driverName]],
чтобы Yii знал какой тип базы данных используется. Например,

```php
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

Кроме свойства [[yii\db\Connection::dsn|dsn]], вам необходимо указать [[yii\db\Connection::username|username]]
и [[yii\db\Connection::password|password]]. Смотрите [[yii\db\Connection]] для того, чтоб посмотреть полный список свойств. 

> Info: При создании экземпляра соединения к БД, фактическое соединение с базой данных будет установлено только
  при выполнении первого SQL запроса или при явном вызове метода [[yii\db\Connection::open()|open()]].

> Tip: Иногда может потребоваться выполнить некоторые запросы сразу после соединения с базой данных, для инициализации
> переменных окружения. Например, чтобы задать часовой пояс или кодировку. Сделать это можно зарегистрировав обработчик
> для события [[yii\db\Connection::EVENT_AFTER_OPEN|afterOpen]] в конфигурации приложения:
> 
> ```php
> 'db' => [
>     // ...
>     'on afterOpen' => function($event) {
>         // $event->sender содержит соединение с базой данных
>         $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
>     }
> ]
> ```

## Выполнение SQL запросов <span id="executing-sql-queries"></span>

После создания экземпляра соединения, вы можете выполнить SQL запрос, выполнив следующие шаги:
 
1. Создать [[yii\db\Command]] из запроса SQL;
2. Привязать параметры (не обязательно);
3. Вызвать один из методов выполнения SQL из [[yii\db\Command]].

Следующий пример показывает различные способы получения данных из базы дынных:
 
```php
// возвращает набор строк. каждая строка - это ассоциативный массив с именами столбцов и значений.
// если выборка ничего не вернёт, то будет получен пустой массив.
$posts = Yii::$app->db->createCommand('SELECT * FROM post')
            ->queryAll();

// вернёт одну строку (первую строку)
// false, если ничего не будет выбрано
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=1')
           ->queryOne();

// вернёт один столбец (первый столбец)
// пустой массив, при отсутствии результата
$titles = Yii::$app->db->createCommand('SELECT title FROM post')
             ->queryColumn();

// вернёт скалярное значение
// или false, при отсутствии результата
$count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM post')
             ->queryScalar();
```

> Note: Чтобы сохранить точность, данные извлекаются как строки, даже если тип поля в базе данных является числовым.

### Привязка параметров <span id="binding-parameters"></span>

При создании команды из SQL запроса с параметрами, вы почти всегда должны использовать привязку параметров для
предотвращения атак через SQL инъекции. Например,

```php
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValue(':id', $_GET['id'])
           ->bindValue(':status', 1)
           ->queryOne();
```

В SQL запрос, вы можете встраивать один или несколько маркеров (например `:id` в примере выше). Маркеры должны быть 
строкой, начинающейся с двоеточия. Далее вам нужно вызвать один из следующих методов для привязки значений к параметрам:

* [[yii\db\Command::bindValue()|bindValue()]]: привязка одного параметра по значению 
* [[yii\db\Command::bindValues()|bindValues()]]: привязка нескольких параметров в одном вызове
* [[yii\db\Command::bindParam()|bindParam()]]: похоже на [[yii\db\Command::bindValue()|bindValue()]], но привязка
  происходит по ссылке.

Следующий пример показывает альтернативный путь привязки параметров:

```php
$params = [':id' => $_GET['id'], ':status' => 1];

$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValues($params)
           ->queryOne();
           
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status', $params)
           ->queryOne();
```

Привязка переменных реализована через [подготавливаемые запросы](https://www.php.net/manual/ru/mysqli.quickstart.prepared-statements.php).
Помимо предотвращения атак путём SQL инъекций, это увеличивает производительность, так как запрос подготавливается
один раз, а потом выполняется много раз с разными параметрами. Например,

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id');

$post1 = $command->bindValue(':id', 1)->queryOne();
$post2 = $command->bindValue(':id', 2)->queryOne();
// ...
```

Так как [[yii\db\Command::bindParam()|bindParam()]] поддерживает привязку параметров по ссылке, следующий код может
быть написан следующим образом:

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id')
              ->bindParam(':id', $id);

$id = 1;
$post1 = $command->queryOne();

$id = 2;
$post2 = $command->queryOne();
// ...
```

Обратите внимание что вы связываете маркер `$id` с переменной перед выполнением запроса, и затем меняете это значение
перед каждым последующим выполнением (часто это делается в цикле). Выполнение запросов таким образом может быть значительно
более эффективным, чем выполнение запроса для каждого значения параметра.

### Выполнение Не-SELECT запросов <span id="non-select-queries"></span>

В методах `queryXyz()`, описанных в предыдущих разделах, вызываются SELECT запросы для извлечения данных из базы.
Для запросов не возвращающих данные, вы должны использовать метод [[yii\db\Command::execute()]]. Например,

```php
Yii::$app->db->createCommand('UPDATE post SET status=1 WHERE id=1')
   ->execute();
```

Метод [[yii\db\Command::execute()]] возвращает количество строк обработанных SQL запросом.

Для запросов INSERT, UPDATE и DELETE, вместо написания чистого SQL, вы можете вызвать методы [[yii\db\Command::insert()|insert()]],
[[yii\db\Command::update()|update()]], [[yii\db\Command::delete()|delete()]], соответственно, для создания указанных
SQL конструкций. Например,

```php
// INSERT (table name, column values)
Yii::$app->db->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// UPDATE (table name, column values, condition)
Yii::$app->db->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE (table name, condition)
Yii::$app->db->createCommand()->delete('user', 'status = 0')->execute();
```

Вы можете также вызвать [[yii\db\Command::batchInsert()|batchInsert()]] для вставки множества строк за один вызов.
Это более эффективно чем вставлять записи по одной за раз:

```php
// table name, column names, column values
Yii::$app->db->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();
```

Обратите внимание, что перечисленные методы лишь создают запрос. Чтобы его выполнить нужно вызывать
[[yii\db\Command::execute()|execute()]].


## Экранирование имён таблиц и столбцов <span id="quoting-table-and-column-names"></span>

При написании независимого от базы данных кода, правильно экранировать имена таблиц и столбцов довольно трудно, так как
в разных базах данных правила экранирования разные. Чтоб преодолеть данную проблему вы можете использовать следующий
синтаксис экранирования используемый в Yii:

* `[[column name]]`: заключайте имя столбца в двойные квадратные скобки; 
* `{{table name}}`: заключайте имя таблицы в двойные фигурные скобки.

Yii DAO будет автоматически преобразовывать подобные конструкции в SQL в правильно экранированные имена таблиц и столбцов.
Например,

```php
// executes this SQL for MySQL: SELECT COUNT(`id`) FROM `employee`
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{employee}}")
            ->queryScalar();
```


### Использование префиксов таблиц <span id="using-table-prefix"></span>

Если большинство ваших таблиц использует общий префикс в имени, вы можете использовать свойство Yii DAO для указания префикса.

Сначала, укажите префикс таблиц через свойство [[yii\db\Connection::tablePrefix]]:

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

Затем в коде, когда вам нужно ссылаться на таблицу, имя которой содержит такой префикс, используйте синтаксис `{{%table name}}`.
Символ процента будет автоматически заменён на префикс таблицы, который вы указали во время конфигурации соединения с
базой данных. Например,

```php
// для MySQL будет выполнен следующий SQL: SELECT COUNT(`id`) FROM `tbl_employee`
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{%employee}}")
            ->queryScalar();
```


## Исполнение транзакций <span id="performing-transactions"></span>

Когда вы выполняете несколько зависимых запросов последовательно, вам может потребоваться обернуть их в транзакцию
для обеспечения целостности вашей базы данных. Если в любом из запросов произойдёт ошибка, база данных откатится на
состояние, которое было до выполнения запросов.

Следующий код показывает типичное использование транзакций:

```php
Yii::$app->db->transaction(function($db) {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
});
```

Код выше эквивалентен приведённому ниже. Разница в том, что в данном случае мы получаем больше контроля над обработкой
ошибок:

```php
$db = Yii::$app->db;
$transaction = $db->beginTransaction();

try {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
    
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
}
```

> Note: в коде выше ради совместимости с PHP 5.x и PHP 7.x использованы два блока catch. 
> `\Exception` реализует интерфейс [`\Throwable` interface](https://www.php.net/manual/ru/class.throwable.php)
> начиная с PHP 7.0. Если вы используете только PHP 7 и новее, можете пропустить блок с `\Exception`.

При вызове метода [[yii\db\Connection::beginTransaction()|beginTransaction()]], будет запущена новая транзакция.
Транзакция представлена объектом [[yii\db\Transaction]] сохранённым в переменной `$transaction`. Потом, запросы будут
выполняться в блоке `try...catch...`. Если запросы будут выполнены удачно, будет выполнен метод [[yii\db\Transaction::commit()|commit()]].
Иначе, будет вызвано исключение, и будет вызван метод [[yii\db\Transaction::rollBack()|rollBack()]] для отката
изменений сделанных до неудачно выполненного запроса внутри транзакции.

### Указание уровня изоляции <span id="specifying-isolation-levels"></span>

Yii поддерживает настройку [уровня изоляции] для ваших транзакций. По умолчанию, при старте транзакции, будет использован
уровень изоляции настроенный в вашей базе данных. Вы можете переопределить уровень изоляции по умолчанию, как
указано ниже:

```php
$isolationLevel = \yii\db\Transaction::REPEATABLE_READ;

Yii::$app->db->transaction(function ($db) {
    ....
}, $isolationLevel);
 
// или

$transaction = Yii::$app->db->beginTransaction($isolationLevel);
```

Yii предоставляет четыре константы для наиболее распространённых уровней изоляции:

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - низший уровень, «Грязное» чтение, не повторяющееся чтение и фантомное чтение.
- [[\yii\db\Transaction::READ_COMMITTED]] - предотвращает «Грязное» чтение.
- [[\yii\db\Transaction::REPEATABLE_READ]] - предотвращает «Грязное» чтение и не повторяющееся чтение.
- [[\yii\db\Transaction::SERIALIZABLE]] - высший уровень, предотвращает все вышеуказанные проблемы.

Помимо использования приведённых выше констант для задания уровня изоляции, вы можете, также, использовать строки
поддерживаемые вашей СУБД. Например, в PostgreSQL, вы можете использовать `SERIALIZABLE READ ONLY DEFERRABLE`.

Заметьте что некоторые СУБД допускают настраивать уровень изоляции только для всего соединения. Следующие транзакции
будут получать тот же уровень изоляции, даже если вы его не укажете. При использовании этой функции может потребоваться
установить уровень изоляции для всех транзакций, чтоб избежать явно конфликтующих настроек.
На момент написания этой статьи страдали от этого ограничения только MSSQL и SQLite.

> Note: SQLite поддерживает только два уровня изоляции, таким образом вы можете использовать только
`READ UNCOMMITTED` и `SERIALIZABLE`. Использование других уровней изоляции приведёт к генерации исключения.

> Note: PostgreSQL не допускает установки уровня изоляции до старта транзакции, так что вы не сможете установить
уровень изоляции прямо при старте транзакции. Вы можете использовать [[yii\db\Transaction::setIsolationLevel()]] в
таком случае после старта транзакции.

[Уровни изоляции]: https://ru.wikipedia.org/wiki/%D0%A3%D1%80%D0%BE%D0%B2%D0%B5%D0%BD%D1%8C_%D0%B8%D0%B7%D0%BE%D0%BB%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%BD%D0%BE%D1%81%D1%82%D0%B8_%D1%82%D1%80%D0%B0%D0%BD%D0%B7%D0%B0%D0%BA%D1%86%D0%B8%D0%B9


### Вложенные транзакции <span id="nesting-transactions"></span>

Если ваша СУБД поддерживает Savepoint, вы можете вкладывать транзакции как показано ниже:

```php
Yii::$app->db->transaction(function ($db) {
    // внешняя транзакция
    
    $db->transaction(function ($db) {
        // внутренняя транзакция
    });
});
```

Или так,

```php
$db = Yii::$app->db;
$outerTransaction = $db->beginTransaction();
try {
    $db->createCommand($sql1)->execute();

    $innerTransaction = $db->beginTransaction();
    try {
        $db->createCommand($sql2)->execute();
        $innerTransaction->commit();
    } catch (\Exception $e) {
        $innerTransaction->rollBack();
    } catch (\Throwable $e) {
        $innerTransaction->rollBack();
        throw $e;
    }

    $outerTransaction->commit();
} catch (\Exception $e) {
    $outerTransaction->rollBack();
} catch (\Throwable $e) {
    $innerTransaction->rollBack();
    throw $e;
}
```


## Репликация и разделение запросов на чтение и запись <span id="read-write-splitting"></span>

Многие СУБД поддерживают [репликацию баз данных](https://ru.wikipedia.org/wiki/%D0%A0%D0%B5%D0%BF%D0%BB%D0%B8%D0%BA%D0%B0%D1%86%D0%B8%D1%8F_(%D0%B2%D1%8B%D1%87%D0%B8%D1%81%D0%BB%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D0%B0%D1%8F_%D1%82%D0%B5%D1%85%D0%BD%D0%B8%D0%BA%D0%B0))
для лучшей доступности базы данных и уменьшения времени ответа сервера. С репликацией базы данных, данные копируются
из *master servers* на *slave servers*. Все вставки и обновления должны происходить на основном сервере, хотя чтение
может производится и с подчинённых серверов.

Чтоб воспользоваться преимуществами репликации и достичь разделения чтения и записи, вам необходимо настроить компонент
[[yii\db\Connection]] как указано ниже:

```php
[
    'class' => 'yii\db\Connection',

    // настройки для мастера
    'dsn' => 'dsn for master server',
    'username' => 'master',
    'password' => '',

    // общие настройки для подчинённых
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // используем небольшой таймаут для соединения
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // список настроек для подчинённых серверов
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

Вышеуказанная конфигурация определяет систему с одним мастером и несколькими подчинёнными. Один из подчинённых
будет подключен и использован для чтения, в то время как мастер будет использоваться для запросов записи.
Такое разделение чтения и записи будет осуществлено автоматически с указанной конфигурацией. Например,

```php
// создание экземпляра соединения, использующего вышеуказанную конфигурацию
Yii::$app->db = Yii::createObject($config);

// запрос к одному из подчинённых
$rows = Yii::$app->db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// запрос к мастеру
Yii::$app->db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Info: Запросы выполненные через [[yii\db\Command::execute()]] определяются как запросы на запись, а все
  остальные запросы через один из "query" методов [[yii\db\Command]] воспринимаются как запросы на чтение.
  Вы можете получить текущий статус соединения к подчинённому серверу через `$db->slave`.

Компонент `Connection` поддерживает балансировку нагрузки и переключение при сбое для подчинённых серверов.
При выполнении первого запроса на чтение, компонент `Connection` будет случайным образом выбирать подчинённый сервер
и попытается подключиться к нему. Если сервер окажется "мёртвым", он попробует подключиться к другому. Если ни один
из подчинённых серверов не будет доступен, он подключится к мастеру. Если настроить
[[yii\db\Connection::serverStatusCache|кеш статуса серверов]], то недоступность серверов может быть запомнена, чтоб не
использоваться в течении [[yii\db\Connection::serverRetryInterval|заданного промежутка времени]].

> Info: В конфигурации выше, таймаут соединения к подчинённому серверу настроен на 10 секунд.
  Это означает, что если сервер не ответит за 10 секунд, он будет считаться "мёртвым". Вы можете отрегулировать
  этот параметр исходя из настроек вашей среды.

Вы также можете настроить несколько основных и несколько подчинённых серверов. Например,

```php
[
    'class' => 'yii\db\Connection',

    // общая конфигурация для основных серверов
    'masterConfig' => [
        'username' => 'master',
        'password' => '',
        'attributes' => [
            // используем небольшой таймаут для соединения
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // список настроек для основных серверов
    'masters' => [
        ['dsn' => 'dsn for master server 1'],
        ['dsn' => 'dsn for master server 2'],
    ],

    // общие настройки для подчинённых
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // используем небольшой таймаут для соединения
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // список настроек для подчинённых серверов
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

Конфигурация выше, определяет два основных и четыре подчинённых серверов. Компонент `Connection` поддерживает 
балансировку нагрузки и переключение при сбое между основными серверами, так же как и между подчинёнными. Различие
заключается в том, что когда ни к одному из основных серверов не удастся подключиться будет выброшено исключение.

> Note: Когда вы используете свойство [[yii\db\Connection::masters|masters]] для настройки одного или нескольких
  основных серверов, все остальные свойства для настройки соединения с базой данных (такие как `dsn`, `username`, `password`)
  будут проигнорированы компонентом `Connection`.

По умолчанию, транзакции используют соединение с основным сервером. И в рамках транзакции, все операции с БД будут
использовать соединение с основным сервером. Например,

```php
$db = Yii::$app->db;
// Транзакция запускается на основном сервере
$transaction = $db->beginTransaction();

try {
    // оба запроса выполняются на основном сервере
    $rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
    $db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch (\Throwable $e) {
    $innerTransaction->rollBack();
    throw $e;
}
```

Если вы хотите запустить транзакцию на подчинённом сервере, вы должны указать это явно, как показано ниже:

```php
$transaction = Yii::$app->db->slave->beginTransaction();
```

Иногда может потребоваться выполнить запрос на чтение через подключение к основному серверу. Это может быть достигнуто
с использованием метода `useMaster()`:

```php
$rows = Yii::$app->db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```

Вы также можете явно установить `$db->enableSlaves` в ложь, чтоб направлять все запросы к соединению с мастером.


## Работа со схемой базы данных <span id="database-schema"></span>

Yii DAO предоставляет целый набор методов для управления схемой базы данных, таких как создание новых таблиц, удаление
столбцов из таблицы, и т.д. Эти методы описаны ниже:

* [[yii\db\Command::createTable()|createTable()]]: создание таблицы
* [[yii\db\Command::renameTable()|renameTable()]]: переименование таблицы
* [[yii\db\Command::dropTable()|dropTable()]]: удаление таблицы
* [[yii\db\Command::truncateTable()|truncateTable()]]: удаление всех записей в таблице
* [[yii\db\Command::addColumn()|addColumn()]]: добавление столбца
* [[yii\db\Command::renameColumn()|renameColumn()]]: переименование столбца
* [[yii\db\Command::dropColumn()|dropColumn()]]: удаление столбца
* [[yii\db\Command::alterColumn()|alterColumn()]]: преобразование столбца
* [[yii\db\Command::addPrimaryKey()|addPrimaryKey()]]: добавление первичного ключа
* [[yii\db\Command::dropPrimaryKey()|dropPrimaryKey()]]: удаление первичного ключа
* [[yii\db\Command::addForeignKey()|addForeignKey()]]: добавление внешнего ключа
* [[yii\db\Command::dropForeignKey()|dropForeignKey()]]: удаление внешнего ключа
* [[yii\db\Command::createIndex()|createIndex()]]: создания индекса
* [[yii\db\Command::dropIndex()|dropIndex()]]: удаление индекса

Эти методы могут быть использованы, как указано ниже:

```php
// CREATE TABLE
Yii::$app->db->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

Вы также сможете получить описание схемы таблицы через вызов метода [[yii\db\Connection::getTableSchema()|getTableSchema()]].
Например,

```php
$table = Yii::$app->db->getTableSchema('post');
```

Метод вернёт объект [[yii\db\TableSchema]], который содержит информацию о столбцах таблицы, первичных ключах, внешних
ключах, и т.д. Вся эта информация используется главным образом для [построителя запросов](db-query-builder.md) и
[active record](db-active-record.md), чтоб помочь вам писать независимый от базы данных код.
