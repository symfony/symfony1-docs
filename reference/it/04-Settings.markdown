Il file di configurazione settings.yml
======================================

Molti aspetti di symfony possono essere configurati sia tramite file di 
configurazione scritti in YAML sia in semplice PHP. In questa sezione viene 
descritto il principale file di configurazione per un'applicazione: `settings.yml`.

Il file di configurazione `settings.yml` per un'applicazione si trova nella 
cartella `apps/NOME_APP/config/`.

Come anticipato nell'introduzione il file `settings.yml` è 
[consapevole dell'ambiente](#chapter_03_consapevolezza_dell_ambiente) 
e beneficia del [**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata).

Ogni sezione dedicata ad un ambiente ha due sotto sezioni: `.actions` e `.settings`.
Tutte le impostazioni vanno inserite nella sotto sezione `.settings`, eccezion 
fatta per le azioni predefinite da visualizzare per alcune pagine comuni.

>**NOTE**
>Il file di configurazione `settings.yml` viene memorizzato in cache come file
PHP; questo processo è gestito in modo automatico dalla 
[classe](#chapter_14_config_handlers_yml)
~`sfDefineEnvironmentConfigHandler`~.

<div class="pagebreak"></div>

Impostazioni
------------

  * `.actions`

    * [`error_404`](#chapter_04_sub_error_404)
    * [`login`](#chapter_04_sub_error_404)
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

La sotto sezione `.actions`
---------------------------

*Configurazione predefinita*:

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

La sotto sezione `.actions` definisce che azione eseguire quando è necessario
visualizzare pagine comuni. Ogni definizione ha due componenti: la prima per il 
modulo (con suffisso `_module`) e la seconda per l'azione (con suffisso `_action`).

### ~`error_404`~

L'azione `error_404` viene eseguita quando deve essere visualizzata una pagina 404
(pagina non trovata).

### ~`login`~

L'azione `login` viene eseguita quando un utente non autenticato cerca di accedere
ad una pagina protetta.

### ~`secure`~

L'azione `secure` viene eseguita quando un utente non ha le credenziali richieste.

### ~`module_disabled`~

L'azione `module_disabled` viene eseguita quando un utente invia una richiesta
ad un modulo disabilitato.

La sotto sezione `.settings`
----------------------------

La sotto sezione `.settings` è dove si specifica la configurazione del framework.
I paragrafi seguenti descrivono tutte le possibili impostazioni ordinate per
importanza.

Tutte le impostazioni definite nella sezione `.settings` sono disponibili in qualsiasi
punto del codice, utilizzando l'oggetto `sfConfig` ed anteponendo al nome dell'impostazione
il prefisso `sf_`. Per esempio, per ottenere il valore dell'impostazione `charset`
utilizzare:

    [php]
    sfConfig::get('sf_charset');

### ~`escaping_strategy`~

*Predefinito*: `true`

L'impostazione `escaping_strategy` è un valore Booleano che determina se il 
sub-framework per l'escape dell'output è abilitato o meno. Quando è abilitato
tutte le variabili disponibili nei template vengono automaticamente sottoposte
all'escape chiamando la funzione di supporto definita nell'impostazione 
`escaping_method` (vedere sotto).

Notare che `escaping_method` è l'helper predefinito usato da symfony, tuttavia
può essere rimpiazzato di volta in volta (un esempio è l'output di una variabile
in uno script Javascript).

Il sub-framework che si occupa dell'escape dell'output utilizza l'impostazione
`charset` per il suo compito.

È vivamente consigliato di lasciare il valore predefinito a `true`.

>**TIP**
>Queste impostazioni possono essere dichiarate quando si crea un'applicazione 
>con il task `generate:app` utilizzando l'opzione `--escaping-strategy`.

### ~`escaping_method`~

*Predefinito*: `ESC_SPECIALCHARS`

L'impostazione `escaping_method` definisce la funzione predefinita da utilizzare
per l'escape delle variabili nei template (vedere `escaping_strategy` precedente).

È possibile selezionare uno dei valori proposti: ~`ESC_SPECIALCHARS`~, ~`ESC_RAW`~,
~`ESC_ENTITIES`~, ~`ESC_JS`~, ~`ESC_JS_NO_ENTITIES`~, e
~`ESC_SPECIALCHARS`~ o creare una nuova funzione.

Per la maggior parte dei casi il valore predefinito è la scelta migliore. Può essere
utilizzato anche l'helper `ESC_ENTITIES` specialmente se si lavora solamente con
l'inglese o lingue europee.

### ~`csrf_secret`~

*Predefinito*: una chiave segreta generata casualmente

L'impostazione `csrf_secret` specifica una chiave univoca di sicurezza per 
l'applicazione. Se non è impostata a `false`, attiva la protezione CSRF per 
tutti i form generati dal framework dei form. Questa impostazione è utilizzata anche 
dall'helper `link_to()` quando c'è la necessità di convertire un link ad un form
(per simulare un metodo HTTP `DELETE` per esempio).

È vivamente consigliato di cambiare il valore predefinito con una chiave segreta
univoca di vostra scelta.

>**TIP**
>Queste impostazioni possono essere dichiarate quando si crea un'applicazione 
>con il task `generate:app` utilizzando l'opzione `--csrf-secret`.

### ~`charset`~

*Predefinito*: `utf-8`

L'impostazione `charset` specifica il set di caratteri che verrà utilizzato in ogni 
situazione dal framework: dall'header di risposta `Content-Type` alla funzionalità
di escape dell'output.

Il più delle volte il valore predefinito va bene.

>**WARNING**
>Questa impostazione è usata in diversi posti nel framework,
>quindi il suo valore è messo in cache in molti posti. Dopo
>averlo cambiato, occorre pulire la cache della configurazione,
>anche nell'ambiente di sviluppo.

### ~`enabled_modules`~

*Predefinito*: `[default]`

L'impostazione `enabled_modules` è un array dei nomi dei moduli da abilitare
per la specifica applicazione. Moduli definiti in plugin o nel core di symfony 
non sono abilitati in modo predefinito e devono quindi essere elencati in questa 
impostazione per essere accessibili.

Aggiungere un modulo è solamente questione di aggiungerlo alla lista (l'ordine
con cui vengono inseriti non importa):

    [yml]
    enabled_modules: [default, sfGuardAuth]

Il modulo `default` definito nel framework contiene tutte le azioni predefinite 
impostate nella sotto sezione `.actions` del file `settings.yml`. Raccomandiamo 
di personalizzare tutte le impostazioni e quindi rimuovere il modulo `default`
da questa configurazione.

### ~`default_timezone`~

*Predefinito*: none

L'impostazione `default_timezone` definisce il fuso orario predefinito usato
da PHP. Può essere un qualsiasi [fuso orario](http://www.php.net/manual/en/class.datetimezone.php)
riconosciuto da PHP.

>**NOTE**
>Se non viene definito nessun fuso orario, verrà chiesto di definirne uno nel file
>`php.ini`. Altrimenti, symfony cercherà di indovinare il migliore fuso orario
>applicabile utilizzando la funzione PHP [`date_default_timezone_get()`](http://www.php.net/date_default_timezone_get).

### ~`cache`~

*Predefinito*: `false`

L'impostazione `cache` abilita o meno la cache dei template.

>**TIP**
>La configurazione generale del sistema della cache viene specificata nelle sezioni 
>[`view_cache_manager`](#chapter_05_view_cache_manager) e 
>[`view_cache`](#chapter_05_view_cache) del file `factories.yml`.
>La configurazione dettagliata viene fatta nel file di configurazione 
>[`cache.yml`](#chapter_09).

### ~`etag`~

*Predefinito*: `true` ad esclusione degli ambienti `dev` e `test`

L'impostazione `etag` abilita e disabilita la generazione automatica degli header 
HTTP `ETag`. L'ETag generato da symfony è un semplice md5 del contenuto della 
risposta.

### ~`i18n`~

*Predefinito*: `false`

L'impostazione `i18n` è un valore Booleano che abilita e disabilita il 
sub-framework i18n. Se l'applicazione è internazionalizzata impostarlo a `true`.

>**TIP**
>La configurazione generale del sistema i18n va indicata nella sezione 
>[`i18n`](05-Factories#chapter_05_i18n) del file di configurazione `factories.yml`.

### ~`default_culture`~

*Predefinito*: `en`

L'impostazione `default_culture` definisce la nazionalità predefinita utilizzata
dal sub-framework i18n. Può essere una qualsiasi delle nazionalità valide.

### ~`standard_helpers`~

*Predefinito*: `[Partial, Cache]`

L'impostazione `standard_helpers` è un array di gruppi di helper da caricare
per tutti i template (nome del gruppo di helper senza il suffisso `Helper`).

### ~`no_script_name`~

*Predefinito*: `true` per l'ambiente `prod` della prima applicazione creata,
`false` per tutte le altre

L'impostazione `no_script_name` determina il fatto che il nome del front controller
venga inserito o meno negli URL generati. In modalità predefinita è impostato a `true` dal 
task `generate:app` per l'ambiente `prod` della prima applicazione creata.

Ovviamente solo un'applicazione e ambiente può avere questa impostazione impostata
a `true` se tutti i front controller sono nella stessa cartella (`web/`). Se fosse
necessario avere più di un'applicazione con `no_script_name` impostato a  `true`,
è necessario spostare i front controller corrispondenti in una sotto cartella
della cartella web principale.

### ~`lazy_cache_key`~

*Predefinito*: `true` per i nuovi progetti, `false` per progetti aggiornati

Quando abilitata l'impostazione `lazy_cache_key` ritarda la creazione di una chiave
cache fino a che non verrà verificato se l'azione o il partial può essere inserito
in cache o meno.

### ~`file_link_format`~

*Predefinito*: nessuno

Nel messaggio di debug, i percorsi dei file sono link cliccabili se
è impostato il valore di configurazione PHP di `sf_file_link_format` o di
`xdebug.file_link_format`.

Per esempio, se si vogliono aprire file in TextMate, si può utilizzare il seguente
valore:

    [yml]
    txmt://open?url=file://%f&line=%l

Il segnaposto `%f` sarà sostituito con il percorso assoluto del file e il
segnaposto `%l` sarà sostituito con il numero di linea.

### ~`logging_enabled`~

*Predefinito*: `true` per tutti gli ambienti escluso `prod`

L'impostazione `logging_enabled` abilita il sub-framework dei log. Impostandola 
a `false`, si aggira completamente il meccanismo di log e si ottiene un piccolo
guadagno in termini di prestazioni.

>**TIP**
>La configurazione fine del sistema dei log deve essere fatta nel file di 
>configurazione `factories.yml`.

### ~`web_debug`~

*Predefinito*: `false` per tutti gli ambienti escluso `dev`

L'impostazione `web_debug` abilita la web debug toolbar. La web debug toolbar 
viene inserita in una pagina quando il content type della risposta è HTML.

### ~`error_reporting`~

*Predefinito*:

  * `prod`:  E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR
  * `dev`:   E_ALL | E_STRICT
  * `test`:  (E_ALL | E_STRICT) ^ E_NOTICE
  * default: E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR

L'impostazione `error_reporting` controlla il livello dell'error reporting di 
PHP (da visualizzare nel browser o da scrivere nei log).

>**TIP**
>Sul sito ufficiale di PHP si possono trovare alcune informazioni su come
>utilizzare gli [operatori logici](http://www.php.net/language.operators.bitwise).

La configurazione predefinita è la più sensibile e non dovrebbe essere modificata.

>**NOTE**
>La visualizzazione degli errori nel browser è disabilitata automaticamente per 
>i front controller che hanno disabilitato il `debug`, questo è il caso predefinito
>per l'ambiente `prod`.

### ~`compressed`~

*Predefinito*: `false`

L'impostazione `compressed` abilita la compressione nativa di PHP delle risposte.
Se impostato a `true` symfony utilizzerà [`ob_gzhandler`](http://www.php.net/ob_gzhandler)
come funzione di callback per `ob_start()`.

È raccomandabile tenere questo valore a `false` ed utilizzare invece il meccanismo 
di compressione nativo del web server utilizzato.

### ~`use_database`~

*Predefinito*: `true`

L'impostazione `use_database` determina se l'applicazione utilizza o meno un 
database.

### ~`check_lock`~

*Predefinito*: `false`

L'impostazione `check_lock` abilita e disabilita il sistema di lock di un'applicazione
azionato da alcuni task come `cache:clear` e `project:disable`.

Se impostato a `true` tutte le richieste verso applicazioni disabilitate verranno
automaticamente redirette alla pagina `lib/exception/data/unavailable.php`
messa a disposizione dal core di symfony.

>**TIP**
>È possibile sovrascrivere il template predefinito aggiungendo un file 
>`config/unavailable.php` al progetto o all'applicazione.


### ~`web_debug_web_dir`~

*Predefinito*: `/sf/sf_web_debug`

L'impostazione `web_debug_web_dir` definisce il percorso per i file della 
web debug toolbar (immagini, fogli di stile e file JavaScript).

