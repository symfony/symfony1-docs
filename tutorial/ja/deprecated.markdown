1.3 で廃止予定になったもしくは削除された機能
==============================================

このドキュメントでは symfony 1.3 で廃止予定になったもしくは削除されたすべての設定、クラス、メソッド、関数とタスクをひととおり説明します。 


コアプラグイン
------------

次のコアプラグインは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました:

  * `sfCompat10Plugin`: このプラグインが廃止予定になることで、このプラグインに依存するほかのすべての要素も廃止予定になりました (symfony 1.0 のアドミンジェネレータとフォームシステム)。これらのなかには、 `lib/plugins/sfPropelPlugin/data/generator/sfPropelAdmin` に設置されているアドミンジェネレータのデフォルトテーマも含まれています。

  * `sfProtoculousPlugin`: このプラグインから提供されるヘルパーは控えめな JavaScript をサポートしないので、今後は使うべきではありません。


メソッドと関数
--------------

次のメソッドと関数は symfony 1.3 もしくはそれ以前のバージョンで廃止予定になり、symfony 1.4 で削除されました:

  * `sfToolkit::getTmpDir()`: このメソッド呼び出しはすべて `sys_get_temp_dir()` に置き換えます。

 * `sfToolkit::removeArrayValueForPath()`、
    `sfToolkit::hasArrayValueForPath()` と `getArrayValueForPathByRef()`

  * `sfValidatorBase::setInvalidMessage()`: 新しい `sfValidatorBase::setDefaultMessage()` メソッド呼び出しに置き換えます。

  * `sfValidatorBase::setRequiredMessage()`: 新しい `sfValidatorBase::setDefaultMessage()` メソッド呼び出しに置き換えます。

  * `sfTesterResponse::contains()`: より強力な `matches()` メソッドを使うことができます

  * `sfTestFunctionalBase` の次のメソッド: `isRedirected()`、`isStatusCode()`、`responseContains()`、
    `isRequestParameter()`、`isResponseHeader()`、
    `isUserCulture()`、`isRequestFormat()` と `checkResponseElement()`: これらのメソッドは symfony 1.2 以降で廃止予定になったので、テスタークラスに置き換えます。

  * `sfTestFunctional` の次のメソッド: `isCached()`、 `isUriCached()`: これらのメソッドは symfony 1.2 以降で廃止予定になったので、テスタークラスに置き換えます。

  * `sfFilesystem::sh()`: このメソッド呼び出しは新しい `sfFilesystem::execute()` メソッド呼び出しに置き換えます。このメソッドの戻り値は `stdout` 出力と `stderr` 出力で構成される配列であることにご注意ください。

  * `sfAction::getDefaultView()`、`sfAction::handleError()`、
    `sfAction::validate()`: これらのメソッドは symfony 1.1 で廃止予定になり、またあまり役に立つものではありませんでした。symfony 1.1 で利用するには、`compat_10` 設定を `on` にセットする必要があります。

  * `sfComponent::debugMessage()`: 代わりに `log_message()` ヘルパーを使います。

  * `sfApplicationConfiguration::loadPluginConfig()`: 代わりに `initializePlugins()` を使います。

  * `sfLoader::getHelperDirs()` と `sfLoader::loadHelpers()`: `sfApplicationConfiguration` オブジェクトから同じメソッドを使ってください。`sfLoader` クラスのメソッドがすべて廃止予定になったので、`sfLoader` は symfony 1.4 で削除されました。

  * `sfController::sendEmail()`: 代わりに symfony 1.3 の新しいメーラーを使います。

  * `sfGeneratorManager::initialize()`: 何も行いません。

  * `debug_message()`: 代わりに `log_message()` ヘルパーを使います。

  * `sfWebRequest::getMethodName()`: 代わりに `getMethod()` を使います。

  * `sfDomCssSelector::getTexts()`: `matchAll()->getValues()` を使います。 

  * `sfDomCssSelector::getElements()`: `matchAll()` を使います。

  * `sfVarLogger::getXDebugStack()`: 代わりに `sfVarLogger::getDebugBacktrace()` を使います。

  * `sfVarLogger`: ロギングでは `debug_backtrace` が推奨され、`debug_stack` は廃止予定になりました。

  * `sfContext::retrieveObjects()`: このメソッドを使っていたのは `ObjectHelper` だけなので、廃止予定になりました

次のメソッドと関数は symfony 1.3 で削除されました:

  * `sfApplicationConfiguration::checkSymfonyVersion()`: 説明は下記の節を参照してください (`check_symfony_version` 設定)。


クラス
------

次のクラスは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました:

  * `sfDoctrineLogger`: 代わりに `sfDoctrineConnectionProfiler` を使います。

  * `sfNoRouting` と `sfPathInfoRouting`

  * `sfRichTextEditor`、`sfRichTextEditorFCK` と `sfRichTextEditorTinyMCE`: これらのクラスはウィジェットシステムに置き換えます (下記の「ヘルパー」の節を参照)。

  * `sfCrudGenerator`、`sfAdminGenerator`、`sfPropelCrudGenerator`、
    `sfPropelAdminGenerator`: これらのクラスは symfony 1.0 のアドミンジェネレータで使われていました。

  * `sfPropelUniqueValidator`、`sfDoctrineUniqueValidator`: これらのクラスは symfony 1.0 のフォームシステムで使われていました。

  * `sfLoader`: 「メソッドと関数」の節を参照してください。

  * `sfConsoleRequest`、`sfConsoleResponse`、`sfConsoleController`

  * `sfDoctrineDataRetriever`、`sfPropelDataRetriever`: これらのクラスを使っていたのは `ObjectHelper` だけなので、廃止予定になりました。

  * `sfWidgetFormI18nSelectLanguage`、
    `sfWidgetFormI18nSelectCurrency` と `sfWidgetFormI18nSelectCountry`:
    対応する `Choice` ウィジェットを使います (対応するのは順に `sfWidgetFormI18nChoiceLanguage`、
    `sfWidgetFormI18nChoiceCurrency` と `sfWidgetFormI18nChoiceCountry`)。カスタマイズできることを除いて、これらのウィジェットはまったく同じように動きます。

  * `SfExtensionObjectBuilder`、`SfExtensionPeerBuilder`、
    `SfMultiExtendObjectBuilder`、`SfNestedSetBuilder`、
    `SfNestedSetPeerBuilder`、`SfObjectBuilder`、`SfPeerBuilder`:
    Propel のカスタムビルダークラスは Propel 1.4 の新しいビヘイビアシステムに移植されました。

次のクラスは symfony 1.3 で削除されました:

  * `sfCommonFilter`: 結果とコードをマイグレートする方法に関する情報は「プロジェクトを 1.2 から 1.3/1.4 にアップグレードする」の「共通フィルタの削除」を参照してください。


ヘルパー
--------

次のヘルパーグループは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました:

  * `sfCompat10Plugin` から提供される symfony 1.0 のフォームシステムに関連するすべてのヘルパー: `DateForm`、`Form`、`ObjectAdmin`、`Object` と `Validation`

`form_tag()` ヘルパーの所属グループが `Form` ヘルパーグループから `Url` ヘルパーグループに移動したので、 このヘルパーは symfony 1.4 でも利用可能です。

PHP のインクルードパスからヘルパーをロードする機能は symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。ヘルパーの設置場所は、プロジェクト、アプリケーションもしくはモジュールの `lib/helper/` ディレクトリのどれか1つにしなければなりません。

設定
----

`settings.yml` 設定で管理されている次の設定は symfony 1.3 から削除されました:

  * `check_symfony_version`: この設定は symfony のバージョンが変更される場合にキャッシュの自動消去を可能にするために数年前に導入されました。この設定は主にすべての顧客のあいだで symfony の同じバージョンが共有される共用ホスティングのコンフィギュレーションに役立っていました。symfony 1.1 以降ではバッドプラクティスなので、設定は無効です (プロジェクトごとに symfony のバージョンを組み込む必要があります)。さらに、この設定が `on` にセットされている場合、ファイルの内容を取得する必要があるときに、小さなオーバーヘッドがそれぞれのリクエストに追加されてしまいます。

  * `max_forwards`: この設定は symfony が例外を投げる前に許容される転送の最大回数をコントロールします。この設定を変更しても効果はありません。5回よりも多くの転送が必要な場合、問題の認識とパフォーマンスの両方で問題があります。

  * `sf_lazy_cache_key`: symfony 1.2.6 で大きなパフォーマンス改善のために導入され、この設定はビューキャッシュのために遅延キャッシュキー生成を有効にすることを許可しました。コア開発者は遅延がベストなアイデアと考える一方で、なかにはアクション自身がキャッシュ可能ではないときでも `sfViewCacheManager::isCacheable()` の呼び出しに頼るひともいました。symfony 1.3 に関して、ふるまいは `sf_lazy_cache_key` が `true` にセットされている場合と同じになります。

  * `strip_comments`: `strip_comments` は PHP 5.0.x のトークナイザによるバグをかかえるコメント除外機能を無効にできるように導入されました。Tokenizer エクステンションが PHP によってコンパイルされていなかった場合に、メモリの大量消費を避けるためにも使われていました。PHP の最小バージョンが 5.2 になったことで、最初の問題は無関係になり、コメント除外機能をシミュレートする正規表現が削除されたことで、2番目の問題はすでに修正されています。

  * `lazy_routes_deserialize`: このオプションはもはや必要ありません。

次の設定は symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました:

  * `calendar_web_dir`、`rich_text_js_dir`: これらの設定を使っていたのは Form ヘルパーグループだけなので symfony 1.3 で廃止予定です。

  * `validation_error_prefix`、`validation_error_suffix`、
    `validation_error_class`、`validation_error_id_prefix`: これらの設定を使っていたのは Validation ヘルパーグループだけなので、symfony 1.3 で廃止予定です。

  * `is_internal` (`module.yml`): `is_internal` フラグはアクションがブラウザから呼び出されるのを防ぐために使われました。このフラグは symfony 1.0 でメール送信を保護するために導入されました。このトリックがメールのサポートに必要なくなったので、このフラグは削除され、symfony コアではチェックされなくなりました。

タスク
------

次のタスクが symfony 1.3 で削除されます:

  * `project:freeze` と `project:unfreeze`: プロジェクトによって使われる symfony のバージョンをプロジェクト自身の内部に組み込むためにこれらのタスクが使われていましたが、もはや必要ありません。長期間にわたって symfony をプロジェクトに組み込むやり方がベストプラクティスになったからです。さらに、symfony のあるバージョンを別のバージョンに切り替える作業は本当にシンプルで、必要なのは `ProjectConfiguration` クラスへのパスを変更することだけです。symfony を手作業で組み込むやり方もシンプルで、必要なのは symfony のディレクトリ全体をプロジェクトの任意の場所にコピーすることだけです (`lib/vendor/symfony/` が推奨されます)。

次のタスクは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました:

  * symfony 1.0 のすべてのタスクのエイリアス

  * `propel:init-admin`: このタスクは symfony 1.0 のアドミンジェネレータモジュールを生成しました。

次の Doctrine タスクは `doctrine:build` にマージされ、symfony 1.4 で削除されました:

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

その他
------

次のふるまいは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されます:

  * `sfParameterHolder::get()`、`sfParameterHolder::has()`、
    `sfParameterHolder::remove()`、
    `sfNamespacedParameterHolder::get()`、
    `sfNamespacedParameterHolder::has()` と `sfNamespacedParameterHolder::remove()` メソッドの配列表記 (`[]`) のサポートは廃止予定になり、symfony 1.4 では利用できません (パフォーマンス向上のため)。

symfony CLI はグローバルな `--dry-run` オプションを受け取らなくなりました。このオプションは symfony の組み込みタスクによって使われていなかったからです。タスクの1つがこのオプションに依存する場合、このオプションをタスククラスのローカルオプションとして追加できます。

symfony 1.0 のアドミンジェネレータの Propel テンプレートと CRUD は symfony 1.4 で削除されました (`plugins/sfPropelPlugin/data/generator/sfPropelAdmin/`)。

「Dynarch Calendar」 (`data/web/calendar/` で見つかります) は symfony 1.4 で削除されました。このライブラリを使っていたのは symfony 1.4 で削除された Form ヘルパーグループだけだからです。

symfony 1.3 に関して、サイトが利用不可能なときに表示されるページは `%SF_APP_CONFIG_DIR%/` と `%SF_CONFIG_DIR%/` ディレクトリでのみ探索されます。まだこのページが `%SF_WEB_DIR%/errors/` に保存されているのであれば、symfony 1.4 へのマイグレーションを行う前に削除しなければなりません。

プロジェクトのルートで `doc/` ディレクトリは生成されなくなりました。このディレクトリが symfony 自身でも使われていないからです。そして関連する `sf_doc_dir` も削除されました。

以前、`sfDoctrinePlugin_doctrine_lib_path` 設定は Doctrine のカスタム lib ディレクトリを指定するのに使われていましたが、symfony 1.3 で廃止予定になり symfony 1.4 で削除されました。代わりに `sf_doctrine_dir` 設定を使ってください。

symfony のすべての `Base*` クラスは抽象クラスではありません。
