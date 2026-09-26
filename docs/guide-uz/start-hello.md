«Salomlashamiz»
================

Ushbu bo'limda biz "Salom" so'zlari bilan yangi sahifani qanday yaratishni ko'rib chiqamiz. Siz masalani hal qilish jarayonida
[nazoratchi amalini](structure-controllers.md) va [ko`rinishi](structure-views.md):ni yaratasiz

* Web-ilova so'rovni bajaradi va nazoratni tegishli amalga o'tkazadi;
* Amal, o'z navbatida, "Salom" deb nomlangan ko'rinishni foydalanuvchiga ko'rsatadi.

Ushbu qo'llanmani o'rganish jarayonida siz ushbu narsalarni o'rganib chiqasiz:

* So'rovlarga javob berish uchun qanday [amalni](structure-controllers.md) yaratish kerak;
* Web ilovaga javob mazmunini shakllantirish uchun qanday qilib [ko`rinish](structure-views.md), yaratish kerak;
* Web ilova so'rovlarni qanday  qilib [amalga](structure-controllers.md). yuboradi.


Amal yaratish <span id="creating-action"></span>
------------------------------------------------


Bizning vazifamiz uchun so'rovdan `message` parametrini o'qiydigan va uning qiymatini foydalanuvchiga ko'rsatadigan `say` deb ataladigan [amal](structure-controllers.md) kerak.
Agar so'rovda `message` parametri bo'lmasa, [amal](structure-controllers.md) o'z novbatida "Salom" matnini qaytaradi.
> Info: [amal](structure-controllers.md) to'g'ridan-to'g'ri foydalanuvchi tomonidan boshqarilishi va [nazoratchilar](structure-controllers.md) ichida guruhlarga bo'linishi mumkin. Amal natijasi foydalanuvchiga qaytariladi.

Amallar [nazoratchilar](structure-controllers.md) (tomonidan) ichida e'lon qilinadi.
Oddiylik uchun siz mavjud bo'lgan SiteController nazoratchisi ichida `say` amalini yaratishingiz mumkin, Ushbu nazoratchi fayli `controllers/SiteControllers.php` yo'li bo`yicha joylashgan
```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...oldindan mavjud bo'lgan kod...

    public function actionSay($message = 'Salom')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

Quyidagi kodda `say` amali `SiteController` sinfi (klassi) `actionSay` usuli (metodi) e'lon qilinmoqda.
Yii amallari usullari (metodlari) oldidan `action` qo'shiladi bu oddiy usullarni (metodlarni) ajratish uchun prefiksidan foydalanadi.

> Info: Amalar nomlari kichik harflar berilishi kerak.Agar identifikator (nom) bir necha so'zdan iborat bo'lsa, ular
  chiziq (minus -) bilan ajiraladi, yani `create-comment`. Amallar usullarining (metodlar) nomlari defislarni olib tashlash yo'li bilan olinib, har bir so'zni katta harfga aylantiradi va `action` prefiksi qo'shadi.
  Misol uchun, amal nomi `create-comment` quydagi usullga to'g'ri keladi `actionCreateComment`.

Yukoridagi amal - `$message` parametrni qabul qiladi u esa, oldindan (agar hech narsa kiritilmasa) `"Salom"` soziga teng. Web ilova sorovni qabul qilib
`say` amali unga javob berishini aniqlaganda, amal parametrlari va so'rovdan kelgan ma'lumotlar parametrlar ichida sorovdagi nomlarga teng bo`lgani topib bir xil ma'lumot bilan to'ldiradi.

Amal usulida (metodida), kerakli ko'rinishni ko'rsatish uchun [[yii\web\Controller::render()|render()]] usuli (metodi) ishlatiladi. Xabarni foydalanuvchiga ko'rsatish uchun, ko'rinishga `message` parametri beriladi.
ko'rinish yaratgan ma'lumotni `return` orqali web ilovaga qaytaradi, u o'z novbatida ma'lumotni foydalanuvchiga qaytaradi.


Ko'rinish yaratish<span id="creating-view"></span>
---------------------------------------------------

[ko'rinish](structure-views.md) - bu foydalanuvchi uchun javob qaytarish skriptlar. Yuqorida dastur uchun siz `say` ko'rinishini yaratishingiz kerak, u o'z ichiga `message` o'zgaruvchini uni chaqirgan amaldan qabul qiladi:

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

`say` ko'rinishi `views/site/say.php` faylida saqlanishi kerak. Ushubu usul (metod) amalda chaqirilganda [[yii\web\Controller::render()|render()]]
, u quydagi yo'l bo'yicha ko'rinish topishga harakat qiladi `views/ControllerID/ViewName.php`.

Aytib o'tish keraki yuqorida kodda `message` [[yii\helpers\Html::encode()|HTML himoyasi]] orqali filtrlanadi.
Buni ishlatish shartdir chunki ma'lumot foydalanuvchidan kirib kelmoqda, u esa o'z yo'lida [XSS ziyon](https://ru.wikipedia.org/wiki/%D0%9C%D0%B5%D0%B6%D1%81%D0%B0%D0%B9%D1%82%D0%BE%D0%B2%D1%8B%D0%B9_%D1%81%D0%BA%D1%80%D0%B8%D0%BF%D1%82%D0%B8%D0%BD%D0%B3)
o'tkazishi o'tkazishi mumkin Javascript-skripti orqali.

Siz `say` ko'rinishi HTML teglar, matn yoki PHP-kod bilan to'ldirishingiz mumkin. Aslini olganda `say` skripti [[yii\web\Controller::render()|render()]] orqali chaqirilayotgan sodda PHP-skript bo'lib chiqayotganini ko'rishimiz mumkin. Skript tomonidan shakilangan ma'lumot esa foydalanuvchiga qaytariladi.


Ishlatib ko'ramiz <span id="trying-it-out"></span>
--------------------------------------

Yuqorida aytib o'tilgan amalrni va amalar ko'rinishlarini yaratib bo'ganingizdan so'ng siz usu URL bo'yicha o'tishingiz mumkin:

```
https://hostname/index.php?r=site%2Fsay&message=Salom+dunyo
```

![Salom, dunyo](images/start-hello-world.png)

Sizga "Salom, dunyo" matni chiqishi kerak. Header (yuqori bo'lim) va  Footer (pastki bo'lim) hamma sahifalardek chiqadi.
Agar siz `message` kiritmasangiz, siz «Salom» matnini ko'rishingiz mumkin. Bunaqa mantiq chiqishi sababi shundagi, `actionSay` usuli (metod)dagi `message` parametri oldindan "Salom" matni berilgan.

> Info: Yangi sahifa hamma ishlatayotgan Header (yuqori bo'limi) va Footer (pastki bo'lim) ishlatadi, chunki 
  [[yii\web\Controller::render()|render()]] usuli `say` ko'rinishini o'zi [maketga](structure-views.md) `views/layouts/main.php`  topib qo'yadi.

Parametr `r` qo'shimcha tushuncha ta'lab qiladi. U [yo'nalishlar (route)](runtime-routing.md) bilan bo'liqdir va nomi boyicha kerakli amalni topib chiqaradi. Uning formati `ControllerID/ActionID`. Web-ilova so'rovni qabul qilishni boshlaganida u `r` paraterni tekshirishni boshlaydi va `ControllerID` ni ishlatadi, va nomi bo'yicha so'ralayotgan nazoratchini topib, `ActionID` bo'yicha shu topilgan nazoratchi ichidan so'raloyotgan amalni topib chiqaradi.
Bizning misolimiz bo'yicha `site/say` so'rovi boyicha `SiteController` nazoratchisi ichidan `say` amalini chiqaradi.
Natijada esa, so'rovni qabul qilish `SiteController::actionSay()` topshiriladi.

> Info: Nazoratchilar ham hudi amalar kabi web-ilovada yagona nomga (identifikator) ega.
  Nazoratchilar nomlash tartibi hudi amalardaqadir. Klasslar nomlari So'larga bo'linib defizlar orqali aniqlanadi, so'zlarni birinchi katta harf bilan yozib o'tib va oxiriga `Controller` qo'shib qidirishni boshlaydi. Misol uchun, nazoratchi `post-comment` nomi `PostCommentController` klassiga teng bo`ladi.


Xulosa <span id="summary"></span>
-----------------------------

Bu bo'limda biz nozaratchilar va ularning ko'rinishlari mavzusini MVC-tuzilmasi ichida yoritib berishga harakat qildik. Siz nazoratchi qismi bo'lgan so'rovlarni qabul qiluvchi amalni yasab so'rovga kerakli ko'rinishni javob tariqasida shakilantirishni ko'rdingiz. Ushbu misolda hech ham modellar ishlatilmadi, chunki misolar juda ham sodda bo'lgan `message` parametri bo'yicha uni foydalanuvchiga qaytargan.

Shuningdek siz `yo'nalishlar (route)` tartibi bilan tanishtingiz, bu esa foydalanuvchi so'roviga kerakli nazoratchini topib foydalanuvchiga javob berish jarayonida juda ham muhim o'ringa egadir.

Keyingi bo'limda siz modelar nimaligi va uni qandey yaratish kerakligini va HTML-shaklrar bilan ishlashni o'rganasiz.

