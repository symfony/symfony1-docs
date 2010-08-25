Chapter 11 - Eメール
====================

symfonyでの ~Eメール~ の送信は、内部で[Swift Mailer](http://www.swiftmailer.org/) ライブラリを利用しており、シンプルかつパワフルなものになっています。~Swift Mailer~はEメールの送信を簡単に行うことが出来ますが、symfonyがその上にわずかばかりの機能のラッピングをすることにより、より一層フレキシブルでパワフルなメール送信機能になっています。この章では、このメール送信機能を使いこなすにはどうすればよいか、について説明していきます。

>**NOTE**
>symfony 1.3 の内部で利用されている Swift Mailer のバージョンは 4.1 です。

はじめに
--------

symfonyでのEメールの管理は、メーラーオブジェクトを軸に行われます。その他多くのsymfonyのオブジェクトと同様に、メーラーもファクトリーとして振る舞います。`factories.yml`ファイル内でオブジェクトが設定され、コンテキストのインスタンス経由でいつでも利用可能です。

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>他のファクトリーと異なり、メーラーは必要に応じて、呼び出し・初期化が行われます。メーラーを利用しないのであれば、パフォーマンスへの影響は一切ありません。

このチュートリアルでは、symfonyにSwift　Mailerを組み込んで利用する方法を説明します。もし、Swift Mailerライブラリ自身の詳細な挙動について知りたい場合には、ライブラリの[ドキュメント](http://www.swiftmailer.org/docs)を参照してください。

アクションからEメールを送る
---------------------------

アクションからメーラーのインスタンスを取得するには、`getMailer()`というショートカットメソッドを使うことで、簡単に行えます。:

    [php]
    $mailer = $this->getMailer();

### もっとも簡単な方法

Eメールを送るのに最も簡単な方法は、~`sfAction::composeAndSend()`~ メソッドをを利用することです。:

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

メールアドレスが１つだけの場合には、パラメータは配列でも文字列でも、どちらでも指定可能です。:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

もちろん、複数の人に対して一度にまとめて送信する事も可能で、その場合にはメソッドの第２引数にメールの配列を渡します:

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

より柔軟にカスタマイズをしたければ、メッセージを作る際に~`sfAction::compose()`~を利用することで実現可能で、これにより最終的にできあがった内容を送信できます。これはとても便利な方法で、たとえば、~attachment|メールへの添付~を行う場合には、以下のようにします:

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
>Swift Mailerの公式ドキュメントの、["Creating Messages"](http://swiftmailer.org/docs/messages)と["Message Headers"](http://swiftmailer.org/docs/headers) の章を読めば、メッセージオブジェクトを作成するのに必要なことがすべてわかります。

### symfonyのViewを使う

アクションの中からメールを送る際に使える、簡単かつ強力な武器として、パーシャルとコンポーネントを利用することができます。

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

設定
----

他のsymfonyのファクトリーと同様、メーラーオブジェクトは`factories.yml`ファイルから設定が可能です。デフォルトの設定は、以下のようになっています:

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

新しいアプリケーションを作ると、ローカルの`factories.yml`設定ファイルがデフォルトの設定を上書きし、`prod`,`env`,`test`といった、各環境と役割に適したデフォルト設定が設定されます:

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

symfonyでSwift Mailerを利用する際、もっとも有用な機能の一つが、「配送計画」です。配送計画はsymfonyがどのようにメールのメッセージを配信するかを指定するもので、`factories.yml`の~`delivery_strategy`~によって設定します。計画を変更すると、それに従って~`send()`|`sfMailer::send()`~の動作が変わります。デフォルトで４つの計画が設定可能で、全体に共通で適用させるべきです。

 * `realtime`:       メッセージを即座に配信します
 * `single_address`: メッセージを単一のアドレスに配信します
 * `spool`:          メッセージをキューに保存します
 * `none`:           メッセージを単純に無視します

### ~`realtime`~ 計画

`realtime` 計画は、デフォルトの配送計画です。特別なことは何もなく、最も簡単にセットアップできます。

Eメールのメッセージは、`factories.yml`の`transport`セクションで指定された転送設定に従って送信されます。(次の節で、メール転送設定について、より多くの情報を説明します)

### ~`single_address`~ 計画

`single_address` 計画は、すべてのメッセージを`delivery_address`で設定した１つのアドレスへ送ります。

この計画は開発環境から本物のユーザへメールを送ってしまう事態を防ぎつつ、それでも開発者がメールの作成内容をチェックする必要がある場合などに、開発環境で非常に有効です。

>**TIP**
>もし、元々の`to`, `cc`, `bcc`などの受け手が正しいかを確認したい場合、次のようなヘッダーを利用可能です:
>`X-Swift-To`, `X-Swift-Cc`, `X-Swift-Bcc`

メールのメッセージは一人のユーザに対して`realtime`計画と同様の転送方法で送信されます。

### ~`spool`~ 計画

`spool`計画を指定すると、メッセージはキューに保存されます。

運用環境において、これがもっともよい計画です。メールの送信によって、webのリクエストを待たせることがありません。

`spool` クラスは ~`spool_class`~ の設定で指定したものになります。デフォルトでは、symfony は以下の３つのがバンドルされています:

 * ~`Swift_FileSpool`~: メッセージをファイルシステムに保存します。

 * ~`Swift_DoctrineSpool`~: メッセージをDoctrineのモデルに保存します。

 * ~`Swift_PropelSpool`~: メッセージをPropelのモデルに保存します。

spoolをインスタンス化する場合、~`spool_arguments`~の設定がコンストラクタの引数として利用されます。ここで、組み込みのキュークラスへの引数として以下の内容が利用可能です:

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

キューに保存されたメッセージを送るには、~`project:send-emails`~タスクを利用します。(このタスクはキューの実装とは完全に独立で、オプションをとれる事に注意してください):

    $ php symfony project:send-emails

>**NOTE**
> `project:send-emails` タスクは、`application` と `env` のオプションを指定できます。

`project:send-emails` タスクを呼び出した際、メールのメッセージは`realtime`計画と同様の転送方法で送信されます。

>**TIP**
>`project:send-emails` タスクは、任意のマシンで実行することができ、メッセージを生成したマシンで実行する必要がはありません。なぜなら、メッセージオブジェクトは、添付ファイルまで含めて、すべて保存されているからです。

-

>**NOTE**
>組み込みで実装されているキューは非常にシンプルです。メールを送る際、エラー管理なく、`realtime`計画を利用したときと同じように送信します。もちろん、デフォルトのキュークラスは拡張することができ、自分でエラー管理や独自のロジックなどを実装できます。

`project:send-emails` タスクは、以下の任意のオプションを指定できます:

 * `message-limit`: 送信するメッセージの数の上限

 * `time-limit`: メッセージの送信を行う時間数の上限(秒数)

両方オプションを併用することも出来ます:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

上述のコマンドは10通のメッセージを送るか、もしくは20秒が経過したら、送信を停止します。

`spool`計画を指定してある場合でも、キューに保存せず即座にメッセージを送信したい場合があるでしょう。そのような場合には、メーラーから`sendNextImmediately()`メソッドを利用します:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

上記の例は、`$message`をキューに保存せず、即座に送信します。メソッド名から予想されるとおり、`sendNextImmediately()`メソッドは直後のメッセージ送信のみに効果があります。

>**NOTE**
>`sendNextImmediately()`は、配信計画が`spool`ではない場合、特別な効果を発揮しません。

### ~`none`~ 計画

この計画は、開発環境において実際のユーザーにメールを送らないようにする際などに有効です。メッセージはWebデバッグツールバーから利用可能です。(この章の下部の、Webデバッグツールバーのメーラーパネルについての節により多くの情報が書かれています。)

テスト環境においてもっともよい計画もこれで、`sfTesterMailer`オブジェクトが実際の送信を行わずにメッセージを内部的に生成してくれます(下部のテストについての節により多くの情報があります。)

メール転送
----------

メールのメッセージは実際には転送によって送信されます。転送は、`factories.yml`で設定され、デフォルトの設定はローカルマシンのSMTPサーバーを利用するようになっています:

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
>Swift Mailerの公式ドキュメントの["Transport Types"](http://swiftmailer.org/docs/transport-types)の節をよめば、組み込みの転送クラスとそのパラメータの違いについて、必要なすべてのことがわかります。

タスクからメールを送る
----------------------

タスクからメールを送るのはアクションからメールを送る場合とかなりにています。タスクシステムも、やはり`getMailer()`メソッドを提供しています。

メーラーを生成する際、タスクシステムは現在の設定を信用します。なので、特定のアプリケーションの設定を利用したい場合には、`--application`オプションを設定しなければなりません。(タスクの章のこのオプションのトピックに、より詳細な情報があります。)

タスクがコントローラーに関して同一の設定を利用していることに注意してください。そのため、`spool`計画で強制的に配信したい場合には、`sendNextImmediately()`メソッドを利用します:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

デバッグ
--------

従来のメールのデバッグは大変でした。symfonyにおいては、~Webデバッグツールバー~のおかげでとても簡単になっています。

ブラウザの機能によって、現在のアクションでいくつのメールが送信されたかをすぐに確認することができます:

![WebデバッグツールバーのEメール](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Emails in the Web Debug Toolbar")

Eメールアイコンをクリックしますと、送信されたEメールのメッセージが以下のようにパネルに表示されます。

![WebデバッグツールバーのEメール - 詳細](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Emails in the Web Debug Toolbar - details")

>**NOTE**
>Eメールが送信される度に、symfonyはログにメッセージを追加します。

テスト
-------

もちろん、メールのメッセージをテストすること無しには、完成ではありません。symfonyはデフォルトの状態から`mailer`テスター (~`sfMailerTester`~) を用意してあり、機能テストでメールのテストを行うことができます。

~`hasSent()`~メソッドは、実行したリクエストの間に送られたメッセージの数をテストします。:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

上記のコードでは`/foo` URLにおいて1つEメールが送信されたことをテストしています。

送られたEメールは、~`checkHeader()`~ メソッドや ~`checkBody()`~ メソッドを使い、さらに詳細なテストをすることができます:


    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;


`checkHeader()`メソッドの第２引数と`checkBody()`メソッドの第１引数では、以下のいずれかを指定することができます:

 * 厳密にマッチするかどうかをチェックする文字列;

 * マッチするかどうかをチェックする正規表現;

 * マッチしないかどうかをチェックする否定の正規表現 (`!`から始まる正規表現)。

デフォルトでは、最初に送られたメールメッセージをチェックします。もし、複数のメールメッセージを送った際には、~`withMessage()`~ メソッドで調べるメールメッセージを選択することができます:

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

`withMessage()`メソッドは、第１引数に受信者のメールアドレスを指定します。また、同じ受信者に複数のメッセージのメールを送信する際には、第２引数にテストをしたいメッセージを指定します。

そして、~`debug()`~ メソッドは、テストが失敗した際に問題を調査するために送信されたメッセージをダンプします:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

クラスとしてメールメッセージを使う
-------------------------

この章の始めに、アクションからEメールを送る方法を学びました。その方法はsymfonyアプリケーションにおいて最も簡単ですし、シンプルなメールメッセージの場合には最良の方法でしょう。

しかし、あなたのアプリケーションがもっと多くの異なるメールメッセージを管理する必要があるのであれば、他の戦略を考えなければなりません。

>**NOTE**
>メールメッセージのためのクラスを使うと、異なるアプリケーションで、同じメールメッセージを使うことができます。例えば、フロントエンドとバックエンドのアプリケーションで使うことができます。

メッセージは、プレーンなPHPのオブジェクトですので、メッセージを構成するための方法は、それぞれのメッセージに対応するクラスを作成することです。

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

これで、異なるアクションやタスクなどからのメッセージ送信は、メッセージクラスをインスタンス化することでシンプルになりました:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

もちろん`From`ヘッダななどの共通のヘッダをまとめたり、共通の署名を追加したりするベースクラスを使い、より便利にすることができます:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        // 特定のヘッダや添付など...
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

        // 全てに共通のヘッダをセットします
        $this->setFrom(array('app@example.com' => 'My App Bot'));
      }
    }

メッセージがモデルオブジェクトに依存している際には、もちろんコンストラクタの引数として渡すことができます:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($user)
      {
        parent::__construct('Confirmation for '.$user->getName(), 'Body');
      }
    }

レシピ
-------

### ~Gmail~を使ってメールを送信する

SMTPサーバがなく、Gmailのアカウントを持っている際には、次の設定でGoogleのサーバを使いメッセージを送信することができます。

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   your_gmail_username_goes_here
        password:   your_gmail_password_goes_here

`username`と`password`をあなたのGmailのアカウントの情報に書き換えるだけです。

### メーラーオブジェクトをカスタマイズする

`factories.yml`を使用してメーラーを設定できますが、もっと細かくカスタマイズしたい際には、~`mailer.configure`~イベントをリスナーに登録することができます。

下に示したように`ProjectConfiguration`クラスにおいて、このイベントに接続することができます:

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

        // $mailerオブジェクトを加工します
      }
    }

以下のセクションでは、このテクニックを使ったパワフルな方法を説明します。

### ~Swift Mailer Plugins~を使う

Swift Mailer pluginsを使うために、上で紹介した`mailer.configure`イベントをリスナーに登録してください:

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
>Swift Mailerの公式ドキュメントの["プラグイン"](http://swiftmailer.org/docs/plugins) セクションでは、ビルトインされたプラグインに関する内容が全て記述されています。

### spoolのビヘイビアをカスタマイズする

ビルトインされているspoolの実装は、とてもシンプルです。それぞれのspoolは、キューからランダムな順序でメールアドレスを取ってきます。

秒単位でメールの送信にかける時間を制限したり、送信するの最大数を制限するなど、spoolを設定することができます:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

このセクションでは、キューで優先順位(priority)を実装する方法を学びます。自分のロジックで実装するのに必要な全ての情報を説明します。


まず、`priority`カラムをスキーマに追加します:

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

メールを送信する際に、priorityヘッダをセットします(優先順位が一番高いのは1になります):

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

次に、デフォルトの`setMessage()`メソッドをオーバーライドし、`MailMessage`オブジェクトのpriorityを変更します:

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

メッセージがキューによってシリアライズされているので、priorityの値を取得する前にアンシリアライズをする必要があります。これで、priorityによって順序を指定したメソッドができました:

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

そして最後に、キューからメッセージを取得するためのデフォルトの方法を変更するために、`factories.yml`に取得メソッドを定義します:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

これで完成です。これで、`project:send-emails`タスクを実行する旅に、メールはpriorityに基づいて送信されます。

>**SIDEBAR**
>基準によってspoolをカスタマイズする
>
>先ほど説明した例では、`priority`という標準的なメッセージヘッダーを使用しました。しかし、他の基準（Criteria）を使用したい場合や、送信したメッセージを変更したくない場合などには、カスタムヘッダーとして基準(Criteria)を格納し、メール送信前に除去することができます。
>
>まず、送信するメールメッセージにカスタムヘッダを追加してください:
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
>次に、キューにメールメッセージを溜める際に、ヘッダーの値を取得して、すぐに除去してください:
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
