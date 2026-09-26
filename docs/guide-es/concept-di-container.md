Contenedor de Inyección de Dependencias
=======================================

Un contenedor de Inyección de Dependencias (ID), es un objeto que sabe como instancias y configurar objetos y sus 
objetos dependientes. El [articulo de Martin](https://martinfowler.com/articles/injection.html) contiene una buena 
explicación de porque son útiles los contenedores de ID. A continuación explicaremos como usar el contenedor de ID que 
proporciona Yii.

Inyección de Dependencias <span id="dependency-injection"></span>
-------------------------

Yii proporciona la función de contenedor de ID mediante la clase [[yii\di\Container]]. Soporta los siguientes tipos 
de ID:

* Inyección de constructores;
* Inyección de setters y propiedades;
* Inyección de [llamadas de retorno PHP](https://www.php.net/manual/es/language.types.callable.php);

### Inyección de Constructores <span id="constructor-injection"></span>

El contenedor de ID soporta inyección de constructores con la ayuda de los indicios (hint) de tipo para los parámetros del 
constructor. Los indicios de tipo le proporcionan información al contenedor para saber cuáles son las clases o 
interfaces dependientes al usarse para crear un nuevo objeto. El contenedor intentara obtener las instancias de las 
clases o interfaces dependientes y las inyectará dentro del nuevo objeto mediante el constructor. Por ejemplo,

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// que es equivalente a:
$bar = new Bar;
$foo = new Foo($bar);
```

### Inyección de Setters y Propiedades <span id="setter-and-property-injection"></span>

La inyección de setters y propiedades se admite a través de [configuraciones](concept-configurations.md). Cuando se 
registra una dependencia o se crea un nuevo objeto, se puede proporcionar una configuración que usará el contenedor 
para inyectar las dependencias a través de sus correspondientes setters y propiedades. Por ejemplo,

```php
use yii\base\BaseObject;

class Foo extends BaseObject
{
    public $bar;

    private $_qux;

    public function getQux()
    {
        return $this->_qux;
    }

    public function setQux(Qux $qux)
    {
        $this->_qux = $qux;
    }
}

$container->get('Foo', [], [
    'bar' => $container->get('Bar'),
    'qux' => $container->get('Qux'),
]);
```

### Inyección de Llamadas de retorno PHP <span id="php-callable-injection"></span>

En este caso, el contenedor usará una llamada de retorno PHP registrada para construir una nueva instancia de una 
clase. La llamada de retorno se responsabiliza de que dependencias debe inyectar al nuevo objeto creado. Por ejemplo,

```php
$container->set('Foo', function ($container, $params, $config) {
    return new Foo(new Bar);
});

$foo = $container->get('Foo');
```

Registro de dependencias <span id="registering-dependencies"></span>
------------------------

Se puede usar [[yii\di\Container::set()]] para registrar dependencias. El registro requiere un nombre de dependencia 
así como una definición de dependencia. Un nombre de dependencia puede ser un nombre de clase, un nombre de interfaz, 
o un nombre de alias; y una definición de dependencia puede ser un nombre de clase, un array de configuración, o una 
llamada de retorno PHP.

```php
$container = new \yii\di\Container;

// registra un nombre de clase como tal. Puede se omitido.
$container->set('yii\db\Connection');

// registra una interfaz
// Cuando una clase depende de una interfaz, la clase correspondiente
// se instanciará como un objeto dependiente
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// registra un nombre de alias. Se puede usar $container->get('foo')
// para crear una instancia de Connection
$container->set('foo', 'yii\db\Connection');

// registrar una clase con configuración. La configuración
// se aplicara cuando la clase se instancie por get()
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// registra un nombre de alias con configuración de clase
// En este caso, se requiere un elemento "clase" para especificar la clase
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// registra una llamada de retorno de PHP
// La llamada de retorno sera ejecutada cada vez que se ejecute $container->get('db') 
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});

// registra un componente instancia
// $container->get('pageCache') devolverá la misma instancia cada vez que se ejecute
$container->set('pageCache', new FileCache);
```

> Tip: Si un nombre de dependencia es el mismo que la definición de dependencia, no es necesario registrarlo con 
  el contenedor de ID.

Una dependencia registrada mediante `set()` generará una instancia cada vez que se necesite la dependencia. Se puede 
usar [[yii\di\Container::setSingleton()]] para registrar una dependencia que genere una única instancia:

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```

Resolución de Dependencias <span id="resolving-dependencies"></span>
--------------------------

Una ves se hayan registrado las dependencias, se puede usar el contenedor de ID para crear nuevos objetos, y el 
contenedor resolverá automáticamente las dependencias instanciándolas e inyectándolas dentro de los nuevos objetos 
creados. La resolución de dependencias es recursiva, esto significa que si una dependencia tiene otras dependencias, 
estas dependencias también se resolverán automáticamente.

Se puede usar [[yii\di\Container::get()]] para crear nuevos objetos. El método obtiene el nombre de dependencia, que 
puede ser un nombre de clase, un nombre de interfaz o un nombre de alias. El nombre de dependencia puede estar 
registrado o no mediante `set()` o `setSingleton()`. Se puede proporcionar opcionalmente un listado de los parámetros 
del constructor de clase y una [configuración](concept-configurations.md) para configurar los nuevos objetos creados. 
Por ejemplo,

```php
// "db" ha sido registrado anteriormente como nombre de alias
$db = $container->get('db');

// equivalente a: $engine = new \app\components\SearchEngine($apiKey, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey], ['type' => 1]);
```

Por detrás, el contenedor de ID efectúa mucho más trabajo la creación de un nuevo objeto. El contenedor primero 
inspeccionará la clase constructora para encontrar los nombres de clase o interfaces dependientes y después 
automáticamente resolverá estas dependencias recursivamente.

El siguiente código muestra un ejemplo más sofisticado. La clase `UserLister` depende del un objeto que implementa la 
interfaz `UserFinderInterface`; la clase `UserFinder` implementa la interfaz y depende del objeto `Connection`.  Todas 
estas dependencias se declaran a través de insinuaciones (hinting) de los parámetros del constructor de clase. Con el 
registro de dependencia de propiedades, el contenedor de ID puede resolver las dependencias automáticamente y crear 
una nueva instancia de `UserLister` con una simple llamada a `get('userLister')`.

```php
namespace app\models;

use yii\base\BaseObject;
use yii\db\Connection;
use yii\di\Container;

interface UserFinderInterface
{
    function findUser();
}

class UserFinder extends BaseObject implements UserFinderInterface
{
    public $db;

    public function __construct(Connection $db, $config = [])
    {
        $this->db = $db;
        parent::__construct($config);
    }

    public function findUser()
    {
    }
}

class UserLister extends BaseObject
{
    public $finder;

    public function __construct(UserFinderInterface $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }
}

$container = new Container;
$container->set('yii\db\Connection', [
    'dsn' => '...',
]);
$container->set('app\models\UserFinderInterface', [
    'class' => 'app\models\UserFinder',
]);
$container->set('userLister', 'app\models\UserLister');

$lister = $container->get('userLister');

// que es equivalente a:

$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```

Uso Practico <span id="practical-usage"></span>
------------

Yii crea un contenedor de ID cuando se incluye el archivo `Yii.php` en el 
[script de entrada](structure-entry-scripts.md) de la aplicación. Cuando se llama a [[Yii::createObject()]] el método 
realmente llama al contenedor del método [[yii\di\Container::get()|get()]] para crear un nuevo objeto. Como se ha 
comentado anteriormente, el contenedor de ID resolverá automáticamente las dependencias (si las hay) y las inyectará 
dentro del nuevo objeto creado. Debido a que Yii utiliza [[Yii::createObject()]] en la mayor parte del núcleo (core) 
para crear nuevo objetos, podemos personalizar los objetos globalmente para que puedan tratar con [[Yii::$container]].

Por ejemplo, se puede personalizar globalmenete el numero predeterminado de números de botones de paginación de 
[[yii\widgets\LinkPager]]:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

Ahora si se usa el widget en una vista con el siguiente código, la propiedad `maxButtonCount` será inicializada con 
valor 5 en lugar de 10 que es el valor predeterminado definido en la clase.

```php
echo \yii\widgets\LinkPager::widget();
```

Se puede sobrescribir el valor establecido mediante el contenedor de ID, como a continuación:

```php
echo \yii\widgets\LinkPager::widget(['maxButtonCount' => 20]);
```

Otro ejemplo es aprovechar la ventaja de la inyección automática de constructores de contenedores de ID. Asumiendo que 
la clase controlador depende de otros objetos, tales como un servicio de reservas de hotel. Se puede declarar una 
dependencia a través de un parámetro del constructor y permitir al contenedor de ID resolverla por nosotros.

```php
namespace app\controllers;

use yii\web\Controller;
use app\components\BookingInterface;

class HotelController extends Controller
{
    protected $bookingService;

    public function __construct($id, $module, BookingInterface $bookingService, $config = [])
    {
        $this->bookingService = $bookingService;
        parent::__construct($id, $module, $config);
    }
}
```

Si se accede al controlador desde el navegador, veremos un error advirtiendo que `BookingInterface` no puede ser 
instanciada. Esto se debe a que necesitamos indicar al contenedor de ID como tratar con esta dependencia:

```php
\Yii::$container->set('app\components\BookingInterface', 'app\components\BookingService');
```

Ahora si se accede al contenedor nuevamente, se creará una instancia de `app\components\BookingService` y se inyectará 
a como tercer parámetro al constructor del controlador.

Cuando Registrar Dependencias <span id="when-to-register-dependencies"></span>
-----------------------------

El registro de dependencias debe hacerse lo antes posible debido a que las dependencias se necesitan cuando se crean 
nuevos objetos. A continuación se listan practicas recomendadas:

* Siendo desarrolladores de una aplicación, podemos registrar dependencias en el 
  [script de entrada](structure-entry-scripts.md) o en un script incluido en el script de entrada.
* Siendo desarrolladores de una [extension](structure-extensions.md) redistribuible, podemos registrar dependencias en 
  la clase de boostraping de la extensión.

Resumen <span id="summary"></span>
-------

Tanto la inyección de dependencias como el [localizador de servicios](concept-service-locator.md) son patrones de 
diseño populares que permiten construir software con acoplamiento flexible y más fácil de testear. Se recomienda 
encarecida la lectura del articulo de [Martin](https://martinfowler.com/articles/injection.html) para obtener una mejor 
comprensión de la inyección de dependencias y de la localización de servicios.

Yii implementa su propio [localizador de servicios](concept-service-locator.md) por encima del contenedor de ID. 
Cuando un localizador de servicios intenta crear una nueva instancia de objeto, se desviará la llamada al contenedor 
de ID. Este último resolverá las dependencias automáticamente como se ha descrito anteriormente.
