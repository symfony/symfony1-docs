第9章 - リンクとルーティングシステム
====================================

リンクと URL は Web アプリケーションのフレームワークにおいて特別な扱いをする価値があります。アプリケーション唯一のエントリーポイント (フロントコントローラー) とヘルパーを利用することで URL の動作方法とそれらの表現方法を完全に分離できるようになります。この機能はルーティング (routing) と呼ばれます。ルーティングはアプリケーションをガジェットよりもユーザーフレンドリーでセキュアにするための便利なツールです。この章では symfony のアプリケーションで URL を扱うために知る必要があることをすべてお伝えします。

  * ルーティングシステムとは何か、またそれがどのように動作するのか
  * 対外的な URL のルーティングを有効にするためにテンプレートのなかでリンクヘルパーを使う方法
  * URL の表示方法を変更するためにルーティングルールを変更する方法

ルーティングのパフォーマンスと最後の仕上げを習得するためにいくつかのトリックを見ることにもなります。

ルーティングとは何か？
----------------------

ルーティング (routing) とは URL をユーザーフレンドリーに書き換えるメカニズムです。しかし、なぜこれが重要なのかを理解するには、URL について数分考えなければなりません。

### サーバーへの命令としてのURL

URL はユーザーが望むアクションを成立させるためにブラウザーからの情報をサーバーに運びます。たとえば、つぎの例のように、伝統的な URL はリクエストを完了させるために必要なスクリプトへのファイルパスとパラメーターを含みます:

    http://www.example.com/web/controller/article.php?id=123456&format_code=6532

この URL はアプリケーションのアーキテクチャとデータベースに関する情報を運びます。通常、開発者はインターフェイス内のアプリケーションのインフラを隠します(たとえば、開発者は "QZ7.65" よりも "個人のプロファイルページ" のようなページタイトルを選びます)。アプリケーション内部への重要な手がかりをURLに露出することはこの努力に相反することで重大な欠陥を晒すことになります。

  * URL に表示される技術上のデータは潜在的なセキュリティの欠点を作ります。前の例において、悪意のあるユーザーが `id` パラメーターの値を変更したら何が起こるでしょうか？アプリケーションがデータベースへの直接のインターフェイスを提供することは何を意味するのでしょうか？もしくはユーザーが面白半分にほかのスクリプト名、たとえば `admin.php` を試したらどうなるでしょうか？一般的に、生の URL を使うとアプリケーションを簡単に不正利用する手段を提供することになるので、これらの方法によってセキュリティの管理がほとんど不可能になります。
  * 不明瞭な URL を使うと、どこに表示されようともうっとおしく、周囲の内容の印象が希薄になります。そして今日において、URL はアドレバーだけに表示されません。検索結果と同様に、これらはユーザーがリンクの上にマウスをホーバーしたときにも表示されます。ユーザーが情報を探すとき、図9-1のようなややこしいURLよりも、ユーザーが探しものを簡単に見つけられるようにする手がかりを与えたいと開発者は願うことでしょう。

図9-1 - URL は検索結果などの多くの場所で表示される

![URL は検索結果などの多くの場所で表示される](http://github.com/masakielastic/symfonybook-ja/raw/master/images/F0901.png "URLは検索結果などの多くの場所で表示される")

  * URL の1つを変更しなければならない場合 (たとえば、スクリプト名もしくはパラメーターの1つが修正される場合)、この URL へのすべてのリンクも同様に変更しなければなりません。コントローラー構造の修正作業は重量級で高くつくので、アジャイル開発において理想的ではありません。

symfony がフロントコントローラーのパラダイムを利用しない場合、事態はもっと悪化する可能性があります; すなわち、つぎのように、多くのディレクトリのなかで、インターネットからアプリケーションにアクセスできるスクリプトがたくさん含まれている場合です:

    http://www.example.com/web/gallery/album.php?name=my%20holidays
    http://www.example.com/web/weblog/public/post/list.php
    http://www.example.com/web/general/content/page.php?name=about%20us

この場合、開発者は URL の構造をファイル構造とマッチさせる必要があり、どちらかの構造を変更したときに、結果は悪夢のようなメンテナンス作業になります。

### インターフェイスの一部としての URL

ルーティングの背後にあるアイディアは URL をインターフェイスの一部としてみなすことです。アプリケーションはユーザーに情報をもたらすためにURLを整形し、ユーザーはアプリケーションのリソースにアクセスするためにURLを利用します。

これは symfony のアプリケーションで実現可能です。エンドユーザーに表示される URL はリクエストを実行するために必要なサーバーへの命令とは無関係だからです。代わりに、リクエストされるリソースに URLを関連づけして、自由に形式を整えることができます。たとえば、symfony はつぎの URL を理解しこの章の最初の URL のように同じページを表示できます:

    http://www.example.com/articles/finance/2006/activity-breakdown.html

恩恵は計り知れません:

  * URL は実際に何かを意味するので、これはユーザーがリンクの背後にあるページのなかに望むものが含まれるかどうかを判断するための助けになります。リンクは返すリソースの詳細内容を追加できます。これはとりわけ検索エンジンの結果に対して役立ちます。加えて、URL は時にページタイトルの参照なしに表示されるので (URL をメールのメッセージにコピーするときを考えてください)、この場合、その URL はそれ自身が何らかの意味を持たなければなりません。ユーザーフレンドリーな URL の例に関しては図9-2をご覧ください。

図9-2 - 刊行日のように、URL はページに関する追加情報を運ぶ

![刊行日のように、URL はページに関する追加情報を運ぶ](http://github.com/masakielastic/symfonybook-ja/raw/master/images/F0902.png "刊行日のように、URL はページに関する追加情報を運ぶ")

  * 紙のドキュメントに書かれている URL は入力しやすく覚えやすいものにすべきです。あなたの名刺に会社の Web サイトが`http://www.example.com/controller/web/index.jsp?id=ERD4`と記載されていたら、多くの訪問者を得ることはないでしょう。
  * URLは、  直感的な方法でアクションを実行するもしくは情報を読みとるために、独自のコマンドラインになることができます。このような機能を提供するアプリケーションによってパワーユーザーは作業をより速くできるようになります。

        // 結果のリスト: 結果のリストを絞るために新しいタグを追加する
        http://del.icio.us/tag/symfony+ajax
        // ユーザープロファイルのページ: 別のユーザープロファイルを取得するために名前を変更する
        http://www.askeet.com/user/francois

  * URLの整形方法とアクションの名前/パラメーターを、それぞれ個別に修正することで、変更できます。全体的に、アプリケーションをゴチャゴチャにすることなく、最初に開発をしたあとで URL の形式を整えることができます。
  * アプリケーションの内部を再編成するときでも、URLは外部の世界に対して同じ状態を保つことができます。動的なページをブックマークできるようになるので、URLの永続性は必須です。
  * 検索エンジンは Web サイトをインデックスに登録するとき、(`.php`、`.asp` などで終わる) 動的なページを無視しがちです。検索エンジンが動的なページに遭遇したときでも、静的な内容をブラウジングしていると考えさせるために URL の形式を整えることができます。結果としてインデックスに登録されるアプリケーションのページの内容がよくなります。
  * より安全です。承認されていない URL を閲覧しようとすると開発者が指定したページにリダイレクトされるので、ユーザーが試しに URL を入力しても Webroot のファイル構造を閲覧することはできません。リクエストによって呼び出された実際のスクリプト名は、そのパラメーターと同様に、隠匿されています。

ユーザーと実際のスクリプト名とリクエストパラメーターに表示される URL 間の対応は設定を通して修正できるパターンに基づいた、ルーティングシステムによって実現されます。

>**NOTE**
>アセット (asset) はどうでしょうか？幸運にも、URL のアセット(画像、スタイルシートと JavaScript) はブラウジングの間は大量に表示されないので、これらに対してルーティングを実際に設定する必要はありません。symfony において、すべてのアセットは `web/` ディレクトリに設置され、URL はファイルシステムの設置場所に一致します。しかしながら、アセットヘルパー内部で生成されたURLを利用することで (アクションによって処理された) 動的なアセットを管理できます。たとえば、動的に生成された画像を表示するには、`image_tag('captcha/image?key='.$key)` ヘルパーを使います。

### どのように動作するのか

symfony は外部 URL と内部 URI を切り離します。これら2つを対応させる作業はルーティングシステムによって行われます。わかりやすくするために、symfony は通常の URL の構文とよく似た内部 URI のための構文を使います。リスト9-1は例を示しています。

リスト9-1 - 外部 URL と内部 URI

    // 内部の URI 構文
    <module>/<action>[?param1=value1][&param2=value2][&param3=value3]...

    // 内部 URI の例で、エンドユーザーに決して表示されない
    article/permalink?year=2006&subject=finance&title=activity-breakdown

    // 外部 URL の例で、エンドユーザーに表示される
    http://www.example.com/articles/finance/2006/activity-breakdown.html

ルーティングシステムを定義するには `routing.yml`という名前の特別な設定ファイルを使います。リスト9-2で示されているルールを考えます。このルールは `articles/*/*/*` のようなパターンを定義し、ワイルドカードとマッチする内容の一部を命名します。

リスト9-2 - サンプルのルーティングルール

    article_by_title:
      url:    articles/:subject/:year/:title.html
      param:  { module: article, action: permalink }

symfony のアプリケーションに送信されるすべてのリクエストは最初ルーティングシステムによって分析されます (単独のフロントコントローラーによって処理されるのでシンプルです)。ルーティングシステムはリクエストされた URL とマッチするルーティングルールで定義されたパターンを探します。マッチするパターンが見つかった場合、名前つきのワイルドカードはリクエストパラメーターになり `param:` キーで定義されたものと統合されます。どのように動くのかはリスト9-3をご覧ください。

リスト9-3 - ルーティングシステムはやってくるリクエスト URL を解釈する

    // ユーザーがつぎの外部 URL を入力する(クリックする)
    http://www.example.com/articles/finance/2006/activity-breakdown.html

    // フロントコントローラーはリクエスト URL が article_by_title ルールにマッチするか調べる
    // ルーティングシステムはつぎのリクエストパラメーターを作成する
      'module'  => 'article'
      'action'  => 'permalink'
      'subject' => 'finance'
      'year'    => '2006'
      'title'   => 'activity-breakdown'

>**TIP**
>外部 URL の `.html` 拡張子はシンプルな飾りつけでルーティングシステムは無視します。その唯一の利点は動的なページを静的なページに見えるようにすることです。この章の後のほうにある"ルーティングの設定"でこの拡張子を有効にする方法を見ることになります。

リクエストは `article` モジュールの `permalink`アクションに渡されます。アクションは表示する記事を決定するためにリクエストパラメーターで求められたすべての情報を格納します。

しかしながらメカニズムはまったく逆のことも行わなければなりません。リンクに外部 URL を表示するアプリケーションに対して、どのルールを適用するのか決定するには十分なデータを持つルーティングルールを提供しなければなりません。ルーティングを完全に無視する `<a>` タグで直接ハイパーリンクを書いてはなりません。代わりに、リスト9-4で示されるように特別なヘルパーを使います。

リスト9-4 - ルーティングシステムはテンプレート内部 URL の出力形式を整える

    [php]
    // url_for() ヘルパーは内部 URI を外部 URL に変換する
    <a href="<?php echo url_for('article/permalink?subject=finance&year=2006&title=activity-breakdown') ?>">ここをクリック</a>

    // ヘルパーは URI が article_by_title ルールにマッチすることを見る
    // ルーティングシステムはそれから外部 URL を作成する
     => <a href="http://www.example.com/articles/finance/2006/activity-breakdown.html">ここをクリック</a>

    // link_to() ヘルパーは直接ハイパーリンクを出力し
    // PHP と HTML を混在させることを回避する
    <?php echo link_to(
      'ここをクリック',
      'article/permalink?subject=finance&year=2006&title=activity-breakdown') ?>
    ) ?>

    // 内部では、link_to() は url_for() を呼び出すので結果はつぎのものと同じ
    => <a href="http://www.example.com/articles/finance/2006/activity-breakdown.html">ここをクリック</a>

ルーティングは2通りのメカニズムであり、すべてのリンクの形式を整える`link_to()`ヘルパーを使う場合のみに機能します。

URL を書き換える
-----------------

ルーティングシステムに深く関わるまえに、1つのことをあきらかにする必要があります。以前のセクションで示された例において、内部 URI のフロントコントローラー (`index.php` もしくは `frontend_dev.php`) の説明をしていません。フロントコントローラーは、アプリケーションの要素ではありませんが、環境を決定します。ですのですべてのリンクは環境に依存しなければならず、フロントコントローラー名は内部 URL に決して現れることはありません。

生成された URL の例にはスクリプト名が存在しません。デフォルトの運用環境では生成URLはスクリプト名を含まないからです。`settings.yml` ファイルの `no_script_name` パラメーターは生成URLのなかでフロントコントローラー名の表示を正確にコントロールします。リスト9-5で示されるように、このパラメーターを `off` に設定すれば、リンクヘルパーによる URL の出力はすべてのリンクにフロントコントローラーの名前を記載します。

リスト9-5 - URL 内部でフロントコントローラーの名前を表示する (`apps/frontend/settings.yml`)

    prod:
      .settings
        no_script_name:  off

生成 URL はつぎのように示されます:

    http://www.example.com/index.php/articles/finance/2006/activity-breakdown.html

運用環境を除いたすべての環境において、`no_script_name` パラメーターはデフォルトでは `off`に設定されます。たとえば、開発環境のアプリケーションをブラウザーで見るとき、フロントコントローラー名はつねに URL に表示されます。

    http://www.example.com/frontend_dev.php/articles/finance/2006/activity-breakdown.html

運用環境において、`no_script_name` パラメーターは `on` に設定されるので、URL はルーティング情報だけを示し、よりユーザーフレンドリーです。技術的な情報は現れません。

    http://www.example.com/articles/finance/2006/activity-breakdown.html

しかしながら、アプリケーションはどのフロントコントローラーが呼び出されるのかをどのように知るのでしょうか？それは URL の書き換えが行われる場所です。URL のなかに何も存在しないときに、Web サーバーが任意のスクリプトを呼び出すために設定できます。

Apache において、これはいったん `mod_rewrite` 拡張機能を有効にすれば可能です。symfonyのすべてのプロジェクトは `.htaccess` ファイルを備えており、このファイルは `web` ディレクトリのために `mod_rewrite` 設定をサーバーの設定に追加します。このファイルのデフォルトの内容はリスト9-6で示されています。

リスト9-6 - Apache のためのデフォルトの書き換えルール (`myproject/web/.htaccess`)

    <IfModule mod_rewrite.c>
      RewriteEngine On

      # 拡張子 .something をもつすべてのファイルをスキップする
      RewriteCond %{REQUEST_URI} \..+$
      RewriteCond %{REQUEST_URI} !\.html$
      RewriteRule .* - [L]

      # .html バージョンがここ (キャッシュ) であるか確認する
      RewriteRule ^$ index.html [QSA]
      RewriteRule ^([^.]+)$ $1.html [QSA]
      RewriteCond %{REQUEST_FILENAME} !-f

      # いいえ、Web のフロントコントローラーにリダイレクトする
      RewriteRule ^(.*)$ index.php [QSA,L]
    </IfModule>

Web サーバーは受けとった URL の形式を検査します。URL がサフィックスを含まず、かつ利用可能なページのキャッシュバージョンが存在しない場合、リクエストは `index.php` に渡されます (12章でキャッシュを説明します)。

しかしながら、symfony プロジェクトの `web/` ディレクトリはプロジェクトのすべてのアプリケーションと環境のあいだで共有されます。これは通常の場合 `web` ディレクトリにあるフロントコントローラーが複数存在することを意味します。たとえば、`frontend` アプリケーションと `backend` アプリケーション、と `dev` 環境と `prod` 環境を持つプロジェクトは4つのフロントコントローラーのスクリプトを `web/` ディレクトリに含みます:

    index.php         // prodのfrontend
    frontend_dev.php  // devのfrontend
    backend.php       // prodのbackend
    backend_dev.php   // devのbackend

mod_rewrite の設定はデフォルトのスクリプトの名前だけを指定します。すべてのアプリケーションと環境に対して `no_script_name` を `on` にセットする場合、すべての URL は `prod` 環境の `frontend` アプリケーションへのリクエストとして解釈されます。これが任意のプロジェクトに対して URL の書き換えを利用できる1つの環境を持つアプリケーションを1つだけ持つことができる理由です。

>**TIP**
>スクリプトの名前を持たないアプリケーションを複数持つ方法が1つあります。Webroot でサブディレクトリを作り、フロントコントローラーをこれらの内部に移動させます。`ProjectConfiguration` ファイルへのパスを変更し、それぞれのアプリケーションに対して必要な `.htaccess`  ファイルの URL 書き換え設定を作ります。

リンクヘルパー
---------------

ルーティングシステムに対して、テンプレート内では通常の `<a>` タグの代わりにリンクヘルパーを使うべきです。これをやっかいな問題と見なさず、むしろ、アプリケーションをきれいな状態に保ち維持しやすくするための機会として見てください。加えて、リンクヘルパーはとても便利で見逃せないショートカットをいくつか提供します。

### ハイパーリンク、ボタン、フォーム

`link_to()` ヘルパーはすでにご存じのとおりです。このヘルパーは XHTML 準拠のハイパーリンクを出力し、2つのパラメーター: クリック可能な要素とそれが指し示すリソースの内部 URI を必要とします。ハイパーリンクの代わりにボタンが欲しい場合、`button_to()`  ヘルパーを使います。フォームも `action` 属性の値を管理するヘルパーを持ちます。つぎの章でフォームを詳しく学びます。リスト9-7はリンクヘルパーのいくつかの例を示します。

リンク 9-7 - `<a>`、`<input>`、`<form>` タグのためのリンクヘルパー

    [php]
    // 文字列上のハイパーリンク
    <?php echo link_to('my article', 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France">my article</a>

    // 画像上のハイパーリンク
    <?php echo link_to(image_tag('read.gif'), 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France"><img src="/images/read.gif" /></a>

    // ボタンタグ
    <?php echo button_to('my article', 'article/read?title=Finance_in_France') ?>
     => <input value="my article" type="button"onclick="document.location.href='/routed/url/to/Finance_in_France';" />

    // フォームタグ
    <?php echo form_tag('article/read?title=Finance_in_France') ?>
     => <form method="post" action="/routed/url/to/Finance_in_France" />

絶対 URL (`http://`で始まり、ルーティングシステムで無視される) とアンカーと同じように、リンクヘルパーは内部 URI を受けとります。実際の世界のアプリケーションにおいて、内部URIは動的なパラメーターで作られます。リスト9-8はこれらすべての事例を示します。

リスト9-8 - リンクヘルパーが受けとる URL

    [php]
    // 内部のURI
    <?php echo link_to('my article', 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France">my article</a>

    // 動的なパラメーターを持つ内部 URI
    <?php echo link_to('my article', 'article/read?title='.$article->getTitle()) ?>

    // アンカーを持つ内部 URI
    <?php echo link_to('my article', 'article/read?title=Finance_in_France#foo') ?>
     => <a href="/routed/url/to/Finance_in_France#foo">my article</a>

    // 絶対 URL
    <?php echo link_to('my article', 'http://www.example.com/foobar.html') ?>
     => <a href="http://www.example.com/foobar.html">my article</a>

### リンクヘルパーのオプション

7章で説明したように、ヘルパーは、連想配列もしくは文字列である追加オプション引数を受けとります。リスト9-9で示されているように、これはリンクヘルパーにもあてはまります。

リスト9-9 - リンクヘルパーは追加オプションを受けとる

    [php]
    // 連想配列としての追加オプション
    <?php echo link_to('my article', 'article/read?title=Finance_in_France', array(
      'class'  => 'foobar',
      'target' => '_blank'
    )) ?>

    // 文字列としての追加オプション(同じ結果)
    <?php echo link_to('my article', 'article/read?title=Finance_in_France','class=foobar target=_blank') ?>
     => <a href="/routed/url/to/Finance_in_France" class="foobar" target="_blank">my article</a>

リンクヘルパーに対して symfony 固有のオプション (`confirm` と `popup`) の1つも追加できます。リスト9-10で示されるように、最初のオプションはリンクがクリックされたときに JavaScript の確認ダイアログボックスが表示され、2番目のオプションは新しいウィンドウにリンクが開かれます。

リスト9-10 - リンクヘルパー用の `'confirm'` オプションと `'popup'` オプション

    [php]
    <?php echo link_to('アイテムを削除する', 'item/delete?id=123', 'confirm=Are you sure?') ?>
     => <a onclick="return confirm('よろしいですか？');"
           href="/routed/url/to/delete/123.html">delete item</a>

    <?php echo link_to('カートに追加する', 'shoppingCart/add?id=100', 'popup=true') ?>
     => <a onclick="window.open(this.href);return false;"
           href="/fo_dev.php/shoppingCart/add/id/100.html">カートに追加する</a>

    <?php echo link_to('カートに追加する', 'shoppingCart/add?id=100', array(
      'popup' => array('popupWindow', 'width=310,height=400,left=320,top=0')
    )) ?>
     => <a onclick="window.open(this.href,'popupWindow','width=310,height=400,left=320,top=0');return false;"
           href="/fo_dev.php/shoppingCart/add/id/100.html">カートに追加する</a>

これらのオプションを結びつけることができます。

### フェイクの GET と POST オプション

Web 開発者は POST を行うために実際には GET リクエストを使うことがあります。たとえば、つぎの URL を考えてみてください:

    http://www.example.com/index.php/shopping_cart/add/id/100

このリクエストは品物をショッピングカートのオブジェクトに追加することで、アプリケーションに含まれるデータを変更します。そしてデータはセッションもしくはデータベースに保存されます。この URL はブックマークされ、キャッシュされ、検索エンジンのインデックスに登録されます。すべての嫌なことがデータベースもしくはこのテクニックを使う Web サイトの評価指標に起こることを想像してみてください。当然のことながら、このリクエストは POST として見なされるべきです。なぜなら検索エンジンのロボットはインデックスの上では POST リクエストを行わないからです。

symfony は `link_to()` ヘルパー、もしくは `button_to()` ヘルパーの呼び出しを実際の POST リクエストに変換する方法を提供します。リスト9-11で示されるように、`post=true` オプションを追加するだけです。

リスト9-11 -リンク呼び出しを POST リクエストにする

    [php]
    <?php echo link_to('ショッピングカートに移動する', 'shoppingCart/add?id=100', 'post=true') ?>
     => <a onclick="f = document.createElement('form'); document.body.appendChild(f);
                    f.method = 'POST'; f.action = this.href; f.submit();return false;"
           href="/shoppingCart/add/id/100.html">ショッピングカートに移動する</a>

この `<a>` タグは `href` 属性を持ち、検索エンジンのロボットなどの、JavaScript サポートを持たないブラウザーはデフォルトのGETメソッドを行いながらリンクを辿ります。POST メソッドだけに応答するように、つぎのようなコードをアクションの始めに追加することで、アクションを制限しなければなりません:

    [php]
    $this->forward404Unless($this->getRequest()->isMethod('post'));

このオプションによって独自の `<form>` タグが生成されるので、フォームに設置されたリンクの上でこのオプションが使われていないことを確認してください(訳注：`<form></form>` のなかにさらに `<form></form>` が生成されるのでフォームが入れ子になります)。

実際にデータを投稿するリンクをPOSTとしてタグ付けするのはよい習慣です。

### リクエストパラメーターを GET 変数として強制する

ルーティングルールに従えば、`link_to()` ヘルパーにパラメーターとして渡された変数はパターンに変換されます。`routing.yml` ファイルで内部 URI にマッチするルールが存在しない場合、リスト9-12で示されるように、デフォルトのルールが `module/action?key=value` を `/module/action/key/value` に変換します。

リスト9-12 - デフォルトのルーティングルール

    [php]
    <?php echo link_to('my article', 'article/read?title=Finance_in_France') ?>
    => <a href="/article/read/title/Finance_in_France">my article</a>

実際に GET 構文を維持する必要がある場合 --リクエストパラメーターを ?key=value 形式で渡す場合 --  `query_string` オプションのなかで、URL パラメーターの外部で強制する必要のある変数を設置する必要があります。
これが URL アンカーにも衝突するので、これを内部 URI に追加する代わりに `anchor` オプションに追加しなければなりません。リスト9-13で示されるように、すべてのリンクヘルパーはこれらのオプションを受けとります。

リスト9-13 - `query_string` オプションで GET 変数を強制する

    [php]
    <?php echo link_to('my article', 'article/read', array(
      'query_string' => 'title=Finance_in_France',
      'anchor' => 'foo'
    )) ?>
    => <a href="/article/read?title=Finance_in_France#foo">my article</a>

GET 変数として表現されるリクエストパラメーターを持つ URL はクライアントサイド上のスクリプト、サーバーサイド上の `$_GET` 変数と `$_REQUEST` 変数によって解釈されます。

>**SIDEBAR**
>**アセットヘルパー**
>
>7章でアセットヘルパーの `image_tag()`、`stylesheet_tag()` と `javascript_include_tag()` を紹介しました。これらのヘルパーによって画像、スタイルシート、JavaScript ファイルをレスポンスに含めることができます。これらのアセットへのパスはルーティングルールによって処理されません。これらは Web 用の公開ディレクトリの元に実際に設置されたレスポンスにリンクするからです。
>
>アセットのためにファイルの拡張子を記載する必要はありません。symfony は自動的に `.png`、`.js`、もしくは `.css` の拡張子を画像、JavaScript もしくはスタイルシートのヘルパー呼び出しに追加します。また、symfony は `web/images/` ディレクトリ、`web/js/` ディレクトリと `web/css/` ディレクトリでこれらのアセットを自動的に探します。もちろん、特定のファイルフォーマットもしくは特定の場所からのファイルを含めたい場合、正式なファイル名もしくはファイルパスを引数として使います。
>`alt_title` は両方の属性を同じ値に設定するので、`alt` と `title` の両方の属性を指定することに悩む必要はありません。これはクロスブラウザー用のツールチップに便利です。symfony 1.2 に関してバリデーターを利用して見つからないタグの検索を楽にするために `alt` 属性はファイルの名前から自動的に推測されないことに注意してください (これを再度有効にするには、`sf_compat_10` を`on` に切り替えます)。
>
>     [php]
>     <?php echo image_tag('test', 'alt=Test') ?>
>     <?php echo image_tag('test.gif', 'title=Test') ?>
>     <?php echo image_tag('/my_images/test.gif', 'alt_title=Test') ?>
>      => <img href="/images/test.png" alt="Test" />
>         <img href="/images/test.gif" title="Test" />
>         <img href="/my_images/test.gif" alt="Test" title="Test" />
>
>画像のサイズを修正するには `size` 属性を使います。これは `x` で区切られたピクセル単位の幅、高さを必要とします。
>
>     [php]
>     <?php echo image_tag('test', 'size=100x20')) ?>
>      => <img href="/images/test.png" width="100" height="20"/>
>
>(JavaScript とスタイルシート) アセットを `<head>` セクションのなかに含めたい場合、レイアウトのなかで `_tag()` バージョンを使う代わりに、テンプレートのなかで `use_stylesheet()` ヘルパーと `use_javascript()` ヘルパーを使います。これらのヘルパーはレスポンスにアセットを追加し、`</head>` タグがブラウザーに送られるまえにこれらのアセットはインクルードされます。

### 絶対パスを使う

デフォルトでリンクヘルパーとアセットヘルパーは相対パスを生成します。絶対パスへの出力を強制するには、リスト9-14で示されるように、`absolute` オプションを `true` にセットします。このテクニックはリンクをメールメッセージ、RSS フィード、API レスポンスに含めるために便利です。

リスト9-14 - 相対 URL の代わりに絶対 URL を取得する

    [php]
    <?php echo url_for('article/read?title=Finance_in_France') ?>
     => '/routed/url/to/Finance_in_France'
    <?php echo url_for('article/read?title=Finance_in_France', true) ?>
     => 'http://www.example.com/routed/url/to/Finance_in_France'

    <?php echo link_to('finance', 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France">finance</a>
    <?php echo link_to('finance', 'article/read?title=Finance_in_France','absolute=true') ?>
     => <a href=" http://www.example.com/routed/url/to/Finance_in_France">finance</a>

    // 同じことがアセットヘルパーにあてはまる
    <?php echo image_tag('test', 'absolute=true') ?>
    <?php echo javascript_include_tag('myscript', 'absolute=true') ?>

>**SIDEBAR**
>**メールヘルパー**
>
>今日において、Eメール収集ロボットが Web を徘徊するので、一日以内にスパムの餌食にならずにすむメールアドレスを表示することはできません。symfony が `mail_to` ヘルパーを提供する理由はそういうことです。
>
>`mail_to()` ヘルパーは２つのパラメーターを受けとります: 実際のメールアドレスと表示される文字列です。追加オプションは HTML では全く読めない何かを出力する `encode` パラメーターを受けとります。ブラウザーはこれを理解できますがロボットは理解できません。
>
>     [php]
>     <?php echo mail_to('myaddress@mydomain.com', 'contact') ?>
>      => <a href="mailto:myaddress@mydomain.com'>contact</a>
>     <?php echo mail_to('myaddress@mydomain.com', 'contact', 'encode=true') ?>
>      => <a href="&#109;&#x61;... &#111;&#x6d;">&#x63;&#x74;... e&#115;&#x73;</a>
>
>エンコードされたメールメッセージはランダムな10進法と16進法のエンティティエンコーダーによって変換された文字列で構成されます。このトリックは現在のアドレス収集するたいていのスパムボットを停止させますが、収集テクニックは急速に発展していることをご了承ください。

ルーティングの設定
------------------

ルーティングシステムは2つの仕事を行います:

  * モジュール/アクションとリクエストパラメーターを決定するために、やってくるリクエストの外部 URL を解釈し、内部 URI に変換します。
  * リンクで使われている内部 URI を外部 URL の形式に整形します (リンクヘルパーを使っていることが前提)。

規約はルーティングルールのセットに基づいています。これらのルールはアプリケーションの `config/` ディレクトリに設置された `routing.yml` 設定ファイルに保存されます。リスト9-15はすべての symfony に搭載されたデフォルトのルーティングルールを示しています。

リスト9-15 - デフォルトのルーティングルール (`frontend/config/routing.yml`)

    # デフォルトのルール
    homepage:
      url:   /
      param: { module: default, action: index }

    default_symfony:
      url:   /symfony/:action/*
      param: { module: default }

    default_index:
      url:   /:module
      param: { action: index }

    default:
      url:   /:module/:action/*

### ルールとパターン

ルーティングルールは外部URLと内部URI間の全単射の関係 (bijective associations) です。典型的なルールはつぎのように構成されます:

  * ユニークなラベル。これは読みやすさと速さのためにあり、リンクヘルパーのために使われます
  * マッチするパターン (`url` キー)
  * リクエストパラメーターの値の配列 (`param` キー)

パターンはワイルドカード (アスタリスクの `*` で表現される) と名前つきのワイルドカード (コロン、`:`で始まる) を含むことができます。名前つきのワイルドカードへのマッチはリクエストパラメーターの値になります。たとえば、リスト9-15で定義された `default` ルールは `/foo/bar` といった URL にマッチし、`module` パラメーターを`foo`に、`action` パラメーターを `bar` に設定します。そして `default_symfony` ルールにおいて、`symfony` はキーワードで、`action` は名前つきのワイルドカードパラメーターです。

>**NOTE**
>**symfony 1.1 の新しい機能** 名前つきのワイルドカードはスラッシュもしくはドットで分離できるので、つぎのようなパターンを書けます:
>
>     my_rule:
>       url:   /foo/:bar.:format
>       param: { module: mymodule, action: myaction }
>
>この方法では、`foo/12.xml` のような外部 URL は `my_rule` にマッチして `$bar=12` と `$format=xml` の2つのパラメーターを持つ `mymodule/myaction` を実行します。
>`sfPatternRouting` ファクトリ設定で `segment_separators` パラメーターを変更することで区切り文字を追加できます (19章を参照)。

ルーティングシステムは `routing.yml` ファイルを上から順に解析し、最初にマッチした時点で止まります。これが独自のルール群をデフォルトのルールの上に追加しなければならない理由です。たとえば、URL の `/foo/123` はリスト9-16で定義されたルールの両方にマッチしますが、symfony は最初 `my_rule:` をテストして、ルールがマッチする場合、`default:` をテストしません。(`foo/123` アクションではなく) `123`に設定された `bar` を持つ `mymodule/myaction` アクションでリクエストが処理されます。

リスト9-16 - ルールは上から下へ順に解析される

    my_rule:
      url:   /foo/:bar
      param: { module: mymodule, action: myaction }

    # デフォルトのルール
    default:
      url:   /:module/:action/*

>**NOTE**
>新しいアクションを作るとき、そのためのルーティングルールを作らなければならないということにはなりません。デフォルトの `module/action` パターンがあなたの用途に合う場合、`routing.yml` ファイルは忘れてください。しかしながら、アクションの外部 URL をカスタマイズしたい場合、デフォルトルールの上に新しいルールを追加します。

リスト9-17は `article/read` アクションの URL の外部形式の変更プロセスを示しています。

リスト9-17 - `article/read` アクションの URL の外部形式を変更する

    [php]
    <?php echo url_for('article/read?id=123') ?>
     => /article/read/id/123       // デフォルトのフォーマッティング

    // これを /article/123 に変更するため、routing.yml の始めに
    // 新しいルールを追加する
    article_by_id:
      url:   /article/:id
      param: { module: article, action: read }

問題はリスト9-17の `article_by_id` ルールは `article` モジュールのほかのすべてのアクション用のデフォルトのルーティングを壊すことです。実際、`article/delete` のような URL は `default` ルールの代わりにこのルールにマッチし、`delete` アクションの代わりに `delete` に設定された `id` で `read` アクションを呼び出します。この問題を回避するには `article_by_id` ルールにパターンの制約を追加することで、ワイルドカードである `id` が整数の URL のときだけにマッチするように設定する必要があります。

### パターンの制約

URL が複数のルールにマッチするとき、制約もしくは要件をパターンに追加することでルールを洗練させなければなりません。要件 (requirements) は正規表現のセットでマッチするルールのためにワイルドカードによってマッチされなければなりません。

たとえば、`id` パラメーターが整数である URL だけにマッチするように `article_by_id` ルールを修正するには、リスト9-18で示されるように、ルールに1行追加します。

リスト9-18 - ルーティングルールに要件 (requirements) を追加する

    article_by_id:
      url:   /article/:id
      param: { module: article, action: read }
      requirements: { id: \d+ }

`article/delete` の URL は `article_by_id` にはマッチしません。`'delete'` の文字列は要件を満たさないからです。それゆえ、ルーティングシステムはつぎのルールでマッチするものを探し続け、最後には `default` ルールを見つけます。

>**SIDEBAR**
>**パーマリンク**
>
>ルーティングのためのよいセキュリティのガイドラインは主キーを隠し、これらを可能なかぎり重要な文字列で置き換えることです。記事の ID ではなくタイトルから記事にアクセスしたい場合はどうしますか？これを行うにはつぎのような外部 URL になります:
>
>     http://www.example.com/article/Finance_in_France
>
>この範囲に対して、新しい `permalink` アクションを作成する必要があります。このアクションは `id` パラメーターの代わりに `slug` パラメーターを使い、新しいルールを追加します:
>
>     article_by_id:
>       url:          /article/:id
>       param:        { module: article, action: read }
>       requirements: { id: \d+ }
>
>     article_by_slug:
>       url:          /article/:slug
>       param:        { module: article, action: permalink }
>
>`permalink` アクションはタイトルからリクエストされた記事を決定する必要があるので、モデルは適切なメソッドを提供しなければなりません。
>
>     [php]
>     public function executePermalink(sfWebRequest $request)
>     {
>       $article = ArticlePeer::retrieveBySlug($request->getParameter('slug');
>       $this->forward404Unless($article);  // 記事がスラッグにマッチしない場合404を表示する
>       $this->article = $article;          // オブジェクトをテンプレートに渡す
>     }
>
>内部 URI の正しいフォーマッティングを有効にするには、テンプレートのなかの `read` アクションへのリンクを `permalink` アクションへのリンクに置き換えることも必要です。
>
>     [php]
>     // つぎのコードを
>     <?php echo link_to('my article', 'article/read?id='.$article->getId()) ?>
>
>     // つぎのコードに置き換える
>     <?php echo link_to('my article', 'article/permalink?slug='.$article->getSlug()) ?>
>
>`requirements` の行のおかげで、`article_by_id` ルールが最初に現れたとしても、`/article/Finance_in_France` のような外部URLが `article_by_slug` ルールにマッチします。
>
>slug によって記事が読みとられるので、データベースのパフォーマンスを最適化するにはインデックスを `Article` モデルの記述内容の `slug` カラムに追加すべきです。

### デフォルト値を設定する

パラメーターが定義されていなくても、ルールを機能させるために名前つきのワイルドカードにデフォルト値を渡すことができます。`param:` 配列のなかでデフォルト値を設定します。

たとえば、`id` パラメーターが設定されていない場合 `article_by_id` ルールはマッチしません。リスト9-19で示されるように、強制することができます。

リスト9-19 - ワイルドカードに対してデフォルト値を設定する

    article_by_id:
      url:          /article/:id
      param:        { module: article, action: read, id: 1 }

デフォルトのパラメーターはパターンのなかで見つかるワイルドカードである必要はありません。リスト9-20において、`display` パラメーターは URL に表示されなくても `true` の値をとります。

リスト9-20 - リクエストパラメーターのデフォルト値を設定する

    article_by_id:
      url:          /article/:id
      param:        { module: article, action: read, id: 1, display: true }

注意深く見ると、パターン内で見つからない変数 `module` と変数 `action` に対して `article` と `read` がそれぞれのデフォルト値であることがわかります。

>**TIP**
>`sfRouting::setDefaultParameter()` メソッドを呼び出すことですべてのルーティングルール用のデフォルトパラメーターを定義できます。たとえば、デフォルトで `theme` パラメーターを `default` に設定するすべてのルールが欲しい場合、`$this->context->getRouting()->setDefaultParameter('theme', 'default');`  をグローバルフィルターの1つに追加します。

### ルールの名前を利用してルーティングを加速する

リスト9-21で示されるように、ルールラベルが'at'記号 (`@`) のまえに来る場合、リンクヘルパーはモジュール/アクションの組の代わりにルールラベルを受けとります。

リスト9-21 - モジュール/アクションの代わりにルールラベルを使う

    [php]
    <?php echo link_to('my article', 'article/read?id='.$article->getId()) ?>

    // つぎのように書くこともできる
    <?php echo link_to('my article', '@article_by_id?id='.$article->getId()) ?>

このトリックに関してよい点とわるい点があります、よい点はつぎのとおりです:

  * 内部URIの整形が速く行われます。symfony はリンクにマッチするルールを見つけるためにすべてのルールを探す必要がないからです。ルーティングされたハイパーリンクをたくさん持つページにおいて、モジュール/アクションの組の代わりにルールラベルを使う場合、速度の押し上げは顕著です。
  * ルールラベルを使うことはアクションの背後にあるロジックを抽象化するための助けになります。アクション名を変更するが URL はそのままにする場合、`routing.yml` ファイルのなかで変更を行うだけで十分です。すべての`link_to()` 呼び出しはさらに変更しなくても機能します。
  * 呼び出しロジックはルールの名前で明確になります。モジュールとアクションが明確な名前を持つ場合でも、`article/display` よりも `@display_article_by_slug` を呼び出したほうがベターです。

一方で、わるい点は、新しいハイパーリンクを追加することが自明ではなくなることです。アクションに対してどのラベルが使われているのか解明するために `routing.yml` ファイルにつねに参照する必要があるからです。

最良の選択はプロジェクト次第です。結局は、あなた次第です。

>**TIP**
>テストの間 (`dev` 環境)、 ブラウザーの任意のリクエストに対してどのルールがマッチしたのかチェックしたい場合、Web デバッグツールバーの「logs and msgs」セクションを展開し、「matched route XXX.」と書かれている行を探してください。Web デバッグモードに関する詳細な情報は16章で知ることになります。

-

>**NOTE**
>**symfony 1.1 の新しい機能** 運用モードでは外部 URL と内部 URI の間の変換がキャッシュされるので、ルーティングオペレーションはずっと速くなります。

### .html拡張子を追加する

つぎの2つのURLを比較してください:

    http://myapp.example.com/article/Finance_in_France
    http://myapp.example.com/article/Finance_in_France.html

同じページであっても、ユーザー (とロボット) は URL なのでこれを違うものとして見るかも知れません。2番目の URL は静的なページの深くてよく整理された Web ディレクトリを呼び出します。静的なページは検索エンジンがインデックスを作成する方法を理解している Web サイトの種類のものです。

リスト9-22で示されるように、サフィックスをルーティングシステムによって生成されたすべての外部 URL に追加するには、`settings.yml` ファイルの `suffix` の値を変更します。

リスト9-22 - すべての URL に対してサフィックスを設定する (`frontend/config/settings.yml`)

    prod:
      routing:
        param:
          suffix: .html

デフォルトのサフィックスはピリオド (`.`) に設定されます。このことはあなたが接尾辞を指定しないかぎりルーティングシステムは接尾辞を追加しないことを意味します。 

時に、唯一のルーティングルールのためにサフィックスを指定する必要があります。その場合、リスト9-23で示されるように、接尾辞を `routing.yml` ファイルの `url:` の関連行に直接書きます。グローバルな接尾辞は無視されます。

リスト9-23 - URL に対してサフィックスを設定する (`frontend/config/routing.yml`)

    article_list:
      url:          /latest_articles
      param:        { module: article, action: list }

    article_list_feed:
      url:          /latest_articles.rss
      param:        { module: article, action: list, type: feed }

### routing.yml なしでルールを作成する

たいていの設定ファイルにあてはまることですが、`routing.yml` ファイルはルーティングルールを定義するための解決方法ですが、唯一の方法ではありません。アプリケーションの`config.php`ファイル、もしくはフロントコントローラースクリプトのなかで、しかし`dispatch()`を呼び出すまえに、ルールをPHPで定義できます。なぜなら、このメソッドは現在のルーティングルールにしたがって実行するアクションを決定するからです。ルールをPHPで定義することは、設定もしくはほかのパラメーターに依存する、動的なルールを作成することを許可することを意味します。

ルーティングルールを扱うオブジェクトは `sfPatternRouting` ファクトリです。`sfContext::getInstance()->getRouting()` を求めることでコードのすべての部分から利用できます。このオブジェクトの `prependRoute()` メソッドは `routing.yml` のなかで定義された既存のルールの上に新しいルールを追加します。このメソッドは4つのパラメーターを必要とします。このパラメーターはルールを定義するために必要なものと同じです: ルートのラベル、パターン、デフォルト値の連想配列、と要件のための別の連想配列です。たとえば、リスト9-18で示される `routing.yml` ルールの定義はリスト9-24で示される PHP コードと同等です。

>**NOTE**
>**symfony 1.1 の新しい機能**: ルーティングクラスは `factories.yml` 設定ファイルで設定可能です (デフォルトのルーティングクラスを変更するには、17章を参照)。この章では `sfPatternRouting` クラスを説明します。このクラスはデフォルトで設定されるルーティングルールです。

リスト9-24 - ルールを PHP で定義する

    [php]
    sfContext::getInstance()->getRouting()->prependRoute(
      'article_by_id',                                  // ルートの名前
      '/article/:id',                                   // ルートパターン
      array('module' => 'article', 'action' => 'read'), // デフォルト値
      array('id' => '\d+'),                             // 要件
    );

Singleton の `sfPatternRouting` は手動でルートを扱うために便利なほかのメソッド、`clearRoutes()`、`hasRoutes()` などを持ちます。もっと学ぶには API ドキュメント ([http://www.symfony-project.org/api/1_2/](http://www.symfony-project.org/api/1_2/)) を参照してください。

>**TIP**
>いったんこの本で説明された概念を十分に理解し始めたら、オンラインの API ドキュメント、もっとよいのは symfony のソースを眺めることで、フレームワークの理解を深めることができます。この本では symfony の調整方法とパラメーターのすべては説明されていません。しかしながら、オンラインドキュメントは無制限です。

アクションのなかでルートを処理する
-------------------------------

現在のルート情報を読みとりたい場合、たとえば「back to page xxx」リンクを用意するには、`sfPatternRouting` オブジェクトのメソッドを使います。リスト9-25で示されるように、`getCurrentInternalUri()` メソッドによって返される URI は、`link_to()` ヘルパーの呼び出しで使われます。

リスト9-25 - 現在のルート情報を読みとるために `sfRouting` オブジェクトを使う

    [php]
    // つぎのような URL を求める場合
    http://myapp.example.com/article/21

    $routing = sfContext::getInstance()->getRouting();

    // つぎの article/read アクションを使う
    $uri = $routing->getCurrentInternalUri();
     => article/read?id=21

    $uri = $routing->getCurrentInternalUri(true);
     => @article_by_id?id=21

    $rule = $routing->getCurrentRouteName();
     => article_by_id

    // 現在の module/action 名が必要なだけなら
    // これらが実際のリクエストパラメーターであることを覚えておく
    $module = $request->getParameter('module');
    $action = $request->getParameter('action');

テンプレートのなかの `url_for()` ヘルパーのように内部 URI を外部 URL に変換する必要がある場合、リスト9-26で示されている `sfController` オブジェクトの `genUrl()` メソッドを使います。

リスト9-26 - 内部URIを変換するために `sfController` オブジェクトを使う

    [php]
    $uri = 'article/read?id=21';

    $url = $this->getController()->genUrl($uri);
     => /article/21

    $url = $this->getController()->genUrl($uri, true);
    => http://myapp.example.com/article/21

まとめ
----

ルーティング (routing) は外部 URL の形式をよりユーザーフレンドリーにするために設計された2つの方法を持つメカニズムです。それぞれのプロジェクトの1つのアプリケーションの URL 内部でフロントコントローラーの名前を省略できるようにするには URL の書き換え (URL  rewriting)が必要です。ルーティングシステムが両方の方法で機能することを望むのであれば、URL をテンプレート内部に出力する必要があるたびにリンクヘルパーを使わなければなりません。`routing.yml` ファイルはルーティングシステムのルールを設定し、優先順位とルールの要件 (requirements) を使います。`settings.yml` ファイルはフロントコントローラーの名前と外部 URL で可能なプレフィックスの存在に関する追加設定を格納します。
