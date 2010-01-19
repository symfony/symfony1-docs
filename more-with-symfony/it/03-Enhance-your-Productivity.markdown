Migliorare la propria produttività
==================================

*Di Fabien Potencier*

L'utilizzo di symfony è già di per se un ottimo modo per aumentare la propria
produttività come sviluppatore web. Naturalmente, tutti sanno che nell'ambiente
di sviluppo le eccezioni di symfony o la barra web degli strumenti per il debug,
permettono di migliorare notevolmente la produttività. Questo capitolo insegnerà
alcuni trucchi e suggerimenti per migliorare ancora di più la produttività,
mediante l'utilizzo di alcune nuove o meno conosciute funzionalità di symfony.

Iniziare più velocemente: personalizzare il processo di creazione del progetto
------------------------------------------------------------------------------

Grazie allo strumento CLI di symfony, la creazione di un nuovo progetto symfony
è veloce come può esserlo digitare il seguente comando:

    $ php /path/to/symfony generate-project foo --orm=Doctrine

Il task `generate:project` genera la struttura predefinita delle cartelle per
il nuovo progetto e crea i file di configurazione con valori predefiniti. Ora
si possono usare altri task di symfony per creare applicazioni, installare plugin,
configurare il modello e altro.

Ma i primi passi per creare un nuovo progetto sono probabilmente sempre gli stessi:
si crea una applicazione principale, si installa un gruppo di plugin, si modificano
a proprio piacimento alcune configurazioni predefinite e così via.

Da symfony 1.3 il processo di creazione di un progetto può essere personalizzato
e automatizzato.

>**NOTE**
>Siccome tutti i task di symfony sono classi, è piuttosto facile personalizzarli
>ed estenderli, escluso però il task `generate:project`. Questo perché quando il task
>è eseguito, non esiste ancora nessun progetto e così non esiste un modo semplice
>per personalizzarlo.
 
Il task `generate:project` prende una opzione `--installer` che è uno script PHP
che verrà eseguito durante il processo di creazione del progetto:

    $ php /path/to/symfony generate:project --installer=/somewhere/my_installer.php

Lo script `/somewhere/my_installer.php` verrà eseguito nel contesto dell'istanza
di `sfGenerateProjectTask`, quindi ha accesso a tutti i metodi che gli permettono
di eseguire il proprio lavoro (richiamandoli tramite l'oggetto `$this`). Le
seguenti sezioni descrivono tutti i metodi disponibili che si possono utilizzare
per personalizzare il processo di creazione del progetto.

>**TIP**
>Se nel `php.ini` si attiva l'accesso URL ai file per la funzione `include()`,
>si può anche passare una URL come programma di installazione (naturalmente
>è necessario stare molto attenti quando si fa questo con uno script di cui non si sa nulla):
>
>      $ symfony generate:project
>      --installer=http://example.com/sf_installer.php

### `installDir()`

Il metodo `installDir()` copia una struttura di cartelle (composte da
sottocartelle e file) nel progetto appena creato:

    [php]
    $this->installDir(dirname(__FILE__).'/skeleton');

### `runTask()`

Il metodo `runTask()` esegue un task. Prende il nome del task e una stringa
che rappresenta i parametri e le opzioni che si desiderano passare ad esso
come parametri:

    [php]
    $this->runTask('configure:author', "'Fabien Potencier'");

I parametri e le opzioni possono essere passati come array:

    [php]
    $this->runTask('configure:author', array('author' => 'Fabien Potencier'));

>**TIP**
>I nomi scorciatoia dei task funzionano come ci si aspetta:
>
>    [php]
>    $this->runTask('cc');

Questo metodo naturalmente può essere utilizzato per installare plugin:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin');

Per installare la versione specifica di un plugin, basta passare le opzioni necessarie:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin', array('release' => '10.0.0', 'stability' => beta'));

>**TIP**
>Per eseguire un task da un plugin appena installato, i task devono
>prima essere ricaricati:
>
>     [php]
>     $this->reloadTasks();

### Logger

Quando viene eseguito lo script di installazione, per dare un riscontro allo sviluppatore,
si può registrare quello che succede nei log, in modo abbastanza semplice:

    [php]
    // un semplice log
    $this->log('Messaggi di installazione');

    // log di un blocco
    $this->logBlock(array('', 'Il programma di installazione matto di Fabien', ''), 'ERROR');

    // log in una sezione
    $this->logSection('install', 'installa alcuni file matti');

### Interazione con l'utente

I metodi `askConfirmation()`, `askAndValidate()` e `ask()` consentono di porre
domande e rendere il processo di installazione configurabile dinamicamente.

Se si ha solo bisogno di una conferma, si può utilizzare il metodo `askConfirmation()`:

    [php]
    if (!$this->askConfirmation('Sei sicuro di voler eseguire questo matto programma di installazione?'))
    {
      $this->logSection('install', 'Hai fatto la scelta giusta!');

      return;
    }

È inoltre possibile fare qualsiasi domanda e ottenere dagli utenti risposte
sotto forma di stringhe, utilizzando il metodo `ask()`:

    [php]
    $secret = $this->ask('Dammi una stringa per il codice CSRF:');

Se si desidera validare la risposta, si può usare il metodo `askAndValidate()`:

    [php]
    $validator = new sfValidatorEmail(array(), array('invalid' => 'hmmm, non sembra una email!'));
    $email = $this->askAndValidate('Per favore, dammi la tua email:', $validator);

### Operazioni sul filesystem

Se si vogliono fare delle modifiche nel filesystem, è possibile accedere all'oggetto
filesystem di symfony:

    [php]
    $this->getFilesystem()->...();

>**SIDEBAR**
>Il processo di creazione della sandbox
>
>La sandbox di symfony è un progetto preconfezionato con una applicazione
>pronta all'uso e un database SQLite preconfigurato. Chiunque può creare una sandbox
>utilizzando il suo script di installazione:
>
>     $ php symfony generate:project --installer=/path/to/symfony/data/bin/sandbox_installer.php
>
>Dare un'occhiata allo script `symfony/data/bin/sandbox_installer.php` per avere
>un esempio funzionante di uno script di installazione.
	
Lo script di installazione è un file PHP come gli altri. Quindi, può fare qualunque
cosa si voglia. Quando si crea un nuovo progetto symfony, invece di eseguire
continuamente sempre gli stessi task, è possibile creare il proprio script di
installazione e modificare l'installazione per il proprio progetto di symfony
nel modo desiderato. Creare un nuovo progetto con un programma di installazione
è molto più rapido, ed inoltre evita di dimenticarsi di alcuni passi. Si possono
anche condividere i propri script di installazione con quelli degli altri!


Sviluppare più velocemente
--------------------------

Dal codice PHP, ai task che utilizzano la CLI, la programmazione richiede molta
digitazione. Vediamo come ridurre questa quantità al minimo indispensabile.

### Scegliere un programma IDE

Usare un IDE aiuta lo sviluppatore ad essere più produttivo in molti modi.

In primo luogo, i più moderni IDE forniscono il completamento del codice. Questo
significa che è sufficiente digitare i primi caratteri del nome di un metodo; ma
significa anche che se non si ricorda il nome di un metodo, non si è costretti a
consultare le API, perché l'IDE proporrà tutti i metodi disponibili dell'oggetto
corrente.

Poi, alcuni di essi, come PHPEdit o Netbeans, conoscono symfony e forniscono
una integrazione più specifica con i progetti di symfony.

>**SIDEBAR**
>Editor di testi
>
>Alcuni utenti preferiscono usare un editor di testo per il loro lavoro di programmazione, 
>soprattutto perché gli editor di testi sono più veloci rispetto agli IDE. Naturalmente,
>gli editor di testo forniscono meno funzionalità di quelli IDE. Ma per i più popolari,
>possono essere usate alcune estensioni e/o plugin per migliorare l'esperienza utente e
>rendere il lavoro più efficiente con PHP e i progetti di symfony.
>
>Ad esempio, molti utenti Linux vogliono usare VIM per qualunque tipo di lavoro.
>Questi possono usare l'estensione [vim-symfony](http://github.com/geoffrey/vim-symfony).
>VIM-symfony è una raccolta di script VIM che integrano symfony
>nel loro editor preferito. Usando vim-symfony, si possono creare facilmente
>macro e comandi di VIM per snellire lo sviluppo con symfony. Ha anche
>un insieme di comandi predefiniti che mettono a disposizione una serie di file di
>configurazione (schema, rotte, ecc) che consentono facilmente di passare dalle azioni
>ai template.
>
>Alcuni utenti MacOS X usano TextMate. È possibile installare il
>[bundle](http://github.com/denderello/symfony-tmbundle); aggiunge un sacco
>di macro e scorciatoie che fanno risparmiare tempo nelle attività giornaliere.

#### Utilizzare un IDE che supporta symfony

Alcuni IDE, come [PHPEdit 3.4](http://www.phpedit.com/en/presentation/extensions/symfony)
e [NetBeans 6.8](http://www.netbeans.org/community/releases/68/), hanno
un supporto nativo a symfony, quindi forniscono una integrazione specifica
con il framework. Per saperne di più, sul loro supporto specifico per symfony e
su come possono aiutare a sviluppare più velocemente, dare un'occhiata alla loro
documentazione specifica.

#### Aiutare l'IDE

Negli IDE, l'autocompletamento del PHP funziona solo per i metodi che sono definiti
esplicitamente nel codice PHP. Ma se il codice utilizza i metodi
`__call()` o `__get()` "magic", gli IDE non hanno modo di capire i metodi o le proprietà
disponibili. La buona notizia è che si può aiutare l'IDE fornendo i metodi e/o le proprietà
in un blocco PHPDoc (utilizzando rispettivamente le annotazioni `@method` e `@property`).

Supponiamo di avere una classe `Message` con una proprietà dinamica (`message`) e
un metodo dinamico (`getMessage()`). Il seguente codice mostra come un IDE può
venirne a conoscenza senza che ci sia una definizione esplicita nel codice PHP:

    [php]
    /**
     * @property clob $message
     *
     * @method clob getMessage() Restituisce il valore corrente di message
     */
    class Message
    {
      public function __get()
      {
        // ...
      }

      public function __call()
      {
        // ...
      }
    }

Anche se il metodo `getMessage()` non esiste, sarà riconosciuto dall'IDE, grazie
alla annotazione `@method`. Lo stesso discorso vale per la proprietà `message`
perché è stata aggiunta l'annotazione `@property`.

Questa tecnica è usata dal task `doctrine:build-model`. Per esempio, una
classe `MailMessage` di Doctrine, con due colonne (`message` e `property`)
risulterebbe così:

    [php]
    /**
     * BaseMailMessage
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @property clob $message
     * @property integer $priority
     * 
     * @method clob        getMessage()  Returns the current record's "message" value
     * @method integer     getPriority() Returns the current record's "priority" value
     * @method MailMessage setMessage()  Sets the current record's "message" value
     * @method MailMessage setPriority() Sets the current record's "priority" value
     * 
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    abstract class BaseMailMessage extends sfDoctrineRecord
    {
        public function setTableDefinition()
        {
            $this->setTableName('mail_message');
            $this->hasColumn('message', 'clob', null, array(
                 'type' => 'clob',
                 'notnull' => true,
                 ));
            $this->hasColumn('priority', 'integer', null, array(
                 'type' => 'integer',
                 ));
        }

        public function setUp()
        {
            parent::setUp();
            $timestampable0 = new Doctrine_Template_Timestampable();
            $this->actAs($timestampable0);
        }
    }

Trovare velocemente la documentazione
-------------------------------------

Dal momento che symfony è un framework con molte caratteristiche, non è
sempre facile ricordare tutte le possibili configurazioni, o tutte le classi
e i metodi che si hanno a disposizione. Come abbiamo visto prima, utilizzando
un IDE si ha un grande aiuto grazie all'autocompletamento. Vediamo come sfruttare
gli strumenti esistenti per trovare le risposte il più velocemente possibile.

### API online

Il modo più veloce per trovare la documentazione di una classe o di un metodo, è
quello di navigare online nelle [API](http://www.symfony-project.org/api/1_3/).

Di maggiore interesse è il motore di ricerca integrato nelle API. Esso consente
di trovare rapidamente una classe o un metodo con poche battiture sulla tastiera.
Nella pagina delle API, basta inserire alcune lettere nella casella di ricerca
e apparirà una finestra in tempo reale con suggerimenti utili.

È possibile cercare digitando l'inizio del nome di una classe:

![Ricerca nelle API](http://www.symfony-project.org/images/more-with-symfony/api_search_1.png "API Search")

o del nome di un metodo:

![Ricerca nelle API](http://www.symfony-project.org/images/more-with-symfony/api_search_2.png "API Search")

o del nome di una classe seguito da `::` per elencare tutti i metodi disponibili:

![Ricerca nelle API](http://www.symfony-project.org/images/more-with-symfony/api_search_3.png "API Search")

o iniziare a scrivere il nome di un metodo per restringere ulteriormente le possibilità:

![Ricerca nelle API](http://www.symfony-project.org/images/more-with-symfony/api_search_4.png "API Search")

Se si desidera elencare tutte le classi di un pacchetto, è sufficiente digitare
il nome del pacchetto e inviare la richiesta.

Si può anche integrare la ricerca delle API di symfony nel proprio browser.
In questo modo non c'è nemmeno bisogno di passare dal sito web di symfony per 
cercare qualcosa. Questo è possibile perché forniamo un supporto nativo
[OpenSearch](http://www.opensearch.org/) per le API di symfony.

Se si utilizza Firefox, il motore di ricerca delle API di symfony apparirà
automaticamente nel menù dei motori di ricerca. Si può anche fare un click sul
link "API OpenSearch" presente nella sezione con la documentazione delle API,
per aggiungerle nella casella di ricerca del browser.

>**NOTE**
>Nel [blog](http://www.symfony-project.org/blog/2009/02/24/opensearch-support-for-the-symfony-api) di symfony,
>si può dare un'occhiata ad uno screencast che mostra come il motore di ricerca
>delle API si symfony ben si integri con Firefox.

### Cheat Sheet

Se può interessare avere alcuni fogli sintetici sulle principali parti del framework,
si possono scaricare molti [cheat sheets](http://trac.symfony-project.org/wiki/CheatSheets):

 * [Struttura delle cartelle e CLI](http://andreiabohner.files.wordpress.com/2007/03/cheatsheetsymfony001_enus.pdf)
 * [View](http://andreiabohner.files.wordpress.com/2007/08/sfviewfirstpartrefcard.pdf)
 * [View: partial, componenti, slot e componenti slot](http://andreiabohner.files.wordpress.com/2007/08/sfviewsecondpartrefcard.pdf)
 * [Lime: test unitari e funzionali](http://trac.symfony-project.com/attachment/wiki/LimeTestingFramework/lime-cheat.pdf?format=raw)
 * [ORM](http://andreiabohner.files.wordpress.com/2007/08/sform_enus.pdf)
 * [Propel](http://andreiabohner.files.wordpress.com/2007/08/sfmodelfirstpartrefcard.pdf)
 * [Schema di Propel](http://andreiabohner.files.wordpress.com/2007/09/sfmodelsecondpartrefcard.pdf)
 * [Doctrine](http://www.phpdoctrine.org/Doctrine-Cheat-Sheet.pdf)

>**NOTE**
>Alcuni di questi cheat sheet non sono ancora stati aggiornati per symfony 1.3.

### Documentazione offline

Le migliori risposte relative alle domande sulla configurazione, si trovano
sulla guida di riferimento a symfony. Questo è un libro che bisogna tenersi
vicini quando si sviluppa con symfony. È il modo più veloce per
trovare ogni configurazione disponibile, grazie ad un indice dei contenuti
molto dettagliato, un indice dei termini, dei riferimenti incrociati all'interno
dei capitoli, tabelle e molto altro ancora.

È possibile leggere questo libro
[online](http://www.symfony-project.org/reference/1_4/it/), comprarne una versione
[stampata](http://books.sensiolabs.com/book/the-symfony-1-4-reference-guide) (disponibile anche in italiano),
, o scaricare una versione in
[PDF](http://www.symfony-project.org/get/pdf/reference-1.4-en.pdf) (solo in inglese).

### Strumenti online

Come si è visto all'inizio di questo capitolo, symfony fornisce un insieme di
strumenti che aiutano a procedere velocemente. Dopo qualche tempo, il progetto
verrà completato e bisognerà metterlo in produzione.

Per verificare che il progetto è pronto per la messa in produzione, si può
utilizzare l'[elenco delle cose da verificare](http://symfony-check.org/).
Questo sito web mostra i punti più importanti che necessitano di verifica prima
di poter andare in produzione.

Eseguire il debug più rapidamente
----------------------------------

Quando si verifica un errore nell'ambiente di sviluppo, symfony mostra una
piacevole pagina con l'eccezione, che contiene molte informazioni utili.
È possibile, ad esempio, guardare lo stack trace e i file che sono stati eseguiti.
Se si imposta la configurazione ~`sf_file_link_format`~ nel file `settings.yml`
(vedere sotto) si può anche fare clic sul nome del file e il file relativo sarà
aperto nella riga di destra nel proprio editor di testo o IDE. Questo è davvero
un buon esempio di una caratteristica davvero piccola, che però può fare
risparmiare una enormità di tempo quando si fa il debug.

>**NOTE**
>I pannelli di log e di view mostrano anche i nomi dei file (in particolare quando XDebug
>è abilitato) e questi nomi di file diventano cliccabili quando si imposta
>l'impostazione `sf_file_link_format`.

Per impostazione predefinita, `sf_file_link_format` è vuoto e symfony si rifà al
alore della [`xdebug.file_link_format`](http://xdebug.org/docs/all_settings#file_link_format)
configurazione PHP se esiste (l'impostazione `xdebug.file_link_format` nel
`php.ini` permette alle versioni recenti di XDebug di aggiungere link a tutti i
nomi dei file presenti nello stack trace).

Il valore per `sf_file_link_format` dipende dall'IDE e dal sistema operativo.
Per esempio, se si vogliono aprire file in ~TextMate~, aggiungere il seguente
frammento a `settings.yml`:

    [yml]
    dev:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

Il segnaposto `%f` è sostituito da symfony con il percorso assoluto del file
e il segnaposto `%l` è sostituito con il numero di linea.

Per chi usa VIM, la configurazione è più complicata ed è descritta online per
[symfony](http://geekblog.over-blog.com/article-symfony-open-exceptions-files-in-remote-vim-sessions-37895120.html)
e [XDebug](http://www.koch.ro/blog/index.php?/archives/77-Firefox,-VIM,-Xdebug-Jumping-to-the-error-line.html).

>**NOTE**
>Utilizzare il motore di ricerca preferito per imparare come configurare l'IDE. Si può
>guardare alla configurazione di `sf_file_link_format` o `xdebug.file_link_format`,
>entrambi funzionano nello stesso modo.

Fare i test più rapidamente
---------------------------

### Registrare i test funzionali

I test funzionali simulano l'interazione dell'utente per valutare accuratamente
l'integrazione di tutti i pezzi dell'applicazione. Scrivere i test funzionali è
facile, ma richiede tempo. Ma siccome ogni file di test funzionale è uno
scenario che simula un utente che naviga nel sito e siccome navigare una
applicazione è più rapido che scrivere del codice PHP, l'ideale sarebbe registrare
una sessione del browser ed averla convertita automaticamente in codice PHP.
Fortunatamente, symfony ha un tale plugin. Si chiama
[swFunctionalTestGenerationPlugin](http://www.symfony-project.org/plugins/swFunctionalTestGenerationPlugin),
e permette di generare in pochi minuti degli scheletri di test pronti per
essere personalizzati. Naturalmente per renderlo utile, sarà comunque necessario
aggiungere le chiamate al tester adeguato, ma questo è in ogni caso un notevole
risparmio di tempo.

Il plugin lavora registrando un filtro di symfony, che intercetta tutte le
richieste e le converte in codice di test funzionali. Dopo aver installato
il plugin nel solito modo, bisogna abilitarlo. Aprire il file `filters.yml`
dell'applicazione e aggiungere le seguenti linee dopo la riga di commento:

    [php]
    functional_test:
      class: swFilterFunctionalTest

Alla fine, attivare il plugin nella classe `sfProjectConfiguration`:

    [php]
    // config/ProjectConfiguration.class.php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->enablePlugin('swFunctionalTestGenerationPlugin');
      }
    }

Siccome il plugin usa la barra degli strumenti web del debug come principale
interfaccia utente, bisogna essere sicuri che sia abilitata (questo è il caso
dell'ambiente di sviluppo, per impostazione predefinita).
Appena abilitato, viene reso disponibile un nuovo menu chiamato "Functional Test".
In questo pannello è possibile avviare la registrazione di una sessione, cliccando
sul link "Activate" e ripristinare la sessione corrente cliccando su "Reset".
Quando si ha finito, copiare e incollare il codice dalla textarea in un file dei
test e iniziare a personalizzarlo.

### Lanciare la suite di test più velocemente

Quando si ha una suite di test molto grande, può richiedere molto tempo lanciare
tutti i test ogni volta che si fanno delle modifiche, specialmente se alcuni
test falliscono. Questo perché ogni volta che si mette a posto un test bisognerebbe
rieseguire nuovamente l'intera suite di test per essere sicuri di non avere creato
dei malfunzionamenti da qualche altra parte. Ma finché i test che falliscono
non vengono messi a posto, non c'è altra possibilità che rieseguire tutti gli
altri test. Il task `test:all` ha una opzione `--only-failed` (`-f` come
scorciatoia) che forza i task a rieseguire solo i test che sono falliti durante
l'esecuzione precedente:

    $ php symfony test:all --only-failed

La prima volta, tutti i test vengono eseguiti come al solito. Ma per l'esecuzione
dei successivi test, solo i test che sono falliti l'ultima volta verranno
eseguiti. Nel momento in cui si mette a posto del codice, alcuni test passeranno
e saranno rimossi dalle successive esecuzioni. Quando tutti i test passeranno
nuovamente, verrà lanciata l'intera suite di test... si può allora ricominciare
il ciclo e ripetere le varie fasi.
