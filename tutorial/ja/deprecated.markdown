1.3の廃止予定と削除
===================

このドキュメントではsymfony 1.3で廃止予定もしくは削除されるすべての設定、クラス、メソッド、関数とタスクの一覧を示します。 

コアプラグイン
------------

次のコアプラグインはsymfony 1.3で廃止予定になりsymfony 1.4で削除されます:

  * `sfCompat10Plugin`: このプラグインを廃止予定にすることで、動作するためにこのプラグインに依存するほかのすべての要素も廃止予定にします(1.0のadminジェネレーターとフォームシステム)。これは`lib/plugins/sfPropelPlugin/data/generator/sfPropelAdmin`に設置される1.0用のadminジェネレーターのデフォルトテーマも含みます。

  * `sfProtoculousPlugin`: このプラグインによって提供されるヘルパーは控えめなJavaScriptをサポートしないので、今後は使うべきではありません。

メソッドと関数
--------------

次のメソッドと関数はsymfony 1.3かそれ以前で廃止予定になり、symfony 1.4で削除されます:

  * `sfToolkit::getTmpDir()`: 存在するこのメソッドのすべてを`sys_get_temp_dir()`に置き換えます。

  * `sfValidatorBase::setInvalidMessage()`: 新しい`sfValidatorBase::setDefaultMessage()`メソッドの呼び出しに置き換えます。

  * `sfValidatorBase::setRequiredMessage()`: 新しい`sfValidatorBase::setDefaultMessage()`メソッドの呼び出しに置き換えます。

  * `sfTesterResponse::contains()`: より強力な`matches()`メソッドを使うことができます

  * `sfTestFunctionalBase`の次のメソッド: `isRedirected()`、`isStatusCode()`、`responseContains()`、`isRequestParameter()`、`isResponseHeader()`、`isUserCulture()`、`isRequestFormat()`と`checkResponseElement()`: これらのメソッドは1.2以降で廃止予定になり、テスタークラスに置き換えられました。

  * `sfFilesystem::sh()`: 存在するこのメソッドのすべてを新しい`sfFilesystem::execute()`メソッドの呼び出しに置き換えます。
    このメソッドの戻り値は`stdout`出力と`stderr`出力で構成される配列であることに注意してください。

  * `sfAction::getDefaultView()`、`sfAction::handleError()`、
    `sfAction::validate()`: これらのメソッドはsymfony 1.1で廃止予定になり、またあまり便利なものではありませんでした。
    symfony 1.1に関して、`compat_10`設定を`on`にセットする必要があります。

  * `sfComponent::debugMessage()`: 代わりに`log_message()`ヘルパーを使います

  * `sfApplicationConfiguration::loadPluginConfig()`: 代わりに`initializePlugins()`を使います

  * `sfLoader::getHelperDirs()`と`sfLoader::loadHelpers()`: `sfApplicationConfiguration`オブジェクトから同じメソッドを使ってください。
    `sfLoader`クラスのすべてのメソッドは廃止予定になったので、symfony 1.4で`sfLoader`は削除されます。

  * `sfController::sendEmail()`

  * `sfGeneratorManager::initialize()`: 何もしません。

  * `debug_message()`: 代わりに`log_message()`ヘルパーを使います。

  * `sfWebRequest::getMethodName()`: 代わりに`getMethod()`を使います。

  * `sfDomCssSelector::getTexts()`: `matchAll()->getValues()`を使います。 

  * `sfDomCssSelector::getElements()`: `matchAll()`を使います。

  * `sfVarLogger::getXDebugStack()`: 代わりに`sfVarLogger::getDebugBacktrace()`を使います。

  * `sfVarLogger`: `debug_backtrace`の値を推奨するのでロギングされる`debug_stack`の値は廃止予定です。

  * `sfContext::retrieveObjects()`: このメソッドを使うのはObjectHelperのみで、廃止予定です

次のメソッドと関数はsymfony 1.3で削除されます:

  * `sfApplicationConfiguration::checkSymfonyVersion()`: 説明は下記を参照してください(`check_symfony_version`設定)

クラス
------

次のクラスはsymfony 1.3で廃止予定になりsymfony 1.4で削除されます:

  * `sfDoctrineLogger`: 代わりに`sfDoctrineConnectionProfiler`を使ってください

  * `sfNoRouting`と`sfPathInfoRouting`

  * `sfRichTextEditor`、`sfRichTextEditorFCK`と`sfRichTextEditorTinyMCE`:
    これらはウィジェットシステムに置き換えられました(下記の"Helpers"セクションを参照)

  * `sfCrudGenerator`、`sfAdminGenerator`、`sfPropelCrudGenerator`、
    `sfPropelAdminGenerator`: これらのクラスは1.0 adminジェネレーターで使われていました。

  * `sfPropelUniqueValidator`、`sfDoctrineUniqueValidator`: これらのクラスは1.0フォームシステムで使われていました

  * `sfLoader`: "メソッドと関数"のセクションを参照

  * `sfConsoleRequest`、`sfConsoleResponse`、`sfConsoleController`

  * `sfDoctrineDataRetriever`、`sfPropelDataRetriever`: これらのクラスはObjectHelperのみで使われ、廃止予定です

  * `sfWidgetFormI18nSelectLanguage`、`sfWidgetFormI18nSelectCurrency`と`sfWidgetFormI18nSelectCountry`: 対応する`Choice`ウィジェットを使います(対応するのは順に`sfWidgetFormI18nChoiceLanguage`、`sfWidgetFormI18nChoiceCurrency`と`sfWidgetFormI18nChoiceCountry`)。
    これらをカスタマイズできる可能性があることを除いて、これらはまったく同じように動作します。

  * `SfExtensionObjectBuilder`、`SfExtensionPeerBuilder`、
    `SfMultiExtendObjectBuilder`、`SfNestedSetBuilder`、
    `SfNestedSetPeerBuilder`、`SfObjectBuilder`、`SfPeerBuilder`: カスタムのPropelビルダークラスはPropel 1.4の新しいビヘイビアシステムに移植されました。

次のクラスはsymfony 1.3で削除されます:

  * `sfCommonFilter`: 結果とコードをマイグレートする方法に関する情報はUPGRADE_TO_1_3ファイルの"共通フィルターの削除"を参照してください。

ヘルパー
--------

次のヘルパーグループはsymfony 1.3で廃止予定になりsymfony 1.4で削除されます:

  * `sfCompat10Plugin`によって提供される1.0フォームシステムに関連するすべてのヘルパー: `DateForm`、`Form`、`ObjectAdmin`、`Object`と`Validation`

設定
----

次の設定(`settings.yml`設定で管理される)はsymfony 1.3から削除されました:

  * `check_symfony_version`: この設定はsymfonyのバージョンが変更される場合にキャッシュの自動クリーニングを可能にするために数年前に導入されました。
    これは主にすべての顧客のあいだでsymfonyのバージョンが共有される共用ホスティングのコンフィギュレーションに便利でした。
    symfony 1.1以降ではバッドプラクティスです(プロジェクトごとにsymfonyのバージョンを埋め込む必要がある)、設定は意味をなしません。
    さらに、この設定が`on`にセットされている場合、ファイルのコンテンツを得る必要があるときに、それぞれのリクエストに小さなオーバーヘッドを追加します。

  * `max_forwards`: この設定はsymfonyが例外を投げる前に許容されるフォワードの最大回数をコントロールします。
    これを設定可能にする値はありません。
    5回より多くのフォワードが必要な場合、問題の認識とパフォーマンスの両方で問題があります。

  * `sf_lazy_cache_key`: symfony 1.2.6で大きなパフォーマンス改善として導入され、
    この設定はビューキャッシュのために遅延キャッシュキージェネレーションを有効にすることを許可しました。
    コア開発者は遅延がベストなアイディアと考える一方で、中にはアクション自身がキャッシュ可能ではないときでも呼び出される`sfViewCacheManager::isCacheable()`に頼る人もいました。
    symfony 1.3に関しては、ふるまいは`sf_lazy_cache_key`が`true`にセットされた場合と同じになります。

  * `strip_comments`: `strip_comments`はPHP 5.0.Xバージョンのトークナイザーのバグが原因のコメントのストリッピングを無効にできるように導入されました。
    TokenizerエクステンションがPHPによってコンパイルされていなかったとき、メモリーの大量消費を避けるためにも使われました。 
    最初の問題はPHPの最小バージョンが5.2なので関係なくなっていることとで2番目の問題はコメントのストリッピング機能をシミュレートした正規表現を削除することですでに修正されていることです。

  * `lazy_routes_deserialize`: このオプションはもう必要ありません。

次の設定はsymfony 1.3で廃止予定でsymfony 1.4で削除されます:

  * `calendar_web_dir`、`rich_text_js_dir`: これらの設定はFormヘルパーグループによってのみ使われ、symfony 1.3で廃止予定です。

  * `validation_error_prefix`、`validation_error_suffix`、
    `validation_error_class`、`validation_error_id_prefix`: これらの設定はValidationヘルパーグループによって使われ、symfony 1.3で廃止予定です。

  * `is_internal` (`module.yml`): `is_internal`フラグはブラウザーからアクションが呼び出されるのを防止するために使われました。 
    これはsymfony 1.0でEメール送信を保護するために追加されました。
    Eメールのサポートはこのトリックを必要としなくなったので、このフラグは削除されsymfonyコアではチェックされません。

タスク
------

次のタスクはsymfony 1.3で削除されました:

  * `project:freeze`と`project:unfreeze`: これらのタスクはプロジェクトによって使われるsymfonyのバージョンをプロジェクト自身の内部に埋め込むために使われました。
    これらはもはや必要ありません。
    長期間をかけてsymfonyをプロジェクトに埋め込むのがベストプラクティスになったからです。
    さらに、あるバージョンのsymfonyを別のバージョンに切り替える作業は本当に単純で必要なのは`ProjectConfiguration`クラスへのパスを変更することだけです。
    symfonyを手作業で埋め込むのもとても単純でsymfonyのディレクトリ全体をプロジェクトのどこかにコピーすることだけ必要です(`lib/vendor/symfony/`が推奨されます)。

次のタスクはsymfony 1.3で廃止予定で、symfony 1.4で削除されます:

  * symfony 1.0のすべてのタスクのエイリアス

  * `propel:init-admin`: このタスクはsymfony 1.0用のadminジェネレーターモジュールを生成しました。

次のDoctrineタスクは`doctrine:build`にマージされsymfony 1.4で削除されます:

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

その他
------

次のふるまいはsymfony 1.3で廃止予定で、symfony 1.4で削除されます:

  * `sfParameterHolder::get()`、`sfParameterHolder::has()`、`sfParameterHolder::remove()`、`sfNamespacedParameterHolder::get()`、`sfNamespacedParameterHolder::has()`と`sfNamespacedParameterHolder::remove()`メソッドは配列表記(`[]`)をサポートし廃止予定でsymfony 1.4では利用できません(パフォーマンスの向上)。

symfony CLIはグローバルな`--dry-run`オプションを受け取ることはありません。
このオプションはsymfonyの組み込みタスクによって使われていなかったからです。
タスクの1つがこのオプションに依存する場合、これをタスククラスのローカルオプションとして追加できます。

1.0 adminジェネレーター用のPropelテンプレートと1.0 CRUDはsymfony 1.4で削除されます(`plugins/sfPropelPlugin/data/generator/sfPropelAdmin/`)。

"Dynarch calendar"(`data/web/calendar/`で見つかる)はsymfony 1.4は削除されます。
これはsymfony 1.4で削除されるFormヘルパーグループによってのみ使われているからです。

symfony 1.3に関して、unavailableページは`%SF_APP_CONFIG_DIR%/`と`%SF_CONFIG_DIR%/`ディレクトリでのみ探されます。
まだこれを`%SF_WEB_DIR%/errors/`に保存している場合、symfony 1.4へのマイグレーションを行う前に削除しなければなりません。

プロジェクトのルートの`doc/`ディレクトリは生成されることはありません。symfony自身でも使われていないからです。 
そして関連する`sf_doc_dir`も削除されました。
