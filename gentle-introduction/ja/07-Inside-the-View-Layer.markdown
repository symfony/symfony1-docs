第7章 - ビューレイヤーの内側
============================

ビュー (view) は特定のアクションと関連づけされた出力をレンダリングする仕事を引き受けます。symfony において、ビューは複数の部分から構成され、それぞれの部分にとり組む人達が簡単に修正できるように設計されています。

  * Web デザイナーは一般的にテンプレート (現在のアクションのデータのプレゼンテーション) と (すべてのページに共通なコードを含む) レイアウトにとり組みます。これらは HTML で書かれ、大部分がヘルパーの呼び出しである、PHP の埋め込みコードの塊 (チャンク) です。
  * 再利用のために、開発者は通常テンプレートコードのフラグメント (fragment - 断片) を部分テンプレート (partial - パーシャル) もしくはコンポーネント (component) にまとめます。これらはレイアウトの複数の領域に影響を与えるためにスロット (slot) を使います。Web デザイナーもこれらのテンプレートのフラグメントにとり組むことができます。
  * 開発者は (レスポンスのプロパティとほかのインターフェイスの要素を設定する) ビューの YAML 設定ファイルとレスポンスオブジェクトにとり組みます。テンプレートのなかで変数を扱うとき、クロスサイトスクリプティング (XSS - Cross-Site Scripting) のリスクを無視してはなりませんし、ユーザーのデータを安全に記録するために出力エスケーピングのテクニックをよく理解していることが求められます。

しかしながら、あなたの役割が何であれ、アクションの結果を表現する退屈な作業を速くする便利なツールを見ることになります。この章ではこれらすべてのツールをカバーします。

テンプレートを利用する
----------------------

リスト7-1は symfony の典型的なテンプレートを示しています。HTML コードと基本的な PHP コード、通常はアクション (`$this->name = 'foo';`) とヘルパーで定義された変数の呼び出しが含まれます。

リスト7-1 - テンプレートのサンプル (`indexSuccess.php`)

    [php]
    <h1>ようこそ</h1>
    <p>お帰りなさい、<?php echo $name ?>！</p>
　　　　<h2>何をなさりたいですか？</h2>
    <ul>
      <li><?php echo link_to('最新の記事を読む', 'article/read') ?></li>
      <li><?php echo link_to('新しい記事を書き始める', 'article/write') ?></li>
    </ul>

4章で説明したように、テンプレートのなかでは PHP 開発者ではない人が読みやすいように PHP の代替構文が望ましいです。テンプレートのなかでは PHP コードは最小限に保つべきです。これらのファイルはアプリケーションの GUI を設計するために使われ、ときどき、別のチームによって作成とメンテナンスが行われ、アプリケーションのロジックではなくプレゼンテーションに特化されているからです。ロジックをアクション内部に保つことで、コードを重複させずに、1つのアクションを共有する複数のテンプレートを持つことが簡単になります。

### ヘルパー

ヘルパーは PHP 関数で HTML コードを返し、テンプレートのなかで使うことができます。リスト7-1において `link_to()` 関数はヘルパーです。時々、テンプレート内でよく使われるコードスニペット(断片)をまとめることで、ヘルパーは時間を節約します。たとえば、つぎのヘルパーの関数定義は簡単に想像がつきます。

    [php]
    <?php echo image_tag('photo.jpg') ?>
     => <img src="/images/photo.jpg" />

上記のコードはリスト7-2のようになります。

リスト7-2 - ヘルパー定義のサンプル

    [php]
    function image_tag($source)
    {
      return '<img src="/images/'.$source.'" />';
    }


実際のところ、symfony に組み込まれている `input_tag()` 関数は、ほかの属性を `<input>` タグに追加する3番目のパラメーターを受けとるので、上記のコードよりも少々複雑です。完全な構文とオプションは [API ドキュメント](http://www.symfony-project.org/api/1_4/).で確認できます。

たいていの場合、ヘルパーは賢いので長くて複雑なコードを書かずにすみます。

    [php]
    <?php echo auto_link_text('我々のサイトにお越しください www.example.com') ?>
     => 我々のサイトにお越しください <a href="http://www.example.com">www.example.com</a>

ヘルパーはテンプレートを書く作業工程を円滑にし、パフォーマンスとアクセシビリティの観点から最高の HTML コードを生み出します。つねに無地の HTML を使うことができますが、ヘルパーのほうがより速く書けます。

>**TIP**
>なぜヘルパーが symfony 内部のすべての場所で使われているラクダ記法の規約ではなくアンダースコアの構文にしたがって命名されるのか疑問に思っていらっしゃるかもしれません。これはヘルパーが関数であり、PHP コアの関数がすべてアンダースコアの構文の規約を利用するからです。

#### ヘルパーを宣言する

ヘルパーの定義を含む symfony のファイルはオートロードされません (これらがクラスではなく関数を含むからです)。ヘルパーは目的によって分類されます。たとえば、テキストを扱うすべてのヘルパー関数は `TextHelper.php` という名前のファイルで定義され、`Text` ヘルパーグループと呼ばれます。ですのでヘルパーをテンプレートのなかで使う必要がある場合、`use_helper()` 関数でヘルパーを宣言することで最初の段階で関連のヘルパーグループをロードしなければなりません。リスト7-3は、`Text` ヘルパーグループの一部である `auto_link_text()` ヘルパーを使うテンプレートを示しています。

リスト7-3 ヘルパーの使用を宣言する

    [php]
    // このテンプレートのなかで特定のヘルパーグループを使う
    <?php use_helper('Text') ?>
    ...
    <h1>説明</h1>
    <p><?php echo auto_link_text($description) ?></p>

>**TIP**
>複数のヘルパーグループを宣言する必要がある場合、より多くの引数を `use_helper()` 呼び出しに追加します。たとえば、ヘルパーグループの `Text` と `Javascript` の両方をテンプレートにロードするには、`<?php use_helper('Text', 'Javascript') ?>` を呼び出します。

いくつかのヘルパーは、すべてのテンプレートのなかで宣言を行わずにデフォルトで利用できます。ヘルパーグループのヘルパーはつぎのようなものがあります:

  * `Helper`: ヘルパーのインクルードに必要 (`use_helper()` 関数は、実際、ヘルパーそのもの)
  * `Tag`: ほとんどすべてのヘルパーによって使われる、基本的なタグヘルパー 
  * `Url`: リンクと URL を管理するヘルパー
  * `Asset`: HTML の `<head>` セクションの投入と外部アセット (画像、JavaScript と CSS) への簡単なリンクを提供するヘルパー
  * `Partial`: テンプレートフラグメントをインクルードできるようにするヘルパー
  * `Cache`: キャッシュされたコードフラグメントを操作するヘルパー

すべてのテンプレート用にデフォルトでロードされる、標準ヘルパーのリストは `settings.yml` ファイルのなかで設定できます。`Cache` グループのヘルパーを使わない、もしくは `Text`グループのヘルパーをつねに使うことがわかっている場合、`standard_helpers` 設定をそれぞれ変更します。これによってアプリケーションの動作を少し加速します。前のリストにある最初の4つのヘルパーグループ (`Helper`、`Tag`、`Url`、`Asset`) は削除できません。なぜなら、テンプレートエンジンが適切に動作するために必須だからです。結果として、標準ヘルパーのリストにも表示されません。

>**TIP**
>テンプレート外部でヘルパーを使う必要がある場合、`sfProjectConfiguration::getActive()->loadHelpers($helpers)` を呼び出すことでどこからでもヘルパーグループをロードできます。`$helpers` はヘルパーグループの名前もしくはヘルパーグループ名の配列です。たとえば、アクションのなかで `auto_link_text()` を使いたい場合、`sfProjectConfiguration::getActive()->loadHelpers('Text')` を最初に呼び出す必要があります。

#### よく使われるヘルパー

ヘルパーが助けしてくれる機能に関連する、いくつかのヘルパーに関する詳細な内容は後の章で学ぶことになります。リスト7-4では、ヘルパーが返す HTML コードと一緒に、よく使われるデフォルトのヘルパーの手短な一覧を示しています。

リスト7-4 - デフォルトの共通ヘルパー

    [php]
    // Helper グループ
    <?php use_helper('HelperName') ?>
    <?php use_helper('HelperName1', 'HelperName2', 'HelperName3') ?>

    // Tag グループ
    <?php echo tag('input', array('name' => 'foo', 'type' => 'text')) ?>
    <?php echo tag('input', 'name=foo type=text') ?>  // 代替のオプション構文
     => <input name="foo" type="text" />
    <?php echo content_tag('textarea', 'ダミーの内容', 'name=foo') ?>
     => <textarea name="foo">ダミーの内容</textarea>

    // Urlグループ
    <?php echo link_to('クリックしてください', 'mymodule/myaction') ?>
    => <a href="/route/to/myaction">クリックしてください</a>  // ルーティングの設定による

    // Assetグループ
    <?php echo image_tag('myimage', 'alt=foo size=200x100') ?>
     => <img src="/images/myimage.png" alt="foo" width="200" height="100"/>
    <?php echo javascript_include_tag('myscript') ?>
     => <script language="JavaScript" type="text/javascript" src="/js/myscript.js"></script>
    <?php echo stylesheet_tag('style') ?>
     => <link href="/stylesheets/style.css" media="screen" rel="stylesheet"type="text/css" />

symfony にはほかの多くのヘルパーが存在し、それらすべてを説明するには一冊の本が必要になります。ヘルパーの最良のリファレンスはオンラインの [API ドキュメント](http:// www.symfony-project.org/api/1_4/) です。そこですべてのヘルパーの構文、オプションと例について十分な説明があります。

### 独自のヘルパーを追加する

symfony はさまざまな目的のために多くのヘルパーを搭載していますが、API ドキュメントで必要なものが見つからない場合、新しいヘルパーを作りたいと思うでしょう。これはとても簡単に行うことができます。

ヘルパー関数 (HTML コードを返す通常の PHP 関数)は `FooBarHelper.php` という名前のファイルに保存されます。`FooBar` はヘルパーグループの名前です。`use_helper('FooBar')` ヘルパーによってファイルが自動的に発見されインクルードされるために、ファイルは `apps/frontend/lib/helper/`に保存されます (もしくはプロジェクトの `lib/` フォルダーの1つのもとで作られた `helper/` ディレクトリ)。

>**TIP**
>このシステムによって既存の symfony ヘルパーをオーバーライドすることもできます。たとえば、`Text` ヘルパーグループのすべてのヘルパーを再定義するために、 `TextHelper.php` ファイルを`apps/frontend/lib/helper/` ディレクトリに作ります。`use_helper('Text')` を呼び出すたびに、symfony は固有のヘルパーよりあなたのヘルパーグループを使います。しかしつぎのことに気をつけてください: オリジナルのファイルがロードされない場合、そのファイルをオーバーライドするすべてのヘルパーグループの関数を再定義しなければなりません; さもなければ、いくつかのオリジナルのヘルパーはまったく利用できなくなります。

### ページのレイアウト

リスト7-1で示されているテンプレートには `DOCTYPE` の定義と `<html>` と `<body>` タグが見つからないので、有効な XHTML ドキュメントではありません。これらが、アプリケーションのほかの場所に存在し、ページのレイアウトを含む `layout.php` ファイルに保存されるからです。このファイルは、グローバルレイアウト (global template) とも呼ばれますが、すべてのテンプレートのなかで繰り返しを避けるためにアプリケーションのすべてのページに共通な HTML コードを保存します。テンプレートの内容をレイアウトに統合されます。または見方を変えるなら、レイアウトはテンプレートを「デコレート (装飾)」します。図7-1で説明されるように、これは Decorator デザインパターンのアプリケーションです。

>**TIP**
>Decorator とほかのデザインパターンについて詳しい情報は Martin Fowler(マーチン・ファウラー) が執筆した Patterns of Enterprise Application Architecture (Addison-Wesley、ISBN: 0-32112-742-0) をご覧ください (邦訳は「エンタープライズアプリケーションアーキテクチャパターン」翔泳社、2005年 ISBN 4798105538)。

図7-1 - レイアウトのなかでテンプレートをデコレートする

![レイアウトのなかでテンプレートをデコレートする](http://www.symfony-project.org/images/book/1_4/F0701.png "レイアウトのなかでテンプレートをデコレートする")

リスト7-5は、アプリケーションの `templates/` ディレクトリのなかに設置された、デフォルトのページレイアウトを示しています。

リスト7-5 - デフォルトのレイアウト (`myproject/apps/frontend/templates/layout.php`)

    [php]
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
      <head>
        <?php include_javascripts() ?>
        <?php include_stylesheets() ?>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
      </head>
      <body>
        <?php echo $sf_content ?>
      </body>
    </html>

`<head>` セクションに呼び出されたヘルパーはレスポンスオブジェクトとビューの設定から情報を取得します。`<body>` タグはテンプレートの結果を出力します。このレイアウトでは、デフォルトの設定とリスト7-1のサンプルのテンプレートおよび処理されたビューはリスト7-6のようになります。

リスト7-6 - 組み立てられたレイアウト、ビューの設定とテンプレート

    [php]
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
      <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="title" content="symfony project" />
        <meta name="robots" content="index, follow" />
        <meta name="description" content="symfony project" />
        <meta name="keywords" content="symfony, project" />
        <title>symfonyのプロジェクト</title>
        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <link rel="shortcut icon" href="/favicon.ico">
      </head>
      <body>
        <h1>ようこそ</h1>
        <p>おかえり、<?php echo $name ?>!</p>
        <h2>何をなさいますか？</h2>
        <ul>
          <li><?php echo link_to('最近の記事を読む', 'article/read') ?></li>
          <li><?php echo link_to('新しい記事を書き始める', 'article/write') ?></li>
        </ul>
      </body>
    </html>

それぞれのアプリケーションごとにグローバルテンプレートを完全にカスタマイズできます。必要な HTML コードを追加します。このレイアウトはサイトのナビゲーション、ロゴなどを格納するためによく使われます。複数のレイアウトを用意することも可能で、それぞれのアクションで使われるレイアウトを選ぶことができます。今のところは JavaScript とスタイルシートのインクルードは気にしないでください。この章の「ビューのコンフィギュレーション」のセクションで扱う方法を説明します。

### テンプレートのショートカット

テンプレートにおいて、いくつかの symfony の変数はつねに利用可能です。symfony のコアオブジェクトを通して、これらのショートカットはテンプレートのなかでもっとも共通に必要とされる情報にアクセスできます:

  * `$sf_context`: コンテキスト全体のオブジェクト (`sfContext` のインスタンス)
  * `$sf_request`: リクエストオブジェクト (`sfRequest` のインスタンス)
  * `$sf_params` : リクエストオブジェクトのパラメーター
  * `$sf_user`   : 現在のユーザーセッションのオブジェクト (`sfUser` のインスタンス)

以前の章で `sfRequest` と `sfUser` オブジェクトの便利なメソッドを詳細に説明しました。`$sf_request` と `$sf_user` 変数を通してテンプレートのこれらのメソッドを実際に呼び出すことができます。たとえば、リクエストが `total` パラメーターを含む場合、その変数はつぎのようにテンプレートのなかで利用できます:

    [php]
    // 長いバージョン
    <?php echo $sf_request->getParameter('total') ?>

    // 短いバージョン
    <?php echo $sf_params->get('total') ?>

    // つぎのアクションコードと同等
    echo $request->getParameter('total')

コードのフラグメント
--------------------

いくつかのページで同じ HTML もしくは PHP コードが必要になることがよくあります。コードの繰り返しを避けるには、多くの場合、PHP の `include()` 文で十分です。 

たとえば、アプリケーションの多くのテンプレートが同じコードフラグメントを必要とする場合、グローバルテンプレートのディレクトリ (`myproject/apps/frontend/templates/`) に存在する `myFragment.php` ファイルにフラグメントを保存し、つぎのようにそのファイルをテンプレートでインクルードします:

    [php]
    <?php include(sfConfig::get('sf_app_template_dir').'/myFragment.php') ?>

しかしこれはフラグメントをまとめるにはあまりきれいな方法ではありません。たいていの場合、フラグメントとそれを含むさまざまなテンプレートのあいだで異なる変数名を持つ可能性があるからです。加えて、symfony のキャッシュシステム (12章で説明) はインクルードを検出する方法を持たないので、コードのフラグメントはテンプレートから独立してキャッシュされません。symfony は `include()` 文を置き換えるために、代わりとなる3つのタイプの賢いコードのフラグメントを提供します;

  * ロジックが軽量の場合、テンプレートに渡すデータにアクセスするテンプレートファイルをインクルードするだけです。そのためには、部分テンプレート (partial) を使います。
  * ロジックが重い場合(たとえば、データモデルにアクセスして/もしくはセッションにしたがって内容を修正する場合)、プレゼンテーションをロジックから分離することが望ましいです。そのためには、コンポーネント (component) を使います。
  * コードのフラグメントでレイアウトの特定の部分を置き換えることを目的とする場合、すでに存在しているであろうデフォルトの内容に対して、スロット (slot) を使います。

これらのコードのフラグメントのインクルードは `Partial` グループのヘルパーによって実現されます。これらのヘルパーは、初期化の宣言を行わずに、symfony のどのテンプレートからも利用できます。

### 部分テンプレート

部分テンプレート (partial) は再利用可能なテンプレートコードの塊 (チャンク) です。たとえば、情報公開を行うアプリケーションにおいて、記事を表示するテンプレートのコードは記事の詳細な内容のページに使われ、もっとも人気のある記事と最新の記事の一覧にも使われます。図7-2で示されるように、このコードは部分テンプレートのための完璧な候補です。

図7-2 - テンプレート内で部分テンプレートを再利用する

![テンプレート内で部分テンプレートを再利用する](http://github.com/masakielastic/symfonybook-ja/raw/master/images/F0702.png "テンプレート内で部分テンプレートを再利用する")

テンプレートのように、部分テンプレートは `templates/` ディレクトリに設置されたファイルで、これらは PHP が埋め込まれた HTML コードを含みます。部分テンプレートのファイルの名前はつねにアンダースコア (`_`) で始まります。テンプレートが同じ `templates` フォルダーに設置されているので、これは部分テンプレートとテンプレートを区別するのに役立ちます。

部分テンプレートが同じモジュール、別のモジュール、もしくはグローバルな `templates/` ディレクトリにあったとしても、テンプレートは部分テンプレートをインクルードできます。リスト7-7に書かれているように、`include_partial()` ヘルパーを利用して部分テンプレートをインクルードして、モジュールと部分テンプレート名をパラメーターとして指定します (ただし先頭のアンダースコアと末尾の `.php` を省略します)。 

リスト7-7 - 部分テンプレートを `mymodule` モジュールのテンプレートのなかに含める

    [php]
    // frontend/modules/mymodule/templates/_mypartial1.php 部分テンプレートをインクルードする
    // テンプレートと部分テンプレートは同じモジュールにあるので、
    // モジュール名を省略できる
    <?php include_partial('mypartial1') ?>

    // frontend/modules/foobar/templates/_mypartial2.php 部分テンプレートをインクルードする
    // この場合モジュール名は必須
    <?php include_partial('foobar/mypartial2') ?>

    // frontend/templates/_mypartial3.php 部分テンプレートをインクルードする
    // 'global' モジュールの一部として見なされる
    <?php include_partial('global/mypartial3') ?>

部分テンプレートは symfony の通常のヘルパーとテンプレートのショートカットにアクセスできます。しかし、部分テンプレートはアプリケーションのどこからでも呼び出すことができるので、部分テンプレートをインクルードするテンプレートを呼び出すアクションで定義された変数が明示的に引数として渡されないかぎり、部分テンプレートは変数に自動的にアクセスできません。たとえば、`$total` 変数にアクセスできる部分テンプレートが欲しい場合、リスト7-8、7-9、7-10で示されるように、アクションは変数をテンプレートに渡さなければなりませんし、また、テンプレートは変数を `include_partial()` 呼び出しの2番目の引数としてヘルパーに渡さなければなりません。

リスト7-8 - アクションは変数を定義する (`mymodule/actions/actions.class.php`)

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeIndex()
      {
        $this->total = 100;
      }
    }

リスト7-9 - テンプレートが部分テンプレートに変数を渡す (`mymodule/templates/indexSuccess.php`)

    [php]
    <p>Hello, world!</p>
    <?php include_partial('mypartial', array('mytotal' => $total)) ?>

リスト7-10 - 部分テンプレートは変数を使える (`mymodule/templates/_mypartial.php`)

    [php]
    <p>合計: <?php echo $mytotal ?></p>

>**TIP**
>すべてのヘルパーはこれまで `<?php echo functionName() ?>` によって呼び出されてきました。通常の PHP の `include()` 文と似たようなふるまいをするように、部分テンプレートのヘルパーは、`echo` することなく、`<?php include_partial() ?>` によって簡単に呼び出されます。実際に表示しないで部分テンプレートの内容を返す関数が必要な場合、代わりに`get_partial()` を使います。この章で説明されたすべての `include_` ヘルパーは `echo` 文で一緒に呼び出すことができる `get_` で始まる対のヘルパーを持ちます。

>**TIP**
>テンプレートで終わる代わりに、アクションは部分テンプレートもしくはコンポーネントを返します。アクションクラスの `renderPartial()` と `renderComponent()` メソッドはコードの再利用性を促進します。加えて、それらは部分テンプレートのキャッシュ機能を利用します (12章を参照)。アクションのなかで定義された変数は、変数の連想配列をメソッドの2番目の引数として定義しないかぎり、自動的に部分テンプレート/コンポーネントに渡されます。
>
>     [php]
>     public function executeFoo()
>     {
>       // 何かを行う
>       $this->foo = 1234;
>       $this->bar = 4567;
>
>       return $this->renderPartial('mymodule/mypartial');
>     }
>
>この例では、部分テンプレートは `$foo` と `$bar` にアクセスできるようになります。アクションがつぎのような行で終わる場合:
>
>       return $this->renderPartial('mymodule/mypartial', array('foo' => $this->foo));
>
>部分テンプレートは `$foo` にのみアクセスできるようになります。

### コンポーネント

2章において、最初のサンプルスクリプトはプレゼンテーションからロジックを分離するために2つの部分に分割されました。MVC パターンがアクションとテンプレートに適用されるように、部分テンプレートをロジックとプレゼンテーションの部分に分割する必要があるかもしれません。その場合、コンポーネント (component) を使うべきです。

コンポーネントは、はるかに速く動くこと以外は、アクションと似ています。コンポーネントのロジックは、 `action/components.class.php` ファイルに設置された `sfComponents` から継承したクラスに保存されます。そのプレゼンテーションは部分テンプレートに保存されます。`sfComponents` クラスのメソッドは、アクションと同じように、`execute` という単語で始まり、アクションが変数を渡す方法と同じように、変数をプレゼンテーションの対応物に渡すことができます。コンポーネントに対してプレゼンテーションとして役割を果たす部分テンプレートはコンポーネントによって (先頭の `execute`ではなく、代わりにアンダースコアで) 命名されます。テーブル7-1はアクションとコンポーネントの間の命名規約を比較しています。

テーブル7-1 - アクションとコンポーネントの命名規約

規約                                    |アクション          |コンポーネント
----------------------------------------|---------------------|--------------
ロジックのファイル                     |`actions.class.php`  |`components.class.php`
ロジッククラスの拡張                   |`sfActions`          |`sfComponents`
メソッドの命名方法                     |`executeMyAction()`  |`executeMyComponent()`
プレゼンテーションのファイルの命名方法|`myActionSuccess.php`|`_myComponent.php`

>**TIP**
>アクションのファイルを分割できるのと同様に、`sfComponents` クラスには `sfAction` クラスに該当する `sfComponent` クラスがあり、これによりアクションと同様の構文で一ファイル一コンポーネントが可能になります。

たとえば、任意の題目に対して最新のニュースの見出しを表示するサイドバーを持つことを仮定します。題目はユーザーのプロファイルに依存し、いくつかのページで再利用されます。ニュースの見出しを取得するために必要なクエリは単純な部分テンプレートで表示するには複雑すぎるので、これらをアクションのようなファイル、コンポーネントに移動させる必要があります。図7-3はこの例を図示しています。

リスト7-11と7-12で示された、この例に対して、コンポーネントは独自のモジュール (`news`) に保持されますが、ビューの機能上の観点から意味がある場合、コンポーネントとアクションを単独のモジュールに混ぜることができます。

図7-3 - テンプレートのなかでコンポーネントを使う

![テンプレートのなかでコンポーネントを使う](http://www.symfony-project.org/images/book/1_4/F0703.png "テンプレートのなかでコンポーネントを使う")

リスト7-11 - コンポーネントクラス (`modules/news/actions/components.class.php`)

    [php]
    <?php

    class newsComponents extends sfComponents
    {
      public function executeHeadlines()
      {
        // Propel
        $c = new Criteria();
        $c->addDescendingOrderByColumn(NewsPeer::PUBLISHED_AT);
        $c->setLimit(5);
        $this->news = NewsPeer::doSelect($c);

        // Doctrine
        $query = Doctrine::getTable('News')
                  ->createQuery()
                  ->orderBy('published_at DESC')
                  ->limit(5);

        $this->news = $query->execute();
      }
    }

リスト7-12 - 部分テンプレート (`modules/news/templates/_headlines.php`)

    [php]
    <div>
      <h1>最新のニュース</h1>
      <ul>
      <?php foreach($news as $headline): ?>
        <li>
          <?php echo $headline->getPublishedAt() ?>
          <?php echo link_to($headline->getTitle(),'news/show?id='.$headline->getId()) ?>
        </li>
      <?php endforeach ?>
      </ul>
    </div>

では、コンポーネントがテンプレートのなかで必要になるたびに、つぎのように呼び出します:

    [php]
    <?php include_component('news', 'headlines') ?>

部分テンプレートのように、コンポーネントは連想配列の形式で追加パラメーターを受けとります。パラメーターはこれらの名前のもとで部分テンプレート内および `$this` オブジェクトを通してコンポーネントのなかで利用できます。例としてリスト7-13をご覧ください。

リスト7-13 - パラメーターをコンポーネントとテンプレートに渡す

    [php]
    // コンポーネントの呼び出し
    <?php include_component('news', 'headlines', array('foo' => 'bar')) ?>

    // コンポーネント自身のなか
    echo $this->foo;
     => 'bar'

    // _headlines.php 部分テンプレートのなか
    echo $foo;
     => 'bar'

通常のテンプレートのように、コンポーネント内部、もしくはグローバルレイアウト内部でコンポーネントをインクルードできます。アクションのように、コンポーネントの `execute` メソッドは変数を関連する部分テンプレートに渡し、同じショートカットにアクセスできます。しかし、似ている点はここまでです。コンポーネントはセキュリティもしくはバリデーションを処理せず、インターネットから呼び出すことはできませんし (呼び出せるのはアプリケーション自身からのみ)、さまざまなものを返すことはできません。それがコンポーネントがアクションよりも実行が速い理由です。

### スロット

部分テンプレートとコンポーネントは再利用のために優れたものです。しかし多くの場合、コードのフラグメントは複数の動的な領域を持つレイアウトの要件を満たすことが求められます。たとえば、カスタムタグをレイアウトの `<head>` セクションに追加することを考えます。レイアウトはアクションの内容に依存します。もしくは、レイアウトが主要な動的な領域を1つ持つことを想定します。その領域はアクションの結果によって内容が満たされます。加えて、レイアウトは別の小さな領域をたくさん持ちます。これらの領域はレイアウトで定義されたデフォルトの内容を持ちますが、テンプレートレベルでオーバーライドできます。

これらの状況に対して、解決方法はスロット (slot) です。基本的には、スロットはビューの要素 (レイアウト、テンプレート、もしくは部分テンプレート) に設置できるプレースホルダです。プレースホルダの内容を満たすことは変数の設定に似ています。内容を満たすコードはレスポンスにグローバルに保存されるので、どこでもスロットを定義できます(レイアウト、テンプレート、もしくは部分テンプレート)。スロットをインクルードするまえにかならずスロットを定義してください。そしてレイアウトはテンプレートのあとで実行され (これはデコレーションプロセス)、部分テンプレートがテンプレート内部に呼び出されるときに部分テンプレートが実行されることを覚えておいてください。説明が抽象的でわかりにくいですか？例を見てみましょう。

1つのテンプレートと2つのスロットに対して1つの領域を持つレイアウトを想像してください。1つはサイドバー用で他はフッター用です。スロットの値はテンプレート内で定義されます。デコレーションプロセスの間、図7-4に示されるように、レイアウトのコードはテンプレートのコードを包み、スロットは事まえに定義された値で満たされます。サイドバーとフッターはメインのアクションに対して文脈依存の関係があります。これは複数の「穴」をともなうレイアウトを持つことと似ています。

図7-4 - テンプレートのなかで定義されるレイアウトのスロット

![テンプレートのなかで定義されるレイアウトのスロット](http://www.symfony-project.org/images/book/1_4/F0704.png "テンプレートのなかで定義されるレイアウトのスロット ")

いくつかのコードを読むことで理解が進みます。スロットを含めるには、`include_slot()` ヘルパーを使います。`has_slot()` ヘルパーはスロットが以前定義されていた場合に `true` を返します。そしておまけとしてフォールバックメカニズムを提供します。たとえば、リスト7-14で示されるように、レイアウトとデフォルトの内容のなかにある `sidebar` に対してプレースホルダを定義してください。

リスト7-14 - `sidebar` スロットをレイアウト内部でインクルードする

    [php]
    <div id="sidebar">
    <?php if (has_slot('sidebar')): ?>
      <?php include_slot('sidebar') ?>
    <?php else: ?>
      <!-- default sidebar code -->
      <h1>コンテキスト上の領域</h1>
      <p>この領域はページのメインの内容と関連するリンクと情報を含みます。</p>

    <?php endif; ?>
    </div>

スロットが定義されていない場合、デフォルトの何らかの内容を表示することはとても日常的なことなので、`include_slot` ヘルパーはスロットが定義されたどうかを示すブール値を返します。リスト7-15はコードを簡略化するためにこの戻り値を考慮する方法を示しています。

リスト7-15 - レイアウトのなかで `sidebar` スロットをインクルードする

    [php]
    <div id="sidebar">
    <?php if (!include_slot('sidebar')): ?>
      <!-- default sidebar code -->
      <h1>コンテキスト依存の領域</h1>
      <p>この領域はページのメインの内容に関連する
         リンクと情報を含みます。</p>
    <?php endif; ?>
    </div>

それぞれのテンプレートはスロットの内容を定義する機能を持ちます (実際には、部分テンプレートが行います)。スロットは HTML コードを格納することが想定されているので、symfony はそれらを定義する便利な方法を提供します: リスト7-16のように、`slot()` と `end_slot()` ヘルパーのあいだでスロットのコードを書くだけです。

リスト7-16 - テンプレートのなかで `sidebar` スロットの内容をオーバーライドする

    [php]
    // ...

    <?php slot('sidebar') ?>
      <!-- 現在のテンプレートに対するカスタムサイドバーのコード -->
      <h1>ユーザーの詳細</h1>
      <p>名前:  <?php echo $user->getName() ?></p>
      <p>Eメール: <?php echo $user->getEmail() ?></p>
    <?php end_slot() ?>

スロットヘルパーに挟まれるコードはテンプレートのコンテキスト内で実行されるので、このコードはアクション内部で定義されたすべての変数にアクセスできます。symfony はこのコードの結果を自動的にレスポンスオブジェクトに設置します。これはテンプレート内部では表示されませんが、リスト7-14のような、将来の `include_slot()` 呼び出しに対して利用できます。

スロットはコンテキストの内容を表示することを目的とした領域を定義するためにとても便利です。これらは特定のアクションに対して HTML コードをレイアウトに追加するために利用することも可能です。たとえば、最新のニュースのリストを表示するテンプレートがレイアウトの `<head>` 部分のなかでリンクをRSSフィードに追加したい場合があります。これは `feed` スロットをレイアウトに追加してテンプレートのリストのなかでこのスロットをオーバーライドすることで簡単に実現されます。

スロットの内容がとても短い場合、これはまさにたとえば `title` スロットを定義する事例にあてはまります。リスト7-17で示されるように内容を `slot()` メソッドの2番目の引数として渡すことができます。

リスト7-17 - 短い値を定義するために `slot()` を使う

    [php]
    <?php slot('title', 'タイトルの値') ?>

>**SIDEBAR**
>**テンプレートのフラグメントが見つかる場所**
>
>テンプレートにとり組む人達は通常は Web デザイナーで、symfonyに ついて詳しくないでしょうし、テンプレートのフラグメントはアプリケーション全体で拡散されているので、見つけることは困難でしょう。これらのいくつかのガイドラインによって symfony のテンプレートシステムにとり組む作業が快適になります。
>
>最初に、symfony のプロジェクトは多くのディレクトリを収納していますが、すべてのレイアウト、テンプレート、テンプレートのフラグメントのファイルは `templates/`ディレクトリのなかに存在します。Web デザイナーのために、プロジェクトの構造をつぎのように小さくすることができます:
>
>
>     myproject/
>       apps/
>         application1/
>           templates/       # application 1 のためのレイアウト
>           modules/
>             module1/
>               templates/   # module 1 のためのテンプレートと部分テンプレート
>             module2/
>               templates/   # module 2 のためのテンプレートと部分テンプレート
>             module3/
>               templates/   # module 3 のためのテンプレートと部分テンプレート
>
>
>ほかのディレクトリはすべて無視されます。
>
>`include_partial()` に遭遇するとき、Web デザイナーは最初の引数だけが重要であることを理解する必要があります。この引数の形式は `module_name/partical_name` で、これはプレゼンテーションのコードが `modules/module_name/templates/_partical_name.php` のなかで見つかることを意味します。
>
>`include_component()` ヘルパーに関して、最初の2つの引数はモジュールと部分テンプレートの名前です。残りに関しては、ヘルパーが何でありテンプレートのなかでもっとも共通なヘルパーはどれかといった一般的な理解をしていれば symfony のアプリケーションのためにテンプレートの設計を始めるには十分です。

ビューのコンフィギュレーション
--------------------------------

symfony において、ビューは2つの相異なる部分で構成されます:

  * アクションの結果の HTML プレゼンテーション (テンプレート、レイアウト、そしてテンプレートフラグメントに保存される)
  * 残りのすべて、以下の内容を含む:

    * メタ宣言: キーワード、説明、もしくはキャッシュの持続時間 
    * ページのタイトル: あなたのサイトのページを見つけるためにいくつものウィンドウを開くユーザーに対して役立つだけでなく、サイト検索のためのインデックス作成にも非常に重要。
    * ファイルのインクルージョン: JavaScript とスタイルシートファイル。
    * レイアウト: アクションのなかにはカスタムレイアウト(ポップアップ、広告など)を必要とするもの、もしくはレイアウトをまったく必要としないもの (Ajax アクションなど)が存在する。

ビューのなかにおいて、HTML ではないすべてのものはビューコンフィギュレーションと呼ばれ、symfony はこれを操作するために2つの方法を提供します。通常の方法は `view.yml` 設定ファイルです。このファイルは値がコンテキスト、もしくはデータベースのクエリに依存しないときはつねに使われます。動的な値を設定する必要があるとき、代わりの方法は `sfResponse` オブジェクトの属性を通してビューの設定をアクション内部で直接設定することです。

>**NOTE**
>`sfResponse` オブジェクトと `view.yml` ファイルの両方を通してビューの設定パラメーターを設定した場合、`sfResponse` の定義が優先されます。

### `view.yml` ファイル

それぞれのモジュールはビューの設定を定義する `view.yml`ファイルを1つ持つことができます。これによってモジュール全体に大してかつ単独のファイルでビューごとにビューの設定を定義できます。`view.yml` ファイルの最初のレベルのキーはモジュールのビューの名前です。リスト7-18はビューのコンフィギュレーションの例を示します。

リスト7-18 - モジュールレベルの `view.yml` のサンプル

    editSuccess:
      metas:
        title: プロファイルを編集する

    editError:
      metas:
        title: プロファイルを編集している間に発生したエラー

    all:
      stylesheets: [my_style]
      metas:
        title: 私のWebサイト

>**CAUTION**
>`view.yml` ファイルのなかのメインキーはビューの名前で、アクションの名前ではないことに注意してください。復習として、ビューの名前はアクションの名前とアクションのサフィックスによって構成されます。たとえば、`edit` アクションが `sfView::SUCCESS` を返す場合 (もしくはデフォルトアクションの接尾辞であるので、何も返さない場合)、ビューの名前は `editSuccess` です。

モジュール用のデフォルト設定はモジュールの `view.yml` の `all:` キーのもとで定義されます。すべてのアプリケーションのビューに対するデフォルト設定はアプリケーションの `view.yml` で定義されます。繰り返しますが、設定カスケードの原則を意識してください。

  * `apps/frontend/modules/mymodules/config/view.yml` において、ビュー単位の定義は1つのビューだけに適用され、モジュールレベルの定義をオーバーライドします。
  * `apps/frontend/modules/mymodule/config/view.yml` において、`all:` 定義はすべてのモジュールのアクションに適用され、アプリケーションレベルの定義をオーバーライドします。
  * `apps/frontend/config/view.yml` において、`default:` 定義はアプリケーションのすべてのモジュールとすべてのアクションに適用されます。

>**TIP**
>モジュールレベルの `view.yml` ファイルはデフォルトでは存在しません。最初に、モジュール用のビュー設定パラメーターを調整する必要があり、`config/` ディレクトリのなかで空の `view.yml` を作らなければなりません。

リスト7-5のデフォルトのテンプレートとリスト7-6の最後のレスポンスを見た後に、ヘッダーの値がどこから来るのか疑問に思うかもしれません。実際には、これらはビューのデフォルト設定であり、アプリケーションの`view.yml`で定義され、リスト7-19で示されます。

リスト7-19 - アプリケーションレベルのデフォルトのビューコンフィギュレーション (`apps/frontend/config/view.yml`)

    default:
      http_metas:
        content-type: text/html

      metas:
        #title:        symfony project
        #description:  symfony project
        #keywords:     symfony, project
        #language:     en
        #robots:       index, follow

      stylesheets:    [main]

      javascripts:    []

      has_layout:     true
      layout:         layout

これらの設定はそれぞれ「ビューのコンフィギュレーション設定」のセクションで詳細に説明します。

### レスポンスオブジェクト

ビューレイヤーの一部ではありますが、レスポンスオブジェクトはしばしアクションによって修正されます。アクションは `getResponse()` メソッドを通して `sfResponse` と呼ばれる symfony のレスポンスオブジェクトにアクセスできます。リスト7-20は、アクションのなかでよく使われる `sfResponse` メソッドのいくつかのリストを示しています。

リスト7-20 アクションは `sfResponse` オブジェクトメソッドにアクセスできる

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeIndex()
      {
        $response = $this->getResponse();

        // HTTPヘッダー
        $response->setContentType('text/xml');
        $response->setHttpHeader('Content-Language', 'en');
        $response->setStatusCode(403);
        $response->addVaryHttpHeader('Accept-Language');
        $response->addCacheControlHttpHeader('no-cache');

        // Cookie
        $response->setCookie($name, $content, $expire, $path, $domain);

        // メタ情報とページのヘッダー
        $response->addMeta('robots', 'NONE');
        $response->addMeta('keywords', 'foo bar');
        $response->setTitle('My FooBar Page');
        $response->addStyleSheet('custom_style');
        $response->addJavaScript('custom_behavior');
      }
    }

ここで示されるセッターメソッドに加えて、`sfReponse` クラスはレスポンス属性の現在の値を返すゲッターを持ちます。

symfony ではヘッダーのセッターはとても強力です。(`sfRenderingFilter` で) ヘッダーは可能なかぎり遅く送信されるので、望むだけ多く、そして遅く変更することができます。それらはとても便利なショートカットも提供します。たとえば、`setContentType()` を呼び出すときに charset を指定できない場合、symfony は `settings.yml` ファイルで定義されたデフォルトの charset を自動的に追加します。

    [php]
    $response->setContentType('text/xml');
    echo $response->getContentType();
     => 'text/xml; charset=utf-8'

symfonyの レスポンスのステータスコードは HTTP の仕様と互換性があります。例外の場合はステータス500を返し、ページが見つからない場合はステータス404を返し、通常のページの場合はステータス200を返し、修正されていないページの場合はステータス304と共にシンプルなヘッダーに減らすことができます (詳細は12章を参照)。しかし、`setStatusCode()` レスポンスメソッドを持つアクションのなかで独自のステータスコードを設定することで、これらのデフォルトをオーバーライドできます。カスタムコードとメッセージ、もしくは単なるカスタムコードを指定できます。この場合、symfony はこのコードに対してもっとも共通したメッセージを追加します。

    [php]
    $response->setStatusCode(404, 'このページは存在しません');

>**TIP**
>ヘッダーを送るまえに、symfony はこれらの名前を正規化します。`setHttpHeader()` 呼び出しのなかで`Contetn-Language` の代わりに `content-language` を書くことに悩む必要はありません。syfmony は前者を理解し、自動的に後者に変換するからです。

### ビューのコンフィギュレーション設定

2種類のビューのコンフィギュレーション設定があることにお気づきかもしれません:

  * ユニークな値を持つもの (値は `view.yml` ファイルのなかの文字列で、レスポンスはそれらに対して `set` メソッドを使う)
  * 複数の値を持つもの (`view.yml` は配列を使い、レスポンスは `add` メソッドを使う)

コンフィギュレーションカスケードはユニークな値の設定を削除し複数の値の設定を集積することを覚えておいてください。この章を読み進めればあきらかになります。

### メタタグのコンフィギュレーション

レスポンスの `<meta>` タグに書かれた情報はブラウザーには表示されませんが、ロボットと検索エンジンには役立ちます。これはすべてのページのキャッシュ設定もコントロールします。リスト7-19のように、これらのタグは `view.yml` のなかの `http_metas:` と `metas:` キーの下で定義するか、リスト7-21のように、アクションの `addHttpMeta()` と `addMeta()` レスポンスメソッドでこれらのタグを定義します。

リスト7-21 - `view.yml` のなかの「キー: 値」のペアとしてのメタの定義

    http_metas:
      cache-control: public

    metas:
      description:   Finance in France
      keywords:      finance, France

リスト7-22 - レスポンスアクションのレスポンス設定としてのメタの定義

    [php]
    $this->getResponse()->addHttpMeta('cache-control', 'public');
    $this->getResponse()->addMeta('description', 'Finance in France');
    $this->getResponse()->addMeta('keywords', 'finance, France');

デフォルトでは既存のキーを追加すると現在の内容が置き換えられます。HTTP メタタグに対して、`addHttpMeta()` メソッド (`setHttpHeader()` も同様) は3番目の値を設定することができ、false を指定した場合は既存の値を置き換えるのではなく値を追加します。

    [php]
    $this->getResponse()->addHttpMeta('accept-language', 'en');
    $this->getResponse()->addHttpMeta('accept-language', 'fr', false);
    echo $this->getResponse()->getHttpHeader('accept-language');
     => 'en, fr'

これらのメタタグが最終的なドキュメントに表示されるように、`include_http_metas()` と `include_metas()` ヘルパーは `<head>` セクションのなかで呼び出されなければなりません (これはデフォルトのレイアウトの場合; リスト7-5を参照)。適切な `<meta>` タグを出力するために symfony はすべての `view.yml` ファイル (リスト7-18で示されているデフォルトのものを含む) とレスポンス属性から自動的に設定を集約します。リスト7-21の例はリスト7-23で示されているような状態で終わります。

リスト7-23 - 最終的なページのメタタグの出力

    [php]
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="cache-control" content="public" />
    <meta name="robots" content="index, follow" />
    <meta name="description" content="Finance in France" />
    <meta name="keywords" content="finance, France" />

おまけとして、レスポンスの HTTP ヘッダーは、レイアウトに `include_http_metas()` ヘルパーが存在しないもしくはレイアウトがまったくない場合、`http-metas:` の定義にも影響されます。たとえば、ページをプレーンテキストとして送る必要がある場合、つぎのように `view.yml` を定義します:

    http_metas:
      content-type: text/plain

    has_layout: false

### タイトルのコンフィギュレーション

ページタイトルは検索エンジンのインデックス作業の重要な部分です。タブ機能を提供するモダンなブラウザーでも非常に便利です。HTML において、タイトルはページのタグとメタの両方の情報であるので、`view.yml` ファイルは`title:` キーを `metas:` キーの子として探します。リスト7-24は `view.yml` のタイトル定義を示し、リスト7-25はアクション定義を示します。

リスト7-24 - `view.yml` のタイトル定義

    indexSuccess:
      metas:
        title: Three little piggies

リスト7-25 - アクションのなかのタイトルの定義 -- 動的なタイトルを可能にする

    [php]
    $this->getResponse()->setTitle(sprintf('%d little piggies', $number));

最終的なドキュメントの `<head>` セクションにおいて、`<title>` タグタイトルの定義は、`include_metas()` ヘルパーが存在する場合は `<meta name="title">` タグを、`include_title()` ヘルパーが存在する場合は `<title>` タグを設定します。両方が含まれる場合(リスト7-5のデフォルトのレイアウトなど)、タイトルはドキュメントのソース内で2回表示されますが (リスト7-6を参照)、これは無害です。

>**TIP**
>タイトルを定義するもう一つ別の方法はスロットを使うことです。スロットを使うことでより上手にコントローラーとテンプレートを分離しておくことができます:それはページのタイトルはビューに関係するものでありコントローラーに関係するものではないからです。

### ファイルのインクルードコンフィギュレーション

リスト7-26が示すように、特定のスタイルシートもしくは JavaScript ファイルをビューに追加することは簡単です。

リスト7-26 - アセットファイルのインクルード

    // view.yml のなか
    indexSuccess:
      stylesheets: [mystyle1, mystyle2]
      javascripts: [myscript]

    // アクション のなか
    $this->getResponse()->addStylesheet('mystyle1');
    $this->getResponse()->addStylesheet('mystyle2');
    $this->getResponse()->addJavascript('myscript');

    // テンプレートのなか
    <?php use_stylesheet('mystyle1') ?>
    <?php use_stylesheet('mystyle2') ?>
    <?php use_javascript('myscript') ?>
    
それぞれの場合、引数はファイル名です。ファイルが正しい拡張子を持つ場合 (`.css` はスタイルシート、`.js` はJavaScript ファイル)、これを省略することができます。ファイルが正しい位置 (`/css/` はスタイルシート、`/js/` は JavaScript ファイル)にある場合、同様に省略できます。symfony は正しい拡張子、もしくは位置を理解するぐらい十分に賢いです。

メタとタイトルの定義とは異なり、ファイルのインクルード定義はテンプレートのヘルパーもしくは含まれるレイアウトを必要としません。このことは以前の設定はテンプレートとレイアウトの内容が何であれ、リスト7-27の HTML コードを出力することを意味します。

リスト7-27 - ファイルのインクルードの結果 -- レイアウトのなかではヘルパーの呼び出しに必要ない

    [php]
    <head>
    ...
    <link rel="stylesheet" type="text/css" media="screen" href="/css/mystyle1.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/mystyle2.css" />
    <script language="javascript" type="text/javascript" src="/js/myscript.js">
    </script>
    </head>

>**NOTE**
>レスポンスではスタイルシートと JavaScript のインクルードは `sfCommonFilter` という名前のフィルターによって実行されます。これはレスポンスの `<head>` タグを探し、`</head>` を丁度閉じるまえに`<link>` タグと `<script>` タグを追加します。レイアウトもしくはテンプレートのなかに `<head>` タグが存在しない場合、インクルードが行われないことを意味します。

設定カスケードの原則が適用されるので、アプリケーションの `view.yml` のなかで定義された任意のファイルインクルードはアプリケーションのすべてのページで現れます。リスト7-28、7-29、7-30はこの原則のお手本を示しています。

リスト7-28 - アプリケーションの `view.yml` のサンプル

    default:
      stylesheets: [main]

リスト7-29 - モジュールの `view.yml` のサンプル

    indexSuccess:
      stylesheets: [special]

    all:
      stylesheets: [additional]

リスト7-30 - `indexSuccess` ビューの結果

    [php]
    <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/additional.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/special.css" />

より高いレベルで定義されたファイルを除外することが必要がある場合、リスト7-31で示されるように、より低いレベルの定義で、マイナス記号(`-`)をファイルの名前のまえに追加します。

リスト7-31 - サンプルモジュールの `view.yml` はアプリケーションレベルで定義されたファイルをとり除く

    indexSuccess:
      stylesheets: [-main, special]

    all:
      stylesheets: [additional]

すべてのスタイルシートもしくは JavaScript ファイルを除外するには、リスト7-32で示されるように、`-*` をファイル名として使います。

リスト7-32 - アプリケーションレベルで定義されるすべてのファイルを除外するモジュールの `view.yml` のサンプル

    indexSuccess:
      stylesheets: [-*]
      javascripts: [-*]

リスト7-33で示されるように、ファイルをインクルードする位置 (最初もしくは最後の位置) を強制するためにより正確な追加のパラメーターを定義することができます。これはスタイルシートとJavaScriptの両方に対して機能にします。

リスト7-33 - インクルードされるアセットの位置を定義する

    // view.ymlのなか
    indexSuccess:
      stylesheets: [special: { position: first }]

    // アクションのなか
    $this->getResponse()->addStylesheet('special', 'first');
    
    // テンプレートのなか
    <?php use_stylesheet('special', 'first') ?>

リスト7-34で示されるように、結果の `<link>` もしくは `<script>` のタグが正しい指定位置を参照するように、アセットファイル名の変形を回避することを決めることもできます。

リスト7-34 - そのままの名前でスタイルシートをインクルードする

    // view.ymlのなか
    indexSuccess:
      stylesheets: [main, paper: { raw_name: true }]

    // アクションのなか
    $this->getResponse()->addStylesheet('main', '', array('raw_name' => true));
    
    // テンプレートのなか
    <?php use_stylesheet('main', '', array('raw_name' => true)) ?>
    
    // ビューの結果
    <link rel="stylesheet" type="text/css" media="print" href="main" />

リスト7-35で示されるように、スタイルシートのインクルードに対して media を指定するために、スタイルシートのタグのデフォルトオプションを変更できます。

リスト7-35 - media を指定してスタイルシートをインクルードする

    // view.yml のなか
    indexSuccess:
      stylesheets: [main, paper: { media: print }]

    // アクション のなか
    $this->getResponse()->addStylesheet('paper', '', array('media' => 'print'));
    
    // テンプレートのなか
    <?php use_stylesheet('paper', '', array('media' => 'print')) ?>
    
    // 結果のビュー
    <link rel="stylesheet" type="text/css" media="print" href="/css/paper.css" />

>**SIDEBAR**
>view.yml ファイルを使ってアセットファイルを利用するときの注意
>
>標準で使用するスタイルシートとjavascriptファイルの指定を行う一番良い方法はプロジェクトのview.ymlで指定し、別のスタイルシートやJavascriptファイルが必要な場合はヘルパーを使用してテンプレート内で指定することです。この場合、既に読み込み指定しているものを取り除いたり置き換えないようにしなければなりません、こうすることがとても苦痛に感じる場面があるでしょう。

### レイアウトのコンフィギュレーション

Web サイトではグラフィカルな表にしたがってレイアウトが複数存在します。古典的な Web サイトは少なくとも2つ持ちます: デフォルトのレイアウトとポップアップのレイアウトです。

デフォルトのレイアウトが `myproject/apps/frontend/templates/layout.php` であることはすでに見ました。追加のレイアウトは同じグローバルな `templates/` ディレクトリに追加しなければなりません。`frontend/templates/my_layout.php` ファイルでビューが欲しい場合、リスト7-36で示されるつぎの構文を使います。 

リスト7-36 - レイアウトの定義

    // view.ymlのなか
    indexSuccess:
      layout: my_layout

    // アクションのなか
    $this->setLayout('my_layout');
    
    // テンプレートのなか
    <?php decorate_with('my_layout') ?>

ビューのなかにはレイアウトがまったく必要のないものがあります(たとえば、RSSフィード上のプレーンテキストのページ)。この場合、リスト7-37で示されるように、`has_layout` を `false` にセットしてください。

リスト7-37 - レイアウトの除去

    // `view.yml` のなか
    indexSuccess:
      has_layout: false

    // アクションのなか
    $this->setLayout(false);
    
    // テンプレートのなか
    <?php decorate_with(false) ?>

>**NOTE**
>デフォルトでは Ajax のアクションのビューはレイアウトを持ちません。

出力エスケーピング機能
----------------------

動的なデータをテンプレートに挿入するとき、データの統合性について気をつけなければなりません。たとえば、データが匿名ユーザーから入力されたフォームから来た場合、クロスサイトスクリプティング (XSS - Cross-Site Scripting) 攻撃を開始する悪意のあるスクリプトを含んでいるリスクがあります。出力データに含まれるHTMLタグが無害になるように、出力データをエスケープできることが必須です。

例として、ユーザーが入力フィールドをつぎのような値で満たすことを想像してください:

    [php]
    <script>alert(document.cookie)</script>

警告なしでこの値を `echo` する場合、JavaScript はすべてのブラウザーで実行され、アラートを表示するよりはるかに危険な攻撃を許してしまいます。値をつぎのように変換するために、表示まえに値をエスケープしなければならない理由はそういうわけです:

    [php]
    &lt;script&gt;alert(document.cookie)&lt;/script&gt;

`htmlspecialchars()` の呼び出しで信頼性のないすべての値を囲むことで出力を手動でエスケープできますが、このアプローチは繰り返し作業がとても多くエラーが起こりやすいです。代わりに symfony は出力エスケーピング (output escaping) と呼ばれる特別なシステムを提供します。これはテンプレート内のすべての変数出力を自動的にエスケープします。これはアプリケーションの `settings.yml` で設定でき、標準で有効になっています。

### 出力エスケーピング機能を有効にする

出力エスケーピング機能は `settings.yml` のなかでアプリケーションに対してグローバルに設定されます。2つのパラメーターが出力エスケーピングのふるまいをコントロールします: エスケーピング戦略 (`escaping_strategy`) がビューのなかで変数を利用できるようにする方法を決定し、エスケーピングメソッド (`escaping_method`) はデータに適用されるデフォルトのエスケーピング機能です。

基本的に、出力エスケーピング機能を有効にするために必要なことは、リスト7-38で示されるように、`escaping_strategy` パラメーターの値を`true` (標準の設定が `true`) にセットすることです。

リスト7-38 - 出力エスケーピング機能を有効にする (`frontend/config/settings.yml`)

    all:
      .settings:
        escaping_strategy: true
        escaping_method:   ESC_SPECIALCHARS

デフォルトではこれはすべての変数の出力に`htmlspeciachars()`を追加します。たとえば、つぎのようにアクションで`test`変数を定義することを想像してみましょう:

    [php]
    $this->test = '<script>alert(document.cookie)</script>';

出力エスケーピング機能を有効にした場合、この変数をテンプレートのなかで `echo` するとエスケープされたデータが出力されます:

    [php]
    echo $test;
     => &lt;script&gt;alert(document.cookie)&lt;/script&gt;

また、すべてのテンプレートは `$sf_data` 変数にアクセスできます。これはエスケープされたすべての変数を参照するコンテナオブジェクトです。ですのでつぎのように `test` 変数を出力することもできます:

    [php]
    echo $sf_data->get('test');
    => &lt;script&gt;alert(document.cookie)&lt;/script&gt;

>**TIP**
>`$sf_data` オブジェクトは Array インターフェイスを実装するので、`$sf_data->get('myvariable')`を使う代わりに、`$sf_data['myvariable']` を呼び出すことでエスケープされた値をとり出すことができます。しかし、これは本当の配列ではないので、`print_r()` のような関数は期待どおりに動きません。

`$sf_data` によってエスケープされていないもしくは生データにもアクセスできます。変数を信用することが前提になりますが、このことは変数がブラウザーに解釈されること想定した HTML コードを保存するときに便利です。生データを出力することが必要なとき、`getRaw()` メソッドを呼び出します。

    [php]
    echo $sf_data->getRaw('test');
     => <script>alert(document.cookie)</script>

実際に HTML として解釈される HTML を含む変数を必要とするたびに生データにアクセスしなければなりません。

`escaping_strategy` が `false` のとき、`$sf_data` はまだ使えますが、つねに名前のデータを返します。

### エスケーピングヘルパー

エスケーピングヘルパー (escaping helper) は入力のエスケープバージョンを返す機能です。これらは `settings.yml` ファイルのなかでデフォルトの `escaping_method` として、もしくはビューのなかで特定の値のためのエスケーピングメソッドを指定するために提供されます。つぎのエスケーピングヘルパーが利用できます:

  * `ESC_RAW`: 値をエスケープしません。
  * `ESC_SPECIALCHARS`: PHP 関数の `htmlspecialchars()` を入力に適用する。
  * `ESC_ENTITIES`: 引用形式として `ENT_QUOTES` で PHP関数の `htmlentities()` を入力に適用します。
  * `ESC_JS`: HTML として使われる予定の JavaScript の文字列のなかに入る値をエスケープする。これは JavaScript の利用によって HTML が動的に変更されるものをエスケープするために便利です。
  * `ESC_JS_NO_ENTITIES`: エンティティを追加しないが JavaScript の文字列のなかに入る値をエスケープする。これはダイアログボックスを利用して値を表示する場合に便利です (たとえば、`javascript:alert(myString);` のなかで使われる `myString` 変数)。

### 配列とオブジェクトをエスケープする

出力エスケーピングは文字列だけでなく、配列とオブジェクトに対しても機能します。オブジェクトもしくは配列である値はそれらのエスケープされた状態をそれらの子供に渡します。`escaping_strategy` を `true` にセットしたと仮定すると、リスト7-39はエスケーピングカスケードのお手本を示しています。

リスト7-39 - エスケーピング機能は配列とオブジェクトに対しても作用する

    [php]
    // クラスの定義
    class myClass
    {
      public function testSpecialChars($value = '')
      {
        return '<'.$value.'>';
      }
    }

    // アクションのなか
    $this->test_array = array('&', '<', '>');
    $this->test_array_of_arrays = array(array('&'));
    $this->test_object = new myClass();

    // テンプレートのなか
    <?php foreach($test_array as $value): ?>
      <?php echo $value ?>
    <?php endforeach; ?>
     => &amp; &lt; &gt;
    <?php echo $test_array_of_arrays[0][0] ?>
     => &amp;
    <?php echo $test_object->testSpecialChars('&') ?>
     => &lt;&amp;&gt;

実際のところ、テンプレート内部の変数はあなたが期待した型ではありません。出力エスケーピングシステムはこれらを「デコレート」して、特別なオブジェクトに変換します:

    [php]
    <?php echo get_class($test_array) ?>
     => sfOutputEscaperArrayDecorator
    <?php echo get_class($test_object) ?>
     => sfOutputEscaperObjectDecorator

これはエスケープされた配列では通常の PHP 関数 (`array_shift()`、`print_r()`などの) がもはや機能しない理由を説明しています。しかし、まだこれらは `[]` を使うことでアクセスすることが可能で、`foreach` を使うことで展開され、これらは `count()` で正しい結果を返します。そしてテンプレートにおいて、ともかくデータはリードオンリーなので、たいていのアクセスは実際に動作するメソッドを通したものになります。

`$sf_data` オブジェクトを通して生データをとり出す方法がまだあります。加えて、エスケープされたオブジェクトのメソッドは追加パラメーター: エスケーピングメソッドを受けとるために変更されます。テンプレート変数を表示するたびに代わりのエスケーピングメソッド、もしくはエスケーピングを無効にする `ESC_RAW` ヘルパーを選ぶことができます。リスト7-40の例をご覧ください。

リスト7-40 - エスケープされたオブジェクトのメソッドは追加パラメーターを受けとる

    [php]
    <?php echo $test_object->testSpecialChars('&') ?>
    => &lt;&amp;&gt;
    // つぎの3行は同じ値を返す
    <?php echo $test_object->testSpecialChars('&', ESC_RAW) ?>
    <?php echo $sf_data->getRaw('test_object')->testSpecialChars('&') ?>
    <?php echo $sf_data->get('test_object', ESC_RAW)->testSpecialChars('&') ?>
     => <&>

テンプレートのなかでオブジェクトを処理する場合、追加パラメーターのトリックを多く利用することになります。これがメソッド呼び出しで生データを得るための最速の方法だからです。

>**CAUTION**
>出力エスケーピングを有効にした場合、symfony の通常の変数もエスケープされます。`$sf_user`、`$sf_request`、`$sf_param` と `$sf_context` はそのまま機能しますが、これらが持つメソッド呼び出しに `ESC_RAW` を最後の引数として追加しないかぎり、メソッドはエスケープされたデータを返すことを覚えておいてください。

>**TIP**
>たとえ XSS がセキュリティ上のもっとも共通する弱点の1つであったとしても、これは唯一の弱点ではありません。CSRF はとても有名なので syfmony はフォームの保護機能を自動的に提供します。

まとめ
----

プレゼンテーションレイヤー (presentation layer) を操作するためにあらゆる種類のツールを利用できます。ヘルパー (helper) のおかげで、テンプレート (template) を秒単位で作れます。レイアウト (layout)、部分テンプレート (partial - もしくはパーシャル)とコンポーネント (component) によってモジュール性と再利用性の両方がもたらされます。ビュー (view) の設定においてたいていのページヘッダーを処理するために速く書ける YAML を利用します。コンフィギュレーションカスケード (configuration cascade)によってビューごとにすべての設定を定義しないですみます。動的なデータに依存するプレゼンテーションをすべて修正するために、アクションは `sfResponse` オブジェクトにアクセスできます。そして出力エスケーピングシステム (output escaping system) のおかげで、ビューはクロスサイトスクリプティング (XSS - Cross-Site Scripting) 攻撃から安全です。
