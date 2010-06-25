Capitolo 19 - Padroneggiare i file di configurazione di symfony
===============================================================

Ora che si conocsce symfony molto bene, si è già in grado di scavare nel suo codice per
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
    Capitolo 6 per maggiori dettagli). Il valore predefinito è `default/login`.
  * `secure_module` e `secure_action`: Azione richiamata quando un utente non ha le
    credenziali necessarie per un'azione. Il valore predefinito è `default/secure`.
  * `module_disabled_module` e `module_disabled_action`: Azione richiamata quando un
    utente richiede un modulo dichiarato come disabilitato in `module.yml`. Il valore
    predefinito è `default/disabled`.

Prima di mettere in produzione un'applicazione, si dovrebbero personalizzare queste
azioni, perché il template del modulo `default` include il logo di symfony. Si veda la

Before deploying an application to production, you should customize these actions, because the `default` module templates include the symfony logo on the page. See Figura 19-1 per una schermata di una di queste pagine, la pagina di errore 404.

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
    esplicito (si veda il Capitolo 16 per maggiori dettagli).
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
>impostazioone predefinita, poiché aggiunge un piccolo overhead a ogni richiesta.

### Attivazione di caratteristiche opzionali

Alcuni parametri di `settings.yml` controllano delle caratteristiche opzionali del
framework, che possono essere abilitate o disabilitate. Disattivare le caratteristiche
inutilizzate aumenta un po' le prestazioni, quindi ci si dovrebbe assicurare di
rivedere le impostazioni elencati nella Tabella 19-1, prima di mandare in produzione
l'applicazione.

Tabella 19-1 - Caratteristiche opzionali impostabili in `settings.yml`

Parametro           | Descrizione                                    | Valore predefinito
------------------- | ---------------------------------------------- | ------------------
`use_database`      | Abilita la gestione del database. Impostare a `false` se non si usa un database. | `true`
`i18n`              | Abilita la traduzione dell'interfaccia (si veda il Capitolo 13). Impostare a `true` per applicazioni multi-lingua. | `false`
`logging_enabled`   | Abilita il log degli eventi di symfony. Impostare a `false` se si vogliono disabilitare i log. | `true`
`escaping_strategy` | Abilita l'escape dell'output (si veda il Capitolo 7). Impostare a `true` se si vuole l'escape dei dati passati ai template. | `true`
`cache`             | Abilita template caching (see Capitolo 12). Impostare a `true` se almeno un modulo include il file `cache.yml`. Il filtro della cache (`sfCacheFilter`) è abilitato. | `false` in sviluppo, `true` in produzione
`web_debug`         | Abilita la web debug toolbar per facilitare il debug (si veda il Capitolo 16). Impostare a `true` per mostrare toolbar su ogni pagina. | `true` in sviluppo, `false` in produzione
`check_symfony_version` | Abilita la verifica della versione di symfony a ogni richiesta. Impostare a `true` per pulire la cache automaticamente dopo un aggiornamento di symfony. Lasciare a `false` se si pulisce la cache a mano dopo un aggiornamento. | `false`
`check_lock`        | Abilita il sistema di blocco dell'applicazione, attivato dai task `cache:clear` e `project:disable` (vedere la sezione precedente). Impostare a `true` per fare in modo che tutte le richieste ad applicazioni disabilitate siano rinviate alla pagina `sfConfig::get('sf_symfony_lib_dir')/exception/data/unavailable.php`. | `false`
`compressed`        | Abilita la compressione della risposta in PHP. Impostare a `true` per comprimere il codice HTML in uscita tramite il gestore di compressione di PHP. | `false`

### Configurazione delle caratteristiche

Symfony usa alcuni parametri di `settings.yml` per modificare il comportamento di
caratteristiche predefinite, come la validazione di form, la cache e moduli di terze
parti.

#### Impostazioni dell'escape dell'output

Le impostazioni di escape dell'output controllano il modo in cui il template accede alle
variabili (vedere Capitolo 7). Il file `settings.yml` include due impostazioni per questa
caratteristica:

  * L'impostazione `escaping_strategy` accetta i valori `true` o `false`.
  * L'impostazione `escaping_method` accetta i valori `ESC_RAW`, `ESC_SPECIALCHARS`,
    `ESC_ENTITIES`, `ESC_JS` o `ESC_JS_NO_ENTITIES`.

#### Impostazioni del routing

Le impostazioni del routing (vedere Capitolo 9) sono definite in `factories.yml`, sotto
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
    un prefisso duepunti (`:`). Ma se si vogliono scrivere regole in una sintassi più
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
    classe e le sue impostazioni nel parametro `cache`. Vedere il Capitolo 15 per la lista
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

TODO....
