Entry Scripts
=============

Entry script là tiến trình đầu tiên của ứng dụng. Một ứng dụng (hoặc
ứng dụng Web hoặc ứng dụng console) đều có một entry script. Người dùng đầu cuối tạo các request tới entry script, entry script
sẽ khởi tạo ứng dụng và nhanh chóng chuyển các yêu cầu tới chúng.

Entry script dành cho các ứng dụng web cần được thiết lập ở dưới thư mục truy cập Web để người dùng cuối có thể truy cập
. Những mục này thường được đặt tên là `index.php`, tuy nhiên có thể sử dụng các tên khác,
được cung cấp và có thể xác định bởi các máy chủ Web.

Entry script cho các ứng dụng console thông thường được nằm ở [đường dẫn cơ sở](structure-applications.md)
của ứng dụng và có tên là `yii` (cùng với hậu tố `.php`). Chúng được xây dựng để thực thi các ứng dụng console
thông qua dòng lệnh `./yii <route> [arguments] [options]`.

Entry scripts có chức năng chính như sau:

* Khai báo các hằng số ở phạm vi toàn cục;
* Đăng ký [Composer autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading);
* Tải các file class của [[Yii]];
* Tải cấu hình ứng dụng;
* Tạo và cấu hình các phiên bản [application](structure-applications.md);
* Gọi phương thức [[yii\base\Application::run()]] để xử lý các request được gọi tới.


## Ứng dụng Web <span id="web-applications"></span>

Mã nguồn dưới đây là các dòng lệnh trong mục script trong [Mẫu ứng dụng cơ bản](start-installation.md).

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// register Composer autoloader
require(__DIR__ . '/../vendor/autoload.php');

// include Yii class file
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// load application configuration
$config = require(__DIR__ . '/../config/web.php');

// create, configure and run application
(new yii\web\Application($config))->run();
```


## Ứng dụng Console(dòng lệnh) <span id="console-applications"></span>

Tương tự, Mã nguồn dưới đây là các dòng lệnh trong mục script của ứng dụng console:

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

// register Composer autoloader
require(__DIR__ . '/vendor/autoload.php');

// include Yii class file
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

// load application configuration
$config = require(__DIR__ . '/config/console.php');

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```


## Định nghĩa các hằng số <span id="defining-constants"></span>

Entry scripts thích hợp để định nghĩa các hằng ở phạm vi toàn cục. Yii hỗ trợ 3 hằng số sau:

* `YII_DEBUG`: xác định xem ứng dụng đang chay trong chế độ debug (gỡ lỗi). Khi ở chế độ debug, ứng dụng
  sẽ log các thông tin, và sẽ thông báo chi tiết về các lỗi nếu có các ngoại lệ được gửi ra. Vì lý do này
  , chế độ debug nên được dùng thường xuyên trong quá trình xây dựng ứng dụng. Giá trị mặc định của hằng `YII_DEBUG` là false.
* `YII_ENV`: xác định thông tin về môi trường của ứng dụng đang chạy (sản phẩm hay đang phát triển). Điều này sẽ mô tả chi tiết trong phần
  [Cấu hình](concept-configurations.md#environment-constants). Giá trị mặc định của hằng số `YII_ENV` là `'prod'`, có nghĩa là ứng dụng đang chạy là phiển bản sản phẩm
  đã phát hành.
* `YII_ENABLE_ERROR_HANDLER`: mô tả nơi cho phép được giữ (handler) các lỗi được cung cấp bởi Yii. Giá trị mặc đình của hằng
 số là true.

Khi định nghĩa các hằng số, chúng ta thường sử dụng đoạn mã như sau:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

Khai báo trên tương đương với đoạn code sau:

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

Ta thấy đoạn code trên ngắn gọi và dễ hiểu hơn nhiều.

Việc định nghĩa các hằng số nên được thực hiện ở phần đầu của entry script để các hằng số này có thể được gọi
ở những file php khác.
