Katakan Hello
============

Bagian ini menjelaskan cara membuat halaman "Hello" baru dalam aplikasi Anda.
Untuk mencapai tujuan ini, Anda akan membuat [action](structure-controllers.md#creating-actions) dan
sebuah [view](structure-views.md):

* Aplikasi ini akan mengirimkan permintaan halaman ke `action`.
* Dan `action` pada gilirannya akan membuat tampilan yang menunjukkan kata "Hello" kepada pengguna akhir.

Melalui tutorial ini, Anda akan belajar tiga hal:

1. Cara membuat [action](structure-controllers.md#creating-actions) untuk menanggapi permintaan,
2. Cara membuat [view](structure-views.md) untuk menyusun konten respon, dan
3. bagaimana aplikasi mengirimkan permintaan ke [action](structure-controllers.md#creating-actions).


Membuat Action <span id="creating-action"></span>
---------------

Untuk tugas "Hello", Anda akan membuat [action](structure-controllers.md#creating-actions) `say` yang membaca
parameter `message` dari request dan menampilkan pesan bahwa kembali ke pengguna. Jika request
tidak memberikan parameter `message`, aksi akan menampilkan pesan "Hello".

> Info: [Action](structure-controllers.md#creating-actions) adalah objek yang pengguna akhir dapat langsung merujuk ke
  eksekusi. Action dikelompokkan berdasarkan [controllers](structure-controllers.md). Hasil eksekusi
  action adalah respon yang pengguna akhir akan terima.

Action harus dinyatakan di [controllers](structure-controllers.md). Untuk mempermudah, Anda mungkin
mendeklarasikan action `say` di` SiteController` yang ada. kontroler ini didefinisikan
dalam file kelas `controllers/SiteController.php`. Berikut adalah awal dari action baru:

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...existing code...

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

Pada kode di atas, action `say` didefinisikan sebagai metode bernama` actionSay` di kelas `SiteController`.
Yii menggunakan awalan `action` untuk membedakan metode action dari metode non-action dalam kelas controller.
Nama setelah awalan `action` peta untuk ID tindakan ini.

Untuk sampai pada penamaan action, Anda harus memahami bagaimana Yii memperlakukan ID action. ID action selalu
direferensikan dalam huruf kecil. Jika ID tindakan membutuhkan beberapa kata, mereka akan digabungkan dengan `tanda hubung`
(Mis, `create-comment`). nama metode aksi yang dipetakan ke ID tindakan diperoleh dengan menghapus tanda hubung apapun dari ID,
mengkapitalkan huruf pertama di setiap kata, dan awalan string yang dihasilkan dengan `action`. Sebagai contoh,
ID action `create-comment` sesuai dengan nama method action `actionCreateComment`.

Metode action dalam contoh kita mengambil parameter `$message`, yang nilai defaultnya adalah `"Hello"` (persis
dengan cara yang sama Anda menetapkan nilai default untuk fungsi atau metode apapun argumen di PHP). Ketika aplikasi
menerima permintaan dan menentukan bahwa action `say` bertanggung jawab untuk penanganan request, aplikasi akan
mengisi parameter ini dengan parameter bernama sama yang ditemukan dalam request. Dengan kata lain, jika permintaan mencakup
a parameter `message` dengan nilai` "Goodbye" `, maka variabel `$message` dalam aksi akan ditugaskan nilai itu.

Dalam metode action, [[yii\web\Controller::render()|render()]] dipanggil untuk membuat
sebuah [view](structure-views.md) dari file bernama `say`. Parameter `message` juga diteruskan ke view
sehingga dapat digunakan di sana. Hasil render dikembalikan dengan metode tindakan. Hasil yang akan diterima
oleh aplikasi dan ditampilkan kepada pengguna akhir di browser (sebagai bagian dari halaman HTML yang lengkap).


Membuat View <span id="creating-view"></span>
---------------

[View](structure-views.md) adalah skrip yang Anda tulis untuk menghasilkan konten respon.
Untuk "Hello" tugas, Anda akan membuat view `say` yang mencetak parameter `message` yang diterima dari metode aksi:

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

View `say` harus disimpan dalam file `views/site/say.php`. Ketika metode [[yii\web\Controller::render()|render()]]
disebut dalam tindakan, itu akan mencari file PHP bernama `views/ControllerID/ViewName.php`.

Perhatikan bahwa dalam kode di atas, parameter `message` adalah di-[[yii\helpers\Html::encode()|HTML-encoded]]
sebelum dicetak. Hal ini diperlukan karena sebagai parameter yang berasal dari pengguna akhir, sangat rentan terhadap
[serangan Cross-site scripting (XSS)](http://en.wikipedia.org/wiki/Cross-site_scripting) dengan melekatkan
kode JavaScript berbahaya dalam parameter.

Tentu, Anda dapat menempatkan lebih banyak konten di view `say`. konten dapat terdiri dari tag HTML, teks biasa, dan bahkan pernyataan PHP.
Nyatanya, view `say` hanyalah sebuah script PHP yang dijalankan oleh metode [[yii\web\Controller::render()|render()]].
Isi dicetak oleh skrip view akan dikembalikan ke aplikasi sebagai hasil respon ini. Aplikasi ini pada gilirannya akan mengeluarkan hasil ini kepada pengguna akhir.


Trying it Out <span id="trying-it-out"></span>
-------------

Setelah membuat action dan view, Anda dapat mengakses halaman baru dengan mengakses URL berikut:

```
http://hostname/index.php?r=site%2Fsay&message=Hello+World
```

![Hello World](images/start-hello-world.png)

URL ini akan menghasilkan halaman yang menampilkan "Hello World". Halaman yang berbagi header dan footer yang sama dengan halaman aplikasi lainnya.

Jika Anda menghilangkan parameter `message` dalam URL, Anda akan melihat tampilan halaman "Hello". Hal ini karena `message` dilewatkan sebagai parameter untuk metode `actionSay()`, dan ketika itu dihilangkan,
nilai default `"Hello"` akan digunakan sebagai gantinya.

> Info: Halaman baru berbagi header dan footer yang sama dengan halaman lain karena metode [[yii\web\Controller::render()|render()]]
  otomatis akan menanamkan hasil view `say` kedalam apa yang disebut [layout](structure-views.md#layouts) yang dalam hal ini
  Kasus terletak di `views/layouts/main.php`.

Parameter `r` di URL di atas memerlukan penjelasan lebih lanjut. Ini adalah singkatan dari [route](runtime-routing.md), sebuah ID unik aplikasi
yang mengacu pada action. format rute ini adalah `ControllerID/ActionID`. Ketika aplikasi menerima
permintaan, itu akan memeriksa parameter ini, menggunakan bagian `ControllerID` untuk menentukan kontroler
kelas harus dipakai untuk menangani permintaan. Kemudian, controller akan menggunakan bagian `ActionID`
untuk menentukan action yang harus dipakai untuk melakukan pekerjaan yang sebenarnya. Dalam contoh kasus ini, rute `site/say`
akan diselesaikan dengan kontroler kelas `SiteController` dan action `say`. Sebagai hasilnya,
metode `SiteController::actionSay()` akan dipanggil untuk menangani permintaan.

> Info: Seperti action, kontroler juga memiliki ID yang unik mengidentifikasi mereka dalam sebuah aplikasi.
  ID kontroler menggunakan aturan penamaan yang sama seperti ID tindakan. nama kelas controller yang berasal dari
  kontroler ID dengan menghapus tanda hubung dari ID, memanfaatkan huruf pertama di setiap kata,
  dan suffixing string yang dihasilkan dengan kata `Controller`. Misalnya, controller ID `post-comment` berkorespondensi
  dengan nama kelas controller `PostCommentController`.


Ringkasan <span id="summary"></span>
-------

Pada bagian ini, Anda telah menyentuh controller dan melihat bagian dari pola arsitektur MVC.
Anda menciptakan sebuah action sebagai bagian dari controller untuk menangani permintaan khusus. Dan Anda juga menciptakan view
untuk menulis konten respon ini. Dalam contoh sederhana ini, tidak ada model yang terlibat. Satu-satunya data yang digunakan adalah parameter `message`.

Anda juga telah belajar tentang rute di Yii, yang bertindak sebagai jembatan antara permintaan pengguna dan tindakan controller.

Pada bagian berikutnya, Anda akan belajar cara membuat model, dan menambahkan halaman baru yang berisi bentuk HTML.
