 Yii 2.0 الدليل التقني الخاص ببيئة العمل
===============================

تم تحرير هذا الملف اعتمادا على [الشروط الخاصة بتوثيف ال Yii](http://www.yiiframework.com/doc/terms/).

جميع الحقوق محفوظة

2014 (c) Yii Software LLC.


المقدمة
------------

* [عن بيئة العمل Yii](intro-yii.md)
* [التحديث من الإصدار 1.1](intro-upgrade-from-v1.md)


البداية من هنا
---------------

* [ماذا يجب أن تعرف عن بيئة العمل](start-prerequisites.md)
* [تنصيب ال Yii](start-installation.md)
* [تشغيل وتطبيق بيئة العمل](start-workflow.md)
* [قل مرحبا - المشروع الأول](start-hello.md)
* [العمل مع ال forms](start-forms.md)
* [العمل مع قواعد البيانات](start-databases.md)
* [إنشاء الشيفرة البرمجية من خلال ال gii](start-gii.md)
* [ماذا الآن - الخطوة القادمة](start-looking-ahead.md)


الهيكلية الخاصة بالتطبيق (Application Structure)
---------------------

* [نظرة عامة عن الهيكلية الخاصة بالتطبيق](structure-overview.md)
* [Entry Scripts](structure-entry-scripts.md)
* [التطبيقات](structure-applications.md)
* [مكونات التطبيقات](structure-application-components.md)
* [Controllers](structure-controllers.md)
* [Models](structure-models.md)
* [Views](structure-views.md)
* [Modules](structure-modules.md)
* [Filters](structure-filters.md)
* [Widgets](structure-widgets.md)
* [Assets](structure-assets.md)
* [Extensions](structure-extensions.md)


التعامل مع ال requests
-----------------

* [نظرة عامة عن التعامل مع ال requests](runtime-overview.md)
* [Bootstrapping](runtime-bootstrapping.md)
* [Routing and URL Creation](runtime-routing.md)
* [Requests](runtime-requests.md)
* [Responses](runtime-responses.md)
* [Sessions and Cookies](runtime-sessions-cookies.md)
* [Handling Errors - التحكم بالأخطاء](runtime-handling-errors.md)
* [Logging - تسجيل الحركات](runtime-logging.md)


المفاهيم الرئيسية (Key Concepts)
------------

* [Components](concept-components.md)
* [Properties](concept-properties.md)
* [Events](concept-events.md)
* [Behaviors](concept-behaviors.md)
* [Configurations](concept-configurations.md)
* [Aliases](concept-aliases.md)
* [Class Autoloading](concept-autoloading.md)
* [Service Locator](concept-service-locator.md)
* [Dependency Injection Container](concept-di-container.md)


التعامل مع قواعد البيانات
----------------------

* [Database Access Objects](db-dao.md): Connecting to a database, basic queries, transactions, and schema manipulation
* [Query Builder](db-query-builder.md): Querying the database using a simple abstraction layer
* [Active Record](db-active-record.md): The Active Record ORM, retrieving and manipulating records, and defining relations
* [Migrations](db-migrations.md): Apply version control to your databases in a team development environment
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


الحصول على البيانات من خلال خلال المستخدمين
-----------------------

* [Creating Forms](input-forms.md)
* [Validating Input](input-validation.md)
* [Uploading Files](input-file-upload.md)
* [Collecting Tabular Input](input-tabular-input.md)
* [Getting Data for Multiple Models](input-multiple-models.md)
* [Extending ActiveForm on the Client Side](input-form-javascript.md)


عرض البيانات
---------------

* [Data Formatting](output-formatting.md)
* [Pagination](output-pagination.md)
* [Sorting](output-sorting.md)
* [Data Providers](output-data-providers.md)
* [Data Widgets](output-data-widgets.md)
* [Working with Client Scripts](output-client-scripts.md)
* [Theming](output-theming.md)


الامان والحماية
--------

* [Security Overview](security-overview.md)
* [Authentication](security-authentication.md)
* [Authorization](security-authorization.md)
* [Working with Passwords](security-passwords.md)
* [Cryptography](security-cryptography.md)
* [Auth Clients](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [Best Practices](security-best-practices.md)


Caching التخزين المؤقت
-------

* [Caching Overview](caching-overview.md)
* [Data Caching](caching-data.md)
* [Fragment Caching](caching-fragment.md)
* [Page Caching](caching-page.md)
* [HTTP Caching](caching-http.md)


RESTful Web Services
--------------------

* [Quick Start](rest-quick-start.md)
* [Resources](rest-resources.md)
* [Controllers](rest-controllers.md)
* [Routing](rest-routing.md)
* [Response Formatting](rest-response-formatting.md)
* [Authentication](rest-authentication.md)
* [Rate Limiting](rest-rate-limiting.md)
* [Versioning](rest-versioning.md)
* [Error Handling](rest-error-handling.md)


الأدوات المساعدة أثناء تطوير التطبيقات
-----------------

* [Debug Toolbar and Debugger](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Generating Code using Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [Generating API Documentation](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


فحص واختبار التطبيقات
-------

* [Testing Overview](test-overview.md)
* [Testing environment setup](test-environment-setup.md)
* [Unit Tests](test-unit.md)
* [Functional Tests](test-functional.md)
* [Acceptance Tests](test-acceptance.md)
* [Fixtures](test-fixtures.md)


مواضيع وعناوين مميزة
--------------

* [Advanced Project Template](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide)
* [Building Application from Scratch](tutorial-start-from-scratch.md)
* [Console Commands](tutorial-console.md)
* [Core Validators](tutorial-core-validators.md)
* [Docker](tutorial-docker.md)
* [Internationalization](tutorial-i18n.md)
* [Mailing](tutorial-mailing.md)
* [Performance Tuning](tutorial-performance-tuning.md)
* [Shared Hosting Environment](tutorial-shared-hosting.md)
* [Template Engines](tutorial-template-engines.md)
* [Working with Third-Party Code](tutorial-yii-integration.md)
* [Using Yii as a micro framework](tutorial-yii-as-micro-framework.md)


Widgets
-------

* [GridView](https://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](https://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](https://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](https://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](https://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](https://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](https://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](https://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Bootstrap Widgets](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap/doc/guide)
* [jQuery UI Widgets](https://www.yiiframework.com/extension/yiisoft/yii2-jui/doc/guide)


Helpers
-------

* [Helpers Overview](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)

