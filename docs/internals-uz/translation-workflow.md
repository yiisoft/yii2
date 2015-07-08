O'zbekchaga tarjima qilish bilan qanday ishlash kerak
=====================================================

Yii juda ko'p tillarga tarjima qilinayabdi shu jumladan o'zbekchaga ham. Tarjima qo'llanma va habarlarni o'z ichiga oladi.

Freymvork habari
----------------

Ikki turdagi habarlar bor: istisnolar, qaysiki ishlab chiquvchi nazarda tutgan va ular tarjima qilinmaydi va habarlar, qaysiki foydalanuvchilarga ko'rsatiladigan. Masalan, validatsiyaning xatoliklari.

Tarjimani yangilash uchun:

1. Konsolda `framework` direktoriyani ochamiz, `yii message/extract messages/config.php` ni ishga tushiramiz.
3. Habarlarni `framework/messages/uz/yii.php` ga ko`chiramiz. Muhimi fayllar UTF-8 kodlashda bo'lishi kerak.
4. `uz` dagi tarjimalar bilan [pull request qilamiz](https://github.com/yiisoft/yii2/blob/master/docs/internals/git-workflow.md), qolgan tillarga tegmaymiz.

Tarjima fayllarda massiv joylashgan. Uning kalitlari - boshlang'ich kodlar, qiymatlari - tarjima. Agar qiymat bo'sh bo'lsa habar tarjima qilinmagan hisoblanadi. Kodda boshqa uchramaydigan habarlar tarjimasi '@@' ga o'ralgan. Ayrim habarlar uchun [sonlar bilan qo'llanilishini qo'llab-quvvatlash uchun maxsus format](../guide-uz/tutorial-i18n.md) ni ishlatish zarur.

Qo'llanma
---------

Qo'llanamani tarjimasi `docs/<original>-uz` da joylashgan, bu yerda <original> - original direktoriyalarga mos keladi, masalan,
`guide` yoki `internals`.

Agar qo'llanma tarjimasi tugagan bo'lsa, build direktoriyasida konsolni ochib va quyidagini bajarib, originaldagi oxirgi tarjimadan keyingi diff o'zgarishlarni olish mumkin:

```
php build translation "../docs/guide" "../docs/guide-uz" "Uzbek guide translation report" > report_guide_uz.html
```

Agar composer uchun urushsa, bosh direktoriyada `composer install` ni bajaring.

Tarjima qilishdan oldin hech kim shug'ullanmayotganligini tekshiring va o'zingizga [barcha tarjima qilinayotgan hujjatlarni ro'yhatini] yozib oling
//Ushbu manzilni ulardan olgandan keyin o'zgartirib qo'yish kerak bo'ladi.
(https://docs.google.com/spreadsheets/d/10dS7VB_3jSxUorryRlplB7nhA59e3i2vLYmTwn_1d3I/edit?usp=sharing).

Barcha o'zgarishlarni quyidagi ko'rinishda olib boramiz [pull request](https://github.com/yiisoft/yii2/blob/master/docs/internals/git-workflow.md).


### Umumiy qoidalar

- Ko'p terminlar o'zbekchada bitta ma'noga ega emas va o'zbekchada ko'p qo'llanilmaydigandir, shu sababli agar matnda 
  shunday terminlar uchrasa ularni birinchi bor qo'llanilgan joyida qavs ichida ingliz tilidagi varianti 
  ko'rsatilishi kerak; (terminlarning tarjimasini qo'llanilish variantlari quyida keltirilgan); 
- Agar tarjima vaqtida matnning qaysidir qismi mazmuni o'zgarayotgan bo'lsa va siz uni shunday tarjima qilish 
  kerakligiga ishonchingiz komil bo'lmayotganday tuyulsa ushbu qismni * ichiga oling(ichidagi matn qiya holatga keladi). 
  Bu olib tashlash/to'g'rilashlar vaqtida ushbu matnlarga alohida e'tibor qaratishga imkon beradi; 
- Tarjima vaqtida fakt xatoliklarni qilmang! 
- Matnda tashqi manbalarga murojaatlar uchraydi, agar murojaat terminni izohlash maqolasiga olib boradigan bo'lsa, u holda 
  o'zbekchada tarjimasi bo'r bo'lgan vaziyatda o'zbekchasiga murojaat qilinadi.
  Masalan `http://en.wikipedia.org/wiki/Captcha` → `http://uz.wikipedia.org/wiki/Captcha`.
- Sharhlar kodda tarjima qilinadi, agarda birinchi holatdagi mazmunini o'zgartirmasa; Matndagi vaqtinchalik sharhlarni 
  imkon qadar faqat *lokal*da ishlatish kerak! Aks holda uni reliz ga tushib qolish ehtimoli bor; 
- Bo'limlarni tarjima qilish vaqtida `README.md` dagi tarjimani olib ketamiz; 
- Shaxsiy qo'shimcha-sharhlarni qo'shish imkoni bor, lekin xaosdan qochish uchun original bitta bo'lishi kerak. Bunga zarurat vaqtida sharh oxiriga 
  "tar. shar." ni qo'shish kerak;
- Hujjatni umumiy to'g'rilashni o'tkazgandan so'ng mustaqil ravishda faqat ushbu bo'limga tegsihli bo'lgan grammatikadagi, fakt xatoliklardagi o'zgarishlarni kiritish talab darajasida tavsiya etiladi. Boshqa holatlarda gaplarni to'g'rilash, yaxshilash uchun va zarur hollarda o'zgarishlarni markazlashgan tarzda barcha bo'limlar uchun amalga oshirish uchun ularni tahlilga qo'yish zarur.
   

### Qo'llanma strukturasi

Tarjima qilish vaqtida hujjatning struktura birligini to'g'ri nomlash muhim. Quyida keltirilgan strukturaga amal qilamiz :

- Bob 1 
  - Bo'lim 1 
  - Bo'lim 2 
    - Qism bo'lim 1 
  - ... 
  - Bo'lim N 
- Bob 2 
- ... 
- Bob N
 
### Maxsus habarlarni tarjimasi

- Tip → Ko'rsatma 
- Note → Eslatma 
- Info → Ma'lumot 

### Rasmlarni tarjima qilish

Qo'llanma uchun rasmlar `images` qism katalogida joylashgan. Ularning barchasi [yED](http://www.yworks.com/en/products_yed_about.html) da yaratilgan.
Zarurat tug'ilgan vaqtda fayl tarjima qilinayotgan katalogning `images` katalogiga nusxalanadi va tarjima qilinib png formatida saqlanadi.

Rasmlardagi yozuvlar tarjima qilinadi.

### Grammatika


Oxirgi variantni tashlashdan oldin uni umumiy stilini, orfografiyasini, punktlarini tekshiring. Tarjimani o'zbek tili uchun orfografiyani Wordda tekshirish imkoni bo'lmasada uning yordamida tayyorlashingiz mumkin;

### Terminlar ro'yhati

- action — amal. 
- active record — tarjimasiz. 
- attach handler — «qayta ishlovchini tayinlash».
- attribute of the model — model atributi. 
- camel case — tarjimasiz. 
- customization — (nozik) sozlash 
- column — ustun (agar MO haqida gap ketayotgan bo'lsa). 
- content — tashkil etuvchilari. 
- controller — kontrollyor. 
- debug (mode) — kod sozlash (tartibi) (production mode ga qarang). 
- eager loading — ziqna yuklash uslubi/ziqna yuklash (lazy loading ga qarang). 
- PHP extension — PHP kengaytmasi. 
- field (of the table) — jadval (yoki atribut) maydoni. 
- framework — freymvork. 
- front-controller — front-kontrollyor. 
- getter — getter. 
- (event) handler — qayta ishlovchi (hodisa). 
- hash — xesh. 
- helper - yordamchi. 
- id — qiymatlovchi(identifikatsiyalovchi). 
- instance — nusxa. 
- lazy loading — chetga olingan yuklash (qanday kerak bo'lsa shunday yuklasymiz va erta emas). 
- method — metod (obektniki) //Diqqat! Obekt/klaslarda funksiyasi yo'q, faqat metodlari bor. 
- model — model, ma`lumotlar modeli. 
- model form — formalar modeli. 
- parameter — parametr (metod yoki funksiyada va lekin klasda emas). 
- to parse — qayta ishlash, agar *kontekst* tushunarsiz bo'lsa *parsing* qilish. 
- placeholder — marker. 
- production (mode) — ishlab chiqarish (rejimi) (см. debug mode). 
- property — xususiyati (obekt). 
- to render — *render* qilish, formallashtirish. 
- related, relation — bog'langan, bog'lanish.
- resolve request — so'rovni oldindan qayta ishlash. 
- route — marshrut. 
- row (of the table) — satr(jadvallarniki). 
- setter — setter. 
- tabular input — tabli kiritish. 
- to validate — tekshirish. 
- valid — mos. 
- validator — validator. 
- validator class — validator klasi. 
- view — namoyish.
- query builder — so'rovlar konstruktori.