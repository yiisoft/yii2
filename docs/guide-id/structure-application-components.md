Komponen Aplikasi
=================

Objek Aplikasi _(Application)_ adalah [service locators](concept-service-locator.md). Objek ini menampung seperangkat
apa yang kita sebut sebagai *komponen aplikasi* yang menyediakan berbagai layanan untuk menangani proses _request_. Sebagai contoh,
_component_ `urlManager` bertanggung jawab untuk menentukan _route_ dari _request_ menuju _controller_ yang sesuai;
_component_ `db` menyediakan layanan terkait database; dan sebagainya.

Setiap _component_ aplikasi memiliki sebuah ID yang mengidentifikasi dirinya secara unik dengan _component_ aplikasi lainnya
di dalam aplikasi yang sama. Anda dapat mengakses _component_ aplikasi melalui _expression_ berikut ini:

```php
\Yii::$app->componentID
```

Sebagai contoh, anda dapat menggunakan `\Yii::$app->db` untuk mengambil [[yii\db\Connection|koneksi ke DB]],
dan `\Yii::$app->cache` untuk mengambil [[yii\caching\Cache|cache utama]] yang terdaftar dalam aplikasi.

Sebuah _component_ aplikasi dibuat pertama kali pada saat objek tersebut pertama diakses menggunakan _expression_ di atas. Pengaksesan
berikutnya akan mengembalikan objek _component_ yang sama.

_Component_ aplikasi bisa merupakan objek apa saja. Anda dapat mendaftarkannya dengan mengatur
_property_ [[yii\base\Application::components]] pada [konfigurasi aplikasi](structure-applications.md#application-configurations).
Sebagai contoh,

```php
[
    'components' => [
        // mendaftarkan component "cache" menggunakan nama class
        'cache' => 'yii\caching\ApcCache',

        // mendaftaran component "db" menggunakan konfigurasi array
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // mendaftaran component "search" menggunakan anonymous function
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Info: Walaupun anda dapat mendaftarkan _component_ aplikasi sebanyak yang anda inginkan, anda harus bijaksana dalam melakukan hal ini.
  _Component_ aplikasi seperti layaknya variabel global. Menggunakan _component_ aplikasi yang terlalu banyak dapat berpotensi
  membuat kode anda menjadi rumit untuk diujicoba dan dikelola. Dalam banyak kasus, anda cukup membuat _component_ lokal
  dan menggunakannya pada saat diperlukan.


## _Bootstrap Components_ <span id="bootstrapping-components"></span>

Seperti yang disebutkan di atas, sebuah _component_ aplikasi akan dibuat ketika _component_ diakses pertama kali.
Jika tidak diakses sepanjang _request_ diproses, objek tersebut tidak akan dibuat. Terkadang, anda ingin
membuat objek _component_ aplikasi tersebut untuk setiap _request_, walaupun _component_ tersebut tidak diakses secara eksplisit.
Untuk melakukannya, anda dapat memasukkan ID _component_ tersebut ke _property_ [[yii\base\Application::bootstrap|bootstrap]] dari objek _Application_.

Sebagai contoh, konfigurasi aplikasi di bawah ini memastikan bahwa objek _component_ `log` akan selalu dibuat disetiap _request_:

```php
[
    'bootstrap' => [
        'log',
    ],
    'components' => [
        'log' => [
            // Konfigurasi untuk component "log"
        ],
    ],
]
```


## _Component_ Aplikasi Inti <span id="core-application-components"></span>

Yii menentukan seperangkat _component_ aplikasi inti dengan ID tetap dan konfigurasi default. Sebagai contoh,
_component_ [[yii\web\Application::request|request]] digunakan untuk memperoleh informasi tentang
_request_ dari pengguna dan merubahnya menjadi [route](runtime-routing.md). _Component_ [[yii\base\Application::db|db]]
merepresentasikan sebuah koneksi ke database yang bisa anda gunakan untuk menjalankan _query_ ke database.
Dengan bantuan _component_ inti inilah maka aplikasi Yii bisa menangani _request_ dari pengguna.

Dibawah ini adalah daftar dari _component_ aplikasi inti. Anda dapat mengatur dan memodifikasinya
seperti _component_ aplikasi pada umumnya. Ketika anda mengatur _component_ aplikasi inti,
jika anda tidak mendefinisikan _class_-nya, maka _class_ default yang akan digunakan.

* [[yii\web\AssetManager|assetManager]]: mengatur bundel aset _(asset bundles)_ dan publikasi aset _(asset publishing)_.
  Harap melihat bagian [Pengelolaan Aset](structure-assets.md) untuk informasi lebih lanjut.
* [[yii\db\Connection|db]]: merepresentasikan sebuah koneksi database yang bisa anda gunakan untuk melakukan _query_ ke database.
  Sebagai catatan, ketika anda mengatur _component_ ini, anda harus menentukan nama _class_ dari _component_ dan _property_ lain dari
  _component_ yang dibutuhkan, seperti [[yii\db\Connection::dsn]].
  Harap melihat bagian [Data Access Objects](db-dao.md) untuk informasi lebih lanjut.
* [[yii\base\Application::errorHandler|errorHandler]]: menangani error PHP dan _exception_.
  Harap melihat bagian [Menangani Error](runtime-handling-errors.md) untuk informasi lebih lanjut.
* [[yii\i18n\Formatter|formatter]]: memformat data ketika data tersebut ditampilkan ke pengguna. Sebagai contoh, sebuah angka
  mungkin ditampilkan menggunakan separator ribuan, dan tanggal mungkin diformat dalam format panjang.
  Harap melihat bagian [Memformat Data](output-formatting.md) untuk informasi lebih lanjut.
* [[yii\i18n\I18N|i18n]]: mendukung penerjemahan dan format pesan _(message)_.
  Harap melihat bagian [Internasionalisasi](tutorial-i18n.md) untuk informasi lebih lanjut.
* [[yii\log\Dispatcher|log]]: mengelola target log.
  Harap melihat bagian [Log](runtime-logging.md) untuk informasi lebih lanjut.
* [[yii\swiftmailer\Mailer|mailer]]: mendukung pembuatan dan pengiriman email.
  Harap melihat bagian [Mail](tutorial-mailing.md) untuk informasi lebih lanjut.
* [[yii\base\Application::response|response]]: merepresentasikan _response_ yang dikirimkan ke pengguna.
  Harap melihat bagian [Response](runtime-responses.md) untuk informasi lebih lanjut.
* [[yii\base\Application::request|request]]: merepresentasikan _request_ yang diterima dari pengguna.
  Harap melihat bagian [Request](runtime-requests.md) untuk informasi lebih lanjut.
* [[yii\web\Session|session]]: merepresentasikan informasi _session_. _Component_ ini hanya tersedia pada
  objek [[yii\web\Application|Aplikasi Web]].
  Harap melihat bagian [Session dan Cookie](runtime-sessions-cookies.md) untuk informasi lebih lanjut.
* [[yii\web\UrlManager|urlManager]]: mendukung penguraian dan pembuatan URL.
  Harap melihat bagian [Route dan Pembuatan URL](runtime-routing.md) untuk informasi lebih lanjut.
* [[yii\web\User|user]]: merepresentasikan informasi otentikasi dari pengguna. _Component_ ini hanya tersedia pada
  objek [[yii\web\Application|Aplikasi Web]].
  Harap melihat bagian [Otentikasi](security-authentication.md) untuk informasi lebih lanjut.
* [[yii\web\View|view]]: mendukung proses _render view_.
  Harap melihat bagian [View](structure-views.md) untuk informasi lebih lanjut.
