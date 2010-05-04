Events
======

The symfony core components are decoupled thanks to an `sfEventDispatcher`
object. The event dispatcher manages the communication between core
components.

Any object can notify an event to the dispatcher, and any other object can
connect to the dispatcher to listen to a specific event.

An event is just a name composed of a namespace and a name separated by a dot
(`.`).

Usage
-----

You can notify an event by first creating an event object:

    [php]
    $event = new sfEvent($this, 'user.change_culture', array('culture' => $culture));

And notify it:

    $dispatcher->notify($event);

The `sfEvent` constructor takes three arguments:

  * The "subject" of the event (most of the time, this is the object notifying
    the event, but it can also be `null`)
  * The event name
  * An array of parameters to pass to the listeners

To listen for an event, connect to that event name:

    [php]
    $dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));

The `connect` method takes two arguments:

  * The event name
  * A PHP callable to call when the event is notified

Here is an implementation example of a listener:

    [php]
    public function listenToChangeCultureEvent(sfEvent $event)
    {
      // change the message format object with the new culture
      $this->setCulture($event['culture']);
    }

The listener gets the event as the first argument. The event object has
several methods to get event information:

  * `getSubject()`: Gets the subject object attached to the event
  * `getParameters()`: Returns the event parameters

The event object can also be accessed as an array to get its parameters.

Event Types
-----------

Events can be triggered by three different methods:

 * `notify()`
 * `notifyUntil()`
 * `filter()`

### ~`notify`~

The `notify()` method notifies all listeners. The listeners cannot return a
value and all listeners are guaranteed to be executed.

### ~`notifyUntil`~

The `notifyUntil()` method notifies all listeners until one stops the chain by
returning a `true` value.

The listener that stops the chain may also call the `setReturnValue()` method.

The notifier can check if a listener has processed the event by calling the
`isProcessed()` method:

    [php]
    if ($event->isProcessed())
    {
      // ...
    }

### ~`filter`~

The `filter()` method notifies all listeners that they can filter the given
value, passed as a second argument by the notifier, and retrieved by the
listener callable as the second argument. All listeners are passed the value
and they must return the filtered value. All listeners are guaranteed to be
executed.

The notifier can get the filtered value by calling the `getReturnValue()`
method:

    [php]
    $ret = $event->getReturnValue();

<div class="pagebreak"></div>

Events
------

 * [`application`](#chapter_15_application)
   * [`application.log`](#chapter_15_sub_application_log)
   * [`application.throw_exception`](#chapter_15_sub_application_throw_exception)
 * [`command`](#chapter_15_command)
   * [`command.log`](#chapter_15_sub_command_log)
   * [`command.pre_command`](#chapter_15_sub_command_pre_command)
   * [`command.post_command`](#chapter_15_sub_command_post_command)
   * [`command.filter_options`](#chapter_15_sub_command_filter_options)
 * [`configuration`](#chapter_15_configuration)
   * [`configuration.method_not_found`](#chapter_15_sub_configuration_method_not_found)
 * [`component`](#chapter_15_component)
   * [`component.method_not_found`](#chapter_15_sub_component_method_not_found)
 * [`context`](#chapter_15_context)
   * [`context.load_factories`](#chapter_15_sub_context_load_factories)
   * [`context.method_not_found`](#chapter_15_sub_context_method_not_found)
 * [`controller`](#chapter_15_controller)
   * [`controller.change_action`](#chapter_15_sub_controller_change_action)
   * [`controller.method_not_found`](#chapter_15_sub_controller_method_not_found)
   * [`controller.page_not_found`](#chapter_15_sub_controller_page_not_found)
 * [`debug`](#chapter_15_debug)
   * [`debug.web.load_panels`](#chapter_15_sub_debug_view_load_panels)
   * [`debug.web.view.filter_parameter_html`](#chapter_15_sub_debug_web_view_filter_parameter_html)
 * [`doctrine`](#chapter_15_doctrine)
   * [`doctrine.configure`](#chapter_15_sub_doctrine_configure)
   * [`doctrine.filter_model_builder_options`](#chapter_15_sub_doctrine_filter_model_builder_options)
   * [`doctrine.filter_cli_config`](#chapter_15_sub_doctrine_filter_cli_config)
   * [`doctrine.configure_connection`](#chapter_15_sub_doctrine_configure_connection)
   * [`doctrine.admin.delete_object`](#chapter_15_sub_doctrine_delete_object)
   * [`doctrine.admin.save_object`](#chapter_15_sub_doctrine_save_object)
   * [`doctrine.admin.build_query`](#chapter_15_sub_doctrine_build_query)
   * [`doctrine.admin.pre_execute`](#chapter_15_sub_doctrine_pre_execute)
 * [`form`](#chapter_15_form)
   * [`form.post_configure`](#chapter_15_sub_form_post_configure)
   * [`form.filter_values`](#chapter_15_sub_form_filter_values)
   * [`form.validation_error`](#chapter_15_sub_form_validation_error)
   * [`form.method_not_found`](#chapter_15_sub_form_method_not_found)
 * [`mailer`](#chapter_15_mailer)
   * [`mailer.configure`](#chapter_15_sub_mailer_configure)
 * [`plugin`](#chapter_15_plugin)
   * [`plugin.pre_install`](#chapter_15_sub_plugin_pre_install)
   * [`plugin.post_install`](#chapter_15_sub_plugin_post_install)
   * [`plugin.pre_uninstall`](#chapter_15_sub_plugin_pre_uninstall)
   * [`plugin.post_uninstall`](#chapter_15_sub_plugin_post_uninstall)
 * [`request`](#chapter_15_request)
   * [`request.filter_parameters`](#chapter_15_sub_request_filter_parameters)
   * [`request.method_not_found`](#chapter_15_sub_request_method_not_found)
 * [`response`](#chapter_15_response)
   * [`response.method_not_found`](#chapter_15_sub_response_method_not_found)
   * [`response.filter_content`](#chapter_15_sub_response_filter_content)
 * [`routing`](#chapter_15_routing)
   * [`routing.load_configuration`](#chapter_15_sub_routing_load_configuration)
 * [`task`](#chapter_15_task)
   * [`task.cache.clear`](#chapter_15_sub_task_cache_clear)
 * [`template`](#chapter_15_template)
   * [`template.filter_parameters`](#chapter_15_sub_template_filter_parameters)
 * [`user`](#chapter_15_user)
   * [`user.change_culture`](#chapter_15_sub_user_change_culture)
   * [`user.method_not_found`](#chapter_15_sub_user_method_not_found)
   * [`user.change_authentication`](#chapter_15_sub_user_change_authentication)
 * [`view`](#chapter_15_view)
   * [`view.configure_format`](#chapter_15_sub_view_configure_format)
   * [`view.method_not_found`](#chapter_15_sub_view_method_not_found)
 * [`view.cache`](#chapter_15_view_cache)
   * [`view.cache.filter_content`](#chapter_15_sub_view_cache_filter_content)

<div class="pagebreak"></div>

`application`
-------------

### ~`application.log`~

*Notify method*: `notify`

*Default notifiers*: lot of classes

| Parameter  | Description
| ---------- | -----------
| `priority` | The priority level (`sfLogger::EMERG`, `sfLogger::ALERT`, `sfLogger::CRIT`, `sfLogger::ERR`, `sfLogger::WARNING`, `sfLogger::NOTICE`, `sfLogger::INFO`, or `sfLogger::DEBUG`)

The `application.log` event is the mechanism used by symfony to do the logging
for web request (see the logger factory). The event is notified by most
symfony core components.

### ~`application.throw_exception`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfException`

The `application.throw_exception` event is notified when an uncaught exception
is thrown during the handling of a request.

You can listen to this event to do something special whenever an uncaught
exception is thrown( like sending an email, or logging the error). You can
also override the default exception management mechanism of symfony by
processing the event.

`command`
---------

### ~`command.log`~

*Notify method*: `notify`

*Default notifiers*: `sfCommand*` classes

| Parameter  | Description
| ---------- | -----------
| `priority` | The priority level (`sfLogger::EMERG`, `sfLogger::ALERT`, `sfLogger::CRIT`, `sfLogger::ERR`, `sfLogger::WARNING`, `sfLogger::NOTICE`, `sfLogger::INFO`, or `sfLogger::DEBUG`)

The `command.log` event is the mechanism used by symfony to do the logging for
the symfony CLI utility (see the logger factory).

### ~`command.pre_command`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfTask`

| Parameter   | Description
| ----------- | -----------
| `arguments` | An array of arguments passed on the CLI
| `options`   | An array of options passed on the CLI

The `command.pre_command` event is notified just before a task is executed.

### ~`command.post_command`~

*Notify method*: `notify`

*Default notifiers*: `sfTask`

The `command.post_command` event is notified just after a task is executed.

### ~`command.filter_options`~

*Notify method*: `filter`

*Default notifiers*: `sfTask`

| Parameter         | Description
| ----------------- | -----------
| `command_manager` | The `sfCommandManager` instance

The `command.filter_options` event is notified before the task CLI options are
parsed. This event can be used to filter the options passed by the user.

`configuration`
---------------

### ~`configuration.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfProjectConfiguration`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `configuration.method_not_found` event is notified when a method is not
defined in the `sfProjectConfiguration` class. By listening to this event, a
method can be added to the class, without using inheritance.

`component`
-----------

### ~`component.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfComponent`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `component.method_not_found` event is notified when a method is not
defined in the `sfComponent` class. By listening to this event, a method can
be added to the class, without using inheritance.

`context`
---------

### ~`context.load_factories`~

*Notify method*: `notify`

*Default notifiers*: `sfContext`

The `context.load_factories` event is notified once per request by the
`sfContext` object just after all factories have been initialized. This is the
first event to be notified with all core classes initialized.

### ~`context.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfContext`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `context.method_not_found` event is notified when a method is not defined
in the `sfContext` class. By listening to this event, a method can be added to
the class, without using inheritance.

`controller`
------------

### ~`controller.change_action`~

*Notify method*: `notify`

*Default notifiers*: `sfController`

| Parameter | Description
| --------- | -----------
| `module`  | The module name to be executed
| `action`  | The action name to be executed

The `controller.change_action` is notified just before an action is executed.

### ~`controller.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfController`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `controller.method_not_found` event is notified when a method is not
defined in the `sfController` class. By listening to this event, a method can
be added to the class, without using inheritance.

### ~`controller.page_not_found`~

*Notify method*: `notify`

*Default notifiers*: `sfController`

| Parameter | Description
| --------- | -----------
| `module`  | The module name that generated the 404 error
| `action`  | The action name that generated the 404 error

The `controller.page_not_found` is notified whenever a 404 error is generated
during the handling of a request.

You can listen to this event to do something special whenever a 404 page
occurs, like sending an email, or logging the error. the event.

`debug`
-------

### ~`debug.web.load_panels`~

*Notify method*: `notify`

*Default notifiers*: `sfWebDebug`

The `debug.web.load_panels` event is notified after the call to the
`configure` method of the `sfWebDebug` instance. You can use this event to
manage your own panels.

### `debug.web.view.filter_parameter_html`

*Notify method*: `filter`

*Default notifiers*: `sfWebDebugPanelView`

| Parameter   | Description
| ----------- | -----------
| `parameter` | The parameter to filter

The `debug.web.view.filter_parameter_html` event filters each parameter
rendered by the `sfWebDebugPanelView` panel.

`doctrine`
----------

### ~`doctrine.configure`~

*Notify method*: `notify`

*Default notifiers*: `sfDoctrinePluginConfiguration`

The `doctrine.configure` event is notified after the Doctrine plugin has been
configured.

### ~`doctrine.filter_model_builder_options`~

*Notify method*: `filter`

*Default notifiers*: `sfDoctrinePluginConfiguration`

The `doctrine.filter_model_builder_options` event filters the options for the
Doctrine schema builder.

### ~`doctrine.filter_cli_config`~

*Notify method*: `filter`

*Default notifiers*: `sfDoctrinePluginConfiguration`

The `doctrine.filter_cli_config` event filters the configuration array for the
Doctrine CLI.

### ~`doctrine.configure_connection`~

*Notify method*: `notify`

*Default notifiers*: `Doctrine_Manager` through `sfDoctrineDatabase`

| Parameter    | Description
| ------------ | -----------
| `connection` | The `Doctrine_Connection` instance
| `database`   | The `sfDoctrineDatabase` instance

The `doctrine.configure_connection` event is notified when a Doctrine database
is initialized for the first time.

### ~`doctrine.admin.delete_object`~

*Notify method*: `notify`

*Default notifiers*: Admin generator module class

| Parameter | Description
| --------- | -----------
| `object`  | The Doctrine object to delete

The `doctrine.admin.delete_object` event is notified when a Doctrine object is
deleted in an admin generator module.

### ~`doctrine.admin.save_object`~

*Notify method*: `notify`

*Default notifiers*: Admin generator module class

| Parameter | Description
| --------- | -----------
| `object`  | The Doctrine object to delete

The `doctrine.admin.save_object` event is notified when a Doctrine object is
saved in an admin generator module.

### ~`doctrine.admin.build_query`~

*Notify method*: `notify`

*Default notifiers*: Admin generator module class

| Parameter | Description
| --------- | -----------
| `object`  | The Doctrine object to delete

The `doctrine.admin.build_query` event is notified when a Doctrine Query is
created in an admin generator module.

### ~`doctrine.admin.pre_execute`~

*Notify method*: `notify`

*Default notifiers*: Admin generator module class

| Parameter        | Description
| ---------------- | -----------
| `configuration`  | The admin generator configuration object

The `doctrine.admin.pre_execute` event is notified in the `preExecute()`
method of admin generator modules.

`form`
------

### ~`form.post_configure`~

*Notify method*: `notify`

*Default notifiers*: `sfFormSymfony`

The `form.post_configure` event is notified after every form is configured.

### ~`form.filter_values`~

*Notify method*: `filter`

*Default notifiers*: `sfFormSymfony`

The `form.filter_values` event filters the merged, tainted parameters and
files array just prior to binding.

### ~`form.validation_error`~

*Notify method*: `notify`

*Default notifiers*: `sfFormSymfony`

| Parameter  | Description
| ---------- | ------------------
| `error`    | The error instance

The `form.validation_error` event is notified whenever form validation fails.

### ~`form.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfFormSymfony`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `form.method_not_found` event is notified when a method is not defined in
the `sfFormSymfony` class. By listening to this event, a method can be added
to the class, without using inheritance.

`mailer`
--------

### ~`mailer.configure`~

*Notify method*: `notify`

*Default notifiers*: `sfMailer`

The `mailer.configure` event is notified after the mailer instance has been
configured. The mailer instance is the subject of the event.

`plugin`
--------

### ~`plugin.pre_install`~

*Notify method*: `notify`

*Default notifiers*: `sfPluginManager`

| Parameter    | Description
| ------------ | -----------
| `channel`    | The plugin channel
| `plugin`     | The plugin name
| `is_package` | Whether the plugin to install is a local package (`true`), or a web package (`false`)

The `plugin.pre_install` event is notified just before a plugin will be
installed.

### ~`plugin.post_install`~

*Notify method*: `notify`

*Default notifiers*: `sfPluginManager`

| Parameter    | Description
| ------------ | -----------
| `channel`    | The plugin channel
| `plugin`     | The plugin name

The `plugin.post_install` event is notified just after a plugin has been
installed.

### ~`plugin.pre_uninstall`~

*Notify method*: `notify`

*Default notifiers*: `sfPluginManager`

| Parameter    | Description
| ------------ | -----------
| `channel`    | The plugin channel
| `plugin`     | The plugin name

The `plugin.pre_uninstall` event is notified just before a plugin will be
uninstalled.

### ~`plugin.post_uninstall`~

*Notify method*: `notify`

*Default notifiers*: `sfPluginManager`

| Parameter    | Description
| ------------ | -----------
| `channel`    | The plugin channel
| `plugin`     | The plugin name

The `plugin.post_uninstall` event is notified just after a plugin has been
uninstalled.

`request`
---------

### ~`request.filter_parameters`~

*Notify method*: `filter`

*Default notifiers*: `sfWebRequest`

| Parameter    | Description
| ------------ | -----------
| `path_info`  | The request path

The `request.filter_parameters` event is notified when the request parameters
are initialized.

### ~`request.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfRequest`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `request.method_not_found` event is notified when a method is not
defined in the `sfRequest` class. By listening to this event, a method can be
added to the class, without using inheritance.

`response`
----------

### ~`response.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfResponse`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `response.method_not_found` event is notified when a method is not
defined in the `sfResponse` class. By listening to this event, a method can be
added to the class, without using inheritance.

### ~`response.filter_content`~

*Notify method*: `filter`

*Default notifiers*: `sfResponse`

The `response.filter_content` event is notified before a response is sent. By
listening to this event, you can manipulate the content of the response before
it is sent.

`routing`
---------

### ~`routing.load_configuration`~

*Notify method*: `notify`

*Default notifiers*: `sfRouting`

The `routing.load_configuration` event is notified when the routing factory
loads the routing configuration.

`task`
------

### ~`task.cache.clear`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfCacheClearTask`

| Parameter | Description
| --------- | -----------
| `app`     | The application name
| `type`    | The type of cache (`all`, `config`, `i18n`, `routing`, `module`, and `template`)
| `env`     | The environment

The `task.cache.clear` event is notified whenever the user clears the cache
from the CLI with the `cache:clear` task.

`template`
----------

### ~`template.filter_parameters`~

*Notify method*: `filter`

*Default notifiers*: `sfViewParameterHolder`

The `template.filter_parameters` event is notified before a view file is
rendered. By listening to this event you can access and manipulate variables
passed to a template.

`user`
------

### ~`user.change_culture`~

*Notify method*: `notify`

*Default notifiers*: `sfUser`

| Parameter | Description
| --------- | -----------
| `culture` | The user culture

The `user.change_culture` event is notified when the user culture is changed
during a request.

### ~`user.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfUser`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `user.method_not_found` event is notified when a method is not defined
in the `sfUser` class. By listening to this event, a method can be added to
the class, without using inheritance.

### ~`user.change_authentication`~

*Notify method*: `notify`

*Default notifiers*: `sfBasicSecurityUser`

| Parameter       | Description
| --------------- | -----------
| `authenticated` | Whether the user is authenticated or not

The `user.change_authentication` event is notified whenever the user
authentication status changes.

`view`
------

### ~`view.configure_format`~

*Notify method*: `notify`

*Default notifiers*: `sfView`

| Parameter  | Description
| ---------- | -----------
| `format`   | The requested format
| `response` | The response object
| `request`  | The request object

The `view.configure_format` event is notified by the view when the request has
the `sf_format` parameter set. The event is notified after symfony has done
simple things like changing setting or unsetting the layout. This event allows
the view and the response object to be changed according to the requested
format.

### ~`view.method_not_found`~

*Notify method*: `notifyUntil`

*Default notifiers*: `sfView`

| Parameter   | Description
| ----------- | -----------
| `method`    | The name of the called missing method
| `arguments` | The arguments passed to the method

The `view.method_not_found` event is notified when a method is not defined
in the `sfView` class. By listening to this event, a method can be added to
the class, without using inheritance.

`view.cache`
------------

### ~`view.cache.filter_content`~

*Notify method*: `filter`

*Default notifiers*: `sfViewCacheManager`

| Parameter  | Description
| ---------- | -----------
| `response` | The response object
| `uri`      | The URI of the cached content
| `new`      | Whether the content is new in cache or not

The `view.cache.filter_content` event is notified whenever a content is
retrieved from the cache.
