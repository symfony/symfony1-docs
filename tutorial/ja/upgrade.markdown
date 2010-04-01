プロジェクトを 1.2 から 1.3/1.4 にアップグレードする
=======================================================

このドキュメントでは symfony 1.3/1.4 で行われた変更と symfony 1.2 のプロジェクトをアップグレードするために必要な作業を説明します。

symfony 1.3 で変更または追加された機能の詳細を知りたければ、[「symfony 1.3/1.4 の新しい機能」](http://www.symfony-project.org/tutorial/1_4/ja/whats-new)のチュートリアルをご覧ください。

>**CAUTION**
>symfony 1.3/1.4 と互換性のある PHP のバージョンは 5.2.4 およびそれ以降です。PHP 5.2.0 から 5.2.3 までのバージョンでも動くかもしれませんが、保証はありません。

symfony 1.4 にアップグレードする
--------------------------------

(すべての廃止予定の機能が削除されていること以外) symfony 1.4 は symfony 1.3 と同じなので、このバージョンにアップグレードするタスクはありません。symfony 1.4  にアップグレードするには、最初に symfony 1.3 にアップグレードしてから symfony 1.4 リリースに切り替えなければなりません。

symfony 1.4 にアップグレードする前に、`project:validate` タスクを実行することで廃止予定のクラス/メソッド/関数/設定などがプロジェクトで使われていないことを検証することもできます:

    $ php symfony project:validate

このタスクは symfony 1.4 に切り替える前に修正する必要のあるすべてのファイルの一覧を表示します。

このタスクに使われている正規表現はおおまかなものであり多くの誤判断をしてしまう可能性があることにご注意ください。また、このタスクは起きる可能性のある問題を特定するのを手助けするものであり、すべてのファイルを検出できる魔法の道具ではありません。「1.3 での廃止予定および削除される機能」のチュートリアルも注意深く読む必要があります。

>**NOTE**
>`sfCompat10Plugin` と `sfProtoculousPlugin` は symfony 1.4 から削除されました。`config/ProjectConfiguration.class.php` などのプロジェクトの設定ファイルでこれらのプラグインを明示的に無効にする場合、これらのファイルからプラグインの記述をすべて削除しなければなりません。

symfony 1.3 にアップグレードするには？
-------------------------------------

プロジェクトをアップグレードするには次の手順を踏みます:

  * プロジェクトで使われているすべてのプラグインが symfony 1.3 と互換性があることを確認します。

  * SCM ツールを使わない場合、かならずプロジェクトのバックアップをとります。

  * symfony を 1.3 にアップグレードします。

  * プラグインを 1.3 対応のバージョンにアップグレードします。

  * 自動アップグレードを実行するためにプロジェクトディレクトリから `project:upgrade1.3` タスクを実行します:

        $ php symfony project:upgrade1.3

    このタスクは副作用なしで複数回実行できます。新しい symfony 1.3 beta/RC もしくは最新のバージョンにアップグレードするたびに、このタスクを起動させなければなりません。

  * 下記で説明される変更のために、モデルとフォームをリビルドする必要があります:

        # Doctrine
        $ php symfony doctrine:build --all-classes

        # Propel
        $ php symfony propel:build --all-classes

  * キャッシュをクリアします:

        $ php symfony cache:clear

残りのセクションは symfony 1.3 におけるなんらかのアップグレード作業 (自動もしくはそうではない) が必要な主な変更を説明します。

廃止予定の機能
--------------

symfony 1.3 を開発しているあいだに、いくつかの設定、クラス、メソッド、関数とタスクが廃止予定もしくは削除されました。詳しい情報は[「1.3 での廃止予定および削除される機能」](http://www.symfony-project.org/tutorial/1_4/ja/deprecated)を参照してくださるようお願いします。

オートローディング
-------------------

symfony 1.3 に関して、`lib/vendor/` ディレクトリの下にあるファイルはオートロードされることはありません。`lib/vendor/` サブディレクトリをオートロードしたい場合、新しいエントリをアプリケーションの `autoload.yml` 設定ファイルに追加します:

    [yml]
    autoload:
      vendor_some_lib:
        name:      vendor_some_lib
        path:      %SF_LIB_DIR%/vendor/some_lib_dir
        recursive: on

`lib/vendor/` ディレクトリのオートロードには複数の理由から問題がありました:

  * すでにオートロードメカニズムがはたらく `lib/vendor/` ディレクトリの下でライブラリを設置する場合、symfony はファイルを再解析してたくさんの不要な情報をキャッシュに追加します (#5893 - http://trac.symfony-project.org/ticket/5893 を参照)。

  * symfony のディレクトリが `lib/vendor/symfony/` という名前でなければ何らかの問題が起こります。これはプロジェクトのオートローダが symfony ディレクトリ全体を再解析することが原因です (#6064 - http://trac.symfony-project.org/ticket/6064 を参照)。

symfony 1.3 のオートローダは大文字と小文字を区別しません。

ルーティング
------------

`sfPatternRouting::setRoutes()`、`sfPatternRouting::prependRoutes()`、
`sfPatternRouting::insertRouteBefore()` と `sfPatternRouting::connect()` メソッドは以前のバージョンのようにルートを配列として返しません。

`lazy_routes_deserialize` オプションはもはや必要ないので削除されました。

symfony 1.3 に関して、ルーティングキャッシュが無効になりました。ほとんどのプロジェクトではこれはパフォーマンスの観点からベストなオプションです。ですので、ルーティングキャッシュをカスタマイズしなければ、このオプションはすべてのアプリケーションで自動的に無効になります。symfony 1.3 にアップグレードした後でプロジェクトの動きが遅くなる場合、役に立っていることを確認するためにルーティングキャッシュを追加するとよいでしょう。symfony 1.2 のデフォルトコンフィギュレーションに戻すには次の内容を `factories.yml` に追加します:

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

`sfCommonFilter` は削除されデフォルトでは使われていません。このフィルタは JavaScript とスタイルシートのタグをレスポンスのコンテンツに自動投入していました。これらのアセットを手動でインクルードするにはレイアウトのなかで `include_stylesheets()` と `include_javascripts()` ヘルパーを明示的に呼び出すことが必要です:

    [php]
    <?php include_javascripts() ?>
    <?php include_stylesheets() ?>

共通フィルタは複数の理由から削除されました:

 * すでによりシンプルで柔軟ですぐれた解決方法があります (`include_stylesheets()` と `include_javascripts()` ヘルパー)。

 * 最初にフィルタの存在を知らなければならず「背後」のマジックがはたらいているので、フィルタを無効にするタスクは簡単ではないからです。

 * ヘルパーを使えばいつどこでアセットがレイアウトにインクルードされるのかきめ細かくコントロールできます (たとえば `head` タグのスタイルシート、`body` タグが終わる直前の JavaScript)

 * つねに暗黙よりも明示的であるほうがすぐれています (おまじないがないのでなんじゃこりゃと驚かずに済みます; この問題に対する苦情はメーリングリストを参照)。

 * 小さな速度の改善がもたらされます。

アップグレードするには？

  * すべての `filters.yml` 設定ファイルから `common` フィルタを削除する必要があります (この作業は `project:upgrade1.3` タスクによって自動的に行われます)。

  * 以前と同じふるまいを保つには `include_stylesheets()` と `include_javascripts()` 呼び出しをレイアウトに追加する必要があります (この作業はアプリケーションの `templates/` ディレクトリに収められている HTML レイアウト用の `project:upgrade1.3` タスクによって自動的に行われます - これらのレイアウトには `<head>` タグが収められていなければなりません。 ほかのレイアウト、もしくはレイアウトをもたないが JavaScript ファイルかつ/もしくスタイルシートに依存するページを手動でアップグレードする必要があります)。


>**NOTE**
>`sfCommonFilter` クラスはまだ symfony 1.3 に搭載されているので、必要があれば `filters.yml` のなかでこのクラスを使うことができます。


タスク
------

次のタスククラスは改名されました:

  symfony 1.2               | symfony 1.3
  ------------------------- | --------------------------------------------------------------------------
  `sfConfigureDatabaseTask` | `sfDoctrineConfigureDatabaseTask` もしくは `sfPropelConfigureDatabaseTask`
  `sfDoctrineLoadDataTask`  | `sfDoctrineDataLoadTask`
  `sfDoctrineDumpDataTask`  | `sfDoctrineDataDumpTask`
  `sfPropelLoadDataTask`    | `sfPropelDataLoadTask`
  `sfPropelDumpDataTask`    | `sfPropelDataDumpTask`

### フォーマッタ

`sfFormatter::format()` の3番目の引数は削除されました。

エスケーピング
---------------

`ESC_JS_NO_ENTITIES` によって参照される `esc_js_no_entities()` は ANSI ではない文字を正しく処理するように更新されました。この変更の前は ANSI の値が`37`から`177`である文字だけがエスケープされませんでした。現在はバックスラッシュ (`\`)、クォート (`'` と `"`) 、そして改行 (`\n` と `\r`) だけがエスケープされます。

Doctrine との統合
-----------------

### Doctrine の必須バージョン

Doctrine の svn:externals は最新の Doctrine 1.2 を使うように更新されました。Doctrine 1.2 の新しい機能に関しては[公式サイトの手引き](http://www.doctrine-project.org/upgrade/1_2)をご覧ください。

### アドミンジェネレータの削除機能

アドミンジェネレータバッチの削除機能はレコードをすべて削除する単独の DQL クエリを発行する代わりにレコードをフェッチしてそれぞれの個別のレコードに `delete()` メソッドを発行するように変更されました。目的はそれぞれの個別のレコードを削除するためのイベントを立ち上げるためです。

### Doctrine プラグインスキーマをオーバーライドする

ローカルスキーマで同じモデルを定義することでプラグインの YAML スキーマに収められているモデルをオーバーライドできます。たとえば `sfDoctrineGuardPlugin` で `email` カラムを `sfGuardUser` モデルに追加するには、次のコードを `config/doctrine/schema.yml` に追加します:

    sfGuardUser:
      columns:
        email:
          type: string(255)

>**NOTE**
>package オプションは Doctrine の機能で symfony プラグインのスキーマに使われます。このことはモデルのパッケージを作るために package 機能を単独で使うことができることを意味しません。この機能を使えるのは直接および symfony プラグインのなかだけです。

### クエリのロギング

Doctrine 統合ログクエリはロガーオブジェクトに直接アクセスする代わりに `sfEventDispatcher` を使うことで機能します。加えて、これらのイベントの監視対象は接続もしくはクエリを実行する命令文です。ロギングは新しい `sfDoctrineConnectionProfiler` クラスによって行われ、このクラスは `sfDoctrineDatabase` オブジェクトを通してアクセスできます。

プラグイン
----------

有効にするプラグインを `ProjectConfiguration` クラスのなかで管理するために `enableAllPluginsExcept()` メソッドを使う場合、異なるプラットフォームのあいだの一貫性を保証するためにプラグインを名前順でソートするように警告されます。

ウィジェット
------------

`sfWidgetFormInput` は抽象クラスです。テキスト入力フィールドは `sfWidgetFormInputText` クラスによって作られます。この変更はフォームクラスのイントロスペクトを簡単にするために行われました。

メーラー
--------

symfony 1.3 では新しいメーラーファクトリが用意されています。アプリケーションが作られるとき、`factories.yml` には `test` と `dev` 環境の実用的なデフォルトが収められています。しかし既存のプロジェクトをアップグレードする場合、これらの環境のために `factories.yml` のコンフィギュレーションを次のように更新するとよいでしょう:

    [yml]
    mailer:
      param:
        delivery_strategy: none

以前のコンフィギュレーションではメールは送信されません。もちろん、これらのコンフィギュレーションはまだロギングされ、`mailer` テスターは機能テストでまだ動きます。

すべてのメールを1つのメールアドレスで受信したいのであれば `single_address` 配信戦略を使います (たとえば `dev` 環境):

    [yml]
    dev:
      mailer:
        param:
          delivery_strategy: single_address
          delivery_address:  foo@example.com

>**CAUTION**
>プロジェクトで Swiftmailer の古いバージョンが使われているのであればそれを削除しなければなりません。

YAML
----

sfYAML は 1.2 の仕様とより多くの互換性をもちます。設定ファイルで変更する必要のあるものは次のとおりです:

 * ブール値は文字列の `true` もしくは `false` だけで表現されます。次のリストにある代替文字列が使われているのであれば、`true` もしくは `false` に置き換えなければなりません:

    * `on`, `y`, `yes`, `+`
    * `off`, `n`, `no`, `-`

`project:upgrade` タスクは古い構文がどこにあるのか教えてくれますが修正はしてくれませんので (たとえばあいまいなコメントを避けるため)、手作業で修正しなければなりません。

すべての YAML ファイルをチェックしたくない場合、 `sfYaml::setSpecVersion()` メソッドを使って YAML 1.1 の仕様に合わせるよう YAML パーサーに強制させることができます:

    [php]
    sfYaml::setSpecVersion('1.1');

Propel
------

symfony の以前のバージョンで使われていた Propel のカスタムビルダークラスは新しい Propel 1.4 のビヘイビアクラスに置き換わりました。この強化内容を利用するにはプロジェクトの `propel.ini` ファイルを更新しなければなりません。

古いビルダークラスを削除します:

    ; builder settings
    propel.builder.peer.class              = plugins.sfPropelPlugin.lib.builder.SfPeerBuilder
    propel.builder.object.class            = plugins.sfPropelPlugin.lib.builder.SfObjectBuilder
    propel.builder.objectstub.class        = plugins.sfPropelPlugin.lib.builder.SfExtensionObjectBuilder
    propel.builder.peerstub.class          = plugins.sfPropelPlugin.lib.builder.SfExtensionPeerBuilder
    propel.builder.objectmultiextend.class = plugins.sfPropelPlugin.lib.builder.SfMultiExtendObjectBuilder
    propel.builder.mapbuilder.class        = plugins.sfPropelPlugin.lib.builder.SfMapBuilderBuilder

そして新しいビヘイビアクラスを追加します:

    ; behaviors
    propel.behavior.default                        = symfony,symfony_i18n
    propel.behavior.symfony.class                  = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfony
    propel.behavior.symfony_i18n.class             = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18n
    propel.behavior.symfony_i18n_translation.class = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18nTranslation
    propel.behavior.symfony_behaviors.class        = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
    propel.behavior.symfony_timestampable.class    = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorTimestampable

`project:upgrade` タスクはこの変更を適用しようとしますが、`propel.ini` でローカルな変更が行われていた場合には変更を適用できないことがあります。

symfony 1.2 では `BaseFormFilterPropel` クラスは `lib/filter/base` に正しく生成されませんでしたが、symfony 1.3 で訂正され、`lib/filter` に生成されます。`project:upgrade` タスクはこのファイルを移動させます。

テスト
------

ユニットテストのブートストラップファイルである `test/bootstrap/unit.php` はプロジェクトのクラスファイルのオートロードをより上手に処理するよう強化されました。次のコードをこのスクリプトに追加しなければなりません:

    [php]
    $autoload = sfSimpleAutoload::getInstance(
      sfConfig::get('sf_cache_dir').'/project_autoload.cache');
    $autoload->loadConfiguration(sfFinder::type(
	  'file')->name('autoload.yml')->in(array(
        sfConfig::get('sf_symfony_lib_dir').'/config/config',
        sfConfig::get('sf_config_dir'),
    )));
    $autoload->register();

`project:upgrade` タスクはこの変更を適用しようとしますが、`test/bootstrap/unit.php` でローカルな変更が行われていた場合には変更を適用できないことがあります。
