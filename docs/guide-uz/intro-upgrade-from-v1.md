1.1 dan keyingi yangilanishlar
==============================

Yii 2.0 talqini uchun batamom boshqatdan yozilganligi bois 1.1 va 2.0 talqinlar orasida ko'p farqlar mavjud.
Shu tufayli 1.1 dan keyingi yangilanishlar minor talqinlar (talqinlarning bir biridan 1-xonasidan keyingi xonalaridagi sonlari farq qiladiganlari) orasidagi yangilanishlar kabi sodda ko'rinishda bo'lmaydi.
Ushbu qo'llanmada ikki talqin orasidagi asossiy farq yangilanishlari keltirilgan.

Agar siz ilgari Yii 1.1 ni ishlatmagan bo'lsangiz u holda ushbu bo'limni tashlab, [Ishni boshlash][start-installation.md] bo'limiga o'tishingiz mumkin.

Shuningdek shuni unutmangki Yii 2.0 bu yerda yozilganidan ko'proq imkoniyatlarga ega. Qaysi imkoniyatlar qo'shilganini bilish uchun qo'llanmani o'qib chiqish tavsiya etiladi. Balkim siz bunga qadar o'zingiz uchun zarur deb bilib yaratgan imkoniyat endi freymvorkning bir qismidir. 


O'rnatish
---------

Yii ning 2.0 talqini PHP uchun bog'liqliklarni boshqaruvchi hisoblangan [Composer](https://getcomposer.org/) ga butunlay asoslangan. 
Freymvorkni o'rnatish va shuningdek kengaytirish Composer orqali qilinadi. Yii 2.0 ni o'rnatish bo'yicha yanada batafsilroq ma'lumot 
[Yii ni o'rnatish](start-installation.md) bo'limida keltirilgan. Yii 2.0 uchun qanday qilib kengaytmalar yaratish yoki 1.1 talqindagi mavjud kengaytmalarni qanday qilib 2.0 talqinga adaptiatsiyalash ko'rsatmalari [Kengaytmalarni yaratish](extend-creating-extensions.md) bo'limida ko'rsatilgan.


PHP talabi
----------

Yii 2.0 talqin Yii 1.1 talqinda qo'llanilgan PHP 5.2 ga nisbatan ancha yaxshilangan PHP 5.4 yoki undan yuqorisini islatadi. 
Shu tufayli siz nazarda tutishingiz kerak bo'lgan tildagi ko'p o'zgarishlar mavjud. 
Quyida PHP ning asosiy o'zgarishlari keltirilgan:

- [Nomlar sohasi](http://php.net/manual/ru/language.namespaces.php);
- [Anonim funksiyalar](http://php.net/manual/ru/functions.anonymous.php);
- Massivlar uchun qisqa sintaksisni qo'llash: `[...elementlar...]` ni `array(...элементы...)` o'rniga;
- Qisqartirilgan teglarni qo'llash `<?=` ko'rinish fayllarida chiqarish uchun.
  PHP 5.4 talqinida ushbu imkoniyatni hech qanday sozlashlarsiz qo'llash mumkin;
- [SPL ning klaslari va interfeyslari](http://php.net/manual/ru/book.spl.php);
- [Kechroq statik bog'lash (LSB)](http://php.net/manual/ru/language.oop5.late-static-bindings.php);
- [Sana va vaqt uchun klaslar](http://php.net/manual/ru/book.datetime.php);
- [Treytlar](http://php.net/manual/ru/language.oop5.traits.php);
- [Xalqarolashtirish (Intl)](http://php.net/manual/ru/book.intl.php); Xalqarolashtirish imkoniyatlaridan foydalanish maqsadida Yii 2.0 PHP ning `intl` kengaytmasini ishlatadi.


Nomlar sohasi
-------------

Yii 2.0 ning asosiy o'zgarishlaridan biri bu nomlar sohasi hisoblanadi. Freymvorkning deyarli har bir sinfi nomlar sohasida joylashgan, masalan, `yii\web\Request`.
"C" qo'shimchasi endi klaslar nomlarida ishlatilmaydi.
Klaslarni nomlash kelishuvi direktoriyalar strukturasiga asoslanilgan. Masalan, `yii\web\Request` ushbu yozuv klasning yii freymvork direktoriyasidagi web/Request.php faylida joylashganini anglatadi.
(Yii ning klaslarni yuklovchisi evaziga siz freymvork klaslarini hech qanday vositachisiz boglab qo'yishingiz mumkin).


Komponent va obekt
------------------

Yii 2.0 da 1.1 dagi `CComponent` klas ikkita klasga ajratilgan: [[yii\base\BaseObject]] va [[yii\base\Component]].
[[yii\base\BaseObject|BaseObject]] klas oddiy asos klas bo'lib xususiyatlar uchun [getter va setter](concept-properties.md) larni ishlatishga imkon beradi. 
[[yii\base\Component|Component]] klas [[yii\base\BaseObject|BaseObject]] klasdan voris bo'lib [xodisalar](concept-events.md) va 
[o'zini tutish](concept-behaviors.md) larni qo'llab quvvatlaydi.

Agar sizni klasingizga xodisalar funksiyalari yoki o'zini tutishlar kerak bo'lmasa asos klas sifatida [[yii\base\BaseObject|BaseObject]] ni qo'llashingiz mumkin. Ushbu holat asosan asos strukturali klaslar yaratilayotgan vaqtda yuz beradi.


Obekt sozlashlari
-----------------

[[yii\base\BaseObject|BaseObject]] klas obektlarni sozlashni yagona usulini tashkillashtiradi. Ixtiyoriy [[yii\base\BaseObject|BaseObject]] ga voris bo'lgan klas (agar kerak bo'lsa) o'zini sozlashi uchun quyidagi ko'rinishda o'ziga konstruktor yaratishi mumkin: 

```php
class MyClass extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... sozlashlar qo'llanilishidan oldin initsializatsiyalash (e'lon qilish va qiymatlash) 

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... sozlashlar qo'llanilganidan keyin initsializatsiyalash
    }
}
```

Yuqoridagi misolda oxirgi parametr obekt xususiyatlarini qiymatlovchi sozlashlar massivi ya'ni kalit-qiymat formatidagi juftlikdan iborat bo'lishi kerak. Siz sozlashlar qo'llanilganidan keyin initsializatsiya ishini amalga oshirish uchun oldindan [[yii\base\BaseObject::init()|init()]] metod yaratib qo'yishingiz mumkin.

Ushbu kelishuvga asoslanib siz sozlash massivi yordamida yangi obektlarni yaratishingiz va sozlashingiz mumkin:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

Sozlashlar haqidagi batafsil ma'lumotlar [Obektlarni sozlash](concept-configurations.md) bo'limida keltirilgan.
