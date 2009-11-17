The cache.yml Configuration File
================================

The ~`cache.yml`~ configuration file describes the cache configuration for the
view layer. This configuration file is only active if the
[`cache`](#chapter_04_sub_cache) setting is enabled in `settings.yml`.

>**TIP**
>The configuration of the class used for caching and
>its associated configuration is to be done in the
>[`view_cache_manager`](#chapter_05_view_cache_manager) and
>[`view_cache`](#chapter_05_view_cache) sections of the `factories.yml`
>configuration file.

When an application is created, symfony generates a default `cache.yml` file
in the application `config/` directory which describes the cache for the whole
application (under the `default` key). By default, the cache is globally set
to `false`:

    [yml]
    default:
      enabled:     false
      with_layout: false
      lifetime:    86400

>**TIP**
>As the `enabled` setting is set to `false` by default, you need to
>enable the cache selectively. You can also work the other way around:
>enable the cache globally and then, disable it on specific pages that
>cannot be cached. Your approach should depend on what represents less work
>for your application.

As discussed in the introduction, the `cache.yml` file benefits from
the [**configuration cascade mechanism**](#chapter_03_configuration_cascade),
and can include [**constants**](#chapter_03_constants).

>**NOTE**
>The `cache.yml` configuration file is cached as a PHP file; the
>process is automatically managed by the ~`sfCacheConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

The default application configuration can be overridden for a module by
creating a `cache.yml` file in the `config/` directory of the module. The main
keys are action names without the `execute` prefix (`index` for the
`executeIndex` method for instance). A partial or component can also be cached
by using its name prefixed with an underscore (`_`).

To determine if an action is cached or not, symfony looks for the information
in the following order:

  * a configuration for the specific action, partial, or component in the
    module configuration file, if it exists;

  * a configuration for the whole module in the module configuration file, if
    it exists (under the `all` key);

  * the default application configuration (under the `default` key).

>**CAUTION**
>An incoming request with `GET` parameters in the query string or
>submitted with the `POST`, `PUT`, or `DELETE` method will never be
>cached by symfony, regardless of the configuration.

~`enabled`~
-----------

*Default*: `false`

The `enabled` setting enables or disables the cache for the current scope.

~`with_layout`~
---------------

*Default*: `false`

The `with_layout` setting determines whether the cache must be for the entire
page (`true`), or for the action only (`false`).

>**NOTE**
>The `with_layout` option is not taken into account for partial and
>component caching as they cannot be decorated by a layout.

~`lifetime`~
------------

*Default*: `86400`

The `lifetime` setting defines the server-side lifetime of the cache in
seconds (`86400` seconds equals one day).

~`client_lifetime`~
-------------------

*Default*: Same value as the `lifetime` one

The `client_lifetime` setting defines the client-side lifetime of the cache in
seconds.

This setting is used to automatically set the `Expires` header and the
`max-cache` cache control variable, unless a `Last-Modified` or `Expires`
header has already been set.

You can disable client-side caching by setting the value to `0`.

~`contextual`~
--------------

*Default*: `false`

The `contextual` setting determines if the cache depends on the current page
context or not. The setting is therefore only meaningful when used for
partials and components.

When a partial output is different depending on the template in which it is
included, the partial is said to be contextual, and the `contextual` setting
must be set to `true`. By default, the setting is set to `false`, which means
that the output for partials and components are always the same, wherever it
is included.

>**NOTE**
>The cache is still obviously different for a different set of parameters.
