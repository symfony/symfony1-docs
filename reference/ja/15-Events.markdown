イベント
========

`sfEventDispatcher` オブジェクトのおかげで symfony のコアコンポーネントは疎結合されています。Event Dispatcher はコアコンポーネントのあいだのコミュニケーションを管理します。

あるオブジェクトがイベントをディスパッチャに通知し、別のオブジェクトが特定のイベントをリスニングするためにディスパッチャに接続できます。

イベントはドット (`.`) で区切られる名前空間と名前で構成される単なる名前です。

使い方
------

最初にイベントオブジェクトを作ることでイベントを通知できます:

    [php]
    $event = new sfEvent($this, 'user.change_culture', array('culture' => $culture));

そしてイベントを通知します:

    $dispatcher->notify($event);

`sfEvent` コンストラクタは3つの引数をとります:

  * イベントの「サブジェクト (対象)」 (ほとんどの場合、これはイベントを通知するオブジェクトですが `null` にもなります)
  * イベントの名前
  * リスナーに渡すパラメータの配列

イベントをリスニングするにはイベントの名前に接続します:

    [php]
    $dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));

`connect` メソッドは2つの引数をとります:

  * イベントの名前
  * イベントが通知されるときに呼び出す PHP callable

リスナーの実装例は次のとおりです:

    [php]
    public function listenToChangeCultureEvent(sfEvent $event)
    {
      // メッセージフォーマットオブジェクトを新しいカルチャで変更する
      $this->setCulture($event['culture']);
    }

リスナーはイベントを最初の引数にとります。イベントオブジェクトはイベントの情報を得るためのメソッドをいくつかもちます:

  * `getSubject()`: イベントにアタッチするサブジェクトオブジェクトを取得します。
  * `getParameters()`: イベントパラメータを返します。

イベントオブジェクトはパラメータを得るために配列としてもアクセスできます。

イベントの種類
--------------

イベントは3つの異なるメソッドで起動できます:

 * `notify()`
 * `notifyUntil()`
 * `filter()`

### ~`notify`~

`notify()` メソッドはすべてのリスナーに通知します。リスナーは値を返すことはできず、すべてのリスナーの実行が保証されています。

### ~`notifyUntil`~

`notifyUntil()` メソッドは `true` の値を返すことで1つのリスナーが連鎖を止めるまですべてのリスナーに通知します。

連鎖を止めるリスナーは `setReturnValue()` メソッドを呼び出すこともできます。

通知オブジェクトは `isProcessed()` メソッドを呼び出すことでリスナーが処理済みのイベントをもっているかチェックできます:

    [php]
    if ($event->isProcessed())
    {
      // ...
    }

### ~`filter`~

`filter()` メソッドは、通知オブジェクトによって2番目の引数として渡される任意の値をフィルタリングし、結果が2番目の引数としてリスナーの callable によって読み出されることを通知します。すべてのリスナーに値が渡され、これらはフィルタリングされた値を返さなければなりません。すべてのリスナーの実行が保証されています。

通知オブジェクトは `getReturnValue()` メソッドを呼び出すことでフィルタリングされた値を得ることができます:

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

`application.log` イベントは Web リクエストのロギングをするために symfony によって利用されるメカニズムです (logger ファクトリを参照)。このイベントは symfony のコアコンポーネントの大半によって通知されます。

### ~`application.throw_exception`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfException`

リクエスト処理のあいだに補足されない例外が投げられるときに `application.throw_exception` イベントが通知されます。

補足されない例外が投げられたときに特別な対応を行うためにこのイベントをリスニングできます (メールを送信する、もしくはエラーをロギングするなど)。イベントを処理することで symfony のデフォルトの例外管理メカニズムをオーバーライドすることもできます。

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

CLI でタスクオプションが解析される前に `command.filter_options` イベントが通知されます。このイベントはユーザーに渡されるオプションをフィルタリングするために使うことができます。

`configuration`
---------------

### ~`configuration.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfProjectConfiguration`

| パラメータ  | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドが `sfProjectConfiguration` クラスで定義されていない場合 `configuration.method_not_found` イベントが通知されます。このイベントをリスニングすることで継承を使わずにメソッドをクラスに追加できます。

`component`
-----------

### ~`component.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfComponent`

| パラメータ | 説明
| ------------ | -----------------------------------
| `method`     | 呼び出されたが見つからないメソッド
| `arguments`  | メソッドに渡される引数

メソッドが `sfComponent` クラスで定義されていないとき `component.method_not_found` イベントが通知されます。このイベントをリスニングすることで継承を使わずにメソッドをクラスに追加できます。

`context`
---------

### ~`context.load_factories`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfContext`

すべてのファクトリが初期化された直後に `sfContext` オブジェクトによってリクエストごとに `context.load_factories` イベントが1回通知されます。これはすべてのコアクラスの初期化で通知される最初のイベントです。

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

メソッドが `sfController` クラスで定義されていないときに `controller.method_not_found` イベントが通知されます。このイベントをリスニングすることで継承を使わずにメソッドをクラスに追加することができます。

### ~`controller.page_not_found`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfController`

| パラメータ  | 説明
| ------------ | ------------------------------------
| `module`     | 404エラーを生成するモジュールの名前
| `action`     | 404エラーを生成するアクションの名前

リクエスト処理のあいだに404エラーが生成されたときに `controller.page_not_found` が通知されます。

404ページが表示されるときに、メールを送信する、エラー、イベントをロギングするなど何か特別な対応を行うためにこのイベントをリスニングできます。

`form`
------

### ~`form.post_configure`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

`form.post_configure` イベントはフォームのコンフィギュレーションが変更されるときに通知されます。

### ~`form.filter_values`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

`form.filter_values` イベントは、バインドする直前の、マージされ、汚染されたパラメータとファイルの配列をフィルタリングします。

### ~`form.validation_error`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

| パラメータ | 説明
| ------------ | ---------------------
| `error`      | エラーのインスタンス

`form.validation_error` イベントはフォームバリデーションに通らないときにつねに通知されます。

### ~`form.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

| パラメータ  | 説明
| ------------ | ----------------------------------------
| `method`     | 呼び出されたが見つからないメソッドの名前
| `arguments`  | メソッドに渡される引数

メソッドが `sfFormSymfony` クラスで定義されていないときに `form.method_not_found` イベントが通知されます。このイベントをリスニングすることで継承を使わずにメソッドをクラスに追加できます。

`plugin`
--------

### ~`plugin.pre_install`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfPluginManager`

| パラメータ  | 説明
| ------------ | ------------------------------------------------------------------------------------
| `channel`    | プラグインのチャンネル
| `plugin`     | プラグインの名前
| `is_package` | ローカルパッケージ (`true`)、もしくは Web パッケージ (`false`) をインストールするかどうか

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

メソッドが `sfRequest` クラスで定義されていないとき `request.method_not_found` イベントが通知されます。このイベントをリスニングすることで継承を使わずにメソッドをクラスに追加できます。

`response`
----------

### ~`response.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfResponse`

| パラメータ  | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからないメソッドの名前
| `arguments` | メソッドに渡される引数

メソッドが `sfResponse` クラスで定義されていないとき `response.method_not_found` イベントが通知されます。このメソッドをリスニングすることで継承を使わずにメソッドをクラスに追加できます。

### ~`response.filter_content`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfResponse`

レスポンスが送信される前に `response.filter_content` イベントが通知されます。このイベントをリスニングすることで送信される前のレスポンスの内容を操作することができます。

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

`cache:clear` タスクでキャッシュが一掃されるときに `task.cache.clear` イベントが通知されます。

`template`
----------

### ~`template.filter_parameters`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfViewParameterHolder`

ビューファイルがレンダリングされる前に `template.filter_parameters` イベントが通知されます。このイベントをリスニングすることでテンプレートに渡される変数へのアクセスおよび操作ができます。

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

メソッドが `sfUser` クラスで定義されていないときに `user.method_not_found` イベントが通知されます。このイベントをリスニングすることで継承を使わずにメソッドをクラスに追加できます。

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

リクエストが `sf_format` パラメータセットをもつとき `view.configure_format` イベントが通知されます。symfony が設定を変更するもしくはレイアウトの設定を解除するなどの単純な作業を行ったあとにこのイベントが通知されます。このイベントによってリクエストされるフォーマットにしたがってビューとレスポンスオブジェクトを変更することができます。

### ~`view.method_not_found`~

*通知メソッド*: `notifyUntil`

*通知元クラス*: `sfView`

| パラメータ  | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからないメソッドの名前
| `arguments` | メソッドに渡される引数

メソッドが `sfView` クラスで定義されていないときに `view.method_not_found` イベントが通知されます。このイベントをリスニングすることで継承を使わずにメソッドをクラスに追加できます。

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
