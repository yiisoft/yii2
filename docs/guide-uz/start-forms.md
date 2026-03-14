Shaklar bilan ishlash
================

Ushbu bo'limda biz foydalanuvchidan ma'lumotlarni qabul qilishni o'rganamiz. Sahifada ma'lumotlarni kiritish uchun ism va emailni kiritish uchun shakl bor. Sahifada kiritilgan ma'lumotlar tasdiqlanishi uchun ko'rsatiladi.

Shu maqsadga erishish uchun biz bitta [amal](structure-controllers.md) va ikkta [ko'rinish](structure-views.md) yasashdan tashqari
[model](structure-models.md) yaratasiz.

Ushbu qo'lanmada siz quydagilarni o'rganasiz:

* Qandey qilib foydalanuvchi tomonidan kiritilgan ma'lumotlar uchun [modelni](structure-models.md) yaratish mumkin;
* Qandey qilib ma'lumotlarni tekshirish uchun mantiq yozishimiz mumkin;
* Qandey qilib HTML-shaklni [ko'rinish](structure-views.md) ichida yaratishimiz mumkin.


Model yaratilishi <span id="creating-model"></span>
---------------------------------------------

Fayl `models/EntryForm.php`da `EntryForm` pastda ko'rsatilganidek klassini yarating. U foydalanuvchidan kelgan ma'lumotlar saqlash uchun ishlatiladi. Klasslarni nomlari haqida siz 
«[Klasslarni avtoyuklanishi](concept-autoloading.md)» bo'limida o'qishingiz mumkin.

```php
<?php

namespace app\models;

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

Ushbu klass quydagi klassni kengaytiradi [[yii\base\Model]], u esa frameworkni bir qismi hisoblanadi va oddatda shakl ma'lumotlari bilan ishlaydi.

Klass 2-ta ochiq hossa `name` va `email` iborat, ular foydalanuvchilar ma'lumotlarni saqlash uchun ishlatiladi.
uni yana `rules()` usuli bor, u esa ma'lumotlarni saqlash qoidalarini (tartibini) yozib o'tadi. Yuqorida yozilgan qoidalar quydagicha ta'riflasa bo'ladi:

* Xossalar `name` va`email` to'ldirilishi shart;
* Xossa`email` ma'lumoti email bo'lishi shart.

Agar `EntryForm` nusxasi (obyekt) ma'lumotlar bilan to'ldirilgan bo'lsa, kiritilgan ma'lumotlarni kiritilgan ma'lumotlar ma'lumot qoidalari talabiga to'g'ri kelishini [[yii\base\Model::validate()|validate()]] tekshirishingiz mumkin. Agar ma'lumotlar tekshirishdan o'tmasa [[yii\base\Model::hasErrors|hasErrors]]
xossasi `true` ga teng bo'lib qoladi. Ushbu xossa: [[yii\base\Model::getErrors|errors]] orqali siz qanaqa xatolar borligini ko'rishingiz mumkin.


Amal yaratilishi <span id="creating-action"></span>
------------------------------------------------

Endi esa huddi pastda ko'rsatilganidek `site` nazoratchisi ichida `entry` amalini yarating.

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\EntryForm;

class SiteController extends Controller
{
    // ...yaratilgan kod...

    public function actionEntry()
    {
        $model = new EntryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // $model ichidagi ma'lumotlar tekshirishdan muvaffaqiyatli o'tgan

            // endi esa biror $model bilan mantiq...
 
            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // Sahifaga kirilgan holatda yoki hato mavjud bo'ganida
            return $this->render('entry', ['model' => $model]);
        }
    }
}
```

Amal `EntryForm` nusxasini yaratadi. Keyin esa u `$_POST` dan kelgan ma'lumotlar bilan modelni to'ldirayapti, u ma'lumotlar Yiida
[[yii\web\Request::post()]] usulu orqali olinadi. Agar model muvaffaqiyatli to'ldirilsa, foydalanuvchi ma'lumotlarni shakldan foydalanib
jonatib ma'lumotlar shu orqali kelganini nazarda tutayapmiz, keyin ma'lumotlar tekshirish uchun [[yii\base\Model::validate()|validate()]]
usuli ishlatilayapti.

Agar hammasi yaxshi o'tsa, amal `entry-confirm` ko'rinishini qaytaradi, u esa foydalanuvchi tomonidan kiritilgan ma'lumotlarini ko'rsatadi.
Aks holda esa `entry` ko'rinishi qaytariladi, u esa HTML-shaklni va xatolarni chiqarib beradi, agar ular bor bo'lsa.

> Info: `Yii::$app` yagona global xossasi Yiining nusxasini o'z ichiga oladi
[ilova](structure-applications.md) (singleton). Bir vaqtni o'zida u [Service Locator](concept-service-locator.md) ham hisoblanadi,
undan esa quydagi komponentlar bilan foydalanish mumkin  `request`, `response`, `db` va boshqa. Yuqorida ko'rsatilgan kodda `$_POST`
massivini qabul qilish uchun biz `request` komponentini ishlatdik.


Ko'rinish yaratilishi <span id="creating-views"></span>
----------------------------------------------------

Yakunda biz `entry-confirm` va `entry` ko'rinishlarini yaratamiz, ular esa yuqorida yozilgan `entry` amali orqali ma'lum bir xolatlarda qaytariladi.

Ko'rinish `entry-confirm` kiritilgan email va ismni ko'rsatadi. U `views/site/entry-confirm.php` fayli ichiga saqlangan bo'lishi kerak.

```php
<?php
use yii\helpers\Html;
?>
<p>Siz quydagi ma'lumotlarni kiritingiz:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->email) ?></li>
</ul>
```

Ko'rinish `entry` HTML-shaklni yasaydi. U quydagi faylga saqlangan bo'lishi kerak `views/site/entry.php`.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'email') ?>

    <div class="form-group">
        <?= Html::submitButton('Jonatish', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>
```

HTML-shaklni yasash uchun juda mukammal [vidjet](structure-widgets.md) [[yii\widgets\ActiveForm|ActiveForm]] ishlatilmoqda.
Quydagi usular `begin()` va `end()` shaklni ochilish va yopilish teglarini yasaydi. Bular orasida esa ma'lumotlar kiritilishi uchun
kerak bo'lgan maydonlar [[yii\widgets\ActiveForm::field()|field()]] yaratilmoqda . Birinchi "name", ikkinchisi esa "email".
Keyin esa shaklni ma'lumotlarni jonatish uchun tugma yasalishiga javob beradigan usul [[yii\helpers\Html::submitButton()]] chaqirilmoqda.


Keling, sinab ko'raylik <span id="trying-it-out"></span>
--------------------------------------

Ish jarayonida yaratilgan bor narsani ko'rish uchun, browserni ochib quydagi URLni kiritaylik:

```
https://hostname/index.php?r=site%2Fentry
```

Siz ikkta maydoni o'z ichiga olgan shaklni ko'rishingiz mumkin. Har bir maydon oldida esa yorlig' bor, u yorlig'lar, qanaqa
ma'lumotlar kiritilishi kerak bo'lishi haqida ma'lumot saqlaydi.
Agar siz ma'lumotlarni kiritmasdan jonatish tugasiga bosangiz yoki email noto'g'ri kiritsangiz, siz har bir maydon oldida hatolarni ko'rishingiz mumkin bo'ladi.

![Hatolar bilan shakl](images/start-form-validation.png)

Ma'lumot kiritilib jonatilgandan so'ng esa, siz hozirgina kiritilgan ma'lumotlar sahifasini ko'rasiz.

![Kiritilgan tasdiqlangan ma'lumotlar](images/start-entry-confirmation.png)



### Bu qandey ishlayapti? <span id="magic-explained"></span>

Siz, bu HTML-shakl aslida qandey qilib ishlayotganini haqida savollar borligi bo'lishi mumkin. Butun jarayon ozgina murakkab ko'rinishi mumkin:
maydonlar oldida yorlig'lar, ma'lumotlar noto'g'ri kiritilganda hatolar va bularni hammasi sahifa qayta yuklanmasdan ishlayotganiga ajablanishingiz mumkin.

Ha albatta, ma'lumotlar tekshirishi aslida Javascript tarafida ham Serverda ham amalga oshirilmoqda.
[[yii\widgets\ActiveForm]] judda yaxshi o'langan, siz `EntryForm`da kirigan qoidalarni olish uchun.
Ularni Javascript kodga aylantirib va tekshirish jarayonida ishlatiladi. Agar browserda Javascript o'chsa tekshirish 
server tarafda ham `actionEntry()` usulida ko'rsatilganidek o'tmoqda. Bu esa foydalanuvchi tomonidan kiritilgan ma'lumotlar ishonchli ekanliginini ko'rishimiz mumkin.

Moydonlar uchun yasalgan yorlig'lar xossalar nomlari assosida quydagi usul `field()` orqali yaratilmoqda. Misol uchun, yasalgan `Name` yorlig'i `name` xossasi uchun shaklangan. Siz yorlig'larni quydagicha o'zgartirishingiz mumkin:

```php
<?= $form->field($model, 'name')->label('Sizning ismingiz') ?>
<?= $form->field($model, 'email')->label('Sizning elektron qutingiz') ?>
```

> Info: Yiida juda ham ko'p vidjetlar bor, ular sizga tez va murakkab ko'rinishlarni yasashga yordam beradi.
  Vijetlarni yasash judda oson va sodda ekanligini keyin (oldinda) bilishingiz mumkin. Ko'rinishdagi narsalarni ko'p narsani vijetlarga chiqarsa bo'ladi, 
  nimaga? keyinchalik ham boshqa joylarda ishlatish uchun, bu esa ishni soddalashtiradi.

Xulosa <span id="summary"></span>
-----------------------------

Bu bo'limda siz MVC-arxitekturasidagi xar bir qismini ishlatib ko'rdingiz.Siz foydalanuvchidan kelgan ma'lumotlarni qabul qilib
tekshirish uchun, model klasslarini yaratishni o'rgandingiz.

Shuningdek, siz foydalanuvchidan ma'lumotlarni qabul qilish va ularni ko'rsatishni o'rgandingiz. Aslida bu ish jarayonida ma'lum bir
vaqtni talab qilishi mumkin. Yii esa juda mukammal vidjetlarni taqdim qiladi, ular esa sizga ish jarayonini tezlashtirishni yordam beradi.

Keyingi bo'limda siz ma'lumotlar ombori bilan ishlashni o'rganasiz, bu esa juda ko'p ilovalarda talab qilinadi.

