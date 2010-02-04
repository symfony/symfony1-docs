symfonyのインストール
====================

### プロジェクトのディレクトリ

symfonyをインストールする前に、最初にプロジェクトに関連するすべてのファイルをホストするディレクトリを作る必要があります:

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Windowsでは次のようになります:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Windowsユーザーの方にはスペースを含まないパスでsymfonyコマンドで実行して新しいプロジェクトをセットアップすることをおすすめします。
>`My Documents`を含めて`Documents and Settings`ディレクトリを使うのは避けます。

-

>**TIP**
>Web公開ディレクトリのルートでsymfonyプロジェクトのディレクトリを作る場合、Webサーバーを設定する必要はありません。
>もちろん、運用環境に関して、Webサーバーの設定のセクションで説明されているようにWebサーバーを設定することを強くおすすめします。

### symfonyのインストール

symfonyフレームワークライブラリのファイルをホストするディレクトリを作ります:

    $ mkdir -p lib/vendor

symfonyをインストールする必要があります。
symfonyフレームワークにはいくつかの安定ブランチが存在しsymfony公式サイトの[インストールの手引きのページ](http://www.symfony-project.org/installation)を読みインストールしたいブランチを選ぶ必要があります。


たとえば[symfony 1.2](http://www.symfony-project.org/installation/1_4)など選んだバージョン用のインストールの手引きのページに進みます。

"**Download as an Archive**"セクションの下で、`.tgz`フォーマットもしくは`.zip`フォーマットでアーカイブが見つかります。
アーカイブをダウンロードし`lib/vendor/`ディレクトリの下に設置し再度展開します:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.0.tgz
    $ mv symfony-1.4.0 symfony
    $ rm symfony-1.4.0.tgz

Windows環境ではzipファイルをエクスプローラで展開できます。
ディレクトリを`symfony`にリネームすると、ディレクトリのフルパスは`c:\dev\sfproject\lib\vendor\symfony`になります。

>**TIP**
>Subversionを使う場合、プロジェクトの`lib/vendor/`にsymfonyを置き、安定ブランチのバグ修正を自動的に反映させるために`svn:externals`プロパティを使うほうがよいです:
>
>     http://svn.symfony-project.com/branches/1.4/

symfonyのバージョンを表示するコマンドを使うことでsymfonyが正しくインストールされていることを確認します(大文字の`V`に注意):

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Windowsでは次のようになります:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

>**TIP**
>このコマンドラインツールが何をできるのかご興味があれば、`symfony`を入力して利用可能なオプションとタスクの一覧を表示してみてください:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Windowsでは次のようになります:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>symfonyコマンドは開発者の最良の友です。
>キャッシュのクリア、コードの生成など日常の活動のためのこれはたくさんのユーティリティを提供します。
