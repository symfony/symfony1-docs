generator.yml 設定ファイル
==========================

symfony のアドミンジェネレーターはモデルクラスのバックエンドインターフェイスの作成を可能にします。これは Propel もしくは Doctrine を ORM として使うことで機能します。

### 作成

アドミンジェネレーターモジュールは `propel:generate-admin` もしくは `doctrine:generate-admin` タスクによって作られます:

    $ php symfony propel:generate-admin backend Article

    $ php symfony doctrine:generate-admin backend Article

上記のコマンドは `Article` モデルクラスの `article` のアドミンジェネレーターモジュールを作ります。

>**NOTE**
>`generator.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は ~`sfGeneratorConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

### 設定ファイル

上記のようなモジュールのコンフィギュレーションは `apps/backend/modules/model/article/generator.yml` ファイルで行います:

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        # パラメーターの配列

ファイルは 2 つのメインエントリ: `class` と `param` を収めます。クラスは Propel に対して `sfPropelGenerator` で Doctrine に対しては `sfDoctrineGenerator` です。

`param` エントリは生成モジュールの設定オプションを収めます。`model_class` はこのモジュールにバインドされるモデルクラスを定義し、`theme` オプションはデフォルトで使うテーマを定義します。

しかしメインのコンフィギュレーションは `config` エントリの下にあります。これは 7 つのセクションにわかれます:

  * `actions`: リストとフォームで見つかるアクションのデフォルトコンフィギュレーション
  * `fields`:  フィールドのデフォルトコンフィギュレーション
  * `list`:    リストのコンフィギュレーション
  * `filter`:  フィルターのコンフィギュレーション
  * `form`:    新規ページと編集フォームのコンフィギュレーション
  * `edit`:    編集ページ固有のコンフィギュレーション
  * `new`:     新規ページ固有のコンフィギュレーション

最初に生成されるとき、すべてのセクションは空であるとして定義されます。アドミンジェネレーターはすべての実行可能なオプションに対して適切なデフォルトを定義します:

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

この章では `config` エントリを通してアドミンジェネレーターをカスタマイズするために使うことのできるすべての利用可能なオプションを説明します。

>**NOTE**
>すべてのオプションは Propel と Doctrine の両方で利用可能で言及していなければ同じ動作をします。

### フィールド

多くのオプションは引数としてフィールドのリストを受け取ります。フィールドは実際のカラム名もしくは仮想的な名前になります。両方のケースにおいてゲッターはモデルクラスで定義しなければなりません (`get` の後にラクダ記法のフィールド名が続く)。

アドミンジェネレーターはコンテキストにもとづき、フィールドをレンダリングするためのスマートなやり方を知っています。レンダリングをカスタマイズするには、パーシャルもしくはコンポーネントを作ります。慣習では、パーシャルにはプレフィックスとしてアンダースコア (`_`) を、コンポーネントにはプレフィックスとしてチルダ (``) をつけます:

    [yml]
    display: [_title, ~content]

上記の例において、`title` フィールドは `title` パーシャルによってレンダリングされ、`content` フィールドは `content` コンポーネントによってレンダリングされます。

アドミンジェネレーターはパーシャルとコンポーネントに対していくつかのパラメーターを渡します:

  * `new` と `edit` ページに対して:

    * `form`:       現在のモデルオブジェクトに関連づけられているフォーム
    * `attributes`: ウィジェットに適用される HTML 属性の配列

  * `list`ページに対して:

    * `type`:       `list`
    * `MODEL_NAME`: 現在のオブジェクトのインスタンス、`MODEL_NAME` はモデルクラスの小文字版の名前。

`edit` もしくは `new` ページにおいて、2 つのカラムレイアウトを維持したい場合 (フィールドラベルとウィジェット)、パーシャルもしくはコンポーネントテンプレートは次のテンプレートにしたがいます:

    [php]
    <div class="sf_admin_form_row">
      <label>
        <!-- Field label or content to be displayed in the first column -->
      </label>
      <!-- Field widget or content to be displayed in the second column -->
    </div>

### オブジェクトプレースホルダー

オプションのなかにはモデルオブジェクトプレースホルダーを受け取ることができるものがあります。プレースホルダーは `%%NAME%%` のパターンにしたがう文字列です。`NAME` の文字列はオブジェクトのゲッターメソッドの名前に使われる妥当な文字列になります (`get` の後にラクダ記法の `NAME` 文字列が続く)。たとえば、`%%title%%` は `$article->getTitle()` の値に置き換わります。現在のコンテキストに関連するオブジェクトにしたがってプレースホルダーの値は実行時に動的に置き換わります。

>**TIP**
>モデルが別のモデルへの外部キーを持つとき、Propel と Doctrine は関連オブジェクトのゲッターを定義します。ほかのゲッターに関して、オブジェクトを文字列に変換する意味のある `__toString()` メソッドを定義していればプレースホルダーとして使うことができます。

### コンフィギュレーションの継承

アドミンジェネレーターのコンフィギュレーションはコンフィギュレーションカスケードの原則にもとづきます。継承ルールは次の通りです:

 * `new` と `edit` は `form` を継承し `form` は `fields` を継承する
 * `list` は `fields` を継承する
 * `filter` は `fields` を継承する

### ~クレデンシャル~

`credential` オプション (下記を参照) を使うユーザークレデンシャルにもとづいて、(リストとフォームの) アドミンジェネレーターのアクションを隠すことができます。しかしながら、リンクもしくはボタンが現れないとしても、違法なアクセスからアクションが適切にセキュアな状態でなければなりません。アドミンジェネレーターのクレデンシャル管理機能は表示のみを処理します。

`credential` オプションは list ページのカラムを隠すのにも使うことができます。

### アクションのカスタマイズ

設定が十分ではないとき、生成メソッドをオーバーライドできます:

 | メソッド               | 説明
 | ---------------------- | -------------------------------------
 | `executeIndex()`       | `list` ビューアクション
 | `executeFilter()`      | フィルターを更新する
 | `executeNew()`         | `new` ビューアクション
 | `executeCreate()`      | 新しいレコードを作成する
 | `executeEdit()`        | `edit` ビューアクション
 | `executeUpdate()`      | レコードを更新する
 | `executeDelete()`      | レコードを削除する
 | `executeBatch()`       | バッチアクションを実行する
 | `executeBatchDelete()` | `_delete` バッチアクションを実行する
 | `processForm()`        | Job フォームを処理する
 | `getFilters()`         | 現在のフィルターを返す
 | `setFilters()`         | フィルターをセットする
 | `getPager()`           | list ページャーを返す
 | `getPage()`            | ページャーページを取得する
 | `setPage()`            | ページャーページをセットする
 | `buildCriteria()`      | list の `Criteria` をビルドする
 | `addSortCriteria()`    | list のソートの `Criteria` を追加する
 | `getSort()`            | 現在のソートカラムを返す
 | `setSort()`            | 現在のソートカラムをセットする

### テンプレートのカスタマイズ

それぞれの生成テンプレートを上書きできます:

 | テンプレート                 | 説明
 | ---------------------------- | ---------------------------------------------
 | `_assets.php`                | テンプレートに使う CSS と JS をレンダリングする
 | `_filters.php`               | フィルターボックスをレンダリングする
 | `_filters_field.php`         | 単独のフィルターフィールドをレンダリングする
 | `_flashes.php`               | flash メッセージをレンダリングする
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
 | `_pagination.php`            | listページパジネーションを表示する
 | `editSuccess.php`            | `edit` ビューを表示する
 | `indexSuccess.php`           | `list` ビューを表示する
 | `newSuccess.php`             | `new` ビューを表示する

### 外見のカスタマイズ

生成されるテンプレートは多くの `class` と `id` 属性を定義するのでアドミンジェネレーターの外見はとても簡単にカスタマイズできます。

`edit` もしくは `new` ページにおいて、それぞれのフィールドの HTML コンテナーには次のクラスが収められています:

  * `sf_admin_form_row`
  * フィールドの型に依存するクラス: `sf_admin_text`、`sf_admin_boolean`、`sf_admin_date`、`sf_admin_time` もしくは `sf_admin_foreignkey`。
  * `sf_admin_form_field_COLUMN`。`COLUMN` がカラムの名前です。

`list` ページにおいて、それぞれのフィールドの HTML コンテナーには次のクラスが収められています:

  * フィールドの型に依存するクラス: `sf_admin_text`、`sf_admin_boolean`、`sf_admin_date`、`sf_admin_time`、もしくは `sf_admin_foreignkey`
  * `sf_admin_form_field_COLUMN`。`COLUMN` がカラムの名前です。

<div class="pagebreak"></div>

利用可能なコンフィギュレーションオプション
-----------------------------------------

 * [`actions`](#chapter_06_actions)

   * [`name`](#chapter_06_sub_name)
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

*デフォルト値*: 人間にわかりやすいカラムの名前

`label` オプションはフィールドに使うラベルを定義します:

    [yml]
    config:
      fields:
        slug: { label: "URL shortcut" }

### ~`help`~

*デフォルト値*: なし

`help` オプションはフィールドに表示するヘルプテキストを定義します。

### ~`attributes`~

*デフォルト値*: `array()`

`attributes` オプションはウィジェットに渡す HTML 属性を定義します:

    [yml]
    config:
      fields:
        slug: { attributes: { class: foo } }

### ~`credentials`~

*デフォルト値*: なし

`credentials` オプションは表示するフィールドに対してユーザーが持たなければならないクレデンシャルを定義します。クレデンシャルはオブジェクトのリストに対してのみ強制されます。

    [yml]
    config:
      fields:
        slug:      { credentials: [admin] }
        is_online: { credentials: [[admin, moderator]] }

>**NOTE**
>クレデンシャルは `security.yml` 設定ファイルと同じルールで定義されます。

### ~`renderer`~

*デフォルト値*: なし

`renderer` オプションはフィールドをレンダリングするために呼び出す PHP コールバックを定義します。定義されていれば、パーシャルもしくはコンポーネントのようにほかのフラグをオーバーライドします。

コールバックは `renderer_arguments` オプションで定義されるフィールドと引数の値で呼び出されます。

### ~`renderer_arguments`~

*デフォルト値*: `array()`

`renderer_arguments` オプションはフィールドをレンダリングする際に PHP の `renderer` コールバックに渡す引数を定義します。`renderer` オプションが定義される場合のみ使われます。

### ~`type`~

*デフォルト*: バーチャルカラムの `Text`

`type` オプションはカラムの型を定義します。デフォルトでは、モデルで定義されるカラムの型を使いますが、バーチャルカラムを作る場合、デフォルトの `Text` 型を有効な型の 1 つでオーバーライドできます:

  * `ForeignKey`
  * `Boolean`
  * `Date`
  * `Time`
  * `Text`
  * `Enum` (Doctrine のみで利用可能)

### ~`date_format`~

*デフォルト値*: `f`

`date_format` オプションは日付を表示するときに使うフォーマットを定義します。これは `sfDateFormat` クラスによって認識されるフォーマットになります。このオプションはフィールドの型が `Date` であるときは使われません。

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

フレームワークは組み込みのアクションをいくつか定義します。これらすべてにプレフィックスとしてアンダースコア (`_`) がつけられます。 それぞれのアクションはこのセクションで説明するオプションでカスタマイズできます。同じオプションは `list`、`edit` もしくは `new` エントリでアクションを定義する際に使うことができます。

### ~`label`~

*デフォルト値*: アクションのキー

`label` オプションはアクションに使うラベルを定義します。

### ~`action`~

*デフォルト値*: アクションの名前にもとづいて定義されます。

`action` オプションはプレフィックスの `execute` なしで実行するアクションの名前を定義します。

### ~`credentials`~

*デフォルト値*: なし

`credentials` オプションは表示するアクションに対してユーザーが持たなければならないクレデンシャルを定義します。

>**NOTE**
>クレデンシャルは `security.yml` 設定ファイルと同じルールで定義されます。

`list`
------

### ~`title`~

*デフォルト値*: サフィックスの「List」がつけられた人間にわかりやすいモデルクラスの名前

`title` オプションは list ページのタイトルを定義します。

### ~`display`~

*デフォルト値*: すべてのモデルのカラム、スキーマファイルでの定義順

`display` オプションは list で表示する順序つきカラムの配列を定義します。

カラム前の等号 (`=`) は文字列を現在のオブジェクトの `edit` ページに向かうリンクに変換する規約です。

    [yml]
    config:
      list:
        display: [=name, slug]

>**NOTE**
>カラムを隠す `hide` オプションもご覧ください。

### ~`hide`~

*デフォルト値*: なし

`hide` オプションは list から隠すカラムを定義します。カラムを隠すのに `display` オプションで表示されるカラムを指定するよりも、こちらのほうが速いことがあります:

    [php]
    config:
      list:
        hide: [created_at, updated_at]

>**NOTE**
>`display` と `hide` オプションが両方とも提供される場合、`hide` オプションが無視されます。

### ~`layout`~

*デフォルト値*: `tabular`

*可能な値*: ~`tabular`~ もしくは ~`stacked`~

`layout` オプションは list を表示するのに使うレイアウトを定義します。

`tabular` レイアウトでは、それぞれのカラムの値は独自テーブルのカラムにあります。

`stacked` レイアウトでは、それぞれのオブジェクトは `params` オプション (下記を参照) で定義される単独文字列で表現されます。

>**NOTE**
>`stacked` レイアウトを使う際にも `display` オプションは必要です。このオプションはユーザーによってソート可能になるカラムを定義するからです。

### ~`params`~

*デフォルト値*: なし

`params` オプションは `stacked` レイアウトを使う際に HTML 文字列のパターンを定義するために使われます。この文字列はモデルオブジェクトプレースホルダーに収めることができます:

    [yml]
    config:
      list:
        params:  |
          %%title%% written by %%author%% and published on %%published_at%%.

カラムの前の等号 (`=`) は文字列を現在のオブジェクトの `edit` ページに向かうリンクに変換する規約です。

### ~`sort`~

*デフォルト値*: なし

`sort` オプションはデフォルトの sort カラムを定義します。これは 2 つのコンポーネントから構成されます: カラムの名前とソートの順序: `asc` もしくは `desc`:

    [yml]
    config:
      list:
        sort: [published_at, desc]

### ~`max_per_page`~

*デフォルト値*: `20`

`max_per_page` オプションは 1 つのページを表示するオブジェクトの最大数を定義します。

### ~`pager_class`~

*デフォルト値*: Propel では `sfPropelPager`、Doctrine では `sfDoctrinePager`

`pager_class` オプションは list を表示する際に使われるページャークラスを定義します。

### ~`batch_actions`~

*デフォルト値*: `{ _delete: ~ }`

`batch_actions` オプションは list のオブジェクト選択で実行できるアクションのリストを定義します。

`action` を定義しない場合、アドミンジェネレーターはプレフィックスが `executeBatch` であるラクダ記法の名前のメソッドを探します。

実行されるメソッドは `ids` リクエストパラメーターを通して選択されたオブジェクトの主キーを受け取ります。

>**TIP**
>バッチアクションの機能はオプションを空の配列: `{}` にセットすることで無効にできます。

### ~`object_actions`~

*デフォルト値*: `{ _edit: ~, _delete: ~ }`

`object_actions` オプションは list のそれぞれのオブジェクトで実行可能なアクションのリストを定義します。

`action` を定義しない場合、アドミンジェネレーターはプレフィックスが `executeList` であるラクダ記法の名前のメソッドを探します。

>**TIP**
>オブジェクトアクションの機能はオプションを空の配列: `{}` にセットすることで無効にできます。

### ~`actions`~

*デフォルト値*: `{ _new: ~ }`

新しいオブジェクトの作成のように、`actions` オプションはオブジェクトを受け取らないアクションを定義します。

`action` が定義されない場合、アドミンジェネレーターは `executeList` をプレフィックスとするラクダ記法の名前のメソッドを探します。

>**TIP**
>オブジェクトアクション機能はオプションを空の配列: `{}` にセットすることで無効にできます。

### ~`peer_method`~

*デフォルト値*: `doSelect`

`peer_method` オプションは list で表示するオブジェクトを読み取るために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Propel にのみ存在します。Doctrine には、`table_method` オプションを使います。

### ~`table_method`~

*デフォルト値*: `doSelect`

`table_method` オプションは list で表示するオブジェクトを読み取るために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Doctrine にのみ存在します。Propel には、`peer_method` オプションを使います。

### ~`peer_count_method`~

*デフォルト値*: `doCount`

`peer_count_method` オプションは現在のフィルターオブジェクトの個数を算出するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Propel にのみ存在します。Doctrine には、`table_count_method` オプションを使います。

### ~`table_count_method`~

*デフォルト値*: `doCount`

`table_count_method` オプションは現在のフィルターオブジェクトの個数を算出するために呼び出すメソッドを定義します。

>**CAUTION**
>このオプションは Doctrine にのみ存在します。Propel には、`peer_count_method` オプションを使います。

`filter`
--------

`filter` セクションは list ページに表示されるフォームをフィルタリングするためのコンフィギュレーションを定義します。

### ~`display`~

*デフォルト値*: 定義の順序で、フィルターフォームクラスで定義されたすべてのフィールド。

`display` オプションは表示するフィールドの順序つきリストを定義します。

>**TIP**
>フィルターフィールドはつねにオプションで、表示するフィールドを設定するためにフィルターフォームクラスをオーバーライドする必要はありません。

### ~`class`~

*デフォルト値*: サフィックスが `FormFilter` であるモデルクラスの名前

`class` オプションは `filter` フォームに使うフォームクラスを定義します。

>**TIP**
>フィルタリング機能を完全に除外するには、`class` を `false` にセットします。

`form`
------

`form` セクションは `edit` と `new` セクションのフォールバックとしてのみ存在します (最初の継承ルールを参照)。

>**NOTE**
>フォームセクション (`form`、`edit` と `new`) に関して、`label` と `help` オプションはフォームクラスで定義されたものをオーバーライドします。

### ~`display`~

*デフォルト値*: フォームクラスで定義されるすべてのクラス。順序は定義された順序と同じ。

`display` オプションは表示するフィールドの順序つきリストを定義します。

このオプションはフィールドをグループに分類するためにも使うことができます:

    [yml]
    # apps/backend/modules/model/config/generator.yml
    config:
      form:
        display:
          Content: [title, body, author]
          Admin:   [is_published, expires_at]

上記のコンフィギュレーションは 2 つのグループ (`Content` と `Admin`) を定義します。それぞれのグループにフォームフィールドのサブセットが収められます。

>**CAUTION**
>モデルフォームで定義されるすべてのフィールドは `display` オプションに存在しなければなりません。そうではない場合、予期しないバリデーションエラーになる可能性があります。

### ~`class`~

*デフォルト値*: サフィックスが `Form` であるモデルクラスの名前

`class` オプションは `edit` と `new` ページに使うフォームクラスを定義します。

>**TIP**
>`new` と `edit` セクションの両方で `class` オプションを定義できますが、1 つのクラスと条件ロジックを使うほうがよいです。

`edit`
------

`edit` セクションは `form` セクションと同じオプションを受け取ります。

### ~`title`~

*デフォルト*: サフィックスが `Edit` である人間にわかりやすいモデルクラスの名前

`title` オプションは edit ページのタイトルの見出しを定義します。これはモデルオブジェクトのプレースホルダーを収めることができます。

### ~`actions`~

*デフォルト値*: `{ _delete: ~, _list: ~, _save: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。

`new`
-----

`new` セクションは `form` セクションと同じオプションを受け取ります。

### ~`title`~

*デフォルト値*: サフィックスが `New` である人間にわかりやすいモデルクラスの名前

`title` オプションは新しいページのタイトルを定義します。これはモデルオブジェクトのプレースホルダーを収めることができます。

>**TIP**
>オブジェクトが新しい場合でも、タイトルの一部として出力したいデフォルトの値を収めることができます。

### ~`actions`~

*デフォルト値*: `{ _delete: ~, _list: ~, _save: ~, _save_and_add: ~ }`

`actions` オプションはフォームを投稿する際に利用可能なアクションを定義します。
