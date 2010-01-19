Playing with symfony's Config Cache
===================================

*by Kris Wallsmith*

One of my personal goals as a symfony developer is to streamline each of my
peer's workflow as much as possible on any given project. While I may know
our codebase inside and out, that's not a reasonable expectation for everyone
on the team. Thankfully, symfony provides mechanisms for isolating and
centralizing functionality within a project, making it easy for others to make
changes with a very light footprint.

Form Strings
------------

An excellent example of this is the symfony form framework. The form framework
is a powerful component of symfony that gives you great control over your
forms by moving their rendering and validation into PHP objects. This is a
godsend for the application developer, because it means you can encapsulate
complex logic in a single form class and extend and reuse it in multiple
places.

However, from a template developer's perspective, this abstraction of how a
form renders can be troublesome. Take a look at the following form:

![Form in its default state](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_default.png)

The class that configures this form looks something like this:

    [php]
    // lib/form/CommentForm.class.php
    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array(
          'min_length' => 12,
        )));
      }
    }

The form is then rendered in a PHP template like this:

    <!-- apps/frontend/modules/main/templates/indexSuccess.php -->
    <form action="#" method="post">
      <ul>
        <li>
          <?php echo $form['body']->renderLabel() ?>
          <?php echo $form['body'] ?>
          <?php echo $form['body']->renderError() ?>
        </li>
      </ul>
      <p><button type="submit">Post your comment now</button></p>
    </form>

The template developer has quite a bit of control over how this form is
rendered. He can change the default labels to be a bit more friendly:

    <?php echo $form['body']->renderLabel('Please enter your comment') ?>

He can add a class to the input fields:

    <?php echo $form['body']->render(array('class' => 'comment')) ?>

These modifications are intuitive and easy. But what if he needs to modify an
error message?

![Form in its error state](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_error.png)

The `->renderError()` method does not accept any arguments, so the template
developer's only recourse is to open the form's class file, find the code that
creates the validator in question, and modify its constructor so the new error
messages are associated with the appropriate error codes.

In our example, the template developer would have to make the following
change:

    [php]
    // before
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    )));

    // after
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    ), array(
      'min_length' => 'You haven't written enough',
    )));

Notice a problem? Oops! I used an apostrophe inside a single-quoted string. Of
course you or I would never make such a silly mistake, but what's to say a
template developer mucking around inside a form class won't?

In all seriousness, can we expect template developers to know their way around
the symfony form framework well enough to pinpoint exactly where an error
message is defined? Should someone working in the view layer be expected to
know the signature for a validator's constructor?

I'm pretty sure we can all agree that the answer to these questions is no.
Template developers do a lot of valuable work but it's simply unreasonable to
expect someone who isn't writing application code to learn the inner-workings
of the symfony form framework.

YAML: A Solution
----------------

To simplify the process of editing form strings we are going to add a layer of
YAML configuration that enhances each form object as it's passed to the view.
This configuration file will look something like this:

    [yml]
    # config/forms.yml
    CommentForm:
      body:
        label:        Please enter your comment
        attributes:   { class: comment }
        errors:
          min_length: You haven't written enough

This is a lot easier, right? The configuration explains itself, plus the
apostrophe issue we encountered earlier is now moot. So let's build it!

Filtering Template Variables
----------------------------

The first challenge is to find a hook in symfony that will allow us to filter
every form variable passed to a template through this configuration. To do
this, we use the `template.filter_parameters` event, which is fired from the
symfony core just prior to rendering a template or template partial.

    [php]
    // lib/form/sfFormYamlEnhancer.class.php
    class sfFormYamlEnhancer
    {
      public function connect(sfEventDispatcher $dispatcher)
      {
        $dispatcher->connect('template.filter_parameters',
          array($this, 'filterParameters'));
      }

      public function filterParameters(sfEvent $event, $parameters)
      {
        foreach ($parameters as $name => $param)
        {
          if ($param instanceof sfForm && !$param->getOption('is_enhanced'))
          {
            $this->enhance($param);
            $param->setOption('is_enhanced', true);
          }
        }

        return $parameters;
      }

      public function enhance(sfForm $form)
      {
        // ...
      }
    }

>**NOTE**
>Notice this code checks an `is_enhanced` option on each form object before
>enhancing it. This is to prevent forms passed from templates to partials from
>being enhanced twice.

This enhancer class needs to be connected from your application configuration:

    [php]
    // apps/frontend/config/frontendConfiguration.class.php
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function initialize()
      {
        $enhancer = new sfFormYamlEnhancer($this->getConfigCache());
        $enhancer->connect($this->dispatcher);
      }
    }

Now that we're able to isolate form variables just before they're passed to a
template or partial we have everything we need to make this work. The final
task is to apply what's been configured in the YAML.

Applying the YAML
-----------------

The easiest way to apply this YAML configuration to each form is to load it
into an array and loop through each configuration:

    [php]
    public function enhance(sfForm $form)
    {
      $config = sfYaml::load(sfConfig::get('sf_config_dir').'/forms.yml');

      foreach ($config as $class => $fieldConfigs)
      {
        if ($form instanceof $class)
        {
          foreach ($fieldConfigs as $fieldName => $fieldConfig)
          {
            if (isset($form[$fieldName]))
            {
              if (isset($fieldConfig['label']))
              {
                $form->getWidget($fieldName)->setLabel($fieldConfig['label']);
              }

              if (isset($fieldConfig['attributes']))
              {
                $form->getWidget($fieldName)->setAttributes(array_merge(
                  $form->getWidget($fieldName)->getAttributes(),
                  $fieldConfig['attributes']
                ));
              }

              if (isset($fieldConfig['errors']))
              {
                foreach ($fieldConfig['errors'] as $code => $msg)
                {
                  $form->getValidator($fieldName)->setMessage($code, $msg);
                }
              }
            }
          }
        }
      }
    }

There are a number of problems with this implementation. First, the YAML file
is read from the filesystem and loaded into `sfYaml` every time a form is
enhanced. Reading from the filesystem in this fashion should be avoided.
Second, there are multiple levels of nested loops and a number of conditionals
that will only slow your application down. The solution for both of these
problems lies in symfony's config cache.

The Config Cache
----------------

The config cache is composed of a collection of classes that optimize the use
of YAML configuration files by automating their translation into PHP code and
writing that code to the cache directory for execution. This mechanism will
eliminate the overhead necessary to load the contents of our configuration
file into `sfYaml` before applying its values.

Let's implement a config cache for our form enhancer. Instead of loading
`forms.yml` into `sfYaml`, let's ask the current application's config cache
for a pre-processed version.

To do this the `sfFormYamlEnhancer` class will need access to the current
application's config cache, so we'll add that to the constructor.

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfSimpleYamlConfigHandler');
      }

      // ...
    }

The config cache needs to be told what to do when a certain configuration file
is requested by the application. For now we've instructed the config cache to
use `sfSimpleYamlConfigHandler` to process `forms.yml`. This config handler
simply parses YAML into an array and caches it as PHP code.

With the config cache in place and a config handler registered for `forms.yml`
we can now call it instead of `sfYaml`:

    [php]
    public function enhance(sfForm $form)
    {
      $config = include $this->configCache->checkConfig('config/forms.yml');

      // ...
    }

This is much better. Not only have we eliminated the overhead of parsing YAML
on all but the first request, we've also switched to using `include`, which
exposes this read to the boons of op-code caching.

>**SIDEBAR**
>Development vs. Production environments
>
>The internals of `->checkConfig()` differ depending on whether your
>application's debug mode is on or off. In your `prod` environment, when debug
>mode is off, this method functions as described here:
>
>  * Check for a cached version of the requested file
>    * If if exists, return the path to that cached file
>    * If it doesn't exist:
>      * Process the configuration file
>      * Save the resulting code to the cache
>      * Return the path to the newly cached file
>
>This method functions differently when debug mode is on. Because config files
>are edited during the course of development, `->checkConfig()` will compare
>when the original and cached files were last modified to make sure it gets
>the latest version. This adds a few more steps to how the same method
>functions when debug mode is off:
>
>  * Check for a cached version of the requested file
>    * If it doesn't exist:
>      * Process the configuration file
>      * Save the resulting code to the cache
>    * If it exists:
>      * Compare when the config and cached files were last modified
>      * If the config file was modified most recently:
>        * Process the configuration file
>        * Save the resulting code to the cache
>  * Return the path to the cached file

Cover me, I'm goin' in!
-----------------------

Let's write some tests before going any further. We can start with this basic
script:

    [php]
    // test/unit/form/sfFormYamlEnhancerTest.php
    include dirname(__FILE__).'/../../bootstrap/unit.php';

    $t = new lime_test(3);

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());
    sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

    $enhancer = new sfFormYamlEnhancer($configuration->getConfigCache());

    // ->enhance()
    $t->diag('->enhance()');

    $form = new CommentForm();
    $form->bind(array('body' => '+1'));

    $enhancer->enhance($form);

    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() enhances labels');
    $t->like($form['body']->render(), '/class="comment"/',
      '->enhance() enhances widgets');
    $t->like($form['body']->renderError(), '/You haven\'t written enough/',
      '->enhance() enhances error messages');

Running this test against the current `sfFormYamlEnhancer` verifies that it is
working correctly:

![Tests passing](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_3_ok.png)

Now we can go about refactoring with the confidence that our tests will raise
a stink if we break anything.

Custom Config Handlers
----------------------

In the enhancer code above, every form variable passed to a template will loop
through every form class configured in `forms.yml`. This gets the job done,
but if you pass multiple form objects to a template, or have a long list of
forms configured in the YAML, you may begin to see an impact on performance.
This is a good opportunity to write a custom config handler that optimizes
this process.

>**SIDEBAR**
>Why go custom?
>
>Writing a custom config handler is not for the faint of heart. As with any
>code generation, config handlers can be error-prone and difficult to test,
>but the benefits can be plentiful. Creating "hard-coded" logic on-the-fly
>hits a sweet spot that gives you the advantage of YAML's flexibility and the
>low-overhead of native PHP code. With an op-code cache added to the mix (such
>as [APC](http://pecl.php.net/apc) or [XCache](http://xcache.lighttpd.net/))
>config handlers are hard to beat for ease of use and performance.

Most of the magic of config handlers happens behind the scenes. The config
cache takes care of the caching logic before it runs any particular config
handler so we can just focus on generating the code necessary to apply the
YAML configuration.

Each config handler must implement the following two methods:

 * `static public function getConfiguration(array $configFiles)`
 * `public function execute($configFiles)`

The first method, `::getConfiguration()`, is passed an array of file paths,
parses them and merges their contents into a single value. In the
`sfSimpleYamlConfigHandler` class we used above, this method includes only one
line:

    [php]
    static public function getConfiguration(array $configFiles)
    {
      return self::parseYamls($configFiles);
    }

The `sfSimpleYamlConfigHandler` class extends the abstract
`sfYamlConfigHandler` which includes a number of helper methods for processing
YAML configuration files:

 * `::parseYamls($configFiles)`
 * `::parseYaml($configFile)`
 * `::flattenConfiguration($config)`
 * `::flattenConfigurationWithEnvironment($config)`

The first two methods implement symfony's
[configuration cascade](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_configuration_cascade).
The second two implement
[environment-awareness](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_environment_awareness).

The `::getConfiguration()` method in our config handler will need a custom
method for merging the configuration based on class inheritance. Create an
`::applyInheritance()` method that encapsulates this logic:

    [php]
    // lib/config/sfFormYamlEnhancementsConfigHander.class.php
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $config = self::getConfiguration($configFiles);

        // compile data
        $retval = "<?php\n".
                  "// auto-generated by %s\n".
                  "// date: %s\nreturn %s;\n";
        $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'),
          var_export($config, true));

        return $retval;
      }

      static public function getConfiguration(array $configFiles)
      {
        return self::applyInheritance(self::parseYamls($configFiles));
      }

      static public function applyInheritance($config)
      {
        $classes = array_keys($config);

        $merged = array();
        foreach ($classes as $class)
        {
          if (class_exists($class))
          {
            $merged[$class] = $config[$class];
            foreach (array_intersect(class_parents($class), $classes) as $parent)
            {
              $merged[$class] = sfToolkit::arrayDeepMerge(
                $config[$parent],
                $merged[$class]
              );
            }
          }
        }

        return $merged;
      }
    }

We now have an array whose values have been merged per class inheritance. This
eliminates the need to filter the entire configuration through `instanceof`
for each form object. What's more, this merge is done in the config handler so
it will only happen once and then be cached.

Now we can apply this merged array to a form object with a simple bit of
search logic:

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfFormYamlEnhancementsConfigHander');
      }

      // ...

      public function enhance(sfForm $form)
      {
        $config = include $this->configCache->checkConfig('config/forms.yml');

        $class = get_class($form);
        if (isset($config[$class]))
        {
          $fieldConfigs = $config[$class];
        }
        else if ($overlap = array_intersect(class_parents($class),
          array_keys($config)))
        {
          $fieldConfigs = $config[current($overlap)];
        }
        else
        {
          return;
        }

        foreach ($fieldConfigs as $fieldName => $fieldConfig)
        {
          // ...
        }
      }
    }

Before we run the test script again, let's add an assertion for the new class
inheritance logic.

    [yml]
    # config/forms.yml

    # ...

    BaseForm:
      body:
        errors:
          min_length: A base min_length message
          required:   A base required message

We can verify that the new `required` message is being applied in the test
script, and confirm that child forms will receive their parents' enhancements,
even if there are none configured for the child class.

    [php]
    $t = new lime_test(5);

    // ...

    $form = new CommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderError(), '/A base required message/',
      '->enhance() considers inheritance');

    class SpecialCommentForm extends CommentForm { }
    $form = new SpecialCommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() applies parent config');

Run this updated test script to verify the form enhancer is working as
expected.

![Tests passing](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_5_ok.png)

Getting Fancy with Embedded Forms
---------------------------------

There is an important feature of the symfony form framework we haven't
considered yet: embedded forms. If an instance of `CommentForm` is embedded in
another form, the enhancements we've made in `forms.yml` will not be applied.
This is easy enough to demonstrate in our test script:

    [php]
    $t = new lime_test(6);

    // ...

    $form = new BaseForm();
    $form->embedForm('comment', new CommentForm());
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['comment']['body']->renderLabel(),
      '/Please enter your comment/',
      '->enhance() enhances embedded forms');

This new assertion demonstrates that embedded forms are not being enhanced:

![Tests failing](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_not_ok.png)

Fixing this test will involve a more advanced config handler. We need to be
able to apply the enhancements configured in `forms.yml` in a modular way to
account for embedded forms, so we are going to generate a tailored enhancer
method for each configured form class. These methods will be generated by our
custom config handler in a new "worker" class.

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      // ...

      protected function getEnhancerCode($fields)
      {
        $code = array();
        foreach ($fields as $field => $config)
        {
          $code[] = sprintf('if (isset($fields[%s]))', var_export($field, true));
          $code[] = '{';

          if (isset($config['label']))
          {
            $code[] = sprintf('  $fields[%s]->getWidget()->setLabel(%s);',
              var_export($config['label'], true));
          }

          if (isset($config['attributes']))
          {
            $code[] = '  $fields[%s]->getWidget()->setAttributes(array_merge(';
            $code[] = '    $fields[%s]->getWidget()->getAttributes(),';
            $code[] = '    '.var_export($config['attributes'], true);
            $code[] = '  ));';
          }

          if (isset($config['errors']))
          {
            $code[] = sprintf('  if ($error = $fields[%s]->getError())',
              var_export($field, true));
            $code[] = '  {';
            $code[] = '    $error->getValidator()->setMessages(array_merge(';
            $code[] = '      $error->getValidator()->getMessages(),';
            $code[] = '      '.var_export($config['errors'], true);
            $code[] = '    ));';
            $code[] = '  }';
          }

          $code[] = '}';
        }

        return implode(PHP_EOL.'    ', $code);
      }
    }

Notice how the config array is checked for certain keys when the code is
generated, rather than at runtime. This will provide a small performance
boost.

>**TIP**
>As a general rule, logic that checks conditions of the configuration should
>be run in the config handler, not in the generated code. Logic that checks
>runtime conditions, such as the nature of the form object being enhanced,
>must be run at runtime.

This generated code is placed inside a class definition, which is then saved
to the cache directory.

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $forms = self::getConfiguration($configFiles);

        $code = array();
        $code[] = '<?php';
        $code[] = '// auto-generated by '.__CLASS__;
        $code[] = '// date: '.date('Y/m/d H:is');
        $code[] = 'class sfFormYamlEnhancementsWorker';
        $code[] = '{';
        $code[] = '  static public $enhancable = '.var_export(array_keys($forms), true).';';

        foreach ($forms as $class => $fields)
        {
          $code[] = '  static public function enhance'.$class.'(sfFormFieldSchema $fields)';
          $code[] = '  {';
          $code[] = '    '.$this->getEnhancerCode($fields);
          $code[] = '  }';
        }

        $code[] = '}';

        return implode(PHP_EOL, $code);
      }

      // ...
    }

The `sfFormYamlEnhancer` class will now defer to the generated worker class to
handle manipulation of form objects, but must now account for recursion
through embedded forms. To do this we must process the form's field schema
(which can be iterated through recursively) and the form object (which
includes the embedded forms) in parallel.

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      protected function doEnhance(sfFormFieldSchema $fieldSchema, sfForm $form)
      {
        if ($enhancer = $this->getEnhancer(get_class($form)))
        {
          call_user_func($enhancer, $fieldSchema);
        }

        foreach ($form->getEmbeddedForms() as $name => $form)
        {
          if (isset($fieldSchema[$name]))
          {
            $this->doEnhance($fieldSchema[$name], $form);
          }
        }
      }

      public function getEnhancer($class)
      {
        if (in_array($class, sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.$class);
        }
        else if ($overlap = array_intersect(class_parents($class),
          sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.current($overlap));
        }
      }
    }

>**NOTE**
>The fields on embedded form objects should not be modified after they've been
>embedded. Embedded forms are stored in the parent form for processing
>purposes, but have no effect on how the parent form is rendered.

With support for embedded forms in place, our tests should now be passing. Run
the script to find out:

![Tests passing](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_ok.png)

How'd we do?
------------

Let's run some benchmarks just to be sure we haven't wasted our time. To make
the results interesting, add a few more form classes to `forms.yml` using a
PHP loop.

    [yml]
    # <?php for ($i = 0; $i < 100; $i++): ?> #
    Form<?php echo $i ?>: ~
    # <?php endfor; ?> #

Create all these classes by running the following snippet of code:

    [php]
    mkdir($dir = sfConfig::get('sf_lib_dir').'/form/test_fixtures');
    for ($i = 0; $i < 100; $i++)
    {
      file_put_contents($dir.'/Form'.$i.'.class.php',
        '<?php class Form'.$i.' extends BaseForm { }');
    }

Now we're ready to run some benchmarks. For the results below, I've ran the
following [Apache](http://httpd.apache.org/docs/2.0/programs/ab.html) command
on my MacBook multiple times until I got a standard deviation of less than
2ms.

    $ ab -t 60 -n 20 http://localhost/config_cache/web/index.php

Start with a baseline benchmark for running the application without the
enhancer connected at all. Comment out `sfFormYamlEnhancer` in
`frontendConfiguration` and run the benchmark:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.5     63      69
    Waiting:       62   63   1.5     63      69
    Total:         62   63   1.5     63      69

Next, paste the first version of `sfFormYamlEnhancer::enhance()` that called
`sfYaml` directly into the class and run the benchmark:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    87   88   1.6     88      93
    Waiting:       87   88   1.6     88      93
    Total:         87   88   1.7     88      94

You can see we've added an average of 25ms to each request, an increase of
almost 40%. Next, undo the change you just made to `->enhance()` so our custom
config handler is restored and run the benchmark again:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.6     63      70
    Waiting:       62   63   1.6     63      70
    Total:         62   64   1.6     63      70

As you can see, we've reduced processing time back to the baseline by creating
a custom config handler.

Just For Fun: Bundling a Plugin
-------------------------------

Now that we have this great system in place for enhancing form objects with a
simple YAML configuration file, why not bundle it up as a plugin and share it
with the community. This may sound intimidating to those who haven't published
a plugin in the past; hopefully we can dispell some of that fear now.

This plugin will have the following file structure:

    sfFormYamlEnhancementsPlugin/
      config/
        sfFormYamlEnhancementsPluginConfiguration.class.php
      lib/
        config/
          sfFormYamlEnhancementsConfigHander.class.php
        form/
          sfFormYamlEnhancer.class.php
      test/
        unit/
          form/
            sfFormYamlEnhancerTest.php

We need to make a few modifications to ease the plugin installation process.
Creation and connection of the enhancer object can be encapsulated in the
plugin configuration class:

    [php]
    class sfFormYamlEnhancementsPluginConfiguration extends sfPluginConfiguration
    {
      public function initialize()
      {
        if ($this->configuration instanceof sfApplicationConfiguration)
        {
          $enhancer = new sfFormYamlEnhancer($this->configuration->getConfigCache());
          $enhancer->connect($this->dispatcher);
        }
      }
    }

The test script will need to be updated to reference the project's bootstrap
script:

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    // ...

Finally, enable the plugin in `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfFormYamlEnhancementsPlugin');
      }
    }

If you want to run tests from the plugin, connect them in
`ProjectConfiguration` now:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function setupPlugins()
      {
        $this->pluginConfigurations['sfFormYamlEnhancementsPlugin']->connectTests();
      }
    }

Now the tests from the plugin will run when you call any of the `test:*`
tasks.

![Plugin tests](http://www.symfony-project.org/images/more-with-symfony/config_cache_plugin_tests.png)

All of our classes are now located in the new plugin's directory, but there is
one problem: the test script relies on files that are still located in the
project. This means that anyone else who may want to run these tests would not
be able to unless they have the same files in their project.

To fix this we'll need to isolate the code in the enhancer class that calls
the config cache so we can overload it in our test script and instead use a
`forms.yml` fixture.

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        $this->loadWorker();
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      public function loadWorker()
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
      }

      // ...
    }

We can then overload the `->loadWorker()` method in our test script to call
the custom config handler directly. The `CommentForm` class should also be
moved to the test script and the `forms.yml` file moved to the plugin's
`test/fixtures` directory.

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    $t = new lime_test(6);

    class sfFormYamlEnhancerTest extends sfFormYamlEnhancer
    {
      public function loadWorker()
      {
        if (!class_exists('sfFormYamlEnhancementsWorker', false))
        {
          $configHandler = new sfFormYamlEnhancementsConfigHander();
          $code = $configHandler->execute(array(dirname(__FILE__).'/../../fixtures/forms.yml'));

          $file = tempnam(sys_get_temp_dir(), 'sfFormYamlEnhancementsWorker');
          file_put_contents($file, $code);

          require $file;
        }
      }
    }

    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
      }
    }

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());

    $enhancer = new sfFormYamlEnhancerTest($configuration->getConfigCache());

    // ...

Finally, packaging the plugin is easy with `sfTaskExtraPlugin` installed. Just
run the `plugin:package` task and a package will be created after a few
interactive prompts.

    $ php symfony plugin:package sfFormYamlEnhancementsPlugin

>**NOTE**
>The code in this article has been published as a plugin and is available to
>download from the symfony plugins site:
>
>    http://symfony-project.org/plugins/sfFormYamlEnhancementsPlugin
>
>This plugin includes what we've covered here and much more, including support
>for `widgets.yml` and `validators.yml` files as well as integration with the
>`i18n:extract` task for easy internationalization of your forms.

Final Thoughts
--------------

As you can see by the benchmarks done here, the symfony config cache makes it
possible to utilize the simplicity of YAML configuration files with virtually
no impact on performance.
