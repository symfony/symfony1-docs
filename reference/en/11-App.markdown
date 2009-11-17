The app.yml Configuration File
==============================

The symfony framework provides a built-in configuration file for application
specific settings, the `app.yml` configuration file.

This YAML file can contain any setting you want that makes sense for your
specific application. In the code, these settings are available through the
global `sfConfig` class, and keys are prefixed with the `app_` string:

    [php]
    sfConfig::get('app_active_days');

All settings are prefixed by `app_` because the `sfConfig` class also provides
access to [symfony settings](#chapter_03_sub_configuration_settings) and
[project directories](#chapter_03_sub_directories).

As discussed in the introduction, the `app.yml` file is
[**environment-aware**](#chapter_03_environment_awareness), and benefits from
the [**configuration cascade mechanism**](#chapter_03_configuration_cascade).

The `app.yml` configuration file is a great place to define settings that
change based on the environment (an API key for instance), or settings that
can evolve over time (an email address for instance). It is also the best
place to define settings that need to be changed by someone who does not
necessarily understand symfony or PHP (a system administrator for instance).

>**TIP**
>Refrain from using `app.yml` to bundle application logic.

-

>**NOTE**
>The `app.yml` configuration file is cached as a PHP file; the process
>is automatically managed by the ~`sfDefineEnvironmentConfigHandler`~
>[class](#chapter_14_config_handlers_yml).
