view.yml 設定ファイル
====================

ビューレイヤーのコンフィギュレーションは `view.yml` 設定ファイルを編集することで変更できます。

[設定ファイルの原則の章](#chapter_03)で説明したように、`view.yml` ファイルでは**コンフィギュレーションカスケードのメカニズム**がはたらき、**定数**を収めることができます。

>**CAUTION**
>ほとんどの場合アクションから呼び出されるテンプレートもしくはメソッドで直接使われるヘルパーを尊重するためこの設定ファイルの変更は非推奨です。

`view.yml` 設定ファイルにはビュー設定のリストが収められています:

    [yml]
    VIEW_NAME_1:
      # コンフィギュレーション

    VIEW_NAME_2:
      # コンフィギュレーション

    # ...

>**NOTE**
>`view.yml` 設定ファイルは PHP ファイルとしてキャッシュされます。処理は `sfViewConfigHandler` [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

`layout`
--------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      has_layout: true
      layout:     layout

`view.yml` 設定ファイルはアプリケーションによって使われるデフォルトの ~`layout`~ を定義します。デフォルトでは名前は `layout` で、symfony はアプリケーションの `templates/` ディレクトリに存在する `layout.php` ファイルのなかですべてのページをデコレートします。~`has_layout`~ エントリを `false` にセットすることでデコレーション処理を完全に無効にすることもできます。

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
>`view.yml` で定義されるスタイルシートのインクルードは `include_stylesheets()` ヘルパーによる手動で行います。

複数のファイルが定義される場合、symfony は定義と同じ順序でこれらのファイルをインクルードします:

    [yml]
    stylesheets: [main.css, foo.css, bar.css]

`media` 属性を変更もしくは `.css` 拡張子を省略することもできます:

    [yml]
    stylesheets: [main, foo.css, bar.css, print.css: { media: print }]

`use_stylesheet()` ヘルパーを尊重するためにこの設定は*非推奨*です:

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
>`view.yml` で定義される JavaScript ファイルのインクルードは `include_javascripts()` ヘルパーによる手動で行います。

複数のファイルが定義される場合、symfony は定義と同じ順序でこれらのファイルをインクルードします:

    [yml]
    javascripts: [foo.js, bar.js]

`.js` 拡張子を省略することもできます:

    [yml]
    javascripts: [foo, bar]

`use_javascript()` ヘルパーを尊重するためにこの設定は*非推奨*です:

    [php]
    <?php use_javascript('foo.js') ?>

>**NOTE**
>`foo.js` のような相対パスを使うとき、symfony はこれらのパスの前に `/js/` をつけます。

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

`http_metas` と `metas` 設定によってレイアウトのなかでインクルードするメタタグを定義できます。

>**NOTE**
>`view.yml` で定義されるメタタグを手動でインクルードするには `include_metas()` と `include_http_metas()` ヘルパーを使います。

静的なメタ情報 (Content-Type など) のためのレイアウトの HTML を純粋に保つ、もしくは動的なメタ情報 (タイトルや説明) のスロットを尊重するためにこれらの設定は*非推奨*です。

>**TIP**
>設定が反映されるとき、[`settings.yml` 設定ファイル](#chapter_04_sub_charset)で定義される文字集合をインクルードするために HTTP の `Content-Type` のメタ情報は自動的に修正されます。
