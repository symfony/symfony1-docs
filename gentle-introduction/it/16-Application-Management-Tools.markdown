Capitolo 16 - Strumenti per la gestione dell'applicazione
=========================================================

Durante le fasi di sviluppo e di deployment, gli sviluppatori richiedono un flusso costante di informazioni diagnostiche, al fine di determinare se l'applicazione sta funzionando come previsto. Tali informazioni generalmente vengono aggregate attraverso utility per il log e il debug. A causa del ruolo centrale dei framework come symfony, utilizzati come motore delle applicazioni, è essenziale che tali capacità siano strettamente integrate in modo da garantire uno sviluppo efficiente.

Durante la vita di un'applicazione sul server di produzione, l'amministratore dell'applicazione ripete un gran numero di task, dalla rotazione dei log agli aggiornamenti. Un framework deve, per quanto possibile anche fornire strumenti per automatizzare questi task.

Questo capitolo spiega come gli strumenti di gestione delle applicazioni di symfony siano in grado di rispondere a tutte queste esigenze.

I log
-----

L'unico modo per capire cosa è andato storto durante l'esecuzione di una richiesta, è quello di vedere un trace del processo di esecuzione. Fortunatamente, come si vedrà in questa sezione, sia PHP che symfony salvano grandi quantità di questo tipo di dati nei log.

### I log di PHP

PHP ha un parametro `error_reporting`, definito in `php.ini`, che specifica quali eventi devono essere registrati nel log. Symfony consente di sovrascrivere questo valore, per applicazione e ambiente, nel file `settings.yml`, come mostrato nel Listato 16-1.

Listato 16-1 - Impostazione del livello di segnalazione degli errori, in `frontend/config/settings.yml`

    prod:
     .settings:
        error_reporting:  <?php echo (E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR)."\n" ?>

    dev:
      .settings:
        error_reporting:  <?php echo (E_ALL | E_STRICT)."\n" ?>

Al fine di evitare problemi di prestazioni nell'ambiente di produzione, il server registra solo gli errori critici PHP. Tuttavia, nell'ambiente di sviluppo, vengono registrati tutti i tipi di eventi, in modo che lo sviluppatore può avere tutte le informazioni necessarie per rintracciare errori.

La posizione del file di log PHP dipendono dalla configurazione nel file `php.ini`. Se non ci si è mai preoccupati di definire tale posizione, PHP probabilmente userà il log fornito dal server web (come i log di errore di Apache). In questo caso, i log di PHP si troveranno sotto la cartella di log del web server.

### I log di symfony

In aggiunta ai log standard di PHP, symfony può registrare molti eventi personalizzati. È possibile trovare tutti i log di symfony sotto la cartella `mioprogetto/log/`. C'è un file per applicazione e ambiente. Per esempio, il file di log dell'ambiente di sviluppo dell'applicazione `frontend` si chiama `frontend_dev.log`, quello di produzione si chiama `frontend_prod.log` e così via.

Se si dispone di un'applicazione in esecuzione di symfony, si può dare un'occhiata al suo file di log. La sintassi è molto semplice. Per ogni evento, viene aggiunta una riga al file di log dell'applicazione. Ogni linea include l'ora esatta dell'evento, la natura dell'evento, l'oggetto che è stato processato e ogni altro dettaglio rilevante. Il Listato 16-2 mostra un esempio del contenuto di un file log di symfony.

Listato 16-2 - Esempio del contenuto di un file log di symfony, in `log/frontend_dev.log`

    Nov 15 16:30:25 symfony [info ] {sfAction} call "barActions->executemessages()"
    Nov 15 16:30:25 symfony [info ] {sfPropelLogger} executeQuery: SELECT bd_message.ID...
    Nov 15 16:30:25 symfony [info ] {sfView} set slot "leftbar" (bar/index)
    Nov 15 16:30:25 symfony [info ] {sfView} set slot "messageblock" (bar/mes...
    Nov 15 16:30:25 symfony [info ] {sfView} execute view for template "messa...
    Nov 15 16:30:25 symfony [info ] {sfView} render "/home/production/mioprogetto/...
    Nov 15 16:30:25 symfony [info ] {sfView} render to client

Si possono trovare molti dettagli in questi file, comprese le query SQL effettive inviate al database, i template chiamati, la catena di chiamate tra gli oggetti e così via.

>**NOTE**
>Il formato dei file di log è configurabile sovrascrivendo le impostazioni `format` e/o `time_format` presenti in `factories.yml` come mostrato nel Listato 16-3.

Listato 16-3 - Cambiare il formato del log

    all:
      logger:
        param:
          sf_file_debug:
            param:
              format:      %time% %type% [%priority%] %message%%EOL%
              time_format: %b %d %H:%M:%S

#### Configurazione del livello di log di symfony

Ci sono otto livelli per i messaggi di log di symfony: `emerg`, `alert`, `crit`, `err`, `warning`, `notice`, `info` e `debug`, che sono gli stessi dei livelli dei pacchetti [`PEAR::Log`](http://pear.php.net/package/Log/). Si può configurare il livello massimo che deve essere nei log per ciascun ambiente nel file di configurazione `factories.yml` di ciascuna applicazione, come mostrato nel Listato 16-4.

Listato 16-4 - Configurazione predefinita del log, in `frontend/config/factories.yml`

    prod:
      logger:
        param:
          level: err

Per impostazione predefinita, in tutti gli ambienti escluso quello di produzione, tutti i messaggi vengono registrati nel log (fino al livello meno importante, il livello `debug`). Nell'ambiente di produzione, per impostazione predefinita il log è disabilitato; se in `settings.yml` si cambia `logging_enabled` a `true`, nei log verranno registrati solo i messaggi più importanti (da `crit` a `emerg`)
		  
Nel file `factories.yml` si può cambiare il livello dei log per ciascun ambiente per limitare il tipo dei messaggi registrati.

>**TIP**
>Per vedere se la registrazione dei log è abilitata, chiamare `sfConfig::get('sf_logging_enabled')`.

#### Aggiungere un messaggio di log

È possibile aggiungere manualmente un messaggio nel file di log di symfony dal codice utilizzando una delle tecniche descritte nel Listato 16-5.

Listato 16-5 - Aggiungere un messaggio di log personalizzato

    [php]
    // Da una azione
    $this->logMessage($message, $level);

    // Da un template
    <?php use_helper('Debug') ?>

    <?php log_message($message, $level) ?>

`$level` può avere gli stessi valori dei messaggi di log.

In alternativa, per scrivere un messaggio nel log da qualsiasi punto della propria applicazione, si possono utilizzare direttamente i metodi `sfLogger`, come mostrato nel Listato 16-6. I metodi disponibili portano i nomi stessi dei livelli di log.

Listato 16-6 - Aggiungere un messaggio di log personalizzato da qualsiasi parte

    [php]
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info($message);
    }

>**SIDEBAR**
>Personalizzazione dei log
>
>Il sistema dei log di symfony è molto semplice, anche riguardo la personalizzazione.
>L'unico prerequisito è che le classi dei logger devono estendere la classe `sfLogger`, la quale definisce un metodo `doLog()`. Symfony chiama il metodo `doLog()` con due parametri: `$message` (il messaggio che deve essere registrato nel log) e `$priority` (il livello del log).
>
>La classe `mioLogger` definisce un semplice logger usando la funzione PHP `error_log`:
>
>     [php]
>     class mioLogger extends sfLogger
>     {
>       protected function doLog($message, $priority)
>       {
>         error_log(sprintf('%s (%s)', $message, sfLogger::getPriorityName($priority)));
>       }
>     }
>
>Per creare un logger da una classe già esistente, basta implementare l'interfaccia `sfLoggerInterface`, che definisce un metodo `log()`. Il metodo prende gli stessi due parametri del metodo `doLog()`:
>
>     [php]
>     require_once('Log.php');
>     require_once('Log/error_log.php');
>
>     // Define a thin wrapper to implement the interface
>     // for the logger we want to use with symfony
>     class Log_mio_error_log extends Log_error_log implements sfLoggerInterface
>     {
>     }

#### Ridurre e ruotare i file di log

Non bisogna dimenticare di eliminare periodicamente la cartella `log/` delle applicazioni, perché questi file hanno la strana abitudine di crescere di diversi megabyte in pochi giorni, a seconda, naturalmente, del traffico. Symfony fornisce un task speciale `log:clear` per questo scopo, ed è possibile lanciarlo periodicamente a mano o metterlo in una tabella di cron. Per esempio il seguente comando cancella i file di log di symfony:

    $ php symfony log:clear

Sia per questioni di prestazioni che di sicurezza, probabilmente si vorrà memorizzare i log di symfony in diversi piccoli file invece di un singolo file di grandi dimensioni. La strategia di memorizzazione ideale per i file di log è quella di eseguire il backup e svuotare il file di log principale regolarmente, ma di tenere solo un numero limitato di backup. È possibile attivare un tale rotazione dei log con un `periodo` di `7` giorni e uno `storico` (numero di copie di backup) di `10`, come mostrato nel Listato 16-7. Si potrebbe lavorare con un file di log attivo più dieci file di backup contenenti sette giorni di storia per ciascuno. Ogni volta che il prossimo periodo di sette giorni termina, il file di registro corrente va nel backup, e il backup più vecchio viene cancellato.

Listato 16-7 - Avviare la rotazione dei log

    $ php symfony log:rotate frontend prod --period=7 --history=10

I file con i backup dei log vengono salvati nella cartella `logs/history/` e gli viene aggiunto un suffisso con la data di quando vengono salvati.

Debug
-----

Non importa quanto si sia abili a programmare, perché si fanno sempre degli errori, anche se si utilizza symfony. L'individuazione e la comprensione degli errori è una delle chiavi per sviluppare velocemente delle applicazioni. Fortunatamente, symfony fornisce molti strumenti di debug per lo sviluppatore.

### La modalità debug di symfony

Symfony ha una modalità di debug che facilita lo sviluppo e il debug delle applicazioni. Quando è abilitata, succedono le cose seguenti:

  * La configurazione viene verificata a ogni richiesta, così la modifica di un qualunque file di configurazione ha un effetto immediato, senza che ci sia la necessità di cancellare la cache della configurazione.
  * I messaggi di errore visualizzano l'intero stack trace in modo chiaro, in modo che si possa trovare velocemente la causa del problema.
  * Sono disponibili più strumenti per il debug (ad esempio i dettagli delle query al database).
  * È anche attivata la modalità di debug per Propel/Doctrine, quindi per qualunque errore in una chiamata a un oggetto Propel/Doctrine verrà mostrata una catena dettagliata con le chiamate dell'architettura Propel/Doctrine.

Al contrario, quando la modalità debug è disattivata, l'elaborazione viene gestita nel modo seguente:

  * I file con la configurazione YAML vengono letti solo una volta e trasformati in file PHP salvati nella cartella `cache/config/`. Ogni richiesta dopo la prima ignora i file YAML e usa al suo posto la configurazione presente nella cache. Di conseguenza, l'elaborazione delle richieste è molto più veloce.
  * Per eseguire una rielaborazione della configuazione, è necessario cancellare manualmente la cache della configurazione.
  * Un errore durante l'elaborazione della richiesta restituisce una risposta con codice 500 (errore interno del server), senza nessuna spiegazione su quale possa essere la causa del problema.

La modalità debug è attivata per applicazione nel front controller. Viene gestita dal valore del terzo argomento passato nella chiamata del metodo `getApplicationConfiguration()`, come mostrato nel Listato 16-8.

Listato 16-8 - Esempio di front controller con la modalità di debug attivata, in `web/frontend_dev.php`

    [php]
    <?php

    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    sfContext::createInstance($configuration)->dispatch();

>**CAUTION**
>Nel server di produzione, bisognerebbe disabilitare la modalità di debug senza lasciare nessun front controller con la modalità di debug disponibile. Non solo la modalità di debug rallenta la consegna delle pagine, ma può anche rivelare informazioni sull'interno dell'applicazione. Anche se gli strumenti di debug non rivelano mai informazioni sulla connessione al database, lo stack delle eccezioni è pieno di informazioni pericolose per un visitatore maleintenzionato.

### Le eccezioni di symfony

Quando si verifica un'eccezione nella modalità di debug, symfony mostra un'utile informazione dull'eccezione che contiene tutto quello di cui si ha bisogno per trovare la causa del problema.

I messaggi con le eccezioni sono scritti in modo chiaro e indicano la causa più probabile del problema. Spesso forniscono una possibile soluzione per risolvere il problema e per la maggior parte dei problemi più comuni, la pagina con l'eccezione può contenere un link a una pagina del sito di symfony con maggiori dettagli sull'eccezione. La pagina dell'eccezione mostra dove è avvenuto l'errore nel codice PHP (con evidenziazione colorata della sintassi), insieme allo stack completo delle chiamate di metodoto, come mostrato in Figura 16-1. È possibile seguire il trace alla prima chiamata che ha causato il problema. Sono anche indicati gli argomenti che sono stati passati ai metodi.

>**NOTE**
>Symfony si basa proprio sulle eccezioni PHP per la segnalazione degli errori. Ad esempio, l'errore 404 può essere lanciato da un `sfError404Exception`.

Figura 16-1 - Esempio di messaggio di una eccezione per una applicazione symfony

![Esempio di messaggio di una eccezione per una applicazione symfony](http://www.symfony-project.org/images/book/1_4/F1601.png "Esempio di messaggio di una eccezione per una applicazione symfony")

Durante la fase di sviluppo, le eccezioni symfony saranno di grande utilità, per il debug dell'applicazione.

### Estensione Xdebug

L'estensione PHP [Xdebug](http://xdebug.org/) permette di aumentare la quantità di informazioni che vengono loggate dal web server. Symfony integra i messaggi Xdebug nei propri feedback per il debug, quindi è una buona idea attivare questa estensione quando si esegue il debug dell'applicazione. L'installazione dell'estensione dipende molto dalla piattaforma; consultare il sito web di Xdebug per le linee guida dettagliate relative all'installazione. Una volta che Xdebug è installato, è necessario attivarlo manualmente nel proprio file `php.ini`. Per le piattaforme *nix, questo si fa aggiungendo la seguente riga:

    zend_extension="/usr/local/lib/php/extensions/no-debug-non-zts-20041030/xdebug.so"

Per le piattaforme Windows, l'attivazione di Xdebug è gestita da questa linea:

    extension=php_xdebug.dll

Il Listato 16-9 mostra un esempio della configurazione di Xdebug, che deve essere aggiunto nel file `php.ini`.

Listato 16-9 - Esempio di configurazione di Xdebug

    ;xdebug.profiler_enable=1
    ;xdebug.profiler_output_dir="/tmp/xdebug"
    xdebug.auto_trace=1             ; enable tracing
    xdebug.trace_format=0
    ;xdebug.show_mem_delta=0        ; memory difference
    ;xdebug.show_local_vars=1
    ;xdebug.max_nesting_level=100

Bisogna riavviare il web server perché la modalità Xdebug venga attivata.

>**CAUTION**
>Non dimenticarsi di disabilitare la modalità Xdebug nella piattaforma di produzione. Non facendolo, si rallenta di molto l'esecuzione di ogni pagina.

### La barra web per il debug

I file di log contengono informazioni interessanti, ma non sono molto facili da leggere. L'azione più semplice, che è quella di trovare le linee di log per una particolare richiesta, può diventare molto complicata se si hanno molti utenti che utilizzano contemporaneamente l'applicazione e un lungo storico con gli eventi. Questo è il momento in cui si comincia a sentire il bisogno di una barar web per il debug.

Questa barra appare come una finestra semitrasparente sovrapposta al normale contenuto del browser, nell'angolo in alto a destra della finestra, come mostrato nella Figura 16-2. Offre l'accesso al log degli eventi di symfony, alla configurazione corrente, alle proprietà degli oggetti request e response, ai dettagli delle query al database inviate durante la richiesta e a un grafico dei tempi di elaborazione legati alla richiesta.

Figura 16-2 - La barra web per il debug appare nell'angolo in alto a destra della finestra

![La barra web per il debug appare nell'angolo in alto a destra della finestra](http://www.symfony-project.org/images/book/1_4/F1602.jpg "La barra web per il debug appare nell'angolo in alto a destra della finestra")

Il colore di sfondo della barra di debug dipende dal più alto livello di messaggio di log verificatosi durante la richiesta. Se nessun messaggio passa il livello `debug`, la barra degli strumenti ha un fondo grigio. Se un singolo messaggio raggiunge il livello `err`, la barra degli strumenti ha uno sfondo rosso.

>**NOTE**
>Non bisogna confondere la modalità di debug con la barra web per il debug. La barra per il debug può essere visualizzata anche nella modalità di debug impostata a off, anche se in questo caso mostrerà molte meno informazioni.

Per attivare la barra web di debug per una applicazione, aprire il file `settings.yml` e cercare la chiave `web_debug`. Negli ambienti prod` e `test`, il valore predefinito per `web_debug` è `false`, per cui se la si vuole utilizzare bisogna abilitarla manualmente. Nell'ambiente `dev` la configurazione predefinita è impostata a `true`, come mostrato nel Listato 16-10.

Listato 16-10 - Attivazione della barra web per il debug, in `frontend/config/settings.yml`

    dev:
      .settings:
        web_debug: true

Quando presente, la barra web per il debug, rende disponibili molte informazioni:

  * Cliccare sul logo di symfony per ridurre la visibilità della barra. Quando è ridotta, la barra non nasconde più gli elementi posizionati nella zona alta della pagina.
  * Cliccare sulla sezione "config" per visualizzare i dettagli di request, response, settings, globals e proprità PHP, come mostrato nella Figura 16-3. La linea in alto racchiude le impostazioni delle configurazioni importanti, come la modalità di debug, la cache e la presenza di un acceleratore PHP (appaiono in rosso se sono disattivati e in verde se sono attivati).

Figura 16-3 - La sezione "config" mostra tutte le variabili e costanti della request

![La sezione "config" mostra tutte le variabili e costanti della request](http://www.symfony-project.org/images/book/1_4/F1603.png "La sezione config mostra tutte le variabili e costanti della request")

  * Quando la cache è abilitata, nella barra appare una freccia verde. Cliccare questa freccia per rieseguire la pagina, indipendentemente da cosa è memorizzato nella cache (la cache non viene cancellata).
  * Cliccare nella sezione "logs" per visualizzare i messaggi di log della richiesta corrente, come mostrato nella Figura 16-4. Secondo l'importanza degli eventi, vengono visualizzati in righe grigie, gialle o rosse. È possibile filtrare in base alla categoria gli eventi che vengono visualizzati, utilizzando i link presenti all'inizio dell'elenco.

Figura 16-4 - La sezione "logs" visualizza i messaggi di log per la richiesta corrente

![La sezione "logs" visualizza i messaggi di log per la richiesta corrente](http://www.symfony-project.org/images/book/1_4/F1604.png "La sezione logs visualizza i messaggi di log per la richiesta corrente")

>**NOTE**
>Quando l'azione corrente proviene da una redirezione, nel pannello "logs" sono presenti solo i log dell'utlima richiesta, quindi i file di log rimangono indispensabili per fare il debug.

  * Quando ci sono richieste di esecuzione di query SQL, appare l'icona di un database nella barra degli strumenti. Cliccare per vedere il dettaglio delle query, come mostrato nella Figura 16-5.
  * Alla destra dell'icona dell'orologio c'è il tempo totale necessario per elaborare la richiesta. Bisogna tener conto che la barra web per il debug e la modalità stessa di debug rallentano l'esecuzione della richiesta, quindi non bisogna considerare i tempi in sé, ma prestare attenzione solo alle differenze tra i tempi di esecuzione di due pagine diverse. Fare clic sull'icona dell'orologio per visualizzare i dettagli dei tempi di elaborazione categoria per categoria, come mostrato nella Figura 16-6. Symfony visualizza il tempo trascorso in diversi momenti dell'elaborazione della richiesta. Solo i tempi relativi alla richiesta corrente hanno un senso per l'ottimizzazione, quindi il tempo impiegato dal core di symfony non viene visualizzato. Ecco perché la somma di questi tempi non è uguale al tempo totale.
  * Fare clic sulla x rossa all'estremità destra della barra degli strumenti, per nascondere la barra stessa.

Figura 16-5 - La sezione query del database, mostra le query eseguite nella richiesta corrente

![La sezione query del database, mostra le query eseguite nella richiesta corrente](http://www.symfony-project.org/images/book/1_4/F1605.png "La sezione query del database, mostra le query eseguite nella richiesta corrente")

Figura 16-6 - L'icona con l'orologio mostra il tempo di esecuzione per categoria

![L'icona con l'orologio mostra il tempo di esecuzione per categoria](http://www.symfony-project.org/images/book/1_4/F1606.png "L'icona con l'orologio mostra il tempo di esecuzione per categoria")

>**SIDEBAR**
>Aggiungere un proprio contatore
>
>Symfony utilizza la classe `sfTimer` per calcolare il tempo impiegato sulla configurazione, il modello, l'azione e la vista. Utilizzando lo stesso oggetto, si può cronometrare un processo personalizzato e mostrare il risultato con gli altri contatori della barra web per il debug. Può essere molto utile quando si lavora sull'ottimizzazione delle prestazioni.
>
>Per inizializzare il cronometro su una specifica porzione di codice, chiamare il metodo `getTimer()`. Questo restituirà un oggetto sfTimer e inizierà il conteggio. Chiamare su questo oggetto il metodo `addTime()` per fermare il conteggio. Il tempo trascorso è disponibile attraverso il metodo `getElapsedTime()` e visualizzato nella barra web di debug con gli altri.
>
>     [php]
>     // Inizializza il cronometro e inizia il conteggio
>     $timer = sfTimerManager::getTimer('mioTimer');
>
>     // Eseguire il codice
>     ...
>
>     // Ferma il cronometro e aggiunge il tempo trascorso
>     $timer->addTime();
>
>     // Recupera il risultato (e ferma il cronometro se non è giù stato fermato)
>     $elapsedTime = $timer->getElapsedTime();
>
>Il vantaggio di dare un nome a ogni timer è che si può chiamare più volte per accumulare conteggi. Per esempio, se il cronometro `mioTimer` viene utilizzato in un metodo che viene chiamato due volte a ogni richiesta, la seconda chiamata al metodo `getTimer('mioTimer')` farà ripartire il cronometro dal punto calcolato quando `addTime()` era stato chiamato l'ultima volta, per cui i tempi si sommano a quelli precedenti. Chiamando `getCalls()` sull'oggetto timer si ottiene il numero di volte in cui è stato lanciato il cronometro e questo dato viene visualizzato anche nella barra web di debug.
>
>     [php]
>     // Recupera il numero di chiamate a timer
>     $nbCalls = $timer->getCalls();
>
>Nella modalità Xdebug, i messaggi di log sono più completi. Tutti i file con gli script PHP e le funzioni che sono chiamate vengono inserite nel log e symfony sa come collegare questa informazione con i suoi log interni. Ogni linea della tabella con i messaggi di log ha un pulsante a doppia freccia, su cui è possibile fare clic per visualizzare ulteriori dettagli sulla relativa richiesta. Se qualcosa va storto, la modalità Xdebug fornisce la massima quantità di dettagli per aiutare a capire le cause.

-

>**NOTE**
>La barra web di debug, nella modalità predefinita non è presente per le risposte Ajax e per i documenti che non hanno il content-type HTML. Per le altre pagine, si può disabilitare la barra manualmente dall'interno di una azione, chiamando `sfConfig::set('sf_web_debug', false)`.

### Debug manuale

È bello avere l'accesso ai messaggi di debug del framework, ma è ancora meglio essere in grado di accedere ai propri messaggi. Symfony fornisce scorciatoie, accessibili sia dalle azioni che dai modelli, per aiutare a traccaire eventi e/o valori durante l'esecuzione della richiesta.

I messaggi di log personalizzati vengono salvati nel file di log di symfony e compaiono nella barra web per il debug. (Il Listato 16-5 fornisce un esempio della sintassi per un messaggio di log personalizzato. Un messaggio personalizzato è un buon modo per controllare il valore di una variabile da un template, ad esempio. Il Listato 16-11 mostra come usare la barra web per il debug per avere il feedback da un template (da una azione invece, si può utilizzare `$this->logMessage()`).

Listato 16-11 - Inserire un messaggio nel log a scopo di debug

    [php]
    <?php use_helper('Debug') ?>
    ...
    <?php if ($problem): ?>
      <?php log_message('{sfAction} been there', 'err') ?>
      ...
    <?php endif ?>

L'utilizzo del livello `err` garantisce che l'evento sarà ben visibile nell'elenco dei messaggi, come mostrato nella Figura 16-7.

Figura 16-7 - Un messaggio di log personalizzato visualizzato nella sezione "logs" della barra web per il debug

![Un messaggio di log personalizzato visualizzato nella sezione "logs" della barra web per il debug](http://www.symfony-project.org/images/book/1_4/F1607.png "Un messaggio di log personalizzato visualizzato nella sezione logs della barra web per il debug")

Usare symfony fuori dal contesto web
------------------------------------

Si può volere eseguire uno script da riga di comando (o tramite cron) che abbia accesso a tutte le classi e le caratteristiche di symfony, ad esempio per inviare e-mail in batch e per aggiornare periodicamente il modello tramite una elaborazione intensiva. Il modo più semplice per farlo è quello di creare uno script PHP che riproduca i primi passi di un front controller, in modo che symfony possa venire correttamente inizializzato. È inoltre possibile utilizzare il sistema a riga di comando di symfony, per trarre vantaggio del parse degli argomenti e dell'inizializzazione automatizzata del database.

### File batch

L'inizializzazione di symfony richiede solo un paio di righe di codice PHP. È possibile usufruire di tutte le funzionalità di symfony creando un file PHP, per esempio sotto la cartella `lib/` del progetto, scrivendo le linee mostrate nel Listato 16-12.

Listato 16-12 - Esempio di script batch, in `lib/mioScript.php`

    [php]
    <?php

    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);

    // Remove the following lines if you don't use the database layer
    $databaseManager = new sfDatabaseManager($configuration);

    // aggiungere qua il codice

Assomiglia molto alle prime righe di un front controller (vedere il capitolo 6), perché queste linee fanno la stessa cosa: inizializzano symfony, analizzano la configurazione del progetto e dell'applicazione. Notare che il metodo `ProjectConfiguration::getApplicationConfiguration` richiede tre parametri:

 * un nome di applicazione
 * un nome di ambiente
 * un booleano, per indicare se le funzionalità di debug devono essere abilitate o no

Per eseguire il codice, basta chiamare lo script dalla riga di comando:

    $ php lib/mioScript.php

### Task personalizzati

Un modo alternativo per creare script personalizzati a riga di comando è scrivere un **task symfony**. Proprio come i task `cache:clear` e `propel:build-model`, è possibile lanciare i propri task personalizzati dalla linea di comando con `php symfony`. I task personalizzati traggono vantaggio della capacità di analizzare argomenti e opzioni della linea di comando, possono incorporare i promi messaggi di aiuto e possono estendere task esistenti.

Un task personalizzato è solo una classe che estende `sfBaseTask` situata in una cartella `lib/task/`, o sotto la root del progetto, o in una cartella di un plugin. Il nome del file deve terminare con 'Task.class.php'. Il Listato 16-13 mostra un esempio di task presonalizzato.

Listato 16-13 - Esempio di task, in `lib/task/testCiaoTask.class.php`

    [php]
    <?php

    class testCiaoTask extends sfBaseTask
    {
      protected function configure()
      {
        $this->namespace = 'test';
        $this->name = 'ciao';
        $this->briefDescription = 'Dice ciao';
      }

      protected function execute($arguments = array(), $options = array())
      {
        // scrivere qua il codice
        $this->log('Ciao, mondo!');
      }
    }

Il codice scritto nel metodo `execute` ha accesso a tutte le librerie di symfony, proprio come nel precedente script batch. La differenza è come viene chiamato il task personalizzato:

    $ php symfony test:ciao

Il nome del task proviene dalle proprietà protette `namespace` e `name` (non dal nome della classe e neanche dal nome del file). E dal momento che il task è integrato nella riga di comando di symfony, compare nell'elenco dei task quando si digita:

    $ php symfony

Invece che scrivere manualmente lo scheletro del task, si può usare il task symfony `generate:task`. Questo cera un task vuoto e ha molte opzioni di personalizzazione. Si possono vedere queste opzioni emdiante:

    $ php symfony help generate:task

I task possono accettare argomenti (parametri obbligatori, in un ordine predefinito) e opzioni (parametri opzionali non ordinati). Il Listato 16-14 mostra un task più completo, che sfrutta tutte queste caratteristiche.

Listato 16-14 - Un esempio di task più completo, in `lib/task/mioSecondoTask.class.php`

    [php]
    class mioSecondoTask extends sfBaseTask
    {
      protected function configure()
      {
        $this->namespace        = 'foo';
        $this->name             = 'mioSecondoTask';
        $this->briefDescription = 'Fa alcune cose con stile';
        $this->detailedDescription = <<<EOF
    Il task [foo:mioSecondoTask|INFO] gestisce il processo della realizzazione di qualche compito.
    Chiamarlo con:

      [php symfony foo:mioSecondoTask frontend|INFO]

    Si può abilitare l'output dettagliato utilizzando l'opzione [verbose|COMMENT]:

      [php symfony foo:mioSecondoTask frontend --verbose=on|INFO]
    EOF;
        $this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
        $this->addOption('verbose', null, sfCommandOption::PARAMETER_REQUIRED, 'Enables verbose output', false);
      }

      protected function execute($arguments = array(), $options = array())
      {
        // aggiungere qua il codice
        
      }
    }

>**NOTE**
>Se il task ha bisogno di accedere al livello del database, deve estendere `sfPropelBaseTask` invece di `sfBaseTask`. L'inizializzazione del task si prenderà cura di caricare le classi di Propel. Si può aprire una connessione al database nel metodo `execute()` chiamando:
>
>    $databaseManager = new sfDatabaseManager($this->configuration);
>
>Se la configurazione del task definisce un argomento `application` e `env`, questi vengono automaticamente considerati quando viene creata la configurazione del task, quindi un task può utilizzare una qualunque delle connessioni a database definite nel file `databases.yml`. Per impostazione predefinita, gli scheletri generati dalla chiamata a `generate:task` comprendono questa inizializzazione.

Per ulteriori esempi sulle capacità del sistema dei task, si possono guaradre i sorgenti dei task esistenti di symfony.

Popolare un database
--------------------

Nel processo di sviluppo delle applicazioni, gli sviluppatori devono spesso affrontare il problema di popolare il database. Esistono alcune soluzioni specifiche per alcuni sistemi di database, ma nessuno può essere utilizzato sopra la nostra mappatura oggetti-relazioni. Grazie a YAML e all'oggetto `sfPropelData`, symfony può trasferire automaticamente i dati da una sorgente di semplice testo a un database. Sebbene la scrittura di un file di testo come sorgente per i dati possa sembrare che necessiti di più lavoro che inserire i record a mano con un'interfaccia CRUD, essa farà risparmiare tempo nel lungo periodo. Questa caratteristica verrà molto utile per archiviare automaticamente e inserire i dati di test per l'applicazione.

### Sintassi del file per le fixture

Symfony è in grado di leggere file di dati che seguono una sintassi molto semplice chiamata YAML, a condizione che si trovino sotto la cartella `data/fixtures/`. I file con le fixture sono organizzati per classi e ogni sezione di una classe inizia con il nome della classe come intestazione. Per ciascuna classe, i record etichettati con una stringa univoca sono definiti da una serie di coppie `nomecampo: valore`.
Il Listato 16-15 mostra un esempio di un file di dati per popolare il database.

Listato 16-15 - Esempio di un file fixture, in `data/fixtures/import_data.yml`

    Article:                             ## Inserisce i record nella tabella blog_article
      first_post:                        ## Etichetta del primo record
        title:       I miei primi ricordi
        content: |
          Per un lungo periodo sono andato a dormire presto. Qualche volta, quando avevo
          la mia candela, i miei occhi si chiudevano così in fretta che non avevo nemmeno il tempo
          di dire "Vado a dormire".

      second_post:                       ## Etichetta del secondo record
        title:       Le cose peggiorarono
        content: |
          A volte sperava che fosse morta, senza dolore, in un qualche incidente,
          lei che era fuori di casa per le strade, attraversando vie occupate, attraversando vie trafficate,
          dalla mattina alla sera.

Symfony traduce le chiavi delle colonne in metodi setter utilizzando un convertitore camelCase (`setTitle()`, `setContent()`). Ciò significa che è possibile definire una chiave `password`, anche se la tabella attuale non dispone di un campo `password`; basta definire un metodo `setPassword()` nell'oggetto `User` e si possono popolare le altre colonne con un algoritmo basato sulla password (ad esempio, una versione hash della password). 
		  
La colonna della chiave primaria non ha bisogno di essere definita. Poiché si tratta di un campo a incremento automatico, il livello del database sa come calcolarlo. 

Anche le colonne `created_at` non devono essere impostate, perché symfony sa che i campi chiamati in questo modo, quando vengono creati devono essere impostati all'ora corrente del sistema.



### Avviare l'importazione

Il task `propel:data-load` importa i dati dai file YAML al database. Le impostazioni di connessione provengono dal file `databases.yml` e quindi hanno bisogno di un nome di applicazione per l'esecuzione. Opzionalmente, è possibile specificare un nome di ambiente con l'aggiunta di una opzione `--env` (`dev` per impostazione predefinita).

    $ php symfony propel:data-load --env=prod --application=frontend

Questo comando legge tutti i file YAML con le fixture dalla cartella `data/fixtures/` e inserisce i record nel database. Per impostazione predefinita, sostituisce il contenuto esistente del database, ma se si aggiunge l'opzione `--append`, il comando non cancellerà i dati già presenti.

    $ php symfony propel:data-load --append --application=frontend

Nella chiamata è possibile specificare un'altra cartella per le fixture. In questo caso, aggiungere un percorso relativo alla cartella del progetto dove sono state inserite.

    $ php symfony propel:data-load --application=frontend data/miefixture

### Utilizzo di tabelle collegate

Si è imparato come aggiungere record a una singola tabella, ma come si fa ad aggiungere record con chiavi esterne a un'altra tabella? Dal momento che la chiave primaria non è inclusa nei dati delle fixture, c'è bisogno di un modo alternativo per relazionare dei record con altri.

Torniamo all'esempio del capitolo 8, dove la tabella `blog_article` è collegata alla tabella `blog_comment`, come mostrato in Figura 16-8.

Figura 16-8 - Esempio di un modello relazionale di database

![Esempio di un modello relazionale di database](http://www.symfony-project.org/images/book/1_4/F1608.png "Esempio di un modello relazionale di database")

Questo è un esempio di come le etichette date ai record siano effettivamente utili. Per aggiungere un campo `Comment` all'articolo `first_post`, si possono semplicemente aggiungere le righe mostrate nel Listato 16-16 al file di dati `import_data.yml`.

Listing 16-16 - Aggiungere un record a una tabella collegata, in `data/fixtures/import_data.yml`

    Comment:
      first_comment:
        article_id:   first_post
        author:       Anonymous
        content:      La tua prosa è troppo prolissa. Scrivere frasi più brevi.

Il task `propel:data-load` riconosce l'etichetta che è stata data precedentemente a un articolo nel file `import_data.yml` e prende la chiave primaria del corrispondente record di `Article` per impostare il campo `article_id`. Non si vedranno mai gli ID dei record, è sufficiente collegarli alle loro etichette.
		
L'unico vincolo per i record collegati è che gli oggetti chiamati in una chiave esterna devono essere definiti in precedenza nel file; il che è come si farebbe se si definissero uno per uno. I file di dati vengono analizzati dall'alto verso il basso e l'ordine in cui sono scritti i record è importante.

Questo metodo funziona anche per le relazioni molti a molti, dove due classi sono collegate attraverso una terza classe. Ad esempio, un `Article` può avere molti `Authors` e un `Author` può avere molti `Articles`. Di solito per fare questo si utilizza la classe `ArticleAuthor`, corrispondente a una tabella `article_author` con una colonna `article_id` e una colonna `author_id`.
Il Listato 16-17 mostra come scrivere un file fixture per definire relazioni molti a molti con questo modello. Notare il nome della tabella in plurale che è stato usato qui. Questo è ciò che fa scattare la voglia di avere una classe intermedia.

Listato 16-17 - Aggiungere un record a una tabella collegata con una relazione molti a molti, in `data/fixtures/import_data.yml`

    Author:
      first_author:
        name: John Doe
        article_authors: [first_post, second_post]

Un file di dati può contenere dichiarazioni di più classi. Ma quando si ha la necessità di inserire molti dati per numerose tabelle, il file con le fixture potrebbe divenare troppo grosso per essere gestito con facilità.
		
Il task `propel:data-load` analizza tutti i file che vengono trovati nella cartella `fixtures/` , quindi nessuno impedisce di suddividere un file YAML con le fixture in file più piccoli. La cosa importante da ricordare è che le le chiavi esterne impongono un ordine di elaborazione per le tabelle. Per essere sicuri che vengano analizzati nel giusto ordine, prefissare i nomi dei file con un numero ordinale.

    100_article_import_data.yml
    200_comment_import_data.yml
    300_rating_import_data.yml

>**NOTE**
>Doctrine non ha bisogno di nomi di file specifici perché si prende cura automaticamente di eseguire i comandi SQL nel giusto ordine.

Il deploy delle applicazioni
----------------------------

Symfony offre comandi manuali per sincronizzare due versioni di un sito web. Questi comandi sono per lo più utilizzati per caricare un sito web da un server di sviluppo a un host finale connesso ad Internet.



### L'utilizzo di `rsync` per il trasferimento incrementale di file

L'invio della cartella principale di un progetto tramite FTP va bene per il primo trasferimento, ma quando si ha bisogno di caricare un aggiornamento dell'applicazione, in cui sono cambiati solo alcuni file, l'FTP non è l'ideale. È necessario trasferire nuovamente l'intero progetto, il che è uno spreco di tempo e di larghezza di banda. In alternativa si può passare alla cartella in cui si sa che alcuni file sono stati modificati e trasferire solo quelli con certe date di modifica. Questo è un lavoro che richiede tempo ed è suscettibile di errori. Inoltre, il sito web può diventare non disponibile o restituire errori durante il tempo del trasferimento. 

La soluzione supportata da symfony è la sincronizzazione rsync attraverso SSH. [Rsync] (http://samba.anu.edu.au/rsync/) è una utility a riga di comando che consente il trasferimento incrementale di file in modo veloce ed è open source. Con il trasferimento incrementale, solo i dati modificati verranno inviati. Se un file non è stato modificato, non sarà inviato all'host. Se un file è stato modificato solo in parte, sarà inviata solo la parte cambiata. Il vantaggio principale è che la sincronizzazione con rsync trasferire solo una piccola quantità di dati ed è molto veloce.

Symfony aggiunge l'SSH ad rsync per mettere in sicurezza il trasferimento dei dati. Sempre più host commerciali supportano un tunnel SSH per mettere in sicurezza l'upload dei file sui loro server e questa è una buona pratica per evitare problemi di sicurezza.

Il client SSH chiamato da symfony, utilizza le impostazioni di connessione del file `config/properties.ini`. Il Listato 16-18 fornisce un esempio di impostazioni di connessione per un server di produzione. Scrivere le impostazioni del proprio server di produzione in questo file prima di ogni sincronizzazione. È anche possibile definire una impostazione unica dei parametri per fornire dei propri parametri per la riga di comando rsync.

Listato 16-18 - Esempio di impostazioni di connessione per una concronizzazione con il server, in `mioprogetto/config/properties.ini`

    [symfony]
      name=mioprogetto

    [production]
      host=miaapp.esempio.com
      port=22
      user=mioutente
      dir=/home/mioaccount/mioprogetto/

>**NOTE**
>Non bisogna confondere il server di produzione (il server host, come definito nel file `properties.ini` del progetto) con l'ambiente di produzione (il front controller e la configurazione utilizzati in produzione, presenti nei file di configurazione di una applicazione).

Eseguireun rsync su SSH richiede diversi comandi e la sincronizzazione può necessitare di di un po' di tempo nel ciclo di vita di una applicazione. Per fortuna, symfony automatizza questo processo con un unico comando:

    $ php symfony project:deploy production

Questo comando lancia il comando `rsync` in modalità dry; questo vuol dire che mostra i file che devono essere sincronizzati, senza sincronizzarli realmente. Se si desidera effettuare la sincronizzazione, è necessario richiederlo esplicitamente con l'aggiunta dell'opzione `--go`.

    $ php symfony project:deploy production --go

Non dimenticarsi di cancellare la cache nel server di produzione dopo la sincronizzazione.

>**TIP**
>Prima di distribuire l'applicazione nel server di produzione, è meglio controllare la configurazione con `check_configuration.php`. Questa utility si trova nella cartella `data/bin` di symfony. Controlla l'ambiente rispetto ai requisiti di symfony. Lo si può lanciare ovunque:
>
>     [php]
>     $ php /percorso/di/symfony/data/bin/check_configuration.php
>
>Anche se si può utilizzare questa utility da riga di comando, è fortemente raccomandato di lanciarla da web, copiandola nella radice della cartella web, perché PHP può utilizzare differenti file di configurazione `php.ini` per l'interfaccia a riga di comando e il web.

-

>**SIDEBAR**
>L'applicazione è terminata?
>
>Prima di inviare l'applicazione in produzione, bisogna essere sicuri che sia pronta per un utilizzo pubblico. Verificare i punti seguenti prima di decidere di distribuire l'aplicazione:
>
>Le pagine degli errori devono essere personalizzate in base al look and feel dell'applicazione. Fare riferimento al capitolo 19 per vedere come personalizzare l'errore 500, l'errore 404, le pagine messe in sicurezza e alla sezione "Gestire un'applicazione in produzione" di questo capitolo per vedere come personalizzare le pagine visualizzate quando il sito non è disponibile.
>
>Il modulo `default` dovrebbe essere rimosso dall'array `enabled_modules` presente in `settings.yml`, in modo che nessuna pagina di symfony possa comparire per errore.
>
>Il meccanismo di gestione della sessione utilizza un cookie lato client e questo cookie in modalità predefinita si chiama `symfony`. Prima di distribuire l'applicazione, si dovrebbe rinominarlo, in modo da evitare la divulgazione del fatto che l'applicazione utilizza symfony. Fare riferimento al capitolo 6 per vedere come personalizzare il nome del cookie nel file `factories.yml`.
>
> Il file `robots.txt` che si trova nella cartella `web/` del progetto, in modalità predefinita è vuoto. Si consiglia di personalizzarlo per informare spider web e robot web su quali parti del sito web possono navigare e quali dovrebbero ignorare. Il più delle volte, questo file viene utilizzato per escludere alcuni URL dall'indicizzazione: per esempio le pagine di risorse che non hanno bisogno di indicizzazione (come l'archivio dei bug), o gli infiniti URL in cui i robot potrebbero rimanere bloccati. 
>
>I browser moderni cercano un file `favicon.ico` quando un utente visualizza per la prima volta l'applicazione, in modo da rappresentare l'applicazione con un'icona nella barra degli indirizzi e nella cartella con i segnalibri. Aggiungendo questo file non solo renderà l'applicazione più carina, ma eviterà anche la comparsa di numerosi errori 404 nei log del server.

### Ignorare i file irrilevanti

Quando si sincronizza il progetto symfony con un host di produzione, alcuni file e cartelle non dovrebbero essere trasferiti: 

  * Tutte le cartelle del controllo di versione (`.svn/`, `CVS/` e così via) e il loro contenuto, sono necessari solo per lo sviluppo.
  * Il front controller dell'ambiente di sviluppo non deve essere disponibile all'utente finale. Gli strumenti per il debug e i log disponibili quando si usa l'applicazione attraverso questo front controller rallentano l'applicazione e forniscono informazioni sulle variabili delle azioni. È qualcosa da tenere lontano dal pubblico dominio.
  * Le cartelle `cache/` e `log/` del progetto nel server host non devono essere cancellate ogni volta che si fa una sincronizzazione. Queste cartelle devono essere ignorate. Se si ha una cartella `stats/`, anche questa probabilmente dovrebbe essere trattata allo stesso modo.
  * I file caricati  dagli utenti non dovrebbero essere trasferiti. Una delle buone pratiche per i progetti symfony è di memorizzare i file caricati nella cartella `web/uploads/`. Questo permette di escludere tutti questi file dalla sincronizzazione indicando una sola cartella.

Per escludere dei file dalla sincronizzazione con rsync, aprire e modificare il file `rsync_exclude.txt` presente nella cartella `mioprogetto/config/`. Ogni linea può contenere un file, una cartella o un pattern. La struttura dei file di symfony è organizzata logicamente e progettata per ridurre al minimo il numero di file o di cartelle da escludere manualmente dalla sincronizzazione. Vedere il Listato 16-19 per un esempio.

Listato 16-19 - Esempio di configurazione di esclusione file con rsync, in `mioprogetto/config/rsync_exclude.txt`

    # Project files
    /cache/*
    /log/*
    /web/*_dev.php
    /web/uploads/*

    # SCM files
    .arch-params
    .bzr
    _darcs
    .git
    .hg
    .monotone
    .svn
    CVS

>**NOTE**
>Le cartelle `cache/` e `log/` non dovrebbero essere sincronizzate rispetto al server di svuluppo, ma devono comunque esistere nel server in produzione. Quindi se non sono presenti nel progetto, bisogna crearle a mano dentro a `mioprogetto/`.

### Gestire una applicazione in produzione

Il comando che è usato più spesso nei server in produzione è `cache:clear`. Bisogna lanciarlo ogni volta che si aggiorna symfony o il progetto (ad esempio, dopo aver chiamato i ltask `project:deploy`) e ogni volta che si fanno dei cambiamenti nella configurazione in produzione.

    $ php symfony cache:clear

>**TIP**
>Se l'interfaccia a riga di comando non è disponibile nel server di produzione, si può cancellare la cache manualmente cancellando il contenuto della cartella `cache/`.

Si può disabilitare temporaneamente l'applicazione per, ad esempio, aggiornare una libreria o una grossa mole di dati.

    $ php symfony project:disable APPLICATION_NAME ENVIRONMENT_NAME

Per impostazione predefinita, una applicazione disabilitata visualizza la pagina `sfConfig::get('sf_symfony_lib_dir')/exception/data/unavailable.php`, ma se si crea un proprio file `unavailable.php` nella cartella `config/` del progetto, symfony userà quella.

Il task `project:enable` riabilita l'applicazione e cancella la cache.

    $ php symfony project:enable APPLICATION_NAME ENVIRONMENT_NAME

>**CAUTION**
>`project:disable` attualmente non ha effetto se il parametro `check_lock` non è
>impostato a `true` in settings.yml.

-

>**SIDEBAR**
>Visualizzare pagina non disponibile quando si cancella la cache
>
>Se si imposta il parametro `check_lock` a `true` nel file `settings.yml`, symfony bloccherà l'applicazione quando la cache viene cancellata e tutte le richieste che arrivano prima che la cache abbia finito di essere cancellata vengono redirette a una pagina che dice che l'applicazione è temporaneamente non disponibile. Se la cache è grossa, il ritardo relativo alla cancellazione può essere più lungo di alcuni millisecondi e se il traffico del sito è alto, questa è una impostazione consigliata. La pagina di non disponibilità è la stessa di quella visualizzata quando si chiama il task symfony `disable`. Il parametro `check_lock` è disattivato per impostazione predefinita perché ha un impatto negativo (anche se lieve) sulle prestazioni.
>
>Il task `project:clear-controllers` cancella la cartella `web/` di tutti i controllori diversi da quelli che devno girare in un ambiente in produzione. Se non si aggiunge il front controller di sviluppo nel file `rsync_exclude.txt`, questo comando garantisce che una backdoor non riveli caratteristiche interne dell'applicazione.
>
>     $ php symfony project:clear-controllers
>
>I permessi dei file e delle cartelle del progetto possono essere errati se si fa un checkout da un repository SVN. Il task `project:permissions` corregge i permessi delle cartelle, ad esempio per cambiare i permessi di `log/` e `cache/` a 0777 (queste cartelle hanno bisogno di essere scrivibili perché il framework funzioni correttamente).
>
>     $ php symfony project:permissions

Riepilogo
---------

Combinando i log di PHP con quelli di symfony, è possibile monitorare ed eseguire il debug dell'applicazione facilmente. Durante lo sviluppo, la modalità di debug, le eccezioni, e la barra web di debug aiutano a individuare i problemi. Per facilitare il debug è anche possibile inserire messaggi personalizzati nei file di log o nella barra degli strumenti.

L'interfaccia a riga di comando fornisce un gran numero di strumenti che facilitano la gestione delle applicazioni, durante le fasi di sviluppo e di produzione. Tra gli altri, i task per il popolamento dei dati e quello per la sincronizzazione fanno risparmiare molto tempo.
