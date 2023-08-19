Ứng dụng
============

Mỗi ứng dụng là một đối tượng giúp quản lý tổng thể cấu trúc và vòng đời của ứng dụng Yii.
Mỗi ứng dụng Yii đều chứa một đối tượng ứng dụng, đối tượng này được khởi tạo tại mục
[entry script](structure-entry-scripts.md) và đồng thời được truy cập qua biểu thức `\Yii::$app`.

> Gợi ý: Phụ thuộc vào từng ngữ cảnh, có khi chúng ta gọi là "một application", có nghĩa là một đối tượng ứng dụng
  hoặc một hệ thống ứng dụng.

Có 2 kiểu ứng dụng: [[yii\web\Application|Ứng dụng Web]] và
[[yii\console\Application|ứng dụng giao diện dòng lệnh]]. Tương tự như vậy, ứng dụng Web xử lý với các yêu cầu về Web,
, ứng dụng còn lại sẽ xử lý với các yêu cầu ở giao diện dòng lệnh.


## Cấu hình ứng dụng <span id="application-configurations"></span>

Mỗi khi [entry script](structure-entry-scripts.md) tạo ứng dụng mới, nó sẽ tải thêm thông tin về
[cấu hình](concept-configurations.md) và gán vào trong ứng dụng, như sau:

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// tải các cấu hình ứng dụng
$config = require(__DIR__ . '/../config/web.php');

// gán cấu hình và khởi tạo ứng dụng
(new yii\web\Application($config))->run();
```

Thông thường việc [cấu hình](concept-configurations.md), ứng dụng sẽ xác định làm thế nào để
khởi tạo các thuộc tính và đối tượng ứng dụng. Do việc cấu hình ứng dụng khá phức tạp nên vậy
, chúng thường được lưu giữ tại [các file cấu hình](concept-configurations.md#configuration-files),
như file `web.php` ở ví dụ trên.


## Các thuộc tính của ứng dụng <span id="application-properties"></span>

Có nhiều thuộc tính quan trọng mà bạn cần phải cấu hình trong ứng dụng. Những thuộc tính này
thường được mô tả về môi trường mà ứng dụng đang chạy. Chẳng hạn, ứng dụng cần biết làm thế nào để tải các [controllers](structure-controllers.md),
nơi lưu trữ các file tạm, vv. Trong phần dưới này, chúng ta sẽ tổng hợp thông tin về thuộc tính.


### Thuộc tính bắt buộc <span id="required-properties"></span>

Ở mỗi ứng dụng, bạn cần cấu hình ít nhất 2 thuộc tính là: [[yii\base\Application::id|id]]
và [[yii\base\Application::basePath|basePath]].


#### [[yii\base\Application::id|id]] <span id="id"></span>

Thuộc tính [[yii\base\Application::id|id]] giúp đặc tả một định danh ID để phân biệt với các ứng dụng khác
. Thuộc tính chủ yếu được sử dụng trong chương trình. Mặc dù nó không được yêu cầu, để thích hợp cho khả năng tương tác
nên chỉ sử dụng các chữ cái chữ số khi mô tả một định danh của ứng dụng.


#### [[yii\base\Application::basePath|basePath]] <span id="basePath"></span>

Thuộc tính [[yii\base\Application::basePath|basePath]] dùng để mô tả thư mục gốc của ứng dụng.
Nó là thư mục chứa tất cả mã nguồn của ứng dụng. Bên trong thư mục,
bạn sẽ thấy các thư mục con như `models`, `views`, và `controllers`, các thư mục con này chứa các mã nguồn
tương ứng với các thành phần trong mô hình MVC.

Bạn phải cấu hình thuộc tính [[yii\base\Application::basePath|basePath]] bằng sử dụng các đường dẫn trực tiếp
hoặc [một bí danh](concept-aliases.md). Trong các trường hợp, các thư mục tương ứng phải tồn tại, nếu không sẽ phát sinh ra lỗi
. Đường dẫn trực tiếp được lấy qua việc gọi hàm `realpath()` .

Thuộc tính [[yii\base\Application::basePath|basePath]] thường được dùng để lấy được các đường dẫn quan trọng khác
(vd đường dẫn dành cho thực thi). Vì vậy, bí danh `@app` được xác định là đường dẫn gốc 
. Các đường dẫn trong ứng dụng được lấy từ bí danh (vd `@app/runtime` tương ứng tới đường dẫn mục runtime).


### Các thuộc tính quan trọng <span id="important-properties"></span>

Các thuộc tính được mô tả trong phần này thường cần được cấu hình bởi vì mỗi ứng dụng có
các thuộc tính khác nhau.


#### [[yii\base\Application::aliases|aliases]] <span id="aliases"></span>

Thuộc tính cho phép khai báo các [bí danh(aliases)](concept-aliases.md) vào trong một mảng.
Các khóa lưu trữ tên bí danh, và giá trị trong mảng tương ứng với đường dẫn được khai báo.
Ví dụ:

```php
[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
```

Thuộc tính này được cung cấp cho bạn việc khai báo các bí danh trong cấu hình ứng dụng thay vì gọi phương thức
[[Yii::setAlias()]].


#### [[yii\base\Application::bootstrap|bootstrap]] <span id="bootstrap"></span>

Thuộc tính này khá quan trọng. Nó cung cấp cho bạn thông tin về mảng các thành phần (components) mà cần được 
chạy trong suốt chu trình ứng dụng [[yii\base\Application::bootstrap()|bootstrapping process]].
Ví dụ, nếu bạn muốn một [module](structure-modules.md) dùng để tùy biến các [URL](runtime-routing.md),
bạn có thể tùy biến các ID như phần tử trong các thuộc tính.

Mỗi thành phần được liệt kê ra có thể khai báo một trong các định dạng sau:

- một đinh danh về thành phần được tuân thủ qua [components](#components),
- một định danh về module tuân thủ theo quy định về [modules](#modules),
- một tên class,
- một mảng các cấu hình,
- một hàm dùng để khởi tạo và trả về một thành phần.

Ví dụ:

```php
[
    'bootstrap' => [
        // một định danh về thành phần hoặc module
        'demo',

        // tên class
        'app\components\Profiler',

        // mảng cấu hình
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ],

        // hàm trả về một thành phần
        function () {
            return new app\components\Profiler();
        }
    ],
]
```

> Lưu ý: Nếu định danh của module trùng với định danh của thành phần , ứng dụng sẽ sử dụng
> trong suốt tiền trình xử lý. Nếu bạn muốn chỉ sử dụng mỗi module, bạn cần lấy nó ở một hàm khác
> như sau:
>
> ```php
> [
>     function () {
>         return Yii::$app->getModule('user');
>     },
> ]
> ```


Trong suốt quá trình xử lý, mỗi thành phần sẽ được khởi tạo. nếu lớp thành phần được hiện thực từ giao diện
[[yii\base\BootstrapInterface]], thì phương thức [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]]
sẽ đồng thời được gọi.

Một ví dụ khác trong việc cấu hình ứng dụng trong [Mẫu Basic Project](start-installation.md),
module `debug` và `gii`  được cấu hình như những thành phần khi ứng dụng khởi chạy
ở môi trường phát triển:

```php
if (YII_ENV_DEV) {
    // cấu hình được thiết lập trong môi trường phát triển 'dev'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

> Lưu ý: Việc đưa quá nhiều các thành phần vào `bootstrap` sẽ làm giảm hiệu năng trong ứng dụng, bởi vì
  mỗi khi có yêu cầu, các thành phần sẽ được chạy. Vì vậy việc sử dụng các thành phần cần sử dụng một cách khôn ngoan.


#### [[yii\web\Application::catchAll|catchAll]] <span id="catchAll"></span>

Thuộc tính này chỉ được hỗ trợ với [[yii\web\Application| ứng dụng Web]]. Nó mô tả một
 [hành động](structure-controllers.md) và nhận xử lý mọi yêu cầu. Thường được sử dụng mỗi khi
ứng dụng đang ở chế độ bảo trì và cần xử lý mọi yêu cầu được gửi tới.

Thông tin được cấu hình bao gồm mảng và chứa thông tin về router và action.
Các thông tin mô tả các tham số (thông tin khóa-giá trị) để giới hạn các action. Ví dụ:

```php
[
    'catchAll' => [
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```


#### [[yii\base\Application::components|components]] <span id="components"></span>

Đây là thuộc tính quan trọng nhất. Nó cho phép đăng ký danh sách cách component để sử dụng ở các mục khác
được gọi là [application components](structure-application-components.md). Ví dụ:

```php
[
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
    ],
]
```

Mỗi thành phần ứng dụng đều xác định một mảng các thông tin chứa cặp key-value. Giá trị key đại diện cho định danh của thành phần,
trong khi đó value đại diện cho tên class hoặc thông tin về [cấu hình](concept-configurations.md).

Bạn có thể đăng ký bất kỳ thành phần nào vào ứng dụng, và các thành phần có thể truy cập ở phạm vi toàn cục
qua biểu thức `\Yii::$app->componentID`.

Xem thêm mục [Application Components](structure-application-components.md) để biết thêm thông tin.


#### [[yii\base\Application::controllerMap|controllerMap]] <span id="controllerMap"></span>

Thuộc tính này cho phép liên kết tới một định danh (ID) tới lớp của trình điều khiển. Mặc định, Yii sẽ liên kết
ID tới các lớp của trình điều khiển dựa trên [các nguyên tắc](#controllerNamespace) (chẳng hạn định danh ID của trình điều khiển `post` sẽ liên kết
tới lớp `app\controllers\PostController`). Bằng việc cấu hình những thuộc tính này, bạn có thay đổi các nguyên tắc này cho các trình điều khiển cụ thể
. Trong ví dụ sau, `account` sẽ được liên kết tới class
`app\controllers\UserController`, trong khi đó `article` sẽ liên kết tới class `app\controllers\PostController`.

```php
[
    'controllerMap' => [
        [
            'account' => 'app\controllers\UserController',
            'article' => [
                'class' => 'app\controllers\PostController',
                'enableCsrfValidation' => false,
            ],
        ],
    ],
]
```

Danh sách khóa của các thuộc tính trên đại diện cho ID của trình điều khiển, giá trị của mỗi khóa sẽ đại diện về thông tin
tên class của trình điều khiển hoặc [các thông tin về cấu hình](concept-configurations.md).


#### [[yii\base\Application::controllerNamespace|controllerNamespace]] <span id="controllerNamespace"></span>

Thuộc tính này xác định các thông tin tên lớp mặc định của trình điều khiển. Mặc định là
`app\controllers`. Nếu ID của trình điều khiển là `post`, theo quy ước thì tên class của trình điều khiển (không bao gồm
không gian tên) sẽ là `PostController`, và tên lớp đầy đủ sẽ là `app\controllers\PostController`.

Các lớp trình điều khiển thường được lưu trữ ở thư mục con của thư mục chính các không gian tên.
Chẳng hạn, với ID của trình điều khiển `admin/post`, tương ứng với tên lớp đầy đủ sẽ là
 `app\controllers\admin\PostController`.

Điều này khá quan trọng vì các lớp điều khiển có thể được [tải tự động](concept-autoloading.md)
và các không gian tên của các lớp điều khiển sẽ khớp với giá trị của các thuộc tính. Nếu không thì,
bạn sẽ nhận thông báo lỗi "Không tìm thấy trang" khi truy cập vào ứng dụng.

Trong trường hợp khác, nếu bạn muốn bỏ các quy ước này như mổ tả ở trên, bạn có thể tùy chỉnh lại các thuộc tính trong phần [controllerMap](#controllerMap).


#### [[yii\base\Application::language|language]] <span id="language"></span>

Thuộc tính này mô tả thông tin về ngôn ngữ trong mỗi ứng dụng và nội dung được hiển thị tới user.
Giá trị mặc định của thuộc tính là `en`, có nghĩa là tiếng Anh. Bạn có thể tùy chỉnh thuộc tính này
rằng nếu ứng dụng của bạn hỗ trợ đa ngôn ngữ .

Giá trị của thuộc tính được xác định theo chuẩn [quốc tế hóa](tutorial-i18n.md),
bao gồm các thông tin, định dạng ngày giờ, số, vv. Ví dụ, widget [[yii\jui\DatePicker]] 
sẽ sử dụng các giá trị thuộc tính qua việc xác định ngôn ngữ nào cần được hiển thị và định dạng ngày giờ như thế nào.

Khuyến khích bạn xác định các ngôn ngữ dựa theo [IETF language tag](https://en.wikipedia.org/wiki/IETF_language_tag).
Ví dụ, `en` là chuẩn cho tiếng anh, trong khi đó `en-US` chuẩn cho tiếng anh ở Mỹ (United States).

Xem thêm thông tin về thuộc tính này tại mục [Internationalization](tutorial-i18n.md).


#### [[yii\base\Application::modules|modules]] <span id="modules"></span>

Thuộc tính này mô tả các thông tin về [modules](structure-modules.md) được chứa trong ứng dụng.

Thuộc tính này chứa mảng các lớp về module hoặc thông tin về [cấu hình](concept-configurations.md) chứa mảng các khóa
về các định danh của module. Ví dụ:

```php
[
    'modules' => [
        //  "booking" mô tả tên class
        'booking' => 'app\modules\booking\BookingModule',

        // "comment" được mô tả với mảng cấu hình
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

Tham khảo thêm ở phần [Modules](structure-modules.md) để biết thêm thông tin.


#### [[yii\base\Application::name|name]] <span id="name"></span>

Thuộc tính này mô tả tên của ứng dụng và hiển thị tới user. Khác với thuộc tính
[[yii\base\Application::id|id]], cần phải là tên duy nhất, thì thuộc tính này dùng với mục đích để hiển thị tới user;
không cần thiết phải là tên duy nhất.

Nếu trong mã nguồn bạn không cần phải dùng tới nó thì bạn không cần phải thiết lập.


#### [[yii\base\Application::params|params]] <span id="params"></span>

Thuộc tính này là mảng chứa các tham số mà có thể truy cập trong ứng dụng ở phạm vi toàn cầu. Thay vì trong mã
nguồn của bạn cần được mã hóa bởi số và ký tự, đây là cách tốt để định nghĩa các tham số của ứng dụng, định nghĩa một lần 
và có thể được truy cập ở mọi nơi. Ví dụ, bạn có thể định nghĩa kích thước ảnh thumbnail
với kích thước như sau:

```php
[
    'params' => [
        'thumbnail.size' => [128, 128],
    ],
]
```

Bạn có thể thực hiện dòng lệnh sau để lấy tham số về kích thước ảnh thumbnail:

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

Bạn có thể thay đổi kích thước ảnh thumbnail sau đó, bạn chỉ cần thay đổi vào trong mục cấu hình ứng dụng;
bạn không cần phải đụng chạm vào mã nguồn của bạn.


#### [[yii\base\Application::sourceLanguage|sourceLanguage]] <span id="sourceLanguage"></span>

Thuộc tính mô tả về ngôn ngữ được sử dụng để viết mã nguồn của bạn. Giá trị mặc đinh là `'en-US'`,
nghĩa là tiếng Anh Mỹ(United States). Bạn nên cấu hình thuộc tính này nếu nội dung trong mã nguồn của bạn không phải là tiếng Anh.

Giống như thuộc tính [language](#language), you should configure this property in terms of
an [IETF language tag](https://en.wikipedia.org/wiki/IETF_language_tag). Ví dụ, `en` chuẩn cho tiếng Anh,
trong khi `en-US` chuẩn cho tiếng Anh Mỹ (United States).

Xem thêm trong phần [Quốc tế hóa](tutorial-i18n.md) để hiểu thêm thuộc tính này.


#### [[yii\base\Application::timeZone|timeZone]] <span id="timeZone"></span>

Thuộc tính này cung cấp cách khác để thiết lập time zone trong PHP.
Qua việc cấu hình thuộc tính này, chủ yếu được gọi qua hàm
[date_default_timezone_set()](https://www.php.net/manual/en/function.date-default-timezone-set.php). Ví dụ:

```php
[
    'timeZone' => 'America/Los_Angeles',
]
```


#### [[yii\base\Application::version|version]] <span id="version"></span>

Thuộc tính mô tả về phiên bản của ứng dụng. Mặc định là `'1.0'`. Bạn không cần phải thiết lập thuộc tính này nếu như
trong mã nguồn của bạn không dùng tới.


### Các thuộc tính thông dụng <span id="useful-properties"></span>

Những thuộc tính được mô tả trong phần dưới thường có sự cấu hình khác nhau bởi vì các giá trị thường khác nhau
. Tuy nhiên, nêu bạn muốn thay đổi giá trị mặc định, bạn có thể cấu hình theo cách của bạn.


#### [[yii\base\Application::charset|charset]] <span id="charset"></span>

Thuộc tính này mô tả các bộ ký tự mà ứng dụng sử dụng. Mặc định là `'UTF-8'`, hầu hết các ứng dụng đều sử dụng.


#### [[yii\base\Application::defaultRoute|defaultRoute]] <span id="defaultRoute"></span>

Thuộc tính này mô tả các [route](runtime-routing.md), ứng dụng sẽ dùng route này để thực hiện khi có yêu cầu
gửi đến mà không được mô tả. Mỗi router gồm có các module ID, a controller ID, hoặc có thể là một action ID.
Ví dụ, `help`, `post/create`, hoặc `admin/post/create`. Nếu action ID không khai báo, thuộc tính sẽ lấy giá trị mặc định
được mô tả trong [[yii\base\Controller::defaultAction]].

Đối với [[yii\web\Application| Ứng dụng Web ]], giá trị mặc định của thuộc tính là `'site'`, nghĩa là
trình điều khiển `SiteController` được gọi và một hành động mặc định được sử dụng. Như vậy, nếu bạn 
truy cập vào ứng dụng mà không cung cấp thông tin route, thì ứng dụng mặc định sẽ trả về hành động `app\controllers\SiteController::actionIndex()`.

Đối với [[yii\console\Application| Ứng dụng console]], thì giá trị mặc định là `'help'`, đồng nghĩa hành động
[[yii\console\controllers\HelpController::actionIndex()]] sẽ được gọi. Như vậy, nếu bạn chạy dòng lệnh `yii`
mà không cung cấp các tham số nào khác, thì nó sẽ hiển thị lên màn hình trợ giúp tương ứng kết quả của action index của trình điều khiển HelpController.


#### [[yii\base\Application::extensions|extensions]] <span id="extensions"></span>

Thuộc tính này mô tả về danh sách các [thành phần mở rộng (extensions)](structure-extensions.md) đã được cài và sử dụng trong ứng dụng.
Mặc định, thuộc tính sẽ nhận mảng được trả về từ file `@vendor/yiisoft/extensions.php`. File `extensions.php` 
được sinh tự động khi bạn sử dụng [Composer](https://getcomposer.org) để cài các thành phần mở rộng.
Ở các trường hợp này, thuộc tính này có thể không cần cấu hình.

Trong trường hợp, khi bạn muốn cấu hình các extension một cách thủ công, bạn có thể cấu hình thuộc tính như sau:

```php
[
    'extensions' => [
        [
            'name' => 'tên extension',
            'version' => 'phiên bản',
            'bootstrap' => 'BootstrapClassName',  // mặc định, giá trị thường là mảng
            'alias' => [  // mặc định
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... các extensions khác ...

    ],
]
```

Như bạn thấy ở phần trên, thuộc tính sẽ nhận thông tin bao gồm mảng các cấu hình. Mỗi extension được mô tả là mảng
bao gồm các thành phần là`name` và `version`. Nêu muốn extension cần được chạy ở tiến trình [bootstrap](runtime-bootstrapping.md)
, mỗi `bootstrap` cần được mô tả về tên lớp hoặc mảng giá trị về [cấu hình](concept-configurations.md)
. Mỗi extension có thể định nghĩa thêm các [bí danh (aliases)](concept-aliases.md).


#### [[yii\base\Application::layout|layout]] <span id="layout"></span>

Thuộc tính này mô tả vê layout mặc định được dùng mỗi khi render dữ liệu ra [view](structure-views.md).
Giá trị mặc định là `'main'`, nghĩa là file `main.php` nằm trong [đường dẫn layout](#layoutPath) được dùng.
Nếu giá trị [layout path](#layoutPath) và [view path](#viewPath) nhận giá trị là mặc định,
giá trị mặc định của file layout có thể được thay thế qua bí danh `@app/views/layouts/main.php`.

Bạn có thể cấu hình thuộc tính với giá trị `false` nếu bạn muốn tắt giá trị mặc định của layout.


#### [[yii\base\Application::layoutPath|layoutPath]] <span id="layoutPath"></span>

Thuộc tính mô tả đường dẫn nơi lưu trữ file layout. Giá trị mặc định sẽ là
`layouts` thư mục con nằm trong [đường dẫn view](#viewPath). Nếu [đường dẫn view](#viewPath) nhận giá trị mặc định
, giá trị mặc định tới đường dẫn layout được thay thế như một đường dẫn của bí danh `@app/views/layouts`.

Bạn có thể cấu hình như đường dẫn hoặc một [bí danh](concept-aliases.md).


#### [[yii\base\Application::runtimePath|runtimePath]] <span id="runtimePath"></span>

Đường dẫn chứa đường dẫn tới các file tạm của ứng dụng, như file log và cache, cần được tạo ra.
Giá trị mặc định của đường dẫn có thể lấy qua bí danh `@app/runtime`.

Bạn có thể cấu hình thuộc tính với đường dẫn hoặc một [bí danh](concept-aliases.md). Đường dẫn này cần được quyền ghi đè lên 
trong quá trình ứng dụng được chạy. Và user không thể truy cập vào đường dẫn
, bởi vì các tập tin này có thể chứa các thông tin nhạy cảm.

Yii cung cấp cách đơn giản nhất để truy cập vào đường dẫn này qua bí danh là `@runtime`.


#### [[yii\base\Application::viewPath|viewPath]] <span id="viewPath"></span>

Thuộc tính này chỉ định thư mục để lưu trữ những file view trong mô hình MVC. Giá trị mặc định là
là một bí danh `@app/views`. Bạn có thể cấu hình nó với thư mục hoặc đường dẫn [alias](concept-aliases.md).


#### [[yii\base\Application::vendorPath|vendorPath]] <span id="vendorPath"></span>

Thuộc tính này quy định cụ thể về thư mục được quản lý bởi [Composer](https://getcomposer.org). Thư mục này chứa các thư viện
được cung cấp bởi nhà phát triển và được dùng trong ứng dụng, bao gồm Yii framework. Giá trị mặc định là
một thư mục được cung cấp bởi bí danh `@app/vendor`.

Thuộc tính có thể được cấu hình là thư mục hoặc là đường dẫn [alias](concept-aliases.md). Mỗi khi bạn thay đổi thuộc tính này
, bạn cần phải thay đổi thông tin cấu hình Composer cho phù hợp.

Yii cung cấp cách thức đơn giản để truy cập vào đường dẫn này qua bí danh là `@vendor`.


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <span id="enableCoreCommands"></span>

Thuộc tính này chỉ được hỗ trợ bởi [[yii\console\Application|ứng dụng console]]. Nó được xác định 
vị trí các dòng lệnh được kích hoạt lên trong phiên bản Yii. Giá trị mặc định là `true`.


## Sự kiện <span id="application-events"></span>

Ứng dụng trong chu trình hoạt động sẽ gán một vài sự kiện để nắm bắt các yêu cầu. Bạn cần liên kết tới các sự kiện
vào trong ứng dụng như sau:

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

Các mô tả về cú pháp của các sự kiện `on eventName` được mô tả ở trong mục [Cấu hình](concept-configurations.md#configuration-format).

Cách khác, bạn có thể nắm bắt các sự kiện tại [tiến trình bootstrapping](runtime-bootstrapping.md)
mỗi khi ứng dụng được khởi tạo. Ví dụ:

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <span id="beforeRequest"></span>

Sự kiện được gán *trước lúc* ứng dụng nhận các yêu cầu. Tên sự kiện gọi là `beforeRequest`.

Mỗi nắm bắt được sự kiện, ứng dụng sẽ tải các thông tin cấu hình và khởi tạo. So it is a good place
to insert your custom code via the event mechanism to intercept the request handling process. For example,
in the event handler, you may dynamically set the [[yii\base\Application::language]] property based on some parameters.


### [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] <span id="afterRequest"></span>

Sự kiện được gán *sau khi* ứng dụng hoàn thành việc xử lý *trước lúc* đưa phản hồi.
Tên sự kiện là `afterRequest`.

Mỗi khi sự kiện được gán, việc nắm giữ yêu cầu xử lý thành công thì bạn có thể xử lý sau đó 
các thông tin về yêu cầu xử lý hoặc nội dung phản hồi.

Lưu ý rằng thành phần [[yii\web\Response|response]] luôn được gán một vài sự kiện mỗi lúc gửi nội dung
tới user. Những sự kiện được gán  *sau* sự kiện này là.


### [[yii\base\Application::EVENT_BEFORE_ACTION|EVENT_BEFORE_ACTION]] <span id="beforeAction"></span>

Sự kiện sẽ được gán *trước* khi thực hiện chạy một [hành động](structure-controllers.md).
Tên sự kiện là `beforeAction`.

Các tham số của sự kiện được khởi tạo từ [[yii\base\ActionEvent]]. Các sự kiện cần thiết lập thuộc tính
[[yii\base\ActionEvent::isValid]] về giá trị `false` để tạm ngưng hành động.
Ví dụ:

```php
[
    'on beforeAction' => function ($event) {
        if (some condition) {
            $event->isValid = false;
        } else {
        }
    },
]
```

Lưu ý, có một sự kiện tương tự `beforeAction` được gán bởi [modules](structure-modules.md)
và [controllers](structure-controllers.md). Ứng dụng sẽ nắm bắt sự kiện này trước
, tiếp sau đó bởi modules (nếu có), và cuối cùng là trình điều khiển. Nếu sự kiện đều thiết lập tham số
[[yii\base\ActionEvent::isValid]] là `false`, tất cả các yêu cầu nằm trong sự kiện sẽ không được nắm giữ.


### [[yii\base\Application::EVENT_AFTER_ACTION|EVENT_AFTER_ACTION]] <span id="afterAction"></span>

Tương tự, sự kiện này được gán *sau khi* khởi chạy một [hành động](structure-controllers.md).
Tên sự kiện là `afterAction`.

Các tham số của sự kiện được khởi tạo từ [[yii\base\ActionEvent]]. Xem thuộc tính
[[yii\base\ActionEvent::result]], sự kiện này có thể truy cập hoặc chỉnh sửa kết quả trả về.
Chẳng hạn:

```php
[
    'on afterAction' => function ($event) {
        if (điều kiện) {
            // thay đổi kết quả $event->result
        } else {
        }
    },
]
```

Lưu ý rằng với sự kiện `afterAction` được gán bởi [modules](structure-modules.md)
và [controllers](structure-controllers.md). Những đối tượng được gán vào trong sự kiện này được ưu tiên ngược lại
như sự kiện `beforeAction`. Đó là, trình điều khiển sẽ nắm giữ trước,
tiếp đến là module modules (nếu có), và cuối cùng là ứng dụng.


## Vòng đời ứng dụng <span id="application-lifecycle"></span>

![Vòng đời ứng dụng](images/application-lifecycle.png)

Khi một [entry script](structure-entry-scripts.md) được gọi và nắm giữ các yêu cầu,
vòng đời của ứng dụng sẽ được thực hiện như sau:

1. Entry script sẽ tải các thông tin cấu hình trong ứng dụng ra một mảng.
2. Entry script sẽ khởi tạo mới một ứng dụng:
  * Phương thức [[yii\base\Application::preInit()|preInit()]] sẽ được gọi, nhằm tải các thông tin cấu hình mà có sự ưu tiên cao
    , như thuộc tính [[yii\base\Application::basePath|basePath]].
  * Đăng ký một [[yii\base\Application::errorHandler|error handler]].
  * Cấu hình các thuộc tính trong ứng dụng.
  * Phương thức [[yii\base\Application::init()|init()]] sẽ được gọi và phương thức
    [[yii\base\Application::bootstrap()|bootstrap()]] sẽ tải thành phần bootstrapping.
3. Entry script sẽ gọi phương thức [[yii\base\Application::run()]] để chạy ứng dụng:
  * Sự kiện [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] sẽ được gán sau đó.
  * Xử lý các yêu cầu: chuyển các yêu cầu vào [bộ định tuyến (route)](runtime-routing.md) và các tham số liên quan;
    khởi tạo đối tượng module, controller, và action như phần mô tả ở bộ định tuyến; và khởi chạy action.
  * Gán sự kiện [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]].
  * Gửi phản hồi tới user.
4. Entry script tiếp nhận trạng thái kết thúc từ ứng dụng hoàn tất xử lý tiến trình.
