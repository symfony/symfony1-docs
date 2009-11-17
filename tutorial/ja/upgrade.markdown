プロジェクトを1.2から1.3にアップグレードする
===========================================

このドキュメントではsymfony 1.3で行われた変更と1.2プロジェクトをアップグレードするために必要な作業を説明します。

symfony 1.3で変更/追加されたものの詳細を知りたければ、[What's new?](http://www.symfony-project.org/tutorial/1_3/ja/whats-new)チュートリアルをご覧ください。

>**CAUTION**
>symfony 1.3はPHP 5.2.4とそれ以降と互換性があります。
>PHP 5.2.0から5.2.3でも動作するかもしれませんが、保証はありません。

アップグレードするには？
-----------------------

プロジェクトをアップグレードするには:

  * プロジェクトで使われているすべてのプラグインがsymfony 1.3と互換性があることを確認する
    1.3

  * SCMツールを使わない場合、かならずプロジェクトのバックアップを行う

  * symfonyを1.3にアップグレードする

  * プラグインを1.3バージョンにアップグレードする

  * 自動アップグレードを実行するためにプロジェクトディレクトリから`project:upgrade1.3`タスクを立ち上げる:

        $ php symfony project:upgrade1.3

    このタスクは副作用なしで複数回立ち上げることができます。 
    新しいsymfony 1.3ベータ/RCもしくは最終版にアップグレードするたびに、このタスクを立ち上げなければなりません。

  * 下記で記述される変更のために、モデルとフォームをリビルドする必要がある:

        # Doctrine
        $ php symfony doctrine:build --all-classes

        # Propel
        $ php symfony propel:build --all-classes

  * キャッシュをクリアする:

        $ php symfony cache:clear

残りのセクションはsymfony 1.3で行われなんらかのアップグレード(自動もしくはそうではない)が必要な主要な変更を説明します。

廃止予定
--------

symfony 1.3の開発の間、設定、クラス、メソッド、関数とタスクを廃止予定にするもしくは削除してきました。
詳細な情報は[Deprecated in 1.3](http://www.symfony-project.org/tutorial/1_3/ja/deprecated)を参照してくださるようお願いします。

Autoloading
-----------

symfony 1.3に関しては、`lib/vendor/`ディレクトリの元にあるファイルはもはやオートロードされません。
`lib/vendor/`サブディレクトリをオートロードしたい場合、アプリケーションの`autoload.yml`設定ファイルに新しいエントリを追加します:

    [yml]
    autoload:
      vendor_some_lib:
        name:      vendor_some_lib
        path:      %SF_LIB_DIR%/vendor/some_lib_dir
        recursive: on

`lib/vendor/`ディレクトリの自動オートロードにはいくつかの理由から問題がありました:

  * オートロードメカニズムをすでに持つ`lib/vendor/`ディレクトリの元でライブラリを設置する場合、symfonyはファイルを再解析してキャッシュに不要なたくさんの情報を追加します(#5893 - http://trac.symfony-project.org/ticket/5893 を参照)。

  * symfonyのディレクトリが`lib/vendor/symfony/`という名前でなければ、プロジェクトのオートローダーはsymfonyディレクトリ全体を再解析し何らかの問題が起こります(#6064 - http://trac.symfony-project.org/ticket/6064を参照)。

symfony 1.3のオートロード機能は大文字と小文字を区別しません。

ルーティング
------------

`sfPatternRouting::setRoutes()`、`sfPatternRouting::prependRoutes()`、`sfPatternRouting::insertRouteBefore()`と`sfPatternRouting::connect()`メソッドは以前のバージョンのようにルートを配列として返しません。

`lazy_routes_deserialize`オプションはもはや必要ないので削除されました。

symfony 1.3に関しては、ルーティング用のキャッシュが無効されました。
パフォーマンスの観点からたいていのプロジェクトにとってこれはベストなオプションです。
ですので、ルーティングキャッシュをカスタマイズしなかった場合、これはすべてのアプリケーションに対して自動的に無効になります。
1.3にアップグレードした後で、プロジェクトの動作が遅くなる場合、役に立つのか確認するためにルーティングキャッシュを追加するとよいでしょう。
`factories.yml`に追加することでsymfony 1.2のデフォルトに戻すコンフィギュレーションは次のとおりです:

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

JavaScriptとスタイルシート
-------------------------

### 共通フィルターの削除

`sfCommonFilter`は削除されました。
このフィルターはJavaScriptとスタイルシートのタグをレスポンスのコンテンツに自動的に注入していました。
レイアウトで`include_stylesheets()`と`include_javascripts()`ヘルパーを明示的に呼び出すことでこれらのアセットを手動でインクルードする必要があります:

    [php]
    <?php include_javascripts() ?>
    <?php include_stylesheets() ?>

これはいくつかの理由から削除されました:

 * すでにより優れた、シンプルで、より柔軟な解決方法がある(`include_stylesheets()`と`include_javascripts()`ヘルパー)

 * フィルターが簡単に無効にできるとしても、最初に存在を知らなければならず"背後"のマジックがはたらいているのでこれは簡単なタスクではありません。

 * ヘルパーを使えばいつどこでアセットがレイアウトにインクルードされるのかよりもよりきめ細かくコントロールできます(例えば`head`タグのスタイルシート、`body`タグが終わる直前のJavaScript)

 * 暗黙よりも明示的であるほうが常に優れている(マジックなしとWTF効果なし; この問題に対する苦情はメーリングリストを参照)

 * これは小さな速度の改善を提供します

アップグレードするには？

  * `common`フィルターはすべての`filters.yml`から削除される必要がある
    設定ファイル(これは`project:upgrade1.3`タスクによって自動的に行われる)。

  * 以前と同じふるまいにするにはレイアウトに`include_stylesheets()`と`include_javascripts()`呼び出しを追加する必要がある(これはアプリケーションの`templates/`ディレクトリに含まれるHTML  - これらは`<head>`タグを持たなければなりません - 用の`project:upgrade1.3`タスクによって自動的に行われます; 他のレイアウト、もしくはレイアウトを持たないがJavaScriptファイルかつ/もしくスタイルシートに依存するページを手動でアップグレードする必要がある)。

タスク
-----

次のタスククラスはリネームされました:

  symfony 1.2               | symfony 1.3
  ------------------------- | --------------
  `sfConfigureDatabaseTask` | `sfDoctrineConfigureDatabaseTask`もしくは`sfPropelConfigureDatabaseTask`
  `sfDoctrineLoadDataTask`  | `sfDoctrineDataLoadTask`
  `sfDoctrineDumpDataTask`  | `sfDoctrineDataDumpTask`
  `sfPropelLoadDataTask`    | `sfPropelDataLoadTask`
  `sfPropelDumpDataTask`    | `sfPropelDataDumpTask`

### フォーマッター

`sfFormatter::format()`の3番目の引数は削除されました。

エスケーピング
-------------

`ESC_JS_NO_ENTITIES`によって参照される`esc_js_no_entities()`はANSIではない文字を正しく処理するように更新されました。
この変更の前はANSIの値が`37`から`177`である文字のみがエスケープされませんでした。
現在はバックスラッシュ `\`、クォート `'` & `"` と改行 `\n` & `\r`のみをエスケープします。

Doctrine統合
------------

### Doctrineの必須バージョン

Doctrineのexternalsは最新のDoctrine 1.2を使うように更新されました。
Doctrine 1.2の新しい機能に関しては[ここ](http://www.doctrine-project.org/upgrade/1_2)をご覧ください。

### adminジェネレータの削除機能

adminジェネレーターバッチの削除機能はレコードをすべて削除する単独のDQLクエリを発行する代わりにレコードをフェッチしてそれぞれの個別のレコードに`delete()`メソッドを発行するように変更されました。それぞれの個別のレコードを削除するためのイベントを起動させるためです。

### Doctrineプラグインスキーマをオーバーライドする

ローカルスキーマで同じモデルを定義することでプラグインのYAMLスキーマに含まれるモデルをオーバーライドできます。
たとえば、"email"カラムをsfDoctrineGuardPluginの`sfGuardUser`モデルに追加するには、次のコードを`config/doctrine/schema.yml`に追加します:

    sfGuardUser:
      columns:
        email:
          type: string(255)

>**NOTE**
>packageオプションはDoctrineの機能でsymfonyプラグインのスキーマに使われます。
>package機能はモデルのパッケージを作成するために独立して使うことができることを意味しません。
>これは直接およびsymfonyプラグインでのみ使わなければなりません。

### クエリのロギング

Doctrine統合ログクエリはロガーオブジェクトに直接アクセスするよりも`sfEventDispatcher`を使うことで機能します。 
加えて、これらのイベントのサブジェクトはコネクションもしくはクエリを実行するステートメントです。
ロギングは新しい`sfDoctrineConnectionProfiler`クラスによって行われ、このクラスは`sfDoctrineDatabase`オブジェクトをとおしてアクセスできます。

プラグイン
----------

`ProjectConfiguration`クラスで有効にされたプラグインを管理するために`enableAllPluginsExcept()`メソッドを使う場合、異なるプラットフォームの間の一貫性を保証するために名前でプラグインをソートするように警告されます。

ウィジェット
------------

`sfWidgetFormInput`は抽象クラスです。
テキスト入力フィールドは`sfWidgetFormInputText`クラスによって作られます。
この変更はフォームクラスのイントロスペクテーションを楽にするために行われました。

メーラー
--------

Symfony 1.3は新しいメーラーファクトリを持ちます。
アプリケーションを作るとき、`factories.yml`は`test`と`dev`環境用の実用的なデフォルトを持ちます。
しかし既存のプロジェクトをアップグレードする場合、これらの環境のために次のコンフィギュレーションを`factories.yml`をアップデートするとよいでしょう:

    [yml]
    mailer:
      param:
        delivery_strategy: none

以前のコンフィギュレーションによって、Eメールは送信されません。
もちろん、これらはまだロギングされ、`mailer`テスターは機能テストでまだ動きます。

1つのアドレスですべてのEメールを受け取りたいのであれば`single_address`デリバリー戦略を使います(例えば`dev`環境):

    [yml]
    dev:
      mailer:
        param:
          delivery_strategy: single_address
          delivery_address:  foo@example.com

YAML
----

sfYAMLは1.2の仕様とより互換性を持ちます。
設定ファイルで行う必要のあるかもしれない変更は次のとおりです:

 * ブール値は`true`もしくは`false`文字列でのみ表現されます。
   次のリストの中の代替文字列を使っていた場合、これらを`true`もしくは`false`に置き換えなければなりません:

    * `on`, `y`, `yes`, `+`
    * `off`, `n`, `no`, `-`

`project:upgrade`タスクは古い構文がどこにあるのか教えてくれますが修正はしません(たとえばルーズなコメントを避けるため)。
これらを手で修正しなければなりません。

すべてのYAMLファイルをチェックしたくない場合、YAML 1.1仕様に合わせるために`sfYaml::setSpecVersion()`メソッドを使ってYAMLパーサーを強制できます:

    [php]
    sfYaml::setSpecVersion('1.1');

Propel
------

symfonyの以前のバージョンで使われていたカスタムのPropelビルダークラスは新しいPropel 1.4のビヘイビアクラスに置き換えられました。 
この強化を利用するにはプロジェクトの`propel.ini`ファイルをアップデートしなければなりません。

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

`project:upgrade`タスクはこの変更を行おうとしますが、`propel.ini`にローカルな変更をする場合、不可能です。

symfony 1.2では`BaseFormFilterPropel`クラスは`lib/filter/base`に正しく生成されませんでしたが、symfony 1.3で訂正されました; クラスは`lib/filter`に生成されます。
`project:upgrade`タスクはこのファイルを移動させます。

テスト
------

ユニットテストのブートストラップファイルである`test/bootstrap/unit.php`はプロジェクトのクラスファイルのオートロードをよりうまく処理するよう強化されました。
次のコードをこのスクリプトに追加しなければなりません:

    [php]
    $autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
    $autoload->loadConfiguration(sfFinder::type('file')->name('autoload.yml')->in(array(
      sfConfig::get('sf_symfony_lib_dir').'/config/config',
      sfConfig::get('sf_config_dir'),
    )));
    $autoload->register();

`project:upgrade`タスクはこの変更を行おうとしますが、`test/bootstrap/unit.php`へのローカルな変更をする場合は不可能であることがあります。
