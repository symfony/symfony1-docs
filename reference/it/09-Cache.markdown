Il file di configurazione cache.yml
===================================

Il file di configurazione ~`cache.yml`~ descrive la configurazione della cache per il
livello della vista. Questo file di configurazione è attivo solo se  l'impostazione
[`cache`](#chapter_04_sub_cache) è abilitata in `settings.yml`.

>**TIP**
>La configurazione della classe usata per la cache e
>le sue configurazioni associate sono spiegate nelle sezioni
>[`view_cache_manager`](#chapter_05_view_cache_manager) e
>[`view_cache`](#chapter_05_view_cache) del file di
>configurazione `factories.yml`.

Quando un'applicazione viene creata, symfony genera un file predefinito `cache.yml`
nella cartella dell'applicazione `config/`, che descrive la cache per l'intera
applicazione (sotto la chiave `default`). Nella modalità predefinita, la cache è
globalmente assegnata a `false`:

    [yml]
    default:
      enabled:     off
      with_layout: false
      lifetime:    86400

>**TIP**
>Dal momento che l'impostazione `enabled` è assegnata a `false` nella modalità predefinita,
>è necessario abilitare la cache selettivamente. Si può anche lavorare in questo altro modo:
>abilitare la cache globalmente, dopodiché disabilitarla su pagine specifiche che
>non possono essere messe in cache. L'approccio migliore dovrebbe dipendere da quale dei due metodi
>necessita di meno lavoro per l'applicazione.

Come discusso in sede di introduzione, il file `cache.yml` trae benefici dal
[**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata),
e può includere [**costanti**](#chapter_03_costanti).

>**NOTE**
>Il file di configurazione `cache.yml` è memorizzato nella cache come file PHP; il
>processo è gestito automaticamente dalla [classe](#chapter_14_config_handlers_yml) 
>~`sfCacheConfigHandler`~.

La configurazione predefinita dell'applicazione può essere sovrascritta per un modulo, con
la creazione di un file `cache.yml` nella cartella `config/` del modulo. Le chiavi
principali sono nomi di azioni senza il prefisso `execute` (ad esempio `index` per il
metodo `executeIndex`). Anche un partial o un componente possono essere messi in cache,
utilizzando il nome con un prefisso di sottolineatura (`_`).

Per determinare se un'azione è messa in cache o no, symfony cerca le informazioni
nel seguente ordine:

  * una configurazione per una specifica azione, partial o componente nel
    file di configurazione del modulo, se esiste;

  * una configurazione per un intero modulo nel file di configurazione del modulo, se
    esiste (sotto la chiave `all`);

  * la configurazione predefinita dell'applicazione (sotto la chiave `default`).

>**CAUTION**
>Una richiesta in arrivo con parametri `GET` nella stringa di richiesta o
>inviata con il metodo `POST`, `PUT` o `DELETE` non sarà mai messa
>in cache da symfony, indipendentemente dalla configurazione.

~`enabled`~
-----------

*Predefinito*: `false`

L'impostazione `enabled` abilita o disabilita la cache per l'ambito corrente.

~`with_layout`~
---------------

*Predefinito*: `false`

L'impostazione `with_layout` determina se la cache deve essere per l'intera
pagina (`true`), o solo per l'azione (`false`).

>**NOTE**
>L'opzione `with_layout` non è presa in considerazione per la cache di
>partial e componenti, in quanto non possono essere decorati da un layout.

~`lifetime`~
------------

*Predefinito*: `86400`

L'impostazione `lifetime` definisce il ciclo di vita lato server della cache, in
secondi (`86400` secondi equivalgono a un giorno).

~`client_lifetime`~
-------------------

*Predefinito*: Stesso valore di `lifetime`

L'impostazione `client_lifetime` definisce il ciclo di vita lato client della cache, in
secondi.

Questa impostazione è usata per assegnare automaticamente l'header `Expires` e la
variabile di controllo cache `max-cache`, a meno che gli header `Last-Modified`
o `Expires` non siano già stati assegnati.

È possibile disabilitare la cache lato client assegnando il valore `0`.

~`contextual`~
--------------

*Predefinito*: `false`

L'impostazione `contextual` determina se la cache dipende dal corrente contesto
di pagina o no. L'impostazione quindi è significativa solo quando usata per
partial e componenti.

Quando l'output di un partial è diverso in base al template in cui è
incluso, il partial è detto contestuale e l'impostazione `contextual`
deve essere assegnata a `true`. Il valore predefinito è assegnato a `false`, il che significa
che l'output per partial e componenti è sempre lo stesso, dovunque
siano inclusi.

>**NOTE**
>La cache rimarrà ovviamente diversa per un diverso insieme di parametri.
