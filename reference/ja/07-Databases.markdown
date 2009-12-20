databases.yml 設定ファイル
==========================

~`databases.yml`~ はデータベース接続のコンフィギュレーションを可能にします。これは symfony に搭載されている ORM である Propel と Doctrine の両方で使われます。

プロジェクトのメインの `databases.yml` 設定ファイルは `config/` ディレクトリで見つかります。

>**NOTE**
>たいていの場合、プロジェクトのすべてのアプリケーションは同じデータベースをを共有します。そのことがメインデータベース設定ファイルがプロジェクトの `config/` ディレクトリにある理由です。もちろんアプリケーションの `config` ディレクトリで `databases.yml` 設定ファイルを定義することでデフォルトコンフィギュレーションをオーバーライドできます。

第3章で説明したように、`databases.yml` ファイルは[**環境を認識し**](#chapter_03_environment_awareness)、[**コンフィギュレーションカスケードのメカニズム**](#chapter_03_configuration_cascade)が有効になり、[**定数**](#chapter_03_constants)を収めることができます。

`databases.yml` で説明されるそれぞれの接続はデータベースオブジェクトを設定するために使う名前、データベースハンドラクラスの名前、パラメーター (`param`) の設定を収めなければなりません:

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

`class` クラスは `sfDatabase` 基底クラスを継承します。

データベースハンドラクラスをオートロードできない場合、`file` パスを定義することでファクトリーが作成される前に自動的に含めることができます:

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>`databases.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; プロセスは ~`sfDatabaseConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

-

>**TIP**
>データベースの設定は `database:configure` タスクを使うことでも設定できます。このタスクは渡される引数に従って `databases.yml` を更新します。

Propel
------

*デフォルトコンフィギュレーション*:

    [yml]
    dev:
      propel:
        param:
          classname:  DebugPDO
          debug:
            realmemoryusage: true
            details:
              time:       { enabled: true }
              slow:       { enabled: true, threshold: 0.1 }
              mem:        { enabled: true }
              mempeak:    { enabled: true }
              memdelta:   { enabled: true }

    test:
      propel:
        param:
          classname:  DebugPDO

    all:
      propel:
        class:        sfPropelDatabase
        param:
          classname:  PropelPDO
          dsn:        mysql:dbname=##PROJECT_NAME##;host=localhost
          username:   root
          password:   
          encoding:   utf8
          persistent: true
          pooling:    true

次のパラメーターは `param` セクションの下で定義できます:

 | キー         | 説明                        | デフォルト値    |
 | ------------ | ----------------------------| -------------- |
 | `classname`  | Propel のアダプタークラス    | `PropelPDO`    |
 | `dsn`        | PDO の DSN (必須)           | -              |
 | `username`   | データベースのユーザー名     | -              |
 | `password`   | データベースのパスワード     | -              |
 | `pooling`    | プーリングを有効にするか     | `true`         |
 | `encoding`   | デフォルトの文字集合         | `UTF-8`        |
 | `persistent` | 永続的接続を作成するか       | `false`        |
 | `options`    | Propel オプションのセット    | -              |
 | `options`    | Propel オプションのセット    | -              |
 | `debug`      | `DebugPDO` クラスのオプション| n/a            |

Doctrine
--------

*デフォルトコンフィギュレーション*:

    [yml]
    all:
      doctrine:
        class:        sfDoctrineDatabase
        param:
          dsn:        mysql:dbname=##PROJECT_NAME##;host=localhost
          username:   root
          password:   
          attributes:
            quote_identifier: false
            use_native_enum: false
            validate: all
            idxname_format: %s_idx
            seqname_format: %s_seq
            tblname_format: %s

次のパラメーターは`param`セクションの下でカスタマイズできます:

 | キー         | 説明                        | デフォルト値 |
 | ------------ | --------------------------- | ------------ |
 | `dsn`        | PDO の DSN (必須)           | -            |
 | `username`   | データベースのユーザー名     | -            |
 | `password`   | データベースのパスワード     | -            |
 | `encoding`   | デフォルトのエンコーディング | `UTF-8`      |
 | `attributes` | Doctrine 属性のセット       | -            |

次の属性は`attributes`セクションの下でカスタマイズできます:

 | キー               | 説明                                   | デフォルト値 |
 | ------------------ | -------------------------------------- | ------------ |
 | `quote_identifier` | 識別子をクォートでラップするか          | `false`      |
 | `use_native_enum`  | ネイティブの列挙型を使うか              | `false`      |
 | `validate`         | データバリデーションを有効にするかどうか | `true`       |
 | `idxname_format`   | インデックス名のフォーマット            | `%s_idx`     |
 | `seqname_format`   | シーケンス名のフォーマット              | `%s_seq`     |
 | `tblname_format`   | テーブル名のフォーマット                | `%s`         |
