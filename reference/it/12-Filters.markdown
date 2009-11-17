Il File di Configurazione filters.yml
=====================================

Il file di configurazione ~`filters.yml`~ descrive la catena di filtri da
eseguire per ogni richiesta.

Il file di configurazione principale `filters.yml` per un'applicazione
può essere trovato nella cartella `apps/NOME_APP/config/`.

Come accennato nell'introduzione, il file `filters.yml` trae benefici dal
[**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata)
e può includere delle [**costanti**](#chapter_03_costanti).

Il file di configurazione `filters.yml` contiene una lista di definizioni
di filtri con nome:

    [yml]
    FILTRO_1:
      # definizione del filtro 1

    FILTRO_2:
      # definizione del filtro 2

    # ...

Quando il controllore inizializza la catena dei filtri per una richiesta,
legge il file `filters.yml` e registra i filtri, cercando il nome della
classe del filtro (`class`) e i parametri (`param`) da usare per
configurare l'oggetto filtro:

    [yml]
    NOME_DEL_FILTRO:
      class: NOME_DELLA_CLASSE
      param: { ARRAY DI PARAMETRI }

I filtri sono eseguiti nello stesso ordine con cui appaiono nel file di
configurazione. Siccome symfony esegue i filtri in catena, il primo
filtro è eseguito all'inizio e alla fine.

La classe `class` dovrebbe estendere la classe base `sfFilter`.

Se la classe del filtro non può essere caricata automaticamente, si può
definire un percorso `file`, che verrà incluso prima della creazione
dell'oggetto:

    [yml]
    NOME_DEL_FACTORY:
      class: NOME_DELLA_CLASSE
      file:  PERCORSO_ASSOLUTO_DEL_FILE

Quando si sovrascrive il file `filters.yml, occorre mantenere tutti i
filtri ereditati dal file di configurazione:

    [yml]
    rendering: ~
    security:  ~
    cache:     ~
    common:    ~
    execution: ~

Per togliere un filtro, occorre disabilitarlo impostando la chiave
`enabled` a `false`:

    [yml]
    NOME_DEL_FACTORY:
      enabled: false

Ci sono due nomi speciali di filtri: `rendering` e `execution`. Entrambi
sono obbligatori e sono identificati dal parametro `type`. Il filtro
`rendering` dovrebbe essere sempre il primo registrato ed il filtro
`execution` dovrebbe essere sempre l'ultimo:

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

    # ...

    execution:
      class:  sfExecutionFilter
      param:
        type: execution

>**NOTE**
>Il file di configurazione `filters.yml` è messo in cache come file PHP;
>il processo è gestito automaticamente dalla
>[classe](#chapter_14_config_handlers_yml) 
>~`sfFilterConfigHandler`~.

<div class="pagebreak"></div>

Filtri
------

 * [`rendering`](#chapter_12_rendering)
 * [`security`](#chapter_12_security)
 * [`cache`](#chapter_12_cache)
 * [`common`](#chapter_12_common)
 * [`execution`](#chapter_12_execution)

`rendering`
-----------

*Configurazione predefinita*:

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

Il filtro `rendering` si occupa dell'output della risposta inviata al
browser. Essendo il primo filtro che dovrebbe essere registrato, è
anche l'ultimo ad avere la possibilità di gestire la richiesta.

`security`
----------

*Configurazione predefinita*:

    [yml]
     security:
       class: sfBasicSecurityFilter
       param:
         type: security

Il filtro `security` verifica la sicurezza, richiamando il metodo
`getCredential()` dell'azione. Una volta che le credenziali sono
state acquisite, verifica che l'utente abbia le stesse credenziali,
richiamando il metodo `hasCredential()` dell'oggetto `user`.

Il filtro `security` deve avere come tipo `security`.

Una configurazione più granulare del filtro `security` può essere
fatta tramite il file di configurazione
[`security.yml`](#chapter_08).

>**TIP**
>Se l'azione richiesta non è configurata come sicura in `security.yml`,
>il filtro `security` non sarà eseguito.

`cache`
-------

*Configurazione predefinita*:

    [yml]
    cache:
      class: sfCacheFilter
      param:
        condition: %SF_CACHE%

Il filtro `cache` gestisce la cache delle azioni e delle pagine.
Inoltre si occupa di aggiungere i necessari header HTTP per la
cache della risposta (`Last-Modified`, `ETag`, `Cache-Control`,
`Expires`, ...).

`common`
--------

*Configurazione predefinita*:

    [yml]
    common:
      class: sfCommonFilter

Il filtro `common` aggiunge i tag per i JavaScript e i fogli di stile
alla risposta principale, a meno che non siano già stati inclusi.

>**TIP**
>Se si usano gli helper `include_stylesheets()` e `include_javascripts()`
>nel layout, si può disabilitare tranquillamente questo filtro e
>beneficiare di un piccolo miglioramento di prestazioni.

`execution`
-----------

*Configurazione predefinita*:

    [yml]
    execution:
      class:  sfExecutionFilter
      param:
        type: execution

Il filtro `execution` è al centro della catena dei filtri e si
occupa dell'esecuzione di tutte le azioni e le viste.

Il filtro `execution` dovrebbe essere l'ultimo filtro registrato.
