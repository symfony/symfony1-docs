Capitolo 2 - Esplorazione del codice di symfony
===============================================

In un primo momento l'esplorazione del codice alla base di un applicativo scritto utilizzando symfony può sembrare scoraggiante. 
Il codice è costituito da molteplici cartelle e script, i vari file sono un mix di classi PHP, HTML e a volte una combinazione di entrambi. 
Capiterà anche di trovare riferimenti a classi che non sono presenti all'interno della  cartella 
dell'applicativo oppure constatare che si può arrivare a una profondità delle cartelle di ben sei livelli. 
Ma una volta compresa la ragione di questa apparente complessità, ci si sentirà talmente a proprio agio che non si vorrebbe assolutamente cambiare la struttura 
dell'applicativo symfony con nessun'altra.

Pattern MVC
-----------

Symfony è basato sul classico web design pattern conosciuto come architettura MVC, che consiste di tre livelli:

  * Il modello (Model) rappresenta le informazioni sulle quali opera l'applicativo--la sua business logic.
  * La vista (View) presenta il modello su una pagina web in modo da renderla interattiva per l'utente.
  * Il controllore (Controller) risponde alle azioni dell'utente e invoca in modo appropriato i cambiamenti sul modello o sulla vista.

La Figura 2-1 illustra il pattern MVC

L'architettura MVC separa la business logic (modello) e la presentazione (vista), 
in questo modo si ottiene grande manutenibilità. 
Per esempio: se l'applicativo dovesse essere eseguito sia su un browser web standard sia su un palmare, 
basterà creare una nuova vista; il controllore originale e il modello non verranno modificati. 
Il controllore aiuta a nascondere i dettagli del protocollo utilizzato per la richiesta (HTTP, modalità console, mail etc.) 
dal modello e dalla vista.
Il modello astrae la logica dei dati rendendo la vista e il controllore indipendenti,
per esempio, dal tipo di database utilizzato dall'applicativo.

Figure 2-1 - Il pattern MVC

![Il pattern MVC](http://www.symfony-project.org/images/book/1_4/F0201.png "Il pattern MVC")

### I livelli dell'MVC

Per comprendere i vantaggi del pattern MVC, verrà illustrato di seguito come convertire un'applicazione base PHP 
nel suddetto pattern architetturale. 
Un perfetto esempio è dato da una lista di post di un web blog.

#### Programmazione "piatta"

Se si volesse mostrare una lista di record estratti da un database utilizzando un unico script PHP 
si utilizzerebbe del codice simile a quello mostrato nel listato 2-1

Listato 2-1 - Un'unico Script


    [php]
    <?php

    // Connessione e selezione del database
    $link = mysql_connect('localhost', 'myuser', 'mypassword');
    mysql_select_db('blog_db', $link);

    // Esecuzione query SQL
    $result = mysql_query('SELECT date, title FROM post', $link);

    ?>

    <html>
      <head>
        <title>List of Posts</title>
      </head>
      <body>
       <h1>List of Posts</h1>
       <table>
         <tr><th>Date</th><th>Title</th></tr>
    <?php
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
    echo "\t<tr>\n";
    printf("\t\t<td> %s </td>\n", $row['date']);
    printf("\t\t<td> %s </td>\n", $row['title']);
    echo "\t</tr>\n";
    }
    ?>
        </table>
      </body>
    </html>

    <?php

    // Chiusura connessione
    mysql_close($link);

    ?>

Il codice appena mostrato è immediato da scrivere, veloce da eseguire e impossibile da mantenere. 
I maggiori problemi che si possono incontrare utilizzando questo codice e questo approccio sono:

  * Non è presente un controllo degli errori (cosa succederebbe se la connessione al db fallisse?)
  * Codice HTML e codice PHP mischiati e intrecciati tra di loro.
  * Il codice è legato al database MYSQL.

#### Isolare la presentazione

Le chiamate `echo` e `printf` presenti nel listato 2-1 rendono il codice difficile da leggere. 
Diventerebbe un'operazione onerosa e complessa modificare il codice HTML per migliorarne la presentazione. 
Il codice può essere spezzato in due parti. 
La prima parte, lo script controllore, conterrà puro codice PHP con tutta la logica di business, come mostrato nel listato 2-2.

Listato 2-2 - Il Controllore, in `index.php`

    [php]
    <?php

    // Connessione e selezione del database
    $link = mysql_connect('localhost', 'myuser', 'mypassword');
    mysql_select_db('blog_db', $link);

    // Esecuzione query SQL
    $result = mysql_query('SELECT date, title FROM post', $link);

    // Popolamento dell'array per la vista
    $posts = array();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
      $posts[] = $row;
    }

    // Chiusura connessione
    mysql_close($link);

    // Richiesta della vista
    require('view.php');

Il codice HTML, contenente sintassi PHP adatta al template, risiede in uno script di vista, come mostrato nel listato 2-3

Listato 2-3 - La Vista, in `view.php`

    [php]
    <html>
      <head>
        <title>List of Posts</title>
      </head>
      <body>
        <h1>List of Posts</h1>
        <table>
          <tr><th>Date</th><th>Title</th></tr>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td><?php echo $post['date'] ?></td>
            <td><?php echo $post['title'] ?></td>
          </tr>
        <?php endforeach; ?>
        </table>
      </body>
    </html>

Una buona regola per determinare se la vista è sufficientemente chiara e pulita dal punto di vista del codice che la compone
è controllare che essa contenga soltanto un minimo quantitativo di codice PHP, in modo tale che sia facilmente comprensibile a un designer HTML 
che non abbia conoscenza del linguaggio PHP.
Le espressioni PHP più comuni presenti nelle vista sono `echo`, `if/endif`, `foreach/endforeach`. 
Inoltre non dovrebbe esserci codice PHP che stampi codice HTML.

Tutta la logica è stata spostata nello script controllore, contente soltanto puro codice PHP, senza codice HTML.
Lo stesso controllore deve essere pensato e progettato in modo tale che possa essere utilizzato per una presentazione totalmente differente,
magari per un file PDF o per una struttura XML.


#### Isolare la manipolazione dei dati

La maggior parte del codice presente nello script controllore è dedicato alla manipolazione dei dati.
Ma se si volesse la lista dei post per una altro controllore, ad esempio un controllore che mostri la lista come feed RSS?
E se si volesse mantenere le query SQL in un unico posto, in modo da non duplicare continuamente il codice?
E se si decidesse di modificare il nome della tabella del database da  `post` a `weblog_post`? 
E se si volesse utilizzare un database diverso da MYSQL, as esempio PostgresSQL?
Per rendere tutto ciò possibile bisogna rimuovere la parte di manipolazione dei dati presente nel controllore 
e spostarla un un altro script
come mostrato nel listato 2-4

Listato 2-4 - Il modello, in `model.php`

    [php]
    <?php

    function getAllPosts()
    {
      // Connessione e selezione del database
      $link = mysql_connect('localhost', 'myuser', 'mypassword');
      mysql_select_db('blog_db', $link);

      // Esecuzione query SQL
      $result = mysql_query('SELECT date, title FROM post', $link);

      // Popolamento dell'array
      $posts = array();
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
      {
         $posts[] = $row;
      }

      //Chiusura della connessione
      mysql_close($link);

      return $posts;
    }

Il controllore rivisto e riscritto è mostrato nel listato 2-5

Listato 2-5,- Il controllore, rivisto, in `index.php`

    [php]
    <?php

    // Richiesta del modello
    require_once('model.php');

    // Recupero della lista dei post
    $posts = getAllPosts();

    // Richeista della vista
    require('view.php');

In questo modo diventa più semplice leggere il codice del controllore.
Il suo unico scopo è quello di ottenere i dati dal modello e passarli alla vista.
In un'applicazione più complessa, il controllore controlla anche con la richiesta, la sessione utente, l'autenticazione, e così via.
L'utilizzo di nomi espliciti per le funzioni del modello rende addirittura non necessario l'utilizzo dei commenti 
nel controllore.

Lo script del modello è dedito all'accesso dei dati e può essere organizzato di conseguenza.
Tutti i parametri che non dipendono dal livello dei dati (ad esempio i parametri presenti nella richiesta) devono essere resi disponibili esclusivamente dal controllore,
e non dal modello. In questo modo le funzioni del modello possono essere riutilizzate in un altro controllore.


### Separazione dei livelli oltre al pattern MVC

Quindi il principio di base di un'architettura MVC è di separare il codice in tre livelli.

La logica che gestisce i dati appartiene al modello, la gestione del codice di presentazione appartiene alla vista e la logica applicativa appartiene al controllore.
L'utilizzo di altri pattern di design possono rendere ancora più semplice l'esperienza di sviluppo e scrittura del codice dell'applicativo.
I livelli modello, vista e controllore possono al loro volta essere suddivisi.

### Astrazione del database

Il livello del modello può essere suddiviso a sua volta in un livello di accesso ai dati e un livello di astrazione del database.
In questo modo, le funzioni di accesso ai dati non utilizzeranno query dipendenti dal tipo di database scelto, 
ma chiameranno altre funzioni che eseguiranno quelle particolari query. 
Se si cambiasse il database scelto, sarà necessario aggiornare solamente il livello di astrazione del database.

Un esempio specifico di accesso ai dati tramite un database MySQL è mostrato nel listato 2-6
seguito da un esempio di un livello di astrazione dell'accesso stesso nel listato 2-7.


Listato 2-6 - Componente di astrazione del database

    [php]
    <?php

    function open_connection($host, $user, $password)
    {
      return mysql_connect($host, $user, $password);
    }

    function close_connection($link)
    {
      mysql_close($link);
    }

    function query_database($query, $database, $link)
    {
      mysql_select_db($database, $link);

      return mysql_query($query, $link);
    }

    function fetch_results($result)
    {
      return mysql_fetch_array($result, MYSQL_ASSOC);
    }

Listato 2-7 - Componente di accesso ai dati del Modello

    [php]
    function getAllPosts()
    {
      // Connessione al database
      $link = open_connection('localhost', 'myuser', 'mypassword');

      // Esecuzione query SQL
      $result = query_database('SELECT date, title FROM post', 'blog_db', $link);

      // Popolamento dell'array
      $posts = array();
      while ($row = fetch_results($result))
      {
         $posts[] = $row;
      }

      // Chiusura connessione
      close_connection($link);

      return $posts;
    }

Come si può notare non sono presenti funzioni di accesso ai dati che sono dipendenti dal tipo di motore di database scelto.
Inoltre, le funzioni create nel componente di astrazione del database posso essere riutilizzate per molte altre funzioni
che richiedano accesso al database.

>**NOTE**
>Gli esempi mostrati nei Listati 2-6 e 2-7 non risultano ancora del tutto soddisfacenti in quanto ci sarebbe ancora del codice da scrivere
>per poter ottenere una completa e reale astrazione del database (astrazione del codice SQL attraverso un costruttore di query,
> spostamento di tutte le funzioni in una classe, e così via). Ma lo scopo di questa guida non è mostrare come si debba scrivere
> tutto questo codice, e verrà mostrato nel capitolo 8 come symfony fornisce già elegantemente questa astrazione.


### Elementi della Vista

Il livello della vista può beneficiare a sua volta di una separazione interna del codice.
Una pagina web contiene spesso degli elementi coerenti in tutto l'applicativo: gli header della pagina
il layout grafico, il footer e il menù di navigazione. In generale soltanto le parti interne della pagina cambiano.
Per questo motivo la vista è separata in altri due livelli: layout e template.
Il layout è solitamente globale nell'applicativo o comunque accomuna un gruppo di pagine.
Il template si occupa di mostrare i valori delle variabili messe a disposizione dal controllore.
È necessaria della logica per poter far in modo che queste componenti lavorino insieme, e questa logica di presentazione è
gestita appunto dalla vista.
In base a questi principi, la parta di vista del listato 2-3 può essere separate in tre parti,
come mostrato dai Listati 2-8, 2-9 e 2-10.



Listato 2-8 - La parte Template della vista, in `mytemplate.php`

    [php]
    <h1>List of Posts</h1>
    <table>
    <tr><th>Date</th><th>Title</th></tr>
    <?php foreach ($posts as $post): ?>
      <tr>
        <td><?php echo $post['date'] ?></td>
        <td><?php echo $post['title'] ?></td>
      </tr>
    <?php endforeach; ?>
    </table>

Listato 2-9 - La parte logica della vista

    [php]
    <?php

    $title = 'List of Posts';
    $posts = getAllPosts();

Listato 2-10 - La parte di layout della vista

    [php]
    <html>
      <head>
        <title><?php echo $title ?></title>
      </head>
      <body>
        <?php include('mytemplate.php'); ?>
      </body>
    </html>

#### Azioni e front controller

Il controllore mostrato nell'esempio precedente non effettua molte operazioni, ma in una applicazione web reale, esso deve svolgere molti compiti. 
Un compito importante e comune a tutti i controllori dell'applicativo.
Un compito comune include la gestione della richiesta, sicurezza, caricamento delle configurazione e faccende simili.
Questo è il motivo per cui spesso il controllore è suddiviso in un front controller, che è unico in tutto l'applicativo, e azioni
che contengono solamente il codice del controllore specifico di una pagina.

Uno dei grandi vantaggi nell'avere un front controller è che viene offerto un unico punto di accesso per tutto l'applicativo.
Qualora si decidesse di rendere inaccessibile l'applicativo, basterà semplicemente modificare lo script del front controller.
In un'applicativo sprovvisto di front controller, si dovrebbe intervenire su ogni singolo controllore per poter ottenere lo stesso effetto.

#### Orientamento agli oggetti

Tutti gli esempi mostrati in precedenza sono stati scritti con un paradigma di programmazione procedurale.
Le possibilità offerte dalla OOP dei moderni linguaggi di programmazione rende la programmazione stessa più semplice, dato che gli oggetti incapsulano logica,
ereditano uno dall'altro e forniscono un chiaro e pulito utilizzo dei nomi.

Implementare un'architettura MVC con un linguaggio che non sia orientato agli oggetti produrrebbe problematiche nella gestione dei namespace, duplicazione del codice
e supratutto un codice difficile da leggere.

Lo sviluppo orientato agli oggetti permette agli sviluppatori di utilizzare strumenti e componenti come l'oggetto vista, l'oggetto controllore
le classi di modello e trasformare tutte le funzioni degli esempi precedenti in metodi.
È una necessità per un'architettura MVC.



>**TIP**
> Se si volesse approfondire meglio i vari design pattern per un applicativo web in un contesto object-oriented
> si consiglia la lettura di Patterns of Enterprise Application Architecture by Martin Fowler (Addison-Wesley, ISBN: 0-32112-742-0).
>Il codice d'esempio presente nel libro di Fowler è scritto in Java o C#, ma è piuttosto comprensibile anche a uno sviluppatore PHP.

### Implementazione del pattern MVC di symfony

Ricapitolando: per una semplice pagine che mostra i post di un weblog, quanti componenti sono richiesti?
Come mostrato nella Figura 2-2, sono presenti le seguenti parti:

  * Model layer
  * Livello del Modello
    * Database abstraction
    * Astrazione del Database
    * Data access
    * Accesso ai Dati
  * View layer
  * Livello della Vista
    * View
    * Vista
    * Template
    * Layout
  * Controller layer
  * Livello del Controllore
    * Front controller
    * Action

Sette script, diversi file da aprire e modificare ogni volta che si crea una pagina!
Ciò nonostante symfony rende le cose semplici. Symfony implementa il meglio dell'architettura MVC
in modo da rendere veloce e indolore lo sviluppo di una applicazione.

Per prima cosa, il Front Controller e il layout sono gli stessi per tutte le azioni dell'applicativo.
È possibile avere controllori e layout multipli, ma è necessario solo uno di essi.
Il Front controller è un componente puramente in logica MVC e non ci sarà mai l'esigenza di scriverne uno perché
symfony si preoccuperà di generarlo.

L'altra buona notizia è che le classi del modello sono anch'esse generate automaticamente,
basandosi sulla struttura dei dati. Questo compito di auto-generazione delle classi del modello è affidato alla libreria ORM, che fornisce lo scheletro e la generazione del codice.
Se la libreria ORM trovasse una chiave esterna o un campo data, genererà degli speciali metodi che renderanno estremamente semplice la manipolazione dei dati e le relazioni tra essi.
La parte di astrazione del database è totalmente invisibile perché viene gestita nativamente da oggetti PHP.
Qualora si decidesse di cambiare il motore del database, non si dovrà toccare minimamente una singola riga di codice applicativo.
Occorre solamente cambiare un parametro di configurazione.


Inoltre la logica della vista può essere descritta attraverso un semplice file di configurazione, senza che ci sia la necessità
di scrivere del codice applicativo.

Figura 2-2 - Flusso di lavoro di symfony

![Flusso di lavoro di symfony](http://www.symfony-project.org/images/book/1_4/F0202.png "Flusso di lavoro di symfony")

Questo significa che per quanto riguarda la lista dei post descritta in precedenza saranno necessari solamente tre file
come mostrato nei Listati 2-11, 2-12, e 2-13.

Listato 2-11 - Azione `list`, in `myproject/apps/myapp/modules/weblog/actions/actions.class.php`

    [php]
    <?php

    class weblogActions extends sfActions
    {
      public function executeList()
      {
        $this->posts = PostPeer::doSelect(new Criteria());
      }
    }

Listato 2-12 - Template `list`, in `myproject/apps/myapp/modules/weblog/templates/listSuccess.php`

    [php]
    <?php slot('title', 'List of Posts') ?>

    <h1>List of Posts</h1>
    <table>
    <tr><th>Date</th><th>Title</th></tr>
    <?php foreach ($posts as $post): ?>
      <tr>
        <td><?php echo $post->getDate() ?></td>
        <td><?php echo $post->getTitle() ?></td>
      </tr>
    <?php endforeach; ?>
    </table>

Si dovrà definire un layout, come mostrato nel listato 2-13, ma esso sarà riutilizzato diverse volte.

Listato 2-13 - Layout, in `myproject/apps/myapp/templates/layout.php`

    [php]
    <html>
      <head>
        <title><?php include_slot('title') ?></title>
      </head>
      <body>
        <?php echo $sf_content ?>
      </body>
    </html>

Quello appena mostrato è l'esatto e unico codice richiesto per mostrare la stessa pagina che si otterrebbe con lo script del listato 2-1. 
Il resto (rendere possibile che tutti i componenti interagiscano tra loro) è gestito da symfony.
Se si contassero le linee di codice, si noterà che creare la lista di post in un'architettura MVC con symfony 
non richieda più tempo o codice rispetto a scrivere la stessa cosa su un unico file.
Inoltre questo approccio porta con se enormi vantaggi: organizzazione chiara e pulita del codice, riusabilità, flessibilità e 
e più divertimento. 
Oltre a ciò symfony fornisce conformità con gli standard XHTML, capacità di debug, semplice configurazione, astrazione del database, un intelligente sistema 
di gestione degli URL, ambienti  multipli e tanti altri strumenti di sviluppo. 

### Le Classi che compongono la base (nucleo) del framework symfony

L'implementazione MVC in symfony utilizza diverse classi che verranno spesso citate all'interno di questa guida:

  * `sfController` è la classe controllore. Decodifica la richiesta e la inoltra all'azione.
  * `sfRequest` immagazzina tutti gli elementi della richiesta (parametri, cookies, headers e così via).
  * `sfResponse` contiente gli header e il contenuto della risposta. Questo è l'oggetto che sarà eventualmente convertito in una risposta HTML e che a sua volta sarà inviata all'utente.
  * Il contesto (ottenuto invocando `sfContext::getInstance()`) mantiene una referenza a tutti gli oggetti principali e alla configurazione corrente; è accessibile in ogni punto dell'applicativo.

Nel capitolo 6 questi oggetti verranno approfonditi maggiormente.

Come si può notare, tutte le classi fornite da symfony utilizzano il prefisso 'sf', così come le variabili del nucleo nei template. 
Questo serve a scongiurare problematiche relative all'utilizzo di nomi per variabili e classi utilizzate dallo sviluppatore e rendendo semplice il loro riconoscimento.

>**NOTE**
>Tra i diversi standard utilizzati da symfony, UpperCamelCase è lo standard per i nomi delle classi delle variabili.
>Ci sono due eccezioni: le classi del nucleo iniziano con `sf`, in minuscolo, e le variabili nei template utilizzano
>il trattino basso come separatore.

Organizzazione del codice
-------------------------

Dopo aver illustrato i diversi componenti di un'applicazione symofony, mostriamo come essi sono sono organizzati.
Symfony organizza il codice in una struttura di progetto e colloca i file in una struttura standard ad albero.

### Struttura di Progetto, Applicazioni, Moduli e Azioni

In symfony, un progetto è un insieme di servizi e operazioni disponibili sotto un dato nome di dominio, 
condividendo lo stesso modello.

All'interno di un progetto, le operazioni sono raggruppate in modo logico all'interno delle applicazioni. 
Un'applicazione può normalmente girare indipendentemente dalle altre applicazioni dello stesso progetto.
Nella maggior parte dei casi, un progetto conterrà due applicazioni: una per il front-office e un per il back-office.
condividendo lo stesso database. Ma è possibile avere anche un progetto contenente dei mini-siti,
un'applicazione per ogni sito. Da notare che i link tra le applicazioni devono essere in forma assoluta.

Ogni applicazione è un insieme di uno o più moduli.
Un modulo di solito rappresenta una pagina o un gruppo di pagina che hanno in comune lo stesso scopo.
Ad esempio si potrebbe avere dei moduli `home`, `articoli`, `help`, `carrelloDellaSpesa`, `account`, e così via.

I moduli contengono le azioni, le quali rappresentano le varie azioni che possono essere eseguite all'interno di un modulo.
Per esempio, un modulo nominato `carrelloDellaSpesa` può contenere le azioni `aggiungi`, `mostra` e aggiorna.
Aver a che fare con le azioni è quasi come interagire con le pagine di una classica applicazione web, sebbene due azioni 
possano risultare nella stessa pagina (ad esempio, aggiungendo un commento a un post in un blog comporterà la ri-visualizzazione del post con il nuovo commento).

>**TIP**
>Se ci fossero troppi livelli di moduli e azioni per un progetto iniziale, è possibile raggruppare tutte le azioni in un singolo modulo,
>in modo tale che la struttura si mantenga semplice. Nel caso in cui successivamente l'applicazione diventi più complessa,
>sarà necessario disporre le azioni in moduli separati.
>Come descritto nel capitolo 1, l'operazione di riscrivere il codice per migliorare la struttura e la leggibilità dello stesso (ma preservando il suo comportamento)
>viene comunemente chiamato 'rifattorizzazione' e questo viene fatto frequentemente applicando i principi RAD.

La Figura 2-3 mostra un esempio di codice per un progetto di un blog, in una struttura `project/application/module/action`.

Figura 2-3 - Esempio di organizzazione del codice

![Esempio di organizzazione del codice](http://www.symfony-project.org/images/book/1_4/F0203.png "Esempio di organizzazione del codice")

### Strutture ad albero dei file

Tutti i progetti web condividono lo stesso tipo di contenuti, ad esempio:

  * Un database, come MYSQL o PostgreSQL
  * File statici (HTML, immagini, file JavaScript, fogli di stile e così via)
  * File caricati dagli utenti del sito e gli amministratori
  * Classi e librerie PHP
  * Librerie esterne (script di terze parti)
  * File Batch (script che vengono lanciati da linea di comando o via cron)
  * File di Log (informazioni scritte dall'applicativo e/o dal server)
  * File di configurazione
  
Symfony fornisce una struttura di file standard per organizzare tutti questi contenuti in modo logico e
consistente con le scelte architetturali (MVC pattern e raggruppamento progetto/applicazione/modulo).
Questa è la struttura che viene creata quando si inizializza un progetto, applicazione o modulo.
Ovviamente è possibile personalizzare completamente la struttura dei file e delle cartelle, qualora fosse necessario.

#### Struttura principale dell'albero

Queste sono le cartelle alla base di un progetto symfony:

    apps/
      frontend/
      backend/
    cache/
    config/
    data/
      sql/
    doc/
    lib/
      model/
    log/
    plugins/
    test/
      bootstrap/
      unit/
      functional/
    web/
      css/
      images/
      js/
      uploads/

Tabella 2-1 descrive il contenuto delle cartelle mostrate

Tabella 2-1 - Cartelle di base

Cartella   | Descrizione
---------- | -----------
`apps/`    | Contiene una  cartella per ogni applicazione del progetto (tipicamente, `frontend` e `backend`).
`cache/`   | Contiene la versione cache della configurazione, e (se attivata) la versione cache delle azioni e dei template del progetto. Il meccanismo di cache (descritto nel capitolo 12) utilizza questi file per velocizzare i tempi di risposta dell'applicativo. Ciascuna applicazione avrà una sotto-cartella, contenente file PHP pre-processati e file HTML.
`config/`  | Contiene la configurazione generale del progetto.
`data/`    | Contiene i file dati del progetto come lo schema del database, un file SQL per la creazione delle tabelle o anche un file SQLite.
`doc/`     | Contiene la documentazione del progetto
`lib/`     | Contiene classi esterne o librerie. In essa può essere aggiunto del codice che deve essere condiviso tra le applicazioni. La sotto-cartella `model/` contiene gli oggetti del modello del progetto (descritto nel capitolo 8).
`log/`     | Contiene i file di log generati direttamente da symfony. Possono anche essere presenti i file di log del server web, del database o file di log provenienti da qualsiasi punto del progetto. Symfony crea un file di log per ogni applicazione e ambiente (i file di log verranno discussi nel capitolo 16).
`plugins/` | Contiene i vari plugin installati nell'applicativo (i plugin verranno discussi nel capitolo 17).
`test/`    | Contiene test unitari e funzionali scritti in PHP e compatibili con il framework di test di symfony (discusso nel capitolo 15). Durante il setup del progetto, symfony crea automaticamente dei file con dei test basilari.
`web/`     | È la cartella radice del server web. Essa contiene gli unici file accessibili dall'esterno.

#### Struttura ad albero di un'applicazione

La struttura ad albero di un'applicazione è la stessa:

    apps/
      [application name]/
        config/
        i18n/
        lib/
        modules/
        templates/
          layout.php

Tabella 2-2 descrive le sotto-cartelle dell'applicazione

Tabella 2-2 - Sotto-cartelle dell'applicazione

Cartella     | Descrizione
------------ | -----------
`config/`    | Contiene un insieme di file di configurazione YAML. In questa sotto-cartelle sono contenuti i principali di configurazione, escludendo i parametri iniziali definiti nel framework stesso. Se fosse necessario i valori dei parametri iniziali posso essere comunque sovrascritti. Questo argomento verrà trattato nello specifico nel capitolo 5.
`i18n/`      | Contiene i file utilizzati per l'internazionalizzazione dell'applicativo (il capitolo 13 approfondisce l'argomento). È possibile non tenere conto di questa  cartella qualora si utilizzasse un database per l'internazionalizzazione.
`lib/`       | Contiene classi e librerie specifiche all'applicazione.
`modules/`   | Contiene tutti i  moduli dell'applicazione
`templates/` | Contiene i template globali dell'applicazione, condivisi da tutti i moduli. Contiene il file `layout.php` come predefinito, che rappresenta il layout principale nel quale i template dei moduli vengono inseriti.

>**NOTE**
>Le cartelle `i18n/`, `lib/` e  `modules/` sono vuote quando viene creata una nuova applicazione.

Le classi di un'applicazione non possono accedere a metodi o attributi di altre applicazioni dello stesso progetto. 
Da notare che i link tra le diverse applicazioni devono essere in forma assoluta e quindi è da tener ben presente qualora si decidesse di suddividere un progetto in diverse applicazioni.


#### Struttura ad albero di un modulo

Ogni applicazione contiene uno o più moduli.
Ogni modulo ha le sue sotto-cartelle nella cartella `modules` e il nome di tale  cartella viene scelta durante la configurazione.

Tipica struttura ad albero di un modulo:

    apps/
      [application name]/
        modules/
          [module name]/
              actions/
                actions.class.php
              config/
              lib/
              templates/
                indexSuccess.php

Tabella 2-3 descrive le sotto-cartelle di un modulo.

Tabella 2-3 - Sotto-cartelle di un modulo

Cartella     | Descrizione
------------ | -----------
`actions/`   | Contiene generalmente un singolo file chiamato `actions.class.php`, nel quale sono scritte tutte le azioni del modulo stesso. È possibile scrivere azioni diverse di un modulo in file separati.
`config/`    | Contiene file di configurazione personalizzati con parametri locali per il modulo.
`lib/`       | Contiene classi e librerie specifiche del modulo.
`templates/` | Contiene i template corrispondenti all'azione del modulo. Un template predefinito, chiamato `indexSuccess.php`, viene creato durante il setup del modulo.

>**NOTE**
>Le cartelle `config/` e `lib/` non vengono create automaticamente per un nuovo modulo, devono essere create manualmente qualora fossero necessarie.

#### Struttura ad albero della cartella Web

Esistono pochi vincoli per la cartella `web`, essa è la cartella contenente file accessibili pubblicamente dall'esterno.
Seguendo poche regole base sarà possibile ottenere dei comportamenti predefiniti, messi a disposizione dal framework stesso, e utili scorciatoie da utilizzare all'interno dei template. 
Esempio della struttura della  cartella `web`:

    web/
      css/
      images/
      js/
      uploads/

È convenzione che i file statici vengano collocati nella  cartella mostrate nella Tabella 2-4

Tabella 2-4 Sotto cartella tipiche della  cartella Web

Cartella   | Descrizione
---------- | -----------
`css/`     | Contiene fogli di stile con estensione `.css`.
`images/`  | Contiene immagini con estensione `.jpg`, `.png`, o `.gif`.
`js/`      | Contiene file JavaScript con estensione `.js`.
`uploads/` | Contiene file caricati dagli utenti. Sebbene la  cartella contenga solitamente immagini, è separata dalla  cartella delle immagini in modo che la sincronizzazione dei server di sviluppo e server di produzione non interferisca con le immagini caricate.

>**NOTE**
>Sebbene sia altamente consigliato che venga mantenuta la struttura ad albero predefinita, è possibile modificarla 
>per esigenze specifiche, ad esempio per permettere a un progetto di girare su un server con strutture particolari. 
>Per maggiori informazioni su quest'ultimo argomento è necessario riferirsi al capitolo 19 nel quale verrà mostrato come modificare la struttura ad albero dei file.


Strumenti comuni
----------------

Durante la lettura di questa guida e nello sviluppo di progetti con symfony si incontreranno alcune tecniche utilizzate ripetutamente.
Tra queste ci sono: contenitori dei parametri, costanti e caricamento automatico delle classi.

### Contenitori di parametri

Molte delle classi che compongono il framework symfony sono dei contenitori di parametri. 
È un modo conveniente di incapsulare gli attributi con dei metodi getter e setter chiari e puliti.
Ad esempio, la classe `sfRequest` ha al suo interno un contenitore di parametri richiamabile attraverso il metodo `getParameterHolder()`.
Ogni contenitore di parametri immagazzina i dati nello stesso modo, come viene mostrato nel listato 2-14.

Listato 2-14 - Utilizzo del contenitore di parametri della classe `sfRequest`

    [php]
    $request->getParameterHolder()->set('foo', 'bar');
    echo $request->getParameterHolder()->get('foo');
     => 'bar'

La maggior parte delle classi che utilizza un contenitore di parametri fornisce un metodo proxy per fornire dei metodi 
più corti per le operazioni di get e set.
Questo è il caso dell'oggetto `sfRequest`, in modo tale che sia possibile fare quando mostrato nel listato 2-14 con il codice del listato 2-15.

Listato 2-15 - Utilizzo del metodo proxy del contenitore dei parametri dell'oggetto `sfRequest`

    [php]
    $request->setParameter('foo', 'bar');
    echo $request->getParameter('foo');
     => 'bar'

Il metodo getter del contenitore di parametri accetta un valore predefinito come secondo argomento.
Tutto ciò fornisce un utile meccanismo di fallback che risulta molto più conciso rispetto a un blocco condizionale.
Si veda come esempio il listato 2-16.

Listato 2-16 - Utilizzo del valore predefinito 

    [php]
    // Il parametro 'foobar' non è definito, il metodo getter restituisce un valore nullo
    echo $request->getParameter('foobar');
     => null

    //Un valore predefinito può essere utilizzato mettendo il metodo getter in un blocco condizionale
    if ($request->hasParameter('foobar'))
    {
      echo $request->getParameter('foobar');
    }
    else
    {
      echo 'default';
    }
     => default

    // Ma risulta più veloce e immediato l'utilizzo di un secondo argomento per il valore predefinito
    echo $request->getParameter('foobar', 'default');
     => default

Alcune classi del nucleo di symfony utilizzano un contenitore di parametri che supporta i namespace (grazie alla classe  `sfNamespacedParameterHolder`).
Se viene specificato un terzo argomento al setter o al getter, esso viene utilizzato come namespace, e il parametro verrà definito esclusivamente all'interno del namespace.
Listato 2-17 ne mostra un esempio.

Listato 2-17 - Utilizzo dei namespace con il contenitore dei parametri di `sfUser`

    [php]
    $user->setAttribute('foo', 'bar1');
    $user->setAttribute('foo', 'bar2', 'my/name/space');
    echo $user->getAttribute('foo');
     => 'bar1'
    echo $user->getAttribute('foo', null, 'my/name/space');
     => 'bar2'

È possibile aggiungere un contenitore dei parametri a delle classi personalizzate per trarre vantaggio da questo meccanismo

Listato 2-18 - Aggiungere un contenitore dei parametri a una classe

    [php]
    class MyClass
    {
      protected $parameterHolder = null;

      public function initialize($parameters = array())
      {
        $this->parameterHolder = new sfParameterHolder();
        $this->parameterHolder->add($parameters);
      }

      public function getParameterHolder()
      {
        return $this->parameterHolder;
      }
    }

### Costanti

Non sono presenti costanti in symfony, perché le loro vera natura è tale da non poter cambiarne i valori qualora siano definiti.
Symfony utilizza un proprio oggetto di configurazione, chiamato `sfConfig`, che rimpiazza l'uso delle costanti.
Esso fornisce metodi statici per accedere ovunque ai vari parametri. Il listato 2-19 mostra l'utilizo dei metodi `sfConfig`.

Listato 2-19 - Utilizzo dei metodi di `sfConfig` al posto delle costanti

    [php]
    // Al posto delle costanti
    define('FOO', 'bar');
    echo FOO;

    // symfony utilizza l'oggetto sfConfig
    sfConfig::set('foo', 'bar');
    echo sfConfig::get('foo');

I metodi di `sfConfig` supporta valori predefiniti ed è possibile invocare più volte il metodo `sfConfig::set()` sullo stesso parametro per cambiarne il valore.
Il capitolo 5 illustra i metodi di `sfConfig` nel dettaglio.

### Caricamento automatico delle classi

Solitamente in PHP quando viene invocato un metodo di una classe o viene creato un oggetto c'è la necessità di includere per prima cosa 
la definizione di tale classe:

    [php]
    include_once 'classes/MyClass.php';
    $myObject = new MyClass();

In un progetto di grosse dimensione con molte classi e una profonda e articolata struttura di cartelle, tener traccia di tutti i file delle vari classi e i percorsi delle stesse può diventare una perdita di tempo.
Symfony fornisce una funzione `spl_autoload_register()` che rende superfluo l'utilizzo della direttiva `include_once`, e che permette di scrivere direttamente:

    [php]
    $myObject = new MyClass();

Symfony cercherà all'interno delle cartelle `lib/' la definizione della classe `MyClass` in tutti file che termineranno con `class.php`.
Se la classe verrà trovata, verrà inclusa automaticamente.

Collocando le proprie classi all'interno delle cartelle `lib/' non sarà più necessario includerle.
Questo è il motivo per il quale solitamente i progetti symfony non contengono direttive `include_once` o `require_once`.

Sommario
--------

L'utilizzo di un framework MVC obbliga lo sviluppatore a organizzare il codice in accordo con le convenzioni del framework stesso.
Il codice di presentazione appartiene alla vista, la manipolazione dei dati appartiene al modello, e la logica della richiesta appartiene al controllore.

Symfony è un framework MVC scritto in PHP.
La sua struttura permette di ottenere il meglio grazie all'utilizzo del pattern MVC, mantenendo al contempo una praticità e semplicità d'utilizzo.
Grazie alla sua versatilità e configurabilità, symfony è adatto per tutte le tipologie di applicativi web.

Ora che è stata mostrata la teoria alla base di symfony, si è in grado di sviluppare una prima applicazione, ma prima di questo occorre un'installazione di symfony e un
server di sviluppo.
