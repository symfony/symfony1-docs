routing.yml 設定ファイル
=======================

`routing.yml` 設定ファイルはルートの定義を可能にします。

アプリケーションのメイン設定ファイルである `routing.yml` は `apps/APP_NAME/config/` ディレクトリで見つかります。

`routing.yml` 設定ファイルには名前つきルート定義のリストが収められています:

    [yml]
    ROUTE_1:
      # ルート1の定義

    ROUTE_2:
      # ルート2の定義

    # ...

リクエストがあると、ルーティングシステムはやって来る URL がルートとマッチするか試します。最初にマッチするルートが優先されるので、`routing.yml` 設定ファイルで定義されるルートの順序は重要です。

`routing.yml` 設定ファイルが読み込まれるとき、それぞれのルートは `class` クラスのオブジェクトに変換されます:

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

 * メインのコンフィギュレーション

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

ルートを細かく調整できるように `routing.yml` 設定ファイルは複数のコンフィギュレーションをサポートします。これらの設定項目はそれぞれのルートをオブジェクトに変換するために `sfRoutingConfigHandler` クラスによって使われます。

### ~`class`~

*デフォルト*: `sfRoute` (もしくは `type` が `collection` である場合 `sfRouteCollection`、下記を参照)

ルートに使うルートクラスは `class` 設定によって変更できます。

### ~`url`~

*デフォルト*: `/`

`url` 設定は現在のリクエストからやって来る URL がマッチしなければならないルートのパターンです。

パターンは複数のセグメント (構成要素) から成り立ちます:

 * 変数 ([コロン `:`](#chapter_05_sub_variable_prefixes) を接頭辞とする単語)
 * 定数
 * キーと値のペアのシーケンスにマッチするワイルドカード (`*`)

それぞれのセグメントはあらかじめ定義される区切り文字の1つで区切らなければなりません ([デフォルトでは `/` もしくは `.`](#chapter_05_sub_segment_separators))。

### ~`params`~

*デフォルト*: 空の配列

`params` 設定はルートに関連するパラメータの配列を定義します。これらは `url` に収められる変数、もしくはこのルートに関連する変数のデフォルトになります。

### ~`param`~

*デフォルト*: 空の配列

この設定は `params` 設定と同等です。

### ~`options`~

*デフォルト*: 空の配列

`options` 設定はふるまいを細かくカスタマイズするためにルートオブジェクトに渡すオプションの配列です。次のセクションでそれぞれのルートクラスで利用可能なオプションを説明します。

### ~`requirements`~

*デフォルト*: 空の配列

`requirements` 設定は `url` 変数が満たさなければならないルート要件の配列です。キーは `url` 変数で値はこの変数がマッチしなければならない正規表現です。

>**TIP**
>正規表現は別の正規表現に含まれるので、区切り文字でこれらを囲んだり、値全体がマッチするようにキャレット (`^`) もしくはドル記号 (`$`) をつける必要はありません。

### ~`type`~

*デフォルト*: `null`

`collection` にセットされている場合、ルートはルートコレクションとして読み込まれます。

>**NOTE**
>`class` の名前に単語の `Collection` が含まれる場合、この設定はコンフィギュレーションハンドラによって自動的に `collection` にセットされます。このことはほとんどの場合この設定を変更する必要がないことを意味します。

~`sfRoute`~
-----------

すべてのルートクラスは `sfRoute` 基底クラスを継承します。この基底クラスは必須のルート設定を提供します。

~`sfRequestRoute`~
------------------

### ~`sf_method`~

*デフォルト*: `get`

`sf_method` オプションは `requirements` 配列で使われます。これはルートのマッチング処理のあいだに HTTP リクエストを強制します。

~`sfObjectRoute`~
-----------------

`sfObjectRoute` の次のすべてのオプションは `routing.yml` 設定ファイルの `options` 設定内部に存在しなければなりません。

### ~`model`~

`model` オプションは必須オプションであり現在のルートに関連するモデルクラスの名前です。

### ~`type`~

`type` オプションは必須オプションでありモデルに必要なルートの種類です。これは `object` もしくは `list` のどちらかになります。`object` 型のルートは単独のモデルオブジェクトを表し、`list` 型のルートはモデルオブジェクトのコレクションを表します。

### ~`method`~

`method` オプションは必須オプションです。このオプションはこのルートに関連するオブジェクトを検索するためにモデルクラスのなかで呼び出すメソッドです。これはスタティックメソッドでなければなりません。このメソッドは引数として解析されるルートのパラメータで呼び出されます。

### ~`allow_empty`~

*デフォルト*: `true`

`allow_empty` オプションが `false` にセットされている場合、`model` の `method` 呼び出しによってオブジェクトが返されないときにルートは404の例外を投げます。

### ~`convert`~

*デフォルト*: `toParams`

`convert` オプションはモデルオブジェクトにもとづいてルートを生成する際にモデルを適切なパラメータの配列に変換するために呼び出すメソッドです。このオプションは少なくともルートパターンの必須パラメータをもつ配列を返さなければなりません (`url` 設定で定義される)。

~`sfPropelRoute`~
-----------------

### ~`method_for_criteria`~

*デフォルト*: コレクションには `doSelect`、単独のオブジェクトには `doSelectOne`

`method_for_criteria` オプションは現在のリクエストに関連するオブジェクトを検索するためにピアクラスで呼び出されるメソッドを定義します。メソッドは引数として解析されるルートのパラメータで呼び出されます。

~`sfDoctrineRoute`~
-------------------

### ~`method_for_query`~

*デフォルト*: none

`method_for_query` オプションは現在のリクエストに関連するオブジェクトを検索するためにモデルを呼び出すメソッドを定義します。現在のクエリオブジェクトが引数として渡されます。

このオプションがセットされていない場合、クエリは `execute()` メソッドで「実行」されるだけです。

~`sfRouteCollection`~
---------------------

`sfRouteCollection` 基底クラスはルートのコレクションを表します。

~`sfObjectRouteCollection`~
---------------------------

### ~`model`~

`model` オプションは現在のルートに関連するモデルクラスの名前で必須です。

### ~`actions`~

*デフォルト*: `false`

`actions` オプションはルートに許可されるアクションの配列を定義します。アクションは利用可能なすべてのアクションのサブセット: `list`、`new`、`create`、`edit`、`update`、`delete` そして `show` でなければなりません。

このオプションと `with_show` オプションの両方が `false` にセットされている場合、`show` アクション以外のすべてのアクションが利用可能になります (下記を参照)。

### ~`module`~

*デフォルト*: ルートの名前

`module` オプションはモジュールの名前を定義します。

### ~`prefix_path`~

*デフォルト*: ルートの名前の前につけられる `/`

`prefix_path` オプションはすべての `url` パターンの接頭辞を指定します。このオプションは任意の有効なパターンになり、変数と複数のセグメントを収めることができます。

### ~`column`~

*デフォルト*: `id`

`column` オプションはモデルオブジェクトの重複しない識別子として使うモデルのカラムを定義します。

### ~`with_show`~

*デフォルト*: `true`

`actions` オプションが `false` にセットされているときでも ルートに許可されるアクションのリストに `show` アクションを加えるために `with_show` オプションが使われます。 

### ~`segment_names`~

*デフォルト*: `array('edit' => 'edit', 'new' => 'new'),`

`segment_names` は `edit` と `new` アクションの `url` パターンで使う単語を定義します。

### ~`model_methods`~

*デフォルト*: 空の配列

`model_methods` オプションはモデルからオブジェクトを検索するために呼び出すメソッドを定義します (`sfObjectRoute` の `method` オプションを参照)。これは実際には `list` と `object` メソッドを定義する配列です:

    [yml]
    model_methods:
      list:   getObjects
      object: getObject

### ~`requirements`~

*デフォルト*: `column` に対して `\d+`

`requirements` オプションはルート変数に適用されるルート要件の配列を定義します。

### ~`with_wildcard_routes`~

*デフォルト*: `false`

`with_wildcard_routes` オプションは2つのワイルドカードのルート: (1つは単独のオブジェクト、もう1つはオブジェクトコレクション) を通してアクションにアクセスできるようにします。

### ~`route_class`~

*デフォルト*: `sfObjectRoute`

`route_class` オプションはコレクションに使われるデフォルトのルートオブジェクトをオーバーライドします。

### ~`collection_actions`~

*デフォルト*: 空の配列

`collection_actions` オプションはコレクションルートで利用可能な追加アクションの配列を定義します。

### ~`object_actions`~

*デフォルト*: 空の配列

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
