Installazione di symfony
========================

Inizializzare la cartella del progetto
--------------------------------------

Prima di installare symfony, occorre creare una cartella che conterrà tutti
i file relativi al progetto:

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Oppure su Windows:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Agli utenti Windows si raccomanda di eseguire symfony e di impostare i nuovi
>progetti in percorsi che non contengano spazi.
>Evitare, se possibile, di utilizzare la cartella `Documents and Settings`,
>incluso tutto ciò che si trova in `My Documents`.

-

>**TIP**
>Creando la cartella del progetto all'interno della cartella radice del web, non sarà
>necessario configurare il server web. Tuttavia, per ambienti di produzione,
>è caldamente consigliato configurare il server web come specificato nella
>sezione dedicata a tale configurazione.

Scegliere la versione di symfony
--------------------------------

Ora si deve installare symfony. Poiché il framework ha diverse versioni
stabili, occorre scegliere quella che si vuole installare, leggendo la
[pagina di installazione](http://www.symfony-project.org/installation) sul
sito di symfony.

Questa guida assume che si voglia installare symfony 1.4.

Scegliere il posto dove installare symfony
------------------------------------------

Si può installare symfony globalmente nella propria macchina, oppure
includerlo in ciascuno dei progetti. Questa seconda opzione è quella
raccomandata, poiché in questo modo i progetti saranno totalmente
indipendenti tra loro. Aggiornando un symfony installato localmente,
non si creeranno problemi inattesi ad altri progetti. Questo vuol dire
che si potranno avere progetti diversi con versioni di symfony diverse
e aggiornarli uno alla volta, secondo le proprie necessità.

Di solito il framework viene installato nella cartella `lib/vendor`
del progetto. Quindi, per prima cosa, creiamo questa cartella:

    $ mkdir -p lib/vendor


Installazione di symfony
------------------------

### Installazione da un archivio

Il modo più facile per installare symfony è quello di scaricare l'archivio
della versione scelta dal sito di symfony. Andate sulla pagina di installazione
per la versione appena scelta,
[symfony 1.4](http://www.symfony-project.org/installation/1_4) per esempio.

Nella sezione "**Download as an Archive**" sono disponibili gli archivi nei formati
`.tgz` o `.zip`. Scaricare l'archivio nella cartella appena creata 
`lib/vendor/`, scompattarlo e rinominare la cartella in `symfony`:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.0.tgz
    $ mv symfony-1.4.0 symfony
    $ rm symfony-1.4.0.tgz

In ambienti Windows, il file zip può essere estratto con explorer. Dopo aver
rinominato la cartella in `symfony`, dovrebbe esserci una cartella chiamata
`c:\dev\sfproject\lib\vendor\symfony`.

### Installazione da Subversion (raccomandata)

Se si usa Subversion, è anche meglio usare la proprietà `svn:externals`
per includere symfony nel proprio progetto, nella cartella `lib/vendor/`:

    $ svn pe svn:externals lib/vendor/

Se tutto è andato bene, questo comando aprirà il vostro editor preferito
per poter configurare i sorgenti esterni di Subversion.

>**TIP**
>Su Windows, si possono usare strumenti come [TortoiseSVN](http://tortoisesvn.net/)
>per poter fare tutto senza usare la riga di comando.

Per un approccio conservativo, legare il progetto a un rilascio specifico
(un tag di Subversion):

    svn checkout http://svn.symfony-project.com/tags/RELEASE_1_4_0

Ogni volta che esce un nuovo rilascio (come annunciato sul
[blog](http://www.symfony-project.org/blog/) di symfony), occorrerà
modificare l'URL.

Se si preferisce la strada dell'aggiornamento continuo, usare il ramo 1.4:

    svn checkout http://svn.symfony-project.com/branches/1.4/

L'uso del ramo apporta ai progetti i benefici dei bug risolti ogni volta
che si esegue il comando `svn update`.
