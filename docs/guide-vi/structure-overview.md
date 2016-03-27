Tổng quan về kiến trúc ứng dụng
========

Các ứng dụng Yii được tổ chức dựa theo mẫu thiết kế [model-view-controller (MVC)](http://wikipedia.org/wiki/Model-view-controller)
. [Models](structure-models.md) chứa nghiệp vụ logic, truy xuất database và định nghĩa các quy tắc xác thực dữ liệu; [views](structure-views.md)
đảm nhận việc hiển thị thôn tin của model; và [controllers](structure-controllers.md) có nhiệm vụ điều hướng các yêu cầu và chuyển các tương tác giữa
[models](structure-models.md) và [views](structure-views.md).

Ngoài mô hình MVC, ứng dụng Yii có những phần phần sau đây:

* [entry scripts](structure-entry-scripts.md): là file đầu tiên chứa các mã nguồn để tiếp nhận các request của người dùng.
  Thành phần này có trách nhiệm bắt đầu về chu trình xử lý các yêu cầu trong ứng dụng.
* [ứng dụng](structure-applications.md): là đối tượng có phạm vi truy cập toàn cục giúp quản lý các thành phần trong ứng dụng
  và điều hướng chúng để thực hiện các yêu cầu.
* [thành phần](structure-application-components.md): là đối tượng được đăng ký với ứng dụng
  và cung cấp những dịch vụ cho các yêu cầu xử lý .
* [modules](structure-modules.md): là những gói có chứa mô hình MVC hoàn chỉnh.
  Một ứng dụng có thể được tổ chức dưới dạng nhiều module.
* [filters](structure-filters.md): chứa những mã nguồn cần được gọi trước và sau việc xử lý của từng yêu cầu của bộ điều khiển
  handling of each request by controllers.
* [widgets](structure-widgets.md): các đối tượng được nhúng vào [views](structure-views.md). Các widget có thể chứa các nghiệp vụ logic
  và có thể tái sử dụng ở những view khác.

Mô hình sau mô tả cấu trúc ứng dụng ở dạng tĩnh:

![Static Structure of Application](images/application-structure.png)
