Migración de Base de Datos
==========================

Durante el curso de desarrollo y mantenimiento de una aplicación con base de datos, la estructura de dicha base de datos
evoluciona tanto como el código fuente. Por ejemplo, durante el desarrollo de una aplicación,
una nueva tabla podría ser necesaria; una vez que la aplicación se encuentra en producción, podría descrubrirse
que debería crearse un índice para mejorar el tiempo de ejecución de una consulta; y así sucesivamente. Debido a los cambios en la estructura de la base de datos
a menudo se requieren cambios en el código, Yii soporta la característica llamada *migración de base de datos*, la cual permite
tener un seguimiento de esos cambios en término de *migración de base de datos*, cuyo versionado es controlado
junto al del código fuente.

Los siguientes pasos muestran cómo una migración puede ser utilizada por un equipo durante el desarrollo:

1. Tim crea una nueva migración (por ej. crea una nueva table, cambia la definición de una columna, etc.).
2. Tim hace un commit con la nueva migración al sistema de control de versiones (por ej. Git, Mercurial).
3. Doug actualiza su repositorio desde el sistema de control de versiones y recibe la nueva migración.
4. Doug aplica dicha migración a su base de datos local de desarrollo, de ese modo sincronizando su base de datos
   y reflejando los cambios que hizo Tim.

Los siguientes pasos muestran cómo hacer una puesta en producción con una migración de base de datos:

1. Scott crea un tag de lanzamiento en el repositorio del proyecto que contiene algunas migraciones de base de datos.
2. Scott actualiza el código fuente en el servidor de producción con el tag de lanzamiento.
3. Scott aplica cualquier migración de base de datos acumulada a la base de datos de producción.

Yii provee un grupo de herramientas de línea de comandos que te permite:

* crear nuevas migraciones;
* aplicar migraciones;
* revertir migraciones;
* re-aplicar migraciones;
* mostrar el historial y estado de migraciones.

Todas esas herramientas son accesibles a través del comando `yii migrate`. En esta sección describiremos en detalle
cómo lograr varias tareas utilizando dichas herramientas. Puedes a su vez ver el uso de cada herramienta a través del comando
de ayuda `yii help migrate`.

> Tip: las migraciones pueden no sólo afectar un esquema de base de datos sino también ajustar datos existentes para que encajen en el nuevo esquema, crear herencia RBAC
  o también limpiar el cache.


## Creando Migraciones <span id="creating-migrations"></span>

Para crear una nueva migración, ejecuta el siguiente comando:

```
yii migrate/create <name>
```

El argumento requerido `name` da una pequeña descripción de la nueva migración. Por ejemplo, si
la migración se trata acerca de crear una nueva tabla llamada *news*, podrías utilizar el nombre `create_news_table`
y ejecutar el siguiente comando:

```
yii migrate/create create_news_table
```

> Note: Debido a que el argumento `name` será utilizado como parte del nombre de clase de la migración generada,
  sólo debería contener letras, dígitos, y/o guines bajos.

El comando anterior un nuevo archivo de clase PHP llamado `m150101_185401_create_news_table.php`
en el directorio `@app/migrations`. El archivo contendrá el siguiente código, que principalmente declara
una clase de tipo migración `m150101_185401_create_news_table` con el siguiente esqueleto de código:

```php
<?php

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

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
```

Cada migración de base de datos es definida como una clase PHP que extiende de [[yii\db\Migration]]. La nombre de clase
de la migración es generado automáticamente en el formato `m<YYMMDD_HHMMSS>_<Name>`, donde

* `<YYMMDD_HHMMSS>` se refiere a la marca de tiempo UTC en la cual el comando de migración fue ejecutado.
* `<Name>` es el mismo valor del argumento `name` provisto al ejecutar el comando.

En la clase de la migración, se espera que tu escribas código en el método `up()`, que realiza los cambios en la base de datos.
Podrías también querer introducir código en el método `down()`, que debería revertir los cambios realizados por `up()`. El método `up()` es llamado
cuando actualizas la base de datos con esta migración, mientras que el método `down()` es llamado cuando reviertes dicha migración.
El siguiente código muestra cómo podrías implementar la clase de migración para crear la tabla `news`:

```php
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
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

> Info: No todas las migraciones son reversibles. Por ejemplo, si el método `up()` elimina un registro en una tabla, podrías 
  no ser capáz de recuperarla en el método `down()`. A veces, podrías ser simplemente demasiado perezoso para implementar
  el método `down()`, debido a que no es muy común revertir migraciones de base de datos. En este caso, deberías devolver
  `false` en el método `down()` para indicar que dicha migración no es reversible.

La clase de migración de base de datos [[yii\db\Migration]] expone una conexión a la base de datos mediante la propiedad [[yii\db\Migration::db|db]].
Puedes utilizar esto para manipular el esquema de la base de datos utilizando métodos como se describen en
[Trabajando con Esquemas de Base de Datos](db-dao.md#database-schema).

En vez de utilizar tipos físicos, al crear tablas o columnas deberías utilizar los *tipos abstractos*
así las migraciones son independientes de algún DBMS específico. La clase [[yii\db\Schema]] define
un grupo de constantes que representan los tipos abstractos soportados. Dichas constantes son llamadas utilizando el formato
de `TYPE_<Name>`. Por ejemplo, `TYPE_PK` se refiere al tipo clave primaria auto-incremental; `TYPE_STRING`
se refiere al tipo string. Cuando se aplica una migración a una base de datos en particular, los tipos abstractos
serán traducidos a los tipos físicos correspondientes. En el caso de MySQL, `TYPE_PK` será transformado
en `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, mientras `TYPE_STRING` se vuelve `varchar(255)`.

Puedes agregar restricciones adicionales al utilizar tipos abstractos. En el ejemplo anterior, ` NOT NULL` es agregado
a `Schema::TYPE_STRING` para especificar que la columna no puede ser `null`.

> Info: El mapeo entre tipos abstractos y tipos físicos es especificado en
  la propiedad [[yii\db\QueryBuilder::$typeMap|$typeMap]] en cada clase concreta `QueryBuilder`.

Desde la versión 2.0.6, puedes hacer uso del recientemente introducido generador de esquemas, el cual provee una forma más conveniente de definir las columnas.
De esta manera, la migración anterior podría ser escrita así:

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }
}
```

Existe una lista de todos los métodos disponibles para la definición de tipos de columna en la API de la documentación de [[yii\db\SchemaBuilderTrait]].


## Generar Migraciones <span id="generating-migrations"></span>

Desde la versión 2.0.7 la consola provee una manera muy conveniente de generar migraciones.

Si el nombre de la migración tiene una forma especial, por ejemplo `create_xxx_table` o `drop_xxx_table` entonces el archivo de la migración generada
contendrá código extra, en este caso para crear/eliminar tablas.
A continuación se describen todas estas variantes.

### Crear Tabla

```php
yii migrate/create create_post_table
```

esto genera

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

Para crear las columnas en ese momento, las puedes especificar vía la opción `--fields`.

```php
yii migrate/create create_post_table --fields="title:string,body:text"
```

genera

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}

```

Puedes especificar más parámetros para las columnas.

```php
yii migrate/create create_post_table --fields="title:string(12):notNull:unique,body:text"
```

genera

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

> Note: la clave primaria es automáticamente agragada y llamada `id` por defecto. Si quieres utilizar otro nombre puedes
> especificarlo así `--fields="name:primaryKey"`.

#### Claves Foráneas

Desde 2.0.8 el generador soporta claves foráneas utilizando la palabra clave `foreignKey`.

```php
yii migrate/create create_post_table --fields="author_id:integer:notNull:foreignKey(user),category_id:integer:defaultValue(1):foreignKey,title:string,body:text"
```

genera

```php
/**
 * Handles the creation for table `post`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `category`
 */
class m160328_040430_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->defaultValue(1),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);

        // creates index for column `author_id`
        $this->createIndex(
            'idx-post-author_id',
            'post',
            'author_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-post-author_id',
            'post',
            'author_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-post-category_id',
            'post',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-post-category_id',
            'post',
            'category_id',
            'category',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-post-author_id',
            'post'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-post-author_id',
            'post'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-post-category_id',
            'post'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-post-category_id',
            'post'
        );

        $this->dropTable('post');
    }
}
```

La posición de la palabra clave `foreignKey` en la descripción de la columna
no cambia el código generado. Esto significa:

- `author_id:integer:notNull:foreignKey(user)`
- `author_id:integer:foreignKey(user):notNull`
- `author_id:foreignKey(user):integer:notNull`

Todas generan el mismo código.

La palabra clave `foreignKey` puede tomar un parámetro entre paréntesis el cual
será el nombre de la tabla relacionada por la clave foránea generada. Si no se pasa ningún parámetro
el nombre de la tabla será deducido en base al nombre de la columna.

En el ejemplo anterior `author_id:integer:notNull:foreignKey(user)` generará
una columna llamada `author_id` con una clave foránea a la tabla `user` mientras
`category_id:integer:defaultValue(1):foreignKey` generará
`category_id` con una clave foránea a la tabla `category`.

### Eliminar Tabla

```php
yii migrate/create drop_post_table --fields="title:string(12):notNull:unique,body:text"
```

genera

```php
class m150811_220037_drop_post_table extends Migration
{
    public function up()
    {
        $this->dropTable('post');
    }

    public function down()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }
}
```

### Agregar Columna

Si el nombre de la migración está en la forma `add_xxx_column_to_yyy_table` entonces el archivo generado contendrá
las declaraciones `addColumn` y `dropColumn` necesarias.

Para agregar una columna:

```php
yii migrate/create add_position_column_to_post_table --fields="position:integer"
```

genera

```php
class m150811_220037_add_position_column_to_post_table extends Migration
{
    public function up()
    {
        $this->addColumn('post', 'position', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('post', 'position');
    }
}
```

### Eliminar Columna

Si el nombre de la migración está en la forma `drop_xxx_column_from_yyy_table` entonces el archivo generado contendrá
las declaraciones `addColumn` y `dropColumn` necesarias.

```php
yii migrate/create drop_position_column_from_post_table --fields="position:integer"
```

genera

```php
class m150811_220037_drop_position_column_from_post_table extends Migration
{
    public function up()
    {
        $this->dropColumn('post', 'position');
    }

    public function down()
    {
        $this->addColumn('post', 'position', $this->integer());
    }
}
```

### Agregar Tabla de Unión

Si el nombre de la migración está en la forma `create_junction_table_for_xxx_and_yyy_tables` entonces se generará el código necesario
para una tabla de unión.

```php
yii migrate/create create_junction_table_for_post_and_tag_tables --fields="created_at:dateTime"
```

genera

```php
/**
 * Handles the creation for table `post_tag`.
 * Has foreign keys to the tables:
 *
 * - `post`
 * - `tag`
 */
class m160328_041642_create_junction_table_for_post_and_tag_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post_tag', [
            'post_id' => $this->integer(),
            'tag_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'PRIMARY KEY(post_id, tag_id)',
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_tag-post_id',
            'post_tag',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_tag-post_id',
            'post_tag',
            'post_id',
            'post',
            'id',
            'CASCADE'
        );

        // creates index for column `tag_id`
        $this->createIndex(
            'idx-post_tag-tag_id',
            'post_tag',
            'tag_id'
        );

        // add foreign key for table `tag`
        $this->addForeignKey(
            'fk-post_tag-tag_id',
            'post_tag',
            'tag_id',
            'tag',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // drops foreign key for table `post`
        $this->dropForeignKey(
            'fk-post_tag-post_id',
            'post_tag'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_tag-post_id',
            'post_tag'
        );

        // drops foreign key for table `tag`
        $this->dropForeignKey(
            'fk-post_tag-tag_id',
            'post_tag'
        );

        // drops index for column `tag_id`
        $this->dropIndex(
            'idx-post_tag-tag_id',
            'post_tag'
        );

        $this->dropTable('post_tag');
    }
}
```

### Migraciones Transaccionales <span id="transactional-migrations"></span>

Al ejecutar migraciones complejas de BD, es importante asegurarse que todas las migraciones funcionen o fallen como una unidad
así la base de datos puede mantener integridad y consistencia. Para alcanzar este objetivo, se recomienda que
encierres las operación de la BD de cada migración en una [transacción](db-dao.md#performing-transactions).

Una manera simple de implementar migraciones transaccionales es poniendo el código de las migraciones en los métodos `safeUp()` y `safeDown()`.
Estos métodos se diferencias con `up()` y `down()` en que son encerrados implícitamente en una transacción.
Como resultado, si alguna de las operaciones dentro de estos métodos falla, todas las operaciones previas son automáticamente revertidas.

En el siguiente ejemplo, además de crear la tabla `news` también insertamos un registro inicial dentro de la dicha tabla.

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
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

Ten en cuenta que usualmente cuando ejecutas múltiples operaciones en la BD en `safeUp()`, deberías revertir su orden de ejecución
en `safeDown()`. En el ejemplo anterior primero creamos la tabla y luego insertamos la finla en `safeUp()`; mientras
que en `safeDown()` primero eliminamos el registro y posteriormente eliminamos la tabla.

> Note: No todos los DBMS soportan transacciones. Y algunas consultas a la BD no pueden ser puestas en transacciones. Para algunos ejemplos,
  por favor lee acerca de [commits implícitos](http://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html). En estos casos,
  deberías igualmente implementar `up()` y `down()`.


### Métodos de Acceso a la Base de Datos <span id="db-accessing-methods"></span>

La clase base [[yii\db\Migration]] provee un grupo de métodos que te permiten acceder y manipular bases de datos.
Podrías encontrar que estos métodos son nombrados de forma similar a los [métodos DAO](db-dao.md) provistos por la clase [[yii\db\Command]].
Por ejemplo, el método [[yii\db\Migration::createTable()]] te permite crear una nueva tabla,
tal como lo hace [[yii\db\Command::createTable()]].

El beneficio de utilizar lo métodos provistos por [[yii\db\Migration]] es que no necesitas explícitamente
crear instancias de [[yii\db\Command]], y la ejecución de cada método mostrará automáticamente mensajes útiles
diciéndote qué operaciones de la base de datos se realizaron y cuánto tiempo tomaron.

Debajo hay una lista de todos los métodos de acceso a la base de datos:

* [[yii\db\Migration::execute()|execute()]]: ejecuta una declaración SQL
* [[yii\db\Migration::insert()|insert()]]: inserta un único registro
* [[yii\db\Migration::batchInsert()|batchInsert()]]: inserta múltiples registros
* [[yii\db\Migration::update()|update()]]: actualiza registros
* [[yii\db\Migration::delete()|delete()]]: elimina registros
* [[yii\db\Migration::createTable()|createTable()]]: crea una nueva tabla
* [[yii\db\Migration::renameTable()|renameTable()]]: renombra una tabla
* [[yii\db\Migration::dropTable()|dropTable()]]: elimina una tabla
* [[yii\db\Migration::truncateTable()|truncateTable()]]: elimina todos los registros de una tabla
* [[yii\db\Migration::addColumn()|addColumn()]]: agrega una columna
* [[yii\db\Migration::renameColumn()|renameColumn()]]: renombra una columna
* [[yii\db\Migration::dropColumn()|dropColumn()]]: elimina una columna
* [[yii\db\Migration::alterColumn()|alterColumn()]]: modifica una columna
* [[yii\db\Migration::addPrimaryKey()|addPrimaryKey()]]: agrega una clave primaria
* [[yii\db\Migration::dropPrimaryKey()|dropPrimaryKey()]]: elimina una clave primaria
* [[yii\db\Migration::addForeignKey()|addForeignKey()]]: agrega una clave foránea
* [[yii\db\Migration::dropForeignKey()|dropForeignKey()]]: elimina una clave foránea
* [[yii\db\Migration::createIndex()|createIndex()]]: crea un índice
* [[yii\db\Migration::dropIndex()|dropIndex()]]: elimina un índice
* [[yii\db\Migration::addCommentOnColumn()|addCommentOnColumn()]]: agrega un comentario a una columna
* [[yii\db\Migration::dropCommentFromColumn()|dropCommentFromColumn()]]: elimina un comentario de una columna
* [[yii\db\Migration::addCommentOnTable()|addCommentOnTable()]]: agrega un comentario a una tabla
* [[yii\db\Migration::dropCommentFromTable()|dropCommentFromTable()]]: elimina un comentario de una tabla

> Info: [[yii\db\Migration]] no provee un método de consulta a la base de datos. Esto es porque normalmente no necesitas
  mostrar mensajes detallados al traer datos de una base de datos. También se debe a que puedes utilizar el poderoso
  [Query Builder](db-query-builder.md) para generar y ejecutar consultas complejas.

> Note: Al manipular datos utilizando una migración podrías encontrar que utilizando tus clases [Active Record](db-active-record.md)
> para esto podría ser útil ya que algo de la lógica ya está implementada ahí. Ten en cuenta de todos modos, que en contraste con
> el código escrito en las migraciones, cuya naturaleza es permanecer constante por siempre, la lógica de la aplicación está sujeta a cambios.
> Entonces al utilizar Active Record en migraciones, los cambios en la lógica en la capa Active Record podrían accidentalmente romper
> migraciones existentes. Por esta razón, el código de las migraciones debería permanecer independiente de determinada lógica de la aplicación
> tal como clases Active Record.


## Aplicar Migraciones <span id="applying-migrations"></span>

To upgrade a database to its latest structure, you should apply all available new migrations using the following command:
Para actualizar una base de datos a su última estructura, deberías aplicar todas las nuevas migraciones utilizando el siguiente comando:

```
yii migrate
```

Este comando listará todas las migraciones que no han sido aplicadas hasta el momento. Si confirmas que quieres aplicar
dichas migraciones, se correrá el método `up()` o `safeUp()` en cada clase de migración nueva, una tras otra,
en el orden de su valor de marca temporal. Si alguna de las migraciones falla, el comando terminará su ejecución sin aplicar
el resto de las migraciones.

> Tip: En caso de no disponer de la línea de comandos en el servidor, podrías intentar utilizar
> la extensión [web shell](https://github.com/samdark/yii2-webshell).

Por cada migración aplicada correctamente, el comando insertará un registro en la base de datos, en la tabla llamada
`migration` para registrar la correcta aplicación de la migración. Esto permitirá a la herramienta de migración identificar
cuáles migraciones han sido aplicadas y cuáles no.

> Info: La herramienta de migración creará automáticamente la tabla  `migration` en la base de datos especificada
  en la opción [[yii\console\controllers\MigrateController::db|db]] del comando. Por defecto, la base de datos
  es especificada en el [componente de aplicación](structure-application-components.md) `db`.

A veces, podrías sólo querer aplicar una o algunas pocas migraciones, en vez de todas las migraciones disponibles.
Puedes hacer esto el número de migraciones que quieres aplicar al ejecutar el comando.
Por ejemplo, el siguiente comando intentará aplicar las tres siguientes migraciones disponibles:

```
yii migrate 3
```

Puedes además explícitamente especificar una migración en particular a la cual la base de datos debería migrar
utilizando el comando `migrate/to` de acuerdo a uno de los siguientes formatos:

```
yii migrate/to 150101_185401                      # utiliza la marca temporal para especificar la migración
yii migrate/to "2015-01-01 18:54:01"              # utiliza un string que puede ser analizado por strtotime()
yii migrate/to m150101_185401_create_news_table   # utiliza el nombre completo
yii migrate/to 1392853618                         # utiliza el tiempo UNIX
```

Si hubiera migraciones previas a la especificada sin aplicar, estas serán aplicadas antes de que la migración especificada
sea aplicada.

Si la migración especificada ha sido aplicada previamente, cualquier migración aplicada posteriormente será revertida.


## Revertir Migraciones <span id="reverting-migrations"></span>

Para revertir (deshacer) una o varias migraciones ya aplicadas, puedes ejecutar el siguiente comando:

```
yii migrate/down     # revierte la más reciente migración aplicada
yii migrate/down 3   # revierte las 3 últimas migraciones aplicadas
```

> Note: No todas las migraciones son reversibles. Intentar revertir tales migraciones producirá un error y detendrá
  completamente el proceso de reversión.


## Rehacer Migraciones <span id="redoing-migrations"></span>

Rehacer (re-ejecutar) migraciones significa primero revertir las migraciones especificadas y luego aplicarlas nuevamente. Esto puede hacerse
de esta manera:

```
yii migrate/redo        # rehace la más reciente migración aplicada
yii migrate/redo 3      # rehace las 3 últimas migraciones aplicadas
```

> Note: Si una migración no es reversible, no tendrás posibilidades de rehacerla.


## Listar Migraciones <span id="listing-migrations"></span>

Para listar cuáles migraciones han sido aplicadas y cuáles no, puedes utilizar los siguientes comandos:

```
yii migrate/history     # muestra las últimas 10 migraciones aplicadas
yii migrate/history 5   # muestra las últimas 5 migraciones aplicadas
yii migrate/history all # muestra todas las migraciones aplicadas

yii migrate/new         # muestra las primeras 10 nuevas migraciones
yii migrate/new 5       # muestra las primeras 5 nuevas migraciones
yii migrate/new all     # muestra todas las nuevas migraciones
```


## Modificar el Historial de Migraciones <span id="modifying-migration-history"></span>

En vez de aplicar o revertir migraciones, a veces simplemente quieres marcar que tu base de datos
ha sido actualizada a una migración en particular. Esto sucede normalmente cuando cambias manualmente la base de datos
a un estado particular y no quieres que la/s migración/es de ese cambio sean re-aplicadas posteriormente. Puedes alcanzar este objetivo
con el siguiente comando:

```
yii migrate/mark 150101_185401                      # utiliza la marca temporal para especificar la migración
yii migrate/mark "2015-01-01 18:54:01"              # utiliza un string que puede ser analizado por strtotime()
yii migrate/mark m150101_185401_create_news_table   # utiliza el nombre completo
yii migrate/mark 1392853618                         # utiliza el tiempo UNIX
```

El comando modificará la tabla `migration` agregando o eliminado ciertos registros para indicar que en la base de datos
han sido aplicadas las migraciones hasta la especificada. Ninguna migración será aplicada ni revertida por este comando.


## Personalizar Migraciones <span id="customizing-migrations"></span>

Hay varias maneras de personalizar el comando de migración.


### Utilizar Opciones de la Línea de Comandos <span id="using-command-line-options"></span>

El comando de migración trae algunas opciones de línea de comandos que pueden ser utilizadas para personalizar su comportamiento:

* `interactive`: boolean (por defecto `true`), especificar si se debe ejecutar la migración en modo interactivo.
  Cuando se indica `true`, se le pedirá confirmación al usuario antes de ejecutar ciertas acciones.
  Puedes querer definirlo como `false` si el comando está siendo utilizado como un proceso de fondo.

* `migrationPath`: string (por defecto `@app/migrations`), especifica el directorio que contiene todos los archivos
  de clase de las migraciones. Este puede ser especificado tanto como una ruta a un directorio un [alias](concept-aliases.md) de ruta.
  Ten en cuenta que el directorio debe existir, o el comando disparará un error.

* `migrationTable`: string (por defecto `migration`), especifica el nombre de la tabla de la base de datos que almacena
  información del historial de migraciones. Dicha tabla será creada por el comando en caso de que no exista.
  Puedes también crearla manualmente utilizando la estructura `version varchar(255) primary key, apply_time integer`.

* `db`: string (por defecto `db`), especifica el ID del [componente de aplicación](structure-application-components.md) de la base de datos.
  Esto representa la base de datos que será migrada en este comando.

* `templateFile`: string (por defecto `@yii/views/migration.php`), especifica la ruta al template
  utilizado para generar el esqueleto de los archivos de clases de migración. Puede ser especificado tanto como una ruta a un archivo
  como una [alias](concept-aliases.md) de una ruta. El template es un archivo PHP en el cual puedes utilizar una variable predefinida
  llamada `$className` para obtener el nombre de clase de la migración.

* `generatorTemplateFiles`: array (por defecto `[
        'create_table' => '@yii/views/createTableMigration.php',
        'drop_table' => '@yii/views/dropTableMigration.php',
        'add_column' => '@yii/views/addColumnMigration.php',
        'drop_column' => '@yii/views/dropColumnMigration.php',
        'create_junction' => '@yii/views/createTableMigration.php'
  ]`), especifica los templates utilizados para generar las migraciones. Ver "[Generar Migraciones](#generating-migrations)"
  para más detalles.

* `fields`: array de strings de definiciones de columna utilizado por el código de migración. Por defecto `[]`. El formato de cada
  definición es `COLUMN_NAME:COLUMN_TYPE:COLUMN_DECORATOR`. Por ejemplo, `--fields=name:string(12):notNull` produce
  una columna string de tamaño 12 que es not `null`.

El siguiente ejemplo muestra cómo se pueden utilizar estas opciones.

Por ejemplo, si queremos migrar un módulo `forum` cuyos arhivos de migración
están ubicados dentro del directorio `migrations` del módulo, podemos utilizar el siguientedocs/guide-es/db-migrations.md
comando:

```
# realiza las migraciones de un módulo forum sin interacción del usuario
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```


### Configurar el Comando Globalmente <span id="configuring-command-globally"></span>

En vez de introducir los valores de las opciones cada vez que ejecutas un comandod e migración, podrías configurarlos
de una vez por todas en la configuración de la aplicación como se muestra a continuación:

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

Con esta configuración, cada vez que ejecutes un comando de migración, la tabla `backend_migration`
será utilizada para registrar el historial de migraciones. No necesitarás volver a especificarla con la opción `migrationTable`
de la línea de comandos.


## Migrar Múltiples Bases de Datos <span id="migrating-multiple-databases"></span>

Por defecto, las migraciones son aplicadas en la misma base de datos especificada en el [componente de aplicación](structure-application-components.md) `db`.
Si quieres que sean aplicadas en una base de datos diferente, puedes especificar la opción `db` como se muestra a continuación,

```
yii migrate --db=db2
```

El comando anterior aplicará las migraciones en la base de datos `db2`.

A veces puede suceder que quieras aplicar *algunas* de las migraciones a una base de datos, mientras algunas otras
a una base de datos distinta. Para lograr esto, al implementar una clase de migración debes especificar explícitamente el ID del componente DB
que la migración debe utilizar, como a continuación:

```php
<?php

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

La migración anterior se aplicará a `db2`, incluso si especificas una base de datos diferente en la opción `db` de la
línea de comandos. Ten en cuenta que el historial aún será registrado in la base de datos especificada en la opción `db` de la línea de comandos.

Si tienes múltiples migraciones que utilizan la misma base de datos, es recomandable que crees una clase base de migración 
con el código `init()` mostrado. Entonces cada clase de migración puede extender de esa clase base.

> Tip: Aparte de definir la propiedad [[yii\db\Migration::db|db]], puedes también operar en diferentes bases de datos
  creando nuevas conexiones de base de datos en tus clases de migración. También puedes utilizar [métodos DAO](db-dao.md)
  con esas conexiones para manipular diferentes bases de datos.

Another strategy that you can take to migrate multiple databases is to keep migrations for different databases in
different migration paths. Then you can migrate these databases in separate commands like the following:
Otra estrategia que puedes seguir para migrar múltiples bases de datos es mantener las migraciones para diferentes bases de datos en
distintas rutas de migración. Entonces podrías migrar esas bases de datos en comandos separados como a continuación:

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

El primer comando aplicará las migraciones que se encuentran en `@app/migrations/db1` en la base de datos `db1`, el segundo comando
aplicará las migraciones que se encuentran en `@app/migrations/db2` en `db2`, y así sucesivamente.
