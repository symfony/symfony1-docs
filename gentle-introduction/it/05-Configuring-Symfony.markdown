Capitolo 5 - Configurare symfony
================================

Per essere semplice da usare, symfony definisce alcune convenzioni, che devono soddisfare i requisiti più comuni nello sviluppo web senza bisogno di modifiche. Comunque, utilizzando un insieme di semplici e potenti file di configurazione, è possibile personalizzare il modo in cui il framework e la propria applicazione interagiscono fra loro. Con questi file di configurazione, si potranno anche aggiungere parametri speciali alla propria applicazione.

Questo capitolo spiega come funziona il sistema di configurazione:

  * La configurazione di symfony è memorizzata in file scritti in YAML, anche se è sempre possibile scegliere un altro formato.
  * Nella struttura di cartelle del progetto, i file di configurazione si trovano a livello di progetto, applicazione e modulo.
  * Si possono definire diversi insiemi di impostazioni: in symfony, un insieme di configurazioni è chiamato ambiente.
  * I valori definiti nei file di configurazione sono disponibili al codice PHP della propria applicazione.
  * Inoltre, symfony permette l'utilizzo di codice PHP e altri trucchi all'interno dei file di configurazione YAML, per rendere il sistema di configurazione ancora più flessibile.

Il Sistema di configurazione
----------------------------

A parte lo scopo, la maggior parte delle applicazioni web condivide un insieme di caratteristiche comuni. Ad esempio, qualche sezione può avere accesso ristretto a un certo insieme di utenti, oppure le pagine possono essere decorate da un layout, o ancora la possibilità di avere i form già riempiti dopo una validazione fallita. Un framework definisce una struttura per simulare queste caratteristiche e gli sviluppatori possono ulteriormente modificarle cambiando i file di configurazione. Questa strategia fa risparmiare molto tempo durante lo sviluppo, dato che molti cambiamenti non necessitano di alcuna linea di codice, anche se ce n'è molto dietro. Questo sistema è anche molto efficiente, perché assicura che queste informazioni siano reperibili sempre in un punto unico e facile da trovare.

Questo approccio ha però due seri svantaggi:

  * Gli sviluppatori di solito finiscono per scrivere complessi file XML senza fine.
  * In un'architettura PHP, ogni richiesta necessita di più tempo per essere eseguita.

Tenendo conto di questi svantaggi, symfony utilizza file di configurazione solo dove sono veramente necessari. L'ambizione del sistema di configurazione di symfony è di essere:

  * Potente: ogni aspetto che possa essere gestito tramite file di configurazione lo è veramente.
  * Semplice: diversi parametri di configurazione non sono mostrati in una normale applicazione, in quanto raramente necessitano di essere modificati.
  * Facile: gli sviluppatori troveranno facile leggere, creare e modificare file di configurazione.
  * Personalizzabile: il linguaggio di configurazione di default è YAML, ma puo' essere INI, XML o qualsiasi altro formato lo sviluppatore preferisca.
  * Veloce: i file di configurazione non vengono processati dall'applicazione ma dal sistema di configurazione, che li compila velocemente in parti di codice PHP sul server.

### Sintassi YAML e convenzioni di symfony

Per la sua configurazione, symfony utilizza il formato YAML, invece dei più tradizionali INI e XML. YAML mostra la struttura tramite indentazione ed è veloce da scrivere. I vantaggi e le regole di base sono già state mostrate nel capitolo 1. Comunque, occorre tenere a mente qualche convenzione quando si scrivono file YAML. Questa sezione introduce diverse convenzioni tra le più importanti. Per approfondimenti, visitare il [sito web di YAML](http://www.yaml.org/).

Prima di tutto non sono ammessi caratteri di tabulazione in YAML: occorre usare spazi vuoti. I parser YAML non capiscono le tabulazioni, per cui si devono utilizzare spazi vuoti per l'indentazione (la convenzione in symfony è di due spazi), come mostrato nel listato 5-1.

Listato 5-1 - YAML vieta l'utilizzo delle tabulazioni

    # Mai usare tabs
    all:
    -> mail:
    -> -> webmaster:  webmaster@example.com

    # Usare solo spazi
    all:
      mail:
        webmaster: webmaster@example.com


Se i parametri sono stringhe che cominciano o finiscono con spazi vuoti, contengono caratteri speciali (come # o ,) o sono parole chiave come "true, false" (intese come una stringa), occorre incapsulare il valore tra singoli apici, come mostrato nel listato 5-2.

Listato 5-2 - Stringhe non standard dovrebbero essere incapsulate tra singoli apici

    error1: This field is compulsory
    error2: '  This field is compulsory  '
    error3: 'Don''t leave this field blank' # i singoli apici devono essere raddoppiati
    error4: 'Enter a # symbol to define an extension number'
    i18n:   'false' # se togliamo gli apici, viene restituito un valore booleano false


Si possono definire stringhe lunghe in più righe e anche stringhe con più di una linea, con gli header speciali di stringa (> e |) più una indentazione addizionale. Il listato 5-3 illustra questa convenzione.

Listato 5-3 - Definizione di stringhe lunghe e su più righe

    # Folded style, introdotto da >
    # Ogni line break è convertito in uno spazio
    # Questo rende YAML più leggibile.
    accomplishment: >
      Mark set a major league
      home run record in 1998.

    # Literal style, introdotto da |
    # Vengono mantenuti i line break
    # L'indentazione non appare nella stringa risultante
    stats: |
      65 Home Runs
      0.278 Batting Average

Per definire un valore come array, racchiudere gli elementi tra parentesi quadre, oppure usare la sintassi estesa con i trattini, come mostrato nel listato 5-4.

Listato 5-4 - Sintassi di array in YAML

    # Sintassi abbreviata per gli array
    players: [ Mark McGwire, Sammy Sosa, Ken Griffey ]

    # Sintassi espansa per gli array
    players:
      - Mark McGwire
      - Sammy Sosa
      - Ken Griffey

Per definire un valore come array associativo, o hash, racchiudere gli elementi tra parentesi graffe e inserire sempre uno spazio tra la chiave e il valore nella coppia 'key: value' e separare gli elementi della lista con delle virgole. Si può anche utilizzare la sintassi estesa, aggiungendo indentazione e ritorno a capo per ogni chiave, come mostrato nel listato 5-5.

Listato 5-5 - Array associativi in YAML

    # Sintassi errata: mancano gli spazi dopo i due-punti e la virgola
    mail: {webmaster:webmaster@example.com,contact:contact@example.com}

    # Sintassi abbreviata corretta per gli array associativi
    mail: { webmaster: webmaster@example.com, contact: contact@example.com }

    # Sintassi estesa per gli array associativi
    mail:
      webmaster: webmaster@example.com
      contact: contact@example.com


Per assegnare un valore booleano, utilizzare i valori `false` e `true`
senza apici.

Listato 5-6 - Sintassi YAML per valori booleani

    true_value: true
    false_value: false

Si possono aggiungere commenti (che devono cominciare con il cancelletto #) e spazi aggiuntivi, per rendere ancora più leggibili i file YAML, come mostrato nel listato 5-7.

Listato 5-7 - Sintassi dei commenti e allineamento dei valori in YAML

    # Questa è una linea di commento
    mail:
      webmaster: webmaster@example.com
      contact:   contact@example.com
      admin:     admin@example.com # gli spazi aggiuntivi permettono un migliore allineamento dei valori

In qualche file di configurazione di symfony capiterà di trovare delle linee che cominciano con un cancelletto (per cui ignorate dal parser YAML) e che assomigliano a normali linee di impostazioni. Questa è una convenzione di symfony: la configurazione predefinita, ereditata da altri file YAML che si trovano nel core, è ripetuta in linee commentate nella propria applicazione per pura informazione. Se si vuole cambiare uno di tali parametri, occorre innanzitutto scommentare la linea, come mostrato nel listato 5-8.

Listato 5-8 - La configurazione predefinita è mostrata commentata

    # La cache è false per default
    settings:
    # cache: false

    # Se si vogliono cambiare questa impostazioni, scommentare la linea
    settings:
      cache: true

Qualche volta symfony raggruppa le definizioni dei parametri in categorie. Tutte le impostazioni relative a una data categoria appaiono indentati sotto il suo header. Strutturare lunghe liste di coppie chiave/valore raggruppandole in categorie aumenta la leggibilità della configurazione. Gli header di categoria cominciano con un punto (.). Il listato 5-9 mostra un esempio di categorie.

Listato 5-9 - Gli header di categoria sembrano chiavi, ma cominciano con un un punto

    all:
        .general:
          tax:    19.6

          mail:
            webmaster: webmaster@example.com

In questo esempio, `mail` è una chiave e `general` è solo un header di categoria. Tutto funziona come se l'header non esistesse, come mostrato nel listato 5-10. Il parametro `tax` è effettivamente un figlio diretto della chiave `all`. Tuttavia l'uso delle categorie aiuta symfony a trattare con gli array che sono sotto la chiave `all`.

Listato 5-10 - Gli header di categoria esistono solo per una questione di leggibilità e sono in effetti ignorati

    all:
      tax: 19.6

    mail:
      webmaster: webmaster@example.com

>**SIDEBAR**
>E se YAML non va bene...
>
>YAML è solo un'interfaccia per definire impostazioni utilizzate da PHP, per cui le configurazioni YAML finiscono per essere trasformate in PHP. Dopo aver navigato la propria applicazione, se ne può controllare la configurazione in cache (ad esempio in cache/frontend/dev/config/). Si vedranno file PHP corrispondenti alle configurazioni YAML. Altre informazioni sulla cache di configurazione sono disponibili più avanti in questo capitolo.
>
>La buona notizia è che se non si vuole usare YAML, si può fare la stessa cosa a mano in PHP o con altri formati (come XML o INI). Nel corso di questo libro, vedremo modi alternativi per definire configurazioni senza YAML e impareremo anche come sostituire il gestore di configurazioni di symfony (nel capitolo 19). Usati largamente, questi trucchi permetteranno di aggirare i file di configurazione o definire il proprio personale formato di configurazione.

### Aiuto, un file YAML ha ucciso l'applicazione!

I file YAML sono analizzati e trasformati in array o hash PHP, quindi i valori sono usati in varie parti dell'applicazione per modificare il comportamento delle viste, del controller o del modello. Molte volte, quando c'è un errore in un file YAML, esso non viene riconosciuto fino a che il valore non è effettivamente necessario. Ancora più spesso, l'eccezione che viene generata non si riferisce chiaramente al file YAML.

Se la propria applicazione smette improvvisamente di funzionare dopo un cambio di configurazione, occorre controllare di non aver fatto qualcuno dei più comuni errori di disattenzione con YAML:

  * Manca uno spazio tra una chiave e il suo valore:

        key1:value1 # Manca uno spazio bianco dopo :

  * Le chiavi in una sequenza non sono indentate nella stessa maniera:

        all:
          key1: value1
           key2: value2 # L'indentazione è diversa da quella degli altri  membri della sequenza
	  key3: value3

  * C'è un carattere riservato in una chiave o un valore, senza delimitatori di stringa:

        message: tell him: go way   # :, [, ], { and } sono riservate in YAML
        message: 'tell him: go way' # sintassi corretta

  * Si sta modificando una linea commentata:

        # key: value # Questa linea è ignorata perché comincia con #

  * Si stanno impostando dei valori con la stessa chiave allo stesso livello:

        key1: value1
        key2: value2
        key1: value3 # key1 è definita due volte, il valore è l'ultimo inserito

  * Si ritiene che un valore sia un tipo speciale, mentre resta una stringa fino a che non sarà convertita:

        income: 12,345 # Ancora una stringa, fino a che non sarà convertita

Riepilogo sui file di configurazione
------------------------------------

La configurazione è suddivisa in file, per oggetto. Questi file contengono definizioni di parametri o impostazioni. Alcuni di tali parametri possono essere sovrascritti a diversi livelli (progetto, applicazione e modulo), altri sono specifici di un certo livello. I prossimi capitoli prenderanno in esame le configurazioni relativamente alle loro finalità principali, mentre il capitolo 19 esaminerà le configurazioni avanzate.

### Configurazione di progetto

Ci sono pochi file di configurazione predefiniti per il progetto. Di seguito quelli che si trovano nella cartella `progetto/config/`:

  * `ProjectConfiguration.class.php`: Questo è assolutamente il primo file incluso da ogni richiesta o comando. Contiene i percorsi ai file del framework e può essere cambiato per usare un'installazione diversa. Vedere il capitolo 19 per usi avanzati di questo file.
  * `databases.yml`: Qui è dove si definisce l'accesso e la connessione al database (host, login, password, nome del database e così via). Vedremo di più su questo nel capitolo 8. Può essere sovrascritto a livello di applicazione.
  * `properties.ini`: Questo file gestisce parametri utilizzati a linea di comando, inclusi il nome del progetto e le impostazioni di connessione a server remoti. Vedere il capitolo 16 per un sommario delle caratteristiche di utilizzo di questo file.
  * `rsync_exclude.txt`: Questo file specifica quali cartelle e file devono essere esclusi dalla sincronizzazione tra server. È discusso nel capitolo 16.
  * `schema.yml`: Si tratta del file di configurazione per l'accesso ai dati usato da Propel e Doctrine (gli ORM di symfony). Esso è usato per far funzionare le librerie dell'ORM con le classi di symfony e i dati del progetto. Il file `schema.yml` contiene una rappresentazione del modello relazionale del progetto. Per Doctrine, il file è in `config/doctrine/`.

Questi file sono usati per lo più da componenti esterni o dalla linea di comando o devono essere processati prima che il framework carichi il programma di analisi YAML. Ecco perché alcuni di essi non usano il formato YAML.

### Configurazione dell'applicazione

La maggior parte della configurazione è occupata dall'applicazione. È definita nel front controller (nella cartella `web/`) per la configurazione principale, in file YAML nella cartella `config/` dell'applicazione, in `i18n/` per l'internazionalizzazione e infine nei file del framework per una invisibile (ma sempre utile) configurazione ulteriore dell'applicazione.

#### Configurazione del front controller

La prima configurazione dell'applicazione in assoluto si trova nel front controller. Si tratta del primo script eseguito da una richiesta. Si veda codice di `web/index.php` mostrato nel listato 5-11.

Listato 5-11 - Il front controller predefinito

    [php]
    <?php
    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

Dopo aver definito il nome dell'applicazione (`frontend`), l'ambiente (`prod`) e la modalità di debug (`false`), la configurazione dell'applicazione è chiamata prima di creare un contesto e del dispatch. Per cui qualche metodo utile è disponibile nella classe di configurazione dell'applicazione:

  * `$configuration->getRootDir()`: La cartella radice del progetto (di norma dovrebbe rimanere come è, a meno che non si voglia cambiare la struttura delle cartelle). 
  * `$configuration->getApplication()`: Nome dell'applicazione nel progetto. Necessario per calcolare i percorsi dei file.
  * `$configuration->getEnvironment()`: Nome dell'ambiente (`prod`, `dev` o qualsiasi altro ambiente specifico creato ad hoc per il progetto). Determinerà quali sono le impostazioni di configurazione da utilizzare. Gli ambienti sono spiegati più avanti in questo capitolo. 
  * `$configuration->isDebug()`: Attivazione della modalità di debug (vedere il capitolo 16 per i dettagli). 

Se si vuole cambiare uno di questi valori, probabilmente occorrerà di un front controller addizionale. Il prossimo capitolo spiegherà più nel dettaglio i front controller e come crearne di nuovi. 

#### Configurazione dell'applicazione principale

La configurazione dell'applicazione principale è memorizzata in file che si trovano nella cartella `progetto/apps/frontend/config/`: 

  * `app.yml`: Questo file dovrebbe contenere configurazioni specifiche all'applicazione; ad esempio variabili globali che definiscono business logic, che non hanno bisogno di essere memorizzate nel db. Tasse, costi di spedizione, indirizzi e-mail sono memorizzati spesso in questo file, che di base è vuoto.
  * `frontendConfiguration.class.php`: questa classe si occupa del bootstrap dell'applicazione, quindi fa tutte le inizializzazioni di base necessarie all'applicazione per partire. È qui che si può definire una struttura di cartelle particolare, oppure delle costanti specifiche (il capitolo 19 ne fornirà maggiori dettagli). Estende la classe `ProjectConfiguration`. 
  * `factories.yml`: symfony definisce le proprie classi per gestire le viste, la richiesta, la risposta, le sessioni e così via. Se invece si vogliono utilizzare le proprie classi personali, questo file è i posto in cui definirle (maggiori informazioni nel capitolo 17). 
  * `filters.yml`: I filtri sono porzioni di codice eseguiti per ogni richiesta. Qui è dove si definisce quali filtri devono essere processati, e può essere sovrascritto in ogni modulo. Il capitolo 6 fornisce maggiori dettagli sui filtri. 
  * `routing.yml`: Le regole di routing, che permettono di trasformare un URL illeggibile in uno più "intelligente", sono definite in questo file. Per nuove applicazioni, esistono poche regole predefinite. Il capitolo 9 è dedicato ai link e al routing.
  * `settings.yml`: Le impostazioni principali di un'applicazione symfony sono definiti in questo file. Qui si specifica se la propria applicazione è internazionalizzata, qual è la lingua di default, il timeout per le richieste e se la cache è attiva o meno. Cambiando una linea di questo file si può "spegnere" la propria applicazione, per poterla aggiornare o manutenere. Le impostazioni più comuni e il loro utilizzo sono approfonditi nel capitolo 19. 
  * `view.yml`: La struttura predefinita della vista (nome del layout, fogli di stile e JavaScript da includere di default, content-type di default e così via) è definita in questo file. Il capitolo 7 approfondirà questo file. Queste impostazioni possono essere sovrascritte in ogni modulo. 

>**TIP**
>Tutti i file di configurazione di symfony sono descritti in dettaglio nella [Guida di riferimento a symfony](http://www.symfony-project.org/reference/1_4/it/).

#### Configurazione dell'internazionalizzazione

Applicazioni internazionalizzate possono mostrare le pagine in diverse lingue. Questo richiede una configurazione specifica. Ci sono due file da configurare per l'internazionalizzazione: 

  * `factories.yml` della cartella `config/` dell'applicazione: Questo file definisce il factory i18n e le opzioni generali di traduzione, come ad esempio la cultura predefinita per la traduzione, se le traduzioni sono in un file o nel database e il loro formato. 
  * File di traduzione nella cartella `i18n/` dell'applicazione: Questi sono fondamentalmente dei dizionari, che forniscono la traduzione delle parole utilizzate nelle template dell'applicazione in modo che le pagine mostrino testo tradotto quando un utente cambia la lingua.
  
Nota che l'attivazione delle feature i18n è impostata nel file `settings.yml`. Approfondimenti a tale proposito nel capitolo 13.

#### Configurazioni addizionali dell'applicazione

Un secondo insieme di file di configurazione è posizionato nella cartella di installazione di symfony in `sfConfig::get('sf_symfony_lib_dir')/config/config/`) e non figura nella cartella di configurazione delle applicazioni. Tali impostazioni raramente hanno bisogno di essere modificate, oppure sono globali a tutti i progetti. Comunque, se si avesse bisogno di modificarle, creare un file vuoto nella cartella `progetto/apps/frontend/config/` e sovrascrivere i parametri da cambiare. Le impostazioni definite nell'applicazione hanno sempre la precedenza rispetto a quelle definite nel framework. Di seguito, i file di configurazione nella cartella `config/` dell'installazione di symfony: 

  * `autoload.yml`: Questo file contiene le impostazioni della funzione di autoloading. Tale funzione ti esonera dall'includere classi personalizzate se esse si trovano in una cartella specifica. Questa funzione è descritta nel capitolo 19.
  * `core_compile.yml`: Queste sono liste di classi da includere per far partire un'applicazione. Queste classi vengono poi concatenate in un file PHP ottimizzato senza commenti, che velocizzerà l'esecuzione minimizzando le operazioni di accesso (è caricato un solo file invece di quaranta per ogni richiesta). Questo risulta specialmente utile se non utilizzi un acceleratore PHP. Le tecniche di ottimizzazione sono descritte nel capitolo 18. 
  * `config_handlers.yml`: Qui si possono aggiungere o modificare i gestori usati per processare ogni file di configurazione. Il capitolo 19 fornisce maggiori dettagli in merito.
  
### Configurazione del modulo

Per impostazione predefinita, un modulo non ha una configurazione specifica. Ma, qualora necessario, si possono sovrascrivere delle impostazioni a livello di applicazione per un dato modulo. Ad esempio, per includere un file JavaScript specifico in tutte le azioni di un modulo. Si può anche scegliere di aggiungere nuovi parametri esclusivamente per un dato modulo al fine di preservare l'incapsulamento. 

Come si può immaginare, le impostazioni di un modulo devono essere nella cartella `progetto/apps/frontend/modules/mymodule/config/`. I file interessati sono i seguenti: 

  * `generator.yml`: Per i moduli generati secondo una tabella di database (scaffolding e amministrazione), questo file descrive il modo in cui le righe e campi devono essere visualizzati e quali tipi di interazioni sono proposti all'utente (filtri, ordinamenti, pulsanti e così via). Il capitolo 14 approfondirà l'argomento. 
  * `module.yml`: Questo file contiene parametri specifici per un modulo (equivalente a `app.yml`, ma a livello di modulo) e la configurazione dell'azione. Il capitolo 6 fornisce maggiori dettagli in merito.
  * `security.yml`: Questo file permette di impostare restrizioni per le azioni. Qui si può impostare il fatto che una certa pagina sia visibile solo agli utenti registrati, oppure a un sottoinsieme di utenti con permessi speciali. Il capitolo 6 fornisce maggiori dettagli in merito. 
  * `view.yml`: Questo file contiene le configurazioni delle viste di una o tutte le azioni di un modulo. Permette di sovrascrivere `view.yml` del livello dell'applicazione ed è descritto nel capitolo 7. 

La maggior parte dei file di configurazione dei moduli offrono la possibilità di definire parametri per tutte le viste o tutte le azioni di un modulo, oppure per un loro sottoinsieme. 

>**SIDEBAR**
>Troppi file?
>
>Ci potrebbe sentire travolti dal numero di file di configurazione presenti nell'applicazione. Ma è bene ricordare:
>
>La maggior parte delle volte non si avrò bisogno di cambiare la configurazione, perché le convenzioni prestabilite soddisfano i requisiti più comuni. Ogni file di configurazione corrisponde a una particolare funzione e il prossimo capitolo affronterà dettagliatamente il loro utilizzo una alla volta. Quando ci si focalizza su un singolo file, si può capire chiaramente cosa fa e come è organizzato. Per lo sviluppo web professionale, la configurazione predefinita è spesso non completamente adatta. I file di configurazione permettono di modificare facilmente i meccanismi di symfony senza scrivere codice. Si immagini l'ammontare di codice PHP necessario per raggiungere lo stesso livello di controllo. Se tutti parametri fossero all'interno di un unico file, non solo esso sarebbe illeggibile, ma non ci potrebbe nemmeno essere la possibilità di sovrascrivere parametri a diversi livelli (vedere la sezione "Configurazione a cascata" più avanti in questo capitolo). 
>
>Il sistema di configurazione è uno dei punti di forza di symfony, perché lo rende utilizzabile per qualsiasi tipo di applicazione web e non solamente per quelle per le quali era stato pensato inizialmente. 

Ambienti
--------

Durante lo sviluppo di un'applicazione, si avrà probabilmente bisogno di mantenere in parallelo diversi insiemi di configurazioni. Ad esempio, occorrerà avere disponibili le impostazioni di connessione al database di test per lo sviluppo e avere allo stesso tempo quelli reali per la produzione. Per soddisfare tale fabbisogno, symfony offre diversi ambienti. 

### Cosa è un ambiente?

Una applicazione può girare in diversi ambienti. Tali ambienti condividono lo stesso codice PHP (a parte il front controller) ma possono avere configurazioni completamente differenti. Per ogni applicazione, symfony fornisce tre ambienti: produzione (`prod`), test (`test`) e sviluppo (`dev`). Inoltre, se ne possono aggiungere altri.

Quindi, fondamentalmente, ambienti e configurazioni sono sinonimi. Ad esempio, un ambiente `test` metterà nei log sia avvisi che errori, mentre un `prod` scriverà solo gli errori. La cache è spesso disattivata in ambienti `dev`, ma attivata in `test`  e `prod`. Gli ambienti `dev` e `test` avranno bisogno di dati di test, memorizzati in un database diverso da quello di produzione. Tutti gli ambienti possono convivere sulla stessa macchina, sebbene di solito un server di produzione ospiti esclusivamente un ambiente `prod`. 

In `dev`, le impostazioni di log e debug sono tutte abilitate, dato che la manutenzione in tale ambiente è più importante delle prestazioni. Al contrario, l'ambiente di produzione è ottimizzato per le prestazioni. Una buona regola è quella di navigare sul sito di sviluppo fino a che si è soddisfatti delle funzioni su cui si sta lavorando, quindi passare a quello in produzione per verificarne le prestazioni. 

L'ambiente `test` differisce dagli altri due in modi diversi. Si interagirà con questo ambiente esclusivamente tramite la linea di comando per finalità di test funzionali o script batch. Conseguentemente, l'ambiente `test` è simile a quello di produzione, ma non vi si accede navigando. Esso simula l'utilizzo di cookie e altri componenti specifici di HTTP.

Per cambiare ambiente nel quale si sta navigando l'applicazione, occorre semplicemente cambiare il front controller. Sinora abbiamo visto solo l'ambiente di sviluppo, dato che gli URL utilizzati negli esempi chiamavano sempre il front controller di sviluppo:

    http://localhost/frontend_dev.php/modulo/index

Se si vuol vedere come l'applicazione reagisce in produzione, si può invece chiamare il front controller per tale ambiente: 

    http://localhost/index.php/modulo/index

Se il server web ha `mod_rewrite` abilitato, si possono anche utilizzare le regole di symfony per la riscrittura, definite in `web/.htaccess`. Esse definiscono il front controller di produzione come indice predefinito e permettono di chiamare URL come: 

    http://localhost/modulo/index

>**SIDEBAR**
>Ambienti e server
>
>Non confondere le nozioni di ambiente e server. In symfony, diversi ambienti sono diverse configurazioni e corrispondono a diversi front controller (gli script che gestiscono le richieste). Server diversi corrispondono a diversi nomi a dominio negli URL. 
>
>     http://localhost/frontend_dev.php/modulo/index
>            _________ _______________
>             server     ambiente
>
>Normalmente, gli sviluppatori lavorano alle applicazioni su server di sviluppo, scollegati da Internet e dove le configurazioni possono essere cambiate a piacimento. Quando un'applicazione deve essere pubblicata, i suoi file vengono spostati sul server di sviluppo e resi così pubblici. 
>
>Questo significa che diversi ambienti sono disponibili su ogni server. Ad esempio, si potrebbe avere sulla stessa macchina ambiente di produzione e di sviluppo. Comunque, la maggior parte delle volte, sul server di produzione ci dovrebbe essere esclusivamente l'ambiente di produzione, per evitare che la configurazione del server sia visibile pubblicamente e quindi ponga dei rischi di sicurezza. Per prevenire l'esposizione accidentale dei controller che non siano di produzione, symfony aggiunge un semplice controllo dell'IP, che consente l'accesso solo da localhost. Se si vuole che siano accessibili, si può rimuovere questo controllo, ma occorre considerare i rischi di renderli accessibili a chiunque, poiché un utente malintenzionato potrebbe indovinare la posizione di `frontend_dev.php` e avere accesso a molte informazioni sensibili.
>
>Per aggiungere un ambiente, non serve creare una cartella o usare la linea di comando di symfony. Creare semplicemente un nuovo front controller e cambiare il nome dell'ambiente. Questo ambiente erediterà tutte le configurazioni predefinite, più i parametri comuni a tutti gli ambienti. Il prossimo capitolo spiegherà come farlo. 

### Configurazione a cascata

Le stesse impostazioni possono essere definite più di una volta, in posti diversi. Ad esempio, si potrebbe volere impostare il tipo mime delle pagine come `text/html` per tutte le applicazioni, tranne per le pagine di un modulo `rss`, che dovrebbe invece avere un tipo mime `text/xml`. Symfony dà la possibilità di scrivere la prima impostazione in `frontend/config/view.yml` e la seconda in `frontend/modules/rss/config/view.yml`. Il sistema di configurazione sa che un'impostazione definita a livello di modulo ha la precedenza rispetto alla stessa definita a livello di applicazione.

Infatti, ci sono diversi livelli di configurazione in symfony:

  * Livelli di granularità:
    * Configurazione predefinita del framework
    * Configurazione globale per l'intero progetto (in `progetto/config/`)
    * Configurazione locale per un'applicazione del progetto (in `progetto/apps/frontend/config/`)
    * Configurazione ristretta a un modulo (in `progetto/apps/frontend/modules/modulo/config/`)
  * Livelli ambiente:
    * Specifico per un ambiente
    * Per tutti gli ambienti

Di tutte le proprietà che possono essere personalizzate, diverse sono dipendenti dall'ambiente. Conseguentemente, diversi file di configurazione YAML sono divisi per ambiente, più una sezione di coda per tutti gli ambienti. Il risultato di una tipica configurazione symfony è mostrato nel listato 5-12.

Listato 5-12 - Struttura dei file di configurazione di symfony 

    [yml]
    # impostazioni per l'ambiente di produzione
    prod:
      ...

    # impostazioni per l'ambiente di sviluppo
    dev:
      ...

    # impostazioni per l'ambiente di test
    test:
      ...

    # impostazioni per un ambiente personalizzato
    myenv:
      ...

    # impostazioni valide per tutti gli ambienti
    all:
      ...

In più, il framework stesso definisce dei valori predefinite in file che non sono situati nell'albero del progetto, bensì nella cartella `sfConfig::get('sf_symfony_lib_dir')/config/config/` dell'installazione di symfony. Questa configurazione è mostrata nel listato 5-13 e tali impostazioni sono ereditate da tutte le applicazioni. 

Listato 5-13 - La configurazione predefinita, in `sfConfig::get('sf_symfony_lib_dir')/config/config/settings.yml`

    [yml]
     # Default settings:
     default:
      .actions:
         default_module:        default
         default_action:        index
         ...

Queste configurazioni predefinite sono ripetute nei file di configurazione di progetti, applicazioni e moduli come commenti, come mostrato nel listato 5-14, così che si possa conoscere quali sono i parametri predefiniti e modificarli. 

Listato 5-14 - Configurazione predefinita, ripetuta per informazione, in frontend/config/settings.yml

    [yml]
    #all:
    #  .actions:
    #    default_module:         default
    #    default_action:         index
    #    ...

Ciò significa che una proprietà può essere definita diverse volte e l'effettivo valore è il risultato di una cascata di definizioni. Il valore di un parametro definito in un ambiente specifico ha la precedenza sullo stesso valore definito per tutti gli ambienti, che a sua volta ha la precedenza su quello predefinito. Un parametro definito a livello di modulo ha la precedenza sullo stesso definito a livello di applicazione, che a sua volta ha la precedenza su quello definito a livello di progetto. Tutto ciò è mostrato nella seguente lista di priorità: 

  1. Modulo
  2. Applicazione
  3. Progetto
  4. Ambiente specifico
  5. Tutti gli ambienti
  6. Predefinito

La cache di configurazione
--------------------------

Analizzare i file YAML e avere a che fare con la cascata di configurazioni comporta un grande carico di lavoro per ogni richiesta. Symfony possiede un meccanismo di cache integrato pensato per accelerare le richieste. 

I file di configurazione, in qualsiasi formato, vengono processati da classi speciali, chiamate gestori, che li trasformano in codice PHP processati velocemente. Nell'ambiente di sviluppo, i gestori controllano se ci siano stati cambiamenti nella configurazione a ogni richiesta, per promuovere l'interattività. Essi analizzano i file modificati di recente in modo che si possa vedere un cambiamento in un file YAML immediatamente. Ma nell'ambiente di produzione, questo avviene solo alla prima richiesta, dopodiché il codice PHP processato viene memorizzato in cache per le richieste successive. Le prestazioni sono garantite, dato che ogni richiesta non farà altro che eseguire codice PHP ottimizzato.

Ad esempio, se il file `app.yml` contenesse:

    [yml]
    all:                   # impostazioni per tutti gli ambienti
      mail:
        webmaster:         webmaster@example.com

allora il file `config_app.yml.php`, situato nella cartella `cache/` del progetto, conterrebbe: 

    [php]
    <?php

    sfConfig::add(array(
      'app_mail_webmaster' => 'webmaster@example.com',
    ));

Come conseguenza, il più delle volte, il framework non analizza nemmeno i file YAML, ma utilizza solo la cache. In ogni caso, in ambiente di sviluppo, symfony controllerà sistematicamente le date dei file YAML e dei file in cache, riprocessando solo i file che sono stati modificati dall'ultima richiesta.

Questo rappresenta un grande vantaggio rispetto a molti framework PHP, che invece analizzano i file di configurazione a ogni richiesta, anche in produzione. Diversamente da Java, PHP non condivide un contesto di esecuzione fra le richieste. Per altri framework PHP, possedere la flessibilità di file XML significa colpire le prestazioni, per poter analizzare la configurazione a ogni richiesta. Questo non è il caso di symfony. Grazie al sistema di cache, il sovraccarico dovuto alla configurazione è molto basso.

C'è un'importante conseguenza di questo meccanismo. Se si cambia la configurazione in produzione, occorre forzare il sistema a rileggere i file per poter vedere le modifiche. Per poter fare questo, basta pulire la cache, rimuovendo tutti i file dalla cartella `cache/` oppure richiamando il comando:

    $ php symfony cache:clear

Accedere alla configurazione dal codice
---------------------------------------

Tutti i file di configurazione sono alla fine trasformati in PHP e molte delle impostazioni che contengono sono utilizzate dal framework senza ulteriori interventi. Comunque, potrebbe capitare di dover accedere ad alcune impostazioni direttamente dal codice (in azioni, template, classi e così via). Le impostazioni definite in `settings.yml`, `app.yml` e `module.yml` sono disponibili attraverso una classe speciale chiamata `sfConfig`. 

### La classe `sfConfig`

Si tratta di un registro dei parametri di configurazione, con un semplice metodo getter utilizzabile da qualunque parte dell'applicazione:

    [php]
    // Recuperare un'impostazione
    $parameter = sfConfig::get('param_name', $default_value);

Inoltre è anche possibile definire, o sovrascrivere, un parametro:

    [php]
    // Definire un'impostazione
    sfConfig::set('param_name', $value);

Il nome del parametro è una concatenazione di diversi elementi, separati da un trattino basso, nel seguente ordine:

  * Un prefisso relativo al nome del file di configurazione (`sf_` per `settings.yml`, `app_` per `app.yml` e `mod_` per `module.yml`)
  * La chiave del genitore, se definita, in minuscolo 
  * Il nome della chiave, in minuscolo 

L'ambiente non è incluso, dato che il codice PHP avrà accesso unicamente ai valori definiti per l'ambiente in cui è eseguito.

Ad esempio, se si avesse bisogno di accedere ai parametri definiti in `app.yml` mostrato nel listato 5-15, occorrerà il codice mostrato nel listato 5-16.

Listato 5-15 - Un esempio di configurazione di `app.yml`

    [yml]
    all:
      .general:
        tax:          19.6
      default_user:
        name:         John Doe
      mail:
        webmaster:    webmaster@example.com
        contact:      contact@example.com
    dev:
      mail:
        webmaster:    dummy@example.com
        contact:      dummy@example.com

Listato 5-16 - Accedere alle impostazioni di configurazione di PHP nell'ambiente `dev`

    [php]
    echo sfConfig::get('app_tax');   // Ricorda che gli header di categoria sono ignorati
     => '19.6'
    echo sfConfig::get('app_default_user_name');
     => 'John Doe'
    echo sfConfig::get('app_mail_webmaster');
     => 'dummy@example.com'
    echo sfConfig::get('app_mail_contact');
     => 'dummy@example.com'

In tal maniera, le impostazioni di configurazione di symfony hanno tutti i vantaggi delle costanti di PHP, ma non gli svantaggi, in quanto i loro valori possono essere modificati.

Tenendo a mente questo concetto, il file `settings.yml`, nel quale si possono impostare i parametri del framework per l'applicazione, è equivalente a una lista di chiamate `sfConfig::set()`. Il listato 5-17 è interpretato come nel listato 5-18.

Listato 5-17 - Estratto di `settings.yml`

    [yml]
    all:
      .settings:
        csrf_secret:       FooBar
        escaping_strategy: true
        escaping_method:   ESC_SPECIALCHARS

Listato 5-18 - Ciò che symfony produce quando analizza `settings.yml`

    [php]
    sfConfig::add(array(
      'sf_csrf_secret' => 'FooBar',
      'sf_escaping_strategy' => true,
      'sf_escaping_method' => 'ESC_SPECIALCHARS',
    ));

Il significato dei parametri del file `settings.yml` è spiegato nel capitolo 19.

### Impostazioni dell'applicazione personalizzati e `app.yml`

La maggior parte delle impostazioni delle funzioni di un'applicazione dovrebbero essere definiti in `app.yml`, dentro la cartella `progetto/apps/frontend/config/`. Questo file dipende dall'ambiente e di base è vuoto. Impostare in questo file tutti i parametri che si vogliono cambiare facilmente e usare la classe `sfConfig` per accedere a tali parametri dal codice. Il listato 5-19 mostra un esempio.

Listato 5-19 - Un esempio di app.yml per definire operazioni su carta di credito 
    
    [yml]
    all:
      creditcards:
        fake:             false
        visa:             true
        americanexpress:  true

    dev:
      creditcards:
        fake:             true

Per sapere se una carta di credito è accettata nell'ambiente corrente, controllare il valore di: 

    [php]
    sfConfig::get('app_creditcards_fake');

>**NOTE**
>Quando occorre richiedere un array PHP direttamente sotto la chiave `all`, occorre usare un header di categoria, altrimenti symfony renderà disponibili i valori separatamente come mostrato sopra.
>
>    [yml]
>     all:
>       .array:
>         creditcards:
>           fake:             false
>           visa:             true
>           americanexpress:  true
>
>     [php]
>     print_r(sfConfig::get('app_creditcards'));
>
>     Array(
>       [fake] => false
>       [visa] => true
>       [americanexpress] => true
>     )

--

>**TIP**
>Ogni qualvolta si è tentati di definire una costante o un parametro in uno script, considerare se non possa essere meglio metterlo nel file `app.yml`. È un posto molto conveniente per memorizzare i parametri dell'applicazione.

Quando servono parametri personalizzati e diventa difficile utilizzare la sintassi di `app.yml`, si può definire una propria sintassi personalizzata. In questo caso, si può memorizzare la configurazione in un nuovo file, interpretato da un nuovo gestore. Il capitolo 19 fornisce maggiori informazioni sui gestori di configurazione. 

Suggerimenti per ottenere di più dai file di configurazione
-----------------------------------------------------------

C'è qualche trucco ancora da imparare prima di scrivere dei file YAML. Servirà per evitare duplicazione della configurazione e per gestire i file YAML.

### Utilizzare costanti nei file YAML

Certi parametri di configurazione si basano sul valore di altri. Per evitare di duplicare i valori, symfony supporta le costanti in YAML. Quando il gestore della configurazione incontra un parametro (accessibile tramite tramite `sfConfig::get()`) in maiuscolo e racchiuso tra simboli `%`, esso lo sostituisce con il suo valore effettivo. Il listato 5-20 ne mostra un esempio.

Listato 5-20 - Utilizzo delle costanti in YAML, esempio da `autoload.yml`

    [yml]
    autoload:
      symfony:
        name:           symfony
        path:           %SF_SYMFONY_LIB_DIR%
        recursive:      on
        exclude:        [vendor]

Il parametro `path` verrà sostituito con il valore di `sfConfig::get('sf_symfony_lib_dir')`. Se si vuole che un file di configurazione si basi su di un altro, occorre essere certi che quello su cui si basa venga analizzato per primo (controllare i sorgenti di symfony per verificare l'ordine in cui i file di configurazione vengono analizzati). `app.yml` è uno degli ultimi file a essere analizzato, per cui si può basare su altri.

Tutte le costanti disponibili sono descritte nella [guida di riferimento a symfony](http://www.symfony-project.org/reference/1_4/it/).

### Utilizzare codice nella configurazione

Potrebbe succedere che la propria configurazione si debba basare su parametri esterni (database o un altro file di configurazione). Per gestire questi casi particolari, viene analizzato il file di configurazione di symfony da PHP prima che essi vengano passati al parser YAML. Questo significa che i file YAML possono contenere codice PHP, come mostrato nel listato 5-21.

Listato 5-21 - I file YAML possono contenere codice PHP

    [yml]
    all:
      translation:
        format: <?php echo (sfConfig::get('sf_i18n') === true ? 'xliff' : null)."\n" ?>

Si faccia attenzione al fatto che l'analisi di questi file viene eseguita molto presto durante il ciclo di vita di una richiesta, per cui non si avranno a disposizione i metodi o le funzioni di symfony.

Inoltre, siccome il costrutto `echo` non aggiunge un ritorno a capo, è necessario aggiungere un "\n" oppure utilizzare l'helper `echoln` per mantenere valido il formato YAML.

    [yml]
    all:
      translation:
        format:  <?php echoln(sfConfig::get('sf_i18n') == true ? 'xliff' : 'none') ?>

>**CAUTION**
>Nell'ambiente di produzione la configurazione è in cache, per cui l'analisi (e l'esecuzione) dei file di configurazione avviene esclusivamente dopo che la cache è stata pulita.

### Navigare i propri file YAML personali

Ogni qualvolta si voglia leggere un file YAML direttamente, si può usare la classe `sfYaml`. Si tratta di un parser YAML che trasforma i file in array associativi di PHP. Il listato 5-22 presenta un file YAML di esempio, mentre il listato 5-23 mostra come analizzarlo.

Listato 5-22 - File di esempio `test.yml`

    [yml]
    house:
      family:
        name:     Doe
        parents:  [John, Jane]
        children: [Paul, Mark, Simone]
      address:
        number:   34
        street:   Main Street
        city:     Nowheretown
        zipcode:  12345

Listato 5-23 - Utilizzo della classe `sfYaml` per trasformare il file precedente in array associativo

    [php]
    $test = sfYaml::load('/path/to/test.yml');
    print_r($test);

    Array(
      [house] => Array(
        [family] => Array(
          [name] => Doe
          [parents] => Array(
            [0] => John
            [1] => Jane
          )
          [children] => Array(
            [0] => Paul
            [1] => Mark
            [2] => Simone
          )
        )
        [address] => Array(
          [number] => 34
          [street] => Main Street
          [city] => Nowheretown
          [zipcode] => 12345
        )
      )
    )

Riepilogo
---------

Il sistema di configurazione di symfony utilizza il linguaggio YAML per poter essere semplice e leggibile. La capacità di gestire ambienti multipli e di definire insiemi di parametri tramite cascata offre grande versatilità allo sviluppatore. Alcuni dei parametri sono accessibili via codice tramite l'oggetto `sfConfig`, specialmente quelli specifici dell'applicazione memorizzati nel file `app.yml`.

È vero, symfony ha molti file di configurazione, ma questo approccio lo rende molto adattabile. Ricordare che non occorre annoiarsi con essi se non occorre un alto livello di personalizzazione.

