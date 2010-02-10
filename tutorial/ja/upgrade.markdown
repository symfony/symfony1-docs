プロジェクトを 1.2 から 1.3/1.4 にアップグレードする
===================================================

このドキュメントでは symfony 1.3/1.4 で行われた変更と 1.2 のプロジェクトをアップグレードするために必要な作業を説明します。

symfony 1.3 で変更または追加された機能の詳細を知りたければ、[「symfony 1.3/1.4 の新しい機能」](http://www.symfony-project.org/tutorial/1_4/ja/whats-new)のチュートリアルをご覧ください。

>**CAUTION**
>symfony 1.3/1.4 は PHP 5.2.4 およびそれ以降のバージョンと互換性があります。PHP 5.2.0 から 5.2.3 までのバージョンでも動作するかもしれませんが、保証されません。

symfony 1.4 にアップグレードする
-------------------------------

(すべての廃止予定の機能が取り除かれていること以外) symfony 1.4 は symfony 1.3 と同じなので、このバージョンにアップグレードするタスクはありません。1.4 にアップグレードするには、最初に 1.3 にアップグレードしてから 1.4 リリースに切り替えなければなりません。

1.4 にアップグレードする前に、`project:validate` タスクを実行することで廃止予定のクラス/メソッド/関数/設定などがプロジェクトで使われてないことを検証することもできます:

    $ php symfony project:validate

このタスクは symfony 1.4 に切り替える前に変更する必要のあるすべてのファイルの一覧を表示します。

このタスクが多くの誤判断をしてしまう可能性のある見せかけの正規表現であることにご注意ください。また、このタスクはすべてを検出できるものではなく、起こりうる問題を特定するのを手助けするものであり、魔法の道具ではありません。「1.3 の廃止予定および削除される機能」のチュートリアルも注意深く読む必要があります。

>**NOTE**
>`sfCompat10Plugin` と `sfProtoculousPlugin` は 1.4 から削除されました。`config/ProjectConfiguration.class.php` などのプロジェクトの設定ファイルでこれらを明示的に無効にする場合、これらのファイルからこれらの記述をすべて削除しなければなりません。

symfony 1.3 にアップグレードするには？
-------------------------------------

プロジェクトをアップグレードするには次の手順を踏みます:

  * プロジェクトで使われているすべてのプラグインが symfony 1.3 と互換性があることを確認します。

  * SCM ツールを使わない場合、かならずプロジェクトのバックアップをとります。

  * symfony を 1.3 にアップグレードします。

  * プラグインを 1.3 対応のバージョンにアップグレードします。

  * 自動アップグレードを実行するためにプロジェクトディレクトリから `project:upgrade1.3` タスクを立ち上げます:

        $ php symfony project:upgrade1.3

    このタスクは副作用なしで複数回立ち上げることができます。新しい symfony 1.3 beta/RC もしくは最新のバージョンにアップグレードするたびに、このタスクを起動させなければなりません。

  * 下記で説明される変更のために、モデルとフォームをリビルドする必要があります:

        # Doctrine
        $ php symfony doctrine:build --all-classes

        # Propel
        $ php symfony propel:build --all-classes

  * キャッシュをクリアします:

        $ php symfony cache:clear

残りのセクションは symfony 1.3 で行われなんらかのアップグレード (自動もしくはそうではない) が必要な主要な変更を説明します。

廃止予定
--------

symfony 1.3 を開発しているあいだに、いくつかの設定、クラス、メソッド、関数とタスクが廃止予定になるもしくは削除されてきました。詳細な情報は[「1.3での廃止予定および削除される機能」](http://www.symfony-project.org/tutorial/1_4/ja/deprecated)を参照してくださるようお願いします。

オートロード機能
----------------

symfony 1.3 に関しては、`lib/vendor/` ディレクトリの下にあるファイルはオートロードされることはありません。`lib/vendor/` サブディレクトリをオートロードしたい場合、新しいエントリをアプリケーションの `autoload.yml` 設定ファイルに追加します:

    [yml]
    autoload:
      vendor_some_lib:
        name:      vendor_some_lib
        path:      %SF_LIB_DIR%/vendor/some_lib_dir
        recursive: on

`lib/vendor/` ディレクトリのオートロードには複数の理由から問題がありました:

  * すでにオートロードメカニズムがはたらく `lib/vendor/` ディレクトリの下でライブラリを設置する場合、symfony はファイルを再解析してキャッシュに不要なたくさんの情報を追加します (#5893 - http://trac.symfony-project.org/ticket/5893 を参照してください)。

  * symfony のディレクトリが `lib/vendor/symfony/` という名前でなければ、プロジェクトのオートローダーは symfony ディレクトリ全体を再解析することが原因で何らかの問題が起こります (#6064 - http://trac.symfony-project.org/ticket/6064 を参照してください)。

symfony 1.3 のオートロード機能は大文字と小文字を区別しません。

ルーティング
------------

`sfPatternRouting::setRoutes()`、`sfPatternRouting::prependRoutes()`、`sfPatternRouting::insertRouteBefore()` と `sfPatternRouting::connect()` メソッドは以前のバージョンのようにルートを配列として返しません。

`lazy_routes_deserialize` オプションはもはや必要ないので削除されました。

symfony 1.3 に関しては、ルーティングキャッシュが無効になりました。これはパフォーマンスの観点からたいていのプロジェクトにはベストなオプションです。ですので、ルーティングキャッシュをカスタマイズしなければ、これはすべてのアプリケーションで自動的に無効になります。1.3 にアップグレードした後で、プロジェクトの動作が遅くなる場合、役に立っているのか確認するためにルーティングキャッシュを追加するとよいでしょう。symfony 1.2 のデフォルトコンフィギュレーションに戻すために `factories.yml` に追加する内容は次の通りです:

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

### 共通フィルターの削除

`sfCommonFilter` は削除されデフォルトでは使われていません。このフィルターは自動的に JavaScript とスタイルシートのタグをレスポンスのコンテンツに注入していました。レイアウトのなかで `include_stylesheets()` と `include_javascripts()` ヘルパーを明示的に呼び出すことでこれらのアセットを手動でインクルードする必要があります:

    [php]
    <?php include_javascripts() ?>
    <?php include_stylesheets() ?>

これはいくつかの理由から削除されました:

 * すでにより優れた、シンプルで、より柔軟な解決方法があります (`include_stylesheets()` と `include_javascripts()` ヘルパー)。

 * フィルターが簡単に無効にできるとしても、最初に存在を知らなければならず"背後"のマジックがはたらいているのでこれは簡単なタスクではありません。

 * ヘルパーを使えばいつどこでアセットがレイアウトにインクルードされるのかよりきめ細かくコントロールできます (たとえば `head` タグのスタイルシート、`body` タグが終わる直前の JavaScript)

 * つねに暗黙よりも明示的であるほうが優れています (おまじないがないのでなんじゃこりゃと驚かずに済みます; この問題に対する苦情はメーリングリストを参照してください)。

 * これは速度の小さな改善を提供します。

アップグレードするには？

  * すべての `filters.yml` 設定ファイルから `common` フィルターを削除する必要があります (これは `project:upgrade1.3` タスクによって自動的に行われます)。

  * 以前と同じふるまいを保つには `include_stylesheets()` と `include_javascripts()` 呼び出しをレイアウトに追加する必要があります (これはアプリケーションの `templates/` ディレクトリに収められている HTML レイアウト用の `project:upgrade1.3` タスクによって自動的に行われます - これらは `<head>` タグを持たなければなりません; ほかのレイアウト、もしくはレイアウトを持たないが JavaScript ファイルかつ/もしくスタイルシートに依存するページを手動でアップグレードする必要があります)。


>**NOTE**
>`sfCommonFilter` クラスはまだ symfony 1.3 に搭載されているので、必要であれば `filters.yml` のなかでこれを使うことができます。


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

### フォーマッター

`sfFormatter::format()` の 3 番目の引数は削除されました。

エスケーピング
-------------

`ESC_JS_NO_ENTITIES` によって参照される `esc_js_no_entities()` は ANSI ではない文字を正しく処理するように更新されました。この変更の前は ANSI の値が `37` から `177` である文字のみがエスケープされませんでした。現在はバックスラッシュ (`\`)、クォート (`'` と `"`) 、そして改行 (`\n` と `\r`) のみをエスケープします。

Doctrine との統合
-----------------

### Doctrine の必須バージョン

Doctrine の svn:externals は最新の Doctrine 1.2 を使うように更新されました。Doctrine 1.2 の新しい機能に関しては[公式サイトの手引き](http://www.doctrine-project.org/upgrade/1_2)をご覧ください。

### アドミンジェネレーターの削除機能

アドミンジェネレーターバッチの削除機能はレコードをすべて削除する単独の DQL クエリを発行する代わりにレコードをフェッチしてそれぞれの個別のレコードに `delete()` メソッドを発行するように変更されました。それぞれの個別のレコードを削除するためのイベントを起動させるためです。

### Doctrine プラグインスキーマをオーバーライドする

ローカルスキーマで同じモデルを定義することでプラグインの YAML スキーマに含まれるモデルをオーバーライドできます。たとえば、`email` カラムを `sfDoctrineGuardPlugin` の `sfGuardUser` モデルに追加するには、次のコードを `config/doctrine/schema.yml` に追加します:

    sfGuardUser:
      columns:
        email:
          type: string(255)

>**NOTE**
>package オプションは Doctrine の機能で symfony プラグインのスキーマに使われます。package 機能はモデルのパッケージを作成するために独立して使うことができることを意味しません。これは直接および symfony プラグインでのみ使わなければなりません。

### クエリのロギング

Doctrine 統合ログクエリはロガーオブジェクトに直接アクセスする代わりに `sfEventDispatcher` を使うことで機能します。加えて、これらのイベントの監視対象は接続もしくはクエリを実行するステートメントです。ロギングは新しい `sfDoctrineConnectionProfiler` クラスによって行われ、このクラスは `sfDoctrineDatabase` オブジェクトを通してアクセスできます。

プラグイン
----------

`ProjectConfiguration` クラスで有効にされるプラグインを管理するために `enableAllPluginsExcept()` メソッドを使う場合、異なるプラットフォームのあいだの一貫性を保証するためにプラグインを名前順でソートするように警告されます。

ウィジェット
------------

`sfWidgetFormInput` は抽象クラスです。テキスト入力フィールドは `sfWidgetFormInputText` クラスによって作られます。この変更はフォームクラスのイントロスペクトを楽にするために行われました。

メーラー
--------

symfony 1.3 は新しいメーラーファクトリを備えています。アプリケーションを作るとき、`factories.yml` は `test` と `dev` 環境の実用的なデフォルトを持ちます。しかし既存のプロジェクトをアップグレードする場合、これらの環境のために `factories.yml` を次のコンフィギュレーションに更新するとよいでしょう:

    [yml]
    mailer:
      param:
        delivery_strategy: none

以前のコンフィギュレーションでは、メールは送信されません。もちろん、これらはまだロギングされ、`mailer` テスターは機能テストでまだ動きます。

1 つのアドレスですべてのメールを受信したいのであれば `single_address` 配信戦略を使います (たとえば `dev` 環境):

    [yml]
    dev:
      mailer:
        param:
          delivery_strategy: single_address
          delivery_address:  foo@example.com

YAML
----

sfYAML は 1.2 の仕様とより互換性を持ちます。設定ファイルで変更する必要のあるものは次の通りです:

 * ブール値は文字列の `true` もしくは `false` でのみ表現されます。次のリストのなかの代替文字列を使っている場合、これらを `true` もしくは `false` に置き換えなければなりません:

    * `on`, `y`, `yes`, `+`
    * `off`, `n`, `no`, `-`

`project:upgrade` タスクは古い構文がどこにあるのか教えてくれますが修正はしません (たとえばあいまいなコメントを避けるため)。これらを手作業で修正しなければなりません。

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

`project:upgrade` タスクはこの変更を行おうとしますが、`propel.ini` でローカルな変更を行う場合、不可能です。

symfony 1.2 では `BaseFormFilterPropel` クラスは `lib/filter/base` に正しく生成されませんでしたが、symfony 1.3 で訂正されました; クラスは `lib/filter` に生成されます。`project:upgrade` タスクはこのファイルを移動させます。

テスト
------

ユニットテストのブートストラップファイルである `test/bootstrap/unit.php` はプロジェクトのクラスファイルのオートロードをよりうまく処理するよう強化されました。次のコードをこのスクリプトに追加しなければなりません:

    [php]
    $autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
    $autoload->loadConfiguration(sfFinder::type('file')->name('autoload.yml')->in(array(
      sfConfig::get('sf_symfony_lib_dir').'/config/config',
      sfConfig::get('sf_config_dir'),
    )));
    $autoload->register();

`project:upgrade` タスクはこの変更を行おうとしますが、`test/bootstrap/unit.php` でローカルな変更をすることが不可能な場合があります。
