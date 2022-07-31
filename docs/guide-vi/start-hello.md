Bắt đầu ứng dụng với lời chào Hello
============

Phần này sẽ mô tả làm thế nào để tạo ra một trang Web mới trong ứng dụng của bạn cùng với lời chào "Hello".
Để đạt được mục tiêu này. bạn sẽ cần tạo mới một [action](structure-controllers.md#creating-actions) và
một [view](structure-views.md):

* Ứng dụng sẽ gửi đi các request từ trang Web để tới các action
* và action sẽ tạo mới View để hiển thị lời chào "Hello" tới user.

Thông qua bài hướng dẫn này, bạn sẽ nắm vững ba điều:

1. Làm thế nào để tạo ra một [action](structure-controllers.md) để đáp ứng các requests,
2. Làm thế nào để tạo ra [view](structure-views.md) để xây dựng nội dung các thông điệp, và
3. Cách ứng dụng gửi đi các request tới các [actions](structure-controllers.md#creating-actions).


Tạo Action <span id="creating-action"></span>
------------------

Với nhiệm vụ tạo ra thông điệp "Hello", bạn sẽ tạo một  [action](structure-controllers.md#creating-actions) `say`, action này 
sẽ lấy các tham số `message` từ request và hiển thị thông điệp trở lại user. Nếu request không cung cấp tham số `message`, 
action sẽ mặc định hiển thị thông điệp  "Hello".

> Info: [Hành động (Actions)](structure-controllers.md#creating-actions) là người dùng cuối có thể truy cập các đối tượng và thực hiện trực tiếp.
 Các Actions được nằm trong [bộ điều khiển (controllers)](structure-controllers.md).
 Các kết quả của một action là người sử dụng cuối cùng nhận được các thông điệp.

Các Actions cần phải được khai báo ở [controllers](structure-controllers.md). Để cho đơn giản, bạn có thể khai báo
action  `say` ở controller `SiteController`. Controller này được khai báo ở trong 
lớp `controllers/SiteController.php`. Action mới cần tạo nằm ở đoạn code sau:

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

Trong đoạn code trên,  action `say` đinh nghĩa phương thức có tên là `actionSay` nằm trong lớp `SiteController`.
Yii sử dụng tiền tố `action` để phân biệt các phương thức thuộc action từ các phương thức không phải là action trong một lớp điều khiển.
Tên nằm sau `action` là tiền tố ánh xạ tới các action's ID.

Để hiểu được quy tắc đặt tên cho actions, Bạn nên hiểu cách hoạt động Yii xử lý với các action IDs. Mỗi Action IDs luôn luôn là những ký tự 
thường. Nếu action ID đòi hỏi nhiều từ, chúng ta sẽ nối những từ đó bằng dấu gạch ngang (ví dụ, `create-comment`). Tên phương thức của action 
sẽ được ánh xa tới action IDs bởi loại bỏ bất kỳ dấu gạch ngang từ IDs, dấu gạch ngang được thêm vào từ chữ cái in hoa đầu tiên trong mỗi từ, và từ đứng trước `action`. Ví dụ,
với action ID `create-comment` tương ứng tới action có phương thức tên là `actionCreateComment`.

Trong ví dụ này, phương thức của action nhận tham số `$message`, mặc đinh giá trị là `"Hello"` (Như việc bạn có thể thiết
lập các giá trị mặc định cho bất kỳ tham số cho các hàm hoặc phương thức trong PHP). Mỗi khi ứng dụng
nhận request và xác đinh là action chịu trách nhiệm cho xử lý các yêu cầu là action `say` , ứng dung
sẽ lưu trữ tham số này cùng với tên tham số được tìm thấy trong request. Nói cách khác, nếu request bao gồm
tham số `message` theo cùng với giá trị `"Goodbye"`, biến `$message` tương ứng trong action sẽ được gán giá trị.

Phương thức [[yii\web\Controller::render()|render()]] nằm trong mỗi action được gọi để trả về một [view](structure-views.md)
có tên là `say`. Tham số `message` luôn luôn được gửi qua view để xem nó có được dùng hay không. Kết quả việc render được
thực hiện trong mỗi action. Ứng dụng sẽ nhận kết quả này và hiển thị tới user trên trình duyệt (như là một trang HTML đầy đủ). 


Tạo mới View <span id="creating-view"></span>
---------------

[Views](structure-views.md) đảm nhận việc hiển thị thông tin và tương tác với người dùng. Để thực hiện yêu câu hiển thị
lời chào "Hello", bạn cần phải tạo một view `say` có chức năng hiển thị tham số `message`, tham số này được nhận từ action gửi đến:

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

Bạn cần lưu trữ view `say` nằm ở đường dẫn `views/site/say.php`. Mỗi khi phương thức [[yii\web\Controller::render()|render()]]
được gọi ở action, nó sẽ tìm kiếm tập tin PHP nằm ở đường dẫn `views/ControllerID/ViewName.php`.

Lưu ý rằng, đoạn code trên, biến `message` đã được phương thức [[yii\helpers\Html::encode()|HTML-encoded]]
mã hóa trước khi được in ra. Việc mã hóa là cần thiết khi gửi các tham số tới user, các tham số này có thể bị tấn công qua
[XSS (cross-site scripting)](https://en.wikipedia.org/wiki/Cross-site_scripting) đây là kỹ thuật tấn công bằng cách chèn chèn các 
thẻ HTML hoặc đoạn mã JavaScript độc hại.

Tất nhiên, bạn có thể thêm các nội dung ở view `say`.Nội dung bao gồm các thẻ HTML, dữ liệu văn bản, và cũng có thể là các câu lệnh PHP.
Trên thực tế, view `say` chỉ là các đoạn mã PHP được thực thi bởi phương thức [[yii\web\Controller::render()|render()]].
Nội dung được gửi ra từ view sẽ được gửi tới ứng dụng (application) như những phản hồi kết quả. 
Sau đó ứng dụng sẽ gửi kết quả tới user.


Trying it Out <span id="trying-it-out"></span>
-------------

Sau khi đã tạo action và view, bạn có thể truy cập vào trang bởi việc truy cập vào URL sau:

```
https://hostname/index.php?r=site/say&message=Hello+World
```

![Hello World](images/start-hello-world.png)

URL này sẽ trả về một trang và hiển thị lời chào "Hello World". Trang này có cùng phần header và footer như những trang khác trong ứng dụng. 

Nếu bạn không nhập tham số `message` vào URL, bạn chỉ xem thấy mỗi dòng "Hello" được hiển thị. Bởi vì tham số `message` được thông qua phương thức `actionSay()`, và mỗi khi tham số này không được nhập,
thì giá trị mặc đinh `"Hello"` sẽ được thay thế.

> Info: Trang này có cùng phần header và footer như những trang khác là bởi vì phương thức [[yii\web\Controller::render()|render()]]
  sẽ tự động nhúng nội dung của view `say` vào một [layout](structure-views.md#layouts) layout này nằm ở `views/layouts/main.php`.

Tham số `r` ở trên URL sẽ được giải thích thêm. Nó là chuẩn cho bộ định tuyến [route](runtime-routing.md), mỗi ứng dụng sẽ cung cấp ID
tương ứng với từng action. Với các đinh dạng router `ControllerID/ActionID`. Khi ứng dụng nhận request, ứng dụng sẽ kiểm tra các tham số 
theo cùng request đó, sử dụng `ControllerID` để xác định lớp điều khiển để xử các request. Sau đó, bộ điều khiển sẽ
xác dịnh `ActionID` cần được khởi tạo để xử lý công việc. Trong ví dụ này, route `site/say`
sẽ gán (ám chỉ tới) bộ điều khiển `SiteController` và action `say`. Điều này sẽ có kết quả là, phương thức `SiteController::actionSay()` sẽ được gọi để xử lý các request.

> Info: Giống như actions, ứng dụng sử dụng các định danh ID để nhận diện các controller. Các Controller ID
  có quy tắc đặt tên giống với các action IDs. Tên của controller được chuyển đổi từ các controller IDs 
  bằng việc loại bỏ dấu gạch ngang từ đinh danh ID, tận dụng các chữ cái đầu tiên trong mỗi từ,
  và từ đứng trước `Controller`. Ví dụ, bộ điều khiển controller ID có tên là `post-comment` sẽ tương ứng
  với controller là `PostCommentController`.


Tổng kết <span id="summary"></span>
-------

Qua phần này, bạn đã thao tác với phần controller và view nằm trong mẫu thiết kế MVC.
Bạn đã tạo một action thuộc phần của controller để xử lý các request . Và bạn cũng đã tạo được view cho việc 
hoàn thành nội dung trong thông điệp trả về . Trong ví dụ đơn giản này, không có model được sử dụng để thao tác dữ liệu mà chỉ sử dụng tham số `message`.

Bạn cũng đã học được router trong Yii, cái mà có vai trò quan trọng trong việc thiết lập kết nối giữa user và các controller actions.

Trong phần tiếp , bạn sẽ tìm hiểu cách tạo một model, và thêm mới các trang có chứa HTML form.
