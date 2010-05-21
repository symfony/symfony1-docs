generator.yml 設定ファイル
==========================

symfony のアドミンジェネレータによってモデルクラスのバックエンドインターフェイスを作ることができます。Propel もしくは Doctrine が ORM として使われます。

### 作成

アドミンジェネレータモジュールは `propel:generate-admin` もしくは `doctrine:generate-admin` タスクによって生成されます:

    $ php symfony propel:generate-admin backend Article

    $ php symfony doctrine:generate-admin backend Article

上記の例では、`Article` モデルクラスに対応する `article` アドミンジェネレータモジュールが生成されます。

>**NOTE**
>`generator.yml` 設定ファイルは PHP ファイルとしてキャッシュされます。処理は ~`sfGeneratorConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

### 設定ファイル

モジュールのコンフィギュレーションの変更は `apps/backend/modules/model/article/generator.yml` ファイルで行います:

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        # パラメータの配列

このファイルには2つのメインエントリ: `class` と `param` が用意されています。`class` エントリに指定されているのは、Propel では `sfPropelGenerator` で、Doctrine では `sfDoctrineGenerator` です。

`param` エントリには生成モジュールのコンフィギュレーションオプションが用意されています。`model_class` オプションはこのモジュールに結びつけるモデルクラスを定義し、`theme` オプションはデフォルトのテーマを定義します。

メインコンフィギュレーションの変更は `config` エントリの下で行います。エントリは7つのセクションにわかれます:

  * `actions`: リストとフォームで見つかるアクションのデフォルトコンフィギュレーション
  * `fields`:  フィールドのデフォルトコンフィギュレーション
  * `list`:    リストのコンフィギュレーション
  * `filter`:  フィルタのコンフィギュレーション
  * `form`:    新規ページと編集フォームのコンフィギュレーション
  * `edit`:    編集ページ固有のコンフィギュレーション
  * `new`:     新規ページ固有のコンフィギュレーション

デフォルトでは、セクションの定義はすべて空です。アドミンジェネレータはすべての実行可能なオプションに合わせて適切なデフォルトを用意します:

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

この章では、アドミンジェネレータをカスタマイズするときに、`config` エントリを通して利用可能なすべてのオプションを説明します。

>**NOTE**
>何らかの説明がなければ、すべてのオプションは Propel と Doctrine の両方で同じように作用します。

### フィールド

多くのオプションはフィールドのリストを引数にとります。フィールドは実際のカラム名もしくは仮想的な名前になります。両方のケースにおいて、ゲッターをモデルクラスのなかで定義しなければなりません (`get` の後にラクダ記法のフィールド名をつけます)。

アドミンジェネレータは、コンテキストに合わせてフィールドをレンダリングする方法を自己判断します。レンダリングをカスタマイズするには、パーシャルもしくはコンポーネントを作ります。慣習では、名前につけるプレフィックスは、パーシャルにはアンダースコア (`_`) で、コンポーネントにはチルダ (`~`) です:

    [yml]
    display: [_title, ~content]

上記の例において、`title` フィールドは `title` パーシャルによってレンダリングされ、`content` フィールドは `content` コンポーネントによってレンダリングされます。

アドミンジェネレータはパーシャルとコンポーネントにいくつかのパラメータを渡します:

  * `new` と `edit` ページに対して:

    * `form`:       現在のモデルオブジェクトに関連づけされているフォーム
    * `attributes`: ウィジェットに適用される HTML 属性の配列

  * `list`ページに対して:

    * `type`:       `list`
    * `MODEL_NAME`: 現在のオブジェクトのインスタンスで、`MODEL_NAME` はモデルクラスの名前の小文字バージョン。

`edit` もしくは `new` ページにおいて、2カラムレイアウトを保ちたい場合 (フィールドラベルとウィジェット)、パーシャルもしくはコンポーネントテンプレートは次のようになります:

    [php]
    <div class="sf_admin_form_row">
      <label>
        <!-- 最初のカラムに表示されるフィールドラベルもしくはコンテンツ -->
      </label>
      <!-- 2番目のカラムに表示されるフィールドウィジェットもしくはコンテンツ -->
    </div>

### オブジェクトプレースホルダ

オプションのなかにはモデルオブジェクトプレースホルダをとるものがあります。プレースホルダは `%%NAME%%` のパターンにしたがう文字列です。`NAME` はオブジェクトのゲッターメソッドに変換される任意の文字列になります (`get` の後にラクダ記法の `NAME` 文字列をつけます)。たとえば `%%title%%` は `$article->getTitle()` の値に置き換わります。実行時において、現在のコンテキストに関連するオブジェクトに合わせて、プレースホルダの値は動的に置き換わります。

>**TIP**
>モデルが別のモデルへの外部キーをもつとき、Propel と Doctrine は関連オブジェクトのゲッターを定義します。ほかのゲッターに関して、オブジェクトを文字列に変換する `__toString()` メソッドが定義されていれば、ゲッターをプレースホルダに使うことができます。

### コンフィギュレーションの継承

アドミンジェネレータのコンフィギュレーションはコンフィギュレーションカスケードの原則にしたがいます。継承ルールは次のとおりです:

 * `new` と `edit` は `form` を継承し、`form` は `fields` を継承します。
 * `list` は `fields` を継承します。
 * `filter` は `fields` を継承します。

### ~クレデンシャル~

`credential` オプション (下記を参照) を使うことで、ユーザークレデンシャルに合わせて (リストとフォームの) アドミンジェネレータのアクションを隠すことができます。しかしながら、リンクもしくはボタンが現れない場合でも、不正なアクセスからアクションを守らなければなりません。アドミンジェネレータのクレデンシャル管理機能は表示だけを担当します。

list ページのカラムを隠すのにも `credential` オプションを使うことができます。

### アクションのカスタマイズ

コンフィギュレーションが十分ではないとき、生成メソッドをオーバーライドできます:

 | メソッド               | 説明
 | ---------------------- | -------------------------------------
 | `executeIndex()`       | `list` ビューアクション
 | `executeFilter()`      | フィルタを更新する
 | `executeNew()`         | `new` ビューアクション
 | `executeCreate()`      | 新しいレコードを作る
 | `executeEdit()`        | `edit` ビューアクション
 | `executeUpdate()`      | レコードを更新する
 | `executeDelete()`      | レコードを削除する
 | `executeBatch()`       | バッチアクションを実行する
 | `executeBatchDelete()` | `_delete` バッチアクションを実行する
 | `processForm()`        | レコードフォームを処理する
 | `getFilters()`         | 現在のフィルタを返す
 | `setFilters()`         | フィルタをセットする
 | `getPager()`           | list ページャを返す
 | `getPage()`            | ページャページを取得する
 | `setPage()`            | ページャページをセットする
 | `buildCriteria()`      | list の `Criteria` をビルドする
 | `addSortCriteria()`    | list のソート `Criteria` を追加する
 | `getSort()`            | 現在のソートカラムを返す
 | `setSort()`            | 現在のソートカラムをセットする

### テンプレートのカスタマイズ

それぞれの生成テンプレートを上書きできます:

 | テンプレート                 | 説明
 | ---------------------------- | ---------------------------------------------
 | `_assets.php`                | テンプレートに使う CSS と JS をレンダリングする
 | `_filters.php`               | フィルタボックスをレンダリングする
 | `_filters_field.php`         | 単独のフィルタフィールドをレンダリングする
 | `_flashes.php`               | フラッシュメッセージをレンダリングする
 | `_form.php`                  | フォームを表示する
 | `_form_actions.php`          | フォームのアクションを表示する
 | `_form_field.php`            | 単独のフォームフィールドを表示する
 | `_form_fieldset.php`         | フォームのフィールドセットを表示する
 | `_form_footer.php`           | フォームのフッターを表示する
 | `_form_header.php`           | フォームのヘッダーを表示する
 | `_list.php`                  | list を表示する
 | `_list_actions.php`          | list アクションを表示する
 | `_list_batch_actions.php`    | list バッチアクションを表示する
 | `_list_field_boolean.php`    | list のなかの単独のブール型フィールドを表示する
 | `_list_footer.php`           | list のフッターを表示する
 | `_list_header.php`           | list のヘッダーを表示する
 | `_list_td_actions.php`       | 列のオブジェクトアクションを表示する
 | `_list_td_batch_actions.php` | 列のチェックボックスを表示する
 | `_list_td_stacked.php`       | 列のスタックレイアウトを表示する
 | `_list_td_tabular.php`       | list の単独フィールドを表示する
 | `_list_th_stacked.php`       | ヘッダーの単独のカラム名を表示する
 | `_list_th_tabular.php`       | ヘッダーの単独のカラム名を表示する
 | `_pagination.php`            | list パジネーション (ページ送り) を表示する
 | `editSuccess.php`            | `edit` ビューを表示する
 | `indexSuccess.php`           | `list` ビューを表示する
 | `newSuccess.php`             | `new` ビューを表示する

### 見た目のカスタマイズ

生成されるテンプレートがたくさんの `class` と `id` 属性を定義するので、アドミンジェネレータの見た目は手軽にカスタマイズできます。

`edit` もしくは `new` ページにおいて、それぞれのフィールドの HTML コンテナには次のクラスが用意されています:

  * `sf_admin_form_row`
  * フィールドの型に依存するクラス: `sf_admin_text`、`sf_admin_boolean`、`sf_admin_date`、`sf_admin_time` もしくは `sf_admin_foreignkey`。
  * `sf_admin_form_field_COLUMN`。`COLUMN` はカラムの名前です。

`list` ページにおいて、それぞれのフィールドの HTML コンテナには次のクラスが用意されています:

  * フィールドの型に依存するクラス: `sf_admin_text`、`sf_admin_boolean`、`sf_admin_date`、`sf_admin_time`、もしくは `sf_admin_foreignkey`
  * `sf_admin_form_field_COLUMN`。`COLUMN` はカラムの名前です。

<div class="pagebreak"></div>

利用可能なコンフィギュレーションオプション
--------------------------------------------

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

*デフォルト*: 人間にわかりやすいカラムの名前

`label` オプションはフィールドに使うラベルを定義します:

    [yml]
    config:
      fields:
        slug: { label: "URL のショートカット" }

### ~`help`~

*デフォルト*: なし

`help` オプションはフィールドに表示するヘルプテキストを定義します。

### ~`attributes`~

*デフォルト*: `array()`

`attributes` オプションはウィジェットに渡す HTML 属性を定義します:

    [yml]
    config:
      fields:
        slug: { attributes: { class: foo } }

### ~`credentials`~

*デフォルト*: なし

`credentials` オプションはフィールドを表示するためにユーザーがもっていなければならない必須のクレデンシャルを定義します。クレデンシャルはオブジェクトのリストに対してのみ強制されます。

    [yml]
    config:
      fields:
        slug:      { credentials: [admin] }
        is_online: { credentials: [[admin, moderator]] }

>**NOTE**
>クレデンシャルは `security.yml` 設定ファイルと同じルールで定義されます。

### ~`renderer`~

*デフォルト*: なし

`renderer` オプションはフィールドをレンダリングするために呼び出す PHP コールバックを定義します。定義されていれば、パーシャルもしくはコンポーネントのようなほかのフラグをオーバーライドします。

コールバックは呼び出される際に `renderer_arguments` オプションで定義されているフィールドと引数の値を渡されます。

### ~`renderer_arguments`~

*デフォルト*: `array()`

`renderer_arguments` オプションはフィールドをレンダリングする際に PHP の `renderer` コールバックに渡す引数を定義します。このオプションは `renderer` オプションが定義されている場合のみ使われます。

### ~`type`~

*デフォルト*: バーチャルカラムの `Text`

`type` オプションはカラムの型を定義します。デフォルトでは、モデルで定義されているカラムの型を使いますが、バーチャルカラムを作る場合、デフォルトの `Text` 型を有効な型のうちの1つでオーバーライドできます:

  * `ForeignKey`
  * `Boolean`
  * `Date`
  * `Time`
  * `Text`
  * `Enum` (Doctrine のみで利用可能)

### ~`date_format`~

*デフォルト*: `f`

`date_format` オプションは日付を表示する際に使うフォーマットを定義します。値は `sfDateFormat` クラスによって認識されるフォーマットです。フィールドの型が `Date` である場合はこのオプションは使われません。

フォーマットには次のトークンを使うことができます:

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

フレームワークは組み込みのアクションをいくつか定義します。これらすべての名前にはプレフィックスのアンダースコア (`_`) がつきます。それぞれのアクションはこの節で説明されているオプションでカスタマイズできます。`list`、`edit` もしくは `new` エントリでアクションを定義する場合にも同じオプションを使うことができます。

### ~`label`~

*デフォルト*: アクションのキー

`label` オプションはアクションに使うラベルを定義します。

### ~`action`~

*デフォルト*: アクションの名前に応じて定義されます。

`action` オプションは実行するアクションの名前を定義します (プレフィックスの `execute` はつけません)。

### ~`credentials`~

*デフォルト*: なし

`credentials` オプションはアクションを表示するためにユーザーがもたなければならないクレデンシャルを定義します。

>**NOTE**
>クレデンシャルは `security.yml` 設定ファイルと同じルールで定義されます。

`list`
------

### ~`title`~

*デフォルト*: 人間にわかりやすく、サフィックスが「List」であるモデルクラスの名前

`title` オプションは list ページのタイトルを定義します。

### ~`display`~

*デフォルト*: すべてのモデルカラム、順序はスキーマファイルでの定義順

`display` オプションは list で表示する順序つきカラムの配列を定義します。

カラムの名前の前に等号 (`=`) をつければ、文字列は現在のオブジェクトの `edit` ページに進むリンクに変換されるようになります。

    [yml]
    config:
      list:
        display: [=name, slug]

>**NOTE**
>カラムを隠す `hide` オプションも参照してください。

### ~`hide`~

*デフォルト*: なし

`hide` オプションは list から隠すカラムを定義します。カラムを隠すのに `display` オプションで表示されるカラムを指定するよりも、こちらのほうが作業が少なくて済むことがあります:

    [php]
    config:
      list:
        hide: [created_at, updated_at]

>**NOTE**
>`display` と `hide` オプションの両方が提供される場合、`hide` オプションは無視されます。

### ~`layout`~

*デフォルト*: `tabular`

*利用可能な値*: ~`tabular`~ もしくは ~`stacked`~

`layout` オプションは list を表示するのに使うレイアウトを定義します。

`tabular` レイアウトでは、それぞれのカラムの値は独自テーブルのカラムに入っています。

`stacked` レイアウトでは、それぞれのオブジェクトは `params` オプション (下記を参照) で定義されている単独の文字列で表されます。

>**NOTE**
>`display` オプションは `stacked` レイアウトを使う際にも必要です。ユーザーがソートできるカラムはこのオプションによって定義されるからです。

### ~`params`~

*デフォルト*: なし

`params` オプションは `stacked` レイアウトに使われる HTML 文字列のパターンを定義します。この文字列にはモデルオブジェクトプレースホルダを入れることができます:

    [yml]
    config:
      list:
        params:  |
          %%title%% written by %%author%% and published on %%published_at%%.

カラムの名前の前に等号 (`=`) をつけると、文字列は現在のオブジェクトの `edit` ページに進むリンクに変換されるようになります。

### ~`sort`~

*デフォルト*: なし

`sort` オプションはデフォルトの sort カラムを定義します。このオプションは2つの要素: カラムの名前とソートの順序 (`asc` もしくは `desc`) から成り立ちます。

    [yml]
    config:
      list:
        sort: [published_at, desc]

### ~`max_per_page`~

*デフォルト*: `20`

`max_per_page` オプションは1つのページで表示するオブジェクトの最大数を定義します。

### ~`pager_class`~

*デフォルト*: Propel では `sfPropelPager`、Doctrine では `sfDoctrinePager`

`pager_class` オプションは list を表示する際に使われるページャクラスを定義します。

### ~`batch_actions`~

*デフォルト*: `{ _delete: ~ }`

`batch_actions` オプションは list のオブジェクト選択で実行できるアクションのリストを定義します。

`action` が定義されていない場合、アドミンジェネレータは `executeBatch` をプレフィックスとするラクダ記法の名前をもつメソッドを探します。

実行されるメソッドは `ids` リクエストパラメータを通して選択されるオブジェクトの主キーを受け取ります。

>**TIP**
>バッチアクションを無効にするには、このオプションを空の配列: `{}` にセットします。

### ~`object_actions`~

*デフォルト*: `{ _edit: ~, _delete: ~ }`

`object_actions` オプションは list のそれぞれのオブジェクトで実行可能なアクションのリストを定義します。

`action` が定義されていない場合、アドミンジェネレータは、`executeList` をプレフィックスとするラクダ記法の名前をもつメソッドを探します。

>**TIP**
>オブジェクトアクションを無効にするには、このオプションを空の配列: `{}` にセットします。

### ~`actions`~

*デフォルト*: `{ _new: ~ }`

新しいオブジェクトの作成のように、`actions` オプションはオブジェクトを受け取らないアクションを定義します。

`action` が定義されていない場合、アドミンジェネレータは、`executeList` をプレフィックスとするラクダ記法の名前のメソッドを探します。

>**TIP**
>オブジェクトアクションの機能を無効にするには、オプションを空の配列: `{}` にセットします。

### ~`peer_method`~

*デフォルト*: `doSelect`

`peer_method` オプションは list で表示するオブジェクトを検索するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Propel 専用です。Doctrine では `table_method` オプションを使います。

### ~`table_method`~

*デフォルト*: `doSelect`

`table_method` オプションは list で表示するオブジェクトを検索するために呼び出すメソッドを定義します。

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

`filter` セクションは list ページに表示されるフォームをフィルタリングする方法を指示するためのコンフィギュレーションを定義します。

### ~`display`~

*デフォルト*: フィルタフォームクラスで定義されているすべてのフィールド、順序は定義順と同じ

`display` オプションは表示するフィールドの順序つきリストを定義します。

>**TIP**
>フィルタフィールドはつねにオプションで、表示するフィールドを設定するためにフィルタフォームクラスをオーバーライドする必要はありません。

### ~`class`~

*デフォルト*: `FormFilter` をサフィックスとするモデルクラスの名前

`class` オプションは `filter` フォームに使うフォームクラスを定義します。

>**TIP**
>フィルタリングを完全に除外するには、このオプションを `false` にセットします。

`form`
------

`form` セクションは `edit` と `new` セクションのフォールバックとしてのみ存在します (最初の継承ルールを参照)。

>**NOTE**
>フォームセクション (`form`、`edit` と `new`) に関して、`label` と `help` オプションはフォームクラスで定義されているものをオーバーライドします。

### ~`display`~

*デフォルト*: フォームクラスで定義されているすべてのクラス。順序は定義の順序と同じ。

`display` オプションは表示するフィールドの順序つきリストを定義します。

このオプションは複数のフィールドをグループにまとめるのにも使うことができます:

    [yml]
    # apps/backend/modules/model/config/generator.yml
    config:
      form:
        display:
          Content: [title, body, author]
          Admin:   [is_published, expires_at]

上記のコンフィギュレーションは2つのグループ (`Content` と `Admin`) を定義します。それぞれのグループには、フォームフィールドのサブセットが用意されています。

>**CAUTION**
>モデルフォームで定義されているすべてのフィールドは `display` オプションに存在しなければなりません。存在しなければ、予期しないバリデーションエラーになる可能性があります。

### ~`class`~

*デフォルト*: `Form` をサフィックスとするモデルクラスの名前

`class` オプションは `edit` と `new` ページに使うフォームクラスを定義します。

>**TIP**
>`class` オプションは `new` と `edit` セクションの両方で定義できますが、1つのクラスを使い、条件ロジックで対応するほうがよいやり方です。

`edit`
------

`edit` セクションは `form` セクションと同じオプションをとります。

### ~`title`~

*デフォルト*: 人間にわかりやすく `Edit` をサフィックスとするモデルクラスの名前

`title` オプションは edit ページのタイトルを定義します。このオプションはモデルオブジェクトのプレースホルダをとることができます。

### ~`actions`~

*デフォルト*: `{ _delete: ~, _list: ~, _save: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。

`new`
-----

`new` セクションは `form` セクションと同じオプションをとります。

### ~`title`~

*デフォルト*: 人間にわかりやすく `New` をサフィックスとするモデルクラスの名前

`title` オプションは新しいページのタイトルを定義します。このオプションはモデルオブジェクトのプレースホルダをとることができます。

>**TIP**
>オブジェクトが新しい場合でも、タイトルの一部として出力したいデフォルトの値を指定することができます。

### ~`actions`~

*デフォルト*: `{ _delete: ~, _list: ~, _save: ~, _save_and_add: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。
