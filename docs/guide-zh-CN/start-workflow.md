运行应用程序
====================

Yii 安装后，就有了一个可以运行的 Yii 应用程序，你可以通过 URL `http://hostname/basic/web/index.php` 或
`http://hostname/index.php` 访问它，具体要取决于你的配置。本章将介绍此应用程序的内置功能，代码的组织方式以及总体上程序是怎样处理请求
的。

> Info: 为简单起见，这个“入门”教程假设你已经将 `basic/web` 设置为了 Web 服务器的文档根目录。访问此程序的是类似
 `http://hostname/index.php` 的 URL 。请根据你的实际情况，在下文描述中作相应调整。


功能 <a name="functionality"></a>
-------------

安装的基础应用程序包含四个页面：

* 首页，访问 URL `http://hostname/index.php` 时显示，
* "About" 页面，
* "Contact" 页面，显示一个联络表单，允许终端用户通过电子邮件与你联系。
* 还有 "Login" 页面，显示一个登录表单，用于验证终端用户。请尝试使用“admin/admin”登录，你将发现主菜单上原来的“Login”变成了“Logout”。

这些页面共享一个通用的 header 和 footer。Header 含有一个主菜单，可以导航到不同的页面。

你应该还会在浏览器窗口的最下面发现有一个工具条。这是一个 Yii 提供的很有用的[调试工具](tool-debugger.md)，它会记录并显示很多调试信息，
例如日志消息，响应状态，数据库执行的查询等等。


应用程序结构 <a name="application-structure"></a>
---------------------

在你的应用程序中最重要的目录和文件是（假设程序的根目录是`basic`）：

```
basic/                  应用程序根目录
    composer.json       用于 Composer，描述包的信息
    config/             包含应用程序及其他配置信息。
        console.php     控制台应用程序配置
        web.php         Web 应用程序配置
    commands/           包含控制台命令类
    controllers/        包含控制器类
    models/             包含模型类
    runtime/            包含 Yii 运行时产生的文件，例如日志和缓存文件等
    vendor/             包含已安装的 Componser 包，包括 Yii 框架本身。
    views/              包含视图文件
    web/                应用程序 Web 根目录，包含可通过 Web 访问的文件
        assets/         包含 Yii 已发布的资源文件（javascript 和 css）
        index.php       应用程序的入口（或引导）脚本
    yii                 Yii 控制台命令可执行脚本
```

总体上，应用程序中的文件可以分为两类：位于 `basic/web` 中的和那些位于其他目录中的。前者可通过 HTTP （例如，在一个浏览器中）直接访问，
后者则不能且不应该能。

Yii 实现了 [模型-视图-控制器 (MVC)](http://wikipedia.org/wiki/Model-view-controller) 设计模式，在上述目录组织中也有体现。
`models` 目录中包含了所有的[模型类](structure-models.md)，`views` 目录包含了所有的[视图脚本](structure-views.md)，
`controllers` 目录包含了所有[控制器类](structure-controllers.md)。

下图展示了一个应用程序的静态结构。

![应用程序的静态结构](images/application-structure.png)

每个应用程序都有一个入口脚本 `web/index.php`，它是应用程序中仅有的可通过 Web 访问的 PHP 脚本。这个入口脚本接收一个传入请求并创建一个
[应用程序](structure-applications.md) 实例处理该请求。[应用程序](structure-applications.md)在其[组件](concept-components.md)
的帮助下解析请求并将请求分派到 MVC 元素上。[视图](structure-views.md)中使用[挂件](structure-widgets.md)协助构建复杂动态的用户接口
元素。


请求的生命周期 <a name="request-lifecycle"></a>
-----------------

下图展示了一个应用是如何处理请求的。

![请求的生命周期](images/application-lifecycle.png)

1. 一个用户提交了对[入口脚本（entry script）](structure-entry-scripts.md) `web/index.php` 的请求。
2. 入口脚本加载应用程序[配置信息（configuration）](concept-configurations.md)并创建一个[应用程序](structure-applications.md)实
例处理该请求。
3. 应用程序在[请求（request）]应用程序组件的协助下解析所请求的[路由（route）](runtime-routing.md)。
4. 应用程序创建一个[控制器（controller）](structure-controllers.md)实例处理该请求。
5. 控制器创建了一个[动作（action）](structure-controllers.md)实例，执行动作中的过滤器（filter）。
6. 如果有任何一个过滤器处理失败，则动作取消。
7. 如果所有的过滤器都执行通过，则动作执行。
8. 动作载入一个数据模型，可能是从一个数据库中加载。
9. 动作渲染一个视图（view），给它提供数据模型。
10. 渲染结果返回给[响应（response）](runtime-responses.md)应用程序组件。
11. 响应组件发送渲染结果到用户的浏览器。

