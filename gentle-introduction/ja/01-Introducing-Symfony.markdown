第1章 - symfony の紹介
=====================

symfony を使うとどういったことができるのでしょうか? また、symfony を使うにはどのような準備が必要なのでしょうか? この章ではこれらの質問にお答えします。

symfony とは
------------

フレームワークを使うと、特定の目的のために採用される多くのパターンを自動化でき、アプリケーション開発の合理化を推し進めることができます。またフレームワークではコードが構造化されており、開発者に読みやすく、メンテナンスしやすいコードを書くことが推奨されます。フレームワークでは複雑な作業が簡単なステートメントにまとめるので、最終的に、プログラミング作業はより楽になります。

symfony は、いくつかの主要な機能によって Web アプリケーションの開発を最適化する目的で設計された、完全なフレームワークです。symfony では Web アプリケーションのビジネスルール、サーバーのロジック、そしてプレゼンテーションのビューが分離されています。また、複雑な Web アプリケーションの開発期間を短くするための多くのツールとクラスがあります。さらに、共通処理が自動化されているため、開発者はアプリケーションのビジネスロジックだけに集中できます。まとめると、新しいアプリケーションを作るたびに車輪を再発明する必要がないということです！

symfony はすべて PHP で書かれています。symfony は現実の世界のさまざまなプロジェクト([answers](http://sf-to.org/answers)、[delicious](http://sf-to.org/delicious)、[dailymotion](http://sf-to.org/dailymotion))で徹底的にテストされ、高度な要件が求められる電子商取引のWebサイトでも実際に利用されています。symfony は、MySQL、PostgreSQL、Oracle、Microsoft SQL Server を含む利用可能なほとんどのデータベースエンジンと互換性があります。symfony は Unix 系や Windows プラットフォームで動作します。次のセクションでは、機能を詳しく見てみましょう。

### symfony の機能

symfony は次の要件を満たすよう開発されました:

  * ほとんどのプラットフォームでのインストールと設定作業が簡単である (そして標準的な Unix 系と Windows プラットフォームでの動作が保証されている)
  * データベースエンジンに依存していない
  * 多くの案件で使いやすい一方で、複雑な案件に適合する柔軟性がある
  * 設定よりも規約 (convention over configuration) の前提に基づく -- 開発者は型にはまらないものだけ設定すればよい
  * 主要な Web 開発のベストプラクティスとデザインパターンに準拠する
  * 企業の既存のIT方針やアーキテクチャに適合しやすく、十分に安定しているため長期間のプロジェクトに使える
  * コードは読みやすく、phpDocumentor コメントも含まれているため、メンテナンスしやすい
  * ほかのベンダーライブラリとの統合もでき、拡張しやすい

#### Web プロジェクト機能の自動化

次のような Web プロジェクトの共通機能の大部分は、symfony によって自動的に処理されます:

  * 内容のローカライゼーションと同様に、組み込みの国際化レイヤーによってデータとインターフェイスの両方の翻訳を可能にする。
  * プレゼンテーションはテンプレートとレイアウトを利用しており、symfony の知識を持たない HTML デザイナーでも開発できる。ヘルパーにより、大部分のコードを単純なコードの呼び出しにカプセル化できるので、プレゼンテーションのコード量を減らす。
  * フォームは自動化されたバリデーションと再投入をサポートする。これにより、データベースのデータの質、および優れたユーザーエクスペリエンスが保証される。
  * 出力エスケーピングにり、不正なデータを使った攻撃からアプリケーションを保護する。
  * キャッシュ機能により、使用する回線の帯域幅やサーバーの負荷を減らす。
  * 認証とクレデンシャル機能により、制限つきのセクションの作成やユーザーのセキュリティ管理が円滑に行える。
  * ルーティングとスマート URL により、ページのアドレスをインターフェイスの一部にし、検索エンジンにフレンドリーにする。
  * 組み込みのEメールや API の管理機能により、古典的なブラウザーのインタラクションのみでは不可能だった処理を Web アプリケーションで可能にする。
  * パジネーション、ソート、そしてフィルタリングにより、一覧画面はよりユーザーフレンドリーになる。
  * ファクトリ、プラグイン、イベントシステムにより、高いレベルの拡張性を提供する。

#### 開発環境とツール

独自のコーディングのガイドラインとプロジェクトの管理ルールを持つ企業の要件を満たすために、symfony を完全にカスタマイズできます。symfony は標準でいくつかの開発環境を提供し、一般的なソフトウェアエンジニアリングのタスクを自動化する、次のような複数のツールを搭載しています:

  * プロトタイプ作成や、バックエンドの管理画面をワンクリックで生成するために大いに役立つコード生成ツール。
  * 完全なテスト駆動開発を可能にするツールを含む、組み込みのユニットテストと機能テストのフレームワーク。
  * 開発者が作業しているページで必要なすべての情報を表示し、デバッグ作業を迅速に行えるデバッグパネル。
  * 2 つのサーバー間でアプリケーションのデプロイを自動化するコマンドラインインターフェイス。
  * 稼働中のアプリケーションの設定を変更し適用できる。
  * アプリケーションの動作に関するすべての詳細情報をサイト管理者に提供するロギング機能。

### symfony をつくったのは誰? なぜ?

symfonyの最初のバージョンは、プロジェクトの創設者であり、この本の共著者でもある Fabien Potencier (ファビアン・プゥトンシェ) によって 2005 年 10 月に公開されました。Fabien は Sensio ([http://www.sensio.com/](http://www.sensio.com/)) の CEO です。Sensio はフランスの Web 制作会社で、Web 開発に関して革新的な見解を持つことでよく知られています。

2003年頃、Fabien は、PHP で作られた Web アプリケーション用のオープンソース開発ツールの調査に時間を費やしましたが、前に説明した要件を満たすものが存在しないことが判明しました。その後 PHP 5 が公開されると、彼は利用可能なツールが十分に成熟し、フルスタックのフレームワークとして統合できる段階に到達したと判断しました。彼はその後、1年を費やして symfony コアを開発しました。symfony コアは、Mojavi のモデル・ビュー・コントローラー (MVC) フレームワーク、Propel のオブジェクトリレーショナルマッピング (ORM)、および Ruby on Rails のテンプレートヘルパーなどがベースとなっています。

Fabien は当初、Sensio のプロジェクトのために symfony を開発しました。開発会社が思いどおりに使える効率的なフレームワークを保有することは、アプリケーションを早く効率的に開発する理想的な方法につながるからです。また、symfony を使うことで Web 開発がより直感的なものになり、symfony を使って開発されたアプリケーションは堅牢でメンテナンスが楽になりました。ランジェリー小売店向けの電子商取引 Web サイトの開発に symfony を採用し、symfony の性能を確認しました。その後、ほかのプロジェクトでも symfony が使われていきました。

symfony を利用していくつかのプロジェクトを成功させた後、Fabien は symfony をオープンソースのライセンスのもとで公開しました。symfony を公開したのは、作品をコミュニティに寄贈するため、ユーザーのフィードバックの恩恵を受けるため、Sensio の実績を示すため、そして Fabien が面白いと感じたからです。

>**Note**
>「symfony」が「FooBarFramework」という名前ではないのはなぜでしょうか? 覚えやすく、他の開発ツールを連想しないようにするために、Fabien は Sensio を表す s とフレームワークを表す f を含む短い単語にしたかったのです。また、彼は大文字が好きではありませんでした。symfony は完全な英語ではありませんが十分に英語に近い名前であり、プロジェクトの名前としても利用可能でした。ちなみに代替案は「baguette (フランスパン)」でした。

symfony の採用率を上げてオープンソースプロジェクトとして成功させるために、大規模な英語のドキュメントが必要でした。Fabien は、Sensio で働く仲間であり、この本のもうひとりの著者でもある François Zaninotto に、symfony のコードを徹底的に研究してオンラインの教科書を書くように依頼しました。この執筆にはしばらく時間がかかりましたが、プロジェクトが公開された時には十分なドキュメントが用意され、大勢の開発者にアピールできました。その後の話はご存じのとおりです。

### symfony のコミュニティ

symfony の公式サイト ([http://www.symfony-project.org/](http://www.symfony-project.org/)) が公開されるとすぐに、世界中の大勢の開発者がフレームワークをダウンロートしてインストールし、オンラインのドキュメントを読み、symfony で最初のアプリケーションを構築し、興奮のざわめきが始まりました。

当時、Web アプリケーションのフレームワークの人気が出つつあり、PHP 製のフルスタックフレームワークは高い需要がありました。symfony は、フレームワークのカテゴリで他のプレイヤーを越える 2 つの長所、つまり、すばらしい品質のコードと特筆すべき量のドキュメントのおかげで、魅力的なソリューションとなりました。すぐに貢献者が名乗り出て、パッチと機能の強化を提案され、ドキュメントの校正を行い、他の必要な役割を果たしました。

公開ソースリポジトリとチケットシステムを使ってさまざまな方法で貢献でき、ボランティアはみな歓迎されます。Fabien は、今もなおソースコードリポジトリの trunk の主要なコミッターで、コードの品質を保証します。

今日では、symfony の[フォーラム](http://forum.symfony-project.org/)、メーリングリスト([ユーザー向け](http://groups.google.com/group/symfony-users)、[開発者向け](http://groups.google.com/group/symfony-devs))、IRC [チャンネル](irc://irc.freenode.net/symfony)は、ざっと見て 1 つの質問に対して平均 4 つの回答を得られる理想的なサポート施設となっています。日々新しい人が symfony をインストールし、ウィキとコードスニペットのセクションには、ユーザーが投稿したドキュメントが多数ホストされています。symfony は、最も有名な PHP フレームワークの 1 つとなっています。

symfony フレームワークの3番目の強みはこのコミュニティで、この本を読んだ後、あなたもコミュニティの活動に参加してくださることを私たち執筆者は望んでいます。

### symfony は私の用途に合っていますか？

あなたが PHP の専門家であるか、Web アプリケーションのプログラミングの新人であるかに関わらず、symfony を使えるようになります。symfony で開発をするかどうか決める主な要因は、あなたのプロジェクトの規模です。

開発する Web サイトが 5 ページから 10 ページ程度の簡単な Web サイトで、データベースへのアクセスが制限されており、パフォーマンスを保証するもしくはドキュメントを提供する義務がなければ、単純な PHP コードにこだわるべきでしょう。Web アプリケーションのフレームワークから多くのことを得られず、おそらくはオブジェクト指向もしくは MVC モデルが開発プロセスを遅らせることになるでしょう。また、PHP スクリプトが CGI モードで動作する共用サーバー向けには symfony は最適化されておらず、効率的に動作しません。

一方で、重いビジネスロジックをかかえる、より複雑な Web アプリケーションを開発する場合、単純な PHP では十分ではありません。将来アプリケーションをメンテナンスする、もしくは拡張することを計画している場合、コードを軽量に、読みやすく、効果的なものにする必要があります。直感的な方法で、(Ajax のような)最新のユーザーインタラクションの機能を使いたい場合、JavaScript を数百行書くことは耐えられません。早く楽しく開発したい場合、単純な PHP だけではおそらく失望するでしょう。これらすべてのケースにでは、symfony を使うことをおすすめします。

そして、もちろん、あなたがプロの Web 開発者であるなら、すでに Web アプリケーションのすべての利点を理解しており、成熟し、充実したドキュメントがあり、大きなコミュニティを持つフレームワークが必要です。もう検索しなくても、symfony があなたのソリューションです。

>**TIP**
>視覚的なデモンストレーションがお好みなら、symfony の Web サイトからスクリーンキャストをご覧ください。symfony でアプリケーションを開発することがどんなに速くて楽しいことなのかわかるでしょう。

基本概念
-------

Before you get started with symfony, you should understand a few basic concepts. Feel free to skip ahead if you already know the meaning of OOP, ORM, RAD, DRY, KISS, TDD, and YAML.

### PHP

Symfony is developed in PHP ([http://www.php.net/](http://www.php.net/)) and dedicated to building web applications with the same language. Therefore, a solid understanding of PHP and object-oriented programming is required to get the most out of the framework. The minimal version of PHP required to run symfony is PHP 5.2.4.

### Object-Oriented Programming (OOP)

Object-oriented programming (OOP) will not be explained in this chapter. It needs a whole book itself! Because symfony makes extensive use of the object-oriented mechanisms available as of PHP 5, OOP is a prerequisite to learning symfony.

Wikipedia explains OOP as follows:

  "The idea behind object-oriented programming is that a computer program may be seen as comprising a collection of individual units, or objects, that act on each other, as opposed to a traditional view in which a program may be seen as a collection of functions, or simply as a list of instructions to the computer."

PHP implements the object-oriented paradigms of class, object, method, inheritance, and much more. Those who are not familiar with these concepts are advised to read the related PHP documentation, available at [http://www.php.net/manual/en/language.oop5.basic.php](http://www.php.net/manual/en/language.oop5.basic.php).

### Magic Methods

One of the strengths of PHP's object capabilities is the use of magic methods. These are methods that can be used to override the default behavior of classes without modifying the outside code. They make the PHP syntax less verbose and more extensible. They are easy to recognize, because the names of the magic methods start with two underscores (`__`).

For instance, when displaying an object, PHP implicitly looks for a `__toString()` method for this object to see if a custom display format was defined by the developer:

    [php]
    $myObject = new myClass();
    echo $myObject;

    // Will look for a magic method
    echo $myObject->__toString();

Symfony uses magic methods, so you should have a thorough understanding of them. They are described in the PHP documentation ([http://www.php.net/manual/en/language.oop5.magic.php](http://www.php.net/manual/en/language.oop5.magic.php)).

### Object-Relational Mapping (ORM)

Databases are relational. PHP and symfony are object-oriented. In order to access the database in an object-oriented way, an interface translating the object logic to the relational logic is required. This interface is called an object-relational mapping, or ORM.

An ORM is made up of objects that give access to data and keep business rules within themselves.

One benefit of an object/relational abstraction layer is that it prevents you from using a syntax that is specific to a given database. It automatically translates calls to the model objects to SQL queries optimized for the current database.

This means that switching to another database system in the middle of a project is easy. Imagine that you have to write a quick prototype for an application, but the client has not decided yet which database system would best suit his needs. You can start building your application with SQLite, for instance, and switch to MySQL, PostgreSQL, or Oracle when the client is ready to decide. Just change one line in a configuration file, and it works.

An abstraction layer encapsulates the data logic. The rest of the application does not need to know about the SQL queries, and the SQL that accesses the database is easy to find. Developers who specialize in database programming also know clearly where to go.

Using objects instead of records, and classes instead of tables, has another benefit: you can add new accessors to your tables. For instance, if you have a table called `Client` with two fields, `FirstName` and `LastName`, you might like to be able to require just a `Name`. In an object-oriented world, this is as easy as adding a new accessor method to the `Client` class, like this:

    [php]
    public function getName()
    {
      return $this->getFirstName().' '.$this->getLastName();
    }

All the repeated data-access functions and the business logic of the data can be maintained within such objects. For instance, consider a class `ShoppingCart` in which you keep items (which are objects). To retrieve the full amount of the shopping cart for the checkout, you can add a `getTotal()` method, like this:

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

Using this method we are able to control the values returned from an object level. Imagine if later there is a decision to add some discount logic which affects the total - it can simply be added to the `getTotal()` method or even to the `getPrice()` methods of the items and the correct value would be returned.

Out of the box, symfony supports the two most popular open source ORMs in PHP: Propel and Doctrine. Symfony integrates both of them seamlessly. When creating a new symfony project, it's a matter of choice to use Propel or Doctrine.

This book will describe how to use the Propel and Doctrine objects, but for a more complete reference, a visit to the [Propel](http://www.propelorm.org/) website or the [Doctrine](http://www.doctrine-project.org/) website is recommended.

### Rapid Application Development (RAD)

Programming web applications has long been a tedious and slow job. Following the usual software engineering life cycles (like the one proposed by the Rational Unified Process, for instance), the development of web applications could not start before a complete set of requirements was written, a lot of Unified Modeling Language (UML) diagrams were drawn, and tons of preliminary documentation was produced. This was due to the general speed of development, the lack of versatility of programming languages (you had to build, compile, restart, and who knows what else before actually seeing your program run), and most of all, to the fact that clients were quite reasonable and didn't change their minds constantly.

Today, business moves faster, and clients tend to constantly change their minds in the course of the project development. Of course, they expect the development team to adapt to their needs and modify the structure of an application quickly. Fortunately, the use of scripting languages like Python, Ruby, and PHP makes it easy to apply other programming strategies, such as rapid application development (RAD) or agile software development.

One of the ideas of these methodologies is to start developing as soon as possible so that the client can review a working prototype and offer additional direction. Then the application gets built in an iterative process, releasing increasingly feature-rich versions in short development cycles.

The consequences for the developer are numerous. A developer doesn't need to think about the future when implementing a feature. The method used should be as simple and straightforward as possible. This is well illustrated by the maxim of the KISS principle: Keep It Simple, Stupid.

When the requirements evolve or when a feature is added, existing code usually has to be partly rewritten. This process is called refactoring, and happens a lot in the course of a web application development. Code is moved to other places according to its nature. Duplicated portions of code are refactored to a single place, thus applying the Don't Repeat Yourself (DRY) principle.

And to make sure that the application still runs when it changes constantly, it needs a full set of unit tests that can be automated. If well written, unit tests are a solid way to ensure that nothing is broken by adding or refactoring code. Some development methodologies even stipulate writing tests before coding--that's called test-driven development (TDD).

>**NOTE**
>There are many other principles and good habits related to agile development. One of the most effective agile development methodologies is called Extreme Programming (abbreviated as XP), and the XP literature will teach you a lot about how to develop an application in a fast and effective way. A good starting place is the XP series books by Kent Beck (Addison-Wesley).

Symfony is the perfect tool for RAD. As a matter of fact, the framework was built by a web agency applying the RAD principle for its own projects. This means that learning to use symfony is not about learning a new language, but more about applying the right reflexes and the best judgment in order to build applications in a more effective way.

### YAML

According to the official YAML [website](http://www.yaml.org/), YAML is "a human friendly data serialization standard for all programming languages". Put another way, YAML is a very simple language used to describe data in an XML-like way but with a much simpler syntax. It is especially useful to describe data that can be translated into arrays and hashes, like this:

    [php]
    $house = array(
      'family' => array(
        'name'     => 'Doe',
        'parents'  => array('John', 'Jane'),
        'children' => array('Paul', 'Mark', 'Simone')
      ),
      'address' => array(
        'number'   => 34,
        'street'   => 'Main Street',
        'city'     => 'Nowheretown',
        'zipcode'  => '12345'
      )
    );

This PHP array can be automatically created by parsing the YAML string:

    [yml]
    house:
      family:
        name:     Doe
        parents:
          - John
          - Jane
        children:
          - Paul
          - Mark
          - Simone
      address:
        number: 34
        street: Main Street
        city: Nowheretown
        zipcode: "12345"

In YAML, structure is shown through indentation, sequence items are denoted by a dash, and key/value pairs within a map are separated by a colon. YAML also has a shorthand syntax to describe the same structure with fewer lines, where arrays are explicitly shown with `[]` and hashes with `{}`. Therefore, the previous YAML data can be written in a shorter way, as follows:

    [yml]
    house:
      family: { name: Doe, parents: [John, Jane], children: [Paul, Mark, Simone] }
      address: { number: 34, street: Main Street, city: Nowheretown, zipcode: "12345" }

YAML is an acronym for "YAML Ain't Markup Language" and pronounced "yamel". The format has been around since 2001, and YAML parsers exist for a large variety of languages.

>**TIP**
>The specifications of the YAML format are available at [http://www.yaml.org/](http://www.yaml.org/).

As you can see, YAML is much faster to write than XML (no more closing tags or explicit quotes), and it is more powerful than `.ini` files (which don't support hierarchy). That is why symfony uses YAML as the preferred language to store configuration. You will see a lot of YAML files in this book, but it is so straightforward that you probably don't need to learn more about it.

Summary
-------

Symfony is a PHP web application framework. It adds a new layer on top of the PHP language, providing tools that speed up the development of complex web applications. This book will tell you all about it, and you just need to be familiar with the basic concepts of modern programming to understand it--namely object-oriented programming (OOP), object-relational mapping (ORM), and rapid application development (RAD). The only required technical background is knowledge of PHP.
