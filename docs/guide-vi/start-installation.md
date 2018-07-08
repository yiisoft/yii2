Cài đặt Yii
==============

Bạn có thể cài đặt Yii theo hai cách, dùng trình quản lý gói [Composer](http://getcomposer.org/) hoặc tải toàn bộ mã nguồn Yii về.
Cách thứ nhất thường được khuyến khích dùng hơn, vì nó cho phép bạn cài đặt thêm các [Gói mở rộng (extensions)](structure-extensions.md)  hoặc cập nhật Yii đơn giản chỉ mới một dòng lệnh.

Mặc định, sau khi cài đặt Yii sẽ cung cấp cho bạn một số tính năng cơ bản, như đăng nhập (login), form liên hệ (contact form), vv. 
Những tính năng trên đều được khuyến khích và sử dụng rộng rãi, vì thế, nó có thể hữu ích cho các dự án của bạn.
    
Trong bài hướng dẫn này và các phần tiếp theo, chúng ta sẽ tìm hiều cách cài ứng dụng Yii với tên *Basic Application Template* và
làm thế nào để triển khai các tính năng mới trên mẫu ứng dụng này. Yii đồng thời cũng cung cấp mẫu ứng dụng tên là [Advanced Application Template](tutorial-advanced-app.md)
Template này hướng đến những đội dự án cần phát triển ứng dụng có nhiều tầng (multiple tiers).

> Lưu ý: *Basic Application Template* thích hợp đến 90% cho việc phát triển web. Nó khác
với [Advanced Application Template](tutorial-advanced-app.md) trong cách tổ chức mã nguồn. Nếu bạn là người mới tìm hiều về Yii, chúng tôi khuyến khích
bạn bắt đầu với *Basic Application Template* , ứng dụng này đơn giản và ít chức năng. Thích hợp hơn cho việc tìm hiểu về Yii.



Cài đặt qua trinh quản lý gói Composer <span id="installing-via-composer"></span>
-----------------------

Nếu bạn chưa cài Composer, bạn có thể cài đặt theo đường link sau
[getcomposer.org](https://getcomposer.org/download/). Đối với hệ điều hành Linux và Mac OS X, bạn có thể chạy các lệnh sau đây:

    curl -s http://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

Còn trên HĐH Windows, bạn có thể tải về và chạy [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Nếu bạn có bất kỳ thắc mắc hoặc muốn biết thêm và nghiên cứu chuyên sâu về Composer, vui lòng tham khảo [Tài liệu Composer](https://getcomposer.org/doc/) 

Nếu bạn đã cài Composer rồi, hãy chắc chắn rằng bạn đang sử dụng phiên bản mới nhất. Bạn có thể cập nhật Composer bằng cách thực hiện lệnh
 `composer self-update`.

Sau khi cài đặt Composer, bạn có thể cài đặt Yii bằng cách chạy lệnh sau ở thư mục Web mà ứng dụng cần chạy:

    composer global require "fxp/composer-asset-plugin:^1.4.1"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

Câu lệnh đầu tiên sẽ cài đặt [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/)
và cho phép Composer có thể quản lý những package dependencies của bower và npm. Câu lệnh này chỉ cần chạy một lần.
Câu lệnh thứ hai sẽ cài đặt phiên bản Yii có tên là  `basic`. Bạn có thể chọn một tên thư mục khác nếu bạn muốn.

> Chú ý: Trong quá trình cài đặt Composer có thể yêu cầu thông tin đăng nhập từ tài khoản Github của bạn. điều này là bình thường bởi vì Composer 
> cần đầy đủ thông tin API rate-limit để lấy các thông tin gói phụ thuộc từ Github. Để biết thêm chi tiết,
> xin vui lòng tham khảo  [Composer documentation](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Thủ thuật: Nếu bạn muốn cài đặt phiên bản phát triển mới nhất của Yii, bạn có thể sử dụng lệnh sau để thay thế,
> điều này chỉ cần thêm [stability option](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Chú ý.  phiên bản phát triển của Yii(dev version) không nên sử dụng cho mô trường ứng dụng bởi vì nó có thể phá vỡ các hoạt động trong code.


Cài đặt từ tập tin lưu trữ <span id="installing-from-archive-file"></span>
-------------------------------

Việc cài đặt Yii từ một tập tin lưu trữ bao gồm ba bước:

1. Tải gói cài đặt từ [yiiframework.com](http://www.yiiframework.com/download/).
2. Giải nén file tải về vào một thư mục Web của ứng dụng cần chạy.
3. Sửa đồi file `config/web.php`  bởi nhập thông tin secret key `cookieValidationKey` ở mục cấu hình
   (này được thực hiện tự động nếu bạn đang cài đặt Yii sử dụng Composer):

   ```php
   // !!! chèn một secret key trong phần sau (nếu rỗng) - Việc này là cần thiết để xác thực cookie trong ứng dụng
   'cookieValidationKey' => 'Nhập secret key tuỳ chọn vào đây',
   ```


Các thiết lập cài đặt khác <span id="other-installation-options"></span>
--------------------------

Yii giới thiệu hai phương pháp cài đặt ở trên, những phương pháp này sẽ tạo ứng dụng Web.
.Đối với các dự án nhỏ hoặc cho việc học để sử dụng, đây là một điểm khởi đầu tốt.

Nhưng cũng có những phương pháp cài đặt khác:

* Nếu bạn chỉ muốn cài đặt các khung cốt lõi và muốn xây dựng toàn bộ một ứng dụng từ đầu,
  bạn có thể làm theo hướng dẫn như đã hướng dẫn ở bài viết [Building Application from Scratch](tutorial-start-from-scratch.md).
* Nếu bạn muốn bắt đầu với một ứng dụng phức tạp hơn, phù hợp hơn với môi trường phát triển trong team bạn,
  bạn có thể xem xét việc cài đặt mẫu ứng dụng [Advanced Application Template](tutorial-advanced-app.md).


Kết quả cài đặt <span id="verifying-installation"></span>
--------------------------

Sau khi cài đặt, bạn có thể sử dụng trình duyệt để truy cập ứng dụng Yii được cài đặt với URL dưới đây:

```
http://localhost/basic/web/index.php
```

URL này giả sử bạn đã cài đặt Yii trong một thư mục có tên `basic`, trực tiếp dưới thư mục gốc tài liệu máy chủ Web của bạn,
và rằng các máy chủ Web đang chạy trên máy tính cục bộ của bạn (`localhost`). Bạn có thể cần phải điều chỉnh nó trong môi trường cài đặt.

![Successful Installation of Yii](images/start-app-installed.png)

Bạn sẽ có thể thấy trang hiển thị "Congratulations!" ở trình duyệt của ban. Còn không, xin vui lòng kiểm tra xem PHP đáp ứng cài đặt của bạn
Các yêu cầu Yii. Bạn có thể kiểm tra xem các yêu cầu tối thiểu được đáp ứng bằng một trong những phương pháp sau đây:

* Sử dụng trình duyệt để truy cập vào URL `http://localhost/basic/requirements.php`
* Chay câu lệnh như sau:

  ```
  cd basic
  php requirements.php
  ```

Bạn nên cấu hình cài đặt PHP của bạn để nó đáp ứng các yêu cầu tối thiểu của Yii. Diều quan trọng nhất, bạn nên có PHP 5.4 hoặc hơn. Bạn cũng nên cài đặt
các gói [PDO PHP Extension](http://www.php.net/manual/en/pdo.installation.php) và một trình điều khiển cơ sở dữ liệu tương ứng
(như là `pdo_mysql` cho CSDL MySQL), nếu ứng dụng của bạn cần thao tác với CSLD.


Cấu hình máy chủ Web <span id="configuring-web-servers"></span>
-----------------------

> Lưu ý: Lưu ý: Nếu bạn chỉ là chạy thử ứng dụng Yii thay vì được triển khai(deploying) trong một môi trường sản xuất,
  bạn có thể bỏ qua phần này.

Các ứng dụng được cài đặt theo phương pháp trên, được chạy trong Windows, Max OS X, Linux hoặc máy chủ [Apache HTTP](http://httpd.apache.org/) 
hoặc [Nginx HTTP server](http://nginx.org/) và PHP phiên bản 5.4 hoặc cao hơn đều có thể được chạy trực tiếp.

Trong môi trường máy chủ sản xuất, bạn có thể cấu hình máy chủ để ứng dụng có thể truy cập thông qua URL http://www.example.com/index.php 
thay vì http://www.example.com/basic/web/index.php. Cấu hình này đòi hỏi các thư mục gốc tài liệu của máy chủ Web vào thư mục basic/web. Bạn cũng có thể  ẩn index.php trên URL, 
chi tiết trên URL phân tích và tạo ra một chương trình chiếu, bạn sẽ tìm hiểu làm thế nào để cấu hình Apache hoặc Nginx máy chủ để đạt được những mục tiêu này.

> Lưu ý: Thiết lập `basic/web` như thư mục gốc, bạn có thể ngăn chặn người dùng truy cập vào các dữ liệu cá nhân và các thông tin nhạy cảm được lưu trữ
 ở các thư mục con nằm trong `basic/web`. Từ chối truy cập vào các thư mục khác là một cải tiến bảo mật.

> Lưu ý: Bạn nên điều chính cấu trúc ứng dụng của bạn để bảo mật tốt hơn, điều này cần thiếu nếu khi ứng dụng của ban chạy trên những hosting miễn phí, ở môi trường mà bạn
không có quyền thay đổi các thiết lập ở server Web. Tham khảo thêm ở phần sau để biết thêm chi tiết [Shared Hosting Environment](tutorial-shared-hosting.md).


### Các khuyến nghị khi cấu hình máy chủ Apache <span id="recommended-apache-configuration"></span>

Sử dụng các cấu hình sau đây trong file `httpd.conf` của Apache hoặc trong một cấu hình máy chủ ảo. Lưu ý rằng bạn nên
thay thế đường dẫn đường dẫn thực tế `path/to/basic/web` cho `basic/web`.

```
# Thiết lập document root tới đường dẫn "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # use mod_rewrite for pretty URL support
    RewriteEngine on
    # If a directory or a file exists, use the request directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Otherwise forward the request to index.php
    RewriteRule . index.php

    # ...other settings...
</Directory>
```


### Các khuyến nghị khi cấu hình Nginx <span id="recommended-nginx-configuration"></span>

Để sử dụng [Nginx](http://wiki.nginx.org/), bạn cần phải cài đặt [FPM SAPI](http://php.net/install.fpm).
Bạn có thể cấu hình Nginx như sau, thay thế đường dẫn `path/to/basic/web` với đường dẫn thực tế ở
`basic/web` và  `mysite.test` thay thế bằng tên máy chủ thực tế để cung cấp dịch vụ.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.test;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Khi sử dụng cấu hình này, bạn cũng nên thiết lập `cgi.fix_pathinfo=0` ở file `php.ini`
để tránh nhiều hệ thống không cần thiết `stat()` khi gọi hệ thống.

Cũng lưu ý rằng khi bạn chạy một máy chủ HTTPS, bạn cần phải thêm dòng `fastcgi_param HTTPS on;` vào file cấu hình 
để Yii có thể hiểu ra những kết nối là an toàn.
