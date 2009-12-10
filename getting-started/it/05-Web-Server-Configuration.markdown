Configurazione del Web Server
=============================

Modalità sporca
---------------

Nel capitolo precedente, è stata creata una cartella che ospita il progetto.
Se essa è stata creata all'interno della cartella radice del server web,
è già possibile accedere al progetto stesso tramite un browser web.

Ovviamente, non essendoci una configurazione specifica, quanto fatto finora è molto facile 
e veloce da impostare, ma se si provasse per esempio ad accedere al file `config/databases.yml` 
tramite il browser web si comprenderebbero le conseguenze negative di tale attitudine.
Se un utente venisse a conoscenza che il sito web in questione è sviluppato 
con symfony, avrebbe facilmente accesso a file che contengono informazioni sensibili.

**Mai utilizzare questa tipologia di configurazione su un server di produzione**,
si invita alla lettura della sezione successiva per comprendere come configurare
correttamente il proprio web server. 

Modalità sicura
----------------

In ambito web è buona prassi posizionare all'interno della cartella radice del
server web solo i file che necessitano l'accesso da parte del browser web, come
ad esempio fogli di stile, JavaScript e immagini.
Come opzione predefinita, si raccomanda di posizionare queste tipologie di file all'interno
della cartella `web/`.

All'interno di questa cartella sono presenti alcune sottocartelle delle varie 
risorse web (`css/` e `images/`) e i due file front controller.
Quest'ultimi sono gli unici file PHP che devono essere posizionati all'interno 
della cartella web. Tutti gli altri file PHP devono essere nascosti, non raggiungibili
dal browser web, che è una buona soluzione per la sicurezza dell'applicativo.

### Configurazione del server web

È giunto il momento di cambiare la configurazione di Apache, in modo
da rendere accessibile esternamente il nuovo progetto.

Individuare e aprire il file di configurazione `httpd.conf` e aggiungere le seguenti
righe alla fine dello stesso:

    # Assicurarsi che questa riga sia presente una sola volta nella configurazione
    NameVirtualHost 127.0.0.1:8080

    # Configurazione specifica del progetto
    Listen 127.0.0.1:8080

    <VirtualHost 127.0.0.1:8080>
      DocumentRoot "/home/sfproject/web"
      cartellaIndex index.php
      <cartella "/home/sfproject/web">
        AllowOverride All
        Allow from All
      </cartella>

      Alias /sf /home/sfproject/lib/vendor/symfony/data/web/sf
      <cartella "/home/sfproject/lib/vendor/symfony/data/web/sf">
        AllowOverride All
        Allow from All
      </cartella>
    </VirtualHost>


>**NOTE**
>L'alias `/sf` permette l'accesso alle immagini e file javascript necessari
>alla visualizzazione delle pagine predefinite di symfony e alla web debug toolbar.
>
>Su Windows, bisogna rimpiazzare la riga che definisce l'`Alias` con
>
>     Alias /sf "c:\dev\sfproject\lib\vendor\symfony\data\web\sf"
>
>e `/home/sfproject/web` dovrebbe essere rimpiazzato con:
>
>     c:\dev\sfproject\web

La configurazione appena descritta mette in ascolto Apache sulla porta `8080`,
quindi il sito web sarà raggiungibile al seguente URL:

    http://localhost:8080/

È possibile sostituire `8080` con qualsiasi altro numero, ma è preferibile utilizzare
numeri superiori a `1024`, in quanto non richiedono privilegi di amministratore.

>**SIDEBAR**
>Configurazione di un dominio dedicato
>
>Nel caso in cui si fosse amministratori del server stesso, è meglio 
>creare e configurare dei virtual host, piuttosto che aggiungere una nuova porta 
>ogniqualvolta si voglia iniziare un progetto. Invece di scegliere una porta e
>aggiungere la direttiva `Listen`, scegliere un dominio e aggiungere la direttiva
>`ServerName`:
>
>     # Configurazione del progetto
>     <VirtualHost 127.0.0.1:80>
>       ServerName sfproject.localhost
>       <!-- stessa configurazione di prima -->
>     </VirtualHost>
>
>Il dominio `sfproject.localhost` utilizzato nella configurazione di Apache
>deve essere dichiarato localmente. Su un sistema Linux, modificare il file `/etc/hosts`.
>In un sistema Windows, invece, il file si trova nella cartella `C:\WINDOWS\system32\drivers\etc\`.
>
>Aggiungere la riga seguente:
>
>     127.0.0.1 sfproject.localhost

### Testare la nuova configurazione 

Riavviare Apache e controllare che sia possibile l'accesso alla nuova applicazione,
aprendo un browser web e digitando `http://localhost:8080/index.php/` oppure
`http://sfproject.localhost/index.php/`, a seconda dalla configurazione scelta
nella precedente sezione.

![Congratulazioni](http://www.symfony-project.org/images/jobeet/1_2/01/congratulations.png)

>**TIP**
>Se il modulo `mod_rewrite` di Apache è installato e attivo, è possibile rimuovere
>`index.php/` dall'URL. Questo è possibile grazie alle regole di riscrittura presenti nel file
>`web/.htaccess`.

È possibile accedere all'applicativo nell'ambiente di sviluppo (vedere la 
sezione successiva per maggiori informazioni sui diversi ambienti). Digitare il
seguente URL:

    http://sfproject.localhost/frontend_dev.php/

Dovrebbe essere visibile nell'angolo in alto a destra la web debug toolbar, con 
delle piccole, se tutto è stato configurato correttamente,
grazie all' `sf/` alias.

![web debug toolbar](http://www.symfony-project.org/images/jobeet/1_2/01/web_debug_toolbar.png)

>**NOTE**
>La creazione e configurazione del progetto è leggermente differente se si volesse
>utilizzare symfony in combinazione col server IIS in ambiente Windows.
>Le istruzioni di configurazioni sono disponibili nella 
>[relativa guida](http://www.symfony-project.com/cookbook/1_0/web_server_iis).
