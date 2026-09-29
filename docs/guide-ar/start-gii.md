# <div dir="rtl">إنشاء الشيفرة البرمجية من خلال ال gii</div>

<p dir="rtl">
    في هذا الجزء التعليمي سنتعرف على آلية التعامل مع ال <a href="https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide">Gii</a>، والذي يستخدم لإنتاج الشيفرة البرمجية الخاصة بمعظم الميزات والخصائص المشتركة في أغلب المواقع بشكل تلقائي، بالإضافة الى ذلك، فإن استخدام ال Gii لإنشاء الشيفرة البرمجية بشكل تلقائي يمثل مجموعة من المعلومات الصحيحة التي بتم إدخالها إعتمادا على التعليمات الموجودة في ال Gii Web Pages.
</p>

<p dir="rtl">
    من خلال هذا البرنامج التعليمي، ستتعلم كيفية:
</p>

<ul dir="rtl">
    <li>تفعيل ال Gii داخل التطبيق الخاص بك</li>
    <li>إستخدام ال Gii لإنشاء ال Active Record class</li>
    <li>إستخدام ال Gii لإنشاء الشيفرة البرمجية الخاصة بال CRUD إعتمادا على الجداول الموجودة في قاعدة البيانات</li>
    <li>تخصيص (customize) الشيفرة البرمجية التي سيتم إنتاجها من خلال ال Gii.</li>
</ul>

## <div dir="rtl">البدء باستخدام ال Gii</div> <span id="starting-gii"></span>

<p dir="rtl">
    يتم تقديم ال <a href="https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide">Gii</a> داخل على ال Yii على أنه <a href="../guide/structure-modules.md">module</a>، ويمكنك تفعيله من خلال الإعدادات الخاصة به والتي تجدها داخل ال application، وبالتحديد داخل ال property التالية [[yii\base\Application::modules|modules]]، واعتمادا على كيفية إنشائك للمشروع، فيمكنك إيجاد الشيفرة البرمجية التالية موجودة بشكل مسبق داخل ال <code>config/web.php</code>:  
</p>

```php
$config = [ ... ];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}
```

<p dir="rtl">
    في الإعدادت الموجودة في الأعلى، فإن التطبيق سيقوم بتضمين وتفعيل ال gii في حال كانت الحالة الخاصة بالتطبيق هي <a href="../guide/concept-configurations.md#environment-constants">development enviroment</a>، بالإضافة الى ذلك، فإنه يجب تضمين واستخدام ال module <code>gii</code>، والموجود ضمن ال class التالي [[yii\gii\Module]]. 
</p>


<p dir="rtl">
    اذا قمت بالتحقق من ال <a href="../guide/structure-entry-scripts.md">entry script</a> وبالتحديد صفحة ال <code>web/index.php</code> في التطبيق الخاص بك، ستجد هذه الأسطر، والتي يجب أن تجعل من ال <code>YII_ENV_DEV</code> ذات قيمة <code>true</code>.
</p>

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

<p dir="rtl">
    كل الشكر لهذا السطر البرمجي، التطبيق الآن أصبح بحالة ال development mode، وأصبح لديك ال Gii enabled بالفعل، والآن، يمكنك الوصول الى ال Gii من خلال عنوان ال URL التالي:     
</p>

```
https://hostname/index.php?r=gii
```

<blockquote class="note"><p dir="rtl">
    ملاحظة: إذا كنت تحاول الوصول إلى Gii  من جهاز آخر غير ال localhost، فسيتم رفض الوصول افتراضيًا لأغراض أمنية، ولكن، يمكنك إعداد ال Gii لإضافة مجموعة من ال IP Addresses المسموح لها بالوصول وذلك من خلال: 
</p></blockquote>

```php
'gii' => [
    'class' => 'yii\gii\Module',
    'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'] // عدل هذه حسب إحتياجاتك
],
```

![Gii](../guide/images/start-gii.png)


## <div dir="rtl">إنشاء ال Active Record Class من خلال ال Gii</div> <span id="generating-ar"></span>

<p dir="rtl">
    لإستخدام ال Gii لإنشاء ال Active Record class, قم باختيار ال "Model Generator" (من خلال النقر على الرابط الموجود بالصفحة الرئيسية لل Gii)، ومن ثم قم بتعبئة ال form كما يلي: 
</p>

<ul dir="rtl">
    <li>إسم الجدول: <code>country</code></li>
    <li><code>Country</code> :Model Class</li>
</ul>

![Model Generator](../guide/images/start-gii-model.png)

<p dir="rtl">
    والآن، قم بالنقر على الزر "Preview"، ستشاهد الآن <code>models/Country.php</code> قد تم إنشائها وإضافتها الى قائمة النتائج، اذا قمت بالنقر على إسم ال class، فإن المحتوى الخاص بهذا ال class سيتم عرضه.
</p>

<p dir="rtl">
    عند استخدام ال Gii، إذا كنت قد قمت بالفعل بإنشاء نفس الملف وستقوم بعمل overwriting عليه، فيمكنك النقر على زر <code>diff</code> الموجود بعد إسم ال class، لتشاهد الفرق بين الشيفرة البرمجية الحالية، والشيفرة البرمجية الجديدة. 
</p>

![Model Generator Preview](../guide/images/start-gii-model-preview.png)

<p dir="rtl">
    عند قيامك بعمل overwriting على ملف موجود، قم بالضغط على ال ckeckbox الموجودة بجانب كلمة overwrite، ومن ثم قم بالنقر على زر "Generate", اذا كان هذا الملف جديد، وغير موجود مسبقا، فيمكنك النقر مباشرة على "Generate"، بعد ذلك ستشاهد صفحة ال confirmation والتي تبين الشيفرات البرمجية التي تم إنشائها بنجاح.
</p>

<p dir="rtl">
    بعد فيامك بالنقر على زر Generate، فإنك ستشاهد صفحة ال confirmation page، والتي تقوم بدورها بتوضيح الشيفرات البرمجية التي تم إنشائها بنجاح، واذا كان الملف موجود، فإنك ستشاهد أيضا رسالة تعلمك بأن الملف قد تم تعديله وتمت إضافة الشيفرة الجديدة مكان القديمة.
</p>

## <div dir="rtl">إنشاء ال CRUD Code</div> <span id="generating-crud"></span>

<p dir="rtl">
    ال CRUD هي اختصار ل Create, Read, Update, And Delete (إنشاء، وقرائة، وتحديث، وحذف)، والتي تمثل أكثر المهمات المطلوبة للتعامل مع البيانات على مواقع الويب. ولإنشاء ال CRUD باستخدام ال Gii، قم باختيار ال "CRUD Generator" (من خلال النقر على الرابط الموجود بالصفحة الرئيسية لل Gii)، وهنا وبالبنسبة للمثال الخاص بال "country"، يمكنك تعبئة ال from بما يلي:
</p>

* Model Class: `app\models\Country`
* Search Model Class: `app\models\CountrySearch`
* Controller Class: `app\controllers\CountryController`

![CRUD Generator](../guide/images/start-gii-crud.png)

<p dir="rtl">
    بعد ذلك، قم بالنقر على زر ال "Preivew"، وستشاهد قائمة بالملفات التي سيتم إنشائها كما في الصورة أدناه.
</p>

![CRUD Generator Preview](../guide/images/start-gii-crud-preview.png)

<p dir="rtl">
    اذا قمت إنشاء الصفحتين <code>controllers/CountryController.php</code> و <code>views/country/index.php</code> عند حديثنا سابقا عند موضوع (التعامل مع قواعد البيانات)، فقم بالضغط على ال "overwrite" ليتم إستبدالهم. (الصفحات القديمة لا توجد فيها كل الخصائص التي سيتم إنتاجها من خلال ال Gii CRUD).
</p>

## <div dir="rtl"> لنجرب المثال </div> <span id="trying-it-out"></span>

<p dir="rtl">
لتشاهد آلية العمل لهذا المثال، والنتائج المتعلقة به، يمكنك إستخدام المتصفح والدخول الى الرابط التالي:
</p>

```
https://hostname/index.php?r=country%2Findex
```

<p dir="rtl">
    عند دخولك إلى الرابط أعلاه، ستشاهد مجموعة الدول التي تم إستدعائها من جدول ال country من قاعدة البيانات، ويمكنك التعامل مع هذا ال grid من حيث الترتيب أو التصفية بنائا على الشروط التي ستقوم بإدخالها في مربعات النص أعلى الأعمدة.
</p>

<p dir="rtl">
لكل دولة تم جلبها وعرضها داخل ال Grid، هناك مجموعة من الخيارات التي يمكنك التعامل معها بشكل إفتراضي، مثل ال view لعرض التفاصيل الخاصة بالدولة المختارة، أو تحديث المعلومات، الخاصة بالدولة، أو حذف هذه الدولة، بالإضافة إلى ذلك يمكنك النقر على "Create Country" الموجودة في أعلى ال Grid والتي ستأخذك بدورها الى صفحة تحتوي form لإنشاء ال country.
</p>

![Data Grid of Countries](../guide/images/start-gii-country-grid.png)

![Updating a Country](../guide/images/start-gii-country-update.png)

<p dir="rtl">
    فيما يلي قائمة بالملفات التي تم إنشاؤها من خلال ال Gii، في حالة رغبتك في التحقق من كيفية عمل هذه الميزات والإطلاع على الشيفرة البرمجية وتخصيصها حسب الرغبة:
</p>

* Controller: `controllers/CountryController.php`
* Models: `models/Country.php` and `models/CountrySearch.php`
* Views: `views/country/*.php`

<blockquote class="info"><p dir="rtl">
معلومة: تم تصميم ال Gii لتكون أداة إنشاء شيفرات برمجية قابلة للتخصيص بشكل كبير للغاية. اذا قمت باستخدامه بحكمة،فإنك ستقوم بتسريع وتيرة التطوير الخاصة بالتطبيق الخاص بك. لمزيد من التفاصيل، يرجى الذهاب إلى الجزء الخاص بال  <a href="https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide">Gii</a>. 
</p></blockquote>

## <div dir="rtl">الخلاصة</div> <span id="summary"></span>

<p dir="rtl">
في هذا الجزء من التوثيق، لقد تعلمنا آلية استخدام ال Gii لإنشاء الشيفرة البرمجية الخاصة بال CRUD، وتحدثنا عن الوظائف التي تقوم فيها، وكيف يمكننا من خلالها إتمام العمليات الخاصة بالبيانات من إدخال وتحديث وحذف وعرض للبيانات من قاعدة البيانات.
</p>
