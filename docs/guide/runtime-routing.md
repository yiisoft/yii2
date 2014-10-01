Routing
=======

When the [[yii\web\Application::run()|run()]] method is called by the [entry script](structure-entry-scripts.md),
the first thing it does is to resolve the incoming request and instantiate an appropriate
[controller action](structure-controllers.md) to handle the request. This process is called *routing*.


## Resolving Route <a name="resolving-route"></a>

The first step of routing is to parse the incoming request into a route which, as described in
the [Controllers](structure-controllers.md#routes) section, is used to address a controller action.
This is done by [[yii\web\Request::resolve()|resolve()]] method of the `request` application component.
The method invokes the [URL manager](runtime-url-handling.md) to do the actual request parsing work.

By default, if the incoming request contains a `GET` parameter named `r`, its value will be considered
as the route. However, if the [[yii\web\UrlManager::enablePrettyUrl|pretty URL feature]] is enabled,
more work will be done to determine the requested route. For more details, please refer to
the [URL Parsing and Generation](runtime-url-handling.md) section.

In case a route cannot be determined, the `request` component will throw a [[yii\web\NotFoundHttpException]].


### Default Route <a name="default-route"></a>

If an incoming request does not specify a route, which often happens to the request for homepages,
the route specified by [[yii\web\Application::defaultRoute]] will be used. The default value of this property
is `site/index`, which refers to the `index` action of the `site` controller. You may customize this property
in the application configuration like the following:

```php
return [
    // ...
    'defaultRoute' => 'main/index',
];
```


### `catchAll` Route <a name="catchall-route"></a>

Sometimes, you may want to put your Web application in maintenance mode temporarily and display the same
informational page for all requests. There are many ways to accomplish this goal. But one of the simplest
ways is to configure the [[yii\web\Application::catchAll]] property like the following in the application configuration:

```php
return [
    // ...
    'catchAll' => ['site/offline'],
];
```

The `catchAll` property should take an array whose first element specifies a route, and
the rest of the elements (name-value pairs) specify the parameters to be bound to the action.

When the `catchAll` property is set, it will replace any route resolved from the incoming requests.
With the above configuration, the same `site/offline` action will be used to handle all incoming requests.


## Creating Action <a name="creating-action"></a>

Once the requested route is determined, the next step is to create the action object corresponding to the route.

The route is broken down into multiple parts by the slashes in it. For example, `site/index` will be
broken into `site` and `index`. Each part is an ID which may refer to a module, a controller or an action.

Starting from the first part in the route, the application conducts the following steps to create modules (if any),
the controller and the action:

1. Set the application as the current module.
2. Check if the [[yii\base\Module::controllerMap|controller map]] of the current module contains the current ID.
   If so, a controller object will be created according to the controller configuration found in the map,
   and do Step 5 with the rest parts of the route.
3. Check if the ID refers to a module listed in the [[yii\base\Module::modules|modules]] property of
   the current module. If so, a module is created according to the configuration found in the module list,
   and do Step 2 with the next part in the route under the context of the newly created module.
4. Treat the ID as a controller ID and create a controller object. Do the next step with the rest part of
   the route.
5. The controller looks for the current ID in its [[yii\base\Controller::actions()|action map]]. If found,
   it creates an action according to the configuration found in the map. Otherwise, the controller will
   attempt to create an inline action which is defined by an action method corresponding to the current ID.

Among the above steps, if any error occurs, a [[yii\web\NotFoundHttpException]] will be thrown, indicating
failure of the routing.
