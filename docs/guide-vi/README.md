The Definitive Guide to Yii 2.0
===============================

Các hướng dẫn được phát hành theo [Các điều khoản về tài liệu Yii](http://www.yiiframework.com/doc/terms/).

All Rights Reserved.

2014 (c) Yii Software LLC.


Giới thiệu
------------

* [Về Yii](intro-yii.md)
* [Nâng cấp lên từ phiên bản 1.1](intro-upgrade-from-v1.md)


Bắt đầu
---------------

* [Cài đặt Yii](start-installation.md)
* [Thực hiện chạy ứng dụng](start-workflow.md)
* [Viết lời chào đầu tiên](start-hello.md)
* [Làm việc với Forms](start-forms.md)
* [Làm việc với Databases](start-databases.md)
* [Sử dụng Gii để sinh code](start-gii.md)
* [Mức cao hơn](start-looking-ahead.md)


Kiến trúc ứng dụng (Application Structure)
---------------------

* [Tổng quan về kiến trúc ứng dụng](structure-overview.md)
* [Mục Scripts](structure-entry-scripts.md)
* [Ứng dụng (Applications)](structure-applications.md)
* [Thành phần ứng dụng](structure-application-components.md)
* [Bộ điều khiển (Controllers)](structure-controllers.md)
* [Models](structure-models.md)
* [Views](structure-views.md)
* [Modules](structure-modules.md)
* [Bộ lọc (Filters)](structure-filters.md)
* [Widgets](structure-widgets.md)
* [Assets](structure-assets.md)
* [Phần mở rộng (Extensions)](structure-extensions.md)


Yêu cầu xử lý (Handling Requests)
-----------------

* [Tổng quan](runtime-overview.md)
* [Bootstrapping](runtime-bootstrapping.md)
* [Routing và URL Creation](runtime-routing.md)
* [Yêu cầu (Requests)](runtime-requests.md)
* [Responses](runtime-responses.md)
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
* [Migrations](db-migrations.md): Cung cấp cho đội dự án dễ dàng trong việc quản lý những schema CSDL trong ứng dụng
* **TBD** [Sphinx](db-sphinx.md)
* **TBD** [Redis](db-redis.md)
* **TBD** [MongoDB](db-mongodb.md)
* **TBD** [ElasticSearch](db-elasticsearch.md)


Nhận dữ liệu từ user
-----------------------

* [Tạo mới Forms](input-forms.md)
* [Kiểm tra dữ liệu đầu vào (Validating Input)](input-validation.md)
* [File Upload](input-file-upload.md)
* [Thu thập dữ liệu từ danh sách đầu vào (Đang phát triển)](input-tabular-input.md)
* [Lấy dữ liệu cho nhiều Models (Chưa giải quyết)](input-multiple-models.md)


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
* [Auth Clients](security-auth-clients.md)
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

* [Thanh công cụ gỡ lỗi và sửa lỗi (Debug Toolbar và Debugger)](tool-debugger.md)
* [Sử dụng Gii để tạo code](tool-gii.md)
* **TBD** [Tạo tài liệu về API ](tool-api-doc.md)


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

* [Advanced Application Template](tutorial-advanced-app.md)
* [Building Application from Scratch](tutorial-start-from-scratch.md)
* [Giao diện dòng lệnh (Console Commands)](tutorial-console.md)
* [Core Validators](tutorial-core-validators.md)
* [Quốc tế hóa (Internationalization)](tutorial-i18n.md)
* [Thư (Mailing)](tutorial-mailing.md)
* [Tối ưu hiệu năng ứng dụng (Performance Tuning)](tutorial-performance-tuning.md)
* [Shared Hosting Environment](tutorial-shared-hosting.md)
* [Template Engines](tutorial-template-engines.md)
* [Working with Third-Party Code](tutorial-yii-integration.md)


Widgets
-------

* GridView: **TBD** liên kết tới trang demo
* ListView: **TBD** liên kết tới trang demo
* DetailView: **TBD** liên kết tới trang demo
* ActiveForm: **TBD** liên kết tới trang demo
* Pjax: **TBD** liên kết tới trang demo
* Menu: **TBD** liên kết tới trang demo
* LinkPager: **TBD** liên kết tới trang demo
* LinkSorter: **TBD** liên kết tới trang demo
* [Bootstrap Widgets](widget-bootstrap.md)
* [jQuery UI Widgets](widget-jui.md)


Helpers
-------

* [Tổng quan](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)

