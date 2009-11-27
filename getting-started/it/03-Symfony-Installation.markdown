Installazione di symfony
========================

### Directory del progetto

Prima di installare symfony c'è bisogno di creare una directory che conterrà tutti
i file relativi al progetto:

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Oppure su Windows:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Agli utenti Windows si raccomanda di eseguire symfony e di impostare i nuovi
>progetti in path che non contengano spazi.
>Evitare possibilmente di utilizzare la directory `Documents and Settings`,
>incluso tutto ciò che si trova in `My Documents`.

-

>**TIP**
>Creando la directory del progetto all'interno della web root directory non sarà
>necessario configurare il web server. Tuttavia, per ambienti di produzione,
>è caldamente consigliato di configurare il web server come specificato nella
>sezione dedicata a tale configurazione.

### Installazione di symfony

Creare una directory per contenere i file del framework symfony:

    $ mkdir -p lib/vendor

È il momento di installare symfony. Dato che il framework symfony ha diversi 
stable branch è necessario selezionare la versione da installare leggendo la 
[pagina di installazione](http://www.symfony-project.org/installation) sul sito
web di symfony.

Andate sulla pagina di installazione per la versione selezionata,
[symfony 1.2](http://www.symfony-project.org/installation/1_2) per esempio.

Nella sezione "**Source Download**" sono disponibili gli archivi nei formati
`.tgz` o `.zip`. Scaricare l'archivio nella directory appena creata 
`lib/vendor/` e decomprimerlo:

    $ cd lib/vendor
    $ tar zxpf symfony-1.2.2.tgz
    $ mv symfony-1.2.2 symfony
    $ rm symfony-1.2.2.tgz

In ambienti Windows il file zip può essere estratto con explorer. Dopo aver
rinominato la directory a `symfony` dovrebbe esserci una directory chiamata
`c:\dev\sfproject\lib\vendor\symfony`.

>**TIP**
>Usando Subversion è consigliabile utilizzare `svn:externals` per includere
>symfony nel proprio progetto nella directory `lib/vendor/` e beneficiare così
>dei bug fix rilasciati in modo automatico nello stable branch:
>
>     http://svn.symfony-project.com/branches/1.2/

Verificare che symfony sia correttamente installato utilizzando la riga di comando
per visualizzare la versione di symfony (attenzione alla `V` maiuscola):

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Su Windows:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

>**TIP**
>Volendo sapere di più su questa riga di comando digitare 
>`symfony` per elencare le opzioni ed i task disponibili:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Su Windows:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>La riga di comando di symfony è la migliore amica dello sviluppatore. Mette
>a disposizione molti strumenti che migliorano la vostra produttività per 
>attività giornaliere come pulizia della cache, generazione di codice e molto
>altro.
