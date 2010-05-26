Chapter 3 - Utilizzare Symfony
==============================

Come si è appreso nei capitoli precedenti, il framework symfony è un insieme di file scritti in PHP. Un progetto symfony utilizza questi file, quindi installare symfony vuol dire prendere questi file e renderli disponibili per il progetto.

Symfony richiede almeno PHP 5.2.4. Per verificare qual'è la versione installata, aprire una linea di comando e digitare:

    $ php -v

    PHP 5.3.1 (cli) (built: Jan  6 2010 20:54:10) 
    Copyright (c) 1997-2009 The PHP Group
    Zend Engine v2.3.0, Copyright (c) 1998-2009 Zend Technologies

Se la versione è la 5.2.4 o maggiore, allora si è pronti per l'installazione, descritta in questo paragrafo.

Prerequisiti
------------

Prima di installare symfony, è necessario controllare che il computer abbia tutto
installato e configurato corretamente. E' bene prendersi il tempo per leggere con calma questo
capitolo e seguire tutti i passaggi necessari per verificare la configurazione, in quanto
può evitare possibili problemi successivi.

### Software di terze parti

Prima di tutto è necessario controllare che il computer abbia un ambiente
di lavoro amichevole per lo sviluppo web. Come minimo, è necessario un web server (Apache,
per esempio), un motore di database (MySQL, PostgreSQL, SQLite, o qualsiasi
[PDO](http://www.php.net/PDO)-motore di database compatibile), e PHP 5.2.4 o
successivo.

### CLI - Interfaccia a riga di comando

Il framework symfony viene distribuito con un tool a riga di comando che consente di
automatizzare molto lavoro per voi. Se si è utenti della famiglia Unix, ci si sentirà a
casa. Se si lavora con un sistema Windows si lavorerà bene ugualmente, ma sarà
necessario digitare alcuni comandi al prompt `cmd`.

>**Note**
>I comandi della shell Unix possono tornare utili in un ambiente Windows.
>Se si desiderano utilizzare strumenti come `tar`, `gzip` o `grep` su Windows, si
>può installare [Cygwin](http://cygwin.com/).
>I più avventurosi possono anche provare
>[Windows Services for Unix](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx) di Microsoft.

### La configurazione di PHP

Essendo che le configurazioni di PHP possono variare molto da un SO ad un altro,
o anche tra diverse distribuzioni Linux, è necessario controllare che la vostra
configurazione di PHP soddisfi i requisiti minimi di symfony.

In primo luogo, assicurarsi di avere installato ome minimo PHP 5.2.4 utilizzando la
funzione PHP `phpinfo()` o eseguendo `php -v` da linea di comando. Bisogna tenere
presente che su alcune configurazioni, è possibile avere installate due diverse versioni
di PHP: una per la linea di comando, e un'altra per il web.

Dopo, scaricare lo script per la verifica della configurazione di symfony al seguente URL:

    http://sf-to.org/1.4/check.php

Salvare lo script da qualche parte sotto la cartella radice del web.

Avviare lo script per la verifica della configurazione di symfony dalla riga di comando:

    $ php check_configuration.php

Se c'è un problema con la configurazione di PHP, l'output del comando
darà suggerimenti su cosa sistemare e su come risolverlo.

Inoltre si dovrebbe eseguire lo script da un browser e correggere i problemi che potrebbe
scoprire. Questo perché PHP può avere distinti file di configurazione `php.ini`
per questi due ambienti, con impostazioni diverse.

>**NOTE**
>In seguito, non dimenticate di rimuovere il file dalla cartella web
>principale.

-

>**NOTE**
>Se l'obiettivo è quello di provare symfony per qualche ora, è possibile installare
>la sandbox di symfony come descritto alla fine di questo capitolo. Se
>si vuole iniziare con un progetto vero e proprio o si vuole saperne di più su
>symfony, proseguire la lettura.

L'installazione di symfony
--------------------------

### Inizializzazione della cartella progetto

Prima di installare symfony, è necessario creare una cartella che ospiterà
tutti i file relativi al progetto:

    $ mkdir -p /home/progettosf
    $ cd /home/progettosf

O su Windows:

    c:\> mkdir c:\dev\progettosf
    c:\> cd c:\dev\progettosf

>**NOTE**
>Agli utenti Windows si consiglia di eseguire symfony e di impostare il nuovo
>progetto in un percorso che non contenga spazi.
>Evitare di utilizzare la cartella `Documents and Settings`, includendo ogni cosa
>sotto `My Documents`.

-

>**TIP**
>Se si crea la cartella del progetto symfony sotto cartella principale web,
>non sarà necessario configurare il server web. Naturalmente, per
>ambienti di produzione, si consiglia vivamente di configurare il web
>server come spiegato nella sezione di configurazione del web server.

### Scegliere la versione di symfony

Ora, è necessario installare symfony. Poiché il framework symfony ha diverse versioni
stabili, si deve scegliere quello che si desidera installare leggendo la
[Pagina di installazione] (http://www.symfony-project.org/installation) sul
sito web di symfony.

### Scegliere dove si vuole installare symfony

È possibile installare symfony globalmente sulla macchina, o inserirlo in ciascun
progetto. Quest'ultimo è l'approccio consigliato, perché in questo modo i progetti saranno
totalmente indipendenti l'uno dall'altro e l'aggiornamento della singola installazione di
symfony non influirà su altri progetti. Inoltre in questo modo si è in grado di
avere progetti che utilizzano diverse versioni di symfony, e aggiornarle una alla volta
in base alle esigenze.

Come buona pratica, molte persone installano i file del framework symfony nella
caretella `lib/vendor` del progetto. Quindi, prima, creare questa cartella:

    $ mkdir -p lib/vendor

### Installare symfony

#### Installare da un archivio

Il modo più semplice per installare symfony è quello di scaricare l'archivio per la versione
scelta dal sito web di symfony. Andare alla pagina di installazione per la
versione scelta, ad esempio symfony
[1.4](http://www.symfony-project.org/installation/1_4).

Sotto la sezione "**Source Download**", ci sono gli archivi nei formati `.tgz`
e `.zip`. Scaricare l'archivio, metterlo dentro la cartella `lib/vendor/` appena creata,
scompattarlo e rinominare la cartella in `symfony`:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.0.tgz
    $ mv symfony-1.4.0 symfony
    $ rm symfony-1.4.0.tgz

Sotto Windows, scompattare il file zip utilizzando Windows Explorer.
Dopo rinominare la cartella in `symfony`. La struttura delle cartelle dovrebbe
essere simile a `c:\dev\progettosf\lib\vendor\symfony`.

#### Installare da Subversion (consigliato)

Se si utilizza Subversion per il progetto, è ancora meglio usare la proprietà `svn:externals`
per incorporare symfony nella cartella `lib/vendor/` del progetto:

    $ svn pe svn:externals lib/vendor/

Se tutto va bene, questo comando aprirà il vostro editor preferito per permettere
di configurare le fonti esterne di Subversion.

>**TIP**
>Su Windows, si possono usare strumenti come [TortoiseSVN](http://tortoisesvn.net/)
>per fare tutto senza la necessità di usare la console.

Per un utilizzo conservativo, legare il progetto ad una specifica versione (un tag
subversion):

    symfony http://svn.symfony-project.com/tags/RELEASE_1_4_0

Ogni volta che esce una nuova release (annunciata sul
[blog](http://www.symfony-project.org/blog/) di symfony), bisogna cambiare la URL
alla nuova versione.
	
Se si vuole seguire la strada meno conservativa ed essere aggiornati alle ultime
modifiche, usare il ramo 1.4:

    symfony http://svn.symfony-project.com/branches/1.4/

Usare il ramo vuold dire che il progetto beneficia dei bug fixe automaticamente
ogni volta che viene eseguito un `svn update`.

#### Verificare l'installazione

Ora che symfony è installato, verificare che tutto funzioni utilizzando la
linea di comando per visualizzare la versione di symfony (notare la `V` maiuscola):

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Su Windows:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

Dopo aver creato il progetto (sotto), l'esecuzione di questo comando fa anche
visualizzare il percorso della cartella di installazione di symfony, che è memorizzato
in `config/ProjectConfiguration.class.php`.

Quando si fa questa verifica, se il percorso di symfony è di tipo assoluto (non dovrebbe
essere la modalità predefinita se si seguono le istruzioni qui sotto), cambiarlo in modo che sia
simile a questo, per avere una migliore portabilità:

    [php]
    // config/ProjectConfiguration.class.php
    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';

In questo modo si può spostare ovunque la cartella del progetto sulla propria
macchina, o su un'altra e tutto continuerà a funzionare

>**TIP**
>Per chi è curioso di sapere cosa si può fare con lo strumento a linea di comando, digitare
>`symfony` per visualizzare le opzioni disponibili e i task:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Su Windows:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>La linea di comando di symfony è il miglior amico dello sviluppatore. Fornisce numerose
>utility per migliorare la produttività di tutti i giorni, come
>la cancellazione della cache, la generazione di codice e molto altro.

Setup del progetto
------------------

In symfony, le **applicazioni** che condividono lo stesso modello per i dati sono raggruppate in
**progetti**. Per la maggior parte dei progetti, si avranno due differenti applicazioni:
frontend e backend.

### Creazione del progetto

Dalla cartella `progettosf/`, lanciare il task symfony `generate:project` per creare
effettivamente il progetto symfony:

    $ php lib/vendor/symfony/data/bin/symfony generate:project NOME_PROGETTO

Su Windows:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project NOME_PROGETTO

Il task `generate:project` genera la struttura predefinita di cartelle e
file che sono necessari per un progetto symfony.

>**NOTE**
>Perché symfony genera così tanti file? Uno dei principali vantaggi di utilizzare
>un framework è quello di standardizzare lo sviluppo. Grazie alla
>struttura predefinita di file e cartelle di symfony, ogni sviluppatore
>con un minimo di conoscenza di symfony può farsi carico del mantenimento di qualsiasi
>progetto symfony. In pochi minuti, egli sarà in grado di tuffarsi nel codice, fare
>bug fix e aggiungere nuove funzionalità.

Il task `generate:project` inoltre ha creato un collegamento `symfony` nella
cartella principale del progetto per ridurre il numero di caratteri che si devono
scrivere per lanciare un task.

Quindi, da ora in poi, invece di utilizzare il percorso completo al programma
symfony, verrà utilizzata la scorciatoia `symfony`.

### Configurazione del database

Il framework symfony supporta tutti i database supportati da [PDO](http://www.php.net/PDO)
(MySQL, PostgreSQL, SQLite, Oracle, MSSQL, ...). Sopra PDO, symfony viene
distribuito con due ORM: Propel e Doctrine.

Quando si crea un nuovo progetto, nella modalità predefinita viene abilitato Doctrine.
La configurazione del database utilizzando Doctrine è semplice come usare il task
`configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=nomedb" root mYsEcret

Il task `configure:database` prende tre argomenti: il
[~DSN PDO~](http://www.php.net/manual/en/pdo.drivers.php), il nome utente e
la password per accedere al database. Se non si ha bisogno di una password per accedere
al database sul server di sviluppo, basta omettere il terzo argomento.

>**TIP**
>Se si vuole utilizzare Propel invece di Doctrine, aggiungere `--orm=Propel` quando si crea
>il progetto con il task `generate:project`. Se non si vuole utilizzare un
>ORM, basta mettere `--orm=none`.

### Creazione dell'applicazione

Ora, creare l'applicazione frontend lanciando il task `generate:app`:

    $ php symfony generate:app frontend

>**TIP**
>Essendo che la scorciatoia del file symfony è eseguibile, gli utenti Unix possono sostituire
>d'ora in poi, tutte le occorrenze di '`php symfony`' con '`./symfony`'.
>
>Su Windows si può copiare il file '`symfony.bat`' nel progetto e usare
>'`symfony`' invece di '`php symfony`':
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

Sulla base del nome dell'applicazione dato come *argomento*, il task generate:app`
crea la struttura predefinita di cartelle necessarie per l'applicazione, nella
cartella `apps/frontend/`.


>**SIDEBAR**
>Sicurezza
>
>Per impostazione predefinita, il task `generate:app` ha messo in sicurezza l'applicazione dalle
>due vulnerabilità più diffuse sul web. Symfony
>attua automaticamente le misure di ~sicurezza|Sicurezza~ per nostro conto.
>
>Per prevenire attacchi di tipo ~XSS~, viene abilitata l'escapizzazione dell'output; per prevenire
>attacchi di tipo ~CSRF~, viene generata una stringa CSRF casuale.
>
>Naturalmente, queste impostazioni si possono modificare, grazie alle *opzioni* seguenti:
>
>  * `--escaping-strategy`: Abilita o disabilita l'escapizzazione dell'output
>  * `--csrf-secret`: Abilita i token di sessione nei form
>
>Per chi ne sa poco di
>[XSS](http://en.wikipedia.org/wiki/Cross-site_scripting) o
>[CSRF](http://en.wikipedia.org/wiki/CSRF), è meglio dedicare un po' di tempo per imparare qualcosa
>su queste vulnerabilità relative alla sicurezza.

### Permessi nella struttura delle cartelle

Prima di provare ad accedere al progetto appena creato, è necessario impostare  ai livelli appropriati
i permessi in scrittura sulle cartelle `cache/` e `log/`, in modo che sia il server web che l'utente tramite
linea di comando, possano scrivere al loro interno:

    $ symfony project:permissions

>**SIDEBAR**
>Consigli per chi usa un tool SCM
>
>symfony scrive sempre e solo in due cartelle di un progetto symfony,
>`cache/` e `log/`. Il contenuto di queste cartelle dovrebbe essere ignorato
>dal SCM (ad esempio, per chi usa Subversion modificando la proprietà
>`svn:ignore`).
