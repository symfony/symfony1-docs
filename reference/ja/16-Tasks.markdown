タスク
======

symfony フレームワークには CLI (コマンドラインインターフェイス) ツールが搭載されています。組み込みのタスクを利用することで、開発しているあいだのさまざまな作業が軽減されます。

引数に何も渡さずに `symfony` コマンドを実行すると、利用可能なタスクの一覧が表示されます。

    $ php symfony

`-V` オプションを渡すと、symfony のバージョンと CLI で使われる symfony ライブラリのパス情報が表示されます。

    $ php symfony -V

CLI ツールはタスクの名前を第1引数にとります。

    $ php symfony list

タスクの名前は、コロン (`:`) で区切られた名前空間と名前からなります。名前空間はオプションです。

    $ php symfony cache:clear

引数とオプションはタスクの名前の後に渡します。

    $ php symfony cache:clear --type=template

CLI ツールは値の有無およびオプションの長短バージョンをそれぞれサポートしています。

`-t` オプションはグローバルオプションで、デバッグ情報の出力を意味します。

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
   * [`project::validate`](#chapter_16_sub_project_validate)
 * [`propel`](#chapter_16_propel)
   * [`propel::build`](#chapter_16_sub_propel_build)
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

`help` タスクはタスクのヘルプメッセージを表示します。

    $ php symfony help [--xml] [task_name]



| 引数        | デフォルト | 説明
| ----------- | -------- | -----------
| `task_name` | `help`   | タスクの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span>| <span style="white-space: nowrap">デフォルト</span> |説明
| ------------------------- | -------- | -----------------------------
| `--xml`       | `-`       | ヘルプメッセージの出力形式が XML になります。


`help` タスクは任意のタスクのヘルプメッセージを表示します。

    ./symfony help test:all

`--xml` オプションをつければ、ヘルプメッセージは XML 形式で出力されます。

    ./symfony help test:all --xml

### ~`list`~

`list` タスクは利用可能なタスクの一覧を表示します。

    $ php symfony list [--xml] [namespace]



| 引数        | デフォルト | 説明
| ----------- | --------- | --------------
| `namespace` | `-`       | 名前空間の名前


| <span style="white-space: nowrap">オプション (ショートカット)</span>   | <span style="white-space: nowrap">デフォルト</span> | 説明
| ----------------------------- | ---------- | ------------------
| `--xml`  | `-`        | ヘルプメッセージの出力形式が XML になります


`list` タスクはすべてのタスクの一覧を表示します。

    ./symfony list

特定の名前空間に属しているタスクの一覧を表示することもできます。

    ./symfony list test

`--xml` オプションをつければ、出力形式は XML になります。

    ./symfony list --xml

`app`
-----

### ~`app::routes`~

`app::routes` タスクはアプリケーションにおける現在のルートを表示します。

    $ php symfony app:routes  application [name]



| 引数          | デフォルト | 説明
| ------------- | ----------- | -----------------------
| `application` | `-`         | アプリケーションの名前
| `name`        | `-`         | ルートの名前




`app:routes` はアプリケーションにおける現在のルートを表示します。

    ./symfony app:routes frontend

`cache`
-------

### ~`cache::clear`~

`cache::clear` タスクはキャッシュをクリアします。

    $ php symfony cache:clear [--app[="..."]] [--env[="..."]] [--type[="..."]] 

*エイリアス*: `cc`



| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span>| 説明
| ---------------------------- | ---------- | ----------------------
| `--app`                      | `-`        | アプリケーションの名前
| `--env`                      | `-`        | 環境
| `--type`                     | `all`      | 種類


`cache:clear` タスクはアプリケーションのキャッシュをクリアします。

デフォルトでは、このタスクはすべての利用可能な種類、すべてのアプリケーションとすべての環境のキャッシュを削除します。

クリアするキャッシュは種類、アプリケーションもしくは環境で選別することができます。

たとえば `frontend` アプリケーションのキャッシュをクリアするには、`--app` オプションを指定します。

    ./symfony cache:clear --app=frontend

`frontend` アプリケーションにおける `prod` 環境のキャッシュをクリアするには、`--env` オプションも指定します。

    ./symfony cache:clear --app=frontend --env=prod

`prod` 環境においてすべてのキャッシュをクリアするには、`--env` オプションだけを指定します。

    ./symfony cache:clear --env=prod

`prod` 環境の `config` キャッシュをクリアするには、`--type` オプションを指定します。

    ./symfony cache:clear --type=config --env=prod

`--type` オプションに組み込まれている引数は次のとおりです。

`config`、`i18n`、`routing`、`module` と `template`


`configure`
-----------

### ~`configure::author`~

`configure::author` タスクはプロジェクトにおけるコードの著者の名前を設定します。

    $ php symfony configure:author  author



| 引数     | デフォルト| 説明
| -------- | ---------- | --------------------------------------
| `author` | `-`        | プロジェクトにおけるコードの著者の名前




`configure:author` タスクはプロジェクトにおけるコードの著者の名前を設定します。

    ./symfony configure:author "Fabien Potencier <fabien.potencier@symfony-project.com>"

ジェネレータはそれぞれの生成ファイルのなかで PHPDoc ヘッダーを用意する際に著者の名前を使います。

著者の名前は `config/properties.ini` ファイルに保存されます。

### ~`configure::database`~

`configure::database` タスクはデータベースの DSN を設定します。

    $ php symfony configure:database [--env[="..."]] [--name[="..."]] [--class[="..."]] [--app[="..."]] dsn [username] [password]



| 引数       | デフォルト| 説明
| ---------- | --------- | ---------------------------
| `dsn`      | `-`       | データベースの DSN
| `username` | `root`    | データベースユーザーの名前
| `password` | `-`       | データベースのパスワード


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span>          | 説明
| ---------------------------- | -------------------- | ------------------------
| `--env`                      | `all`                | 環境
| `--name`                     | `doctrine`           | コネクションの名前
| `--class`                    | `sfDoctrineDatabase` | データベースクラスの名前
| `--app`                      | `-`                  | アプリケーションの名前


`configure:database` タスクはプロジェクトにおけるデータベースの DSN を設定します。

    ./symfony configure:database mysql:host=localhost;dbname=example root mYsEcret

デフォルトでは、このタスクはすべての環境のコンフィギュレーションを変更します。環境ごとに DSN を変更したいのであれば、`--env` オプションを指定します。

    ./symfony configure:database --env=dev mysql:host=localhost;dbname=example_dev root mYsEcret

特定のアプリケーションのコンフィギュレーションを変更するには、`--app` オプションを指定します。

    ./symfony configure:database --app=frontend mysql:host=localhost;dbname=example root mYsEcret

データベースコネクションの名前とクラスの名前も指定できます。

    ./symfony configure:database --name=main --class=ProjectDatabase mysql:host=localhost;dbname=example root mYsEcret

>**WARNING**
> `Propel` を使う場合、`--app` オプションを指定せずに `--all` 環境のコンフィギュレーションを変更すれば、`propel.ini` ファイルも更新されます。

`doctrine`
----------

### ~`doctrine::build`~

`doctrine::build` タスクはスキーマに対応したコードを生成します。

    $ php symfony doctrine:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-migrate] [--and-load[="..."]] [--and-append[="..."]] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| --------------------------- | ---------- | ---------------------------------------------------------
| `--application`     | `1`  | アプリケーションの名前
| `--env`             | `dev`| 環境
| `--no-confirmation` | `-`  | データベースの削除を強制するか
| `--all`             | `-`  | すべてをビルドしてデータベースをリセットします
| `--all-classes`     | `-`  | すべてのクラスをビルドします
| `--model`           | `-`  | モデルクラスをビルドします
| `--forms`           | `-`  | フォームクラスをビルドします
| `--filters`         | `-`  | フィルタクラスをビルドします
| `--sql`             | `-`  | SQL をビルドします
| `--db`              | `-`  | SQL を削除、作成もしくは挿入する、もしくはデータベースをマイグレートします
| `--and-migrate`     | `-`  | データベースをマイグレートします
| `--and-load`        | `-`  | フィクスチャデータをロードします (複数の値が可能です)
| `--and-append`      | `-`  | フィクスチャデータを追加します (複数の値が可能です)


`doctrine:build` タスクはスキーマに対応したコードを生成します。

    ./symfony doctrine:build

ビルドしたいものを指定しなければなりません。たとえば、モデルとフォームクラスをビルドしたいのであれば、`--model` と `--forms` オプションをつけます。

    ./symfony doctrine:build --model --forms

すべてのクラスと SQL を生成してデータベースを再構築したいのであれば、ショートカットの `--all` オプションをつけます。

    ./symfony doctrine:build --all

上記の例は次のタスクの組み合わせと同じです。

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:build-sql
    ./symfony doctrine:insert-sql

ショートカットの `--all-classes` オプションをつけると、クラスファイルだけが生成されます。このオプションを単独でつけると、データベースは修正されません。

    ./symfony doctrine:build --all-classes

`--and-migrate` オプションをつければ、ビルドが完了したときに保留中のマイグレーションが実行されます。

    ./symfony doctrine:build --db --and-migrate

`--and-load` オプションをつければ、プロジェクトとプラグインの `data/fixtures/` ディレクトリからデータがロードされます。

    ./symfony doctrine:build --db --and-migrate --and-load

指定したフィクスチャをロードの対象に加えるには、フィクスチャのパスを `--and-load` オプションに加えます。

    ./symfony doctrine:build --all --and-load="data/fixtures/dev/"

データベースのレコードを削除せずにフィクスチャを追加するには、`--and-append` オプションをつけます。

    ./symfony doctrine:build --all --and-append

### ~`doctrine::build-db`~

`doctrine::build-db` タスクは現在のモデルに対応したデータベースを作ります。

    $ php symfony doctrine:build-db [--application[="..."]] [--env="..."] [database1] ... [databaseN]

*エイリアス*: `doctrine:create-db`

| 引数       | デフォルト | 説明
| ---------- | --------- | -------------------
| `database` | `-`       | 特定のデータベース


| <span style="white-space: nowrap">オプション (ショートカット)</span>| <span style="white-space: nowrap">デフォルト</span>   | 説明
| --------------------------- | ----------- | -----------------------
| `--application`             | `1`         | アプリケーションの名前
| `--env`                     | `dev`       | 環境


`doctrine:build-db` タスクは `config/` ディレクトリにある `databases.yml` ファイルのコンフィギュレーションをもとに1つもしくは複数のデータベースを作ります。

    ./symfony doctrine:build-db

作るデータベースの名前を指定することもできます。

    ./symfony doctrine:build-db slave1 slave2

### ~`doctrine::build-filters`~

`doctrine::build-filters` タスクは現在のモデルに対応したフィルタフォームクラスを作ります。

    $ php symfony doctrine:build-filters [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--generator-class="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ------------------- | -------- | -------------------------------------------
| `--application`     | `1`      | アプリケーションの名前
| `--env`             | `dev`    | 環境
| `--model-dir-name`  | `model`  | モデルのディレクトリ名
| `--filter-dir-name` | `filter` | フィルタフォームのディレクトリ名
| `--generator-class` | `sfDoctrineFormFilterGenerator` | ジェネレータクラス


`doctrine:build-filters` タスクはスキーマに対応したフォームフィルタクラスを作ります。

    ./symfony doctrine:build-filters

モデルに対応したフォームフィルタクラスは `lib/doctrine/filter/` ディレクトリに作られます。

このタスクが `lib/doctrine/filter/` ディレクトリにあるカスタムクラスを上書きすることはけっしてありません。このタスクは `lib/doctrine/filter/base/` ディレクトリに生成された基底クラスを置き換えるだけです。

### ~`doctrine::build-forms`~

`doctrine::build-forms` タスクは現在のモデルに対応したフォームクラスを作ります。

    $ php symfony doctrine:build-forms [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--generator-class="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span>   | <span style="white-space: nowrap">デフォルト</span> | 説明
| ------------------- | -------- | -------------------------------------------
| `--application`     | `1`      | アプリケーションの名前
| `--env`             | `dev`    | 環境
| `--model-dir-name`  | `model`  | モデルのディレクトリ名
| `--filter-dir-name` | `filter` | フィルタフォームのディレクトリ名
| `--generator-class` | `sfDoctrineFormFilterGenerator` | ジェネレータクラス


`doctrine:build-forms` タスクはスキーマに対応したフォームクラスを作ります。

    ./symfony doctrine:build-forms

上記の例では、モデルに対応したフォームクラスが `lib/doctrine/form/` ディレクトリに作られます。

このタスクは `lib/doctrine/form/` ディレクトリにあるカスタムクラスを上書きすることはけっしてありません。このタスクは `lib/doctrine/form/base/` ディレクトリに生成された基底クラスだけを置き換えるだけです。

### ~`doctrine::build-model`~

`doctrine::build-model` タスクは現在のモデルに対応したクラスを作ります。

    $ php symfony doctrine:build-model [--application[="..."]] [--env="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span>  | 説明
| ---------------------------- | -------- | ------------------------
| `--application`              | `1`      | アプリケーションの名前
| `--env`                      | `dev`    | 環境


`doctrine:build-model` タスクはスキーマに対応したモデルクラスを作ります。

    ./symfony doctrine:build-model

このタスクはプロジェクトと有効なプラグインの `config/doctrine/` ディレクトリに配置されているすべての YAML スキーマファイルの情報を読み込みます。

モデルクラスファイルは `lib/model/doctrine/` ディレクトリに作られます。

このタスクは `lib/model/doctrine/` ディレクトリのなかのカスタムタスクを上書きすることはけっしてありません。このタスクは `lib/model/doctrine/base/` ディレクトリのファイルだけを置き換えます。

### ~`doctrine::build-schema`~

`doctrine::build-schema` タスクは既存のデータベースに対応したスキーマを作ります。

    $ php symfony doctrine:build-schema [--application[="..."]] [--env="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ----------- | -----------------------
| `--application`              | `1`         | アプリケーションの名前
| `--env`                      | `dev`       | 環境


`doctrine:build-schema` タスクはスキーマのもとになるデータベースをイントロスペクトします。

    ./symfony doctrine:build-schema

YAML ファイルが `config/doctrine` ディレクトリに作られます。

### ~`doctrine::build-sql`~

`doctrine::build-sql` タスクは現在のモデルに対応した SQL 文を生成します。

    $ php symfony doctrine:build-sql [--application[="..."]] [--env="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | ----------------------
| `--application`              | `1`     | アプリケーションの名前
| `--env`                      | `dev`   | 環境


`doctrine:build-sql` タスクはテーブル作成の SQL 文を生成します。

    ./symfony doctrine:build-sql

生成される SQL 文は `config/` ディレクトリにある `databases.yml` ファイルで指定されているデータベースに合わせて最適化されます。

    doctrine.database = mysql

### ~`doctrine::clean-model-files`~

`doctrine::clean-model-files` タスクは YAML スキーマにもはや存在しないモデルに合わせて生成されたモデルクラスをすべて削除します。

    $ php symfony doctrine:clean-model-files [--no-confirmation] 

*エイリアス*: `doctrine:clean`



| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | ---------------------------
| `--no-confirmation`          | `-`     | 確認メッセージを省略します


`doctrine:clean-model-files` タスクはプロジェクトもしくはプラグインの `schema.yml` ファイルに存在しないモデルクラスを削除します。

    ./symfony doctrine:clean-model-files

### ~`doctrine::create-model-tables`~

`doctrine::create-model-tables` タスクは指定されたモデルのテーブルを削除した後で再作成します。

    $ php symfony doctrine:create-model-tables [--application[="..."]] [--env="..."] [models1] ... [modelsN]



| 引数     | デフォルト | 説明
| -------- | ------- | --------------
| `models` | `-`     | モデルのリスト


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ---------- | ----------------------
| `--application`              | `frontend` | アプリケーションの名前
| `--env`                      | `dev`      | 環境


`doctrine:create-model-tables` タスクは指定されたモデルのテーブルを削除した後で再作成します。

    ./symfony doctrine:create-model-tables User

### ~`doctrine::data-dump`~

`doctrine::data-dump` タスクはデータベースのデータをフィクスチャディレクトリに吐き出します。

    $ php symfony doctrine:data-dump [--application[="..."]] [--env="..."] [target]



| 引数     | デフォルト | 説明
| -------- | ------- | ---------------------
| `target` | `-`     | ターゲットのファイル名


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | ---------- | ----------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `dev`      | 環境

`doctrine:data-dump` タスクはデータベースのデータを吐き出します。

    ./symfony doctrine:data-dump

データベースのデータは `data/fixtures/%target%/` ディレクトリに吐き出されます。

吐き出された YAML ファイルを再インポートするには `doctrine:data-load` タスクを使います。

    ./symfony doctrine:data-load

### ~`doctrine::data-load`~

`doctrine::data-load` タスクは YAML フィクスチャのデータをロードします。

    $ php symfony doctrine:data-load [--application[="..."]] [--env="..."] [--append] [dir_or_file1] ... [dir_or_fileN]



| 引数          | デフォルト | 説明
| ------------- | ---------- | -----------------------------------------------
| `dir_or_file` | `-`        | ロードの対象となるディレクトリもしくはファイル


| <span style="white-space: nowrap">オプション (ショートカット)</span>| <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------- | ----------- | ----------------------
| `--application`| `1`   | アプリケーションの名前
| `--env`        | `dev` | 環境
| `--append`     | `-`   | データベースの現在のデータは削除しません


`doctrine:data-load` タスクはデータベースにフィクスチャのデータをロードさせます。

    ./symfony doctrine:data-load

`data/fixtures/` ディレクトリで見つかるすべてのファイルからデータがロードされます。

特定のファイルもしくはディレクトリからデータをロードしたいのであれば、引数にこれらのパスを渡します。

    ./symfony doctrine:data-load data/fixtures/dev data/fixtures/users.yml

データベースにおける既存のデータを削除したくなければ、`--append` オプションをつけます。

    ./symfony doctrine:data-load --append

### ~`doctrine::delete-model-files`~

`doctrine::delete-model-files` タスクは任意のモデルに関連した自動生成ファイルをすべて削除します。

    $ php symfony doctrine:delete-model-files [--no-confirmation] name1 ... [nameN]


| 引数   | デフォルト | 説明
| ------ | ------ | ------------------------------------------------
| `name` | `-`    | 削除したいすべてのファイルに関連したモデルの名前



| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ------------------- | ---- | --------------------------
| `--no-confirmation` | `-`  | 確認メッセージを省略します


`doctrine:delete-model-files` タスクは特定のモデルに関連したすべてのファイルを削除します。

    ./symfony doctrine:delete-model-files Article Author

### ~`doctrine::dql`~

`doctrine::dql` タスクは DQL クエリを実行して結果を表示します。

    $ php symfony doctrine:dql [--application[="..."]] [--env="..."] [--show-sql] [--table] dql_query [parameter1] ... [parameterN]



| 引数        | デフォルト | 説明
| ----------- | -------- | -------------------
| `dql_query` | `-`      | 実行する DQL クエリ
| `parameter` | `-`      | クエリパラメータ


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | ------------------------
| `--application`            | `1`       | アプリケーションの名前
| `--env`                    | `dev`     | 環境
| `--show-sql`               | `-`       | 実行される SQL 文を表示します
| `--table`                  | `-`       | 結果を表形式で返します


`doctrine:dql` タスクは DQL クエリを実行して結果を整えて表示します。

    ./symfony doctrine:dql "FROM User"

`--show-sql` オプションをつけると、実行される SQL 文が表示されます。

    ./symfony doctrine:dql --show-sql "FROM User"

追加のクエリパラメータを引数に渡すことができます。

    ./symfony doctrine:dql "FROM User WHERE email LIKE ?" "%symfony-project.com"

### ~`doctrine::drop-db`~

`doctrine::drop-db` タスクは現在のモデルに対応したデータベースを削除します。

    $ php symfony doctrine:drop-db [--application[="..."]] [--env="..."] [--no-confirmation] [database1] ... [databaseN]



| 引数       | デフォルト | 説明
| ---------- | ------ | ------------------
| `database` | `-`    | 特定のデータベース


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | -------------------------------
| `--application`            | `1`       | アプリケーションの名前
| `--env`                    | `dev`     | 環境
| `--no-confirmation`        | `-`       | データベースの削除を強制するか


`config/` ディレクトリに配置されている `databases.yml` ファイルのコンフィギュレーションをもとに `doctrine:drop-db` タスクは1つもしくは複数のデータベースを削除します。

    ./symfony doctrine:drop-db

`--no-confirmation` オプションをつけないかぎり、タスクからデータベースを削除してよいのかたずねられます。

    ./symfony doctrine:drop-db --no-confirmation

削除したいデータベースの名前を指定できます。

    ./symfony doctrine:drop-db slave1 slave2

### ~`doctrine::generate-admin`~

`doctrine::generate-admin` タスクは Doctrine に対応したアドミンモジュールを生成します。

    $ php symfony doctrine:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model



| 引数             | デフォルト | 説明
| ---------------- | ------- | --------------------------------
| `application`    | `-`     | アプリケーションの名前
| `route_or_model` | `-`     | ルートの名前もしくはモデルクラス


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | ---------- | -----------------
| `--module`                 | `-`        | モジュールの名前
| `--theme`                  | `admin`    | テーマの名前
| `--singular`               | `-`        | 単数形の名前
| `--plural`                 | `-`        | 複数形の名前
| `--env`                    | `dev`      | 環境
| `--actions-base-class`     | `sfActions`| アクションの基底クラス 


`doctrine:generate-admin` タスクは Doctrine に対応したアドミンモジュールを生成します。

    ./symfony doctrine:generate-admin frontend Article

上記の例では、`%Article%` モデルに対応したモジュールが `%frontend%` アプリケーションに生成されます。

ルートはアプリケーションの `routing.yml` ファイルに用意されます。

ルートの名前を引数に渡すことで、Doctrine アドミンモジュールを生成することもできます。

    ./symfony doctrine:generate-admin frontend article

上記の例では、`routing.yml` ファイルで定義されている `%article%` ルートに対応したモジュールが `%frontend%` アプリケーションに生成されます。

フィルタとバッチアクションがきちんと動くようにするには、ルートのコンフィギュレーションのなかで `wildcard` オプションに `true` をセットする必要があります。

    article:
      class: sfDoctrineRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`doctrine::generate-migration`~

`doctrine::generate-migration` タスクはマイグレーションクラスを生成します。

    $ php symfony doctrine:generate-migration [--application[="..."]] [--env="..."] [--editor-cmd="..."] name



| 引数     | デフォルト | 説明
| -------- | ------ | ----------------------
| `name`   | `-`    | マイグレーションの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | -----------------------------------------
| `--application`            | `1`       | アプリケーションの名前
| `--env`                    | `dev`     | 環境
| `--editor-cmd`             | `-`       | このコマンドでスクリプトを開きます


`doctrine:generate-migration` タスクはマイグレーションテンプレートを生成します。

    ./symfony doctrine:generate-migration AddUserEmailColumn

新しいマイグレーションクラスをエディタの作成メニューで開くには、`--editor-cmd` オプションを指定します。

    ./symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

### ~`doctrine::generate-migrations-db`~

`doctrine::generate-migrations-db` タスクは既存のデータベースコネクションからマイグレーションクラスを生成します。

    $ php symfony doctrine:generate-migrations-db [--application[="..."]] [--env="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | ------------------------
| `--application`            | `1`       | アプリケーションの名前
| `--env`                    | `dev`     | 環境


`doctrine:generate-migrations-db` タスクは既存のデータベースコネクションからマイグレーションクラスを生成します。

    ./symfony doctrine:generate-migrations-db

### ~`doctrine::generate-migrations-diff`~

`doctrine::generate-migrations-diff` タスクは新旧のスキーマの差分からマイグレーションクラスを生成します。

    $ php symfony doctrine:generate-migrations-diff [--application[="..."]] [--env="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | -----------
| `--application` | `1`   | アプリケーションの名前
| `--env`         | `dev` | 環境


`doctrine:generate-migrations-diff` タスクは新旧のスキーマの差分からマイグレーションクラスを生成します。

    ./symfony doctrine:generate-migrations-diff

### ~`doctrine::generate-migrations-models`~

`doctrine::generate-migrations-models` タスクは既存のモデルセットからマイグレーションクラスを生成します。

    $ php symfony doctrine:generate-migrations-models [--application[="..."]] [--env="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | ----------------------
| `--application`            | `1`       | アプリケーションの名前
| `--env`                    | `dev`     | 環境


`doctrine:generate-migrations-models` タスクは既存のモデルセットからマイグレーションクラスを生成します。

    ./symfony doctrine:generate-migrations-models

### ~`doctrine::generate-module`~

`doctrine::generate-module` タスクは Doctrine に対応したモジュールを生成します。

    $ php symfony doctrine:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-doctrine-route] [--env="..."] [--actions-base-class="..."] application module model



| 引数                  | デフォルト | 説明
| --------------------- | ------- | ------------------------
| `application`         | `-`     | アプリケーションの名前
| `module`              | `-`     | モジュールの名前
| `model`               | `-`     | モデルクラスの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | ----------------------------------
| `--theme`                  | `default` | テーマの名前
| `--generate-in-cache`      | `-`       | キャッシュでモジュールを生成します
| `--non-verbose-templates`  | `-`       | 冗長ではないテンプレートを生成します
| `--with-show`              | `-`       | show メソッドを生成します
| `--singular`               | `-`       | 単数形の名前
| `--plural`                 | `-`       | 複数形の名前
| `--route-prefix`           | `-`       | ルートのプレフィックス
| `--with-doctrine-route`    | `-`       | Doctrine のルートを使うかどうか
| `--env`                    | `dev`     | 環境
| `--actions-base-class`   | `sfActions` | アクションの基底クラス


`doctrine:generate-module` タスクは Doctrine に対応したモジュールを生成します。

    ./symfony doctrine:generate-module frontend article Article

上記の例では、`%model%` モデルクラスに対応した `%module%` モジュールが `%application%` アプリケーションに生成されます。

`--generate-in-cache` オプションをつけると、実行時に生成されるモジュールからアクションとテンプレートを継承する空のモジュールが `%sf_app_cache_dir%/modules/auto%module%/` ディレクトリに作られます。

    ./symfony doctrine:generate-module --generate-in-cache frontend article Article

`--theme` オプションを指定すれば、カスタマイズずみのテーマが使われます。

    ./symfony doctrine:generate-module --theme="custom" frontend article Article

この方法では、自分のスタイルに合わせてモジュールジェネレータを作ることができます。

生成モジュールに使われるアクションの基底クラスに変更することもできます (デフォルトは `sfActions`)。

    ./symfony doctrine:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`doctrine::generate-module-for-route`~

`doctrine::generate-module-for-route` タスクは Doctrine のルート定義に対応したモジュールを生成します。

    $ php symfony doctrine:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route



| 引数          | デフォルト | 説明
| ------------- | ------ | ------------------------
| `application` | `-`    | アプリケーションの名前
| `route`       | `-`    | ルートの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span>  | <span style="white-space: nowrap">デフォルト</span>  | 説明
| ----------------------------- | ---------- | --------------------------------------
| `--theme`                     | `default`  | テーマの名前
| `--non-verbose-templates`     | `-`        | 冗長ではないテンプレートを生成します
| `--singular`                  | `-`        | 単数形の名前
| `--plural`                    | `-`        | 複数形の名前
| `--env`                       | `dev`      | 環境
| `--actions-base-class`        | `sfActions`| アクションの基底クラス


`doctrine:generate-module-for-route` タスクはルート定義に対応した Doctrine モジュールを生成します。

    ./symfony doctrine:generate-module-for-route frontend article

上記の例では、`routing.yml` ファイルの `%article%` ルート定義に対応したモジュールが `%frontend%` アプリケーションに生成されます。

### ~`doctrine::insert-sql`~

`doctrine::insert-sql` タスクは現在のモデルに対応したテーブルを作ります。

    $ php symfony doctrine:insert-sql [--application[="..."]] [--env="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | ----------------------
| `--application`              | `1`     | アプリケーションの名前
| `--env`                      | `dev`   | 環境


`doctrine:insert-sql` タスクはデータベースのテーブルを作ります。

    ./symfony doctrine:insert-sql

このタスクはデータベースに接続し、`lib/model/doctrine/` ディレクトリにある `*.class.php` ファイルに対応したテーブルを作ります。

### ~`doctrine::migrate`~

`doctrine::migrate` タスクはデータベースを現在の/指定されたバージョンにマイグレートします。

    $ php symfony doctrine:migrate [--application[="..."]] [--env="..."] [--up] [--down] [--dry-run] [version]



| 引数      | デフォルト | 説明
| --------- | ------- | ----------------------------
| `version` | `-`     | マイグレートするバージョン


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| --------------- |---- | --------------------------------
| `--application` | `1` | アプリケーションの名前
| `--env` | `dev` | 環境
| `--up` | `-` | あるバージョンにマイグレートアップします
| `--down` | `-` | あるバージョンにマイグレートダウンします
| `--dry-run` | `-` | マイグレーションをつづけません


`doctrine:migrate` タスクはデータベースをマイグレートします。

    ./symfony doctrine:migrate

特定のバージョンにマイグレートするには、引数にバージョン番号を渡します。

    ./symfony doctrine:migrate 10

あるバージョンにマイグレートアップもしくはマイグレートダウンするには、`--up` もしくは `--down` オプションをつけます。

    ./symfony doctrine:migrate --down

データベースが DDL 文のロールバックをサポートしている場合、`--dry-run` オプションをつければ、マイグレーションはドライモードで実行されます。

    ./symfony doctrine:migrate --dry-run

`generate`
----------

### ~`generate::app`~

`generate::app` タスクは新しいアプリケーションを生成します。

    $ php symfony generate:app [--escaping-strategy="..."] [--csrf-secret="..."] app



|引数          | デフォルト | 説明
| ------------ | ------- | ----------------------
| `app` | `-`  | アプリケーションの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | ------------------------------------
| `--escaping-strategy`        | `1`     | エスケーピングストラテジを出力します
| `--csrf-secret`              | `1`     | CSRF 対策に使う秘密の文字列


`generate:app` タスクは現在のプロジェクトにおいて新しいアプリケーションの基本ディレクトリ構造を作ります。

    ./symfony generate:app frontend

2つのフロントコントローラも `web/` ディレクトリに作られます。

    web/%application%.php     運用環境
    web/%application%_dev.php 開発環境

最初のアプリケーションに関して、運用環境のスクリプトの名前は `index.php` です。

同じ名前のアプリケーションがすでに存在している場合、`sfCommandException` のインスタンスが投げられます。

デフォルトでは、XSS を防ぐために出力エスケーピングが有効で、CSRF も防ぐためにランダムな秘密の文字列が生成されます。

`--escaping-strategy` オプションを指定すれば、出力エスケーピングを無効にできます。

    ./symfony generate:app frontend --escaping-strategy=false

(CSRF を防ぐために) 秘密の文字列を `--csrf-secret` オプションで指定すれば、フォームのセッショントークンを有効にできます。

    ./symfony generate:app frontend --csrf-secret=UniqueSecret

`%sf_data_dir%/skeleton/app/` ディレクトリを用意すれば、タスクが使うデフォルトのスケルトンをカスタマイズできます。

### ~`generate::module`~

`generate::module` タスクは新しいモジュールを生成します。

    $ php symfony generate:module  application module



| 引数          | デフォルト | 説明
| ------------- | ------- | -----------------------
| `application` | `-`     | アプリケーションの名前
| `module`      | `-`     | モジュールの名前




`generate:module` タスクは既存のアプリケーションにおいて新しいモジュールの基本ディレクトリ構造を作ります。

    ./symfony generate:module frontend article

`config/` ディレクトリに配置されている `properties.ini` ファイルのなかで著者が指定されている場合、タスクは `actions.class.php` ファイルのなかの名前も変更することができます。

    [symfony]
      name=blog
      author=Fabien Potencier <fabien.potencier@sensio.com>

`%sf_data_dir%/skeleton/module/` ディレクトリを用意すれば、タスクが使うデフォルトのスケルトンをカスタマイズできます。

`ActionsTest.class.php` という名前の機能テストのスタブも `%sf_test_dir%/functional/%application%/%module/` ディレクトリに作られます。デフォルトでは、このテストのスタブは通りません。

すでにアプリケーションのなかに同じ名前のモジュールが存在している場合、`sfCommandException` のインスタンスが投げられます。

### ~`generate::project`~

`generate::project` タスクは新しいプロジェクトを生成します。

    $ php symfony generate:project [--orm="..."] [--installer="..."] name [author]



| 引数     | デフォルト       | 説明
| -------- | ---------------- | ------------------
| `name`   | `-`              | プロジェクトの名前
| `author` | `Your name here` | プロジェクトの著者 


| <span style="white-space: nowrap">オプション (ショートカット)</span>   | <span style="white-space: nowrap">デフォルト</span> | 説明
| ------------- | ------------- | ---------------------------------
| `--orm`       | `Doctrine`    | デフォルトで使われる ORM
| `--installer` | `-`           | 実行するインストーラスクリプト


`generate:project` タスクは現在のディレクトリにおいて新しいプロジェクトの基本ディレクトリ構造を作ります。

    ./symfony generate:project blog

すでに同じ名前のプロジェクトが存在している場合、`sfCommandException` のインスタンスが投げられます。

タスクが使うデフォルトの ORM は Doctrine です。Propel を使いたければ、`--orm` オプションで指定します。

    ./symfony generate:project blog --orm=Propel

ORM を使いたくなければ、`--orm` オプションに `none` を渡します。

    ./symfony generate:project blog --orm=none

`--installer` オプションを指定してプロジェクトを細かくカスタマイズすることもできます。

    ./symfony generate:project blog --installer=./installer.php

オプションとして、新しいクラスの著者の名前を第2引数に渡して指名することができます。

    ./symfony generate:project blog "Jack Doe"

### ~`generate::task`~

`generate::task` タスクは新しいタスクのスケルトンクラスを作ります。

    $ php symfony generate:task [--dir="..."] [--use-database="..."] [--brief-description="..."] task_name



| 引数        | デフォルト | 説明
| ----------- | ------- | ----------------------------------
| `task_name` | `-`     | タスクの名前 (名前空間がつきます)


| <span style="white-space: nowrap">オプション (ショートカット)</span>     | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------- | ---------- | ------------------------------------------------------------
| `--dir`              | `lib/task` | タスクが配置されるディレクトリ
| `--use-database`     | `doctrine` | タスクがデータベースにアクセスするモデルの初期化を必要とするか
| `--brief-description`| `-`        | タスクの短い説明 (タスクのリストに表示されます)



`generate:task` タスクは引数に渡された名前をもとに `sfBaseTask` 基底クラスを継承する新しいクラスを作ります。

    ./symfony generate:task namespace:name

`namespaceNameTask.class.php` タスクのスケルトンは `lib/task/` ディレクトリに作られます。名前空間がオプションであることにご注意ください。

タスクファイルが作られるディレクトリを変更したいのであれば、`--dir` オプションで指定します (プロジェクトのルートフォルダに対して相対的な位置)。指定したディレクトリがまだ存在していなければ作られます。

    ./symfony generate:task namespace:name --dir=plugins/myPlugin/lib/task

デフォルトの `doctrine` 以外のコネクションを利用したいのであれば、`--use-database` オプションにコネクションの名前を指定します。

    ./symfony generate:task namespace:name --use-database=main

`--use-database` オプションはデータベースの初期化を無効にする場合にも使うことができます。

    ./symfony generate:task namespace:name --use-database=false

タスクの説明を指定することもできます。

    ./symfony generate:task namespace:name --brief-description="Does interesting things"

`i18n`
------

### ~`i18n::extract`~

`i18n::extract` タスクは PHP ファイルから国際対応した文字列を抽出します。

    $ php symfony i18n:extract [--display-new] [--display-old] [--auto-save] [--auto-delete] application culture



| 引数          | デフォルト    | 説明
| ------------- | ---------- | ----------------------
| `application` | `-`        | アプリケーションの名前
| `culture`     | `-`        | ターゲットのカルチャ


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | ----------- | ------------------------------
| `--display-new` | `-`      | 新たに見つかるすべての文字列を出力します
| `--display-old` | `-`      | すべての古い文字列を出力します
| `--auto-save`   | `-`      | 新しい文字列を保存します
| `--auto-delete` | `-`      | 古い文字列を削除します


`i18n:extract` タスクはアプリケーションとターゲットのカルチャのプロジェクトファイルから国際対応した文字列を抽出します。

    ./symfony i18n:extract frontend fr

デフォルトでは、このタスクは現在のプロジェクトで見つかる新旧の文字列の数だけを表示します。

新しい文字列を表示したいのであれば、`--display-new` オプションをつけます。

    ./symfony i18n:extract --display-new frontend fr

これらの文字列を国際対応したメッセージのカタログに保存するには、`--auto-save` オプションをつけます。

    ./symfony i18n:extract --auto-save frontend fr

国際対応したメッセージのカタログで見つかるが、アプリケーションで見つからない文字列を表示したければ、`--display-old` オプションをつけます。

    ./symfony i18n:extract --display-old frontend fr

古い文字列を自動的に削除するには `--auto-delete` オプションをつけますが、特にプラグインの翻訳がある場合、表示されるのは、現在の文字列ではなく古い文字列であることにご注意ください。

    ./symfony i18n:extract --auto-delete frontend fr

### ~`i18n::find`~

`i18n::find` タスクはアプリケーションのなかの「国際対応していない」文字列を見つける。

    $ php symfony i18n:find [--env="..."] application



| 引数          | デフォルト| 説明
| ------------- | ---------- | -----------------------
| `application` | `-`        | アプリケーションの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | ---------- | -----------
| `--env`                    | `dev`      | 環境


`i18n:find` タスクはテンプレートに埋め込まれた国際対応していない文字列を見つけます。

    ./symfony i18n:find frontend

このタスクは純粋な HTML と PHP コードのなかで国際対応していない文字列を見つけることができます。

    <p>Non i18n text</p>
    <p><?php echo 'Test' ?></p>

このタスクは PHP コードに埋め込まれたすべての文字列を返しますが、誤検出する可能性があります (特に文字列構文をヘルパーの引数に使う場合)。

`log`
-----

### ~`log::clear`~

`log::clear` タスクはログファイルをクリアします。

    $ php symfony log:clear  







`log:clear` タスクはすべてのログをクリアします。

    ./symfony log:clear

### ~`log::rotate`~

`log::rotate` タスクはアプリケーションにおいてログファイルのローテーションを実行します。

    $ php symfony log:rotate [--history="..."] [--period="..."] application env



| 引数          | デフォルト | 説明
| ------------- | ---------- | ----------------------
| `application` | `-`        | アプリケーションの名前
| `env`         | `-`        | 環境の名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | -------------- | ----------------------
| `--history`   | `10`         | 保存する古いログファイルの最大個数
| `--period`    | `7`          | 日にち単位の保存期間


`log:rotate` タスクは任意の環境のアプリケーションにおいてログファイルのローテーションを実行します。

    ./symfony log:rotate frontend dev

`--period` もしくは `--history` オプションを指定できます。

    ./symfony log:rotate frontend dev --history=10 --period=7

`plugin`
--------

### ~`plugin::add-channel`~

`plugin::add-channel` タスクは新しい PEAR チャンネルを追加します。

    $ php symfony plugin:add-channel  name



| 引数   | デフォルト | 説明
| ------ | ------- | ---------------
| `name` | `-`     | チャンネルの名前




`plugin:add-channel` タスクは新しい PEAR チャンネルを追加します。

    ./symfony plugin:add-channel symfony.plugins.pear.example.com

### ~`plugin::install`~

`plugin::install` タスクはプラグインをインストールします。

    $ php symfony plugin:install [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] [-d|--install_deps] [--force-license] name



| 引数   | デフォルト | 説明
| ------ | ------- | -----------------
| `name` | `-`     | プラグインの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | ------------------------------------
| `--stability`<br />`(-s)`    | `-`     | 安定性 (stable、beta、alpha)
| `--release`<br />`(-r)`      | `-`     | 優先バージョン
| `--channel`<br />`(-c)`      | `-`     | PEAR チャンネルの名前
| `--install_deps`<br />`(-d)` | `-`     | 必須の依存パッケージのインストールを強制するか
| `--force-license`            | `-`     | MIT のようなライセンスではない場合にインストールを強制するか


`plugin:install` タスクはプラグインをインストールします。

    ./symfony plugin:install sfGuardPlugin

デフォルトでは、最新の `stable` リリースがインストールされます。

まだ安定版ではないプラグインをインストールしたければ、`--stability` オプションを指定します。

    ./symfony plugin:install --stability=beta sfGuardPlugin
    ./symfony plugin:install -s beta sfGuardPlugin

特定のバージョンのインストールを強制することもできます。

    ./symfony plugin:install --release=1.0.0 sfGuardPlugin
    ./symfony plugin:install -r 1.0.0 sfGuardPlugin

依存の必須パッケージをすべて強制的にインストールするには、`--install_deps` オプションを指定します。

    ./symfony plugin:install --install-deps sfGuardPlugin
    ./symfony plugin:install -d sfGuardPlugin

デフォルトで使われる PEAR チャンネルは `symfony-plugins` (plugins.symfony-project.org) です。

別のチャンネルを `--channel` オプションで指定できます。

    ./symfony plugin:install --channel=mypearchannel sfGuardPlugin
    ./symfony plugin:install -c mypearchannel sfGuardPlugin

PEAR パッケージがホストされている Web サイトの URL を直接指定してインストールすることもできます。

    ./symfony plugin:install http://somewhere.example.com/sfGuardPlugin-1.0.0.tgz

もしくはローカルな PEAR パッケージをインストールすることができます。

    ./symfony plugin:install /home/fabien/plugins/sfGuardPlugin-1.0.0.tgz

プラグインにサイトのコンテンツ (画像、スタイルシートもしくは JavaScript) が収められている場合、このタスクはこれらのアセットのために `web/` ディレクトリの下で `%name%` シンボリックリンクを作ります。Windows では、このタスクはこれらすべてのファイルを `web/%name%/` ディレクトリにコピーします。

### ~`plugin::list`~

`plugin::list` タスクはインストールずみのプラグインの一覧を表示します。

    $ php symfony plugin:list  







`plugin:list` タスクはインストールずみのプラグインの一覧を表示します。

    ./symfony plugin:list

このタスクはそれぞれのプラグインのチャンネルとバージョンも表示します。

### ~`plugin::publish-assets`~

`plugin::publish-assets` タスクはすべてのプラグインからサイトのアセットを公開します。

    $ php symfony plugin:publish-assets [--core-only] [plugins1] ... [pluginsN]



| 引数      | デフォルト | 説明
| --------- | ------ | -------------------------------
| `plugins` | `-`    | プラグインのアセットを公開します


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | -------------------------------------------------------------
| `--core-only`              | `-`       | セットされていれば、コアプラグインのみがアセットを公開します


`plugin:publish-assets` タスクはすべてのプラグインからサイトのアセットを公開します。

    ./symfony plugin:publish-assets

実際、このタスクは `plugin.post_install` イベントをそれぞれのプラグインに送信します。

1つもしくは複数のプラグインにアセットをインストールするには、これらのプラグインの名前を引数に渡します。

    ./symfony plugin:publish-assets sfDoctrinePlugin

### ~`plugin::uninstall`~

`plugin::uninstall` タスクはプラグインをアンインストールします。

    $ php symfony plugin:uninstall [-c|--channel="..."] [-d|--install_deps] name



| 引数   | デフォルト | 説明
| ------ | ------- | -----------------
| `name` | `-`     | プラグインの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | ---------------------------------------
| `--channel`<br />`(-c)`      | `-`     | PEAR チャンネル名
| `--install_deps`<br />`(-d)` | `-`     | 依存パッケージのインストールを強制するか


`plugin:uninstall` タスクはプラグインをアンインストールします。

    ./symfony plugin:uninstall sfGuardPlugin

デフォルトのチャンネルは `symfony` です。

異なるチャンネルで配布されているプラグインをアンインストールすることもできます。

    ./symfony plugin:uninstall --channel=mypearchannel sfGuardPlugin

    ./symfony plugin:uninstall -c mypearchannel sfGuardPlugin

もしくは `channel/package` の表記を選ぶことができます。

    ./symfony plugin:uninstall mypearchannel/sfGuardPlugin

`plugin:list` タスクを実行すれば、プラグインの PEAR チャンネル名を得られます。

プラグインにサイトのコンテンツ (画像、スタイルシートもしくは JavaScript) が収められている場合、タスクは `web/%name%` シンボリックリンク (Unix 系) もしくはディレクトリ (Windows) も削除します。

### ~`plugin::upgrade`~

`plugin::upgrade` タスクはプラグインをアップグレードします。

    $ php symfony plugin:upgrade [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] name



| 引数   | デフォルト | 説明
| ------ | ------- | -----------
| `name` | `-`     | プラグイン名


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ----------------- | ------- | ------------------
| `--stability`<br />`(-s)` | `-` | 優先される安定性 (stable、beta、alpha)
| `--release`<br />`(-r)`   | `-` | 優先されるバージョン
| `--channel`<br />`(-c)`   | `-` | PEAR チャンネル名


`plugin:upgrade` タスクはプラグインをアップグレードしようとします。

    ./symfony plugin:upgrade sfGuardPlugin

デフォルトのチャンネルは `symfony` です。

サイトのコンテンツ (画像、スタイルシートもしくは JavaScript) がプラグインに収められている場合、Windows において、このタスクは `web/%name%/` ディレクトリの内容を更新します。

プラグインの名前とオプションのフォーマットのくわしい説明は `plugin:install` タスクをご参照ください。

`project`
---------

### ~`project::clear-controllers`~

`project::clear-controllers` タスクは運用環境以外のすべての環境のコントローラをクリアします。

    $ php symfony project:clear-controllers  







`project:clear-controllers` タスクは運用環境以外のすべての環境のコントローラをクリアします。

    ./symfony project:clear-controllers

運用サーバーにおいて、運用環境以外のすべてのフロントコントローラのスクリプトを削除したい場合に、このタスクを使うことができます。

`frontend` と `backend` という名前の2つのアプリケーションがある場合、4つのデフォルトコントローラが `web/` ディレクトリに配置されます。

    index.php
    frontend_dev.php
    backend.php
    backend_dev.php

`project:clear-controllers` タスクを実行すると、2つのコントローラスクリプトが　`web/` ディレクトリに残されます。

    index.php
    backend.php

これらの2つのコントローラではデバッグモードとデバッグツールバーが無効なので安全です。

### ~`project::deploy`~

`project::deploy` タスクはプロジェクトを別のサーバーにデプロイします。

    $ php symfony project:deploy [--go] [--rsync-dir="..."] [--rsync-options[="..."]] server



| 引数     | デフォルト | 説明
| -------- | --- | ---------------
| `server` | `-` | サーバーの名前



| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ------- | -----------
| `--go`            | `-`      | デプロイを実行します
| `--rsync-dir`     | `config` | `rsync*.txt` ファイルを探すディレクトリ
| `--rsync-options` | `-azC --force --delete --progress` | rsync の実行ファイルに渡すオプションの値


`project:deploy` タスクはプロジェクトをサーバーにデプロイします。

    ./symfony project:deploy production

このタスクのコンフィギュレーションは `config/` ディレクトリに配置されている `properties.ini` ファイルのなかで変更します。

    [production]
      host=www.example.com
      port=22
      user=fabien
      dir=/var/www/sfblog/
      type=rsync

このタスクはデプロイを自動化するために SSH を通じて rsync を使います。SSH アクセスをキーで設定するもしくはパスワードを `config/` ディレクトリに配置されている `properties.ini` ファイルのなかで指定しなければなりません。

デフォルトでは、このタスクはドライモードです。本当にデプロイを実行するには、`--go` オプションを指定しなければなりません。

    ./symfony project:deploy --go production

`config/rsync_exclude.txt` ファイルで指定されているファイルとディレクトリはデプロイされません。

    .svn
    /web/uploads/*
    /cache/*
    /log/*

自前の `rsync.txt` と `rsync_include.txt` ファイルを用意することもできます。

サーバーに合わせて `rsync*.txt` ファイルをカスタマイズする必要がある場合、`--rsync-dir` オプションを指定します。

    ./symfony project:deploy --go --rsync-dir=config/production production

最後に、rsync コマンドのオプションはタスクの `--rsync-options` オプションを通じて指定できます (デフォルトは `-azC --force --delete --progress`)。

    ./symfony project:deploy --go --rsync-options=-avz

### ~`project::disable`~

`project::disable` タスクは任意の環境のアプリケーションを無効にします。

    $ php symfony project:disable  env [app1] ... [appN]



| 引数  | デフォルト | 説明
| ----- | --- | ----------------------
| `env` | `-` | 環境の名前
| `app` | `-` | アプリケーションの名前




`project:disable` タスクは環境を無効にします。

    ./symfony project:disable prod

特定の環境で無効にするアプリケーションを個別に指定することもできます。

    ./symfony project:disable prod frontend backend

### ~`project::enable`~

`project::enable` タスクは任意の環境のアプリケーションを有効にします。

    $ php symfony project:enable  env [app1] ... [appN]



| 引数  | デフォルト | 説明
| ------ | --- | ----------------------
| `env` | `-` | 環境の名前
| `app` | `-` | アプリケーションの名前




`project:enable` タスクは特定の環境を有効にします。

    ./symfony project:enable frontend prod

特定の環境で有効にするアプリケーションを個別に指定することもできます。

    ./symfony project:enable prod frontend backend

### ~`project::optimize`~

`project::optimize` タスクはパフォーマンス改善のためにプロジェクトを最適化します。

    $ php symfony project:optimize  application [env]



| 引数 | デフォルト | 説明
| ---- | ------- | ---------------------------
| `application`  | `-` | アプリケーションの名前
| `env` | `prod` | 環境の名前




`project:optimize` はパフォーマンス改善のためにプロジェクトを最適化します。

    ./symfony project:optimize frontend prod

このタスクを実行する場所は運用サーバーに限定すべきです。プロジェクトのコードを修正するたびにタスクを再実行することをお忘れなく。

### ~`project::permissions`~

`project::permissions` タスクは symfony のディレクトリのパーミッションを修正します。

    $ php symfony project:permissions  







`project:permissions` タスクはディレクトリのパーミッションを修正します。

    ./symfony project:permissions

### ~`project::send-emails`~

`project::send-emails` タスクはキューに保存されているメールを送信します。

    $ php symfony project:send-emails [--application[="..."]] [--env="..."] [--message-limit[="..."]] [--time-limit[="..."]] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | --------------------
| `--application`   | `1`    | アプリケーションの名前
| `--env`           | `dev`  | 環境
| `--message-limit` | `0`    | 送信するメッセージの最大数
| `--time-limit`    | `0`    | メッセージ送信の時間制限 (秒単位)


`project:send-emails` はキューに保存されているメールを送信します。

    php symfony project:send-emails

送信するメッセージの数を制限できます。

    php symfony project:send-emails --message-limit=10

もしくは秒単位で時間制限できます。

    php symfony project:send-emails --time-limit=10

### ~`project::validate`~

`project::validate` タスクはプロジェクトのなかで使われている廃止予定の機能を見つけます。

    $ php symfony project:validate  







`project:validate` タスクはプロジェクトのなかで使われている廃止予定の機能を検出します。

    ./symfony project:validate

このタスクは symfony 1.4 に切り替える前に修正する必要のあるすべてのファイルの一覧を表示します。

`propel`
--------

### ~`propel::build`~

`propel::build` タスクはスキーマに対応したコードを生成します。

    $ php symfony propel:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-load[="..."]] [--and-append[="..."]] 




| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ------------------- | ----- | --------------------------------------------
| `--application`     | `1`   | アプリケーションの名前
| `--env`             | `dev` | 環境
| `--no-confirmation` | `-`   | データベースの削除を強制するか
| `--all`             | `-`   | すべてをビルドしてデータベースをリセットします
| `--all-classes`     | `-`   | すべてのクラスをビルドします
| `--model`           | `-`   | モデルクラスをビルドします
| `--forms`           | `-`   | フォームクラスをビルドします
| `--filters`         | `-`   | フィルタクラスをビルドします
| `--sql`             | `-`   | SQL をビルドします
| `--db`              | `-`   | SQL を削除、作成、および挿入します
| `--and-load`        | `-`   | フィクスチャデータをロードします (複数の値が可能です)
| `--and-append`      | `-`   | フィクスチャデータを追加します (複数の値が可能です)



`propel:build` タスクはスキーマに対応したコードを生成します。

    ./symfony propel:build

ビルドしたいものを指定しなければなりません。たとえば、モデルとフォームをビルドしたいのであれば、`--model` と `--forms` オプションをつけます。

    ./symfony propel:build --model --forms

すべてのクラスと SQL ファイルを生成してデータベースを再構築したいのであれば、ショートカットの `--all` オプションをつけます。

    ./symfony propel:build --all

このタスクは次のタスクの組み合わせと同じです。

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

ショートカットの `--all-classes` オプションをつけると、クラスファイルだけが生成されます。このオプションを単独でつければ、データベースは修正されません。

    ./symfony propel:build --all-classes

`--and-load` オプションをつけると、プロジェクトとプラグインの `data/fixtures/` ディレクトリからデータがロードされます。

    ./symfony propel:build --db --and-load

指定したフィクスチャをロードの対象に加えるには、`--and-load` オプションにフィクスチャのパスを渡します。

    ./symfony propel:build --all --and-load="data/fixtures/dev/"

データベースのレコードを削除せずにフィクスチャのデータを追加するには、`--and-append` オプションをつけます。

    ./symfony propel:build --all --and-append

### ~`propel::build-all`~

`propel::build-all` タスクは Propel に対応したモデルとフォームクラス、SQL を生成しデータベースを初期化します。

    $ php symfony propel:build-all [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span>   | <span style="white-space: nowrap">デフォルト</span>  | 説明
| ----------------------------- | ---------- | ------------------------------
| `--application`               | `1`        | アプリケーションの名前
| `--env`                       | `dev`      | 環境
| `--connection`                | `propel`   | コネクションの名前
| `--no-confirmation`           | `-`        | 確認メッセージを省略します
| `--skip-forms`<br />`(-F)`    | `-`        | フォーム生成を省略します
| `--classes-only`<br />`(-C)`  | `-`        | データベースを初期化しません
| `--phing-arg`                 | `-`        | Phing の任意の引数 (複数の値が可能です)

`propel:build-all` タスクはほかの5つのタスクのショートカットです。

    ./symfony propel:build-all

このタスクは次のタスクの組み合わせと同じです。

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

くわしい説明はこれらのタスクのヘルプページをご参照ください。

確認メッセージを省略するには、`--no-confirmation` オプションをつけます。

    ./symfony propel:buil-all --no-confirmation

すべてのクラスをビルドする際にデータベースの初期化を省略するには、`--classes-only` オプションをつけます。

    ./symfony propel:build-all --classes-only

### ~`propel::build-all-load`~

`propel::build-all-load` タスクは Propel に対応したモデルとフォームクラス、SQL を生成し、データベースを初期化し、データをロードします。

    $ php symfony propel:build-all-load [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] [--append] [--dir="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span>  | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ---------- | ---------------------------------------------------
| `--application`              | `1`        | アプリケーションの名前
| `--env`                      | `dev`      | 環境
| `--connection`               | `propel`   | コネクションの名前
| `--no-confirmation`          | `-`        | 確認メッセージを省略します
| `--skip-forms`<br />`(-F)`   | `-`        | フォーム生成を省略します
| `--classes-only`<br />`(-C)` | `-`        | データベースを初期化しません
| `--phing-arg`                | `-`        | Phing の任意の引数 (複数の値が可能です)
| `--append`                   | `-`        | データベースの現在の値を削除しません
| `--dir`                      | `-`        | フィクスチャを探すディレクトリ (複数の値が可能です)


`propel:build-all-load` タスクはほかの2つのタスクのショートカットです。

    ./symfony propel:build-all-load

このタスクは次の2つのタスクの組み合わせと同じです。

    ./symfony propel:build-all
    ./symfony propel:data-load

くわしい説明はこれらのタスクのヘルプページをご参照ください。

確認メッセージを省略するには、`--no-confirmation` オプションをつけます。

    ./symfony propel:buil-all-load --no-confirmation

### ~`propel::build-filters`~

`propel::build-filters` タスクは現在のモデルに対応したフィルタフォームクラスを作ります。

    $ php symfony propel:build-filters [--connection="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--application[="..."]] [--generator-class="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ------------------- | ---------- | ------------------------------------
| `--connection`      | `propel`   | コネクションの名前
| `--model-dir-name`  | `model`    | モデルディレクトリ名
| `--filter-dir-name` | `filter`   | フィルタフォームのディレクトリ名
| `--application`     | `1`        | アプリケーション名
| `--generator-class` | `sfPropelFormFilterGenerator` | ジェネレータクラス


`propel:build-filters` タスクはスキーマからフィルタフォームクラスを作ります。

    ./symfony propel:build-filters

このタスクはプロジェクトとインストールずみのすべてのプラグインから `config/` ディレクトリの `*schema.xml` かつ/もしくは `*schema.yml` ファイルの情報を読み込みます。

このタスクは `config/` ディレクトリの `databases.yml` ファイルのなかで定義されている `propel` コネクションを利用します。`--connection` オプションを指定すれば、別のコネクションを利用することができます。

    ./symfony propel:build-filters --connection="name"

モデルのフィルタフォームクラスは `lib/filter/` ディレクトリに作られます。

このタスクは `lib/filter/` ディレクトリのなかのカスタムクラスを上書きすることはけっしてありません。このタスクは `lib/filter/base/` ディレクトリに生成された基底クラスを置き換えます。

### ~`propel::build-forms`~

`propel::build-forms` タスクは現在のモデルに対応したフォームクラスを作ります。

    $ php symfony propel:build-forms [--connection="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--application[="..."]] [--generator-class="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span>  | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ---------- | --------------------------
| `--connection`      | `propel`   | コネクションの名前
| `--model-dir-name`  | `model`    | モデルディレクトリの名前
| `--form-dir-name`   | `form`     | フォームディレクトリの名前
| `--application`     | `1`        | アプリケーションの名前
| `--generator-class` | `sfPropelFormGenerator` | ジェネレータクラス


`propel:build-forms` タスクはスキーマからフォームクラスを作ります。

    ./symfony propel:build-forms

このタスクはプロジェクトとインストールずみのすべてのプラグインから `config/` ディレクトリの `*schema.xml` かつ/もしくは `*schema.yml` ファイルの情報を読み込みます。

このタスクは `config/` ディレクトリの `databases.yml` ファイルのなかで定義されている `propel` コネクションを利用します。`--connection` オプションを指定すれば、別のコネクションを利用できます。

    ./symfony propel:build-forms --connection="name"

モデルのフォームクラスは `lib/form/` ディレクトリに作られます。

このタスクは `lib/form/` ディレクトリのカスタムクラスを上書きすることはありません。このタスクは `lib/form/base/` ディレクトリに生成された基底クラスのみを置き換えます。

### ~`propel::build-model`~

`propel::build-model` タスクは現在のモデルに対応したクラスを作ります。

    $ php symfony propel:build-model [--phing-arg="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | ---------- | ----------------------------------------
| `--phing-arg`              | `-`        | Phing の任意の引数 (複数の値が可能です)


`propel:build-model` タスクはスキーマからモデルクラスを作ります。

    ./symfony propel:build-model

このタスクはプロジェクトとインストールずみのすべてのプラグインから `config/` ディレクトリの `*schema.xml` かつ/もしくは `*schema.yml` ファイルの情報を読み込みます。

YAML と XML ファイルを組み合わせることができます。このタスクは Propel タスクを呼び出す前に YAML を XML に変換します。

モデルクラスのファイルは `lib/model/` ディレクトリに作られます。

このタスクは `lib/model/` ディレクトリのなかのカスタムクラスを置き換えることはけっしてありません。このタスクは `lib/model/om/` と `lib/model/map/` ディレクトリの下にあるファイルだけを置き換えます。

### ~`propel::build-schema`~

`propel::build-schema` タスクは既存のデータベースからスキーマを作ります。

    $ php symfony propel:build-schema [--application[="..."]] [--env="..."] [--connection="..."] [--xml] [--phing-arg="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | ---------- | --------------------------------------
| `--application`            | `1`        | アプリケーションの名前
| `--env`                    | `cli`      | 環境
| `--connection`             | `-`        | コネクションの名前
| `--xml`                    | `-`        | YAML の代わりに XML スキーマを作ります
| `--phing-arg`              | `-`        | Phing の任意の引数 (複数の値が可能です)


`propel:build-schema` タスクはデータベースをイントロスペクトしてスキーマを作ります。

    ./symfony propel:build-schema

デフォルトでは、このタスクは YAML ファイルを作りますが、XML ファイルも作ることができます。

    ./symfony --xml propel:build-schema

XML フォーマットは YAML よりも多くの情報を格納できます。

### ~`propel::build-sql`~

`propel::build-sql` タスクは現在のモデルに対応した SQL ファイルを生成します。

    $ php symfony propel:build-sql [--phing-arg="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ---------- | ----------------------------------------
| `--phing-arg`                | `-`        | Phing の任意の引数 (複数の値が可能です)


`propel:build-sql` タスクはテーブル作成の SQL ファイルを生成します。

    ./symfony propel:build-sql

生成される SQL ファイルは `config/` ディレクトリの `propel.ini` ファイルのなかで指定されているデータベースに合わせて最適化されます。

    propel.database = mysql

### ~`propel::data-dump`~

`propel::data-dump` タスクはデータをフィクスチャディレクトリに吐き出します。

    $ php symfony propel:data-dump [--application[="..."]] [--env="..."] [--connection="..."] [--classes="..."] [target]



| 引数     | デフォルト | 説明
| -------- | ------ | ---------------------
| `target` | `-`    | ターゲットのファイル名


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| --------------------------- | ---------- | ------------------------------------------
| `--application`             | `1`        | アプリケーションの名前
| `--env`                     | `cli`      | 環境
| `--connection`              | `propel`   | コネクションの名前
| `--classes`                 | `-`        | 吐き出されるクラスの名前 (コロンで区切られます)


`propel:data-dump` タスクはデータベースデータを吐き出します。

    ./symfony propel:data-dump > data/fixtures/dump.yml

デフォルトでは、データは標準出力されますが、ファイルの名前を第2引数に渡すこともできます。

    ./symfony propel:data-dump dump.yml

上記の例では、データは `data/fixtures/%target%/` ディレクトリに吐き出されます (この例では `data/fixtures/dump.yml`)。

`propel:data-load` タスクを使えば、吐き出されたファイルを YAML フォーマットで再インポートできます。

デフォルトでは、`config/` ディレクトリの `databases.yml` ファイルのなかで定義されている `propel` コネクションが利用されます。`--connection` オプションを指定すれば、別のコネクションを利用できます。

    ./symfony propel:data-dump --connection="name"

クラスだけを吐き出させたいのであれば、`--classes` オプションを指定します。

    ./symfony propel:data-dump --classes="Article,Category"

アプリケーションから特定のデータベースコネクションを利用したければ、`--application` オプションを指定します。

    ./symfony propel:data-dump --application=frontend

### ~`propel::data-load`~

`propel::data-load` タスクは YAML フィクスチャのデータをロードします。

    $ php symfony propel:data-load [--application[="..."]] [--env="..."] [--append] [--connection="..."] [dir_or_file1] ... [dir_or_fileN]



| 引数 | デフォルト  | 説明
| ---- | ------------ | ----------------------------------------------
| `dir_or_file` | `-` | ロードの対象となるディレクトリもしくはファイル


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ----------- | -----------
| `--application`  | `1` | アプリケーションの名前
| `--env`  | `cli` | 環境
| `--append` | `-` | データベースの現在のデータを削除しません
| `--connection`   | `propel` | コネクションの名前


`propel:data-load` タスクはデーターベースにデータフィクスチャをロードさせます。

    ./symfony propel:data-load

`data/fixtures/` で配置されているすべてのファイルからデータがロードされます。

ロードの対象となるファイルもしくはディレクトリを指定したいのであれば、引数にこれらのパスを渡します。

    ./symfony propel:data-load data/fixtures/dev data/fixtures/users.yml

このタスクは `config/` ディレクトリの `databases.yml` ファイルのなかで定義されている `propel` コネクションを利用します。`--connection` オプションを指定すれば、別のコネクションを利用できます。

    ./symfony propel:data-load --connection="name"

データベースのなかの既存のデータを削除したくなければ、`--append` オプションをつけます。

    ./symfony propel:data-load --append

アプリケーションから特定のデータベースコンフィギュレーションを使いたければ、`--application` オプションを指定します。

    ./symfony propel:data-load --application=frontend

### ~`propel::generate-admin`~

`propel::generate-admin` タスクは Propel に対応したアドミンモジュールを生成します。

    $ php symfony propel:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model



| 引数 | デフォルト | 説明
| -------- | ------- | -------------------------------------
| `application` | `-` | アプリケーションの名前
| `route_or_model` | `-` | ルートの名前もしくはモデルクラス


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ----------------- | ------- | -------------------
| `--module` | `-` | モジュールの名前
| `--theme` | `admin` | テーマの名前
| `--singular` | `-` | 単数形の名前
| `--plural` | `-` | 複数形の名前
| `--env` | `dev` | 環境
| `--actions-base-class` | `sfActions` | アクションの基底クラス


`propel:generate-admin` タスクは Propel に対応したアドミンモジュールを生成します。

    ./symfony propel:generate-admin frontend Article

上記の例では、`%Article%` モデルに対応したモジュールが `%frontend%` アプリケーションに生成されます。

このタスクはあなたに代わってアプリケーションの `routing.yml` ファイルにルートを作ります。

ルートの名前を引数に渡せば、Propel に対応したアドミンモジュールを生成することもできます。

    ./symfony propel:generate-admin frontend article

上記の例では、`%frontend%` アプリケーションのなかで `routing.yml` ファイルの `%article%` に対応したモジュールが生成されます。

フィルタとバッチアクションがきちんと動くようにするには、ルートのコンフィギュレーションのなかで `with_wildcard_routes` オプションに `true` をセットする必要があります。

    article:
      class: sfPropelRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`propel::generate-module`~

`propel::generate-module` タスクは Propel に対応したモジュールを生成します。

    $ php symfony propel:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-propel-route] [--env="..."] [--actions-base-class="..."] application module model



| 引数     | デフォルト | 説明
| -------- | -------- | --------------------------
| `application` | `-` | アプリケーションの名前
| `module`      | `-` | モジュールの名前
| `model`       | `-` | モデルクラスの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| --------------------------- | ---------- | -----------
| `--theme` | `default` | テーマの名前
| `--generate-in-cache` | `-` | モジュールをキャッシュに生成します
| `--non-verbose-templates` | `-` | 冗長ではないテンプレートを生成します
| `--with-show` | `-` | show メソッドを生成します
| `--singular` | `-` | 単数形の名前
| `--plural` | `-` | 複数形の名前
| `--route-prefix` | `-` | ルートのプレフィックス
| `--with-propel-route` | `-` | Propel ルートを使うかどうか
| `--env` | `dev` | 環境
| `--actions-base-class` | `sfActions` | アクションの基底クラス


`propel:generate-module` タスクは Propel に対応したモジュールを生成します。

    ./symfony propel:generate-module frontend article Article

上記の例では、`%model%` モデルクラスに対応した `%module%` モジュールが `%application%` アプリケーションに生成されます。

`--generate-in-cache` オプションを指定すれば、実行時に生成されるモジュールからアクションとテンプレートを継承した空のモジュールが `%sf_app_cache_dir%/modules/auto%module%/` ディレクトリに作られます。

    ./symfony propel:generate-module --generate-in-cache frontend article Article

`--theme` オプションを指定すれば、カスタマイズずみのテーマが使われます。

    ./symfony propel:generate-module --theme="custom" frontend article Article

この方法では、自分のスタイルに合わせてモジュールジェネレータを作ることができます。

`--actions-base-class` オプションを指定すれば、生成モジュールに使われるデフォルトの基底クラス (sfActions) を変更することもできます。

    ./symfony propel:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`propel::generate-module-for-route`~

`propel::generate-module-for-route` タスクは Propel のルート定義に対応したモジュールを生成します。

    $ php symfony propel:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route



| 引数 | デフォルト | 説明
| -------- | ------- | ------------------------
| `application` | `-` | アプリケーションの名前
| `route` | `-` | ルートの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ----------------- | -------- | -----------------
| `--theme` | `default` | テーマの名前
| `--non-verbose-templates` | `-` | 冗長ではないテンプレートを生成します
| `--singular` | `-` | 単数形の名前
| `--plural` | `-` | 複数形の名前
| `--env` | `dev` | 環境
| `--actions-base-class` | `sfActions` | アクションの基底クラス


`propel:generate-module-for-route` タスクは Propel のルート定義に対応したモジュールを生成します。

    ./symfony propel:generate-module-for-route frontend article

上記の例では、`routing.yml` ファイルの `%article%` ルートに対応したモジュールが `%frontend%` アプリケーションに生成されます。

### ~`propel::graphviz`~

`propel::graphviz` タスクは現在のオブジェクトモデルに対応した Graphviz チャートを生成します。

    $ php symfony propel:graphviz [--phing-arg="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ----------------- | ------- | ------------------------------
| `--phing-arg` | `-` | 任意の Phing 引数 (複数の値が可能です)


`propel:graphviz` タスクは、オブジェクトモデルの自動グラフ描画のために Graphviz の DOT 可視化画像を生成します。

    ./symfony propel:graphviz

### ~`propel::insert-sql`~

`propel::insert-sql` タスクは現在のモデルに対応したテーブルを作ります。

    $ php symfony propel:insert-sql [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [--phing-arg="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ----------------- | ------- | -------------------
| `--application` | `1` | アプリケーションの名前
| `--env` | `cli` | 環境
| `--connection` | `-` | コネクションの名前
| `--no-confirmation` | `-` | 確認メッセージを省略します
| `--phing-arg` | `-` | 任意の Phing 引数 (複数の値が可能です)


`propel:insert-sql` タスクはデータベースのテーブルを作ります。

    ./symfony propel:insert-sql

このタスクはデータベースに接続し、`config/sql/` ディレクトリの `*schema.sql` ファイルのなかで見つかるすべての SQL 文を実行します。

このタスクは実行前にデータベースのデータをすべて削除するので、本当に実行するかどうかたずねてきます。

確認メッセージを省略するには、`--no-confirmation` オプションをつけます。

    ./symfony propel:insert-sql --no-confirmation

このタスクは `databases.yml` ファイルからデータベースのコンフィギュレーションを読み込みます。`--application` もしくは `--env` オプションを指定すれば、特定のアプリケーション/環境を利用することができます。

任意のコネクションで SQL 文だけを読み込ませたいのであれば、`--connection` オプションを指定します。

### ~`propel::schema-to-xml`~

`propel::schema-to-xml` タスクは schema.yml ファイルから schema.xml ファイルを作ります。

    $ php symfony propel:schema-to-xml  







`propel:schema-to-xml` タスクは YAML スキーマを XML に変換します。

    ./symfony propel:schema-to-xml

### ~`propel::schema-to-yml`~

`propel::schema-to-yml` タスクは `schema.xml` ファイルから `schema.yml` ファイルを作ります。

    $ php symfony propel:schema-to-yml  







`propel:schema-to-yml` タスクは XML スキーマを YAML に変換します。

    ./symfony propel:schema-to-yml

`symfony`
---------

### ~`symfony::test`~

`symfony::test`タスクはテストスイートを実行します。

    $ php symfony symfony:test [-u|--update-autoloader] [-f|--only-failed] [--xml="..."] [--rebuild-all] 





| <span style="white-space: nowrap">オプション (ショートカット)</span>      | <span style="white-space: nowrap">デフォルト</span>  | 説明
| --------------------------------- | --------- | -------------------------------------------
| `--update-autoloader`<br />`(-u)` | `-`       | sfCoreAutoload クラスを更新します
| `--only-failed`<br />`(-f)`       | `-`       | 最後に通らなかったテストだけを実行します
| `--xml`                           | `-`       | JUnit と互換性のある XML ログファイルの名前
| `--rebuild-all`                   | `-`       | すべての生成フィクスチャファイルを再構築します


`test:all` タスクはテストスイートを実行します。

    ./symfony symfony:test

`test`
------

### ~`test::all`~

`test::all` タスクはすべてのテストを実行します。

    $ php symfony test:all [-f|--only-failed] [--xml="..."] 





| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| --------------------------- | ---------- | -------------------------------------------
| `--only-failed`<br />`(-f)` | `-`        | 最後に通らなかったテストだけを実行します
| `--xml`                     | `-`        | JUnit と互換性のある XML ログファイルの名前


`test:all` タスクはすべてのユニットテストと機能テストを実行します。

    ./symfony test:all

このタスクは `test/` で見つかるすべてのテストを実行します。

`--trace` オプションをつければ、テストの一部が通らない場合にくわしい説明が表示されます。

    ./symfony test:all -t

もしくはこれらのテストスイートを `test:unit` と `test:functional` タスクで実行することで、問題の解決にとりかかることもできます。

`--only-failed` オプションをつければ、以前の実行のときに通らなかったテストだけを実行するように強制させることができます。

    ./symfony test:all --only-failed

どのように動くのかを説明します。最初において、すべてのテストがいつものように実行されますが、それ以降では、前回通らなかったテストのみが実行されます。コードの修正作業が進むにつれて、テストの一部が通るようになり、その後の実行から除外されるようになります。すべてのテストが通るようになったとき、フルテストスイートが実行され、徹底的に繰り返すことができます。

`--xml` オプションをつけると、JUnit と互換性のある XML ログファイルが出力されます。

    ./symfony test:all --xml=log.xml

### ~`test::coverage`~

`test::coverage` タスクはテストのコードカバレッジを出力します。

    $ php symfony test:coverage [--detailed] test_name lib_name



| 引数        | デフォルト | 説明
| ----------- | ---------- | --------------------------------------------------------------
| `test_name` | `-`        | テストファイルの名前もしくはテストのディレクトリ
| `lib_name`  | `-`        | カバレージを知りたいファイルの名前もしくは lib ディレクトリ


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | ---------- | --------------------
| `--detailed`               | `-`        | くわしい説明を出力します


`test:coverage` タスクは test ディレクトリまたは test ディレクトリと lib ファイルまたは lib ディレクトリとしてコードカバレージを出力します。

    ./symfony test:coverage test/unit/model lib/model

`--detailed` オプションを指定すれば、カバーされていない行が出力されます。

    ./symfony test:coverage --detailed test/unit/model lib/model

### ~`test::functional`~

`test::functional` タスクは機能テストを実行します。

    $ php symfony test:functional [--xml="..."] application [controller1] ... [controllerN]



| 引数          | デフォルト | 説明
| ------------- | ---------- | -----------------------
| `application` | `-`        | アプリケーションの名前
| `controller`  | `-`        | コントローラの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| ---------------------------- | ----------- | ---------------
| `--xml` | `-` | JUnit と互換性のある XML ログファイルの名前


`test:functional` タスクは任意のアプリケーションに対して機能テストを実行します。

    ./symfony test:functional frontend

このタスクは `test/functional/%application%/` ディレクトリで見つかるすべてのテストを実行します。

`--trace` オプションをつければ、テストの一部が通らない場合にくわしい説明が表示されます。

    ./symfony test:functional frontend -t

コントローラの名前を渡せば、特定のコントローラに対するすべての機能テストを実行することができます。

    ./symfony test:functional frontend article

複数のコントローラに対する機能テストをまとめて実行することもできます。

    ./symfony test:functional frontend article comment

`--xml` オプションを指定すると、JUnit と互換性のある XML ログファイルが出力されます。

    ./symfony test:functional --xml=log.xml

### ~`test::unit`~

`test::unit` タスクはユニットテストを実行します。

    $ php symfony test:unit [--xml="..."] [name1] ... [nameN]



| 引数     | デフォルト | 説明
| -------- | ---------- | -----------
| `name`   | `-`        | テストの名前


| <span style="white-space: nowrap">オプション (ショートカット)</span> | <span style="white-space: nowrap">デフォルト</span> | 説明
| -------------------------- | --------- | ----------------------------
| `--xml`       | `-`        | JUnit と互換性のある XML ログファイルの名前


`test:unit` タスクはユニットテストを実行します。

    ./symfony test:unit

このタスクは `test/unit/` ディレクトリで見つかるすべてのテストを実行します。

`--trace` オプションをつければ、テストの一部が通らない場合にくわしい説明が表示されます。

    ./symfony test:unit -t

特定のユニットテストを指名することができます。

    ./symfony test:unit strtolower

複数のユニットテストをまとめて実行することもできます。

    ./symfony test:unit strtolower strtoupper

`--xml` オプションを指定すれば、JUnit と互換性のある XML ログファイルが出力されます。

    ./symfony test:unit --xml=log.xml



