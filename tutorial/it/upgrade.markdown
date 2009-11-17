Aggiornare i Progetti da 1.2 a 1.3
==================================

Questo documento descrive i cambiamenti fatti in symfony 1.3 e cosa
serve per aggiornare i propri progetti basati su symfony 1.2.

Per informazioni più dettagliate su cosa è stato modificato/aggiunto in
symfony 1.3, si può leggere il tutorial
[Che c'è di nuovo?](http://www.symfony-project.org/tutorial/1_3/it/whats-new).

>**CAUTION**
>symfony 1.3 è compatibile con PHP 5.2.4 e versioni successive.
>Potrebbe funzionare anche con PHP 5.2.0 fino a 5.2.3, ma non è garantito.

Come aggiornare?
----------------

Per aggiornare un progetto:

  * Verificare che tutti i plugin usati dal progetto siano compatibili con
    symfony 1.3

  * Se non si usa uno strumento di gestione dei sorgenti, fare una copia di
    backup del progetto.

  * Aggiornare symfony alla versione 1.3

  * Aggiornare i plugin alle rispettive versioni 1.3

  * Lanciare il task `project:upgrade1.3` dalla cartella del progetto, per
    eseguire un aggiornamento automatico:

        $ php symfony project:upgrade1.3

    Il task può essere lanciato diverse volte, senza effetti collaterali.
    Ogni volta che si aggiorna ad una versione di symfony 1.3 beta / RC o all
    versione finale symfony 1.3, si deve lanciare il task.

  * Occorre ricostruire i modelli ed i form, a causa di alcune modifiche
    descritte più avanti:

        # Doctrine
        $ php symfony doctrine:build --all-classes

        # Propel
        $ php symfony propel:build --all-classes

  * Pulire la cache:

        $ php symfony cache:clear

Le sezioni seguenti spiegano le modifiche principali fatte in symfony 1.3 che
necessitano di alcuni aggiornamenti (automatici o meno).

Deprecati
---------

Durante lo sviluppo di symfony 1.3, sono stati deprecate e rimosse alcune
impostazioni, classi, metodi, funzioni e task. Si faccia riferimento al
[Deprecati in 1.3](http://www.symfony-project.org/tutorial/1_3/it/deprecated)
per maggiori informazioni.

Autoload
--------

Da symfony 1.3, i file nella cartella `lib/vendor/` non sono più caricati
automaticamente. Se si desidera l'autoload di alcune sottocartelle di
`lib/vendor/`, aggiungere una nuova voce nel file di configurazione
`autoload.yml` dell'applicazione:

    [yml]
    autoload:
      vendor_some_lib:
        name:      vendor_some_lib
        path:      %SF_LIB_DIR%/vendor/some_lib_dir
        recursive: on

Il caricamento automatico della cartella `lib/vendor/` era problematico per
diverse ragioni:

  * Inserendo in `lib/vendor/` una libreria che ha già un proprio meccanismo
    di autoload, symfony riesaminerà i file, aggiungendo molte informazioni
    superflue nella cache
    (si veda #5893 - http://trac.symfony-project.org/ticket/5893).

  * Se la cartella di symfony non si chiama esattamente `lib/vendor/symfony/`,
    l'autoloader del progetto riesaminerà l'intera cartella di symfony, il che
    potrebbe causare alcuni problemi
    (si veda #6064 - http://trac.symfony-project.org/ticket/6064).

L'autoload in symfony 1.3 non tiene più conto delle differenze tra maiuscole e
minuscole.

Routing
-------

I metodi `sfPatternRouting::setRoutes()`, `sfPatternRouting::prependRoutes()`,
`sfPatternRouting::insertRouteBefore()` e `sfPatternRouting::connect()` non
restituiscono le rotte come array, come invece facevano nelle precedenti versioni.

L'opzione `lazy_routes_deserialize` è stata rimossa, poiché non è più necessaria.

Da symfony 1.3, la cache per il routing è disabilitata, poiché questa è l'opzione
migliore per la maggior parte dei progetti in cui le prestazioni sono importanti.
Quindi, se la cache del routing non era stata personalizzata, sarà automaticamente
disabilitata per tutte le applicazioni. Se, dopo l'aggiornamento alla versione 1.3,
il progetto risulta più lento, si potrebbe voler aggiungere la cache del routing,
per vedere se questo migliora le cose. Questa è la configurazione predefinita di
symfony 1.2, che si può aggiungere nuovamente in `factories.yml`:

    [yml]
    routing:
      param:
        cache:
          class: sfFileCache
          param:
            automatic_cleaning_factor: 0
            cache_dir:                 %SF_CONFIG_CACHE_DIR%/routing
            lifetime:                  31556926
            prefix:                    %SF_APP_DIR%/routing

JavaScript e Fogli di Stile
---------------------------

### Rimozione del filtro common

`sfCommonFilter` è stato rimosso. Questo filtro serviva per inserire automaticamente
i tag per JavaScript e fogli di stile nel contenuto della risposta. Ora si deve
includere manualmente questi elementi richiamando esplicitamente gli helper
`include_stylesheets()` e `include_javascripts()` nel layout:

    [php]
    <?php include_javascripts() ?>
    <?php include_stylesheets() ?>

Il filtro è stato rimosso per diverse ragioni:

 * Abbiamo già una soluzione migliore, semplice e più flessibile (gli
   helper `include_stylesheets()` ed `include_javascripts()`)

 * Anche se il filtro può essere rimosso facilmente, non è semplice farlo,
   perché richiede di conoscerne l'esistenza ed il suo lavoro "dietro le quinte"

 * L'uso degli helper fornisce un controllo più granulare su quando e dove
   includere gli elementi nel layout (ad esempio, i fogli di stile nel tag `head`
   ed i JavaScript subito prima della fine del tag `body`)

 * È sempre meglio essere espliciti piuttosto che impliciti (nessuna magia e
   nessun effetto indesiderato; si veda la mailing list degli utenti per
   le molte lamentele riguardo alla questione)

 * Fornisce un piccolo miglioramento di velocità

Come aggiornare?

  * Il filtro `common` deve essere rimosso da tutti i file di configurazione
    `filters.yml` (questo viene fatto automaticamente dal task `project:upgrade1.3`).

  * Occorre aggiungere le chiamate `include_stylesheets()` e `include_javascripts()`
    nei layout, in modo da avere lo stesso comportamento di prima (questo
    viene fatto automaticamente dal task `project:upgrade1.3` per i layout HTML
    contenuti nelle cartelle `templates/` delle applicazioni - ma solo se hanno
    un tag `<head>`; occorre aggiornare manualmente ogni altro layout e ogni altra
    pagina che non ha un layout, ma si basa su file JavaScript e/o fogli di stile).

Task
----

Le seguenti classi di task sono state rinominate:

  symfony 1.2               | symfony 1.3
  ------------------------- | --------------
  `sfConfigureDatabaseTask` | `sfDoctrineConfigureDatabaseTask` o `sfPropelConfigureDatabaseTask`
  `sfDoctrineLoadDataTask`  | `sfDoctrineDataLoadTask`
  `sfDoctrineDumpDataTask`  | `sfDoctrineDataDumpTask`
  `sfPropelLoadDataTask`    | `sfPropelDataLoadTask`
  `sfPropelDumpDataTask`    | `sfPropelDataDumpTask`

La firma per i task `*:data-load` è cambiata. Specifiche cartelle o file ora devono
essere forniti come parametri. L'opzione `--dir` è stata rimossa.

    $ php symfony doctrine:data-load data/fixtures/dev

### Formatter

Il terzo parametro di `sfFormatter::format()` è stato rimosso.

Escape
------

`esc_js_no_entities()`, referito da `ESC_JS_NO_ENTITIES` è stato aggiornato per
gestire correttamente i caratteri non-ANSI. Prima di questa modifica,
solo i caratteri con valori ANSI da `37` a `177` non subivano escape. Ora subiranno
escape solamente i caratteri di barra `\`, le virgolette `'` e `"` e gli "a capo"
`\n` e `\r`.

Integrazione con Doctrine
-------------------------

### Versione Richiesta di Doctrine

Gli external verso Doctrine sono stati aggiornati all'ultima versione di
Doctrine 1.2. Si possono avere informazioni sulle novità di Doctrine 1.2 
[qui](http://www.doctrine-project.org/upgrade/1_2).

### Cancellazione nell'Admin Generator

La cancellazione multipla nell'admin generator è stata modificata per
scorrere le righe ed eseguire il metodo `delete()` su ciascuna di esse
singolarmente, piuttosto che eseguire una singola query DQL per cancellarle
tutte. Questo per fare in modo che siano invocati gli eventi per la
cancellazione di ogni singola riga.

### Sovrascrittura dello Schema Doctrine dei Plugin

Si può sovrascrivere il modello incluso in uno schema YAML di un plugin,
semplicemente definendo lo stesso modello nel proprio schema locale.
Ad esempio, per aggiungere una colonna "email" al modello `sfGuardUser`
di sfDoctrineGuardPlugin, si aggiungano le seguenti righe a
`config/doctrine/schema.yml`:

    [yml]
    sfGuardUser:
      columns:
        email:
          type: string(255)

>**NOTE**
>L'opzione package è una caratteristiche di Doctrine ed è usata per gli schemi
>dei plugin di symfony. Questo non vuol dire che la caratteristica package possa
>essere usata indipendentemente per impacchettare i propri modelli. Deve essere
>usata direttamente e solo per i plugin di symfony.

### Log delle Query

L'integrazione con Doctrine esegue il log delle query usando `sfEventDispatcher`,
invece che accedendo direttamente all'oggetto logger. Inoltre, l'oggetto di questi
eventi è la connessione o il comando che sta eseguendo la query. Il log viene
fatto dalla nuova classe `sfDoctrineConnectionProfiler`, a cui si può accedere
tramite l'oggetto `sfDoctrineDatabase`.

Plugin
------

Se si usa il metodo `enableAllPluginsExcept()` per gestire i plugin abilitati
nella classe `ProjectConfiguration`, si faccia attenzione al fatto che ora
i plugin sono ordinati per nome, in modo da assicurare coerenza tra 
piattaforme diverse.

Widget
------

La classe `sfWidgetFormInput` è ora astratta. I campi input text ora sono creati
con la classe `sfWidgetFormInputText`. Questa modifica facilita l'introspezione
delle classi dei form.

Mailer
------

Symfony 1.3 ha un nuovo factory mailer. Quando si crea una nuova applicazione,
il file `factories.yml` ha delle impostazioni predefinite per gli ambienti
`test` e `dev`. Se si aggiorna un progetto esistente, si potrebbe voler
aggiornare il file `factories.yml` con la seguente configurazione per tali
ambienti:

    [yml]
    mailer:
      param:
        delivery_strategy: none

Con tale configurazione, i messaggi email non saranno inviati. Ovviamente,
saranno ancora nei log ed il tester `mailer` funzionerà nei test funzionali.

Se invece si preferisce ricevere tutti i messaggi email ad un unico indirizzo,
si può usare la strategia di invio `single_address` (ad esempio per l'ambiente
`dev`):

    [yml]
    dev:
      mailer:
        param:
          delivery_strategy: single_address
          delivery_address:  foo@example.com

YAML
----

sfYAML è ora compatibile con le specifiche 1.2. Ecco le modifiche da fare
nei file di configurazione:

 * I booleani possono ora essere rappresentati solo con le stringhe `true`
   e `false`. Se si usavano stringhe alternative, contenute nell'elenco
   seguente, queste vanno sostituite con `true` o `false`:

    * `on`, `y`, `yes`, `+`
    * `off`, `n`, `no`, `-`

Il task `project:upgrade` dirà dove è usata la vecchia sintassi, ma non la
modificherà (per evitare ad esempio di perdere dei commenti). Le correzioni
vanno eseguite manualmente.

Se non si vogliono controllare tutti i proprio file YAML, si può forzare il
parser YAML ad usare le specifiche YAML 1.1, usando il metodo
`sfYaml::setSpecVersion()`:

    [php]
    sfYaml::setSpecVersion('1.1');
  
Propel
------

Le classi di build personalizzate di Propel, usate nelle versioni
precedenti, sono state sostituite dalle nuovi classi di comportamento di
Propel 1.4. Per sfruttare questo miglioramento, occorre aggiornare il
file `propel.ini` del progetto.

Rimuovere le vecchie classi di build:

    ; builder settings
    propel.builder.peer.class              = plugins.sfPropelPlugin.lib.builder.SfPeerBuilder
    propel.builder.object.class            = plugins.sfPropelPlugin.lib.builder.SfObjectBuilder
    propel.builder.objectstub.class        = plugins.sfPropelPlugin.lib.builder.SfExtensionObjectBuilder
    propel.builder.peerstub.class          = plugins.sfPropelPlugin.lib.builder.SfExtensionPeerBuilder
    propel.builder.objectmultiextend.class = plugins.sfPropelPlugin.lib.builder.SfMultiExtendObjectBuilder
    propel.builder.mapbuilder.class        = plugins.sfPropelPlugin.lib.builder.SfMapBuilderBuilder

Aggiungere le nuove classi di comportamento:

    ; behaviors
    propel.behavior.default                        = symfony,symfony_i18n
    propel.behavior.symfony.class                  = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfony
    propel.behavior.symfony_i18n.class             = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18n
    propel.behavior.symfony_i18n_translation.class = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18nTranslation
    propel.behavior.symfony_behaviors.class        = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
    propel.behavior.symfony_timestampable.class    = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorTimestampable

Il task `project:upgrade` prova a fare questa modifica, ma potrebbe non riuscirci
in caso di modifiche locali a `propel.ini`.

La classe `BaseFormFilterPropel` veniva generata in modo non corretto in
`lib/filter/base` in symfony 1.2. Questo problema è stato risolto in symfony 1.3;
la classe ora è generata in `lib/filter`. Il task `project:upgrade` si
occuperà di spostare questo file.

Test
----

Il file iniziale per i test unitari, `test/bootstrap/unit.php`, è stato migliorato
per gestire meglio il caricamento automatico dei file delle classi. Le seguenti
righe devono essere aggiunte a tale script:

    [php]
    $autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
    $autoload->loadConfiguration(sfFinder::type('file')->name('autoload.yml')->in(array(
      sfConfig::get('sf_symfony_lib_dir').'/config/config',
      sfConfig::get('sf_config_dir'),
    )));
    $autoload->register();

Il task `project:upgrade` prova a fare questa modifica, ma potrebbe non riuscirci
in caso di modifiche locali a `test/bootstrap/unit.php`.

