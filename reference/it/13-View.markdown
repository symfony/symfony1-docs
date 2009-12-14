Il file di configurazione view.yml
==================================

Il livello della vista può essere configurato modificando il file
di configurazione ~`view.yml`~.

Come accennato nell'introduzione, il file `view.yml` beneficia del
[**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata),
e può includere delle [**costanti**](#chapter_03_costanti).

>**CAUTION**
>Questo file di configurazione è per lo più deprecato a favore di helper
>usati direttamente nei template o di metodi chiamati dalle azioni.

Il file di configurazione `view.yml` contiene un elenco di configurazioni
della vista:

    [yml]
    NOME_VISTA_1:
      # configurazione

    NOME_VISTA_2:
      # configurazione

    # ...

>**NOTE**
>Il file di configurazione `view.yml` viene messo in cache come file
>PHP; tale processo è gestito autonomamente dalla
>[classe](#chapter_14_config_handlers_yml) ~`sfViewConfigHandler`~.

Layout
------

*Configurazione predefinita*:

    [yml]
    default:
      has_layout: true
      layout:     layout

Il file di configurazione `view.yml` definisce il ~layout~ predefinito
usato dall'applicazione. Per impostazione predefinita, il nome è `layout`,
quindi symfony decora ogni pagina con il file `layout.php`, che si trova
nella cartella `templates/` dell'applicazione. Si può anche disabilitare
del tutto il processo di decorazione impostando la voce `~has_layout~`
a `false`.

>**TIP**
>Il layout viene disabilitato automaticamente per le richieste XML HTTP
>e per tutti i tipi di contenuto che non siano HTML, a meno che non sia
>specificato diversamente in modo esplicito.

Fogli di Stile
--------------

*Configurazione Predefinita*:

    [yml]
    default:
      stylesheets: [main.css]

La voce `stylesheets` definisce un array di fogli di stile da usare per
la vista corrente.

>**NOTE**
>L'inclusione dei fogli di stile definiti in `view.yml` può essere fatta
>manualmente con l'helper `include_stylesheets()` oppure automaticamente
>con il [filtro `common`](#chapter_12_common).

Se si definiscono diversi file, symfony li includerà nello stesso ordine
in cui sono definiti:

    [yml]
    stylesheets: [main.css, foo.css, bar.css]

Si può anche cambiare l'attributo `media` oppure omettere il suffisso
`.css`:

    [yml]
    stylesheets: [main, foo.css, bar.css, print.css: { media: print }]

Questa impostazione è *deprecata* in favore dell'helper `use_stylesheet()`:

    [php]
    <?php use_stylesheet('main.css') ?>

>**NOTE**
>Nel file di configurazione predefinito `view.yml`, il file riferito è
>`main.css` e non `/css/main.css`. Di fatto, entrambe le definizioni sono
>equivalenti, poiché symfony aggiunge `/css/` ai percorsi relativi.

JavaScript
----------

*Configurazione Predefinita*:

    [yml]
    default:
      javascripts: []

La voce `javascripts` definisce un array di file JavaScript da usare per
la vista corrente.

>**NOTE**
>L'inclusione dei file JavaScript definiti in `view.yml` può essere fatta
>manualmente con l'helper `include_javascripts()`.

Se si definiscono diversi file, symfony li includerà nello stesso ordine
in cui sono definiti:

    [yml]
    javascripts: [foo.js, bar.js]

Si può anche omettere il suffisso `.js`:

    [yml]
    javascripts: [foo, bar]

Questa impostazione è *deprecata* in favore dell'helper `use_javascript()`:

    [php]
    <?php use_javascript('foo.js') ?>

>**NOTE**
>Se si usano percorsi relativi, come `foo.js`, symfony aggiunge `/js/`
>all'inizio.

Meta e HTTP Meta
----------------

*Configurazione Predefinita*:

    [yml]
    default:
      http_metas:
        content-type: text/html

      metas:
        #title:        symfony project
        #description:  symfony project
        #keywords:     symfony, project
        #language:     en
        #robots:       index, follow

Le impostazioni `http_metas` e `metas` consentono di definire dei meta
tag da includere nel layout.


>**NOTE**
>L'inclusione dei meta tag definiti in `view.yml` può essere fatta
>manualmente con gli helper `include_metas()` e `include_http_metas()`.

Queste impostazioni sono *deprecate* in favore dell'uso di puro HTML
nel layout, per i meta statici (come il content-type) o in favore
di uno slot, per i meta dinamici (come il titolo o la descrizione).

>**TIP**
>Se ha senso, il meta HTTP `content-type` è modificato automaticamente
>per includere il set di caratteri definito nel
>[file di configurazione `settings.yml`](#chapter_04_sub_charset),
>se non è già presente.
