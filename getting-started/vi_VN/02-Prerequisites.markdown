Yêu cầu
=============

Trước khi cài đặt symfony, bạn cần dành chút thời gian để kiểm tra các thiết lập trên máy bạn, nó sẽ giúp bạn không phải mất thời gian với những lỗi phát sinh sau này.

Phần mềm
---------

Trước tiên, bạn cần có một môi trường phát triển web trên máy: web server (ví dụ Apache), hệ quản trị CSDL (MySQL, PostgreSQL, SQLite, ..), và PHP 5.2.4 hoặc mới hơn.

Giao diện dòng lệnh
----------------------

Framework symfony chứa sẵn công cụ dòng lệnh để tự động làm nhiều việc cho bạn. Nếu bạn dùng hệ điều hành họ Unix, bạn sẽ cảm thấy quen thuộc. Nếu sử dụng Windows, bạn cần dùng cửa sổ dòng lệnh `cmd`.

>**Note**
>Unix shell command có thể đưa vào môi trường Windows.
>Nếu bạn thích dùng những lệnh như `tar`, `gzip`, hay `grep` ở Windows, bạn
>có thể cài đặt [Cygwin](http://cygwin.com/).  Tài liệu trên trang chủ khá ít,
>bạn có thể đọc hướng dẫn cài đặt
>[tại đây](http://www.soe.ucsc.edu/~you/notes/cygwin-install.html).
>Bạn cũng có thể thử dùng Microsoft's
>[Windows Services for Unix](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx).

Cấu hình PHP
-----------------

Cấu hình PHP khác nhau tùy vào hệ điều hành. Bạn cần kiểm tra để chắc rằng cấu hình PHP đạt các yêu cầu tối thiểu của symfony.

Đầu tiên, hãy chắc rằng bạn có PHP 5.2.4 hoặc mới hơn bằng cách sử dụng function
`phpinfo()` hoặc chạy lệnh `php -v`. Bạn nên kiểm tra cả 2 cách do ở một vài cấu hình, bạn có thể có 2 phiên bản PHP được cài đặt: một cho dòng lệnh, và một cho web.

Sau đó, download script sau để kiểm tra cấu hình:

    http://sf-to.org/1.2/check.php

Lưu script này vào thư mục web root.

Chạy script từ dòng lệnh để kiểm tra cấu hình:

    $ php check_configuration.php

Nếu có vấn đề gì với cấu hình PHP hiện tại, đoạn script sẽ đưa ra gợi ý cách sửa.

Bạn cũng nên chạy script này từ trình duyệt và sửa các lỗi phát sinh, do PHP có thể có các file cấu hình `php.ini` riêng cho 2 môi trường, với các thiết lập khác nhau.

>**NOTE**
>Đừng quên xóa file script sau khi kiểm tra xong cấu hình.

-

>**NOTE**
>Nếu bạn chỉ muốn dùng thử symfony trong vài giờ, bạn có thể cài đặt
>symfony sandbox như mô tả trong [Phụ lục A](A-The-Sandbox). Nếu bạn muốn tạo
>một project thực sự hoặc muốn học về
>symfony, hãy đọc tiếp.
