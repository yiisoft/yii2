Làm việc với Forms
==================

Ở phần này sẽ hướng dẫn làm thế nào để tạo mới trang Web cho phép ứng dụng lấy các thông tin về user từ form.
Trang này sẽ có chức năng hiển thị form cho user cùng với các input như name (tên người dùng) và email.
Sau khi nhận hai thông tin về user, trang web sẽ hiển thị thông tin tới user.

Để làm được trang web này, bên cạnh tạo ra [action](structure-controllers.md) và
hai giao diện [views](structure-views.md), bạn cần phải tạo ra đối tượng [model](structure-models.md) để xử lý các nghiệp vụ logic truy xuất CSDL.

Trong phần này, bạn sẽ được tìm hiểu về:

* Tạo đối tượng [model](structure-models.md) nhận thông tin từ user được nhập từ form
* Khai báo rules để xách minh dữ liệu nhập vào
* Xây dựng form HTML ở [view](structure-views.md)


Tạo Model <span id="creating-model"></span>
----------------

Dữ liệu của user cần xử lý sẽ đại diện bởi lớp model `EntryForm` sau đây và
được lưu ở file `models/EntryForm.php`. Tham khảo thêm về phần [Class Autoloading](concept-autoloading.md)
để biết thêm chi tiết về quy tắc đặt tên cho các lớp.

```php
<?php

namespace app\models;

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

Lớp trên được kế thừa từ lớp [[yii\base\Model]], lớp này được Yii cung cấp , thường được dùng cho việc xử lý dữ liệu từ form.

> Lưu ý: [[yii\base\Model]] là lớp cơ sở cho việc tương tác với các lớp dữ liệu và nó *không* liên quan tới các bảng trong CSDL.
[[yii\db\ActiveRecord]] là lớp thường được dùng với CSDL mỗi lớp này sẽ tương xứng với các bảng trong CSDL.

Lớp `EntryForm` chứa hai biến ở phạm vi toàn cục (public), `name` và `email`, Các biến này sẽ được dùng để lưu trữ dữ liệu
khi người dùng nhập và gửi lên. Lớp này đồng thời chứa phương thức là `rules()`, phương thức này trả về tập quy tắc để xác thực
dữ liệu. Các quy tắc chứng thực được khai báo ở phần trên với ý nghĩa rằng.

* cả hai giá trị `name` và `email` cần phải có
* giá trị `email` phải đúng cú pháp là địa chỉ email

Nếu đã có đối tượng `EntryForm` cùng với dữ liệu user đã nhập, bạn có thể sử dụng phương thức
[[yii\base\Model::validate()|validate()]] để xác thực dữ liệu mỗi khi user gửi lên. Việc xác thực dữ liệu sai sẽ
thiết lập thuộc tính [[yii\base\Model::hasErrors|hasErrors]] thành "true", và bạn có thể xem thông tin về việc xác thực lỗi
từ phương thức [[yii\base\Model::getErrors|errors]].

```php
<?php
$model = new EntryForm();
$model->name = 'Qiang';
$model->email = 'bad';
if ($model->validate()) {
    // Xác thực thành công!
} else {
    // Xác thực lỗi!
    // Use $model->getErrors()
}
```


Tạo Action <span id="creating-action"></span>
------------------

Tiếp theo, trong controller `site` bạn sẽ tạo action là `entry` action này cần dùng tới model. Quy trình và cách tạo mới action
đã được hướng dẫn ở mục [Saying Hello](start-hello.md).

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

Action này sẽ tạo đối tượng `EntryForm`. Sau khi được khởi tạo, nó sẽ lấy các thông tin thông qua biến
 `$_POST`, biến này được Yii cung cấp [[yii\web\Request::post()]].
Nếu dữ liệu gửi đến cho model thành công(chẳng hạn., khi user gửi thông tin từ HTML form), action sẽ gọi phương thức
[[yii\base\Model::validate()|validate()]] để chắc chắn rằng những giá trị được nhập vào là hợp lý.

> Thông tin thêm: Thành phần `Yii::$app` được mô tả ở mục [application](structure-applications.md),
  Thành phần này là một mẫu thiết kế singleton cho phép truy cập ở toàn cục. Được hoạt động như một [service locator](concept-service-locator.md) that
  để cung cấp các thành phần như `request`, `response`, `db`, vv. nhằm để hỗ trợ thêm các chức năng đặc biệt.
  Ở đoạn code trên, component `request`  được khởi tạo bỏi ứng dụng dùng để truy cập dữ liệu từ `$_POST`.

Nếu không có lỗi gì, action sẽ trả về (render) view tên là `entry-confirm` để xác nhận dữ liệu được gửi lên.
. Nếu dữ liệu trống hoặc gặp lỗi, dữ liệu sẽ được gửi về view `entry`, chứa form HTML, cùng với các thông điệp ở việc xác thực bị lỗi.

> Lưu ý: ở bài hướng dẫn này, chúng ta chỉ xác nhận trang khi có dũ liệu hợp lệ. Bài thực hành này,
  bạn cần lưu ý việc sử dụng các phương thức [[yii\web\Controller::refresh()|refresh()]] hoặc [[yii\web\Controller::redirect()|redirect()]]
  nhằm để tránh [form resubmission problems](https://en.wikipedia.org/wiki/Post/Redirect/Get).


Tạo Views <span id="creating-views"></span>
--------------

Cuối cùng, chúng ta tạo mới 2 tập tin view có tên là `entry-confirm` và `entry`. Những view này sẽ được trả về như được mô tả ở trên từ action `entry`.

View `entry-confirm` đơn giản chỉ hiển thị dữ liệu cho 2 thuộc tính name và email . View này được lưu trữ ở tập tin `views/site/entry-confirm.php`.

```php
<?php
use yii\helpers\Html;
?>
<p>Bạn đã nhập với những thông tin như sau:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->email) ?></li>
</ul>
```

View `entry` sẽ hiển thị một form chứa các mã HTML.  View này được lưu trữ ở tập tin `views/site/entry.php`.

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

Những [widget](structure-widgets.md) gọi là [[yii\widgets\ActiveForm|ActiveForm]] to
thường được dùng để xây dựng các Form. Các phương thức `begin()` và `end()` dùng để mở và đóng các tag tương ứng
. Giữa hai phương thức này, phương thức [[yii\widgets\ActiveForm::field()|field()]] sẽ tạo mới các input của form . Input đầu tiên sẽ dùng cho trường dữ liệu "name",
và input thức hai sẽ dược dùng cho trường "email". Sau cùng của các input, phương thức [[yii\helpers\Html::submitButton()]] 
sẽ được gọi và tạo ra nút submit dùng để gửi dữ liệu.


Thử xem kết quả <span id="trying-it-out"></span>
-------------

Truy cập vào URL sau để xem kết quả:

```
https://hostname/index.php?r=site/entry
```

Bạn sẽ thấy trang Web cùng với việc hiển thị form chứa 2 trường để nhập dữ liệu . Trước mỗi trường nhập liệu, có nhãn được chỉ định những dữ liệu nhập vào . 
Nếu bạn không nhập dữ liệu gì vào và nhấn nút submit, hoặc nếu bạn cung cấp địa chỉ email sai, bạn sẽ thấy thông điệp thông báo lỗi ở mỗi trường nhập liệu.

![Form with Validation Errors](images/start-form-validation.png)

Sau khi nhập đúng các trường name và địa chỉ email đồng thời click vào nút submit, bạn sẽ thấy trang web mới
cùng với dữ liệu bạn vừa nhập .

![Confirmation of Data Entry](images/start-entry-confirmation.png)



### Thông tin thêm <span id="magic-explained"></span>

Chúng ta sẽ thắc mắc rằng làm sao các form HTMl được xây dựng lên, bởi vì nó dường như là những thủ thuật có thể
hiển thị các nhãn (label) cho từng trường input và hiển thị thông báo lỗi nếu bạn không nhập dữ liệu chính xác mà không cần
tải lại trang.

Đúng vậy, việc xác nhận dữ liệu được thực hiện ở máy client sử dụng JavaScript, và tiếp đế được thực hiện ở máy chủ PHP.
Đối tượng [[yii\widgets\ActiveForm]] rất hữu dụng cho việc xác nhận những quy tắc (rules) mà bạn đã khai báo ở model `EntryForm`,
và biến chúng thành những đoạn mã javaScript thực thi, và sử dụng javaScript để xác thực. Trường hợp bạn đã vô hiệu hóa
javaScript trên trình duyệt, việc xác thực sẽ thực hiện ở phía server, nằm ở phương thức
`actionEntry()`. Điều này đảm bảo tính hợp lệ dữ liệu trong mọi trường hợp.

> Cảnh báo: Việc xác thực ở phía client thường cung cấp cho sự trải nghiệm của người dùng tốt hơn. Xác thực phía server
  thì luôn luôn được thực thi, có thể có hoặc không việc xác thực ở phía client.

Các nhãn (label) cho các input được tạo ra bởi phương thức `field()`, sử dụng tên của thuộc tính nằm trong model.
Chẳng hạn, tên nhãn `Name` sẽ được tạo bởi thuộc tính `name`. 

Bạn có thể sửa tên nhãn ở đoạn code sau:

```php
<?= $form->field($model, 'name')->label('Tên của bạn Name') ?>
<?= $form->field($model, 'email')->label('Địa chỉ Email') ?>
```n

> Thông tin thêm: Yii giúp bạn xây dụng nhanh chóng đối với các view phức tạp bằng việc cung cấp các widget.
  Bạn sẽ được học ở phần sau, cách đơn giản nhất để viết một widget. Bạn nên chuyển những code ở view của bạn
  sang dạng widget để đơn giản hơn sự phát triển ứng dụng và tái sử dụng nó.


Tóm lược <span id="summary"></span>
-------

Trong phần hướng dẫn này, bạn đã làm việc với tất cả các thành phần trong mô hình MVC. Bạn đã học cách tạo mới model và xác thực dữ liệu.

Bạn đã tìm hiểu cách lấy dữ liệu từ user và hiển thị dữ liệu ra trình duyệt. Chức năng này có thể dành nhiều thời gian khi
xây dựng ứng dụng, tuy nhiên Yii hỗ trợ các chức năng thật đơn giản bằng việc cung cấp những widget.

Trong phần tiếp theo, bạn sẽ tìm hiều làm thể nào để làm việc với CSDL, điều cần thiết với những ứng dụng.
