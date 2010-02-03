Symfony Installation
====================

Initializing the Project Directory
----------------------------------

Before installing symfony, you first need to create a directory that will host
all the files related to your project:

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Or on Windows:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Windows users are advised to run symfony and to setup their new
>project in a path which contains no spaces.
>Avoid using the `Documents and Settings` directory, including anywhere
>under `My Documents`.

-

>**TIP**
>If you create the symfony project directory under the web root
>directory, you won't need to configure your web server.  Of course, for
>production environments, we strongly advise you to configure your web
>server as explained in the web server configuration section.

Choosing the Symfony Version
----------------------------

Now, you need to install symfony. As the symfony framework has several stable
versions, you need to choose the one you want to install by reading the
[installation page](http://www.symfony-project.org/installation) on the
symfony website.

This tutorial assumes you want to install symfony 1.4.

Choosing the Symfony Installation Location
-------------------------------------------

You can install symfony globally on your machine, or embed it into each of
your project. The latter is the recommended one as projects will then be
totally independent from each others. Upgrading your locally installed symfony
won't break some of your projects unexpectedly. It means you will be able to
have projects on different versions of symfony, and upgrade them one at a time
as you see fit.

As a best practice, many people install the symfony framework files in the
`lib/vendor` project directory. So, first, create this directory:

    $ mkdir -p lib/vendor

Installing Symfony
------------------

### Installing from an archive

The easiest way to install symfony is to download the archive for the version
you choose from the symfony website. Go to the installation page for the
version you have just chosen, symfony
[1.4](http://www.symfony-project.org/installation/1_4) for instance.

Under the "**Installing from an archive**" section, you will find the archive in `.tgz`
or in `.zip` format. Download the archive, put it under the freshly created
`lib/vendor/` directory, un-archive it, and rename the directory to `symfony`:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.0.tgz
    $ mv symfony-1.4.0 symfony
    $ rm symfony-1.4.0.tgz

Under Windows, unzipping the zip file can be achieved using Windows Explorer.
After you rename the directory to `symfony`, there should be a directory
structure similar to `c:\dev\sfproject\lib\vendor\symfony`.

### Installing from Subversion (recommended)

If you use Subversion, it is even better to use the `svn:externals` property
to embed symfony into your project in the `lib/vendor/` directory:

    $ svn pe svn:externals lib/vendor/

If everything goes well, this command will run your favorite editor to give
you the opportunity to configure the external Subversion sources.

>**TIP**
>On Windows, you can use tools like [TortoiseSVN](http://tortoisesvn.net/)
>to do everything without the need to use the console.

If you are conservative, tie your project to a specific release (a subversion
tag):

    symfony http://svn.symfony-project.com/tags/RELEASE_1_4_0

Whenever a new release comes out (as announced on the symfony
[blog](http://www.symfony-project.org/blog/)), you will need to change the URL
to the new version.

If you want to go the bleeding-edge route, use the 1.4 branch:

    symfony http://svn.symfony-project.com/branches/1.4/

Using the branch makes your project benefits from the bug fixes automatically
whenever you run a `svn update`.

### Installation Verification

Now that symfony is installed, check that everything is working by using the
symfony command line to display the symfony version (note the capital `V`):

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

On Windows:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

The `-V` option also displays the path to the symfony installation directory,
which is stored in `config/ProjectConfiguration.class.php`.

If the path to symfony is an absolute one (which should not be by default if
you follow the above instructions), change it so it reads like follows for
better portability:

    [php]
    // config/ProjectConfiguration.class.php
    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';

That way, you can move the project directory anywhere on your machine or
another one, and it will just work.

>**TIP**
>If you are curious about what this command line tool can do for you, type
>`symfony` to list the available options and tasks:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>On Windows:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>The symfony command line is the developer's best friend. It provides a lot of
>utilities that improve your productivity for day-to-day activities like
>cleaning the cache, generating code, and much more.
