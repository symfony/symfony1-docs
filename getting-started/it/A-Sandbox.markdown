Appendice A - La Sandbox
========================

Se il vostro obiettivo è provare symfony per qualche ora, continuate a leggere questo
capitolo e vi mostreremo il modo più veloce per iniziare. Se invece intendete avviare
un progetto reale, dovreste andare al capitolo sull'
[installazione](03-Symfony-Installation#chapter_03).

Il modo più veloce per sperimentare symfony è installare la sandbox di symfony. La
sandbox è un modo facilissimo per installare un progetto symfony pronto all'uso, già
configurato con alcuni pratici default. Questa è un ottima modalità per sperimentare symfony 
senza doversi preoccupare delle problematiche di una installazione che rispetti le best practice
dello sviluppo web.

>**CAUTION**
>La sandbox è preconfigurata per utilizzare SQLite come database,
>occorre verificare che il vostro PHP supporti SQLite (vedere il
>capitolo [Prerequisiti](02-Prerequisites#chapter_02)). Potete anche
>leggere la sezione [Configurare il database](04-Project-Setup#chapter_04_configurare_il_database)
>per imparare come cambiare il database utilizzato nella sandbox.

È possibile scaricare la sandbox nei formati `.tgz` o `.zip` dalla
[pagina di installazione](http://www.symfony-project.org/installation/1_4) di symfony
oppure direttamente ai seguenti URL:

    http://www.symfony-project.org/get/sf_sandbox_1_4.tgz

    http://www.symfony-project.org/get/sf_sandbox_1_4.zip

Scompattare i file da qualche parte nella cartella radice del server web e tutto è pronto.
Il progetto symfony è ora accessibile richiamando lo script `web/index.php`
dal browser.

>**CAUTION**
>Mantenere tutti i file di symfony nella cartella radice del server web va bene per
>testare symfony in locale, ma è veramente una pessima idea su
>un server di produzione, visto che rende tutti i meccanismi interni della vostra
>applicazione potenzialmente visibili agli utenti finali.

Per completare l'installazione, continuare la lettura dei capitoli
[Configurazione del web server](05-Web-Server-Configuration#chapter_05)
e [Gli ambienti](06-Environments#chapter_06).

>**NOTE**
>Dato che la sandbox è un normale progetto symfony dove sono stati eseguiti
>per voi alcuni task e modificate alcune configurazioni, è abbastanza facile
>utilizzarla come punto di partenza per un nuovo progetto.
>Ma tenete a mente che probabilmente dovrete adattare la configurazione; ad esempio
>cambiando le impostazioni relative alla sicurezza (vedere la configurazione di XSS
>e CSRF in questo tutorial).
