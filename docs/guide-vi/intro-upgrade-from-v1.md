Nâng cấp lên từ phiên bản 1.1
==========================

Có nhiều sự khác biệt giữa các phiên bản 1.1 và 2.0 của Yii khi cấu trúc framework được viết lại hoàn toàn cho 2.0.
Do vậy, việc nâng cấp từ phiên bản 1.1 không dễ dàng như việc nâng cấp giữa các phiên bản nhỏ. Trong bản hướng dẫn này, bạn sẽ
thấy sựa khác biệt chính giữa hai phiên bản.

Nếu bạn chưa sử dụng bản Yii 1.1, bạn có thể bỏ qua phần này một cách an toàn và chuyển trực tiếp qua mục "[Getting started](start-installation.md)".

Xin lưu ý rằng Yii 2.0 giới thiệu nhiều tính năng mới hơn so với trong phần tóm tắt này. Do vậy chúng tôi khuyến khíc bạn đọc qua toàn bộ bài hướng dẫn 
để tìm hiểu sâu hơn về Yii 2.0. Rất có thể là một số tính năng mà trước đây bạn phải phát triển cho chính mình nhưng bây giờ là một phần của mã cốt lõi.


Cài đặt
------------

Yii 2.0 được tích hợp hoàn toàn với [Composer](https://getcomposer.org/), trình quản lý các gói cho PHP. Việc cài đặt
các gói cốt lõi của framework, cũng như các thư viện, được thực hiện qua Composer. Vui lòng tham khảo mục
[Hướng dẫn cài đặt Yii](start-installation.md) để tìm hiểu việc cài đặt Yii 2.0. Nếu bạn muốn tạo các thư viện
mới, hoặc chuyển các thư viện từ phiên bản 1.1 hiện có của bạn cho tương thích với phiên bản 2.0, thì vui lòng tham khảo
phần [Tạo các thư viện](structure-extensions.md#creating-extensions) của hướng dẫn.


Các yêu cầu về PHP
----------------

Yii 2.0 yêu cầu PHP 5.4 trở lên, đó là một cải tiến rất lớn so với phiên bản PHP 5.2 được Yii 1.1 yêu cầu.
Như được liệt kê dưới, có nhiều sự khác biệt về cấp độ ngôn ngữ mà bạn nên chú ý. 
Dưới đây là tóm tắt về những thay đổi lớn liên quan đến PHP:

- [Namespaces](https://php.net/manual/en/language.namespaces.php).
- [Hàm ẩn danh (Anonymous functions)](https://php.net/manual/en/functions.anonymous.php).
- Cú pháp khai báo mảng ngắn `[...elements...]` được dùng thay cho cú pháp kiểu `array(...elements...)`.
- Thẻ echo ngắn `<?=` được dùng trong các tập tin của view. Điều này là an toàn để sử dụng bắt đầu từ PHP 5.4.
- [SPL classes và interfaces](https://php.net/manual/en/book.spl.php).
- [Late Static Bindings](https://php.net/manual/en/language.oop5.late-static-bindings.php).
- [Ngày và Thời gian](https://php.net/manual/en/book.datetime.php).
- [Traits](https://php.net/manual/en/language.oop5.traits.php).
- [intl](https://php.net/manual/en/book.intl.php). Yii 2.0 sử dụng phần mở rộng PHP intl để hỗ trợ các tính năng quốc tế hóa.


Không gian tên (Namespace)
---------

Thay đổi rõ ràng nhất trong Yii 2.0 là việc sử dụng các không gian tên. Hầu hết mọi lớp lõi đều được đặt không gian tên, ví dụ:
, `yii\web\Request`. Tiền tố "C" không còn được sử dụng trong tên lớp. Sơ đồ đặt tên bây giờ theo cấu trúc thư mục. 
Chẳng hạn, `yii\web\Request` chỉ ra rằng tệp lớp tương ứng là `web/Request.php` trong thư mục khung Yii.

(Bạn có thể sử dụng bất kỳ lớp lõi nào mà không khai báo rõ ràng tệp lớp đó, điều này là nhờ trình tải lớp Yii.)


Các Component và Object
--------------------

Yii 2.0 chia lớp `CComponent` trong 1.1 thành hai lớp: [[yii\base\BaseObject]] và [[yii\base\Component]].
The [[yii\base\BaseObject|BaseObject]] class is a lightweight base class that allows defining [object properties](concept-properties.md)
via getters and setters. Lớp [[yii\base\Component|Component]] được kế thừa từ lớp [[yii\base\BaseObject|BaseObject]] và hỗ trợ thêm các 
[sự kiện (event)](concept-events.md) và các [hành vi (behaviors)](concept-behaviors.md).

Nếu lớp của bạn không cần tính năng sự kiện hoặc hành vi, bạn nên xem xét sử dụng 
[[yii\base\BaseObject|BaseObject]] làm lớp cơ sở. Đây thường là trường hợp cho các lớp đại diện cho cấu trúc dữ liệu cơ bản.


Object Configuration
--------------------

Lớp [[yii\base\BaseObject|BaseObject]] giới thiệu một cách thống nhất để cấu hình các đối tượng. Bất kỳ các lớp con của
lớp [[yii\base\BaseObject|BaseObject]] nên khai báo các hàm khởi tạo (constructor) (nếu cần thiết)  theo cách sau để có thể được
cấu hình đúng:

```php
class MyClass extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... khởi tạo trước khi cấu hình được áp dụng

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... khởi tạo sau khi cấu hình được áp dụng
    }
}
```

Ở trên, tham số cuối cùng của hàm tạo phải lấy một mảng cấu hình có chứa các cặp tên-giá trị để 
khởi tạo các thuộc tính ở cuối hàm tạo. Bạn có thể ghi đè phương thức [[yii\base\BaseObject::init()|init()]]
để thực hiện công việc khởi tạo nên được thực hiện sau khi cấu hình được áp dụng.

Bằng cách tuân theo quy ước này, bạn sẽ có thể tạo và định cấu hình các đối tượng mới
bằng cách sử dụng một mảng cấu hình:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

Thông tin chi tiết về cấu hình có thể được tìm thấy trong phần [Cấu hình](concept-configurations.md).


Sự kiện (Events)
------

Trong Yii 1, các sự kiện đã được tạo bằng cách xác định phương thức `on` (ví dụ, `onBeforeSave`). Trong Yii 2, bây giờ bạn có thể sử dụng bất kỳ tên sự kiện
. Bạn kích hoạt một sự kiện bằng cách gọi phương thức [[yii\base\Component::trigger()|trigger()]]:

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

Để đính kèm một trình xử lý vào một sự kiện, hãy sử dụng phương thức [[yii\base\Component::on()|on()]]:

```php
$component->on($eventName, $handler);
// Để tách trình xử lý, sử dụng:
// $component->off($eventName, $handler);
```

Có nhiều cải tiến cho các tính năng sự kiện. Để biết thêm chi tiết, vui lòng tham khảo phần [Sự kiện](concept-events.md).


Đường dẫn cho bí danh (Aliases)
------------

Yii 2.0 mở rộng việc sử dụng các bí danh đường dẫn cho cả đường dẫn tệp / thư mục và URL. Yii 2.0 hiện cũng yêu cầu một
tên bí danh để bắt đầu bằng ký tự @, để phân biệt các bí danh với các đường dẫn tệp / thư mục thông thường hoặc URL.
Ví dụ, với bí danh `@yii` đề cập đến thư mục cài đặt Yii.Các bí danh đường dẫn được hỗ trợ ở hầu hết các vị trí trong
mã lõi Yii. Ví dụ, [[yii\caching\FileCache::cachePath]] có thể lấy cả bí danh đường dẫn
và đường dẫn thư mục bình thường.

Một bí danh đường dẫn cũng liên quan chặt chẽ đến một không gian tên lớp. Bạn nên xác định một bí danh đường dẫn cho từng không gian tên gốc, do đó
cho phép bạn sử dụng trình tải tự động lớp Yii mà không cần cấu hình thêm. Ví dụ, vì bí danh `@yii` đề cập đến thư mục cài đặt Yii,
một lớp `yii\web\Request` có thể được tải tự động. Nếu bạn sử dụng thư viện của bên thứ ba,
chẳng hạn như thư viện Zend Framework, bạn có thể định nghĩa một bí danh đường dẫn `@Zend` đề cập đến thư mục cài đặt của framework đó
. Khi bạn đã thực hiện điều đó, Yii cũng sẽ có thể tự động tải bất kỳ lớp nào trong thư viện Zend Framework đó.

Thông tin thêm về các đường dẫn bí danh có thể được tìm thấy trong phần [Bí danh](concept-aliases.md).


Giao diện (Views)
-----

Thay đổi đáng kể nhất về views trong Yii 2 là biến `$this` trong view không còn đề cập đến
controller và widget hiện tại. Mà, biến `$this` bây giờ được đề cập tới đối tượng *view*, một khái niệm mới được giới thiệu trong phiên bản 2.0.
Đối tượng *view* là loại [[yii\web\View]], nó đại diện cho phần view
của mô hình MVC. Nếu bạn muốn truy cập tới controller hoặc widget trong view, bạn có thể sử dụng cú pháp là `$this->context`.

Để được xuất bản một phần view trong một view khác, bạn dụng phương thức `$this->render()`, không phải là `$this->renderPartial()`.
Việc gọi `xuất bản` bây giờ phải được lặp lại rõ ràng, như phương thức `render()` sẽ trả về kết quả việc xuất bản, thay vì
trực tiếp hiển thị nó. Ví dụ:

```php
echo $this->render('_item', ['item' => $item]);
```

Bên cạnh việc áp dụng PHP như là ngôn ngữ giao diện chính, Yii 2.0 cũng được trang bị hỗ trợ chính thức cho hai công cụ mẫu giao diện
phổ biến là: Smarty và Twig. Công cụ mẫu của Prado không còn được hỗ trợ.
Để sử dụng các công cụ mẫu này, bạn cần thiết lập cấu hình ứng dụng cho `view` bằng việc thiết lập thuộc tính
[[yii\base\View::$renderers|View::$renderers]]. Vui lòng tham khảo mục [Template Engines](tutorial-template-engines.md)
để biết thêm chi tiết.


Dữ liệu (Models)
------

Yii 2.0 dùng [[yii\base\Model]] làm model cơ sở, tương tự như `CModel` trong 1.1.
Lớp `CFormModel` đã bị loại bỏ hoàn toàn. Thay vào đó, trong Yii 2 bạn nên kế thừa [[yii\base\Model]] để tạo lớp model mẫu.

Yii 2.0 giới thiệu một phương thức mới gọi là [[yii\base\Model::scenarios()|scenarios()]] để khai báo các kịch bản (scenarios) được hỗ trợ
, và để chỉ ra kịch bản nào mà một thuộc tính cần được xác nhận, có thể được coi là an toàn hay không, v.v. Ví dụ:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

Ở trên, hai kịch bản được khai báo là: `backend` và `frontend`. Với kịch bản `backend`, cả 2 thuộc tính
`email` và `role` là thuộc tính an toàn, và có thể được gán dữ liệu an toàn. Còn với kịch bản `frontend`, thuộc tính
`email` có thể được gán dữ liệu an toàn trong khi đó thuộc tính `role` thì không. Cả 2 thuộc tính `email` và `role` nên được xác nhận bằng các quy tắc.

Phức thức [[yii\base\Model::rules()|rules()]] vẫn được sử dụng để khai báo các quy tắc xác thực. Lưu ý rằng như giới thiệu về phương thức [[yii\base\Model::scenarios()|scenarios()]],
thì việc xác nhận đối tượng `unsafe` không còn được hỗ trợ.

Trong hầu hết các trường hợp, bạn không cần ghi đè phương thức [[yii\base\Model::scenarios()|scenarios()]]
nếu trong phương thức [[yii\base\Model::rules()|rules()]] chỉ định đầy đủ các kịch bản sẽ tồn tại, và nếu không cần phải khai báo các thuộc tính
` không an toàn`.

Để tìm hiểu thêm về models, vui lòng tham khảo mục [Models](structure-models.md) để biết thêm chi tiết.


Điều khiển (Controllers)
-----------

Yii 2.0 sử dụng [[yii\web\Controller]] như lớp cơ sở cho controller, tương tự như lớp `CController` trong Yii 1.1.
[[yii\base\Action]] là lớp cơ sở cho các lớp hành động.

Tác động rõ ràng nhất của những thay đổi này đối với mã của bạn là các action của bộ điều khiển sẽ trả về nội dung
mà bạn muốn xuất bản thay vì hiển thị nó:

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Vui lòng tham khảo mục [Controllers](structure-controllers.md) để biết thêm chi tiết về controllers.


Widgets
-------

Yii 2.0 sử dụng [[yii\base\Widget]] như lớp cơ sở cho các widget, tương tự như `CWidget` trong Yii 1.1.

Để được hỗ trợ tốt hơn cho framework trong các IDEs, Yii 2.0 giới thiệu một cú pháp mới để sử dụng widgets. Các phương thức tĩnh
[[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]], và [[yii\base\Widget::widget()|widget()]]
đã được giới thiệu, được sử dụng như vậy:

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Lưu ý rằng bạn phải "echo" kết quả để hiển thị nó
echo Menu::widget(['items' => $items]);

// Truyền một mảng để khởi tạo các thuộc tính đối tượng
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... các form input fields nằm tại đây ...
ActiveForm::end();
```

Vui lòng tham khảo mục [Widgets](structure-widgets.md) để biết thêm thông tin.


Themes
------

Themes hoạt động hoàn toàn khác nhau trong 2.0. Bây giờ các theme dựa trên cơ chế ánh xạ tới đường dẫn có liên kết tới nguồn
đến đường dẫn file cho theme. Ví dụ, nếu đường dẫn liên kết cho theme là `['/web/views' => '/web/themes/basic']`, tiếp đến
phiên bản cho các file view là `/web/views/site/index.php` sẽ là `/web/themes/basic/site/index.php`. Vì lý do này, các themes 
bây giờ có thể được áp dụng vào bất cứ file view, thậm chí một sự kiện được xuất bản bên ngoài của nội dung một controller hoặc một widget.

Ngoài ra, không còn component`CThemeManager`. Thay vào đó, `theme` bây giờ được cấu hình qua thuộc tính của thành phần `view`.

Vui lòng tham khảo mục [Theming](output-theming.md) để biết thêm thông tin chi tiết.


Ứng dụng Console
--------------------

Các ứng dụng Console hiện được tổ chức dưới dạng như controllers, như các ứng dụng Web. Các controller của ứng dụng Console
nên được kế thừa từ [[yii\console\Controller]], tương tự như `CConsoleCommand` trong bản 1.1.

Để chạy lệnh console, nhập `yii <route>`, với `<route>` là viết tắt cho controller route
(v.d. `sitemap/index`). Các tham số ẩn danh được truyền qua như những tham số tương ứng
trong phương thức hành động của controller, trong khi các đối số được đặt tên được phân tích cú pháp 
theo các khai báo trong [[yii\console\Controller::options()]].

Yii 2.0 hỗ trợ tự động xuất nội dung của các lệnh trợ giúp từ các khối bình luận.

Vui lòng tham khảo mục [Console Commands](tutorial-console.md) để biết thêm thông tin chi tiết.


I18N
----

Yii 2.0 loại bỏ các phần định dạng ngày và phần định dạng số tích hợp có lợi cho [PECL intl PHP module](https://pecl.php.net/package/intl).

Dịch văn bản bây giờ được thực hiện qua thành phần ứng dụng `i18n`.
Thành phần này quản lý một tập hợp các nguồn văn bản, cho phép bạn sử dụng các nguồn văn bản khác nhau dựa trên các loại văn bản.

Vui lòng tham khảo mục [Internationalization](tutorial-i18n.md) để biết thêm thông tin chi tiết.


Bộ lọc Action
--------------

Bộ lọc Action bây giờ được thực hiện thông qua các hành vi (behaviors. Để xác định một cái mới, tùy biến filter, kế thừa tư [[yii\base\ActionFilter]].
Để dùng các filter, gắn các lớp filter vào controller
như các behavior. Ví dụ, để sử dụng filter [[yii\filters\AccessControl]], bạn sẽ có đoạn mã sau trong controller:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

Vui lòng tham khảo mục [Filtering](structure-filters.md) để biết thêm thông tin chi tiết.


Assets
------

Yii 2.0 giới thiệu một khái niệm mới gọi là *asset bundle* thay thế khái niệm gói script được tìm thấy trong Yii 1.1.

Một asset bundle là bộ tổng hớp các file asset (v.d. JavaScript files, CSS files, image files, vv.)
trong một thư mục. Mỗi asset bundle được đại diện như một class kế thừa từ [[yii\web\AssetBundle]].
Bằng việc đăng ký một asset bundle qua [[yii\web\AssetBundle::register()]], bạn cho ra các
assets nằm trong bundle có thể được truy cập từ Web. Không giống như trong Yii 1, các trang đăng ký các bundle sẽ tự động
có chứa các tham chiếu đến các file JavaScript và CSS files được quy định trong bundle đó.

Vui lòng tham khảo mục [Managing Assets](structure-assets.md) để biết thêm thông tin chi tiết.


Helpers
-------

Yii 2.0 giới thiệu thêm nhiều các lớp helper thường được sử dụng, bao gồm.

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]

Vui lòng tham khảo mục [Helper Overview](helper-overview.md) để biết thêm thông tin chi tiết.

Forms
-----

Yii 2.0 giới thiệu khái niệm *field* cho việc xây dựng form sử dụng [[yii\widgets\ActiveForm]]. Một field
bao gồm chứa một label, một input, một văn bản báo lỗi (error message), và/hoặc một trợ giúp văn bản (hint text).
Một field được đại diện như một đối tượng [[yii\widgets\ActiveField|ActiveField]].
Để sử dụng các field, bạn có thể xây dựng một form gọn gàng hơn trước đây:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Vui lòng tham khảo mục [Creating Forms](input-forms.md) để biết thêm thông tin chi tiết.


Xây dựng các truy vấn (Query Builder)
-------------

Trong 1.1, xây dựng truy vấn nằm rải rác trong một số lớp, bao gồm lớp `CDbCommand`,
`CDbCriteria`, và `CDbCommandBuilder`. Yii 2.0 đại diện cho các truy vấn CSDL nằm trong các khoản của đối tượng [[yii\db\Query|Query]]
có thể chuyển qua các lệnh SQL với sự giúp đỡ của đối tượng [[yii\db\QueryBuilder|QueryBuilder]] đàng sau.
Ví dụ:

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

Cách tốt nhất, việc xây dựng các truy vấn có thể được dùng khi làm việc với [Active Record](db-active-record.md).

Vui lòng tham khảo mục [Query Builder](db-query-builder.md) để biết thêm thông tin chi tiết.


Active Record
-------------

Yii 2.0 giới thiệu rất nhiều thay đổi về [Active Record](db-active-record.md). The two most obvious ones involve
query building và relational query handling.

Lớp `CDbCriteria` trong bản 1.1 được thay thế bởi lớp [[yii\db\ActiveQuery]] trong Yii 2. Lớp này được kế thừa tư [[yii\db\Query]], và đồng thời
kế thừa tất cả các phương thức xây dựng câu truy vấn. Bạn có thể thử gọi phương thức [[yii\db\ActiveRecord::find()]] để xây dựng câu truy vấn:

```php
// để nhận tất cả các danh sách khách hàng có điều kiện được *active* và sắp xếp theo thứ tự ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

Để khai bao một quan hệ (relation), cách đơn giản là khai báo một phương thức getter có trả về đối tượng [[yii\db\ActiveQuery|ActiveQuery]].
Tên thuộc tính được xác định bởi getter đại diện cho tên quan hệ. Ví dụ, đoạn code sau khai báo một quan hệ
là `orders` (trong 1.1, bạn sẽ phải khai báo quan hệ ở vị trí nằm giữa phương thức `relations()`):

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

Bây giờ bạn dùng `$customer->orders` để truy cập các orders trong khách hàng từ bảng quan hệ. Bạn có thể dùng đoạn mã sau
để thực hiện truy vấn quan hệ nhanh chóng với điều kiện truy vấn tùy chỉnh::

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

When eager loading a relation, Yii 2.0 does it differently from 1.1. In particular, in 1.1 a JOIN query
would be created to select both the primary and the relational records. In Yii 2.0, two SQL statements are executed
without using JOIN: the first statement brings back the primary records and the second brings back the relational
records by filtering with the primary keys of the primary records.

Instead of returning [[yii\db\ActiveRecord|ActiveRecord]] objects, you may chain the [[yii\db\ActiveQuery::asArray()|asArray()]]
method when building a query to return a large number of records. This will cause the query result to be returned
as arrays, which can significantly reduce the needed CPU time and memory if large number of records . Ví dụ:

```php
$customers = Customer::find()->asArray()->all();
```

Một thay đổi khác là bạn không thể khai báo các giá trị mặc định thuộc tính thông qua các thuộc tính công khai nữa.
Nếu bạn cần những thứ đó, bạn nên đặt chúng trong phương thức init của lớp bản ghi của bạn.

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NEW;
}
```

Có một số vấn đề với việc ghi đè hàm tạo của lớp ActiveRecord trong 1.1. Các vấn đề này không còn xuất hiện trong bản
2.0 nữa. Lưu ý rằng khi thêm tham số vào hàm tạo, bạn có thể phải ghi đè [[yii\db\ActiveRecord::instantiate()]].

Có nhiều thay đổi và cải tiến khác đối với Active Record. Vui lòng tham khảo mục
[Active Record](db-active-record.md) để biết thêm thông tin chi tiết.


Các hành vi trong Active Record
-----------------------

Trong 2.0, chúng tôi đã lược bỏ lớp lớp base của hành vi `CActiveRecordBehavior`. Nếu bạn muốn tạo một hành vi cho Active Record,
bạn cần được kế thừa trực tiếp từ lớp `yii\base\Behavior`. Nếu trong lớp hành vi cần được phản hồi một số sự kiện của lớp cha, bạn
cần ghi đề phương thức `events()` như sau:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```


User và IdentityInterface
--------------------------

Lớp `CWebUser` trong 1.1 bây giời được thay thế bởi [[yii\web\User]], và không còn hỗ trợ lớp
`CUserIdentity` nữa. Thay vì đó, bạn nên hiện thực lớp [[yii\web\IdentityInterface]] để được sử dụng được đơn giản hơn.
Trong mẫu dự án advanced có tích hợp một vài ví dụ như vậy .

Vui lòng tham khảo mục [Authentication](security-authentication.md), [Authorization](security-authorization.md), và [Mẫu dự án Advanced](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide) để biết thêm thông tin chi tiết.


Quản lý các URL
--------------

Quản lý các URL trong Yii 2 tương tự như trong 1.1. Một cải tiến lớn cho việc quản lý các URL bây giờ hỗ trợ tùy chọn các
tham số. Ví dụ, nếu bạn có một quy tắc (rule) được khai báo như sau, tiếp sau đó quy tắc này phù hợp với cả hai
`post/popular` và `post/1/popular`. Trong 1.1, bạn sẽ phải sử dụng hai quy tắc để đạt được cùng một mục tiêu.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Vui lòng tham khảo mục [Url manager docs](runtime-routing.md) để biết thêm thông tin chi tiết.

Một thay đổi quan trọng trong quy ước đặt tên cho các routes và tên camel case của controllers
và actions hiện được chuyển đổi thành ký tự thấp mỗi từ được phân tách bằng một gạch ngang, bd. định danh cho controller
`CamelCaseController` sẽ là `camel-case`.
Xem thêm ở mục [định danh controller](structure-controllers.md#controller-ids) và [định danh action](structure-controllers.md#action-ids) để biết thêm thông tin.


Dùng Yii 1.1 và 2.x cùng với nhau
------------------------------

Nếu bạn có các đoạn mã Yii 1.1 trước đây mà bạn muốn sử dụng cùng với Yii 2.0, vui lòng tham khảo mục
[Using Yii 1.1 and 2.0 Together](tutorial-yii-integration.md#using-both-yii2-yii1).

