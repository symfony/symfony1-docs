Appendice A - All'interno del livello del modello (Propel)
==========================================================

Gran parte della trattazione finora è stata dedicata alla costruzione di pagine e all'elaborazione delle richieste e delle risposte. Ma la business logic di una applicazione web si basa principalmente sul suo modello di dati. Il componente predefinito di symfony per il modello, è basato su un livello che mappa oggetti e relazioni. Symfony è distribuito con i due più popolari ORM per PHP: [Propel](http://www.propelorm.org/) e [Doctrine](http://www.doctrine-project.org/). In un'applicazione symfony, si accede ai dati memorizzati in un database e li si modifica, attraverso gli oggetti; non è necessario riferirsi direttamente al database. Quest'ultimo mantiene un elevato livello di astrazione e portabilità.

Questo capitolo spiega come creare un modello di dati a oggetti e come accedere ai dati e modificarli con Propel. Viene anche trattata l'integrazione di Propel con symfony.

Perché usare un ORM e un livello per l'astrazione?
--------------------------------------------------

I database sono relazionali. PHP 5 e symfony sono orientati agli oggetti. Per poter accedere nel modo più efficace al database in un contesto orientato agli oggetti, è indispensabile una interfaccia per tradurre la logica degli oggetti nella logica relazionale. Come spiegato nel capitolo 1, questa interfaccia è chiamata Object-Relational Mapping (ORM), ed è costituita di oggetti che forniscono l'accesso ai dati e mantengono le business rules all'interno di se stessi.

Il vantaggio principale di un ORM è la riusabilità, che consente ai metodi di un oggetto di tipo dato, di essere chiamato da varie parti dell'applicazione, anche da diverse applicazioni. Il livello ORM incapsula anche la logica dei dati, ad esempio, il calcolo del punteggio degli utenti di un forum basato su quanti contributi sono stati fatti e quanto sono popolari. Quando una pagina deve visualizzare un tale punteggio degli utenti, basta chiamare semplicemente un metodo nel modello dei dati, senza preoccuparsi dei dettagli del calcolo. Se in seguito bisogna modificare il calcolo, sarà sufficiente modificare il metodo nel modello, lasciando il resto dell'applicazione invariata.

Usare oggetti al posto di record e classi al posto di tabelle, ha un altro vantaggio: la possibilità di aggiungere agli oggetti nuove funzioni di accesso che non necessariamente corrispondono a una colonna in una tabella. Per esempio, se si ha una tabella chiamata `cliente` con due campi chiamati `nome` e `cognome`, si potrebbe volere la possibilità di chiedere solo il `Nome`. In un mondo orientato agli oggetti, basta aggiungere un nuovo metodo di accesso alla classe `Cliente`, come si può vedere nel listato 8-1. Dal punto di vista dell'applicativo, non vi è alcuna differenza tra `Nome`, `Cognome` e `NomePersona`: sono tutti attributi della classe `Cliente`. Solo la classe stessa può determinare quali attributi corrispondono a una colonna del database.

Listato 8-1 - Il metodo di accesso maschera la struttura della tabella in una classe del modello

    [php]
    public function getName()
    {
      return $this->getFirstName().' '.$this->getLastName();
    }

Tutte le funzioni ripetute di accesso ai dati e la business logic dei dati stessi, possono essere tenute in tali oggetti. Supponiamo di avere una classe `ShoppingCart` in cui si tenere gli `Articoli` (che sono oggetti). Per ottenere l'importo totale del carrello della spesa, necessario per il pagamento, bisogna scrivere un metodo personalizzato per incapsulare il calcolo effettivo, come mostrato nel listato 8-2.

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

C'è un altro punto importante da considerare quando si realizzano delle procedure di accesso ai dati: ogni database utilizza una variante diversa di sintassi SQL. Il passaggio a un altro database costringe a riscrivere parte delle query SQL che sono state progettate per quello precedente. Costruendo le query utilizzando una sintassi indipendente dal database e lasciando la traduzione reale del codice SQL a un componente di terze parti, è possibile cambiare il tipo di database senza troppi problemi. Questo è l'obiettivo del livello di astrazione del database. Costringe a usare una sintassi specifica per le query e fa il lavoro sporco di conformarsi alle particolarità del database e di ottimizzare il codice SQL.

Il principale vantaggio del livello di astrazione è la portabilità, perché rende possibile il passaggio a un'altra base di dati, anche nel bel mezzo di un progetto. Si supponga di dover scrivere rapidamente un prototipo per un'applicazione, ma il cliente non ha ancora deciso quale sistema di base dati può essere la più adatto alle sue esigenze. Si può cominciare a costruire l'applicazione con SQLite, per esempio e passare a MySQL, PostgreSQL, Oracle quando il cliente ha fatto la scelta. Per fare il cambiamento, basta cambiare una riga in un file di configurazione.

Symfony usa Propel o Doctrine come ORM e questi usano oggetti PHP per l'astrazione dei dati del database. Queste due componenti di terze parti, entrambi sviluppati dal team di Propel e Doctrine, sono perfettamente integrati in symfony ed è possibile considerarli come parte del framework. La loro sintassi e le loro convenzioni, descritte in questo capitolo, sono state adattate in modo da differenziarsi il meno possibile da quelle di symfony.

>**NOTE**
>In un progetto symfony, tutte le applicazioni condividono lo stesso modello. Questo è un punto fondamentale a livello di progetto: raggruppare le applicazioni che si basano su regole di business comuni. Questa è la ragione per cui il modello è indipendente dalle applicazioni e i file del modello sono memorizzati in una cartella `lib/model/` nella radice del progetto.

Lo schema del database di symfony
---------------------------------

Allo scopo di creare il modello a oggetti dei dati che symfony userà, bisogna tradurre tutti i modelli relazionali del database in un modello dati a oggetti. L'ORM ha bisogno di una descrizione del modello relazionale per fare la mappatura e questo è chiamato schema. In uno schema si definiscono le tabelle, le relazioni e le caratteristiche delle colonne.

La sintassi di symfony per gli schemi utilizza il formato YAML. I file `schema.yml` devono essere messi nella cartella `mioprogetto/config`.

>**NOTE**
>Symfony riconosce inoltre la sintassi native di Propel in XML, come descritto nella sezione "Oltre schema.yml: schema.xml" più avanti in questo capitolo.

### Esempio di schema

Come tradurre la struttura del database in uno schema? Un esempio è il modo migliore per capirlo. Immaginiamo di avere il database di un blog con due tabelle: `blog_articolo` e `blog_commento`, con la struttura mostrata in figura 8-1.

Figura 8-1 - Struttura delle tabelle del database di un blog

![Struttura delle tabelle del database di un blog](http://www.symfony-project.org/images/book/1_4/F0801.png "Struttura delle tabelle del database di un blog")

Il relativo file `schema.yml` dovrebbe apparire come nel listato 8-3.

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

In un file `schema.yml`, la prima chiave rappresenta un nome della connessione. Può contenere diverse tabelle, ognuna con un set di colonne. In base alla sintassi YAML, le chiavi terminano con un simbolo di due punti e la struttura è mostrata tramite indentazione (uno o più spazi, non tabulazioni).

Una tabella può avere attributi speciali, incluso `phpName` (il nome della classe che verrà generata). Se non si vuole usare un `phpName` per una tabella, symfony lo creerà in base alla versione camelCase del nome della tabella stessa.

>**TIP**
>La convezione camelCase rimuove i trattini bassi dalle parole e rende maiuscole le prime lettere delle parole interne. Le versioni camelCase predefinite di `blog_articolo` e `blog_commento` sono `BlogArticolo` e `BlogCommento`. Il nome di questa convenzione viene dall'aspetto delle lettere maiuscole all'interno di una lunga parola, come le gobbe di un cammello.


Una tabella contiene colonne. Il valore delle colonne può essere definito in tre differenti modi:

  * Se non si definisce nulla (`~` in YAML equivale a `null` in PHP), symfony indovinerà i migliori attributi in base al nome della colonna e alcune convenzioni che saranno descritte nella sezione "Colonne vuote" più avanti in questo capitolo. Per esempio, la colonna `id` nel listato 8-3 non ha bisogno di essere definita: symfony la renderà un intero auto incrementale, chiave primaria della tabella. La colonna `blog_article_id` nella tabella `blog_comment` sarà capita come una chiave esterna verso la tabella `blog_article` (le colonne che terminano con `_id` sono considerate chiave esterne e la tabella collegata è determinata automaticamente a seconda della prima parte del nome della colonna). Le colonne chiamate `created_at` sono automaticamente impostate al tipo `timestamp`. Per queste colonne, non occorre specificare nessun tipo. Questo è il motivo per cui `schema.yml` è così semplice da scrivere.

  * Se si definisce soltanto un attributo, questo sarà il tipo della colonna. Symfony conosce i classici tipi: `boolean`, `integer`, `float`, `date`, `varchar(lunghezza)`, `longvarchar` (convertito, per esempio, a `text` in MySQL) e così via. Per stringhe superiori ai 256 caratteri occorre usare il valore `longvarchar`, che non ha lunghezza (ma non può eccedere 65KB in MySQL).

  * Se occorre definire altri attributi (come il valore di default, se il campo è obbligatorio e così via), si dovrebbero scrivere gli attributi della colonna come un insieme di `chiave: valore`. Questa sintassi estesa è spiegata più avanti nel capitolo.

Le colonne possono avere un attributi `phpName`, che è la versione con prima lettera maiuscola del nome (`Id`, `Title`, `Content` e così via) e non ha bisogno di essere specificata nella maggior parte dei casi.

Le tabelle possono inoltre contenere esplicite chiavi esterne e indici, come anche alcune definizioni specifiche per alcuni database. Controlla la sezione `Sintassi estesa dello schema` più avanti in questo capitolo per saperne di più.

Le classi del modello
---------------------
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

Probabilmente si avrà bisogno di aggiungere metodi e proprietà agli oggetti del modello (pensiamo al metodo `getNome()` nel listato 8-1). Mano a mano che il progetto si evolve, si vorranno aggiungere tabelle o colonne. Ogni volta che si cambia il file `schema.yml`, bisogna rigenerare le classi del modello a oggetti facendo una nuova chiamata di propel:build-model. Se i metodi personalizzati venissero scritti nelle classi generate, sarebbero cancellati dopo ogni rigenerazione.

Le classi `Base` presenti nella cartella `lib/model/om/` sono le uniche effettivamente generate dallo schema. Non bisogna mai modificarle, dal momento che nuove ricostruzioni del modello cancelleranno completamente questi file.

D'altra parte, le classi di oggetti personalizzati presenti nella cartella `lib/model/`, di fatto ereditano da quelle Base`. Quando il task `propel:build-model` è chiamato su un modello esistente, queste classi non vengono modificate. Quindi questo è il posto dove aggiungere i metodi personalizzati.

Il listato 8-4 mostra un esempio di una classe personalizzata del modello, così come viene creata dopo la prima chiamata del task `propel:build-model`.

Listato 8-4 - Esempio di file di una classe del modello, in `lib/model/Article.php`

    [php]
    class Article extends BaseArticle
    {
    }

La classe `Article` eredita ogni cosa della classe `BaseArticle`, ma modifiche nello schema non hanno effetti su `Article`.

Il meccanismo delle classi personalizzate che estendono delle classi base consente di iniziare lo sviluppo, anche senza conoscere il modello relazionale finale del database. La relativa struttura dei file rende il modello sia personalizzabile che estendibile.

### Oggetti e classi Peer

`Article` e `Comment` sono classi oggetto che rappresentano una riga nel database. Danno accesso alle colonne di una riga e colonne collegate. Questo significa che sarai capace di conoscere il titolo di un articolo chiamando un metodo sull'oggetto `Article`, come nell'esempio mostrato nel listato 8-5.

Listato 8-5 - Getter per colonne dei record sono disponibili nelle classi oggetto

    [php]
    $article = new Article();
    // ...
    $title = $article->getTitle();

`ArticlePeer` e `CommentPeer` sono classi peer; ovvero, classi che contengono metodi statici che operano sulle tabelle. Possono fornire un modo per recuperare righe dalle tabelle. I loro metodi restituiscono solitamente un oggetto o collezione di oggetti della collegata classe oggetto, come mostrato nel listato 8-6.

Listato 8-6 - Metodi statici per recuperare record sono disponibili nelle classi peer

    [php]
    // $articles è un array di oggetti di classe Article
    $articles = ArticlePeer::retrieveByPks(array(123, 124, 125));

>**NOTE**
>Da un punto di vista di modello di dati, non potrebbe esserci un oggetto peer. Per questo che i metodi delle classi peer sono chiamati con `::` (operatore per metodi statici), al posto del solito `->` (per le chiamate a metodi d'istanza).

Per questo combinare classi oggetto e peer, base e personalizzate risulta in quattro classi generate per ogni tabella descritta nello schema. A dir la verità, c'è una quinta classe creata nella cartella `lib/model/map/`, che contiene metadati riguardo le tabelle ed è necessaria per l'ambiente di esecuzione. Ma dato che probabilmente non si avrà mai a che fare con questa classe, ce ne si può tranquillamente dimenticare.

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

Quando symfony costruisce il modello, crea una classe base di un oggetto per ciascuno dei modelli definiti nel file `schema.yml`. Ciascuna di queste classi è dotata di accessori e modificatori predefiniti generati in base alle definizioni della colonna: i metodi `new`, `getXXX()` e `setXXX()` aiutano a creare oggetti e forniscono accesso alle proprietà dell'oggetto, come mostrato nel listato 8-7.

Listato 8-7 - Metodi generati nella classe dell'oggetto

    [php]
    $article = new Article();
    $article->setTitle('Il mio primo articolo');
    $article->setContent("Questo è il mio primo articolo.\n Spero che possa piacere!");

    $title   = $article->getTitle();
    $content = $article->getContent();

>**NOTE**
>La classe oggetto generata si chiama `Article`, che è il `phpName` dato alla tabella `blog_article`. Se `phpName` non è stato definito nello schema, la classe sarebbe stata chiamata `BlogArticle`. Gli accessori e modificatori usano una variante camelCase del nome delle colonne, quindi il metodo `getTitle()` resituirà il valore della colonna `title`.

Per impostare diversi valori in una sola volta, si può usare il metodo `fromArray()`, generato per ogni classe oggetto, come mostrato nel listato 8-8.

Listato 8-8 - Il metodo `fromArray()` è un setter multiplo

    [php]
    $article->fromArray(array(
      'Title'   => 'Il mio primo articolo',
      'Content' => 'Questo è il mio primo articolo.\n Spero che possa piacere!',
    ));

>**NOTE**
>Il metodo `fromArray()` ha un secondo parametro, `keyType`. Si può specificare il tipo di chiave dell'array passando uno tra `BasePeer::TYPE_PHPNAME`, `BasePeer::TYPE_STUDLYPHPNAME`, `BasePeer::TYPE_COLNAME`, `BasePeer::TYPE_FIELDNAME` e `BasePeer::TYPE_NUM`. Il valore predefinito è PhpName (i.e. `AuthorId`).

### Recuperare i record correlati

La colonna `blog_article_id` nella tabella `blog_comment` definisce implicitamente una chiave esterna verso la tabella `blog_article`. Ogni commento è collegato a un articolo e un articolo può avere più commenti. Le classi generate possono contenere cinque metodi che traducono queste relazioni in codice a oggetti, come segue:

  * `$comment->getArticle()`: Per ottenere il relativo oggetto `Article`
  * `$comment->getArticleId()`: Per ottenere l'ID del relativo oggetto `Article`
  * `$comment->setArticle($article)`: Per impostare il relativo oggetto `Article`
  * `$comment->setArticleId($id)`: Per impostare il relativo oggetto `Article` tramite il suo ID
  * `$article->getComments()`: Per ottenere un array con i relativi oggetti `Comment`

I metodi `getArticleId()` e `setArticleId()` mostrano che si può considerare la colonna `blog_article_id` come una colonna normale e impostare le relazioni a mano, ma non sono molto interessanti. Il beneficio dell'approccio orientato agli oggetti è molto più evidente negli altri tre metodi. Il listato 8-9 mostra come usare il setter generati.

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

Il listato 8-10 mostra come usare i getter generati. Può inoltre dimostrare come concatenare chiamate ai metodi di oggetti del modello.

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

Il metodo `getArticle()` restituisce un oggetto di classe `Article`, con i benefici del metodo accessore `getTitle()`. Questo è molto migliore di dover eseguire la join tu stesso, che potrebbe richiedere alcune linee di codice (a partire dalla riga `$comment->getArticleId()`).

La variabile `$comments` nel listato 8-10 contiene un array di oggetti di classe `Comment`. Si può mostrare il primo con `$comments[0]` o iterare sulla collezione con `foreach ($comments as $comment)`.

>**NOTE**
>Oggetti del modello sono definiti con un nome singolare per convenzione, e ora si capisce perché. La chiave esterna definita nella tabelle `blog_comment` causa la creazione del metood `getComments()`, chiamato aggiungendo una `s` al nome dell'oggetto `Comment`. Se tu dessi un nome plurale al modello, la generazione porterebbe a un metodo chiamato `getCommentss()`, che non avrebbe senso.

### Salvare ed eliminare dati

Chiamando il costruttore con `new`, un nuovo oggetto è stato creato, ma non un record nella tabella `blog_article`. Nemmeno modificare l'oggetto ha effetto sul database. Per salvare dati nel database, occorre chiamare il metodo `save()` sull'oggetto.

    [php]
    $article->save();

L'ORM è intelligente abbastanza da capire le relazioni tra gli oggetti, quindi salvare l'oggetto `$article` salverà anche il relativo oggetto `$comment`. Conosce inoltre se l'oggetto salvato è già presente nel database, quindi una chiamata a `save()` sarà tradotta in una istruzione `INSERT` oppure `UPDATE` in SQL. La chiave primaria è impostata automaticamente dal metodo `save()`, quindi dopo il salvataggio, si può recuperare la nuova chiave primaria con `$article->getId()`.

>**TIP**
>Si può controllare se un oggetto è nuovo chiamando `isNew()`. E se ti domandi se un oggetto è stato modificato e necessita del salvataggio, si può usare il metodo `isModified()`.

Leggendo i commenti agli articoli, si potrebbe cambiare idea riguardo il pubblicare contenuti su Internet. E se non si apprezza l'ironia dei lettori, si possono cancellare semplicemente i commenti con il metodo `delete()`, come mostrato nel listato 8-11.

Listato 8-11 - Cancellare righe dal database con il metodo `delete()` dell'oggetto

    [php]
    foreach ($article->getComments() as $comment)
    {
      $comment->delete();
    }

>**TIP**
>Anche dopo aver chiamato il metodo `delete()`, un oggetto rimane disponibile fino alla fine dell'esecuzione. Per determinare se un oggetto è stato cancellato dal database, chiama il metodo `isDeleted()`.

### Recuperare record tramite chiave primaria

Se si conosce la chiave primaria di un certo record, usare il metodo `retrieveByPk()` della classe peer per ottenere il relativo oggetto.

    [php]
    $article = ArticlePeer::retrieveByPk(7);

Il file `schema.yml` definisce il campo `id` come la chiave primaria della tabella `blog_article`, quindi questo comando restituisce un articolo che ha `id` uguale a 7. Dato che è stata usata la chiave primaria, sappiamo che soltanto un record sarà restituito; la variabile `$article` contiene un oggetto della classe `Article`.

In alcuni casi, una chiave primaria può consistere in più di una colonna. In questi casi, il metodo `retrieveByPk()` accetta parametri multipli, uno per ogni colonna della chiave primaria.

Si possono inoltre selezionare multipli oggetti basandoti sulla loro chiave primaria, chiamato in metodo generato `retrieveByPks()`, che ha come parametri un array di chiavi primarie.

### Recuperare record con Criteria

Quando si vuole recuperare più di un record, occorre utilizzare il metodo `doSelect()` della classe peer corrispondente agli oggetti che si vogliono ottenere. Per esempio, per recuperare oggetti della classe `Article`, chiamare `ArticlePeer::doSelect()`.

Il primo parametro del metodo `doSelect()` è un oggetto della classe `Criteria`, che è una semplice classe per la costruzione delle query, senza l'utilizzo di SQL per mantenere l'astrazione dal database.

Un oggetto `Criteria` vuoto restituisce tutti gli oggetti della classe. Per esempio, il codice mostrato nel listato 8-12 restituisce tutti gli articoli.

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
>La chiamata a `::doSelect()` è a dir la verità molto più potente di una semplice query SQL. Per prima cosa, il codice SQL generato è ottimizzato per il DBMS scelto. Secondo, su ogni valore passato a `Criteria` viene effettuato l'escape prima di venir intergrato nel codice SQL, che previene rischi di SQL injection. Terzo, questo metodo restituisce un array di oggetti piuttosto che una risorsa PHP. L'ORM crea automaticamente gli oggetti basandosi sulla risorsa restituita dal database. Questo processo è chiamato idratazione.

Per una selezione più complessa, avrai bisogno degli equivalenti di WHERE, ORDER BY, GROUP BY e altre istruzioni SQL. L'oggetto `Criteria` ha metodi e parametri per tutte queste condizioni. Per esempio, per ottenere tutti i commenti scritti da Steve, ordinati per data, costruire un `Criteria` come mostrato nel listato 8-13.

Listato 8-13 - Recuperare record usando Criteria e `doSelect()` (Criteria con condizioni)

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
>Perché usare `CommentPeer::AUTHOR` al posto di `blog_comment.AUTHOR`, che è quello che sarà utilizzato nel codice SQL in ogni caso? Supponi di dover cambiare il nome della colonna da `author` a `contributor` nel database. Se tu avessi usato `blog_comment.AUTHOR`, dovresti cambiarlo a ogni chiamata del metodo. Invece, usando `CommentPeer::AUTHOR`, occorre soltanto cambiare il nome della colonna nel file `schema.yml`, impostare il valore `phpName` ad `AUTHOR` e ricostruire il modello.

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
>Il modo migliore per scoprire e capire quali metodi sono disponibili nelle classi generate è guardare ai file `Base` nella cartella `lib/model/om/` dopo la generazione. I nomi dei metodi sono abbastanza espliciti, ma se servono dei commenti in essi si può impostare il parametro `propel.builder.addComments` a `true` nel file `config/propel.ini` e ricostruire il modello.

Il listato 8-14 mostra un altro esempio di `Criteria` con condizioni multiple. Recupera tutti i commenti di Steve sugli articoli contenenti la parola "enjoy", ordinati per data.

Listato 8-14 - Un altro esempio di record recuperato con Criteria e `doSelect()`--Criteria con condizioni

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

In aggiunta al metodo `doSelect()`, ogni classe peer ha un metodo `doCount()`, che semplicemente restituiscono il numero di record che soddisfano i requisiti passati come parametri e restituisce un numero intero. Dato che nessun oggetto viene restituito, il processo di idratazione non viene eseguito in questo caso, perciò il metodo `doCount()` è più veloce di `doSelect()`.

La classi peer forniscono inoltre i metodi `doDelete()`, `doInsert()` e `doUpdate()`, che ricevono un oggetto `Criteria` come parametro. Questi metodi consentono di eseguire interrogazioni di tipo `DELETE`, `INSERT` e `UPDATE` sul database. Dare un'occhiata alle classi peer generate nel modello per ulteriori dettagli su questi metodi di Propel.

Infine, se si vuole soltanto il primo oggetto, sostituire `doSelect()` con `doSelectOne()`. Questo potrebbe essere il caso in cui si sa che `Criteria` produrrà un risultato soltanto, con il vantaggio che il metodo restuituirà direttamente un oggetto piuttosto di un array.

>**TIP**
>Quando una query restituisce un gran numero di risultati, si potrebbe volerne mostrare soltanto un sottoinsieme. Symfony fornisce una classe per la suddivisione in pagine chiamata `sfPropelPager`, che automatizza la paginazione dei risultati.

### Usare query SQL

A volte non serve recuperare oggetti, ma soltanto avere risultati sintetici calcolati dal database. Per esempio, per ottenere l'ultima data di creazione di tutti gli articoli, non ha senso recuperare tutti gli articoli ed eseguire un ciclo sull'array. È preferibile richiedere direttamente al database di restituire soltanto il risultato, perché salterà il processo di idratazione.

D'altra parte, non è il caso di usare i comandi PHP per gestire direttamente il database, perché perderesti i vantaggi dell'astrazione dal database. Questo significa che occorre aggirare l'ORM (Propel) ma non l'astrazione dal database (PDO).

Una query al database con PHP Data Objects richiede i seguentii passi:

  1. Ottienre una connessione al database
  2. Creare una query
  3. Usarla per creare uno "statement"
  4. Ciclare sul risultato dell'esecuzione dello statement

Se queste sembrano parole campate in aria, il codice del listato 8-15 probabilmente sarà più esplicito.

Listato 8-15 - Query SQL personalizzata con PDO

    [php]
    $connection = Propel::getConnection();
    $query = 'SELECT MAX(?) AS max FROM ?';
    $statement = $connection->prepare($query);
    $statement->bindValue(1, ArticlePeer::CREATED_AT);
    $statement->bindValue(2, ArticlePeer::TABLE_NAME);
    $statement->execute();
    $resultset = $statement->fetch(PDO::FETCH_OBJ);
    $max = $resultset->max;

Proprio come le selezioni con Propel, le query con PDO sono complesse quando si inizia a usarle. Ma anche stavolta, esempi da applicazioni esistenti e tutorial mostreranno il modo giusto.

>**CAUTION**
>Se si è tentati di aggirare questo processo e accedere al database direttamente, si rischia di perdere la sicurezza e l'astrazione fornite da Propel. Facendo in questo modo si impiega di più, ma costinge a usare le migliori pratiche per garantire prestazioni, portabilità e sicurezza della propria applicazione. Questo è vero specialmente per query che contengono parametri che arrivano da una fonte non sicura (come ad esempio l'utente). Propel fa tutto il lavoro necessario per mettere al sicuro il database. Accedere al database dirttamente mette a rischio di attacchi di tipo SQL-injection.

### Uso di colonne speciali per le date

Di solito, quando una tabella ha una colonna chiamata `created_at`, è usata per salvare il timestamp della data di quando il record è stato creato. Lo stesso succede per le colonne `updated_at`, che saranno aggiornate ogni volta che il record stesso viene aggiornato.

La buona notizia è che symfony riconoscerà i nomi di queste colonne e gestirà il loro autonomamente. Non servirò impostare manualmente i valori di `created_at` e `updated_at`, saranno aggiornati automaticamente, come mostrato nel listato 8-16. Lo stesso succede per le colonne chiamate `created_on` e `updated_on`.

Listato 8-16 - Le colonne `created_at` e `updated_at` sono gestite in automatico

    [php]
    $comment = new Comment();
    $comment->setAuthor('Steve');
    $comment->save();

    // Mostra la data di creazione
    echo $comment->getCreatedAt();
      => [date of the database INSERT operation]

Inoltre, il getter per le colonne di tipo data accetta un formato di data come parametro:

    [php]
    echo $comment->getCreatedAt('Y-m-d');

>**SIDEBAR**
>Rifattorizzazione del livello di gestione dei dati
>
>Durante lo sviluppo di un progetto symfony, si inizia spesso scrivendo il codice per il database nelle azioni. Ma le query al database e manipolazione del modello non dovrebbero risiedere nel livello del controllore. Per questo, tutto il codice per la gestione del database dovrebbe essere spostato nel modello. Nel caso occorra fare la stessa richiesta al database in più di un punto nelle azioni, si dovrebbe trasferire il relativo codice nel modello. Aiuta a tenere le azioni corte e leggibili.
>
>Per esempio, si immagini il codice in un blog per recuperare i dieci articoli più popolari per un determinato tag (passato come parametro della richiesta). Questo codice non dovrebbe risiedere nell'azione, ma nel modello. Infatti, se si avrà bisogno di mostrare questa lista in un template, l'azione dovrebbe essere così semplice:
>
>     [php]
>     public function executeShowPopularArticlesForTag($request)
>     {
>       $tag = TagPeer::retrieveByName($request->getParameter('tag'));
>       $this->forward404Unless($tag);
>       $this->articles = $tag->getPopularArticles(10);
>     }
>
>L'azione crea un oggetto della classe `Tag` dal parametro di richiesta. Poi, tutto il codice necessario alla query al database è localizzato nel metodo `getPopularArticles()` di questa classe. Rende l'azione più leggibile e il codice può essere facilmente riutilizzato in un'altra azione.
>
>Spostare il codice nel posto più appropriato è una delle tecniche della rifattorizzazione. Se lo si fa spesso, il proprio codice sarà facilmente mantenibile e comprensibile da altri sviluppatori. Una buona convenzione sul quando rifattorizzare verso il modello consiste nel riuscire a mantenere un numero di righe di codice di un'azione inferiore a dieci.

Connessioni al database
-----------------------

Il modello è indipendente dal database usato, ma per forza di cose si userà un database. Le informazioni minime richiesta da symfony per inviare richieste al database sono il nome, le credenziali di accesso e il tipo di database. Queste impostazioni di connessione possono essere configurati passando un data source name (DSN) al task `configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=blog" root mYsEcret

Le impostazioni di connessione dipendono dall'ambiente. Si possono definire configurazioni differenti per gli ambienti `prod`, `dev` e `test`, o per ogni altro ambiente nella propria applicazione usando l'opzione `env`:

    $ php symfony configure:database --env=dev "mysql:host=localhost;dbname=blog_dev" root mYsEcret

Questa configurazione può inoltre essere sovrascritta per ogni applicazione. Per esempio, si può usare questo approccio per avere differenti politiche di sicurezza per le applicazioni frontend e backend, e definire utenti del database differenti con privilegi diversi per gestire tutto ciò:

    $ php symfony configure:database --app=frontend "mysql:host=localhost;dbname=blog" root mYsEcret

Per ogni ambiente, si possono definire differenti connessioni. Ogni connessione si riferisce allo schema chiamato con lo stesso nome. La connessione predefinita si chiama `propel` e si riferisce allo schema `propel` nel listato 8-3. L'opzione `name` consente di creare un'altra connessione:

    $ php symfony configure:database --name=main "mysql:host=localhost;dbname=example" root mYsEcret

Si possono inoltre inserire queste impostazioni di connessione manualmente nel file `databases.yml`, collocato nella cartella `config/`. Il listato 8-17 mostra un esempio di questo file e il listato 8-18 mostra lo stesso esempio con la notazione estesa.

Listato 8-17 - Notazione semplice per la connessione al database

    [yml]
    all:
      propel:
        class:          sfPropelDatabase
        param:
          dsn:          mysql://login:passwd@localhost/blog

Listato 8-18 - Esempio di impostazioni di connessione al database, in `myproject/config/databases.yml`

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
          phptype:            mysql     # Tipo di database
          hostspec:           localhost
          database:           blog
          username:           login
          password:           passwd
          port:               80
          encoding:           utf8      # Codifica di default per la creazione delle tabelle
          persistent:         true      # Usa connessione persistente

I valori consentiti per il parametri `phptype` sono quelli dei database supportati da PDO:

  * `mysql`
  * `mssql`
  * `pgsql`
  * `sqlite`
  * `oracle`

`hostspec`, `database`, `username` e `password` sono i normali parametri di connessione.

Per sovrascrivere la configurazione per applicazione, avrai bisogno di modificare il file specifico dell'applicazione, come `apps/frontend/config/databases.yml`.

Se usi un database SQLite, il parametro `hostspec` deve essere impostato al percorso del file del database. Per esempio, se si tieni il proprio blog in `data/blog.db`, il file `databases.yml` sarà come quello nel listato 8-19.

Listato 8-19 - Connessione al database specifica per SQLite usando un percorso file come host

    [yml]
    all:
      propel:
        class:      sfPropelDatabase
        param:
          phptype:  sqlite
          database: %SF_DATA_DIR%/blog.db

Estendere il modello
--------------------

I metodi generati del modello sono ottimi, ma spesso non sufficienti. Al momento di implementare la logica, si avrà bisogno di estenderlo, aggiungendo nuovi metodi oppure sovrascrivendo quelli esistenti.

### Aggiungere nuovi metodi

Si possono aggiungere nuovi metodi alle classi del modello vuote generate nella cartella `lib/model`. Usare `$this` per chiamare metodi sull'oggetto corrente e usare `self::` per chiamare metodi statici sulla classe corrente. Si ricordi che le classi personalizzate ereditano i metodi dalle classi `Base` collocate nella cartella `lib/model/om`.

Per esempio, per l'oggetto `Article` generato basato sul listato 8-3, si può aggiungere un metodo magico `__toString()` per far sì che un comando `echo` su un oggetto di classe `Article` mostri il suo titolo, come mostrato nel listato 8-20.

Listato 8-20 - Personalizzazione del modello, in `lib/model/Article.php`

    [php]
    class Article extends BaseArticle
    {
      public function __toString()
      {
        return $this->getTitle();  // getTitle() è ereditato da BaseArticle
      }
    }

Si possono inoltre estendere le classi peer, per esempio, per aggiungere un metodo per recuperare tutti gli articoli ordinati per data di creazione, come mostrato nel listato 8-21.

Listato 8-21 - Personalizzazione del modello, in `lib/model/ArticlePeer.php`

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

I nuovi metodi sono disponibili nello stesso modo di quelli generati, come mostrato nel listato 8-22.

Listato 8.22 - Usare i metodi personalizzati è come usare i metodi generati

    [php]
    foreach (ArticlePeer::getAllOrderedByDate() as $article)
    {
      echo $article;      // Chiamerà il metodo magico __toString()
    }

### Sovrascrivere i metodi esistenti

Se alcuni dei metodi generati nelle classi `Base` non soddisfano i requisiti, si può comunque sovrascriverli nelle classi personalizzate. Assicurarsi che abbiano la stessa firma (ovvero lo stesso numero di parametri).

Per esempio, il metodo `$article->getComments()` restituisce un array di oggetti `Comment`, senza alcun ordine particolare. Se si vogliono i risultati ordinati per data di creazione, con gli ultimi commenti per primi, sovrascrivere il metodo `getComments()`, come mostrato nel listato 8-23. Attenzione: il metodo originale `getComments()` (collocato in `lib/model/om/BaseArticle.php`) si aspetta un oggetto `Criteria` e una connessione come parametri, quindi la nuova funzione deve fare lo stesso.

Listato 8-23 - Sovrascrivere metodi esistenti del modello, in `lib/model/Article.php`

    [php]
    public function getComments($criteria = null, $con = null)
    {
      if (is_null($criteria))
      {
        $criteria = new Criteria();
      }
      else
      {
        // Gli oggetti sono passati per riferimento in PHP5, quindi per evitare di modificare l'originale, occorre clonarlo
        $criteria = clone $criteria;
      }
      $criteria->addDescendingOrderByColumn(CommentPeer::CREATED_AT);

      return parent::getComments($criteria, $con);
    }

Il metodo personalizzato chiama infine quello della classe Base ed è una buona pratica. Comunque, lo si può aggirare completamente e restituire il risultato desiderato.

### Usare i comportamenti (behavior) del modello

Alcune modifiche al modello sono generiche e possono venire riutilizzate. Per esempio, i metodi per rendere un oggetto del modello ordinabile e un blocco ottimistico per prevenire conflitti tra il salvataggio di oggetti concorrenti sono estensioni generiche che possono venir aggiunte a diverse classi.

Symfony fornisce queste estensioni tramite i behavior. I behavior sono classi esterne che forniscono metodi aggiuntivi alle classi del modello. Le classi del modello contengono già degli "appigli" (hooks) e symfony sa come estenderle.

Per abilitare i behavior nelle classi del modello, occorre modificare un'impostazione nel file `config/propel.ini`:

    propel.builder.AddBehaviors = true     // Il valore di default è false

Non c'è nessun behavior incluso in symfony di default, ma possono essere installati tramite plugin. Dopo che un behavior è installato, si può assegnare il behavior a una classe con una singola istruzione. Per esempio, se si installa il plugin `sfPropelParanoidBehaviorPlugin` nella propria applicazione, si può estendere una classe `Article` con questo behavior, aggiungendo queste righe alla fine di `Article.class.php`:

    [php]
    sfPropelBehavior::add('Article', array(
      'paranoid' => array('column' => 'deleted_at')
    ));

Dopo aver ricostruito il modello, gli oggetti `Article` eliminati resteranno nel database, invisibile alle query create usando l'ORM, a meno che non si disabiliti temporaneamente il behavior con `sfPropelParanoidBehavior::disable()`.

In alternativa, si possono dichiarare i behavior direttamente dentro allo `schema.yml`, aggiungendoli all'interno della chiave `_behaviors` (vedere il listato 8-34 di seguito).

Controllare la lista dei plugin di symfony sul [repository](http://www.symfony-project.org/plugins/) ufficiale per trovare i behavior. Ognuno ha la sua documentazione e guida di installazione.

Sintassi estesa dello schema
----------------------------

Un file `schema.yml` può essere semplice, come mostrato nel listato 8-3. Ma i modelli relazionali sono spesso complessi. Per questo lo schema ha una sinstassi estesa, che consente di gestire quasi tutti i casi.

### Attributi

Le connessioni e le tabelle possono avere attributi specifici, come mostrato nel listato 8-24. Questi sono sotto la chiave `_attributes`.

Listato 8-24 - Attributi per connessioni e tabelle

    [yml]
    propel:
      _attributes:   { noXsd: false, defaultIdMethod: none, package: lib.model }
      blog_article:
        _attributes: { phpName: Article }

Si potrebbe voler validare lo schema prima di eseguire la generazione. Per farlo, disattivare l'attributo `noXSD` della connessione. La connessione supporta anche un attributo `defaultIdMethod`. Se non viene fornito, sarà usato il metodo nativo di generazione di ID, ad esempio `autoincrement` per MySQL o `sequences` per PostgreSQL. L'altro possibile valore è `none`.

L'attributo `package` è come un namespace: esso determina il percorso in cui mettere le classi generate. Il valore predefinito è `lib/model/`, ma lo si può cambiare per organizzare i modelli in pacchetti. Ad esempio, se non si vogliono mischiare le classi in una sola cartella, si possono definire due schemi: `lib.model.business` e `lib.model.stats`.

Abbiamo già visto l'attributo delle tabelle `phpName`, usato per impostare il nome della classe generata riferita a una tabella.

Le tabelle contenenti dati localizzati (cioè diverse versioni dello stesso contenuto, in una tabella correlata, per l'internazionalizzazione) posso accettare due ulteriori parametri (si veda il capitolo 13 per i dettagli), come mostrato nel listato 8-25.

Listato 8-25 - Attributi per le tabelle i18n

    [yml]
    propel:
      blog_article:
        _attributes: { isI18N: true, i18nTable: db_group_i18n }

>**SIDEBAR**
>Gestire schemi multipli
>
>Si può avere più di uno schema per applicazione. Symfony considererà ogni file il cui nome finisce per `schema.yml` o `schema.xml` nella cartella `config/`. Se l'applicazione ha molte tabelle o se alcune tabelle non condividono la stessa connessione, questo approccio può essere molto utile.
>
>Si considerino questi due schemi:
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
>Entrambi gli schemi condividono la stessa connessione (`propel`) e le classi `Article` e `Hit` saranno generate sotto la stessa cartella `lib/model/`. È tutto come se avessimo un unico schema.
>
>Si possono anche avere schemi diversi per connessioni diverse (per esempio, `propel` e `propel_bis`, da definire in `databases.yml`) e organizzare le classi generate in sotto-cartelle:
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
>Diverse applicazioni usano più di uno schema. In particolare, alcuni plugin hanno il loro schema e il loro `package`, per evitare di confondersi con le classi del progetto (si veda il capitolo 17 per i dettagli).

### Dettagli delle colonne

La sintassi di base fornisce due scelte: lasciare che symfony deduca le caratteristiche delle colonne dal loro nome (dando un valore vuoto) o definire il tipo con una della chiavi. Il listato 8-26 mostra queste scelte.

Listato 8-26 - Attributi di base delle colonne

    [yml]
    propel:
      blog_article:
        id:    ~            # symfony fa da solo
        title: varchar(50)  # specificato

Ma si può definire di più per una colonna. Se si vuole, occorre definire le impostazioni della colonna come array associativo, come mostrato nel listato 8-27.

Listato 8-27 - Attributi complessi delle colonne

    [yml]
    propel:
      blog_article:
        id:       { type: integer, required: true, primaryKey: true, autoIncrement: true }
        name:     { type: varchar(50), default: foobar, index: true }
        group_id: { type: integer, foreignTable: db_group, foreignReference: id, onDelete: cascade }

I parametri delle colonne sono i seguenti:

  * `type`: il tipo. Le scelte sono tra `boolean`, `tinyint`, `smallint`, `integer`, `bigint`, `double`, `float`, `real`, `decimal`, `char`, `varchar(size)`, `longvarchar`, `date`, `time`, `timestamp`, `bu_date`, `bu_timestamp`, `blob` e `clob`.
  * `required`: Booleano. Impostare a `true` se si vuole che la colonna sia obbligatoria.
  * `size`: La dimensione o la lunghezza del campo, per i tipi che la supportano.
  * `scale`: Numero di cifre decimali, per il tipo `decimal` (occorre specificare anche `size`).
  * `default`: Il valore predefinito.
  * `primaryKey`: Booleano. Impostare a `true` per le chiavi primarie.
  * `autoIncrement`: Booleano. Impostare a `true` per le colonne di tipo `integer` che devono avere un valore auto-incrementato.
  * `sequence`: Nome della sequenza da usare per le colonne `autoIncrement` (per PostgreSQL e Oracle).
  * `index`: Booleano. Impostare a `true` per un indice semplice o a `unique` per una chiave univoca sulla colonna.
  * `foreignTable`: Un nome di tabella, usato per creare una chiave esterna.
  * `foreignReference`: Il nome di una colonna correlata, se una chiave esterna è definita con `foreignTable`.
  * `onDelete`: Determinea l'azione da eseguire quando un record viene cancellato. Se impostato a `setnull`, la colonna della chiave esterna viene posta a `null`. Se impostato a `cascade`, il record viene cancellato. Se il database non supporta questi comportamenti, l'ORM li emula. Questo è rilevante solo per colonne con `foreignTable` e `foreignReference`.
  * `isCulture`: Booleano. Impostare a `true` per colonne che riportano la cultura nelle tabelle localizzate (vedere capitolo 13).

### Chiavi esterne

Come alternativa agli attributi `foreignTable` e `foreignReference` delle colonne, si può usare la chiave `_foreignKeys:` in una tabella. Lo schema nel listato 8-28 crea una chiave esterna per la colonna `user_id` verso la colonna `id` della tabella `blog_user`.

Listato 8-28 - Sintassi alternativa per le chiavi esterne

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

La sintassi alternativa è utile per chiavi esterne multiple e per dare un nome alle chiavi esterne, come mostrato nel listato 8-29.

Listato 8-29 - Sintassi alternativa per le chiavi esterne applicata a chiavi esterne multiple

        _foreignKeys:
          nome_chiave:
            foreignTable:  db_user
            onDelete:      cascade
            references:
              - { local: user_id, foreign: id }
              - { local: post_id, foreign: id }

### Indici

Come alternativa all'attributo `index` di una colonna, si possono aggiungere indici sotto la chiave `_indexes:` in una tabella. Se si vogliono definire chiavi univoche, occorre invece usare `_uniques:`. Per colonne che richiedono una dimensione, perché sono colonne testuali, la dimensione dell'indice è specificata nello stesso modo della lunghezza delle colonne, usando le parentesi. Il listato 8-30 mostra la sintassi alternativa per gli indici.

Listato 8-30 - Sintassi alternativa per indici e chiavi univoche

    [yml]
    propel:
      blog_article:
        id:               ~
        title:            varchar(50)
        created_at:
        _indexes:
          mio_indice:     [title(10), user_id]
        _uniques:
          mio_indice_2:   [created_at]

La sintassi alternativa è utile solo per indici su più colonne.

### Colonne vuote

Quando incontra una colonna senza valori, symfony fa alcune magie per aggiungere i suoi valori. Si veda il listato 8-31 per i dettagli aggiunti alle colonne vuote.

Listato 8-31 - Dettagli delle colonne dedotti dal nome della colonna

    // Colonne vuote chiamate "id" sono considerate chiavi primarie
    id:         { type: integer, required: true, primaryKey: true, autoIncrement: true }

    // Colonne vuote chiamate "XXX_id" sono considerate chiavi esterne
    foobar_id:  { type: integer, foreignTable: db_foobar, foreignReference: id }

    // Colonne vuote chiamate "created_at", "updated at", "created_on" e "updated_on"
    // sono considerate date e diventano di tipo timestamp
    created_at: { type: timestamp }
    updated_at: { type: timestamp }

Per le chiavi esterne, symfony cercherà una tabella con lo stesso `phpName` dell'inizio del nome della colonna e, se ne troverà uno, userà il suo nome come `foreignTable`.

### Tabelle I18n

Symfony supporta l'internazionalizzazione dei contenuti tramite tabelle dedicate. Questo significa che quando si ha un contenuto da internazionalizzare, viene memorizzato in due tabelle separate: una per le colonne che non cambiano e una per le colonne da internazionalizzare.

In un file `schema.yml`, tutto è implicito quando si dà il nome `pippo_i18n` a una tabella. Per esempio, lo schema mostrato nel listato 8-32 verrà automaticamente completato con colonne e attributi per far funzionare il meccanismo di internazionalizzazione. Internamente, symfony lo interpreterà come se fosse scritto come nel listato 8-33. Il capitolo 13 contiene maggiori informazioni su i18n.

Listato 8-32 - Meccanismo i18n implicito

    [yml]
    propel:
      db_group:
        id:          ~
        created_at:  ~

      db_group_i18n:
        name:        varchar(50)

Listato 8-33 - Meccanismo i18n esplicito

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

### Comportamenti

I comportamenti sono modificatori dei modelli forniti da plugin, che aggiungono nuove capacità alle classi di Propel. Il capitolo 17 parla più approfonditamente dei comportamenti. I comportamenti si possono definire nello schema, elencandoli per ciascuna tabella, insieme con i loro parametri, sotto la chiave `_behaviors`. Il listato 8-34 fornisce un esempio estendendo la classe `BlogArticle` con il comportamento `paranoid`.

Listato 8-34 - Dichiarazione dei comportamenti

    [yml]
    propel:
      blog_article:
        title:          varchar(50)
        _behaviors:
          paranoid:     { column: deleted_at }

### Oltre lo schema.yml: lo schema.xml

Di fatto, il formato `schema.yml` è interno a symfony. Quando si richiama un comando di Propel, symfony traduce tale file in un file `generated-schema.xml`, che è il tipo di file che Propel si aspetta per eseguire effettivamente ciò che deve fare sul modello.

Il file `schema.xml` contiene le stesse informazioni del suo equivalente YAML. Per esempio, il listato 8-3 viene convertito nel file XML mostrato nel listato 8-35.

Listato 8-35 - Esempio di `schema.xml`, corrispondente al listato 8-3

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

La descrizione del formato `schema.xml` si trova nella sezione "Getting Started" del [sito di Propel](http://www.propelorm.org/wiki/Documentation/1.4/Schema-Reference).

Il formato YAML è stato disegnato per mantenere gli schemi semplici da leggere e da scrivere, ma il prezzo da pagare è la difficoltà di descrivere schemi più complessi con un file `schema.yml`. D'altro canto, il formato XML consente una descrizione completa dello schema, qualunque complessità esso abbia, e include impostazioni specifiche per i database, ereditarietà delle tabelle e così via.

Symfony comprende anche gli schemi scritti in formato XML. Quindi, se si ha uno schema troppo complesso per la sintassi YAML o se si ha uno schema XML già esistente, non è necessario passare alla sintassi YAML. Basta mettere il proprio `schema.xml` nella cartella `config/` del progetto e costruire il modello.

>**SIDEBAR**
>Propel in symfony
>
>Tutti i dettagli dati in questo capitolo non sono specifici di symfony, ma piuttosto di Propel. Si può scegliere un livello di astrazione di ogetti/relazioni, ma symfony funziona molto bene con Propel, per le seguenti ragioni:
>
>Tutti gli oggetti delle classi del modello e le classi `Criteria` si caricano automaticamente. Non appena le si usa, symfony includerà i file giusti e non si avrà bisogno di istruzioni di inclusione. In symfony, Propel non ha bisogno di essere lanciato né inizializzato. Quando un oggetto usa Propel, la libreria si inizializza da sé. Alcuni helper di symfony usano gli oggetti di Propel per compiti ad alto livello (come paginazione o filtri). Gli oggetti di Propel consentono una prototipazione rapida e la generazione di un backend per la propria applicazione (il capitolo 14 fornisce maggiori dettagli in merito). Lo schema è più veloce da scrivere con il file `schema.yml`.
>
>Infine, Propel è indipendente dal database usato, così come lo è symfony.

Non creare il modello due volte
-------------------------------

Lo svantaggio nell'utilizzo di un ORM è che bisogna definire la struttura dati due volte: una per il database e una per il modello a oggetti. Per fortuna, symfony fornisce degli strumenti a riga di comando per generare l'uno basato sull'altro, in modo da evitare la duplicazione del lavoro.

### Creare l'SQL della struttura di un database basandosi su uno schema esistente

Se si inizia l'applicazione scrivendo il file `schema.yml`, symfony può generare una query SQL che crea le tabelle direttamente dal modello YAML dei dati. Per generare la query, andare nella cartella radice del progetto e digitare:

    $ php symfony propel:build-sql

Verrà creato un file `lib.model.schema.sql` in `mioprogetto/data/sql/`. Notare che il codice SQL generato sarà ottimizzato per il sistema di database definito nel parametro `phptype` o nel file `propel.ini`.

Si può usare il file `lib.model.schema.sql` direttamente per costruire le tabelle. Ad esempio, in MySQL, digitare:

    $ mysqladmin -u root -p create blog
    $ mysql -u root -p blog < data/sql/lib.model.schema.sql

Il codice SQL generato è utile anche per ricostruire il database in un altro ambiente o per passare a un altro database. Se le impostazioni di connessioni sono definite correttamente in `propel.ini`, si può anche usare il comando `php symfony propel:insert-sql` per farlo automaticamente.

>**TIP**
>La riga di comando offre anche un task per popolare il database con i dati caricati da un file di testo. Vedere il capitolo 16 per maggiori informazioni sul task `propel:data-load` e i file delle fixture in YAML.

### Generare un modello dei dati YAML da un database esistente

Symfony può usare Propel per generare un file `schema.yml` da un database esistente, grazie alla introspezione (la capacità dei database di determinare la struttura delle tabelle sulle quali stanno operando). Questo può essere particolarmente utile quando si fa reverse-engineering, oppure quando si preferisce lavorare sul database prima di lavorare sul modello a oggetti.

Per fare ciò, è necessario assicurarsi che il file `databases.yml` del progetto punti al database corretto e contenga tutte le informazioni per la connessione. Quindi lanciare il comando `propel:build-schema`:

    $ php symfony propel:build-schema

Dalla struttura del database viene generato un nuovo file `schema.yml` nella cartella `config/`. Si può costruire il modello basato su questo schema.

Il comando di generazione dello schema è potente e può aggiungere allo schema molte informazioni dipendenti dal database. Poiché il formato YAML non gestisce questo tipo di informazioni, occorre generare uno schema XML per poterle sfruttare. Lo si può fare semplicemente aggiungendo un parametro `xml` al task `build-schema`:

    $ php symfony propel:build-schema --xml

Invece di generare un file `schema.yml`, verrà creato un file `schema.xml` pienamente compatibile con Propel, contenente tutte le informazioni specifiche del database. Si faccia però attenzione, perché gli schemi XML tendono a essere molto prolissi e difficili da leggere.

>**SIDEBAR**
>La configurazione `propel.ini`
>
>Questo file contiene altre impostazioni usate per configurare il generatore di Propel per rendere le classi del modello generate maggiormente compatibili con symfony. La maggior parte delle impostazioni sono di uso interno e senza interesse per l'utente, tranne alcune:
>
>      // Le classi Base sono autocaricate in symfony
>      // Impostare a true per usare invece include_once
>      // (Piccolo impatto negativo sulle prestazioni)
>      propel.builder.addIncludes = false
>
>      // Le classi generate non sono commentate
>      // Impostare a true per aggiungere i commenti alle classi Base
>      // (Piccolo impatto negativo sulle prestazioni)
>      propel.builder.addComments = false
>
>      // I comportamenti non sono gestiti
>      // Impostare a true per poterli gestire
>      propel.builder.AddBehaviors = false
>
>
>Dopo una modifica al file `propel.ini`, non dimenticare di ricostruire il modello, in modo che i cambiamenti abbiano effetto.

Riepilogo
---------

Symfony usa Propel come ORM e gli oggetti dei dati di PHP per il livello di astrazione del database. Ciò significa che è necessario prima descrivere lo schema relazionale del database in YAML prima di generare le classi del modello a oggetti. Poi, in fase di runtime, utilizzare i metodi dell'oggetto e le classi peer per recuperare informazioni su un record o un insieme di record. È possibile sovrascrivere ed estendere facilmente il modello aggiungendo metodi alle classi personalizzate. Le impostazioni di connessione sono definite in un file `databases.yml`, che può supportare più di una connessione. E la linea di comando contiene dei task speciali per evitare di duplicare la definizione della struttura. 

Il livello del modello è il più complesso del framework symfony. Una delle ragioni di questa complessità è che la manipolazione dei dati è una questione intricata. I problemi di sicurezza correlati sono fondamentali per un sito web e non devono essere ignorati. Un'altra ragione è che symfony è più adatto ad applicazioni di medio-grandi dimensioni in contesto enterprise. In tali applicazioni, le automazioni fornite dal modello di symfony possono davvero rappresentare un guadagno di tempo che vale l'investimento per apprendere il suo funzionamento. 

Quindi non esitate nel dedicare un periodo di prova al modello a oggetti e ai metodi, per comprenderli pienamente. La solidità e la scalabilità delle applicazioni saranno la ricompensa.
