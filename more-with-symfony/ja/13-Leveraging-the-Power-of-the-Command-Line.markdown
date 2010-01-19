コマンドラインとタスクの活用
============================

*Geoffrey Bachelet 著*

以前の pake ベースのタスクシステムに代わり、モダンで強力で柔軟なコマンドラインシステムが symfony 1.1 で導入されました。 このタスクシステムはバージョンアップと改良が行われ、現在に至っています。

多くの Web 開発者はタスクを使うことの価値や、コマンドラインの便利さにも気づいていないかもしれません。この章では、タスクの高度な使用方法から始めて、日々の作業の効率化やタスクの最大限の活用方法といったタスクの詳細に触れます。

タスクの概要
------------

タスクとは、プロジェクトのルートでコマンドラインから `symfony`PHP スクリプトを使って実行するコード片です。シェルで次のように実行して、よく知られた `cache:clear` タスク(または `cc`)を実行したことがあるはずです:

    $ php symfony cc

symfony では、さまざま場面で使える汎用的な組み込みタスクが提供されています。`symfony` スクリプトを引数やオプションをつけずに実行すると、利用可能なタスクの一覧が表示されます:

    $ php symfony

次のような一覧が表示されます(内容は省略してあります):

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

すでにお気づきの方もいらっしゃるかと思いますが、タスクはグループ化されています。タスクのグループを名前空間と呼び、タスクの名前は一般的には名前空間とタスク名で構成されます。
(`help` タスクと `list` タスクは例外で、名前空間がありません)
この命名規則によりタスクを簡単に分類でき、独自に作ったタスクに意味のある名前空間を使うことができます。


独自のタスクを記述する
----------------------

symfony でタスクの開発を始めるのはとても簡単です。タスクを作り、名前をつけ、何らかのロジックを記述するだけで、初めてのカスタムタスクを実行できます。例として、とても単純な *Hello, World!* タスクを `lib/task/sayHelloTask.class.php` に作ります:

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

このタスクを、次のコマンドで実行します:

    $ php symfony say:hello

このタスクでは *Hello, World!* と表示されるだけですが、最初の例としては十分です。実際のタスクでは、`echo` や `print` 文を使って直接内容を表示する必要はありません。`sfBaseTask` を継承すると、たとえば内容の表示を行う `log()` メソッドのような、便利ないくつかのメソッドを使えます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log('Hello, World!');
    }

1 度のタスクの実行で複数行の内容を表示したい場合、`logSection()` メソッドを使うと便利です:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, World!');
    }

`execute()` メソッドに 2 つの引数 `$arguments` と `$options` が渡されています。これらの引数には、実行時に指定されたすべての引数とオプションが格納されています。引数とオプションについては、後の節で詳細に説明します。ここでは単純に、タスクで挨拶する相手を指定できるようにしてみましょう:

    [php]
    public function configure()
    {
      $this->addArgument('who', sfCommandArgument::OPTIONAL, 'Who to say hello to?', 'World');
    }

    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, '.$arguments['who'].'!');
    }

次のようにコマンドを実行します:

    $ php symfony say:hello Geoffrey

すると、次のように表示されます:

    >> say       Hello, Geoffrey!

簡単ですね!

ところで、たとえばタスクの処理内容といった簡単なメタデータをタスクに記述したい場合があります。このような場合、`briefDescription` プロパティや `detailedDescription` プロパティを設定します:

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

この例のように、基本的なマークアップを使って説明文を装飾できます。symfony のタスクヘルプシステムを使って表示を確認してください:

    $ php symfony help say:hello

オプションシステム
------------------

symfony のタスクでは、オプションは 2 つの別々の集合、オプションと引数にまとめられます。

### オプション

オプションはハイフン付きで指定します。オプションは、コマンドラインに任意の順序で指定できます。オプションの値の指定は任意ですが、値を指定しない場合はブール値として動作します。オプションには、短い形式と長い形式の両方が用意されていることがよくあります。長い形式は通常 2 つのハイフンを使って指定され、短い形式は 1 つのハイフンで指定されます。

よく使われるオプションの例として、ヘルプ用のスイッチ(`--help`または`-h`)、冗長表示のスイッチ(`--quiet`または`-q`)、バージョン情報のスイッチ(`--version`または`-V`)があります。

>**NOTE**
>オプションは `sfCommandOption` クラスで定義され、`sfCommandOptionSet` クラスに保存されます。

### 引数

引数はコマンドラインに追加する短いデータです。引数は、定義された順に指定し、引数のデータに空白がある場合は引用符で囲うか、空白をエスケープする必要があります。引数の値の指定は任意ですが、値を指定しない場合に使われるデフォルト値を引数の定義で指定しなければなりません。

>**NOTE**
>引数は `sfCommandArgument` クラスで定義され、`sfCommandArgumentSet` クラスに保存されます。

### デフォルトセット

symfony のすべてのタスクには、デフォルトで次のオプションと引数があります:

  * `--help` (-`H`): このヘルプメッセージを表示する
  * `--quiet` (`-q`): 標準出力にメッセージを出力しない
  * `--trace` `(-t`): トレースの呼び出し/実行をオンにし、フルバックトレースを有効にする
  * `--version` (`-V`): プログラムのバージョンを表示する
  * `--color`: ANSI カラー出力を強制する

### 特別なオプション

symfony のタスクシステムでは、2 つの特別なオプション `application` と `env` を使えます。

`application` オプションは、`sfProjectConfiguration` ではなく `sfApplicationConfiguration` のインスタンスにアクセスしたい場合に指定します。これは、たとえばルーティングから URL を生成する場合、ルーティングは特定のアプリケーションに関連付けられているために必要になります。

`application` オプションがタスクに指定されると symfony によって自動的に検出され、デフォルトの `sfProjectConfiguration` オブジェクトの代わりに指定されたアプリケーションに対応する `sfApplicationConfiguration` オブジェクトが作られます。このオプションのデフォルト値を設定することも可能で、デフォルト値を設定すればタスクを実行するたびにオプションでアプリケーションを指定する必要はなくなります。

`env` オプションで、タスクを実行する環境を制御できます。環境を指定しない場合は、デフォルトで `test` 環境が使われます。`application` と同様に `env` オプションのデフォルト値を設定すると、タスクの実行時に自動的に適用されます。

`application` と `env` はデフォルトオプションセットに含まれていないので、次のようにタスクに手作業で追加する必要があります:

    [php]
    public function configure()
    {
      $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      ));
    }

この例では、`frontend` アプリケーションが自動的に使われ、別の環境が指定されなければ `dev` 環境でタスクが実行されます。

データベースへのアクセス
------------------------

symfony のタスクからデータベースにアクセスするには、`sfDatabaseManager` をインスタンス化します:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
    }

ORM のコネクションオブジェクトにも直接アクセスできます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase()->getConnection();
    }

`databases.yml` に複数のコネクションが定義されている場合はどうするのでしょうか?この場合は、たとえば次のように `connection` オプションをタスクに追加します:

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

通常は、このオプションにデフォルト値を設定しておくと便利でしょう。

これだけで、通常の symfonyアプリケーションと同様にモデルの操作などを行えます!

>**NOTE**
>ORM オブジェクトを使ってバッチ処理を行う場合は注意が必要です。Propel と Doctrine のいずれの場合でも、よく知られた PHP の循環参照とガベージコレクターのバグによりメモリーリークが発生する問題の影響を受けます。この問題は PHP 5.3 で部分的に解消されています。

メールの送信
------------

タスクのよくある使用法の1つに、メールの送信があります。symfony 1.3 まではメールの送信は単純な作業ではありませんでした。symfony 1.3 からは高機能な PHP メーラーライブラリ [Swift Mailer](http://swiftmailer.org/) が完全に統合されたので、これを使ってみましょう!

symfony のタスクシステムは `sfCommandApplicationTask::getMailer()` メソッドでメーラーオブジェクトを公開しています。これを使って簡単にメーラーオブジェクトにアクセスしてメールを送信できます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $mailer  = $this->getMailer();
      $mailer->composeAndSend($from, $recipient, $subject, $messageBody);
    }

>**NOTE**
>メーラーのコンフィギュレーションはアプリケーションコンフィギュレーションから読み込まれるので、メーラーオブジェクトを使うにはタスクで application オプションを受け取る必要があります。

-

>**NOTE**
>メーラーのスプールを有効にしている場合は、`project:send-emails` タスクを実行するまでメールは送信されません。

多くの場合、送りたいメッセージの内容がすでに魔法の変数 `$messageBody` に設定されて送信を待っていることはないので、何らかの方法で生成します。symfony でメールの内容を生成する推奨の方法はありませんが、以降でいくつかの TIPS を紹介します。

### 内容の生成を委譲する

例として、送信するメールの内容を返す protected なメソッドをタスクに作ります:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->getMailer()->composeAndsend($from, $recipient, $subject, $this->getMessageBody());
    }

    protected function getMessageBody()
    {
      return 'Hello, World';
    }

### Swift Mailer の Decorator プラグインを使う

Swift Mailer にはシンプルで効果的なテンプレートエンジンである [`Decorator`](http://swiftmailer.org/docs/decorator-plugin) プラグインがあり、宛先ごとに置き換え値の組み合わせを使えるシンプルなテンプレートエンジンを使って、送信するすべてのメールに置き換えを適用できます。

詳細は [Swift Mailerのドキュメント](http://swiftmailer.org/docs/) を参照してください。

### 外部のテンプレートライブラリーを使う

サードパーティのテンプレートライブラリーを使うのも簡単です。たとえば、Symfony Components Project の 1 つとしてリリースされた新しいテンプレートコンポーネントを使えます。コンポーネントのコードをプロジェクトのどこか(`lib/vendor/templating/`がよいでしょう)に配置し、次のコードをタスクに追加します:

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

### 2 つのテンプレートシステムのよい点を使う

もう少しメーラーについて考えてみましょう。Swift Mailer の `Decorator` プラグインは宛先ごとの置き換えを管理できるのでとても便利です。つまり、各宛先に対して一連の置き換えを定義すると、Swift Mailer により、トークンが、送信されるメールの宛先に応じた適切な値に置き換えられます。これをテンプレートコンポーネントと組み合わせてみます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $message = Swift_Message::newInstance();

      // ユーザーの一覧を取得する
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

`apps/frontend/templates/emails/user_specific_data.php` ファイルには次のコードを記述します:

    Hi {username}!

    We just wanted to let you know your specific data:

    {specific_data}

これだけで、完全に機能するメール本文用のテンプレートエンジンを使えるようになりました!

URL を生成する
--------------

メールの内容を作成する際に、ルーティングコンフィギュレーションを元に URL を生成したい場合があります。symfony 1.3 ではこのような URL の生成は簡単で、タスク内で `sfCommandApplicationTask::getRouting()` メソッドを使うことで現在のアプリケーションのルーティングに直接アクセスできます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $routing = $this->getRouting();
    }

>**NOTE**
>ルーティングはアプリケーションに依存しているので、開発しているアプリケーションでアプリケーションコンフィギュレーションが利用可能である必要があります。利用可能でない場合はルーティングを使った URL の生成を行えません。
>
>タスクでアプリケーションコンフィギュレーションを自動的に有効にする方法については「特別なオプション」の節を参照してください。

ルーティングのインスタンスにアクセスできたので、`generate()` メソッドを使って URL 文字列を次のように生成できます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('default', array('module' => 'foo', 'action' => 'bar'));
    }

最初の引数はルートの名前で、2 番目の引数はルートのパラメーター配列です。これで相対 URL を生成することができ、通常はこれで十分です。ただし、HTTP ホスト名を取得するための `sfWebRequest` オブジェクトがタスクからは利用できないので、絶対 URL を生成することはできません。

この問題の簡単な解決策として、`factories.yml` コンフィギュレーションファイルに HTTP ホストを設定しておく方法があります:

    [yml]
    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true
          context:
            host: example.org

`context_host` 設定を見てください。この設定はルーティングで絶対 URL を生成する際に使われます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('my_route', array(), true);
    }

I18N システムへのアクセス
------------------------

すべてのファクトリーがメーラーやルーティングのように簡単にアクセスできるわけではありません。しかし他のファクトリーオブジェクトにアクセスしたい場合でも、ファクトリーオブジェクトをインスタンス化するのはさほど難しいことではありません。たとえばタスクを国際化したい場合、symfony の i18n サブシステムにアクセスする必要があります。`sfFactoryConfigHandler` を使うと、簡単にファクトリーオブジェクトをインスタンス化できます:

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

このコードで何を行っているのか見てみましょう。まず、毎回の呼び出しごとに i18n コンポーネントが再構築されないように単純なキャッシュ機構を使っています。次に `sfFactoryConfigHandler` を使って、コンポーネントのコンフィギュレーションを取得し、コンポーネントをインスタンス化しています。最後にカルチャーコンフィギュレーションを設定しています。これでタスクから国際化機能にアクセスできます:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log($this->getI18N('fr')->__('some translated text!'));
    }

もちろん、タスクで頻繁にカルチャーを変更するのでない限り、毎回カルチャーを指定するのは面倒です。このような場合のテクニックを次の節で紹介します。

タスクのリファクタリング
------------------------

メールの送信や内容の生成、および URL の生成は 2 つのよくあるタスクなので、この 2 つの機能をすべてのタスクで自動的使えるようにするベースタスクを作ると便利です。これはとても簡単です。たとえばプロジェクト内の `lib/task/sfBaseEmailTask.class.php` に、次のようなベースクラスを作ります:

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

ここで、タスクのオプションの設定も自動化しておきましょう。次のメソッドを `sfBaseEmailTask` クラスに追加します:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    }

    protected function generateUrl($route, $params = array())
    {
      return $this->getRouting()->generate($route, $params, true);
    }

継承するタスクで共通して使われるオプションを `configure()` メソッドに追加します。ただし、`sfBaseEmailTask` を継承するタスクの `configure()` メソッド内で `parent::configure` を呼び出す必要があります。これは、すべてのクラスに同じオプションの記述を追加することに比べれば、さほど手間ではないでしょう。

それでは前の節の i18n にアクセスするコードをリファクタリングしましょう:

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

ここで 1 つ問題がありました。タスクでは、`execute()` メソッドのスコープ外で引数やオプションの値にアクセスできません。これを解決するために単純に `process()` メソッドをオーバーライドし、オプションのマネージャーオブジェクトの参照を保存するようにします。オプションマネージャーは、その名前のとおり、現在のタスクの引数とオプションを管理しています。たとえば、`getOptionValue()` メソッドを使ってオプションの値にアクセスできます。

タスク内でタスクを実行する
--------------------------

タスクをリファクタリングする別の方法として、タスクを別のタスクの中に埋め込む方法があります。これは、`sfCommandApplicationTask::createTask()` メソッドと `sfCommandApplicationTask::runTask()` メソッドを利用して簡単に行えます。

`createTask()` メソッドでタスクをインスタンス化します。コマンドラインで実行するように、単にタスクの名前を引数で渡すと、指定したタスクのインスタンスが返され、実行できます:

    [php]
    $task = $this->createTask('cache:clear');
    $task->run();

面倒な場合は、`runTask` メソッドで一度に実行できます:

    [php]
    $this->runTask('cache:clear');

もちろん、引数とオプションを渡すこともできます(順番はこのとおりです):

    [php]
    $this->runTask('plugin:install', array('sfGuardPlugin'), array('install_deps' => true));

タスクの埋め込みは、単純なタスクを組み合わせて強力なタスクを作るのに便利です。たとえば、プロジェクトをデプロイした後に実行するいくつかのタスクをまとめて、`project:clean` タスクを作れます:

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

ファイルシステムを操作する
--------------------------

symfony には組み込みの単純なファイルシステム抽象化機構(`sfFilesystem`)があり、ファイルやディレクトリの単純な操作を行えます。`sfFilesystem` にはタスク内から `$this->getFilesystem()` のようにアクセスできます。`sfFilesystem` には、次のようなメソッドがあります:

* `sfFilesystem::copy()`: ファイルをコピーする
* `sfFilesystem::mkdirs()`: ディレクトリを再帰的に作る
* `sfFilesystem::touch()`: ファイルを作る
* `sfFilesystem::remove()`: ファイルまたはディレクトリを削除する
* `sfFilesystem::chmod()`: ファイルまたはディレクトリのパーミッションを変更する
* `sfFilesystem::rename()`: ファイルまたはディレクトリの名前を変更する
* `sfFilesystem::symlink()`: ディレクトリへのリンクを作る
* `sfFilesystem::relativeSymlink()`: ディレクトリへの相対リンクを作る
* `sfFilesystem::mirror()`: ファイルツリーの完全なミラーを作る
* `sfFilesystem::execute()`: 任意のシェルコマンドを実行する

また、便利な `replaceTokens()` メソッドもありますが、これについては次の節で説明します。

スケルトンを使ってファイルを生成する
------------------------------------

よくあるタスクの別の使われ方として、ファイルの生成があります。ファイルの生成は、スケルトンと先に触れた `sfFilesystem::replaceTokens()` メソッドを使うと簡単に実行できます。名前から分かるように、このメソッドで一連のファイルの中のトークンを置き換えることができます。つまり、メソッドにファイルの配列とトークンのリストを渡すと、配列のすべてのファイルに対して、トークンの箇所を割り当てられた値に置き換えることができます。

このメソッドの便利さを理解するために、既存の組み込みタスクである `generate:module` を部分的に書き換えてみましょう。分かりやすくするためにこのタスクの `execute` メソッドの部分だけに着目します。必要なオプションで適切に設定されていると仮定します。バリデーションについてもここでは割愛します。

タスクを記述し始める前に、作成するディレクトリやファイルのスケルトンを準備し、たとえば `data/skeleton/` ディレクトリに保存します:

    data/skeleton/
      module/
        actions/
          actions.class.php
        templates/

`actions.class.php` スケルトンは次のようになっています:

    [php]
    class %moduleName%Actions extends %baseActionsClass%
    {
    }

タスクの処理の最初のステップは、ファイルツリーを適切な場所へミラーすることです:

    [php]
    $moduleDir = sfConfig::get('sf_app_module_dir').$options['module'];
    $finder    = sfFinder::type('any');
    $this->getFilesystem()->mirror(sfConfig::get('sf_data_dir').'/skeleton/module', $moduleDir, $finder);

次に、`actions.class.php` のトークンを置き換えます:

    [php]
    $tokens = array(
      'moduleName'       => $options['module'],
      'baseActionsClass' => $options['base-class'],
    );

    $finder = sfFinder::type('file');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '%', '%', $tokens);

これで、カスタマイズできるトークンの置き換え処理を使って、新しいモジュールが生成できました。

>**NOTE**
>実際、組み込みの `generate:module` タスクも `data/skeleton/` ディレクトリにあるスケルトンをデフォルトのスケルトンの代わりに使います。ここで作ったものが残っている場合は注意してください。

dry-run オプションを使う
------------------------

タスクを実際に実行する前に、実行結果をプレビューしたい場合があります。この機能を実装するためのいくつかの TIPS を紹介します。

最初に、`dry-run` のように標準的な名前を使うことを推奨します。標準的な名前を使うことで、多くの人がオプションの意味をすぐに理解できます。symfony 1.3 より以前のバージョンでは、`sfCommandApplication` によりデフォルトで `dry-run` オプションが*追加されていました*が、現在は手作業で追加する必要があります(上で説明したようにベースクラスなどに):

    [php]
    $this->addOption(new sfCommandOption('dry-run', null, sfCommandOption::PARAMETER_NONE, 'Executes a dry run');

タスクを次のように実行できるようになります:

    ./symfony my:task --dry-run

`dry-run` オプションは、タスクが実際には変更を行わないことを示します。

*実際には変更を行わない*ということがキーワードなので、覚えておいてください。`dry-run` モードでタスクを実行する場合、タスクは実行前の環境をそのままにしなくてはなりません。この環境には次のようなものがあります:

* データベース: テーブルへの追加、更新および削除を行ってはいけません。これには、トランザクションを使います。
* ファイルシステム: ファイルシステムへの作成、変更および削除を行ってはいけません。
* メールの送信: メールを送信してはいけません。またはデバッグ用のアドレスに送信します。

`dry-run` オプションを使う簡単なサンプルは次のとおりです:

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

ユニットテストを書く
--------------------

タスクにはさまざまな目的があるので、タスクのユニットテストを行うのは簡単ではありません。同様にタスクをテストする方法もさまざまですが、タスクをテストしやすくするための基本原則があります。

最初に、コントローラーのようなタスクについて考えます。コントローラーについてのルールはご存知でしょうか?*Thin controllers, fat models(軽量なコントローラーと高機能なモデル)*です。つまり、ビジネスロジックはすべてモデル内に記述します。こうすることで、タスクではなくモデルをテストすればよく、タスクよりも容易にテストできます。

モデルのロジックが増えてこれ以上追加できなくなった場合は、`execute()` メソッドを分割して、テストしやすいコードのまとまりごとに自身からアクセスできる(public アクセス)メソッドにします。コードの分割には、いくつかの利点があります:

  1. タスクの `execute` メソッドの可読性が上がります
  1. タスクがテストしやすくなります
  1. タスクを拡張しやすくなります

想像力を働かせて、ためらわずにテスト用に小さくて具体的な環境を用意してみましょう。もし、記述したすばらしいタスクのテスト方法が見つからない場合、それは記述方法が間違っているか、誰か他の人にアドバイスを求める必要があることを示しています。また、他の人がどのようにコードをテストしているのかをいつでも見ることができます。たとえば symfony のタスクは、ジェネレーターでさえもすべてテストが記述されています。

ヘルパーメソッド: ロギング
-------------------------

symfony のタスクシステムにはロギングやユーザーとのインタラクションといった共通操作用の便利なヘルパーメソッドがあり、開発者の作業が楽になるよう工夫されています。

たとえば、`log` ファミリーのメソッドを使うと `STDOUT` へ簡単にメッセージをロギングできます:

  * `log`: メッセージの配列を受け取ります
  * `logSection`: 多少高機能で、最初の引数のプレフィックスや、4番目の引数のメッセージの種類でメッセージをフォーマットできます。
    ファイルパスのように長いメッセージをロギングする場合、通常 `logSection` では余分なメッセージが切捨てられます。
    3番目の引数で、使うメッセージに合うようにメッセージの最大長を指定できます。
  * `logBlock`: 例外で使われているロギングのスタイルです。フォーマット用のスタイルを引数で渡すことができます。

利用可能なロギングのフォーマットは `ERROR`、`INFO`、`COMMENT` および `QUESTION` です。それぞれ試して確認してみてください。

使用例:

    [php]
    $this->logSection('file+', $aVeryLongFileName, $this->strlen($aVeryLongFileName));

    $this->logBlock('Congratulations! You ran the task successfuly!', 'INFO');

ヘルパーメソッド: ユーザーインタラクション
------------------------------------------

ユーザーインタラクションの処理を容易にするためのヘルパーが 3 つあります:

  * `ask()`: 質問を表示し、ユーザーの任意の入力を返します。

  * `askConfirmation()`: ユーザーに確認を表示し、ユーザーの入力として `y`(はい)と `n`(いいえ)を受け付けます。

  * `askAndValidate()`: とても便利なメソッドで、質問を表示し、ユーザーの入力を2番目の引数で渡された `sfValidator`でバリデートします。
    3番目の引数はオプションの配列で、デフォルト値(`value`)、最大試行回数(`attempts`)、フォーマットスタイル(`style`)を指定できます。

たとえば、ユーザーにメールアドレスを問い合わせ、入力された値をその場でバリデートできます:

    [php]
    $email = $this->askAndValidate('What is your email address?', new sfValidatorEmail());

ボーナスラウンド: crontab からタスクを使う
------------------------------------------

ほとんどの UNIX や GNU/Linux システムでは、*cron* と呼ばれる機構でタスクのプランニングを行えます。*cron* は、*crontab* というコンフィギュレーションファイルをチェックして、特定の時間にコマンドを実行します。symfony のタスクは簡単に crontab に追加できます。たとえば `project:send-emails` タスクの場合は次のように crontab に記述します:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails

このコンフィギュレーションでは、毎日午前 3 時に `project:send-emails` タスクを実行し、タスクからの出力(たとえばログやエラーなど)を *you@example.org* というメールアドレスに送信するよう *cron* に指示しています。

>**NOTE**
>crontab コンフィギュレーションファイルのフォーマットの詳細を知りたい場合は、ターミナルで `man 5 crontab` と入力してください。

crontab ではタスクの引数やオプションも指定できます:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails --env=prod --application=frontend

>**NOTE**
>お使いの環境の PHP CLI バイナリの場所に合わせて `/usr/bin/php` を書き換えてください。PHP CLI バイナリの場所が分からない場合は、Linux システムでは `which php` コマンド、UNIX システムでは `whereis php` コマンドを実行してください。

ボーナスラウンド: STDIN を使う
-----------------------------

タスクはコマンドライン環境で使うので、標準入力ストリーム(STDIN)にアクセスできます。UNIX のコマンドラインでは、アプリケーションがさまざまな目的で相互にやりとりでき、その方法の 1 つに *|* という文字で表される*パイプ*があります。*パイプ*を使うと、あるアプリケーションの出力(*STDOUT*)を別のアプリケーションの標準入力(*STDIN*)に渡せます。これらのデータは、PHP の特殊定数 `STDIN` と `STDOUT` を使うことでタスクからアクセスできます。3つめの標準ストリームとしてアプリケーションのエラーメッセージをやり取りする *STDERR* があり、`STDERR` を使ってアクセスできます。

では標準入力を使ってどのようなことができるのでしょうか?サーバー上のアプリケーションで、symfony アプリケーションと通信したい場合を考えてみましょう。もちろん HTTP 経由で通信することもできますが、アプリケーションの出力をパイプ経由で symfony タスクに送る方が効率的です。アプリケーションが、データベースに保存したいドメインオブジェクトを、たとえば PHP の配列のシリアライズデータのような構造化されたデータを送信するとします。次のようにタスクを記述できます:

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

このタスクを次のように使います:

    /usr/bin/data_provider | ./symfony data:import

`data_provider` は新しいドメインオブジェクトを送信するアプリケーションで、`data:import` は作成したタスクです。

最後に
------

あなたの想像力次第で、タスクをより有効に活用できます。symfony のタスクシステムは強力で柔軟なので、思いついたことをすぐに実現できるでしょう。これに強力な UNIX シェルが加われば、あなたはタスクが大好きになるに違いありません!
