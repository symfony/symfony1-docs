Project Setup
=============

In symfony, **applications** sharing the same data model are regrouped into
**projects**. For most projects, you will have two different applications: a
frontend and a backend.

Project Creation
----------------

From the `sfproject/` directory, run the symfony `generate:project` task to
actually create the symfony project:

    $ php lib/vendor/symfony/data/bin/symfony generate:project PROJECT_NAME

On Windows:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project PROJECT_NAME

The `generate:project` task generates the default structure of directories and
files needed for a symfony project:

 | Directory   | Description
 | ----------- | ----------------------------------
 | `apps/`     | Hosts all project applications
 | `cache/`    | The files cached by the framework
 | `config/`   | The project configuration files
 | `lib/`      | The project libraries and classes
 | `log/`      | The framework log files
 | `plugins/`  | The installed plugins
 | `test/`     | The unit and functional test files
 | `web/`      | The web root directory (see below)

>**NOTE**
>Why does symfony generate so many files? One of the main benefits of using
>a full-stack framework is to standardize your developments. Thanks to
>symfony's default structure of files and directories, any developer with
>some symfony knowledge can take over the maintenance of any symfony project.
>In a matter of minutes, he will be able to dive into the code, fix bugs,
>and add new features.

The `generate:project` task has also created a `symfony` shortcut in the
project root directory to shorten the number of characters you have to write
when running a task.

So, from now on, instead of using the fully qualified path to the symfony
program, you can use the `symfony` shortcut.

Configuring the Database
------------------------

The symfony framework supports all [PDO](http://www.php.net/PDO)-supported
databases (MySQL, PostgreSQL, SQLite, Oracle, MSSQL, ...) out of the box. On
top of PDO, symfony comes bundled with two ORM tools: Propel and Doctrine.

When creating a new project, Doctrine is enabled by default. Configuring the
database used by Doctrine is as simple as using the `configure:database` task:

    $ php symfony configure:database "mysql:host=localhost;dbname=dbname" root mYsEcret

The `configure:database` task takes three arguments: the
[~PDO DSN~](http://www.php.net/manual/en/pdo.drivers.php), the username, and
the password to access the database. If you don't need a password to access
your database on the development server, just omit the third argument.

>**TIP**
>If you want to use Propel instead of Doctrine, add `--orm=Propel` when creating
>the project with the `generate:project` task. And if you don't want to use an
>ORM, just pass `--orm=none`.

Application Creation
--------------------

Now, create the frontend application by running the `generate:app` task:

    $ php symfony generate:app frontend

>**TIP**
>Because the symfony shortcut file is executable, Unix users can replace all
>occurrences of '`php symfony`' by '`./symfony`' from now on.
>
>On Windows you can copy the '`symfony.bat`' file to your project and use
>'`symfony`' instead of '`php symfony`':
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

Based on the application name given as an *argument*, the `generate:app` task
creates the default directory structure needed for the application under the
`apps/frontend/` directory:

 | Directory    | Description
 | ------------ | -------------------------------------
 | `config/`    | The application configuration files
 | `lib/`       | The application libraries and classes
 | `modules/`   | The application code (MVC)
 | `templates/` | The global template files

>**SIDEBAR**
>Security
>
>By default, the `generate:app` task has secured our application from the two
>most widespread vulnerabilities found on the web. That's right, symfony
>automatically takes ~security|Security~ measures on our behalf.
>
>To prevent ~XSS~ attacks, output escaping has been enabled; and to prevent
>~CSRF~ attacks, a random CSRF secret has been generated.
>
>Of course, you can tweak these settings thanks to the following *options*:
>
>  * `--escaping-strategy`: Enables or disables output escaping
>  * `--csrf-secret`: Enables session tokens in forms
>
>If you know nothing about
>[XSS](http://en.wikipedia.org/wiki/Cross-site_scripting) or
>[CSRF](http://en.wikipedia.org/wiki/CSRF), take the time to learn more these
>security vulnerabilities.

Directory Structure Rights
--------------------------

Before trying to access your newly created project, you need to set the write
permissions on the `cache/` and `log/` directories to the appropriate levels,
so that your web server can write to them:

    $ chmod 777 cache/ log/

>**SIDEBAR**
>Tips for People using a SCM Tool
>
>symfony only ever writes in two directories of a symfony project,
>`cache/` and `log/`. The content of these directories should be ignored
>by your SCM (by editing the `svn:ignore` property if you use Subversion
>for instance).
