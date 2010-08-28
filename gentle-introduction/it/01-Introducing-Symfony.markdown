Capitolo 1 - introduzione a symfony
===================================

Cosa può fare symfony per voi? Cosa occorre per usarlo? Questo capitolo risponde
a queste domande.

Symfony in breve
----------------

Un framework snellisce lo sviluppo di un'applicazione, automatizzando molti degli
schemi impiegati per un dato scopo. Un framework inoltre aggiunge struttura al
codice, spingendo lo sviluppatore a scrivere codice migliore, più leggibile e
più mantenibile. Infine, un framework rende la programmazione più facile,
perché impacchetta delle operazioni complesse in semplici istruzioni.

Symfony è un framework completo, disegnato per ottimizzare lo sviluppo di applicazioni
web tramite diverse caratteristiche chiave. Per chi inizia, esso separa le regole
logiche di un'applicazione web, automatizza dei compiti comuni, in modo che lo
sviluppatore possa concentrarsi interamente sulle specifiche dell'applicazione.
Il risultato finale di questi vantaggi è che non serve più reinventare la ruota
ogni volta che si costruisce una nuova applicazione!

Symfony è scritto interamente in PHP. È stato testato in diversi
[progetti](http://sf-to.org/dailymotion) del [mondo](http://sf-to.org/delicious)
[reale](http://sf-to.org/answers) ed è attualmente usato per siti commerciali
ad alto traffico. È compatibile con i database esistenti, inclusi MySQL, PostgreSQL,
Oracle e Microsoft SQL Server. Gira su piattaforme *nix e Windows. Iniziamo a
vedere più da vicino alcune sue caratteristiche.

### Caratteristiche di symfony

Symfony è stato creato per soddisfare i seguenti requisiti:

  * Facile da installare e configurare sulla maggior parte delle piattaforme (e garanzia
    di funzionamento su piattaforme standard *nix e Windows)
  * Indipendente dal database
  * Semplice da usare, in molti casi, ma abbastanza flessibile da adattarsi a casi
    complessi
  * Basato sulla premessa della convenzione sulla configurazione: lo sviluppatore deve
    configurare solo quello che non è convenzionale
  * Conforme alla maggior parte delle buone pratiche e agli schemi di progettazione
  * Pronto e adatto per le imprese con politiche e architetture informatiche esistenti e
    abbastanza stabile per progetti a lungo termine
  * Codice molto leggibile, con commenti basati su phpDocumentor, per una facile
    manutenzione
  * Facile da estendere, che consenta l'integrazione con librerie di terze parti

#### Caratteristiche automatizzate nei progetti web

La maggior parte delle caratteristiche comuni dei progetti web sono automatizzate in
symfony, come segue:

  * Il livello di internazionalizzazione consente la traduzione sia dei dati
    che delle interfacce, così come anche localizzazione dei contenuti.
  * La presentazione usa template e layout che possono essere costruiti da grafici HTML
    senza alcuna conoscenza del framework. Le funzioni helper riducono l'ammontare di
    codice da scrivere per la presentazione, incapsulando larghe porzioni di codice
    in semplici chiamate a funzioni.
  * I form supportano la validazione e la ripopolazione automatica, il che assicura una
    buona qualità dei dati nel database e una migliore esperienza dell'utente.
  * L'escaping dell'output protegge le applicazioni da attacchi tramite dati corrotti.
  * La gestione della cache riduce l'utilizzo di banda e il carico dei server.
  * L'autenticazione e le credenziali facilitano la creazione di sezioni riservate e la
    gestione della sicurezza degli utenti.
  * Le rotte e gli URL intelligenti rendono gli indirizzi delle pagine amichevoli per i
    motori di ricerca.
  * La gestione precostituita della posta elettronica consente alle applicazioni web di
    andare oltre le classiche interazioni col browser.
  * Gli elenchi sono più amichevoli, grazie alla paginazione, l'ordinamento e i filtri
    automatici.
  * Factory, plugin ed eventi forniscono un alto livello di estensibilità.

#### Strumenti e ambienti di sviluppo

Per soddisfare i requisiti delle imprese, che hanno linee guida e regole di gestione
proprie, symfony può essere completamente personalizzato. Esso fornisce, per
impostazione predefinita, diversi ambienti di sviluppo ed è distribuito con molteplici
strumenti che automatizzano i comuni compiti di produzione del software:

  * Gli strumenti di generazione del codice sono ottimi per creare prototipi e per
    l'amministrazione in un click
  * Il framework precostituito di test unitari e funzionali fornisce gli strumenti
    perfetti per lo sviluppo per test (TDD).
  * Il pannello di debug accelera le operazioni di debug, mostrando le informazioni di
    cui lo sviluppatore ha bisogno, direttamente nella pagina su cui sta lavorando.
  * L'interfaccia a linea di comando automatizza il rilascio delle applicazioni
    tra server diversi.
  * Le modifiche istantanee sono possibili ed effettive.
  * Le caratteristiche di log forniscono agli amministratori dettagli completi sulle
    attività delle applicazioni.

### Chi ha creato symfony e perché?

La prima versione di symfony è stata rilasciata nell'ottobre 2005 dal fondatore del
progetto, Fabien Potencier, co-autore di questo libro. Fabien è il CEO di Sensio
([http://www.sensio.com/](http://www.sensio.com/)), una web agency francese ben
nota per la sua visione innovativa dello sviluppo web.

Nel 2003, Fabien ha passato un po' di tempo alla ricerca di strumenti esistenti open
source per lo sviluppo di applicazioni web in PHP. Non ha trovato nulla che
soddisfacesse i requisiti sopra descritti. Quando è uscito PHP 5, ha deciso che gli
strumenti a disposizione avevano raggiunto un livello di maturità tale da poter essere
integrati in un framework completo. Quindi, ha passato un anno a sviluppare il nocciolo
di symfony, basando il suo lavoro sul framework MVC Mojavi, l'ORM Propel e gli helper
di Ruby on Rails.

Fabien originariamente ha costruito symfony per i progetti di Sensio, perché la
disponibilità di un framework efficace è un modo ideale per sviluppare applicazioni
velocemente ed efficacemente. Ha anche reso lo sviluppo web più intuitivo e le
applicazioni sviluppate più robuste e facili da mantenere. Il framework è stato
messo alla prova quando è stato impiegato per costruire un sito di commercio
elettronico per un rivenditore di biancheria intima e successivamente applicato
ad altri progetti.

Dopo aver usato con successo symfony per alcuni progetti, Fabien ha decido di
rilasciarlo sotto una licenza open source. Lo ha fatto per donare il suo lavoro
alla comunità, per beneficiare del feedback degli utenti, per mostrare l'esperienza
di Sensio e perché è divertente.

>**Note**
>Perché "symfony" e non "PippoPlutoFramework"? Perché Fabien voleva un nome breve che
>contenesse una S, come Sensio, e una F, come framework, facile da ricordare e non
>associato con altri strumenti di sviluppo. Inoltre, non ama le lettere maiuscole.
>Symfony era abbastanza vicino, anche se non completamente inglese, ed era anche
>disponibile come nome di progetto. L'alternativa era "baguette".

Per poter essere un progetto open source di successo, symfony aveva bisogno di una
documentazione estesa, in inglese, per aumentare il tasso di adozione. Fabien ha chiesto
al suo collega François Zaninotto, l'altro autore di questo libro, di analizzare il
codice e di scrivere un libro online. Ci è voluto un po' di tempo, ma quando il
progetto è stato reso pubblico, era documentato abbastanza bene da attrarre numerosi
sviluppatori. Il resto è storia.

### La comunità di symfony

Non appena il sito di symfony ([http://www.symfony-project.org/](http://www.symfony-project.org/))
è stato lanciato, numerosi sviluppatori da tutto il mondo l'hanno scaricato e
installato, hanno letto la documentazione online e hanno costruito la loro prima
applicazione con symfony e la voce ha iniziato a spandersi.

All'epoca, i framework per lo sviluppo di applicazioni web stavano diventando popolari
e il bisogno di un framework completo in PHP era alto. Symfony offriva una soluzione
convincente, grazie alla sua grande qualità del codice e alla significativa quantità di
documentazione, due grandi vantaggi sulla concorrenza nel mondo dei framework.
Presto sono arrivati dei contributi, proposte di patch e di miglioramenti, correzioni
alla documentazione e altri contributi molto richiesti.

Il repository pubblico del sorgente e il sistema di ticket offrono molti modi di
contribuire e tutti i volontari sono benvenuti. Fabien è sempre il principale
contribuente del codice e garantisce la qualità del codice.

Oggi, il [forum](http://forum.symfony-project.org/) di symfony, le 
[mailing](http://groups.google.com/group/symfony-users) [list](http://groups.google.com/group/symfony-devs)
e il canale (IRC)[channel](irc://irc.freenode.net/symfony) offrono degli sbocchi
ideali per il supporto, in cui pare che ogni risposta trovi in media quattro risposte.
Nuovi arrivati installano symfony ogni giorni e le sezioni del wiki e dei consigli
sul codice ospitano tantissima documentazione scritta dagli utenti. Oggigiorno, symfony
è uno dei framework PHP più popolari.

La comunità di symfony è la terza forza del framework e speriamo che tutti voi vi
uniate a essa dopo aver letto questo libro.

### Symfony fa per me?

Che siate esperti di PHP o novizi della programmazione, potrete usare symfony.
Il fattore principale nella scelta è la dimensione del progetto.

Se si vuole sviluppare un semplice sito con una decina di pagine, un accesso limitato
al database e nessuna esigenza di prestazioni o di documentazione, probabilmente il
semplice PHP è la scelta migliore. Non si otterrebbero grandi vantaggi da un framework,
mentre l'uso di MVC probabilmente rallenterebbe il processo di sviluppo. Inoltre,
symfony non è ottimizzato per girare efficacemente un un server condiviso, su cui
gli script PHP possono girare solo in modalità CGI.

D'altro canto, se si devono sviluppare applicazioni più complesse, con una logica molto
articolata, PHP da solo non basta. Se si ha intenzione di mantenere o estendere
l'applicazione nel futuro, si avrà bisogno di codice leggero, leggibile ed efficace.
Se si vogliono usare gli ultimi ritrovati nell'interazione con gli utenti (come ajax)
in modo intuitivo, non si possono semplicemente scrivere centinaia di righe di
JavaScript. Se ci si vuole divertire e sviluppare rapidamente, PHP da solo è
probabilmente deludente. In tutti questi casi, symfony fa per voi.

Ovviamente, per chi è uno sviluppatore professionista, i benefici di un framework sono
già noti e si ha bisogno di un framework maturo, ben documentato e con una grande
comunità. Non serve cercare ancora, symfony è la soluzione.

>**TIP**
>Per una dimostrazione visuale, si vedano i video disponibili sul sito di symfony.
>Si potrà vedere quanto è veloce e divertente sviluppare applicazioni con symfony.

Concetti fondamentali
---------------------

Prima di iniziare con symfony, occorre capire alcuni concetti di base.
Chi conosce già il significato di OOP, ORM, RAD, DRY, KISS, TDD, YAML, può
saltare questa parte.

### PHP

Symfony è sviluppato in PHP ([http://www.php.net/](http://www.php.net/)) e dedicato
a costruire applicazioni web nello stesso linguaggio. Quindi, occorre una conoscenza
solida di PHP e della programmazione orientata agli oggetti, per ottenere il meglio
dal framework. La versione minima richiesta è PHP 5.2.4.

### Programmazione orientata agli oggetti (OOP)

La programmazione orientata agli oggetti (OOP) non sarà spiegata in questo capitolo.
Avrebbe bisogno di un intero libro! Poiché symfony fa un largo uso di meccanismi
orientati agli oggetti disponibili in PHP 5, OOP è un prerequisito per imparare symfony.

Wikipedia spiega l'OOP come segue:

  "L'idea dietro alla programmazione orientata agli oggetti è che un programma possa
essere visto come un insieme di unità individuali, o oggetti, che agiscono l'uno
sull'altro, in opposizione alla visione tradizionale in cui un programma possa essere
visto come un insieme di funzioni o semplicemente come una lista di istruzioni al
computer."
  
PHP implementa i paradigmi orientati agli oggetti di classe, oggetto, metodo,
ereditarietà e molto altro. Chi non abbia familiarità con questi concetti dovrebbe
leggere la relativa documentazione di PHP, disponibile su
[http://www.php.net/manual/en/language.oop5.basic.php](http://www.php.net/manual/en/language.oop5.basic.php).

### Metodi magici

Uno dei punti di forza delle capacità degli oggetti di PHP è l'uso di metodi magici.
Questi metodi possono essere usati per sovrascrivere il comportamento predefinito
di classi, senza modificare il codice esterno. Essi rendono la sintassi di PHP meno
verbosa e più estensibile. Sono facili da riconoscere, perché i loro nomi iniziano
con due trattini bassi (`__`).

Ad esempio, quando si mostra un oggetto, PHP cerca implicitamente un metodo `__toString()`
per tale oggetto, per vedere se lo sviluppatore abbia definito un formato
personalizzato di visualizzazione:

    [php]
    $myObject = new myClass();
    echo $myObject;

    // Cerca un metodo magico
    echo $myObject->__toString();

Symfony usa i metodi magici, quindi occorre avere una buona conoscenza di essi.
Sono descritti nella documentazione di PHP 
([http://www.php.net/manual/en/language.oop5.magic.php](http://www.php.net/manual/en/language.oop5.magic.php)).

### Object-Relational Mapping (ORM)

I database sono relazionali. PHP e symfony sono orientati agli oggetti. Per poter
accedere al database in un modo orientato agli oggetti, serve un'interfaccia che
traduca la logica degli oggetti nella logica relazionale. Questa interfaccia si chiama
object-relational mapping o ORM.

Un ORM è fatto di oggetti che danno accesso a dati e che mantengono la logica al loro
interno.

Un beneficio di un livello di astrazione object-relational è quello di evitare l'uso
di sintassi specifiche di un dato database. Esso traduce automaticamente le chiamate
agli oggetti del modello in query SQL ottimizzate per il database scelto.

Questo significa che passare a un altro database è facile. Si immagini di aver
scritto un rapido prototipo di un'applicazione, ma il cliente non ha deciso quale
database sia meglio per le sue esigenza. Si può iniziare a costruire l'applicazione
con SQLite, ad esempio, per poi passare a MySQL, PostgreSQL o Oracle, quando il cliente
è pronto a decidere. Basta cambiare una sola riga nel file di configurazione e il gioco
è fatto.

Un livello di astrazione incapsula la logica dei dati. Il resto dell'applicazione non ha
bisogno di sapere delle query SQL e il codice SQL che accede al database è facile da
trovare. Inoltre, gli sviluppatori specializzati nella programmazione dei database sanno
dove andare.

Usare oggetti invece di righe e classi invece di tabelle ha altri benefici: si possono
aggiungere nuovi modi di accedere alle tabelle. Ad esempio, se si ha una tabella chiamata
`Client` con due campi, `FirstName` e `LastName`, si può pensare di voler semplicemente
avere un `Name`. Nel mondo orientato agli oggetti è facile, basta aggiungere un nuovo
metodo di accesso alla classe `Client`, come questo:

    [php]
    public function getName()
    {
      return $this->getFirstName().' '.$this->getLastName();
    }

Tutte le funzioni di accesso ai dati e la logica dei dati possono essere mantenuti
in oggetti simili. Ad esempio, si consideri una classe `ShoppingCart`, in cui si
tengono delle cose (che sono oggetti). Per recuperare il totale del carrello per il
pagamento, si può aggiungere un metodo `getTotal()`, come questo:

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

Usando questo metodo, possiamo controllare i valori restituiti a livello di oggetto.
Si immagini poi di voler aggiungere una logica di sconti, che abbia effetto sul totale:
basterà semplicemente aggiungerla al metodo `getTotal()` o anche al metodo
`getPrice()` di `item`, per ottenere il valore corretto.

Symfony supporta nativamente i due ORM open source più popolari: Propel e Doctrine.
Symfony li integra entrambi senza problemi. Quando si crea un nuovo progetto, basta
scegliere se usare Propel o Doctrine.

Questo libro descrive come usare gli oggetti Propel e Doctrine, ma per un riferimento
più completo, si raccomanda di visitare i siti di [Propel](http://www.propelorm.org/)
e di [Doctrine](http://www.doctrine-project.org/).

### Rapid Application Development (RAD)

La programmazione di applicazioni web è stata a lungo un lavoro lungo e noioso.
Seguendo i classici cicli di sviluppo del software (come ad esempio quello proposto
dal Rational Unified Process, ad esempio), lo sviluppo di un'applicazione web non può
iniziare prima di aver scritto un insieme completo di requisiti, aver disegnato molti
diagrammi UML e prodotto tonnellate di documentazione preliminare. Questo era dovuto alla
velocità generale di sviluppo, alla mancanza di versatilità dei linguaggi di
programmazione (occorreva costruire, compilare, ricominciare e chissà cos'altro prima di
vedere un programma girare) e, la maggior parte delle volte, al fatto che i clienti erano
abbastanza ragionevoli da non cambiare idea continuamente.

Oggi gli affari si muovo più velocemente e i clienti tendono a cambiare idea continuamente
durante il corso dello sviluppo di un progetto. Ovviamente, si aspettano che il team di
sviluppo si adatti alle loro esigenze e cambi la struttura dell'applicazione velocemente.
Fortunatamente, l'uso di linguaggi di scripting come Python, Ruby e PHP rende facile
applicare altre strategie di programmazione, come lo sviluppo rapido (RAD) o lo
sviluppo agile.

Una delle idee di queste metodologie è quella di iniziare lo sviluppo il più presto
possibile, in modo che il cliente possa valutare un prototipo funzionante e offrire
ulteriori istruzioni. Quindi l'applicazione viene costruita in un processo iterativo,
rilasciando versioni incrementali in brevi cicli di sviluppo.

Le conseguenze per gli sviluppatori sono numerose. Uno sviluppatore non ha bisogno di
pensare al futuro, quando implementa una funzionalità. Il metodo usato dovrebbe essere
più semplice e diretto possibile. Questo concetto è ben illustrato dalla massima del
principio KISS: Keep It Simple, Stupid (mantienilo semplice, stupido).

Quando i requisiti evolvono o viene aggiunta una nuova caratteristica, il codice
esistente deve solitamente essere in parte riscritto. Questo processo è chiamato
rifattorizzazione e capita spesso nel corso dello sviluppo di un'applicazione web.
Il codice viene spostato in altri posti secondo la sua natura. Le parti duplicate
di codice sono rifattorizzate in un solo posto, applicando quindi il principio
DRY (Don't Repeat Yourself, non ti ripetere).

E per essere sicuri che l'applicazione funzioni ancora, quando cambia costantemente,
occorre un insieme completo di test unitari, che possano essere automatizzati.
Se ben scritti, i test unitari sono una solida via per assicurare che niente si
rompa durante la rifattorizzazione. Alcune metodologie di sviluppo addirittura
prevedono la scrittura dei test prima di quella del codice: è il cosiddetto
sviluppo per test (Test Driven Developement o TDD).

>**NOTE**
>Ci sono molti altri principi e buone abitudini relative allo sviluppo agile.
>Una delle metodologie agili più efficaci si chiama Extreme Programming (XP). La
>letteratura disponibile su XP insegna diverse cose su come sviluppare un'applicazione
>in modo rapido ed efficace. Un buon punto di partenza è la serie di libri su XP
>di Kent Beck (Addison-Wesley).

Symfony è lo strumento perfetto per il RAD. Di fatto, il framework è stato costruito
da una web agency che applica il principio del RAD per i suoi stessi progetti.
Questo vuol dire che imparare a usare symfony non vuol dire imparare un nuovo
linguaggio, quanto piuttosto applicare i giusti riflessi e il giudizio migliore
per poter costruire applicazioni in modo più efficace.

### YAML

Stando al suo [sito](http://www.yaml.org/) ufficiale, YAML è una "serializzazione
standard amichevole per tutti i linguaggi di programmazione". In altre parole, YAML è
un linguaggio molto semplice usato per descrivere dati in modo simile a XML, ma con una
sintassi più semplice. È utile specialmente per descrivere dati che possono essere
tradotti in array e hash, come questi:

    [php]
    $house = array(
      'family' => array(
        'name'     => 'Doe',
        'parents'  => array('John', 'Jane'),
        'children' => array('Paul', 'Mark', 'Simone')
      ),
      'address' => array(
        'number'   => 34,
        'street'   => 'Main Street',
        'city'     => 'Nowheretown',
        'zipcode'  => '12345'
      )
    );

Questo array PHP può essere creato automaticamente leggendo la seguente stringa YAML:

    [yml]
    house:
      family:
        name:     Doe
        parents:
          - John
          - Jane
        children:
          - Paul
          - Mark
          - Simone
      address:
        number: 34
        street: Main Street
        city: Nowheretown
        zipcode: "12345"

In YAML, la struttura è mostrata tramite l'indentazione, gli oggetti in sequenza
denotati da un trattino e le coppie chiave/valore in una mappa separate da una virgola.
YAML ha anche una sintassi breve per descrivere le stesse strutture in meno righe,
in cui gli array sono mostrati esplicitamente con `[]` e gli hash con `{}`. Quindi,
lo YAML precedente può essere scritto in modo più breve, come segue:

    [yml]
    house:
      family: { name: Doe, parents: [John, Jane], children: [Paul, Mark, Simone] }
      address: { number: 34, street: Main Street, city: Nowheretown, zipcode: "12345" }

YAML è un acronimo per "YAML Ain't Markup Language" e si pronuncia "yamel". Il formato
è stato creato all'incirca nel 2001 e ha dei parser per molti linguaggi.

>**TIP**
>Le specifiche del formato YAML sono disponibili su [http://www.yaml.org/](http://www.yaml.org/).

Come si può vedere, YAML è molto più veloce rispetto a scrivere XML (nessun tag di
chiusura o virgolette esplicite) ed è più potente dei file `.ini` (che non supportano
le gerarchie). Per questo symfony usa YAML come linguaggio eletto per memorizzare le
configurazioni. Ci saranno molti file YAML in questo libro, ma sono così facili che
probabilmente non servirà imparare molto di più su di esso.

Riepilogo
---------

Symfony è un framework per applicazioni web in PHP. Aggiunge un nuovo livello sopra al
linguaggio PHP, fornisce strumenti che accelerano lo sviluppo di applicazioni web
complesse. Questo libro spiegherà tutto su di esso e per comprenderlo basterà essere
familiari coi concetti base della programmazione moderna: programmazione a oggetti (OOP),
object-relational mapping (ORM) e sviluppo rapido (RAD). L'unico requisito tecnico è
la conoscenza di PHP.


