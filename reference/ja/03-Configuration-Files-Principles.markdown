設定ファイルの原則
===================

symfony の設定ファイルは一連の共通原則にしたがって共通のプロパティを共有します。ほかの章のガイダンスとして、この章では共通原則をくわしく説明します。

キャッシュ
----------

すべての設定ファイルのキャッシュはコンフィギュレーションハンドラクラスによって PHP ファイルとして保存されます。`is_debug` 設定に `false` がセットされている場合 (たとえば `prod` 環境)、YAML ファイルがアクセスされるのは初回リクエストのときにかぎられます。次回以降のリクエストではキャッシュとして保存された PHP ファイルが使われます。このような仕様が意味するのは、YAML ファイルのパースと解釈が実行されるのは初回リクエストにかぎられるので、「重い」処理は1回だけですむということです。

>**TIP**
>`dev` 環境のデフォルトでは、`is_debug` 設定に `true` がセットされており、設定ファイルが変更されるたびにコンパイルが実行されます (symfony がファイルの修正時刻をチェックします)。

[`config_handler.yml`](#chapter_14_config_handlers_yml) ファイルで指定されている特別なコンフィギュレーションハンドラクラスがそれぞれの設定ファイルのパースとキャッシュを担当します。

次の節で「コンパイル」について説明します。コンパイルされるとは、初回アクセスのときに、YAML ファイルが PHP ファイルに変換され、キャッシュに保存されることを意味します。

>**TIP**
>コンフィギュレーションキャッシュのリロードを強制するには、`cache:clear` タスクを実行します。
>
>     $ php symfony cache:clear --type=config

定数
----

*設定ファイル*:
`core_compile.yml`、`factories.yml`、`generator.yml`、`databases.yml`、
`filters.yml`、`view.yml`、`autoload.yml`

設定ファイルのなかにはあらかじめ定義されている定数の使用を許可しているものがあります。定数の宣言は `%XXX%` プレースホルダ (XXX は大文字のキー) であらわされ、「コンパイル」のときに実際の値に置き換わります。

### コンフィギュレーションの設定項目

`settings.yml` ファイルで定義されている任意の設定に定数を投入することができます。プレースホルダのキーは `SF_` をプレフィックスとする大文字の設定キーの名前です。

    [yml]
    logging: %SF_LOGGING_ENABLED%

symfony が設定ファイルをコンパイルするとき、既存のすべての `%SF_XXX%` プレースホルダは `settings.yml` ファイルからの値に置き換わります。上記の例では、`SF_LOGGING_ENABLED` プレースホルダは `settings.yml` ファイルで定義されている `logging_enabled` 設定の値に置き換わります。

### アプリケーションの設定項目

`app.yml` ファイルで定義されている設定にアクセスするには、キーの名前にプレフィックスの `APP_` をつけた文字列を使います。

### 特別な定数

デフォルトでは、現在のフロントコントローラに対して4つの定数が定義されています。

 | 定数                   | 説明            | コンフィギュレーションメソッド |
 | ---------------------- | --------------  | -------------------------------------- |
 | ~`SF_APP`~             | 現在のアプリケーションの名前     | `getApplication()`  |
 | ~`SF_ENVIRONMENT`~     | 現在の環境の名前                 | `getEnvironment()`  |
 | ~`SF_DEBUG`~           | デバッグモードが有効であるか     | `isDebug()`         |
 | ~`SF_SYMFONY_LIB_DIR`~ | symfony ライブラリのディレクトリ | `getSymfonyLibDir()` |

### ディレクトリ

ディレクトリもしくはファイルパスを参照するとき、ハードコーディングよりも定数のほうがはるかに便利です。共通のプロジェクトとアプリケーションディレクトリのためにさまざまな定数が定義されています。

階層の基点となるプロジェクトのルートディレクトリをあらわす定数は `SF_ROOT_DIR` です。ほかのすべての定数はこのルートディレクトリから派生します。

プロジェクトのディレクトリ構造は次のように定義されています。

 | 定数               | デフォルト           |
 | ------------------ | -------------------- |
 | ~`SF_APPS_DIR`~    | `SF_ROOT_DIR/apps`   |
 | ~`SF_CONFIG_DIR`~  | `SF_ROOT_DIR/config` |
 | ~`SF_CACHE_DIR`~   | `SF_ROOT_DIR/cache`  |
 | ~`SF_DATA_DIR`~    | `SF_ROOT_DIR/data`   |
 | ~`SF_LIB_DIR`~     | `SF_ROOT_DIR/lib`    |
 | ~`SF_LOG_DIR`~     | `SF_ROOT_DIR/log`    |
 | ~`SF_PLUGINS_DIR`~ | `SF_ROOT_DIR/plugins`|
 | ~`SF_TEST_DIR`~    | `SF_ROOT_DIR/test`   |
 | ~`SF_WEB_DIR`~     | `SF_ROOT_DIR/web`    |
 | ~`SF_UPLOAD_DIR`~  | `SF_WEB_DIR/uploads` |

アプリケーションのディレクトリ構造は `SF_APPS_DIR/APP_NAME` ディレクトリの下で定義されています。

 | 定数                    | デフォルト             |
 | ----------------------- | ---------------------- |
 | ~`SF_APP_CONFIG_DIR`~   | `SF_APP_DIR/config`    |
 | ~`SF_APP_LIB_DIR`~      | `SF_APP_DIR/lib`       |
 | ~`SF_APP_MODULE_DIR`~   | `SF_APP_DIR/modules`   |
 | ~`SF_APP_TEMPLATE_DIR`~ | `SF_APP_DIR/templates` |
 | ~`SF_APP_I18N_DIR`~     | `SF_APP_DIR/i18n`      |

最後に、アプリケーションキャッシュのディレクトリ構造は次のように定義されています。

 | 定数                      | デフォルト                       |
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

設定ファイルのなかには環境を認識するものがあり、これらの環境認識は symfony のプロジェクトがホストされているサーバーの環境にもとづいています。これらの設定ファイルには、環境ごとに専用のセクションが設けられており、環境ごとに異なるコンフィギュレーションを定義できます。作られた直後のアプリケーションでは、3つのデフォルト環境 (`prod`、`test` と `dev`) を含む設定ファイルが用意されています。

    [yml]
    prod:
      # prod 環境のコンフィギュレーション

    test:
      # test 環境のコンフィギュレーション

    dev:
      # dev 環境のコンフィギュレーション

    all:
      # すべての環境のデフォルトコンフィギュレーション

symfony が設定ファイルからの値を必要とするとき、現在の環境セクションで見つかるコンフィギュレーションと `all` セクションのコンフィギュレーションがマージされます。`all` セクションは特殊なセクションで、すべての環境のデフォルトコンフィギュレーションが定義されています。環境セクションが定義されていなければ、`all` コンフィギュレーションが代わりに使われます。

コンフィギュレーションカスケード
--------------------------------

*設定ファイル*: `core_compile.yml`、`autoload.yml`、`settings.yml`、
`factories.yml`、`databases.yml`、`security.yml`、`cache.yml`、
`app.yml`、`filters.yml`、`view.yml`

symfony のプロジェクトディレクトリには複数の `config/` サブディレクトリが収められており、設定ファイルを配置することができます。

コンフィギュレーションがコンパイルされるとき、すべての異なるファイルからの値は次の優先順位にしたがってマージされます。

  * モジュールのコンフィギュレーション (`PROJECT_ROOT_DIR/apps/APP_NAME/modules/MODULE_NAME/config/XXX.yml`)
  * アプリケーションのコンフィギュレーション (`PROJECT_ROOT_DIR/apps/APP_NAME/config/XXX.yml`)
  * プロジェクトのコンフィギュレーション (`PROJECT_ROOT_DIR/config/XXX.yml`)
  * プラグインで定義されているコンフィギュレーション (`PROJECT_ROOT_DIR/plugins/*/config/XXX.yml`)
  * symfony のライブラリで定義されているデフォルトコンフィギュレーション (`SF_LIB_DIR/config/XXX.yml`)

たとえば、アプリケーションディレクトリに配置されている `settings.yml` ファイルが継承するのは、プロジェクトの `config/` ディレクトリに収められている一連のメインコンフィギュレーション、およびフレームワーク自身に収められているデフォルトコンフィギュレーションです (`lib/config/config/settings.yml`)。

>**TIP**
>設定ファイルは複数のディレクトリのなかで定義可能で、環境を認識します。次の優先順位リストが適用されます。
>
> 1. モジュール
> 2. アプリケーション
> 3. プロジェクト
> 4. 特定の環境
> 5. すべての環境
> 6. デフォルト
