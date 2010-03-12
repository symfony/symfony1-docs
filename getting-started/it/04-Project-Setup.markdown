Preparazione del progetto
=========================

In symfony, le **applicazioni** che condividono lo stesso modello dei dati
sono raggruppate in **progetti**. Per la maggior parte dei progetti si
avranno due diverse applicazioni: un frontend e un backend.

Creazione del progetto
----------------------

Dalla cartella `sfproject/`, eseguire il task di symfony `generate:project`
per creare effettivamente il progetto symfony:

    $ php lib/vendor/symfony/data/bin/symfony generate:project PROJECT_NAME

Su Windows:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project PROJECT_NAME

Il task `generate:project` genera la struttura predefinita di cartella e di
file necessaria a un progetto symfony:

 | Cartella    | Descrizione
 | ----------- | ----------------------------------
 | `apps/`     | Contiene tutte le applicazioni del progetto
 | `cache/`    | I file messi in cache dal framework
 | `config/`   | I file di configurazione del progetto
 | `data/`     | I file di dati, come infissi iniziale
 | `lib/`      | Le classi e le librerie del progetto
 | `log/`      | I file di log del framework
 | `plugins/`  | I plugin installati
 | `test/`     | I file per i test unitari e funzionali
 | `web/`      | La cartella radice del web (vedi sotto)

>**NOTE**
>Per quale motivo symfony genera così tanti file? Uno dei maggiori
>benefici che derivano dall'uso di un framework full-stack è quello
>della standardizzazione dello sviluppo. Grazie alla struttura
>predefinita di file e cartella di symfony, ogni sviluppatore che
>conosca symfony può occuparsi della manutenzione di qualsiasi
>progetto symfony. In pochi minuti avrà la possibilità
>di analizzare il codice, sistemare i bug e aggiungere nuove
>caratteristiche.

Il task `generate:project` crea anche un collegamento `symfony` nella
cartella radice del progetto, per accorciare il numero di caratteri
da scrivere quando si esegue un task.

Quindi, d'ora in poi, invece di usare il percorso completo a symfony,
si può usare il collegamento `symfony`.

### Verifica dell'installazione

Ora che symfony è installato, verificare che sia tutto a posto, utilizzando la riga di comando
per visualizzare la versione di symfony (attenzione alla `V` maiuscola):

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Su Windows:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

L'opzione `-V` mostra anche il percorso della cartella di installazione di
symfony, memorizzato in `config/ProjectConfiguration.class.php`.

Se il percorso di symfony è assoluto (il che non dovrebbe essere, se si sono
seguite le istruzioni viste sopra), modificarlo come segue, per una
migliore portabilità:

    [php]
    // config/ProjectConfiguration.class.php
    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';

In questo modo, si può spostare la cartella del progetto ovunque, senza
problemi.

>**TIP**
>Volendo sapere di più su questa riga di comando, digitare 
>`symfony` per elencare le opzioni e i task disponibili:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Su Windows:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>La riga di comando di symfony è il migliore amico dello sviluppatore. Mette
>a disposizione molti strumenti che migliorano la produttività per 
>attività giornaliere come pulizia della cache, generazione di codice e molto
>altro.

Configurazione del database
---------------------------

Il framework symfony supporta nativamente tutti i database
supportati da [PDO]((http://www.php.net/PDO)) (MySQL, PostgreSQL,
SQLite, Oracle, MSSQL, ...). Appoggiandosi a PDO, symfony è distribuito
con due strumenti ORM: Propel e Doctrine. Quando si crea un nuovo
progetto, Doctrine è abilitato in modo predefinito. La configurazione
del database è semplificata dall'uso del task `configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=dbname" root mYsEcret

Il task `configure:database` accetta tre parametri: il
[~DSN di PDO~](http://www.php.net/manual/it/pdo.drivers.php), il nome utente e
la password per accedere al database. Se non si ha bisogno di una password per
accedere al database sul server di sviluppo, basta omettere il terzo parametro.

>**TIP**
>Se si vuole usare Propel invece di Doctrine, aggiungere `--orm=Propel` quando
>si crea il progetto col task `generate:project`. Se invece non si vuole
>usare nessun ORM, basta passare `--orm=none`.

Creazione di un'applicazione
----------------------------

Creare l'applicazione frontend, eseguendo il task `generate:app`:

    $ php symfony generate:app --escaping-strategy=on --csrf-secret=UniqueSecret frontend

>**TIP**
>Essendo il collegamento a symfony un file eseguibile, gli utenti Unix
>possono d'ora in poi sostituire tutte le occorrenze di '`php symfony`'
>con '`./symfony`'
>
>Su Windows, si può copiare il file '`symfony.bat`' all'interno del
>progetto e usare '`symfony`' invece di '`php symfony`':
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

Basandosi sul nome dell'applicazione fornito come *parametro*, il task
`generate:app` crea la struttura di cartella predefinita necessaria
per l'applicazione, nella cartella `apps/frontend/`:

 | Cartella     | Descrizione
 | ------------ | -------------------------------------
 | `config/`    | I file di configurazione dell'applicazione
 | `lib/`       | Le librerie e le classi dell'applicazione
 | `modules/`   | Il codice dell'applicazione (MVC)
 | `templates/` | I file dei template globali

>**SIDEBAR**
>Sicurezza
>
>Per impostazione predefinita, il task `generate:app` ha protetto
>l'applicazione da due delle vulnerabilità più diffuse sul web.
>Symfony si occupa automaticamente delle misure di ~sicurezza|Sicurezza~
>al posto nostro.
>
>Per prevenire gli attacchi ~XSS~, l'escape dell'output è stato abilitato;
>per prevenire gli attacchi ~CSRF~, un CSRF segreto è stato generato
>casualmente.
>
>Ovviamente si possono modificare queste impostazioni, con le seguenti
>*opzioni*:
>
>  * `--escaping-strategy`: Abilita l'escape dell'output
>  * `--csrf-secret`: Abilita i token di sessione nei form
>
>Passando queste due opzioni facoltative al task, abbiamo messo in sicurezza
>lo sviluppo futuro da due delle più diffuse vulnerabilità del web. Già, symfony
>si prende cura della sicurezza per noi.
>
>Se si ignora cosa siano 
>[XSS](http://it.wikipedia.org/wiki/Cross-site_scripting) e
>[CSRF](http://it.wikipedia.org/wiki/CSRF), sarebbe meglio spendere un po' di tempo
>per sapere di più su queste vulnerabilità.

Permessi sulla struttura delle cartelle
---------------------------------------

Prima di provare ad accedere al nuovo progetto, occorre impostare i
permessi di scrittura sulle cartella `cache/` e `log/` ai livelli
appropriati, in modo tale che il server web possa scriverci dentro:

    $ chmod 777 cache/ log/

>**SIDEBAR**
>Consigli per chi usa uno strumento di revisione del codice
>
>symfony scrive solamente in due cartella di un progetto symfony,
>`cache/` e `log/`. Il contenuto di queste due cartella dovrebbe essere
>ignorato dagli strumenti di revisione del codice (ad esempio
>utilizzando la proprietà `svn:ignore`, se si usa Subversion).
