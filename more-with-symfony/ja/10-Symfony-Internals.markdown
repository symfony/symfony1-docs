symfony の内側
=============

*Geoffrey Bachelet 著*

みなさん、HTTP リクエストにより symfony アプリケーションが実行されるとき、どのように処理されているのか疑問に思ったことはありますか? この章は、そんなみなさんの疑問にお答えします。この章では、symfony が HTTP リクエストをどのように処理し、レスポンスを準備して返しているのかを詳しく説明します。もちろん、このようなプロセスの説明は少し退屈かもしれませんので、いくつかの面白い使い方や、このプロセスをカスタマイズする方法なども説明します。

ブートストラップ
---------------

最初はアプリケーションのコントローラーから始まります。symfony のサンプルプロジェクトとしてもはや古典的ですが、`dev` 環境で `frontend` コントローラーを作成したとします。この場合、フロントコントローラーの場所は[`web/frontend_dev.php`](http://trac.symfony-project.org/browser/branches/1.3/lib/task/generator/skeleton/app/web/index.php) になります。このファイルは何をしているのでしょうか? とても短いコードですが、symfony はアプリケーションコンフィギュレーションを取得し、`sfContext` のインスタンスを作っています。この `sfContext` オブジェクトが、リクエストのディスパッチを行います。`sfContext` オブジェクトは、symfony の舞台裏でアプリケーションに応じた処理の中心的な役割を担うため、このオブジェクトの作成にはアプリケーションコンフィギュレーションが必要となります。

>**TIP**
>このプロセスで変更できることはわずかです。~`ProjectConfiguration::getApplicationConfiguration()`~ の 4 番目の引数を使うと、アプリケーションのルートディレクトリを変更できます。同様に、[`sfContext::createInstance()`](http://www.symfony-project.org/api/1_3/sfContext#method_createinstance) の 3 番目の引数にカスタムコンテキストクラスを渡すことで、ルートディレクトリを変更することもできます。(カスタムコンテキストクラスは、`sfContext` を継承している必要があります。)

アプリケーションコンフィギュレーションの取得は、とても重要なステップです。まず、`sfProjectConfiguration` でアプリケーションに対応するコンフィギュレーションクラスの推測が行われます。通常は `${application}Configuration` で、`apps/${application}/config/${application}Configuration.class.php` にあります。

`sfApplicationConfiguration` は `ProjectConfiguration` を継承しているので、すべてのアプリケーションから `ProjectConfiguration` の任意のメソッドを利用できます。また、`sfApplicationConfiguration` のコンストラクタには `ProjectConfiguration` と `sfProjectConfiguration` のコンストラクタも含まれることも意味します。ほとんどのプロジェクトの設定は、`sfProjectConfiguration` コンストラクタ内で行われます。最初に、プロジェクトのルートディレクトリや symfony のライブラリディレクトリといったいくつかの役立つ値が求められ、変数に保存されます。フロントコントローラーの `ProjectConfiguration::getApplicationConfiguration()` の 5 番目の引数でイベントディスパッチャーが渡されていない場合は、`sfProjectConfiguration` で `sfEventDispatcher` 型の新しいイベントディスパッチャーが作成されます。

これ以降の設定プロセスは、派生クラスで `ProjectConfiguration` の `setup()` メソッドをオーバーライドすることでカスタマイズできます。通常ここでは、[`sfProjectConfiguration::setPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setplugins)、[`sfProjectConfiguration::enablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableplugins)、[`sfProjectConfiguration::disablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_disableplugins)、[`sfProjectConfiguration::enableAllPluginsExcept()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableallpluginsexcept) を使ってプラグインの有効化/無効化を行います。

次に [`sfProjectConfiguration::loadPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_loadplugins) によりプラグインが読み込まれます。[`sfProjectConfiguration::setupPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setupplugins) メソッドをオーバーライドすることで、プラグインの読み込みプロセスをカスタマイズできます。

プラグインの初期化は、先頭から順に行われます。各プラグインに対して `${plugin}Configuration` クラス (たとえば `sfGuardPluginConfiguration`) を探し、見つかった場合はインスタンス化します。見つからない場合は `sfPluginConfigurationGeneric` を使います。プラグインの設定は、次の 2 つのメソッドを使ってカスタマイズできます:

 * `${plugin}Configuration::configure()`: オートロードの完了前
 * `${plugin}Configuration::initialize()`: オートロードの完了後

次に、 `sfApplicationConfiguration` は自身の `configure()` メソッドを呼び出します。派生クラスでこのメソッドをオーバーライドすることで、[`sfApplicationConfiguration::initConfiguration()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_initconfiguration) から始まる一連の内部設定の初期化プロセスの前に、アプリケーションごとの設定をカスタマイズできます。

この symfony の設定プロセスには多くの処理があり、このプロセスをカスタマイズするためのエントリポイントもいくつかあります。たとえば、`autoload.filter_config` イベントを使うことで、オートローダーの設定をカスタマイズできます。また、`settings.yml` や `app.yml` といったとても重要な設定ファイルが読み込まれるので、これらのファイルで設定を変更することもできます。さらに、各プラグインの `config/config.php` ファイル、またはコンフィギュレーションクラスの `initialize()` メソッドを使って、プラグインを設定することもできます。

`sf_check_lock` が有効な場合、ここでロックファイルの確認が行われます。(ロックファイルは、たとえば `project:disable` タスクにより作られます。) ロックファイルがある場合は、次のファイルを順に探し、最初に見つかったファイルをインクルードしてスクリプトの実行をただちに終了します:

 1. `apps/${application}/config/unavailable.php`
 1. `config/unavailable.php`
 1. `web/errors/unavailable.php`
 1. `lib/vendor/symfony/lib/exception/data/unavailable.php`

最後に、派生クラスで ~`sfApplicationConfiguration::initialize()`~ メソッドをオーバーライドすることでアプリケーションの初期化をカスタマイズできます。

### ブートストラップと設定のまとめ

 * アプリケーションの設定の取得
  * `ProjectConfiguration::setup()` (プラグインを定義)
  * プラグインの読み込み
   * `${plugin}Configuration::configure()`
   * `${plugin}Configuration::initialize()`
  * `ProjectConfiguration::setupPlugins()` (プラグインのセットアップ)
  * `${application}Configuration::configure()`
  * `autoload.filter_config` イベントの通知
  * `settings.yml` と `app.yml`の読み込み
  * `${application}Configuration::initialize()`
 * `sfContext` インスタンスの作成

`sfContext` とファクトリー
-------------------------

ディスパッチプロセスの内部へ踏み込む前に、symfony のワークフローにおいて重要なファクトリーについて説明します。

symfony でファクトリーとは、アプリケーションで使用する一連のコンポーネントまたはクラスです。ファクトリーの例としては、`logger`、`i18n` などがあります。各ファクトリーは `factories.yml` で設定し、コンフィグハンドラーによってコンパイルされ、PHP コードに変換されます。この PHP コードから、ファクトリーオブジェクトがインスタンス化されます。 (コンパイルされた PHP コードは、キャッシュディレクトリの `cache/frontend/dev/config/config_factories.yml.php` ファイルで確認できます。)

>**NOTE**
>ファクトリーの読み込みは、`sfContext` の初期化中に行われます。詳細は、[`sfContext::initialize()`](http://www.symfony-project.org/api/1_3/sfContext#method_initialize) や [`sfContext::loadFactories()`](http://www.symfony-project.org/api/1_3/sfContext#method_loadfactories) を参照してください。

この `factories.yml` の設定を編集することで、symfony の動作の大部分をカスタマイズできます。symfony 組み込みのファクトリークラスを独自のクラスに置き換えることも可能です!

>**NOTE**
>ファクトリーについてさらに詳しく知りたい方は、[symfony リファレンスブック](http://www.symfony-project.org/reference/1_3/en/05-Factories) や [`factories.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/factories.yml) ファイルを参照してください。

生成された `config_factories.yml.php` を見ると、ファクトリーが特定の順番でインスタンス化されていることがわかります。いくつかのファクトリーは他のファクトリーに依存しているので、インスタンス化する順番が重要になります。(たとえば、`routing` コンポーネントは必要な情報を取得するために `request` が必要です。)

`request` についてより細かく見ていきましょう。デフォルトでは、`request` には `sfWebRequest` クラスが使われます。`request` がインスタンス化されると、[`sfWebRequest::initialize()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_initialize) が呼び出されます。このメソッドで、HTTP メソッドや GET/POST パラメーターなどの関連する情報が集められます。`request.filter_parameters` イベントを使うことで、独自のリクエストパラメーター処理を追加できます。

### `request.filter_parameter` イベントを使う

たとえば、ユーザーにパブリック API を公開する Web サイトを開発しているとしましょう。API は HTTP 経由で利用可能で、API を利用する各ユーザーには、リクエストヘッダー (たとえば `X_API_KEY`) で正しい API キーを指定させ、このヘッダーをアプリケーションで検証します。検証処理は、`request.filter_parameter` イベントを使うと次のように簡単に実装できます:

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

こうすると、リクエストから API ユーザーにアクセスできます:

    [php]
    public function executeFoobar(sfWebRequest $request)
    {
      $api_user = $request->getParameter('api_user');
    }

このテクニックは、たとえば Web サービスの呼び出しの検証などにも使えます。

>**NOTE**
>`request.filter_parameters` イベントには、リクエストに関する多くの情報が渡されます。詳細については、[`sfWebRequest::getRequestContext()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_getrequestcontext) メソッドを参照してください。

次に重要なファクトリーはルーティングです。ルーティングの初期化は先頭から順に行われ、ほとんどが特定のオプションの収集と設定で構成されています。`routing.load_configuration` イベントを使うと、このプロセスをカスタマイズできます。

>**NOTE**
>`routing.load_configuration` イベントを使うと、ロードされたルーティングオブジェクトのインスタンスにアクセスできます。ルーティングオブジェクトのクラスのデフォルトは [`sfPatternRouting`](http://trac.symfony-project.org/browser/branches/1.3/lib/routing/sfPatternRouting.class.php) です。このオブジェクトのさまざまなメソッドを使って、登録されているルートを操作できます。

### `routing.load_configuration` イベントの使用例

たとえば、ルートの追加は次のように簡単です:

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

この初期化の直後に、[`sfPatternRouting::parse()`](http://www.symfony-project.org/api/1_3/sfPatternRouting#method_parse) メソッドで URL のパースが行われます。`parse` メソッドでの処理は少ないのですが、 このメソッドの最後まで処理が到達した場合は対応するルートが見つかったことを示し、ルートオブジェクトのインスタンスが取り出されて対応するパラメーターが結合されます。

>**NOTE**
>ルーティングに関する詳細は、`進化したルーティング`の章を参照してください。

すべてのファクトリーが読み込まれセットアップが正常に終了すると、`context.load_factories` イベントが通知されます。このイベントは symfony フレームワークのすべてのコアファクトリーオブジェクト (リクエスト、レスポンス、ユーザー、ロギング、データベースなど）に もっとも早くアクセスできるので、重要です。

この後で、とても便利な `template.filter_parameters` イベントに接続しています。このイベントは、[`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php) でファイルがレンダリングされるときに通知され、実際にテンプレートに渡されるパラメータを制御できます。`sfContext` では、このイベントを利用して `$sf_context`、`$sf_request`、`$sf_params`、`$sf_response` および `$sf_user` といった便利なパラメーターを各テンプレートに追加しています。

同じように `template.filter_parameters` イベントを使って、カスタムグローバルパラメーターをすべてのテンプレートに追加することもできます。

### `template.filter_parameters` イベントを使う

プロジェクト内の各テンプレートで、特定のオブジェクト、たとえばヘルパーオブジェクトにアクセスする必要があるとします。次のコードを `ProjectConfiguration` に追加してください:

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

こうすると、各テンプレートで `$my_helper_object` から `MyHelperObject` のインスタンスにアクセスできます。

### `sfContext` のまとめ

1. `sfContext` の初期化
1. ファクトリーの読み込み
1. 次のイベントの通知:
 1. [request.filter_parameters](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_request_filter_parameters)
 1. [routing.load_configuration](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_routing_load_configuration)
 1. [context.load_factories](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_context_load_factories)
1. グローバルテンプレートパラメーターの追加

コンフィグハンドラーについて
---------------------------

コンフィグハンドラーは、symfony のコンフィギュレーションシステムの心臓部です。コンフィグハンドラーは、各コンフィギュレーションファイルの種類に応じて実行されます。各コンフィグハンドラークラスは、YAML コンフィギュレーションファイルを読み込んで、必要なときに実行できるよう PHP のコードブロックに変換します。コンフィギュレーションファイルごとに、[`config_handlers.yml` ファイル](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/config_handlers.yml)にある特定のコンフィグハンドラーが割り当てられます。

正確には、コンフィグハンドラーが YAML ファイルをパースするわけでは*ありません* (`sfYaml` で行われます)。コンフィグハンドラーの仕事は、YAML の情報に基づいて一連の PHP ディレクトリを作成し、作成したディレクトリに PHP ファイルを保存することです。保存された PHP ファイルは後でインクルードされます。*コンパイルされた*各 YAML コンフィギュレーションファイルは、キャッシュディレクトリに保存されます。

もっともよく使われるコンフィグハンドラーは、[`sfDefineEnvironmentConfigHandler`]http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfDefineEnvironmentConfigHandler.class.php) ではないでしょうか。このコンフィグハンドラーにより、環境ごとのコンフィギュレーションの設定が可能になっています。つまり、このコンフィグハンドラーでは現在の環境のコンフィギュレーションのみを取得します。

別のコンフィグハンドラーも見てみましょう。[`sfFactoryConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfFactoryConfigHandler.class.php) というコンフィグハンドラーがあります。このコンフィグハンドラーでは、symfony の重要なファイルの 1 つ `factories.yml` がコンパイルされます。このコンフィグハンドラーの処理は特徴的で、YAML コンフィギュレーションファイルを、さまざまなファクトリー (先に説明したように、すべて重要なコンポーネントです) をインスタンス化する PHP コードに変換しています。他のコンフィグハンドラーとはだいぶ異なっています。

リクエストのディスパッチと実行
-----------------------------

前の節ではファクトリーについて詳しく説明しました。話をディスパッチプロセスに戻しましょう。`sfContext` の初期化が完了すると、最後にコントローラーの `dispatch()` メソッド [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) が呼び出されます。

symfony のディスパッチプロセス自体はとても単純です。実際、`sfFrontWebController::dispatch()` では単純にモジュール名とアクション名をリクエストパラメーターから取得し、アプリケーションを [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) メソッドでフォワードしているだけです。

>**NOTE**
>この時点で、ルーティングにより現在の URL からモジュール名またはアクションを判別できなかった場合、[`sfError404Exception`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfError404Exception.class.php) が発生します。この例外により、リクエストは 404 エラーのハンドリングモジュールへフォワードされます。([`sf_error_404_module`と`sf_error_404_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_error_404) を参照してください。) アプリケーションでこの例外を発生させると、同じように 404 エラーへフォワードすることができます。

`forward` メソッドでは、多くの実行前のチェック、および実行するアクションのコンフィギュレーションとデータの準備が行われます。

コントローラーは最初に、現在のモジュールの [`generator.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/generator.yml) ファイルがあるかどうかをチェックします。`generator.yml` コンフィグファイルがある場合、モジュールの基本的なアクションクラスをコンフィグファイルから生成する必要があるため、基本的なモジュール名、アクション名のクリーンアップの後最初にコンフィグファイルのチェックが実行されます。(アクションの生成は[コンフィグハンドラー `sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php) で行われます。) この後で、モジュールとアクションの存在チェックが行われます。このチェックはコントローラーに委譲され、[`sfController::actionExists()`](http://www.symfony-project.org/api/1_3/sfController#method_actionexists) を経由して [`sfController::controllerExists()`](http://www.symfony-project.org/api/1_3/sfController#method_controllerexists) メソッドが呼び出されます。ここで `actionExists()` メソッドによるチェックが失敗した場合も、`sfError404Exception` 例外を発生させます。

>**NOTE**
>[`sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php) は特殊なコンフィグハンドラーで、モジュールに対応する適切なジェネレータークラスをインスタンス化して実行します。コンフィグハンドラーに関する詳細は、「コンフィグハンドラーについて」の節を参照してください。また、`generator.yml` に関する詳細は [symfony リファレンスブックの第 6 章](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator)を参照してください。

派生クラスでアプリケーションのコンフィギュレーションクラスの [`sfApplicationConfiguration::getControllerDirs()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_getcontrollerdirs) メソッドをオーバーライドすると、この部分の動作をカスタマイズできます。このメソッドでは、コントローラーのファイルがあるディレクトリの配列と、各ディレクトリのコントローラーが有効かどうかを `settings.yml` の `sf_enabled_modules` 設定オプションでチェックすることを示すパラメーターが返されます。次のコードは、`getControllerDirs()` をカスタマイズした例です:

    [php]
    /**
     * /tmp/myControllers にあるコントローラーは、有効化するための検査は不要
     */
    public function getControllerDirs($moduleName)
    {
      return array_merge(parent::getControllerDirs($moduleName), array(
        '/tmp/myControllers/'.$moduleName => false
      ));
    }

>**NOTE**
>アクションが存在しない場合は `sfError404Exception` がスローされます。

次のステップで、アクションを含むコントローラーのインスタンスを取得します。この処理は、[`sfController::getAction()`](http://www.symfony-project.org/api/1_3/sfController#method_getaction) メソッドで行われ、`actionExists()` と同じように内部で [`sfController::getController()`](http://www.symfony-project.org/api/1_3/sfController#method_getcontroller) メソッドが呼び出されます。最後に、コントローラーのインスタンスが`アクションスタック`に追加されます。

>**NOTE**
>アクションスタックは FIFO (先入れ先出し) スタイルのスタックで、リクエスト処理で実行するすべてのアクションが格納されています。スタック内の各要素は `sfActionStackEntry` オブジェクトでラップされています。`sfContext::getInstance()->getActionStack()` または `$this->getController()->getActionStack()` を使うと、アクション内からアクションスタックにいつでもアクセスできます。

この後の多少のコンフィギュレーションの読み込みを行うと、アクションを実行する準備が完了します。次の 2 つの場所にあるモジュールごとのコンフィギュレーションを読み込む必要があります。1 つ目は `module.yml` ファイルで、通常は `apps/frontend/modules/yourModule/config/module.yml` にあります。これは YAML コンフィグファイルなので、コンフィグキャッシュを使います。このコンフィギュレーションファイルではモジュールを*内部用*として宣言でき、`mod_yourModule_is_internal` のように設定すると、このモジュールを外部から呼び出せなくなるためリクエストはエラーになります。

>**NOTE**
>内部用モジュールは、たとえば `getPresentationFor()` などでメール本文の生成に使われていました。現在は、パーシャルレンダリング (`$this->renderPartial()`) など別のテクニックを使うことが推奨されています。

`module.yml` を読み込んだ後、現在のモジュールが有効かどうかのチェックが再度行われます。`mod_$moduleName_enabled` を `false` に設定すると、モジュールを無効化できます。

>**NOTE**
>説明したように、モジュールを有効化または無効化する方法は 2 通りあります。この 2 つの違いは、モジュールが無効化された場合の処理にあります。1 つ目のケースでは `sf_enabled_modules` 設定がチェックされ、モジュールが無効化されていれば [`sfConfigurationException`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfConfigurationException.class.php) がスローされます。これは、モジュールを恒久的に無効化する場合に使います。2 つ目のケースでは、`mod_$moduleName_enabled` 設定で無効化されたモジュールにアクセスすると、アプリケーションは無効モジュールへフォワードされます。([`sf_module_disabled_module` と `sf_module_disabled_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_module_disabled) 設定を参照してください。) これは、モジュールを一時的に無効化する場合に使います。

読み込むコンフィギュレーションの 2 つ目は `config.php` ファイル (`apps/frontend/modules/yourModule/config/config.php`) で、 このファイルには [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) メソッドのコンテキストで実行する任意の PHP コードを記述できます。(コードは文字通り `sfController` クラスの内部で実行されるため、`$this` 変数を使って `sfController` インスタンスにアクセスします。)

### ディスパッチプロセスのまとめ

1. [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) の呼び出し
1. [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) の呼び出し
1. `generator.yml` のチェック
1. モジュール/アクションの存在チェック
1. コントローラーディレクトリの一覧の取得
1. アクションのインスタンスの取得
1. `module.yml` や `config.php` からモジュール設定の読み込み

フィルターチェーン
-----------------

ようやくすべてのコンフィギュレーションが完了したので、実際の動作を開始します。実際の動作とは、ここではフィルターチェーンを実行することです。

>**NOTE**
>symfony のフィルターチェーンは、[chain of responsibility](http://en.wikipedia.org/wiki/Chain-of-responsibility_pattern) と呼ばれるデザインパターンで実装されています。これはアクションを連鎖して実行できるシンプルで強力なパターンです。チェーンの実行を継続するか中断するかをチェーンの各パートごとに決定できます。チェーンの各パートの処理は、チェーンの残りの部分の実行の前または後のどちらでも実行できます。

フィルターチェーンのコンフィギュレーションは現在のモジュールの [`filters.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/filters.yml) から取得するので、アクションのインスタンスが必要になります。チェーンで実行する一連のフィルターを変更することもできます。ただし、レンダリングフィルターは常にリストの先頭に配置する必要があることに注意してください。デフォルトのフィルターコンフィギュレーションは次のとおりです:

* [`rendering`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfRenderingFilter.class.php)
* [`security`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfSecurityFilter.class.php)
* [`cache`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfCacheFilter.class.php)
* [`execution`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfExecutionFilter.class.php)

>**NOTE**
>カスタムフィルターを作った場合は、`security` フィルターと `cache` フィルターの間に配置することが強く推奨されています。

### セキュリティフィルター

`rendering` フィルターは、自身の処理を行う前に他のフィルターの実行完了を待つので、実際に最初に実行されるのは `security` フィルターです。このフィルターにより、[`security.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/security.yml) コンフィギュレーションファイルの設定が反映されます。具体的には、認証されていないユーザーを `login` モジュール/アクションへフォワードし、資格が不十分なユーザーを `secure` モジュール/アクションへフォワードします。このフィルターは、指定されたアクションでセキュリティが有効になっている場合にのみ実行されます。

### キャッシュフィルター

次は `cache` フィルターです。このフィルターは、フィルターが以降のフィルターの実行をスキップできることを利用しています。実際、該当するキャッシュが見つかった場合は、アクションを実行する必要はありません。もちろんこのフィルターは、ページ全体をキャッシュ可能な場合にのみ動作します。大多数のページはこれには該当しないでしょう。

キャッシュフィルターには、実行フィルターの実行後、レンダリングフィルターの実行の直前にわずかなロジックがあります。このコードにより、HTTP のキャッシュヘッダーが設定され、必要に応じて [`sfViewCacheManager::setPageCache()`](http://www.symfony-project.org/api/1_3/sfViewCacheManager#method_setpagecache) メソッドでページをキャッシュに保存します。

### 実行フィルター

最後は `execution` フィルターで、ここでようやくビジネスロジックの実行と、関連するビューの処理が行われます。

このフィルターの処理は、現在のアクションのキャッシュのチェックから始まります。アクションがキャッシュされている場合は、アクションの実行はスキップされ、`Success` ビューが実行されます。

アクションがキャッシュされていない場合は、コントローラーの `preExecute()` ロジックが実行され、その後アクション自身が実行されます。この処理では、アクションインスタンスの [`sfActions::execute()`](http://www.symfony-project.org/api/1_3/sfActions#method_execute) メソッドが呼び出されます。このメソッドには多くの処理はありません。アクションが呼び出し可能かどうかチェックし、呼び出し可能なら呼び出します。アクションの処理が完了すると、フィルターは実行したアクションの `postExecute()` ロジックを実行します。

>**NOTE**
>アクションの戻り値によって実行するビューを決定するため、戻り値はとても重要です。デフォルトでは、戻り値が見つからない場合 `sfView::SUCCESS` であると仮定されます (これは想像したとおり `Success`、つまり `indexSuccess.php` に変換されます)。

次のステップはビューの処理です。フィルターにより、アクションから返される2つの特別な戻り値 `sfView::HEADER_ONLY` と `sfView::NONE` がチェックされます。これらは名前から分かるとおり、HTTP ヘッダーのみを送信 (内部では [`sfWebResponse::setHeaderOnly()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_setheaderonly) で処理)、またはレンダリングをスキップします。

>**NOTE**
>組み込みのビューの名前は `ALERT`、`ERROR`、`INPUT`、`NONE`、`SUCCESS` です。ただし基本的には任意の名前を返すことができます。

何かをレンダリングする必要がある場合は、フィルターの最後のステップであるビューの実行へ進みます。

最初に [`sfController::getView()`](http://www.symfony-project.org/api/1_3/sfController#method_getview) メソッドを使って [`sfView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfView.class.php) オブジェクトを取得します。ビューオブジェクトは次の 2 つの場所で定義されています。1 つ目は特定のアクション用のカスタムビューで、`apps/frontend/modules/module/view/actionSuccessView.class.php` ファイルにある `actionSuccessView` または `module_actionSuccessView` です。(ここでは現在のモジュール/アクションを単純にモジュール/アクションと記述します。) カスタムビューファイルが存在しない場合は、`mod_module_view_class` コンフィギュレーションエントリーで定義されたクラスが使われます。このエントリーのデフォルト値は [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php) です。

>**TIP**
>カスタムビュークラスを使うことで、ビューの [`sfView::execute()`](http://www.symfony-project.org/api/1_3/sfView#method_execute) メソッドで独自のロジックを実行できます。たとえば、独自のテンプレートエンジンをインスタンス化できます。

ビューのレンダリングには次の 3 つのモードがあります:

1. `sfView::RENDER_NONE`: `sfView::NONE` と同じで、実際のレンダリングをキャンセルします。
1. `sfView::RENDER_VAR`: アクションの結果を生成し、[`sfActionStackEntry::getPresentation()`](http://www.symfony-project.org/api/1_3/sfActionStackEntry#method_getpresentation) メソッドを使って他のアクションスタックエントリからアクセスできるようにします。
1. `sfView::RENDER_CLIENT`: デフォルトのモードで、ビューをレンダリングしてレスポンスの内容を送信します。

>**NOTE**
>レンダリングモードは、現在のモジュール/アクションのレンダリング結果を返す [`sfController::getPresentationFor()`](http://www.symfony-project.org/api/1_3/sfController#method_getpresentationfor) メソッドでのみ使われます。

### レンダリングフィルター

いよいよ終わりが近づいてきました。これが最後のステップです。フィルターチェーンの実行はほとんど完了しましたが、レンダリングフィルターは何もしていなかったことを思い出してください。レンダリングフィルターは、チェーンの最初で他のフィルターの処理が完了するのを待っており、ここでようやく自身の処理を実行できます。名前から想像できますが、レンダリングフィルターから [`sfWebResponse::send()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_send) メソッドを使ってレスポンスの内容がブラウザーへ送信されます。

### フィルターチェーンの実行のまとめ

1. `filters.yml` ファイルのコンフィギュレーションに応じたフィルターチェーンのインスタンス化
1. `security` フィルターによる認証と資格のチェック
1. `cache` フィルターによる現在のページのキャッシュ処理
1. `execution` フィルターによるアクションの実行
1. `rendering` フィルターによる `sfWebResponse` からのレスポンスの送信

全体のまとめ
------------

1. アプリケーションのコンフィギュレーションの取得
1. `sfContext` インスタンスの作成
1. `sfContext` の初期化
1. ファクトリーの読み込み
1. 次のイベントの通知:
 1. ~`request.filter_parameters`~
 1. ~`routing.load_configuration`~
 1. ~`context.load_factories`~
1. グローバルなテンプレートパラメーターの追加
1. [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) の呼び出し
1. [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) の呼び出し
1. `generator.yml` のチェック
1. モジュール/アクションの存在チェック
1. コントローラーディレクトリーの一覧の取得
1. アクションのインスタンスの取得
1. `module.yml` または `config.php` からモジュールのコンフィギュレーションの取得
1. `filters.yml` ファイルのコンフィギュレーションに応じてフィルターチェインのインスタンス化
1. `security` による認証と資格のチェック
1. `cache` フィルターによる現在のページのキャッシュ処理
1. `execution` フィルターによるアクションの実行
1. `rendering` フィルターによる `sfWebResponse` からのレスポンスの送信

最後に
------

ようやく終わりました! リクエストの処理が完了し、次のリクエストの処理を待ちます。symfony の内部プロセスは、それだけで 1 冊の本を執筆できるほど複雑で、この章はその概要にすぎません。さらに詳しく調べるには、ご自分でソースを読んでみると良いでしょう。ソースを読むことは、現在も、またこの先においても、なんらかのフレームワークやライブラリーのメカニズムを学習する一番の方法だからです。
