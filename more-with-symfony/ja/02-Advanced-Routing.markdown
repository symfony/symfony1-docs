進化したルーティング
===================

*Ryan Weaver 著*

symfony のルーティングフレームワークの中心となるのは、個別の URL とプロジェクトで使う特定のロケーションとを双方向に結びつけるマップです。ルーティングフレームワークを使うと、アプリケーションロジックとは完全に独立しながら、簡単にきれいな URL を生成できます。最新の symfony ではより高度なことができるようになっており、ルーティングフレームワークはさらに先を進んでいます。

この章では、各クライアントが別々のサブドメインで使えるシンプルな Web アプリケーションの作り方を説明していきます(例: `client1.mydomain.com` と `client2.mydomain.com`)。ルーティングフレームワークを拡張すると、このようなアプリケーションを簡単に構築できます。

>**NOTE**
>この章では、プロジェクトの ORM として Doctrine を使って説明します。

プロジェクトセットアップ: 多くのクライアントのための CMS
-----------------------------------------------------

このプロジェクトでは架空の会社 - Sympal Builder - の CMS を開発し、クライアントが `sympalbuilder.com` のサブドメインを使って Web サイトを構築できるようにします。具体的には、クライアント XXX のサイトは `xxx.sympalbuilder.com` で閲覧でき、管理者ページは `xxx.sympalbuilder.com/backend.php` から使えるようにします。

>**NOTE**
>`Sympal` という名前は Jonathan Wage の [Sympal](http://www.sympalphp.org/) からお借りしました。Sympal は symfony を使って作られたコンテンツマネージメントフレームワーク (CMF) です。

このプロジェクトには 2 つの基本要件があります。

  * ユーザーがページを作成でき、各ページのタイトル、コンテンツ、URL を指定できる必要があります。
  
  * アプリケーション全体を 1 つの symfony プロジェクトとして構築し、このプロジェクトですべてのクライアントサイトのフロントエンド、およびバックエンドを処理するようにします。また、サブドメインによりクライアントを判別し、正しいデータを読み込む必要があります。


>**NOTE**
>このアプリケーションを作成するため、サーバーは全ての `*.sympalbuilder.com` サブドメインが同じドキュメントルートを指すようセットアップする必要があります。ドキュメントルートは symfony プロジェクトの web ディレクトリです。

### スキーマとデータ

本プロジェクト向けのデータベースは `Client` と `Page` オブジェクトから構成されます。各 `Client` オブジェクトは1つのサブドメインサイトを意味し、それぞれが多数の `Page` オブジェクトで構成されます。

    [yml]
    # config/doctrine/schema.yml
    Client:
      columns:
        name:       string(255)
        subdomain:  string(50)
      indexes:
        subdomain_index:
          fields:   [subdomain]
          type:     unique

    Page:
      columns:
        title:      string(255)
        slug:       string(255)
        content:    clob
        client_id:  integer
      relations:
        Client:
          alias:        Client
          foreignAlias: Pages
          onDelete:     CASCADE
      indexes:
        slug_index:
          fields:   [slug, client_id]
          type:     unique

>**NOTE**
>各テーブルごとのインデックスは必須ではありませんが、アプリケーションでこれらのカラムに基づいたクエリを頻繁に使うのであれば、インデックスを設定しておくとよいでしょう。

プロジェクトに命を吹き込むため、下記のテストデータを `data/fixtures/fixtures.yml` ファイル内に記述します:

    [yml]
    # data/fixtures/fixtures.yml
    Client:
      client_pete:
        name:      Pete's Pet Shop
        subdomain: pete
      client_pub:
        name:      City Pub and Grill
        subdomain: citypub

    Page:
      page_pete_location_hours:
        title:     Location and Hours | Pete's Pet Shop
        content:   We're open Mon - Sat, 8 am - 7pm
        slug:      location
        Client:    client_pete
      page_pub_menu:
        title:     City Pub And Grill | Menu
        content:   Our menu consists of fish, Steak, salads, and more.
        slug:      menu
        Client:    client_pub

このテストデータにより、初期状態としてページが 1 つだけある Web サイトが 2 つ作られます。各ページのフル URL は、`Client` オブジェクトの `subdomain` カラムと `Page` オブジェクトの `slug` カラムの両方を使って定義されます。

    http://pete.sympalbuilder.com/location
    http://citypub.sympalbuilder.com/menu

### ルーティング

Sympal Builder の Web サイトの各ページは `Page` モデルオブジェクトと直接対応しており、出力するタイトルとコンテンツが `Page` モデルオブジェクトで定義されています。各 URL を `Page` オブジェクトへ厳密に関連付けるために、`slug` フィールドを使う `sfDoctrineRoute` というタイプのオブジェクトルートを作ります。次のコードで、URL にマッチする `slug` フィールドのある `Page` オブジェクトをデータベースから自動的に検索します:

    [yml]
    # apps/frontend/config/routing.yml
    page_show:
      url:        /:slug
      class:      sfDoctrineRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show
        
上記のルートにより、`http://pete.sympalbuilder.com/location` という URL のページについては正しい `Page` オブジェクトにマッチします。しかし、このルートは `http://pete.sympalbuilder.com/menu` という間違った URL でもマッチしてしまいます。これでは、レストランのメニューが Pete の Web サイトでも表示されてしまう、ということを意味します。現時点では、このルートはクライアントのサブドメインを考慮していないからです。

アプリケーションを正しく動作させるために、ルートでサブドメインを正しく判定する必要があります。つまり、ルートが `cliend_id` と `slug` の両方を加味した正しい `Page` オブジェクトにマッチしなければいけません。ここで `cliend_id` は、ホスト (例 `pete.sympalbuilder.com`) を `Client` モデルオブジェクトの `subdomain` カラムと照合して決定します。このような機能を実現するためにルーティングフレームワークを活用し、カスタムルーティングクラスを作ります。

このような実装を行う前に、ルーティングシステムの動作についての理解を深めておきましょう。

ルーティングシステムの動作の仕組み
---------------------------------

symfony において"ルート"とは `sfRoute` オブジェクトのことであり、このオブジェクトは次の 2 つの重要な仕事を担当します:

 * URL の生成 : 例えば、`page_show` というルートに `slug` パラメータを渡すと、実際のURLを生成できます (例 `/location`)。
 
 * 受け取った URL のマッチング : 受け取ったリクエストの URL に対して、各ルートはそのURLが自身の条件にマッチするかどうかを決定します。

通常、個々のルートの情報は、各アプリケーションのコンフィグディレクトリにある `app/yourappname/config/routing.yml` ファイルでセットアップします。ここで*各ルートは `sfRoute` オブジェクトである*と繰り返し言っておきます。これらのシンプルな YAML エントリーが、どのように処理されて `sfRoute` オブジェクトになるのでしょうか?

### ルーティングのキャッシュコンフィグハンドラー

実際、ほとんどのルートは YAML ファイルで定義しますが、YAML ファイルの各エントリーはキャッシュコンフィグハンドラーという特別なクラスにより、リクエスト時に実際のオブジェクトに変換されます。これは最終的に、アプリケーション内のすべてのルートをあらわす PHP コードになります。この処理の詳細は本章では取り上げませんが、`page_show` ルートのコンパイルされたバージョンが最終的にどのようになっているのか見ておきましょう。コンパイルされたファイルは、 アプリケーションと環境ごとに `cache/yourappname/envname/config/config_routing_yml.php` に保存されます。`page_show` ルートの部分のみを切り出すと、次のようになっています:

    [php]
    new sfDoctrineRoute('/:slug', array (
      'module' => 'page',
      'action' => 'show',
    ), array (
      'slug' => '[^/\\.]+',
    ), array (
      'model' => 'Page',
      'type' => 'object',
    ));

>**TIP**
>各ルートのクラス名は、`routing.yml` ファイルにて `class` キーで定義します。`class` キーが指定されていない場合は、デフォルトで `sfRoute` クラスのルートになります。よく使われる他のルートクラスとしては、RESTful なルートを実装できる `sfRequestRoute` クラスがあります。すべてのルートクラスと利用可能なオプションの一覧については、[symfony リファレンスブック](http://www.symfony-project.org/reference/1_3/en/10-Routing)を参照してください。

### 特定ルートに対するリクエストのマッチング

ルーティングフレームワークの主な仕事の 1 つは、リクエストされた各 URL を正しいルートオブジェクトとマッチングさせることです。このようなルーティングエンジンの中心は `sfPatternRouting` クラスで、マッチング処理を担当します。このように `sfPatternRouting` はとても重要なクラスですが、開発者が直接操作することはほとんどありません。

リクエストされた URL を正しいルートにマッチングさせるために、`sfPatternRouting` は各 `sfRoute` を順に処理し、URL にマッチするかどうかを各ルートに問い合わせます。この処理の内部では、`sfPatternRouting` により各ルートオブジェクトの ~`sfRoute::matchsUrl()`~ メソッドが呼び出されています。ルートがリクエストされた URL にマッチしない場合は、このメソッドは単純に `false` を返します。

ルートがリクエストされた URL に*マッチする*場合は、`sfRoute::matchsUrl()` は単純に `true` を返すのではなく、リクエストオブジェクトにマージされるパラメータ配列を返します。例えば、`http://pete.sympalbuilder.com/location` という URL は `page_show` ルートにマッチし、`matchsUrl()` メソッドは次のような配列を返します:

    [php]
    array('slug' => 'location')

この情報はリクエストオブジェクトにマージされるので、アクションファイルやその他の場所からルートの変数 (例 `slug`) にアクセスすることが可能となります。

    [php]
    $this->slug = $request->getParameter('slug');

ここで気づかれた方もいらっしゃるかと思いますが、何らかの目的でルートを拡張したりカスタマイズするには、`sfRoute::matchsUrl()` メソッドをオーバーライドすると良いでしょう。

カスタムルートクラスの作成
--------------------------

`Client` オブジェクトのサブドメインに基づいてマッチングするように `page_show` ルートを拡張するために、新しいカスタムルートクラスを作ります。次の内容で `acClientObjectRoute.class.php` という名前のファイルを作り、プロジェクトの `lib/routing` ディレクトリに配置します (ディレクトリを作る必要があります):

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        return $parameters;
      }
    }

他に必要なことは、`page_show` ルートでこのルートクラスを使うように設定するだけです。`routing.yml` ファイルで、次のようにルートの `class` キーを変更します:

    [yml]
    # apps/fo/config/routing.yml
    page_show:
      url:        /:slug
      class:      acClientObjectRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show

今までのところ、`acClientObjectRoute` クラスには何の機能も追加していませんが、全ての構成要素は準備が整っています。`matchsUrl()` メソッドに追加する 2 つの処理については、次の節で説明します。

### カスタムルートクラスへのロジックの追加

必要な機能をカスタムルートクラスに追加するには、`acClientObjectRoute.class.php` ファイルの中身を次のコードに置き換えます:

    [php]
    class acClientObjectRoute extends sfDoctrineRoute
    {
      protected $baseHost = '.sympalbuilder.com';

      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        // baseHost が見つからない場合は false を返す
        if (strpos($context['host'], $this->baseHost) === false)
        {
          return false;
        }

        $subdomain = str_replace($this->baseHost, '', $context['host']);

        $client = Doctrine_Core::getTable('Client')
          ->findOneBySubdomain($subdomain)
        ;

        if (!$client)
        {
          return false;
        }

        return array_merge(array('client_id' => $client->id), $parameters);
      }
    }

最初に呼び出している `parent::matchesUrl()` により、通常のルートマッチング処理が実行されることに注意してください。この例では、`/location` という URL が `page_show` ルートにマッチするので、マッチした `slug` パラメータを含む配列が `parent::matchesUrl()` から返されます。

    [php]
    array('slug' => 'location')

つまり、ルートマッチングの共通処理はこれで完了しており、メソッドの残りの部分では、正しい `Client` サブドメインに基づくマッチング処理にフォーカスできます。

    [php]
    public function matchesUrl($url, $context = array())
    {
      // ...

      $subdomain = str_replace($this->baseHost, '', $context['host']);

      $client = Doctrine_Core::getTable('Client')
        ->findOneBySubdomain($subdomain)
      ;

      if (!$client)
      {
        return false;
      }

      return array_merge(array('client_id' => $client->id), $parameters);
    }

単純な文字列置換を実行することでホストのサブドメイン部を分離することができ、このサブドメインを持つ `Client` オブジェクトがあるかどうかをデータベースに問い合わせることができます。サブドメインにマッチする `Client` オブジェクトが見つからない場合は `false` を返し、これはリクエストされた URL がこのルートにマッチしないことを示します。リクエストされた URL のサブドメインを持った `Client` オブジェクトが存在する場合は、パラメーター配列に追加のパラメーター `client_id` をマージして返します。

>**TIP**
>`matchesUrl()` に渡される配列 `$context` には、現在のリクエストに関する多くの役立つ情報が事前にセットされています。たとえば `host` やブール値の `is_secure`、 `request_uri`、および HTTP メソッドの `method` などがあります。

ところで、実際カスタムルートクラスにはどういった処理を実装したのでしょうか?`acClientObjectRoute` クラスは次の処理を実行します:

 * リクエストされた `$url` の `host` が、`Client` オブジェクトの1つが持っているサブドメインを含む場合にのみマッチします。
 
 * ルートがマッチした場合、マッチした `Client` オブジェクトの `client_id` を追加パラメータとして返し、リクエストパラメータに結合されます。

### カスタムルートクラスの利用

`acClientObjectRoute` によって正しい `client_id` パラメータが返されるので、リクエストオブジェクトから `client_id` パラメータにアクセスできます。たとえば、`page/show` アクションで `client_id` を使って、正しい `Page` オブジェクトを次のように取得できます:

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = Doctrine_Core::getTable('Page')->findOneBySlugAndClientId(
        $request->getParameter('slug'),
        $request->getParameter('client_id')
      );

      $this->forward404Unless($this->page);
    }

>**NOTE**
>`findOneBySlugAndClientId()` メソッドは Doctrine 1.2 から使えるようになった[マジックファインダー](http://www.doctrine-project.org/upgrade/1_2#Expanded%20Magic%20Finders%20to%20Multiple%20Fields)タイプのメソッドで、複数のフィールドに基づきオブジェクトを問い合わせます。

上で示したやり方でも問題ありませんが、ルーティングフレームワークを使ったより洗練された解決法があります。まず最初に、`acClientObjectRoute` クラスに次のメソッドを追加します:

    [php]
    protected function getRealVariables()
    {
      return array_merge(array('client_id'), parent::getRealVariables());
    }

次に示す最後のコードは、アクションから正しい `Page` オブジェクトを返す処理を完全にルートに任せています。`page/show` アクションは 1 行だけになりました:

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = $this->getRoute()->getObject();
    }

これ以上の処理を追加しなくても、上記のコードで `slug` と `client_id` の両方のカラムに基づいて `Page` オブジェクトを問い合わせます。さらに、他のオブジェクトルートと同じように、一致するオブジェクトが無ければアクションは自動的に 404 ページにフォワードされます。

しかしどうやってこのような動作をするのでしょうか？`acClientObjectRoute` の継承元である `sfDoctrineRoute` のようなオブジェクトルートは、ルートの `url` キーにある変数に基づいて関連するオブジェクトを自動的に問い合わせます。例えば、`page_show` ルートには `url` キーの中に `:slug` 変数があるので、`slug` カラムを使って `Page` オブジェクトを問い合わせます。

しかしながらこのアプリケーションでは、`page_show` ルートで `Page` オブジェクトを問い合わせる際に、`client_id` カラムも加味する必要があります。これを実行するため、~`sfObjectRoute::getRealVariables()`~ をオーバーライドしました。このメソッドは内部で呼び出され、オブジェクトの問い合わせに使うカラムを決定します。このメソッドが返す配列に `client_id` フィールドを追加することで、`acClientObjectRoute` は `slug` と `client_id` の両方のカラムに基づいてオブジェクトを問い合わせます。

>**NOTE**
>オブジェクトルートでは、実在するカラムに一致しない変数は自動的に無視されます。たとえば、URL のキーに `:page` という変数が含まれていても、対応するテーブルに `page` カラムが存在しない場合、この変数は無視されます。

現時点では、カスタムルートクラスはほんの少しの努力で必要とされる全ての機能を達成しています。以降のセクションでは、新しいルートを再利用してクライアント固有の管理エリアを作っていきます。

### 正しい URL の生成

ルートの URL を生成すると、問題があることが分かります。次のコードのようにページへのリンクを作ったとしましょう:

    [php]
    <?php echo link_to('Locations', 'page_show', $page) ?>

-

    生成された URL : /location?client_id=1

このように、URL に自動的に `client_id` が追加されています。ルートで URL を生成する際に、利用可能なルートの変数をすべて使うからです。このルートでは `slug` パラメーターと `client_id` パラメーターの両方を扱うようにしたので、URL を生成するときにも両方が使われてしまいます。

この処理を修正するため、`acClientObjectRoute` クラスに次のメソッドを追加します:

    [php]
    protected function doConvertObjectToArray($object)
    {
      $parameters = parent::doConvertObjectToArray($object);

      unset($parameters['client_id']);

      return $parameters;
    }

オブジェクトルートで URL を生成するとき、`doConvertObjectArray()` メソッドを呼び出して必要な全ての情報を取得しようと試みます。デフォルトでは、`$parameter` 配列で `client_id` も返すようになっています。この `cliend_id` を配列から破棄することで、生成される URL から client_id を除外できます。`Client` を識別する情報は URL のサブドメインであるということに注意してください。

>**TIP**
>`doConvertObjectToArray()` プロセス全体をオーバーライドし、モデルクラスに `toParams()` メソッドを追加することで、この処理を完全にカスタマイズできます。`toParams()` メソッドは、ルートでの URL 生成に使うパラメーターの配列を返すようにします。

ルートコレクション
------------------

Sympal Builder のアプリケーションを仕上げるため、それぞれの `Client` が自身の `Page` 群を管理できるような管理エリアを作成する必要があります。管理エリアには、`Page` オブジェクトの一覧表示、作成、更新、削除ができる一連のアクションが必要です。このような処理を行うモジュールは頻繁に使われるため、symfony ではモジュールを自動生成できます。コマンドラインから次のタスクを実行すると、`backend` と呼ばれるアプリケーション内に `pageAdmin` モジュールが生成されます:

    $ php symfony doctrine:generate-module backend pageAdmin Page --with-doctrine-route --with-show

上記のタスクでは、任意の `Page` オブジェクトの変更に必要なアクションファイルや関連するテンプレートを含むモジュールが生成されます。生成された CRUD の大部分はカスタマイズ可能ですが、カスタマイズの詳細については本章では扱いません。

上記のタスクで必要なモジュールは準備できていますが、まだ各アクションごとにルートを作成する必要があります。タスクに `--with-doctrine-route` オプションを指定しているので、各アクションはオブジェクトルートを使うように生成されています。こうすると、各アクション内のコード量を減少します。たとえば、`edit` アクションは次のように 1 行のみで構成されます:

    [php]
    public function executeEdit(sfWebRequest $request)
    {
      $this->form = new PageForm($this->getRoute()->getObject());
    }

全体で必要なルートは `index`、`new`、`create`、`edit`、`update`、`delete` アクションです。通常、[RESTful](http://en.wikipedia.org/wiki/Representational_State_Transfer) の様式でこれらのルートを生成するには、次のように `routing.yml` に多くのセットアップ項目が必要になります。

    [yml]
    pageAdmin:
      url:         /pages
      class:       sfDoctrineRoute
      options:     { model: Page, type: list }
      params:      { module: page, action: index }
      requirements:
        sf_method: [get]
    pageAdmin_new:
      url:        /pages/new
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: new }
      requirements:
        sf_method: [get]
    pageAdmin_create:
      url:        /pages
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: create }
      requirements:
        sf_method: [post]
    pageAdmin_edit:
      url:        /pages/:id/edit
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: edit }
      requirements:
        sf_method: [get]
    pageAdmin_update:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: update }
      requirements:
        sf_method: [put]
    pageAdmin_delete:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: delete }
      requirements:
        sf_method: [delete]
    pageAdmin_show:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: show }
      requirements:
        sf_method: [get]

これらのルートを確認するには `app:routes` タスクを使います。このタスクを実行すると、特定のアプリケーションにおけるすべてのルートの概要が表示されます:

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages
    pageAdmin_new    GET    /pages/new
    pageAdmin_create POST   /pages
    pageAdmin_edit   GET    /pages/:id/edit
    pageAdmin_update PUT    /pages/:id
    pageAdmin_delete DELETE /pages/:id
    pageAdmin_show   GET    /pages/:id

### ルートをルートコレクションに置き換える

symfony では、このような CRUD で使うすべてのルートをとても簡単に指定できます。さきほどの `routing.yml` の内容は、次のようにたった 1 つのルートに置き換えることができます:

    [yml]
    pageAdmin:
      class:   sfDoctrineRouteCollection
      options:
        model:        Page
        prefix_path:  /pages
        module:       pageAdmin
        
再度 `app:routes` タスクを実行して、すべてのルートを表示してみてください。先ほどの 7 つルートがすべて存在しています。

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages.:sf_format
    pageAdmin_new    GET    /pages/new.:sf_format
    pageAdmin_create POST   /pages.:sf_format
    pageAdmin_edit   GET    /pages/:id/edit.:sf_format
    pageAdmin_update PUT    /pages/:id.:sf_format
    pageAdmin_delete DELETE /pages/:id.:sf_format
    pageAdmin_show   GET    /pages/:id.:sf_format

ルートコレクションは特別な種類のルートオブジェクトで、内部では複数のルートをあらわします。たとえば `sfDoctrineRouteCollection` ルートでは、CRUD に必要なよく使われる 7 つのルートが自動生成されます。`sfDoctrineRouteCollection` の内部では、`routing.yml` に手作業で指定していた 7 つのルートと同じルートを作っているだけです。基本的には、ルートコレクションはルートの共通グループを作成するためのショートカットです。

カスタムルートコレクションの作成
-------------------------------

現時点で、各 `Client` は `/pages` URL からアクセスできる CRUD を使って `Page` オブジェクトを編集できます。しかし、`Page` オブジェクトが `Client` に関連付けられたものであるかどうかに関わらず、各 `Client` は*すべての* `Page` オブジェクトを閲覧し編集できてしまいます。たとえば `http://pete.sympalbuilder.com/backend.php/pages` という URL にアクセスすると、fixtures に記述した*両方*のページ、つまり Pete's ペットショップの `location` ページと City Pub の `menu` ページが一覧表示されます。

この処理を修正するため、フロントエンド用に作った `acClientObjectRoute` を再利用します。`sfDoctrineRouteCollection` クラスは、`sfDoctrineRoute` オブジェクトのグループを生成します。このアプリケーションでは、代わりに `acClientObjectRoute` オブジェクトのグループを生成する必要があります。

このためには、カスタムルートコレクションクラスを使う必要があります。新しく `acClientObjectRouteCollection.class.php` という名前のファイルを作り、`lib/routing` ディレクトリに配置します。作成したファイルは信じられないくらいシンプルな構成です:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    class acClientObjectRouteCollection extends sfObjectRouteCollection
    {
      protected
        $routeClass = 'acClientObjectRoute';
    }

`$routeClass` プロパティで、コレクションの各ルートの生成に使われるクラスを定義します。こうすると、コレクションで生成する各ルーティングは `acClientObjectRoute` ルートになるので、必要な変更作業は完了しました。例えば、`http://pete.sympalbuilder.com/backend.php/pages` という URL にアクセスすると、一覧には Pete's ペットショップの `location` ページのみが表示されます。カスタムルートクラスのおかげで、インデックスアクションでは、リクエストのサブドメインに対応する `Client` に関連付けられた `Page` オブジェクトのみが返されます。数行のコードだけで、複数のクライアントが安全に使えるバックエンドモジュール全体を作成できました。

### 足りない要素: 新規ページの生成

現在は、バックエンドで `Page` オブジェクトを作成または編集するとき、`Client` の選択ボックスが表示されます。ユーザーに `Client` を選択させるのはセキュリティリスクとなるため、現在のリクエストのサブドメインに応じた `Client` を自動でセットするようにしましょう。

まず最初に、`lib/form/PageForm.class.php` にある `PageForm` オブジェクトを変更します。

    [php]
    public function configure()
    {
      $this->useFields(array(
        'title',
        'content',
      ));
    }

これで、意図したとおりセレクトボックスは `Page` フォームに表示されなくなりました。しかし、新しい `Page` オブジェクトを作成しても、`client_id` はセットされません。これを修正するため、`new` アクションと `create` アクション内で関連する `Client` を手作業でセットします。

    [php]
    public function executeNew(sfWebRequest $request)
    {
      $page = new Page();
      $page->Client = $this->getRoute()->getClient();
      $this->form = new PageForm($page);
    }

上のコードには新しいメソッド `getClient()` があります。このメソッドは `acClientObjectRoute` クラスにまだ存在していません。以前のコードに多少の変更を加え、このメソッドをクラスに追加しましょう:

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      // ...

      protected $client = null;

      public function matchesUrl($url, $context = array())
      {
        // ...

        $this->client = $client;

        return array_merge(array('client_id' => $client->id), $parameters);
      }

      public function getClient()
      {
        return $this->client;
      }
    }

`$client` クラスプロパティを追加し、`matchesUrl()` メソッド内でこの値をセットしています。こうすると、ルートから簡単に `Client` オブジェクトを利用できるようになります。新しい `Page` オブジェクトの `client_id` カラムには、現在のホストのサブドメインに対応する値が自動的にセットされます。

オブジェクトルートコレクションのカスタマイズ
------------------------------------------

ルーティングフレームワークを使うと、Sympal Builder アプリケーションの構築における問題を簡単に解決できます。アプリケーションが大きくなった場合、今回作成したカスタムルートをバックエンドエリアの他のモジュールで再利用できるでしょう (例 `Client` ごとに写真ギャラリーを管理する)。

カスタムルートコレクションを作るもう1つのよくある理由は、頻繁に使われるルートの追加です。たとえば、`is_active` カラムのあるモデルがプロジェクトに多数あると仮定します。管理エリアでは、これらのモデルの特定のオブジェクトの `is_active` の値を簡単にトグルするような方法が必要となります。最初に、`acClientObjectRouteCollection` を変更してコレクションに新しいルートを追加するようにします:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    protected function generateRoutes()
    {
      parent::generateRoutes();

      if (isset($this->options['with_is_active']) && $this->options['with_is_active'])
      {
        $routeName = $this->options['name'].'_toggleActive';

        $this->routes[$routeName] = $this->getRouteForToggleActive();
      }
    }

~`sfObjectRouteCollection::generateRoutes()`~ メソッドはコレクションオブジェクトがインスタンス化されるときに呼び出され、必要なすべてのルートを生成してクラスプロパティの `$routes` 配列へ追加します。このケースでは、実際のルートの生成処理は、次の `getRouteForToggleActive()` という新しい protected メソッドにまかせています:

    [php]
    protected function getRouteForToggleActive()
    {
      $url = sprintf(
        '%s/:%s/toggleActive.:sf_format',
        $this->options['prefix_path'],
        $this->options['column']
      );

      $params = array(
        'module' => $this->options['module'],
        'action' => 'toggleActive',
        'sf_format' => 'html'
      );

      $requirements = array('sf_method' => 'put');

      $options = array(
        'model' => $this->options['model'],
        'type' => 'object',
        'method' => $this->options['model_methods']['object']
      );

      return new $this->routeClass(
        $url,
        $params,
        $requirements,
        $options
      );
    }

残りのステップは、`routing.yml` 内でルートコレクションをセットアップするだけです。新しいルートを追加する前に、`generateRoutes()` メソッドで `with_is_active` という名前のオプションをチェックしていることに注意してください。このようなロジックを追加しておくと、将来的に `acClientObjectRouteCollection` を使いたいが `toggleActive` ルートを必要としない場合に制御しやすくなります:

    [yml]
    # apps/frontend/config/routing.yml
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        model:          Page
        prefix_path:    /pages
        module:         pageAdmin
        with_is_active: true

`app:routes` タスクを実行して、新しい `toggleActive` ルートが存在していることを確認しましょう。残っている作業は、実際に処理を行うアクションを作ることだけです。このルートコレクションと対応するアクションを複数のモジュールで使うため、新しく次の内容の `backendActions.class.php` ファイルを `apps/backend/lib/action` ディレクトリに作ります (ディレクトリを作る必要があります):

    [php]
    # apps/backend/lib/action/backendActions.class.php
    class backendActions extends sfActions
    {
      public function executeToggleActive(sfWebRequest $request)
      {
        $obj = $this->getRoute()->getObject();

        $obj->is_active = !$obj->is_active;

        $obj->save();

        $this->redirect($this->getModuleName().'/index');
      }
    }

最後に、新しい `backendActions` クラスを継承するように `pageAdminActions` クラスの基底クラスを 変更します。

    [php]
    class pageAdminActions extends backendActions
    {
      // ...
    }

この作業でどのようなことを行ったのでしょうか？ルートコレクションにルートを追加し、関連するアクションを基本アクションファイルに追加することで、`acClientObjectRouteCollection` を使い、`backendActions` を継承するだけで、任意の新しいモジュールでこれらの機能を使えます。このようにして、共通機能を多くのモジュールで簡単に共有できます。

ルートコレクションのオプション
------------------------------

オブジェクトルートコレクションには、高度にカスタマイズするための一連のオプションがあります。多くの場合、新しいルートコレクションクラスを作るのではなく、これらのオプションを使ってコレクションを設定できます。ルートコレクションのオプションの詳細な一覧は [symfony リファレンスブック](http://www.symfony-project.org/reference/1_3/en/10-Routing#chapter_10_sfobjectroutecollection)を参照してください。

### アクションルート

各オブジェクトルートコレクションには、コレクションで生成されるルートを厳密に定義するための 3 つのオプションがあります。詳細な説明は省きますが、次に示すコレクションによりデフォルトの 7 つのルートと、追加のコレクションルート、オブジェクトルートが生成されます:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        actions:      [list, new, create, edit, update, delete, show]
        collection_actions:
          indexAlt:   [get]
        object_actions:
          toggle:     [put]

### カラム

デフォルトでは、生成されたすべての URL、およびオブジェクトの問い合わせにモデルのプライマリーキーが使われます。もちろん、このルールは簡単に変更できます。たとえば、次のコードではプライマリーキーの代わりに `slug` カラムを使います:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        column: slug

### モデルメソッド

デフォルトでは、ルートはコレクションルートに関連するすべてのオブジェクトを取得し、オブジェクトルートに指定された `culumn` で問い合わせます。この処理を書き換える必要がある場合は、ルートに `model_methods` オプションを追加します。この例では、`PageTable` クラスに `fetchAll()` メソッドと `findForRoute` メソッドを追加する必要があります。これらのメソッドは、引数としてリクエストパラメータの配列を受け取ります:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        model_methods:
          list:       fetchAll
          object:     findForRoute

### デフォルトパラメータ

最後に、コレクションの各ルートに対して、リクエストで利用できる特定のリクエストパラメータを作る必要があると仮定します。これは `default_params` オプションを使えば簡単です:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        default_params:
          foo:   bar


最後に
------

従来は URL のマッチングと生成を行うだけだったルーティングフレームワークは、プロジェクトにおける複雑な URL 要件のほとんどをカバーできる、完全にカスタマイズ可能なシステムに進化しました。ルートオブジェクトを制御することで、特殊な URL 構造をビジネスロジックから分離し、ルートの中で完結させることができます。その結果、より制御しやすく、柔軟性があり、管理しやすいコードになるのです。
