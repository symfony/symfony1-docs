Capitolo 6 - All'interno del livello del controllore
====================================================

Il controllore in symfony è il livello che contiene il codice che collega la business logic alla presentazione, diviso in diversi componenti che si usano per diversi scopi:

  * Il front controller è il punto d'accesso univoco all'applicazione. Si occupa di caricare la configurazione e determina l'azione da eseguire.
  * Le azioni contengono la logica dell'applicazione. Verificano l'integrità della richiesta e preparano i dati necessari al livello della vista.
  * Richiesta, risposta e oggetti di sessione permettono l'accesso ai parametri della richiesta, alle intestazioni della risposta e ai dati persistenti dell'utente. Vengono usati molto spesso nel livello controllore.
  * I filtri sono porzioni di codice eseguito a ogni richiesta, prima o dopo l'azione. Per esempio i filtri di sicurezza e validazione sono usati comunemente nelle applicazioni web. Si può facilmente estendere il framework creando i propri filtri.

Questo capitolo descrive tutti questi componenti, non fatevi intimidire dal loro numero. Per una pagina semplice molto probabilmente basterà scrivere poche righe di codice nella classe dell'azione, tutto qui. Gli altri componenti del controllore vengono utilizzati solo in situazioni particolari.

Il Front Controller
-------------------

Tutte le richieste web vengono gestite da un singolo front controller, che rappresenta l'unico punto d'accesso per l'intera applicazione in un ambiente.

Quando il front controller riceve una richiesta utilizza il sistema delle rotte per identificare il nome di un'azione e il nome di un modulo partendo dall'URL inserito (o cliccato) dall'utente. Per esempio, l'URL della richiesta seguente richiama lo script `index.php` (il front controller) e verrà interpretato come una chiamata all'azione `myAction` del modulo `miomodulo`:

    http://localhost/index.php/miomodulo/myAction

Se non si è interessati a conoscere gli internals di symfony, questo è tutto ciò che si deve sapere a proposito del front controller. Si tratta di un componente indispensabile nell'architettura MVC di symfony, raramente sarà necessario modificarlo. Detto questo è possibile passare alla prossima sezione a meno di non essere realmente interessati a sviscerare l'argomento front controller.

### Il lavoro del front controller in dettaglio

Il front controller si occupa di distribuire le richieste, questo però significa qualcosa di più della semplice determinazione dell'azione da eseguire. Infatti esegue il codice comune a tutte le azioni, incluso il seguente:

  1. Carica la classe con la configurazione del progetto e le librerie di symfony.
  2. Crea la configurazione dell'applicazione e il contesto di symfony.
  3. Carica e inizializza le classi del nucleo del framework.
  4. Carica la configurazione.
  5. Interpreta l'URL della richiesta per determinare l'azione da eseguire e i parametri della richiesta. 
  6. Se l'azione non esiste redirige all'azione d'errore 404.
  7. Attiva i filtri (per esempio se la richiesta richiede autenticazione).
  8. Esegue i filtri, primo passaggio.
  9. Esegue l'azione e rende la vista.
  10. Esegue i filtri, secondo passaggio.
  11. Restituisce la risposta.

### Il front controller di default

Il front controller di default, chiamato `index.php` e posizionato nella cartella `web/` del progetto, è un semplice file PHP come si può vedere nel listato 6-1.

Listato 6-1 - Il front controller di default per l'ambiente di produzione

    [php]
    <?php
    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

Il front controller crea un'istanza della configurazione dell'applicazione che si occupa dei passaggi dal 2 al 4. La chiamata al metodo `dispatch()` dell'oggetto `sfController` (che è l'oggetto controllore cardine dell'architettura MVC di symfony) smista le richieste, facendosi carico dei punti da 5 a 7. Gli ultimi compiti sono gestiti dalla catena dei filtri, come verrà spiegato più avanti in questo capitolo.

### Chiamare un diverso front controller per cambiare ambiente

Esiste un front controller per ogni ambiente. Infatti è proprio l'esistenza di un front controller a definire un ambiente. L'ambiente è definito dal secondo parametro passato alla chiamata del metodo `ProjectConfiguration::getApplicationConfiguration()`.

Per cambiare l'ambiente in cui si sta visualizzando l'applicazione, è sufficiente scegliere un altro front controller. I front controller predefiniti alla creazione di una nuova applicazione, con il task `generate:app`, sono `index.php` per l'ambiente di produzione e `frontend_dev.php` per l'ambiente di sviluppo (ammesso che l'applicazione si chiami `frontend`). La configurazione predefinita del `mod_rewrite` userà `index.php` quando l'URL non conterrà il nome di un front controller. Quindi, i seguenti URL visualizzeranno la stessa pagina (`miomodulo/index`) nell'ambiente di produzione:

    http://localhost/index.php/miomodulo/index
    http://localhost/miomodulo/index

e questo URL visualizzerà la stessa pagina nell'ambiente di sviluppo:

    http://localhost/frontend_dev.php/miomodulo/index

Creare un nuovo ambiente è semplice: basta creare un nuovo front controller. Per esempio, potrebbe essere necessario avere un ambiente di stage, per consentire ai clienti di testare l'applicazione prima di andare in produzione. Per creare questo ambiente, basta copiare `web/frontend_dev.php` in `web/frontend_staging.php` e cambiare il valore del secondo parametro della chiamata a `ProjectConfiguration::getApplicationConfiguration()` in `staging`. Ora in tutti i file di configurazione è possibile aggiungere una nuova sezione `staging:` per impostare valori specifici per questo ambiente, come mostrato nel listato 6-2.

Listato 6-2 - Esempio di `app.yml` con impostazioni specifiche per l'ambiente di stage

    staging:
      mail:
        webmaster:    finto@miosito.it
        contact:      finto@miosito.it
    all:
      mail:
        webmaster:    webmaster@miosito.it
        contact:      contatti@miosito.it

Per vedere come l'applicazione reagisce in questo ambiente, basta richiamare il front controller relativo:

    http://localhost/frontend_staging.php/miomodulo/index

Azioni
------

Le azioni sono il cuore di un'applicazione, perché contengono tutta la logica dell'applicazione stessa. Si occupano di richiamare il modello e di definire le variabili per la vista. Facendo una richiesta web a un'applicazione symfony, l'URL definisce un'azione e i parametri della richiesta.

### La classe dell'azione

Le azioni sono metodi chiamati `executeNomeAzione` di una classe denominata `moduloNomeAzione`, che eredita dalla classe `sfActions`, e raggruppati in moduli. La classe azione di un modulo è memorizzata nel file `actions.class.php` nella cartella `actions/` del modulo stesso.

Listato 6-3 mostra un esempio di file `actions.class.php` con una sola azione `index` per l'intero modulo `miomodulo`.

Listato 6-3 - Classe di azione d'esempio, in `apps/frontend/modules/miomodulo/actions/actions.class.php`

    [php]
    class miomoduloActions extends sfActions
    {
      public function executeIndex($request)
      {
        // ...
      }
    }

>**CAUTION**
>Anche se i nomi dei metodi non sono case-sensitive in PHP, lo sono in symfony. Perciò è importante non dimenticare che i metodi delle azioni devono iniziare con un `execute` minuscolo seguito dallo stesso identico nome dell'azione, con la prima lettera maiuscola.

Per poter richiedere un'azione è necessario invocare lo script del front controller, passando come parametri i nomi di un modulo e di un'azione. L'impostazione predefinita non fa altro che appendere la coppia `nome_modulo`/`nome_azione` allo script. Questo significa che l'azione definita nel listato 6-4 può essere richiamata con questo URL:

    http://localhost/index.php/miomodulo/index

Aggiungere nuove azioni significa aggiungere ulteriori metodi `execute` all'oggetto `sfActions` come mostrato nel listato 6-4.

Listato 6-4 - Classe azione con due azioni, in `frontend/modules/miomodulo/actions/actions.class.php`

    [php]
    class miomoduloActions extends sfActions
    {
      public function executeIndex($request)
      {
        // ...
      }

      public function executeList($request)
      {
        // ...
      }
    }

Se la dimensione di una classe azione tende a crescere troppo, molto probabilmente necessita di un po' di rifattorizzazione per spostare del codice verso il livello del modello. Le azioni dovrebbero essere mantenute sempre brevi (non più di alcune righe), mentre tutta la business logic dovrebbe essere nel modello.

Nonostante questo, il numero di azioni in un modulo potrebbe essere così elevato da spingere a separarlo in due moduli.

>**SIDEBAR**
>Standard di codice di symfony
>
>Negli esempi di codice di questo libro, sarà balzato agli occhi il fatto che le parentesi graffe (`{` e `}`) occupano una riga ciascuna. Questo standard permette una più semplice lettura del codice.
>
>Tra gli altri standard di codice del framework, ricordiamo l'indentazione, che è sempre fatta da due spazi vuoti; le tabulazioni non vengono utilizzate. Questo perché le tabulazioni hanno spazi diversi a seconda dell'editor di testo utilizzato, inoltre codice in cui l'indentazione è mista tra tabulazioni e spazi vuoti è impossibile da leggere.
>
>I file PHP del nucleo e quelli generati da symfony non terminano con il consueto tag di chiusura `?>`. Questo è possibile perché non è realmente necessario e perché potrebbe causare problemi all'output nel caso in cui fossero presenti spazi vuoti dopo il tag stesso.
>
>Prestando davvero molta attenzione, sarà facile vedere come una riga di codice non finisca mai con uno spazio vuoto in symfony. La ragione questa volta è molto più banale: le righe che terminano con spazi vuoti si vedono molto male nell'editor di testo di Fabien.

### Sintassi alternativa per le classi azione

Una sintassi alternativa per l'azione è a disposizione per distribuire le azioni in file separati, un file per azione. In questo caso ogni classe azione estende `sfAction` (invece di `sfActions`) ed è chiamata `actionNameAction`. L'attuale metodo azione è semplicemente chiamato `execute`. Il nome del file è lo stesso della classe. Questo significa che l'equivalente del listato 6-4 può essere scritto con i due file dei listati 6-5 e 6-6.

Listato 6-5 - File azione singolo, in `frontend/modules/miomodulo/actions/indexAction.class.php`

    [php]
    class indexAction extends sfAction
    {
      public function execute($request)
      {
        // ...
      }
    }

Listato 6-6 - File azione singolo, in `frontend/modules/miomodulo/actions/listAction.class.php`

    [php]
    class listAction extends sfAction
    {
      public function execute($request)
      {
        // ...
      }
    }

### Recuperare informazioni nell'azione

La classe azione mette a disposizione dei modi di accesso alle informazioni relative al controller e agli oggetti del nucleo di symfony. Il listato 6-7 mostra come utilizzarli.

Listato 6-7 - Metodi comuni `sfActions`

    [php]
    class miomoduloActions extends sfActions
    {
      public function executeIndex(sfWebRequest $request)
      {
        // Recupera i parametri della richiesta
        $password    = $request->getParameter('password');

        // Recupera informazioni sul controllore
        $moduleName  = $this->getModuleName();
        $actionName  = $this->getActionName();

        // Recupera gli oggetti del nucleo del framework
        $userSession = $this->getUser();
        $response    = $this->getResponse();
        $controller  = $this->getController();
        $context     = $this->getContext();

        // Impostare le variabili dell'azione per passare informazioni al template
        $this->setVar('pippo', 'pluto');
        $this->pippo = 'pluto';          // Versione breve
      }
    }

>**SIDEBAR**
>Il singleton context
>
>Abbiamo già visto, nel front controller, una chiamata a `sfContext::createInstance()`. Nell'azione il metodo `getContext()` restituisce lo stesso singleton. Questo è un oggetto molto utile che contiene un riferimento a tutti gli oggetti del nucleo di symfony associati a una richiesta, mettendo a disposizione una via di accesso a ognuno di loro:
>
>`sfController`: L'oggetto controllore (`->getController()`)
>
>`sfRequest`: L'oggetto richiesta (`->getRequest()`)
>
>`sfResponse`: L'oggetto risposta (`->getResponse()`)
>
>`sfUser`: L'oggetto della sessione utente (`->getUser()`)
>
>`sfRouting`: L'oggetto delle rotte (`->getRouting()`)
>
>`sfMailer`: L'oggetto mailer (`->getMailer()`)
>
>`sfI18N`: L'oggetto dell'internazionalizzazione (`->getI18N()`)
>
>`sfLogger`: L'oggetto logger (`->getLogger()`)
>
>`sfDatabaseConnection`: La connessione al database (`->getDatabaseConnection()`)
>
>Tutti questi oggetti del nucleo sono disponibili tramite il singleton `sfContext::getInstance()` in ogni parte del codice. Tuttavia è una pratica disdicevole perché crea dipendenze così forti in grado di rendere il codice davvero difficile da testare, riutilizzare e mantenere. In questo libro si potrà imparare come evitare l'utilizzo di `sfContext::getInstance()`.

### Terminare l'azione

Alla fine dell'esecuzione di un'azione si possono assumere diversi comportamenti. Il valore restituito dal metodo dell'azione determina come la vista verrà generata. Le costanti della classe `sfView` vengono utilizzate per specificate quale template utilizzare per mostrare il risultato dell'azione.

Se esiste una vista predefinita da invocare (questo è il caso più comune), l'azione dovrebbe terminare come segue:

    [php]
    return sfView::SUCCESS;

Symfony cercherà quindi un template chiamato `actionNameSuccess.php`. Questo è definito come comportamento predefinito, quindi anche omettendo la direttiva `return` nel metodo di un'azione symfony cercherà ancora un template `actionNameSuccess.php`. Le azioni vuote scatenano lo stesso comportamento. Nel listato 6-8 alcuni esempi di corretta conclusione delle azioni.

Listato 6-8 - Azioni che invocano i template `indexSuccess.php` e `listSuccess.php`

    [php]
    public function executeIndex()
    {
      return sfView::SUCCESS;
    }

    public function executeList()
    {
    }

Se esiste una vista di errore da invocare, l'azione dovrebbe concludersi così:

    [php]
    return sfView::ERROR;

Symfony cercherà quindi un template chiamato `actionNameError.php`.

Per chiamare una vista personalizzata usare questo finale:

    [php]
    return 'MyResult';

Symfony cercherà quindi un template chiamato `actionNameMyResult.php`.

Nel caso in cui non esista una vista da chiamare--per esempio nel caso in cui un'azione venga eseguita in un processo batch--l'azione dovrebbe chiudersi come segue:

    [php]
    return sfView::NONE;

Nessun template verrà eseguito in questo caso. Significa che è possibile aggirare completamente il livello della vista e impostare il codice HTML di risposta direttamente in un'azione. Come mostrato nel listato 6-9, symfony mette a disposizione uno specifico metodo `renderText()` per questo caso. Può essere utile quando si ha bisogno di un'azione estremamente responsiva, come per le interazioni Ajax, che verranno affrontate nel capitolo 11.

Listato 6-9 - Aggirare la vista facendo l'echo della risposta e restituendo `sfView::NONE`

    [php]
    public function executeIndex()
    {
      $this->getResponse()->setContent("<html><body>Hello, World!</body></html>");

      return sfView::NONE;
    }

    // Equivalente a
    public function executeIndex()
    {
      return $this->renderText("<html><body>Hello, World!</body></html>");
    }

In alcuni casi è necessario inviare una risposta vuota ma con alcune intestazioni definite in essa (specialmente l'intestazione `X-JSON`). Definire le intestazioni tramite l'oggetto `sfResponse`, di cui si parlerà nel capitolo successivo, e restituire la costante `sfView::HEADER_ONLY`, come mostrato nel listato 6-10.

Listato 6-10 - Evitare la creazione della vista inviando solamente le intestazioni

    [php]
    public function executeRefresh()
    {
      $output = '<"title","My basic letter"],["name","Mr Brown">';
      $this->getResponse()->setHttpHeader("X-JSON", '('.$output.')');

      return sfView::HEADER_ONLY;
    }

Se l'azione dev'essere presentata da un template specifico ignorare la dichiarazione `return` utilizzando invece il metodo `setTemplate()`.

    [php]
    public function executeIndex()
    {
      $this->setTemplate('myCustomTemplate');
    }
    
Con questo codice symfony cercherà un file `myCustomTemplateSuccess.php` invece che `indexSuccess.php`.

### Saltare a un'altra azione

In alcuni casi l'esecuzione di un'azione termina richiedendo l'esecuzione di un'altra azione. Per esempio, un'azione che gestisce l'invio di un form tramite una richiesta POST generalmente redireziona a un'altra azione dopo aver aggiornato il database.

La classe azione mette a disposizione due metodi per eseguire un'altra azione:

  * Se l'azione inoltra la chiamata a un'altra azione:

        [php]
        $this->forward('otherModule', 'index');

  * Se l'azione termina con una redirezione web:

        [php]
        $this->redirect('otherModule/index');
        $this->redirect('http://www.google.com/');


>**NOTE**
>Il codice situato dopo un forward o un redirect in un'azione non viene mai eseguito. Chiamate di questo tipo possono essere considerate come un `return`. Essi sollevano un `sfStopException` per bloccare l'esecuzione di un'azione; questa eccezione è colta successivamente da symfony e semplicemente ignorata.

La scelta tra un redirect e un forward a volte può essere difficoltosa. Per scegliere la soluzione migliore, va ricordato che un forward è interno all'applicazione e totalmente trasparente per l'utente. Per quanto riguarda l'utente, l'URL visualizzato sarà uguale a quello richiesto. Al contrario, un redirect è un messaggio al browser dell'utente e coinvolge una nuova richiesta, con conseguente cambio di URL finale.

Se l'azione è chiamata da un form inviato con `method="post"`, sarà necessario ricorrere **sempre** a un redirect. Il vantaggio principale è che, se l'utente aggiorna la pagina con la risposta, il form non verrà inviato nuovamente; inoltre il pulsante indietro si comporterà come previsto, visualizzando il form e non un avviso che chiede all'utente se vuole inviare nuovamente una richiesta POST.

Esiste un tipo particolare di forward usato molto spesso. Il metodo `forward404()` inoltra a un'azione "pagina non trovata". Questo metodo viene chiamato spesso quando un parametro necessario all'esecuzione dell'azione non è presente nella richiesta (individuando così un URL errato). Il listato 6-11 mostra un esempio di azione `show` che si aspetta un parametro `id`.

Listato 6-11 - Utilizzo del metodo `forward404()`

    [php]
    public function executeShow(sfWebRequest $request)
    {
      // Doctrine
      $article = Doctrine::getTable('Article')->find($request->getParameter('id'));
      
      // Propel
      $article = ArticlePeer::retrieveByPK($request->getParameter('id'));
      
      if (!$article)
      {
        $this->forward404();
      }
    }

>**TIP**
>Se siete in cerca dell'azione e del template per l'errore 404, sappiate che si trova nella cartella `$sf_symfony_ lib_dir/controller/default/`. 
>È possibile personalizzare questa pagina creando un nuovo modulo `default` nell'applicazione, sovrascrivendo quella proposta dal framework e definendo al suo interno un'azione `error404` e un template error404Success. Altrimenti è possibile impostare le costanti `error_404_module` e `error_404_action` nel file `settings.yml` per utilizzare un'azione esistente.

L'esperienza insegna che, la maggior parte delle volte, un'azione esegue un redirect o un forward dopo aver verificato qualcosa, come nel listato 6-12. Questo è il motivo per cui la classe `sfActions` ha alcuni metodi aggiuntivi chiamati `forwardIf()`, `forwardUnless()`, `forward404If()`, `forward404Unless()`, `redirectIf()` e `redirectUnless()`. Questi parametri accettano semplicemente un parametro aggiuntivo, che rappresenta una condizione in grado di scatenare l'esecuzione se verificato positivamente (per i metodi `xxxIf()`) o negativamente (per i metodi `xxxUnless()`), come illustrato nel listato 6-12.

Listato 6-12 - Utilizzo del metodo `forward404If()`

    [php]
    // Questa azione è equivalente a quella presentata nel listato 6-11
    public function executeShow(sfWebRequest $request)
    {
      $article = Doctrine::getTable('Article')->find($request->getParameter('id'));
      $this->forward404If(!$article);
    }

    // Allo stesso modo questa
    public function executeShow(sfWebRequest $request)
    {
      $article = Doctrine::getTable('Article')->find($request->getParameter('id'));
      $this->forward404Unless($article);
    }

Utilizzare questi metodi oltre a mantenere il codice compatto permette di renderlo più leggibile.

>**TIP**
>Quando un'azione invoca `forward404()` o gli altri metodi dello stesso tipo, symfony lancia una `sfError404Exception` in grado di gestire la risposta 404. Questo significa che nel caso in cui fosse necessario visualizzare un messaggio 404 da qualche parte senza necessariamente accedere al controllore è possibile farlo lanciando un'eccezione simile.

### Ripetere codice per diversa azioni di un modulo

La convenzione per la denominazione delle azioni come `executeActionName()` (nel caso delle classi `sfActions`) o `execute()` (nel caso delle classi `sfAction`) garantisce che symfony possa trovare il metodo dell'azione. Permette anche di aggiungere altri metodi che non verranno considerati come azioni a patto che non inizino con `execute`.

Esiste un'altra utile convenzione quando è necessario ripetere diverse dichiarazioni in ogni azione prima della reale esecuzione. È possibile spostare queste dichiarazioni nel metodo `preExecute()` della classe azione. Allo stesso modo è possibile ripetere delle dichiarazioni dopo l'esecuzione di ogni azione: basta spostarle nel metodo `postExecute()`. La sintassi di questi metodi è visibile nel listato 6-13.

Listato 6-13 - Utilizzo di `preExecute()`, `postExecute()` e metodi personalizzati nella classe azione

    [php]
    class miomoduloActions extends sfActions
    {
      public function preExecute()
      {
        // Il codice inserito qui viene eseguito all'inizio di ogni azione
        ...
      }

      public function executeIndex($request)
      {
        ...
      }

      public function executeList($request)
      {
        ...
        $this->myCustomMethod();  // I metodi della classe azione sono accessibili
      }

      public function postExecute()
      {
        // Il codice inserito qui viene eseguito alla fine di ogni azione
        ...
      }

      protected function myCustomMethod()
      {
        // È possibile aggiungere i propri metodi, ammesso che non inizino con "execute"
        // In questo caso è consigliabile dichiararli protetti o privati
        ...
      }
    }

>**TIP**
>Dato che i metodi pre/post esecuzione vengono invocati per **ogni** azione del modulo corrente, è necessario assicurarsi di aver realmente bisogno di eseguire questo codice per **tutte** le azioni, per evitare effetti collaterali inattesi.

Accedere alla richiesta
-----------------------

Il primo parametro passato a ogni metodo di un'azione è l'oggetto della richiesta, che in symfony si chiama `sfWebRequest`. Si è gia visto il metodo `getParameter('myparam')`, usato per recuperare il valore di un parametro della richiesta usando il suo nome. La Tabella 6-1 elenca i metodi  `sfWebRequest` più utili.

Table 6-1 - Metodi dell'oggetto `sfWebRequest`

Nome                             | Funzione                             |  Output d'esempio
-------------------------------- | ------------------------------------ | -------------------------------------------------------
**Informazioni della richiesta** |                                      |
`isMethod($method)`              | È una post o una get?                | `true` o `false`
`getMethod()`                    | Nome del metodo della richiesta      | `'POST'`
`getHttpHeader('Server')`        | Valore di un'intestazione HTTP       | `'Apache/2.0.59 (Unix) DAV/2 PHP/5.1.6'`
`getCookie('foo')`               | Valore di un cookie                  | `'bar'`
`isXmlHttpRequest()`*            | È una richiesta Ajax?                | `true`
`isSecure()`                     | È una richiesta SSL?                 | `true`
**Parametri della richiesta**    |                                      |
`hasParameter('foo')`            | Questo parametro è nella richiesta?  | `true`
`getParameter('foo')`            | Valore di un parametro               | `'bar'`
`getParameterHolder()->getAll()` | Array  dei parametri della richiesta |
**Informazioni relative a URI**  |                                      |
`getUri()`                       | URI completo                         | `'http://localhost/frontend_dev.php/miomodulo/miaazione'`
`getPathInfo()`                  | Path info                            | `'/miomodulo/miaazione'`
`getReferer()`**                 | Referrer                             | `'http://localhost/frontend_dev.php/'`
`getHost()`                      | Host name                            | `'localhost'`
`getScriptName()`                | Front controller path e nome         | `'frontend_dev.php'`
**Informazioni Client Browser**  |                                      |
`getLanguages()`                 | Array delle lingue accettate         | `Array( [0] => fr [1] => fr_FR [2] => en_US [3] => en )`
`getCharsets()`                  | Array dei charset accettati          | `Array( [0] => ISO-8859-1 [1] => UTF-8 [2] => * )`
getAcceptableContentTypes()      | Array dei content type accettati     | `Array( [0] => text/xml [1] => text/html )`

`*` *Funziona con prototype, Prototype, Mootools e jQuery*

`**` *A volte bloccato dai proxy*

Non sarà necessario preoccuparsi del fatto che i propri server supportino le variabili `$_SERVER` o `$_ENV`, oppure dei valori predefiniti o di problemi di compatibilità a livello server: i metodi `sfWebRequest` si occuperanno di tutto. Inoltre, i loro nomi sono così espliciti da fare in modo che non sia più necessario controllare la documentazione di PHP per vedere come recuperare dei dati dalla richiesta.

Sessione utente
---------------

Symfony gestisce automaticamente le sessioni utente ed è in grado di mantenere dati in modo persistente tra le varie richieste degli utenti. Utilizza i meccanismi di gestione delle sessioni inclusi in PHP e li migliora per renderli più configurabili e facili da usare.

### Accedere alla sessione utente

L'oggetto di sessione per l'utente corrente è accessibile nell'azione grazie al metodo `getUser()` ed è un'istanza della classe `sfUser`. Tale classe mette a disposizione un contenitore di parametri, che offre la possibilità di memorizzare ogni attributo dell'utente al suo interno. Questi dati saranno disponibili per le altre richieste fino alla fine della sessione utente, come mostrato nel listato 6-14. Gli attributi dell'utente possono memorizzare ogni tipo di dato (stringhe, array, array associativi). Essi possono essere impostati per ogni singolo utente, anche nel caso in qui questo non fosse identificato.

Listato 6-14 - L'oggetto `sfUser` può contenere attributi utenti personalizzati tra le richieste

    [php]
    class miomoduloActions extends sfActions
    {
      public function executeFirstPage($request)
      {
        $nickname = $request->getParameter('nickname');

        // Memorizza dati nella sessione utente
        $this->getUser()->setAttribute('nickname', $nickname);
      }

      public function executeSecondPage()
      {
        // Recupera dati dalla sessione utente con un valore predefinito
        $nickname = $this->getUser()->getAttribute('nickname', 'Anonimo');
      }
    }

>**CAUTION**
>È possibile memorizzare oggetti nella sessione utente, ma è una pratica fermamente sconsigliata. Questo perché l'oggetto sessione viene serializzato tra le richieste. Quando l'oggetto viene deserializzato, la classe degli oggetti memorizzati deve essere ancora caricata e spesso non è così. Inoltre potrebbero esserci degli oggetti "scaduti", nel caso in cui si fossero memorizzati oggetti di Propel o Doctrine.

Come molti altri getter in symfony, il metodo `getAttribute()` accetta un secondo parametro per specificare il valore predefinito, da utilizzare nel caso in cui l'attributo non fosse definito. Per verificare che un attributo sia stato definito per un utente, si può usare il metodo `hasAttribute()`. Gli attributi sono memorizzati in un contenitore di parametri, a cui si può accedere con il metodo `getAttributeHolder()`. Questo permette una semplice pulizia degli attributi degli utenti con i soliti metodi dei contenitori di parametri, come mostrato nel listato 6-15.

Listato 6-15 - Rimozione di dati dalla sessione utente

    [php]
    class miomoduloActions extends sfActions
    {
      public function executeRemoveNickname()
      {
        $this->getUser()->getAttributeHolder()->remove('nickname');
      }

      public function executeCleanup()
      {
        $this->getUser()->getAttributeHolder()->clear();
      }
    }

Gli attributi della sessione utente sono disponibili anche nei template, tramite la variabile `$sf_user`, che contiene l'attuale oggetto `sfUser`, come mostrato nel listato 6-16.

Listato 6-16 - Anche i template hanno accesso agli attributi della sessione utente

    [php]
    <p>
      Hello, <?php echo $sf_user->getAttribute('nickname') ?>
    </p>

### Attributi flash

Un problema ricorrente con gli attributi utente riguarda la pulizia della sessione stessa, una volta che l'attributo non sia più necessario. Per esempio, si potrebbe voler mostrare una conferma dopo l'aggiornamento di alcuni dati tramite un form. Dato che l'azione che si occupa di gestire il form esegue un redirect, l'unico modo per passare informazioni da questa azione a quella in cui si è rediretti è quello di memorizzare tali informazioni nella sessione utente. Una volta che il messaggio di conferma è stato visualizzato, è necessario rimuovere l'attributo, altrimenti rimarrà nella sessione fino a quando non sarà scaduta.

L'attributo flash è un attributo effimero che può essere definito e dimenticato, consci del fatto che scomparirà dopo la successiva richiesta, lasciando così la sessione utente pulita per il futuro. Nell'azione, un attributo flash si definisce così:

    [php]
    $this->getUser()->setFlash('notice', $value);

Il template verrà reso e mostrato all'utente, che farà poi una nuova richiesta a un'altra azione. In questa seconda azione, basta semplicemente recuperare il valore dell'attributo flash, in questo modo:

    [php]
    $value = $this->getUser()->getFlash('notice');

Poi ci si può dimenticare di tutto. Dopo la consegna di questa seconda pagina, l'attributo flash `notice` verrà eliminato. E anche se non fosse necessario durante questa seconda azione, il flash verrebbe comunque eliminato dalla sessione.

Per accedere a un attributo flash in un template, utilizzare l'oggetto `$sf_user`:

    [php]
    <?php if ($sf_user->hasFlash('notice')): ?>
      <?php echo $sf_user->getFlash('notice') ?>
    <?php endif; ?>

o semplicemente:

    [php]
    <?php echo $sf_user->getFlash('notice') ?>

Gli attributi flash sono un modo pulito per passare informazioni alla richiesta successiva.

### Gestione delle sessioni

La funzionalità di gestione delle sessioni di symfony maschera completamente la memorizzazione degli ID di sessione lato client e lato server nei confronti dello sviluppatore. Tuttavia, nel caso in cui si volesse modificare il comportamento predefinito dei meccanismi di gestione delle sessioni, si sappia che è comunque possibile. Questa è una cosa principalmente per utenti avanzati.

Lato client, le sessioni sono gestite da cookie. Il cookie di sessione di symfony è chiamato `symfony`, ma è possibile cambiare questo nome modificando il file di configurazione `factories.yml`, come mostrato nel listato 6-17.

Listato 6-17 - Modificare il nome del cookie di sessione, in `apps/frontend/config/factories.yml`

    all:
      storage:
        class: sfSessionStorage
        param:
          session_name: nome_del_mio_cookie

>**TIP**
>La sessione viene avviata (tramite la funzione PHP `session_start()`) solo se il parametro `auto_start` è impostato a true in `factories.yml` (è il valore predefinito). Se si volesse far partire la sessione utente in modo manuale, basterebbe disabilitare questa impostazione del factory `storage`.

La gestione delle sessioni di symfony è basata sulle sessioni di PHP. Questo significa che nel caso in cui si volesse far gestire le sessioni lato client dai parametri URL invece che dai cookie, basterebbe cambiare l'impostazione `use_trans_sid` nel file php.ini. Questa è un'impostazione non raccomandata.

    session.use_trans_sid = 1

Lato server, symfony memorizza le sessioni utente su file come comportamento predefinito. È possibile memorizzarle sul database, cambiando il valore del parametro `class` nel file `factories.yml` come mostrato nel listato 6-18.

Listato 6-18 - Cambiare modalità di memorizzazione della sessione, in `apps/frontend/config/factories.yml`

    [yml]
    all:
      storage:
        class: sfMySQLSessionStorage
        param:
          db_table:    session              # Nome della tabella che contiene le sessioni
          database:    propel               # Nome della connessione al database da usare
          # Optional parameters
          db_id_col:   sess_id              # Nome della colonna che contiene l'id di sessione
          db_data_col: sess_data            # Nome della colonna che contiene i dati di sessione
          db_time_col: sess_time            # Nome della colonna che contiene il timestamp di sessione

L'impostazione `database` definisce quale connessione al database utilizzare. Symfony userà così `databases.yml` (vedere capitolo 8) per determinare i parametri di connessione (host, nome database, utente e password).

La classi disponibili per la memorizzazione della sessione sono `sfCacheSessionStorage`, `sfMySQLSessionStorage`, `sfMySQLiSessionStorage`, `sfPostgreSQLSessionStorage` e `sfPDOSessionStorage`; l'ultima è quella da preferire. Per disabilitare totalmente la memorizzazione della sessione, si può utilizzare la classe `sfNoStorage`.

Le sessioni scadono automaticamente dopo 30 minuti. Questa impostazione predefinita può essere modificata per ogni ambiente nello stesso file di configurazione `factories.yml`, questa volta però nel factory `user`, come mostrato nel listato 6-19.

Listato 6-19 - Modificare la durata delle sessioni, in `apps/frontend/config/factories.yml`

    [yml]
    all:
      user:
        class:       myUser
        param:
          timeout:   1800           # Durata delle sessioni in secondi

Per conoscere più a fondo i factory, fare riferimento al capitolo 19.

Sicurezza delle azioni
----------------------

L'abilità di eseguire un'azione può essere ristretta a utenti con specifici privilegi. Gli strumenti messi a disposizione da symfony per questo scopo permettono la creazione di applicazioni sicure, in cui gli utenti devono essere autenticati prima di poter accedere alle funzionalità o a parti dell'applicazione. Mettere in sicurezza un'applicazione richiede due fasi: dichiarare i requisiti di sicurezza per ogni azione e autenticare gli utenti con determinati privilegi, in modo da permettergli l'accesso a queste azioni sicure.

### Restrizioni d'accesso

Prima di essere eseguita, ogni azione passa attraverso un filtro speciale, che controlla se l'utente corrente è in possesso dei privilegi per accedere all'azione richiesta. In symfony i privilegi sono composti da due parti:

  * Le azioni sicure richiedono che l'utente sia autenticato.
  * Le credenziali sono determinati privilegi di sicurezza, che permettono l'organizzazione della sicurezza in gruppi.

Si può restringere l'accesso a un'azione semplicemente creando e modificando un file di configurazione YAML, chiamato `security.yml`, nella cartella `config/` del modulo. In questo file si possono specificare i requisiti di sicurezza che l'utente deve soddisfare per ogni singola azione o per tutte le azioni. Il listato 6-20 mostra un file `security.yml` d'esempio.

Listato 6-20 - Impostare le restrizioni d'accesso, in `apps/frontend/modules/miomodulo/config/security.yml`

    read:
      is_secure:   false       # Tutti gli utenti possono richiedere l'azione di lettura

    update:
      is_secure:   true        # L'azione di update è disponibile solo a utenti autenticati

    delete:
      is_secure:   true        # Solo per utenti autenticati
      credentials: admin       # Con credenziali admin

    all:
      is_secure:  false        # false è comunque il valore predefinito

Le azioni non sono sicure in modo predefinito, quindi quando non è presente un `security.yml` o non viene menzionata nessuna azione in esso, le azioni sono accessibili a tutti. Nel caso in cui esista un `security.yml`, symfony cerca il nome dell'azione richiesta e, se esiste, verifica il soddisfacimento dei requisiti di sicurezza. Ciò che accade quando un utente prova ad accedere a un'azione sicura dipende dalle sue credenziali:

  * Se l'utente è autenticato e detiene le credenziali corrette, l'azione viene eseguita.
  * Se l'utente non viene riconosciuto, viene rimandato all'azione `login` predefinita.
  * Se l'utente viene riconosciuto, ma non detiene le credenziali necessarie, viene rimandato all'azione `secure` predefinita, mostrata in figura 6-1.

Le pagine predefinite `login` e `secure` sono molto semplici, molto probabilmente si avrà la necessità di personalizzarle. È possibile configurare quali azioni chiamare in caso di privilegi insufficienti nell'applicazione nel file `settings.yml`, cambiando il valore delle proprietà mostrate nel listato 6-21.

Figure 6-1 - La pagina secure predefinita

![La pagina secure predefinita](http://www.symfony-project.org/images/book/1_4/F0601.jpg "La pagina secure predefinita")

Listato 6-21 - Le azioni di sicurezza predefinite sono definite in in `apps/frontend/config/settings.yml`

    all:
      .actions:
        login_module:  default
        login_action:  login

        secure_module: default
        secure_action: secure

### Assegnare l'accesso

Per accedere ad azione riservate gli utenti devono essere autenticati e/o possedere alcune credenziali. Estendere i privilegi di un utente è permesso dai metodi dell'oggetto `sfUser`. Lo stato di autenticazione di un utente è impostato dal metodo `setAuthenticated()` e può essere verificato con `isAuthenticated()`. Il listato 6-22 mostra un semplice esempio di autenticazione.

Listato 6-22 - Impostare lo stato di autenticazione di un utente

    [php]
    class mioAccountActions extends sfActions
    {
      public function executeLogin($request)
      {
        if ($request->getParameter('login') === 'pippo')
        {
          $this->getUser()->setAuthenticated(true);
        }
      }

      public function executeLogout()
      {
        $this->getUser()->setAuthenticated(false);
      }
    }

Le credenziali sono leggermente più complesse da utilizzare, dato che si possono compiere diverse azioni su di esse, come verifica, aggiunta, rimozione e reimpostazione. Il listato 6-23 descrive i metodi della classe `sfUser`.

Listato 6-23 - Lavorare con le credenziali utente nell'azione

    [php]
    class mioAccountActions extends sfActions
    {
      public function executeDoThingsWithCredentials()
      {
        $user = $this->getUser();

        // Aggiungere una o più credenziali
        $user->addCredential('pippo');
        $user->addCredentials('pippo', 'pluto');

        // Verificare che l'utente abbia una credenziale
        echo $user->hasCredential('pippo');                         // =>   true

        // Verificare che l'utente abbia entrambe le credenziali
        echo $user->hasCredential(array('pippo', 'pluto'));         // =>   true

        // Verificare che l'utente abbia una delle credenziali
        echo $user->hasCredential(array('pippo', 'pluto'), false);  // =>   true

        // Rimuovere una credenziale
        $user->removeCredential('pippo');
        echo $user->hasCredential('pippo');                         // =>   false

        // Rimuovere tutte le credenziali (utile nel processo di logout)
        $user->clearCredentials();
        echo $user->hasCredential('pluto');                         // =>   false
      }
    }

Se un utente ha la credenziale `pippo`, sarà in grado di accedere alle azioni per le quali il `security.yml` richiede tale credenziale. Le credenziali possono anche essere utilizzate per mostrare nei template contenuti solo agli utenti autorizzati, come mostrato nel listato 6-24.

Listato 6-24 - Lavorare con le credenziali utenti in un template

    [php]
    <ul>
      <li><?php echo link_to('sezione1', 'content/section1') ?></li>
      <li><?php echo link_to('sezione2', 'content/section2') ?></li>
      <?php if ($sf_user->hasCredential('sezione3')): ?>
        <li><?php echo link_to('sezione3', 'content/section3') ?></li>
      <?php endif; ?>
    </ul>

Come per lo stato di autenticato, le credenziali sono spesso assegnate all'utente durante il processo di login. Ecco perché l'oggetto `sfUser` viene spesso esteso per aggiungere i metodi di login e logout, in modo da impostare lo stato di sicurezza in un posto centralizzato.

>**TIP**
>Tra i plugin di symfony [`sfGuardPlugin`](http://www.symfony-project.org/plugins/sfGuardPlugin) e [`sfDoctrineGuardPlugin`](http://www.symfony-project.org/plugins/sfDoctrineGuardPlugin) estendono la classe della sessione per semplificare login e logout. Fare riferimento al capitolo 17 per maggiori informazioni.

### Credenziali complesse

La sintassi YAML utilizzata nel file `security.yml` permette di restringere l'accesso agli utenti in possesso di una combinazione di credenziali utilizzando associazioni di tipo AND o OR. Con la combinazione di queste si può costruire un complesso workflow e sistema di gestione dei privilegi, come per esempio il back-office di un content management system (CMS) accessibile solo agli utenti con credenziali amministrative, dove gli articoli possono essere editati solo da utenti con la credenziale `editor` e pubblicati solo da quelli con la credenziale `publisher`. Il listato 6-25 mostra quest'esempio.

Listato 6-25 - Sintassi per la combinazione di credenziali

    editArticle:
      credentials: [ admin, editor ]              # admin AND editor

    publishArticle:
      credentials: [ admin, publisher ]           # admin AND publisher

    userManagement:
      credentials: [[ admin, superuser ]]         # admin OR superuser

Ogni volta che si aggiunge un livello di parentesi quadre, l'operatore logico cambia da AND a OR. In questo modo si possono creare combinazioni di credenziali molto complesse, come questa:

    credentials: [[root, [supplier, [owner, quasiowner]], accounts]]
                 # root OR (supplier AND (owner OR quasiowner)) OR accounts

Filtri
------

Il processo di sicurezza può essere interpretato come un filtro dal quale devono passare tutte le richieste prima di eseguire l'azione associata. In funzione di alcuni test eseguiti nel filtro l'esecuzione della richiesta viene modificata, per esempio cambiando l'azione eseguita (`default`/`secure` invece dell'azione richiesta nel caso in cui il filtro di sicurezza lo richieda). Symfony estende quest'idea alle classi di filtri. Si può specificare un numero qualsiasi di filtri da eseguire prima dell'esecuzione dell'azione o prima di restituire la risposta, ripetendolo per ogni richiesta. I filtri possono essere visti come un modo per impacchettare del codice, come si fa con `preExecute()` and `postExecute()`, ma a un livello più alto (per l'intera applicazione invece che per un singolo modulo).

### La catena dei filtri

Symfony in realtà vede il processo di una richiesta come una catena di filtri. Quando una richiesta viene ricevuta dal framework, il primo filtro (che è sempre `sfRenderingFilter`) viene eseguito. A un certo punto, questo chiama il prossimo filtro nella catena, poi il successivo e via dicendo. Quando l'ultimo filtro (che è sempre `sfExecutionFilter`) viene eseguito, quello procedente può terminare e via così fino al filtro di rendering. La figura 6-3 illustra l'idea di fondo con un diagramma di sequenza, utilizzando una piccola e ipotetica catena di filtri (quella reale ne contiene molti di più).

Figure 6-3 - Catena di filtri d'esempio

![Catena di filtri d'esempio](http://www.symfony-project.org/images/book/1_4/F0603.png "Catena di filtri d'esempio")

Questo processo giustifica la struttura delle classi dei filtri. Tutte quante estendono la classe `sfFilter` e contengono un metodo `execute()`, che si aspetta un oggetto di tipo `$filterChain` come parametro. A un certo punto, in questo in questo metodo il filtro passa il controllo al filtro successivo, invocando`$filterChain->execute()`.
Confrontare il listato 6-26 per un esempio. I filtri sono quindi divisi principalmente in due parti:

  * Il codice prima della chiamata a `$filterChain->execute()` viene eseguito prima dell'esecuzione dell'azione.
  * Il codice dopo la chiamata a `$filterChain->execute()` viene eseguito dopo l'esecuzione dell'azione e prima del rendering.

Listato 6-26 - Struttura di una classe filtro

    [php]
    class myFilter extends sfFilter
    {
      public function execute ($filterChain)
      {
        // Codice da eseguire prima dell'esecuzione dell'azione
        ...

        // Esegue il prossimo filtro della catena
        $filterChain->execute();

        // Codice da eseguire dopo l'esecuzione dell'azione e prima del rendering
        ...
      }
    }

La catena dei filtri predefinita è impostata in un file di configurazione dell'applicazione chiamato `filters.yml` e viene mostrata nel listato 6-27. Questo file elenca i filtri da eseguire per ogni richiesta.

Listato 6-27 - Catena dei filtri predefinita, in `frontend/config/filters.yml`

    rendering: ~
    security:  ~

    # Generalmente, si vorranno inserire i propri filtri qui

    cache:     ~
    execution: ~

Queste dichiarazioni non hanno parametri (il carattere tilde `~` significa "null" in YAML) perché ereditano i parametri definiti nel nucleo di symfony. Nel nucleo symfony definisce le impostazioni `class` e `param` per ognuno di questi filtri. Per esempio il listato 6-28 mostra i parametri predefiniti per il filtro `rendering`.

Listato 6-28 - Parametri predefiniti per il filtro rendering, in `sfConfig::get('sf_symfony_lib_dir')/config/config/filters.yml`

    rendering:
      class: sfRenderingFilter   # Classe filtro
      param:                     # Parametri dei filtri
        type: rendering

Lasciando il valore nullo (`~`) nel file `filters.yml` dell'applicazione, si comunica a symfony l'intenzione di volere applicare il filtro con le impostazioni predefinite dal nucleo.

La catena dei filtri può essere personalizzata in varie maniere:

  * Disabilitare alcuni filtri dalla catena aggiungendo il parametro `enabled: false`. Se per esempio si volesse disabilitare il filtro `security` basterebbe scrivere:

        security:
          enabled: false

  * Non va rimossa la dichiarazione dal file `filters.yml` per disabilitare un filtro; symfony solleverebbe un'eccezione in questo caso.
  * Aggiungere le proprie dichiarazioni in un certo punto della catena (di solito dopo il filtro `security`) per includere un filtro personalizzato (come vedremo nella prossima sezione). Prestare attenzione al fatto che il filtro `rendering` sia sempre in prima posizione, così come il filtro  `execution` sia in ultima posizione nella catena dei filtri.
  * Sovrascrivere la classe predefinita e dei parametri dei filtri predefiniti (in particolare per modificare il sistema di sicurezza e utilizzare i propri filtri).

### Costruire i propri filtri

Costruire un proprio filtro è una cosa piuttosto semplice. Creare una classe definita in modo simile a quanto mostrato nel listato 6-26 e posizionarla in una delle cartelle `lib/` del progetto per sfruttare i vantaggi dell'autocaricamento.

Dato che un'azione può fare forward o redirect a un'altra azione e conseguentemente rilanciare l'intera catena di filtri, potrebbe essere necessario limitare l'esecuzione dei propri filtri solamente alla prima azione chiamata dalla richiesta. Il metodo `isFirstCall()` della classe `sfFilter` restituisce un booleano per questo scopo. Questa chiamata ha senso solamente prima dell'esecuzione dell'azione.

Questi concetti saranno più chiari con un esempio. Il listato 6-29 mostra un filtro utilizzato per l'auto-login degli utenti con un cookie specifico `MioSito`, che supponiamo sia creato dall'azione di login. È un modo tanto rudimentale quanto funzionante per implementare la funzionalità "ricordami" offerta nei moduli di login.

Listato 6-29 - Classe filtro d'esempio, salvata in `apps/frontend/lib/rememberFilter.class.php`

    [php]
    class rememberFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        // Esegue questo filtro solo una volta
        if ($this->isFirstCall())
        {
          // I filtri non hanno accesso diretto agli oggetti richiesta e utente
          // Sarà necessario ricorrere all'utilizzo dell'oggetto context
          $request = $this->getContext()->getRequest();
          $user    = $this->getContext()->getUser();

          if ($request->getCookie('MioSito'))
          {
            // entra
            $user->setAuthenticated(true);
          }
        }

        // Esegue il filtro successivo
        $filterChain->execute();
      }
    }

In alcuni casi, invece che continuare l'esecuzione della catena di filtri, potrebbe essere necessario un forward a una specifica azione alla fine del filtro. `sfFilter` non ha un metodo `forward()`, tuttavia `sfController` lo ha, quindi questo può essere fatto semplicemente chiamando:

    [php]
    return $this->getContext()->getController()->forward('miomodulo', 'myAction');

>**NOTE**
>La classe `sfFilter` ha un metodo `initialize()` eseguito alla creazione dell'oggetto filtro. È possibile sovrascrivere il metodo nei filtri personalizzati, nel caso in cui fosse necessario lavorare con i parametri dei filtri (definiti in `filters.yml`, come si vedrà in seguito) in modo personale.

### Attivazione dei filtri e parametri

Creare il file di un filtro non è una condizione sufficiente per attivarlo. Il filtro va aggiunto alla catena di filtri, va dichiarata la classe in `filters.yml`, raggiungibile nella cartella `config/` dell'applicazione o del modulo, come mostrato nel listato 6-30.

Listato 6-30 - File d'esempio per l'attivazione di un filtro, salvato in `apps/frontend/config/filters.yml`

    rendering: ~
    security:  ~

    remember:                 # I filtri hanno bisogno di nomi univoci
      class: rememberFilter
      param:
        cookie_name: MyWebSite
        condition:   %APP_ENABLE_REMEMBER_ME%

    cache:     ~
    execution: ~

Quando attivo il filtro viene eseguito per ogni singola richiesta. Il file di configurazione di un filtro può contenere una o più definizioni di parametri sotto la chiave `param`. La classe filtro è in grado di recuperare il valore di questi parametri con il metodo `getParameter()`. Il listato 6-31 dimostra come recuperare il valore di un parametro di un filtro.

Listato 6-31 - Recuperare il valore di un parametro, in `apps/frontend/lib/rememberFilter.class.php`

    [php]
    class rememberFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        // ...

        if ($request->getCookie($this->getParameter('nome_cookie')))
        {
          // ...
        }

        // ...
      }
    }

Il parametro `condition` viene verificato dalla catena dei filtri per capire se il filtro in questione debba essere eseguito. Quindi le dichiarazioni dei filtri possono contare su una configurazione dell'applicazione proprio uguale a quella del listato 6-30. Il filtro remember verrà eseguito solamente se nell'applicazione il file `app.yml` contiene questo:

    all:
      enable_remember_me: true

### Filtri d'esempio

La funzionalità dei filtri è utile per ripetere del codice per ogni azione. Per esempio, se si utilizzasse un sistema di statistiche esterno, molto probabilmente sarebbe necessario inserire una porzione di codice in grado di richiamare uno script tracker esterno in ogni pagina. Questo codice potrebbe essere inserito nel layout globale, tuttavia in questo modo sarebbe attivo per tutta l'applicazione. Altrimenti si potrebbe inserire in un filtro, come mostrato nel listato 6-32, e attivato per ogni singolo modulo che lo richieda.

Listato 6-32 - Filtro Google Analytics

    [php]
    class sfGoogleAnalyticsFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        // Niente da fare prima dell'azione
        $filterChain->execute();

        // Completa la risposta con il codice del tracker
        $googleCode = '
    <script src="http://www.google-analytics.com/urchin.js"  type="text/javascript">
    </script>
    <script type="text/javascript">
      _uacct="UA-'.$this->getParameter('google_id').'";urchinTracker();
    </script>';
        $response = $this->getContext()->getResponse();
        $response->setContent(str_ireplace('</body>', $googleCode.'</body>',$response->getContent()));
       }
    }

Attenzione però, questo filtro non è perfetto dato che non dovrebbe aggiungere il codice del tracker nelle risposte non HTML.

Un altro esempio potrebbe essere rappresentato da un filtro che cambia le richieste a SSL nel caso non lo fossero già, per rendere più sicura la comunicazione, come nel listato 6-33.

Listato 6-33 - Filtro per comunicazione sicura

    [php]
    class sfSecureFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        $context = $this->getContext();
        $request = $context->getRequest();

        if (!$request->isSecure())
        {
          $secure_url = str_replace('http', 'https', $request->getUri());

          return $context->getController()->redirect($secure_url);
          // We don't continue the filter chain
        }
        else
        {
          // La richiesta è già sicura, si può continuare
          $filterChain->execute();
        }
      }
    }

I filtri vengono utilizzati in modo massivo nel plug-in visto che permettono di estendere le funzionalità di un'applicazione in modo completo. Fare riferimento al capitolo 17 per saperne di più sui plugin.

Configurazione dei moduli
-------------------------

Alcuni comportamenti dei moduli dipendono dalla configurazione. Per modificarli è necessario creare un file `module.yml` nella cartella `config/` del modulo e definirvi le impostazioni per ogni singolo ambiente (oppure nell'intestazione `all:` per tutti gli ambienti). Il listato 6-34 mostra un esempio di file `module.yml` per il modulo `miomodulo`.

Listato 6-34 - Configurazione di un modulo, in `apps/frontend/modules/miomodulo/config/module.yml`

    all:                  # Per tutti gli ambienti
      enabled:            true
      is_internal:        false
      view_class:         sfPHP
      partial_view_class: sf

Il parametro `enabled` permette di disabilitare tutte le azioni di un modulo. Tutte le azioni verranno redirette all'azione `module_disabled_module`/`module_disabled_action` (come definito in `settings.yml`).

Il parametro `is_internal` permette di limitare l'esecuzione di tutte le azioni di un modulo solamente a chiamate interne. Per esempio questo è utile per azioni riguardanti le email che devono poter essere invocate da altre azioni, per mandare messaggi e-mail, ma non dall'esterno.

Il parametro `view_class` definisce la classe della vista. Deve ereditare da `sfView`. Sovrascrivere questo parametro consente di usare altri sistemi di viste, con altri motori di template, come Smarty.

Il parametro `partial_view_class` definisce la classe della vista utilizzata per i partial del modulo in questione. Deve ereditare da `sfPartialView`.

Sommario
--------

In symfony il livello del controllore è diviso in due parti: il front controller, l'unico punto d'accesso per l'applicazione in un dato ambiente, e le azioni che contengono la logica delle pagine. Un'azione ha l'abilità di determinare come verrà eseguita la sua vista restituendo una delle costanti `sfView`. All'interno di un'azione si possono manipolare i diversi elementi del context, inclusi l'oggetto della richiesta (`sfRequest`) e l'oggetto della sessione utente corrente (`sfUser`).

Combinando assieme la potenza dell'oggetto sessione, l'oggetto azione, le configurazioni di sicurezza, symfony mette a disposizione un completo sistema di sicurezza con restrizione sull'accesso e sistema di credenziali associato. Se i metodi `preExecute()` e `postExecute()` sono stati pensati per il riutilizzo del codice all'interno di un modulo, i filtri permettono lo stesso grado di riutilizzo per tutte le applicazioni facendo eseguire codice del controllore per ogni singola richiesta.
