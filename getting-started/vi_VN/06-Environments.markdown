Các môi trường
================

Trong thư mục `web/` có 2 file PHP:
`index.php` và `frontend_dev.php`. Những file này được gọi là **front controller**;
tất cả các yêu cầu tới ứng dụng đều thông qua chúng. Nhưng tại sao chúng ta lại có 2
front controller cho mỗi application?

Cả 2 file này đều cho cùng một application nhưng ở các **môi trường** khác nhau.
Khi bạn phát triển một ứng dụng, trừ khi bạn phát triển trực tiếp trên
production server, bạn cần vài môi trường:

  * **Môi trường phát triển**: môi trường này được sử dụng bởi **lập trình viên** khi họ làm việc với ứng dụng để sửa lỗi, thêm tính năng mới, ...
  * **Môi trường test**: Môi trường này được dùng tự động bởi ứng dụng.
  * **Môi trường giả lập**: Môi trường này được dùng bởi **khách hàng**
    để kiểm tra ứng dụng và thông báo lỗi hoặc thiếu tính năng.
  * **Môi trường sản phẩm**: Đây là môi trường để **người dùng cuối** tương tác.

Các môi trường khác nhau như thế nào? Trong môi trường phát triển,
ứng dụng cần log tất cả các yêu cầu để dễ debug, hệ thống cache phải tắt để khi ứng dụng thay đổi khi thay đổi code. Do đó, môi trường development phải được cấu hình phù hợp cho lập trình viên. Ví dụ khi một lỗi xảy ra, để giúp lập trình viên debug lỗi nhanh hơn, symfony hiển thị exception với tất cả các thông tin về yêu cầu hiện tại trên trình duyệt:

![An exception in the dev environment](http://www.symfony-project.org/images/jobeet/1_2/01/exception_dev.png)

Nhưng ở môi trường sản phẩm, tầng cache phải được bật và tất nhiên ứng dụng phải hiểu thị thông báo lỗi đã được sửa lại thay vì hiển thị lỗi thực sự. Do đó, môi trường sản phẩm phải được tối ưu về tốc độ và tính thân thiện với người dùng.

![An exception in the prod environment](http://www.symfony-project.org/images/jobeet/1_2/01/exception_prod.png)

>**TIP**
>Nếu bạn mở file front controller, bạn sẽ thấy nội dung những file này tương tự như nhau, chỉ khác nội dung cấu hình môi trường:
>
>     [php]
>     // web/index.php
>     <?php
>
>     require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
>
>     $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
>     sfContext::createInstance($configuration)->dispatch();

Web debug toolbar cũng là một ví dụ về việc sử dụng môi trường. Nó hiển thị trên môi trường phát triển và cho phép bạn truy cập nhiều thông tin bằng cách click vào các tab: cấu hình hiện tại của ứng dụng, log yêu cầu hiện tại, câu SQL thực thi ở database engine, thông tin bộ nhớ, và thời gian thực thi.
