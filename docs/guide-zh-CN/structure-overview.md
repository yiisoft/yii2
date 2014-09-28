总览
========

Yii应用参照[模型-视图-控制器 (MVC)](http://wikipedia.org/wiki/Model-view-controller)
设计模式来架构. [模型](structure-models.md) 代表数据、业务逻辑和规则; [视图](structure-views.md)
展示模型的输出; [控制器](structure-controllers.md) 接受 [模型](structure-models.md) 和 [视图](structure-views.md) 的输入并输出前端.

除了MVC, Yii应用还有以下部分:

* [入口脚本](structure-entry-scripts.md): 终端用户能直接访问的PHP脚本，负责启动一个请求处理周期。
* [应用](structure-applications.md): 能全局范围内访问的对象，管理协调组件来完成请求.
* [应用组件](structure-application-components.md): 应用组件在应用中注册，提供不同的功能来完成请求。
* [模块](structure-modules.md): 模块是包含完整MVC结构的独立包，一个应用可以用多个模块来组建。 
* [过滤器](structure-filters.md): 控制器在处理请求之前或之后需要触发执行的代码。
* [小部件](structure-widgets.md): 可嵌入到[视图](structure-views.md)中的对象， 可包含控制器逻辑，可被不同视图重复调用。

下面的示意图展示了 Yii 应用的静态结构:

![Yii应用静态结构](images/application-structure.png)
