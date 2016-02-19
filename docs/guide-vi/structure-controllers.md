Bộ điều khiển (Controller)
===========

Controller thuộc một phần trong mẫu thiết kế [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).
Controller là đối tượng được kế thừa từ class [[yii\base\Controller]] và chịu trách nhiệm xứ lý các yêu cầu và gửi phản hồi
. Đặc biệt, sau khi tiếp nhận các yêu cầu điều khiển từ [ứng dụng](structure-applications.md),
controllers sẽ phân tích thông tin yêu cầu được gửi đến, gửi dữ liệu qua [models](structure-models.md) để xử lý, và gán kết quả xử lý từ model
vào [views](structure-views.md), và cuối cùng là gửi phản hồi.


## Hành động (Actions) <span id="actions"></span>

Mỗi Controller đều chứa các *action* để user có thế tìm thấy, gửi yêu cầu tới ứng dụng để xử lý
. Mỗi bộ điều khiển có thể có nhiều hành động.

Ví dụ dưới mô tả Controller `post` cùng với 2 action là : `view` và `create`:

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

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Post;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
}
```

Với action `view` (được định nghĩa bởi phương thức `actionView()`), dòng đầu dùng [Model](structure-models.md)
để tải dữ liệu dựa theo định danh ID; Nếu Model được tải thành công, thì sẽ được hiển thị qua
[view](structure-views.md) là  `view`. Còn không, ứng dụng sẽ thông báo ngoại lệ là không tìm thấy.

Với action `create` (được định nghĩa bởi phương thức `actionCreate()`), tương tự như vậy. Trước tiên
sẽ khởi tạo [Model](structure-models.md), Model sẽ thực hiện nhận dữ liệu và lưu thông tin. Nếu cả hai việc này thành công thì Controller
sẽ điều hướng trình duyệt tới View `view` cùng với định danh ID vừa được tạo bởi Model. Còn không, Controller sẽ gọi
View  `create` có chức năng hiển thị form nhập liệu.


## Bộ định tuyến (Routes) <span id="routes"></span>

Người dùng có thể tìm thấy các actions qua các bộ định tuyến gọi là *routes*. Mỗi Route là chuỗi bao gồm các thông tin:

* Một định danh của Module:  chỉ tồn tại nếu bộ điều khiển thuộc về thành phần [module](structure-modules.md);
* Một định danh của [Controller](#controller-ids): là một chuỗi xác định duy nhất của Controller trong ứng dụng
  (hoặc có thể là Module nếu Controller tương ứng là một Module);
* Một [Action ](#action-ids):là một chuỗi xác định duy nhất của Action trong ứng dụng.

Mỗi Route có định dạng như sau:

```
ControllerID/ActionID
```

hoặc có định dạng sau nếu Controller được gán như một Module:

```php
ModuleID/ControllerID/ActionID
```

Như vậy nếu user truy cập vào đường dẫn sau `http://hostname/index.php?r=site/index`, thì hành động `index` nằm trong bộ điều khiển `site`
sẽ được thực hiện. Để biết thêm thông tin về cách bộ định tuyến xác định các hành động, vui lòng tham khảo tại mục
[Routing và URL Generation](runtime-routing.md).


## Tạo Controller <span id="creating-controllers"></span>

Trong mỗi [[yii\web\Application|Ứng dụng Web]], Controllers cần được kế thừa từ class [[yii\web\Controller]] hoặc các lớp con của nó
. Tương tự trong [[yii\console\Application|Ứng dụng console]], Controllers cần được kế thừa từ class
[[yii\console\Controller]] hoặc các lớp con của nó. Đoạn code sau được định nghĩa trong Controller `site` :

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### Định danh Controller (Controller ID) <span id="controller-ids"></span>

Thông thường, Controller được thiết kế để xử lý các yêu cầu từ các nguồn tài nguyên cụ thể.
Vì lý do này, mỗi định danh của Controller thường là danh từ cập nhật tới kiểu tài nguyên được xử lý.
Ví dụ, sử dụng `article` như định danh của Controller nhằm lưu giữ các dữ liệu về bài viết.

Mặc định, Thông tin các định danh Controller nên chỉ chứa các ký tự : Các chữ cái viết thường, số,
dấu gạch dưới, dấu nối, và dấu gạch nghiêng phải.  Ví dụ, `article` và `post-comment` đều là những định danh đúng,
trong khi đó `article?`, `PostComment`, `admin\post` đều không hợp lý.

Các định danh Controller ID nên chứa tiền tố trong thư mục con. Ví dụ, `admin/article` xác định cho Controller `article`
nằm trong thư mục `admin` được dựa theo [[yii\base\Application::controllerNamespace|controller namespace]].
Các ký tự hợp lệ dùng cho các tiền tố của thư mục con bao gồm: Các chữ cái viết thường, số, dấu gạch, và dấu gạch nghiêng phải
, dấu gạch nghiêng phải được dùng để phân cách như các thư mục con (vd. `panels/admin`).


### Tên lớp Controller <span id="controller-class-naming"></span>

Tên lớp của các Controller được khởi tạo từ các định danh Controller theo các bước sau:

1. Chuyển ký tự đầu tiên trong mỗi từ cách nhau bởi dấu gạch nối thành ký tự hoa. Lưu ý rằng nếu các định danh Controller 
  có chứa dấu gạch chéo, thì quy tắc này chỉ được áp dụng ở phần sau dấu gạch chéo cuối cùng trong các định danh.
2. Xoá các dấu gạch nối và thay thế các dấu gạch chéo xuôi thành dấu gạch chéo ngược.
3. Thêm hậu tố `Controller`.
4. Thêm [[yii\base\Application::controllerNamespace|controller namespace]].

Xem ví dụ sau, giả sử [[yii\base\Application::controllerNamespace|controller namespace]]
nhận giá trị mặc định là `app\controllers`:

* `article` thành `app\controllers\ArticleController`;
* `post-comment` thành `app\controllers\PostCommentController`;
* `admin/post-comment` thành `app\controllers\admin\PostCommentController`;
* `adminPanels/post-comment` thành `app\controllers\adminPanels\PostCommentController`.

Các lớp Controller cần được [tự động tải](concept-autoloading.md). Vì vậy, trong ví dụ trên,
lớp của Controller `article` cần được lưu vào file có [bí danh](concept-aliases.md)
là `@app/controllers/ArticleController.php`; trong khi đó `admin/post-comment` cần được lưu vào file
là `@app/controllers/admin/PostCommentController.php`.

> Lưu ý: Ở ví dụ với định danh Controller `admin/post-comment` hướng dẫn bạn đặt các Controller vào trong thư mục con
  của [[yii\base\Application::controllerNamespace|không gian tên Controller]]. Thông tin này khá là hữu ích
  mỗi khi bạn muốn quản lý các Controllers và các chuyên mục và bạn không muốn sử dụng thành phần [Modules](structure-modules.md).


### Controller Map <span id="controller-map"></span>

Bạn có thể cấu hình thông tin về mục  [[yii\base\Application::controllerMap|controller map]] để khắc phục những hạn chế về các định danh
và tên class của Controller được mô tả ở trên. Điều này khá hữu ích khi bạn muốn sử dụng
các Controller ở bên thứ ba và bạn không có quyền việc kiểm soát các class này.

Bạn có thể cấu hình [[yii\base\Application::controllerMap|controller map]] trong mục
[cấu hình ứng dụng](structure-applications.md#application-configurations). Ví dụ:

```php
[
    'controllerMap' => [
        // mô tả Controller "account" được sử dụng
        'account' => 'app\controllers\UserController',

        // mô tả về cấu hình Controller"article" dạng mảng 
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]
```


### Controller mặc định <span id="default-controller"></span>

Mỗi ứng dụng đều có một Controller mặc định được mô tả qua thuộc tính [[yii\base\Application::defaultRoute]].
Khi một yêu cầu không được mô tả cụ thể ở mục [route](#routes), thì route mặc định sẽ được gọi.
Chẳng hạn [[yii\web\Application|Web applications]], có giá trị là `'site'`, trong khi đó [[yii\console\Application|ứng dụng console]],
có route mặc định là `help`. Vì vậy, nếu truy cập vào URL sau `http://hostname/index.php`, thì Controller `site` sẽ được gọi và xử lý yêu cầu.

Bạn có thể thay đổi thông tin Controller mặc định tại mục [cấu hình ứng dung](structure-applications.md#application-configurations) như sau:

```php
[
    'defaultRoute' => 'main',
]
```


## Tạo Actions <span id="creating-actions"></span>

Tạo mới một Action khá là đơn giản, bằng chỉ việc định nghĩa trong lớp Controller cùng với tên *action phương thức*. Các phương thức của mỗi Action
đều có phạm vi *toàn cục* tên của phương thức được bắt đầu bằng từ `action`. Kết quả trả về của mỗi action sẽ tương ứng với
dữ liệu được gửi tới user. Đoạn mã sau sẽ định nghĩa hai action là, `index` và `hello-world`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHelloWorld()
    {
        return 'Hello World';
    }
}
```


### Định danh của Action (Action ID) <span id="action-ids"></span>

Mỗi action được dùng cho nhiệm vụ với tài nguyên cụ thể. Vì lý do này, mỗi
action ID thường là một đông từ, như `view`, `update`, vv.

Mặc định, mỗi action ID chỉ nên chứa các chữ cái: Chữ thường, số,
dấu gạch dưới, và dấu gạch ngang. (Bạn cũng có thể sử dụng các dấu gạch ngang để nối các từ lại với nhau.) Ví dụ,
`view`, `update2`, và `comment-post` đều là những định danh hợp lệ, còn `view?` và `Update` là không hợp lệ.

Có hai cách để tạo mới các action: inline actions và standalone actions. Với inline action được định nghĩa
như những phương thức trong lớp Controller, trong khi đó standalone action là lớp được kế thừa từ lớp
[[yii\base\Action]] hoặc là lớp con. Nếu bạn không muốn tái sử dụng các action thì bạn có thể dùng inline actions
, cách này thường hay được sử dụng hơn. Trong khi đó standalone actions thường được tạo để sử dụng
ở những Controllers khác nhau và được dùng như [thành phần mở rộng](structure-extensions.md).


### Inline Actions <span id="inline-actions"></span>

Inline actions được định nghĩa vào trong các phương thức như chúng ta đã mô tả trên.

Tên của phương thức thường đặt tên theo định danh của action và theo các bước sau:

1. Chuyển ký tự đầu tiên trong mỗi từ định danh của action thành ký tự in hoa.
2. Xoá dấu gạch nối.
3. Thêm tiền tố `action`.

Ví dụ, `index` thành `actionIndex`, và `hello-world` thành `actionHelloWorld`.

> Lưu ý: Việc đặt tên của các phương thức cần phải *cẩn thận*. Nếu bạn có phương thức là `ActionIndex`,
  thì phương thức của action sẽ không xác định, như vậy, khi có yêu cầu tới action `index`
  thì sẽ sinh ra lỗi. Cũng lưu ý rằng các phương thức này phải ở phạm vi public (toàn cục). Các phạm vi private (riêng) hoặc protected (bảo vệ)
  thì sẽ không được định nghĩa như một action.


Inline actions thường được hay sử dụng hơn bởi vì việc khởi tạo đơn giản hơn. Tuy nhiên,
nếu bạn muốn tái sử dụng action ở những vị trí khác, bạn có thể tham khảo thêm ở mục *standalone action*.


### Standalone Actions <span id="standalone-actions"></span>

Standalone actions được định nghĩa từ việc kế thừa từ class [[yii\base\Action]] hoặc các lớp con của nó.
Ví dụ, ở phiên bản Yii đã phát hành, các action [[yii\web\ViewAction]] và [[yii\web\ErrorAction]], đều là những
standalone actions.

Để sử dụng standalone action, bạn cần phải khai báo ở phần *liên kết các action* bằng việc ghi đè lên phương thức
[[yii\base\Controller::actions()]] ở lớp Controller như sau:

```php
public function actions()
{
    return [
        // khai báo action "error" bằng việc sử dụng tên class
        'error' => 'yii\web\ErrorAction',

        // khai báo action "view" bằng thông tin cấu hình dạng mảng
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

Như vậy, phương thức `actions()` sẽ trả về một mảng và chứa các khoá của các định danh action và giá trị tương ứng
tên class hoặc thông tin [cấu hình](concept-configurations.md). Không giống như inline actions, action ID được dùng cho standalone
actions có thể chứa các ký tự tuỳ ý, miễn là chúng được khai báo trong phương thức `actions()`.

Để tạo các class standalone action, bạn nên kế thừa từ lớp [[yii\base\Action]] hoặc lớp con của nó, và hiện thực
phương thức là `run()`. Vài trò của phương thức `run()` tương tự như một phương thức của action. Chẳng hạn,

```php
<?php
namespace app\components;

use yii\base\Action;

class HelloWorldAction extends Action
{
    public function run()
    {
        return "Hello World";
    }
}
```


### Kết quả trả về của Action <span id="action-results"></span>

Kết quả trả về của phương thức action hoặc phương thức `run()` của standalone action khá quan trọng. Nó là
kết quả tương ứng của từng action.

Giá trị trả về là đối tượng [phản hồi](runtime-responses.md) được gửi tới user như những phản hồi.

* Chẳng hạn với [[yii\web\Application|Ứng dụng Web]], kết quả trả về bao gồm dữ liệu được gán vào thuộc tính
  [[yii\web\Response::data]] và chuyển sang dữ liệu là string chuyển tới nội dung phản hồi kết quả.
* Với [[yii\console\Application|ứng dụng console]], kết quả trả về là số nguyên tương ứng với thuộc tính
  [[yii\console\Response::exitStatus|exit status]] của mỗi lần thực thi lệnh.

Ở ví dụ dưới, action sẽ trả về là chuỗi dữ liệu và được xử lý như nội dung phản hồi tới user
. Ví dụ dưới chỉ cách các action điều hướng tới trình duyệt một URL 
bằng việc gửi một đối tượng phản hồi (vì phương thức [[yii\web\Controller::redirect()|redirect()]] sẽ trả về
một đối tượng):

```php
public function actionForward()
{
    // điều hướng tới URL http://example.com
    return $this->redirect('http://example.com');
}
```


### Các tham số của Action <span id="action-parameters"></span>

Các phương thức dành cho inline action và phương thức `run()` cho standalone actions có thể nhận các tham số,
được gọi là *các tham số action*. Giá trị nhận được từ các yêu cầu. Với [[yii\web\Application|Ứng dụng Web]],
giá trị của các tham số được nhận từ biến `$_GET` sử dụng các tham số như các khoá;
với [[yii\console\Application|ứng dụng console]], các tham số sẽ tương ứng với các đối số dòng lệnh.

Trong ví dụ sau, action `view`  (là một inline action) được khai báo hai tham số là: `$id` và `$version`.

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public function actionView($id, $version = null)
    {
        // ...
    }
}
```

Các tham số cho action sẽ được dùng như sau và tương ứng với các yêu cầu khác nhau:

* `http://hostname/index.php?r=post/view&id=123`: biến `$id` sẽ nhận giá trị là
  `'123'`,  trong khi đó tham số `$version` nhận giá trị null vì không có đối số `version` được truyền lên.
* `http://hostname/index.php?r=post/view&id=123&version=2`: biến `$id` và `$version` sẽ nhận giá trị tương ứng là
   `'123'` và `'2'`.
* `http://hostname/index.php?r=post/view`: ngoại lệ [[yii\web\BadRequestHttpException]] sẽ được gửi ra
  vì tham số `$id` không được gửi lên.
* `http://hostname/index.php?r=post/view&id[]=123`: a [[yii\web\BadRequestHttpException]] exception will be thrown
  because `$id` parameter is receiving an unexpected array value `['123']`.

If you want an action parameter to accept array values, you should type-hint it with `array`, like the following:

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

Now if the request is `http://hostname/index.php?r=post/view&id[]=123`, the `$id` parameter will take the value
of `['123']`. If the request is `http://hostname/index.php?r=post/view&id=123`, the `$id` parameter will still
receive the same array value because the scalar value `'123'` will be automatically turned into an array.

The above examples mainly show how action parameters work for Web applications. For console applications,
please refer to the [Console Commands](tutorial-console.md) section for more details.


### Action mặc định <span id="default-action"></span>

Each controller has a default action specified via the [[yii\base\Controller::defaultAction]] property.
When a [route](#routes) contains the controller ID only, it implies that the default action of
the specified controller is requested.

By default, the default action is set as `index`. If you want to change the default value, simply override
this property in the controller class, like the following:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'home';

    public function actionHome()
    {
        return $this->render('home');
    }
}
```


## Chu trình Controller <span id="controller-lifecycle"></span>

When processing a request, an [application](structure-applications.md) will create a controller
based on the requested [route](#routes). The controller will then undergo the following lifecycle
to fulfill the request:

1. The [[yii\base\Controller::init()]] method is called after the controller is created and configured.
2. The controller creates an action object based on the requested action ID:
   * If the action ID is not specified, the [[yii\base\Controller::defaultAction|default action ID]] will be used.
   * If the action ID is found in the [[yii\base\Controller::actions()|action map]], a standalone action
     will be created;
   * If the action ID is found to match an action method, an inline action will be created;
   * Otherwise an [[yii\base\InvalidRouteException]] exception will be thrown.
3. The controller sequentially calls the `beforeAction()` method of the application, the module (if the controller
   belongs to a module), and the controller.
   * If one of the calls returns false, the rest of the uncalled `beforeAction()` methods will be skipped and the
     action execution will be cancelled.
   * By default, each `beforeAction()` method call will trigger a `beforeAction` event to which you can attach a handler.
4. The controller runs the action.
   * The action parameters will be analyzed and populated from the request data.
5. The controller sequentially calls the `afterAction()` method of the controller, the module (if the controller
   belongs to a module), and the application.
   * By default, each `afterAction()` method call will trigger an `afterAction` event to which you can attach a handler.
6. The application will take the action result and assign it to the [response](runtime-responses.md).


## Best Practices <span id="best-practices"></span>

In a well-designed application, controllers are often very thin, with each action containing only a few lines of code.
If your controller is rather complicated, it usually indicates that you should refactor it and move some code
to other classes.

Here are some specific best practices. Controllers

* may access the [request](runtime-requests.md) data;
* may call methods of [models](structure-models.md) and other service components with request data;
* may use [views](structure-views.md) to compose responses;
* should NOT process the request data - this should be done in [the model layer](structure-models.md);
* should avoid embedding HTML or other presentational code - this is better done in [views](structure-views.md).
