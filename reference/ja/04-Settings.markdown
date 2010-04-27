settings.yml 設定ファイル
=========================

symfony のほとんどのコンフィギュレーションは YAML もしくはプレーンな PHP で書かれている設定ファイルを通して変更できます。このセクションでは `settings.yml` を説明します。

アプリケーションのメイン設定ファイルである `settings.yml` は `apps/APP_NAME/config/` ディレクトリで見つかります。

[設定ファイルの原則の章](#chapter_03)で説明したように、`settings.yml` ファイルでは、**環境が認識され**、**コンフィギュレーションカスケードのメカニズム**がはたらきます。

それぞれの環境には2つのサブセクション (`.actions` と `.settings`) が用意されています。共通ページにおいてレンダリングされるデフォルトのアクション以外、すべてのコンフィギュレーションディレクティブは `.settings` サブセクションの下に格納されています。

>**NOTE**
>`settings.yml` 設定ファイルは PHP ファイルとしてキャッシュされます。処理は ~`sfDefineEnvironmentConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

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

`login` アクションは認証されていないユーザーがセキュアなページにアクセスしようとする際に実行されます。

### ~`secure`~

`secure` アクションはユーザーが必須のクレデンシャルをもたないときに実行されます。

### ~`module_disabled`~

`module_disabled` アクションはユーザーが無効なモジュールをリクエストするときに実行されます。

`.settings` サブセクション
--------------------------

`.settings` サブセクションはフレームワークの設定を調整する場所です。下記のパラグラフでは、すべての利用可能な設定項目を説明し、これらを重要度順におおまかに並べてあります。

`.settings` セクションで定義されている設定項目の名前はすべて設定の名前にプレフィックスの `sf_` をつけたものであり、`sfConfig` オブジェクトを通してコードのなかの任意の場所で利用できます。たとえば `charset` 設定の値を得るには、次のコードを使います:

    [php]
    sfConfig::get('sf_charset');

### ~`escaping_strategy`~

*デフォルト*: `true`

`escaping_strategy` 設定はブール値をとり、出力エスケーパサブフレームワークが有効であるかどうかを決めます。この設定が有効なとき、`escaping_method` 設定で定義されているヘルパー関数を呼び出すことで、テンプレートのなかで利用可能なすべての変数は自動的にエスケープされます (下記を参照)。

`escaping_method` 設定は symfony によって使われるデフォルトのヘルパーであることにご注意ください。しかし、たとえば JavaScript スクリプトのタグで変数を出力するときなど、ケースバイケースでこの設定をオーバーライドできます。

出力エスケーパサブフレームワークはエスケープの際に `charset` 設定を使います。

デフォルトの `true` のままにしておくことをぜひおすすめします。
    
>**TIP**
>アプリケーションを `generate:app` タスクで作る際に `--escaping-strategy` オプションを指定すれば、この設定を自動的にセットできます。


### ~`escaping_method`~

*デフォルト*: `ESC_SPECIALCHARS`

`escaping_method` 設定はテンプレートのなかでエスケープするために使うデフォルト関数を定義します (上記の `escaping_strategy` 設定を参照)。

組み込み関数の1つ: ~`ESC_RAW`~、~`ESC_ENTITIES`~、~`ESC_JS`~、
~`ESC_JS_NO_ENTITIES`~ と~`ESC_SPECIALCHARS`~ を選ぶ、もしくは独自関数を作ることができます。

ほとんどの場合、デフォルトで十分です。英語もしくはヨーロッパの言語だけを扱う場合には `ESC_ENTITIES` ヘルパーを選ぶこともできます。

### ~`csrf_secret`~

*デフォルト*: ランダムに生成される秘密の文字列

`csrf_secret` 設定はアプリケーションにおいて一意性をもつ秘密の文字列です。`false` にセットされていない場合、フォームフレームワークで定義されているすべてのフォームで CSRF 防止機能が有効になります。この設定は `link_to()` ヘルパーにも使われ、リンクをフォームに変換することが必要なとき (たとえば HTTP `DELETE` メソッドをシミュレートしたい場合) に役立ちます。

デフォルトをあなたが選んだ一意性のある秘密の文字列に変更することをぜひおすすめします。

>**TIP**
>アプリケーションを `generate:app` タスクで作る際に `--csrf-secret` オプションを指定すれば、この設定は自動的にセットされます

### ~`charset`~

*デフォルト*: `utf-8`

`charset` 設定はフレームワークのあらゆる場所で使われる文字集合を指定します。適用範囲はレスポンスの `Content-Type` ヘッダーから出力エスケーピングまで及びます。

ほとんどの場合、デフォルトで十分です。

>**WARNING**
>この設定はフレームワークの多くの場所で使われるので、この値は複数の場所で保存されます。この設定を変更した後では、開発環境であっても、コンフィギュレーションキャッシュをクリアしなければなりません。

### ~`enabled_modules`~

*デフォルト*: `[default]`

`enabled_modules` 設定はこのアプリケーションで有効なモジュール名の配列です。デフォルトでは、プラグインもしくは symfony コアで定義されているモジュールは有効ではなく、これらにアクセスできるようにするには、この配列に加えなければなりません。

モジュールの追加方法はシンプルで、リストに名前を加えるだけです (モジュールの順序は問いません):

    [yml]
    enabled_modules: [default, sfGuardAuth]

`settings.yml` に用意されているサブセクションの `.actions` において、セットされているすべてのデフォルトアクションはフレームワークで定義されている `default` モジュールに収められています。これらすべてをカスタマイズし、この設定から `default` モジュールを除外することをおすすめします。

### ~`default_timezone`~

*デフォルト*: なし

`default_timezone` 設定は PHP で使われるデフォルトのタイムゾーンを定義します。この設定は PHP で認識される任意の[タイムゾーン](http://www.php.net/manual/class.datetimezone.php)をとります。


>**NOTE**
>タイムゾーンが定義されていなければ、`php.ini` ファイルで定義することをおすすめします。そうでなければ、symfony は PHP の [`date_default_timezone_get()`](http://www.php.net/date_default_timezone_get) 関数を呼び出すことで最善のタイムゾーンを推測します。

### ~`cache`~

*デフォルト*: `false`

`cache` 設定はテンプレートキャッシュを有効もしくは無効にします。

>**TIP**
>キャッシュシステム全般のコンフィギュレーションの変更は `factories.yml` 設定ファイルの [`view_cache_manager`](#chapter_05_view_cache_manager) と [`view_cache`](#chapter_05_view_cache) セクションで行います。コンフィギュレーションのきめ細かい調整は [`cache.yml`](#chapter_09) 設定ファイルで行います。

### ~`etag`~

*デフォルト*: `dev` と `test` 環境を除いて、デフォルトでは `true`

`etag` 設定は HTTP の `ETag` ヘッダーの自動生成を有効もしくは無効にします。symfony によって生成される ETag はレスポンスのコンテンツの単純な md5 です。

### ~`i18n`~

*デフォルト*: `false`

`i18n` 設定は国際化対応サブフレームワークを有効もしくは無効にします。この設定はブール値をとります。アプリケーションを国際化対応にするのであれば、この設定を `true` にセットします。

>**TIP**
>国際化対応システム全般のコンフィギュレーションの変更は `factories.yml` 設定ファイルの [`i18n`](#chapter_05_i18n) セクションで行います。

### ~`default_culture`~

*デフォルト*: `en`

`default_culture` 設定は国際化サブフレームワークで使われるデフォルトのカルチャを定義します。この設定は任意の有効なカルチャの文字列をとります。

### ~`standard_helpers`~

*デフォルト*: `[Partial, Cache]`

`standard_helpers` 設定はすべてのテンプレートのなかでロードされるヘルパーグループの配列です (ヘルパーグループの名前にはサフィックスの `Helper` をつけません)。

### ~`no_script_name`~

*デフォルト*: 最初に作られるアプリケーションの `prod` 環境では `true`、その他すべてでは `false`

`no_script_name` 設定は生成される URL にフロントコントローラスクリプトの名前をつけ足すかどうかを決めます。最初に作られるアプリケーションの `prod` 環境のデフォルトでは、この設定は `generate:app` タスクによって `true` にセットされます。

すべてのフロントコントローラが同じディレクトリ (`web/`) にある場合、この設定を `true` にセットできるのはあきらかに1つのアプリケーションと環境だけです。`no_script_name` が `true` にセットされているアプリケーションが複数必要であれば、該当するフロントコントローラを Web 公開ディレクトリのなかに移動させます。

### ~`lazy_cache_key`~

*デフォルト*: 新しいプロジェクトでは `true`、アップグレードしたプロジェクトでは `false`

`lazy_cache_key` 設定が有効なとき、キャッシュキーの作成はアクションもしくはパーシャルがキャッシュ可能になるまで延期されます。テンプレートパーシャルの使い方しだいではパフォーマンスが大いに改善されます。

### ~`file_link_format`~

*デフォルト*: なし

デバッグメッセージにおいて、`sf_file_link_format` もしくは PHP の `xdebug.file_link_format` 設定の値がセットされている場合、ファイルパスがクリック可能なリンクに変換されます。たとえば、ファイルを TextMate で開きたい場合、次の値を使います:

    [yml]
    txmt://open?url=file://%f&line=%l

`%f` プレースホルダはファイルの絶対パスに置き換わり、`%l` プレースホルダは行番号に置き換わります。

### ~`logging_enabled`~

*デフォルト*: `prod` 以外のすべての環境では `true`

`logging_enabled` 設定はロギングサブフレームワークを有効にします。この設定を `false` にセットすれば、ロギングメカニズムが回避され、パフォーマンスが少し向上します。

>**TIP**
>ロギングコンフィギュレーションのきめ細かい調整は `factories.yml` 設定ファイルで行います。

### ~`web_debug`~

*デフォルト*: `dev` 以外のすべての環境では `false`

`web_debug` 設定はデバッグツールバーを有効にします。レスポンスの Content-Type が HTML であるときにデバッグツールバーがページに投入されます。

### ~`error_reporting`~

*デフォルト*:

  * `prod`:  E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR
  * `dev`:   E_ALL | E_STRICT
  * `test`:  (E_ALL | E_STRICT) ^ E_NOTICE
  * デフォルト: E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR

`error_reporting` 設定は PHP のエラーレポートのレベルをコントロールします (ログに書き込まれ、ブラウザに表示されます)。

>**TIP**
>[ビット演算子](http://www.php.net/language.operators.bitwise)の使い方に関する情報は PHP 公式サイトにあります。

デフォルトのコンフィギュレーションはもっとも利にかなったものであり、変更すべきではありません。

>**NOTE**
>`prod` 環境のフロントコントローラでは `debug` が無効なので、ブラウザのエラー表示は自動的に無効になります。

### ~`compressed`~

*デフォルト*: `false`

`compressed` 設定は PHP ネイティブなレスポンス圧縮を有効にします。`true` にセットされている場合、symfony は [`ob_gzhandler`](http://www.php.net/ob_gzhandler) を `ob_start()` のコールバック関数に使います。

この設定は `false` のままにしておいて、代わりに Web サーバーに備わっている圧縮メカニズムを利用することをおすすめします。

### ~`use_database`~

*デフォルト*: `true`

`use_database` はアプリケーションがデータベースを使うかどうかを決めます。

### ~`check_lock`~

*デフォルト*: `false`

`check_lock` 設定は `cache:clear` と `project:disable` のようなタスクによって実行されるアプリケーションのロックシステムを有効もしくは無効にします。

`true` にセットされている場合、無効なアプリケーションへのすべてのリクエストは自動的に symfony コアの `lib/exception/data/unavailable.php` ページにリダイレクトされます。

>**TIP**
>`config/unavailable.php` ファイルをプロジェクトもしくはアプリケーションに追加すれば、アプリケーションが無効なときに表示されるページのデフォルトテンプレートをオーバーライドできます。

### ~`web_debug_web_dir`~

*デフォルト*: `/sf/sf_web_debug`

`web_debug_web_dir` はデバッグツールバーのアセット (画像、スタイルシートそして JavaScript ファイル) への Web サイト上のパスをセットします。
