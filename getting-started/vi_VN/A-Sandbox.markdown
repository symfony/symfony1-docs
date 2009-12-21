Phụ lục A: Sandbox
==================

Chương này sẽ chỉ ra cho bạn cách nhanh nhất để bắt đầu với symfony. Nếu bạn muốn bắt đầu một dự án thực, bạn có thể bỏ qua chương này, và chuyển sang đọc [chương cài đặt](#chapter_03-Symfony-Installation).

Sandbox là cách đơn giản nhất để sử dụng symfony mà không cần các bước cài đặt phức tạp.

>**CAUTION**
>Sandbox được cấu hình sẵn sử dụng SQLite, nên bạn cần cấu hình PHP hỗ trợ SQLite (xem chương [yêu cầu](#chapter_02-Prerequisites)). Bạn cũng cần đọc mục
>[cấu hình Database](#chapter_05-Project-Setup_sub_configuring_the_database)
>để biết cách thay đổi database cho sandbox.

Bạn có thể download symfony sandbox dưới định dạng `.tgz` hay `.zip` từ [trang cài đặt](http://www.symfony-project.org/installation/1_4) hoặc tại đường dẫn sau:

    http://www.symfony-project.org/get/sf_sandbox_1_4.tgz

    http://www.symfony-project.org/get/sf_sandbox_1_4.zip

Giải nén file này vào thư mục web root, và bạn đã thực hiện xong. Symfony project bây giờ đã có thể truy cập từ trình duyệt bằng cách gọi script `web/index.php`.

>**CAUTION**
>Đưa tất cả các file của symfony vào trong thư mục web root là cách đơn giản để
>test symfony ở máy local, nhưng không nên làm vậy ở
>production server do tất cả các file trong ứng dụng của bạn có thể thấy bởi
>end user.

Bây giờ bạn có thể kết thúc cài đặt bằng cách đọc [cấu hình Web Server](#chapter_06-Web-Server-Configuration)
và chương [môi trường](#chapter_07-Environments).

>**NOTE**
>Sandbox là một symfony project đã được cấu hình sẵn, nó rất
>dễ sử dụng cho một project mới. Nhưng luôn ghi nhớ rằng
>bạn cần cấu hình lại cho phù hợp; ví dụ
>đổi cấu hình bảo mật (xem cấu hình của XSS
>và CSRF ở các hướng dẫn sau).
