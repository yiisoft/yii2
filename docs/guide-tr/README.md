Yii 2.0 için Açıklayıcı Rehber
==============================

Bu döküman, [Yii Dökümantasyon Koşulları](https://www.yiiframework.com/doc/terms/) altında yayınlandı.

Tüm hakları saklıdır.

2014 (c) Yii Software LLC.


Giriş
------------

* [Yii hakkında](intro-yii.md)
* [Sürüm 1.1'den yükseltme](intro-upgrade-from-v1.md)


Başlarken
---------------

* [Ne bilmeye ihtiyacın var](start-prerequisites.md)
* [Yii Kurulumu](start-installation.md)
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
* [Kontrolcüler](structure-controllers.md)
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
* [Yönlendirme ve URL Oluşturma](runtime-routing.md)
* [İstekler](runtime-requests.md)
* [Yanıtlar](runtime-responses.md)
* [Oturumlar ve Çerezler](runtime-sessions-cookies.md)
* [Hataları İşleme](runtime-handling-errors.md)
* [Logging](runtime-logging.md)


Anahtar Kavramlar
------------

* [Bileşenler](concept-components.md)
* [Özellikler](concept-properties.md)
* [Olaylar](concept-events.md)
* [Davranışlar](concept-behaviors.md)
* [Yapılandırmalar](concept-configurations.md)
* [Takma Adlar](concept-aliases.md)
* [Sınıf Otomatik Yüklemesi](concept-autoloading.md)
* [Servis Bulucu](concept-service-locator.md)
* [Dependency Injection Container](concept-di-container.md)


Veritabanıyla Çalışırken
----------------------

* [Veritabanı Erişim Nesneleri](db-dao.md): Bir veritabanına bağlanmak, basit sorgular, işlemler ve şema manipilasyonu
* [Sorgu Oluşturucu](db-query-builder.md): Basit bir ayırma katmanı kullanarak veritabanı sorgulama
* [Active Record](db-active-record.md): Active Record ORM, kayıtları almak, değiştirmek ve ilişkileri tanımlama
* [Taşıma İşlemleri](db-migrations.md): Bir takım geliştirme ortamında sürüm kontrolünü veritabanlarına uygula
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


Kullanıcılardan Veri Alırken
-----------------------

* [Form Oluşturma](input-forms.md)
* [Veri Kontrolü](input-validation.md)
* [Dosya Yükleme](input-file-upload.md)
* [Collecting Tabular Input](input-tabular-input.md)
* [Birden fazla Model için Veri Alma](input-multiple-models.md)
* [Extending ActiveForm on the Client Side](input-form-javascript.md)


Veriyi Gösterirken
---------------

* [Veri Tipini Değiştirme](output-formatting.md)
* [Sayfalama](output-pagination.md)
* [Sıralama](output-sorting.md)
* [Veri Sağlayıcıları](output-data-providers.md)
* [Veri Araçları](output-data-widgets.md)
* [Scriptlerle Çalışmak](output-client-scripts.md)
* [Tema Kullanımı](output-theming.md)


Güvenlik
--------

* [Güvenliğe Genel Bakış](security-overview.md)
* [Kimlik Denetleme](security-authentication.md)
* [Yetkilendirme](security-authorization.md)
* [Şifrelerle Çalışma](security-passwords.md)
* [Kriptografi](security-cryptography.md)
* [Auth Clients](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [Egzersizler](security-best-practices.md)


Cache Almak
-------

* [Cache Genel Bakış](caching-overview.md)
* [Veriyi Cache Alma](caching-data.md)
* [Sayfanın Sadece Bir Kısmını Cache Alma](caching-fragment.md)
* [Sayfanın Tamamını Cache Alma](caching-page.md)
* [HTTP Cache Alma](caching-http.md)


RESTful Web Servisleri
--------------------

* [Hızlı Başlangıç](rest-quick-start.md)
* [Kaynaklar](rest-resources.md)
* [Kontrolcüler](rest-controllers.md)
* [Rota Yöntemleri](rest-routing.md)
* [Yanıt Formatlama](rest-response-formatting.md)
* [Kimlik Denetleme](rest-authentication.md)
* [İstek Sınırlama](rest-rate-limiting.md)
* [Sürümlere Ayırma](rest-versioning.md)
* [Hataları Kullanma](rest-error-handling.md)


Geliştirici Araçları
-----------------

* [Geliştirici Aracı ve Debugger](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Gii'yi Kullanarak Kod Oluşturmak](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [API Dökümanı Oluşturma](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


Test
-------

* [Testlere Genel Bakış](test-overview.md)
* [Testler için Ortam Kurulumu](test-environment-setup.md)
* [Unit Testleri](test-unit.md)
* [Fonksiyonel Testler](test-functional.md)
* [Kabul Testleri](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Özel Başlıklar
--------------

* [Gelişmiş Proje Şablonu](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide)
* [Building Application from Scratch](tutorial-start-from-scratch.md)
* [Konsol Komutları](tutorial-console.md)
* [Core Validators](tutorial-core-validators.md)
* [Docker](tutorial-docker.md)
* [Uluslararası Hale Getirme](tutorial-i18n.md)
* [Mail Gönderme](tutorial-mailing.md)
* [Performans Ayarları](tutorial-performance-tuning.md)
* [Paylaşımlı Sunucu Ortamı](tutorial-shared-hosting.md)
* [Şablon Motoru](tutorial-template-engines.md)
* [3. Parti Kodlarla Çalışma](tutorial-yii-integration.md)
* [Yii'yi Mikro Kütüphane Gibi Kullanma](tutorial-yii-as-micro-framework.md)


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

