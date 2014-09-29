总览
========

Yii 应用参照[模型-视图-控制器 （MVC）](http://wikipedia.org/wiki/Model-view-controller)
 设计模式来组织。 [模型](structure-models.md)代表数据、业务逻辑和规则；[视图](structure-views.md)展示模型的输出；[控制器](structure-controllers.md)接受出入并将其转换为[模型](structure-models.md)和[视图](structure-views.md)命令。

除了 MVC, Yii 应用还有以下部分：

* [入口脚本](structure-entry-scripts.md)：终端用户能直接访问的 PHP 脚本，负责启动一个请求处理周期。
* [应用](structure-applications.md)：能全局范围内访问的对象，管理协调组件来完成请求.
* [应用组件](structure-application-components.md)：在应用中注册的对象，提供不同的功能来完成请求。
* [模块](structure-modules.md)：包含完整 MVC 结构的独立包，一个应用可以由多个模块组建。 
* [过滤器](structure-filters.md)：控制器在处理请求之前或之后需要触发执行的代码。
* [小部件](structure-widgets.md)：可嵌入到[视图](structure-views.md)中的对象，可包含控制器逻辑，可被不同视图重复调用。

下面的示意图展示了 Yii 应用的静态结构：

![Yii应用静态结构](images/application-structure.png)
