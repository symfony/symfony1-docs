The routing.yml Configuration File
==================================

The `routing.yml` configuration file allows the definition of routes.

The main `routing.yml` configuration file for an application can be found in
the `apps/APP_NAME/config/` directory.

The `routing.yml` configuration file contains a list of named route
definitions:

    [yml]
    ROUTE_1:
      # definition of route 1

    ROUTE_2:
      # definition of route 2

    # ...

When a request comes in, the routing system tries to match a route to the
incoming URL. The first route that matches wins, so the order in which routes
are defined in the `routing.yml` configuration file is important.

When the `routing.yml` configuration file is read, each route is converted to
an object of class `class`:

    [yml]
    ROUTE_NAME:
      class: CLASS_NAME
      # configuration if the route

The `class` name should extend the `sfRoute` base class. If not provided, the
`sfRoute` base class is used as a fallback.

>**NOTE**
>The `routing.yml` configuration file is cached as a PHP file; the
>process is automatically managed by the ~`sfRoutingConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

<div class="pagebreak"></div>

Route Classes
-------------

 * [Main Configuration](#chapter_10_route_configuration)

   * [`class`](#chapter_10_sub_class)
   * [`options`](#chapter_10_sub_options)
   * [`param`](#chapter_10_sub_param)
   * [`params`](#chapter_10_sub_params)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`type`](#chapter_10_sub_type)
   * [`url`](#chapter_10_sub_url)

 * [`sfRoute`](#chapter_10_sfroute)
 * [`sfRequestRoute`](#chapter_10_sfrequestroute)

   * [`sf_method`](#chapter_10_sub_sf_method)

 * [`sfObjectRoute`](#chapter_10_sfobjectroute)

   * [`allow_empty`](#chapter_10_sub_allow_empty)
   * [`convert`](#chapter_10_sub_convert)
   * [`method`](#chapter_10_sub_method)
   * [`model`](#chapter_10_sub_model)
   * [`type`](#chapter_10_sub_type)

 * [`sfPropelRoute`](#chapter_10_sfpropelroute)

   * [`method_for_criteria`](#chapter_10_sub_method_for_criteria)

 * [`sfDoctrineRoute`](#chapter_10_sfdoctrineroute)

   * [`method_for_query`](#chapter_10_sub_method_for_query)

 * [`sfRouteCollection`](#chapter_10_sfroutecollection)
 * [`sfObjectRouteCollection`](#chapter_10_sfobjectroutecollection)

   * [`actions`](#chapter_10_sub_actions)
   * [`collection_actions`](#chapter_10_sub_collection_actions)
   * [`column`](#chapter_10_sub_column)
   * [`model`](#chapter_10_sub_model)
   * [`model_methods`](#chapter_10_sub_model_methods)
   * [`module`](#chapter_10_sub_module)
   * [`object_actions`](#chapter_10_sub_object_actions)
   * [`prefix_path`](#chapter_10_sub_prefix_path)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`route_class`](#chapter_10_sub_route_class)
   * [`segment_names`](#chapter_10_sub_segment_names)
   * [`with_show`](#chapter_10_sub_with_show)
   * [`with_wildcard_routes`](#chapter_10_sub_with_wildcard_routes)

 * [`sfPropelRouteCollection`](#chapter_10_sfpropelroutecollection)
 * [`sfDoctrineRouteCollection`](#chapter_10_sfdoctrineroutecollection)

<div class="pagebreak"></div>

Route Configuration
-------------------

The `routing.yml` configuration file supports several settings to further
configure the routes. These settings are used by the `sfRoutingConfigHandler`
class to convert each route to an object.

### ~`class`~

*Default*: `sfRoute` (or `sfRouteCollection` if `type` is `collection`, see below)

The `class` setting allows to change the route class to use for the route.

### ~`url`~

*Default*: `/`

The `url` setting is the pattern that must match an incoming URL for the route
to be used for the current request.

The pattern is made of segments:

 * variables (a word prefixed with a [colon `:`](#chapter_05_sub_variable_prefixes))
 * constants
 * a wildcard (`*`) to match a sequence of key/value pairs

Each segment must be separated by one of the pre-defined separator
([`/` or `.` by default](#chapter_05_sub_segment_separators)).

### ~`params`~

*Default*: An empty array

The `params` setting defines an array of parameters associated with the route.
They can be default values for variables contained in the `url`, or any other
variable relevant for this route.

### ~`param`~

*Default*: An empty array

This setting is equivalent to the `params` settings.

### ~`options`~

*Default*: An empty array

The `options` setting is an array of options to be passed to the route object
to further customize its behavior. The following sections describe the
available options for each route class.

### ~`requirements`~

*Default*: An empty array

The `requirements` settings is an array of requirements that must be satisfied
by the `url` variables. The keys are the url variables and the values are
regular expressions that the variable values must match.

>**TIP**
>The regular expression will be included in a another regular
>expression, and as such, you don't need to wrap them between
>separators, nor do you need to bound them with `^` or `$` to match the
>whole value.

### ~`type`~

*Default*: `null`

If set to `collection`, the route will be read as a route collection.

>**NOTE**
>This setting is automatically set to `collection` by the config handler
>class if the `class` name contains the word `Collection`. It means that
>most of the time, you do not need to use this setting.

~`sfRoute`~
-----------

All route classes extends the `sfRoute` base class, which provides the
required settings to configure a route.

~`sfRequestRoute`~
------------------

### ~`sf_method`~

*Default*: `get`

The `sf_method` option is to be used in the `requirements` array. It enforces
the HTTP request in the route matching process.

~`sfObjectRoute`~
-----------------

All the following options of `sfObjectRoute` must be used inside the `options`
setting of the `routing.yml` configuration file.

### ~`model`~

The `model` option is mandatory and is the name of the model class to be
associated with the current route.

### ~`type`~

The `type` option is mandatory and is the type of route you want for your
model; it can be either `object` or `list`. A route of type `object`
represents a single model object, and a route of type `list` represents a
collection of model objects.

### ~`method`~

The `method` option is mandatory. It is the method to call on the model class to
retrieve the object(s) associated with this route. This must be a static
method. The method is called with the parameters of the parsed route as an
argument.

### ~`allow_empty`~

*Default*: `true`

If the `allow_empty` option is set to `false`, the route will throw a 404
exception if no object is returned by the call to the `model` `method`.

### ~`convert`~

*Default*: `toParams`

The `convert` option is a method to call to convert a model object to an array
of parameters suitable for generating a route based on this model object. It
must returns an array with at least the required parameters of the route
pattern (as defined by the `url` setting).

~`sfPropelRoute`~
-----------------

### ~`method_for_criteria`~

*Default*: `doSelect` for collections, `doSelectOne` for single objects

The `method_for_criteria` option defines the method called on the model Peer
class to retrieve the object(s) associated with the current request. The
method is called with the parameters of the parsed route as an argument.

~`sfDoctrineRoute`~
-------------------

### ~`method_for_query`~

*Default*: none

The `method_for_query` option defines the method to call on the model to
retrieve the object(s) associated with the current request. The current query
object is passed as an argument.

If the option is not set, the query is just "executed" with the `execute()`
method.

~`sfRouteCollection`~
---------------------

The `sfRouteCollection` base class represents a collection of routes.

~`sfObjectRouteCollection`~
---------------------------

### ~`model`~

The `model` option is mandatory and is the name of the model class to be
associated with the current route.

### ~`actions`~

*Default*: `false`

The `actions` option defines an array of authorized actions for the route. The
actions must be a sub-set of all available actions: `list`, `new`, `create`,
`edit`, `update`, `delete`, and `show`.

If the option is set to `false`, the default, all actions will be available
except for the `show` one if the `with_show` option is set to `false` (see
below).

### ~`module`~

*Default*: The route name

The `module` option defines the module name.

### ~`prefix_path`~

*Default*: `/` followed by the route name

The `prefix_path` option defines a prefix to prepend to all `url` patterns. It
can be any valid pattern and can contain variables and several segments.

### ~`column`~

*Default*: `id`

The `column` option defines the column of the model to use as the unique
identifier for the model object.

### ~`with_show`~

*Default*: `true`

The `with_show` option is used when the `actions` option is set to `false` to
determine if the `show` action must be included in the list of authorized
actions for the route.

### ~`segment_names`~

*Default*: array('edit' => 'edit', 'new' => 'new'),

The `segment_names` defines the words to use in the `url` patterns for the
`edit` and `new` actions.

### ~`model_methods`~

*Default*: An empty array

The `model_methods` options defines the methods to call to retrieve the
object(s) from the model (see the `method` option of `sfObjectRoute`). This is
actually an array defining the `list` and the `object` methods:

    [yml]
    model_methods:
      list:   getObjects
      object: getObject

### ~`requirements`~

*Default*: `\d+` for the `column`

The `requirements` option defines an array of requirements to apply to the
route variables.

### ~`with_wildcard_routes`~

*Default*: `false`

The `with_wildcard_routes` option allows for any action to be accessed via two
wildcard routes: one for a single object, and another for object collections.

### ~`route_class`~

*Default*: `sfObjectRoute`

The `route_class` option can override the default route object used for the
collection.

### ~`collection_actions`~

*Default*: An empty array

The `collection_actions` options defines an array of additional actions
available for the collection routes. The keys are the action names and the
values are the valid methods for that action:

    [yml]
    articles:
      options:
        collection_actions: { filter: post, filterBis: [post, get] }
        # ...

### ~`object_actions`~

*Default*: An empty array

The `object_actions` options defines an associative array of additional
actions available for the object routes. The keys are the action names and the
values are the valid methods for that action:

    [yml]
    articles:
      options:
        object_actions: { publish: put, publishBis: [post, put] }
        # ...

~`sfPropelRouteCollection`~
---------------------------

The `sfPropelRouteCollection` route class extends the `sfRouteCollection`, and
changes the default route class to `sfPropelRoute` (see the `route_class`
option above).

~`sfDoctrineRouteCollection`~
-----------------------------

The `sfDoctrineRouteCollection` route class extends the `sfRouteCollection`,
and changes the default route class to `sfDoctrineRoute` (see the
`route_class` option above).
