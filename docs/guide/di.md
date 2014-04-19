Service Locator and Dependency Injection
========================================

Both service locator and dependency injection are design patterns that allow building software
in a loosely-coupled fashion. Yii uses service locator and dependency injection extensively,
even though you may not be aware of them. In this tutorial, we will explore their implementation
and support in Yii to help you write code more consciously. We also highly recommend you to
read [Martin's article](http://martinfowler.com/articles/injection.html) to get a deeper
understanding of SL and DI.


Service Locator
---------------

A service locator is an object that knows how to provide all sorts of services that an application
might need. The most commonly used service locator in Yii is the *application* object accessible through
`\Yii::$app`. It provides services under the name of *application components*. The following code
shows how you can obtain an application component (service) from the application object:

```php
$request = \Yii::$app->get('request');
// or alternatively
$request = \Yii::$app->request;
```

Behind the scene, the application object serves as a service locator because it extends from
the [[yii\di\ServiceLocator]] class.


Dependency Injection
--------------------
