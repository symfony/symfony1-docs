その他の設定ファイル
======================

この章では、symfony のその他の設定ファイルを説明します。これらを変更する必要性はほとんどありません。

~`autoload.yml`~
----------------

`autoload.yml` 設定ファイルでは、オートロードされる必要のあるディレクトリが決まります。PHP クラスとインターフェイスを見つけるためにそれぞれのディレクトリがスキャンされます。

[設定ファイルの原則の章](#chapter_03)で説明したように、`autoload.yml` ファイルでは、**コンフィギュレーションカスケードのメカニズム**がはたらき、**定数**を定義することができます。

>**NOTE**
>`autoload.yml` 設定ファイルは PHP ファイルとしてキャッシュされます。

ほとんどのプロジェクトでは、デフォルトのコンフィギュレーションで十分です:

    [yml]
    autoload:
      # プロジェクト
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony]

      project_model:
        name:           project model
        path:           %SF_LIB_DIR%/model
        recursive:      true

      # アプリケーション
      application:
        name:           application
        path:           %SF_APP_LIB_DIR%
        recursive:      true

      modules:
        name:           module
        path:           %SF_APP_DIR%/modules/*/lib
        prefix:         1
        recursive:      true

それぞれのコンフィギュレーションは名前をもち、その名前をもつキーの下でセットしなければなりません。このことによって、デフォルトのコンフィギュレーションをオーバーライドできます。

>**TIP**
>ご覧のとおり、デフォルトでは、`lib/vendor/symfony/` ディレクトリは除外されます。symfony はコアクラスに対して異なるオートロードメカニズムを利用するからです。

オートロードのふるまいをカスタマイズするために、いくつかのキーを使うことができます:

 * `name`: 説明
 * `path`: オートロードするパス
 * `recursive`: サブディレクトリで PHP クラスを探索するか
 * `exclude`: 検索から除外するディレクトリの名前の配列
 * `prefix`: 指定モジュールのために、パスで見つかるクラスだけをオートロードの対象にする場合 `true` にセットします (デフォルトは `false`)
 * `files`: PHP クラスのために明示的にパースするファイルの配列
 * `ext`: PHP クラスの拡張子 (デフォルトは `.php`)

たとえば、オートロードをサポートする大きなライブラリをプロジェクトの `lib/` ディレクトリに組み込む場合、パフォーマンスを向上させるには、`project` のオートロードコンフィギュレーションを修正して、symfony のデフォルトのオートロードシステムの対象からこのライブラリを除外します:

    [yml]
    autoload:
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony, vendor/large_lib]

~`config_handlers.yml`~
-----------------------

`config_handlers.yml` 設定ファイルでは、ほかのすべての YAML 設定ファイルを解釈するために使われるコンフィギュレーションハンドラクラスを記入します。`settings.yml` 設定ファイルをロードするために使われるデフォルトコンフィギュレーションは次のとおりです:

    [yml]
    config/settings.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: sf_

それぞれの設定ファイルはクラス (`class` エントリ) によって定義され、`param` セクションの下でパラメータを定義することで、細かくカスタマイズできます。

デフォルトの `config_handlers.yml` ファイルは次のようなパーサークラスを定義します:

 | 設定ファイル       | コンフィグハンドラクラス         |
 | ------------------ | ---------------------------------- |
 | `autoload.yml`     | `sfAutoloadConfigHandler`          |
 | `databases.yml`    | `sfDatabaseConfigHandler`          |
 | `settings.yml`     | `sfDefineEnvironmentConfigHandler` |
 | `app.yml`          | `sfDefineEnvironmentConfigHandler` |
 | `factories.yml`    | `sfFactoryConfigHandler`           |
 | `core_compile.yml` | `sfCompileConfigHandler`           |
 | `filters.yml`      | `sfFilterConfigHandler`            |
 | `routing.yml`      | `sfRoutingConfigHandler`           |
 | `generator.yml`    | `sfGeneratorConfigHandler`         |
 | `view.yml`         | `sfViewConfigHandler`              |
 | `security.yml`     | `sfSecurityConfigHandler`          |
 | `cache.yml`        | `sfCacheConfigHandler`             |
 | `module.yml`       | `sfDefineEnvironmentConfigHandler` |

~`core_compile.yml`~
--------------------

`core_compile.yml` 設定ファイルでは、symfony のロード時間を短縮するために `prod` 環境で1つの大きなファイルにマージされる PHP ファイルを記入します。デフォルトでは、symfony のメインのコアクラスが定義されます。アプリケーションがリクエストごとにロードする必要のある複数のクラスに依存する場合、プロジェクトもしくはアプリケーションの `core_compile.yml` 設定ファイルを用意すれば、これらのクラスを設定ファイルに追加できます。デフォルトコンフィギュレーションの抜粋は次のとおりです:

    [yml]
    - %SF_SYMFONY_LIB_DIR%/autoload/sfAutoload.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfComponent.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfAction.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfActions.class.php

[設定ファイルの原則の章](#chapter_03)で説明したように、`core_compile.yml` ファイルでは、**コンフィギュレーションカスケードのメカニズム**がはたらき、**定数**を定義することができます。

>**NOTE**
>`core_compile.yml` 設定ファイルは PHP ファイルとしてキャッシュされます。処理は ~`sfCompileConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

~`module.yml`~
--------------

`module.yml` 設定ファイルではモジュールのコンフィギュレーションを変更できます。この設定ファイルが変更されることはほとんどなく、下記の定義済みのエントリだけが用意されています。

`module.yml` ファイルは symfony によってロードされるモジュールの `config/` サブディレクトリに保存されます。次のコードはすべての設定のデフォルトが用意されている `module.yml` の典型的な内容を示しています:

    [yml]
    all:
      enabled:            true
      view_class:         sfPHP
      partial_view_class: sf

`enabled` パラメータが `false` にセットされている場合、モジュールのすべてのアクションは無効になります。これらのアクションへのリクエストは ([`settings.yml`](#chapter_04) で定義されている) [~`module_disabled_module`~](#chapter_04)/~`module_disabled_action`~ アクションにリダイレクトされます。

`view_class` パラメータは、モジュールのすべてのアクションによって使われ、`sfView` を継承するビュークラスを定義します (サフィックスの `View` はつけません)。

`partial_view_class` パラメータは、このモジュールのパーシャルに使われ、`sfPartialView` を継承するビュークラスを定義します (サフィックスの `PartialView` はつけません)。
