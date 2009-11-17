Web Server Configuration
========================

The ugly Way
------------

In the previous chapters, you have created a directory that hosts the project.
If you have created it somewhere under the web root directory of your web
server, you can already access the project in a web browser.

Of course, as there is no configuration, it is very fast to set up, but try to
access the `config/databases.yml` file in your browser to understand the bad
consequences of such a lazy attitude. If the user knows that your website is
developed with symfony, he will have access to a lot of sensitive files.

**Never ever use this setup on a production server**, and read the next
section to learn how to configure your web server properly.

The secure Way
--------------

A good web practice is to put under the web root directory only the files that
need to be accessed by a web browser, like stylesheets, JavaScripts and
images. By default, we recommend to store these files under the `web/`
sub-directory of a symfony project.

If you have a look at this directory, you will find some sub-directories for
web assets (`css/` and `images/`) and the two front controller files. The
front controllers are the only PHP files that need to be under the web root
directory. All other PHP files can be hidden from the browser, which is a good
idea as far as security is concerned.

### Web Server Configuration

Now it is time to change your Apache configuration, to make the new project
accessible to the world.

Locate and open the `httpd.conf` configuration file and add the following
configuration at the end:

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
>The `/sf` alias gives you access to images and javascript files needed
>to properly display default symfony pages and the web debug toolbar.
>
>On Windows, you need to replace the `Alias` line with something like:
>
>     Alias /sf "c:\dev\sfproject\lib\vendor\symfony\data\web\sf"
>
>And `/home/sfproject/web` should be replaced with:
>
>     c:\dev\sfproject\web

This configuration makes Apache listen to port `8080` on your machine, so
the website will be accessible at the following URL:

    http://localhost:8080/

You can change `8080` to any number, but favour numbers greater than `1024` as
they do not require administrator rights.

>**SIDEBAR**
>Configure a dedicated Domain Name
>
>If you are an administrator on your machine, it is better to setup
>virtual hosts instead of adding a new port each time you start a new
>project. Instead of choosing a port and add a `Listen` statement,
>choose a domain name (for instance the real domain name with
>`.localhost` added at the end) and add a `ServerName` statement:
>
>     # This is the configuration for your project
>     <VirtualHost 127.0.0.1:80>
>       ServerName www.myproject.com.localhost
>       <!-- same configuration as before -->
>     </VirtualHost>
>
>The domain name `www.myproject.com.localhost` used in the Apache configuration
>has to be declared locally. If you run a Linux system, it has to be
>done in the `/etc/hosts` file. If you run Windows XP, this file is
>located in the `C:\WINDOWS\system32\drivers\etc\` directory.
>
>Add in the following line:
>
>     127.0.0.1 www.myproject.com.localhost

### Test the New Configuration

Restart Apache, and check that you now have access to the new application by
opening a browser and typing `http://localhost:8080/index.php/`, or
`http://www.myproject.com.localhost/index.php/` depending on the Apache configuration
you chose in the previous section.

![Congratulations](http://www.symfony-project.org/images/getting-started/1_4/congratulations.png)

>**TIP**
>If you have the Apache `mod_rewrite` module installed, you can remove
>the `index.php/` part of the URL. This is possible thanks to the
>rewriting rules configured in the `web/.htaccess` file.

You should also try to access the application in the development environment
(see the next section for more information about environments). Type in the
following URL:

    http://www.myproject.com.localhost/frontend_dev.php/

The web debug toolbar should show in the top right corner, including small
icons proving that your `sf/` alias configuration is correct.

![web debug toolbar](http://www.symfony-project.org/images/getting-started/1_4/web_debug_toolbar.png)

>**Note**
>The setup is a little different if you want to run symfony on an IIS server in
>a Windows environment. Find how to configure it in the
>[related tutorial](http://www.symfony-project.com/cookbook/1_0/web_server_iis).
