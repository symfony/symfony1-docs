Lavorare con la comunità di symfony
===================================

*di Stefan Koopmanschap*

Esistono molte ragioni per lavorare con il software Open-Source. Avere la 
possibilità di vedere il codice sorgente è una di queste. Il software è sempre
libero. Ma una delle ragioni più importanti per scegliere l'Open-Source è la 
comunità. Ci sono vari tipi di comunità che gravitano attorno ai progetti
Open-Source in base al progetto stesso. La comunità di symfony viene generalmente 
descritta come aperta e amichevole. Ma come si può ottenere il massimo dalla 
propria relazione con la comunità? E quali sono le vie migliori per dare il 
proprio contributo? In questo capitolo viene introdotta la comunità di symfony 
e come lavorare con essa. Sia le aziende che i singoli sviluppatori avranno modo
di ottenere preziosi consigli su come interagire al meglio con la comunità
e ricavarne il massimo.

Ottenere il massimo dalla comunità
----------------------------------

Esistono molti modi per ottenere qualcosa dalla comunità di symfony. Alcune di
queste vie sono così connesse all'utilizzo di symfony come framework, al punto da
non far considerare il fatto che ciò che si sta facendo è possibile solo grazie
alla comunità. La cosa principale, sicuramente, è l'utilizzo di symfony. A parte 
tutto symfony è stato sviluppato inizialmente da un'azienda e tutt'ora ha alle
spalle Sensio, tuttavia non sarebbe mai arrivato dov'è oggi senza una solida
comunità a sostegno. Vediamo quindi come trarre vantaggio dalla comunità tralasciando
il framework stesso.

### Supporto

Ogni sviluppatore, specialmente se si inizia ad utilizzare il framework
da zero, si troverà ad un certo punto a non avere idea di come affrontare
qualche problema. Ci si blocca in un punto in cui non si capisce quale possa
essere il modo migliore di procedere.
Fortunatamente symfony ha una comunità molto amichevole, in grado di fornire
aiuto per ogni tipo di domanda che si possa avere. In base alle proprie
preferenze, esistono varie strade per ottenere le risposte che si cercano.
Il concetto di fondo è sempre lo stesso: si pone la domanda e nella maggior
parte dei casi si ottiene una risposta in modo molto veloce.

#### Prima di fare una domanda

Prima di fare una domanda attraverso uno dei modi elencati di seguito, è
opportuno cercare di trovare la soluzione per conto proprio. Sicuramente 
si può ricorrere all'utilizzo di [Google](http://www.google.com/) 
per cercare sul web (e si deve fare!) ma se si vuole raffinare un minimo 
la ricerca, gli archivi delle varie mailinglist, in special modo
[gli archivi degli utenti symfony](http://groups.google.com/group/symfony-users/topics),
sono un buon punto di partenza.

#### Fare una domanda

È importante sapere come porre una domanda. Può sembrare semplice ma in questo
momento è importante pensare prima a cosa si sta chiedendo. Inoltre è fondamentale
assicurarsi di aver controllato la documentazione esistente per verificare se 
alla domanda è già stata data risposta. Ci sono alcune considerazioni generali che
possono essere d'aiuto per ottenere una risposta migliore:

 * Pensate alla vostra domanda. Fate in modo di formularla in modo chiaro. Indicate
   cosa state facendo (o provate a fare), cosa non siete in grado di fare e 
   ricordate di indicare in modo chiaro qualsiasi errore abbiate ottenuto.

 * Fate un breve riassunto delle soluzioni che avete provato. Menzionate la 
   documentazione utilizzata per provare a risolvere il problema, la possibile
   soluzione trovata navigando sul web o negli archivi delle mailinglist, o
   ognuna delle cose provate mentre cercavate di risolvere il problema.

#### Mailinglist

Esistono diversi [Gruppi Google](http://groups.google.com) relativi a symfony con
obiettivi diversi. Queste mailinglist sono il canale principale per entrare in
contatto con utenti e sviluppatori di symfony. Se state lavorando con symfony
e siete in cerca di aiuto per un problema incontrato, la mailinglist
[symfony-users](http://groups.google.com/group/symfony-users) è il posto giusto.
I lettori di questa mailinglist sono un mix di normali utenti di symfony, utenti 
alle prime armi e gran parte del core team di symfony. Per ogni domanda posta lì,
c'è qualcuno in grado di dare una risposta. Esistono anche molte altre liste con
altri obiettivi:

 * [symfony-devs](http://groups.google.com/group/symfony-devs): Per argomenti
   riguardanti lo sviluppo del core di symfony (*non per supporto!*)

 * [symfony-docs](http://groups.google.com/group/symfony-docs): Per argomenti
   riguardanti la documentazione di symfony

 * [symfony-community](http://groups.google.com/group/symfony-community): Per
   argomenti riguardanti le iniziative della comunità
   
 * [symfony-it] (http://groups.google.com/group/symfony-it=: Per cercare aiuto
   nella comunità italiana di symfony.
   
Nell'utilizzo di tutte queste mailinglist bisogna tenere a mente il fatto che 
per loro natura sono meno dirette degli altri mezzi di comunicazione, come
per esempio IRC. Possono essere necessarie da alcune ore a qualche giorno,
per ottenere la risposta che si sta cercando.
È inoltre importante essere tempestivi nelle risposte alle ulteriori domande
che gli altri utenti possono porre e non essere troppo impazienti.

Diversamente da IRC bisogna fornire tutte le informazioni di contorno relative
alla propria domanda. Informazioni sulla configurazione, l'ORM utilizzato, il
tipo di sistema operativo utilizzato, che tipo di soluzioni sono già state provate
e quali si sono rilevate inadatte. Ogni codice esemplificativo può essere
incluso nell'email per descrivere al meglio il contesto del problema che può
guidare alla soluzione.

#### IRC

IRC è lo strumento più diretto per ottenere risposte grazie al fatto che permette
una comunicazione in tempo reale. Symfony ha un canale dedicato chiamato
 #symfony sul [network Freenod](http://freenode.net/) dove in ogni momento del giorno
sono presenti molti utenti. Bisogna ricordare però che nonostante il canale abbia 
oltre 100 utenti presenti, spesso buona parte di essi sono impegnati al
lavoro e non controllano costantemente il canale IRC. Quindi nonostante
la comunicazione sia in tempo reale, a volte, può essere necessario un po' di
tempo per ricevere una risposta.

IRC non è propriamente adatta per visualizzare grosse porzioni di codice o 
simili. Quindi va bene descrivere il proprio problema nel canale IRC ma se si vogliono
mostrare porzioni di codice o contenuti dei file di configurazione, assicurarsi
di utilizzare siti come pastebin.com e segnalare solamente il link nel
canale IRC. Gli utenti vedono il fatto di incollare porzioni di codice, come
un'invasione del canale. Questo non è apprezzato e sarà difficile ottenere
una risposta alla propria domanda.

Quando si fa una domanda su IRC bisogna prestare molta attenzione alle risposte
che si ricevono. Bisogna essere reattivi ad ogni domanda aggiuntiva che si riceve
riguardo al problema. Considerare il fatto che a volte gli utenti del
canale IRC potrebbero criticare l'intero approccio al problema.
A volte potrebbero aver ragione, a volte potrebbero aver torto, considerando
la vostra situazione in modo errato, ma bisogna fare in modo di rispondere alle loro
domande e descrivere la propria situazione quando richiesto. Gli
altri utenti non conoscono l'intera architettura del progetto, per 
questo potrebbero fare delle ipotesi che si potrebbero rivelare non corrette
una volta che si è spiegato meglio il problema. Bisogna non sentirsi offesi da
queste cose: gli utenti del canale provano solo ad aiutare.

Quanto il canale è piuttosto pieno assicurarsi di indirizzare alla persona a
cui si sta rispondendo le risposte alle domande specifiche, indicando 
il loro nome. Questo permetterà di capire in modo chiaro con chi si sta
parlando per colui che ha fatto la domanda, ma anche per gli altri impegnati
in altre conversazioni; ora sanno che possono ignorare questi messaggi
perché non fanno parte delle loro conversazioni.

### Fix e nuove funzionalità

Queste cose sembrano essere scontate, ma va detto quanto segue: l'intera 
base del codice di symfony è frutto del grande sforzo della comunità. In
tutto questo c'è molto tempo dedicato da parte di Sensio e di Fabien 
specialmente, ma anche il loro lavoro fa parte della comunità visto che
rilasciando symfony come Open-Source hanno mostrato il loro amore per
la comunità. Ma anche tutti gli altri sviluppatori che lavorano per la 
realizzazione di nuove funzionalità, o inviano bug fix lo fanno per la
comunità. Quindi quando si lavora con symfony (o qualsiasi altro progetto
Open-Source) è bene ricordare che si può utilizzarlo grazie alla comunità.

### Plugin

Symfony ha un sistema di plugin molto vasto che permette di installare molto
semplicemente plugin esterni nel proprio progetto. Il sistema dei plugin è
basato sull'installer PEAR e sul suo sistema di canali che lo rende molto 
flessibile. Parallelamente ai plugin che sono inclusi nella distribuzione
di symfony, esiste una gran quantità di plugin che sono stati sviluppati
e vengono mantenuti dalla comunità. Si può visitare 
[il sito dei plugin](http://www.symfony-project.org/plugins/) e navigare 
al suo interno in base alle categorie, all'ORM utilizzato, alla versione
di symfony supportata, oltre a poter utilizzare la ricerca libera. Grazie al
lavoro della comunità si possono trovare dei plugin per gran parte delle comuni
funzionalità che si trovano nelle applicazioni web odierne.

### Conferenze ed eventi

Oltre a partecipare alla comunità attraverso i canali digitali come descritto
in precedenza, è possibile comunicare con la comunità alle conferenze e in
occasione di altri eventi. La maggioranza delle conferenze legate a PHP e anche 
alcune di quelle dedicate ad un pubblico più ampio del solo PHP, vedono alcuni 
membri della comunità di symfony come partecipanti, oppure come relatori. Grazie 
al lavoro di questi membri della comunità alle conferenze è possibile ricevere
aiuto e imparare cose nuove. Esistono eventi completamente dedicati a symfony.
Alcuni esempi lampanti sono [Symfony Live](http://www.symfony-live.com/), 
[Symfony Day](http://www.symfonyday.com/) e [SymfonyCamp](http://www.symfonycamp.com/). 
Tutti questi eventi hanno alle spalle un'azienda, ma gran parte del lavoro è frutto
degli sforzi della comunità, partecipando ad eventi del genere è possibile 
imparare molto riguardo symfony, oltre ad entrare in contatto con i principali
membri della comunità che possono essere particolarmente d'aiuto quando si hanno
dei problemi. Avendo l'opportunità di partecipare ad un evento del genere, è
davvero consigliato farlo.

Parallelamente alle conferenze di cui si è appena parlato, esiste anche un
numero sempre maggiore di gruppi di utenti symfony in tutto il mondo. Questi 
gruppi di utenti di solito non hanno aziende alle spalle, sono solo gruppi di
utenti che si incontrano ad appuntamenti fissi in una zona specifica. È molto
semplice partecipare ad uno di questi incontri, l'unico sforzo è quello di 
presentarsi. Questi incontri permettono di creare una rete di persone che 
utilizzano symfony, che possono aiutare nei problemi relativi a symfony, 
che possono avere delle offerte lavorative, o conoscere uno sviluppatore 
disponibile quando ne serve uno.

### Reputazione

Essere presenti nella comunità, essere visti dalle altre persone, parlare alla 
gente, anche diventando più attivo nella comunità permette di creare una propria 
reputazione. All'inizio questo potrebbe sembrare inutile, eccetto per una spinta
positiva al proprio ego, ma è possibile guadagnare molto di più da questa 
reputazione. Quando si cerca un lavoro e si segnala la cosa alla comunità, spesso 
si può essere contattati da una o due persone per vedere se la propria figura
può adattarsi alle posizioni aperte che hanno da offrire. Ma una volta che 
si è iniziato a costruire una propria reputazione, il numero delle offerte che 
si ricevono aumenterà, così come la qualità delle stesse, che diventerà sempre più
interessante.

Allo stesso modo, quando si è in cerca di sviluppatori e si segnala alla
comunità, si riceveranno alcune risposte. Man mano che la propria reputazione
aumenta, il numero di risposte aumenterà e si sarà in grado di poter
scegliere tra alcuni dei principali nomi della comunità per i posti 
vacanti.

Dare il proprio contributo alla comunità
----------------------------------------

Ogni comunità funziona solamente basandosi sul sistema dare/avere. Se non ci
fosse nessuno che offre qualcosa alla comunità, non ci sarebbe niente da prendere
e la comunità stessa non esisterebbe. Quindi come si prende qualcosa dalla comunità,
è utile pensare anche a come dare il proprio contributo. Come aiutarla
a migliorare e crescere? Come contribuire all'esistenza e alla forza
della comunità? Si vedranno assieme alcune vie percorribili per dare il proprio 
contributo.

### Il forum e le mailinglist

Come già visto in precedenza, i forum e le mailinglist sono posti dove ricevere
supporto. Ricevere risposte alle proprie domande, suggerimenti su come risolvere
problemi, o feedback su un'approccio particolare utilizzato per il proprio
progetto. Anche se si è appena iniziato ad utilizzare symfony, man mano che
viene accumulata dell'esperienza, si sarà in grado di rispondere alle domande più semplici
di altri utenti agli inizi. Quando si diventerà più esperti si potranno rispondere a 
domande più complesse e partecipare ad argomenti di ogni tipo. Anche dare 
qualche semplice indicazione o suggerimento, può aiutare le persone a trovare le
soluzioni ai loro problemi. Essendo già iscritti ad una mailinglist, mettere a
disposizione la propria esperienza è un piccolo impegno che può aiutare gli
altri.

### IRC

Come visto in precedenza, la comunicazione più diretta per ciò che riguarda
symfony viene fatta su IRC. Come per le mailinglist, se ci si affaccia ogni tanto
sul canale IRC, si può dare un'occhiata in cerca di domande a cui si sa 
rispondere. Non è necessario restare costantemente presenti sul canale IRC, 
molti dei presenti sul canale #symfony non sono sempre presenti. Tuttavia
quando c'è bisogno di una breve pausa dal lavoro si passa al client IRC, 
controllando le discussioni in corso e provando a dare il proprio contributo per
chiarire qualche problema, aiutando qualcuno o semplicemente argomentando su una
specifica funzionalità. Essere presenti su IRC, sebbene non si controlli il 
canale ogni minuto, permette agli altri utenti di mettervi in luce menzionando
il vostro nickname. Gran parte dei client IRC notifica il fatto che qualcuno ha
chiamato il vostro nick in modo che voi possiate rispondere. Questo permette di
essere più facilmente rintracciabili dalla comunità, se si pensa che la persona
sappia rispondere ad una certa domanda. Quindi anche quando non si fa niente si può
riuscire comunque a fare qualcosa di utile: essere disponibili.

### Contribuire al codice

Per chi lavora con symfony, probabilmente la via più semplice per contribuire è
dare del codice. Visto che tutti gli utenti di symfony sono degli
sviluppatori questo è il modo più divertente per dare il proprio contributo alla
comunità. Esistono molti modi per offrire il proprio codice. Di seguito una lista
di modalità per farlo.

#### Patch al core

È possibile che lavorando con symfony si incontri un bug. Oppure semplicemente
c'è bisogno di fare qualcosa che attualmente symfony non offre, attraverso 
una qualche funzionalità specifica. Lavorare con una versione modificata di un 
framework non è raccomandabile, visto che potrebbero verificarsi non pochi 
problemi non appena ci fosse bisogno di eseguire un aggiornamento di versione.
È anche possibile dimenticarsi della modifica e ravvisare dei problemi in 
futuro. Specialmente quando si scova un bug, è buona norma segnalare il problema
agli sviluppatori del framework. Come si può fare questo?

Prima di tutto le modifiche vanno fatte al codice di symfony. I file del framework
vanno modificati per risolvere un bug, o aggiungere una qualche funzionalità.
Poi, assumendo che i cambiamenti siano stati apportati a del codice ottenuto 
con un checkout da Subversion del codice di symfony, un diff può essere creato 
sulla base dei cambiamenti apportati al codice base di symfony. Con Subversion
questo può essere fatto ricorrendo al seguente comando:

    $ svn diff > my_feature_or_bug_fix.patch

Questo comando deve essere eseguito nella cartella root del checkout di symfony,
per assicurarsi che tutti i cambiamenti fatti al codice vengano raccolti in un
file patch.

Il passo successivo è quello di andare sul
[bugtracker di symfony](http://trac.symfony-project.org). Dopo aver effettuato
il login è possibile creare un nuovo ticket. Creando un nuovo ticket assicurarsi
di compilare quanti più campi possibili, per rendere più semplice il compito del 
core team nel riprodurre il bug, o per far capire quali parti di symfony sono
affette da esso.

Nel riquadro "Ticket Properties" fare molta attenzione a selezionare la versione
corretta di symfony su cui la patch si basa. Quando ad essere coinvolti sono 
più componenti, selezionare quello maggiormente coinvolto e assicurarsi di 
segnalare nel campo "Full Description" quali altre parti sono interessate dal
problema.

Importante notare che il campo "Short Summary" dovrebbe contenere il prefisso 
[PATCH] quando una patch viene allegata. Alla fine del form assicurarsi di 
marcare il checkbox che indica la presenza di una patch allegata al nuovo ticket.

#### Contribuire ai plugin

Lavorare sul core del framework non è per tutti. Ma gli utenti di symfony 
lavorano su progetti che contengono funzionalità sviluppate ad hoc. Qualche
funzionalità è molto specifica per il progetto, per questo non sarebbe molto 
utile renderla disponibile per l'utilizzo da parte di alti utenti, spesso però 
un progetto contiene codice piuttosto generico che può tornare utile ad altri 
utenti. È una best practice inserire gran parte della logica dell'applicazione 
nei plugin, per essere in grado di riutilizzarla in modo semplice almeno
internamente alla propria organizzazione. Ma dato che il codice è stato 
raggruppato in un plugin, è anche possibile renderlo Open-Source, facendolo
diventare disponibile a tutti gli utenti di symfony.

Contribuire alla comunità di symfony con un plugin è abbastanza semplice. È 
possibile 
[leggere la documentazione](http://www.symfony-project.org/jobeet/1_4/Doctrine/it/20#chapter_20_contribuire_con_un_plugin)
su come creare, organizzare il plugin e caricarlo sul sito di symfony.
Il sito di symfony permette agli sviluppatori di plugin di utilizzare un intero
insieme di strumenti per pubblicare attraverso il server del canale dei plugin, 
oltre a ospitare il codice del plugin in un repository Subversion sul server di
symfony, in modo che gli altri sviluppatori possano accedervi. Quando si valuta
di rendere pubblico un plugin, questa è la via consigliabile per farlo. È molto 
più semplice che gestire un server Subversion, un server per i pacchetti PEAR e creare
la documentazione per spiegare agli utenti come utilizzare il sistema. Aggiungere
un plugin al sistema di plugin di symfony, lo rende automaticamente disponibile
a tutti gli utenti di symfony senza ulteriori configurazioni. Ovviamente è sempre
possibile creare un server per pacchetti PEAR e richiedere agli utenti di aggiungerlo 
ai loro progetti per installare il plugin.

### Documentazione

Uno dei punti di forza di symfony è la documentazione. Il core team ha scritto
molta della documentazione su come utilizzare symfony, ma una grossa fetta è
disponibile anche grazie alla comunità. Ci sono anche sforzi congiunti tra il core
team e la comunità, come per esempio il lavoro sul tutorial Jobeet. La
documentazione aiuta i nuovi sviluppatori ad imparare ad utilizzare symfony,
oltre ad essere una guida sempre presente per gli sviluppatori più esperti; è 
quindi molto importante avere documentazione di qualità. Dare il proprio 
contributo alla documentazione di symfony può essere fatto in molti modi.

#### Scrivere sul proprio blog

Condividere esperienze e conoscenze su symfony è molto importante per la 
comunità. Specialmente quando ci si imbatte in qualcosa che è difficile da 
realizzare, condividere ciò con la comunità è un bel gesto. Altre persone
potrebbero incontrare lo stesso problema e utilizzare un motore di ricerca a 
caccia di qualcuno che abbia avuto la stessa esperienza. Ottenere dei buoni
risultati nella ricerca li aiuterà a risolvere il loro problema più velocemente.

Quindi quando si scrive un post sul proprio blog, l'argomento non dev'essere una 
generica introduzione a symfony (anche se lo può essere) ma dovrebbe essere
relativo a esperienze sul framework, soluzioni a problemi incontrati durante il
proprio lavoro, o una funzionalità interessante dell'ultima versione.

Chiunque scriva su symfony, può aggiungere il proprio blog nella 
[lista dei blogger di symfony](http://trac.symfony-project.org/wiki/SymfonyBloggers). 
Tutti i blog di questa lista sono aggregati sulla
[pagina della comunità di symfony](http://www.symfony-project.org/community). 
Esistono tuttavia alcune linee guida: è richiesto un feed specifico per symfony, 
in questo modo tutti i contenuti sulla pagina della comunità, sono relativi a 
symfony. Inoltre è importante non aggiungere cose all'infuori di blog 
(per esempio, no ai feed di twitter).

#### Scrivere articoli

Le persone che si trovano a loro agio con la scrittura possono dedicarsi a farlo
su un gradino superiore. Esistono diverse riviste di PHP in tutto il mondo, così
come molte riviste di computer che permettono di proporre degli articoli. Articoli
per questo tipo di pubblicazioni sono spesso ad un livello più avanzato, più 
strutturati e di maggiore qualità rispetto a quella media dei post sui blog, ma
sono letti anche da molte più persone.

Molte pubblicazioni hanno le loro regole particolari per accettare gli articoli
proposti, quindi verificare sul sito della rivista o sulla rivista stessa, quali
sono i passi da seguire per proporre dei contenuti.

Parallelamente alle riviste ci sono molti altri posti dove questo tipo di 
articoli sono benvenuti. Per esempio i siti dei gruppi di utenti PHP o dei gruppi
di utenti symfony, i siti generici sullo sviluppo web e altre pubblicazioni online,
spesso apprezzano articoli dal buon contenuto che possono essere pubblicati
sul loro sito.

#### Tradurre la documentazione

Al giorno d'oggi molte persone interessate allo sviluppo con PHP hanno buona
familiarità con la lingua inglese. Tuttavia, per molte persone non si tratta 
della propria lingua madre e questo rende difficile leggere contenuti
tecnici piuttosto corposi. Symfony promuove la traduzione della documentazione
e offre il proprio supporto garantendo l'accesso in scrittura al repository della
documentazione ai traduttori e pubblica le versioni tradotte della documentazione
stessa sul sito di symfony.

La traduzione della documentazione è principalmente coordinata e organizzata
attraverso la [mailinglist della documentazione su symfony](http://groups.google.com/group/symfony-docs).
Se si è interessati ad aiutare nella traduzione della documentazione nella propria
lingua madre, questo è il primo posto da visitare. È possibile che vi siano
molti traduttori per una lingua, in questo caso è molto importante coordinare
gli sforzi per non duplicare il lavoro svolto. La mailinglist di symfony dedicata
alla documentazione, è il luogo perfetto dove dare il via alle traduzioni.

#### Aggiungere contenuti al wiki

Un wiki è una delle vie più libere per documentare qualsiasi cosa. Symfony ha un
[wiki](http://trac.symfony-project.org/wiki) dove gli utenti possono aggiungere
documentazione. È sempre buona cosa avere nuovi contenuti sul wiki. Comunque
è possibile contribuire leggendo gli articoli esistenti, apportando correzioni, o
aggiornandoli. Oltre ai nuovi articoli ne esistono di vecchi che contengono
esempi datati, o che sono totalmente non aggiornati. Aiutare a mantenere puliti
i contenuti esistenti sul wiki, è un ottimo modo per rendere più semplice la ricerca
da parte degli utenti che cercano i contenuti che fanno al caso loro.

Per farsi un'idea del tipo di contenuti presenti sul wiki, o trarre un po' di 
ispirazione per qualcosa che si può volee scrivere, basta dare un'occhiata alla
[homepage del wiki](http://trac.symfony-project.org/wiki) per vedere cosa c'è
già.

### Presentazioni

Scrivere è un buon modo per condividere conoscenza ed esperienze. Il contenuto è
disponibile a molte persone ed è ricercabile. Nonostante questo, vi sono altri 
modi per trasmettere conoscenza ed esperienza. Un modo molto apprezzato è quello
di fare delle presentazioni. Una presentazione può essere fatta in molti modi e 
per molti tipi di pubblico. Per esempio:

 * Una conferenza su PHP/symfony
 * Ad un meeting locale di un (PHP) user group
 * All'interno della propria azienda (per i propri colleghi sviluppatori)
 * All'interno della propria azienda (per il management)

In funzione del posto e del pubblico con cui ci si relaziona, sarà necessario
adattare la propria presentazione. Mentre il management non sarà molto interessato
in tutti i dettagli tecnici, il pubblico ad una conferenza riguardante symfony
non necessiterà di una introduzione di base al framework. Bisogna riservare il tempo
necessario per analizzare il giusto argomento e per preparare la presentazione. 
Cercare di ottenere una revisione delle slide da parte di qualche altro e, se 
possibile, provare la presentazione con delle persone in grado di dare un 
feedback onesto: non solo lodi, ma piuttosto critiche per migliorare la propria 
presentazione prima di farla realmente.

Un aiuto aggiuntivo sulla preparazione e sull'esposizione durante una presentazione,
è sempre disponibile sulla
[mailinglist della comunità](http://groups.google.com/group/symfony-community),
dove speaker esperti, oltre a visitatori abitudinari delle conferenze, saranno
in grado di aiutare con suggerimenti, consigli ed esperienza. Inoltre se non 
si è a conoscenza di una conferenza o di un gruppo di utenti dove fare la
presentazione si può registrarsi alla mailinglist, per ricevere aggiornamenti sui
Call for Papers delle conferenze, o entrare in contatto con i gruppi di utenti.

### Organizzare un evento/incontro

Oltre a offrire presentazioni a conferenze e meeting già esistenti, è possibile
organizzarne personalmente di nuovi. Possono essere molto piccole o molto grandi.
Possono essere rivolte a tutta la comunità, o solo agli utenti di una certa zona.
Possono anche essere inglobate come parte di un evento già esistente.

Un esempio reale è il meeting symfony creato ad-hoc e tenuto alla conferenza
PHPNW nel 2008. È tutto nato su Twitter e sul canale IRC per la conferenza, dove
molti utenti di symfony chiedevano come sarebbe stato symfony 1.2. Alla fine,
grazie all'organizzazione venne preparata una stanza e durante una delle pause 
tra le sessioni, un gruppo di circa 10 persone si è riunito per avere degli
aggiornamenti riguardanti symfony 1.2. Fu tanto piccolo e semplice quanto efficace,
per permettere ai presenti di avere un'idea di quanto aspettarsi (dall'allora)
nuova versione di symfony.

Un altro esempio lampante è l'organizzazione di conferenze della comunità, quali
[SymfonyCamp](http://www.symfonycamp.com/) e 
[SymfonyDay Cologne](http://www.symfonyday.com/). Entrambe le conferenze vennero
organizzate da aziende che sviluppano con PHP utilizzando symfony e che volevano
offrire qualcosa alla comunità. Tutte queste conferenze ottennero una buona 
partecipazione, con in programma partecipanti noti, e un bellissimo spirito di 
comunità.

### Diventare attivi localmente

Come detto in precedenza, non tutti sono capaci di capire grandi quantità di
contenuti tecnici in inglese. Inoltre a volte può essere piacevole non dover
parlare di symfony solamente online. Ci si può attivare localmente. Un buon 
esempio per questo, sono i gruppi di utenti symfony. Durante l'anno scorso si 
sono viste diverse iniziative per l'avvio di nuovi gruppi di utenti, molti hanno 
organizzato diversi incontri per persone interessate a symfony e al suo utilizzo.
Gran parte di questi eventi sono molto informali, gratuiti e sono completamente
guidati dalla comunità.

La già citata
[mailinglist della comunità symfony](http://groups.google.com/group/symfony-community) 
è il posto migliore dove cercare un gruppo di utenti esistente nella propria area,
o per dar vita ad un nuovo gruppo. Sono presenti membri e organizzatori di
gruppi di utenti locali di symfony che possono offrire il loro aiuto per 
avviare un nuovo gruppo e per organizzarlo al meglio.

Oltre alle attività in real life che si possono organizzare localmente, è anche 
possibile provare a promuovere online symfony nella propria regione. Un buon
esempio è aprire un portale symfony locale. Un esempio reale è il
[portale Spagnolo di symfony](http://www.symfony.es/), che riporta in maniera
costante quanto succede relativamente a symfony, pubblicando gli aggiornamenti 
sul sito in spagnolo. Il sito offre inoltre molta documentazione in spagnolo,
rappresentando così un ottimo punto per gli sviluppatori della Spagna per imparare
symfony e restare aggiornati sui nuovi sviluppi relativi al framework.

### Diventare parte del core team

Il core team è a tutti gli effetti, parte della comunità. Le persone che lo 
compongono hanno tutte iniziato come semplici utenti del framework e grazie
alla loro propensione a partecipare, in un modo o nell'altro sono diventate
parte del core team. Symfony è 
[meritocratico](http://it.wikipedia.org/wiki/Meritocrazia), questo significa 
che se si dà prova del proprio talento e delle proprie capacità, si dimostra di 
essere in grado di entrare a far parte del core team.

Abbiamo l'esempio
[dell'ingresso di Bernhard Schussek](http://www.symfony-project.org/blog/2009/08/27/bernhard-schussek-joins-the-core-team).
Bernard è entrato a far parte del core team dopo il suo fantastico lavoro 
sulla seconda versione del framework dei test Lime e per aver proposto 
patch interessanti per un lungo periodo di tempo.

### Da dove iniziare?

Ora che si è visto cosa è possibile ottenere dalla comunità e come dare il
proprio contributo, è interessante avere una breve panoramica sui punti di 
partenza per essere coinvolti nella comunità di symfony. Si possono usare per
trovare la propria via nella comunità.

#### Mailinglist Symfony-community

La [mailinglist symfony-community](http://groups.google.com/group/symfony-community)
è una mailinglist dove i membri possono esaminare idee per impegni nella
comunità, partecipare ad attività in via di realizzazione ed entrare in contatto con 
tutto ciò che è relativo alla comunità. Per partecipare ad una di queste 
iniziative, basta rispondere ad una delle discussioni disponibili. Se si ha 
qualche nuova idea che può rivelarsi utile, è possibile pubblicarla sulla
mailinglist. Se ci sono domande sulla comunità, o sui vari modi  per entrare in
contatto con essa, questo è il posto giusto dove farle.

#### La pagina "Come contribuire a symfony"

Da diverso tempo ormai, symfony ha una pagina speciale sul wiki intitolata
[Come contribuire a symfony](http://trac.symfony-project.org/wiki/HowToContributeToSymfony).
Questa pagina elenca tutte le possibilità per dare il proprio aiuto a symfony 
come progetto e alla sua comunità, qualsiasi siano le proprie abilità, indicando
i posti migliori dove si potrebbe essere d'aiuto. È una lettura raccomandata per
tutti quelli che vogliano partecipare attivamente alla comunità di symfony.

### Comunità esterne

L'invito è quello di non fermarsi ad essere coinvolti solo nella comunità di 
symfony come proposto in quest'articolo. Esistono molte altre iniziative nel 
mondo, avviate attorno a symfony da utenti del framework. Di seguito diamo 
particolare attenzione a due iniziative molto utili per le persone che lavorano 
con symfony.

#### Symfonians

La [comunità Symfonians](http://www.symfonians.net/) è una comunità che elenca
le persone e le aziende che lavorano con symfony e i progetti realizzati con
symfony. Il sito permette alle aziende di pubblicare offerte di lavoro e 
permette agli utenti di ricercare utenti, aziende e progetti nel proprio paese.

Il sito oltre ad essere ottimo per entrare in contatto con alti utenti di
symfony o cercare un lavoro, presenta un archivio di applicazioni che rendono
l'idea di cosa si possa realizzare con symfony. Esiste un'enorme varietà in 
termini di applicazioni elencati sul sito stesso, che rendono molto piacevole 
la navigazione per vedere cosa è stato realizzato con il framework.

Visto che tutti i contenuti del sito sono generati dalla comunità, è possibile 
ottenere un account e creare il proprio profilo, il profilo della propria azienda,
aggiungere applicazioni che sono state sviluppate con symfony e pubblicare le proprie
offerte di lavoro.

#### Gruppo symfony su LinkedIn

Ogni sviluppatore PHP professionista si è imbattuto molto probabilmente in 
LinkedIn. La maggior parte avrà un suo profilo personale. Per quelli che non 
conoscono LinkedIn: è un network dove realizzare una propria rete di contatti
professionali e restante in contatto con essi.

LinkedIn mette a disposizione anche funzionalità dedicate ai gruppi, permettendo
discussioni, inserimento di notizie e di annunci di lavoro. Molti argomenti hanno
un gruppo su LinkedIn e 
[symfony non fa eccezione](http://www.linkedin.com/groups?gid=29205&trk=myg_ugrp_ovr)
(è richiesto il login). Usando questo gruppo relativo a symfony, è possibile 
discutere di argomenti relativi ad esso, seguire le notizie, pubblicare offerte
di lavoro per sviluppatori symfony, trainer, consulenti e architetti.

Conclusioni
-----------

Ora si dovrebbe avere una buona idea su ciò che ci si può aspettare dalla comunità e 
di ciò che la comunità si aspetta da voi. Ricordare che ogni software open 
source fa affidamento su una comunità in grado di dare supporto al software. 
Questo supporto può essere qualsiasi cosa, dal fatto di rispondere a qualche
domanda, all'invio di patch e creazione di plugin, passando per la promozione
del software. Sarà fantastico unirsi alla comunità!
