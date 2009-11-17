filters.yml設定ファイル
=======================

~`filters.yml`~設定ファイルはすべてのリクエストで実行されるフィルターチェーンを記述します。

アプリケーション用のメインの`filters.yml`設定ファイルは`apps/APP_NAME/config/`ディレクトリで見つかります。

はじめの章で説明したように、`filters.yml`ファイルは[**コンフィギュレーションカスケードのメカニズム**](#chapter_03_configuration_cascade)が有効で、[**定数**](#chapter_03_constants)を格納することができます。

`filters.yml`設定ファイルは名前つきフィルター定義のリストを格納できます:

    [yml]
    FILTER_1:
      # definition of filter 1

    FILTER_2:
      # definition of filter 2

    # ...

コントローラーがリクエストに対してフィルターを初期化するとき、`filters.yml`ファイルを読み込みフィルターオブジェクトを設定するために使われるフィルター(`class`)とパラメーター(`param`)のクラス名を探すことでフィルターを登録します:

    [yml]
    FILTER_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

フィルターは設定ファイルで記述されている順序で実行されます。
symfonyは複数のフィルターを1つのチェーンとして実行するので、最初に登録されたフィルターは最初と最後に実行されます。

`class`クラスは`sfFilter`基底クラスを継承します。

フィルタークラスがオートロードできない場合、`file`パスを定義することが可能でフィルターオブジェクトが作られる前に自動的にインクルードされます:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

`filters.yml`ファイルをオーバーライドするとき、継承する設定ファイルからすべてのフィルターを維持しなければなりません:

    [yml]
    rendering: ~
    security:  ~
    cache:     ~
    common:    ~
    execution: ~

フィルターを除外するには、`enabled`キーを`false`にセットすることでこれを無効にする必要があります:

    [yml]
    FACTORY_NAME:
      enabled: false

2つの特別な名前のフィルター: `rendering`と`execution`があります。
これらは両方とも必須で`type`パラメーターで識別されます。
`rendering`フィルターは常に最初に登録されフィルタリングされ`execution`フィルターは最後のものになります:

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

    # ...

    execution:
      class:  sfExecutionFilter
      param:
        type: execution

>**NOTE**
>`filters.yml`設定ファイルはPHPファイルとしてキャッシュされます; 
>処理は~`sfFilterConfigHandler`~[クラス](#chapter_14_config_handlers_yml)によって自動的に管理されます。

<div class="pagebreak"></div>

フィルター
----------

 * [`rendering`](#chapter_12_rendering)
 * [`security`](#chapter_12_security)
 * [`cache`](#chapter_12_cache)
 * [`common`](#chapter_12_common)
 * [`execution`](#chapter_12_execution)

`rendering`
-----------

*デフォルトコンフィギュレーション*:

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

`rendering`フィルターはブラウザーへのレスポンス出力の責務を担います。
これは最初に登録されるフィルターになるので、リクエストを管理する機会を持つ最後のフィルターにもなります。

`security`
----------

*デフォルトコンフィギュレーション*:

    [yml]
     security:
       class: sfBasicSecurityFilter
       param:
         type: security

`security`フィルターはアクションの`getCredential()`メソッドを呼び出すことでセキュリティをチェックします。
いったんクレデンシャルが得られたら、ユーザーオブジェクトの`hasCredential()`メソッドを呼び出すことでユーザーが同じクレデンシャルを持つことを確認します。

`security`フィルターは`security`型を持たなければなりません。

`security`フィルターのきめ細かい設定は`security.yml`設定[ファイル](#chapter_08)を通して行われます。

>**TIP**
>必須のアクションが`security.yml`でセキュアなものとして設定されていない場合、`security`フィルターは実行されません。

`cache`
-------

*デフォルトコンフィギュレーション*:

    [yml]
    cache:
      class: sfCacheFilter
      param:
        condition: %SF_CACHE%

`cache`フィルターはアクションとページを管理します。
これは必要とされるHTTPキャッシュヘッダーをレスポンスに追加するための責務も担います(`Last-Modified`、`ETag`、`Cache-Control`、`Expires`、・・・)。

`common`
--------

*デフォルトコンフィギュレーション*:

    [yml]
    common:
      class: sfCommonFilter

`common`フィルターはJavaScriptとスタイルシートがすでにインクルードされていない場合メインのレスポンスにこれらを追加します。

>**TIP**
>レイアウトで`include_stylesheets()`と`include_javascripts()`ヘルパーを使う場合、このフィルターを安全に無効化することが可能で、小さなパフォーマンスの恩恵を受けます。

`execution`
-----------

*デフォルトコンフィギュレーション*:

    [yml]
    execution:
      class:  sfExecutionFilter
      param:
        type: execution

`execution`フィルターはフィルターチェーンの真ん中にあり、すべてのアクションとビューの実行を行います。

`execution`フィルターは最後に登録されたフィルターになります。
