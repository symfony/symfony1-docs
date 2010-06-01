Altri file di configurazione
============================

Questo capitolo descrive altri file di configurazione, che raramente necessitano di
essere cambiati.

~`autoload.yml`~
----------------

La configurazione `autoload.yml` determina quali cartelle necessitano di essere
autocaricate da symfony. Ogni cartella è scansionata per classi e 
interfaccie PHP.

Come discusso in sede di introduzione, il file `autoload.yml` trae beneficio dal
[**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata) e
può includere [**costanti**](#chapter_03_costanti).

>**NOTE**
>Il file di configurazione `autoload.yml` è messo in cache come file PHP; il
>processo è gestito automaticamente dalla [classe](#chapter_14_config_handlers_yml) 
>~`sfAutoloadConfigHandler`~.

La configurazione predefinita va bene per la maggior parte dei progetti:

    [yml]
    autoload:
      # project
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony]

      project_model:
        name:           project model
        path:           %SF_LIB_DIR%/model
        recursive:      true

      # application
      application:
        name:           application
        path:           %SF_APP_LIB_DIR%
        recursive:      true

      modules:
        name:           module
        path:           %SF_APP_DIR%/modules/*/lib
        prefix:         1
        recursive:      true

Ogni configurazione ha un nome e deve essere assegnato con una chiave che ha quel nome. Questo
consente di sovrascrivere la configurazione predefinita.

>**TIP**
>Come si può vedere, la cartella `lib/vendor/symfony/` è esclusa per impostazione predefinita,
>dal momento che symfony utilizza un diverso meccanismo di autocaricamento per le classi del core.

Diverse chiavi possono essere usate per personalizzare il comportamento dell'autocaricamento:

 * `name`: Una descrizione
 * `path`: Il percorso da autocaricare
 * `recursive`: Per cercare le classi PHP nelle sotto-cartelle
 * `exclude`: Un array di nomi di cartelle da escludere nella ricerca
 * `prefix`: Assegnare a `true` se le classi trovate nel percorso devono essere autocaricate solo per un dato modulo (valore predefinito `false`)
 * `files`: Un array di file di cui fare il parsing esplicitamente per le classi PHP
 * `ext`: L'estensione delle classi PHP (valore predefinito `.php`)

Per esempio, se si incorpora una grossa libreria all'interno del progetto sotto la
cartella `lib/` e se essa già supporta l'autocaricamento, si può escluderla
dal sistema di autocaricamento predefinito di symfony per beneficiare di un aumento
delle prestazioni, modificando la configurazione `project` di autocaricamento:

    [yml]
    autoload:
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony, vendor/large_lib]

~`config_handlers.yml`~
-----------------------

Il file di configurazione `config_handlers.yml` descrive la configurazione
usata per gestire le classi usate per analizzare e interpretare tutti gli altri file di
configurazione YAML. Di seguito la configurazione predefinita usata per caricare il file
di configurazione `settings.yml`:

    [yml]
    config/settings.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: sf_

Ogni file di configurazione è definito da una classe (la entry `class`) e può essere
ulteriormente personalizzato definendo alcuni parametri sotto la sezione `param`.

>**TIP**
>Quando si aggiungono i propri gestori di configurazione, occorre specificare
>sia il nome della classe che il percorso completo del file sorgente del
>gestore, rispettivamente sotto le voci `class` e `file`.
>Questo perché la configurazione viene inizializzata prima del meccanismo
>di autoload in sfApplicationConfiguration.

Il file predefinito `config_handlers.yml` definisce le classi da analizzare come segue:

 | File di configurazione | Config classe Handler              |
 | ---------------------- | ---------------------------------- |
 | `autoload.yml`         | `sfAutoloadConfigHandler`          |
 | `databases.yml`        | `sfDatabaseConfigHandler`          |
 | `settings.yml`         | `sfDefineEnvironmentConfigHandler` |
 | `app.yml`              | `sfDefineEnvironmentConfigHandler` |
 | `factories.yml`        | `sfFactoryConfigHandler`           |
 | `core_compile.yml`     | `sfCompileConfigHandler`           |
 | `filters.yml`          | `sfFilterConfigHandler`            |
 | `routing.yml`          | `sfRoutingConfigHandler`           |
 | `generator.yml`        | `sfGeneratorConfigHandler`         |
 | `view.yml`             | `sfViewConfigHandler`              |
 | `security.yml`         | `sfSecurityConfigHandler`          |
 | `cache.yml`            | `sfCacheConfigHandler`             |
 | `module.yml`           | `sfDefineEnvironmentConfigHandler` |

~`core_compile.yml`~
--------------------

Il file di configurazione `core_compile.yml` descrive i file PHP che sono
uniti in un unico grosso file nell'ambiente `prod`, per velocizzare il tempo
necessario a symfony per caricare. Per impostazione predefinita, le principali classi del core di symfony
sono definite in questo file di configurazione. Se l'applicazione si basa su alcune classi
che necessitano di essere caricate per ogni richiesta, è possibile creare un file di
configurazione `core_compile.yml` nel progetto o applicazione e aggiungerli ad esso. Questo è
un estratto della configurazione predefinita:

    [yml]
    - %SF_SYMFONY_LIB_DIR%/autoload/sfAutoload.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfComponent.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfAction.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfActions.class.php

Come discusso in sede di introduzione, il file `core_compile.yml` beneficia del
[**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata) e
può includere [**costanti**](#chapter_03_costanti).

>**NOTE**
>Il file di configurazione `core_compile.yml` è messo in cache come file PHP; il
>processo è gestito automaticamente dalla [classe](#chapter_14_config_handlers_yml)
>~`sfCompileConfigHandler`~.

~`module.yml`~
--------------

Il file di configurazione `module.yml` consente la configurazione di un modulo. Questo
file di configurazione è utilizzato raramente e può contenere solo le voci definite 
di seguito.

Il file `module.yml` richiede di essere memorizzato nella sotto-cartella `config/` di un
modulo che deve essere caricato da symfony. Il codice seguente mostra il contenuto tipico
di `module.yml` con i valori predefiniti per tutte le impostazioni:

    [yml]
    all:
      enabled:            true
      view_class:         sfPHP
      partial_view_class: sf

Se il parametro `enabled` è impostato a `false`, tutte le azioni di un modulo sono
disabilitate. Queste vengono reindirizzate all'azione
[~`module_disabled_module`~](#chapter_04_the_actions_sub_section)/~`module_disabled_action`~
(come definito in [`settings.yml`](#chapter_04)).

Il parametro `view_class` definisce la classe vista usata da tutte le azioni del
modulo (senza il suffisso `View`). Essa deve ereditare da `sfView`.

Il parametro `partial_view_class` definisce la classe vista usata per i partial di
questo modulo (senza il suffisso `PartialView`). ssa deve ereditare da
`sfPartialView`.
