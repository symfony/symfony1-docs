第14章 - Admin ジェネレーター
=============================

多くのアプリケーションはデータベースに保存されたデータに基づいており、それにアクセスするためのインターフェイスを提供します。symfony は Propel または Doctrine オブジェクトに基づいたデータ操作機能を提供するモジュール作成の反復タスクを自動化します。オブジェクトモデルが適切に定義したのであれば、symfony はサイト全体の管理機能 (アドミニストレーション) も自動的に生成します。この章では、Propel プラグインと Doctrine プラグインに搭載された Admin ジェネレーターをお教えします。これは完全な構文による特別な設定ファイルに依存するので、この章の多くは Admin ジェネレーターのさまざまな可能性について説明します。

モデルをもとにコードを生成する
------------------------------

Webアプリケーションにおいて、データアクセスオペレーションはつぎのように分類できます:

  * レコードの作成
  * レコードの検索
  * レコードの更新(とカラムの修正)
  * レコードの削除

これらのオペレーションは共通なので、頭文字をとった専用の略語である CRUD (Create、Retrieval、Update、Deletion) が存在します。多くのページはこれらの1つに還元できます。たとえばフォーラムのアプリケーションにおいて、最新投稿のリストは検索オペレーション (retrieve) で、投稿への返答は作成オペレーション (create) に対応します。

任意のテーブルに対する CRUD オペレーションを実装する基本的なアクションとテンプレートは Web アプリケーション内部で繰り返し作られます。symfony において、バックエンドインターフェイスの初期開発を加速するために、モデルレイヤーは CRUD オペレーションを生成できるようにするための十分な情報を持ちます。

### データモデルの例

この章全体を通して、一覧表示機能はシンプルな例に基づいた symfony の Admin ジェネレーターの機能を示します。これによって8章を思い出すでしょう。これは2つの `BlogArticle` クラスと `BlogComment` クラスを含む、ブログアプリケーションのよく知られた例です。図14-1で描かれているように、リスト14-1はスキーマを示します。

リスト14-1 - ブログアプリケーションの Propel におけるスキーマの例

    [yml]
    propel:
      blog_category:
        id:               ‾
        name:             varchar(255)
      blog_author:
        id:               ‾
        name:             varchar(255)
      blog_article:
        id:               ‾
        title:            varchar(255)
        content:          longvarchar
        blog_author_id:   ‾
        blog_category_id: ‾
        is_published:     boolean
        created_at:       ‾
      blog_comment:
        id:               ‾
        blog_article_id:  ‾
        author:           varchar(255)
        content:          longvarchar
        created_at:       ‾

図14-1 データモデルの例

![データモデルの例](http://www.symfony-project.org/images/book/1_4/F1401.png "データモデルの例")

コード生成を可能にするためにスキーマ作成の期間で従わなければならない特別なルールは存在しません。symfony はスキーマをそのまま使い、管理画面を生成するためにスキーマの属性を解釈します。

>**TIP**
>この章を最大限に活用するには、例で示すの内容を実際構築して試すことをおすすめします。リストで説明されたすべてのステップを眺めるのであれば、symfony が何を生成し、生成されたコードで何が行われるのかということをより理解できるようになります。モデルの初期化は、次のように `propel:build` タスクを実行するだけなので簡単です:
>
>      $ php symfony propel:build --all --no-confirmation

生成された管理インターフェイスは、作業が楽になるようにいくつかのマジックメソッドに依存しています。次のように各モデルクラスに `__toString()` メソッドを作ってください。

    [php]
    class BlogAuthor extends BaseBlogAuthor
    {
      public function __toString()
      {
        return $this->getName();
      }
    }

    class BlogCategory extends BaseBlogCategory
    {
      public function __toString()
      {
        return $this->getName();
      }
    }

    class BlogArticle extends BaseBlogArticle
    {
      public function __toString()
      {
        return $this->getTitle();
      }
    }


管理画面
--------

symfony は、バックエンドアプリケーションのために、`schema.yml` ファイルからのモデルクラス定義に基づいて、モジュールを作成できます。生成された Admin モジュールだけを用いてサイト全体の管理画面を作成できます。このセクションの例では、`backend` アプリケーションに追加される Admin モジュールを説明します。プロジェクトが `backend` アプリケーションを持たない場合、`generate:app` タスクを呼び出してスケルトンを作成します:

    $ php symfony generate:app backend

Admin モジュールは `generator.yml` という名前の特別な設定ファイルを通してモデルを解釈します。すべての生成コンポーネントとモジュールの外見を拡張するために `generator.yml` ファイルを変更できます。このようなモジュールは通常のモジュールメカニズムからの恩恵を受けます (レイアウト、ルーティング、カスタム設定、オートロードなど)。独自機能を生成された管理画面に統合するために、生成されたアクションもしくはテンプレートを上書きすることもできますが、`generator.yml` はもっとも共通の要件を考慮して、PHP コードの使いかたを限定します。

>**NOTE**
>たいていの要件は `generator.yml` 設定ファイルでカバーされますが、この章の後のほうで見るように、設定クラスを通して Admin モジュールを設定することもできます。

### Admin モジュールを初期化する

モデルを基本単位として symfony コマンドで管理画面をビルドします。モジュールは `propel:generate-admin` タスクを使って、Propel オブジェクトまたは Doctrine オブジェクトに基づいて生成されます:

    // Propel
    $ php symfony propel:generate-admin backend BlogArticle --module=article
    
    // Doctrine
    $ php symfony doctrine:generate-admin backend BlogArticle --module=article

>**NOTE**
>Admin モジュールは REST アーキテクチャに基づいています。`propel:generate-admin` タスクは `routing.yml` 設定ファイルにルートなどを自動的に追加します:
>
>     [yml]
>     # apps/backend/config/routing.yml
>     article:
>       class: sfPropelRouteCollection
>       options:
>         model:                BlogArticle
>         module:               article
>         with_wildcard_routes: true
>
>独自のルートを作成しモデルクラスの名前の代わりに独自の名前を引数としてタスクに渡すこともできます:
>
>     $ php symfony propel:generate-admin backend article --module=article

この呼び出しだけで `backend` アプリケーションのなかに `BlogArticle`ク ラスの定義に基づく `article` モジュールが作成されるので、つぎの URL からアクセスできます:

    http://localhost/backend.php/article

図14-2、図14-3で描かれている生成モジュールの外見は、商用アプリケーションとしてそのまま利用できるほど十分に洗練されています。

>**TIP**
>期待どおりの外見でなければ(スタイルシートと画像がない)、`plugin:publish-assets` タスクを実行してプロジェクトにアセットをインストールする必要があります:
>
>     $ php symfony plugin:publish-assets

図14-2 - `backend` アプリケーション内部の `article` モジュールの `list` ビュー

![backend アプリケーション内部の article モジュールの list ビュー](http://www.symfony-project.org/images/book/1_4/F1402.png "backend アプリケーション内部の article モジュールの list ビュー")

図14-3 - バックエンドの `article` モジュールの `edit` ビュー

![バックエンドの article モジュールの edit ビュー](http://www.symfony-project.org/images/book/1_4/F1403.png "バックエンドの article モジュールの edit ビュー")

### 生成コードを見る

`apps/backend/modules/article/` ディレクトリ内の記事の管理画面のコードは初期化だけ行われたので空に見えます。このモジュールの生成コードを吟味するための最良の方法はブラウザーを利用してこれと情報のやりとりをすることと `cache/` フォルダーの内容を確認することです。リスト14-2はキャッシュのなかで見つかる生成アクションとテンプレートのリストを表示します。

リスト14-2 - 生成された管理画面の要素 (`cache/backend/ENV/modules/autoArticle/`)

    // actions/actions.class.phpのアクション
    index            // テーブルのレコードのリストを表示する
    filter           // リストで使われるフィルターを更新する
    new              // 新しいレコードを作成するフォームを表示する
    create           // 新しいレコードを作成する
    edit             // レコードのフィールドを修正するフォームを表示する
    update           // 既存のレコードを更新する
    delete           // レコードを削除する
    batch            // 選択されたレコードのリストでアクションを実行する
    batchDelete      // 選択したレコードの一覧を削除するアクションを実行する
    
    // templates/のなか
    _assets.php
    _filters.php
    _filters_field.php
    _flashes.php
    _form.php
    _form_actions.php
    _form_field.php
    _form_fieldset.php
    _form_footer.php
    _form_header.php
    _list.php
    _list_actions.php
    _list_batch_actions.php
    _list_field_boolean.php
    _list_footer.php
    _list_header.php
    _list_td_actions.php
    _list_td_batch_actions.php
    _list_td_stacked.php
    _list_td_tabular.php
    _list_th_stacked.php
    _list_th_tabular.php
    _pagination.php
    editSuccess.php
    indexSuccess.php
    newSuccess.php

これは生成された Admin モジュールがおもに3つのビュー、`list`、`new` と `edit` で構成されることを示します。コードを見てみると、モジュール性が非常に高く、読みやすく拡張性のあるものであることがわかります。

### `generator.yml` 設定ファイル入門

生成された Admin モジュールは YAML フォーマットの `generator.yml` 設定ファイルで見つかるパラメーターに依存します。新しく生成された Admin モジュールのデフォルト設定を見るには、リスト14-3で再現されている、`backend/modules/article/config/` ディレクトリに設置された `generator.yml` ファイルを開いてください。

リスト14-3 - ジェネレーターのデフォルトコンフィギュレーション (`backend/modules/article/config/generator.yml`)

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        model_class:           BlogArticle
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              BlogArticle
        plural:                BlogArticles
        route_prefix:          blog_article
        with_propel_route:     1
        actions_base_class:    sfActions

        config:
          actions: ‾
          fields:  ‾
          list:    ‾
          filter:  ‾
          form:    ‾
          edit:    ‾
          new: 

このコンフィギュレーションは基本的な管理画面を生成するのに十分です。カスタマイズした内容は `config` キーの下に追加されます。リスト14-4は `generator.yml` をカスタマイズした典型例を示しています。

リスト14-4 - 典型的なジェネレーターの全設定

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        model_class:           BlogArticle
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              BlogArticle
        plural:                BlogArticles
        route_prefix:          blog_article
        with_propel_route:     1
        actions_base_class:    sfActions

        config:
          actions:
            _new: { label: "Create a new article" }

          fields:
            author_id:    { label: Article author }
            published_on: { credentials: editor }

          list:
            title:          Articles
            display:        [title, blog_author, blog_category]
            fields:
              published_on: { date_format: dd/MM/yy }
            layout:         stacked
            params:         |
              %%is_published%%<strong>%%=title%%</strong><br /><em>by %%blog_author%%
              in %%blog_category%% (%%created_at%%)</em><p>%%content%%</p>
            max_per_page:   2
            sort:           [title, asc]

          filter:
            display: [title, blog_category_id, blog_author_id, is_published]

          form:
            display:
              "Post":       [title, blog_category_id, content]
              "Workflow":   [blog_author_id, is_published, created_at]
            fields:
              published_at: { help: "Date of publication" }
              title:        { attributes: { style: "width: 350px" } }

          new:
            title: New article

          edit:
            title: Editing article "%%title%%"

このコンフィギュレーションでは、6つのセクションがあります。これらのうち4つはビューを表し (`list`、`filter`、`new` と `edit`) とこれらのうちの2つは「バーチャル」(`fields` と `form`) で設定目的のためのみ存在します。

つぎのセクションでこの設定ファイルで利用可能なすべてのパラメーターの詳細内容を説明します。

ジェネレーターの設定
---------------------

ジェネレーターの設定ファイルはとても強力で、生成された管理画面を多くの方法で変更できます。しかしこの機能には代償があります: 全体の構文の記述が読んで学ぶには長いので、文章で説明したら、この章がこの本でもっとも長くなってしまいます。ですので symfony の Web サイトはこの構文を学ぶための助けになる追加リソースを提示します: Admin ジェネレーターのチートシートが図14-7で再現されてます。[http://www.symfony-project.org/uploads/assets/sfAdminGeneratorRefCard.pdf](http://www.symfony-project.org/uploads/assets/sfAdminGeneratorRefCard.pdf)から保存し、この章のつぎの例を読むときにこれを頭のなかにとどめておいてください。

このセクションの例は、`BlogComment` クラスの定義に基づいて `article` 管理モジュールと同様に `comment` 管理モジュールを調整します。`propel:generate-admin` タスクを実行して後者を作成します:

    $ php symfony propel:generate-admin backend BlogComment --module=comment

図14-4 - Admin ジェネレーターのチートシート

![Admin ジェネレーターのチートシート](http://www.symfony-project.org/images/book/1_4/F1404 " Admin ジェネレーターのチートシート")

### フィールド

デフォルトでは、`list` ビューのカラムは `schema.yml` で定義されるカラムです。`new` と `edit` ビューのフィールドはモデルに関連づけされたフォームで定義します (`BlogArticleForm`)。`generator.yml` によって、どのフィールドが表示され、どれが非表示であるかを選び、オブジェクトモデルで直接対応するものがなくても独自フィールドを追加できます。

#### フィールドの設定

Admin ジェネレーターは `schema.yml` ファイルのカラムごとに1つのフィールドを作ります。`fields` キーの下で、それぞれのフィールドの表示方法やフォーマット方法などを修正できます。たとえば、リスト14-5で示されるフィールドの設定は `title` フィールド用のカスタムラベルクラスと入力タイプ、そして `content` フィールド用のラベルとツールチップを定義します。つぎのセクションでそれぞれのパラメーターが動作する方法を詳細に説明します。

リスト14-5 - カラムに対してカスタムラベルを設定する

    [yml]
    config:
      fields:
        title:
          label: Article Title
          attributes: { class: foo }
        content: { label: Body, help: Fill in the article body }

リスト14-6のように、すべてのビューに対するこのデフォルトの定義に加えて、任意のビュー (`list`、`filter`、`form`、`new` と `edit`) に対するフィールドの設定をオーバーライドできます。

リスト14-6 - ビュー単位でグローバルな設定ビューをオーバーライドする

    [yml]
    config:
      fields:
        title:     { label: Article Title }
        content:   { label: Body }

      list:
        fields:
          title:   { label: Title }

      form:
        fields:
          content: { label: Body of the article }

これは一般的な原則です: `fields` キーの下のモジュール全体に対して設定される設定項目はビュー固有の領域でオーバーライドできます。オーバーライドのルールはつぎのとおりです:

  * `new` と `edit` は `form` を継承し `form` は `fields` を継承します
  * `list` は `fields` を継承します
  * `filter` は `fields` を継承します

#### display 設定にフィールドを追加する

`fields` セクションで定義したフィールドはそれぞれのビューに対して表示、隠す、順番に並べるなど、さまざまな方法で分類できます。`display` キーはこの目的のために使われます。たとえば、`comment` モジュールのフィールドを順番に並べるには、リスト14-7のコードを使います。

リスト14-7 - 表示フィールドを選択する (`modules/comment/config/generator.yml`)

    [yml]
    config:
      fields:
        article_id: { label: Article }
        created_at: { label: Published on }
        content:    { label: Body }

      list:
        display:    [id, blog_article_id, content]

      form:
        display:
          NONE:     [blog_article_id]
          Editable: [author, content, created_at]

図14-5のように `list` は3つのカラムを表示し、図14-6のように、`new` と `edit` フォームは2つのグループに集められた4つのフィールドを表示します。 

図14-5 - `comment` モジュールの `list` ビュー内部のカスタムカラム設定

![comment モジュールの list ビュー内部のカスタムカラム設定](http://www.symfony-project.org/images/book/1_4/F1405.png "comment モジュールの list ビュー内部のカスタムカラム設定")

図14-6 - `comment` モジュールの `edit` ビュー内部でフィールドを分類する

![comment モジュールの edit ビュー内でフィールドを分類する](http://www.symfony-project.org/images/book/1_4/F1406.png "comment モジュールの edit ビュー内でフィールドを分類する")

`display` 設定を2つの方法で利用できます:

  * `list` ビューに対して: 表示するカラムと表示される順序を選ぶために単純な配列にフィールドを置きます。
  * `form`、`new` と `edit` ビューに対して: グループの名前をキーとするフィールドをグループ分けするのに連想配列、もしくは名前のないグループに対して `NONE` を使います。値はカラムの名前で並べ替えられた配列のままです。フォームクラスに参照されるすべての必須フィールドの一覧を表示することには注意を払ってください。さもないと予期せぬバリデーションエラーに遭遇するかもしれません(第10章を参照してください)。

#### カスタムフィールド

当然のことながら、`generator.yml` で設定されたフィールドはスキーマで定義された実際のカラムに対応している必要はありません。関連クラスがカスタムゲッターを提供する場合、これを `list` ビューのためのフィールドとして使うことができます; ゲッターかつ/もしくはセッターが存在する場合、このクラスは `edit` ビューでも利用できます。たとえば、リスト14-8のように、`BlogArticle` モデルを `getNbComments()` メソッドで拡張できます。

リスト14-8 - モデルにカスタムゲッターを追加する (`lib/model/BlogArticle.class.php`)

    [php]
    public function getNbComments()
    {
      return $this->countBlogComments();
    }

リスト14-9のように、`nb_comments` は生成するモジュールでフィールドとして利用できます (ゲッターではフィールド名にキャメルケースを使っていることに注意してください)。

リスト14-9 - カスタムゲッターで管理モジュール用のカラムを提供する (`backend/modules/article/config/generator.yml`)

    [yml]
    config:
      list:
        display:  [title, blog_author, blog_category, nb_comments]

`article` モジュールの `list` ビューは図14-7のようになります

図14-7 - `article` モジュールの `list` ビュー内のカスタムフィールド

![article モジュールの list ビュー内部のカスタムフィールド](http://www.symfony-project.org/images/book/1_4/F1407.png "article モジュールの list ビュー内部のカスタムフィールド")


#### パーシャルフィールド

モデルに設置されたコードはプレゼンテーションから独立していなければなりません。前出の `getArticleLink()` メソッドの例はレイヤー分離の原則を順守していません。ビューコードのなかにはモデルレイヤーに現れるものがあるからです。また、前出のコードではデフォルトではエスケープされた `<a>` タグとして表示されてしまうでしょう。正しい方法で、同じ結果を実現するには、カスタムフィールドに対応する HTML を出力するコードはパーシャルテンプレートで対応するほうがベターです。幸い、Admin ジェネレーターによってアンダースコアのプレフィックスを持つフィールド名を宣言できます。この場合、リスト14-11の `generator.yml` ファイルはリスト14-12のように修正されます。

リスト14-12 - パーシャルを追加カラムとして使うには、プレフィックスに `_` を指定する

    [yml]
    config:
      list:
        display: [id, _article_link, created_at]

これを機能させるには、リスト14-13で示す内容で、`modules/comment/tempaltes/` ディレクトリに `_article_link.php` パーシャルを作る必要があります。

リスト14-13 - `list` ビュー用のパーシャルテンプレートの例 (`modules/comment/templates/_article_link.php`)

    [php]
    <?php echo link_to($BlogComment->getBlogArticle()->getTitle(), 'blog_article_edit', $BlogComment->getBlogArticle()) ?> 

パーシャルフィールドのテンプレートでは、特定の名前の変数で現在のオブジェクトにアクセスできる必要があります(この例では `$BlogComment`)。

Figure 14-8 - `article` モジュールの `list` ビュー内のパーシャルフィールド

![articleモジュールのlistビュー内のパーシャルフィールド](http://www.symfony-project.org/images/book/1_4/F1408.png "articleモジュールのlistビュー内のパーシャルフィールド")

レイヤー分離の原則が順守されます。この原則に慣れたら、アプリケーションのメンテナンスが容易になります。

パーシャルフィールドのパラメーターをカスタマイズする必要がある場合、`fields` キーの下で通常のフィールドと同じ事を行います。アンダースコア (`_`) をキーの先頭に含めないでください。リスト14-14をご覧ください。

リスト14-14 - パーシャルフィールドのプロパティは `fields` キーの下でカスタマイズできる

    [yml]
    config:
      fields:
        article_link: { label: Article }

パーシャルにロジックが混在するようになったら、コンポーネントに置き換えることをおすすめします。リスト14-15のように、プレフィックスの `_` を `‾` に変更することで、コンポーネントフィールドを定義できます。

リスト14-15 - コンポーネントを追加カラムとして利用する。プレフィックスに `‾` を使う

    [yml]
    config:
      list:
        display: [id, ‾article_link, created_at]

このようにすると、生成するテンプレートで現在のモジュールの `articleLink` コンポーネントが呼び出されます。

>**NOTE**
>カスタムフィールドとパーシャルフィールドは `list`、`new`、`edit` と `filter` ビューで使うことができます。複数のビューに対して同じパーシャルを使う場合、コンテキスト (`list`、`new`、`edit` もしくは `filter`) は変数 `$type` に保存されます。

### ビューのカスタマイズ

`new`、`edit` と `list` ビューの外見を変更するために、テンプレートを変更したいと思うことがあります。しかしこれらは自動的に生成されるので、生成されたテンプレートを変更するのはあまりよいアイデアではありません。代わりに `generator.yml` 設定ファイルを使うべきです。モジュール性を損なうことなく、ほとんどすべての必要なことを行えるからです。

#### ビューのタイトルを変更する

フィールドのカスタムセットに加えて、`list`、`new`、`edit` ページはカスタムページタイトルを持ちます。たとえば、`article` ビューのタイトルをカスタマイズしたい場合、リスト14-16のように行います。`edit` ビューの結果は図14-9に描かれています

リスト14-16 - それぞれのビューに対してカスタムタイトルを設定する (`backend/modules/article/config/generator.yml`)

    [yml]
    config:
      list:
        title: List of Articles

      new:
        title: New Article

      edit:
        title: Edit Article %%title%% (%%id%%)

図14-9 - `article` モジュールの `edit` ビュー内のカスタムタイトル

![article モジュールの edit ビュー内のカスタムタイトル](http://www.symfony-project.org/images/book/1_4/F1409.png "article モジュールの edit ビュー内のカスタムタイトル")

デフォルトのタイトルはクラスの名前を使うので、モデルが明白なクラスの名前を使うのであれば、これらのクラス名で十分であることはよくあります。

>**TIP**
>`generator.yml` の文字列の値において、フィールドの値は `%%` で囲まれたフィールドの名前を通してアクセスできます。

#### ツールチップを追加する

`list`、`new`、`edit` と `filter` ビュー内部において、表示されるフィールドを記述するための助けになるツールチップを追加できます。たとえば、ツールチップを `comment` モジュールの `edit` ビューの `blog_article_id` フィールドに追加するには、リスト14-17のように、`fields` の定義に `help` プロパティを追加します。結果は図14-10に示されています。

リスト14-17 - `edit` ビューでツールチップを設定する (`modules/comment/config/generator.yml`)

    [yml]
    config:
      edit:
        fields:
          blog_article_id: { help: The current comment relates to this article }

図14-10 - `comment` モジュールの `edit` ビュー内のツールチップ

![comment モジュールの edit ビュー内のツールチップ](http://www.symfony-project.org/images/book/1_4/F1410.png "comment モジュールの edit ビュー内のツールチップ")

`list` ビューにおいて、ツールチップはカラムのヘッダーに表示されます; `new`、`edit`、`filter` ビューにおいてこれらは field タグの下で現れます。

#### 日付の書式を修正する

リスト14-18でお手本が示されているように、`date_format` オプションを使うことで同時にカスタム書式で日付を表示できます。

リスト14-18 `list` ビューのなかで日付の書式設定をする

    [yml]
    config:
      list:
        fields:
          created_at: { label: Published, date_format: dd/MM }

このパラメーターは以前の章で説明された `format_date()` ヘルパーと同じフォーマットパラメーターを受けとります。

>**SIDEBAR**
>管理画面のテンプレートは国際化の準備ができています
>
>admin で生成されたモジュールはインターフェイスの文字列 (デフォルトのアクションの名前、ページ分割のヘルプメッセージ、・・・) とカスタム文字列 (タイトル、カラムのラベル、ヘルプメッセージ、エラーメッセージ、・・・) で構成されます。
>
>インターフェイスの文字列の多言語翻訳機能はsymfonyに搭載されています。しかし独自のものを追加するか `sf_admin` カタログ (`apps/frontend/i18n/sf_admin.XX.xml`、`XX` は言語のISOコード) 用の `i18n` ディレクトリでカスタムXLIFFファイルを作成することで既存のものをオーバーライドできます
>
>生成されたテンプレートで見つかるすべてのカスタム文字列も自動的に国際化されます (すなわち、`__()`ヘルパーへの呼び出しと一緒に)。このことは前の章で説明したように、`apps/frontend/i18n/` ディレクトリで XLIFF ファイルにフレーズの翻訳を追加することで、生成された管理画面を簡単に翻訳できることを意味します。
>
>`i18n_catalogue` パラメーターを指定することでカスタム文字列用に使われるデフォルトのカタログを変更できます:
>
>     [yml]
>     generator:
>       class: sfPropelGenerator
>       param:
>         i18n_catalogue: admin

### list ビュー固有のカスタマイゼーション

`list` ビューは、表形式、もしくはすべての詳細な内容が一行に積み重ねられた状態で、レコードの詳細を表示できます。これはフィルター、ページ分割とソート機能も含みます。これらの機能は設定によって変更できます。詳細はつぎのセクションで説明します。

#### レイアウトを変更する

デフォルトでは、`list` ビューと `edit` ビュー間のハイパーリンクは主キーのカラムによって生成されます。図14-8に戻ると、コメントリストの `id` カラムがそれぞれのコメントの主キーを表示するだけでなく、ユーザーが `edit` ビューにアクセスすることを許可するハイパーリンクを提供します。

別のカラムに現れるレコードの詳細内容へのハイパーリンクが望ましいのであれば、`display` キーの等号 (`=`) をプレフィックスとしてカラムの名前に追加します。リスト14-19は `list` コメントの表示フィールドから `id` を除去して代わりに `content` フィールドにハイパーリンクを置く方法を示しています。図14-11のスクリーンショットを確認してください。

リスト14-19 - `edit` ビューのためのハイパーリンクを `list` ビューに移動させる (`modules/comment/config/gererator.yml`)

    [yml]
    config:
      list:
        display: [_article_link, =content]

図14-11 - `comment` モジュールの `list` ビューのなかで、リンクを別のカラム上の `edit` ビューに移動させる

![comment モジュールの list ビュー内で、リンクを別のカラム上の edit ビューに移動させる](http://www.symfony-project.org/images/book/1_4/F1411.png "comment モジュールの list ビュー内で、リンクを別のカラム上の edit ビューに移動させる")

デフォルトでは、以前示されたように、`list` ビューはフィールドがカラムとして表示される`tabular`レイアウトを使います。しかし `stacked` レイアウトを使ってフィールドをテーブルの全長を詳しく記述する単独の文字列に連結することもできます。`stacked` レイアウトを選ぶ場合、`params` キーにリストのそれぞれの行の値を定義するパターンを組み込まなければなりません。たとえば、リスト14-20は `comment` モジュールの `list` ビューに対して `stacked` レイヤーを定義します。結果は図14-12です。

リスト14-20 - `list` ビューで `stacked` レイアウトを使う (`modules/comment/cofig/generator.yml`)

    [yml]
    config:
      list:
        layout:  stacked
        params:  |
          %%=content%%<br />
          (sent by %%author%% on %%created_at%% about %%_article_link%%)
        display:  [created_at, author, content]

図14-12 - `comment` モジュールの `list` ビュー内のスタックレイアウト

![comment モジュールの list ビュー内のスタックレイアウト](http://www.symfony-project.org/images/book/1_4/F1412.png "comment モジュールの list ビュー内のスタックレイアウト")

`tabular` レイアウトは `display` キーの下でフィールドの配列を必要としますが、`stacked` レイアウトはそれぞれのレコードに対して生成された HTML コードのために `params` キーを使うことに留意してください。しかしながら、インタラクティブなソートに対して利用できるカラムヘッダーを決定するために `display` 配列が `stacked` レイアウトでも使われます

#### 結果をフィルタリングする

`list` ビューにおいて、フィルターのインタラクションのセットを追加できます。これらのフィルターによって、ユーザーは結果をより少なく表示して速く望むものに到達できます。フィールド名の配列で、`filter` キーの下にフィルターを設定します。たとえば、図14-13のフィルターボックスと同じようなものを表示するには、リスト14-21のように、`blog_article_id`、`author` フィールドと `created_at` フィールド上のフィルターをコメントの`list` ビューに追加します。

リスト14-21 - `list` ビューでフィルターを設定する (`modules/comment/config/generator.yml`)

    [yml]
    config:
      list:
        layout:  stacked
        params:  |
          %%=content%% <br />
          (sent by %%author%% on %%created_at%% about %%_article_link%%)
        display:  [created_at, author, content]

      filter:
        display: [blog_article_id, author, created_at]

図14-13 - `comment` モジュールの `list` ビュー内のフィルター

![comment モジュールの list ビュー内のフィルター](http://www.symfony-project.org/images/book/1_4/F1413.png "comment モジュールの list ビュー内のフィルター")

symfony に表示されるフィルターはスキーマで定義されるカラムの型に依存し、フィルターフォームクラスでカスタマイズできます:

  * テキストカラム (たとえば `comment` モジュール内の `author` フィールド) に対して、フィルターはテキストベースの検索を可能にするテキスト入力です (ワイルドカードが自動的に追加).
  * 外部キー (たとえば `comment` モジュール内部の `blog_article_id` フィールドのリスト) に対して、フィルターは関連するテーブルのレコードのドロップダウンリストです。デフォルトでは、ドロップダウンリストのオプションは関連するクラスの `__toString()` メソッドによって返されるものです。
  * 日付のカラム (たとえば `comment` カラム内の `created_at`フィールド) に対して、フィルターはリッチな日付タグのペアで、時間の間隔を選択できるようにします。
  * ブール型のカラムに対して、フィルターは `true` と `false` と `true or false` オプションを持つドロップダウンリストです。最後の値はフィルターを再初期化します。

`new` と `edit` ビューがフォームクラスに結びつけられているように、モデルに関連するデフォルトのフィルターフォームクラスです (たとえば `BlogArticle` モデルに対して `BlogArticleFormFilter`)。フィルターフォームに対してカスタムクラスを定義することで、フォームフレームワークの力を活用しすべての利用可能なフィルターウィジェットを使うことでフィルターフィールドをカスタマイズできます。リスト14-22で示されるように、`filter` エントリーの下で `class` を定義することで簡単に実現できます。

リスト14-22 - フィルタリングに使われるフォームクラスをカスタマイズする

    [yml]
    config:
      filter:
        class: BackendArticleFormFilter

>**TIP**
>フィルターを一緒に無効にするには、フィルター用の `class` に `false` を指定するだけです。

フィルターを修正するためにパーシャルフィルターも使うことができます。それぞれのパーシャルはフォームをレンダリングするさいに `form` と HTMLの `attributes` を受けとります。リスト14-23はパーシャル以外のデフォルトのふるまいを模倣する実装の例を示しています。

リスト14-23 - パーシャルフィルターを使う

    [php]
    // templates/_state.php で、パーシャルを定義する
    <?php echo $form[$name]->render($attributes->getRawValue()) ?>

    // config/generator.yml でパーシャルフィルターをリストに追加する
    config:
      filter: [created_at, _state]

#### リストをソートする

`list` ビューにおいて、図14-18で示されるように、テーブルのヘッダーはリストを再び順番に並べるために使われるハイパーリンクです。これらのヘッダーは `tabular` レイアウトと `stacked` レイアウトの両方で表示されます。これらのリンクをクリックするとリストの順番を再配置する `sort` パラメーターでページがリロードされます。

図14-14 - `list` ビューのテーブルヘッダーはソートをコントロールする

![list ビューのテーブルヘッダーはソートはコントロールする](http://www.symfony-project.org/images/book/1_4/F1414.png "list ビューのテーブルヘッダーはソートをコントロールする")

あるひとつのカラムによってソートされたリストを直接指定する構文を再利用できます:

    [php]
    <?php echo link_to('日付ごとのコメントのリスト', '@blog_comment?sort=created_at&sort_type=desc' ) ?>

`generator.yml` ファイル内部の `list` ビューに対して直接デフォルトの `sort` の順番を定義することも可能です。構文はリスト14-24で示された例に従います。

リスト14-24 - `list` ビューのなかでデフォルトの `sort` フィールドを設定する

    [yml]
    config:
      list:
        sort:   created_at
        # ソートの順序を指定するための、代替構文
        sort:   [created_at, desc]

>**NOTE**
>実際のカラムに対応するフィールドだけが、ソートのコントロール機能に変換されます。カスタムもしくはパーシャルフィールドには対応していません。

#### パジネーションをカスタマイズする

生成された管理画面は大きなテーブルさえも効率的に処理します。`list` ビューがデフォルトでパジネーションを使うからです。テーブル内の実際の行の数がページごとの行の最大数を越える場合、パジネーションのコントロール機能がリスト下部に表示されます。たとえば、図14-15はテーブルに 6 件のテストコメントがあるリストで、1 ページに表示するのは 5 件に制限されています。パジネーションにより、表示される行のみがデータベースから効率的に抽出されるのでパフォーマンスが保証され、管理モジュールによって何百万行もあるテーブルを管理できるのでユーザービリティが保証されます。

図14-15 - 長いリストでパジネーションのコントロール機能が表示される

![長いリストでパジネーションのコントロール機能が表示される](http://www.symfony-project.org/images/book/1_4/F1415.png "長いリストでパジネーションのコントロール機能が表示される")

`max_per_page` パラメーターによってそれぞれのページに表示されるレコードの数をカスタマイズできます:

    [yml]
    config:
      list:
        max_per_page:   5

#### ページの表示を高速化するために Join を使う

デフォルトでは、Admin ジェネレーターは`doSelect()` を使ってレコードのリストを取得します。しかし、リストで関連オブジェクトを使う場合、リストを表示するために必要なデータベースクエリの数が急に増えることがあります。たとえば、コメントのリストで記事の名前を表示したい場合、関連する `BlogArticle` オブジェクトを検索するためにリスト内のそれぞれの投稿に対する追加クエリが必要です。ですので、クエリの数を最適化するために、`doSelectJoinXXX()` メソッドを使うページャーを強制したい場合があるかもしれません。これは `peer_method` パラメーターで指定できます。

    [yml]
    config:
      list:
        peer_method: doSelectJoinBlogArticle

18章では Join の概念についてより広範囲に説明します。

### new と edit ビュー固有のカスタマイズ

`new` もしくは `edit` ビューにおいて、ユーザーは新しいレコードもしくは渡されたレコードに対してそれぞれのカラムの値を修正できます。デフォルトでは、admin ジェネレーターで使われるフォームはモデル: `BlogArticle` モデルに対して `BlogArticleForm` に関連するフォームです。リスト14-25で示されるように `form` エントリーの下で `class` を定義することで使うクラスをカスタマイズできます。

リスト14-25 - `new` と `edit` ビューに使われるフォームクラスをカスタマイズする

    [yml]
    config:
      form:
        class: BackendBlogArticleForm

カスタムフォームクラスを利用することで admin ジェネレーター用のウィジェットとバリデーターすべてをカスタマイズできます。frontend アプリケーションに対してデフォルトのフォームクラスが使われ特別にカスタマイズされます。

リスト14-26で示されるように `generator.yml` 設定ファイルでラベル、ヘルプメッセージ、とフォームのレイアウトを直接カスタマイズすることもできます。

リスト14-26 - フォーム表示をカスタマイズする

    [yml]
    config:
      form:
        display:
          NONE:     [article_id]
          Editable: [author, content, created_at]
        fields:
          content:  { label: body, help: "The content can be in the Markdown format" }

#### パーシャルフィールドを扱う

パーシャルフィールドは `list` ビューのように `new` と `edit` ビューで扱えます。

### 外部キーを扱う

スキーマがテーブルのリレーションを定義する場合、生成された Admin モジュールはこれを活用して、より自動化されたコントロール方法を提供するので、リレーションの管理作業が大いに簡略化されます。

#### 一対多のリレーション

1対多のテーブルのリレーションは Admin ジェネレーターによって考慮されます。以前の図14-1で記述されたように、`blog_comment` テーブルは `blog_article_id` フィールドを通して `blog_article` テーブルとの関係を持ちます。`BlogComment` クラスのモジュールを Admin ジェネレーターによって初期化する場合、`edit` ビューは `blog_article` テーブル内の利用可能なレコードの ID を示す `blog_article_id` をドロップダウンリストとして表示します (説明図は図14-9を再度確認)。

`article` モジュール(多対一のリレーション)内の記事に関連するコメントのリストを表示する場合も同様です。

#### 多対多のリレーション

symfony は図14-16で示されるように多対多のリレーションも考慮します。

図14-16 - 多対多のリレーション

![多対多のリレーション](http://www.symfony-project.org/images/book/1_4/F1416.png "多対多のリレーション")

リレーションをレンダリングするために使われるウィジェットをカスタマイズすることで、フィールドのレンダリングを調整できます(図14-17で説明):

図14-17 - 多対多のリレーションに対して利用できるコントロール機能

![多対多のリレーションに対して利用できるコントロール機能](http://www.symfony-project.org/images/book/1_4/F1417.png "多対多のリレーションに対して利用できるコントロール機能")

### インタラクションを追加する

Admin モジュールはユーザーが通常のCRUDオペレーションを実行できるようにしますが、ビューに対して独自のインタラクションを追加するもしくは可能なインタラクションを制限することもできます。たとえば、リスト14-27で示されているインタラクションを定義することで `article` モジュール上のすべての CRUD アクションにデフォルトでアクセスできるようになります。

リスト14-27 - それぞれのビューに対してインタラクションを定義する (`backend/modules/article/config/generator.yml`)

    [yml]
    config:
      list:
        title:          List of Articles
        object_actions:
          _edit:         ‾
          _delete:       ‾
        batch_actions:
          _delete:       ‾
        actions:
          _new:          ‾

      edit:
        title:          Body of article %%title%%
        actions:
          _delete:       ‾
          _list:         ‾
          _save:         ‾
          _save_and_add: ‾

`list` ビューにおいて、アクションの設定が3つ存在します: すべてのオブジェクトに対して利用可能なアクション (`object_actions`)、オブジェクトの選択に対して利用可能なアクション (`batch_actions`)、ページ全体に対して利用可能なアクション (`actions`) です。リスト14-27で定義されたリストのインタラクションは図14-18のようにレンダリングします。それぞれの行はレコードを編集するためのボタンとレコードを削除するためのボタン、それらに加えて、レコードの選択を削除するためにそれぞれの行の上に1つのチェックボックスを表示します。リストの一番下で、1つのボタンによって新しいレコードを作ることができます。

図14-18 - `list` ビュー内のインタラクション

![list ビュー内のインタラクション](http://www.symfony-project.org/images/book/1_4/F1418.png "list ビュー内のインタラクション")

`new` と `edit` ビューにおいて、一度に編集されるレコードは1つだけであり、(`actions` のもとで) 定義するアクションのセットは1つだけです。リスト14-27で定義された `edit` インタラクションは図14-23のようにレンダリングされます。`save` アクションと `save_and_add` アクションは現在の編集をレコードに保存します。これらのアクションの違いは、`save` アクションは保存したあとで現在のレコード上に `edit` ビューを表示するのに対して、`save_adn_add` アクションは別のレコードを追加するために `new` ビューを表示することです。`save_and_add` アクションは続けざまに多くのレコードを追加するときに非常に便利なショートカットです。`delete` アクションの位置に関しては、これはほかのボタンから分離されているので、ユーザーが誤ってクリックすることはありません。

アンダースコア (`_`) で始まるインタラクション名は symfony に対してこれらのアクションに対応するデフォルトのアイコンとアクションを使うように伝えます。Admin ジェネレーターは `_edit`、`_delete`、`_new`、`_list`、`_save`、`_save_and_add` と `_create` を理解します。

図14-19 - `edit` ビュー内のインタラクション

![edit ビュー内のインタラクション](http://www.symfony-project.org/images/book/1_4/F1419.png "edit ビュー内のインタラクション")

しかしカスタムインタラクションを追加することもできます。この場合リスト14-28で示されるように、アンダースコアで始まらない名前、と現在のモジュールのなかのターゲットのアクションを指定しなければなりません。

リスト14-28 - カスタムインタラクションを定義する

    [yml]
    list:
      title:          List of Articles
      object_actions:
        _edit:        -
        _delete:      -
        addcomment:   { label: Add a comment, action: addComment }

図14-20で示されるように、リストにおけるそれぞれのアクションは `Add a comment` リンクを表示します。これをクリックすることで現在のモジュール内で `addComment` アクションを呼び出すことが行われます。現在のオブジェクトの主キーはリクエストパラメーターに自動的に追加されます。

図14-20 - `list` ビュー内部のカスタムインタラクション

![list ビュー内のカスタムインタラクション](http://www.symfony-project.org/images/book/1_4/F1420.png "listビュー内のカスタムインタラクション")

`addComment` アクションはリスト14-29のように実装できます。

リスト14-29 - カスタムインタラクションアクション (`actions/actions.class.php`)

    [php]
    public function executeAddComment($request)
    {
      $comment = new BlogComment();
      $comment->setArticleId($request->getParameter('id'));
      $comment->save();

      $this->redirect('blog_comment_edit', $comment);
    }

バッチスクリプトは `ids` リクエストパラメーターのなかで選択されたレコードの主キーの配列を受けとります。

アクションについて最後の一言です: あるカテゴリのためにアクションを完全に抑制したい場合、リスト14-30で示されるように、空のリストを使います。

リスト14-30 - `list` ビューのすべてのアクションを除去する

    [yml]
    config:
      list:
        title:   List of Articles
        actions: {}

### フォームのバリデーション

バリデーションは `new` と `edit` ビューで使われるフォームを自動的に考慮します。対応するフォームクラスを編集することでこのフォームをカスタマイズできます。

### クレデンシャルを利用してユーザーのアクションを制限する

特定の Admin モジュールに対して、利用可能なフィールドとインタラクションはログインユーザーのクレデンシャルによって変化します (symfony のセキュリティ機能の説明に関しては6章を参照)。

適切なクレデンシャルを持つユーザーのみに対して表示されるようにするためにジェネレーター内部のフィールドは `credentials` パラメーターを考慮に入れます。これは `list`エントリーに対して機能します。加えて、ジェネレーターはクレデンシャルにしたがってインタラクションも隠すことができます。リスト14-31はこれらの機能のお手本を示しています。

リスト14-31 - クレデンシャルを使う (`generator.yml`)

    [yml]
    config:
      # adminクレデンシャルを持つユーザーに対してのみidカラムは表示される
      list:
        title:          List of Articles
        display:        [id, =title, content, nb_comments]
        fields:
          id:           { credentials: [admin] }

      # addcommentインタラクションはadminクレデンシャルを持つユーザーに制限される
      actions:
        addcomment: { credentials: [admin] }

生成モジュールのプレゼンテーションを修正する
-----------------------------------------------

独自のスタイルシートを適用するだけでなく、デフォルトのテンプレートで上書きすることで、生成されたモジュールのプレゼンテーションが既存のグラフィカルな表にマッチするように、そのモジュールを修正できます。

### カスタムスタイルシートを使う

生成された HTML コードは構造化された内容を持つので、プレゼンテーションであなたが望むことを大いに行うことができます。

リスト14-32で示されるように、`css` パラメーターを生成されたジェネレーターの設定に追加することで、Admin モジュールに対して、デフォルトの代わりにカスタム CSS を定義できます。

リスト14-32 - デフォルトの代わりにカスタムスタイルシートを使う

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        model_class:           BlogArticle
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              BlogArticle
        plural:                BlogArticles
        route_prefix:          blog_article
        with_propel_route:     1
        actions_base_class:    sfActions
        css:                   mystylesheet

もう一つの方法として、ビュー単位でスタイルを上書きするためにモジュールの `view.yml` によって提供されるメカニズムも利用できます。

### カスタムヘッダーとフッターを生成する

`list`、`new`、`edit` ビューはヘッダーとフッター部分テンプレートを系統的に含むことができます。Admin モジュールの `templates/` ディレクトリのなかにはそのような部分テンプレートは存在しませんが、部分テンプレートを自動的にインクルードするには以下の名前の1つを追加する必要があります:

    _list_header.php
    _list_footer.php
    _form_header.php
    _form_footer.php

たとえば、カスタムヘッダーを `article/edit` ビューに追加したい場合、リスト14-33のように `_form_header.php` という名前のファイルを作ります。これは追加の設定なしで動作します。

リスト14-33 - `edit` ヘッダーパーシャルの例 (`modules/article/templates/_form_header.php`)

    [php]
    <?php if ($blog_article->getNbComments() > 0): ?>
      <h2>この記事には<?php echo $blog_article->getNbComments() ?>件のコメントがあります</h2>
    <?php endif; ?>

`edit` 部分テンプレートはクラスによって命名された変数を通していつでも現在のオブジェクトにアクセス可能で、`list` 部分テンプレートは `$pager` 変数を通していつでも現在のページャーにアクセスできることに留意してください。


### テーマをカスタマイズする

カスタム要件を満たすために、モジュールの `templates/` フォルダーのなかでオーバーライド可能でフレームワークから継承された別の部分テンプレートが存在します。

ジェネレーターのテンプレートは個別に上書きできる小さな部分に分割可能で、アクションは一つずつ変更できます。

しかしながら、同じ方法でいくつかのモジュールに対してこれらを上書きしたいのであれば、おそらくは再利用可能なテーマを作るべきです。テーマ (theme) はテンプレートとアクションのサブセットで、`generator.yml` の始めでテーマの値に指定された場合、Admin モジュールのなかで使えます。デフォルトのテーマと一緒に、symfony は `$sf_symfony_lib_dir/plugins/sfPropelPlugin/data/generator/sfPropelModule/admin/` で定義されたファイルを使います。

テーマのファイルは `data/generator/sfPropelModule/[theme_name]/` ディレクトリ内部の、プロジェクトのツリー構造に設置しなければならず、デフォルトのテーマからオーバーライドしたいファイルをコピーすることで新しいテーマを使い始めることができます (`$sf_symfony_lib_dir/plugins/sfPropelPlugin/data/generator/sfPropelModule/admin/` ディレクトリに設置されます):

    // [theme_name]/template/templates/ のパーシャル
    _assets.php
    _filters.php
    _filters_field.php
    _flashes.php
    _form.php
    _form_actions.php
    _form_field.php
    _form_fieldset.php
    _form_footer.php
    _form_header.php
    _list.php
    _list_actions.php
    _list_batch_actions.php
    _list_field_boolean.php
    _list_footer.php
    _list_header.php
    _list_td_actions.php
    _list_td_batch_actions.php
    _list_td_stacked.php
    _list_td_tabular.php
    _list_th_stacked.php
    _list_th_tabular.php
    _pagination.php
    editSuccess.php
    indexSuccess.php
    newSuccess.php

    // [theme_name]/parts のアクション
    actionsConfiguration.php
    batchAction.php
    configuration.php
    createAction.php
    deleteAction.php
    editAction.php
    fieldsConfiguration.php
    filterAction.php
    filtersAction.php
    filtersConfiguration.php
    indexAction.php
    newAction.php
    paginationAction.php
    paginationConfiguration.php
    processFormAction.php
    sortingAction.php
    sortingConfiguration.php
    updateAction.php

テンプレートファイルは実際にはテンプレートのテンプレート (templates of templates) であることに注意してください。すなわち、PHP ファイルはジェネレーターの設定に基づくテンプレートを生成する特別なユーティリティによって解析されます (これはコンパイレーションフェーズ (compilation phase) と呼ばれます)。生成テンプレートが実際にブラウジングしている間に実行されるPHPコードを含まなければならないので、テンプレートのテンプレートは最初のパスの間に PHP コードを実行しないようにするために代替構文を使います。リスト14-34はデフォルトのテンプレートのテンプレートの抜粋です。

リスト14-34 - テンプレートのテンプレートの構文

    [php]
    <h1>[?php echo <?php echo $this->getI18NString('edit.title') ?> ?]</h1>

    [?php include_partial('<?php echo $this->getModuleName() ?>/flashes') ?]

このリストにおいて、(コンパイル時に) `<?` によって導入されたPHPコードは即座に実行され、`[?`によって導入されたものは実行時のみに実行されますが、テンプレートエンジンは最後には `[?` タグを `<?` タグに変換するのでテンプレートの結果はつぎのようになります:

    [php]
    <h1><?php echo __('List of all Articles') ?></h1>

    <?php include_partial('article/flashes') ?>

テンプレートのテンプレートは扱いにくいので、独自のテーマを作りたい場合、もっともお勧めすることは `admin` テーマから始め、少しずつ修正し、こまめにテストすることです。

>**TIP**
>ジェネレーターのテーマをプラグインのパッケージにすることができるので、再利用性が高くなり、複数のアプリケーションにまたがってデプロイすることが簡単になります。詳細な情報は17章を参照してください。

-

>**SIDEBAR**
>独自のジェネレーターを開発する
>
>Admin ジェネレーターは symfony の内部コンポーネントのセットを使います。内部コンポーネントはキャッシュ内部に生成されたアクションとテンプレート、テーマの使用、テンプレートのテンプレートの解析を自動化します。
>
>このことは symfony が既存のジェネレーターとは似ているもしくは完全に異なる独自のジェネレーターを作るためのすべてのツールを提供することを意味します。モジュールの生成は `sfGeneratorManager` クラスの `generate()` メソッドで管理されます。たとえば、管理画面を生成するために、symfony は内部でつぎのコードを呼び出します:
>
>     [php]
>     $manager = new sfGeneratorManager();
>     $data = $manager->generate('sfPropelGenerator', $parameters);
>
>独自のジェネレーターを開発したい場合、`sfGeneratorManeger` クラスと `sfGenerator` クラスの API ドキュメントを見て、`sfModelGenerator` クラスと `sfPropelGenerator` クラスを例としてください。

まとめ
----

バックエンドのアプリケーションを自動的に生成したい場合、基本はよく定義されたスキーマとオブジェクトモデルです。管理画面のカスタマイズによって生成されたモジュールのカスタマイズの大半は設定を通して行われます。

`generator.yml`ファイルは生成されたバックエンドのプログラミングにおいて中心的な役割を果たします。このファイルによって内容、機能、`list`、`new` と `edit` ビューの見た目を完全にカスタマイズできます。また PHP コードを一行も書かずに、フィールドラベル、ツールチップ、フィルター、ソートの順番、ページのサイズ、入力タイプ、外部のリレーション、カスタムインタラクション、とクレデンシャルを YAML で直接管理できます。

Admin ジェネレーターが必要な機能をネイティブにサポートしない場合、アクションをオーバーライドする部分テンプレートフィールドと機能は完全な拡張性を提供します。それに加えて、テーマのメカニズムのおかげで、Admin ジェネレーターのメカニズムへの適合方法を再利用できます。
