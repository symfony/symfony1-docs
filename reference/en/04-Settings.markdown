The settings.yml Configuration File
===================================

Most aspects of symfony can be configured either via a configuration file
written in YAML, or with plain PHP. In this section, the main configuration
file for an application, `settings.yml`, will be described.

The main `settings.yml` configuration file for an application can be found in
the `apps/APP_NAME/config/` directory.

As discussed in the introduction, the `settings.yml` file is
[**environment-aware**](#chapter_03_environment_awareness), and benefits from
the [**configuration cascade mechanism**](#chapter_03_configuration_cascade).

Each environment section has two sub-sections: `.actions` and `.settings`. All
configuration directives go under the `.settings` sub-section, except for the
default actions to be rendered for some common pages.

>**NOTE**
>The `settings.yml` configuration file is cached as a PHP file; the process is
>automatically managed by the ~`sfDefineEnvironmentConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

<div class="pagebreak"></div>

Settings
--------

  * `.actions`

    * [`error_404`](#chapter_04_sub_error_404)
    * [`login`](#chapter_04_sub_login)
    * [`secure`](#chapter_04_sub_secure)
    * [`module_disabled`](#chapter_04_sub_module_disabled)

  * `.settings`

    * [`cache`](#chapter_04_sub_cache)
    * [`charset`](#chapter_04_sub_charset)
    * [`check_lock`](#chapter_04_sub_check_lock)
    * [`compressed`](#chapter_04_sub_compressed)
    * [`csrf_secret`](#chapter_04_sub_csrf_secret)
    * [`default_culture`](#chapter_04_sub_default_culture)
    * [`default_timezone`](#chapter_04_sub_default_timezone)
    * [`enabled_modules`](#chapter_04_sub_enabled_modules)
    * [`error_reporting`](#chapter_04_sub_error_reporting)
    * [`escaping_strategy`](#chapter_04_sub_escaping_strategy)
    * [`escaping_method`](#chapter_04_sub_escaping_method)
    * [`etag`](#chapter_04_sub_etag)
    * [`i18n`](#chapter_04_sub_i18n)
    * [`lazy_cache_key`](#chapter_04_sub_lazy_cache_key)
    * [`file_link_format`](#chapter_04_sub_file_link_format)
    * [`logging_enabled`](#chapter_04_sub_logging_enabled)
    * [`no_script_name`](#chapter_04_sub_no_script_name)
    * [`standard_helpers`](#chapter_04_sub_standard_helpers)
    * [`use_database`](#chapter_04_sub_use_database)
    * [`web_debug`](#chapter_04_sub_web_debug)
    * [`web_debug_web_dir`](#chapter_04_sub_web_debug_web_dir)

<div class="pagebreak"></div>

The `.actions` Sub-Section
--------------------------

*Default configuration*:

    [yml]
    default:
      .actions:
        error_404_module:       default
        error_404_action:       error404

        login_module:           default
        login_action:           login

        secure_module:          default
        secure_action:          secure

        module_disabled_module: default
        module_disabled_action: disabled

The `.actions` sub-section defines the action to execute when common pages
must be rendered. Each definition has two components: one for the module
(suffixed by `_module`), and one for the action (suffixed by `_action`).

### ~`error_404`~

The `error_404` action is executed when a 404 page must be rendered.

### ~`login`~

The `login` action is executed when a non-authenticated user tries to access a
secure page.

### ~`secure`~

The `secure` action is executed when a user doesn't have the required
credentials.

### ~`module_disabled`~

The `module_disabled` action is executed when a user requests a disabled
module.

The `.settings` Sub-Section
---------------------------

The `.settings` sub-section is where the framework configuration occurs. The
paragraphs below describe all possible settings and are roughly ordered by
importance.

All settings defined in the `.settings` section are available anywhere in the
code by using the `sfConfig` object and prefixing the setting with `sf_`. For
instance, to get the value of the `charset` setting, use:

    [php]
    sfConfig::get('sf_charset');

### ~`escaping_strategy`~

*Default*: `true`

The `escaping_strategy` setting is a Boolean setting that determines if the
output escaper sub-framework is enabled. When enabled, all variables made
available in the templates are automatically escaped by calling the helper
function defined by the `escaping_method` setting (see below).

Be careful that the `escaping_method` is the default helper used by symfony,
but this can be overridden on a case by case basis, when outputting a variable
in a JavaScript script tag for example.

The output escaper sub-framework uses the `charset` setting for the escaping.

It is highly recommended to leave the default value to `true`.

>**TIP**
>This settings can be set when you create an application with the
>`generate:app` task by using the `--escaping-strategy` option.

### ~`escaping_method`~

*Default*: `ESC_SPECIALCHARS`

The `escaping_method` defines the default function to use for escaping
variables in templates (see the `escaping_strategy` setting above).

You can choose one of the built-in values: ~`ESC_SPECIALCHARS`~, ~`ESC_RAW`~,
~`ESC_ENTITIES`~, ~`ESC_JS`~, ~`ESC_JS_NO_ENTITIES`~, and
~`ESC_SPECIALCHARS`~, or create your own function.

Most of the time, the default value is fine. The `ESC_ENTITIES` helper can
also be used, especially if you are only working with English or European
languages.

### ~`csrf_secret`~

*Default*: a randomly generated secret

The `csrf_secret` is a unique secret for your application. If not set to
`false`, it enables CSRF protection for all forms defined with the form
framework. This settings is also used by the `link_to()` helper when it needs
to convert a link to a form (to simulate a `DELETE` HTTP method for example).

It is highly recommended to change the default value to a unique secret of
your choice.

>**TIP**
>This settings can be set when you create an application with the
>`generate:app` task by using the `--csrf-secret` option.

### ~`charset`~

*Default*: `utf-8`

The `charset` setting is the charset that will be used everywhere in the
framework: from the response `Content-Type` header, to the output escaping
feature.

Most of the time, the default is fine.

>**WARNING**
>This setting is used in many different places in the framework,
>and so its value is cached in several places. After changing it,
>the configuration cache must be cleared, even in the development
>environment.

### ~`enabled_modules`~

*Default*: `[default]`

The `enabled_modules` is an array of module names to enable for this
application. Modules defined in plugins or in the symfony core are not enabled
by default, and must be listed in this setting to be accessible.

Adding a module is as simple as appending it to the list (the order of the
modules do not matter):

    [yml]
    enabled_modules: [default, sfGuardAuth]

The `default` module defined in the framework contains all the default actions
set in the `.actions` sub-section of `settings.yml`. It is recommended that
you customize all of them, and then remove the `default` module from this
setting.

### ~`default_timezone`~

*Default*: none

The `default_timezone` setting defines the default timezone used by PHP. It
can be any [timezone](http://www.php.net/manual/en/class.datetimezone.php)
recognized by PHP.

>**NOTE**
>If you don't define a timezone, you are advised to define one in the
>`php.ini` file. If not, symfony will try to guess the best timezone by
>calling the
>[`date_default_timezone_get()`](http://www.php.net/date_default_timezone_get)
>PHP function.

### ~`cache`~

*Default*: `false`

The `cache` setting enables or disables template caching.

>**TIP**
>The general configuration of the cache system is done in
>the [`view_cache_manager`](#chapter_05_view_cache_manager) and
>[`view_cache`](#chapter_05_view_cache) sections of the `factories.yml`
>configuration file. The fined-grained configuration is done in
>the [`cache.yml`](#chapter_09) configuration file.

### ~`etag`~

*Default*: `true` by default except for the `dev` and `test` environments

The `etag` setting enables or disables the automatic generation of `ETag` HTTP
headers. The ETag generated by symfony is a simple md5 of the response
content.

### ~`i18n`~

*Default*: `false`

The `i18n` setting is a Boolean that enables or disables the i18n
sub-framework. If your application is internationalized, set it to `true`.

>**TIP**
>The general configuration of the i18n system is to be done in the
>[`i18n`](#chapter_05_i18n) section of the `factories.yml` configuration
>file.

### ~`default_culture`~

*Default*: `en`

The `default_culture` setting defines the default culture used by the i18n
sub-framework. It can be any valid culture.

### ~`standard_helpers`~

*Default*: `[Partial, Cache]`

The `standard_helpers` setting is an array of helper groups to load for all
templates (name of the group helper without the `Helper` suffix).

### ~`no_script_name`~

*Default*: `true` for the `prod` environment of the first application created,
`false` for all others

The `no_script_name` setting determines whether the front controller script
name is prepended to generated URLs or not. By default, it is set to `true` by
the `generate:app` task for the `prod` environment of the first application
created.

Obviously, only one application and environment can have this setting set to
`true` if all front controllers are in the same directory (`web/`). If you want
more than one application with `no_script_name` set to `true`, move the
corresponding front controller(s) under a sub-directory of the web root
directory.

### ~`lazy_cache_key`~

*Default*: `true` for new projects, `false` for upgraded projects

When enabled, the `lazy_cache_key` setting delays the creation of a cache key
until after checking whether an action or partial is cacheable. This can
result in a big performance improvement, depending on your usage of template
partials.

### ~`file_link_format`~

*Default*: none

In the debug message, file paths are clickable links if the
`sf_file_link_format` or if the `xdebug.file_link_format` PHP configuration
value is set.

For example, if you want to open files in TextMate, you can use the following
value:

    [yml]
    txmt://open?url=file://%f&line=%l

The `%f` placeholder will be replaced with file's absolute path and the `%l`
placeholder will be replaced with the line number.

### ~`logging_enabled`~

*Default*: `true` for all environments except `prod`

The `logging_enabled` setting enables the logging sub-framework. Setting it to
`false` bypasses the logging mechanism completely and provides a small
performance gain.

>**TIP**
>The fined-grained configuration of the logging is to be done in the
>`factories.yml` configuration file.

### ~`web_debug`~

*Default*: `false` for all environments except `dev`

The `web_debug` setting enables the web debug toolbar. The web debug toolbar
is injected into a page when the response content type is HTML.

### ~`error_reporting`~

*Default*:

  * `prod`:  E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR
  * `dev`:   E_ALL | E_STRICT
  * `test`:  (E_ALL | E_STRICT) ^ E_NOTICE
  * default: E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR

The `error_reporting` setting controls the level of PHP error reporting (to be
displayed in the browser and written to the logs).

>**TIP**
>The PHP website has some information about how to use
>[bitwise operators](http://www.php.net/language.operators.bitwise).

The default configuration is the most sensible one, and should not be altered.

>**NOTE**
>The display of errors in the browser is automatically disabled for
>front controllers that have `debug` disabled, which is the case by default
>for the `prod` environment.

### ~`compressed`~

*Default*: `false`

The `compressed` setting enables native PHP response compression. If set to
`true`, symfony will use [`ob_gzhandler`](http://www.php.net/ob_gzhandler) as a
callback function for `ob_start()`.

It is recommended to keep it to `false`, and use the native compression
mechanism of your web server instead.

### ~`use_database`~

*Default*: `true`

The `use_database` determines if the application uses a database or not.

### ~`check_lock`~

*Default*: `false`

The `check_lock` setting enables or disables the application lock system
triggered by some tasks like `cache:clear` and `project:disable`.

If set to `true`, all requests to disabled applications are automatically
redirected to the symfony core `lib/exception/data/unavailable.php` page.

>**TIP**
>You can override the default unavailable template by adding a
>`config/unavailable.php` file to your project or application.

### ~`web_debug_web_dir`~

*Default*: `/sf/sf_web_debug`

The `web_debug_web_dir` sets the web path to the web debug toolbar assets
(images, stylesheets, and JavaScript files).
