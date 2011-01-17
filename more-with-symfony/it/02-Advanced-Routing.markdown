Utilizzo avanzato delle rotte
=============================

*di Ryan Weaver*

Nel core, il framework delle rotte è la mappa che collega ogni url a una specifica
ubicazione interna di un progetto symfony e viceversa. Si possono facilmente
creare dei meravigliosi URL che rimangono completamente indipendenti dalla
logica dell'applicazione. Grazie ai progressi che ha compiuto nelle sue versioni
recenti, ora anche il framework dei form si è molto migliorato.

In questo capitolo si illustra come creare una semplice applicazione web in cui
ogni cliente utilizza un sottodominio separato (es. `cliente1.miodominio.it` e
`cliente2.miodominio.it`). Estendendo il framework delle rotte, la cosa diventa
abbastanza semplice.

>**NOTE**
>Questo capitolo richiede per il progetto l'utilizzo dell'ORM Doctrine.

Preparazione del progetto: un CMS per molti clienti
---------------------------------------------------

In questo progetto, una società immaginaria - Sympal Builder - vuole creare un
CMS in modo che i suoi clienti possano costruire siti web come sottodomini di
`sympalbuilder.com`. In particolare, il cliente XXX può vedere il suo sito su
`xxx.sympalbuilder.com` e usare l'area admin su `xxx.sympalbuilder.com/backend.php`.

>**NOTE**
>Il nome `Sympal` è stato preso in prestito dal content management framework (CMF)
>[Sympal](http://www.sympalphp.org/), realizzato da Jonathan Wage con symfony.

Questo progetto ha due requisiti fondamentali:

  * Gli utenti dovrebbero essere in grado di creare pagine e specificare il titolo, il contenuto
    e l'URL di queste pagine.

  * L'intera applicazione dovrebbe essere costruita all'interno di un progetto symfony che
    gestisce il frontend e il backend di tutti i siti dei clienti, determinando il cliente
    e caricando sulla base del sottodominio i dati corretti.

>**NOTE**
>Per creare questa applicazione, il server dovrà essere impostato per indirizzare tutti
>i sottodomini `*.sympalbuilder.com` alla stessa document root, la cartella web
>del progetto symfony.

### Lo schema e i dati

Il database del progetto è formato dagli oggetti `Client` e `Page`. Ciascun
`Client` rappresenta un sito del sottodominio ed è costituito da molti oggetti `Page`.

    [yml]
    # config/doctrine/schema.yml
    Client:
      columns:
        name:       string(255)
        subdomain:  string(50)
      indexes:
        subdomain_index:
          fields:   [subdomain]
          type:     unique

    Page:
      columns:
        title:      string(255)
        slug:       string(255)
        content:    clob
        client_id:  integer
      relations:
        Client:
          alias:        Client
          foreignAlias: Pages
          onDelete:     CASCADE
      indexes:
        slug_index:
          fields:   [slug, client_id]
          type:     unique

>**NOTE**
>Anche se gli indici su ciascuna tabella non sono necessari, sono una buona idea,
>perché l'applicazione farà frequenti query su queste colonne.

Per portare in vita il progetto, inserire i seguenti dati di test nel
file `data/fixtures/fixtures.yml`:

    [yml]
    # data/fixtures/fixtures.yml
    Client:
      client_pete:
        name:      Pete's Pet Shop
        subdomain: pete
      client_pub:
        name:      City Pub and Grill
        subdomain: citypub

    Page:
      page_pete_location_hours:
        title:     Location and Hours | Pete's Pet Shop
        content:   We're open Mon - Sat, 8 am - 7pm
        slug:      location
        Client:    client_pete
      page_pub_menu:
        title:     City Pub And Grill | Menu
        content:   Our menu consists of fish, Steak, salads, and more.
        slug:      menu
        Client:    client_pub

I dati di test inseriscono due siti web, ciascuno con una pagina.
L'URL completo di ogni pagina è definito sia dalla colonna `subdomain`
dell'oggetto `Client`, che dalla colonna `slug` dell'oggetto `Page`.

    http://pete.sympalbuilder.com/location
    http://citypub.sympalbuilder.com/menu

### Le rotte

Ogni pagina del sito web Sympal Builder corrisponde direttamente  a un oggetto
del modello `Page`, che definisce il titolo e il contenuto dell'output.
Per collegare ogni specifico URL a un oggetto `Page`, creare un oggetto rotta
di tipo `sfDoctrineRoute` che usa il campo `slug`. Il seguente codice cercherà
automaticamente nel database un oggetto `Page` con un campo `slug` che
corrisponda all'url:

    [yml]
    # apps/frontend/config/routing.yml
    page_show:
      url:        /:slug
      class:      sfDoctrineRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show

La rotta di cui sopra troverà la corretta corrispondenza per la pagina
`http://pete.sympalbuilder.com/location` con l'oggetto `Page`. Purtroppo
la rotta sopra potrebbe *anche* combaciare con l'URL `http://pete.sympalbuilder.com/menu`,
nel senso che nel menu del ristorante sarà mostrato sito web di Pete! In questo momento
la rotta non è a conoscenza dell'importanza dei sottodomini del cliente.

Per rendere funzionale l'applicazione, la rotta deve essere "intelligente". Essa
dovrebbe trovare il `Page` corretto basandosi sullo `slug` *e* sul client_id`,
che può essere determinato dalla corrispondenza dell'host (es. `pete.sympalbuilder.com`)
con la colonna `subdomain` del modello `Client`. Per poterlo fare, si utilizzerà il
framework delle rotte, creando una classe di rotte personalizzata.

Prima, però, bisogna chiarire alcuni retroscena sul funzionamento del sistema di routing.

Come funziona il sistema delle rotte
------------------------------------

Una "rotta" in symfony è un oggetto di tipo ~`sfRoute`~, che fa due importanti
lavori:

 * Generare un URL. Per esempio, se si passa al metodo `page_show` un parametro
   `slug`, dovrebbe essere in grado di generare un URL reale (es. `/location`).

 * Trovare la corrispondenza con un URL in arrivo. Dato l'URL di una richiesta in
   arrivo, ciascuna rotta deve essere in grado di determinare se l'URL "combacia"
   con i requisiti della rotta.

Le informazioni per le singole rotte sono generalmente configurate all'interno
della cartella config di ciascuna applicazione, collocata in
`app/nomeapplicazione/config/routing.yml`. Ricordiamo che ogni rotta è
*"un oggetto di tipo `sfRoute`"*. Allora, come fanno queste semplici voci YAML
a diventare oggetti di tipo `sfRoute`?

### Gestione della configurazione della cache per le rotte 

Sebbene la maggior parte delle rotte siano definite in un file
YAML, ciascuna voce di questo file al momento della richiesta è trasformata
in un oggetto reale, tramite un particolare tipo di classe chiamato gestore
del configuratore della cache. Il risultato finale è un codice PHP che che
rappresenta ogni rotta dell'applicazione. Anche se la specificità di questo
processo va oltre lo scopo di questo capitolo, ci spostiamo alla fine, nella
versione compilata della rotta `page_show`. Il file compilato si trova in
`cache/nomeapp/nomeamb/config/config_routing.yml.php` per gli specifici
applicazione (nomeapp) e ambiente (nomeamb). Qui di seguito c'è una versione
ridotta del codice presente nella rotta `page_show`:

    [php]
    new sfDoctrineRoute('/:slug', array (
      'module' => 'page',
      'action' => 'show',
    ), array (
      'slug' => '[^/\\.]+',
    ), array (
      'model' => 'Page',
      'type' => 'object',
    ));

>**TIP**
>Il nome della classe di ciascuna rotta è definito dalla chiave `class` presente 
>nel file `routing.yml`. Se non è specificata la chiave `class`, la rotta diventerà
>per impostazione predefinita una classe `sfRoute`. Un'altra classe
>di rotta comune è `sfRequestRoute`, che permette allo sviluppatore di creare delle
>rotte REST. Una lista completa delle opzioni disponibili è presente nella
>[Guida di riferimento a symfony](http://www.symfony-project.org/reference/1_4/it/10-Routing)

### Soddisfare la richiesta in arrivo per una rotta specifica

Uno dei compiti principali del framework delle rotte è quello di trovare la
corrispondenza tra ciascun URL in arrivo e il corretto oggetto per la rotta.
La classe ~`sfPatternRouting`~ rappresenta il nucleo del motore delle rotte ed
è incaricato di questo compito specifico. Nonostante la sua importanza, uno
sviluppatore raramente interagirà direttamente con `sfPatternRouting`.

Per fare l'abbinamento con il percorso corretto, `sfPatternRouting` scorre ogni
`sfRoute` e "chiede" alla rotta se corrisponde all'url in arrivo.
Internamente, questo significa che `sfPatternRouting` chiama il metodo
~`sfRoute::matchesUrl()`~ su ciascun oggetto della rotta. Questo metodo
restituisce `false` se la rotta non corrisponde all'url in entrata.

Tuttavia, se il percorso *non* corrisponde all'URL in entrata, `sfRoute::matchesUrl()`
non si limita a restituire `true`. Invece, il percorso restituisce un array di parametri
che sono fusi nell'oggetto richiesto. Per esempio, l'URL
`http://pete.sympalbuilder.com/location` corrisponde alla rotta `page_show`,
il cui metodo `matchesUrl()` restituisce il seguente array:

    [php]
    array('slug' => 'location')

Queste informazioni vengono poi fuse nell'oggetto della richiesta e questo è il
motivo per cui è possibile accedere alle variabili della rotta (es. `slug`)
dai file delle azioni e da altri posti.

    [php]
    $this->slug = $request->getParameter('slug');

Come si può intuire, sovrascrivere il metodo `sfRoute::matchesUrl()` è
un ottimo modo per estendere e personalizzare una rotta, per poterci fare quasi
qualsiasi cosa.

Creazione di una classe di rotte personalizzata
-----------------------------------------------

Al fine di estendere la rotta `page_show` per trovare una corrispondenza
sulla base del sottodominio degli oggetti `Client`, si creerà una nuova
classe personalizzata per le rotte. Creare un file chiamato `acClientObjectRoute.class.php`
e posizionarlo nella cartella `lib/routing` del progetto (bisognerà
creare questa cartella):

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        return $parameters;
      }
    }

L'unico altro passo da fare è quello di istruire la rotta `page_show` per usare
la classe della rotta. In `routing.yml`, aggiornare la chiave `class` della rotta:

    [yml]
    # apps/fo/config/routing.yml
    page_show:
      url:        /:slug
      class:      acClientObjectRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show

Finora, `acClientObjectRoute` non aggiunge ulteriori funzionalità, ma tutti
i pezzi sono a posto. Il metodo `matchesUrl()` fa due lavori specifici.

### Aggiungere la logica alla rotta personalizzata

Per aggiungere le necessarie funzionalità alla rotta personalizzata, sostituire il
contenuto del file `acClientObjectRoute.class.php` con il seguente.

    [php]
    class acClientObjectRoute extends sfDoctrineRoute
    {
      protected $baseHost = '.sympalbuilder.com';

      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        // restituisce false se baseHost non viene trovato
        if (strpos($context['host'], $this->baseHost) === false)
        {
          return false;
        }

        $subdomain = str_replace($this->baseHost, '', $context['host']);

        $client = Doctrine_Core::getTable('Client')
          ->findOneBySubdomain($subdomain)
        ;

        if (!$client)
        {
          return false;
        }

        return array_merge(array('client_id' => $client->id), $parameters);
      }
    }

La chiamata iniziale a `parent::matchesUrl()` è importante, in quanto passa
attraverso il normale processo di ricerca corrispondenza delle rotte. In
questo esempio, dal momento che l'URL `/location` ha corrispondenza con la
rotta `page_show`, `parent::matchesUrl()` restituirebbe un array contenente la
corrispondenza del parametro `slug`.

    [php]
    array('slug' => 'location')

In altre parole, tutto il lavoro per trovare la corrispondenza delle rotte viene
già fatto per noi, il che permette alla parte rimanente del metodo di focalizzarsi
sulla corrispondenza in base al corretto sottodominio `Client`.

    [php]
    public function matchesUrl($url, $context = array())
    {
      // ...

      $subdomain = str_replace($this->baseHost, '', $context['host']);

      $client = Doctrine_Core::getTable('Client')
        ->findOneBySubdomain($subdomain)
      ;

      if (!$client)
      {
        return false;
      }

      return array_merge(array('client_id' => $client->id), $parameters);
    }

Eseguendo una semplice sostituzione di stringhe, siamo in grado di isolare la
parte sottodominio dell'host e quindi interrogare il database per vedere se uno
degli oggetti  `Client` ha questo sottodominio. Se nessun oggetto cliente
corrisponde al sottodominio, allora viene restituito `false`, indicando
che la richiesta in ingresso non corrisponde alla rotta. In caso contrario, se
c'è un oggetto cliente con il sottodominio corrente, viene aggiunto un parametro
extra, `client_id` nell'array restituito.

>**TIP**
>L'array `$context` passato a `matchesUrl()` è preassegnato con molte
>informazioni utili sulla richiesta corrente, compreso `host`, un
>booleano `is_secure`, `request_uri`, il metodo HTTP `method` e altro.

Come si comporta veramente la rotta personalizzata? Ora la classe
`acClientObjectRoute` fa le seguenti cose:

 * L'`$url` in entrata corrisponderà solo se `host` contiene un sottodominio
   appartenente a uno degli oggetti `Client`.

 * Se c'è corrispondenza con la rotta, verrà restituito un parametro aggiuntivo
   `client_id`, per l'oggetto `Client` con cui è stata trovata corrispondenza e
    infine fuso nei parametri della richiesta.

### Sfruttare la rotta personalizzata

Ora che il parametro `client_id` corretto viene restituito da `acClientObjectRoute`,
abbiamo accesso a esso tramite l'oggetto richiesta. Per esempio, l'azione `page/show`
potrebbe utilizzare il `client_id` per trovare il giusto oggetto `Page`:

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = Doctrine_Core::getTable('Page')->findOneBySlugAndClientId(
        $request->getParameter('slug'),
        $request->getParameter('client_id')
      );

      $this->forward404Unless($this->page);
    }

>**NOTE**
>Il metodo `findOneBySlugAndClientId()` è un tipo di
>[finder magico](http://www.doctrine-project.org/upgrade/1_2#Expanded%20Magic%20Finders%20to%20Multiple%20Fields)
>nuovo in Doctrine 1.2 che esegue una ricerca per oggetti basati su più campi.

Ma il framework delle rotte permette una soluzione ancora più elegante.
In primo luogo, aggiungere il seguente metodo alla classe `acClientObjectRoute`:

    [php]
    protected function getRealVariables()
    {
      return array_merge(array('client_id'), parent::getRealVariables());
    }

Con questo pezzo finale, l'azione può contare completamente sulla rotta per
restituire il giusto oggetto `Page`. L'azione `page/show` può essere ridotta a
una singola linea.

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = $this->getRoute()->getObject();
    }

Senza alcun lavoro supplementare, il codice sopra farà la query per un oggetto
`Page` basata sulle colonne `slug` *e* `client_id`. Inoltre, come tutte le
rotte di oggetti, l'azione sarà automaticamente inoltrata a una pagina 404 se
non verrà trovato l'oggetto corrispondente.

Ma come funziona? Rotte di oggetti come `sfDoctrineRoute`, che estendono la
classe `acClientObjectRoute`, fanno una interrogazione automatica per il relativo
oggetto, in base alle variabili nella chiave `url` della rotta. Ad esempio, la
rotta `page_show`, che contiene la variabile `:slug` nella sua `url`, interroga
per l'oggetto `Page` attraverso la colonna `slug`.

In questa applicazione, tuttavia, la rotta `page_show` deve anche interrogare
per gli oggetti `Page` basati sulla colonna `client_id`. Per fare questo, si
sovrascrive ~`sfObjectRoute::getRealVariables()`~, che è chiamato internamente
per determinare le colonne da utilizzare per l'interrogazione dell'oggetto.
Con l'aggiunta del campo `client_id` a questo array, `acClientObjectRoute`
interroga sulla base delle colonne `slug` e `client_id`.

>**NOTE**
>Le rotte di oggetti ignorano automaticamente le variabili che non corrispondono
>a una vera colonna. Ad esempio, se la chiave URL contiene una variabile `:page`,
>ma non esiste nessuna colonna `page` sulla relativa tabella, la variabile sarà ignorata.

A questo punto, la classe della rotta personalizzata implementa tutto quello
che è stato richiesto, con poco sforzo. Nelle prossime sezioni, si riutilizzerà la
nuova rotta per creare un'area amministrativa specifica per il cliente.

### Generare la rotta corretta

Resta un piccolo problema con il modo in cui la rotta è generata. 
Supponiamo di creare un link a una pagina con il seguente codice:

    [php]
    <?php echo link_to('Locations', 'page_show', $page) ?>

-

    Generated url: /location?client_id=1

Come si può vedere, `client_id` è stato automaticamente aggiunto alla url.
Ciò si verifica perché la rotta tenta di utilizzare tutte le sue variabili
disponibili per generare l'url. Poiché la rotta è a conoscenza sia del
parametro `slug` che del parametro `client_id`, quando genera la rotta usa
entrambi.

Per risolvere questo problema, aggiungere il seguente metodo alla classe
`acClientObjectRoute:

    [php]
    protected function doConvertObjectToArray($object)
    {
      $parameters = parent::doConvertObjectToArray($object);

      unset($parameters['client_id']);

      return $parameters;
    }

Quando l'oggetto della rotta è generato, tenta di recuperare tutte le
informazioni necessarie chiamando `doConvertObjectToArray()`. Per impostazione
predefinita, `client_id` è restituito nell'array `$parameters`. Togliendolo, si
impedisce che venga incluso nella URL generata. Ricordiamo che possiamo
permettercelo, in quanto l'informazione `Client` è contenuta nel sottodominio stesso.

>**TIP**
>È possibile sovrascrivere interamente il processo `doConvertObjectToArray()`
>e gestirlo da soli, aggiungendo un metodo `toParams()` alla classe del modello.
>Questo metodo dovrebbe restituire un array dei parametri che si vuole 
>utilizzare durante la generazione della rotta.

Collezioni di rotte
-------------------

Per terminare l'applicazione Sympal Builder, c'è bisogno di creare uno spazio
per l'amministrazione dove ciascun individuo `Client` è in grado di gestire le
sue `Pages`. Per fare questo, aci sarà bisogno di un insieme di azioni che permetta
di elencare, creare, aggiornare e cancellare oggetti `Page`. Poiché questi tipi di
moduli sono abbastanza comuni, symfony può generare automaticamente il modulo.
Eseguire il seguente task dalla linea di comando, per generare un modulo `pageAdmin`
all'interno di un'applicazione chiamata `backend`:

    $ php symfony doctrine:generate-module backend pageAdmin Page --with-doctrine-route --with-show

Il task sopra genera un modulo con un file di azioni e relativi template,
in grado di fare tutte le modifiche necessarie a qualsiasi oggetto `Page`.
Potrebbero essere apportate molte personalizzazioni al presente CRUD generato,
ma non rientrano nello scopo di questo capitolo.

Ache se i task di cui sopra generano il modulo, si ha ancora bisogno
di creare una rotta per ciascuna azione. Passando l'opzione `--with-doctrine-route`
al task, ciascuna azione viena generata per lavorare con un oggetto rotta.
Questo diminuisce la quantità di codice di ogni azione. Ad esempio l'azione
`edit` contiene una semplice linea:

    [php]
    public function executeEdit(sfWebRequest $request)
    {
      $this->form = new PageForm($this->getRoute()->getObject());
    }

In conclusione, si hanno bisogno delle rotte per le azioni `index`, `new`,
`create`, `edit`, `update` e `delete`. Normalmente, la creazione di queste
rotte in modalità [RESTful](http://it.wikipedia.org/wiki/Representational_State_Transfer)
richiederebbe notevoli configurazioni nel file `routing.yml`.

    [yml]
    pageAdmin:
      url:         /pages
      class:       sfDoctrineRoute
      options:     { model: Page, type: list }
      params:      { module: page, action: index }
      requirements:
        sf_method: [get]
    pageAdmin_new:
      url:        /pages/new
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: new }
      requirements:
        sf_method: [get]
    pageAdmin_create:
      url:        /pages
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: create }
      requirements:
        sf_method: [post]
    pageAdmin_edit:
      url:        /pages/:id/edit
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: edit }
      requirements:
        sf_method: [get]
    pageAdmin_update:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: update }
      requirements:
        sf_method: [put]
    pageAdmin_delete:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: delete }
      requirements:
        sf_method: [delete]
    pageAdmin_show:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: show }
      requirements:
        sf_method: [get]

Per visualizzare queste rotte, usare il task `app:routes`, che mostra un riepilogo
per ciascuna rotta di una specifica applicazione:

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages
    pageAdmin_new    GET    /pages/new
    pageAdmin_create POST   /pages
    pageAdmin_edit   GET    /pages/:id/edit
    pageAdmin_update PUT    /pages/:id
    pageAdmin_delete DELETE /pages/:id
    pageAdmin_show   GET    /pages/:id

### Sostituire le rotte con una collezione di rotte

Fortunatamente, symfony fornisce un modo molto più semplice per specificare
tutte le rotte che appartengono a un tradizionale CRUD. Sostituire l'intero
contenuto del file `routing.yml` con questa semplice rotta.

    [yml]
    pageAdmin:
      class:   sfDoctrineRouteCollection
      options:
        model:        Page
        prefix_path:  /pages
        module:       pageAdmin

Ancora una volta, eseguire il task `app:routes` per visualizzare tutte le rotte.
Come si può vedere, tutte e sette le rotte precedenti esistono ancora.

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages.:sf_format
    pageAdmin_new    GET    /pages/new.:sf_format
    pageAdmin_create POST   /pages.:sf_format
    pageAdmin_edit   GET    /pages/:id/edit.:sf_format
    pageAdmin_update PUT    /pages/:id.:sf_format
    pageAdmin_delete DELETE /pages/:id.:sf_format
    pageAdmin_show   GET    /pages/:id.:sf_format

Le collezioni di rotte sono un tipo speciale di oggetti di rotte, che internamente
rappresentano più di una rotta. La rotta ~`sfDoctrineRouteCollection`~ per esempio,
genera automaticamente le sette più comuni rotte richieste da un CRUD. Dietro
le quinte, `sfDoctrineRouteCollection` non sta facendo altro che creare le stesse
sette rotte precedentemente specificate nel file `routing.yml`. Le collezioni di
rotte, fondamentalmente esistono come scorciatoia per creare un gruppo comune di
rotte.

Creazione di una collezione personalizzata di rotte
---------------------------------------------------

A questo punto, ogni `Client` sarà in grado di modificare i suoi oggetti `Page`
all'interno di una struttura crud attraverso l'URL `/pages`. Purtroppo, ogni
`Client` al momento può vedere e modificare *tutti* gli oggetti `Page`, sia
quelli appartenenti, che quelli non appartenenti a `Client`. Ad esempio,
`http://pete.sympalbuilder.com/backend.php/pages` visualizzerà un elenco con
*entrambe* le pagine delle fixture, la pagina `location` del Pet Shop di Pete
e la pagina `menu` del City Pub.

Per risolvere questo problema, si riutilizza la rotta `acClientObjectRoute` che
era stata creata per il frontend. La classe `sfDoctrineRouteCollection` genera
un gruppo di oggetti `sfDoctrineRoute`. In questa applicazione invece, abbiamo
bisogno di generare un gruppo di oggetti `acClientObjectRoute`.

Per fare questo, c'è bisogno di utilizzare una classe personalizzata di
collezione di rotte. Creare un nuovo file chiamato `acClientObjectRouteCollection.class.php`
e metterlo nella cartella `lib/routing`. Il suo contenuto è incredibilmente semplice:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    class acClientObjectRouteCollection extends sfObjectRouteCollection
    {
      protected
        $routeClass = 'acClientObjectRoute';
    }

La proprietà `$routeClass` definisce la classe che sarà usata durante la creazione
di ciascuna rotta sottostante. Ora che ciascuna di queste rotte sottostanti è 
una rotta `acClientObjectRoute`, l'implementazione è effettivamente fatta. Ad
esempio, `http://pete.sympalbuilder.com/backend.php/pages` ora mostrerà solo *una* pagina:
la pagina `location` del Pet Shop di Pete. Grazie alla classe di rotte
personalizzata, l'azione index restituisce solo oggetti `Page` legati al `Client`
corretto, sulla base del sottodominio della richiesta. Con poche righe di codice,
si è creato un intero modulo backend che può tranquillamente essere utilizzato
da più clienti.

### Un pezzo mancante: creare nuove pagine

Attualmente, nel backend, quando si creano o si modificano oggetti `Page`,
compare un select box `Client`. Invece di consentire agli utenti di scegliere
il `Client` (che sarebbe un rischio per la sicurezza), è meglio impostare
automaticamente il `Client` in base al sottodominio corrente della richiesta.

Per prima cosa, aggiornare l'oggetto `PageForm` presente in `lib/form/PageForm.class.php`.

    [php]
    public function configure()
    {
      $this->useFields(array(
        'title',
        'content',
      ));
    }

Ora, come richiesto, il select box non compare più nel form di `Page`. Tuttavia,
quando vengono creati nuovi oggetti `Page`, il `client_id` non viene mai impostato.
Per risolvere questo problema, impostare manualmente il relativo `Client` in
entrambe le azioni `new` e `create`.

    [php]
    public function executeNew(sfWebRequest $request)
    {
      $page = new Page();
      $page->Client = $this->getRoute()->getClient();
      $this->form = new PageForm($page);
    }

Questo introduce una nuova funzione, `getClient()`, che attualmente non esiste
nella classe `acClientObjectRoute`. Aggiungiamola alla classe, facendo alcune
semplici modifiche :

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      // ...

      protected $client = null;

      public function matchesUrl($url, $context = array())
      {
        // ...

        $this->client = $client;

        return array_merge(array('client_id' => $client->id), $parameters);
      }

      public function getClient()
      {
        return $this->client;
      }
    }

Con l'aggiunta di una proprietà alla classe `$client` e impostandola nella funzione
`matchesUrl()`, si può facilmente rendere l'oggetto `Client` disponibile alla rotta.
La colonna `client_id` dei nuovi oggetti `Page`, ora verrà automaticamente e
correttamente impostata, sulla base del sottodominio dell'host corrente.

Personalizzare una collezione di oggetti di rotte
-------------------------------------------------

Utilizzando il framework delle rotte, è stato facilmente risolto il problema
della creazione dell'applicazione Sympal Builder. Al crescere della domanda, lo
sviluppatore sarà in grado di riutilizzare le rotte personalizzate per altri
moduli dell'area di backend (ad esempio in modo che ogni `Client` possa gestire
le proprie gallerie di foto).

Un altro motivo comune per creare una collezione personalizzata di rotte è quello
di aggiungere rotte usate di frequente. Per esempio, supponiamo che un progetto
impieghi molti modelli, ciascuno con una colonna `is_active`. Nell'area
di amministrazione ci deve essere un modo facile per attivare/disattivare il
valore `is_active` per qualunque particolare oggetto. In primo luogo, modificare
`acClientObjectRouteCollection` e istruirlo al fine di aggiungere una nuova rotta
alla collezione:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    protected function generateRoutes()
    {
      parent::generateRoutes();

      if (isset($this->options['with_is_active']) && $this->options['with_is_active'])
      {
        $routeName = $this->options['name'].'_toggleActive';

        $this->routes[$routeName] = $this->getRouteForToggleActive();
      }
    }

Il metodo ~`sfObjectRouteCollection::generateRoutes()`~ è chiamato quando
l'oggetto della collezione è istanziato, è responsabile della creazione di
tutte le rotte necessarie e viene aggiunto alla proprietà dell'array `$routes`
della classe. In questo caso, si ferma la creazione effettiva della rotta in
un nuovo metodo protetto chiamato `getRouteForToggleActive()`:

    [php]
    protected function getRouteForToggleActive()
    {
      $url = sprintf(
        '%s/:%s/toggleActive.:sf_format',
        $this->options['prefix_path'],
        $this->options['column']
      );

      $params = array(
        'module' => $this->options['module'],
        'action' => 'toggleActive',
        'sf_format' => 'html'
      );

      $requirements = array('sf_method' => 'put');

      $options = array(
        'model' => $this->options['model'],
        'type' => 'object',
        'method' => $this->options['model_methods']['object']
      );

      return new $this->routeClass(
        $url,
        $params,
        $requirements,
        $options
      );
    }

L'unico passo rimanente è quello di impostare la collezione di rotte in `routing.yml`.
Si noti che `generateRoutes()` cerca un'opzione chiamata `with_is_active` prima
di aggiungere  la nuova rotta. L'aggiunta di questa logica, fornisce un ulteriore
controllo nel caso in seguito si voglia usare `acClientObjectRouteCollection` in
qualche parte in cui non si ha bisogno della rotta `toggleActive`:

    [yml]
    # apps/frontend/config/routing.yml
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        model:          Page
        prefix_path:    /pages
        module:         pageAdmin
        with_is_active: true

Verificare nel task `app:routes` che sia presente la nuova rotta `toggleActive`.
L'unica cosa che rimane da fare è creare l'azione che che farà il lavoro
effettivo. Dal momento che si vuole usare questa collezione di rotte e la
corrispondente azione in vari moduli, creare un nuovo file
`backendActions.class.php` nella cartella `apps/backend/lib/action`
(questa cartella è da creare):

    [php]
    # apps/backend/lib/action/backendActions.class.php
    class backendActions extends sfActions
    {
      public function executeToggleActive(sfWebRequest $request)
      {
        $obj = $this->getRoute()->getObject();

        $obj->is_active = !$obj->is_active;

        $obj->save();

        $this->redirect($this->getModuleName().'/index');
      }
    }

Infine modificare la classe base della classe `pageAdminActions`, per estendere
questa nuova classe `backendActions`.

    [php]
    class pageAdminActions extends backendActions
    {
      // ...
    }

Che cosa è stato fatto? Aggiungendo una rotta alla collezione di rotte e
un'azione associata in un file di azioni base, ogni nuovo modulo può automaticamente
utilizzare questa semplice funzionalità usando `acClientObjectRouteCollection`
ed estendendo la classe `backendActions`. In questo modo, le funzionalità comuni
possono essere facilmente  condivise tra molti moduli.

Opzioni su una collezione di rotte
---------------------------------

Le collezioni di oggetti di rotte contengono una serie di opzioni che gli
permettono di essere altamente personalizzate. In molti casi, uno sviluppatore
può usare queste opzioni per configurare la collezione senza avere bisogno
di creare una nuova classe di collezioni di rotte.
Un elenco dettagliato sulle opzioni delle collezioni di rotte è
disponibile nella [Guida di riferimento a symfony](http://www.symfony-project.org/reference/1_4/it/10-Routing#chapter_10_sfobjectroutecollection)

### Rotte di azioni

Ogni collezione di oggetti di rotte, accetta tre diffferenti opzioni che
determinano le esatte rotte generate nella collezione. Senza entrare nei
dettagli, la collezione seguente genera tutte e sette le rotte predefinite
con una aggiuntiva collezione di rotte e un oggetto di rotte:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        actions:      [list, new, create, edit, update, delete, show]
        collection_actions:
          indexAlt:   [get]
        object_actions:
          toggle:     [put]

### Colonna

Per impostazione predefinita, la chiave primaria del modello è utilizzata in
tutti gli URL generati e viene usata per interrogare gli oggetti. Questo,
naturalmente, può essere facilmente cambiato. Per esempio, il seguente codice
utilizza la colonna `slug` al posto della chiave primaria:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        column: slug

### Metodi di modelli

Per impostazione predefinita, la rotta recupera tutti gli oggetti collegati
per una collezione di rotte e interroga sulla `column` specificata, per le
rotte dell'oggetto. Se si ha bisogno di sovrascriverle, aggiungere l'opzione 
`model_methods` alla rotta. In questo esempio, i metodi `fetchAll()` e
`findForRoute` hanno bisogno di essere aggiunti alla classe `PageTable`. Entrambi
i metodi riceveranno un array di parametri richiesta come parametro:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        model_methods:
          list:       fetchAll
          object:     findForRoute

### Parametri predefiniti

Infine, supponiamo di avere bisogno di creare uno specifico parametro di richiesta
da rendere disponibile nella request per ciascuna rotta nella collezione. Questo
si può fare facilmente con l'opzione `default_params`:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        default_params:
          foo:   bar

Considerazioni finali
---------------------

Il lavoro basilare del framework delle rotte - trovare le corrispondenze e
generare url - si è evoluto in un sistema completamente personalizzabile in
grado di gestire le URL più complesse. Prendendo il controllo degli oggetti
rotta, la speciale struttura delle URL può essere astratta dalla business logic
e tenuta totalmente all'interno della rotta a cui essa appartiene.
Il risultato finale è un maggiore controllo, maggiore flessibilità e gestibilità
di codice.
