Ma'lumotlar ombori bilan ishlash
======================

Bu bo'lim sizga yangi sahifani yaratishga yordam beradi, u sahifa esa `countries` jadvalidan kelgan ma'lumotlarni chiqarib beradi. Buning uchun esa siz ma'lumotlar ombori bilan aloqani sozlashingiz kerak, klass yaratish [Active Record](db-active-record.md),[amalni](structure-controllers.md) va [ko'rinish](structure-views.md) yaratishimiz.

Bu bo'limda siz nimalarni o'rganasiz:

* Qandey qilib ma'lumotlar omboriga ulanish.
* Active Record klassini yasash
* Ma'lumotlarni olishni, Active Recordni ishlatishni.
* viewda ma'lumotlarni sahifalarga bo'lib chiqarishni.

Etibor bering, shu bo'limni o`zlashtirish uchun, siz ma'lumotlar ombori bilan ishlash bilimingiz bo'lishi kerak. 
Aniqroq esa,siz hech ma'lumotlar omborini yasashni va unga SQL-so'rov jo'natishni, ma'lumotlar ombori klientlarini ishlatishni bilishingiz kerak.

Ma'lumotlar omborini tayyorlaymiz <span id="preparing-database"></span>
----------------------------------------------------------------

Birinchi bo'lib `yii2basic` nomli ma'lumotlar ombri yarating, chunki undan siz web ilovadan ma'lumotlarni qabul qilasiz.
Ma'lumotlar omborini siz SQLite, MySQL, PostgreSQL, MSSQL yoki Oracle da yaratishingiz mumkin, chunki Yiida aytib o'tilgan barcha ma'lumotlar ombori bilan ishlay oladi. Soddalik uchun keyinchalik, ma'lumotlar ombori misol uchun MYSql haqida gap boradi.

Endi esa ma'lumotlar omborida `country` degan jadval yarating va unga ozgina misol uchun ma'lumotlar qo'shing. Siz quydagi SQL-buyruqni bajarishingiz mumkin yuqorida aytilgan amalarni bajarish uchun:

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

Hozirda sizda `yii2basic` degan ma'lumotlar ombori bo'lishi kerak va unda 3 ta ustundan iborat `country` degan jadval bo'lishi kerak, o'nta satrdan iborat ma'lumot bilan.

Ma'lumotlar omborida bilan aloqani sozlaymiz<span id="configuring-db-connection"></span>
-------------------------------------------------------------------------

Ishni boshlashdan oldin sizda [PDO](https://www.php.net/manual/ru/book.pdo.php) kengaytmasi o'rnatilganiga amin bo'ling va PDO-boshqaruvchisi ma'lumotlar ombori bilan boshqara olishingiz uchun(misol uchun, `pdo_mysql` MYSql uchun). Bu siz aloqali ma'lumtolar ombori bilan ishlashingiz uchun assosiy talablardir.
Endi esa, hammasini o'rnatib bo'lganingizdan so'ng, `config/db.php` oching va sozlamalarni o`zingizni ma'lumotlar omboringiz sozlamalariga o'zgartiring. Boshlanish sozlamalar quydagichadir:

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

Fayl `config/db.php` — oddiy [sozlamalar](concept-configurations.md) vositasidir, faylarda assoslangan. Quydagi sozlama fayli [[yii\db\Connection]] klass nusxasini olish uchun kerak bo'lgan assosiy talablarga javob beradigan sozlamalardan iboratdir, undan esa siz SQL-buyruqlarni ma'lumotlar omboriga jonatishingiz mumkin.

Ma'lumotlar omboriga yuqoridagi sozlamalar sozlab bo'lgandan so'ng ulanish, undan foydalanish uchun `Yii::$app->db` orqali amalga oshiriladi.

> Info: fayl `config/db.php` ilovaning assosiy sozlama fayli orqali ulanadi `config/web.php`,
  u esa [web-ilova](structure-applications.md) nusxasini sozlamalarini o'z ichiga oladi.
  qo'shimcha ma'lumot uchun, iltimos, ushbu [Sozlamalar](concept-configurations.md) bo'limga ko'rib chiqing.

Agar siz frameworkda yo'q bo'lgan ma'lumotlar omborlari bilan ishlashingiz kerak bo'lsa quydagi kengaytmalarga etibor bering:

- [Informix](https://github.com/edgardmessias/yii2-informix)
- [IBM DB2](https://github.com/edgardmessias/yii2-ibm-db2)
- [Firebird](https://github.com/edgardmessias/yii2-firebird)


Active Record klassi (sinf) avlodini yaratamiz<span id="creating-active-record"></span>
-----------------------------------------------------------------------

`country`dan ma'lumotlarni olib ishlatib ko'rish uchun, [Active Record](db-active-record.md)ni kengaytirgan klass yarating, nomi `Country` va uni `models/Country.php` fayliga saqlang.

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
}
```

`Country` klassi [[yii\db\ActiveRecord]]ni kengaytirmoqda. Siz uning ichida bir satr kod ham yozmasligingiz mumkin! Yuqoridagi kod assosida Yii o'zi ma'lumotlar omboridagi jadval va klass orasida aloqa yasaydi.

> Info: Agar ma'lumotlar omboridagi nom va klassnomi orasida bir xillikni saqlash imkoni  bo'lmasa siz klassga aynan jadal ismini [[yii\db\ActiveRecord::tableName()]] usuli orqali yozishingiz mumkin.

`Country` klassini ishlatganingizda, siz osonlikcha `country` jadvalidagi ma'lumotlar bilan boshqarishingiz mumkin, misol uchun pastdagi qatorlarda shuni ko'rishingiz mumkin:

```php
use app\models\Country;

// "country" jadvalidan hamma ma'lumotlarni qabul qilamiz va ularni "name" ustuni bo'yicha saralaymiz.
$countries = Country::find()->orderBy('name')->all();

// "US" jadval ka'liti bo'yicha ma'lumotni olib beradi.
$country = Country::findOne('US');

//"United States"
echo $country->name;

// nomi o`zgartiramiz "U.S.A."ga va ma'lumotlar omborida saqlaymiz.
$country->name = 'U.S.A.';
$country->save();
```

> Info: Active Record — bu ma'lumotlar ombori bilan ishlash uchun juda ham mukammal vosita hisoblanadi.
  Yana ko'oproq ma'lumotni siz [Active Record](db-active-record.md) bo'limida topishingiz mumkin. Qo'shimcha tariqasida, siz yana boshqa yanada past qism vositasi [Data Access Objects](db-dao.md)dan foydalanishingiz mumkin.


Amal yaratamiz <span id="creating-action"></span>
-------------------------------------------------

Ma'lumotlarni foydalanuvchiga ko'rsatish uchun siz amal yaratishingiz kerak. Oldingi misolarda farqli o'laroq `site` nazoratchisi ichida amal yasash o'rniga to'g'riroq buning uchun maxsus nazoratchi yasab unga hamma usularni yig'ish to'g'riroq bo'ladi,Yangi nazoratchini `CountryController` deb nomlang, va unda `index` degan amal yarating uning ichida pastda ko'rsatilgandek ma'lumot kiriting:

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

Yuqoridagi kodni quydagicha saqlang `controllers/CountryController.php`.

`index` amali `Country::find()`ni chaqirmoqda. Ushubu Active Record usuli `country` jadvalidan hamma ma'lumotlarni qaytaradi.
kelayotgan ma'lumotlar bo'lish sahifalarga bo'lish uchun [[yii\data\Pagination]] klassi ishlatilmoqda. `Pagination` nusxasi ikkta maqsadga kerak:
* `offset` va `limit` SQL-buyrug`i uchun belgilanadi, bu esa birmartada faqat bitta sahifani qaytarishga berilgan (bitta sahifada misl uchun 5 ta satr)
* u esa viewda paginatsiya (pagination) chiqarilishi uchun ishlatilmoqda, turi esa sonlardan iborat, bularni hammasini oldinda tushuntirilib kelinadi.

Kodni oxirida esa `index` amali `index` ko'rinishini chiqarib beradi, unga hamma chiqarilishi kerak bo'lgan ma'lumotlarni va paginatsiyanni qaytarayapti.

Ko'rinish yaratamiz <span id="creating-view"></span>
---------------------------------------------

Birinchi bo'lib `country` degan papka ochamiz `views` ichida. Bu papka hamma nazoratchi ko'rinishlarni saqlash uchun ishlatamiz. Ushbu papkada `views/country` fayl yaratamiz `index.php`, so'ng quydagi kodni yozamiz:

```php
<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Countries</h1>
<ul>
<?php foreach ($countries as $country): ?>
    <li>
        <?= Html::encode("{$country->code} ({$country->name})") ?>:
        <?= $country->population ?>
    </li>
<?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $pagination]) ?>
```

Ko'rinish ikkta qisimga bo'lingan ma'lumot chiqishiga nisbatan. Ko'rinishni birinchi qismida ma'lumotlar tartibsiz HTML-ro'yxati kabi chiqayapti.
Ikkinchisida esa [[yii\widgets\LinkPager]] vidjiti, u ko'rinishga qaytarilayotgan paginatsiya ma'lumoti assosida shaklanadi. Vidjet `LinkPager` sahifalar tugalarini chiqarib beradi. Ulardan birortasiga bosangiz sahifa yangilanib ma'lumotlar o'zgaradi


Keling sinab ko'raylik <span id="trying-it-out"></span>
------------------------------------------------------

Yuqoridagi yozgan kodni ishlashini ko'rish uchun iltimos ushbu yo'l (url) bo'yicha o'ting:

```
https://hostname/index.php?r=country%2Findex
```

![Список Стран](images/start-country-list.png)

Boshida siz ma'lumotlar omboridan beshta ma'lumotni chiqarib berganini ko'rishingiz mumkin. Ma'lumotlar ostida. Agar siz "2" tugmasiga bosangiz, keyingi ikkinchi sahifa ma'lumotlarini ko'rsatayotganini ko'rishingiz mumkin.
Etibor bilan qarasangiz, URLda ham quydagi "2" sahifa chiqti.

```
https://hostname/index.php?r=country%2Findex&page=2
```

[[yii\data\Pagination|Pagination]] Ma'lumotlarni sahifalarga bo'lib qisman chiqarish umumiy ishlash tartibi esa quydagicha:
* Boshida [[yii\data\Pagination|Pagination]] birinchi sahifani ko'rsatadi, uning SQL-buyrugi qismi  `LIMIT 5 OFFSET 0` bo'ladi. Natijada, birinchi beshta ma'lumot qaytarilib ko'rsatiladi.
* Vidjet [[yii\widgets\LinkPager|LinkPager]] URLdagi ma'lumotda assosan tugmachalarni shaklantirib beradi, Urlni esa [[yii\data\Pagination::createUrl()|Pagination]]. Bu URLda `page` parametri mavjud bo'ladi, tugmachalarda esa xar xil page parametri mavjud.
* Agar "2" tugmasiga bosangiz, sahifa yangilanib `country/index` uchun yangi so'rov amalga oshadi. Shundey qilib SQL-so'rovi `LIMIT 5 OFFSET 5` tashkil qiladi va keying beshtalikni qaytaradi.

Xulosa <span id="summary"></span>
-------------------------------------

Quydagi bo'limda siz ma'lumotlar ombori bilan ishlashni o'rgandingiz. Shundey qilib siz yana ma'lumotlarni qismlarga bo'lib chiqarishni o'rgandingiz sizga [[yii\data\Pagination]] va [[yii\widgets\LinkPager]] yordam berdi.

Keyingi bo'limda esa siz kodni yasash uchun juda kuchli [Gii](start-gii.md) vositasini ishlatishni o'rganasiz, uning yordamida siz juda ko'p ishlatiladigan masalalarni hal qilishingiz mumkin, Shulardan Create-Read-Update-Delete (CRUD) ma'lumotlar ombori jadvali bilan ishlash. Aslida siz hozir Gii yasaydigan kodni yuqorida yozib chiqdingiz, Yiida siz shularni avotmatik tarzda Gii yordamida kodni yaratishingiz mumkin.
