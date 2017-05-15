Bekerja dengan Form
==================

Bagian ini memaparkan bagaimana membuat halaman dengan form untuk mengambil data dari pengguna.
Halaman akan menampilkan form dengan input field Nama dan Email.
Setelah mendapatkan dua data dari pengguna, halaman akan menampilkan kembali data yang diinput pada form sebagai konfirmasi.

Untuk mencapai tujuan, disamping membuat sebuah [_action_](structure-controllers.md), dan
dua [_view_](structure-views.md), anda juga harus membuat [_model_](structure-models.md).

Sepanjang tutorial ini, anda akan mempelajari bagaimana cara untuk:

* Membuat sebuah [model](structure-models.md) sebagai representasi data yang diinput oleh pengguna melalui form,
* Membuat _rules_ untuk memvalidasi data yang telah diinput.
* Membuat form HTML di dalam [view](structure-views.md).

Membuat Model <span id="creating-model"></span>
----------------

Data yang akan diambil dari pengguna akan direpresentasikan oleh class model `EntryForm` sebagaimana ditunjukkan di bawah dan
di simpan pada file `models/EntryForm.php`. Silahkan membaca bagian [Class Autoloading](concept-autoloading.md)
untuk penjelasan lengkap mengenai penamaan file class.

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;

class EntryForm extends Model
{
    public $name;
    public $email;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
        ];
    }
}
```

Class di _extends_ dari [[yii\base\Model]], class standar yang disediakan oleh Yii, yang secara umum digunakan
untuk representasi data dari form.

> Info: [[yii\base\Model]] digunakan sebagai _parent_ untuk class model yang tidak berhubungan dengan database.
  [[yii\db\ActiveRecord]] normalnya digunakan sebagai _parent_ untuk class model yang berhubungan dengan tabel di database.

Class `EntryForm` terdiri dari dua _public property_, `name` dan `email`, dimana akan digunakan untuk menyimpan
data yang diinput oleh pengguna. Class ini juga terdapat _method_ yang dinamakan `rules()`, yang akan mengembalikan (_return_) sejumlah
pengaturan (_rules_) untuk memvalidasi data. Pengaturan validasi (_Validation Rules_) yang di deklarasikan harus mendeskripsikan bahwa

* kedua field, yaitu `name` and `email` wajib di input
* data `email` harus merupakan alamat email yang valid

Jika anda memiliki objek `EntryForm` yang sudah mengandung data yang di input oleh pengguna, anda boleh memanggil
method [[yii\base\Model::validate()|validate()]] untuk melaksanakan validasi data. Kegagalan validasi data
akan menentukan (_set_) property [[yii\base\Model::hasErrors|hasErrors]] menjadi `true`, dan anda dapat mengetahui pesan kegagalan validasi
melalui [[yii\base\Model::getErrors|errors]].

```php
<?php
$model = new EntryForm();
$model->name = 'Qiang';
$model->email = 'bad';
if ($model->validate()) {
    // Valid!
} else {
    // Tidak Valid!
    // Panggil $model->getErrors()
}
```


Membuat Action <span id="creating-action"></span>
------------------

Selanjutnya, anda harus membuat `entry` _action_ pada controller `site` yang akan memanfaatkan model yang baru saja dibuat. Proses
membuat dan menggunakan _action_ dijelaskan pada bagian [Mengatakan Hello](start-hello.md).

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\EntryForm;

class SiteController extends Controller
{
    // ...kode lain...

    public function actionEntry()
    {
        $model = new EntryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // data yang valid diperoleh pada $model

            // lakukan sesuatu terhadap $model di sini ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // menampilkan form pada halaman, ada atau tidaknya kegagalan validasi tidak masalah
            return $this->render('entry', ['model' => $model]);
        }
    }
}
```

Pertama-tama, _action_ membuat objek `EntryForm`. Kemudian objek tersebut membangun model
menggunakan data dari `$_POST`, yang disediakan oleh Yii dengan method [[yii\web\Request::post()]].
Jika model berhasil dibuat (misal, jika pengguna telah mengirim form HTML), _action_ akan memanggil method
[[yii\base\Model::validate()|validate()]] untuk memastikan data yang di input tersebut valid.

> Info : _Expression_ `Yii::$app` adalah representasi dari objek [aplikasi](structure-applications.md),
  dimana objek tersebut adalah _singleton_ yang bebas diakses secara global. Objek tersebut juga merupakan [service locator](concept-service-locator.md) yang
  menyediakan _components_ seperti `request`, `response`, `db`, dll. untuk mendukung pekerjaan yang spesifik.
  Pada kode di atas, _component_ `request` dari objek aplikasi digunakan untuk mengakses data `$_POST`.

Jika tidak ada error, _action_ akan me-_render_ _view_ bernama `entry-confirm` untuk menginformasikan ke pengguna bahwa pengiriman
data tersebut berhasil. Jika tidak ada data yang dikirim atau data tersebut tidak valid, _view_ `entry` yang akan di _render_,
dimana form HTML akan ditampilkan, beserta informasi kegagalan pengiriman form tersebut.

> Note: Pada contoh sederhana ini kita hanya me-_render_ halaman konfirmasi jika data yang dikirim tersebut valid. Pada prakteknya,
  anda harus pertimbangkan untuk menggunakan [[yii\web\Controller::refresh()|refresh()]] atau [[yii\web\Controller::redirect()|redirect()]]
  untuk mencegah [permasalahan pengiriman form](http://en.wikipedia.org/wiki/Post/Redirect/Get).


Membuat View <span id="creating-views"></span>
--------------

Terakhir, buatlah dua file _view_ dengan nama `entry-confirm` dan `entry`. _View_ ini akan di-_render_ oleh _action_ `entry`,
yang sebelumnya dibahas.

_View_ `entry-confirm` hanya menampilkan data nama dan email. File _view_ tersebut harus di simpan di `views/site/entry-confirm.php`.

```php
<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->email) ?></li>
</ul>
```

_View_ `entry` akan menampilkan form HTML. File _view_ tersebut harus di simpan di `views/site/entry.php`.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'email') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>
```

_View_ ini menggunakan [_widget_](structure-widgets.md) yaitu [[yii\widgets\ActiveForm|ActiveForm]] untuk
membangun form HTML. _Method_ `begin()` dan `end()` dari _widget_ masing-masing berfungsi untuk me-_render_ tag pembuka dan penutup
dari form tag. Diantara dua method tersebut, akan dibuat field input oleh
method [[yii\widgets\ActiveForm::field()|field()]]. Input field yang pertama diperuntukkan untuk data "name",
dan yang kedua diperuntukkan untuk data "email". Setelah field input, _method_ [[yii\helpers\Html::submitButton()]]
akan dipanggil untuk me-_render_ tombol pengiriman data.


Mari kita uji <span id="trying-it-out"></span>
-------------

Untuk melihat bagaimana prosesnya, gunakan browser anda untuk mengakses URL ini :

```
http://hostname/index.php?r=site%2Fentry
```

Anda akan melihat halaman yang menampilkan form dengan dua field input. Dibagian atas dari semua input field, ada label yang menginformasikan data yang mana yang akan diinput. Jika anda menekan tombol pengiriman data tanpa
menginput apapun, atau anda tidak menginput email address yang tidak valid, anda akan melihat pesan kegagalan yang di tampilkan di bagian bawah field input yang bermasalah.

![Form yang validasinya gagal](images/start-form-validation.png)

Setelah menginput nama dan alamat email yang benar dan menekan tombol kirim, anda akan melihat halaman baru
yang menampilkan data yang barusan anda input.

![Konfirmasi penginputan data](images/start-entry-confirmation.png)



### Penjelasan <span id="magic-explained"></span>

Anda mungkin bertanya-tanya bagaimana form HTML bekerja dibelakang layar, sepertinya tampak ajaib karna form tersebut mampu
menampilkan label di setiap field input dan menampilkan pesan kegagalan jika anda tidak menginput data dengan benar
tanpa me-_reload_ halaman.

Betul, validasi data sebenarnya dilakukan di sisi klien menggunakan Javascript, dan selanjutnya dilakukan lagi di sisi server menggunakan PHP.
[[yii\widgets\ActiveForm]] cukup cerdas untuk menerjemahkan pengaturan validasi yang anda deklarasikan pada class `EntryForm`,
kemudian merubahnya menjadi kode Javascript, dan menggunakan Javascript untuk melakukan validasi data. Jika saja anda menonaktifkan
Javascript pada browser anda, validasi tetap akan dilakukan di sisi server, sepertinya yang ditunjukkan pada
_method_ `actionEntry`. Hal ini memastikan bahwa data akan divalidasi dalam segala kondisi.

> Warning: Validasi melalui sisi klien akan membuat pengalaman pengguna lebih baik. Validasi di sisi server
  harus selalu dilakukan, walaupun validasi melalui sisi klien digunakan atau tidak.

Label untuk field input dibuat oleh method `field()`, menggunakan nama _property_ dari model.
Contoh, label `Name` akan dibuat untuk _property_ `name`.

Anda boleh memodifikasi label di dalam view menggunakan
kode seperti di bawah ini:

```php
<?= $form->field($model, 'name')->label('Your Name') ?>
<?= $form->field($model, 'email')->label('Your Email') ?>
```

> Info: Yii menyediakan banyak _widget_ untuk membantu anda dalam membangun _view_ yang kompleks dan dinamis.
  Sebentar lagi anda akan mengetahui, bahwa menulis _widget_ juga sangat mudah. Anda mungkin akan mengganti sebagian besar
  dari kode _view_ anda menjadi _widget-widget_ yang mampu digunakan ulang untuk menyederhanakan penulisan _view_ ke depannya.


Rangkuman <span id="summary"></span>
-------

Pada bagian kali ini, anda telah mengetahui semua bagian dari pola arsitektur MVC. Anda sudah mempelajari bagaimana
untuk membuat class model sebagai representasi data pengguna dan memvalidasinya.

Anda juga mempelajari bagaimana mengambil data dari pengguna dan bagaimana menampilkan kembali data tersebut ke browser. Pekerjaan seperti ini
biasanya memakan waktu lama pada saat mengembangkan aplikasi, tetapi Yii menyediakan _widget_ yang bermanfaat
yang akan membuat pekerjaan ini menjadi lebih mudah.

Di bagian selanjutnya, anda akan mempelajari bagaimana untuk bekerja dengan database, dimana hal tersebut hampir sangat dibutuhkan pada setiap aplikasi.
