第15章 - ユニットテストと機能テスト
===================================

自動テストはプログラミングにおけるオブジェクト指向以降の最大の進歩の1つです。とりわけ、Web アプリケーションを開発するための助けになるので、例えおびただしい数のアプリケーションがリリースされたとしても、アプリケーションの品質を保証できます。この章では自動テストを円滑に運用するためのさまざまなツールを紹介します。

自動ツール
----------

Web アプリケーションを開発した経験を持つ開発者はテストを実施するために時間がかかることを承知しています。テストケースを書き、それらを実施して、結果を解析するのは退屈な作業です。加えて、Web アプリケーションの要件はつねに変化しがちなので、コードのリファクタリングとアプリケーションのリリースが継続して行われることになります。この作業の流れでは、予期しない新たなエラーが定期的に起こりがちです。

なぜ自動ツールが、必要ではなくても提案され、成功した開発環境の一部になっている理由はそういうわけです。テストケースのセットはアプリケーションが実際に行うことを保証します。内部のコードが頻繁に書き直される場合、自動化テストは予想外の回帰を防止します。加えて、厳格な標準フォーマットによって、テストフレームワークが理解しやすいようにテストを書くことを開発者に強制します。

自動テストは時に開発者のドキュメントにとって代わります。アプリケーションが行うことの説明になっているからです。よいテストスイートはテスト入力のセットのためにどんな出力が期待されているのかを示し、メソッドの目的を説明するよい方法です。

symfony フレームワークはこの原則を自分自身に適用します。symfony の内部は自動テストによって検証されます。これらのユニットテスト (unit test) と機能テスト (functional test) は PEAR パッケージには搭載されていませんが、SVN リポジトリからチェックアウトするか、[オンライン](http://trac.symfony-project.org/browser/branches/1.4/test)で眺めることができます。

### ユニットテストと機能テスト

ユニットテスト (もしくは単体テスト - unit test) は単一のコードコンポーネントが任意の入力に対して正しい出力を提供することを確認します。これらのテストは関数とメソッドがすべての特定のケースで動作する方法を検証します。ユニットテストは一度に1つのケースを処理するので、たとえば、1つのメソッドが特定の状況で異なる動作をする場合、いくつかのユニットテストが必要になることがあります。

機能テスト (functional test) は、シンプルな入力から出力への変換ではなく、完全な機能を検証します。たとえば、キャッシュシステムは機能テストだけで検証できます。なぜなら複数のステップが含まれるからです: 最初、ページがリクエストされ、レンダリングされます; つぎに、キャッシュからページが取得されます。ですので機能テストはプロセスを検証し、シナリオを必要とします。symfony において、すべてのアクションに対して機能テストを書くべきです。

もっと複雑なインタラクションのには、これらの2つのタイプのテストは不十分かもしれません。たとえば、Ajax インタラクションは JavaScript を実行するために Web  ブラウザーを必要とするので、これらを自動的にテストするには特別なサードパーティのツールが必要です。さらに、視覚効果を検証できるのは人間だけです。

自動ツールへの広い範囲でのアプローチがある場合、おそらく、これらすべての方法の組み合わせを使う必要があります。指針としては、テストをシンプルで読みやすいものに保つべきであることを覚えておいてください。

>**NOTE**
>自動テストは結果と予期される出力の比較によって動作します。言い換えると、アサーション (`$a == 2` などの式) を評価します。アサーションの値は `true` もしくは `false` で、テストが合格したか失敗したかを判定します。自動テストの技術を扱うとき「アサーション」(もしくは表明 - assertion)という言葉は一般的に使われます。

### テスト駆動開発

テスト駆動開発 (TDD - Test-Driven Development) の方法論において、テストはコードのまえに書かれます。最初にテストを書くことは実際に開発するまえに機能が実現するタスクに焦点を当てるための助けになります。エクストリームプログラミング (XP - Extreme Programming) のような、これはよい習慣で、同様にお勧めです。加えて、この方法論はユニットテストを最初に書いておかないとあとで書くことはないという事実を考慮しています。

たとえば、テキストをとり除く機能を開発しなければならない場合を考えてみましょう。この機能は文字列の最初と最後の空白スペースをとり除き、アルファベットでない文字をアンダースコアに置き換え、すべての大文字を小文字に変換します。テスト駆動開発において、テーブル15-1で示されるように、すべてのあり得る場合を考え、それぞれの場合に対して入力の例と期待される出力を準備することになります。

テーブル15-1 想定されるテキストをとり除く機能

入力                  | 期待される出力
--------------------- | ---------------------
`" foo "`             | `"foo"`
`"foo bar"`           | `"foo_bar"`
`"-)foo:..=bar?"`     | `"__foo____bar_"`
`"FooBar"`            | `"foobar`"
`"Don't foo-bar me!"` | `"don_t_foo_bar_me_"`

ユニットテストを書きたい場合、それらを実行して、失敗する様子を見てください。最初のテストケースを処理するために必要なコードを追加し、テストを再度動かし、最初のテストが成功するのを見て、そのように続けます。最終的に、すべてのテストケースは成功したとき、機能は正しいです。

テスト駆動方法論で開発されたアプリケーションは大まかに実際のコードと同じぐらいのテストコードで終わります。テストケースをデバッグすることに時間を費やしたくないのであれば、それらをシンプルに保ってください。

>**NOTE**
>メソッドをリファクタリングすると以前は現れなかった新しいバグが作られる可能性があります。運用環境に新しいリリースのアプリケーションをデプロイするまえに、すべての自動テストを実行することもよい習慣であるのはそういうわけです。これは回帰テスト (regression testing) と呼ばれます。

### lime テストフレームワーク

PHP の世界においてユニットテストのフレームワークは多く存在し、PhpUnit がもっともよく知られています。symfony は lime と呼ばれる独自のテストフレームワークを持ちます。Perl ライブラリの `Test::More` に基づき、TAP (Test Anything Protoco) に準拠しています、このことは、テストの出力をより読みやすくするために設計された TAP で定められているように、テストの結果が表示されることを意味します。

lime はユニットテストをサポートします。PHP のテストフレームワークよりも軽量でいくつかの利点があります:

  * lime はそれぞれの動くテストの間の奇妙な副作用を回避するためにテストファイルをサンドボックスで起動します。
  * lime テストおよびそのテストの出力はとても読みやすいです。互換性のあるシステムで、重要な情報を見分けられるように、lime はスマートな方法でカラー出力を使います。
  * 回帰テストのために symfony 自身が `lime` テストを使うので、ユニットテストと回帰テストの多くの例が symfony のソースコードで見つかります。
  * lime コアはユニットテストによって検証されます。
  * lime は PHP で書かれており、速く動作し上手に書かれています。他のライブラリに依存しません。

つぎのセクションで説明されるさまざまなテストは lime の構文を使います。symfony をインストールしたのであればこれらのテストはそのまま動きます。

>**NOTE**
>運用サーバーでユニットテストと機能テストを起動させることは想定されていません。これらのテストは開発者のツールなので、ホストサーバーではなく、開発者のコンピュータで動かすべきです。

ユニットテスト
-------------

symfony のユニットテストは `Test.php` で終わるシンプルな PHP ファイルで、アプリケーションの `test/unit/` ディレクトリに設置されています。これらはシンプルで読みやすい構文に従います。

### ユニットテストは何に見えますか？

リスト15-1は `strtolower()` 関数のための典型的なユニットテストの一式を示しています。このテストは `lime_test` オブジェクトをインスタンス化することで始まります(今はパラメーターに悩む必要はありません)。それぞれのユニットテストは `lime_test` インスタンスの呼び出しです。これらのメソッドの最後のパラメーターはつねに出力として提供されるオプションの文字列です。

リスト15-1 - ユニットテストのファイルの例 (`test/unit/strtolwerTest.php`)

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    $t = new lime_test(7);

    // strtolower()
    $t->diag('strtolower()');
    $t->isa_ok(strtolower('Foo'), 'string',
        'strtolower() は文字列を返す');
    $t->is(strtolower('FOO'), 'foo',
        'strtolower() は入力を小文字に変換する');
    $t->is(strtolower('foo'), 'foo',
        'strtolower() は小文字を変更しない');
    $t->is(strtolower('12#?@~'), '12#?@~',
        'strtolower() はアルファベットではない文字を変更しない');
    $t->is(strtolower('FOO BAR'), 'foo bar',
        'strtolower() は空白をそのままにする');
    $t->is(strtolower('FoO bAr'), 'foo bar',
        'strtolower() は混合する文字の入力を扱う');
    $t->is(strtolower(''), 'foo',
        'strtolower() は空の文字列を foo に変換する');

コマンドラインから `test:unit` タスクでテストセットを起動させてください。コマンドラインの出力内容はとても明確なので、成功したテストと失敗したテストを見つけ出すための助けになります。リスト15-2のテストの出力例をご覧ください。

リスト15-2 - 1つのユニットテストをコマンドラインから起動させる

    $ php symfony test:unit strtolower
    
    1..7
    # strtolower()
    ok 1 - strtolower() は文字列を返す
    ok 2 - strtolower() は入力を小文字に変換する
    ok 3 - strtolower() は小文字を変更しない
    ok 4 - strtolower() はアルファベットではない文字を変更しない
    ok 5 - strtolower() は空白をそのままにする
    ok 6 - strtolower() は混合する文字の入力を扱う
    not ok 7 - strtolower() は空の文字列をfooに変換する
    #     Failed test (.\batch\test.php at line 21)
    #            got: ''
    #       expected: 'foo'
    # Looks like you failed 1 tests of 7.

>**TIP**
>リスト15-1の始めの `include` ステートメントはオプションですが、`php test/unit/strtolowerTest.php` を呼び出すことで、テストファイルは symfony コマンドラインを使わずに実行できる独立した PHP スクリプトになります。

### ユニットテストのメソッド

テーブル15-2で一覧が示されるように、`lime_test` オブジェクトには多くのテストメソッドが付随しています。

テーブル15-2 - ユニットテストのための `lime_test` オブジェクトのメソッド

メソッド                                      | 説明
-------------------------------------------   | -----------------------------------------
`diag($msg)`                                  | コメントを出力するがテストは実施しない
`ok($test[, $msg])`                           | 条件をテストして true の場合にパスする
`is($value1, $value2[, $msg])`                | ２つの値を比較して等しい場合にパスする
`isnt($value1, $value2[, $msg])`              | ２つの値を比較し、等しくない場合にパスする
`like($string, $regexp[, $msg])`              | 正規表現に対して文字列をテストする
`unlike($string, $regexp[, $msg])`            | 文字列が正規表現にマッチしないことをチェックする
`cmp_ok($value1, $operator, $value2[, $msg])` | 演算子で引数を比較する 
`isa_ok($variable, $type[, $msg])`            | 引数のタイプをチェックする 
`isa_ok($object, $class[, $msg])`             | オブジェクトのクラスをチェックする
`can_ok($object, $method[, $msg])`            | オブジェクトもしくはクラスのためのメソッドが利用できるかチェックする 
`is_deeply($array1, $array2[, $msg])`         | 同じ値を持つ2つの配列をチェックする
`include_ok($file[, $msg])`                   | ファイルが存在し、適切に含まれるかをバリデートする
`fail([$msg])`                                | つねに失敗する--テストの例外に便利である
`pass([$msg])`                                | つねに成功する-- テストの例外に便利である
`skip($msg, $nb_tests)`                       | `$nb_tests` 件のテストをカウントします--条件つきのテストに便利です
`todo([$msg])`                                | テストとしてカウントします-- まだ書かれていないテストに便利です
`comment($msg)`                               | コメントメッセージは出力するがテストは実施しない 
`error($msg)`                                 | エラーメッセージは出力するがテストは実施しない
`info($msg)`                                  | 情報メッセージは出力するがテストは実施しない 


構文はとても単刀直入です; たいていのメソッドはメッセージを最後のパラメーターとして受けとることに注意してください。このメッセージはテストが成功したときに出力に表示されます。これらのメソッドを学ぶベストな方法はこれらを実際にテストすることです。ですので、これらのメソッドをすべて使っているリスト15-3をご覧ください。

リスト15-3 - `lime_test` オブジェクトのメソッドをテストする (`test/unit/exampleTest.php`)

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    // テストを目的としたスタブオブジェクトと関数
    class myObject
    {
      public function myMethod()
      {
      }
    }

    function throw_an_exception()
    {
      throw new Exception('exception thrown');
    }

    // テストオブジェクトを初期化する
    $t = new lime_test(16);

    $t->diag('hello world');
    $t->ok(1 == '1', '等号演算子は型を無視する');
    $t->is(1, '1', '文字列は比較のために数字に変換される');
    $t->isnt(0, 1, '0 と 1 は等しくない');
    $t->like('test01', '/test\d+/', 'test01 はテストの番号付けパターンに従う');
    $t->unlike('tests01', '/test\d+/', 'tests01 はこのパターンに従わない');
    $t->cmp_ok(1, '<', 2, '1 は 2 より小さい');
    $t->cmp_ok(1, '!==', true, '1 と true はまったく同じではない');
    $t->isa_ok('foobar', 'string', '\'foobar\' は文字列');
    $t->isa_ok(new myObject(), 'myObject', 'new演算子は右のクラスのオブジェクトを作る');
    $t->can_ok(new myObject(), 'myMethod', 'myObject クラスのオブジェクトは myMethod メソッドを持つ');
    $array1 = array(1, 2, array(1 => 'foo', 'a' => '4'));
    $t->is_deeply($array1, array(1, 2, array(1 => 'foo', 'a' => '4')),
        '最初と2番目の配列は同じ');
    $t->include_ok('./fooBar.php', 'fooBar.php ファイルが適切にインクルードされた');

    try
    {
      throw_an_exception();
      $t->fail('例外が投じられた後コードは実行されません');
    }
    catch (Exception $e)
    {
      $t->pass('例外の捕捉を成功しました');
    }

    if (!isset($foobar))
    {
      $t->skip('テストの回数を正確に保つために1つのテストをスキップする', 1);
    }
    else
    {
      $t->ok($foobar, 'foobar');
    }

    $t->todo('すべきテストが1つ残っている');

symfonyのユニットテストのなかにこれらのメソッドの使いかたの例が多く見つかります。

>**TIP**
>なぜ `ok()` と対照的に `is()` を使うのかとまどっているかもしれません。`is()` によるエラーメッセージの出力がはるかに明確だからです; このメソッドがテストの両方のメンバーを表示する一方で `ok()` は条件が失敗したことを伝えます。

### パラメーターをテストする

`lime_test` オブジェクトの初期化は最初のパラメーターとして実行されるテストの数をとります。最終的に実行されるテストの数がこのパラメーターの数値と異なる場合、lime はそのことに関する警告を出力します。たとえば、リスト15-3のテストセットはリスト15-4のように出力します。16回のテストが実施されることが保証されましたが、実際には15回だけ行われたので、出力はこれを示してします。

リスト15-4 - テストの実行回数のカウントはテストの計画の助けになる

    $ php symfony test:unit example

    1..16
    # hello world
    ok 1 - 等号演算子は型を無視する
    ok 2 - 文字列は比較のために数字に変換される
    ok 3 - 0と1は等しくない
    ok 4 - test01 はテストの番号付けパターンに従う
    ok 5 - tests01 はこのパターンに従わない
    ok 6 - 1は2より小さい
    ok 7 - 1と true はまったく同じではない
    ok 8 - 'foobar' は文字列
    ok 9 - new 演算子は右のクラスのオブジェクトを作る
    ok 10 - myObject クラスのオブジェクトは amyMethod メソッドを持つ
    ok 11 - 最初と2番目の配列は同じ
    not ok 12 - fooBar.php フ ァイルが適切にインクルードされた
    #     Failed test (.\test\unit\testTest.php at line 27)
    #       Tried to include './fooBar.php'
    ok 13 - 例外の捕捉が成功しました
    ok 14 # SKIP テストの回数を正確に保つために1つのテストをスキップする
    ok 15 # TODO すべきテストが1つ残っている
    # Looks like you planned 16 tests but only ran 15.
    # Looks like you failed 1 tests of 16.

`diag()` メソッドはテストとしてカウントされません。コメントを表示するためにこれを使えば、テストの出力は整理され読みやすい状態に保たれます。一方で、`todo()` メソッドと `skip()` メソッドは実際のテストとしてカウントされます。`try/catch` ブロックの `pass()/fail()` メソッドの組み合わせは単独のテストとしてカウントされます。

よく計画されたテスト戦略は予想されるテストの数を含まなければなりません。とりわけテストが内部の条件もしくは例外の条件で動作する複雑なケースにおいて、テストの数が独自のテストファイルを検証するためにとても便利であることがわかるでしょう。そして、テストがある時点で失敗するとすぐにテストの数がわかります。実行テストの最後の数が初期化の間に渡された数字と一致しないからです。

### test:unit タスク

`test:unit` タスクは、コマンドラインからユニットテストを起動させ、テストの名前のリストもしくはファイルのパターンを必要とします。リスト15-5で詳細をご覧ください。

リスト15-5 - ユニットテストを起動させる

    // testのディレクトリ構造
    test/
      unit/
        myFunctionTest.php
        mySecondFunctionTest.php
        foo/
          barTest.php

    $ php symfony test:unit myFunction                   ## myFunctionTest.php を実行する
    $ php symfony test:unit myFunction mySecondFunction  ## 両方のテストを実行する
    $ php symfony test:unit foo/*                        ## barTest.php を実行する
    $ php symfony test:unit *                            ## すべてのテストを実行する (再帰的)

### スタブ、フィクスチャ、オートロード

ユニットテストにおいて、デフォルトではオートロード機能は有効ではありません。テストで使うそれぞれのクラスはテストファイルで定義するか、外部依存のファイルとして `require` ステートメントで読み込まなければなりません。リスト15-6で示されるように、多くのテストファイルが複数行の `include` ステートメントで始まるのはそういうわけです。

リスト15-6 - ユニットテストのクラスをインクルードする

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';
    require_once sfConfig::get('sf_symfony_lib_dir').'/util/sfToolkit.class.php';

    $t = new lime_test(7);

    // isPathAbsolute()
    $t->diag('isPathAbsolute()');
    $t->is(sfToolkit::isPathAbsolute('/test'), true,
        'isPathAbsolute() が絶対パスであるなら true を返す');
    $t->is(sfToolkit::isPathAbsolute('\\test'), true,
        'isPathAbsolute() が絶対パスであるなら true を返す');
    $t->is(sfToolkit::isPathAbsolute('C:\\test'), true,
        'isPathAbsolute() が絶対パスであるなら true を返す');
    $t->is(sfToolkit::isPathAbsolute('d:/test'), true,
        'isPathAbsolute() が絶対パスであるなら true を返す');
    $t->is(sfToolkit::isPathAbsolute('test'), false,
        'isPathAbsolute() が相対パスであるなら false を返す');
    $t->is(sfToolkit::isPathAbsolute('../test'), false,
        'isPathAbsolute() が相対パスであるなら false を返す');
    $t->is(sfToolkit::isPathAbsolute('..\\test'), false,
        'isPathAbsolute() が相対パスであるなら false を返す');

ユニットテストにおいて、テストしているオブジェクトだけをインスタンス化するだけでなく、依存オブジェクトもインスタンス化する必要があります。ユニットテストは単一性を保たなければならないので、ほかのクラスに依存している場合1つのクラスが壊れると複数のテストが失敗する可能性があります。加えて、本当のオブジェクトをセットアップすることはコードの行数と実行時間の点から割高です。開発者は遅い作業にすぐに飽きるので、ユニットテストにおいてスピードが重大であることを覚えておいてください。

ユニットテストのために多くのスクリプトをインクルードを開始する場合はつねにシンプルなオートロードシステムが必要になります。この目的のために、`sfSimpleAutoload` クラス (手動でインクルードしなければなりません) は `addDirectory()` メソッドを提供します。このメソッドはパラメーターとして絶対パスを必要とし検索パス上の複数のディレクトリをインクルードする必要がある場合に何度も呼び出すことができます。このパスの元に設置されたすべてのクラスがオートロードされます。たとえば、`$sf_symfony_lib_dir/util/` の元に設置されたすべてのクラスをオートロードしたい場合、つぎのようなコードでユニットテストのスクリプトを始めてください。

    [php]
    require_once sfConfig::get('sf_symfony_lib_dir').'/autoload/sfSimpleAutoload.class.php';
    $autoload = sfSimpleAutoload();
    $autoload->addDirectory($sf_symfony_lib_dir.'/util');
    $autoload->register();

オートロード問題の別のよい次善策はスタブを使うことです。スタブ (stub) とは実際のメソッドがシンプルであらかじめ用意されたデータに置き換えられるクラスの代替実装です。これは実際のクラスのふるまいを真似しますが、コストはかかりません。スタブのよい例はデータベースの接続もしくは Web サービスのインターフェイスです。リスト15-7において、マッピング API 用のユニットテストは `WebService` クラスに依存します。実際のWebサービスのクラスの本当の `fetch()` メソッドを呼び出す代わりに、テストはテストデータを返すスタブを使います。

リスト15-7 - ユニットテストでスタブを使う

    [php]
    require_once dirname(__FILE__).'/../../lib/WebService.class.php';
    require_once dirname(__FILE__).'/../../lib/MapAPI.class.php'
    
    class testWebService extends WebService
    {
      public static function fetch()
      {
        return file_get_contents(dirname(__FILE__).'/fixtures/data/fake_web_service.xml');
      }
    }

    $myMap = new MapAPI();

    $t = new lime_test(1, new lime_output_color());

    $t->is($myMap->getMapSize(testWebService::fetch(), 100));

テストデータは文字列やメソッドの呼び出しよりも複雑になる可能性があります。複雑なテストデータはしばしフィクスチャ (fixture - 付属品) として参照されます。コーディングを明確にするために、とりわけフィクスチャが複数のユニットテストのファイルによって使われる場合、フィクスチャを別々のファイルに保存したほうがベターです。symfony が `sfYAML::load()` メソッドによって YAML ファイルを配列に簡単に変換できることもお忘れなく。リスト15-8のように、このことは PHP の長い配列を書く代わりに、YAML ファイルでテストデータを書くことができることを意味します。

リスト15-8 - ユニットテストでフィクスチャファイルを使う

    [php]
    // fixtures.ymlにて:
    -
      input:   '/test'
      output:  true
      comment: isPathAbsolute() が絶対パスである場合trueを返す
    -
      input:   '\\test'
      output:  true
      comment: isPathAbsolute() が絶対パスである場合 true を返す
    -
      input:   'C:\\test'
      output:  true
      comment: isPathAbsolute() が絶対パスである場合 true を返す
    -
      input:   'd:/test'
      output:  true
      comment: isPathAbsolute() が絶対パスである場合 true を返す
    -
      input:   'test'
      output:  false
      comment: isPathAbsolute() が相対パスである場合 false を返す
    -
      input:   '../test'
      output:  false
      comment: isPathAbsolute() が相対パスである場合 false を返す
    -
      input:   '..\\test'
      output:  false
      comment: isPathAbsolute() が相対パスである場合 false を返す

    // testTest.phpにて
    <?php

    include(dirname(__FILE__).'/../bootstrap/unit.php');
    require_once sfConfig::get('sf_symfony_lib_dir').'/util/sfToolkit.class.php';
    require_once sfConfig::get('sf_symfony_lib_dir').'/yaml/sfYaml.class.php';
    
    $testCases = sfYaml::load(dirname(__FILE__).'/fixtures.yml');

    $t = new lime_test(count($testCases), new lime_output_color());

    // isPathAbsolute()
    $t->diag('isPathAbsolute()');
    foreach ($testCases as $case)
    {
      $t->is(sfToolkit::isPathAbsolute($case['input']), $case['output'],$case['comment']);
    }

### ORM のクラスをユニットテストする

Propel または Doctrine の生成オブジェクトは長いカスケード状のクラスに依存するので、クラスをテストするのは少し複雑です。さらに、Propel に有効なデータベース接続を提供してデータベースにいくつかのテストデータを送り込む必要もあります。

ありがたいことに、symfony は必要なすべての機能を提供しているのでこれはとても簡単です:

  * オートロードを取得するには、設定オブジェクトを初期化する必要があります
  * データベースの接続を得るには、`sfDatabaseManager` クラスを初期化する必要があります
  * テストデータをロードするには、`sfPropelData` クラスを使うことができます

典型的な Propel のテストファイルはリスト15-9で示されています。

リスト15-9 - Propel のクラスをテストする

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    new sfDatabaseManager($configuration);
    $loader = new sfPropelData();
    $loader->loadData(sfConfig::get('sf_data_dir').'/fixtures');

    $t = new lime_test(1, new lime_output_color());

    // モデルクラスのテストを始める
    $t->diag('->retrieveByUsername()');
    $user = UserPeer::retrieveByUsername('fabien');
    $t->is($user->getLastName(), 'Potencier', '->retrieveByUsername() は任意のユーザー名のための User を返す');

典型的な Doctrine のテストファイルを、リスト 15-10 のようになります。
    
リスト 15-10 - Doctrine のクラスをテストする

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    new sfDatabaseManager($configuration);
    Doctrine_Core::loadData(sfConfig::get('sf_data_dir').'/fixtures');

    $t = new lime_test(1, new lime_output_color());

    // モデルクラスのテストの開始
    $t->diag('->retrieveByUsername()');
    $user = Doctrine::getTable('User')->findOneByUsername('fabien');
    $t->is($user->getLastName(), 'Potencier', '->findOneByUsername() returns the User for the given username');

機能テスト
----------

機能テスト (functional test) はアプリケーションの一部を検証します。これらのテストは、アクションが想定された動作をするか手作業で検証する方法と同じように、ブラウジングセッションをシミュレートし、リクエストを作り、レスポンスの要素をチェックします。

### 機能テストはどのように見えますか？

テキストブラウザーと多くの正規表現で機能テストを実行できますが、時間の大きな無駄遣いです。symfony は `sfBrowser` という名前の特別なオブジェクトを提供します。このオブジェクトは実際に必要なサーバーをともなわずに symfony のアプリケーションに接続したブラウザーのようにふるまいます。そして HTTP の転送の減速は起きません。このオブジェクトはそれぞれのリクエスト (リクエスト、セッション、コンテキスト、レスポンスオブジェクト) のコアオブジェクトにアクセスできます。symfony は `TestBrowser` と呼ばれるこのクラスの拡張機能も提供します。`sfTestFunctional` は機能テストのために設計され、`sfBrowser` オブジェクトの機能に加えてスマートなアサートメソッドを持ちます。

伝統的に機能テストはテストブラウザーのオブジェクトを初期化することから始まります。このオブジェクトはリアクションへのレスポンスを作成し、レスポンス内に存在するいくつかの要素を変更します。

たとえば、`generate:module` タスクもしくは `propel:generate-module`タスクでモジュールスケルトンを生成するたびに、symfony はこのモジュールのためにシンプルな機能テストを作ります。テストはモジュールのデフォルトアクションにリクエストを行いレスポンスのステータスコード、ルーティングシステムによって算出されたモジュールとアクション、とレスポンスの内容のなかの特定のセンテンスの存在をチェックします。`foobar`  モジュールに対して、生成された `foobarActionsTest.php` ファイルはリスト15-11のようになります。

リスト15-11 - 新しいモジュール用のデフォルトの機能テスト (`tests/functional/frontend/foobarActionsTest.php`)

    [php]
    <?php

    include dirname(__FILE__).'/../../bootstrap/functional.php';

    $browser = new sfTestFunctional(new sfBrowser());

    $browser->
      get('/foobar/index')->

      with('request')->begin()->
        isParameter('module', 'foobar')->
        isParameter('action', 'index')->
      end()->

      with('response')->begin()->
        isStatusCode(200)->
        checkElement('body', '!/This is a temporary page/')->
      end()
    ;

>**TIP**
>`browser` のメソッドは `sfTestFunctional`オブジェクトを返すので、テストファイルを読みやすくするためにメソッドチェーンを利用できます。これはオブジェクトへの流れるようなインターフェイス (fluid interface もしくは fluent interface) と呼ばれます。この名前の由来はメソッド呼び出しの流れを止めるものがないからです。

機能テストはいくつかのリクエストと複雑なアサーションを含むことができます; つぎのセクションですべての機能を見ることになります。

機能テストを立ち上げるために、リスト15-12で示されるように、コマンドラインで `test:functional` タスクを使います。このタスクはアプリケーションの名前とテストの名前を必要とします (`Test.php`のサフィックスを出力します)。

リスト15-12 - コマンドラインから1つの機能テストを立ち上げる

    $ php symfony test:functional frontend foobarActions

    # get /comment/index
    ok 1 - status code is 200
    ok 2 - request parameter module is foobar
    ok 3 - request parameter action is index
    not ok 4 - response selector body does not match regex /This is a temporary page/
    # Looks like you failed 1 tests of 4.
    1..4

新しいモジュールに対して生成された機能テストはデフォルトでは成功しません。新しく作成されたモジュールにおいて、`index` アクションは、「This is a temporary page.」という文を含む初期ページにフォワードします (symfony の `default` モジュールを含みます)。`index` アクションを修正しないかぎり、このモジュールに対するテストは失敗します。これは未終了のモジュールですべてのテストを成功できないことを保証します。

>**NOTE**
>機能テスト において、オートロードが有効なので、手動でファイルをインクルードする必要はありません。

### sfBrowser オブジェクトでブラウジングする

テストブラウザーは `GET` リクエストと `POST` リクエストを行う機能を持ちます。両方の場合において、本当の URI をパラメーターとして使います。リスト15-13はリクエストをシミュレートするために `sfTestBrowser`オブジェクトの呼び出しの書きかたを示しています。

リスト15-13 - `sfBrowser` オブジェクトでリクエストをシミュレートする

    [php]
    include dirname(__FILE__).'/../../bootstrap/functional.php';
    
    // 新しいブラウザーを作成する
    $b = new sfBrowser();

    $b->get('/foobar/show/id/1');                   // GETリクエスト
    $b->post('/foobar/show', array('id' => 1));     // POSTリクエスト

    // get()メソッドとpost()メソッドはcall()メソッドへのショートカット
    $b->call('/foobar/show/id/1', 'get');
    $b->call('/foobar/show', 'post', array('id' => 1));

    // call()メソッドは任意のメソッドによるリクエストをシミュレートする
    $b->call('/foobar/show/id/1', 'head');
    $b->call('/foobar/add/id/1', 'put');
    $b->call('/foobar/delete/id/1', 'delete');

典型的なブラウジングセッションは特定のアクションへのリクエストだけでなく、リンクとブラウザーボタンへのクリックも含みます。リスト15-14で示されるように、`sfBrowser` オブジェクトはこれらもシミュレートできます。

リスト15-14 - `sfBrowser` オブジェクトでナビゲーションをシミュレートする

    [php]
    $b->get('/');                  // ホームページへのリクエスト
    $b->get('/foobar/show/id/1');
    $b->back();                    // 履歴の1つのページに戻る
    $b->forward();                 // 履歴の1つのページに進む
    $b->reload();                  // 現在のページをリロードする
    $b->click('go');               // 'go'リンクもしくはボタンを探してクリックする

ブラウザーはコールスタックを処理するので、`back()` メソッドと `forward()` メソッドは本当のブラウザーと同じように動作します。

>**TIP**
>ブラウザーはセッション (`sfTestStorage`) と Cookie を管理する独自のメカニズムを持ちます。

テストする必要のあるインタラクションのなかで、おそらくフォームに関連するものが最優先されます。フォームの入力と投稿をシミュレートするには、選択肢が3つあります。送信したいパラメーターで POST リクエストを行う場合、配列としての `form` パラメーターで `click()` を呼び出すか、1つずつフィールドを入力して、投稿ボタンをクリックします。いずれにせよ、これらはすべて同じPOSTリクエストになります。リスト15-15は例を示しています。

リスト15-15 - `sfBrowser` オブジェクトでフォーム入力をシミュレートする

    [php]
    // modules/foobar/templates/editSuccess.php でのテンプレートの例
    <?php echo form_tag('foobar/update') ?>
      <input type="hidden" name="id" value="<?php echo $sf_params->get('id') ?>" />
      <input type="text" name="name" value="foo" />
      <input type="submit" value="go" />
      <textarea name="text1">foo</textarea>
      <textarea name="text2">bar</textarea>
    </form>

    // このフォームのための機能テストの例
    $b = new sfBrowser();
    $b->get('/foobar/edit/id/1');

    // オプション 1: POST リクエスト
    $b->post('/foobar/update', array('id' => 1, 'name' => 'dummy', 'commit' => 'go'));

    // オプション 2: パラメーターで投稿ボタンをクリックする
    $b->click('go', array('name' => 'dummy'));

    // オプション 3: フィールド名でフォームの値を入力し投稿ボタンをクリックする
    $b->setField('name', 'dummy')->
        click('go');

>**NOTE**
>2番目と3番目のオプションによって、デフォルトのフォームの値は自動的にフォームの投稿に含まれ、フォームターゲットを指定する必要はありません。

`redirect()` メソッドによってアクションが終了した場合、テストブラウザーは自動的にリダイレクトされません; リスト15-16でお手本が示されるように、手動による `followRedirect()` メソッドでテストブラウザーをリダイレクトします。

リスト15-16 - テストブラウザーは自動的にリダイレクトされない

    [php]
    // modules/foobar/actions/actions.class.php のアクションの例
    public function executeUpdate($request)
    {
      // ...

      $this->redirect('foobar/show?id='.$request->getParameter('id'));
    }

    // このアクションのための機能テストの例
    $b = new sfBrowser();
    $b->get('/foobar/edit?id=1')->
        click('go', array('name' => 'dummy'))->
        followRedirect();    // 手動でリダイレクトの後に続く

ブラウジングのために便利なメソッドが1つ残っています。`restart()` はあたかもブラウザーを再起動したようにブラウジングの履歴、セッションと Cookie を再び初期化します。

このメソッドが最初のリクエストを行うと、`sfBrowser` オブジェクトはリクエスト、コンテキスト、レスポンスオブジェクトにアクセスできます。テキストの内容からレスポンスヘッダー、リクエストパラメーターと設定までおよぶ、多くの内容をチェックできます:

    [php]
    $request  = $b->getRequest();
    $context  = $b->getContext();
    $response = $b->getResponse();


### アサーションを使う

レスポンスとリクエストのほかのコンポーネントにアクセスできる `sfTestFunctional` オブジェクトのおかげで、これらのコンポーネントでテストを実施できます。この目的のために新しい `lime_test` オブジェクトを作ることができますが、幸いにして、`sfTestBrowser` は `lime_test` オブジェクトを返す `test()` メソッドを提示します。`sfTestFunctional` 経由でアサーションを行う方法に関してはリスト15-15で確認してください。

リスト15-17 - テストブラウザーは `test()` メソッドによるテスト機能を提供する

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1');
    $request  = $b->getRequest();
    $context  = $b->getContext();
    $response = $b->getResponse();

    // test()メソッドを通して lime_test メソッドにアクセスする
    $b->test()->is($request->getParameter('id'), 1);
    $b->test()->is($response->getStatuscode(), 200);
    $b->test()->is($response->getHttpHeader('content-type'), 'text/html;charset=utf-8');
    $b->test()->like($response->getContent(), '/edit/');

>**NOTE**
>`getResponse()`、`getContent()`、`getRquest()` と `test()`メソッドは `sfBrowser` オブジェクトを返さないので、これらのあとでは `sfBrowser` メソッド呼び出しのチェーンを使うことはできません。

リスト15-17で示されるように、リクエストオブジェクトとレスポンスオブジェクトを通して新旧の Cookie をチェックできます。

リスト15-17 - `sfBrowser` で Cookie をテストする

    [php]
    $b->test()->is($request->getCookie('foo'), 'bar');     // 入ってくる Cookie
    $cookies = $response->getCookies();
    $b->test()->is($cookies['foo'], 'foo=bar');            // 出て行く Cookie

リクエストの要素をテストするために `test()` メソッドを使うと長い行のコードを書くことになります。幸いにして、`sfTestFunctional` オブジェクトは機能テストを読みやすく短く保つ一連のプロキシメソッドを含みます。さらに、これらのメソッドはこれら自身で `sfTestFunctional` オブジェクトを返します。たとえば、リスト15-18で示されるように、リスト15-15をより速い方法で書き換えることができます。

リスト15-18 - `sfTestFunctional` で直接テストする

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1')->
    with('request')->isParameter('id', 1)->
    with('response')->begin()->
      isStatusCode()->
      isHeader('content-type', 'text/html; charset=utf-8')->
      matches('/edit/')->
    end()
    ;

各プロキシメソッドはテスターグループの一部です。テスターグループは、`with()` メソッドと `end()` メソッドの呼び出しで囲みます。`with()` メソッドには、テスターグループの名前を渡します(`request` や `response`)。

ステータス200は `isStatusCode()` メソッドによって求められるパラメーターのデフォルト値なので、連続するレスポンスをテストするために引数なしでこのメソッドを呼び出すことができます。

プロキシメソッドの利点は `lime_test` メソッドで出力テキストを指定する必要がないことです。メッセージはプロキシメソッドによって自動的に生成され、テストの出力は明快で読みやすいです。

    # get /foobar/edit/id/1
    ok 1 - request parameter "id" is "1"
    ok 2 - status code is "200"
    ok 3 - response header "content-type" is "text/html"
    ok 4 - response matches "/edit/"
    1..4

実際には、リスト15-17のプロキシメソッドは通常のテストの大部分をカバーするので、`sfTestFunctional` オブジェクト上で `test()` メソッドを使うことはめったにありません。

リスト15-15は `sfBrowser` メソッドは自動的にリダイレクトの後に続かないことを示しました。これは1つの利点を持ちます: リダイレクトをテストできることです。たとえば、リスト15-18はリスト15-19のレスポンスをテストする方法を示しています。

リスト15-19 - `sfTestFunctional` でリダイレクトをテストする

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->
      get('/foobar/edit/id/1')->
      click('go', array('name' => 'dummy'))->
      with('request')->begin()->
        isParameter('module', 'foobar')->
        isParameter('action', 'update')->
      end()->
      with('response')->begin()->
        isStatusCode(200)->
        isRedirected()->      // レスポンスがリダイレクトかどうかチェックする
      end()->

      followRedirect()->    // 手動でリダイレクトする

      with('request')->begin()->
        isRequestParameter('module', 'foobar')->
        isRequestParameter('action', 'show')->
      end()->
      with('response')->isStatusCode(200)
    ;

### CSS セレクタを使う

多くの機能テストはコンテンツのなかにテキストが存在することを確認することでページが正しいかを検証します。`matches()` メソッドで正規表現の助けを借りることで、表示されるテキスト、タグの属性、もしくは値をチェックできます。しかし、レスポンスの DOM に深く埋め込まれたものをチェックしたいのであれば、正規表現は理想的な方法ではありません。

`sfTestFunctional` オブジェクトが `getResponseDom()` メソッドをサポートするわけはそういうわけです。これは libXML2 の DOM オブジェクトを返し、解析とテストの実行はフラットなテキストよりもはるかに簡単です。このメソッドの使いかたの例はリスト15-20をご覧ください。

リスト15-20 - テストブラウザーは DOM オブジェクトとしてレスポンスの内容にアクセスできる

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1');
    $dom = $b->getResponseDom();
    $b->test()->is($dom->getElementsByTagName('input')->item(1)->getAttribute('type'),'text');

PHP の DOM メソッドによる HTML ドキュメントの解析は十分な速さで行われずまた簡単でもありません。CSS セレクタに慣れているのであれば、これらのセレクタが HTML ドキュメントから要素を読みとるためのより強力な方法であることをご存じでしょう。symfony は `sfDomCssSelector` と呼ばれるツールクラスを提供します。これは DOM ドキュメントをコンストラクターの必須パラメーターとし CSS セレクタにしたがって文字列の配列を返す `getValues()` メソッドと、DOM 要素の配列を返す `getElements()` メソッドを持ちます。リスト15-21の例をご覧ください。

リスト15-21 - テストブラウザーは `sfDomCssSelector` オブジェクトとしてのレスポンスの内容にアクセスできる

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1');
    $c = new sfDomCssSelector($b->getResponseDom())
    $b->test()->is($c->getValues('form input[type="hidden"][value="1"]'), array('');
    $b->test()->is($c->getValues('form textarea[name="text1"]'), array('foo'));
    $b->test()->is($c->getValues('form input[type="submit"]'), array(''));

簡潔さと明瞭さを絶えず追求するために、symfonyはショートカットを提供します: `response` テスターグループの `checkElement()` プロキシメソッドです。このメソッドはリスト15-22の内容をリスト15-21のようにします。

リスト15-22 - テストブラウザーは CSS セレクタによってレスポンス要素にアクセスできる

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1')->
      with('response')->begin()->
        checkElement('form input[type="hidden"][value="1"]', true)->
        checkElement('form textarea[name="text1"]', 'foo')->
        checkElement('form input[type="submit"]', 1)->
      end()
    ;

`checkElement()` メソッドのふるまいはそれが受けとる2番目の引数の型に依存します:

  * ブール値の場合、CSS セレクタにマッチする要素が存在するかチェックをします。
  * 整数の場合、CSS セレクタがこの数の結果を返すのかチェックをします。
  * 正規表現の場合、CSS セレクタによって見つかる最初の要素がそれにマッチするかチェックをします。
  * !で始まる正規表現の場合、パターンにマッチしない最初の要素をチェックします。 
  * そのほかの場合、CSS セレクタで見つかる最初の要素と2番目の引数を文字列として比較します。

メソッドは3番目のオプションパラメーターを連想配列の形式で受けとります。リスト15-23で示されるように、(セレクタがいくつかの要素を返す場合)セレクタによって返された最初の要素ではなく、特定の位置のほかの要素でテストが実行されます。

リスト15-23 - 特定の位置で要素にマッチする位置オプションを使う

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit?id=1')->
      with('response')->begin()->
        checkElement('form textarea', 'foo')->
        checkElement('form textarea', 'bar', array('position' => 1))->
      end()
    ;

オプションの配列は2つのテストを同時に実施するためにも使われます。リスト15-24で示されるように、セレクタが要素にマッチするかどうかとそれらが存在する数に関してテストできます。

リスト15-24 - マッチする数をカウントする count オプションを使う

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit?id=1')->
      with('response')->checkElement('form input', true, array('count' => 3));

セレクタのツールはとても強力です。これは CSS3 のセレクタの大部分を受け入れ、リスト15-25のような複雑なクエリに対して利用できます。

リスト15-25 - `checkElement()` が受け入れる複雑な CSS セレクタの例

    [php]
    ->checkElement('ul#list li a[href]', 'click me');
    ->checkElement('ul > li', 'click me');
    ->checkElement('ul + li', 'click me');
    ->checkElement('h1, h2', 'click me');
    ->checkElement('a[class$="foo"][href*="bar.html"]', 'my link');
    ->checkElement('p:last ul:nth-child(2) li:contains("Some text")');

### エラーをテストする

ときどき、アクションもしくはモデルが例外を故意に投じます (たとえば404ページを表示するため)。HTML の生成コードのなかの特定のエラーメッセージをチェックするために CSS セレクタを使う場合でも、リスト15-26で示されるように例外が投じられたことをチェックするために `throwsException` メソッドを使うほうがよいです。

リスト15-26 - 例外に対してテストを行う

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->
      get('/foobar/edit/id/1')->
      click('go', array('name' => 'dummy'))->
      throwsException()->                   // 最後のリクエストが例外を投じるかチェックする
      throwsException('RuntimeException')-> // 例外のクラスをチェックする
      throwsException(null, '/error/');     // 例外のメッセージが正規表現にマッチするかチェックする

### テスト環境でとり組む

`sfTestFunctional` オブジェクトは `test` 環境で設定される特別なフロントコントローラーを使います。この環境に対するデフォルト設定はリスト15-27で表されます。

リスト15-27 - テスト環境のデフォルト設定 (`frontend/config/settings.php`)

    test:
      .settings:
        error_reporting:        <?php echo ((E_ALL | E_STRICT) ^ E_NOTICE)."\n" ?>
        cache:                  false
        web_debug:              false
        no_script_name:         false
        etag:                   false

この環境においてキャッシュ (cache) と Web デバッグツールバー (web_debug) は `false` に設定されます。しかしながら、コードの実行は、`dev` 環境と `prod` 環境のログファイルは別にして、ログファイルにトレースされているので、それぞれのファイルを個別に確認できます (`myproject/log/frontend_test.log`)。この環境において、例外はスクリプトの実行を停止させません。1つのテストが失敗してもテスト全体のセットを実施できます。たとえば、テストデータを持つほかのデータベースを使うために、個別のデータベースの設定を持つことができます。

`sfBrowser` オブジェクトは使うまえに初期化しなければなりません。必要であれば、アプリケーションのホストの名前とクライアントのIPアドレスを指定できます。すなわち、これら2つのパラメーターを通してアプリケーションがコントロールする場合です。リスト15-28はこの方法を示しています。

リスト15-28 - ホスト名と IP でテストブラウザーをセットアップする

    [php]
    $b = new sfBrowser('myapp.example.com', '123.456.789.123');

### `test:functional` タスクを使う

`test:functional` タスクによって1つもしくは複数の機能テストを実施することが可能で、このタスクは受けとる引数の数に依存します。リスト15-29で示されるように、機能テストが最初の引数としてアプリケーションの名前を必要とすること以外、ルールは `test:unit` タスクのものと同じになります。

リスト15-29 - 機能テストのタスク構文

    // testディレクトリの構造
    test/
      functional/
        frontend/
          myModuleActionsTest.php
          myScenarioTest.php
        backend/
          myOtherScenarioTest.php

    ## 再帰的に、1つのアプリケーションに対してすべての機能テストを実行する
    $ php symfony test:functional frontend

    ## 1つの任意の機能テストを実行する
    $ php symfony test:functional frontend myScenario

    ## パターンに基づいていくつかのテストを実行する
    $ php symfony test:functional frontend my*

テストの命名慣習
----------------

このセクションではテストを整理して維持しやすい状態に保つためのいくつかの慣習の一覧を示します。使いこなすための秘訣はファイルの整理、ユニットテストと機能テストに関することです。

ファイル構造に関しては、テストする予定のクラス名でユニットテストのファイルを名づけ、テストする予定のモジュールもしくはシナリオの名前で機能テストを名づけます。例としてリスト15-30をご覧ください。`test/` ディレクトリはすぐに多くのファイルを収納するようになるので、これらのガイドラインに従わないと、長期間運用しているとテストを見つけるのが困難になる可能性があります。

リスト15-30 - ファイルの命名慣習の例

    test/
      unit/
        myFunctionTest.php
        mySecondFunctionTest.php
        foo/
          barTest.php
      functional/
        frontend/
          myModuleActionsTest.php
          myScenarioTest.php
        backend/
          myOtherScenarioTest.php

ユニットテストのためのよい習慣は関数もしくはメソッドによってテストを分類することと `diag()` 呼び出しでそれぞれのテストのグループを始めることです。それぞれのユニットテストのメッセージは関数の名前もしくは、テストされたメソッドを含み、動詞とプロパティの後に続くので、テストの出力はオブジェクトのプロパティを説明する文のように見えます。リスト15-31は例を示しています。

リスト15-31 - ユニットテストの命名慣習の例

    [php]
    // srttolower()
    $t->diag('strtolower()');
    $t->isa_ok(strtolower('Foo'), 'string', 'strtolower() は文字列を返す');
    $t->is(strtolower('FOO'), 'foo', 'strtolower() は入力を小文字に変換する');

    # strtolower()
    ok 1 - strtolower() は文字列を返す
    ok 2 - strtolower() は入力を小文字に変換する

機能テストはページによって分類されリクエストによって始まります。リスト15-32はこの慣習を説明しています。

リスト15-32 - 機能テストの命名慣習の例

    [php]
    $browser->
      get('/foobar/index')->
      with('request')->begin()->
        isParameter('module', 'foobar')->
        isParameter('action', 'index')->
      end()->
      with('response')->begin()->
        isStatusCode(200)->
        checkElement('body', '/foobar/')->
      end()
    ;

    # /comment/indexを取得する
    ok 1 - status code is 200
    ok 2 - request parameter module is foobar
    ok 3 - request parameter action is index
    ok 4 - response selector body matches regex /foobar/

この規約に従えば、プロジェクトの開発者のドキュメントとして使うさいにテストの出力は十分に明快なものになります。そしていくつかの場合においてドキュメントを実際に書かなくてもすみます。

特別なテストのニーズ
--------------------

たいていの場合、symfony によって提供されたユニットテストと機能テストのツールで十分です。自動テストにおける共通の問題を解決するためのいくつかの補足のテクニックの一覧をこのセクションに書いておきます: 孤立した環境でテストの立ち上げ、テストの範囲以内でデータベースにアクセスし、キャッシュのテスト、クライアントサイド上でインタラクションのテストを行うことです。

### テストをテストハーネスで実行する

`test:unit` と `test:functional` タスクは単独のテストもしくはテストのセットを立ち上げることができます。しかしながら、これらのタスクをパラメーターなしで呼び出す場合、これらは`test/`ディレクトリのなかのすべてのユニットテストと機能テストを立ち上げます。テストのあいだの汚染を回避するには、それぞれのテストファイルを独立したサンドボックスに分離する特定のメカニズムが必要です。さらに、(出力は何千行の長さになるので)その場合、単独のテストファイルのように同じ出力を続けるのは無意味なので、テストの結果は統合的なビューにまとめられます。これが多くのテストファイルを実行するためにテストハーネスを使う理由です。テストハーネス (test harness) は特別な機能を持つ自動テストフレームワークです。テストハーネスは `lime_harness` 呼ばれる lime フレームワークのコンポーネントに依存しています。リスト15-33のように、これはファイルごとのテストの状態と終了したテストの数の概要を示します。

リスト15-33 - すべてのテストをテストハーネスで立ち上げる

    $ php symfony test:all

    unit/myFunctionTest.php................ok
    unit/mySecondFunctionTest.php..........ok
    unit/foo/barTest.php...................not ok

    Failed Test                     Stat  Total   Fail  List of Failed
    ------------------------------------------------------------------
    unit/foo/barTest.php               0      2      2  62 63
    Failed 1/3 test scripts, 66.66% okay. 2/53 subtests failed, 96.22% okay.

テストは1つずつ呼び出すときと同じ方法で実行されます; 本当に便利にするために出力だけが短くなります。とりわけ、最後の表は失敗したテストに焦点を当てているので、これらのテストを見つけるための助けになります。

リスト15-34で示されるように、テストハーネスの `test:all` タスクを使うことですべてのテストを1つの呼び出しで起動できます。最新のリリース以降でリグレッション(回帰)が起こらないことを保証するために、この呼び出しはすべてのコードを運用環境に転送するまえに行うべきです。

リスト15-34 - プロジェクトのすべてのテストを立ち上げる

    $ php symfony test:all

### データベースにアクセスする

ユニットテストにおいてデータベースにアクセスすることがよく必要になります。最初に `sfBrowser::get()` を呼び出すときにデータベース接続は自動的に初期化されます。しかしながら `sfBrowser` を使うまえにもデータベースに接続したい場合、リスト15-35のように、手動で `sfDabataseManager` を初期化しなければなりません。

リスト15-35 - テストにおいてデータベースを初期化する

    [php]
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->loadConfiguration();

    // オプションとして、現在のデータベース接続を取得できる
    $con = Propel::getConnection();

テストを始めるまえにデータベースにフィクスチャを投入します。これは `sfPropelData` オブジェクトを通して行うことができます。リスト15-36で示されるように `propel:data-load` タスクのように、ファイルからもしくは配列から、このオブジェクトはデータをロードします。

リスト15-36 - テストファイルからデータベースに投入する

    [php]
    $data = new sfPropelData();

    // ファイルからデータをロードする
    $data->loadData(sfConfig::get('sf_data_dir').'/fixtures/test_data.yml');

    // 配列からデータをロードする
    $fixtures = array(
      'Article' => array(
        'article_1' => array(
          'title'      => 'foo title',
          'body'       => 'bar body',
          'created_at' => time(),
        ),
        'article_2'    => array(
          'title'      => 'foo foo title',
          'body'       => 'bar bar body',
          'created_at' => time(),
        ),
      ),
    );
    $data->loadDataFromArray($fixtures);

それから、あなたのテストのニーズに合わせて通常のアプリケーションのように Propel オブジェクトを使います。これらのファイルをユニットテストにインクルードすることを覚えておいてください (この章の以前の「スタブ、フィクスチャ、オートロード」のセクションで説明したように、これを自動化するために `sfSimpleAutoload` クラスを使うことができます)。Propel オブジェクトは機能テストにオートロードされます。

### キャッシュをテストする

アプリケーションに対してキャッシュを有効にしたとき、機能テストはキャッシュされたアクションが期待通りに動作するか検証します。

最初に行うべきことはテスト環境 (`settings.yml` ファイル) に対してキャッシュを有効にすることです。それから、ページがキャッシュから由来するものなのか、生成されたものであるのかをテストしたい場合、`view_cache` テストグループが提供する `isCached()` テストメソッドを使います。リスト15-37はこのメソッドの使いかたを示しています。

リスト15-37 - `isCached()` メソッドはキャッシュをテストする

    [php]
    <?php

    include dirname(__FILE__).'/../../bootstrap/functional.php';

    // 新しいテストブラウザーを作成する
    $b = new sfTestFunctional(new sfBrowser());

    $b->get('/mymodule');
    $b->with('view_cache')->isCached(true);       // レスポンスがキャッシュからやって来たことを確認する
    $b->with('view_cache')->isCached(true, true); // キャッシュされたレスポンスがレイアウトと一緒に来ることを確認する
    $b->with('view_cache')->isCached(false);      // レスポンスがキャッシュからやって来ないことを確認する

>**NOTE**
>機能テストの最初にキャッシュをクリアする必要はありません; ブートストラップのスクリプトが代行してくれます。

### クライアント上のインタラクションをテストする

ここまでで説明されたテクニックの主な難点は JavaScript をシミュレートできないことです。たとえば、Ajax インタラクションのようなとても複雑なインタラクションのために、ユーザーが行うマウスとキーボードの入力とクライアントサイド上でのスクリプトの実行を再現できることが必要です。通常、これらのテストは手作業で再現されますが、とても時間がかかりエラーになりがちです。

解決方法は [Selenium](http://seleniumhq.org/) と呼ばれるもので、完全に JavaScript で書かれたテストフレームワークです。このツールは、現在のブラウザーウィンドウを利用して、通常のユーザーが行うようなページ上のアクションのセットを実行します。`sfBrowser` オブジェクトを越える利点は Slemenium がページ内で JavaScript を実行できるので、Ajax インタラクションも Slemenium でテストできることです。

symfony は Selenium をデフォルトで搭載していません。これをインストールするには、`web/` ディレクトリのなかで新たに `selenium/` ディレクトリを作り、Selenium [アーカイブ](http://seleniumhq.org/download/)の内容を展開する必要があります。なぜなら、Selenium は JavaScript に依存するので、たいていのブラウザー内のセキュリティ設定の基準に従えば、アプリケーションに関して同じホストとポート上で JavaScript が利用できないかぎり、Selenium の動作が許可されないからです。

>**CAUTION**
>`selenium/` ディレクトリを運用サーバーに直接転送しないように気をつけてください。ブラウザーを通して誰でもWebドキュメントのルートにアクセスできるからです。

Selenium テストは HTML 形式で記述され `web/slenium/tests/` ディレクトリに保存されます。たとえば、リスト15-38は、ホームページがロードされ、click me のリンクがクリックされ、レスポンスの「Hello, World」のテキストが探される機能テストを示します。テスト環境でアプリケーションにアクセスするには、`frontend_test.php` フロントコントローラーを指定する必要があります。

リスト15-38 - Selenium テストのサンプル (`web/selenium/test/testIndex.html`)

    [php]
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
      <meta content="text/html; charset=UTF-8" http-equiv="content-type">
      <title>Index tests</title>
    </head>
    <body>
    <table cellspacing="0">
    <tbody>
      <tr><td colspan="3">First step</td></tr>
      <tr><td>open</td>              <td>/frontend_test.php/</td> <td>&nbsp;</td></tr>
      <tr><td>clickAndWait</td>      <td>link=click me</td>    <td>&nbsp;</td></tr>
      <tr><td>assertTextPresent</td> <td>Hello, World!</td>    <td>&nbsp;</td></tr>
    </tbody>
    </table>
    </body>
    </html>

テストケースはコマンド、ターゲット、値の3つのカラムを持つテーブルを含む HTML ドキュメントによって表現されます。すべてのコマンドは値をとりません。コマンドが値をとらない場合、カラムを空白にしておくか、テーブルを見やすくするために `&nbsp;` を使うことです。コマンドの完全な一覧は Selenium の Web サイトを参照してください。

同じディレクトリに設置された `TestSuite.html` ファイル内に新しい行を挿入することで、このテストをグローバルテストスイートに追加する必要があります。リスト15-39はこれを行う方法を示しています。

リスト15-39 - テストスイートにテストファイルを追加する (`web/selenium/test/TestSuite.html`)

    ...
    <tr><td><a href='./testIndex.html'>My First Test</a></td></tr>
    ...

テストを実施するには、つぎの URL にブラウザーでアクセスします。

    http://myapp.example.com/selenium/index.html

Main Test Suite を選択し、すべてのテストを実行するボタンをクリックし、実施するように伝えたステップをブラウザーが再現する様子を観察してください。

>**NOTE**
>Selenium のテストは本当のブラウザーで動作するので、これらによってブラウザーの不一致もテストできます。1つのブラウザーでテストを作り、単独のリクエストで動作することになっているサイト上でそのほかのすべてのブラウザー上で Selenium のテストを実施してください。

Selenim は HTML で書かれているので、Selenium のテストを書くことは面倒でした。しかし、Firefox の Selenium [拡張機能](http://seleniumhq.org/projects/ide/)のおかげで、テストを実施するために必要なことはレコードセッションで1回のテストを実施するだけです。レコードセッションでナビゲートする一方で、ブラウザーのウィンドウ内で右クリックをしてポップアップメニュー内の Append Selenium Command のもとで適切なチェック項目を選択することで、アサート型のテストを追加できます。

アプリケーションに対してテストスイートを実施するためにテストを HTML ファイルに保存できます。Firefox の拡張機能によって記録した Selenium テストも実行できるようになります。

>**NOTE**
>Selenium テストを立ち上げるまえにテストデータを再び初期化することを忘れないでください。

まとめ
----

自動テストとしてメソッドもしくは関数を検証するユニットテスト (unit test) と機能を検証する機能テスト (functional test) が存在します。symfony はユニットテストのための lime テストフレームワークに依存し、機能テスト用に特化した `sfBrowser` クラスと `sfTestFunctional` クラスを提供します。これらのテストツールは、CSS セレクタのように、両方とも基礎から応用まで及ぶ多くのアサーションメソッドを提供します。テストを起動させるには symfony コマンドラインを使います。1つずつ実施するには `test:unit` タスクもしくは `test:functional`  タスクを使い、テストハーネスを実施するには `test-all` タスクを使います。データを扱うとき、自動テストはフィクスチャ (fixture) とスタブ (stub) を使い、これは symfony のユニットテストのなかで簡単に実現されます。

(おそらくテスト駆動開発 (TDD) の方法論を利用して) アプリケーションの大部分をカバーするために十分なユニットテストをかならず書けば、内部をリファクタリングするもしくは新しい機能を追加するときに、安心感を得られドキュメントを作る時間を節約することもできます。
