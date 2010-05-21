Windows と symfony
==================

*Laurent Bonnet 著*

概要
----

このドキュメントは新しい段階的なチュートリアルで Windows Server 2008 における symfony フレームワークのインストール、デプロイメントと機能テストをカバーします。

インターネットデプロイメントを準備するために、チュートリアルはインターネットでホストされた専用サーバー環境で実行されます。

もちろん、ローカルサーバーもしくは読者のワークステーションでのバーチャルマシンでチュートリアルを完結することは可能です。

### 新しいチュートリアルを書く理由

現在、symfony 公式サイトには Microsoft Internet Information Server (IIS) に関連する情報ソースとして [ウィキ](http://trac.symfony-project.org/wiki/symfonyOnIIS) と[クックブック](http://www.symfony-project.org/cookbook/1_2/ja/web_server_iis)の2つがあります。しかしこれらは新しいバージョンの Microsoft Windows オペレーティングシステム、とりわけ PHP 開発者が興味ある多くの変更を含む Windows Server 2008 (2008年2年リリース) は含まれていない以前のバージョンを参照しています:

 * Windows Server 2008 に組み込まれる IIS 7 は、完全なモジュラーデザインに書き直されました。

 * IIS 7 は製品の発表以降、Windows Update からのごく少数の修正が必要だっただけであり、とても信頼性のあることが証明されてきました。

 * IIS 7 には FastCGI アクセラレータや Windows OS のネイティブなスレッドモデルを利用するマルチスレッドを持つアプリケーションプールが含まれています。

 * PHP の FastCGI の実装は Windows と IIS での伝統的な ISAPI もしくは CGI の PHP のデプロイメントと比べると、実行時におけるパフォーマンスがキャッシュなしで5倍から10倍に改善されています。

 * つい最近 Microsoft は PHP 用のキャッシュアクセラレータを発表しました。執筆の時点 (2009-11-02) で製品候補です。

>**SIDEBAR**
>このチュートリアルで予定される拡張
>
>この章の補足のセクションは作業中でこの本の出版の後 symfony プロジェクトの公式サイトにリリースされます。Microsoft がまもなく改善を計画している PDO 経由の MS SQL Server への接続をカバーします。
>
>      [PHP_PDO_MSSQL]
>      extension=php_pdo_mssql.dll
>
>現在、コード実行でのベストなパフォーマンスは PHP 5 の Microsoft SQL Server のネイティブドライバによって得られます。オープンソースの Windows ドライバはバージョン1.1が入手できます。これは PHP の新しい拡張 DLL として実装されています:
>
>      [PHP_SQLSRV]
>      extension=php_sqlsrv.dll
>
>Microsoft SQL Server 2005 もしくは2008のどちらかをデータベースとして使うことができます。計画されるチュートリアルの拡張は無料で利用できるエディションの SQL Server Express もカバーします。

### 32ビットを含め、異なる Windows システムでチュートリアルを遊ぶ方法

このドキュメントは64ビット版の Windows Server 2008 に向けて書かれました。しかし、複雑な作業なしでほかのバージョンを使うことができます。

>**NOTE**
>スクリーンショットの OS の正確なバージョンは64ビット版の Windows Server 2008 Enterprise Edition Service Pack 2 です。

#### 32ビットバージョンの Windows

このチュートリアルは32ビットバージョンの Windows でも次のテキストを参考にして置き換えれば簡単に利用できます:

 * 64ビットエディション: `C:\Program Files (x86)\` と `C:\Windows\SysWOW64\`

 * 32ビットエディション: `C:\Program Files\` と `C:\Windows\System32\`

#### Enterprise 以外のエディションについて

Enterprise Edition がない場合でも、問題ではありません。このドキュメントは Windows Server のほかのエディションでも直接利用できます:

 * Windows Server 2008 Web、Standard もしくは Datacenter
 * Windows Server 2008 Service Pack 2 Web、Standard もしくは Datacenter
 * Windows Server 2008 R2 Web、Standard、Enterprise もしくは Datacenter エディション

Windows Server 2008 R2 のすべてのエディションは64ビットの OS としてのみ利用可能であることを注意してください。

#### 国際エディションについて

スクリーンショットで使われている地域の設定は `en-US` です。フランス語の国際化言語パッケージもインストールしました。

Windows クライアント OS でチュートリアルを実行することは可能です: Windows XP、Windows VistaとWindows 7 の x64 と x86 モードの両方。

### ドキュメントで使われる Web サーバー

IIS 7.0 で使われる Web サーバーは Windows Server 2008 のすべてのエディションにロールとして含まれています。十分な機能をもつ Windows Server 2008 サーバーで始めゼロから IIS をインストールします。インストールステップはデフォルトの選択を使います。IIS 7.0 に付属する2つのモジュール: **FastCGI** と **URL Rewrite** を追加するだけです。

### データベース

SQLite は symfony のサンドボックス用にあらかじめ設定されたデータベースです。Windows では、特別にインストールするものはありません: SQLite のサポートは SQLite の PDO エクステンションによって直接実装されており、PHP インストール時に一緒にインストールされます。

そういうわけで、SQLITE.EXE の個別のインスタンスをダウンロードして実行する必要はありません:

      [PHP_PDO_SQLITE]
      extension=php_pdo_sqlite.dll

### Windows Server のコンフィギュレーション

この章の段階的なスクリーンショットに合わせるために Windows Server を新たにインストールしたほうがよいです。

もちろん既存のマシンで直接動かすことができますが、インストール済のソフトウェア、ランタイムと地域のコンフィギュレーションのために困難に遭遇することがあります。

このチュートリアルと同じスクリーンショットを得るには、インターネット上で無料で入手でき、30日の期間利用できる専用の Windows Server を仮想環境で試すことをおすすめします。

>**SIDEBAR**
>無料の Windows Server トライアルを得るには？
>
>インターネットにアクセスできる専用サーバーを使うのはもちろん可能です。物理的なサーバーもしくはバーチャルの専用サーバー (VDS) でも完璧に動きます。
>
>Windows サーバーの30日トライアルは Ikoula から入手できます。このサイトは開発者とデザイナーのためのサービスの総合リストを提供しています。Microsoft Hyper-V 環境を稼働させている Windows Virtual PC のトライアルは0円から始めることができます。
>もちろん、Windows Server 2008 Web、Standard、Enterprise もしくは Datacenter エディションでも十分な機能を持つ30日トライアルの仮想マシンを得られます。
>
>そのためには、http://www.ikoula.com/flex_server にブラウザでアクセスして「Testez gratuitement」ボタンをクリックします。
>
>このドキュメントの記述と同じメッセージを得るには、Flex サーバーと一緒に頼んだ OS は: 「Windows Server 2008 Enterprise エディション64ビット」です。これは x64 ディストリビューションで、fr-FR と en-US ロケールで配布されています。Windows コントロールパネルから `fr-FR` から `en-US` に切り替えるのは簡単です。とりわけ、この設定は「Keyboards and Languages」タブに存在する「Regional and Language Options」で見つかります。「Install/uninstall languages」をクリックするだけです。

サーバーへの管理者権限が必須です。

リモートワークステーションから作業する場合、読者はリモートデスクトップサービス (以前はターミナルサーバークライアントとして知られていました) を実行しなければなりません。そして読者に管理者権限があることを確認してください。

ここで使われるディストリビューションは次のものです: Windows Server 2008 Service Pack 2

![winver コマンドでスタートアップ環境を確認する - ここでは英語](http://www.symfony-project.org/images/more-with-symfony/windows_01.png)

グラフィカル環境でインストールされた Windows Server 2008 は Windows Vista の見た目と一致します。ディストリビューションのサイズを減らすために同じサービスを持つコマンドラインのみのバージョンの Windows Server 2008 を使うこともできます (6.5GBの代わりに1.5 GB)。これは攻撃対象領域と適用する必要のあるたくさんの Windows Update パッチも減らします。

一時検査 - インターネット上の専用サーバー
-------------------------------------------

サーバーはインターネットから直接アクセスできるので、Windows ファイアウォールがアクティブプロテクションを提供していることを確認するのはよい考えです。確認する例外は次のものだけです:

 * コアネットワーキング
 * リモートデスクトップ (リモートからアクセスする場合)
 * Secure World Wide Web Services (HTTPS)
 * World Wide Web Services (HTTP)

![コントロールパネルから直接ファイアウォールの設定を確認する](http://www.symfony-project.org/images/more-with-symfony/windows_02.png)

それから、すべてのソフトウェアピースが最新の修正、パッチとドキュメントで最新の状態になっていることを確認するために一連の Windows Update を実行するのはよいことです。

![コントロールパネルから直接 Windows Update ステータスをチェックする](http://www.symfony-project.org/images/more-with-symfony/windows_03.png)

準備の最後の段階として、既存の Windows ディストリビューションもしくは IIS コンフィギュレーションでの潜在的なパラメータの衝突を削除するために、以前役割として Webサーバー を Windows Server にインストールしたのであればアンインストールすることを推奨します。

![サーバーマネージャから、Web サーバーの役割を削除する](http://www.symfony-project.org/images/more-with-symfony/windows_04.png)

PHP をインストールする - わずか数クリック
----------------------------------------

さて、IIS と PHP は1つの単純なオペレーションでインストールできます。

PHP は Windows Server 2008 の配布物の一部*ではない*ので、最初に Web PI 2.0 (Microsoft Web Platform Installer 2.0) をインストールする必要があります。

Web PI は Windows/IIS システムで PHP を実行するのに必要なすべての依存ソフトウェアのインストールの面倒を見てくれます。そういうわけで、これは IIS を Web サーバーのための最小限の役割サービスでデプロイし、PHP ランタイムの最小限のオプションも提供します。

![http://www.microsoft.com/web - ダウンロードする](http://www.symfony-project.org/images/more-with-symfony/windows_05.png)

Web PI 2.0 のインストールはコンフィギュレーションアナライザを含み、既存のモジュールを確認し、必要なモジュールのアップグレードを提案し、Microsoft Web プラットフォームのリリース前のエクステンションのベータテストも許可します。

![Web PI 2.0 - 初見](http://www.symfony-project.org/images/more-with-symfony/windows_06.png)

Web PI 2.0 は PHP 実行環境のワンクリックインストールを提供します。セレクションは PHP の「スレッドセーフではない」Win32 実装をインストールしこれは IIS 7 と FastCGI との兼ね合いでベストです。これは最新のテストされたランタイム、ここでは5.2.11も提示します。これを見つけるには、左の「Frameworks and Runtimes」タブを選ぶだけです:

![Web PI 2.0 - フレームワークとランタイムタブ](http://www.symfony-project.org/images/more-with-symfony/windows_07.png)

PHP を選んだ後で、Web PI 2.0 は IIS 7.0 の最小の役割サービスを含む、Web サーバーに保存される `.php` ページを提供するために必要なすべての依存ソフトウェアを自動的に選択します:

![Web PI 2.0 - 自動的に追加される依存ソフトウェア - 1/3.](http://www.symfony-project.org/images/more-with-symfony/windows_08.png)

![Web PI 2.0 - 自動的に追加される依存ソフトウェア - 2/3.](http://www.symfony-project.org/images/more-with-symfony/windows_09.png)

![Web PI 2.0 - 自動的に追加される依存ソフトウェア - 3/3.](http://www.symfony-project.org/images/more-with-symfony/windows_10.png)

次に、Install をクリックし、その次に「I Accept」ボタンをクリックします。IIS コンポーネントのインストールが始まり、平行して、ダウンロードされた PHP [ランタイム](http://windows.php.net)とモジュールが更新されます (たとえば IIS 7.0 の FastCGI のための更新)。

![Web PI 2.0 - IIS コンポーネントがインストールされその間に Web からダウンロードされ更新される](http://www.symfony-project.org/images/more-with-symfony/windows_11.png)

最後に、PHP セットアッププログラムが実行され、数分後に次のウィンドウが表示されます:

![Web PI 2.0 - PHPのインストールが完了](http://www.symfony-project.org/images/more-with-symfony/windows_12.png)

「Finish」をクリックします。

Windows Server はポート80をリスニングしており応答できるようになっています。

これをブラウザで確認してみましょう:

![Firefox - IIS 7.0 はポート80でレスポンスする](http://www.symfony-project.org/images/more-with-symfony/windows_13.png)

PHP が正しくインストールされ、IIS から利用できることを確認するために、ポート80の Web サーバーがアクセスできる `C:\inetpub\wwwroot` で小さな `phpinfo.php` ファイルを作ります。

これを行う前に、Windows Explorer でファイルの正しい拡張子を見ることができるように、「登録されている拡張子は表示しない」のチェックが外されていることを確認してください。

![Windows Explorer - 登録されている拡張子は表示しないのチェックを外す](http://www.symfony-project.org/images/more-with-symfony/windows_14.png)

Windows Explorer を開き、`C:\inetpub\wwwroot` に移動します。右クリックをして「新しいテキストドキュメント」をクリックします。このファイルの名前を `phpinfo.php` に変更していつもの関数呼び出しをコピーします。


![Windows Explorer - phpinfo.php を作成する](http://www.symfony-project.org/images/more-with-symfony/windows_15.png)

次に、Web ブラウザを再度開き、サーバーのURLの末尾に `/phpinfo.php` をつけてアクセスします:

![Firefox - phpinfo.php の実行は OK](http://www.symfony-project.org/images/more-with-symfony/windows_16.png)

最後に、symfony を問題なくインストールできるか確認するために、[check_configuration.php](http://sf-to.org/1.3/check.php)をダウンロードします。

![PHP - check.php をダウンロードする場所](http://www.symfony-project.org/images/more-with-symfony/windows_17.png)

これを `phpinfo.php` と同じディレクトリ (`C:\inetpub\wwwroot`) にコピーし必要であればこれを `check_configuration.php` にリネームします。

![PHP - check_configuration.php をコピーしてリネームする](http://www.symfony-project.org/images/more-with-symfony/windows_18.png)

最後に、Web ブラウザをもう一度開き、サーバーの URL の最後に `/check_configuration.php` をつけます:

![Firefox - check_configuration.php の実行は OK](http://www.symfony-project.org/images/more-with-symfony/windows_19.png)

CLI から PHP を実行する
-----------------------

後で symfony のコマンドラインタスクを実行するためには、PHP.EXE がコマンドプロンプトからアクセス可能で正しく実行できることを確認する必要があります。

`C:\inetpub\wwwroot` でコマンドプロンプトを開き次のコマンドを打ち込みます

    PHP phpinfo.php

次のエラーメッセージが表示されます:

![PHP - MSVCR71.DLL was not found.](http://www.symfony-project.org/images/more-with-symfony/windows_20.png)

何もしなければ `MSVCR71.DLL` がないために `PHP.EXE` はハングしています。ですのでこの DLL ファイルを見つけて正しい場所にインストールしなければなりません。

この `MSVCR71.DLL` は2003の時代にさかのぼる Microsoft Visual C++ ランタイムの古いバージョンです。これは .Net Framework 1.1 再頒布可能パッケージに含まれます。

.Net Framework 1.1 再頒布可能パッケージ、[MSDN](http://msdn.microsoft.com/en-us/netframework/aa569264.aspx)でダウンロードできます。

探しているファイルは次のディレクトリにインストールされます: `C:\Windows\Microsoft.NET\Framework\v1.1.4322`

`MSVCR71.DLL`ファイルを次のディレクトリにコピーします:

 * x64 システム: `C:\windows\syswow64` ディレクトリ
 * x86 システム: `C:\windows\system32` ディレクトリ

.Net Framework 1.1 はアンインストールできます。

これで PHP.EXE 実行ファイルをコマンドラインからエラーなしで実行できます。例です:

    PHP phpinfo.php
    PHP check_configuration.php

後で、symfony コマンドのフロントエンドである `symfony.bat` (サンドボックスディストリビューション) も期待どおりのレスポンスをするのか確認します。

symfony サンドボックスのインストールと使い方
------------------------------------------

次のパラグラフは[「The Sandbox](http://www.symfony-project.org/getting-started/1_3/ja/A-Sandbox) ページ」の 「symfony を始める」から抜粋したものです: 「symfony を経験する最速の方法は symfony のサンドボックスをインストールすることです。 サンドボックスはとってもインストールが簡単であらかじめ symfony プロジェクトがパッケージになっており、すでに理にかなったデフォルトで設定されています。これは Web のベストプラクティスを尊重する適切だがわずらわしいインストール作業をしなくても symfony を使って練習するためのすばらしい方法です。」

サンドボックスは SQLite をデータベースエンジンとして使うようあらかじめ設定されています。Windows では特別にインストールするものはありません: SQLite のサポートは SQLite の PDO エクステンションで直接実装されており PHP のインストール時点で一緒にインストールされます。PHP ランタイムが Microsoft Web PI を通してインストールされたときにすでにこれは完了しています。

SQLite エクステンションが `php.ini` ファイルで正しくインストールされていることを確認します。`php.ini` は `C:\Program Files (x86)\PHP` ディレクトリのなかにあり、SQlite の PDO サポートを実装する DLL は `C:\Program Files (x86)\PHP\ext\php_pdo_sqlite.dll` です。

![PHP - php.ini 設定ファイルの位置](http://www.symfony-project.org/images/more-with-symfony/windows_21.png)

### ダウンロードし、ディレクトリを作成しすべてのファイルをコピーする

symfony のサンドボックスプロジェクトは「インストールと実行の準備ができており」、`.zip` アーカイブ形式で配布されています。

[アーカイブ](http://www.symfony-project.org/get/sf_sandbox_1_3.zip)をダウンロードし、`C:\Users\Administrator`ディレクトリのなかで読み書きできる「downloads」ディレクトリのような一時的な位置に展開します。

![サンドボックス - アーカイブをダウンロードして展開する](http://www.symfony-project.org/images/more-with-symfony/windows_22.png)

サンドボックスの最終的な設置場所として `F:\dev\sfsandbox` のようなディレクトリを作ります:

![サンドボックス - sfsandbox ディレクトリを作る](http://www.symfony-project.org/images/more-with-symfony/windows_23.png)

すべてのファイルを選択する - Windows Explorer で `Ctrl-A` - ダウンロードの位置(ソース)から、そしてこれらを `F:\dev\sfsandbox` ディレクトリへコピーします。

2599アイテムが目的のディレクトリにコピーされるのが見えます:

![サンドボックス - 2599のアイテムをコピーする](http://www.symfony-project.org/images/more-with-symfony/windows_24.png)

### テストを実行する

コマンドプロンプトを開き、`F:\dev\sfsandbox` に移動し次のコマンドを実行します:

    PHP symfony -V

これは次の文字列を返します:

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

同じコマンドプロンプトから、次のコマンドを実行します:

    SYMFONY.BAT -V

これも同じ結果を返します:

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

![サンドボックス - コマンドラインのテスト - 成功](http://www.symfony-project.org/images/more-with-symfony/windows_25.png)

### Web アプリケーションの作成

ローカルサーバーで Web アプリケーションを作るには、IIS 7 マネージャを使います。これは IIS 関連のすべての活動のための GUI  コントロールパネルです。この UI から発動されるすべてのアクションは実際には背後のコマンドラインインターフェイス経由で実行されます。

IIS マネージャコンソールはスタートメニューのプログラムの管理ツールのインターネット インフォメーション サービス (IIS) マネージャでアクセス可能です。

#### ポート80のインターフェイスを利用できないように「Default Web Site」を再設定する

symfony のサンドボックスがポート80 (HTTP) でのみ応答するようにすることを望みます。このためには、既存の「Default Web Site」のポートを8080に変更します。

![IIS マネージャ - 「Default Web Site」のバインディングを編集する](http://www.symfony-project.org/images/more-with-symfony/windows_26.png)

Windows ファイアウォールが有効な場合、「Default Web Site」に到達できるようにポート8080用の例外を作らなければならないことがあることにご注意ください。この目的のために、Windows コントロールパネルに移動し、Windows ファイアウォールを選択し、「Windows ファイアウォールによるプログラムの許可」をクリックし、「ポートの追加」をクリックします。作成の後でチェックボックスにチェックして例外を有効にします。

![Windows ファイアウォール - ポート8080の例外を作成する](http://www.symfony-project.org/images/more-with-symfony/windows_27.png)

#### サンドボックス用の新しい Web サイトを追加する

管理ツールから IIS マネージャを開きます。左のペインで、「Sites」のアイコンを選び右クリックします。ポップアップメニューから Add Web Site を選びます。サイトの名前としてたとえば「Symfony Sandbox」を、物理的なパスとして `D:\dev\sfsandbox` を入力し、ほかのフィールドはそのままにします。ダイアルボックスを見ることにあります:

![IIS マネージャ - Add Web Site.](http://www.symfony-project.org/images/more-with-symfony/windows_28.png)

OK をクリックします。小さな `x` が Web サイトのアイコンに現れる場合(ビュー/サイト機能のなか)、消すために右ペインの「Restart」をクリックするのをためらわないでください。

#### Web サイトが応答しているかチェックする

IIS マネージャから、右ペインの「Symfony Sandbox」のサイトを選択し、「Browse *.80 (http)」をクリックします。

![IISマネージャ - Browse *.80をクリックする](http://www.symfony-project.org/images/more-with-symfony/windows_29.png)

明示的なエラーが表示される場合、これは期待される動作ではありません: `HTTP Error 403.14 - Forbidden`。Web サーバーはこのディレクトリのコンテンツの一覧を表示しないように設定されています。

これはこのディレクトリの内容を表示しないように指定するデフォルトの Web サーバーのコンフィギュレーションに由来します。`D:\dev\sfsandbox` に `index.php` もしくは `index.html` のようなデフォルトのファイルが存在しないので、サーバーは正しく "Forbidden" エラーメッセージを返したのです。こわがらないでください。

![Internet Explorer - 通常のエラー](http://www.symfony-project.org/images/more-with-symfony/windows_30.png)

ブラウザの URL バーに `http://localhost` の代わりに `http://localhost/web` を入力します。デフォルトの Internet Explorer では、「Symfony Project Created」が表示されます:

![IIS マネージャ - http://localhost/web を入力して成功！](http://www.symfony-project.org/images/more-with-symfony/windows_31.png)

ところで、トップに「Intranet settings are now turned off by default. Intranet settings are less secure than Internet settings. Click for options.」という黄色のバーが見えます。このメッセージにおどろかないでください。

これを恒久的に閉じるには、黄色のバーを右クリックし、適切なオプションを選びます。

このスクリーンはデフォルトの `index.php` ページが `D:\dev\sfsandbox\web\index.php` から正しくロードされ、正しく実行され、symfony のライブラリが正しく設定されたことを裏づけします。

symfony サンドボックスで遊び始める前に最後のタスクを実行する必要があります: URL 書き換えルールをインポートすることでフロントエンドページを設定します。これらのルールは `.htaccess` ファイルとして実装され IIS マネージャで数クリックするだけでコントロールできます。

### サンドボックス: Web フロントエンドコンフィギュレーション

実際の symfony スタッフを遊び始めるためにサンドボックスアプリケーションのフロントエンドを設定する必要があります。デフォルトでは、ローカルマシン (すなわち名前が `localhost` もしくはアドレスが `127.0.0.1`) からリクエストされるときにフロントページが到達し正しく実行されます。

![Internet Explorer - frontend_dev.php pageはlocalhost から OK](http://www.symfony-project.org/images/more-with-symfony/windows_32.png)

Windows Server 2008 でサンドボックスが十分に機能するか確認するために Web デバッグパネルの「configuration」、「logs」と「timers」を調べてみましょう。 

![サンドボックスの使い方: コンフィギュレーション](http://www.symfony-project.org/images/more-with-symfony/windows_33.png)

![サンドボックスの使い方: ログ](http://www.symfony-project.org/images/more-with-symfony/windows_34.png)

![サンドボックスの使い方: タイマー](http://www.symfony-project.org/images/more-with-symfony/windows_35.png)

インターネットもしくはリモート IP アドレスからサンドボックスアプリケーションをリクエストしたい気がしますが、サンドボックスはローカルマシンで symfony フレームワークを学ぶために設計されたツールです。ですので、最後のセクションでリモートアクセスに関する詳細な内容をカバーします。

新しい symfony プロジェクトの作成
--------------------------------

実際の開発目的のために symfony プロジェクト環境を作る作業はサンドボックスのインストールのように直感的です。サンドボックスのインストールとデプロイメントと同じように、簡略化された手続きでインストールプロセス全体を見ることになります。

違いはこの「project」セクションでは、インターネットのどこからでも動くように Web アプリケーションのコンフィギュレーションに焦点を合わせることです。

サンドボックスのように、symfony のプロジェクトは SQLite をデータベースエンジンとして使うようあらかじめ設定されています。これはこの章の前のほうでインストールされ設定されました。

### ダウンロードし、ディレクトリを作成しファイルをコピーする

symfony のそれぞれのバージョンは .zip ファイルでダウンロードでき最初からプロジェクトを作成するために使われます。

[symfony 公式サイト](http://www.symfony-project.org/get/symfony-1.3.0.zip)からライブラリを含むアーカイブをダウンロードします。次に、含まれるディレクトリを "downloads" ディレクトリのような一時的な場所に展開します。

![Windows Explorer - プロジェクトのアーカイブをダウンロードして展開する](http://www.symfony-project.org/images/more-with-symfony/windows_37.png)

プロジェクトの最終的な場所のディレクトリツリーを作る必要があります。これはサンドボックスよりも少しややこしいです。

### ディレクトリツリーのセットアップ

プロジェクトのディレクトリツリーを作りましょう。たとえば `D:` などのボリュームルートから始めます。

`D:` で `\dev` ディレクトリを作り、そこで `sfproject` という名前の別のディレクトリを作ります:

    D:
    MD dev
    CD dev
    MD sfproject
    CD sfproject

次のディレクトリにいます: `D:\dev\sfproject`

そこから、`lib`、`vendor` と `symfony` ディレクトリを順番に作ることでサブディレクトリツリーを作ります

    MD lib
    CD lib
    MD vendor
    CD vendor
    MD symfony
    CD symfony

次のディレクトリにいます: `D:\dev\sfproject\lib\vendor\symfony`

![Windows Explorer - プロジェクトのディレクトリツリー](http://www.symfony-project.org/images/more-with-symfony/windows_38.png)

ダウンロードした位置 (ソース) からすべてのファイル (Windows Explorer では `CTRL-A`) を選び、Downloads から `D:\dev\sfproject\lib\vendor\symfony` へコピーします。3819のアイテムが目的のディレクトリにコピーされる様子を見ることになります:

![Windows Explorer - 3819のアイテムをコピーする](http://www.symfony-project.org/images/more-with-symfony/windows_39.png)

### 作成と初期化

コマンドプロンプトを開きます。`D:\dev\sfproject` ディレクトリに移動し次のコマンドを実行します:

    PHP lib\vendor\symfony\data\bin\symfony -V

これは次の文字列を返します:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

プロジェクトを初期化するには、次の PHP コマンドラインを実行するだけです:

    PHP lib\vendor\symfony\data\bin\symfony generate:project sfproject

これは `chmod 777` コマンドを含むファイルオペレーションのリストを返します:

![Windows Explorer - プロジェクトの初期化は OK](http://www.symfony-project.org/images/more-with-symfony/windows_40.png)

コマンドプロンプトのなかで、次のコマンドを実行することで symfony アプリケーションを作ります:

    PHP lib\vendor\symfony\data\bin\symfony generate:app sfapp

繰り返しますが、このコマンドは `chmod 777` コマンドを含むファイルオペレーションのリストを返します。

この点から、必要なときごとに `php lib\vendor\symfony\data\bin\symfony` を打ち込むよりも、オリジナルから`symfony.bat`ファイルをコピーします:

    copy lib\vendor\symfony\data\bin\symfony.bat

`D:\dev\sfproject` で実行する便利なコマンドがあります。

`D:\dev\sfproject` において、今や古典的なコマンドを実行します:

    symfony -V

古典的な回答が得られます:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

### Web アプリケーションの作成

次の行では「Default Web Site」を再設定する以前のステップの「サンドボックス: Web アプリケーションの作成」を読んだことを前提とします。ですのでポート80のインターフェイスが存在しません。

#### プロジェクトの新しい Web サイトを追加する

管理ツールから IIS マネージャを開きます。左ペインで「Sites」 のアイコンを選び右クリックします。ポップアップメニューから「Add Web Site」を選びます。たとえばサイトの名前として「Symfony Project」を、物理パスとして `D:\dev\sfproject` を入力しそのほかのフィールドはそのままにしておきます。次のダイアログボックスが表示されます:

![IIS マネージャ - Web サイトを追加する](http://www.symfony-project.org/images/more-with-symfony/windows_41.png)

OK をクリックします。小さな `x` が Web サイトのアイコンに現れる場合 (ビュー/サイト機能)、これを消すために「Restart」をクリックするのをためらわないでください。

#### Web サイトが応答するかチェックする

IIS マネージャから、「Symfony Project」のサイトを選択し、右のペインで「Browse *.80 (http)」をクリックします。

サンドボックスを試したときと同じエラーメッセージが得られます:

    HTTP Error 403.14 - Forbidden

Web サーバーはこのディレクトリのコンテンツを表示するように設定されていません。

ブラウザの URL バーで `http://localhost/web` を入力すると「Symfony Project Created」 ページを見ることになります。サンドボックスの初期化の結果には同じページから少しの違いがあります: 画像が存在しません:

![Internet Explorer - 作成された Symfony Project - 画像なし](http://www.symfony-project.org/images/more-with-symfony/windows_42.png)

画像は symfony の `sf` ディレクトリに設置されているにも関わらずこの時点では表示されません。`sf` という名前の仮想ディレクトリを `/web` に追加し、`D:\dev\sfproject\lib\vendor\symfony\data\web\sf` を指し示すことで、これらの画像を `/seb` ディレクトリにリンクするのは容易です。

![IIS マネージャ - symfony のバーチャルディレクトリを追加する](http://www.symfony-project.org/images/more-with-symfony/windows_43.png)

期待どおりの通常の画像つきの「symfony の初期ページ」が表示されます:

![Internet Explorer - symfony プロジェクトの初期ページ - 画像あり](http://www.symfony-project.org/images/more-with-symfony/windows_44.png)

そして最後に、symfony のアプリケーション全体が動いています。Web ブラウザから、Web アプリケーションの URL、すなわち `http://localhost/web/sfapp_dev.php` を入力します:

![Internet Explorer - localhost から sfapp_dev.php ページは OK](http://www.symfony-project.org/images/more-with-symfony/windows_45.png)

ローカルモードで1つのテストを実行してみましょう: プロジェクトが十分な機能を持つか Web デバッグパネルの「configuration」、「logs」と「timers」を確認します。

![Internet Explorer - localhost からログページは OK](http://www.symfony-project.org/images/more-with-symfony/windows_46.png)

### インターネットに対応するアプリケーションのコンフィギュレーション

symfony の一般的なプロジェクトは `http://localhost` もしくは `http://127.0.0.1` に設置される localhost サーバーからサンドボックスのようにローカルで動いています。

インターネットからアプリケーションにアクセスできるようにします。

プロジェクトのデフォルトコンフィギュレーションはアプリケーションがリモート位置から実行されるのを防止しています。にもかかわらず、実際には `index.php` と `sfapp_dev.php` ファイルの両方にアクセスするのは OK です。Web ブラウザからプロジェクトを実行してみましょう。サーバーの外部の IP アドレス (たとえば `94.125.163.150`) と専用の仮想サーバーの FQDN (たとえば `12543hpv163150.ikoula.com`) を使うことで、サーバー内部から両方のアドレスを使うこともできます。これはこれらが `127.0.0.1` にマッピングされていないからです:

![Internet Explorer - インターネットから index.php にアクセスするのは OK](http://www.symfony-project.org/images/more-with-symfony/windows_47.png)

![Internet Explorer - インターネットからの sfapp_dev.php は OK ではない](http://www.symfony-project.org/images/more-with-symfony/windows_48.png)

前に説明したように、リモート位置からの `index.php` と `sfapp_dev.php` へのアクセスは OK です。しかし、`sfapp_dev.php` の実行は失敗します。これはデフォルトで許可されないからです。これは潜在的に悪意のあるユーザーが、プロジェクトに関する慎重に扱うべき情報を含む開発環境にアクセスするのを防止します。これを動かすために `sfapp_dev.php` ファイルを編集できますが、強く非推奨です。
前に説明したように、リモート位置からの `index.php` と `sfapp_dev.php` へのアクセスは OK です。しかし、`sfapp_dev.php` の実行は失敗します。これはデフォルトで許可されないからです。これは潜在的に悪意のあるユーザーが、プロジェクトに関する慎重に扱うべき情報を含む開発環境にアクセスするのを防止します。これを動かすために `sfapp_dev.php` ファイルを編集できますが、強く非推奨です。

最後に、"hosts" ファイルを編集することで実際のドメインをシミュレートできます。

このファイルは Windows で DNS サービスをインストールする必要なしにローカルの FQDN 名前解決を実行します。DNS サーバーは Windows Server 2008 Standard、Enterprise と Datacenter エディションも含めて、Windows Server 2008 R2 のすべてのエディションで利用可能です。

Windows x64 OS では、"hosts" ファイルはデフォルトで `C:\Windows\SysWOW64\Drivers\etc` に設置されています。

`hosts` ファイルにはマシンが `localhost` を IPv4 では `127.0.0.1`、IPv6 では `::1` として解決するようにあらかじめ記述されています。

`sfwebapp.local` のようなフェイクの実際のドメイン名を追加して、ローカルで解決するようにしましょう。

![変更を hosts ファイルに適用する](http://www.symfony-project.org/images/more-with-symfony/windows_50.png)

これで symfony プロジェクトは Web サーバー内から実行される Web ブラウザセッションによって DNS なしで Web で動きます。
