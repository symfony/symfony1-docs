第19章 - symfony の設定ファイルをマスターする
===============================================

現在あなたは symfony をとてもよく理解しています。すでに symfony コアの設計を理解し、新しい隠し機能を見つけるためにコードを徹底的に調べる準備ができています。しかし、独自要件に適用するために symfony のクラスを拡張するまえに、設定ファイルをもっとよく見ることが必要です。すでに多くの機能は symfony に組み込まれ、設定を少し変更すれば有効になります。このことはクラスをオーバーライドしなくても symfony コアのふるまいを調整できることを意味します。この章では設定ファイルとこれらの強力な機能を詳しく説明します。

symfony の設定
-------------

`frontend/config/settings.yml` ファイルはおもに `frontend` アプリケーションの設定を格納します。すでに以前の章でこれらのファイルから多くの設定の機能を見てきましたが、見直すことにします。

5章で説明したように、このファイルは環境に依存します。すなわちそれぞれの設定が環境ごとに異なる値をとります。このファイルで定義されるそれぞれのパラメーターは `sfConfig` クラスを通してPHPクラスの内部からアクセスできることを覚えておいてください。パラメーターの名前は設定の名前にプレフィックスの `sf_` をつけたものです。たとえば、`cache`パラメーターの値を得たいのであれば、必要なのは `sfConfig::get('sf_cache')` を呼び出すことだけです。

### デフォルトのモジュールとアクション

symfony は特殊な状況のためのデフォルトページを用意します。ルーティングエラーの場合、symfony は `default`モジュールのアクションを実行します。このモジュールは `$sf_symfony_lib_dir/controller/default/` ディレクトリに保存されています。`settings.yml` ファイルはそれぞれのエラーに対処するアクションを定義します:

  * `error_404_module` と `error_404_action`: ユーザーによって入力されたURLがどのルートにもマッチしないもしくは `sfError404Exception` が起動するときに呼び出されるアクションです。デフォルト値は `default/error404` です。
  * `login_module` と `login_action`: `security.yml` ファイルのなかで `secure` と定義されるページに認証されていないユーザーがアクセスしようとするときに呼び出されるアクション(詳細は6章を参照)。デフォルト値は `default/login` です。
  * `secure_module` と `secure_action`: ユーザーがアクションから要求されるクレデンシャルを持たないときに呼び出されるアクションです。デフォルト値は `default/secure` です。
  * `module_disabled_module` と `module_disabled_action`: ユーザーが `module.yml` ファイルのなかで無効と宣言されたモジュールをリクエストすると呼び出されるアクション。デフォルト値は `default/disabled` です。

運用サーバーにアプリケーションをデプロイするまえにこれらのアクションをカスタマイズすべきです。symfony公式サイトのロゴが `default` モジュールのテンプレートのページに入っているからです。図19-1はこれらのページの1つの404エラーページのスクリーンショットです。

図19-1 - デフォルトの404エラーページ

![デフォルトの404エラーページ](http://www.symfony-project.org/images/book/1_4/F1901.jpg "デフォルトの404エラーページ")

つぎの2つの方法でデフォルトのページをオーバーライドできます:

  * アプリケーションの `modules/` ディレクトリのなかで独自のデフォルトモジュールを作り、`settings.yml` ファイルのなかで定義されるすべてのアクション (`index`、`error404`、`login`、`secure`、`disabled`)と関連テンプレート (`indexSuccess.php`、`error404Success.php`、`loginSuccess.php`、`secureSuccess.php`、`disabledSuccess.php`) をオーバーライドします。
  * アプリケーションのページを使うにはデフォルトモジュールとアクションの設定を `settings.yml` ファイルで変更します。

ほかの2つのページの見た目は symfony 公式サイトと同じなので、運用サーバーにデプロイするまえにこれらのページをカスタマイズすることも必要です。これらのページは `default` モジュールには存在しません。これは symfony が適切に動作しないときに呼び出されるからです。代わりに、これらのデフォルトページは `$sf_symfony_lib_dir/exception/data/` ディレクトリで見つかります:

  * `error.html.php`: 運用環境でサーバーの内部エラーが起きるときに呼び出されるページ (`debug` が `true` にセットされている) ほかの環境においてエラーが起きるとき、symfony はすべての実行スタックと明快なエラーメッセージを表示します(詳細は16章を参照)。
  * `unavailable.php`: アプリケーションが (`project:disable` タスクで)無効にされているあいだにユーザーがページをリクエストしたときに呼び出されるページ。このページはキャッシュがクリアされているあいだも呼び出されます (すなわち、`php symfony cache:clear` タスクの呼び出し時と終了時のあいだ)。とても大きなキャッシュをかかえるシステムでは、キャッシュのクリア処理に数秒かかる可能性があります。symfony は一部がクリアされたキャッシュでリクエストを実行できないので、処理が終わるまえに受理されたリクエストはこのページにリダイレクトされます。	

これらのページをカスタマイズするには、プロジェクトもしくはアプリケーションの `config/` ディレクトリのなかで `error/error.html.php` ページと `unavailable.php` ページを作ります。symfony は固有のページの代わりにこれらのテンプレートを使うようになります。

>**NOTE**
>必要なときにリクエストを `unavailable.php` ページにリダイレクトするには、アプリケーションの `settings.yml` にある `check_lock` 設定を `true` にセットする必要があります。デフォルトではこの設定は無効です。この設定によってすべてのリクエストに対してわずかですがオーバーヘッドが追加されるからです。

### オプション機能の有効

`settings.yml` ファイルのパラメーターには symfony のオプション機能の有効化もしくは無効化をコントロールするものがあります。使わない機能を無効にすることでパフォーマンスを少し改善できるので、アプリケーションをデプロイするまえにテーブル19-1で示されている設定の一覧を見直してください。

テーブル 19-1 - `settings.yml` ファイルを通して設定されるオプション機能

パラメーター              | 説明        | デフォルト値
----------------------- | ----------- | -------------
`use_database`          | データベースマネージャーを有効にする。データベースを使わない場合は `false` に切り替える。 | `treu`
`i18n`                  | インターフェイスの翻訳機能を有効にする(13章を参照)。多言語アプリケーションで `true` にセットする。 | `false`
`logging_enabled`       | symfony のイベントのロギング機能を有効にする。ロギング機能を完全にオフにしたい場合は `false` にセットする。 | `true`
`escaping_strategy`     | 出力エスケーピング機能を有効にする (7章を参照)。テンプレートに渡すデータをエスケープしたい場合は `true` にセットする。| `false`
`cache`                 | テンプレートキャッシュを有効にする (12章を参照)。モジュールの1つが `cache.yml` ファイルを収納する場合に `true` にセットする。キャッシュフィルター (`sfCacheFilter`) が `true` にセットされている場合にのみ有効 | 開発環境では `false`、運用環境では `true`
`web_debug`             | 簡単なデバッグ作業のためにWebデバッグツールバーを有効にする(16章を参照)。ツールバーをすべてのページに表示するには `true` にセットする。| 開発環境では `true`、運用環境では `false`
`check_symfony_version` | すべてのリクエストで symfony のバージョンチェックを有効にする。symfony をアップグレードした後にキャッシュを自動的にクリアするには `true`にセットする。アップグレードした後につねにキャッシュをクリアする場合は `false` のままにしておく。|`false`
`check_lock`            | アプリケーションのロックシステムを有効にする。`cache:clear` と `project:disable` タスクによって起動する(以前のセクションを参照)。`$sf_symfony_lib_dir/exception/data/unavailable.php` のページにリダイレクトするように無効なアプリケーションにリクエストするには `true` にセットする。| `false`
`compressed`            | PHP のレスポンス圧縮機能を有効にする。PHP 圧縮ハンドラー経由で出力する HTML を圧縮するには `true` にセットする。 | `false`

### 機能のコンフィギュレーション

バリデーション、キャッシュ、サードパーティのモジュールなどの組み込み機能のふるまいを変更するには、`settings.yml` ファイルのパラメーターをいくつか使います。

#### 出力エスケーピングの設定

出力エスケーピングの設定は変数がテンプレートにアクセスする方法をコントロールします (7章を参照)。`settings.yml` ファイルはこの機能のために2つの設定を格納します:

  * `escaping_strategy` 設定は値として `true` もしくは `false` をとります。
  * `escaping_method` 設定の値は `ESC_RAW`、`ESC_SPECIALCHARS`、`ESC_ENTITIES`、`ESC_JS` もしくは `ESC_JS_NO_ENTITIES` にセットできます。

#### ルーティングの設定

ルーティングの設定 (9章を参照) は `factories.yml` ファイルの `routing` キーの下で定義されます。リスト19-1はデフォルトのルーティングコンフィギュレーションを示しています。

リスト19-1 - ルーティングコンフィギュレーション (`frontend/config/factories.yml`)

    routing:
      class: sfPatternRouting
      param:
        load_configuration: true
        suffix:             .
        default_module:     default
        default_action:     index
        variable_prefixes:  [':']
        segment_separators: ['/', '.']
        variable_regex:     '[\w\d_]+'
        debug:              %SF_DEBUG%
        logging:            %SF_LOGGING_ENABLED%
        cache:
          class: sfFileCache
          param:
            automatic_cleaning_factor: 0
            cache_dir:                 %SF_CONFIG_CACHE_DIR%/routing
            lifetime:                  31556926
            prefix:                    %SF_APP_DIR%

  * `suffix` パラメーターは生成 URL のデフォルトのサフィックスを設定します。デフォルト値はピリオド (`.`) で、どの接尾辞にも一致しません。たとえば、すべての生成URLを静的なページとして見せるには `.html` に設定します。
  * `module` パラメーターもしくは `action` パラメーターがルーティングルールのなかで定義されていないとき、代わりに `factories.yml` ファイルからの値が使われます:
    * `default_module`: デフォルトの `module` リクエストパラメーター。デフォルトは `default` モジュール。
    * `default_action`: デフォルトの `action` リクエストパラメーター。デフォルトは `index` アクション。
  * デフォルトでは、ルートのパターンでは名前つきのワイルドカードはプレフィックスのコロン (`:`) によって見わけられます。ルールを PHP によりフレンドリな構文で書きたければ、配列 `variable_prefixes` にドル記号 (`$`) を追加します。この方法では、`/article/:year/:month/:day/:title` の代わりに `/article/$year/$month/$day/$title` のようなパターンを書けます。
  * ルーティングのパターンでは区切り文字のあいだの名前つきのワイルドカードが認識されます。デフォルトの区切り文字はスラッシュとドットですが、望むのであればさらに `segment_separators` パラメーターに区切り文字を追加できます。たとえば、ダッシュ (`-`) を追加したければ、 `/article/:year-:month-:day/:title` のようにパターンを書きます。
  * 運用モードにおいて、外部URLと内部URIのあいだの変換を加速するために、独自のキャッシュがルーティングのパターンに使われます。デフォルトでは、このキャッシュはファイルシステムを利用しますが、クラスと設定を `cache` パラメーターで宣言すれば任意のキャッシュクラスが利用できます。利用可能なキャッシュストレージクラスのリストは15章を参照してください。運用環境でルーティングキャッシュを無効にするには、`debug` パラメーターを `true` にセットします。

`sfPatternRouting` クラス専用の設定があります。アプリケーションのルーティング、独自もしくは symfony のルーティングファクトリ (`sfNoRouting` と `sfPathInfoRouting`) のどちらかに対して別のクラスを使えます。これら2つのうちどちらかを使うことで、すべての外部 URL は `module/action?key1=param1`のようになります。カスタマイズできませんが、処理は速いです。違いは最初のものは PHP の `GET` リクエストを使い、2番目は `PATH_INFO` を使います。おもにこれらはバックエンドのインターフェイスで使われます。

ルーティングに関連する追加パラメーターが1つありますが、これは `settings.yml` ファイルに保存されます:

  * `no_script_name` 設定は生成URLのなかでフロントコントローラーを有効にします。フロントコントローラーをさまざまなディレクトリに保存してデフォルトのURL書き換えルールを変更しないかぎり、`no_script_name` 設定はプロジェクトの単独のアプリケーションでのみ `on` です。通常この設定の値は運用環境のメインアプリケーションで `on` でそのほかでは `off` です。

#### フォームバリデーションの設定

>**NOTE**
>このセクションで説明されている機能はsymfony 1.1では廃止され `sfCompat10`プラグインを有効にしている場合のみに動作します。

フォームバリデーションの設定は `Validation`ヘルパーによるエラーメッセージの表示方法をコントロールします (10章を参照)。これらのエラーは `<div>` 要素に含まれ、`id`属性を作り上げるためにこれらのエラーは `class`  属性として `validation_error_class` 設定と `validation_error_id_prefix` 設定を使います。デフォルト値は `form_error` と `error_for_` なので、`foobar` という名前の入力に対して `form_error()` ヘルパー呼び出しによる属性出力は `class="form_error" id="error_for_foobar"` となります。

2つの設定はそれぞれのエラーメッセージの前後に追加される文字 (`validation_error_prefix` と `validation_error_suffix`) を決定します。すべてのエラーメッセージをまとめてカスタマイズするにはこれらの設定を変更します。

#### キャッシュの設定

キャッシュ設定の大半は `cache.yml` ファイルで定義されますが、`settings.yml` ファイルのなかの2つは異なります: `cache` はテンプレートキャッシュのメカニズムを有効にし、`etag`はサーバーサイドの ETag ハンドリングを有効にします (15章を参照)。2つのすべてのキャッシュシステム (ビューキャッシュ、ルーティングキャッシュと、国際化キャッシュ) に対してどのストレージを使うのかを `factories.yml` ファイルのなかで指定することもできます。リスト19-2はビューのキャッシュファクトリのデフォルトコンフィギュレーションを示しています。

リスト19-2 - ビューのキャッシュコンフィギュレーション (`frontend/config/factories.yml`)

    view_cache:
      class: sfFileCache
      param:
        automatic_cleaning_factor: 0
        cache_dir:                 %SF_TEMPLATE_CACHE_DIR%
        lifetime:                  86400
        prefix:                    %SF_APP_DIR%/template

`class` の値は `sfFileCache`、`sfAPCCache`、`sfEAcceleratorCache`、`sfXCacheCache`、`sfMemcacheCache` と `sfSQLiteCache` のどれかになります。独自クラスも利用可能で、条件は、`sfCache` を継承し、設定、キャッシュのキーの検索と削除用に同じ一般メソッドを提供することです。ファクトリのパラメーターは選ぶクラスに依存しますが、定数が存在します:

  * `lifetime` はキャッシュが削除されるまでの秒数を定義します
  * `prefix` はすべてのキャッシュキーにつけられるプレフィックスです (環境によって異なるキャッシュを利用するためにプレフィックスのなかの環境を使う)。2つのアプリケーションのあいだでキャッシュを共有したい場合は同じプレフィックスを使います。

ファクトリごとにキャッシュストレージの位置を定義しなければなりません。

 * `sfFileCache` に関して、`cache_dir` パラメーターはキャッシュディレクトリへの絶対パスを探します
 * `sfAPCCache`、`sfEAcceleratorCache` と `sfXCacheCache` は位置パラメーターをとりません。これらが APC、EAccelerator もしくは XCache キャッシュシステムとコミュニケーションするために PHP のネイティブ関数を使うからです
 * `sfMemcacheCache` に関して、`host` パラメーターに Memcached サーバーのホストの名前を、もしくは `servers` パラメーターにホストの配列を入力します
 * `sfSQLiteCache` に関して、`database` パラメーターに SQLite データベースファイルへの絶対パスを入力します。

追加パラメーターに関して、それぞれのキャッシュクラスのAPIドキュメントを確認してください。

ビューはキャッシュを利用できる唯一のコンポーネントではありません。ビューキャッシュと同じように、`routing` ファクトリと `I18N` ファクトリは両方ともキャッシュファクトリを設定できる `cache` パラメーターを提供します。たとえば、リスト19-1は加速戦術にファイルキャッシュを使うデフォルトのルーティングを示していますが、好きなものに変更できます。

#### ロギングの設定

2つのロギング設定 (16章を参照) は `settings.yml` ファイルに保存されます:

  * `error_reporting` 設定は PHP ログに記録されるイベントを指定します。デフォルトでは、運用環境では`E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR`に(ロギングされるイベントは`E_PARSE`、`E_COMPILE_ERROR`、`E_ERROR`、`E_CORE_ERROR`と`E_USER_ERROR`)、開発環境では `E_ALL | E_STRICT` にセットされます。
  * `web_debug` 設定はWebデバッグツールバーを有効にします。開発とテスト環境でのみ `true` にセットします。

#### アセットへのパス

`settings.yml` ファイルはアセットへのパスも保存します。symfony に搭載されるアセット以外の別のバージョンのアセットを使いたければ、これらのパスの設定を変更できます:

  * `admin_web_dir` に保存され administration ジェネレーターが必要なファイル
  * `web_debug_web_dir` に保存され Web デバッグツールバーが必要なファイル

#### デフォルトのヘルパー

デフォルトのヘルパーは、すべてのテンプレートでロードされ、`standard_helpers` 設定で宣言されます (7章を参照)。デフォルトは `Partial`、`Cache` ヘルパーグループです。アプリケーションのすべてのテンプレートのなかでヘルパーグループを利用する場合、`standard_helpers` 設定にヘルパーグループの名前を追加すれば、`use_helper()` ヘルパーを使ってそれぞれのテンプレートのなかでヘルパーグループを宣言するわずらわしい作業を行わずにすみます。

#### 有効なモジュール

プラグインもしくは symfony コアから有効にされるモジュールは `enabled_modules` パラメーターで宣言されます。プラグインがモジュールを搭載する場合、`enabled_modules` パラメーターで宣言されないかぎりユーザーはこのモジュールをリクエストできません。`default` モジュールは symfony のデフォルトページ (初期ページ、ページが見つからないさいに表示されるページなど) を提供し、デフォルトで唯一有効なモジュールです。

#### 文字集合

レスポンスの文字集合は多くのコンポーネントで使われるので、アプリケーション全体の設定です (テンプレート、出力エスケーパー、ヘルパーなど)。デフォルトで定義される `charset` 設定の値は `utf-8` (推奨) です。

>**SIDEBAR**
>アプリケーションの設定を追加する
>
>`settings.yml` ファイルはアプリケーションの設定を定義します。新しいパラメーターを追加したければ、5章で説明したように最適の場所は `frontend/config/app.yml` ファイルです。このファイルは環境にも依存しており、このファイルが定義する設定の値は `sfConfig` クラスとプレフィックスの `app_` を通して使えます。
>
>
>     all:
>       creditcards:
>         fake:             false    # app_creditcards_fake
>         visa:             true     # app_creditcards_visa
>         americanexpress:  true     # app_creditcards_americanexpress
>
>
>プロジェクトの設定ディレクトリのなかで `app.yml` ファイルを書くことも可能で、カスタムプロジェクト設定を定義できます。設定カスケードはこのファイルにも適用されるので、アプリケーションの `app.yml` ファイルで定義される設定はプロジェクトレベルで定義される設定をオーバーライドします。

オートロード機能を拡張する
-------------------------

オートロード機能は2章で手短に説明しましたが、これによってコードを特定のディレクトリに設置していればクラスの読み込みを指示するコードを書かずにすみます。このことは、適切なときに必要なクラスだけをロードする作業を symfony に任せられることを意味します。

`autoload.yml` ファイルはオートロードされるクラスが保存されるパスの一覧を示します。この設定ファイルが最初に処理されるとき、symfony はこのファイルに参照されるすべてのディレクトリを解析します。これらのディレクトリの1つのなかで `.php` 拡張子を持つファイルが見つかるたびに、このファイルのなかで見つかるファイルパスとクラス名がオートロードクラスの内部リストに追加されます。このリストはキャッシュ、`config/config_autoload.yml` ファイルに保存されます。それから、実行時にクラスが使われるとき、このリストのなかで symfony はクラスのパスを探し `.php` ファイルを自動的にインクルードします。

オートロード機能はクラスかつ/またはインターフェイスを含むすべての `.php` ファイルに対して動作します。

デフォルトでは、つぎのプロジェクトディレクトリに保存されるクラスはオートロード機能からの恩恵を受けます:

  * `myproject/lib/`
  * `myproject/lib/model`
  * `myproject/apps/frontend/lib/`
  * `myproject/apps/frontend/modules/mymodule/lib`

`autolaod.yml` ファイルはアプリケーションのデフォルトの設定ディレクトリには存在しません。symfony の設定を修正したい場合、たとえばファイル構造のどこかに保存されたクラスをオートロードするには、空の `autoload.yml` ファイルを作り、`$sf_symfony_lib_dir/config/autoload.yml` ファイルもしくは独自ファイルの設定をオーバーライドします。

`autoload.yml` ファイルは `autoload:` キーで始まり、symfony がクラスを探す場所のリストを記載します。それぞれの場所はラベルを必要とします: これによって symfony のエントリーをオーバーライドできます。それぞれの場所に対して、`name` (`config_autload.yml.php` でコメントとして表示される) と絶対パス (`path`) を記入します。それから、検索を再帰的 (`recursive`) に定義すると、symfony はすべてのサブディレクトリで`.php`ファイルを探します。また望むサブディレクトリを除外 (`exclude`) します。リスト19-3はデフォルトで使われる場所とファイルの構文を示しています。

リスト19-3 - オートロードのデフォルトコンフィギュレーション (`$sf_symfony_lib_dir/config/autoload.yml`)

    autoload:
      # プラグイン
      plugins_lib:
        name:           plugins lib
        path:           %SF_PLUGINS_DIR%/*/lib
        recursive:      true

      plugins_module_lib:
        name:           plugins module lib
        path:           %SF_PLUGINS_DIR%/*/modules/*/lib
        prefix:         2
        recursive:      true

      # プロジェクト
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony]

      project_model:
        name:           project model
        path:           %SF_LIB_DIR%/model
        recursive:      true

      # アプリケーション
      application:
        name:           application
        path:           %SF_APP_LIB_DIR%
        recursive:      true

      modules:
        name:           module
        path:           %SF_APP_DIR%/modules/*/lib
        prefix:         1
        recursive:      true

ルールのパスにワイルドカードを含めることは可能で設定クラスのなかで定義されるファイルパスのパラメーターが使えます (つぎのセクションを参照)。これらのパラメーターを設定ファイルのなかで使う場合、大文字で記述し始めと終わりを`%`で挟まなければなりません。

独自の`autoload.yml` ファイルを編集すれば symfony のオートロードの対象に新しい位置が追加されますが、このメカニズムを拡張して symfony のハンドラーに独自のオートロードハンドラーを追加したいことがあります。symfony はクラスのオートロードを管理するために標準の `spl_autoload_register()` 関数を使うので、アプリケーションの設定クラスに複数のコールバックを登録できます:

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function initialize()
      {
        parent::initialize(); // 最初に symfony のオートロード機能をロードする

        // ここに独自のオートロードコールバックを挿入する
        spl_autoload_register(array('myToolkit', 'autoload'));
      }
    }

PHP のオートロードシステムが新しいクラスに遭遇するとき、最初に symfony のオートロードメソッドが試されます (そして `autoload.yml` ファイルのなかで定義された位置が使われます)。クラスの定義が見つからない場合、クラスが見つかるまで `spl_autoload_register()` で登録されたすべての callable が呼び出されます。たとえば、ほかのフレームワークコンポーネントへのブリッジを提供するために(17章を参照)、オートロードメカニズムを望む数だけ追加できます。

カスタムファイル構造
----------------------

symfony　フレームワークは何か　(コアクラスからテンプレート、プラグイン、設定など)　を探すさいには、実際のパスの代わりにパス変数を使います。これらの変数を変更することで、symfony　プロジェクトのディレクトリ構造を完全に変更して顧客のファイル構造の要件に合わせることができます。

>**CAUTION**
>symfony　プロジェクトのディレクトリ構造をカスタマイズするのは可能ですが、かならずしもよいアイディアではありません。symfony　のようなフレームワークの強みの1つは慣習を尊重して開発されたプロジェクトを　Web　開発者が安心して見ることができることです。独自のディレクトリ構造を利用することを決定するまえにかならずこの問題を考えてください。

### 基本的なファイル構造

パス変数は `sfProjectConfiguration` と `sfApplicationConfiguration` クラスのなかで定義され `sfConfig` オブジェクトに保存されます。リスト19-4はパス変数とこれらが参照するディレクトリの一覧を示しています。

リスト19-4 - デフォルトのファイル構造のパス変数 (`sfProjectConfiguration` と `sfApplicationConfiguration`)

    sf_root_dir           # myproject/
    sf_apps_dir           #   apps/
    sf_app_dir            #     frontend/
    sf_app_config_dir     #       config/
    sf_app_i18n_dir       #       i18n/
    sf_app_lib_dir        #       lib/
    sf_app_module_dir     #       modules/
    sf_app_template_dir   #       templates/
    sf_cache_dir          #   cache/
    sf_app_base_cache_dir #     frontend/
    sf_app_cache_dir      #       prod/
    sf_template_cache_dir #         templates/
    sf_i18n_cache_dir     #         i18n/
    sf_config_cache_dir   #         config/
    sf_test_cache_dir     #         test/
    sf_module_cache_dir   #         modules/
    sf_config_dir         #   config/
    sf_data_dir           #   data/
    sf_lib_dir            #   lib/
    sf_log_dir            #   log/
    sf_test_dir           #   test/
    sf_plugins_dir        #   plugins/
    sf_web_dir            #   web/
    sf_upload_dir         #     uploads/

重要なすべてのディレクトリへのパスは`_dir`で終わるパラメーターによって決定されます。あとで必要なときにパスを変更できるように、つねに本当の (相対もしくは絶対) ファイルパスの代わりにパス変数を使ってください。たとえば、ファイルをアプリケーションの `uploads/` ディレクトリに移動させたいとき、パスの記述には `sfConfig::get('sf_root_dir').'/web/uploads/'` の代わりに `sfConfig::get('sf_upload_dir')` を使います。

### ファイル構造をカスタマイズする

アプリケーションを開発するさいに、すでに顧客がディレクトリ構造を定義しており、symfony のロジックに合わせて構造を変更する意志のない場合、プロジェクトのデフォルトのファイル構造を修正する必要があるでしょう。`sf_XXX_dir` 変数を `sfConfig` でオーバーライドすることで、デフォルトとはまったく異なるディレクトリ構造で symfony を動かすことができます。これを行う最良の場所はプロジェクトのディレクトリに対してはアプリケーションの `ProjectConfiguration` クラス、もしくはアプリケーションのディレクトリに対しては `XXXConfiguration` クラスです。

たとえば、すべてのアプリケーションでテンプレートのレイアウト用の共通ディレクトリを共有したい場合、`sf_app_template_dir` 設定をオーバーライドするために `ProjectConfiguration` クラスの `setup()` メソッドにつぎの行を追加します:

    [php]
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'templates');

>**NOTE**
>`sfConfig::set()` を呼び出してプロジェクトのディレクトリ構造を変更できる場合でも、プロジェクトとアプリケーションの設定クラスによって定義された専用メソッドを使うほうがすべての関連パスの変更が考慮されるので優れています。たとえば、`setCacheDir()` メソッドはつぎの定数: `sf_cache_dir`、`sf_app_base_cache_dir`、`sf_app_cache_dir`、`sf_template_cache_dir`、`sf_i18n_cache_dir`、`sf_config_cache_dir`、`sf_test_cache_dir` と`sf_module_cache_dir` を変更します。

### Web 公開ディレクトリのルートの修正

設定クラスに組み込まれるすべてのパスはプロジェクトのルートディレクトリに依存します。このディレクトリパスはプロジェクトの `ProjectConfiguration` クラスによって決定されます。通常のルートディレクトリは `web/` ディレクトリの上位にありますが、異なる構造を利用できます。メインのディレクトリ構造が2つのディレクトリから構成される場合を考えてみます。リスト19-5で示されるように、一方は公開領域で、他方は非公開領域に存在します。共用ホスティングサービスでプロジェクトをホストするときにこのコンフィギュレーションを選ぶことはよくあります。

リスト19-5 - 共用サーバー用のカスタムディレクトリ構造の例

    symfony/    # 非公開領域
      apps/
      config/
      ...
    www/        # 公開領域
      images/
      css/
      js/
      index.php

この場合、ルートディレクトリは `symfony/` ディレクトリです。ですのでアプリケーションを動かすために必要なことはフロントコントローラーの `index.php` が `config/ProjectConfiguration.class.php` ファイルをインクルードすることだけです:

    [php]
    require_once(dirname(__FILE__).'/../symfony/config/ProjectConfiguration.class.php');

加えて、公開領域を通常の `web/` から `www/` に変更するには、つぎのように `setWebDir()` メソッドを使います:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->setWebDir($this->getRootDir().'/../www');
      }
    }

コンフィギュレーションハンドラを理解する
------------------------------------------

設定ファイルはそれぞれのハンドラーを持ちます。コンフィギュレーションハンドラー (configuration handler) の仕事は設定カスケードを管理することと、実行時に設定ファイルを最適化して実行可能な PHP コードに変換することです。

### デフォルトのコンフィギュレーションハンドラー

ハンドラーのデフォルト設定は `$sf_symfony_lib_dir/config/config/config_handlers.yml` ファイルに保存されます。このファイルはファイルパスにしたがってハンドラーを設定ファイルにリンクします。リスト19-6はこのファイルの内容を抜粋したものです。

リスト19-6 - `$sf_symfony_lib_dir/config/config/config_handlers.yml` ファイルの内容の抜粋

    config/settings.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: sf_

    config/app.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: app_

    config/filters.yml:
      class:    sfFilterConfigHandler

    modules/*/config/module.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: mod_
        module: yes

それぞれの設定ファイルごとに `class` キーの下でハンドラーを指定します。`config_handlers.yml` ファイルはワイルドカードを含むファイルパスでそれぞれのファイルを特定します。

`sfDefineEnvironmentConfigHandler` クラスによって処理される設定ファイルの設定は `sfConfig` クラスを通してコードのなかで直接利用できるようになり、`param` キーはプレフィックスの値を格納します。

設定ファイルの処理に使われるハンドラーを追加もしくは修正することができます。たとえば、YAML ファイルの代わりに `INI` ファイルもしくは `XML` ファイルを使うためなどです。

>**NOTE**
>`config_handlers.yml` ファイル用のコンフィギュレーションハンドラーは `sfRootConfigHandler` クラスで、あきらかに変更できません。

設定の解析方法を修正する必要がある場合、アプリケーションの `config/` フォルダーのなかで空の `config_handlers.yml` ファイルを作り、`class` キーの値をオーバーライドします。

### 独自ハンドラーを追加する

設定ファイルを処理するハンドラーを利用することで大きな利点が2つもたらされます:

  * 設定ファイルは PHP の実行コードに変換され、このコードはキャッシュに保存されます。このことは、運用環境において設定は1回だけ解析されるのでパフォーマンスが最適化されることを意味します。
  * 設定ファイルは異なるレベル (プロジェクトとアプリケーション) で定義することが可能で、最後のパラメーターの値はカスケードから由来します。これらのパラメーターをプロジェクトレベルで定義し、アプリケーション単位でオーバーライドできます。

独自のコンフィギュレーションハンドラーを書きたい場合、`$sf_symfony_lib_dir/config/` ディレクトリのなかで symfony によって使われる構造の例にしたがってください。

アプリケーションが `myMapAPI` クラスを持つ場合を考えてみましょう。`myMapAPI` クラスは地図を配信するサードパーティのサービスのためのインターフェイスを提供します。リスト19-7で示されるように、このクラスは URL とユーザー名で初期化することが必要です。

リスト19-7
 - `myMapAPI` クラスの初期化の例

    [php]
    $mapApi = new myMapAPI();
    $mapApi->setUrl($url);
    $mapApi->setUser($user);

`map.yml` という名前のカスタム設定ファイルをアプリケーションの `config/` ディレクトリに設置しつぎの2つのパラメーターを保存するとよいでしょう。この設定ファイルはつぎのような内容を格納します:

    api:
      url:  map.api.example.com
      user: foobar

これらの設定をリスト19-7と同等なコードに変換するには、コンフィギュレーションハンドラーを作らなければなりません。それぞれのコンフィギュレーションハンドラーは `sfConfigHandler` クラスを継承し `execute()` メソッドを提供しなければなりません。`execute()` メソッドはパラメーターとして設定ファイルへのファイルパスの配列が必要で、キャッシュファイルに書き込まれるデータを返さなければなりません。YAML ファイル用のハンドラーは `sfYamlConfigHandler` クラスを継承します。このクラスは YAML パーサーのために追加のファシリティを提供します。`map.yml` ファイルに対する典型的なコンフィギュレーションハンドラーはリスト19-8のように書けます。

リスト19-8 - カスタムコンフィギュレーションハンドラー (`frontend/lib/myMapConfigHandler.class.php`)

    [php]
    <?php

    class myMapConfigHandler extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        // YAMLを解析する
        $config = $this->parseYamls($configFiles);

        $data  = "<?php\n";
        $data .= "\$mapApi = new myMapAPI();\n";

        if (isset($config['api']['url'])
        {
          $data .= sprintf("\$mapApi->setUrl('%s');\n", $config['api']['url']);
        }

        if (isset($config['api']['user'])
        {
          $data .= sprintf("\$mapApi->setUser('%s');\n", $config['api']['user']);
        }

        return $data;
      }
    }

symfony が `execute()` メソッドに渡す配列 `$configFiles` は `config/` フォルダーのなかで見つかるすべての `map.yml` ファイルへのパスを格納します。`parseYamls()` メソッドはコンフィギュレーションカスケードを処理します。

この新しいハンドラーを `map.yml` ファイルと関連づけるには、つぎのような内容を格納する `config_handlers.yml` 設定ファイルを作らなければなりません:

    config/map.yml:
      class: myMapConfigHandler

>**NOTE**
>`class` はオートロードするか (上記の例) `param` キーの下の `file` パラメーターで指定されるファイルのなかでファイルパスを定義しなければなりません。

ほかの多くの symfony 設定ファイルに関しては、コンフィギュレーションハンドラーを PHP コードに直接登録することもできます:

    sfContext::getInstance()->getConfigCache()->registerConfigHandler('config/map.yml', 'myMapConfigHandler', array());

`map.yml` ファイルをもとに `myMapConfigHandler` ハンドラーによって生成されるコードがアプリケーション内部で必要な場合、つぎの行を呼び出します:

    [php]
    include(sfContext::getInstance()->getConfigCache()->checkConfig('config/map.yml'));

`checkConfig()` メソッドを呼び出すとき、`map.yml.php` ファイルがキャッシュにまだ存在しないもしくは `map.yml` ファイルがキャッシュよりも新しい場合、symfony は設定ディレクトリのなかで既存の `map.yml` ファイルを探し `config_handlers.yml` ファイルで指定されるハンドラーを使ってこれらのファイルを処理します。

>**TIP**
>YAML 設定ファイル内部で環境を扱いたい場合、ハンドラーに `sfYamlConfigHandler` クラスの代わりに `sfDefineEnvironmentConfigHandler` クラスを継承させます。設定を読み込むには、`parseYaml()` メソッドの代わりに`getConfiguration()`メソッド: `$config = $this->getConfiguration($configFiles)`を呼び出します。

-

>**SIDEBAR**
>既存のコンフィギュレーションハンドラーを使う
>
>ユーザーが`sfConfig`クラス経由でコードから値を読み込みすることだけが必要なら、`sfDefineEnvironmentConfigHandler`コンフィギュレーションハンドラークラスを使います。たとえば、`url`と`user`パラメーターをそれぞれ`sfConfig::get('map_url')`と`sfConfig::get('map_user')`として使えるようにするには、つぎのようにハンドラーを定義します:
>
>     config/map.yml:
>       class: sfDefineEnvironmentConfigHandler
>       param:
>         prefix: map_
>
>すでにほかのハンドラーによって使われているプレフィックスを選ばないように気をつけてください。既存のプレフィックスは`sf_`、`app`、と`mod_`です。

まとめ
----

設定ファイル(configuration file)はsymfonyフレームワークの動作方法を大いに変更します。symfonyはコア機能とファイルの読み込みでさえも設定に依存するので、標準の専用ホストよりも多くの環境に適用できます。このすばらしい設定の柔軟性はsymfonyの主要な強みの1つです。設定ファイルのなかで学ぶべきたくさんの規約を見た初心者を怖がらせることがあるにせよ、symfony製のアプリケーションは膨大な数のプラットフォーム、環境に対して、互換性があります。ひとたびsymfonyの設定を習得すれば、あなたのアプリケーションを動かすことを拒むサーバーは存在しないでしょう。
