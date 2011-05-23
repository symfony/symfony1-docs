プロジェクトを1.2から1.3/1.4にアップグレードする
================================================

このドキュメントでは、symfony 1.3/1.4 での変更内容と symfony 1.2 のプロジェクトをアップグレードするために必要な作業を説明します。

symfony1.3 で変更または追加された機能の詳細については、[「symfony 1.3/1.4 の新しい機能」](http://www.symfony-project.org/tutorial/1_4/ja/whats-new)のチュートリアルをご参照ください。

>**CAUTION**
>symfony 1.3/1.4 と互換性のある PHP のバージョンは5.2.4およびそれ以降です。PHP 5.2.0 から5.2.3までのバージョンでも動くかもしれませんが、保証はございません。

symfony 1.4 にアップグレードする
--------------------------------

廃止予定の機能がすべて削除されたこと以外、symfony 1.3 と symfony 1.4 は同じなので、このバージョンにアップグレードするタスクは用意されていません。symfony 1.4 にアップグレードするには、最初に symfony 1.3 にアップグレードしてから symfony 1.4 に切り替えなければなりません。

symfony 1.4 にアップグレードする前に、`project:validate` タスクを実行して、廃止予定のクラス/メソッド/関数/設定がプロジェクトで使われていないことを検証することもできます。

    $ php symfony project:validate

このタスクは symfony 1.4 に切り替える前に修正する必要のあるすべてのファイルの一覧を表示します。

このタスクに使われている正規表現はおおまかなものであり、たくさんの誤判定が起きる可能性があることをあらかじめご了承願います。また、このタスクは起きる可能性のある問題を特定するために用意されたものであり、該当するファイルをすべて検出できる魔法の道具ではありません。「1.3での廃止予定および削除される機能」のチュートリアルもじっくりお読みください。

>**NOTE**
>`sfCompat10Plugin` と `sfProtoculousPlugin` クラスは symfony 1.4 から削除されました。プロジェクトの `config/ProjectConfiguration.class.php` ファイルのなかでこれらのプラグインを無効にしている場合、プラグインの記述をすべて削除しなければなりません。

symfony 1.3 にアップグレードするには？
-------------------------------------

プロジェクトをアップグレードするには次の手順を踏みます。

  * プロジェクトで使われているすべてのプラグインが symfony 1.3 と互換性があることを確認します。

  * SCM ツールを利用していないのであれば、プロジェクトのバックアップをかならずとってください。

  * symfony を1.3にアップグレードします。

  * プラグインを1.3対応のバージョンにアップグレードします。

  * 自動アップグレードを実行するには、プロジェクトディレクトリから `project:upgrade1.3` タスクを実行します。

        $ php symfony project:upgrade1.3

    このタスクを複数回実行しても副作用はありません。新しい symfony 1.3 beta/RC もしくは最新のバージョンにアップグレードするたびに、このタスクを実行しなければなりません。

  * 下記の節で説明されている変更に対応するために、モデルとフォームを再構築する必要があります。

        # Doctrine
        $ php symfony doctrine:build --all-classes

        # Propel
        $ php symfony propel:build --all-classes

  * キャッシュをクリアします。

        $ php symfony cache:clear

残りの節では、symfony 1.3 においてなんらかのアップグレード作業 (自動もしくは手動) が必要になる変更内容を説明します。

廃止予定の機能
--------------

symfony 1.3 の開発期間の途中で、さまざまな設定、クラス、メソッド、関数とタスクが廃止予定になったもしくは削除されました。くわしい説明は[「1.3での廃止予定および削除される機能」](http://www.symfony-project.org/tutorial/1_4/ja/deprecated)をご参照ください。

オートローディング
-------------------

symfony 1.3 では、`lib/vendor/` ディレクトリの下に配置されているファイルはオートロードの対象に入っていません。`lib/vendor/` サブディレクトリをオートロードの対象に加えるには、新しいエントリをアプリケーションの `autoload.yml` ファイルに登録します。

    [yml]
    autoload:
      vendor_some_lib:
        path:      %SF_LIB_DIR%/vendor/some_lib_dir
        recursive: on

複数の理由から `lib/vendor/` ディレクトリのオートロードには問題がありました。

  * すでにオートロードのメカニズムがはたらいている `lib/vendor/` ディレクトリにライブラリを配置する場合、symfony はファイルを再びパースして、たくさんの不要な情報をキャッシュにつけ加えてしまいます (#5893 - http://trac.symfony-project.org/ticket/5893 をご参照ください)。

  * symfony のパスが `lib/vendor/symfony/` でなければ、何らかの問題が起きます。これはプロジェクトのオートローダが symfony ディレクトリ全体を再びパースすることが原因です (#6064 - http://trac.symfony-project.org/ticket/6064 をご参照ください)。

symfony 1.3 のオートローダは大文字と小文字を区別しません。

ルーティング
------------

次のメソッドは以前のバージョンのようにルートを配列として返さなくなりました。

`sfPatternRouting::setRoutes()`、`sfPatternRouting::prependRoutes()`、
`sfPatternRouting::insertRouteBefore()` と `sfPatternRouting::connect()` 

不要になった `lazy_routes_deserialize` オプションは削除されました。

ルーティングキャッシュは無効になりました。たいていのプロジェクトでは、これはパフォーマンスの観点から最善の選択です。ですので、ルーティングキャッシュをカスタマイズしていなければ、すべてのアプリケーションでこのオプションは自動的に無効になります。symfony 1.3 にアップグレードした後でアプリケーションの動きが遅くなる場合、役に立っていることを確認するには、ルーティングキャッシュを追加するとよいでしょう。symfony 1.2 のデフォルトコンフィギュレーションに戻すには、次の内容を `factories.yml` ファイルに書き加えます。

    [yml]
    routing:
      param:
        cache:
          class: sfFileCache
          param:
            automatic_cleaning_factor: 0
            cache_dir:                 %SF_CONFIG_CACHE_DIR%/routing
            lifetime:                  31556926
            prefix:                    %SF_APP_DIR%/routing

JavaScript とスタイルシート
--------------------------

### 共通フィルタの削除

デフォルトで使われなくなった `sfCommonFilter` クラスは symfony 1.4 で削除されました。共通フィルタは JavaScript とスタイルシートのタグをレスポンスのコンテンツに自動投入していました。今後、これらのアセットをレスポンスに含めるには、レイアウトのなかで `include_stylesheets()` と `include_javascripts()` ヘルパーを明確に呼び出すことが必要になります。

    [php]
    <?php include_javascripts() ?>
    <?php include_stylesheets() ?>

共通フィルタは複数の理由から削除されました。

 * すでにもっとシンプルで柔軟な解決策があります (`include_stylesheets()` と `include_javascripts()` ヘルパー)。

 * フィルタの存在をあらかじめ知っていなければ、フィルタが問題の原因になっていることに気づきにくいからです。

 * ヘルパーを使えば、レイアウトのなかでいつどこでアセットがインクルードされるのか、きめ細かくコントロールできます (たとえば、`head` タグのスタイルシート、`body` タグが終わる直前の JavaScript)

 * 暗黙の了解を明文化することはつねによいことです (おまじないがないのでなんじゃこりゃぁあと叫ぶ事態に陥らずに済みます。この問題に関する苦情はメーリングリストをご参照ください)。

 * 処理速度が少し改善されます。

アップグレードするには？

  * すべての `filters.yml` ファイルから `common` フィルタを削除する必要があります (この作業は `project:upgrade1.3` タスクによって自動的におこなわれます)。

  * 以前と同じふるまいを保つには、`include_stylesheets()` と `include_javascripts()` ヘルパーの呼び出しをレイアウトに追加する必要があります (アプリケーションの `templates/` ディレクトリに収められている HTML レイアウトを修正する作業は `project:upgrade1.3` タスクによって自動的に行われます。これらのレイアウトには、`<head>` タグを入れなければなりません。ほかのレイアウト、もしくはレイアウトをもたないが JavaScript ファイルかつ/もしくスタイルシートに依存するページは、手作業で更新する必要があります)。


>**NOTE**
>`sfCommonFilter` クラスはまだ symfony 1.3 に搭載されているので、必要であれば、`filters.yml` ファイルのなかでこのクラスを使うことができます。


タスク
------

次のタスククラスは改名されました。

  symfony 1.2               | symfony 1.3
  ------------------------- | --------------------------------------------------------------------------
  `sfConfigureDatabaseTask` | `sfDoctrineConfigureDatabaseTask` もしくは `sfPropelConfigureDatabaseTask`
  `sfDoctrineLoadDataTask`  | `sfDoctrineDataLoadTask`
  `sfDoctrineDumpDataTask`  | `sfDoctrineDataDumpTask`
  `sfPropelLoadDataTask`    | `sfPropelDataLoadTask`
  `sfPropelDumpDataTask`    | `sfPropelDataDumpTask`

### フォーマッタ

`sfFormatter::format()` メソッドの第3引数は削除されました。

エスケーピング
---------------

`ESC_JS_NO_ENTITIES` によって参照される `esc_js_no_entities()` ヘルパーは修正され、ANSI ではない文字を正しく処理するようになりました。この変更の前は ANSI の値が`37`から`177`である文字だけがエスケープされませんでした。現在はバックスラッシュ (`\`)、クォート (`'` と `"`) 、そして改行 (`\n` と `\r`) だけがエスケープされます。

Doctrine との統合
-----------------

### Doctrine の必須バージョン

Doctrine の svn:externals が更新され、最新の Doctrine 1.2 が使われるようになりました。Doctrine 1.2 の新しい機能については[公式サイトの手引き](http://www.doctrine-project.org/upgrade/1_2)をご参照ください。

### アドミンジェネレータの削除機能

アドミンジェネレータバッチの削除機能が変更され、レコードをすべて削除する単独の DQL クエリを発行する代わりに、レコードをフェッチして個別のレコードに `delete()` メソッドを発行するようになりました。個別のレコードを削除する際にイベントを起動できるようにするためです。

### Doctrine プラグインのスキーマをオーバーライドする

ローカルスキーマのなかで同じモデルを定義することで、プラグインの YAML スキーマに記述されたモデルをオーバーライドできます。たとえば `sfDoctrineGuardPlugin` の `sfGuardUser` モデルに `email` カラムを追加するには、`config/doctrine/schema.yml` ファイルに次のコードを書き加えます。

    sfGuardUser:
      columns:
        email:
          type: string(255)

>**NOTE**
>package オプションは Doctrine の機能で symfony プラグインのスキーマに使われます。このことは、モデルのパッケージを作るために package 機能を単独で使えることを意味しません。この機能を使える場所は直接か symfony のプラグインのなかにかぎられています。

### クエリのロギング

Doctrine の統合ログクエリを実行するには、ロガーオブジェクトに直接アクセスする代わりに `sfEventDispatcher` オブジェクトを使います。さらに、これらのイベントの監視対象はコネクション、もしくはクエリを実行するステートメントです。ロギングは新たに導入された `sfDoctrineConnectionProfiler` クラスが担い、`sfDoctrineDatabase` オブジェクトを通じてこのクラスにアクセスできます。

プラグイン
----------

有効にするプラグインを `ProjectConfiguration` クラスのなかで管理するために `enableAllPluginsExcept()` メソッドを使う場合、異なるプラットフォームのあいだの一貫性を保証するためにプラグインを名前順でソートするように警告されます。

ウィジェット
------------

`sfWidgetFormInput` は抽象クラスに変更されました。また、テキスト入力フィールドは `sfWidgetFormInputText` クラスによって生成されるようになりました。これらの変更によってフォームクラスのイントロスペクションを実行しやすくなりました。

メーラー
--------

新しいメーラーファクトリが導入されました。新しいアプリケーションの `factories.yml` ファイルには、`test` と `dev` 環境の実用的なデフォルトが用意されています。既存のプロジェクトをアップグレードする場合、これらの環境に対応させるために、`factories.yml` ファイルのコンフィギュレーションを次のように更新するとよいでしょう。

    [yml]
    mailer:
      param:
        delivery_strategy: none

以前のコンフィギュレーションでは、メールは送信されません。もちろん、これらのコンフィギュレーションはロギングされ、`mailer` テスターは機能テストで動きます。

すべてのメールを1つのメールアドレスで受信するには、`single_address` デリバリストラテジを選びます。たとえば `dev` 環境のコンフィギュレーションは次のようになります。

    [yml]
    dev:
      mailer:
        param:
          delivery_strategy: single_address
          delivery_address:  foo@example.com

>**CAUTION**
>Swift Mailer の古いバージョンがプロジェクトで使われているのであれば削除しなければなりません。

YAML
----

sfYAML は YAML 1.2 の仕様とより多くの互換性をもつようになりました。設定ファイルのなかで修正する必要のあるものは次のとおりです。

 * ブール値をあらわす文字列リテラルは `true` と `false` にかぎられています。次の一覧に入っている代替文字列が使われているのであれば、`true` もしくは `false` に置き換えなければなりません。

    * `on`, `y`, `yes`, `+`
    * `off`, `n`, `no`, `-`

`project:upgrade` タスクは古い代替文字列がどこにあるのか教えてくれますが、修正はしてくれませんので、手作業で修正しなければなりません (たとえばコメントを誤って処理しないようにするため)。

すべての YAML ファイルをチェックしたくなければ、`sfYaml::setSpecVersion()` メソッドを使うことで、YAML パーサーに YAML 1.1 の仕様に準拠するよう強制させることができます。

    [php]
    sfYaml::setSpecVersion('1.1');

Propel
------

symfony の以前のバージョンで使われていた Propel 専用のビルダークラスは Propel 1.4 の新しいビヘイビアクラスに置き換わりました。この強化内容を利用するには、プロジェクトの `propel.ini` ファイルを更新しなければなりません。

古いビルダークラスを削除します。

    ; builder settings
    propel.builder.peer.class              = plugins.sfPropelPlugin.lib.builder.SfPeerBuilder
    propel.builder.object.class            = plugins.sfPropelPlugin.lib.builder.SfObjectBuilder
    propel.builder.objectstub.class        = plugins.sfPropelPlugin.lib.builder.SfExtensionObjectBuilder
    propel.builder.peerstub.class          = plugins.sfPropelPlugin.lib.builder.SfExtensionPeerBuilder
    propel.builder.objectmultiextend.class = plugins.sfPropelPlugin.lib.builder.SfMultiExtendObjectBuilder
    propel.builder.mapbuilder.class        = plugins.sfPropelPlugin.lib.builder.SfMapBuilderBuilder

そして新しいビヘイビアクラスを追加します。

    ; behaviors
    propel.behavior.default                        = symfony,symfony_i18n
    propel.behavior.symfony.class                  = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfony
    propel.behavior.symfony_i18n.class             = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18n
    propel.behavior.symfony_i18n_translation.class = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18nTranslation
    propel.behavior.symfony_behaviors.class        = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
    propel.behavior.symfony_timestampable.class    = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorTimestampable

`project:upgrade` タスクはこの変更を適用しようとしますが、`propel.ini` ファイルでローカルな変更がおこなわれていた場合には変更を適用できないことがあります。

symfony 1.2 では、`BaseFormFilterPropel` クラスは `lib/filter/base/` ディレクトリに正しく生成されませんでしたが、symfony 1.3 で訂正され、`lib/filter/` ディレクトリに生成されるようになりました。`project:upgrade` タスクはこのファイルを移動させます。

テスト
------

ユニットテストのブートストラップファイルである `test/bootstrap/unit.php` はプロジェクトのクラスファイルのオートロードをより適切に処理するように強化されました。このスクリプトに次のコードを書き加えなければなりません。

    [php]
    $autoload = sfSimpleAutoload::getInstance(
      sfConfig::get('sf_cache_dir').'/project_autoload.cache');
    $autoload->loadConfiguration(sfFinder::type(
	  'file')->name('autoload.yml')->in(array(
        sfConfig::get('sf_symfony_lib_dir').'/config/config',
        sfConfig::get('sf_config_dir'),
    )));
    $autoload->register();

`project:upgrade` タスクはこの変更を適用しようとしますが、`test/bootstrap/unit.php` ファイルのなかでローカルな変更がおこなわれていた場合には変更を適用できないことがあります。
