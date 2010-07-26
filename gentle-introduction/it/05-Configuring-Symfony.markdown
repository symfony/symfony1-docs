Capitolo 5 - Configurare symfony
================================

Per essere semplice da usare, symfony definisce alcune convenzioni, che devono soddisfare i requisiti più comuni nello sviluppo web senza bisogno di modifiche. Comunque, utilizzando un insieme di semplici e potenti file di configurazione, è possibile personalizzare il modo in cui il framework e la tua applicazione interagiscono fra loro. Con questi file di configurazione, potrai anche aggiungere parametri speciali alla tua applicazione.

Questo capitolo spiega come funziona il sistema di configurazione:

  * La configurazione di symfony è memorizzata in file scritti in YAML, anche se è sempre possibile scegliere un altro formato.
  * Nella struttura di cartelle del progetto, i file di configurazione si trovano ai livelli progetto, applicazione e modulo.
  * Puoi definire diversi insiemi di impostazioni; in symfony, un insieme di configurazioni è chiamato ambiente.
  * I valori definiti nei file di configurazione sono disponibili al codice PHP della tua applicazione.
  * Inoltre, symfony permette l'utilizzo di codice PHP e altri trucchi all'interno dei file di configurazione YAML, per rendere il sistema di configurazione ancora più flessibile.

Il Sistema di configurazione
----------------------------

A parte lo scopo, la maggior parte delle applicazioni web condivide un insieme di caratteristiche comuni. Ad esempio, qualche sezione può avere accesso ristretto a un certo insieme di utenti, oppure le pagine possono essere decorate da un layout, o ancora la possibilità di avere le form già riempite dopo una validazione fallita. Un framework definisce una struttura per simulare queste caratteristiche e gli sviluppatori possono ulteriormente modificarle cambiando i file di configurazione. Questa strategia fa risparmiare molto tempo durante lo sviluppo, dato che molti cambiamenti non necessitano di alcuna linea di codice, anche se ce n'è molto dietro. Questo sistema è anche molto efficiente, perché assicura che queste informazioni siano reperibili sempre in un punto unico e facile da trovare.

Questo approccio ha però due seri svantaggi:

  * Gli sviluppatori di solito finiscono per scrivere complessi file XML senza fine.
  * In un'architettura PHP, ogni richiesta necessita di più tempo per essere eseguita.

Tenendo conto di questi svantaggi, symfony utilizza file di configurazione solo dove sono veramente necessari. L'ambizione del sistema di configurazione di symfony è di essere:

  * Potente: ogni aspetto che possa essere gestito tramite file di configurazione lo è veramente.
  * Semplice: diversi parametri di configurazione non sono mostrati in una normale applicazione, in quanto raramente necessitano di essere modificati.
  * Facile: gli sviluppatori troveranno facile leggere, creare e modificare file di configurazione.
  * Personalizzabile: il linguaggio di configurazione di default è YAML, ma puo' essere INI, XML, o qualsiasi altro formato lo sviluppatore preferisca.
  * Veloce: i file di configurazione non vengono processati dall'applicazione ma dal sistema di configurazione, che li compila velocemente in parti di codice PHP sul server.

### Sintassi YAML e convenzioni di symfony

Per la propria configurazione, symfony utilizza il formato YAML, invece dei più tradizionali INI e XML. YAML mostra la struttura tramite indentazione ed è veloce da scrivere. I vantaggi e le regole di base sono già state mostrate nel Capitolo 1. Comunque, devi tenere a mente qualche convenzione quando vuoi scrivere file di YAML. Questa sezione introduce diverse convenzioni tra le più importanti. Per approfondimenti visita il sito web di YAML [website](http://www.yaml.org/).

Prima di tutto non sono ammessi caratteri di tabulazione in YAML; occorre usare spazi bianchi. I parser YAML non capiscono le tabulazioni, per cui utilizza spazi bianchi per l'indentazione (la convenzione in symfony è di due spazi bianchi), come mostrato nel Listato 5-1.

Listato 5-1 - YAML vieta l'utilizzo delle tabulazioni

    # Mai usare tabs
    all:
    -> mail:
    -> -> webmaster:  webmaster@example.com

    # Usare solo spazi
    all:
      mail:
        webmaster: webmaster@example.com


Se i tuoi parametri sono stringhe che cominciano o finiscono con spazi vuoti, contengono caratteri speciali (come # o ,), o sono parole chiave come "true, false" (intese come una stringa), devi incapsulare il valore tra singoli apici, come mostrato nel Listato 5-2.

Listato 5-2 - Stringhe non standard dovrebbero essere incapsulate tra singoli apici

    error1: This field is compulsory
    error2: '  This field is compulsory  '
    error3: 'Don''t leave this field blank' # i singoli apici devono essere raddoppiati
    error4: 'Enter a # symbol to define an extension number'
    i18n:   'false' # se togliamo gli apici, viene restituito un valore booleano false


Puoi definire stringhe lunghe in più righe e anche stringhe con più di una linea, con gli header speciali di stringa (> e |) più una indentazione addizionale. Il Listato 5-3 illustra questa convenzione.

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

Per definire un valore come array, racchiudi gli elementi tra parentesi quadre, oppure usa la sintassi estesa con i trattini, come mostrato nel Listato 5-4.

Listato 5-4 - Sintassi di array in YAML

    # Sintassi abbreviata per gli array
    players: [ Mark McGwire, Sammy Sosa, Ken Griffey ]

    # Sintassi espansa per gli array
    players:
      - Mark McGwire
      - Sammy Sosa
      - Ken Griffey

Per definire un valore come array associativo, o hash, racchiudi gli elementi tra parentesi graffe e inserisci sempre uno spazio tra la chiave e il valore nella coppia 'key: value' e separa gli elementi della lista con delle virgole. Puoi anche utilizzare la sintassi estesa, aggiungendo indentazione e ritorno a capo per ogni chiave, come mostrato nel Listato 5-5.

Listato 5-5 - Array associativi in YAML

    # Sintassi errata: mancano gli spazi dopo i duepunti e la virgola
    mail: {webmaster:webmaster@example.com,contact:contact@example.com}

    # Sintassi abbreviata corretta per gli array associativi
    mail: { webmaster: webmaster@example.com, contact: contact@example.com }

    # Sintassi estesa per gli array associativi
    mail:
      webmaster: webmaster@example.com
      contact: contact@example.com


Per assegnare un valore booleano, utilizza i valori `false` e `true`
senza apici.

Listato 5-6 - Sintassi YAML per valori booleani

    true_value: true
    false_value: false

Non esitare ad aggiungere commenti (che devono cominciare con il cancelletto #) e spazi aggiuntivi, per rendere ancora più leggibili tuoi file YAML, come mostrato nel Listato 5-7.

Listato 5-7 - Sintassi dei commenti e allineamento dei valori in YAML

    # Questa è una linea di commento
    mail:
      webmaster: webmaster@example.com
      contact:   contact@example.com
      admin:     admin@example.com # gli spazi aggiuntivi permettono un migliore allineamento dei valori

In qualche file di configurazione di symfony, ti capiterà di trovare delle linee che cominciano con un cancelletto (per cui ignorate dal parser YAML) e che assomigliano a normali linee di impostazioni. Questa è una convenzione di symfony: la configurazione di default, ereditata da altri file YAML che si trovano nel core, è ripetuta in linee commentate nella tua applicazione per pura informazione. Se vuoi cambiare uno di tali parametri, devi innanzitutto decommentare la linea, come mostrato nel Listato 5-8.

Listato 5-8 - La configurazione di default è mostrata commentata

    # La cache è false per default
    settings:
    # cache: false

    # Se vuoi cambiare questa impostazioni, decommenta la linea
    settings:
      cache: true

Qualche volta symfony raggruppa le definizioni dei parametri in categorie. Tutte le impostazioni relative a una data categoria appaiono indentati sotto il suo header. Strutturare lunghe liste di coppie chiave: valore raggruppandole in categorie aumenta la leggibilità della configurazione. Gli header di categoria cominciano con un punto (.). Il Listato 5-9 mostra un esempio di categorie.

Listato 5-9 - Gli header di categoria sembrano chiavi, ma cominciano con un un punto

    all:
        .general:
          tax:    19.6

          mail:
            webmaster: webmaster@example.com

In questo esempio, mail è una chiave e general è solo un header di categoria. Tutto funziona come se l'header non esistesse, come mostrato nel Listato 5-10. Il parametro tax è effettivamente un figlio diretto della chiave all. Tuttavia l'uso delle categorie aiuta symfony a trattare con gli array che sono sotto la chiave all.

Listato 5-10 - Gli header di categoria esistono solo per una questione di leggibilità e sono in effetti ignorati

    all:
      tax: 19.6

    mail:
      webmaster: webmaster@example.com

>**SIDEBAR**
>E se non ti piace YAML...
>
>YAML è solo un'interfaccia per definire impostazioni utilizzate da PHP, per cui le configurazioni YAML finiscono per essere trasformate in PHP. Dopo aver navigato la tua applicazione, controllane la configurazione in cache (ad esempio in cache/frontend/dev/config/). Vedrai file PHP corrispondenti alle configurazioni YAML. Imparerai di più sulla cache di configurazione più avanti in questo capitolo.
>
>La buona notizia è che se non vuoi usare YAML, puoi fare la stessa cosa a mano in PHP o con altri formati (come XML o INI). Nel corso di questo libro, incontrerai modi alternativi per definire configurazioni senza YAML e imparerai anche come sostituire il gestore di configurazioni di symfony (nel Capitolo 19). Se li usi largamente, questi trucchi ti permetteranno di bypassare i file di configurazione o definire il tuo personale formato di configurazione.

### Aiuto, un file YAML ha ucciso la mia applicazione!

I file YAML sono parsati in array o hash PHP, quindi i valori sono usati in varie parti dell'applicazione per modificare il comportamento delle viste, del controller o del modello. Molte volte, quando c'è un errore in un file YAML, esso non viene riconosciuto fino a che il valore non è effettivamente necessario. Ancora più spesso, l'eccezione che viene generata non si riferisce chiaramente al file YAML.

Se la tua applicazione smette improvvisamente di funzionare dopo un cambio di configurazione, devi controllare di non aver fatto qualcuno dei più comuni errori di disattenzione con YAML:

  * Ti manca uno spazio tra una chiave e il suo valore:

        key1:value1 # Manca uno spazio bianco dopo :

  * Le chiavi in una sequenza non sono indentate nella stessa maniera:

        all:
          key1: value1
           key2: value2 # L'indentazione è diversa da quella degli altri  membri della sequenza
	  key3: value3

  * C'è un carattere riservato in una chiave o un valore, senza delimitatori di stringa:

        message: tell him: go way   # :, [, ], { and } sono riservate in YAML
        message: 'tell him: go way' # sintassi corretta

  * Stai modificando una linea commentata:

        # key: value # Questa linea è ignorata perché comincia con #

  * Imposti dei valori con la stessa chiave allo stesso livello:

        key1: value1
        key2: value2
        key1: value3 # key1 è definita due volte, il valore è l'ultimo inserito

  * Pensi che un valore sia un tipo speciale, mentre resta una stringa fino a che non sarà convertita:

        income: 12,345 # Ancora una stringa, fino a che non sarà convertita

Riepilogo sui file di configurazione
-----------------------------------

La configurazione è suddivisa in file, per oggetto. Questi file contengono definizioni di parametri, o impostazioni. Alcuni di tali parametri possono essere sovrascritti a diversi livelli (progetto, applicazione e modulo); altri sono specifici di un certo livello. I prossimi capitoli prenderanno in esame le configurazioni relativamente alle loro finalità principali, mentre il Capitolo 19 esaminerà le configurazioni avanzate.

### Configurazione di progetto

Ci sono per default pochi file di configurazione per progetto. Di seguito quelli che si trovano nella cartella myproject/config/:

  * `ProjectConfiguration.class.php`: Questo è assolutamente il primo file incluso da ogni richiesta o comando. Contiene i percorsi ai file del framework, e può essere cambiato per usare un'installazione diversa. Vedi il Capitolo 19 per usi avanzati di questo file.
  * `databases.yml`: Qui è dove definisci l'accesso e la connessione al database (host, login, password, nome del database, e così via). Imparerai di più su questo nel Capitolo 8. Può essere sovrascritto a livello applicazione.
  * `properties.ini`: Questo file gestisce parametri utilizzati a linea di comando, inclusi il nome del progetto e le impostazioni di connessione a server remoti. Vedi il Capitolo 16 per un sommario delle caratteristiche di utilizzo di questo file.
  * `rsync_exclude.txt`: Questo file specifica quali cartelle e file devono essere esclusi dalla sincronizzazione tra server. È discusso nel Capitolo 16.
  * `schema.yml`: Si tratta del file di configurazione per l'accesso ai dati usato da Propel e Doctrine (il livello ORM di symfony). Esso è usato per far funzionare le librerie dell'ORM con le classi di symfony e i dati del tuo progetto. schema.yml contiene una rappresentazione del modello relazionale del progetto.

Questi file sono usati per lo più da componenti esterni o dalla linea di comando, o devono essere processati prima che il framework carichi il programma di parsing YAML. Ecco perché alcuni di essi non usano il formato YAML.

### Configurazione dell'applicazione

La maggior parte della configurazione è occupata dall'applicazione. È definita nel front controller (nella cartella web/) per la configurazione principale, in file YAML nella cartella config/ dell'applicazione, in i18n/ per l'internazionalizzazione e infine nei file del framework per una invisibile (ma sempre utile) configurazione addizionale dell'applicazione.

#### Configurazione del front controller

La prima configurazione dell'applicazione in assoluto si trova nel front controller; si tratta del primo script eseguito da una richiesta. Dai un'occhiata al codice di web/index.php mostrato nel Listato 5-11.

Listato 5-11 - Il front controller di default 

    [php]
    <?php
    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

Dopo aver definito il nome dell'applicazione (frontend), l'ambiente (prod) e il modo di debug (false), la configurazione dell'applicazione è chiamata prima di creare un contesto e del dispatching. Per cui qualche metodo utile è disponibile nella classe di configurazione dell'applicazione:

  * `$configuration->getRootDir()`: La cartella di root del progetto (di norma dovrebbe rimanere come è di default, a meno che tu non voglia cambiare la struttura delle cartelle). 
  * `$configuration->getApplication()`: Nome dell'applicazione nel progetto. Necessario per computare i percorsi dei file.
  * `$configuration->getEnvironment()`: Nome dell'ambiente (prod, dev, o qualsiasi altro ambiente specifico che tu creerai ad hoc per il tuo progetto). Determinerà quali sono le impostazioni di configurazione da utilizzare. Gli ambienti sono spiegati più avanti in questo capitolo. 
  * `$configuration->isDebug()`: Attivazione della modalità di debug (vedere il Capitolo 16 per i dettagli). 

Se vuoi cambiare uno di questi valori, probabilmente avrai bisogno di un front controller addizionale. Il prossimo capitolo spiegherà più nel dettaglio i front controller e come crearne di nuovi. 

#### Configurazione dell'applicazione principale

La configurazione dell'applicazione principale è memorizzata in file che si trovano nella cartella myproject/apps/frontend/config/: 

  * app.yml: Questo file dovrebbe contenere configurazioni specifiche all'applicazione; ad esempio variabili globali che definiscono business logic o applicativa specifiche, che non hanno bisogno di essere memorizzate nel db. Tasse, costi di spedizione, indirizzi e-mail sono memorizzati spesso in questo file, che di default è vuoto.
  * frontendConfiguration.class.php: questa classe si occupa del bootstrap dell'applicazione, quindi fa tutte le inizializzazioni di base necessarie all'applicazione per partire. È qui che puoi definire una struttura di cartelle particolare, oppure delle costanti specifiche (il Capitolo 19 ne fornirà maggiori dettagli). Estende la classe ProjectConfiguration. 
  * factories.yml: symfony definisce le proprie classi per gestire le viste, la richiesta, la risposta, le sessioni e così via. Se invece vuoi utilizzare le tue classi personali, questo file è i posto in cui definirle (maggiori informazioni nel Capitolo 17). 
  * filters.yml: I filtri sono porzioni di codice eseguiti per ogni richiesta. Qui è dove definisci quali filtri devono essere processati, e può essere sovrascritto in ogni modulo. Il Capitolo 6 fornisce maggiori dettagli sui filtri. 
  * routing.yml: Le regole di routing, che permettono di trasformare una URL illeggibile in una più "intelligente", sono definite in questo file. Per nuove applicazioni, esistono poche regole di default. Il Capitolo 9 è dedicato ai link e al routing.
  * settings.yml: Le impostazioni principali di un'applicazione symfony sono definiti in questo file. Qui è dove specifichi se la tua applicazione è internazionalizzata, qual è la lingua di default, il timeout per le richieste e se la cache è attiva o meno. Cambiando una linea di questo file puoi "spegnere" la tua applicazione, per poterla aggiornare o manutenere. Le impostazioni più comuni e il loro utilizzo sono approfonditi nel Capitolo 19. 
  * view.yml: La struttura della vista di default (nome del layout, fogli di stile e JavaScript da includere di default, content-type di default e così via) è definita in questo file. Il Capitolo 7 approfondirà questo file. Queste impostazioni possono essere sovrascritte in ogni modulo. 

>**TIP**
>Tutti i file di configurazione di symfony sono descritti in dettaglio nella [Guida di riferimento a symfony](http://www.symfony-project.org/reference/1_4/it/).

#### Configurazione dell'internazionalizzazione

Applicazioni internazionalizzate possono mostrare le pagine in diverse lingue. Questo richiede una configurazione specifica. Ci sono due file da configurare per l'internazionalizzazione: 

  * factories.yml della cartella config/ dell'applicazione: Questo file definisce il factory i18n e le opzioni generali di traduzione, come ad esempio la cultura di default per la traduzione, se le traduzioni sono in un file o nel database e il loro formato. 
  * File di traduzione nella cartella i18n/ dell'applicazione: Questi sono fondamentalmente dei dizionari, che forniscono la traduzione delle parole utilizzate nelle template dell'applicazione in modo che le pagine mostrino testo tradotto quando un utente cambia la lingua.
  
Nota che l'attivazione delle feature i18n è impostata nel file settings.yml. Approfondimenti a tale proposito nel Capitolo 13.

#### Configurazioni addizionali dell'applicazione

Un secondo insieme di file di configurazione è posizionato nella cartella di installazione di symfony in `sfConfig::get('sf_symfony_lib_dir')/config/config/`) e non figura nella cartella di configurazione delle tue applicazioni. Tali impostazioni sono default che raramente hanno bisogno di essere modificate, oppure che sono globali a tutti i progetti. Comunque, se tu avessi bisogno di modificarle, crea un file vuoto nella cartella myproject/apps/frontend/config/  e sovrascrivi i parametri che vuoi cambiare. Le impostazioni definite nell'applicazione hanno sempre la precedenza rispetto a quelle definiti nel framework. Di seguito i file di configurazione nella cartella config/  dell'installazione di symfony: 

  * autoload.yml: Questo file contiene le impostazioni della funzione di autoloading. Tale funzione ti esonera dall'includere classi personalizzate se esse si trovano in una cartella specifica. Questa funzione è descritta nel Capitolo 19.
  * `core_compile.yml`: Queste sono liste di classi da includere per far partire un'applicazione. Queste classi vengono poi concatenate in un file PHP ottimizzato senza commenti, che velocizzerà l'esecuzione minimizzando le operazioni di accesso (è caricato un solo file invece di quaranta per ogni richiesta). Questo risulta specialmente utile se non utilizzi un acceleratore PHP. Le tecniche di ottimizzazione sono descritte nel Capitolo 18. 
  * config_handlers.yml: Qui puoi aggiungere o modificare i gestori usati per processare ogni file di configurazione. Il Capitolo 19 fornisce maggiori dettagli in merito.
  
### Configurazione del modulo

Per default un modulo non ha una configurazione specifica. Ma, qualora necessario, puoi fare l'override di qualche impostazione del livello applicazione per un dato modulo. Ad esempio, potresti farlo per includere un file JavaScript specifico in tutte le azioni di un modulo. Puoi anche scegliere di aggiungere nuovi parametri esclusivamente per un dato modulo al fine di preservare l'incapsulamento. 

Come puoi immaginare, le impostazioni di un modulo devono essere nella cartella myproject/apps/frontend/modules/mymodule/config/. I file interessati sono i seguenti: 

  * generator.yml: Per i moduli generati secondo una tabella di database (scaffolding e amministrazione), questo file descrive il modo in cui le righe e campi devono essere visualizzati e quali tipi di interazioni sono proposti all'utente (filtri, ordinamenti, pulsanti e cosi' via). Il Capitolo 14 approfondira' l'argomento. 
  * module.yml: Questo file contiene parametri specifici a un modulo (equivalente a app.yml, ma a livello modulo) e la configurazione dell'azione. Il Capitolo 6 fornisce maggiori dettagli in merito.
  * security.yml: Questo file permette di impostare restrizioni per le azioni. Qui puoi impostare il fatto che una certa pagina sia visibile solo agli utenti registrati, oppure a un sottoinsieme di utenti con permessi speciali. Il Capitolo 6 fornisce maggiori dettagli in merito. 
  * view.yml: Questo file contiene le configurazioni delle viste di una o tutte le azioni di un modulo. Permette l'override di view.yml  del livello applicazione, ed è descritto nel Capitolo 7. 

La maggior parte dei file di configurazione dei moduli offrono la possibilità di definire parametri per tutte le viste o tutte le azioni di un modulo, oppure per un loro sottoinsieme. 

>**SIDEBAR**
>Troppi file?
>
>Ti potresti sentire travolto dal numero di file di configurazione presenti nell'applicazione. Ma ricorda:
>
>La maggior parte delle volte non avrai bisogno di cambiare la configurazione, perché le convenzioni stabilite per default soddisfano i requisiti più comuni. Ogni file di configurazione corrisponde a una particolare funzione e il prossimo capitolo le affronterà dettagliatamente il loro utilizzo una alla volta. Quando ti focalizzi su di un singlo file, puoi capire chiaramente cosa fa e come è organizzato. Per lo sviluppo web professionale, la configurazione di default è spesso non completamente adatta. I file di configurazione permettono di modificare facilmente i meccanismi di symfony senza scrivere codice. Immagina l'ammontare di codice PHP necessario per raggiungere lo stesso livello di controllo. Se tutti parametri fossero all'interno di un unico file, non solo esso sarebbe illeggibile, ma non ci potrebbe nemmeno essere la possibilità di fare override di parametri a divresi livelli (v. la sezione "Configurazione a cascata" più avanti in questo capitolo). 
>
>Il sistema di configurazione è uno dei punti di forza di symfony, perché lo rende utilizzabile per qualsiasi tipo di applicazione web e non solamente per quelle per le quali era stato pensato inizialmente. 

Ambienti
--------

Durante lo sviluppo di un'applicazione, avrai probabilmente bisogno di mantenere in parallelo diversi insiemi di configurazioni. Ad esempio avrai bisogno di avere disponibili le impostazioni di connessione al tuo database di test per lo sviluppo e di avere allo stesso tempo quelli reali per la produzione. Per soddisfare tale fabbisogno, symfony offre diversi ambienti. 

### Cosa è un ambiente?

Una applicazione può girare in diversi ambienti. Tali ambienti condividono lo stesso codice PHP (a parte il front controller) ma possono avere configurazioni completamente differenti. Per ogni applicazione, symfony fornisce tre ambienti: produzione (prod), test (test) e sviluppo (dev). Sei inoltre libero di aggiungerne quanti ne vuoi.

Quindi, fondamentalmente, ambienti e configurazioni sono sinonimi. Ad esempio, un ambiente `test` metterà nei log sia alert che errori, mentre un `prod` scriverà solo gli errori. La cache è spesso disattivata in ambienti dev, ma attivata in test  e prod. Gli ambienti `dev` e `test` avranno bisogno di dati di test, memorizzati in un database diverso da quello di produzione. Tutti gli ambienti possono convivere sulla stessa macchina, sebbene di solito un server di produzione ospiti esclusivamente un ambiente `prod`. 

In `dev`, le impostazioni di log e debug sono tutte abilitate, dato che la manutenzione in tale ambiente è più importante delle performance. Al contrario, l'ambiente di produzione è ottimizzato per le performance per default. Una buona regola è quella di navigare sul sito di sviluppo fino a che sei soddisfatto delle funzioni su cui stai lavorando, quindi passare a quello in produzione per verificarne le performance. 

L'ambiente test differisce dagli altri due in modi diversi. Interagirai con questo ambiente esclusivamente tramite la linea di comando per finalità di test funzionali o scripting batch. Conseguentemente, l'ambiente test è simile a quello di produzione ma non vi si accede navigando. Esso simula l'utilizzo di cookie e altri componenti specifici di HTTP.

Per cambiare ambiente nel quale stai navigando l'applicazione, devi semplicemente cambiare il front controller. Sino a ora hai visto solo l'ambiente di sviluppo, dato che le URL utilizzate negli esempi chiamavano sempre il front controller di sviluppo:

    http://localhost/frontend_dev.php/mymodule/index

Se vuoi vedere come l'applicazione reagisce in produzione, puoi invece chiamare il front controller per tale ambiente: 

    http://localhost/index.php/mymodule/index

Se il tuo web server ha il mod_rewrite abilitato, puoi anche utilizzare le regole di symfony per il rewriting, definite in web/.htaccess. Esse definiscono il front controller di produzione come esecuzione di default e permettono di chiamare URL come: 

    http://localhost/mymodule/index

>**SIDEBAR**
>Ambienti e server
>
>Non confondere le nozioni di ambiente e server. In symfony, diversi ambienti sono diverse configurazioni e corrispondono a diversi front controller (gli script che gestiscono le richieste). Server diversi corrispondono a diversi nomi a dominio nelle URL. 
>
>     http://localhost/frontend_dev.php/mymodule/index
>            _________ _______________
>             server     environment
>
>Normalmente, gli sviluppatori lavorano alle applicazioni su server di sviluppo, scollegati da Internet e dove le configurazioni possono essere cambiate a piacimento. Quando un'applicazione deve essere pubblicata, i suoi file vengono spostati sul server di sviluppo e resi così pubblici. 
>
>Questo significa che diversi ambienti sono disponibili su ogni server. Ad esempio, potresti avere sulla stessa macchina ambiente di produzione e di sviluppo. Comunque, la maggior parte delle volte, sul server di produzione ci dovrebbe essere esclusivamente l'ambiente di produzione, per evitare che la configurazione del server sia visibile pubblicamente e quindi ponga dei rischi di sicurezza. Per prevenire l'esposizione accidentale dei controller che non siano di produzione, symfony aggiunge un semplice controllo dell'IP, che consente l'accesso solo da localhost. Se vuoi che siano accessibili, puoi rimuovere questo controllo, ma devi considerare i rischi di renderli accessibili a chiunque, poiché un utente malintenzionato potrebbe indovinare la posizione di default di frontend_dev.php e avere accesso a molte informazioni sensibili.
>
>Per aggiungere un ambiente, non hai bisogno di create una cartella o usare la symfony CLI. Crea semplicemente un nuovo front controller e cambia il nome dell'ambiente. Questo ambiente erediterà tutte le configurazioni di default più i parametri comuni a tutti gli ambienti. Il prossimo capitolo ti insegnerà come farlo. 

### Configurazione a cascata

Le stesse impostazioni possono essere definite più di una volta, in posti diversi. Ad esempio, potresti volere impostare il mime-type delle tue pagine come `text/html` per tutte le applicazioni tranne per le pagine di un modulo `rss`, che dovrebbe invece avere un mime-type `text/xml`. Symfony ti da la possibilità di scrivere la prima impostazione in `frontend/config/view.yml` e il secondo in `frontend/modules/rss/config/view.yml`. Il sistema di configurazione sa che un'impostazione definita a livello modulo ha la precedenza rispetto alla stessa definito a livello applicazione.

Infatti, ci sono diversi livelli di configurazione in symfony:

  * Livelli di granularità:
    * Configurazione di default situata nel framework
    * Configurazione globale per l'intero progetto (in `myproject/config/`)
    * Configurazione locale per un'applicazione del progetto (in `myproject/apps/frontend/config/`)
    * Configurazione ristretta a un modulo (in `myproject/apps/frontend/modules/mymodule/config/`)
  * Livelli ambiente:
    * Specifico a un ambiente
    * Per tutti gli ambienti

Di tutte le proprietà che possono essere personalizzate, diverse sono dipendenti dall'ambiente. Conseguentemente, diversi file di configurazione YAML sono divisi per ambiente, più una sezione di coda per tutti gli ambienti. Il risultato di una tipica configurazione symfony è mostrato nel Listato 5-12.

Listato 5-12 - Struttura dei file di configurazione di symfony 

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

In più, il framework stesso definisce dei valori di default in file che non sono situati nell'albero del progetto, bensì nella cartella `sfConfig::get('sf_symfony_lib_dir')/config/config/` dell'installazione di symfony. Questa configurazione è mostrata nel Listato 5-13 e tali impostazioni sono ereditate da tutte le applicazioni. 

Listato 5-13 - La configurazione di default, in `sfConfig::get('sf_symfony_lib_dir')/config/config/settings.yml`

     # Default settings:
     default:
      .actions:
         default_module:        default
         default_action:         index
         ...

Queste configurazione di default sono ripetute nei file di configurazione di progetti, applicazione e moduli come commenti, come mostrato nel Listato 5-14, così che tu possa conoscere quali sono i parametri di default e modificarli. 

Listato 5-14 - Configurazione di default, ripetuta per informazione, in frontend/config/settings.yml

    #all:
    #  .actions:
    #    default_module:         default
    #    default_action:         index
    #    ...

Ciò significa che una proprietà può essere definita diverse volte e l'effettivo valore è il risultato di una cascata di definizioni. Il valore di un parametro definito in un ambiente specifico ha la precedenza sullo stesso valore definito per tutti gli ambienti, che a sua volta ha la precedenza su quello definito per default. Un parametro definito a livello modulo ha la precedenza sullo stesso definito a livello applicazione, che a sua volta ha la precedenza su quello definito a livello progetto. Tutto ciò è mostrato nella seguente lista di priorità: 

  1. Modulo
  2. Applicazione
  3. Progetto
  4. Ambiente specifico
  5. Tutti gli ambienti
  6. Default

La cache di configurazione
--------------------------

Fare il parsing di file YAML e avere a che fare con la cascata di configurazioni comporta un grande carico di lavoro per ogni richiesta. Symfony possiede un meccanismo di cache integrato pensato per accelerare le richieste. 

I file di configurazione, in qualsiasi formato, vengono processati da classi speciali, chiamate gestori, che li trasformano in codice PHP processati velocemente. Nell'ambiente di sviluppo, i gestori controllano se ci sono stati cambiamenti nella configurazione a ogni richiesta, per promuovere l'interattività. Essi fanno il parsing dei file modificati di recente in modo che tu possa vedere un cambiamento in un file YAML immediatamente. Ma nell'ambiente di produzione, questo avviene solo alla prima richiesta, dopodiché il codice PHP processato viene memorizzato in cache per le richieste successive. Le performance sono garantite, dato che ogni richiesta non farà altro che eseguire codice PHP ottimizzato.

Ad esempio, se il file app.yml contenesse:

    all:                   # impostazioni per tutti gli ambienti
      mail:
        webmaster:         webmaster@example.com

allora il file config_app.yml.php, situato nella cartella cache/  del tuo progetto, conterrebbe: 

    [php]
    <?php

    sfConfig::add(array(
      'app_mail_webmaster' => 'webmaster@example.com',
    ));

Come conseguenza, il più delle volte, il framework non fa nemmeno il parsing dei file YAML, ma utilizza solo la cache. In ogni caso, in ambiente di sviluppo, symfony controllerà sistematicamente le date dei file YAML e dei file in cache, riprocessando solo il file che sono stati modificati dall'ultima richiesta.

Questo rappresenta un grande vantaggio rispetto a molti framework PHP, che invece fanno il parsing dei file di configurazione a ogni richiesta, anche in produzione. Diversamente da Java, PHP non condivide un contesto di esecuzione fra le richieste. Per altri framework PHP, possedere la flessibilità di file XML significa colpire le performance, per poter fare il parsing della configurazione a ogni richiesta. Questo non è il caso di symfony. Grazie al sistema di cache, il sovraccarico dovuto alla configurazione è molto basso.

C'è un'importante conseguenza di questo meccanismo. Se cambi la configurazione in produzione, devi forzare il sistema a rileggere i file per poter vedere le tue modifiche. Per poter fare questo, basta pulire la cache, rimuovendo tutti i file dalla cartella cache/ oppure richiamando il comando:

    $ php symfony cache:clear

Accedere alla configurazione dal codice
---------------------------------------

Tutti i file di configurazione sono alla fine trasformati in PHP e molte delle impostazioni che contengono sono utilizzate dal framework senza ulteriori interventi. Comunque, ti potrebbe capitare di dover accedere ad alcune impostazioni direttamente dal tuo codice (in azioni, template, classi e così via). Le impostazioni definite in settings.yml, app.yml e module.yml sono disponibili attraverso una classe speciale chiamata sfConfig. 

### la classe sfConfig

Si tratta di un registro dei parametri di configurazione, con un semplice metodo getter utilizzabile da qualunque parte dell'applicazione:

    [php]
    // Recuperare un'impostazione
    $parameter = sfConfig::get('param_name', $default_value);

Inoltre è anche possibile definire, o sovrascrivere, di un parametro:

    [php]
    // Definire un'impostazione
    sfConfig::set('param_name', $value);

Il nome del parametro è una concatenazione di diversi elementi, separati da un trattino basso, nel seguente ordine:

  * Un prefisso relativo al nome del file di configurazione (`sf_` per `settings.yml`, `app_` per `app.yml` e `mod_` per `module.yml`)
  * La chiave del genitore, se definita, in minuscolo 
  * Il nome della chiave, in minuscolo 

L'ambiente non è incluso, dato che il tuo codice PHP avrà accesso unicamente ai valori definiti per l'ambiente in cui è eseguito.

Ad esempio, se tu avessi bisogno di accedere ai parametri definiti in app.yml  mostrato nel Listato 5-15, avrai bisogno del codice mostrato nel Listato 5-16.

Listato 5-15 - Un esempio di configurazione di app.yml

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

Tenendo a mente questo concetto, il file settings.yml, nel quale puoi impostare i parametri del framework per l'applicazione, è equivalente a una lista di chiamate sfConfig::set(). Il Listato 5-17 è interpretato come nel Listato 5-18.

Listato 5-17 - Estratto di `settings.yml`

    all:
      .settings:
        csrf_secret:       FooBar
        escaping_strategy: true
        escaping_method:   ESC_SPECIALCHARS

Listato 5-18 - Ciò che symfony produce quando fa il parsing di settings.yml

    [php]
    sfConfig::add(array(
      'sf_csrf_secret' => 'FooBar',
      'sf_escaping_strategy' => true,
      'sf_escaping_method' => 'ESC_SPECIALCHARS',
    ));

Il significato dei parametri del file settings.yml è spiegato nel Capitolo 19.

### Impostazioni dell'applicazione personalizzati e app.yml

La maggior parte delle impostazioni delle funzioni di un'applicazione dovrebbero essere definiti in app.yml, dentro la cartella myproject/apps/frontend/config/. Questo file dipende dall'ambiente e di default vuoto. Imposta in questo file tutti i parametri che vuoi cambiare facilmente e usa la classe sfConfig  per accedere a tali parametri dal tuo codice. Il Listato 5-19 mostra un esempio.

Listato 5-19 - Un esempio di app.yml per definire operazioni su carta di credito 

    all:
      creditcards:
        fake:             false
        visa:             true
        americanexpress:  true

    dev:
      creditcards:
        fake:             true

Per sapere se una carta di credito è accettata nell'ambiente corrente, controlla il valore di: 

    [php]
    sfConfig::get('app_creditcards_fake');

>**NOTE**
>Quando devi richiedere un array PHP direttamente sotto la chiave `all`, devi usare un header di categoria, altrimenti symfony renderà disponibili i valori separatamente come mostrato sopra.
>
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

>**TIP**
>Ogni qualvolta sei tentato di definire una costante od un parametro nei tuoi script, pensa se non possa essere meglio metterlo nel file `app.yml`. È un posto molto conveniente per memorizzare i parametri dell'applicazione.

Quando hai bisogno di parametri personalizzati e diventa difficile utilizzare la sintassi di `app.yml`, puoi definire una tua sintassi personalizzata. In questo caso, puoi memorizzare la configurazione in un nuovo file, interpretato da un nuovo gestore. Il Capitolo 19 fornisce maggiori informazioni sui gestori di configurazione. 

Suggerimenti per ottenere di più dai file di configurazione
-----------------------------------------------------------

C'è qualche trucco ancora da imparare prima di scrivere i tuoi file YAML. Ti servirà per evitare duplicazione della configurazione e per gestire i tuoi file YAML.

### Utilizzare costanti nei file YAML

Certi parametri di configurazione si basano sul valore di altri. Per evitare di duplicare i valori, symfony supporta le costanti in YAML. Quando il gestore della configurazione incontra un parametro (accessibile tramite tramite `sfConfig::get()`) in maiuscolo e racchiuso tra %, esso lo sostituisce con il suo valore effettivo. Il Listato 5-20 ne mostra un esempio.

Listato 5-20 - Utilizzo delle costanti in YAML, esempio da `autoload.yml`

    autoload:
      symfony:
        name:           symfony
        path:           %SF_SYMFONY_LIB_DIR%
        recursive:      on
        exclude:        [vendor]

Il parametro `path` verrà sostituito con il valore di `sfConfig::get('sf_symfony_lib_dir')`. Se vuoi che un file di configurazione si basi su di un altro, devi essere certo che quello su cui si basa venga parsato per primo (controlla i sorgenti di symfony per verificare l'ordine in cui i file di configurazione vengono parsati). `app.yml` è uno degli ultimi file di cui viene fatto il parsing, per cui si può basare su altri.

Tutte le costanti disponibili sono descritte in  [symfony reference book](http://www.symfony-project.org/reference/1_4/en/).

### Utilizzare codice nella configurazione

Potrebbe succedere che la tua configurazione si debba basare su parametri esterni (database od un altro file di configurazione). Per gestire questi casi particolari, viene fatto il parsing dei file di configurazione di symfony da PHP prima che essi vengano passi al parser YAML. Questo significa che i file YAML possono contenere codice PHP, come mostrato nel Listato 5-21.

Listato 5-21 - I file YAML possono contenere codice PHP

    all:
      translation:
        format: <?php echo (sfConfig::get('sf_i18n') === true ? 'xliff' : null)."\n" ?>

Ma fai attenzione al fatto che il parsing di questi file viene eseguito molto presto durante il ciclo di vita di una richiesta, per cui non avrai a tua disposizione i metodi o funzioni di symfony.

Inoltre, siccome il costrutto `echo` non aggiunge un ritorno a capo di default, è necessario aggiungere un "\n" oppure utilizzare l'helper `echoln` per mantenere valido il formato YAML.

    all:
      translation:
        format:  <?php echoln(sfConfig::get('sf_i18n') == true ? 'xliff' : 'none') ?>

>**CAUTION**
>Nell'ambiente di produzione la configurazione è in cache, per cui il parsing (e l'esecuzione) dei file di configurazione avviene esclusivamente dopo che la cache è stata pulita.

### Navigare i tuoi file YAML personali

Ogni qualvolta tu voglia leggere un file YAML direttamente, puoi usare la classe `sfYaml`. Si tratta di un parser YAML che ne trasforma i file in array associativi di PHP. Il Listato 5-22 presenta un file YAML di esempio, mentre il Listato 5-23 mostra come farne il parsing.

Listato 5-22 - File di esempio `test.yml`

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

È vero, symfony ha molti file di configurazione, ma questo approccio lo rende molto adattabile. Ricorda che non hai bisogno di annoiarti con essi se non hai bisogno di un alto livello di personalizzazione.
