1.3 での廃止予定および削除される機能
======================================

このドキュメントでは symfony 1.3 で廃止予定もしくは削除されるすべての設定、クラス、メソッド、関数とタスクの一覧を示します。 


コアプラグイン
------------

次のコアプラグインは symfony 1.3 で廃止予定になり symfony 1.4 で削除されます:

  * `sfCompat10Plugin`: このプラグインが廃止予定になることで、動くためにこのプラグインに依存するほかのすべての要素も廃止予定になります (1.0 のアドミンジェネレータとフォームシステム)。これらのなかには `lib/plugins/sfPropelPlugin/data/generator/sfPropelAdmin` に設置されている 1.0 アドミンジェネレータのデフォルトテーマも含まれています。

  * `sfProtoculousPlugin`: このプラグインによって提供されるヘルパーは控えめな JavaScript をサポートしないので、今後は使うべきではありません。


メソッドと関数
--------------

次のメソッドと関数は symfony 1.3 もしくはそれ以前で廃止予定になり、symfony 1.4 で削除されます:

  * `sfToolkit::getTmpDir()`: このメソッド呼び出しはすべて `sys_get_temp_dir()` に置き換わります。

 * `sfToolkit::removeArrayValueForPath()`、
    `sfToolkit::hasArrayValueForPath()` と `getArrayValueForPathByRef()`

  * `sfValidatorBase::setInvalidMessage()`: 新しい `sfValidatorBase::setDefaultMessage()` メソッド呼び出しに置き換わります。

  * `sfValidatorBase::setRequiredMessage()`: 新しい `sfValidatorBase::setDefaultMessage()` メソッド呼び出しに置き換わります。

  * `sfTesterResponse::contains()`: より強力な `matches()` メソッドを使うことができます

  * `sfTestFunctionalBase` の次のメソッド: `isRedirected()`、`isStatusCode()`、`responseContains()`、
    `isRequestParameter()`、`isResponseHeader()`、
    `isUserCulture()`、`isRequestFormat()` と `checkResponseElement()`: これらのメソッドは 1.2 以降で廃止予定になり、テスタークラスに置き換わります。

  * `sfTestFunctional` の次のメソッド: `isCached()`、 `isUriCached()`: これらのメソッドは 1.2 以降で廃止予定になり、テスタークラスに置き換わります。

  * `sfFilesystem::sh()`: このメソッド呼び出しはすべて新しい `sfFilesystem::execute()` メソッド呼び出しに置き換わります。このメソッドの戻り値は `stdout` 出力と `stderr` 出力で構成される配列であることに注意してください。

  * `sfAction::getDefaultView()`、`sfAction::handleError()`、
    `sfAction::validate()`: これらのメソッドは symfony 1.1 で廃止予定になり、またあまり役に立つものではありませんでした。symfony 1.1 に関して、`compat_10` 設定を `on` にセットする必要があります。

  * `sfComponent::debugMessage()`: 代わりに `log_message()` ヘルパーを使います。

  * `sfApplicationConfiguration::loadPluginConfig()`: 代わりに `initializePlugins()` を使います。

  * `sfLoader::getHelperDirs()` と `sfLoader::loadHelpers()`: `sfApplicationConfiguration` オブジェクトから同じメソッドを使ってください。`sfLoader` クラスのすべてのメソッドは廃止予定なので、`sfLoader` は symfony 1.4 で削除されます。

  * `sfController::sendEmail()`: 代わりに symfony 1.3 の新しいメーラー機能を使います。

  * `sfGeneratorManager::initialize()`: 何も行いません。

  * `debug_message()`: 代わりに `log_message()` ヘルパーを使います。

  * `sfWebRequest::getMethodName()`: 代わりに `getMethod()` を使います。

  * `sfDomCssSelector::getTexts()`: `matchAll()->getValues()` を使います。 

  * `sfDomCssSelector::getElements()`: `matchAll()` を使います。

  * `sfVarLogger::getXDebugStack()`: 代わりに `sfVarLogger::getDebugBacktrace()` を使います。

  * `sfVarLogger`: ロギングでは `debug_backtrace` の値が推奨されるので `debug_stack` の値は廃止予定です。

  * `sfContext::retrieveObjects()`: このメソッドを使うのは `ObjectHelper` のみなので、廃止予定です

次のメソッドと関数は symfony 1.3 で削除されます:

  * `sfApplicationConfiguration::checkSymfonyVersion()`: 説明は下記のセクションを参照してください (`check_symfony_version` 設定)。


クラス
------

次のクラスは symfony 1.3 で廃止予定になり symfony 1.4 で削除されます:

  * `sfDoctrineLogger`: 代わりに `sfDoctrineConnectionProfiler` を使います。

  * `sfNoRouting` と `sfPathInfoRouting`

  * `sfRichTextEditor`、`sfRichTextEditorFCK` と `sfRichTextEditorTinyMCE`: これらはウィジェットシステムに置き換わりました (下記の「ヘルパー」のセクションを参照)。

  * `sfCrudGenerator`、`sfAdminGenerator`、`sfPropelCrudGenerator`、
    `sfPropelAdminGenerator`: これらのクラスは 1.0 のアドミンジェネレータで使われていました。

  * `sfPropelUniqueValidator`、`sfDoctrineUniqueValidator`: これらのクラスは 1.0 のフォームシステムで使われていました。

  * `sfLoader`: 「メソッドと関数」のセクションを参照してください。

  * `sfConsoleRequest`、`sfConsoleResponse`、`sfConsoleController`

  * `sfDoctrineDataRetriever`、`sfPropelDataRetriever`: これらのクラスは `ObjectHelper` のみで使われていたので廃止予定です。

  * `sfWidgetFormI18nSelectLanguage`、
    `sfWidgetFormI18nSelectCurrency` と `sfWidgetFormI18nSelectCountry`:
    対応する `Choice` ウィジェットを使います (対応するのは順に `sfWidgetFormI18nChoiceLanguage`、
    `sfWidgetFormI18nChoiceCurrency` と `sfWidgetFormI18nChoiceCountry`)。カスタマイズできることを除いて、これらはまったく同じように動きます。

  * `SfExtensionObjectBuilder`、`SfExtensionPeerBuilder`、
    `SfMultiExtendObjectBuilder`、`SfNestedSetBuilder`、
    `SfNestedSetPeerBuilder`、`SfObjectBuilder`、`SfPeerBuilder`:
    Propel のカスタムビルダークラスは Propel 1.4 の新しいビヘイビアシステムに移植されました。

次のクラスは symfony 1.3 で削除されます:

  * `sfCommonFilter`: 結果とコードをマイグレートする方法に関する情報は「プロジェクトを 1.2 から 1.3/1.4 にアップグレードする」の「共通フィルタの削除」を参照してください。


ヘルパー
--------

次のヘルパーグループは symfony 1.3 で廃止予定になり symfony 1.4 で削除されます:

  * `sfCompat10Plugin` によって提供される 1.0 のフォームシステムに関連するすべてのヘルパー: `DateForm`、`Form`、`ObjectAdmin`、`Object` と `Validation`

`form_tag()` ヘルパーの所属グループが `Form` ヘルパーグループから `Url` ヘルパーグループに移動したので、 symfony 1.4 でも利用可能です。

PHP のインクルードパスからヘルパーをロードする機能は 1.3 で廃止予定になり1.4で削除されます。ヘルパーはプロジェクト、アプリケーションもしくはモジュールの `lib/helper/` ディレクトリのどれか1つに設置しなければなりません。

設定
----

次の設定 (`settings.yml` 設定で管理されます) は symfony 1.3 から削除されます:

  * `check_symfony_version`: この設定は symfony のバージョンが変更される場合にキャッシュの自動消去を可能にするために数年前に導入されました。これはおもにすべての顧客のあいだで同じバージョンの symfony が共有される共用ホスティングのコンフィギュレーションに役立っていました。symfony 1.1 以降ではバッドプラクティスですので (プロジェクトごとに symfony のバージョンを組み込む必要があるため)、設定は無意味です。さらに、この設定が `on` にセットされている場合、ファイルのコンテンツを得る必要があるときに、小さなオーバーヘッドがそれぞれのリクエストに追加されてしまいます。

  * `max_forwards`: この設定は symfony が例外を投げる前に許容される転送の最大回数をコントロールします。これを設定可能にする値はありません。5回よりも多くの転送が必要な場合、問題の認識とパフォーマンスの両方で問題があります。

  * `sf_lazy_cache_key`: symfony 1.2.6 で大きなパフォーマンス改善のために導入され、この設定はビューキャッシュのために遅延キャッシュキー生成を有効にすることを許可しました。コア開発者は遅延がベストなアイデアと考える一方で、なかにはアクション自身がキャッシュ可能ではないときでも `sfViewCacheManager::isCacheable()` の呼び出しに頼るひともいました。symfony 1.3 に関して、ふるまいは `sf_lazy_cache_key` が `true` にセットされている場合と同じになります。

  * `strip_comments`: `strip_comments` は PHP 5.0.x のトークナイザが原因でバグのあるコメント除外機能を無効にできるように導入されました。Tokenizer エクステンションが PHP によってコンパイルされていなかったとき、メモリの大量消費を避けるためにも使われていました。PHP の最小バージョンが 5.2 になることで最初の問題は 無関係になり、2番目の問題はコメント除外機能をシミュレートした正規表現を削除することですでに修正されています。

  * `lazy_routes_deserialize`: このオプションはもう必要ありません。

次の設定は symfony 1.3 で廃止予定で symfony 1.4 で削除されます:

  * `calendar_web_dir`、`rich_text_js_dir`: これらの設定を使っていたのは Form ヘルパーグループだけなので、symfony 1.3 で廃止予定です。

  * `validation_error_prefix`、`validation_error_suffix`、
    `validation_error_class`、`validation_error_id_prefix`: これらの設定は Validation ヘルパーグループによって使われ、symfony 1.3 で廃止予定です。

  * `is_internal` (`module.yml`): `is_internal` フラグはアクションがブラウザから呼び出されるのを防ぐために使われました。これは symfony 1.0 でメール送信を保護するために追加されました。メールのサポートにこのトリックが必要なくなったので、このフラグは削除され symfony コアではチェックされません。

タスク
------

次のタスクが symfony 1.3 で削除されます:

  * `project:freeze` と `project:unfreeze`: これらのタスクはプロジェクトによって使われる symfony のバージョンをプロジェクト自身の内部に組み込むために使われました。これらはもはや必要ありません。長期間に渡って symfony をプロジェクトに組み込むのがベストプラクティスになったからです。さらに、あるバージョンの symfony を別のバージョンに切り替える作業は本当に単純で必要なのは `ProjectConfiguration` クラスへのパスを変更することだけです。symfony を手作業で組み込むやり方も単純で必要なのは symfony のディレクトリ全体をプロジェクトのどこかにコピーすることだけです (`lib/vendor/symfony/` が推奨されます)。

次のタスクは symfony 1.3 で廃止予定で、symfony 1.4 で削除されます:

  * symfony 1.0 のすべてのタスクのエイリアス

  * `propel:init-admin`: このタスクは symfony 1.0 のアドミンジェネレータモジュールを生成しました。

次の Doctrine タスクは `doctrine:build` にマージされ symfony 1.4 で削除されます:

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

その他
------

次のふるまいは symfony 1.3 で廃止予定で、symfony 1.4 で削除されます:

  * `sfParameterHolder::get()`、`sfParameterHolder::has()`、
    `sfParameterHolder::remove()`、
    `sfNamespacedParameterHolder::get()`、
    `sfNamespacedParameterHolder::has()` と `sfNamespacedParameterHolder::remove()` メソッドの配列表記 (`[]`) のサポートは廃止予定になり symfony 1.4 では利用できません (パフォーマンスの向上)。

symfony CLI はグローバルな `--dry-run` オプションを受け取ることはありません。このオプションは symfony の組み込みタスクによって使われていなかったからです。タスクの1つがこのオプションに依存する場合、これをタスククラスのローカルオプションとして追加できます。

1.0 のアドミンジェネレータの Propel テンプレートと 1.0 の CRUD は symfony 1.4 で削除されます (`plugins/sfPropelPlugin/data/generator/sfPropelAdmin/`)。

「Dynarch Calendar」 (`data/web/calendar/` で見つかります) は symfony 1.4 は削除されます。これは symfony 1.4 で削除される Form ヘルパーグループだけにしか使われていなかったからです。

symfony 1.3 に関して、サイトが利用不可能なときに表示されるページは `%SF_APP_CONFIG_DIR%/` と `%SF_CONFIG_DIR%/` ディレクトリでのみ探されます。まだこれを `%SF_WEB_DIR%/errors/` に保存している場合、symfony 1.4 へのマイグレーションを行う前に削除しなければなりません。

プロジェクトのルートで `doc/` ディレクトリが生成されなくなりました。これは symfony 自身でも使われていないからです。そして関連する `sf_doc_dir` も削除されました。

`sfDoctrinePlugin_doctrine_lib_path` 設定は、以前 Doctrine のカスタム lib ディレクトリを指定するのに使われていましたが、1.3 で廃止予定になり 1.4 で削除されます。代わりに `sf_doctrine_dir` 設定を使ってください。

symfony のすべての `Base*` クラスは抽象クラスではありません。
