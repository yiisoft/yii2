Service Locator and Dependency Injection
========================================

Both service locator and dependency injection are popular design patterns that allow building software
in a loosely-coupled fashion. Yii uses service locator and dependency injection extensively,
even though you may not be aware of them. In this tutorial, we will explore their implementation
and support to help you write code more consciously. We also highly recommend you to read
[Martin's article](http://martinfowler.com/articles/injection.html) to get a deeper understanding of
service locator and dependency injection.


Service Locator
---------------

A service locator is an object that knows how to provide all sorts of services (or components) that an application
might need. Within a service locator, each component has only a single instance which is uniquely identified by an ID.
You use the ID to retrieve a component from the service locator. In Yii, a service locator is simply an instance 
of [[yii\di\ServiceLocator]] or its child class.

The most commonly used service locator in Yii is the *application* object which can be accessed through
`\Yii::$app`. The services it provides are called *application components*, such as `request`, `response`,
`urlManager`. You may configure these components or replace them with your own implementations easily
through functionality provided the service locator.

Besides the application object, each module object is also a service locator.

To use a service locator, the first step is to register components. A component can be registered
via [[yii\di\ServiceLocator::set()]]. The following code shows different ways of registering components:

```php
$locator = new \yii\di\ServiceLocator;

// register "cache" using a class name that can be used to create a component
$locator->set('cache', 'yii\caching\ApcCache');

// register "db" using a configuration array that can be used to create a component
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// register "db" using an anonymous function that builds a component
$locator->set('search', function () {
    return new app\components\SolrService;
});
```

Once a component is registered, you can access it using its ID in one of the following two ways:

```php
$cache = $locator->get('cache');
// or alternatively
$cache = $locator->cache;
```

As shown above, [[yii\di\ServiceLocator]] allows you to access a component like a property using the component ID.
When you access a component for the first time, [[yii\di\ServiceLocator]] will use the component registration
information to create a new instance of the component and return it. Later if the component is accessed again,
the service locator will return the same instance.

You may use [[yii\di\ServiceLocator::has()]] to check if a component ID has already been registered.
If you call [[yii\di\ServiceLocator::get()]] with an invalid ID, an exception will be thrown.


Because service locators are often being configured using configuration arrays, a method named
[[yii\di\ServiceLocator::setComponents()]] is provided to allow registering components in configuration arrays.
The method is a setter which defines a writable property `components` that can be configured.
The following code shows a configuration array that can be used to configure an application and register
the "db", "cache" and "search" components:

```php
return [
    // ...
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],
        'cache' => 'yii\caching\ApcCache',
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
];
```


Dependency Injection
--------------------

A dependency injection (DI) container is an object that knows how to instantiate and configure objects and
all their dependent objects. [Martin's article](http://martinfowler.com/articles/injection.html) has well
explained why DI container is useful. Here we will mainly explain the usage of the DI container provided by Yii.

Yii provides the DI container feature through the class [[yii\di\Container]]. It supports the following kinds of
dependency injection:

* Constructor injection;
* Setter injection;
* PHP callable injection.


### Registering Dependencies

You can use [[yii\di\Container::set()]] to register dependencies. The registration requires a dependency name
as well as a dependency definition. The name can be a class name, an interface name, or an alias name;
and the definition can be a class name, a configuration array, or a PHP callable.

```php
$container = new \yii\di\Container;

// register a class name as is. This can be skipped.
$container->set('yii\db\Connection');

// register an interface
// When a class depends on the interface, the corresponding class
// will be instantiated as the dependent object
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// register an alias name. You can use $container->get('foo')
// to create an instance of Connection
$container->set('foo', 'yii\db\Connection');

// register a class with configuration. The configuration
// will be applied when the class is instantiated by get()
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// register an alias name with class configuration
// In this case, a "class" element is required to specify the class
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// register a PHP callable
// The callable will be executed when $container->get('db') is called
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});
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


### Resolving Dependencies

Once you have registered dependencies, you can use the DI container to create new objects,
and the container will automatically resolve dependencies by instantiating them and injecting
them into the newly created objects. The dependency resolution is recursive, meaning that
if a dependency has other dependencies, those dependencies will also be resolved automatically.

You use [[yii\di\Container::get()]] to create new objects. The method takes a class name or
a dependency name (class name, interface name or alias name) that you previously registered
via `set()` or `setSingleton()`. You may optionally provide a list of class constructor parameters
and a list of name-value pairs to configure the newly created object. For example,

```php
// equivalent to: $map = new \app\components\GoogleMap($apiKey);
$map = $container->get('app\components\GoogleMap', [$apiKey]);

// "db" is a previously registered alias name
$db = $container->get('db');
```

Behind the scene, the DI container does much more work than just creating a new object.
The container will inspect the class constructor to find out dependent class or interface names
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


### Practical Usage

Yii creates a DI container when you include the `yii.php` file in your application's entry script.
The DI container is accessible via [[Yii::$container]]. When you call [[Yii::createObject()]], the method
will actually call the container's [[yii\di\Container::get()|get()]] method to create a new object.
As aforementioned, the DI container will automatically resolve the dependencies (if any) and inject them
into the newly created object. Because Yii uses [[Yii::createObject()]] in most of its core code to create
new objects, this means you can customize the objects globally by dealing with [[Yii::$container]].

For example, you can customize globally the default number of pagination buttons of [[yii\widgets\LinkPager]]:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

Now if you use the widget in a view with the following code, the `maxButtonCount` property will be initialized
as 5 instead of 10 as defined in the class.

```php
echo \yii\widgets\LinkPager::widget();
```

You can still override the value set via DI container:

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


### When to Register Dependencies

Because dependencies are needed when new objects are being created, their registration should be done
as early as possible. The followings are the recommended practices:

* If you are the developer of an application, you can register dependencies in your
  application's entry script or in a script that is included by the entry script.
* If you are the developer of a redistributable extension, you can register dependencies
  in the bootstrap class of the extension.

