Apa Itu Yii
===========

Yii adalah kerangka kerja PHP berkinerja tinggi, berbasis komponen yang digunakan untuk mengembangkan aplikasi web modern dengan cepat.
Nama Yii (diucapkan `Yee` atau` [ji:] `) berarti" sederhana dan evolusi "dalam bahasa Cina. Hal ini dapat juga
dianggap sebagai singkatan **Yes It Is (Ya, Itu Dia)**!


Yii Terbaik untuk Apa?
---------------------

Yii adalah kerangka kerja pemrograman web umum, yang berarti bahwa hal itu dapat digunakan untuk mengembangkan semua jenis
aplikasi Web yang menggunakan PHP. Karena arsitektur berbasis komponen dan dukungan caching yang canggih,
 Yii sangat cocok untuk mengembangkan aplikasi skala besar seperti portal, forum, konten
sistem manajemen (CMS), proyek e-commerce, layanan web REST, dan sebagainya.


Bagaimana Yii Dibandingkan dengan Frameworks lain?
-------------------------------------------

Jika Anda sudah akrab dengan framework lain, Anda mungkin menghargai pengetahuan bagaimana Yii dibandingkan:

- Seperti kebanyakan PHP framework, Yii mengimplementasikan MVC (Model-View-Controller) pola arsitektur dan mempromosikan kode
  organisasi berdasarkan pola itu.
- Yii mengambil filosofi kode yang harus ditulis dengan cara sederhana namun elegan. Yii tidak akan pernah mencoba untuk
  mendesain berlebihan terutama untuk secara ketat mengikuti beberapa pola desain.
- Yii adalah framework penuh yang menyediakan banyak fitur teruji dan siap pakai seperti: query builder
  dan ActiveRecord baik untuk relasional maupun NoSQL database; dukungan pengembangan API REST; dukungan 
  caching banyak lapis dan masih banyak lagi.
- Yii sangat extensible. Anda dapat menyesuaikan atau mengganti hampir setiap bagian dari kode inti Yii. Anda juga bisa
  mengambil keuntungan dari arsitektur ekstensi Yii yang padat untuk menggunakan atau mengembangkan ekstensi untuk disebarkan kembali.
- Kinerja tinggi selalu tujuan utama dari Yii.

Yii bukan one-man show, Yii didukung oleh [tim pengembang inti yang kuat][about_yii], serta komunitas besar
profesional yang terus memberikan kontribusi bagi pengembangan Yii. Tim pengembang Yii
terus mengamati perkembangan tren terbaru Web, pada praktik terbaik serta fitur yang
ditemukan dalam framework dan proyek lain. Praktik terbaik yang paling relevan dan fitur yang ditemukan di tempat lain secara teratur 
dimasukkan ke dalam kerangka inti dan menampakkannya melalui antarmuka yang sederhana dan elegan.

[about_yii]: http://www.yiiframework.com/about/

Versi Yii
----------

Yii saat ini memiliki dua versi utama yang tersedia: 1.1 dan 2.0. Versi 1.1 adalah generasi tua dan sekarang dalam modus pemeliharaan. 
Versi 2.0 adalah penulisan ulang lengkap dari Yii, mengadopsi teknologi dan protokol terbaru, termasuk composer, PSR, namespace, trait, dan sebagainya. 
Versi 2.0 merupakan generasi framework yang sekarang dan terus menerima upaya pengembangan selama beberapa tahun ke depan.
Panduan ini terutama tentang versi 2.0.


Persyaratan dan Prasyarat
--------------------------

Yii 2.0 memerlukan PHP 5.4.0 atau lebih. Anda dapat menemukan persyaratan yang lebih rinci untuk setiap fitur
dengan menjalankan pengecek persyaratan yang diikutsertakan dalam setiap rilis Yii.

Menggunakan Yii memerlukan pengetahuan dasar tentang pemrograman berorientasi objek (OOP), mengingat Yii adalah framework berbasis OOP murni.
Yii 2.0 juga memanfaatkan fitur terbaru dari PHP, seperti [namespace](http://www.php.net/manual/en/language.namespaces.php) dan [traits](http://www.php.net/manual/en/language.oop5.traits.php). 
Memahami konsep-konsep ini akan membantu Anda lebih mudah memahami Yii 2.0.