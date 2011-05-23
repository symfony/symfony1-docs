generator.yml 設定ファイル
==========================

モデルクラスのバックエンドインターフェイスの作成には symfony のアドミンジェネレータを使います。ORM として Propel もしくは Doctrine が使われます。

### 作成

アドミンジェネレータモジュールを生成するには `propel:generate-admin` もしくは `doctrine:generate-admin` タスクを使います。

    $ php symfony propel:generate-admin backend Article

    $ php symfony doctrine:generate-admin backend Article

上記の例では、`Article` モデルクラスに対応した `article` アドミンジェネレータモジュールが生成されます。

>**NOTE**
>`generator.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は ~`sfGeneratorConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)にゆだねられます。

### 設定ファイル

モジュールのコンフィギュレーションを変更できる場所は `apps/backend/modules/model/article/` ディレクトリに配置されている `generator.yml` ファイルです。

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        # パラメータの配列

このファイルにはメインエントリが2つが用意されています (`class` と `param`)。`class` エントリにセットされているデフォルトの値は、Propel の場合は `sfPropelGenerator` で、Doctrine の場合は `sfDoctrineGenerator` です。

`param` エントリには生成モジュールのコンフィギュレーションオプションがとりそろえられています。`model_class` オプションはこのモジュールに結びつけるモデルクラスを定義し、`theme` オプションはデフォルトのテーマを定義します。

メインコンフィギュレーションを変更する場所は `config` エントリの下側です。エントリは7つのセクションにわかれています。

  * `actions`: リストとフォームで見つかるアクションのデフォルトコンフィギュレーション
  * `fields`:  フィールドのデフォルトコンフィギュレーション
  * `list`:    リストのコンフィギュレーション
  * `filter`:  フィルタのコンフィギュレーション
  * `form`:    新規ページと編集フォームのコンフィギュレーション
  * `edit`:    編集ページ専用のコンフィギュレーション
  * `new`:     新規ページ専用のコンフィギュレーション

デフォルトでは、セクションの定義はすべて空です。アドミンジェネレータは実行可能なすべてのオプションに応じて適切なデフォルトを用意します。

    [yml]
    generator:
      param:
        config:
          actions: ~
          fields:  ~
          list:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

この章では、アドミンジェネレータをカスタマイズするときにおいて、`config` エントリを通じて利用可能なすべてのオプションを説明します。

>**NOTE**
>何らかの説明がなければ、Propel と Doctrine の両方においてオプションの効果は同じです。

### フィールド

多くのオプションはフィールドのリストを引数にとります。フィールドは実際のカラム名もしくは仮想的な名前になります。両方のケースにおいて、ゲッターをモデルクラスのなかで定義しなければなりません (`get` の後にキャメルケースのフィールド名をつけます)。

アドミンジェネレータはコンテキストに応じてフィールドをレンダリングする方法を自分で選びます。レンダリングをカスタマイズするには、パーシャルもしくはコンポーネントを用意します。慣習では、名前につけるプレフィックスは、パーシャルにはアンダースコア (`_`) で、コンポーネントにはチルダ (`~`) です。

    [yml]
    display: [_title, ~content]

上記の例において、`title` フィールドは `title` パーシャルによってレンダリングされ、`content` フィールドは `content` コンポーネントによってレンダリングされます。

アドミンジェネレータはパーシャルとコンポーネントにいくつかのパラメータを渡します。

  * `new` と `edit` ページに渡されるパラメータ:

    * `form`:       現在のモデルオブジェクトに関連づけされているフォーム
    * `attributes`: ウィジェットに適用される HTML 属性の配列

  * `list`ページに渡されるパラメータ:

    * `type`:       `list`
    * `MODEL_NAME`: 現在のオブジェクトのインスタンスで、MODEL_NAME はジェネレータオプションにセットされている単数形の名前です。値が明確に指定されていなければ、単数形の名前はアンダースコアで区切られたモデルクラスの名前になります (CamelCase は camel_case になります)
	
`edit` もしくは `new` ページにおいて、2カラムレイアウトを保ちたい場合 (フィールドのラベルとウィジェット)、パーシャルもしくはコンポーネントテンプレートは次のようになります。

    [php]
    <div class="sf_admin_form_row">
      <label>
        <!-- 1番目のカラムに表示されるフィールドラベルもしくはコンテンツ -->
      </label>
      <!-- 2番目のカラムに表示されるフィールドウィジェットもしくはコンテンツ -->
    </div>

### オブジェクトプレースホルダ

オプションのなかにはモデルオブジェクトプレースホルダを受け取るものがあります。プレースホルダは `%%NAME%%` のパターンにしたがう文字列です。`NAME` は任意の文字列で、オブジェクトのゲッターメソッドに変換されます (`get` の後にキャメルケースの `NAME` 文字列がつきます)。たとえば、`%%title%%` は `$article->getTitle()` の値に置き換わります。実行時において、現在のコンテキストに関連するオブジェクトに応じて、プレースホルダの値は動的に置き換わります。

>**TIP**
>モデルのなかに別のモデルへの外部キーが存在していれば、Propel と Doctrine は関連オブジェクトのゲッターを定義します。その他のゲッターに関して、オブジェクトを文字列に変換する `__toString()` メソッドが定義されていれば、ゲッターをプレースホルダに用いることができます。

### コンフィギュレーションの継承

アドミンジェネレータのコンフィギュレーションはコンフィギュレーションカスケードの原則にしたがいます。継承ルールは次のとおりです。

 * `new` と `edit` は `form` を継承し、`form` は `fields` を継承します。
 * `list` は `fields` を継承します。
 * `filter` は `fields` を継承します。

### ~クレデンシャル~

`credential` オプション (下記の節をご参照ください) を使えば、ユーザークレデンシャルに応じて (リストとフォームにおける) アドミンジェネレータのアクションを隠すことができます。しかしながら、リンクもしくはボタンが表示されないアクションであっても不正なアクセスからアクションを守らなければならないことがあります。アドミンジェネレータのクレデンシャル管理機能は表示の仕事だけを受け持ちます。

`credential` オプションは list ページのカラムを隠すためにも使うこともできます。

### アクションのカスタマイズ

既存のコンフィギュレーションがもの足りなければ、生成メソッドをオーバーライドする選択肢があります。

 | メソッド               | 説明
 | ---------------------- | -------------------------------------
 | `executeIndex()`       | `list` ビューアクション
 | `executeFilter()`      | フィルタを更新します
 | `executeNew()`         | `new` ビューアクション
 | `executeCreate()`      | 新しいレコードを作ります
 | `executeEdit()`        | `edit` ビューアクション
 | `executeUpdate()`      | レコードを更新します
 | `executeDelete()`      | レコードを削除します
 | `executeBatch()`       | バッチアクションを実行します
 | `executeBatchDelete()` | `_delete` バッチアクションを実行します
 | `processForm()`        | レコードフォームを処理します
 | `getFilters()`         | 現在のフィルタを返します
 | `setFilters()`         | フィルタをセットします
 | `getPager()`           | list ページャを返します
 | `getPage()`            | ページャページを取得します
 | `setPage()`            | ページャページをセットします
 | `buildCriteria()`      | list の `Criteria` をビルドします
 | `addSortCriteria()`    | list のソート `Criteria` を追加します
 | `getSort()`            | 現在のソートカラムを返します
 | `setSort()`            | 現在のソートカラムをセットします

### テンプレートのカスタマイズ

それぞれの生成テンプレートをオーバーライドできます。

 | テンプレート                 | 説明
 | ---------------------------- | --------------------------------------------------
 | `_assets.php`                | テンプレートに使う CSS と JS をレンダリングします
 | `_filters.php`               | フィルタボックスをレンダリングします
 | `_filters_field.php`         | 単独のフィルタフィールドをレンダリングします
 | `_flashes.php`               | フラッシュメッセージをレンダリングします
 | `_form.php`                  | フォームを表示します
 | `_form_actions.php`          | フォームのアクションを表示します
 | `_form_field.php`            | 単独のフォームフィールドを表示します
 | `_form_fieldset.php`         | フォームのフィールドセットを表示します
 | `_form_footer.php`           | フォームのフッターを表示します
 | `_form_header.php`           | フォームのヘッダーを表示します
 | `_list.php`                  | list を表示します
 | `_list_actions.php`          | list アクションを表示します
 | `_list_batch_actions.php`    | list バッチアクションを表示します
 | `_list_field_boolean.php`    | list における単独のブール型フィールドを表示します
 | `_list_footer.php`           | list のフッターを表示します
 | `_list_header.php`           | list のヘッダーを表示します
 | `_list_td_actions.php`       | 列のオブジェクトアクションを表示します
 | `_list_td_batch_actions.php` | 列のチェックボックスを表示します
 | `_list_td_stacked.php`       | 列のスタックレイアウトを表示します
 | `_list_td_tabular.php`       | list の単独フィールドを表示します
 | `_list_th_stacked.php`       | ヘッダーにおける単独のカラム名を表示します
 | `_list_th_tabular.php`       | ヘッダーにおける単独のカラム名を表示します
 | `_pagination.php`            | list のページ送りを表示します
 | `editSuccess.php`            | `edit` ビューを表示します
 | `indexSuccess.php`           | `list` ビューを表示します
 | `newSuccess.php`             | `new` ビューを表示します

### 見た目のカスタマイズ

生成されるテンプレートにはさまざまな `class` と `id` 属性が定義されているので、アドミンジェネレータの見た目をかんたんにカスタマイズできます。

`edit` もしくは `new` ページにおいて、それぞれのフィールドの HTML コンテナには次のクラスがとりそろえられています。

  * `sf_admin_form_row`
  * フィールドの型に依存するクラス: `sf_admin_text`、`sf_admin_boolean`、`sf_admin_date`、`sf_admin_time` もしくは `sf_admin_foreignkey`。
  * `sf_admin_form_field_COLUMN`。`COLUMN` はカラムの名前です。

`list` ページにおいて、それぞれのフィールドの HTML コンテナには次のクラスがとりそろえられています。

  * フィールドの型に依存するクラス: `sf_admin_text`、`sf_admin_boolean`、`sf_admin_date`、`sf_admin_time`、もしくは `sf_admin_foreignkey`
  * `sf_admin_form_field_COLUMN`。`COLUMN` はカラムの名前です。

<div class="pagebreak"></div>

利用可能なコンフィギュレーションオプションの一覧
------------------------------------------------

 * [`actions`](#chapter_06_actions)

   * [`label`](#chapter_06_sub_label)
   * [`action`](#chapter_06_sub_action)
   * [`credentials`](#chapter_06_sub_credentials)

 * [`fields`](#chapter_06_fields)

   * [`label`](#chapter_06_sub_label)
   * [`help`](#chapter_06_sub_help)
   * [`attributes`](#chapter_06_sub_attributes)
   * [`credentials`](#chapter_06_sub_credentials)
   * [`renderer`](#chapter_06_sub_renderer)
   * [`renderer_arguments`](#chapter_06_sub_renderer_arguments)
   * [`type`](#chapter_06_sub_type)
   * [`date_format`](#chapter_06_sub_date_format)

 * [`list`](#chapter_06_list)

   * [`title`](#chapter_06_sub_title)
   * [`display`](#chapter_06_sub_display)
   * [`hide`](#chapter_06_sub_hide)
   * [`layout`](#chapter_06_sub_layout)
   * [`params`](#chapter_06_sub_params)
   * [`sort`](#chapter_06_sub_sort)
   * [`max_per_page`](#chapter_06_sub_max_per_page)
   * [`pager_class`](#chapter_06_sub_pager_class)
   * [`batch_actions`](#chapter_06_sub_batch_actions)
   * [`object_actions`](#chapter_06_sub_object_actions)
   * [`actions`](#chapter_06_sub_actions)
   * [`peer_method`](#chapter_06_sub_peer_method)
   * [`peer_count_method`](#chapter_06_sub_peer_count_method)
   * [`table_method`](#chapter_06_sub_table_method)
   * [`table_count_method`](#chapter_06_sub_table_count_method)

 * [`filter`](#chapter_06_filter)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`form`](#chapter_06_form)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`edit`](#chapter_06_edit)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

 * [`new`](#chapter_06_new)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

<div class="pagebreak"></div>

`fields`
--------

`fields` セクションはそれぞれのフィールドのデフォルトコンフィギュレーションを定義します。このコンフィギュレーションはすべてのページに対して定義され、ページごとにオーバーライドできます (`list`、`filter`、`form`、`edit` と `new`)。

### ~`label`~

*デフォルト*: わかりやすい名前のカラム

`label` オプションはフィールドに使われるラベルを定義します。

    [yml]
    config:
      fields:
        slug: { label: "URL のショートカット" }

### ~`help`~

*デフォルト*: なし

`help` オプションはフィールドに表示されるヘルプテキストを定義します。

### ~`attributes`~

*デフォルト*: `array()`

`attributes` オプションはウィジェットに渡される HTML 属性を定義します。

    [yml]
    config:
      fields:
        slug: { attributes: { class: foo } }

### ~`credentials`~

*デフォルト*: なし

`credentials` オプションはフィールドを表示するためにユーザーがもっていなければならない必須のクレデンシャルを定義します。クレデンシャルが強制されるアクションはリストにあげられているものにかぎられます。

    [yml]
    config:
      fields:
        slug:      { credentials: [admin] }
        is_online: { credentials: [[admin, moderator]] }

>**NOTE**
>クレデンシャルは `security.yml` ファイルと同じルールで定義されます。

### ~`renderer`~

*デフォルト*: なし

`renderer` オプションはフィールドをレンダリングするために呼び出す PHP コールバックを定義します。定義されていれば、パーシャルやコンポーネントなどのフラグはオーバーライドします。

コールバックは呼び出される際に `renderer_arguments` オプションで定義されているフィールドと引数の値を受け取ります。

### ~`renderer_arguments`~

*デフォルト*: `array()`

`renderer_arguments` オプションはフィールドがレンダリングされる際に PHP の `renderer` コールバックに渡される引数を定義します。このオプションが適用されるのは `renderer` オプションが定義されている場合にかぎられます。

### ~`type`~

*デフォルト*: バーチャルカラムの `Text`

`type` オプションはカラムの型を定義します。デフォルトでは、モデルで定義されているカラムの型が使われますが、バーチャルカラムを作るのであれば、デフォルトの `Text` 型を有効な型のうちの1つでオーバーライドできます。

  * `ForeignKey`
  * `Boolean`
  * `Date`
  * `Time`
  * `Text`
  * `Enum` (Doctrine 限定)

### ~`date_format`~

*デフォルト*: `f`

`date_format` オプションは日付の表示に使われるフォーマットを定義します。値は `sfDateFormat` クラスによって認識されるフォーマットです。フィールドの型が `Date` である場合はこのオプションは適用されません。

次のトークンをフォーマットに使うことができます。

 * `G`: Era
 * `y`: year
 * `M`: mon
 * `d`: mday
 * `h`: Hour12
 * `H`: hours
 * `m`: minutes
 * `s`: seconds
 * `E`: wday
 * `D`: yday
 * `F`: DayInMonth
 * `w`: WeekInYear
 * `W`: WeekInMonth
 * `a`: AMPM
 * `k`: HourInDay
 * `K`: HourInAMPM
 * `z`: TimeZone

`actions`
---------

さまざまなアクションがあらかじめ定義され、組み込まれています。これらすべての名前にはプレフィックスのアンダースコア (`_`) がつきます。それぞれのアクションはこの節で説明されているオプションでカスタマイズできます。アクションを `list`、`edit` もしくは `new` エントリで定義する場合にも同じオプションを使うことができます。

### ~`label`~

*デフォルト*: アクションのキー

`label` オプションはアクションに使うラベルを定義します。

### ~`action`~

*デフォルト*: アクションの名前に応じて定義されます。

`action` オプションは実行するアクションの名前を定義します (プレフィックスの `execute` はつきません)。

### ~`credentials`~

*デフォルト*: なし

`credentials` オプションはアクションを表示するためにユーザーがもっていなければならないクレデンシャルを定義します。

>**NOTE**
>クレデンシャルは `security.yml` ファイルと同じルールで定義されます。

`list`
------

### ~`title`~

*デフォルト*: わかりやすい名前で、サフィックスが「List」であるモデルクラス

`title` オプションは list ページのタイトルを定義します。

### ~`display`~

*デフォルト*: すべてのモデルカラム、表示順序はスキーマファイルの定義順と同じ

`display` オプションは list ページに表示されるカラムの順序つき配列を定義します。

カラムの名前の前に等号 (`=`) をつければ、文字列は現在のオブジェクトの `edit` ページに進むリンクに変換されるようになります。

    [yml]
    config:
      list:
        display: [=name, slug]

>**NOTE**
>カラムを隠す `hide` オプションもご参照ください。

### ~`hide`~

*デフォルト*: なし

`hide` オプションは list ページから隠すカラムを定義します。表示されるカラムを `display` オプションで指定するよりも、こちらのほうが作業が少なくてすむことがあります。

    [php]
    config:
      list:
        hide: [created_at, updated_at]

>**NOTE**
>`display` と `hide` オプションの両方が指定されている場合、`hide` オプションは無視されます。

### ~`layout`~

*デフォルト*: `tabular`

*利用可能な値*: ~`tabular`~ もしくは ~`stacked`~

`layout` オプションは list ページの表示に使われるレイアウトを定義します。

`tabular` レイアウトでは、独自のテーブルのカラムにそれぞれのカラムの値が入ります。

`stacked` レイアウトでは、それぞれのオブジェクトは `params` オプションで定義されている単独の文字列であらわされます (下記の節をご参照ください)。

>**NOTE**
>ユーザーがソートできるカラムは `display` オプションによって定義されるので、`stacked` レイアウトを使う場合にもこのオプションは必要です。

### ~`params`~

*デフォルト*: なし

`params` オプションは `stacked` レイアウトに使われる HTML 文字列のパターンを定義します。この文字列にモデルオブジェクトのプレースホルダを埋め込むことができます。

    [yml]
    config:
      list:
        params:  |
          %%title%% written by %%author%% and published on %%published_at%%.

カラムの名前の前に等号 (`=`) をつけると、文字列は現在のオブジェクトの `edit` ページに進むリンクに変換されるようになります。

### ~`sort`~

*デフォルト*: なし

`sort` オプションはデフォルトの sort カラムを定義します。このオプションはカラムの名前とソートの順序 (`asc` もしくは `desc`) からなります。

    [yml]
    config:
      list:
        sort: [published_at, desc]

### ~`max_per_page`~

*デフォルト*: `20`

`max_per_page` オプションは1ページに表示されるオブジェクトの最大個数を定義します。

### ~`pager_class`~

*デフォルト*: Propel では `sfPropelPager`、Doctrine では `sfDoctrinePager`

`pager_class` オプションは list ページが表示される際に使われるページャクラスを定義します。

### ~`batch_actions`~

*デフォルト*: `{ _delete: ~ }`

`batch_actions` オプションは list ページにおいてオブジェクトを選ぶために実行されるアクションのリストを定義します。

`action` オプションが定義されていない場合、アドミンジェネレータは `executeBatch` をプレフィックスとするキャメルケースの名前をもつメソッドを探します。

実行されるメソッドは `ids` リクエストパラメータを通じて選ばれたオブジェクトのプライマリキーを受け取ります。

>**TIP**
>バッチアクションを無効にするには、このオプションに空の配列 (`{}`) にセットします。

### ~`object_actions`~

*デフォルト*: `{ _edit: ~, _delete: ~ }`

`object_actions` オプションはリストのそれぞれのオブジェクトで実行できるアクションのリストを定義します。

    [yml]
    object_actions:
      moveUp:     { label: "move up", action: "moveUp" }
      moveDown:   { label: "move down", action: "moveDown" }
      _edit:      ~
      _delete:    ~

`action` オプションが定義されていなければ、アドミンジェネレータは `executeList` をプレフィックスとするキャメルケースの名前をもつメソッドを探します。

>**TIP**
>オブジェクトアクションを無効にするには、このオプションに空の配列 (`{}`) をセットします。

### ~`actions`~

*デフォルト*: `{ _new: ~ }`

`actions` オプションはオブジェクトを受け取らないアクションを定義します。用例としてオブジェクトの新規作成をあげることができます。

`action` オプションが定義されていない場合、アドミンジェネレータは `executeList` をプレフィックスとするキャメルケースの名前のメソッドを探します。

>**TIP**
>オブジェクトアクションの機能を無効にするには、オプションに空の配列 (`{}`) をセットします。

### ~`peer_method`~

*デフォルト*: `doSelect`

`peer_method` オプションは list ページで表示されるオブジェクトを検索するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Propel 専用です。Doctrine では `table_method` オプションを使います。

### ~`table_method`~

*デフォルト*: `doSelect`

`table_method` オプションは list ページで表示されるオブジェクトを検索するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Doctrine 専用です。Propel では `peer_method` オプションを使います。

### ~`peer_count_method`~

*デフォルト*: `doCount`

`peer_count_method` オプションは現在のフィルタオブジェクトの個数を算出するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Propel 専用です。Doctrine では `table_count_method` オプションを使います。

### ~`table_count_method`~

*デフォルト*: `doCount`

`table_count_method` オプションは現在のフィルタオブジェクトの個数を算出するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Doctrine 専用です。Propel では `peer_count_method` オプションを使います。

`filter`
--------

`filter` セクションは list ページに表示されるフォームにフィルタをかける方法を指定するためのコンフィギュレーションを定義します。

### ~`display`~

*デフォルト*: フィルタフォームクラスで定義されているすべてのフィールド、表示順序は定義順と同じ

`display` オプションは表示されるフィールドの順序つきリストを定義します。

>**TIP**
>フィルタフィールドはつねにオプションで、表示されるフィールドを設定するためにフィルタフォームクラスをオーバーライドする必要はありません。

### ~`class`~

*デフォルト*: `FormFilter` をサフィックスとするモデルクラスの名前

`class` オプションは `filter` フォームに使われるフォームクラスを定義します。

>**TIP**
>フィルタリングを完全に除外するには、このオプションに `false` をセットします。

`form`
------

`form` セクションは `edit` と `new` セクションのフォールバックとしての役目を果たすためだけに用意されています (最初の継承ルールをご参照ください)。

>**NOTE**
>フォームセクション (`form`、`edit` と `new`) に関して、`label` と `help` オプションはフォームクラスで定義されているものをオーバーライドします。

### ~`display`~

*デフォルト*: フォームクラスで定義されているすべてのクラス。表示順序は定義順と同じ。

`display` オプションは表示されるフィールドの順序つきリストを定義します。

このオプションは複数のフィールドを1つのグループにまとめるために使うこともできます。

    [yml]
    # apps/backend/modules/model/config/generator.yml
    config:
      form:
        display:
          Content: [title, body, author]
          Admin:   [is_published, expires_at]

上記のコンフィギュレーションは2つのグループ (`Content` と `Admin`) を定義しています。それぞれのグループにフォームフィールドのサブセットが用意されています。

>**CAUTION**
>モデルフォームで定義されているすべてのフィールドは `display` オプションに存在していなければなりません。存在していなければ、予期せぬバリデーションエラーが発生する可能性があります。

### ~`class`~

*デフォルト*: `Form` をサフィックスとするモデルクラスの名前

`class` オプションは `edit` と `new` ページに使われるフォームクラスを定義します。

>**TIP**
>`class` オプションは `new` と `edit` セクションの両方で定義できますが、1つのクラスを使い、条件ロジックで対処するほうがよいやりかたです。

`edit`
------

`edit` セクションは `form` セクションと同じオプションをとります。

### ~`title`~

*デフォルト*: `Edit` をサフィックスとするモデルクラスの名前

`title` オプションは edit ページのタイトルを定義します。このオプションはモデルオブジェクトのプレースホルダをとることができます。

### ~`actions`~

*デフォルト*: `{ _delete: ~, _list: ~, _save: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。

`new`
-----

`new` セクションは `form` セクションと同じオプションをとります。

### ~`title`~

*デフォルト*: `New` をサフィックスとするモデルクラスの名前

`title` オプションは新しいページのタイトルを定義します。このオプションはモデルオブジェクトのプレースホルダをとることができます。

>**TIP**
>オブジェクトが新しい場合でも、タイトルの一部として出力したいデフォルトの値を指定することができます。

### ~`actions`~

*デフォルト*: `{ _delete: ~, _list: ~, _save: ~, _save_and_add: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。
