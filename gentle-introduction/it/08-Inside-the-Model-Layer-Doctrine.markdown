Capitolo 8 - All'interno dello strato modello (Doctrine)
===========================================

Gran parte della trattazione finora è stata dedicata alla costruzione di pagine e all'elaborazione delle richieste e delle risposte. Ma la business logic di una applicazione web si basa principalmente sul suo modello di dati. Il componente predefinito di symfony per il modello, è basato su uno strato che mappa oggetti e relazioni. Symfony è in bundle con i due più popolari ORM per PHP: [Propel](http://www.propelorm.org/) e [Doctrine](http://www.doctrine-project.org/). In un'applicazione symfony, si accede ai dati memorizzati in un database e li si modifica, attraverso gli oggetti; non è necessario riferirsi direttamente al database. Quest'ultimo mantiene un elevato livello di astrazione e portabilità.

Questo capitolo spiega come creare un modello di dati a oggetti e come accedere ai dati e modificarli con Doctrine. Viene anche trattata l'integrazione di Doctrine con symfony.

>**TIP**
>Se si vuole utilizzare Propel al posto di Doctrine, leggere l'Appendice A che contiene le stesse informazioni ma riferite a Propel.

Perché usare un ORM e uno strato per l'astrazione?
--------------------------------------------------

I database sono relazionali. PHP 5 e symfony sono orientati agli oggetti. Per poter accedere nel modo più efficace al database in un contesto orientato agli oggetti, è indispensabile una interfaccia per tradurre la logica degli oggetti nella logica relazionale. Come spiegato nel capitolo 1, questa interfaccia è chiamata Object-Relational Mapping (ORM), ed è costituita di oggetti che forniscono l'accesso ai dati e mantengono le business rules all'interno di se stessi.

Il vantaggio principale di un ORM è la riutilizzabilità, che consente ai metodi di un oggetto di tipo dato, di essere chiamato da varie parti dell'applicazione, anche da diverse applicazioni. Lo strato ORM incapsula anche la logica dei dati, ad esempio, il calcolo del punteggio degli utenti di un forum basato su quanti contributi sono stati fatti e quanto sono popolari. Quando una pagina deve visualizzare un tale punteggio degli utenti, basta chiamare semplicemente un metodo nel modello dei dati, senza preoccuparsi dei dettagli del calcolo. Se in seguito bisogna modificare il calcolo, sarà sufficiente modificare il metodo nel modello, lasciando il resto dell'applicazione invariata.

Usare oggetti al posto di record e classi al posto di tabelle, ha un altro vantaggio: la possibilità di aggiungere agli oggetti nuove funzioni di accesso che non necessariamente corrispondono a una colonna in una tabella. Per esempio, se si ha una tabella chiamata `cliente` con due campi chiamati `nome` e `cognome`, si potrebbe volere la possibilità di chiedere solo il `Nome`. In un mondo orientato agli oggetti, basta aggiungere un nuovo metodo accessor alla classe `Cliente`, come si può vedere nel Listato 8-1. Dal punto di vista dell'applicativo, non vi è alcuna differenza tra `Nome`, `Cognome`, e `NomePersona`: sono tutti attributi della classe `Cliente`. Solo la classe stessa può determinare quali attributi corrispondono a una colonna del database.

Listato 8-1 - Il metodo accessor maschera la struttura della tabella in una classe del modello

    [php]
    public function getNomePersona()
    {
      return $this->getNome().' '.$this->getCognome();
    }

Tutte le funzioni ripetute di accesso ai dati e la business logic dei dati stessi, possono essere tenute in tali oggetti. Supponiamo di avere una classe `ShoppingCart` in cui si tenere gli `Articoli` (che sono oggetti). Per ottenere l'importo totale del carrello della spesa, necessario per il pagamento, bisogna scrivere un metodo personalizzato per incapsulare il calcolo effettivo, come mostrato nel Listato 8-2.

Listato 8-2 - Il metodo accessor maschera la logica dei dati

    [php]
    public function getTotale()
    {
      $totale = 0;
      foreach ($this->getArticoli() as $articolo)
      {
        $totale += $articolo->getPrezzo() * $articolo->getQuantita();
      }

      return $totale;
    }

C'è un altro punto importante da considerare quando si realizzano delle procedure di accesso ai dati: ogni database utilizza una variante diversa di sintassi SQL. Il passaggio a un altro DataBase Management System (DBMS) costringe a riscrivere parte delle query SQL che sono state progettate per quello precedente. Costruendo le query utilizzando una sintassi indipendente dal database e lasciando la traduzione reale nell'SQL a un componente di terze parti, è possibile cambiare il tipo di database senza troppi problemi. Questo è l'obiettivo dello strato di astrazione del database. Costringe a usare una sintassi specifica per le query e fa il lavoro sporco di conformarsi alle particolarità del DBMS e di ottimizzare il codice SQL. 

Il principale vantaggio del livello di astrazione è la portabilità, perché rende possibile il passaggio ad un'altra base di dati, anche nel bel mezzo di un progetto. Si supponga di dover scrivere rapidamente un prototipo per un'applicazione, ma il cliente non ha ancora deciso quale sistema di base dati può essere la più adatto alle sue esigenze. Si può cominciare a costruire l'applicazione con SQLite, per esempio e passare a MySQL, PostgreSQL, Oracle quando il cliente ha fatto la scelta. Per fare il cambiamento, basta cambiare una riga in un file di configurazione. 

Symfony usa Propel o Doctrine come ORM, e questi usano oggetti PHP per l'astrazione dei dati del database. Queste due componenti di terze parti, entrambi sviluppati dal team di Propel e Doctrine, sono perfettamente integrati in symfony, ed è possibile considerarli come parte del framework. La loro sintassi e le loro convenzioni, descritte in questo capitolo, sono state adattate in modo da differenziarsi il meno possibile da quelle di symfony.

>**NOTE**
>In un progetto symfony, tutte le applicazioni condividono lo stesso modello. Questo è un punto fondamentale a livello di progetto: raggruppare le applicazioni che si basano su regole di business comuni. Questa è la ragione per cui il modello è indipendente dalle applicazioni e i file del modello sono memorizzati in una cartella `lib/model/` nella radice del progetto.

Lo schema del database di symfony
---------------------------------

Allo scopo di creare il modello a oggetti dei dati che symfony andrà ad usare, bisogna tradurre tutti i modelli relazionali del database in un modello dati a oggetti. L'ORM ha bisogno di una descrizione del modello relazionale per fare la mappatura e questo è chiamato schema. In uno schema si definiscono le tabelle, le relazioni e le caratteristiche delle colonne.

La sintassi di symfony per gli schemi utilizza il formato YAML. I file `schema.yml` devono essere messi nella cartella `mioprogetto/config/doctrine`

### Esempio di schema

Come tradurre la struttura del database in uno schema? Un esempio è il modo migliore per capirlo. Immaginiamo di avere il database di un blog con due tabelle:
 `blog_articolo` e `blog_commento`, con la struttura mostrata in Figura 8-1.

Figura 8-1 - Struttura delle tabelle del database di un blog

![Struttura delle tabelle del database di un blog](http://www.symfony-project.org/images/book/1_4/F0801.png "Struttura delle tabelle del database di un blog")

Il relativo file `schema.yml` dovrebbe apparire come nel Listato 8-3.

Listato 8-3 - Esempio di file `schema.yml`

    [yml]
    Articolo:
      actAs: [Timestampable]
      tableName: blog_articolo
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        titolo:   string(255)
        contenuto: clob
    
    Commento:
      actAs: [Timestampable]
      tableName: blog_commento
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        articolo_id: integer
        autore: string(255)
        contenuto: clob
      relations:
        Articolo:
          onDelete: CASCADE
          foreignAlias: Comments

Notare che il nome del database (`blog`) non compare nel file `schema.yml`. Il database invece è descritto con un nome di connessione (`doctrine` in questo esempio). Questo perché le impostazioni di connessione effettive possono dipendere dall'ambiente in cui l'applicazione è in esecuzione. Per esempio, quando si esegue l'applicazione nell'ambiente di sviluppo, si accede ad un database di sviluppo (può essere `blog_dev`), ma con lo stesso schema del database di produzione. Le impostazioni di connessione saranno specificate nel file `databases.yml`, descritto più avanti in questo capitolo nella sezione "Connessioni del database". Lo schema non contiene nessuna impostazione di connessione, solo un nome di connessione, per mantenere l'astrazione del database.

### Sintassi di base dello schema

In un file `schema.yml`, la prima chiave rappresenta un nome del modello. È possibile specificare più modelli, ciascuno con un insieme di colonne. Secondo la sintassi YAML, le chiavi terminano con i due punti e la struttura è specificata mediante indentazione (uno o più spazi, ma non tab).

Un modello può avere attributi speciali, tra cui la `tableName` (il nome della tabella del database relativa al modello). Se non si specifica la `tableName` per un modello, Doctrine lo crea facendo una versione con sottolineatura del nome del modello. 

>**TIP**
>La convenzione della sottolineatura aggiunge sottolineature tra le parole e utilizza solo caratteri minuscoli. Le versioni sottolineate predefinite di `Articolo` e `Commento` sono `articolo` e `commento`.

Un modello contiene colonne. Il valore della colonna può essere definito in due modi diversi:

  * Se si definisce solo un attributo, è il tipo della colonna. Symfony interpreta i tipi di colonne più comuni: `boolean`, `integer`, `float`, `date`, `string(size)`, `clob` (che ad esempio in MySQL è convertito in `text`) e così via.

  * Se si ha bisogno di definire altri attributi per le colonne (come il valore predefinito, quello richiesto, ecc.), bisogna scrivere gli attributi della colonna come una coppia di `chiave: valore`. Questa sintassi estesa dello schema è descritta più avanti in questo capitolo.

I modelli possono anche contenere chiavi esterne esplicite e indici. Per saperne di più fare riferimento alla sezione "Sintassi estesa dello schema" che si trova più avanti in questo capitolo.

Le classi del modello
---------------------

Lo schema è usato per costruire le classi del modello nello strato ORM. Per risparmiare tempo di esecuzione, queste classi sono generate con un task a riga di comando chiamato `doctrine:build-model`.

    $ php symfony doctrine:build-model

>**TIP**
>Dopo aver generato il modello, bisogna ricordarsi di cancellare la cache interna di symfony con `php symfony cc` in modo che symfony possa trovare i nuovi modelli creati.

La digitazione del comando lancerà l'analisi dello schema e la generazione delle classe base del modello dei dati nella cartella `lib/model/doctrine/base` del progetto:

  * `BaseArticolo.php`
  * `BaseCommento.php`

Inoltre nella cartella `lib/model/doctrine` verranno create le classi personalizzate del modello :

  * `Articolo.php`
  * `ArticoloTable.php`
  * `Commento.php`
  * `CommentoTable.php`

Sono stati definiti solo due modelli e ci si ritrova con sei file. Non c'è nulla di sbagliato, ma questo risultato merita una ulteriore spiegazione.

### Classi base e personalizzate

Perché tenere due versioni dello stesso modello a oggetti dei dati, in due diverse cartelle?

Probabilmente si avrà bisogno di aggiungere metodi e proprietà agli oggetti del modello (pensiamo al metodo `getNome()` nel Listato 8-1). Mano a mano che il progetto si evolve, si vorranno aggiungere tabelle o colonne. Ogni volta che si cambia il file `schema.yml`, bisogna rigenerare le classi del modello a oggetti facendo una nuova chiamata di doctrine:build-model. Se i metodi personalizzati venissero scritti nelle classi generate, sarebbero cancellati dopo ogni rigenerazione.

Le classi `Base` presenti nella cartella `lib/model/doctrine/base/` sono le uniche effettivamente generate dallo schema. Non bisogna mai modificarle, dal momento che nuove ricostruzioni del modello cancelleranno completamente questi file.

D'altra parte, le classi di oggetti personalizzati presenti nella cartella `lib/model/doctrine`, di fatto ereditano da quelle Base`. Quando il task `doctrine:build-model` è chiamato su un modello esistente, queste classi non vengono modificate. Quindi questo è il posto dove aggiungere i metodi personalizzati.

Il Listato 8-4 mostra un esempio di una classe personalizzata del modello, così come viene creata dopo la prima chiamata del task `doctrine:build-model`.

Listato 8-4 - Esempio di file di una classe del modello, in `lib/model/doctrine/Article.php`

    [php]
    class Articolo extends BaseArticolo
    {
    }

La classe Articolo eredita ogni cosa della classe `BaseArticolo`, ma modifiche nello schema non hanno effetti su Articolo.
	
Il meccanismo delle classi personalizzate che estendono delle classi base consente di iniziare lo sviluppo, anche senza conoscere il modello relazionale finale del database. La relativa struttura dei file rende il modello sia personalizzabile che estendibile.

### Classi di oggetti e tabelle

`Articolo` e `Commento` sono classi di oggetti che rappresentano un record nel database. Forniscono accesso alle colonne di un record e ai relativi record. Questo significa che si è in grado di sapere il titolo di un articolo chiamando un metodo di un oggetto Articolo, come nell'esempio mostrato nel Listato 8-5.

Listato 8-5 - Nella classe dell'oggetto sono disponibili dei metodi getter per tutte le colonne del record

    [php]
    $articolo = new Articolo();
    // ...
    $titolo = $articolo->getTitolo();

`ArticoloTable` e `CommentoTable` sono classi per le tabelle; cioè classi che contengono metodi pubblici che permettono di operare sulle tabelle. Essi forniscono un modo per recuperare i record dalle tabelle. I loro metodi di solito restituiscono un oggetto o un insieme di oggetti della relativa classe dell'oggetto, come mostrato nel Listato 8-6

Listato 8-6 - Nella classe della tabella sono disponibili dei metodi pubblici per recuperare i record

    [php]
    // $articolo è una istanza della classe Articolo
    $articolo = Doctrine_Core::getTable('Articolo')->find(123);

Accesso ai dati
---------------

In symfony si accede ai dati attraverso oggetti. Se si è abituati al modello relazionale e ad usare l'SQL per recuperare e modificare i dati, i metodi a oggetti del modello potranno sembrare complicati inizialmente. Ma una volta che si prova la potenza dell'accesso ai dati tramite interfaccia orientata agli oggetti, probabilmente ci si troverà a proprio agio.

Ma prima, vediamo di essere sicuri di condividere lo stesso vocabolario. Il modello dei dati relazionale e a oggetti utilizza concetti simili, ma ciascuno ha una propria nomenclatura:

Relazionale   | Orientato agli oggetti
------------- | ----------------------
Tabella       | Classe
Riga, record  | Oggetto
Campo, colonna| Proprietà

### Recuperare il valore della colonna

Quando symfony costruisce il modello, crea una classe base di un oggetto per ciascuno dei modelli definiti nel file `schema.yml`. Ciascuna di queste classi è dotata di accessor e mutator predefiniti generati in base alle definizioni della colonna: i metodi `new`, `getXXX()` e `setXXX()` aiutano a creare oggetti e forniscono accesso alle proprietà dell'oggetto, come mostrato nel Listato 8-7.

Listato 8-7 - Metodi generati nella classe dell'oggetto

    [php]
    $articolo = new Articolo();
    $articolo->setTitolo('Il mio primo articolo');
    $articolo->setContenuto("Questo è il mio primo articolo.\n Spero che possa piacere!");

    $titolo   = $articolo->getTitolo();
    $contenuto = $articolo->getContenuto();

>**NOTE**
>La classe generata per l'oggetto è chiamata `Articolo` ma nel database il dato è memorizzato in una tabella chiamata `blog_articolo`. Se nello schema `tableName` non fosse stato definito, la classe sarebbe stata chiamata `articolo`. I metodi accessor e mutator usano una variante camelCase dei nomi delle colonne, quindi il metodo `getTitolo()` recupera il valore della colonna `titolo`.

Per impostare molti campi in una sola volta, si può usare il metodo `fromArray()`, disponibile anche per ciascuna classe dell'oggetto, come mostrato nel Listato 8-8.

Listato 8-8 - Il metodo `fromArray()` è un setter multiplo

    [php]
    $articolo->fromArray(array(
      'Titolo'   => 'Il mio primo articolo',
      'Contenuto' => 'Questo è il mio primo articolo.\n Spero che possa piacere!',
    ));

### Recuperare i record correlati

La colonna `articolo_id` della tabella `blog_commento` definisce implicitamente una chiave esterna alla tabella `blog_articolo`. Ogni commento è correlato a un articolo e un articolo può avere molti commenti. Le classi generate contengono cinque metodi per tradurre queste relazioni in una modalità orientata agli oggetti. Sono i seguenti:

  * `$commento->getArticolo()`: Per ottenere gli oggetti relativi ad `Articolo`
  * `$commento->getArticoloId()`: Per ottenere l'ID del relativo oggetto `Articolo`
  * `$commento->setArticolo($articolo)`: Per definire il relativo oggetto `Articolo`
  * `$commento->setArticoloId($id)`: Per definire il relativo oggetto `Articolo` da un ID
  * `$articolo->getCommenti()`: Per ottenere i relativi oggetti `Commento`

I metodi `getArticoloId()` e `setArticoloId()` mostrano che si può considerare la colonna `articolo_id` come una normale colonna e impostare le relazioni a mano, ma non è una cosa molto utile. Il vantaggio di un approccio orientato agli oggetti è molto più evidente nei tre altri metodi. Il Listato 8-9 mostra come usare i metodi setter generati.

Listato 8-9 - Le chiavi esterne sono tradotte in un setter speciale

    [php]
    $commento = new Commento();
    $commento->setAutore('Fabrizio');
    $commento->setContenuto('Fantastico, è il miglior articolo che ho letto!');

    // Collega questo commento al precedente oggetto $articolo
    $commento->setArticolo($articolo);

    // Sintassi alternativa
    // Ha senso solo se l'oggetto è stato già salvato nel database
    $commento->setArticoloId($articolo->getId());

Il Listato 8-10 mostra come usare i metodi getter generati. Mostra anche come concatenare le chiamate di metodi sugli oggetti del modello.

Listato 8-10 - Le chiavi esterne sono tradotte in getter speciali

    [php]
    // Relazione molti a uno
    echo $commento->getArticolo()->getTitolo();
     => Il mio primo articolo
    echo $commento->getArticolo()->getContenuto();
     => Questo è il mio primo articolo.
	    Spero che possa piacere!

    // Relazione uno a molti
    $commenti = $articolo->getCommenti();

Il metodo `getArticolo()` restituisce un oggetto della classe `Articolo`, che trae beneficio dall'accessor `getTitolo()`. Questa è una operazione migliore rispetto a fare la join da soli, la quale può necessitare di qualche riga di codice in più (partendo dalla chiamata  `$commento->getArticoloId()`).

La variabile `$commenti` nel Listato 8-10 contiene un array di oggetti della classe `Commento`. Si può visualizzare il primo con `$commenti[0]` o iterare sulla collezione con `foreach ($commenti as $commento)`.

### Salvare e cancellare i dati

Chiamando il costruttore `new`, viene creato un nuovo oggetto, ma non un nuovo record nella tabella `blog_articolo`. La modifica dell'oggetto non ha effetto sul database. Per salvare i dati nel database, bisogna chiamare il metodo `save()` dell'oggetto.

    [php]
    $articolo->save();

L'ORM riesce a riconoscere le relazioni tra oggetti, quindi salvando l'oggetto `$articolo` viene anche salvato l'oggetto `$commento` ad esso collegato. L'ORM sa anche se l'oggetto salvato ha una controparte esistente nel database , quindi la chiamata `save()` a volte è tradotta in SQL con `INSERT` e a volte con `UPDATE`. La chiave primaria è impostata automaticamente dal metodo `save()`, quindi dopo aver salvato, si può recuperare la nuova chiave primaria con `$articolo->getId()`.

>**TIP**
>Si può controllare se un oggetto è nuovo, chiamando `isNew()`. Se si vuole sapere se un oggetto è stato modificato per eventualmente evitare il salvataggio, basta chiamare il metodo `isModified()`.

Se si leggono commenti ai propri articoli, si potrebbe cambiare idea circa l'opportunità di pubblicarli su Internet. E se non si apprezza l'ironia dei commentatori dell'articolo, è possibile eliminare facilmente i commenti con il metodo `delete()`, come mostrato nel Listato 8-11.

Listato 8-11 - Cancellare i record dal database con il metodo `delete()` sul relativo oggetto

    [php]
    foreach ($articolo->getCommenti() as $commento)
    {
      $commento->delete();
    }

### Recuperare i record tramite chiave primaria

Se si conosce la chiave primaria di un certo record usare il metodo `find()` della classe della tabella per recuperare il relativo oggetto.

    [php]
    $articolo = Doctrine_Core::getTable('Articolo')->find(7);

Il file `schema.yml` definisce il campo `id` come chiave primaria della tabella `blog_articolo`, quindi questo comando restituirà l'articolo che ha `id` 7. Essendo che è stata utilizzata la chiave primaria, sappiamo che verrà restituito solo un record; la variabile `$articolo` contiene un oggetto della classe `Articolo`.

In alcuni casi, una chiave primaria piò essere costituita da più di una colonna. Per gestire questi casi, il metodo `find()` accetta parametri multipli, uno per ciascuna chiave primaria di colonna.

### Recuperare i record tramite Doctrine_Query

Quando si vuole recuperare più di un record, bisogna chiamare il metodo `createQuery()` della classe della tabella corrispondente agli oggetti che si vogliono recuperare. Ad esempio, per recuperare oggetti della classe `Articolo`, chiamare `Doctrine_Core::getTable('Articolo')->createQuery()->execute()`.

Il primo parametro del metodo `execute()` è un array di parametri, che è l'array di valori per sostituire tutti i segnaposto trovati nella query.

Una `Doctrine_Query` vuota restituisce tutti gli oggetti della classe. Ad esempio, il codice mostrato nel Listato 8-12 restituisce tutti gli articoli.

Listato 8-12 - Recuperare i record di Doctrine_Query con `createQuery()`--Query vuota

    [php]
    $q = Doctrine_Core::getTable('Articolo')->createQuery();
    $articoli = $q->execute();

    // Verrà generata la seguente query SQL
    SELECT b.id AS b__id, b.titolo AS b__titolo, b.contento AS b__contenuto, b.created_at AS b__created_at, b.updated_at AS b__updated_at FROM blog_articolo b

>**SIDEBAR**
>Idratazione
>
>La chiamata a `->execute()` è molto più potente di una semplice query SQL. Primo, l'SQL è ottimizzato per il DBMS che si è scelto. Secondo, ogni valore passato a `Doctrine_Query` è escapizzato prima di essere inserito nel codice SQL, il che previene i rischi di SQL injection. Terzo, il metodo restituisce un array di oggetti, piuttosto che un insieme di risultati. L'ORM crea e popola automaticamente gli oggetti basandosi sull'insieme dei risultati del database. Questo processo è chiamato idratazione.

Per selezionare gli oggetti in modo più complesso, è necessario un qualcosa di equivalente a WHERE, ORDER BY, GROUP BY e alle altre istruzioni SQL. L'oggetto `Doctrine_Query` ha metodi e parametri per tutte queste condizioni. Ad esempio, per recuperare tutti i commenti scritti da Fabrizio, ordinati per data, fare una `Doctrine_Query` come mostrato nel Listato 8-13.

Listato 8-13 - Recuperare i record di `Doctrine_Query` con `createQuery()`--Doctrine_Query con condizioni

    [php]
    $q = Doctrine_Core::getTable('Commento')
      ->createQuery('c')
      ->where('c.autore = ?', 'Fabrizio')
      ->orderBy('c.created_at ASC');
    $commenti = $q->execute();

    // Verrà generata la seguente query SQL
    SELECT b.id AS b__id, b.articolo_id AS b__articolo_id, b.autore AS b__autore, b.contenuto AS b__contenuto, b.created_at AS b__created_at, b.updated_at AS b__updated_at FROM blog_commento b WHERE (b.autore = ?) ORDER BY b.created_at ASC

La Tabella 8-1 confronta la sintassi SQL con la sintassi dell'oggetto `Doctrine_Query`.

Tabella 8-1 - Sintassi SQL e criteri dell'oggetto

SQL                                                          | Criteri
------------------------------------------------------------ | -----------------------------------------------
`WHERE column = value`                                       | `->where('acolumn = ?', 'value')`
**Altre parole chiave SQL**                                  |
`ORDER BY column ASC`                                        | `->orderBy('acolumn ASC')`
`ORDER BY column DESC`                                       | `->addOrderBy('acolumn DESC')`
`LIMIT limit`                                                | `->limit(limit)`
`OFFSET offset`                                              | `->offset(offset) `
`FROM table1 LEFT JOIN table2 ON table1.col1 = table2.col2`  | `->leftJoin('a.Model2 m')`
`FROM table1 INNER JOIN table2 ON table1.col1 = table2.col2` | `->innerJoin('a.Model2 m')`

Il Listato 8-14 mostra un'altro esempio dell'utilizzo di `Doctrine_Query` con condizioni multiple. Recupera tutti i commenti di Fabrizio sugli articoli contenenti la parola "piacere" ordinati per data.

Listato 8-14 - Altro esempio di recupero dei record di Doctrine_Query con `createQuery()`--Doctrine_Query con condizioni

    [php]
    $q = Doctrine_Core::getTable('Commento')
      ->createQuery('c')
      ->where('c.autore = ?', 'Fabrizio')
      ->leftJoin('c.Articolo a')
      ->andWhere('a.contenuto LIKE ?', '%piacere%')
      ->orderBy('c.created_at ASC');
    $comments = $q->execute();

    // Verrà generata la seguente query SQL
    SELECT b.id AS b__id, b.articolo_id AS b__articolo_id, b.autore AS b__autore, b.contenuto AS b__contenuto, b.created_at AS b__created_at, b.updated_at AS b__updated_at, b2.id AS b2__id, b2.titolo AS b2__titolo, b2.contenuto AS b2__contenuto, b2.created_at AS b2__created_at, b2.updated_at AS b2__updated_at FROM blog_commento b LEFT JOIN blog_articolo b2 ON b.articolo_id = b2.id WHERE (b.autore = ? AND b2.contenuto LIKE ?) ORDER BY b.created_at ASC

Così come l'SQL è un linguaggio che permette di costruire query molto complesse, l'oggetto Doctrine_Query può gestire condizioni con ogni livello di complessità. Ma dal momento che molti sviluppatori prima pensano in SQL e poi traducono la condizione nella logica orientata agli oggetti, l'oggetto `Doctrine_Query` potrebbe all'inizio essere difficile da comprendere. Il miglior modo per impararlo è guardarsi esempi e applicazioni di esempio. Il sito web del progetto symfony, è pieno di esempi di costruzioni di `Doctrine_Query` che potranno essere di aiuto.
	
Ogni istanza di `Doctrine_Query` ha un metodo `count()`, che semplicemente conta il numero dei record per la query e restituisce un intero. Poiché non c'è un oggetto da restituire, il processo di idratazione in questo caso non avviene e per questo motivo il metodo `count()` è più veloce di `execute()`.

Le classi della tabella forniscono anche i metodi `findAll()`, `findBy*()` e `findOneBy*()`, che sono scorciatoie per la creazione di istanze `Doctrine_Query`, l'esecuzione di esse e la restituzione dei risultati.

Infine, se si vuole che venga restituito solo il primo oggetto, sostituire la chiamata di `execute()` con `fetchOne()`. Questo può essere il caso quando si sa che un `Doctrine_Query` restituirà un solo risultato e il vantaggio è che questo metodo restituisce un oggetto piuttosto che un array di oggetti.

>**TIP**
>Quando una query `execute()` restituisce un grosso numero di risultati, si potrebbe voler visualizzare solo un sottoinsieme di questi nella risposta. Symfony fornisce una classe per la paginazione chiamata `sfDoctrinePager`, che automatizza la paginazione dei risultati.

### Utilizzo di query SQL raw

A volte non si vogliono recuperare oggetti, ma solo risultati sintetici calcolati dal database. Ad esempio, per ottenere l'ultima data di creazione tra tutti gli articoli, non ha senso recuperare tutti gli articoli e ciclare sull'array. Si preferirà chiedere al database di restituire solo il risultato, perché in questo modo verrà saltato il processo di idratazione.

D'altra parte, non si vogliono chiamare direttamente i comandi PHP per la gestione del database, perché in questo modo si perderebbero i benefici dell'astrazione del database. Questo significa che bisogna bypassare l'ORM (Doctrine), ma non l'astrazione del database (PDO).

Interrogare il database con PHP Data Objects (PDO), richiede di eseguire le seguenti operazioni:

  1. Ottenere una connessione al database.
  2. Costruire una stringa per la query.
  3. Creare una dichiarazione fuori da essa.
  4. Iterare sull'insieme dei risultati che provengono dall'esecuzione dell'istruzione.

Se questo dovesse essere poco chiaro, il codice del Listato 8-15 dovrebbe essere più esplicito

Listato 8-15 - Query SQL personalizzate con PDO

    [php]
    $connection = Doctrine_Manager::connection();
    $query = 'SELECT MAX(created_at) AS max FROM blog_articolo';
    $statement = $connection->execute($query);
    $statement->execute();
    $resultset = $statement->fetch(PDO::FETCH_OBJ);
    $max = $resultset->max;

Così come per Doctrine, le query PDO possono sembrare difficili all'inizio. Ancora una volta, gli esempi di applicazioni esistenti e i tutorial, mostreranno il modo più corretto per utilizzarle.

>**CAUTION**
>Se si è tentati di saltare questo processo e accedere direttamente al database, si rischia di perdere la sicurezza e l'astrazione fornite da Doctrine. Farlo con Doctrine è più lungo, ma costringe a usare buone pratiche che garantiscono prestazioni, portabilità e sicurezza per l'applicazione. Ciò è particolarmente vero per le query che contengono parametri provenienti da una fonte non attendibile (ad esempio, un utente Internet). Doctrine fa tutte le necessarie escapizzazioni e protegge i dati. L'accesso al database mette direttamente a rischio di attacchi di tipo SQL injection.

### Uso di colonne speciali per le date

Generalmente, quando una tabella ha una colonna chiamata `created_at`, è usata per memorizzare un timestamp della data di creazione di un record. Stessa cosa per le colonne `updated_at` (aggiornato_il), che vengono aggiornate con il valore del tempo corrente, ogni volta che il record stesso viene aggiornato

La buona notizia è che Doctrine ha un comportamento `Timestampable` che gestirà questi aggiornamenti per noi. Non è necessario impostare manualmente le colonne `created_at` e `updated_at`; verranno aggiornate automaticamente , come mostrato nel Listato 8-16.

Listato 8-16 - Le colonne `created_at` e `updated_at` Columns sono gestite automaticamente

    [php]
    $commento = new Commento();
    $commento->setAutore('Fabrizio');
    $commento->save();

    // Mostra la creazione della data
    echo $commento->getCreatedAt();
      => [data dell'operazione di INSERT nel database]

>**SIDEBAR**
>Rifattorizzazione nello strato dei dati
>
>Quando si sviluppa un progetto symfony, spesso si inizia scrivendo il codice della logica di dominio nelle azioni. Ma le query sul database e la manipolazione del modello non devono essere messi nel livello del controllore. Tutta la logica relativa ai dati dovrebbe essere spostata al livello di modello. Ogni volta che si deve fare la stessa richiesta in più di un posto nelle azioni, è meglio pensare di trasferire il relativo codice al modello. Aiuta a mantenere le azioni brevi e leggibili.
>
>Per esempio, si può immaginare in un blog il codice necessario per recuperare i dieci articoli più popolari per un dato tag (passato come parametro della request). Questo codice non deve essere messo in una azione, ma nel modello. In effetti, se si ha bisogno di visualizzare questo elenco in un template, l'azione dovrebbe apparire così:
>
>     [php]
>     public function executeMostraArticoliPopolariPerTag($request)
>     {
>       $tag = Doctrine_Core::getTable('Tag')->findOneByName($request->getParameter('tag'));
>       $this->forward404Unless($tag);
>       $this->articoli = $tag->getArticoliPopolari(10);
>     }
>
>L'azione crea un oggetto  della classe `Tag` dal parametro della request. Tutto il codice necessario per interrogare il database si trova nel metodo `getArticoliPopolari` di questa classe. Rende l'azione più leggibile e il codice del modello può facilmente venire riutilizzato in un'altra azione.
>
>Spostare il codice in un posto più appropriato è una delle tecniche della rifattorizzazione. Se la si fa spesso, il codice sarà semplice da mantenere e da comprendere da parte di altri sviluppatori. Una buona regola per capire quando fare refactoring nello strato dei dati, è che il codice di una azione raramente deve contenere più di dieci righe di codice PHP.

Connessioni al database
---------------------------

Il modello del dati è indipendente dal database usato, ma dovrà sicuramente utilizzare un database. Le minime informazioni richieste da symfony per inviare richieste al database del progetto sono il nome, le credenziali e il tipo di database. Queste impostazioni per la connessione possono essere configurate passando il nome della sorgente dati (DSN, data source name) al task `configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=blog" root mYsEcret

Le impostazioni della connessione sono dipendenti dall'ambiente. Si possono definire impostazioni differenti per gli ambienti `prod`, `dev` e `test`, o ogni altro ambiente dell'applicazione, usando l'opzione `env`:

    $ php symfony configure:database --env=dev "mysql:host=localhost;dbname=blog_dev" root mYsEcret

La configurazione può anche essere sovrascritta per applicazione. Ad esempio, si può usare questo approccio per avere politiche di sicurezza diverse per l'applicazione front-end e back-end e per definire diversi utenti del database con differenti privilegi, in questo modo:

    $ php symfony configure:database --app=frontend "mysql:host=localhost;dbname=blog" root mYsEcret

Per ciascun ambiente, si possono definire più connessioni. Il nome della connessione predefinita è `doctrine`. L'opzione `name` permette di creare un'altra connessione:

    $ php symfony configure:database --name=main "mysql:host=localhost;dbname=example" root mYsEcret

Le impostazioni per la connessione si possono anche impostare manualmente nel file `databases.yml` presente nella cartella `config/`. Il Listato 8-17 mostra un esempio di questo file e il Listato 8-18 mostra lo stess esempio con la notazione estesa.

Listato 8-17 - Impostazioni manuali della connessione al database

    [yml]
    all:
      doctrine:
        class:          sfDoctrineDatabase
        param:
          dsn:          mysql://login:passwd@localhost/blog

Listato 8-18 - Esempio di impostazioni per la connessione al database, in `mioprogetto/config/databases.yml`

    [yml]
    prod:
      doctrine:
        param:
          dsn:        mysql:dbname=blog;host=localhost
          username:   login
          password:   passwd
          attributes:
            quote_identifier: false
            use_native_enum: false
            validate: all
            idxname_format: %s_idx
            seqname_format: %s_seq
            tblname_format: %s

Per sovrascrivere la configurazione per applicazione, è necessario modificare il file della specifica applicazione, ad esempio `apps/frontend/config/databases.yml`.
			
Se si vuole usare il database SQLite, il parametro `dsn` deve essere impostato con il percorso al file del database. Ad esempio, se il database del blog è in `data/blog.db`, il file `databases.yml` apparirà come nel Listato 8-19.

Listato 8-19 - Impostazioni per la connessione al database SQLite. Usare un percorso file come host

    [yml]
    all:
      doctrine:
        class:      sfDoctrineDatabase
        param:
          dsn:      sqlite:///%SF_DATA_DIR%/blog.db

Estendere il modello
--------------------

I metodi generati del modello sono utili ma a volte non sufficienti. Non appena si implementa la propria business logic, è necessario estenderli, aggiungendo nuovi metodi o sosvrascrivendo quelli esistenti.

### Aggiungere nuovi metodi

Si possono aggiungere nuovi metodi alle classi vuote del modello generate nella cartella `lib/model/doctrine`. Usare `$this` per chiamare i metodi dell'oggetto corrente e usare `self::` per chiamare metodi statici della classe corrente. Ricordarsi che le classi personalizzate ereditano i metodi dalle classi `Base` presenti nella cartella `lib/model/doctrine/base`.

Ad esempio, per l'oggetto `Articolo` generato basandosi sul Listato 8-3, si può aggiungere un metodo magico `__toString()` in modo che faccendo l'echo di un oggetto della classe `Articolo` venga visualizzato il suo titolo, come mostrato nel Listato 8-20.

Listato 8-20 - Personalizzazione del modello, in `lib/model/doctrine/Articolo.php`

    [php]
    class Articolo extends BaseArticolo
    {
      public function __toString()
      {
        return $this->getTitolo();  // getTitolo() è ereditato da BaseArticolo
      }
    }

Si possono anche estendere le classi della tabella, ad esempio per aggiungere un metodo che restituisca tutti gli articoli ordinati per data di creazione, come mostrato nel Listato 8-21.

Listato 8-21 - Personalizzazione del modello, in `lib/model/doctrine/ArticoloTable.php`

    [php]
    class ArticoloTable extends BaseArticoloTable
    {
      public function getTuttoOrdinatoPerData()
      {
        $q = $this->createQuery('a')
          ->orderBy('a.created_at ASC');

        return $q->execute();
      }
    }

I nuovi metodi sono disponibili nello stesso modo di quelli generati, come mostrato nel Listato 8-22.

Listato 8-22 - Usare i metodi personalizzati dei modelli è come usare i metodi generati

    [php]
    $articoli = Doctrine_Core::getTable('Articolo')->getTuttoOrdinatoPerData();
    foreach ($articoli as $articolo)
    {
      echo $articolo;      // Chiamerà il metodo magico __toString()
    }

### Sovrascrivere i metodi esistenti

Se alcuni dei metodi generati nelle classi `Base` non si adattano alle proprie esigenze, si può sovrascriverle nella classe personalizzata. Basta fare in modo di utilizzare la stessa firma nel metodo (cioè lo stesso numero di argomenti).

Ad esempio, il metodo `$article->getCommenti()` restituisce una collezione di oggetti `Commento`, in nessun ordine particolare. Se si vogliono avere i risultati ordinati per data di creazione, con gli ultimi commenti messi all'inizio, allora bisogna creare il metodo `getCommenti()` come mostrato nel Listato 8-23.

Listato 8-23 - Sovrascrivere i metodi esistenti del modello, in `lib/model/Articolo.php`

    [php]
    public function getCommenti()
    {
      $q = Doctrine_Core::getTable('Commento')
        ->createQuery('c')
        ->where('c.articolo_id = ?', $this->getId())
        ->orderBy('c.created_at ASC');

      return $q->execute();
    }

### Usare i comportamenti per il modello

Alcune modifiche al modello sono generiche e possono essere riusate. Ad esempio, i metodi che rendono l'oggetto del modello ordinabile e il lock ottimistico per prevenire conflitti nel salvataggio di oggetti concorrenti, sono estensioni generiche che possono essere aggiunte a molte classi.

Symfony impacchetta queste estensioni nei comportamenti. I comportamenti sono classi esterne che forniscono metodi aggiuntivi alle classi del modello. Le classi del modello
hanno già dei ganci e symfony sa come estenderle.

Per abilitare i comportamenti nelle classi del modello, è necessario modificare lo schema e usare l'opzione `actAs`:

    [yml]
    Articolo:
      actAs: [Timestampable, Sluggable]
      tableName: blog_articolo
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        titolo:   string(255)
        contenuto: clob

Dopo aver rigenerato i modelli, il modello `Articolo` ha una colonna slug che è automaticamente impostata con una stringa "amichevole" per le url basata sul titolo.
		
Alcuni dei comportamenti disponibili per Doctrine sono:

 * Timestampable
 * Sluggable
 * SoftDelete
 * Searchable
 * I18n
 * Versionable
 * NestedSet

Sintassi estesa per lo schema
-----------------------------

Un file `schema.yml` può essere semplice, come mostrato nel Listato 8-3. Ma i modelli relazionali spesso sono complessi. Questo è il motivo per il quale lo schema ha una sintassi estesa capace di gestire pressoché ogni caso.

### Attributi

Connessioni e tabelle possono avere attributi specifici, come mostrato nel Listato 8-24. Sono assegnati tramite una chiave `_attributes`.

Listato 8-24 - Attributi per le impostazioni del modello

    [yml]
    Articolo:
      attributes:
        export: tables
        validate: none

L'attributo `export` controlla quale SQL è esportato al database quando vengono create le tabelle per questo modello. Utilizzando il valore `tables` viene solo esportata la struttura della tabella e non le chiavi esterne, gli indici, ecc.

Le tabelle che contengono contenuto localizzato (cioè, diverse versioni del contenuto, in una tabella correlata per l'internazionalizzazione) devono utilizzare il comportamento I18n (si veda il Capitolo 13 per dettagli), come mostrato nel Listato 8-25.

Listato 8-25 - Il comportamento I18n

    [yml]
    Articolo:
      actAs:
        I18n:
          fields: [titolo, contenuto]

>**SIDEBAR**
>Trattare con più schemi
>
>Si può avere più di uno schema per applicazione. Symfony considererà tutti i file che finiscono con`.yml` presenti nella cartella `config/doctrine`. Se l'applicazione ha molti modelli, o se alcuni modelli non condividono la stessa connessione, si potrà trovare questo approccio molto utile.
>
>Si considerino questi due schemi:
>
>     [yml]
>     // In config/doctrine/business-schema.yml
>     Articolo:
>       id:
>         type: integer
>         primary: true
>         autoincrement: true
>       title: string(50)
>
>     // In config/doctrine/stats-schema.yml
>     Hit:
>       actAs: [Timestampable]
>       columns:
>         id:
>           type: integer
>           primary: true
>           autoincrement: true
>         resource: string(100)
>
>
>Entrambi gli schemi condividono la stessa connessione (`doctrine`) e le classi `Articolo` e `Hit` verranno generate sotto la stessa cartella `lib/model/doctrine`. Tutto avviene come se si fosse scritto un solo schema.
>
>Si possono anche avere schemi diversi che usano connessioni diverse (ad esempio, `doctrine` e `doctrine_bis`, da definire `databases.yml`) e associarli a questa connessione:
>
>
>     [yml]
>     // In config/doctrine/business-schema.yml
>     Articolo:
>       connection: doctrine
>       id:
>         type: integer
>         primary: true
>         autoincrement: true
>       titolo: string(50)
>
>     // In config/doctrine/stats-schema.yml
>     Hit:
>       connection: doctrine_bis
>       actAs: [Timestampable]
>       columns:
>         id:
>           type: integer
>           primary: true
>           autoincrement: true
>         resource: string(100)
>
>
>Molte applicazioni usano più di uno schema. In particolare, alcuni plug-in hanno il loro proprio schema per evitare problemi con quelli di altre classi (vedere il capitolo 17 per maggiori dettagli).

### Dettagli delle colonne

La sintassi di base permette di definire il tipo con una delle parole chiave per i tipi. Il Listato 8-26 mostra queste scelte.

Listato  8-26 - Attributi base della colonna

    [yml]
    Articolo:
      columns:
        titolo: string(50)  # Specifica il tipo e la lunghezza

Ma si possono definire molte altre informazioni per una colonna. Per farlo, c'è bisogno di impostare le definizioni della colonna come un array associativo, come mostrato nel Listato 8-27.

Listato 8-27 - Attributi complessi per le colonne

    [yml]
    Articolo:
      columns:
        id:       { type: integer, notnull: true, primary: true, autoincrement: true }
        nome:     { type: string(50), default: foobar }
        group_id: { type: integer }

I parametri delle colonne sono i seguenti:

  * `type`: tipo della colonna. Le scelte sono `boolean`, `integer`, `double`, `float`, `decimal`, `string(size)`, `date`, `time`, `timestamp`, `blob` e `clob`.
  * `notnull`: booleano. Impostarlo a `true` se si vuole che la colonna sia richiesta.
  * `length`: la dimensione o la lunghezza del campo per i tipi che la supportano
  * `scale`: numero di cifre decimali per l'utilizzo con il tipo di dato decimale (deve essere specificata anche la dimensione)
  * `default`: valore predefinito.
  * `primary`: booleano. Impostarlo a `true` per le chiavi primarie.
  * `autoincrement`: booleano. Impostarlo a `true` per le colonne di tipo `integer` che necessitano di prendere un valore auto incrementale.
  * `sequence`: nome della sequenza per i database che usano le sequenze per le colonne auto incrementali (ad esempio, PostgreSQL e Oracle).
  * `unique`: booleano. Impostarlo a `true` se si vuole che la colonna sia unica.

### Relazioni

In un modello, è possibile specificare relazioni con chiavi esterne sotto la chiave `relations`. Lo schema nel Listato 8-28 creerà una chiave esterna sulla colonna `user_id`, collegando la colonna `id` nella tabella `blog_user`.

Listato 8-28 - Sintassi alternativa per la chiave esterna

    [yml]
    Articolo:
      actAs: [Timestampable]
      tableName: blog_articolo
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        titolo:   string(255)
        contenuto: clob
        user_id: integer
      relations:
        User:
          onDelete: CASCADE
          foreignAlias: Articles

### Indexes

Si possono aggiungere indici in un modello, sotto la chiave `indexes:`. Se si vogliono definire indici univoci, bisogna usare la sintassi `type: unique`. Per le colonne che richiedono una dimensione, perché sono colonne di testo, la dimensione dell'indice è specificata nello stesso modo della lunghezza della colonna usando le parentesi. Il Listato 8-30 mostra la sintassi alternativa per gli indici.

Listato 8-30 - Indici e indici univoci

    [yml]
    Articolo:
      actAs: [Timestampable]
      tableName: blog_articolo
      columns:
        id:
          type: integer
          primary: true
          autoincrement: true
        titolo:   string(255)
        contenuto: clob
        user_id: integer
      relations:
        User:
          onDelete: CASCADE
          foreignAlias: Articles
      indexes:
        my_index:
          fields:
            title:
              length: 10
            user_id: []
        my_other_index:
          type: unique
          fields:
            created_at

### Tabelle I18n

Symfony supporta l'internazionalizzazione dei contenuti tramite tabelle dedicate. Questo significa che quando si ha un contenuto da internazionalizzare, viene memorizzato in due tabelle separate: una per le colonne che non cambiano e una per le colonne da internazionalizzare.

Listato 8-33 - Meccanismo I18n

    [yml]
    DbGroup:
      actAs:
        I18n:
          fields: [nome]
      columns:
        nome: string(50)

### Comportamenti

I comportamenti sono modificatori dei modelli forniti da plug-in, che aggiungono nuove capacità alle classi di Doctrine. Il capitolo 17 parla più approfonditamente dei comportamenti. I comportamenti si possono definire nello schema, elencandoli per ciascuna tabella, insieme con i loro parametri, sotto la chiave `actAs`. Il Listato 8-34 fornisce un esempio estendendo la classe `Articolo` con il comportamento `Sluggable`.

Listato 8-34 - Dichiarazione dei comportamenti

    [yml]
    Articolo:
      actAs: [Sluggable]
      # ...

Non creare il modello due volte
-------------------------------

Lo svantaggio nell'utilizzo di un ORM è che bisogna definire la struttura dati due volte: una per il database e una per il modello a oggetti. Per fortuna, symfony fornisce dei tool a riga di comando per generare l'uno basato sull'altro, in modo da evitare la duplicazione del lavoro.

### Creare l'SQL della struttura di un database basandosi su uno schema esistente

Se si inizia l'applicazione scrivendo il file `schema.yml`, symfony può generare una query SQL che crea le tabelle direttamente dal modello YAML dei dati. Per generare la query, andare nella cartella radice del progetto e digitare:

    $ php symfony doctrine:build-sql

Verrà creato un file `schema.sql` in `mioprogetto/data/sql/`. Notare che il codice SQL generato sarà ottimizzato per il sistema di database definito in `databases.yml`.

Si può usare il file `schema.sql` direttamente per costruire le tabelle. Ad esempio, in MySQL, digitare:

    $ mysqladmin -u root -p create blog
    $ mysql -u root -p blog < data/sql/schema.sql

L'SQL generato è utile anche per ricostruire il database in un altro ambiente o per passare ad un altro DBMS.

>**TIP**
>La riga di comando offre anche un task per popolare il database con i dati caricati da un file di testo. Vedere il capitolo 16 per maggiori informazioni sul task `doctrine:data-load` e i file delle fixture in YAML.

### Generare un modello dei dati YAML da un database esistente

Symfony può usare Doctrine per generare un file `schema.yml` da un database esistente, grazie alla introspezione (la capacità dei database di determinare la struttura delle tabelle sulle quali stanno operando). Questo può essere particolarmente utile quando si fa reverse-engineering, oppure quando si preferisce lavorare sul database prima di lavorare sul modello a oggetti.

Per fare ciò, è necessario assicurarsi che il file `databases.yml` del progetto punti al database corretto e contenga tutte le informazioni per la connessione. Quindi lanciare il comando `doctrine:build-schema`:

    $ php symfony doctrine:build-schema

Dalla struttura del database viene generato un nuovo file `schema.yml` nella cartella `config/doctrine/`. Si può costruire il modello basato su questo schema.

Riepilogo
---------

Symfony usa Doctrine come ORM e PHP Data Objects per il livello di astrazione del database. Ciò significa che è necessario prima descrivere lo schema relazionale del database in YAML prima di generare le classi del modello a oggetti. Poi, in fase di runtime, utilizzare i metodi dell'oggetto e le classi delle tabelle per recuperare informazioni su un record o un insieme di record. È possibile facilmente sovrascrivere ed estendere il modello aggiungendo metodi alle classi personalizzate. Le impostazioni di connessione sono definite in un file `databases.yml`, che può supportare più di una connessione. E la linea di comando contiene dei task speciali per evitare di duplicare la definizione della struttura. 

Lo strato del modello è il più complesso del framework symfony. Una delle ragioni di questa complessità è che la manipolazione dei dati è una questione intricata. I problemi di sicurezza correlati sono fondamentali per un sito web e non devono essere ignorati. Un'altra ragione è che symfony è più adatto ad applicazioni di medio-grandi dimensioni in contesto enterprise. In tali applicazioni, le automazioni fornite dal modello di symfony possono davvero rappresentare un guadagno di tempo che vale l'investimento per apprendere il suo funzionamento. 

Quindi non esitate nel dedicare un periodo di prova al modello a oggetti e ai metodi, per comprenderli pienamente. La solidità e la scalabilità delle applicazioni saranno la ricompensa.
