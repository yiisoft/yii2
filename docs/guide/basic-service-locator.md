Service Locator
===============

> Note: This chapter needs cleanup.

Both service locator and dependency injection are popular design patterns that allow building software
in a loosely-coupled fashion. Yii uses service locator and dependency injection extensively,
even though you may not be aware of them. In this tutorial, we will explore their implementation
and support to help you write code more consciously. We also highly recommend you to read
[Martin's article](http://martinfowler.com/articles/injection.html) to get a deeper understanding of
service locator and dependency injection.

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
