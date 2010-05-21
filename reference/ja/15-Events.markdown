イベント
========

`sfEventDispatcher` オブジェクトのおかげで、symfony のコアコンポーネントは疎結合されています。Event Dispatcher はコアコンポーネントのあいだのコミュニケーションを管理します。

あるオブジェクトがディスパッチャにイベントを通知すれば、ほかのオブジェクトは、ディスパッチャに接続していることで (つながっていることで)、特定のイベントをリスニングできます。

イベントは単なる名前で、ドット (`.`) で区切られる名前空間と名前で構成されます。

使い方
------

最初にイベントオブジェクトを作ります:

    [php]
    $event = new sfEvent($this, 'user.change_culture', array('culture' => $culture));

そしてディスパッチャにイベントを通知させます:

    $dispatcher->notify($event);

`sfEvent` コンストラクタは3つの引数をとります:

  * イベントの「サブジェクト (対象)」 (ほとんどの場合、これはイベントを通知するオブジェクトになりますが、`null` にもなります)
  * イベントの名前
  * リスナーに渡すパラメータの配列

リスナーにイベントをリスニングさせるために、リスナーをディスパッチャに接続させます (つなげます):

    [php]
    $dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));

ディスパッチャの `connect` メソッドは2つの引数をとります:

  * イベントの名前
  * イベントが通知されるときに呼び出される関数/メソッド 

リスナーの実装例は次のとおりです:

    [php]
    public function listenToChangeCultureEvent(sfEvent $event)
    {
      // メッセージフォーマットオブジェクトを新しいカルチャで変更する
      $this->setCulture($event['culture']);
    }

リスナーはイベントを第1引数にとります。イベントオブジェクトはイベント情報を提供するためのメソッドをいくつかもちます:

  * `getSubject()`: イベントにアタッチされているサブジェクトオブジェクトを取得します。
  * `getParameters()`: イベントパラメータを返します。

イベントオブジェクトには配列形式としてもアクセスできます。

イベントの種類
--------------

イベントは3つの異なるメソッドによって発生します:

 * `notify()`
 * `notifyUntil()`
 * `filter()`

### ~`notify`~

`notify()` メソッドはすべてのリスナーに通知します。リスナーは値を返すことはできません。すべてのリスナーの実行は保証されます。

### ~`notifyUntil`~

1つのリスナーが `true` の値を返して、チェーンを止めるまで、`notifyUntil()` メソッドはすべてのリスナーに通知し続けます。

チェーンを止めるリスナーは `setReturnValue()` メソッドを呼び出すこともできます。

通知オブジェクトは、`isProcessed()` メソッドを呼び出すことで、リスナーが処理済みのイベントをもっているかチェックできます:

    [php]
    if ($event->isProcessed())
    {
      // ...
    }

### ~`filter`~

`filter()` メソッドは、通知オブジェクトによって第2引数に渡される任意の値をフィルタリングし、リスナーの第2引数に渡される関数/メソッドによって結果が読み出されることを通知します。すべてのリスナーは受け取った値をフィルタリングして返さなければなりません。すべてのリスナーの実行は保証されます。

通知オブジェクトは `getReturnValue()` メソッドを呼び出すことで、フィルタリング済みの値を得ることができます:

    [php]
    $ret = $event->getReturnValue();

<div class="pagebreak"></div>

イベント
-------

 * [`application`](#chapter_15_application)
   * [`application.log`](#chapter_15_sub_application_log)
 * [`command`](#chapter_15_command)
   * [`command.log`](#chapter_15_sub_command_log)
   * [`command.pre_command`](#chapter_15_sub_command_pre_command)
   * [`command.post_command`](#chapter_15_sub_command_post_command)
   * [`command.filter_options`](#chapter_15_sub_command_filter_options)
 * [`configuration`](#chapter_15_configuration)
   * [`configuration.method_not_found`](#chapter_15_sub_configuration_method_not_found)
 * [`component`](#chapter_15_component)
   * [`component.method_not_found`](#chapter_15_sub_component_method_not_found)
 * [`context`](#chapter_15_context)
   * [`context.load_factories`](#chapter_15_sub_context_load_factories)
 * [`controller`](#chapter_15_controller)
   * [`controller.change_action`](#chapter_15_sub_controller_change_action)
   * [`controller.method_not_found`](#chapter_15_sub_controller_method_not_found)
   * [`controller.page_not_found`](#chapter_15_sub_controller_page_not_found)
 * [`form`](#chapter_15_form)
   * [`form.post_configure`](#chapter_15_sub_form_post_configure)
   * [`form.filter_values`](#chapter_15_sub_form_filter_values)
   * [`form.validation_error`](#chapter_15_sub_form_validation_error)
   * [`form.method_not_found`](#chapter_15_sub_form_method_not_found)
 * [`plugin`](#chapter_15_plugin)
   * [`plugin.pre_install`](#chapter_15_sub_plugin_pre_install)
   * [`plugin.post_install`](#chapter_15_sub_plugin_post_install)
   * [`plugin.pre_uninstall`](#chapter_15_sub_plugin_pre_uninstall)
   * [`plugin.post_uninstall`](#chapter_15_sub_plugin_post_uninstall)
 * [`request`](#chapter_15_request)
   * [`request.filter_parameters`](#chapter_15_sub_request_filter_parameters)
   * [`request.method_not_found`](#chapter_15_sub_request_method_not_found)
 * [`response`](#chapter_15_response)
   * [`response.method_not_found`](#chapter_15_sub_response_method_not_found)
   * [`response.filter_content`](#chapter_15_sub_response_filter_content)
 * [`routing`](#chapter_15_routing)
   * [`routing.load_configuration`](#chapter_15_sub_routing_load_configuration)
 * [`task`](#chapter_15_task)
   * [`task.cache.clear`](#chapter_15_sub_task_cache_clear)
 * [`template`](#chapter_15_template)
   * [`template.filter_parameters`](#chapter_15_sub_template_filter_parameters)
 * [`user`](#chapter_15_user)
   * [`user.change_culture`](#chapter_15_sub_user_change_culture)
   * [`user.method_not_found`](#chapter_15_sub_user_method_not_found)
   * [`user.change_authentication`](#chapter_15_sub_user_change_authentication)
 * [`view`](#chapter_15_view)
   * [`view.configure_format`](#chapter_15_sub_view_configure_format)
   * [`view.method_not_found`](#chapter_15_sub_view_method_not_found)
 * [`view.cache`](#chapter_15_view_cache)
   * [`view.cache.filter_content`](#chapter_15_sub_view_cache_filter_content)

<div class="pagebreak"></div>

`application`
-------------

### ~`application.log`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: たくさんのクラス

| パラメータ  | 説明
| ------------ | ----------------------------------------------------------------------------------
| `priority`   | 優先順位 (`sfLogger::EMERG`、`sfLogger::ALERT`、`sfLogger::CRIT`、`sfLogger::ERR`、 `sfLogger::WARNING`、`sfLogger::NOTICE`、`sfLogger::INFO` もしくは `sfLogger::DEBUG`)

`application.log` イベントは、HTTP リクエストのロギングをするために symfony によって利用されるメカニズムです (logger ファクトリを参照)。このイベントは symfony のコアコンポーネントの大半によって通知されます。

### ~`application.throw_exception`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfException`

リクエスト処理のあいだに捕まらない例外が投げられるとき、`application.throw_exception` イベントが通知されます。

このイベントをリスニングすることで、捕まらない例外が投げられたときに特別な対応を行うことができます (メールを送信する、もしくはエラーをロギングするなど)。イベントを処理することで、symfony のデフォルトの例外管理メカニズムをオーバーライドすることもできます。

`command`
---------

### ~`command.log`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfCommand*` クラス

| パラメータ  | 説明
| ------------ | -----------------------------------------------------------------------------------
| `priority`   | 優先順位 (`sfLogger::EMERG`、`sfLogger::ALERT`、`sfLogger::CRIT`、`sfLogger::ERR`、 `sfLogger::WARNING`、`sfLogger::NOTICE`、`sfLogger::INFO` もしくは `sfLogger::DEBUG`)

`command.log` イベントは symfony CLI ユーティリティでロギングするために symfony によって利用されるメカニズムです (`logger` ファクトリを参照)。

### ~`command.pre_command`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfTask`

| パラメータ  | 説明
| ------------ | ------------------------------
| `arguments`  | CLI に渡される引数の配列
| `options`    | CLI に渡されるオプションの配列

タスクの実行直前に `command.pre_command` イベントが通知されます。

### ~`command.post_command`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfTask`

タスクの実行直後に `command.post_command` イベントが通知されます。

### ~`command.filter_options`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfTask`

| パラメータ       | 説明
| ----------------- | -------------------------------
| `command_manager` | `sfCommandManager` インスタンス

タスクオプションが CLI によってパースされる前に `command.filter_options` イベントが通知されます。このイベントはユーザーに渡すオプションをフィルタリングするために使うことができます。

`configuration`
---------------

### ~`configuration.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfProjectConfiguration`

| パラメータ  | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfProjectConfiguration` クラスで定義されていなければ、`configuration.method_not_found` イベントが通知されます。継承を使わなくても、このイベントをリスニングすることで、クラスにメソッドを追加できます。

`component`
-----------

### ~`component.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfComponent`

| パラメータ | 説明
| ------------ | -----------------------------------
| `method`     | 呼び出されたが見つからないメソッド
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfComponent` クラスで定義されていないときに `component.method_not_found` イベントが通知されます。継承を使わなくても、このイベントをリスニングすることで、クラスにメソッドを追加できます。

`context`
---------

### ~`context.load_factories`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfContext`

すべてのファクトリが初期化された直後から、リクエストがやって来るたびに、`sfContext` オブジェクトによって`context.load_factories` イベントが1回通知されます。すべてのコアクラスが初期化されるときに、このイベントが最初に通知されます。

`controller`
------------

### ~`controller.change_action`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfController`

| パラメータ | 説明
| ------------ | ---------------------------
| `module`     | 実行されるモジュールの名前
| `action`     | 実行されるアクションの名前

アクションが実行される直前に `controller.change_action` が通知されます。

### ~`controller.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfController`

| パラメータ | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfController` クラスで定義されていなければ、`controller.method_not_found` イベントが通知されます。継承を使わなくても、このイベントをリスニングすることで、クラスにメソッドを追加できます。

### ~`controller.page_not_found`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfController`

| パラメータ  | 説明
| ------------ | ------------------------------------
| `module`     | 404エラーを生成するモジュールの名前
| `action`     | 404エラーを生成するアクションの名前

リクエスト処理のあいだに404エラーが生成されたとき、`controller.page_not_found` が通知されます。

404ページが表示されるとき、メールを送信する、エラー、イベントをロギングするなど何か特別な対応を行うために、このイベントをリスニングできます。

`form`
------

### ~`form.post_configure`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

`form.post_configure` イベントはフォームのコンフィギュレーションが変更されるときに通知されます。

### ~`form.filter_values`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

`form.filter_values` イベントは、バインドする直前の、マージされ、汚染されているパラメータとファイルの配列をフィルタリングします。

### ~`form.validation_error`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

| パラメータ  | 説明
| ------------ | ---------------------
| `error`      | エラーのインスタンス

フォームバリデーションが通らないときに `form.validation_error` イベントはつねに通知されます。

### ~`form.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

| パラメータ  | 説明
| ------------ | ----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfFormSymfony` クラスで定義されていなければ、`form.method_not_found` イベントが通知されます。継承を使わなくても、このイベントをリスニングすることで、クラスにメソッドを追加できます。

`plugin`
--------

### ~`plugin.pre_install`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfPluginManager`

| パラメータ  | 説明
| ------------ | ------------------------------------------------------------------------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前
| `is_package` | ローカルパッケージ (`true`)、もしくは Web 公開パッケージ (`false`) をインストールするかどうか

プラグインがインストールされる直前に `plugin.pre_install` イベントが通知されます。

### ~`plugin.post_install`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfPluginManager`

| パラメータ | 説明
| ------------ | ------------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前

プラグインがインストールされた直後に `plugin.post_install` イベントが通知されます。

### ~`plugin.pre_uninstall`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfPluginManager`

| パラメータ   | 説明
| ------------ | ------------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前

プラグインがアンインストールされる直前に `plugin.pre_uninstall` イベントが通知されます。

### ~`plugin.post_uninstall`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfPluginManager`

| パラメータ  | 説明
| ------------ | -----------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前

プラグインがアンインストールされた直後に `plugin.post_uninstall` イベントが通知されます。

`request`
---------

### ~`request.filter_parameters`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfWebRequest`

| パラメータ   | 説明
| ------------ | -----------------
| `path_info`  | リクエストのパス

リクエストパラメータが初期化されるときに `request.filter_parameters` イベントが通知されます。

### ~`request.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfRequest`

| パラメータ  | 説明
| ----------- | ------------------------------------------
| `method`    | 呼び出されたが見つからないメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfRequest` クラスで定義されていなければ、`request.method_not_found` イベントが通知されます。継承を使わなくても、このイベントをリスニングすることで、クラスにメソッドを追加できます。

`response`
----------

### ~`response.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfResponse`

| パラメータ  | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからないメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfResponse` クラスで定義されていなければ、`response.method_not_found` イベントが通知されます。継承を使わなくても、このメソッドをリスニングすることで、クラスにメソッドを追加できます。

### ~`response.filter_content`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfResponse`

レスポンスが送信される前に `response.filter_content` イベントが通知されます。このイベントをリスニングすることで、送信される前のレスポンスの内容を操作できます。

`routing`
---------

### ~`routing.load_configuration`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfRouting`

ルーティングファクトリがルーティングコンフィギュレーションをロードするときに `routing.load_configuration` イベントが通知されます。

`task`
------

### ~`task.cache.clear`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfCacheClearTask`

| パラメータ | 説明
| ---------- | --------------------------
| `app`      | アプリケーションの名前
| `type`     | キャッシュの種類 (`all`、`config`、`i18n`、`routing`、`module`、そして `template`)
| `env`      | 環境

キャッシュが `cache:clear` タスクによって一掃されるときに `task.cache.clear` イベントが通知されます。

`template`
----------

### ~`template.filter_parameters`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfViewParameterHolder`

ビューファイルがレンダリングされる前に `template.filter_parameters` イベントが通知されます。このイベントをリスニングすることで、テンプレートに渡される変数へのアクセスおよび操作ができます。

`user`
------

### ~`user.change_culture`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfUser`

| パラメータ | 説明
| ----------- | -----------------
| `culture`   | ユーザーカルチャ

リクエストのあいだにユーザーカルチャが変更されるときに `user.change_culture` イベントが通知されます。

### ~`user.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfUser`

| パラメータ | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからないメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfUser` クラスで定義されていなければ、`user.method_not_found` イベントが通知されます。継承を使わなくても、このイベントをリスニングすることで、クラスにメソッドを追加できます。

### ~`user.change_authentication`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfBasicSecurityUser`

| パラメータ      | 説明
| --------------- | ----------------------------------
| `authenticated` | ユーザーが認証されているかどうか

ユーザーの認証ステータスが変更されるたびに `user.change_authentication` イベントが通知されます。

`view`
------

### ~`view.configure_format`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfView`

| パラメータ | 説明
| ---------- | -------------------------------
| `format`   | リクエストされるフォーマット
| `response` | レスポンスオブジェクト
| `request`  | リクエストオブジェクト

リクエストにおいて `sf_format` パラメータセットが存在するときに `view.configure_format` イベントが通知されます。symfony が設定を変更するもしくはレイアウトの設定を解除するなどの単純な作業を行った後にこのイベントが通知されます。このイベントによってリクエストされるフォーマットに合わせてビューとレスポンスオブジェクトを変更することができます。

### ~`view.method_not_found`~

*通知メソッド*: `notifyUntil`

*通知元クラス*: `sfView`

| パラメータ  | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからないメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfView` クラスで定義されていなければ、`view.method_not_found` イベントが通知されます。継承を使わなくても、このイベントをリスニングすることで、クラスにメソッドを追加できます。

`view.cache`
------------

### ~`view.cache.filter_content`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfViewCacheManager`

| パラメータ | 説明
| ---------- | ----------------------------------------------------------
| `response` | レスポンスオブジェクト
| `uri`      | キャッシュ済みのコンテンツの URI
| `new`      | コンテンツがキャッシュのなかで新しいものであるかどうか

`view.cache.filter_content` イベントはキャッシュからコンテンツが読み込まれるときに通知されます。
