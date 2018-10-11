# <div dir="rtl">ما هي بيئة العمل Yii</div>

<p dir="rtl">Yii هو إطار PHP عالي الأداء يعتمد على المكونات لتطوير تطبيقات الويب الحديثة بسرعة.
إن الاسم "Yii" (يُنطق بـ "يي" أو "[جي:]" يعني "بسيطًا وتطوريًا" باللغة الصينية. ومن الممكن ايضا
    اعتباره اختصارًا لـ <b>Yes It Is</b>!</p>


# <div dir="rtl">ما هي أفضل التطبيقات أو البرمجيات التي يمكن برمجتها وتتناسب مع ال Yii</div>

<p dir="rtl">
Yii هو إطار عام لبرمجة الويب ، مما يعني أنه يمكن استخدامه لتطوير كافة أنواع
تطبيقات الويب باستخدام PHP. وذلك بسبب البنية القائمة على  البنية التركيبة لبيئة العمل وترابطها مع المكونات والتخزين المؤقت، وهو مناسب بشكل خاص لتطوير portals, forums, content management systems (CMS), e-commerce projects, RESTful Web services. وما إلى ذلك.
</p>


# <div dir="rtl">كيف يمكن مقارنة بيئة العمل الخاصة بال Yii مع الأطر أو بيئات العمل الأخرى؟</div>

<p dir="rtl">
    إذا كنت بالفعل على دراية بإطار العمل الأخرى ، فيمكنك معرفة كيف تتم مقارنة ال Yii:

<ul  dir="rtl">
    <li> مثل معظم أطر عمل ال PHP ، يطبق Yii النمط المعماري MVC (Model-View-Controller).</li>
<li> ال Yii يتبنى الفلسفة التي تقول أن الشيفرة البرمجية يجب أن تكتب بأسهل طريقها وادقها، ولكنها بذات الوقت يجب أن تكون أنيقة الكتابة مظهرا ومضمونا (شكلا وتطيبقا).</li>
<li> ال Yii هو إطار متكامل  (full stack) يوفر العديد من الميزات الجاهزة للإستخدام والمعدة مسبقا، مثل ال query builders وال ActiveRecord لقواعد البيانات العلاقئية (relational) وغير العلائقية (Nosql)، بالإضافة الى دعم وتجهيز ال RESTful API  والتخزين المؤقت (caching) وغيرها الكثير. </li> 
<li> من مميزات ال Yii إمكانية التعديل (استبدال جزء معين أو تخيصيص وإضافة) جزء معين على أغلب ال Yii core code، وبالإضافة الى هذا، يمكنك بناء ملحقات برمجية اعتمادا على ال core code، ومن ثم نشر هذه الشيفرة وتوزيعها واستخدامها دون وجود أي مشاكل أو صعوبة تذكر.</li>
<li> الأداء العالي هو الهدف الأساسي من ال Yii.</li>
    </ul>
</p>

<p dir="rtl">
ال Yii إطار عمل صمم من قبل فريق برمجي متكامل، فهو ليس مجرد عمل فردي ،  بل يتكون من فريق تطوير أساسي وقوي(http://www.yiiframework.com/team/) ، بالإضافة إلى منتدى كبير
من المهنيين الذين يساهمون باستمرار في تطوير هذا الإطار. فريق المطورين الخاص بال Yii
يراقب عن كثب أحدث اتجاهات تطوير الويب وأفضل الممارسات والمميزات التي
وجدت في الأطر والمشاريع الأخرى. وتدرج بانتظام بإضافة أفضل الممارسات والميزات الى ال Yii عبر واجهات بسيطة وأنيقة.
</p>


Yii Versions
------------

Yii currently has two major versions available: 1.1 and 2.0. Version 1.1 is the old generation and is now in maintenance mode. Version 2.0 is a complete rewrite of Yii, adopting the latest
technologies and protocols, including Composer, PSR, namespaces, traits, and so forth. Version 2.0 represents the current
generation of the framework and will receive the main development efforts over the next few years.
This guide is mainly about version 2.0.


Requirements and Prerequisites
------------------------------

Yii 2.0 requires PHP 5.4.0 or above and runs best with the latest version of PHP 7. You can find more detailed
requirements for individual features by running the requirement checker included in every Yii release.

Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
Yii 2.0 also makes use of the latest features of PHP, such as [namespaces](http://www.php.net/manual/en/language.namespaces.php)
and [traits](http://www.php.net/manual/en/language.oop5.traits.php). Understanding these concepts will help
you more easily pick up Yii 2.0.

