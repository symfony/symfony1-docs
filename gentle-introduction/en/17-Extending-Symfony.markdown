Chapter 17 - Extending Symfony
==============================

Eventually, you will need to alter symfony's behavior. Whether you need to modify the way a certain class behaves or add your own custom features, themoment will inevitably happen because all clients have specific requirements that no framework can forecast. Actually, this situation is so common that symfony provides a mechanism to extend existing classes at runtime, beyond simple class inheritance. You can even replace the core symfony classes by modifying the factories settings. Once you have built an extension, you can easily package it as a plug-in, so that it can be reused in other applications, or by other symfony users.

Events
------

PHP does not support multiple inheritance, which means it is not possible to have a class extend more than one other class. Also it is not possible to add new methods to an existing class or override existing methods. To ease these two limitations and to make the framework truly extendable, symfony introduces an *event system*, inspired by the Cocoa notification center, and based on the [Observer design pattern](http://en.wikipedia.org/wiki/Observer_pattern).

### Understanding Events

Some of the symfony classes "notify the dispatcher of an event" at various moments of their life. For instance, when the user changes their culture, the user object notifies that a `change_culture` event has occurred. This is like a shout in the project's space, saying: "I'm doing that. Do whatever you want about it".

You can decide to do something special when an event is fired. For instance, you could save the user culture to a database table each time the `change_culture` event occurs. In order to do so, you need to *register an event listener*, in other words you must declare a function that will be called when the event occurs. Listing 17-1 shows how to register a listener on the user's `change_culture` event.

Listing 17-1 - Registering an Event Listener

    [php]
    $dispatcher->connect('user.change_culture', 'changeUserCulture');
    
    function changeUserCulture(sfEvent $event)
    {
      $user = $event->getSubject();
      $culture = $event['culture'];

      // do something with the user culture
    }

All events and listener registrations are managed by a special object called the *event dispatcher*. This object is available from everywhere in symfony by way of the `ProjectConfiguration` instance, and most symfony objects offer a `getEventDispatcher()` method to get direct access to it. Using the dispatcher's `connect()` method, you can register any PHP callable (either a class method or a function) to be called when an event occurs. The first argument of `connect()` is the event identifier, which is a string composed of a namespace and a name. The second argument is a PHP callable.

>**Note**
>Retrieving the event dispatcher from anywhere in the application:
>
>     [PHP]
>     $dispatcher = ProjectConfiguration::getActive()->getEventDispatcher();

Once the function is registered with the event dispatcher, it waits until the event is fired. The event dispatcher keeps a record of all event listeners, and knows which ones to call when an event occurs. When calling these methods or functions, the dispatcher passes them an `sfEvent` object as a parameter.

The event object stores information about the notified event. The event notifier can be retrieved thanks to the `getSubject()` method, and the event parameters are accessible by using the event object as an array  (for example, `$event['culture']` can be used to retrieve the `culture` parameter passed by `sfUser` when notifying `user.change_culture`).

To wrap up, the event system allows you to add abilities to an existing class or modify its methods at runtime, without using inheritance. 

### Notifying an Event listener

Just like symfony classes notify that events have occurred, your own classes can offer runtime extensibility and notify of events at certain occasions. For instance, let's say that your application requests several third-party web services, and that you have written an `sfRestRequest` class to wrap the REST logic of these requests. A good idea would be to trigger an event each time this class makes a new request. This would make the addition of logging or caching capabilities easier in the future. Listing 17-2 shows the code you need to add to an existing `fetch()` method to make it notify an event listener.

Listing 17-2 - Notifying an Event listener

    [php]
    class sfRestRequest
    {
      protected $dispatcher = null;

      public function __construct(sfEventDispatcher $dispatcher)
      {
        $this->dispatcher = $dispatcher;
      }
      
      /**
       * Makes a query to an external web service
       */
      public function fetch($uri, $parameters = array())
      {
        // Notify the dispatcher of the beginning of the fetch process
        $this->dispatcher->notify(new sfEvent($this, 'rest_request.fetch_prepare', array(
          'uri'        => $uri,
          'parameters' => $parameters
        )));
        
        // Make the request and store the result in a $result variable
        // ...
        
        // Notify the dispatcher of the end of the fetch process
        $this->dispatcher->notify(new sfEvent($this, 'rest_request.fetch_success', array(
          'uri'        => $uri,
          'parameters' => $parameters,
          'result'     => $result
        )));
        
        return $result;
      }
    }

The `notify()` method of the event dispatcher expects an `sfEvent` object as an argument; this is the very same object that is passed to the event listeners. This object always carries a reference to the notifier (that's why the event instance is initialized with `this`) and an event identifier. Optionally, it accepts an associative array of parameters, giving listeners a way to interact with the notifier's logic.

### Notifying the dispatcher of an Event Until a Listener handles it

By using the `notify()` method, you make sure that all the listeners registered on the notifying event are executed. However, in some cases you need to allow a listener to stop the event and prevent further listeners from being notified about it. In this case, you should use `notifyUntil()` instead of `notify()`. The dispatcher will then execute all listeners until one returns `true`, and then stop the event notification. In other words, `notifyUntil()` is like a shout in the project space saying: "I'm doing that. If somebody cares, then I won't tell anybody else". Listing 17-3 shows how to use this technique in combination with a magic `__call()` method to add methods to an existing class at runtime.

Listing 17-3 - Notifying of an Event Until a Listener Returns True

    [php]
    class sfRestRequest
    {
      // ...
      
      public function __call($method, $arguments)
      {
        $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'rest_request.method_not_found', array(
          'method'    => $method, 
          'arguments' => $arguments
        )));
        if (!$event->isProcessed())
        {
          throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
        }
        
        return $event->getReturnValue();
      }
    }

An event listener registered on the `rest_request.method_not_found` event can test the requested `$method` and decide to handle it, or pass to the next event listener callable. In Listing 17-4, you can see how a third party class can add `put()` and `delete()` methods to the `sfRestRequest` class at runtime with this trick.

Listing 17-4 - Handling a "Notify Until" Event type

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        // Register our listener
        $this->dispatcher->connect('rest_request.method_not_found', array('sfRestRequestExtension', 'listenToMethodNotFound'));
      }
    }

    class sfRestRequestExtension
    {
      static public function listenToMethodNotFound(sfEvent $event)
      {
        switch ($event['method'])
        {
          case 'put':
            self::put($event->getSubject(), $event['arguments'])

            return true;
          case 'delete':
            self::delete($event->getSubject(), $event['arguments'])

            return true;
          default:
            return false;
        }
      }

      static protected function put($restRequest, $arguments)
      {
        // Make a put request and store the result in a $result variable
        // ...
        
        $event->setReturnValue($result);
      }
      
      static protected function delete($restRequest, $arguments)
      {
        // Make a delete request and store the result in a $result variable
        // ...
        
        $event->setReturnValue($result);
      }
    }

In practice, `notifyUntil()` offers multiple inheritance capabilities, or rather mixins (the addition of methods from third-party classes to an existing class), to PHP. You can now "inject" new methods to objects that you can't extend by way of inheritance. And this happens at runtime. You are not limited by the Object Oriented capabilities of PHP anymore when you use symfony.

>**TIP**: As the first listener to catch a `notifyUntil()` event prevents further notifications, you may worry about the order in which listeners are executed. This order corresponds to the order in which listeners were registered - first registered, first executed. In practice, cases where this could be an issue seldom happen. If you realize that two listeners conflict on a particular event, perhaps your class should notify several events, for instance one at the beginning and one at the end of the method execution. And if you use events to add new methods to an existing class, name your methods wisely so that other attempts at adding methods don't conflict. Prefixing method names with the name of the listener class is a good practice.

### Changing the Return Value of a Method

You can probably imagine how a listener can not only use the information given by an event, but also modify it, to alter the original logic of the notifier.  If you want to allow this, you should use the `filter()` method of the event dispatcher rather than `notify()`. All event listeners are then called with two parameters: the event object, and the value to filter. Event listeners must return the value, whether they altered it or not. Listing 17-5 shows how `filter()` can be used to filter a response from a web service and escape special characters in that response.

Listing 17-5 - Notifying of and Handling a Filter Event

    [php]
    class sfRestRequest
    {
      // ...
      
      /**
       * Make a query to an external web service
       */
      public function fetch($uri, $parameters = array())
      {
        // Make the request and store the result in a $result variable
        // ...
        
        // Notify of the end of the fetch process
        return $this->dispatcher->filter(new sfEvent($this, 'rest_request.filter_result', array(
          'uri'        => $uri,
          'parameters' => $parameters,
        )), $result)->getReturnValue();
      }
    }

    // Add escaping to the web service response
    $dispatcher->connect('rest_request.filter_result', 'rest_htmlspecialchars');

    function rest_htmlspecialchars(sfEvent $event, $result)
    {
      return htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
    }

### Built-In Events

Many of symfony's classes have built-in events, allowing you to extend the framework without necessarily changing the classes themselves. Table 17-1 lists these events, together with their types and arguments.

Table 17-1 - Symfony's Events

| **Event name** (**Type**)                      | **Notifiers**                 | **Arguments**               |
| ---------------------------------------------- | ----------------------------- | --------------------------- |
| application.log (notify)                       | lot of classes                | priority                    |
| application.throw_exception (notifyUntil)      | sfException                   | -                           |
| autoload.filter_config (filter)                | sfAutoloadConfigHandler       | -                           |
| command.log (notify)                           | sfCommand* classes            | priority                    |
| command.pre_command (notifyUntil)              | sfTask                        | arguments, options          |
| command.post_command (notify)                  | sfTask                        | -                           |
| command.filter_options (filter)                | sfTask                        | command_manager             |
| configuration.method_not_found (notifyUntil)   | sfProjectConfiguration        | method, arguments           |
| component.method_not_found (notifyUntil)       | sfComponent                   | method, arguments           |
| context.load_factories (notify)                | sfContext                     | -                           |
| context.method_not_found (notifyUntil)         | sfContext                     | method, arguments           |
| controller.change_action (notify)              | sfController                  | module, action              |
| controller.method_not_found (notifyUntil)      | sfController                  | method, arguments           |
| controller.page_not_found (notify)             | sfController                  | module, action              |
| debug.web.load_panels (notify)                 | sfWebDebug                    | -                           |
| debug.web.view.filter_parameter_html (filter)  | sfWebDebugPanelView           | parameter                   |
| doctrine.configure (notify)                    | sfDoctrinePluginConfiguration | -                           |
| doctrine.filter_model_builder_options (filter) | sfDoctrinePluginConfiguration | -                           |
| doctrine.filter_cli_config (filter)            | sfDoctrinePluginConfiguration | -                           |
| doctrine.configure_connection (notify)         | Doctrine_Manager              | connection, database        |
| doctrine.admin.delete_object (notify)          | -                             | object                      |
| doctrine.admin.save_object (notify)            | -                             | object                      |
| doctrine.admin.build_query (filter)            | -                             |                             |
| doctrine.admin.pre_execute (notify)            | -                             | configuration               |
| form.post_configure (notify)                   | sfFormSymfony                 | -                           |
| form.filter_values (filter)                    | sfFormSymfony                 | -                           |
| form.validation_error (notify)                 | sfFormSymfony                 | error                       |
| form.method_not_found (notifyUntil)            | sfFormSymfony                 | method, arguments           |
| mailer.configure (notify)                      | sfMailer                      | -                           |
| plugin.pre_install (notify)                    | sfPluginManager               | channel, plugin, is_package |
| plugin.post_install (notify)                   | sfPluginManager               | channel, plugin             |
| plugin.pre_uninstall (notify)                  | sfPluginManager               | channel, plugin             |
| plugin.post_uninstall (notify)                 | sfPluginManager               | channel, plugin             |
| propel.configure (notify)                      | sfPropelPluginConfiguration   | -                           |
| propel.filter_phing_args (filter)              | sfPropelBaseTask              | -                           |
| propel.filter_connection_config (filter)       | sfPropelDatabase              | name, database              |
| propel.admin.delete_object (notify)            | -                             | object                      |
| propel.admin.save_object (notify)              | -                             | object                      |
| propel.admin.build_criteria (filter)           | -                             |                             |
| propel.admin.pre_execute (notify)              | -                             | configuration               |
| request.filter_parameters (filter)             | sfWebRequest                  | path_info                   |
| request.method_not_found (notifyUntil)         | sfRequest                     | method, arguments           |
| response.method_not_found (notifyUntil)        | sfResponse                    | method, arguments           |
| response.filter_content (filter)               | sfResponse, sfException       | -                           |
| routing.load_configuration (notify)            | sfRouting                     | -                           |
| task.cache.clear (notifyUntil)                 | sfCacheClearTask              | app, type, env              |
| task.test.filter_test_files (filter)           | sfTestBaseTask                | arguments, options          |
| template.filter_parameters (filter)            | sfViewParameterHolder         | -                           |
| user.change_culture (notify)                   | sfUser                        | culture                     |
| user.method_not_found (notifyUntil)            | sfUser                        | method, arguments           |
| user.change_authentication (notify)            | sfBasicSecurityUser           | authenticated               |
| view.configure_format (notify)                 | sfView                        | format, response, request   |
| view.method_not_found (notifyUntil)            | sfView                        | method, arguments           |
| view.cache.filter_content (filter)             | sfViewCacheManager            | response, uri, new          |

You are free to register event listeners on any of these events. Just make sure that listener callables return a Boolean when registered on a `notifyUntil` event type, and that they return the filtered value when registered on a `filter` event type.

Note that the event namespaces don't necessarily match the class role. For instance, all symfony classes notify of an `application.log` event when they need something to appear in the log files (and in the web debug toolbar):

    [php]
    $dispatcher->notify(new sfEvent($this, 'application.log', array($message)));

Your own classes can do the same and also notify symfony events when it makes sense to do so.

### Where To Register Listeners?

Event listeners need to be registered early in the life of a symfony request. In practice, the right place to register event listeners is in the application configuration class. This class has a reference to the event dispatcher that you can use in the `configure()` method. Listing 17-6 shows how to register a listener on one of the `rest_request` events of the above examples.

Listing 17-6 - Registering a Listener in the Application Configuration Class, in `apps/frontend/config/ApplicationConfiguration.class.php`

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('rest_request.method_not_found', array('sfRestRequestExtension', 'listenToMethodNotFound'));
      }
    }

Plug-ins (see below) can register their own event listeners. They should do it in the plug-in's `config/config.php` script, which is executed during application initialization and offers access to the event dispatcher through `$this->dispatcher`.

Factories
---------

A factory is the definition of a class for a certain task. Symfony relies on factories for its core features such as the controller and session capabilities. For instance, when the framework needs to create a new request object, it searches in the factory definition for the name of the class to use for that purpose. The default factory definition for requests is `sfWebRequest`, so symfony creates an object of this class in order to deal with requests. The great advantage of using a factory definition is that it is very easy to alter the core features of the framework: Just change the factory definition, and symfony will use your custom request class instead of its own.

The factory definitions are stored in the `factories.yml` configuration file. Listing 17-7 shows the default factory definition file. Each definition is made of the name of an autoloaded class and (optionally) a set of parameters. For instance, the session storage factory (set under the `storage:` key) uses a `session_name` parameter to name the cookie created on the client computer to allow persistent sessions.

Listing 17-7 - Default Factories File, in `frontend/config/factories.yml`

    -
    prod:
      logger:
        class:   sfNoLogger
        param:
          level:   err
          loggers: ~

    test:
      storage:
        class: sfSessionTestStorage
        param:
          session_path: %SF_TEST_CACHE_DIR%/sessions

      response:
        class: sfWebResponse
        param:
          send_http_headers: false

      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true

      view_cache_manager:
        class: sfViewCacheManager
        param:
          cache_key_use_vary_headers: true
          cache_key_use_host_name:    true

The best way to change a factory is to create a new class inheriting from the default factory and to add new methods to it. For instance, the user session factory is set to the `myUser` class (located in `frontend/lib/`) and inherits from `sfUser`. Use the same mechanism to take advantage of the existing factories. Listing 17-8 shows an example of a new factory for the request object.

Listing 17-8 - Overriding Factories

    [php]
    // Create a myRequest.class.php in an autoloaded directory,
    // For instance in frontend/lib/
    <?php

    class myRequest extends sfRequest
    {
      // Your code here
    }

    // Declare this class as the request factory in factories.yml
    all:
      request:
        class: myRequest

Plug-Ins
--------

You will probably need to reuse a piece of code that you developed for one of your symfony applications. If you can package this piece of code into a single class, no problem: Drop the class in one of the `lib/` folders of another application and the autoloader will take care of the rest. But if the code is spread across more than one file, such as a complete new theme for the administration generator or a combination of JavaScript files and helpers to automate your favorite visual effect, just copying the files is not the best solution.

Plug-ins offer a way to package code disseminated in several files and to reuse this code across several projects. Into a plug-in, you can package classes, filters, event listeners, helpers, configuration, tasks, modules, schemas and model extensions, fixtures, web assets, etc. Plug-ins are easy to install, upgrade, and uninstall. They can be distributed as a `.tgz` archive, a PEAR package, or a simple checkout of a code repository. The PEAR packaged plug-ins have the advantage of managing dependencies, being easier to upgrade and automatically discovered. The symfony loading mechanisms take plug-ins into account, and the features offered by a plug-in are available in the project as if the plug-in code was part of the framework.

So, basically, a plug-in is a packaged extension for a symfony project. With plug-ins, not only can you reuse your own code across applications, but you can also reuse developments made by other contributors and add third-party extensions to the symfony core.

### Finding Symfony Plug-Ins

The symfony project website contains a section dedicated to symfony plug-ins and accessible with the following URL:

    http://www.symfony-project.org/plugins/

Each plug-in listed there has its own page, with detailed installation instructions and documentation.

Some of these plug-ins are contributions from the community, and some come from the core symfony developers. Among the latter, you will find the following:

  * `sfFeed2Plugin`: Automates the manipulation of RSS and Atom feeds
  * `sfThumbnailPlugin`: Creates thumbnails--for instance, for uploaded images
  * `sfMediaLibraryPlugin`: Allows media upload and management, including an extension for rich text editors to allow authoring of images inside rich text
  * `sfGuardPlugin`: Provides authentication, authorization, and other user management features above the standard security feature of symfony
  * `sfSuperCachePlugin`: Writes pages in cache directory under the web root to allow the web server to serve them as fast as possible
  * `sfErrorLoggerPlugin`: Logs every 404 and 500 error in a database and provides an administration module to browse these errors
  * `sfSslRequirementPlugin`: Provides SSL encryption support for actions

You should regularly check out the symfony plugin section, because new plug-ins are added all the time, and they bring very useful shortcuts to many aspects of web application programming.

Apart from the symfony plugin section, the other ways to distribute plug-ins are to propose a plug-ins archive for download, to host them in a PEAR channel, or to store them in a public version control repository.

### Installing a Plug-In

The plug-in installation process differs according to the way it's packaged. Always refer to the included README file and/or installation instructions on the plug-in download page.

Plug-ins are installed applications on a per-project basis. All the methods described in the following sections result in putting all the files of a plug-in into a `myproject/plugins/pluginName/` directory.

#### PEAR Plug-Ins

Plug-ins listed on the symfony plugin section can be bundled as PEAR packages and made available via the official symfony plugins PEAR channel: `plugins.symfony-project.org`. To install such a plug-in, use the `plugin:install` task with a plugin name, as shown in Listing 17-9.

Listing 17-9 - Installing a Plug-In from the Official symfony plugins PEAR Channel

    $ cd myproject
    $ php symfony plugin:install pluginName

Alternatively, you can download the plug-in and install it from the disk. In this case, use the path to the package archive, as shown in Listing 17-10.

Listing 17-10 - Installing a Plug-In from a Downloaded PEAR Package

    $ cd myproject
    $ php symfony plugin:install /home/path/to/downloads/pluginName.tgz

Some plug-ins are hosted on external PEAR channels. Install them with the `plugin:install` task, and don't forget to register the channel and mention the channel name, as shown in Listing 17-11.

Listing 17-11 - Installing a Plug-In from a PEAR Channel

    $ cd myproject
    $ php symfony plugin:add-channel channel.symfony.pear.example.com
    $ php symfony plugin:install --channel=channel.symfony.pear.example.com pluginName

These three types of installation all use a PEAR package, so the term "PEAR plug-in" will be used indiscriminately to talk about plug-ins installed from the symfony plugins PEAR channel, an external PEAR channel, or a downloaded PEAR package.

The `plugin:install` task also takes a number of options, as shown on Listing 17-12.

Listing 17-12 - Installing a Plug-In with some Options

    $ php symfony plugin:install --stability=beta pluginName
    $ php symfony plugin:install --release=1.0.3 pluginName
    $ php symfony plugin:install --install-deps pluginName

>**TIP**
>As for every symfony task, you can have a full explanation of the `plugin:install` options and arguments by launching `php symfony help plugin:install`.

#### Archive Plug-Ins

Some plug-ins come as a simple archive of files. To install those, just unpack the archive into your project's `plugins/` directory. If the plug-in contains a `web/` subdirectory, don't forget to run the `plugin:publish-assets` command to create the corresponding symlink under the main `web/` folder as shown in listing 17-13. Finally, don't forget to clear the cache.

Listing 17-13 - Installing a Plug-In from an Archive

    $ cd plugins
    $ tar -zxpf myPlugin.tgz
    $ cd ..
    $ php symfony plugin:publish-assets
    $ php symfony cc

#### Installing Plug-Ins from a Version Control Repository

Plug-ins sometimes have their own source code repository for version control. You can install them by doing a simple checkout in the `plugins/` directory, but this can be problematic if your project itself is under version control.

Alternatively, you can declare the plug-in as an external dependency so that every update of your project source code also updates the plug-in source code. For instance, Subversion stores external dependencies in the `svn:externals` property. So you can add a plug-in by editing this property and updating your source code afterwards, as Listing 17-14 demonstrates.

Listing 17-14 - Installing a Plug-In from a Source Version Repository

    $ cd myproject
    $ svn propedit svn:externals plugins
      pluginName   http://svn.example.com/pluginName/trunk
    $ svn up
    $ php symfony plugin:publish-assets
    $ php symfony cc

>**NOTE**
>If the plug-in contains a `web/` directory, the symfony `plugin:publish-assets` command has to be run to generate the corresponding symlink under the main `web/` folder of the project.

#### Activating a Plug-In Module

Some plug-ins contain whole modules. The only difference between module plug-ins and classical modules is that module plug-ins don't appear in the `myproject/apps/frontend/modules/` directory (to keep them easily upgradeable). They also need to be activated in the `settings.yml` file, as shown in Listing 17-15.

Listing 17-15 - Activating a Plug-In Module, in `frontend/config/settings.yml`

    all:
      .settings:
        enabled_modules:  [default, sfMyPluginModule]

This is to avoid a situation where the plug-in module is mistakenly made available for an application that doesn't require it, which could open a security breach. Think about a plug-in that provides `frontend` and `backend` modules. You will need to enable the `frontend` modules only in your `frontend` application, and the `backend` ones only in the `backend` application. This is why plug-in modules are not activated by default.

>**TIP**
>The default module is the only enabled module by default. That's not really a plug-in module, because it resides in the framework, in `sfConfig::get('sf_symfony_lib_dir')/controller/default/`. This is the module that provides the congratulations pages, and the default error pages for 404 and credentials required errors. If you don't want to use the symfony default pages, just remove this module from the `enabled_modules` setting.

#### Listing the Installed Plug-Ins

If a glance at your project's `plugins/` directory can tell you which plug-ins are installed, the `plugin:list` task tells you even more: the version number and the channel name of each installed plug-in (see Listing 17-16).

Listing 17-16 - Listing Installed Plug-Ins

    $ cd myproject
    $ php symfony plugin:list

    Installed plugins:
    sfPrototypePlugin               1.0.0-stable # plugins.symfony-project.com (symfony)
    sfSuperCachePlugin              1.0.0-stable # plugins.symfony-project.com (symfony)
    sfThumbnail                     1.1.0-stable # plugins.symfony-project.com (symfony)

#### Upgrading and Uninstalling Plug-Ins

To uninstall a PEAR plug-in, call the `plugin:uninstall` task from the root project directory, as shown in Listing 17-17. You must prefix the plug-in name with its installation channel if it's different from the default `symfony` channel (use the `plugin:list` task to determine this channel).

Listing 17-17 - Uninstalling a Plug-In

    $ cd myproject
    $ php symfony plugin:uninstall sfSuperCachePlugin
    $ php symfony cc

To uninstall an archive plug-in or an SVN plug-in, remove manually the plug-in files from the project `plugins/` and `web/` directories, and clear the cache.

To upgrade a plug-in, either use the `plugin:upgrade` task (for a PEAR plug-in) or do an `svn update` (if you grabbed the plug-in from a version control repository). Archive plug-ins can't be upgraded easily.

### Anatomy of a Plug-In

Plug-ins are written using the PHP language. If you can understand how an application is organized, you can understand the structure of the plug-ins.

#### Plug-In File Structure

A plug-in directory is organized more or less like a project directory. The plug-in files have to be in the right directories in order to be loaded automatically by symfony when needed. Have a look at the plug-in file structure description in Listing 17-18.

Listing 17-18 - File Structure of a Plug-In

    pluginName/
      config/
        routing.yml        // Application config file
        *schema.yml        // Data schema
        *schema.xml
        config.php         // Specific plug-in configuration
      data/
        generator/
          sfPropelAdmin
            */             // Administration generator themes
              template/
              skeleton/
        fixtures/
          *.yml            // Fixtures files
      lib/
        *.php              // Classes
        helper/
          *.php            // Helpers
        model/
          *.php            // Model classes
        task/
          *Task.class.php  // CLI tasks
      modules/
        */                 // Modules
          actions/
            actions.class.php
          config/
            module.yml
            view.yml
            security.yml
          templates/
            *.php
      web/
        *                  // Assets

#### Plug-In Abilities

Plug-ins can contain a lot of things. Their content is automatically taken into account by your application at runtime and when calling tasks with the command line. But for plug-ins to work properly, you must respect a few conventions:

  * Database schemas are detected by the `propel-` tasks. When you call `propel:build --classes` or `doctrine:build --classes` in your project, you rebuild the project model and all the plug-in models with it. Note that a Propel plug-in schema must always have a package attribute under the shape `plugins.pluginName`. `lib.model`, as shown in Listing 17-19. If you use Doctrine, the task will automatically generate the classes in the plugin directory.

Listing 17-19 - Example of Propel Schema Declaration in a Plug-In, in `myPlugin/config/schema.yml`

    propel:
      _attributes:    { package: plugins.myPlugin.lib.model }
      my_plugin_foobar:
        _attributes:    { phpName: myPluginFoobar }
          id:
          name:           { type: varchar, size: 255, index: unique }
          ...

  * The plug-in configuration is to be included in the plug-in configuration class (`PluginNameConfiguration.class.php`). This file is executed after the application and project configuration, so symfony is already bootstrapped at that time. You can use this file, for instance, to extend existing classes with event listeners and behaviors.
  * Fixtures files located in the plug-in `data/fixtures/` directory are processed by the `propel:data-load` or `doctrine:data-load` task.
  * Custom classes are autoloaded just like the ones you put in your project `lib/` folders.
  * Helpers are automatically found when you call `use_helper()` in templates. They must be in a` helper/` subdirectory of one of the plug-in's `lib/` directory.
  * If you use Propel, model classes in `myplugin/lib/model/` specialize the model classes generated by the Propel builder (in `myplugin/lib/model/om/` and `myplugin/lib/model/map/`). They are, of course, autoloaded. Be aware that you cannot override the generated model classes of a plug-in in your own project directories.
  * If you use Doctrine, the ORM generates the plugins base classes in `myplugin/lib/model/Plugin*.class.php`, and concrete classes in `lib/model/myplugin/`. This means that you can easily override the model classes in your application. 
  * Tasks are immediately available to the symfony command line as soon as the plug-in is installed. A plugin can either add new tasks, or override an existing one. It is a best practice to use the plug-in name as a namespace for the task. Type `php symfony` to see the list of available tasks, including the ones added by plug-ins.
  * Modules provide new actions accessible from the outside, provided that you declare them in the `enabled_modules` setting in your application.
  * Web assets (images, scripts, style sheets, etc.) are made available to the server. When you install a plug-in via the command line, symfony creates a symlink to the project `web/` directory if the system allows it, or copies the content of the module `web/` directory into the project one. If the plug-in is installed from an archive or a version control repository, you have to copy the plug-in `web/` directory by hand (as the `README` bundled with the plug-in should mention).

>**TIP**: Registering routing rules in a Plug-in
>A plug-in can add new rules to the routing system, but it is not recomandable to do it by using a custom `routing.yml` configuration file. This is because the order in which rules are defined is very important, and the simple cascade configuration system of YAML files in symfony would mess this order up. Instead, plug-ins need to register an event listener on the `routing.load_configuration` event and manually prepend rules in the listener:
>
>     [php]
>     // in plugins/myPlugin/config/config.php
>     $this->dispatcher->connect('routing.load_configuration', array('myPluginRouting', 'listenToRoutingLoadConfigurationEvent'));
>     
>     // in plugins/myPlugin/lib/myPluginRouting.php
>     class myPluginRouting
>     {
>       static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
>       {
>         $routing = $event->getSubject();
>         // add plug-in routing rules on top of the existing ones
>         $routing->prependRoute('my_route', new sfRoute('/my_plugin/:action', array('module' => 'myPluginAdministrationInterface')));
>       }
>     }
>

#### Manual Plug-In Setup

There are some elements that the `plugin:install` task cannot handle on its own, and which require manual setup during installation:

  * Custom application configuration can be used in the plug-in code (for instance, by using `sfConfig::get('app_myplugin_foo')`), but you can't put the default values in an `app.yml` file located in the plug-in `config/` directory. To handle default values, use the second argument of the `sfConfig::get()` method. The settings can still be overridden at the application level (see Listing 17-25 for an example).
  * Custom routing rules have to be added manually to the application `routing.yml`.
  * Custom filters have to be added manually to the application `filters.yml`.
  * Custom factories have to be added manually to the application `factories.yml`.

Generally speaking, all the configuration that should end up in one of the application configuration files has to be added manually. Plug-ins with such manual setup should embed a `README` file describing installation in detail.

#### Customizing a Plug-In for an Application

Whenever you want to customize a plug-in, never alter the code found in the `plugins/` directory. If you do so, you will lose all your modifications when you upgrade the plug-in. For customization needs, plug-ins provide custom settings, and they support overriding.

Well-designed plug-ins use settings that can be changed in the application `app.yml`, as Listing 17-20 demonstrates.

Listing 17-20 - Customizing a Plug-In That Uses the Application Configuration

    [php]
    // example plug-in code
    $foo = sfConfig::get('app_my_plugin_foo', 'bar');

    // Change the 'foo' default value ('bar') in the application app.yml
    all:
      my_plugin:
        foo:       barbar

The module settings and their default values are often described in the plug-in's `README` file.

You can replace the default contents of a plug-in module by creating a module of the same name in your own application. It is not really overriding, since the elements in your application are used instead of the ones of the plug-in. It works fine if you create templates and configuration files of the same name as the ones of the plug-ins.

On the other hand, if a plug-in wants to offer a module with the ability to override its actions, the `actions.class.php` in the plug-in module must be empty and inherit from an autoloading class, so that the method of this class can be inherited as well by the `actions.class.php` of the application module. See Listing 17-21 for an example.

Listing 17-21 - Customizing a Plug-In Action

    [php]
    // In myPlugin/modules/mymodule/lib/myPluginmymoduleActions.class.php
    class myPluginmymoduleActions extends sfActions
    {
      public function executeIndex()
      {
        // Some code there
      }
    }

    // In myPlugin/modules/mymodule/actions/actions.class.php

    require_once dirname(__FILE__).'/../lib/myPluginmymoduleActions.class.php';

    class mymoduleActions extends myPluginmymoduleActions
    {
      // Nothing
    }

    // In frontend/modules/mymodule/actions/actions.class.php
    class mymoduleActions extends myPluginmymoduleActions
    {
      public function executeIndex()
      {
        // Override the plug-in code there
      }
    }

>**SIDEBAR**
>Customizing the plug-in schema
>
>###Doctrine
>When building the model, Doctrine will look for all the `*schema.yml` in application's and plugins' `config/` directories, so a project schema can override a plugin schema. The merging process allows for addition or modification of table or columns. For instance, the following example shows how to add columns to a table defined in a plugin schema.
>
>     #Original schema, in plugins/myPlugin/config/schema.yml
>     Article:
>       columns:
>         name: string(50)
>
>     #Project schema, in config/schema.yml
>     Article:
>       columns:
>         stripped_title: string(50)
>
>     # Resulting schema, merged internally and used for model and sql generation
>     Article:
>       columns:
>         name: string(50)
>         stripped_title: string(50)
>
>
>
>###Propel
>When building the model, symfony will look for custom YAML files for each existing schema, including plug-in ones, following this rule:
>
>Original schema name                   | Custom schema name
>-------------------------------------- | ------------------------------
>config/schema.yml                      | schema.custom.yml
>config/foobar_schema.yml               | foobar_schema.custom.yml
>plugins/myPlugin/config/schema.yml     | myPlugin_schema.custom.yml
>plugins/myPlugin/config/foo_schema.yml | myPlugin_foo_schema.custom.yml
>
>Custom schemas will be looked for in the application's and plugins' `config/` directories, so a plugin can override another plugin's schema, and there can be more than one customization per schema.
>
>Symfony will then merge the two schemas based on each table's `phpName`. The merging process allows for addition or modification of tables, columns, and column attibutes. For instance, the next listing shows how a custom schema can add columns to a table defined in a plug-in schema.
>
>     # Original schema, in plugins/myPlugin/config/schema.yml
>     propel:
>       article:
>         _attributes:    { phpName: Article }
>         title:          varchar(50)
>         user_id:        { type: integer }
>         created_at:
>
>     # Custom schema, in myPlugin_schema.custom.yml
>     propel:
>       article:
>         _attributes:    { phpName: Article, package: foo.bar.lib.model }
>         stripped_title: varchar(50)
>
>     # Resulting schema, merged internally and used for model and sql generation
>     propel:
>       article:
>         _attributes:    { phpName: Article, package: foo.bar.lib.model }
>         title:          varchar(50)
>         user_id:        { type: integer }
>         created_at:
>         stripped_title: varchar(50)
>
>As the merging process uses the table's `phpName` as a key, you can even change the name of a plugin table in the database, provided that you keep the same `phpName` in the schema.

### How to Write a Plug-In

Only plug-ins packaged as PEAR packages can be installed with the `plugin:install` task. Remember that such plug-ins can be distributed via the symfony plugin section, a PEAR channel, or a simple file download. So if you want to author a plug-in, it is better to publish it as a PEAR package than as a simple archive. In addition, PEAR packaged plug-ins are easier to upgrade, can declare dependencies, and automatically deploy assets in the `web/` directory.

#### File Organization

Suppose you have developed a new feature and want to package it as a plug-in. The first step is to organize the files logically so that the symfony loading mechanisms can find them when needed. For that purpose, you have to follow the structure given in Listing 17-18. Listing 17-22 shows an example of file structure for an `sfSamplePlugin` plug-in.

Listing 17-22 - Example List of Files to Package As a Plug-In

    sfSamplePlugin/
      README
      LICENSE
      config/
        schema.yml
        sfSamplePluginConfiguration.class.php
      data/
        fixtures/
          fixtures.yml
      lib/
        model/
          sfSampleFooBar.php
          sfSampleFooBarPeer.php
        task/
          sfSampleTask.class.php
        validator/
          sfSampleValidator.class.php
      modules/
        sfSampleModule/
          actions/
            actions.class.php
          config/
            security.yml
          lib/
            BasesfSampleModuleActions.class.php
          templates/
            indexSuccess.php
      web/
        css/
          sfSampleStyle.css
        images/
          sfSampleImage.png

For authoring, the location of the plug-in directory (`sfSamplePlugin/` in Listing 17-22) is not important. It can be anywhere on the disk.

>**TIP**
>Take examples of the existing plug-ins and, for your first attempts at creating a plug-in, try to reproduce their naming conventions and file structure.

#### Creating the package.xml File

The next step of plug-in authoring is to add a package.xml file at the root of the plug-in directory. The `package.xml` follows the PEAR syntax. Have a look at a typical symfony plug-in `package.xml` in Listing 17-23.

Listing 17-23 - Example `package.xml` for a Symfony Plug-In

    [xml]
    <?xml version="1.0" encoding="UTF-8"?>
    <package packagerversion="1.4.6" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
     <name>sfSamplePlugin</name>
     <channel>plugins.symfony-project.org</channel>
     <summary>symfony sample plugin</summary>
     <description>Just a sample plugin to illustrate PEAR packaging</description>
     <lead>
      <name>Fabien POTENCIER</name>
      <user>fabpot</user>
      <email>fabien.potencier@symfony-project.com</email>
      <active>yes</active>
     </lead>
     <date>2006-01-18</date>
     <time>15:54:35</time>
     <version>
      <release>1.0.0</release>
      <api>1.0.0</api>
     </version>
     <stability>
      <release>stable</release>
      <api>stable</api>
     </stability>
     <license uri="http://www.symfony-project.org/license">MIT license</license>
     <notes>-</notes>
     <contents>
      <dir name="/">
       <file role="data" name="README" />
       <file role="data" name="LICENSE" />
       <dir name="config">
        <!-- model -->
        <file role="data" name="schema.yml" />
        <file role="data" name="ProjectConfiguration.class.php" />
       </dir>
       <dir name="data">
        <dir name="fixtures">
         <!-- fixtures -->
         <file role="data" name="fixtures.yml" />
        </dir>
       </dir>
       <dir name="lib">
        <dir name="model">
         <!-- model classes -->
         <file role="data" name="sfSampleFooBar.php" />
         <file role="data" name="sfSampleFooBarPeer.php" />
        </dir>
        <dir name="task">
         <!-- tasks -->
         <file role="data" name="sfSampleTask.class.php" />
        </dir>
        <dir name="validator">
         <!-- validators -->
         <file role="data" name="sfSampleValidator.class.php" />
        </dir>
       </dir>
       <dir name="modules">
        <dir name="sfSampleModule">
         <file role="data" name="actions/actions.class.php" />
         <file role="data" name="config/security.yml" />
         <file role="data" name="lib/BasesfSampleModuleActions.class.php" />
         <file role="data" name="templates/indexSuccess.php" />
        </dir>
       </dir>
       <dir name="web">
        <dir name="css">
         <!-- stylesheets -->
         <file role="data" name="sfSampleStyle.css" />
        </dir>
        <dir name="images">
         <!-- images -->
         <file role="data" name="sfSampleImage.png" />
        </dir>
       </dir>
      </dir>
     </contents>
     <dependencies>
      <required>
       <php>
        <min>5.2.4</min>
       </php>
       <pearinstaller>
        <min>1.4.1</min>
       </pearinstaller>
       <package>
        <name>symfony</name>
        <channel>pear.symfony-project.com</channel>
        <min>1.3.0</min>
        <max>1.5.0</max>
        <exclude>1.5.0</exclude>
       </package>
      </required>
     </dependencies>
     <phprelease />
     <changelog />
    </package>

The interesting parts here are the `<contents>` and the `<dependencies>` tags, described next. For the rest of the tags, there is nothing specific to symfony, so you can refer to the PEAR online [manual](http://pear.php.net/manual/en/) for more details about the `package.xml` format.

#### Contents

The `<contents>` tag is the place where you must describe the plug-in file structure. This will tell PEAR which files to copy and where. Describe the file structure with `<dir>` and `<file>` tags. All `<file>` tags must have a `role="data"` attribute. The `<contents>` part of Listing 17-23 describes the exact directory structure of Listing 17-22.

>**NOTE**
>The use of `<dir>` tags is not compulsory, since you can use relative paths as `name` values in the `<file>` tags. However, it is recommended so that the `package.xml` file remains readable.

#### Plug-In Dependencies

Plug-ins are designed to work with a given set of versions of PHP, PEAR, symfony, PEAR packages, or other plug-ins. Declaring these dependencies in the `<dependencies>` tag tells PEAR to check that the required packages are already installed, and to raise an exception if not.

You should always declare dependencies on PHP, PEAR, and symfony, at least the ones corresponding to your own installation, as a minimum requirement. If you don't know what to put, add a requirement for PHP 5.2.4, PEAR 1.4, and symfony 1.3.

It is also recommended to add a maximum version number of symfony for each plug-in. This will cause an error message when trying to use a plug-in with a more advanced version of the framework, and this will oblige the plug-in author to make sure that the plug-in works correctly with this version before releasing it again. It is better to have an alert and to download an upgrade rather than have a plug-in fail silently.

If you specify plugins as dependencies, users will be able to install your plugin and all its dependencies with a single command:

    $ php symfony plugin:install --install-deps sfSamplePlugin

#### Building the Plug-In

The PEAR component has a command (`pear package`) that creates the `.tgz` archive of the package, provided you call the command shown in Listing 17-24 from a directory containing a `package.xml`.

Listing 17-24 - Packaging a Plug-In As a PEAR Package

    $ cd sfSamplePlugin
    $ pear package

    Package sfSamplePlugin-1.0.0.tgz done

Once your plug-in is built, check that it works by installing it yourself, as shown in Listing 17-25.

Listing 17-25 - Installing the Plug-In

    $ cp sfSamplePlugin-1.0.0.tgz /home/production/myproject/
    $ cd /home/production/myproject/
    $ php symfony plugin:install sfSamplePlugin-1.0.0.tgz

According to their description in the `<contents>` tag, the packaged files will end up in different directories of your project. Listing 17-26 shows where the files of the `sfSamplePlugin` should end up after installation.

Listing 17-26 - The Plug-In Files Are Installed on the `plugins/` and `web/` Directories

    plugins/
      sfSamplePlugin/
        README
        LICENSE
        config/
          schema.yml
          sfSamplePluginConfiguration.class.php
        data/
          fixtures/
            fixtures.yml
        lib/
          model/
            sfSampleFooBar.php
            sfSampleFooBarPeer.php
          task/
            sfSampleTask.class.php
          validator/
            sfSampleValidator.class.php
        modules/
          sfSampleModule/
            actions/
              actions.class.php
            config/
              security.yml
            lib/
              BasesfSampleModuleActions.class.php
            templates/
              indexSuccess.php
    web/
      sfSamplePlugin/               ## Copy or symlink, depending on system
        css/
          sfSampleStyle.css
        images/
          sfSampleImage.png

Test the way the plug-in behaves in your application. If it works well, you are ready to distribute it across projects--or to contribute it to the symfony community.

#### Hosting Your Plug-In in the Symfony Project Website

A symfony plug-in gets the broadest audience when distributed by the `symfony-project.org` website. Even your own plug-ins can be distributed this way, provided that you follow these steps:

  1. Make sure the `README` file describes the way to install and use your plug-in, and that the `LICENSE` file gives the license details. Format your `README` with the [Markdown](http://daringfireball.net/projects/markdown/syntax) Formatting syntax.
  2. Create a symfony account (http://www.symfony-project.org/user/new) and create the plugin (http://www.symfony-project.org/plugins/new).
  3. Create a PEAR package for your plug-in by calling the `pear package` command, and test it. The PEAR package must be named `sfSamplePlugin-1.0.0.tgz` (1.0.0 is the plug-in version).
  4. Upload your PEAR package (`sfSamplePlugin-1.0.0.tgz`).
  5. Your plugin must now appear in the list of [plugins](http://www.symfony-project.org/plugins/).

If you follow this procedure, users will be able to install your plug-in by simply typing the following command in a project directory:

    $ php symfony plugin:install sfSamplePlugin

#### Naming Conventions

To keep the `plugins/` directory clean, ensure all the plug-in names are in camelCase and end with `Plugin` (for example, `shoppingCartPlugin`, `feedPlugin`, and so on). Before naming your plug-in, check that there is no existing plug-in with the same name.

>**NOTE**
>Plug-ins relying on Propel should contain `Propel` in the name (the same goes for if you use Doctrine). For instance, an authentication plug-in using the Propel data access objects should be called `sfPropelAuth`.

Plug-ins should always include a `LICENSE` file describing the conditions of use and the chosen license. You are also advised to add a `README` file to explain the version changes, purpose of the plug-in, its effect, installation and configuration instructions, etc.

Summary
-------

The symfony classes notify events that give them the ability to be modified at the application level. The event mechanism allows multiple inheritance and class overriding at runtime even if the PHP limitations forbid it. So you can easily extend the symfony features, even if you have to modify the core classes for that--the factories configuration is here for that.

Many such extensions already exist; they are packaged as plug-ins, to be easily installed, upgraded, and uninstalled through the symfony command line. Creating a plug-in is as easy as creating a PEAR package, and provides reusability across applications.

The symfony plugin section contains many plug-ins, and you can even add your own. So now that you know how to do it, we hope that you will enhance the symfony core with a lot of useful extensions!
