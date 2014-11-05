路由
=======

当[入口脚本](structure-entry-scripts.md)在调用 [[yii\web\Application::run()|run()]] 
方法时，它进行的第一个操作就是解析输入的请求，然后实例化对应的[控制器操作](structure-controllers.md)处理这个请求。该过程就被称为**引导路由（routing）**。（译者注：中文里既是动词也是名词）


## 解析路由 <a name="resolving-route"></a>

引导路由第一步，是解析输入请求为一个路由。如 [控制器（Controllers）](structure-controllers.md#routes)
所描述的那样，路由是一个用于定位控制器操作的地址。这个过程通过 `request` 应用组件的 [[yii\web\Request::resolve()|resolve()]] 
方法实现，该方法会调用 [URL 管理器](runtime-url-handling.md) 进行实质上的请求解析工作。
 
The first step of routing is to parse the incoming request into a route which, as described in
the [Controllers](structure-controllers.md#routes) section, is used to address a controller action.
This is done by [[yii\web\Request::resolve()|resolve()]] method of the `request` application component.
The method invokes the [URL manager](runtime-url-handling.md) to do the actual request parsing work.

默认情况下，输入请求会包含一个名为 `r` 的 `GET` 参数，它的值即被视为路由。但是如果启用
[[yii\web\UrlManager::enablePrettyUrl|pretty URL feature]]，确定请求路由时则会进行更多处理。具体的细节请参考
[URL 的解析与生成](runtime-url-handling.md) 章节。

By default, if the incoming request contains a `GET` parameter named `r`, its value will be considered
as the route. However, if the [[yii\web\UrlManager::enablePrettyUrl|pretty URL feature]] is enabled,
more work will be done to determine the requested route. For more details, please refer to
the [URL Parsing and Generation](runtime-url-handling.md) section.

若好死不死地路由最终无法被确定，那么 `request` 组件会抛出 [[yii\web\NotFoundHttpException]] 异常（译者注：大名鼎鼎的 404）。

In case a route cannot be determined, the `request` component will throw a [[yii\web\NotFoundHttpException]].


### 默认路由 <a name="default-route"></a>

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


### `catchAll` 路由（全拦截路由） <a name="catchall-route"></a>

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


## 创建一个操作 <a name="creating-action"></a>

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
