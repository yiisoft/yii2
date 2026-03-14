# Upgrade dari Versi 1.1

Ada banyak perbedaan antara versi 1.1 dan 2.0 karena Yii Framework benar-benar ditulis ulang di versi 2.0.
Akibatnya, upgrade dari versi 1.1 tidak mudah seperti upgrade untuk versi minor. Dalam panduan ini Anda akan
menemukan perbedaan utama antara dua versi.

Jika Anda belum pernah menggunakan Yii 1.1 sebelumnya, Anda dapat dengan aman melewati bagian ini dan menuju ke "[Persiapan](start-installation.md)".

Harap dicatat bahwa Yii 2.0 memperkenalkan lebih banyak fitur baru dari yang tercakup dalam ringkasan ini. Sangat dianjurkan
Anda membaca keseluruhan panduan definitif untuk mempelajari hal tersebut. Ada kemungkinan bahwa
beberapa fitur yang sebelumnya harus anda kembangkan sendiri, kini telah menjadi bagian dari kode inti.

## Instalasi

Yii 2.0 sepenuhnya menggunakan [composer](https://getcomposer.org/), yaitu dependency manager yang sudah diakui oleh PHP.

-   Instalasi dari kerangka inti serta ekstensi, ditangani melalui Composer.
-   Silakan merujuk ke bagian [Instalasi Yii](start-installation.md) untuk belajar cara menginstal Yii 2.0.
-   Jika ingin membuat ekstensi baru, atau mengubah/memperbarui ekstensi 1.1 yang telah Anda buat ke ekstensi 2.0 (agar kompatibel), silakan merujuk pada panduan [Membuat Ekstensi](structure-extensions.md#menciptakan-ekstensi).

## Persyaratan PHP

Yii 2.0 membutuhkan PHP 5.4 atau versi yang lebih tinggi, ini karena ada perubahan besar atas PHP versi 5.2 yang sebelumnya dibutuhkan oleh Yii 1.1. Akibatnya, ada banyak perbedaan pada tingkat bahasa yang harus Anda perhatikan.
Dibawah ini ringkasan perubahan utama mengenai PHP tersebut:

-   [Namespaces](https://www.php.net/manual/en/language.namespaces.php).
-   [Anonymous fungsi](https://www.php.net/manual/en/functions.anonymous.php).
-   Sintaks array pendek `[... elemen ...]` digunakan sebagai pengganti `array (... elemen ...)`.
-   Tags echo pendek `<=` digunakan dalam tampilan file. Ini aman digunakan mulai dari PHP 5.4.
-   [Class SPL dan interface](https://www.php.net/manual/en/book.spl.php).
-   [Late Static Bindings](https://www.php.net/manual/en/language.oop5.late-static-bindings.php).
-   [Tanggal dan Waktu](https://www.php.net/manual/en/book.datetime.php).
-   [Traits](https://www.php.net/manual/en/language.oop5.traits.php).
-   [Intl](https://www.php.net/manual/en/book.intl.php). Yii 2.0 menggunakan `ekstensi PHP intl`
      untuk mendukung fitur internasionalisasi.

## Namespace

Perubahan yang paling terlihat jelas dalam Yii 2.0 adalah penggunaan namespace. Hampir setiap kelas inti
menggunakan namespace, misalnya, `yii\web\Request`. Awalan yang sebelumnya menggunakan huruf "C" tidak lagi
digunakan dalam nama kelas. Skema penamaan sekarang mengikuti struktur direktori. Misalnya, `yii\web\Request`
mengindikasikan bahwa file kelasnya adalah `web/Request.php` sesuai dengan lokasi folder framework Yii yang ada.

(Anda dapat menggunakan setiap kelas inti tanpa menyertakannya secara eksplisit berkat Yiiclass loader.)

## Komponen dan Object

Yii 2.0 membagi kelas `CComponent` di 1.1 menjadi dua kelas: [[yii\base\BaseObject]] dan [[yii\base\Component]].
Class [[yii\base\BaseObject|BaseObject]] adalah class dasar ringan yang memungkinkan mendefinisikan [objek properti](concept-properties.md)
melalui getter dan setter. Class [[yii\base\Component|Component]] adalah perluasan(extends) dari [[yii\base\BaseObject|BaseObject]] dengan dukungan [Event](concept-events.md) dan [Behavior](concept-behaviors.md).

Jika class Anda tidak memerlukan fitur event atau behavior, Anda harus mempertimbangkan menggunakan
[[yii\base\BaseObject|BaseObject]] sebagai class dasar. Hal ini biasanya terjadi untuk class yang mewakili
struktur data dasar.

## Konfigurasi objek

Class [[yii\base\BaseObject|BaseObject]] memperkenalkan cara seragam untuk mengkonfigurasi objek. Setiap class turunan
dari [[yii\base\BaseObject|BaseObject]] harus menyatakan konstruktor (jika diperlukan) dengan cara berikut agar
dapat dikonfigurasi dengan benar:

```php
class MyClass extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... inisialisasi sebelum konfigurasi diterapkan

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... inisialisasi setelah konfigurasi diterapkan
    }
}
```

Dalam contoh diatas, parameter terakhir dari konstruktor harus mengambil array konfigurasi (`$config`)
yang berisi pasangan nama-nilai `[key => value]` untuk meng-inisialisasi properti pada akhir konstruktor.
Anda dapat mengganti method [[yii\base\BaseObject::init()|init()]] untuk melakukan inisialisasi yang harus dilakukan setelah
konfigurasi diterapkan.

Dengan mengikuti ketentuan ini, Anda akan dapat membuat dan mengkonfigurasi objek baru menggunakan array konfigurasi:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

Rincian lebih lanjut mengenai konfigurasi dapat ditemukan pada bagian [Konfigurasi](concept-configurations.md).

## Event

Di Yii 1, event dibuat dengan mendefinisikan method `on` (misalnya,`onBeforeSave`). Di Yii 2, Anda sekarang dapat menggunakan semua nama sebagai event.
Untuk memicu suatu event, Anda dapat melakukannya dengan memanggil method [[yii\base\Component::trigger()|trigger()]]:

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

Untuk melekatkan/memasang handler pada suatu event, dapat dilakukan dengan mengunakan method [[yii\base\Component::on()|on()]]:

```php
$component->on($eventName, $handler);
// Untuk mematikannya dapat dilakukan dengan cara:
// $component->off($eventName, $handler);
```

Ada banyak pengembangan dari fitur event. Untuk lebih jelasnya, silakan lihat pada bagian [Event](concept-events.md).

## Path Alias

Yii 2.0 memperluas penggunaan alias path baik untuk file/direktori maupun URL. Yii 2.0 juga sekarang mensyaratkan
nama alias dimulai dengan karakter `@`.
Misalnya, alias `@yii` mengacu pada direktori instalasi Yii. Alias path
didukung di sebagian besar tempat di kode inti Yii. Misalnya, [[yii\caching\FileCache::cachePath]] dapat mengambil
baik alias path maupun direktori normal.

Sebuah alias juga terkait erat dengan namespace kelas. Disarankan alias didefinisikan untuk setiap akar namespace,
sehingga memungkinkan Anda untuk menggunakan autoloader class Yii tanpa konfigurasi lebih lanjut.
Misalnya, karena `@yii` mengacu pada direktori instalasi Yii, class seperti `yii\web\Request` dapat otomatis diambil.
Jika Anda menggunakan librari pihak ketiga seperti Zend Framework. Anda dapat menentukan alias path `@Zend` yang mengacu pada
direktori instalasi framework direktori. Setelah Anda selesai melakukannya, Yii akan dapat menload setiap class dalam librari Zend Framework.

Lebih jauh tentang alias path dapat pelajari pada bagian [Alias](concept-aliases.md).

## View

Perubahan yang paling signifikan tentang view di Yii 2 adalah bahwa variabel khusus `$this` dalam sebuah view tidak lagi mengacu
controller saat ini atau widget. Sebaliknya, `$this` sekarang mengacu pada objek _view_, konsep baru
yang diperkenalkan di 2.0. Objek _view_ adalah [[yii\web\View]], yang merupakan bagian view
dari pola MVC. Jika Anda ingin mengakses controller atau widget di tampilan, Anda dapat menggunakan `$this->context`.

Untuk membuat tampilan parsial dalam view lain, Anda menggunakan `$this->render()`, tidak lagi `$this->renderPartial()`.
Panggilan untuk `render` juga sekarang harus secara eksplisit _di-echo)-kan_, mengingat method `render()` sekarang mengembalikan nilai yang dirender, bukan langsung menampilkannya. Sebagai contoh:

```php
echo $this->render('_item', ['item' => $item]);
```

Selain menggunakan PHP sebagai bahasa template utama, Yii 2.0 juga dilengkapi dengan dukungan resmi
dua _template engine_ populer: Smarty dan Twig. _Template engine_ Prado tidak lagi didukung.
Untuk menggunakan mesin template ini, Anda perlu mengkonfigurasi komponen aplikasi `view` dengan menetapkan
properti [[yii\base\View::$renderers|View::$renderers]]. Silakan merujuk ke bagian [Template Engine](tutorial-template-engines.md)
untuk lebih jelasnya.

## Model

Yii 2.0 menggunakan [[yii\base\Model]] sebagai model dasar, mirip dengan `CModel` di 1.1.
class `CFormModel` telah dibuang seluruhnya. Sebaliknya, di Yii 2 Anda harus memperluas [[yii\base\Model]] untuk membuat class model formulir.

Yii 2.0 memperkenalkan metode baru yang disebut [[yii\base\Model::scenario()|scenario()]] untuk menyatakan
skenario yang didukung, dan untuk menunjukkan di mana skenario atribut perlu divalidasi serta atribut yang dapat dianggap sebagai aman atau tidak
dll Sebagai contoh:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

Dalam contoh di atas, dua skenario dinyatakan: `backend` dan` frontend`. Untuk `skenario backend`, baik
atribut `email` maupun` role` aman dan dapat diassign secara masal. Untuk `skenario frontend`,
`email` dapat diassign secara masal sementara` role` tidak bisa. Kedua `email` dan` role` harus divalidasi sesuai aturan.

Method [[yii\base\Model::rules()|rules()]] ini masih digunakan untuk menyatakan aturan validasi. Perhatikan bahwa dengan dikenalkannya
[[yii\base\Model::scenario()|scenario()]] sekarang tidak ada lagi validator `unsafe`.

Dalam kebanyakan kasus, Anda tidak perlu menimpa [[yii\base\Model::scenario()|scenario()]]
jika method [[yii\base\Model::rules()|rules()]] sepenuhnya telah menentukan skenario yang akan ada dan jika tidak ada kebutuhan untuk menyatakan
atribut `unsafe`.

Untuk mempelajari lebih lanjut tentang model, silakan merujuk ke bagian [Model](structure-models.md).

## Controller

Yii 2.0 menggunakan [[yii\web\Controller]] sebagai kelas dasar controller, yang mirip dengan `CController` di Yii 1.1.
[[Yii\base\Action]] adalah kelas dasar untuk kelas action.

Dampak paling nyata dari perubahan ini pada kode Anda adalah bahwa aksi kontroler harus mengembalikan nilai konten
alih-alih menampilkannya:

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Silakan merujuk ke bagian [Controller](structure-controllers.md) untuk rincian lebih lanjut tentang controller.

## widget

Yii 2.0 menggunakan [[yii\base\Widget]] sebagai kelas dasar widget, mirip dengan `CWidget` di Yii 1.1.

Untuk mendapatkan dukungan yang lebih baik untuk kerangka di IDE, Yii 2.0 memperkenalkan sintaks baru untuk menggunakan widget.
Metode statis [[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]], dan [[yii\base\Widget::widget()|widget()]]
mulai diperkenalkan, yang akan digunakan seperti:

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Perlu diingat bahwa "echo" tetap diperlukan untuk menampilkan hasilnya
echo Menu::widget(['items' => $items]);

// Passing an array to initialize the object properties
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... form input fields here ...
ActiveForm::end();
```

Silakan merujuk ke bagian [Widget](structure-widgets.md) untuk lebih jelasnya.

## Tema

Tema benar-benar bekerja secara berbeda di 2.0. Kini tema telah berdasarkan mekanisme pemetaan path yang memetakan
file sumber ke file tema. Misalnya, jika peta path untuk tema adalah
`['/web/views' => '/web/themes/basic']`, maka versi tema dari file view
`/web/views/site/index.php` akan menjadi `/web/themes/basic/site/index.php`. Untuk alasan ini, tema sekarang bisa
diterapkan untuk setiap file view, bahkan view diberikan di luar controller ataupun widget.

Juga, tidak ada lagi komponen `CThemeManager`. Sebaliknya, `theme` adalah properti dikonfigurasi dari komponen `view`
pada aplikasi.

Silakan merujuk ke bagian [Theming](output-theming.md) untuk lebih jelasnya.

## Aplikasi konsol (CLI)

Aplikasi konsol sekarang diatur sebagai controller seperti pada aplikasi Web. kontroler konsol
harus diperluas dari [[yii\console\Controller]], mirip dengan `CConsoleCommand` di 1.1.

Untuk menjalankan perintah konsol, menggunakan `yii <route>`, di mana `<route>` adalah rute kontroler
(Misalnya `sitemap/index`). Argumen anonim tambahan dilewatkan sebagai parameter ke
action controller yang sesuai, sedangkan argumen bernama diurai menurut
deklarasi pada [[yii\console\Controller::options()]].

Yii 2.0 mendukung pembuatan informasi bantuan command secara otomatis berdasarkan blok komentar.

Silakan lihat bagian [Console Commands](tutorial-console.md) untuk lebih jelasnya.

## I18N

Yii 2,0 menghilangkan formater tanggal dan angka terpasang bagian dari [PECL modul intl PHP](https://pecl.php.net/package/intl).

Penterjemahan pesan sekarang dilakukan melalui komponen aplikasi `i18n`.
Komponen ini mengelola satu set sumber pesan, yang memungkinkan Anda untuk menggunakan pesan yang berbeda
sumber berdasarkan kategori pesan.

Silakan merujuk ke bagian [Internasionalisasi](tutorial-i18n.md) untuk rincian lebih lanjut.

## Action Filter

Action Filter sekarang diimplementasikan melalui behavior. Untuk membuat baru, filter diperluas dari [[yii\base\ActionFilter]].
Untuk menggunakan filter, pasang Kelas filter untuk controller sebagai behavior. Misalnya, untuk menggunakan filter [[yii\filters\AccessControl]],
Anda harus mengikuti kode berikut di kontroler:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

Silakan merujuk ke bagian [Filtering](structure-filters.md) untuk lebih jelasnya.

## Aset

Yii 2.0 memperkenalkan konsep baru yang disebut _bundel aset_ yang menggantikan konsep paket script di Yii 1.1.

Bundel aset adalah kumpulan file asset (misalnya file JavaScript, file CSS, file gambar, dll)
dalam direktori. Setiap bundel aset direpresentasikan sebagai kelas turunan dari [[yii\web\AssetBundle]].
Dengan mendaftarkan bundel aset melalui [[yii\web\AssetBundle::register()]], Anda membuat
aset dalam bundel diakses melalui Web. Tidak seperti di Yii 1, halaman yang mendaftarkan bundel akan secara otomatis
berisi referensi ke JavaScript dan file CSS yang ditentukan dalam bundel itu.

Silakan merujuk ke bagian [Managing Aset](structure-assets.md) untuk lebih jelasnya.

## Helper

Yii 2.0 memperkenalkan banyak helper umum untuk digunakan, termasuk.

-   [[yii\helpers\Html]]
-   [[yii\helpers\ArrayHelper]]
-   [[yii\helpers\StringHelper]]
-   [[yii\helpers\FileHelper]]
-   [[yii\helpers\Json]]

Silakan lihat bagian [Tinjauan Helper](helper-overview.md) untuk lebih jelasnya.

## Formulir

Yii 2.0 memperkenalkan konsep _field_ untuk membangun formulir menggunakan [[yii\widgets\ActiveForm]]. Field
adalah wadah yang terdiri dari label, masukan, pesan kesalahan, dan atau teks petunjuk.
Field diwakili sebagai objek [[yii\widgets\ActiveField|ActiveField]].
Menggunakan field, Anda dapat membangun formulir yang lebih bersih dari sebelumnya:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Silakan merujuk ke bagian [Membuat Formulir](input-forms.md) untuk lebih jelasnya.

## Query Builder

Dalam 1.1, query builder itu tersebar di antara beberapa kelas, termasuk `CDbCommand`,
`CDbCriteria`, dan` CDbCommandBuilder`. Yii 2.0 merepresentasikan sebuah query DB sebagai objek [[yii\db\Query|Query]]
yang dapat berubah menjadi sebuah pernyataan SQL dengan bantuan [[yii\db\QueryBuilder|QueryBuilder]].
Sebagai contoh:

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

Yang terbaik dari semua itu adalah, query builder juga dapat digunakan ketika bekerja dengan [Active Record](db-active-record.md).

Silakan lihat bagian [Query Builder](db-query-builder.md) untuk lebih jelasnya.

## Active Record

Yii 2.0 memperkenalkan banyak perubahan [Active Record](db-active-record.md). Dua yang paling jelas melibatkan
query builder dan penanganan permintaan relasional.

Kelas `CDbCriteria` di 1.1 digantikan oleh [[yii\db\ActiveQuery]] di Yii 2. Karena kelas tersebut adalah perluasan dari [[yii\db\Query]], dengan demikian
mewarisi semua metode query builder. Anda bisa memanggil [[yii\db\ActiveRecord::find()]] untuk mulai membangun query:

```php
// Untuk mengambil semua customer yang *active* diurutkan sesuai ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

Untuk menyatakan suatu relasi, hanya dengan menentukan metod getter yang mengembalikan sebuah objek [[yii\db\ActiveQuery|ActiveQuery]].
Nama properti yang didefinisikan oleh getter akan menjadi nama relasi. Misalnya, kode berikut mendeklarasikan
sebuah relasi `orders` (di 1.1, Anda akan harus menyatakan relasi di tempat `relations()`):

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

Sekarang Anda dapat menggunakan `$customer->orders` untuk mengakses pesanan pelanggan dari tabel terkait. Anda juga dapat menggunakan kode berikut
untuk melakukan permintaan relasi secara cepat dengan kondisi permintaan yang disesuaikan:

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

Ketika ingin memuat relasi, Yii 2.0 melakukannya secara berbeda dari 1.1. Secara khusus, di 1.1 query JOIN
akan dibuat untuk memilih data utama dan data relasi. Di Yii 2.0, dua pernyataan SQL dijalankan
tanpa menggunakan JOIN: pernyataan pertama membawa kembali data utama dan yang kedua membawa kembali data relasi
dengan menyaring sesuai kunci primer dari data utama.

Alih-alih mengembalikan objek [[yii\db\ActiveRecord|ActiveRecord]], Anda mungkin ingin menyambung dengan [[yii\db\ActiveQuery::asArray()|asArray()]]
ketika membangun query untuk mendapatkan sejumlah besar data. Hal ini akan menyebabkan hasil query dikembalikan
sebagai array, yang dapat secara signifikan mengurangi waktu CPU yang dibutuhkan dan memori jika terdapat sejumlah besar data. Sebagai contoh:

```php
$customers = Customer::find()->asArray()->all();
```

Perubahan lain adalah bahwa Anda tidak dapat menentukan nilai default atribut melalui properti publik lagi.
Jika Anda membutuhkan mereka, Anda harus mengatur mereka dalam metode init kelas record Anda.

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NEW;
}
```

Ada beberapa masalah dengan override konstruktor dari kelas ActiveRecord di 1.1. Ini tidak lagi hadir di
versi 2.0. Perhatikan bahwa ketika menambahkan parameter ke constructor Anda mungkin harus mengganti [[yii\db\ActiveRecord::instantiate()]].

Ada banyak perubahan lain dan perangkat tambahan untuk Rekaman Aktif. Silakan merujuk ke
bagian [Rekaman Aktif](db-active-record.md) untuk rincian lebih lanjut.

## Active Record Behaviors

Dalam 2.0, kami telah membuang kelas behavior dasar `CActiveRecordBehavior`. Jika Anda ingin membuat behavior Active Record,
Anda akan harus memperluasnya langsung dari `yii\base\Behavior`. Jika kelas behavior perlu menanggapi beberapa event
dari pemilik, Anda harus mengganti method `events()` seperti berikut ini,

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```

## Pengguna dan IdentityInterface

Kelas `CWebUser` di 1.1 kini digantikan oleh [[yii\web\User]], dan sekarang tidak ada lagi
Kelas `CUserIdentity`. Sebaliknya, Anda harus menerapkan [[yii\web\IdentityInterface]] yang
jauh lebih mudah untuk digunakan. Template proyek lanjutan memberikan contoh seperti itu.

Silakan merujuk ke bagian [Otentikasi](security-authentication.md), [Otorisasi](security-authorization.md),
dan [Template Proyek Lanjutan](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md) untuk lebih jelasnya.

## Manajemen URL

Manajemen URL di Yii 2 mirip dengan yang di 1.1. Tambahan utamanya adalah, sekarang manajemen URL mendukung opsional
parameter. Misalnya, jika Anda memiliki aturan dinyatakan sebagai berikut, maka akan cocok
baik dengan `post/popular` maupun `post/1/popular`. Dalam 1.1, Anda akan harus menggunakan dua aturan untuk mencapai
tujuan yang sama.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Silakan merujuk ke bagian [docs manajer Url](runtime-routing.md) untuk lebih jelasnya.

Perubahan penting dalam konvensi penamaan untuk rute adalah bahwa nama-nama camelcase dari controller
dan action sekarang dikonversi menjadi huruf kecil di mana setiap kata dipisahkan oleh hypen, misal controller
id untuk `CamelCaseController` akan menjadi `camel-case`.
Lihat bagian tentang [Kontroler ID](structure-controllers.md#controller-ids) dan [Action ID](structure-controllers.md#action-ids) untuk lebih jelasnya.

## Menggunakan Yii 1.1 dan 2.x bersama-sama

Jika Anda memiliki warisan kode Yii 1.1 yang ingin Anda gunakan bersama-sama dengan Yii 2.0, silakan lihat
bagian [Menggunakan Yii 1.1 dan 2.0 Bersama](tutorial-yii-integration.md).
