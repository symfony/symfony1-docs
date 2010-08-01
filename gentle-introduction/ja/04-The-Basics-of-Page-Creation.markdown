第4章 - ページ作成の基本
========================

不思議なことに、プログラマが新しい言語やフレームワークを学ぶとき、最初に学ぶチュートリアルは「Hello,world!」をスクリーンに表示させることです。人工知能の分野におけるあらゆる試みが貧弱な会話しかできない現状を終わっていることを考えると、コンピュータが世界全体にあいさつさせよう考えるのは奇妙なことです。しかし、symfonyはほかのプログラムよりも愚かではなく、その証明として、「Hello, `<あなたのお名前>`」を表示するページを作ることができます。

この章ではモジュールの作り方を学習します。モジュール (module) とは複数のページをひとまとめに扱う構造上の要素です。MVC パターンに則って、アクションとテンプレートに分割してページを作成していく方法についても紹介していきます。。加えて、リンクとフォームは Web をつなげる基本的な導線ですが、これらをテンプレートに挿入したり、アクションで処理する方法についても学びます。

モジュールのスケルトンを作る
-----------------------------

2章で説明したように、symfony は複数ページをひとまとまりにしてモジュールという単位で扱います。そのため、ページを作るまえに、まずモジュールを作る必要があります。モジュールはsymfonyからはファイルの配置構造によって識別され、最初の段階では空っぽの状態です。

symfony はコマンドによってモジュールを自動生成します。`generate:module`タスクの引数としてアプリケーション名とモジュール名を与えることで、新しいモジュールが生成されます。まえの章で作った `frontend` アプリケーションに `content` モジュールを追加するには、つぎのコマンドを入力します:

    & cd ~/myproject
    $ php symfony generate:module frontend content

    >> dir+      ~/myproject/apps/frontend/modules/content/actions
    >> file+     ~/myproject/apps/frontend/modules/content/actions/actions.class.php
    >> dir+      ~/myproject/apps/frontend/modules/content/templates
    >> file+     ~/myproject/apps/frontend/modules/content/templates/indexSuccess.php
    >> file+     ~/myproject/test/functional/frontend/contentActionsTest.php
    >> tokens    ~/myproject/test/functional/frontend/contentActionsTest.php
    >> tokens    ~/myproject/apps/frontend/modules/content/actions/actions.class.php
    >> tokens    ~/myproject/apps/frontend/modules/content/templates/indexSuccess.php

`actions/` と `templates/` というディレクトリを除けば、このコマンドが生成するファイルは3つだけです。`test/` フォルダーのなかに作られたファイルは機能テストに関係しますが、15章まで気にする必要はありません。`actions.class.php` (リスト4-1) は `default` モジュールの初期ページに転送(フォワード)する処理が書かれています。`templates/indexSuccess.php` ファイルは空です。

リスト4-1 - 生成されたデフォルトのアクション (`actions/actions.class.php`)

    [php]
    <?php

    class contentActions extends sfActions
    {
      public function executeIndex(sfWebRequest $request)
      {
        $this->forward('default', 'module');
      }
    }

>**NOTE**
>実際の `actions.class.php`ファイルを見ると、このリスト数行よりもずっと多く、大量のコメントが書いてあります。これは、symfony がプロジェクト内でPHPコメントを記述するよう推奨しているためで、その下準備としてクラスファイル内にphpDocumentor ([http://www.phpdoc.org/](http://www.phpdoc.org/))と互換な形式のコメントを挿入しているのです。

新しく生成されたモジュールには、symfony がデフォルトの `index` アクションを用意しています。これは`executeIndex` と呼ばれるアクションメソッドと `indexSuccess.php` と呼ばれるテンプレートファイルから構成されます。プレフィックスの `execute` とサフィックスの `Success` の意味は6章と7章で説明します。それまでのあいだはこの命名方法を規約として考えることにします。つぎの URL をブラウザーに入力すると対応ページ (図4-1で再現) を見ることができます:

    http://localhost/frontend_dev.php/content/index

この章ではデフォルトの `index` アクションを使わないので、`actions.class.php` ファイルから `executeIndex()` メソッドを、`templates/` ディレクトリから `indexSuccess.php` ファイルを削除してしまいましょう。

>**NOTE**
>symfony はコマンドライン以外にもモジュールを初期化する事が出来ます。その1つは、自分でファイルとディレクトリを作ることです。多くの場合、モジュールのアクションとテンプレートは、任意のテーブルのデータを操作するために作られます。テーブルからレコードを作成する、読みとる、更新する、削除するために必要なコードはだいたい同じなので、symfony はこのコードを生成するためのメカニズムを提供します。このテクニックに関する詳細な情報は14章を参照してください。

図4-1 - 生成されたデフォルトの index ページ

![生成されたデフォルトの index ページ](http://www.symfony-project.org/images/book/1_4/F0401.jpg "生成されたデフォルトの index ページ")

ページを追加する
----------------

symfony において、ページの裏側にあるロジックはアクションに記述され、表示部分はテンプレートに記述されます。ロジックをともなわないページでも空のアクションを必要とします。

### アクションを追加する

「Hello, world!」 のページは `show` アクションを通してアクセスできます。リスト4-2で示されるように、このアクションを作るには、`contentActions` クラスに `executeShow` メソッドを追加します。

リスト4-2 - アクションを追加するにはアクションクラスに `executeXxx()` メソッドを追加する (`actions/actions.class.php`)

    [php]
    <?php

    class contentActions extends sfActions
    {
      public function executeShow()
      {
      }
    }

アクションメソッドの名前はつねに `executeXxx()` で、名前の2パート目に当たる大文字で始まる`Xxx`の部分が、アクションの名前になります。

では、つぎの URL にアクセスしてみてください:

    http://localhost/frontend_dev.php/content/show

symfony が `showSuccess.php` テンプレートが見つからないことを訴えます。これは正常な動作です; symfony において、つねにページは１つのアクションと１つのテンプレートのセットで構成されます。

>**CAUTION**
>URL (ドメイン名ではありません) と同じように、symfony は大文字と小文字を区別します (PHP のメソッド名は大文字と小文字を区別しません)。このことはブラウザーで `sHow` を呼び出すと、symfony が404エラーを返すことを意味します。

-

>**SIDEBAR**
>URL はレスポンスの一部
>
>symfony は、実際のアクション名と、そのアクションを呼び出すために必要な URL を完全に分離できるようにするルーティングシステムを備えています。このメカニズムによって URL をあたかもレスポンスの一部のようにカスタム形式で整えることができます。もはやファイル構造やリクエストパラメーターによって制限されることはありません; アクション用の URL を好きな形に指定できます。たとえば、通常、article モジュールの index アクションを呼び出す URL はつぎのようになります:
>
>     http://localhost/frontend_dev.php/article/index?id=123
>
>このURLは、データベースから任意の記事をとり出すものだとしましょう。この例では、ヨーロッパのセクションのとりわけフランスのファイナンスを議論している記事 (`id=123`) がとり出されます。しかしながら、`routing.yml` 設定ファイルを少し変更することで URL を完全に異なる形式で書けます:
>
>     http://localhost/articles/europe/france/finance.html
>
>このようにわかりやすいURLにする事は、検索エンジンとの相性がよくなるだけでなく、ユーザーにとっても重要です。ユーザーは、カスタムクエリを行うために、つぎのようにアドレスバーを擬似コマンドラインとして使えます:
>
>     http://localhost/articles/tagged/finance+france+euro
>
>symfony はユーザーのためにスマート URL を解析・生成します。ルーティングシステムはスマート URL からリクエストパラメーターを自動的に読みとり、アクションがこれらを利用できるようにします。レスポンスに含まれるハイパーリンクの形式も整形してくれるので、これらは「スマート」に見えます。この機能は9章で詳しく学ぶことになります。
>
>すなわち、このことは、アプリケーションのアクションの命名に関して、アクションを呼び出すための URL に依存して決めるのではなく、アクションの機能に依存して決めるべきであるを意味しています。アクションの名前はアクションが実際の動作を説明し、不定形の動詞 (`show`、`list`、`edit` など) であることがよくあります。アクションの名前はエンドユーザーには見えないので、何が起きるかが明確にわかってしまう名前 (`listByName` もしくは `showWithComments` など) を使うことをためらわないでください。わざわざコメントでアクションの機能を説明するコードの必要がなくなる事に加えて、コードがはるかに読みやすくなります。

### テンプレートを追加する

アクションは自分自身をレンダリングすることをテンプレートに要求します。テンプレートはモジュールの `templates/` ディレクトリに設置されたファイルで、アクションの名前とアクションの終了状態をつなげて命名されます。アクションのデフォルトの終了状態は「success」なので、`show` アクションのために作られるテンプレートファイルは `showSuccess.php` という名前になります。

テンプレートはプレゼンテーション用のコードだけを格納することを前提としているので、これらをできるかぎり小さな PHP コードとして保ってください。当然のことながら、「Hello, world!」を表示するページはリスト4-3のようなシンプルなテンプレートになります。

リスト4-3 - テンプレート (`content/templates/showSuccess.php`)

    [php]
    <p>Hello, world!</p>

テンプレートのなかで PHP コードを実行する必要がある場合、リスト4-4で示すように、通常の PHP 構文は避けるべきです。代わりに、PHP プログラマではない人でも理解できるように、リスト4-5で示される PHP の代替構文を使ってテンプレートを書きましょう。最終的なコードを正しくインデントできるだけでなく、複雑な PHP コードはアクション側に記述し、テンプレートをシンプルに保つ助けになります。なぜなら制御文 (`if`、`foreach`、`while` など) だけが代替構文を持つからです。

リスト4-4 - アクションにはよいが、テンプレートにはよくない通常の PHP 構文 (`templates/showSuccess.php`)

    [php]
    <p>Hello, world!</p>
    <?php

    if ($test)
    {
      echo "<p>".time()."</p>";
    }

    ?>

リスト4-5 - テンプレート用の代替の PHP 構文のよい例 (`templates/showSuccess.php`)

    [php]
    <p>Hello, world!</p>
    <?php if ($test): ?>
      <p><?php echo time(); ?></p>
    <?php endif; ?>

>**TIP**
>テンプレートの構文が十分に読みやすいものか確認するためのよい経験則は PHPでHTMLをechoしたり、PHPのコードの中に波かっこを書かないことです。また多くの場合、`<?php` で展開するとき、同じ行の `?>` で閉じるようにするのもよいでしょう。

### アクションからテンプレートに情報を渡す

アクションの仕事はすべての複雑な計算、データの読み出しとテストの実施、およびecho もしくはテストされるテンプレートに対して、変数を設定することです。symfony は (アクション内で `$this->variableName` としてアクセスされる) アクションクラスのプロパティが (`$variableName` 経由で) グローバルな名前空間のテンプレートのなかで直接アクセスできるようにします。リスト4-6と4-7はテンプレートにアクションからの情報を渡す方法を示しています。

リスト4-6 - テンプレートのなかでアクションのプロパティを使えるようにアクションを設定する (`content/actions/actions.class.php`)

    [php]
    <?php

    class contentActions extends sfActions
    {
      public function executeShow()
      {
        $today = getdate();
        $this->hour = $today['hours'];
      }
    }

リスト4-7 - テンプレートはアクションのプロパティに直接アクセスできる (`templates/showSuccess.php`)

    [php]
    <p>Hello, world!</p>
    <?php if ($hour >= 18): ?>
      <p>Or should I say good evening? It is already <?php echo $hour ?>.</p>
    <?php endif; ?>

省略形式の開始タグ (`<?=` は `<?php echo` と同じ) の使用はプロフェッショナルな Web アプリケーションでは非推奨であることに注意してください。運用している Web サーバーが複数のスクリプト言語を混同する可能性があるからです。加えて省略形式の開始タグは PHP のデフォルト設定では機能せず、有効にするためにサーバーを調整する必要があるからです (訳注：`short_open_tag` ディレクティブを `on` に設定する)。結局のところ、XML とバリデーションを処理しなければならない場合、XML において `<?` は特別な意味を持ってしまうので不適切な表記です。

>**NOTE**
>アクションの変数をセットアップしなくても、テンプレートはデフォルトで一部のデータにアクセスできます。すべてのテンプレートは `$sf_request`, `$sf_params`、`$sf_response` と `$sf_user` オブジェクトのメソッドを呼び出すことができます。これらのオブジェクトはそれぞれ現在のリクエスト、リクエストパラメーター、レスポンス、セッションに関連するデータを格納しています。まもなくこれらの効率的な使いかたを学ぶことになります。

別のアクションにリンクする
---------------------------

アクションの名前とそれを呼び出すURLが完全に分離されていることは既にご存じのとおりです。ですのでリスト4-8のようにテンプレートのなかで `update` アクションへのリンクを書き込んでしまうと、リンクはデフォルトのルーティングでしか機能しません。あとで URL の表示方法の変更する必要がある場合、ハイパーリンクを変更するにはすべてのテンプレートを再吟味する必要があります。

リスト4-8 - 古典的なハイパーリンク

    [php]
    <a href="/frontend_dev.php/content/update?name=anonymous">
      私の名前は教えません
    </a>

このやっかいな問題を避けるには、つねにアプリケーションのアクションへのハイパーリンクを作る `link_to` ヘルパーを使うべきです。そして URL の部分だけ生成したい場合、 `url_for()` を使うべきでしょう。

ヘルパー (helper) とは symfony によって定義された、テンプレートのなかで HTML コードを出力するPHP関数です。HTML コードを直接書くよりこれを使うほうが速く書けます。リスト4-9はハイパーリンクヘルパーの使いかたを示しています。

リスト4-9 - `link_to()` と `url_for()` ヘルパー (`templates/***Success.php`)

    [php]
    <p>こんにちは！</p>
    <?php if ($hour >= 18): ?>
    <p>もしくはこんばんはと言ったほうがよろしいでしょうか？現在<?php echo $hour ?>時です。</p>
    <?php endif; ?>
    <form method="post" action="<?php echo url_for('content/update') ?>">
      <label for="name">お名前は？</label>
      <input type="text" name="name" id="name" value="" />
      <input type="submit" value="Ok" />
      <?php echo link_to('名前を教えない','content/update?name=anonymous') ?>
    </form>

ルーティングルールを変更しても、すべてのテンプレートは正しくふるまい、ルールにしたがって整形後の URL が新しいものに変更される部分以外、 HTML は以前のものと同じになります。

symfony はフォームの操作をずっと楽にするツールをたくさん提供するので、フォームの操作はそれだけで1つの章の説明をする必要があります。10章でこれらのヘルパーについて詳細な内容を学ぶことになります。

`link_to()` ヘルパーは、多くのヘルパーと同じように、特別なオプションと追加のタグ属性用の別の引数を受けとります。リスト4-10はオプション引数の例とHTMLの出力結果を示しています。オプション引数は連想配列もしくは空白文字で区切られた `key=value` の組を表示するシンプルな文字列です。

リスト4-10 - たいていのヘルパーはオプション引数を受けとる (`templates/***Success.php`)

    [php]
    // 連想配列としてのオプション引数
    <?php echo link_to('名前を教えない', 'content/update?name=anonymous',
      array(
        'class'    => 'special_link',
        'confirm'  => 'よろしいですか？',
        'absolute' => true
    )) ?>

    // 文字列としてのオプション引数
    <?php echo link_to('名前を教えない', 'content/update?name=anonymous',
      'class=special_link confirm=Are you sure? absolute=true') ?>

    // 両方の呼び出しは同じ内容を出力する
     => <a class="special_link" onclick="return confirm('Are you sure?');"
        href="http://localhost/frontend_dev.php/content/update/name/anonymous">
        名前を教えない</a>

HTML タグを出力する symfony のヘルパーを使うとき、オプション引数に (リスト4-12の例の `class` 属性のような) 追加のタグ属性を挿入できます。これらの属性を「速くて汚い」HTML 4.0 の形式(ダブルクォートなし)で書くこともできますが、symfony はこれらをすばらしく整形されたXHTML形式で出力します。それが HTML よりもヘルパーを速く書ける別の理由です。

>**NOTE**
>追加の解析と変換が必要なので、文字列の構文は配列よりも若干遅いです。

symfony のすべてのヘルパーのように、リンクヘルパーとオプションは数多く存在します。9章でこれらを詳しく説明します。

リクエストから情報を入手する
----------------------------

ユーザーが送信した情報がフォーム経由 (通常は POST リクエスト) もしくは URL 経由 (GET リクエスト) のどちらでも、`sfRequest` オブジェクトの `getParameter()` メソッドを使ってアクションから関連データを読み出せます。リスト4-11は、`update` メソッドによる `name` パラメーターの値の読み出しかたを示しています。

リスト4-11 - アクションのリクエストパラメーターからデータを読み出す (`content/actions/actions.class.php`)

    [php]
    <?php

    class contentActions extends sfActions
    {
      // ...

      public function executeUpdate($request)
      {
        $this->name = $request->getParameter('name');
      }
    }

利便性のために、すべての `executeXxx()` メソッドは最初の引数として現在の `sfRequest` オブジェクトを受けとります。

データの操作がシンプルであるなら、リクエストパラメーターをとり出すためにアクションを使う必要もありません。テンプレートが `$sf_params` オブジェクトにアクセスできるからです。アクションの `getParameter()` と同様に、`$sf_params` はリクエストパラメーターをとり出す `get()` メソッドを提供します。

`executeUpdate()` が空の場合、リスト4-12は `updateSuccess.php` テンプレートが同じ `name` パラメーターを読み出す方法を示しています。

リスト4-12 - テンプレートからリクエストパラメーターを直接とり出す (`templates/***Success.php`)

    [php]
    <p>Hello, <?php echo $sf_params->get('name') ?>!</p>

>**NOTE**
>代わりに `$_POST`、`$_GET`、`$_REQUEST`変数を使わないのはなぜでしょうか？URLは異なるようにフォーマットされるため(たとえば`?`や`=`をともなわない `http://localhost/articles/europe/france/finance.html`)、通常のPHP変数はそれ以上機能しないので、ルーティングシステムのみがリクエストパラメーターをとり出すことができるからです。悪意のあるコードのインジェクションを防止するために入力フィルタリングを追加したい場合、すべてのリクエストパラメーターを1つのクリーンなパラメーターホルダーに格納することで実現できます。

`$sf_params` オブジェクトはたんに配列に同等のゲッターを渡すよりも強力です。たとえば、リクエストパラメーターの存在を確認したい場合、リスト4-13のように、実際の値を `get()` で試すよりも `$sf_params->has()` メソッドを使うほうが楽です。

リスト4-13 - テンプレートのなかでリクエストパラメーターを試す (`templates/***Success.php`)

    [php]
    <?php if ($sf_params->has('name')): ?>
      <p>こんにちは、<?php echo $sf_params->get('name') ?>さん！</p>
    <?php else: ?>
      <p>こんにちは、John Doeさん！</p>
    <?php endif; ?>

読者のなかにはこれを一行で書けることを推測している人がいることでしょう。symfony のたいていのゲッターメソッドに関しては、アクションの `$request->getParameter()` メソッドとテンプレートの `$sf_params->get()` メソッド (実際には、同じオブジェクトの同じメソッドの呼び出し) の両方とも2番目の引数としてリクエストパラメーターが存在しない場合に使われるデフォルト値を受けとります。

    [php]
    <p>こんにちは、<?php echo $sf_params->get('name', 'John Doe') ?>さん！</p>

まとめ
----

symfony において、ページはアクション (action) とテンプレート (template) で構成されます。アクションは `actions/actions.class.php` ファイルのメソッドでプレフィックスは `execute` です。テンプレートは `templates/` ディレクトリのなかのファイルで、通常は `Success.php` で終わります。これらはアプリケーションの機能にしたがって、モジュール (module) に分類されます。ヘルパーによってテンプレートを書く作業が円滑になります。ヘルパー (helper) とは symfony によって提供される HTML コードを返す関数です。そして、必要に応じて整形される URL はレスポンスの一部として考えることが必要です。ですのでアクションの命名もしくはリクエストパラメーターの読みだしにおいてURLを直接参照することは避けるべきです。

これらの基本原則を理解したら、すでにアプリケーション全体を書けます。しかしこれだけでは作業が長すぎます。アプリケーションの開発過程で成し遂げなければならないほとんどすべてのタスクは symfony の別の機能によって1つもしくは別の方法で円滑に行われます・・・今のところこの本が終わらない理由です。
