Skrip Masuk
===========

Skrip masuk adalah langkah pertama pada proses _bootstrap_ aplikasi. Dalam sebuah aplikasi (apakah
itu aplikasi web atau aplikasi konsol) memiliki satu skrip masuk. Pengguna mengirim _request_ ke
skrip masuk dimana skrip tersebut membangun objek aplikasi dan meneruskan _request_ ke objek tersebut.

Skrip masuk untuk aplikasi web harus disimpan pada direktori yang dapat diakses dari web sehingga
dapat di akses oleh pengguna. Secara umum, skrip tersebut diberi nama `index.php`, tetapi boleh menggunakan nama lain,
selama _web server_ bisa mengakses skrip tersebut.

Skrip masuk untuk aplikasi konsol pada umumnya disimpan di dalam [base path](structure-applications.md)
dari objek aplikasi dan diberi nama `yii` (dengan suffix `.php`). Skrip tersebut harus memiliki akses _execute_
sehingga pengguna dapat menjalan aplikasi konsol menggunakan perintah `./yii <route> [argument] [option]`.

Skrip masuk umumnya mengerjakan tugas berikut ini:

* Menentukan _global constant_;
* Mendaftarkan [autoloader Composer](https://getcomposer.org/doc/01-basic-usage.md#autoloading);
* Memasukkan file _class_ [[Yii]];
* Mengambil konfigurasi aplikasi, dan memuatnya;
* Membuat dan mengatur objek [application](structure-applications.md);
* Memanggil [[yii\base\Application::run()]] untuk memproses _request_ yang diterima;


## Aplikasi Web<span id="web-applications"></span>

Kode berikut ini adalah kode yang terdapat pada skrip masuk [Template Proyek Dasar](start-installation.md).

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// mendaftarkan autoloader Composer
require __DIR__ . '/../vendor/autoload.php';

// memasukkan file class Yii
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// Mengambil konfigurasi aplikasi
$config = require __DIR__ . '/../config/web.php';

// Membuat, mengkonfigurasi, dan menjalankan aplikasi
(new yii\web\Application($config))->run();
```


## Aplikasi Konsol <span id="console-applications"></span>

Demikian juga dengan aplikasi konsol, kode berikut ini adalah kode yang terdapat pada skrip masuk aplikasi konsol :

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// mendaftarkan autoloader composer
require __DIR__ . '/vendor/autoload.php';

// memasukkan file class Yii
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Mengambil konfigurasi aplikasi
$config = require __DIR__ . '/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```


## Menentukan _Constant_ <span id="defining-constants"></span>

Skrip masuk adalah file yang tepat untuk menentukan _global constant_. Yii mengenali tiga _constant_ berikut ini:

* `YII_DEBUG`: untuk menentukan apakah aplikasi sedang dalam mode _debug_. Pada saat mode _debug_, aplikasi
  akan menyimpan informasi log lebih banyak, dan akan menampilkan detail error urutan pemanggilan _(error call stack)_ jika ada _exception_ yang di-_throw_. Alasan inilah,
  kenapa mode _debug_ sebaiknya digunakan pada tahap pengembangan. Nilai _default_ dari `YII_DEBUG` adalah `false`.
* `YII_ENV`: untuk menentukan pada mode _environment_ manakah aplikasi ini dijalankan. _Constant_ ini akan dijelaskan lebih lanjut di
  bagian [Konfigurasi](concept-configurations.md#environment-constants).
  Nilai _default_ dari `YII_ENV` adalah `prod`, yang berarti aplikasi sedang dijalankan pada _production environment_.
* `YII_ENABLE_ERROR_HANDLER`: untuk menentukan apakah akan mengaktifkan penanganan eror yang disediakan oleh Yii. Nilai _default_
  dari _constant_ ini adalah `true`.

Untuk menentukan _constant_, kita biasanya menggunakan kode berikut ini:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

kode di atas memiliki tujuan yang sama dengan kode berikut ini:

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

Jelas, kode yang pertama lah yang lebih ringkas dan lebih mudah untuk dimengerti.

Penentuan _constant_ sebaiknya ditulis di baris-baris awal pada skrip masuk sehingga akan berfungsi
ketika file PHP lain akan dimasukkan _(include)_.
