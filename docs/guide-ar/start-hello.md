# <div dir="rtl">قل مرحبا - Saying Hello</div>

<p dir="rtl">
    في هذا الموضوع سنتعرف على كيفية إنشاء صفحة "Hello" جديدة في التطبيق الذي قمت بتثبيته، ولتحقيق ذلك، يجب عليك القيام بإنشاء <a href="../guide/structure-controllers.md#creating-actions">action</a> و <a href="../guide/structure-views.md">view</a> لهذه الصفحة:
</p>

<ul dir="rtl">
    <li>سيقوم التطبيق بإرسال ال request الخاص بالصفحة إلى ال action.</li>
    <li>وسيقوم ال action بدوره في جلب ال view التي تعرض كلمة "Hello" إلى المستخدم النهائي.</li>
</ul>

<p dir="rtl">
    من خلال هذا البرنامج التعليمي ، ستتعلم ثلاثة أشياء: 
</p>

<ol dir="rtl">
    <li>كيفية إنشاء <a href="../guide/structure-controllers.md#creating-actions">action</a> ليقوم بإستقبال ال request ومن ثم الرد (respond) عليها.</li>
    <li>كيفية إنشاء <a href="../guide/structure-views.md">view</a> وإضافة المحتوى الى ال respond.</li>
    <li>و كيفية إنشاء التطبيق لل requests التي يوجهها لل <a href="../guide/structure-controllers.md#creating-actions">actions</a>. </li>
</ol>

##  <div dir="rtl">إنشاء ال Action</div> <span id="creating-action"></span>

<p dir="rtl">
    لإنشاء صفحة "Hello"، ستقوم بإنشاء <code>say</code> <a href="../guide/structure-controllers.md#creating-actions">action</a> والذي بدوره سيقوم  بقراءة ال <code>message</code> parameter من ال request، ومن ثم عرض ال <code>message</code> مرة أخرى إلى المستخدم. إذا كان ال request لا يحمل معه ال message parameter فإن ال action سيقوم بطباعة message إفتراضية وهي "Hello".
</p>

<blockqoute><p dir="rtl">
    معلومة: ال <a href="../guide/structure-controllers.md#creating-actions">Actions</a> هي الكائنات(objects) التي يمكن للمستخدمين من الوصول اليها وتنفيذ ما في بداخلها بشكل مباشر.  يتم تجميع هذه ال Actions بواسطة ال <a href="../guide/structure-controllers.md">controllers</a>. ونتيجة لذلك فإن ال response الراجعة للمستخدم ستكون هي نتيجة التنفيذ الخاصة بال action. 
</p></blockqoute>

<p dir="rtl">
    يجب تعريف ال actions داخل ال <a href="../guide/structure-controllers.md">controller</a>، ولتبسيط الفكرة، سنقوم بتعريف ال <code>say</code> action داخل أحد ال controller الموجود مسبقا وهو ال <code>siteController</code>. هذا ال controller ستجده داخل المسار <code>controllers/siteController.php</code>. ومن هنا سنبدأ بإضافة ال action الجديد: 
</p>

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...existing code...

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

<p dir="rtl">
    في الشيفرة البرمجية السابقة ، تم تعريف ال <code>say</code> action من خلال إنشاء الدالة <code>actionSay</code> داخل الكلاس  <code>siteController</code>. يستخدم ال Yii كلمة action ك prefix للدوال للتميز بين الدوال ال action و ال non-action في ال controller، كما يستخدم الإسم الخاص بال action مباشرة بعد ال prefix، ويتم عمل mapping بين ال action method name وال action id ليستطيع المستخدم من الوصول للدالة المنشئة من خلال ال action id. 
</p>

<p dir="rtl">
    عندما يتعلق الأمر بتسمية ال action الخاصة بك، يجب عليك  أن تفهم كيف يقوم ال Yii بالتعامل مع ال action id، ال action id يجب أن يكون دائما أحرف lower case، وإذا إحتوى ال action id على عدة كلمات فإن الفاصل بينهم سيكون الداش (dashes)  مثل  <code>create-comment</code>، ويتم ربط ال action id بال action method name من  خلال حذف ال dashes من ال IDs، ويتم عمل capitalizing لأول خرف من كل كلمة، وإضافة ال prefix الى الناتج السابق من التحويل. مثال: ال action id المسمى ب <code>create-comment</code> سيتم تحويله الى ال action method name التالي: <code>actionCreateComment</code>. 
</p>

<p dir="rtl">
    في المثال السابق، يقوم ال action method على إستقبال parameter يسمى ب <code>$message</code>، والذي يملك قيمة إفتراضية وهي <code>"Hello"</code> (وهي مشابة تماما لطريقة وضع القيم الإفتراضية لل argument في ال PHP). عندما يستقبل التطبيق ال request ويحدد أن ال action المطلوب للتعامل مع الطلب (request) هو <code>say</code>، فإن التطبيق سيقوم بإسناد القيمة الموجودة بال request الى ال parameter الموجود بال action بشرط أن يكون الإسم الموجود بال request هو نفسه الموجود في ال action. ولتبسيط الموضوع يمكن القول أن ال request اذا احتوى على كلمة <code>message</code> وقيمة هذا ال parameter هي <code>"GoodBye"</code>، فإن ال <code>$message</code> الموجودة في ال action ستصبح قيمتها <code>"GoodBye"</code>.  
</p>


<p dir="rtl">
    من خلال ال action method، يتم استدعاء ال  [[yii \ web \ Controller :: render () | render ()]] لتقديم
الملف الخاص بال view والمسمى هنا ب <code>say</code>. أيضا فإن ال <code>message</code> يتم تمرريرها الى ال view مما يسمح لك باستخدام هذا ال parameter داخل ال view. النتيجة المرجعة لل view تتم معالجتها وإرجاعها من خلال ال action method، وهذه النتائج سيتم إستقبالها من خلال المتصفح الخاص بالمستخدم ليتم عرضها وكأنها (جزء من صفحة Html كاملة). 
</p>

## <div dir="rtl">إنشاء ال View</div> <span id="creating-view"></span>

<p dir="rtl">
    ال <a href="../guide/structure-views.md">Views</a> هي شيفرات برمجية كتبت ﻹنشاء المحتوى المناسب بنائا على ال response الراجع اليها من خلال ال action. 
    بالنسبة إلى مثال "Hello" ، الآن سنقوم بإنشاء view مسمى ب <code>say</code>، والذي سيقوم بدوره بطباعة ال <code>message</code> التي تم إستلامها من ال action، شاهد المثال: 
</p>


```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

<p dir="rtl">
    صفحة ال view <code>say</code> يجب أن يتم حفظها داخل المسار التالي: <code>views/site/say.php</code>. عندما يتم إستدعاء الدالة [[yii\web\Controller::render()|render()]] من قبل ال action، فإنه سينظر للمسار على الشكل التالي: <code>views/ControllerID/ViewName.php</code>، بحيث يكون في مثالنا السابق ال ContollerID هو ال <code>site</code> وال viewName هو <code>say</code>.
</p>

<p dir="rtl">
    ملاحظة:  في الشيفرة البرمجية أعلاه، تكون ال <code>message</code> مضمنة داخل ال  [[yii\helpers\Html::encode()]] قبل أن يتم طباعتها، هذا الأمر ضروري لأن ال parameter التي تأتي من المستخدم النهائي لا يجب الوثوق بها،  فهي يمكن أن تحتوي على شيفرات برمجية تستغل الضعف الحاص بك بموضوع الأمان مثل <a href="http://en.wikipedia.org/wiki/Cross-site_scripting">vulnerable to XSS attack</a> عن طريق دمج JS code مع ال parameter. 
</p>

<p dir="rtl">
    وبطبيعة الحال، يمكنك وضع المزيد من المحتوى في صفحة ال <code>say</code> view. ويمكن أن يتكون هذا المحتوى من HTML tag و plain text وحتى PHP statements.
في الواقع، تعد ال <code>say</code> view مجرد شيفرة برمجية بلغة ال PHP والتي يتم تنفيذها بواسطة [[yii \ web \ Controller :: render () | render ()]].
المحتوى الذي سيتم طباعته من خلال ال view سيتم إرجاعه الى التطبيق من خلال ال response، وسيقوم التطبيق بدوره بإخراج هذه النتيجة إلى المستخدم النهائي.
</p>


Trying it Out <span id="trying-it-out"></span>
-------------

After creating the action and the view, you may access the new page by accessing the following URL:

```
http://hostname/index.php?r=site%2Fsay&message=Hello+World
```

![Hello World](../guide/images/start-hello-world.png)

This URL will result in a page displaying "Hello World". The page shares the same header and footer as the other application pages. 

If you omit the `message` parameter in the URL, you would see the page display just "Hello". This is because `message` is passed as a parameter to the `actionSay()` method, and when it is omitted,
the default value of `"Hello"` will be used instead.

> Info: The new page shares the same header and footer as other pages because the [[yii\web\Controller::render()|render()]]
  method will automatically embed the result of the `say` view in a so-called [layout](structure-views.md#layouts) which in this
  case is located at `views/layouts/main.php`.

The `r` parameter in the above URL requires more explanation. It stands for [route](runtime-routing.md), an application wide unique ID
that refers to an action. The route's format is `ControllerID/ActionID`. When the application receives
a request, it will check this parameter, using the `ControllerID` part to determine which controller
class should be instantiated to handle the request. Then, the controller will use the `ActionID` part
to determine which action should be instantiated to do the real work. In this example case, the route `site/say`
will be resolved to the `SiteController` controller class and the `say` action. As a result,
the `SiteController::actionSay()` method will be called to handle the request.

> Info: Like actions, controllers also have IDs that uniquely identify them in an application.
  Controller IDs use the same naming rules as action IDs. Controller class names are derived from
  controller IDs by removing dashes from the IDs, capitalizing the first letter in each word,
  and suffixing the resulting string with the word `Controller`. For example, the controller ID `post-comment` corresponds
  to the controller class name `PostCommentController`.


Summary <span id="summary"></span>
-------

In this section, you have touched the controller and view parts of the MVC architectural pattern.
You created an action as part of a controller to handle a specific request. And you also created a view
to compose the response's content. In this simple example, no model was involved as the only data used was the `message` parameter.

You have also learned about routes in Yii, which act as the bridge between user requests and controller actions.

In the next section, you will learn how to create a model, and add a new page containing an HTML form.
