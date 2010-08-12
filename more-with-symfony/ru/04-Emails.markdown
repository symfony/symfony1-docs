Emails
======

*by Fabien Potencier*

Sending ~emails~ with symfony is simple and powerful, thanks to the usage of
the [Swift Mailer](http://www.swiftmailer.org/) library. Although ~Swift Mailer~
makes sending emails easy, symfony provides a thin wrapper on top of it to
make sending emails even more flexible and powerful. This chapter will teach
you how to put all the power at your disposal.

>**NOTE**
>symfony 1.3 embeds Swift Mailer version 4.1.

Introduction
------------

Email management in symfony is centered around a mailer object. And like many
other core symfony objects, the mailer is a factory. It is configured in the
`factories.yml` configuration file, and always available via the context
instance:

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>Unlike other factories, the mailer is loaded and initialized on demand. If
>you don't use it, there is no performance impact whatsoever.

This tutorial explains the Swift Mailer integration in symfony. If you want to
learn the nitty-gritty details of the Swift Mailer library itself, refer to its
dedicated [documentation](http://www.swiftmailer.org/docs).

Sending Emails from an Action
-----------------------------

From an action, retrieving the mailer instance is made simple with the
`getMailer()` shortcut method:

    [php]
    $mailer = $this->getMailer();

### The Fastest Way

Sending an email is then as simple as using the ~`sfMailer::composeAndSend()`~
method:

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

The `composeAndSend()` method takes four arguments:

 * the sender email address (`from`);
 * the recipient email address(es) (`to`);
 * the subject of the message;
 * the body of the message.

Whenever a method takes an email address as a parameter, you can pass a string
or an array:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

Of course, you can send an email to several people at once by passing an array
of emails as the second argument of the method:

    [php]
    $to = array(
      'foo@example.com',
      'bar@example.com',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

    $to = array(
      'foo@example.com' => 'Mr Foo',
      'bar@example.com' => 'Miss Bar',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

### The Flexible Way

If you need more flexibility, you can also use the ~`sfMailer::compose()`~
method to create a message, customize it the way you want, and eventually send
it. This is useful, for instance, when you need to add an
~attachment|email attachment~ as shown below:

    [php]
    // create a message object
    $message = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // send the message
    $this->getMailer()->send($message);

### The Powerful Way

You can also create a message object directly for even more flexibility:

    [php]
    $message = Swift_Message::newInstance()
      ->setFrom('from@example.com')
      ->setTo('to@example.com')
      ->setSubject('Subject')
      ->setBody('Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    $this->getMailer()->send($message);

>**TIP**
>The ["Creating Messages"](http://swiftmailer.org/docs/messages) and
>["Message Headers"](http://swiftmailer.org/docs/headers) sections of the
>Swift Mailer official documentation describe all you need to know about
>creating messages.

### Using the Symfony View

Sending your emails from your actions allows you to leverage the power of
partials and components quite easily.

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

Configuration
-------------

As any other symfony factory, the mailer can be configured in the
`factories.yml` configuration file. The default configuration reads as
follows:

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

When creating a new application, the local `factories.yml` configuration file
overrides the default configuration with some sensible defaults for the
`prod`, `env`, and `test` environments:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

The Delivery Strategy
---------------------

One of the most useful feature of the Swift Mailer integration in symfony is
the delivery strategy. The delivery strategy allows you to tell symfony how to
deliver email messages and is configured via the ~`delivery_strategy`~ setting
of `factories.yml`. The strategy changes the way the
~`send()`|`sfMailer::send()`~ method behaves. Four strategies are available by
default, which should suit all the common needs:

 * `realtime`:       Messages are sent in realtime.
 * `single_address`: Messages are sent to a single address.
 * `spool`:          Messages are stored in a queue.
 * `none`:           Messages are simply ignored.

### The ~`realtime`~ Strategy

The `realtime` strategy is the default delivery strategy, and the easiest to
setup as there is nothing special to do.

Email messages are sent via the transport configured in the `transport`
section of the `factories.yml` configuration file (see the next section for
more information about how to configure the mail transport).

### The ~`single_address`~ Strategy

With the `single_address` strategy, all messages are sent to a single address,
configured via the `delivery_address` setting.

This strategy is really useful in the development environment to avoid sending
messages to real users, but still allow the developer to check the rendered
message in an email reader.

>**TIP**
>If you need to verify the original `to`, `cc`, and `bcc` recipients, they are
>available as values of the following headers: `X-Swift-To`, `X-Swift-Cc`, and
>`X-Swift-Bcc` respectively.

Email messages are sent via the same email transport as the one used for the
`realtime` strategy.

### The ~`spool`~ Strategy

With the `spool` strategy, messages are stored in a queue.

This is the best strategy for the production environment, as web requests do
not wait for the emails to be sent.

The `spool` class is configured with the ~`spool_class`~ setting. By default,
symfony comes bundled with three of them:

 * ~`Swift_FileSpool`~: Messages are stored on the filesystem.

 * ~`Swift_DoctrineSpool`~: Messages are stored in a Doctrine model.

 * ~`Swift_PropelSpool`~: Messages are stored in a Propel model.

When the spool is instantiated, the ~`spool_arguments`~ setting is used as the
constructor arguments. Here are the options available for the built-in queues
classes:

 * `Swift_FileSpool`:

    * The absolute path of the queue directory (messages are stored in
      this directory)

 * `Swift_DoctrineSpool`:

    * The Doctrine model to use to store the messages (`MailMessage` by
      default)

    * The column name to use for message storage (`message` by default)

    * The method to call to retrieve the messages to send (optional). It
      receives the queue options as a argument.

 * `Swift_PropelSpool`:

    * The Propel model to use to store the messages (`MailMessage` by default)

    * The column name to use for message storage (`message` by default)

    * The method to call to retrieve the messages to send (optional). It
      receives the queue options as a argument.

Here is a classic configuration for a Doctrine spool:

    [yml]
    # Schema configuration in schema.yml
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: blob, notnull: true }

-

    [yml]
    # configuration in factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

And the same configuration for a Propel spool:

    [yml]
    # Schema configuration in schema.yml
    mail_message:
      message:    { type: blob, required: true }
      created_at: ~

-

    [yml]
    # configuration in factories.yml
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class:       Swift_PropelSpool
          spool_arguments:   [ MailMessage, message, getSpooledMessages ]

To send the message stored in a queue, you can use the ~`project:send-emails`~
task (note that this task is totally independent of the queue implementation,
and the options it takes):

    $ php symfony project:send-emails

>**NOTE**
>The `project:send-emails` task takes an `application` and `env` options.

When calling the `project:send-emails` task, email messages are sent via the
same transport as the one used for the `realtime` strategy.

>**TIP**
>Note that the `project:send-emails` task can be run on any machine, not
>necessarily on the machine that created the message. It works because
>everything is stored in the message object, even the file attachments.

-

>**NOTE**
>The built-in implementation of the queues are very simple. They send emails
>without any error management, like they would have been sent if you have used
>the `realtime` strategy. Of course, the default queue classes can be extended
>to implement your own logic and error management.

The `project:send-emails` task takes two optional options:

 * `message-limit`: Limits the number of messages to sent.

 * `time-limit`: Limits the time spent to send messages (in seconds).

Both options can be combined:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

The above command will stop sending messages when 10 messages are sent or
after 20 seconds.

Even when using the `spool` strategy, you might need to send a message
immediately without storing it in the queue. This is possible by using the
special `sendNextImmediately()` method of the mailer:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

In the previous example, the `$message` won't be stored in the queue and will
be sent immediately. As its name implies, the `sendNextImmediately()` method
only affects the very next message to be sent.

>**NOTE**
>The `sendNextImmediately()` method has no special effect when the delivery
>strategy is not `spool`.

### The ~`none`~ Strategy

This strategy is useful in the development environment to avoid emails to be
sent to real users. Messages are still available in the web debug toolbar
(more information in the section below about the mailer panel of the web debug
toolbar).

It is also the best strategy for the test environment, where the
`sfTesterMailer` object allows you to introspect the messages without the need
to actually send them (more information in the section below about testing).

The Mail Transport
------------------

Mail messages are actually sent by a transport. The transport is configured in
the `factories.yml` configuration file, and the default configuration uses the
SMTP server of the local machine:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer comes bundled with three different transport classes:

  * ~`Swift_SmtpTransport`~: Uses a SMTP server to send messages.

  * ~`Swift_SendmailTransport`~: Uses `sendmail` to send messages.

  * ~`Swift_MailTransport`~: Uses the native PHP `mail()` function to send
    messages.

>**TIP**
>The ["Transport Types"](http://swiftmailer.org/docs/transport-types) section
>of the Swift Mailer official documentation describes all you need to know
>about the built-in transport classes and their different parameters.

Sending an Email from a Task
----------------------------

Sending an email from a task is quite similar to sending an email from an
action, as the task system also provides a `getMailer()` method.

When creating the mailer, the task system relies on the current configuration.
So, if you want to use a configuration from a specific application, you must
accept the `--application` option (see the chapter on tasks for more
information on this topic).

Notice that the task uses the same configuration as the controllers. So, if
you want to force the delivery when the `spool` strategy is used, use
`sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Debugging
---------

Traditionally, debugging emails has been a nightmare. With symfony, it is very
easy, thanks to the ~web debug toolbar~.

From the comfort of your browser, you can easily and rapidly see how many
messages have been sent by the current action:

![Emails in the Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Emails in the Web Debug Toolbar")

If you click on the email icon, the sent messages are displayed in the panel
in their raw form as shown below.

![Emails in the Web Debug Toolbar - details](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Emails in the Web Debug Toolbar - details")

>**NOTE**
>Each time an email is sent, symfony also adds a message in the log.

Testing
-------

Of course, the integration would not have been complete without a way to test
mail messages. By default, symfony registers a `mailer` tester
(~`sfMailerTester`~) to ease mail testing in functional tests.

The ~`hasSent()`~ method tests the number of messages sent during the current
request:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

The previous code checks that the `/foo` URL sends only one email.

Each sent email can be further tested with the help of the ~`checkHeader()`~
and ~`checkBody()`~ methods:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

The second argument of `checkHeader()` and the first argument of `checkBody()`
can be one of the following:

 * a string to check an exact match;

 * a regular expression to check the value against it;

 * a negative regular expression (a regular expression starting with a `!`) to
   check that the value does not match.

By default, the checks are done on the first message sent. If several messages
have been sent, you can choose the one you want to test with the
~`withMessage()`~ method:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(2)->
        withMessage('foo@example.com')->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

The `withMessage()` takes a recipient as its first argument. It also takes a
second argument to indicate which message you want to test if several ones
have been sent to the same recipient.

Last but not the least, the ~`debug()`~ method dumps the sent messages to spot
problems when a test fails:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

Email Messages as Classes
-------------------------

In this chapter's introduction, you have learnt how to send emails from an
action. This is probably the easiest way to send emails in a symfony
application and probably the best when you just need to send a few simple
messages.

But when your application needs to manage a large number of different email
messages, you should probably have a different strategy.

>**NOTE**
>As an added bonus, using classes for email messages means that the same email
>message can be used in different applications; a frontend and a backend one for
>instance.

As messages are plain PHP objects, the obvious way to organize your messages
is to create one class for each of them:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends Swift_Message
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        $this
          ->setFrom(array('app@example.com' => 'My App Bot'))
          ->attach('...')
        ;
      }
    }

Sending a message from an action, or from anywhere else for that matter, is
simple a matter of instantiating the right message class:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Of course, adding a base class to centralize the shared headers like the
`From` header, or to add a common signature can be convenient:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        // specific headers, attachments, ...
        $this->attach('...');
      }
    }

    // lib/email/ProjectBaseMessage.class.php
    class ProjectBaseMessage extends Swift_Message
    {
      public function __construct($subject, $body)
      {
        $body .= <<<EOF
    --

    Email sent by My App Bot
    EOF
        ;
        parent::__construct($subject, $body);

        // set all shared headers
        $this->setFrom(array('app@example.com' => 'My App Bot'));
      }
    }

If a message depends on some model objects, you can of course pass them as
arguments to the constructor:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($user)
      {
        parent::__construct('Confirmation for '.$user->getName(), 'Body');
      }
    }

Recipes
-------

### Sending Emails via ~Gmail~

If you don't have an SMTP server but have a Gmail account, use the following
configuration to use the Google servers to send and archive messages:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   your_gmail_username_goes_here
        password:   your_gmail_password_goes_here

Replace the `username` and `password` with your Gmail credentials and you are
done.

### Customizing the Mailer Object

If configuring the mailer via the `factories.yml` is not enough, you can
listen to the ~`mailer.configure`~ event, and further customize the mailer.

You can connect to this event in your `ProjectConfiguration` class like shown
below:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->dispatcher->connect(
          'mailer.configure',
          array($this, 'configureMailer')
        );
      }

      public function configureMailer(sfEvent $event)
      {
        $mailer = $event->getSubject();

        // do something with the mailer
      }
    }

The following section illustrates a powerful usage of this technique.

### Using ~Swift Mailer Plugins~

To use Swift Mailer plugins, listen to the `mailer.configure` event (see the
section above):

    [php]
    public function configureMailer(sfEvent $event)
    {
      $mailer = $event->getSubject();

      $plugin = new Swift_Plugins_ThrottlerPlugin(
        100, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
      );

      $mailer->registerPlugin($plugin);
    }

>**TIP**
>The ["Plugins"](http://swiftmailer.org/docs/plugins) section of the
>Swift Mailer official documentation describes all you need to know about the
>built-in plugins.

### Customizing the Spool Behavior

The built-in implementation of the spools is very simple. Each spool retrieves
all emails from the queue in a random order and sends them.

You can configure a spool to limit the time spent to send emails (in seconds),
or to limit the number of messages to send:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

In this section, you will learn how to implement a priority system for the
queue. It will give you all the information needed to implement your own
logic.

First, add a `priority` column to the schema:

    [yml]
    # for Propel
    mail_message:
      message:    { type: blob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # for Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: blob, notnull: true }
        priority: { type: integer }

When sending an email, set the priority header (1 means highest):

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

Then, override the default `setMessage()` method to change the priority of the
`MailMessage` object itself:

    [php]
    // for Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->setPriority($msg->getPriority());

        return parent::setMessage($message);
      }
    }

    // for Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->priority = $msg->getPriority();

        return $this->_set('message', $message);
      }
    }

Notice that the message is serialized by the queue, so it has to be unserialized 
before getting the priority value. Now, create a method that orders the
messages by priority:

    [php]
    // for Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // for Doctrine
    class MailMessageTable extends Doctrine_Table
    {
      public function getSpooledMessages()
      {
        return $this->createQuery('m')
          ->orderBy('m.priority')
        ;
      }

      // ...
    }

The last step is to define the retrieval method in the `factories.yml`
configuration to change the default way in which the messages are obtained
from the queue:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

That's all there is to it. Now, each time you run the `project:send-emails`
task, each email will be sent according to its priority.

>**SIDEBAR**
>Customizing the Spool with any Criteria
>
>The previous example uses a standard message header, the priority. But if you
>want to use any criteria, or if you don't want to alter the sent message,
>you can also store the criteria as a custom header, and remove it before
>sending the email.
>
>First, add a custom header to the message to be sent:
>
>     [php]
>     public function executeIndex()
>     {
>       $message = $this->getMailer()
>         ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
>       ;
>     
>       $message->getHeaders()->addTextHeader('X-Queue-Criteria', 'foo');
>     
>       $this->getMailer()->send($message);
>     }
>
>Then, retrieve the value from this header when storing the message in the
>queue, and remove it immediately:
>
>     [php]
>     public function setMessage($message)
>     {
>       $msg = unserialize($message);
>     
>       $headers = $msg->getHeaders();
>       $criteria = $headers->get('X-Queue-Criteria')->getFieldBody();
>       $this->setCriteria($criteria);
>       $headers->remove('X-Queue-Criteria');
>     
>       return parent::_set('message', serialize($msg));
>     }
