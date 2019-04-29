Modules
=======

Modules là các đơn vị phần mềm độc lập bao gồm các [models](structure-models.md), [views](structure-views.md),
[controllers](structure-controllers.md), và các thành phần hỗ trợ khác. Người dùng cuối có thể truy cập các controller
của module khi các controller đã được cài đặt trong [application](structure-applications.md). Vì những lý do này, thường được xem là
ứng dụng nhỏ. Modules khác với [applications](structure-applications.md) trong đó các mô-đun không thể tự được triển khai
và phải nằm trong các ứng dụng


## Tạo Modules <span id="creating-modules"></span>

Một module được tổ chức dưới dạng như một thư mục được gọi là [[yii\base\Module::basePath|đường dẫn cơ sở]] của module.
Trong thư mục, còn có các thư mục con, như là `controllers`, `models`, `views`, những thư mục này chứa các controllers,
models, views, và các đoạn mã khác, giống như trong một ứng dụng. Ví dụ sau đây cho thấy nội dung trong một module:

```
forum/
    Module.php                   tập tin lớp module
    controllers/                 chứa các tệp controller
        DefaultController.php    lớp controller mặc định
    models/                      chứa các tệp model
    views/                       chứa các tệp view và layout
        layouts/                 chứa các tệp layout của view
        default/                 chứa các file view cho DefaultController
            index.php            tập tin index cho view
```


### Lớp Module <span id="module-classes"></span>

Mỗi module nên có một lớp module cơ sở được kế thừa từ lớp [[yii\base\Module]]. Lớp nên được đặt ngay dưới
module [[yii\base\Module::basePath|base path]] và nên được [autoloadable](concept-autoloading.md).
Khi một module đang được truy cập, một phiên bản của lớp module tương ứng sẽ được tạo.
Giống như các [application instances](structure-applications.md), các phiên bản của module được dùng để chia sẽ dữ liệu và các component
cho mã trong các module.

Ví dụ sau cho thấy lớp module trông như thế nào:

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->params['foo'] = 'bar';
        // ...  các đoạn mã khởi tạo khác ...
    }
}
```

Nếu phương thức `init()` có chứa một số đoạn mã khởi tạo cho các thuộc tính module, bạn có thể lưu chúng theo các điều khoản
của mục [cấu hình](concept-configurations.md) và tải chúng theo đoạn mã sau ở phương thức `init()`:

```php
public function init()
{
    parent::init();
    // khởi tạo module với các cấu hình được tải từ file config.php
    \Yii::configure($this, require __DIR__ . '/config.php');
}
```

nơi tập tin cấu hình `config.php` có thể chứa nội dung sau, tương tự như trong
[cấu hình ứng dụng](structure-applications.md#application-configurations).

```php
<?php
return [
    'components' => [
        // danh sách các cấu hình thành phần
    ],
    'params' => [
        // danh sách các parameter
    ],
];
```


### Controller trong Modules <span id="controllers-in-modules"></span>

Khi tạo controllers trong module, các quy ước đặt tên lớp controller theo `controllers` với
không gian tên phụ của không gian tên của lớp module. Điều này cũng có nghĩa là các tệp lớp controller nên được đặt trong
đường dẫn `controllers` nằm trong module [[yii\base\Module::basePath|đường dẫn cơ sở]].
Ví dụ, để tạo controller `post` nằm trong module `forum` thể hiện trong mục con, bạn nên
khai báo lớp controller như sau:

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

Bạn có thể tùy chỉnh không gian tên của lớp controller bằng việc cấu hình thuộc tính [[yii\base\Module::controllerNamespace]]
. Trong trường hợp một số controllers nằm ngoài không gian tên, bạn có thể làm cho chúng có thể truy cập bằng cách cấu hình
qua thuộc tính [[yii\base\Module::controllerMap]], tương tự như [điều bạn làm cho ứng dụng](structure-applications.md#controller-map).


### Views trong Modules <span id="views-in-modules"></span>

Views trong module nên đặt tại đường dẫn là `views` nằm trong module [[yii\base\Module::basePath|base path]].
Đối với các views được xuất bản bởi controller trong module, chúng nên được đặt trong thư mục `views/ControllerID`,
với `ControllerID` đề cập đến [controller ID](structure-controllers.md#routes). Ví dụ, nếu lớp
controller là `PostController`, thì đường dẫn sẽ là `views/post` nằm trong
[[yii\base\Module::basePath|đường dẫn cơ sở]] của module.

Một module có thể chỉ định [layout](structure-views.md#layouts) được áp dụng để views được xuất bản qua các controller trong module
. Các layout nên mặc định đặt trong đường dẫn là `views/layouts`, và bạn nên cấu hình
thuộc tính [[yii\base\Module::layout]] để đề cập tới tên của layout. Nếu bạn không cấu hình thuộc tính `layout`,
thì layout trong ứng dụng sẽ được dùng thay thế.


### Console commands trong Modules <span id="console-commands-in-modules"></span>

Module có thể được khai báo các command, sẽ có sẵn thông qua chế độ [Console](tutorial-console.md).

Để tiện ích command nhìn thấy các command của bạn, bạn sẽ cần thay đổi thuộc tính [[yii\base\Module::controllerNamespace]]
, khi Yii được thực thi ở chế độ dòng lệnh, và trỏ nó vào không gian tên của các command.

Một cách để đạt điều trên là kiểm tra các loại instance của ứng dụng Yii trong phương thức `init()` của module:

```php
public function init()
{
    parent::init();
    if (Yii::$app instanceof \yii\console\Application) {
        $this->controllerNamespace = 'app\modules\forum\commands';
    }
}
```

Các lệnh của bạn sau đó sẽ có sẵn từ dòng lệnh bằng cách sử dụng route sau:

```
yii <module_id>/<command>/<sub_command>
```

## Sử dụng Module <span id="using-modules"></span>

Để sử dụng module trong ứng dụng, chỉ cần cấu hình ứng dụng bằng cách liệt kê các module trong thuộc tính
[[yii\base\Application::modules|modules]] của ứng dụng. Đoạn mã sau đây trong mục
[cấu hình ứng dụng](structure-applications.md#application-configurations) sử dụng từ khóa `forum` module:

```php
[
    'modules' => [
        'forum' => [
            'class' => 'app\modules\forum\Module',
            // ... các cấu hình khác cho module ...
        ],
    ],
]
```

Thuộc tính [[yii\base\Application::modules|modules]] nhận một mảng cấu hình cho các module. Mỗi key của mảng
đại diện cho *định danh module* trong đó xác định duy nhất module trong số tất cả các module trong ứng dụng, và giá trị
tương ứng của mảng là các [cấu hình](concept-configurations.md) cho việc tạo module.


### Routes <span id="routes"></span>

Giống như truy cập các controller trong ứng dụng, [routes](structure-controllers.md#routes) được dùng để truy cập tới các
controller trong module. Một route cho controller trong một module phải bắt đầu bằng định danh module theo sau là
[định danh controller](structure-controllers.md#controller-ids) và [định danh action](structure-controllers.md#action-ids).
Ví dụ, nếu một ứng dụng sử dụng module là `forum`, sau đó route
`forum/post/index` sẽ đại diện cho action `index` của controller `post` nằm trong module. Nếu route
chỉ có chứa định danh module, và thuộc tính [[yii\base\Module::defaultRoute]], sẽ mặc định là `default`,
sẽ xác định cho controller/action nào được sử dụng. Điều này có nghĩa là route `forum` sẽ đại diện cho controller `default`
trong module `forum`.

Các quy tắc quản lý URL cho các module nên được thêm vào trước phương thức [[yii\web\UrlManager::parseRequest()]] được kích hoạt. Có nghĩa là 
thực hiện nó trong phương thức `init()` của module sẽ không hoạt động vì module sẽ được khởi tạo khi các routes được xử lý. Vì vậy, các quy tắc
nên được thêm vào ở [giai đoạn bootstrap](structure-extensions.md#bootstrapping-classes). Đây cũng là cách hay để
để nhóm các quy tắc URL của module bằng [[\yii\web\GroupUrlRule]].  

Trong trường hợp một module được sử dụng để [version API](rest-versioning.md), quy tắc URL của nó nên được thêm trực tiếp vào mục `urlManager` 
của cấu hình ứng dụng.


### Truy cập vào Module <span id="accessing-modules"></span>

Trong một module, bạn có thể cần lấy thông tin instance của [lớp module](#module-classes) do vậy bạn có thể truy cập qua
định danh của module, tham số của module, module components, vv. Bạn có thể làm như vậy bằng cách sử dụng câu lệnh sau:

```php
$module = MyModuleClass::getInstance();
```

trong đó `MyModuleClass` đề cập đến tên của lớp module mà bạn đang quan tâm. Phương thức `getInstance()` method
sẽ trả về instance được yêu cầu của lớp module. Nếu module không được yêu cầu, phương thức sẽ trả về là
`null`. Lưu ý rằng bạn không muốn tự tạo một instance mới của lớp module vì nó sẽ khác với phiên bản được tạo bởi
Yii để đáp ứng các yêu cầu.

> Thông tin: Khi phát triển một module, bạn không nên cho rằng module sẽ có định danh cố định. Điều này là do một module
  có thể liên kế tới một định danh tùy ý khi được sử dụng trong một ứng dụng hoặc một module khác. Để có một định danh
  module, bạn nên sử dụng cách tiếp cận ở trên để lấy một instance của module trước, và nhận thông tin định danh qua
  `$module->id`.

Bạn cũng có thể truy cập vào instance của module bằng các phương pháp sau:

```php
// lấy module con có định danh là "forum"
$module = \Yii::$app->getModule('forum');

// lấy module mà controller đang yêu cầu
$module = \Yii::$app->controller->module;
```

Cách tiếp cận đầu tiên chỉ hữu ích khi bạn biết rõ định danh của module, trong khi cách tiếp cận thứ hai
được sử dụng tốt nhất khi bạn biết về các controller đang được yêu cầu.

Một khi bạn có các instance module, bạn có thể truy cập các tham số và các components đã đăng ký với module. Ví dụ,

```php
$maxPostCount = $module->params['maxPostCount'];
```


### Tải trước các module <span id="bootstrapping-modules"></span>

Một số module có thể cần phải được chạy cho mọi yêu cầu. Module [[yii\debug\Module|debug]] là một ví dụ như vậy.
Để làm được như vậy, liệt kê ra các định danh của modules trong thuộc tính [[yii\base\Application::bootstrap|bootstrap]] của ứng dụng.

Ví dụ, cấu hình ứng dụng sau đây đảm bảo module `debug` luôn luôn được tải:

```php
[
    'bootstrap' => [
        'debug',
    ],

    'modules' => [
        'debug' => 'yii\debug\Module',
    ],
]
```


## Các module con <span id="nested-modules"></span>

Các module có thể được lồng vào nhau với không giới hạn. Đó là, một module có thể chứa một module khác và module này có thể chứa
một module khác. Chúng tôi gọi module đầu là *module cha mẹ* trong khi module sau gọi là *module con*. Các module con phải được khai báo trong
thuộc tính [[yii\base\Module::modules|modules]] của các module cha mẹ. Ví dụ,

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                // bạn nên xem xét sử dụng một không gian tên ngắn hơn ở đây!
                'class' => 'app\modules\forum\modules\admin\Module',
            ],
        ];
    }
}
```

Đối với controller trong các module con, các route của nó nên bao gồm các định danh của các module cha.
Ví dụ, với route `forum/admin/dashboard/index` đại diện cho action `index` của controller`dashboard` 
bên trong module `admin` đó là một module con của module`forum`.

> Thông tin: Phương thức [[yii\base\Module::getModule()|getModule()]] chỉ trả về mô đun con trực tiếp thuộc về nó
. Thuộc tính [[yii\base\Application::loadedModules]] giữ một danh sách các module được tải, bao gồm cả
module trực tiếp và module lồng nhau, được đánh dầu bởi tên class.

## Truy cập các thành phần từ bên trong các module

Kể từ phiên bản 2.0.13 modules hỗ trợ [tree traversal](concept-service-locator.md#tree-traversal). Điều này cho phép các nhà phát triển
xây dựng module tham chiếu tới các thành phần (ứng dụng) thông qua service locator nằm trong module.
Điều này có nghĩa là nó được sử dụng `$module->get('db')` hơn là `Yii::$app->get('db')`.
Người dùng module có thể chỉ định thành phần cụ thể được sử dụng cho các module trong trường hợp thành phần (cấu hình) khác
được yêu cầu.

Ví dụ, xem xét một phần cấu hình ứng dụng này:

```php
'components' => [
    'db' => [
        'tablePrefix' => 'main_',
        'class' => Connection::class,
        'enableQueryCache' => false
    ],
],
'modules' => [
    'mymodule' => [
        'components' => [
            'db' => [
                'tablePrefix' => 'module_',
                'class' => Connection::class
            ],
        ],
    ],
],
```

Các bảng cơ sở dữ liệu ứng dụng sẽ có tiền tố là `main_`, trong khi đó các bảng trong module có tiền tố là `module_`.
Lưu ý rằng cấu hình ở trên không được hợp nhất; thành phần modules cho ví dụ trên sẽ có các bộ đệm truy vấn được bật 
vì nó là giá trị mặc định.

## Bài thực hành <span id="best-practices"></span>

Các module được sử dụng tốt nhất trong các ứng dụng lớn có tính năng có thể được chia thành nhiều nhóm, mỗi bộ bao gồm một tập hợp các 
tính năng liên quan chặt chẽ. Mỗi nhóm tính năng như vậy có thể được phát triển dưới dạng một module được phát triển và duy trì bởi
một nhà phát triển hoặc nhóm cụ thể.

Các module cũng là một cách tốt để sử dụng lại mã ở cấp độ nhóm tính năng. Một số tính năng thường được sử dụng,chẳng hạn như 
quản lý người dùng, quản lý bình luận, tất cả đều có thể được phát triển về mặt module để chúng 
có thể được tái sử dụng dễ dàng trong các dự án trong tương lai.
