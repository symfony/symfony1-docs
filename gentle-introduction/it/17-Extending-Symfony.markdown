Capitolo 17 - Estendere symfony
===============================

A volte è necessario modificare il comportamento di symfony. Può accadere di dover modificare il modo in cui una certa classe si comporta o aggiungere delle caratteristiche personalizzate e ciò avverrà inevitabilmente perché ogni cliente ha esigenze specifiche che nessun framework può prevedere. In realtà, questa situazione è così comune che symfony fornisce un meccanismo per estendere classi esistenti in fase di runtime, al di là della semplice ereditarietà delle classi. È anche possibile sostituire le classi del core di symfony modificando le impostazioni di fabbrica. Una volta si è scrita una estensione, si può facilmente pacchettizzarla come plug-in, in modo che possa essere riutilizzata in altre applicazioni, o da altri utenti di symfony.

Eventi
------

PHP non supporta l'ereditarietà multipla, il che significa che non è possibile avere una classe che estende più di una classe. Inoltre non è possibile aggiungere nuovi metodi a una classe esistente o sostituire i metodi già esistenti. Per rimediare a queste due limitazioni e per rendere il framework realmente estendibile, symfony introduce un *sistema per gli eventi* ispirato al centro notifica Cocoa e basato sul [design pattern Observer](http://it.wikipedia.org/wiki/Observer_pattern).

### Capire gli eventi

Alcune classi di symfony "notificano un evento al dispatcher" in vari momenti della loro vita. Per esempio, quando l'utente cambia la cultura, l'oggetto utente notifica che si è verificato un evento `change_culture`. È come un messaggio nello spazio del progetto che dice: "Sto facendo questo. Potete fare quello che volete a questo riguardo".

Si può decidere di fare qualcosa di speciale quando un evento viene generato. Per esempio, è possibile salvare la cultura utente in una tabella di database ogni volta che si verifica l'evento `change_culture`. Per fare ciò, è necessario *registrare un ascoltatore di eventi*, in altri termini è necessario dichiarare una funzione che verrà chiamata al verificarsi dell'evento. Il listato 17-1 mostra come registrare un ascoltatore per l'evento `change_culture` dell'utente.

Listato 17-1 - Registrare un ascoltatore di eventi

    [php]
    $dispatcher->connect('user.change_culture', 'changeUserCulture');
    
    function changeUserCulture(sfEvent $event)
    {
      $user = $event->getSubject();
      $culture = $event['culture'];

      // fa qualcosa con la cultura dell'utente
    }

Tutti gli eventi e le registrazioni degli ascoltatori sono gestiti da un oggetto speciale chiamato *dispatcher di eventi*. Questo oggetto è disponibile ovunque in symfony attraverso l'istanza di `ProjectConfiguration` e la maggior parte degli oggetti di symfony offrono un metodo `getEventDispatcher()` per accedervi direttamente. Utilizzando il metodo `connect()` del dispatcher, è possibile registrare qualsiasi callable PHP (o un metodo di classe o una funzione) da chiamare quando si verifica un evento. Il primo argomento di `connect()` è l'identificatore dell'evento, che è una stringa composta da uno spazio nomi e da un nome. Il secondo argomento è un callable PHP.

>**Note**
>Recuperare il dispatcher degli eventi da un qualsiasi punto dell'applicazione:
>
>     [PHP]
>     $dispatcher = ProjectConfiguration::getActive()->getEventDispatcher();

Una volta che la funzione è registrata con il dispatcher di eventi, aspetta fino a quando l'evento viene generato. Il dispatcher di eventi tiene un registro di tutti gli ascoltatori di eventi e sa quelli da chiamare quando si verifica un evento. Quando si chiamano questi metodi o funzioni, il dispatcher passa un oggetto `sfEvent` come parametro.

L'oggetto evento memorizza le informazioni sugli eventi notificati. L'evento notificante può essere recuperato grazie al metodo `getSubject()` e i parametri dell'evento sono accessibili mediante l'oggetto evento come array (per esempio, `$event['culture']` può essere utilizzato per recuperare il parametro `culture` passato da` sfUser` quando viene notificato `user.change_culture`).

Per concludere, il sistema degli eventi permette di aggiungere capacità a una classe esistente o modificare i suoi metodi in fase di runtime, senza usare l'ereditarietà.

### Notificare un ascoltatore di eventi

Proprio come le classi di symfony notificano gli eventi si sono verificati, le proprie classi possono offrire estensibilità runtime e notifire degli eventi in determinate occasioni. Per esempio, supponiamo che le propria applicazione richieda una serie di servizi web di terze parti e che si sia scritta una classe `sfRestRequest` per separare la logica REST di queste richieste. Una buona idea sarebbe quella di attivare un evento ogni volta che questa classe fa una nuova richiesta. Ciò renderebbe l'aggiunta di funzionalità di log o di caching più facile in futuro. Il listato 17-2 mostra il codice che è necessario aggiungere a un metodo esistente `fetch()` per realizzare la notifica a un ascoltatore di eventi.

Listato 17-2 - Notificare un ascoltatore di eventi

    [php]
    class sfRestRequest
    {
      protected $dispatcher = null;

      public function __construct(sfEventDispatcher $dispatcher)
      {
        $this->dispatcher = $dispatcher;
      }
      
      /**
       * Fa una query a un web service esterno
       */
      public function fetch($uri, $parameters = array())
      {
        // Notifica al dispatcher l'inizio del processo di fetch
        $this->dispatcher->notify(new sfEvent($this, 'rest_request.fetch_prepare', array(
          'uri'        => $uri,
          'parameters' => $parameters
        )));
        
        // Esecuzione della richiesta e memorizzazione del risultato in una variabile $result
        // ...
        
        // Notica al dispatcher la fine del processo di fetch
        $this->dispatcher->notify(new sfEvent($this, 'rest_request.fetch_success', array(
          'uri'        => $uri,
          'parameters' => $parameters,
          'result'     => $result
        )));
        
        return $result;
      }
    }

Il metodo `notify()` del dispatcher di eventi si aspetta un oggetto `sfEvent` come argomento; è l'oggetto stesso che viene passato agli ascoltatori di eventi. Questo oggetto porta sempre un riferimento al notificatore (è per questo che l'istanza dell'evento viene inizializzata con `this`) e un identificativo di evento. Facoltativamente, accetta un array associativo di parametri, dando agli ascoltatori un modo per interagire con la logica del notificante.

### Notifica di un evento al dispatcher finché un ascoltatore lo prende in carico

Usando il metodo `notify()`, si è sicuri che tutti gli ascoltatori registrati sull'evento da notificare vengono eseguiti. Tuttavia, in alcuni casi è necessario consentire a un ascoltatore di fermare l'evento e prevenire che ulteriori ascoltatori possano fare notifiche su di esso. In questo caso, si dovrebbe usare `notifyUntil()` al posto di `notify()`. Il dispatcher eseguirà tutti gli ascoltatori fino a quando uno di questi restituisce `true` e quindi fermerà la notifica degli eventi. In altre parole, `notifyUntil()` è come un messaggio nello spazio del progetto che dice: "Sto facendo questo. Se qualcuno se ne occupa, allora non lo dirò a nessun altro". Il listato 17-3 mostra come usare questa tecnica in combinazione con un metodo magico `__call()` per aggiungere metodi a una classe esistente in fase di runtime.

Listato 17-3 - Notifica di un evento finché l'ascoltatore restituisce true

    [php]
    class sfRestRequest
    {
      // ...
      
      public function __call($method, $arguments)
      {
        $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'rest_request.method_not_found', array(
          'method'    => $method, 
          'arguments' => $arguments
        )));
        if (!$event->isProcessed())
        {
          throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
        }
        
        return $event->getReturnValue();
      }
    }

Un ascoltatore di eventi registrato sull'evento `rest_request.method_not_found`, può verificare la richiesta `$method` e decidere di gestirla, o passare al prossimo ascoltatore di eventi callable. Nel listato 17-4, si può vedere come una classe creata da altri può aggiungere dei metodi `put()` e `delete()` alla classe `sfRestRequest` in fase di runtime con questo trucco.

Listato 17-4 - Gestire un evento del tipo "notificare finché"

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        // Registra l'ascoltatore
        $this->dispatcher->connect('rest_request.method_not_found', array('sfRestRequestExtension', 'listenToMethodNotFound'));
      }
    }

    class sfRestRequestExtension
    {
      static public function listenToMethodNotFound(sfEvent $event)
      {
        switch ($event['method'])
        {
          case 'put':
            self::put($event->getSubject(), $event['arguments']);

            return true;
          case 'delete':
            self::delete($event->getSubject(), $event['arguments']);

            return true;
          default:
            return false;
        }
      }

      static protected function put($restRequest, $arguments)
      {
        // Fa una richiesta put e memorizza il risultato in una variabile $result
        // ...
        
        $event->setReturnValue($result);
      }
      
      static protected function delete($restRequest, $arguments)
      {
        // Fa una richiesta delete e memorizza il risultato in una variabile $result
        // ...
        
        $event->setReturnValue($result);
      }
    }

In pratica, `notifyUntil()` aggiunge funzionalità di ereditarietà multipla, o meglio mixin (l'aggiunta di metodi da parte di classi esterne rispetto a una classe esistente), al PHP. Quindi ora si possono "iniettare" nuovi metodi agli oggetti che non si possono estendere atrtaverso l'ereditarietà. E questo avviene a runtime. Non si è più limitati dalle capacità orientate agli oggetti di PHP quando si utilizza symfony.

>**TIP**
>Poiché il primo ascoltatore che cattura un evento `notifyUntil()` impedisce ulteriori notifiche, bisogna prestare attenzione all'ordine con cui vengono eseguiti gli ascoltatori. Questo ordine corrisponde all'ordine in cui gli ascoltatori sono stati registrati - il primo a essere registrato, è il primo a essere eseguito. Nella pratica, i casi in cui questo potrebbe essere un problema sono rari. Se ci si rende conto che due ascoltatori sono in conflitto su un particolare evento, forse la classe dovrebbe notificare eventi diversi, per esempio uno all'inizio e uno alla fine dell'esecuzione del metodo. E se si utilizzano gli eventi per aggiungere nuovi metodi a una classe esistente, è meglio dare un nome appropriato a questi metodi, in modo che altre volte in cui si aggiungono metodi non si creino dei conflitti. Prefissare i nomi dei metodi con il nome della classe dell'ascoltatore è una buona pratica.

### Cambiare il valore di ritorno di un metodo

Si può immaginare che un ascoltatore non solo possa usare le informazioni fornite da un evento, ma anche modificarle, alterando la logica originaria del notificante. Se si desidera consentire questo, si dovrebbe utilizzare il metodo `filter()` del dispatcher di eventi, piuttosto che `notify()`. Tutti gli ascoltatori di eventi sono chiamati con due parametri: l'oggetto dell'evento e il valore da filtrare. Gli ascoltatori di eventi devono restituire il valore, indipendentemente dal fatto che sia stato modificato o meno. Il listato 17-5 mostra come `filter()` può essere usato per filtrare una risposta da un servizio Web e fare l'escape dei caratteri speciali nella risposta.

Listato 17-5 - Notifica e gestione di un evento filtro

    [php]
    class sfRestRequest
    {
      // ...
      
      /**
       * Esegue una query a un servizio web esterno
       */
      public function fetch($uri, $parameters = array())
      {
        // Esegue la richiesta e memorizza il risultato in una variabile $result
        // ...
        
        // Notifica la fine del processo fetch
        return $this->dispatcher->filter(new sfEvent($this, 'rest_request.filter_result', array(
          'uri'        => $uri,
          'parameters' => $parameters,
        )), $result)->getReturnValue();
      }
    }

    // Esegue l'escape alla risposta del servizio web
    $dispatcher->connect('rest_request.filter_result', 'rest_htmlspecialchars');

    function rest_htmlspecialchars(sfEvent $event, $result)
    {
      return htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
    }

### Eventi predefiniti

Molte classi di symfony hanno eventi predefiniti, che permettono si estendere il framework senza modificare necessariamente le classi stesse. La Tabella 17-1 elenca questi eventi, insieme ai loro tipi e argomenti.

Tabella 17-1 - Gli eventi di symfony

| **Nome dell'evento** (**Tipo**)                | **Notificatori**              | **Argomenti**               |
| ---------------------------------------------- | ----------------------------- | --------------------------- |
| application.log (notify)                       | numerose classi               | priority                    |
| application.throw_exception (notifyUntil)      | sfException                   | -                           |
| autoload.filter_config (filter)                | sfAutoloadConfigHandler       | -                           |
| command.log (notify)                           | sfCommand* classes            | priority                    |
| command.pre_command (notifyUntil)              | sfTask                        | arguments, options          |
| command.post_command (notify)                  | sfTask                        | -                           |
| command.filter_options (filter)                | sfTask                        | command_manager             |
| configuration.method_not_found (notifyUntil)   | sfProjectConfiguration        | method, arguments           |
| component.method_not_found (notifyUntil)       | sfComponent                   | method, arguments           |
| context.load_factories (notify)                | sfContext                     | -                           |
| context.method_not_found (notifyUntil)         | sfContext                     | method, arguments           |
| controller.change_action (notify)              | sfController                  | module, action              |
| controller.method_not_found (notifyUntil)      | sfController                  | method, arguments           |
| controller.page_not_found (notify)             | sfController                  | module, action              |
| debug.web.load_panels (notify)                 | sfWebDebug                    | -                           |
| debug.web.view.filter_parameter_html (filter)  | sfWebDebugPanelView           | parameter                   |
| doctrine.configure (notify)                    | sfDoctrinePluginConfiguration | -                           |
| doctrine.filter_model_builder_options (filter) | sfDoctrinePluginConfiguration | -                           |
| doctrine.filter_cli_config (filter)            | sfDoctrinePluginConfiguration | -                           |
| doctrine.configure_connection (notify)         | Doctrine_Manager              | connection, database        |
| doctrine.admin.delete_object (notify)          | -                             | object                      |
| doctrine.admin.save_object (notify)            | -                             | object                      |
| doctrine.admin.build_query (filter)            | -                             |                             |
| doctrine.admin.pre_execute (notify)            | -                             | configuration               |
| form.post_configure (notify)                   | sfFormSymfony                 | -                           |
| form.filter_values (filter)                    | sfFormSymfony                 | -                           |
| form.validation_error (notify)                 | sfFormSymfony                 | error                       |
| form.method_not_found (notifyUntil)            | sfFormSymfony                 | method, arguments           |
| mailer.configure (notify)                      | sfMailer                      | -                           |
| plugin.pre_install (notify)                    | sfPluginManager               | channel, plugin, is_package |
| plugin.post_install (notify)                   | sfPluginManager               | channel, plugin             |
| plugin.pre_uninstall (notify)                  | sfPluginManager               | channel, plugin             |
| plugin.post_uninstall (notify)                 | sfPluginManager               | channel, plugin             |
| propel.configure (notify)                      | sfPropelPluginConfiguration   | -                           |
| propel.filter_phing_args (filter)              | sfPropelBaseTask              | -                           |
| propel.filter_connection_config (filter)       | sfPropelDatabase              | name, database              |
| propel.admin.delete_object (notify)            | -                             | object                      |
| propel.admin.save_object (notify)              | -                             | object                      |
| propel.admin.build_criteria (filter)           | -                             |                             |
| propel.admin.pre_execute (notify)              | -                             | configuration               |
| request.filter_parameters (filter)             | sfWebRequest                  | path_info                   |
| request.method_not_found (notifyUntil)         | sfRequest                     | method, arguments           |
| response.method_not_found (notifyUntil)        | sfResponse                    | method, arguments           |
| response.filter_content (filter)               | sfResponse, sfException       | -                           |
| routing.load_configuration (notify)            | sfRouting                     | -                           |
| task.cache.clear (notifyUntil)                 | sfCacheClearTask              | app, type, env              |
| task.test.filter_test_files (filter)           | sfTestBaseTask                | arguments, options          |
| template.filter_parameters (filter)            | sfViewParameterHolder         | -                           |
| user.change_culture (notify)                   | sfUser                        | culture                     |
| user.method_not_found (notifyUntil)            | sfUser                        | method, arguments           |
| user.change_authentication (notify)            | sfBasicSecurityUser           | authenticated               |
| view.configure_format (notify)                 | sfView                        | format, response, request   |
| view.method_not_found (notifyUntil)            | sfView                        | method, arguments           |
| view.cache.filter_content (filter)             | sfViewCacheManager            | response, uri, new          |

Si è liberi di registrare ascoltatori di eventi su uno qualunque di questi eventi. Basta fare in modo che il callable dell'ascoltatore restituisca un booleano quando è registrato su un evento di tipo `notifyUntil` e che restituisca il valore filtrato quando è registrato su un evento di tipo `filter`.

Si noti che gli spazi dei nomi dell'evento non corrispondono necessariamente al ruolo della classe. Per esempio, tutte le classi di symfony notificano un evento `application.log` quando hanno bisogno di fare apparire qualcosa nei file di log (e nella barra web di debug):

    [php]
    $dispatcher->notify(new sfEvent($this, 'application.log', array($message)));

Le classi create dallo sviluppatore possono fare lo stesso e notificare anche eventi symfony quando ha senso farlo.

### Dove registrare gli ascoltatori?

Gli ascoltatori di eventi hanno bisogno di essere registrati all'inizio della vita di una richiesta di symfony. In pratica, il posto giusto per registrare gli ascoltatori di eventi è nella classe di configurazione dell'applicazione. Questa classe ha un riferimento al dispatcher di eventi che è possibile utilizzare nel metodo `configure()`. Il listato 17-6 mostra come registrare un ascoltatore su uno degli eventi `rest_request` degli esempi di cui sopra.

Listato 17-6 - Registrare un ascoltatore nella classe di configurazione  dell'applicazione, in `apps/frontend/config/ApplicationConfiguration.class.php`

    [php]
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('rest_request.method_not_found', array('sfRestRequestExtension', 'listenToMethodNotFound'));
      }
    }

I plugin (vedere sotto) possono registrare i propri ascoltatori di eventi. Dovrebbero farlo nello script `config/config.php` del plugin, che viene eseguito durante l'inizializzazione dell'applicazione e offre l'accesso al dispatcher di eventi attraverso `$this->dispatcher`.

Factory
-------

Factory è la definizione di una classe per un determinato compito. Symfony per le sue caratteristiche del core, come la gestione del controllore e della sessione, si basa su factory. Ad esempio, quando il framework deve creare un oggetto per un nuova richiesta, cerca nella definizione del factory il nome della classe da utilizzare a tale scopo. La definizione predefinita del factory per le richieste è `sfWebRequest`, quindi symfony crea un oggetto di questa classe, al fine di gestire le richieste. Il grande vantaggio di utilizzare una definizione del factory è che è molto facile alterare le caratteristiche core del framework: basta cambiare la definizione del factory e symfony userà la classe personalizzata per la richiesta, invece della sua.

Le definizioni dei factory sono memorizzate nel file di configurazione `factories.yml`. Il listato 17-7 mostra il file con le definizioni predefinite dei factory. Ogni definizione è costituita dal nome di una classe autocaricata e (opzionalmente) da un insieme di parametri. Per esempio, il factory per la memorizzazione delle sessioni (impostata sotto la chiave `storage:`), utilizza un parametro `session_name` per dare un nome al cookie creato sul computer client, che consente le sessioni persistenti.

Listato 17-7 - File predefinito per i factory, in `frontend/config/factories.yml`

    [yml]
    -
    prod:
      logger:
        class:   sfNoLogger
        param:
          level:   err
          loggers: ~

    test:
      storage:
        class: sfSessionTestStorage
        param:
          session_path: %SF_TEST_CACHE_DIR%/sessions

      response:
        class: sfWebResponse
        param:
          send_http_headers: false

      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true

      view_cache_manager:
        class: sfViewCacheManager
        param:
          cache_key_use_vary_headers: true
          cache_key_use_host_name:    true

Il modo migliore per cambiare un factory è quello di creare una nuova classe che eredita dal factory predefinito e aggiungervi nuovi metodi. Per esempio, il factory della sessione utente è impostato con la classe `myUser` (che si trova in `frontend/lib/`) ed eredita da `sfUser`. Utilizzare lo stesso meccanismo per trarre beneficio dai factory esistenti. Il listato 17-8 mostra un esempio di un nuovo factory per l'oggetto request.

Listato 17-8 - Sovrascrivere i factory

    [php]
    // Creare un file myRequest.class.php in una cartella con autocaricamento,
    // ad esempio in frontend/lib/
    <?php

    class myRequest extends sfRequest
    {
      // Codice qui
    }

    // Dechiarare questa classe come factory per la request in factories.yml
    all:
      request:
        class: myRequest

Plugin
------

Probabilmente capiterà di dover riutilizzare parte di codice che si è sviluppato in una delle proprie applicazioni symfony. Se questo pezzo di codice si può includere in una sola classe, nessun problema: si sposta la classe in una delle cartelle `lib/` di un'altra applicazione e l'autocaricamente si occuperà del resto. Ma se il codice è distribuito su più di un file, ad esempio un tema completamente nuovo per il generatore di amministrazione o un insieme di file JavaScript e di helper per gestire un qualche effetto grafico, la semplice copia dei file non è la soluzione migliore.

I plugin offrono un modo per pacchettizzare il codice sparso su più file e di riutilizzarlo su diversi progetti. In un plugin, è possibile inserire classi, filtri, ascoltatori di eventi, helper, configurazioni, task, moduli, schemi ed estensioni del modello, fixture, le risorse web, ecc. I plugin sono facili da installare, aggiornare e disinstallare. Possono essere distribuiti come un archivio .tgz`, un pacchetto PEAR, o un semplice checkout da un repository di codice. I plugin con pacchetti PEAR hanno il vantaggio di gestire le dipendenze, essere più facili da aggiornare e sono rilevati automaticamente. I meccanismi di caricamento di symfony tengono in considerazione i plugin e le funzionalità offerte da un plugin sono disponibili nel progetto come se il codice del plugin facesse parte del framework.

Quindi, fondamentalmente, un plugin è una estensione pacchettizzata per un progetto symfony. Con i plugin, non solo è possibile riutilizzare il proprio codice tra le applicazioni, ma si possono anche riutilizzare sviluppi fatti da altri contributori e aggiungere estensioni di terze parti al core di symfony.

### Cercare i plugin di symfony

Il sito web del progetto symfony ha una sezione dedicata ai plugin che è accessibile dal seguente URL:

    http://www.symfony-project.org/plugins/

Ciascun plugin elencato ha la propria pagina, con dettagliate istruzioni per l'installazione e documentazione sull'utilizzo.

Alcuni di questi plugin sono stati creati dalla comunità, mentre altri provengono dagli sviluppatori del core di symfony. Tra questi ultimi, ci sono i seguenti:

  * `sfFeed2Plugin`: Automatizza la manipolazione dei feed RSS e Atom
  * `sfThumbnailPlugin`: Crea miniature, ad esempio per le immagini caricate da web
  * `sfMediaLibraryPlugin`: Permette il caricamento e la gestione di file e include una estensione per un editor di testo avanzato che permette la creazione di immagini all'interno di un testo con formattazione grafica
  * `sfGuardPlugin`: Fornisce funzionalità per la gestione degli utenti, come autenticazione, autorizzazione di accesso e altre che si collocano sopra le funzionalità di sicurezza predefinite di symfony
  * `sfSuperCachePlugin`: Scrive le pagine della cartella cache, sotto la cartella radice principale per il web, per consentire al server web di accedervi il più velocemente possibile
  * `sfErrorLoggerPlugin`: Salva nel database ogni errore 404 e 500 e fornisce un modulo amministrativo per visualizzare questi errori
  * `sfSslRequirementPlugin`: Fornisce il supporto di crittografia SSL per le azioni

Si dovrebbe controllare regolarmente la sezione con i plugin di symfony, perché nuovi plugin vengono aggiunti di continuo e permettono di velocizzare molti aspetti nella programmazione di applicazioni web.

A parte la sezione dei plugin di symfony, altri modi per distribuire i plugin sono: proporre un file plugin per il download, farlo ospitare in un canale PEAR, o tenerlo in un repository pubblico per il controllo di versione.

### Installare un plugin

Le modalità di installazione di un plugin dipendono da come è stato pacchettizzato. Fare sempre riferimento al file README incluso e/o alle istruzioni di installazionepresenti sulla pagina di download del plugin.

I plugin sono applicazioni installate su un singolo progetto. Tutti i metodi descritti nelle sezioni seguenti hanno come esito quello dimettere tutti i file di un plugin in una cartella `mioprogetto/plugins/nomePlugin/`.

#### Plugin PEAR

I plugin elencati nella sezione plugin di symfony possono essere creati come pacchetti PEAR e resi disponibili attraverso il canale ufficiale PEAR per i plugin di symfony:
`plugins.symfony-project.org`. Per installare un plugin, usare il task `plugin:install` con il nome di un plugin, come mostrato nel listato 17-9.

Listato 17-9 - Installare un plugin dal canale ufficiale PEAR dei plugin di symfony

    $ cd mioprogetto
    $ php symfony plugin:install nomePlugin

In alternativa, è possibile scaricare il plugin e installarlo dal disco. In questo caso, utilizzare il percorso dell''archivio con il pacchetto, come mostrato nel listato 17-10.

Listato 17-10 - Installare un plugin da un pacchetto PEAR scaricato

    $ cd mioprogetto
    $ php symfony plugin:install /home/path/to/downloads/nomePlugin.tgz

Alcuni plugin sono ospitati su canali PEAR esterni. Installarli con il task `plugin:install` e non dimenticarsi di registrare il canale e indicare il nome del canale, come mostrato nel listato 17-11.

Listato 17-11 - Installare un plugin da un canale PEAR

    $ cd mioprogetto
    $ php symfony plugin:add-channel channel.symfony.pear.example.com
    $ php symfony plugin:install --channel=channel.symfony.pear.example.com nomePlugin

Tutti questi tre tipi di installazione utilizzano un pacchetto PEAR, quindi il termine "plugin PEAR" verrà usato indiscriminatamente per parlare di plugin installati da un canale PEAR per i plugin di symfony, un canale PEAR esterno, o un pacchetto PEAR scaricato.

Il task `plugin:install` ha anche alcune opzioni, come mostrato nel listato 17-12.

Listato 17-12 - Installare un plugin con alcune opzioni

    $ php symfony plugin:install --stability=beta nomePlugin
    $ php symfony plugin:install --release=1.0.3 nomePlugin
    $ php symfony plugin:install --install-deps nomePlugin

>**TIP**
>Come per ogni task di symfony, si può avere una spiegazione completa delle opzioni e degli argomenti di `plugin:install` lanciando `php symfony help plugin:install`.

#### Plugin come archivio di file

Alcuni plugin escono come semplici archivi di file. Per installarli, basta scompattare l'archivio nella cartella `plugins/` del progetto. Se il plugin contiene una sotto cartella `web/`, non dimenticarsi di lanciare il comando `plugin:publish-assets` per creare il corrispondente link simbolico sotto la cartella principale `web/` come mostrato nel listato 17-13. In ultimo cancellare la cache.

Listato 17-13 - Installare un plugin da un archivio

    $ cd plugins
    $ tar -zxpf mioPlugin.tgz
    $ cd ..
    $ php symfony plugin:publish-assets
    $ php symfony cc

#### Installare plugin da un repositoty sotto controllo di versione

I plugin a volte hanno il loro repository per il controllo di versione del codice sorgente. Si può installarli facendo un semplice checkout nella cartella `plugins/`, ma questo può essere un problema se anche il progetto stesso e sotto controllo di versione.

In alternativa, si può dichiarare il plugin come dipendenza esterna, così che ogni aggiornamento del codice sorgente del proprio progetto, aggiorni anche il codice sorgente del plugin. Ad esempio, Subversion memorizza le dipendenze esterne  nella proprietà `svn:externals`. Quindi si può aggiungere un plugin, modificando questa proprietà e aggiornando in seguito il proprio codice sorgente, come illustra il listato 17-14.

Listato 17-14 - Installare un plugin da un repository per il versionamento del codice sorgente

    $ cd mioprogetto
    $ svn propedit svn:externals plugins
      nomePlugin   http://svn.example.com/nomePlugin/trunk
    $ svn up
    $ php symfony plugin:publish-assets
    $ php symfony cc

>**NOTE**
>Se il plugin contiene una cartella `web/`, deve essere lanciato il comando di symfony `plugin:publish-assets` in modo da generare il corrispondente link simbolico sotto la cartella `web/` principale del progetto.

#### Attivare il modulo di un plugin

Alcuni plugin contengono interi moduli. L'unica differenza tra i moduli dei plugin e i moduli normali è che i moduli dei plugin non compaiono nella cartella `mioprogetto/apps/frontend/modules/` (per far si che siano facilmente aggiornabili). Inoltre devono essere attivati nel file `settings.yml`, come mostrato nel listato 17-15.

Listato 17-15 - Attivazione del modulo di un plugin, in `frontend/config/settings.yml`

    all:
      .settings:
        enabled_modules:  [default, sfMyPluginModule]

Questo per evitare una situazione in cui il modulo di un plugin è erroneamente reso disponibile per una applicazione che non lo richiede, che potrebbe aprire un buco nella sicurezza. Pensiamo a un plug-in che fornisce i moduli `frontend` e `backend`. Sarà necessario abilitare il modulo `frontend` solo nell'applicazione `frontend` e il modulo `backend` solo nell'applicazione `backend`. Questo è il motivo per cui i moduli dei plug-in, nella modalità predefinita non sono attivati.

>**TIP**
>Il modulo default è l'unico modulo che viene abilitato in modalità predefinita. In realtà non è un vero modulo di plugin, perché risiede nel framework, in `sfConfig::get('sf_symfony_lib_dir')/controller/default/`. È il modulo che fornisce le pagine di congratulazioni e le pagine di errore predefinite per gli errori 404 e la richiesta credenziali. Se non si desidera utilizzare le pagine predefinite di symfony, è sufficiente rimuovere il modulo dall'impostazione `enabled_modules`.

#### Visualizzare l'elenco dei plugin installati

Se uno sguardo alla cartella `plugins/` del progetto può mostrare quali plugin sono installati, il task `plugin:list` fornisce maggiori informazioni: il numero di versione e il nome del canale di ciascun plugin installato (vedere il listato 17-16).

Listato 17-16 - Visualizzare l'elenco dei plugin installati

    $ cd mioprogetto
    $ php symfony plugin:list

    Installed plugins:
    sfPrototypePlugin               1.0.0-stable # plugins.symfony-project.com (symfony)
    sfSuperCachePlugin              1.0.0-stable # plugins.symfony-project.com (symfony)
    sfThumbnail                     1.1.0-stable # plugins.symfony-project.com (symfony)

#### Aggiornare e disinstallare i plugin

Per disinstallare un plugin PEAR, chiamare il task `plugin:uninstall` dalla cartella principale del progetto, come mostrato nel listato 17-17. Bisogna prefissare il nome del plugin con il suo canale di installazione  se è diverso dal canale predefinito `symfony` (usare il task `plugin:list` per determinare questo canale).

Listato 17-17 - Disinstallare un plugin

    $ cd mioprogetto
    $ php symfony plugin:uninstall sfSuperCachePlugin
    $ php symfony cc

Per disinstallare un plugin messo tramite archivio o tramite SVN, rimuovere manualmente i file del plugin dalle cartelle `plugins/` e `web/` e cancellare la cache.
	
Per aggiornare un plugin, utilizzare il task `plugin:upgrade` (per un plugin PEAR) o fare un `svn update` (se si è preso il plugin da un repository per il controllo di versione). I plugin installati tramite archivio non sono facilmente aggiornabili.

### Anatomia di un plugin

I plugin sono scritti usando il linguaggio PHP. Se si sa come è organizzata una applicazione, è possibile comprendere la struttura dei plugin.

#### La struttura dei file di un plugin

La cartella di un plugin è organizzata più o meno come quella della cartella di un progetto. I file del plugin devono essere nelle cartelle giuste per poter essere caricati automaticamente da symfony quando necessario. Dare un'occhiata alla descrizione della struttura dei plugin nel listato 17-18.

Listato 17-18 - La strutura dei file di un plugin

    nomePlugin/
      config/
        routing.yml        // File delle rotte
        *schema.yml        // Schema della struttura dati
        *schema.xml
        config.php         // Configurazione specifica per il plugin
      data/
        generator/
          sfPropelAdmin
            */             // Temi per il generatore di amministrazione
              template/
              skeleton/
        fixtures/
          *.yml            // File con le fixture
      lib/
        *.php              // Classi
        helper/
          *.php            // Helper
        model/
          *.php            // Classi del modello
        task/
          *Task.class.php  // Task CLI
      modules/
        */                 // Moduli
          actions/
            actions.class.php
          config/
            module.yml
            view.yml
            security.yml
          templates/
            *.php
      web/
        *                  // Risorse web

#### Possibilità dei plugin

I plugin possono contenere molte cose. Il loro contenuto è automaticamente preso in considerazione dall'applicazione in fase di runtime e quando si chiamano i task tramite riga di comando. Ma perché i plugin funzionino correttamente, è necessario rispettare alcune convenzioni:

  * Gli schemi per il database vengono rilevati dai task `propel-`. Quando nel proprio progetto si chiamano `propel:build --classes` o `doctrine:build --classes`, si ricreano i modelli del progetto e con esso tutti i modelli dei plug-in. Si noti che lo schema di un plugin Propel deve sempre avere un attributo package sotto forma di `plugins.nomePlugin`. `lib.model`, come mostrato nel listato 17-19. Se si utilizza Doctrine, il task di genererà automaticamente le classi nella cartella dei plugin.

Listato 17-19 - Esempio della dichiarazione di uno schema di Propel in un plugin, in `mioPlugin/config/schema.yml`

    propel:
      _attributes:    { package: plugins.mioPlugin.lib.model }
      my_plugin_foobar:
        _attributes:    { phpName: mioPluginFoobar }
          id:
          name:           { type: varchar, size: 255, index: unique }
          ...

  * La configurazione del plug-in deve essere presente nella classe di configurazione del plug-in (`NomePluginConfiguration.class.php`). Questo file viene eseguito dopo la configurazione dell'applicazione e del progetto, in modo da symfony a quel punto sia già inizializzato. È possibile utilizzare questo file, per esempio, per estendere classi esistenti con ascoltatori di eventi e comportamenti.
  * I file con le fixture che si trovano nella cartella `data/fixtures/` del plugin vengono analizzati dai task `propel:data-load` o `doctrine:data-load`.
  * Le classi personalizzate sono autocaricate proprio come quelle che si mettono nelle cartelle `lib/` del proprio progetto.
  * Gli helper vengono trovati automaticamente quando si chiama `use_helper()` nei template. Essi devono essere in una sotto cartella `helper/` di una delle cartelle `lib/` del plugin.
  * Se si usa Propel, le classi del modello in `myplugin/lib/model/` specializzano le classi del modello generate dal generatore di Propel (in `myplugin/lib/model/om/` and `myplugin/lib/model/map/`) . Essi sono, ovviamente, caricate automaticamente. Bisogna essere a conoscenza che non è possibile sovrascrivere le classi del modello generato di un plug-in, nelle cartelle del proprio progetto.
  * Se si usa Doctrine, l'ORM genera le classi base dei plugin in `myplugin/lib/model/Plugin*. class.php` e le classi reali in `lib/model/myplugin/`. Questo significa che si possono sovrascrivere facilmente le classi del modello nella propria applicazione.
  * I task sono immediatamente disponibili per la riga di comando di symfony non appena il plug-in viene installato. Un plugin può aggiungere nuovi task o sovrascriverne uno esistente. È buona pratica usare il nome del plugin come spazio dei nomi per il task. Digitare `php symfony` per visualizzare l'elenco dei task disponibili, inclusi quelli aggiunti dai plugin.
  * I moduli forniscono nuove azioni accessibili dall'esterno, a condizione che li si dichiari impostandoli in `enabled_modules` nell'applicazione.
  * Le risorse web (immagini, script, fogli di stile, ecc) sono messe a disposizione del server. Quando si installa un plug-in tramite la riga di comando, symfony crea un link simbolico alla cartella `web/` del progetto, se il sistema lo consente, o copia il contenuto della cartella `web/` del modulo nel progetto. Se il plugin è installato da un archivio o un repository di controllo della versione, è necessario copiare la cartella `web/` del plugin a mano (come dovrebbe indicare il file `README` incluso nel plug-in).
		  


>**TIP**: Registrazione delle regole di routing in un plugin
>Un plugin può aggiungere nuove regole al sistema delle rotte, ma non è consigliato farlo utilizzando il consueto filedi configurazione `routing.yml`. Questo perché l'ordine con cui sono definite le regole è molto importante e il semplice sistema di configurazione a cascata dei file YAML in symfony mischierebbe tale ordinamento. Utilizzare invece un ascoltatore di eventi da registrare sull'evento `routing.load_configuration` e aggiungere le regole manualmente nell'ascoltatore:
>
>     [php]
>     // in plugins/mioPlugin/config/config.php
>     $this->dispatcher->connect('routing.load_configuration', array('mioPluginRouting', 'listenToRoutingLoadConfigurationEvent'));
>     
>     // in plugins/mioPlugin/lib/mioPluginRouting.php
>     class mioPluginRouting
>     {
>       static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
>       {
>         $routing = $event->getSubject();
>         // aggiunge le regole per le rotte del plugin in cima a quelle esistenti
>         $routing->prependRoute('my_route', new sfRoute('/my_plugin/:action', array('module' => 'mioPluginAdministrationInterface')));
>       }
>     }
>

#### Configurazione manuale del plugin

Ci sono alcuni elementi che il task `plugin:install` non può gestire da solo e che richiedono una impostazione manuale durante l'installazione:

  * Configurazione personalizzate dell'applicazione possono essere utilizzate nel codice del plugin (ad esempio, utilizzando `sfConfig::get('app_myplugin_foo')`), ma non si possono mettere i valori predefiniti in un file `app.yml` che si trova nella cartella `config/` del plugin. Per gestire i valori predefiniti, si usa il secondo argomento del metodo `sfConfig::get()`. Le impostazioni possono comunque essere sovrascritte a livello di applicazione (vedere il listato 17-25 per un esempio).
  * Le regole personalizzate delle rotte devono essere aggiunte manualmente nel file `routing.yml` dell'applicazione.
  * I filtri personalizzati devono essere aggiunti manualmente nel file `filters.yml` dell'applicazione.
  * I factory personalizzati devono essere aggiunti manualmente nel file `factories.yml` dell'applicazione.

In generale, tutta la configurazione che dovrebbe finire in uno dei file di configurazione dell'applicazione deve essere aggiunta manualmente. I plugin con tali impostazioni manuali dovrebbero incorporare un file `README` che descrive i dettagli dell'installazione.

#### Personalizzare un plugin per una applicazione

Ogni volta che si desidera personalizzare un plug-in, non modificare mai il codice che si trova nella cartella `plugins/`. Se lo si fa, quando si aggiorna il plugin si perderanno tutte le modifiche. Per esigenze di personalizzazione, i plugin forniscono impostazioni personalizzate e supportano la sovrascrittura del codice.

I plugin ben progettati permettono di usare impostazioni che possono essere modificate nel file `app.yml` dell'applicazione, come dimostra il listato 17-20.

Listato 17-20 - Personalizzazione di un plugin che usa la configurazione dell'applicazione

    [php]
    // esempio di codice del plugin
    $foo = sfConfig::get('app_my_plugin_foo', 'bar');

    // Cambiare il valore di 'foo' predefinito ('bar') nel file app.yml dell'applicazione
    all:
      mio_plugin:
        foo:       barbar

Le impostazioni del modulo e i loro valori predefiniti spesso sono descritti nel file `README` del plugin.
		
Si possono sostituire i contenuti predefiniti del modulo di un plugin, creando un modulo dello stesso nome nella propria applicazione. Non è una vera e propria sovrascrittura del codice, perché gli elementi presenti nell'applicazione sono usati al posto di quelli del plugin. Funziona correttamente se si creano file di template e di configurazione con lo stesso nome di quelli dei plugin.

D'altra parte, se un plugin vuole mettere a disposizione un modulo che abbia la possibilità di far sovrascrivere le proprie azioni, il file `actions.class.php` nel modulo del plugin deve essere vuoto ed ereditare da una classe in autocaricamento, così che il metodo di questa classe può essere ereditato anche da `actions.class.php` del modulo dell'applicazione. Per un esempio, vedere il listato 17-21.

Listato 17-21 - Personalizzare l'azione di un plugin

    [php]
    // In mioPlugin/modules/mymodule/lib/mioPluginmymoduleActions.class.php
    class mioPluginmymoduleActions extends sfActions
    {
      public function executeIndex()
      {
        // Un po' di codice
      }
    }

    // In mioPlugin/modules/mymodule/actions/actions.class.php

    require_once dirname(__FILE__).'/../lib/mioPluginmymoduleActions.class.php';

    class mymoduleActions extends mioPluginmymoduleActions
    {
      // Niente
    }

    // In frontend/modules/mymodule/actions/actions.class.php
    class mymoduleActions extends mioPluginmymoduleActions
    {
      public function executeIndex()
      {
        // Sovrascrivere qui il codice del plugin
      }
    }

>**SIDEBAR**
>Personalizzare lo schema di un plugin
>
>###Doctrine
>Quando si costruisce il modello, Doctrine guarda tutti i file del tipo `*schema.yml` presenti nelle cartelle `config/` dell'applicazione e del plugin, quindi lo schema di un progetto può sovrascrivere lo schema di un plugin. Il processo di fusione permette di aggiungere o modificare tabelle o colonne. Il seguente esempio mostra come aggiungere colonne a una tabella definita in uno schema del plugin.
>
>     #Schema originale, in plugins/mioPlugin/config/schema.yml
>     Article:
>       columns:
>         name: string(50)
>
>     #Schema del progetto, in config/schema.yml
>     Article:
>       columns:
>         stripped_title: string(50)
>
>     #Schema risultante, ottenuto tramite una fusione interna e utilizzato per la generazione dei modelli e dell'sql
>     Article:
>       columns:
>         name: string(50)
>         stripped_title: string(50)
>
>
>
>###Propel
>Quando si costruisce il modello, symfony cercherà i file personalizzati YAML per ogni schema esistente, compresi i plugin, seguendo questa regola:
>
>Nome originale dello schema             | Nome dello schema personalizzato
>--------------------------------------- | --------------------------------
>config/schema.yml                       | schema.custom.yml
>config/foobar_schema.yml                | foobar_schema.custom.yml
>plugins/mioPlugin/config/schema.yml     | mioPlugin-schema.custom.yml
>plugins/mioPlugin/config/foo_schema.yml | mioPlugin_foo-schema.custom.yml
>
>Gli schemi personalizzati saranno cercati nelle cartelle `config/` dell'applicazione e del plugin, quindi un plugin può sovrascrivere lo schema di un'altro plugin e ci può essere più di una personalizzazione per ciascuno schema.
>
>Symfony unirà i due schemi basandosi su ciascuna delle tabelle `phpName`. Il processo di fusione permette di aggiungere o modificare tabelle, colonne e attributi di colonna. Per esempio, il listato seguente mostra come uno schema personalizzato possa aggiungere colonne a una tabella definita in uno schema del plugin.
>
>     # Schema originale, in plugins/mioPlugin/config/schema.yml
>     propel:
>       article:
>         _attributes:    { phpName: Article }
>         title:          varchar(50)
>         user_id:        { type: integer }
>         created_at:
>
>     # Schema personalizzato, in mioPlugin_schema.custom.yml
>     propel:
>       article:
>         _attributes:    { phpName: Article, package: foo.bar.lib.model }
>         stripped_title: varchar(50)
>
>     # Schema risultante, unito internamente e utilizzato per la generazione dei modelli e dell'sql
>     propel:
>       article:
>         _attributes:    { phpName: Article, package: foo.bar.lib.model }
>         title:          varchar(50)
>         user_id:        { type: integer }
>         created_at:
>         stripped_title: varchar(50)
>
>Poiché il processo di fusione usa la tabella `phpName` come chiave, si può anche cambiare il nome della tabella di un plugin nel database, a condizione che si mantenga lo stesso `phpName` nello schema.

### Come scrivere un plugin

Solo i plugin pacchettizzati con PEAR possono essere installati con il task `plugin:install`. È bene ricordare che tali plugin possono essere distribuiti attraverso la sezione plugin di symfony, un canale PEAR o un semplice file da scaricare. Quindi, se si vuole scrivere un plugin, è meglio pubblicarlo come pacchetto PEAR che come semplice file archivio. Inoltre, i plugin che utilizzano PEAR sono più facili da aggiornare, possono dichiarare dipendenze e fare il deploy in automatico delle risorse nella cartella `web/`.

#### Organizzazione dei file

Supponiamo di avere sviluppato una nuova funzionalità e di volerla pacchettizzare come plugin. Il primo passo è quello di organizzare logicamente i file in modo che i meccanismi di caricamento di symfony possano trovare tutto il necessario. A tale scopo, bisogna seguire la struttura fornita nel listato 17-18. Il listato 17-22 mostra un esempio di struttura dei file per un plugin `sfEsempioPlugin`.

Listato 17-22 - Esempio di un elenco di file da pacchettizzare come plugin

    sfEsempioPlugin/
      README
      LICENSE
      config/
        schema.yml
        sfEsempioPluginConfiguration.class.php
      data/
        fixtures/
          fixtures.yml
      lib/
        model/
          sfEsempioFooBar.php
          sfEsempioFooBarPeer.php
        task/
          sfEsempioTask.class.php
        validator/
          sfEsempioValidator.class.php
      modules/
        sfEsempioModule/
          actions/
            actions.class.php
          config/
            security.yml
          lib/
            BasesfEsempioModuleActions.class.php
          templates/
            indexSuccess.php
      web/
        css/
          sfEsempioStyle.css
        images/
          sfEsempioImage.png

Per la creazione, la posizione della cartella del plugin (`sfEsempioPlugin/` nel listato 17-22) non è importante. Può trovarsi in una qualunque cartella del disco.

>**TIP**
>Prendere esempio dai plugin esistenti e per il primo tentativo di creazione di un plugin provare a seguire la loro struttura di file e le loro convenzioni per i nomi.

#### Creazione del file package.xml

Il prossimo passo nella creazione del plugin è quello di aggiungere un file package.xml nella radice della cartella con il plugin. Il file `package.xml` segue la sintassi di PEAR. Dare un'occhiata a un tipico `package.xml` di un plugin di symfony nel listato 17-23.

Listato 17-23 - Esempio di file `package.xml` per un plugin di symfony

    [xml]
    <?xml version="1.0" encoding="UTF-8"?>
    <package packagerversion="1.4.6" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
     <name>sfEsempioPlugin</name>
     <channel>plugins.symfony-project.org</channel>
     <summary>esempio di plugin symfony</summary>
     <description>Solo un plugin di esempio per mostrare il pacchetto PEAR</description>
     <lead>
      <name>Fabien POTENCIER</name>
      <user>fabpot</user>
      <email>fabien.potencier@symfony-project.com</email>
      <active>yes</active>
     </lead>
     <date>2006-01-18</date>
     <time>15:54:35</time>
     <version>
      <release>1.0.0</release>
      <api>1.0.0</api>
     </version>
     <stability>
      <release>stable</release>
      <api>stable</api>
     </stability>
     <license uri="http://www.symfony-project.org/license">MIT license</license>
     <notes>-</notes>
     <contents>
      <dir name="/">
       <file role="data" name="README" />
       <file role="data" name="LICENSE" />
       <dir name="config">
        <!-- model -->
        <file role="data" name="schema.yml" />
        <file role="data" name="ProjectConfiguration.class.php" />
       </dir>
       <dir name="data">
        <dir name="fixtures">
         <!-- fixtures -->
         <file role="data" name="fixtures.yml" />
        </dir>
       </dir>
       <dir name="lib">
        <dir name="model">
         <!-- model classes -->
         <file role="data" name="sfEsempioFooBar.php" />
         <file role="data" name="sfEsempioFooBarPeer.php" />
        </dir>
        <dir name="task">
         <!-- tasks -->
         <file role="data" name="sfEsempioTask.class.php" />
        </dir>
        <dir name="validator">
         <!-- validators -->
         <file role="data" name="sfEsempioValidator.class.php" />
        </dir>
       </dir>
       <dir name="modules">
        <dir name="sfEsempioModule">
         <file role="data" name="actions/actions.class.php" />
         <file role="data" name="config/security.yml" />
         <file role="data" name="lib/BasesfEsempioModuleActions.class.php" />
         <file role="data" name="templates/indexSuccess.php" />
        </dir>
       </dir>
       <dir name="web">
        <dir name="css">
         <!-- stylesheets -->
         <file role="data" name="sfEsempioStyle.css" />
        </dir>
        <dir name="images">
         <!-- images -->
         <file role="data" name="sfEsempioImage.png" />
        </dir>
       </dir>
      </dir>
     </contents>
     <dependencies>
      <required>
       <php>
        <min>5.2.4</min>
       </php>
       <pearinstaller>
        <min>1.4.1</min>
       </pearinstaller>
       <package>
        <name>symfony</name>
        <channel>pear.symfony-project.com</channel>
        <min>1.3.0</min>
        <max>1.5.0</max>
        <exclude>1.5.0</exclude>
       </package>
      </required>
     </dependencies>
     <phprelease />
     <changelog />
    </package>

Le parti più interessanti sono i tag `<contents>` e `<dependencies>`, descritti sotto. Negli altri tag, non c'è nulla di specifico per symfony, quindi si può fare riferimento al [manuale](http://pear.php.net/manual/en/) online di PEAR per maggiori dettagli sul formato `package.xml`.

#### Il tag contents

Nel tag `<contents>` bisogna descrivere la struttura dei file del plugin. In quest modo PEAR saprà quali file copiare e dove. Descrivere la struttura dei file con i tag `<dir>` e `<file>`. Tutti i tag `<file>` devono avere un attributo `role="data"`. La parte `<contents>` del listato 17-23 descrive l'esatta struttura delle cartelle del listato 17-22.

>**NOTE**
>L'uso dei tag `<dir>` non è obbligatorio, dato che è possibile utilizzare i percorsi relativi come valori `name` nei tag `<file>`. Tuttavia, è raccomandato il loro utilizzo perché in questo modo il file `package.xml` rimane leggibile.

#### Dipendenze del plugin

I plug-in sono progettati per funzionare con un dato insieme di versioni di PHP, PEAR, symfony, pacchetti PEAR, o altri plugin. Dichiarare queste dipendenze nel tag <dependencies>` chiede a PEAR di verificare che i pacchetti richiesti siano già installati e di sollevare un'eccezione in caso contrario.

È sempre necessario dichiarare le dipendenze da PHP, PEAR e symfony, almeno quelle corrispondenti alla propria installazione, come requisito minimo. Se non si sa cosa mettere, aggiungere un requisito per PHP 5.2.4, PEAR 1.4, e symfony 1.3.

Si raccomanda inoltre di aggiungere un numero massimo di versione symfony per ogni plugin. Ciò causerà un messaggio di errore quando si tenta di utilizzare un plugin con una versione più avanzata del framework e questo obbligherà l'autore del plugin ad assicurarsi che il plug-in funzioni correttamente con questa versione prima del nuovo rilascio. È meglio avere una segnalazione e scaricare un aggiornamento piuttosto che avere un plugin che fallisce silenziosamente.

Se si specificano plugin come dipendenze, gli utenti saranno in grado di installare il plugin e tutte le sue dipendenze con un singolo comando:

    $ php symfony plugin:install --install-deps sfEsempioPlugin

#### Realizzare il plugin

Il componente PEAR ha un comando (`pear package`) che crea l'archivio `.tgz` del pacchetto, purché si chiami il comando mostrato nel listato 17-24 da una cartella contenente un file `package.xml`.

Listato 17-24 - Creare il pacchetto PEAR di un plugin

    $ cd sfEsempioPlugin
    $ pear package

    Package sfEsempioPlugin-1.0.0.tgz done

Una volta che il plugin è stato creato, verificare il funzionamento installandolo sulla propria macchina, come mostrato nel listato 17-25.

Listato 17-25 - Installare il plugin

    $ cp sfEsempioPlugin-1.0.0.tgz /home/production/mioprogetto/
    $ cd /home/production/mioprogetto/
    $ php symfony plugin:install sfEsempioPlugin-1.0.0.tgz

In base alla descrizione contenuta nel tag `<contents>`, i file del pacchetto andranno nelle diverse cartelle del progetto. Il listato 17-26 mostra dove dovrebbero andare i file del plugin `sfEsempioPlugin` dopo l'installazione.

Listato 17-26 - Il file del plugin vengono installati nelle cartelle `plugins/` e `web/`

    plugins/
      sfEsempioPlugin/
        README
        LICENSE
        config/
          schema.yml
          sfEsempioPluginConfiguration.class.php
        data/
          fixtures/
            fixtures.yml
        lib/
          model/
            sfEsempioFooBar.php
            sfEsempioFooBarPeer.php
          task/
            sfEsempioTask.class.php
          validator/
            sfEsempioValidator.class.php
        modules/
          sfEsempioModule/
            actions/
              actions.class.php
            config/
              security.yml
            lib/
              BasesfEsempioModuleActions.class.php
            templates/
              indexSuccess.php
    web/
      sfEsempioPlugin/               ## Copia o link simbolico, dipende dal sistema
        css/
          sfEsempioStyle.css
        images/
          sfEsempioImage.png

Verificare il modo in cui si comporta il plugin con la propria applicazione. Se funziona bene, si è pronti a distribuirlo nei propri progetti, o a contribuire alla comunità di symfony.

#### Fare ospitare il proprio plugin nel sito web del progetto symfony

Un plugin di symfony ha una visibilità migliore se distribuito dal sito web `symfony-project.org`. Anche il proprio plugin può essere distribuiti in questo modo, a condizione di seguire questi passaggi: 

  1. Assicurarsi che il file `README` descriva le modalità di installazione e di utilizzo del plugin e che il file `LICENSE` fornisca i dettagli della licenza. Formattare il file `README` utilizzando la sintassi [Markdown](http://daringfireball.net/projects/markdown/syntax).
  2. Creare un account symfony (http://www.symfony-project.org/user/new) e creare il plugin (http://www.symfony-project.org/plugins/new).
  3. Creare un pacchetto PEAR per il plugin utilizzando il comando `pear package` e verificarne il funzionamento. Il pacchetto PEAR deve essere chiamato `sfEsempioPlugin-1.0.0.tgz` (1.0.0 è la versione del plugin).
  4. Caricare il pacchetto PEAR (`sfEsempioPlugin-1.0.0.tgz`).
  5. Il plugin ora comparirà nell'elenco dei [plugin](http://www.symfony-project.org/plugins/).

Se si segue questa procedura, gli utenti saranno in grado di installare il plugin semplicemente digitando il seguente comando in una cartella del progetto:

    $ php symfony plugin:install sfEsempioPlugin

#### Convenzioni per i nomi

Per mantenere la cartella `plugins/` pulita, assicurarsi che tutti i nomi dei plugin siano in camelCase e terminino con `Plugin` (per esempio `shoppingCartPlugin`, `feedPlugin` e così via). Prima di dare un nome al proprio plugin, verificare che non ce ne sia già uno con lo stesso nome.

>**NOTE**
>I plugin che si basano su Propel dovrebbero contenere la parola `Propel` nel nome (lo stesso vale per l'utilizzo di Doctrine). Ad esempio, un plugin di autenticazione che utilizzi gli oggetti di Propel per l'accesso ai dati, dovrebbe essere chiamato `sfPropelAuth`.

I plugin devono sempre includere un file `LICENSE` che descrive le condizioni di utilizzo e la licenza scelta. Si consiglia inoltre di aggiungere un file README per spiegare i cambiamenti di versione, lo scopo del plugin, i suoi effetti, le istruzioni di installazione e configurazione, ecc.

Riepilogo
---------

Le classi di symfony notificano eventi che danno loro la possibilità di essere modificati a livello di applicazione. Il meccanismo degli eventi permette l'ereditarietà multipla e la sovrascrittura delle classi in fase di runtime, anche se le limitazioni di PHP lo impedirebbero. Quindi le funzionalità di symfony si possono estendere facilmente, anche quando è necessario modificare le classi core: la configurazione dei factory è qui per questo.

Molte estensioni esistono già, sono pacchettizzate come plugin, per essere installate facilmente, aggiornate e disinstallate tramite la riga di comando di symfony. Creare un plugin è facile come creare un pacchetto PEAR e fornisce riutilizzabilità tra le applicazioni.

La sezione plugin del sito di symfony contiene molti plugin e si possono anche aggiungere i propri. Quindi, ora che si sa come fare, speriamo che si aggiungano al core di symfony un sacco di estensioni utili!
