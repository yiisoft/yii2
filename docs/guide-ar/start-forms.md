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

<blockquote><p dir="rtl">
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


<blockquote><p dir="rtl">
    معلومة: يمثل التعبير Yii::$app ال  <a href="../guide/structure-applications.md">Application</a> instance الذي يمكن الوصول اليه من خلال ال singleton <br />(singleton globally accessible). وهو أيضا  <a href="../guide/concept-service-locator.md">service locator</a>  بحيث يوفر الدعم لل components مثل ال request, response, db..الخ، لدعم وظائف محددة. مثلا في المثال الموجود في الأعلى، فإن ال request هو component من ال application instance والذي يستخدم للوصول الى البيانات الموجودة داخل ال $_POST. 
</p></blockquote>

<p dir="rtl">
    إذا كان كل شيء على ما يرام، فسوف يقوم ال action بجلب ال view التالية: <code>entry-confirm</code>، وذلك لتأكيد أن العملية قد تمت بنجاح بالنسبة للمستخدم، أما إن كانت البيانات غير صحيحة، أو لم يتم إرسال أي بيانات، فإن ال view <code>entry</code> هي التي سيتم جلبها وعرضها للمستخدم، حيث يتم عرض ال Html form، مع أي رسائل تحذير بخصوص الأخطاء التي تم العثور عليها من عملية التحقق.
</p>

<blockquote><p dir="rtl">
ملاحظة: في هذا المثال البسيط، نعرض صفحة التأكيد فقط عند إرسال البيانات بشكل صحيح. عند الممارسة العملية، يجب عليك استخدام [[yii\web\Controller::refresh()|refresh()]] أو [[yii\web\Controller::redirect()|redirect()]] لتجنب أي مشكلة تحصل عن طريق ال resubmission والتي تندرج تحت العنوان <a href="http://en.wikipedia.org/wiki/Post/Redirect/Get">form resubmission problems</a>.
</p></blockquote>

<div dir="rtl">إنشاء ال views</a> <span id="creating-views"></span>
--------------

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

The `entry` view displays an HTML form. It should be stored in the file `views/site/entry.php`.

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

The view uses a powerful [widget](structure-widgets.md) called [[yii\widgets\ActiveForm|ActiveForm]] to
build the HTML form. The `begin()` and `end()` methods of the widget render the opening and closing
form tags, respectively. Between the two method calls, input fields are created by the
[[yii\widgets\ActiveForm::field()|field()]] method. The first input field is for the "name" data,
and the second for the "email" data. After the input fields, the [[yii\helpers\Html::submitButton()]] method
is called to generate a submit button.


Trying it Out <span id="trying-it-out"></span>
-------------

To see how it works, use your browser to access the following URL:

```
http://hostname/index.php?r=site%2Fentry
```

You will see a page displaying a form with two input fields. In front of each input field, a label indicates what data is to be entered. If you click the submit button without
entering anything, or if you do not provide a valid email address, you will see an error message displayed next to each problematic input field.

![Form with Validation Errors](../guide/images/start-form-validation.png)

After entering a valid name and email address and clicking the submit button, you will see a new page
displaying the data that you just entered.

![Confirmation of Data Entry](../guide/images/start-entry-confirmation.png)



### Magic Explained <span id="magic-explained"></span>

You may wonder how the HTML form works behind the scene, because it seems almost magical that it can
display a label for each input field and show error messages if you do not enter the data correctly
without reloading the page.

Yes, the data validation is initially done on the client-side using JavaScript, and secondarily performed on the server-side via PHP.
[[yii\widgets\ActiveForm]] is smart enough to extract the validation rules that you have declared in `EntryForm`,
turn them into executable JavaScript code, and use the JavaScript to perform data validation. In case you have disabled
JavaScript on your browser, the validation will still be performed on the server-side, as shown in
the `actionEntry()` method. This ensures data validity in all circumstances.

> Warning: Client-side validation is a convenience that provides for a better user experience. Server-side validation
  is always required, whether or not client-side validation is in place.

The labels for input fields are generated by the `field()` method, using the property names from the model.
For example, the label `Name` will be generated for the `name` property. 

You may customize a label within a view using 
the following code:

```php
<?= $form->field($model, 'name')->label('Your Name') ?>
<?= $form->field($model, 'email')->label('Your Email') ?>
```

> Info: Yii provides many such widgets to help you quickly build complex and dynamic views.
  As you will learn later, writing a new widget is also extremely easy. You may want to turn much of your
  view code into reusable widgets to simplify view development in future.


Summary <span id="summary"></span>
-------

In this section of the guide, you have touched every part in the MVC architectural pattern. You have learned how
to create a model class to represent the user data and validate said data.

You have also learned how to get data from users and how to display data back in the browser. This is a task that
could take you a lot of time when developing an application, but Yii provides powerful widgets
to make this task very easy.

In the next section, you will learn how to work with databases, which are needed in nearly every application.
