Bekerja dengan Database
======================

Bagian ini akan memaparkan bagaimana membuat halaman yang menampilkan daftar data negara yang diambil dari
tabel `country` pada database. Untuk menyelesaikan tugas ini, anda akan melakukan konfigurasi koneksi ke database,
membuat class [Active Record](db-active-record.md), membuat [action](structure-controllers.md),
dan membuat [view](structure-views.md).

Sepanjang tutorial ini, anda akan mempelajari bagaimana cara untuk:

* konfigurasi koneksi ke database,
* membuat class _ActiveRecord_,
* mengambil _(query)_ data menggunakan class _ActiveRecord_,
* menampilkan data ke view dengan halaman per halaman.

Sebagai catatan untuk menyelesaikan bagian ini, anda harus memiliki pengetahuan dan pengalaman dasar dalam menggunakan database.
Secara khusus, anda harus mengetahui cara membuat database, dan cara menjalankan perintah SQL menggunakan aplikasi klien database.


Menyiapkan Database <span id="preparing-database"></span>
----------------------

Untuk memulai, buatlah database dengan nama `yii2basic`, yang akan digunakan untuk mengambil data dalam aplikasi anda.
Anda bisa membuat database SQLite, MySQL, PostgreSQL, MSSQL, atau Oracle, dimana Yii mendukung banyak aplikasi database. Untuk memudahkan, database yang digunakan adalah MySQL.

Selanjutnya, buat tabel dengan nama `country` pada database, dan _insert_ beberapa data sampel. Anda bisa menjalankan perintah SQL dibawah untuk memudahkan:

```sql
CREATE TABLE `country` (
  `code` CHAR(2) NOT NULL PRIMARY KEY,
  `name` CHAR(52) NOT NULL,
  `population` INT(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `country` VALUES ('AU','Australia',24016400);
INSERT INTO `country` VALUES ('BR','Brazil',205722000);
INSERT INTO `country` VALUES ('CA','Canada',35985751);
INSERT INTO `country` VALUES ('CN','China',1375210000);
INSERT INTO `country` VALUES ('DE','Germany',81459000);
INSERT INTO `country` VALUES ('FR','France',64513242);
INSERT INTO `country` VALUES ('GB','United Kingdom',65097000);
INSERT INTO `country` VALUES ('IN','India',1285400000);
INSERT INTO `country` VALUES ('RU','Russia',146519759);
INSERT INTO `country` VALUES ('US','United States',322976000);
```

Hingga saat ini, anda memiliki database bernama `yii2basic`, dan didalamnya terdapat tabel `country` dengan tiga kolom, berisi 10 baris data.

Konfigurasi Koneksi Database <span id="configuring-db-connection"></span>
---------------------------

Sebelum melanjutkan, pastikan anda memasang ekstensi PHP [PDO](http://www.php.net/manual/en/book.pdo.php) dan
driver PDO untuk database yang anda gunakan (misal, `pdo_mysql` untuk MySQL). Ini adalah kebutuhan mendasar
jika aplikasi anda menggunakan _relational database_.

Jika sudah terpasang, buka file `config/db.php` dan sesuaikan parameter yang sesuai untuk database anda. Secara default,
isi file konfigurasi tersebut adalah:

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

File `config/db.php` adalah tipikal [konfigurasi](concept-configurations.md) yang menggunakan file. File konfigurasi seperti ini menentukan parameter-parameter
yang dibutuhkan untuk membuat dan menginisialisasi objek [[yii\db\Connection]], dimana anda dapat menjalankan perintah SQL
dengan database yang dituju.

Konfigurasi koneksi database di atas dapat diakses pada kode aplikasi melalui _expression_ `Yii::$app->db`.

> Info: File `config/db.php` akan di _include_ oleh konfigurasi aplikasi utama `config/web.php`,
  yang berfungsi sebagai konfigurasi untuk inisialisasi objek [aplikasi](structure-applications.md).
  Untuk penjelasan lebih lengkap, silahkan lihat bagian [Konfigurasi](concept-configurations.md).

Jika anda membutuhkan dukungan database yang tidak didukung oleh Yii, silahkan cek _extensions_ di bawah ini:

- [Informix](https://github.com/edgardmessias/yii2-informix)
- [IBM DB2](https://github.com/edgardmessias/yii2-ibm-db2)
- [Firebird](https://github.com/edgardmessias/yii2-firebird)


Membuat Active Record <span id="creating-active-record"></span>
-------------------------

Untuk mengambil data di tabel `country`, buat class turunan [Active Record](db-active-record.md)
dengan nama `Country`, dan simpan pada file `models/Country.php`.

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
}
```

_Class_ `Country` di _extends_ dari [[yii\db\ActiveRecord]]. Anda tidak perlu untuk menulis kode di dalamnya! Hanya dengan kode di atas,
Yii akan mengetahui nama tabel yang dimaksud dari nama _class_ tersebut.

> Info: Jika nama _class_ tidak sesuai dengan nama tabel, anda dapat meng-_override_
  method [[yii\db\ActiveRecord::tableName()]] untuk menentukan nama tabel secara eksplisit.

Menggunakan class `Country`, anda bisa memanipulasi data pada tabel `country` dengan mudah, sebagaimana yang ditunjukkan pada kode di bawah ini:

```php
use app\models\Country;

// mengambil semua negara dari tabel country, dan mengurutkan berdasarkan "name" (nama)
$countries = Country::find()->orderBy('name')->all();

// mengambil negara yang memiliki primary key "US"
$country = Country::findOne('US');

// menampilkan "United States"
echo $country->name;

// Mengganti nama negara menjadi "U.S.A." dan menyimpan ke database
$country->name = 'U.S.A.';
$country->save();
```

> Info: _Active Record_ adalah cara yang efektif untuk mengakses dan memanipulasi data dari database secara _object-oriented_.
  Anda bisa mengetahui lebih banyak lagi pada bagian [Active Record](db-active-record.md). Sebagai alternatif, anda mungkin berinteraksi dengan database menggunakan metode data akses yang lebih mendasar yang disebut [Data Access Objects](db-dao.md).


Membuat Action <span id="creating-action"></span>
------------------

Untuk menampilkan data negara ke pengguna, anda harus membuat _action_. Dibanding menempatkan _action_ baru ini pada _controller_ `site`
seperti yang sudah anda lakukan pada bagian sebelumnya, sekarang ini ada baiknya membuat spesifik _controller_
untuk semua _action_ yang berhubungan dengan data negara. Namakan _controller_ baru ini dengan `CountryController`, dan buat
_action_ `index` pada controller tersebut, seperti yang ditunjukkan di bawah ini.

```php
<?php

namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Country;

class CountryController extends Controller
{
    public function actionIndex()
    {
        $query = Country::find();

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);

        $countries = $query->orderBy('name')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'countries' => $countries,
            'pagination' => $pagination,
        ]);
    }
}
```

Simpan kode di atas pada file `controllers/CountryController.php`.

_Action_ `index` memanggil `Country::find()`. _Method_ _Active Record_ ini membuat _query_ ke database dan mengambil semua data negara dari tabel `country`.
Untuk membatasi jumlah negara yang didapatkan pada setiap pengambilan data, _query_ tersebut dipecah menjadi halaman per halaman dengan bantuan dari
objek [[yii\data\Pagination]]. Objek `Pagination` diperuntukkan untuk dua tujuan:

* Menentukan klausa `offset` dan `limit` pada perintah SQL yang digunakan untuk _query_ agar mengambil
  hanya satu halaman data dalam sekali perintah (pada umumnya akan mengambil 5 baris dalam satu halaman).
* Digunakan pada _view_ untuk menampilkan tombol halaman yang terdiri dari tombol-tombol nomor halaman, yang selanjutnya akan dijelaskan
  pada sub bagian berikutnya.

Di akhir kode, _action_ `index` me-_render_ _view_ dengan nama `index`, dan mengirimkan data negara beserta dengan informasi
halaman dari data tersebut.


Membuat View <span id="creating-view"></span>
---------------

Di dalam folder `views`, pertama-tama buatlah sub-folder dengan nama `country`. Folder ini akan digunakan untuk menyimpan semua
_view_ yang akan di _render_ oleh _controller_ `country`. Di dalam folder `views/country`, buatlah file dengan nama `index.php`
berisi kode di bawah ini:

```php
<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Countries</h1>
<ul>
<?php foreach ($countries as $country): ?>
    <li>
        <?= Html::encode("{$country->name} ({$country->code})") ?>:
        <?= $country->population ?>
    </li>
<?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $pagination]) ?>
```

Terkait dengan tampilan data negara, _view_ ini terdiri dari dua bagian. Bagian pertama, dilakukan perulangan _(looping)_ pada data negara yang tersedia dan di-_render_ sebagai _unordered list_ HTML.
Bagian kedua, _widget_ [[yii\widgets\LinkPager]] di-_render_ menggunakan informasi halaman _(pagination)_ yang dikirimkan dari _action_.
_Widget_ `LinkPager` menampilkan tombol-tombol halaman. Mengklik pada salah satu tombol tersebut akan melakukan pengambilan data negara
terkait dengan halaman yang diklik.


Mari Kita Coba <span id="trying-it-out"></span>
-------------

Untuk melihat bagaimana kode-kode di atas bekerja, gunakan browser anda untuk mengakses URL ini:

```
http://hostname/index.php?r=country%2Findex
```

![Daftar Country](images/start-country-list.png)

Awalnya, anda akan melihat sebuah halaman yang menampilkan 5 negara. Dibawah daftar negara tersebut, anda akan melihat tombol halaman yang berjumlah empat tombol.
Jika anda mengklik tombol "2", anda akan melihat halaman tersebut menampilkan 5 negara lain pada database: halaman kedua pada record.
Silahkan melakukan observasi secara perlahan-lahan dan anda akan mengetahui bahwa URL pada browser juga akan berganti menjadi

```
http://hostname/index.php?r=country%2Findex&page=2
```

Di belakang layar, [[yii\data\Pagination|Pagination]] menyediakan semua kebutuhkan untuk memecah data menjadi halaman per halaman:

* Pertama-tama, [[yii\data\Pagination|Pagination]] menampilkan halaman pertama, dimana menjalankan perintah SELECT pada tabel `country`
  dengan klausa `LIMIT 5 OFFSET 0`. Hasilnya, 5 negara pertama akan diambil dan ditampilkan.
* _Widget_ [[yii\widgets\LinkPager|LinkPager]] me-_render_ tombol halaman menggunakan URL
  yang dibentuk oleh method [[yii\data\Pagination::createUrl()|Pagination]]. URL tersebut mengandung _query string_ `page`, yang
  merupakan representasi dari nomor halaman.
* Jika anda mengklik tombol halaman "2", sebuah _request_ yang mengarah ke _route_ `country/index` akan dijalankan hingga selesai.
  [[yii\data\Pagination|Pagination]] membaca _query string_ `page` dari URL dan kemudian menentukan halaman sekarang adalah halaman 2.
  Query data negara yang baru mengandung klausa `LIMIT 5 OFFSET 5` dan mengambil 5 data negara selanjutnya untuk
  kemudian ditampilkan.


Rangkuman <span id="summary"></span>
-------

Pada bagian ini, anda mempelajari bagaimana bekerja dengan database. Anda juga mempelajari bagaimana cara mengambil dan membagi
data dengan halaman per halaman dengan bantuan [[yii\data\Pagination]] dan [[yii\widgets\LinkPager]].

Di bagian selanjutnya, anda akan mempelajari bagaimana menggunakan _generator_ kode yang disebut [Gii](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide/README.md),
untuk membantu anda mengimplementasikan fitur-fitur umum pada aplikasi secara instan, seperti operasi _Create_-_Read_-_Update_-_Delete_ (CRUD)
untuk bekerja dengan data yang terdapat pada tabel di sebuah database. Sebenarnya, kode-kode yang barusan anda tulis, semuanya bisa
di _generate_ secara otomatis oleh Yii menggunakan tool Gii.
