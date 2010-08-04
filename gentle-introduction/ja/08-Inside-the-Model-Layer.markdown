第8章 - モデルレイヤーの内側 (Doctrine)
============================

これまでのところ、ページを作りリクエストとレスポンスを処理することをおもに検討してきました。しかしながら Web アプリケーションのビジネスロジックの多くはデータモデルに依存しています。symfony のデフォルトモデルのコンポーネントはオブジェクト/リレーショナルマッピング (ORM - Object-Relational Mapping) のレイヤーに基づいています。symfonyは2つの最も人気があるORMをバンドルしています: それは[Propel](http://www.propelorm.org/) と [Doctrine](http://www.doctrine-project.org/)です。symfony のアプリケーションでは、アプリケーションの開発者はオブジェクトを通してデータベースに保存されたデータにアクセスし、オブジェクトを通してこれを修正します。データベースに直接とり組むことはありません。このことによって高い抽象性と移植性が維持されます。

この章では、オブジェクトのデータモデルを作成する方法と、Doctrine のデータにアクセスして修正する方法を説明します。これは Doctrineが symfony に統合されていることも実証します。

>**TIP**
>Doctrine の代わりに Propel を使いたい場合は Appendix Aを代わりに読んでください。そこに Propel での同じ情報が含まれています。

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

symfony は Propel と Doctrine を ORM として利用し、これらはデータベースの抽象化のために PDO (PHP Data Objects) を利用します。これら2つのサードパーティのコンポーネントは、両方とも Propel と Doctrine の開発チームによって開発され、symfony にシームレスに統合されているので、これらをフレームワークの一部としてみなすことができます。この章で説明しますが、これらの構文と規約はできるかぎり symfony のものとは異ならないように採用されました。

>**NOTE**
>symfony のプロジェクトにおいて、すべてのアプリケーションは同じモデルを共有します。これがプロジェクトレベルの肝心な点: 共通のビジネスルールに依存するアプリケーションを再編することです。モデルがアプリケーションから独立しており、モデルのファイルがプロジェクトのルートの `lib/model/` ディレクトリに保存される理由です。

symfony のデータベーススキーマ
------------------------------

symfony が使うデータオブジェクトモデルを作るために、データベースが持つリレーショナルモデルはどんなものでもオブジェクトデータモデルに翻訳する必要があります。ORM はマッピングを行うためにリレーショナルモデルの記述が必要です。これを記述するものはスキーマ (schema) と呼ばれます。スキーマにおいて、開発者はテーブル、それらのリレーションシップ、とカラムの特徴を定義します。

symfony のスキーマ構文は YAML フォーマットを利用します。`schema.yml` ファイルは `myproject/config/doctrine` ディレクトリ内部に設置しなければなりません。

### スキーマの例

データベースの構造をスキーマにどのように変換しますか？具体例は理解するための最良の方法です。2つのテーブル: `blog_article` と `blog_comment` を持つブログのデータベースを想像してください。テーブルの構造は図8-1で示されています。

図8-1 - ブログのデータベースのテーブル構造

![ブログのデータベースのテーブル構造](http://www.symfony-project.org/images/book/1_4/F0801.png "ブログのデータベースのテーブル構造")

関連する `schema.yml` ファイルはリスト8-3のようになります。

リスト8-3 - `schema.yml` のサンプル

    [yml]
    Article:
      actAs: [Timestampable]
      tableName: blog_article
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        title:   string(255)
        content: clob
    
    Comment:
      actAs: [Timestampable]
      tableName: blog_comment
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        article_id: integer
        author: string(255)
        content: clob
      relations:
        Article:
          onDelete: CASCADE
          foreignAlias: Comments


データベース自身 (`blog`) の名前が `schema.yml` に登場しないことに注目してください。代わりに、データベースの内容は接続名 (この例では `doctrine`) の下に記述されます。これは実際の接続設定はアプリケーションが稼働している環境に依存する可能性があるからです。たとえば、開発環境においてアプリケーションを稼働させるとき、開発データベース (たとえば `blog_dev`) にアクセスすることになりますが、運用のデータベースも同じスキーマを使います。接続設定は `databases.yml` ファイルのなかで指定されます。このファイルはこの章のあとのほうの"データベースの接続"のセクションで説明します。スキーマは、データベースの抽象化を保つために、詳細な接続情報の設定を収めず、接続名だけを収めます。

### スキーマの基本構文

`schema.yml` ファイルにおいて、最初のキーはモデル名を表します。複数のモデルを指定することができます。それぞれのモデルはカラムのセットを持ちます。YAML 構文に従い、キーはコロン (:) で終わり、構造はインデント (1つか複数のスペース、ただしタブはなし) を通して示されます。

モデルは `tableName` (データベースのテーブルの名前) を含めて、特別な属性を持つことができます。`tableName` がモデルに記載されていない場合、Doctrine はそのモデルに基づきアンダースコアー区切りの名前で作ります。

>**TIP**
>アンダースコアーの規約によれば単語と単語の間にアンダースコアを追加し、内部の単語の全て小文字にします。`Article` と `Comment` がアンダースコアー化されると `article` と `comment` になります.

モデルはカラムを格納します。カラムの値は2つの異なる方法で定義できます:

  * 1つの属性だけを定義する場合は、カラムの種類を指定します。symfony が理解できるカラムの種類は `boolean`、`integer`、`floot`、`date`、`string(size)`、 `clob` (たとえばMySQLだと`text`に変換されます)、などがあります。.

  * その他の属性（標準での値、必須かどうか、等々）を定義する必要がある場合は、`key: value`の組み合わせで書かなかればなりません。この拡張されたスキーマの構文については後ほど説明します。

モデルは外部キーとインデックスを含むこともできます。詳しく学ぶにはこの章の後のほうにある"スキーマの拡張構文"のセクションを参照してください。

モデルクラス
------------

スキーマは ORM レイヤーのモデルクラスをビルドするために使われます。作業時間を節約するために、これらのクラスは `doctrine:build-model` という名前のコマンドラインタスクによって生成されます。

    $ php symfony doctrine:build-model

>**TIP**
>モデルをビルドしたあとで、symfony が新しく生成されたモデルを見つけられるように、`php symfony cc` で symfony の内部キャッシュをクリアすることをお忘れなく。

このコマンドを入力することでプロジェクトの `lib/model/doctrine/base/` ディレクトリでスキーマの解析と基底データモデルクラスの生成が実行されます:

  * `BaseArticle.php`
  * `BaseComment.php`

さらに、実際のデータモデルクラスは `lib/model/doctrine` のなかに作られます:

  * `Article.php`
  * `ArticleTable.php`
  * `Comment.php`
  * `CommentTable.php`

2つのモデルだけを定義したので、6つのファイルで終わります。間違ったことは何もありませんが、いくつかの説明をする必要があります。

### 基底とカスタムクラス

2つのバージョンのデータオブジェクトを2つの異なるディレクトリに保存するのはなぜでしょうか？

おそらくモデルのオブジェクトにカスタムメソッドとプロパティを追加することが必要になります (リスト8-1の `getName()` メソッドを考えてください)。しかしプロジェクトの開発では、テーブルもしくはカラムも追加することになります。`schema.yml` ファイルを変更するたびに、`doctrine:build-model` を新たに呼び出してオブジェクトモデルクラスを再生成する必要があります。カスタムメソッドが実際に生成されたクラスのなかに書かれているとしたら、それらはそれぞれが生成されたあとで削除されます。

`lib/model/doctrine/base` ディレクトリに保存される `Base` クラスはスキーマから直接生成されたものです。これらを修正すべきではありません。すべての新しいモデルのビルドによってこれらのファイルが完全に削除されるからです。

一方で、`lib/model/doctrine` ディレクトリのなかに保存される、カスタムオブジェクトクラスは実際には `Base` クラスを継承します。既存のモデルで `doctrine:build-model` タスクが呼び出されるとき、これらのクラスは修正されません。ですのでここがカスタムメソッドを追加できる場所です。

リスト8-4は `doctrine:build-model` タスクを最初に呼び出したときに作成されるカスタムモデルクラスの例を示しています。

リスト8-4 - モデルクラスのファイルのサンプル (`lib/model/doctrine/Article.php`)

    [php]
    class Article extends BaseArticle
    {
    }

これは `BaseArticle` クラスのすべてのメソッドを継承しますが、スキーマの修正からは影響を受けません。

基底クラスを拡張するカスタムクラスのメカニズムによって、データベースの最終的なリレーショナルモデルを知らなくても、コードを書き始めることができます。関連ファイルの構造によってモデルはカスタマイズ可能で発展性のあるものになります。

### オブジェクトクラスとテーブルクラス

`Article` と `Comment` はデータベースのレコードを表すオブジェクトクラスです。これらはレコードのカラムと関連レコードにアクセスできます。リスト8-5で示される例のように、このことは `Article` オブジェクトのメソッドを呼び出すことで、記事のタイトルを知ることができることを意味します。

リスト8-5 - レコードカラムのゲッターはオブジェクトクラスのなかで使える

    [php]
    $article = new Article();
    // ...
    $title = $article->getTitle();

`ArticleTable` と `CommentTable` は対のクラスです; すなわち、テーブル上で実行するパブリックメソッドを含むクラスです。これらはテーブルからレコードを検索する方法を提供します。リスト8-6で示されるように、通常これらのメソッドは関連オブジェクトクラスのオブジェクトもしくはオブジェクトのコレクションを返します。

リスト8-6 - レコードを検索するパブリックメソッドは対となるテーブルクラスのなかで使える

    [php]
    // $articles は Article クラスのオブジェクトの配列
    $article = Doctrine_Core::getTable('Article')->find(123);

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

symfony はモデルをビルドするとき、`schema.yml`で 定義されるそれぞれのテーブルごとに1つの基底オブジェクトクラスを作ります。それぞれのクラスはカラム定義をもとにデフォルトのアクセサー、ミューテーターを備えています: リスト8-7で示されるように、`new`、`getXXX()`、`setXXX()` メソッドはオブジェクトを作りオブジェクトのプロパティにアクセスする作業を助けます。

リスト8-7 - 生成オブジェクトクラスのメソッド

    [php]
    $article = new Article();
    $article->setTitle('初めての記事');
    $article->setContent("これは初めての記事です。\n 皆様が楽しんでくださることを祈っています！");

    $title   = $article->getTitle();
    $content = $article->getContent();

>**NOTE**
>生成オブジェクトクラスは `Article` という名前になりデータベースのテーブル名である `blog_article` という名前で保存されません。スキーマで `tableName` を定義していなければ `article` という名前で定義されていると見なされます。アクセサーとミューテーターはキャメルケース形式のカラム名を使うので、`getTitle()` メソッドは `title` カラムの値を検索します。

リスト8-8で示されるように一度に複数のフィールドを設定するには、それぞれのオブジェクトクラスで利用できる、 `fromArray()` メソッドを使います。

リスト8-8 - `fromArray()` メソッドは複数のセッターである

    [php]
    $article->fromArray(array(
      'title'   => '初めての記事',
      'content' => 'これは初めての記事です。\n 皆様が楽しんでくださることを祈っています！',
    ));

### 関連レコードを検索する

`blog_comment` テーブルの `article_id` カラムは明示的に外部キーを`blog_article`テーブルに定義します。それぞれのコメントは1つの記事に関連し、1つの記事は多くのコメントを持つことができます。生成クラスは次のようにこのリレーションシップをオブジェクト指向の方法に翻訳する5つのメソッドを収めます:

  * `$comment->getArticle()`: 関連する `Article` オブジェクトを取得する
  * `$comment->getArticleId()`: 関連する `Article` オブジェクトの ID を取得する
  * `$comment->setArticle($article)`: 関連する `Article` オブジェクトを定義する
  * `$comment->setArticleId($id)`: ID から関連する `Article` オブジェクトを定義する
  * `$article->getComments()`: 関連する `Comment` オブジェクトを取得する

`getArticleId()` と `setArticleId()` メソッドは開発者が `article_id` カラムを通常のカラムとみなしてリレーションシップを手動で設定できることを示します。しかしこれらはあまり面白いものではありません。オブジェクト指向のアプローチの利点はほかの3つのメソッドでおおいにあきらかになります。リスト8-9は生成されたセッターの使い方を示します。

リスト8-9 - 外部キーは特別なセッターに翻訳される

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

### レコードを主キーで検索する

特定のレコードの主キーを知っている場合、関連オブジェクトを検索するにはピアクラスの `find()` クラスメソッドを使います。

    [php]
    $article = Doctrine_Core::getTable('Article')->find(7);

`schema.yml` ファイルは `id` フィールドを `blog_article` の主キーとして定義します。このステートメントは実際には`id`が7である記事を返します。主キーを使いましたので、あなたは1つのレコードだけが返されることを知っています; `$article` 変数は `Article` クラスのオブジェクトを収めます。

場合によっては、主キーが複数のカラムで構成されます。このような場合には、`retrieveByPK()` メソッドは複数のパラメーターをとり、主キーのカラムごとのパラメーターは1つです。

### Doctrine_Queryでレコードを検索する

複数のレコードを検索したいとき、検索したいオブジェクトに対応する対のテーブルクラスの `createQuery()` メソッドを呼び出す必要があります。たとえば、`Article` クラスのオブジェクトを検索するには、`Doctrine_Core::getTable('Article')->createQuery()->execute()` を呼び出します。

`execute()` メソッドの最初のパラメーターはパラメーターの配列です。これはクエリー内にあるプレースホルダーを置き換えるために使用する値の配列です。

空の `Doctrine_Query` はすべてのクラスのオブジェクトを返します。たとえば、リスト8-12で示されるコードは全ての記事を検索します。

リスト8-12 - `createQuery()`をパラメーターを空のまま呼び出し `Doctrine_Query` を使うことでレコードを検索する

    [php]
    $q = Doctrine_Core::getTable('Article')->createQuery();
    $articles = $q->execute();

    // 上記のコードは次のSQLクエリになります
    SELECT b.id AS b__id, b.title AS b__title, b.content AS b__content, b.created_at AS b__created_at, b.updated_at AS b__updated_at FROM blog_article b

>**SIDEBAR**
>ハイドレイティング (hydrating)
>
>`->execute()` 呼び出しは実際にはシンプルなSQLクエリよりはるかに強力です。最初に、SQL は選択した DBMS のために最適化されます、2番目に、 `Doctrine_Query` に渡されるどの値もSQLコードに統合される前にエスケープされ、SQL インジェクションのリスクが予防されます。3番目に、メソッドは、結果セットではなく、オブジェクト配列を返します。ORM はデータベースの結果セットをもとにオブジェクトを自動的に作成し投入します。このプロセスはハイドレイティング (hydrating) と呼ばれます。

より複雑なオブジェクトを選ぶには、`WHERE`、`ORDER BY`、`GROUP BY`、およびほかのSQLステートメントと同等のものが必要です。`Doctrine_Query` オブジェクトはこれらすべての条件のためのメソッドとパラメーターを持ちます。たとえば、リスト8-13のように、Steve によって書かれ、日付順に並べ替えられた、すべてのコメントを取得するには、`Doctrine_Query` をビルドします。

リスト8-13 - `createQuery()` を条件をつけて呼び出し `Doctrine_Query` を使うことでレコードを検索する

    [php]
    $q = Doctrine_Core::getTable('Comment')
      ->createQuery('c')
      ->where('c.author = ?', 'Steve')
      ->orderBy('c.created_at ASC');
    $comments = $q->execute();

    // 上記のコードは次のような SQL クエリになる
    SELECT b.id AS b__id, b.article_id AS b__article_id, b.author AS b__author, b.content AS b__content, b.created_at AS b__created_at, b.updated_at AS b__updated_at FROM blog_comment b WHERE (b.author = ?) ORDER BY b.created_at ASC

テーブル8-1は SQL と `Doctrine_Query` オブジェクトの構文を比較します。

テーブル8-1 - SQL と `Doctrine_Query` オブジェクトの構文

SQL                                                          | Criteria
------------------------------------------------------------ | -----------------------------------------------
`WHERE column = value`                                       | `->where('acolumn = ?', 'value')`
**Other SQL Keywords**                                       |
`ORDER BY column ASC`                                        | `->orderBy('acolumn ASC')`
`ORDER BY column DESC`                                       | `->addOrderBy('acolumn DESC')`
`LIMIT limit`                                                | `->limit(limit)`
`OFFSET offset`                                              | `->offset(offset) `
`FROM table1 LEFT JOIN table2 ON table1.col1 = table2.col2`  | `->leftJoin('a.Model2 m')`
`FROM table1 INNER JOIN table2 ON table1.col1 = table2.col2` | `->innerJoin('a.Model2 m')`

リスト8-14は複数の条件つきの `Doctrine_Query` の別の例を示します。日付順に並べ替えられた "enjoy" の単語を含む記事の Steve によるすべてのコメントを検索する。

リスト8-14 - `Doctrine_Query()` を条件をつけて呼び出しレコードを検索する例

    [php]
    $q = Doctrine_Core::getTable('Comment')
      ->createQuery('c')
      ->where('c.author = ?', 'Steve')
      ->leftJoin('c.Article a')
      ->andWhere('a.content LIKE ?', '%enjoy%')
      ->orderBy('c.created_at ASC');
    $comments = $q->execute();

    // 上記のコードは次のようなSQLクエリになる
    SELECT b.id AS b__id, b.article_id AS b__article_id, b.author AS b__author, b.content AS b__content, b.created_at AS b__created_at, b.updated_at AS b__updated_at, b2.id AS b2__id, b2.title AS b2__title, b2.content AS b2__content, b2.created_at AS b2__created_at, b2.updated_at AS b2__updated_at FROM blog_comment b LEFT JOIN blog_article b2 ON b.article_id = b2.id WHERE (b.author = ? AND b2.content LIKE ?) ORDER BY b.created_at ASC
    $c = new Criteria();
    $c->add(CommentPeer::AUTHOR, 'Steve');
    $c->addJoin(CommentPeer::ARTICLE_ID, ArticlePeer::ID);
    $c->add(ArticlePeer::CONTENT, '%enjoy%', Criteria::LIKE);
    $c->addAscendingOrderByColumn(CommentPeer::CREATED_AT);
    $comments = CommentPeer::doSelect($c);

SQL はとても複雑なクエリを開発できるシンプルな言語なので、`Doctrine_Query` オブジェクトはどんな複雑なレベルの条件を処理できます。しかし、多くの開発者は条件をオブジェクト指向のロジックに翻訳する前に最初に SQL を考えるので、最初に `Doctrine_Query` を把握するのは難しいでしょう。これを理解する最良の方法は具体例とサンプルのアプリケーションから学ぶことです。たとえば、 symfony 公式サイトは多くの方法であなたを啓発する `Doctrine_Query` の開発例で満たされています。

すべての `Doctrine_Query` は `count()` メソッドを持っています。これは簡単にクエリの結果取得したレコード数を数値で返してくれるので数えることができます。クエリの結果としてオブジェクトが返されない場合は、ハイドレイティングは行われないので `count()` メソッドは `execute()` メソッドより高速です。

対となるテーブルクラスには `findAll()`、 `findBy*()` そして `findOneBy*()` が用意されています。これらのメソッドは `Doctrine_Query` インスタンスを生成するためのショートカットであり、指定した内容を処理し結果を返します。

最後に、最初に返されたオブジェクトが欲しい場合、`execute()` をすべて `fetchOne()` の呼び出しに置き換えます。これは `Doctrine_Query` が1つの結果だけを返すことを知っているときにあてはまる場合で、利点はこのメソッドがオブジェクト配列ではなくオブジェクトを返すことです。

>**TIP**
>`execute()`クエリが多数の結果を返すとき、レスポンスのなかでその部分集合だけを表示したいことがあります。symfony は結果のページ分割を自動化する `sfDoctrinePager` と呼ばれるページャークラスを提供します。

### 生の SQL クエリを使う

ときには、オブジェクトを検索する必要はないが、データベースによって算出された総合結果だけが欲しいことがあります。たとえば、すべての記事作成の最新日時を取得するために、すべての記事を検索し、配列でループしても無意味です。結果だけを返すようにデータベースに求めるほうが望ましいです。なぜなら、これはオブジェクトのハイドレイティングをスキップするからです。

一方で、データベース抽象化の利点を失いたくないので、データベース管理のために PHP コマンドを直接呼び出したくないことがあります。これは ORM (Doctrine) を回避し、データベースの抽象化 (PDO) を回避しないことが必要であることを意味します。

PDO でデータベースにクエリを行うには次の作業を行う必要があります:

  1. データベースの接続を取得する。
  2. クエリの文字列をビルドする。
  3. それからステートメントを作る。
  4. ステートメントの実行から得られた結果セットをイテレートする。

何を言っているのかよくわからないのでしたら、おそらくリスト8-15のコードを見ればより明確になるでしょう。

リスト8-15 - PDO でカスタム SQL クエリ

    [php]
    $connection = Doctrine_Manager::connection();
    $query = 'SELECT MAX(created_at) AS max FROM blog_article';
    $statement = $connection->execute($query);
    $statement->execute();
    $resultset = $statement->fetch(PDO::FETCH_OBJ);
    $max = $resultset->max;

Doctrine の SELECT 機能と同じように、PDO クエリを使い始めたときこれらは扱いにくいです。繰り返しますが、既存のアプリケーションとチュートリアルの例は正しい方法を示します。

>**CAUTION**
>このプロセスを回避しデータベースに直接アクセスする場合、Propel によって提供されたセキュリティと抽象化を失うリスクを負うことになります。Doctrine のやりかたは長いですが、パフォーマンス、ポータビリティ、アプリケーションのセキュリティを保証するよい習慣が強制されます。これは信用できないソース (たとえばインターネットのユーザー) からのパラメーターを収めるクエリにとりわけあてはまります。Propel は必要なすべてのエスケープを行い、データベースを安全にします。データベースに直接アクセスすることは SQL インジェクション攻撃のリスクが存在する状態にさらされることを意味します。

### 特別な日付カラムを使う

通常、テーブルに `created_at` と呼ばれるカラムがあるとき、レコードの作成日時のタイムスタンプを保存するためにこのカラムは使われます。同じことが `updated_at` カラムにもあてはまります。レコード自身が更新されるたびに現在の時間の値に更新されます。

よい知らせは symfony がこれらのカラムを認識し更新を処理することです。`created_at` カラムと `updated_at` カラムを手動で設定する必要はありません; リスト8-16で示されるように、これらは自動的に更新されます。同じことが `created_on` と `updated_on` カラムにもあてはまります。
便利なことに Doctrine には `Timestampable` ビヘイビアが用意されています。このビヘイビアはあなたの代わりに更新日を変更してくれます。そのため手動で `created_at` と `updated_at` のカラムをセットする必要はありません。リスト 8-16 のように自動で行ってくれます。

リスト8-16 - `created_at` と `updated_at` カラムは自動的に処理される

    [php]
    $comment = new Comment();
    $comment->setAuthor('Steve');
    $comment->save();

    // 作成時点の日付を表示する
    echo $comment->getCreatedAt();
      => [date of the database INSERT operation]

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
>       $tag = Doctrine_Core::getTable('Tag')->findOneByName($request->getParameter('tag'));
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

環境ごとに複数の接続を定義できます。それぞれの接続は同じ名前でラベルづけされたスキーマを参照します。デフォルトで使われる接続名は `doctrine` でこれはリスト8-3の `propel` スキーマを参照します。`name` オプションによって別の接続を作成することができます:

    $ php symfony configure:database --name=main "mysql:host=localhost;dbname=example" root mYsEcret 

`config/` ディレクトリに設置される `databases.yml` ファイルのなかでこれらの接続設定を手動で入力することもできます。リスト8-17はファイルの例を示しリスト8-18は拡張記法による同じ例を示します。

リスト8-17 - データベース接続設定の省略記法

    [yml]
    all:
      doctrine:
        class:          sfDoctrineDatabase
        param:
          dsn:          mysql://login:passwd@localhost/blog

リスト8-18 - データベース接続設定のサンプル (`myproject/config/databases.yml`)

    [yml]
    prod:
      doctrine:
        param:
          dsn:        mysql:dbname=blog;host=localhost
          username:   login
          password:   passwd
          attributes:
            quote_identifier: false
            use_native_enum: false
            validate: all
            idxname_format: %s_idx
            seqname_format: %s_seq
            tblname_format: %s

アプリケーションごとに設定をオーバーライドするには、 `apps/frontend/config/databases.yml` のようなアプリケーション固有のファイルを編集する必要があります。

SQLiteデータベースを使う場合、`dsn` パラメーターにデータベースファイルのパスを設定しなければなりません。たとえば、ブログのデータベースを `data/blog.db` に保存する場合、`databases.yml` ファイルはリスト8-19のようになります。

リスト8-19 - SQlite データベースの接続設定はファイルパスをホストとして使う

    [yml]
    all:
      doctrine:
        class:      sfDoctrineDatabase
        param:
          dsn:      sqlite:///%SF_DATA_DIR%/blog.db

モデルを拡張する
----------------

生成モデルのメソッドはすばらしいものですが、十分ではないことはよくあります。独自のビジネスロジックを実装すると同時に、新しいメソッドを追加するか既存のメソッドをオーバーライドすることで、ビジネスロジックを拡張する必要があります。

### 新しいメソッドを追加する

`lib/model/doctrine` ディレクトリのなかの空の生成モデルクラスに新しいメソッドを追加できます。現在のオブジェクトメソッドを呼び出すには `$this` を使い、現在のクラスのスタティックメソッドを呼び出すには `self::` を使います。カスタムクラスが `lib/model/doctrine/base` ディレクトリのなかに設置される `Base` クラスのメソッドを継承することを覚えておいてください。

たとえば、リスト8-20で示されるように、リスト8-3をもとに生成された `Article` オブジェクトに対して、`Article` クラスのオブジェクトを echo することでタイトルを表示できるように、`__toString()` マジックメソッドを追加できます。

リスト8-20 - モデルをカスタマイズする (`lib/model/doctrine/Article.php`)

    [php]
    class Article extends BaseArticle
    {
      public function __toString()
      {
        return $this->getTitle();  // getTitle() は BaseArticle から継承される
      }
    }

対のクラスを拡張することもできます。たとえば、リスト8-21で示されるように、記事作成の日付順で並べられたすべての記事を検索するためにメソッドを追加します。

リスト8-21 - モデルをカスタマイズする (`lib/model/doctrine/ArticleTable.php`)

    [php]
    class ArticleTable extends BaseArticleTable
    {
      public function getAllOrderedByDate()
      {
        $q = $this->createQuery('a')
          ->orderBy('a.created_at ASC');

        return $q->execute();
      }
    }

リスト8-22で示されるように、新しいメソッドの使い方は生成メソッドと同じです。

リスト8-22 -カスタムモデルメソッドの使い方は生成メソッドと似ている

    [php]
    $articles = Doctrine_Core::getTable('Article')->getAllOrderedByDate();
    foreach ($articles as $article)
    {
      echo $article;      // __toString()マジックメソッドを呼び出す
    }

### 既存のメソッドをオーバーライドする

`Baseクラス` の生成メソッドがあなたの要件に合わない場合、これらのメソッドをカスタムクラスでオーバーライドすることもできます。同じメソッドのシグニチャ (すなわち、同じ数の引数) を使っていることを確認してください。

たとえば、`$article->getComments()` メソッドは`Comment`オブジェクトのコレクションを順不同で返します。最新コメントが一番最初になるように作成日時の順序でコメントを並べたい場合、リスト8-23で示されるように`getComments()`メソッドをオーバーライドします。

リスト8-23 - 既存のモデルメソッドをオーバーライドする (`lib/model/doctrine/Article.php`)

    [php]
    public function getComments()
    {
      $q = Doctrine_Core::getTable('Comment')
        ->createQuery('c')
        ->where('c.article_id = ?', $this->getId())
        ->orderBy('c.created_at ASC');

      return $q->execute();
    }

### モデルのビヘイビアを使う

一般的に複数のモデルを修正するものは再利用可能です。たとえば、モデルオブジェクトをソート可能にしてオブジェクトの保存が同時に起きることを防止する楽観的ロック(オプティミスティックロック)にすることは多くのクラスに追加できる一般的な拡張方法です。

symfony はこれらの拡張機能をビヘイビアにまとめます。ビヘイビア (behavior) とはモデルクラスに追加メソッドを提供する外部クラスです。モデルクラスはフックを持ちます。symfony にはビヘイビアを拡張する方法があります。

モデルクラスのビヘイビアを有効にするには、スキーマを修正しに `actAs` オプションを追加しなければなりません:

    [yml]
    Article:
      actAs: [Timestampable, Sluggable]
      tableName: blog_article
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        title:   string(255)
        content: clob

モデルをリビルドすると、`Article` モデルはタイトルに基づいたフレンドリーなURL文字列を自動的にセットしてくれる slug カラムを持つようになります。

Doctrineには次のようなビヘイビアが用意されています:

 * Timestampable
 * Sluggable
 * SoftDelete
 * Searchable
 * I18n
 * Versionable
 * NestedSet

スキーマの拡張構文
------------------

リスト8-3で示されるように、`schema.yml` ファイルを簡略化できます。しかしながらリレーショナルモデルが複雑であることがよくあります。ほとんどすべての事例に対処できるスキーマの拡張構文がある理由はそういうわけです。

### 属性

リスト8-24で示されるように、接続とテーブルは固有の属性を持つことができます。これらは `_attributes` キーの下で設定します。

リスト8-24 - モデルに対する設定の属性

    [yml]
    Article:
      attributes:
        export: tables
        validate: none

`export` 属性はモデルのためのテーブルを生成するときにデータベースにどんな SQL がエクスポートされるかを調整します。 `tables` を指定するとテーブル構造だけをエクスポートし外部キー、インデックスなどの情報はエクスポートしません。

リスト8-25で示されるように、ローカライズされる内容を収めるテーブル (すなわち、国際化のために、関連するテーブルのなかに存在する、複数のバージョンの内容) を使用するためには I18n ビヘイビアを使います (詳細は13章を参照)。

リスト8-25 - 国際化テーブル用の属性

    [yml]
    Article:
      actAs:
        I18n:
          fields: [title, content]

>**SIDEBAR**
>**複数のスキーマを扱う**
>
>アプリケーションごとに複数のスキーマを用意できます。symfony は `config/doctrine` フォルダーの名前が `*.yml` で終わるすべてのファイルを考慮に入れます。アプリケーションが多くのテーブルを持つ場合、もしくはテーブルが同じ接続を共有しない場合、このアプローチがとても便利であることがわかります。
>
>次の2つのスキーマを考えてください:
>
>     [yml]
>     // In config/doctrine/business-schema.yml
>     Article:
>       id:
>         type: integer
>         primary: true
>         autoincrement: true
>       title: string(50)
>
>     // In config/doctrine/stats-schema.yml
>     Hit:
>       actAs: [Timestampable]
>       columns:
>         id:
>           type: integer
>           primary: true
>           autoincrement: true
>         resource: string(100)
>
>
>同じ接続を共有する両方のスキーマ (`doctrine`) と `Article` クラスと `Hit` クラスは同じ `lib/model/doctrine` ディレクトリのもとで生成されます。あたかもスキーマを1つだけ書いたようにすべてのものごとが行われます。
>
>異なる接続(たとえば、`databases.yml` で定義される `doctrine` と `doctrine_bis`) を使う異なるスキーマを持つことで、接続先ごとに設定することができます。
>
>     [yml]
>     // In config/doctrine/business-schema.yml
>     Article:
>       connection: doctrine
>       id:
>         type: integer
>         primary: true
>         autoincrement: true
>       title: string(50)
>
>     // In config/doctrine/stats-schema.yml
>     Hit:
>       connection: doctrine_bis
>       actAs: [Timestampable]
>       columns:
>         id:
>           type: integer
>           primary: true
>           autoincrement: true
>         resource: string(100)
>
>
>多くのアプリケーションは複数のスキーマを使います。とりわけ、プラグインのなかにはアプリケーション独自のクラスに干渉しないようにプラグイン独自のスキーマを持つものがあります(詳細は17章を参照)。

### カラムの詳細

基本的な構文によってカラムの型を表すキーワードから1つを指定することで型を定義することができます。リスト8-26はこれらの選択肢のお手本を示しています。

リスト8-26 - 基本的なカラム属性

    [yml]
    Article:
      columns:
        title: string(50)  # カラムの型と長さを指定

しかしながら、もっと多くのカラム属性を定義できます。もし行う場合、リスト8-27で示されるように、カラムの設定を連想配列として定義する必要があります。

リスト8-27 - 複雑なカラム属性

    [yml]
    Article:
      columns:
        id:       { type: integer, notnull: true, primary: true, autoincrement: true }
        name:     { type: string(50), default: foobar }
        group_id: { type: integer }

カラムのパラメーターは次のとおりです:

  * `type`: カラムの型。選択肢は `boolean`、 `integer`、 `double`、 `float`、 `decimal`、 `string(size)`、 `date`、 `time`、 `timestamp`、 `blob` そして `clob`.
  * `notnull`: ブール値。カラムを必須にしたい場合これを `true` にセットします。
  * `length`: 型がサポートするフィールドのサイズもしくは長さ。
  * `scale`: decimal データ型のための小数位 (size も指定しなければなりません)。
  * `default`: デフォルト値。
  * `primary`: ブール値。プライマリキーの場合は `true` をセットします。
  * `autoincrement`: ブール値. オートインクリメントされる `integer` 型のカラムの場合は `true` をセットします。
  * `sequence`: `autoIncrement` カラムに対してシーケンスを使うデータベース (たとえば PostgreSQL、Oracle) のためのシーケンス名。
  * `unique`: ブール値。カラムにユニーク制約を必要とする場合に `true` をセットします。

### リレーション設定

モデルに `relations` キーを設定することで外部キーによるリレーションを指定することができます。リスト8-28のスキーマは `blog_user` テーブルの `id` カラムにマッチする `user_id` カラムの上側に外部キーを作ります

リスト8-28 - 外部キーの代替構文

    [yml]
    Article:
      actAs: [Timestampable]
      tableName: blog_article
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        title:   string(255)
        content: clob
        user_id: integer
      relations:
        User:
          onDelete: CASCADE
          foreignAlias: Articles

### インデックス

モデルの `indexes:` キーの下でインデックスを指定することができます。ユニークインデックスを定義したい場合、`type: unique` 構文を使わなければなりません。テキスト型のカラムでサイズを必要とするカラムのために丸括弧を使ってインデックスのサイズの長さを指定します。リスト8-30はインデックスを指定するもうひとつの方法を示しています。

リスト8-30 - インデックスとユニークインデックスの代替構文

    [yml]
    Article:
      actAs: [Timestampable]
      tableName: blog_article
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        title:   string(255)
        content: clob
        user_id: integer
      relations:
        User:
          onDelete: CASCADE
          foreignAlias: Articles
      indexes:
        my_index:
          fields:
            title:
              length: 10
            user_id: []
        my_other_index:
          type: unique
          fields:
            created_at

### 国際化テーブル

symfony は関連テーブルのコンテンツの国際化をサポートをします。このことは、コンテンツのサブジェクトを国際化するとき、2つのテーブルに個別に保存されることを意味します: 1つは変わらないカラムでもう1つが国際化されたカラムです。

リスト8-33 - 明示的な国際化メカニズム

    [yml]
    DbGroup:
      actAs:
        I18n:
          fields: [name]
      columns:
        name: string(50)

### ビヘイビア

ビヘイビア (behavior) は Doctrine のクラスに新しい機能を追加をプラグインによって提供することができるモデルを修正するライブラリです。17章でビヘイビアを詳しく説明します。ビヘイビアをそれぞれのテーブルに対して、パラメーターと一緒に、`_behaviors` キーの下に並べることでスキーマのなかでビヘイビアを直接定義できます。リスト8-34は `BlogArticle` クラスを `paranoid` ビヘイビアで拡張する例を示しています。

リスト8-34 - ビヘイビアの宣言

    [yml]
    Article:
      actAs: [Sluggable]
      # ...

同じモデルを2回作らない
-----------------------

ORM を使う場合のトレードオフはデータ構造を2回定義しなければならないことです: 1回目はデータベースに対して、2回目はオブジェクトモデルに対してです。幸いにして、symfony は一方をもとにもう一方を生成するコマンドラインツールを提供するので、重複作業を回避できます。

### 既存のスキーマをもとにSQLのデータベース構造をビルドする

`schema.yml` ファイルを書くことでアプリケーションを始める場合、symfony は YAML データモデルから直接テーブルを作成する SQL クエリを生成できます。クエリを使うには、プロジェクトのルートに移動して次のコマンドを入力します:

    $ php symfony propel:build-sql

`myproject/data/sql/` ディレクトリのなかで `schema.sql` ファイルが作られます。SQL の生成コードが `database.yml` で定義されるデータベースシステムに対して最適化されることを覚えておいてください。

テーブルを直接ビルドするために `schema.yml` ファイルを利用できます。たとえば、MySQL では、次のコマンドを入力します:

    $ mysqladmin -u root -p create blog
    $ mysql -u root -p blog < data/sql/schema.sql

生成される SQL もほかの環境のデータベースのリビルド、もしくはほかの DBMS に変更するために役立ちます。

>**TIP**
>コマンドラインはテキストファイルをもとにデータをデータベースに投入するタスクも提供します。`doctrine:data-load` タスクと YAML フィクスチャファイルの詳細な情報は16章をご覧ください。

### 既存のデータベースから YAML データモデルを生成する

イントロスペクション (introspection) のおかげで、既存のデータベースから `schema.yml` ファイルを生成するために symfony は Doctrine を利用できます。これはリバースエンジニアリングを行うとき、もしくはオブジェクトモデルよりもデータベースにとり組みたい場合に役立ちます。

これを行うために、プロジェクトの `databases.yml` ファイルが正しいデータベースを指し示しすべての接続設定を収めていることを確認する必要があります。それから `doctrine:build-schema` コマンドを呼び出します:

    $ php symfony propel:build-schema

データベース構造から生成された新品の `schema.yml` ファイルは `config/doctrine/` ディレクトリのなかで生成されます。このスキーマをもとにモデルをビルドできます。

まとめ
------

symfony は Doctrine をオブジェクトリレーショナルマッピング (ORM - Object-Relational Mapping) として、PDO (PHP Data Objects) をデータベース抽象化レイヤー (database abstraction layer) として利用します。これはオブジェクトモデルクラスを生成する前に、最初に YAML フォーマットでデータベースのリレーショナルスキーマを記述しなければならないことを意味します。それから、実行時において、オブジェクトのメソッドとレコードもしくはレコードセットの情報を検索するためにピアクラスを使います。接続設定は複数の接続をサポートする `databases.yml` ファイルで定義されます。そして、コマンドラインには重複して構造を定義しないようにする特別なタスクが含まれます。

モデルレイヤー (model layer) は symfony フレームワークのなかでもっとも複雑です。複雑である理由の1つはデータ操作が込み入った問題であるからです。関連するセキュリティ問題はWebサイトにとって重大で無視できません。ほかの理由は symfony が中規模から大規模のアプリケーションにもっとも適しているからです。このようなアプリケーションにおいて、symfony のモデルによって提供される自動化は本当に時間を節約するので、内部構造を学ぶ価値はあります。

ですので、モデルオブジェクトとメソッドを十分に理解するにはこれらをテストすることに時間を費やすことをためらわないでください。大きな報酬としてアプリケーションの堅牢性とスケーラビリティが得られます。
