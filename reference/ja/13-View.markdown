view.yml 設定ファイル
====================

View レイヤーは `view.yml` 設定ファイルを編集することで設定できます。

はじめの章で説明したように、`view.yml` ファイルは[**コンフィギュレーションカスケードのメカニズム**](#chapter_03_configuration_cascade)が有効で、[**定数**](#chapter_03_constants)を格納することができます。

>**CAUTION**
>アクションから呼び出されるテンプレートもしくはメソッドで直接使われるヘルパーのためにたいていの場合この設定ファイルは非推奨です。

`view.yml` 設定ファイルはビュー設定のリストを格納できます:

    [yml]
    VIEW_NAME_1:
      # configuration

    VIEW_NAME_2:
      # configuration

    # ...

>**NOTE**
>`view.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は `sfViewConfigHandler` [クラス](#chapter_14-Other-Configuration-Files_config_handlers_yml)によって自動的に管理されます。

`layout`
--------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      has_layout: on
      layout:     layout

`view.yml` 設定ファイルはアプリケーションによって使われるデフォルトの ~`layout`~ を定義します。デフォルトでは、名前は `layout` で、symfony はアプリケーションの `templates/` ディレクトリで `layout.php` ファイルですべてのページをデコレートします。~`has_layout`~ エントリーを`false`にセットすることでデコレーションプロセスを一緒に無効にすることもできます。

>**TIP**
>`view` に対して明示的にセットされない限り、`layout` は XML、HTTP リクエストと非 HTML の Content-Type に対して自動的に無効にされます。

`stylesheets`
-------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      stylesheets: [main.css]

`stylesheets` エントリーは現在のビューで使うスタイルシートの配列を定義します。

>**NOTE**
>`view.yml` で定義されるスタイルシートのインクルードは `include_stylesheets()` ヘルパーによる手動もしくは [common フィルター](#chapter_12-Filters_sub_common)で自動的に行われます。

複数のフィルターが定義されている場合、symfony は定義と同じ順序でこれらをインクルードします:

    [yml]
    stylesheets: [main.css, foo.css, bar.css]

`media` 属性を変更もしくは `.css` 拡張子を省略することもできます:

    [yml]
    stylesheets: [main, foo.css, bar.css, print.css: { media: print }]

`use_stylesheet()` ヘルパーに対してこの設定は*非推奨*です:

    [php]
    <?php use_stylesheet('main.css') ?>

>**NOTE**
>デフォルトの `view.yml` 設定ファイルでは、参照されるファイルは `main.css` であり `/css/main.css` ではありません。
>当然のことながら、symfony は相対パスの前に `/css/` をつけるので、両方の定義は同等です。

`javascripts`
-------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      javascripts: []

`javascripts` エントリーは現在のビューに使う JavaScript ファイルの配列を定義します。

>**NOTE**
>`view.yml` で定義される JavaScript ファイルのインクルードは `include_javascripts()` ヘルパーで手動、もしくは  [common フィルター](#chapter_12-Filters_sub_common)で自動的に行われます。

多くのファイルが定義されている場合、symfony は定義と同じ順序でこれらをインクルードします:

    [yml]
    javascripts: [foo.js, bar.js]

`.js` 拡張子を省略することもできます:

    [yml]
    javascripts: [foo, bar]

`use_javascript()` ヘルパーのためにこの設定は*非推奨*です:

    [php]
    <?php use_javascript('foo.js') ?>

>**NOTE**
>`foo.js` のように相対パスを使うとき、symfony はこれらの前に `/js/` をつけます。

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

`http_metas` と `metas` 設定はレイアウトにインクルードするメタタグの定義を可能にします。

>**NOTE**
>`view.yml` で定義されるメタタグのインクルードは `include_metas()` と `include_http_metas()` ヘルパーで手動で行うことができます。

静的なメタ情報 (Content-Type など) に対するレイアウトの HTML を純粋に保つ、もしくは動的なメタ情報 (タイトルや説明) のためのスロットのためにこれらの設定は*非推奨*です。

>**TIP**
>効果があるとき、[`settings.yml` 設定ファイル](#chapter_04-Settings_sub_charset)で定義された文字集合をインクルードするために HTTP の `Content-Type` のメタ情報は自動的に修正されます。
