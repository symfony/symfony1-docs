そのほかの設定ファイル
======================

この章ではそのほかの symfony の設定ファイルを説明します。これらを変更する必要性はほとんどありません。

~`autoload.yml`~
----------------

`autoload.yml` 設定は symfony によってオートロードされる必要のあるディレクトリを決定します。PHP クラスとインターフェイスを見つけるためにそれぞれのディレクトリがスキャンされます。

第 3 章で説明したように、`autoload.yml` ファイルは[**コンフィギュレーションカスケードのメカニズム**](#chapter_03)が有効で、[**定数**](#chapter_03)を収めることができます。

>**NOTE**
>`autoload.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 

たいていのプロジェクトではデフォルトのコンフィギュレーションで十分です:

    [yml]
    autoload:
      # project
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      on
        exclude:        [model, symfony]

      project_model:
        name:           project model
        path:           %SF_LIB_DIR%/model
        recursive:      on

      # application
      application:
        name:           application
        path:           %SF_APP_LIB_DIR%
        recursive:      on

      modules:
        name:           module
        path:           %SF_APP_DIR%/modules/*/lib
        prefix:         1
        recursive:      on

それぞれのコンフィギュレーションは名前を持ち、その名前を持つキーの下でセットしなければなりません。これによってオーバーライドされるデフォルトの設定が可能になります。

>**TIP**
>ご覧のとおり、デフォルトでは `lib/vendor/symfony/` ディレクトリは除外されます。symfony はコアクラスには異なるオートロードのメカニズムを利用するからです。

オートロードのふるまいをカスタマイズするためにいくつかのキーを使うことができます:

 * `name`: 説明
 * `path`: オートロードするパス
 * `recursive`: サブディレクトリで PHP クラスを探すか
 * `exclude`: 検索から除外するディレクトリの名前の配列
 * `prefix`: パスで見つかるクラスが指定されたモジュールのみをオートロードする場合 `true` にセットする (デフォルトでは `false`)
 * `files`: PHP クラスのために明示的に解析するファイルの配列
 * `ext`: PHP クラスの拡張子 (デフォルトは `.php`)

たとえば、`lib/` ディレクトリの下でプロジェクト内部で大きなライブラリを埋め込み、オートロード機能がすでにサポートされている場合、パフォーマンスを向上させるために `project` のオートロードコンフィギュレーションを修正することで symfony のデフォルトのオートロードシステムからそのライブラリを除外できます:

    [yml]
    autoload:
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      on
        exclude:        [model, symfony, vendor/large_lib]

~`config_handlers.yml`~
-----------------------

`config_handlers.yml` 設定ファイルはほかのすべての YAML 設定ファイルを解釈するために使われるコンフィギュレーションハンドラークラスを記述します。`settings.yml` 設定ファイルをロードするために使われるデフォルトコンフィギュレーションは次のとおりです:

    [yml]
    config/settings.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: sf_

それぞれの設定ファイルはクラス (`class` エントリ) によって定義し `param` セクションの下でパラメーターを定義することでさらにカスタマイズできます。

デフォルトの `config_handlers.yml` ファイルは次のようにパーサークラスを定義します:

 | 設定ファイル       | コンフィギュレーションハンドラークラス |
 | ------------------ | ------------------------------------ |
 | `autoload.yml`     | `sfAutoloadConfigHandler`            |
 | `databases.yml`    | `sfDatabaseConfigHandler`            |
 | `settings.yml`     | `sfDefineEnvironmentConfigHandler`   |
 | `app.yml`          | `sfDefineEnvironmentConfigHandler`   |
 | `factories.yml`    | `sfFactoryConfigHandler`             |
 | `core_compile.yml` | `sfCompileConfigHandler`             |
 | `filters.yml`      | `sfFilterConfigHandler`              |
 | `routing.yml`      | `sfRoutingConfigHandler`             |
 | `generator.yml`    | `sfGeneratorConfigHandler`           |
 | `view.yml`         | `sfViewConfigHandler`                |
 | `security.yml`     | `sfSecurityConfigHandler`            |
 | `cache.yml`        | `sfCacheConfigHandler`               |
 | `module.yml`       | `sfDefineEnvironmentConfigHandler`   |

~`core_compile.yml`~
--------------------

`core_compile.yml` 設定ファイルは symfony のロード時間を加速するために `prod` 環境で 1 つの大きなファイルにマージされる PHP ファイルを記述します。デフォルトでは、symfony のメインのコアクラスはこの設定ファイルで定義されます。アプリケーションがそれぞれのリクエストごとにロードする必要のあるいくつかのクラスに依存する場合、プロジェクトもしくはアプリケーションの `core_compile.yml` 設定ファイルを作成しこれらのクラスを設定ファイルに追加できます。デフォルトコンフィギュレーションの抜粋は次の通りです:

    [yml]
    - %SF_SYMFONY_LIB_DIR%/autoload/sfAutoload.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfComponent.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfAction.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfActions.class.php

第 3 章で説明したように、`core_compile.yml` ファイルは[**コンフィギュレーションカスケードのメカニズム**](#chapter_03)が有効で、[**定数**](#chapter_03)を収めることができます。

>**NOTE**
>`core_compile.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は ~`sfCompileConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

~`module.yml`~
--------------

`module.yml` 設定ファイルはモジュールのコンフィギュレーションを可能にします。この設定ファイルはほとんど使われることはなく、下記の定義されたエントリのみを収めます。

`module.yml` ファイルは symfony によってロードされるモジュールの `config/` サブディレクトリに保存されます。次のコードはすべての設定のデフォルト値を持つ `module.yml` の典型的な内容を示しています:

    [yml]
    all:
      enabled:            true
      view_class:         sfPHP
      partial_view_class: sf

`enabled` パラメーターが `false` にセットされる場合、モジュールのすべてのアクションは無効になります。これらは ([`settings.yml`](#chapter_04) で定義される) ~[`module_disabled_module`](#chapter_04_the_actions_sub_section)~/~`module_disabled_action`~ アクションにリダイレクトされます。

`view_class` パラメーターはモジュールのすべてのアクションによって使われる (サフィックスなしの `View` の) ビュークラスを定義します。これは `sfView` を継承しなければなりません。

`partial_view_class` パラメーターは (サフィックスの `PartialView` なしの) このモジュールのパーシャルに使われるビュークラスを定義します。`sfPartialView` を継承しなければなりません。
