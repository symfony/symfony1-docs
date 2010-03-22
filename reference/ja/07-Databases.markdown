databases.yml 設定ファイル
==========================

~`databases.yml`~ はデータベース接続のコンフィギュレーションの変更を可能にします。これは symfony に搭載される ORM である Propel と Doctrine の両方で使われます。

プロジェクトのメイン設定ファイルの `databases.yml` は `config/` ディレクトリで見つかります。

>**NOTE**
>ほとんどの場合、プロジェクトのすべてのアプリケーションは同じデータベースを共有します。これがデータベースのメイン設定ファイルがプロジェクトの `config/` ディレクトリに存在する理由です。もちろんアプリケーションの `config` ディレクトリで `databases.yml` 設定ファイルを定義することでデフォルトコンフィギュレーションをオーバーライドできます。

第3章で説明したように、`databases.yml` ファイルでは[**環境が認識され**](#chapter_03)、[**コンフィギュレーションカスケードのメカニズム**](#chapter_03)がはたらき、[**定数**](#chapter_03)を収めることができます。

`databases.yml` で記述されるそれぞれの接続はデータベースオブジェクトを設定するために使う名前、データベースハンドラクラスの名前、パラメータ (`param`) の設定を収めなければなりません:

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

`class` クラスは `sfDatabase` 基底クラスを継承します。

データベースハンドラクラスをオートロードできない場合、ファクトリが作られる前に `file` パスが定義され自動的にインクルードされます:

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>`databases.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は ~`sfDatabaseConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

-

>**TIP**
>`database:configure` タスクを使うことでもデータベースを設定できます。このタスクは渡される引数にしたがって `databases.yml` を更新します。

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

次のパラメータは `param` セクションの下でカスタマイズできます:

 | キー         | 説明                        | 既定値    |
 | ------------ | ----------------------------| -------------- |
 | `classname`  | Propel のアダプタクラス    | `PropelPDO`    |
 | `dsn`        | PDO の DSN (必須)            | -              |
 | `username`   | データベースのユーザー名     | -              |
 | `password`   | データベースのパスワード     | -              |
 | `pooling`    | プーリングを有効にするか     | `true`         |
 | `encoding`   | デフォルトのエンコーディング | `utf8`        |
 | `persistent` | 永続的接続を作成するか       | `false`        |
 | `options`    | Propel オプションのセット    | -              |
 | `debug`      | `DebugPDO` クラスのオプション| n/a            |

`debug` エントリは Propel の[ドキュメント](http://propel.phpdb.org/docs/api/1.4/runtime/propel-util/DebugPDO.html#class_details)で説明されるすべてのオプションを定義します。次の YAML は利用可能なオプションを示します:

    [yml]
    debug:
      realmemoryusage: true
      details:
        time:
          enabled: true
        slow:
          enabled: true
          threshold: 0.001
        memdelta:
          enabled: true
        mempeak:
          enabled: true
        method:
          enabled: true
        mem:
          enabled: true
        querycount:
          enabled: true

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

次のパラメータは`param`セクションの下でカスタマイズできます:

 | キー         | 説明                        | 既定値 |
 | ------------ | --------------------------- | ------------ |
 | `dsn`        | PDO の DSN (必須)           | -            |
 | `username`   | データベースのユーザー名     | -            |
 | `password`   | データベースのパスワード     | -            |
 | `encoding`   | デフォルトのエンコーディング | `utf8`      |
 | `attributes` | Doctrine 属性のセット       | -            |

次の属性は `attributes` セクションの下でカスタマイズできます:

 | キー               | 説明                                   | 既定値 |
 | ------------------ | -------------------------------------- | ------------ |
 | `quote_identifier` | 識別子をクォートでラップするか          | `false`      |
 | `use_native_enum`  | ネイティブの列挙型を使うか              | `false`      |
 | `validate`         | データバリデーションを有効にするかどうか | `true`       |
 | `idxname_format`   | インデックス名のフォーマット            | `%s_idx`     |
 | `seqname_format`   | シーケンス名のフォーマット              | `%s_seq`     |
 | `tblname_format`   | テーブル名のフォーマット                | `%s`         |
