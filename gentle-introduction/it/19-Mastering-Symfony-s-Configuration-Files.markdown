Capitolo 19 - Padroneggiare i file di configurazione di symfony
===============================================================

Ora che si conosce symfony molto bene, si è già in grado di scavare nel suo codice per
capire le sue impostazioni fondamentali e scoprire nuove abilità nascoste. Ma prima di
estendere le classi di symfony per soddisfare i propri requisiti, meglio dare un'occhiata
più da vicino ad alcuni file di configurazione. Molte caratteristiche sono già costruite
in symfony e possono essere attivate semplicemente modificando le impostazioni della
configurazione. Questo vuol dire che si può mettere a punto il comportamento fondamentale
di symfony senza sovrascrivere le sue classi. Questo capitolo approfondisce i file di
configurazione e le loro potenti capacità.

Impostazioni di symfony
-----------------------

Il file `frontend/config/settings.yml` contiene la configurazione principale di symfony
per l'applicazione `frontend`. Abbiamo già visto le funzionalità di molte impostazioni di
questo file nei capitoli precedenti, ma facciamone un riepilogo.

Come spiegato nel capitolo 5, questo file dipende dall'ambiente, il che vuol dire che ogni
impostazione può avere un valore diverso per ogni ambiente. Si ricordi che ogni parametro
definito in questo file è accessibile all'interno del codice PHP tramite la classe
`sfConfig`. Il nome del parametro è il nome dell'impostazione, prefissato con `sf_`. Ad
esempio, se si vuole ottenere il parametro `cache`, basta richiamare
`sfConfig::get('sf_cache')`.

### Moduli e azioni predefiniti

Symfony fornisce delle pagine predefinite per situazioni speciali. In caso di errore del
routing, symfony esegue un'azione del modulo `default`, che si trova nella cartella
`sfConfig::get('sf_symfony_lib_dir')/controller/default/`. Il file `settings.yml`
definisce quale azione eseguire, a seconda dell'errore:

  * `error_404_module` ed `error_404_action`: Azione richiamata quando l'URL inserito
    dall'utente non corrisponde ad alcuna rotta o quando capita una `sfError404Exception`.
    Il valore predefinito è `default/error404`.
  * `login_module` e `login_action`: Azione richiamata quando un utente non autenticato
    tenta di accedere a una pagina definita come `secure` in `security.yml` (si veda il
    capitolo 6 per maggiori dettagli). Il valore predefinito è `default/login`.
  * `secure_module` e `secure_action`: Azione richiamata quando un utente non ha le
    credenziali necessarie per un'azione. Il valore predefinito è `default/secure`.
  * `module_disabled_module` e `module_disabled_action`: Azione richiamata quando un
    utente richiede un modulo dichiarato come disabilitato in `module.yml`. Il valore
    predefinito è `default/disabled`.

Prima di mettere in produzione un'applicazione, si dovrebbero personalizzare queste
azioni, perché il template del modulo `default` include il logo di symfony. Si veda la
Figura 19-1 per una schermata di una di queste pagine, la pagina di errore 404.

Figura 19-1 - Pagina di errore 404 predefinita

![Pagina di errore 404 predefinita](http://www.symfony-project.org/images/book/1_4/F1901.jpg "Pagina di errore 404 predefinita")

Si possono sovrascrivere le pagine predefinite in due modi:

  * Creando un proprio modulo default nella cartella `modules/` della propria applicaizone,
    ridefinendo tutte le azioni definite in `settings.yml` (`index`, `error404`, `login`,
    `secure`, `disabled`) e i relativi template (`indexSuccess.php`, `error404Success.php`,
    `loginSuccess.php`, `secureSuccess.php`, `disabledSuccess.php`).
  * Cambiando le impostazioni per il modulo e le azioni predefinite in `settings.yml`,
    indicando le pagine della propria applicazione.

Altre due pagine hanno un aspetto che richiama symfony e quindi hanno bisogno di essere
personalizzate prima di andare in produzione. Queste pagine non sono nel modulo `default`,
perché sono richiamate quando symfony non riesce a essere eseguito correttamente. Si
possono trovare queste pagine nella cartella
`sfConfig::get('sf_symfony_lib_dir')/exception/data/`:

  * `error.html.php`: Pagina richiamata quando si verifica un errore interno del server in
    ambiente di produzione. In altri ambienti (quando debug è impostato a `true`), in caso
    di errore, symfony mostra l'intera pila di esecuzione con un messaggio di errore
    esplicito (si veda il capitolo 16 per maggiori dettagli).
  * `unavailable.php`: Pagina richiamata quando un utente richiede una pagina mentre
     l'applicazione è disabilitata (tramite il task `project:disable`). Viene anche
     richiamata quando la cache è in fase di pulizia (cioè tra la chiamata al task
     `cache:clear` e la fine dell'esecuzione del task stesso). Su un sistema con una
     cache molto grande, il processo di pulizia della cache potrebbe richiedere diversi
     secondi. Symfony non può soddisfare una richiesta con una cache pulita solo in parte,
     quindi le richieste ricevute prima della fine del processo sono redirette a questa
     pagina.

Per personalizzare queste pagine, basta creare i file `error/error.html.php` e
`unavailable.php` nella cartella `config/` della propria applicazione. Symfony userà
questi template al posto dei suoi.

>**NOTE**
>Per rimandare le richieste alla pagina `unavailable.php` quando necessario, occorre
>impostare `check_lock` a `true` in `settings.yml`. Il controllo è disattivato per
>impostazione predefinita, poiché aggiunge un piccolo overhead a ogni richiesta.

### Attivazione di caratteristiche opzionali

Alcuni parametri di `settings.yml` controllano delle caratteristiche opzionali del
framework, che possono essere abilitate o disabilitate. Disattivare le caratteristiche
inutilizzate aumenta un po' le prestazioni, quindi ci si dovrebbe assicurare di
rivedere le impostazioni elencati nella Tabella 19-1, prima di mandare in produzione
l'applicazione.

Tabella 19-1 - Caratteristiche opzionali configurabili in `settings.yml`

Parametro           | Descrizione                                    | Valore predefinito
------------------- | ---------------------------------------------- | ------------------
`use_database`      | Abilita la gestione del database. Impostare a `false` se non si usa un database. | `true`
`i18n`              | Abilita la traduzione dell'interfaccia (si veda il capitolo 13). Impostare a `true` per applicazioni multi-lingua. | `false`
`logging_enabled`   | Abilita il log degli eventi di symfony. Impostare a `false` se si vogliono disabilitare i log. | `true`
`escaping_strategy` | Abilita l'escape dell'output (si veda il capitolo 7). Impostare a `true` se si vuole l'escape dei dati passati ai template. | `true`
`cache`             | Abilita la cache dei template (vedere capitolo 12). Impostare a `true` se almeno un modulo include il file `cache.yml`. Il filtro della cache (`sfCacheFilter`) è abilitato. | `false` in sviluppo, `true` in produzione
`web_debug`         | Abilita la web debug toolbar per facilitare il debug (si veda il capitolo 16). Impostare a `true` per mostrare toolbar su ogni pagina. | `true` in sviluppo, `false` in produzione
`check_symfony_version` | Abilita la verifica della versione di symfony a ogni richiesta. Impostare a `true` per pulire la cache automaticamente dopo un aggiornamento di symfony. Lasciare a `false` se si pulisce la cache a mano dopo un aggiornamento. | `false`
`check_lock`        | Abilita il sistema di blocco dell'applicazione, attivato dai task `cache:clear` e `project:disable` (vedere la sezione precedente). Impostare a `true` per fare in modo che tutte le richieste ad applicazioni disabilitate siano rinviate alla pagina `sfConfig::get('sf_symfony_lib_dir')/exception/data/unavailable.php`. | `false`
`compressed`        | Abilita la compressione della risposta in PHP. Impostare a `true` per comprimere il codice HTML in uscita tramite il gestore di compressione di PHP. | `false`

### Configurazione delle caratteristiche

Symfony usa alcuni parametri di `settings.yml` per modificare il comportamento di
caratteristiche predefinite, come la validazione di form, la cache e moduli di terze
parti.

#### Impostazioni dell'escape dell'output

Le impostazioni di escape dell'output controllano il modo in cui il template accede alle
variabili (vedere capitolo 7). Il file `settings.yml` include due impostazioni per questa
caratteristica:

  * L'impostazione `escaping_strategy` accetta i valori `true` o `false`.
  * L'impostazione `escaping_method` accetta i valori `ESC_RAW`, `ESC_SPECIALCHARS`,
    `ESC_ENTITIES`, `ESC_JS` o `ESC_JS_NO_ENTITIES`.

#### Impostazioni del routing

Le impostazioni del routing (vedere capitolo 9) sono definite in `factories.yml`, sotto
la chiave `routing`. Il Listato 19-1 mostra la configurazione predefinita del routing.

Listato 19-1 - Impostazioni di configurazione del routing, in `frontend/config/factories.yml`

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

  * Il parametro `suffix` imposta il suffisso predefinito per gli URL generati. Il valore
    predefinito è un punto (`.`) e corrisponde a nessun suffisso. Impostare a `.html`, ad
    esempio, per far sembrare statici tutti gli URL generati.
  * Quando una regole di routing rule non definisce il parametro `module` o `action`,
    vengono usati i valori di `factories.yml`:
    * `default_module`: Parametro `module` predefinito. Il valore predefinito è `default`.
    * `default_action`: Parametro `action` predefinito. Il valore predefinito è `index`.
  * Per impostazione predefinita, gli schemi del routing identificano i segnaposto tramite
    un prefisso due-punti (`:`). Ma se si vogliono scrivere regole in una sintassi più
    familiare per PHP, si può aggiungere il simbolo del dollaro (`$`) in
    `variable_prefixes`. In questo modo, si possono scrivere schemi come
    '/article/$year/$month/$day/$title' invece di '/article/:year/:month/:day/:title'.
  * Lo schema del routing identifica i segnaposto tra i separatori. I separatori
    predefiniti sono la barra e il punto, ma se ne possono aggiungere altri nel parametro
    `segment_separators`. Ad esempio, se si aggiunge il trattino (`-`), si possono
    scrivere schemi come '/article/:year-:month-:day/:title'.
  * Lo schema del routing usa una sua cache, in produzione, per accelerare la conversione
    tra URL esterni e interni. Per impostazione predefinita, questa cache usa il
    filesystem, ma si può usare qualsiasi classe di cache, a patto di dichiarare tale
    classe e le sue impostazioni nel parametro `cache`. Vedere il capitolo 15 per la lista
    delle classi disponibili. Per disattivare la cache del routing in produzione,
    impostare il parametro `debug` a `true`.

Queste sono solo le impostazioni per la classe `sfPatternRouting`. Si può usare un'altra
classe per il routing, una propria oppure uno dei factory di symfony (`sfNoRouting` e
`sfPathInfoRouting`). Con uno di questi due, tutti gli URL esterni appaiono come
'module/action?key1=param1'. Non si può personalizzare, ma è veloce. La differenze è che
il primo usa `GET` di PHP e il secondo usa `PATH_INFO`. Meglio usarli solo per interfacce
di amministrazione.

Ci sono altri parametri legati al routing, ma questo si trova in `settings.yml`:

  * `no_script_name` abilita il nome del front controller negli URL generati.
     L'impostazione `no_script_name` può essere attiva solo per una singola applicazione
     a progetto, a meno che non si pongano i front controller in cartelle diverse e si
     alterino le regole predefinite di routing. Di solita si attiva per l'ambiente di
     produzione dell'applicazione principale e si disattiva per le altre.

#### Impostazioni di validazione dei form

>**NOTE**
>Le caratteristiche descritte in questa sezione sono deprecate da symfony 1.1 e
>funzionano solo abilitando il plugin `sfCompat10`.

Le impostazioni di validazione dei form controllano il modo in cui i messaggi di errore
sono mostrati dagli helper `Validation` (vedere capitolo 10). Questi errori sono inseriti
in tag `<div>` usano `validation_error_ class` come attributo `class` e
`validation_error_id_prefix` per costruire l'attributo `id`. I valori predefiniti sono
`form_error` e `error_for_`, quindi gli attributi mostrati da una chiamata all'helper
`form_error()` per un input di nome `pippo` saranno `class="form_error" id="error_for_pippo"`.

Due impostazioni determinano quali caratteri precedono e seguono ogni messaggio di
errore: `validation_error_prefix` e `validation_error_suffix`. Possono essere modicati
per personalizzare tutti i messaggi di errore.

#### Impostazioni della cache

Le impostazioni della cache sono definiti per la maggior parte in `cache.yml`, tranne per
due impostazioni in `settings.yml`: `cache` abilita la cache e `etag` abilita la gestione
di ETag sul server (vedere capitolo 15). Si può anche specificare quale sistema di
memorizzazione usare per tutte le cache (della vista, del routing e di i18n) in
`factories.yml`. Il Listato 19-2 mostra la configurazione predefinita della cache della
vista.

Listato 19-2 - Impostazioni della cache della vista, in `frontend/config/factories.yml`

    view_cache:
      class: sfFileCache
      param:
        automatic_cleaning_factor: 0
        cache_dir:                 %SF_TEMPLATE_CACHE_DIR%
        lifetime:                  86400
        prefix:                    %SF_APP_DIR%/template

La voce `class` può essere una tra `sfFileCache`, `sfAPCCache`, `sfEAcceleratorCache`,
`sfXCacheCache`, `sfMemcacheCache` e `sfSQLiteCache`. Può anche essere una classe
personalizzata, a patto che estenda `sfCache` e fornisca gli stessi metodi generici per
impostare, recuperare e cancellare una chiave nella cache. I parametri del factory
dipendono dalla classe scelta, ma ci sono alcune costanti:

  * `lifetime` definisce il numero di secondo dopo i quali una parte di cache va rimossa
  * `prefix` è un prefisso aggiunto a ogni chiave della cache (usare l'ambiente nel
    prefisso per avere cache diverse in base all'ambiente). Usare lo stesso prefisso per
    due applicazioni, se si vuole che condividano la cache.

Poi, per ogni particolare factory, occorre definire la locazione della memorizzazione
della cache.

 * per `sfFileCache`, il parametro `cache_dir` individua il percorso assoluto della
   cartella della cache
 * `sfAPCCache`, `sfEAcceleratorCache` e `sfXCacheCache` non hanno parametri di locazione,
   perché usano funzioni native di PHP per comunicare con APC, EAccelerator o  XCache
 * per `sfMemcacheCache`, inserire il nome dell'host del server Memcached nel
   parametro `host` oppure un array di host nel parametro `servers`
 * per `sfSQLiteCache`, inserire il percorso assoluto del file del database SQLite nel
   parametro `database`

Per ulteriori parametri, controllare la documentazione delle API per ogni classe.

La vista non è l'unica parte a poter usare una cache. Sia il factory `routing` che quello
`I18N` offrono un parametro `cache`, in cui si può impostare un factory cache, proprio
come per la vista. Ad esempio, il Listato 19-1 mostra come il routing usi la cache
dei file per accelerare, ma la si può cambiare.

#### Impostazioni di log

Ci sono due impostazioni di log (vedere capitolo 16) in `settings.yml`:

  * `error_reporting` specifica quali eventi inserire nei log di PHP. La sua impostazione
    predefinita è `E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR` per
    l'ambiente di produzione (quindi gli eventi che vanno in log sono `E_PARSE`,
    `E_COMPILE_ERROR`, `E_ERROR`, `E_CORE_ERROR` e `E_USER_ERROR`) e `E_ALL | E_STRICT`
    per l'ambiente di sviluppo.
  * L'impostazione `web_debug` attiva la web debug toolbar. Impostare a `true` solo per
    ambiente di sviluppo e di test.

#### Percorsi delle risorse

Il file `settings.yml` memorizza anche dei percorsi per le risorse. Se si vuole usare una
versione diversa di una risorsa distribuita con symfony, si possono cambiare questi
percorsi:

  * I file necessari al generatore di amministrazione, in `admin_web_dir`
  * I file necessari alla web debug toolbar, in `web_debug_web_dir`

#### Helper predefiniti

Gli helper predefiniti, caricati per ogni template, sono dichiarati nell'impostazione
`standard_helpers` (vedere capitolo 7). I predefiniti sono i gruppi `Partial` e `Cache`.
Se si usano altri gruppi di helper in tutti i template di un'applicazione, aggiungere i
loro nomi all'impostazione `standard_helpers` fa risparmiare la dichiarazione ripetuta di
`use_helper()` in ogni template.

#### Moduli attivati

I moduli attivati dai plugin o da symfony sono dichiarati nel parametro `enabled_modules`.
Anche se un plugin ha un modulo, questo non può essere usato a meno di non essere
dichiarati in `enabled_modules`. Il modulo `default`, che fornisce alcune pagine
predefinite di symfony (congratulazioni, pagina non trovata, ecc.), è l'unico modulo
già abilitato.

#### Set di caratteri

Il set di caratteri della risposta è un'impostazione generale dell'applicazione, perché è
usata da diversi componenti del framework (template, escape dell'output, helper, ecc.).
Definito nell'impostazione `charset`, il valore predefinito (e consigliato) è `utf-8`.

>**SIDEBAR**
>Aggiungere le proprie impostazioni
>
>Il file `settings.yml` definisce le impostazioni di symfony per un'applicazione. Come
>discusso nel capitolo 5, quando si vogliono aggiungere nuovi parametri, il posto migliore
>per farlo è il file `frontend/config/app.yml`. Questo file è anche dipendente
>dall'ambiente e le impostazioni che vi sono definite sono disponibili tramite la classe
>`sfConfig` col prefisso `app_`.
>
>
>     all:
>       creditcards:
>         fake:             false    # app_creditcards_fake
>         visa:             true     # app_creditcards_visa
>         americanexpress:  true     # app_creditcards_americanexpress
>
>
>Si può mettere un file `app.yml` anche nella cartella di configurazione del progetto e
>definire quindi impostazioni personalizzate per il progetto. La configurazione a cascata
>si applica anche a questo file, quindi le impostazioni definite in `app.yml`
>dell'applicazione sovrascrivono qelle definite a livello di progett.

Estendere l'autocaricamento
---------------------------

L'autocaricamento, spiegato brevemente nel capitolo 2, evita di dover richiedere ogni
volta le classi, se sono inserite in cartelle specifiche. Questo vuol dire che si può
lasciare che il framework faccia il lavoro per noi, consentendogli di caricare solo le
classi necessarie nel momento opportuno e solo quando necessario.

Il file `autoload.yml` elenca i percorsi in cui le classi autocaricate risiedono. La
prima volta che questo file viene processato, symfony analizza tutte le cartelle. Ogni
volta che trova un file con estensione `.php` in una di queste cartelle, il percorso del
file e il nome della classe sono aggiunti a una lista interna di classi autocaricate.
Questa lista è salvata in cache, in un file chiamato `config/config_autoload.yml.php`.
Quindi, durante l'esecuzione, quando si usa una classe, symfony cerca il percorso in
questa lista e include automaticamente il file `.php`.

L'autocaricamento funziona per tutti i file `.php` che contengono classi o interfacce.

Per impostazione predefinita, le classi che si trovano nelle seguenti cartelle del
progetto beneficiano automaticamente dell'autocaricamento:

  * `progetto/lib/`
  * `progetto/lib/model`
  * `progetto/apps/frontend/lib/`
  * `progetto/apps/frontend/modules/mymodule/lib`

Non c'è un file `autoload.yml` nella cartella di configurazione predefinita di
un'applicazione. Se si vogliono modificare le impostazioni del framework, ad esempio per
caricare automaticamente classi che si trovano altrove, basta creare un file `autoload.yml`
vuoto e sovrascrivere le impostazioni di
`sfConfig::get('sf_symfony_lib_dir')/config/config/autoload.yml` o aggiungere le proprie.

Il file `autoload.yml` deve iniziare con la chiave `autoload:` ed elencare i posti in cui
symfony deve cercare le classi. Ogni posto richiede un'etichetta. Questo dà la
possibilità di sovrascrivere le voci di symfony. Per ogni posto, fornire un nome `name`
(apparirà come commento in `config_autoload.yml.php`) e un percorso assoluto `path`.
Quindi definire se la ricerca debba essere `recursive` (ricorsiva), cioè se symfony deve
cercare nelle sottocartelle, oppure `exclude` (escludere alcune sottocartelle). Il
Listato 19-3 mostra i posti predefiniti e la sintassi del file.

Listato 19-3 - Configurazione predefinita di autocaricamento, in `sfConfig::get('sf_symfony_lib_dir')/config/config/autoload.yml`

    autoload:
      # plugins
      plugins_lib:
        name:           plugins lib
        path:           %SF_PLUGINS_DIR%/*/lib
        recursive:      true

      plugins_module_lib:
        name:           plugins module lib
        path:           %SF_PLUGINS_DIR%/*/modules/*/lib
        prefix:         2
        recursive:      true

      # project
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony]

      project_model:
        name:           project model
        path:           %SF_LIB_DIR%/model
        recursive:      true

      # application
      application:
        name:           application
        path:           %SF_APP_LIB_DIR%
        recursive:      true

      modules:
        name:           module
        path:           %SF_APP_DIR%/modules/*/lib
        prefix:         1
        recursive:      true

I percorsi possono contenere caratteri jolly e usare i parametri dei percorsi dei file
definiti nelle classi di configurazione (vedere la prossima sezione). Se si usano questi
parametri nel file di configurazione, devono essere scritti in maiuscolo e racchiusi tra
simboli di percentuale `%`.

Modificando il proprio `autoload.yml`, si aggiungeranno nuovi posti all'autocaricamento di
symfony, ma si potrebbe voler estendere questo meccanismo per aggiungere i propri gestori
di autocaricamento al gestore di symfony. Poiché symfony usa la funzione standard
`spl_autoload_register()` per gestire l'autocaricamento, si possono registrare ulteriori
callback nella classe di configurazione dell'applicazione:

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function initialize()
      {
        parent::initialize(); // carica prima l'autocaricamento di symfony

        // inserire qui le proprie funzioni o metodi di autocaricamento
        spl_autoload_register(array('myToolkit', 'autoload'));
      }
    }

Quando il sistema di autocaricamento di PHP incontra una nuova classe, prova prima il
metodo di autocaricamento di symfony (e usa i posti definiti in `autoload.yml`). Se non
trova una definizione di classe, tutti gli altri callable registrati con
`spl_autoload_register()` saranno richiamati, finché la classe non viene trovata. Quindi
si possono aggiungere quanti meccanismi di autocaricamento si vuole, ad esempio per
fornire sistemi di aggancio con componenti di altri framework (vedere capitolo 17).

Struttura dei file personalizzata
---------------------------------

Ogni volta che il framework usa un percorso per cercare qualcosa (classi, template,
plugin, configurazioni, ecc.), usa una variabile invece di un percorso reale.
Cambiando queste variabili, si può alterare completamente la struttura di cartelle di
un progetto symfony e adattarsi ai requisiti di organizzazione dei file di qualsiasi
cliente.

>**CAUTION**
>Personalizzare la struttura di cartella di un progetto è possibile, ma non sempre è una
>buona idea. Uno dei punti di forza di un framework come symfony sta nel fatto che ogni
>sviluppatore può guardare un progetto già fatto e trovarlo familiare, perché le
>convenzioni sono state rispettate. Assicurarsi di considerare bene questo aspetto, prima
>di decidere di personalizzare la struttura delle cartelle.

### La struttura di base dei file

Le variabili dei percorsi sono definiti nelle classi `sfProjectConfiguration` e
`sfApplicationConfiguration` e memorizzate nell'oggetto `sfConfig`. Il Listato 19-4 mostra
un elenco di variabili di percorsi e le relative cartelle.

Listato 19-4 - Variabili predefinite della struttura dei file, definite in `sfProjectConfiguration` e `sfApplicationConfiguration`

    sf_root_dir           # progetto/
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

Ogni percorso a una cartella è determinato da un parametro che finisce con `_dir`. Usare
sempre le variabili invece dei percorsi veri (relativi o assoluti) dei file, in modo da
poterli cambiare successivamente, se necessario. Ad esempio, se si vuole spostare un file
nella cartella `uploads/`, usare `sfConfig::get('sf_upload_dir')` e non
`sfConfig::get('sf_root_dir').'/web/uploads/'`.

### Personalizzare la struttura dei file

Se si deve sviluppare un'applicazione per un cliente che ha già una struttura definita,
probabilmente si dovrà modificare la struttura predefinita del progetto. Ridefinendo le
variabili `sf_XXX_dir` con `sfConfig`, si può far funzionare symfony in una struttura
completamente diversa. Il posto migliore dove farlo è nella classe `ProjectConfiguration`
dell'applicazione oppure nella classe `XXXConfiguration`, per le cartelle delle
applicazioni.

Per esempio, se si vuole che tutte le applicazioni condividano una sola cartella per
i template del layout, basta aggiungere questa riga nel metodo `setup()` della classe
`ProjectConfiguration` per ridefinire le impostazioni di `sf_app_template_dir`:

    [php]
    sfConfig::set('sf_app_template_dir', sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'templates');

>**NOTE**
>Anche se si può cambiare la struttura delle cartelle del progetto con `sfConfig::set()`,
>è meglio usare i metodi dedicati definiti dalle classi di configurazione del progetto e
>dell'applicazione, se possibile, perché questi si occupano di cambiare tutti i percorsi
>correlati. Per esempio, il metodo `setCacheDir()` cambia le seguenti costanti:
>`sf_cache_dir`, `sf_app_base_cache_dir`, `sf_app_cache_dir`, `sf_template_cache_dir`,
>`sf_i18n_cache_dir`, `sf_config_cache_dir`, `sf_test_cache_dir` e `sf_module_cache_dir`.

### Cambiare la cartella radice del progetto

Tutti i percorsi costruiti nelle classi di configurazione si basano sulla cartella radice
del progetto, che è stabilita dal file `ProjectConfiguration` incluso dal front
controller. Di solito la cartella radice è un livello sopra la cartella `web/`, ma si
potrebbe usare una struttura diversa. Si supponga che la propria struttura principale di
cartella sia costituita da due cartelle, una pubblica e l'altra privata, come mostrato nel
Listato 19-5. Questo succede solitamente per progetti su host condivisi.

Listato 19-5 - Esempio di struttura di cartelle personalizzata per un host condiviso

    symfony/    # Area privata
      apps/
      config/
      ...
    www/        # Area pubblica
      images/
      css/
      js/
      index.php

In questo caso, la cartella radice è la cartella `symfony/`. Quindi il front controller
`index.php` deve solo includere il file `config/ProjectConfiguration.class.php`, come
segue, per far funzionare l'applicazione:

    [php]
    require_once(dirname(__FILE__).'/../symfony/config/ProjectConfiguration.class.php');

Inoltre, usare il metodo `setWebDir()` per cambiare l'area pubblica dal solito `web/` a
`www/`, come segue:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->setWebDir($this->getRootDir().'/../www');
      }
    }

Capire i gestori di configurazione
----------------------------------

Ogni file di configurazione ha un gestore. Il compito dei gestori di configurazione è
quello di gestire la configurazione a cascata e di fare la traduzione tra i file di
configurazione e il codice PHP ottimizzato eseguibile a runtime.

### Gestori di configurazione predefiniti

Il gestore di configurazione predefinito è memorizzato in
`sfConfig::get('sf_symfony_lib_dir')/config/config/config_handlers.yml`. Questo file
collega i gestori ai file di configurazione, secondo un percorso di file. Il Listato 19-6
mostra un estratto di questo file.

Listato 19-6 - Estratto di `sfConfig::get('sf_symfony_lib_dir')/config/config/config_handlers.yml`

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

Per ogni file di configurazione (`config_handlers.yml` identifica ogni file in base a un
percorso di file con caratteri jolly), la classe gestore è specificata sotto la chiave
`class`.

Le impostazioni dei file di configurazione gestiti da `sfDefineEnvironmentConfigHandler`
possono essere rese disponibili direttamente nel codice, tramite la classe `sfConfig`, e
il parametro `key` contiene il valore del prefisso.

Si possono aggiungere o modificare i gestori usati per processare ogni file di
configurazione, ad esempio per usare file INI o XML invece di file YAML.

>**NOTE**
>Il gestore di configurazione per il file `config_handlers.yml` è `sfRootConfigHandler` e
>ovviamente non può essere modificato.

Se si dovesse aver bisogno di cambiare il modo in cui la configurazione viene analizzata,
si può creare un file vuoto `config_handlers.yml` nella cartella `config/`
dell'applicazione e sovrascrivere le righe `class` con le proprie classi.

### Aggiungere il proprio gestore

L'utilizzo di un gestore che si occupi di file di configurazione fornisce due importanti
vantaggi:

  * Il file di configurazione viene trasformato in un file di codice PHP e questo codice
    viene memorizzato nella cache. Questo vuol dire che la configurazione viene analizzata
    soloa una volta, in produzione, è le prestazioni sono ottimizzate.
  * Il file di configurazione può essere definito a diversi livelli (progetto e
    applicazione) e i valori finali dei parametri risulteranno da una cascata. Quindi si
    possono definire parametri a livello di progetto e sovrascriverli in base alle
    applicazioni.

Se ci si sente di scrivere un proprio gestore di configurazione, seguire l'esempio della
struttura usata dal framework nella cartella `sfConfig::get('sf_symfony_lib_dir')/config/`.

Supponiamo di avere nella propria applicazione una classe `myMapAPI`, che fornisce
un'interfaccia verso servizi di mappe di terze parti. Questa classe ha bisogno di essere
inizializzata con un URL e un nome utente, come mostrato nel Listato 19-7.

Listato 19-7 - Esempio di inizializzazione della classe `myMapAPI`

    [php]
    $mapApi = new myMapAPI();
    $mapApi->setUrl($url);
    $mapApi->setUser($user);

Si potrebbe voler memorizzare questi due parametri in un file di configurazione
personalizzato chiamato `map.yml`, situato nella cartella `config/` dell'applicazione.
Il file potrebbe avere i seguenti contenuti:

    api:
      url:  map.api.example.com
      user: pippo

Per poter trasformare queste impostazioni nel codice del Listato 19-7, occorre costruire
un gestore di configurazione. Ogni gestore di configurazione deve iniziare con
`sfConfigHandler` e fornire un metodo `execute()`, che accetta come parametro un array di
percorsi di file di configurazione e deve restituire i dati da scrivere nel file di cache.
I gestori di file YAML dovrebbero estendere la classe `sfYamlConfigHandler`, che fornisce
strutture aggiuntive per l'analisi del codice YAML. Per il file `map.yml`, un tipico
gestore di configurazione potrebbe essere scritto come nel Listato 19-8.

Listato 19-8 - Un gestore di configurazione personalizzato, in `frontend/lib/myMapConfigHandler.class.php`

    [php]
    <?php

    class myMapConfigHandler extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        // Analizza lo yaml
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

L'array `$configFiles` che symfony passa al metodo `execute()` conterrà un percorso verso
tutti i file `map.yml` trovati nelle cartelle `config/`. Il metodo `parseYamls()`
gestirà la configurazione a cascata.

Per poter associare questo nuovo gestore con il file `map.yml`, si deve creare un file di
configurazione `config_handlers.yml`, con il seguente contenuto:

    config/map.yml:
      class: myMapConfigHandler

>**NOTE**
>La classe definita in `class` deve essere autocaricata (come in questo caso) oppure
>definita nel file il cui percorso è definito nel parametro `file`, sotto la voce `param`.

Come per molti altri file di configurazione di symfony, si può anche registrare un gestore
di configurazione direttamente nel codice PHP:

    sfContext::getInstance()->getConfigCache()->registerConfigHandler('config/map.yml', 'myMapConfigHandler', array());

Quando, nella propria applicazione, si ha bisogno del codice basato sul file `map.yml`
generato dal gestore `myMapConfigHandler`, basta usare il seguente codice:

    [php]
    include sfContext::getInstance()->getConfigCache()->checkConfig('config/map.yml');

Quando il metodo `checkConfig()` viene richiamato, symfony cerca dei file `map.yml`
esistenti nelle cartelle di configurazione e li processa col gestore specificato nel file
`config_handlers.yml`, se non esiste già un `map.yml.php` in cache, oppure se il file
`map.yml` è più recente di quello in cache.

>**TIP**
>Se si vogliono gestire gli ambienti in un file di configurazione YAML, il gestore può
>estendere la classe `sfDefineEnvironmentConfigHandler`, invece di `sfYamlConfigHandler`.
>Invece di richiamare il metodo `parseYaml()` per recuperare la configurazione, si deve
>richiamare il metodo `getConfiguration()`:
>`$config = $this->getConfiguration($configFiles)`.

-

>**SIDEBAR**
>Usare i gestore di configurazione esistenti
>
>Se si ha solo bisogno di consentire agli utenti di recuperare dei valori dal codice
>tramite `sfConfig`, si può usare la classe di gestione della configurazione
>`sfDefineEnvironmentConfigHandler`. Ad esempio, per rendere disponibili i parametri
>`url` e `user` come `sfConfig::get('map_url')` e `sfConfig::get('map_user')`, definire il
>proprio gestore come segue:
>
>     [yml]
>     config/map.yml:
>       class: sfDefineEnvironmentConfigHandler
>       param:
>         prefix: map_
>
>Si faccia attenzione a non usare un prefisso già usato da altri gestori. Prefissi già
>usati sono `sf_`, `app_` e `mod_`.

Riepilogo
---------

I file di configurazione possono modificare pesantemente il modo in cui funziona il
framework. Poiché symfony si basa sulla configurazione anche per le proprie
caratteristiche basilari e per caricare i file, esso può adattarsi a molti più ambienti
di quelli predefiniti. Questa grande configurabilità è uno dei maggiori punti di forza di
symfony. Anche se a volte spaventa i nuovi arrivati, che vedono nei file di
configurazione un sacco di convenzioni da imparare, essa consente alle applicazioni
symfony di essere compatibili con un grandissimo numero di piattaforme e di ambienti.
Una volta in grado di padroneggiare la configurazione di symfony, nessun server potrà
rifiutarsi di eseguire le nostre applicazioni!
 
