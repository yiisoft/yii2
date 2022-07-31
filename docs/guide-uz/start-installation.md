Yiini o'rnatish <span id="installing-from-composer"></span>
==============

Siz Yii'ni ikki usulda o'rnatishingiz mumkin: [Composer](https://getcomposer.org/) - dan foydalanishingiz mumkin yoki arxivdan yuklab olib ishlatishingiz mumkin. Birinchi usul afzalroq bo'ladi, chunki u yangi [kengaytmalar](structure-extensions.md) o'rnatish yoki Yii'ni bitta buyruq bilan yangilash imkonini beradi. 


> Eslatma: Yii 1 dan farqli o'laroq, Yii2ni standart yo`li bilan o`rnatsangiz siz ham shabloni va freymvorkni o`rnatasiz. 


Composer bilan o'rnatish usuli<span id="installing-via-composer"></span>
-----------------------

### Composer-ni o'rnatish 

Agar Composer xali o'rnatilmagan bo'lsa, uni [getcomposer.org](https://getcomposer.org/download/) saytiga kirib ko'rsatmalarga rioya qilib o`rnatishingiz mumkin yoki quyidagi usullardan birini amalga oshirishingiz mumkin. Linux yoki Mac-da quyidagi buyruqni bajaring: 

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```
Windowsda [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)-ni yuklab oling va ishga tushiring.


Muammo bo'lsa, Composer hujjatida ["Muammo bartaraf qilish" bo'limini o'qing]((https://getcomposer.org/doc/articles/troubleshooting.md)). Agar siz faqat Composer dasturidan foydalanmoqchi bo'lsangiz, hech bo'lmasa ["Foydalanish asoslari"]((https://getcomposer.org/doc/01-basic-usage.md)) bo'limini o'qishni tavsiya etamiz. 

Ushbu qo'llanma `composer` global tizim boyicha umumiy o'rnatilganligini ta'kidlaydi. Ya'ni, bu `composer` buyrug'i orqali mavjud. Agar local (ichki) katalogdan `composer.phar` foydalanayotgan bo'lsangiz, buyruqlar mos ravishda o'zgartiring. 

Composer o'rnatilgan bo'lsa, uni composer `self-update`. 

> Eslatma: O'rnatish vaqtida Yii Composer Github API orqali juda ko'p miqdordagi ma'lumotlarni yulaydi.
>So'rovlar soni sizning loyihangizning boglanmalar soniga bog'liq bo'lib, **Github API** chegaralaridan oshib ketishi mumkin. Agar shunday bo'lsa qolsa, Composer Githubdan login va parolni so'raydi. Bu Github API uchun token olish uchun zarur.  Internet tezligi tez bo'lsa Composer xatolikni bartaraf qilishdan oldin ham sodir bo'lishi mumkin, shuning uchun Yii'ni o'rnatishdan avval kirish uchun tokeni o'rnatish tavsiya etamiz.
>Ko'rsatmalar [Composer qo`lanmasida Github API'sining identifikatorlari bo'yicha](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens) taqdim etiladi.

### Yiini o'rnatish

```bash
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

Ushbu buyruq Yining oxirgi ishlaydigan versiyasini `basic` katalogiga o'rnatadi. Agar xohlasangiz, boshqa katalog nomini tanlashingiz mumkin. 

> Info: Agar `composer create-project` buyrug'i yaxshi ishlamasa , Composer hujjatining ["Muammo bartaraf qilish"](https://getcomposer.org/doc/articles/troubleshooting.md) bo'limiga murojaat qiling . Yozib otilgan boshqa odatiy xatolar mavjud. Xatolikni bartaraf qilganingizdan so'ng, `basic` katalogda `composer update` ishga tushiring.

> Maslahat: Agar siz Yii ning eng so`ngi tekshirilmagan taxririni o'rnatmoqchi bo'lsangiz, [stability](https://getcomposer.org/doc/04-schema.md#minimum-stability) sozlamasini ozgartirib quydagi quyidagi buyruqni ishlatishingiz mumkin::
>
> ```bash
> composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
> ```
>
> Ishlab serverlarida Yii'ning tekshirilmagan taxrirlarini foydalanmaslikka harakat qiling, chunki bu taxrir tekshirilmagan kodi to'satdan xatolik kelib chiqishi mumkin. 


Arxivdan o'rnatish  <span id="installing-from-archive-file"></span>
-------------------------------

Yining arxivdan o'rnatilishi uch bosqichdan iborat:

1. Yiiframework.com dan [arxivni](https://www.yiiframework.com/download/) yuklab oling.
2. Yuklangan arxivni Internetdan kirib boladigan katalogiga tashlab, arxivni oching. 
3. `config/web.php` `cookieValidationKey` (Composer orqali o'rnatilganda, bu avtomatik tarzda amalga oshiriladi) uchun maxsus kalitni qo'shing:

```php
// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
'cookieValidationKey' => 'enter your secret key here',
```

Boshqacha o'rnatish sozlamalari  <span id="other-installation-options"></span>
--------------------------

Yuqorida, Yii'ni ishlatish uchun tayyor bo'lgan asosiy dastur sifatida o'rnatish bo'yicha ko'rsatmalar mavjud. Bu kichik loyihalar uchun yoki "Yii" ni o'rgana boshlaganlar uchun ajoyib variant. 

Bunday o'rnatish uchun ikkita asosiy variant mavjud: 

* Agar sizga faqatgina freymvork kerak bolsa va dasturni noldan yaratmoqchi bo'lsangiz, «[Noldan ilovani yaratish](tutorial-start-from-scratch.md)» bo'limida ko'rsatilgan ko'rsatmalardan foydalaning.
* Agar siz jamoaviy ish uchun juda kengaytirilgan taxriri bilan boshlamoqchi bolsangiz, [murakkab shabloni](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md) ishlatishingiz mumkin.


O`rnatilganligini tekshirish <span id="verifying-installation"></span>
----------------------

O`rnatilgandan so'ng, dastur quyidagi URLda mavjud bo'ladi: 

```
http://localhost/basic/web/index.php
```

Ushbu dasturni veb-serveringizning ildiz katalogidagi `basic` katalogiga o'rnatgan deb hisoblaydi, server ichki `(localhost)` ishlayapti. Uni oldindan sozlashingiz kerak bo'lishi mumkin. 
![O'rnatilgan Yii](images/start-app-installed.png)

Sizni "Tabriklaymiz!" Xush kelibsiz sahifasini ko'rishingiz kerak. Agar shunday bo'lmasa - Yii talablarini quyidagicha tekshiring: 

* Browserda quydagi sahifani oching `http://localhost/basic/requirements.php`
* Yoki quydagi buyrug'ni terminalda bajaring: 

```bash
cd basic
php requirements.php
```

Freymvork to'g'ri ishlashi uchun PHPni minimal talablarga javob beradigan tarzda sozlashingiz kerak. Asosiy talablardan biri bu PHP versiyasi 5.4 va undan yuqori bolishi kerak. Agar veb-ilovangiz ma'lumotlar bazasi bilan ishlayotgan bo'lsa , [PHP PDO kengaytmasini](https://www.php.net/manual/ru/pdo.installation.php) va tegishli drayverni (masalan, MySQL uchun pdo_mysql ) o'rnatishingiz kerak.


Web-server sozlamasi<span id="configuring-web-servers"></span>
-----------------------

> Info: Agar siz faqatgina freymvork bilan tanishishni boshlagan bo'lsangiz va uni ish  serveriga joylashtirmagan bo'lsangiz, ushbu bo'limni o'tkazib yuborishingiz mumkin. 

Yuqoridagi ko'rsatmalarga muvofiq o'rnatilgan ilovalar Windows va Linux ostida PHP 5.4 va undan yuqori taxrirlarda o'rnatilgan [Apache](https://httpd.apache.org/) va [Nginx](https://nginx.org/) bilan ishlaydi.Yii 2.0 [HHVM](https://hhvm.com/) bilan ham mos keladi. Etiborli bo'ling, ba'zi hollarda, HHVM bilan ishlashda odatdagi PHPdan farq qiladi.

Ish serverida siz `https://www.example.com/basic/web/index.php` dan `https://www.example.com/index.php` manziliga dastur URL manzilini o'zgartirishni xohlasangiz. 
Buni amalga oshirish uchun veb-server parametrlarida ildiz katalogini `basic/web` ga o'zgartiring. Bundan tashqari, ["URL sozlamalari"](runtime-routing.md) qismidagi malumotga ko'ra, `index.php` yashirishi mumkin. Keyinchalik Apache va Nginx ni qanday sozlashni ko'rsatamiz.

> Info: Veb-serverning `basic/web` ildiz katalogini o'rnatib, siz ruxsat berilmagan kirish kodidan va `basic/web` sahifadagi ma'lumotlardan himoya qilasiz. Bu ilovani yanada xavfsiz holga keltiradi. 

> Info: Agar dastur veb-server sozlamalariga kirish imkoni bo'lmagan hosting bilan ishlayotgan bo'lsa, siz ["Birgalikda Hosting ustida ishlash"](tutorial-shared-hosting.md) bo'limida ko'rsatilganidek, ilovaning tuzilishini o'zgartirishingiz mumkin.


### Ta'vsiya etilgan Apache sozlamalari <span id="recommended-apache-configuration"></span>

Quyidagilarni Apache `httpd.conf` yoki virtual sozlamalar faylga qo'shing. `path/to/basic/web` to'g'ri manzilga `path/to/basic/web` almashtirishni unutmang. 

```
# Ildiz kotalogini o`rnatayapmiz "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    #Agar so`ralayotgan fayl yoki katalogiy bor bo`lsa unga to`g`ridan to`g`ri murojat qilamiz 
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Agar yo`q bo`lsa index.php ga o`tqazib yuboramiz.
    RewriteRule . index.php

    # ...boshqa sozlamalar...
</Directory>
```


### Tavsiya etilgan Nginx sozlamalari <span id="recommended-nginx-configuration"></span>

PHP Nginx uchun [FPM SAPI](https://www.php.net/manual/ru/install.fpm.php) sifatida o'rnatilishi kerak. Quyidagi [Nginx](https://wiki.nginx.org/) sozlamalaridan foydalaning va `basic/web` va `mysite.test` to'g'ri manzilini hostname-ga almashtirishni  va `path/to/basic/web` ni almashtirishni unutmang. 

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## слушаем ipv6

    server_name mysite.test;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/project/log/access.log;
    error_log   /path/to/project/log/error.log;

    location / {
        # Barcha so'rovlarni indeks bo'lmagan katalog va fayllarga yo'naltiramiz. Index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # Yii-ni ishlamaslik uchun mavjud bo'lmagan statik fayllarni chaqirish uchun quyidagi qatorlarni belgilang
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Ushbu konfiguratsiya yordamida `php.ini` `cgi.fix_pathinfo=0` ni `stat()` tizimiga keraksiz tizim chaqiruvlariga yo'l qo'ymaslik uchun o'rnating. 
 
HTTPS-dan foydalanib, `fastcgi_param HTTPS on;` ni belgilash kerak `fastcgi_param HTTPS on;` Shunday qilib Yii xavfsiz ishlashini aniq belgilashi mumkin. 
