Yii là gì
===========

Yii là một PHP Framework mã nguồn mở và hoàn toàn miễn phí, có hiệu năng xử lý cao, phát triển tốt nhất trên các ứng dụng Web 2.0, sử dụng tối đa các thành phần (component-based PHP framework) để tăng tốc độ viết ứng dụng.
Tên Yii (được phát âm là `Yee` hoặc `[ji:]`) ở Trung Quốc có nghĩa là  "thật đơn giản và luôn phát triển". Nghĩa thứ hai có thể đọc ngắn gọn là **Yes It Is**!


Yii thích hợp nhất để làm gì?
---------------------

Yii, nói chung, là một framework phát triển ứng dụng Web nên có thể dùng để viết mọi loại ứng dụng Web 
và sử dụng ngôn ngữ lập trình PHP. Yii rất nhẹ và được trang bị giải pháp cache tối ưu nên đặc biệt 
hữu dụng cho ứng dụng web có dung lượng dữ liệu trên đường truyền lớn như web portal, forum, CMS, e-commerce, 
các dự án thương mại điện tử và các dịch vụ Web RESTful..


So sánh Yii Với các Frameworks khác?
-------------------------------------------

Nếu bạn có kinh nghiệm làm việc với các framework khác, bạn sẽ rất vui mừng khi thấy những nỗ lực của Yii:

- Giống như những PHP frameworks khác, Yii sử dụng mô hình MVC (Model-View-Controller) tổ chức code một cách hợp lý và có hệ thống.
- Yii tạo ra code đơn giản và thanh lịch, đây là triết lý trong chương trình. Yii sẽ không bao giờ 
  cố gắng tạo ra những mấu thiết kế quá an toàn và ít có sự thay đổi.
- Yii là framework hoàn chỉnh, cung cấp nhiều tính năng và được xác minh như: query builders, thao tác dữ liệu với
  ActiveRecord được dùng cho CSDL quan hệ và NoSQL; hỗ trợ phát triển RESTful API; sự hỗ trợ đa bộ nhớ cache; và nhiều hơn.
- Yii rất dễ mở rộng. Bạn có thể tùy chình hoặc thay thế bất kỳ một trong những bộ code chuẩn. Bạn cũng có thể
  tận dụng lợi thế của kiến trúc mở rộng chuẩn Yii để sử dụng hoặc phát triển mở rộng phân phối..
- Hiệu suất cao luôn luôn là một trong những mục tiêu chính của Yii.

Yii không chỉ được phát triển từ một người, nó được hỗ trợ bởi [đội ngũ phát triển cốt lõi mạnh mẽ][about_yii], cũng như một cộng đồng lớn, trong đó các chuyên gia liên tục
đóng góp cho sự phát triển của Yii. Nhóm nghiên cứu phát triển Yii giữ một mắt đóng trên các xu hướng phát triển Web mới nhất và trên thực hành tốt nhất và 
các tính năng được tìm thấy trong các khuôn khổ và các dự án khác. Các thực hành tốt nhất và các tính năng được tìm thấy ở những nơi khác có liên quan nhất thường xuyên 
được đưa vào khuôn khổ lõi và tiếp xúc thông qua giao diện đơn giản và thanh lịch.

[about_yii]: http://www.yiiframework.com/about/

Các phiên bản Yii
------------

Yii Hiện nay có hai phiên bản chính: 1.1 và 2.0. Phiên bản 1.1 là phiên bản cũ và bây giờ là trong chế độ bảo trì. Tiếp đến, phiên bản 2.0 là phiên bản đuọc viết lại hoàn toàn Yii, sử dụng các
công nghệ mới và giao thức mới, bao gồm trình quản lý gói Composer, các tiêu chuẩn code PHP PSR, namespaces, traits, và như vậy. Phiên bản 2.0 đại diện cho sự hình thành của framework 
và sẽ nhận được những nỗ lực phát triển chính trong vài năm tới. 
Hướng dẫn này chủ yếu là về phiên bản 2.0.


Yêu cầu hệ thống và các điều kiện cần thiết
------------------------------

Yii 2.0 đòi hỏi phiên bản PHP 5.4.0 hoặc cao hơn. Bạn có thể chạy bất kỳ gói Yii đi kèm với các yêu cầu hệ thống. 
kiểm tra xem những gì các đặc điểm cụ thể của từng cấu hình PHP.

Để tìm hiểu Yii, bạn cần có kiến thức cơ bản về lập trình hướng đối tượng (OOP), vì Yii là một framework hướng đối tượng
thuần túy. Yii 2.0 cũng sử dụng các tính năng PHP mới nhất, chẳng hạn như [namespaces](http://www.php.net/manual/en/language.namespaces.php) và [traits](http://www.php.net/manual/en/language.oop5.traits.php).
Hiểu được những khái niệm này sẽ giúp bạn nhanh chóng nắm bắt Yii 2.0.
