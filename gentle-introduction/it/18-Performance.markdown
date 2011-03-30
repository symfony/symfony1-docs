Capitolo 18 - Prestazioni
=========================

Se ci si aspetta che il proprio sito web possa attirare molte visite i problemi di prestazioni e di ottimizzazione dovrebbero essere argomenti trattati a fondo durante la fase di sviluppo. State sicuri che quella delle prestazioni è stata sempre una delle principali preoccupazioni per gli sviluppatori del core di symfony.

Mentre i vantaggi ottenuti dall'accelerazione del processo di sviluppo comportano un piccolo overhead, gli sviluppatori del core di symfony sono sempre stati a conoscenza dei requisiti relativi alle prestazioni. Proprio per questo ogni classe e ogni metodo son stati analizzati e ottimizzati per essere più veloci possibile. Il piccolo overhead, che può essere misurato confrontando il tempo necessario a visualizzare un "hello, world" con e senza symfony, è minimo. Conseguentemente il framework è scalabile e reagisce positivamente agli stress test. Come ultima prova alcuni siti a [estremamente](http://sf-to.org/answers) [alto](http://sf-to.org/delicious) [traffico](http://sf-to.org/dailymotion) (questo significa, siti web con milioni di utenti attivi e molti server che erogano interazioni Ajax) usano symfony e sono molto soddisfatti delle sue prestazioni.

Tuttavia i siti ad alto traffico molte volte si possono permettere di espandere la propria server farm e di fare upgrade hardware man mano che le risorse vengono utilizzate. Quando non si hanno le risorse per agire in questo modo, o quando si vuole essere certi di avere a disposizione l'intera potenza del framework, esistono degli accorgimenti per rendere ulteriormente più veloce la propria applicazione symfony. Questo capitolo elenca alcune delle ottimizzazioni raccomandate per le prestazioni a tutti i livelli del framework che sono principalmente per utenti avanzati. Alcuni di essi sono già stati citati nei capitoli precedenti, è utile tuttavia averli tutti assieme in un unico posto.

Ottimizzare il server
---------------------

Un'applicazione ben ottimizzata dovrebbe poter fare affidamento su un server ben ottimizzato. È importante conoscere le basi relative alle prestazioni di un server per assicurarsi di non avere colli di bottiglia esterni a symfony. Di seguito alcune cose da verificare per essere sicuri che il proprio server non sia inutilmente lento.

Avere `magic_quotes_gpc` impostato a `true` nel `php.ini` rallenta l'applicazione perché dice a PHP di eseguire l'escape di tutti gli apici nei parametri della richiesta, tuttavia symfony in seguito eliminerà sistematicamente tutti gli escape con la sola conseguenza di una perdita di tempo e di problemi con l'escape su alcune piattaforme. Quindi è consigliabile disattivare questa impostazione se si ha l'accesso alla configurazione di PHP.

Utilizzare la release più recente di PHP è sempre un'ottima scelta (PHP 5.3 è più performante di PHP 5.2). Assicurarsi quindi di aggiornare la propria versione di PHP per beneficiare delle migliorie dell'ultima versione.

L'utilizzo di un acceleratore PHP (come APC, XCache o eAccelerator) è praticamente obbligatorio su un server di produzione, può permettere di ottenere migliori prestazioni mediamente del 50% in più senza compromessi. Assicurarsi di installare un acceleratore per apprezzare la reale velocità di PHP.

D'altro canto ci si deve assicurare di aver disattivato qualsiasi strumento di debug, come Xdebug o APD, sul server di produzione.

>**NOTE**
>Legittimo chiedersi cosa comporta l'overhead causato dall'estensione `mod_rewrite`: è trascurabile. Certamente caricare un'immagine con regole di rewrite è un'operazione più lenta rispetto al caricamento senza regole, ma il rallentamento è di ordini di grandezza inferiore all'esecuzione di ogni singola operazione di PHP.

-

>**TIP**
>Quando un server non è abbastanza è sempre possibile aggiungerne un secondo e utilizzare un sistema di bilanciamento del carico. Ammesso che la cartella `uploads/` sia condivisa tra le macchine e si utilizzi il database per lo storage delle sessioni, symfony risponderà allo stesso modo in un'architettura bilanciata.

Ottimizzare il modello
----------------------

In symfony lo strato del modello ha la reputazione di essere la parte più lenta. Se i benchmark evidenziano la necessità di ottimizzare questo strato, analizziamo alcuni possibili miglioramenti.

### Ottimizzare l'integrazione di Propel o Doctrine

L'inizializzazione dello strato del modello (le classi del core dell'ORM) richiede un po' di tempo per la necessità di caricare alcune classi e creare diversi oggetti. Comunque, grazie a come symfony integra tutti e due gli ORM, questo processo di inizializzazione si verifica solo quando un'azione necessita realmente del modello e questo viene fatto più tardi possibile. Le classi dell'ORM vengono inizializzate solo quando un oggetto del modello auto generato è oggetto di auto-caricamento.

Se l'intera applicazione non richiede l'utilizzo dello strato del modello è possibile evitare l'inizializzazione del `sfDatabaseManager` disabilitando completamente lo strato in `settings.yml`:

    all:
      .settings:
        use_database: false

        
#### Miglioramenti di Propel

Le classi generate del modello (in `lib/model/om/`) sono già ottimizzate non contengono commenti, beneficiano del sistema di auto-caricamento. Fare affidamento sull'auto-caricamento invece che sull'inclusione manuale dei file significa che le classi vengono caricate solo se realmente necessarie. Quindi nel caso in cui una classe del modello non fosse necessario l'auto-caricamento permette di risparmiare tempo di esecuzione, cosa non permessa dal metodo alternativo in cui si utilizza `include`. Stesso discorso per i commenti, documentano l'utilizzo dei metodi generati ma allungano i file del modello risultando in un minore overhead su dischi lenti. Dato che i nomi dei metodi generati sono piuttosto espliciti, i commenti sono disabilitati per impostazione predefinita.

Queste due ottimizzazioni sono specifiche per symfony, è possibile tornare sui valori predefiniti di Propel modificando due impostazioni nel file `propel.ini` come segue:

    propel.builder.addIncludes = true   # Aggiunge gli include nelle classi generate
                                        # invece che fare affidamento al sistema di auto-caricamento
    propel.builder.addComments = true   # Aggiunge commenti alle classi generate

### Limitare il numero di oggetti da idratare

Quando si utilizza un metodo di una classe peer per recuperare degli oggetti la query attraversa il processo di idratazione (creazione e popolamento degli oggetti basata sulle righe del risultato della query). Per esempio per recuperare tutte le righe della tabella `article` con Propel abitualmente si agisce così:

    [php]
    $articles = ArticlePeer::doSelect(new Criteria());

La variabile `$articles` ottenuta è un array di oggetti della classe `Article`. Ogni oggetto deve essere creato e inizializzato, cosa che richiede tempo. Questo ha una conseguenza principale: diversamente dalle query dirette al database la velocità delle query di Propel è direttamente proporzionale al numero di risultati che genera. Questo significa che i metodi dei modelli dovrebbero essere ottimizzati per restituire solo un numero ben preciso di risultati. Quando non sono necessari tutti i risultati restituiti da un `Criteria`, è opportuno limitarli usando i metodi `setLimit()` e `setOffset()`. Se per esempio si volessero solo le righe dalla 10 alla 20 di una specifica query si potrebbe migliorare il `Criteria` come nel listato 18-1.

Listato 18-1 - Limitare il numero di risultati restituito da un Criteria

    [php]
    $c = new Criteria();
    $c->setOffset(10);  // Offset del primo record restituito
    $c->setLimit(10);   // Numero di record restituito
    $articles = ArticlePeer::doSelect($c);

Questo può essere automatizzato utilizzando un sistema di paginazione. L'oggetto `sfPropelPager` gestisce automaticamente l'offset e il limite di una query di Propel per idratare solamente gli oggetti richiesti da una pagina specifica.

### Minimizzare il numero di query con le Join

Durante lo sviluppo di un'applicazione è bene tenere sott'occhio il numero delle query inviate al database da ogni singola richiesta. La web debug toolbar visualizza il numero delle query per ogni pagina, cliccando sulla piccola icona del database è possibile analizzare il codice SQL di tutte queste query. Se il numero delle query cresce in modo anomalo è giunto il momento di valutare l'utilizzo di alcune Join.

Prima di analizzare i metodi di Join rivediamo cosa accade quando si itera su un array di oggetti usando un getter di Propel per ottenere i dettagli di una classe relazionata come nel listato 18-2. Questo esempio suppone che lo schema descriva una tabella `article` con una chiave esterna a una tabella `author`.

Listato 18-2 - Recuperare dettagli su un classe relazionata in un loop

    [php]
    // Nell'azione con Propel
    $this->articles = ArticlePeer::doSelect(new Criteria());
    // O con Doctrine
    $this->articles = Doctrine::getTable('Article')->findAll();

    // Query invocata dal doSelect()
    SELECT article.id, article.title, article.author_id, ...
    FROM   article

    // Nel template
    <ul>
    <?php foreach ($articles as $article): ?>
      <li><?php echo $article->getTitle() ?>,
        written by <?php echo $article->getAuthor()->getName() ?></li>
    <?php endforeach; ?>
    </ul>

Se l'array `$articles` contenesse dieci oggetti, il metodo `getAuthor()` verrebbe chiamato dieci volte, eseguendo quindi una query ogni volta che viene invocato per idratare un oggetto della classe `Author`, come nel listato 18-3.

Listato 18-3 - Getter su chiavi esterne richiedono una query a database

    [php]
    // Nel template
    $article->getAuthor()

    // Qeury al database invocata da getAuthor()
    SELECT author.id, author.name, ...
    FROM   author
    WHERE  author.id = ?                // ? is article.author_id

Quindi la pagina del listato 18-2 richiederà in totale 11 query: una necessaria alla creazione dell'array di oggetti `Article` più le dieci query necessarie per creare un oggetto `Author` alla volta. Si tratta di molte query per la sola visualizzazione di una lista di articoli con i relativi autori.

####Come ottimizzare le query con Propel

Se si stesse usando semplice SQL non dovrebbe essere molto difficile ridurre il numero di query a una sola recuperando le colonne della tabella `article` e quelle della tabella `author` nello stesso momento. Questo è esattamente il comportamento del metodo `doSelectJoinAuthor()` della classe `ArticlePeer`. Questo metodo invoca una query leggermente più complessa della semplice chiamata `doSelect()`, le colonne aggiuntive del result set permettono a Propel di idratare sia gli oggetti `Article` che gli oggetti `Author` relazionati. Il codice del listato 18-4 mostra esattamente lo stesso risultato del listato 18-2 ma richiede una singola query al database invece che 11 risultando così più veloce.

Listato 18-4 - Recuperare dati degli articoli e dei loro autori nella stessa query

    [php]
    // Nell'azione
    $this->articles = ArticlePeer::doSelectJoinAuthor(new Criteria());

    // Query al database invocata da doSelectJoinAuthor()
    SELECT article.id, article.title, article.author_id, ...
           author.id, author.name, ...
    FROM   article, author
    WHERE  article.author_id = author.id

    // Nel template (inalterato)
    <ul>
    <?php foreach ($articles as $article): ?>
      <li><?php echo $article->getTitle() ?>,
        written by <?php echo $article->getAuthor()->getName() ?></li>
    <?php endforeach; ?>
    </ul>

Non c'è alcuna differenza tra il risultato fornito dalla chiamata ai metodi `doSelect()` e `doSelectJoinXXX()`; entrambi restituiscono lo stesso array di oggetti (della classe `Article` nell'esempio). La differenza appare invece quando viene utilizzato un getter su una chiave esterna di questi oggetti. Nel caso di `doSelect()` viene invocata la query e un oggetto viene idratato con il risultato; nel caso del `doSelectJoinXXX()` l'oggetto esterno esiste già e non è richiesta nessuna query, il processo è così molto più veloce. Se si è a conoscenza di dover utilizzare oggetti relazionati invocare un metodo `doSelectJoinXXX()` per ridurre il numero di query al database e migliorare le prestazioni della pagina.

Il metodo `doSelectJoinAuthor()` viene generato automaticamente dalla chiamata di `propel-build-model` grazie alla relazione tra le tabelle `article` e `author`. Nel caso in cui esistessero altre chiavi esterne della struttura della tabella degli articoli, per esempio a una tabella delle categorie, la classe generata `BaseArticlePeer` avrebbe altri metodi Join come mostrato nel listato 18-5.

Listato 18-5 - Esempio dei metodi `doSelect` disponibili nella classe `ArticlePeer`

    [php]
    // Recupera oggetti Article
    doSelect()

    // Recupera oggetti Article e idrata oggetti Author relazionati
    doSelectJoinAuthor()

    // Recupera oggetti Article e idrata oggetti Category relazionati
    doSelectJoinCategory()

    // Recupera oggetti Article e idrata oggetti relazionati a eccezione di Author
    doSelectJoinAllExceptAuthor()

    // Sinonimo di
    doSelectJoinAll()

Le classi peer contengono anche metodi Join per `doCount()`. Le classi con una controparte i18n (vedere capitolo 13) mettono a disposizione un metodo `doSelectWithI18n()` che si comporta allo stesso modo dei metodi Join ma per gli oggetti i18n. Per scoprire i metodi Join disponibili nelle classi del modello analizzare le classi peer generate in `lib/model/om/`. Nel caso in cui non fosse presente il metodo Join necessario per una particolare query (per esempio non esistesse un metodo Join generato automaticamente per le relazioni molti-a-molti) è possibile crearlo ed estendere il modello.

>**TIP**
>Certamente una chiamata a `doSelectJoinXXX()` è leggermente più lenta di una chiamata a `doSelect()`, quindi migliora le prestazioni generali solo se si stanno utilizzando oggetti idratati.

#### Ottimizzare le query con Doctrine

Doctrine dispone di un proprio linguaggio per le interrogazioni chiamato DQL, acronimo di *Doctrine Query Language*. La sintassi è molto simile a quella di SQL, tuttavia permette di recuperare insiemi di oggetti piuttosto che di righe. In SQL si vorrebbero recuperare le colonne delle tabelle `article` e `author` nella stessa query. Con DQL la soluzione è piuttosto semplice dato che basta semplicemente aggiungere un join alla query originale, Doctrine idraterà gli oggetti nel modo più appropriato. Il codice seguente mostra come utilizzare il join tra due tabelle:

    [php]
    // Nell'azione
    Doctrine::getTable('Article')
      ->createQuery('a')
      ->innerJoin('a.Author') // "a.Author" fa riferimento alla relazione denominata "Author"
      ->execute();
      
    // Nel template (non modificato)
    <ul>
    <?php foreach ($articles as $article): ?>
      <li><?php echo $article->getTitle() ?>,
        written by <?php echo $article->getAuthor()->getName() ?></li>
    <?php endforeach; ?>
    </ul>


### Evitare l'utilizzo di array temporanei

Anche utilizzando Propel gli oggetti vengono già idratati, quindi non c'è nessun bisogno di preparare array temporanei per i template. Gli sviluppatori non abituati all'utilizzo di un ORM spesso cadono in questa trappola. Vogliono preparare un array di stringhe o di interi nonostante il template sia in grado di utilizzare direttamente un array di oggetti esistente. Per esempio si immagini un template che mostra l'elenco di tutti i titoli degli articoli presenti nel database. Uno sviluppatore che non sfrutta l'OOP probabilmente scriverebbe del codice simile a quello riportato nel listato 18-6.

Listato 18-6 - Preparare un array nell'azione è superfluo se già se ne possiede uno

    [php]
    // In the action
    $articles = ArticlePeer::doSelect(new Criteria());
    $titles = array();
    foreach ($articles as $article)
    {
      $titles[] = $article->getTitle();
    }
    $this->titles = $titles;

    // In the template
    <ul>
    <?php foreach ($titles as $title): ?>
      <li><?php echo $title ?></li>
    <?php endforeach; ?>
    </ul>

Il problema relativo a questo codice è dato dal fatto che l'idratazione è già stata fatta dalla chiamata a `doSelect()` (che costa tempo), rendendo così l'array `$titles` superfluo dato che è possibile scrivere lo stesso codice come nel listato 18-7. In questo modo il tempo speso per costruire l'array `$titles` può essere guadagnato per migliorare le prestazioni dell'applicazione.

Listato 18-7 - Utilizzare un array di oggetti esonera dalla creazione di un array temporaneo

    [php]
    // In the action
    $this->articles = ArticlePeer::doSelect(new Criteria());
    // With Doctrine
    $this->articles = Doctrine::getTable('Article')->findAll();

    // In the template
    <ul>
    <?php foreach ($articles as $article): ?>
      <li><?php echo $article->getTitle() ?></li>
    <?php endforeach; ?>
    </ul>

Se realmente si ha la necessità di costruire un array temporaneo perché è necessaria qualche operazione sugli oggetti, il modo giusto per farlo è quello di creare un nuovo metodo nella classe del modello che restituisce direttamente quell'array. Se per esempio si avesse bisogno di un array di titoli e numero di commenti per ogni articolo l'azione e il template dovrebbero assomigliare al listato 18-8.

Listato 18-8 - Utilizzare un metodo custom per costruire un array temporaneo

    [php]
    // In the action
    $this->articles = ArticlePeer::getArticleTitlesWithNbComments();

    // In the template
    <ul>
    <?php foreach ($articles as $article): ?>
      <li><?php echo $article['title'] ?> (<?php echo $article['nb_comments'] ?> comments)</li>
    <?php endforeach; ?>
    </ul>

Sta poi allo sviluppatore costruire un metodo `getArticleTitlesWithNbComments()` performante nel modello, magari bypassando l'intero strato di astrazione dell'ORM e del database.

### Bypassare l'ORM

Quando realmente non si ha bisogno di oggetti ma solo di alcune colonne da varie tabelle, come nell'esempio precedente, si possono creare metodi specifici nel modello per bypassare completamente lo strato dell'ORM. Si può interrogare il database direttamente con PDO, per esempio, e restituire un array costruito sulla base delle proprie esigenze. listato 18-9 illustra quest'idea.

Listato 18-9 - Accesso diretto tramite PDO per metodi ottimizzati nel modello, in `lib/model/ArticlePeer.php`

    [php]
    // Con Propel
    class ArticlePeer extends BaseArticlePeer
    {
      public static function getArticleTitlesWithNbComments()
      {
        $connection = Propel::getConnection();
        $query = 'SELECT %s as title, COUNT(%s) AS nb_comments FROM %s LEFT JOIN %s ON %s = %sGROUP BY %s';
        $query = sprintf($query,
          ArticlePeer::TITLE, CommentPeer::ID,
          ArticlePeer::TABLE_NAME, CommentPeer::TABLE_NAME,
          ArticlePeer::ID, CommentPeer::ARTICLE_ID,
          ArticlePeer::ID
        );

        $statement = $connection->prepare($query);
        $statement->execute();

        $results = array();
        while ($resultset = $statement->fetch(PDO::FETCH_OBJ))
        {
          $results[] = array('title' => $resultset->title, 'nb_comments' => $resultset->nb_comments);
        }

        return $results;
      }
    }
    
    // Con Doctrine
    class ArticleTable extends Doctrine_Table
    {
      public function getArticleTitlesWithNbComments()
      {
        return $this->createQuery('a')
            ->select('a.title, count(*) as nb_comments')
            ->leftJoin('a.Comments')
            ->groupBy('a.id')
            ->fetchArray();
      }
    }

Quando si iniziano a creare metodi di questo tipo è probabile si finisca con lo scrivere un metodo personalizzato per ogni azione, perdendo quelli che sono i benefici della separazione tra strati per non menzionare poi il fatto che si perde l'indipendenza dal database.

### Migliorare le prestazioni del database

Esistono molte tecniche di ottimizzazione specificatamente per il database da utilizzare sia nel caso in cui si stia utilizzando symfony o meno. Questa sezione analizza brevemente le strategie più comuni per l'ottimizzazione dei database, tuttavia una buona padronanza del funzionamento dei database ed esperienza di amministrazione sono necessarie per ottenere il meglio dallo strato del modello.

>**TIP**
>Va ricordato che la web debug toolbar mostra il tempo impiegato da ogni query su una pagina e che ogni miglioria andrebbe monitorata per determinare se realmente migliori le prestazioni.

Le interrogazioni sulle tabelle sono spesso basate su colonne che non sono la chiave primaria. Per migliorare la velocità di tali interrogazioni è utile definire degli indici nello schema del database. Per aggiungere un indice a una singola colonna aggiungere la proprietà `index: true` alla definizione della stessa come nel listato 18-10.

Listato 18-10 - Aggiungere un index a una singola colonna, in `config/schema.yml`

    [yml]
    # Propel schema
    propel:
      article:
        id:
        author_id:
        title: { type: varchar(100), index: true }
    
    
    # Doctrine schema
    Article:
     columns:
       author_id: integer
       title: string(100)
     indexes:
       title:
         fields: [title]
     
L'utilizzo alternativo della sintassi `index: unique` è per definire un indice unico invece di uno classico. Si possono definire inoltre indici su più colonne dallo `schema.yml` (fare riferimento al capitolo 8 per maggiori dettagli sulla sintassi da utilizzare per gli indici). L'utilizzo degli indici è caldamente consigliato visto che molto spesso rappresentano una buona soluzione per migliorare le prestazioni di una query complessa.

Dopo aver aggiunto un indice allo schema andrà aggiunto anche al database stesso, ricorrendo a una query diretta `ADD INDEX` oppure utilizzando il comando `propel-build-all` (che non solo rigenera la struttura delle tabelle ma elimina anche tutti i dati esistenti).

>**TIP**
>L'utilizzo degli indici tende a rendere più veloci le query di tipo `SELECT` mentre saranno più lente `INSERT`, `UPDATE` e `DELETE`. Inoltre i motori dei database utilizzano solo un indice a interrogazione e lo scelgono a ogni query basandosi su un'euristica interna. Aggiungere un indice a volte può essere controproducente in termini di prestazioni, è bene quindi verificarne il risultato.

Se non diversamente specificato, in symfony ogni richiesta utilizza una singola connessione al database che viene chiusa alla fine della richiesta stessa. Si possono attivare le connessioni persistenti al database per utilizzare un pool di connessioni che restino aperte tra una query e l'altra impostando il parametro `persistent: true` nel file `databases.yml` come mostrato nel listato 18-11.

Listato 18-11 - Abilitare il supporto per le connessioni persistenti al database, in `config/databases.yml`

    prod:
      propel:
        class:         sfPropelDatabase
        param:
          dsn:         mysql:dbname=example;host=localhost
          username:    username
          password:    password
          persistent:  true      # Utilizza connessioni persistenti

Questo può o meno migliorare le prestazioni generali del database in funzione di molti fattori. La documentazione sull'argomento è abbondante e facilmente reperibile su Internet. Anche qui è opportuno testare le prestazioni dell'applicazione prima e dopo il cambio di questa impostazione per verificarne l'impatto.

>**SIDEBAR**
>Suggerimenti specifici per MySQL
>
>Molte impostazioni della configurazione di MySQL, situate nel file my.cnf, possono alterare le prestazioni del database. Assicurarsi di aver letto la [documentazione online](http://dev.mysql.com/doc/refman/5.0/en/option-files.html) su questo argomento.
>
>Uno degli strumenti offerti da MySQL è il log delle query lente. Tutte le interrogazioni SQL che richiedono più tempo in secondi di `long_query_time` per essere eseguite (questa è un'impostazione che può essere modificata in `my.cnf`) vengono registrate in un file che è abbastanza difficile da analizzare manualmente ma che grazie al comando `mysqldumpslow` genera un sommario molto utile. Si tratta di un ottimo strumento per individuare le query che necessitano di ottimizzazione.

Mettere a punto la vista
------------------------

In funzione di come è stato progettato e realizzato lo strato della vista si possono incontrare piccoli rallentamenti o miglioramenti. Questa sezione descrive alcune alternative e i loro compromessi.

### Utilizzare il frammento di codice più veloce

Se non si utilizza il sistema di cache si deve sapere che un `include_component()` è leggermente più lento di un `include_partial()` che a sua volta è leggermente più lento di un semplice `include` in PHP. Questo dipende dal fatto che symfony istanzia una vista per includere un partial e un oggetto della classe `sfComponent` per includere un component, che nel complesso aggiungono un po' di overhead oltre a quanto già richiesto per includere il file.

Tuttavia questo overhead è insignificante fino a quando non si includono molti partial o component in un template. Cosa che può accadere in elenchi o tabelle e ogni volta in cui si utilizza un `include_partial()` helper all'interno di un `foreach`. Quando ci si accorge che un numero considerevole di inclusioni di partial o component hanno un impatto significativo sulle prestazioni è il momento di considerare la cache (vedere capitolo 12). Se questa non fosse un'opzione praticabile si consiglia di passare all'utilizzo di semplici `include`.

Lo stesso discorso vale per gli slot, la differenza in termini di prestazioni è sensibile. Il tempo necessario per impostare e includere uno slot è irrilevante, equivalente all'impostazione di una variabile. Gli slot vengono sempre inseriti in cache assieme al template che li include.

### Accelerare il routing

Come spiegato nel capitolo 9, ogni chiamata a un helper per i link in un template richiede al sistema delle rotte di processare un URI interno in un URL esterno. Questo avviene trovando una corrispondenza tra l'URI e gli schemi del file `routing.yml`. Symfony lo fa in modo molto semplice: verifica se c'è corrispondenza tra la prima regola e l'URI, se non è così prova con la seguente e così via. Dato che ogni verifica coinvolge le espressioni regolari, questa è un'operazione piuttosto pesante in termini di risorse.

Esiste una semplice scappatoia: utilizzare il nome della rotta invece che la coppia modulo/azione. Questo dirà a symfony quale regola utilizzare e il sistema delle rotte non perderà tempo cercando una corrispondenza con tutte le regole precedenti.

In parole povere, si consideri la seguente regola per le rotte, definita nel file `routing.yml`:

    article_by_id:
      url:          /article/:id
      param:        { module: article, action: read }

Quindi invece di creare un link in questo modo:

    [php]
    <?php echo link_to('my article', 'article/read?id='.$article->getId()) ?>

si dovrebbe utilizzare la versione più veloce:

    [php]
    <?php echo link_to('my article', 'article_by_id', array('id' => $article->getId())) ?>

La differenza inizia a farsi vedere quando una pagina include alcune dozzine di link collegati alle rotte.

### Ignorare il template

Solitamente una risposta è composta da un insieme di intestazioni e di contenuti. Alcune risposte però non necessitano di contenuto. Per esempio, alcune interazioni Ajax richiedono solo alcune porzioni di dati dal server, per alimentare un programma JavaScript che si occupa di aggiornare diverse parti di una pagina. Per questo tipo di risposte brevi, un solo insieme di intestazioni è più veloce da trasmettere. Come visto nel capitolo 11, un'azione può restiture anche solo un'intestazione JSON. Il listato 18-12 propone un esempio dal capitolo 11.

Listato 18-12 - Esempio di azione che restituisce un'intestazione JSON

    [php]
    public function executeRefresh()
    {
      $output = '{"title":"My basic letter","name":"Mr Brown"}';
      $this->getResponse()->setHttpHeader("X-JSON", '('.$output.')');

      return sfView::HEADER_ONLY;
    }

Questo esclude il template e il layout: la risposta può essere inviata singolarmente. Dato che contiene solamente intestazioni, è più leggera e richiederà meno tempo per essere trasmessa all'utente.

Il capitolo 6 ha mostrato un altro modo per evitare il caricamento del template, restituendo del testo come contenuto dall'azione. Questo infrange la separazione MVC, ma può aumentare la velocità di risposta di un'azione in modo drastico. Verificare il listato 18-13 per un esempio.

Listato 18-13 - Esempio di azione che restituisce direttamente testo come contenuto

    [php]
    public function executeFastAction()
    {
      return $this->renderText("<html><body>Hello, World!</body></html>");
    }

Ottimizzare la cache
--------------------

Il capitolo 12 ha già descritto come mettere in cache porzioni di una risposta o la risposta completa. L'utilizzo della cache per le risposte rappresenta una miglioria sostanziale per le prestazioni e dovrebbe essere una delle prime ottimizzazioni da considerare. Per ottenere il massimo dal sistema della cache, si consiglia di continuare la lettura: questa sezione svelerà alcuni accorgimenti a cui non si penserebbe.

### Invalidare selettivamente porzioni di cache

Durante lo sviluppo di un'applicazione, è necessario ripulire la cache in diverse situazioni:

  * Quando si crea una nuova classe: aggiungere una classe a una delle cartelle soggette ad auto-caricamento (una delle cartelle `lib/` del progetto) non è abbastanza perché symfony possa individuarla automaticamente in ambienti non di sviluppo. È necessario svuotare la cache della configurazione dell'auto-caricamento, in modo che symfony analizzi nuovamente tutte le cartelle indicate dal file `autoload.yml` e referenzi la posizione delle classi include le nuove.
  * Quando si cambia la configurazione in produzione: la configurazione viene processata solo durante la prima richiesta in produzione. Le richieste successive utilizzano invece la versione memorizzata in cache. Quindi una modifica nella configurazione dell'ambiente di produzione (o qualunque ambiente in cui il debug è impostato a `false`) non ha effetto fino alla cancellazione della versione memorizzata in cache del file.
  * Quando si modifica un template in un ambiente dove la cache per i template è abilitata: i template validi dalla cache vengono sempre utilizzati al posto dei template in produzione, quindi una modifica a un template viene ignorata fino a quando la cache non viene cancellata o diventa obsoleta.
  * Quando si aggiorna un'applicazione con il comando `project:deploy`: questo caso solitamente comprende le tre modifiche appena viste.

Il problema della cancellazione dell'intera cache è rappresentato dal fatto che la richiesta successiva richiederà un tempo più lungo per essere processata, perché la cache della configurazione deve essere rigenerata. Inoltre i template non modificati verranno anch'essi rimossi dalla cache, perdendo i benefici delle richieste precedenti.

Questo significa che è una buona idea rimuovere dalla cache solamente i file che realmente necessitano di essere rigenerati. Utilizzare le opzioni del task `cache:clear` per definire un sottoinsieme di file della cache da rimuovere come dimostrato nel listato 18-14.

Listato 18-14 - Rimuovere solo parti specifiche della cache

    // Rimuovere solamente la cache dell'applicazione frontend
    $ php symfony cache:clear frontend

    // Rimuovere solo la cache HTML dell'applicazione frontend
    $ php symfony cache:clear frontend template

    // Rimuovere solo la cache dei file di configurazione dell'applicazione frontend
    $ php symfony cache:clear frontend config

Si possono rimuovere i file anche manualmente nella cartella `cache/` o eliminare i file di cache dei template selettivamente dall'azione, con il metodo `$cacheManager->remove()`, come descritto nel capitolo 12.

Tutte queste tecniche minimizzeranno l'impatto negativo sulle prestazioni di ognuna delle modifiche elencate precedentemente.

>**TIP**
>Quando si aggiorna symfony, la cache viene rimossa automaticamente senza intervento manuale (se il parametro `check_symfony_version` è impostato a `true` nel file `settings.yml`).

### Generare pagine in cache

Quando si mette in produzione una nuova applicazione, la cache dei template è vuota. È necessario aspettare che gli utenti visitino una pagina perché essa venga inserita in cache. Nei rilasci più critici, l'overhead del processo di una pagina non è accettabile e i benefici della cache devono essere disponibili già alla prima richiesta.

La soluzione è rappresentata dalla visita delle pagine dell'applicazione nell'ambiente di stage (dove la configurazione è simile a quella di produzione) per generare la cache dei template e poi trasferire l'applicazione con la cache in produzione.

Per visitare le pagine in modo automatico, un'opzione è quella di creare uno script di shell che analizza una lista di URL esterni con un browser (curl per esempio). Esiste però una soluzione migliore e più veloce: uno script PHP che utilizza l'oggetto `sfBrowser` già visto al capitolo 15.  Si tratta di un browser interno scritto in PHP (e utilizzato da `sfTestFunctional` per i test funzionali). Accetta un URL esterno e restituisce una risposta, ma la cosa interessante è che scatena la creazione della cache del template, proprio come un browser tradizionale. Dato che inizializza symfony solamente una volta e non utilizza lo strato di trasporto HTTP, questo metodo risulta molto veloce.

Il listato 18-15 mostra uno script d'esempio utilizzato per generare cache dei template nell'ambiente di stage. Avviarlo chiamando `php generate_cache.php`.

Listato 18-15 - Generare la cache dei template, in `generate_cache.php`

    [php]
    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'staging', false);
    sfContext::createInstance($configuration);

    // Array di URL da visitare
    $uris = array(
      '/foo/index',
      '/foo/bar/id/1',
      '/foo/bar/id/2',
      ...
    );

    $b = new sfBrowser();
    foreach ($uris as $uri)
    {
      $b->get($uri);
    }

### Utilizzare un sistema di storage su database per la cache

Il sistema predefinito di storage per la cache dei template in symfony è il file system: porzioni di HTML o oggetti di risposta serializzati vengono memorizzati nella cartella `cache/` del progetto. Symfony propone anche una via alternativa per la memorizzazione della cache: un database SQLite. Tale database è un semplice file che PHP sa già come interrogare molto efficaciemente in modo nativo.

Per far utilizzare a symfony lo storage SQLite invece che il file system per la cache dei template modificare il parametro `view_cache` nel file `factories.yml` come segue:

    view_cache:
      class: sfSQLiteCache
      param:
        database: %SF_TEMPLATE_CACHE_DIR%/cache.db

I benefici offerti dall'utilizzo dello storage SQLite per la cache dei template sono operazioni di lettura e scrittura più veloci quando il numero degli elementi in cache si fa importante. Se l'applicazione fa grosso uso del sistema di cache, i file di cache vengono archiviati in una struttura piuttosto profonda; in questo caso il passaggio allo storage SQLite garantirà migliori prestazioni. Si aggiunga che la cancellazione della cache richiede, per il file system storage, di rimuovere molti file dal disco e quest'operazione può durare alcuni secondi durante i queli l'applicazione non sarà disponibile. Con uno storage SQLite la cancellazione della cache consiste in una singola operazione su file: la cancellazione del file del database SQLite. Indipendentemente dal numero di elementi inseriti in cache l'operazione è istantanea.

### Aggirare symfony

Forse il modo migliore per velocizzare symfony è quello di aggirarlo completamente... questo è solo in parte per scherzo. Alcune pagine non cambiano e non hanno la necessità di essere riprocessate dal framework a ogni richiesta. La cache dei template viene già utilizzata per accelerare la consegna delle pagine, ma si basa ancora su symfony.

Un paio di suggerimenti descritti nel capitolo 12 permettono di aggirare symfony totalmente per alcune pagine. Il primo coinvolge l'utilizzo delle intestazioni HTTP 1.1 per chiedere ai proxy e ai browser client di mettere in cache le pagine in modo autonomo, così non le richiederanno la prossima volta che la pagina sarà necessaria. Il secondo suggerimento è la super fast cache (automatizzata dal plug-in `sfSuperCachePlugin`), che consiste nel memorizzare una copia della risposta nella cartella `web/`, modificando le regole di rewrite per fare in modo che Apache cerchi una versione in cache prima di inoltrare la richiesta a symfony.

Tutti e due questi metodi sono molto efficaci e, anche se sono applicabili solo a pagine statiche, si fanno carico della gestione di queste pagine senza coinvolgere symfony, permettendo così al server di essere completamente disponibile per le richieste più complesse.

### Mettere in cache il risultato di una chiamata a una funzione

Se una funzione non utilizza valori dipendenti dal contesto o non casuali, chiamandola due volte con gli stessi parametri dovrebbe fornire lo stesso risultato. Questo significa che la seconda chiamata potrebbe davvero essere evitata nel caso un cui si fosse memorizzato il primo risultato. Questo è esattamente ciò che la classe `sfFunctionCache` si occupa di fare. Questa classe ha un metodo `call()` che si aspetta un callable e un array di parametri. Quando invocato questo metodo crea un hash md5 con tutti i parametri e cerca nella cache una chiave denominata con quell'hash. Se la chiave viene trovata, la funzione restituisce il risultato memorizzato nella cache. Altrimenti, `sfFunctionCache` esegue la funzione, memorizza il risultato nella cache e lo restituisce. In questo modo, la seconda esecuzione del listato 18-16 sarà più veloce della prima.

Listato 18-16 - Mettere in cache il risultato di una funzione

    [php]
    $cache = new sfFileCache(array('cache_dir' => sfConfig::get('sf_cache_dir').'/function'));
    $fc = new sfFunctionCache($cache);
    $result1 = $fc->call('cos', array(M_PI));
    $result2 = $fc->call('preg_replace', array('/\s\s+/', ' ', $input));

Il costruttore della classe `sfFunctionCache` si aspetta un oggetto di tipo cache. Il primo parametro del metodo `call()` deve essere un callable, quindi può essere il nome di una funzione, un array contenente il nome di una classe e il nome di un metodo statico oppure un array con il nome di un oggetto e il nome di un metodo pubblico. Lo stesso vale per l'altro parametro del metodo `call()`, si tratta di un array di parametri che verranno passati al callable.

>**CAUTION**
>Se si utilizza un oggetto cache basato su file come nell'esempio è consigliabile utilizzare una cartella all'interno della cartella `cache/`, così facendo verrà svuotata automaticamente dal task `cache:clear`. Se si memorizza la cache della funzione da qualche altra parte non verrà rimossa automaticamente quando si svuoterà la cache utilizzando la linea di comando.

### Mettere in cache i dati sul server

Gli acceleratori PHP mettono a disposizione funzioni speciali per memorizzare dati in memoria per poterli riutilizzare su più richieste. Il problema è che hanno tutti sintassi diverse e ognuno ha il suo modo speciale di assolvere questo compito. Le classi della cache di symfony riescono ad astrarre tutte queste differenze e funzionano con qualsiasi acceleratore si decida di utilizzare. Si analizzi il listato 18-17.

Listato 18-17 - Utilizzare un acceleratore PHP per memorizzare dati

    [php]
    $cache = new sfAPCCache();

    // Memorizzare dati nella cache
    $cache->set($name, $value, $lifetime);

    // Recuperare i dati
    $value = $cache->get($name);

    // Verificare se un dato esiste nella cache
    $value_exists = $cache->has($name);

    // Ripulire la cache
    $cache->clear();

Il metodo `set()` restituisce il valore `false` se il processo non ha funzionato. Il valore inserito in cache può essere qualsiasi cosa (stringa, array, oggetto); la classe `sfAPCCache` lo permette ricorrendo alla serializzazione. Il metodo `get()` restituisce `null` se la variabile richiesta non esiste nella cache.

>**TIP**
>Per approfondire il discorso della cache in memoria è fondamentale analizzare la classe `sfMemcacheCache`. Mette a disposizione la stessa interfaccia come le altre classi per la cache e può essere d'aiuto per ridurre il carico del database su applicazioni bilanciate.

Disattivare le funzionalità inutilizzate
----------------------------------------

La configurazione standard di symfony attiva le funzionalità più comuni per le applicazione web. Tuttavia, se non si ha la necessità di utilizzarle tutte, sarebbe opportuno disattivarle per risparmiare il tempo necessario alla loro inizializzazione consumato a ogni richiesta.

Per esempio nel caso in cui l'applicazione non utilizzi il meccanismo delle sessioni, o si volesse attivare la gestione delle sessioni manualmente, si dovrebbe modificare il valore del parametro `auto_start` impostandolo a `false` sotto la chiave `storage` nel file `factories.yml` come nel listato 18-18.

Listato 18-18 - Disabilitare le sessioni, in `frontend/config/factories.yml`

    all:
      storage:
        class: sfSessionStorage
        param:
          auto_start: false

Lo stesso dicasi per le funzionalità del database (come descritto precedentemente nella sezione "Tweaking the Model" in questo capitolo). Se l'applicazione non utilizza un database si può disattivare per un piccolo guadagno nelle prestazioni nel file `settings.yml` (vedere listato 18-19).

Listato 18-19 - Disabilitare il database, in `frontend/config/settings.yml`

    all:
      .settings:
        use_database:      false    # Funzionalità per database e modello

Lo stesso vale per le funzionalità di sicurezza (vedere capitolo 6) che si possono disattivare nel file `filters.yml`, come mostrato nel listato 18-20.

Listato 18-20 - Disabilitare funzionalità di sicurezza, in `frontend/config/filters.yml`

    rendering: ~
    security:
      enabled: false

    # generally, you will want to insert your own filters here

    cache:     ~
    execution: ~

Alcune funzionalità sono utili solamente nell'ambiente di sviluppo, non vanno quindi attivate in produzione. Questa è già la situazione standard dato che l'ambiente di produzione di symfony è totalmente ottimizzato per le migliori prestazioni. Tra tutte le funzionalità di sviluppo che hanno un impatto sulle prestazioni, la modalità di debug è sicuramente la più severa. Come per i log di symfony la funzionalità è già disabilitata nell'ambiente di produzione.

Ci si potrebbe chiedere come ottenere informazioni sulle richieste fallite nell'ambiente di produzione se il log è disabilitato, facendo notare anche che i problemi non si manifestano solo durante lo sviluppo. Fortunatamente symfony può utilizzare il plugin `sfErrorLoggerPlugin` che lavora in background nell'ambiente di produzione e registra i dettagli degli errori 404 e 500 in un database. È molto più veloce della funzionalità di log su file dato che i metodi del plug-in vengono invocati solamente quando una richiesta fallisce, mentre il meccanismo di log, se attivo, aggiunge un overhead non indifferente, indipendentemente dal livello impostato. Controllare le istruzioni di installazione e il [manuale](http://plugins.symfony-project.org/plugins/sfErrorLoggerPlugin).

>**TIP**
>È buona abitudine controllare regolarmente i log degli errori del server dato che contengono informazioni molto utili riguardo agli errori 404 e 500.

Ottimizzare il proprio codice
-----------------------------

È possibile rendere più performante un'applicazione ottimizzandone il codice. Questa sezione offre alcuni spunti su come fare ciò.

### Compilazione del core

Caricare dieci file richiede più operazioni di I/O rispetto al caricamento di un grande file, specialmente su dischi lenti. Caricare un file molto grande richiede più risorse rispetto a caricarne uno più piccolo, specialmente se grossa parte del contenuto del file non è di alcun interesse per il parser PHP, è il caso dei commenti.

Quindi fondere un grosso numero di file eliminandone i commenti contenuti è un'operazione che migliora le prestazioni. Symfony esegue già tale ottimizzazione, si chiama compilazione del core. All'inizio della prima richiesta (o dopo aver svuotato la cache) un'applicazione symfony concatena tutte le classi del core del framework (`sfActions`, `sfRequest`, `sfView` e così via) in un unico file, riduce la dimensione del file rimuovendo commenti e doppi spazi e salva tutto nella cache in un file chiamato `config_core_compile.yml.php`. Ogni richiesta seguente caricherà solamente questo singolo file ottimizzato invece che i 30 file che lo compongono.

Se l'applicazione ha classi che devono essere caricare ogni volta, specialmente se sono classi grandi con molti commenti, può essere un vantaggio aggiungerle al file compilato del core. Per fare questo basta aggiungere un file `core_compile.yml` nella cartella `config/` dell'applicazione in cui si elencheranno le classi che si vogliono aggiungere come nel listato 18-21.

Listato 18-21 - Aggiungere le proprie classi al file compilato del core, in `frontend/config/core_compile.yml`

    - %SF_ROOT_DIR%/lib/myClass.class.php
    - %SF_ROOT_DIR%/apps/frontend/lib/myToolkit.class.php
    - %SF_ROOT_DIR%/plugins/myPlugin/lib/myPluginCore.class.php
    ...

### Il task `project:optimize`

Symfony mette a disposizione anche un altro strumento di ottimizzazione, il task `project:optimize`. Applica varie strategie di ottimizzazione al codice di symfony e dell'applicazione che possono migliorare ulteriormente le prestazioni.

    $ php symfony project:optimize frontend prod

Per vedere le strategie di ottimizzazione utilizzate nel task basta dare un'occhiata al suo codice sorgente.

Sommario
--------
Symfony è già un framework molto ottimizzato e in grado di gestire siti ad alto traffico senza problemi. Ma se davvero si avesse la necessità di ottimizzare ulteriormente le prestazioni della propria applicazione, mettere a punto la configurazione (che sia la configurazione del server, di PHP o le impostazioni dell'applicazione) può fornire un piccolo miglioramento. È consigliabile seguire le best practice per scrivere metodi del modello efficienti; e dato che il database rappresenta sempre un collo di bottiglia per le applicazioni web su di esso andrà riposta particolare attenzione. I template possono beneficiare anch'essi di alcune ottimizzazioni, ma i miglioramenti più evidenti arriveranno dall'utilizzo del sistema della cache. Infine non si esiti nell'analizzare plug-in esistenti dato che alcuni di essi mettono a disposizione tecniche innovative per aumentare ulteriormente la consegna delle pagine web (`sfSuperCache`, `project:optimize`).
