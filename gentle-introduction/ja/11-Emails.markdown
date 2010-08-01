Chapter 11 - Eメール
====================

symfonyでの ~Eメール~ の送信は、内部で[Swift Mailer](http://www.swiftmailer.org/)
ライブラリを利用しており、シンプルかつパワフルなものになっています。
~Swift Mailer~はEメールの送信を簡単に行うことが出来ますが、symfonyがその上にわずかばかりの機能のラッピングをすることにより、
より一層フレキシブルでパワフルなメール送信機能になっています。
この章では、このメール送信機能を使いこなすにはどうすればよいか、について説明していきます。

>**NOTE**
>symfony 1.3 の内部で利用されている Swift Mailer のバージョンは 4.1 です。

はじめに
--------

symfonyでのEメールの管理は、メーラーオブジェクトを軸に行われます。
その他多くのsymfonyのオブジェクトと同様に、メーラーもファクトリーとして振る舞います。
`factories.yml`ファイル内でオブジェクトが設定され、コンテキストのインスタンス経由で
いつでも利用可能です。

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>他のファクトリーと異なり、メーラーは必要に応じて、呼び出し・初期化が行われます。
>メーラーを利用しないのであれば、パフォーマンスへの影響は一切ありません。

このチュートリアルでは、symfonyにSwift　Mailerを組み込んで利用する方法を説明します。
もし、Swift Mailerライブラリ自身の詳細な挙動について知りたい場合には、
ライブラリの[ドキュメント](http://www.swiftmailer.org/docs)を参照してください。

アクションからEメールを送る
---------------------------

アクションからメーラーのインスタンスを取得するには、`getMailer()`という
ショートカットメソッドを使うことで、簡単に行えます。:

    [php]
    $mailer = $this->getMailer();

### もっとも簡単な方法

Eメールを送るのに最も簡単な方法は、~`sfAction::composeAndSend()`~ メソッドを
を利用することです。:

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

`composeAndSend()`メソッドは、４つの引数をとります:

 * 送信元メールアドレス (`from`);
 * 送信先メールアドレス(の一覧) (`to`);
 * メールの題名;
 * メールの本文

メールアドレスが１つだけの場合には、パラメータは配列でも文字列でも、
どちらでも指定可能です。:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

もちろん、複数の人に対して一度にまとめて送信する事も可能で、その場合にはメソッドの第２引数に
メールの配列を渡します:

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

### 柔軟な方法

より柔軟にカスタマイズをしたければ、メッセージを作る際に~`sfAction::compose()`~を利用することで
実現可能で、これにより最終的にできあがった内容を送信できます。
これはとても便利な方法で、たとえば、~attachment|メールへの添付~を
行う場合には、以下のようにします:

    [php]
    // create a message object
    $message = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // send the message
    $this->getMailer()->send($message);

### 強力な方法

さらに柔軟な操作をしたい場合、直接メッセージオブジェクトを作る事も出来ます:

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
>Swift Mailerの公式ドキュメントの、
>["Creating Messages"](http://swiftmailer.org/docs/messages)と
>["Message Headers"](http://swiftmailer.org/docs/headers) の章を読めば、
>メッセージオブジェクトを作成するのに必要なことがすべてわかります。

### symfonyのViewを使う

アクションの中からメールを送る際に使える、簡単かつ強力な武器として、
パーシャルとコンポーネントを利用することができます。

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

設定
----

他のsymfonyのファクトリーと同様、メーラーオブジェクトは
`factories.yml`ファイルから設定が可能です。
デフォルトの設定は、以下のようになっています:

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

新しいアプリケーションを作ると、ローカルの`factories.yml`設定ファイルが
デフォルトの設定を上書きし、`prod`,`env`,`test`といった、各環境と役割に適した
デフォルト設定が設定されます:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

配送計画
--------

symfonyでSwift Mailerを利用する際、もっとも有用な機能の一つが、
「配送計画」です。
配送計画はsymfonyがどのようにメールのメッセージを配信するかを指定するもので、
`factories.yml`の~`delivery_strategy`~によって設定します。
計画を変更すると、それに従って~`send()`|`sfMailer::send()`~の動作が変わります。
デフォルトで４つの計画が設定可能で、全体に共通で適用させるべきです。

 * `realtime`:       メッセージを即座に配信します
 * `single_address`: メッセージを単一のアドレスに配信します
 * `spool`:          メッセージをキューに保存します
 * `none`:           メッセージを単純に無視します

### ~`realtime`~ 計画

`realtime` 計画は、デフォルトの配送計画です。
特別なことは何もなく、最も簡単にセットアップできます。

Eメールのメッセージは、`factories.yml`の`transport`セクションで指定された
転送設定に従って送信されます。(次の節で、メール転送設定について、
より多くの情報を説明します)

### ~`single_address`~ 計画

`single_address` 計画は、すべてのメッセージを`delivery_address`で設定した
１つのアドレスへ送ります。

この計画は開発環境から本物のユーザへメールを送ってしまう事態を防ぎつつ、
それでも開発者がメールの作成内容をチェックする必要がある場合などに、
開発環境で非常に有効です。

>**TIP**
>もし、元々の`to`, `cc`, `bcc`などの受け手が正しいかを確認したい場合、
>次のようなヘッダーを利用可能です:
>`X-Swift-To`, `X-Swift-Cc`, `X-Swift-Bcc`

メールのメッセージは一人のユーザに対して`realtime`計画と同様の
転送方法で送信されます。

### ~`spool`~ 計画

`spool`計画を指定すると、メッセージはキューに保存されます。

運用環境において、これがもっともよい計画です。
メールの送信によって、webのリクエストを待たせることがありません。

`spool` クラスは ~`spool_class`~ の設定で指定したものになります。
デフォルトでは、symfony は以下の３つのがバンドルされています:

 * ~`Swift_FileSpool`~: メッセージをファイルシステムに保存します。

 * ~`Swift_DoctrineSpool`~: メッセージをDoctrineのモデルに保存します。

 * ~`Swift_PropelSpool`~: メッセージをPropelのモデルに保存します。

spoolをインスタンス化する場合、~`spool_arguments`~の設定がコンストラクタの
引数として利用されます。
ここで、組み込みのキュークラスへの引数として以下の内容が利用可能です:

 * `Swift_FileSpool`:

    * キューを保存するディレクトリの絶対パス
      (メッセージはこのディレクトリに保存されます)

 * `Swift_DoctrineSpool`:

    * メッセージの保存に利用するDoctrineのモデル
      (デフォルトは`MailMessage`)

    * メッセージを保存するのに利用するカラム名(デフォルトは`message`)

    * メッセージを取得・送信する際に呼び出されるメソッド(任意)
      引数としてキューのオプションをとります。

 * `Swift_PropelSpool`:

    * メッセージの保存に利用するPropelのモデル
      (デフォルトは`MailMessage`)

    * メッセージを保存するのに利用するカラム名(デフォルトは`message`)

    * メッセージを取得・送信する際に呼び出されるメソッド(任意)
      引数としてキューのオプションをとります。

ここでは、Doctrine spoolの典型的な設定を示します:

    [yml]
    # Schema configuration in schema.yml
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: clob, notnull: true }

-

    [yml]
    # configuration in factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

同様に、Propelの場合の設定も示します:

    [yml]
    # Schema configuration in schema.yml
    mail_message:
      message:    { type: clob, required: true }
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

キューに保存されたメッセージを送るには、~`project:send-emails`~タスク
を利用します。
(このタスクはキューの実装とは完全に独立で、オプションをとれる事に注意してください):

    $ php symfony project:send-emails

>**NOTE**
> `project:send-emails` タスクは、`application` と `env` のオプションを指定できます。

`project:send-emails` タスクを呼び出した際、メールのメッセージは`realtime`計画と
同様の転送方法で送信されます。

>**TIP**
>`project:send-emails` タスクは、任意のマシンで実行することができ、
>メッセージを生成したマシンで実行する必要がはありません。
>なぜなら、メッセージオブジェクトは、添付ファイルまで含めて、すべて保存されているからです。

-

>**NOTE**
>組み込みで実装されているキューは非常にシンプルです。
>メールを送る際、エラー管理なく、`realtime`計画を利用したときと同じように送信します。
>もちろん、デフォルトのキュークラスは拡張することができ、自分でエラー管理や
>独自のロジックなどを実装できます。

`project:send-emails` タスクは、以下の任意のオプションを指定できます:

 * `message-limit`: 送信するメッセージの数の上限

 * `time-limit`: メッセージの送信を行う時間数の上限(秒数)

両方オプションを併用することも出来ます:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

上述のコマンドは10通のメッセージを送るか、もしくは20秒が経過したら、
送信を停止します。

`spool`計画を指定してある場合でも、キューに保存せず即座にメッセージを送信
したい場合があるでしょう。
そのような場合には、メーラーから`sendNextImmediately()`メソッドを利用します:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

上記の例は、`$message`をキューに保存せず、即座に送信します。
メソッド名から予想されるとおり、`sendNextImmediately()`メソッドは
直後のメッセージ送信のみに効果があります。

>**NOTE**
>`sendNextImmediately()`は、配信計画が`spool`ではない場合、特別な
>効果を発揮しません。

### ~`none`~ 計画

この計画は、開発環境において実際のユーザーにメールを送らないようにする際などに有効です。
メッセージはWebデバッグツールバーから利用可能です。
(この章の下部の、Webデバッグツールバーのメーラーパネルについての節に
より多くの情報が書かれています。)

テスト環境においてもっともよい計画もこれで、`sfTesterMailer`オブジェクトが
実際の送信を行わずにメッセージを内部的に生成してくれます
(下部のテストについての節により多くの情報があります。)

メール転送
----------

メールのメッセージは実際には転送によって送信されます。
転送は、`factories.yml`で設定され、デフォルトの設定はローカルマシンの
SMTPサーバーを利用するようになっています:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer は３つの異なる転送クラスをバンドルしています:

  * ~`Swift_SmtpTransport`~: SMTPサーバを使ってメールを送信する

  * ~`Swift_SendmailTransport`~: `sendmail`コマンドを使ってメールを送信する

  * ~`Swift_MailTransport`~: PHPの`mail()`関数を使ってメールを送信する


>**TIP**
>Swift Mailerの公式ドキュメントの["Transport Types"](http://swiftmailer.org/docs/transport-types)
>の節をよめば、組み込みの転送クラスとそのパラメータの違いについて、
>必要なすべてのことがわかります。

タスクからメールを送る
----------------------

タスクからメールを送るのはアクションからメールを送る場合とかなりにています。
タスクシステムも、やはり`getMailer()`メソッドを提供しています。

メーラーを生成する際、タスクシステムは現在の設定を信用します。
なので、特定のアプリケーションの設定を利用したい場合には、
`--application`オプションを設定しなければなりません。
(タスクの章のこのオプションのトピックに、より詳細な情報があります。)

タスクがコントローラーに関して同一の設定を利用していることに注意してください。
そのため、`spool`計画で強制的に配信したい場合には、`sendNextImmediately()`
メソッドを利用します:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

デバッグ
--------

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
      message:    { type: clob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # for Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: clob, notnull: true }
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

        parent::setMessage($message);
      }
    }

    // for Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->priority = $msg->getPriority();

        $this->_set('message', $message);
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
>       parent::setMessage($message);
>     }
