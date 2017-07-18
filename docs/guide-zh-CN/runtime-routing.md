路由
=======

当[入口脚本](structure-entry-scripts.md)在调用 [[yii\web\Application::run()|run()]] 
方法时，它进行的第一个操作就是解析输入的请求，然后实例化对应的[控制器操作](structure-controllers.md)处理这个请求。该过程就被称为**引导路由（routing）**。（译注：中文里既是动词也是名词）


## 解析路由 <span id="resolving-route"></span>

路由引导的第一步，是把传入请求解析为一个路由。如我们在 [控制器（Controllers）](structure-controllers.md#routes)
章节中所描述的那样，路由是一个用于定位控制器操作的地址。这个过程通过 `request` 应用组件的 [[yii\web\Request::resolve()|resolve()]] 
方法实现，该方法会调用 [URL 管理器](runtime-url-handling.md) 进行实质上的请求解析工作。
 
默认情况下，传入请求会包含一个名为 `r` 的 `GET` 参数，它的值即被视为路由。但是如果启用
[[yii\web\UrlManager::enablePrettyUrl|美化 URL 功能]]，那么在确定请求的路由时，就会进行更多处理。具体的细节请参考
[URL 的解析与生成](runtime-url-handling.md) 章节。

假使某路由最终实在无法被确定，那么 `request` 组件会抛出 [[yii\web\NotFoundHttpException]] 异常（译注：大名鼎鼎的 404）。


### 缺省路由 <span id="default-route"></span>

如果传入请求并没有提供一个具体的路由，（一般这种情况多为于对首页的请求）此时就会启用由
[[yii\web\Application::defaultRoute]] 属性所指定的缺省路由。该属性的默认值为 `site/index`，它指向 `site` 控制器的 `index`
操作。你可以像这样在应用配置中调整该属性的值：

```php
return [
    // ...
    'defaultRoute' => 'main/index',
];
```


### `catchAll` 路由（全拦截路由） <span id="catchall-route"></span>

有时候，你会想要将你的 Web
应用临时调整到维护模式，所有的请求下都会显示相同的信息页。当然，要实现这一点有很多种方法。这里面最简单快捷的方法就是在应用配置中设置下
[[yii\web\Application::catchAll]] 属性：

```php
return [
    // ...
    'catchAll' => ['site/offline'],
];
```

`catchAll` 属性需要传入一个数组做参数，该数组的第一个元素为路由，剩下的元素会（以名值对的形式）指定绑定于该操作的各个参数。

当设置了 `catchAll` 属性时，它会替换掉所有从输入的请求中解析出来的路由。如果是上文的这种设置，用于处理所有传入请求的操作都会是相同的 `site/offline`。


## 创建操作 <span id="creating-action"></span>

一旦请求路由被确定了，紧接着的步骤就是创建一个“操作（action）”对象，用以响应该路由。

路由可以用里面的斜杠分割成多个组成片段，举个栗子，`site/index` 可以分解为 `site` 和 `index`
两部分。每个片段都是指向某一模块（Module）、控制器（Controller）或操作（action）的 ID。

从路由的首个片段开始，应用会经过以下流程依次创建模块（如果有），控制器，以及操作：

1. 设置应用主体为当前模块。
2. 检查当前模块的 [[yii\base\Module::controllerMap|controller map（控制器映射表）]] 是否包含当前 ID。如果是，会根据该表中的配置创建一个控制器对象，然后跳到步骤五执行该路由的后续片段。
3. 检查该 ID 是否指向当前模块中 [[yii\base\Module::modules|modules]] 属性里的模块列表中的一个模块。如果是，会根据该模块表中的配置创建一个模块对象，然后会以新创建的模块为环境，跳回步骤二解析下一段路由。
4. 将该 ID 视为控制器 ID，并创建控制器对象。用下个步骤解析路由里剩下的片段。
5. 控制器会在他的 [[yii\base\Controller::actions()|action map（操作映射表）]]里搜索当前 ID。如果找得到，它会根据该映射表中的配置创建一个操作对象；反之，控制器则会尝试创建一个与该 ID 
   相对应，由某个 action 方法所定义的行内操作（inline action）。

在上面的步骤里，如果有任何错误发生，都会抛出 [[yii\web\NotFoundHttpException]]，指出路由引导的过程失败了。