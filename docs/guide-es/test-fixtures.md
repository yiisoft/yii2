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
donde `FixturePath` correponde al directorio conteniendo el archivo de clase del fixture, y `TableName`
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
porque la table de perfiles de usuarios contiene una cláve foránea a la tabla user.
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

The dependency also ensures, that the fixtures are loaded and unloaded in a well defined order. In the above example `UserFixture` will
always be loaded before `UserProfileFixture` to ensure all foreign key references exist and will be unloaded after `UserProfileFixture`
has been unloaded for the same reason.
La dependencia también asegura que los fixtures son cargados y descargados en un orden bien definido. En el ejemplo `UserFixture`
será siempre cargado antes de `UserProfileFixture` para asegurar que todas las referencias de las claves foráneas existan y será siempre descargado después de `UserProfileFixture`
por la misma razón.

Arriba te mostramos cómo definir un fixture de BD. Para definir un fixture no relacionado a BD
(por ej. un fixture acerca de archivos y directorios), puedes extender de la clase base más general
[[yii\test\Fixture]] y sobrescribir los métodos [[yii\test\Fixture::load()|load()]] y [[yii\test\Fixture::unload()|unload()]].


Utilizar Fixtures
-----------------

Si estás utilizando [Codeception](http://codeception.com/) para hacer tests de tu código, deberías considerar el utilizar
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
            'profiles' => UserProfileFixture::className(),
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

The fixtures described above are mainly used by individual test cases. In most cases, you also need some global
fixtures that are applied to ALL or many test cases. An example is [[yii\test\InitDbFixture]] which does
two things:
Los fixtures descritos arriba son principalmente utilizados para casos de tests individuales. En la mayoría de los casos, puedes necesitar algunos
fixtures globales que sean aplicados a TODOS o muchos casos de test. Un ejemplo sería [[yii\test\InitDbFixture]], que hace
dos cosas:

* Realiza alguna tarea de inicialización común al ejectutar un script ubicado en `@app/tests/fixtures/initdb.php`;
* Deshabilita la comprobación de integridad antes de cargar otros fixtures de BD, y la rehabilita después de que todos los fixtures son descargados.

Utilizar fixtures globales es similar a utilizar los no-globales. La única diferencia es que declaras estos fixtures
en [[yii\codeception\TestCase::globalFixtures()]] en vez de en `fixtures()`. Cuando un caso de test carga fixtures,
primero carga los globales y luego los no-globales.

By default, [[yii\codeception\DbTestCase]] already declares `InitDbFixture` in its `globalFixtures()` method.
This means you only need to work with `@app/tests/fixtures/initdb.php` if you want to do some initialization work
before each test. You may otherwise simply focus on developing each individual test case and the corresponding fixtures.
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

In this way you will avoid collision of fixture data files between tests and use them as you need.
De esta manera evitarás la colisión de archivos de datos de fixtures entre tests y podrás utlilizarlos como necesites.

> Note: In the example above fixture files are named only for example purpose. In real life you should name them
> according to which fixture class your fixture classes are extending from. For example, if you are extending
> from [[yii\test\ActiveFixture]] for DB fixtures, you should use DB table names as the fixture data file names;
> If you are extending from [[yii\mongodb\ActiveFixture]] for MongoDB fixtures, you should use collection names as the file names.

The similar hierarchy can be used to organize fixture class files. Instead of using `data` as the root directory, you may
want to use `fixtures` as the root directory to avoid conflict with the data files.


Summary
-------

> Note: This section is under development.

In the above, we have described how to define and use fixtures. Below we summarize the typical workflow
of running unit tests related with DB:

1. Use `yii migrate` tool to upgrade your test database to the latest version;
2. Run a test case:
   - Load fixtures: clean up the relevant DB tables and populate them with fixture data;
   - Perform the actual test;
   - Unload fixtures.
3. Repeat Step 2 until all tests finish.


**To be cleaned up below**

Managing Fixtures
=================

> Note: This section is under development.
>
> todo: this tutorial may be merged with the above part of test-fixtures.md

Fixtures are important part of testing. Their main purpose is to populate you with data that needed by testing
different cases. With this data using your tests becoming more efficient and useful.

Yii supports fixtures via the `yii fixture` command line tool. This tool supports:

* Loading fixtures to different storage such as: RDBMS, NoSQL, etc;
* Unloading fixtures in different ways (usually it is clearing storage);
* Auto-generating fixtures and populating it with random data.

Fixtures format
---------------

Fixtures are objects with different methods and configurations, refer to official [documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixtures.md) on them.
Lets assume we have fixtures data to load:

```
#users.php file under fixtures data path, by default @tests\unit\fixtures\data

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
If we are using fixture that loads data into database then these rows will be applied to `users` table. If we are using nosql fixtures, for example `mongodb`
fixture, then this data will be applied to `users` mongodb collection. In order to learn about implementing various loading strategies and more, refer to official [documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixtures.md).
Above fixture example was auto-generated by `yii2-faker` extension, read more about it in these [section](#auto-generating-fixtures).
Fixture classes name should not be plural.

Loading fixtures
----------------

Fixture classes should be suffixed by `Fixture` class. By default fixtures will be searched under `tests\unit\fixtures` namespace, you can
change this behavior with config or command options. You can exclude some fixtures due load or unload by specifying `-` before its name like `-User`.

To load fixture, run the following command:

```
yii fixture/load <fixture_name>
```

The required `fixture_name` parameter specifies a fixture name which data will be loaded. You can load several fixtures at once.
Below are correct formats of this command:

```
// load `User` fixture
yii fixture/load User

// same as above, because default action of "fixture" command is "load"
yii fixture User

// load several fixtures
yii fixture User UserProfile

// load all fixtures
yii fixture/load "*"

// same as above
yii fixture "*"

// load all fixtures except ones
yii fixture "*" -DoNotLoadThisOne

// load fixtures, but search them in different namespace. By default namespace is: tests\unit\fixtures.
yii fixture User --namespace='alias\my\custom\namespace'

// load global fixture `some\name\space\CustomFixture` before other fixtures will be loaded.
// By default this option is set to `InitDbFixture` to disable/enable integrity checks. You can specify several
// global fixtures separated by comma.
yii fixture User --globalFixtures='some\name\space\Custom'
```

Unloading fixtures
------------------

To unload fixture, run the following command:

```
// unload Users fixture, by default it will clear fixture storage (for example "users" table, or "users" collection if this is mongodb fixture).
yii fixture/unload User

// Unload several fixtures
yii fixture/unload User,UserProfile

// unload all fixtures
yii fixture/unload "*"

// unload all fixtures except ones
yii fixture/unload "*" -DoNotUnloadThisOne

```

Same command options like: `namespace`, `globalFixtures` also can be applied to this command.

Configure Command Globally
--------------------------
While command line options allow us to configure the migration command
on-the-fly, sometimes we may want to configure the command once for all. For example you can configure
different migration path as follows:

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

Auto-generating fixtures
------------------------

Yii also can auto-generate fixtures for you based on some template. You can generate your fixtures with different data on different languages and formats.
These feature is done by [Faker](https://github.com/fzaninotto/Faker) library and `yii2-faker` extension.
See extension [guide](https://github.com/yiisoft/yii2-faker) for more docs.
