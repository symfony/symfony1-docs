view.yml 設定ファイル
====================

ビューレイヤーのコンフィギュレーションは `view.yml` 設定ファイルを編集することで変更できます。

[第3章](#chapter_03)で説明したように、`view.yml` ファイルでは**コンフィギュレーションカスケードのメカニズム**がはたらき、**定数**を収めることができます。

>**CAUTION**
>ほとんどの場合アクションから呼び出されるテンプレートもしくはメソッドで直接使われるヘルパーのためにこの設定ファイルの変更は非推奨です。

`view.yml` 設定ファイルはビュー設定のリストを収めることができます:

    [yml]
    VIEW_NAME_1:
      # コンフィギュレーション

    VIEW_NAME_2:
      # コンフィギュレーション

    # ...

>**NOTE**
>`view.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は `sfViewConfigHandler` [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

`layout`
--------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      has_layout: on
      layout:     layout

`view.yml` 設定ファイルはアプリケーションによって使われるデフォルトの ~`layout`~ を定義します。デフォルトでは、名前は `layout` で、symfony はアプリケーションの `templates/` ディレクトリにある `layout.php` ファイルですべてのページをデコレートします。~`has_layout`~ エントリを `false` にセットすることでデコレーション処理を一緒に無効にすることもできます。

>**TIP**
>`view` に対して明示的にセットされていないかぎり、`layout` は XML、HTTP リクエストと非 HTML の Content-Type に対して自動的に無効になります。

`stylesheets`
-------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      stylesheets: [main.css]

`stylesheets` エントリは現在のビューで使うスタイルシートの配列を定義します。

>**NOTE**
>`view.yml` で定義されているスタイルシートのインクルードは `include_stylesheets()` ヘルパーによる手動で行うかもしくは [common フィルタ](#chapter_12_common)で自動的に行われます。

複数のファイルが定義されている場合、symfony は定義と同じ順序でこれらをインクルードします:

    [yml]
    stylesheets: [main.css, foo.css, bar.css]

`media` 属性を変更もしくは `.css` 拡張子を省略することもできます:

    [yml]
    stylesheets: [main, foo.css, bar.css, print.css: { media: print }]

`use_stylesheet()` ヘルパーのためにこの設定は*非推奨*です:

    [php]
    <?php use_stylesheet('main.css') ?>

>**NOTE**
>デフォルトの `view.yml` 設定ファイルでは、参照されるファイルは `main.css` であり `/css/main.css` ではありません。実際のところ、symfony は相対パスの前に `/css/` をつけるので、両方の定義は同等です。

`javascripts`
-------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      javascripts: []

`javascripts` エントリは現在のビューに使う JavaScript ファイルの配列を定義します。

>**NOTE**
>`view.yml` で定義されている JavaScript ファイルのインクルードは `include_javascripts()` ヘルパーによる手動で行う、もしくは  [common フィルタ](#chapter_12_common)で自動的に行われます。

複数のファイルが定義されている場合、symfony は定義と同じ順序でこれらをインクルードします:

    [yml]
    javascripts: [foo.js, bar.js]

`.js` 拡張子を省略することもできます:

    [yml]
    javascripts: [foo, bar]

`use_javascript()` ヘルパーのためにこの設定は*非推奨*です:

    [php]
    <?php use_javascript('foo.js') ?>

>**NOTE**
>`foo.js` のような相対パスを使うとき、symfony はこれらの前に `/js/` をつけます。

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

`http_metas` と `metas` 設定でレイアウトでインクルードするメタタグの定義を可能にします。

>**NOTE**
>`view.yml` で定義されているメタタグのインクルードは `include_metas()` と `include_http_metas()` ヘルパーで手動で行うことができます。

静的なメタ情報 (Content-Type など) のためのレイアウトの HTML を純粋に保つ、もしくは動的なメタ情報 (タイトルや説明) のスロットのためにこれらの設定は*非推奨*です。

>**TIP**
>効果があるとき、[`settings.yml` 設定ファイル](#chapter_04_charset)で定義されている文字集合をインクルードするために HTTP の `Content-Type` のメタ情報は自動的に修正されます。
