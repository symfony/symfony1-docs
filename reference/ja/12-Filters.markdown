filters.yml 設定ファイル
========================

すべてのリクエストで実行されるフィルタチェーンは ~`filters.yml`~ ファイルに記述します。

アプリケーションの `filters.yml` ファイルは `apps/APP_NAME/config/` ディレクトリに配置されています。

[設定ファイルの原則の章](#chapter_03)で述べたように、`filters.yml` ファイルでは、**コンフィギュレーションカスケード**のメカニズムがはたらいており、**定数**を定義することができます。

名前つきフィルタの定義リストが `filters.yml` ファイルに用意されています。

    [yml]
    FILTER_1:
      # フィルタ1の定義

    FILTER_2:
      # フィルタ2の定義

    # ...

コントローラがリクエストに応じてフィルタチェーンを初期化するとき、`filters.yml` ファイルが読み込まれ、フィルタのクラス名 (`class`) とフィルタオブジェクトを初期化する際に使われるパラメータ (`param`) が探索され、フィルタが登録されます。

    [yml]
    FILTER_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

フィルタは設定ファイルで定義されている順番で実行されます。複数のフィルタは1つのチェーンとして実行されるので、最初に登録されるフィルタは最初と最後に実行されます。

`class` クラスは `sfFilter` 基底クラスを継承しなければなりません。

フィルタクラスがオートロードされていなければ、`file` パスが定義され、フィルタオブジェクトが作られる前に自動的にインクルードされます。

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

`filters.yml` ファイルをオーバーライドする場合、継承するファイルからすべてのフィルタを守らなければなりません。

    [yml]
    rendering: ~
    security:  ~
    cache:     ~
    execution: ~

フィルタを除外するには、`enabled` キーに `false` をセットして無効にする必要があります。

    [yml]
    FACTORY_NAME:
      enabled: false

特殊なフィルタが2つ用意されています (`rendering` と `execution`)。これらのフィルタは両方とも必須で、`type` パラメータで指定します。最初に登録されるのはつねに `rendering` フィルタで、最後に登録されるのは `execution` フィルタです。

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
>`filters.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は ~`sfFilterConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)にゆだねられます。

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

`rendering` フィルタはブラウザへのレスポンス出力を受け持ちます。このフィルタは最初に登録されるフィルタであり、リクエストを操作する機会をもつ最後のフィルタでもあります。

`security`
----------

*デフォルトコンフィギュレーション*:

    [yml]
     security:
       class: sfBasicSecurityFilter
       param:
         type: security

`security` フィルタはアクションの `getCredential()` メソッドを呼び出してセキュリティをチェックします。いったんクレデンシャルを得られたら、ユーザーオブジェクトの `hasCredential()` メソッドを呼び出して、ユーザーが同じクレデンシャルをもっていることを確認できます。

`security` フィルタのデータ型は `security` でなければなりません。

`security` フィルタのコンフィギュレーションをきめ細かく調整する場所は `security.yml` [ファイル](#chapter_08)です。

>**TIP**
>`security.yml` ファイルのなかで必須のアクションにアクセス制限がかけられていない場合、`security` フィルタは実行されません。

`cache`
-------

*デフォルトコンフィギュレーション*:

    [yml]
    cache:
      class: sfCacheFilter
      param:
        condition: %SF_CACHE%

`cache` フィルタはアクションとページを管理します。このフィルタは必要な HTTP キャッシュヘッダーをレスポンスに追加する仕事も受け持ちます (`Last-Modified`、`ETag`、`Cache-Control`、`Expires`、・・・)。

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
