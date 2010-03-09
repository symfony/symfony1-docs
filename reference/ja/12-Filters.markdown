filters.yml 設定ファイル
========================

~`filters.yml`~ 設定ファイルはすべてのリクエストで実行されるフィルタチェーンを記述します。

アプリケーションのメインの `filters.yml` 設定ファイルは `apps/APP_NAME/config/` ディレクトリで見つかります。

第 3 章で説明したように、`filters.yml` ファイルでは[**コンフィギュレーションカスケードのメカニズム**](#chapter_03)がはたらき、[**定数**](#chapter_03)が収められます。

`filters.yml` 設定ファイルは名前つきフィルタ定義のリストを収めることができます:

    [yml]
    FILTER_1:
      # definition of filter 1

    FILTER_2:
      # definition of filter 2

    # ...

コントローラがリクエストに対応してフィルタを初期化するとき、`filters.yml` ファイルを読み込みフィルタオブジェクトを設定するために使われるフィルタ (`class`) とパラメータ (`param`) のクラス名を探すことでフィルタを登録します:

    [yml]
    FILTER_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

フィルタは設定ファイルに記載されている順序で実行されます。symfony は複数のフィルタを1つのチェーンとして実行するので、最初に登録されたフィルタは最初と最後に実行されます。

`class` クラスは `sfFilter` 基底クラスを継承します。

フィルタクラスがオートロードされない場合、`file` パスを定義することが可能でフィルタオブジェクトが作られる前に自動的にインクルードされます:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

`filters.yml` ファイルをオーバーライドするとき、継承する設定ファイルからのすべてのフィルタを維持しなければなりません:

    [yml]
    rendering: ~
    security:  ~
    cache:     ~
    common:    ~
    execution: ~

フィルタを除外するには、`enabled` キーを `false` にセットすることでこれを無効にする必要があります:

    [yml]
    FACTORY_NAME:
      enabled: false

2つの特別な名前のフィルタ: `rendering` と `execution` があります。これらは両方とも必須で `type` パラメータで指定されます。`rendering` フィルタはつねに最初に登録されフィルタリングされ `execution` フィルタは最後になります:

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
>`filters.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は ~`sfFilterConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

<div class="pagebreak"></div>

フィルタ
----------

 * [`rendering`](#chapter_12_rendering)
 * [`security`](#chapter_12_security)
 * [`cache`](#chapter_12_cache)
 * [`execution`](#chapter_12_execution)

`rendering`
-----------

*デフォルトコンフィギュレーション*:

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

`rendering` フィルタはブラウザへのレスポンス出力の責務を担います。これは最初に登録されるフィルタになるので、リクエストを管理する機会を持つ最後のフィルタにもなります。

`security`
----------

*デフォルトコンフィギュレーション*:

    [yml]
     security:
       class: sfBasicSecurityFilter
       param:
         type: security

`security` フィルタはアクションの `getCredential()` メソッドを呼び出すことでセキュリティをチェックします。いったんクレデンシャルが得られたら、ユーザーオブジェクトの `hasCredential()` メソッドを呼び出すことでユーザーが同じクレデンシャルを持つことを確認します。

`security` フィルタのデータ型は `security` でなければなりません。

`security` フィルタのきめ細かい設定は `security.yml` 設定[ファイル](#chapter_08)を通して行われます。

>**TIP**
>`security.yml` で必須のアクションがセキュアなものとして設定されていない場合、`security` フィルタは実行されません。

`cache`
-------

*デフォルトコンフィギュレーション*:

    [yml]
    cache:
      class: sfCacheFilter
      param:
        condition: %SF_CACHE%

`cache` フィルタはアクションとページを管理します。これは必要とされる HTTP キャッシュヘッダーをレスポンスに追加するための責務も担います (`Last-Modified`、`ETag`、`Cache-Control`、`Expires`、・・・)。

`execution`
-----------

*デフォルトコンフィギュレーション*:

    [yml]
    execution:
      class:  sfExecutionFilter
      param:
        type: execution

`execution` フィルタはフィルタチェーンのまんなかにあり、すべてのアクションとビューの実行を行います。

`execution` フィルタは最後に登録されるフィルタになります。
