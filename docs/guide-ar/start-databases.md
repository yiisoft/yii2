# <div dir="rtl">التعامل مع قواعد البيانات</div>

<p dir="rtl">
    في هذا الجزء التعليمي ستتعلم آلية إنشاء صفحة جديدة تعرض بيانات يتم جلبها من قاعدة البيانات  -في هذا المثال، البيانات تخص ال country-، هذه البيانات سيتم جلبها من جدول موجود في قاعدة البيانات يسمى ب <code>country</code>. لتحقيق هذا المهمة، ستقوم بعمل ال config الخاص بالإتصال بقاعدة بيانات، بالإضافة لإنشاء ال <a href="../guide/db-active-record.md">Active Record</a> class، وتعريف ال <a href="../guide/structure-controllers.md">action</a>، وإنشاء <a href="../guide/structure-views.md">view</a> لهذه الصفحة. 
</p>


<p dir="rtl">
 في هذا الشرح ستتعلم كيف يمكنك القيام بما يلي: 
</p>

<ul dir="rtl">
    <li>إعداد ال connection الخاص بقاعدة البيانات</li>
    <li> التعرف على ال active record.</li>
    <li>إنشاء جمل إستعلام عن البياتات بإستخدام ال active record class</li>
    <li>عرض البيانات داخل ال view من خلال ال paginated fashion.</li>
</ul>

<p dir="rtl">
    ملاحظة: من أجل الانتهاء من هذا الجزء التعليمي، يجب أن يكون لديك المعرفة الأساسية والخبرة باستخدام قواعد البيانات. وعلى وجه الخصوص، يجب أن تعرف كيفية إنشاء قواعد البيانات، وكيفية تنفيذ ال statements SQL باستخدام أي DB client tool.
</p>

## <div dir="rtl">إعداد قاعدة البيانات</div> <span id="preparing-database"></span>

<p dir="rtl">
    في البداية، عليك إنشاء قاعدة بيانات تسمى ب <code>yii2basic</code>، والتي ستستخدم لجلب البيانات الخاصة بالتطبيق، ويمكنك إستخدام أي من ال SQLite, MySql, PostgreSQL, MSSQL or Oracle database, ال Yii بشكل افتراضي بدعم العديد من قواعد البيانات والتي يمكنك إستخدامها مباشرة في التطبيق الخاص بك، ولتبسيط الأمور، ال MySql هي التي سيتم إستخدامها في في هذا الشرح. 
</p>

<blockquote><p dir="rtl">
    معلومة: إذا كنت ترغب بالحصول على خيارات متقدمة  مثل دعم ال <code>JSON</code> الموجود داخل MariaDB، فيمكنك من إستخدام أحد ال Extension المذكوره بالأسفل للقيام بهذه المهمة بدلا من الإستغناء عن ال MySql، فإستخدام MariaDB بدلا عن ال MySql لم يعد صحيحا تماما. 
</p></blockquote>

<p dir="rtl">
    بعد قيامك بإنشاء قاعدة البيانات، سنقوم بإنشاء جدول إسمه <code>country</code>، ومن ثم سنقوم بإدخال بعض البيانات كعينة للإختيار، وللقيام بذلك، قم بتنفيذ الأوامر التالية: 
</p>

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

<p dir="rtl">
    الآن، أصبح لديك قاعدة بيانات إسمها <code>yii2basic</code>، وتحوي بداخلها جدول بثلاث أعمدة يسمى ب <code>country</code>، وفيه 10 صفوف من البيانات.     
</p>

## <div dir="rtl">إعدادات الإتصال الخاصة بقواعد البيانات - Configuring a DB Connection</div> <span id="configuring-db-connection"></span>

<p dir="rtl">
    قبل أن تكمل الشرح، تأكد من تثبيت ال PHP <a href="http://www.php.net/manual/en/book.pdo.php">PDO</a> وال PDO driver، بالنسبة لهذا المثال، فإننا سنستخدم ال driver الخاص بال MySql وهو ال <code>pdo_mysql</code>، وهذه هي المتطلبات الأساسية لبناء أي التطبيق اذا كان التطبيق يستخدم ال relational database. 
</p>

<blockquote><p dir="rtl">
   ملاحظة: يمكنك تقعيل ال PDO مباشرة من خلال الدخول الى php.ini ومن ثم حذف الفاصلة المنقوطة قبل السطر التالي: <code>extension=php_pdo.dll</code>
    كما يمكنك تفعيل ال driver المطلوب عن طريق حذف الفاصلة المنقوطة قبل ال driver المقصود مثل: 
<code>extension=php_pdo_mysql.dll</code>
    ويمكنك الإطلاع على المزيد من هنا: 
<a href="http://php.net/manual/en/pdo.installation.php">pdo installation</a>    
</p></blockquote>

<p dir="rtl">
    بعد إتمام ما سبق، قم بفتح الملف <code>config/db.php</code> ومن ثم قم بتعديل ال parameters لتكون الإعدادات الخاصة بقاعدة البيانات صحيحة -الإعدادت الخاصة بك-، بشكل افتراضي، يحتوي الملف على ما يلي: 
</p>
    

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

<p dir="rtl">
    يمثل ملف ال <code>config/db.php</code> أداة نموذجية تعتمد على الملفات للقيام بال <a href="../guide/concept-configurations.md">configuration</a>. يقوم ملف ال configuration بتحديد ال parameters المطلوبة لإنشاء وإعداد ال instance الخاص بال <code>[[yii\db\Connection]]</code>، ومن خلالها يمكنك إجراء عمليات الإستعلام على قاعدة البيانات.
</p>

<p dir="rtl">
 الإعدادات الخاصة بالإتصال بقاعدة البيانات والمذكورة في الملف أعلاه يمكن الوصول اليها من خلال التطبيق عن طريق تنفيذ الأمر التالي
    <code>Yii::$app->db</code>
</p>

<blockquote><p dir="rtl">
    معلومة: سيتم تضمين ملف ال <code>config/db.php</code> من خلال  ال main application configuration والذي يتمثل بالملف <code>config/web.php</code>، والذي يقوم بدوره بتحديد كيف يمكن تهيئة ال instance الخاص <a href="../guide/concept-configurations.md">بالتطبيق</a>، لمزيد من المعلومات، يرجى الإطلاع على قسم ال <a href="../guide/concept-configurations.md">Configurations</a>.
</p></blockquote>

<p dir="rtl">
    إذا كنت بحاجة إلى العمل مع إحدى قواعد البيانات الغير مدعومة بشكل إفتراضي من ال Yii، فيمكنك التحقق من الإضافات التالية:
</p>

<ul dir="rtl">
    <li><a href="https://github.com/edgardmessias/yii2-informix">Informix</a></li>
<li> <a href="https://github.com/edgardmessias/yii2-ibm-db2">IBM DB2</a></li>
<li> <a href="https://github.com/edgardmessias/yii2-firebird">Firebird</a></li>
<li> <a href="https://github.com/sam-it/yii2-mariadb">MariaDB</a></li>
</ul>

## <div dir="rtl">إنشاء ال Active Record<span id="creating-active-record"></span>

<p dir="rtl">
    لجلب البيانات وعرضها من جدول ال <code>country</code>، سنقوم بإضافة ال <a href="../guide/db-active-record.md">Active Record</a> الى ال class المسمى ب <code>country</code>، والموجود في المسار <code>models/Country.php</code>.
</p>

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
}
```
<p dir="rtl">
    يرث ال <code>Country</code> Class ال [[yii\db\ActiveRecord]]، ولذلك، أنت لست بحاجة لكتابة أي شيفرة برمجية بداخله، فقط الشيفرة التي تشاهدها بالأعلى. سيقوم ال Yii بشكل تلقائي بالحصول على إسم الجدول في قاعدة البيانات من خلال إسم ال Class. 
</p>

<blockquote><p dir="rtl">
معلومة: إذا لم يكن من الممكن إجراء مطابقة مباشرة بين اسم ال class واسم الجدول، فيمكنك تجاوز هذه المشكلة من خلال إستخدام الدالة  [[yii\db\ActiveRecord::tableName()]] ، والتي ستقوم بعمل override على اسم الجدول. 
</p></blockquote>

<p dir="rtl">
    من خلال إستخدام ال <code>Country</code> class، يمكنك التحكم بكل سهولة بالبيانات الموجودة داخل جدول ال <code>country</code>، شاهد هذه الشيفرة البرمجية لتبسيط الفكرة:  
</p>

```php
use app\models\Country;

// get all rows from the country table and order them by "name"
$countries = Country::find()->orderBy('name')->all();

// get the row whose primary key is "US"
$country = Country::findOne('US');

// displays "United States"
echo $country->name;

// modifies the country name to be "U.S.A." and save it to database
$country->name = 'U.S.A.';
$country->save();
```

<blockquote><p dir="rtl">
    معلومة: يعتبر ال Active Record وسيلة قوية للوصول إلى بيانات قاعدة البيانات والتعامل معها بطريقة ال object oriented.
    ستجد معلومات أكثر تفصيلاً في الجزء الخاص بال <a href="../guide/db-active-record.md">Active Record</a>. بالإضافة الى ذلك، يمكنك التفاعل مباشرة مع قاعدة البيانات باستخدام lower-level data accessing والتي تسمى ب <a href="../guide/db-dao.md">Database Access Objects</a>.
</p></blockquote>

## <div dir="rtl">إنشاء ال Action</a> <span id="creating-action"></span>

<p dir="rtl">
لعرض بيانات ال country للمستخدمين، يلزمك إنشاء action جديد، وبدلاً من وضع ال action الجديد في ال <code>site</code> controller كما فعلنا في المرات السابقة، سنقوم بإنشاء controller جديد، ومن ثم سنقوم بوضع ال action بداخله، والسبب المنطقي لهذا العمل أنك ستقوم بتجميع الشيفرة البرمجية المسؤولة عن أداء وظائف معينة في مكان واحد، وبهذا فإن جميع الإجرائات التي تخص ال country من المنطقي أن تكون موجودة داخل ال <code>CountryController</code>، والآن لنقم بإنشاء هذا ال controller الجديد، وال action الجديد وسيكون باسم <code>index</code>، كما هو موضح أدناه: 
</p>

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
<p dir="rtl">
    قم بحفظ الشيفرة البرمجية التي بالأعلى داخل هذا الملف <code>controllers/CountryController.php</code>
</p>

<p dir="rtl">
    يقوم ال <code>index</code> action باستخدام ال <code>Country::find()</code>، وهذه الدالة موجودة من ضمن ال <code>active record</code>، وتقوم هذه الدالة على بناء الإستعلام الخاص بقاعدة البيانات مما يسمح باسترجاع جميع البيانات الموجودة بداخل جدول ال country، ولتحديد الحد الأعلى المسموح إرجاعه في كل request، يمكنك إستخدام ال <code>[[yii\data\Pagination]]</code> object كوسيلة مساعدة، ويقدم هذا ال object غرضين أساسيين وهما: 
</p>

* Sets the `offset` and `limit` clauses for the SQL statement represented by the query so that it only
  returns a single page of data at a time (at most 5 rows in a page).
* It's used in the view to display a pager consisting of a list of page buttons, as will be explained in
  the next subsection.

At the end of the code, the `index` action renders a view named `index`, and passes the country data as well as the pagination
information to it.


Creating a View <span id="creating-view"></span>
---------------

Under the `views` directory, first create a sub-directory named `country`. This folder will be used to hold all the
views rendered by the `country` controller. Within the `views/country` directory, create a file named `index.php`
containing the following:

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

The view has two sections relative to displaying the country data. In the first part, the provided country data is traversed and rendered as an unordered HTML list.
In the second part, a [[yii\widgets\LinkPager]] widget is rendered using the pagination information passed from the action.
The `LinkPager` widget displays a list of page buttons. Clicking on any of them will refresh the country data
in the corresponding page.


Trying it Out <span id="trying-it-out"></span>
-------------

To see how all of the above code works, use your browser to access the following URL:

```
http://hostname/index.php?r=country%2Findex
```

![Country List](../guide/images/start-country-list.png)

At first, you will see a page showing five countries. Below the countries, you will see a pager with four buttons.
If you click on the button "2", you will see the page display another five countries in the database: the second page of records.
Observe more carefully and you will find that the URL in the browser also changes to

```
http://hostname/index.php?r=country%2Findex&page=2
```

Behind the scenes, [[yii\data\Pagination|Pagination]] is providing all of the necessary functionality to paginate a data set:

* Initially, [[yii\data\Pagination|Pagination]] represents the first page, which reflects the country SELECT query
  with the clause `LIMIT 5 OFFSET 0`. As a result, the first five countries will be fetched and displayed.
* The [[yii\widgets\LinkPager|LinkPager]] widget renders the page buttons using the URLs
  created by [[yii\data\Pagination::createUrl()|Pagination]]. The URLs will contain the query parameter `page`, which 
  represents the different page numbers.
* If you click the page button "2", a new request for the route `country/index` will be triggered and handled.
  [[yii\data\Pagination|Pagination]] reads the `page` query parameter from the URL and sets the current page number to 2.
  The new country query will thus have the clause `LIMIT 5 OFFSET 5` and return  the next five countries
  for display.


Summary <span id="summary"></span>
-------

In this section, you learned how to work with a database. You also learned how to fetch and display
data in pages with the help of [[yii\data\Pagination]] and [[yii\widgets\LinkPager]].

In the next section, you will learn how to use the powerful code generation tool, called [Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide),
to help you rapidly implement some commonly required features, such as the Create-Read-Update-Delete (CRUD)
operations for working with the data in a database table. As a matter of fact, the code you have just written can all
be automatically generated in Yii using the Gii tool.
