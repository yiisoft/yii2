Objetos de Acceso a Bases de Datos
==================================

> Nota: Esta sección está en desarrollo.

Yii incluye una capa de acceso a bases de datos basado en el [PDO](http://php.net/manual/es/book.pdo.php) de PHP. La
interfaz de objetos de acceso a bases de datos (DAO) proporciona una API uniforme y soluciona algunas inconsistencias
que existen entre diferentes aplicaciones de bases de datos. Mientras el Active Record proporciona interacciones con
los modelos, y el Constructor de Consultas (Query Builder) ayuda en la composición de consultas dinámicas, DAO es una
manera simple y eficiente para ejecutar SQL en la base de datos. Por lo general, se usará DAO cuando la ejecución de
la consulta sea muy costosa y/o no se requieran modelos de aplicación y sus correspondientes lógicas de negocio.

De forma predeterminada, Yii soporta los siguientes DBMS (Sistemas de Gestión de Base de Datos):

- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/)
- [CUBRID](http://www.cubrid.org/): versión 9.3 o superior. (Tenga en cuenta que debido al
  [bug](http://jira.cubrid.org/browse/APIS-658) en la extensión PDO de cubrid, los valores entrecomillados no
  funcionarán, por lo que se necesita CUBRID 9.3 tanto para el cliente como para el servidor)
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): versión 2008 o superior.

Configuración
-------------

Para empezar a interaccionar con la base de datos (usando DAO o de otra forma), se tiene que configurar el componente
de conexión a la base de datos de la aplicación. El DSN (Nombre de Origen de Datos) configura que aplicación de BBDD y
que BBDD especifica debe conectar la aplicación:

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase', // MySQL, MariaDB
            //'dsn' => 'sqlite:/path/to/database/file', // SQLite
            //'dsn' => 'pgsql:host=localhost;port=5432;dbname=mydatabase', // PostgreSQL
            //'dsn' => 'cubrid:dbname=demodb;host=localhost;port=33000', // CUBRID
            //'dsn' => 'sqlsrv:Server=localhost;Database=mydatabase', // MS SQL Server, sqlsrv driver
            //'dsn' => 'dblib:host=localhost;dbname=mydatabase', // MS SQL Server, dblib driver
            //'dsn' => 'mssql:host=localhost;dbname=mydatabase', // MS SQL Server, mssql driver
            //'dsn' => 'oci:dbname=//localhost:1521/mydatabase', // Oracle
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

Se puede encontrar más información del formato de la cadena DSN en el
[manual de PHP](http://php.net/manual/es/pdo.construct.php). Además se puede encontrar el listado completo de
propiedades que se pueden configurar en la clase en [[yii\db\Connection]].

Hay que tener en cuenta que si se conecta a una base de datos mediante ODBC, se debe configurar la propiedad
[[yii\db\Connection::driverName]] para que Yii sepa el tipo de bases de datos actual. Por ejemplo,

```php
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

Se puede acceder a la conexión `db` primaria mediante la expresión `\Yii::$app->db`. También se pueden configurar
múltiples conexiones de BBDD en una única aplicación. Simplemente asignándoles diferentes IDs en la configuración de
la aplicación:

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'secondDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:/path/to/database/file',
        ],
    ],
    // ...
];
```

Ahora se pueden usar las dos conexiones a la base de datos al mismo tiempo si es necesario:

```php
$primaryConnection = \Yii::$app->db;
$secondaryConnection = \Yii::$app->secondDb;
```

Si no se quiere definir la conexión como un [componente de aplicación](structure-application-components.md), se puede
instanciar directamente:

```php
$connection = new \yii\db\Connection([
    'dsn' => $dsn,
    'username' => $username,
    'password' => $password,
]);
$connection->open();
```

> Consejo: Si se necesita ejecutar una consulta SQL inmediatamente después de establecer la conexión
  (ej. para establecer la zona horaria (timezone) o el juego de caracteres), se puede añadir el siguiente código en el
  archivo de configuración de la aplicación:
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
                $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
            }
        ],
    ],
    // ...
];
```

Ejecución de Consultas SQL Básicas
----------------------------------

Una vez instanciada una conexión a la base de datos, se pueden ejecutar consultas SQL usando [[yii\db\Command]].

### Ejecutando Consultas SELECT

Cuando la consulta que tiene que ser ejecutada devuelve un conjunto de filas, se usará `queryAll`:

```php
$command = $connection->createCommand('SELECT * FROM post');
$posts = $command->queryAll();
```

Cuando la consulta que se ejecute devuelva una única fila, se usará `queryOne`:

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=1');
$post = $command->queryOne();
```

Cuando la consulta devuelva múltiples filas pero solo una columna, se usará `queryColumn`:

```php
$command = $connection->createCommand('SELECT title FROM post');
$titles = $command->queryColumn();
```

Cuando la consulta solo devuelva un valor escalar, se usará `queryScalar`:

```php
$command = $connection->createCommand('SELECT COUNT(*) FROM post');
$postCount = $command->queryScalar();
```

### Ejecución de Consultas que No Devuelvan Valores

Si el SQL ejecutado no devuelve ningún dato, por ejemplo, INSER, UPDATE, y DELETE, se puede usar el método `execute`:

```php
$command = $connection->createCommand('UPDATE post SET status=1 WHERE id=1');
$command->execute();
```

De forma alternativa, se pueden usar los métodos `insert`, `update`, y `delete`. Estos métodos se encargarán de
gestionar el entrecomillado de los nombres de las tablas y de las columnas que se usen en la consulta, y solo se
tendrá que proporcionar los valores necesarios.

[[Se tiene que poner el enlace de documentación aquí.]]

```php
// INSERT
$connection->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// insertar múltiples filas a la vez
$connection->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();

// UPDATE
$connection->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE
$connection->createCommand()->delete('user', 'status = 0')->execute();
```

Entrecomillado de los Nombres de las Tablas y las Columnas <a name="quoting-table-and-column-names"></a>
----------------------------------------------------------

Para hacer que los nombres de las columnas y las tablas sean seguros para usarse en las consultas, se puede utilizar Yii
adecuadamente para que los entrecomille:

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

En el código anterior, se convertirá `[[$column]]` a un nombre de columna debidamente entrecomillado, mientras que se
convertirá `{{table}}` a un nombre de tabla debidamente entrecomillado.

Hay una variante especial de esta sintaxis especifica para que los nombres de las tablas: `{{%Y}}` añade automáticamente
el prefijo de la tabla de la aplicación para proporcionar un valor, si se ha establecido un prefijo de tabla:

```php
$sql = "SELECT COUNT([[$column]]) FROM {{%table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

El código anterior dará como resultado una consulta de selección de la tabla `tbl_table`, si se tiene el prefijo de
tabla configurado como el siguiente:

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

La alternativa es entrecomillar los nombres de las tablas y las columnas manualmente usando
[[yii\db\Connection::quoteTableName()]] y [[yii\db\Connection::quoteColumnName()]]:

```php
$column = $connection->quoteColumnName($column);
$table = $connection->quoteTableName($table);
$sql = "SELECT COUNT($column) FROM $table";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

Uso de Sentencias Preparadas
----------------------------

Para pasar parámetros seguros a las consultas, se deben usar las sentencias preparadas. Primero, se tiene que crear un
*parámetro de substitución* (placeholder) en una consulta (usando la sintaxis `:placeholder`). Después intercambiar el
parámetro de substitución por una variable y ejecutar la consulta:

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
$command->bindValue(':id', $_GET['id']);
$post = $command->queryOne();
```

Otra finalidad de las sentencias preparadas (aparte de mejorar la seguridad) es la habilidad de ejecutar una consulta
múltiples veces mientras que sólo se ha preparado una vez:

```php
$command = $connection->createCommand('DELETE FROM post WHERE id=:id');
$command->bindParam(':id', $id);

$id = 1;
$command->execute();

$id = 2;
$command->execute();
```

Tenga en cuenta que se efectúa la asignación del parámetro de substitución antes de su ejecución, y después se cambia
el valor antes de la siguiente ejecución (normalmente se ejecuta en bucles). La ejecución de consultas con este
método, puede ser mucho más eficiente que la ejecución de una consulta cada vez.

Realización de Transacciones
----------------------------

Cuando se ejecutan múltiples, consultas relacionadas en una secuencia, puede que se tengan que envolver en una
transacción para proteger la integridad de los datos. Las transacciones permiten escribir una serie de consultas de
forma que o todas se ejecutan correctamente o no tendrán ningún efecto. Yii proporciona una interfaz sencilla para
trabajar con transacciones en casos simples pero también para el uso avanzado cuando tengan que definir los niveles de
aislamiento.

El siguiente código muestra un patrón simple que debe seguir todo código que utilice consultas transaccionales:

```php
$transaction = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();
    $connection->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

La primera linea empieza una nueva transacción usando el método
[[yii\db\Connection::beginTransaction()|beginTransaction()]] del objeto de conexión a la base de datos. La transacción
en si misma se representa con el objeto [[yii\db\Transaction]] almacenado en `$transaction`. Nosotros encapsulamos la
ejecución de todas las consultas en un bloque try-catch para poder gestionar los errores. Llamamos a
[[yii\db\Transaction::commit()|commit()]] cuando todo es correcto para efectuar la transacción y si sucede algún error
ejecutamos [[yii\db\Transaction::rollBack()|rollBack()]]. Esto revertirá el efecto de todas las consultas que se hayan
ejecutado dentro de la transacción. Se usa `throw $e` para relanzar la excepción en caso de que nosotros no podamos
gestionar el error y se delega a otro código del gestor de errores de Yii.

Es posible anidar múltiples transacciones si es necesario:

```php
// transacción exterior
$transaction1 = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();

    // transacción interior
    $transaction2 = $connection->beginTransaction();
    try {
        $connection->createCommand($sql2)->execute();
        $transaction2->commit();
    } catch (Exception $e) {
        $transaction2->rollBack();
    }

    $transaction1->commit();
} catch (Exception $e) {
    $transaction1->rollBack();
}
```

Tenga en cuanta que el DBMS debe soportar Puntos de Registro (Savepoints) para que funcionen correctamente. El código
anterior, trabajará con cualquier DBMS pero sólo se garantizarán las transacciones que se ejecuten bajo un DBMS
que las soporte.

Yii también soporta la configuración de [niveles de aislamiento] en las transacciones. Cuando empiece una transacción
se ejecutará con el nivel predeterminado de aislamiento definido por la base de datos. Se puede especificar un nivel
de aislamiento específico cuando se empieza una transacción:

```php
$transaction = $connection->beginTransaction(\yii\db\Transaction::REPEATABLE_READ);
```

Yii proporciona cuatro constantes para los niveles de aislamiento más comunes:

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - el nivel más bajo, pueden ocurrir lecturas Dirty, lecturas
  Non-repeatable y Phantoms.
- [[\yii\db\Transaction::READ_COMMITTED]] - evita lecturas Dirty.
- [[\yii\db\Transaction::REPEATABLE_READ]] - evita lecturas Dirty y lecturas Non-repeatable.
- [[\yii\db\Transaction::SERIALIZABLE]] - el nivel más fuerte, evita todos los problemas nombrados anteriormente.

Se pueden usar las constantes descritas anteriormente aunque también se pueden usar cadenas de texto que representen
la sintaxis que puede ser utilizada en el DBMS seguido de `SET TRANSACTION ISOLATION LEVEL`.  Para postgres podría
utilizarse, por ejemplo, `SERIALIZABLE READ ONLY DEFERRABLE`.

Tenga en cuenta que algunos DBMS permiten configuraciones de niveles de aislamiento solo a nivel de conexión y por
consiguiente las transacciones pueden obtener el mismo nivel de aislamiento incluso si no se especifica ninguno.
Cuando se usa esta característica, se puede tener que establecer el nivel de aislamiento explícitamente para evitar
conflictos de configuración. En este momento se ven afectados los DBMS MSSQL y SQLite.

> NOTA: SQLite solo soporta dos niveles de aislamiento, por lo que solo se puede usar `READ UNCOMMITTED` y
`SERIALIZABLE`. El uso de otros niveles causará el lanzamiento de una excepción.

> Nota: PostgreSQL no permite configurar el nivel de aislamiento antes que la transacción empiece por lo que no se
  puede especificar el nivel de aislamiento directamente cuando empieza la transacción. Se tiene que ejecutar
  [[yii\db\Transaction::setIsolationLevel()]] después de que la transacción haya empezado.

[isolation levels]: http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels

Réplicas y División Lectura-Escritura
-------------------------------------

Muchos DBMS soportan
[replicación de bases de datos](http://en.wikipedia.org/wiki/Replication_(computing)#Database_replication) para tener
una mejor disponibilidad de la base de datos y un mejor tiempo de respuesta del servidor. Con la replicación de bases
de datos, los datos están replicados en los llamados *servidores maestros* (master servers) y *servidores esclavos*
(slave servers). Todas las escrituras y actualizaciones deben hacerse en el servidor maestro mientras que las lecturas
se efectuarán en los servidores esclavos.

Para aprovechar las ventajas de la replicación de BBDD y lograr una división de lecuta-escritura, se puede configurar
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
se conectará y se usará para ejecutar consultas de lectura mientras que el maestro se usará para realizar consultas de
escritura. De este modo la división de lectura-escritura se logra automáticamente con esta configuración, Por ejemplo,

```php
// crea una instancia de Connection usando la configuración anterior
$db = Yii::createObject($config);

// consulta contra uno de los esclavos
$rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// consulta contra el maestro
$db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Información: Las consultas realizadas ejecutando [[yii\db\Command::execute()]] se consideran consultas de escritura,
  mientras que todas las demás se ejecutan mediante alguno de los métodos "query" de [[yii\db\Command]] son consultas
  de lectura. Se puede obtener la conexión de esclavo activa mediante `$db->slave`.

El componente `Connection` soporta el balanceo de carga y la conmutación de errores entre esclavos. Cuando se realiza
una consulta de lectura por primera vez, el componente `Connection` elegirá un esclavo aleatorio e intentará realizar
una conexión a este. Si está "muerto", se intentará con otro. Si no está disponible ningún esclavo, se conectará al
maestro. Configurando una [[yii\db\Connection::serverStatusCache|server status cache]], se recordarán los servidores
"muertos" por lo que no se intentará volver a conectar a ellos durante
[[yii\db\Connection::serverRetryInterval|certain period of time]].

> Información: En la configuración anterior, se especifica un tiempo de espera (timeout) de conexión de 10 segundos
  para cada esclavo. Esto significa que si no se puede conectar a un esclavo en 10 segundos, este será considerado
  como "muerto". Se puede ajustar el parámetro basado en el entorno actual.

También se pueden configurar múltiples parámetros para múltiples esclavos. Por ejemplo,

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

Las conexiones usan la conexión de maestro de forma predeterminada. Y todas las operaciones de BBDD que estén dentro
de una transacción, usaran la conexión de maestro. Por ejemplo,

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

Si se quiere empezar la conexión con una conexión a un esclavo, se debe hacer explícitamente como se muestra a
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
consultas a la conexión de maestro.

Trabajar con Esquemas de Bases de Datos
---------------------------------------

### Obtención de la información del esquema

Se puede obtener una instancia de [[yii\db\Schema]] como se muestra a continuación:

```php
$schema = $connection->getSchema();
```

Contiene una serie de métodos que permiten obtener información varia acerca de la base de datos:

```php
$tables = $schema->getTableNames();
```

Para hacer referencia al esquema entero, se puede revisar [[yii\db\Schema]].

### Modificación de esquemas

Aparte de consultas SQL básicas, [[yii\db\Command]] contiene un conjunto de métodos que permiten modificar el esquema
de la base de datos:

- createTable, renameTable, dropTable, truncateTable
- addColumn, renameColumn, dropColumn, alterColumn
- addPrimaryKey, dropPrimaryKey
- addForeignKey, dropForeignKey
- createIndex, dropIndex

Que pueden usarse como se muestra a continuación:

```php
// CREAR TABLA
$connection->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

Para la referencia completa, se puede revisar [[yii\db\Command]].
