Instalasi Yii
==============

Anda dapat menginstal Yii dalam dua cara, menggunakan [Composer](https://getcomposer.org/) paket manager atau dengan mengunduh file arsip.
Yang pertama adalah cara yang lebih disukai, karena memungkinkan Anda untuk menginstal [ekstensi](structure-extensions.md)  baru atau memperbarui Yii dengan hanya menjalankan *command line*.

Hasil instalasi standar Yii baik framework maupun template proyek keduanya akan terunduh dan terpasang.
Sebuah template proyek adalah proyek Yii yang menerapkan beberapa fitur dasar, seperti login, formulir kontak, dll.
Kode diatur dalam cara yang direkomendasikan. Oleh karena itu, dapat berfungsi sebagai titik awal yang baik untuk proyek-proyek Anda.
    
Dalam hal ini dan beberapa bagian berikutnya, kita akan menjelaskan cara menginstal Yii dengan apa yang disebut *Template Proyek Dasar* dan
bagaimana menerapkan fitur baru di atas template ini. Yii juga menyediakan template lain yang disebut
yang [Template Proyek Lanjutan](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md) yang lebih baik digunakan dalam lingkungan pengembangan tim
untuk mengembangkan aplikasi dengan beberapa tingkatan.

> Info: Template Proyek Dasar ini cocok untuk mengembangkan 90 persen dari aplikasi Web. Ini berbeda
  dari Template Proyek Lanjutan terutama dalam bagaimana kode mereka diatur. Jika Anda baru untuk Yii, kami sangat
  merekomendasikan Anda tetap pada Template Proyek Dasar untuk kesederhanaan dan fungsi yang cukup.


Menginstal melalui Komposer <span id="installing-via-composer"></span>
-----------------------

Jika Anda belum memiliki Composer terinstal, Anda dapat melakukannya dengan mengikuti petunjuk di
[getcomposer.org] (https://getcomposer.org/download/). Pada Linux dan Mac OS X, Anda akan menjalankan perintah berikut:

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Pada Windows, Anda akan mengunduh dan menjalankan [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Silakan merujuk ke [Dokumentasi Composer](https://getcomposer.org/doc/) jika Anda menemukan
masalah atau ingin mempelajari lebih lanjut tentang penggunaan Composer.

Jika Composer sudah terinstal sebelumnya, pastikan Anda menggunakan versi terbaru. Anda dapat memperbarui Komposer
dengan menjalankan `composer self-update`.

Dengan Komposer diinstal, Anda dapat menginstal Yii dengan menjalankan perintah berikut di bawah folder yang terakses web:

```bash
composer global require "fxp/composer-asset-plugin:^1.3.1"
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

Perintah pertama menginstal [komposer aset Plugin](https://github.com/francoispluchino/composer-asset-plugin/)
yang memungkinkan mengelola bower dan paket npm melalui Composer. Anda hanya perlu menjalankan perintah ini
sekali untuk semua. Perintah kedua menginstal Yii dalam sebuah direktori bernama `basic`. Anda dapat memilih nama direktori yang berbeda jika Anda ingin.

> Catatan: Selama instalasi, Composer  dapat meminta login Github Anda. Ini normal karena Komposer
> Perlu mendapatkan cukup API rate-limit untuk mengambil informasi paket dari Github. Untuk lebih jelasnya,
> Silahkan lihat [Documentation Composer](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Tip: Jika Anda ingin menginstal versi pengembangan terbaru dari Yii, Anda dapat menggunakan perintah berikut sebagai gantinya,
> Yang menambahkan [opsi stabilitas](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
> ```bash
> composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
> ```
>
> Perhatikan bahwa versi pengembangan dari Yii tidak boleh digunakan untuk produksi karena kemungkinan dapat *merusak* kode Anda yang sedang berjalan.


Instalasi dari file Arsip <span id="installing-from-archive-file"></span>
-------------------------------

Instalasi Yii dari file arsip melibatkan tiga langkah:

1. Download file arsip dari [yiiframework.com](http://www.yiiframework.com/download/).
2. Uraikan file yang didownload ke folder yang bisa diakses web.
3. Memodifikasi `config/web.php` dengan memasukkan kunci rahasia untuk `cookieValidationKey`.
   (Ini dilakukan secara otomatis jika Anda menginstal Yii menggunakan Composer):

   ```php
   // !!! Isikan nilai key jika kosong - ini diperlukan oleh cookie validation
   'cookieValidationKey' => 'enter your secret key here',
   ```


Pilihan Instalasi lainnya <span id="other-installation-options"></span>
--------------------------

Petunjuk instalasi di atas menunjukkan cara menginstal Yii, yang juga menciptakan aplikasi Web dasar yang bekerja di luar kotak.
Pendekatan ini adalah titik awal yang baik untuk sebagian besar proyek, baik kecil atau besar. Hal ini terutama cocok jika Anda hanya
mulai belajar Yii.

Tetapi ada pilihan instalasi lain yang tersedia:

* Jika Anda hanya ingin menginstal kerangka inti dan ingin membangun seluruh aplikasi dari awal,
  Anda dapat mengikuti petunjuk seperti yang dijelaskan dalam [Membangun Aplikasi dari Scratch](tutorial-start-from-scratch.md).
* Jika Anda ingin memulai dengan aplikasi yang lebih canggih, lebih cocok untuk tim lingkungan pengembangan,
  Anda dapat mempertimbangkan memasang [Template Lanjutan Proyek] (https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md).


Memverifikasi Instalasi <span id="memverifikasi instalasi"></span>
--------------------------

Setelah instalasi selesai, baik mengkonfigurasi web server Anda (lihat bagian berikutnya) atau menggunakan
[Built-in web server PHP] (https://secure.php.net/manual/en/features.commandline.webserver.php) dengan menjalankan berikut
konsol perintah sementara dalam proyek `web` direktori:

```bash
php yii serve
```

> Catatan: Secara default HTTP-server akan mendengarkan port 8080. Namun jika port yang sudah digunakan atau Anda ingin
melayani beberapa aplikasi dengan cara ini, Anda mungkin ingin menentukan port apa yang harus digunakan. Cukup tambahkan argumen --port:

```bash
php yii serve --port = 8888
```

Anda dapat menggunakan browser untuk mengakses aplikasi Yii yang diinstal dengan URL berikut:

```
http://localhost:8080/
```

![Instalasi Sukses dari Yii](images/start-app-installed.png)

Anda seharusnya melihat halaman "Congratulations!" di browser Anda. Jika tidak, periksa apakah instalasi PHP Anda memenuhi
persyaratan Yii. Anda dapat memeriksa apakah persyaratan minimumnya cocok dengan menggunakan salah satu pendekatan berikut:

* Copy `/requirements.php` ke `/web/requirements.php` kemudian gunakan browser untuk mengakses melalui `http://localhost/requirements.php`
* Jalankan perintah berikut:

  ```bash
  cd basic
  php requirements.php
  ```

Anda harus mengkonfigurasi instalasi PHP Anda sehingga memenuhi persyaratan minimal Yii. Yang paling penting, Anda
harus memiliki PHP versi 5.4 atau lebih. Anda juga harus menginstal [PDO PHP Ekstensi](http://www.php.net/manual/en/pdo.installation.php)
dan driver database yang sesuai (seperti `pdo_mysql` untuk database MySQL), jika aplikasi Anda membutuhkan database.


Konfigurasi Web Server <span id="configuring-web-servers"></span>
-----------------------

> Info: Anda dapat melewati seksi ini untuk saat ini jika Anda hanya menguji sebuah Yii dengan niat
  penggelaran itu untuk server produksi.

Aplikasi yang diinstal sesuai dengan petunjuk di atas seharusnya bekerja dengan baik
pada [Apache HTTP server](http://httpd.apache.org/) atau [Nginx HTTP server](http://nginx.org/), pada
Windows, Mac OS X, atau Linux yang menjalankan PHP 5.4 atau lebih tinggi. Yii 2.0 juga kompatibel dengan facebook
[HHVM](http://hhvm.com/). Namun, ada beberapa kasus di mana HHVM berperilaku berbeda dari PHP asli,
sehingga Anda harus mengambil beberapa perlakuan ekstra ketika menggunakan HHVM.

Pada server produksi, Anda mungkin ingin mengkonfigurasi server Web Anda sehingga aplikasi dapat diakses
melalui URL `http://www.example.com/index.php` bukannya `http://www.example.com/dasar/web/index.php`. konfigurasi seperti itu
membutuhkan root dokumen server Web Anda menunjuk ke folder `basic/web`. Anda mungkin juga
ingin menyembunyikan `index.php` dari URL, seperti yang dijelaskan pada bagian [Routing dan Penciptaan URL](runtime-routing.md).
Dalam bagian ini, Anda akan belajar bagaimana untuk mengkonfigurasi Apache atau Nginx server Anda untuk mencapai tujuan tersebut.

> Info: Dengan menetapkan `basic/web` sebagai akar dokumen, Anda juga mencegah pengguna akhir mengakses
kode private aplikasi Anda dan file data sensitif yang disimpan dalam direktori sejajar
dari `basic/web`. Mencegah akses ke folder lainnya adalah sebuah peningkatan keamanan.

> Info: Jika aplikasi Anda akan berjalan di lingkungan shared hosting di mana Anda tidak memiliki izin
untuk memodifikasi konfigurasi server Web-nya, Anda mungkin masih menyesuaikan struktur aplikasi Anda untuk keamanan yang lebih baik. Silakan merujuk ke
yang lebih baik. Lihat bagian [Shared Hosting Lingkungan](tutorial-shared-hosting.md) untuk rincian lebih lanjut.


### Konfigurasi Apache yang Direkomendasikan <span id="recommended-apache-configuration"></span>

Gunakan konfigurasi berikut di file `httpd.conf` Apache atau dalam konfigurasi virtual host. Perhatikan bahwa Anda
harus mengganti `path/to/basic/web` dengan path ` dasar/web` yang sebenarnya.

```apache
# Set document root to be "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # use mod_rewrite for pretty URL support
    RewriteEngine on
    # If a directory or a file exists, use the request directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Otherwise forward the request to index.php
    RewriteRule . index.php

    # ...other settings...
</Directory>
```


### Konfigurasi Nginx yang Direkomendasikan<span id="recommended-nginx-configuration"></span>

Untuk menggunakan [Nginx](http://wiki.nginx.org/), Anda harus menginstal PHP sebagai [FPM SAPI](http://php.net/install.fpm).
Anda dapat menggunakan konfigurasi Nginx berikut, menggantikan `path/to/basic/web` dengan path yang sebenarnya untuk
`basic/web` dan `mysite.local` dengan hostname yang sebenarnya untuk server.

```nginx
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Bila menggunakan konfigurasi ini, Anda juga harus menetapkan `cgi.fix_pathinfo=0` di file` php.ini`
untuk menghindari banyak panggilan `stat()` sistem yang tidak perlu.

Sekalian catat bahwa ketika menjalankan server HTTPS, Anda perlu menambahkan `fastcgi_param HTTPS on;` sehingga Yii
benar dapat mendeteksi jika sambungan aman.
