routing.yml 設定ファイル
=======================

`routing.yml` 設定ファイルはルートの定義を可能にします。

アプリケーションのメインの `routing.yml` 設定ファイルは `apps/APP_NAME/config/` ディレクトリで見つかります。

`routing.yml` 設定ファイルは名前つきルートの定義のリストを収めます:

    [yml]
    ROUTE_1:
      # ルート1の定義

    ROUTE_2:
      # ルート2の定義

    # ...

リクエストがあると、ルーティングシステムはルートがやって来る URL にマッチするか試します。最初にマッチするルートが優先されるので、`routing.yml` 設定ファイルで定義されるルートの順序は重要です。

`routing.yml` 設定ファイルが読み込まれるとき、それぞれのルートが `class` クラスのオブジェクトに変換されます:

    [yml]
    ROUTE_NAME:
      class: CLASS_NAME
      # ルートが存在する場合のコンフィギュレーション

`class` クラスは `sfRoute` 基底クラスを継承します。クラスが提供されない場合、`sfRoute` 基底クラスがフォールバックとして使われます。

>**NOTE**
>`routing.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は `sfRoutingConfigHandler` [クラス](#chapter_14-Other-Configuration-Files_config_handlers_yml)によって自動管理されます。

<div class="pagebreak"></div>

ルートクラス
------------

 * [メインのコンフィギュレーション](#chapter_10_route_configuration)

   * [`class`](#chapter_10_sub_class)
   * [`options`](#chapter_10_sub_options)
   * [`param`](#chapter_10_sub_param)
   * [`params`](#chapter_10_sub_params)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`type`](#chapter_10_sub_type)
   * [`url`](#chapter_10_sub_url)

 * [`sfRoute`](#chapter_10_sfroute)
 * [`sfRequestRoute`](#chapter_10_sfrequestroute)

   * [`sf_method`](#chapter_10_sub_sf_method)

 * [`sfObjectRoute`](#chapter_10_sfobjectroute)

   * [`allow_empty`](#chapter_10_sub_allow_empty)
   * [`convert`](#chapter_10_sub_convert)
   * [`method`](#chapter_10_sub_method)
   * [`model`](#chapter_10_sub_model)
   * [`type`](#chapter_10_sub_type)

 * [`sfPropelRoute`](#chapter_10_sfpropelroute)

   * [`method_for_criteria`](#chapter_10_sub_method_for_criteria)

 * [`sfDoctrineRoute`](#chapter_10_sfdoctrineroute)

   * [`method_for_query`](#chapter_10_sub_method_for_query)

 * [`sfRouteCollection`](#chapter_10_sfroutecollection)
 * [`sfObjectRouteCollection`](#chapter_10_sfobjectroutecollection)

   * [`actions`](#chapter_10_sub_actions)
   * [`collection_actions`](#chapter_10_sub_collection_actions)
   * [`column`](#chapter_10_sub_column)
   * [`model`](#chapter_10_sub_model)
   * [`model_methods`](#chapter_10_sub_model_methods)
   * [`module`](#chapter_10_sub_module)
   * [`object_actions`](#chapter_10_sub_object_actions)
   * [`prefix_path`](#chapter_10_sub_prefix_path)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`route_class`](#chapter_10_sub_route_class)
   * [`segment_names`](#chapter_10_sub_segment_names)
   * [`with_show`](#chapter_10_sub_with_show)
   * [`with_wildcard_routes`](#chapter_10_sub_with_wildcard_routes)

 * [`sfPropelRouteCollection`](#chapter_10_sfpropelroutecollection)
 * [`sfDoctrineRouteCollection`](#chapter_10_sfdoctrineroutecollection)

<div class="pagebreak"></div>

ルートのコンフィギュレーション
-------------------------------

`routing.yml` 設定ファイルはルートを細かく設定するための複数のコンフィギュレーションをサポートします。これらの設定項目はそれぞれのルートをオブジェクトに変換するために `sfRoutingConfigHandler` クラスによって使われます。

### ~`class`~

*デフォルト値*: `sfRoute` (もしくは `type` が `collection` である場合 `sfRouteCollection`、下記を参照)

ルートに使うルートクラスは `class` 設定によって変更できます。

### ~`url`~

*デフォルト値*: `/`

`url` 設定は現在のリクエストからやって来る URL がマッチしなければならないルートのパターンです。

パターンは複数のセグメントで構成されます:

 * 変数 ([コロン `:`](#chapter_05_sub_variable_prefixes) を接頭辞とする単語)
 * 定数
 * キーと値のペアのシーケンスにマッチするワイルドカード (`*`)

それぞれのセグメントはあらかじめ定義される区切り文字の1つで区切らなければなりません ([デフォルトでは `/` もしくは `.`](#chapter_05_sub_segment_separators))。

### ~`params`~

*デフォルト値*: 空の配列

`params` 設定はルートに関連するパラメータの配列を定義します。これらは `url` に収められる変数、もしくはこのルートに関連する変数のデフォルト値になります。

### ~`param`~

*デフォルト値*: 空の配列

この設定は `params` 設定と同等です。

### ~`options`~

*デフォルト値*: 空の配列

`options` 設定はふるまいを細かくカスタマイズするためにルートオブジェクトに渡すオプションの配列です。次のセクションでそれぞれのルートクラスで利用可能なオプションを説明します。

### ~`requirements`~

*デフォルト値*: 空の配列

`requirements` 設定は `url` 変数によって満たさなければならないルート要件の配列です。キーは `url` 変数で値はこの変数がマッチしなければならない正規表現です。

>**TIP**
>正規表現は別の正規表現に含まれるので、区切り文字でこれらを囲んだり、値全体がマッチするように `^` もしくは `$` をつける必要はありません。

### ~`type`~

*デフォルト値*: `null`

`collection` にセットされている場合、ルートはルートコレクションとして読み込まれます。

>**NOTE**
>`class` の名前に単語の `Collection` が含まれる場合、この設定はコンフィギュレーションハンドラによって自動的に `collection` にセットされます。このことはたいていの場合この設定を使う必要のないことを意味します。

~`sfRoute`~
-----------

すべてのルートクラスは `sfRoute` 基底クラスを継承します。この基底クラスは必須のルート設定を提供します。

~`sfRequestRoute`~
------------------

### ~`sf_method`~

*デフォルト値*: `get`

`sf_method` オプションは `requirements` 配列で使われます。これはルートのマッチング処理のなかで HTTP リクエストを強制します。

~`sfObjectRoute`~
-----------------

`sfObjectRoute` の次のすべてのオプションは `routing.yml` 設定ファイルの `options` 設定内部に存在しなければなりません。

### ~`model`~

`model` オプションは必須であり現在のルートに関連するモデルクラスの名前です。

### ~`type`~

`type` オプションは必須でありモデルに必要なルートの種類です。これは `object` もしくは `list` のどちらかになります。`object` 型のルートは単独のモデルオブジェクトを表し、`list` 型のルートはモデルオブジェクトのコレクションを表します。

### ~`method`~

`method` オプションは必須です。このオプションはこのルートに関連するオブジェクトを検索するためにモデルクラスで呼び出すメソッドです。これはスタティックなメソッドでなければなりません。このメソッドは引数として解析されるルートのパラメータで呼び出されます。

### ~`allow_empty`~

*デフォルト値*: `true`

`allow_empty` オプションが `false` にセットされている場合、`model` の `method` 呼び出しによってオブジェクトが返されないときにルートは404の例外を投げます。

### ~`convert`~

*デフォルト値*: `toParams`

`convert` オプションはモデルオブジェクトにもとづいてルートを生成する際にモデルを適切なパラメータの配列に変換するために呼び出すメソッドです。これは少なくともルートパターンの必須パラメータを持つ配列を返さなければなりません (`url` 設定で定義される)。

~`sfPropelRoute`~
-----------------

### ~`method_for_criteria`~

*デフォルト値*: コレクションには `doSelect`、単独のオブジェクトには `doSelectOne`

`method_for_criteria` オプションは現在のリクエストに関連するオブジェクトを検索するために対のクラスで呼び出されるメソッドを定義します。メソッドは引数として解析されるルートのパラメータで呼び出されます。

~`sfDoctrineRoute`~
-------------------

### ~`method_for_query`~

*デフォルト値*: none

`method_for_query` オプションは現在のリクエストに関連するオブジェクトを検索するためにモデルを呼び出すメソッドを定義します。現在のクエリオブジェクトは引数として渡されます。

このオプションがセットされていない場合、クエリは `execute()` メソッドで「実行」されるだけです。

~`sfRouteCollection`~
---------------------

`sfRouteCollection` 基底クラスはルートのコレクションを表します。

~`sfObjectRouteCollection`~
---------------------------

### ~`model`~

`model` オプションは現在のルートに関連するモデルクラスの名前で必須です。

### ~`actions`~

*デフォルト値*: `false`

`actions` オプションはルートに許可されるアクションの配列を定義します。アクションはすべての利用可能なアクションのサブセット: `list`、`new`、`create`、`edit`、`update`、`delete` そして `show` でなければなりません。

このオプションと `with_show` オプションの両方が `false` にセットされている場合、`show` アクション以外のすべてのアクションが利用可能になります (下記を参照)。

### ~`module`~

*デフォルト値*: ルートの名前

`module` オプションはモジュールの名前を定義します。

### ~`prefix_path`~

*デフォルト値*: ルートの名前の前につけられる `/`

`prefix_path` オプションはすべての `url` パターンの先頭につけられる接頭辞を指定します。これは任意の有効なパターンになり変数と複数のセグメントを収めることができます。

### ~`column`~

*デフォルト値*: `id`

`column` オプションはモデルオブジェクトの一意の識別子として使うモデルのカラムを定義します。

### ~`with_show`~

*デフォルト値*: `true`

`actions` オプションが `false` にセットされているときでも `show` アクションをルートに許可されるアクションのリストに入れるために `with_show` オプションが使われます。 

### ~`segment_names`~

*デフォルト値*: `array('edit' => 'edit', 'new' => 'new'),`

`segment_names` は `edit` と `new` アクションの `url` パターンで使う単語を定義します。

### ~`model_methods`~

*デフォルト値*: 空の配列

`model_methods` オプションはモデルからオブジェクトを検索するために呼び出すメソッドを定義します (`sfObjectRoute` の`method` オプションを参照)。これは実際には `list` と `object` メソッドを定義する配列です:

    [yml]
    model_methods:
      list:   getObjects
      object: getObject

### ~`requirements`~

*デフォルト値*: `column` に対して `\d+`

`requirements` オプションはルート変数に適用するルート要件の配列を定義します。

### ~`with_wildcard_routes`~

*デフォルト値*: `false`

`with_wildcard_routes` オプションは2つのワイルドカードのルート: (1つは単独のオブジェクト、もう1つはオブジェクトコレクション) を通してアクションにアクセスできるようにします。

### ~`route_class`~

*デフォルト値*: `sfObjectRoute`

`route_class` オプションはコレクションに使われるデフォルトのルートオブジェクトをオーバーライドします。

### ~`collection_actions`~

*デフォルト値*: 空の配列

`collection_actions` オプションはコレクションルートで利用可能な追加アクションの配列を定義します。

### ~`object_actions`~

*デフォルト値*: 空の配列

`object_actions` オプションはオブジェクトルートで利用可能な追加アクションの配列を定義します。

    [yml]
    articles:
      options:
        object_actions: { publish: put }
        # ...

~`sfPropelRouteCollection`~
---------------------------

`sfPropelRouteCollection` ルートクラスは `sfRouteCollection` を継承し、デフォルトのルートクラスを `sfPropelRoute` に変更します (上記の `route_class` オプションを参照)。

~`sfDoctrineRouteCollection`~
-----------------------------

`sfDoctrineRouteCollection` ルートクラスは `sfRouteCollection` を継承し、デフォルトのルートクラスを `sfDoctrineRoute` に変更します (上記の `route_class` オプションを参照)。
