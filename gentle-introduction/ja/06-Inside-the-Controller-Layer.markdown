第6章 - コントローラーレイヤーの内側
======================================

symfony において、コントローラーレイヤーはビジネスロジックとプレゼンテーションを結びつけるコードを格納し、異なる目的で利用するためにいくつかのコンポーネントに分割されます:

  * フロントコントローラー (front controller) はアプリケーションへの唯一のエントリーポイント (entry point - 入り口) です。設定をロードし、実行するアクションを決定します。
  * アクション (action) はアプリケーションのロジックを格納します。リクエストの整合性をチェックし、プレゼンテーションレイヤーが必要なデータを準備します。
  * リクエスト、レスポンス、セッションオブジェクトはリクエストパラメーター、レスポンスヘッダー、永続的なユーザーデータにアクセスできます。これらはコントローラーレイヤー内部でよく使われます。
  * フィルター (filter) は、アクションの前あとで、すべてのリクエストに対して実行されるコードの一部です。たとえば、セキュリティとバリデーション (検証) フィルターはWebアプリケーションで共通に使われます。独自フィルターを作成することでフレームワークを拡張できます。

この章では、これらすべてのコンポーネントを説明しますが、数の多さに怖がらないでください。基本的なページに対して必要なことはアクションクラスのなかで数行のコードを書くことだけです。ほかのコントローラーコンポーネントは特定の状況のみに使われます。

フロントコントローラー
--------------------

すべての Web リクエストは1つのフロントコントローラーによって処理されます。フロントコントローラーは特定の環境におけるアプリケーション全体への唯一のエントリーポイントです。

フロントコントローラーはリクエストを受けとるとき、ユーザーが入力した (もしくはクリックした) URL を用いてアクションとモジュールの名前をマッチさせるルーティングシステムを使います。たとえば、つぎのリクエストURLは`index.php` スクリプト (フロントコントローラー) を呼び出し、`mymodule` モジュールの `myAction`アクションの呼び出しとして解釈されます:


    http://localhost/index.php/mymodule/myAction

symfony の内部にご興味がなければ、フロントコントローラーについて知る必要のあることはこれだけです。これは symfony の MVC アーキテクチャの不可欠なコンポーネントですが、変更が必要になることはほとんどありません。フロントコントローラーの内部構造を本当に理解したいと思わなければ、つぎのセクションに飛ぶことができます。

### フロントコントローラーの仕事の詳細

フロントコントローラーはリクエストのディスパッチ (dispatch - 発送) を行いますが、このことは単に実行するアクションを決定することよりも少し多くのことが行われていることを意味します。実際、つぎのようなすべてのアクションに共通なコードを実行します:

  1. プロジェクトの設定クラスと symfony のライブラリをロードする
  2. アプリケーションの設定と symfony の内容を作成する
  3. symfony のコアクラスをロードして初期化する
  4. 設定をロードする
  5. 実行するアクションとリクエストパラメーターを決定するためにリクエスト URL をデコードする
  6. アクションが存在しない場合、404エラーのアクションにリダイレクトする
  7. フィルターを有効にする (たとえば、リクエストが認証を必要とする場合)
  8. フィルターを実行する (ファーストパス部分)*
  9. アクションを実行しビューをレンダリングする
  10. フィルターを実行する (セカンドパス部分)*
  11. レスポンスを出力する

(訳注)* は、図6-3およびリスト6-30を参照

### デフォルトのフロントコントローラー

デフォルトのフロントコントローラーは、`index.php` という名前でプロジェクトの `web/` ディレクトリに設置されています。これはリスト6-1で示されるシンプルな PHP ファイルです。

リスト6-1 - 運用環境用のデフォルトのフロントコントローラー

    [php]
    <?php

    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

フロントコントローラーはアプリケーションの設定のインスタンスを作成します。これはステップ2から4に該当します。`sfController` オブジェクトの `dispatch()` メソッドの呼び出し( symfonyの MVC アーキテクチャのコアコントローラー) はリクエストをディスパッチします。これはステップ5から7に該当します。最後のステップはフィルターチェーンによって処理されます。これはこの章のあとで説明します。

### 環境を切り替えるためにほかのフロントコントローラーを呼び出す

環境ごとに1つのフロントコントローラーが存在します。当然のことながら、これは環境を定義するフロントコントローラーそのものです。環境は `ProjectConfiguration::getApplicationConfiguration()` メソッドの呼び出しに渡す2番目の引数によって定義されます。

ブラウザーでアプリケーションを見ながら環境を変更するには、ほかのフロントコントローラーを選びます。`generate:app` タスクで新しいアプリケーションを作成するときに利用できるデフォルトのフロントコントローラーは運用環境用の`index.php` と開発環境用の `frontend_dev.php` です (アプリケーションの名前が `frontend` であることが前提)。URLがフロントコントローラーのスクリプト名を含まないとき、デフォルトの `mod_rewrite` 設定は `index.php` を使います。両方の URL は運用環境で同じページ (`mymodule/index`) を表します:

    http://localhost/index.php/mymodule/index
    http://localhost/mymodule/index

そしてこの URL は開発環境で同じページを表示します:

    http://localhost/frontend_dev.php/mymodule/index

新しい環境を作成することは新しいフロントコントローラーを作成することと同じぐらい簡単です。たとえば、運用環境に移行するまえに顧客がアプリケーションをテストできるようにステージング環境 (staging environment) が必要になることがあります。このステージング環境を作成するには、`web/frontend_dev.php` の内容を`web/frontend_staging.php` にコピーして、`ProjectConfiguration::getApplicationConfiguration()`の呼び出しの2番目の引数の値を `staging` に変更します。すべての設定ファイルにおいて、リスト6-2で示されるように、この環境に対して特定の値を設定するために、新しい `staging:` セクションを追加できます。

リスト6-2 - ステージング環境のための特別な設定を格納する `app.yml` のサンプル

    staging:
      mail:
        webmaster:    dummy@mysite.com
        contact:      dummy@mysite.com
    all:
      mail:
        webmaster:    webmaster@mysite.com
        contact:      contact@mysite.com

この新しい環境でアプリケーションがどのように反応するのかを見たければ、関連するフロントコントローラーを呼び出します:

    http://localhost/frontend_staging.php/mymodule/index

アクション
----------

アクションはすべてのアプリケーションのロジックを格納するので、アプリケーションの中心的な役割を担います。アクションはモデルをコールし、ビューのための変数を定義します。symfony のアプリケーションで Web リクエストを作成するとき、URL はアクションとリクエストパラメーターを定義します。

### アクションクラス

アクションは `sfActions` クラスを継承する `moduleNameActions` クラスの `executeActionName` メソッドであり、モジュールによって分類されます。モジュールのアクションクラスは、モジュールの `actions/` ディレクトリの `actions.class.php` ファイルに保存されます。

リスト6-3は全体の `mymodule` モジュールに対して `index` アクションだけを持つ `actions.class.php` ファイルの例を示しています。

リスト6-3 - アクションクラスのサンプル (`apps/frontend/modules/mymodule/actions/actions.class.php`)

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeIndex($request)
      {
        // ...
      }
    }

>**CAUTION**
>PHP はメソッドの名前が大文字か小文字かを区別しませんが symfony は区別します。アクションメソッドは小文字の `execute` で始まり、つぎに最初が大文字で始まるアクションの名前が続くことを忘れないでください。

アクションをリクエストするには、パラメーターとしてモジュール名とアクション名を使いフロントコントローラーのスクリプトを呼び出す必要があります。デフォルトでは、この作業はスクリプトに `module_name`/`action_name` の組を追加することで行われます。このことはリスト6-4で定義されたアクションがつぎのURLで呼び出されることを意味します:

    http://localhost/index.php/mymodule/index

リスト6-4で示されるように、より多くのアクションを追加することは `sfActions` オブジェクトにより多くの `execute` メソッドを追加することを意味します。

リスト6-4 - 2つのアクションを持つアクションクラス (`frontend/modules/mymodule/actions/actions.class.php`)

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeIndex($request)
      {
        // ...
      }

      public function executeList($request)
      {
        // ...
      }
    }

アクションクラスのサイズが大きくなりすぎたら、リファクタリングを行いコードをモデルレイヤーに移動させることが必要でしょう。通常は、アクションのコードを短く保ち(数行以内)、すべてのビジネスロジックをモデル内部に保つべきです。

それでも、1つのモジュール内部にたくさんのアクションが存在するのであれば、そのモジュールを2つに分割することが重要です。

>**SIDEBAR**
>symfony のコーディング規約
>
>この本で示されたコードの例において、開き波かっこと閉じ波かっこ (`{` と `}`) がそれぞれ一行を占めることにおそらくお気づきでしょう。この規約によってコードはより読みやすくなります。
>
>symfony のほかのコーディング規約では、インデントはつねに2つの空白文字で行われます: タブは使いません。これはテキストエディタによってタブは異なる空白文字の値を持つので、タブと空白のインデントが混在するコードを見分けることが不可能だからです。
>
>symfony コアと生成されたPHPファイルは通常の閉じタグの `?>` で終わりません。これは本当に必要がないからと、このタグの後ろに空白がある場合、出力の問題が引き起こされる可能性があるからです。
>
>そして本当に注意を払っているのであれば、symfony では決して空白文字では終わりません。今回の場合、理由はつまらないことです: Fabien (筆者の一人) のテキストエディタでは空白で終わる行が不細工に見えるからです！

### アクションクラスの代替構文

アクションの代替構文は、個別のファイル、アクションごとに1つのファイルのアクションをディスパッチするために使えます。この場合、それぞれのアクションクラスは (`sfActions` の代わりに) `sfAction` を継承し、`actionNameAction` と名づけられます。実際のアクションメソッドはたんに `execute` と名づけられます。ファイルの名前はクラスの名前と同じです。このことはリスト6-4の同等の内容はリスト6-5と6-6で示される2つのファイルで書けることを意味します。

リスト6-5 - 単一アクションのファイル (`frontend/modules/mymodules/action/indexAction.class.php`)

    [php]
    class indexAction extends sfAction
    {
      public function execute($request)
      {
        // ...
      }
    }

リスト6-6 - 単一アクションのファイル (`frontend/modules/mymodules/actions/listAction.class.php`)

    [php]
    class listAction extends sfAction
    {
      public function execute($request)
      {
        // ...
      }
    }

### アクションの情報をとり出す

アクションクラスはコントローラー関連の情報と symfony のコアオブジェクトにアクセスする方法を提供します。リスト6-7はこれらの方法の実際の例を示しています。

リスト6-7 - `sfActions` の共通メソッド

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeIndex(sfWebRequest $request)
      {
        // リクエストパラメーターを読みとる
        $password    = $request->getParameter('password');

        // コントローラー情報をとり出す
        $moduleName  = $this->getModuleName();
        $actionName  = $this->getActionName();

        // symfonyのコアオブジェクトをとり出す
        $userSession = $this->getUser();
        $response    = $this->getResponse();
        $controller  = $this->getController();
        $context     = $this->getContext();

        // テンプレートに情報を渡すためにアクション変数を設定する
        $this->setVar('foo', 'bar');
        $this->foo = 'bar';            // 短いバージョン
      }
    }

>**SIDEBAR**
>Context Singleton
>
>すでにフロントコントローラー内部で `sfContext::createInstance()` の呼び出しを見ました。アクションにおいて、`getContext()` メソッドは同じ Singleton を返します。これは任意のリクエストに関連するすべての symfony のコアオブジェクトへの参照を保存し、各オブジェクトに対してアクセサーを提供する非常に便利なオブジェクトです:
>
>`sfController`: コントローラーオブジェクト (`->getController()`)
>
>`sfRequest:` リクエストオブジェクト (`->getRequest()`)
>
>`sfResponse`: レスポンスオブジェクト (`->getResponse()`)
>
>`sfUser`: ユーザーセッションオブジェクト (`->getUser()`)
>
>`sfRouting`: ルーティングオブジェクト (`->getRouting()`)
>
>`sfMailer`: メーラーオブジェクト (`->getMailer()`)
>
>`sfI18N`: 国際化オブジェクト (`->getI18N()`)
>
>`sfLogger`: loggerオブジェクト (`->getLogger()`)
>
>`sfDatabaseConnection`: データベースコネクション (`->getDatabaseConnection()`)
>全てのこれらのコアオブジェクトは `sfContext::getInstance()` シングルトンを通してどこからでも利用できます。
>しかしながら、依存性が高くなってしまうことによりテストするのが難しくなるでしょう。
>この本で `sfContext::getinstance()`をどのように使わないようにするかを学ぶことができます。

### アクションの終了方法

アクションの実行結果においてさまざまなふるまいが可能です。アクションメソッドが返す値はビューをレンダリングする方法を決定します。`sfView` クラスの定数はアクションの結果の表示に使われるテンプレートを指定するために使われます。

呼び出すビューのデフォルトが存在する場合 (もっとも共通の事例)、アクションはつぎのように終わります:

    [php]
    return sfView::SUCCESS;

symfony は `actionNameSuccess.php` という名前のテンプレートを探します。これはアクションのデフォルトのふるまいとして定義されているので、アクションメソッド側で `return` 文が省略されると、symfony は `actionNameSuccess.php` テンプレートも探します。空のアクションでも同じふるまいをします。成功したアクションの終了方法の例はリスト6-8をご覧ください。

リスト6-8 - `indexSuccess.php` と `listSuccess.php` テンプレートを呼び出すアクション

    [php]
    public function executeIndex()
    {
      return sfView::SUCCESS;
    }

    public function executeList()
    {
    }

呼び出すエラービューが存在する場合、アクションはつぎのように終わります:

    [php]
    return sfView::ERROR;

symfony は `actionNameError.php` という名前のテンプレートを探します。

カスタムビューを呼び出すには、つぎのように終わらせます:

    [php]
    return 'MyResult';

symfony は `actionNameMyResult.php` によって呼び出されたテンプレートを探します。

呼び出すビューが存在しない場合、たとえばバッチプロセスのなかで実行されたアクションの場合、アクションはつぎのように終わります:

    [php]
    return sfView::NONE;

この場合、テンプレートは実行されません。このことはビューレイヤーを完全に回避して、アクションから直接 HTML コードを出力できることを意味します。リスト6-9で示されるように、この事例のために symfony は特別な `renderText()` メソッドを提供します。これは11章で検討される Ajax インタラクションなどのアクションの非常に高い反応性を必要とするときに役立ちます。

リスト6-9 - `sfView::NONE` を返すことでビューを回避してレスポンスを直接出力する

    [php]
    public function executeIndex()
    {
      echo "<html><body>Hello, World!</body></html>";

      return sfView::NONE;
    }

    // つぎの内容と同等
    public function executeIndex()
    {
      return $this->renderText("<html><body>Hello, World!</body></html>");
    }

いくつかの場合において、定義されたヘッダー以外のヘッダー (特に `X-JSON` ヘッダー)をともなう空のレスポンスを送信する必要があります。つぎの章で検討しますがリスト6-10で示されているように、`sfResponse` オブジェクト経由でヘッダーを定義して `sfView::HEADER_ONLY` 定数を返します。

リスト6-10 - ビューのレンダリングを回避してヘッダーのみを送信する

    [php]
    public function executeRefresh()
    {
      $output = '<"title","My basic letter"],["name","Mr Brown">';
      $this->getResponse()->setHttpHeader("X-JSON", '('.$output.')');

      return sfView::HEADER_ONLY;
    }

アクションを特定のテンプレートによってレンダリングしなければならない場合、`return` 文を無視して、代わりに `setTemplate()` メソッドを使います。

    [php]
    public function executeIndex()
    {
      $this->setTemplate('myCustomTemplate');
    }

このコードを書くことで、symfonyは `indexSuccess.php` ファイルの代わりに `myCustomTemplateSuccess.php` ファイルを探そうとします。

### 別のアクションにスキップする

アクションの実行が新しいアクションの実行をリクエストすることで終わることがあります。たとえば POST リクエストフォームでは通常の場合、投稿を処理するアクションはデータベースを更新したあとで別のアクションにリダイレクトします。

アクションクラスは別のアクションを実行するためのメソッドを2つ提供します:

  * アクションが別のアクションにフォワードする場合:

        [php]
        $this->forward('otherModule', 'index');

  * アクションが Web リダイレクトの結果になる場合:

        [php]
        $this->redirect('otherModule/index');
        $this->redirect('http://www.google.com/');

>**NOTE**
>アクションにおいてフォワードもしくはリダイレクトの後に設置されたコードは決して実行されません。これらの呼び出しは `return` 文と同等のものとみなすことができます。アクションの実行を停止させるためにこれらは `sfStopException` を投げます; この例外は symfony によってあとで捕捉され、たんに無視されます。

ときにリダイレクトもしくはフォワードを選びにくいことがあります。最良の解決法を選ぶには、フォワードはアプリケーションの内部に存在しユーザーには見えないことを覚えておいてください。ユーザーに関しては、表示されるURL はリクエストされたものと同じです。対照的に、リダイレクトはユーザーのブラウザーへのメッセージであり、それ以降の新しいリクエストと URL の最後の結果の変更を含みます。

アクションが `method="post"` メソッドで投稿されたフォームから呼び出された場合、**つねに**リダイレクトを行うべきです。主な利点はユーザーが結果ページをリフレッシュするさいにフォームが再投稿されないことです; 加えて、フォームを表示して、ユーザーが POST リクエストを再投稿したいかどうかを確認するアラートを表示しないことで前のページに戻るボタンが期待どおりに動作します。

とてもよく使われる特殊なフォワードが1つあります。`forward404()` メソッドは「見つからないページ」用のアクションにフォワードします。アクションの実行に必要なパラメーターがリクエストのなかに存在しないときに、このメソッドはよく呼び出されます (誤って入力された URL を検出します)。リスト6-11は `id` パラメーターを必要とする `show` アクションの例を示しています。

リスト6-11 - `forward404()` メソッドの使いかた

    [php]
    public function executeShow(sfWebRequest $request)
    {
      // Doctrine
      $article = Doctrine::getTable('Article')->find($request->getParameter('id'));

      // Propel
      $article = ArticlePeer::retrieveByPK($request->getParameter('id'));
      if (!$article)
      {
        $this->forward404();
      }
    }

>**TIP**
>404エラー用のアクションとテンプレートは `$sf_symfony_ lib_dir/controller/default/` ディレクトリで見つかります。新しい `default` モジュールをあなたのアプリケーションに追加することで、もしくはフレームワーク内に設置されたものをオーバーライドすることで、または内部で `error404` アクションと `error404Success` テンプレートを定義することによって、このページをカスタマイズできます。代わりの方法として、既存のアクションを使うために `settings.yml` ファイルで`error_404_module` と `error_404_action` アクション定数を設定することもできます。

経験則によれば、多くの場合リスト6-12にような何かをテストした後にアクションはリダイレクトもしくはフォワードを作ることがあります。`sfActions` クラスのメソッド、`forwardIf()`、`forwardUndless()`、`forward404If()`、`forward404Unless()`、`redirectIf()`と `redirectUnless()` があるのはそういうわけです。リスト6-12で示されるように、これらのメソッドは、テストして (`xxxIf()` メソッドに対して) true もしくは (`xxxUnless()` メソッド) false を返された場合に実行する条件を表す、1つの追加パラメーターをとります。

リスト6-12 - `forward404If()` メソッドの使いかた

    [php]
    // このアクションはリスト6-12で示されているものと同等である
    public function executeShow(sfWebRequest $request)
    {
      $article = Doctrine::getTable('Article')->find($request->getParameter('id'));
      $this->forward404If(!$article);
    }

    // これも同じ
    public function executeShow(sfWebRequest $request)
    {
      $article = Doctrine::getTable('Article')->find($request->getParameter('id'));
      $this->forward404Unless($article);
    }

これらのメソッドを使うことでコードを短く保つことができるだけでなく、読みやすくなります。

>**TIP**
>アクションが `forward404()` メソッドもしくはその仲間のメソッドを呼び出すとき、symfony は404エラーのレスポンスを管理する `sfError404Exception` を投げます。コントローラーにアクセスしたくないどこかの場所から404エラーのメッセージを表示することが必要な場合、同じような例外が投げられることを意味します。

### 1つのモジュールの複数のアクションのあいだでコードを繰り返す

アクション名を `executeActionName()` (`sfActions` クラスの場合)もしくは `execute()` メソッド(`sfAction` クラスの場合)`execute` とする規約によって symfony がアクションメソッドを探すことが保証されます。この規約によってメソッド名が `execute` で始まらないかぎり、アクションと見なされたくない独自のほかのメソッドを追加できるようになります。

アクションを実際に実行するまえにそれぞれのアクションごとにおいて複数のステートメントを繰り返す必要があるとき、別の便利なメソッドがあります。これらをアクションクラスの `preExecute()` メソッドに展開できます。アクションが実行されるたびにそのあとでステートメントを繰り返す方法を推測できるでしょう: それらを `postExecute()` メソッドにラップします。これらのメソッドの構文はリスト6-13で示されています。

リスト6-13 - アクションクラスのなかで `preExecute()`、`postExecute()` とカスタムメソッドを使う

    [php]
    class mymoduleActions extends sfActions
    {
      public function preExecute()
      {
        // ここに挿入されたコードはそれぞれのアクション呼び出しの始めに実行される
        ...
      }

      public function executeIndex($request)
      {
        ...
      }

      public function executeList($request)
      {
        ...
        $this->myCustomMethod();  // アクションクラスのメソッドがアクセス可能である
      }

      public function postExecute()
      {
        // ここに挿入されたコードはそれぞれのアクション呼び出しの終了時に実行される
        ...
      }

      protected function myCustomMethod()
      {
        // "execute"で始まらないかぎり、独自メソッドも追加できる
        // この場合、protected もしくは private として宣言するほうがよい
        ...
      }
    }

>**TIP**
>preExecuteとpostExecuteメソッドは現在のモジュールの**各**アクションごとに呼ばれるので、思わぬ結果にならないように**全ての**アクションでこのコードが実行されるということを理解しておく必要があります。

リクエストにアクセスする
------------------------

アクションメソッドの最初の引数はリクエストオブジェクトであり、symfony では `sfWebRequest` と呼ばれます。以前の章で名前でリクエストパラメーターの値を読みとるために使う `getParameter('myparam')` メソッドに慣れました。テーブルは `sfWebRequest` オブジェクトのもっとも便利なメソッドの一覧です。

テーブル6-1 - `sfWebRequest` オブジェクトのメソッド

名前                            |機能                                |サンプルの出力
--------------------------------|------------------------------------|------------------
**リクエスト情報**              |                                    |
`isMethod($method)`             | POST もしくは GET？                  | trueもしくはfalse
`getMethod()`                   | リクエストメソッドの名前           | `'POST'`
`getHttpHeader('Server')`       |任意の HTTP ヘッダーの値             |`'Apache/2.0.59 (Unix) DAV/2 PHP/5.1.6'` 
`getCookie('foo')`              |名前つき Cookie の値              |`'bar'` 
`isXmlHttpRequest()*`           |Ajax リクエストであるか？            |`true` 
`isSecure()`                    |SSLリクエストであるか？             |`true` 
**リクエストパラメーター**    |                                         |
`hasParameter('foo')`           |リクエストにパラメーターが存在するか？|`true` 
`getParameter('foo')`           |命名されたパラメーターの値            |`'bar'` 
`getParameterHolder()->getAll()`|すべてのリクエストパラメーターの配列  |
URI関連の情報                   |                                    |
`getUri()`                      |完全なURI                             |`'http://localhost/frontend_dev.php/mymodule/myaction'`
`getPathInfo()`                 |パス情報                      |`'/mymodule/myaction'` 
`getReferer()**`                |リファラ                            |`'http://localhost/frontend_dev.php/'` 
`getHost()`                     |ホスト名                     |`'localhost'` 
`getScriptName()`               |フロント・コントローラーのパス名    |`'frontend_dev.php'` 
**クライアントのブラウザー情報**  |                                    |
`getLanguages()`                |受信した言語の配列                  |`Array( [0] => fr [1] => fr_FR [2] => en_US [3] => en )` 
`getCharsets()`                 |受信した文字集合の配列            |`Array( [0] => ISO-8859-1 [1] => UTF-8 [2] => * )` 
`getAcceptableContentType()`    | 受信した Content-Type の配列       | `Array( [0] => text/xml [1] => text/html`

`*` *Prototype、Mootools と jQuery で動作する*

`**` *時々プロキシでブロックされる*

ユーザーセッション
------------------

symfony はユーザーセッションの管理を自動化してユーザーのためにリクエスト間のデータを一貫したものに保ちます。symfony は PHP 組み込みのセッションハンドリングメカニズムを利用しこれらをより柔軟に設定可能で使いやすいものにするために強化します。

### ユーザーセッションにアクセスする

現在のユーザーセッションオブジェクトには `sfUser` クラスのインスタンスである `getUser()` メソッドを持つアクションでアクセスします。このクラスはユーザー属性の保存を可能にするパラメーターホルダーを持ちます。リスト6-15で示されるように、このデータはユーザーセッションの終了までほかのリクエストで利用可能です。ユーザー属性はさまざまなデータ型 (文字列、配列と連想配列) を保存できます。ユーザーが認証されていないとしても個別のユーザーごとに設定できます。

リスト6-14 - `sfUser` オブジェクトは複数のリクエストにまたがって存在するカスタムユーザー属性を保持する

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeFirstPage($request)
      {
        $nickname = $request->getParameter('nickname');

        // データをユーザーセッションに保存する
        $this->getUser()->setAttribute('nickname', $nickname);
      }

      public function executeSecondPage()
      {
        // デフォルト値を指定してユーザーセッションからデータをとり出す
        $nickname = $this->getUser()->getAttribute('nickname', 'Anonymous Coward');
      }
    }

>**CAUTION**
>オブジェクトをユーザーセッションに保存できますがこれは非推奨です。なぜならリクエストの間にセッションオブジェクトがシリアライズされるからです。セッションのシリアライズが解除されたとき、保存されたオブジェクトのクラスはすでにロードされなければなりませんが、つねにあてはまることではないからです。加えて、Propel や Doctrine オブジェクトを保存する場合、膠着状態 (stalled) になったオブジェクトが存在する可能性があります。

symfony の多くのゲッターのように、属性が定義されていないときに使われるデフォルト値を指定するために `getAttribute()` メソッドは2番目の引数を受けとります。ユーザーのために属性が定義されているかどうかを確認するには、`hasAttribute()` メソッドを使います。属性は `getAttributeHolder()` メソッドによってアクセス可能なパラメーターホルダーに保存されます。リスト6-15で示すように、これによって通常のパラメーターホルダーのメソッドでユーザー属性を簡単に一掃できるようになります。

リスト6-15 - ユーザーセッションからデータを削除する

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeRemoveNickname()
      {
        $this->getUser()->getAttributeHolder()->remove('nickname');
      }

      public function executeCleanup()
      {
        $this->getUser()->getAttributeHolder()->clear();
      }
    }

リスト6-16で示されるように、現在の `sfUser` オブジェクトを保存する `$sf_user` 変数を通して、ユーザーセッション属性はテンプレートのなかでもデフォルトで利用できます。

リスト6-16 - テンプレートはユーザーセッション属性にもアクセスできる

    [php]
    <p>
      Hello, <?php echo $sf_user->getAttribute('nickname') ?>
    </p>

### フラッシュ属性

ユーザー属性に関して繰り返し起きる問題は属性が不要になったときにユーザーセッションを消去することです。たとえば、フォームを通してデータを更新したあとに確認メッセージを表示したい場合を考えます。フォームを処理するアクションがリダイレクトを行う場合、このアクションからの情報をリダイレクトアクションに渡す唯一の方法はユーザーセッションに情報を保存することです。しかしいったん確認メッセージが表示されるら、属性をクリアする必要があります; そうでなければ、有効期間が切れるまでセッションが維持されることになります。

フラッシュ (flash) 属性は定義した後にすぐに忘れてもよい短命の属性です。これはすぐつぎのリクエストのあとで消えるので将来のユーザーセッションはクリーンな状態に保たれます。アクションのなかでは、つぎのようにフラッシュ属性を定義します:

    [php]
    $this->getUser()->setFlash('notice', $value);

テンプレートはレンダリングされ、別のアクションに新しくリクエストするユーザーに配信されます。この2番目のアクションにおいて、フラッシュ属性の値を得るにはつぎのように書きます:

    [php]
    $value = $this->getUser()->getFlash('notice');

それから忘れてください。この2番目のページを配信したあとで、フラッシュ属性の `notice` は消去されます。この2番目のアクションの期間にこの属性を求めない場合でも、どのみちflash属性はセッションから消えます。

テンプレートからflash属性にアクセスする必要がある場合、`$sf_user` オブジェクトを使います:

    [php]
    <?php if ($sf_user->hasFlash('notice')): ?>
      <?php echo $sf_user->getFlash('notice') ?>
    <?php endif; ?>

もしくはつぎのように書くこともできます:

    [php]
    <?php echo $sf_user->getFlash('notice') ?>

flash属性はすぐつぎのリクエストに情報を渡すための正当な手段です。

### セッションの管理

symfony のセッションハンドリングによって完全にクライアントとサーバーのセッション ID の保存は開発者に対して覆い隠されます。しかしながら、セッション管理メカニズムのデフォルトのふるまいを修正することも可能です。このセクションはおもに上級ユーザー向けです。

クライアントサイドにおいて、セッションは Cookie によって処理されます。symfony セッションの Cookie の名前は `symfony` ですが、リスト6-18で示されるように、`factories.yml` 設定ファイルを編集することでこの名前を変更できます。

リスト6-18 - Cookie の名前を変更する (`apps/frontend/config/factories.yml`)

    all:
      storage:
        class: sfSessionStorage
        param:
          session_name: my_cookie_name

>**TIP**
>`factories.yml` で `auto_start` パラメーターが `true` としてセットされている場合のみ (PHP の `session_start()` 関数で) セッションは始まります (デフォルトの場合)。ユーザーセッションを手動で始めたい場合、このストレージファクトリの設定を無効にします。

symfony のセッションハンドリング機能は PHP セッションに基づいています。Cookie の代わりに URL パラメーターによって扱うセッションのクライアントサイドの管理が必要な場合、`php.ini` の `use_trans_sid` 設定を変更する必要があります。しかしながら、これは推奨されていないことをご了承ください。

    session.use_trans_sid = 1

サーバーサイドにおいて、symfony はデフォルトでユーザーセッションをファイルに保存します。リスト6-18で示されるように、`factories.yml` の `class` パラメーターの値を変更することでそれらをデータベースに保存できます

リスト6-18 - サーバーセッションストレージ (`apps/frontend/config/factories.yml`)

    all:
      storage:
        class: sfMySQLSessionStorage
        param:
          db_table:    session              # セッションを保存するテーブルの名前
          database:    propel               # 使うデータベース接続の名前
          # オプションのパラメーター
          db_id_col:   sess_id              # セッションidを保存するカラムの名前
          db_data_col: sess_data            # セッションのデータを保存するカラムの名前
          db_time_col: sess_time            # セッションのタイムスタンプを保存するカラムの名前

`database` 設定は使うデータベース設定を定義します。この接続に対してデータベース設定 (ホスト、データベース名、ユーザー、とパスワード) を決定するために、symfony は `databases.yml` (8章を参照) を使います。

利用できるセッションストレージクラスは `sfCacheSessionStorage`、 `sfMySQLSessionStorage`、 `sfMySQLiSessionStorage`、 `sfPostgreSQLSessionStorage` そして `sfPDOSessionStorage` です; 後者が望ましいです。セッションストレージを完全に無効にするには、`sfNoStorage` クラスを使います。

セッションの期限切れは30分後に自動的に起きます。このデフォルトの設定は同じ `factories.yml` 設定ファイルのなかでそれぞれの環境に対して修正できますが、リスト6-19で示されるように、今回は `user` ファクトリで行います。

リスト6-19 - セッションの有効期間を変更する (`apps/frontend/config/settings.yml`)

    all:
      user:
        class:       myUser
        param:
          timeout:   1800           # 秒単位のセッションの有効期間

ファクトリを詳しく学ぶには19章を参照してください。

アクションのセキュリティ
-------------------------

アクションを実行する機能は特定の権限を持つユーザーに制限されます。この目的のために symfony が提供するツールによってセキュアなアプリケーションを作ることができます。このアプリケーションでは、ユーザーはいくつかの機能もしくはアプリケーションの一部にアクセスするまえに認証される必要があります。アプリケーションをセキュアにするには2つのステップが必要です: それぞれのアクションに対してセキュリティ要件を宣言し、これらのセキュアなアクションにアクセスできるようにユーザーがログインして権限を持つことです。

### アクセスの制限

実行されるまえに、すべてのアクションは特別なフィルターに通されます。このフィルターは現在のユーザーがリクエストしたアクションにアクセスする権限を持つかどうかをチェックします。symfony において、権限は2つの部分で構成されます:

  * セキュアなアクションはユーザーを認証することを必要とします。
  * クレデンシャル (credential) はセキュリティをグループ単位で編成できるようにする名前つきのセキュリティ権限です。

アクションへのアクセスを制限するにはモジュールの `config/` ディレクトリの `security.yml` という名前の YAML 設定ファイルを作り編集します。このファイルにおいて、ユーザーがそれぞれのアクションもしくは `all` アクションに対して満たさなければならないセキュリティ要件を指定できます。リスト6-20は `security.yml` のサンプルを示しています。

リスト6-20 - アクセス制限の設定 (`apps/frontend/modules/mymodule/config/security.yml`)

    read:
      is_secure:   false       # すべてのユーザーは read アクションをリクエストできる

    update:
      is_secure:   true        # update アクションは認証されたユーザーに対してのみ

    delete:
      is_secure:   true        # admin クレデンシャルを持つ
      credentials: admin       # 認証されたユーザーのみ

    all:
      is_secure:  false        # ともかく off はデフォルト値

デフォルトではアクションはセキュアではありませんので、`security.yml` もしくはアクションに関する記述が存在しない場合、アクションは誰でもアクセスできます。`security.yml` が存在する場合、symfony はリクエストされたアクションの名前を探し、存在する場合、セキュリティ要件を満たしているかチェックします。ユーザーが制限されたアクションにアクセスしようとしたときに起きることはユーザーのクレデンシャルによって異なります:

  * ユーザーが認証され、適切なクレデンシャルを持つ場合、アクションは実行されます。
  * ユーザーが識別されなかった場合、ユーザーはデフォルトのログインアクションにリダイレクトされます。
  * ユーザーが識別されているが、適切なクレデンシャルを持たない場合、図6-1で示されるように、ユーザーはデフォルトのセキュアなアクションにリダイレクトされます。

デフォルトのログインとセキュアなページはとてもシンプルなのでカスタマイズしたいことでしょう。リスト6-21で示されるように、アプリケーションの `settings.yml` でプロパティの値を変更することで、不十分な権限の場合に呼び出されるアクションを設定できます。

図6-1 - デフォルトのセキュアなアクションページ

![デフォルトのセキュアなアクションページ](http://www.symfony-project.org/images/book/1_4/F0601.jpg "デフォルトのセキュアなアクションページ")

リスト6-21 - デフォルトのセキュリティアクションを定義する (`apps/frontend/config/settings.yml`)

    all:
      .actions:
        login_module:  default
        login_action:  login

        secure_module: default
        secure_action: secure

### アクセス権を付与する

制限されたアクションにアクセスするには、ユーザーは認証されるかつ/もしくは特定のクレデンシャルを持つことが必要です。`sfUser` オブジェクトのメソッドを呼び出すことでユーザーの権限を拡張できます。ユーザーの認証ステータスは `setAuthenticated()` メソッドによって設定され `isAuthenticated()` によってチェックされます。リスト6-22はユーザー認証のシンプルな例を示します。

リスト6-22 - ユーザーの認証ステータスを設定する

    [php]
    class myAccountActions extends sfActions
    {
      public function executeLogin($request)
      {
        if ($request->getParameter('login') === 'foobar')
        {
          $this->getUser()->setAuthenticated(true);
        }
      }

      public function executeLogout()
      {
        $this->getUser()->setAuthenticated(false);
      }
    }

クレデンシャルをチェック、追加、削除、クリアできるので、クレデンシャルの取り扱いは少し複雑です。リスト6-23は `sfUser` クラスのクレデンシャルメソッドを説明しています。

リスト6-23 - アクションのなかでユーザーのクレデンシャルを処理する

    [php]
    class myAccountActions extends sfActions
    {
      public function executeDoThingsWithCredentials()
      {
        $user = $this->getUser();

        // 1つもしくは複数のクレデンシャルを追加する
        $user->addCredential('foo');
        $user->addCredentials('foo', 'bar');

        // ユーザーがクレデンシャルを持つかどうかを確認する
        echo $user->hasCredential('foo');                      =>   true

        // ユーザーが1つのクレデンシャルを持つのか確認する
        echo $user->hasCredential(array('foo', 'bar'));        =>   true

        // ユーザーが両方のクレデンシャルを持つのか確認する
        echo $user->hasCredential(array('foo', 'bar'), false); =>   true

        // 1つのクレデンシャルを削除する
        $user->removeCredential('foo');
        echo $user->hasCredential('foo');                      =>   false

        // すべてのクレデンシャルをクリアする(ログアウト処理で便利)
        $user->clearCredentials();
        echo $user->hasCredential('bar');                      =>   false
      }
    }

ユーザーが `foo` クレデンシャルを持つ場合、そのクレデンシャルを必要とする `security.yml` に対してそのユーザーはアクセスできるようになります。リスト6-24で示されるように、クレデンシャルはテンプレート内の認証された内容を表示するためだけにも使えます。

リスト6-24 - テンプレートのなかでユーザーのクレデンシャルを処理する

    [php]
    <ul>
      <li><?php echo link_to('section1', 'content/section1') ?></li>
      <li><?php echo link_to('section2', 'content/section2') ?></li>
      <?php if ($sf_user->hasCredential('section3')): ?>
      <li><?php echo link_to('section3', 'content/section3') ?></li>
      <?php endif; ?>
    </ul>

認証ステータスに関しては、ログイン処理の間にしばしばユーザーにクレデンシャルが付与されます。中央管理方式でユーザーのセキュリティステータスを設定するために、しばし `sfUser` オブジェクトにログインとログアウトのメソッドが追加され拡張される理由はそういうことです。

>**TIP**
>symfony のプラグインである、[`sfGuardPlugin`](http://www.symfony-project.org/plugins/sfGuardPlugin) and [`sfDoctrineGuardPlugin`](http://www.symfony-project.org/plugins/sfDoctrineGuardPlugin)  はログインとログアウトを簡単にするセッションクラスを拡張します。詳細な情報は17章を参照してください。

### 複雑なクレデンシャル

`security.yml` ファイルのなかで使われる YAML ファイルは、AND 型と OR 型の関係を利用することで、クレデンシャルの組み合わせを持つユーザーの権限を制限できます。このような組み合わせによって、複雑なワークフローとユーザーの権限管理システム、たとえば CMS (Content Management System) を開発できます。CMS において `admin` クレデンシャルを持つユーザーのみがバックオフィスにアクセス可能で、記事の作成は `editor` クレデンシャルを持つユーザーだけが、記事の公開は `publisher` クレデンシャルを持つユーザーだけが可能です。リスト6-25はこの例を示しています。

リスト6-25 - クレデンシャル構文の組み合わせ

    editArticle:
      credentials: [ admin, editor ]              # admin AND editor

    publishArticle:
      credentials: [ admin, publisher ]           # admin AND publisher

    userManagement:
      credentials: [[ admin, superuser ]]         # admin OR superuser

新しいレベルの角かっこを追加するたびにロジックの AND と OR をお互いに交換できます。つぎのように、とても複雑なスクレデンシャルの組み合わせを作ることができます:

    credentials: [[root, [supplier, [owner, quasiowner]], accounts]]
                 # root OR (supplier AND (owner OR quasiowner)) OR accounts

フィルター
--------

セキュリティ処理はすべてのリクエストがアクションを実行するまえに通過しなければならない1つのフィルター (filter) として理解できます。フィルターのなかで実行されるいくつかのテストにしたがって、リクエストの処理は、たとえば実行されるアクションを変更することで修正されます(セキュリティフィルターの場合、リクエストされたアクションの代わりに `default`/`secure`)。symfony はこのアイディアをフィルタークラスに発展させます。アクションの実行前、もしくはレスポンスのレンダリングのまえに実行されるフィルタークラスの数を指定し、すべてのリクエストに対してこれを行います。フィルターをコードにまとめる方法としてみなすことができます。これは `preExecute()` と `postExecute()` と似ていますが、より高いレベルです (モジュール全体の代わりにアプリケーション全体)。

### フィルターチェーン

実際には symfony はリクエストの処理をフィルターチェーン (filter chain) と見なします。リクエストがフレームワークによって受信されたとき、最初のフィルター (つねに `sfRenderingFilter`)が実行されます。ある時点で、チェーンのなかのつぎのフィルターを呼び出し、同じように続きます。最後のフィルター (つねに `sfExecutionFilter`) が実行されるとき、以前のフィルターを終了させることが可能で、フィルターのレンダリングなどに戻ります。図6-3は、連続するダイアグラムで、模型の小さなフィルターチェーン (本物はもっと多くのフィルターを含む) を使ってこのアイディアを説明しています。

図6-3 - フィルターチェーンのサンプル

![図6-3 - フィルターチェーンのサンプル](http://www.symfony-project.org/images/book/1_4/F0603.png "図6-3 - フィルターチェーンのサンプル")

このプロセスはフィルタークラスの構造が正しいことを納得させてくれます。これらすべてのフィルタークラスは `sfFilter` クラスを継承し、`$filterChain` オブジェクトをパラメーターとして必要とする `execute()` メソッドを持ちます。このメソッドのどこかで、フィルターは `$filterChain->execute()` を呼び出すことでチェーンのつぎのフィルターに移動します。リスト6-26を例としてご覧ください。基本的には、フィルターは2つの部分に分割されます:

  * `$filterChain->execute()` の呼び出し前のコードはアクションが実行されるまえに実行されます。
  * `$filterChain->execute()` の呼び出し後のコードはアクションが実行された後とレンダリングのまえに実行されます。

リスト6-26 - フィルタークラスの構造

    [php]
    class myFilter extends sfFilter
    {
      public function execute ($filterChain)
      {
        // この部分のコードは、アクションが実行されるまえに実行される(訳注：ファーストパス部分)
        ...

        // チェーンでつぎのフィルターを実行する
        $filterChain->execute();

        // この部分のコードは、アクションが実行された後、レンダリングが実行されるまえに実行される(訳注：セカンドパス部分)
        ...
      }
    }

リスト6-27で示されるように、デフォルトのフィルターチェーンは `filters.yml` という名前のアプリケーション設定ファイルで定義されます。このファイルはすべてのリクエストに対して実行される予定のフィルターの一覧を表示します。

リスト6-27 - デフォルトのフィルターチェーン (`frontend/config/filters.yml`)

    rendering: ~
    security:  ~

    # 一般的に、ここで独自フィルターを差し込む

    cache:     ~
    execution: ~

これらの宣言はパラメーターを持ちません (チルダ文字 `~` は YAML において「null」を意味します)。なぜなら、symfony はコアで定義されたパラメーターを継承するからです。コアにおいて、symfonyはそれぞれのフィルターに対して `class` と `param` 設定を定義します。たとえば、リスト6-28は `rendering` フィルター用のデフォルトパラメーターを示しています。

リスト6-28 - フィルターをレンダリングするためのデフォルトパラメーター (`$sf_symfony_data_dir/config/filters.yml`)

    rendering:
      class: sfRenderingFilter   # フィルタークラス
      param:                     # フィルターパラメーター
        type: rendering

空の値 (`~`) をアプリケーションの `filters.yml` に残すことで、コア内部で定義されたデフォルト設定をフィルターに適用するように symfony に伝えます。

さまざまな方法でフィルターチェーンをカスタマイズできます:

  * `enabled: false` パラメーターを追加することでチェーン内の複数のフィルターを無効にできます。たとえば、`security` フィルターを無効にするには、つぎのように書きます:

        security:
          enabled: false

  * フィルターを無効にするには `filters.yml` からエントリーを削除しないでください； この場合、symfony は例外を投じます。
  * カスタムフィルターを追加するには独自の宣言をチェーンのどこか (通常は `security` フィルターの後) に追加してください(つぎのセクションで検討)。`rendering` フィルターは最初のエントリーでなければならないこと、`execution` フィルターはフィルターチェーンの最後のエントリーでなければならないことに注意してください。
  * デフォルトフィルターのデフォルトクラスとパラメーターをオーバーライドします (特にセキュリティシステムを修正して、独自のセキュリティフィルターを使うため)。

### 独自フィルターを開発する

フィルターの開発はとてもシンプルです。オートロード機能を利用するには、リスト6-31のような定義を作りプロジェクトの `lib/` フォルダーの1つに設置します。

アクションは別のアクションにフォワードするかリダイレクトすることが可能で、結果としてフィルターのフルチェーンを再起動するので、独自フィルターの実行をリクエストの最初のアクション呼び出しに制限したいことがあります。この目的のために `sfFilter` クラスの `isFirstCall()` メソッドはブール値を返します。この呼び出しの意味があるのはアクションが実行されるまえだけです。

これらの概念は実例でよりあきらかになります。リスト6-29は、ログインアクションによって作られたことを前提とした、名前が `MyWebSite` である固有の Cookie を持つユーザーを自動ログインするために使われるフィルターを示します。ログインフォームに提供された 「remember me」 の機能を実装することは初歩的ですが実用的な方法です。

リスト6-29 - フィルタークラスのサンプル (`apps/frontend/lib/rememberFilter.class.php`)

    [php]
    class rememberFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        // このフィルターを1回だけ実行する
        if ($this->isFirstCall())
        {
          // フィルターはリクエストとユーザーのオブジェクトに直接アクセスできない。
          // これらを手に入れるためにcontextオブジェクトを使う必要がある
          $request = $this->getContext()->getRequest();
          $user    = $this->getContext()->getUser();

          if ($request->getCookie('MyWebSite'))
          {
            // ログイン
            $user->setAuthenticated(true);
          }
        }

        // つぎのフィルターを実行する
        $filterChain->execute();
      }
    }

いくつかの場合において、フィルターチェーンを実行する代わりに、フィルターの最後で特定のアクションにフォワードすることが必要になります。`sfFilter` は `forward()` メソッドを持ちませんが `sfContoroller` が代行するので、つぎのコードを呼び出すことで簡単に実現できます:

    [php]
    return $this->getContext()->getController()->forward('mymodule', 'myAction');

>**NOTE**
>`sfFilter` クラスは `initialize()` メソッドを持ち、このメソッドはフィルターオブジェクトが作られたときに実行されます。独自の方法でフィルターパラメーター(次で説明されるように `filters.yml` ファイルで定義される)を処理する必要がある場合、カスタムフィルター内でそのメソッドをオーバーライドできます。

### フィルターの有効化とパラメーター

フィルターファイルを有効にするには作成するだけでは不十分です。フィルターをフィルターチェーンに追加する必要があります。そのためには、リスト6-30で示されるように、アプリケーションもしくはモジュールの `config/` ディレクトリに設置される、`filteres.yml` のなかでフィルタークラスを宣言しなければなりません。

リスト6-30 - フィルターのサンプルを有効にするファイル (`apps/frontend/config/filters.yml`)

    rendering: ~
    security:  ~

    remember:                 # フィルターは独自の名前が必要
      class: rememberFilter
      param:
        cookie_name: MyWebSite
        condition:   %APP_ENABLE_REMEMBER_ME%

    cache:     ~
    execution: ~

有効にされたとき、フィルターはそれぞれのリクエストに対して実行されます。フィルターの設定ファイルは `param` キーのもとで1つもしくは複数のパラメーターの定義を持ちます。フィルタークラスは `getParameter()` メソッドを使ってこれらのパラメーターの値を得る機能を持ちます。リスト6-31ではフィルターパラメーターの値を得る方法を示しています。

リスト6-31 - パラメーターの値を得る (`apps/frontend/lib/rememberFilter.class.php`)

    [php]
    class rememberFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        // ...

        if ($request->getCookie($this->getParameter('cookie_name')))
        {
          // ...
        }

        // ...
      }
    }

フィルターを実行しなければならないのかを確かめるために `condition` パラメーターはフィルターチェーンによってテストされます。リスト6-32のように、フィルターの宣言はアプリケーションの設定に依存する可能性があります。remember フィルターはアプリケーションの `app.yml` がつぎの内容を示す場合のみ実行されます:

    all:
      enable_remember_me: true

### サンプルのフィルター

フィルター機能はすべてのアクションに対してコードを繰り返すために便利です。たとえば、外部の分析システムを利用する場合、おそらくは外部のトラッカースクリプトを呼び出すコードスニペットをすべてのページに設置することが必要です。このコードをグローバルレイアウトに設置できますが、すべてのアプリケーションに対して有効になってしまいます。代わりの方法として、リスト6-32で示されるように、コードをフィルターのなかで設置してモジュール単位で有効にできます。

リスト6-32 - Google Analytics のフィルター

    [php]
    class sfGoogleAnalyticsFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        // アクションのまえは何もしない
        $filterChain->execute();

        // トラッカーコードでレスポンスをデコレートする
        $googleCode = '
    <script src="http://www.google-analytics.com/urchin.js"  type="text/javascript">
    </script>
    <script type="text/javascript">
      _uacct="UA-'.$this->getParameter('google_id').'";urchinTracker();
    </script>';
        $response = $this->getContext()->getResponse();
        $response->setContent(str_ireplace('</body>', $googleCode.'</body>',$response->getContent()));
       }
    }

フィルターは HTML ではないレスポンスにトラッカーを追加しないので、フィルターは完全なものではないことに注意してください。

別の例は、リクエストを SSL に切り替えるフィルターです。リスト6-33で示されるように、このフィルターはコミュニケーションを安全にするために、まだ SSL に切り替えられていない場合に切り替えが行われます。

リスト6-33 - セキュアなコミュニケーションフィルター

    [php]
    class sfSecureFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        $context = $this->getContext();
        $request = $context->getRequest();

        if (!$request->isSecure())
        {
          $secure_url = str_replace('http', 'https', $request->getUri());

          return $context->getController()->redirect($secure_url);
          // フィルターチェーンを継続しない
        }
        else
        {
          // すでにリクエストはセキュアなので、続けることができる
          $filterChain->execute();
        }
      }
    }

フィルターはアプリケーションの機能をグローバルに拡張できるので、フィルターはプラグインで広く使われます。プラグインを詳しく学ぶには17章を参照してください。

モジュールの設定
----------------

モジュールのふるまいのいくつかは設定に依存します。修正するには、モジュールの `config/` ディレクトリに `module.yml` ファイルを作り、環境ごとに (もしくはすべての環境: `all: `ヘッダー) 設定を定義しなければなりません。リスト6-34は `mymodule` モジュールのための `module.yml` の例を示します。

リスト6-34 - モジュール設定 (`apps/frontend/modules/mymodule/config/module.yml`)

    all:                  # すべての環境用
      enabled:            true
      is_internal:        false
      view_class:         sfPHP
      partial_view_class: sf

`enabled` パラメーターによってモジュールのすべてのアクションを無効にできます。すべてのアクションは `module_disabled_module/module_disabled_action`ア クションにリダイレクトされます (`settings.yml`で定義)。

`is_internal` パラメーターによってモジュールのすべてのアクションの実行を内部呼び出しに制限できます。この機能は、たとえば、Eメールのメッセージを、外部からではなく、内部から送るために、別のアクションから呼び出さなければならないメールアクションに対して便利です。

`view_name` パラメーターはビュークラスを定義します。このパラメーターは `sfView` から継承しなければなりません。この値をオーバーライドすることで Smarty などのほかのテンプレートシステムによるビューシステムを利用できるようになります。

まとめ
----

symfony において、コントロールレイヤーは2つの部分 (フロントコントローラーとアクション) に分割されます。フロントコントローラー (front controller) は任意の環境、ページロジックを格納するアクションのためのアプリケーションへの唯一のエントリーポイント (entry point - 入り口) です。アクション (action) はページロジックを格納します。アクションは `sfView` 定数の1つを返すことでビューが実行される方法を決める機能を持ちます。アクションの内部では、リクエストオブジェクト (`sfRequest`) と現在のユーザーセッションオブジェクト (`sfUser`) を含む、コンテキストの異なる要素を操作することができます。

セッションオブジェクト、アクションオブジェクトとセキュリティの設定の組み合わせることで、symfony はアクセス制限と権限を設定できる完全なセキュリティーシステムを提供することができます。
そして `preExecute()` と `postExecute()` メソッドがモジュール内部のコードの再利用のために役立つ場合、フィルター (filter) は、コントローラーのコードをリクエストごとに実行することで、すべてのアプリケーションに対して同じ再利用性を公認します。
