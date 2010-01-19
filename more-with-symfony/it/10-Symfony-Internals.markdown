Symfony all'interno
===================

*di Geoffrey Bachelet*

Vi siete mai chiesti cosa accade ad una richiesta HTTP quando raggiunge una
applicazione symfony? Se sì, allora siete nel posto giusto. Questo capitolo
spiegherà nel dettaglio come symfony processa ogni richiesta al fine di creare
e restituire la risposta. Naturalmente, descrivere solo tale processo sarebbe
un po' noioso, così verrà anche dato uno sguardo ad alcune cose interessanti
che si possono fare e dove si può interagire con questo processo.

Bootstrap
---------

Tutto comincia nel controllore dell'applicazione. Diciamo di avere un controllore
`frontend` con un ambiente `dev` (un inizio molto classico per un progetto
symfony). In questo caso, ci si ritroverà con un controllore principale che si trova
in [`web/frontend_dev.php`](http://trac.symfony-project.org/browser/branches/1.3/lib/task/generator/skeleton/app/web/index.php).
Che cosa succede esattamente in questo file? In poche righe di codice, symfony
recupera la configurazione dell'applicazione e crea un'istanza di `sfContext`,
che è responsabile per l'invio della richiesta. La configurazione dell'applicazione
è necessaria quando si crea l'oggetto `sfContext`, che è il motore
applicazione-dipendente dietro symfony.  

>**TIP**
>Symfony dà già un po' di controllo su quello che succede qui, consentendo
>di passare una cartella root personalizzata per l'applicazione come quarto
>parametro di ~`ProjectConfiguration::getApplicationConfiguration()`~, così come
>una classe personalizzata del contesto come terzo (e ultimo) parametro di
>[`sfContext::createInstance()`](http://www.symfony-project.org/api/1_3/sfContext#method_createinstance)
>(ma ricordarsi che deve estendere `sfContext`).

Recuperare la configurazione dell'applicazione è un punto molto importante.
`sfProjectConfiguration` è responsabile per cercare la classe con la configurazione
dell'applicazione, generalmente `${application}Configuration`, che si trova in
`apps/${application}/config/${application}Configuration.class.php`.

`sfApplicationConfiguration` in realtà estende `ProjectConfiguration`, nel
senso che ogni metodo in `ProjectConfiguration` può essere condiviso tra tutte
le applicazioni. Questo significa anche che `sfApplicationConfiguration` condivide
il suo costruttore con entrambi `ProjectConfiguration` e `sfProjectConfiguration`.
Questa è una buona cosa, perché gran parte del progetto è configurato dentro al
costruttore `sfProjectConfiguration`. In primo luogo, alcuni valori utili sono
calcolati e memorizzati, come la cartella root del progetto e la cartella con le
librerie di symfony. `sfProjectConfiguration` crea anche un nuovo evento
dispatcher di tipo `sfEventDispatcher`, a meno che non ne sia stato passato uno come quinto
parametro di `ProjectConfiguration::getApplicationConfiguration()` nel controllore
principale.

Dopo aver fatto questo, si ha la possibilità di interagire con il processo di
configurazione, sovrascrivendo il metodo `setup()` di `ProjectConfiguration`.
Questo di solito è il posto migliore per abilitare / disabilitare i plugin (usando
[`sfProjectConfiguration::setPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setplugins),
[`sfProjectConfiguration::enablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableplugins),
[`sfProjectConfiguration::disablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_disableplugins) o
[`sfProjectConfiguration::enableAllPluginsExcept()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableallpluginsexcept)).

Dopo, i plugin sono caricati da [`sfProjectConfiguration::loadPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_loadplugins)
e lo sviluppatore ha la possibilità di interagire con questo processo attraverso
[`sfProjectConfiguration::setupPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setupplugins), che può essere sovrascritto.

L'inizializzazione dei plugin è abbastanza semplice. Per ciascun plugin, symfony
cerca una classe `${plugin}Configuration` (ad esempio `sfGuardPluginConfiguration`)
e se la trova crea una istanza. In caso contrario, è usato `sfPluginConfigurationGeneric`.
È possibile agganciarsi ad una configurazione di plugin, attraverso due metodi:

 * `${plugin}Configuration::configure()`, prima che sia stato fatto l'autoloading
 * `${plugin}Configuration::initialize()`, dopo l'autoloading

Dopo, `sfApplicationConfiguration` esegue il suo metodo `configure()`,
che può essere usato per personalizzare ciascuna configurazione dell'applicazione
prima che la maggior parte del processo interno di configurazione dell'inizializzazione
inizi in [`sfApplicationConfiguration::initConfiguration()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_initconfiguration).

Questa parte del processo di configurazione di symfony, è responsabile di molte
cose e ci sono diversi punti di accesso, se si desidera agganciarsi in questo
processo. Ad esempio, è possibile interagire con la configurazione dell'autoloader
collegandosi all'evento `autoload.filter_config`. Successivamente, vengono caricati
alcuni file di configurazione molto importanti, tra cui `settings.yml` e `app.yml`.
Infine, un'ultima possibilità per configurare i plugin, è disponibile in ogni
file `config/config.php` dei plugin, o nel metodo `initialize()` delle classi di
configurazione.

Se è attivato `sf_check_lock`, symfony controlla per un file di lock (quello
creato dal task `project:disable` per esempio). Se viene trovato il lock,
vengono controllati i seguenti file e viene incluso il primo disponibile, seguito
dalla terminazione immediata dello script:

 1. `apps/${application}/config/unavailable.php`,
 1. `config/unavailable.php`,
 1. `web/errors/unavailable.php`,
 1. `lib/vendor/symfony/lib/exception/data/unavailable.php`,

Infine, lo sviluppatore ha un'ultima possibilità per personalizzare l'inizializzazione
dell'applicazione, attraverso il metodo ~`sfApplicationConfiguration::initialize()`~.

### Bootstrap e riepilogo della configurazione

 * Recupero della configurazione dell'applicazione
  * `ProjectConfiguration::setup()` (definire qua i plugin)
  * I plugin vengono caricati
   * `${plugin}Configuration::configure()`
   * `${plugin}Configuration::initialize()`
  * `ProjectConfiguration::setupPlugins()` (configurare qua i plugin)
  * `${application}Configuration::configure()`
  * Viene notificato `autoload.filter_config`
  * Caricamento di `settings.yml` e `app.yml`
  * `${application}Configuration::initialize()`
 * Creazione di una istanza di `sfContext`

`sfContext` e i factory
-----------------------

Prima di tuffarsi nel processo di dispatch, è bene parlare di una parte vitale del
flusso di lavoro di symfony: i factory.

In symfony, i factory sono un insieme di componenti o classi su cui si basa
l'applicazione. Esempi di factory sono il `logger`, l'`i18n`, ecc.
Ciascun factory è configurato attraverso il file `factories.yml`, che è compilato
attraverso un gestore di configurazione (più avanti verranno date maggiori informazioni
sui gestori di configurazione) e convertito in codice PHP che istanzia effettivamente
gli oggetti del factory (è possibile visualizzare questo codice nel file
`cache/frontend/dev/config/config_factories.yml.php` della cache).

>**NOTE**
>Il caricamento del factory avviene sull'inizializzazione di `sfContext`. Vedere
>[`sfContext::initialize()`](http://www.symfony-project.org/api/1_3/sfContext#method_initialize)
>e [`sfContext::loadFactories()`](http://www.symfony-project.org/api/1_3/sfContext#method_loadfactories)
>per maggiori informazioni.

A questo punto, è già possibile personalizzare gran parte del comportamento di
symfony solo modificando la configurazione di `factories.yml`. Si possono anche
sostituire le classi factory integrate in symfony con le proprie!

>**NOTE**
>Se si è interessati a saperne di più sui factory,
>[La guida di riferimento a symfony](http://www.symfony-project.org/reference/1_4/it/05-Factories),
>così come i file stessi
>[`factories.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/factories.yml),
>sono risorse preziose.

Se si guarda il file generato `config_factories.yml.php`, si può notare che i
factory sono istanziati in un certo ordine. Questo ordine è importante, poiché
alcuni factory sono dipendenti da altri (per esempio, il componente `routing`
ovviamente richiede `request` per recuperare le informazioni di cui ha bisogno).

Parliamo in maggiore dettaglio della `request`. Per impostazione predefinita,
la classe `sfWebRequest` rappresenta la `request`. È chiamata su istanziazione
[`sfWebRequest::initialize()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_initialize)
raccogliendo informazioni pertinenti, quali i parametri GET / POST, così come il
metodo HTTP. Si ha quindi l'opportunità di aggiungere la propria richiesta
attraverso l'evento `request.filter_parameters`.

### Usare l'evento `request.filter_parameter`

Si supponga di gestire un sito web, esponendo una API pubblica ai propri utenti.
L'API è disponibile tramite HTTP e ogni utente che vuole utilizzarla deve fornire
una chiave API valida attraverso una richiesta header (per esempio `X_API_KEY`),
che deve essere convalidata dall'applicazione. Questo obiettivo può essere
facilmente raggiunto utilizzando l'evento `request.filter_parameter`:

    [php]
    class apiConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('request.filter_parameters', array(
          $this, 'requestFilterParameters'
        ));
      }

      public function requestFilterParameters(sfEvent $event, $parameters)
      {
        $request = $event->getSubject();

        $api_key = $request->getHttpHeader('X_API_KEY');

        if (null === $api_key || false === $api_user = Doctrine_Core::getTable('ApiUser')->findOneByToken($api_key))
        {
          throw new RuntimeException(sprintf('Invalid api key "%s"', $api_key));
        }

        $request->setParameter('api_user', $api_user);

        return $parameters;
      }
    }

Quindi si sarà in grado di accedere all'API dell'utente dalla richiesta:

    [php]
    public function executeFoobar(sfWebRequest $request)
    {
      $api_user = $request->getParameter('api_user');
    }

Questa tecnica può essere usata, ad esempio, per validare chiamate di servizi web.

>**NOTE**
>L'evento `request.filter_parameters` arriva con molte informazioni sulla
>request. Per maggiori informazioni vedere il metodo
>[`sfWebRequest::getRequestContext()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_getrequestcontext).

Il successivo importante factory è quello delle rotte. L'inizializzazione delle
rotte è abbastanza semplice e consiste principalmente nel raccogliere e impostare
opzioni specifiche. È possibile, tuttavia, collegarsi a questo processo attraverso
l'evento `routing.load_configuration`.

>**NOTE**
>L'evento `routing.load_configuration` dà l'accesso all'istanza corrente
>dell'oggetto delle rotte (per impostazione predefinita,
>[`sfPatternRouting`](http://trac.symfony-project.org/browser/branches/1.3/lib/routing/sfPatternRouting.class.php)).
>È quindi possibile modificare le rotte registrate, attraverso una varietà di metodi.

### Esempio di utilizzo dell'evento `routing.load_configuration`

Come esempio, si può facilmente aggiungere una rotta:

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('routing.load_configuration', array(
        $this, 'listenToRoutingLoadConfiguration'
      ));
    }

    public function listenToRoutingLoadConfiguration(sfEvent $event)
    {
      $routing = $event->getSubject();

      if (!$routing->hasRouteName('my_route'))
      {
        $routing->prependRoute('my_route', new sfRoute(
          '/my_route', array('module' => 'default', 'action' => 'foo')
        ));
      }
    }

L'analisi dell'URL si verifica subito dopo l'inizializzazione, tramite il metodo
[`sfPatternRouting::parse()`](http://www.symfony-project.org/api/1_3/sfPatternRouting#method_parse).
Ci sono alcuni metodi che intervengono, ma è sufficiente sapere che nel
momento in cui si arriva alla fine del metodo `parse`, la rotta è stata trovata,
istanziata e legata ai relativi parametri.

>**NOTE**
>Per maggiori informazioni sulle rotte, vedere il capitolo `Utilizzo avanzato
>delle rotte` di questo libro.

Una volta che tutti i factory sono stati caricati e configurati correttamente,
l'evento `context.load_factories` viene attivato. Questo evento è importante,
perché è il primo evento nel framework in cui lo sviluppatore ha accesso a tutti
gli oggetti del factory del core di symfony (request, response, user, logging,
database, ecc.).

È il momento di connettersi anche a un altro evento molto utile:
`template.filter_parameters`. Questo evento si verifica quando un file viene reso
da [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php)
e consente allo sviluppatore di controllare i parametri effettivamente passati al template.
`sfContext` approfitta di questo evento per aggiungere alcuni parametri utili per ogni
template (vale a dire `$sf_context`, `$sf_request`, `$sf_params`, `$sf_response`
e `$sf_user`).

È possibile connettersi all'evento `template.filter_parameters`, al fine di
aggiungere parametri personalizzati globali per tutti i template.

### Utilizzare l'evento `template.filter_parameters`

Si supponga di decidere che ogni singolo template che si usa, debba avere accesso
ad un particolare oggetto, per esempio un oggetto helper.  Si potrebbe quindi
aggiungere il seguente codice a `ProjectConfiguration`: 

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('template.filter_parameters', array($this, 'templateFilterParameters'));
    }

    public function templateFilterParameters(sfEvent $event, $parameters)
    {
      $parameters['my_helper_object'] = new MyHelperObject();

      return $parameters;
    }

Ora ogni template ha accesso ad una istanza di `MyHelperObject` attraverso
`$my_helper_object`.

### Riepilogo di `sfContext`

1. Inizializzazione di `sfContext`
1. Caricamento del factory
1. Eventi notificati:
 1. [request.filter_parameters](http://www.symfony-project.org/reference/1_4/it/15-Events#chapter_15_sub_request_filter_parameters)
 1. [routing.load_configuration](http://www.symfony-project.org/reference/1_4/it/15-Events#chapter_15_sub_routing_load_configuration)
 1. [context.load_factories](http://www.symfony-project.org/reference/1_4/it/15-Events#chapter_15_sub_context_load_factories)
1. Aggiunti i parametri globali dei template

Una parola sui gestori di configurazione
----------------------------------------

I gestori di configurazione sono al centro del sistema di configurazione di symfony.
Un gestore di configurazione ha il compito di *capire* il significato di un file
di configurazione. Ciascun gestore di configurazione, è semplicemente una classe che
viene utilizzata per tradurre una serie di file di configurazione YAML in un
blocco di codice PHP, che può essere eseguito quando se ne ha la necessità.
Ogni file di configurazione viene assegnato a uno specifico gestore di configurazione
nel [file `config_handlers.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/config_handlers.yml).

Ad essere precisi, il lavoro di un gestore di configurazione *non* è quello di
analizzare realmente i file YAML (questa funzione viene eseguita da `sfYaml`).
Invece, ogni gestore di configurazione crea una serie di direttive PHP sulla
base delle informazioni YAML e salva queste direttive in un file PHP, che può
essere efficacemente incluso in seguito. La versione *compilata* di ciascun file
di configurazione YAML, può essere trovata nella cartella della cache. 

Il gestore di configurazione usato più comunemente è certamente
[`sfDefineEnvironmentConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfDefineEnvironmentConfigHandler.class.php),
che consente impostazioni di configurazioni specifiche per l'ambiente.
Questo gestore di configurazione si prende cura di recuperare solo le impostazioni
di configurazione dell'ambiente attuale.

Non siete ancora convinti? Esploriamo
[`sfFactoryConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfFactoryConfigHandler.class.php).
Questo gestore di configurazione viene utilizzato per compilare `factories.yml`,
che è uno dei più importanti file di configurazione in symfony. Il gestore di
configurazione è molto particolare, dal momento che converte un file di configurazione
YAML nel codice PHP che in ultimo istanzia i factory (tutti gli importanti componenti
di cui si è parlato in precedenza). Non proprio un comune gestore di configurazione, vero?

Il dispatch e l'esecuzione della richiesta
------------------------------------------

È stato detto abbastanza sui factory, si può tornare in carreggiata parlando del processo
del dispatch. Una volta che `sfContext` ha terminato l'inizializzazione, il passo
finale è chiamare il metodo `dispatch()` del controllore,
[`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch).

Lo stesso processo di dispatch di symfony è molto semplice. In realtà,
`sfFrontWebController::dispatch()` prende semplicemente i nomi del modulo e
dell'azione dai parametri della richiesta e li inoltra all'applicazione tramite
[`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward).

>**NOTE**
>A questo punto, se la rotta non è in grado di analizzare ciascun nome di modulo
>o nome di azione dalla url corrente, viene sollevato un errore
>[`sfError404Exception`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfError404Exception.class.php),
>che inoltrerà la richiesta a un modulo per la gestione dell'errore 404 (vedere
>[`sf_error_404_module` e
>`sf_error_404_action`](http://www.symfony-project.org/reference/1_4/it/04-Settings#chapter_04_sub_error_404)).
>Notare che per ottenere questo effetto, è possibile sollevare una tale eccezione
>da qualsiasi punto dell'applicazione.

Il metodo `forward` è responsabile di molti dei controlli pre-esecuzione, nonché
della preparazione della configurazione e dei dati per l'azione da eseguire.

In primo luogo il controllore verifica la presenza di un file
[`generator.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/generator.yml)
per il modulo corrente. Questo controllo viene eseguito prima (dopo alcune pulizie
di base al nome del modulo / azione), perché il file di configurazione `generator.yml`
(se esiste) è responsabile di generare la classe di azioni di base per il modulo
(attraverso il suo gestore di configurazione, `sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php)).
Ciò è necessario per il passo successivo, che controlla se il modulo e l'azione
esistono. Questo è delegato al controllore, attraverso
[`sfController::actionExists()`](http://www.symfony-project.org/api/1_3/sfController#method_actionexists),
che a sua volta chiama il metodo
[`sfController::controllerExists()`](http://www.symfony-project.org/api/1_3/sfController#method_controllerexists).
Anche in questo caso, se il metodo `actionExists()` fallisce, viene sollevata una
eccezione `sfError404Exception`.

>**NOTE**
>[`sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php) è
>uno speciale gestore di configurazione che si occupa di istanziare la giusta
>classe generatore per il modulo, ed eseguirla. Per ulteriori informazioni sulla
>configurazione dei gestori, vedere *Una parola sul gestore di configurazione*
>in questo capitolo. Inoltre, per maggiori informazioni su `generator.yml`, vedere
>il [capitolo 6 della guida di riferimento a symfony](http://www.symfony-project.org/reference/1_4/it/06-Admin-Generator).

Non c'è molto da fare qui, oltre a sovrascrivere il metodo
[`sfApplicationConfiguration::getControllerDirs()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_getcontrollerdirs)
nella classe di configurazione dell'applicazione. Questo metodo restituisce un
array di cartelle dove si trovano i file del controllore, con un parametro
aggiuntivo per dire a symfony se deve verificare che i controllori di ciascuna
cartella sono abilitati, tramite l'opzione di configurazione `sf_enabled_modules`
del file `settings.yml`. Per esempio, `getControllerDirs() potrebbe essere simile
a questo:

    [php]
    /**
     * I controllori in /tmp/myControllers non hanno bisogno di essere abilitati
     * per essere rilevati
     */
    public function getControllerDirs($moduleName)
    {
      return array_merge(parent::getControllerDirs($moduleName), array(
        '/tmp/myControllers/'.$moduleName => false
      ));
    }

>**NOTE**
>Se l'azione non esiste, viene lanciata una eccezione `sfError404Exception`.

Il passo successivo, è quello di recuperare un'istanza del controllore contenente
l'azione. Questo viene gestito tramite il metodo
[`sfController::getAction()`](http://www.symfony-project.org/api/1_3/sfController#method_getaction)
il quale, come `actionExists()`, è un facade per il metodo
[`sfController::getController()`](http://www.symfony-project.org/api/1_3/sfController#method_getcontroller),
In ultimo, l'istanza del controllore è aggiunta all'`action stack`.

>**NOTE**
>La pila dell'azione è del tipo FIFO (First In First Out, il primo ad entrare è
>il primo ad uscire), la quale detiene tutte le azioni eseguite durante la richiesta.
>Ogni oggetto nella pila è avvolto in un
>oggetto `sfActionStackEntry`. È sempre possibile accedere alla pila con
>`sfContext::getInstance()->getActionStack()` o
>`$this->getController()->getActionStack()` dall'interno di una azione.

Dopo un po' di caricamenti di configurazione, si sarà in grado di eseguire l'azione.
La configurazione specifica per il modulo deve ancora essere caricata e si può
trovare in due punti distinti. Prima symfony cerca un file `module.yml`
(normalmente posizionato in `apps/frontend/modules/yourModule/config/module.yml`),
il quale essendo un file di configurazione YAML, usa la configurazione della cache.
Inoltre, questo file di configurazione può dichiarare il modulo come *interno*,
usando l'opzione `mod_yourModule_is_internal` che fa sì che a questo punto la
richiesta fallisca, dato che un modulo interno non può essere chiamato
pubblicamente.

>**NOTE**
>I moduli interni, sono stati già utilizzati per generare il contenuto delle email
>(tramite `getPresentationFor()`, ad esempio). Qua si dovrebbero utilizzare altre
>tecniche, come ad esempio un partial (`$this->renderPartial()`).

Ora che `module.yml` è caricato, è il momento di verificare per la seconda volta
che il modulo corrente sia abilitato. Infatti, è possibile impostare `mod_$moduleName_enabled`
a `false`, se a questo punto si desidera disattivare il modulo.

>**NOTE**
>Come già detto, ci sono due modi diversi per attivare o disattivare un modulo.
>La differenza è che cosa accade quando il modulo è disattivato. Nel primo caso,
>quando l'impostazione `sf_enabled_modules` è selezionata, un modulo disabilitato
>comporterà il lancio di una eccezione
>[`sfConfigurationException`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfConfigurationException.class.php)
>Questo dovrebbe essere usato quando si vuole disabilitare un modulo in modo permanente.
>Nel secondo caso, attraverso l'impostazione `mod_$moduleName_enabled`, un modulo
>disabilitato comporterà all'applicazione l'inoltro al modulo disabilitato (vedere
>le impostazioni di [`sf_module_disabled_module` e
>`sf_module_disabled_action`](http://www.symfony-project.org/reference/1_4/it/04-Settings#chapter_04_sub_module_disabled)
>). Si dovrebbero utilizzare quando si vuole disattivare temporaneamente un modulo.

L'ultima opportunità di configurare un modulo si trova nel file `config.php`
(`apps/frontend/modules/yourModule/config/config.php`), in cui è possibile inserire
codice PHP arbitrario da eseguire nel contesto del metodo
[`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
 (è così perché si ha accesso all'istanza `sfController` attraverso la variabile
`$this`, dal momento che il codice viene eseguito letteralmente all'interno
della classe `sfController`).

### Riepilogo del processo dispatch

1. Viene chiamato [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch)
1. Viene chiamato [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
1. Verifica la presenza di `generator.yml`
1. Verifica l'esistenza del modulo / azione
1. Recupera un elenco di cartelle di controllori
1. Recupera una istanza dell'azione
1. Carica la configurazione del modulo attraverso `module.yml` e/o `config.php`

La catena dei filtri
--------------------

Ora che la configurazione è stata fatta, è il momento di iniziare il lavoro vero.
Il lavoro reale, in questo caso particolare, è l'esecuzione della catena dei filtri.

>**NOTE**
>La catena dei filtri di symfony implementa un design pattern noto come
[catena delle responsabilità](http://it.wikipedia.org/wiki/Chain_of_responsibility_pattern).
>Questo è un semplice ma potente pattern che permette azioni concatenate, in cui
>ogni parte della catena è in grado di decidere se la catena deve continuare l'esecuzione
>oppure no.
>Ogni parte della catena è anche in grado di eseguire entrambi, sia prima che
>dopo il resto della esecuzione della catena.

La configurazione della catena dei filtri è recuperata dal modulo corrente
[`filters.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/filters.yml),
ed è il motivo per cui l'istanza dell'azione è necessaria. A questo punto c'è la
possibilità di modificare la serie di filtri eseguiti dalla catena. Basta ricordare
che il filtro di rendering dovrebbe essere sempre il primo della lista (si vedrà
dopo il perché). L'impostazione predefinita di configurazione dei filtri è la seguente:

* [`rendering`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfRenderingFilter.class.php)
* [`security`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfSecurityFilter.class.php)
* [`cache`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfCacheFilter.class.php)
* [`execution`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfExecutionFilter.class.php)

>**NOTE**
>Si raccomanda vivamente di aggiungere i propri filtri tra il filtro `security`
>e quello di `cache`.

### Il filtro security

Dato che il filtro `rendering` attende che tutto il resto sia fatto, prima di
fare qualsiasi cosa, il primo filtro che in realtà viene eseguito è il filtro
`security`. Questo filtro assicura che tutto sia a posto in base al file di configurazione
[`security.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/security.yml).
In particolare, il filtro inoltra un utente non autenticato al modulo / azione
`login` e un utente con credenziali insufficienti al modulo / azione `secure`.
Si noti che questo filtro è eseguito solo se la sicurezza è attivata per la
determinata azione.

### La cache del filtro

Dopo viene eseguito il filtro `cache`. Questo filtro sfrutta la sua capacità di
evitare che i filtri vengano eseguiti nuovamente. Infatti, se la cache è attivata
e se si ha una richiesta, perché eseguire nuovamente l'azione? Naturalmente, questo
funzionerà solo per una pagina che si possa mettere completamente in cache,
che non è il caso per la stragrande maggioranza delle pagine.

Ma questo filtro ha una seconda logica che viene eseguita dopo l'esecuzione del
filtro e appena prima del filtro di rendering. Questo codice è responsabile per
la corretta costituzione della cache degli header HTTP e per l'inserimento della pagina
nella cache, se necessario, grazie al metodo
[`sfViewCacheManager::setPageCache()`](http://www.symfony-project.org/api/1_3/sfViewCacheManager#method_setpagecache).

### Il filtro di esecuzione

Ultimo ma non meno importante, il filtro `execution`, si prenderà infine cura di
eseguire la logica dell'applicazione e di gestire la visualizzazione associata.

Tutto inizia quando il filtro controlla la cache per l'azione in corso. Naturalmente,
se se ha qualcosa nella cache, l'esecuzione attuale dell'azione salta e viene
eseguita la vista `Success`.

Se l'azione non viene trovata nella cache, allora è il momento di eseguire la
logica `preExecute()` del controllore e infine di eseguire l'azione stessa.
Questa operazione viene eseguita dall'istanza dell'azione tramite una chiamata
a [`sfActions::execute()`](http://www.symfony-project.org/api/1_3/sfActions#method_execute).
Questo metodo non fa molto: semplicemente verifica che l'azione è invocabile
e quindi la chiama. Tornando nel filtro viene eseguita la logica `postExecute()`.

>**NOTE**
>Il valore restituito dall'azione è molto importante, perché determina
>quale vista verrà eseguita. Per impostazione predefinita, se non viene trovato
>nessun valore di ritorno, si assume `sfView::SUCCESS` (che si traduce, come avrete
>indovinato, `Success`, come ad esempio in `indexSuccess.php`).

Un altro passo ed è il momento della vista. Il filtro esegue una verifica per
due speciali valori di ritorno che l'azione può avere restituito, `sfView::HEADER_ONLY`
e `sfView::NONE`. Ciascuno fa esattamente quello che dicono i propri nomi:
inviare solo le intestazioni HTTP (gestite internamente tramite
[`sfWebResponse::setHeaderOnly()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_setheaderonly))
o saltare tutta la fase di rendering.

>**NOTE**
>I nomi per le viste già integrati in symfony, sono: `ALERT`, `ERROR`, `INPUT`, `NONE` and `SUCCESS`.
>Ma fondamentalmente è possibile restituire tutto quello che si vuole.

Una volta che si sa che si vuole fare il render di qualcosa, si è pronti per
entrare nel passo finale del filtro: l'effettiva esecuzione della vista.

La prima cosa da fare è recuperare un oggetto [`sfView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfView.class.php)
attraverso il metodo [`sfController::getView()`](http://www.symfony-project.org/api/1_3/sfController#method_getview). Questo oggetto può provenire da
due posti diversi. In primo luogo si potrebbe avere un oggetto personalizzato
per la vista per questa specifica azione (supponendo che il corrente modulo/azione,
sia modulo/azione) `actionSuccessView` o `module_actionSuccessView` in un file
chiamato `apps/frontend/modules/module/view/actionSuccessView.class.php`.
In caso contrario, sarà usata la classe definita nella voce di configurazione
`mod_module_view_class`. Il suo valore predefinito è [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php).

>**TIP**
>Utilizzare la propria classe di visualizzazione, dà la possibilità di eseguire
>delle logiche specifiche per la vista, attraverso il metodo [`sfView::execute()`](http://www.symfony-project.org/api/1_3/sfView#method_execute)
>Ad esempio, è possibile creare un'istanza per il proprio motore di template.

Ci sono tre possibili modalità di rendering per la vista:

1. `sfView::RENDER_NONE`": equivalente a `sfView::NONE`, cancella qualunque rendering che potrebbe essere visualizzato
1. `sfView::RENDER_VAR`: popola la presentazione delle azioni, che è quindi accessibile tramite il metodo [`sfActionStackEntry::getPresentation()`](http://www.symfony-project.org/api/1_3/sfActionStackEntry#method_getpresentation) con le voci del suo stack.
1. `sfView::RENDER_CLIENT`, la modalità predefinita, visualizzerà la vista e il contenuto della risposta

>**NOTE**
>In effetti, la modalità di rendering è usata solo attraverso il metodo
>[`sfController::getPresentationFor()`](http://www.symfony-project.org/api/1_3/sfController#method_getpresentationfor) che restituisce il rendering per
>un dato modulo / azione

### Il filtro per il rendering

La trattazione è quesi finita, rimane solo un ultimo passaggio. La catena dei filtri ha
quasi terminato la sua esecuzione, ma bisogna ricordarsi del filtro di rendering.
È rimasto in attesa dall'inizio della catena, che tutti completassero il proprio
lavoro, in modo che alla fine possa svolgere il suo compito. Il filtro di rendering
invia il contenuto della risposta al browser, utilizzando
[`sfWebResponse::send()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_send).

### Riepilogo dell'esecuzione della catena dei filtri

1. La catena dei filtri viene istanziata con la configurazione, dal file `filters.yml`
1. Il filtro `security` verifica le autorizzazioni e le credenziali
1. Il filtro `cache` gestisce la cache per la pagina corrente
1. Il filtro `execution` esegue effettivamente l'azione
1. Il filtro `rendering` invia la risposta tramite `sfWebResponse`

Riepilogo complessivo
---------------------

1. Recupero della configurazione dell'applicazione
1. Creazione di una istanza `sfContext`
1. Inizializzazione di `sfContext`
1. Caricamento dei factory
1. Notifica dei seguenti eventi:
 1. ~`request.filter_parameters`~
 1. ~`routing.load_configuration`~
 1. ~`context.load_factories`~
1. Aggiunta dei parametri globali del template
1. Viene chiamato [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch)
1. Viene chiamato [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
1. Verifica dell'esistenza di un `generator.yml`
1. Verifica dell'esistenza di un modulo / azione
1. Recupero di un elenco di cartelle dei controllori
1. Recupero di una istanza dell'azione
1. Caricamento della configurazione del modulo attraverso `module.yml` e/o `config.php`
1. La catena dei filtri viene istanziata con la configurazione dal file `filters.yml`
1. Il filtro `security` verifica le autorizzazioni e le credenziali
1. Il filtro `cache` gestisce la cache per la pagina corrente
1. Il filtro `execution` esegue effettivamente l'azione
1. Il filtro `rendering`  invia la risposta tramite `sfWebResponse`

Considerazioni finali
---------------------

È tutto! La richiesta è stata gestita e ora si è pronti per gestirne un'altra.
Certo, si potrebbe scrivere un intero libro sui processi interni di symfony, però
questo capitolo è utile per avere una buona visione d'insieme. Siete più che
benvenuti a esplorare da soli i sorgenti: questo è e sarà sempre, il modo
migliore per imparare i reali meccanismi di qualunque framework o libreria.
