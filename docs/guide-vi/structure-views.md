Views (Giao diện)
=====

Views là phần trong mô hình [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).
Thành phần này chịu trách nhiệm chính trong việc hiển thị dữ liệu tới người dùng. Tại ứng dụng Web, views thường được tạo
cùng với các *bản mẫu giao diện (view template)* là những file kịch bản của PHP có chứa các mã HTML và mã PHP.
Các file giao diện được quản lý bởi [[yii\web\View|view]] [là thành phần ứng dụng](structure-application-components.md) thành phần này có chứa các phương thức chung
để các giao diện được đóng gói và xuất bản. Để cho đơn giản, chúng ta thường gọi các bản mẫu giao diện hoặc các file bản mẫu giao diện như một giao diện.


## Tạo mới Views <span id="creating-views"></span>

Như đã nêu trên, một view đơn giản là một kịch bản PHP chưa hỗn hợp các mã HTML và PHP. Đoạn mã sau là một view
được thiết lập cho form đăng nhập. Như bạn thấy dưới, các mã PHP dùng trong việc sinh ra các nội dung động, chẳng hạn như tiêu đề
và các nội dung form, còn các mã HTML tổ chức thành các trang nội dung HTML.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Please fill out the following fields to login:</p>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php ActiveForm::end(); ?>
```

Tại mỗi view, bạn có thể truy cập biến `$this` tương ứng với class [[yii\web\View|thành phân giao diện]] là để quản lý và xuất bản các bản mẫu giao diện.

Ngoài biến `$this`, ta cũng có thể khai báo thêm các biến ở trong view, như biến `$model` tại ví dụ trên
. Những biến này có chứa dữ liệu đã được *thêm* vào view bởi [controllers](structure-controllers.md)
hoặc các đối tượng khác được cho vào từ việc [xuất bản view](#rendering-views).

> Mẹo: Các biến được định nghĩa trong ở các dòng comment ở đầu mục của view thường được quản lý bởi các
  IDEs. Định nghĩa như vậy sẽ có các thông tin về tài liệu mô tả cho views.


### Bảo mật dữ liệu (Security) <span id="security"></span>

Khi tạo views được sinh bởi các trang HTML, điều quan trọng là bạn mã hóa và/hoặc lọc dữ liệu đến từ người dùng
cuối trước khi hiển thị lên. Nếu không thì, ứng dụng của bạn có thể bị tấn công bởi
[cross-site scripting](http://en.wikipedia.org/wiki/Cross-site_scripting).

Để hiển thị các nội dung thuần, hãy encode trước bằng việc gọi phương thức [[yii\helpers\Html::encode()]]. Ví dụ, đoạn mã sau sẽ
encodes thông tin user name trước khi được hiển thị:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

Để hiển thị nội dung HTML, sử dụng lớp [[yii\helpers\HtmlPurifier]] để lọc ra các nội dung trước. chẳng hạn, đoạn mã sau sẽ
lọc nội dung bài viết trước khi hiển thị ra:

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

> Mẹo: Khi dùng trích xuất thông tin an toàn từ đối tượng HTMLPurifier là cách tốt, tuy nhiên nó không được nhanh. Bạn nên xem xét sử dụng kỹ thuật
  [caching](caching-overview.md) cho việc lưu nội dung nếu ứng dụng có yêu cầu hiệu suất cao.


### Tổ chức Views <span id="organizing-views"></span>

Cũng giống như [controllers](structure-controllers.md) và [models](structure-models.md), có các quy tắc để quản lý các views.

* Với views được xuất bản từ controller, mặc định các view được đặt trong thư mục `@app/views/ControllerID`,
  với `ControllerID` tương ứng với [controller ID](structure-controllers.md#routes). Chẳng hạn, nếu
  tên class của controller là `PostController`, đường dẫn sẽ là `@app/views/post`; nếu controller là `PostCommentController`,
  thì đường dẫn sẽ là `@app/views/post-comment`. Trong trường hợp controller nằm trong module, thì đường dẫn
  sẽ là `views/ControllerID` và đường dẫn nằm trong [[yii\base\Module::basePath|đường dẫn module]].
* Với views được xuất bản từ các [widget](structure-widgets.md), mặc định chúng được đặt trong đường dẫn `WidgetPath/views`
  , `WidgetPath` là tên đường dẫn có chứa các lớp của widget.
* Với views được xuất bản từ các đối tượng khác, thì bạn nên tuân theo quy ước tương tự như đối với các tiện ích widgets.

Bạn có thể tùy biến giá trị mặc định của đường dẫn bằng việc ghi đè phương thức [[yii\base\ViewContextInterface::getViewPath()]]
của controllers hoặc các widgets.


## Xuất bản View (Rendering )<span id="rendering-views"></span>

View được xuất bản tại [controllers](structure-controllers.md), [widgets](structure-widgets.md), hoặc những nơi khác
khi được gọi phương thức xuất bản tới view. Những phương thức này có cùng nội dung như sau,

```
/**
 * @param string $view view name or file path, depending on the actual rendering method
 * @param array $params the data to be passed to the view
 * @return string rendering result
 */
methodName($view, $params = [])
```


### Xuất bản tại Controllers <span id="rendering-in-controllers"></span>

Trong các [controllers](structure-controllers.md), bạn có thể gọi những phương thức sau trong controller để xuất bản view:

* [[yii\base\Controller::render()|render()]]: xuất bản [tên view](#named-views) và gán vào cùng [layout](#layouts)
  để xuất bản nội dung.
* [[yii\base\Controller::renderPartial()|renderPartial()]]: xuất bản [tên view](#named-views) không gán layout.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: xuất bản [tên view](#named-views) không gán layout,
  vào cho vào tất cả các file kịch bản JS/CSS. Xuất bản này thường được dùng để phản hồi các yêu cầu của AJAX Web.
* [[yii\base\Controller::renderFile()|renderFile()]]: xuất bản một view được mô tả trong các đường dẫn của view hoặc
  [alias](concept-aliases.md).
* [[yii\base\Controller::renderContent()|renderContent()]]: xuất bản chuỗi bằng việc nhúng view vào 
  [layout](#layouts) hiện tại. Phương thức này có từ phiên bản 2.0.1.

Ví dụ,

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        // xuất bản view có tên "view" và gán layout vào nó
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```


### Xuất bản tại các Widget <span id="rendering-in-widgets"></span>

Tại các [widgets](structure-widgets.md), bạn có thể gọi phương thức sau trong widget để xuất bản views.

* [[yii\base\Widget::render()|render()]]: dùng để xuất bản cùng với [tên view](#named-views).
* [[yii\base\Widget::renderFile()|renderFile()]]: xuất bản view được mô tả với đường dẫn file view hoặc một
  [alias (bí danh)](concept-aliases.md).

Ví dụ,

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class ListWidget extends Widget
{
    public $items = [];

    public function run()
    {
        // renders a view named "list"
        return $this->render('list', [
            'items' => $this->items,
        ]);
    }
}
```


### Xuất bản tại Views <span id="rendering-in-views"></span>

Xuất bản view nằm trong view khác bằng việc gọi một phương trong những phương thức sau được cung cấp bởi [[yii\base\View|thành phần view]]:

* [[yii\base\View::render()|render()]]: xuất bản ra [tên view](#named-views).
* [[yii\web\View::renderAjax()|renderAjax()]]: xuất bản ra [tên view](#named-views) đăng ký cùng với những file kịch bản
  JS/CSS. Các file này dùng trong các yêu cầu AJAX Web.
* [[yii\base\View::renderFile()|renderFile()]]: xuất bản view được mô tả với đường dẫn view hoặc một
  [alias](concept-aliases.md).

Ví dụ, đoạn mã sau nằm trong view sẽ xuất bản tên view là `_overview.php` view này nằm cùng đường dẫn như view hiện tại được xuất bản
. Lưu ý rằng biến `$this` nằm trong view đã có trong thành phần của [[yii\base\View|view]] :

```php
<?= $this->render('_overview') ?>
```


### Xuất bản vị trí khác <span id="rendering-in-other-places"></span>

Tại những vị trí khác, bạn có thể truy cập thành phần ứng dụng [[yii\base\View|view]] qua cú pháp
`Yii::$app->view` và gọi nó qua các phương thức đã nói trên để xuất bản view. Ví dụ,

```php
// hiển thị ra file view "@app/views/site/license.php"
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```


### Đặt tên Views <span id="named-views"></span>

Khi xuất bản view, bạn có thể chỉ định sử dụng tên view bao gồm tên view hoặc tên file của view qua đường dẫn/bí danh. Trong các trường hợp,
bạn nên sử dụng các cách trước đây bởi vì nó ngắn gọn và linh hoạt hơn. Chúng ta gọi view được chỉ định sử dụng tên như *tên của views*.

Tên một view được gán vào đường dẫn file tương ứng dựa theo các quy tắc sau:

* Tên một view có thể bỏ qua phần mở rộng file file. Trong trường hợp này, đuôi `.php` sẽ được dùng như một phần mở rộng. Ví dụ,
  tên view là `about` sẽ tương ứng với tên file là `about.php`.
* Nếu tên view được bắt đầu với 2 dấu gạch chéo `//`, đường dẫn view tương ứng sẽ là `@app/views/ViewName`.
  Điều này, tên view sẽ không được phép dưới [[yii\base\Application::viewPath|application's view path]].
  Ví dụ, `//site/about` sẽ được cập nhật thành `@app/views/site/about.php`.
* Nếu tên view được bắt đầu với một dấu gạch chéo `/`, đường dẫn file của view có giá trị tương ứng với tiền tố tên view
  cùng với [[yii\base\Module::viewPath|đường dẫn view]] của [module](structure-modules.md) đang được chọn.
  Nếu không có module được chọn, `@app/views/ViewName` sẽ được dùng. Ví dụ, `/user/create` sẽ có nội dung là
  `@app/modules/user/views/user/create.php`, nếu module được chọn `user`. Nếu không có module chọn,  
  đường dẫn file view sẽ là `@app/views/user/create.php`.
* Nếu view được xuất bản với [[yii\base\View::context|context]] và context được thực thi từ [[yii\base\ViewContextInterface]],
  thì đường dẫn file của view được hình thành bằng tiền tố từ [[yii\base\ViewContextInterface::getViewPath()|đường dẫn view]] của
  context tới tên view. Điều này quan trọng khi xuất bản views trong controllers và widgets. Ví dụ,
  `about` sẽ tương ứng tới đường dẫn `@app/views/site/about.php` nếu context có controller là `SiteController`.
* Nếu view được xuất bản trong các view khác, đường dẫn chứa file view khác sẽ chứa tên view mới
  tới đường dẫn thực tế của đường dẫn view. Ví dụ, `item` sẽ tương ứng tới `@app/views/post/item.php`
  nếu nó được xuất bản từ view `@app/views/post/index.php`.

Dựa theo những quy tắc trên, khi gọi `$this->render('view')` tại controller `app\controllers\PostController` thì thực ra
sẽ gọi tớ file view `@app/views/post/view.php` để xuất bản, trong khi đó việc gọi `$this->render('_overview')` tại view sẽ xuất bản
view file là `@app/views/post/_overview.php`.


### Truy cập dữ liệu tại Views <span id="accessing-data-in-views"></span>

Có 2 cách để truy cập dữ liệu trong view là: đây và kéo.

Bằng cách truyền dữ liệu vào tham số thứ 2 vào phương thức xuất bản của view, bạn đang sử dụng cách đẩy.
Dữ liệu nên được lập như một mảng gồm cặp khóa-giá trị. Mỗi khi view được xuất bản, máy chủ PHP gọi hàm
`extract()` cho mảng này vì vậy mảng được cho vào biến tới view.
Ví dụ, view sau sẽ xuất bản rendering tại controller và sẽ đẩy 2 biến vào view `report` :
`$foo = 1` và `$bar = 2`.

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

Cách kéo dữ liệu sẽ nhận dữ liệu từ [[yii\base\View|view component]] hoặc các đối tượng khác được truy cập
trong views (vd. `Yii::$app`). Đoạn mã sau là ví dụ, trong view bạn có thể lấy dữ liệu từ controller qua
câu lệnh `$this->context`. Và kết quả là, trong view có thể truy cập bất cứ thuộc tính hoặc phương thức
nào nằm trong controller tại view `report`, đoạn mã sau sẽ cho hiển thị định danh của controller:

```php
Định danh của controller là: <?= $this->context->id ?>
```

Cách đẩy thường được dùng hơn khi truy cập dữ liệu trong views, bởi vì nó làm views ít phụ thuộc hơn ở đối tượng
context. Nó có hạn chế hơn khi bạn cần xây dựng dữ liệu thủ công ở các thời điểm, điều này có thể khiển nhẹ và dễ xảy ra lỗi hơn nếu view
được chia sẻ và xuất bản ở những vị trí khác nhau.


### Chia sẽ dữ liệu giữa các Views <span id="sharing-data-among-views"></span>

Thành phần [[yii\base\View]] cung cấp thuộc tính [[yii\base\View::params|params]] cho bạn có thể
chia sẽ dữ liệu giữa các view.

Ví dụ, tại `about`, bạn có thể sử dụng mã code sau để thiết lập giá trị hiện tại của breadcrumbs.

```php
$this->params['breadcrumbs'][] = 'About Us';
```

Sau đó, tại file [layout](#layouts), cũng là một view, bạn có thể hiển thị breadcrumbs sử dụng dữ liệu
qua tham số [[yii\base\View::params|params]]:

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## Layouts (Bố cục) <span id="layouts"></span>

Layouts là một views đặc biệt và chứa những phần chung của các views khác. Ví dụ, những trang
của các ứng dụng Web được chia sẻ cùng header và footer. Thay vì bạn lặp lại nội dunng các trang header và footer
tại mỗi view, thì có cách tốt hơn là bạn chỉ thực hiện một lần ở layout và nhúng nội dung xuất bản của view tại
vị trí thích hợp trong layout.


### Tạo mới Layouts <span id="creating-layouts"></span>

Bởi vì các layout cũng là view, do vậy các layout cũng được tạo như các view khác. Mặc định, layouts
được lưu ở đường dẫn `@app/views/layouts`. Với các layouts dùng trong [module](structure-modules.md),
thì được lưu ở đường dẫn `views/layouts` nằm trong [[yii\base\Module::basePath|đường dẫn module]].
Bạn có thể tùy biến lại đường dẫn mặc định của layout bằng việc cấu hình vào thuộc tính [[yii\base\Module::layoutPath]]
của ứng dụng hoặc module.

Ví dụ sau chỉ cho bạn cách khai báo một layout. Lưu ý rằng đây chỉ là ví dụ đơn giản, chúng ta đã đơn giản hóa các đoạn mã
vào layout. Tuy nhiên trong thực tế, bạn có thể thêm nhiều nội dung vào layout, như các thẻ head, main menu, vv.

```php
<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <header>My Company</header>
    <?= $content ?>
    <footer>&copy; 2014 by My Company</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

Như bạn thấy trên, layout sinh ra các thẻ HTML và được dùng chung ở tất cả các trang. Nằm trong section `<body>`,
layout xuất bản nội dung biến `$content` chứa nội dung của views khi xuất bản
và được đẩy vào layout khi phương thức [[yii\base\Controller::render()]] được gọi.

Hầu hết layouts có thể gọi các phương thức như liệt kê ở dưới sau. Những phương thức này chịu trách nhiệm chính trong liên kết các sự kiện
về quá trình xuất bản để các kịch bản và các thẻ được đăng ký ở những view khác có thể được được hiển thị ra
tại các vị trí mà các phương thức được gọi.

- [[yii\base\View::beginPage()|beginPage()]]: Phương thức được gọi ở mỗi đầu của layout.
  Phương thức này sẽ triggers tới sự kiện [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]] cho biết đến phần đầu của trang.
- [[yii\base\View::endPage()|endPage()]]: Phương thức được gọi ở mỗi cuối của layout.
  Phương thức này sẽ triggers tới sự kiện [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]] cho biết đến phần cuối của trang.
- [[yii\web\View::head()|head()]]: Phương thức được gọi nằm trong session `<head>` của trang HTML.
  Phương thức sinh ra các placeholder sẽ được thay thế bằng các đoạn mã HTML (vd. các thẻ link, meta)
  khi trang kết thúc việc xuất bản.
- [[yii\web\View::beginBody()|beginBody()]]: Phương thức được gọi ở mỗi đầu của section `<body>`.
  Phương thức triggers tới sự kiện [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] và sinh các placeholder sẽ được thay 
  thế bởi các mã HTML đã đăng ký (vd. JavaScript) nhằm vào phần đầu của body.
- [[yii\web\View::endBody()|endBody()]]: Phương thức được gọi ở mỗi cuối section `<body>`.
  Phương thức triggers tới sự kiện [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]] và sinh các placeholder có được thay thế
  bởi các mã HTML đã đăng ký (vd. JavaScript) nhằm vào phần cuối của body.


### Truy cập dữ liệu trong Layouts <span id="accessing-data-in-layouts"></span>

Trong layout, bạn có thể truy cập dữ liệu qua 2 biến đã định nghĩa là: `$this` và `$content`. Thành phần đã được đề
cập là [[yii\base\View|view]], cũng  giống như những view khác, trong khi dữ liệu cuối cùng được xuất bản ra
view dữ liệu này được xuất bản bằng việc gọi phương thức [[yii\base\Controller::render()|render()]] tại controllers.

Nếu bạn muốn truy cập những dữ liệu khác trong layouts, bạn có thể sử dụng phương pháp pull như đã mô tả ở mục
[Accessing Data in Views](#accessing-data-in-views). Nếu bạn chuyển dữ liệu từ content view
vào layout, bạn có thể sử dụng phương thức được mô tả trong phần [Sharing Data among Views](#sharing-data-among-views).


### Sử dụng Layout <span id="using-layouts"></span>

Như mô tả ở mục [Xuất bản tại Controllers](#rendering-in-controllers), khi bạn xuất bản view
bằng việc gọi phương thức [[yii\base\Controller::render()|render()]] tại controller, một layout sẽ được áp dụng
để xuất bản nội dung result. Mặc định, layout `@app/views/layouts/main.php` sẽ được sử dụng trong việc xuất bản nội dung. 

Bạn có thể sử dụng những layout khác bằng việc cấu hình thuộc tính [[yii\base\Application::layout]] hoặc [[yii\base\Controller::layout]].
Thuộc tính trước layout được dùng tại tất cả các controller, trong khi đó thuộc tính sau được ghi đè trong mỗi controller.
Ví dụ, đoạn mã sau thiết lập controller `post` dùng layout `@app/views/layouts/post.php` để
xuất bản nội dung ra views. Còn tại các controller khác, giả sử rằng thuộc tính `layout` không bị thay đổi, thì controller sử dụng layout mặc định là
`@app/views/layouts/main.php` vào việc xuất bản nội dung.
 
```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';
    
    // ...
}
```

Với controller thuộc trong module, bạn có thể thiết lập thuộc tính [[yii\base\Module::layout|layout]] trong module để
thiết lập layout cụ thể cho những controller. 

Because the `layout` property may be configured at different levels (controllers, modules, application),
behind the scene Yii takes two steps to determine what is the actual layout file being used for a particular controller.

In the first step, it determines the layout value and the context module:

- If the [[yii\base\Controller::layout]] property of the controller is not `null`, use it as the layout value and
  the [[yii\base\Controller::module|module]] of the controller as the context module.
- If the [[yii\base\Controller::layout]] property of the controller is `null`, search through all ancestor modules (including the application itself) of the controller and 
  find the first module whose [[yii\base\Module::layout|layout]] property is not `null`. Use that module and
  its [[yii\base\Module::layout|layout]] value as the context module and the chosen layout value.
  If such a module cannot be found, it means no layout will be applied.
  
In the second step, it determines the actual layout file according to the layout value and the context module
determined in the first step. The layout value can be:

- a path alias (e.g. `@app/views/layouts/main`).
- an absolute path (e.g. `/main`): the layout value starts with a slash. The actual layout file will be
  looked for under the application's [[yii\base\Application::layoutPath|layout path]] which defaults to
  `@app/views/layouts`.
- a relative path (e.g. `main`): the actual layout file will be looked for under the context module's
  [[yii\base\Module::layoutPath|layout path]] which defaults to the `views/layouts` directory under the
  [[yii\base\Module::basePath|module directory]].
- the boolean value `false`: no layout will be applied.

If the layout value does not contain a file extension, it will use the default one `.php`.


### Nested Layouts <span id="nested-layouts"></span>

Sometimes you may want to nest one layout in another. Ví dụ, in different sections of a Web site, you
want to use different layouts, while all these layouts share the same basic layout that generates the overall
HTML5 page structure. You can achieve this goal by calling [[yii\base\View::beginContent()|beginContent()]] and
[[yii\base\View::endContent()|endContent()]] in the child layouts like the following:

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...child layout content here...

<?php $this->endContent(); ?>
```

As shown above, the child layout content should be enclosed within [[yii\base\View::beginContent()|beginContent()]] and
[[yii\base\View::endContent()|endContent()]]. The parameter passed to [[yii\base\View::beginContent()|beginContent()]]
specifies what is the parent layout. It can be either a layout file or alias.

Using the above approach, you can nest layouts in more than one levels.


### Using Blocks <span id="using-blocks"></span>

Blocks allow you to specify the view content in one place while displaying it in another. They are often used together
with layouts. Ví dụ, you can define a block in a content view and display it in the layout.

You call [[yii\base\View::beginBlock()|beginBlock()]] and [[yii\base\View::endBlock()|endBlock()]] to define a block.
The block can then be accessed via `$view->blocks[$blockID]`, where `$blockID` stands for a unique ID that you assign
to the block when defining it.

The following example shows how you can use blocks to customize specific parts of a layout in a content view.

First, in a content view, define one or multiple blocks:

```php
...

<?php $this->beginBlock('block1'); ?>

...nội dung của block1...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('block3'); ?>

...nội dung của block3...

<?php $this->endBlock(); ?>
```

Then, in the layout view, render the blocks if they are available, or display some default content if a block is
not defined.

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... default content for block1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... default content for block2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... default content for block3 ...
<?php endif; ?>
...
```


## Sử dụng các thành phần View <span id="using-view-components"></span>

[[yii\base\View|View components]] provides many view-related features. While you can get view components
by creating individual instances of [[yii\base\View]] or its child class, in most cases you will mainly use
the `view` application component. You can configure this component in [application configurations](structure-applications.md#application-configurations)
like the following:

```php
[
    // ...
    'components' => [
        'view' => [
            'class' => 'app\components\View',
        ],
        // ...
    ],
]
```

View components provide the following useful view-related features, each described in more details in a separate section:

* [theming](output-theming.md): allows you to develop and change the theme for your Web site.
* [fragment caching](caching-fragment.md): allows you to cache a fragment within a Web page.
* [client script handling](output-client-scripts.md): supports CSS and JavaScript registration and rendering.
* [asset bundle handling](structure-assets.md): supports registering and rendering of [asset bundles](structure-assets.md).
* [alternative template engines](tutorial-template-engines.md): allows you to use other template engines, such as
  [Twig](http://twig.sensiolabs.org/), [Smarty](http://www.smarty.net/).

You may also frequently use the following minor yet useful features when you are developing Web pages.


### Setting Page Titles <span id="setting-page-titles"></span>

Every Web page should have a title. Normally the title tag is being displayed in a [layout](#layouts). However, in practice
the title is often determined in content views rather than layouts. To solve this problem, [[yii\web\View]] provides
the [[yii\web\View::title|title]] property for you to pass the title information from content views to layouts.

To make use of this feature, in each content view, you can set the page title like the following:

```php
<?php
$this->title = 'My page title';
?>
```

Then in the layout, make sure you have the following code in the `<head>` section:

```php
<title><?= Html::encode($this->title) ?></title>
```


### Registering Meta Tags <span id="registering-meta-tags"></span>

Web pages usually need to generate various meta tags needed by different parties. Like page titles, meta tags
appear in the `<head>` section and are usually generated in layouts.

If you want to specify what meta tags to generate in content views, you can call [[yii\web\View::registerMetaTag()]]
in a content view, like the following:

```php
<?php
$this->registerMetaTag(['name' => 'keywords', 'content' => 'yii, framework, php']);
?>
```

The above code will register a "keywords" meta tag with the view component. The registered meta tag is
rendered after the layout finishes rendering. The following HTML code will be generated and inserted
at the place where you call [[yii\web\View::head()]] in the layout:

```php
<meta name="keywords"
``` content="yii, framework, php">

Note that if you call [[yii\web\View::registerMetaTag()]] multiple times, it will register multiple meta tags,
regardless whether the meta tags are the same or not.

To make sure there is only a single instance of a meta tag type, you can specify a key as a second parameter when calling the method.
Ví dụ, the following code registers two "description" meta tags. However, only the second one will be rendered.

```php
$this->registerMetaTag(['name' => 'description', 'content' => 'This is my cool website made with Yii!'], 'description');
$this->registerMetaTag(['name' => 'description', 'content' => 'This website is about funny raccoons.'], 'description');
```


### Registering Link Tags <span id="registering-link-tags"></span>

Like [meta tags](#registering-meta-tags), link tags are useful in many cases, such as customizing favicon, pointing to
RSS feed or delegating OpenID to another server. You can work with link tags in the similar way as meta tags
by using [[yii\web\View::registerLinkTag()]]. Ví dụ, in a content view, you can register a link tag like follows,

```php
$this->registerLinkTag([
    'title' => 'Live News for Yii',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'http://www.yiiframework.com/rss.xml/',
]);
```

The code above will result in

```html
<link title="Live News for Yii" rel="alternate" type="application/rss+xml" href="http://www.yiiframework.com/rss.xml/">
```

Similar as [[yii\web\View::registerMetaTag()|registerMetaTag()]], you can specify a key when calling
[[yii\web\View::registerLinkTag()|registerLinkTag()]] to avoid generating repeated link tags.


## Sự kiện View <span id="view-events"></span>

[[yii\base\View|View components]] trigger several events during the view rendering process. You may respond
to these events to inject content into views or process the rendering results before they are sent to end users.

- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: triggered at the beginning of rendering a file
  in a controller. Handlers of this event may set [[yii\base\ViewEvent::isValid]] to be `false` to cancel the rendering process.
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: triggered after rendering a file by the call of [[yii\base\View::afterRender()]].
  Handlers of this event may obtain the rendering result through [[yii\base\ViewEvent::output]] and may modify
  this property to change the rendering result.
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: triggered by the call of [[yii\base\View::beginPage()]] in layouts.
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: triggered by the call of [[yii\base\View::endPage()]] in layouts.
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: triggered by the call of [[yii\web\View::beginBody()]] in layouts.
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: triggered by the call of [[yii\web\View::endBody()]] in layouts.

Ví dụ, the following code injects the current date at the end of the page body:

```php
\Yii::$app->view->on(View::EVENT_END_BODY, function () {
    echo date('Y-m-d');
});
```


## Xuất bản các trang tĩnh <span id="rendering-static-pages"></span>

Static pages refer to those Web pages whose main content are mostly static without the need of accessing
dynamic data pushed from controllers.

You can output static pages by putting their code in the view, and then using the code like the following in a controller:

```php
public function actionAbout()
{
    return $this->render('about');
}
```

If a Web site contains many static pages, it would be very tedious repeating the similar code many times.
To solve this problem, you may introduce a [standalone action](structure-controllers.md#standalone-actions)
called [[yii\web\ViewAction]] in a controller. Ví dụ,

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'page' => [
                'class' => 'yii\web\ViewAction',
            ],
        ];
    }
}
```

Now if you create a view named `about` under the directory `@app/views/site/pages`, you will be able to
display this view by the following URL:

```
http://localhost/index.php?r=site%2Fpage&view=about
```

The `GET` parameter `view` tells [[yii\web\ViewAction]] which view is requested. The action will then look
for this view under the directory `@app/views/site/pages`. You may configure [[yii\web\ViewAction::viewPrefix]]
to change the directory for searching these views.


## Bài thực hành <span id="best-practices"></span>

Views are responsible for presenting models in the format that end users desire. In general, views

* should mainly contain presentational code, such as HTML, and simple PHP code to traverse, format and render data.
* should not contain code that performs DB queries. Such code should be done in models.
* should avoid direct access to request data, such as `$_GET`, `$_POST`. This belongs to controllers.
  If request data is needed, they should be pushed into views by controllers.
* may read model properties, but should not modify them.

To make views more manageable, avoid creating views that are too complex or contain too much redundant code.
You may use the following techniques to achieve this goal:

* use [layouts](#layouts) to represent common presentational sections (e.g. page header, footer).
* divide a complicated view into several smaller ones. The smaller views can be rendered and assembled into a bigger
  one using the rendering methods that we have described.
* create and use [widgets](structure-widgets.md) as building blocks of views.
* create and use helper classes to transform and format data in views.

