Il file di configurazione app.yml
=================================

Il framework symfony mette a disposizione uno specifico file di configurazione 
per le impostazione di un'applicazione, parliamo del file `app.yml`.

Questo file YAML può contenere ogni tipo di impostazione che può servire ad una 
specifica applicazione. Nel codice queste impostazioni sono disponibili tramite
la classe globale `sfConfig` e le chiavi hanno come prefisso la stringa `app_`:

    [php]
    sfConfig::get('app_active_days');

Tutte le impostazioni hanno il prefisso `app_` perché la classe `sfConfig` 
permette l'accesso anche alle [impostazioni di symfony](#chapter_03_sub_impostazioni_della_configurazione)
ed alle [cartelle del progetto](#chapter_03_sub_le_cartelle).

Come già visto nell'introduzione il file `app.yml` ha la 
[consapevolezza dell'ambiente](#chapter_03_consapevolezza_dell_ambiente) e 
beneficia della [configurazione a cascata](#chapter_03_configurazione_a_cascata).

Il file di configurazione `app.yml` è un ottimo posto in cui definire le impostazioni
che cambiano in base all'ambiente (una chiave API per esempio) o le impostazioni
che possono mutare nel tempo (pensate ad un indirizzo email). È inoltre il posto
migliore dove definire le impostazioni che necessitano di essere modificate da
qualcuno che non deve necessariamente conoscere symfony o il PHP 
(un amministratore di sistema per esempio).

>**TIP**
>Astenersi dall'utilizzo del file `app.yml` per collegare parti di business logic
>delle applicazioni.

-

>**NOTE**
>Il file di configurazione `app.yml` è memorizzato in cache come file 
>PHP; questo processo è gestito in modo automatico dalla [classe](#chapter_14_config_handlers_yml)
>~`sfDefineEnvironmentConfigHandler`~.
