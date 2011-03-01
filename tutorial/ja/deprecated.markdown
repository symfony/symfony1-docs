1.3で廃止予定になったもしくは削除された機能
============================================

このドキュメントでは symfony 1.3 で廃止予定になったもしくは削除された設定、クラス、メソッド、関数とタスクをひととおり説明します。 


コアプラグイン
--------------

次のコアプラグインは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。

  * `sfCompat10Plugin`: このプラグインが廃止予定になったことにともない、このプラグインに依存するほかのすべての要素も廃止予定になりました (symfony 1.0 のアドミンジェネレータとフォームシステム)。これらのなかには、 `lib/plugins/sfPropelPlugin/data/generator/sfPropelAdmin` ディレクトリに配置されていたアドミンジェネレータのデフォルトテーマも含まれます。

  * `sfProtoculousPlugin`: このプラグインから提供されるヘルパーは控えめな JavaScript をサポートしていないので、今後使うことはおすすめしません。


メソッドと関数
--------------

次のメソッドと関数は symfony 1.3 もしくはそれ以前のバージョンで廃止予定になり、symfony 1.4 で削除されました。

  * `sfToolkit::getTmpDir()`: このメソッド呼び出しはすべて PHP の `sys_get_temp_dir()` 関数に置き換えます。

 * `sfToolkit::removeArrayValueForPath()`、
    `sfToolkit::hasArrayValueForPath()` と `getArrayValueForPathByRef()`

  * `sfValidatorBase::setInvalidMessage()`: 新しい `sfValidatorBase::setDefaultMessage()` メソッド呼び出しに置き換えます。

  * `sfValidatorBase::setRequiredMessage()`: 新しい `sfValidatorBase::setDefaultMessage()` メソッド呼び出しに置き換えます。

  * `sfTesterResponse::contains()`: より強力な `matches()` メソッドを使うことができます

  * `sfTestFunctionalBase` の次のメソッド: `isRedirected()`、`isStatusCode()`、`responseContains()`、
    `isRequestParameter()`、`isResponseHeader()`、
    `isUserCulture()`、`isRequestFormat()` と `checkResponseElement()`: これらのメソッドは symfony 1.2 以降で廃止予定になりましたので、テスタークラスに置き換えます。

  * `sfTestFunctional` の次のメソッド: `isCached()`、 `isUriCached()`: これらのメソッドは symfony 1.2 以降で廃止予定になりましたので、テスタークラスに置き換えます。

  * `sfFilesystem::sh()`: このメソッド呼び出しは新しい `sfFilesystem::execute()` メソッド呼び出しに置き換えます。このメソッドの戻り値は `stdout` 出力と `stderr` 出力からなる配列であることにご注意ください。

  * `sfAction::getDefaultView()`、`sfAction::handleError()`、
    `sfAction::validate()`: これらのメソッドはあまり役に立っていませんでしたので symfony 1.1 で廃止予定になりました。symfony 1.1 で利用するには、`compat_10` 設定に `on` をセットする必要があります。

  * `sfComponent::debugMessage()`: 代わりに `log_message()` ヘルパーを使います。

  * `sfApplicationConfiguration::loadPluginConfig()`: 代わりに `initializePlugins()` メソッドを使います。

  * `sfLoader::getHelperDirs()` と `sfLoader::loadHelpers()`: `sfApplicationConfiguration` オブジェクトから同じ名前のメソッドを使います。`sfLoader` クラスのメソッドがすべて廃止予定になったことを受けて、symfony 1.4 で `sfLoader` クラスは削除されました。

  * `sfController::sendEmail()`: 代わりに symfony 1.3 で導入された新しいメーラーを使います。

  * `sfGeneratorManager::initialize()`: 何もおこないません。

  * `debug_message()`: 代わりに `log_message()` ヘルパーを使います。

  * `sfWebRequest::getMethodName()`: 代わりに `getMethod()` メソッドを使います。

  * `sfDomCssSelector::getTexts()`: `matchAll()->getValues()` のメソッドチェーンを使います。 

  * `sfDomCssSelector::getElements()`: `matchAll()` メソッドを使います。

  * `sfVarLogger::getXDebugStack()`: 代わりに `sfVarLogger::getDebugBacktrace()` メソッドを使います。

  * `sfVarLogger`: ロギングにおいて `debug_backtrace` オプションが推奨されるようになり、`debug_stack` オプションは廃止予定になりました。

  * `sfContext::retrieveObjects()`: このメソッドを使っていたのは `ObjectHelper` クラスだけにかぎられていたので、廃止予定になりました。

次のメソッドと関数は symfony 1.3 で削除されました。

  * `sfApplicationConfiguration::checkSymfonyVersion()`: くわしい説明は下記の節をご参照ください (`check_symfony_version` 設定)。


クラス
------

次のクラスは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。

  * `sfDoctrineLogger`: 代わりに `sfDoctrineConnectionProfiler` クラスを使います。

  * `sfNoRouting` と `sfPathInfoRouting`

  * `sfRichTextEditor`、`sfRichTextEditorFCK` と `sfRichTextEditorTinyMCE`: これらのクラスはウィジェットシステムに置き換えます (下記の「ヘルパー」の節をご参照ください)。

  * `sfCrudGenerator`、`sfAdminGenerator`、`sfPropelCrudGenerator`、
    `sfPropelAdminGenerator`: これらのクラスは symfony 1.0 のアドミンジェネレータで使われていました。

  * `sfPropelUniqueValidator`、`sfDoctrineUniqueValidator`: これらのクラスは symfony 1.0 のフォームシステムで使われていました。

  * `sfLoader`: 「メソッドと関数」の節をご参照ください。

  * `sfConsoleRequest`、`sfConsoleResponse`、`sfConsoleController`

  * `sfDoctrineDataRetriever`、`sfPropelDataRetriever`: これらのクラスを使っていたのは `ObjectHelper` クラスだけにかぎられていたので、廃止予定になりました。

  * `sfWidgetFormI18nSelectLanguage`、
    `sfWidgetFormI18nSelectCurrency` と `sfWidgetFormI18nSelectCountry`:
    対応する `Choice` ウィジェットを使います (対応するのは順に `sfWidgetFormI18nChoiceLanguage`、
    `sfWidgetFormI18nChoiceCurrency` と `sfWidgetFormI18nChoiceCountry`)。カスタマイズできることを除いて、これらのウィジェットはまったく同じように動きます。

  * `SfExtensionObjectBuilder`、`SfExtensionPeerBuilder`、
    `SfMultiExtendObjectBuilder`、`SfNestedSetBuilder`、
    `SfNestedSetPeerBuilder`、`SfObjectBuilder`、`SfPeerBuilder`:
    Propel 専用のビルダークラスは Propel 1.4 で導入された新しいビヘイビアシステムに移植されました。

次のクラスは symfony 1.3 で削除されました。

  * `sfCommonFilter`: 結果とコードをマイグレートする方法に関する情報は「プロジェクトを1.2から1.3/1.4にアップグレードする」の「共通フィルタの削除」をご参照ください。


ヘルパー
--------

次のヘルパーグループは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。

  * `sfCompat10Plugin` から提供される symfony 1.0 のフォームシステムに関連するすべてのヘルパー: `DateForm`、`Form`、`ObjectAdmin`、`Object` と `Validation`

`form_tag()` ヘルパーの所属グループが `Form` ヘルパーグループから `Url` ヘルパーグループに変わりました。 このヘルパーは symfony 1.4 でも利用できます。

PHP のインクルードパスからヘルパーをロードする機能は symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。ヘルパーの設置場所は、プロジェクト、アプリケーションもしくはモジュールの `lib/helper/` ディレクトリのどれか1つに絞らなければなりません。

設定
----

`settings.yml` ファイルで管理されていた次の設定は symfony 1.3 で削除されました。

  * `check_symfony_version`: この設定は symfony のバージョンが変更される場合にキャッシュの自動消去を可能にするために数年前に導入されました。すべての顧客のあいだで symfony の同じバージョンが共有される共用ホスティングのコンフィギュレーションにおいてこの設定は役立っていました。symfony 1.1 以降ではバッドプラクティスになり、設定は無効になっています (プロジェクトごとに symfony のバージョンを組み込む必要があります)。さらに、この設定に `on` がセットされている場合、ファイルの内容を取得する必要があるときに、リクエストごとに小さなオーバーヘッドが発生します。

  * `max_forwards`: この設定は symfony が例外を投げる前に許容される転送の最大回数をコントロールしていました。この設定を変更しても反映されなくなりました。5回よりも多くの転送が必要になるのであれば、問題の認識とパフォーマンスの両方で問題があります。

  * `sf_lazy_cache_key`: この設定は symfony 1.2.6 で大きなパフォーマンス改善のために導入され、ビューキャッシュのために遅延キャッシュキー生成を有効にすることができました。コア開発者は遅延がベストなアイディアと考える一方で、アクション自身がキャッシュ可能ではないときでも `sfViewCacheManager::isCacheable()` メソッド呼び出しに頼るひともいました。symfony 1.3 に関して、ふるまいは `sf_lazy_cache_key` 設定に `true` がセットされている場合と同じになります。

  * `strip_comments`: この設定は PHP 5.0.x のトークナイザによるバグをかかえているコメント除外機能を無効にできるように導入されました。Tokenizer エクステンションが PHP によってコンパイルされていなかった場合に、メモリの大量消費を避けるためにも使われていました。PHP の最小バージョンが5.2になったことで、最初の問題は無関係になり、コメント除外機能をシミュレートする正規表現が削除されたことで、2番目の問題はすでに解決されています。

  * `lazy_routes_deserialize`: このオプションは不要になりました。

次の設定は symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。

  * `calendar_web_dir`、`rich_text_js_dir`: これらの設定を使っていたのは Form ヘルパーグループだけにかぎられていたので、symfony 1.3 で廃止予定になりました。

  * `validation_error_prefix`、`validation_error_suffix`、
    `validation_error_class`、`validation_error_id_prefix`: これらの設定を使っていたのは Validation ヘルパーグループだけにかぎられていたので、symfony 1.3 で廃止予定になりました。

  * `is_internal` (`module.yml`): このフラグは symfony 1.0 で導入され、ブラウザからメール送信アクションが呼び出されないようにするために使われていました。このしくみはメールのサポートに必要なくなりましたので、このフラグは削除され、symfony コアではチェックされなくなりました。

タスク
------

次のタスクは symfony 1.3 で削除されました。

  * `project:freeze` と `project:unfreeze`: symfony の特定のバージョンをプロジェクト内部に組み込むためにこれらのタスクが使われていましたが、長期間にわたって symfony をプロジェクトに組み込むやりかたがベストプラクティスになりましたので、これらのタスクは不要になりました。さらに、symfony のあるバージョンを別のバージョンに切り替える作業は本当にシンプルで、必要なのは `ProjectConfiguration` クラスのなかでパスを変更することだけです。symfony を組み込む作業もシンプルで、必要なのは symfony のディレクトリ全体をプロジェクトの任意の場所にコピーすることだけです (`lib/vendor/symfony/` ディレクトリがおすすめです)。

次のタスクは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。

  * symfony 1.0 のすべてのタスクのエイリアス

  * `propel:init-admin`: このタスクは symfony 1.0 のアドミンジェネレータモジュールを生成するのに使われていました。

Doctrine の次の一連のタスク群の機能は `doctrine:build` タスクに統合され、symfony 1.4 で削除されました。

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

その他
------

次のふるまいは symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。

  * `sfParameterHolder::get()`、`sfParameterHolder::has()`、
    `sfParameterHolder::remove()`、
    `sfNamespacedParameterHolder::get()`、
    `sfNamespacedParameterHolder::has()` と `sfNamespacedParameterHolder::remove()` メソッドの配列表記 (`[]`) のサポートは廃止予定になり、symfony 1.4 では利用できません (パフォーマンス向上のため)。

symfony CLI はグローバルな `--dry-run` オプションを受けつけなくなりました。組み込みのタスクがこのオプションを使っていなかったからです。このオプションに依存しているタスクがあれば、このオプションをタスククラスのローカルオプションに追加することで、変更に対応できます。

symfony 1.0 のアドミンジェネレータの Propel テンプレートと CRUD は symfony 1.4 で削除されました (`plugins/sfPropelPlugin/data/generator/sfPropelAdmin/`)。

「Dynarch Calendar」は symfony 1.4 で削除されました (`data/web/calendar/` ディレクトリに配置されていました)。このライブラリを使っていたヘルパーグループが symfony 1.4 で削除された Form ヘルパーグループだけにかぎられていたからです。

symfony 1.3 に関して、サイトが利用不可能なときに表示されるページが探索される場所は `%SF_APP_CONFIG_DIR%/` と `%SF_CONFIG_DIR%/` ディレクトリに限定されるようになりました。まだこのページが `%SF_WEB_DIR%/errors/` ディレクトリに保存されているのであれば、symfony 1.4 へのマイグレーションをおこなう前に削除しなければなりません。

プロジェクトのルートで `doc/` ディレクトリは生成されなくなりました。このディレクトリが symfony コアでも使われていなかったからです。変更にともない、`sf_doc_dir` 設定は削除されました。

以前、`sfDoctrinePlugin_doctrine_lib_path` 設定は Doctrine の lib ディレクトリを独自に指定するのに使われていましたが、symfony 1.3 で廃止予定になり、symfony 1.4 で削除されました。代わりに `sf_doctrine_dir` 設定を使います。

symfony のすべての `Base*` クラスは抽象クラスではなくなりました。
