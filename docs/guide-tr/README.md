Yii 2.0 için Açıklayıcı Rehber
===============================

Bu döküman, [Yii Dökümantasyon Koşulları](http://www.yiiframework.com/doc/terms/) altında yayınlandı.

Tüm hakları saklıdır.

2014 (c) Yii Software LLC.


Giriş
------------

* [Yii hakkında](intro-yii.md)
* [Sürüm 1.1'den yükseltme](intro-upgrade-from-v1.md)


Başlarken
---------------

* [Ne öğrenmeye ihtiyacın var](start-prerequisites.md)
* [Yii Yükleme](start-installation.md)
* [Uygulamaları Çalıştırmak](start-workflow.md)
* [Merhaba deyin](start-hello.md)
* [Formlarla Çalışırken](start-forms.md)
* [Veritabanıyla Çalışırken](start-databases.md)
* [Gii ile Kod Oluşturmak](start-gii.md)
* [İleriye Bakmak](start-looking-ahead.md)


Uygulama Yapısı
---------------------

* [Uygulama Yapısına Genel Bakış](structure-overview.md)
* [Giriş Komutları](structure-entry-scripts.md)
* [Uygulamalar](structure-applications.md)
* [Uygulama Bileşenleri](structure-application-components.md)
* [Kontroller](structure-controllers.md)
* [Modeller](structure-models.md)
* [Görünümler](structure-views.md)
* [Modüller](structure-modules.md)
* [Filtreler](structure-filters.md)
* [Araçlar](structure-widgets.md)
* [Varlıklar](structure-assets.md)
* [Uzantılar](structure-extensions.md)


İstekleri İşlemek
-----------------

* [İstekleri İşlemeye Genel Bakış](runtime-overview.md)
* [Bootstrapping](runtime-bootstrapping.md)
* [Yönlendirme ve URL Oluşturmak](runtime-routing.md)
* [İstekler](runtime-requests.md)
* [Cevaplar](runtime-responses.md)
* [Sessionlar ve Cookieler](runtime-sessions-cookies.md)
* [Hataları İşleme](runtime-handling-errors.md)
* [Olay Günlüğü](runtime-logging.md)


Anahtar Kavramlar
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


Veritabanıyla Çalışırken
----------------------

* [Database Access Objects](db-dao.md): Connecting to a database, basic queries, transactions, and schema manipulation
* [Query Builder](db-query-builder.md): Querying the database using a simple abstraction layer
* [Active Record](db-active-record.md): The Active Record ORM, retrieving and manipulating records, and defining relations
* [Migrations](db-migrations.md): Apply version control to your databases in a team development environment
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


Kullanıcılardan Veri Alırken
-----------------------

* [Creating Forms](input-forms.md)
* [Validating Input](input-validation.md)
* [Uploading Files](input-file-upload.md)
* [Collecting Tabular Input](input-tabular-input.md)
* [Getting Data for Multiple Models](input-multiple-models.md)
* [Extending ActiveForm on the Client Side](input-form-javascript.md)


Veriyi Gösterirken
---------------

* [Data Formatting](output-formatting.md)
* [Pagination](output-pagination.md)
* [Sorting](output-sorting.md)
* [Data Providers](output-data-providers.md)
* [Data Widgets](output-data-widgets.md)
* [Working with Client Scripts](output-client-scripts.md)
* [Theming](output-theming.md)


Güvenlik
--------

* [Security Overview](security-overview.md)
* [Authentication](security-authentication.md)
* [Authorization](security-authorization.md)
* [Working with Passwords](security-passwords.md)
* [Cryptography](security-cryptography.md)
* [Auth Clients](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [Best Practices](security-best-practices.md)


Önbellek
-------

* [Caching Overview](caching-overview.md)
* [Data Caching](caching-data.md)
* [Fragment Caching](caching-fragment.md)
* [Page Caching](caching-page.md)
* [HTTP Caching](caching-http.md)


RESTful Web Servisleri
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


Geliştirici Araçları
-----------------

* [Debug Toolbar and Debugger](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Generating Code using Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [Generating API Documentation](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


Test
-------

* [Testing Overview](test-overview.md)
* [Testing environment setup](test-environment-setup.md)
* [Unit Tests](test-unit.md)
* [Functional Tests](test-functional.md)
* [Acceptance Tests](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Özel Başlıklar
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


Araçlar
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


Yardımcılar
-------

* [Helpers Overview](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)

