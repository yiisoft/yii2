Objetos de Acceso a Bases de Datos
==================================

Construido sobre [PDO](http://php.net/manual/es/book.pdo.php), Yii DAO (Objetos de Acceso a Bases de Datos) proporciona una
API orientada a objetos para el acceso a bases de datos relacionales. Es el fundamento para otros métodos de acceso a bases de datos
más avanzados, incluyendo el [constructor de consultas](db-query-builder.md) y [active record](db-active-record.md).

Al utilizar Yii DAO, principalmente vas a tratar con SQLs planos y arrays PHP. Como resultado, esta es la manera más eficiente
de acceder a las bases de datos. Sin embargo, como la sintaxis puede variar para las diferentes bases de datos, utilizando
Yii DAO también significa que tienes que tienes que tomar un esfuerzo adicional para crear una aplicación de database-agnostic.

Yii DAO soporta las siguientes bases de datos:

- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/)
- [CUBRID](http://www.cubrid.org/): versión 9.3 o superior.
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): versión 2008 o superior.

## Creando Conexiones DB <span id="creating-db-connections"></span>

Para acceder a una base de datos, primero necesitas conectarte a tu bases de datos mediante la creación
de una instancia de [yii\db\Connection]]:

```php
$db = new yii\db\Connection([
    'dsn' => 'mysql:host=localhost;dbname=example',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```

Debido a una conexión DB a menudo necesita ser accedido en diferentes lugares, una práctica común es
configurarlo en términos de un [componente de aplicación](structure-application-components.md) como
se muestra a continuación:

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

Puedes acceder a la conexión DB mediante la expresión `Yii::$app->db`.

> Consejo: Puedes configurar múltiples componentes de aplicación DB si tu aplicación necesita acceder a múltiples bases de datos.

Cuando configuras una conexión DB, deberías siempre especificar el Nombre de Origen de Datos (DSN) mediante la
propiedad [[yii\db\Connection::dsn|dsn]]. El formato del DSN varia para cada diferente base de datos. Por favor consulte el
[manual de PHP](http://www.php.net/manual/es/function.PDO-construct.php) para más detalles. Abajo están algunos ejemplos:

* MySQL, MariaDB: `mysql:host=localhost;dbname=mydatabase`
* SQLite: `sqlite:/path/to/database/file`
* PostgreSQL: `pgsql:host=localhost;port=5432;dbname=mydatabase`
* CUBRID: `cubrid:dbname=demodb;host=localhost;port=33000`
* MS SQL Server (mediante sqlsrv driver): `sqlsrv:Server=localhost;Database=mydatabase`
* MS SQL Server (mediante dblib driver): `dblib:host=localhost;dbname=mydatabase`
* MS SQL Server (mediante mssql driver): `mssql:host=localhost;dbname=mydatabase`
* Oracle: `oci:dbname=//localhost:1521/mydatabase`

Nota que si estás conectándote con una base de datos mediante ODBC, deberías configurar la propiedad [[yii\db\Connection::driverName]]
para que Yii pueda conocer el tipo de base de datos actual. Por ejemplo,

```php
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

Además de la propiedad [[yii\db\Connection::dsn|dsn]], a menudo es necesario configurar el [[yii\db\Connection::username|username]]
y [[yii\db\Connection::password|password]]. Por favor consulta [[yii\db\Connection]] para ver la lista completa de propiedades configurables.

> Información: Cuando se crea una instancia de conexión DB, la conexión actual a la base de datos no se establece hasta que
  ejecutes el primer SQL o llames explícitamente al método [[yii\db\Connection::open()|open()]].


## Ejecutando Consultas SQL <span id="executing-sql-queries"></span>

Una vez tienes instanciada una conexión a la base de datos, se pueden ejecutar consultas SQL tomando
los siguientes pasos:

1. Crea un [[yii\db\Command]] con SQL plano;
2. Vincula parámetros (opcional);
3. Llama a uno de los métodos de ejecución SQL con [[yii\db\Command]].

El siguiente ejemplo muestra varias maneras de obtener datos de una base de datos:

```php
$db = new yii\db\Connection(...);

// retorna un conjunto de filas. Cada fila es un array asociativo de columnas de nombres y valores.
// un array vacío es retornado si no hay resultados
$posts = $db->createCommand('SELECT * FROM post')
            ->queryAll();

// retorna una sola fila (la primera fila)
// false es retornado si no hay resultados
$post = $db->createCommand('SELECT * FROM post WHERE id=1')
           ->queryOne();

// retorna una sola columna (la primera columna)
// un array vacío es retornado si no hay resultados
$titles = $db->createCommand('SELECT title FROM post')
             ->queryColumn();

// retorna un escalar
// false es retornado si no hay resultados
$count = $db->createCommand('SELECT COUNT(*) FROM post')
             ->queryScalar();
```

> Nota: Para preservar la precisión, los datos obtenidos de las bases de datos son todos representados como cadenas, incluso si el tipo de columna correspondiente
a la base de datos es numérico.

> Consejo: Si necesitas ejecutar una consulta SQL inmediatamente después de establecer una conexión (ej., para establecer una zona horaria o un conjunto de caracteres),
> puedes hacerlo con el evento [[yii\db\Connection::EVENT_AFTER_OPEN]]. Por ejemplo,
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
                // $event->sender se refiere a la conexión DB
                $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
            }
        ],
    ],
    // ...
];
```


### Parámetros Vinculados (Binding Parameters) <span id="binding-parameters"></span>

Cuando creamos un comando DB para un SQL con parámetros, nosotros deberíamos casi siempre aprovechar el uso de los parámetros vinculados
para prevenir los ataques de inyección de SQL. Por ejemplo,

```php
$post = $db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValue(':id', $_GET['id'])
           ->bindValue(':status', 1)
           ->queryOne();
```

En la sentencia SQL, puedes incrustar uno o múltiples parámetros placeholders (ej. `:id` en el ejemplo anterior). Un parámetro
placeholder debería ser una cadena que empiece con dos puntos. A continuación puedes llamar a uno de los siguientes métodos para
unir los valores de los parámetros vinculados:

* [[yii\db\Command::bindValue()|bindValue()]]: une un solo parámetro
* [[yii\db\Command::bindValues()|bindValues()]]: une múltiples parámetros en una sola llamada
* [[yii\db\Command::bindParam()|bindParam()]]: similar a [[yii\db\Command::bindValue()|bindValue()]] pero también
  soporta las referencias de parámetros vinculados.

El siguiente ejemplo muestra formas alternativas de vincular parámetros:

```php
$params = [':id' => $_GET['id'], ':status' => 1];

$post = $db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValues($params)
           ->queryOne();

$post = $db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status', $params)
           ->queryOne();
```

La vinculación parámetros es implementada mediante [sentencias preparadas (prepared statements)](http://php.net/manual/es/mysqli.quickstart.prepared-statements.php).
Además de prevenir ataques de inyección de SQL, también puede mejorar el rendimiento preparando una sola vez una sentencia SQL y ejecutándola múltiples veces con diferentes
parámetros. Por ejemplo,

```php
$command = $db->createCommand('SELECT * FROM post WHERE id=:id');

$post1 = $command->bindValue(':id', 1)->queryOne();
$post2 = $command->bindValue(':id', 2)->queryOne();
```

Porque [[yii\db\Command::bindParam()|bindParam()]] soporta parámetros vinculados por referencias, el código de arriba también
puede ser escrito como lo siguiente:

```php
$command = $db->createCommand('SELECT * FROM post WHERE id=:id')
              ->bindParam(':id', $id);

$id = 1;
$post1 = $command->queryOne();

$id = 2;
$post2 = $command->queryOne();
```

Observe que vincula el placeholder a la variable `$id` antes de la ejecución, y entonces cambia el valor de esa variable
antes de cada subsiguiente ejecución (esto se hace a menudo con bucles). Ejecutando consultas de esta manera puede ser
bastante más eficiente que ejecutar una nueva consulta para cada valor diferente del parámetro.


### Ejecutando Consultas Non-SELECT <span id="non-select-queries"></span>

El método `queryXyz()` introducidos en las secciones previas todos tratan con consultas SELECT los cuales recogen los
datos de la base de datos. Para las consultas que no devuelven datos, deberías llamar a el método [[yii\db\Command::execute()]]
en su lugar. Por ejemplo,

```php
$db->createCommand('UPDATE post SET status=1 WHERE id=1')
   ->execute();
```

El método [[yii\db\Command::execute()]] retorna el número de filas afectadas por la ejecución SQL.

Para consultas INSERT, UPDATE y DELETE, en vez de escribir SQLs planos, puedes llamar a [[yii\db\Command::insert()|insert()]],
[[yii\db\Command::update()|update()]], [[yii\db\Command::delete()|delete()]], respectivamente, construyen los correspondientes
SQLs. Estos métodos entrecomillan adecuadamente las tablas y los nombres de columnas y los valores de los parámetros vinculados.
Por ejemplo,

```php
// INSERT (table name, column values)
$db->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// UPDATE (table name, column values, condition)
$db->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE (table name, condition)
$db->createCommand()->delete('user', 'status = 0')->execute();
```

Puedes también llamar a [[yii\db\Command::batchInsert()|batchInsert()]] para insertar múltiples filas de una sola vez,
que es mucho más eficiente que insertar una fila de cada vez:

```php
// table name, column names, column values
$db->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();
```


## Entrecomillado de Tablas y Nombres de Columna <span id="quoting-table-and-column-names"></span>

Al escribir código de database-agnostic, entrecomillar correctamente los nombres de las tablas y las columnas es a menudo
un dolor de cabeza porque las diferentes bases de datos tienen diferentes reglas para entrecomillar los nombres. Para
solventar este problema, puedes usar la siguiente sintaxis de entrecomillado introducido por Yii:

* `[[column name]]`: encierra con dobles corchetes el nombre de una columna que debe ser entrecomillado;
* `{{table name}}`: encierra con dobles llaves el nombre de una tabla que debe ser entrecomillado.

Yii DAO automáticamente convertirá tales construcciones en un SQL con los correspondientes entrecomillados de los nombres de las columnas o tablas.
Por ejemplo,

```php
// ejecuta esta SQL para MySQL: SELECT COUNT(`id`) FROM `employee`
$count = $db->createCommand("SELECT COUNT([[id]]) FROM {{employee}}")
            ->queryScalar();
```


### Usadno Prefijos de Tabla <span id="using-table-prefix"></span>

Si la mayoría de tus tablas de BD utilizan algún prefijo común en sus tablas, puedes usar la función de prefijo de tabla soportado
por Yii DAO.

Primero, especifica el prefijo de tabla mediante la propiedad [[yii\db\Connection::tablePrefix]]:

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

Luego en tu código, siempre que lo necesites para hacer referencia a una tabla cuyo nombre tiene un prefijo, utiliza la sintaxis
`{{%table name}}`. El carácter porcentaje se sustituye con el prefijo de la tabla que has especificado en la configuración de
la conexión DB. Por ejemplo,

```php
// ejecuta esta SQL para MySQL: SELECT COUNT(`id`) FROM `tbl_employee`
$count = $db->createCommand("SELECT COUNT([[id]]) FROM {{%employee}}")
            ->queryScalar();
```


## Realización de Transacciones <span id="performing-transactions"></span>

Cuando se ejecutan múltiples consultas relacionadas en una secuencia, puede que se tengan que envolver en una
transacción para asegurar la integridad de los datos y la consistencia de tu base de datos. Si cualquiera de las consultas
falla, la base de datos debe ser revertida al estado anterior como si ninguna de estas consultas se haya ejecutado.

El siguiente código muestra una manera típica de usar transacciones:

```php
$db->transaction(function($db) {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... ejecutando otras sentencias SQL
});
```

El código de arriba es equivalente a lo siguiente:

```php
$transaction = $db->beginTransaction();

try {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... ejecutando otras sentencias SQL

    $transaction->commit();

} catch(\Exception $e) {

    $transaction->rollBack();

    throw $e;
}
```

Al llamar al método [[yii\db\Connection::beginTransaction()|beginTransaction()]], se inicia una nueva transacción.
La transacción se representa como un objeto [[yii\db\Transaction]] almacenado en la variable `$transaction`. Luego,
las consultas que se ejecutan están encerrados en un bloque `try...catch...`. Si todas las consultas son ejecutadas satisfactoriamente,
el método [[yii\db\Transaction::commit()|commit()]] es llamado para confirmar la transacción. De lo contrario, una excepción
se disparará y se capturará, y el método [[yii\db\Transaction::rollBack()|rollBack()]] es llamado para revertir
los cambios hechos por las consultas antes de que fallara la consulta en la transacción.


### Especificando los Niveles de Aislamiento <span id="specifying-isolation-levels"></span>

Yii también soporta la configuración de [niveles de aislamiento] para tus transacciones. Por defecto, cuando comienza una  nueva transacción,
utilizará el nivel de aislamiento definido por tu sistema de base de datos. Se puede sobrescribir el nivel de aislamiento por defecto de la
siguiente manera,

```php
$isolationLevel = \yii\db\Transaction::REPEATABLE_READ;

$db->transaction(function ($db) {
    ....
}, $isolationLevel);

// or alternatively

$transaction = $db->beginTransaction($isolationLevel);
```

Yii proporciona cuatro constantes para los niveles de aislamiento más comunes:

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - el nivel más bajo, pueden ocurrir lecturas Dirty, lecturas
  Non-repeatable y Phantoms.
- [[\yii\db\Transaction::READ_COMMITTED]] - evita lecturas Dirty.
- [[\yii\db\Transaction::REPEATABLE_READ]] - evita lecturas Dirty y lecturas Non-repeatable.
- [[\yii\db\Transaction::SERIALIZABLE]] - el nivel más fuerte, evita todos los problemas nombrados anteriormente.

Además de usar las constantes de arriba para especificar los niveles de aislamiento, puedes también usar cadenas con
una sintaxis valida soportada por el DBMS que estés usando. Por ejemplo, en PostgreSQL, puedes utilizar `SERIALIZABLE READ ONLY DEFERRABLE`.

Tenga en cuenta que algunos DBMS permiten configuraciones de niveles de aislamiento solo a nivel de conexión. Las transacciones subsiguientes
recibirá el mismo nivel de aislamiento , incluso si no se especifica ninguna. Al utilizar esta característica
es posible que necesites ajustar el nivel de aislamiento para todas las transacciones de forma explícitamente para evitar conflictos
en las configuraciones.
En el momento de escribir esto, solo MSSQL y SQLite serán afectadas.

> Nota: SQLite solo soporta dos niveles de aislamiento, por lo que solo se puede usar `READ UNCOMMITTED` y
`SERIALIZABLE`. El uso de otros niveles causará el lanzamiento de una excepción.

> Nota: PostgreSQL no permite configurar el nivel de aislamiento antes que la transacción empiece por lo que no se
  puede especificar el nivel de aislamiento directamente cuando empieza la transacción. Se tiene que llamar a
  [[yii\db\Transaction::setIsolationLevel()]] después de que la transacción haya empezado.

[isolation levels]: http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels


### Transacciones Anidadas <span id="nesting-transactions"></span>

Si tu DBMS soporta Savepoint, puedes anidar múltiples transacciones como a continuación:

```php
$db->transaction(function ($db) {
    // outer transaction

    $db->transaction(function ($db) {
        // inner transaction
    });
});
```

O alternativamente,

```php
$outerTransaction = $db->beginTransaction();
try {
    $db->createCommand($sql1)->execute();

    $innerTransaction = $db->beginTransaction();
    try {
        $db->createCommand($sql2)->execute();
        $innerTransaction->commit();
    } catch (Exception $e) {
        $innerTransaction->rollBack();
    }

    $outerTransaction->commit();
} catch (Exception $e) {
    $outerTransaction->rollBack();
}
```


## Replicación y División Lectura-Escritura <span id="read-write-splitting"></span>

Muchos DBMS soportan [replicación de bases de datos](http://en.wikipedia.org/wiki/Replication_(computing)#Database_replication) para tener
una mejor disponibilidad de la base de datos y un mejor tiempo de respuesta del servidor. Con la replicación de bases
de datos, los datos están replicados en los llamados *servidores maestros* (master servers) y *servidores esclavos*
(slave servers). Todas las escrituras y actualizaciones deben hacerse en el servidor maestro, mientras que las lecturas
se efectuarán en los servidores esclavos.

Para aprovechar las ventajas de la replicación de la base de datos y lograr una división de lecuta-escritura, se puede configurar
el componente [[yii\db\Connection]] como se muestra a continuación:

```php
[
    'class' => 'yii\db\Connection',

    // configuración para el maestro
    'dsn' => 'dsn for master server',
    'username' => 'master',
    'password' => '',

    // configuración para los esclavos
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // utiliza un tiempo de espera de conexión más pequeña
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // listado de configuraciones de esclavos
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

La configuración anterior especifica una configuración con un único maestro y múltiples esclavos. Uno de los esclavos
se conectará y se usará para ejecutar consultas de lectura, mientras que el maestro se usará para realizar consultas de
escritura. De este modo la división de lectura-escritura se logra automáticamente con esta configuración, Por ejemplo,

```php
// crea una instancia de Connection usando la configuración anterior
$db = Yii::createObject($config);

// consulta contra uno de los esclavos
$rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// consulta contra el maestro
$db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Información: Las consultas realizadas llamando a [[yii\db\Command::execute()]] se consideran consultas de escritura,
  mientras que todas las demás se ejecutan mediante alguno de los métodos "query" de [[yii\db\Command]] son consultas
  de lectura. Se puede obtener la conexión de esclavo activa mediante `$db->slave`.

El componente `Connection` soporta el balanceo de carga y la conmutación de errores entre esclavos. Cuando se realiza
una consulta de lectura por primera vez, el componente `Connection` elegirá un esclavo aleatorio e intentará realizar
una conexión a este. Si el esclavo se encuentra "muerto", se intentará con otro. Si no está disponible ningún esclavo, se conectará al maestro. Configurando una [[yii\db\Connection::serverStatusCache|server status cache]], se recordarán los servidores
"muertos" por lo que no se intentará volver a conectar a ellos durante
[[yii\db\Connection::serverRetryInterval|certain period of time]].

> Información: En la configuración anterior, se especifica un tiempo de espera (timeout) de conexión de 10 segundos
  para cada esclavo. Esto significa que si no se puede conectar a un esclavo en 10 segundos, este será considerado
  como "muerto". Se puede ajustar el parámetro basado en el entorno actual.

También se pueden configurar múltiples maestros con múltiples esclavos. Por ejemplo,

```php
[
    'class' => 'yii\db\Connection',

    // configuracion habitual para los maestros
    'masterConfig' => [
        'username' => 'master',
        'password' => '',
        'attributes' => [
            // utilizar un tiempo de espera de conexión más pequeña
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // listado de configuraciones de maestros
    'masters' => [
        ['dsn' => 'dsn for master server 1'],
        ['dsn' => 'dsn for master server 2'],
    ],

    // configuración habitual para esclavos
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // utilizar un tiempo de espera de conexión más pequeña
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // listado de configuración de esclavos
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

La configuración anterior especifica dos maestros y cuatro esclavos. El componente `Connection` también da soporte al
balanceo de carga y la conmutación de errores entre maestros igual que hace con los esclavos. La diferencia es que
cuando no se encuentra ningún maestro disponible se lanza una excepción.

> Nota: cuando se usa la propiedad [[yii\db\Connection::masters|masters]] para configurar uno o múltiples maestros, se
  ignorarán todas las otras propiedades que especifiquen una conexión de base de datos
  (ej. `dsn`, `username`, `password`), junto con el mismo objeto `Connection`.

Por defecto. las transacciones usan la conexión del maestro. Y dentro de una transacción, todas las operaciones de DB usarán
la conexión del maestro. Por ejemplo,

```php
// la transacción empieza con la conexión al maestro
$transaction = $db->beginTransaction();

try {
    // las dos consultas se ejecutan contra el maestro
    $rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
    $db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

Si se quiere empezar la transacción con una conexión a un esclavo, se debe hacer explícitamente como se muestra a
continuación:

```php
$transaction = $db->slave->beginTransaction();
```

A veces, se puede querer forzar el uso de una conexión maestra para realizar una consulta de lectura. Se puede lograr
usando el método `useMaster()`:

```php
$rows = $db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```

También se puede utilizar directamente estableciendo `$db->enableSlaves` a `false` para que se redirijan todas las
consultas a la conexión del maestro.

## Trabajando con Esquemas de Bases de Datos <span id="database-schema"></span>

Yii DAO proporciona todo un conjunto de métodos que permites manipular el esquema de tu base de datos, tal como
crear nuevas tablas, borrar una columna de una tabla, etc. Estos métodos son listados a continuación:

* [[yii\db\Command::createTable()|createTable()]]: crea una tabla
* [[yii\db\Command::renameTable()|renameTable()]]: renombra una tabla
* [[yii\db\Command::dropTable()|dropTable()]]: remueve una tabla
* [[yii\db\Command::truncateTable()|truncateTable()]]: remueve todas las filas de una tabla
* [[yii\db\Command::addColumn()|addColumn()]]: añade una columna
* [[yii\db\Command::renameColumn()|renameColumn()]]: renombra una columna
* [[yii\db\Command::dropColumn()|dropColumn()]]: remueve una columna
* [[yii\db\Command::alterColumn()|alterColumn()]]: altera una columna
* [[yii\db\Command::addPrimaryKey()|addPrimaryKey()]]: añade una clave primaria
* [[yii\db\Command::dropPrimaryKey()|dropPrimaryKey()]]: remueve una clave primaria
* [[yii\db\Command::addForeignKey()|addForeignKey()]]: añade una clave ajena
* [[yii\db\Command::dropForeignKey()|dropForeignKey()]]: remueve una clave ajena
* [[yii\db\Command::createIndex()|createIndex()]]: crea un indice
* [[yii\db\Command::dropIndex()|dropIndex()]]: remueve un indice

Estos métodos puedes ser usados como se muestra a continuación:

```php
// CREATE TABLE
$db->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

También puedes recuperar la información de definición de una tabla a través
del método [[yii\db\Connection::getTableSchema()|getTableSchema()]] de una conexión DB. Por ejemplo,

```php
$table = $db->getTableSchema('post');
```

El método retorna un objeto [[yii\db\TableSchema]] que contiene la información sobre las columnas de las tablas,
claves primarias, claves ajenas, etc. Toda esta información principalmente es utilizada por el
[constructor de consultas](db-query-builder.md) y [active record](db-active-record.md) para ayudar a
escribir código database-agnostic.
