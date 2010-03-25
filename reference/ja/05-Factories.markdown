factories.yml 設定ファイル
==========================

ファクトリはリクエストが存続しているあいだにフレームワークが必要とするコアオブジェクトです。これらのコンフィギュレーションは `factories.yml` 設定ファイルで変更され `sfContext` オブジェクトを通してつねにアクセスできます:

    [php]
    // ユーザーファクトリを取得する
    sfContext::getInstance()->getUser();

アプリケーションのメイン設定ファイルである `factories.yml` は `apps/APP_NAME/config/` ディレクトリで見つかります。

[第3章](#chapter_03)で説明したように、`factories.yml` ファイルでは**環境が認識され**、**コンフィギュレーションカスケードのメカニズム**がはたらき、**定数**を収めることができます。

`factories.yml` 設定ファイルには名前つきファクトリのリストが収められています:

    [yml]
    FACTORY_1:
      # ファクトリ1の定義

    FACTORY_2:
      # ファクトリ2の定義

    # ...

サポートされるファクトリの名前は次のとおりです: 
`controller`、`logger`、`i18n`、`request`、`response`、`routing`、`storage`、
`user`、`view_cache` と `view_cache_manager`

`sfContext` がファクトリを初期化するとき、ファクトリオブジェクトを設定するために使われるファクトリのクラス名 (`class`) とパラメータ (`param`) を得るために `factories.yml` ファイルを読み込みます:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

ファクトリをカスタマイズできることは symfony コアのデフォルトクラスの代わりにカスタムクラスを使うことができることを意味します。これらのクラスに送信するパラメータをカスタマイズすることでこれらのクラスのデフォルトのふるまいを変更することもできます。

ファクトリクラスをオートロードできないとき、`file` パスが定義されファクトリが作られる前に自動的にインクルードされます:

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>`factories.yml` 設定ファイルは PHP ファイルとしてキャッシュされます; 処理は ~`sfFactoryConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。

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

`charset` オプションはメールメッセージに使う文字集合を定義します。デフォルトでは、`settings.yml` の `charset` 設定が使われます。

### ~`delivery_strategy`~

`delivery_strategy` オプションはメーラーによってメールメッセージがどのように配信されるのかを定義します。デフォルトでは4つの戦略を選ぶことが可能で、すべての共通ニーズに適しています:

 * `realtime`:       メッセージはリアルタイムで送信されます。

 * `single_address`: メッセージは単独のアドレスに送信されます。

 * `spool`:          メッセージはキューに保存されます。

 * `none`:           メッセージは単に無視されます。

### ~`delivery_address`~

`delivery_address` オプションは `delivery_strategy` が `single_address` にセットされているときにすべてのメッセージの受信アドレスを定義します。

### ~`spool_class`~

`spool_class` オプションは `delivery_strategy` が `spool` にセットされているときに使うスプールクラスを定義します:

  * ~`Swift_FileSpool`~: メッセージはファイルシステムに保存されます。

  * ~`Swift_DoctrineSpool`~: メッセージは Doctrine モデルに保存されます。

  * ~`Swift_PropelSpool`~: メッセージは Propel モデルに保存されます。

>**NOTE**
>スプールがインスタンス化されるとき、~`spool_arguments`~ オプションがコンストラクタの引数に使われます。

### ~`spool_arguments`~

`spool_arguments` オプションはスプールのコンストラクタの引数を定義します。組み込みのキュークラスで利用できるオプションは次のとおりです:

 * `Swift_FileSpool`:

    * キューディレクトリの絶対パス (メッセージはこのディレクトリに保存される)

 * `Swift_DoctrineSpool`:

    * メッセージを保存する Doctrine モデル (デフォルトは `MailMessage`)

    * メッセージ保存に使われるカラムの名前 (デフォルトは `message`)

    * 送信するメッセージを読み出すために呼び出すメソッド (オプション)。このメソッドはキューオプションを引数にとります。

 * `Swift_PropelSpool`:

    * メッセージを保存するために使う Propel モデル (デフォルトは `MailMessage`)

    * メッセージ保存に使うカラムの名前 (デフォルトは `message`)

    * 送信するメッセージを読み出すために呼び出すメソッド (オプション)。このメソッドは現在の Criteria を引数にとります。

Doctrine スプールのコンフィギュレーションの典型例は次のとおりです:

    [yml]
    # factories.yml のコンフィギュレーション
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

### ~`transport`~

`transport` オプションはメールメッセージを実際に送信するために使うトランスポートを定義します。

`class` 設定は `Swift_Transport` を実装する任意のクラスになります。デフォルトでは3つの設定が提供されます:

  * ~`Swift_SmtpTransport`~: メッセージを送信するために SMTP サーバーを使います。

  * ~`Swift_SendmailTransport`~: メッセージを送信するために `sendmail` を使います。

  * ~`Swift_MailTransport`~: メッセージを送信するために PHP ネイティブの `mail()` 関数を使います。

`param` 設定をセットすることでトランスポートを細かく調整できます。組み込みのトランスポートクラスと異なるパラメータに関して知る必要のあるすべての知識は Swift Mailer の公式ドキュメントの [「Transport Types」](http://swiftmailer.org/docs/transport-types) のセクションで説明されています。

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

`path_info_array` オプションは情報を検索するために使われるグローバルな PHP 配列を定義します。コンフィギュレーションしだいではデフォルトを `SERVER` から `ENV` に変更するとよいでしょう。

### ~`path_info_key`~

`path_info_key` オプションは `PATH_INFO` の情報を見つけられるキーを定義します。

`IIFR` もしくは `ISAPI` のような rewrite モジュールが付属する IIS を使う場合、このオプションの値を `HTTP_X_REWRITE_URL` に変更するとよいでしょう。

### ~`formats`~

`formats` オプションはファイルの拡張子と `Content-Type` の配列です。リクエスト URI の拡張子にもとづいてレスポンスの `Content-Type` を自動管理するために、このオプションは symfony によって使われます。

### ~`relative_url_root`~

`relative_url_root` オプションは URL のなかのフロントコントローラより前の部分を定義します。ほとんどの場合、このオプションはフレームワークによって自動的に検出されるので変更する必要はありません。

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

`send_http_headers` オプションはレスポンスのコンテンツに加えて HTTP レスポンスヘッダーを送信するかどうかを指定します。出力の後でヘッダーを送信しようとすると警告を発してくれる PHP の `header()` 関数でヘッダーが送信されるので、この設定はテストの際にもっとも役立ちます。

### ~`charset`~

`charset` オプションはレスポンスに使う文字集合を定義します。デフォルトでは、`settings.yml` の `charset` 設定が使われます。ほとんどの場合、デフォルトで十分です。

### ~`http_protocol`~

`http_protocol` オプションはレスポンスに使う HTTP プロトコルのバージョンを定義します。デフォルトでは、利用可能であれば `$_SERVER['SERVER_PROTOCOL']` の値もしくは `HTTP/1.0` です。

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
>デフォルトでは、`myUser` クラスは `sfBasicSecurityUser` を継承します。これは [`security.yml`](#chapter_08) 設定ファイルで設定できます。

### ~`timeout`~

`timeout` オプションはユーザー認証のタイムアウトを定義します。このオプションはセッションのタイムアウトとは関係ありません。デフォルトの設定では30分間何もしていないユーザーの認証が自動的に解除されます。

`sfBasicSecurityUser` 基底クラスを継承するユーザークラスだけがこのオプションを使います。具体例として `myUser`生成クラスが当てはまります。

>**NOTE**
>予期しないふるまいを避けるために、ユーザークラスはセッションガベージコレクタの最長有効期間 (`session.gc_maxlifetime`) をタイムアウトよりもはるかに長くなるように強制します。

### ~`use_flash`~

`use_flash` オプションはフラッシュコンポーネントを有効もしくは無効にします。

### ~`default_culture`~

`default_culture` オプションはサイトに初めて訪問したユーザーのためにデフォルトカルチャを定義します。デフォルトでは、`settings.yml` の `default_culture` が使われ、ほとんどの場合これで十分です。

>**CAUTION**
>`factories.yml` もしくは `settings.yml` の ~`default_culture`~ 設定を変更する場合、結果を確認するためにブラウザのクッキーを消去する必要があります。

`storage`
---------

HTTP リクエストのあいだのユーザーデータの一貫性を保つためにストレージファクトリがユーザーファクトリによって使われます。

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

`auto_start` オプションは (`session_start()` 関数を通して) PHP のセッション自動開始機能を有効もしくは無効にします。

### ~`session_name`~

`session_name` オプションはユーザーセッションを保存するために symfony によって使われるクッキーの名前を定義します。デフォルトの名前は `symfony` で、このことはすべてのアプリケーションが同じクッキー (と対応する認証と承認) を共有することを意味します。

### `session_set_cookie_params()` パラメータ

`storage` ファクトリは [`session_set_cookie_params()`](http://www.php.net/session_set_cookie_params) 関数に次のオプションを渡します:

 * ~`session_cookie_lifetime`~: セッションクッキーの有効期間。秒単位で定義されます。
 * ~`session_cookie_path`~: クッキーが機能するドメインのパスです。ドメインのすべてのパスに対して単独のスラッシュ (`/`) を使います。
 * ~`session_cookie_domain`~: クッキーのドメインで、たとえば `www.php.net` です。すべてのサブドメインでクッキーが見えるようにするには `.php.net` のようにドメインの接頭辞としてドットをつけなければなりません。
 * ~`session_cookie_secure`~: `true` にセットされている場合、クッキーはセキュアなコネクションを通してのみ送信されます。
 * ~`session_cookie_httponly`~: `true` にセットされている場合、セッションクッキーを設定する際に PHP は `httponly` フラグを送信しようとします。

>**NOTE**
>それぞれのオプションの説明は PHP 公式マニュアルの `session_set_cookie_params()` 関数のページにあります。

### ~`session_cache_limiter`~

`session_cache_limiter` オプションがセットされている場合、PHP の [`session_cache_limiter()`](http://www.php.net/session_cache_limiter) 関数が呼び出され引数としてオプションの値が渡されます。

### データベースストレージ固有のオプション

`sfDatabaseSessionStorage` クラスを継承するストレージを使うとき、いくつかの追加オプションが利用できます:

 * ~`database`~:     データベースの名前 (必須)
 * ~`db_table`~:     テーブルの名前 (必須)
 * ~`db_id_col`~:    主キーのカラムの名前 (デフォルトは `sess_id`)
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
>[`cache`](#chapter_04_sub_cache) 設定が `true` にセットされている場合にのみこのファクトリが作られます。

このファクトリのコンフィギュレーションの大半は `view_cache` ファクトリ経由で変更されます。`view_cache` ファクトリはビューキャッシュマネージャによって使われる内部のキャッシュオブジェクトを定義します。

### ~`cache_key_use_vary_headers`~

`cache_key_use_vary_headers` オプションはキャッシュキーが Vary ヘッダーの部分を含むかどうかを指定します。実際には `vary` キャッシュパラメータで指定されるように、このオプションはページキャッシュが HTTP ヘッダーに依存するかどうかを伝えるのに使われます (デフォルト: `true`)。

### ~`cache_key_use_host_name`~

`cache_key_use_host_name` オプションはキャッシュキーがホスト名の部分を含むか指定します。実際には、このオプションはページキャッシュがホスト名に依存するかどうかを伝えます (デフォルト: `true`)。

`view_cache`
------------

*sfContext アクセサ*: なし (`view_cache_manager` ファクトリによって直接使われる)

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
>[`cache`](#chapter_04_sub_cache) 設定が `true` にセットされている場合のみこのファクトリが定義されます。

`view_cache` ファクトリは `sfCache` を継承するキャッシュクラスを定義します (詳しい情報はキャッシュのセクションを参照)。

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
>[`i18n`](#chapter_04_sub_i18n) 設定が `true` にセットされている場合のみこのファクトリが定義されます。

### ~`source`~

`source` オプションは翻訳コンテナの種類を定義します。

*組み込みのコンテナ*: `XLIFF`、`SQLite`、`MySQL` と `gettext`

### ~`debug`~

`debug` オプションはデバッグモードをセットします。`true` にセットされている場合、未翻訳のメッセージは接頭辞と接尾辞によってデコレートされます (下記を参照)。

### ~`untranslated_prefix`~

`untranslated_prefix` は未翻訳のメッセージに使われる接頭辞を定義します。

### ~`untranslated_suffix`~

`untranslated_suffix` は未翻訳のメッセージに使われる接尾辞を定義します。

### ~`cache`~

`cache` オプションは国際化対応データのキャッシュに使われる匿名キャッシュファクトリを定義します (詳しい情報はキャッシュのセクションを参照)。

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

`variable_prefixes` オプションはルートパターンのなかで変数名を始める文字のリストを定義します。

### ~`segment_separators`~

*デフォルト*: `/` と `.`

`segment_separators` オプションはルートの区切り文字のリストを定義します。特定のルート以外、ルーティング全体でこのオプションをオーバーライドすることはほとんどないでしょう。

### ~`generate_shortest_url`~

*デフォルト*: 新しいプロジェクトでは `true`、アップグレードしたプロジェクトでは `false`

`true` にセットされている場合、`generate_shortest_url` オプションは実現可能な最短ルートをルーティングシステムに生成するよう指示します。symfony 1.0 と 1.1 と後方互換性のあるルートがほしければ、`false` にセットします。

### ~`extra_parameters_as_query_string`~

*デフォルト*: 新しいプロジェクトでは `true`、アップグレードしたプロジェクトでは `false`

ルート生成に使われていないパラメータがあるとき、`extra_parameters_as_query_string` はルート生成に使われていないパラメータをクエリ文字列に変換することができます。symfony 1.0 もしくは 1.1 のふるまいに戻すのであれば `false` にセットします。これらのバージョンでは、ルート生成に使われていないパラメータはルーティングシステムによって無視されるだけでした。

### ~`cache`~

*デフォルト*: なし

`cache` オプションはルーティングコンフィギュレーションとデータのキャッシュに使われる匿名キャッシュファクトリを定義します (詳しい情報はキャッシュのセクションを参照)。

### ~`suffix`~

*デフォルト*: なし

すべてのルートに使われるデフォルトの接尾辞です。このオプションは非推奨でもはや役に立ちません。

### ~`load_configuration`~

*デフォルト*: `true`

`load_configuration` オプションは `routing.yml` ファイルが自動的にロードされ解析される必要があるかどうかを定義します。symfony プロジェクト外部の symfony ルーティングシステムを使いたい場合 `false` にセットします。

### ~`lazy_routes_deserialize`~

*デフォルト*: `false`

`true` にセットされている場合、`lazy_routes_deserialize` 設定はルーティングキャッシュの遅延デシリアライゼーションを有効にします。たくさんのルートをかかえておりマッチするルートが最初のほうにある場合、この設定はアプリケーションのパフォーマンスを改善できます。特定の状況ではパフォーマンスにわるい影響を与える可能性があるので、運用サーバーにデプロイする前にこの設定をテストすることを強くおすすめします。

### ~`lookup_cache_dedicated_keys`~

*デフォルト*: `false`

`lookup_cache_dedicated_keys` 設定はルーティングキャッシュが構築される方法を決定します。`false` にセットされている場合、キャッシュは1つの大きな値として保存されます; `true` にセットされている場合、それぞれのルートに独自のキャッシュストアが用意されます。この設定はパフォーマンスを最適化します。

経験則によれば、ファイルベースのキャッシュクラス (たとえば `sfFileCache`) を使う際にはこの設定を `false` に、メモリベースのキャッシュクラス (たとえば `sfAPCCache`) を使う際には `true` にするとよいです。

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

`sfAggregateLogger` を使いたくなければ、`loggers` パラメータに `null` を指定することをお忘れなく。

>**CAUTION**
>このファクトリはつねに定義されていますが、`logging_enabled` 設定が `true` にセットされている場合のみロギングが行われます。

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

キャッシュオブジェクトがコンフィギュレーションで定義されていれば、いくつかのファクトリ (`view_cache`、`i18n` と `routing`) はこのファクトリを利用できます。キャッシュオブジェクトのコンフィギュレーションはすべてのファクトリと似ています。`cache` キーは匿名キャッシュファクトリを定義します。ほかのファクトリと同じように、このファクトリは `class` と `param` エントリをとります。`param` エントリは任意のキャッシュクラスで利用可能な任意のオプションをとります。

もっとも重要なのは `prefix` オプションで、異なる環境/アプリケーション/プロジェクトのあいだでキャッシュを共有するもしくは分離できるようにします。

*組み込みのキャッシュクラス*: 
`sfAPCCache`、`sfEAcceleratorCache`、`sfFileCache`、`sfMemcacheCache`、
`sfNoCache`、`sfSQLiteCache` と `sfXCachCache`
