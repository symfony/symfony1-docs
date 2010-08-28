第3章 - symfony を動かす
========================

前回の章で、symfony が PHP で書かれたファイルの集まりであることを学びました。symfony はこれらのファイルを使うので、symfony をインストールすることは、これらのファイルを手に入れてプロジェクトのために利用できるようになることを意味します。

symfony は少なくとも PHP 5.2.4 が必要です。PHP 5 がインストールされているか確認するにはコマンドラインを開きつぎのコマンドを入力します:

    $ php -v

    PHP 5.3.1 (cli) (built: Jan  6 2010 20:54:10) 
    Copyright (c) 1997-2009 The PHP Group
    Zend Engine v2.3.0, Copyright (c) 1998-2009 Zend Technologies

バージョンが 5.2.4 かそれ以降であるならば、この章で説明されているように、あなたはインストールする準備ができています。

前提条件
----

symfony をインストールする前に、あなたがインストールしようとしているコンピューターが正しく設定されているかを確認する必要があります。
今後あなたが余計なことに時間を掛けなくてもすむように、注意深くこの章を読んで設定を確認するために必要な全ての手順に従うようにしてください。

### サードパーティーのソフトウェア

まず最初に、あなたのコンピューターが Webアプリケーションを開発するための環境になっているかどうかを確認する必要があります。
少なくとも、（Apacheなどの） Webサーバー、（MySQL、 PostgreSQL、 SQLite、 もしくは[PDO](http://www.php.net/PDO)と互換性のあるデータベースエンジン）データベースエンジン、そしてPHP 5.2.4 またはそれ以降のバージョンのものが必要になります。

### コマンドライン インターフェース

symfony フレームワークにはあなたの代わりに多くの作業を自動で行ってくれるコマンドラインツールが用意されています。
もしあなたが Unix 系の OS を使用しているユーザーであれば、気楽に感じることでしょう。
もしあなたが Windows を使用しているとしても `cmd` プロンプトでコマンドを入力するだけでコマンドラインツールは正しく動作します。

>**Note**
>UnixのシェルコマンドはWindows環境ではほとんど動作しません。
>もし `tar`、 `gzip` もしくは `grep` コマンドを Windows 環境のコマンドを使用したいのであれば、[Cygwin](http://cygwin.com/)をインストールする必要があります。
>危険をかえり見ない勇気がある人は マイクロソフト の[Unix のための Windows サービス](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx)を試してみてください。

### PHP の設定

PHP の設定は OS が違うだけでも異なりますし、Linux のディストリビューション間でさえも異なるため、 symfony のために最低限の PHP の設定を確認します。

まず最初に、 コマンドラインで `php -v` を実行するか組み込み関数である `phpinfo()` を使って PHP 5.2.4 がインストールされているかを確認します。
2つのバージョンの PHP がインストールされているということが設定から気づくでしょう: 1つはコマンドライン用のPHPであり、もう1つはWeb用のPHPです。

そして、symfony 設定チェッカーのスクリプトを以下のURLからダウンロードします:

    http://sf-to.org/1.4/check.php

スクリプトを Web サーバーのルートディレクトリより下の階層に保存しておきます。

設定チェッカーのスクリプトをコマンドラインから起動させます:

    $ php check_configuration.php

PHP の設定に問題がある場合は、コマンドを実行することで何が問題でどう直せばよいかについてのヒントが出力されます。

このチェッカーのスクリプトはブラウザーからも実行し、もし問題があれば修正しなければなりません。
というのも、PHP は`php.ini` 設定ファイルをこれら2つの環境で別の設定ができるようにそれぞれで用意することができるからです。

>**NOTE**
>確認が終わった後に Web のルートディレクトリからこのスクリプトのファイルを削除するのを忘れないようにしてください

>**NOTE**
>あなたがやりたいことが数時間で symfony を試してみるということであれば、この章の最後で説明している symfony のサンドボックスをインストールしてみてください。
>そうではなく、実際のプロジェクトで symfony を使ってみようと思っていたり、もっと詳しく知りたいと思っている場合はこのまま読み進めてください。

symfony のインストール
------------------

### プロジェクトディレクトリの初期化

symfony をインストールする前に、プロジェクトに関する全てのファイルを管理するディレクトリを作成しておく必要があります:

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

もしくは Windows をお使いの場合は:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Windows ユーザーはプロジェクトを配置するパスに空白のスペースを含めないようにしてください。
>`My Documents` ももちろんのこと `Documents and Settings` ディレクトリを使わないようにしてください。

-

>**TIP**
>symfony プロジェクトディレクトリを Webのルートディレクトリ以下の階層に作る場合は、Webサーバーの設定は必要ありません。
>もちろん、運用環境のために Webサーバーの設定についての部分で説明しているようなサーバーの設定を行うことを強く推奨します。

### symfony のバージョンの選択

次に、symfony をインストールする必要があります。symfony フレームワークには複数の安定バージョンが存在するため、[インストールについてのページ](http://www.symfony-project.org/installation)を読みインストールしたいバージョンを選択する必要があります。

### symfony をインストールする場所の選択

あなたのマシンのどこへでも symfony はインストールすることができ、それぞれのプロジェクトの中に symfony を含めてしまうこともできます。
後者の方法が全体への影響が少ないためローカルにある symfony をアップグレードしたときに予期せずに動かなくなることもないのでお勧めします。
またこれは異なる symfony のバージョンをインストールしておくことができるということでもあり、必要なときに一度にアップグレードすることができます。

ベストプラクティスとして、多くの人が最初にインストールするためのディレクトリを作成し `lib/vendor` に symfony フレームワークのファイルをインストールします。

    $ mkdir -p lib/vendor

### symfony のインストール

#### アーカイブでインストール

もっとも簡単に symfony をインストールする方法はあなたが選んだバージョンの symfony のアーカイブを symfony の Webサイトからダウンロードすることです。
たとえば、インストールについてのページを開き symfony [1.4](http://www.symfony-project.org/installation/1_4)を選択するだけです。

"**Source Download**"の下に、 `.tgz` または `.zip` フォーマットのアーカイブを見つけることができます。
アーカイブをダウンロードし、何もない奇麗な `lib/vendor/` ディレクトリに置き、それを展開し、ディレクトリを `symfony` という名前に変更します:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.0.tgz
    $ mv symfony-1.4.0 symfony
    $ rm symfony-1.4.0.tgz

Windows の場合は、 zip ファイルをエクスプローラーを使って展開することができます。
ディレクトリを `symfony` に変更した後のディレクトリ構造は `c:\dev\sfproject\lib\vendor\symfony` のようになっているはずです。

### Subversion でインストール（推奨）

もし、プロジェクトで Subversion を使っているのであれば、 `svn:externals` プロパティを使って symfony を `lib/vendor/` ディレクトリに組み込むのが良いでしょう。

    $ svn pe svn:externals lib/vendor/

全てがうまくいけば、このコマンドによってあなたのお気に入りのエディターが起動し Subversion のexternalsの設定を行うことができます。

>**TIP**
>Windowsでは、 コンソールを利用せずに全てが行えるように [TortoiseSVN](http://tortoisesvn.net/) を使うことができます。

もし、あなたが保守的な方であれば（subversion のタグを利用して）特定のリリース版をプロジェクトに結びつけることができます。

    symfony http://svn.symfony-project.com/tags/RELEASE_1_4_0

(symfony の[ブログ](http://www.symfony-project.org/blog/)でアナウンスされて)新しいリリースがあったときは、新しいバージョンへ URL を変更しなければなりません。

最新版が欲しい場合は、 1.4 ブランチを使います:

    symfony http://svn.symfony-project.com/branches/1.4/

ブランチを利用することで `svn update` すればいつでも自動的にバグフィックスが取り込まれるという利点があります。

#### インストールの確認

symfony がインストールされたので、symfony のコマンドラインで symfony のバージョンを表示させ(大文字の `V` を指定)正しく動作しているかを確認します:

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Windowsの場合:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

プロジェクトを作ったあとに(プロジェクトの作り方は後ほど説明します)、このコマンドをもう一度実行すると symfony をインストールしたディレクトリのパスが表示されます。
このパスは `config/ProjectConfiguration.class.php` ファイルに保存されます。

このコマンドで確認するとき、symfony コマンドへのパスが絶対パスになります。(これはこれから説明する方法に従って行った場合であり標準ではありません)
そのため、より可読性が良いものにするために次のように変更します。

    [php]
    // config/ProjectConfiguration.class.php
    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';

こういう風に、プロジェクトをマシンや他のマシンの好きなところに移動することができ、移動させても正しく動作します。

>**TIP**
>symfony コマンドでどんなことができるか知りたい場合は、 `symfony` コマンドを入力し利用可能なオプションやタスクの一覧を表示してみてください:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Windowsの場合:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>symfony のコマンドラインは開発者にとても親しみやすいツールです。
>キャッシュを消去したり、コードを生成したりなどの日々の活動で生産性を高めてくれる多くのユーティリティーが用意されています。

プロジェクトのセットアップ
--------------

symfony では、 **プロジェクト**の中に同じデータやモデルを共有する**アプリケーション**を用意します。
ほとんど多くのプロジェクトでは、2つの異なる環境を用意することになるでしょう:それがフロントエンドとバックエンドです。

### プロジェクトの生成

`sfproject/` ディレクトリから、symfony の `generate:project` タスクを実行し実際に symfony のプロジェクトを生成してみます。

    $ php lib/vendor/symfony/data/bin/symfony generate:project PROJECT_NAME

Windowsの場合:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project PROJECT_NAME

`generate:project` タスクは標準のディレクトリの構造を生成し symfony のプロジェクトで必要なファイルを生成します。

>**NOTE**
>symfony はなぜ多くのファイルを生成するのでしょうか？
>フルスタックのフレームワークを利用する最大の利点の1つは開発の標準化を行えることです。
>symfony の標準のディレクトリとファイルの構造のおかげで、symfony の知識がある開発者は、他のどんな symfony のプロジェクトでもメンテナンスすることができます。
>ほんの数分で、彼はコードを見て、バグを直して、新しい機能を追加することができるようになります。

`generate:project` タスクは `symfony` コマンドのショートカットもプロジェクトのルートディレクトリに作成します。
そのため、コマンドを実行するために何文字も入力する必要はなくなります。

そういうわけで、これからは、フルパスを指定して symfony のプログラムを使う代わりに、 `symfony` ショートカットを使うことができます。

### データベースの設定

symfony フレークワークは全ての [PDO](http://www.php.net/PDO) をサポートしているデータベース（MySQL、 PostgreSQL、 SQLite、 Oracle、 MSSQL、 ...）をサポートしています。
PDOに加えて、symfony は2つの ORM ツールをバンドルしています:それは Propel と Doctrine です。

新しいプロジェクトを作成するとき、標準で Doctrine が利用できるようになります。 Doctrine を使われるデータベースの設定は `configure:databse` タスクを次のように実行するだけで簡単です:

    $ php symfony configure:database "mysql:host=localhost;dbname=dbname" root mYsEcret

`configure:database` タスクには3つの引数があります:それは [~PDO DSN~](http://www.php.net/manual/en/pdo.drivers.php)、ユーザー名そしてデータベースにアクセスするためのパスワードです。
もし、開発サーバーでデータベースにアクセスするためにパスワードが不要であれば、3つ目の引数を省略するだけです。

>**TIP**
>Doctrine の代わりに Propel を使いたい場合は、 `generate:project` タスクでプロジェクトを作成するときに `--orm=Propel` を追加します。
>また、もし ORM を使わない場合は `--orm=none` を渡すだけです。

### アプリケーションの作成

では、フロントエンドアプリケーションを `generate:app` タスクで作成します:

    $ php symfony generate:app frontend

>**TIP**
>symfony コマンドへのショートカットファイルが実行されるので、Unix ユーザーは以降の全ての `php symfony` を `./symfony` に置き換えることができます。
>
>Windows ユーザーは `symfony.bat` ファイルをあなたのプロジェクトにコピーすることで `php symfony` の代わりに `symfony` を使うことができます。
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

*引数*で渡したアプリケーション名に基づき、`generate:app` タスクは `apps/frontend/` ディレクトリ以下にアプリケーションのために必要な標準のディレクトリ構造を作成します。

>**SIDEBAR**
>セキュリティー
>
>標準で、`generate:app` タスクはWeb上で広まっている脆弱製2つの脆弱製からアプリケーションを守ります。
>そうです。symfony は自動で私たちに代わり ~セキュリティー~ 対策を行ってくれるのです。
>
> ~XSS~攻撃を防ぐために、出力時のエスケープ処理機能が有効になっています。
>そして、~CSRF~攻撃を防ぐために、ランダムなCSRFのための秘密鍵が生成されます。
>
>もちろん、これらの設定は次の*オプション*で微調整することができます。
>
>
>  * `--escaping-strategy`: 出力時のエスケープ処理機能の有効化/無効化
>  * `--csrf-secret`: フォームでのセッショントークンの有効化
>
>もし[XSS](http://en.wikipedia.org/wiki/Cross-site_scripting)や[CSRF](http://en.wikipedia.org/wiki/CSRF)について何も知らない場合は、これらのセキュリティの脆弱製について時間をかけて学習してください。

### ディレクトリ構造を正しくする

新しい作成されたプロジェクトにアクセスしてみる前に、Web サーバーとコマンドラインからでも正しく動作するように `cache/` と `log/` ディレクトリのパーミッションを正しく設定する必要があります。

    $ symfony project:permissions

>**SIDEBAR**
>SCM ツールを使っている方へのTips
>
>symfony はプロジェクトの2つのディレクトリに対してのみ書き込みを行おうとします。それが `cache/` と `log/` です。
>これらのディレクトリの内容は標準で SCM からは除外されるべきです。
>(たとえば Subversion を使用している場合は `svn:ignore` プロパティで設定が行えます)

Webサーバーの設定
-----------

### 安全でない設定方法

前章で、プロジェクトのために新しくディレクトリを作成しました。
もし、Webサーバーのドキュメントルート以下の階層にディレクトリを作成したとすると、誰もが Webブラウザーでプロジェクトのファイルにアクセスできてしまいます。

もちろん、特別な設定作業が必要ないので、セットアップはとても早く終えることができますが、Webブラウザーで `config/databases.yml` にアクセスしてみることで早く終えただけできちんと設定作業を行っていないという悪い結果を見ることになるでしょう。
もし、誰かがあなたのサイトが symfony で開発されているということを知っていたら、多くの見せたくないファイルにアクセスすることができてしまうでしょう。

**このような設定は本番運用環境では決して行わないでください**、そして次のセクションを読み Webサーバーを適切に設定する方法を学んでください。

### 安全な設定方法

グッドプラクティスは Webブラウザーからアクセスさえるファイルのみ Webのルートディレクトリ以下の階層に置くことです。
たとえばスタイルシート、 JavaScriptのファイルや画像のファイルです。
標準のsymfonyでは、symfony のプロジェクトディレクトリにある `web/` ディレクトリにこれらのファイルを置くことを強く推奨しています。

このディレクトリを見てみると、HTMLで使用するファイルのためのサブディレクトリ(`css/` と `images/`)と、2つのフロントコントローラーのファイルが存在していることがわかります。
フロントコントローラーのファイルは Webのルートディレクトリ以下の階層に配置する必要がある唯一の PHPのファイルです。
他の全ての PHPファイルはブラウザから見えないところに配置することができ、セキュリティの観点からもとても優れています。

### Webサーバーの設定

次に新しいプロジェクトを世界中からアクセスできるように Apacheの設定を変更してみましょう。

`httpd.conf` ファイルを開いて次の設定をファイルの最後に追加します。

	# Be sure to only have this line once in your configuration
	NameVirtualHost 127.0.0.1:8080

	# This is the configuration for your project
	Listen 127.0.0.1:8080

	<VirtualHost 127.0.0.1:8080>
	  DocumentRoot "/home/sfproject/web"
	  DirectoryIndex index.php
	  <Directory "/home/sfproject/web">
	    AllowOverride All
	    Allow from All
	  </Directory>

	  Alias /sf /home/sfproject/lib/vendor/symfony/data/web/sf
	  <Directory "/home/sfproject/lib/vendor/symfony/data/web/sf">
	    AllowOverride All
	    Allow from All
	  </Directory>
	</VirtualHost>

>**NOTE**
>`/sf` というエイリアスによって symfony の標準のページや Web デバッグツールバーで使用される画像やJavaScriptのファイルにアクセスすることができます。
>
>WIndows では、 `Alias` の行を次のように書き換える必要があります。
>
>     Alias /sf "c:\dev\sfproject\lib\vendor\symfony\data\web\sf"
>
>そして `/home/sfproject/web` は次のように書き換えてください:
>
>     c:\dev\sfproject\web

上記の設定によって、あなたのマシンで Apache は `8080` ポートを使うようになるため、Webサイトへは次のような URL によってアクセスすることができるようになります:

    http://localhost:8080/

`8080` のポート番号は変更することはできますが、管理者権限を必要としない `1024` 以上の数字を指定することができます。

>**SIDEBAR**
>ドメイン名の設定
>
>もしあなたがマシンのアドミニストレーター権限を持っているのであれば、新しいプロジェクトを作成するたびに新しいポートを追加するよりは
>バーチャルホストを使用して設定するほうがより良いでしょう。
> `Listen` の記述を追加する代わりに、ドメイン名(たとえば `.localhost`を最後に追加した本当に存在するドメイン名など)を `ServerName` に追加します。
>
>     # This is the configuration for your project
>     <VirtualHost 127.0.0.1:80>
>       ServerName www.myproject.com.localhost
>       <!-- same configuration as before -->
>     </VirtualHost>
>
>Apache の設定で使用する `www.myproject.com.localhost` というドメイン名はローカル環境でのみ定義されなければなりません。
>Linux 環境であれば、 `/etc/hosts` ファイルに記述します。
>Windows XP、 Vista または Win7 の環境であれば、該当ファイルは C:\WINDOWS\system32\drivers\etc\` にあります。
>
>次の行を追加します:
>
>    127.0.0.1 www.myproject.com.localhost

#### 新しい設定のテスト

Apache を再起動させて、 あなたが行った設定内容になるようにブラウザで `http://localhost:8080/index.php/`、または
`http://www.myproject.com.localhost/index.php/`にアクセスして確認してみましょう。

![Congratulations](http://www.symfony-project.org/images/getting-started/1_4/congratulations.png)

>**TIP**
>もし `mod_rewrite` モジュールが Apache にインストールされているなら、URL の `index.php` 部分を省略することができます。
>`web/.htaccess` ファイルに設定したリライティングのルールのおかげでこのように省略することが可能になっています。

また、開発環境（この環境という言葉の意味については時節を参照してください）でもアプリケーションにアクセスしてみましょう。
次のURLをブラウザに入力してください:

    http://www.myproject.com.localhost/frontend_dev.php/

ブラウザの右上に Webデバッグツールバーが表示され、 `sf/` のエイリアス設定が正しく行われていれば symfony の小さいアイコンが表示されているはずです。

![web debug toolbar](http://www.symfony-project.org/images/getting-started/1_4/web_debug_toolbar.png)

>**Note**
>Windows環境でのIIS上で symfony を動作させたいのであれば、少し設定が異なります。
>[関連する tutorial](http://www.symfony-project.org/more-with-symfony/1_4/en/11-Windows-and-Symfony)で設定方法について説明しています

Sandboxを使う
------------------

もしあなたが数時間で symfony を使ってみることを目標にしているのであれば、最も手早く symfony を使う方法について説明していきますのでこの章を読み進めてください。

最も手早く symfony を用意する方法は symfony の sandbox をインストールすることです。
sandbox は symfony のプロジェクトを前もってパッケージ化した一番簡単なインストール方法です。
sandbox には必要な設定が既に行われています。
symfony のインストールに苦労せず symfony を使って練習するためグッドプラクティスです。

>**CAUTIONS**
>sandbox ではデータベースエンジンとして SQLite を使うように設定されています。
>そのため、あなたの環境の PHP が SQLite をサポートしているか確認する必要があります。
>sandbox で使われているデータベースをどのように変更するかは `データベースの設定` の章で説明しています。

symfony の sandbox は `.tgz` または `.zip` 形式で symfony の [インストール方法についてのページ](http://www.symfony-project.org/installation/1_4)か以下のURLからダウンロードできます:

	http://www.symfony-project.org/get/sf_sandbox_1_4.tgz

	http://www.symfony-project.org/get/sf_sandbox_1_4.zip

あなたの Webのルートディレクトリ以下の階層のどこかにダウンロードしたファイルを展開します。それで終わりです。
これで、設置した sandbox の symfony には ブラウザから `web/index.php` を呼び出すことでアクセスできます。

>**CAUTION**
>Web のルートディレクトリ以下の階層に全ての symfony のファイルを置くことはローカルコンピューターでテストを行うにはちょうど良いでしょう。
>しかし、本番の運用環境でこのように配置することはエンドユーザーがアプリケーションの内部のファイルを見ることができてしまうので良くありません。

Web サーバーの設定についての章はこれで終わりです。

>**NOTE**
>sandbox はあなたのために設定を変更してあり、タスクをあらかじめ実行しているだけの通常の symfony プロジェクトにすぎません。
>そのため、新しいプロジェクトを始めるという部分において本当に簡単です。
>しかしながら、セキュリティの設定（`XSS` や `CSRF` の設定など）を変更するように、適切に設定を行う必要があるということを覚えておいてください。

まとめ
----

ローカルサーバーで symfony を試してみるためには、sandbox を使ってインストールすることが一番適しています。なぜなら、sandbox には前もって設定済みの symfony が含まれているからです。

実際にに開発したり、本番の運用環境で使用するのであれば、 アーカイブをダウンロードするか SVN でチェックアウトする方法があります。
これで symfony のライブラリがインストールされますが、プロジェクトとアプリケーションのの初期化が必要です。
セットアップの最後はサーバーの設定です。サーバーの設定には多くの方法があります。
symfonyはバーチャルホストで完全にすばらしく動作します。そのため、バーチャルホストでの設定が推奨される方法です。

インストールで何か問題があった場合は多くのチュートリアルで確認したり、 symfony の公式サイトでよくある質問に対する回答を見つけることができるでしょう。
必要があれば、 symfony コミュニティーにあなたの問題を投稿することで素早く効果的な回答を得ることができるでしょう。

インストールを行った後は、バージョン管理処理をやってみる良い機会です。

これで symfony を使うための準備ができました。これから基本的な Webアプリケーションを構築方法について見てみましょう。


