Windows e symfony
=================

*di Laurent Bonnet*

Panoramica
----------

Questo capitolo è un nuovo tutorial passo-passo che copre l'installazione,
il deployment e i test funzionali del framework symfony su Windows Server
2008.

Al fine di predisporlo per il deployment su Internet, il tutorial può essere
eseguito su un ambiente server dedicato, ospitato su Internet.

Naturalmente, è possibile fare il tutorial su un server locale, o su una
macchina virtuale di una workstation.

### Le ragioni per un nuovo tutorial

Attualmente, ci sono due sorgenti di informazione relative a Microsoft Internet
Information Server (IIS) sul
[sito web](http://trac.symfony-project.org/wiki/symfonyOnIIS)
  [](http://www.symfony-project.org/cookbook/1_2/en/web_server_iis)
di symfony, ma fanno riferimento a precedenti versioni e non sono stati
aggiornati per le nuove versioni dei sistemi operativi Microsoft Windows, in
particolare Windows Server 2008 (rilasciato nel febbraio 2008), che includono
molte cose interessanti per gli sviluppatori PHP:

 * IIS versione 7, la versione inclusa in Windows Server 2008, è stata
   completamente riscritta per utilizzare una architettura modulare.

 * IIS 7 ha dimostrato di essere molto affidabile, perché dal lancio del prodotto
    sono state necessarie pochissime correzioni apportate da Windows Update.

 * IIS 7 include anche l'acceleratore FastCGI, un'insieme di applicazioni multi-thread
   che sfruttano il modello di thread nativo dei sistemi operativi Windows.

 * L'implementazione di FastCGI per PHP equivale a un miglioramento di prestazioni
   in esecuzione da 5 a 10 volte, senza cache, rispetto alle tradizionali distribuzioni
   ISAPI o CGI di PHP su Windows e IIS.

 * Più recentemente, Microsoft ha aperto il sipario su un acceleratore di cache
   per PHP, che al momento della stesura di questo capitolo (02/11/2009), si
   trova nello stato di Release Candidate. 

>**SIDEBAR**
>Estensioni previste per questo tutorial
>
>Una sezione supplementare di questo capitolo è in lavorazione e verrà rilasciata
>sul sito web del progetto symfony poco dopo la pubblicazione di
>questo libro. Riguarda la connessione al server MS SQL tramite PDO, che tra
>l'altro Microsoft ha pianificato di migliorare entro breve.
>
>      [PHP_PDO_MSSQL]
>      extension=php_pdo_mssql.dll
>
>Al momento, le migliori prestazioni per l'esecuzione del codice vengono ottenute con i driver 
>Microsoft nativi di SQL Server per PHP 5, un driver open-source disponibile su
>Windows e attualmente disponibile nella versione 1.1. È implementato come una
>nuova estensione DLL per PHP:
>
>      [PHP_SQLSRV]
>      extension=php_sqlsrv.dll
>
>È possibile usare Microsoft SQL Server 2005 o 2008 per il
>database. L'estensione pianificata del tutorial coprirà l'utilizzo dell'edizione,
>che è disponibile gratuitamente: SQL Server Express.

### Come utilizzare il tutorial su differenti sistemi Windows, compresi quelli a 32-bit

Queste pagine sono scritte specificamente per le edizioni a 64-bit di Windows Server
2008. Tuttavia, si dovrebbe essere in grado di utilizzare le altre versioni senza complicazioni.

>**NOTE**
>L'esatta versione del sistema operativo usato nelle schermate è
>Windows Server 2008 Enterprise Edition con Service Pack 2, edizione a 64-bit.

#### Versioni di Windows a 32-bit 

Il tutorial è facilmente portabile alle versioni a 32-bit di Windows, sostituendo
i seguenti riferimenti nel testo:

 * Sulle versioni a 64-bit: `C:\Program Files (x86)\` e `C:\Windows\SysWOW64\`

 * Sulle versioni a 32-bit: `C:\Program Files\` e `C:\Windows\System32\`

#### Riguardo le versioni diverse dall'Enterprise

Se non si ha la versione Enterprise non è un problema. Questo
documento è facilmente portabile ad altre versioni di Windows Server:
Windows Server 2008 Web, Standard o Datacenter Windows Server 2008 Web,
Standard o Datacenter con Service Pack 2 Windows Server 2008 R2 Web,
Standard, Enterprise o Datacenter edition.

Notare che tutte le versioni di Windows Server 2008 R2 sono disponibili unicamente
per i sistemi operativi a 64-bit.

#### Riguardo le versioni internazionali

Le impostazioni internazionali usate nelle schermate sono `en-US`. È stato
installato anche un pacchetto linguistico internazionale per la Francia.

È possibile eseguire il tutorial sui sistemi operativi client di Windows:
Windows XP, Windows Vista e Windows 7 sia in versione x64 che x86.

### Il server web utilizzato in tutto il documento

Il server web utilizzato è Microsoft Internet Information Server versione 7.0,
che è incluso in tutte le versioni di Windows Server 2008. Il tutorial parte con
un server Windows Server 2008 pienamente funzionale e l'installazione da zero
di IIS. I passi dell'installazione utilizzano le scelte predefinite, aggiungendo
solo due moduli specifici che provengono dal disegno modulare di IIS 7.0:
**FastCGI** e **URL Rewrite**.

### I database

SQLite è il database preconfigurato per la sandbox di symfony. Su Windows,
non c'è niente di particolare da installare: il supporto a SQLite è direttamente
implementato nell'estensione PDO di PHP per SQLite, che viene compreso nel momento
dell'installazione di PHP.

Quindi, non c'è bisogno di scaricare ed eseguire un'istanza separata di SQLITE.EXE:

      [PHP_PDO_SQLITE]
      extension=php_pdo_sqlite.dll

### Configurazione di Windows Server

È meglio utilizzare una nuova installazione di Windows Server al fine di
trovare una corrispondenza passo-passo nelle schermate di questo capitolo.

Naturalmente è possibile lavorare direttamente su una macchina esistente,
ma si possono riscontrare differenze dovute al software installato, il
runtime e le configurazioni della regione.

Al fine di ottenere le stesse schermate del tutorial, si consiglia di
recuperare un Windows Server dedicato in un ambiente virtuale, disponibile
gratuitamente su Internet per un periodo di 30 giorni.

>**SIDEBAR**
>Come ottenere una versione di prova gratuita di Windows Server?
>
>Naturalmente è possibile utilizzare qualsiasi server dedicato con accesso a Internet.
>Un server reale o anche un server dedicato virtuale (VDS) funzioneranno perfettamente.
>
>Un server prova per 30-giorni è disponibile su Ikoula, un host web francese
>che offre un'offerta completa di servizi per sviluppatori e
>grafici. La versione prova parte da 0 € / mese per una macchina virtuale Windows
>che gira in un ambiente Microsoft Hyper-V. Sì, è possibile avere una completa e
>funzionale macchina virtuale con Windows Server 2008 Web, Standard, Enterprise
>o anche Datacenter edition GRATIS per un periodo di 30 giorni.
>
>Per richiederla, andare su http://www.ikoula.com/flex_server e
>cliccare sul bottone "Testez gratuitement".
>
>Allo scopo di avere gli stessi messaggi che vengono mostrati nel capitolo, il sistema
>operativo ordinato con il server Flex è: "Windows Server 2008 Enterprise
>Edition 64 bits". Questa è una distribuzione x64, rilasciata sia con locale fr-FR
>che en-US. È facile passare da `fr-FR` a `en-US` e viceversa
>dal pannello di controllo di Windows. In particolare, questa impostazione si trova
>in "Regional and Language Options", presente nel tab "Keyboards
>and Languages". Basta cliccare su "Install/uninstall languages".

È necessario avere l'accesso di amministratore al server.

Se si sta lavorando da una workstation remota, il lettore dovrebbe lanciare
Remote Desktop Services (precedentemente conosciuto come Terminal Server Client)
e assicurarsi di avere l'accesso come amministratore.

La distribuzione usata qui è: Windows Server 2008 con Service Pack 2. 

![Verificare l'ambiente iniziale con Winver command - qua in inglese](http://www.symfony-project.org/images/more-with-symfony/windows_01.png)

Windows Server 2008 è stato installato con l'ambiente grafico, che è simile a
quello di Windows Vista. È anche possibile utilizzare una versione di Windows
Server 2008 che è solo a riga di comando ma con gli stessi servizi, al fine di
ridurre le dimensioni della distribuzione (1.5 GB invece di 6.5 GB). Questa
riduce anche lo spazio di partenza e il numero di patch di Windows Update che
sarà necessario applicare.

Verifiche preliminari - Server dedicati su Internet
---------------------------------------------------

Essendo il server direttamente accessibile da Internet, è sempre una
buona idea verificare che il firewall di Windows stia fornendo una adeguata
protezione. Le cose che dovrebbero essere verificate sono:

 * Core Networking
 * Desktop remoto (se accessibile da remoto)
 * Servizi Secure World Wide Web (HTTPS)
 * Servizi World Wide Web (HTTP)

![Verificare la configurazione del firewall, direttamente dal pannello di controllo.](http://www.symfony-project.org/images/more-with-symfony/windows_02.png)

Dopo, è sempre una buona idea lanciare Windows Update, per assicurarsi che
tutto il software sia aggiornato con gli ultimi fix, patch e documentazione.

![Verificare lo stato di Windows Update, direttamente dal pannello di controllo.](http://www.symfony-project.org/images/more-with-symfony/windows_03.png)

Come ultima fase nella preparazione e con lo scopo di eliminare eventuali
potenziali conflitti nella distribuzione esistente di Windows o della
configurazione di IIS, si consiglia di disinstallare il ruolo Web nel server
Windows, se precedentemente installato.

![Rimuovere il ruolo Web Server, da Server Manager.](http://www.symfony-project.org/images/more-with-symfony/windows_04.png)

Installare PHP con pochi click
------------------------------

Ora si può installare IIS e PHP in una sola operazione.

PHP NON fa parte della distribuzione di Windows Server 2008, quindi bisogna
prima installare Microsoft Web Platform Installer 2.0, denominato come Web PI
nelle sezioni a seguire.

Web PI si prende cura di installare tutte le dipendenze necessarie per
l'esecuzione di PHP su qualsiasi sistema Windows/IIS. Quindi, esegue il deploy
di IIS con il minimo di Role Services per il server web e fornisce anche le
opzioni minime per il runtime PHP.

![http://www.microsoft.com/web - Scaricarlo ora.](http://www.symfony-project.org/images/more-with-symfony/windows_05.png)

L'installazione di Microsoft Web Platform Installer 2.0 comprende un
analizzatore di configurazione, verifica i moduli esistenti, propone ogni
necessario upgrade dei moduli e permette anche di fare il beta-test di estensioni
non ancora rilasciate della piattaforma web di Microsoft.

![Web PI 2.0 - Prima schermata.](http://www.symfony-project.org/images/more-with-symfony/windows_06.png)

Web PI 2.0 permette di installare il runtime PHP in un click. Installa
l'implementazione "non-thread safe" Win32 di PHP, che è la migliore in abbinamento
con IIS 7 e FastCGI. Offre anche il più recente runtime testato, qui il 5.2.11.
Per trovarlo, basta selezionare il tab "Frameworks and Runtimes" sulla sinistra:

![Web PI 2.0 - Il tab Frameworks and Runtimes.](http://www.symfony-project.org/images/more-with-symfony/windows_07.png)

Dopo avere selezionato PHP, Web PI 2.0 gestisce automaticamente tutte le
dipendenze richieste dalle pagine web `.php` salvate sul server, includendo
i ruoli minimi per i servizi di IIS 7.0:

![Web PI 2.0 - Le dipendenze aggiunte automaticamente - 1/3.](http://www.symfony-project.org/images/more-with-symfony/windows_08.png)

![Web PI 2.0 - Le dipendenze aggiunte automaticamente - 2/3.](http://www.symfony-project.org/images/more-with-symfony/windows_09.png)

![Web PI 2.0 - Le dipendenze aggiunte automaticamente - 3/3.](http://www.symfony-project.org/images/more-with-symfony/windows_10.png)

Dopo, cliccare su Install, poi sul bottone "I Accept". L'installazione dei
componenti di IIS partirà e in parallelo verrà scaricato il [runtime](http://windows.php.net) di PHP
e saranno aggiornati alcuni moduli (ad esempio IIS 7.0 FastCGI).

![Web PI 2.0 - Installazione dei componenti IIS mentre gli aggiornamenti vengono scaricati dal web.](http://www.symfony-project.org/images/more-with-symfony/windows_11.png)

In ultimo, viene eseguito il setup di PHP e, dopo pochi minuti, dovrebbe comparire:

![Web PI 2.0 - L'installazione di PHP è stata completata.](http://www.symfony-project.org/images/more-with-symfony/windows_12.png)

Cliccare su "Finish".

Il server Windows ora sta ascoltando sulla porta 80 ed è in grado di rispondere.

Verificarlo nel browser:

![Firefox - IIS 7.0 sta rispondendo sulla porta 80.](http://www.symfony-project.org/images/more-with-symfony/windows_13.png)

Ora, per verificare che PHP sia stato installato correttamente e che sia disponibile
IIS, si può creare un piccolo file `phpinfo.php`, accessibile dal server web
predefinito sulla porta 80, in `C:\inetpub\wwwroot`.

Prima di farlo, assicurarsi che in Windows Explorer si possa vedere la corretta
estensione dei file. Selezionare "Unhide Extensions for Known Files Types".

![Windows Explorer - Unhide Extensions for Known Files Types.](http://www.symfony-project.org/images/more-with-symfony/windows_14.png)

Aprire Windows Explorer e andare in `C:\inetpub\wwwroot`. Click sul tasto destro,
selezionare "New Text Document". Rinominarlo in `phpinfo.php` e copiare la chiamata
della funzione:

![Windows Explorer - Crere phpinfo.php.](http://www.symfony-project.org/images/more-with-symfony/windows_15.png)

Dopo, riaprire il browser e mettere `/phpinfo.php` alla fine dell'URL
del server:

![Firefox - phpinfo.php L'esecuzione è OK](http://www.symfony-project.org/images/more-with-symfony/windows_16.png)

In ultimo, per essere sicuri che symfony verrà installato senza problemi, scaricare 
[http://sf-to.org/1.3/check.php](`check_configuration.php`).

![PHP - Dove scaricare check.php.](http://www.symfony-project.org/images/more-with-symfony/windows_17.png)

Copiarlo nella stessa cartella di `phpinfo.php` (`C:\inetpub\wwwroot`) e
rinominarlo, se necessario, in `check_configuration.php`.

![PHP - Copiare e rinominare check_configuration.php.](http://www.symfony-project.org/images/more-with-symfony/windows_18.png)

In ultimo, riaprire il browser un'ultima volta e mettere
`/check_configuration.php` alla fine dell'URL del server:

![Firefox - check_configuration.php L'esecuzione è OK.](http://www.symfony-project.org/images/more-with-symfony/windows_19.png)

Eseguire PHP dall'interfaccia a riga di comando
-----------------------------------------------

Allo scopo di eseguire i task a riga di comando con symfony, bisogna assicurarsi
che PHP.EXE sia accessibile dal prompt e che sia eseguito correttamente.

Aprire il prompt in `C:\inetpub\wwwroot` e digitare

    PHP phpinfo.php

Dovrebbe apparire il seguente messaggio di errore:

![PHP - MSVCR71.DLL was not found.](http://www.symfony-project.org/images/more-with-symfony/windows_20.png)

Se non si fa niente, l'esecuzione di PHP.EXE dovrebbe bloccarsi per l'assenza di
MSVCR71.DLL. Quindi bisogna trovare il file DLL e installarlo nella cartella
corrente.

Questo `MSVCR71.DLL` è una vecchia versione runtime del Microsoft Visual C++, che
è datato 2003. È presente nel pacchetto redistribuibile Framework .Net
versione 1.1.

Il pacchetto redistribuibile .Net Framework 1.1, può essere scaricato da
[MSDN](http://msdn.microsoft.com/en-us/netframework/aa569264.aspx)

Il file che si sta cercando è installato nella seguente cartella:
`C:\Windows\Microsoft.NET\Framework\v1.1.4322`

Basta copiare il file `MSVCR71.DLL` nella seguente destinazione:

 * sui sitemi x64: cartella `C:\windows\syswow64`
 * sui sistemi x86: cartella `C:\windows\system32`

Ora si può disinstallare .Net Framework 1.1.

L'eseguibile PHP.EXE ora può essere lanciato dal prompt dei comandi senza
errori. Ad esempio:

    PHP phpinfo.php
    PHP check_configuration.php

Dopo si potrà verificare che anche SYMFONY.BAT (dalla distribuzione Sandbox)
dia il risultato atteso, che è la visualizzazione della sintassi dei comandi
di symfony.

L'installazione della sandbox di symfony e il suo utilizzo
----------------------------------------------------------

Il seguente paragrafo è un estratto dalla pagina "Getting Started with symfony",
["La sandbox"](http://www.symfony-project.org/getting-started/1_4/it/A-Sandbox):
"La sandbox è un progetto symfony facile da installare, preconfezionato e
già configurato con alcune impostazioni predefinite. È un ottimo modo per
fare pratica con symfony senza il problema di una installazione che debba
rispettare le best practice per il web.

La sandbox è preconfigurata per utilizzare SQLite come database.
Su Windows non c'è nulla di particolare da installare: il supporto a SQLite
è direttamente implementato nella estensione PDO per SQLite, che è
installato nel momento dell'installazione di PHP. Quest'ultima è stata già fatta
prima, quando il runtime di PHP è stato installato tramite Microsoft Web PI.

Basta verificare che l'estensione SQLite sia presente nel file PHP.INI,
posto nella cartella `C:\Program Files (x86)\PHP` e che la DLL che implementa
il supporto PDO per SQLite sia impostata su `C:\Program Files
(x86)\PHP\ext\php_pdo_sqlite.dll`.


![PHP - Posizione del file di configurazione php.ini.](http://www.symfony-project.org/images/more-with-symfony/windows_21.png)

### Scaricare, creare la cartella, copiare tutti i file

Il progetto sandbox di symfony è "pronto da installare ed eseguire" ed è
fornito in un archivio `.zip`.

Scaricare l'[archivio](http://www.symfony-project.org/get/sf_sandbox_1_3.zip)
ed estrarlo in una cartella temporanea, ad esempio la cartella "downloads",
che è disponibile in R/W nella cartella `C:\Users\Administrator`.

![sandbox - Scaricare ed estrarre l'archivio.](http://www.symfony-project.org/images/more-with-symfony/windows_22.png)

Creare una cartella per la collocazione finale della sandbox, ad esempio
`F:\dev\sfsandbox`:

![sandbox - Creare la cartella sfsandbox.](http://www.symfony-project.org/images/more-with-symfony/windows_23.png)

Selezionare tutti i file - `CTRL-A` in Windows Explorer - dalla cartella di download
(sorgente), e copiarli nella cartella `F:\dev\sfsandbox`.

Si dovrebbero vedere 2599 elementi copiati nella cartella di destinazione:

![sandbox - Copiare 2599 elementi.](http://www.symfony-project.org/images/more-with-symfony/windows_24.png)

### Esecuzione del test

Aprire il prompt dei comandi. Andare in `F:\dev\sfsandbox` ed eseguire il seguente
comando:

    PHP symfony -V

Questo dovrebbe restituire:

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

Dallo stesso prompt dei comandi, eseguire:

    SYMFONY.BAT -V

Questo dovrebbe restituire lo stesso risultato:

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

![sandbox - Test della riga di comando eseguito con successo.](http://www.symfony-project.org/images/more-with-symfony/windows_25.png)

### La creazione dell'applicazione web

Per creare una applicazione web sul server locale, si userà IIS7 manager,
che è il pannello di controllo dell'interfaccia grafica utente per tutte le
attività collegate con IIS. Tutte le azioni attivate da tale UI sono effettivamente
eseguite dietro le quinte, attraverso l'interfaccia a riga di comando.

La console di IIS Manager è accessibile dal menu Start in Programmi,
Administrative Tools, Internet Information Server (IIS) Manager.

#### Riconfigurare "Default Web Site" in modo che non interferisca con la porta 80

Si vuole essere sicuri che solo la sandbox di symfony risponda sulla porta 80
(HTTP). Per farlo, cambiare il "Default Web Site" esistente in 8080.

![IIS Manager - Modificare il "Default Web Site".](http://www.symfony-project.org/images/more-with-symfony/windows_26.png)

Notare che, se è attivo Windows Firewall, potrebbe essere necessario creare
un'eccezione per la porta 8080, per essere in grado di raggiungere il "Sito
web predefinito". A tal fine andare nel pannello di controllo di Windows,
selezionare il Windows Firewall, cliccare su "Allow a program through Windows
Firewall" e cliccare su "Add port" per creare questa eccezione. Dopo la creazione
selezionare la casella per abilitarla. 

![Windows Firewall - Creare una eccezione per la porta 8080.](http://www.symfony-project.org/images/more-with-symfony/windows_27.png)

#### Aggiungere un nuovo sito web per la sandbox

Aprire IIS Manager da Administration Tools. Sul pannello di sinistra, selezionare l'icona
"Sites" e cliccare il tasto destro del mouse. Selezionare Add Web Site dal menu popup. Inserire, per
esempio, "Symfony Sandbox" come nome del sito, `D:\dev\sfsandbox` per il percorso fisico
e lasciare immutati gli altri campi. Si dovrebbe vedere questa finestra di dialogo:

![IIS Manager - Aggiungere il sito web.](http://www.symfony-project.org/images/more-with-symfony/windows_28.png)

Cliccare su OK. Se sull'icona del sito web compare una piccola `x` (in Features View /
Sites), cliccare "Restart" sul pannello di destra per farla scomparire.

#### Verificare se il sito web risponde

Dal gestore di IIS, selezionare il sito "Symfony Sandbox" e sul pannello di destra,
cliccare su "Browse *.80 (http)".

![IIS Manager - Cliccare su Browse port 80.](http://www.symfony-project.org/images/more-with-symfony/windows_29.png)

Si dovrebbe ricevere un messaggio di errore, ma non è inaspettato:

`HTTP Error 403.14 - Forbidden`.
Il server Web è configurato per non mostrare l'elenco dei contenuti di questa cartella.

Ciò deriva dalla configurazione predefinita del server web, che specifica
che il contenuto di questa cartella non dovrebbe essere elencato. Poiché non esiste
nessun file predefinito come `index.php` o `index.html` in `D:\dev\sfsandbox`, il
server restituisce correttamente il messaggio di errore "Forbidden". 

![Internet Explorer - Un errore normale.](http://www.symfony-project.org/images/more-with-symfony/windows_30.png)

Digitare `http://localhost/web` nella barra del browser per l'URL, invece che solo
`http://localhost`. Ora nel browser si dovrebbe vedere comparire
 "Symfony Project Created":

![IIS Manager - Digitare http://localhost/web nell'URL. Successo!](http://www.symfony-project.org/images/more-with-symfony/windows_31.png)

Tra l'altro, si può vedere una banda gialla in alto che dice "Ora le impostazioni
Intranet sono disattivate per impostazione predefinita." Le impostazioni
Intranet sono meno sicure delle impostazioni Internet. Cliccare per le opzioni.
Non c'è da preoccuparsi per questo messaggio.

Per chiuderlo definitivamente, basta cliccare con il tasto destro nella banda
gialla e selezionare l'opzione corretta.

Questa schermata conferma che la pagina predefinita `index.php` è stata correttamente
caricata da `D:\dev\sfsandbox\web\index.php`, correttamente eseguita e che le
librerie di symfony sono correttamente configurate.

C'è bisogno di fare ancora una cosa prima di poter iniziare a giocare con la
sandbox di symfony: configurare la pagina web front-end per utilizzare le
regole di riscrittura delle URL.
Queste regole sono implementate come file `.htaccess` e possono essere controllare
con pochi click nel gestore di IIS.

### Sandbox: la configurazione del front-end web

Si vuole configurare il front-end dell'applicazione sandbox per iniziare
finalmente a giocare con symfony. Per impostazione predefinita, la pagina di
front-end può essere raggiunta ed eseguita correttamente quando richiesta dalla
macchina locale (ad es. il nome `localhost` o l'indirizzo `127.0.0.1`).

![Internet Explorer - frontend_dev.php La pagina è OK da localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_32.png)

Si vogliono esplorare i pannelli del web debug, "configuration", "logs" e "timers"
per essere sicuri che la sandbox sia pienamente funzionale su Windows Server 2008.

![utilizzo della sandbox: configurazione.](http://www.symfony-project.org/images/more-with-symfony/windows_33.png)

![utilizzo della sandbox: log.](http://www.symfony-project.org/images/more-with-symfony/windows_34.png)

![utilizzo della sandbox: timer.](http://www.symfony-project.org/images/more-with-symfony/windows_35.png)

Anche se si potrebbe provare a richiedere l'applicazione sandbox da Internet o
da un indirizzo IP remoto, la sandbox è per lo più concepita come uno strumento
per imparare il framework symfony sulla macchina locale. Quindi, si vedranno i
dettagli relativi all'accesso remoto nell'ultimo paragrafo:
Progetto: configurazione del front-end web.

Creazione di un nuovo progetto symfony
--------------------------------------

Creare l'ambiente di un progetto symfony per uno sviluppo reale è quasi semplice
come l'installazione della sandbox. Si vedrà l'intero processo di installazione
con una procedura semplificata, in quanto è equivalente all'installazione della
sandbox e al suo deployment.

La differenza è che, in questo paragrafo "progetto", ci si concentrerà sulla
configurazione dell'applicazione web per renderla funzionante da qualunque parte
su Internet.

Come la sandbox, il progetto symfony è preconfigurato per usare SQLite come
database. SQLite è stato installato in precedenza in questo capitolo.

### Scaricare, creare una cartella e copiare i file

Ogni versione di symfony può essere scaricata come file .zip e usata
per creare un progetto da zero.

Scaricare l'archivio contenente la libreria dal
[sito web di symfony](http://www.symfony-project.org/get/symfony-1.3.0.zip)
Dopo, estrarre la cartella presente in un posto temporaneo, ad esempio la
cartella "downloads".

![Windows Explorer - Scaricare e fare l'unzip dell'archivio con il progetto.](http://www.symfony-project.org/images/more-with-symfony/windows_37.png)

Ora c'è bisogno di creare un albero di cartelle per la destinazione finale
del progetto. Questo è un po' più complicato della sandbox.

### Installazione dell'albero della cartelle

Creare un albero di cartelle per il progetto. Iniziare dalla radice del volume,
ad esempio `D:`.

Creare una cartella `\dev` su `D:` e creare un'altra cartella chiamata `sfproject`.
Ecco:

    D:
    MD dev
    CD dev
    MD sfproject
    CD sfproject

Ora si è nella cartella: `D:\dev\sfproject`

Da qui, creare un albero di sottocartelle, creando le cartelle `lib`,
`vendor` e `symfony` in una modalità a cascata:

    MD lib
    CD lib
    MD vendor
    CD vendor
    MD symfony
    CD symfony

Ora si è nella cartella: `D:\dev\sfproject\lib\vendor\symfony`

![Windows Explorer - l'albero con le cartelle del progetto.](http://www.symfony-project.org/images/more-with-symfony/windows_38.png)

Selezionare tutti i fie (`CTRL-A` in Windows Explorer) dalla cartella del
download (sorgente) e copiare da Downloads in `D:\dev\sfproject\lib\vendor\symfony`.
Si dovrebbero vedere 3819 elementi copiati nella cartella di destinazione:

![Windows Explorer - Copiare 3819 elementi.](http://www.symfony-project.org/images/more-with-symfony/windows_39.png)

### Creazione e inizializzazione

Aprire il prompt dei comandi. Andare nella cartella `D:\dev\sfproject` ed eseguire
il seguente comando:

    PHP lib\vendor\symfony\data\bin\symfony -V

Questo dovrebbe restituire:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

Per inizializzare il progetto, basta lanciare la seguente riga di comando PHP:

    PHP lib\vendor\symfony\data\bin\symfony generate:project sfproject

Questa dovrebbe restituire un elenco di operazioni sui file, compresi alcuni
comandi `chmod 777`:

![Windows Explorer - Inizializzazione del progetto OK.](http://www.symfony-project.org/images/more-with-symfony/windows_40.png)

Sempre all'interno del prompt dei comandi, creare un'applicazione symfony eseguendo il
il seguente comando:

    PHP lib\vendor\symfony\data\bin\symfony generate:app sfapp

Questo dovrebbe restituire ancora una volta un elenco di operazioni sui file,
compresi alcuni comandi `chmod 777 `.

Da questo momento, invece di digitare `PHP lib\vendor\symfony\data\bin\symfony`
ogni volta, copiare il file `symfony.bat` dalla sua posizione di origine:

    copy lib\vendor\symfony\data\bin\symfony.bat

Ora in `D:\dev\sfproject` si ha a disposizione un comodo comando per lanciare
il prompt a riga di comando.

Da `D:\dev\sfproject`, si è in grado di lanciare il classico comando:

    symfony -V

per avere la classica risposta:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

### Creazione dell'applicazione web

Nelle righe seguenti, si suppone che sia stato letto "Sandbox: creazione
dell'applicazione web" per riconfiguare il "Sito web predefinito", in modo
che non possa interferire con la porta 80.

#### Aggiungere un nuovo sito web ad un progetto

Aprire il gestore di IIS da Administration Tools. Sul pannello di sinistra, selezionare l'icona
"Sites", cliccare sul tasto destro. Selezionare "Add Web Site" dal menu popup. Inserire, ad
esempio, "Symfony Project" come nome del sito, `D:\dev\sfproject` per il
"percorso fisico" e lasciare invariati gli altri campi; si dovrebbe vedere questa
finestra di dialogo:

![Gestore di IIS - Aggiungere un sito web.](http://www.symfony-project.org/images/more-with-symfony/windows_41.png)

Cliccare OK. Se compare una piccola `x` sull'icona del sito web (in Features View /
Sites), basta cliccare su "Restart" del pannello a destra per farla scomparire.

#### Verificare se il sito web sta rispondendo

Dal gestore di IIS, selezionare il sito "Symfony Project" e sul pannello di destra,
cliccare su "Browse *.80 (http)".

Dovrebbe comparire lo stesso messaggio di errore avuto quando si stava provando la sandbox:

    HTTP Error 403.14 - Forbidden

Il server web è configurato per non mostrare l'elenco dei contenuti di questa cartella.

Digitare `http://localhost/web` nella barra dell'URL del browser. Si dovrebbe
vedere la pagina "Symfony Project Created", ma con una piccola differenza rispetto
alla stessa pagina dell'inizializzazione della sandbox: non ci sono immagini.

![Internet Explorer - Creazione del progetto symfony - Nessuna immagine.](http://www.symfony-project.org/images/more-with-symfony/windows_42.png)

Le immagini al momento non ci sono, sebbene siano presenti in una cartella `sf`
della libreria di symfony. È facile creare un link alla cartella `/web`
aggiungendo una cartella virtuale in `/web`, chiamata `sf` e puntandola
a `D:\dev\sfproject\lib\vendor\symfony\data\web\sf`.

![IIS Manager - Aggiungere una cartella virtuale sf.](http://www.symfony-project.org/images/more-with-symfony/windows_43.png)

Ora come ci si può aspettare, compare la pagina corretta "Symfony Project Created" con le immagini:

![Internet Explorer - Symfony Project Created - con immagini.](http://www.symfony-project.org/images/more-with-symfony/windows_44.png)

Finalmente l'intera applicazione symfony sta funzionando. Dal browser,
inserire l'URL dell'applicazione, ad esempio `http://localhost/web/sfapp_dev.php`:

![Internet Explorer - La pagina sfapp_dev.php page è OK da localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_45.png)

Si può fare un'ultima prova in modalità locale: verificare "configuration",
"logs" e "timers" nella barra per il web debug, per essere sicuri che il progetto
sia pienamente funzionale.

![Internet Explorer - La pagina log è OK da localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_46.png)

### Configurazione delle applicazioni per Internet

Il progetto symfony ora sta funzionando in locale, come la sandbox, dal server
localhost, localizzato su `http://localhost` o `http://127.0.0.1`.

Ora si vorrebbe essere in grado di accedere all'applicazione da Internet.

La configurazione predefinita del progetto protegge l'applicazione dal fatto di
poter essere eseguita da una postazione remota, anche se, in realtà, dovrebbe
andar bene per accedere a entrambi i file `index.php` e `sfapp_dev.php`.
Eseguire il progetto dal browser, utilizzando l'indirizzo IP esterno del
server (ad esempio `94.125.163.150`) e il FQDN del server virtuale dedicato
(per esempio `12543hpv163150.ikoula.com`). È anche possibile utilizzare entrambi
gli indirizzi dall'interno del server, in quanto non sono mappati su `127.0.0.1`:

![Internet Explorer - Accedere a index.php da Internet è OK.](http://www.symfony-project.org/images/more-with-symfony/windows_47.png)

![Internet Explorer - L'esecuzione di sfapp_dev.php da Internet NON è OK.](http://www.symfony-project.org/images/more-with-symfony/windows_48.png)

Come detto prima, l'accesso a `index.php` e `sfapp_dev.php` da una postazione
remota è OK. L'esecuzione di `sfapp_dev.php`, invece fallisce, poiché non è
consentito per impostazione predefinita. Questo impedisce agli utenti
potenzialmente dannosi di accedere all'ambiente di sviluppo, che contiene
informazioni sensibili sul progetto. È possibile modificare il file `sfapp_dev.php`
per farlo funzionare, ma questo è fortemente sconsigliato.

Ora si è in grado di simulare un dominio reale modificando il file "hosts".

Questo file esegue la risoluzione del nome locale FQDN senza bisogno di
installare il servizio DNS su Windows. Il servizio DNS è disponibile in tutte
le edizioni di Windows Server 2008 R2 e anche in Windows Server 2008 Standard,
Enterprise ed edizioni Datacenter.

Sui sistemi operativi Windows x64, il file "hosts" in modalità predefinita è
posizionato in:
`C:\Windows\SysWOW64\Drivers\etc`

Il file "hosts" è prepopolato per avere la macchina che risolve `localhost` in
`127.0.0.1` su IPv4 e `::1` su IPv6.

Aggiungere un finto nome dominio, come `sfwebapp.local` per poterlo risolvere
localmente.

![Modifiche applicate al file "hosts".](http://www.symfony-project.org/images/more-with-symfony/windows_50.png)

Il progetto symfony ora gira su web, senza DNS, da una sessione del browser 
eseguita all'interno del server web.
