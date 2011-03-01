factories.yml 設定ファイル
==========================

ファクトリはリクエストが存続しているあいだに symfony フレームワークによって必要とされるコアオブジェクトです。これらのコンフィギュレーションは `factories.yml` ファイルのなかで変更することが可能で、`sfContext` オブジェクトを通じてつねにアクセスできます。

    [php]
    // ユーザーファクトリを取得します
    sfContext::getInstance()->getUser();

アプリケーションの `factories.yml` ファイルは `apps/APP_NAME/config/` ディレクトリに配置されています。

[設定ファイルの原則の章](#chapter_03)で述べたように、`factories.yml` ファイルでは、**環境**が認識され、**コンフィギュレーションカスケード**のメカニズムがはたらいており、**定数**を定義することができます。

`factories.yml` ファイルには、名前つきファクトリのリストが用意されています。

    [yml]
    FACTORY_1:
      # ファクトリ1の定義

    FACTORY_2:
      # ファクトリ2の定義

    # ...

サポートされているファクトリは次のとおりです。

`controller`、`logger`、`i18n`、`request`、`response`、`routing`、`storage`、
`user`、`view_cache` と `view_cache_manager`

`sfContext` オブジェクトはファクトリを初期化するとき、`factories.yml` ファイルを読み込み、ファクトリオブジェクトを設定するために使うファクトリのクラス名 (`class`) とパラメータ (`param`) の値を得ます。

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

ファクトリをカスタマイズできることが意味するのは、symfony コアから提供されているデフォルトのクラスの代わりに自前のクラスに切り替えられるということです。これらのクラスに渡すパラメータを調整すれば、これらのクラスのデフォルトのふるまいが変わります。

ファクトリクラスがオートロードされていなければ、`file` パスが定義され、ファクトリが作られる前に自動的にインクルードされます。

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>`factories.yml` ファイルのキャッシュは PHP ファイルとして保存されます。処理は ~`sfFactoryConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)にゆだねられます。

<div class="pagebreak"></div>

ファクトリ
----------

 * [`mailer`](#chapter_05_mailer)

  * [`charset`](#chapter_05_sub_charset)
  * [`delivery_address`](#chapter_05_sub_delivery_address)
  * [`delivery_strategy`](#chapter_05_sub_delivery_strategy)
  * [`spool_arguments`](#chapter_05_sub_spool_arguments)
  * [`spool_class`](#chapter_05_sub_spool_class)
  * [`transport`](#chapter_05_sub_transport)

 * [`request`](#chapter_05_request)

   * [`formats`](#chapter_05_sub_formats)
   * [`path_info_array`](#chapter_05_sub_path_info_array)
   * [`path_info_key`](#chapter_05_sub_path_info_key)
   * [`relative_url_root`](#chapter_05_sub_relative_url_root)

 * [`response`](#chapter_05_response)

   * [`charset`](#chapter_05_sub_charset)
   * [`http_protocol`](#chapter_05_sub_http_protocol)
   * [`send_http_headers`](#chapter_05_sub_send_http_headers)

 * [`user`](#chapter_05_user)

   * [`default_culture`](#chapter_05_sub_default_culture)
   * [`timeout`](#chapter_05_sub_timeout)
   * [`use_flash`](#chapter_05_sub_use_flash)

 * [`storage`](#chapter_05_storage)

   * [`auto_start`](#chapter_05_sub_auto_start)
   * `database`
   * `db_table`
   * `db_id_col`
   * `db_data_col`
   * `db_time_col`
   * [`session_cache_limiter`](#chapter_05_sub_session_cache_limiter)
   * `session_cookie_domain`
   * `session_cookie_httponly`
   * `session_cookie_lifetime`
   * `session_cookie_path`
   * `session_cookie_secure`
   * [`session_name`](#chapter_05_sub_session_name)

 * [`view_cache_manager`](#chapter_05_view_cache_manager)

   * [`cache_key_use_vary_headers`](#chapter_05_sub_cache_key_use_vary_headers)
   * [`cache_key_use_host_name`](#chapter_05_sub_cache_key_use_host_name)

 * [`view_cache`](#chapter_05_view_cache)
 * [`i18n`](#chapter_05_i18n)

   * [`cache`](#chapter_05_sub_cache)
   * [`debug`](#chapter_05_sub_debug)
   * [`source`](#chapter_05_sub_source)
   * [`untranslated_prefix`](#chapter_05_sub_untranslated_prefix)
   * [`untranslated_suffix`](#chapter_05_sub_untranslated_suffix)

 * [`routing`](#chapter_05_routing)

   * [`cache`](#chapter_05_sub_cache)
   * [`extra_parameters_as_query_string`](#chapter_05_sub_extra_parameters_as_query_string)
   * [`generate_shortest_url`](#chapter_05_sub_generate_shortest_url)
   * [`lazy_routes_deserialize`](#chapter_05_sub_lazy_routes_deserialize)
   * [`lookup_cache_dedicated_keys`](#chapter_05_sub_lookup_cache_dedicated_keys)
   * [`load_configuration`](#chapter_05_sub_load_configuration)
   * [`segment_separators`](#chapter_05_sub_segment_separators)
   * [`suffix`](#chapter_05_sub_suffix)
   * [`variable_prefixes`](#chapter_05_sub_variable_prefixes)

 * [`logger`](#chapter_05_logger)

   * [`level`](#chapter_05_sub_level)
   * [`loggers`](#chapter_05_sub_loggers)

 * [`controller`](#chapter_05_controller)

<div class="pagebreak"></div>

`mailer`
--------

*sfContext アクセサ*: `$context->getMailer()`

*デフォルトコンフィギュレーション*:

    [yml]
    mailer:
      class: sfMailer
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        delivery_strategy: realtime
        transport:
          class: Swift_SmtpTransport
          param:
            host:       localhost
            port:       25
            encryption: ~
            username:   ~
            password:   ~

*`test` 環境のデフォルトコンフィギュレーション*:

    [yml]
    mailer:
      param:
        delivery_strategy: none

*`dev` 環境のデフォルトコンフィギュレーション*:

    [yml]
    mailer:
      param:
        delivery_strategy: none

### ~`charset`~

`charset` オプションはメールメッセージに使われる文字集合を定義します。デフォルトでは、`settings.yml` ファイルの `charset` 設定が使われます。

### ~`delivery_strategy`~

`delivery_strategy` オプションはメーラーによるメールメッセージの配信方法を定義します。デフォルトでは、4つのストラテジを選ぶことが可能で、すべての共通ニーズに適しています。

 * `realtime`:       メッセージはリアルタイムで送信されます。

 * `single_address`: メッセージは単独のアドレスに送信されます。

 * `spool`:          メッセージはキューに保存されます。

 * `none`:           メッセージは単に無視されます。

### ~`delivery_address`~

`delivery_address` オプションは `delivery_strategy` オプションに `single_address` がセットされている場合にすべてのメッセージの受信アドレスを定義します。

### ~`spool_class`~

`spool_class` オプションは `delivery_strategy` オプションに `spool` がセットされている場合に使われるスプールクラスを定義します。

  * ~`Swift_FileSpool`~: メッセージはファイルシステムに保存されます。

  * ~`Swift_DoctrineSpool`~: メッセージは Doctrine モデルに保存されます。

  * ~`Swift_PropelSpool`~: メッセージは Propel モデルに保存されます。

>**NOTE**
>スプールのインスタンスが作られるとき、コンストラクタの引数に ~`spool_arguments`~ オプションが渡されます。

### ~`spool_arguments`~

`spool_arguments` オプションはスプールのコンストラクタの引数を定義します。組み込みのキュークラスで利用できるオプションは次のとおりです。

 * `Swift_FileSpool`:

    * キューディレクトリの絶対パス (メッセージはこのディレクトリに保存されます)

 * `Swift_DoctrineSpool`:

    * メッセージの保存に使われる Doctrine モデル (デフォルトは `MailMessage`)

    * メッセージの保存に使われるカラムの名前 (デフォルトは `message`)

    * 送信するメッセージをとり出すために呼び出すメソッド (オプション)。このメソッドはキューオプションを引数にとります。

 * `Swift_PropelSpool`:

    * メッセージの保存に使われる Propel モデル (デフォルトは `MailMessage`)

    * メッセージの保存に使われるカラムの名前 (デフォルトは `message`)

    * 送信するメッセージをとり出すために呼び出すメソッド (オプション)。このメソッドは現在の Criteria を引数にとります。

次のコードは Doctrine スプールにおけるコンフィギュレーションの典型的な例です。

    [yml]
    # factories.yml ファイルのコンフィギュレーション
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

### ~`transport`~

`transport` オプションはメールメッセージを実際に送信するために使われるトランスポートを定義します。

`class` 設定は `Swift_Transport` を実装する任意のクラスになります。デフォルトでは、3つの設定が用意されています。

  * ~`Swift_SmtpTransport`~: メッセージの送信に SMTP サーバーが使われます。

  * ~`Swift_SendmailTransport`~: メッセージの送信に `sendmail` が使われます。

  * ~`Swift_MailTransport`~: メッセージの送信に PHP ネイティブの `mail()` 関数が使われます。

`param` 設定を変更することで、トランスポートを細かく調整できます。組み込みのトランスポートクラスと各種パラメータについては、SwiftMailer の公式ドキュメントの [「Transport Types (トランスポートの種類)」](http://swiftmailer.org/docs/transport-types) の節で網羅されています。

`request`
---------

*sfContext アクセサ*: `$context->getRequest()`

*デフォルトコンフィギュレーション*:

    [yml]
    request:
      class: sfWebRequest
      param:
        logging:           %SF_LOGGING_ENABLED%
        path_info_array:   SERVER
        path_info_key:     PATH_INFO
        relative_url_root: ~
        formats:
          txt:  text/plain
          js:   [application/javascript, application/x-javascript, text/javascript]
          css:  text/css
          json: [application/json, application/x-json]
          xml:  [text/xml, application/xml, application/x-xml]
          rdf:  application/rdf+xml
          atom: application/atom+xml

### ~`path_info_array`~

`path_info_array` オプションは情報検索に使われるグローバルな PHP 配列を定義します。コンフィギュレーションによってはデフォルトの `SERVER` から `ENV` に変更するとよいでしょう。

### ~`path_info_key`~

`path_info_key` オプションは `PATH_INFO` の情報を見つけられるようにするためのキーを定義します。

`IIFR` もしくは `ISAPI` のような rewrite モジュールが付属している IIS を使うのであれば、このオプションの値を `HTTP_X_REWRITE_URL` に変更するとよいでしょう。

### ~`formats`~

`formats` オプションはファイルの拡張子と `Content-Type` の配列です。このオプションはリクエスト URI の拡張子に応じてレスポンスヘッダーの `Content-Type` を調整するのに使われます。

### ~`relative_url_root`~

`relative_url_root` オプションは URL のなかのフロントコントローラより前の部分を定義します。ほとんどの場合、このオプションはフレームワークによって自動検出されるので、変更する必要はありません。

`response`
----------

*sfContext アクセサ*: `$context->getResponse()`

*デフォルトコンフィギュレーション*:

    [yml]
    response:
      class: sfWebResponse
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        send_http_headers: true

*`test` 環境のデフォルトコンフィギュレーション*:

    [yml]
    response:
      class: sfWebResponse
      param:
        send_http_headers: false

### ~`send_http_headers`~

`send_http_headers` オプションは、レスポンスのコンテンツに加えて HTTP レスポンスヘッダーを送信するかどうかを決めます。ヘッダーを実際に送信するのは PHP の `header()` 関数です。出力の後でヘッダーを送信しようとすると、この関数が警告を発してくれるので、このオプションはテストの際に重宝します。

### ~`charset`~

`charset` オプションはレスポンスに使われる文字集合を定義します。デフォルトでは、`settings.yml` ファイルの `charset` 設定が使われます。ほとんどの場合、デフォルトの値で事足ります。

### ~`http_protocol`~

`http_protocol` オプションはレスポンスに使われる HTTP プロトコルのバージョンを定義します。デフォルトでは、利用可能であればスーパーグローバルの `$_SERVER['SERVER_PROTOCOL']` の値が使われ、それ以外の場合は `HTTP/1.0` が使われます。

`user`
------

*sfContext のアクセサ*: `$context->getUser()`

*デフォルトコンフィギュレーション*:

    [yml]
    user:
      class: myUser
      param:
        timeout:         1800
        logging:         %SF_LOGGING_ENABLED%
        use_flash:       true
        default_culture: %SF_DEFAULT_CULTURE%

>**NOTE**
>デフォルトでは、`myUser` クラスは `sfBasicSecurityUser` 基底クラスを継承します。この基底クラスは [`security.yml`](#chapter_08) ファイルのなかで変更できます。

### ~`timeout`~

`timeout` オプションはユーザー認証のタイムアウトを定義します。このオプションはセッションのタイムアウトとは関係ありません。デフォルトでは、30分間何もしていないユーザーの認証は自動的に解除されます。

このオプションを利用できるクラスは `sfBasicSecurityUser` 基底クラスを継承するユーザークラスにかぎられます。`myUser`生成クラスを具体例にあげることができます。

>**NOTE**
>予期せぬふるまいに遭遇しなくてすむように、ユーザークラスはセッションガベージコレクタの最長有効期間 (`session.gc_maxlifetime`) をタイムアウトよりもはるかに長く設けます。

### ~`use_flash`~

`use_flash` オプションはフラッシュコンポーネントを有効もしくは無効にします。

### ~`default_culture`~

`default_culture` オプションはサイトにはじめて訪問したユーザーのデフォルトカルチャを定義します。デフォルトでは、`settings.yml` ファイルの `default_culture` 設定が使われ、たいていの場合、この値で事足ります。

>**CAUTION**
>`factories.yml` もしくは `settings.yml` ファイルの ~`default_culture`~ 設定を変更して結果を確認するには、ブラウザの Cookie を消去する必要があります。

`storage`
---------

HTTP リクエストにおけるユーザーデータの一貫性を保つために、ユーザーファクトリはストレージファクトリを使います。

*sfContext アクセサ*: `$context->getStorage()`

*デフォルトコンフィギュレーション*:

    [yml]
    storage:
      class: sfSessionStorage
      param:
        session_name: symfony

*`test` 環境のデフォルトコンフィギュレーション*:

    [yml]
    storage:
      class: sfSessionTestStorage
      param:
        session_path: %SF_TEST_CACHE_DIR%/sessions

### ~`auto_start`~

`auto_start` オプションは (`session_start()` 関数を通じて) PHP セッションの自動開始を有効もしくは無効にします。

### ~`session_name`~

`session_name` オプションはユーザーセッションの保存に使われる Cookie の名前を定義します。デフォルトの名前は `symfony` で、このことが意味するのは、すべてのアプリケーションが同じ Cookie (と対応する認証と承認) を共有するということです。

### `session_set_cookie_params()` パラメータ

`storage` ファクトリは [`session_set_cookie_params()`](http://www.php.net/session_set_cookie_params) 関数に次のオプションの値を渡します。

 * ~`session_cookie_lifetime`~: セッション Cookie の有効期間。秒単位で定義されます。
 * ~`session_cookie_path`~: Cookie が機能するドメインのパスです。ドメインのパスに単独のスラッシュ (`/`) が使われます。
 * ~`session_cookie_domain`~: Cookie のドメインで、たとえば `www.php.net` です。すべてのサブドメインで Cookie が見えるようにするには、`.php.net` のようにドメインのプレフィックスとしてドットをつけなければなりません。
 * ~`session_cookie_secure`~: このオプションに `true` がセットされている場合、Cookie はセキュアなコネクションを通じてのみ送信されます。
 * ~`session_cookie_httponly`~: このオプションに `true` がセットされている場合、セッション Cookie を設定する際に、PHP は `httponly` フラグを送信しようとします。

>**NOTE**
>PHP 公式マニュアルにおいて、それぞれのオプションの説明が  `session_set_cookie_params()` 関数のページに記載されています。

### ~`session_cache_limiter`~

`session_cache_limiter` オプションは `null` にセットされている場合 (デフォルト)、このオプションの値は  `php.ini` ファイルにしたがって PHP によって自動的にセットされます。ほかのすべての値に関して、PHP の
[`session_cache_limiter()`](http://www.php.net/session_cache_limiter) 関数が呼び出され、オプションの値は引数として渡されます。

### データベースストレージ固有のオプション

`sfDatabaseSessionStorage` クラスを継承するストレージを使う場合には、次の追加オプションを選べます。

 * ~`database`~:     データベースの名前 (必須)
 * ~`db_table`~:     テーブルの名前 (必須)
 * ~`db_id_col`~:    プライマリキーのカラムの名前 (デフォルトは `sess_id`)
 * ~`db_data_col`~:  データカラムの名前 (デフォルトは `sess_data`)
 * ~`db_time_col`~:  時間カラムの名前 (デフォルトは `sess_time`)

`view_cache_manager`
--------------------

*sfContext アクセサ*: `$context->getViewCacheManager()`

*デフォルトコンフィギュレーション*:

    [yml]
    view_cache_manager:
      class: sfViewCacheManager
      param:
        cache_key_use_vary_headers: true
        cache_key_use_host_name:    true

>**CAUTION**
>[`cache`](#chapter_04_sub_cache) 設定に `true` がセットされている場合にかぎり、このファクトリは作られます。

このファクトリのコンフィギュレーションの大半は `view_cache` ファクトリを通じて変更されます。`view_cache` ファクトリはビューキャッシュマネージャによって使われる内部のキャッシュオブジェクトを定義します。

### ~`cache_key_use_vary_headers`~

`cache_key_use_vary_headers` オプションは Vary ヘッダーの部分をキャッシュキーに入れるどうかを指定します。`vary` キャッシュパラメータで指定されるように、このオプションの実例はページキャッシュが HTTP ヘッダーに依存するかどうかを伝えることです (デフォルト: `true`)。

### ~`cache_key_use_host_name`~

`cache_key_use_host_name` オプションはキャッシュキーにホスト名の部分が含まれるかどうかを指定します。このオプションの実例は、ページキャッシュがホスト名に依存するかどうかを伝えることです (デフォルト: `true`)。

`view_cache`
------------

*sfContext アクセサ*: なし (`view_cache_manager` ファクトリによって直接使われます)

*デフォルトコンフィギュレーション*:

    [yml]
    view_cache:
      class: sfFileCache
      param:
        automatic_cleaning_factor: 0
        cache_dir:                 %SF_TEMPLATE_CACHE_DIR%
        lifetime:                  86400
        prefix:                    %SF_APP_DIR%/template

>**CAUTION**
>[`cache`](#chapter_04_sub_cache) 設定に `true` がセットされている場合にかぎり、このファクトリは定義されます。

`view_cache` ファクトリは `sfCache` 抽象クラスを継承するキャッシュクラスを定義しなければなりません (くわしい説明はキャッシュの節をご参照ください)。

`i18n`
------

*sfContext アクセサ*: `$context->getI18N()`

*デフォルトコンフィギュレーション*:

    [yml]
    i18n:
      class: sfI18N
      param:
        source:               XLIFF
        debug:                false
        untranslated_prefix:  "[T]"
        untranslated_suffix:  "[/T]"
        cache:
          class: sfFileCache
          param:
            automatic_cleaning_factor: 0
            cache_dir:                 %SF_I18N_CACHE_DIR%
            lifetime:                  31556926
            prefix:                    %SF_APP_DIR%/i18n

>**CAUTION**
>[`i18n`](#chapter_04_sub_i18n) 設定に `true` がセットされている場合にかぎり、このファクトリは定義されます。

### ~`source`~

`source` オプションは翻訳コンテナの種類を定義します。

*組み込みのコンテナ*: `XLIFF`、`SQLite`、`MySQL` と `gettext`

### ~`debug`~

`debug` オプションはデバッグモードを指定します。このオプションに `true` がセットされている場合、翻訳されていないメッセージはプレフィックスとサフィックスによって飾りつけられます (くわしい説明は下記の節をご参照ください)。

### ~`untranslated_prefix`~

`untranslated_prefix` オプションは翻訳されていないメッセージに使われるプレフィックスを定義します。

### ~`untranslated_suffix`~

`untranslated_suffix` オプションは翻訳されていないメッセージに使われるサフィックスを定義します。

### ~`cache`~

`cache` オプションは国際対応したデータのキャッシュに使われる匿名キャッシュファクトリを定義します (くわしい説明はキャッシュの節をご参照ください)。

`routing`
---------

*sfContext アクセサ*: `$context->getRouting()`

*デフォルトコンフィギュレーション*:

    [yml]
    routing:
      class: sfPatternRouting
      param:
        load_configuration:               true
        suffix:                           ''
        default_module:                   default
        default_action:                   index
        debug:                            %SF_DEBUG%
        logging:                          %SF_LOGGING_ENABLED%
        generate_shortest_url:            false
        extra_parameters_as_query_string: false
        cache:                            ~

### ~`variable_prefixes`~

*デフォルト*: `:`

`variable_prefixes` オプションはルートパターンの変数につけられるプレフィックスのリストを定義します。

### ~`segment_separators`~

*デフォルト*: `/` と `.`

`segment_separators` オプションはルートに使われる区切り文字のリストを定義します。特定のルートを除いて、ルーティング全体でこのオプションをオーバーライドする事態に遭遇することはまずないでしょう。

### ~`generate_shortest_url`~

*デフォルト*: 新しいプロジェクトでは `true`、アップグレードしたプロジェクトでは `false`

`generate_shortest_url` オプションに `true` がセットされている場合、ルーティングシステムは実現可能な最短ルートを生成します。symfony 1.0 と 1.1 と後方互換性のあるルートが必要であれば、このオプションに `false` をセットします。

### ~`extra_parameters_as_query_string`~

*デフォルト*: 新しいプロジェクトでは `true`、アップグレードしたプロジェクトでは `false`

`extra_parameters_as_query_string` オプションに `true` がセットされていれば、ルート生成に使われていないパラメータはクエリ文字列に変換されます。symfony 1.0 もしくは1.1のふるまいに戻すのであれば、このオプションに `false` をセットします。これらのバージョンでは、ルート生成に使われていないパラメータはルーティングシステムによって無視されるだけでした。

### ~`cache`~

*デフォルト*: なし

`cache` オプションはルーティングコンフィギュレーションとデータのキャッシュに使われる匿名キャッシュファクトリを定義します (くわしい説明はキャッシュの節をご参照ください)。

### ~`suffix`~

*デフォルト*: なし

すべてのルートに使われるデフォルトのサフィックスです。このオプションは推奨されていません。

### ~`load_configuration`~

*デフォルト*: `true`

`load_configuration` オプションは `routing.yml` ファイルをオートロードの対象に加えて、パースするかどうかを決めます。symfony プロジェクト外部の symfony ルーティングシステムを利用したいのであれば、このオプションに `false` をセットします。

### ~`lazy_routes_deserialize`~

*デフォルト*: `false`

`lazy_routes_deserialize` 設定に `true` がセットされていれば、ルーティングキャッシュの遅延デシリアライゼーションが有効になります。たくさんのルートをかかえており、もっともマッチするルートの順番が最初のほうにあれば、アプリケーションのパフォーマンスは改善されます。状況によっては、パフォーマンスにわるい影響を及ぼす可能性があるので、運用サーバーにデプロイする前に、この設定をテストしておくことをぜひおすすめします。

### ~`lookup_cache_dedicated_keys`~

*デフォルト*: `false`

`lookup_cache_dedicated_keys` 設定は生成されるルーティングキャッシュの形式を決めます。この設定に `false` がセットされている場合、キャッシュは1つの大きな値として保存されます。この設定に `true` がセットされている場合、キャッシュを保存するためにルートごとに専用のクラスが用意されます。この設定はパフォーマンスを最適化します。

経験則によれば、ファイルベースのキャッシュクラス (たとえば `sfFileCache`) を使う場合には、この設定に `false` を、メモリベースのキャッシュクラス (たとえば `sfAPCCache`) を使う場合には、この設定に `true` をセットするとよいでしょう。

`logger`
--------

*sfContext アクセサ*: `$context->getLogger()`

*デフォルトコンフィギュレーション*:

    [yml]
    logger:
      class: sfAggregateLogger
      param:
        level: debug
        loggers:
          sf_web_debug:
            class: sfWebDebugLogger
            param:
              level: debug
              condition:       %SF_WEB_DEBUG%
              xdebug_logging:  false
              web_debug_class: sfWebDebug
          sf_file_debug:
            class: sfFileLogger
            param:
              level: debug
              file: %SF_LOG_DIR%/%SF_APP%_%SF_ENVIRONMENT%.log

*`prod` 環境のデフォルトコンフィギュレーション*:

    [yml]
    logger:
      class:   sfNoLogger
      param:
        level:   err
        loggers: ~

`sfAggregateLogger` クラスを使いたくなければ、`loggers` パラメータに `null` をセットしておくことをお忘れなく。

>**CAUTION**
>このファクトリはつねに定義されていますが、ロギングが実行されるのは `logging_enabled` 設定に `true` がセットされている場合にかぎられます。

### ~`level`~

`level` オプションはロガーのレベルを定義します。

*利用可能な値*: `EMERG`、`ALERT`、`CRIT`、`ERR`、`WARNING`、`NOTICE`、`INFO` もしくは `DEBUG`

### ~`loggers`~

`loggers` オプションは使用するロガーのリストを定義します。リストは匿名ロガーファクトリの配列です。

*組み込みのロガークラス*: `sfConsoleLogger`、`sfFileLogger`、`sfNoLogger`、
`sfStreamLogger` と `sfVarLogger`

`controller`
------------

*sfContext アクセサ*: `$context->getController()`

*デフォルトコンフィギュレーション*:

    [yml]
    controller:
      class: sfFrontWebController

匿名キャッシュファクトリ
-------------------------

コンフィギュレーションのなかでキャッシュオブジェクトが定義されていれば、いくつかのファクトリ (`view_cache`、`i18n` と `routing`) はこのファクトリを利用できます。キャッシュオブジェクトのコンフィギュレーションはすべてのファクトリと似ています。`cache` キーは匿名キャッシュファクトリを定義します。ほかのファクトリと同じように、このファクトリには `class` と `param` エントリが用意されています。`param` エントリはキャッシュクラスで利用可能な任意のオプションをとります。

もっとも重要なのは `prefix` オプションで、このオプションを使うことで異なる環境/アプリケーション/プロジェクトのあいだでキャッシュを共有するもしくは分離することができます。

*組み込みのキャッシュクラス*: 
`sfAPCCache`、`sfEAcceleratorCache`、`sfFileCache`、`sfMemcacheCache`、
`sfNoCache`、`sfSQLiteCache` と `sfXCachCache`
