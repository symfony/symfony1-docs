Appendix A - The Sandbox
========================

If your goal is to give symfony a try for a few hours, keep reading this
chapter as we will show you the fastest way to get you started. If you want to
bootstrap a real world project, you can safely skip this chapter, and
[jump](04-Symfony-Installation#chapter_04) to the next one right away.

The fastest way to experiment with symfony is to install the symfony sandbox.
The sandbox is a dead-easy-to-install pre-packaged symfony project, already
configured with some sensible defaults. It is a great way to practice using
symfony without the hassle of a proper installation that respects the web best
practices.

>**CAUTION**
>As the sandbox is pre-configured to use SQLite as a database
>engine, you need to check that your PHP supports SQLite (see the
>[Prerequisites](02-Prerequisites#chapter_02) chapter). You can also
>read the [Configuring the Database](05-Project-Setup#chapter_05_sub_configuring_the_database)
>section to learn how to change the database used by the sandbox.

You can download the symfony sandbox in `.tgz` or `.zip` format from the
symfony [installation page](http://www.symfony-project.org/installation/1_4)
or at the following URLs:

    http://www.symfony-project.org/get/sf_sandbox_1_4.tgz

    http://www.symfony-project.org/get/sf_sandbox_1_4.zip

Un-archive the files somewhere under your web root directory, and you are
done. Your symfony project is now accessible by requesting the `web/index.php`
script from a browser.

>**CAUTION**
>Having all the symfony files under the web root directory is fine for
>testing symfony on your local computer, but is a really bad idea for
>a production server as it potentially makes all the internals of your
>application visible to end users.

You can now finish your installation by reading the
[Web Server Configuration](06-Web-Server-Configuration#chapter_06)
and the [Environments](07-Environments#chapter_07) chapters.

>**NOTE**
>As a sandbox is just a normal symfony project where some tasks have
>been executed for you and some configuration changed, it is quite
>easy to use it as a starting point for a new project. However, keep in mind
>that you will probably need to adapt the configuration; for instance
>changing the security related settings (see the configuration of XSS
>and CSRF later in this tutorial).
