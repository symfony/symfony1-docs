Appendix A - モデルレイヤーの内側(Propel)
=========================================

これまでのところ、ページを作りリクエストとレスポンスを処理することをおもに検討してきました。しかしながら Web アプリケーションのビジネスロジックの多くはデータモデルに依存しています。symfony のデフォルトのモデルコンポーネントはオブジェクト/リレーショナルマッピングのレイヤーに基づいています。symfonyには、２つのメジャーなPHP ORM: [Propel](http://www.propelorm.org/) と [Doctrine](http://www.doctrine-project.org/) がバンドルされています。symfony のアプリケーションでは、アプリケーションの開発者はオブジェクトを通してデータベースに保存されたデータにアクセスし、オブジェクトを通してこれを修正します。データベースに直接とり組むことはありません。このことによって高い抽象性と移植性が維持されます。

この章では、オブジェクトのデータモデルを作成する方法と、Propel のデータにアクセスして修正する方法を説明します。これは Propelが symfony に統合されていることも実証します。

なぜ ORM と抽象化レイヤーを利用するのか？
---------------------------------------

データベースはリレーショナルです。一方で PHP 5 と symfony はオブジェクト指向です。オブジェクト指向のコンテキストでもっとも効果的にデータベースにアクセスするには、オブジェクトをリレーショナルなロジックに変換するインターフェイスが求められます。1章で説明されたように、このインターフェイスはオブジェクトリレーショナルマッピング (ORM - Object-Relational Mapping) と呼ばれ、データにアクセス可能でオブジェクトの範囲でビジネスルールを維持するオブジェクトで構成されます。

ORM の主な利点は再利用性です。アプリケーションのさまざまな部分から、異なるアプリケーションからでも、データオブジェクトのメソッドを呼び出すことができます。ORM レイヤーはデータロジックもカプセル化します。たとえば、行われた投稿回数とそれらの投稿がどれだけ人気なのかをもとにフォーラムのユーザーの評価を計算する方法です。ページがそのようなユーザーの評価を表示する必要があるとき、symfony は詳細な計算に悩むことなくデータモデルのメソッドを簡単に呼び出します。計算方法があとで変わった場合、必要なのはモデルの評価メソッドを修正するだけで、アプリケーションの残りの部分はそのままにできます。

レコードの代わりにオブジェクトを使い、テーブルの代わりにクラスを使うことには別の利点があります: これらによって新しいアクセサーをテーブルのカラムにかならずしもマッチしないオブジェクトに追加できます。`client` という名前のテーブルが存在し、これが `first_name` と `last_name` という2つのフィールドを持つ場合に `Name` だけを求めることを考えます。オブジェクト指向の世界において、リスト8-1のように、`Client` クラスに新しいアクセサーメソッドを追加することと同じぐらい簡単です。アプリケーションの観点から、`Client` クラスの `FirstName`、`LastName` と `Name` 属性のあいだの違いは存在しません。クラス自身がどの属性がデータベースのカラムに対応するのかを決定できます。

リスト8-1 - アクセサーはモデルクラスの実際のテーブル構造を覆い隠す

    [php]
    public function getName()
    {
      return $this->getFirstName().' '.$this->getLastName();
    }

繰り返されるすべてのデータアクセス関数とデータのビジネスロジック自身はこのようなオブジェクトのなかに保たれます。`Items` (オブジェクト) を持つ `ShoppingCart` クラスを考えてみましょう。精算のためにショッピングカートの合計金額を得る方法は、リスト8-2で示されるように、実際の計算をカプセル化するカスタムメソッドを書くことです。

リスト8-2 - アクセサーはデータロジックを覆い隠す

    [php]
    public function getTotal()
    {
      $total = 0;
      foreach ($this->getItems() as $item)
      {
        $total += $item->getPrice() * $item->getQuantity();
      }

      return $total;
    }

データとアクセスの手順を設けるときに考慮すべき別の重要な点があります: データベースベンダーは異なる SQL 構文の方言を使います。ほかのデータベースマネジメントシステム (DBMS) に切り替えると以前の DBMS のために設計されたSQLクエリの部分を書き直さなければなりません。データベースから独立した構文を使うクエリを作り、サードパーティのコンポーネントに実際の SQL の翻訳を任せておけば、苦痛をともなわずにデータベースの構文を切り替えることができます。これがデータベースの抽象化レイヤーの目的です。これによってクエリに対して特定の構文を使うことが強制され、DBMS の固有機能に適合して SQL コードを最適化する汚い作業が推進されます。

抽象化レイヤーの主な利点は移植性です。これによって、プロジェクトなかばでも、別のデータベースに切り替えることができます。アプリケーションのプロトタイプを迅速に書く必要があるが、顧客が自身のニーズに最適なデータベースシステムがどれなのかを決断していない場合を考えてみましょう。SQLite でアプリケーションの開発を始めることが可能であり、たとえば、顧客が決断をする準備ができたときに、MySQL、PostgreSQL、または Oracle に切り替えます。設定ファイルの一行を変更すれば、アプリケーションは動きます。

symfony は Propel や Doctrine を ORM として利用し、これらはデータベースの抽象化のために PDO (PHP Data Objects) を利用します。これら2つのサードパーティのコンポーネントは、両方とも Propel と Doctrine の開発チームによって開発され、symfony にシームレスに統合されているので、これらをフレームワークの一部としてみなすことができます。この章で説明しますが、これらの構文と規約はできるかぎり symfony のものとは異ならないように採用されました。

>**NOTE**
>symfony のプロジェクトにおいて、すべてのアプリケーションは同じモデルを共有します。これがプロジェクトレベルの肝心な点: 共通のビジネスルールに依存するアプリケーションを再編することです。モデルがアプリケーションから独立しており、モデルのファイルがプロジェクトのルートの `lib/model/` ディレクトリに保存される理由です。

symfony のデータベーススキーマ
------------------------------

symfony が使うデータオブジェクトモデルを作るために、データベースが持つリレーショナルモデルはどんなものでもオブジェクトデータモデルに翻訳する必要があります。ORM はマッピングを行うためにリレーショナルモデルの記述が必要です。これを記述するものはスキーマ (schema) と呼ばれます。スキーマにおいて、開発者はテーブル、それらのリレーションシップ、とカラムの特徴を定義します。

symfony のスキーマ構文は YAML フォーマットを利用します。`schema.yml` ファイルは `myproject/config/` ディレクトリ内部に設置しなければなりません。

>**NOTE**
>symfony はこの章のあとのほうにある "schema.yml を越えて: schema.xml" のセッションで説明される Propel ネイティブな XML 形式のスキーマも理解することになります。

### スキーマの例

データベースの構造をスキーマにどのように変換しますか？具体例は理解するための最良の方法です。2つのテーブル: `blog_article` と `blog_comment` を持つブログのデータベースを想像してください。テーブルの構造は図8-1で示されています。

図8-1 - ブログのデータベースのテーブル構造

![ブログのデータベースのテーブル構造](http://www.symfony-project.org/images/book/1_4/F0801.png "ブログのデータベースのテーブル構造")

関連する `schema.yml` ファイルはリスト8-3のようになります。

リスト8-3 - `schema.yml` のサンプル

    [yml]
    propel:
      blog_article:
        _attributes: { phpName: Article }
        id:          ~
        title:       varchar(255)
        content:     longvarchar
        created_at:  ~
      blog_comment:
        _attributes: { phpName: Comment }
        id:               ~
        blog_article_id:  ~
        author:      varchar(255)
        content:     longvarchar
        created_at:       ~

データベース自身 (`blog`) の名前が `schema.yml` に登場しないことに注目してください。代わりに、データベースの内容は接続名 (この例では `propel`) の下に記述されます。これは実際の接続設定はアプリケーションが稼働している環境に依存する可能性があるからです。たとえば、開発環境においてアプリケーションを稼働させるとき、開発データベース (たとえば `blog_dev`) にアクセスすることになりますが、運用のデータベースも同じスキーマを使います。接続設定は `databases.yml` ファイルのなかで指定されます。このファイルはこの章のあとのほうの"データベースの接続"のセクションで説明します。スキーマは、データベースの抽象化を保つために、詳細な接続情報の設定を収めず、接続名だけを収めます。

### スキーマの基本構文

`schema.yml` ファイルにおいて、最初のキーは接続名を表します。これは、テーブルを複数格納することができます。それぞれのテーブルはカラムのセットを持ちます。YAML 構文に従い、キーはコロン (:) で終わり、構造はインデント (1つか複数のスペース、ただしタブはなし) を通して示されます。

テーブルは `phpName` (生成クラスの名前) を含めて、特別な属性を持つことができます。`phpName` がテーブルに記載されていない場合、symfony はそのテーブルをキャメルケースバージョンの名前で作ります。

>**TIP**
>キャメルケースの規約によれば単語からアンダースコアをとり除き、内部の単語の最初の文字を大文字にします。`blog_article` と `blog_comment` のデフォルトのキャメルケースのバージョンは `BlogArticle` と `BlogComment` です。この規約名は長い単語内部の大文字がラクダのコブに見えることから由来しています。

テーブルはカラムを格納します。カラムの値は3つの異なる方法で定義できます:

  * 何も定義していない場合(YAML中の`~`は、PHPでいうところの`null`のことです)、symfony はカラムの名前といくつかの規約にしたがってベストな属性を推測します。カラムの名前と規約はこの章の後のほうにある"空のカラム"のセクションで説明します。たとえば、リスト8-3にある `id` カラムは定義する必要はありません。symfony はそれを、オートインクリメントの整数型で、テーブルの主キーと定義します。`blog_comment` テーブルの `blog_article_id` は`blog_article` テーブルへの外部キーとして理解されます (`_id` で終わるカラムは外部キーとして見なされ、関連するテーブルはカラム名の最初の部分にしたがって自動的に決定されます)。`created_at` という名前のカラムは自動的に `timestamp` 型に設定されます。これらすべてのカラムに対して、型を指定する必要はありません。それが `schema.yml` を書くことがなぜ簡単であるかの理由の1つです。 

  * 1つの属性だけを定義する場合、これはカラム型です。symfony は通常のカラム型を理解します: `boolean`、`integer`、`float`、`date`、`varchar(size)`、 `longvarchar` などです(たとえば MySQL では `text` に変換されます)。256文字を越えるテキストの内容に関しては、サイズを持たない `longvarchar` 型 (MySQL では65KBを越えることはできません) を使う必要があります。

  * ほかのカラム属性を定義する必要がある場合 (デフォルト値、必須の値など)、カラム属性を `key: value` のセットとして書きます。このスキーマの拡張構文はこの章のあとのほうで説明します。

カラムは大文字で始まるバージョンの名前 (`Id`、`Title`、`Content` など) である `phpName` 属性を持ち、たいていの場合、オーバーライドする必要はありません。

少数のデータベース固有の構造の定義と同様に、テーブルは明示的な外部キーとインデックスを収めることができます。詳しく学ぶにはこの章の後のほうにある"スキーマの拡張構文"のセクションを参照してください。

モデルクラス
------------

スキーマは ORM レイヤーのモデルクラスをビルドするために使われます。作業時間を節約するために、これらのクラスは `propel:build-model` という名前のコマンドラインタスクによって生成されます。

    $ php symfony propel:build-model

>**TIP**
>モデルをビルドしたあとで、symfony が新しく生成されたモデルを見つけられるように、`php symfony cc` で symfony の内部キャッシュをクリアすることをお忘れなく。

このコマンドを入力することでプロジェクトの `lib/model/om/` ディレクトリでスキーマの解析と基底データモデルクラスの生成が実行されます:

  * `BaseArticle.php`
  * `BaseArticlePeer.php`
  * `BaseComment.php`
  * `BaseCommentPeer.php`

さらに、実際のデータモデルクラスは `lib/model/` のなかに作られます:

  * `Article.php`
  * `ArticlePeer.php`
  * `Comment.php`
  * `CommentPeer.php`

2つのテーブルだけを定義したので、8つのファイルで終わります。間違ったことは何もありませんが、いくつかの説明をする必要があります。

### 基底とカスタムクラス

2つのバージョンのデータオブジェクトを2つの異なるディレクトリに保存するのはなぜでしょうか？

おそらくモデルのオブジェクトにカスタムメソッドとプロパティを追加することが必要になります (リスト8-1の `getName()` メソッドを考えてください)。しかしプロジェクトの開発では、テーブルもしくはカラムも追加することになります。`schema.yml` ファイルを変更するたびに、`propel:build-model` を新たに呼び出してオブジェクトモデルクラスを再生成する必要があります。カスタムメソッドが実際に生成されたクラスのなかに書かれているとしたら、それらはそれぞれが生成されたあとで削除されます。

`lib/model/om/` ディレクトリに保存される `Base` クラスはスキーマから直接生成されたものです。これらを修正すべきではありません。すべての新しいモデルのビルドによってこれらのファイルが完全に削除されるからです。

一方で、`lib/model/` ディレクトリのなかに保存される、カスタムオブジェクトクラスは実際には `Base` クラスを継承します。既存のモデルで `propel:build-model` タスクが呼び出されるとき、これらのクラスは修正されません。ですのでここがカスタムメソッドを追加できる場所です。

リスト8-4は `propel:build-model` タスクを最初に呼び出したときに作成されるカスタムモデルクラスの例を示しています。

リスト8-4 - モデルクラスのファイルのサンプル (`lib/model/Article.php`)

    [php]
    class Article extends BaseArticle
    {
    }

これは `BaseArticle` クラスのすべてのメソッドを継承しますが、スキーマの修正からは影響を受けません。

基底クラスを拡張するカスタムクラスのメカニズムによって、データベースの最終的なリレーショナルモデルを知らなくても、コードを書き始めることができます。関連ファイルの構造によってモデルはカスタマイズ可能で発展性のあるものになります。

### オブジェクトクラスとピアクラス

`Article` と `Comment` はデータベースのレコードを表すオブジェクトクラスです。これらはレコードのカラムと関連レコードにアクセスできます。リスト8-5で示される例のように、このことは `Article` オブジェクトのメソッドを呼び出すことで、記事のタイトルを知ることができることを意味します。

リスト8-5 - レコードカラムのゲッターはオブジェクトクラスのなかで使える

    [php]
    $article = new Article();
    // ...
    $title = $article->getTitle();

`ArticlePeer` と `CommentPeer` は対のクラスです; すなわち、テーブル上で実行するスタティックメソッドを含むクラスです。これらはテーブルからレコードを検索する方法を提供します。リスト8-6で示されるように、通常これらのメソッドは関連オブジェクトクラスのオブジェクトもしくはオブジェクトのコレクションを返します。

リスト8-6 -　レコードを検索するスタティックメソッドは対のクラスのなかで使える

    [php]
    // $articles は Article クラスのオブジェクトの配列
    $articles = ArticlePeer::retrieveByPks(array(123, 124, 125));

>**NOTE**
>データモデルの観点から、複数の対のオブジェクトは存在できません。対のクラスのメソッドが通常の `->` (インスタンスメソッドの呼び出し) の代わりに `::` (スタティックメソッドの呼び出し) で呼び出されるのはそういうわけです。

基底とカスタムバージョンのオブジェクトクラスと対のクラスを結合した結果はスキーマのなかに記述されたテーブルごとに生成された4つのクラスになります。実際には、5番目の生成クラスが `lib/model/map/` ディレクトリに存在します。このディレクトリは実行環境のために必要なテーブルについてのメタデータ情報を含みます。しかしながら、おそらくこのクラスを変更することはないので、忘れてもかまいません。

データにアクセスする
-------------------

symfony では、オブジェクトを通してデータにアクセスします。リレーショナルモデルとデータを検索し変更する SQL を使うことに慣れていたら、オブジェクトモデルのメソッドは複雑に見えるかもしれません。しかし、ひとたびデータアクセスのためのオブジェクト指向の力を味わえば、とても好きになることでしょう。

しかし最初は、同じ用語を共有していることを確認してみましょう。リレーショナルデータモデルとオブジェクトデータモデルは似たような概念を使いますが、これらはお互いに独自の命名法を持ちます:

リレーショナル     | オブジェクト指向
------------------ | ----------------
テーブル           | クラス
列、レコード       | オブジェクト
フィールド、カラム | プロパティ

### カラムの値を検索する

symfony はモデルをビルドするとき、`schema.yml`で 定義されるそれぞれのテーブルごとに1つの基底オブジェクトクラスを作ります。それぞれのクラスはカラム定義をもとにデフォルトのコンストラクター、アクセサー、ミューテーターを備えています: リスト8-7で示されるように、`new`、`getXXX()`、`setXXX()` メソッドはオブジェクトを作りオブジェクトのプロパティにアクセスする作業を助けます。

リスト8-7 - 生成オブジェクトクラスのメソッド

    [php]
    $article = new Article();
    $article->setTitle('初めての記事');
    $article->setContent("これは初めての記事です。\n 皆様が楽しんでくださることを祈っています！");

    $title   = $article->getTitle();
    $content = $article->getContent();

>**NOTE**
>生成オブジェクトクラスは `Article` と呼ばれ、`blog_article` テーブルに渡される `phpName` です。`phpName` がスキーマで定義されていない場合、クラスは `BlogArticle` という名前になります。アクセサーとミューテーターはキャメルケース形式のカラム名を使うので、`getTitle()` メソッドは `title` カラムの値を検索します。

リスト8-8で示されるように一度に複数のフィールドを設定するには、それぞれのオブジェクトクラスに対して生成される、 `fromArray()` メソッドを使います。

リスト8-8 - `fromArray()` メソッドは複数のセッターである

    [php]
    $article->fromArray(array(
      'title'   => '初めての記事',
      'content' => 'これは初めての記事です。\n 皆様が楽しんでくださることを祈っています！',
    ));

>**NOTE**
>`fromArray()`メソッドは第２引数に`keyType`をとります。これを指定することによって、配列のキーにどのような値を設定するかを指定できます。指定できるクラス定数は、`BasePeer::TYPE_PHPNAME`、 `BasePeer::TYPE_STUDLYPHPNAME`、 `BasePeer::TYPE_COLNAME`、 `BasePeer::TYPE_FIELDNAME`、 `BasePeer::TYPE_NUM`があります。デフォルトのキータイプはカラムのPhpName(たとえば、`AuthorId`)です。

### 関連レコードを検索する

`blog_comment` テーブルの `blog_article_id` カラムは明示的に外部キーを`blog_article`テーブルに定義します。それぞれのコメントは1つの記事に関連し、1つの記事は多くのコメントを持つことができます。生成クラスは次のようにこのリレーションシップをオブジェクト指向の方法に翻訳する5つのメソッドを収めます:

  * `$comment->getArticle()`: 関連する `Article` オブジェクトを取得する
  * `$comment->getArticleId()`: 関連する `Article` オブジェクトの ID を取得する
  * `$comment->setArticle($article)`: 関連する `Article` オブジェクトを定義する
  * `$comment->setArticleId($id)`: ID から関連する `Article` オブジェクトを定義する
  * `$article->getComments()`: 関連する `Comment` オブジェクトを取得する

`getArticleId()` と `setArticleId()` メソッドは開発者が `blog_article_id` カラムを通常のカラムとみなしてリレーションシップを手動で設定できることを示します。しかしこれらはあまり面白いものではありません。オブジェクト指向のアプローチの利点はほかの3つのメソッドでおおいにあきらかになります。リスト8-9は生成されたセッターの使い方を示します。

リスト8-9 - 外部キーは特別なセッターに翻訳*される*

    [php]
    $comment = new Comment();
    $comment->setAuthor('Steve');
    $comment->setContent('うわ～、すごい、感動的だ: 最高の記事だよ！');

    // このコメントを以前の $article オブジェクトに加える
    $comment->setArticle($article);

    // 代替構文はすでにオブジェクトがデータベースに保存されている場合のみ意味をなす
    $comment->setArticleId($article->getId());

リスト8-10は生成されるゲッターの使い方を示しています。これはモデルオブジェクトのメソッドチェーンの使い方も示しています。

リスト8-10 - 外部キーは特別なゲッターに翻訳される

    [php]
    // 多対一のリレーションシップ
    echo $comment->getArticle()->getTitle();
     => 初めての記事
    echo $comment->getArticle()->getContent();
     => これは初めての記事です。皆様が楽しんでくださることを祈っています！

    // 一対多のリレーションシップ
    $comments = $article->getComments();

`getArticle()` メソッドは `getTitle()` アクセサーから恩恵を受ける `Article` クラスのオブジェクトを返します。これは開発者自身が JOIN を行うよりも優れており、(`$comment->getArticleId()` の呼び出しから始まる) わずかな行のコードしか必要としません。

リスト8-10の `$comments` 変数は `Comment` クラスのオブジェクト配列を収めます。`$comments[0]` で最初のものを表示する、もしくは `foreach($comments as $comment)` によるコレクションを通して繰り返すことができます。

>**NOTE**
>モデルからのオブジェクトは規約によって単数形の名前で定義されるのはなぜなのかこれで理解できます。`blog_comment` テーブルで定義される外部キーによって `getComments()` メソッドが作られます。`getComments()` メソッドの名前は `Comment` オブジェクトの名前に `s` をつけ足したものです。モデルオブジェクトに複数形の名前をつけると、`getCommentss()` と名づけられた無意味なメソッドが生成されることになります。

### データの保存と削除を行う

`new` コンストラクターを呼び出すことで、新しいオブジェクトが作られましたが、`blog_article` テーブルのなかには実際のレコードが作成されていません。オブジェクトを修正してもデータベースは何も影響を受けません。データをデータベースに保存するには、オブジェクトの `save()` メソッドを呼び出す必要があります。

    [php]
    $article->save();

ORM は賢いのでオブジェクトのあいだのリレーションシップを検出し、`$article` オブジェクトを保存することで関連する `$comment` オブジェクトも保存されます。symfony は保存されるオブジェクトがデータベースのなかに既存の対応部分を持つことも知っているので、`save()` の呼び出しはときどき `INSERT` もしくは `UPDATE` によって SQL に翻訳されます。主キーは `save()` メソッドによって自動的に設定されるので、保存後に、新しい主キーを `$article->getId()` で検索できます。

>**TIP**
>`isNew()` を呼び出すことでオブジェクトが新しいかどうかをチェックできます。修正されたオブジェクトを保存すべきかどうか判断がつかなければ、`isModified()` メソッドを呼び出します。

記事のコメントを読む場合、記事をインターネットに公開するのか気が変わることがあります。記事の評論家の皮肉が面白くないのであれば、リスト8-11で示されるように、`delete()` メソッドでコメントを簡単に削除できます。

リスト8-11 - 関連オブジェクトの `delete()` メソッドでデータベースからレコードを削除する

    [php]
    foreach ($article->getComments() as $comment)
    {
      $comment->delete();
    }

>**TIP**
>`delete()` メソッドを呼び出したあとでも、リクエストが終了するまでオブジェクトは利用できます。データベースのなかでオブジェクトが削除されることを確認するには、`isDeleted()` メソッドを呼び出します。

### レコードを主キーで検索する

特定のレコードの主キーを知っている場合、関連オブジェクトを検索するにはピアクラスの `retrieveByPk()` クラスメソッドを使います。

    [php]
    $article = ArticlePeer::retrieveByPk(7);

`schema.yml` ファイルは `id` フィールドを `blog_article` の主キーとして定義します。このステートメントは実際には`id`が7である記事を返します。主キーを使いましたので、あなたは1つのレコードだけが返されることを知っています; `$article` 変数は `Article` クラスのオブジェクトを収めます。

場合によっては、主キーが複数のカラムで構成されます。このような場合には、`retrieveByPK()` メソッドは複数のパラメーターをとり、主キーのカラムごとのパラメーターは1つです。

生成された `retrieveByPKs()` メソッドを呼び出すことで、主キーをもとに複数のオブジェクトを選ぶこともできます。`retireveByPKs()` メソッドの必須パラメーターは主キーの配列です。

### レコードを Criteria で検索する

複数のレコードを検索したいとき、検索したいオブジェクトに対応する対のクラスの `doSelect()` メソッドを呼び出す必要があります。たとえば、`Article` クラスのオブジェクトを検索するには、`ArticlePeer::doSelect()` を呼び出します。

`doSelect()` メソッドの最初のパラメーターは `Criteria` クラスのオブジェクトです。`Criteria` クラス (「基準」) はデータベース抽象化のためにSQLなしで定義されたシンプルなクエリの定義クラスです。

空の `Criteria` はすべてのクラスのオブジェクトを返します。たとえば、リスト8-12で示されるコードはすべての記事を検索します。

リスト8-12 - 空の Criteria -- Criteria と `doSelect()` でレコードを検索する

    [php]
    $c = new Criteria();
    $articles = ArticlePeer::doSelect($c);

    // 上記のコードは次のSQLクエリになります
    SELECT blog_article.ID, blog_article.TITLE, blog_article.CONTENT,
           blog_article.CREATED_AT
    FROM   blog_article;

>**SIDEBAR**
>ハイドレイティング (hydrating)
>
>`::doSelect()` 呼び出しは実際にはシンプルなSQLクエリよりはるかに強力です。最初に、SQL は選択した DBMS のために最適化されます。2番目に、`Criteria` に渡されるどの値もSQLコードに統合される前にエスケープされ、SQL インジェクションのリスクが予防されます。3番目に、メソッドは、結果セットではなく、オブジェクト配列を返します。ORM はデータベースの結果セットをもとにオブジェクトを自動的に作成し投入します。このプロセスはハイドレイティング (hydrating) と呼ばれます。

より複雑なオブジェクトを選ぶには、`WHERE`、`ORDER BY`、`GROUP BY`、およびほかのSQLステートメントと同等のものが必要です。`Criteria` オブジェクトはこれらすべての条件のためのメソッドとパラメーターを持ちます。たとえば、リスト8-13のように、Steve によって書かれ、日付順に並べ替えられた、すべてのコメントを取得するには、`Criteria` をビルドします。

リスト8-13 - `Criteria` と `doSelect()` とレコードを検索する -- 条件つき `Criteria`

    [php]
    $c = new Criteria();
    $c->add(CommentPeer::AUTHOR, 'Steve');
    $c->addAscendingOrderByColumn(CommentPeer::CREATED_AT);
    $comments = CommentPeer::doSelect($c);

    // 上記のコードは次のような SQL クエリになる
    SELECT blog_comment.ARTICLE_ID, blog_comment.AUTHOR, blog_comment.CONTENT,
           blog_comment.CREATED_AT
    FROM   blog_comment
    WHERE  blog_comment.author = 'Steve'
    ORDER BY blog_comment.CREATED_AT ASC;

`add()` メソッドのパラメーターとして渡されるクラスの定数はプロパティ名を参照します。これらの定数はカラム名の大文字バージョンから名前をつけられます。たとえば、`blog_article` テーブルの `content` カラムを扱うには、`ArticlePeer::CONTENT` クラス定数を使います。

>**NOTE**
>なぜ `blog_comment.AUTHOR` の代わりに `CommentPeer::AUTHOR` を使うのか？ SQL クエリに出力される方法はどちらなのか？データベースの `author` フィールドの名前を `contributor` に変更する必要がある場合を考えてみましょう。`blog_comment.AUTHOR` を使う場合、モデル上のすべての呼び出しを変更しなければなりません。一方で、`CommentPeer::AUTHOR` を使う場合、必要なのは `schema.yml` のカラム名を変更し、`phpName` を `AUTHOR` として保存し、モデルをリビルドすることだけです。

テーブル8-1は SQL と `Criteria` オブジェクトの構文を比較します。

テーブル8-1 - SQL と `Criteria` オブジェクトの構文

SQL                                                          | Criteria
------------------------------------------------------------ | -----------------------------------------------
`WHERE column = value`                                       | `->add(column, value);`
`WHERE column <> value`                                      | `->add(column, value, Criteria::NOT_EQUAL);`
**ほかの比較演算子**                                          | 
`> , <`                                                      | `Criteria::GREATER_THAN, Criteria::LESS_THAN`
`>=, <=`                                                     | `Criteria::GREATER_EQUAL, Criteria::LESS_EQUAL`
`IS NULL, IS NOT NULL`                                       | `Criteria::ISNULL, Criteria::ISNOTNULL`
`LIKE, ILIKE`                                                | `Criteria::LIKE, Criteria::ILIKE`
`IN, NOT IN`                                                 | `Criteria::IN, Criteria::NOT_IN`
**ほかのSQLキーワード**                                       | 
`ORDER BY column ASC`                                        | `->addAscendingOrderByColumn(column);`
`ORDER BY column DESC`                                       | `->addDescendingOrderByColumn(column);`
`LIMIT limit`                                                | `->setLimit(limit)`
`OFFSET offset`                                              | `->setOffset(offset) `
`FROM table1, table2 WHERE table1.col1 = table2.col2`        | `->addJoin(col1, col2)`
`FROM table1 LEFT JOIN table2 ON table1.col1 = table2.col2`  | `->addJoin(col1, col2, Criteria::LEFT_JOIN)`
`FROM table1 RIGHT JOIN table2 ON table1.col1 = table2.col2` | `->addJoin(col1, col2, Criteria::RIGHT_JOIN)`

>**TIP**
>生成クラスで利用可能なメソッドがどれなのか見つけて理解するためのベストの方法は、生成後に `lib/model/om/` フォルダーの `Base` ファイルを見ることです。メソッドの名前はとてもわかりやすいですが、これらに関する詳細なコメントが必要な場合、`config/propel.ini` ファイルの `propel.builder.addComments` パラメーターを `true` にセットして、モデルをリビルドします。

リスト8-14は複数の条件つきの `Criteria` のほかの例を示します。日付順に並べ替えられた "enjoy" の単語を含む記事の Steve によるすべてのコメントを検索する。

リスト8-14 - `Criteria` と `doSelect()` でレコードを検索する別の例-- 条件つき `Criteria`

    [php]
    $c = new Criteria();
    $c->add(CommentPeer::AUTHOR, 'Steve');
    $c->addJoin(CommentPeer::ARTICLE_ID, ArticlePeer::ID);
    $c->add(ArticlePeer::CONTENT, '%enjoy%', Criteria::LIKE);
    $c->addAscendingOrderByColumn(CommentPeer::CREATED_AT);
    $comments = CommentPeer::doSelect($c);

    // 上記のコードは次のようなSQLクエリになる
    SELECT blog_comment.ID, blog_comment.ARTICLE_ID, blog_comment.AUTHOR,
           blog_comment.CONTENT, blog_comment.CREATED_AT
    FROM   blog_comment, blog_article
    WHERE  blog_comment.AUTHOR = 'Steve'
           AND blog_article.CONTENT LIKE '%enjoy%'
           AND blog_comment.ARTICLE_ID = blog_article.ID
    ORDER BY blog_comment.CREATED_AT ASC

SQL はとても複雑なクエリを開発できるシンプルな言語なので、`Criteria` オブジェクトはどんな複雑なレベルの条件を処理できます。しかし、多くの開発者は条件をオブジェクト指向のロジックに翻訳する前に最初に SQL を考えるので、最初に `Criteria` を把握するのは難しいでしょう。これを理解する最良の方法は具体例とサンプルのアプリケーションから学ぶことです。たとえば、 symfony 公式サイトは多くの方法であなたを啓発する `Criteria` の開発例で満たされています。

`doSelect()` メソッドに加えて、すべての対のクラスは `doCount()` メソッドを持ちます。`doCount()` メソッドはパラメーターとして渡された基準を満たすレコードの数をそのままカウントして、カウント数を整数として返します。この場合、返すオブジェクトが存在しないので、ハイドレイティングは行われません。また `doCount()` メソッドは `doSelect()` よりも速いです。

対のクラスは `Criteria` を必須パラメーターとする `doDelete()`、`doInsert()` と `doUpdate()` メソッドも提供します。これらのメソッドによってデータベースに `DELETE`、`INSERT` と `UPDATE` クエリを発行できます。これらの Propel のメソッドの詳細に関しては生成モデルのピアクラスを確認してください。

最後に、最初に返されたオブジェクトが欲しい場合、`doSelect()` をすべて `doSelectOne()` の呼び出しに置き換えます。これは `Criteria` が1つの結果だけを返すことを知っているときにあてはまる場合で、利点はこのメソッドがオブジェクト配列ではなくオブジェクトを返すことです。

>**TIP**
>`doSelect()`クエリが多数の結果を返すとき、レスポンスのなかでその部分集合だけを表示したいことがあります。symfony は結果のページ分割を自動化する `sfPropelPager` と呼ばれるページャークラスを提供します。

### 生の SQL クエリを使う

ときには、オブジェクトを検索する必要はないが、データベースによって算出された総合結果だけが欲しいことがあります。たとえば、すべての記事作成の最新日時を取得するために、すべての記事を検索し、配列でループしても無意味です。結果だけを返すようにデータベースに求めるほうが望ましいです。なぜなら、これはオブジェクトのハイドレイティングをスキップするからです。

一方で、データベース抽象化の利点を失いたくないので、データベース管理のために PHP コマンドを直接呼び出したくないことがあります。これは ORM (Propel) を回避し、データベースの抽象化 (PDO) を回避しないことが必要であることを意味します。

PDO でデータベースにクエリを行うには次の作業を行う必要があります:

  1. データベースの接続を取得する。
  2. クエリの文字列をビルドする。
  3. それからステートメントを作る。
  4. ステートメントの実行から得られた結果セットをイテレートする。

何を言っているのかよくわからないのでしたら、おそらくリスト8-15のコードを見ればより明確になるでしょう。

リスト8-15 - PDO でカスタム SQL クエリ

    [php]
    $connection = Propel::getConnection();
    $query = 'SELECT MAX(?) AS max FROM ?';
    $statement->bindValue(1, ArticlePeer::CREATED_AT);
    $statement->bindValue(2, ArticlePeer::TABLE_NAME);  
    $statement = $connection->prepare($query);
    $statement->execute();
    $resultset = $statement->fetch(PDO::FETCH_OBJ);
    $max = $resultset->max;

Propel の SELECT 機能と同じように、PDO クエリを使い始めたときこれらは扱いにくいです。繰り返しますが、既存のアプリケーションとチュートリアルの例は正しい方法を示します。

>**CAUTION**
>このプロセスを回避しデータベースに直接アクセスする場合、Propel によって提供されたセキュリティと抽象化を失うリスクを負うことになります。Propel のやりかたは長いですが、パフォーマンス、ポータビリティ、アプリケーションのセキュリティを保証するよい習慣が強制されます。これは信用できないソース (たとえばインターネットのユーザー) からのパラメーターを収めるクエリにとりわけあてはまります。Propel は必要なすべてのエスケープを行い、データベースを安全にします。データベースに直接アクセスすることは SQL インジェクション攻撃のリスクが存在する状態にさらされることを意味します。

### 特別な日付カラムを使う

通常、テーブルに `created_at` と呼ばれるカラムがあるとき、レコードの作成日時のタイムスタンプを保存するためにこのカラムは使われます。同じことが `updated_at` カラムにもあてはまります。レコード自身が更新されるたびに現在の時間の値に更新されます。

よい知らせは symfony がこれらのカラムを認識し更新を処理することです。`created_at` カラムと `updated_at` カラムを手動で設定する必要はありません; リスト8-16で示されるように、これらは自動的に更新されます。同じことが `created_on` と `updated_on` カラムにもあてはまります。

リスト8-16 - `created_at` と `updated_at` カラムは自動的に処理される

    [php]
    $comment = new Comment();
    $comment->setAuthor('Steve');
    $comment->save();

    // 作成時点の日付を表示する
    echo $comment->getCreatedAt();
      => [date of the database INSERT operation]

加えて、日付カラムのゲッターは引数として日付フォーマットを受けとります:

    [php]
    echo $comment->getCreatedAt('Y-m-d');

>**SIDEBAR**
>**データレイヤーへのリファクタリング**
>
>symfony を開発しているとき、アクションのドメインロジックのコードを書き始めることがよくあります。しかしながらデータベースクエリとモデル操作のコードはコントローラーレイヤーに保存すべきではなく、データに関連するすべてのロジックはモデルレイヤーに移動させるべきです。アクションの複数の場所で同じリクエストを行う必要があるときは、関連コードをモデルに移動させることを考えてください。この作業を行うことでアクションのコードを短くて読みやすい状態に保つための助けになります。
>
>たとえば、ブログで (リクエストパラメーターとして渡される) 任意のタグに対してもっとも人気のある記事を検索するために必要なコードを想像してください。このコードはアクションではなくモデルのなかに存在します。実際、テンプレートのなかでこの記事の一覧を表示する必要がある場合、アクションは次のようなシンプルなものになります:
>
>     [php]
>     public function executeShowPopularArticlesForTag($request)
>     {
>       $tag = TagPeer::retrieveByName($request->getParameter('tag'));
>       $this->forward404Unless($tag);
>       $this->articles = $tag->getPopularArticles(10);
>     }
>
>アクションはリクエストパラメーターから `Tag` クラスのオブジェクトを作ります。それからデータベースにクエリを行うために必要なすべてのコードはこのクラスの `getPopularArticles()` メソッドに設置されます。これによってアクションは読みやすくなり、モデルのコードは別のアクションのなかで簡単に再利用できます。
>
>コードをより適切な場所に移動させることはリファクタリングの技術の1つです。頻繁にこの作業を行えば、コードは維持しやすくほかの開発者にわかりやすくなります。データレイヤーでリファクタリングを行うときのよい経験則はアクションのコードに含まれる PHP コードのほとんどが10行を越えないことです。

データベースの接続
------------------

データモデルは利用されるデータベースから独立していますが、間違いなくデータベースを使うことになります。リクエストをプロジェクトのデータベースに送信するために symfony に求められる最小限の情報は名前、クレデンシャル、とデータベースの種類です。これらの接続設定は `configure:database` タスクにデータソース名 (DSN - Data Source Name) を渡すことで設定可能です:

    $ php symfony configure:database "mysql:host=localhost;dbname=blog" root mYsEcret

接続設定は環境に依存します。アプリケーションの `prod`、`dev`、と `test` 環境もしくは `env` オプションを使って別の環境に対して異なる設定を定義できます:

    $ php symfony configure:database --env=dev "mysql:host=localhost;dbname=blog_dev" root mYsEcret 

この設定はアプリケーションごとにオーバーライドすることもできます。たとえば、フロントエンドとバックエンドのアプリケーションに対して異なるセキュリティポリシーを適用し、データベースを扱うために1つのデータベースのなかで異なる権限を持つ複数のデータベースユーザーを定義するために、このアプローチを利用できます:

    $ php symfony configure:database --app=frontend "mysql:host=localhost;dbname=blog" root mYsEcret 

環境ごとに複数の接続を定義できます。それぞれの接続は同じ名前でラベルづけされたスキーマを参照します。デフォルトで使われる接続名は `propel` でこれはリスト8-3の `propel` スキーマを参照します。`name` オプションによって別の接続を作成することができます:

    $ php symfony configure:database --name=main "mysql:host=localhost;dbname=example" root mYsEcret 

`config/` ディレクトリに設置される `databases.yml` ファイルのなかでこれらの接続設定を手動で入力することもできます。リスト8-17はファイルの例を示しリスト8-18は拡張記法による同じ例を示します。

リスト8-17 - データベース接続設定の省略記法

    [yml]
    all:
      propel:
        class:          sfPropelDatabase
        param:
          dsn:          mysql://login:passwd@localhost/blog

リスト8-18 - データベース接続設定のサンプル (`myproject/config/databases.yml`)

    [yml]
    prod:
      propel:
        param:
          hostspec:           mydataserver
          username:           myusername
          password:           xxxxxxxxxx

    all:
      propel:
        class:                sfPropelDatabase
        param:
          phptype:            mysql     # データベースベンダー
          hostspec:           localhost
          database:           blog
          username:           login
          password:           passwd
          port:               80
          encoding:           utf8      # テーブル作成のためのデフォルトの文字集合
          persistent:         true      # 永続的接続を使う

認められる `phptype` パラメーターの値は PDO によってサポートされるデータベースシステムの1つです:

  * `mysql`
  * `mssql`
  * `pgsql`
  * `sqlite`
  * `oracle`

`hostspec`、`database`、`username`、と `password` は通常はデータベース接続設定です。

アプリケーションごとに設定をオーバーライドするには、 `apps/frontend/config/databases.yml` のようなアプリケーション固有のファイルを編集する必要があります。

SQLiteデータベースを使う場合、`hostspec` パラメーターにデータベースファイルのパスを設定しなければなりません。たとえば、ブログのデータベースを `data/blog.db` に保存する場合、`databases.yml` ファイルはリスト8-19のようになります。

リスト8-19 - SQlite データベースの接続設定はファイルパスをホストとして使う

    [yml]
    all:
      propel:
        class:      sfPropelDatabase
        param:
          phptype:  sqlite
          database: %SF_DATA_DIR%/blog.db

モデルを拡張する
----------------

生成モデルのメソッドはすばらしいものですが、十分ではないことはよくあります。独自のビジネスロジックを実装すると同時に、新しいメソッドを追加するか既存のメソッドをオーバーライドすることで、ビジネスロジックを拡張する必要があります。

### 新しいメソッドを追加する

`lib/model/` ディレクトリのなかの空の生成モデルクラスに新しいメソッドを追加できます。現在のオブジェクトメソッドを呼び出すには `$this` を使い、現在のクラスのスタティックメソッドを呼び出すには `self::` を使います。カスタムクラスが `lib/model/om/` ディレクトリのなかに設置される `Base` クラスのメソッドを継承することを覚えておいてください。

たとえば、リスト8-20で示されるように、リスト8-3をもとに生成された `Article` オブジェクトに対して、`Article` クラスのオブジェクトを echo することでタイトルを表示できるように、`__toString()` マジックメソッドを追加できます。

リスト8-20 - モデルをカスタマイズする (`lib/model/Article.php`)

    [php]
    class Article extends BaseArticle
    {
      public function __toString()
      {
        return $this->getTitle();  // getTitle() は BaseArticle から継承される
      }
    }

対のクラスを拡張することもできます。たとえば、リスト8-21で示されるように、記事作成の日付順で並べられたすべての記事を検索するためにメソッドを追加します。

リスト8-21 - モデルをカスタマイズする (`lib/model/ArticlePeer.php`)

    [php]
    class ArticlePeer extends BaseArticlePeer
    {
      public static function getAllOrderedByDate()
      {
        $c = new Criteria();
        $c->addAscendingOrderByColumn(self::CREATED_AT);

        return self::doSelect($c);

      }
    }

リスト8-22で示されるように、新しいメソッドの使い方は生成メソッドと同じです。

リスト8-22 -カスタムモデルメソッドの使い方は生成メソッドと似ている

    [php]
    foreach (ArticlePeer::getAllOrderedByDate() as $article)
    {
      echo $article;      // __toString()マジックメソッドを呼び出す
    }

### 既存のメソッドをオーバーライドする

`Baseクラス` の生成メソッドがあなたの要件に合わない場合、これらのメソッドをカスタムクラスでオーバーライドすることもできます。同じメソッドのシグニチャ (すなわち、同じ数の引数) を使っていることを確認してください。

たとえば、`$article->getComments()` メソッドは`Comment`オブジェクトの配列を順不同で返します。最新コメントが一番最初になるように作成日時の順序でコメントを並べたい場合、リスト8-23で示されるように`getComments()`メソッドをオーバーライドします。オリジナルの `getComments()` メソッド (`lib/model/om/BaseArticle.php` で見つかる) の必須パラメーターは基準値と接続の値なので、あなたのメソッドが同じことを行わなければならないことに注意してください。

リスト8-23 - 既存のモデルメソッドをオーバーライドする (`lib/model/Article.php`)

    [php]
    public function getComments($criteria = null, $con = null)
    {
      if (is_null($criteria))
      {
        $criteria = new Criteria();
      }
      else
      {
        // PHP 5 ではオブジェクトは参照で渡されるので、オリジナルの修正を避けるには、clone しなければならない
        $criteria = clone $criteria;
      }
      $criteria->addDescendingOrderByColumn(CommentPeer::CREATED_AT);

      return parent::getComments($criteria, $con);
    }

カスタムメソッドは最終的に親の `Base` クラスの1つを呼び出します。これはよい習慣です。しかしながら、完全にそれを回避し、望む結果を返すことができます。

### モデルのビヘイビアを使う

一般的に複数のモデルを修正するものは再利用可能です。たとえば、モデルオブジェクトをソート可能にしてオブジェクトの保存が同時に起きることを防止する楽観的ロック(オプティミスティックロック)にすることは多くのクラスに追加できる一般的な拡張方法です。

symfony はこれらの拡張機能をビヘイビアにまとめます。ビヘイビア (behavior) とはモデルクラスに追加メソッドを提供する外部クラスです。モデルクラスはフックを持ち、ビヘイビアを拡張する方法を知っています。

モデルクラスのビヘイビアを有効にするには、`config/propel.ini` ファイルの設定の1つを修正しなければなりません:

    propel.builder.AddBehaviors = true     // デフォルト値は false

symfony にデフォルトで搭載されているビヘイビアは存在しませんが、それらはプラグインを通してインストールできます。いったんビヘイビアのプラグインがインストールされると、1行でビヘイビアにクラスを割り当てることができます。たとえば、アプリケーションに `sfPropelParanoidBehaviorPlugin` をインストールする場合、`Article.class.php` の最後の行に次のコードを追加すればこのビヘイビアを持つ `Article` クラスを拡張できます:

    [php]
    sfPropelBehavior::add('Article', array(
      'paranoid' => array('column' => 'deleted_at')
    ));

モデルをリビルドしたあとで、`sfPropelParanoidBehavior::disable()` でビヘイビアを一時的に無効にしないかぎり、削除された `Article` オブジェクトは ORM を使うクエリには見えないだけで、データベースに保存されたままになります。

代わりに、ビヘイビアのリストを`_behaviors` キーの下に追加することで `schema.yml` のなかで直接ビヘイビアを宣言することもできます (次のリスト8-34を参照)。

ビヘイビアを見つけるには symfony のプラグインのオフィシャル[リポジトリ](http://www.symfony-project.org/plugins/)を参照してください。それぞれのプラグインには独自のドキュメントとインストールガイドがあります。

スキーマの拡張構文
------------------

リスト8-3で示されるように、`schema.yml` ファイルを簡略化できます。しかしながらリレーショナルモデルが複雑であることがよくあります。ほとんどすべての事例に対処できるスキーマの拡張構文がある理由はそういうわけです。

### 属性

リスト8-24で示されるように、接続とテーブルは固有の属性を持つことができます。これらは `_attributes` キーの下で設定します。

リスト8-24 - 接続とテーブルの属性

    [yml]
    propel:
      _attributes:   { noXsd: false, defaultIdMethod: none, package: lib.model }
      blog_article:
        _attributes: { phpName: Article }

コード生成が行われる前にスキーマを検証したい場合を考えます。これを行うには、接続の `noXSD` 属性を無効にします。接続は `defaultIdMethod` 属性もサポートします。何も提供されなければ、ID を生成するデータベースのネイティブなメソッドが使われます。たとえば、MySQL では `autoincrement`、PostgreSQL では `sequences` です。ほかのとりうる値は `none` です。

`package` 属性は名前空間のようなものです; これは生成クラスが保存される場所のパスを決めます。デフォルト値は`lib/model/`ですが、サブパッケージのモデルを編成するために変更できます。たとえば、ビジネスのコアクラスとデータベースに保存される統計エンジンを定義するクラスを同じディレクトリのなかで混在させたくない場合、`lib.model.business` パッケージと `lib.model.stats` パッケージで2つのスキーマを定義します。

テーブルをマッピングする生成クラスの名前を設定するために使われる `phpName` テーブル属性はすでに見ました。

リスト8-25で示されるように、ローカライズされる内容を収めるテーブル (すなわち、国際化のために、関連するテーブルのなかに存在する、複数のバージョンの内容) も2つの追加属性をとります (詳細は13章を参照)。

リスト8-25 - 国際化テーブル用の属性

    [yml]
    propel:
      blog_article:
        _attributes: { isI18N: true, i18nTable: db_group_i18n }

>**SIDEBAR**
>**複数のスキーマを扱う**
>
>アプリケーションごとに複数のスキーマを用意できます。symfony は `config/` フォルダーの名前が `schema.yml` もしくは `schema.yml` で終わるすべてのファイルを考慮に入れます。アプリケーションが多くのテーブルを持つ場合、もしくはテーブルが同じ接続を共有しない場合、このアプローチがとても便利であることがわかります。
>
>次の2つのスキーマを考えてください:
>
>     [yml]
>     // config/business-schema.yml
>     propel:
>       blog_article:
>         _attributes: { phpName: Article }
>       id:
>       title: varchar(50)
>
>     // config/stats-schema.yml
>     propel:
>       stats_hit:
>         _attributes: { phpName: Hit }
>       id:
>       resource: varchar(100)
>       created_at:
>
>
>同じ接続を共有する両方のスキーマ (`propel`) と `Article` クラスと `Hit` クラスは同じ `lib/model/` ディレクトリのもとで生成されます。あたかもスキーマを1つだけ書いたようにすべてのものごとが行われます。
>
>異なる接続(たとえば、`databases.yml` で定義される `propel` と `propel_bis`) を使う異なるスキーマを持つことが可能で生成クラスをサブディレクトリに分類できます。
>
>     [yml]
>     // config/business-schema.yml
>     propel:
>       blog_article:
>         _attributes: { phpName: Article, package: lib.model.business }
>       id:
>       title: varchar(50)
>
>     // config/stats-schema.yml
>     propel_bis:
>       stats_hit:
>         _attributes: { phpName: Hit, package: lib.model.stat }
>       id:
>       resource: varchar(100)
>       created_at:
>
>
>多くのアプリケーションは複数のスキーマを使います。とりわけ、プラグインのなかにはアプリケーション独自のクラスに干渉しないようにプラグイン独自のスキーマとパッケージを持つものがあります(詳細は17章を参照)。

### カラムの詳細

基本構文は選択肢を2つ与えてくれます; (空の値を渡すことで) symfony に名前からカラムの特徴を推測させるか、1つの `type` キーワードで型を定義するかです。リスト8-26はこれらの選択肢のお手本を示しています。

リスト8-26 - 基本的なカラム属性

    [yml]
    propel:
      blog_article:
        id:    ~            # symfony に仕事を任せる
        title: varchar(50)  # あなた自身が型を指定する

しかしながら、もっと多くのカラム属性を定義できます。もし行う場合、リスト8-27で示されるように、カラムの設定を連想配列として定義する必要があります。

リスト8-27 - 複雑なカラム属性

    [yml]
    propel:
      blog_article:
        id:       { type: integer, required: true, primaryKey: true, autoIncrement: true }
        name:     { type: varchar(50), default: foobar, index: true }
        group_id: { type: integer, foreignTable: db_group, foreignReference: id, onDelete: cascade }

カラムのパラメーターは次のとおりです:

  * `type`: カラムの型。選択肢は `boolean`、`tinyint`、`smallint`、`integer`、`bigint`、`double`、`float`、`real`、`decimal`、`char`、`varchar(size)`、`longbarchar`、`date`、`time`、`timestamp`、`bu_date`、`bu_timestamp`、`blob`と`clob`です。
  * `required`: ブール値。カラムを必須にしたい場合これを `true` にセットします。
  * `size`: 型がサポートするフィールドのサイズもしくは長さ。
  * `scale`: decimal データ型のための小数位 (size も指定しなければなりません)。
  * `default`: デフォルト値。
  * `primaryKey`: ブール値。主キーに対してこれを `true` にセットします。
  * `autoIncrement`: ブール値。オートインクリメントされる値をとる `integer` 型のカラムに対してこれを `true` にセットします。
  * `sequence`: `autoIncrement` カラムに対してシーケンスを使うデータベース (たとえば PostgreSQL、Oracle) のためのシーケンス名。
  * `index`: ブール値。シンプルなインデックスが欲しい場合は `true` に、カラムでユニークインデックスを作りたい場合は `unique` にセットします。
  * `foreignTable`: 別のテーブルに外部キーを作るために使われる、テーブルの名前。
  * `foreignReference`: `foreingTable` 経由で外部キーが定義される場合の関連カラムの名前。
  * `onDelete`: 関連テーブルに存在するレコードが削除されたときにアクションを起動させるために指定します。`setnull` にセットしたとき、外部キーのカラムは `null` にセットされます。`cascade` にセットしたとき、レコードは削除されます。データベースエンジンが set ビヘイビアをサポートしない場合、ORM がエミュレートします。これは `foreignTable` と `foreingReference` を持つカラムだけが該当します。
  * `isCulture`: ブール値。ローカライズされた内容テーブルに存在する culture カラムに対してこれを `true` にセットしてください(13章を参照)。

### 外部キー

`foreignTable` と `foreignReference` カラム属性の代わりに、外部キーをテーブルの `_foreignKeys:` キーの下に追加できます。リスト8-28のスキーマは `blog_user` テーブルの `id` カラムにマッチする `user_id` カラムの上側に外部キーを作ります

リスト8-28 - 外部キーの代替構文

    [yml]
    propel:
      blog_article:
        id:      ~
        title:   varchar(50)
        user_id: { type: integer }
        _foreignKeys:
          -
            foreignTable: blog_user
            onDelete:     cascade
            references:
              - { local: user_id, foreign: id }

リスト8-29で示されるように、この代替構文は複数参照を持つ外部キーに名前をつけるために役立ちます。

リスト8-29 - 複数参照の外部キーに適用される外部キーの代替構文

        _foreignKeys:
          my_foreign_key:
            foreignTable:  db_user
            onDelete:      cascade
            references:
              - { local: user_id, foreign: id }
              - { local: post_id, foreign: id }

### インデックス

`index` カラム属性の代わりに、インデックスをテーブルの `indexes:` キーの下に追加できます。ユニークインデックスを定義したい場合、`_uniques:` ヘッダーを代わりに使わなければなりません。リスト8-30はインデックスの代替構文を示しています。

リスト8-30 - インデックスとユニークインデックスの代替構文

    [yml]
    propel:
      blog_article:
        id:               ~
        title:            varchar(50)
        created_at:
        _indexes:
          my_index:       [title(10), user_id]
        _uniques:
          my_other_index: [created_at]

代替構文は複数のカラムで構築されるインデックスに対してのみ役立ちます。

### 空のカラム

値を持たないカラムに遭遇するとき、symfony はいくつかのマジックを行い、それ自身の値を追加します。空のカラムに追加される詳細内容に関してリスト8-31をご覧ください。

リスト8-31 - カラムの名前から推定されるカラムの詳細内容

    // id という名前を持つ空のカラムは主キーと見なされる
    id:         { type: integer, required: true, primaryKey: true, autoIncrement: true }

    // XXX_id という名前を持つ空のカラムは外部キーと見なされる
    foobar_id:  { type: integer, foreignTable: db_foobar, foreignReference: id }

    // created_at、updated at、created_on と updated_on という名前を持つ空のカラムは
    // 日付と見なされ自動的に timestamp 型をとる
    created_at: { type: timestamp }
    updated_at: { type: timestamp }

外部キーに対して、symfony はカラムの名前の始めで同じ `phpName` を持つテーブルを探し、1つが見つかったら、このテーブルの名前を `foreignTable` としてとります。

### 国際化テーブル

symfony は関連テーブルのコンテンツの国際化をサポートをします。このことは、コンテンツのサブジェクトを国際化するとき、2つのテーブルに個別に保存されることを意味します: 1つは変わらないカラムでもう1つが国際化されたカラムです。

`schema.yml` ファイルにおいて、テーブルに `footbar_i18n` という名前をつけたときすべてが暗黙のうちに行われます。たとえば、国際化される内容のメカニズムが働くようにリスト8-32で示されるスキーマはカラムとテーブル属性を自動的に備えています。内部では、あたかもリスト8-33のように書かれたものとして symfony は理解します。国際化は13章で詳しく説明します。

リスト8-32 - 暗黙的な国際化メカニズム

    [yml]
    propel:
      db_group:
        id:          ~
        created_at:  ~

      db_group_i18n:
        name:        varchar(50)

リスト8-33 - 明示的な国際化メカニズム

    [yml]
    propel:
      db_group:
        _attributes: { isI18N: true, i18nTable: db_group_i18n }
        id:         ~
        created_at: ~

      db_group_i18n:
        id:       { type: integer, required: true, primaryKey: true,foreignTable: db_group, foreignReference: id, onDelete: cascade }
        culture:  { isCulture: true, type: varchar(7), required: true,primaryKey: true }
        name:     varchar(50)

### ビヘイビア

ビヘイビア (behavior) は Propel のクラスに新しい機能を追加するプラグインによって提供されるモデルを修正するライブラリです。17章でビヘイビアを詳しく説明します。ビヘイビアをそれぞれのテーブルに対して、パラメーターと一緒に、`_behaviors` キーの下に並べることでスキーマのなかでビヘイビアを直接定義できます。リスト8-34は `BlogArticle` クラスを `paranoid` ビヘイビアで拡張する例を示しています。

リスト8-34 - ビヘイビアの宣言

    [yml]
    propel:
      blog_article:
        title:          varchar(50)
        _behaviors:
          paranoid:     { column: deleted_at }

### schema.yml を越えて: schema.xml

実際のところ、`schema.yml` フォーマットは symfony 内部に存在します。`propel-command` を呼び出すとき、symfony は実際にこのファイルを`generated-schema.xml` ファイルに翻訳します。実際にはこのXMLファイルはモデルのタスクを実行するために Propel が必要とする種類のファイルです。

`schema.xml` ファイルは YAML と同等のものとして同じ情報を格納します。たとえば、リスト8-35で示されるように、リスト8-3は XML ファイルに変換されます。

リスト8-35 - リスト8-3に対応する `schema.yml` のサンプル

    [xml]
    <?xml version="1.0" encoding="UTF-8"?>
     <database name="propel" defaultIdMethod="native" noXsd="true" package="lib.model">
        <table name="blog_article" phpName="Article">
          <column name="id" type="integer" required="true" primaryKey="true"autoIncrement="true" />
          <column name="title" type="varchar" size="255" />
          <column name="content" type="longvarchar" />
          <column name="created_at" type="timestamp" />
        </table>
        <table name="blog_comment" phpName="Comment">
          <column name="id" type="integer" required="true" primaryKey="true"autoIncrement="true" />
          <column name="article_id" type="integer" />
          <foreign-key foreignTable="blog_article">
            <reference local="article_id" foreign="id"/>
          </foreign-key>
          <column name="author" type="varchar" size="255" />
          <column name="content" type="longvarchar" />
          <column name="created_at" type="timestamp" />
        </table>
     </database>

`schema.xml` フォーマットの書きかたは Propel プロジェクトの [公式サイト](http://propel.phpdb.org/docs/user_guide/chapters/appendices/AppendixB-SchemaReference.html) ドキュメントと "Getting Started" のセクションで見ることができます。

YAML フォーマットはスキーマの読み書きをシンプルに保つために設計されましたが、 トレードオフはもっとも複雑なスキーマを `schema.yml` ファイルで記述できないことです。一方で、XML フォーマットは、どんなに複雑なものであれ、データベースのベンダー固有の設定、テーブル、継承などを含めて、完全なスキーマ構文を記述できます。

実際には symfony は XML フォーマットで書かれたスキーマを理解します。あなたのスキーマが YAML の構文で記述するには複雑すぎる場合、既存の XML スキーマがある場合、もしくはすでに Propel の XML フォーマットに慣れ親しんでいる場合、symfony の YAML 構文に切り替える必要はありません。`schema.yml` をプロジェクトの `config/` ディレクトリに設置し、モデルをビルドします。簡単でしょ。

>**SIDEBAR**
>**symfony における Propel**
>
>この章で説明されたすべての内容は symfony 固有のものではなく、むしろ Propel のものです。Propel は symfony で優先されるオブジェクト/リレーショナル抽象化レイヤーですが、代わりのものを選ぶことができます。しかしながら、次の理由から、 symfony は Propel でよりシームレスに動作します:
>
>すべてのオブジェクトデータモデルクラスと `Criteria` クラスはオートロードクラスです。これらを使うと同時に、symfony は正しいファイルをインクルードし、ファイルにインクルードステートメントを手動で追加する必要はありません。symfony において、Propel を起動したり、初期化する必要もありません。オブジェクトが Propel を利用するとき、ライブラリは自分自身で初期化を行います。symfony ヘルパーはハイレベルなタスク (たとえばページ分割もしくはフィルタリング) を実現するために Propel オブジェクトをパラメーターとして使います。Propel オブジェクトはアプリケーションに対してラピッドプロトタイピングとバックエンドの生成を可能にします (14章で詳細な説明をします)。`schema.yml` ファイルを通してスキーマを速く書けます。
>
>Propel がデータベースに対して独立していることと同様に、symfony も Propel に対して独立しています。

同じモデルを2回作らない
-----------------------

ORM を使う場合のトレードオフはデータ構造を2回定義しなければならないことです: 1回目はデータベースに対して、2回目はオブジェクトモデルに対してです。幸いにして、symfony は一方をもとにもう一方を生成するコマンドラインツールを提供するので、重複作業を回避できます。

### 既存のスキーマをもとにSQLのデータベース構造をビルドする

`schema.yml` ファイルを書くことでアプリケーションを始める場合、symfony は YAML データモデルから直接テーブルを作成する SQL クエリを生成できます。クエリを使うには、プロジェクトのルートに移動して次のコマンドを入力します:

    $ php symfony propel:build-sql

`myproject/data/sql/` ディレクトリのなかで `lib.model.schema.sql` ファイルが作られます。SQL の生成コードが `propel.ini` ファイルの `phptype` パラメーターで定義されるデータベースシステムに対して最適化されることを覚えておいてください。

テーブルを直接ビルドするために `schema.sql` ファイルを利用できます。たとえば、MySQL では、次のコマンドを入力します:

    $ mysqladmin -u root -p create blog
    $ mysql -u root -p blog < data/sql/lib.model.schema.sql

生成される SQL もほかの環境のデータベースのリビルド、もしくはほかの DBMS に変更するために役立ちます。接続設定が `propel.ini` で適切に定義される場合、これを自動的に行う `propel:insert-sql` タスクを使うこともできます。

>**TIP**
>コマンドラインはテキストファイルをもとにデータをデータベースに投入するタスクも提供します。`propel:data-load` タスクと YAML フィクスチャファイルの詳細な情報は16章をご覧ください。

### 既存のデータベースから YAML データモデルを生成する

イントロスペクション (introspection) のおかげで、既存のデータベースから `schema.yml` ファイルを生成するために symfony は Propel を利用できます。これはリバースエンジニアリングを行うとき、もしくはオブジェクトモデルよりもデータベースにとり組みたい場合に役立ちます。

これを行うために、プロジェクトの `databases.yml` ファイルが正しいデータベースを指し示しすべての接続設定を収めていることを確認する必要があります。それから `propel:build-schema` コマンドを呼び出します:

    $ php symfony propel:build-schema

データベース構造から生成された新品の `schema.yml` ファイルは `config/` ディレクトリのなかで生成されます。このスキーマをもとにモデルをビルドできます。

スキーマ生成コマンドはとても強力でデータベースに依存する多くの情報をスキーマに追加できます。YAML フォーマットはこの種のベンダー情報を扱うことができないので、この情報を利用するには XML フォーマットを生成する必要があります。`build-schema` タスクに `xml` の引数を追加することでこれを簡単に行うことができます:

    $ php symfony propel:build-schema --xml

`schema.yml` ファイルを生成する代わりに、これは、Propel と十分に互換性を持ち、すべてのベンダー情報を収める `schema.xml` ファイルを作ります。しかし、XML の生成スキーマはとても冗長で読むのがむずかしいことを念頭に置いてください。

>**SIDEBAR**
>propel.ini の設定
>
>`propel:build-sql` と `propel:build-schema` タスクは `databases.yml` ファイルで定義される接続設定を使いません。むしろ、`propel.ini` という名前の別のファイルの接続設定を使います。`propel.ini` はプロジェクトの `config/` ディレクトリに保存されます:
>
>
>      propel.database.createUrl = mysql://login:passwd@localhost
>      propel.database.url       = mysql://login:passwd@localhost/blog
>
>
>このファイルは生成モデルクラスを symfony と互換性のあるものにする Propel ジェネレーターを設定するために使われるほかの設定を収めます。ごく一部を除いて、多くの設定は内部に関するもので、ユーザーには面白くないものです:
>
>
>      // 基底クラスは symfony でオートロードされる
>      // 代わりに include_once ステートメントを使うためにこれをtrueにセットする
>      // (パフォーマンスに対してわずかながら負の影響がある)
>      propel.builder.addIncludes = false
>
>      // 生成クラスはデフォルトでコメントされない
>      // コメントを基底クラスに追加するためにこれをtrueにセットする
>      // (パフォーマンスに小さな負の影響がある)
>      propel.builder.addComments = false
>
>      // ビヘイビアはデフォルトで扱われない
>      // これらを扱うことができるようにするには次の項目を true にセットする
>      propel.builder.AddBehaviors = false
>
>
>`propel.ini` 設定ファイルの修正後に、変更が反映されるようにモデルをリビルドすることを忘れないでください。

まとめ
------

symfony は Propel をオブジェクトリレーショナルマッピング (ORM - Object-Relational Mapping) として、PDO (PHP Data Objects) をデータベース抽象化レイヤー (database abstraction layer) として利用します。これはオブジェクトモデルクラスを生成する前に、最初に YAML フォーマットでデータベースのリレーショナルスキーマを記述しなければならないことを意味します。それから、実行時において、オブジェクトのメソッドとレコードもしくはレコードセットの情報を検索するためにピアクラスを使います。接続設定は複数の接続をサポートする `databases.yml` ファイルで定義されます。そして、コマンドラインには重複して構造を定義しないようにする特別なタスクが含まれます。

モデルレイヤー (model layer) は symfony フレームワークのなかでもっとも複雑です。複雑である理由の1つはデータ操作が込み入った問題であるからです。関連するセキュリティ問題はWebサイトにとって重大で無視できません。ほかの理由は symfony が中規模から大規模のアプリケーションにもっとも適しているからです。このようなアプリケーションにおいて、symfony のモデルによって提供される自動化は本当に時間を節約するので、内部構造を学ぶ価値はあります。

ですので、モデルオブジェクトとメソッドを十分に理解するにはこれらをテストすることに時間を費やすことをためらわないでください。大きな報酬としてアプリケーションの堅牢性とスケーラビリティが得られます。
