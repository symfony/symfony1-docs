factories.yml 設定ファイル
==========================

ファクトリーはリクエストが存続するあいだにフレームワークが必要とするコアオブジェクトです。これらは `factories.yml` 設定ファイルで設定され `sfContext` オブジェクトを通して常にアクセス可能です:

    [php]
    // ユーザーファクトリーを取得する
    sfContext::getInstance()->getUser();

アプリケーションのメインの `factories.yml` 設定ファイルは `apps/APP_NAME/config/` ディレクトリで見つかります。

第3章で説明したように、`factories.yml` ファイルは[**環境を認識し**](#chapter_03_environment_awareness)、[**コンフィギュレーションカスケードのメカニズム**](#chapter_03_configuration_cascade)が有効になり、[**定数**](#chapter_03_constants)を収めることができます。

`factories.yml` 設定ファイルは名前つきのファクトリーのリストを収めます:

    [yml]
    FACTORY_1:
      # definition of factory 1

    FACTORY_2:
      # definition of factory 2

    # ...

サポートされるファクトリーの名前は次のとおりです: `controller`、`logger`、`i18n`、`request`、`response`、`routing`、`storage`、`user`、`view_cache` と `view_cache_manager`

`sfContext` がファクトリーを初期化するとき、ファクトリーオブジェクトを設定するために使われるファクトリー (`class`) とパラメーター (`param`) のクラス名の `factories.yml` ファイルを読み込みます:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

ファクトリーをカスタマイズできることは symfony のコアオブジェクトのデフォルトクラスの代わりにカスタムクラスを使うことができることを意味します。これらに送信するパラメーターをカスタマイズすることでこれらのクラスのデフォルトのふるまいを変更することもできます。

ファクトリークラスがオートロードできないとき、`file` パスが定義されファクトリーが作成される前に自動的にインクルードできます:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>`factories.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; プロセスは ~`sfFactoryConfigHandler`~ [クラス](#chapter_14-Other-Configuration-Files_config_handlers_yml)によって自動管理されます。

<div class="pagebreak"></div>

ファクトリー
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
   * [`database`](#chapter_05_sub_database_storage_specific_options)
   * [`db_table`](#chapter_05_sub_database_storage_specific_options)
   * [`db_id_col`](#chapter_05_sub_database_storage_specific_options)
   * [`db_data_col`](#chapter_05_sub_database_storage_specific_options)
   * [`db_time_col`](#chapter_05_sub_database_storage_specific_options)
   * [`session_cache_limiter`](#chapter_05_sub_session_cache_limiter)
   * [`session_cookie_domain`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_httponly`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_lifetime`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_path`](#chapter_05_sub_session_set_cookie_params_parameters)
   * [`session_cookie_secure`](#chapter_05_sub_session_set_cookie_params_parameters)
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

*sfContext アクセサー*: `$context->getMailer()`

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

`charset` オプションはメールメッセージに使う文字集合を定義します。デフォルトでは、`settings.yml` の `charset` 設定が使われます。

### ~`delivery_strategy`~

`delivery_strategy` オプションはEメールメッセージがメーラーによってどのように配信されるのかを定義します。デフォルトでは4つの戦略を選ぶことが可能で、すべての共通ニーズに適しています:

 * `realtime`:       メッセージはリアルタイムで送信されます。

 * `single_address`: メッセージは単独のアドレスに送信されます。

 * `spool`:          メッセージはキューに保存されます。

 * `none`:           メッセージは単に無視されます。

### ~`delivery_address`~

`delivery_address` オプションは `delivery_strategy` が `single_address` にセットされる場合すべてのメッセージの受信者を定義します。

### ~`spool_class`~

`spool_class` オプションは `delivery_strategy` が `spool` にセットされるときに使うスプールクラスを定義します:

  * ~`Swift_FileSpool`~: メッセージはファイルシステムに保存されます。

  * ~`Swift_DoctrineSpool`~: メッセージは Doctrine モデルに保存されます。

  * ~`Swift_PropelSpool`~: メッセージは Propel モデルに保存されます。

>**NOTE**
>スプールがインスタンス化されるとき、~`spool_arguments`~ オプションがコンストラクターの引数として使われます。

### ~`spool_arguments`~

`spool_arguments` オプションはスプールのコンストラクターの引数を定義します。組み込みのキュークラスに利用できるオプションは次のとおりです:

 * `Swift_FileSpool`:

    * キューディレクトリの絶対パス (メッセージはこのディレクトリに保存される)

 * `Swift_DoctrineSpool`:

    * メッセージを保存する Doctrine モデル (デフォルトでは `MailMessage`)

    * メッセージ保存に使われるカラムの名前 (デフォルトでは `message`)

    * 送信するメッセージを読み出すために呼び出すメソッド (オプション)。これは引数としてキューオプションを受け取る。

 * `Swift_PropelSpool`:

    * メッセージを保存するために使う Propel モデル (デフォルトでは `MailMessage`)

    * メッセージ保存に使うカラム名 (デフォルトでは `message`)

    * 送信するメッセージを読み出すために呼び出すメソッド (オプション)。これは引数としてキューオプションを受け取ります。

Doctrine スプールの典型的なコンフィギュレーションは次のとおりです:

    [yml]
    # configuration in factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

### ~`transport`~

`transport` オプションはEメールメッセージを実際に送信するために使うトランスポートを定義します。

`class` 設定は `Swift_Transport` を実装する任意のクラスになります。デフォルトでは3つの設定が提供されます:

  * ~`Swift_SmtpTransport`~: メッセージを送信するために SMTP サーバーを使います。

  * ~`Swift_SendmailTransport`~: メッセージを送信するために `sendmail` を使います。

  * ~`Swift_MailTransport`~: メッセージを送信するために PHP ネイティブの `mail()` 関数を使います。

`param` 設定をセットすることでトランスポートをさらに設定できます。Swift Mailer の公式ドキュメントの ["Transport Types"](http://swiftmailer.org/docs/transport-types) の節で組み込みの転送クラスと異なるパラメーターに関して知る必要のあるすべての知識が説明されています。

`request`
---------

*sfContext アクセサー*: `$context->getRequest()`

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

`path_info_array` オプションは情報を読み取るために使われるグローバルな PHP 配列を定義します。設定によってはデフォルトの `SERVER` の値を `ENV` に変更するとよいでしょう。

### ~`path_info_key`~

`path_info_key` オプションは `PATH_INFO` の情報が見つかるキーを定義します。

`IIFR` もしくは `ISAPI` のような rewrite モジュールつきの IIS を使う場合、この値を `HTTP_X_REWRITE_URL` に変更するとよいでしょう。

### ~`formats`~

`formats` オプションはファイル拡張子と `Content-Type` の配列です。リクエスト URI の拡張子に基づいて、レスポンスの `Content-Type` を自動管理するために symfony によって使われます。

### ~`relative_url_root`~

`relative_url_root` オプションはフロントコントローラーの前の URL の部分を定義します。たいていの場合、これはフレームワークによって自動的に検出されるので変更する必要はありません。

`response`
----------

*sfContext アクセサー*: `$context->getResponse()`

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

`send_http_headers` オプションはレスポンス内に含まれるコンテンツと一緒に HTTP レスポンスヘッダーを送信するかを指定します。この設定は出力の後でヘッダーを送信しようとすると警告を発する PHP の `header()` 関数でヘッダーが送信されるので、テストの際に便利です。

### ~`charset`~

`charset` オプションはレスポンスに使う文字集合を定義します。デフォルトでは、`settings.yml` の `charset` 設定が使われます。 

### ~`http_protocol`~

`http_protocol` オプションはレスポンスに使う HTTP プロトコルのバージョンを定義します。デフォルトでは、利用可能であれば `$_SERVER['SERVER_PROTOCOL']` の値をチェックします。デフォルトは `HTTP/1.0` です。

`user`
------

*sfContext のアクセサー*: `$context->getUser()`

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
>デフォルトでは、`myUser` クラスは `sfBasicSecurityUser` を継承します。これは [`security.yml`](#chapter_08) 設定ファイルで設定できます。

### ~`timeout`~

`timeout` オプションはユーザー認証のタイムアウトを定義します。これはセッションのタイムアウトとは関係ありません。デフォルトの設定は30分間何もしていないユーザーの認証を自動的に解除します。

`sfBasicSecurityUser` 基底クラスを継承するユーザークラスのみがこの設定を使います。これは `myUser`クラス が生成される例が当てはまります。

>**NOTE**
>予期していないふるまいを避けるために、ユーザークラスはセッションガーベッジコレクターの最長有効期間 (`session.gc_maxlifetime`) をタイムアウトよりも長くなるように強制します。

### ~`use_flash`~

`use_flash` オプションは flash コンポーネントを有効もしくは無効にします。

### ~`default_culture`~

`default_culture` オプションはサイトに初めて訪問したユーザーのためにデフォルトの culture を定義します。デフォルトでは、`settings.yml` の `default_culture` が使われ、たいていの場合これで十分です。

>**CAUTION**
>`factories.yml` もしくは `settings.yml` の ~`default_culture`~ 設定を変更する場合、結果を確認するためにブラウザーの Cookie をクリアする必要があります。

`storage`
---------

ストレージファクトリーは HTTP リクエストのあいだのユーザーデータを一貫させるためにユーザーファクトリーによって使われます。

*sfContext アクセサー*: `$context->getStorage()`

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

`auto_start` オプションは (`session_start()` 関数を通して) PHP のセッション自動開始機能を有効もしくは無効にします。

### ~`session_name`~

`session_name` オプションはユーザーセッションを保存するために symfony によって使われる Cookie の名前を定義します。デフォルトの名前は `symfony` で、すべてのアプリケーションが同じ Cookie を共有することを意味します (そして対応する認証と権限付与も)。

### `session_set_cookie_params()` パラメーター

`storage` ファクトリーは次のオプションの値で [`session_set_cookie_params()`](http://www.php.net/session_set_cookie_params) 関数を呼び出します:

 * ~`session_cookie_lifetime`~: セッション Cookie の有効期間。秒単位で定義します。
 * ~`session_cookie_path`~:   Cookie が機能するドメイン上のパス。ドメインのすべてのパスに対して単独のスラッシュ (`/`)を使います。
 * ~`session_cookie_domain`~: Cookie のドメイン、たとえば `www.php.net`。すべてのサブドメインに Cookie を見えるようにするためには `.php.net` のようにプレフィックスとしてドットをドメインにつけなければなりません。
 * ~`session_cookie_secure`~: `true` の場合 Cookie はセキュアなコネクションを通してのみ送信されます。
 * ~`session_cookie_httponly`~: `true` にセットされている場合、セッション Cookie を設定する際に PHP は `httponly` フラグを送信しようとします。

>**NOTE**
>それぞれのオプションの説明は `session_set_cookie_params()` 関数の説明は PHP の公式サイトに説明に由来しています。

### ~`session_cache_limiter`~

`session_cache_limiter` オプションがセットされている場合、PHP の [`session_cache_limiter()`](http://www.php.net/session_cache_limiter) 関数が呼び出され引数としてオプションの値が渡されます。

### データベースストレージ固有のオプション

`sfDatabaseSessionStorage` クラスを継承するストレージを使うとき、いくつかの追加オプションが利用可能です:

 * ~`database`~:     データベースの名前 (必須)
 * ~`db_table`~:     テーブルの名前 (必須)
 * ~`db_id_col`~:    主キーのカラムの名前 (デフォルトは `sess_id`)
 * ~`db_data_col`~:  データカラムの名前 (デフォルトは `sess_data`)
 * ~`db_time_col`~:  時間カラムの名前 (デフォルトは `sess_time`)

`view_cache_manager`
--------------------

*sfContext アクセサー*: `$context->getViewCacheManager()`

*デフォルトコンフィギュレーション*:

    [yml]
    view_cache_manager:
      class: sfViewCacheManager
      param:
        cache_key_use_vary_headers: true
        cache_key_use_host_name:    true

>**CAUTION**
>[`cache`](#chapter_04-Settings_sub_cache) 設定が `on` にセットされている場合にのみこのファクトリーは作成されます。

このファクトリーのコンフィギュレーションの大半は `view_cache` ファクトリー経由で行われます。`view_cache` ファクトリーはビューキャッシュマネージャーによって使われる内部のキャッシュオブジェクトを定義します。

### ~`cache_key_use_vary_headers`~

`cache_key_use_vary_headers` オプションはキャッシュキーが Vary ヘッダーの部分を含むか指定します。実際には、`vary` キャッシュパラメーターで指定されるように、これはページキャッシュが HTTP ヘッダーに依存することを伝えます(デフォルト値: `true`)。

### ~`cache_key_use_host_name`~

`cache_key_use_host_name` オプションはキャッシュキーがホスト名の部分を含むか指定します。実際には、これはページキャッシュがホスト名に依存するかを伝えます (デフォルト値: `true`)。

`view_cache`
------------

*sfContext アクセサー*: なし (`view_cache_manager` ファクトリーによって直接使われる)

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
>[`cache`](#chapter_04-Settings_sub_cache) 設定が `on` にセットされている場合のみこのファクトリーが定義されます。

`view_cache` ファクトリーは `sfCache` を継承するキャッシュクラスを定義します (詳細な情報はキャッシュの節を参照)。

`i18n`
------

*sfContext アクセサー*: `$context->getI18N()`

*デフォルトコンフィギュレーション*:

    [yml]
    i18n:
      class: sfI18N
      param:
        source:               XLIFF
        debug:                off
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
>[`i18n`](#chapter_04-Settings_sub_i18n) 設定が `on` にセットされている場合のみこのファクトリーが定義されます。

### ~`source`~

`source` オプションは翻訳コンテナーの種類を定義します。

*組み込みのコンテナー*: `XLIFF`、`SQLite`、`MySQL` と `gettext`

### ~`debug`~

`debug` オプションはデバッグモードをセットします。`on` にセットされる場合、未翻訳のメッセージはプレフィックスとサフィックスによってデコレートされます (下記を参照)。

### ~`untranslated_prefix`~

`untranslated_prefix` は未翻訳のメッセージに使われるプレフィックスを定義します。

### ~`untranslated_suffix`~

`untranslated_suffix` は未翻訳のメッセージに使われるサフィックスを定義します。

### ~`cache`~

`cache` オプションは国際化データのキャッシュに使われる匿名キャッシュファクトリーを定義します (詳細な情報はキャッシュのセクションを参照)。

`routing`
---------

*sfContext アクセサー*: `$context->getRouting()`

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
        cache: 

### ~`variable_prefixes`~

*デフォルト*: `:`

`variable_prefixes` オプションはルートのパターンの変数名を始める文字のリストを定義します。

### ~`segment_separators`~

*デフォルト*: `/` と `.`

`segment_separators` オプションはルートセグメントの区切り文字のリストを定義します。たいていの場合、特定のルート以外、ルーティング全体に対してこのオプションをオーバーライドすることはないでしょう。

### ~`generate_shortest_url`~

*デフォルト*: 新しいプロジェクトでは `true`、アップグレードしたプロジェクトには `false`

`true` にセットされる場合、`generate_shortest_url` オプションはルーティングシステムに実現可能な最短ルートを生成するよう伝えます。symfony 1.0 と1.1との後方互換性のあるルートがほしい場合は、`false` にセットします。

### ~`extra_parameters_as_query_string`~

*デフォルト*: 新しいプロジェクトには `true`、アップグレードしたプロジェクトには `false`

ルートの生成に使われないパラメーターがあるとき、`extra_parameters_as_query_string` はルート生成に利用していないパラメーターをクエリ文字列に変換することが可能です。symfony 1.0 もしくは1.1のふるまいを代替するには `false` にセットします。このバージョンでは、ルート生成に利用していないパラメーターはルーティングシステムによって無視されるだけでした。

### ~`cache`~

*デフォルト*: なし

`cache` オプションはルーティング設定とデータのキャッシュに使われる匿名キャッシュファクトリーを定義します(詳細な情報はキャッシュセクションを参照)。

### ~`suffix`~

*デフォルト*: なし

すべてのルートに使われるデフォルトのサフィックスです。このオプションは非推奨でもはや役に立ちません。

### ~`load_configuration`~

*デフォルト*: `true`

`load_configuration` オプションは `routing.yml` ファイルが自動的にロードされ解析される必要があるかどうかを定義します。symfony プロジェクトではない外部のルーティングシステムを使いたい場合 `false` にセットします。

### ~`lazy_routes_deserialize`~

*デフォルト*: `false`

`true` にセットする場合、`lazy_routes_deserialize` 設定はルーティングキャッシュの遅延デシリアライズを有効にします。
たくさんのルートをかかえており、マッチするルートが最初のものである場合この設定はアプリケーションのパフォーマンスを改善できます。特定の状況ではパフォーマンスに悪い影響を与える可能性があるので運用サーバーにデプロイする前に設定をテストすることを強くおすすめします。

### ~`lookup_cache_dedicated_keys`~

*デフォルト*: `false`

`lookup_cache_dedicated_keys` 設定はルーティングキャッシュが構成される方法を決定します。`false` にセットされている場合、キャッシュはひとつの大きな値として保存されます; `true` にセットされている場合それぞれのルートは独自のキャッシュストアを持ちます。この設定はパフォーマンス最適化設定です。

経験則として、ファイルベースのキャッシュクラス (たとえば `sfFileCache`) を使う際にはこの設定を `false` に、メモリベースのキャッシュクラス (たとえば `sfAPCCache`) を使う際には `true` にするとよいです。

`logger`
--------

*sfContext アクセサー*: `$context->getLogger()`

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
              xdebug_logging:  true
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

>**CAUTION**
>このファクトリーは常に定義されますが、`logging_enabled` 設定が `on` にセットされている場合のみロギングが行われます。

### ~`level`~

`level` オプションはロガーのレベルを定義します。

*可能な値*: `EMERG`、`ALERT`、`CRIT`、`ERR`、`WARNING`、`NOTICE`、`INFO`もしくは`DEBUG`

### ~`loggers`~

`loggers` オプションは使用するロガーのリストを定義します。リストは匿名ロガーファクトリーの配列です。

*組み込みのロガークラス*: `sfConsoleLogger`、`sfFileLogger`、`sfNoLogger`、
`sfStreamLogger` と `sfVarLogger`

`controller`
------------

*sfContext アクセサー*: `$context->getController()`

*デフォルトコンフィギュレーション*:

    [yml]
    controller:
      class: sfFrontWebController

匿名キャッシュファクトリー
------------------------

いくつかのファクトリー (`view_cache`、`i18n` と `routing`) はそれぞれ設定で定義されている場合、効果のあるキャッシュオブジェクトを利用できます。キャッシュオブジェクトの設定はすべてのファクトリーと似ています。`cache` キーは匿名キャッシュファクトリーを定義します。ほかのファクトリーと同じように、これは `class` と `param` エントリーをとります。`param` エントリーは与えられたキャッシュクラスで利用可能な任意のオプションをとります。

もっとも重要なのは `prefix` オプションで異なる環境／アプリケーション/プロジェクトのあいだでキャッシュを共有するもしくは分離できるようにします。

*組み込みのキャッシュクラス*: `sfAPCCache`、`sfEAcceleratorCache`、`sfFileCache`、`sfMemcacheCache`、`sfNoCache`、`sfSQLiteCache` と `sfXCachCache`
