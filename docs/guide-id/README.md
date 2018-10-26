Panduan Definitif Untuk Yii 2.0
===============================

Tutorial ini dirilis di bawah [Persyaratan Dokumentasi Yii](http://www.yiiframework.com/doc/terms/).

Seluruh hak cipta dilindungi.

2014 (c) Yii Software LLC.


Pengantar
------------

* [Tentang Yii](intro-yii.md)
* [Upgrade dari Versi 1.1](intro-upgrade-from-v1.md)


Mulai
---------------

* [Instalasi Yii](start-installation.md)
* [Menjalankan Aplikasi](start-workflow.md)
* [Mengatakan Hello](start-hello.md)
* [Bekerja dengan Form](start-forms.md)
* [Bekerja dengan Database](start-databases.md)
* [Membuat Kode Otomatis dengan Gii](start-gii.md)
* [Menatap ke Depan](start-looking-ahead.md)


Struktur Aplikasi
---------------------

* [Tinjauan](structure-overview.md)
* [Script Masuk](structure-entry-scripts.md)
* [Aplikasi](structure-applications.md)
* [Komponen Aplikasi](structure-application-components.md)
* [Controller](structure-controllers.md)
* [Model](structure-models.md)
* [Views](structure-views.md)
* [Modul](structure-modules.md)
* [Filter](structure-filters.md)
* [Widgets](structure-widgets.md)
* [Aset](structure-assets.md)
* [Ekstensi](structure-extensions.md)


Penanganan Permintaan
-----------------

* [Tinjauan](runtime-overview.md)
* [Bootstrap](runtime-bootstrapping.md)
* [Routing dan Pembuatan URL](runtime-routing.md)
* [Permintaan](runtime-requests.md)
* [Tanggapan](runtime-responses.md)
* [Sesi dan Cookies](runtime-sessions-cookies.md)
* [Penanganan Kesalahan](runtime-handling-errors.md)
* [Logging](runtime-logging.md)


Konsep Pokok
------------

* [Komponen](concept-components.md)
* [Properti](concept-properties.md)
* [Event](concept-events.md)
* [Perilaku](concept-behaviors.md)
* [Konfigurasi](concept-configurations.md)
* [Alias](concept-aliases.md)
* [Class Autoloading](concept-autoloading.md)
* [Layanan Locator](concept-service-locator.md)
* [Dependency Injection](concept-di-container.md)


Bekerja dengan Database
----------------------

* [Data Access Objects](db-dao.md): Menghubungkan ke database, query dasar, transaksi, dan manipulasi skema
* [Query Builder](db-query-builder.md): Query database menggunakan lapisan abstraksi sederhana
* [Active Record](db-active-record.md): ORM Active Record, mengambil dan memanipulasi catatan, dan mendefinisikan hubungan
* [Migrasi](db-migrations.md): Terapkan kontrol versi untuk database Anda dalam lingkungan pengembangan tim
* [Sphinx](https://github.com/yiisoft/yii2-sphinx/blob/master/docs/guide/README.md)
* [Redis](https://github.com/yiisoft/yii2-redis/blob/master/docs/guide/README.md)
* [MongoDB](https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/README.md)
* [ElasticSearch](https://github.com/yiisoft/yii2-elasticsearch/blob/master/docs/guide/README.md)


Mendapatkan Data dari Pengguna
-----------------------

* [Membuat Formulir](input-forms.md)
* [Memvalidasi Masukan](input-validation.md)
* [Mengunggah File](input-file-upload.md)
* [Mengumpulkan Masukan Tabel](input-tabular-input.md)
* [Mendapatkan Data untuk Beberapa Model](input-multiple-models.md)


Menampilkan Data
---------------

* [Pemformatan Data](output-formatting.md)
* [Pagination](output-pagination.md)
* [Pengurutan](output-sorting.md)
* [Penyedia Data](output-data-providers.md)
* [Data Widget](output-data-widgets.md)
* [Bekerja dengan Script Client](output-client-scripts.md)
* [Tema](output-theming.md)


Keamanan
--------

* [Tinjauan](security-overview.md)
* [Otentikasi](security-authentication.md)
* [Otorisasi](security-authorization.md)
* [Bekerja dengan Kata Sandi](security-passwords.md)
* [Kriptografi](security-cryptography.md)
* [Otentikasi Klien](https://github.com/yiisoft/yii2-authclient/blob/master/docs/guide/README.md)
* [Praktik Terbaik](security-best-practices.md)


Caching
-------

* [Tinjauan](caching-overview.md)
* [Caching Data](caching-data.md)
* [Caching Fragmen](caching-fragment.md)
* [Caching Halaman](caching-page.md)
* [Caching HTTP](caching-http.md)


Layanan Web RESTful
--------------------

* [Quick Start](rest-quick-start.md)
* [Sumber Daya](rest-resources.md)
* [Controller](rest-controllers.md)
* [Routing](rest-routing.md)
* [Penformatan Respon](rest-response-formatting.md)
* [Otentikasi](rest-authentication.md)
* [Pembatasan Laju](rest-rate-limiting.md)
* [Versi](rest-versioning.md)
* [Penanganan Kesalahan](rest-error-handling.md)


Alat Pengembangan
-----------------

* [Debug Toolbar dan Debugger](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
* [Membuat Kode Otomatis dengan Gii](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide/README.md)
* [Membuat API Documentation](https://github.com/yiisoft/yii2-apidoc)


Pengujian
-------

* [Tinjauan](test-overview.md)
* [Persiapan Lingkungan Pengujian](test-environment-setup.md)
* [Tes Satuan](test-unit.md)
* [Tes Fungsional](test-functional.md)
* [Tes Penerimaan](test-acceptance.md)
* [Jadwal](test-fixtures.md)


Topik Khusus
--------------

* [Cetakan Proyek Lanjutan](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md)
* [Membangun Aplikasi dari Awal](tutorial-start-from-scratch.md)
* [Console Commands](tutorial-console.md)
* [Validator Inti](tutorial-core-validators.md)
* [Internasionalisasi](tutorial-i18n.md)
* [Mailing](tutorial-mailing.md)
* [Penyetelan Performa](tutorial-performance-tuning.md)
* [Lingkungan Shared Hosting](tutorial-shared-hosting.md)
* [Template Engine](tutorial-template-engines.md)
* [Bekerja dengan Kode Pihak Ketiga](tutorial-yii-integration.md)


Widget
-------

* [GridView](http://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](http://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](http://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](http://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](http://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](http://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](http://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](http://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Bootstrap Widgets](https://github.com/yiisoft/yii2-bootstrap/blob/master/docs/guide/README.md)
* [JQuery UI Widgets](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/README.md)


Alat Bantu
---------

* [Tinjauan](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)
