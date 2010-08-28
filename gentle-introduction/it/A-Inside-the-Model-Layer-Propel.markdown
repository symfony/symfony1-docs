Appendice A - All'interno del livello del modello (Propel)
==========================================================

Gran parte della trattazione finora è stata dedicata alla costruzione di pagine e all'elaborazione delle richieste e delle risposte. Ma la business logic di una applicazione web si basa principalmente sul suo modello di dati. Il componente predefinito di symfony per il modello, è basato su un livello che mappa oggetti e relazioni. Symfony è distrubuito con i due più popolari ORM per PHP: [Propel](http://www.propelorm.org/) e [Doctrine](http://www.doctrine-project.org/). In un'applicazione symfony, si accede ai dati memorizzati in un database e li si modifica, attraverso gli oggetti; non è necessario riferirsi direttamente al database. Quest'ultimo mantiene un elevato livello di astrazione e portabilità.

Questo capitolo spiega come creare un modello di dati a oggetti e come accedere ai dati e modificarli con Propel. Viene anche trattata l'integrazione di Propel con symfony.

Perché usare un ORM e un livello per l'astrazione?
--------------------------------------------------

I database sono relazionali. PHP 5 e symfony sono orientati agli oggetti. Per poter accedere nel modo più efficace al database in un contesto orientato agli oggetti, è indispensabile una interfaccia per tradurre la logica degli oggetti nella logica relazionale. Come spiegato nel capitolo 1, questa interfaccia è chiamata Object-Relational Mapping (ORM) ed è costituita di oggetti che forniscono l'accesso ai dati e mantengono le business rules all'interno di se stessi.

Il vantaggio principale di un ORM è la riutilizzabilità, che consente ai metodi di un oggetto di tipo dato, di essere chiamato da varie parti dell'applicazione, anche da diverse applicazioni. Il livello ORM incapsula anche la logica dei dati, ad esempio, il calcolo del punteggio degli utenti di un forum basato su quanti contributi sono stati fatti e quanto sono popolari. Quando una pagina deve visualizzare un tale punteggio degli utenti, basta chiamare semplicemente un metodo nel modello dei dati, senza preoccuparsi dei dettagli del calcolo. Se in seguito bisogna modificare il calcolo, sarà sufficiente modificare il metodo nel modello, lasciando il resto dell'applicazione invariata.

Usare oggetti al posto di record e classi al posto di tabelle, ha un altro vantaggio: la possibilità di aggiungere agli oggetti nuove funzioni di accesso che non necessariamente corrispondono a una colonna in una tabella. Per esempio, se si ha una tabella chiamata `cliente` con due campi chiamati `nome` e `cognome`, si potrebbe volere la possibilità di chiedere solo il `Nome`. In un mondo orientato agli oggetti, basta aggiungere un nuovo metodo accessor alla classe `Cliente`, come si può vedere nel Listato 8-1. Dal punto di vista dell'applicativo, non vi è alcuna differenza tra `Nome`, `Cognome`, e `NomePersona`: sono tutti attributi della classe `Cliente`. Solo la classe stessa può determinare quali attributi corrispondono a una colonna del database.

Listato 8-1 - Il metodo accessor maschera la struttura della tabella in una classe del modello

    [php]
    public function getName()
    {
      return $this->getFirstName().' '.$this->getLastName();
    }

Tutte le funzioni ripetute di accesso ai dati e la business logic dei dati stessi, possono essere tenute in tali oggetti. Supponiamo di avere una classe `ShoppingCart` in cui si tenere gli `Articoli` (che sono oggetti). Per ottenere l'importo totale del carrello della spesa, necessario per il pagamento, bisogna scrivere un metodo personalizzato per incapsulare il calcolo effettivo, come mostrato nel Listato 8-2.

Listato 8-2 - Il metodo accessor maschera la logica dei dati

    [php]
    public function getTotal()
    {
      $total = 0;
      foreach ($this->getItems() as $item)
      {
        $total += $item->getPrice() * $item->getQuantity();
      }

      return $total;
    }

C'è un altro punto importante da considerare quando si realizzano delle procedure di accesso ai dati: ogni database utilizza una variante diversa di sintassi SQL. Il passaggio a un altro DataBase Management System (DBMS) costringe a riscrivere parte delle query SQL che sono state progettate per quello precedente. Costruendo le query utilizzando una sintassi indipendente dal database e lasciando la traduzione reale nell'SQL a un componente di terze parti, è possibile cambiare il tipo di database senza troppi problemi. Questo è l'obiettivo del livello di astrazione del database. Costringe a usare una sintassi specifica per le query e fa il lavoro sporco di conformarsi alle particolarità del DBMS e di ottimizzare il codice SQL.

Il principale vantaggio del livello di astrazione è la portabilità, perché rende possibile il passaggio a un'altra base di dati, anche nel bel mezzo di un progetto. Si supponga di dover scrivere rapidamente un prototipo per un'applicazione, ma il cliente non ha ancora deciso quale sistema di base dati può essere la più adatto alle sue esigenze. Si può cominciare a costruire l'applicazione con SQLite, per esempio e passare a MySQL, PostgreSQL, Oracle quando il cliente ha fatto la scelta. Per fare il cambiamento, basta cambiare una riga in un file di configurazione.

Symfony usa Propel o Doctrine come ORM, e questi usano oggetti PHP per l'astrazione dei dati del database. Queste due componenti di terze parti, entrambi sviluppati dal team di Propel e Doctrine, sono perfettamente integrati in symfony ed è possibile considerarli come parte del framework. La loro sintassi e le loro convenzioni, descritte in questo capitolo, sono state adattate in modo da differenziarsi il meno possibile da quelle di symfony.

>**NOTE**
>In un progetto symfony, tutte le applicazioni condividono lo stesso modello. Questo è un punto fondamentale a livello di progetto: raggruppare le applicazioni che si basano su regole di business comuni. Questa è la ragione per cui il modello è indipendente dalle applicazioni e i file del modello sono memorizzati in una cartella `lib/model/` nella radice del progetto.

Lo schema del database di symfony
-------------------------

Allo scopo di creare il modello a oggetti dei dati che symfony userà, bisogna tradurre tutti i modelli relazionali del database in un modello dati a oggetti. L'ORM ha bisogno di una descrizione del modello relazionale per fare la mappatura e questo è chiamato schema. In uno schema si definiscono le tabelle, le relazioni e le caratteristiche delle colonne.

La sintassi di symfony per gli schemi utilizza il formato YAML. I file `schema.yml` devono essere messi nella cartella `mioprogetto/config`.

>**NOTE**
>Symfony riconosce inoltre la sintassi native di Propel in XML, come descritto nella sezione "Oltre schema.yml: schema.xml" più avanti in questo capitolo.

### Esempio di schema

Come tradurre la struttura del database in uno schema? Un esempio è il modo migliore per capirlo. Immaginiamo di avere il database di un blog con due tabelle: `blog_articolo` e `blog_commento`, con la struttura mostrata in Figura 8-1.

Figura 8-1 - Struttura delle tabelle del database di un blog

![Struttura delle tabelle del database di un blog](http://www.symfony-project.org/images/book/1_4/F0801.png "Struttura delle tabelle del database di un blog")

Il relativo file `schema.yml` dovrebbe apparire come nel Listato 8-3.

Listato 8-3 - Esempio di file `schema.yml`

    [yml]
    propel:
      blog_article:
        _attributes: { phpName: Article }
        id:          ~
        title:       varchar(255)
        content:     longvarchar
        created_at:  ~
      blog_comment:
        _attributes: { phpName: Comment }
        id:               ~
        blog_article_id:  ~
        author:           varchar(255)
        content:          longvarchar
        created_at:       ~

Notare che il nome del database (`blog`) non compare nel file `schema.yml`. Il database invece è descritto con un nome di connessione (`propel` in questo esempio). Questo perché le impostazioni di connessione effettive possono dipendere dall'ambiente in cui l'applicazione è in esecuzione. Per esempio, quando si esegue l'applicazione nell'ambiente di sviluppo, si accede a un database di sviluppo (può essere `blog_dev`), ma con lo stesso schema del database di produzione. Le impostazioni di connessione saranno specificate nel file `databases.yml`, descritto più avanti in questo capitolo nella sezione "Connessioni del database". Lo schema non contiene nessuna impostazione di connessione, solo un nome di connessione, per mantenere l'astrazione del database.

### Sintassi di base dello schema

In un file `schema.yml`, la prima chiave rappresenta un nome della connessione. Può contenere diverse tabelle, ognuna con un set di colonne. In base alla sintassi YAML, le chiavi terminano con un simbolo di due punti, e la struttura è mostrata tramite indentazione (uno o più spazi, non tabulazioni).

Una tabella può avere attributi speciali, incluso `phpName` (il nome della classe che verrà generata). Se non vuoi usare un `phpName` per una tabella, symfony lo creerà in base alla versione camelCase del nome della tabella stessa.

>**TIP**
>La convezione camelCase rimuove i trattini bassi dalle parole, e rende maiuscole le prime lettere delle parole interne. La versione camelCase di default di `blog_articolo` e `blog_commento` sono `BlogArticolo` e `BlogCommento`. Il nome di questa convenzione viene dall'aspetto delle lettere maiuscole all'interno di una lunga parola, come le gobbe di un cammello.


Una tabella contiene colonne. Il valore delle colonne può essere definito in tre differenti modi:

  * Se non definisci nulla (`~` in YAML equivale a `null` in PHP), symfony indovinerà i migliori attributi in base al nome della colonna e alcune convenzioni che saranno descritte nella sezione "Colonne vuote" più avanti in questo capitolo. Per esempio, la colonna `id` nel Listato 8-3 non ha bisogno di essere definita: Symfony la renderà un intero auto incrementale, chiave primaria della tabella. La colonna `blog_article_id` nella tabella `blog_comment` sarà capita come una chiave esterna verso la tabella `blog_article` (le colonne che terminano con `_id` sono considerate chiave esterne, e la tabella collegata è determinata automaticamente a seconda della prima parte del nome della colonna). Le colonne chiamate `created_at` sono automaticamente impostate al tipo `timestamp`. Per queste colonne, non devi specificare nessun tipo. Questo è il motivo per cui `schema.yml` è così semplice da scrivere.

  * Se definisci soltanto un attributo, questo sarà il tipo della colonna. Symfony conosce i classici tipi: `boolean`, `integer`, `float`, `date`, `varchar(lunghezza)`, `longvarchar` (convertito, per esempio, a `text` in MySQL), e così via. Per stringhe superiori ai 256 caratteri devi usare il valore `longvarchar` che non ha lunghezza (ma non può eccedere 65KB in MySQL).

  * Se hai bisogno di definire altri attributi (come il valore di default, se il campo è obbligatorio, e così via), dovresti scrivere gli attributi della colonna come un insieme di `chiave: valore`. Questa sintassi estesa è spiegata più avanti nel capitolo.

Le colonne possono avere un attributi `phpName`, che è la versione con prima lettera maiuscola del nome (`Id`, `Title`, `Content`, e così via) e non ha bisogno di essere specificata nella maggior parte dei casi.

Le tabelle possono inoltre contenere esplicite chiavi esterne e indici, come anche alcune definizioni specifiche per alcuni database. Controlla la sezione `Sintassi estesa dello schema` più avanti in questo capitolo per saperne di più.

Le classi del modello
-------------
Lo schema è usato per costruire le classi del modello nel livello ORM. Per risparmiare tempo di esecuzione, queste classi sono generate con un task a riga di comando chiamato `propel:build-model`.

    $ php symfony propel:build-model

>**TIP**
>Dopo aver generato il modello, bisogna ricordarsi di cancellare la cache interna di symfony con `php symfony cc` in modo che symfony possa trovare i nuovi modelli creati.

La digitazione del comando lancerà l'analisi dello schema e la generazione delle classe base del modello dei dati nella cartella `lib/model/om/` del progetto:

  * `BaseArticle.php`
  * `BaseArticlePeer.php`
  * `BaseComment.php`
  * `BaseCommentPeer.php`

Inoltre nella cartella `lib/model/` verranno create le classi personalizzate del modello:

  * `Article.php`
  * `ArticlePeer.php`
  * `Comment.php`
  * `CommentPeer.php`

Sono stati definiti solo due modelli e ci si ritrova con otto file. Non c'è nulla di sbagliato, ma questo risultato merita una ulteriore spiegazione.

### Classi base e personalizzate

Perché tenere due versioni dello stesso modello a oggetti dei dati, in due diverse cartelle?

Probabilmente si avrà bisogno di aggiungere metodi e proprietà agli oggetti del modello (pensiamo al metodo `getNome()` nel Listato 8-1). Mano a mano che il progetto si evolve, si vorranno aggiungere tabelle o colonne. Ogni volta che si cambia il file `schema.yml`, bisogna rigenerare le classi del modello a oggetti facendo una nuova chiamata di doctrine:build-model. Se i metodi personalizzati venissero scritti nelle classi generate, sarebbero cancellati dopo ogni rigenerazione.

Le classi `Base` presenti nella cartella `lib/model/om/` sono le uniche effettivamente generate dallo schema. Non bisogna mai modificarle, dal momento che nuove ricostruzioni del modello cancelleranno completamente questi file.

D'altra parte, le classi di oggetti personalizzati presenti nella cartella `lib/model/`, di fatto ereditano da quelle Base`. Quando il task `propel:build-model` è chiamato su un modello esistente, queste classi non vengono modificate. Quindi questo è il posto dove aggiungere i metodi personalizzati.

Il Listato 8-4 mostra un esempio di una classe personalizzata del modello, così come viene creata dopo la prima chiamata del task `propel:build-model`.

Listing 8-4 - Esempio di file di una classe del modello, in `lib/model/Article.php`

    [php]
    class Article extends BaseArticle
    {
    }

La classe `Article` eredita ogni cosa della classe `BaseArticle`, ma modifiche nello schema non hanno effetti su `Article`.

Il meccanismo delle classi personalizzate che estendono delle classi base consente di iniziare lo sviluppo, anche senza conoscere il modello relazionale finale del database. La relativa struttura dei file rende il modello sia personalizzabile che estendibile.

### Oggetti e classi Peer

`Article` e `Comment` sono classi oggetto che rappresentano una riga nel database. Danno accesso alle colonne di una riga e colonne collegate. Questo significa che sarai capace di conoscere il titolo di un articolo chiamando un metodo sull'oggetto `Article`, come nell'esempio mostrato nel Listato 8-5.

Listato 8-5 - Getter per colonne dei record sono disponibili nelle classi oggetto

    [php]
    $article = new Article();
    // ...
    $title = $article->getTitle();

`ArticlePeer` e `CommentPeer` sono classi peer; ovvero, classi che contengono metodi statici che operano sulle tabelle. Possono fornire un modo per recuperare righe dalle tabelle. I loro metodi ritornano solitamente un oggetto o collezione di oggetti della collegata classe oggetto, come mostrato nel Listato 8-6.

Listato 8-6 - Metodi statici per recuperare record sono disponibili nelle classi peer

    [php]
    // $articles è un array di oggetti di classe Article
    $articles = ArticlePeer::retrieveByPks(array(123, 124, 125));

>**NOTE**
>Da un punto di vista di modello di dati, non potrebbe esserci un oggetto peer. Per questo che i metodi delle classi peer sono chiamati con `::` (operatore per metodi statici), al posto del solito `->` (per le chiamate a metodi d'istanza).

Per questo combinare classi oggetto e peer, base e personalizzate risulta in quattro classi generate per ogni tabella descritta nello schema. A dir la verità, c'è una quinta classe creata nella directory `lib/model/map/`, che contiene metadati riguardo le tabelle ed è necessaria per l'ambiente di esecuzione. Ma dato che probabilmente non avrai mai a che fare con questa classe, te ne puoi tranquillamente dimenticare.

Accesso ai dati
---------------

In symfony si accede ai dati attraverso oggetti. Se si è abituati al modello relazionale e a usare l'SQL per recuperare e modificare i dati, i metodi a oggetti del modello potranno sembrare complicati inizialmente. Ma una volta che si prova la potenza dell'accesso ai dati tramite interfaccia orientata agli oggetti, probabilmente ci si troverà a proprio agio.

Ma prima, vediamo di essere sicuri di condividere lo stesso vocabolario. Il modello dei dati relazionale e a oggetti utilizza concetti simili, ma ciascuno ha una propria nomenclatura:

Relazionale   | Orientato agli oggetti
------------- | ----------------------
Tabella       | Classe
Riga, record  | Oggetto
Campo, colonna| Proprietà

### Recuperare il valore della colonna

Quando symfony costruisce il modello, crea una classe base di un oggetto per ciascuno dei modelli definiti nel file `schema.yml`. Ciascuna di queste classi è dotata di accessori e modificatori predefiniti generati in base alle definizioni della colonna: i metodi `new`, `getXXX()` e `setXXX()` aiutano a creare oggetti e forniscono accesso alle proprietà dell'oggetto, come mostrato nel Listato 8-7.

Listato 8-7 - Metodi generati nella classe dell'oggetto

    [php]
    $article = new Article();
    $article->setTitle('Il mio primo articolo');
    $article->setContent("Questo è il mio primo articolo.\n Spero che possa piacere!");

    $title   = $article->getTitle();
    $content = $article->getContent();

>**NOTE**
>La classe oggetto generata si chiama `Article`, che è il `phpName` dato alla tabella `blog_article`. Se `phpName` non è stato definito nello schema, la classe sarebbe stata chiamata `BlogArticle`. Gli accessori e modificatori usano una variante camelCase del nome delle colonne, quindi il metodo `getTitle()` resituirà il valore della colonna `title`.

Impostare diversi valori in un unica volta, puoi usare il metodo `fromArray()`, generato per ogni classe oggetto, come mostrato nel Listato 8-8.

Listato 8-8 - Il metodo `fromArray()` è un setter multiplo

    [php]
    $article->fromArray(array(
      'Title'   => 'Il mio primo articolo',
      'Content' => 'Questo è il mio primo articolo.\n Spero che possa piacere!',
    ));

>**NOTE**
>Il metodo `fromArray()` ha un secondo argomento, `keyType`. Puoi specificare il tipo di chiave dell'array passando uno tra `BasePeer::TYPE_PHPNAME`, `BasePeer::TYPE_STUDLYPHPNAME`, `BasePeer::TYPE_COLNAME`, `BasePeer::TYPE_FIELDNAME` e `BasePeer::TYPE_NUM`. Il valore di default è PhpName (i.e. `AuthorId`).

### Recuperare i record correlati

La colonna `blog_article_id` nella tabella `blog_comment` definisce implicitamente una chiave esterna verso la tabella `blog_article`. Ogni commento è collegato ad un articolo e un articolo può avere più commenti. Le classi generate possono contenere cinque metodi che traducono queste relazioni in codice ad oggetti, come segue:

  * `$comment->getArticle()`: Per ottenere il relativo oggetto `Article`
  * `$comment->getArticleId()`: Per ottenere l'ID del relativo oggetto `Article`
  * `$comment->setArticle($article)`: Per impostare il relativo oggetto `Article`
  * `$comment->setArticleId($id)`: Per impostare il relativo oggetto `Article` tramite il suo ID
  * `$article->getComments()`: Per ottenere un array con i relativi oggetti `Comment`

I metodi `getArticleId()` e `setArticleId()` mostrano che puoi considerare la colonna `blog_article_id` come una colonna normale e impostare le relazioni a mano, ma non sono molto interessanti. Il beneficio dell'approccio orientato agli oggetti è molto più evidente negli altri tre metodi. Il Listato 8-9 mostra come usare il setter generati.

Listato 8-9 - Le chiavi esterne sono tradotte in speciali setter

    [php]
    $comment = new Comment();
    $comment->setAuthor('Steve');
    $comment->setContent('Accidenti, amico, sei forte: miglior articolo di sempre!');

    // Collega questo commento al precedente oggetto $article
    $comment->setArticle($article);

    // Sintassi alternativa
    // Ha senso soltanto se l'oggetto è già salvato nel database
    $comment->setArticleId($article->getId());

Il Listato 8-10 mostra come usare i getter generati. Può inoltre dimostrare come concatenare chiamate ai metodi di oggetti del modello.

Listato 8-10 - Chiavi esterne sono trasformate in speciali getter

    [php]
    // Relazione molti-a-uno
    echo $comment->getArticle()->getTitle();
     => Il mio primo articolo
    echo $comment->getArticle()->getContent();
     => Questo è il mio primo articolo.
        Spero che possa piacere!

    // Relazione uno-a-molti
    $comments = $article->getComments();

Il metodo `getArticle()` ritorna un oggetto di classe `Article`, con i benefici del metodo accessore `getTitle()`. Questo è molto migliore di dover eseguire la join tu stesso, che potrebbe richiedere alcune linee di codice (a partire dalla riga `$comment->getArticleId()`).

La variabile `$comments` nel listato 8-10 contiene un array di oggetti di classe `Comment`. Puoi mostrare il primo con `$comments[0]` o iterare sulla collezione con `foreach ($comments as $comment)`.

>**NOTE**
>Oggetti del modello sono definiti con un nome singolare per convenzione, e ora si capisce perché. La chiave esterna definita nella tabelle `blog_comment` causa la creazione del metood `getComments()`, chiamato aggiungendo una `s` al nome dell'oggetto `Comment`. Se tu dessi un nome plurale al modello, la generazione porterebbe ad un metodo chiamato `getCommentss()`, che non avrebbe senso.

### Salvare ed eliminare dati

Chiamando il costruttore con `new`, hai creato un nuovo oggetto, ma non un record nella tabella `blog_article`. Nemmeno modificare l'oggetto ha effetto sul database. Per salvare dati nel database, devi chiamare il metodo `save()` sull'oggetto.

    [php]
    $article->save();

L'ORM è intelligente abbastanza da capire le relazioni tra gli oggetti, quindi salvare l'oggetto `$article` salverà anche il relativo oggetto `$comment`. Conosce inoltre se l'oggetto salvato è già presente nel database, quindi una chiamata a `save()` sarà tradotta in una istruzione `INSERT` oppure `UPDATE` in SQL. La chiave primaria è impostata automaticamente dal metodo `save()`, quindi dopo il salvataggio, puoi recuperare la nuova chiave primaria con `$article->getId()`.

>**TIP**
>Puoi controllare se un oggetto è nuovo chiamando `isNew()`. E se ti domandi se un oggetto è stato modificato e necessita del salvataggio, puoi usare il metodo `isModified()`.

Se leggi i commenti ai tuoi articoli, potresti cambiare idea riguardo il pubblicare contenuti su internet. E se non apprezzi l'ironia dei lettori, puoi cancellare semplicemente i commenti con il metodo `delete()`, come mostrato nel Listato 8-11.

Listato 8-11 - Cancellare righe dal database con il metodo `delete()` dell'oggetto

    [php]
    foreach ($article->getComments() as $comment)
    {
      $comment->delete();
    }

>**TIP**
>Anche dopo aver chiamato il metodo `delete()`, un oggetto rimane disponibile fino alla fine dell'esecuzione. Per determinare se un oggetto è stato cancellato dal database, chiama il metodo `isDeleted()`.

### Recuperare record usando la chiave primaria

Se conosci la chiave primaria di un particolare record, usa il metodo `retrieveByPk()` della classe peer per ottenere il relativo oggetto.

    [php]
    $article = ArticlePeer::retrieveByPk(7);

Il file `schema.yml` definisce il campo `id` come la chiave primaria della tabella `blog_article`, quindi questo comando ritornerà un articolo che ha `id` uguale a 7. Dato che hai utilizzato la chiave primaria, sai che soltanto un record sarà ritornato; la variabile `$article` contiene un oggetto della classe `Article`.

In alcuni casi, una chiave primaria può consistere in più di una colonna. In questi casi, il metodo `retrieveByPk()` accetta parametri multipli, uno per ogni colonna della chiave primaria.

Puoi inoltre selezionare multipli oggetti basandoti sulla loro chiave primaria, chiamato in metodo generato `retrieveByPks()`, che ha come parametri un array di chiavi primarie.

### Recuperare record con Criteria

Quando vuoi recuperare più di un record, dovrai utilizzare il metodo `doSelect()` della classe peer corrispondente agli oggetti che vuoi ottenere. Per esempio, per recuperare oggetti della classe `Article`, chiama `ArticlePeer::doSelect()`.

Il primo parametro del metodo `doSelect()` è un oggetto della classe `Criteria`, che è una semplice classe per la costruzione delle query, senza l'utilizzo di SQL per mantenere l'astrazione dal database.

Un oggetto `Criteria` vuoto ritorna tutti gli oggetti della classe. Per esempio, il codice mostrato nel Listato 8-12 ritorna tutti gli articoli.

Listato 8-12 - Recuperare record usando Criteria e `doSelect()`--Criteria vuoto

    [php]
    $c = new Criteria();
    $articles = ArticlePeer::doSelect($c);

    // Risulterà nella seguente query SQL
    SELECT blog_article.ID, blog_article.TITLE, blog_article.CONTENT,
           blog_article.CREATED_AT
    FROM   blog_article;

>**SIDEBAR**
>Idratazione
>
>La chiamata a `::doSelect()` è a dir la verità molto più potente di una semplice query SQL. Per prima cosa, il codice SQL generato è ottimizzato per il DBMS scelto. Secondo, su ogni valore passato a `Criteria` viene effettuato l'escape prima di venir intergrato nel codice SQL, che previene rischi di SQL injection. Terzo, questo metodo ritorna un array di oggetti piuttosto che una risorsa PHP. L'ORM crea automaticamente gli oggetti basandosi sulla risorsa ritornata dal database. Questo processo è chiamato idratazione.

Per una selezione più complessa, avrai bisogno degli equivalenti di WHERE, ORDER BY, GROUP BY e altre istruzioni SQL. L'oggetto `Criteria` ha metodi e parametri per tutte queste condizioni. Per esempio, per ottenere tutti i commenti scritti da Steve, ordinati per data, costruisci un `Criteria` come mostrato nel Listato 8-13.

Listato 8-13 - Recuperare record usando Criteria e `doSelect()`--Criteria con condizioni

    [php]
    $c = new Criteria();
    $c->add(CommentPeer::AUTHOR, 'Steve');
    $c->addAscendingOrderByColumn(CommentPeer::CREATED_AT);
    $comments = CommentPeer::doSelect($c);

    // Risulterà nella seguente query SQL
    SELECT blog_comment.ARTICLE_ID, blog_comment.AUTHOR, blog_comment.CONTENT,
           blog_comment.CREATED_AT
    FROM   blog_comment
    WHERE  blog_comment.author = 'Steve'
    ORDER BY blog_comment.CREATED_AT ASC;

Le costanti di classe passate come parametri dei metodi `add()` si riferiscono ai nomi delle propietà. Sono chiamate con la versione maiuscola dei nomi delle colonne. Per esempio, per la colonna `content` della tabella `blog_article`, usa la costante `ArticlePeer::CONTENT`.

>**NOTE**
>Perché usare `CommentPeer::AUTHOR` al posto di `blog_comment.AUTHOR`, che è quello che sarà utilizzato nel codice SQL in ogni caso? Supponi di dover cambiare il nome della colonna da `author` a `contributor` nel database. Se tu avessi usato `blog_comment.AUTHOR`, dovresti cambiarlo ad ogni chiamata del metodo. Invece, usando `CommentPeer::AUTHOR`, devi soltanto cambiare il nome della colonna nel file `schema.yml`, impostare il valore `phpName` ad `AUTHOR` e ricostruire il modello.

La Tabella 8-1 confronta la sintassi SQL con quella della classe `Criteria`.

Tabella 8-1 - Sintassi SQL e Criteria

SQL                                                          | Criteria
------------------------------------------------------------ | -----------------------------------------------
`WHERE column = value`                                       | `->add(column, value);`
`WHERE column <> value`                                      | `->add(column, value, Criteria::NOT_EQUAL);`
**Altri operatori di confronto**                             | 
`> , <`                                                      | `Criteria::GREATER_THAN, Criteria::LESS_THAN`
`>=, <=`                                                     | `Criteria::GREATER_EQUAL, Criteria::LESS_EQUAL`
`IS NULL, IS NOT NULL`                                       | `Criteria::ISNULL, Criteria::ISNOTNULL`
`LIKE, ILIKE`                                                | `Criteria::LIKE, Criteria::ILIKE`
`IN, NOT IN`                                                 | `Criteria::IN, Criteria::NOT_IN`
**Altre keywords SQL**                                       |
`ORDER BY column ASC`                                        | `->addAscendingOrderByColumn(column);`
`ORDER BY column DESC`                                       | `->addDescendingOrderByColumn(column);`
`LIMIT limit`                                                | `->setLimit(limit)`
`OFFSET offset`                                              | `->setOffset(offset) `
`FROM table1, table2 WHERE table1.col1 = table2.col2`        | `->addJoin(col1, col2)`
`FROM table1 LEFT JOIN table2 ON table1.col1 = table2.col2`  | `->addJoin(col1, col2, Criteria::LEFT_JOIN)`
`FROM table1 RIGHT JOIN table2 ON table1.col1 = table2.col2` | `->addJoin(col1, col2, Criteria::RIGHT_JOIN)`

>**TIP**
>Il modo migliore per scoprire e capire quali metodi sono disponibili nelle classi generate è guardare ai file `Base` nella cartella `lib/model/om/` dopo la generazione. I nomi dei metodi sono abbastanza espliciti, ma se hai bisogno di commenti in essi imposta il parametro `propel.builder.addComments` a `true` nel file `config/propel.ini` e ricostruisci il modello.

Il Listato 8-14 mostra un altro esempio di `Criteria` con condizioni multiple. Recupera tutti i commenti di Steve sugli articoli contenenti la parola "enjoy", ordinati per data.

Listato 8-14 - Un altro esempio di recuperato record con Criteria e `doSelect()`--Criteria con condizioni

    [php]
    $c = new Criteria();
    $c->add(CommentPeer::AUTHOR, 'Steve');
    $c->addJoin(CommentPeer::ARTICLE_ID, ArticlePeer::ID);
    $c->add(ArticlePeer::CONTENT, '%enjoy%', Criteria::LIKE);
    $c->addAscendingOrderByColumn(CommentPeer::CREATED_AT);
    $comments = CommentPeer::doSelect($c);

    // Risulterà nella seguente query SQL
    SELECT blog_comment.ID, blog_comment.ARTICLE_ID, blog_comment.AUTHOR,
           blog_comment.CONTENT, blog_comment.CREATED_AT
    FROM   blog_comment, blog_article
    WHERE  blog_comment.AUTHOR = 'Steve'
           AND blog_article.CONTENT LIKE '%enjoy%'
           AND blog_comment.ARTICLE_ID = blog_article.ID
    ORDER BY blog_comment.CREATED_AT ASC

Come SQL è un linguaggio semplice che consente di gestire interrogazioni molto complesse, l'oggetto Criteria può gestire condizioni con ogni livello di complessità. Ma dato che molti sviluppatori pensano in SQL prima di tradurre una condizione in logica object-oriented, l'oggetto `Criteria` può essere difficile da comprendere all'inizio. Il miglior modo di capirlo è imparare dagli esempi e applicazioni esistenti. Il sito di symfony, per esempio, è pieno di esempi di utilizzo di `Criteria` che ti aiuteranno in molte situazioni.

In aggiunta al metodo `doSelect()`, ogni classe peer ha un metodo `doCount()`, che semplicemente ritorna il numero di record che soddisfano i requisiti passati come parametri e ritorna un numero intero. Dato che nessun oggetto viene ritornato, il processo di idratazione non viene eseguito in questo caso, perciò il metodo `doCount()` è più veloce di `doSelect()`.

La classi peer forniscono inoltre i metodi `doDelete()`, `doInsert()` e `doUpdate()`, che ricevono un oggetto `Criteria` come parametro. Questi metodi consentono di eseguire interrogazioni di tipo `DELETE`, `INSERT` e `UPDATE` sul database. Dai un'occhiata alle classi peer generate nel tuo modello per ulteriori dettagli su questi metodi di Propel.

Infine, se vuoi soltanto il primo oggetto, sostituisci `doSelect()` con `doSelectOne()`. Questo potrebbe essere il caso in cui sai che `Criteria` produrrà un risultato soltanto, con il vantaggio che il metodo ritornerà direttamente un oggetto piuttosto di un array.

>**TIP**
>Quando una query ritorna un gran numero di risultati, potresti volerne mostrare soltanto un sottoinsieme. Symfony fornisce una classe per la suddivisione in pagine chiamata `sfPropelPager`, che automatizza la paginazione dei risultati.

### Using Raw SQL Queries

Sometimes, you don't want to retrieve objects, but want to get only synthetic results calculated by the database. For instance, to get the latest creation date of all articles, it doesn't make sense to retrieve all the articles and to loop on the array. You will prefer to ask the database to return only the result, because it will skip the object hydrating process.

On the other hand, you don't want to call the PHP commands for database management directly, because then you would lose the benefit of database abstraction. This means that you need to bypass the ORM (Propel) but not the database abstraction (PDO).

Querying the database with PHP Data Objects requires that you do the following:

  1. Get a database connection.
  2. Build a query string.
  3. Create a statement out of it.
  4. Iterate on the result set that results from the statement execution.

If this looks like gibberish to you, the code in Listing 8-15 will probably be more explicit.

Listing 8-15 - Custom SQL Query with PDO

    [php]
    $connection = Propel::getConnection();
    $query = 'SELECT MAX(?) AS max FROM ?';
    $statement = $connection->prepare($query);
    $statement->bindValue(1, ArticlePeer::CREATED_AT);
    $statement->bindValue(2, ArticlePeer::TABLE_NAME);
    $statement->execute();
    $resultset = $statement->fetch(PDO::FETCH_OBJ);
    $max = $resultset->max;

Just like Propel selections, PDO queries are tricky when you first start using them. Once again, examples from existing applications and tutorials will show you the right way.

>**CAUTION**
>If you are tempted to bypass this process and access the database directly, you risk losing the security and abstraction provided by Propel. Doing it the Propel way is longer, but it forces you to use good practices that guarantee the performance, portability, and security of your application. This is especially true for queries that contain parameters coming from a untrusted source (such as an Internet user). Propel does all the necessary escaping and secures your database. Accessing the database directly puts you at risk of SQL-injection attacks.

### Using Special Date Columns

Usually, when a table has a column called `created_at`, it is used to store a timestamp of the date when the record was created. The same applies to updated_at columns, which are to be updated each time the record itself is updated, to the value of the current time.

The good news is that symfony will recognize the names of these columns and handle their updates for you. You don't need to manually set the `created_at` and `updated_at` columns; they will automatically be updated, as shown in Listing 8-16. The same applies for columns named `created_on` and `updated_on`.

Listing 8-16 - `created_at` and `updated_at` Columns Are Dealt with Automatically

    [php]
    $comment = new Comment();
    $comment->setAuthor('Steve');
    $comment->save();

    // Show the creation date
    echo $comment->getCreatedAt();
      => [date of the database INSERT operation]

Additionally, the getters for date columns accept a date format as an argument:

    [php]
    echo $comment->getCreatedAt('Y-m-d');

>**SIDEBAR**
>Refactoring to the Data layer
>
>When developing a symfony project, you often start by writing the domain logic code in the actions. But the database queries and model manipulation should not be stored in the controller layer. So all the logic related to the data should be moved to the model layer. Whenever you need to do the same request in more than one place in your actions, think about transferring the related code to the model. It helps to keep the actions short and readable.
>
>For example, imagine the code needed in a blog to retrieve the ten most popular articles for a given tag (passed as request parameter). This code should not be in an action, but in the model. In fact, if you need to display this list in a template, the action should simply look like this:
>
>     [php]
>     public function executeShowPopularArticlesForTag($request)
>     {
>       $tag = TagPeer::retrieveByName($request->getParameter('tag'));
>       $this->forward404Unless($tag);
>       $this->articles = $tag->getPopularArticles(10);
>     }
>
>The action creates an object of class `Tag` from the request parameter. Then all the code needed to query the database is located in a `getPopularArticles()` method of this class. It makes the action more readable, and the model code can easily be reused in another action.
>
>Moving code to a more appropriate location is one of the techniques of refactoring. If you do it often, your code will be easy to maintain and to understand by other developers. A good rule of thumb about when to do refactoring to the data layer is that the code of an action should rarely contain more than ten lines of PHP code.

Database Connections
--------------------

The data model is independent from the database used, but you will definitely use a database. The minimum information required by symfony to send requests to the project database is the name, the credentials, and the type of database.These connection settings can be configured by passing a data source name (DSN) to the `configure:database` task:

    $ php symfony configure:database "mysql:host=localhost;dbname=blog" root mYsEcret

The connection settings are environment-dependent. You can define distinct settings for the `prod`, `dev`, and `test` environments, or any other environment in your application by using the `env` option:

    $ php symfony configure:database --env=dev "mysql:host=localhost;dbname=blog_dev" root mYsEcret

This configuration can also be overridden per application. For instance, you can use this approach to have different security policies for a front-end and a back-end application, and define several database users with different privileges in your database to handle this:

    $ php symfony configure:database --app=frontend "mysql:host=localhost;dbname=blog" root mYsEcret

For each environment, you can define many connections. Each connection refers to a schema being labeled with the same name. The default connection name used is `propel` and it refers to the `propel` schema in Listing 8-3. The `name` option allows you to create another connection:

    $ php symfony configure:database --name=main "mysql:host=localhost;dbname=example" root mYsEcret

You can also enter these connection settings manually in the `databases.yml` file located in the `config/` directory. Listing 8-17 shows an example of such a file and Listing 8-18 shows the same example with the extended notation.

Listing 8-17 - Shorthand Database Connection Settings

    [yml]
    all:
      propel:
        class:          sfPropelDatabase
        param:
          dsn:          mysql://login:passwd@localhost/blog

Listing 8-18 - Sample Database Connection Settings, in `myproject/config/databases.yml`

    [yml]
    prod:
      propel:
        param:
          hostspec:           mydataserver
          username:           myusername
          password:           xxxxxxxxxx

    all:
      propel:
        class:                sfPropelDatabase
        param:
          phptype:            mysql     # Database vendor
          hostspec:           localhost
          database:           blog
          username:           login
          password:           passwd
          port:               80
          encoding:           utf8      # Default charset for table creation
          persistent:         true      # Use persistent connections

The permitted values of the `phptype` parameter are the ones of the database systems supported by PDO:

  * `mysql`
  * `mssql`
  * `pgsql`
  * `sqlite`
  * `oracle`

`hostspec`, `database`, `username`, and `password` are the usual database connection settings.

To override the configuration per application, you need to edit an application-specific file, such as `apps/frontend/config/databases.yml`.

If you use a SQLite database, the `hostspec` parameter must be set to the path of the database file. For instance, if you keep your blog database in `data/blog.db`, the `databases.yml` file will look like Listing 8-19.

Listing 8-19 - Database Connection Settings for SQLite Use a File Path As Host

    [yml]
    all:
      propel:
        class:      sfPropelDatabase
        param:
          phptype:  sqlite
          database: %SF_DATA_DIR%/blog.db

Extending the Model
-------------------

The generated model methods are great but often not sufficient. As soon as you implement your own business logic, you need to extend it, either by adding new methods or by overriding existing ones.

### Adding New Methods

You can add new methods to the empty model classes generated in the `lib/model/` directory. Use `$this` to call methods of the current object, and use `self::` to call static methods of the current class. Remember that the custom classes inherit methods from the `Base` classes located in the `lib/model/om/` directory.

For instance, for the `Article` object generated based on Listing 8-3, you can add a magic `__toString()` method so that echoing an object of class `Article` displays its title, as shown in Listing 8-20.

Listing 8-20 - Customizing the Model, in `lib/model/Article.php`

    [php]
    class Article extends BaseArticle
    {
      public function __toString()
      {
        return $this->getTitle();  // getTitle() is inherited from BaseArticle
      }
    }

You can also extend the peer classes--for instance, to add a method to retrieve all articles ordered by creation date, as shown in Listing 8-21.

Listing 8-21 - Customizing the Model, in `lib/model/ArticlePeer.php`

    [php]
    class ArticlePeer extends BaseArticlePeer
    {
      public static function getAllOrderedByDate()
      {
        $c = new Criteria();
        $c->addAscendingOrderByColumn(self::CREATED_AT);

        return self::doSelect($c);
      }
    }

The new methods are available in the same way as the generated ones, as shown in Listing 8-22.

Listing 8-22 - Using Custom Model Methods Is Like Using the Generated Methods

    [php]
    foreach (ArticlePeer::getAllOrderedByDate() as $article)
    {
      echo $article;      // Will call the magic __toString() method
    }

### Overriding Existing Methods

If some of the generated methods in the `Base` classes don't fit your requirements, you can still override them in the custom classes. Just make sure that you use the same method signature (that is, the same number of arguments).

For instance, the `$article->getComments()` method returns an array of `Comment` objects, in no particular order. If you want to have the results ordered by creation date, with the latest comment coming first, then override the `getComments()` method, as shown in Listing 8-23. Be aware that the original `getComments()` method (found in `lib/model/om/BaseArticle.php`) expects a criteria value and a connection value as parameters, so your function must do the same.

Listing 8-23 - Overriding Existing Model Methods, in `lib/model/Article.php`

    [php]
    public function getComments($criteria = null, $con = null)
    {
      if (is_null($criteria))
      {
        $criteria = new Criteria();
      }
      else
      {
        // Objects are passed by reference in PHP5, so to avoid modifying the original, you must clone it
        $criteria = clone $criteria;
      }
      $criteria->addDescendingOrderByColumn(CommentPeer::CREATED_AT);

      return parent::getComments($criteria, $con);
    }

The custom method eventually calls the one of the parent Base class, and that's good practice. However, you can completely bypass it and return the result you want.

### Using Model Behaviors

Some model modifications are generic and can be reused. For instance, methods to make a model object sortable and an optimistic lock to prevent conflicts between concurrent object saving are generic extensions that can be added to many classes.

Symfony packages these extensions into behaviors. Behaviors are external classes that provide additional methods to model classes. The model classes already contain hooks, and symfony knows how to extend them.

To enable behaviors in your model classes, you must modify one setting in the `config/propel.ini` file:

    propel.builder.AddBehaviors = true     // Default value is false

There is no behavior bundled by default in symfony, but they can be installed via plug-ins. Once a behavior plug-in is installed, you can assign the behavior to a class with a single line. For instance, if you install the `sfPropelParanoidBehaviorPlugin` in your application, you can extend an `Article` class with this behavior by adding the following at the end of the `Article.class.php`:

    [php]
    sfPropelBehavior::add('Article', array(
      'paranoid' => array('column' => 'deleted_at')
    ));

After rebuilding the model, deleted `Article` objects will remain in the database, invisible to the queries using the ORM, unless you temporarily disable the behavior with `sfPropelParanoidBehavior::disable()`.

Alternatively, you can also declare behaviors directly in the `schema.yml`, by listing them under the `_behaviors` key (see Listing 8-34 below).

Check the list of symfony plug-ins on the official [repository](http://www.symfony-project.org/plugins/) to find behaviors. Each has its own documentation and installation guide.

Extended Schema Syntax
----------------------

A `schema.yml` file can be simple, as shown in Listing 8-3. But relational models are often complex. That's why the schema has an extensive syntax able to handle almost every case.

### Attributes

Connections and tables can have specific attributes, as shown in Listing 8-24. They are set under an `_attributes` key.

Listing 8-24 - Attributes for Connections and Tables

    [yml]
    propel:
      _attributes:   { noXsd: false, defaultIdMethod: none, package: lib.model }
      blog_article:
        _attributes: { phpName: Article }

You may want your schema to be validated before code generation takes place. To do that, deactivate the `noXSD` attribute for the connection. The connection also supports the `defaultIdMethod` attribute. If none is provided, then the database's native method of generating IDs will be used--for example, `autoincrement` for MySQL, or `sequences` for PostgreSQL. The other possible value is `none`.

The `package` attribute is like a namespace; it determines the path where the generated classes are stored. It defaults to `lib/model/`, but you can change it to organize your model in subpackages. For instance, if you don't want to mix the core business classes and the classes defining a database-stored statistics engine in the same directory, then define two schemas with `lib.model.business` and `lib.model.stats` packages.

You already saw the `phpName` table attribute, used to set the name of the generated class mapping the table.

Tables that contain localized content (that is, several versions of the content, in a related table, for internationalization) also take two additional attributes (see Chapter 13 for details), as shown in Listing 8-25.

Listing 8-25 - Attributes for i18n Tables

    [yml]
    propel:
      blog_article:
        _attributes: { isI18N: true, i18nTable: db_group_i18n }

>**SIDEBAR**
>Dealing with multiple Schemas
>
>You can have more than one schema per application. Symfony will take into account every file ending with `schema.yml` or `schema.xml` in the `config/` folder. If your application has many tables, or if some tables don't share the same connection, you will find this approach very useful.
>
>Consider these two schemas:
>
>     [yml]
>     // In config/business-schema.yml
>     propel:
>       blog_article:
>         _attributes: { phpName: Article }
>         id:
>         title: varchar(50)
>
>     // In config/stats-schema.yml
>     propel:
>       stats_hit:
>         _attributes: { phpName: Hit }
>         id:
>         resource: varchar(100)
>         created_at:
>
>
>Both schemas share the same connection (`propel`), and the `Article` and `Hit` classes will be generated under the same `lib/model/` directory. Everything happens as if you had written only one schema.
>
>You can also have different schemas use different connections (for instance, `propel` and `propel_bis`, to be defined in `databases.yml`) and organize the generated classes in subdirectories:
>
>
>     [yml]
>     // In config/business-schema.yml
>     propel:
>       blog_article:
>         _attributes: { phpName: Article, package: lib.model.business }
>         id:
>         title: varchar(50)
>
>     // In config/stats-schema.yml
>     propel_bis:
>       stats_hit:
>         _attributes: { phpName: Hit, package: lib.model.stat }
>         id:
>         resource: varchar(100)
>         created_at:
>
>
>Many applications use more than one schema. In particular, some plug-ins have their own schema and package to avoid messing with your own classes (see Chapter 17 for details).

### Column Details

The basic syntax gives you two choices: let symfony deduce the column characteristics from its name (by giving an empty value) or define the type with one of the type keywords. Listing 8-26 demonstrates these choices.

Listing 8-26 - Basic Column Attributes

    [yml]
    propel:
      blog_article:
        id:    ~            # Let symfony do the work
        title: varchar(50)  # Specify the type yourself

But you can define much more for a column. If you do, you will need to define column settings as an associative array, as shown in Listing 8-27.

Listing 8-27 - Complex Column Attributes

    [yml]
    propel:
      blog_article:
        id:       { type: integer, required: true, primaryKey: true, autoIncrement: true }
        name:     { type: varchar(50), default: foobar, index: true }
        group_id: { type: integer, foreignTable: db_group, foreignReference: id, onDelete: cascade }

The column parameters are as follows:

  * `type`: Column type. The choices are `boolean`, `tinyint`, `smallint`, `integer`, `bigint`, `double`, `float`, `real`, `decimal`, `char`, `varchar(size)`, `longvarchar`, `date`, `time`, `timestamp`, `bu_date`, `bu_timestamp`, `blob`, and `clob`.
  * `required`: Boolean. Set it to `true` if you want the column to be required.
  * `size`: The size or length of the field for types that support it
  * `scale`: Number of decimal places for use with decimal data type (size must also be specified)
  * `default`: Default value.
  * `primaryKey`: Boolean. Set it to `true` for primary keys.
  * `autoIncrement`: Boolean. Set it to `true` for columns of type `integer` that need to take an auto-incremented value.
  * `sequence`: Sequence name for databases using sequences for `autoIncrement` columns (for example, PostgreSQL and Oracle).
  * `index`: Boolean. Set it to `true` if you want a simple index or to `unique` if you want a unique index to be created on the column.
  * `foreignTable`: A table name, used to create a foreign key to another table.
  * `foreignReference`: The name of the related column if a foreign key is defined via `foreignTable`.
  * `onDelete`: Determines the action to trigger when a record in a related table is deleted. When set to `setnull`, the foreign key column is set to `null`. When set to `cascade`, the record is deleted. If the database engine doesn't support the set behavior, the ORM emulates it. This is relevant only for columns bearing a `foreignTable` and a `foreignReference`.
  * `isCulture`: Boolean. Set it to `true` for culture columns in localized content tables (see Chapter 13).

### Foreign Keys

As an alternative to the `foreignTable` and `foreignReference` column attributes, you can add foreign keys under the `_foreignKeys:` key in a table. The schema in Listing 8-28 will create a foreign key on the `user_id` column, matching the `id` column in the `blog_user` table.

Listing 8-28 - Foreign Key Alternative Syntax

    [yml]
    propel:
      blog_article:
        id:      ~
        title:   varchar(50)
        user_id: { type: integer }
        _foreignKeys:
          -
            foreignTable: blog_user
            onDelete:     cascade
            references:
              - { local: user_id, foreign: id }

The alternative syntax is useful for multiple-reference foreign keys and to give foreign keys a name, as shown in Listing 8-29.

Listing 8-29 - Foreign Key Alternative Syntax Applied to Multiple Reference Foreign Key

        _foreignKeys:
          my_foreign_key:
            foreignTable:  db_user
            onDelete:      cascade
            references:
              - { local: user_id, foreign: id }
              - { local: post_id, foreign: id }

### Indexes

As an alternative to the `index` column attribute, you can add indexes under the `_indexes:` key in a table. If you want to define unique indexes, you must use the `_uniques:` header instead. For columns that require a size, because they are text columns, the size of the index is specified the same way as the length of the column using parentheses. Listing 8-30 shows the alternative syntax for indexes.

Listing 8-30 - Indexes and Unique Indexes Alternative Syntax

    [yml]
    propel:
      blog_article:
        id:               ~
        title:            varchar(50)
        created_at:
        _indexes:
          my_index:       [title(10), user_id]
        _uniques:
          my_other_index: [created_at]

The alternative syntax is useful only for indexes built on more than one column.

### Empty Columns

When meeting a column with no value, symfony will do some magic and add a value of its own. See Listing 8-31 for the details added to empty columns.

Listing 8-31 - Column Details Deduced from the Column Name

    // Empty columns named id are considered primary keys
    id:         { type: integer, required: true, primaryKey: true, autoIncrement: true }

    // Empty columns named XXX_id are considered foreign keys
    foobar_id:  { type: integer, foreignTable: db_foobar, foreignReference: id }

    // Empty columns named created_at, updated at, created_on and updated_on
    // are considered dates and automatically take the timestamp type
    created_at: { type: timestamp }
    updated_at: { type: timestamp }

For foreign keys, symfony will look for a table having the same `phpName` as the beginning of the column name, and if one is found, it will take this table name as the `foreignTable`.

### I18n Tables

Symfony supports content internationalization in related tables. This means that when you have content subject to internationalization, it is stored in two separate tables: one with the invariable columns and another with the internationalized columns.

In a `schema.yml` file, all that is implied when you name a table `foobar_i18n`. For instance, the schema shown in Listing 8-32 will be automatically completed with columns and table attributes to make the internationalized content mechanism work. Internally, symfony will understand it as if it were written like Listing 8-33. Chapter 13 will tell you more about i18n.

Listing 8-32 - Implied i18n Mechanism

    [yml]
    propel:
      db_group:
        id:          ~
        created_at:  ~

      db_group_i18n:
        name:        varchar(50)

Listing 8-33 - Explicit i18n Mechanism

    [yml]
    propel:
      db_group:
        _attributes: { isI18N: true, i18nTable: db_group_i18n }
        id:         ~
        created_at: ~

      db_group_i18n:
        id:       { type: integer, required: true, primaryKey: true,foreignTable: db_group, foreignReference: id, onDelete: cascade }
        culture:  { isCulture: true, type: varchar(7), required: true,primaryKey: true }
        name:     varchar(50)

### Behaviors

Behaviors are model modifiers provided by plug-ins that add new capabilities to your Propel classes. Chapter 17 explains more about behaviors. You can define behaviors right in the schema, by listing them for each table, together with their parameters, under the `_behaviors` key. Listing 8-34 gives an example by extending the `BlogArticle` class with the `paranoid` behavior.

Listing 8-34 - Behaviors Declaration

    [yml]
    propel:
      blog_article:
        title:          varchar(50)
        _behaviors:
          paranoid:     { column: deleted_at }

### Beyond the schema.yml: The schema.xml

As a matter of fact, the `schema.yml` format is internal to symfony. When you call a propel- command, symfony actually translates this file into a `generated-schema.xml` file, which is the type of file expected by Propel to actually perform tasks on the model.

The `schema.xml` file contains the same information as its YAML equivalent. For example, Listing 8-3 is converted to the XML file shown in Listing 8-35.

Listing 8-35 - Sample `schema.xml`, Corresponding to Listing 8-3

    [xml]
    <?xml version="1.0" encoding="UTF-8"?>
     <database name="propel" defaultIdMethod="native" noXsd="true" package="lib.model">
        <table name="blog_article" phpName="Article">
          <column name="id" type="integer" required="true" primaryKey="true"autoIncrement="true" />
          <column name="title" type="varchar" size="255" />
          <column name="content" type="longvarchar" />
          <column name="created_at" type="timestamp" />
        </table>
        <table name="blog_comment" phpName="Comment">
          <column name="id" type="integer" required="true" primaryKey="true"autoIncrement="true" />
          <column name="article_id" type="integer" />
          <foreign-key foreignTable="blog_article">
            <reference local="article_id" foreign="id"/>
          </foreign-key>
          <column name="author" type="varchar" size="255" />
          <column name="content" type="longvarchar" />
          <column name="created_at" type="timestamp" />
        </table>
     </database>

The description of the `schema.xml` format can be found in the documentation and the "Getting Started" sections of the Propel project [website](http://propel.phpdb.org/docs/user_guide/chapters/appendices/AppendixB-SchemaReference.html).

The YAML format was designed to keep the schemas simple to read and write, but the trade-off is that the most complex schemas can't be described with a `schema.yml` file. On the other hand, the XML format allows for full schema description, whatever its complexity, and includes database vendor-specific settings, table inheritance, and so on.

Symfony actually understands schemas written in XML format. So if your schema is too complex for the YAML syntax, if you have an existing XML schema, or if you are already familiar with the Propel XML syntax, you don't have to switch to the symfony YAML syntax. Place your `schema.xml` in the project `config/` directory, build the model, and there you go.

>**SIDEBAR**
>Propel in symfony
>
>All the details given in this chapter are not specific to symfony, but rather to Propel. Propel is the preferred object/relational abstraction layer for symfony, but you can choose an alternative one. However, symfony works more seamlessly with Propel, for the following reasons:
>
>All the object data model classes and the `Criteria` class are autoloading classes. As soon as you use them, symfony will include the right files, and you don't need to manually add the file inclusion statements. In symfony, Propel doesn't need to be launched nor initialized. When an object uses Propel, the library initiates by itself. Some symfony helpers use Propel objects as parameters to achieve high-level tasks (such as pagination or filtering). Propel objects allow rapid prototyping and generation of a backend for your application (Chapter 14 provides more details). The schema is faster to write through the `schema.yml` file.
>
>And, as Propel is independent of the database used, so is symfony.

Don't Create the Model Twice
----------------------------

The trade-off of using an ORM is that you must define the data structure twice: once for the database, and once for the object model. Fortunately, symfony offers command-line tools to generate one based on the other, so you can avoid duplicate work.

### Building a SQL Database Structure Based on an Existing Schema

If you start your application by writing the `schema.yml` file, symfony can generate a SQL query that creates the tables directly from the YAML data model. To use the query, go to your root project directory and type this:

    $ php symfony propel:build-sql

A `lib.model.schema.sql` file will be created in `myproject/data/sql/`. Note that the generated SQL code will be optimized for the database system defined in the `phptype` parameter of the `propel.ini` file.

You can use the `schema.sql` file directly to build the tables. For instance, in MySQL, type this:

    $ mysqladmin -u root -p create blog
    $ mysql -u root -p blog < data/sql/lib.model.schema.sql

The generated SQL is also helpful to rebuild the database in another environment, or to change to another DBMS. If the connection settings are properly defined in your `propel.ini`, you can even use the `php symfony propel:insert-sql` command to do this automatically.

>**TIP**
>The command line also offers a task to populate your database with data based on a text file. See Chapter 16 for more information about the `propel:data-load` task and the YAML fixture files.

### Generating a YAML Data Model from an Existing Database

Symfony can use Propel to generate a `schema.yml` file from an existing database, thanks to introspection (the capability of databases to determine the structure of the tables on which they are operating). This can be particularly useful when you do reverse-engineering, or if you prefer working on the database before working on the object model.

In order to do this, you need to make sure that the project `databases.yml` file points to the correct database and contains all connection settings, and then call the `propel:build-schema` command:

    $ php symfony propel:build-schema

A brand-new `schema.yml` file built from your database structure is generated in the `config/` directory. You can build your model based on this schema.

The schema-generation command is quite powerful and can add a lot of database-dependent information to your schema. As the YAML format doesn't handle this kind of vendor information, you need to generate an XML schema to take advantage of it. You can do this simply by adding an `xml` argument to the `build-schema` task:

    $ php symfony propel:build-schema --xml

Instead of generating a `schema.yml` file, this will create a `schema.xml` file fully compatible with Propel, containing all the vendor information. But be aware that generated XML schemas tend to be quite verbose and difficult to read.

>**SIDEBAR**
>The `propel.ini` Configuration
>
>This file contains other settings used to configure the Propel generator to make generated model classes compatible with symfony. Most settings are internal and of no interest to the user, apart from a few:
>
>      // Base classes are autoloaded in symfony
>      // Set this to true to use include_once statements instead
>      // (Small negative impact on performance)
>      propel.builder.addIncludes = false
>
>      // Generated classes are not commented by default
>      // Set this to true to add comments to Base classes
>      // (Small negative impact on performance)
>      propel.builder.addComments = false
>
>      // Behaviors are not handled by default
>      // Set this to true to be able to handle them
>      propel.builder.AddBehaviors = false
>
>
>After you make a modification to the `propel.ini` settings, don't forget to rebuild the model so the changes will take effect.

Summary
-------

Symfony uses Propel as the ORM and PHP Data Objects as the database abstraction layer. It means that you must first describe the relational schema of your database in YAML before generating the object model classes. Then, at runtime, use the methods of the object and peer classes to retrieve information about a record or a set of records. You can override them and extend the model easily by adding methods to the custom classes. The connection settings are defined in a `databases.yml` file, which can support more than one connection. And the command line contains special tasks to avoid duplicate structure definition.

The model layer is the most complex of the symfony framework. One reason for this complexity is that data manipulation is an intricate matter. The related security issues are crucial for a website and should not be ignored. Another reason is that symfony is more suited for middle- to large-scale applications in an enterprise context. In such applications, the automations provided by the symfony model really represent a gain of time, worth the investment in learning its internals.

So don't hesitate to spend some time testing the model objects and methods to fully understand them. The solidity and scalability of your applications will be a great reward.
