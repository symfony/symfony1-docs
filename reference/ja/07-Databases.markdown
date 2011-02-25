databases.yml 設定ファイル
==========================

データベースコネクション (接続) のコンフィギュレーションは ~`databases.yml`~ ファイルのなかで変更できます。この設定ファイルは symfony に搭載されている ORM の Propel と Doctrine の両方で使われます。

プロジェクトのメイン設定ファイルである `databases.yml` ファイルは `config/` ディレクトリに配置されています。

>**NOTE**
>たいていのプロジェクトにおいて、すべてのアプリケーションは同じデータベースを共有しています。このことがデータベースのメイン設定ファイルがプロジェクトの `config/` ディレクトリに配置されている理由です。もちろん、`databases.yml` ファイルをアプリケーションの `config/` ディレクトリに配置すれば、デフォルトコンフィギュレーションをオーバーライドできます。

[設定ファイルの原則の章](#chapter_03)で述べたように、`databases.yml` ファイルでは、**環境**が認識され、**コンフィギュレーションカスケード**のメカニズムがはたらいており、**定数**を定義することができます。

`databases.yml` ファイルのなかのそれぞれのコネクションにおいて、データベースオブジェクトの初期化に使われる名前、データベースハンドラクラスの名前、パラメータ (`param`) をセットしなければなりません。

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

`class` クラスは `sfDatabase` 基底クラスを継承しなければなりません。

データベースハンドラクラスがオートロードされていなければ、ファクトリが作られる前に `file` パスが定義され、自動的にインクルードされます。

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>`databases.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は ~`sfDatabaseConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)にゆだねられます。

-

>**TIP**
>データベースコンフィギュレーションの変更は `database:configure` タスクでも実行することができます。このタスクは引数に渡された値に応じて `databases.yml` ファイルを更新します。

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

次のパラメータは `param` セクションのなかでカスタマイズできます。

 | キー         | 説明                        | デフォルト    |
 | ------------ | ----------------------------| -------------- |
 | `classname`  | Propel のアダプタクラス    | `PropelPDO`    |
 | `dsn`        | PDO の DSN (必須)            | -              |
 | `username`   | データベースのユーザー名     | -              |
 | `password`   | データベースのパスワード     | -              |
 | `pooling`    | プーリングを有効にするか     | `true`         |
 | `encoding`   | デフォルトのエンコーディング | `utf8`         |
 | `persistent` | 永続的なコネクションを作成するか | `false`    |
 | `options`    | Propel オプションのセット    | -              |
 | `debug`      | `DebugPDO` クラスのオプション| n/a            |

`debug` エントリでは Propel の[ドキュメント](http://www.propelorm.org/docs/api/1.4/runtime/propel-util/DebugPDO.html#class_details)に記載されているすべてのオプションを定義できます。次の YAML コードは利用可能なオプションを示しています。

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

次のパラメータは `param` セクションのなかでカスタマイズできます。

 | キー         | 説明                        | デフォルト |
 | ------------ | --------------------------- | ------------ |
 | `dsn`        | PDO の DSN (必須)           | -            |
 | `username`   | データベースのユーザー名     | -            |
 | `password`   | データベースのパスワード     | -            |
 | `encoding`   | デフォルトのエンコーディング | `utf8`      |
 | `attributes` | Doctrine 属性のセット       | -            |

次の属性は `attributes` セクションのなかでカスタマイズできます。

 | キー               | 説明                                   | デフォルト |
 | ------------------ | -------------------------------------- | ------------ |
 | `quote_identifier` | 識別子をクォートで囲むか                | `false`      |
 | `use_native_enum`  | ネイティブの列挙型を使うか              | `false`      |
 | `validate`         | データバリデーションを有効にするかどうか | `true`      |
 | `idxname_format`   | インデックス名のフォーマット            | `%s_idx`     |
 | `seqname_format`   | シーケンス名のフォーマット              | `%s_seq`     |
 | `tblname_format`   | テーブル名のフォーマット                | `%s`         |
 
 
>**TIP**
>*訳注*: `attributes` セクションのなかで文字集合と照合順序 (`default_table_charset` と `default_table_collate`) および MySQL を利用している場合はストレージエンジンのデフォルトもカスタマイズできます (`default_table_type`)。
