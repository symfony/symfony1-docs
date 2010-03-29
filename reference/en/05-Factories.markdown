The factories.yml Configuration File
====================================

Factories are core objects needed by the framework during the life of any
request. They are configured in the `factories.yml` configuration file and
always accessible via the `sfContext` object:

    [php]
    // get the user factory
    sfContext::getInstance()->getUser();

The main `factories.yml` configuration file for an application can be found in
the `apps/APP_NAME/config/` directory.

As discussed in the introduction, the `factories.yml` file is
[**environment-aware**](#chapter_03_environment_awareness), benefits from
the [**configuration cascade mechanism**](#chapter_03_configuration_cascade),
and can include [**constants**](#chapter_03_constants).

The `factories.yml` configuration file contains a list of named factories:

    [yml]
    FACTORY_1:
      # definition of factory 1

    FACTORY_2:
      # definition of factory 2

    # ...

The supported factory names are: `controller`, `logger`, `i18n`, `request`,
`response`, `routing`, `storage`, `user`, `view_cache`, and
`view_cache_manager`.

When the `sfContext` initializes the factories, it reads the `factories.yml`
file for the class name of the factory (`class`) and the parameters (`param`)
used to configure the factory object:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

Being able to customize the factories means that you can use a custom class
for symfony core objects instead of the default one. You can also change the
default behavior of these classes by customizing the parameters sent to them.

If the factory class cannot be autoloaded, a `file` path can be defined and
will be automatically included before the factory is created:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>The `factories.yml` configuration file is cached as a PHP file; the
>process is automatically managed by the ~`sfFactoryConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

<div class="pagebreak"></div>

Factories
---------

 * [`mailer`](#chapter_05_mailer)

  * [`charset`](#chapter_05_sub_charset)
  * [`delivery_address`](#chapter_05_sub_delivery_address)
  * [`delivery_strategy`](#chapter_05_sub_delivery_strategy)
  * [`spool_arguments`](#chapter_05_sub_spool_arguments)
  * [`spool_class`](#chapter_05_sub_spool_class)
  * [`transport`](#chapter_05_sub_transport)

 * [`request`](#chapter_05_request)

   * [`formats`](#chapter_05_sub_formats)
   * [`path_info_array`](#chapter_05_sub_path_info_array)
   * [`path_info_key`](#chapter_05_sub_path_info_key)
   * [`relative_url_root`](#chapter_05_sub_relative_url_root)

 * [`response`](#chapter_05_response)

   * [`charset`](#chapter_05_sub_charset)
   * [`http_protocol`](#chapter_05_sub_http_protocol)
   * [`send_http_headers`](#chapter_05_sub_send_http_headers)

 * [`user`](#chapter_05_user)

   * [`default_culture`](#chapter_05_sub_default_culture)
   * [`timeout`](#chapter_05_sub_timeout)
   * [`use_flash`](#chapter_05_sub_use_flash)

 * [`storage`](#chapter_05_storage)

   * [`auto_start`](#chapter_05_sub_auto_start)
   * [`database`](#chapter_05_sub_database_storage_specific_options)
   * [`db_table`](#chapter_05_sub_database_storage_specific_options)
   * [`db_id_col`](#chapter_05_sub_database_storage_specific_options)
   * [`db_data_col`](#chapter_05_sub_database_storage_specific_options)
   * [`db_time_col`](#chapter_05_sub_database_storage_specific_options)
   * [`session_cache_limiter`](#chapter_05_sub_session_cache_limiter)
   * [`session_cookie_domain`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_httponly`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_lifetime`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_path`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_secure`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_name`](#chapter_05_sub_session_name)

 * [`view_cache_manager`](#chapter_05_view_cache_manager)

   * [`cache_key_use_vary_headers`](#chapter_05_sub_cache_key_use_vary_headers)
   * [`cache_key_use_host_name`](#chapter_05_sub_cache_key_use_host_name)

 * [`view_cache`](#chapter_05_view_cache)
 * [`i18n`](#chapter_05_i18n)

   * [`cache`](#chapter_05_sub_cache)
   * [`debug`](#chapter_05_sub_debug)
   * [`source`](#chapter_05_sub_source)
   * [`untranslated_prefix`](#chapter_05_sub_untranslated_prefix)
   * [`untranslated_suffix`](#chapter_05_sub_untranslated_suffix)

 * [`routing`](#chapter_05_routing)

   * [`cache`](#chapter_05_sub_cache)
   * [`extra_parameters_as_query_string`](#chapter_05_sub_extra_parameters_as_query_string)
   * [`generate_shortest_url`](#chapter_05_sub_generate_shortest_url)
   * [`lazy_routes_deserialize`](#chapter_05_sub_lazy_routes_deserialize)
   * [`lookup_cache_dedicated_keys`](#chapter_05_sub_lookup_cache_dedicated_keys)
   * [`load_configuration`](#chapter_05_sub_load_configuration)
   * [`segment_separators`](#chapter_05_sub_segment_separators)
   * [`suffix`](#chapter_05_sub_suffix)
   * [`variable_prefixes`](#chapter_05_sub_variable_prefixes)

 * [`logger`](#chapter_05_logger)

   * [`level`](#chapter_05_sub_level)
   * [`loggers`](#chapter_05_sub_loggers)

 * [`controller`](#chapter_05_controller)

<div class="pagebreak"></div>

`mailer`
--------

*sfContext Accessor*: `$context->getMailer()`

*Default configuration*:

    [yml]
    mailer:
      class: sfMailer
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        delivery_strategy: realtime
        transport:
          class: Swift_SmtpTransport
          param:
            host:       localhost
            port:       25
            encryption: ~
            username:   ~
            password:   ~

*Default configuration for the `test` environment*:

    [yml]
    mailer:
      param:
        delivery_strategy: none

*Default configuration for the `dev` environment*:

    [yml]
    mailer:
      param:
        delivery_strategy: none

### ~`charset`~

The `charset` option defines the charset to use for the mail messages. By
default, it uses the `charset` setting from `settings.yml`.

### ~`delivery_strategy`~

The `delivery_strategy` option defines how email messages are delivered by the
mailer. Four strategies are available by default, which should suit all the
common needs:

 * `realtime`:       Messages are sent in realtime.

 * `single_address`: Messages are sent to a single address.

 * `spool`:          Messages are stored in a queue.

 * `none`:           Messages are simply ignored.

### ~`delivery_address`~

The `delivery_address` option defines the recipient of all message when the
`delivery_strategy` is set to `single_address`.

### ~`spool_class`~

The `spool_class` option defines the spool class to use when the
`delivery_strategy` is set to `spool`:

  * ~`Swift_FileSpool`~: Messages are stored on the filesystem.

  * ~`Swift_DoctrineSpool`~: Messages are stored in a Doctrine model.

  * ~`Swift_PropelSpool`~: Messages are stored in a Propel model.

>**NOTE**
>When the spool is instantiated, the ~`spool_arguments`~ option is used as the
>constructor arguments.

### ~`spool_arguments`~

The `spool_arguments` option defines the constructor arguments of the spool.
Here are the options available for the built-in queues classes:

 * `Swift_FileSpool`:

    * The absolute path of the queue directory (messages are stored in
      this directory)

 * `Swift_DoctrineSpool`:

    * The Doctrine model to use to store the messages (`MailMessage` by
      default)

    * The column name to use for message storage (`message` by default)

    * The method to call to retrieve the messages to send (optional).

 * `Swift_PropelSpool`:

    * The Propel model to use to store the messages (`MailMessage` by default)

    * The column name to use for message storage (`message` by default)

    * The method to call to retrieve the messages to send (optional). It
      receives the current Criteria as an argument.

The configuration below shows a typical configuration for a Doctrine spool:

    [yml]
    # configuration in factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

### ~`transport`~

The `transport` option defines the transport to use to actually send email
messages.

The `class` setting can be any class that implements from `Swift_Transport`,
and three are provided by default:

  * ~`Swift_SmtpTransport`~: Uses a SMTP server to send messages.

  * ~`Swift_SendmailTransport`~: Uses `sendmail` to send messages.

  * ~`Swift_MailTransport`~: Uses the native PHP `mail()` function to send
    messages.

  * ~`Swift_NullTransport`~: Disables the transport altogether (useful with the
    `none` strategy to bypass the connection to the mail server).

You can further configure the transport by setting the `param` setting. The
["Transport Types"](http://swiftmailer.org/docs/transport-types) section of
the Swift Mailer official documentation describes all you need to know about
the built-in transport classes and their different parameters.

`request`
---------

*sfContext Accessor*: `$context->getRequest()`

*Default configuration*:

    [yml]
    request:
      class: sfWebRequest
      param:
        logging:           %SF_LOGGING_ENABLED%
        path_info_array:   SERVER
        path_info_key:     PATH_INFO
        relative_url_root: ~
        formats:
          txt:  text/plain
          js:   [application/javascript, application/x-javascript, text/javascript]
          css:  text/css
          json: [application/json, application/x-json]
          xml:  [text/xml, application/xml, application/x-xml]
          rdf:  application/rdf+xml
          atom: application/atom+xml

### ~`path_info_array`~

The `path_info_array` option defines the global PHP array that will be used to
retrieve information. On some configurations you may want to change the
default `SERVER` value to `ENV`.

### ~`path_info_key`~

The `path_info_key` option defines the key under which the `PATH_INFO`
information can be found.

If you use ~IIS~ with a rewriting module like `IIFR` or `ISAPI`, you may need
to change this value to `HTTP_X_REWRITE_URL`.

### ~`formats`~

The `formats` option defines an array of file extensions and their
corresponding `Content-Type`s. It is used by the framework to automatically
manage the `Content-Type` of the response, based on the request URI extension.

### ~`relative_url_root`~

The `relative_url_root` option defines the part of the URL before the front
controller. Most of the time, this is automatically detected by the framework
and does not need to be changed.

`response`
----------

*sfContext Accessor*: `$context->getResponse()`

*Default configuration*:

    [yml]
    response:
      class: sfWebResponse
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        send_http_headers: true

*Default configuration for the `test` environment*:

    [yml]
    response:
      class: sfWebResponse
      param:
        send_http_headers: false

### ~`send_http_headers`~

The `send_http_headers` option specifies whether the response should send HTTP
response headers along with the response content. This setting is mostly
useful for testing, as headers are sent with the `header()` PHP function which
sends warnings if you try to send headers after some output.

### ~`charset`~

The `charset` option defines the charset to use for the response. By default,
it uses the `charset` setting from `settings.yml`, which is what you want most
of the time.

### ~`http_protocol`~

The `http_protocol` option defines the HTTP protocol version to use for the
response. By default, it checks the `$_SERVER['SERVER_PROTOCOL']` value if
available or defaults to `HTTP/1.0`.

`user`
------

*sfContext Accessor*: `$context->getUser()`

*Default configuration*:

    [yml]
    user:
      class: myUser
      param:
        timeout:         1800
        logging:         %SF_LOGGING_ENABLED%
        use_flash:       true
        default_culture: %SF_DEFAULT_CULTURE%

>**NOTE**
>By default, the `myUser` class inherits from `sfBasicSecurityUser`,
>which can be configured in the [`security.yml`](#chapter_08)
>configuration file.

### ~`timeout`~

The `timeout` option defines the timeout for user authentication. It is not
related to the session timeout. The default setting automatically
unauthenticates a user after 30 minutes of inactivity.

This setting is only used by user classes that inherit from the
`sfBasicSecurityUser` base class, which is the case of the generated `myUser`
class.

>**NOTE**
>To avoid unexpected behavior, the user class automatically forces the maximum
>lifetime for the session garbage collector (`session.gc_maxlifetime`)
>to be greater than the timeout.

### ~`use_flash`~

The `use_flash` option enables or disables the flash component.

### ~`default_culture`~

The `default_culture` option defines the default culture to use for a user who
comes to the site for the first time. By default, it uses the
`default_culture` setting from `settings.yml`, which is what you want most of
the time.

>**CAUTION**
>If you change the ~`default_culture`~ setting in `factories.yml` or
>`settings.yml`, you need to clear your cookies in your browser to check
>the result.

`storage`
---------

The storage factory is used by the user factory to persist user data between
HTTP requests.

*sfContext Accessor*: `$context->getStorage()`

*Default configuration*:

    [yml]
    storage:
      class: sfSessionStorage
      param:
        session_name: symfony

*Default configuration for the `test` environment*:

    [yml]
    storage:
      class: sfSessionTestStorage
      param:
        session_path: %SF_TEST_CACHE_DIR%/sessions

### ~`auto_start`~

The `auto_start` option enables or disables the session auto-starting feature
of PHP (via the `session_start()` function).

### ~`session_name`~

The `session_name` option defines the name of the cookie used by symfony to
store the user session. By default, the name is `symfony`, which means that
all your applications share the same cookie (and as such the corresponding
authentication and authorizations).

### `session_set_cookie_params()` parameters

The `storage` factory calls the
[`session_set_cookie_params()`](http://www.php.net/session_set_cookie_params)
function with the value of the following options:

 * ~`session_cookie_lifetime`~: Lifetime of the session cookie, defined in
                                seconds.
 * ~`session_cookie_path`~:   Path on the domain where the cookie will work.
                              Use a single slash (`/`) for all paths on the
                              domain.
 * ~`session_cookie_domain`~: Cookie domain, for example `www.php.net`. To
                              make cookies visible on all subdomains then the
                              domain must be prefixed with a dot like `.php.net`.
 * ~`session_cookie_secure`~: If `true` cookie will only be sent over secure
                              connections.
 * ~`session_cookie_httponly`~: If set to `true` then PHP will attempt to send the
                                `httponly` flag when setting the session cookie.

>**NOTE**
>The description of each option comes from the `session_set_cookie_params()`
>function description on the PHP website

### ~`session_cache_limiter`~

If the `session_cache_limiter` option is set, PHP's
[`session_cache_limiter()`](http://www.php.net/session_cache_limiter)
function is called and the option value is passed as an argument.

### Database Storage-specific Options

When using a storage that inherits from the `sfDatabaseSessionStorage` class,
several additional options are available:

 * ~`database`~:     The database name (required)
 * ~`db_table`~:     The table name (required)
 * ~`db_id_col`~:    The primary key column name (`sess_id` by default)
 * ~`db_data_col`~:  The data column name (`sess_data` by default)
 * ~`db_time_col`~:  The time column name (`sess_time` by default)

`view_cache_manager`
--------------------

*sfContext Accessor*: `$context->getViewCacheManager()`

*Default configuration*:

    [yml]
    view_cache_manager:
      class: sfViewCacheManager
      param:
        cache_key_use_vary_headers: true
        cache_key_use_host_name:    true

>**CAUTION**
>This factory is only created if the [`cache`](#chapter_04_sub_cache)
>setting is set to `true`.

Most configuration of this factory is done via the `view_cache` factory, which
defines the underlying cache object used by the view cache manager.

### ~`cache_key_use_vary_headers`~

The `cache_key_use_vary_headers` option specifies if the cache keys should
include the vary headers part. In practice, it says if the page cache should
be HTTP header dependent, as specified in `vary` cache parameter (default
value: `true`).

### ~`cache_key_use_host_name`~

The `cache_key_use_host_name` option specifies if the cache keys should
include the host name part. In practice, it says if page cache should be
hostname dependent (default value: `true`).

`view_cache`
------------

*sfContext Accessor*: none (used directly by the `view_cache_manager` factory)

*Default configuration*:

    [yml]
    view_cache:
      class: sfFileCache
      param:
        automatic_cleaning_factor: 0
        cache_dir:                 %SF_TEMPLATE_CACHE_DIR%
        lifetime:                  86400
        prefix:                    %SF_APP_DIR%/template

>**CAUTION**
>This factory is only defined if the [`cache`](#chapter_04_sub_cache)
>setting is set to `true`.

The `view_cache` factory defines a cache class that must inherit from
`sfCache` (see the Cache section for more information).

`i18n`
------

*sfContext Accessor*: `$context->getI18N()`

*Default configuration*:

    [yml]
    i18n:
      class: sfI18N
      param:
        source:               XLIFF
        debug:                false
        untranslated_prefix:  "[T]"
        untranslated_suffix:  "[/T]"
        cache:
          class: sfFileCache
          param:
            automatic_cleaning_factor: 0
            cache_dir:                 %SF_I18N_CACHE_DIR%
            lifetime:                  31556926
            prefix:                    %SF_APP_DIR%/i18n

>**CAUTION**
>This factory is only defined if the [`i18n`](#chapter_04_sub_i18n)
>setting is set to `true`.

### ~`source`~

The `source` option defines the container type for translations.

*Built-in containers*: `XLIFF`, `SQLite`, `MySQL`, and `gettext`.

### ~`debug`~

The `debug` option sets the debugging mode. If set to `true`, un-translated
messages are decorated with a prefix and a suffix (see below).

### ~`untranslated_prefix`~

The `untranslated_prefix` defines a prefix to used for un-translated messages.

### ~`untranslated_suffix`~

The `untranslated_suffix` defines a suffix to used for un-translated messages.

### ~`cache`~

The `cache` option defines a anonymous cache factory to be used for caching
i18n data (see the Cache section for more information).

`routing`
---------

*sfContext Accessor*: `$context->getRouting()`

*Default configuration*:

    [yml]
    routing:
      class: sfPatternRouting
      param:
        load_configuration:               true
        suffix:                           ''
        default_module:                   default
        default_action:                   index
        debug:                            %SF_DEBUG%
        logging:                          %SF_LOGGING_ENABLED%
        generate_shortest_url:            false
        extra_parameters_as_query_string: false
        cache:                            ~

### ~`variable_prefixes`~

*Default*: `:`

The `variable_prefixes` option defines the list of characters that starts a
variable name in a route pattern.

### ~`segment_separators`~

*Default*: `/` and `.`

The `segment_separators` option defines the list of route segment separators.
Most of the time, you don't want to override this option for the whole
routing, but for specific routes.

### ~`generate_shortest_url`~

*Default*: `true` for new projects, `false` for upgraded projects

If set to `true`, the `generate_shortest_url` option will tell the routing
system to generate the shortest route possible. Set it to `false` if you want
your routes to be backward compatible with symfony 1.0 and 1.1.

### ~`extra_parameters_as_query_string`~

*Default*: `true` for new projects, `false` for upgraded projects

When some parameters are not used in the generation of a route, the
`extra_parameters_as_query_string` allows those extra parameters to be
converted to a query string. Set it to `false` to fallback to the behavior of
symfony 1.0 or 1.1. In those versions, the extra parameters were just ignored
by the routing system.

### ~`cache`~

*Default*: none

The `cache` option defines an anonymous cache factory to be used for caching
routing configuration and data (see the Cache section for more information).

### ~`suffix`~

*Default*: none

The default suffix to use for all routes. This option is deprecated and is not
useful anymore.

### ~`load_configuration`~

*Default*: `true`

The `load_configuration` option defines whether the `routing.yml` files must
be automatically loaded and parsed. Set it to `false` if you want to use the
routing system of symfony outside of a symfony project.

### ~`lazy_routes_deserialize`~

*Default*: `false`

If set to `true`, the `lazy_routes_deserialize` setting enables lazy
unserialization of the routing cache. It can improve the performance of your
applications if you have a large number of routes and if most matching routes
are among the first ones. It is strongly advised to test the setting before
deploying to production, as it can harm your performance in certain
circumstances.

### ~`lookup_cache_dedicated_keys`~

*Default*: `false`

The `lookup_cache_dedicated_keys` setting determines how the routing cache is
constructed. When set to `false`, the cache is stored as one big value; when
set to `true`, each route has its own cache store. This setting is a
performance optimization setting.

As a rule of thumb, setting this to `false` is better when using a file-based
cache class (`sfFileCache` for instance), and setting it to `true` is better
when using a memory-based cache class (`sfAPCCache` for instance).

`logger`
--------

*sfContext Accessor*: `$context->getLogger()`

*Default configuration*:

    [yml]
    logger:
      class: sfAggregateLogger
      param:
        level: debug
        loggers:
          sf_web_debug:
            class: sfWebDebugLogger
            param:
              level: debug
              condition:       %SF_WEB_DEBUG%
              xdebug_logging:  true
              web_debug_class: sfWebDebug
          sf_file_debug:
            class: sfFileLogger
            param:
              level: debug
              file: %SF_LOG_DIR%/%SF_APP%_%SF_ENVIRONMENT%.log

*Default configuration for the `prod` environment*:

    [yml]
    logger:
      class:   sfNoLogger
      param:
        level:   err
        loggers: ~

If you don't use the `sfAggregateLogger`, don't forget to specify a `null`
value for the `loggers` parameter.

>**CAUTION**
>This factory is always defined, but the logging only occurs if the
>`logging_enabled` setting is set to `true`.

### ~`level`~

The `level` option defines the level of the logger.

*Possible values*: `EMERG`, `ALERT`, `CRIT`, `ERR`, `WARNING`, `NOTICE`,
`INFO`, or `DEBUG`.

### ~`loggers`~

The `loggers` option defines a list of loggers to use. The list is an array of
anonymous logger factories.

*Built-in logger classes*: `sfConsoleLogger`, `sfFileLogger`, `sfNoLogger`,
`sfStreamLogger`, and `sfVarLogger`.

`controller`
------------

*sfContext Accessor*: `$context->getController()`

*Default configuration*:

    [yml]
    controller:
      class: sfFrontWebController

Anonymous Cache Factories
-------------------------

Several factories (`view_cache`, `i18n`, and `routing`) can take advantage of
a cache object if defined in their configuration. The configuration of the
cache object is similar for all factories. The `cache` key defines an
anonymous cache factory. Like any other factory, it takes a `class` and a
`param` entries. The `param` entry can take any option available for the given
cache class.

The `prefix` option is the most important one as it allows to share or
separate a cache between different environments/applications/projects.

*Built-in cache classes*: `sfAPCCache`, `sfEAcceleratorCache`, `sfFileCache`,
`sfMemcacheCache`, `sfNoCache`, `sfSQLiteCache`, and `sfXCacheCache`.
