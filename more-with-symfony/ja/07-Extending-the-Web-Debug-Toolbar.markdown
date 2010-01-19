Web デバッグツールバーの拡張
==============================

*Ryan Weaver 著*

symfony の Web ツールバーには、デバッグやパフォーマンス改善に役立つ様々なツールがはじめからそろっています。Web デバッグツールバーの各ツールは *Web デバッグパネル*と呼ばれ、キャッシュ、設定、ロギング、メモリ使用量、symfony のバージョン、処理時間に対応しています。さらに、symfony 1.3 では`ビュー`情報と`メール`デバッグの 2 つの *Web デバッグパネル*が導入されます。

![Web デバッグツールバー](http://www.symfony-project.org/images/more-with-symfony/web_debug_01.png "symfony 1.3 のデフォルトウィジェットの Web デバッグツールバー")


symfony 1.2 のときのように、開発者は簡単に *Web デバッグパネル*を作り追加することができます。この章では新しい *Web デバッグパネル*を用意して異なったツールの実行とカスタマイズを可能にしていきます。さらに、この章で使ったテクニックのいくつかを使って、[ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin) に便利で興味深いデバッグパネルを追加します。

新しい Web デバッグパネルをつくる
----------------------------------

Web デバッグツールバーの個々の部品は *Web デバッグパネルと*呼ばれ、~`sfWebDebugPanel`~ という特殊なクラスを継承します。新しいパネルの作成は実に簡単です。`sfWebDebugPanelDocumentation.class.php` という名前のファイルをプロジェクトディレクトリの `lib/debug/` に作成します （もしこのディレクトリがなければ作成する必要があります)。

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    class acWebDebugPanelDocumentation extends sfWebDebugPanel
    {
      public function getTitle()
      {
        return '<img src="/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
      }

      public function getPanelTitle()
      {
        return 'Documentation';
      }
      
      public function getPanelContent()
      {
        $content = 'Placeholder Panel Content';
        
        return $content;
      }
    }

最低でも、すべてのデバッグパネルには `getTitle()`、`getPanelTitle()`、`getPanelContent()` が実装されている必要があります。

 * ~`sfWebDebugPanel::getTitle()`~: パネルがツールバーの上にどのように表示されるかを決定します。他のパネルと同様に、今から作るカスタムパネルは小さいアイコンと短い名前を持ちます。

 * ~`sfWebDebugPanel::getPanelTitle()`~: パネル内部のトップに表示される `h1` タグに使われます。これは、ツールバーのアイコンを囲むリンクタグの `title` 属性にも使われるので  html のコードを含んでは*いけません*。

 * ~`sfWebDebugPanel::getPanelContent()`~: パネルのアイコンをクリックしたときに表示される 素の html コンテンツを生成します。

最後に、新しいパネルをツールバーへ含めたいということをアプリケーションに通知します。
このために、Web デバッグツールバーが可能性のあるパネルを集めているときに通知される `debug.web.load_panels` イベントのリスナーを追加します。まず、イベントを listen するように `config/ProjectConfiguration.class.php` ファイルを設定します:

    [php]
    // config/ProjectConfiguration.class.php
    public function initialize()
    {
      //...

      $this->dispatcher->connect('debug.web.load_panels', array(
        'acWebDebugPanelDocumentation',
        'listenToLoadDebugWebPanelEvent'
      ));
    }

次に、ツールバーにパネルを追加するために `listenToLoadDebugWebPanelEvent()` リスナー関数を `acWebDebugPanelDocumentation.class.php` に追加します


    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->setPanel(
        'documentation',
        new self($event->getSubject())
      );
    }

以上です！ブラウザをリロードすれば簡単に結果を見ることができます。

![Web デバッグツールバー](http://www.symfony-project.org/images/more-with-symfony/web_debug_02.png "新しいカスタムパネルを追加した Web デバッグツールバー")

>**TIP**
>symfony 1.3 では、url パラメータによりページが読み込まれたときに特定のウェブデバッグパネルを自動的に開く事が出来ます。例えば、`?sfWebDebugPanel=documentation` を url の末尾に追加することで自動的に今つくったドキュメンテーションパネルを開くことが出来ます。これはカスタムパネルを作る時にとても重宝します。


3 種類のウェブデバッグパネル
---------------------------

内部的に、異なった 3 種類のウェブデバッグパネルががあります。

### *アイコンのみ*のパネル

もっとも基本的なパネルはツールバーにアイコンとテキストを表示するだけのものです。典型的な例は、メモリーの利用量を表示しクリックしても何も起こらない、`メモリー`パネルです。*アイコンのみ*パネルを作るには、単純に `getPanelContent()` で空文字列を返すだけです。パネルの唯一の出力は `getTitle()` メソッドによるものだけです:

    [php]
    public function getTitle()
    {
      $totalMemory = sprintf('%.1f', (memory_get_peak_usage(true) / 1024));

      return '<img src="'.$this->webDebug->getOption('image_root_path').'/memory.png" alt="Memory" /> '.$totalMemory.' KB';
    }

    public function getPanelContent()
    {
      return;
    }

### *リンク*パネルタイプ

*アイコンのみ*パネルの様に、*リンク*パネルはパネルの中身を持ちません。*アイコンのみ*と違うのは、ツールバーの*リンク*パネルをクリックすると `getTitleUrl()` で示された url に遷移します。*リンク*パネルを作るには `getPanelContent()` で空文字列を返して `getTitleUrl()` メソッドをクラスに追加します。

    [php]
    public function getTitleUrl()
    {
      // 外部 uri へのリンク
      return 'http://www.symfony-project.org/api/1_3/';

      // もしくは、アプリケーションのルート
      return url_for('homepage');
    }

    public function getPanelContent()
    {
      return;
    }

### *コンテンツ*パネルタイプ    


おそらくもっとも一般的なパネルの種類は、*コンテンツ*パネルです。この種類のパネルは、デバッグツールバーのパネルをクリックしたときに完全な html のコンテンツを表示します。このタイプのパネルを作るには、単に `getPanelContent()` で空文字列でないものを返すだけです。

パネルの内容のカスタマイズ
--------------------------

ツールバーに独自の Web デバッグパネルを追加するには、単に `getPanelContent()` メソッドを実装するだけです。symfony には豊富で使いやすいコンテンツを作るための補助機能が提供されています。

### ~`sfWebDebugPanel::setStatus()`~

標準では、Web デバッグツールバーのそれぞれのパネルはグレーの背景が使われています。この背景はパネルの内部のコンテンツによって注意を引くためにオレンジや赤の背景に変更されるべきです。

![エラーのある Web デバッグツールバー](http://www.symfony-project.org/images/more-with-symfony/web_debug_05.png "logs にエラー状態を表示している Web デバッグツールバー")

パネルの背景の色を変えるには、単に `setStatus()` メソッドを実装するだけです。このメソッドでは、[sfLogger](http://www.symfony-project.org/api/1_3/sfLogger) クラスの `priority` 定数を利用できます。3 種類の異なったステータスレベルに 3 種類の異なったパネルの背景色 (グレー、オレンジ、赤) が割り当てられています。通常、`setStatus()` メソッドは、ある条件で特別に注意を引く必要があるときに、`getPanelContent()` メソッドの内部から呼び出されます。

    [php]
    public function getPanelContent()
    {
      // ...

      // 背景をグレーにする (デフォルト)
      $this->setStatus(sfLogger::INFO);

      // 背景をオレンジにする
      $this->setStatus(sfLogger::WARNING);

      // 背景を赤にする
      $this->setStatus(sfLogger::ERR);
    }


### ~`sfWebDebugPanel::getToggler()`~

Web パネルのもっとも基本的な機能の 1 つがトグラーです:
▲▼マークをクリックするとコンテンツの表示/非表示が切り替わります。

![Web デバッグトグラー](http://www.symfony-project.org/images/more-with-symfony/web_debug_03.png "トグラー動作中の Web デバッグツールバー")

この機能は `getToggler()` 関数をつかってカスタム Web デバッグパネルで簡単に使うことができます。例えば、パネルの中で内容のリストを切り替えたいときには次のようになります:

    [php]
    public function getPanelContent()
    {
      $listContent = '<ul id="debug_documentation_list" style="display: none;">
        <li>List Item 1</li>
        <li>List Item 2</li>
      </ul>';

      $toggler = $this->getToggler('debug_documentation_list', 'Toggle list');

      return sprintf('<h3>List Items %s</h3>%s',  $toggler, $listContent);
    }

`getToggler` は 2 つの引数を持ちます: 切り替える要素の DOM の `id` と、トグラーリンクの `title` 属性として設定される `title` です。`id` 属性を持った DOM 要素を作成し、トグラーを説明するラベル (例えば "List Items")を設定します。


### ~`sfWebDebugPanel::getToggleableDebugStack()`~

`getToggler()` に似て、`getToggleableDebugStack()` はコンテンツの表示を切り替えるためのクリックできる矢印を表示します。こちらは、デバッグスタックトレースです。この関数はカスタムクラスのログを表示する必要がある時に便利です。例えば、`myCustomClas` というクラスのログを表示したいときには:


    [php]
    class myCustomClass
    {
      public function doSomething()
      {
        $dispatcher = sfApplicationConfiguration::getActive()
          ->getEventDispatcher();

        $dispatcher->notify(new sfEvent($this, 'application.log', array(
          'priority' => sfLogger::INFO,
          'Beginning execution of myCustomClass::doSomething()',
        )));
      }
    }
    
例として、`myCustomClass` 関連のログメッセージの一覧をすべてデバッグスタックトレースに表示してみましょう。

    [php]
    public function getPanelContent()
    {
      // retrieves all of the log messages for the current request
      $logs = $this->webDebug->getLogger()->getLogs();

      $logList = '';
      foreach ($logs as $log)
      {
        if ($log['type'] == 'myCustomClass')
        {
          $logList .= sprintf('<li>%s %s</li>',
            $log['message'],
            $this->getToggleableDebugStack($log['debug_backtrace'])
          );
        }
      }

      return sprintf('<ul>%s</ul>', $logList);
    }

![トグルできる Web デバッグ](http://www.symfony-project.org/images/more-with-symfony/web_debug_04.png "トグルできるデバッグスタックの動作")

>**NOTE**
>カスタムパネルを生成しないにもかかわらず、`myCustomClass` のログメッセージがログパネルに表示されます。これが良い点は単にログメッセージの一部として 1 つの場所に集められ出力が制御出来る点です。

### ~`sfWebDebugPanel::formatFileLink()`~

symfony 1.3 で Web デバッグツールバーのファイルをクリックして、指定したテキストエディタで開く事が出来るようになりました。より詳しくは、["新しくなった点"](http://www.symfony-project.org/tutorial/1_3/ja/whats-new)を参照してください。

ファイルパスを特定する機能を有効にするには、`formatFileLink()` を使う必要があります。加えて開くファイル自身の目標とする行数の指定もできます。例えば、下のコードでは `config/ProjectConfiguration.class.php` ファイルの15行目へリンクします。

    [php]
    public function getPanelContent()
    {
      $content = '';

      // ...

      $path = sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php';
      $content .= $this->formatFileLink($path, 15, 'Project Configuration');

      return $content;
    }

    
2 番目の引数 (行番号) と 3 番目の引数 (リンクテキスト) はオプションです。もし"リンクテキスト"引数がなければ、ファイルパスがリンクテキストになります。


>**NOTE**
>テストの前に、新しいファイルリンク機能の設定を忘れないでください。この機能は `settings.yml` の `sf_file_link_format` キーか [xdebug](http://xdebug.org/docs/stack_trace#file_link_format) の `file_link_format` で設定できます。後者のメソッドは、プロジェクトが特定の統合開発環境に縛られていないことを保証します。

Web でバッグツールバーの他の小技
----------------------------------

###デフォルトパネルの削除

デフォルトでは、symfony は自動的に幾つかの Web デバッグパネルをツールバーに追加します。`debug.web.load_panels` イベントを使うことで、これらのデフォルトパネルを簡単に取り除くことができます。前に宣言した同じリスナー関数を使い、内部を `removePanel()` 関数に置き換えます。下のコードは`メモリー`パネルをツールバーから除きます:

    [php]
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->removePanel('memory');
    }


###パネルからリクエストパラメータを利用する

Web デバッグパネルの内部で一般的に必要とされる事の一つがリクエストパラメータです。例えば、`event_id` リクエストパラメータに対応するデータベースの `Event` オブジェクトから情報を表示したい時には次のようになります:

    [php]
    $parameters = $this->webDebug->getOption('request_parameters');
    if (isset($parameters['event_id']))
    {
      $event = Doctrine::getTable('Event')->find($parameters['event_id']);
    }

###状況によりパネルを隠す

現在のリクエストによっては、パネルがあまり有用ではないかもしれません。このようなときは、そのパネルを隠すこともできます。以前の例で、`event_id` リクエストパラメータが無かったときは情報を表示しないようにしてみましょう。パネルを隠すには、単に `getTitle()` メソッドで空を返すだけです:

    [php]
    public function getTitle()
    {
      $parameters = $this->webDebug->getOption('request_parameters');
      if (!isset($parameters['event_id']))
      {
        return;
      }

      return '<img src="/acWebDebugPlugin/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16"/> docs';
    }

あとがき
--------
Web デバッグツールバーで開発者は快適になりますが、デフォルトで表示されるのはあらかじめ用意されてる情報だけです。カスタム Web デバッグパネルを追加することで、開発者の工夫次第で Web デバッグツールバーの可能性は広がります。[ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin) はいくつかのパネルを含むだけです。あなた自身で工夫してみましょう。
