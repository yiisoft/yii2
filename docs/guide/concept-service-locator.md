Service Locator
===============

A service locator is an object that knows how to provide all sorts of services (or components) that an application
might need. Within a service locator, each component exists as only a single instance, uniquely identified by an ID.
You use the ID to retrieve a component from the service locator.

In Yii, a service locator is simply an instance of [[yii\di\ServiceLocator]] or a child class.

The most commonly used service locator in Yii is the *application* object, which can be accessed through
`\Yii::$app`. The services it provides are called *application components*, such as the `request`, `response`, and
`urlManager` components. You may configure these components, or even replace them with your own implementations, easily
through functionality provided by the service locator.

Besides the application object, each module object is also a service locator. Modules implement [tree traversal](#tree-traversal).

To use a service locator, the first step is to register components with it. A component can be registered
via [[yii\di\ServiceLocator::set()]]. The following code shows different ways of registering components:

```php
use yii\di\ServiceLocator;
use yii\caching\FileCache;

$locator = new ServiceLocator;

// register "cache" using a class name that can be used to create a component
$locator->set('cache', 'yii\caching\ApcCache');

// register "db" using a configuration array that can be used to create a component
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// register "search" using an anonymous function that builds a component
$locator->set('search', function () {
    return new app\components\SolrService;
});

// register "pageCache" using a component
$locator->set('pageCache', new FileCache);
```

Once a component has been registered, you can access it using its ID, in one of the two following ways:

```php
$cache = $locator->get('cache');
// or alternatively
$cache = $locator->cache;
```

As shown above, [[yii\di\ServiceLocator]] allows you to access a component like a property using the component ID.
When you access a component for the first time, [[yii\di\ServiceLocator]] will use the component registration
information to create a new instance of the component and return it. Later, if the component is accessed again,
the service locator will return the same instance.

You may use [[yii\di\ServiceLocator::has()]] to check if a component ID has already been registered.
If you call [[yii\di\ServiceLocator::get()]] with an invalid ID, an exception will be thrown.


Because service locators are often being created with [configurations](concept-configurations.md),
a writable property named [[yii\di\ServiceLocator::setComponents()|components]] is provided. This allows you 
to configure and register multiple components at once. The following code shows a configuration array
that can be used to configure a service locator (e.g. an [application](structure-applications.md)) with 
the `db`, `cache`, `tz` and `search` components:

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
        'tz' => function() {
            return new \DateTimeZone(Yii::$app->formatter->defaultTimeZone);
        },
        'search' => function () {
            $solr = new app\components\SolrService('127.0.0.1');
            // ... other initializations ...
            return $solr;
        },
    ],
];
```

In the above, there is an alternative way to configure the `search` component. Instead of directly writing a PHP
callback which builds a `SolrService` instance, you can use a static class method to return such a callback, like
shown as below:

```php
class SolrServiceBuilder
{
    public static function build($ip)
    {
        return function () use ($ip) {
            $solr = new app\components\SolrService($ip);
            // ... other initializations ...
            return $solr;
        };
    }
}

return [
    // ...
    'components' => [
        // ...
        'search' => SolrServiceBuilder::build('127.0.0.1'),
    ],
];
```

This alternative approach is most preferable when you are releasing a Yii component which encapsulates some non-Yii
3rd-party library. You use the static method like shown above to represent the complex logic of building the
3rd-party object, and the user of your component only needs to call the static method to configure the component.

## Tree traversal <span id="tree-traversal"></span>

Modules allow arbitrary nesting; a Yii application is essentially a tree of modules.
Since each of these modules is a service locator it makes sense for children to have access to their parent.
This allows modules to use `$this->get('db')` instead of referencing the root service locator `Yii::$app->get('db')`.
Added benefit is the option for a developer to override configuration in a module.

Any request for a service to be retrieved from a module will be passed on to its parent in case the module is not able to satisfy it.

Note that configuration from components in a module is never merged with configuration from a component in a parent module. The Service Locator pattern allows us to define named services but one cannot assume services with the same name use the same configuration parameters.
