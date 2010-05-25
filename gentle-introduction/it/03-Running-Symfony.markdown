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
