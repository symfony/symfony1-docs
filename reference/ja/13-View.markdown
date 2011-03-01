view.yml 設定ファイル
=====================

ビューレイヤーのコンフィギュレーションは `view.yml` ファイルのなかで変更できます。

[設定ファイルの原則の章](#chapter_03)で述べたように、`view.yml` ファイルでは、**コンフィギュレーションカスケード**のメカニズムがはたらいており、**定数**を定義することができます。

>**CAUTION**
>アクションから呼び出されるテンプレートもしくはメソッドによって直接使われるヘルパーのほうが望ましいので、この設定ファイルの変更は推奨されていません。

ビュー設定のリストは `view.yml` ファイルに用意されています。

    [yml]
    VIEW_NAME_1:
      # コンフィギュレーション

    VIEW_NAME_2:
      # コンフィギュレーション

    # ...

>**NOTE**
>`view.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は `sfViewConfigHandler` [クラス](#chapter_14_config_handlers_yml)にゆだねられます。

`layout`
--------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      has_layout: true
      layout:     layout

アプリケーションによって使われるデフォルトの ~`layout`~ は `view.yml` ファイルのなかで定義されます。デフォルトの名前は `layout` で、すべてのページはアプリケーションの `templates/` ディレクトリに配置されている `layout.php` ファイルによってデコレートされます。~`has_layout`~ エントリに `false` をセットすれば、デコレーション処理を完全に無効にすることもできます。

>**TIP**
>`view` に対して `layout` が明確にセットされていないかぎり、XML、HTTP リクエストおよび HTML ではない Content-Type に対して自動的に無効になります。

`stylesheets`
-------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      stylesheets: [main.css]

`stylesheets` エントリは現在のビューで使われるスタイルシートの配列を定義します。

>**NOTE**
>`include_stylesheets()` ヘルパーを使えば、`view.yml` ファイルで定義されているスタイルシートを手動でインクルードできます。

複数のファイルが定義されている場合、定義と同じ順番でインクルードされます。

    [yml]
    stylesheets: [main.css, foo.css, bar.css]

`media` 属性を変更もしくは `.css` 拡張子を省略することもできます。

    [yml]
    stylesheets: [main, foo.css, bar.css, print.css: { media: print }]

`use_stylesheet()` ヘルパーを使うやりかたのほうが望ましいので、この設定を変更することは*推奨されていません*。

    [php]
    <?php use_stylesheet('main.css') ?>

>**NOTE**
>デフォルトの `view.yml` ファイルでは、参照されるファイルは `main.css` であり `/css/main.css` ではありません。実際のところ、symfony は相対パスの前に `/css/` をつけるので、両方の定義は同じです。

`javascripts`
-------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      javascripts: []

`javascripts` エントリは現在のビューに使う JavaScript ファイルの配列を定義します。

>**NOTE**
>`view.yml` 設定ファイルで定義されている JavaScript ファイルを手動でインクルードするには、`include_javascripts()` ヘルパーを使います。

複数のファイルが定義されている場合、定義と同じ順番でこれらのファイルがインクルードされます。

    [yml]
    javascripts: [foo.js, bar.js]

`.js` 拡張子を省略することもできます。

    [yml]
    javascripts: [foo, bar]

`use_javascript()` ヘルパーを使うやりかたのほうが望ましいので、この設定を変更することは*推奨されていません*。

    [php]
    <?php use_javascript('foo.js') ?>

>**NOTE**
>`foo.js` のような相対パスを使うと、パスの前に `/js/` がつきます。

`metas` と `http_metas`
-----------------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      http_metas:
        content-type: text/html

      metas:
        #title:        symfony project
        #description:  symfony project
        #keywords:     symfony, project
        #language:     en
        #robots:       index, follow

レイアウトのなかでインクルードするメタタグは `http_metas` と `metas` 設定によって定義できます。

>**NOTE**
>`view.yml` ファイルで定義されているメタタグを手動でインクルードするには、`include_metas()` と `include_http_metas()` ヘルパーを使います。

変化しないメタ情報 (Content-Type など) のためにレイアウトのなかで HTML を純粋に保つため、もしくは動的なメタ情報 (タイトルや説明) のスロットのほうが望ましいので、これらの設定は*推奨されていません*。

>**TIP**
>設定が反映されるとき、[`settings.yml` ファイル](#chapter_04_sub_charset)で定義されている文字集合をインクルードするために、`Content-Type` ヘッダーのメタ情報は自動的に修正されます。
