その他の設定ファイル
=====================

この章では、その他の設定ファイルを説明します。これらを変更することはほとんどありません。

~`autoload.yml`~
----------------

オートロードの対象となるディレクトリは `autoload.yml` ファイルのなかで指定できます。PHP クラスとインターフェイスを捜索するために、それぞれのディレクトリがスキャンされます。

[設定ファイルの原則の章](#chapter_03)で述べたように、`autoload.yml` ファイルでは、**コンフィギュレーションカスケード**のメカニズムがはたらいており、**定数**を定義することができます。

>**NOTE**
>`autoload.yml` ファイルのキャッシュは PHP ファイルとして保存されます。

ほとんどのプロジェクトでは、デフォルトのコンフィギュレーションで事足ります。

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

それぞれのコンフィギュレーションに名前をつけ、その名前と同じキーの下側でコンフィギュレーションの内容を記述しなければなりません。これらの作業をおこなうことで、デフォルトのコンフィギュレーションをオーバーライドできます。

>**TIP**
>ご覧のとおり、デフォルトでは、`lib/vendor/symfony/` ディレクトリは除外されています。コアクラスに対して異なるオートロードメカニズムがはたらいているからです。

オートロードのふるまいをカスタマイズするには、次のキーを使います。

 * `name`: 説明
 * `path`: オートロードの対象となるパス
 * `recursive`: サブディレクトリで PHP クラスを探索するか
 * `exclude`: 検索の対象から除外するディレクトリの名前の配列
 * `prefix`: 指定したモジュールのために、オートロードの対象になるクラスをパスで見つかるものに限定する場合、`true` をセットします (デフォルトは `false`)
 * `files`: PHP クラスのためにパースされるファイルの配列
 * `ext`: PHP クラスの拡張子 (デフォルトは `.php`)

たとえば、オートロード機能をサポートしている大きなライブラリをプロジェクトの `lib/` ディレクトリに組み込む場合、パフォーマンスを向上させるために、`project` のオートロードコンフィギュレーションを修正して、このライブラリをオートロードの対象から外すことができます。

    [yml]
    autoload:
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony, vendor/large_lib]

~`config_handlers.yml`~
-----------------------

ほかのすべての YAML ファイルのパースに使われるコンフィギュレーションハンドラクラスは `config_handlers.yml` ファイルに登録します。`settings.yml` ファイルのロードに使われるデフォルトのコンフィギュレーションは次のようになります。

    [yml]
    config/settings.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: sf_

それぞれの設定ファイルはクラス (`class` エントリ) によって定義され、`param` セクションのなかでパラメータを定義することで、細かくカスタマイズできます。

>**TIP**
>自前のコンフィギュレーションハンドラを追加するとき、ハンドラのソースファイルに設けられている `class` と `file` エントリの下側でクラスの名前とフルパスをそれぞれ指定しなけばなりません。`sfApplicationConfiguration` クラスのなかでメカニズムを有効にする前にコンフィギュレーションを初期化する必要があるからです。

`config_handlers.yml` ファイルでは、次のようなデフォルトのパーサークラスが定義されています。

 | 設定ファイル       | コンフィグハンドラクラス           |
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

symfony のロード時間を短縮するために、`prod` 環境において1つの大きなファイルにマージされる PHP ファイルの名前は `core_compile.yml` ファイルに登録できます。デフォルトでは、symfony コアの主要なクラスが登録されています。アプリケーションがリクエストごとにロードする必要のある複数のクラスに依存している場合、プロジェクトもしくはアプリケーションの `core_compile.yml` ファイルを配置すれば、これらのクラスをマージの対象に追加できます。次のコードはデフォルトのコンフィギュレーションの内容を抜粋したものです。

    [yml]
    - %SF_SYMFONY_LIB_DIR%/autoload/sfAutoload.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfComponent.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfAction.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfActions.class.php

[設定ファイルの原則の章](#chapter_03)で述べたように、`core_compile.yml` ファイルでは、**コンフィギュレーションカスケード**のメカニズムがはたらいており、**定数**を定義することができます。

>**NOTE**
>`core_compile.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は ~`sfCompileConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)にゆだねられます。

~`module.yml`~
--------------

モジュールのコンフィギュレーションを変更する場所は `module.yml` ファイルです。この設定ファイルを変更することはほとんどなく、下記の定義済みのエントリが用意されています。

`module.yml` ファイルはモジュールの `config/` サブディレクトリに配置されています。次のコードは `module.yml` ファイルの典型的な内容で、すべての設定のデフォルトが用意されています。

    [yml]
    all:
      enabled:            true
      view_class:         sfPHP
      partial_view_class: sf

`enabled` パラメータに `false` をセットすれば、モジュールのアクションはすべて無効になります。これらのアクションへのリクエストは ([`settings.yml`](#chapter_04) ファイルで定義されている) [~`module_disabled_module`~](#chapter_04)/~`module_disabled_action`~ アクションにリダイレクトされます。

`view_class` パラメータは `sfView` 基底クラスを継承するビュークラスを定義します (サフィックスの `View` はつきません)。このクラスはモジュールのすべてのアクションによって使われます。

`partial_view_class` パラメータは `sfPartialView` クラスを継承するビュークラスを定義します (サフィックスの `PartialView` はつきません)。このクラスはモジュールのパーシャルによって使われます。
