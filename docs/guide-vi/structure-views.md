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

Bởi vì thuộc tính `layout` có thể đã thiết lập ở những vị trí khác như (controllers, modules, application),
phía nền Yii cũng thực hiện 2 bước để xác định rằnglayout file sẽ được dùng vào controller cụ thể.

Tại bước đầu tiên, Yii xác định giá trị layout và context module:

- Nếu thuộc tính [[yii\base\Controller::layout]] tại controller có giá trị khác `null`, sẽ dùng nó như giá trị layout và
  thuộc tính [[yii\base\Controller::module|module]] của controller như là một context module.
- Nếu thuộc tính [[yii\base\Controller::layout]] của controller có giá trị là `null`, tìm kiếm tất cả trong các modules liên quan (bao gồm bên trong ứng dụng) của controller và 
  tìm kiếm module đầu tiên mà có thuộc tính [[yii\base\Module::layout|layout]] có giá trị khác `null`. Sử dụng nó như module và
  giá trị [[yii\base\Module::layout|layout]] như một context module và chọn lấy giá trị layout.
  Nếu một trong những module không được tìm thấy, đồng nghĩa với việc không có layout nào được chọn.
  
Tại bước tiếp theo, nó xác định file layout được dùng dựa vào giá trị layout và context module
được xác định ở bước. Giá trị layout có thể là:

- một đường dẫn bí danh (vd. `@app/views/layouts/main`).
- một đường dẫn tuyệt đối (vd. `/main`): giá trị layout bắt đầu với dấu gách chéo. thì file layout thực tế sẽ được tìm
  dưới đường dẫn của ứng dụng [[yii\base\Application::layoutPath|layout path]] có mặc định là
  `@app/views/layouts`.
- một đường dẫn tương đối (vd. `main`): thì file layout được tìm dưới các context module's
  [[yii\base\Module::layoutPath|layout path]] có mặc định ở đường dẫn `views/layouts` dưới đường dẫn module
  [[yii\base\Module::basePath|module directory]].
- giá trị là `false`: không có layout nào được áp dụng.

Nếu tên layout không có chứa thông tin phần mở rộng tệp, thì Yii sử dụng đuôi mở rộng là `.php`.


### Layout lồng nhau <span id="nested-layouts"></span>

Đôi lúc bạn muốn nhúng layout vào layout khác. Ví dụ, tại những mục khác nhau của Web site, bạn muốn
sử dụng những layout khác, trong khi tất cả các layouts chia sẽ với layout chung được sinh bởi hầu hết các trang
HTML5. Bạn có thể kết hợp các layout bằng việc gọi phương thức [[yii\base\View::beginContent()|beginContent()]] và
[[yii\base\View::endContent()|endContent()]] tại những layout con như sau:

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...nội dung layout con nằm trong đây...

<?php $this->endContent(); ?>
```

Như mô tả trên, nội dung layout nên được nằm trong phương thức [[yii\base\View::beginContent()|beginContent()]] và phương thức
[[yii\base\View::endContent()|endContent()]]. Các tham số được gán vào phương thức [[yii\base\View::beginContent()|beginContent()]]
có chỉ định các thông tin của layout cha. Nó có thể dùng cả các file layout hoặc alias.

Việc sử dụng phương pháp trên, bạn có thể lồng các layout theo nhiều cấp độ.


### Sử dụng các khối(Block) <span id="using-blocks"></span>

Các khối cho phép bạn xác định các nội dung ở view tại một vị trí khi muốn hiển thị khối đó tại nơi khác trong view. Chúng thường được dùng với
các layout. Ví dụ, bạn có thể định nghĩa các khối ở nội dung view và hiển thị nó vào layout.

Khi gọi các phương thức [[yii\base\View::beginBlock()|beginBlock()]] và [[yii\base\View::endBlock()|endBlock()]] để định nghĩa các khối.
Khối được truy cập qua phương thức `$view->blocks[$blockID]`, với `$blockID` là một định danh ID duy nhất mà bạn gán
vào khối khi định nghĩa khối đó.

Ví dụ sau sẽ chỉ cho bạn các để sử dụng các khối để tùy biến các phần của layout tại nội dung view.

Đầu tiên, tại trang nội dung của view, ta định nghĩa một hoặc nhiều khối:

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

Tiếp đến, tại giao diện layout, sẽ xuất bản các khối nếu khối này có nội dung, còn không sẽ hiển thị các nội dung mặc định của khối nếu
các khối không được định nghĩa.

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... nội dung mặc định cho khối block1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... nội dung mặc định cho khối block2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... nội dung mặc định cho khối block3 ...
<?php endif; ?>
...
```


## Sử dụng các thành phần View <span id="using-view-components"></span>

[[yii\base\View|View components]] cung cấp nhiều tính năng cho  phần giao diện. While you can get view components
by creating individual instances of [[yii\base\View]] or its child class, in most cases you will mainly use
the `view` application component. Bạn có thể cấu hình các component trong mục [application configurations](structure-applications.md#application-configurations)
như sau:

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

Các thành phần View cung cấp các tính năng hữu ích được liệt kê dưới, mỗi mô tả có trong các trang chi tiết:

* [theming](output-theming.md): cho phép bạn xây dựng và thay đổi theme cho các trang Web.
* [fragment caching](caching-fragment.md): cho phép bạn xử lý cache các fragment trong các trang Web.
* [client script handling](output-client-scripts.md): hỗ trợ đăng ký vào xuất bản các nội dung về CSS và JavaScript.
* [asset bundle handling](structure-assets.md): hỗ trợ việc đăng ký và xuất bản các [asset bundles](structure-assets.md).
* [alternative template engines](tutorial-template-engines.md): cho phép bạn sử dụng các bộ giao diện, chẳng hạn như
  [Twig](http://twig.sensiolabs.org/), [Smarty](http://www.smarty.net/).

You may also frequently use the following minor yet useful features when you are developing Web pages.


### Thiết lập tiêu đề trang <span id="setting-page-titles"></span>

Tại mỗi trang Web cần có các tiêu đề. Thông thường các thẻ tiêu đề được hiển hị trong các [layout](#layouts). Tuy nhiên, trong bài thực hành này
các tiêu đề thường được xác định tại trang nội dung của view hơn là xác định tại các layout. Để làm được việc này, lớp [[yii\web\View]] cung cấp
thuộc tính [[yii\web\View::title|title]] cho bạn đẩy thông tin tiêu đề từ nội dung view qua các layout.

Để thực hiện tính năng này, tại mỗi trang nội dung của view, bạn có thể thiết lập tiêu đề trang như sau:

```php
<?php
$this->title = 'My page title';
?>
```

Tiếp đến tại layout, hãy chắc chắn rằng bạn đặt đoạn mã sau vào mục `<head>`:

```php
<title><?= Html::encode($this->title) ?></title>
```


### Thực hiện đăng ký các thẻ Meta Tags <span id="registering-meta-tags"></span>

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

Lưu ý rằng nếu bạn gọi phương thức [[yii\web\View::registerMetaTag()]] multiple times, it will register multiple meta tags,
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

Các View chịu trách nhiệm trong việc hiển thị dữ liệu từ model tới người dùng. Trong các trường hợp, view thường

* nên chứa các mã code hiển thị, như HTML, và các câu lệnh PHP đơn giản để xử lý, định dạng và xuất bản dữ liệu.
* không nên chứa các mã code có chứa các câu lệnh truy vấn vào CSDL. Những câu lệnh này nên đặt trong các model.
* nên tranh các câu lệnh điều hướng yêu cầu dữ liệu, như `$_GET`, `$_POST`. Các lệnh này nên xử lý ở các controller.
  Nếu cấn lấy dữ liệu, chúng nên được đẩy vào view qua controller.
* nên có đọc các thuộc tính của model, nhưng không được sửa nội dung trong đó.

Để việc quản lý các view dễ dàng hơn, nên tránh việc tạo các view quá phức tạp hoặc chứa nhiều các mã code dự phòng.
Bạn có thể tham khảo các thủ thuật sau để đạt việc quản lý view tốt:

* use [layouts](#layouts) to represent common presentational sections (e.g. page header, footer).
* divide a complicated view into several smaller ones. The smaller views can be rendered and assembled into a bigger
  one using the rendering methods that we have described.
* create and use [widgets](structure-widgets.md) as building blocks of views.
* create and use helper classes to transform and format data in views.

