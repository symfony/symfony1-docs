第17章 - symfony を拡張する
============================

結局のところ、symfony のふるまいを変える必要があります。特定のクラスのふるまいを修正するもしくは独自のカスタム機能を追加する必要があるにせよ、変更作業は必然です。フレームワークが予想できない固有の要望を顧客が持つからです。実際のところ、この状況はとてもありふれているので symfony は単純なクラスの継承を越えて、実行時に既存のクラスを拡張する方法を提供します。ファクトリ (factory) の設定を利用することで、symfony のコアクラスを独自クラスに置き換えることさえできます。いったん拡張機能 (エクステンション) を作れば、それをプラグインとして簡単にパッケージにできるので、別のアプリケーションもしくは別の symfony のユーザーが再利用できます。

イベント
--------

PHPの現在の制限のなかで、もっとも悩ましいことは複数のクラスを継承できるクラスを持ていないことです。
別の制限は既存のクラスに新しいクラスを追加できないこともしくは既存のクラスをオーバーライドできないことです。
これらの2つの制限を緩和して symfony フレームワークを本当に拡張できるものにするために、
symfony は*イベントシステム*を導入します。
この機能は Cocoa フレームワークのイベントマネージャにインスパイアされ
[Observer デザインパターン](http://en.wikipedia.org/wiki/Observer_pattern)に基づいています。

### イベントを理解する

symfony のクラスのなかにはさまざまな瞬間に"イベントのディスパッチャを通知する"ものがあります。たとえば、ユーザーがカルチャを変更するとき、ユーザーオブジェクトは `change_culture` イベントを通知します。このイベントはプロジェクト空間のなかでつぎの内容を大声で叫ぶことに似ています: "それをやっています。お望みのことは何でもしてください。"

イベントが起きるときに何か特別なことを実行することに決めることができます。たとえば、ユーザーが望むカルチャを記録するために、`change_culture` イベントが通知されるたびにユーザーカルチャをデータベースのテーブルに保存できます。これを行うには、*イベントリスナーを登録する必要があります*。イベントリスナー (event listener) はイベントが起きるときに呼び出される関数を宣言することを伝える複雑なステートメントです。リスト17-1は `change_culture` イベントにリスナーを登録する方法を示してます。

リスト 17-1 - イベントリスナーを登録する

    [php]
    $dispatcher->connect('user.change_culture', 'changeUserCulture');
    
    function changeUserCulture(sfEvent $event)
    {
      $user = $event->getSubject();
      $culture = $event['culture'];

      // ユーザーカルチャで何かを行う
    }

すべてのイベントとリスナーの登録は *Event Dispatcher* と呼ばれる特別なオブジェクトによって管理されます。
このオブジェクトは `ProjectConfiguration` インスタンスを利用することでsymfonyのどこからでもアクセス可能で、
たいていの symfony のオブジェクトはそれにアクセスできる `getEventDispatcher()` メソッドを提供します。
ディスパッチャの `connect()` メソッドを利用することで、
イベントが起きるときに呼び出される PHP の callable (クラスのメソッドもしくは関数) を登録できます。
 `connect()` の最初の引数はイベントの識別子で、名前空間と名前で構成される文字列です。2番目の引数は PHP の callable です。

>**Note**
>アプリケーションの任意の場所から Event Dispatcher を読みとることができます:
>
>     [PHP]
>     $dispatcher = ProjectConfiguration::getActive()->getEventDispatcher();

いったん関数が Event Dispatcher に登録されると、イベントは停止されるまで待機します。
Event Dispatcher はすべてのイベントリスナーの記録を行い、イベントが通知されるときに
どれが呼び出されるのかを知っています。これらのメソッドもしくは関数を呼び出すとき、
ディスパッチャはこれらに `sfEvent` オブジェクトをパラメーターとして渡します。

イベントオブジェクトは通知されたイベントに関する情報を保存します。`getSubject()` メソッドのおかげでイベント通知オブジェクト (notifier) を読みとることが可能で、イベントパラメーターはイベントオブジェクトを配列として利用することでアクセスできます(たとえば、`user.change_culture` を通知するとき `sfUser` によって渡される `culture` パラメーターを読みとるために `$event['culture']` を利用できます)。

まとめると、イベントシステムによって、継承を利用せずに、既存のクラスに機能を追加するもしくは実行時にメソッドを修正できるようになります。

### イベントを通知する

symfony のクラスがイベントを通知するように、独自のクラスは実行時の拡張性を提供し特定の状況時にイベントを通知できます。たとえば、アプリケーションがいくつかのサードパーティの Web サービスをリクエストしこれらのリクエストの REST ロジックをラップするために `sfRestRequest` クラスを書いた場合を考えてみましょう。このクラスが新たにリクエストするたびにイベントを通知するのはよいアイデアといえます。これによって将来のロギング機能もしくはキャッシュ機能の追加が簡単になります。リスト17-2はイベントを通知するために既存の `fetch()` メソッドに追加するコードを示してます。

リスト 17-2 - イベントを通知する

    [php]
    class sfRestRequest
    {
      protected $dispatcher = null;

      public function __construct(sfEventDispatcher $dispatcher)
      {
        $this->dispatcher = $dispatcher;
      }
      
      /**
       * 外部の Web サービスにクエリを行う
       */
      public function fetch($uri, $parameters = array())
      {
        // 取得プロセスの初めのディスパッチャを通知する
        $this->dispatcher->notify(new sfEvent($this, 'rest_request.fetch_prepare', array(
          'uri'        => $uri,
          'parameters' => $parameters
        )));
        
        // リクエストを行い結果を変数 $result に保存する
        // ...
        
        // 取得プロセスの終了を通知する
        $this->dispatcher->notify(new sfEvent($this, 'rest_request.fetch_success', array(
          'uri'        => $uri,
          'parameters' => $parameters,
          'result'     => $result
        )));
        
        return $result;
      }
    }

Event Dispatcher の `notify()` メソッドは `sfEvent` オブジェクトをパラメーターとして必要とします; これはイベントリスナーに渡されるオブジェクトそのものです。このオブジェクトはつねに参照を通知機能とイベント識別子に運びます(これがイベントのインスタンスが `$this`  で初期化される理由)。オプションとして、これはパラメーターの連想配列を受けとります。これはリスナーに通知機能のロジックと情報をやりとりする方法を提供します。

### リスナーがイベントを処理するまでイベントを通知する

`notify()` メソッドを利用することで、通知されたイベントに登録されたすべてのリスナーが実行されることを確認できます。
しかしいくつかの場合において、リスナーがイベントを停止させて通知されないようにさせることが必要です。
この場合、`notify()`メソッドの代わりに `notifyUntil()` メソッドを使うべきです。
ディスパッチャは特定のリスナーが `true` を返すまですべてのリスナーを実行します。
言い換えると、`notifyUntil()` メソッドはプロジェクトの空間で「やってます。
誰かが気づいたら、誰かに伝えることを止めます。」ということを伝えるようなものです。
リスト17-3はメソッドを追加するために `__call()` マジックメソッドを組み合わせるテクニックの使いかたを示しています。

リスト17-3 - リスナーが `true` を返すまでイベントを通知する

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

`rest_request.method_not_found` イベントに登録されたすべてのリスナーはリクエストされた `$method` をテストしそれを扱うことを決定する、もしくはつぎの呼び出し可能なイベントリスナーへの移動します。リスト17-4において、このトリックを利用してサードパーティのクラスが `sfRestRequest` クラスに `put()` メソッドと `delete()` メソッドを実行時に追加する方法を見ることができます。

リスト 17-4 - 「条件を満たすまで通知する」タイプのイベントを処理する

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        // リスナーを登録する
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
            self::put($event->getSubject(), $event['arguments']);

            return true;
          case 'delete':
            self::delete($event->getSubject(), $event['arguments']);

            return true;
          default:
            return false;
        }
      }

      static protected function put($restRequest, $arguments)
      {
        // putリクエストを行い結果を変数 $result に保存する
        // ...
        
        $event->setReturnValue($result);
      }
      
      static protected function delete($restRequest, $arguments)
      {
        // delete リクエストを行い結果を変数 $result に保存する
        // ...
        
        $event->setReturnValue($result);
      }
    }

実際には、`notifyUntil()` メソッドは、mixin (既存のクラスにサードパーティのメソッドを追加する) よりも、PHP に多重継承の機能を提供します。これで継承では拡張できないオブジェクトに新しいメソッドを「注入 (inject)」できます。そしてこれは実行時に行われます。これで symfony を利用するときに PHP のオブジェクト指向の機能によって制限されることはありません。

>**TIP**
>`notifyUntil` イベントをキャッチする最初のリスナーはそれ以降のイベント通知を防止するので、
>リスナーの実行順序が不安になるかもしれません。
>この順序はリスナーの登録順序に対応します。つまり最初に登録されたものが最初に実行されます。
>しかし実際には、登録順序が原因でほかのリスナーの実行を妨げることはほとんどありません。
>2つのリスナーが特定のイベントで衝突することに気がついたら、
>一方のクラスに新たなイベント通知を追加すればよいのです。
>たとえばメソッド実行の始めと終わりなどです。
>そして既存のクラスに新しいメソッドを追加するためにイベントを使う場合、
>メソッドを追加する時点でほかのものが衝突しないようにメソッドの名前をつけてください。
>メソッドの名前のプレフィックスをリスナークラスの名前にするのはよい習慣です。

### メソッドの戻り値を変更する

リスナーがイベントによって渡された情報を利用するだけでなく、通知機能のオリジナルのロジックを変更するためにそれを修正する方法も想像できるでしょう。これを実現したければ、`notify()` メソッドよりも `filter()` メソッドを使うべきです。すべてのイベントリスナーは2つのパラメーター: イベントオブジェクトとフィルタリングする値で呼び出されます。値を変更するかしないかにかかわらず、イベントリスナーは値を返さなければなりません。リスト17-5は Web サービスからレスポンスをフィルタリングしてそのレスポンスのなかで特別な文字をエスケープするために使われる `filter()` メソッドを表示します。

リスト 17-5 - フィルターイベントを通知して扱う

    [php]
    class sfRestRequest
    {
      // ...
      
      /**
       * 外部の Web サービスにクエリを行う
       */
      public function fetch($uri, $parameters = array())
      {
        // リクエストを行い結果を$result変数に保存する
        // ...
        
        // 取得プロセスの終了を通知する
        return $this->dispatcher->filter(new sfEvent($this, 'rest_request.filter_result', array(
          'uri'        => $uri,
          'parameters' => $parameters,
        )), $result)->getReturnValue();
      }
    }

    //  Web サービスのレスポンスにエスケーピング機能を追加する
    $dispatcher->connect('rest_request.filter_result', 'rest_htmlspecialchars');

    function rest_htmlspecialchars(sfEvent $event, $result)
    {
      return htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
    }

### 組み込みのイベント

symfony のクラスの多くは組み込みのイベントを持つので、クラス自身を変更せずにフレームワークを拡張できます。テーブル17-1はこれらのイベント、それぞれのタイプと引数の一覧を示しています。

テーブル17-1 - symfonyのイベント

| **イベントの名前** (**タイプ**)                 | **通知元**                     | **引数**                    |
| ---------------------------------------------- | ----------------------------- | --------------------------- |
| application.log (notify)                       | たくさんのクラス               | priority                    |
| application.throw_exception (notifyUntil)      | sfException                   | -                           |
| autoload.filter_config (filter)                | sfAutoloadConfigHandler       | -                           |
| command.log (notify)                           | sfCommand* クラス              | priority                    |
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

これらのイベントにすべてのリスナーを自由に登録できます。呼び出し可能なリスナーは、`notifyUntil` 型のイベントに登録されたときブール値を返し、`filter` 型のイベントに登録されたときはフィルタリングされた値を返すことを確認してください。

イベントの名前空間がクラスの役割にマッチする必要はかならずしもないことに注意してください。たとえば、すべての symfony のクラスは何かをログファイル(と Web デバッグツールバー)に表示する必要があるときに `application.log` イベントを通知します:

    [php]
    $dispatcher->notify(new sfEvent($this, 'application.log', array($message)));

これがつじつまが合っているとき独自のクラスは同じことを行い symfony のイベントに通知も行います。

### リスナーを登録する場所は？

symfony のリクエストの初期段階でイベントリスナーを登録することが必要です。実際には、イベントリスナーを登録する正しい場所はアプリケーションの設定クラスです。このクラスは `configure()` メソッドで使える Event Dispatcher への参照を持ちます。リスト17-6は上記の例の `rest_request` イベントの1つにリスナーを登録する方法を示しています。

リスト17-6 - アプリケーションの設定クラスにリスナーを登録する (`apps/frontend/config/ApplicationConfiguration.class.php`)

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('rest_request.method_not_found', array('sfRestRequestExtension', 'listenToMethodNotFound'));
      }
    }

プラグイン(下記を参照)は独自のイベントリスナーを登録できます。これらのプラグインはプラグインの `config/config.php` スクリプトでこれを実行します。これはアプリケーションの初期化のあいだに実行され `$this->dispatcher` を通して Event Dispatcher にアクセスできます。

ファクトリ
----------

ファクトリ (factory) は特定のタスク用のクラスの定義です。symfony はコントローラーやセッション機能などのコア機能についてファクトリに依存します。たとえば、symfony が新しいリクエストオブジェクトを作る必要がある場合、symfony はこの目的のために使うクラスの名前のためのファクトリの定義を検索します。リクエスト用のデフォルトのファクトリの定義は `sfWebRequest` クラスで、symfony はリクエストを処理するためにこのクラスのオブジェクトを作ります。ファクトリの定義の利点は symfony のコア機能をとても簡単に変更できることです: ファクトリの定義を変更し、symfony  は自身の代わりにカスタムリクエストクラスを使います。

ファクトリの定義は `factories.yml` 設定ファイルに保存されます。リスト17-7はデフォルトのファクトリの定義ファイルを示します。それぞれの定義はオートロードされるクラスの名前と(オプションとして)パラメーターの一式から構成されます。たとえば、セッションストレージのファクトリ (`storage:` キーの下で設定) は一貫したセッションを可能にするためにクライアントコンピュータで作られたクッキーに名前をつける `session_name` パラメーターを使います。

リスト17-7 - デフォルトのファクトリファイル (`frontend/config/factories.yml`)

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

ファクトリを変更する最良の方法はデフォルトのファクトリから継承した新しいクラスを作り、そのクラスに新しいメソッドを追加することです。たとえば、ユーザーセッションのファクトリは `myUser` クラス (`frontend/lib/` ディレクトリ) で設定され、`sfUser` クラスを継承します。既存のファクトリを使うには同じメカニズムを利用します。リスト17-8はリクエストオブジェクトのための新しいファクトリの例を示しています。

リスト17-8 - ファクトリをオーバーライドする

    [php]
    //オートロードされたディレクトリで myRequest.class.php を作る
    // たとえばfrontend/lib/において
    <?php

    class myRequest extends sfRequest
    {
      // あなたのコードをここに
    }

    // factories.yml でクラスをリクエストファクトリとして宣言する
    all:
      request:
        class: myRequest

プラグイン
----------

symfony アプリケーションのために開発したコードのピースの再利用が必要になるでしょう。このコードのピースを単独のクラスのパッケージにすることができるのであれば、問題ありません: クラスを別のアプリケーションの `lib/`  フォルダーの1つに設置すればオートローダが残りを引き受けます。しかし、コードが複数のファイルに散在している場合、たとえば、アドミニストレーションジェネレーター用の完全に新しいテーマ、もしくは好みの視覚効果を自動化するJavaScriptファイルとヘルパの組み合わせなどの場合、ファイルをコピーするだけでは最良の解決方法とは言えません。

プラグインは複数のファイルにまたがるコードをパッケージにする方法と、複数のプロジェクトをまたがってこのコードを再利用する方法を提供します。プラグインのなかで、クラス、フィルター、イベントリスナー、ヘルパ、設定、タスク、モジュール、スキーマ、モデルの拡張、フィクスチャ、 Web アセットなどをパッケージにすることができます。プラグインをインストール、アップグレード、アンインストールする方法は簡単です。これらは `.tgz` アーカイブ、PEAR パッケージ、もしくはコードリポジトリとして配布可能で、コードのリポジトリから簡単にチェックアウトできます。PEAR パッケージとなったプラグインは依存関係の管理機能を利用し、アップグレードと自動検出が簡単です。symfony  のロードメカニズムはプラグインを考慮し、プラグインによって提供される機能はあたかもフレームワークの一部であるかのようにプロジェクトで利用できます。

ですので、プラグインは基本的に symfony プロジェクトのために拡張機能をパッケージにしたものです。プラグインによってアプリケーションを越えて独自コードを再利用できるだけでなく、別の投稿者によって開発されたものも再利用可能で symfony  コアにサードパーティの拡張機能を追加できます。

### symfony のプラグインを見つける

symfony の公式サイトにはプラグイン専用のセクションが存在しており、つぎの URL からアクセスできます:

    http://www.symfony-project.org/plugins/

ここに掲載されている各プラグインはそれぞれのページでインストール方法の説明やドキュメントが整備されています。

ここにあるプラグインはコミュニティから寄せられたものもあれば、symfony のコア開発者が開発したものもあります。後者のものについては以下を参照してください。:

  * `sfFeed2Plugin`: RSS と Atom フィードの操作を自動化する
  * `sfThumbnailPlugin`: たとえばアップロードされたイメージのためにサムネイルを作る。
  * `sfMediaLibraryPlugin`: メディアのアップロードと管理を可能にします。リッチテキスト内部でイメージの編集を可能にするリッチテキストエディタのための拡張機能を含む
  * `sfGuardPlugin`: 認証、承認と symfony の標準的なセキュリティ機能を上回る別のユーザーの管理機能を提供する
  * `sfSuperCachePlugin`: Web のルートディレクトリ下にキャッシュを書き出すことで、Web サーバーの処理を可能なかぎり早めます
  * `sfErrorLoggerPlugin`: データベースにすべての404エラーと500エラーをログに記録しこれらのエラーを閲覧するためのアドミニストレーションモジュールを提供する
  * `sfSslRequirementPlugin`: アクションのための SSL 暗号化サポートを提供する

公式サイトのプラグインセクションを定期的に確認すべきです。いつも新しいプラグインが追加され、これらは Web アプリケーションのプログラミングの多くの面にとても便利なショートカットをもたらしてくれます。

公式サイトのプラグインセクションは別にして、プラグインを配布するほかの方法はダウンロードのためのプラグインアーカイブを提供することと、PEAR チャンネルでプラグインをホストすること、もしくは公開のバージョンコントロールリポジトリに保存することです。

### プラグインをインストールする

プラグインのインストール手順はプラグインパッケージの作り方によって異なります。つねに `README` ファイルかつ/もしくはプラグインのダウンロードページのインストールの手引きを参照してください。

プラグインはプロジェクト単位でインストールされます。つぎのセクションで説明されるすべての方法ではすべてのプラグインのファイルを `myproject/plugins/pluginName/` ディレクトリに設置します。

### PEAR プラグイン

公式サイトのプラグインセクションに記載されているプラグインは PEAR チャンネル: `plugins:symfony-project.org` を通して入手できます。プラグインをインストールするには、リスト17-9で示されるように、プラグインの名前と一緒に `plugin:install` タスクを使います。

リスト17-9 - 公式サイトの PEAR チャンネルからプラグインをインストールする

    $ cd myproject
    $ php symfony plugin:install pluginName

代わりの方法として、プラグインをダウンロードしてディスクからインストールすることもできます。この場合、リスト17-10で示されるように、パッケージアーカイブへのパスを使います。

リスト17-10 - ダウンロードしたパッケージからプラグインをインストールする

    $ cd myproject
    $ php symfony plugin:install /home/path/to/downloads/pluginName.tgz

プラグインのなかには外部の PEAR チャンネルでホストされるものがあります。リスト17-11で示されるように、`plugin:install` タスクでそれらをインストールしてチャンネルを登録してチャンネルの名前を記載することを忘れないでください。

リスト17-11 - PEAR チャンネルからプラグインをインストールする

    $ cd myproject
    $ php symfony plugin:add-channel channel.symfony.pear.example.com
    $ php symfony plugin:install --channel=channel.symfony.pear.example.com pluginName

これら3つのタイプのインストール方法はすべてPEARパッケージを使うので、「PEAR プラグイン」という用語は  symfony プラグインの PEAR チャンネル、外部の PEAR チャンネル、もしくはダウンロードした PEAR パッケージからインストールしたプラグインを区別なく説明するために使われます。

リスト17-12で示されているように、`plugin:install` タスクは多くのオプションをとります。

リスト17-12 - いくつかのオプションをつけてプラグインをインストールする

    $ php symfony plugin:install --stability=beta pluginName
    $ php symfony plugin:install --release=1.0.3 pluginName
    $ php symfony plugin:install --install-deps pluginName

>**TIP**
>すべての symfony タスクに関しては、`php symfony help plugin:install` を実行すれば `plugin:install` のオプションと引数の説明を見ることができます

#### アーカイブプラグイン

プラグインのなかには単純にファイルのアーカイブとしてやってくるものがあります。それらをインストールするには、アーカイブをプロジェクトの `plugins/` ディレクトリに解凍してください。プラグインが `web/` サブディレクトリを収納しているのであれば、リスト17-13で示されるように、`web/` フォルダーのもとで対応するシンボリックリンクを作成するために `plugin:publish-assets` コマンドを実行します。最後に、キャッシュをクリアすることをお忘れなく。

リスト17-13 - アーカイブからプラグインをインストールする

    $ cd plugins
    $ tar -zxpf myPlugin.tgz
    $ cd ..
    $ php symfony plugin:publish-assets
    $ php symfony cc

#### バージョン管理システムのリポジトリからプラグインをインストールする

プラグインはときにバージョン管理システム用の独自のソースコードリポジトリを持つことがあります。`plugins/` ディレクトリのなかでチェックアウトするだけでこれらのプラグインをインストールできますが、プロジェクト自身がバージョン管理システムの管理下にある場合、この作業によって問題が引き起こされる可能性があります。

代わりの方法として、プラグインを外部依存のライブラリとして宣言することが可能で、すべてのプロジェクトのソースコードを更新するとプラグインのソースコードも更新されます。たとえば、Subversion は `svn:externals` プロパティで外部依存を保存します。ですので、リスト17-14で示されているように、このプロパティを編集してソースコードをあとで更新することでプラグインを追加できます。

リスト17-14 - ソースのバージョン管理リポジトリからプラグインをインストールする

    $ cd myproject
    $ svn propedit svn:externals plugins
      pluginName   http://svn.example.com/pluginName/trunk
    $ svn up
    $ php symfony plugin:publish-assets
    $ php symfony cc

>**NOTE**
>プラグインが `web/` ディレクトリを含む場合、プロジェクトのメインの `web/` フォルダーのもとで対応するシンボリックリンクを作るために `plugin:publish-assets` コマンドを実行しなければなりません。

#### プラグインモジュールを有効にする

プラグインのなかにはモジュール全体を収納するものがあります。プラグインモジュールと古典的なモジュールの違いはプラグインモジュールが `myproject/frontend/modules/` ディレクトリに現れないことだけです (簡単にアップグレードできる状態を保つため)。リスト17-15で示されるように、`settings.yml` ファイルのなかでこれらを有効にしなければなりません。

リスト17-15 - プラグインモジュールを有効にする (`frontend/config/settings.yml`)

    all:
      .settings:
        enabled_modules:  [default, sfMyPluginModule]

これはプラグインモジュールを必要としないアプリケーションが誤ってそのプラグインを利用できるように設定する状況を避けるためです。その状況ではセキュリティの欠陥を公開してしまう可能性があります。`frontend`  モジュールと `backend`  モジュールを提供するプラグインを考えてください。`frontend` モジュールは`frontend`アプリケーション専用として、`backend` モジュールは `backend` アプリケーション専用として有効にする必要があります。プラグインモジュールがデフォルトで有効にされない理由はそういうわけです。

>**TIP**
>default モジュールはデフォルトで唯一有効なモジュールです。これは本当のプラグインモジュールではありません。フレームワークの `$sf_symfony_lib_dir/controller/default/` に所属するからです。これは初期ページと、404エラー用のデフォルトページとクレデンシャルが必要なエラーページを提供するモジュールです。symfony のデフォルトページを使いたくない場合、このモジュールを `enabled_modules` 設定から除外します。

#### インストールしたプラグインの一覧を表示する

プロジェクトの `plugins/` ディレクトリをざっと見るとプラグインがインストールされている場所がわかります。そして `plugin:list` タスクは詳細な情報を示します: バージョン番号とインストールしたそれぞれのプラグインのチャンネル名です。(リスト17-16を参照してください)

リスト17-16 - インストール済みのプラグインの一覧

    $ cd myproject
    $ php symfony plugin:list

    Installed plugins:
    sfPrototypePlugin               1.0.0-stable # plugins.symfony-project.com (symfony)
    sfSuperCachePlugin              1.0.0-stable # plugins.symfony-project.com (symfony)
    sfThumbnail                     1.1.0-stable # plugins.symfony-project.com (symfony)

#### プラグインのアップグレードとアンインストール

PEAR プラグインをアンインストールするには、リスト17-17で示されるように、プロジェクトのルートディレクトリから `plugin:uninstall` タスクを呼び出します。プラグインの名前にプラグインをインストールしたチャンネル名をプレフィックスとして追加しなければなりません (このチャンネルを決めるために `plugin:list` タスクを使います)。

リスト17-17 - プラグインをアンインストールする

    $ cd myproject
    $ php symfony plugin:uninstall sfSuperCachePlugin
    $ php symfony cc

アーカイブからインストールしたプラグインもしくは SVN リポジトリからインストールしたプラグインをアンインストールするには、プロジェクトの `plugins/` と `web/`  ディレクトリからプラグインのファイルを手動で削除してキャッシュをクリアします。

プラグインをアップグレードするには、`plugin:upgrade` タスク (PEAR プラグインの場合) もしくは `svn update` を実行します (バージョン管理システムのリポジトリからプラグインを入手した場合)。アーカイブからインストールしたプラグインは簡単にアップグレードできません。

### プラグインの分析

プラグインは PHP で書かれています。アプリケーションの編成方法を理解しているのであれば、プラグインの構造を理解できます。

#### プラグインのファイル構造

プラグインのディレクトリはおおよそプロジェクトのディレクトリと同じように編成されています。必要な時に symfony によって自動的にロードされるようにするためにプラグインファイルは正しいディレクトリに存在しなければなりません。ファイル構造の記述に関してはリスト17-18をご覧ください。

リスト17-18 - プラグインのファイル構造

    pluginName/
      config/
        routing.yml        // アプリケーションの設定ファイル
        *schema.yml        // データスキーマ
        *schema.xml
        config.php         // 特定のプラグイン設定
      data/
        generator/
          sfPropelAdmin
            */             // アドミニストレーションジェネレーターテーマ
              template/
              skeleton/
        fixtures/
          *.yml            // フィクスチャファイル
      lib/
        *.php              // クラス
        helper/
          *.php            // ヘルプ
        model/
          *.php            // モデルクラス
        task/
          *Task.class.php  // CLI タスク
      modules/
        */                 // モジュール
          actions/
            actions.class.php
          config/
            module.yml
            view.yml
            security.yml
          templates/
            *.php
      web/
        *                  // アセット

#### プラグインの機能

プラグインは多くのものを含みます。コマンドラインでタスクを呼び出すときに実行中のアプリケーションはこれらの内容を自動的に考慮します。しかしプラグインを適切に機能させるには、いくつかの規約を遵守しなければなりません:

  * データベースのスキーマは `propel-` タスクによって検出されます。`propel:build --classes` タスクまたは `doctrine:build --classes` タスクを呼び出すと、プロジェクトモデルとすべてのプラグインモデルがリビルドされます。リスト17-19で示されるように、プラグインスキーマはつねに `plugins.pluginName`. `lib.model` 形式で `package` 属性を持つことに注意してください。Doctrine を使っている場合は、プラグインディレクトリ内に自動的にクラスが生成されます。

リスト17-19 - スキーマ宣言の例 (`myPlugin/config/schema.yml`)

    propel:
      _attributes:    { package: plugins.myPlugin.lib.model }
      my_plugin_foobar:
        _attributes:    { phpName: myPluginFoobar }
          id:
          name:           { type: varchar, size: 255, index: unique }
          ...

  * プラグインの設定はプラグインのコンフィギュレーションクラス (`PluginNameConfiguration.class.php`) に格納されます。このファイルはアプリケーションとプロジェクト設定のあとで実行されるので、すでにその時点で symfony は起動しています。たとえば、既存のクラスをイベントリスナーもしくはビヘイビアで拡張するためです。
  * プラグインの `data/fixtures/` ディレクトリに設置されたフィクスチャファイルは `propel:data-load` タスク、または `doctrine:data-load` タスクで処理されます。
  * プロジェクトの `lib/` フォルダーに設置されたクラスのようにカスタムクラスはオートロードされます。
  * テンプレートのなかで `use_helper()` ヘルパを呼び出すときにヘルパは自動的に発見されます。これらはプラグインの`lib/`ディレクトリの1つの`helper/`サブディレクトリに存在しなければなりません。
  * Propel を使っている場合、`myplugin/lib/model/` ディレクトリのモデルクラスは (`myplugin/lib/model/om/` ディレクトリと `myplugin/lib/model/map/` ディレクトリ)内部の Propel ビルダによって生成されたモデルクラスを専門に扱います。もちろんこれらもオートロードされます。独自プロジェクトのディレクトリで生成されたプラグインのモデルクラスはオーバーライドできません。
  * Doctrine を使っている場合、ORM によりプラグインのベースクラスが `myplugin/lib/model/Plugin*.class.php` に生成され、実際のクラスが `lib/model/myplugin/` に生成されます。これにより、モデルクラスをアプリケーションで簡単にオーバーライドできます。 
  * プラグインをインストールすればタスクは symfony コマンドですぐに利用できます。プラグインは新しいタスクを追加もしくは既存のものを上書きできます。タスク用にプラグインの名前を名前空間として使うことは最良の習慣です。プラグインに追加されたものを含めて、利用可能なタスクの一覧を見るには、`php symfony` を入力してください。
  * アプリケーションの `enabled_modules` 設定で宣言すれば、モジュールは外部からアクセス可能な新しいアクションを提供します。
  * サーバーは Web アセット (イメージ、スクリプト、スタイルシートなど) を利用できます。コマンドライン経由でプラグインをインストールしたとき、システムが許可するのであれば symfony はプロジェクトの `web/` ディレクトリにシンボリックリンクを作るもしくは `web/` ディレクトリの内容をプロジェクトのディレクトリにコピーします。プラグインがアーカイブもしくはバージョン管理ツールのリポジトリからインストールされた場合、手動で `web/` ディレクトリにコピーしなければなりません(プラグインに添付されている `README` に記載されています)。

>**TIP** **ルーティングルールをプラグインに登録する**
>
>プラグインはルーティングシステムに新しいルールを追加できますが、そのためにカスタム `routing.yml` 設定ファイルを使うことはできません。これはルールが定義された順序が非常に重要で、symfony の YAML ファイルのシンプルなカスケードコンフィギュレーションシステムはこの順序をごちゃごちゃにするからです。代わりにプラグインはイベントリスナーを `routing.load_configuration` イベントに登録してリスナーの先頭にルールを手動で追加する必要があります:
>
>     [php]
>     // plugins/myPlugin/config/config.php のなか
>     $this->dispatcher->connect('routing.load_configuration', array('myPluginRouting', 'listenToRoutingLoadConfigurationEvent'));
>     
>     // plugins/myPlugin/lib/myPluginRouting.php のなか
>     class myPluginRouting
>     {
>       static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
>       {
>         $routing = $event->getSubject();
>         // 既存のルーティングルールの上にプラグインのルールを追加する
>         $routing->prependRoute('my_route', new sfRoute('/my_plugin/:action', array('module' => 'myPluginAdministrationInterface')));
>       }
>     }
>

#### 手動によるプラグインのセットアップ

`plugin:install` タスクが独自に処理できない要素がいくつかあります。インストール作業のあいだにこれらを手動でセットアップする必要があります:

  * カスタムアプリケーション設定はプラグインのコードで使われますが(たとえば、`sfConfig::get('app_myplugin_foo')` を利用する)、デフォルト値をプラグインの `config/` ディレクトリに設置された `app.yml` ファイルに設定できません。デフォルト値を処理するには、`sfConfig::get()` メソッドの2番目の引数を使います。設定はまだアプリケーションレベルでオーバーライドできます (リスト17-25で例をご覧ください)。
  * カスタムルーティングルールはアプリケーションの `routing.yml` に手動で追加しなければなりません。
  * カスタムフィルターはアプリケーションの `filters.yml` に手動で追加しなければなりません。
  * カスタムファクトリはアプリケーションの `factories.yml` に手動で追加しなければなりません。

一般的に言えば、アプリケーションの設定ファイルの1つに帰結するようなすべての設定は手動で追加しなければなりません。このような手動のセットアップが必要なプラグインは `README` ファイルで詳細なインストール方法を説明しています。

#### アプリケーションのためにプラグインをカスタマイズする

プラグインをカスタマイズしたいときは、`plugins/` ディレクトリ内で見つかるコードをけっして変更してはなりません。これを行うと、プラグインをアップグレードするときにすべての修正内容が失われてしまいます。必要なカスタマイズを行うために、プラグインはカスタム設定を提供し、オーバーライドをサポートします。

リスト17-20で示されるように、よく設計されたプラグインはアプリケーションの `app.yml` ファイルで変更できる設定を利用します。

リスト17-20 - アプリケーションの設定を利用するプラグインをカスタマイズする

    [php]
    // プラグインのコードの例
    $foo = sfConfig::get('app_my_plugin_foo', 'bar');

    // アプリケーションの app.yml で 'foo' のデフォルト値 ('bar') を変更する
    all:
      my_plugin:
        foo:       barbar

モジュールの設定とデフォルト値はプラグインの `README` ファイルで詳しく説明されています。

独自のアプリケーション内部で同じ名前のモジュールを作成することでプラグインモジュールのデフォルトの内容を置き換えることができます。プラグイン要素の代わりにアプリケーション要素が使われているので、本当の上書きではありません。プラグインの名前と同じ名前のテンプレートと設定ファイルを作ればプラグインモジュールは立派に機能します。

一方で、アクションをオーバーライドする機能を持つモジュールをプラグインに持たせたい場合、プラグインモジュールの `actions.class.php` のメソッドがアプリケーションモジュールの `actions.class.php` によって継承できるように、`actions.class.php` は空でなければならずオートロードクラスから継承しなければなりません。お手本に関してはリスト17-21を参照してください。

リスト17-21 - プラグインのアクションをカスタマイズする

    [php]
    // myPlugin/modules/mymodule/lib/myPluginmymoduleActions.class.phpのなか
    class myPluginmymoduleActions extends sfActions
    {
      public function executeIndex()
      {
        // ここに何らかのコード
      }
    }

    // myPlugin/modules/mymodule/actions/actions.class.phpのなか

    require_once dirname(__FILE__).'/../lib/myPluginmymoduleActions.class.php';

    class mymoduleActions extends myPluginmymoduleActions
    {
      // なし
    }

    // frontend/modules/mymodule/actions/actions.class.phpのなか
    class mymoduleActions extends myPluginmymoduleActions
    {
      public function executeIndex()
      {
        // ここでプラグインのコードをオーバーライドする
      }
    }

>**SIDEBAR**
>プラグインスキーマのカスタマイズ
>
>###Doctrine
>モデルをビルドするとき、Doctrineによりアプリケーションとプラグインの `config/` ディレクトリにあるすべての `*schema.yml` が検索されるので、プロジェクトのスキーマでプラグインのスキーマを上書きできます。このマージ処理で、テーブルやカラムを追加したり変更したりできます。たとえば、以下の例ではプラグインスキーマで定義されたテーブルにカラムを追加する方法を示しています。
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
>モデルをビルドするとき、symfony  は、つぎのルールにしたがって、プラグインのものを含めて、それぞれの既存のスキーマのためにカスタムYAMLファイルを探します:
>
>オリジナルのスキーマ名                | カスタムスキーマ名
>-------------------------------------- | ------------------------------
>config/schema.yml                      | schema.custom.yml
>config/foobar_schema.yml               | foobar_schema.custom.yml
>plugins/myPlugin/config/schema.yml     | myPlugin-schema.custom.yml
>plugins/myPlugin/config/foo_schema.yml | myPlugin_foo-schema.custom.yml
>
>カスタムスキーマはアプリケーションとプラグインの `config/` ディレクトリを探すので、プラグインは別のプラグインのスキーマを上書きをして、スキーマ単位で複数のカスタマイズが可能です。
>
>symfony はそれぞれのテーブルの `phpName` をもとに2つのスキーマをマージします。マージ処理によってテーブル、カラム、カラムの属性の追加もしくは修正できます。たとえば、つぎの一覧はカスタムスキーマがカラムをプラグインのスキーマで定義されたテーブルに追加する方法を示しています。
>
>     # オリジナルのスキーマ (plugins/myPlugin/config/schema.yml)
>     propel:
>       article:
>         _attributes:    { phpName: Article }
>         title:          varchar(50)
>         user_id:        { type: integer }
>         created_at:
>
>     # カスタムスキーマ (myPlugin_schema.custom.yml)
>     propel:
>       article:
>         _attributes:    { phpName: Article, package: foo.bar.lib.model }
>         stripped_title: varchar(50)
>
>     # スキーマの結果、内部でマージされモデルと SQL 生成用に内部で使われる
>     propel:
>       article:
>         _attributes:    { phpName: Article, package: foo.bar.lib.model }
>         title:          varchar(50)
>         user_id:        { type: integer }
>         created_at:
>         stripped_title: varchar(50)
>
>マージ処理はテーブルの `phpName` をキーとして使うので、スキーマのなかで同じ `phpName` を保つのであれば、データベースのプラグインテーブルの名前を変更できます。

### プラグインの書き方

`plugin:install` タスクでは PEAR パッケージ形式のプラグインのみがインストールされます。このようなプラグインは公式サイトのプラグインセクション、PEAR チャンネル経由もしくはダウンロードできる通常のファイルとして配布されていることを覚えておいてください。プラグインを編集したい場合は、単純なアーカイブよりも PEAR パッケージとして公開したほうがベターでしょう。加えて、プラグインを PEAR パッケージにすればアップグレード作業が簡単になり、依存関係の宣言が可能で、自動的にアセットを `web/` ディレクトリにデプロイできます。

#### ファイルのコンフィギュレーション

新しい機能を開発し、プラグインとしてパッケージにすることを考えてみましょう。最初の段階はファイルを論理的に編成して、symfony のロードメカニズムが必要なときにこれらのファイルを見つけることができるようにしましょう。この目的のために、リスト17-18で示されているディレクトリ構造に従う必要があります。リスト17-22は `sfSamplePlugin` プラグインのためのファイル構造の例を示しています。

リスト17-22 - プラグインとしてパッケージにするファイルの一覧の例

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

編集に関して、プラグインのディレクトリの位置(リスト17-22の`sfSamplePlugin/`)は重要ではありません。これはディスク上の任意の場所に設置できます。

>**TIP**
>既存のプラグインを練習問題として考え、初めてプラグインを作るさいには、これらの名前の規約とファイルの構造を再現してみてください。

#### package.xml ファイルを作る

プラグイン編集のつぎの段階はプラグインディレクトリのルートで `package.xml` ファイルを追加することです。`package.xml` は PEAR の構文に従います。リスト17-23の典型的な symfony プラグインの `package.xml` をご覧ください。

リスト17-23 - symfony プラグイン用の `package.xml`

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

ここで注目すべき部分は `<contents>` タグと `<dependencies>` タグで、つぎに説明します。残りのタグに関しては、symfony 固有のものではありませんので、`package.xml` フォーマットに関する詳細な内容は [PEAR オンラインマニュアル](http://pear.php.net/manual/) を参照してください。

#### 内容

`<contents>` タグはプラグインのファイル構造を記述しなければならない場所です。このタグはコピーするファイルとその場所を PEAR に伝えます。`<dir>` タグと `<file>` タグでファイル構造を記述してください。すべての `<file>` タグは `role="data"` 属性を持たなければなりません。リスト17-23の `<contents>` タグの部分はリスト17-22の正しいディレクトリ構造を記載しています。

>**NOTE**
>`<dir>` タグの使用は義務ではありません。`<file>` タグのなかで相対パスは `name` の値として使えるからです。`package.xml` ファイルを読みやすくするためにおすすめです。

#### プラグインの依存関係

任意のバージョンの PHP、PEAR、symfony、PEAR パッケージ、もしくはほかのプラグインの一式で動くようにプラグインは設計されています。`<dependencies>` タグでこれらの依存関係を宣言すれば必要なパッケージがすでにインストールされていることを確認してそうでなければ例外を起動するよう PEAR に伝えることになります。

最小要件として、少なくともつねに開発環境に対応した PHP、PEAR と symfony の依存関係を宣言します。何を追加すればよいのかわからなければ、PHP 5.2.4、PEAR 1.4と symfony  1.3 の要件を追加してください。

それぞれのプラグインに対して symfony の最大のバージョン番号を追加することも推奨されます。これによって上位バージョンの symfony でプラグインを使うときにエラーメッセージが表示され、プラグインを再リリースするまえにこのバージョンでプラグインが正しく動作するのかを確認することをプラグインの作者に義務づけます。無言でプラグインの動作が失敗するよりも警告を発してダウンロードとアップグレードするほうがベターです。

プラグインを依存関係のあるものとして指定すれば、ユーザーはプラグインとすべての依存関係を1つのコマンドでインストールできるようになります:

    $ php symfony plugin:install --install-deps sfSamplePlugin

#### プラグインをビルドする

PEAR コンポーネントはパッケージの `.tgz` アーカイブを作るコマンド (`pear package`) を持ちます。リスト17-24では、`package.xml` を含むディレクトリでこのコマンドを呼び出しています。

リスト17-24 - プラグインを PEAR パッケージにする

    $ cd sfSamplePlugin
    $ pear package

    Package sfSamplePlugin-1.0.0.tgz done

いったんプラグインのパッケージがビルドされたら、リスト17-25で示されるように、あなたの環境にこれをインストールして動作を確認してください。

リスト17-25 - プラグインをインストールする

    $ cp sfSamplePlugin-1.0.0.tgz /home/production/myproject/
    $ cd /home/production/myproject/
    $ php symfony plugin:install sfSamplePlugin-1.0.0.tgz

`<contents>` タグにある説明にしたがって、パッケージにされたファイルは最終的にプロジェクトの異なるディレクトリに設置されます。リスト17-26はインストールのあとで `sfSamplePlugin` のファイルがインストールされる場所を示しています。

リスト17-26 - プラグインファイルは `plugin/` と `web/` ディレクトリにインストールされる

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
      sfSamplePlugin/               ## システムによっては、コピーもしくはシンボリックリンク
        css/
          sfSampleStyle.css
        images/
          sfSampleImage.png

このプラグインのふるまいをアプリケーションでテストしてください。きちんと動くのであれば、プラグインを複数のプロジェクトにまたがって配布するもしくは symfony コミュニティに寄付する準備ができています。

#### 公式サイトでプラグインを配布する

symfony のプラグインはつぎの手順にしたがって `symfony-project.org` の Web サイトで配布されるときにもっとも幅広い利用者を得ます。独自プラグインをつぎのような方法で配布できます:

  1. `README` ファイルにプラグインのインストール方法と使いかたが、`LICENSE` ファイルにはライセンスの詳細が記述されていることを確認する。`README` は [Markdown の構文](http://daringfireball.net/projects/markdown/syntax)で記述する。
  2. 公式サイトのアカウント (http://www.symfony-project.org/user/new) を作りプラグインのページ (http://www.symfony-project.org/plugins/new) を作る。
  3. `pear package` コマンドを呼び出してプラグイン用のPEARパッケージを作りテストする。PEAR パッケージの名前は `sfSamplePlugin-1.0.0.tgz` (1.0.0 はプラグインのバージョン) でなければならない。
  4. PEARパッケージをアップロードする (`sfSamplePlugin-1.0.0.tgz`)。
  5. アップロードしたプラグインは[一覧ページ](http://www.symfony-project.org/plugins/)に表示される。

この手続きを行えば、ユーザーはプロジェクトのディレクトリでつぎのコマンドを入力するだけでプラグインをインストールできるようになります:

    $ php symfony plugin:install sfSamplePlugin

#### 命名規約

`plugin/` ディレクトリをきれいに保つために、すべてのプラグインの名前がラクダ記法であり `Plugin` のサフィックスで終わることを確認してください (たとえば、`shoppingCartPlugin`、`feedPlugin`)。プラグインに名前をつけるまえに、同じ名前のプラグインが存在しないことを確認してください。

>**NOTE**
>Propel に依存するプラグインの名前は `Propel` を含みます(Doctrine の場合も同様です)。たとえば、Propel のデータアクセスオブジェクトを利用する認証プラグインは `sfPropelAuth` という名前になります。

プラグインには使用条件と選んだライセンスを説明する `LICENSE` ファイルをつねに含めなければなりません。バージョンの履歴、プラグインの目的、効果、インストールと設定の手引きなどを含めることも推奨されます。

まとめ
----

symfony のクラスはアプリケーションレベルで修正できる機能を提供するイベントを通知します。イベントのメカニズムは PHP の制約が禁止している実行時のクラスの多重継承とオーバーライドを可能にします。ですのでそのためにコアクラスを修正しなければならないとしても、またファクトリ (factory) の設定がそこに存在するとしても symfony の機能を簡単に拡張できます。

すでに多くの拡張機能 (エクステンション) が存在し、プラグインとしてパッケージが作成されています。symfony のコマンドラインによってインストール、アップグレード、アンインストールするのが簡単です。プラグインをPEARパッケージを作成するのと同じぐらい簡単で、複数のアプリケーションをまたがって再利用できます。

symfony 公式サイトのプラグインセクションには多くのプラグインが含まれ、あなた自身のプラグインも追加できます。これであなたは方法を理解したので、私たち symfony の開発者はあなたが多くの便利な拡張機能で symfony コアを強化してくださることを望んでおります！
