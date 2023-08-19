Dependency Injection Container
==============================

A dependency injection (DI) container is an object that knows how to instantiate and configure objects and
all their dependent objects. [Martin Fowler's article](https://martinfowler.com/articles/injection.html) has well
explained why DI container is useful. Here we will mainly explain the usage of the DI container provided by Yii.


Dependency Injection <span id="dependency-injection"></span>
--------------------

Yii provides the DI container feature through the class [[yii\di\Container]]. It supports the following kinds of
dependency injection:

* Constructor injection;
* Method injection;
* Setter and property injection;
* PHP callable injection;


### Constructor Injection <span id="constructor-injection"></span>

The DI container supports constructor injection with the help of type hints for constructor parameters.
The type hints tell the container which classes or interfaces are dependent when it is used to create a new object.
The container will try to get the instances of the dependent classes or interfaces and then inject them
into the new object through the constructor. For example,

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// which is equivalent to the following:
$bar = new Bar;
$foo = new Foo($bar);
```


### Method Injection <span id="method-injection"></span>

Usually the dependencies of a class are passed to the constructor and are available inside the class during the whole lifecycle.
With Method Injection it is possible to provide a dependency that is only needed by a single method of the class
and passing it to the constructor may not be possible or may cause too much overhead in the majority of use cases.

A class method can be defined like the `doSomething()` method in the following example:

```php
class MyClass extends \yii\base\Component
{
    public function __construct(/*Some lightweight dependencies here*/, $config = [])
    {
        // ...
    }

    public function doSomething($param1, \my\heavy\Dependency $something)
    {
        // do something with $something
    }
}
```

You may call that method either by passing an instance of `\my\heavy\Dependency` yourself or using [[yii\di\Container::invoke()]] like the following:

```php
$obj = new MyClass(/*...*/);
Yii::$container->invoke([$obj, 'doSomething'], ['param1' => 42]); // $something will be provided by the DI container
```

### Setter and Property Injection <span id="setter-and-property-injection"></span>

Setter and property injection is supported through [configurations](concept-configurations.md).
When registering a dependency or when creating a new object, you can provide a configuration which
will be used by the container to inject the dependencies through the corresponding setters or properties.
For example,

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

> Info: The [[yii\di\Container::get()]] method takes its third parameter as a configuration array that should
  be applied to the object being created. If the class implements the [[yii\base\Configurable]] interface (e.g.
  [[yii\base\BaseObject]]), the configuration array will be passed as the last parameter to the class constructor;
  otherwise, the configuration will be applied *after* the object is created.


### PHP Callable Injection <span id="php-callable-injection"></span>

In this case, the container will use a registered PHP callable to build new instances of a class.
Each time when [[yii\di\Container::get()]] is called, the corresponding callable will be invoked.
The callable is responsible to resolve the dependencies and inject them appropriately to the newly
created objects. For example,

```php
$container->set('Foo', function ($container, $params, $config) {
    $foo = new Foo(new Bar);
    // ... other initializations ...
    return $foo;
});

$foo = $container->get('Foo');
```

To hide the complex logic for building a new object, you may use a static class method as callable. For example,

```php
class FooBuilder
{
    public static function build($container, $params, $config)
    {
        $foo = new Foo(new Bar);
        // ... other initializations ...
        return $foo;
    }
}

$container->set('Foo', ['app\helper\FooBuilder', 'build']);

$foo = $container->get('Foo');
```

By doing so, the person who wants to configure the `Foo` class no longer needs to be aware of how it is built.


Registering Dependencies <span id="registering-dependencies"></span>
------------------------

You can use [[yii\di\Container::set()]] to register dependencies. The registration requires a dependency name
as well as a dependency definition. A dependency name can be a class name, an interface name, or an alias name;
and a dependency definition can be a class name, a configuration array, or a PHP callable.

```php
$container = new \yii\di\Container;

// register a class name as is. This can be skipped.
$container->set('yii\db\Connection');

// register an interface
// When a class depends on the interface, the corresponding class
// will be instantiated as the dependent object
$container->set('yii\mail\MailInterface', 'yii\symfonymailer\Mailer');

// register an alias name. You can use $container->get('foo')
// to create an instance of Connection
$container->set('foo', 'yii\db\Connection');

// register an alias with `Instance::of`
$container->set('bar', Instance::of('foo'));

// register a class with configuration. The configuration
// will be applied when the class is instantiated by get()
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// register an alias name with class configuration
// In this case, a "class" or "__class" element is required to specify the class
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// register callable closure or array
// The callable will be executed each time when $container->get('db') is called
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});
$container->set('db', ['app\db\DbFactory', 'create']);

// register a component instance
// $container->get('pageCache') will return the same instance each time it is called
$container->set('pageCache', new FileCache);
```

> Tip: If a dependency name is the same as the corresponding dependency definition, you do not
  need to register it with the DI container.

A dependency registered via `set()` will generate an instance each time the dependency is needed.
You can use [[yii\di\Container::setSingleton()]] to register a dependency that only generates
a single instance:

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```

Resolving Dependencies <span id="resolving-dependencies"></span>
----------------------

Once you have registered dependencies, you can use the DI container to create new objects,
and the container will automatically resolve dependencies by instantiating them and injecting
them into the newly created objects. The dependency resolution is recursive, meaning that
if a dependency has other dependencies, those dependencies will also be resolved automatically.

You can use [[yii\di\Container::get()|get()]] to either create or get object instance.
The method takes a dependency name, which can be a class name, an interface name or an alias name. 
The dependency name may be registered via [[yii\di\Container::set()|set()]] 
or [[yii\di\Container::setSingleton()|setSingleton()]]. You may optionally provide a list of class 
constructor parameters and a [configuration](concept-configurations.md) to configure the newly created object. 

For example:

```php
// "db" is a previously registered alias name
$db = $container->get('db');

// equivalent to: $engine = new \app\components\SearchEngine($apiKey, $apiSecret, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey, $apiSecret], ['type' => 1]);

// equivalent to: $api = new \app\components\Api($host, $apiKey);
$api = $container->get('app\components\Api', ['host' => $host, 'apiKey' => $apiKey]);
```

Behind the scene, the DI container does much more work than just creating a new object.
The container will first inspect the class constructor to find out dependent class or interface names
and then automatically resolve those dependencies recursively.

The following code shows a more sophisticated example. The `UserLister` class depends on an object implementing
the `UserFinderInterface` interface; the `UserFinder` class implements this interface and depends on
a `Connection` object. All these dependencies are declared through type hinting of the class constructor parameters.
With proper dependency registration, the DI container is able to resolve these dependencies automatically
and creates a new `UserLister` instance with a simple call of `get('userLister')`.

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

// which is equivalent to:

$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```


Practical Usage <span id="practical-usage"></span>
---------------

Yii creates a DI container when you include the `Yii.php` file in the [entry script](structure-entry-scripts.md)
of your application. The DI container is accessible via [[Yii::$container]]. When you call [[Yii::createObject()]],
the method will actually call the container's [[yii\di\Container::get()|get()]] method to create a new object.
As aforementioned, the DI container will automatically resolve the dependencies (if any) and inject them
into obtained object. Because Yii uses [[Yii::createObject()]] in most of its core code to create
new objects, this means you can customize the objects globally by dealing with [[Yii::$container]].

For example, let's customize globally the default number of pagination buttons of [[yii\widgets\LinkPager]]. 

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

> Note: Properties given in the widget call will always override the definition in the DI container.
> Even if you specify an array, e.g. `'options' => ['id' => 'mypager']` these will not be merged
> with other options but replace them.

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

Since Yii 2.0.36 when using PHP 7 action injection is available for both web and console controllers:

```php
namespace app\controllers;

use yii\web\Controller;
use app\components\BookingInterface;

class HotelController extends Controller
{    
    public function actionBook($id, BookingInterface $bookingService)
    {
        $result = $bookingService->book($id);
        // ...    
    }
}
``` 

Advanced Practical Usage <span id="advanced-practical-usage"></span>
---------------

Say we work on API application and have:

- `app\components\Request` class that extends `yii\web\Request` and provides additional functionality
- `app\components\Response` class that extends `yii\web\Response` and should have `format` property 
  set to `json` on creation
- `app\storage\FileStorage` and `app\storage\DocumentsReader` classes that implement some logic on
  working with documents that are located in some file storage:
  
  ```php
  class FileStorage
  {
      public function __construct($root) {
          // whatever
      }
  }
  
  class DocumentsReader
  {
      public function __construct(FileStorage $fs) {
          // whatever
      }
  }
  ```

It is possible to configure multiple definitions at once, passing configuration array to
[[yii\di\Container::setDefinitions()|setDefinitions()]] or [[yii\di\Container::setSingletons()|setSingletons()]] method.
Iterating over the configuration array, the methods will call [[yii\di\Container::set()|set()]]
or [[yii\di\Container::setSingleton()|setSingleton()]] respectively for each item.

The configuration array format is:

 - `key`: class name, interface name or alias name. The key will be passed to the
 [[yii\di\Container::set()|set()]] method as a first argument `$class`.
 - `value`: the definition associated with `$class`. Possible values are described in [[yii\di\Container::set()|set()]]
 documentation for the `$definition` parameter. Will be passed to the [[set()]] method as
 the second argument `$definition`.

For example, let's configure our container to follow the aforementioned requirements:

```php
$container->setDefinitions([
    'yii\web\Request' => 'app\components\Request',
    'yii\web\Response' => [
        'class' => 'app\components\Response',
        'format' => 'json'
    ],
    'app\storage\DocumentsReader' => function ($container, $params, $config) {
        $fs = new app\storage\FileStorage('/var/tempfiles');
        return new app\storage\DocumentsReader($fs);
    }
]);

$reader = $container->get('app\storage\DocumentsReader'); 
// Will create DocumentReader object with its dependencies as described in the config 
```

> Tip: Container may be configured in declarative style using application configuration since version 2.0.11. 
Check out the [Application Configurations](concept-configurations.md#application-configurations) subsection of
the [Configurations](concept-configurations.md) guide article.

Everything works, but in case we need to create `DocumentWriter` class, 
we shall copy-paste the line that creates `FileStorage` object, that is not the smartest way, obviously.

As described in the [Resolving Dependencies](#resolving-dependencies) subsection, [[yii\di\Container::set()|set()]]
and [[yii\di\Container::setSingleton()|setSingleton()]] can optionally take dependency's constructor parameters as
a third argument. To set the constructor parameters, you may use the `__construct()` option:

Let's modify our example:

```php
$container->setDefinitions([
    'tempFileStorage' => [ // we've created an alias for convenience
        'class' => 'app\storage\FileStorage',
        '__construct()' => ['/var/tempfiles'], // could be extracted from some config files
    ],
    'app\storage\DocumentsReader' => [
        'class' => 'app\storage\DocumentsReader',
        '__construct()' => [Instance::of('tempFileStorage')],
    ],
    'app\storage\DocumentsWriter' => [
        'class' => 'app\storage\DocumentsWriter',
        '__construct()' => [Instance::of('tempFileStorage')]
    ]
]);

$reader = $container->get('app\storage\DocumentsReader'); 
// Will behave exactly the same as in the previous example.
```

You might notice `Instance::of('tempFileStorage')` notation. It means, that the [[yii\di\Container|Container]]
will implicitly provide a dependency registered with the name of `tempFileStorage` and pass it as the first argument 
of `app\storage\DocumentsWriter` constructor.

> Note: [[yii\di\Container::setDefinitions()|setDefinitions()]] and [[yii\di\Container::setSingletons()|setSingletons()]]
  methods are available since version 2.0.11.
  
Another step on configuration optimization is to register some dependencies as singletons. 
A dependency registered via [[yii\di\Container::set()|set()]] will be instantiated each time it is needed.
Some classes do not change the state during runtime, therefore they may be registered as singletons
in order to increase the application performance. 

A good example could be `app\storage\FileStorage` class, that executes some operations on file system with a simple 
API (e.g. `$fs->read()`, `$fs->write()`). These operations do not change the internal class state, so we can
create its instance once and use it multiple times.

```php
$container->setSingletons([
    'tempFileStorage' => [
        'class' => 'app\storage\FileStorage',
        '__construct()' => ['/var/tempfiles']
    ],
]);

$container->setDefinitions([
    'app\storage\DocumentsReader' => [
        'class' => 'app\storage\DocumentsReader',
        '__construct()' => [Instance::of('tempFileStorage')],
    ],
    'app\storage\DocumentsWriter' => [
        'class' => 'app\storage\DocumentsWriter',
        '__construct()' => [Instance::of('tempFileStorage')],
    ]
]);

$reader = $container->get('app\storage\DocumentsReader');
```

When to Register Dependencies <span id="when-to-register-dependencies"></span>
-----------------------------

Because dependencies are needed when new objects are being created, their registration should be done
as early as possible. The following are the recommended practices:

* If you are the developer of an application, you can register your dependencies using application configuration.
  Please, read the [Application Configurations](concept-configurations.md#application-configurations) subsection of 
  the [Configurations](concept-configurations.md) guide article.
* If you are the developer of a redistributable [extension](structure-extensions.md), you can register dependencies
  in the bootstrapping class of the extension.


Summary <span id="summary"></span>
-------

Both dependency injection and [service locator](concept-service-locator.md) are popular design patterns
that allow building software in a loosely-coupled and more testable fashion. We highly recommend you to read
[Martin's article](https://martinfowler.com/articles/injection.html) to get a deeper understanding of
dependency injection and service locator.

Yii implements its [service locator](concept-service-locator.md) on top of the dependency injection (DI) container.
When a service locator is trying to create a new object instance, it will forward the call to the DI container.
The latter will resolve the dependencies automatically as described above.

