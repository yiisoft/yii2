Các thành phần ứng dụng
======================

Mỗi ứng dụng là hiện thực của [mẫu thiết kế Service Locators](concept-service-locator.md). Mỗi ứng dụng sẽ chứa các thành phần
được gọi là *thành phần ứng dụng* giúp cung cấp các dịch vụ cho các tiến trình xử lý. Chẳng hạn,
thành phần `urlManager` đảm nhiệm chức năng cho bộ định tuyến cho các yêu cầu xử lý tới các bộ điều khiển;
thành phần `db` cung cấp các dịch vụ để giao tiếp với cơ sở dữ liệu (CSDL); và các thành phần khác.

Mỗi thành phần ứng dụng đều có một định danh ID giúp xác định thành phần duy nhất trong cùng một ứng dụng
. Bạn có thể truy cập vào các thành phần ứng dụng qua câu lệnh sau.

```php
\Yii::$app->componentID
```

Ví dụ, sử dụng câu lệnh `\Yii::$app->db` để lấy thông tin [[yii\db\Connection|kết nối tới CSDL]],
và câu lệnh `\Yii::$app->cache` để lấy thông tin [[yii\caching\Cache|primary cache]] đã đăng ký trong ứng dụng.

Mỗi thành phần ứng dụng được tạo một lần và được truy cập trong ứng dụng. Và có bất kỳ sự truy cập nào
sau đó đều trả về cùng một thể hiện của thành phần đó.

Bất kỳ đối tượng nào cũng có thể là thành phần ứng dụng. Bạn có thể đăng ký chúng bằng việc thiết lập các
thuộc tính [[yii\base\Application::components]] vào trong [mục cấu hình ứng dụng](structure-applications.md#application-configurations).
Ví dụ,

```php
[
    'components' => [
        // Dung class để đăng ký thành phần "cache"
        'cache' => 'yii\caching\ApcCache',

        // Dùng mảng các tham số để đăng ký thành phần "db"
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // Dùng hàm để đăng ký thành phần "search"
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Lưu ý: Bạn cần đăng ký các thành phần ứng dụng một cách cẩn thận.
  Các thành phần ứng dụng cũng như các biến có phạm vi toàn cục. Sử dụng quá nhiều các thành phần ứng dụng có thể khiến mã nguồn
  khó kiểm tra và bảo trì. Cách tốt nhất, bạn nên khởi tạo các thành phần ở phạm vi cục bộ
  và khi cần thiết có thể thêm vào ứng dụng.


## Thành phần tải tự động (Bootstrapping) <span id="bootstrapping-components"></span>

Như đề cập ở trên, các thành phần ứng dụng chỉ được khởi tạo khi nó được truy cập vào lần đầu tiên.
Nếu thành phần không được truy cập tại các yêu cầu xử lý, thì sẽ không được khởi tạo. Tuy vậy , thỉnh thoảng, các thành phần ứng dụng
có thể được khởi tạo ở mỗi yêu cầu, thậm chí nó không được truy cập.
Để làm được như vậy, bạn cần liệt kê các định danh vào trong thuộc tinh [[yii\base\Application::bootstrap|bootstrap]] của ứng dụng application.

Ví dụ, thông tin cấu hình sau sẽ chắc chắn rằng thành phần `log` luôn luôn được tải:

```php
[
    'bootstrap' => [
        'log',
    ],
    'components' => [
        'log' => [
            // Các thiết lập cho thành phần "log"
        ],
    ],
]
```


## Các thành phần ứng dụng chính <span id="core-application-components"></span>

Yii định nghĩa danh sách các thành phần ứng dụng chính cùng với nó là các định danh và thông tin cấu hình. Ví dụ,
thành phần [[yii\web\Application::request|request]] được dùng để lấy thông tin về các yêu cầu từ user
và xác minh rồi gửi tới các [bộ định tuyến (route)](runtime-routing.md); thành phần [[yii\base\Application::db|db]]
có chức năng thiết lập các kết nối và thông qua đó bạn có thể thực hiện các truy vấn vào CSDL.
Như vậy, các thành phần ứng dụng sẽ giúp ứng dụng Yii tiếp nhận các yêu cầu từ user.

Phần dưới là danh sách các thành phần ứng dụng chính được xác định trước. Bạn cần phải cấu hình và tùy biến chúng
như những thành phần ứng dụng khác. Mỗi khi bạn cấu hình các thành phần này,
nếu bạn không xác định các class, thì giá trị mặc định sẽ được dùng.

* [[yii\web\AssetManager|assetManager]]: quản lý các file tài nguyên (asset) được đóng gói và chia sẽ.
  Tham khảo thêm mục [Quản lý các file tài nguyên](structure-assets.md) để biết thêm chi tiết.
* [[yii\db\Connection|db]]: thực hiện kết nối CSDL và dựa vào thành phần có thể thực hiện các câu lệnh truy vấn dữ liệu.
  Lưu ý, khi bạn thiết lập thành phần này, bạn cần phải cung cấp các thông tin về các thuộc tính được yêu cầu
  , như [[yii\db\Connection::dsn]].
  Tham khảo thêm tại mục [Data Access Objects](db-dao.md) để biết thêm thông tin.
* [[yii\base\Application::errorHandler|errorHandler]]: nắm giữ các ngoại lệ và lỗi của PHP.
  Tham khảo thêm mục [Bắt lỗi](runtime-handling-errors.md) để biết thêm thông tin.
* [[yii\i18n\Formatter|formatter]]: định dạng dữ liệu mỗi khi gửi tới user. Ví dụ, các số có thể được
  hiển thị cùng với các dấu ngăn cách phần ngàn, ngày có thể được định dạng ở dạng ngày dài.
  Tham khảo thêm tại mục [Định dạng dữ liệu](output-formatting.md) để biết thêm thông tin.
* [[yii\i18n\I18N|i18n]]: hỗ trợ định dạng và dịch đa ngôn ngữ.
  Tham khảo thêm tại mục [Internationalization](tutorial-i18n.md) để biết thêm thông tin.
* [[yii\log\Dispatcher|log]]: quản lý mục log.
  Tham khảo thêm tại mục [Logging](runtime-logging.md) để biết thêm thông tin.
* [[yii\swiftmailer\Mailer|mail]]: hỗ trợ soạn thảo và gửi email.
  Tham khảo thêm tại mục [Mailing](tutorial-mailing.md) để biết thêm thông tin..
* [[yii\base\Application::response|response]]: represents the response being sent to end users.
  Tham khảo thêm tại mục [Responses](runtime-responses.md) để biết thêm thông tin..
* [[yii\base\Application::request|request]]: tiếp nhận các yêu cầu từ user.
  Tham khảo thêm tại mục [Requests](runtime-requests.md) để biết thêm thông tin..
* [[yii\web\Session|session]]: quản lý các phiên (session). Thành phần này chỉ được kích hoạt với
  [[yii\web\Application|Ứng dụng Web]].
  Tham khảo thêm tại mục [Sessions and Cookies](runtime-sessions-cookies.md) để biết thêm thông tin..
* [[yii\web\UrlManager|urlManager]]: xử lý thông tin về URL.
  Tham khảo thêm tại mục [URL Parsing and Generation](runtime-routing.md) để biết thêm thông tin..
* [[yii\web\User|user]]: giúp xác thực người dùng. Thành phần này chỉ được kích hoạt với
  [[yii\web\Application|Ứng dụng Web]]
  Tham khảo thêm tại mục [Xác thực (Authentication)](security-authentication.md) để biết thêm thông tin..
* [[yii\web\View|view]]: hỗ trợ giao diện.
  Tham khảo thêm tại mục[Views](structure-views.md) để biết thêm thông tin..
