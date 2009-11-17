Prerequisites
=============

Before installing symfony, you need to check that your computer has everything
installed and configured correctly. Take the time to conscientiously read this
chapter and follow all the steps required to check your configuration, as it
may save your day further down the road.

Third-Party Software
--------------------

First of all, you need to check that your computer has a friendly working
environment for web development. At a minimum, you need a web server (Apache,
for instance), a database engine (MySQL, PostgreSQL, SQLite, or any
[PDO](http://www.php.net/PDO)-compatible database engine), and PHP 5.2.4 or
later.

Command Line Interface
----------------------

The symfony framework comes bundled with a command line tool that automates a
lot of work for you. If you are a Unix-like OS user, you will feel right at
home. If you run a Windows system, it will also work fine, but you will just
have to type a few commands at the `cmd` prompt.

>**Note**
>Unix shell commands can come in handy in a Windows environment.
>If you would like to use tools like `tar`, `gzip` or `grep` on Windows, you
>can install [Cygwin](http://cygwin.com/).
>The adventurous may also like to try Microsoft's
>[Windows Services for Unix](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx).

PHP Configuration
-----------------

As PHP configurations can vary a lot from one OS to another, or even between
different Linux distributions, you need to check that your PHP configuration
meets the symfony minimum requirements.

First, ensure that you have PHP 5.2.4 at a minimum installed by using the
`phpinfo()` built-in function or by running `php -v` on the command line. Be
aware that on some configurations, you might have two different PHP versions
installed: one for the command line, and another for the web.

Then, download the symfony configuration checker script at the following URL:

    http://sf-to.org/1.4/check.php

Save the script somewhere under your current web root directory.

Launch the configuration checker script from the command line:

    $ php check_configuration.php

If there is a problem with your PHP configuration, the output of the command
will give you hints on what to fix and how to fix it.

You should also execute the checker from a browser and fix the issues it might
discover. That's because PHP can have a distinct `php.ini` configuration file
for these two environments, with different settings.

>**NOTE**
>Don't forget to remove the file from your web root directory
>afterwards.

-

>**NOTE**
>If your goal is to give symfony a try for a few hours, you can install
>the symfony sandbox as described in [Appendix A](A-The-Sandbox). If
>you want to bootstrap a real world project or want to learn more about
>symfony, keep reading.
