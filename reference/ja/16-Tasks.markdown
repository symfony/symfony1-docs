タスク
======

symfony フレームワークはコマンドラインインターフェイスツールを搭載しています。組み込みのタスクによって開発者はプロジェクト期間に繰り返されるたくさんのタスクを実行できるようになります。

`symfony` コマンドを引数なしで実行すると、利用可能なタスクの一覧が表示されます:

    $ php symfony

`-V`オプションを渡すことで、symfony のバージョンと CLI で使われる symfony ライブラリのパス情報が得られます:

    $ php symfony -V

CLI ツールは最初の引数としてタスクの名前を受け取ります:

    $ php symfony list

タスクの名前は、コロン (`:`) で区切られるオプションの名前空間と名前で構成されます:

    $ php symfony cache:clear

引数とオプションはタスクの名前の後に渡します:

    $ php symfony cache:clear --type=template

CLI ツールは値の有無とオプションの長短バージョンの表記をそれぞれサポートします。

タスクにデバッグ情報を出力するよう指示する `-t` オプションはグローバルオプションです。

<div class="pagebreak"></div>

利用可能なタスク
---------------

 * グローバルタスク
   * [`help`](#chapter_16_sub_help)
   * [`list`](#chapter_16_sub_list)
 * [`app`](#chapter_16_app)
   * [`app::routes`](#chapter_16_sub_app_routes)
 * [`cache`](#chapter_16_cache)
   * [`cache::clear`](#chapter_16_sub_cache_clear)
 * [`configure`](#chapter_16_configure)
   * [`configure::author`](#chapter_16_sub_configure_author)
   * [`configure::database`](#chapter_16_sub_configure_database)
 * [`doctrine`](#chapter_16_doctrine)
   * [`doctrine::build`](#chapter_16_sub_doctrine_build)
   * [`doctrine::build-all`](#chapter_16_sub_doctrine_build_all)
   * [`doctrine::build-all-load`](#chapter_16_sub_doctrine_build_all_load)
   * [`doctrine::build-all-reload`](#chapter_16_sub_doctrine_build_all_reload)
   * [`doctrine::build-all-reload-test-all`](#chapter_16_sub_doctrine_build_all_reload_test_all)
   * [`doctrine::build-db`](#chapter_16_sub_doctrine_build_db)
   * [`doctrine::build-filters`](#chapter_16_sub_doctrine_build_filters)
   * [`doctrine::build-forms`](#chapter_16_sub_doctrine_build_forms)
   * [`doctrine::build-model`](#chapter_16_sub_doctrine_build_model)
   * [`doctrine::build-schema`](#chapter_16_sub_doctrine_build_schema)
   * [`doctrine::build-sql`](#chapter_16_sub_doctrine_build_sql)
   * [`doctrine::clean-model-files`](#chapter_16_sub_doctrine_clean_model_files)
   * [`doctrine::create-model-tables`](#chapter_16_sub_doctrine_create_model_tables)
   * [`doctrine::data-dump`](#chapter_16_sub_doctrine_data_dump)
   * [`doctrine::data-load`](#chapter_16_sub_doctrine_data_load)
   * [`doctrine::delete-model-files`](#chapter_16_sub_doctrine_delete_model_files)
   * [`doctrine::dql`](#chapter_16_sub_doctrine_dql)
   * [`doctrine::drop-db`](#chapter_16_sub_doctrine_drop_db)
   * [`doctrine::generate-admin`](#chapter_16_sub_doctrine_generate_admin)
   * [`doctrine::generate-migration`](#chapter_16_sub_doctrine_generate_migration)
   * [`doctrine::generate-migrations-db`](#chapter_16_sub_doctrine_generate_migrations_db)
   * [`doctrine::generate-migrations-diff`](#chapter_16_sub_doctrine_generate_migrations_diff) 
   * [`doctrine::generate-migrations-models`](#chapter_16_sub_doctrine_generate_migrations_models)
   * [`doctrine::generate-module`](#chapter_16_sub_doctrine_generate_module)
   * [`doctrine::generate-module-for-route`](#chapter_16_sub_doctrine_generate_module_for_route)
   * [`doctrine::insert-sql`](#chapter_16_sub_doctrine_insert_sql)
   * [`doctrine::migrate`](#chapter_16_sub_doctrine_migrate)
   * [`doctrine::rebuild-db`](#chapter_16_sub_doctrine_rebuild_db)
   * [`doctrine::reload-data`](#chapter_16_sub_doctrine_reload_data)
 * [`generate`](#chapter_16_generate)
   * [`generate::app`](#chapter_16_sub_generate_app)
   * [`generate::module`](#chapter_16_sub_generate_module)
   * [`generate::project`](#chapter_16_sub_generate_project)
   * [`generate::task`](#chapter_16_sub_generate_task)
 * [`i18n`](#chapter_16_i18n)
   * [`i18n::extract`](#chapter_16_sub_i18n_extract)
   * [`i18n::find`](#chapter_16_sub_i18n_find)
 * [`log`](#chapter_16_log)
   * [`log::clear`](#chapter_16_sub_log_clear)
   * [`log::rotate`](#chapter_16_sub_log_rotate)
 * [`plugin`](#chapter_16_plugin)
   * [`plugin::add-channel`](#chapter_16_sub_plugin_add_channel)
   * [`plugin::install`](#chapter_16_sub_plugin_install)
   * [`plugin::list`](#chapter_16_sub_plugin_list)
   * [`plugin::publish-assets`](#chapter_16_sub_plugin_publish_assets)
   * [`plugin::uninstall`](#chapter_16_sub_plugin_uninstall)
   * [`plugin::upgrade`](#chapter_16_sub_plugin_upgrade)
 * [`project`](#chapter_16_project)
   * [`project::clear-controllers`](#chapter_16_sub_project_clear_controllers)
   * [`project::deploy`](#chapter_16_sub_project_deploy)
   * [`project::disable`](#chapter_16_sub_project_disable)
   * [`project::enable`](#chapter_16_sub_project_enable)
   * [`project::optimize`](#chapter_16_sub_project_optimize)
   * [`project::permissions`](#chapter_16_sub_project_permissions)
   * [`project::send-emails`](#chapter_16_sub_project_send_emails)
   * [`project::upgrade1.3`](#chapter_16_sub_project_upgrade1_3)
 * [`propel`](#chapter_16_propel)
   * [`propel::build-all`](#chapter_16_sub_propel_build_all)
   * [`propel::build-all-load`](#chapter_16_sub_propel_build_all_load)
   * [`propel::build-filters`](#chapter_16_sub_propel_build_filters)
   * [`propel::build-forms`](#chapter_16_sub_propel_build_forms)
   * [`propel::build-model`](#chapter_16_sub_propel_build_model)
   * [`propel::build-schema`](#chapter_16_sub_propel_build_schema)
   * [`propel::build-sql`](#chapter_16_sub_propel_build_sql)
   * [`propel::data-dump`](#chapter_16_sub_propel_data_dump)
   * [`propel::data-load`](#chapter_16_sub_propel_data_load)
   * [`propel::generate-admin`](#chapter_16_sub_propel_generate_admin)
   * [`propel::generate-module`](#chapter_16_sub_propel_generate_module)
   * [`propel::generate-module-for-route`](#chapter_16_sub_propel_generate_module_for_route)
   * [`propel::graphviz`](#chapter_16_sub_propel_graphviz)
   * [`propel::init-admin`](#chapter_16_sub_propel_init_admin)
   * [`propel::insert-sql`](#chapter_16_sub_propel_insert_sql)
   * [`propel::schema-to-xml`](#chapter_16_sub_propel_schema_to_xml)
   * [`propel::schema-to-yml`](#chapter_16_sub_propel_schema_to_yml)
 * [`symfony`](#chapter_16_symfony)
   * [`symfony::test`](#chapter_16_sub_symfony_test)
 * [`test`](#chapter_16_test)
   * [`test::all`](#chapter_16_sub_test_all)
   * [`test::coverage`](#chapter_16_sub_test_coverage)
   * [`test::functional`](#chapter_16_sub_test_functional)
   * [`test::unit`](#chapter_16_sub_test_unit)


<div class="pagebreak"></div>

### ~`help`~

`help` タスクはタスクのヘルプメッセージを表示する:

    $ php symfony help [--xml] [task_name]

*エイリアス*: `h`

| 引数        | デフォルト| 説明
| ----------- | -------- | -----------
| `task_name` | `help`   | タスクの名前

| オプション (ショートカット)|デフォルト |説明
| ------------------------- | -------- | -------------------------
| `--xml`       | `-`       | ヘルプメッセージを XML 形式で出力する

`help` タスクは任意のタスクのヘルプメッセージを表示します:

    ./symfony help test:all

`--xml` オプションをつけることでヘルプメッセージを XML として出力することもできます:

    ./symfony help test:all --xml

### ~`list`~

`list` タスクはタスクの一覧を表示する:

    $ php symfony list [--xml] [namespace]

| 引数        | デフォルト | 説明
| ----------- | --------- | --------------
| `namespace` | `-`       | 名前空間の名前

| オプション (ショートカット)| デフォルト | 説明
| ------------------------- | ---------- | ----------------------
| `--xml`      | `-`        | ヘルプメッセージを XML として出力する

`list` タスクはすべてのタスクの一覧を表示します:

    ./symfony list

特定の名前空間のタスクを表示することもできます:

    ./symfony list test

`--xml` オプションをつけることで情報を XML として出力することもできます:

    ./symfony list --xml

`app`
-----

### ~`app::routes`~

`app::routes` タスクは現在のアプリケーションのルートを表示する:

    $ php symfony app:routes  application [name]

| 引数          | デフォルト | 説明
| ------------- | --------- | -----------------------
| `application` | `-`       | アプリケーションの名前
| `name`        | `-`       | ルートの名前

`app:routes` は現在のアプリケーションのルートを表示します:

    ./symfony app:routes frontend

`cache`
-------

### ~`cache::clear`~

`cache::clear` タスクはキャッシュをクリアする:

    $ php symfony cache:clear [--app[="..."]] [--env[="..."]] [--type[="..."]] 

*エイリアス*: `cc、clear-cache`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | ----------------------
| `--app`                    | `-`        | アプリケーションの名前
| `--env`                    | `-`        | 環境
| `--type`                   | `all`      | 種類

`cache:clear` タスクは symfony のキャッシュをクリアします。

デフォルトでは、これはすべての利用可能な種類、すべてのアプリケーションとすべての環境のキャッシュを削除します。

種類、アプリケーションもしくは環境で制限できます:

たとえば、`frontend` アプリケーションのキャッシュをクリアするには:

    ./symfony cache:clear --app=frontend

`frontend` アプリケーションの `prod` 環境のキャッシュをクリアするには:

    ./symfony cache:clear --app=frontend --env=prod

すべての `prod` 環境のキャッシュをクリアするには:

    ./symfony cache:clear --env=prod

`prod` 環境の `config` キャッシュをクリアするには:

    ./symfony cache:clear --type=config --env=prod

type オプションの組み込みの引数は次のとおりです: `config`、`i18n`、`routing`、`module` と `template`


`configure`
-----------

### ~`configure::author`~

`configure::author` タスクはプロジェクトの著者を設定する:

    $ php symfony configure:author  author

| 引数     | デフォルト | 説明
| -------- | --------- | ------------------
| `author` | `-`       | プロジェクトの著者

`configure:author` タスクはプロジェクトの著者を設定します:

    ./symfony configure:author "Fabien Potencier <fabien.potencier@symfony-project.com>"

それぞれの生成ファイルで PHPDoc ヘッダーをあらかじめ設定するためにジェネレーターによって著者の名前が使われます。

値は `config/properties.ini` に保存されます。

### ~`configure::database`~

`configure::database` タスクはデータベースのDSNを設定する:

    $ php symfony configure:database [--env[="..."]] [--name[="..."]] [--class[="..."]] [--app[="..."]] dsn [username] [password]

| 引数       | デフォルト | 説明
| ---------- | --------- | ---------------------------
| `dsn`      | `-`       | データベースの DSN
| `username` | `root`    | データベースユーザーの名前
| `password` | `-`       | データベースのパスワード

| オプション(ショートカット)    | デフォルト        | 説明
| ---------------------------- | ------------------ | --------------------------
| `--env`                      | `all`              | 環境
| `--name`                     | `propel`           | 接続名
| `--class`                    | `sfPropelDatabase` | データベースクラスの名前
| `--app`                      | `-`                | アプリケーションの名前

`configure:database` タスクはプロジェクトのデータベースの DSN を設定します:

    ./symfony configure:database mysql:host=localhost;dbname=example root mYsEcret

デフォルトでは、このタスクはすべての環境のコンフィギュレーションを変更します。特定の環境のために DSN を変更したい場合、`env` オプションを使います:

    ./symfony configure:database --env=dev mysql:host=localhost;dbname=example_dev root mYsEcret

特定のアプリケーションのコンフィギュレーションを変更するには、`app` オプションを使います:

    ./symfony configure:database --app=frontend mysql:host=localhost;dbname=example root mYsEcret

接続名とデータベースのクラス名を指定することもできます:

    ./symfony configure:database --name=main --class=ProjectDatabase mysql:host=localhost;dbname=example root mYsEcret

WARNING: `Propel` データベースを使い `app` なしで `all` 環境を設定するときは `propel.ini` ファイルも更新されます。

`doctrine`
----------

### ~`doctrine::build`~

`doctrine::build` タスクはスキーマにもとづいてコードを生成する:

    $ php symfony doctrine:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-migrate] [--and-load[="..."]] [--and-append[="..."]] 

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | --------- | ---------------------------------------------------------
| `--application`     | `1`  | アプリケーションの名前
| `--env`             | `dev`| 環境
| `--no-confirmation` | `-`  | データベースの削除を強制するか
| `--all`             | `-`  | すべてをビルドしてデータベースをリセットする
| `--all-classes`     | `-`  | すべてのクラスをビルドする
| `--model`           | `-`  | モデルクラスをビルドする
| `--forms`           | `-`  | フォームクラスをビルドする
| `--filters`         | `-`  | フィルタークラスをビルドする
| `--sql`             | `-`  | SQL をビルドする
| `--db`              | `-`  | SQL を削除、作成もしくは挿入する、もしくはデータベースをマイグレートする
| `--and-migrate`     | `-`  | データベースをマイグレートする
| `--and-load`        | `-`  | フィクスチャデータをロードする (複数の値が可能)
| `--and-append`      | `-`  | フィクスチャデータを追加する (複数の値が可能)

`doctrine:build` タスクはスキーマに基づいてコードを生成します:

    ./symfony doctrine:build

ビルドしたいものを指定しなければなりません。たとえば、`--model` と `--forms` オプションをつけてモデルとフォームクラスをビルドしたいとします:

    ./symfony doctrine:build --model --forms

すべてのクラスと SQL を生成させてデータベースをリビルドしたければ `--all` ショートカットオプションを使うことができます:

    ./symfony doctrine:build --all

これは次のタスク群を実行するのと同じです:

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:build-sql
    ./symfony doctrine:insert-sql

`--all-classes` ショートカットオプションをつけることでクラスファイルのみを生成することもできます。このオプションが単独で使われるとき、データベースは修正されません。

    ./symfony doctrine:build --all-classes

`--and-migrate` オプションはビルドが完了したらマイグレーションの追加を実行します:

    ./symfony doctrine:build --db --and-migrate

`--and-load` オプションはプロジェクトとプラグインの `data/fixtures/` ディレクトリからデータをロードします:

    ./symfony doctrine:build --db --and-migrate --and-load

ロードされるフィクスチャを指定するには、パラメーターを `--and-load` オプションに加えます:

    ./symfony doctrine:build --all --and-load="data/fixtures/dev/"

データベースからのレコードを削除せずにフィクスチャを追加するには、`--and-append` オプションをつけます:

    ./symfony doctrine:build --all --and-append

### ~`doctrine::build-all`~

`doctrine::build-all` タスクは Doctrine モデル、SQL を生成しデータベースを初期化する:

    $ php symfony doctrine:build-all [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] 

*エイリアス*: `doctrine-build-all`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | ------------------------------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境
| `--no-confirmation`        | `-`        | 確認の質問をしない
| `--skip-forms`<br />`(-F)` | `-`        | フォーム生成を省く
| `--migrate`                | `-`        | データベースをリセットする代わりにマイグレートする

`doctrine:build-all` タスクは6つのタスクのショートカットです:

    ./symfony doctrine:build-all

このタスクは次のものと同等です:

    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:insert-sql

詳細な情報はこれらのタスクのヘルプページを参照してください。

確認を回避するには、`no-confirmation` オプションを渡します:

    ./symfony doctrine:buil-all-load --no-confirmation

Doctrine SQL を挿入するよりもプロジェクトのマイグレーションを実行したいのであれば `--migrate` オプションを含めます。

    ./symfony doctrine:build-all --migrate

このタスクは次のものと同等です:

    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:migrate

### ~`doctrine::build-all-load`~

`doctrine::build-all-load` タスクは Doctrine モデル、SQL を生成し、データベースを初期化し、フィクスチャデータをロードする:

    $ php symfony doctrine:build-all-load [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append] 

*エイリアス*: `doctrine-build-all-load`

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | -------------------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境
| `--no-confirmation`        | `-`        | 確認の質問をしない
| `--skip-forms`<br />`(-F)` | `-`        | フォーム生成を省く
| `--migrate`                | `-`        | データベースをリセットする代わりにマイグレートする
| `--dir`                    | `-`        | フィクスチャを探すディレクトリ (複数の値が許可される)
| `--append`                 | `-`        | データベースの現在のデータを削除しない

`doctrine:build-all-load` タスクは7つのタスクのショートカットです:

    ./symfony doctrine:build-all-load

このタスクは次のものと同等です:

    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load

`doctrine:data-load` タスクなのでアプリケーションの引数を受け取ります。詳細な情報は `doctrine:data-load` のヘルプページを参照してください。

確認を回避するには、`no-confirmation` オプションを渡します:

    ./symfony doctrine:build-all-load --no-confirmation

Doctrine SQL を挿入するよりもマイグレーションを実行したいのであれば `--migrate` オプションをつけます。

    ./symfony doctrine:build-all-load --migrate

### ~`doctrine::build-all-reload`~

`doctrine::build-all-reload` タスクは Doctrine モデル、SQL を生成し、データベースを初期化し、データをロードする:

    $ php symfony doctrine:build-all-reload [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append] 

*エイリアス*: `doctrine-build-all-reload`

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ------------------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境
| `--no-confirmation`        | `-`        | 確認をしない
| `--skip-forms`<br />`(-F)` | `-`        | フォーム生成を省く
| `--migrate`                | `-`        | データベースをリセットする代わりにマイグレートする
| `--dir`                    | `-`        | フィクスチャを探すディレクトリ (複数の値が許可される)
| `--append`                 | `-`        | データベースの現在のデータを削除しない

`doctrine:build-all-reload` タスクは8つのタスクのショートカットです:

    ./symfony doctrine:build-all-reload

このタスクは次のものと同等です:

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load

Doctrine SQL を挿入する代わりにプロジェクトのマイグレーションを実行したいのであれば `--migrate` オプションを含めます。

    ./symfony doctrine:build-all-reload --migrate

### ~`doctrine::build-all-reload-test-all`~

`doctrine::build-all-reload-test-all` タスクは Doctrine モデル、SQL を生成し、データベースを初期化し、データをロードしてすべてのテストを実施する:

    $ php symfony doctrine:build-all-reload-test-all [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append]

*エイリアス*: `doctrine-build-all-reload-test-all`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | --------- | ---------------------------------------------------
| `--application`            | `1`       | アプリケーションの名前
| `--env`                    | `dev`     | 環境
| `--no-confirmation`        | `-`       | 確認の質問をしない
| `--skip-forms`<br />`(-F)` | `-`       | フォームの生成を省略する
| `--migrate`                | `-`       | データベースをリセットする代わりにマイグレートする
| `--dir`                    | `-`       | フィクスチャを探すディレクトリ (複数の値が許可される)
| `--append`                 | `-`       | データベースの現在のデータを削除しない

`doctrine:build-all-reload` タスクは9つのタスクのショートカットです:

    ./symfony doctrine:build-all-reload-test-all frontend

このタスクは次のものと同等です:
  
    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load
    ./symfony test-all

`doctrine:data-load` タスクなのでアプリケーションの引数を受け取ります。詳細な情報は `doctrine:data-load` のヘルプページを参照してください。

Doctrine SQL を挿入する代わりにプロジェクトのマイグレーションを実行したいのであれば `--migrate` オプションを含めます。

    ./symfony doctrine:build-all-reload-test-all --migrate

### ~`doctrine::build-db`~

`doctrine::build-db` タスクは現在のモデルのデータベースを作成する:

    $ php symfony doctrine:build-db [--application[="..."]] [--env="..."] 

*エイリアス*: `doctrine-build-db`

| オプション (ショートカット)| デフォルト   | 説明
| ------------------------- | ----------- | -----------------------
| `--application`           | `1`         | アプリケーションの名前
| `--env`                   | `dev`       | 環境

`doctrine:build-db` タスクはデータベースを作成します:

    ./symfony doctrine:build-db

このタスクは `config/doctrine/databases.yml` の接続情報を読み込みます:

### ~`doctrine::build-filters`~

`doctrine::build-filters` タスクは現在のモデルのフィルターフォームクラスを作成する:

    $ php symfony doctrine:build-filters [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--filter-dir-name="..."] 

| オプション (ショートカット)     | デフォルト  | 説明
| ------------------------------ | ---------- | ----------------------
| `--application`     | `1`      | アプリケーションの名前
| `--env`             | `dev`    | 環境
| `--model-dir-name`  | `model`  | モデルディレクトリの名前
| `--filter-dir-name` | `filter` | フィルターフォームディレクトリの名前

`doctrine:build-filters` タスクはスキーマからフォームフィルタークラスを作成します:

    ./symfony doctrine:build-filters

このクラスはモデルにもとづいてフォームフィルタークラスを作成します。このクラスは `lib/doctrine/filter` に作られます。

このタスクは `lib/doctrine/filter` のカスタムクラスをオーバーライドすることはありません。これは `lib/doctrine/filter/base` に生成された基底クラスを置き換えるだけです。

### ~`doctrine::build-forms`~

`doctrine::build-forms` タスクは現在のモデルのフォームクラスを作成する:

    $ php symfony doctrine:build-forms [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--form-dir-name="..."]

| オプション (ショートカット)| デフォルト  | 説明
| ------------------------- | ---------- | -------------------------
| `--application`           | `1`        | アプリケーションの名前
| `--env`                   | `dev`      | 環境
| `--model-dir-name`        | `model`    | モデルディレクトリの名前
| `--form-dir-name`         | `form`     | フォームディレクトリの名前


`doctrine:build-forms` タスクはスキーマからフォームクラスを作成します:

    ./symfony doctrine:build-forms

このタスクはプロジェクトとすべてのインストールしたプラグインから `config/*schema.xml` かつ/もしくは `config/*schema.yml` のスキーマ情報を読み取ります。モデルフォームクラスのファイルは `lib/form` で作成されます。

このタスクは `lib/form` のなかのカスタムクラスをけっして上書きしません。これは `lib/form/base` で生成される基底クラスのみを置き換えます。

### ~`doctrine::build-model`~

`doctrine::build-model` タスクは現在のモデルのクラスを作成する:

    $ php symfony doctrine:build-model [--application[="..."]] [--env="..."] 

*エイリアス*: `doctrine-build-model`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | ------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:build-model` タスクはスキーマからモデルクラスを作成します:

    ./symfony doctrine:build-model

このタスクはプロジェクトと有効なすべてのプラグインから `config/doctrine/*.yml` のスキーマ情報を読み込みます。

モデルクラスファイルは `lib/model/doctrine` で作成されます。

このタスクは `lib/model/doctrine` のなかのカスタムタスクをけっして上書きしません。これは `lib/model/doctrine/base` のファイルのみを置き換えます。

### ~`doctrine::build-schema`~

`doctrine::build-schema` タスクは既存のデータベースからスキーマを作成する:

    $ php symfony doctrine:build-schema [--application[="..."]] [--env="..."] 

*エイリアス*: `doctrine-build-schema`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | -----------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:build-schema` タスクはスキーマを作成するデータベースをイントロスペクトします:

    ./symfony doctrine:build-schema

このタスクは `config/doctrine` に YAML ファイルを作成します。

### ~`doctrine::build-sql`~

`doctrine::build-sql` タスクは現在のモデルの SQL を作成する:

    $ php symfony doctrine:build-sql [--application[="..."]] [--env="..."] 

*エイリアス*: `doctrine-build-sql`

| オプション (ショートカット)   | デフォルト | 説明
| ---------------------------- | ---------- | ----------------------
| `--application`              | `1`        | アプリケーションの名前
| `--env`                      | `dev`      | 環境

`doctrine:build-sql` タスクはテーブル作成用の SQL ステートメントを作成します:

    ./symfony doctrine:build-sql

生成 SQL は `config/databases.yml` で設定されるデータベース向けに最適化されます:

    doctrine.database = mysql

### ~`doctrine::clean-model-files`~

`doctrine::clean-model-files` タスクは YAML スキーマにもはや存在しないモデルに向けて生成されたすべてのモデルクラスを削除する:

    $ php symfony doctrine:clean-model-files [--no-confirmation] 

*エイリアス*: `doctrine:clean`

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | -------------------
| `--no-confirmation`        | `-`        | 確認の質問をしない

`doctrine:clean-model-files` タスクはプロジェクトもしくはプラグインの `schema.yml` ファイルに存在しないモデルクラスを削除します:

    ./symfony doctrine:clean-model-files

### ~`doctrine::create-model-tables`~

`doctrine::create-model-tables` タスクは指定されるモデルのテーブルを削除し再作成する:

    $ php symfony doctrine:create-model-tables [--application[="..."]] [--env="..."] [models1] ... [modelsN]

| 引数     | デフォルト  | 説明
| -------- | ---------- | --------------
| `models` | `-`        | モデルのリスト

| オプション (ショートカット)  | デフォルト | 説明
| -------------------------- | ---------- | ----------------------
| `--application`            | `frontend` | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:create-model-tables` は指定されるモデルのテーブルを削除し再作成します:

    ./symfony doctrine:create-model-tables User

### ~`doctrine::data-dump`~

`doctrine::data-dump` タスクはデータをフィクスチャディレクトリにダンプする:

    $ php symfony doctrine:data-dump [--application[="..."]] [--env="..."] [target]

*エイリアス*: `doctrine-dump-data`

| 引数     | デフォルト  | 説明
| -------- | ---------- | ---------------------
| `target` | `-`        | ターゲットのファイル名

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ----------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:data-dump` タスクはデータベースデータをダンプします:

    ./symfony doctrine:data-dump

このタスクはデータベースのデータを `data/fixtures/%target%` にダンプします。

`doctrine:data-load` タスクを使ってダンプファイルは YAML フォーマットで再インポートできます。

    ./symfony doctrine:data-load

### ~`doctrine::data-load`~

`doctrine::data-load` タスクは YAML フィクスチャデータをロードする:

    $ php symfony doctrine:data-load [--application[="..."]] [--env="..."] [--append] [--connection="..."] [--dir="..."] 

*エイリアス*: `doctrine-load-data`

| 引数          | デフォルト  | 説明
| ------------- | ---------- | --------------------------------------
| `dir_or_file` | `-`        | ロードするディレクトリもしくはファイル

| オプション(ショートカット)  | デフォルト  | 説明
| -------------------------- | ---------- | ----------------------
| `--application`| `1`       | アプリケーションの名前
| `--env`        | `dev`     | 環境
| `--append`     | `-`       | データベースの現在の値を削除しない

`doctrine:data-load` タスクはデータフィクスチャをデータベースにロードします:

    ./symfony doctrine:data-load

このタスクは `data/fixtures/` で見つかるすべてのファイルからデータをロードします。

特定のファイルもしくはディレクトリからデータをロードしたいのであれば、これらを引数として追加できます:

    ./symfony doctrine:data-load data/fixtures/dev data/fixtures/users.yml

このタスクにデータベースの既存のデータを削除させたくない場合、`--append` オプションを指定します:

    ./symfony doctrine:data-load --append

### ~`doctrine::delete-model-files`~

`doctrine::delete-model-files` タスクは任意のモデル名に関連する自動生成されたファイルをすべて削除する:

    $ php symfony doctrine:delete-model-files [--no-confirmation] name

| 引数   | デフォルト  | 説明
| ------ | ---------- | ----------------------------------------------
| `name` | `-`        | 削除したいすべてのファイルに関連するモデルの名前

| オプション (ショートカット) | デフォルト | 説明
| ------------------- | ---- | ------------------
| `--no-confirmation` | `-`  | 確認の質問をしない

`doctrine:delete-model-files` タスクは特定のモデルに関連するすべてのファイルを削除します:

    ./symfony doctrine:delete-model-files Article Author

### ~`doctrine::dql`~

`doctrine::dql` タスクは DQL クエリを実行し結果を表示する:

    $ php symfony doctrine:dql [--application[="..."]] [--env="..."] [--show-sql] [--table] dql_query

*エイリアス*: `doctrine-dql`

| 引数        | デフォルト  | 説明
| ----------- | ---------- | -------------------
| `dql_query` | `-`        | 実行する DQL クエリ

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境
| `--show-sql`               | `-`        | 実行される SQL を表示する
| `--table`                  | `-`        | 結果を表形式で返す

`doctrine:data-dql` タスクは DQL クエリを実行しフォーマットされた結果を表示します:

    ./symfony doctrine:dql "FROM User u"

`--dir` オプションを指定して実行される SQL を表示できます:

    ./symfony doctrine:dql --show-sql "FROM User u"

### ~`doctrine::drop-db`~

`doctrine::drop-db` タスクは現在のモデルのデータベースを削除する:

    $ php symfony doctrine:drop-db [--application[="..."]] [--env="..."] [--no-confirmation] 

*エイリアス*: `doctrine-drop-db`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | -------------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境
| `--no-confirmation`        | `-`        | データベースの削除を強制するか

`doctrine:drop-db` タスクはデータベースを削除します:

    ./symfony doctrine:drop-db

このタスクは `config/databases.yml` の接続情報を読み込みます:

### ~`doctrine::generate-admin`~

`doctrine::generate-admin` タスクは Doctrine の admin モジュールを生成する:

    $ php symfony doctrine:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_mode

| 引数             | デフォルト  | 説明
| ---------------- | ---------- | --------------------------------
| `application`    | `-`        | アプリケーションの名前
| `route_or_model` | `-`        | ルートの名前もしくはモデルクラス

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | -----------------
| `--module`                 | `-`        | モジュールの名前
| `--theme`                  | `admin`    | テーマの名前
| `--singular`               | `-`        | 単数形の名前
| `--plural`                 | `-`        | 複数形の名前
| `--env`                    | `dev`      | 環境
| `--actions-base-class`     | `sfActions`| アクションの基底クラス 

`doctrine:generate-admin` タスクは Doctrine の admin モジュールを生成します:

    ./symfony doctrine:generate-admin frontend Article

このタスクは `%Article%` モデルの `%frontend%` アプリケーションのモジュールを作成します。

このタスクはアプリケーションの `routing.yml` のルートを作成します。

ルートの名前を渡すことで Doctrine の admin モジュールを生成することもできます:

    ./symfony doctrine:generate-admin frontend article

このタスクは `routing.yml` で見つかる `%article%` ルートの `%frontend%` アプリケーションのモジュールを作成します。

フィルターとバッチアクションを適切に動作させるために、ルートに `wildcard` オプションを追加する必要があります:

    article:
      class: sfDoctrineRouteCollection
      options:
        model:              Article
        with_wildcard_routes:   true

### ~`doctrine::generate-migration`~

`doctrine::generate-migration` タスクはマイグレーションクラスを生成する:

    $ php symfony doctrine:generate-migration [--application[="..."]] [--env="..."] [--editor-cmd="..."] name

*エイリアス*: `doctrine-generate-migration`

| 引数     | デフォルト  | 説明
| -------- | ---------- | ----------------------
| `name`   | `-`        | マイグレーションの名前

| オプション (ショートカット) | デフォルト| 説明
| -------------------------- | --------- | -----------------------------------------
| `--application`            | `1`       | アプリケーションの名前
| `--env`                    | `dev`     | 環境
| `--editor-cmd`             | `-`       | 作成するためにこのコマンドでスクリプトを開く

`doctrine:generate-migration` タスクはマイグレーションテンプレートを生成します。

    ./symfony doctrine:generate-migration AddUserEmailColumn

新しいマイグレーションクラスをエディターの作成メニューで開くために `--editor-cmd` オプションをつけることができます:

    ./symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

### ~`doctrine::generate-migrations-db`~

`doctrine::generate-migrations-db` タスクは既存のデータベース接続からマイグレーションクラスを生成する:

    $ php symfony doctrine:generate-migrations-db [--application[="..."]] [--env="..."] 

*エイリアス*: `doctrine-generate-migrations-db`、`doctrine-gen-migrations-from-db`

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:generate-migrations-db` タスクは既存のデータベース接続からマイグレーションクラスを生成します。

    ./symfony doctrine:generate-migrations-db

### ~`doctrine::generate-migrations-diff`~

`doctrine::generate-migrations-diff` タスクは新旧のスキーマの差分を作り出すことでマイグレーションクラスを生成する:

    $ php symfony doctrine:generate-migrations-diff [--application[="..."]] [--env="..."]

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | -----------
| `--application` | `1`   | アプリケーションの名前
| `--env`         | `dev` | 環境

`doctrine:generate-migrations-diff` タスクは新旧のスキーマの差分を作り出すことでマイグレーションクラスを生成します。

    ./symfony doctrine:generate-migrations-diff

### ~`doctrine::generate-migrations-models`~

`doctrine::generate-migrations-models` タスクは既存のモデルのセットからマイグレーションクラスを生成する:

    $ php symfony doctrine:generate-migrations-models [--application[="..."]] [--env="..."] 

*エイリアス*: `doctrine-generate-migrations-models`、`doctrine-gen-migrations-from-models`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | ----------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:generate-migration` タスクは既存のモデルのセットからマイグレーションクラスを生成します。

    ./symfony doctrine:generate-migration

### ~`doctrine::generate-module`~

`doctrine::generate-module` タスクは Doctrine モジュールを生成する:

    $ php symfony doctrine:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-doctrine-route] [--env="..."] [--actions-base-class="..."] application module model

*エイリアス*: `doctrine-generate-crud`、`doctrine:generate-crud`

| 引数                  | デフォルト   | 説明
| --------------------- | ----------- | ------------------------
| `application`         | `-`         | アプリケーションの名前
| `module`              | `-`         | モジュールの名前
| `model`               | `-`         | モデルクラスの名前
| `--actions-base-class`| `sfActions` | アクションの基底クラス 

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ----------------------------------
| `--theme`                  | `default`  | テーマの名前
| `--generate-in-cache`      | `-`        | キャッシュでモジュールを生成する
| `--non-verbose-templates`  | `-`        | 冗長ではないテンプレートを生成する
| `--with-show`              | `-`        | show メソッドを生成する
| `--singular`               | `-`        | 単数形の名前
| `--plural`                 | `-`        | 複数形の名前
| `--route-prefix`           | `-`        | ルートのプレフィックス
| `--with-doctrine-route`    | `-`        | Doctrine のルートを使うかどうか
| `--env`                    | `dev`      | 環境

`doctrine:generate-module` タスクは Doctrine のモジュールを生成します:

    ./symfony doctrine:generate-module frontend article Article

`%model%` モデルクラスの `%application%` アプリケーションの `%module%` モジュールを作成します。

`--generate-in-cache` オプションをつけることで `%sf_app_cache_dir%/modules/auto%module%` で実行時に生成されたモジュールからアクションとテンプレートを継承する空のモジュールを作ることもできます:

    ./symfony doctrine:generate-module --generate-in-cache frontend article Article

`--theme` オプションをつけることでジェネレーターはカスタマイズされたテーマを使うことができます:

    ./symfony doctrine:generate-module --theme="custom" frontend article Article

この方法では、独自仕様に合わせてモジュールジェネレーターを作成できます。

### ~`doctrine::generate-module-for-route`~

`doctrine::generate-module-for-route` タスクはルート定義の Doctrine モジュールを生成する:

    $ php symfony doctrine:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route

| 引数          | デフォルト  | 説明
| ------------- | ---------- | ------------------------
| `application` | `-`        | アプリケーションの名前
| `route`       | `-`        | ルートの名前

| オプション (ショートカット)    | デフォルト | 説明
| ----------------------------- | ---------- | ---------------------------------
| `--theme`                     | `default`  | テーマの名前
| `--non-verbose-templates`     | `-`        | 冗長ではないテンプレートを生成する
| `--singular`                  | `-`        | 単数形の名前
| `--plural`                    | `-`        | 複数形の複数形の名前
| `--env`                       | `dev`      | 環境
| `--actions-base-class`        | `sfActions`| アクションの基底クラス

`doctrine:generate-module-for-route` タスクはルート定義の Doctrine モジュールを生成します:

    ./symfony doctrine:generate-module-for-route frontend article

このタスクは `routing.yml` で見つかる `%article%` ルート定義の `%frontend%` アプリケーションでモジュールを作成します。

### ~`doctrine::insert-sql`~

`doctrine::insert-sql` タスクは現在のモデルに SQL を INSERT する:

    $ php symfony doctrine:insert-sql [--application[="..."]] [--env="..."] 

*エイリアス*: `doctrine-insert-sql`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | ----------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:insert-sql` タスクはデータベースのテーブルを作成します:

    ./symfony doctrine:insert-sql

このタスクはデータベースに接続しすべての `lib/model/doctrine/*.class.php` ファイルのテーブルを作成します。

### ~`doctrine::migrate`~

`doctrine::migrate` タスクは現在の／指定バージョンのデータベースにマイグレートする:

    $ php symfony doctrine:migrate [--application[="..."]] [--env="..."] [version]

*エイリアス*: `doctrine-migrate`

| 引数      | デフォルト  | 説明
| --------- | ---------- | --------------------------
| `version` | `-`        | マイグレートするバージョン

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | ----------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:migrate` タスクは現在の／指定バージョンにデータベースをマイグレートします。

   ./symfony doctrine:migrate

### ~`doctrine::rebuild-db`~

`doctrine::rebuild-db` タスクは現在のモデルのデータベースを作成する:

    $ php symfony doctrine:rebuild-db [--application[="..."]] [--env="..."] [--no-confirmation] [--migrate]

*エイリアス*: `doctrine-rebuild-db`

| オプション (ショートカット)   | デフォルト | 説明
| ---------------------------- | ---------- | ----------------------------------------
| `--application`              | `1`        | アプリケーションの名前
| `--env`                      | `dev`      | 環境
| `--no-confirmation`          | `-`        | 確認をせずにデータベースをドロップするか
| `--migrate`                  | `-`        | データベースをリセットする代わりにマイグレートする

`doctrine:rebuild-db` タスクはデータベースを作成します:

    ./symfony doctrine:rebuild-db

このタスクは `config/databases.yml` の接続情報を読み込みます:

Doctrine SQL を挿入する代わりにアプリケーションのマイグレーションを実行したいのであれば `--migrate` オプションを含めます。

    ./symfony doctrine:rebuild-db --migrate

### ~`doctrine::reload-data`~

`doctrine::reload-data` タスクはプロジェクトのデータベースとフィクスチャをリロードする:

    $ php symfony doctrine:reload-data [--application[="..."]] [--env="..."] [--no-confirmation] [--dir="..."] [--migrate] [--append] 

*エイリアス*: `doctrine-reload-data`

| オプション (ショートカット)  | デフォルト            | 説明
| ------------------- | ----- | -------------------------------------------------
| `--application`     | `1`   | アプリケーションの名前
| `--env`             | `dev` | 環境
| `--no-confirmation` | `-`   | 確認の質問をしない
| `--dir`             | `-`   | フィクスチャを探すディレクトリ (複数の値が可能)
| `--migrate`         | `-`   | データベースをリセットする代わりにマイグレートする
| `--append`          | `-`   | データベースの現在のデータを削除しない

`doctrine:reload-data` タスクはデータベースを削除してから再作成しフィクスチャをロードします:

    php symfony doctrine:reload-data
  
このタスクは次のものと同等です:

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load`  

`generate`
----------

### ~`generate::app`~

`generate::app` タスクは新しいアプリケーションを生成する:

    $ php symfony generate:app [--escaping-strategy="..."] [--csrf-secret="..."] application

*エイリアス*: `init-app`

| 引数          | デフォルト | 説明
| ------------- | ---------- | ----------------------
| `application` | `-`        | アプリケーションの名前

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ----------------------------
| `--escaping-strategy`      | `1`        | エスケーピング戦略を出力する
| `--csrf-secret`            | `1`        | CSRF 防止に使う秘密の文字列

`generate:app` タスクは現在のプロジェクトの新しいアプリケーションのための基本的なディレクトリ構造を作成します:

    ./symfony generate:app frontend

このタスクは2つのフロントコントローラーも `web/` ディレクトリに作成します:

    web/%application%.php`     運用環境
    web/%application%_dev.php` 開発環境

最初のアプリケーションに関して、運用環境のスクリプトの名前は `index.php` です。

すでに同じ名前のアプリケーションが存在する場合、`sfCommandException` が投げられます。

デフォルトでは、XSS を阻止するために出力エスケーピングが有効で、CSRF を阻止するためにランダムな秘密の文字列も生成されます。

`escaping-strategy` オプションをつけることで出力エスケーピングを無効にできます:

    ./symfony generate:app frontend --escaping-strategy=false

(CSRF を阻止するために) `csrf-secret` オプションで秘密の文字列を定義することでフォームのセッショントークンを有効にできます:

    ./symfony generate:app frontend --csrf-secret=UniqueSecret

`%sf_data_dir%/skeleton/app` ディレクトリを作成することでタスクによって使われるデフォルトのスケルトンをカスタマイズできます。

### ~`generate::module`~

`generate::module`タスクは新しいモジュールを生成する:

    $ php symfony generate:module  application module

*エイリアス*: `init-module`

| 引数          | デフォルト  | 説明
| ------------- | ---------- | -----------------------
| `application` | `-`        | アプリケーションの名前
| `module`      | `-`        | モジュールの名前

`generate:module` タスクは既存のアプリケーションの新しいモジュールの基本ディレクトリ構造を作成します:

    ./symfony generate:module frontend article

`config/properties.ini` で設定した場合、タスクは `actions.class.php` で見つかる著者の名前を変更することもできます:

    [symfony]
      name=blog
      author=Fabien Potencier <fabien.potencier@sensio.com>

`%sf_data_dir%/skeleton/module` ディレクトリを作ることでタスクによって使われるデフォルトのスケルトンをカスタマイズできます。

`%sf_test_dir%/functional/%application%/%module%ActionsTest.class.php` という名前の機能テストのスタブも作成します。これはデフォルトではパスしません。

すでにアプリケーションに同じ名前のモジュールがある場合、`sfCommandException`が投げられます。

### ~`generate::project`~

`generate::project` タスクは新しいプロジェクトを生成する:

    $ php symfony generate:project [--orm="..."] [--installer="..."] name [author]

*エイリアス*: `init-project`

| 引数     | デフォルト       | 説明
| -------- | ---------------- | ------------------
| `name`   | `-`              | プロジェクトの名前
| `author` | `Your name here` | プロジェクトの著者 

| オプション (ショートカット)  | デフォルト | 説明
| ------------- | ----------- | ---------------------------------
| `--orm`       | `Doctrine`  | デフォルトで使う ORM
| `--installer` | `-`         | 実行するインストーラースクリプト

`generate:project` タスクは現在のディレクトリに新しいプロジェクトの基本ディレクトリ構造を作成します:

    ./symfony generate:project blog

すでに現在のディレクトリが symfony のプロジェクトに収まっている場合、`sfCommandException` が投げられます。

ORM を使いたくないのであれば、`--orm` オプションに `none` を渡します:

    ./symfony generate:project blog --orm=none

プロジェクトをさらにカスタマイズするために `--installer` オプションを渡すこともできます:

    ./symfony generate:project blog --installer=./installer.php

symfony が新しいクラスを生成するときに著者として使う名前を指定するためにオプションとして2番目の引数の `author` を含めることができます:

    ./symfony generate:project blog "Jack Doe"

### ~`generate::task`~

`generate::task` タスクは新しいタスクのスケルトンクラスを作成する:

    $ php symfony generate:task [--dir="..."] [--use-database="..."] [--brief-description="..."] task_name

| 引数        | デフォルト  | 説明
| ----------- | ---------- | ---------------------------------------
| `task_name` | `-`        | タスクの名前 (名前空間を含むことができる)

| オプション (ショートカット) | デフォルト | 説明
| -------------------- | ---------- | ------------------------------------------------------------
| `--dir`              | `lib/task` | タスクを作成するディレクトリ
| `--use-database`     | `doctrine` | タスクがデータベースにアクセスするモデルの初期化を必要とするか
| `--brief-description`| `-`        | 短いタスクの説明 (タスクのリストに表示される)

`generate:task` は引数として渡される名前をもとに新しい `sfTask` クラスを作成します:

    ./symfony generate:task namespace:name

`namespaceNameTask.class.php` スケルトンタスクは `lib/task/` ディレクトリの下に作成されます。名前空間はオプションであることに留意してください。

別のディレクトリ (プロジェクトのルートフォルダーに相対的) でファイルを作りたい場合、`--dir` オプションにこれを渡します。このディレクトリはまだ存在しなければ作成されます。

    ./symfony generate:task namespace:name --dir=plugins/myPlugin/lib/task

デフォルトの `doctrine` 以外の接続を使いたい場合、`--use-database` オプションで接続の名前を提供します:

    ./symfony generate:task namespace:name --use-database=main

`--use-database` オプションは生成タスクでデータベースの初期化を無効化するためにも使うことができます:

    ./symfony generate:task namespace:name --use-database=false

説明を指定することもできます:

    ./symfony generate:task namespace:name --brief-description="Does interesting things"

`i18n`
------

### ~`i18n::extract`~

`i18n::extract` タスクは PHP ファイルから国際化された文字列を抽出する:

    $ php symfony i18n:extract [--display-new] [--display-old] [--auto-save] [--auto-delete] application culture

| 引数          | デフォルト | 説明
| ------------- | ---------- | ----------------------
| `application` | `-`        | アプリケーションの名前
| `culture`     | `-`        | ターゲットの culture

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | --------- | -------------------------
| `--display-new` | `-`      | 新しく見つかるすべての文字列を出力する
| `--display-old` | `-`      | すべての古い文字列を出力する
| `--auto-save`   | `-`      | 新しい文字列を保存する
| `--auto-delete` | `-`      | 古い文字列を削除する

`i18n:extract` タスクはアプリケーションとターゲットの culture のプロジェクトファイルから国際化文字列を抽出します:

    ./symfony i18n:extract frontend fr

デフォルトでは、このタスクは現在のプロジェクトで見つかる新旧の文字列の数のみを表示します。

新しい文字列を表示したい場合、`--display-new` オプションを指定します:

    ./symfony i18n:extract --display-new frontend fr

これらを国際化メッセージカタログに保存するには、`--auto-save` オプションを指定します:

    ./symfony i18n:extract --auto-save frontend fr

国際化メッセージカタログで見つかるがアプリケーションで見つからない文字列を表示したい場合、`--display-old` オプションを指定します:

    ./symfony i18n:extract --display-old frontend fr

古い文字列を自動的に削除するには `--auto-delete` を指定しますが、とりわけプラグインの翻訳がある場合、これらは現在のではなく古い文字列として現れるので、注意してください:

    ./symfony i18n:extract --auto-delete frontend fr

### ~`i18n::find`~

`i18n::find` タスクはアプリケーションで"国際化に対応"していない文字列を見つける:

    $ php symfony i18n:find [--env="..."] application

| 引数          | デフォルト  | 説明
| ------------- | ---------- | -----------------------
| `application` | `-`        | アプリケーションの名前

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | -----------
| `--env`                    | `dev`      | 環境

`i18n:find` タスクはテンプレートに埋め込まれる非国際化文字列を見つけます:

    ./symfony i18n:find frontend

このタスクは純粋な HTML と PHP コードで非国際化文字列を見つけることができます:

    <p>Non i18n text</p>
    <p><?php echo 'Test' ?></p>

このタスクは PHP に埋め込まれたすべての文字列を返しますが、誤検出である可能性があります (とりわけヘルパーの引数の文字列構文を使う場合)。

`log`
-----

### ~`log::clear`~

`log::clear` タスクはログファイルをクリアする:

    $ php symfony log:clear  

*エイリアス*: `log-purge`

`log:clear` タスクはすべての symfony ログをクリアします:

    ./symfony log:clear

### ~`log::rotate`~

`log::rotate` タスクはアプリケーションのログファイルのローテーションを行います:

    $ php symfony log:rotate [--history="..."] [--period="..."] application env

*エイリアス*: `log-rotate`

| 引数          | デフォルト  | 説明
| ------------- | ---------- | ----------------------
| `application` | `-`        | アプリケーションの名前
| `env`         | `-`        | 環境名

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ----------- | ----------------------
| `--history`   | `10`       | 維持する古いログファイルの最大個数
| `--period`    | `7`        | 日にち単位の期間

`log:rotate` タスクは任意の環境のアプリケーションのログファイルのローテーションを行います:

    ./symfony log:rotate frontend dev

`period` もしくは `history` オプションを指定できます:

    ./symfony --history=10 --period=7 log:rotate frontend dev

`plugin`
--------

### ~`plugin::add-channel`~

`plugin::add-channel` タスクは新しい PEAR チャンネルを追加する:

    $ php symfony plugin:add-channel  name


| 引数     | デフォルト  | 説明
| -------- | ---------- | -------------
| `name`   | `-`        | チャンネル名

`plugin:add-channel` タスクは新しい PEAR チャンネルを追加します:

    ./symfony plugin:add-channel symfony.plugins.pear.example.com

### ~`plugin::install`~

`plugin::install` タスクはプラグインをインストールする:

    $ php symfony plugin:install [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] [-d|--install_deps] [--force-license] name

*エイリアス*: `plugin-install`

| 引数     | デフォルト  | 説明
| -------- | ---------- | -----------------
| `name`   | `-`        | プラグインの名前

| オプション (ショートカット)   | デフォルト | 説明
| ---------------------------- | ---------- | ------------------------------------
| `--stability`<br />`(-s)`    | `-`        | 安定性 (stable、beta、alpha)
| `--release`<br />`(-r)`      | `-`        | 優先バージョン
| `--channel`<br />`(-c)`      | `-`        | PEAR チャンネルの名前
| `--install_deps`<br />`(-d)` | `-`        | 必須の依存パッケージのインストールを強制するか
| `--force-license`            | `-`        | MITのようなライセンスではない場合にインストールを強制するか

`plugin:install` タスクはプラグインをインストールします:

    ./symfony plugin:install sfGuardPlugin

デフォルトでは、最新の `stable` リリースがインストールされます。

まだ安定版ではないプラグインをインストールしたい場合、`stability` オプションを指定します:

    ./symfony plugin:install --stability=beta sfGuardPlugin
    ./symfony plugin:install -s beta sfGuardPlugin

特定のバージョンのインストールを強制することもできます:

    ./symfony plugin:install --release=1.0.0 sfGuardPlugin
    ./symfony plugin:install -r 1.0.0 sfGuardPlugin

依存の必須パッケージをすべて強制的にインストールするには、`install_deps` フラグを指定します:

    ./symfony plugin:install --install-deps sfGuardPlugin
    ./symfony plugin:install -d sfGuardPlugin

デフォルトで使われる PEAR チャンネルは `symfony-plugins` (plugins.symfony-project.org)です。

`channel` オプションで別のチャンネルを指定できます:

    ./symfony plugin:install --channel=mypearchannel sfGuardPlugin
    ./symfony plugin:install -c mypearchannel sfGuardPlugin

Web サイトでホストされている PEAR パッケージをインストールすることもできます:

    ./symfony plugin:install http://somewhere.example.com/sfGuardPlugin-1.0.0.tgz

もしくはローカルな PEAR パッケージ:

    ./symfony plugin:install /home/fabien/plugins/sfGuardPlugin-1.0.0.tgz

プラグインが Web コンテンツ(画像、スタイルシートもしくは JavaScript)を収める場合、このタスクはこれらのアセットのために `web/` の下で `%name%` シンボリックリンクを作成します。Windows では、このタスクはこれらすべてのファイルを `web/%name%` ディレクトリにコピーします。

### ~`plugin::list`~

`plugin::list` タスクはインストールされたプラグインの一覧を表示する:

    $ php symfony plugin:list  

*エイリアス*: `plugin-list`

`plugin:list` タスクはインストールされたプラグインの一覧を表示します:

    ./symfony plugin:list

これはそれぞれのプラグインのチャンネルとバージョンも表示します。

### ~`plugin::publish-assets`~

`plugin::publish-assets` タスクはすべてのプラグインの Web アセットを公開する:

    $ php symfony plugin:publish-assets [--core-only] [plugins1] ... [pluginsN]

| 引数      | デフォルト  | 説明
| --------- | ---------- | -------------------------------
| `plugins` | `-`        | プラグインのアセットを公開する 

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | --------- | ---------------------------------------------------------
| `--core-only`              | `-`       | セットされている場合コアプラグインのみがアセットを公開します

`plugin:publish-assets` タスクはすべてのプラグインから Web アセットを公開します。

    ./symfony plugin:publish-assets

実際これはそれぞれのプラグインに `plugin.post_install` イベントを送信します。

これらのプラグインの名前を引数として渡すことでひとつもしくは複数のプラグインがアセットをインストールするか指定できます:

    ./symfony plugin:publish-assets sfDoctrinePlugin

### ~`plugin::uninstall`~

`plugin::uninstall` タスクはプラグインをアンインストールする:

    $ php symfony plugin:uninstall [-c|--channel="..."] [-d|--install_deps] name

*エイリアス*: `plugin-uninstall`

| 引数     | デフォルト  | 説明
| -------- | ---- ----- | ----------------
| `name`   | `-`        | プラグインの名前

| オプション (ショートカット)    | デフォルト | 説明
| ----------------------------- | ---------- | ---------------------------------------
| `--channel`<br />`(-c)`       | `-`        | PEAR チャンネル名
| `--install_deps`<br />`(-d)`  | `-`        | 依存パッケージをインストールするかどうか

`plugin:uninstall` タスクはプラグインをアンインストールします:

    ./symfony plugin:uninstall sfGuardPlugin

デフォルトのチャンネルは `symfony` です。

異なるチャンネルを持つプラグインをアンインストールすることもできます:

    ./symfony plugin:uninstall --channel=mypearchannel sfGuardPlugin

    ./symfony plugin:uninstall -c mypearchannel sfGuardPlugin

もしくは `channel/package` の表記を使うことができます:

    ./symfony plugin:uninstall mypearchannel/sfGuardPlugin

`plugin:list` タスクを立ち上げてプラグインの PEAR チャンネルを取得できます。

プラグインが Web コンテンツを収める場合 (画像、スタイルシートもしくは JavaScript)、このタスクは `web/%name%` シンボリックリンク (Unix 系) もしくはディレクトリ (Windows) も削除します。

### ~`plugin::upgrade`~

`plugin::upgrade` タスクはプラグインをアップグレードする:

    $ php symfony plugin:upgrade [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] name

*エイリアス*: `plugin-upgrade`

| 引数     | デフォルト  | 説明
| -------- | ---------- | ----------------
| `name`   | `-`        | プラグインの名前

| オプション (ショートカット) | デフォルト   | 説明
| -------------------------- | ----------- | -----------------------------
| `--stability`<br />`(-s)`  | `-`         | 安定性 (stable、beta、alpha)
| `--release`<br />`(-r)`    | `-`         | 優先バージョン
| `--channel`<br />`(-c)`    | `-`         | PEAR チャンネルの名前

`plugin:upgrade` タスクはプラグインをアップグレードしようとします:

    ./symfony plugin:upgrade sfGuardPlugin

デフォルトのチャンネルは `symfony` です。

プラグインが Web コンテンツを収める場合 (画像、スタイルシートもしくは JavaScript)、Windows ではこのタスクは `web/%name%` ディレクトリのコンテンツもアップグレードします。

プラグインの名前とオプションのフォーマットの詳細情報に関しては `plugin:install` を参照してください。

`project`
---------

### ~`project::clear-controllers`~

`project::clear-controllers` タスクは運用環境以外のコントローラーをクリアする:

    $ php symfony project:clear-controllers  

*エイリアス*: `clear-controllers`

`project:clear-controllers` タスクは運用環境以外のコントローラーをクリアします:

    ./symfony project:clear-controllers

運用サーバーで運用環境以外のすべてのフロントコントローラーを削除するためにこのタスクを使うことができます。

`frontend` と `backend` という名前の2つのアプリケーションがある場合、`web/` にはデフォルトのコントローラーは4つあります:

    index.php
    frontend_dev.php
    backend.php
    backend_dev.php

`project:clear-controllers` タスクを実行した後で、`web/` には2つのフロントコントローラーが残ります:

    index.php
    backend.php

デバッグモードと Web デバッグツールバーが無効なのでこれら2つのコントローラーは安全です。

### ~`project:deploy`~

`project:deploy` タスクはプロジェクトを別のサーバーにデプロイする:

    $ php symfony project:deploy [--go] [--rsync-dir="..."] [--rsync-options[="..."]] server

*エイリアス*: `sync`

| 引数     | デフォルト  | 説明
| -------- | ---------- | ---------------
| `server` | `-`        | サーバーの名前

| オプション (ショートカット) | デフォルト    | 説明
| -------------------------- | ------------- | ---------------------------------------------
| `--go`           | `-`                     | デプロイを行う
| `--rsync-dir`    | `config`                | rsync*.txt ファイルを探すディレクトリ
| `--rsync-options`| `-azC --force --delete --progress` | rsync 実行ファイルに渡すオプション

`project:deploy` タスクはプロジェクトをサーバーにデプロイします:

    ./symfony project:deploy production

サーバーは `config/properties.ini` で設定しなければなりません:

    [production]
      host=www.example.com
      port=22
      user=fabien
      dir=/var/www/sfblog/
      type=rsync

デプロイを自動化するために、このタスクは SSH 越しに rsync を使います。キーで SSH のアクセス権限を設定するか `config/properties.ini` でパスワードを設定しなければなりません。

デフォルトでは、このタスクはドライモードです。本当にデプロイするには、`--go` オプションを渡さなければなりません:

    ./symfony project:deploy --go production

`config/rsync_exclude.txt` で設定されるファイルとディレクトリはデプロイされません:

    .svn
    /web/uploads/*
    /cache/*
    /log/*

`rsync.txt` と `rsync_include.txt` ファイルも作成できます。

サーバーにもとづいて `rsync*.txt` ファイルをカスタマイズする必要がある場合、`rsync-dir` オプションを渡すことができます:

    ./symfony project:deploy --go --rsync-dir=config/production production

最後に、`rsync-options` オプションをつけることで、rsync 実行ファイルに渡されるオプションを指定することができます (デフォルトは `-azC --force --delete --progress`):

    ./symfony project:deploy --go --rsync-options=-avz

### ~`project::disable`~

`project::disable` タスクは任意の環境のアプリケーションを無効にする:

    $ php symfony project:disable  env [app1] ... [appN]

*エイリアス*: `disable`

| 引数          | デフォルト  | 説明
| ------------- | ---------- | ----------------------
| `env`         | `-`        | 環境の名前
| `app`         | `-`        | アプリケーションの名前

`project:disable` タスクは環境を無効にします:

    ./symfony project:disable prod

指定環境で無効にするアプリケーションを個別に指定することもできます:

    ./symfony project:disable prod frontend backend

### ~`project::enable`~

`project::enable` タスクは任意の環境のアプリケーションを有効にする:

    $ php symfony project:enable  env [app1] ... [appN]

*エイリアス*: `enable`

| 引数          | デフォルト | 説明
| ------------- | ---------- | -----------------------
| `env`         | `-`        | 環境の名前
| `app`         | `-`        | アプリケーションの名前

`project:enable` タスクは特定の環境を有効にします:

    ./symfony project:enable frontend prod

指定環境で無効にするアプリケーションを個別に指定することもできます:

    ./symfony project:enable prod frontend backend

### ~`project::optimize`~

`project::optimize` タスクはよりよいパフォーマンスのためにプロジェクトを最適化する:

    $ php symfony project:optimize  environment [application]

| 引数          | デフォルト | 説明
| ------------- | --------- | ----------------------
| `application` | `-`       | アプリケーションの名前
| `environment` | `prod`    | 環境の名前

`project:optimize` はよりよいパフォーマンスのためにプロジェクトを最適化します:

    ./symfony project:optimizes prod frontend

このタスクは運用サーバーのみで使うべきです。プロジェクトを変更するたびにこのタスクを再実行することをお忘れなく。

### ~`project::permissions`~

`project::permissions` タスクは symfony のディレクトリのパーミッションを修正する:

    $ php symfony project:send-emails [--application[="..."]] [--env="..."] [--message-limit[="..."]] [--time-limit[="..."]] 

| オプション (ショートカット) | デフォルト | 説明
| ------------------ | ----- | ---------------------------------
| `--application`    | `1`   | アプリケーションの名前
| `--env`            | `dev` | 環境
| `--message-limit`  | `0`   | 送信するメッセージの最大数
| `--time-limit`     | `0`   | メッセージ送信の時間制限 (秒単位)

*エイリアス*: `permissions, fix-perms`

`project:permissions` タスクはディレクトリのパーミッションを修正します:

    ./symfony project:permissions

### ~`project::send-emails`~

`project::send-emails` タスクはキューに保存されるメールを送信する:

    $ php symfony project:send-emails [--application[="..."]] [--env="..."] 

| オプション (ショートカット)  | デフォルト  | 説明
| --------------------------- | ---------- | ----------------------
| `--application`             | `1`        | アプリケーションの名前
| `--env`                     | `dev`      | 環境

`project:send-emails` はキューに保存されるメールを送信します:

    php symfony project:send-emails

送信するメッセージの数を制限できます:

    php symfony project:send-emails --message-limit=10

もしくは時間を制限できます (秒単位):

    php symfony project:send-emails --time-limit=10

### ~`project::upgrade1.3`~

`project::upgrade1.3` タスクは symfony のプロジェクトを (1.2) から symfony 1.3 リリースにアップグレードする:

    $ php symfony project:upgrade1.3  

`project:upgrade1.3` タスクは1.2リリースの symfony プロジェクトを1.3リリースにアップグレードします。

    ./symfony project:upgrade1.3

このタスクが行うことに関する情報は"プロジェクトを1.2から1.3／1.4にアップグレードする"のチュートリアルを参照してくださるようお願いします。

`propel`
--------

### ~`propel::build-all`~

`propel::build-all` タスクは Propel モデルとフォームクラス、SQL を生成しデータベースを初期化する:

    $ php symfony propel:build-all [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] 

*エイリアス*: `propel-build-all`

| オプション (ショートカット)     | デフォルト | 説明
| ----------------------------- | ---------- | ------------------------------
| `--application`               | `1`        | アプリケーションの名前
| `--env`                       | `dev`      | 環境
| `--connection`                | `propel`   | 接続の名前
| `--no-confirmation`           | `-`        | 確認の質問をしない
| `--skip-forms`<br />`(-F)`    | `-`        | フォーム生成を省く
| `--classes-only`<br />`(-C)`  | `-`        | データベースを初期化しない
| `--phing-arg`                 | `-`        | Phing の任意の引数 (複数の値が可)

`propel:build-all` タスクはほかの5つのタスクのショートカットです:

    ./symfony propel:build-all

このタスクは次のものと同等です:

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

詳細な情報はこれらのタスクのヘルプページを参照してください。

確認のプロンプトを回避するには、`no-confirmation` オプションを渡します:

    ./symfony propel:buil-all --no-confirmation

すべてのクラスをビルドするがデータベースの初期化を省くには、`classes-only` オプションを指定します:

    ./symfony propel:build-all --classes-only

### ~`propel::build-all-load`~

`propel::build-all-load` タスクは Propel モデルとフォームクラス、SQL を生成し、データベースを初期化し、データをロードします:

    $ php symfony propel:build-all-load [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] [--append] [--dir="..."] 

*エイリアス*: `propel-build-all-load`

| オプション (ショートカット)   | デフォルト | 説明
| ---------------------------- | ---------- | ---------------------------------------------------
| `--application`              | `1`        | アプリケーションの名前
| `--env`                      | `dev`      | 環境
| `--connection`               | `propel`   | 接続名
| `--no-confirmation`          | `-`        | 確認の質問をしない
| `--skip-forms`<br />`(-F)`   | `-`        | フォーム生成を省く
| `--classes-only`<br />`(-C)` | `-`        | データベースを初期化しない
| `--phing-arg`                | `-`        | Phing の任意の引数 (複数の値が可)
| `--append`                   | `-`        | データベースの現在の値を削除しない
| `--dir`                      | `-`        | フィクスチャを探すディレクトリ (複数の値が許可される)

`propel:build-all-load` タスクはほかの2つのタスクのショートカットです:

    ./symfony propel:build-all-load

このタスクは次のものと同等です:

    ./symfony propel:build-all
    ./symfony propel:data-load

詳細な情報はこれらのタスクのヘルプページを参照してください。

確認を回避するには、`no-confirmation` オプションを渡します:

    ./symfony propel:buil-all-load --no-confirmation

### ~`propel::build-filters`~

`propel::build-filters` タスクは現在のモデルのフィルターフォームクラスを作成する:

    $ php symfony propel:build-filters [--connection="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--application[="..."]] 

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ------------------------------------
| `--connection`             | `propel`   | 接続名
| `--model-dir-name`         | `model`    | モデルディレクトリ名
| `--filter-dir-name`        | `filter`   | フィルターフォームのディレクトリ名
| `--application`            | `1`        | アプリケーション名

`propel:build-filters` タスクはスキーマからフィルターフォームクラスを作成します:

    ./symfony propel:build-filters

このタスクはプロジェクトとすべてのインストールされたプラグインから `config/*schema.xml` かつ `/` もしくは `config/*schema.yml` のスキーマ情報を読み込みます。

このタスクは `config/databases.yml` で定義される `propel` 接続を使います。`--connection` オプションをつけることで別の接続が使えます:

    ./symfony propel:build-filters --connection="name"

モデルのフィルターフォームクラスは `lib/filter` に作成されます。

このタスクは `lib/filter` のなかのカスタムクラスをけっして上書きしません。これは `lib/filter/base` で生成された既定クラスを置き換えます。

### ~`propel::build-forms`~

`propel::build-forms` タスクは現在のモデルのフォームクラスを作成する:

    $ php symfony propel:build-forms [--connection="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--application[="..."]] 

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | --------------------------
| `--connection`             | `propel`   | 接続名
| `--model-dir-name`         | `model`    | モデルディレクトリの名前
| `--form-dir-name`          | `form`     | フォームディレクトリの名前
| `--application`            | `1`        | アプリケーションの名前

`propel:build-forms` タスクはスキーマからフォームクラスを作成します:

    ./symfony propel:build-forms

このタスクはプロジェクトとインストールしたすべてのプラグインから `config/*schema.xml` かつ／もしくは `config/*schema.yml` のスキーマ情報を読み込みます。

このタスクは `config/databases.yml` で定義される `propel` 接続を使います。`--connection` オプションをつけることで別の接続を使うことができます:

    ./symfony propel:build-forms --connection="name"

モデルのフォームクラスは `lib/form` に作成されます。

このタスクは `lib/form` のカスタムクラスをけっして上書きしません。これは `lib/form/base` に生成された基底クラスのみを置き換えます。

### ~`propel::build-model`~

`propel::build-model` タスクは現在のモデルのクラスを作成する:

    $ php symfony propel:build-model [--phing-arg="..."] 

*エイリアス*: `propel-build-model`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | --------------------------------
| `--phing-arg`              | `-`        | Phing の任意の引数 (複数の値が可)

`propel:build-model` タスクはスキーマからモデルクラスを作成します:

    ./symfony propel:build-model

このタスクはプロジェクトとインストールされたすべてのプラグインから `config/*schema.xml` かつ／もしくは `config/*schema.yml` のスキーマ情報を読み込みます。

YAML と XML スキーマファイルを混ぜることができます。このタスクは Propel タスクを呼び出す前に YAML を XML に変換します。

モデルクラスのファイルは `lib/model` に作成されます。

このタスクは `lib/model` のなかのカスタムクラスをけっして置き換えません。`lib/model/om` と `lib/model/map` のファイルのみを置き換えます。

### ~`propel::build-schema`~

`propel::build-schema` タスクは既存のデータベースからスキーマを作成する:

    $ php symfony propel:build-schema [--application[="..."]] [--env="..."] [--connection="..."] [--xml] [--phing-arg="..."] 

*エイリアス*: `propel-build-schema`

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | --------------------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `cli`      | 環境
| `--connection`             | `-`        | 接続名
| `--xml`                    | `-`        | YAML の代わりに XML スキーマを作成する
| `--phing-arg`              | `-`        | Phing の任意の引数(複数の値が可)

`propel:build-schema` タスクはスキーマを作成するためにデータベースをイントロスペクトします:

    ./symfony propel:build-schema

デフォルトでは、このタスクは YAML ファイルを作成しますが、XML ファイルも作成することができます:

    ./symfony --xml propel:build-schema

XML フォーマットは YAML よりも多くの情報を収めることができます。

### ~`propel::build-sql`~

`propel::build-sql` タスクは現在のモデルの SQL を作成する:

    $ php symfony propel:build-sql [--phing-arg="..."] 

*エイリアス*: `propel-build-sql`

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | --------------------------------
| `--phing-arg`              | `-`        | Phing の任意の引数 (複数の値が可)

`propel:build-sql` タスクはテーブル作成用の SQL ステートメントを作成します:

    ./symfony propel:build-sql

生成される SQL は `config/propel.ini`で 設定されるデータベースに最適化されます:

    propel.database = mysql

### ~`propel::data-dump`~

`propel::data-dump` タスクはデータをフィクスチャディレクトリにダンプする:

    $ php symfony propel:data-dump [--application[="..."]] [--env="..."] [--connection="..."] [--classes="..."] [target]

*エイリアス*: `propel-dump-data`

| 引数     | デフォルト  | 説明
| -------- | ---------- | ---------------------
| `target` | `-`        | ターゲットのファイル名

| オプション (ショートカット)  | デフォルト | 説明
| --------------------------- | ---------- | ------------------------------------------
| `--application`             | `1`        | アプリケーションの名前
| `--env`                     | `cli`      | 環境
| `--connection`              | `propel`   | 接続名
| `--classes`                 | `-`        | ダンプするクラスの名前 (コロンで区切られる)

`propel:data-dump` タスクはデータベースデータをダンプします:

    ./symfony propel:data-dump > data/fixtures/dump.yml

デフォルトでは、タスクはデータを標準出力しますが、2番目の引数としてファイルの名前を渡すこともできます:

    ./symfony propel:data-dump dump.yml

このタスクはデータを `data/fixtures/%target%` にダンプします(この例では `data/fixtures/dump.yml`)。

`propel:data-load` タスクを使うことでダンプファイルを YAML フォーマットで再インポートできます。

デフォルトでは、`config/databases.yml` で定義される `propel` 接続を使います。`connection` オプションをつけることで別の接続を使うことができます:

    ./symfony propel:data-dump --connection="name"

クラスをダンプしたいだけなら、`classes` オプションを指定します:

    ./symfony propel:data-dump --classes="Article,Category"

アプリケーションから特定のデータベース接続を使いたい場合、`application` オプションを指定します:

    ./symfony propel:data-dump --application=frontend

### ~`propel::data-load`~

`propel::data-load` タスクは YAML フィクスチャデータをロードする:

    $ php symfony propel:data-load [--application[="..."]] [--env="..."] [--append] [--connection="..."] [--dir="..."] 

*エイリアス*: `propel-load-data`

| 引数          | デフォルト  | 説明
| ------------- | ---------- | ---------------------------------------
| `dir_or_file` | `-`        | ロードするディレクトリもしくはファイル

| オプション (ショートカット)  | デフォルト  | 説明
| --------------------------- | ---------- | ---------------------------------------
| `--application` | `1`       | アプリケーションの名前
| `--env`         | `cli`     | 環境
| `--append`      | `-`       | データベースの現在の値を削除しない
| `--connection`  | `propel`  | 接続名

`propel:data-load` タスクはデータフィクスチャをデータベースにロードします:

    ./symfony propel:data-load

このタスクは `data/fixtures/` で見つかるすべてのファイルからデータをロードします。

特定のファイルもしくはディレクトリからデータをロードしたいのであれば、これらを引数として追加できます:

    ./symfony propel:data-load data/fixtures/dev data/fixtures/users.yml

このタスクは `config/databases.yml` で定義される `propel` 接続を使います。`--connection` オプションをつけることで別の接続を使うことができます:

    ./symfony propel:data-load --connection="name"

このタスクにデータベースの既存のデータを削除させたくない場合、`--append` オプションを指定します:

    ./symfony propel:data-load --append

アプリケーションからデータベース接続を使いたい場合、`application` オプションを指定します:

    ./symfony propel:data-load --application=frontend

### ~`propel::generate-admin`~

`propel::generate-admin` タスクは Propel の admin モジュールを生成する:

    $ php symfony propel:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model

| 引数             | デフォルト  | 説明
| ---------------- | ---------- | ---------------------------------
| `application`    | `-`        | アプリケーションの名前
| `route_or_model` | `-`        | ルートの名前もしくはモデルクラス

| オプション (ショートカット) | デフォルト   | 説明
| -------------------------- | ----------- | -----------------
| `--module`                 | `-`         | モジュールの名前
| `--theme`                  | `admin`     | テーマの名前
| `--singular`               | `-`         | 単数形の名前
| `--plural`                 | `-`         | 複数形の名前
| `--env`                    | `dev`       | 環境
| `--actions-base-class`     | `sfActions` | アクションの基底クラス 

`propel:generate-admin` タスクは Propel の admin モジュールを生成します:

    ./symfony propel:generate-admin frontend Article

このタスクは `%Article%` モデルの `%frontend%` アプリケーションのモジュールを作成します。

このタスクはアプリケーションの `routing.yml` でルートを作成します。

ルートの名前を渡すことでも Propel の admin モジュールを生成することもできます:

    ./symfony propel:generate-admin frontend article

このタスクは `routing.yml` で見つかる `%article%` ルート定義の `%frontend%` アプリケーションのモジュールを作成します。

フィルターとバッチアクションを適切に機能させるために、ルートに `wildcard` オプションを追加する必要があります:

    article:
      class: sfPropelRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`propel::generate-module`~

`propel::generate-module` タスクは Propel モジュールを生成する:

    $ php symfony propel:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-propel-route] [--env="..."] [--actions-base-class="..."] application module model

*エイリアス*: `propel-generate-crud`、`propel:generate-crud`

| 引数          | デフォルト | 説明
| ------------- | ---------- | -----------------------
| `application` | `-`        | アプリケーションの名前
| `module`      | `-`        | モジュールの名前
| `model`       | `-`        | モデルクラスの名前

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | ----------------------------------
| `--theme`                  | `default`  | テーマの名前
| `--generate-in-cache`      | `-`        | キャッシュでモジュールを生成する
| `--non-verbose-templates`  | `-`        | 冗長ではないテンプレートを生成する
| `--with-show`              | `-`        | show メソッドを生成する
| `--singular`               | `-`        | 単数形の名前
| `--plural`                 | `-`        | 複数形の名前
| `--route-prefix`           | `-`        | ルートのプレフィックス
| `--with-propel-route`      | `-`        | Propel のルートを使うかどうか
| `--env`                    | `dev`      | 環境
| `--actions-base-class`     | `sfActions`| アクションの基底クラス 

`propel:generate-module` タスクは Propel モジュールを生成します:

    ./symfony propel:generate-module frontend article Article

このタスクは `%model%` モデルクラスの `%application%` アプリケーションで `%module%` モジュールを作成します。

`--generate-in-cache` オプションをつけることで `%sf_app_cache_dir%/modules/auto%module%` の実行時に生成されるモジュールからアクションとテンプレートを継承する空のモジュールを作成することもできます:

    ./symfony propel:generate-module --generate-in-cache frontend article Article

`--theme` オプションをつけることでジェネレーターはカスタマイズされたテーマを使うことができます:

    ./symfony propel:generate-module --theme="custom" frontend article Article

この方法では、独自仕様に合わせてモジュールジェネレーターを作ることができます。

### ~`propel::generate-module-for-route`~

`propel::generate-module-for-route` タスクはルート定義の Propel モジュールを生成する:

    $ php symfony propel:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route

| 引数          | デフォルト  | 説明
| ------------- | ---------- | -----------------------
| `application` | `-`        | アプリケーションの名前
| `route`       | `-`        | ルートの名前

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ----------------------------------
| `--theme`                  | `default`  | テーマの名前
| `--non-verbose-templates`  | `-`        | 冗長ではないテンプレートを生成する
| `--singular`               | `-`        | 単数形の名前
| `--plural`                 | `-`        | 複数形の名前
| `--env`                    | `dev`      | 環境
| `--actions-base-class`     | `sfActions`| アクションの基底クラス 

`propel:generate-module-for-route` タスクはルート定義の Propel モジュールを生成します:

    ./symfony propel:generate-module-for-route frontend article

このタスクは `routing.yml` で見つかる `%article%` ルート定義の `%frontend%` アプリケーションでモジュールを作成します。

### ~`propel::graphviz`~

`propel::graphviz` タスクは現在のオブジェクトモデルの Graphviz によるチャートを生成する:

    $ php symfony propel:graphviz [--phing-arg="..."] 

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | -------------------------------
| `--phing-arg`              | `-`        | Phing の任意の引数 (複数の値が可)

`propel:graphviz` タスクはオブジェクトモデルのグラフの自動描画に Graphviz の DOT を作成します:

    ./symfony propel:graphviz

### ~`propel::init-admin`~

`propel::init-admin` タスクは Propel の admin モジュールを初期化する:

    $ php symfony propel:init-admin [--theme="..."] application module model

*エイリアス*: `propel-init-admin`

| 引数          | デフォルト | 説明
| ------------- | ---------- | ----------------------
| `application` | `-`        | アプリケーションの名前
| `module`      | `-`        | モジュールの名前
| `model`       | `-`        | モデルクラスの名前

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | -------------
| `--theme`                  | `default`  | テーマの名前

`propel:init-admin` タスクは Propel の admin モジュールを生成します:

    ./symfony propel:init-admin frontend article Article

`%model%` モデルクラスの `%application%` アプリケーションで `%module%` モジュールを作成します。

作成されるモジュールは `%sf_app_cache_dir%/modules/auto%module%` の実行時に生成されるモジュールからアクションとテンプレートを継承する空のモジュールです。

ジェネレーターは `--theme` オプションをつけることでカスタマイズされたテーマを使うことができます:

    ./symfony propel:init-admin --theme="custom" frontend article Article

### ~`propel::insert-sql`~

`propel::insert-sql` タスクは現在のモデルの SQL を INSERT する:

    $ php symfony propel:insert-sql [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [--phing-arg="..."] 

*エイリアス*: `propel-insert-sql`

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | --------------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `cli`      | 環境
| `--connection`             | `-`        | 接続名
| `--no-confirmation`        | `-`        | 確認の質問をしない
| `--phing-arg`              | `-`        | Phing の任意の引数 (複数の値が可)

`propel:insert-sql` タスクはデータベースのテーブルを作成します:

    ./symfony propel:insert-sql

このタスクはデータベースに接続して `config/sql/*schema.sql` ファイルで見つかるすべての SQL ステートメントを実行します。

実行する前に、データベースのすべてのデータが削除されるのでこのタスクは本当に実行するか確認します。

確認を回避するには、`--no-confirmation` オプションを渡します:

    ./symfony propel:insert-sql --no-confirmation

このタスクは `databases.yml` からデータベース設定を読み込みます。`--application` もしくは `--env` オプションを渡せば特定のアプリケーションの環境を使うことができます。

任意の接続で SQL ステートメントをロードしたいだけなら `--connection` オプションをつけます。

### ~`propel::schema-to-xml`~

`propel::schema-to-xml` タスクは `schema.yml` から `schema.xml` を作成する:

    $ php symfony propel:schema-to-xml  

*エイリアス*: `propel-convert-yml-schema`

`propel:schema-to-xml` タスクは YAML スキーマを XML スキーマに変換します:

    ./symfony propel:schema-to-xml

### ~`propel::schema-to-yml`~

`propel::schema-to-yml` タスクは `schema.xml` から `schema.yml` を作成する:

    $ php symfony propel:schema-to-yml  

*エイリアス*: `propel-convert-xml-schema`

`propel:schema-to-yml` タスクは XML スキーマを YAML に変換します:

    ./symfony propel:schema-to-yml

`symfony`
---------

### ~`symfony::test`~

`symfony::test`タスクは symfony テストスイートを立ち上げる:

    $ php symfony symfony:test [-u|--update-autoloader] [-f|--only-failed] [--xml="..."] [--rebuild-all] 

| オプション (ショートカット)        | デフォルト | 説明
| --------------------------------- | --------- | -------------------------------------------
| `--update-autoloader`<br />`(-u)` | `-`       | sfCoreAutoload クラスを更新する
| `--only-failed`<br />`(-f)`       | `-`       | 最後に通らなかったテストのみを実行する
| `--xml`                           | `-`       | JUnit と互換性のある XML ログファイルの名前
| `--rebuild-all`                   | `-`       | すべての生成フィクスチャファイルをリビルドする

`test:all`タスクは symfony テストスイートを立ち上げます:

    ./symfony symfony:test

`test`
------

### ~`test::all`~

`test::all` タスクはすべてのテストを立ち上げる:

    $ php symfony test:all [-f|--only-failed] [--xml="..."]  

*エイリアス*: `test-all`

| オプション (ショートカット)  | デフォルト  | 説明
| --------------------------- | ---------- | -------------------------------------------
| `--only-failed`<br />`(-f)` | `-`        | 最後に通らなかったテストのみを実行する
| `--xml`                     | `-`        | JUnit と互換性のある XML ログファイルの名前

`test:all` タスクはすべてのユニットテストと機能テストを立ち上げます:

    ./symfony test:all

このタスクは `test/` で見つかるすべてのテストを立ち上げます。

テストの一部が通らないのであれば、通らないことに関する詳しい情報を表示するために `--trace` オプションをつけます:

      `./symfony test:all -t

もしくは `test:unit` と `test:functional` タスクでこれらのテストスイートを立ち上げることで問題の修正にとりかかることもできます。

以前の実行のときに通らなかったテストのみの実行を強制するために `--only-failed` オプションをつけます:

      `./symfony test:all --only-failed

どのように動くのは次のとおりです: 最初に、通常のようにすべてのテストが実行されます。しかしその後実行されるテストでは、前回通らなかったテストのみが実行されます。コードを修正するにつれて、テストの一部が通るようになり、その後の実行から除外されるようになります。すべてのテストが通るとき、フルテストスイートが実行され、洗い流し繰り返すことができます。

このタスクは `--xml` オプションで JUnit と互換性のある XML ログファイルを出力することができます:

    ./symfony test:all --xml=log.xml

### ~`test::coverage`~

`test::coverage` タスクはテストのコードカバレッジを出力する:

    $ php symfony test:coverage [--detailed] test_name lib_name

| 引数        | デフォルト  | 説明
| ----------- | ---------- | --------------------------------------------------------------
| `test_name` | `-`        | テストファイルの名前もしくはテストのディレクトリ
| `lib_name`  | `-`        | カバレージを知りたい lib ファイルの名前もしくは lib ディレクトリ

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | ---------- | --------------------
| `--detailed`               | `-`        | 詳細な情報を出力する

`test:coverage` タスクはテストディレクトリもしくはテストディレクトリと lib ファイルもしくは lib ディレクトリのほしいコードカバレージを出力します:

    ./symfony test:coverage test/unit/model lib/model

カバーされていない行を出力するには、`--detailed` オプションを渡します:

    ./symfony test:coverage --detailed test/unit/model lib/model

### ~`test::functional`~

`test::functional` タスクは機能テストを立ち上げる:

    $ php symfony test:functional [--xml="..."] application [controller1] ... [controllerN]

*エイリアス*: `test-functional`

| 引数          | デフォルト | 説明
| ------------- | ---------- | -----------------------
| `application` | `-`        | アプリケーションの名前
| `controller`  | `-`        | コントローラーの名前

| オプション (ショートカット) | デフォルト  | 説明
| -------------------------- | ---------- | ---------------
| `--xml` | `-` | JUnit と互換性のある XML ログファイルの名前

`test:functional` タスクは任意のアプリケーション用の機能テストを立ち上げます:

    ./symfony test:functional frontend

このタスクは `test/functional/%application%` で見つかるすべてのテストを立ち上げます。

テストの一部が通らないのであれば、通らないことに関する詳しい情報を表示するために `--trace` オプションをつけます:

      `./symfony test:functional frontend -t

コントローラーの名前を渡すことで特定のコントローラーのすべての機能テストを立ち上げることができます:

    ./symfony test:functional frontend article

複数のコントローラーの機能テストをすべて立ち上げることもできます:

    ./symfony test:functional frontend article comment

このタスクは `--xml` オプションで JUnit と互換性のある XML ログファイルを出力します:

    ./symfony test:functional --xml=log.xml

### ~`test::unit`~

`test::unit` タスクはユニットテストを立ち上げます:

    $ php symfony test:unit [--xml="..."] [name1] ... [nameN]

*エイリアス*: `test-unit`

| 引数     | デフォルト  | 説明
| -------- | ---------- | -----------
| `name`   | `-`        | テストの名前

| オプション (ショートカット) | デフォルト | 説明
| -------------------------- | --------- | ----------------------------
| `--xml`       | `-`        | JUnit と互換性のある XML ログファイルの名前

`test:unit` タスクはユニットテストを立ち上げます:

    ./symfony test:unit

このタスクは `test/unit` で見つかるすべてのテストを立ち上げます。

テストの一部が通らないのであれば、通らないことに関する詳しい情報を表示するために `--trace` オプションをつけます:

      `./symfony test:unit -t

特定の名前のユニットテストを立ち上げることができます:

    ./symfony test:unit strtolower

複数の名前のユニットテストを立ち上げることもできます:

    ./symfony test:unit strtolower strtoupper

このタスクは `--xml` オプションで JUnit と互換性のある XML ログファイルを出力できます:

    ./symfony test:unit --xml=log.xml


