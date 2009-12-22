Khởi tạo Project
=============

Ở symfony, các **application** chia sẻ cùng một data model và được nhóm lại trong một
**project**. Trong phần lớn các project, chúng ta thường có 2 application: frontend và backend.

Tạo Project
-----------

Ở thư mục `sfproject/`, chạy tác vụ symfony `generate:project` để tạo project symfony:

    $ php lib/vendor/symfony/data/bin/symfony generate:project PROJECT_NAME

Ở Windows:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project PROJECT_NAME

Tác vụ `generate:project` tạo ra cấu trúc thư mục và các file cần thiết cho một project symfony:

 | Thư mục     | Mô tả
 | ----------- | ----------------------------------
 | `apps/`     | Chứa các application của project
 | `cache/`    | Chứa các file cache của framework
 | `config/`   | Chứa các file cấu hình
 | `data/`     | -
 | `lib/`      | Chứa các thư viện dùng trong project
 | `log/`      | Chứa file log của framework
 | `plugins/`  | Chứa các plugin đã cài đặt
 | `test/`     | Chứa các file unit và functional test
 | `web/`      | Thư mục web root (xem bên dưới)

>**NOTE**
>Tại sao symfony tạo ra quá nhiều file như vậy? Một trong những lợi ích của việc sử dụng full-stack framework là chuẩn hóa việc phát triển. Nhờ có cấu trúc
>file và thư mục mặc định của symfony, bất kì lập trình viên nào có kiến thức về
>symfony cũng có thể maintenance project symfony.
>Trong vài phút, anh ta đã có thể nắm bắt được code, sửa lỗi,
>và thêm tính năng mới.

Tác vụ `generate:project` cũng tạo một shortcut `symfony` ở thư mục gốc của project để giảm số kí tự bạn phải gõ khi chạy một lệnh.

Do đó, từ bây giờ, thay vì sử dụng đầy đủ đường dẫn đến symfony, bạn chỉ cần dùng shortcut `symfony`.

Cấu hình cơ sở dữ liệu
----------------------

Symfony framework hỗ trợ tất cả các sơ sở dữ liệu hỗ trợ [PDO]((http://www.php.net/PDO)) (MySQL, PostgreSQL,
SQLite, Oracle, MSSQL, ...). Symfony chứa sẵn 2 ORM: Propel và Doctrine.

Khi bạn tạo project, Doctrine được sử dụng mặc định. Cấu hình cơ sở dữ liệu được thực hiện thông qua tác vụ `configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=dbname" root mYsEcret

Tác vụ `configure:database` nhận 3 tham số: [~PDO DSN~](http://www.php.net/manual/en/pdo.drivers.php), username, và password để truy cập database. Nếu bạn không cần password để truy cập database, bạn có thể bỏ qua tham số này.

>**TIP**
>Nếu bạn muốn sử dụng Propel thay cho Doctrine, thêm tham số `--orm=Propel` khi tạo project
>trong lệnh `generate:project`. Và nếu bạn không muốn sử dụng ORM,
>hãy dùng `--orm=none`.

Tạo Application
---------------

Tạo application frontend bằng cách chạy tác vụ `generate:app`:

    $ php symfony generate:app frontend

>**TIP**
>Do dùng file shortcut symfony, nên người dùng Unix có thể thay thế cụm
>'`php symfony`' bằng '`./symfony`'.
>
>Ở Windows bạn có thể copy file '`symfony.bat`' tới project của bạn và sử dụng
>'`symfony`' thay vì '`php symfony`':
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

Dựa vào tên application được cung cấp dưới dạng một *tham số*, tác vụ `generate:app` tạo cấu trúc thư mục mặc định cần thiết trong thư mục `apps/frontend/`:

 | Thư mục      | Mô tả
 | ------------ | -------------------------------------
 | `config/`    | Chứa file cấu hình của application
 | `lib/`       | Chứa thư viện dùng trong application
 | `modules/`   | Mã nguồn application (MVC)
 | `templates/` | File template toàn cục

>**SIDEBAR**
>Security
>
>Mặc định, lệnh `generate:app` bảo mật ứng dụng của bạn khỏi 2 tấn công phổ biến trên web.
>
>Để tránh tấn công ~XSS~, output escaping đã được enable; và để tránh tấn công
>~CSRF~, một chuỗi CSRF bất kì đã được tạo.
>
>Tất nhiên bạn cũng có thể thay đổi các thiết lập này với 2 *options*:
>
>  * `--escaping-strategy`: Cho phép output escaping để tránh tấn công XSS
>  * `--csrf-secret`: Cho phép session tokens trong forms để tránh tấn công CSRF
>
>Bằng cách cung cấp 2 tham số này cho tác vụ, bạn đã bảo vệ ứng dụng của mình
>khỏi những tấn công phổ biến trên web.
>
>Nếu bạn chưa biết về
>[XSS](http://en.wikipedia.org/wiki/Cross-site_scripting) và
>[CSRF](http://en.wikipedia.org/wiki/CSRF), hãy dành chút thời gian để tìm hiểu về chúng.

Phân quyền cho thư mục
----------------------

Trước khi thử truy cập project vừa tạo, bạn cần thiết lập quyền khi cho thư mục
`cache/` và `log/`, để web server có thể ghi vào đó:

    $ chmod 777 cache/ log/

>**SIDEBAR**
>Thủ thuật cho người sử dụng công cụ SCM
>
>Symfony chỉ ghi lên 2 thư mục
>`cache/` và `log/`. Nội dung của những thư mục này có thể được bỏ qua
>bởi SCM (bằng cách sửa property `svn:ignore` nếu bạn dùng Subversion).
