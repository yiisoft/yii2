The Definitive Guide to Yii 2.0
===============================

Các hướng dẫn được phát hành theo [Các điều khoản về tài liệu Yii](https://www.yiiframework.com/doc/terms/).

Tất cả bản quyền đã được bảo hộ (All Rights Reserved).

2014 (c) Yii Software LLC.


Giới thiệu
------------

* [Về Yii](intro-yii.md)
* [Hướng dẫn nâng cấp lên từ phiên bản 1.1](intro-upgrade-from-v1.md)


Bắt đầu
---------------

* [Những gì bạn cần biết](start-prerequisites.md)
* [Cài đặt Yii](start-installation.md)
* [Thực hiện chạy ứng dụng](start-workflow.md)
* [Viết chương trình đầu tiên](start-hello.md)
* [Làm việc với Forms](start-forms.md)
* [Làm việc với Databases](start-databases.md)
* [Sử dụng Gii để sinh mã tự động](start-gii.md)
* [Nâng cao](start-looking-ahead.md)


Kiến trúc ứng dụng (Application Structure)
---------------------

* [Tổng quan về kiến trúc ứng dụng](structure-overview.md)
* [Mục Scripts](structure-entry-scripts.md)
* [Ứng dụng (Applications)](structure-applications.md)
* [Các thành phần bên trong ứng dụng](structure-application-components.md)
* [Controllers](structure-controllers.md)
* [Models](structure-models.md)
* [Views](structure-views.md)
* [Modules](structure-modules.md)
* [Bộ lọc (Filters)](structure-filters.md)
* [Widgets](structure-widgets.md)
* [Assets](structure-assets.md)
* [Phần mở rộng (Extensions)](structure-extensions.md)


Xử lý yêu cầu (Handling Requests)
-----------------

* [Tổng quan](runtime-overview.md)
* [Khởi động](runtime-bootstrapping.md)
* [Định tuyến (Routing) và khởi tạo đường dẫn (URL Creation)](runtime-routing.md)
* [Yêu cầu (Requests)](runtime-requests.md)
* [Kết quả (Responses)](runtime-responses.md)
* [Sessions và Cookies](runtime-sessions-cookies.md)
* [Xử lý lỗi (Handling Error)](runtime-handling-errors.md)
* [Logging](runtime-logging.md)


Các khái niệm chính
------------

* [Thành phần (Components)](concept-components.md)
* [Thuộc tính (Properties)](concept-properties.md)
* [Sự kiện (Events)](concept-events.md)
* [Hành vi (Behaviors)](concept-behaviors.md)
* [Cấu hình (Configurations)](concept-configurations.md)
* [Bí danh (Aliases)](concept-aliases.md)
* [Lớp tự động nạp (Autoloading)](concept-autoloading.md)
* [Service Locator](concept-service-locator.md)
* [Dependency Injection Container](concept-di-container.md)


Làm việc với Databases
----------------------

* [Data Access Objects](db-dao.md): Kết nối cơ sở dữ liệu, truy vấn cơ bản, giao dịch và phương thức hoạt động
* [Query Builder](db-query-builder.md): Sử dụng một truy vấn đơn giản, các lớp cơ sở dữ liệu trừu tượng
* [Active Record](db-active-record.md): The Active Record ORM, truy vấn và thao tác với dữ liệu, định nghĩa các mối quan hệ giữa các bảng
* [Migrations](db-migrations.md): Cung cấp cho đội dự án một công cụ dễ dàng trong việc quản lý những schema CSDL trong ứng dụng
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


Nhận dữ liệu từ user
-----------------------

* [Tạo mới Forms](input-forms.md)
* [Kiểm tra dữ liệu đầu vào (Validating Input)](input-validation.md)
* [File Upload](input-file-upload.md)
* [Thu thập dữ liệu từ danh sách đầu vào (Đang phát triển)](input-tabular-input.md)
* [Lấy dữ liệu cho nhiều Models (Chưa giải quyết)](input-multiple-models.md)
* [Mở rộng ActiveForm ở phía Máy khách](input-form-javascript.md)


Hiển thị dữ liệu
---------------

* [Định dạng dữ liệu (Data Formatting)](output-formatter.md)
* [Phân trang (Pagination)](output-pagination.md)
* [Sắp xếp (Sorting)](output-sorting.md)
* [Cung cấp dữ liệu ra (Data Providers)](output-data-providers.md)
* [Dữ liệu Widgets](output-data-widgets.md)
* [làm việc với Client Scripts](output-client-scripts.md)
* [Giao diện (Theming)](output-theming.md)


Bảo mật (Security)
--------

* [Xác thực (Authentication)](security-authentication.md)
* [Quyền (Authorization)](security-authorization.md)
* [Các thao tác xử lý với Passwords (Đang phát triển)](security-passwords.md)
* [Auth Clients](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [Best Practices](security-best-practices.md)


Bộ nhớ Cache
-------

* [Tổng quan](caching-overview.md)
* [Cache dữ liệu](caching-data.md)
* [Fragment Caching](caching-fragment.md)
* [Page Caching](caching-page.md)
* [HTTP Caching](caching-http.md)


RESTful Web Services
--------------------

* [Bắt đầu](rest-quick-start.md)
* [Tài nguyên (Resources)](rest-resources.md)
* [Bộ điều khiển (Controllers)](rest-controllers.md)
* [Routing](rest-routing.md)
* [Định dạng thông điệp gửi đi (Response Formatting)](rest-response-formatting.md)
* [Xác thực (Authentication)](rest-authentication.md)
* [Rate Limiting](rest-rate-limiting.md)
* [Phiên bản (Version)](rest-versioning.md)
* [Error Handling](rest-error-handling.md)


Công cụ phát triển (Development Tools)
-----------------

* [Thanh công cụ gỡ lỗi và sửa lỗi (Debug Toolbar và Debugger)](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Sử dụng Gii để tạo code](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [Tạo tài liệu về API ](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


Testing
-------

* [Tổng quan](test-overview.md)
* [Thiết lập môi trường](test-environment-setup.md)
* [Unit Tests](test-unit.md)
* [Kiểm tra chức năng (Functional Tests)](test-functional.md)
* [Acceptance Tests](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Chủ đề năng cao
--------------

* [Advanced Application Template](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide)
* [Building Application from Scratch](tutorial-start-from-scratch.md)
* [Giao diện dòng lệnh (Console Commands)](tutorial-console.md)
* [Core Validators](tutorial-core-validators.md)
* [Quốc tế hóa (Internationalization)](tutorial-i18n.md)
* [Thư (Mailing)](tutorial-mailing.md)
* [Tối ưu hiệu năng ứng dụng (Performance Tuning)](tutorial-performance-tuning.md)
* [Shared Hosting Environment](tutorial-shared-hosting.md)
* [Template Engines](tutorial-template-engines.md)
* [Tích hợp mã nguồn của bên thứ ba (Working with Third-Party Code)](tutorial-yii-integration.md)
* [Dùng Yii như các framework nhỏ](tutorial-yii-as-micro-framework.md)


Widgets
-------

* [GridView](https://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](https://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](https://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](https://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](https://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](https://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](https://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](https://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Bootstrap Widgets](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap/doc/guide)
* [jQuery UI Widgets](https://www.yiiframework.com/extension/yiisoft/yii2-jui/doc/guide)


Helpers
-------

* [Tổng quan](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)

