イベント
========

symfony のコアコンポーネントは `sfEventDispatcher` オブジェクトによって疎結合されています。イベントディスパッチャ (Event Dispatcher) はコアコンポーネントのあいだのコミュニケーションを司ります。

あるオブジェクトがディスパッチャにイベントを通知すれば、ディスパッチャに接続しているほかのオブジェクトがそのイベントをリスニングできるようになります。

イベントは単なる名前で、ドット (`.`) で区切られた名前空間と名前からなります。

使いかた
------

最初にイベントオブジェクトを作ります。

    [php]
    $event = new sfEvent($this, 'user.change_culture', array('culture' => $culture));

そしてディスパッチャにイベントを通知させます。

    $dispatcher->notify($event);

`sfEvent` コンストラクタは3つの引数をとります。

  * イベントの「サブジェクト (対象)」 (ほとんどの場合、これはイベントを通知するオブジェクトになりますが、`null` にもなります)
  * イベントの名前
  * リスナーに渡すパラメータの配列

リスナーがイベントをリスニングできるようにするために、リスナーをディスパッチャに接続させます。

    [php]
    $dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));

ディスパッチャの `connect` メソッドは2つの引数をとります。

  * イベントの名前
  * イベントが通知されるときに呼び出される関数/メソッド 

リスナーの実装例は次のようになります。

    [php]
    public function listenToChangeCultureEvent(sfEvent $event)
    {
      // メッセージフォーマットオブジェクトを新しいカルチャで変更します
      $this->setCulture($event['culture']);
    }

リスナーはイベントを第1引数にとります。イベントオブジェクトにはイベント情報を提供するためのメソッドがいくつか備わっています。

  * `getSubject()`: イベントにアタッチされているサブジェクトオブジェクトを取得します。
  * `getParameters()`: イベントパラメータを返します。

配列方式によるイベントオブジェクトへのアクセス方法も用意されています。

イベントの種類
--------------

イベントは3つの異なるメソッドによって作られます。

 * `notify()`
 * `notifyUntil()`
 * `filter()`

### ~`notify`~

`notify()` メソッドはすべてのリスナーに通知します。リスナーは値を返すことはできません。すべてのリスナーの実行は保証されています。

### ~`notifyUntil`~

1つのリスナーが `true` の値を返されることで、チェーンが止まるまで、`notifyUntil()` メソッドはすべてのリスナーに通知しつづけます。

チェーンを止めるリスナーは `setReturnValue()` メソッドを呼び出すこともできます。

リスナーが処理済みのイベントをもっていることをチェックするには、通知オブジェクトのなかで `isProcessed()` メソッドを呼び出します。

    [php]
    if ($event->isProcessed())
    {
      // ...
    }

### ~`filter`~

`filter()` メソッドは、通知オブジェクトから第2引数に渡される任意の値にフィルタをかけ、リスナーの第2引数に渡される関数/メソッドによって結果が取り出されたことを通知します。すべてのリスナーは受け取った値にフィルタをかけて返さなければなりません。すべてのリスナーの実行は保証されています。

通知オブジェクトは `getReturnValue()` メソッドを呼び出すことで、フィルタ処理済みの値を得ることができます。

    [php]
    $ret = $event->getReturnValue();

<div class="pagebreak"></div>

イベント
-------

 * [`application`](#chapter_15_application)
   * [`application.log`](#chapter_15_sub_application_log)
   * [`application.throw_exception`](#chapter_15_sub_application_throw_exception)
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
   * [`context.method_not_found`](#chapter_15_sub_context_method_not_found)
 * [`controller`](#chapter_15_controller)
   * [`controller.change_action`](#chapter_15_sub_controller_change_action)
   * [`controller.method_not_found`](#chapter_15_sub_controller_method_not_found)
   * [`controller.page_not_found`](#chapter_15_sub_controller_page_not_found)
 * [`debug`](#chapter_15_debug)
   * [`debug.web.load_panels`](#chapter_15_sub_debug_view_load_panels)
   * [`debug.web.view.filter_parameter_html`](#chapter_15_sub_debug_web_view_filter_parameter_html)
 * [`doctrine`](#chapter_15_doctrine)
   * [`doctrine.configure`](#chapter_15_sub_doctrine_configure)
   * [`doctrine.filter_model_builder_options`](#chapter_15_sub_doctrine_filter_model_builder_options)
   * [`doctrine.filter_cli_config`](#chapter_15_sub_doctrine_filter_cli_config)
   * [`doctrine.configure_connection`](#chapter_15_sub_doctrine_configure_connection)
   * [`doctrine.admin.delete_object`](#chapter_15_sub_doctrine_delete_object)
   * [`doctrine.admin.save_object`](#chapter_15_sub_doctrine_save_object)
   * [`doctrine.admin.build_query`](#chapter_15_sub_doctrine_build_query)
   * [`doctrine.admin.pre_execute`](#chapter_15_sub_doctrine_pre_execute)
 * [`form`](#chapter_15_form)
   * [`form.post_configure`](#chapter_15_sub_form_post_configure)
   * [`form.filter_values`](#chapter_15_sub_form_filter_values)
   * [`form.validation_error`](#chapter_15_sub_form_validation_error)
   * [`form.method_not_found`](#chapter_15_sub_form_method_not_found)
 * [`mailer`](#chapter_15_mailer)
   * [`mailer.configure`](#chapter_15_sub_mailer_configure)
 * [`plugin`](#chapter_15_plugin)
   * [`plugin.pre_install`](#chapter_15_sub_plugin_pre_install)
   * [`plugin.post_install`](#chapter_15_sub_plugin_post_install)
   * [`plugin.pre_uninstall`](#chapter_15_sub_plugin_pre_uninstall)
   * [`plugin.post_uninstall`](#chapter_15_sub_plugin_post_uninstall)
 * [`propel`](#chapter_15_propel)
   * [`propel.configure`](#chapter_15_sub_propel_configure)
   * [`propel.filter_phing_args`](#chapter_15_sub_propel_filter_phing_args)
   * [`propel.filter_connection_config`](#chapter_15_sub_propel_filter_connection_config)
   * [`propel.admin.delete_object`](#chapter_15_sub_propel_admin_delete_object)
   * [`propel.admin.save_object`](#chapter_15_sub_propel_admin_save_object)
   * [`propel.admin.build_criteria`](#chapter_15_sub_propel_admin_build_criteria)
   * [`propel.admin.pre_execute`](#chapter_15_sub_propel_admin_pre_execute)
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

*デフォルトの通知オブジェクト*: さまざまなクラス

| パラメータ  | 説明
| ------------ | ----------------------------------------------------------------------------------
| `priority`   | 優先順位 (`sfLogger::EMERG`、`sfLogger::ALERT`、`sfLogger::CRIT`、`sfLogger::ERR`、 `sfLogger::WARNING`、`sfLogger::NOTICE`、`sfLogger::INFO` もしくは `sfLogger::DEBUG`)

`application.log` イベントは HTTP リクエストのロギングシステムに利用されています (logger ファクトリをご参照ください)。さまざまな symfony のコアコンポーネントがこのイベントを通知します。

### ~`application.throw_exception`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfException`

リクエスト処理のあいだに捕まえられない例外が投げられたときに `application.throw_exception` イベントが通知されます。

このイベントをリスニングしていれば、捕まえられない例外が投げられた場合に、メールを送信する、もしくはエラーログに記録するなどの措置を講じることができます。イベントを扱うことで、symfony におけるデフォルトの例外管理メカニズムをオーバーライドすることもできます。

`command`
---------

### ~`command.log`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfCommand*` クラス

| パラメータ  | 説明
| ------------ | -----------------------------------------------------------------------------------
| `priority`   | 優先順位 (`sfLogger::EMERG`、`sfLogger::ALERT`、`sfLogger::CRIT`、`sfLogger::ERR`、 `sfLogger::WARNING`、`sfLogger::NOTICE`、`sfLogger::INFO` もしくは `sfLogger::DEBUG`)

`command.log` イベントは symfony CLI ユーティリティによるロギングにも利用できます (`logger` ファクトリをご参照ください)。

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

| パラメータ        | 説明
| ----------------- | -------------------------------
| `command_manager` | `sfCommandManager` のインスタンス

タスクオプションが CLI によってパースされる前に `command.filter_options` イベントが通知されます。このイベントはユーザーに渡すオプションにフィルタをかけます。

`configuration`
---------------

### ~`configuration.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfProjectConfiguration`

| パラメータ  | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されたが見つからなかったメソッドの名前
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfProjectConfiguration` クラスで定義されていなければ、`configuration.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

`component`
-----------

### ~`component.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfComponent`

| パラメータ   | 説明
| ------------ | -----------------------------------
| `method`     | 呼び出されたが見つからなかったメソッド
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfComponent` クラスで定義されていなければ、`component.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

`context`
---------

### ~`context.load_factories`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfContext`

すべてのファクトリが初期化された直後から、リクエストが来るたびに、`sfContext` オブジェクトによって `context.load_factories` イベントが1回通知されます。すべてのコアクラスが初期化された際にこのイベントが最初に通知されます。

### `context.method_not_found`

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfContext`

| パラメータ  | 説明
| ----------- | -----------------------------------
| `method`    | 呼び出されたが見つからなかったメソッド
| `arguments` | メソッドに渡される引数

`sfContext` クラスで定義されていないメソッドが呼び出された際に `context.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

`controller`
------------

### ~`controller.change_action`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfController`

| パラメータ | 説明
| ------------ | ---------------------------
| `module`     | 実行されるモジュールの名前
| `action`     | 実行されるアクションの名前

アクションが実行される直前に `controller.change_action` イベントが通知されます。

### ~`controller.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfController`

| パラメータ | 説明
| ------------ | -----------------------------------------
| `method`     | 呼び出されたが見つからなかったメソッドの名前
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfController` クラスで定義されていなければ、`controller.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

### ~`controller.page_not_found`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfController`

| パラメータ  | 説明
| ------------ | ------------------------------------
| `module`     | 404エラーが発生したモジュールの名前
| `action`     | 404エラーが発生したアクションの名前

リクエスト処理のあいだに404エラーが発生したときに `controller.page_not_found` イベントが通知されます。

このイベントをリスニングしていれば、404ページが表示されるときに、メールを送信する、エラー、イベントのログをとるなどの措置を講じることができます。

`debug`
-------

### `debug.web.load_panels`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfWebDebug`

`sfWebDebug` インスタンスの `configure()` メソッドを呼び出した後に、`debug.web.load_panels` イベントが通知されます。このイベントをパネルの管理に使うことができます。

### `debug.web.view.filter_parameter_html`

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfWebDebugPanelView`

| パラメータ  | 説明
| ----------- | -----------------------------
| `parameter` | フィルタをかけるパラメータ

`debug.web.view.filter_parameter_html` イベントは `sfWebDebugPanelView` パネルによってレンダリングされるそれぞれのパラメータにフィルタをかけます。

`doctrine`
----------

### `doctrine.configure`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfDoctrinePluginConfiguration`

Doctrine プラグインのコンフィギュレーションが変更された後で `doctrine.configure` イベントが通知されます。

### `doctrine.filter_model_builder_options`

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfDoctrinePluginConfiguration`

`doctrine.filter_model_builder_options` イベントは Doctrine スキーマビルダーのオプションにフィルタをかけます。

### `doctrine.filter_cli_config`

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfDoctrinePluginConfiguration`

`doctrine.filter_cli_config` イベントは Doctrine CLI のコンフィギュレーション配列にフィルタをかけます。

### `doctrine.configure_connection`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `Doctrine_Manager` (`sfDoctrineDatabase`)

| パラメータ   | 説明
| ------------ | --------------------------------------
| `connection` | `Doctrine_Connection` のインスタンス
| `database`   | `sfDoctrineDatabase` のインスタンス

Doctrine のデータベースオブジェクトがはじめて初期化されたときに `doctrine.configure_connection` イベントが通知されます。

### `doctrine.admin.delete_object`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: アドミンジェネレータのモジュールクラス

| パラメータ | 説明
| --------- | ------------------------------
| `object`  | 削除された Doctrine オブジェクト

アドミンジェネレータモジュールのなかで Doctrine オブジェクトが削除されたときに `doctrine.admin.delete_object` イベントが通知されます。

### `doctrine.admin.save_object`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: アドミンジェネレータモジュールのクラス

| パラメータ | 説明
| --------- | --------------------------------
| `object`  |保存された Doctrine オブジェクト

アドミンジェネレータモジュールのなかで Doctrine オブジェクトが保存されたときに `doctrine.admin.save_object` イベントが通知されます。

### `doctrine.admin.build_query`

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: アドミンジェネレータモジュールのクラス

アドミンジェネレータモジュールのなかで Doctrine Query オブジェクトが生成されたときに `doctrine.admin.build_query` イベントが通知されます。

### `doctrine.admin.pre_execute`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: アドミンジェネレータモジュールのクラス

| パラメータ        | 説明
| ---------------- | -----------
| `configuration`  | アドミンジェネレータのコンフィギュレーションオブジェクト

アドミンジェネレータモジュールの `preExecute()` メソッドが呼び出されたときに `doctrine.admin.pre_execute` イベントが通知されます。

`form`
------

### ~`form.post_configure`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

`form.post_configure` イベントはフォームのコンフィギュレーションが変更されたときに通知されます。

### ~`form.filter_values`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfFormSymfony`

`form.filter_values` イベントは、バインドされる直前の、マージされ、汚染されているパラメータとファイルの配列にフィルタをかけます。

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
| `method`     | 呼び出されたが見つからなかったメソッドの名前
| `arguments`  | メソッドに渡される引数

呼び出されたメソッドが `sfFormSymfony` クラスで定義されていなければ、`form.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

`mailer`
--------

### `mailer.configure`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfMailer`

メーラーのコンフィギュレーションが変更された後で `mailer.configure` イベントが通知されます。メーラーのインスタンスはイベントのサブジェクトです。

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

`propel`
--------

### `propel.configure`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfPropelPluginConfiguration`

Propel プラグインのコンフィギュレーションが変更された後で `propel.configure` イベントが通知されます。

### `propel.filter_phing_args`

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfPropelBaseTask`

`propel.filter_phing_args` イベントは Propel CLI のコンフィギュレーション配列にフィルタをかけます。

### `propel.filter_connection_config`

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfPropelDatabase`

| パラメータ   | 説明
| ------------ | ----------------------------------
| `name`       | コネクションの名前
| `database`   | `sfPropelDatabase` のインスタンス

Propel
データベースが最初に初期化されたときに `propel.filter_connection_config` イベントが通知されます。

### `propel.admin.delete_object`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: アドミンジェネレータモジュールのクラス

| パラメータ | 説明
| --------- | -----------
| `object`  | 削除された Propel オブジェクト

Propel オブジェクトが削除されたときに `propel.admin.delete_object` イベントが通知されます。

### `propel.admin.save_object`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: アドミンジェネレータモジュールのクラス

| パラメータ | 説明
| --------- | ------------------------------
| `object`  | 保存された Propel オブジェクト

アドミンジェネレータモジュールのなかで Propel オブジェクトが保存されたときに `propel.admin.save_object` イベントが通知されます。

### `propel.admin.build_criteria`

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: アドミンジェネレータモジュールのクラス

アドミンジェネレータモジュールのなかで Propel の Criteria が生成されたときに `propel.admin.build_criteria` イベントが通知されます。

### `propel.admin.pre_execute`

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: アドミンジェネレータモジュールクラス

| パラメータ       | 説明
| ---------------- | ---------------------------------------------------------
| `configuration`  | アドミンジェネレータのコンフィギュレーションオブジェクト

アドミンジェネレータモジュールの `preExecute()` メソッドが呼び出されたときに `propel.admin.pre_execute` イベントが通知されます。

`request`
---------

### ~`request.filter_parameters`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfWebRequest`

| パラメータ   | 説明
| ------------ | -----------------
| `path_info`  | リクエストのパス

リクエストパラメータが初期化されたときに `request.filter_parameters` イベントが通知されます。

### ~`request.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfRequest`

| パラメータ  | 説明
| ----------- | ------------------------------------------
| `method`    | 呼び出されたが見つからなかったメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfRequest` クラスで定義されていなければ、`request.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

`response`
----------

### ~`response.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfResponse`

| パラメータ  | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからなかったメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfResponse` クラスで定義されていなければ、`response.method_not_found` イベントが通知されます。このメソッドをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

### ~`response.filter_content`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfResponse`

レスポンスが送信される前に `response.filter_content` イベントが通知されます。このイベントをリスニングしていれば、送信される前のレスポンスの内容に手を加えることができます。

`routing`
---------

### ~`routing.load_configuration`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfRouting`

ルーティングファクトリがルーティングコンフィギュレーションをロードしたときに `routing.load_configuration` イベントが通知されます。

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

キャッシュが `cache:clear` タスクによって一掃されたときに `task.cache.clear` イベントが通知されます。

`template`
----------

### ~`template.filter_parameters`~

*通知メソッド*: `filter`

*デフォルトの通知オブジェクト*: `sfViewParameterHolder`

ビューファイルがレンダリングされる前に `template.filter_parameters` イベントが通知されます。このイベントをリスニングしていれば、テンプレートに渡される変数にアクセスして、変数に収められている値を書き換えることができます。

`user`
------

### ~`user.change_culture`~

*通知メソッド*: `notify`

*デフォルトの通知オブジェクト*: `sfUser`

| パラメータ | 説明
| ----------- | -----------------
| `culture`   | ユーザーカルチャ

リクエストのあいだにユーザーカルチャが変更されたときに `user.change_culture` イベントが通知されます。

### ~`user.method_not_found`~

*通知メソッド*: `notifyUntil`

*デフォルトの通知オブジェクト*: `sfUser`

| パラメータ | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからなかったメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfUser` クラスで定義されていなければ、`user.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

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
| `format`   | リクエストされたフォーマット
| `response` | レスポンスオブジェクト
| `request`  | リクエストオブジェクト

リクエストにおいて `sf_format` パラメータセットが存在しているときに `view.configure_format` イベントが通知されます。symfony が設定を変更するもしくはレイアウトの設定を解除するなどの処理を施した後でこのイベントが通知されます。このイベントによってリクエストされたフォーマットに応じてビューとレスポンスオブジェクトを変更することができます。

### ~`view.method_not_found`~

*通知メソッド*: `notifyUntil`

*通知元クラス*: `sfView`

| パラメータ  | 説明
| ----------- | -------------------------------------------
| `method`    | 呼び出されたが見つからなかったメソッドの名前
| `arguments` | メソッドに渡される引数

呼び出されたメソッドが `sfView` クラスで定義されていなければ、`view.method_not_found` イベントが通知されます。このイベントをリスニングしていれば、継承を使わなくても、クラスにメソッドを追加できます。

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

`view.cache.filter_content` イベントはキャッシュからコンテンツが読み込まれるたびに通知されます。
