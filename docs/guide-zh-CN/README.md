Yii 2.0 权威指南
===============================

本教程的发布遵循 [Yii 文档使用许可](http://www.yiiframework.com/doc/terms/)。

版权所有。

2014 (c) Yii Software LLC。


介绍（Introduction）
------------------

* [关于 Yii（About Yii）](intro-yii.md)
* [从 Yii 1.1 升级（Upgrading from Version 1.1）](intro-upgrade-from-v1.md)


入门（Getting Started）
---------------------

* [你需要了解什么（What do you need to know）](start-prerequisites.md)
* [安装 Yii（Installing Yii）](start-installation.md)
* [运行应用（Running Applications）](start-workflow.md)
* [第一次问候（Saying Hello）](start-hello.md)
* [使用 Forms（Working with Forms）](start-forms.md)
* [玩转 Databases（Working with Databases）](start-databases.md)
* [用 Gii 生成代码（Generating Code with Gii）](start-gii.md)
* [更上一层楼（Looking Ahead）](start-looking-ahead.md)


应用结构（Application Structure）
------------------------------

* [结构概述（Overview）](structure-overview.md)
* [入口脚本（Entry Scripts）](structure-entry-scripts.md)
* [应用（Applications）](structure-applications.md)
* [应用组件（Application Components）](structure-application-components.md)
* [控制器（Controllers）](structure-controllers.md)
* [模型（Models）](structure-models.md)
* [视图（Views）](structure-views.md)
* [模块（Modules）](structure-modules.md)
* [过滤器（Filters）](structure-filters.md)
* [小部件（Widgets）](structure-widgets.md)
* [前端资源（Assets）](structure-assets.md)
* [扩展（Extensions）](structure-extensions.md)


请求处理（Handling Requests）
--------------------------

* [运行概述（Overview）](runtime-overview.md)
* [引导（Bootstrapping）](runtime-bootstrapping.md)
* [路由引导与创建 URL（Routing and URL Creation）](runtime-routing.md)
* [请求（Requests）](runtime-requests.md)
* [响应（Responses）](runtime-responses.md)
* [Sessions and Cookies](runtime-sessions-cookies.md)
* [错误处理（Handling Errors）](runtime-handling-errors.md)
* [日志（Logging）](runtime-logging.md)


关键概念（Key Concepts）
---------------------

* [组件（Components）](concept-components.md)
* [属性（Properties）](concept-properties.md)
* [事件（Events）](concept-events.md)
* [行为（Behaviors）](concept-behaviors.md)
* [配置（Configurations）](concept-configurations.md)
* [别名（Aliases）](concept-aliases.md)
* [类自动加载（Class Autoloading）](concept-autoloading.md)
* [服务定位器（Service Locator）](concept-service-locator.md)
* [依赖注入容器（Dependency Injection Container）](concept-di-container.md)


配合数据库工作（Working with Databases）
------------------------------------

* [数据库访问（Data Access Objects）](db-dao.md): 数据库连接、基本查询、事务和模式操作
* [查询生成器（Query Builder）](db-query-builder.md): 使用简单抽象层查询数据库
* [活动记录（Active Record）](db-active-record.md): 活动记录对象关系映射（ORM），检索和操作记录、定义关联关系
* [数据库迁移（Migrations）](db-migrations.md): 在团体开发中对你的数据库使用版本控制
* [Sphinx](https://github.com/yiisoft/yii2-sphinx/blob/master/docs/guide-zh-CN/README.md)
* [Redis（yii2-redis）](yii2-redis.md): yii2-redis 扩展详解
* [MongoDB](https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide-zh-CN/README.md)
* [ElasticSearch](https://github.com/yiisoft/yii2-elasticsearch/blob/master/docs/guide-zh-CN/README.md)


接收用户数据（Getting Data from Users）
------------------------------------

* [创建表单（Creating Forms）](input-forms.md)
* [输入验证（Validating Input）](input-validation.md)
* [文件上传（Uploading Files）](input-file-upload.md)
* [收集列表输入（Collecting Tabular Input）](input-tabular-input.md)
* [多模型同时输入（Getting Data for Multiple Models）](input-multiple-models.md)
* [在客户端扩展 ActiveForm（Extending ActiveForm on the Client Side）](input-form-javascript.md)


显示数据（Displaying Data）
------------------------

* [格式化输出数据（Data Formatting）](output-formatting.md)
* [分页（Pagination）](output-pagination.md)
* [排序（Sorting）](output-sorting.md)
* [数据提供器（Data Providers）](output-data-providers.md)
* [数据小部件（Data Widgets）](output-data-widgets.md)
* [操作客户端脚本（Working with Client Scripts）](output-client-scripts.md)
* [主题（Theming）](output-theming.md)


安全（Security）
--------------

* [概述（Overview）](security-overview.md)
* [认证（Authentication）](security-authentication.md)
* [授权（Authorization）](security-authorization.md)
* [处理密码（Working with Passwords）](security-passwords.md)
* [加密（Cryptography）](security-cryptography.md)
* [客户端认证（Auth Clients）](https://github.com/yiisoft/yii2-authclient/blob/master/docs/guide-zh-CN/README.md)
* [安全领域的最佳实践（Best Practices）](security-best-practices.md)


缓存（Caching）
-------------

* [概述（Overview）](caching-overview.md)
* [数据缓存（Data Caching）](caching-data.md)
* [片段缓存（Fragment Caching）](caching-fragment.md)
* [分页缓存（Page Caching）](caching-page.md)
* [HTTP 缓存（HTTP Caching）](caching-http.md)


RESTful Web 服务（RESTful Web Services）
--------------------------------------

* [快速入门（Quick Start）](rest-quick-start.md)
* [资源（Resources）](rest-resources.md)
* [控制器（Controllers）](rest-controllers.md)
* [路由（Routing）](rest-routing.md)
* [格式化响应（Response Formatting）](rest-response-formatting.md)
* [授权验证（Authentication）](rest-authentication.md)
* [速率限制（Rate Limiting）](rest-rate-limiting.md)
* [版本化（Versioning）](rest-versioning.md)
* [错误处理（Error Handling）](rest-error-handling.md)


开发工具（Development Tools）
--------------------------

* [调试工具栏和调试器（Debug Toolbar and Debugger）](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide-zh-CN/README.md)
* [使用 Gii 生成代码（Generating Code using Gii）](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide-zh-CN/README.md)
* [生成 API 文档（Generating API Documentation）](https://github.com/yiisoft/yii2-apidoc)


测试（Testing）
-------------

* [概述（Overview）](test-overview.md)
* [搭建测试环境（Testing environment setup）](test-environment-setup.md)
* [单元测试（Unit Tests）](test-unit.md)
* [功能测试（Functional Tests）](test-functional.md)
* [验收测试（Acceptance Tests）](test-acceptance.md)
* [测试夹具（Fixtures）](test-fixtures.md)


高级专题（Special Topics）
-----------------------

* [高级应用模版（Advanced Project Template）](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-zh-CN/README.md)
* [从头构建自定义模版（Building Application from Scratch）](tutorial-start-from-scratch.md)
* [控制台命令（Console Commands）](tutorial-console.md)
* [核心验证器（Core Validators）](tutorial-core-validators.md)
* [Docker](tutorial-docker.md)
* [国际化（Internationalization）](tutorial-i18n.md)
* [收发邮件（Mailing）](tutorial-mailing.md)
* [性能优化（Performance Tuning）](tutorial-performance-tuning.md)
* [共享主机环境（Shared Hosting Environment）](tutorial-shared-hosting.md)
* [模板引擎（Template Engines）](tutorial-template-engines.md)
* [集成第三方代码（Working with Third-Party Code）](tutorial-yii-integration.md)
* [使用 Yii 作为微框架（Using Yii as a micro framework）](tutorial-yii-as-micro-framework.md)


小部件（Widgets）
---------------

* [GridView](http://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](http://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](http://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](http://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](http://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](http://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](http://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](http://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Bootstrap Widgets](https://github.com/yiisoft/yii2-bootstrap/blob/master/docs/guide-zh-CN/README.md)
* [jQuery UI Widgets](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide-zh-CN/README.md)


助手类（Helpers）
---------------

* [助手一览（Overview）](helper-overview.md)
* [Array 助手（ArrayHelper）](helper-array.md)
* [Html 助手（Html）](helper-html.md)
* [Url 助手（Url）](helper-url.md)

