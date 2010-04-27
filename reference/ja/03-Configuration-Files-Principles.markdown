設定ファイルの原則
===================

symfony の設定ファイルは一連の共通原則にしたがって共通のプロパティを共有します。この節ではほかの節のガイダンスとして共通原則を詳しく説明します。

キャッシュ
----------

symfony のすべての設定ファイルはコンフィギュレーションハンドラクラスによって PHP ファイルとしてキャッシュされます。`is_debug` 設定が `false` にセットされているとき (たとえば `prod` 環境)、YAML ファイルは初回リクエストのときのみアクセスされます。次回以降のリクエストでは PHP キャッシュが使われます。このような仕様が意味するのは、初回時のみ YAML ファイルのパースと解釈を行うことで、「重い」処理は1回だけで済むということです。

>**TIP**
>`dev` 環境のデフォルトでは、`is_debug` が `true` にセットされており、設定ファイルが変更されるたびにコンパイルが行われます (symfony がファイルの修正時刻をチェックします)。

それぞれの設定ファイルのパースとキャッシュは、[`config_handler.yml`](#chapter_14_config_handlers_yml)  で設定されている特別なコンフィギュレーションハンドラクラスで行われます。

次の節で「コンパイル」について説明します。コンパイルとは、初回アクセスのときに、YAML ファイルが PHP ファイルに変換され、キャッシュに保存されることを意味します。

>**TIP**
>コンフィギュレーションキャッシュのリロードを強制するには、`cache:clear` タスクを使います:
>
>     $ php symfony cache:clear --type=config

定数
----

*設定ファイル*:
`core_compile.yml`、`factories.yml`、`generator.yml`、`databases.yml`、
`filters.yml`、`view.yml`、`autoload.yml`

設定ファイルのなかには、あらかじめ定義されている定数の使用を許可するものがあります。定数は `%XXX%` (XXX は大文字のキー) で表されるプレースホルダで宣言され、「コンパイル」のときに実際の値に置き換わります。

### コンフィギュレーションの設定項目

定数には `settings.yml` 設定ファイルで定義されている任意の設定が格納されます。プレースホルダのキーは `SF_` をプレフィックスとする大文字の設定キーの名前です:

    [yml]
    logging: %SF_LOGGING_ENABLED%

symfony が設定ファイルをコンパイルするとき、既存の `%SF_XXX%` プレースホルダはすべて `settings.yml` からの値に置き換わります。上記の例では、`SF_LOGGING_ENABLED` プレースホルダは `settings.yml` で定義されている `logging_enabled` の値に置き換わります。

### アプリケーションの設定項目

`app.yml` 設定ファイルで定義されている設定にアクセスするには、キーの名前にプレフィックスの `APP_` をつけた文字列を使います。

### 特別な定数

デフォルトでは、現在のフロントコントローラに合わせて、symfony は4つの定数を定義します:

 | 定数                   | 説明           | コンフィギュレーション<br />メソッド |
 | ---------------------- | ----------- --- | -------------------------------------- |
 | ~`SF_APP`~             | 現在のアプリケーションの名前     | `getApplication()`  |
 | ~`SF_ENVIRONMENT`~     | 現在の環境の名前                  | `getEnvironment()`  |
 | ~`SF_DEBUG`~           | デバッグモードが有効であるか     | `isDebug()`         |
 | ~`SF_SYMFONY_LIB_DIR`~ | symfony ライブラリのディレクトリ | `getSymfonyLibDir()` |

### ディレクトリ

ディレクトリもしくはファイルパスを参照するとき、決め打ちよりも定数のほうがはるかに便利です。共通のプロジェクトとアプリケーションディレクトリのために、symfony は多くの定数を定義します。

階層の基点になる定数は `SF_ROOT_DIR` で、これはプロジェクトのルートディレクトリを表します。ほかのすべての定数はこのルートディレクトリから派生します。

プロジェクトのディレクトリ構造は次のように定義されています:

 | 定数               | デフォルト値        |
 | ------------------ | -------------------- |
 | ~`SF_APPS_DIR`~    | `SF_ROOT_DIR/apps`   |
 | ~`SF_CONFIG_DIR`~  | `SF_ROOT_DIR/config` |
 | ~`SF_CACHE_DIR`~   | `SF_ROOT_DIR/cache`  |
 | ~`SF_DATA_DIR`~    | `SF_ROOT_DIR/data`   |
 | ~`SF_DOC_DIR`~     | `SF_ROOT_DIR/doc`    |
 | ~`SF_LIB_DIR`~     | `SF_ROOT_DIR/lib`    |
 | ~`SF_LOG_DIR`~     | `SF_ROOT_DIR/log`    |
 | ~`SF_PLUGINS_DIR`~ | `SF_ROOT_DIR/plugins`|
 | ~`SF_TEST_DIR`~    | `SF_ROOT_DIR/test`   |
 | ~`SF_WEB_DIR`~     | `SF_ROOT_DIR/web`    |
 | ~`SF_UPLOAD_DIR`~  | `SF_WEB_DIR/uploads` |

アプリケーションのディレクトリ構造は `SF_APPS_DIR/APP_NAME` ディレクトリの下で定義されています:

 | 定数                    | デフォルト値          |
 | ----------------------- | ---------------------- |
 | ~`SF_APP_CONFIG_DIR`~   | `SF_APP_DIR/config`    |
 | ~`SF_APP_LIB_DIR`~      | `SF_APP_DIR/lib`       |
 | ~`SF_APP_MODULE_DIR`~   | `SF_APP_DIR/modules`   |
 | ~`SF_APP_TEMPLATE_DIR`~ | `SF_APP_DIR/templates` |
 | ~`SF_APP_I18N_DIR`~     | `SF_APP_DIR/i18n`      |

最後に、アプリケーションキャッシュのディレクトリ構造は次のように定義されています:

 | 定数                      | デフォルト値                    |
 | ------------------------- | -------------------------------- |
 | ~`SF_APP_BASE_CACHE_DIR`~ | `SF_CACHE_DIR/APP_NAME`          |
 | ~`SF_APP_CACHE_DIR`~      | `SF_CACHE_DIR/APP_NAME/ENV_NAME` |
 | ~`SF_TEMPLATE_CACHE_DIR`~ | `SF_APP_CACHE_DIR/template`      |
 | ~`SF_I18N_CACHE_DIR`~     | `SF_APP_CACHE_DIR/i18n`          |
 | ~`SF_CONFIG_CACHE_DIR`~   | `SF_APP_CACHE_DIR/config`        |
 | ~`SF_TEST_CACHE_DIR`~     | `SF_APP_CACHE_DIR/test`          |
 | ~`SF_MODULE_CACHE_DIR`~   | `SF_APP_CACHE_DIR/modules`       |

環境の認識
----------

*設定ファイル*: `settings.yml`、`factories.yml`、`databases.yml`、`app.yml`

symfony の設定ファイルのなかには環境を認識するものがあり、これらの環境認識は symfony が動く現在の環境に依存します。これらの設定ファイルには環境ごとに異なるセクションが用意されており、それぞれのコンフィギュレーションを定義できます。新しいアプリケーションを作るとき、symfony は3つのデフォルト環境 (`prod`、`test` と `dev`) を含む適切な設定ファイルを用意します:

    [yml]
    prod:
      # prod 環境のコンフィギュレーション

    test:
      # test 環境のコンフィギュレーション

    dev:
      # dev 環境のコンフィギュレーション

    all:
      # すべての環境のデフォルトコンフィギュレーション

symfony が設定ファイルからの値を必要とするとき、現在の環境セクションで見つかるコンフィギュレーションと `all` セクションのコンフィギュレーションをマージします。特別な `all` セクションはすべての環境のデフォルトコンフィギュレーションを定義します。環境セクションが定義されていなければ、symfony は代わりに `all` コンフィギュレーションを使います。

コンフィギュレーションカスケード
--------------------------------

*設定ファイル*: `core_compile.yml`、`autoload.yml`、`settings.yml`、
`factories.yml`、`databases.yml`、`security.yml`、`cache.yml`、
`app.yml`、`filters.yml`、`view.yml`

プロジェクトのディレクトリ構造には複数の `config/` サブディレクトリが収められており、設定ファイルを設置すれば、考慮されます。

コンフィギュレーションがコンパイルされるとき、すべての異なるファイルからの値は次の優先順位にしたがってマージされます

  * モジュールのコンフィギュレーション (`PROJECT_ROOT_DIR/apps/APP_NAME/modules/MODULE_NAME/config/XXX.yml`)
  * アプリケーションのコンフィギュレーション (`PROJECT_ROOT_DIR/apps/APP_NAME/config/XXX.yml`)
  * プロジェクトのコンフィギュレーション (`PROJECT_ROOT_DIR/config/XXX.yml`)
  * プラグインで定義されているコンフィギュレーション (`PROJECT_ROOT_DIR/plugins/*/config/XXX.yml`)
  * symfony ライブラリで定義されているデフォルトコンフィギュレーション (`SF_LIB_DIR/config/XXX.yml`)

たとえば、アプリケーションディレクトリのなかで定義されている `settings.yml` は、プロジェクトの `config/` ディレクトリのメインコンフィギュレーションのセット、およびフレームワーク自身に収められているデフォルトコンフィギュレーション (`lib/config/config/settings.yml`) を継承します。

>**TIP**
>設定ファイルは、複数のディレクトリのなかで定義可能で、環境を認識します。次の優先順位リストが適用されます:
>
> 1. モジュール
> 2. アプリケーション
> 3. プロジェクト
> 4. 特定の環境
> 5. すべての環境
> 6. デフォルト
