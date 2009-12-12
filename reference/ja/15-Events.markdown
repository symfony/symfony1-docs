イベント
========

symfony のコアコンポーネントは `sfEventDispatcher` オブジェクトのおかげで疎結合されています。Event Dispatcher はコアコンポーネントのあいだのコミュニケーションを管理します。

オブジェクトはディスパッチャーにイベントを通知し、ほかのオブジェクトは特定のイベントをリスニングするためにディスパッチャーに接続できます。

イベントはドット (`.`) で区切られた名前空間と名前で構成される単なる名前です。

使い方
------

最初にイベントオブジェクトを作ることでイベントを通知できます:

    [php]
    $event = new sfEvent($this, 'user.change_culture', array('culture' => $culture));

そして通知します:

    $dispatcher->notify($event);

`sfEvent` コンストラクターは3つの引数を受け取ります:

  * イベントの "subject" (たいていの場合、これはイベントを通知するオブジェクトだが `null` にもなる)
  * イベントの名前
  * リスナーに渡すパラメーターの配列

イベントをリスニングするには、そのイベントの名前に接続します:

    [php]
    $dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));

`connect` メソッドは2つの引数を受け取ります:

  * イベントの名前
  * イベントが通知されたときに呼び出す PHP callable

リスナーの実装例は次のとおりです:

    [php]
    public function listenToChangeCultureEvent(sfEvent $event)
    {
      // メッセージフォーマットオブジェクトを新しい culture で変更する
      $this->setCulture($event['culture']);
    }

リスナーは最初の引数としてイベントを受け取ります。イベントオブジェクトはイベントの情報を得るためのメソッドをいくつか持ちます:

  * `getSubject()`: イベントに添付する subject オブジェクトを取得する
  * `getParameters()`: イベントパラメーターを返す

イベントオブジェクトはパラメーターを取得するために配列としてもアクセスできます。

イベントの種類
--------------

イベントは3つの異なるメソッドで起動できます:

 * `notify()`
 * `notifyUntil()`
 * `filter()`

### `notify`

`notify()` メソッドはすべてのリスナーを通知します。リスナーは値を返すことはできずすべてのリスナーが実行されることが保証されます。

### `notifyUntil`

`notifyUntil()` メソッドは `true` の値を返すことで1つのリスナーがチェーンを停止させるまですべてのリスナーを通知します。

チェーンを停止させるリスナーは `setReturnValue()` メソッドを呼び出すこともできます。

通知元のオブジェクトはリスナーは `isProcessed()` メソッドを呼び出すことで処理されたイベントが保有されているかチェックできます:

    [php]
    if ($event->isProcessed())
    {
      // ...
    }

### `filter`

`filter()` メソッドはすべてのリスナーを通知します。これらは任意の値をフィルタリング可能で、通知元オブジェクトによって2番目の引数として渡され、3番目の引数としてリスナーの callable によって読み取りされます。すべてのリスナーには値が渡され、これらはフィルタリングされた値を返さなければなりません。すべてのリスナーの実行は保証されます。

通知元オブジェクトは `getReturnValue()` メソッドを呼び出すことでフィルタリングされた値を取得できます:

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

*デフォルトの通知元クラス*: たくさんのクラス

| パラメーター  | 説明
| ------------ | ----------------------------------------------------------------------------------
| `priority`   | 優先順位 (`sfLogger::EMERG`、`sfLogger::ALERT`、`sfLogger::CRIT`、`sfLogger::ERR`、 `sfLogger::WARNING`、`sfLogger::NOTICE`、`sfLogger::INFO` もしくは `sfLogger::DEBUG`)

`application.log` イベントは Web リクエストに対してロギングを行うために symfony によって利用されるメカニズムです(logger ファクトリを参照)。このイベントはたいていの symfony のコアコンポーネントによって通知されます。

### ~`application.throw_exception`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfException`

リクエスト処理のあいだに補足されない例外が投げられるときに `application.throw_exception` イベントが通知されます。

補足されない例外が投げられたときに特別なことを行うためにこのイベントをリスニングできます (メールを送信する、もしくはエラーをロギングするなど)。イベントを処理することで symfony のデフォルトの例外管理メカニズムをオーバーライドすることもできます。

`command`
---------

### ~`command.log`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfCommand` クラス

| パラメーター  | 説明
| ------------ | -----------------------------------------------------------------------------------
| `priority`   | 優先順位(`sfLogger::EMERG`、`sfLogger::ALERT`、`sfLogger::CRIT`、`sfLogger::ERR`、 `sfLogger::WARNING`、`sfLogger::NOTICE`、`sfLogger::INFO` もしくは `sfLogger::DEBUG`)

`command.log` イベントは symfony の CLI ユーティリティでロギングを行うために symfony によって利用されるメカニズムです。(`logger` ファクトリを参照)。

### ~`command.pre_command`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfTask`

| パラメーター | 説明
| ------------ | ------------------------------
| `arguments`  | CLI に渡される引数の配列
| `options`    | CLI に渡されるオプションの配列

タスクの実行直前に `command.pre_command` イベントが通知されます。

### ~`command.post_command`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfTask`

タスクの実行直後に `command.post_command` イベントが通知されます。

### ~`command.filter_options`~

*通知メソッド*: `filter`

*デフォルトの通知元クラス*: `sfTask`

| パラメーター       | 説明
| ----------------- | -------------------------------
| `command_manager` | `sfCommandManager` インスタンス

タスク CLI のオプションが解析される前に `command.filter_options` イベントが通知されます。このイベントはユーザーに渡されるオプションをフィルタリングするために使うことができます。

`configuration`
---------------

### ~`configuration.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfProjectConfiguration`

| パラメーター  | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されるが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドが `sfProjectConfiguration` クラスで定義されていない場合 `configuration.method_not_found` イベントが通知されます。このイベントをリスニングすることで、継承を使わずにクラスにメソッドを追加できます。

`component`
-----------

### ~`component.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfComponent`

| パラメーター | 説明
| ------------ | -----------------------------------
| `method`     | 呼び出されるが見つからないメソッド
| `arguments`  | メソッドに渡される引数

メソッドが `sfComponent` クラスで定義されていないとき `component.method_not_found` イベントが通知されます。このイベントをリスニングすることで、継承を使わずにメソッドをクラスに追加できます。

`context`
---------

### ~`context.load_factories`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfContext`

すべてのファクトリが初期化された直後に `sfContext` オブジェクトによってリクエストごとに `context.load_factories` イベントが1回通知されます。これはすべてのコアクラスの初期化で通知される最初のイベントです。

`controller`
------------

### ~`controller.change_action`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfController`

| パラメーター | 説明
| ------------ | ---------------------------
| `module`     | 実行されるモジュールの名前
| `action`     | 実行されるアクションの名前

アクションが実行される直前に `controller.change_action` が通知されます。

### ~`controller.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfController`

| パラメーター | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されるが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドが `sfController` クラスで定義されていないときに `controller.method_not_found` イベントが通知されます。このイベントをリスニングすることで、継承を使わずにメソッドをクラスに追加することができます。

### ~`controller.page_not_found`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfController`

| パラメーター | 説明
| ------------ | ------------------------------------
| `module`     | 404エラーを生成するモジュールの名前
| `action`     | 404エラーを生成するアクションの名前

リクエスト処理のあいだに404エラーが生成されたときに `controller.page_not_found` が通知されます。

Eメールを送信する、エラー、イベントをロギングするなど、404ページが表示されるときに何か特別なことを行うためにこのイベントをリスニングできます。

`form`
------

### ~`form.post_configure`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfFormSymfony`

`form.post_configure` イベントはフォームが設定されるごとに通知されます。

### ~`form.filter_values`~

*通知メソッド*: `filter`

*デフォルトの通知元クラス*: `sfFormSymfony`

`form.filter_values` イベントは、バインディングする直前の、マージされ、汚染されたパラメーターとファイルの配列をフィルタリングします。

### ~`form.validation_error`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfFormSymfony`

| パラメーター | 説明
| ------------ | ---------------------
| `error`      | エラーのインスタンス

`form.validation_error` イベントはフォームバリデーションが失敗するときに常に通知されます。

### ~`form.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfFormSymfony`

| パラメーター | 説明
| ------------ | ----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

`sfFormSymfony` クラスで定義されていないときに `form.method_not_found` イベントが通知されます。このイベントをリスニングすることで、継承を使わずにメソッドをクラスに追加できます。

`plugin`
--------

### ~`plugin.pre_install`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfPluginManager`

| パラメーター  | 説明
| ------------ | ------------------------------------------------------------------------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前
| `is_package` | ローカルパッケージ (`true`)、もしくは Web パッケージ (`false`) をインストールするかどうか

プラグインがインストールされる直前に `plugin.pre_install` イベントが通知されます。

### ~`plugin.post_install`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfPluginManager`

| パラメーター | 説明
| ------------ | ------------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前

プラグインがインストールされた直後に `plugin.post_install` イベントが通知されます。

### ~`plugin.pre_uninstall`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfPluginManager`

| パラメーター | 説明
| ------------ | ----------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前

プラグインがアンインストールされる直前に `plugin.pre_uninstall` イベントが通知されます。

### ~`plugin.post_uninstall`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfPluginManager`

| パラメーター | 説明
| ------------ | -----------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前

プラグインがアンインストールされた直後に `plugin.post_uninstall` イベントが通知されます。

`request`
---------

### ~`request.filter_parameters`~

*通知メソッド*: `filter`

*デフォルトの通知元クラス*: `sfWebRequest`

| パラメーター | 説明
| ------------ | -----------------
| `path_info`  | リクエストのパス

リクエストパラメーターが初期化されるときに `request.filter_parameters` イベントが通知されます。

### ~`request.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfRequest`

| パラメーター | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドが `sfRequest` クラスで定義されていないときに `request.method_not_found` イベントは通知されます。このイベントをリスニングすることで、継承を使わずにメソッドをクラスに追加できます。

`response`
----------

### ~`response.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfResponse`

| パラメーター | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドで `sfResponse` クラスが定義されていないとき `response.method_not_found` イベントが通知されます。このイベントをリスニングすることで、継承を使わずに、メソッドをクラスに追加できます。

### ~`response.filter_content`~

*通知メソッド*: `filter`

*デフォルトの通知元クラス*: `sfResponse`

レスポンスが送信される前に `response.filter_content` イベントが通知されます。このイベントをリスニングすることで、送信される前にレスポンスの内容を操作できます。

`routing`
---------

### ~`routing.load_configuration`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfRouting`

ルーティングファクトリがルーティングコンフィギュレーションをロードするとき `routing.load_configuration` イベントが通知されます。

`task`
------

### ~`task.cache.clear`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfCacheClearTask`

| パラメーター  | 説明
| ------------ | -------------------------------------------------------------------------------
| `app`        | アプリケーションの名前
| `type`       | キャッシュの種類 (`all`、`config`、`i18n`、`routing`、`module` と `template`)
| `env`        | 環境

ユーザーが `cache:clear` タスクで CLI からキャッシュをクリアするときに `task.cache.clear` イベントが通知されます。

`template`
----------

### ~`template.filter_parameters`~

*通知メソッド*: `filter`

*デフォルトの通知元クラス*: `sfViewParameterHolder`

ビューファイルがレンダリングされる前に `template.filter_parameters` イベントが通知されます。このイベントをリスニングすることでテンプレートに渡される変数にアクセスおよび操作できます。

`user`
------

### ~`user.change_culture`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfUser`

| パラメーター  | 説明
| ------------ | ------------------
| `culture`    | ユーザーの culture

リクエストのあいだにユーザーの culture が変更されるときに `user.change_culture` イベントが通知されます。

### ~`user.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfUser`

| パラメーター | 説明
| ------------ | ------------------------------------------
| `method`     | 呼び出されるが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドが `sfUser` クラスで定義されていないときに、`user.method_not_found` イベントが通知されます。このイベントをリスニングすることで、継承を使わずにメソッドをクラスに追加できます。

### ~`user.change_authentication`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfBasicSecurityUser`

| パラメーター    | 説明
| --------------- | ---------------------------------
| `authenticated` | ユーザーが認証されているかどうか

ユーザーの認証ステータスが変更されるときに `user.change_authentication` イベントが通知されます。

`view`
------

### ~`view.configure_format`~

*通知メソッド*: `notify`

*デフォルトの通知元クラス*: `sfView`

| パラメーター | 説明
| ------------ | -----------------------
| `format`     | リクエストフォーマット
| `response`   | レスポンスオブジェクト
| `request`    | リクエストオブジェクト

リクエストが `sf_format` をセットするとき、ビューによって `view.configure_format` イベントが通知されます。レイアウトを設定するもしくは設定解除するといった単純作業を symfony が行った後でこのイベントが通知されます。このイベントによってリクエストされたフォーマットに従ってビューとレスポンスオブジェクトを変更できます。

### ~`view.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知元クラス*: `sfView`

| パラメーター | 説明
| ------------ | -------------------------------------
| `method`     | 呼び出され見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドが `sfView` クラスで定義されるときに `view.method_not_found` イベントが通知されます。このイベントをリスニングすることで、継承を使わずにメソッドをクラスに追加できます。

`view.cache`
------------

### ~`view.cache.filter_content`~

*通知メソッド*: `filter`

*デフォルトの通知元クラス*: `sfViewCacheManager`

| パラメーター  | 説明
| ------------ | ------------------------------------------------
| `response`   | レスポンスオブジェクト
| `uri`        | キャッシュされるコンテンツの URI
| `new`        | コンテンツがキャッシュのなかで新しいものかどうか

コンテンツがキャッシュから読み込まれるときに `view.cache.filter_content` イベントが通知されます。
