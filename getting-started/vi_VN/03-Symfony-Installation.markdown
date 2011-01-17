Cài đặt Symfony
====================

### Thư mục Project

Trước khi cài đặt symfony, bạn cần tạo thư mục để chứa toàn bộ file của dự án:

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Ở Windows:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Với người dùng Windows, không nên đặt
>project trong các thư mục có tên chứa dấu cách, như thư mục
>`Documents and Settings`, hay `My Documents`.

-

>**TIP**
>Nếu bạn tạo project trong thư mục web root, bạn sẽ không phải cấu hình web server.  Tuy nhiên, với môi trường production, chúng tôi khuyên bạn cấu hình như mô tả trong mục cấu hình web server.

Chọn phiên bản Symfony
----------------------

Bây giờ, bạn cần cài đặt symfony. Do symfony có vài phiên bản chính thức, bạn cần chọn một phiên bản bạn muốn cài đặt bằng cách đọc giới thiệu ở [trang cài đặt](http://www.symfony-project.org/installation).

Hướng dẫn này dành cho bạn muốn cài đặt symfony 1.4.

Chọn thư mục để cài đặt Symfony
-------------------------------------------

Bạn có thể cài đặt symfony  dùng chung cho các project hoặc nhúng nó vào mỗi project.
Cách sau được khuyên dùng để các project không phụ thuộc vào nhau.
Nâng cấp symfony ở project này sẽ không ảnh hưởng đến project khác. Điều này cũng có nghĩa là bạn có thể có vài project với các phiên bản khác nhau.

Thông thường, mọi người sẽ cài đặt symfony vào thư mục `lib/vendor`. Đầu tiên, hãy tạo thư mục này:

    $ mkdir -p lib/vendor

### Cài đặt Symfony

### Cài đặt từ file nén

Cách dễ nhất để cài đặt symfony là download file nén từ trang chủ. Chuyển đến trang cài đặt của phiên bản bạn đã chọn, ví dụ
[symfony 1.4](http://www.symfony-project.org/installation/1_4).

Dưới mục "**Download as an Archive**", bạn sẽ thấy file nén ở định dạng
`.tgz` hoặc `.zip`. Tải file nén về, đặt nó vào trong thư mục `lib/vendor/` và giải nén:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.8.tgz
    $ mv symfony-1.4.8 symfony
    $ rm symfony-1.4.8.tgz

Đổi tên thư mục thành `symfony`
`c:\dev\sfproject\lib\vendor\symfony`.

### Cài đặt từ Subversion (khuyên dùng)

Nếu bạn sử dụng Subversion, tốt hơn nên dùng lệnh `svn:externals`
để nhúng symfony vào trong thư mục `lib/vendor/` của project:

    $ svn pe svn:externals lib/vendor/

Nếu mọi thứ chạy đúng, lệnh này sẽ mở một editor để bạn có thể cấu hình nguồn Subversion này.

>**TIP**
>Ở Windows, bạn có thể sử dụng công cụ như [TortoiseSVN](http://tortoisesvn.net/)
>để thực hiện mà không cần dùng dòng lệnh

Nếu bạn là người thận trọng, hãy sử dụng một phiên bản cụ thể (một subversion
tag):

    svn checkout http://svn.symfony-project.com/tags/RELEASE_1_4_8 symfony

Mỗi khi có một phiên bản mới (được thông báo ở [blog](http://www.symfony-project.org/blog/)), bạn sẽ cần đổi URL để cập nhật phiên bản mới.

Bạn cũng có thể dùng branch 1.4:

    svn checkout http://svn.symfony-project.com/branches/1.4/ symfony

Sử dụng branch bạn sẽ cập nhật được các bản vá lỗi khi chạy lệnh `svn update`.

### Xác thực cài đặt

Kiểm tra xem symfony đã cài đặt đúng chưa bằng lệnh `symfony` để hiển thị phiên bản của symfony (chữ `V` viết hoa):

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Ở Windows:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

Option `-V` cũng hiển thị đường dẫn của thư mục cài đặt symfony,
đường dẫn này được lưu trong file `config/ProjectConfiguration.class.php`:

    [php]
    // config/ProjectConfiguration.class.php
    require_once '/Users/fabien/work/symfony/dev/1.2/lib/autoload/sfCoreAutoload.class.php';

Để thuận tiện, bạn nên đổi đường dẫn tuyệt đối sang đường dẫn tương đối:

    [php]
    // config/ProjectConfiguration.class.php
    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';

Với cách này, bạn có thể di chuyển project đến bất kì đâu, nó vẫn làm việc.

>**TIP**
>Nếu bạn tò mò về những lệnh có thể thực hiện, gõ
>`symfony` để hiện danh sách các tác vụ và lựa chọn:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Ở Windows:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>Lệnh của symfony rất tiện dụng. Nó cung cấp rất nhiều công cụ
>phục vụ cho công việc hằng ngày của bạn như
>xóa cache, tạo sẵn code, ...
