Leveraging the Power of the Command Line
========================================

*by Geoffrey Bachelet*

Symfony 1.1 introduced a modern, powerful, and flexible command line
system in replacement of the old pake-based tasks system. From version to
version, the tasks system has been improved to make it what it is today.

Many web developers won't see the added value in tasks. Often, those developers
don't realize the power of the command line. In this chapter, we are going to
dive into tasks, from the very beginning to more advanced usage, seeing how it
can help your everyday work, and how you can get the best from tasks.

Tasks at a Glance
-----------------

A task is a piece of code that is run from the command line using the `symfony`
php script at the root of your project. You may already have run into tasks
through the well-known `cache:clear` task (also known as `cc`) by running it in
a shell:

    $ php symfony cc

Symfony provides a set of general purpose built-in tasks for a variety of uses.
You can get a list of the available tasks by running the `symfony` script
without any arguments or options:

    $ php symfony

The output will look something like this (content truncated):

    Usage:
      symfony [options] task_name [arguments]

    Options:
      --help        -H  Display this help message.
      --quiet       -q  Do not log messages to standard output.
      --trace       -t  Turn on invoke/execute tracing, enable full backtrace.
      --version     -V  Display the program version.
      --color           Forces ANSI color output.
      --xml             To output help as XML

    Available tasks:
      :help                        Displays help for a task (h)
      :list                        Lists tasks
    app
      :routes                      Displays current routes for an application
    cache
      :clear                       Clears the cache (cc, clear-cache)

You may have already noticed that tasks are grouped. Groups of tasks are called
namespaces, and tasks name are generally composed of a namespace and a task name
(except for the  `help` and `list` tasks that don't have a namespace). This
naming scheme allows for easy task categorization, and you should choose a
meaningful namespace for each of your tasks.


Writing your own Tasks
----------------------

Getting started writing tasks with symfony takes only a few minutes. All
you have to do is create your task, name it, put some logic into it, and voilà,
you're ready to run your first custom task. Let's create a very simple *Hello,
World!* task, for example in `lib/task/sayHelloTask.class.php`:

    [php]
    class sayHelloTask extends sfBaseTask
    {
      public function configure()
      {
        $this->namespace = 'say';
        $this->name      = 'hello';
      }

      public function execute($arguments = array(), $options = array())
      {
        echo 'Hello, World!';
      }
    }

Now run it with the following command:

    $ php symfony say:hello

This task will only output *Hello, World!*, but it's only a start! Tasks are
not really meant to output content directly through the `echo` or `print`
statements. Extending `sfBaseTask` allows us to use a handful of helpful methods,
including the `log()` method, which does just what we want to do, output
content:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log('Hello, World!');
    }

Since a single task call can result in multiple tasks outputting content, you
may actually want to use the `logSection()` method:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, World!');
    }

Now, you might have already noticed the two arguments passed to the `execute()`
method, `$arguments` and `$options`. These are meant to hold all arguments and
options passed to your task at runtime. We will cover arguments and options
extensively later. For now, let's just add a bit of interactivity to our task
by allowing the user to specify who we want to say hello to:

    [php]
    public function configure()
    {
      $this->addArgument('who', sfCommandArgument::OPTIONAL, 'Who to say hello to?', 'World');
    }

    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, '.$arguments['who'].'!');
    }

Now the following command:

    $ php symfony say:hello Geoffrey

Should produce the following output:

    >> say       Hello, Geoffrey!

Wow, that was easy.

By the way, you might want to include a little more metadata in the tasks, like
what it does for example. You can do so by setting the `briefDescription` and
`detailedDescription` properties:

    [php]
    public function configure()
    {
      $this->namespace           = 'say';
      $this->name                = 'hello';
      $this->briefDescription    = 'Simple hello world';

      $this->detailedDescription = <<<EOF
    The [say:hello|INFO] task is an implementation of the classical
    Hello World example using symfony's task system.

      [./symfony say:hello|INFO]

    Use this task to greet yourself, or somebody else using
    the [--who|COMMENT] argument.
    EOF;

      $this->addArgument('who', sfCommandArgument::OPTIONAL, 'Who to say hello to?', 'World');
    }

As you can see, you can use a basic set of markup to decorate your description.
You can check the rendering using symfony's task help system:

    $ php symfony help say:hello

The Options System
------------------

Options in a symfony task are organized into two distinct sets, options and
arguments.

### Options

Options are those that you pass using hyphens. You can add them to your
command line in any order. They can either have a value or not, in
which case they act as a boolean. More often than not, options have both a
long and short form. The long form is usually invoked using two hyphens while
the short form requires only one hyphen.

Examples of common options are the help switch (`--help` or `-h`), the
verbosity switch (`--quiet` or `-q`) or the version switch (`--version` or
`-V`).

>**NOTE**
>Options are defined with an `sfCommandOption` class and stored in an
>`sfCommandOptionSet` class.

### Arguments

Arguments are just a piece of data that you append to your command line. They
must be specified in the same order in which they were defined, and you must
enclose them in quotes if you want to include a space in them (or you could
also escape the spaces). They can be either required or optional, in which case
you should specify a default value in the argument's definition.

>**NOTE**
>Obviously, arguments are defined with an `sfCommandArgument` class and stored in an
>`sfCommandArgumentSet` class.

### Default Sets

Every symfony task comes with a set of default options and arguments:

  * `--help` (-`H`): Displays this help message.
  * `--quiet` (`-q`): Do not log messages to standard output.
  * `--trace` `(-t`): Turns on invoke/execute tracing, enable full backtrace.
  * `--version` (`-V`): Displays the program version.
  * `--color`: Forces ANSI color output.

### Special Options

Symfony's task system understands two very special options, `application` and
`env`.

The `application` option is needed when you want access to an
`sfApplicationConfiguration` instance rather than just an `sfProjectConfiguration`.
instance. This is the case, for example, when you want to generate URLs, since
routing is generally associated to a specific application.

When an `application` option is passed to a task, symfony will automatically
detect it and create the corresponding `sfApplicationConfiguration` object instead of
the default `sfProjectConfiguration` object. Note that you can set a default value for
this option, hence saving you the hassle of having to pass an application by
hand each time you run the task.

The `env` option controls, obviously, the environment in which the task
executes. When no environment is passed, `test` is used by default. Just like
for `application`, you can set a default value for the `env` option that will
automatically be used by symfony.

Since `application` and `env` are not included in the default options set, you
have to add them by hand in your task:

    [php]
    public function configure()
    {
      $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      ));
    }

In this example, the `frontend` application will be automatically used, and
unless a different environment is specified, the task will run in the `dev`
environment.

Accessing the Database
----------------------

Having access to your database from inside a symfony task is just a matter of
instantiating an `sfDatabaseManager` instance:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
    }

You can also access the ORM's connection object directly:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase()->getConnection();
    }

But what if you have several connections defined in your `databases.yml`? You
could, for example, add a `connection` option to your task:

    [php]
    public function configure()
    {
      $this->addOption('connection', sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine');
    }

    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase(isset($options['connection']) ? $options['connection'] : null)->getConnection();
    }

As usual, you can set a default value for this option.

Voilà! You can now manipulate your models just as if you were in your symfony
application.

>**NOTE**
>Be careful when batch processing using your favorite ORM's objects. Both Propel and
>Doctrine suffer from a well known PHP bug related to cyclic references and the
>garbage collector that results in a memory leak. This has been partially fixed
>in PHP 5.3.

Sending Emails
--------------

One of the most common use for tasks is sending emails. Until symfony 1.3,
sending email was not really straightforward. But times have changed: symfony
now features full integration with [Swift Mailer](http://swiftmailer.org/), a
feature-rich PHP mailer library, so let's use it!

Symfony's task system exposes the mailer object through the
`sfCommandApplicationTask::getMailer()` method. That way, you can gain access
to the mailer and easily send emails:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $mailer  = $this->getMailer();
      $mailer->composeAndSend($from, $recipient, $subject, $messageBody);
    }

>**NOTE**
>Since the mailer's configuration is read from the application configuration,
>your task must accept an application option in order to be able to use the
>mailer.

-

>**NOTE**
>If you are using the spool strategy, emails are only sent when you call
>the `project:send-emails` task.

In most cases, you won't have your message's content sitting in a magical
`$messageBody` variable just waiting to be sent, you'll want to somehow
generate it. There is no preferred way in symfony to generate content for your
emails, but there are a couple tips you can follow to make your life easier:

### Delegate Content Generation

For example, create a protected method for your task that returns the content for the
email you're sending:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->getMailer()->composeAndsend($from, $recipient, $subject, $this->getMessageBody());
    }

    protected function getMessageBody()
    {
      return 'Hello, World';
    }

### Use Swift Mailer's Decorator Plugin

Swift Mailer features a plugin known as
[`Decorator`](http://swiftmailer.org/docs/decorator-plugin) that is basically a
very simple, yet efficient, template engine that can take recipient-specific
replacement value-pairs and apply them throughout all mails being sent.

See [Swift Mailer's documentation](http://swiftmailer.org/docs/) for more information.

### Use an external Templating Library

Integrating a third party templating library is easy. For example, you could
use the brand new templating component released as part of the Symfony
Components project. Just drop the component code somewhere in your project
(`lib/vendor/templating/` would be a good place), and put down the following
code in your task:

    [php]
    protected function getMessageBody($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine()
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_dir').'/templates/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

### Getting the best of both Worlds

There's still more that you can do. Swift Mailer's `Decorator` plugin is very handy
since it can manage replacements on a recipient-specific basis. It means that
you define a set of replacements for each of your recipients, and Swift Mailer
takes care of replacing tokens with the right value based on the recipient
of the mail being sent. Let's see how we can integrate this with the templating
component:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $message = Swift_Message::newInstance();

      // fetches a list of users
      foreach($users as $user)
      {
        $replacements[$user->getEmail()] = array(
          '{username}'      => $user->getEmail(),
          '{specific_data}' => $user->getSomeUserSpecificData(),
        );

        $message->addTo($user->getEmail());
      }

      $this->registerDecorator($replacements);

      $message
        ->setSubject('User specific data for {username}!')
        ->setBody($this->getMessageBody('user_specific_data'));

      $this->getMailer()->send($message);
    }

    protected function registerDecorator($replacements)
    {
      $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
    }

    protected function getMessageBody($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine($replacements = array())
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

With `apps/frontend/templates/emails/user_specific_data.php` containing the
following code:

    Hi {username}!

    We just wanted to let you know your specific data:

    {specific_data}

And that's it! You now have a fully featured template engine to build your
email content.

Generating URLs
---------------

Writing emails usually requires that you generate URLs based on your routing
configuration. Fortunately enough, generating URLs has been made easy in
symfony 1.3 since you can directly access the routing of the current
application from inside a task by using the `sfCommandApplicationTask::getRouting()`
method:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $routing = $this->getRouting();
    }

>**NOTE**
>Since the routing is application dependent, you have to make sure that your
>application has an application configuration available, otherwise you won't
>be able to generate URLs using the routing.
>
>See the *Special Options* section to learn how to automatically get an
>application configuration in your task.

Now that we have a routing instance, it's quite straightforward to generate a
URL using the `generate()` method:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('default', array('module' => 'foo', 'action' => 'bar'));
    }

The first argument is the route's name and the second is an array of parameters for the
route. At this point, we have generated a relative URL, which is most likely
not what we want. Unfortunately, generating absolute URLs in a task will not work
since we don't have an `sfWebRequest` object to rely on to fetch the HTTP host.

One simple way to solve this is to set the HTTP host in your
`factories.yml` configuration file:

    [yml]
    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true
          context:
            host: example.org

See the `context_host` setting? This is what the routing will use when asked
for an absolute URL:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('my_route', array(), true);
    }

Accessing the I18N System
-------------------------

Not all factories are as easily accessible as the mailer and the routing.
Should you need access to one of them, it's really not too hard to instantiate
them. For example, say you want to internationalize your tasks, you would then
want to access symfony's i18n subsystem. This is easily done using the
`sfFactoryConfigHandler`:

    [php]
    protected function getI18N($culture = 'en')
    {
      if (!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class  = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);
      }

      $this->i18n->setCulture($culture);

      return $this->i18n;
    }

Let's see what's going on here. First, we are using a simple caching
technique to avoid re-building the i18n component at each call. Then, using the
`sfFactoryConfigHandler`, we retrieve the component's configuration in order to
instantiate it. We finish by setting the culture configuration. The task now
has access to internationalization:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log($this->getI18N('fr')->__('some translated text!'));
    }

Of course, always passing the culture is not very handy, especially if you
don't need to change culture very often in your task. We will see how to
arrange that in the next section.

Refactoring your Tasks
----------------------

Since sending emails (and generating content for them) and generating URLs are
two very common task, it may be a good idea to create a base task that
provides these two features automatically for each task. This is fairly easy to
do. Create a base class inside your project, for example
`lib/task/sfBaseEmailTask.class.php`.

    [php]
    class sfBaseEmailTask extends sfBaseTask
    {
      protected function registerDecorator($replacements)
      {
        $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
      }

      protected function getMessageBody($template, $vars = array())
      {
        $engine = $this->getTemplateEngine();
        return $engine->render($template, $vars);
      }

      protected function getTemplateEngine($replacements = array())
      {
        if (is_null($this->templateEngine))
        {
          $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/templates/emails/%s.php');
          $this->templateEngine = new sfTemplateEngine($loader);
        }

        return $this->templateEngine;
      }
    }

While we're at it, we are going to automate the task's options setup. Add these
methods to the `sfBaseEmailTask` class:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    }

    protected function generateUrl($route, $params = array())
    {
      return $this->getRouting()->generate($route, $params, true);
    }

We use the `configure()` method to add common options to all extending tasks.
Unfortunately, any class extending `sfBaseEmailTask` will now have to call
`parent::configure` in its own `configure()` method, but that's really a minor
annoyance in regard of added value.

Now let's refactor the I18N access code from the previous section:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
      $this->addOption('culture', null, sfCommandOption::PARAMETER_REQUIRED, 'The culture', 'en');
    }

    protected function getI18N()
    {
      if (!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class  = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);

        $this->i18n->setCulture($this->commandManager->getOptionValue('culture'));
      }

      return $this->i18n;
    }

    protected function changeCulture($culture)
    {
      $this->getI18N()->setCulture($culture);
    }

    protected function process(sfCommandManager $commandManager, $options)
    {
      parent::process($commandManager, $options);
      $this->commandManager = $commandManager;
    }

We have a problem to solve here: it is not possible to access arguments and
options values outside `execute()`'s scope.  To fix that, we are simply
overloading the `process()` method to attach the options manager to the class.
The options manager is, as its name says, managing arguments and options for
the current task. For example, you can access options values via the
`getOptionValue()` method.

Executing a Task inside a Task
------------------------------

An alternative way to refactor your tasks is to embed a task inside another
task. This is made particularly easy through the
`sfCommandApplicationTask::createTask()` and
`sfCommandApplicationTask::runTask()` methods.

The `createTask()` method will create an instance of a task for you. Just pass it
a task name, just as if you were on the command line, and it will return you an
instance of the desired task, ready to be run:

    [php]
    $task = $this->createTask('cache:clear');
    $task->run();

But since we are lazy, the `runTask` does everything for us:

    [php]
    $this->runTask('cache:clear');

Of course, you can pass arguments and options (in this order):

    [php]
    $this->runTask('plugin:install', array('sfGuardPlugin'), array('install_deps' => true));

Embedding tasks is useful for composing powerful tasks from more simple tasks.
For example, you could combine several tasks in a `project:clean` task that you
would run after each deployment:

    [php]
    $tasks = array(
      'cache:clear',
      'project:permissions',
      'log:rotate',
      'plugin:publish-assets',
      'doctrine:build-model',
      'doctrine:build-forms',
      'doctrine:build-filters',
      'project:optimize',
      'project:enable',
    );

    foreach($tasks as $task)
    {
      $this->run($task);
    }

Manipulating the Filesystem
---------------------------

Symfony comes with a built-in simple filesystem abstraction (`sfFilesystem`)
that permits the execution of simple operations on files and directories. It is
accessible inside a task with `$this->getFilesystem()`. This abstraction
exposes the following methods:

* `sfFilesystem::copy()`, to copy a file
* `sfFilesystem::mkdirs()`, creates recursive directories
* `sfFilesystem::touch()`, to create a file
* `sfFilesystem::remove()`, to delete a file or directory
* `sfFilesystem::chmod()`, to change permissions on a file or directory
* `sfFilesystem::rename()`, to rename a file or directory
* `sfFilesystem::symlink()`, to create a link to a directory
* `sfFilesystem::relativeSymlink()`, to create a relative link to a directory
* `sfFilesystem::mirror()`, to mirror a complete file tree
* `sfFilesystem::execute()`, to execute an arbitrary shell command

It also exposes a very handy method that we are going to cover in the next
section: `replaceTokens()`.

Using Skeletons to generate Files
---------------------------------

Another common use for tasks is to generate files. Generating files can be made
easy using skeletons and the aforementioned method
`sfFilesystem::replaceTokens()`. As its name suggests, this methods replaces
tokens inside a set of files. That is, you pass it an array of file, a list of
tokens and it replaces every occurrence of each token with its
assigned value, for each file in the array.

To better understand how this is useful, we are going to partially rewrite an
existing task: `generate:module`. For the sake of clarity and brevity, we will
only look at the `execute` part of this task, assuming it has been configured
properly with all needed options. We will also skip validation.

Even before starting to write the task, we need to create a skeleton for the
directories and files we are going to create, and store it somewhere like
`data/skeleton/`:

    data/skeleton/
      module/
        actions/
          actions.class.php
        templates/

The `actions.class.php` skeleton could look like something like this:

    [php]
    class %moduleName%Actions extends %baseActionsClass%
    {
    }

The first step of our task will be to mirror the file tree at the right place:

    [php]
    $moduleDir = sfConfig::get('sf_app_module_dir').$options['module'];
    $finder    = sfFinder::type('any');
    $this->getFilesystem()->mirror(sfConfig::get('sf_data_dir').'/skeleton/module', $moduleDir, $finder);

Now let's replace the tokens in `actions.class.php`:

    [php]
    $tokens = array(
      'moduleName'       => $options['module'],
      'baseActionsClass' => $options['base-class'],
    );

    $finder = sfFinder::type('file');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '%', '%', $tokens);

And that's it, we generated our new module, using token replacing to customize
it.

>**NOTE**
>The built-in `generate:module` actually looks into `data/skeleton/` for
>alternative skeleton to use instead of the default ones, so watch your step!

Using a dry-run Option
----------------------

Often you want to be able to preview the result of a task before actually
running it. Here are a couple of tips on how to do so.

First, you should use a standard name, such as `dry-run`. Everyone will
recognize this for what it is. Until symfony 1.3, `sfCommandApplication` *did*
add a default `dry-run` option, but now it should be added by hand (possibly in
a base class, as demonstrated above):

    [php]
    $this->addOption(new sfCommandOption('dry-run', null, sfCommandOption::PARAMETER_NONE, 'Executes a dry run');

You would then invoke your task like this:

    ./symfony my:task --dry-run

The `dry-run` option indicates that the task should not make any change.

*Should not make any change*, remember this, they are the key words. When
running in `dry-run` mode, your task must leave the environment exactly as it
was before, including (but not limited to):

* The database: do not insert, update or delete records from your tables. You
  can use a transaction to achieve this.
* The filesystem: do not create, modify or delete files from your filesystem.
* Email sending: do not send emails, or send them to a debug address.

Here is a simple example of using the `dry-run` option:

    [php]
    $connection->beginTransaction();

    // modify your database

    if ($options['dry-run'])
    {
      $connection->rollBack();
    }
    else
    {
      $connection->commit();
    }

Writing unit Tests
------------------

Since tasks can achieve a variety of goals, unit testing them is not an easy
thing. As such, there's not one way to test tasks, but there are some
principles to follow that can help make your tasks more testable.

First, think of your task like a controller. Remember the rule about controller?
*Thin controllers, fat models*. That is, move all the business logic inside
your models, that way, you can test your models instead of the task, which is
way easier.

Once you think you can't get more logic into models, split your `execute()`
method into chunks of easily testable code, each residing in its own easily
accessible (read: public) method. Splitting your code has several benefits:

  1. it makes your task's `execute` more readable
  1. it makes your task more testable
  1. it makes your task more extendable

Be creative, don't hesitate to build a small specific environment for your
testing needs. And if you can't find any way to test that awesome task that
you just wrote, there are two possibilities: either you wrote it bad or
you should ask someone for his opinion. Also, you can always dig into
someone else's code to see how they test things (symfony's tasks are well tested
for example, even generators).

Helper Methods: Logging
-----------------------

Symfony's task system tries hard to make the developer's day easier, providing
handy helper method for common operations such as logging and user interaction.

One can easily log messages to `STDOUT` using the `log` family of methods:

  * `log`, accepts an array of messages
  * `logSection`, a bit more elaborate, formats your message with a prefix
    (first argument) and a message type (fourth argument). When you log something
    too long, like a file path, `logSection` will usually shrink your message,
    which can prove annoying. Use the third argument to specify a message max
    size that fits your message
  * `logBlock`, is the logging style used for exceptions. Here again, you can pass
    a formatting style

Available logging formats are `ERROR`, `INFO`, `COMMENT` and `QUESTION`. Don't
hesitate to try them to see what they look like.

Example usage:

    [php]
    $this->logSection('file+', $aVeryLongFileName, $this->strlen($aVeryLongFileName));

    $this->logBlock('Congratulations! You ran the task successfuly!', 'INFO');

Helper Methods: User Interaction
--------------------------------

Three more helpers are provided to ease user interaction:

  * `ask()`, basically prints a question and returns any user input

  * `askConfirmation()`, we ask the user for a confirmation, allowing `y` (yes) and
    `n` (no) as user input

  * `askAndValidate()`, a very useful method that prints a question and validates
    the user input through an `sfValidator` passed as the second argument. The third
    argument is an array of options in which you can pass a default value
    (`value`), a maximum number of attempts (`attempts`) and a formatting style
    (`style`).

For example, you can ask a user for his email address and validate it on the fly:

    [php]
    $email = $this->askAndValidate('What is your email address?', new sfValidatorEmail());

Bonus Round: Using Tasks with a Crontab
---------------------------------------

Most UNIX and GNU/Linux systems allows for task planning through a
mechanism known as *cron*. The *cron* checks a configuration file (a *crontab*)
for commands to run at a certain time. Symfony tasks can easily be integrated
into a crontab, and the `project:send-emails` task is a perfect candidate for
an example of that:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails

This configuration tells *cron* to run the `project:send-emails` every day at
3am and to send all possible output (that is, logs, errors, etc) to the address
*you@example.org*.

>**NOTE**
>For more information on the crontab configuration file format, type `man 5
>crontab` in a terminal.

You can, and should actually, pass arguments and options:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails --env=prod --application=frontend

>**NOTE**
>You should replace `/usr/bin/php` with the location of your PHP CLI binary.
>If you don't have this information, you can try `which php` on linux systems
>or `whereis php` on most other UNIX systems.

Bonus Round: Using STDIN
------------------------

Since tasks are run in a command line environment, you can access the standard
input stream (STDIN). The UNIX command line allows applications to interact
between each other by a variety of means, one of which is the *pipe*,
symbolized by the character *|*. The *pipe* allows you to pass an application's
output (know as *STDOUT*) to another application's standard input (known as
*STDIN*). These are made accessible in your tasks through PHP's special
constants `STDIN` and `STDOUT`. There's also a third standard stream, *STDERR*,
accessible through `STDERR`, meant to carry an applications' error messages.

So what can we do exactly with the standard input? Well, imagine you have an
application running on your server that would like to communicate with your
symfony application. You could of course have it communicate through HTTP, but
a more efficient way would be to pipe its output to a symfony task. Say the
application can send structured data (for example a PHP array serialization)
describing domain objects that you want to include into your database. You
could write the following task:

    [php]
    while ($content = trim(fgets(STDIN)))
    {
      if ($data = unserialize($content) !== false)
      {
        $object = new Object();
        $object->fromArray($data);
        $object->save();
      }
    }

You would then use it like this:

    /usr/bin/data_provider | ./symfony data:import

`data_provider` being the application providing new domain objects, and
`data:import` being the task you just wrote.

Final Thoughts
--------------

What tasks can achieve is limited only by your imagination. Symfony's task
system is powerful and flexible enough that you can do merely anything you can
think off. Add to that the power of an UNIX shell, and you are really going to
love tasks.
