routing.yml 設定ファイル
========================

ルートは `routing.yml` ファイルのなかで定義できます。

アプリケーションの `routing.yml` ファイルは `apps/APP_NAME/config/` ディレクトリに配置されています。

`routing.yml` ファイルには、名前つきルート定義のリストが用意されています。

    [yml]
    ROUTE_1:
      # ルート1の定義

    ROUTE_2:
      # ルート2の定義

    # ...

リクエストがやってくると、ルーティングシステムはリクエストされた URL がルートとマッチするか試します。最初にマッチするルートが優先されるので、`routing.yml` ファイルにおけるルートの記載順は重要です。

`routing.yml` ファイルが読み込まれるとき、それぞれのルートは `class` クラスのオブジェクトに変換されます。

    [yml]
    ROUTE_NAME:
      class: CLASS_NAME
      # ルートが存在する場合のコンフィギュレーション

`class` クラスは `sfRoute` 基底クラスを継承しなければなりません。クラスが指定されていなければ、`sfRoute` 基底クラスがフォールバックに使われます。

>**NOTE**
>`routing.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は `sfRoutingConfigHandler` [クラス](#chapter_14-Other-Configuration-Files_config_handlers_yml)にゆだねられます。

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

ルートを細かく調整できるようにするために、`routing.yml` ファイルは複数のコンフィギュレーションをサポートしています。`sfRoutingConfigHandler` クラスによってそれぞれのルートがオブジェクトに変換される際にこれらの設定項目は使われます。

### ~`class`~

*デフォルト*: `sfRoute` (もしくは `type` に `collection` がセットされている場合は `sfRouteCollection` です。下記の節をご参照ください)

`class` 設定はルートに使われるルートクラスを指定します。

### ~`url`~

*デフォルト*: `/`

`url` 設定はリクエストされた URL に対するパターンマッチに使われるルートのパターンです。

パターンは複数のセグメント (構成要素) からなります。

 * 変数 ([コロン `:`](#chapter_05_sub_variable_prefixes) をプレフィックスとする単語)
 * 定数
 * キーと値のペアのシーケンスにマッチするワイルドカード (`*`)

それぞれのセグメントはあらかじめ定義されている区切り文字の1つで区切らなければなりません (デフォルトは `/` もしくは `.`)。

### ~`params`~

*デフォルト*: 空の配列

`params` 設定はルートに関連づけられたパラメータの配列を定義します。これらのパラメータは `url` で指定されている変数、もしくはこのルートに関連づけられた変数のデフォルトになります。

### ~`param`~

*デフォルト*: 空の配列

この設定は `params` 設定と同等です。

### ~`options`~

*デフォルト*: 空の配列

`options` 設定はふるまいを細かくカスタマイズするためにルートオブジェクトに渡すオプションの配列です。次の節では、それぞれのルートクラスで利用可能なオプションを説明します。

### ~`requirements`~

*デフォルト*: 空の配列

`requirements` 設定は `url` 変数が満たさなければならないルート要件の配列です。キーは `url` 変数で指定され、値はこの変数のパターンマッチに使われる正規表現です。

>**TIP**
>1つの正規表現は別の正規表現に含まれるので、区切り文字で囲んだり、値全体がマッチするようにキャレット (`^`) もしくはドル記号 (`$`) をつける必要はありません。

### ~`type`~

*デフォルト*: `null`

`type` 設定に `collection` がセットされている場合、ルートはルートコレクションとして読み込まれます。

>**NOTE**
>`class` の名前に単語の `Collection` が含まれる場合、コンフィギュレーションハンドラによってこの設定に `collection` が自動的にセットされます。このことが意味するのは、ほとんどの場合において、この設定を変更する必要はないということです。

~`sfRoute`~
-----------

すべてのルートクラスは `sfRoute` 基底クラスを継承しなければなりません。この基底クラスは必須のルート設定を提供します。

~`sfRequestRoute`~
------------------

### ~`sf_method`~

*デフォルト*: `get`

`sf_method` オプションは `requirements` 配列に使われます。このオプションによって、ルートのパターンマッチのあいだに HTTP リクエストが強制されます。

~`sfObjectRoute`~
-----------------

`sfObjectRoute` ルートクラスのオプションを指定する場所は `routing.yml` ファイルの `options` 設定の範囲に入っていなければなりません。

### ~`model`~

`model` オプションは必須オプションであり、現在のルートに関連づけられたモデルクラスの名前です。

### ~`type`~

`type` オプションは必須オプションであり、モデルに必要なルートの種類です。このオプションは `object` もしくは `list` のどちらかになります。`object` 型のルートは単独のモデルオブジェクトをあらわし、`list` 型のルートはモデルオブジェクトのコレクションをあらわします。

### ~`method`~

`method` オプションは必須オプションです。このオプションはモデルクラスのなかでこのルートに関連づけられたオブジェクトを検索する際に呼び出されるスタティックメソッドです。このメソッドは、パースされたルートのパラメータを引数にとります。

### ~`allow_empty`~

*デフォルト*: `true`

`allow_empty` オプションに `false` がセットされている場合、`model` の `method` が呼び出された際にオブジェクトが返されなければ、ルートは404エラーの例外を投げます。

### ~`convert`~

*デフォルト*: `toParams`

`convert` オプションは、モデルオブジェクトに応じてルートが生成される場合において、モデルが適切なパラメータの配列に変換される際に呼び出されるメソッドです。このメソッドが返す配列には、少なくともルートパターンの必須パラメータが要素として含まれていなければなりません (`url` 設定で定義されます)。

~`sfPropelRoute`~
-----------------

### ~`method_for_criteria`~

*デフォルト*: コレクションには `doSelect`、単独のオブジェクトには `doSelectOne`

`method_for_criteria` オプションは、現在のリクエストに関連づけされたオブジェクトを検索する際にピアクラスによって呼び出されるメソッドを定義します。このメソッドはパースされたルートのパラメータを引数にとります。

~`sfDoctrineRoute`~
-------------------

### ~`method_for_query`~

*デフォルト*: none

`method_for_query` オプションは現在のリクエストに関連づけされたオブジェクトを検索する際にモデルを呼び出すメソッドを定義します。このメソッドは現在のクエリオブジェクトを引数にとります。

このオプションに値がセットされていなければ、`execute()` メソッドによるクエリの「実行」だけがおこなわれます。

~`sfRouteCollection`~
---------------------

`sfRouteCollection` 基底クラスはルートのコレクションをあらわします。

~`sfObjectRouteCollection`~
---------------------------

### ~`model`~

`model` オプションは現在のルートに関連づけられたモデルクラスの名前で必須です。

### ~`actions`~

*デフォルト*: `false`

`actions` オプションはルートに許可されるアクションの配列を定義します。アクションは利用可能なすべてのアクションの部分集合に含まれていなければなりません (`list`、`new`、`create`、`edit`、`update`、`delete` そして `show`)。

このオプションと `with_show` オプションの両方に `false` がセットされている場合、`show` アクション以外のすべてのアクションが利用可能になります (下記の説明をご参照ください)。

### ~`module`~

*デフォルト*: ルートの名前

`module` オプションはモジュールの名前を定義します。

### ~`prefix_path`~

*デフォルト*: ルートの名前の前につく `/`

`prefix_path` オプションはすべての `url` パターンのプレフィックスを指定します。このオプションがとる値は、任意の有効なパターンであり、変数と複数のセグメントです。

### ~`column`~

*デフォルト*: `id`

`column` オプションはモデルオブジェクトの一意性をあらわす識別子に使うモデルのカラムを定義します。

### ~`with_show`~

*デフォルト*: `true`

`with_show` オプションに `true` がセットされていれば、`actions` オプションに `false` がセットされている場合でも、ルートに許可されるアクションのリストに `show` アクションを加えることができます。

### ~`segment_names`~

*デフォルト*: `array('edit' => 'edit', 'new' => 'new'),`

`segment_names` オプションは `edit` と `new` アクションの `url` パターンに使われる単語を定義します。

### ~`model_methods`~

*デフォルト*: 空の配列

`model_methods` オプションはモデルからオブジェクトが検索される際に呼び出されるメソッドを定義します (`sfObjectRoute` ルートクラスの `method` オプションをご参照ください)。実際には、このオプションは `list` と `object` メソッドを定義する配列です。

    [yml]
    model_methods:
      list:   getObjects
      object: getObject

### ~`requirements`~

*デフォルト*: `column` に対して `\d+`

`requirements` オプションはルート変数に適用されるルート要件の配列を定義します。

### ~`with_wildcard_routes`~

*デフォルト*: `false`

`with_wildcard_routes` オプションは2つのワイルドカードのルート (1つは単独のオブジェクト、もう1つはオブジェクトコレクション) を通じてアクションにアクセスできるようにします。

### ~`route_class`~

*デフォルト*: `sfObjectRoute`

`route_class` オプションはコレクションに使われるデフォルトのルートオブジェクトをオーバーライドします。

### ~`collection_actions`~

*デフォルト*: 空の配列

`collection_actions` オプションはコレクションルートで利用可能な追加アクションの配列を定義します。キーはアクションの名前で、値はそのアクションに対して有効なメソッドです。

    [yml]
    articles:
      options:
        collection_actions: { filter: post, filterBis: [post, get] }
        # ...

### ~`object_actions`~

*デフォルト*: 空の配列

`object_actions` オプションはオブジェクトルートで利用可能な追加アクションの配列を定義します。配列のキーはアクションの名前で、値はそのアクションに対して有効なメソッドです。

    [yml]
    articles:
      options:
        object_actions: { publish: put }
        # ...

~`sfPropelRouteCollection`~
---------------------------

`sfPropelRouteCollection` ルートクラスは `sfRouteCollection` 基底クラスを継承し、デフォルトのルートクラスを `sfPropelRoute` に変更します (上記の `route_class` オプションの説明をご参照ください)。

~`sfDoctrineRouteCollection`~
-----------------------------

`sfDoctrineRouteCollection` ルートクラスは `sfRouteCollection` 基底クラスを継承し、デフォルトのルートクラスを `sfDoctrineRoute` に変更します (上記の `route_class` オプションの説明をご参照ください)。
