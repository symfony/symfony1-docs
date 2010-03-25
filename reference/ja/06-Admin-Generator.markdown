generator.yml 設定ファイル
==========================

symfony のアドミンジェネレータはモデルクラスのバックエンドインターフェイスの作成を可能にします。これは Propel もしくは Doctrine を ORM として使うことで機能します。

### 作成

アドミンジェネレータモジュールは `propel:generate-admin` もしくは `doctrine:generate-admin` タスクによって作られます:

    $ php symfony propel:generate-admin backend Article

    $ php symfony doctrine:generate-admin backend Article

上記のコマンドは `Article` モデルクラスの `article` アドミンジェネレータモジュールを作ります。

>**NOTE**
>`generator.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は ~`sfGeneratorConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

### 設定ファイル

上記のようなモジュールのコンフィギュレーションの変更は `apps/backend/modules/model/article/generator.yml` ファイルで行います:

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        # パラメータの配列

ファイルには2つのメインエントリ: `class` と `param` が収められています。クラスは Propel では `sfPropelGenerator` で Doctrine では `sfDoctrineGenerator` です。

`param` エントリは生成モジュールのコンフィギュレーションオプションを収めます。`model_class` はこのモジュールに結びつけられるモデルクラスを定義し、`theme` オプションはデフォルトで使うテーマを定義します。

しかしメインコンフィギュレーションの変更は `config` エントリの下で行います。エントリは7つのセクションにわかれています:

  * `actions`: リストとフォームで見つかるアクションのデフォルトコンフィギュレーション
  * `fields`:  フィールドのデフォルトコンフィギュレーション
  * `list`:    リストのコンフィギュレーション
  * `filter`:  フィルタのコンフィギュレーション
  * `form`:    新規ページと編集フォームのコンフィギュレーション
  * `edit`:    編集ページ固有のコンフィギュレーション
  * `new`:     新規ページ固有のコンフィギュレーション

最初に生成されるとき、すべてのセクションは空として定義されます。アドミンジェネレータはすべての実行可能なオプションに応じて適切なデフォルトを定義します:

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

この章では `config` エントリを通してアドミンジェネレータをカスタマイズするために利用可能なすべてのオプションを説明します。

>**NOTE**
>何らかの説明がなければ、すべてのオプションは Propel と Doctrine の両方で同じように動きます。

### フィールド

多くのオプションはフィールドのリストを引数にとります。フィールドは実際のカラム名もしくは仮想的な名前になります。両方のケースにおいてゲッターはモデルクラスのなかで定義しなければなりません (`get` の後にラクダ記法のフィールド名をつける)。

アドミンジェネレータはフィールドをレンダリングする方法をコンテキストにもとづいて自己判断します。レンダリングをカスタマイズするにはパーシャルもしくはコンポーネントを作ります。慣習では、パーシャルの名前には接頭辞のアンダースコア (`_`) を、コンポーネントの名前には接頭辞のチルダ (`~`) をつけます:

    [yml]
    display: [_title, ~content]

上記の例において、`title` フィールドは `title` パーシャルによってレンダリングされ、`content` フィールドは `content` コンポーネントによってレンダリングされます。

アドミンジェネレータはパーシャルとコンポーネントにいくつかのパラメータを渡します:

  * `new` と `edit` ページに対して:

    * `form`:       現在のモデルオブジェクトに関連づけられているフォーム
    * `attributes`: ウィジェットに適用される HTML 属性の配列

  * `list`ページに対して:

    * `type`:       `list`
    * `MODEL_NAME`: 現在のオブジェクトのインスタンスで、`MODEL_NAME` はモデルクラスの名前の小文字バージョン。

`edit` もしくは `new` ページにおいて、2カラムレイアウトを維持したい場合 (フィールドラベルとウィジェット)、パーシャルもしくはコンポーネントテンプレートは次のようになります:

    [php]
    <div class="sf_admin_form_row">
      <label>
        <!-- 最初のカラムに表示されるフィールドラベルもしくはコンテンツ -->
      </label>
      <!-- 2番目のカラムに表示されるフィールドウィジェットもしくはコンテンツ -->
    </div>

### オブジェクトプレースホルダ

オプションのなかにはモデルオブジェクトプレースホルダをとるものがあります。プレースホルダは `%%NAME%%` のパターンにしたがう文字列です。`NAME` はオブジェクトのゲッターメソッドに変換される任意の文字列になります (`get` の後にラクダ記法の `NAME` 文字列をつけます)。たとえば `%%title%%` は `$article->getTitle()` の値に置き換わります。実行時において、現在のコンテキストに関連するオブジェクトにしたがってプレースホルダの値は動的に置き換わります。

>**TIP**
>モデルが別のモデルへの外部キーをもつとき、Propel と Doctrine は関連オブジェクトのゲッターを定義します。ほかのゲッターに関して、オブジェクトを文字列に変換する `__toString()` メソッドが定義されていればゲッターをプレースホルダとして使うことができます。

### コンフィギュレーションの継承

アドミンジェネレータのコンフィギュレーションはコンフィギュレーションカスケードの原則にもとづきます。継承ルールは次のとおりです:

 * `new` と `edit` は `form` を継承し `form` は `fields` を継承します
 * `list` は `fields` を継承します
 * `filter` は `fields` を継承します

### ~クレデンシャル~

`credential` オプション (下記を参照) を使うユーザークレデンシャルにもとづいて、(リストとフォームの) アドミンジェネレータのアクションを隠すことができます。しかしながら、リンクもしくはボタンが現れない場合でも、違法なアクセスからアクションを適切にセキュアな状態にしなければなりません。アドミンジェネレータのクレデンシャル管理機能は表示のみを処理します。

list ページのカラムを隠すのに `credential` オプションを使うことができます。

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
 | `_list_field_boolean.php`    | list の単独のブール型フィールドを表示する
 | `_list_footer.php`           | list のフッターを表示する
 | `_list_header.php`           | list のヘッダーを表示する
 | `_list_td_actions.php`       | 列のオブジェクトアクションを表示する
 | `_list_td_batch_actions.php` | 列のチェックボックスを表示する
 | `_list_td_stacked.php`       | 列のスタックレイアウトを表示する
 | `_list_td_tabular.php`       | list の単独フィールドを表示する
 | `_list_th_stacked.php`       | ヘッダーの単独のカラム名を表示する
 | `_list_th_tabular.php`       | ヘッダーの単独のカラム名を表示する
  | `_pagination.php`            | list パジネーションを表示する
 | `editSuccess.php`            | `edit` ビューを表示する
 | `indexSuccess.php`           | `list` ビューを表示する
 | `newSuccess.php`             | `new` ビューを表示する

### 見た目のカスタマイズ

生成されるテンプレートが多くの `class` と `id` 属性を定義するので、アドミンジェネレータの見た目はとても簡単にカスタマイズできます。

`edit` もしくは `new` ページにおいて、それぞれのフィールドの HTML コンテナには次のクラスが収められています:

  * `sf_admin_form_row`
  * フィールドの型に依存するクラス: `sf_admin_text`、`sf_admin_boolean`、`sf_admin_date`、`sf_admin_time` もしくは `sf_admin_foreignkey`。
  * `sf_admin_form_field_COLUMN`。`COLUMN` はカラムの名前です。

`list` ページにおいて、それぞれのフィールドの HTML コンテナには次のクラスが収められています:

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

`fields` セクションはそれぞれのフィールドのデフォルトコンフィギュレーションを定義します。このコンフィギュレーションはすべてのページに対して定義されページごとにオーバーライドできます (`list`、`filter`、`form`、`edit` と `new`)。

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

`credentials` オプションはフィールドを表示するためにユーザーがもたなければならないクレデンシャルを定義します。クレデンシャルはオブジェクトのリストに対してのみ強制されます。

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

コールバックは `renderer_arguments` オプションで定義されるフィールドと引数の値で呼び出されます。

### ~`renderer_arguments`~

*デフォルト*: `array()`

`renderer_arguments` オプションはフィールドをレンダリングする際に PHP の `renderer` コールバックに渡す引数を定義します。このオプションは `renderer` オプションが定義される場合のみ使われます。

### ~`type`~

*デフォルト*: バーチャルカラムの `Text`

`type` オプションはカラムの型を定義します。デフォルトでは、モデルで定義されるカラムの型を使いますが、バーチャルカラムを作る場合、デフォルトの `Text` 型を有効な型のうちの1つでオーバーライドできます:

  * `ForeignKey`
  * `Boolean`
  * `Date`
  * `Time`
  * `Text`
  * `Enum` (Doctrine のみで利用可能)

### ~`date_format`~

*デフォルト*: `f`

`date_format` オプションは日付を表示するときに使うフォーマットを定義します。値は `sfDateFormat` クラスによって認識されるフォーマットになります。フィールドの型が `Date` であるときはこのオプションは使われません。

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

フレームワークは組み込みのアクションをいくつか定義します。これらすべての名前に接頭辞のアンダースコア (`_`) がつきます。 それぞれのアクションはこのセクションで説明されるオプションでカスタマイズできます。同じオプションは `list`、`edit` もしくは `new` エントリでアクションを定義する際に使うことができます。

### ~`label`~

*デフォルト*: アクションのキー

`label` オプションはアクションに使うラベルを定義します。

### ~`action`~

*デフォルト*: アクションの名前にもとづいて定義されます。

`action` オプションは実行するアクションの名前 (接頭辞の `execute` はつけません) を定義します。

### ~`credentials`~

*デフォルト*: なし

`credentials` オプションはアクションを表示するためにユーザーがもたなければならないクレデンシャルを定義します。

>**NOTE**
>クレデンシャルは `security.yml` 設定ファイルと同じルールで定義されます。

`list`
------

### ~`title`~

*デフォルト*: 人間にわかりやすく接尾辞が「List」であるモデルクラスの名前

`title` オプションは list ページのタイトルを定義します。

### ~`display`~

*デフォルト*: すべてのモデルカラム、順序はスキーマファイルでの定義順

`display` オプションは list で表示する順序つきカラムの配列を定義します。

カラム名の前につけられている等号 (`=`) は文字列が現在のオブジェクトの `edit` ページに進むリンクに変換されることを意味します。

    [yml]
    config:
      list:
        display: [=name, slug]

>**NOTE**
>カラムを隠す `hide` オプションもご覧ください。

### ~`hide`~

*デフォルト*: なし

`hide` オプションは list から隠すカラムを定義します。カラムを隠すには `display` オプションで表示されるカラムを指定するよりも、こちらのほうが速いことがあります:

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

`tabular` レイアウトでは、それぞれのカラムの値は独自テーブルのカラムに収められています。

`stacked` レイアウトでは、それぞれのオブジェクトは `params` オプション (下記を参照) で定義される単独文字列で表現されます。

>**NOTE**
>`stacked` レイアウトを使う際にも `display` オプションは必要です。このオプションがユーザーによってソート可能になるカラムを定義するからです。

### ~`params`~

*デフォルト*: なし

`params` オプションは `stacked` レイアウトを利用する際に HTML 文字列のパターンを定義するために使われます。この文字列はモデルオブジェクトプレースホルダに収めることができます:

    [yml]
    config:
      list:
        params:  |
          %%title%% written by %%author%% and published on %%published_at%%.

カラム名の前につけられている等号 (`=`) は文字列が現在のオブジェクトの `edit` ページに進むリンクに変換されることを意味します。

### ~`sort`~

*デフォルト*: なし

`sort` オプションはデフォルトの sort カラムを定義します。これは2つのコンポーネント: カラムの名前とソートの順序 (`asc` もしくは `desc`) から構成されます。

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

`action` が定義されていない場合、アドミンジェネレータは `executeBatch` を接頭辞とするラクダ記法の名前をもつメソッドを探します。

実行されるメソッドは `ids` リクエストパラメータを通して選択されるオブジェクトの主キーを受け取ります。

>**TIP**
>バッチアクションの機能はオプションを空の配列: `{}` にセットすることで無効にできます。

### ~`object_actions`~

*デフォルト*: `{ _edit: ~, _delete: ~ }`

`object_actions` オプションは list のそれぞれのオブジェクトで実行可能なアクションのリストを定義します。

`action` が定義されていない場合、アドミンジェネレータは `executeList` を接頭辞とするラクダ記法の名前をもつメソッドを探します。

>**TIP**
>オブジェクトアクションの機能はオプションを空の配列: `{}` にセットすることで無効にできます。

### ~`actions`~

*デフォルト*: `{ _new: ~ }`

新しいオブジェクトの作成のように、`actions` オプションはオブジェクトをとらないアクションを定義します。

`action` が定義されていない場合、アドミンジェネレータは `executeList` を接頭辞とするラクダ記法の名前のメソッドを探します。

>**TIP**
>オブジェクトアクションの機能はオプションを空の配列: `{}` にセットすることで無効にできます。

### ~`peer_method`~

*デフォルト*: `doSelect`

`peer_method` オプションは list で表示するオブジェクトを検索するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Propel にのみ存在します。Doctrine では `table_method` オプションを使います。

### ~`table_method`~

*デフォルト*: `doSelect`

`table_method` オプションは list で表示するオブジェクトを検索するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Doctrine にのみ存在します。Propel では `peer_method` オプションを使います。

### ~`peer_count_method`~

*デフォルト*: `doCount`

`peer_count_method` オプションは現在のフィルタオブジェクトの個数を算出するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Propel にのみ存在します。Doctrine では `table_count_method` オプションを使います。

### ~`table_count_method`~

*デフォルト*: `doCount`

`table_count_method` オプションは現在のフィルタオブジェクトの個数を算出するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Doctrine にのみ存在します。Propel では、`peer_count_method` オプションを使います。

`filter`
--------

`filter` セクションは list ページに表示されるフォームをフィルタリングするためのコンフィギュレーションを定義します。

### ~`display`~

*デフォルト*: フィルタフォームクラスで定義されるすべてのフィールド、順序は定義順と同じ

`display` オプションは表示するフィールドの順序つきリストを定義します。

>**TIP**
>フィルタフィールドはつねにオプションで、表示するフィールドを設定するためにフィルタフォームクラスをオーバーライドする必要はありません。

### ~`class`~

*デフォルト*: `FormFilter` を接尾辞とするモデルクラスの名前

`class` オプションは `filter` フォームに使うフォームクラスを定義します。

>**TIP**
>フィルタリングを完全に除外するには、`class` を `false` にセットします。

`form`
------

`form` セクションは `edit` と `new` セクションのフォールバックとしてのみ存在します (最初の継承ルールを参照)。

>**NOTE**
>フォームセクション (`form`、`edit` と `new`) に関して、`label` と `help` オプションはフォームクラスで定義されるものをオーバーライドします。

### ~`display`~

*デフォルト*: フォームクラスで定義されるすべてのクラス。順序は定義の順序と同じ。

`display` オプションは表示するフィールドの順序つきリストを定義します。

このオプションはフィールドを複数のグループにわけるのにも使うことができます:

    [yml]
    # apps/backend/modules/model/config/generator.yml
    config:
      form:
        display:
          Content: [title, body, author]
          Admin:   [is_published, expires_at]

上記のコンフィギュレーションは2つのグループ (`Content` と `Admin`) を定義します。それぞれのグループにフォームフィールドのサブセットが収められます。

>**CAUTION**
>モデルフォームで定義されるすべてのフィールドは `display` オプションに存在しなければなりません。そうではない場合、予期しないバリデーションエラーになる可能性があります。

### ~`class`~

*デフォルト*: `Form` を接尾辞とするモデルクラスの名前

`class` オプションは `edit` と `new` ページに使うフォームクラスを定義します。

>**TIP**
>`new` と `edit` セクションの両方で `class` オプションを定義できますが、1 つのクラスを使い条件ロジックで対応するほうがよいです。

`edit`
------

`edit` セクションは `form` セクションと同じオプションをとります。

### ~`title`~

*デフォルト*: 人間にわかりやすく `Edit` を接尾辞とするモデルクラスの名前

`title` オプションは edit ページのタイトルの見出しを定義します。これはモデルオブジェクトのプレースホルダを収めることができます。

### ~`actions`~

*デフォルト*: `{ _delete: ~, _list: ~, _save: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。

`new`
-----

`new` セクションは `form` セクションと同じオプションをとります。

### ~`title`~

*デフォルト*: 人間にわかりやすく `New` を接尾辞とするモデルクラスの名前

`title` オプションは新しいページのタイトルを定義します。これはモデルオブジェクトのプレースホルダを収めることができます。

>**TIP**
>オブジェクトが新しい場合でも、タイトルの一部として出力したいデフォルトの値を収めることができます。

### ~`actions`~

*デフォルト*: `{ _delete: ~, _list: ~, _save: ~, _save_and_add: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。
