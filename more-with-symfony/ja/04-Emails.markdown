メール
======

*Fabien Potencier 著*

[Swift Mailer](http://www.swiftmailer.org/) ライブラリのおかげで、symfony でのメールは簡単でパワフルです。

Swift Mailer を使うとメールを簡単に送信できますが、symfony ではさらに薄いラッパーをその上にかぶせて、柔軟でパワフルなメール送信を実現しています。

この章ではこれらのパワーを自由に使いこなすための方法を教えます。

>**NOTE**
>symfony 1.3　には、Swift Mailer　のバージョン4.1が同梱されています。

はじめに
--------

symfony におけるメール管理の中心は mailer オブジェクトです。他の多くの symfony コアオブジェクトと同様に、mailer はファクトリーです。'factories.yml' ファイルで設定でき、context インスタンスを経由していつでも以下のように呼び出すことができます:

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>他のファクトリーと違い、mailer は呼ばれたときだけロードされ、初期化されます。使わないのであれば、パフォーマンスには影響しません

このチュートリアルでは、Swift Mailer の symfony への統合について説明します。もし Swift Mailer ライブラリのすばらしい詳細を学びたければ、そちらの[ドキュメント](http://www.swiftmailer.org/docs)を参照してください。

アクションからメールを送る
--------------------------

アクションから mailer インスタンスを取得するには、便利メソッドの `getMailer()` を呼ぶのが簡単です:

    [php]
    $mailer = $this->getMailer();

### 一番簡単な方法

~`sfAction::composeAndSend()`~ メソッドを使ってメールを送るのが一番簡単です:

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

`composeAndSend()` メソッドは次の 4 つの引数を取ります:

 * 送信者のメールアドレス(`from`)
 * 受信者(複数も可能)のメールアドレス (`to`)
 * メールのタイトル
 * メール本文

メールアドレスを引数で受け取るメソッドでは、常に文字列を配列として渡すことが可能です:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

もちろん、2 番目の引数に配列を渡すことで、一度に複数の相手にメールを送ることもできます:

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

もっと柔軟な指定がしたければ、~`sfAction::compose()`~ メソッドでメッセージを作成し、それを好みに合わせてカスタマイズした上で送信することもできます。

これは例えば、次のようにメールにファイルを添付して送りたいときなどに有用です:

    [php]
    // メッセージオブジェクトの生成
    $message = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // メッセージを送る
    $this->getMailer()->send($message);

### もっとパワフルな方法

もっと柔軟に、メッセージオブジェクトを直接作ることもできます:

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
>Swift Mailer の公式マニュアル中の["メッセージの作成"](http://swiftmailer.org/docs/messages)と["メッセージヘッダー"](http://swiftmailer.org/docs/headers)セクションで、メッセージの作成に必要な知識が網羅されています。

### symfony のビューを使う

アクションからメールを送る時に、パーシャルやコンポーネントを活用することも簡単です。

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

設定
----

他の symfony ファクトリーと同様に、mailer も設定ファイル `factories.yml` で変更できます。デフォルトの設定は以下のとおりです:

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

新規にアプリケーションを作成するときは、アプリケーションにローカルな設定ファイル `factories.yml` が生成され、`prod`、`env`、`test` 環境に応じたそれぞれのデフォルト設定値で上書きをします。

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

配送のための戦略
----------------

Swift Mailer の symfony への統合で最もすばらしいものの一つが、配送戦略 (delivery strategy) です。

配送戦略を使うと、symfony に対してメールをどう送るかを、設定ファイル `factories.yml` 中の ~`delivery_strategy`~ を書くだけで変更することができるのです。

戦略の変更は、~`send()`|`sfMailer::send()`~ メソッドの動作を変えます。デフォルトでは 4 つの異なる戦略が用意されていて、ほとんどの場合はこれらが使えるでしょう:

 * `realtime`:       メッセージはリアルタイムで直ちに送信されます。
 * `single_address`: メッセージはすべて一つのアドレスに送信されます。
 * `spool`:          メッセージはキューに格納されます。
 * `none`:           メッセージは単に無視され、捨てられます。

### リアルタイム (~`realtime`~) 戦略

リアルタイム (`realtime`) 戦略はデフォルトの配送戦略で、何も設定しなくて良いため一番簡単に使えます。

メールは設定ファイル `factories.yml` の `transport` セクションで設定されたトランスポートを使って送信されます。メールのトランスポートを設定する方法については次の節をご覧ください。

### 単一アドレス(~`single_address`~)戦略

単一アドレス (~`single_address`~) 戦略では、すべてのメールは `delivery_address` で設定された一つのアドレスに送られます。

この戦略は、開発環境において本当のユーザーに間違ってメールを送らないようにしつつも、メールの作成がちゃんとうまくいっているということを確認できるのでとても有用です。

>**TIP**
>元々のヘッダ指定 `to`、`cc`、`bcc` のどれに送られたメールなのかを確認できるよう、それぞれ `X-Swift-To`、`X-Swift-Cc`、`X-Swift-Bcc` というヘッダが追加されます。

この時のメールは、リアルタイム (~`realtime`~) 戦略と同じトランスポートを使って送信されます。

### スプール (~`spool`~) 戦略

スプール (~`spool`~) 戦略では、メッセージはキューに格納されます。

運用環境においては、これが一番良い戦略でしょう。なぜならば、ウェブのリクエストがメールが実際に送信されるのを待つ必要がないからです。

`spool` クラスは設定ファイルの ~`spool_class`~ で指定します。symfony はデフォルトで 3 つのクラスを持っています:

 * ~`Swift_FileSpool`~: メッセージはファイルシステムに格納されます。

 * ~`Swift_DoctrineSpool`~: メッセージは Doctrine のモデルに格納されます。

 * ~`Swift_PropelSpool`~: メッセージは Propel のモデルに格納されます。

スプールが生成されるとき、コンストラクタの引数には ~`spool_arguments`~ で設定した値が使われます。内蔵のキュークラスに与えることができるオプションは以下になります:

 * `Swift_FileSpool`:

    * キューファイルを格納するディレクトリの絶対パス (メッセージはこのディレクトリに格納されます)。

 * `Swift_DoctrineSpool`:

    * メッセージを格納するのに使う Doctrine のモデル (デフォルトは `MailMessage`)。

    * メッセージを格納するカラム名 (デフォルトは `message`)。

    * 送信時にメッセージを取得するメソッド (オプション)。
      キューオプションを引数として受け取ります。

 * `Swift_PropelSpool`:

    * メッセージを格納するのに使う Propel のモデル (デフォルトは `MailMessage`)。

    * メッセージを格納するカラム名 (デフォルトは `message`)。

    * 送信時にメッセージを取得するメソッド (オプション)。
      キューオプションを引数として受け取ります。

Doctrine スプールのための典型的な設定はこうなります:

    [yml]
    # schema.yml のスキーマ設定
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: clob, notnull: true }

-

    [yml]
    # factories.yml の設定
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

同じ設定を Propel スプールではこう書きます:

    [yml]
    # schema.yml のスキーマ設定
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~

-

    [yml]
    # factories.yml の設定
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class:       Swift_PropelSpool
          spool_arguments:   [ MailMessage, message, getSpooledMessages ]

キューに格納されたメッセージを送信するには、~`project:send-emails`~ タスクを使います (このタスクはキューの実装やそのオプションとは完全に独立していることに注目してください):

    $ php symfony project:send-emails

>**NOTE**
>`project:send-emails` タスクはオプション `application`と`env` を受け取ります。

`project:send-emails` タスクを実行する時、メールはリアルタイム (`realtime`) 戦略で使われるのと同じトランスポートを使って送信されます。

>**TIP**
>`project:send-emails` タスクはどのマシンからでも実行できます。作成したマシンで実行することにこだわる必要はありません。これが動くのは、メッセージオブジェクトに添付ファイルを含めすべてが保存されているからです。

-

>**NOTE**
>内蔵のキューの実装は非常に単純です。メールの送信にはリアルタイム戦略の時と変わらず、エラー管理の仕組みがありません。もちろん、デフォルトのキュークラスを拡張してあなた自身のエラー管理ロジックを追加することが可能です。

`project:send-emails` タスクは二つの省略可能なオプションを取ります:

 * `message-limit`: 送信するメッセージ数の上限

 * `time-limit`: メッセージを送信する時間の上限 (秒)

2 つのオプションは組み合わせて指定することもできます:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

上記のコマンドは、10 通のメッセージが送信されたか、20 秒経ったかした時に終了します。

スプール (`spool`) 戦略を使っているときでも、キューに格納せずにメッセージをすぐに送信したい場合もあるかもしれません。これには  mailer の特別なメソッド `sendNextImmediately()` を使います:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

上の例では、`$message` はキューには格納されず、直ちに送信されます。名前が示すように、`sendNextImmediately()` メソッドは次送られようとするメールだけに影響するのです。

>**NOTE**
>`sendNextImmediately()` メソッドは配送戦略が `spool` 以外の時は何も変わったことはしません。

### ~`none`~ 戦略

この戦略は、開発環境で実際のユーザーへメールが送られないようにする時に有用です。

この時もウェブデバッグツールバーにはメッセージが表示されます (ウェブデバッグツールバーのメーラーパネルついては、この章の後半を参照してください)。

これはまた、テスト向けにも最適な戦略です。実際にメッセージを送ることなしに、`sfTesterMailer` オブジェクトでメッセージの確認をすることができます (詳細については、テストに関する後の方の節を参照してください)。

メールのトランスポート
------------------

メールは、実際にはトランスポートによって送信されます。

トランスポートは設定ファイル `factories.yml` で設定され、デフォルトではローカルマシン上の SMTP サーバを利用します:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer には三種類の異なるトランスポートクラスがついてきます:

  * ~`Swift_SmtpTransport`~: メッセージの送信に SMTP サーバを使います。

  * ~`Swift_SendmailTransport`~: メッセージの送信に `sendmail` コマンドを使います。

  * ~`Swift_MailTransport`~: メッセージの送信にPHPのネイティブ関数 `mail()` を使います。

>**TIP**
>Swift Mailer 公式ドキュメントの ["Transport Types"](http://swiftmailer.org/docs/transport-types) 節が、内蔵のトランスポートクラスとそれぞれのパラメーターについて必要な情報を網羅しています。

タスクからメールを送る
---------------------

symfony のタスクからメールを送るのは、タスクシステムも `getMailer()` メソッドを提供していることから、アクションから送る場合とそっくりです。

mailer を生成するとき、タスクシステムは現在の設定に依存します。つまり、特定のアプリケーションの設定を使いたいならば、タスクは `--application` オプションを受け取らなければなりません (これについてもっと情報が必要であれば、タスクの章を読んでください)。

タスクはコントローラーと同じ設定を使うことに注意してください。すなわち、`spool` 戦略を使ってるときに配送を強制したいなら、`sendNextImmediately()` を呼ぶ必要があるということです:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

デバッグ
---------

昔から、メールのデバッグは悪夢でした。symfony では、~Web デバッグツールバー~のおかげで、非常に簡単です。

ブラウザだけを使って、現在のアクションから送信されたメッセージが何通あったかをすぐ簡単に確認することができるのです:

![Web デバッグツールバーでのメール](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Web デバッグツールバーでのメール")

メールアイコンをクリックすると、送信されたメッセージがパネル中に、以下のような形式で表示されます。

![Web デバッグツールバーでのメール - 詳細](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Web デバッグツールバーでのメール - 詳細")

>**NOTE**
>メールが送られるたびに、symfony はログにもメッセージを追加します。

テスト
-------

もちろん、メールをテストする方法抜きに、統合は完成したとは言えませんね。

デフォルトでは、symfony は機能テストを支援するための `mailer` テスター (~`sfMailerTester`~) を登録します。

~`hasSent()`~ メソッドは現在のリクエストで何通のメッセージが送られたかをテストします:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

上のコードでは、`/foo` URL へアクセスしたときにメールが一通だけ送信されることを確認します。

送られるそれぞれのメールは、~`checkHeader()`~ と ~`checkBody()`~ メソッドを使うことで、さらに細かくテストできます:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

`checkHeader()` メソッドの2 番目引数と `checkBody()` の最初の引数は、以下のうちどれかになります:

 * 完全に一致する文字列

 * チェックのための正規表現

 * マッチしないことをチェックするための否定の正規表現 (`!`で始まる正規表現)

デフォルトでは、チェックは最初に送られたメッセージに対して行なわれます。

もし複数のメッセージが送られるなら、~`withMessage()`~ メソッドでどのメッセージをテストしたいかを指定できます:

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

`withMessage()` は受信者のアドレスを最初の引数に取ります。また、複数のメールが同じ宛先に送られる場合には、2 番目の引数にそのうち何番目のメッセージかを指定することもできます。

そして、テストが失敗した際に送信されたメッセージをダンプする ~`debug()`~ も重要です:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

クラスとしてのメールメッセージ
-----------------------------

この章の最初に、アクションからメールを送る方法を学びました。それはたぶん、symfony アプリケーションからメールを送る一番単純な方法で、2、3 通の単純なメッセージを送る場合にはベストな方法でしょう。

しかし、アプリケーションが大量の異なるメールを送信しなければならないとしたら、おそらく異なる戦略をとる必要があるでしょう。

>**NOTE**
>メールメッセージにクラスを使うことのオマケとして、そのメッセージが他のアプリケーションでも使いまわせるという利点があります。たとえば frontend と backend で。

メッセージは単純な PHP オブジェクトなので、メッセージ群を組織化する明らかな方法はそれぞれについてクラスを 1 つ作成することです:

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

アクションから、あるいはそれが必要などこかからメッセージを送るというのは、単に適切なメッセージクラスをインスタンス化するということになります:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

もちろん、ベースクラスを作って `From` ヘッダなど共通のヘッダを集約したり、共通の署名をつけたりするのが便利でしょう:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        // 特定のヘッダやアタッチメントなどを追加
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

        // 共通のヘッダを設定
        $this->setFrom(array('app@example.com' => 'My App Bot'));
      }
    }

もしメッセージがいくつかのモデルオブジェクトに依存しているなら、もちろんコンストラクタに引数として渡せます:

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

### ~Gmail~ からメールを送る

SMTP サーバは無いけれど Gmail のアカウントがある場合、以下のような設定を使えば Google のサーバからメールを送ったりアーカイブしたりできます:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   あなたの Gmail ユーザ名
        password:   あなたの Gmail パスワード

`username` と `password` をあなた自身のものに変えるだけで動きます。

### mailer オブジェクトのカスタマイズ

mailer を設定ファイル `factories.yml` で設定するだけでは不十分な場合、~`mailer.configure`~ イベントを listen することで mailer をさらにカスタマイズできます。

このイベントに接続して mailer を変更するには、`ProjectConfiguration` クラス内で以下のように書きます:

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

        // mailerに対して何かをする
      }
    }

次の節では、このテクニックを使ったパワフルな手法を紹介します。

### ~Swift Mailer プラグイン~を使う

Swift Mailer プラグインを使うには、`mailer.configure` イベントを listen します (前節を参照):

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
>Swift Mailer 公式ドキュメントの["プラグイン"](http://swiftmailer.org/docs/plugins)の節に、最初から提供されているプラグインの情報がすべて載っています。

### スプールの挙動をカスタマイズする

内蔵のスプールの実装は非常に単純なものです。それぞれのスプールはキューからランダムな順序ですべてのメールを取ってきて、送信します。

メールを送信する際の経過時間に上限 (秒) を指定することや、送信するメッセージ数の上限を指定することができます:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

この節では、キューに優先順位を実装する方法について学びます。これを読めば、あなた自身のロジックを実装するために必要なすべての情報が得られるでしょう。

まず、DB スキーマに `priority` カラムを追加します:

    [yml]
    # Propel 用
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # Doctrine 用
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: clob, notnull: true }
        priority: { type: integer }

メールを送る時、この優先順位ヘッダを設定します (1 が最優先):

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

次に、デフォルトの `setMessage()` メソッドをオーバーライドして、`MailMessage` オブジェクトの優先順位を変更するようにします:

    [php]
    // Propel 用
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->setPriority($msg->getPriority());

        parent::setMessage($message);
      }
    }

    // Doctrine 用
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->priority = $msg->getPriority();

        $this->_set('message', $message);
      }
    }

このメッセージがキューの中でシリアライズされていることに注意してください。つまり優先順位の値を得るにはアンシリアライズする必要があります。

そして、メッセージ群を優先順位で並べるメソッドを作成します:

    [php]
    // Propel 用
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // Doctrine 用
    class MailMessageTable extends Doctrine_Table
    {
      public function getSpooledMessages()
      {
        return $this->createQuery('m')
          ->orderBy('m.priority')
          ->execute()
        ;
      }

      // ...
    }

最後に、設定ファイル `factories.yml` で、キューからメッセージを取得する際のメソッドをデフォルトからこのメソッドに変更します:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

これですべてです。

`project:send-emails` タスクを実行するたびに、それぞれのメールは優先順位に従って送信されるでしょう。

>**SIDEBAR**
>スプールをどんな条件でもうけつけるようにカスタマイズする
>
>上の例では、標準のメッセージヘッダー priority (優先順位) を使いました。
>
>しかし、もし任意の条件を使いたいとか、送信されるメッセージのヘッダを書き換えたくないとかいう場合は、その条件をカスタムヘッダに格納し、実際にメールが送信される直前にそれを取り除くということもできます。まず、送信されるメッセージにカスタムヘッダを追加します:
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
>続いて、キューにメッセージを格納するときにこのヘッダから値を得、すぐにそのヘッダを除去します:
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
