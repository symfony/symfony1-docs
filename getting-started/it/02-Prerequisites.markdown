Prerequisiti
============

Prima di installare symfony, si deve verificare che il proprio computer
abbia ogni cosa installata e configurata correttamente. Una lettura
approfondita di questo capitolo e l'esecuzione di tutti i passi
richiesti per verificare la propria configurazione potrebbe far
risparmiare molto tempo successivamente.

Programmi
---------

Prima di tutto, occorre verificare che il proprio computer disponga di
un ambiente di lavoro adatto allo sviluppo sul web. Come minimo, si ha
bisogno di un server web (come Apache), un database (MySQL, PostgreSQL,
SQLite, o qualsiasi altro database compatibile con PDO) e di PHP 5.2.4
(o una versione successiva).

Interfaccia a riga di comando
------------------------------

Il framework symfony viene distribuito con uno strumento a riga di
comando che automatizza molte cose. Se siete utenti di un sistema
operativo derivato da Unix, vi troverete a vostro agio. Se siete su un sistema
Windows, andrà tutto bene, dovrete solo scrivere pochi comandi nel
prompt `cmd`.

>**Note**
>I comandi del terminale di Unix possono essere utili in un ambiente
>Windows. Se si vogliono usare strumenti come `tar`, `gzip`, o `grep`
>su Windows, si può installare [Cygwin](http://cygwin.com/). La
>documentazione ufficiale è un po' scarsa, ma si può trovare una
>buona guida di installazione [qui](http://www.soe.ucsc.edu/~you/notes/cygwin-install.html).
>I più arditi possono anche provare
>[Windows Services for Unix](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx)
>di Microsoft.

Configurazione di PHP
---------------------

Siccome le configurazioni di PHP possono variare molto da un sistema
operativo all'altro, o anche tra diverse distribuzioni Linux, occorre
verificare che la propria configurazione di PHP soddisfi i requisiti
minimi di symfony.

Innanzitutto, assicurarsi di avere almeno PHP 5.2.4 installato, usando
la funzione di libreria `phpinfo()`, oppure eseguendo `php -v` nella
riga di comando. Fare attenzione: in alcune configurazioni, si potrebbero
avere installate versioni di PHP diverse, una per la riga di comando
e l'altra per il web.

Quindi, scaricare lo script di symfony per la verifica della configurazione,
al seguente indirizzo:

    http://sf-to.org/1.2/check.php

Salvare lo script da qualche parte nella propria directory root del server web.

Lanciare lo script di verifica della configurazione dalla riga di comando:

    $ php check_configuration.php

Se c'è un problema con la configurazione di PHP, il risultato del
comando darà alcuni consigli su cosa sistemare e su come farlo.

Si dovrebbe eseguire lo script anche da un browser e sistemare gli
eventuali problemi scoperti. Questo perché PHP può avere un file di
configurazione `php.ini` diverso per questi due ambienti, con
diverse impostazioni.

>**NOTE**
>Non dimenticare di rimuovere il file dalla directory root del server web
>quando si ha finito.
