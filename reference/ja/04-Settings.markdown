settings.yml 設定ファイル
=========================

symfony のほとんどは YAML もしくはプレーンな PHP で書かれた設定ファイルをとおして設定できます。このセクションでは、アプリケーションのメインの設定ファイルである、`settings.yml` を説明します。

アプリケーションのメインの `settings.yml` 設定ファイルは `apps/APP_NAME/config/` ディレクトリで見つかります。

第3章で説明したように、`settings.yml` ファイルは[**環境を認識し**](#chapter_03_environment_awareness)、[**コンフィギュレーションカスケードのメカニズム**](#chapter_03_configuration_cascade)が有効になります。

それぞれの環境は2つのサブセクション: `.actions` と `.settings` を持ちます。共通ページにレンダリングされるデフォルトのアクション以外、すべての設定ディレクティブは `.settings` サブセクションの下で機能します。

>**NOTE**
>`settings.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; プロセスは ~`sfDefineEnvironmentConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

<div class="pagebreak"></div>

設定
----

  * `.actions`

    * [`error_404`](#chapter_04_sub_error_404)
    * [`login`](#chapter_04_sub_login)
    * [`secure`](#chapter_04_sub_secure)
    * [`module_disabled`](#chapter_04_sub_module_disabled)

  * `.settings`

    * [`cache`](#chapter_04_sub_cache)
    * [`charset`](#chapter_04_sub_charset)
    * [`check_lock`](#chapter_04_sub_check_lock)
    * [`compressed`](#chapter_04_sub_compressed)
    * [`csrf_secret`](#chapter_04_sub_csrf_secret)
    * [`default_culture`](#chapter_04_sub_default_culture)
    * [`default_timezone`](#chapter_04_sub_default_timezone)
    * [`enabled_modules`](#chapter_04_sub_enabled_modules)
    * [`error_reporting`](#chapter_04_sub_error_reporting)
    * [`escaping_strategy`](#chapter_04_sub_escaping_strategy)
    * [`escaping_method`](#chapter_04_sub_escaping_method)
    * [`etag`](#chapter_04_sub_etag)
    * [`i18n`](#chapter_04_sub_i18n)
    * [`lazy_cache_key`](#chapter_04_sub_lazy_cache_key)
    * [`file_link_format`](#chapter_04_sub_file_link_format)
    * [`logging_enabled`](#chapter_04_sub_logging_enabled)
    * [`no_script_name`](#chapter_04_sub_no_script_name)
    * [`max_forwards`](#chapter_04_sub_max_forwards)
    * [`standard_helpers`](#chapter_04_sub_standard_helpers)
    * [`use_database`](#chapter_04_sub_use_database)
    * [`web_debug`](#chapter_04_sub_web_debug)
    * [`web_debug_web_dir`](#chapter_04_sub_web_debug_web_dir)

<div class="pagebreak"></div>

`.actions` サブセクション
-------------------------

*デフォルトコンフィギュレーション*:

    [yml]
    default:
      .actions:
        error_404_module:       default
        error_404_action:       error404

        login_module:           default
        login_action:           login

        secure_module:          default
        secure_action:          secure

        module_disabled_module: default
        module_disabled_action: disabled

`.actions` サブセクションは共通のページがレンダリングされるときに実行するアクションを定義します。それぞれの定義は2つのコンポーネントを収めます: ひとつはモジュール向け (サフィックスは `_module`)、もうひとつはアクション向けです (サフィックスは`_action`)。

### ~`error_404`~

`error_404` アクションは404ページがレンダリングされるときに実行されます。

### ~`login`~

`login` アクションは認証されていないユーザーがセキュアなページにアクセスしようとするときに実行されます。

### ~`secure`~

`secure` アクションはユーザーが必須のクレデンシャルを持たないときに実行されます。

### ~`module_disabled`~

`module_disabled` アクションはユーザーが無効なモジュールをリクエストするときに実行されます。

`.settings` サブセクション
--------------------------

`.settings` サブセクションはフレームワークの設定が行われるところです。下記のパラグラフはすべての可能な設定を説明し、重要度順におおまかに並べています。

`.settings` セクションで定義されるすべての設定は `sfConfig` オブジェクトを使いプレフィックスの `sf_` をつけることでコードの任意の場所で利用可能です。たとえば、`charset` 設定の値を得るには、次のコードを使います:

    [php]
    sfConfig::get('sf_charset');

### ~`escaping_strategy`~

*デフォルト*: `on`

`escaping_strategy` 設定は出力エスケーパーサブフレームワークが有効であるかどうかを決定するブール値の設定です。有効なとき、テンプレートのなかで利用可能なすべての変数は `escaping_method` 設定で定義されるヘルパー関数を呼び出すことで自動的にエスケープされます (下記を参照)。

`escaping_method` 設定は symfony によって使われるデフォルトのヘルパーであることに注意してください。しかしこれはたとえばJavaScript スクリプトのタグで変数を出力するときなど、ケースバイケースでオーバーライドできます。

出力エスケーパーサブフレームワークはエスケープに `charset` 設定を使います。

デフォルトの値を `on` のままにしておくことを強くおすすめします。
    
>**TIP**
>`generate:app` タスクでアプリケーションを作成するときに `--escaping-strategy` オプションを指定することでこの設定は自動的にセットできます。


### ~`escaping_method`~

*デフォルト*: `ESC_SPECIALCHARS`

`escaping_method` 設定はテンプレートでエスケープするために使うデフォルト関数を定義します (上記の `escaping_strategy` 設定を参照)。

組み込み関数のひとつ: ~`ESC_SPECIALCHARS`~、~`ESC_RAW`~、~`ESC_ENTITIES`~、~`ESC_JS`~、~`ESC_JS_NO_ENTITIES`~ と~`ESC_SPECIALCHARS`~ を選ぶ、もしくは独自関数を作ることができます。

たいていの場合、デフォルトの値で十分です。英語もしくはヨーロッパの言語のみ扱う場合のみ `ESC_ENTITIES` ヘルパーも使うことができます。

### ~`csrf_secret`~

*デフォルト*: ランダムに生成される秘密の文字列

`csrf_secret` 設定はアプリケーションの一意的な秘密の文字列です。`false` にセットされていない場合、これはフォームフレームワークで定義されるすべてのフォームで CSRF 防止機能を有効にします。リンクをフォームに変換することが必要なとき (たとえば HTTP `DELETE` メソッドをシミュレートする)、この設定は `link_to()` ヘルパーにも使われます。

デフォルトの値をあなたが選んだ一意の秘密の文字列に変更することを強くおすすめします。

>**TIP**
>`--csrf-secret` オプションを指定して `generate:app` でアプリケーションを作る際にこの設定は自動的にセットされます

### ~`charset`~

*デフォルト*: `utf-8`

`charset` 設定はフレームワークにおいてレスポンスの `Content-Type` ヘッダーから出力エスケーピング機能までのどこでも使われる文字集合です。

たいていの場合、デフォルトで十分です。

>**WARNING**
>この設定はフレームワークの多くの場所で使われるので、この値は複数の場所で保存されます。これを変更した後では、開発環境であってもコンフィギュレーションキャッシュをクリアしなければなりません。

### ~`enabled_modules`~

*デフォルト*: `[default]`

`enabled_modules` 設定はこのアプリケーションで有効なモジュール名の配列です。デフォルトでは、プラグインもしくは symfony コアで定義されるモジュールは有効ではなく、アクセスできるようにこの設定のリストに含めなければなりません。

モジュールの追加方法はシンプルで名前をリストに追加するだけです (モジュールの順序は問題にはなりません):

    [yml]
    enabled_modules: [default, sfGuardAuth]

フレームワークで定義される `default` モジュールはサブセクションの `settings.yml` の `.actions` でセットされるすべてのデフォルトのアクションを収めます。これらすべてをカスタマイズし、この設定から `default` モジュールを削除することをおすすめします。

### ~`default_timezone`~

*デフォルト*: なし

`default_timezone` 設定は PHP で使われるデフォルトのタイムゾーンを定義します。PHP で認識される任意の[タイムゾーン](http://www.php.net/manual/class.datetimezone.php)になります。


>**NOTE**
>タイムゾーンが定義されていない場合、`php.ini` ファイルで定義することをおすすめします。そうでなければ、symfony は PHP の [`date_default_timezone_get()`](http://www.php.net/date_default_timezone_get) 関数を呼び出すことでベストのタイムゾーンを推測しようとします。

### ~`cache`~

*デフォルト*: `off`

`cache` 設定はテンプレートキャッシュを有効もしくは無効にする。

>**TIP**
>キャッシュシステムの一般設定は `factories.yml` 設定ファイルの [`view_cache_manager`](#chapter_05_view_cache_manager) と [`view_cache`](#chapter_05_view_cache) セクションで行われます。きめ細かい設定は [`cache.yml`](#chapter_09) 設定ファイルで行います。

### ~`etag`~

*デフォルト*: `dev` と `test` 環境を除いて、デフォルトでは `on`

`etag` 設定は HTTP の `ETag` ヘッダーの自動生成を有効もしくは無効にします。symfony によって生成される ETag はレスポンスのコンテンツの単純な md5 です。

### ~`i18n`~

*デフォルト*: `off`

`i18n` 設定は国際化サブフレームワークを有効もしくは無効にするブール値です。アプリケーションを国際化する場合、`on` にセットします。

>**TIP**
>国際化システムの一般設定は `factories.yml` 設定ファイルの [`i18n`](#chapter_05-Factories_sub_i18n) セクションで行われます。

### ~`default_culture`~

*デフォルト*: `en`

`default_culture` 設定は国際化サブフレームワークで使われるデフォルトの culture を定義します。これは任意の有効な culture になります。

### ~`standard_helpers`~

*デフォルト*: `[Partial, Cache, Form]`

`standard_helpers` 設定はすべてのテンプレート用にロードされるヘルパーグループの配列です (サフィックスの `Helper` を持たないヘルパーグループの名前)。

### ~`no_script_name`~

*デフォルト*: `on` は最初に作成されるアプリケーションの `prod` 環境用に、`off` はその他すべて

`no_script_name` 設定はフロントコントローラースクリプトの名前が生成 URL に追加されるかどうかを決定します。デフォルトでは、最初に作成されるアプリケーションの `prod` 環境のために `generate:app` タスクによってこれは `on` にセットされます。

すべてのフロントコントローラーが同じディレクトリ (`web/`) にある場合、あきらかに、ひとつのアプリケーションと環境のみがこの設定を `on` にセットできます。`no_script_name` を `on` にセットしたアプリケーションが複数ほしいのであれば、対応するフロントコントローラーを Web 公開ディレクトリのサブディレクトリのなかに移動させます。

### ~`lazy_cache_key`~

*デフォルト*: 新しいプロジェクトでは `on`、アップグレードしたプロジェクトでは `off`

これが有効なとき、`lazy_cache_key` 設定はアクションもしくはパーシャルがキャッシュ可能になるまでキャッシュキーの作成を遅延させます。テンプレートパーシャルの使い方によって、これは大きなパフォーマンスの改善になります。

### ~`file_link_format`~

*デフォルト*: なし

デバッグメッセージにおいて、`sf_file_link_format` もしくは PHP の `xdebug.file_link_format` 設定の値がセットされている場合、ファイルパスがクリック可能なリンクになります。たとえば、TextMate でファイルを開きたい場合、次の値を使います:

    [yml]
    txmt://open?url=file://%f&line=%l

`%f` プレースホルダーはファイルの絶対パスに置き換えられ `%l` プレースホルダーは行番号に置き換えられます。

### ~`logging_enabled`~

*デフォルト*: `prod` 以外のすべての環境に対して `on`

`logging_enabled` 設定はロギングサブフレームワークを有効にします。これを `false` に設定することでロギングメカニズムを回避し少しパフォーマンスが向上します。

>**TIP**
>ロギングのきめ細かい設定は `factories.yml` 設定ファイルで行います。

### ~`web_debug`~

*デフォルト*: `dev` 以外のすべての環境に対して `off`

`web_debug` 設定は Web デバッグツールバーを有効にします。レスポンスの Content-Type が HTML であるときに Web デバッグツールバーがページに注入されます。

### ~`error_reporting`~

*デフォルト*:

  * `prod`:  E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR
  * `dev`:   E_ALL | E_STRICT
  * `test`:  (E_ALL | E_STRICT) ^ E_NOTICE
  * デフォルト: E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR

`error_reporting` 設定は (ブラウザーに表示されログに書き込まれる) PHP のエラーレポートのレベルをコントロールします。

>**TIP**
>[ビット演算子](http://www.php.net/language.operators.bitwise) の使い方に関する情報は PHP の公式サイトにあります。

デフォルト設定はもっとも利にかなったものであり、変えるべきではありません。

>**NOTE**
>`prod` 環境のフロントコントローラーは `debug` を無効にしておりブラウザーのエラー表示は自動的に無効になります。

### ~`compressed`~

*デフォルト*: `off`

`compressed` 設定は PHP ネイティブなレスポンス圧縮を有効にします。`on` にセットされている場合、symfony は `ob_start()` のコールバック関数として [`ob_gzhandler`](http://www.php.net/ob_gzhandler) を使います。

これは `off` のままにしておいて、代わりに Web サーバーのネイティブな圧縮メカニズムを使うことをおすすめします。

### ~`use_database`~

*デフォルト*: `on`

`use_database` はアプリケーションがデータベースを使うかどうかを決定します。

### ~`check_lock`~

*デフォルト*: `off`

`check_lock` 設定は `cache:clear` と `project:disable` のようなタスクによって実行されるアプリケーションのロックシステムを有効もしくは無効にします。

`on` に設定される場合、無効なアプリケーションへのすべてのリクエストは symfony コアの `lib/exception/data/unavailable.php` ページに自動的にリダイレクトされます。

>**TIP**
>`config/unavailable.php` ファイルをプロジェクトもしくはアプリケーションに追加することでアプリケーションが無効なときん表示されるページのデフォルトテンプレートをオーバーライドできます。

### ~`web_debug_web_dir`~

*デフォルト*: `/sf/sf_web_debug`

`web_debug_web_dir` は Web デバッグツールバーのアセットへの Web パスをセットします (画像、スタイルシート、と  JavaScript ファイル)。
