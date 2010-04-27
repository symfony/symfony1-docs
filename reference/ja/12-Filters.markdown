filters.yml 設定ファイル
========================

~`filters.yml`~ 設定ファイルでは、すべてのリクエストで実行されるフィルタチェーンを記述します。

アプリケーションのメイン設定ファイルである `filters.yml` は `apps/APP_NAME/config/` ディレクトリで見つかります。

[設定ファイルの原則の章](#chapter_03)で説明したように、`filters.yml` ファイルでは、**コンフィギュレーションカスケードのメカニズム**がはたらき、**定数**を定義することができます。

`filters.yml` 設定ファイルには名前つきフィルタ定義のリストが用意されています:

    [yml]
    FILTER_1:
      # フィルタ1の定義

    FILTER_2:
      # フィルタ2の定義

    # ...

コントローラがリクエストに対応してフィルタチェーンを初期化するとき、`filters.yml` ファイルを読み込み、フィルタオブジェクトを設定するために使われるフィルタのクラス名 (`class`) とパラメータ (`param`) を探し、フィルタを登録します:

    [yml]
    FILTER_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

フィルタは設定ファイルに記載されている順序で実行されます。symfony は複数のフィルタを1つのチェーンとして実行するので、最初に登録されるフィルタは最初と最後に実行されます。

`class` クラスは `sfFilter` 基底クラスを継承します。

フィルタクラスがオートロードされない場合、`file` パスが定義され、フィルタオブジェクトが作られる前に自動的にインクルードされます:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

`filters.yml` ファイルをオーバーライドするとき、継承するファイルからすべてのフィルタを守らなければなりません:

    [yml]
    rendering: ~
    security:  ~
    cache:     ~
    execution: ~

フィルタを除外するには、`enabled` キーを `false` にセットして無効にする必要があります:

    [yml]
    FACTORY_NAME:
      enabled: false

特別な名前のフィルタが2つあります (`rendering` と `execution`)。これらのフィルタは両方とも必須で、`type` パラメータで指定します。最初に登録されるのはつねに `rendering` フィルタで、最後に登録されるのは `execution` フィルタです:

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
>`filters.yml` 設定ファイルは PHP ファイルとしてキャッシュされます。処理は ~`sfFilterConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

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

`rendering` フィルタはブラウザへのレスポンス出力の責務を担います。このフィルタは最初に登録されるフィルタであり、リクエストを管理する機会をもつ最後のフィルタでもあります。

`security`
----------

*デフォルトコンフィギュレーション*:

    [yml]
     security:
       class: sfBasicSecurityFilter
       param:
         type: security

`security` フィルタは、アクションの `getCredential()` メソッドを呼び出すことでセキュリティをチェックします。いったんクレデンシャルを得られたら、ユーザーオブジェクトの `hasCredential()` メソッドを呼び出すことで、ユーザーが同じクレデンシャルをもつことを確認できます。

`security` フィルタのデータ型は `security` でなければなりません。

`security` フィルタのコンフィギュレーションをきめ細かく調整するには、`security.yml` 設定[ファイル](#chapter_08)を通して行います。

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

`cache` フィルタはアクションとページを管理します。このフィルタは必要な HTTP キャッシュヘッダーをレスポンスに追加するための責務も担います (`Last-Modified`、`ETag`、`Cache-Control`、`Expires`、・・・)。

`execution`
-----------

*デフォルトコンフィギュレーション*:

    [yml]
    execution:
      class:  sfExecutionFilter
      param:
        type: execution

`execution` フィルタはフィルタチェーンのまんなかにあり、すべてのアクションとビューの実行を担います。

`execution` フィルタは最後に登録されるフィルタになります。
