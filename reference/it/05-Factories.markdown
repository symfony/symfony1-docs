Il file di configurazione factories.yml
=======================================

I factory sono oggetti del core necessari al framework durante la vita di ogni richiesta.
Sono inizializzati nel file di configurazione `factories.yml` e sempre accessibili tramite l'oggetto
`sfContext`:

    [php]
    // restituisce il factory per l'oggetto User
    sfContext::getInstance()->getUser();

Per una applicazione il file di configurazione `factories.yml` può essere trovato nella cartella 
`apps/NOME_APP/config/`.

Come abbiamo detto durante l'introduzione, il file `factories.yml` è
[**consapevole dell'ambiente**](#chapter_03_consapevolezza_dell_ambiente), beneficia del 
[**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata),
e può includere [**costanti**](#chapter_03_costanti).

Il file di configurazione `factories.yml` contiene un elenco di dichiarazioni di factory:

    [yml]
    FACTORY_1:
      # definizione del factory 1

    FACTORY_2:
      # definizione del factory 2

    # ...

I nomi dei factory supportati sono: `controller`, `logger`, `i18n`, `request`,
`response`, `routing`, `storage`, `user`, `view_cache`, e
`view_cache_manager`.

Quando `sfContext` inizializza i factory, legge dal file `factories.yml`
i nomi delle classi dei factory (`class`) e i relativi parametri (`param`)
per configurare i corrispettivi oggetti:

    [yml]
    NOME_DEL_FACTORY:
      class: NOME_DELLA_CLASSE
      param: { ARRAY DI PARAMETRI }

La possibilità di modificare i factory significa che è possibile usare una classe
personalizzata per istanziare un oggetto del core di symfony piuttosto che quella 
predefinita. È inoltre possibile cambiare anche il comportamento di queste classi 
modificando i parametri inviati alle stesse.

Se la classe di un factory non può essere caricata automaticamente, deve essere definito 
un parametro `file` che sarà utilizzato per indicare il percorso della classe che verrà
automaticamente usato prima che il factory sia creato:

    [yml]
    FACTORY_NAME:
      class: NOME_DELLA_CLASSE
      file:  PERCORSO_ASSOLUTO_DEL_FILE

>**NOTE**
>Il file di configurazione `factories.yml` viene salvato in cache come file PHP; Il processo
>è automaticamente gestito dalla [classe](#chapter_14_config_handlers_yml) 
> ~`sfFactoryConfigHandler`~.

<div class="pagebreak"></div>

Factory
---------

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

   * [`cache_key_use_vary_headers`](chapter_05_sub_cache_key_use_vary_headers)
   * [`cache_key_use_host_name`](chapter_05_sub_cache_key_use_host_name)
   
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

*sfContext Accessor*: `$context->getMailer()`

*Configurazione predefinita*:

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

*Configurazione predefinita per l'ambiente `test`*:

    [yml]
    mailer:
      param:
        delivery_strategy: none

*Configurazione predefinita per l'ambiente `dev`*:

    [yml]
    mailer:
      param:
        delivery_strategy: none

### ~`charset`~

L'opzione `charset` definisce l'insieme di caratteri da usare per i mesaggi di mail. Per
impostazione predefinita, usa l'impostazione `charset` da `settings.yml`.

### ~`delivery_strategy`~

L'opzione `delivery_strategy` definisce come i messaggi email vengono consegnati dal
mailer. Per impostazione predefinita sono disponibili quattro strategie, che dovrebbero soddisfare tutte le
esigenze più comuni:

 * `realtime`:       I messaggi vengono inviati in tempo reale.

 * `single_address`: I messaggi vengono inviati a un singolo indirizzo.

 * `spool`:          I messaggi vengono memorizzati in una coda.

 * `none`:           I messaggi vengono semplicemente ignorati.

### ~`delivery_address`~

L'opzione `delivery_address` definisce il destinatario di tutti i messaggi quando
`delivery_strategy` è imposatto a `single_address`.

### ~`spool_class`~

L'opzione `spool_class` definisce la classe di spool da usare quando
`delivery_strategy` è impostato a `spool`:

  * ~`Swift_FileSpool`~: I messaggi sono memorizzati sul filesystem.

  * ~`Swift_DoctrineSpool`~: I messaggi sono memorizzati in un modello di Doctrine.

  * ~`Swift_PropelSpool`~: I messaggi sono memorizzati in un modello di Propel.

>**NOTE**
>Quando lo spool è istanziato, l'opzione ~`spool_arguments`~ è usata come
>argomento del costruttore.

### ~`spool_arguments`~

L'opzione `spool_arguments` definisce gli argomenti del costruttore dello spool.
Queste sono le opzioni disponibili per le classi built-in delle code:

 * `Swift_FileSpool`:

    * Il percorso assoluto della cartella delle code (i messaggi vengono memorizzati in
      questa cartella)

 * `Swift_DoctrineSpool`:

    * Il modello di Doctrine da usare per memorizzare i messaggi (Predefinito `MailMessage`)

    * Il nome della colonna da usare per memorizzare il messaggio (Predefinito `message`)

    * Il metodo da chiamare per recuperare i messaggi da inviare (facoltativo).

 * `Swift_PropelSpool`:

    * Il modello di Propel da usare per memorizzare i messaggi (Predefinito `MailMessage`)

    * Il nome della colonna da usare per memorizzare il messaggio (Predefinito `message`)

    * Il metodo da chiamare per recuperare i messaggi da inviare (facoltativo). Esso
      riceve il Criteria corrente come argomento.

La configurazione sottostante è una configurazione tipica per uno spool di Doctrine:

    [yml]
    # configurazione in factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

### ~`transport`~

L'opzione `transport` definisce il mezzo da usare per inviare effettivamente i
messaggi email.

L'impostazione `class` può essere qualunque classe che implementa `Swift_Transport`,
e ne sono fornite tre predefinite:

  * ~`Swift_SmtpTransport`~: Usa un server SMTP per inviare i messaggi.

  * ~`Swift_SendmailTransport`~: Usa `sendmail` per inviare i messaggi.

  * ~`Swift_MailTransport`~: Usa la funzione PHP nativa `mail()` per inviare
    i messaggi.

È possibile configurare ulteriormente il mezzo con cui viene inviata la mail
impostando `param`. La sezione
["Transport Types"](http://swiftmailer.org/docs/transport-types) della
documentazione ufficiale di Swift Mailer descrive tutto quello che dovete sapere
sulle classi di trasporto built-in e i loro differenti parametri.

`request`
---------

*sfContext Accessor*: `$context->getRequest()`

*Configurazione standard*:

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

L'opzione `path_info_array` definisce l'array PHP globale che sarà usato per recuperare informazioni. 
In alcune configurazioni il valore predefinito potrebbe essere cambiato da `SERVER` ad `ENV`.

### ~`path_info_key`~

L'opzione `path_info_key` definisce la chiave sotto la quale l'informazione relativa a `PATH_INFO`
può essere trovata.

Se è usato ~IIS~ con un modulo di riscrittura delle URL come `IIFR` o `ISAPI`, è necessario impostare questo parametro
a `HTTP_X_REWRITE_URL`.

### ~`formats`~

L'opzione `formats` definisce un array di estensioni di file e il corrispettivo
`Content-Type`. È usata automaticamente dal framework per gestire il `Content-Type` di una risposta, 
in base all'estensione dell'URI richiesta.

### ~`relative_url_root`~

L'opzione `relative_url_root` definisce la parte di URL che precede il front
controller. Normalmente, è automaticamente gestita dal framework e non necessita
cambiamenti.

`response`
----------

*sfContext Accessor*: `$context->getResponse()`

*Configurazione standard*:

    [yml]
    response:
      class: sfWebResponse
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        send_http_headers: true

*Configurazione standard per l'ambiente di `test`*:

    [yml]
    response:
      class: sfWebResponse
      param:
        send_http_headers: false

### ~`send_http_headers`~

L'opzione `send_http_headers` specifica quando deve essere inviato un
header di risposta insieme al contenuto della risposta. Questa opzione è particolarmente 
comoda per fare test, in quanto gli header sono inviati tramite la funzione PHP 
`header()`, che invia un warning se si sta provando ad inviare header dopo qualche tipo
di output.

### ~`charset`~

L'opzione `charset` definisce il charset da utilizzare nella risposta. Il valore predefinito,
preso dal parametro `charset` nel file `settings.yml`, è quello che serve la maggior parte
delle volte.

### ~`http_protocol`~

L'opzione `http_protocol` definisce la versione del protocollo HTTP da utilizzare 
per la risposta. Come valore predefinito utilizza `$_SERVER['SERVER_PROTOCOL']` 
altrimenti usa `HTTP/1.0`.

`user`
------

*sfContext Accessor*: `$context->getUser()`

*Configurazione standard*:

    [yml]
    user:
      class: myUser
      param:
        timeout:         1800
        logging:         %SF_LOGGING_ENABLED%
        use_flash:       true
        default_culture: %SF_DEFAULT_CULTURE%

>**NOTE**
>La classe `myUser` eredita da `sfBasicSecurityUser`,
>che può essere configurata nel file di configurazione
>[`security.yml`](#chapter_08).

### ~`timeout`~

L'opzione `timeout` definisce il timeout per l'autenticazione utente.
Non è correlata al timeout della sessione. Il valore predefinito rimuove l'autenticazione
ad un utente dopo 30 minuti di inattività.

Questa impostazione è usata solo dalle classi user che ereditano dalla classe base 
`sfBasicSecurityUser`, come nel caso della classe generata dal sistema `myUser`.

>**NOTE**
>Per evitare comportamenti inaspettati, la classe user forza automaticamente la massima durata
>per il garbage collector delle sessioni (`session.gc_maxlifetime`) in modo che 
>sia maggiore, o uguale, al timeout.

### ~`use_flash`~

L'opzione `use_flash` abilita o disabilita il componente flash.

### ~`default_culture`~

L'opzione `default_culture` definisce la direttiva di traduzione da usare per 
l'utente che entra nel sito per la prima volta. Se non dichiarato, utilizza il valore 
`default_culture` impostato nel file `settings.yml`.

>**CAUTION**
>Se l'impostazione ~`default_culture`~ viene cambiata in `factories.yml` o
>in `settings.yml`, è necessario eliminare i cookie del browser per vedere le modifiche.

`storage`
---------

Il factory storage è usato dal factory user per salvare i dati dell'utente tra
una richiesta HTTP e l'altra.

*sfContext Accessor*: `$context->getStorage()`

*Configurazione standard*:

    [yml]
    storage:
      class: sfSessionStorage
      param:
        session_name: symfony

*Configurazione standard per l'ambiente di `test`*:

    [yml]
    storage:
      class: sfSessionTestStorage
      param:
        session_path: %SF_TEST_CACHE_DIR%/sessions

### ~`auto_start`~

L'opzione `auto_start` abilita o disabilita la partenza automatica della sessione di PHP 
(usando la funzione `session_start()` del linguaggio).

### ~`session_name`~

L'opzione `session_name` definisce il nome del cookie usato da symfony per
memorizzare la sessione utente. Per impostazione predefinita, il nome è `symfony`, il che significa che
tutte le applicazioni condividono lo stesso cookie (e quindi anche le corrispondenti
autenticazioni e autorizzazioni).

### `session_set_cookie_params()` parameters

Il factory `storage`  chiama la funzione
[`session_set_cookie_params()`](http://www.php.net/session_set_cookie_params)
con il valore delle seguenti opzioni:

 * ~`session_cookie_lifetime`~: Durata del cookie di sessione, definita in
                                secondi.
 * ~`session_cookie_path`~:   Percorso sul dominio dove il cookie andrà a lavorare.
                              Usare una barra singola (`/`) per tutti i percorsi sul
                              dominio.
 * ~`session_cookie_domain`~: Dominio del cookie, per esempio `www.php.net`. Per
                              rendere visibili i cookie su tutti i sotto domini,
                              il dominio deve essere preceduto da un punto, come `.php.net`.
 * ~`session_cookie_secure`~: Se `true`, il cookie sarà inviato solo su connessioni
                              sicure.
 * ~`session_cookie_httponly`~: Se è impostato a `true`, PHP tenterà di inviare il
                                flag `httponly` quando imposta il cookie di sessione.

>**NOTE**
>La descrizione di ciascuna opzione proviene dalla descrizione della funzione 
>`session_set_cookie_params()` presente sul sito web del PHP

### ~`session_cache_limiter`~

Se l'opzione `session_cache_limiter` è assegnata, la funzione PHP
[`session_cache_limiter()`](http://www.php.net/session_cache_limiter)
è chiamata e il valore dell'opzione è passato come parametro.

### Database Storage-specific Options

Quando si utilizza uno storage che eredita dalla classe `sfDatabaseSessionStorage`,
sono disponibili molte altre opzioni:

 * ~`database`~:     Il nome del database (necessario)
 * ~`db_table`~:     Il nome della tabella (necessario)
 * ~`db_id_col`~:    Il nome della colonna della chiave primaria (`sess_id` per impostazione predefinita)
 * ~`db_data_col`~:  Il nome della colonna con i dati (`sess_data` per impostazione predefinita)
 * ~`db_time_col`~:  Il nome della colonna con il tempo (`sess_time` per impostazione predefinita)

`view_cache_manager`
--------------------

*sfContext Accessor*: `$context->getViewCacheManager()`

*Configurazione standard*:

    [yml]
    view_cache_manager:
      class: sfViewCacheManager
      param:
        cache_key_use_vary_headers: true
        cache_key_use_host_name:    true

>**CAUTION**
>Questo factory è creato solo se l'impostazione [`cache`](#chapter_04_sub_cache)
>è impostata su `true`.

La maggior parte della configurazione di questo factory è fatta tramite il factory `view_cache`, che
definisce l'oggetto cache sottostante usato dal gestore cache della vista.

### ~`cache_key_use_vary_headers`~

L'opzione `cache_key_use_vary_headers` specifica se le chiavi della cache devono
includere le parti variabili degli header. In pratica, dice se la cache della pagina deve
essere dipendente dall'header HTTP, come specificato nel parametro della cache `vary` (valore
predefinito: `true`).

### ~`cache_key_use_host_name`~

L'opzione `cache_key_use_host_name` specifica se le chiavi della cache devono
includere la parte del nome host. In pratica, dice se la cache della pagina deve essere
dipendente dal nome dell'host (valore predefinito: `true`).

`view_cache`
------------

*sfContext Accessor*: none (usato direttamente dal factory `view_cache_manager`)

*Configurazione standard*:

    [yml]
    view_cache:
      class: sfFileCache
      param:
        automatic_cleaning_factor: 0
        cache_dir:                 %SF_TEMPLATE_CACHE_DIR%
        lifetime:                  86400
        prefix:                    %SF_APP_DIR%/template

>**CAUTION**
>Questo factory è definito solo se l'impostazione [`cache`](#chapter_04_sub_cache)
>è impostata a `true`.

Il factory `view_cache` definisce una classe cache che deve ereditare da
`sfCache` (vedere la sezione Cache per maggiori informazioni).

`i18n`
------

*sfContext Accessor*: `$context->getI18N()`

*Configurazione standard*:

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
>Questo factory è definito solo se l'impostazione [`i18n`](#chapter_04_sub_i18n)
>è impostata a `true`.

### ~`source`~

L'opzione `source` definisce il tipo di contenitore per le traduzioni.

*Contenitori già disponibili*: `XLIFF`, `SQLite`, `MySQL` e `gettext`.

### ~`debug`~

L'opzione `debug` imposta la modalità debug. Se impostato a `true`, i messaggi
non tradotti sono decorati con un prefisso e un suffisso (vedere sotto).

### ~`untranslated_prefix`~

`untranslated_prefix` definisce un prefisso da usare per i messaggi non tradotti.

### ~`untranslated_suffix`~

`untranslated_suffix` definisce un suffisso da usare per i messaggi non tradotti.

### ~`cache`~

L'opzione `cache` definisce un factory cache anonimo da usare per mettere
in cache i dati i18n (vedere la sezione Cache per maggiori informazioni).

`routing`
---------

*sfContext Accessor*: `$context->getRouting()`

*Configurazione standard*:

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

*Predefinito*: `:`

L'opzione `variable_prefixes` definisce l'elenco dei caratteri che iniziano un
nome variabile in uno schema di rotta.

### ~`segment_separators`~

*Predefinito*: `/` e `.`

L'opzione `segment_separators` definisce l'elenco dei separatori delle parti di rotta.
La maggior parte delle volte, non si vuole sovrascrivere questa opzione per tutte le
rotte, ma per alcune specifiche rotte.

### ~`generate_shortest_url`~

*Predefinito*: `true` per i nuovi progetti, `false` per i progetti aggiornati

Se impostata `true`, l'opzione `generate_shortest_url` chiederà al sistema
per le rotte di generare la rotta più corta possibile. Impostare a `false` se si vuole
che le rotte siano compatibili all'indietro con symfony 1.0 e 1.1.

### ~`extra_parameters_as_query_string`~

*Predefinito*: `true` per i nuovi progetti, `false` per i progetti aggiornati

Se alcuni parametri non sono utilizzati per la generazione di una rotta,
`extra_parameters_as_query_string` permette ai parametri aggiuntivi di essere
convertiti in una query string. Settare a `false` per tornare al comportamento di
symfony 1.0 o 1.1. In queste versioni, i parametri extra erano semplicemente ignorati
dal sistema delle rotte.

### ~`cache`~

*Predefinito*: nessuno

L'opzione `cache` definisce un factory cache anonimo da usare per mettere in cache
la configurazione delle rotte e i dati (vedere la sezione Cache per maggiori informazioni).

### ~`suffix`~

*Predefinito*: nessuno

Il suffisso predefinito da utilizzare per tutte le rotte. Questa opzione è deprecata e non è
più di nessuna utilità.

### ~`load_configuration`~

*Predefinito*: `true`

L'opzione `load_configuration` definisce se i file `routing.yml` devono
essere automaticamente caricati ed elaborati. Impostare a `false` se si vuole usare il
sistema delle rotte di symfony fuori da un progetto symfony.

### ~`lazy_routes_deserialize`~

*Predefinito*: `false`

Se impostata a `true`, l'impostazione `lazy_routes_deserialize` abilita la
de-serializzazione "lazy" della cache delle rotte. Questa opzione può migliorare le
prestazioni delle applicazioni se si ha un elevato numero di rotte e se la maggior parte
delle corrispondenze con le rotte sono collocate nelle prime posizioni. Si consiglia vivamente
di verificare l'impostazione prima di andare in produzione, perché in certe circostanze
può fare degradare le prestazioni.


### ~`lookup_cache_dedicated_keys`~

*Predefinito*: `false`

L'impostazione `lookup_cache_dedicated_keys` determina come la cache del routing è
costruita. Quando impostata a `false`, la cache è memorizzata come un solo grande valore; quando
è impostata a `true`, ciascuna rotta ha la sua memorizzazione nella cache. Questa impostazione è
per ottimizzare le performance.

Come regola generale, l'impostazione a `false` è migliore quando si usa una classe cache
basata su file (per esempio `sfFileCache`), l'impostazione a `true` è migliore
quando si usa una classe cache basata sulla memoria (per esempio `sfAPCCache`).


`logger`
--------

*sfContext Accessor*: `$context->getLogger()`

*Configurazione standard*:

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

*Configurazione standard per l'ambiente `prod`*:

    [yml]
    logger:
      class:   sfNoLogger
      param:
        level:   err
        loggers: ~

Se non si usa `sfAggregateLogger`, non dimenticare di specificare un valore
`null` per il parametro `loggers`.

>**CAUTION**
>Questo factory è sempre definito, ma il logging si verifica soltanto se
>l'impostazione `logging_enabled` è impostata a `true`.

### ~`level`~

L'opzione `level` definisce il livello del logger.

*Possibili valori*: `EMERG`, `ALERT`, `CRIT`, `ERR`, `WARNING`, `NOTICE`,
`INFO`, or `DEBUG`.

### ~`loggers`~

L'opzione `loggers` definisce un elenco di logger da usare. L'elenco è un array di
factory logger anonimi.

*Classi già disponibili*: `sfConsoleLogger`, `sfFileLogger`, `sfNoLogger`,
`sfStreamLogger` e `sfVarLogger`.

`controller`
------------

*sfContext Accessor*: `$context->getController()`

*Configurazione standard*:

    [yml]
    controller:
      class: sfFrontWebController

Factory cache anonimi
---------------------

Alcuni factory (`view_cache`, `i18n` e `routing`) possono trarre vantaggio da
un oggetto cache se definito nella loro configurazione. La configurazione
dell'oggetto cache è simile per tutti i factory. La chiave `cache` definisce un
factory cache anonimo. Come ogni altro factory, accetta le voci `class` e
`param`. La voce `param` può accettare qualunque opzione disponibile per la data
classe cache.

L'opzione `prefix` è la più importante, dal momento che permette di condividere o
separare una cache tra differenti ambienti/applicazioni/progetti.

*Classi cache disponibili*: `sfAPCCache`, `sfEAcceleratorCache`, `sfFileCache`,
`sfMemcacheCache`, `sfNoCache`, `sfSQLiteCache`, e `sfXCacheCache`.
