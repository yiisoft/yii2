# <div dir="rtl">العمل مع ال Forms</div>

<p dir="rtl">
في هذا الموضوع سنتعلم كيفية إنشاء صفحة تحتوي على form للحصول على البيانات من خلال المستخدمين، وستعرض هذه الصفحة form يحتوي على حقل لإدخال الإسم وحقل إدخال للبريد الإلكتروني.
وبعد الحصول على المعلومات الخاصة بهذه الحقول من المستخدم، ستقوم الصفحة بطباعة القيم التي تم إدخالها. 
</p>

<p dir="rtl">
    في هذا الشرح، ستقوم بإضافة <a href="../guide/structure-controllers.md">action</a> وصحفتين <a href="../guide/structure-views.md">views</a>، وستتعرف أيضا على طريقة إنشاء ال <a href="../guide/structure-models.md">model</a>.
</p>

<p dir="rtl">
من خلال هذا البرنامج التعليمي ، ستتعلم كيفية:
</p>

<ul dir="rtl">
    <li>إنشاء model لتمثيل البيانات التي تم إدخالها من خلال المستخدم عن طريق ال form.</li>
    <li>إنشاء rules للتحقق من صحة البيانات التي تم إدخالها.</li>
    <li>بناء html form داخل صفحة ال view.</li>
</ul>

## <div dir="rtl">إنشاء ال Model</div> <span id="creating-model"></span>

<p dir="rtl">
    يتم تمثيل البيانات التي يتم طلبها من خلال المستخدم عن طريق ال <code>EntryForm</code> model class  كما هو موضح أدناه، ويتم حفظ هذا الملف داخل المسار models، ويكون إسم ال model ومساره في مثالنا هذا هو  <code>models/EntryForm.php</code>. يرجى الرجوع إلى صفحة ال <a href="../guide/concept-autoloading.md">Class Autoloading</a> للحصول على مزيد من التفاصيل حول طريقة التعامل مع التسمية الخاصة بال class في Yii. 
</p>

```php
<?php

namespace app\models;

use Yii;
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

<p dir="rtl">
    هذا ال class يرث ال [[yii\base\Model]], وهو base class تم تصميمه من خلال ال Yii, وبشكل عام وظيفته هي تثمثيل البيانات الخاصة بأي نموذج.
</p>

<blockquote class="info"><p dir="rtl">
معلومة: يتم إستخدام ال  [[yii\base\Model]] كأصل لل model class <b>ولا</b> يرتبط بجداول قواعد البيانات. ويستخدم ال  [[yii\db\ActiveRecord]]  بالشكل الإعتيادي ليكون هو الأصل الذي من خلاله يتم الإرتباط بجداول بقواعد البيانات. 
</p></blockquote>

<p dir="rtl">
    يحتوي class ال <code>EntryForm</code> على متغيرين إثنين من نوع Public، هما <code>name</code> و <code>email</code>، واللذان يستخدمان في تخزين البيانات التي أدخلها المستخدم. كما يحتوي أيضًا على method باسم <code>rules()</code>، والتي تُرجع مجموعة
الشروط الخاصة بالبيانات للتحقق من صحتها. والشيفرة البرمجية الموجودة داخل ال rules method تعني: 
</p>

<ul dir="rtl">
    <li>كل من ال <code>name</code> وال <code>email</code> حقول الزامية (required).</li>
    <li>ال <code>email</code> حقل يجب أن يحتوي بداخله قيمة صحيحة تعبر عن البريد الإلكتروني (القواعد النحوية لكتابة البريد الإلكتروني).</li>
</ul>

<p dir="rtl">
    إذا كان لديك object من ال  <code>EntryForm</code> ويحتوي على البيانات التي أدخلها المستخدم،  فيمكنك حينها إستدعاء الدالة  [[yii\base\Model::validate()|validate()]] للتحقق من صحة البيانات. اذا فشلت عملية التحقق من صحة البيانات، فسيؤدي ذلك إلى تغيير قيمة ال  [[yii\base\Model::hasErrors|hasErrors]] إلى <code>true</code> ، بالإضافة الى ذلك يمكنك التعرف الى الأخطاء المتعلقة بهذه البيانات من خلال الدالة [[yii\base\Model::getErrors|errors]].
</p>

```php
<?php
$model = new EntryForm();
$model->name = 'Qiang';
$model->email = 'bad';
if ($model->validate()) {
    // Good!
} else {
    // Failure!
    // Use $model->getErrors()
}
```


## <div dir="rtl">إنشاء Action</div> <span id="creating-action"></span>

<p dir="rtl">
    الآن، ستحتاج إلى إنشاء <code>action</code> جديد في ال <code>site</code> controller وليكن إسمه <code>entry</code>، والذي سيقوم بدوره باستخدام ال model الجديد الذي قمنا بإنشائه. هذه العملية تم شرحها سابقا في الجزء التالي من التوثيق <a href="start-hello.md">Saying Hello - قل مرحبا</a>.
</p>

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\EntryForm;

class SiteController extends Controller
{
    // ...existing code...

    public function actionEntry()
    {
        $model = new EntryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // valid data received in $model

            // do something meaningful here about $model ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // either the page is initially displayed or there is some validation error
            return $this->render('entry', ['model' => $model]);
        }
    }
}
```
<p dir="rtl">
    أولا، يقوم ال action بإنشاء object من ال  <code>EntryForm</code>. ثم يحاول تعبئة البيانات لل object من خلال ال <code>$ _POST</code>، والتي يتم تقديمها في ال Yii من خلال ال [[yii\web\Request::post()]].
إذا تم ملء ال object بنجاح (على سبيل المثال، إذا قام المستخدم بإدخال البيانات داخل ال form ومن ثم قام بإرسالها(submitted html form))، فسيتم استدعاء ال [[yii\base\Model::validate()|validate()]] من خلال ال action للتأكد من صلاحية القيم المدخلة.
</p>


<blockquote class="info"><p dir="rtl">
    معلومة: يمثل التعبير Yii::$app ال  <a href="../guide/structure-applications.md">Application</a> instance الذي يمكن الوصول اليه من خلال ال singleton <br />(singleton globally accessible). وهو أيضا  <a href="../guide/concept-service-locator.md">service locator</a>  بحيث يوفر الدعم لل components مثل ال request, response, db..الخ، لدعم وظائف محددة. مثلا في المثال الموجود في الأعلى، فإن ال request هو component من ال application instance والذي يستخدم للوصول الى البيانات الموجودة داخل ال $_POST. 
</p></blockquote>

<p dir="rtl">
    إذا كان كل شيء على ما يرام، فسوف يقوم ال action بجلب ال view التالية: <code>entry-confirm</code>، وذلك لتأكيد أن العملية قد تمت بنجاح بالنسبة للمستخدم، أما إن كانت البيانات غير صحيحة، أو لم يتم إرسال أي بيانات، فإن ال view <code>entry</code> هي التي سيتم جلبها وعرضها للمستخدم، حيث يتم عرض ال Html form، مع أي رسائل تحذير بخصوص الأخطاء التي تم العثور عليها من عملية التحقق.
</p>

<blockquote class="note"><p dir="rtl">
ملاحظة: في هذا المثال البسيط، نعرض صفحة التأكيد فقط عند إرسال البيانات بشكل صحيح. عند الممارسة العملية، يجب عليك استخدام [[yii\web\Controller::refresh()|refresh()]] أو [[yii\web\Controller::redirect()|redirect()]] لتجنب أي مشكلة تحصل عن طريق ال resubmission والتي تندرج تحت العنوان <a href="https://en.wikipedia.org/wiki/Post/Redirect/Get">form resubmission problems</a>.
</p></blockquote>

## <div dir="rtl">إنشاء ال views</div> <span id="creating-views"></span>

<p dir="rtl">
    أخيرا، سنقوم بإنشاء صفحتين لل views الأولى بإسم <code>entry-confirm</code> والثانية <code>entry</code>. وهاتين الصفحتين سيتم جلبهم من خلال ال <code>entry</code> action. 
</p>

<p dir="rtl">
    ال <code>entry-confirm</code> ستقوم بكل بساطة بعرض الإسم والبريد الإلكتروني الذي تم إدخالهم من قبل المستخدم. ويجب حفظ هذه الصفحة بالمسار التالي: <code>views/site/entry-confirm.php</code> 
</p>

```php
<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->email) ?></li>
</ul>
```
<p dir="rtl">
   صفحة ال <code>entry</code> ستقوم بعرض ال HTML form. هذه الصفحة يجب أن يتم حفظها داخل المسار التالي: <code>views/site/entry.php</code>
</p>

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'email') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>
```
<p dir="rtl">
    تستخدم ال view أسلوب مميز لبناء ال Forms، وذلك عن طريق ال <a href="../guide/structure-widgets.md">widget</a> الذي يسمى ب  [[yii\widgets\ActiveForm|ActiveForm]]. إن الأسلوب المستخدم في هذا ال widget يقوم على إستخدام كل من الدالة <code>begin()</code> و <code>end()</code>  لجلب ال opening  وال closing form tags على التوالي (فتحة ال tag، ثم الإغلاق الخاص بهذا ال tag)، وبين الفتحة والإغلاق يمكنك إنشاء الحقول عن طريق إستخدام الدالة [[yii\widgets\ActiveForm::field()|field()]]. في هذا المثال كان الحقل الأول في ال form يشير الى name data، والثاني يشير الى ال email data، وبعد هذه الحقول ستجد الدالة المستخدمة لإنشاء ال Submit button وهي [[yii\helpers\Html::submitButton()]].
</p>

## <div dir="rtl">لنجرب المثال</div> <span id="trying-it-out"></span>

<p dir="rtl">
 لتشاهد آلية العمل لهذا المثال، والنتائج المتعلقة به، يمكنك إستخدام المتصفح والدخول الى الرابط التالي:
</p>

```
https://hostname/index.php?r=site%2Fentry
```

<p dir="rtl">
عند دخولك الى الرابط السابق، سترى صفحة تعرض Html form يحتوي على حقلين لإدخال المعلومات. أمام كل حقل إدخال ستجد label يشير إلى البيانات المطلوب إدخالها. إذا قمت بالنقر فوق الزر "submit" بدون
أي إدخال، أو إذا لم تقم بكتابة عنوان البريد الإلكتروني بشكل صحيح، فستظهر لك رسالة خطأ بجوار الحقل المقصود.
</p>

![Form with Validation Errors](../guide/images/start-form-validation.png)

<p dir="rtl">
    بعد إدخالك لإسم وبريد الكتروني صحيح، وقيامك بالنقر على زر submit، فإنك ستشاهد صفحة جديدة تقوم بعرض البيانات التي قمت بإدخالها.
</p>

![Confirmation of Data Entry](../guide/images/start-entry-confirmation.png)

## <div dir="rtl">كيف ظهر الخطأ؟ هل هو سحر؟!</div> <span id="magic-explained"></span>

<p dir="rtl">
قد تتساءل كيف يعمل ال Html form بالخفاء، وقد يبدو ذلك سحرا للوهلة الأولى، فهو يعرض ال label لكل حقل إدخال، ويعرض رسائل الخطأ إذا لم تقم بإدخال البيانات بشكل صحيح، وكل ذلك دون الحاجة لإعادة تحميل الصفحة.
</p>

<p dir="rtl">
    إن السحر الموجود لدينا هنا، هو كيفية العمل الخاصة بالشيفرة البرمجية لل form،  والتي تعمل بالخفاء، إن إجراء التحقق عن صحة البيانات يتم في البداية من جانب العميل -client side- وذلك باستخدام الجافا سكربت، ومن ثم -بعد تجاوز التحقق الخاص بالجافا سكربت- بتم تنفيذ التحقق من جانب ال server-side عبر ال PHP. ال  [[yii\widgets\ActiveForm]] ذكية بما فيه الكفاية لاستخراج ال rule الخاصة بالتحقق والتي قمت بإنشائها وتعريفها داخل ال <code>EntryForm</code>، ومن ثم تحويل هذه القواعد إلى شيفرة برمجية بالجافا سكربت قابلة للتنفيذ، ومن ثم استخدام هذه الشيفرة من قبل الجافا سكربت لإجراء التحقق من صحة البيانات. في حال قمت بإيقاف الجافا سكربت في المتصفح الخاص بك، سوف يستمر إجراء التحقق من جانب الخادم -server side-، كما هو موضح في ال action المسمى <code>actionEntry()</code>. وهذا يضمن صحة البيانات في جميع الظروف.
</p>

<blockquote class="warning"><p dir="rtl">
    تحذير: التحقق من جانب العميل -client side-  يوفر تجربة أفضل للمستخدم، لكن يجب الأخذ بعين الإعتبار أن التحقق من جانب الخادم -server- مطلوب دائمًا، سواء تم التحقق من جانب العميل أم لا.
</p></blockquote>

<p dir="rtl">
    يتم إنشاء ال labels الخاصة بحقول الإدخال بواسطة الدالة <code>field()</code>، وذلك من خلال إستخدام أسماء ال property الموجودة داخل ال model. على سبيل المثال، سيتم إنشاء ال label التالي <code>Name</code> للproperty التالية: <code>name</code>.
</p>

<p dir="rtl">
    كما يمكنك تعديل ال label الإفتراضي لأي حقل من خلال الشيفرة البرمجية التالية:
</p>

```php
<?= $form->field($model, 'name')->label('Your Name') ?>
<?= $form->field($model, 'email')->label('Your Email') ?>
```

<blockquote class="info"><p dir="rtl">
    معلومة: يوفر ال Yii العديد من ال widgets لمساعدتك في إنشاء views معقدة وديناميكية بسرعة. كما أنك ستتعلم في وقت لاحق كيف يمكنك إنشاء widget جديد، وستكتشف أن الموضوع سهل وبسيط، مما سيدفعك إلى كتابة الشيفرة البرمجية الخاصة بك داخل ال widget، والذي بدوره سيجعل من هذه الشيفرة قابلة للتطوير والإستخدام في أكثر من مكان في المستقبل. 
</p></blockquote>

## <div dir="rtl">الخلاصة</div> <span id="summary"></span>

<p dir="rtl">
    في هذا الجزء من التوثيق، تحدثنا عن كل جزء في ال MVC architectural pattern، لقد تعلمت الآن كيف يمكنك إنشاء model class ليقوم بتمثيل البيانات الخاصة بالمستخدمين، ومن ثم التحقق منها.   
</p>

<p dir="rtl">
    لقد تعلمت أيضًا كيفية الحصول على البيانات من المستخدمين، وكيفية عرض البيانات مرة أخرى في المتصفح. هذه المهمة يمكن أن تأخذ الكثير من الوقت عند تطوير أي تطبيق، ولكن، يوفر ال Yii العديد من ال widgets القوية، والتي تجعل من هذه المهمة أمرا سهلا للغاية.
</p>

<p dir="rtl">
    في الجزء القادم من هذا التوثيق، ستتعلم كيف يمكنك التعامل مع قواعد البيانات، والتي سنحتاجها -غالبا- مع كل تطبيق ستعمل عليه تقريبا. 
</p>
