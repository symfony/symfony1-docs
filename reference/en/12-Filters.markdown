The filters.yml Configuration File
==================================

The ~`filters.yml`~ configuration file describes the filter chain to be
executed for every request.

The main `filters.yml` configuration file for an application can be found in
the `apps/APP_NAME/config/` directory.

As discussed in the introduction, the `filters.yml` file benefits from the
[**configuration cascade mechanism**](#chapter_03_configuration_cascade), and
can include [**constants**](#chapter_03_constants).

The `filters.yml` configuration file contains a list of named filter
definitions:

    [yml]
    FILTER_1:
      # definition of filter 1

    FILTER_2:
      # definition of filter 2

    # ...

When the controller initializes the filter chain for a request, it reads the
`filters.yml` file and registers the filters by looking for the class name of
the filter (`class`) and the parameters (`param`) used to configure the filter
object:

    [yml]
    FILTER_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

The filters are executed in the same order as they appear in the configuration
file. As symfony executes the filters as a chain, the first registered filter
is executed first and last.

The `class` name should extend the `sfFilter` base class.

If the filter class cannot be autoloaded, a `file` path can be defined and
will be automatically included before the filter object is created:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

When you override the `filters.yml` file, you must keep all filters from the
inherited configuration file:

    [yml]
    rendering: ~
    security:  ~
    cache:     ~
    execution: ~

To remove a filter, you need to disable it by setting the `enabled` key to
`false`:

    [yml]
    FACTORY_NAME:
      enabled: false

There are two special name filters: `rendering` and `execution`. They are both
mandatory and are identified with the `type` parameter. The `rendering` filter
should always be the first registered filter and the `execution` filter
should be the last one:

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

    # ...

    execution:
      class:  sfExecutionFilter
      param:
        type: execution

>**NOTE**
>The `filters.yml` configuration file is cached as a PHP file; the
>process is automatically managed by the ~`sfFilterConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

<div class="pagebreak"></div>

Filters
-------

 * [`rendering`](#chapter_12_rendering)
 * [`security`](#chapter_12_security)
 * [`cache`](#chapter_12_cache)
 * [`execution`](#chapter_12_execution)

`rendering`
-----------

*Default configuration*:

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

The rendering filter is responsible for the output of the response to the
browser. As it should be the first filter registered, it is also the last one
to have a chance to manage the request.

`security`
----------

*Default configuration*:

    [yml]
     security:
       class: sfBasicSecurityFilter
       param:
         type: security

The security filter checks the security by calling the `getCredential()`
method of the action. Once the credential has been acquired, it verifies that
the user has the same credential by calling the `hasCredential()` method of
the user object.

The security filter must have a type of `security`.

The fine-grained configuration of the security filter is done via the
`security.yml` configuration [file](#chapter_08).

>**TIP**
>If the requested action is not configured as secure in `security.yml`, the
>security filter will not be executed.

`cache`
-------

*Default configuration*:

    [yml]
    cache:
      class: sfCacheFilter
      param:
        condition: %SF_CACHE%

The cache filter manages the caching of actions and pages. It is also
responsible for adding the needed HTTP cache headers to the response
(`Last-Modified`, `ETag`, `Cache-Control`, `Expires`, ...).

`execution`
-----------

*Default configuration*:

    [yml]
    execution:
      class:  sfExecutionFilter
      param:
        type: execution

The execution filter is at the center of the filter chain and does all action
and view execution.

The execution filter should be the last registered filter.
