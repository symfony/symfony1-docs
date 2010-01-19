生産性を高める
==============

*Fabien Potencier 著*

symfony を使うこと自体が Web 開発者としてのあなたの生産性を高めるためのすばらしい方法です。もちろん、だれもがすでに symfony の詳細な例外情報と Web デバッグツールバーが生産性を大いに高めていることを知っています。この章では新しいもしくはあまり知られていない symfony の機能を使うことであなたの生産性をもっと高めるいくつかのヒントとこつをお教えします。

より速く始める: プロジェクト作成プロセスをカスタマイズする
--------------------------------------------------------

symfony CLI ツールのおかげで、新しい symfony のプロジェクトを作るのは速くてシンプルです:

    $ php /path/to/symfony generate:project foo --orm=Doctrine

`generate:project` タスクは新しいプロジェクトのデフォルトのディレクトリ構造を生成し、適切なデフォルトを持つ設定ファイルを作成します。アプリケーションを作成する、プラグインをインストールする、モデルを設定するなどのほかの symfony タスクを使うことができます。

しかし新しいプロジェクトを作成する最初のステップは通常はほとんど同じです: メインアプリケーションを作成する、プラグインをインストールする、リンクのコンフィギュレーションのデフォルトを調整する、などです。

symfony 1.3 に関して、プロジェクト作成プロセスはカスタマイズかつ自動化が可能です。

>**NOTE**
>すべての symfony のタスクはクラスなので、これらをカスタマイズして拡張するのはとても簡単です。ただし `generate:project` タスクはタスクが実行されるときプロジェクトが存在しないので簡単にカスタマイズできません。

`generate:project` タスクは `--installer` オプションをとります。このタスクは PHP スクリプトでプロジェクト作成プロセスの間に実行されます:

    $ php /path/to/symfony generate:project --installer=/somewhere/my_installer.php

`/somewhere/my_installer.php` スクリプトは `sfGenerateProjectTask` インスタンスのコンテキストで実行されるので、`$this` オブジェクトを使うことでタスクのメソッドにアクセスできます。次のセクションでプロジェクト作成プロセスをカスタマイズするために使うことのできるすべてのメソッドを説明します。

>**TIP**
>`php.ini` で `include()` 関数のために URL ファイルアクセスを有効にする場合、URL をインストーラーとして渡すことさえできます(もちろん何も知らないスクリプトでこれを行う場合は十分に注意する必要があります):
>
>      $ symfony generate:project
>      --installer=http://example.com/sf_installer.php

### `installDir()`

`installDir()` メソッドは新しく作られたプロジェクトのディレクトリ構造 (サブディレクトリとファイルで構成される) を反映します:

    [php]
    $this->installDir(dirname(__FILE__).'/skeleton');

### `runTask()`

`runTask()` メソッドはタスクを実行します。これはタスクの名前と、複数の引数として渡したい引数とオプションを表す文字列をとります:

    [php]
    $this->runTask('configure:author', "'Fabien Potencier'");

引数とオプションは配列としても渡すことができます:

    [php]
    $this->runTask('configure:author', array('author' => 'Fabien Potencier'));

>**TIP**
>タスクのショートネームは次のように使います:
>
>     [php]
>     $this->runTask('cc');

もちろんこのメソッドはプラグインをインストールするのにも使うことができます:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin');

特定のバージョンのプラグインをインストールするには、必要なオプションを渡すだけです:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin', array('release' => '10.0.0', 'stability' => beta'));

>**TIP**
>真新しくインストールしたプラグインからタスクを実行するには、最初にタスクをリロードする必要があります:
>
>     [php]
>     $this->reloadTasks();

新しいアプリケーションを作り `generate:module` のような特定のアプリケーションに依存するタスクを使いたい場合、コンフィギュレーションのコンテキストを自分自身で変更しなければなりません:

    [php]
    $this->setConfiguration($this->createConfiguration('frontend', 'dev'));

### ロガー

インストーラースクリプトを実行するとき開発者にフィードバックをするために、きわめて簡単にロギングできます:

    [php]
    // シンプルなロギング
    $this->log('some installation message');

    // ブロックをロギングする
    $this->logBlock('Fabien\'s Crazy Installer', 'ERROR_LARGE');

    // セクションでロギングする
    $this->logSection('install', 'install some crazy files');

### ユーザーとのやりとり

`askConfirmation()`、`askAndValidate()` と `ask()` メソッドはあなたに質問をしインストールプロセスは動的に設定できるようになります。

確認だけが必要であれば、`askConfirmation()` メソッドを使います:

    [php]
    if (!$this->askConfirmation('Are you sure you want to run this crazy installer?'))
    {
      $this->logSection('install', 'You made the right choice!');

      return;
    }

`ask()` メソッドを使うことで質問をしてユーザーの回答を文字列として得ることができます:

    [php]
    $secret = $this->ask('Give a unique string for the CSRF secret:');

質問を検証したいのであれば、`askAndValidate()` メソッドを使います:

    [php]
    $validator = new sfValidatorEmail(array(), array('invalid' => 'hmmm, it does not look like an email!'));
    $email = $this->askAndValidate('Please, give me your email:', $validator);

### ファイルシステムのオペレーション

ファイルシステムを変更したいのであれば、symfony のファイルシステムオブジェクトにアクセスします:

    [php]
    $this->getFilesystem()->...();

>**SIDEBAR**
>サンドボックス作成プロセス
>
>symfony のサンドボックスは symfony のプロジェクトがあらかじめパッケージになっており、あらかじめ作成されたアプリケーションと設定された SQLite データベースが入っています。インストーラースクリプトを使えば誰でもサンドボックスを作ることができます:
>
>     $ php symfony generate:project --installer=/path/to/symfony/data/bin/sandbox_installer.php
>
>インストーラースクリプトの実例は `symfony/data/bin/sandbox_installer.php` スクリプトを見てください。

インストーラースクリプトは単なる別の PHP ファイルです。ですので、思いどおりのことができます。新しい symfony プロジェクトを作成するたびに同じタスクを何度も実行する代わりに、独自のインストーラースクリプトを作り思いどおりに symfony のプロジェクトのインストール方法を調整できます。インストーラーで新しいプロジェクトを作るのははるかに速く、ステップを見失うのを防ぎます。インストーラースクリプトをほかの人と共有することもできます！

>**TIP**
>[6 章](#chapter_06)では、カスタムインストーラーを使います。このためのコードは[付録 B](#chapter_b) で見つかります。

開発をより速くする
-----------------

PHP コードから CLI タスクまで、プログラムを組むことはたくさんの打ち込みをすることを意味します。打ち込みを最小限に減らすやり方を見てゆきましょう。

### IDEを選ぶ

IDE を使うことは複数の方法で開発者をより生産的にすることを手助けします。

最初に、最新の IDE は設定をしなくても PHP の自動入力補完を提供します。このことは必要なことはメソッド名の最初の数文字を入力するだけであることを意味します。このことはメソッド名を覚えたくない場合でも、API を見ることなく IDE が現在のオブジェクトの利用可能なすべてのメソッドを提示することも意味します。

加えて、PHPEdit もしくは Netbeans のような IDE は、symfony により精通しており symfony プロジェクトとの特別な統合を提供します。

>**SIDEBAR**
>テキストエディター
>
>ユーザーのなかにはプログラミングにおいてテキストエディターを好む人もいます。これはおもにテキストエディターは IDE よりも速いからです。もちろん、テキストエディターが提供する IDE 指向の機能は少なめです。しかしながら、もっとも人気のあるエディターは、ユーザーエクスペリエンスを強化するのに使えるプラグイン/エクステンションを提供し PHP と symfony のプロジェクトでエディターをより効率的に機能させてくれます。
>
>たとえば、多くの Linux ユーザーはすべての作業に VIM を使う傾向にあります。
>このツールをお使いの方は、[vim-symfony](http://github.com/geoffrey/vim-symfony) エクステンションが利用できます。vim-symfony は VIM スクリプトのセットで symfony を好みのエディターに統合します。vim-symfony を使えば、symfony の開発を効率化する vim マクロとコマンドを簡単に作ることができます。
>これはデフォルトコマンドも搭載しており、少し指を動かせばたくさんの設定ファイル (スキーマ、ルーティングなど) が挿入されアクションからテンプレートに簡単に切り替えることができます。
>
>Mac OS X　ユーザーのなかには TextMate を使っている人がいます。これらの開発者は[バンドル](http://github.com/denderello/symfony-tmbundle)をインストールできます。これは日々の活動での多くの時間を節約してくれるマクロとショートカットを追加します。

#### symfony をサポートする IDE を使う

IDE のなかには、[PHPEdit 3.4](http://www.phpedit.com/en/presentation/extensions/symfony) と [NetBeans 6.8](http://www.netbeans.org/community/releases/68/) のように、symfony のネイティブサポートがあるので、symfony とのきめ細かい統合を提供します。これらの symfony 固有のサポートおよび、これが開発をどのように手助けしてくれるのか学ぶためにはそれらのドキュメントを見てください。

#### IDE を助ける

IDE での PHP 自動入力補完は PHP コードで明示的に定義されたメソッドにのみ機能します。しかしコードが `__call()` もしくは `__get()` "マジック"メソッドを使う場合、IDEが 利用可能なメソッドもしくはプロパティを推測する方法はありません。よい知らせは PHPDoc ブロックでメソッドかつ/もしくはプロパティを提供することでたいていの IDE を手助けすることができます (それぞれ `@method` と `@property` アノテーションを使うことで)。

動的なプロパティ (`message`) と動的なメソッド (`getMessage()`) を持つ `Message` クラスがあるとします。次のコードは IDE が PHP コードでの明示的なコードなしでこれらをわかる様子を示すものです:

    [php]
    /**
     * @property clob $message
     *
     * @method clob getMessage() Returns the current message value
     */
    class Message
    {
      public function __get()
      {
        // ...
      }

      public function __call()
      {
        // ...
      }
    }

`getMessage()` メソッドが存在しなくても `@method` アノテーションがあるので、IDE によりこのメソッドが認識されます。`message` プロパティについても `@property` アノテーションを追加したので、同様に認識されます。

このテクニックは `doctrine:build-model` タスクで使われています。たとえば、2 つのカラム (`message`と`priority`) を持つ Doctrine の `MailMessage` クラスは次のようになります:

    [php]
    /**
     * BaseMailMessage
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @property clob $message
     * @property integer $priority
     *
     * @method clob        getMessage()  Returns the current record's "message" value
     * @method integer     getPriority() Returns the current record's "priority" value
     * @method MailMessage setMessage()  Sets the current record's "message" value
     * @method MailMessage setPriority() Sets the current record's "priority" value
     *
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    abstract class BaseMailMessage extends sfDoctrineRecord
    {
        public function setTableDefinition()
        {
            $this->setTableName('mail_message');
            $this->hasColumn('message', 'clob', null, array(
                 'type' => 'clob',
                 'notnull' => true,
                 ));
            $this->hasColumn('priority', 'integer', null, array(
                 'type' => 'integer',
                 ));
        }

        public function setUp()
        {
            parent::setUp();
            $timestampable0 = new Doctrine_Template_Timestampable();
            $this->actAs($timestampable0);
        }
    }

ドキュメントを見つけるのを速くする
---------------------------------

symfony は多くの機能を持つ大きなフレームワークなので、可能性のあるコンフィギュレーションもしくはすべてのクラスとメソッドを思いどおりにすべて覚えるのは簡単であるとは限りません。これまで見てきたとおり、IDE を使うことは自動入力補完で大きな効果があります。回答をできるかぎり速く見つけるために既存のツールがどのようにてこいれされるのか調べてみましょう。

### オンライン API

クラスもしくはメソッドに関するドキュメントを見つけるための最速の方法は、オンライン [API](http://www.symfony-project.org/api/1_3/) を眺めることです。

よりおもしろいものは組み込みの API 検索エンジンです。結果は数タッチでクラスもしくはメソッドを素早く見つけることができます。数文字を API ページの検索ボックスに入力した後で、クイックサーチの結果ボックスは役に立つ提示内容とともにリアルタイムで表示されます。

クラス名の最初を入力することで検索できます:

![API 検索](http://www.symfony-project.org/images/more-with-symfony/api_search_1.png "API 検索")

もしくはメソッド名でもできます:

![API 検索](http://www.symfony-project.org/images/more-with-symfony/api_search_2.png "API 検索")

すべての利用可能なメソッドの一覧を表示するにはクラス名の後に `::` をつけます:

![API 検索](http://www.symfony-project.org/images/more-with-symfony/api_search_3.png "API 検索")

もしくはさらなる候補を精選するためにメソッドの始めを入力します:

![API 検索](http://www.symfony-project.org/images/more-with-symfony/api_search_4.png "API 検索")

すべてのクラスのパッケージのリストを表示したければ、パッケージの名前を打ち込んでリクエストを投稿します。

symfony API 検索をブラウザーに統合するさえできます。この方法では、何かを探すために、symfony 公式サイトに移動する必要はありません。これが可能なのは symfony API のネイティブの [OpenSearch](http://www.opensearch.org/) サポートが提供されているからです。

Firefox を使っているのであれば、symfony API 検索エンジンは検索エンジンのメニューに自動的に現れます。ブラウザーの検索ボックスにこれらの1つを追加するために API ドキュメントのセクションから "API OpenSearch" のリンクをクリックすることもできます。

>**NOTE**
>symfony 公式[ブログ](http://www.symfony-project.org/blog/2009/02/24/opensearch-support-for-the-symfony-api)で symfony API 検索エンジンが Firefox に統合されている様子を示すスクリーンキャストを見ることができます。

### チートシート

symfony の主要な部分に関する情報に素早くアクセスしたいのであれば、[チートシート](http://trac.symfony-project.org/wiki/CheatSheets)の大きなコレクションを利用できます:

 * [ディレクトリ構造と CLI](http://andreiabohner.files.wordpress.com/2007/03/cheatsheetsymfony001_enus.pdf)
 * [ビュー](http://andreiabohner.files.wordpress.com/2007/08/sfviewfirstpartrefcard.pdf)
 * [ビュー: パーシャル、コンポーネント、スロットとコンポーネントスロット](http://andreiabohner.files.wordpress.com/2007/08/sfviewsecondpartrefcard.pdf)
 * [Lime によるユニットテストと機能テスト](http://trac.symfony-project.com/attachment/wiki/LimeTestingFramework/lime-cheat.pdf?format=raw)
 * [ORM](http://andreiabohner.files.wordpress.com/2007/08/sform_enus.pdf)
 * [Propel](http://andreiabohner.files.wordpress.com/2007/08/sfmodelfirstpartrefcard.pdf)
 * [Propel スキーマ](http://andreiabohner.files.wordpress.com/2007/09/sfmodelsecondpartrefcard.pdf)
 * [Doctrine](http://www.doctrine-project.org/Doctrine-Cheat-Sheet.pdf)

>**NOTE**
>これらのチートシートのなかには symfony 1.3/1.4 に対応していないものがあります。

### オフラインドキュメント

コンフィギュレーションに関する質問は symfony リファレンスガイドによってもっとよく回答されています。 
symfony を開発するときはぜひこの本を手元に置いておくべきです。この本はとても詳細な目次、用語の索引、章内部の相互参照、表などのおかげで利用可能なすべてのコンフィギュレーションを見つけるための最速の手段です。

[オンライン](http://www.symfony-project.org/reference/1_4/ja/)で読む、[印刷されたもの](http://books.sensiolabs.com/book/the-symfony-1-4-reference-guide)を購入する、もしくは [PDF](http://www.symfony-project.org/get/pdf/reference-1.4-en.pdf) バージョンをダウンロードできます。

### オンラインツール

この章の始めで見てきたとおり、symfony はより速く始めるための手助けをしてくれるすばらしいツールセットを提供します。最後にはプロジェクトを終わらせ、運用サーバーにデプロイするときが来ます。

プロジェクトの開発の準備が整っているか確認するには、オンラインの開発[チェックリスト](http://symfony-check.org/)を使うことができます。このサイトは運用環境に移行する前にチェックする必要のある主要な項目をカバーしています。

より速くデバッグをする
---------------------

開発環境でエラーが起きるとき、symfony は役に立つ情報で満たされたすばらしい例外ページを表示します。たとえば、実行されてきたスタックトレースとファイルを見ることができます。`settings.yml` 設定ファイル (下記を参照) で ~`sf_file_link_format`~ 設定をセットアップする場合、ファイル名でクリックすれば関連ファイルは好きなテキストエディターもしくは IDE で即座に開かれます。これは問題をデバッグするときたくさんの時間を節約してくれるとても小さな機能のすばらしい例です。

>**NOTE**
>Web デバッグツールバーの log と view パネルはファイル名も表示します (とくに XDebug が有効なとき)。`sf_file_link_format` 設定がセットされているときそのファイル名はクリック可能になります。

デフォルトでは、`sf_file_link_format` は空で PHP コンフィギュレーションの [`xdebug.file_link_format`](http://xdebug.org/docs/all_settings#file_link_format) の値が存在する場合はそれが symfony のデフォルトとなります (最新バージョンの XDebug にて `php.ini` に `xdebug.file_link_format` をセットすることで、スタックトレースですべてのファイル名へのリンクを追加することを許可します)。

`sf_file_link_format` の値は IDE と OS に依存します。たとえば、~TextMate~ でファイルを開きたい場合、次のコードを `settings.yml` に追加します:

    [yml]
    dev:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

`%f` プレースホルダーはファイルの絶対パスに置き換えられ、`%l` プレースホルダーは行数に置き換えられます。

VIM を使うのであれば、コンフィギュレーションはより複雑でありオンラインの [symfony](http://geekblog.over-blog.com/article-symfony-open-exceptions-files-in-remote-vim-sessions-37895120.html) と [XDebug](http://www.koch.ro/blog/index.php?/archives/77-Firefox,-VIM,-Xdebug-Jumping-to-the-error-line.html) で説明されています。

>**NOTE**
>IDE の設定方法を学ぶには好みの検索エンジンを使います。同じ方法で両方を動かすために `sf_file_link_format` もしくは `xdebug.file_link_format` のコンフィギュレーションを見ることができます。

より速くテストする
-----------------

### 機能テストを記録する

機能テストはアプリケーションのすべてのピースの統合をテストすることを通してユーザーとのやりとりをシミュレートします。 機能テストを書くのは簡単ですが時間がかかります。それぞれの機能テストのファイルはユーザーがブラウザーで Web サイトにアクセスするのをシミュレートするシナリオであり、アプリケーションにブラウザーでアクセスするほうが PHP のコードを書くよりも速いので、ブラウザーセッションを記録して PHP コードに自動的に変換できるとしたらどうしますか？ありがたいことに、symfony にはこのためのプラグインがあります。これは [swFunctionalTestGenerationPlugin](http://www.symfony-project.org/plugins/swFunctionalTestGenerationPlugin) と呼ばれ、ほんの数分でカスタマイズの準備ができているテストスケルトンを生成することを可能にします。もちろん、より便利にするために適切なテスター呼び出しを追加する必要がありますが、それにも関わらずこれはすばらしく時間を節約してくれます。

プラグインは symfony のフィルターを登録することで動きます。すべてのリクエストを傍受し、これらを機能テストのコードに変換します。プラグインを通常のやり方でインストールした後で、これを有効にする必要があります。アプリケーションの `filters.yml` を開きコメント行の後に次の行を追加します:

    [php]
    functional_test:
      class: swFilterFunctionalTest

次に、`ProjectConfiguration` クラスでプラグインを有効にします:

    [php]
    // config/ProjectConfiguration.class.php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->enablePlugin('swFunctionalTestGenerationPlugin');
      }
    }

プラグインはメインのユーザーインターフェイスとして Web デバッグツールバーを使うので、これが有効になっていることを確認してください (開発環境でのデフォルトのケース)。有効にされているとき、"Functional Test" という名前の新しいメニューが利用できるようになります。このパネルにおいて、"Activate" のリンクをクリックすればセッションの記録が始まり、"Reset" をクリックすれば現在のセッションがリセットされます。これを行うとき、ファイルをテストしてカスタマイズするためにテキストエリアからコピー＆ペーストします。

### より速くテストスイートを実行する

大きなテストスイートがあるとき、変更をするたびに、とりわけ一部のテストが通らない場合、すべてのテストを立ち上げるのにとても時間がかかることがあります。テストを修正するたびに、ほかのテストを壊していないことを再度確認するために全体のテストを実行することになります。ただし通らないテストが修正されないかぎり、ほかのすべてのテストを再実行するのは無意味です。このプロセスを速くするために、`test:all` タスクには以前の実行で通らなかったテストのみを再実行するように強制する `--only-failed` (ショートカットは `-f`) オプションがあります:

    $ php symfony test:all --only-failed

最初の実行では、すべてのテストがいつものとおりに実行されます。しかし次回以降のテストでは、最後に通らなかったテストのみが実行されます。コードを修正するにつれて、テストの一部が通り、次回以降の実行から除外されます。すべてのテストが再び通るとき、フルテストスイートが実行され、きれいにすることができます。
