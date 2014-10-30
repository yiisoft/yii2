路由
=======

当[入口脚本](structure-entry-scripts.md)在调用 [[yii\web\Application::run()|run()]] 
方法时，它进行的第一个操作就是解析输入的请求，然后实例化对应的[控制器操作](structure-controllers.md)处理这个请求。该过程就被称为**引导路由（routing）**。（译注：中文里既是动词也是名词）


## 解析路由 <a name="resolving-route"></a>

引导路由第一步，是解析传入请求为一个路由。如 [控制器（Controllers）](structure-controllers.md#routes)
所描述的那样，路由是一个用于定位控制器操作的地址。这个过程通过 `request` 应用组件的 [[yii\web\Request::resolve()|resolve()]] 
方法实现，该方法会调用 [URL 管理器](runtime-url-handling.md) 进行实质上的请求解析工作。
 
The first step of routing is to parse the incoming request into a route which, as described in
the [Controllers](structure-controllers.md#routes) section, is used to address a controller action.
This is done by [[yii\web\Request::resolve()|resolve()]] method of the `request` application component.
The method invokes the [URL manager](runtime-url-handling.md) to do the actual request parsing work.

默认情况下，传入请求会包含一个名为 `r` 的 `GET` 参数，它的值即被视为路由。但是如果启用
[[yii\web\UrlManager::enablePrettyUrl|pretty URL feature]]，确定请求路由时则会进行更多处理。具体的细节请参考
[URL 的解析与生成](runtime-url-handling.md) 章节。

By default, if the incoming request contains a `GET` parameter named `r`, its value will be considered
as the route. However, if the [[yii\web\UrlManager::enablePrettyUrl|pretty URL feature]] is enabled,
more work will be done to determine the requested route. For more details, please refer to
the [URL Parsing and Generation](runtime-url-handling.md) section.

若好死不死地路由最终无法被确定，那么 `request` 组件会抛出 [[yii\web\NotFoundHttpException]] 异常（译注：大名鼎鼎的 404）。

In case a route cannot be determined, the `request` component will throw a [[yii\web\NotFoundHttpException]].


### 默认路由 <a name="default-route"></a>

如果进来的请求并没有提供一个具体的路由，一般这种情况多为于对首页的请求，此时就会启用由
[[yii\web\Application::defaultRoute]] 属性所指定的路由。该属性的默认值为 `site/index`，指向 `site` 控制器的 `index`
操作。你可以像这样在应用配置中自定义该属性：

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

有时候，你会想要将你的 Web 应用临时置于维护模式，所有的请求下都会显示同一张信息页。有很多种方法都可以实现这一点。但是其中一个最简单快捷的方法的是在应用配置中设置
[[yii\web\Application::catchAll]] 属性：

Sometimes, you may want to put your Web application in maintenance mode temporarily and display the same
informational page for all requests. There are many ways to accomplish this goal. But one of the simplest
ways is to configure the [[yii\web\Application::catchAll]] property like the following in the application configuration:

```php
return [
    // ...
    'catchAll' => ['site/offline'],
];
```

`catchAll` 属性需要一个数组做参数，该数组的第一个元素为路由，剩下的元素会（以名值对的形式）指定绑定于该操作的各个参数。

The `catchAll` property should take an array whose first element specifies a route, and
the rest of the elements (name-value pairs) specify the parameters to be bound to the action.

当设置了 `catchAll` 属性时，他会替换掉所有从输入的请求中解析出来的路由。如果是上文的这种设置，用于处理所有传入请求的操作都会是相同的 `site/offline`。

When the `catchAll` property is set, it will replace any route resolved from the incoming requests.
With the above configuration, the same `site/offline` action will be used to handle all incoming requests.


## 创建一个操作 <a name="creating-action"></a>

一旦请求路由被确定了，紧接着的步骤就是创建一个“操作（action）”对象响应该路由。

Once the requested route is determined, the next step is to create the action object corresponding to the route.

路由可以通过里面的斜杠分割成多个组成片段，举个栗子，`site/index` 可以分解为 `site` 和 `index`
两部分。每个片段都是指向某一模块（Module），控制器（Controller）或操作（action）的一个 ID。

The route is broken down into multiple parts by the slashes in it. For example, `site/index` will be
broken into `site` and `index`. Each part is an ID which may refer to a module, a controller or an action.

从路由的首个片段开始，应用会经过以下流程依次创建模块（如果有），控制器，以及操作：

Starting from the first part in the route, the application conducts the following steps to create modules (if any),
the controller and the action:

1. 设置应用主体为当前模块。
2. 检查当前模块的 [[yii\base\Module::controllerMap|controller map（控制器映射表）]] 是否包含当前 ID。如果是，会根据该表中的配置创建一个控制器对象，然后跳到步骤五执行该路由的后续片段。
3. 检查该 ID 是否指向当前模块中 [[yii\base\Module::modules|modules]] 属性里的模块列表中的一个模块。如果是，会根据该模块表中的配置创建一个模块对象，然后会以新创建的模块为环境，跳回步骤二解析下一段路由。
4. 将该 ID 视为控制器 ID，并创建控制器对象。用下个步骤解析路由里剩下的片段。
5. 控制器会在他的 [[yii\base\Controller::actions()|action map（操作映射表）]]里搜索当前 ID。如果找得到，它会根据该映射表中的配置创建一个操作对象；反之，控制器则会尝试创建一个与该 ID 
   相对应，由某个 action 方法所定义的行内操作（inline action）。
   
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

在上面的步骤里，如果有任何错误发生，都会抛出 [[yii\web\NotFoundHttpException]]（译注：就是404），标识出路由引导失败。

Among the above steps, if any error occurs, a [[yii\web\NotFoundHttpException]] will be thrown, indicating
failure of the routing.
