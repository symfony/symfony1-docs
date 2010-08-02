Symfony Internals
=================

*by Geoffrey Bachelet*

Have you ever wondered what happens to a HTTP request when it reaches a
symfony application? If yes, then you are in the right place. This chapter
will explain in depth how symfony processes each request in order to create and
return the response. Of course, just describing the process would lack a
bit of fun, so we'll also have a look at some interesting things you can do and
where you can interact with this process.

The Bootstrap
-------------

It all begins in your application's controller. Say you have a `frontend`
controller with a `dev` environment (a very classic start for any symfony
project). In this case, you'll end up with a front controller located at
[`web/frontend_dev.php`](http://trac.symfony-project.org/browser/branches/1.3/lib/task/generator/skeleton/app/web/index.php).
What exactly happens in this file? In just a few lines of code, symfony
retrieves the application configuration and creates an instance of
`sfContext`, which is responsible for dispatching the request. The application
configuration is necessary when creating the `sfContext` object, which is
the application-dependent engine behind symfony.

>**TIP**
>Symfony already gives you quite a bit of control on what happens here allowing
>you to pass a custom root directory for your application as the fourth
>argument of ~`ProjectConfiguration::getApplicationConfiguration()`~ as well as
>a custom context class as the third (and last) argument of
>[`sfContext::createInstance()`](http://www.symfony-project.org/api/1_3/sfContext#method_createinstance)
>(but remember it has to extend `sfContext`).

Retrieving the application's configuration is a very important step. First,
`sfProjectConfiguration` is responsible for guessing the application's
configuration class, usually `${application}Configuration`, located in
`apps/${application}/config/${application}Configuration.class.php`.

`sfApplicationConfiguration` actually extends `ProjectConfiguration`, meaning
that any method in `ProjectConfiguration` can be shared between all applications.
This also means that `sfApplicationConfiguration` shares its constructor
with both `ProjectConfiguration` and `sfProjectConfiguration`. This is
fortunate since much of the project is configured inside the
`sfProjectConfiguration` constructor. First, several useful values are
computed and stored, such as the project's root directory and the symfony
library directory. `sfProjectConfiguration` also creates a new event
dispatcher of type `sfEventDispatcher`, unless one was passed as the fifth
argument of `ProjectConfiguration::getApplicationConfiguration()` in the
front controller.

Just after that, you are given a chance to interact with the configuration
process by overriding the `setup()` method of `ProjectConfiguration`. This
is usually the best place to enable / disable plugins (using
[`sfProjectConfiguration::setPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setplugins),
[`sfProjectConfiguration::enablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableplugins),
[`sfProjectConfiguration::disablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_disableplugins) or
[`sfProjectConfiguration::enableAllPluginsExcept()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableallpluginsexcept)).

Next the plugins are loaded by [`sfProjectConfiguration::loadPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_loadplugins)
and the developer has a chance to interact with this process through the
[`sfProjectConfiguration::setupPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setupplugins) that can be overriden.

Plugin initialization is quite straight forward. For each plugin, symfony
looks for a `${plugin}Configuration` (e.g. `sfGuardPluginConfiguration`) class
and instantiates it if found. Otherwise, `sfPluginConfigurationGeneric` is
used. You can hook into a plugin's configuration through two methods:

 * `${plugin}Configuration::configure()`, before autoloading is done
 * `${plugin}Configuration::initialize()`, after autoloading

Next,  `sfApplicationConfiguration` executes its `configure()` method,
which can be used to customize each application's configuration before
the bulk of the internal configuration initialization process begins in
[`sfApplicationConfiguration::initConfiguration()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_initconfiguration).

This part of symfony's configuration process is responsible for many things
and there are several entry points if you want to hook into this process.
For example, you can interact with the autoloader's configuration
by connecting to the `autoload.filter_config` event. Next, several very
important configuration files are loaded, including `settings.yml` and
`app.yml`. Finally, a last bit of plugin configuration is available through
each plugin's `config/config.php` file or configuration class's `initialize()`
method.

If `sf_check_lock` is activated, symfony will now check for a lock file (the
one created by the `project:disable` task, for example). If the lock is found,
the following files are checked and the first available is included, followed
immediately by termination of the script:

 1. `apps/${application}/config/unavailable.php`,
 1. `config/unavailable.php`,
 1. `web/errors/unavailable.php`,
 1. `lib/vendor/symfony/lib/exception/data/unavailable.php`,

Finally, the developer has one last chance to customize the application's
initialization through the ~`sfApplicationConfiguration::initialize()`~ method.

### Bootstrap and configuration summary

 * Retrieval of the application's configuration
  * `ProjectConfiguration::setup()` (define your plugins here)
  * Plugins are loaded
   * `${plugin}Configuration::configure()`
   * `${plugin}Configuration::initialize()`
  * `ProjectConfiguration::setupPlugins()` (setup your plugins here)
  * `${application}Configuration::configure()`
  * `autoload.filter_config` is notified
  * Loading of `settings.yml` and `app.yml`
  * `${application}Configuration::initialize()`
 * Creation of an `sfContext` instance

`sfContext` and Factories
-------------------------

Before diving into the dispatch process, let's talk about a vital part of the
symfony workflow: the factories.

In symfony, factories are a set of components or classes that your
application relies on. Examples of factories are `logger`, `i18n`, etc.
Each factory is configured via `factories.yml`, which is compiled by a
config handler (more on config handlers later) and converted into PHP
code that actually instantiates the factory objects (you can view this
code in your cache in the
`cache/frontend/dev/config/config_factories.yml.php` file).

>**NOTE**
>Factory loading happens upon `sfContext` initialization. See
>[`sfContext::initialize()`](http://www.symfony-project.org/api/1_3/sfContext#method_initialize)
>and [`sfContext::loadFactories()`](http://www.symfony-project.org/api/1_3/sfContext#method_loadfactories)
>for more information.

At this point, you can already customize a large part of symfony's behavior
just by editing the `factories.yml` configuration. You can even replace symfony's
built-in factory classes with your own!

>**NOTE**
>If you're interested in knowing more about factories,
>[The symfony reference book](http://www.symfony-project.org/reference/1_3/en/05-Factories)
>as well as the
>[`factories.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/factories.yml)
>file itself are invaluable resources.

If you looked at the generated `config_factories.yml.php`, you may have
noticed that factories are instantiated in a certain order. That order is
important since some factories are dependent on others (for example, the
`routing` component obviously needs the `request` to retrieve the information
it needs).

Let's talk in greater details about the `request`. By default, the
`sfWebRequest` class represents the `request`. Upon instantiation,
[`sfWebRequest::initialize()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_initialize)
is called, which gathers relevant information such as the GET / POST parameters
as well as the HTTP method. You're then given an opportunity to add your own
request processing through the `request.filter_parameters` event.

### Using the `request.filter_parameter` event

Let's say you're operating a website exposing a public API to your users. The API
is available through HTTP, and each user wanting to use it must provide a valid
API key through a request header (for example `X_API_KEY`) to be validated by
your application. This can be easily achieved using the
`request.filter_parameter` event:

    [php]
    class apiConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('request.filter_parameters', array(
          $this, 'requestFilterParameters'
        ));
      }

      public function requestFilterParameters(sfEvent $event, $parameters)
      {
        $request = $event->getSubject();

        $api_key = $request->getHttpHeader('X_API_KEY');

        if (null === $api_key || false === $api_user = Doctrine_Core::getTable('ApiUser')->findOneByToken($api_key))
        {
          throw new RuntimeException(sprintf('Invalid api key "%s"', $api_key));
        }

        $request->setParameter('api_user', $api_user);

        return $parameters;
      }
    }

You will then be able to access your API user from the request:

    [php]
    public function executeFoobar(sfWebRequest $request)
    {
      $api_user = $request->getParameter('api_user');
    }

This technique can be used, for example, to validate webservice calls.

>**NOTE**
>The `request.filter_parameters` event comes with a lot of information about
>the request, see the
>[`sfWebRequest::getRequestContext()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_getrequestcontext)
>method for more information.

The next very important factory is the routing. Routing's initialization is
fairly straightforward and consists mostly of gathering and setting specific
options. You can, however, hook up to this process through the
`routing.load_configuration` event.

>**NOTE**
>The `routing.load_configuration` event gives you access to the current
>routing object's instance (by default,
>[`sfPatternRouting`](http://trac.symfony-project.org/browser/branches/1.3/lib/routing/sfPatternRouting.class.php)).
>You can then manipulate registered routes through a variety of methods.

### `routing.load_configuration` event usage example

For example, you can easily add a route:

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('routing.load_configuration', array(
        $this, 'listenToRoutingLoadConfiguration'
      ));
    }

    public function listenToRoutingLoadConfiguration(sfEvent $event)
    {
      $routing = $event->getSubject();

      if (!$routing->hasRouteName('my_route'))
      {
        $routing->prependRoute('my_route', new sfRoute(
          '/my_route', array('module' => 'default', 'action' => 'foo')
        ));
      }
    }

URL parsing occurs right after initialization, via the
[`sfPatternRouting::parse()`](http://www.symfony-project.org/api/1_3/sfPatternRouting#method_parse)
method. There are quite a few methods involved, but it suffices to say that by the
time we reach the end of the `parse()` method, the correct route has been found,
instantiated and bound to relevant parameters.

>**NOTE**
>For more information about routing, please see the `Advanced Routing` chapter of this
>book.

Once all factories have been loaded and properly setup, the
`context.load_factories` event is triggered. This event is important since
it's the earliest event in the framework where the developer has access to all
of symfony's core factory objects (request, response, user, logging, database,
etc.).

This is also the time to connect to another very useful event:
`template.filter_parameters`. This event occurs whenever a file is rendered by
[`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php)
and allows the developer to control the parameters actually passed to the template.
`sfContext` takes advantage of this event to add some useful parameters to each
template (namely, `$sf_context`, `$sf_request`, `$sf_params`, `$sf_response`
and `$sf_user`).

You can connect to the `template.filter_parameters` event in order to add
additional custom global parameters to all templates.

### Taking advantage of the `template.filter_parameters` event

Say you decide that every single template you use should have access to a
particular object, say a helper object. You would then add the following
code to `ProjectConfiguration`:

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('template.filter_parameters', array(
        $this, 'templateFilterParameters'
      ));
    }

    public function templateFilterParameters(sfEvent $event, $parameters)
    {
      $parameters['my_helper_object'] = new MyHelperObject();

      return $parameters;
    }

Now every template has access to an instance of `MyHelperObject` through
`$my_helper_object`.

### `sfContext` summary

1. Initialization of `sfContext`
1. Factory loading
1. Events notified:
 1. [request.filter_parameters](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_request_filter_parameters)
 1. [routing.load_configuration](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_routing_load_configuration)
 1. [context.load_factories](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_context_load_factories)
1. Global templates parameters added

A Word on Config Handlers
-------------------------

Config handlers are at the heart of symfony's configuration system. A config
handler is tasked with *understanding* the meaning behind a configuration
file. Each config handler is simply a class that is used to translate a set of
yaml configuration files into a block of PHP code that can be executed as
needed. Each configuration file is assigned to one specific config handler in the
[`config_handlers.yml` file](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/config_handlers.yml).

To be clear, the job of a config handler is *not* to actually parse the yaml
files (this is handled by `sfYaml`). Instead each config handler creates a set
of PHP directions based on the YAML information and saves those directions
to a PHP file, which can be efficiently included later. The *compiled*
version of each YAML configuration file can be found in the cache directory.

The most commonly used config handler is most certainly
[`sfDefineEnvironmentConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfDefineEnvironmentConfigHandler.class.php),
which allows for environment-specific configuration settings.
This config handler takes care to fetch only the configuration settings
of the current environment.

Still not convinced? Let's explore
[`sfFactoryConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfFactoryConfigHandler.class.php).
This config handler is used to compile `factories.yml`, which is one of the
most important configuration file in symfony. This config handler is very
particular since it converts a YAML configuration file into the PHP code that
ultimately instantiate the factories (the all-important components we talked about
earlier). Not your average config handler, is it?

The Dispatching and Execution of the Request
--------------------------------------------

Enough said about factories, let's get back on track with the dispatch process.
Once `sfContext` is finished initializing, the final step is to call the
controller's `dispatch()` method,
[`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch).

The dispatch process itself in symfony is very simple. In fact,
`sfFrontWebController::dispatch()` simply pulls the module and action names
from the request parameters and forwards the application via
[`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward).

>**NOTE**
>At this point, if the routing could not parse any module name or action name
>from the current url, an
>[`sfError404Exception`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfError404Exception.class.php) is
>raised, which will forward the request to the error 404 handling module (see
>[`sf_error_404_module` and
>`sf_error_404_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_error_404)).
>Note that you can raise such an exception from anywhere in your application to
>achieve this effect.

The `forward` method is responsible for a lot of pre-execution checks as
well as preparing the configuration and data for the action to be executed.

First the controller checks for the presence of a
[`generator.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/generator.yml)
file for the current module. This check is performed first (after some basic
module / action name cleanup) because the `generator.yml` config file (if
it exists) is responsible for generating the base actions class for the module
(through its
[config handler, `sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php)).
This is needed for the next step, which checks if the module and action
exists. This is delegated to the controller, through
[`sfController::actionExists()`](http://www.symfony-project.org/api/1_3/sfController#method_actionexists),
which in turn calls the
[`sfController::controllerExists()`](http://www.symfony-project.org/api/1_3/sfController#method_controllerexists)
method. Here again, if the `actionExists()` method fails, an `sfError404Exception`
is raised.

>**NOTE**
>The
>[`sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php) is
>a special config handler that takes care of instantiating the right generator
>class for your module and executing it. For more information about config
>handlers, see *A word on config handler* in this chapter.
>Also, for more information about the `generator.yml`, see
>[chapter 6 of the symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

There's not much you can do here besides overriding the
[`sfApplicationConfiguration::getControllerDirs()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_getcontrollerdirs)
method in the application's configuration class. This method returns an array
of directories where the controller files live, with an additional parameter
to tell symfony if it should check whether controllers in each directory are
enabled via the `sf_enabled_modules` configuration option from `settings.yml`.
For example, `getControllerDirs()` could look something like this:

    [php]
    /**
     * Controllers in /tmp/myControllers won't need to be enabled
     * to be detected
     */
    public function getControllerDirs($moduleName)
    {
      return array_merge(parent::getControllerDirs($moduleName), array(
        '/tmp/myControllers/'.$moduleName => false
      ));
    }

>**NOTE**
>If the action does not exist, an `sfError404Exception` is thrown.

The next step is to retrieve an instance of the controller containing the action.
This is handled via the
[`sfController::getAction()`](http://www.symfony-project.org/api/1_3/sfController#method_getaction)
method, which, like `actionExists()` is a facade for the
[`sfController::getController()`](http://www.symfony-project.org/api/1_3/sfController#method_getcontroller),
method. Finally, the controller instance is added to the `action stack`.

>**NOTE**
>The action stack is a FIFO (First In First Out) style stack which holds all
>actions executed during the request. Each item within the stack is wrapped in
>an `sfActionStackEntry` object. You can always access the stack with
>`sfContext::getInstance()->getActionStack()` or
>`$this->getController()->getActionStack()` from within an action.

After a little more configuration loading, we'll be ready to execute our action.
The module-specific configuration must still be loaded, which can be
found in two distinct places. First symfony looks for the `module.yml` file
(normally located in `apps/frontend/modules/yourModule/config/module.yml`)
which, because it's a YAML config file, uses the config cache. Additionally,
this configuration file can declare the module as *internal*, using the
`mod_yourModule_is_internal` setting which will cause the request to fail at
this point since an internal module cannot be called publicly.

>**NOTE**
>Internal modules were formerly used to generate email content (through
>`getPresentationFor()`, for example). You should now use other techniques,
>such as partial rendering (`$this->renderPartial()`) instead.

Now that `module.yml` is loaded, it's time to check for a second time that the
current module is enabled. Indeed, you can set the `mod_$moduleName_enabled`
setting to `false` if you want to disable the module at this point.

>**NOTE**
>As mentioned, there are two different ways of enabling or disabling a module.
>The difference is what happens when the module is disabled. In the first case,
>when the `sf_enabled_modules` setting is checked, a disabled module will cause
>an
>[`sfConfigurationException`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfConfigurationException.class.php)
>to be thrown. This should be used when disabling a module permanently. In
>the second case, via the `mod_$moduleName_enabled` setting, a disabled
>module will cause the application to forward to the disabled module (see [the
>`sf_module_disabled_module` and
>`sf_module_disabled_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_module_disabled)
>settings). You should use this when you want to temporarily disable a module.

The final opportunity to configure a module lies in the `config.php` file
(`apps/frontend/modules/yourModule/config/config.php`) where you can place
arbitrary PHP code to be run in the context of the
[`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
method (that is, you have access to the `sfController` instance via the `$this`
variable, as the code is literally run inside the `sfController` class).

### The Dispatching Process Summary

1. [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) is called
1. [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) is called
1. Check for a `generator.yml`
1. Check if the module / action exists
1. Retrieve a list of controllers directories
1. Retrieve an instance of the action
1. Load module configuration through `module.yml` and/or `config.php`

The Filter Chain
----------------

Now that all the configuration has been done, it's time to start the real work.
Real work, in this particular case, is the execution of the filter chain.

>**NOTE**
>Symfony's filter chain implements a design pattern known as
[chain of responsibility](http://en.wikipedia.org/wiki/Chain-of-responsibility_pattern).
>This is a simple yet powerful pattern that allows for chained actions, where each
>part of the chain is able to decide whether or not the chain should continue
>execution.
>Each part of the chain is also able to execute both before and after the rest
>of the chain's execution.

The configuration of the filter chain is pulled from the current module's
[`filters.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/filters.yml),
which is why the action instance is needed. This is your chance to modify the
set of filters executed by the chain. Just remember that the rendering filter
should always be the first in the list (we will see why later). The default
filters configuration is as follow:

* [`rendering`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfRenderingFilter.class.php)
* [`security`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfSecurityFilter.class.php)
* [`cache`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfCacheFilter.class.php)
* [`execution`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfExecutionFilter.class.php)

>**NOTE**
>It is strongly advised that you add your own filters between the `security`
>and the `cache` filter.

### The Security Filter

Since the `rendering` filter waits for everyone to be done before doing anything, the
first filter that actually gets executed is the `security` filter. This filter
ensures that everything is right according to the
[`security.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/security.yml)
configuration file. Specifically, the filter forwards an unauthenticated user
to the `login` module / action and a user with insufficient credentials to
the `secure` module / action. Note that this filter is only executed if
security is enabled for the given action.

### The Cache Filter

Next comes the `cache` filter. This filter takes advantage of its ability to
prevent further filters from being executed. Indeed, if the cache is activated,
and if we have a hit, why even bother executing the action? Of course, this
will work only for a fully cacheable page, which is not the case for the vast
majority of pages.

But this filter has a second bit of logic that gets executed after the
execution filter, and just before the rendering filter. This code is
responsible for setting up the right HTTP cache headers, and placing the page
into the cache if necessary, thanks to the
[`sfViewCacheManager::setPageCache()`](http://www.symfony-project.org/api/1_3/sfViewCacheManager#method_setpagecache)
method.

### The Execution Filter

Last but not least, the `execution` filter will, finally, take care of
executing your business logic and handling the associated view.

Everything starts when the filter checks the cache for the current action. Of
course, if we have something in the cache, the actual action execution is
skipped and the `Success` view is then executed.

If the action is not found in the cache, then it is time to execute the
`preExecute()` logic of the controller, and finally to execute the action
itself. This is accomplished by the action instance via a call to
[`sfActions::execute()`](http://www.symfony-project.org/api/1_3/sfActions#method_execute).
This method doesn't do much: it simply checks that the action is callable, then calls
it. Back in the filter, the `postExecute()` logic of the action is now executed.

>**NOTE**
>The return value of your action is very important, since it will determine
>what view will get executed. By default, if no return value is found,
>`sfView::SUCCESS` is assumed (which translates to, you guessed it, `Success`, as in
>`indexSuccess.php`).

One more step ahead, and it's view time. The filter checks for two special
return values that your action may have returned, `sfView::HEADER_ONLY`
and `sfView::NONE`. Each does exactly what their names say: sends HTTP
headers only (internally handled via
[`sfWebResponse::setHeaderOnly()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_setheaderonly))
or skips rendering altogether.

>**NOTE**
>Built-in view names are: `ALERT`, `ERROR`, `INPUT`, `NONE` and `SUCCESS`. But you can
>basically return anything you want.

Once we know that we *do* want to render something, we're ready to get into the
final step of the filter: the actual view execution.

The first thing we do is retrieve an [`sfView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfView.class.php)
object through the [`sfController::getView()`](http://www.symfony-project.org/api/1_3/sfController#method_getview) method. This object can come from
two different places. First you could have a custom view object for this specific action
(assuming the current module/action is, let's keep it simple,
module/action) `actionSuccessView` or `module_actionSuccessView` in a file
called `apps/frontend/modules/module/view/actionSuccessView.class.php`.
Otherwise, the class defined in the `mod_module_view_class` configuration
entry will be used. This value defaults to [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php).

>**TIP**
>Using your own view class gives you a chance to run some view specific logic,
>through the [`sfView::execute()`](http://www.symfony-project.org/api/1_3/sfView#method_execute)
>method. For example, you could instantiate your own template engine.

There are three rendering modes possible for rendering the view:

1. `sfView::RENDER_NONE`": equivalent to `sfView::NONE`, this cancels any rendering from being actually, well, rendered.
1. `sfView::RENDER_VAR`: populates the action's presentation, which is then accessible through its stack entry's [`sfActionStackEntry::getPresentation()`](http://www.symfony-project.org/api/1_3/sfActionStackEntry#method_getpresentation) method.
1. `sfView::RENDER_CLIENT`, the default mode, will render the view and feed the response's content.

>**NOTE**
>Indeed, the rendering mode is used only through the
>[`sfController::getPresentationFor()`](http://www.symfony-project.org/api/1_3/sfController#method_getpresentationfor) method that returns the rendering for a
>given module / action

### The Rendering Filter

We're almost done now, just one very last step. The filter chain has almost
finished executing, but do you remember the rendering filter? It's been waiting
since the beginning of the chain for everyone to complete their work so that it can
do its own job. Namely, the rendering filter sends the response content to the browser, using
[`sfWebResponse::send()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_send).

### Summary of the filter chain execution

1. The filter chain is instantiated with configuration from the `filters.yml` file
1. The `security` filter checks for authorizations and credentials
1. The `cache` filter handles the cache for the current page
1. The `execution` filter actually executes the action
1. The `rendering` filter send the response through `sfWebResponse`

Global Summary
--------------

1. Retrieval of the application's configuration
1. Creation of an `sfContext` instance
1. Initialization of `sfContext`
1. Factories loading
1. Events notified:
 1. ~`request.filter_parameters`~
 1. ~`routing.load_configuration`~
 1. ~`context.load_factories`~
1. Global templates parameters added
1. [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) is called
1. [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) is called
1. Check for a `generator.yml`
1. Check if the module / action exists
1. Retrieve a list of controllers directories
1. Retrieve an instance of the action
1. Load module configuration through `module.yml` and/or `config.php`
1. The filter chain is instantiated with configuration from the `filters.yml` file
1. The `security` filter checks for authorizations and credentials
1. The `cache` filter handles the cache for the current page
1. The `execution` filter actually executes the action
1. The `rendering` filter send the response through `sfWebResponse`

Final Thoughts
--------------

That's it! The request has been handled and we're now ready for another one. Of
course, we could write an entire book about symfony's internal processes, so
this chapter serves only as an overview. You are more than welcome to explore
the source by yourself - it is, and always will be, the best way to learn the
true mechanics of any framework or library.
