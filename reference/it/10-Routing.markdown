Il file di configurazione routing.yml
=====================================

Il file di configurazione `routing.yml` permette la definizione di rotte.

Il file di configurazione principale `routing.yml` per una applicazione si trova
nella cartella `apps/NOME_APP/config/`.

Il file di configurazione `routing.yml` contiene un elenco di definizioni
di rotte, basate su nomi:

    [yml]
    ROTTA_1:
      # definizione della rotta 1

    ROTTA_2:
      # definizione della rotta 2

    # ...

Quando arriva una richiesta, il sistema delle rotte prova a confrontare una rotta con
l'URL entrante. La prima rotta che combacia vince, in questo modo l'ordine con il quale sono
definite le rotte nel file di configurazione `routing.yml` è importante.

Quando viene letto il file di configurazione `routing.yml`, ciascuna rotta è convertita in
un oggetto della classe `class`:

    [yml]
    NOME_ROTTA:
      class: NOME_CLASSE
      # configurazione della rotta

Il nome `class` dovrebbe estendere la classe base `sfRoute`. Se così non avviene, come
ripiego è usata la classe base `sfRoute`.

>**NOTE**
>Il file di configurazione `routing.yml` è messo in cache come file PHP; il
>processo è gestito automaticamente dalla [classe](#chapter_14_config_handlers_yml)
>~`sfRoutingConfigHandler`~.

<div class="pagebreak"></div>

Le classi per le rotte
----------------------

 * [Configurazione principale](#chapter_10_configurazione_delle_rotte)

   * [`class`](#chapter_10_sub_class)
   * [`options`](#chapter_10_sub_options)
   * [`param`](#chapter_10_sub_param)
   * [`params`](#chapter_10_sub_params)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`type`](#chapter_10_sub_type)
   * [`url`](#chapter_10_sub_url)

 * [`sfRoute`](#chapter_10_sfroute)
 * [`sfRequestRoute`](#chapter_10_sfrequestroute)

   * [`sf_method`](#chapter_10_sub_sf_method)

 * [`sfObjectRoute`](#chapter_10_sfobjectroute)

   * [`allow_empty`](#chapter_10_sub_allow_empty)
   * [`convert`](#chapter_10_sub_convert)
   * [`method`](#chapter_10_sub_method)
   * [`model`](#chapter_10_sub_model)
   * [`type`](#chapter_10_sub_type)

 * [`sfPropelRoute`](#chapter_10_sfpropelroute)

   * [`method_for_criteria`](#chapter_10_sub_method_for_criteria)

 * [`sfDoctrineRoute`](#chapter_10_sfdoctrineroute)

   * [`method_for_query`](#chapter_10_sub_method_for_query)

 * [`sfRouteCollection`](#chapter_10_sfroutecollection)
 * [`sfObjectRouteCollection`](#chapter_10_sfobjectroutecollection)

   * [`actions`](#chapter_10_sub_actions)
   * [`collection_actions`](#chapter_10_sub_collection_actions)
   * [`column`](#chapter_10_sub_column)
   * [`model`](#chapter_10_sub_model)
   * [`model_methods`](#chapter_10_sub_model_methods)
   * [`module`](#chapter_10_sub_module)
   * [`object_actions`](#chapter_10_sub_object_actions)
   * [`prefix_path`](#chapter_10_sub_prefix_path)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`route_class`](#chapter_10_sub_route_class)
   * [`segment_names`](#chapter_10_sub_segment_names)
   * [`with_show`](#chapter_10_sub_with_show)
   * [`with_wildcard_routes`](#chapter_10_sub_with_wildcard_routes)

 * [`sfPropelRouteCollection`](#chapter_10_sfpropelroutecollection)
 * [`sfDoctrineRouteCollection`](#chapter_10_sfdoctrineroutecollection)

<div class="pagebreak"></div>

Configurazione delle rotte
--------------------------

Il file di configurazione `routing.yml` supporta diverse impostazioni per configurare
ulteriormente le rotte. Queste impostazioni sono usate dalla classe `sfRoutingConfigHandler`
per convertire ciascuna rotta in un oggetto.

### ~`class`~

*Predefinito*: `sfRoute` (o `sfRouteCollection` se `type` è `collection`, vedere sotto)

L'impostazione `class` consente di cambiare la classe route da usare per la rotta.

### ~`url`~

*Predefinito*: `/`

L'impostazione `url` è il pattern che deve confrontare una URL entrante con la rotta
che deve essere usata per la richiesta corrente.

Il pattern è costituito da segmenti:

 * variabili (una parola con prefisso [due punti `:`](#chapter_05_sub_variable_prefixes))
 * costanti
 * un carattere jolly (`*`) per confrontare una sequenza di coppie chiave/valore

Ciascun segmento deve essere separato da uno dei separatori predefiniti
([`/` o `.` per impostazione predefinita](#chapter_05_sub_segment_separators)).

### ~`params`~

*Predefinito*: Un array vuoto

L'impostazione `params` definisce un array di parametri associati con la rotta.
Possono essere valori predefiniti per variabili contenute nell'`url`, o qualsiasi altra
pertinente variabile per questa rotta.

### ~`param`~

*Predefinito*: Un array vuoto

Questa impostazione è equivalente all'impostazione `params`.

### ~`options`~

*Predefinito*: Un array vuoto

L'impostazione `options` è un array di opzioni che possono essere passate all'oggetto route
per personalizzare ulteriormente il comportamento. Le seguenti sezioni descrivono le
opzioni disponibili per ciascuna classe di rotte.

### ~`requirements`~

*Predefinito*: Un array vuoto

L'impostazione `requirements` è un array di requisiti che devono essere soddisfatti
dalle variabili `url`. Le chiavi sono le variabili url e i valori sono
espressioni regolari a cui i valori della variabile devono corrispondere.

>**TIP**
>L'espressione regolare sarà inclusa in un'altra espressione
>regolare, questo vuol dire che non c'è bisogno di racchiuderla tra
>separatori e non c'è bisogno di delimitarla con `^` o `$` per fare
>corrispondere l'intero valore.

### ~`type`~

*Predefinito*: `null`

Se valorizzato a `collection`, la rotta verrà letta come un insieme di rotte.

>**NOTE**
>Questa impostazione è automaticamente valorizzata a `collection` dalla classe che gestisce
>il config, se il nome `class` contiene la parola `Collection`. Questo significa che,
>la maggior parte delle volte, non si ha la necessità di usare questa impostazione.

~`sfRoute`~
-----------

Tutte le classi per le rotte estendono la classe base `sfRoute`, che fornisce le
impostazioni richieste per configurare una rotta.

~`sfRequestRoute`~
------------------

### ~`sf_method`~

*Predefinito*: `get`

L'opzione `sf_method` deve essere utilizzata nell'array `requirements`. Si applica
la richiesta HTTP nel processo di confronto rotte.

~`sfObjectRoute`~
-----------------

Tutte le seguenti opzioni di `sfObjectRoute` devono essere utilizzate all'interno dell'impostazione
`options` del file di configurazione `routing.yml`.

### ~`model`~

L'opzione `model` è obbligatoria ed è il nome della classe del modello che è
associato con la rotta corrente.

### ~`type`~

L'opzione `type` è obbligatoria ed è il tipo di rotta che si vuole per il
modello; essa può essere `object` o `list`. Una rotta di tipo `object`
rappresenta un oggetto per un singolo modello e una rotta di tipo `list` rappresenta un
insieme di oggetti di modelli.

### ~`method`~

L'opzione `method` è obbligatoria. È il metodo da chiamare sulla classe del modello
per recuperare l'oggetto o gli oggetti associati a questa rotta. Deve essere un
metodo statico. Il metodo è chiamato con i parametri della rotta scansionata come
parametro.

### ~`allow_empty`~

*Predefinito*: `true`

Se l'opzione `allow_empty` è `false`, la rotta lancerà una
eccezione 404 se nessun oggetto è restituito dalla chiamata a `method` di `model`.

### ~`convert`~

*Predefinito*: `toParams`

L'opzione `convert` è un metodo da chiamare per convertire l'oggetto di un modello in un array
di parametri adatto a generare rotte basate su questo oggetto di modello.
Deve restituire un array con almeno i parametri richiesti dallo schema
della rotta (come definito dall'impostazione `url`).

~`sfPropelRoute`~
-----------------

### ~`method_for_criteria`~

*Predefinito*: `doSelect` per insiemi, `doSelectOne` per oggetti singoli

L'opzione `method_for_criteria` definisce il metodo chiamato sulla classe del
modello Peer per recuperare l'oggetto (o gli oggetti) associati con la richiesta corrente. Il
metodo è chiamato con i parametri della rotta scansionata come parametro.

~`sfDoctrineRoute`~
-------------------

### ~`method_for_query`~

*Predefinito*: none

L'opzione `method_for_query` definisce il metodo da chiamare sul modello per
recuperare l'oggetto (o gli oggetti) associati con la richiesta corrente. L'oggetto della richiesta
corrente è passato come parametro.

Se l'opzione non è assegnata, la richiesta è solo "eseguita" con il metodo
`execute()`.

~`sfRouteCollection`~
---------------------

La classe base `sfRouteCollection` rappresenta un insieme di rotte.

~`sfObjectRouteCollection`~
---------------------------

### ~`model`~

L'opzione `model` è obbligatoria ed è il nome della classe del modello che deve essere
associato con la rotta corrente.

### ~`actions`~

*Predefinito*: `false`

L'opzione `actions` definisce un array di azioni autorizzate per la rotta. Le
azioni devono essere un sottoinsieme di tutte le azioni disponibili: `list`, `new`, `create`,
`edit`, `update`, `delete` e `show`.

Se l'opzione è `false`, il valore predefinito, tutte le azioni saranno disponibili
eccetto per l'azione `show` se l'opzione `with_show` è `false` (vedere
sotto).

### ~`module`~

*Predefinito*: Il nome della rotta

L'opzione `module` definisce il nome del modulo.

### ~`prefix_path`~

*Predefinito*: `/` seguito dal nome della rotta

L'opzione `prefix_path` definisce un prefisso da anteporre a tutti i modelli delle `url`.
Può essere un qualunque modello valido e può contenere variabili e diversi segmenti.

### ~`column`~

*Predefinito*: `id`

L'opzione `column` definisce la colonna del modello da usare come identificativo
univoco per l'oggetto del modello.

### ~`with_show`~

*Predefinito*: `true`

L'opzione `with_show` è usata quando l'opzione `actions` è `false` per
determinare se l'azione `show` deve essere inclusa nell'elenco delle azioni
autorizzate per la rotta.

### ~`segment_names`~

*Predefinito*: array('edit' => 'edit', 'new' => 'new'),

`segment_names` definisce le parole da usare nei modelli degli `url` per le
azioni `edit` e `new`.

### ~`model_methods`~

*Predefinito*: Un array vuoto

L'opzione `model_methods` definisce i metodi da chiamare per recuperare l'oggetto
(o gli oggetti) dal modello (vedere l'opzione `method` di `sfObjectRoute`). Questo
si realizza definendo i metodi `list` e `object`:

    [yml]
    model_methods:
      list:   getObjects
      object: getObject

### ~`requirements`~

*Predefinito*: `\d+` per `column`

L'opzione `requirements` definisce un array di requisiti da applicare
alle variabili della rotta.

### ~`with_wildcard_routes`~

*Predefinito*: `false`

L'opzione `with_wildcard_routes` permette a ogni azione di essere acceduta tramite due
rotte jolly: una per un singolo oggetto e un'altra per insiemi di oggetti.

### ~`route_class`~

*Predefinito*: `sfObjectRoute`

L'opzione `route_class` può sovrascrivere l'oggetto della rotta predefinita utilizzata
per l'insieme.

### ~`collection_actions`~

*Predefinito*: Un array vuoto

Lìopzione `collection_actions` definisce un array di azioni aggiuntive
disponibili per l'insieme di rotte.

### ~`object_actions`~

*Predefinito*: Un array vuoto

L'opzione `object_actions` definisce un array di azioni aggiuntive disponibili
per l'oggetto delle rotte.

~`sfPropelRouteCollection`~
---------------------------

La classe per la rotta `sfPropelRouteCollection` estende `sfRouteCollection` e
cambia la classe predefinita della rotta a `sfPropelRoute` (vedere sopra
l'opzione `route_class`).

~`sfDoctrineRouteCollection`~
-----------------------------

La classe per la rotta `sfDoctrineRouteCollection` estende `sfRouteCollection`,
e cambia la classe predefinita della rotta a `sfDoctrineRoute` (vedere sopra
l'opzione `route_class`).
