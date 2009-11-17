The Environments
================

If you have a look at the `web/` directory, you will find two PHP files:
`index.php` and `frontend_dev.php`. These files are called **front
controllers**; all requests to the application are made through them. But why
do we have two front controllers for each application?

Both files point to the same application but for different **environments**.
When you develop an application, except if you develop directly on the
production server, you need several environments:

  * The **development environment**: This is the environment used by **web
    developers** when they work on the application to add new features, fix
    bugs, ...

  * The **test environment**: This environment is used to automatically test
    the application.

  * The **staging environment**: This environment is used by the **customer**
    to test the application and report bugs or missing features.

  * The **production environment**: This is the environment **end users**
    interact with.

What makes an environment unique? In the development environment for instance,
the application needs to log all the details of a request to ease
debugging, but the cache system must be disabled as all changes made to the
code must be taken into account right away. So, the development environment
must be optimized for the developer. The best example is certainly when an
exception occurs. To help the developer debug the issue faster, symfony
displays the exception with all the information it has about the current
request right into the browser:

![An exception in the dev environment](http://www.symfony-project.org/images/getting-started/1_3/exception_dev.png)

But on the production environment, the cache layer must be activated and, of
course, the application must display customized error messages instead of raw
exceptions. So, the production environment must be optimized for performance
and the user experience.

![An exception in the prod environment](http://www.symfony-project.org/images/getting-started/1_3/exception_prod.png)

>**TIP**
>If you open the front controller files, you will see that their content is
>the same except for the environment setting:
>
>     [php]
>     // web/index.php
>     <?php
>
>     require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
>
>     $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
>     sfContext::createInstance($configuration)->dispatch();

The web debug toolbar is also a great example of the usage of environment. It
is present on all pages in the development environment and gives you access to
a lot of information by clicking on the different tabs: the current
application configuration, the logs for the current request, the SQL
statements executed on the database engine, memory information, and time
information.
