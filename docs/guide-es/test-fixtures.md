Fixtures
========

Los fixtures son una parte importante de los tests. Su propósito principal es el de preparar el entorno en una estado fijado/conocido
de manera que los tests sean repetibles y corran de la manera esperada. Yii provee un framework de fixtures que te permite
dichos fixtures de manera precisa y usarlo de forma simple.

Un concepto clave en el framework de fixtures de Yii es el llamado *objeto fixture*. Un objeto fixture representa
un aspecto particular de un entorno de pruebas y es una instancia de [[yii\test\Fixture]] o heredada de esta. Por ejemplo,
puedes utilizar `UserFixture` para asegurarte de que la tabla de usuarios de la BD contiene un grupo de datos fijos. Entonces cargas uno o varios
objetos fixture antes de correr un test y lo descargas cuando el test ha concluido.

Un fixture puede depender de otros fixtures, especificándolo en su propiedad [[yii\test\Fixture::depends]].
Cuando un fixture está siendo cargado, los fixtures de los que depende serán cargados automáticamente ANTES que él;
y cuando el fixture está siendo descargado, los fixtures dependientes serán descargados DESPUÉS de él.


Definir un Fixture
------------------

Para definir un fixture, crea una nueva clase que extienda de [[yii\test\Fixture]] o [[yii\test\ActiveFixture]].
El primero es más adecuado para fixtures de propósito general, mientras que el último tiene características mejoradas específicamente
diseñadas para trabajar con base de datos y ActiveRecord.

El siguiente código define un fixture acerca del ActiveRecord `User` y su correspondiente tabla user.

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
}
```

> Tip: Cada `ActiveFixture` se encarga de preparar la tabla de la DB para los tests. Puedes especificar la tabla
> definiendo tanto la propiedad [[yii\test\ActiveFixture::tableName]] o la propiedad [[yii\test\ActiveFixture::modelClass]].
> Haciéndolo como el último, el nombre de la tabla será tomado de la clase `ActiveRecord` especificada en `modelClass`.

> Note: [[yii\test\ActiveFixture]] es sólo adecualdo para bases de datos SQL. Para bases de datos NoSQL, Yii provee
> las siguientes clases `ActiveFixture`:
>
> - Mongo DB: [[yii\mongodb\ActiveFixture]]
> - Elasticsearch: [[yii\elasticsearch\ActiveFixture]] (desde la versión 2.0.2)


Los datos para un fixture `ActiveFixture` son usualmente provistos en un archivo ubicado en `FixturePath/data/TableName.php`,
donde `FixturePath` corresponde al directorio conteniendo el archivo de clase del fixture, y `TableName`
es el nombre de la tabla asociada al fixture. En el ejemplo anterior, el archivo debería ser
`@app/tests/fixtures/data/user.php`. El archivo de datos debe devolver un array de registros
a ser insertados en la tabla user. Por ejemplo,

```php
<?php
return [
    'user1' => [
        'username' => 'lmayert',
        'email' => 'strosin.vernice@jerde.com',
        'auth_key' => 'K3nF70it7tzNsHddEiq0BZ0i-OU8S3xV',
        'password' => '$2y$13$WSyE5hHsG1rWN2jV8LRHzubilrCLI5Ev/iK0r3jRuwQEs2ldRu.a2',
    ],
    'user2' => [
        'username' => 'napoleon69',
        'email' => 'aileen.barton@heaneyschumm.com',
        'auth_key' => 'dZlXsVnIDgIzFgX4EduAqkEPuphhOh9q',
        'password' => '$2y$13$kkgpvJ8lnjKo8RuoR30ay.RjDf15bMcHIF7Vz1zz/6viYG5xJExU6',
    ],
];
```

Puedes dar un alias al registro tal que más tarde en tu test, puedas referirte a ese registra a través de dicho alias. En el ejemplo anterior,
los dos registros tienen como alias `user1` y `user2`, respectivamente.

Además, no necesitas especificar los datos de columnas auto-incrementales. Yii automáticamente llenará esos valores
dentro de los registros cuando el fixture está siendo cargado.

> Tip: Puedes personalizar la ubicación del archivo de datos definiendo la propiedad [[yii\test\ActiveFixture::dataFile]].
> Puedes también sobrescribir [[yii\test\ActiveFixture::getData()]] para obtener los datos.

Como se describió anteriormente, un fixture puede depender de otros fixtures. Por ejemplo, un `UserProfileFixture` puede necesitar depender de `UserFixture`
porque la table de perfiles de usuarios contiene una clave foránea a la tabla user.
La dependencia es especificada vía la propiedad [[yii\test\Fixture::depends]], como a continuación,

```php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserProfileFixture extends ActiveFixture
{
    public $modelClass = 'app\models\UserProfile';
    public $depends = ['app\tests\fixtures\UserFixture'];
}
```

La dependencia también asegura que los fixtures son cargados y descargados en un orden bien definido. En el ejemplo `UserFixture`
será siempre cargado antes de `UserProfileFixture` para asegurar que todas las referencias de las claves foráneas existan y será siempre descargado después de `UserProfileFixture`
por la misma razón.

Arriba te mostramos cómo definir un fixture de BD. Para definir un fixture no relacionado a BD
(por ej. un fixture acerca de archivos y directorios), puedes extender de la clase base más general
[[yii\test\Fixture]] y sobrescribir los métodos [[yii\test\Fixture::load()|load()]] y [[yii\test\Fixture::unload()|unload()]].


Utilizar Fixtures
-----------------

Si estás utilizando [Codeception](https://codeception.com/) para hacer tests de tu código, deberías considerar el utilizar
la extensión `yii2-codeception`, que tiene soporte incorporado para la carga y acceso a fixtures.
En caso de que utilices otros frameworks de testing, puedes usar [[yii\test\FixtureTrait]] en tus casos de tests
para alcanzar el mismo objetivo.

A continuación describiremos cómo escribir una clase de test de unidad `UserProfile` utilizando `yii2-codeception`.

En tu clase de test de unidad que extiende de [[yii\codeception\DbTestCase]] o [[yii\codeception\TestCase]],
indica cuáles fixtures quieres utilizar en el método [[yii\test\FixtureTrait::fixtures()|fixtures()]]. Por ejemplo,

```php
namespace app\tests\unit\models;

use yii\codeception\DbTestCase;
use app\tests\fixtures\UserProfileFixture;

class UserProfileTest extends DbTestCase
{
    public function fixtures()
    {
        return [
            'profiles' => UserProfileFixture::class,
        ];
    }

    // ...métodos de test...
}
```

Los fixtures listados en el método `fixtures()` serán automáticamente cargados antes de correr cada método de test
en el caso de test y descargado al finalizar cada uno. También, como describimos antes, cuando un fixture está
siendo cargado, todos sus fixtures dependientes serán cargados primero. En el ejemplo de arriba, debido a que
`UserProfileFixture` depende de `UserFixture`, cuando ejecutas cualquier método de test en la clase,
dos fixtures serán cargados secuencialmente: `UserFixture` y `UserProfileFixture`.

Al especificar fixtures en `fixtures()`, puedes utilizar tanto un nombre de clase o un array de configuración para referirte a
un fixture. El array de configuración te permitirá personalizar las propiedades del fixture cuando este es cargado.

Puedes también asignarles alias a los fixtures. En el ejemplo anterior, el `UserProfileFixture` tiene como alias `profiles`.
En los métodos de test, puedes acceder a un objeto fixture utilizando su alias. Por ejemplo, `$this->profiles`
devolverá el objeto `UserProfileFixture`.

Dado que `UserProfileFixture` extiende de `ActiveFixture`, puedes por lo tanto usar la siguiente sintáxis para acceder
a los datos provistos por el fixture:

```php
// devuelve el registro del fixture cuyo alias es 'user1'
$row = $this->profiles['user1'];
// devuelve el modelo UserProfile correspondiente al registro cuyo alias es 'user1'
$profile = $this->profiles('user1');
// recorre cada registro en el fixture
foreach ($this->profiles as $row) ...
```

> Info: `$this->profiles` es todavía del tipo `UserProfileFixture`. Las características de acceso mostradas arriba son implementadas
> a través de métodos mágicos de PHP.


Definir y Utilizar Fixtures Globales
------------------------------------

Los fixtures descritos arriba son principalmente utilizados para casos de tests individuales. En la mayoría de los casos, puedes necesitar algunos
fixtures globales que sean aplicados a TODOS o muchos casos de test. Un ejemplo sería [[yii\test\InitDbFixture]], que hace
dos cosas:

* Realiza alguna tarea de inicialización común al ejectutar un script ubicado en `@app/tests/fixtures/initdb.php`;
* Deshabilita la comprobación de integridad antes de cargar otros fixtures de BD, y la rehabilita después de que todos los fixtures son descargados.

Utilizar fixtures globales es similar a utilizar los no-globales. La única diferencia es que declaras estos fixtures
en [[yii\codeception\TestCase::globalFixtures()]] en vez de en `fixtures()`. Cuando un caso de test carga fixtures,
primero carga los globales y luego los no-globales.

Por defecto, [[yii\codeception\DbTestCase]] ya declara `InitDbFixture` en su método `globalFixtures()`.
Esto significa que sólo necesitas trabajar con `@app/tests/fixtures/initdb.php` si quieres realizar algún trabajo de inicialización
antes de cada test. Sino puedes simplemente enfocarte en desarrollar cada caso de test individual y sus fixtures correspondientes.


Organizar Clases de Fixtures y Archivos de Datos
------------------------------------------------

Por defecto, las clases de fixtures busca los archivos de datos correspondientes dentro de la carpeta `data`, que es una subcarpeta
de la carpeta conteniendo los archivos de clases de fixtures. Puedes seguir esta convención al trabajar en proyectos simples.
Para proyectos más grandes, es probable que a menudo necesites intercambiar entre diferentes archivos de datos para la misma clase de fixture
en diferentes tests. Recomendamos que organices los archivos de datos en forma jerárquica similar
a tus espacios de nombre de clases. Por ejemplo,

```
# bajo la carpeta tests\unit\fixtures

data\
    components\
        fixture_data_file1.php
        fixture_data_file2.php
        ...
        fixture_data_fileN.php
    models\
        fixture_data_file1.php
        fixture_data_file2.php
        ...
        fixture_data_fileN.php
# y así sucesivamente
```

De esta manera evitarás la colisión de archivos de datos de fixtures entre tests y podrás utlilizarlos como necesites.

> Note: En el ejemplo de arriba los archivos de fixtures son nombrados así sólo como ejemplo. En la vida real deberías nombrarlos
> de acuerdo a qué clase de fixture extienden tus clases de fixtures. Por ejemplo, si estás extendiendo
> de [[yii\test\ActiveFixture]] para fixtures de BD, deberías utilizar nombres de tabla de la BD como nombres de los archivos de fixtures;
> Si estás extendiendo de [[yii\mongodb\ActiveFixture]] para fixtures de MongoDB, deberías utilizar nombres de colecciones para los nombres de archivo.

Se puede utilizar una jerarquía similar para organizar archivos de clases de fixtures. En vez de utilizar `data` como directorio raíz, podrías
querer utilizar `fixtures` como directorio raíz para evitar conflictos con los archivos de datos.


Resumen
-------

> Note: Esta sección se encuentra en desarrollo.

Arriba, definimos cómo definir y utilizar fixtures. Abajo resumiremos el típico flujo de trabajo
de correr tests de unidad relacionados a BD:

1. Usa la herramienta `yii migrate` para actualizar tu base de datos de prueba a la última versión;
2. Corre el caso de test:
   - Carga los fixtures: limpia las tablas de la BD relevantes y cargala con los datos de los fixtures;
   - Realiza el test en sí;
   - Descarga los fixtures.
3. Repite el Paso 2 hasta que todos los tests terminen.


**Lo siguiente, a ser limpiado**

Administrar Fixtures
====================

> Note: Esta sección está en desarrollo.
>
> todo: este tutorial podría ser unificado con la parte de arriba en test-fixtures.md

Los fixtures son una parte importante del testing. Su principal propósito es el de poblarte con datos necesarios para el test
de diferentes casos. Con estos datos. utilizar tests se vuelve más eficiente y útil.

Yii soporta fixtures a través de la herramienta de línea de comandos `yii fixture`. Esta herramienta soporta:

* Cargar fixtures a diferentes almacenamientos: RDBMS, NoSQL, etc;
* Descargar fixtures de diferentes maneras (usualmente limpiando el almacenamiento);
* Auto-generar fixtures y poblarlos con datos al azar.

Formato de Fixtures
-------------------

Los fixtures son objetos con diferentes métodos y configuraciones, inspecciónalos en la [documentación oficial](https://github.com/yiisoft/yii2/blob/master/docs/guide-es/test-fixtures.md).
Asumamos que tenemos datos de fixtures a cargar:

```
#archivo users.php bajo la ruta de los fixtures, por defecto @tests\unit\fixtures\data

return [
    [
        'name' => 'Chase',
        'login' => 'lmayert',
        'email' => 'strosin.vernice@jerde.com',
        'auth_key' => 'K3nF70it7tzNsHddEiq0BZ0i-OU8S3xV',
        'password' => '$2y$13$WSyE5hHsG1rWN2jV8LRHzubilrCLI5Ev/iK0r3jRuwQEs2ldRu.a2',
    ],
    [
        'name' => 'Celestine',
        'login' => 'napoleon69',
        'email' => 'aileen.barton@heaneyschumm.com',
        'auth_key' => 'dZlXsVnIDgIzFgX4EduAqkEPuphhOh9q',
        'password' => '$2y$13$kkgpvJ8lnjKo8RuoR30ay.RjDf15bMcHIF7Vz1zz/6viYG5xJExU6',
    ],
];
```
Si estamos utilizando un fixture que carga datos en la base de datos, entonces esos registros serán insertados en la tabla `users`. Si estamos utilizando fixtures no sql, por ejemplo de `mongodb`,
entonces estos datos serán aplicados a la colección mongodb `users`. Para aprender cómo implementar varias estrategias de carga y más, visita la [documentación oficial](https://github.com/yiisoft/yii2/blob/master/docs/guide-es/test-fixtures.md).
El fixture de ejemplo de arriba fue autogenerado por la extensión `yii2-faker`, lee más acerca de esto en su [sección](#auto-generating-fixtures).
Los nombres de clase de fixtures no deberían ser en plural.

Cargar fixtures
----------------

Las clases de fixture deberían tener el prefijo `Fixture`. Por defecto los fixtures serán buscados bajo el espacio de nombre `tests\unit\fixtures`, puedes
modificar este comportamiento con opciones de comando o configuración. Puedes excluir algunos fixtures para carga o descarga especificando `-` antes de su nombre, por ejemplo `-User`.

Para cargar un fixture, ejecuta el siguiente comando:

```
yii fixture/load <fixture_name>
```

El parámetro requerido `fixture_name` especifica un nombre de fixture cuyos datos serán cargados. Puedes cargar varios fixtures de una sola vez.
Abajo se muestran formatos correctos de este comando:

```
// carga el fixture `User`
yii fixture/load User

// lo mismo que arriba, dado que la acción por defecto del comando "fixture" es "load"
yii fixture User

// carga varios fixtures
yii fixture "User, UserProfile"

// carga todos los fixtures
yii fixture/load "*"

// lo mismo que arriba
yii fixture "*"

// carga todos los fixtures excepto uno
yii fixture "*, -DoNotLoadThisOne"

// carga fixtures, pero los busca en diferente espacio de nombre. El espacio de nombre por defecto es: tests\unit\fixtures.
yii fixture User --namespace='alias\my\custom\namespace'

// carga el fixture global `some\name\space\CustomFixture` antes de que otros fixtures sean cargados.
// Por defecto está opción se define como `InitDbFixture` para habilitar/deshabilitar la comprobación de integridad. Puedes especificar varios
// fixtures globales separados por coma.
yii fixture User --globalFixtures='some\name\space\Custom'
```

Descargar fixtures
------------------

Para descargar un fixture, ejecuta el siguiente comando:

```
// descarga el fixture Users, por defecto limpiará el almacenamiento del fixture (por ejemplo la tabla "users", o la colección "users" si es un fixture mongodb).
yii fixture/unload User

// descarga varios fixtures
yii fixture/unload "User, UserProfile"

// descarga todos los fixtures
yii fixture/unload "*"

// descarga todos los fixtures excepto uno
yii fixture/unload "*, -DoNotUnloadThisOne"

```

Opciones de comando similares como: `namespace`, `globalFixtures` también pueden ser aplicadas a este comando.

Configurar el Comando Globalmente
---------------------------------
Mientras que las opciones de línea de comandos nos permiten configurar el comando de migración
en el momento, a veces queremos configurar el comando de una vez y para siempre. Por ejemplo puedes configurar
diferentes rutas de migración como a continuación:

```
'controllerMap' => [
    'fixture' => [
        'class' => 'yii\console\controllers\FixtureController',
        'namespace' => 'myalias\some\custom\namespace',
        'globalFixtures' => [
            'some\name\space\Foo',
            'other\name\space\Bar'
        ],
    ],
]
```

Autogenerando fixtures
----------------------

Yii puede también autogenerar fixtures por tí basándose en algún template. Puedes generar tus fixtures con distintos datos en diferentes lenguajes y formatos.
Esta característica es realizada por la librería [Faker](https://github.com/fzaninotto/Faker) y la extensión `yii2-faker`.
Visita la [guía de la extensión](https://github.com/yiisoft/yii2-faker) para mayor documentación.
