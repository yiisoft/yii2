Контейнер Внедрения Зависимостей
==============================

Контейнер внедрения зависимостей - это объект, который знает, как создать и настроить экземпляр объекта и зависимых от него объектов.
[Статья Мартина Фаулера](http://martinfowler.com/articles/injection.html) хорошо объясняет, почему контейнер внедрения зависимостей является полезным. Здесь, преимущественно, будет объясняться использование контейнера внедрения зависимостей, предоставляемого в Yii.




Внедрение зависимостей <a name="dependency-injection"></a>
--------------------

Yii обеспечивает функционал контейнера внедрения зависимостей через класс [[yii\di\Container]]. Он поддерживает следующие виды внедрения зависимостей:

* Внедрение зависимости через конструктор.
* Внедрение зависимости через сеттер и свойство.
* Внедрение зависимости через PHP callback.


### Внедрение зависимости через конструктор <a name="constructor-injection"></a>

Контейнер внедрения зависимостей поддерживает внедрение зависимости через конструктор при помощи указания типов для параметров конструктора.
Указанные типы сообщают контейнеру, какие классы или интерфейсы зависят от него при создании нового объекта.
Контейнер попытается получить экземпляры зависимых классов или интерфейсов, а затем передать их в новый объект через конструктор. Например,

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// что равносильно следующему:
$bar = new Bar;
$foo = new Foo($bar);
```


### Внедрение зависимости через сеттер и свойство <a name="setter-and-property-injection"></a>

Внедрение зависимости через сеттер и свойство поддерживается через [конфигурации](concept-configurations.md).
При регистрации зависимости или при создании нового объекта, вы можете предоставить конфигурацию, которая
будет использована контейнером для внедрения зависимостей через соответствующие сеттеры или свойства.
Например,

```php
use yii\base\Object;

class Foo extends Object
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


### Внедрение зависимости через PHP callback <a name="php-callable-injection"></a>

В данном случае, контейнер будет использовать зарегистрированный PHP callback для создания новых экземпляров класса.
Callback отвечает за разрешения зависимостей и внедряет их в соответствии с вновь создаваемыми объектами. Например,

```php
$container->set('Foo', function () {
    return new Foo(new Bar);
});

$foo = $container->get('Foo');
```


Регистрация зависимостей <a name="registering-dependencies"></a>
------------------------

Вы можете использовать [[yii\di\Container::set()]] для регистрации зависимостей. При регистрации требуется имя зависимости, а так же определение зависимости. 
Именем звисимости может быть имя класса, интерфейса или алиас, так же определением зависимости может быть имя класса, конфигурационным массивом, или PHP calback'ом.

```php
$container = new \yii\di\Container;

// регистрация имени класса, как есть. это может быть пропущено.
$container->set('yii\db\Connection');

// регистраци интерфейса
// Когда класс зависит от интерфейса, соответствующий класс
// будет использован в качестве зависимости объекта
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// регистрация алиаса. Вы можете использовать $container->get('foo')
// для создания экземпляра Connection
$container->set('foo', 'yii\db\Connection');

// Регистрация класса с конфигурацией. Конфигурация
// будет применена при создании экземпляра класса через get()
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// регистрация алиаса с конфигурацией класса
// В данном случае, параметр "class" требуется для указания класса
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// регистрация PHP callback'a
// Callback будет выполняться каждый раз при вызове $container->get('db')
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});

// регистрация экземпляра компонента
// $container->get('pageCache') вернёт тот же экземпляр при каждом вызове
$container->set('pageCache', new FileCache);
```

> Подсказка: Если имя зависимости такое же, как и определение соответствующей зависимости, то её повторная регистрация в контейнере внедрения зависимостей не нужна.

Зависимость, зарегистрированная через `set()` создаёт экземпляр каждый раз, когда зависимость необходима.
Вы можете использовать [[yii\di\Container::setSingleton()]] для регистрации зависимости, которая создаст только один экземпляр:

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```


Resolving Dependencies <a name="resolving-dependencies"></a>
----------------------
После регистрации зависимостей, вы можете использовать контейнер внедрения зависимостей (DI) для создания новых объектов,
и контейнер автоматически разрешит зависимости их экземпляра и их внедрений во вновь создаваемых объектах. Разрешение зависимостей рекурсивно, то есть
если зависимость имеет другие зависимости, эти зависимости также будут автоматически разрешены.

Вы можете использовать [[yii\di\Container::get()]] для создания новых объектов. Метод принимает имя зависимости, которым может быть имя класса, имя интерфейса или псевдоним.
Имя зависимости может быть или не может быть зарегистрирована через `set()` или `setSingleton()`. 
Вы можете опционально предоставить список параметров конструктора класса и [конфигурацию](concept-configurations.md) для настройки созданного объекта.
Например,

```php
// "db" ранее зарегистрированный псевдоним
$db = $container->get('db');

// эквивалентно: $engine = new \app\components\SearchEngine($apiKey, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey], ['type' => 1]);
```

Behind the scene, the DI container does much more work than just creating a new object.
The container will first inspect the class constructor to find out dependent class or interface names
and then automatically resolve those dependencies recursively.

The following code shows a more sophisticated example. The `UserLister` class depends on an object implementing
the `UserFinderInterface` interface; the `UserFinder` class implements this interface and depends on
a `Connection` object. All these dependencies are declared through type hinting of the class constructor parameters.
With property dependency registration, the DI container is able to resolve these dependencies automatically
and creates a new `UserLister` instance with a simple call of `get('userLister')`.

```php
namespace app\models;

use yii\base\Object;
use yii\db\Connection;
use yii\di\Container;

interface UserFinderInterface
{
    function findUser();
}

class UserFinder extends Object implements UserFinderInterface
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

class UserLister extends Object
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

// which is equivalent to:

$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```


Практическое использование <a name="practical-usage"></a>
---------------

Yii creates a DI container when you include the `Yii.php` file in the [entry script](structure-entry-scripts.md)
of your application. The DI container is accessible via [[Yii::$container]]. When you call [[Yii::createObject()]],
the method will actually call the container's [[yii\di\Container::get()|get()]] method to create a new object.
As aforementioned, the DI container will automatically resolve the dependencies (if any) and inject them
into the newly created object. Because Yii uses [[Yii::createObject()]] in most of its core code to create
new objects, this means you can customize the objects globally by dealing with [[Yii::$container]].

For example, you can customize globally the default number of pagination buttons of [[yii\widgets\LinkPager]]:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

Now if you use the widget in a view with the following code, the `maxButtonCount` property will be initialized
as 5 instead of the default value 10 as defined in the class.

```php
echo \yii\widgets\LinkPager::widget();
```

You can still override the value set via DI container, though:

```php
echo \yii\widgets\LinkPager::widget(['maxButtonCount' => 20]);
```

Another example is to take advantage of the automatic constructor injection of the DI container.
Assume your controller class depends on some other objects, such as a hotel booking service. You
can declare the dependency through a constructor parameter and let the DI container to resolve it for you.

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

If you access this controller from browser, you will see an error complaining the `BookingInterface`
cannot be instantiated. This is because you need to tell the DI container how to deal with this dependency:

```php
\Yii::$container->set('app\components\BookingInterface', 'app\components\BookingService');
```

Now if you access the controller again, an instance of `app\components\BookingService` will be
created and injected as the 3rd parameter to the controller's constructor.


When to Register Dependencies <a name="when-to-register-dependencies"></a>
-----------------------------

Because dependencies are needed when new objects are being created, their registration should be done
as early as possible. The followings are the recommended practices:

* If you are the developer of an application, you can register dependencies in your
  application's [entry script](structure-entry-scripts.md) or in a script that is included by the entry script.
* If you are the developer of a redistributable [extension](structure-extensions.md), you can register dependencies
  in the bootstrap class of the extension.


Summary <a name="summary"></a>
-------

Both dependency injection and [service locator](concept-service-locator.md) are popular design patterns
that allow building software in a loosely-coupled and more testable fashion. We highly recommend you to read
[Martin's article](http://martinfowler.com/articles/injection.html) to get a deeper understanding of
dependency injection and service locator.

Yii implements its [service locator](concept-service-locator.md) on top of the dependency injection (DI) container.
When a service locator is trying to create a new object instance, it will forward the call to the DI container.
The latter will resolve the dependencies automatically as described above.

