Cấu hình Web Server
===================

Cách tồi
------------

Trong chương trước, chúng ta đã tạo một thư mục chứa project.
Nếu bạn tạo nó trong thư mục web root, bạn đã có thể truy cập project từ trình duyệt.

Đó là một cách nhanh chóng và không cần phải cấu hình gì cả, nhưng thử truy cập file `config/databases.yml` từ trình duyệt để hiểu hậu quả của sự lười biếng này. Nếu người dùng biết được website được phát triển với symfony, anh ta có thể truy cập nhiều file nhạy cảm.

**Không bao giờ sử dụng cách cài đặt này cho môi trường production server**, và đọc mục tiếp theo để biết cách cấu hình đúng cho web server của bạn.

Cách bảo mật
--------------

Chỉ nên đặt trong thư mục web root những file có thể truy cập bởi trình duyệt, như file stylesheet, JavaScript và image. Mặc định, những file này nằm trong thư mục `web/` của project symfony.

Thư mục này gồm các thư mục con `css/`, `images/` và 2 file front controller. Các file front controller này là các file php duy nhất nằm trong thư mục web root. Tất cả các file PHP đều nên để ẩn đối với trình duyệt, và đó là cách tốt để bảo mật ứng dụng.

### Cấu hình Web Server

Bây giờ hãy thay đổi cấu hình Apache để có thể truy cập project.

Mở file `httpd.conf` và thêm đoạn cấu hình sau vào cuối:

    # Be sure to only have this line once in your configuration
    NameVirtualHost 127.0.0.1:8080

    # This is the configuration for your project
    Listen 127.0.0.1:8080

    <VirtualHost 127.0.0.1:8080>
      DocumentRoot "/home/sfproject/web"
      DirectoryIndex index.php
      <Directory "/home/sfproject/web">
        AllowOverride All
        Allow from All
      </Directory>

      Alias /sf /home/sfproject/lib/vendor/symfony/data/web/sf
      <Directory "/home/sfproject/lib/vendor/symfony/data/web/sf">
        AllowOverride All
        Allow from All
      </Directory>
    </VirtualHost>

>**NOTE**
>Alias `/sf` cho phép bạn truy cập file ảnh và javascript cần thiết để
>hiển thị các trang mặc định của symfony và web debug toolbar.
>
>Ở Windows, bạn cần thay thế dòng `Alias` thành:
>
>     Alias /sf "c:\dev\sfproject\lib\vendor\symfony\data\web\sf"
>
>Và `/home/sfproject/web` có thể thay thế bởi:
>
>     c:\dev\sfproject\web

Với cấu hình này, Apache sẽ lắng nghe cổng `8080`, do đó website có thể truy cập theo đường dẫn sau:

    http://localhost:8080/

Bạn có thể đổi cổng `8080` thành bất kì số nào lớn hơn `1024`.

>**SIDEBAR**
>Cấu hình một tên miền riêng
>
>Nếu bạn có quyền administrator, tốt hơn nên thiết lập một
>virtual hosts thay vì thêm một cổng mới mỗi khi bạn tạo một
>project. Thay vì chọn một cổng và thêm lệnh `Listen`,
>hãy chọn một tên mền và thêm lệnh `ServerName`:
>
>     # This is the configuration for your project
>     <VirtualHost 127.0.0.1:80>
>       ServerName sfproject.localhost
>       <!-- same configuration as before -->
>     </VirtualHost>
>
>Tên miền `sfproject.localhost` được dùng trong cấu hình Apache
>để xác định local. Nếu bạn dùng Linux, nó được cấu hình trong file
>`/etc/hosts`. Nếu bạn sử dụng Windows XP, file này nằm trong thư mục
>`C:\WINDOWS\system32\drivers\etc\`.
>
>Thêm vào dòng sau:
>
>     127.0.0.1 sfproject.localhost

### Kiểm tra cấu hình mới

Khởi động lại Apache, và kiểm tra xem bạn có thể truy cập ứng dụng chưa bằng cách mở trình duyệt và gõ `http://localhost:8080/index.php/`, hoặc
`http://sfproject.localhost/index.php/` tùy vào cấu hình bạn đã chọn ở mục trước.

![Congratulations](http://www.symfony-project.org/images/jobeet/1_2/01/congratulations.png)

>**TIP**
>Nếu module Apache `mod_rewrite` đã được cài đặt, bạn có thể bỏ
>đường dẫn `index.php/` trên URL. Đó là nhờ cấu hình
>rewriting rule trong file `web/.htaccess`.

Bạn cũng có thể thử truy cập ứng dụng trong môi trường phát triển
(xem phần tiếp theo để hiểu rõ hơn về các môi trường). Gõ đường dẫn sau:

    http://sfproject.localhost/frontend_dev.php/

Web debug toolbar sẽ hiển thị ở góc phải, bao gồm các biểu tượng nhỏ nếu `sf/` được cấu hình đúng.

![web debug toolbar](http://www.symfony-project.org/images/jobeet/1_2/01/web_debug_toolbar.png)

>**Note**
>Việc cài đặt có khác chút nếu bạn chạy symfony ở server IIS trong môi trường
>Windows. Bạn có thể đọc hướng dẫn cấu hình
>[tại đây](http://www.symfony-project.com/cookbook/1_0/web_server_iis).
