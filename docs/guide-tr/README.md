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
* [Olaylar](concept-events.md)
* [Behaviors](concept-behaviors.md)
* [Yapılandırmalar](concept-configurations.md)
* [Aliases](concept-aliases.md)
* [Class Autoloading](concept-autoloading.md)
* [Service Locator](concept-service-locator.md)
* [Dependency Injection Container](concept-di-container.md)


Veritabanıyla Çalışırken
----------------------

* [Database Access Objects](db-dao.md): Bir veritabanına bağlanmak, basit sorgular, işlemler ve şema manipilasyonu
* [Query Builder](db-query-builder.md): Basit bir ayırma katmanı kullanarak veritabanı sorgulamak
* [Active Record](db-active-record.md): Active Record ORM, kayıtları almak, değiştirmek ve ilişkileri tanımlamak
* [Migrations](db-migrations.md): Bir takım geliştirme ortamında sürüm kontrolünü veritabanlarına uygula
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


Kullanıcılardan Veri Alırken
-----------------------

* [Form Oluşturma](input-forms.md)
* [Validating Input](input-validation.md)
* [Dosya Yükleme](input-file-upload.md)
* [Collecting Tabular Input](input-tabular-input.md)
* [Birden fazla Model için Veri Alma](input-multiple-models.md)
* [Extending ActiveForm on the Client Side](input-form-javascript.md)


Veriyi Gösterirken
---------------

* [Veri Formatlama](output-formatting.md)
* [Sayfalama](output-pagination.md)
* [Sıralama](output-sorting.md)
* [Veri Sağlayıcıları](output-data-providers.md)
* [Veri Araçları](output-data-widgets.md)
* [Scriptlerle Çalışmak](output-client-scripts.md)
* [Tema Kullanımı](output-theming.md)


Güvenlik
--------

* [Güvenliğe Genel Bakış](security-overview.md)
* [Doğrulama](security-authentication.md)
* [Yetkilendirme](security-authorization.md)
* [Şifrelerle Çalışma](security-passwords.md)
* [Kriptografi](security-cryptography.md)
* [Auth Clients](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [En İyi Egzersizler](security-best-practices.md)


Önbellek
-------

* [Cache Genel Bakış](caching-overview.md)
* [Veriyi Cache Almak](caching-data.md)
* [Kısım Cachelemek](caching-fragment.md)
* [Sayfayı Cache Almak](caching-page.md)
* [HTTP Cache Almak](caching-http.md)


RESTful Web Servisleri
--------------------

* [Hızlı Başlangıç](rest-quick-start.md)
* [Kaynaklar](rest-resources.md)
* [Kontroller](rest-controllers.md)
* [Routing](rest-routing.md)
* [Yanıt Formatlama](rest-response-formatting.md)
* [Doğrulama](rest-authentication.md)
* [İstek Sınırlama](rest-rate-limiting.md)
* [Sürümlere Ayırma](rest-versioning.md)
* [Hataları Kullanma](rest-error-handling.md)


Geliştirici Araçları
-----------------

* [Debug Toolbar ve Debugger](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Gii'yi Kullanarak Kod Oluşturmak](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [API Dökümanı OLuşturma](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


Test
-------

* [Testlere Genel Bakış](test-overview.md)
* [Testlere Ortam Kurulumu](test-environment-setup.md)
* [Unit Testleri](test-unit.md)
* [Fonksiyonel Testler](test-functional.md)
* [Kabul Testleri](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Özel Başlıklar
--------------

* [Gelişmiş Proje Şablonu](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide)
* [Building Application from Scratch](tutorial-start-from-scratch.md)
* [Console Komutları](tutorial-console.md)
* [Core Validators](tutorial-core-validators.md)
* [Docker](tutorial-docker.md)
* [Uluslararası hale getirme](tutorial-i18n.md)
* [Mailing](tutorial-mailing.md)
* [Performans Ayarları](tutorial-performance-tuning.md)
* [Paylaşımlı Hosting Ortamı](tutorial-shared-hosting.md)
* [Şablon Motoru](tutorial-template-engines.md)
* [3. parti kodlarla çalışmak](tutorial-yii-integration.md)
* [Yii'yi mikro kütüphane gibi kullanmak](tutorial-yii-as-micro-framework.md)


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

