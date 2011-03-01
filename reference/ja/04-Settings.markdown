settings.yml 設定ファイル
=========================

symfony のコンフィギュレーションの大半は YAML もしくはプレーンな PHP で書かれた設定ファイルを通じて変更できます。この章では `settings.yml` ファイルを説明します。

アプリケーションの `settings.yml` ファイルは `apps/APP_NAME/config/` ディレクトリに配置されています。

[設定ファイルの原則の章](#chapter_03)で述べたように、`settings.yml` ファイルでは、**環境**が認識され、**コンフィギュレーションカスケード**のメカニズムがはたらいています。

それぞれの環境には2つのサブセクション (`.actions` と `.settings`) が設けられています。共通ページにおいてレンダリングされるデフォルトのアクション以外、すべてのコンフィギュレーションディレクティブは `.settings` サブセクションにとりそろえられています。

>**NOTE**
>`settings.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は ~`sfDefineEnvironmentConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)にゆだねられます。

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

`.actions` サブセクションは共通ページがレンダリングされる際に実行されるアクションを定義します。それぞれの定義には2つの要素があり、1つはモジュール (サフィックスは `_module`) で、もう1つはアクション (サフィックスは `_action`) です。

### ~`error_404`~

`error_404` アクションは404ページがレンダリングされる際に実行されます。

### ~`login`~

`login` アクションは認証されていないユーザーが認証を必要とするページにアクセスしようとすると実行されます。

### ~`secure`~

`secure` アクションはアクセスしてきたユーザーが必須のクレデンシャルをもっていない場合に実行されます。

### ~`module_disabled`~

`module_disabled` アクションはユーザーが無効なモジュールをリクエストした際に実行されます。

`.settings` サブセクション
--------------------------

`.settings` サブセクションではフレームワークの設定を調整します。下記の段落では、利用可能なすべての設定項目を説明します。設定項目はおおまかな重要度順で並べられています。

`.settings` セクションで定義されているすべての設定項目の名前は設定の名前にプレフィックスの `sf_` をつけたものであり、`sfConfig` オブジェクトを通じて任意の場所で利用できます。たとえば `charset` 設定の値を得るには、コードを次のように書きます。

    [php]
    sfConfig::get('sf_charset');

### ~`escaping_strategy`~

*デフォルト*: `true`

`escaping_strategy` 設定は出力エスケーパサブフレームワークを有効にするかどうかを決めます。この設定はブール値をとります。この設定が有効な場合、`escaping_method` 設定で定義されているヘルパー関数が呼び出され、テンプレートのなかで利用可能なすべての変数は自動的にエスケープされます (下記の説明をご参照ください)。

`escaping_method` 設定がデフォルトのヘルパーであることにご注意ください。ケースバイケースでこの設定をオーバーライドすれば、たとえば JavaScript スクリプトタグのなかで変数を出力するなどの状況に対処できます。

出力エスケーパサブフレームワークはエスケープの際に `charset` 設定を使います。

設定の値はデフォルトの `true` のままにしておくことをぜひおすすめします。
    
>**TIP**
>アプリケーションを `generate:app` タスクで作る際に `--escaping-strategy` オプションを指定すれば、エスケープは自動的に有効になります。


### ~`escaping_method`~

*デフォルト*: `ESC_SPECIALCHARS`

`escaping_method` 設定はテンプレートのなかでエスケープに使われるデフォルトの関数を定義します (上記の `escaping_strategy` 設定をご参照ください)。

組み込み関数の1つを選ぶ、もしくは自前の関数を作ることができます (~`ESC_RAW`~、~`ESC_ENTITIES`~、~`ESC_JS`~、
~`ESC_JS_NO_ENTITIES`~ と ~`ESC_SPECIALCHARS`~)。

ほとんどの場合、デフォルトで事足ります。英語もしくはヨーロッパの言語だけを扱う場合には `ESC_ENTITIES` ヘルパーを選ぶこともできます。

### ~`csrf_secret`~

*デフォルト*: ランダムに生成される秘密の文字列

`csrf_secret` 設定はアプリケーションにおいて一意性のある秘密の文字列です。この設定に `false` がセットされていないかぎり、フォームフレームワークで定義されているすべてのフォームで CSRF 対策機能が有効になります。この設定は `link_to()` ヘルパーにも使われ、リンクをフォームに変換することが必要な場合に役立ちます  (たとえば HTTP `DELETE` メソッドをシミュレートしたい場合)。

デフォルトをあなたが選んだ一意性のある秘密の文字列に変更しておくことをぜひおすすめします。

>**TIP**
>`generate:app` タスクでアプリケーションを作る際に `--csrf-secret` オプションを指定すれば、CSRF 対策機能は自動的に有効になります。

### ~`charset`~

*デフォルト*: `utf-8`

`charset` 設定はフレームワークのあらゆる場所に使われる文字集合を指定します。この設定の利用範囲はレスポンスの `Content-Type` ヘッダーから出力エスケーピングまでおよびます。

ほとんどの場合、デフォルトで事足ります。

>**WARNING**
>この設定の値はフレームワークのさまざまな場所で使われるので、複数の場所に保存されます。この設定を変更した後では、開発環境であっても、コンフィギュレーションのキャッシュをクリアしなければなりません。

### ~`enabled_modules`~

*デフォルト*: `[default]`

`enabled_modules` 設定はこのアプリケーションで有効なモジュール名の配列です。デフォルトでは、プラグインもしくは symfony コアで定義されているモジュールは有効ではなく、これらにアクセスできるようにするには、この配列につけ加えなければなりません。

モジュールの追加方法はシンプルで、リストに名前を加えるだけです (モジュールの順序は問いません)。

    [yml]
    enabled_modules: [default, sfGuardAuth]

`settings.yml` ファイルの `.actions` サブセクションであらかじめ定義されているすべてのデフォルトアクションは symfony フレームワークの `default` モジュールに収められています。これらすべてをカスタマイズし、この設定から `default` モジュールを除外しておくことをおすすめします。

### ~`default_timezone`~

*デフォルト*: なし

`default_timezone` 設定は PHP が使うデフォルトのタイムゾーンを定義します。この設定は PHP が認識する任意の[タイムゾーン](http://www.php.net/manual/class.datetimezone.php)をとります。


>**NOTE**
>タイムゾーンは `php.ini` ファイルのなかで設定しておくことをおすすめします。そうでなければ、symfony は PHP の [`date_default_timezone_get()`](http://www.php.net/date_default_timezone_get) 関数を呼び出して、最善のタイムゾーンを推測します。

### ~`cache`~

*デフォルト*: `false`

`cache` 設定はテンプレートのキャッシュを有効もしくは無効にします。

>**TIP**
>キャッシュシステム全体のコンフィギュレーションを変更できる場所は `factories.yml` ファイルの [`view_cache_manager`](#chapter_05_view_cache_manager) と [`view_cache`](#chapter_05_view_cache) セクションです。コンフィギュレーションをきめ細かく調整できる場所は [`cache.yml`](#chapter_09) ファイルです。

### ~`etag`~

*デフォルト*: `dev` と `test` 環境を除いて、デフォルトでは `true`

`etag` 設定は HTTP の `ETag` ヘッダーの自動生成を有効もしくは無効にします。レスポンスのコンテンツにおいて、symfony によって生成される ETag ヘッダーは単純な md5 のハッシュです。

### ~`i18n`~

*デフォルト*: `false`

`i18n` 設定は国際対応サブフレームワークを有効もしくは無効にします。この設定はブール値をとります。国際対応したアプリケーションを開発するのであれば、この設定に `true` をセットします。

>**TIP**
>国際対応システム全般において、コンフィギュレーションを変更できる場所は `factories.yml` ファイルの [`i18n`](#chapter_05_i18n) セクションです。

### ~`default_culture`~

*デフォルト*: `en`

`default_culture` 設定は国際対応サブフレームワークで使われるデフォルトのカルチャを定義します。この設定は任意の有効なカルチャの文字列をとります。

### ~`standard_helpers`~

*デフォルト*: `[Partial, Cache]`

`standard_helpers` 設定はすべてのテンプレートのなかでロードされるヘルパーグループの配列です (ヘルパーグループの名前にはサフィックスの `Helper` をつけません)。

### ~`no_script_name`~

*デフォルト*: 新しいアプリケーションの `prod` 環境では `true`、それ以外の環境では `false`

`no_script_name` 設定は生成される URL にフロントコントローラスクリプトの名前をつけ足すかどうかを決めます。`generate:app` タスクによって作られた新しいアプリケーションの `prod` 環境において、この設定には `true` がセットされています。

すべてのフロントコントローラが同じディレクトリ (`web/`) に配置されている場合、この設定に `true` をセットできるのはあきらかに1つのアプリケーションと環境にかぎられます。`no_script_name` 設定に `true` がセットされているアプリケーションが複数必要であれば、該当するフロントコントローラを Web 公開ディレクトリに移動させます。

### ~`lazy_cache_key`~

*デフォルト*: 新しいプロジェクトでは `true`、アップグレードしたプロジェクトでは `false`

`lazy_cache_key` 設定が有効な場合、キャッシュキーの作成はアクションもしくはパーシャルがキャッシュ可能になるまで延期されます。テンプレートパーシャルの使いかたしだいではパフォーマンスを大きく改善できます。

### ~`file_link_format`~

*デフォルト*: なし

デバッグメッセージにおいて、`sf_file_link_format` 設定もしくは PHP の `xdebug.file_link_format` ディレクティブに値がセットされている場合、ファイルパスはクリック可能なリンクに変換されます。たとえば、ファイルが TextMate で開かれるようにしたいのであれば、次の値をセットします。

    [yml]
    txmt://open?url=file://%f&line=%l

`%f` プレースホルダはファイルの絶対パスに置き換わり、`%l` プレースホルダは行番号に置き換わります。

### ~`logging_enabled`~

*デフォルト*: `prod` 以外のすべての環境では `true`

`logging_enabled` 設定はロギングサブフレームワークを有効にします。この設定に `false` がセットされていれば、ロギングメカニズムが回避され、パフォーマンスが少し改善されます。

>**TIP**
>ロギングのコンフィギュレーションをきめ細かく調整できる場所は `factories.yml` ファイルです。

### ~`web_debug`~

*デフォルト*: `dev` 以外のすべての環境では `false`

`web_debug` 設定はデバッグツールバーを有効にします。レスポンスの Content-Type ヘッダーに HTML がセットされている場合、デバッグツールバーがページに投入されます。

### ~`error_reporting`~

*デフォルト*:

  * `prod`:  E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR
  * `dev`:   E_ALL | E_STRICT
  * `test`:  (E_ALL | E_STRICT) ^ E_NOTICE
  * デフォルト: E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR

`error_reporting` 設定は PHP のエラーレポートのレベルをコントロールします (ログに書き込まれ、ブラウザに表示されます)。

>**TIP**
>[ビット演算子](http://www.php.net/language.operators.bitwise)の解説は PHP 公式マニュアルに掲載されています。

デフォルトのコンフィギュレーションがもっとも理にかなったものであり、変更すべきではありません。

>**NOTE**
>`prod` 環境のフロントコントローラでは `debug` 設定が無効になっているので、ブラウザのエラー表示は自動的に無効になります。

### ~`compressed`~

*デフォルト*: `false`

`compressed` 設定は PHP ネイティブなレスポンス圧縮を有効にします。この設定に `true` がセットされている場合、[`ob_gzhandler()`](http://www.php.net/ob_gzhandler) 関数が `ob_start()` 関数のコールバックに使われます。

この設定を `false` のままにしておいて、Web サーバーに備わっている圧縮メカニズムを利用することをおすすめします。

### ~`use_database`~

*デフォルト*: `true`

`use_database` 設定はアプリケーションでデータベースを使うかどうかを決めます。

### ~`check_lock`~

*デフォルト*: `false`

`check_lock` 設定は `cache:clear` や `project:disable` タスクなどによって実行されるアプリケーションのロックシステムを有効もしくは無効にします。

この設定に `true` がセットされている場合、無効なアプリケーションへのリクエストはすべて自動的に symfony コアの `lib/exception/data/` ディレクトリに配置されている `unavailable.php` ページにリダイレクトされます。

>**TIP**
>`config/unavailable.php` ファイルをプロジェクトもしくはアプリケーションに追加すれば、アプリケーションが無効なときに表示されるページのデフォルトテンプレートをオーバーライドできます。

### ~`web_debug_web_dir`~

*デフォルト*: `/sf/sf_web_debug`

`web_debug_web_dir` 設定には Web サイトにおけるデバッグツールバーのアセット (画像、スタイルシートそして JavaScript ファイル) へのパスがセットされます。
