# Apa Itu Yii

Yii merupakan kerangka kerja berbasis komponen yang berkecepatan tinggi (high performance) yang digunakan untuk kasus
pengembangan yang cepat pada aplikasi Web Modern. Disebut Yii (diucapkan `Yee` atau `[ji:]`) yang berarti "sederhana dan
berevolusi" dalam bahasa Cina. Bisa juga dianggap sebagai (akronim) singkatan dari **Yes It Is (Ya, Itu Dia)**!

## Yii Paling Cocok Digunakan untuk Apa?

Yii adalah kerangka kerja pemrograman web yg umum(general), ini berarti kita bisa menggunakannya untuk membangun berbagai
macam aplikasi berbasis web menggunakan PHP. Karena merupakan arsitektur berbasis komponen yang mana memiliki dukungan
caching yang canggih, Yii sangat cocok untuk pengembangan aplikasi skala besar seperti web portal, forum, CMS, e-commerce,
REST API dan lain sebagainnya.

## Bagaimana jika Yii Dibandingkan dengan Frameworks lain?

Jika Anda sudah familiar dengan framework lain, Anda mungkin akan bersyukur ketika membandingkan Yii dengan yang lain:

-   Seperti kebanyakan PHP framework, Yii mengimplementasikan pola arsitektur MVC (Model-View-Controller) dan mempromosikan kode organisasi berdasarkan pola itu.
-   Yii mengambil filosofi bahwa kode harus ditulis dengan cara sederhana namun elegan. Yii tidak akan pernah mencoba untuk mendesain berlebihan terutama untuk mengikuti beberapa pola desain secara ketat.
-   Yii adalah fullstack framework yang menyediakan banyak fitur teruji dan siap pakai seperti: query builder dan ActiveRecord baik untuk relasional maupun NoSQL database; dukungan pengembangan REST API; dukungan caching berlapis dan masih banyak lagi.
-   Yii sangat extensible. Anda dapat menyesuaikan atau mengganti hampir setiap bagian dari kode inti Yii. Anda juga bisa mengambil keuntungan dari arsitektur ekstensi Yii yang solid untuk menggunakan atau mengembangkan ekstensi untuk disebarkan kembali.
-   Kinerja tinggi (High performance) selalu menjadi tujuan utama dari Yii.

Yii tidak dikerjakan oleh satu orang, Yii didukung oleh [tim pengembang inti yang kuat][yii_team], serta komunitas besar
profesional yang terus memberikan kontribusi bagi pengembangan Yii. Tim pengembang Yii terus mengamati perkembangan tren
terbaru Web, pada penerapan terbaik (best practices) serta fitur yang ditemukan dalam framework dan proyek lain.
Penerapan terbaik yang paling relevan dan fitur yang ditemukan di tempat lain secara teratur dimasukkan ke dalam kerangka inti
dan menampilkannya melalui antarmuka yang sederhana dan elegan.

[yii_team]: https://www.yiiframework.com/team

## Versi Yii

Yii saat ini memiliki dua versi utama yang tersedia: 1.1 dan 2.0. Versi 1.1 adalah generasi lama dan sekarang dalam mode pemeliharaan.
Versi 2.0 adalah penulisan ulang lengkap dari Yii, mengadopsi teknologi dan protokol terbaru, termasuk composer, PSR, namespace, trait, dan sebagainya.
Versi 2.0 merupakan generasi framework yang sekarang dan terus menerima upaya pengembangan selama beberapa tahun ke depan.
Panduan ini terutama tentang versi 2.0.

## Persyaratan dan Prasyarat

Yii 2.0 memerlukan PHP 5.4.0 atau versi lebih tinggi. Anda dapat menemukan persyaratan yang lebih rinci untuk setiap fitur
dengan menjalankan pengecek persyaratan yang diikutsertakan dalam setiap rilis Yii.

Menggunakan Yii memerlukan pengetahuan dasar tentang pemrograman berorientasi objek (OOP), mengingat Yii adalah framework berbasis OOP murni.
Yii 2.0 juga memanfaatkan fitur terbaru dari PHP, seperti [namespace](https://www.php.net/manual/en/language.namespaces.php) dan [traits](https://www.php.net/manual/en/language.oop5.traits.php).
Memahami konsep-konsep ini akan membantu Anda lebih mudah memahami Yii 2.0.
