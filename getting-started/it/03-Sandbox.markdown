La Sandbox
===========

Se il vostro obiettivo è provare symfony per qualche ora, continuate a leggere questo
capitolo e vi mostreremo il modo più veloce per iniziare. Se invece intendete avviare
un progetto reale, potete tranquillamente saltare questo capitolo e
[andare](04-Symfony-Installation) direttamente al prossimo.

Il modo più veloce per sperimentare symfony è installare la symfony sandbox. La
sandbox è un modo facilissimo per installare un progetto symfony pronto all'uso, già
configurato con alcuni pratici default. Questa è un ottima modalità per sperimentare symfony 
senza doversi preoccupare delle problematiche di una installazione che rispetti le best practice
dello sviluppo web.

>**CAUTION**
>La sandbox è preconfigurata per utilizzare SQLite come database
>engine, dovete quindi verificare che il vostro PHP supporti SQLite (vedere il
>capitolo [Prerequisiti](02-Prerequisites) ). Potete anche
>leggere la sezione [Configurare il database](05-Project-Setup#chapter_05_sub_configurare_il_database)
>per imparare come cambiare il database utilizzato nella sandbox.

È possibile effettuare il download della symfony sandbox nei formati `.tgz` o `.zip` dalla
[pagina di installazione](http://www.symfony-project.org/installation/1_2) di symfony
oppure direttamente ai seguenti URL:

    http://www.symfony-project.org/get/sf_sandbox_1_2.tgz

    http://www.symfony-project.org/get/sf_sandbox_1_2.zip

Scompattare i file da qualche parte nella directory root del server web e tutto è pronto.
Il progetto symfony è ora accessibile richiedendo lo script `web/index.php`
dal browser.

>**CAUTION**
>Mantenere tutti i file di symfony nella directory root del server web va bene per
>testare symfony in locale, ma è veramente una pessima idea su
>un server di produzione visto che rende tutti i meccanismi interni della vostra
>applicazione potenzialmente visibili agli utenti finali.

Per completare l'installazione continuare la lettura dei capitoli
[Configurazione del web server](06-Web-Server-Configuration)
e [Gli ambienti](07-Environments).

>**NOTE**
>Dato che la sandbox è un normale progetto symfony dove sono stati eseguiti
>per voi alcuni task e modificate alcune configurazioni, è abbastanza facile
>utilizzarla come punto di partenza per un nuovo progetto.
>Ma tenete a mente che probabilmente dovrete adattare la configurazione; ad esempio
>cambiando i settaggi relativi alla sicurezza (vedere la configurazione di XSS
>e CSRF più avanti in questo tutorial).
