Il file di configurazione generator.yml
=======================================

Il generatore di admin di symfony permette la creazione di una interfaccia di backend
per le classi del modello. Funziona utilizzando sia Propel che Doctrine come ORM.

### Creazione

I moduli del generatore di admin sono creati dai task `propel:generate-admin` o
`doctrine:generate-admin`:

    $ php symfony propel:generate-admin backend Article

    $ php symfony doctrine:generate-admin backend Article

I comandi precedenti creano un modulo `article` generatore di admin per
la classe del modello `Article`.

>**NOTE**
>Il file di configurazione `generator.yml` è messo in cache come file PHP; il
>processo è gestito automaticamente dalla [classe](#chapter_14_config_handlers_yml)
>~`sfGeneratorConfigHandler`~.

### File di configurazione

La configurazione di un modulo può essere fatta nel
file `apps/backend/modules/model/article/generator.yml`:

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        # An array of parameters

Il file contiene due voci principali: `class` e `param`. La classe è
`sfPropelGenerator` per Propel e `sfDoctrineGenerator` per Doctrine.

La voce `param` contiene le opzioni di configurazione per il modulo generato.
`model_class` definisce la classe del modello  associata a questo modulo e
l'opzione `theme` definisce il tema predefinito da usare.

Ma la configurazione principale è presente sotto la voce `config`. È organizzata
in sette sezioni:

  * `actions`: Configurazione predefinita per le azioni trovate sull'elenco e sui form
  * `fields`:  Configurazione predefinita per i campi
  * `list`:    Configurazione per l'elenco
  * `filter`:  Configurazione per i filtri
  * `form`:    Configurazione per il form nuovo/modifica
  * `edit`:    Configurazione specifica per la pagina modifica
  * `new`:     Configurazione specifica per la pagina nuovo

Al momento della generazione, tutte le sezioni sono definite come vuote, sebbene il generatore
di admin definisce ragionevoli impostazioni predefinite per tutte le possibili opzioni:

    [yml]
    generator:
      param:
        config:
          actions: ~
          fields:  ~
          list:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Questo documento descrive tutte le possibili opzioni che è possibile usare per personalizzare il
generatore di admin attraverso la voce `config`.

>**NOTE**
>Tutte le opzioni sono disponibili sia per Propel che per Doctrine e funzionano
>allo stesso modo se non diversamente indicato.

### Campi

Molte opzioni accettano un elenco di campi come parametro. Un campo può essere il nome
di una colonna reale o di una colonna virtuale. In entrambi i casi, deve essere definito un getter
nella classe del modello (`get` più un suffisso costituito dal nome del campo avente la prima lettera maiuscola).

In base al contesto, il generatore di admin è sufficientemente intelligente per conoscere come
visualizzare i campi. Per personalizzare la visualizzazione, è possibile creare un partial o un
componente. Per convenzione, i partial sono prefissati con una riga di sottolineatura (`_`) e
i componenti da una tilde (`~`):

    [yml]
    display: [_title, ~content]

Nell'esempio sopra, il campo `title` sarà visualizzato dal partial `title`,
e il campo `content` dal componente `content`.

Il generatore di admin passa alcuni parametri ai partial e ai componenti:

  * Per le pagine `new` ed `edit`:

    * `form`:       Il form associato al corrente oggetto del modello
    * `attributes`: Un array di attributi HTML da applicare ai widget

  * Per la pagina `list`:

    * `type`:       `list`
    * `MODEL_NAME`: L'attuale istanza dell'oggetto, dove `MODEL_NAME` è il
                    nome della classe del modello in minuscolo.

In una pagina `edit` o `new`, se si desidera conservare il layout a due colonne (etichetta
del campo e widget), il modello del partial o del componente dovrebbe seguire questo
modello:

    [php]
    <div class="sf_admin_form_row">
      <label>
        <!-- Etichetta del campo o contenuto che deve essere visualizzato nella prima colonna -->
      </label>
      <!-- Widget del campo o contenuto che deve essere visualizzato nella seconda colonna -->
    </div>

### Oggetti segnaposto

Alcune opzioni possono prendere segnaposti per oggetti del modello. Un segnaposto è una stringa
che segue il modello: `%%NOME%%`. La stringa `NOME` può essere qualunque cosa possa
essere convertita a un metodo getter valido di un oggetto (`get` più un suffisso
costituito dalla stringa `NOME` con il primo carattere maiuscolo). Per esempio, `%%titolo%%` sarà
sostituito dal valore di `$article->getTitolo()`. I valori segnaposto sono
sostituiti dinamicamente a runtime in accordo con l'oggetto associato nel
contesto corrente.

>**TIP**
>Quando un modello ha una chiave esterna a un'altro modello, Propel e Doctrine
>determinano un getter per il relativo oggetto. Come per ogni altro getter, questo
>può essere usato come segnaposto se si definisce un significativo metodo `__toString()`
>che converte l'oggetto in una stringa.

### Configurazione per l'ereditarietà

La configurazione del generatore di admin è basata sul principio delle configurazioni
a cascata. Le regole di ereditarietà sono le seguenti:

 * `new` e `edit` ereditano da `form` che eredita da `fields`
 * `list` eredita da `fields`
 * `filter` eredita da `fields`

### ~Credenziali~

Nel generatore di admin (sulle liste e sui form) le azioni possono essere nascoste,
in base alle credenziali dell'utente, usando l'opzione `credential` (vedere sotto).
Tuttavia, anche se il collegamento o il bottone non appaiono, le azioni devono
essere adeguatamente protette da accessi illeciti. La gestione delle credenziali nel
generatore di admin si prende cura solo della visualizzazione.

L'opzione `credential` può anche essere usata per nascondere colonne nella pagina dell'elenco.

### Personalizzazione delle azioni

Quando la configurazione non è sufficiente, è possibile sovrascrivere i metodi generati:

 | Metodo                 | Descrizione
 | ---------------------- | -------------------------------------
 | `executeIndex()`       | Azione `list` della vista
 | `executeFilter()`      | Aggiorna i filtri
 | `executeNew()`         | Azione `new` della vista
 | `executeCreate()`      | Crea un nuovo record
 | `executeEdit()`        | Azione `edit` della vista
 | `executeUpdate()`      | Aggiorna un record
 | `executeDelete()`      | Cancella un record
 | `executeBatch()`       | Esegue una azione batch
 | `executeBatchDelete()` | Esegue l'azione batch `_delete`
 | `processForm()`        | Processa il form del record
 | `getFilters()`         | Restituisce i filtri correnti
 | `setFilters()`         | Assegna i filtri
 | `getPager()`           | Restituisce il paginatore dell'elenco
 | `getPage()`            | Ottiene la pagina dal paginatore
 | `setPage()`            | Assegna la pagina al paginatore
 | `buildCriteria()`      | Costruisce i `Criteri` per l'elenco
 | `addSortCriteria()`    | Aggiunge l'ordinamento ai `Criteri` per l'elenco
 | `getSort()`            | Restituisce l'attuale colonna dell'ordinamento
 | `setSort()`            | Imposta l'attuale colonna per l'ordinamento

### Personalizzazione dei modelli

Ogni modello generato può essere sovrascritto:

 | Modello                      | Descrizione
 | ---------------------------- | -------------------------------------
 | `_assets.php`                | Renderizza i CSS e JS da usare per il modello
 | `_filters.php`               | Renderizza i box per i filtri
 | `_filters_field.php`         | Renderizza un singolo campo di filtro
 | `_flashes.php`               | Renderizza i messaggi flash
 | `_form.php`                  | Visualizza il form
 | `_form_actions.php`          | Visualizza le azioni del form
 | `_form_field.php`            | Visualizza un singolo campo del form
 | `_form_fieldset.php`         | Visualizza il fieldset del form 
 | `_form_footer.php`           | Visualizza il piè di pagina del form
 | `_form_header.php`           | Visualizza l'intestazione del form
 | `_list.php`                  | Visualizza l'elenco
 | `_list_actions.php`          | Visualizza le azioni dell'elenco
 | `_list_batch_actions.php`    | Visualizza le azioni batch dell'elenco
 | `_list_field_boolean.php`    | Visualizza un singolo campo booleano nell'elenco
 | `_list_footer.php`           | Visualizza il piè di pagina dell'elenco
 | `_list_header.php`           | Visualizza l'intestazione dell'elenco
 | `_list_td_actions.php`       | Visualizza le azioni dell'oggetto per una riga
 | `_list_td_batch_actions.php` | Visualizza il checkbox per una riga
 | `_list_td_stacked.php`       | Visualizza lo schema della pila per una riga
 | `_list_td_tabular.php`       | Visualizza un singolo campo per l'elenco
 | `_list_th_stacked.php`       | Visualizza un singolo nome colonna per l'intestazione
 | `_list_th_tabular.php`       | Visualizza un singolo nome colonna per l'intestazione
 | `_pagination.php`            | Visualizza la paginazione dell'elenco
 | `editSuccess.php`            | Visualizza la vista `edit`
 | `indexSuccess.php`           | Visualizza la vista `list`
 | `newSuccess.php`             | Visualizza la vista `new`

### Personalizzazione dell'estetica

L'estetica del generatore di admin può essere ottimizzata molto facilmente visto che i modelli
generati definiscono molti attributi HTML `class` e `id`.

Nella pagina `edit` o `new`, ogni contenitore di campo HTML ha le seguenti
classi:

  * `sf_admin_form_row`
  * una classe che dipende dal tipo di campo: `sf_admin_text`, `sf_admin_boolean`,
    `sf_admin_date`, `sf_admin_time`, o `sf_admin_foreignkey`.
  * `sf_admin_form_field_COLUMN` dove `COLUMN` è il nome della colonna

Nella pagina `list`, ogni contenitore di campo HTML ha le seguenti classi:

  * una classe che dipende dal tipo di campo: `sf_admin_text`, `sf_admin_boolean`,
    `sf_admin_date`, `sf_admin_time`, o `sf_admin_foreignkey`.
  * `sf_admin_form_field_COLUMN` dove `COLUMN` è il nome della colonna

<div class="pagebreak"></div>

Opzioni di configurazione disponibili
-------------------------------------

 * [`actions`](#chapter_06_actions)

   * [`label`](#chapter_06_sub_label)
   * [`action`](#chapter_06_sub_action)
   * [`credentials`](#chapter_06_sub_credentials)

 * [`fields`](#chapter_06_fields)

   * [`label`](#chapter_06_sub_label)
   * [`help`](#chapter_06_sub_help)
   * [`attributes`](#chapter_06_sub_attributes)
   * [`credentials`](#chapter_06_sub_credentials)
   * [`renderer`](#chapter_06_sub_renderer)
   * [`renderer_arguments`](#chapter_06_sub_renderer_arguments)
   * [`type`](#chapter_06_sub_type)
   * [`date_format`](#chapter_06_sub_date_format)

 * [`list`](#chapter_06_list)

   * [`title`](#chapter_06_sub_title)
   * [`display`](#chapter_06_sub_display)
   * [`hide`](#chapter_06_sub_hide)
   * [`layout`](#chapter_06_sub_layout)
   * [`params`](#chapter_06_sub_params)
   * [`sort`](#chapter_06_sub_sort)
   * [`max_per_page`](#chapter_06_sub_max_per_page)
   * [`pager_class`](#chapter_06_sub_pager_class)
   * [`batch_actions`](#chapter_06_sub_batch_actions)
   * [`object_actions`](#chapter_06_sub_object_actions)
   * [`actions`](#chapter_06_sub_actions)
   * [`peer_method`](#chapter_06_sub_peer_method)
   * [`peer_count_method`](#chapter_06_sub_peer_count_method)
   * [`table_method`](#chapter_06_sub_table_method)
   * [`table_count_method`](#chapter_06_sub_table_count_method)

 * [`filter`](#chapter_06_filter)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`form`](#chapter_06_form)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`edit`](06-Admin-Generator#chapter_06_edit)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

 * [`new`](#chapter_06_new)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

<div class="pagebreak"></div>

`fields`
--------

La sezione `fields` definisce la configurazione predefinita per ciascun campo. Questa
configurazione è definita per tutte le pagine e può essere sovrascritta singolarmente per ciascuna
pagina di base (`list`, `filter`, `form`, `edit` e `new`).

### ~`label`~

*Predefinito*: Il nome della colonna umanizzato

L'opzione `label` definisce l'etichetta da usare per il campo:

    [yml]
    config:
      fields:
        slug: { label: "URL shortcut" }

### ~`help`~

*Predefinito*: nessuno

L'opzione `help` definisce il testo di aiuto da mostrare per il campo.

### ~`attributes`~

*Predefinito*: `array()`

L'opzione `attributes` definisce gli attributi HTML da passare al widget:

    [yml]
    config:
      fields:
        slug: { attributes: { class: foo } }

### ~`credentials`~

*Predefinito*: nessuno

L'opzione `credentials` definisce le credenziali che l'utente deve avere perché i campi
siano visualizzati. Le credenziali sono forzate solo per l'oggetto list.

    [yml]
    config:
      fields:
        slug:      { credentials: [admin] }
        is_online: { credentials: [[admin, moderator]] }

>**NOTE**
>Le credenziali devono essere definite con le stesse regole del
>file di configurazione `security.yml`.

### ~`renderer`~

*Predefinito*: nessuno

L'opzione `renderer` definisce un callback PHP per eseguire la visualizzazione del campo. Se
definita, sovrascrive ogni altra cosa come partial o componenti.

Il callback è chiamato con il valore del campo e i parametri definiti
dall'opzione `renderer_arguments`.

### ~`renderer_arguments`~

*Predefinito*: `array()`

L'opzione `renderer_arguments` definisce i parametri da passare
al callback PHP `renderer` quando visualizza il campo. È usata unicamente se
l'opzione `renderer` è definita.

### ~`type`~

*Predefinito*: `Text` per colonne virtuali

L'opzione `type` definisce il tipo della colonna. Per impostazione predefinita,
symfony usa il tipo definito nella definizione del modello, ma se si crea una colonna
virtuale, si può sovrascrivere il tipo `Text` predefinito con uno dei tipi validi:

  * `ForeignKey`
  * `Boolean`
  * `Date`
  * `Time`
  * `Text`
  * `Enum` (only available for Doctrine)

### ~`date_format`~

*Predefinito*: `f`

L'opzione `date_format` definisce il formato da usare quando si visualizzano le date. Può
essere qualsiasi formato riconosciuto dalla classe `sfDateFormat`. Questa opzione non è
usata quando il tipo del campo è `Date`.

Per il formato, possono essere utilizzati i seguenti token:

 * `G`: Era
 * `y`: year
 * `M`: mon
 * `d`: mday
 * `h`: Hour12
 * `H`: hours
 * `m`: minutes
 * `s`: seconds
 * `E`: wday
 * `D`: yday
 * `F`: DayInMonth
 * `w`: WeekInYear
 * `W`: WeekInMonth
 * `a`: AMPM
 * `k`: HourInDay
 * `K`: HourInAMPM
 * `z`: TimeZone

`actions`
---------

Il framework definisce alcune azioni incorporate. Queste sono tutte prefissate da un
trattino di sottolineatura (`_`). Ciascuna azione può essere personalizzata con le opzioni descritte in
questa sezione. Le stesse opzioni possono essere usate quando si definisce una azione nelle
voci `list`, `edit`, o `new`.

### ~`label`~

*Predefinito*: La chiave dell'azione

L'opzione `label` definisce l'etichetta da usare per l'azione.

### ~`action`~

*Predefinito*: Definita in base al nome dell'azione

L'opzione `action` definisce il nome dell'azione da eseguire senza il prefisso
`execute`.

### ~`credentials`~

*Predefinito*: nessuno

L'opzione `credentials` definisce le credenziali che l'utente deve avere per l'azione
che deve essere visualizzata.

>**NOTE**
>Le credenziali devono essere definite con le stesse regole del
>file di configurazione `security.yml`.

`list`
------

### ~`title`~

*Predefinito*: Il nome della classe del modello umanizzato e preceduto da "List"

L'opzione `title` definisce il titolo della pagina elenco.

### ~`display`~

*Predefinito*: Tutte le colonne del modello, nell'ordine della loro definizione nel file
dello schema

L'opzione `display` definisce un array di colonne ordinate da visualizzare
nell'elenco.

Il segno di uguale (`=`) prima di una colonna è una convenzione per convertire la stringa in un
link che va alla pagina di `edit` dell'oggetto corrente.

    [yml]
    config:
      list:
        display: [=name, slug]

>**NOTE**
>Vedere anche l'opzione `hide` per nascondere alcune colonne.

### ~`hide`~

*Predefinito*: nessuno

L'opzione `hide` definisce le colonne da nascondere da un elenco. Invece di
specificare le colonne che devono essere visualizzate tramite l'opzione `display`,
a volte è più veloce nascondere alcune colonne:

    [php]
    config:
      list:
        hide: [created_at, updated_at]

>**NOTE**
>Se sono presenti entrambe le opzioni `display` e `hide`, l'opzione
>`hide` è ignorata.

### ~`layout`~

*Predefinito*: `tabular`

*Valori possibili*: ~`tabular`~ or ~`stacked`~

L'opzione `layout` definisce che cosa deve utilizzare l'impaginazione per visualizzare l'elenco.

Con l'impaginazione `tabular`, ciascun valore della colonna è in una propria colonna della tabella.

Con l'impaginazione `stacked`, ciascun oggetto è rappresentato da una singola stringa,
che è definita dall'opzione `params` (vedere sotto).

>**NOTE**
>L'opzione `display` è nuovamente necessaria quando si usa l'impaginazione `stacked` dal
>momento che definisce le colonne che saranno ordinabili dall'utente.

### ~`params`~

*Valore predefinito*: nessuno

L'opzione `params` è usata per definire lo schema di stringhe HTML da usare quando
si utilizza l'impaginazione `stacked`. Questa stringa può contenere segnaposti nell'oggetto del modello:

    [yml]
    config:
      list:
        params:  |
          %%title%% scritto da %%author%% e pubblicato il %%published_at%%.

Il segno di uguale (`=`) prima di una colonna è una convenzione per convertire la stringa in un
link che va alla pagina di `edit` dell'oggetto corrente.

### ~`sort`~

*Valore predefinito*: nessuno

L'opzione `sort` definisce la colonna predefinita per l'ordinamento. È un array costituito da
due componenti: il nome della colonna e la direzione dell'ordinamento: `asc` o `desc`:

    [yml]
    config:
      list:
        sort: [published_at, desc]

### ~`max_per_page`~

*Valore predefinito*: `20`

L'opzione `max_per_page` definisce il numero massimo di oggetti da visualizzare
su una pagina.

### ~`pager_class`~

*Valore predefinito*: `sfPropelPager` per Propel e `sfDoctrinePager` per Doctrine

L'opzione `pager_class` definisce la classe da usare per la paginazione quando viene visualizzato
un elenco.

### ~`batch_actions`~

*Valore predefinito*: `{ _delete: ~ }`

L'opzione `batch_actions` definisce l'elenco di azioni che possono essere eseguite
per una selezione di oggetti in un elenco.

Se non si definisce una `action`, il generatore di admin cercherà per un metodo
chiamato con il nome avente la prima lettera maiuscola preceduto da `executeBatch`.

Il metodo eseguito riceve le chiavi primarie degli oggetti selezionati tramite il
parametro `ids` di richiesta.

>**TIP**
>Il funzionamento delle azioni batch può essere disabilitato impostando l'opzione a un
>array vuoto: `{}`

### ~`object_actions`~

*Valore predefinito*: `{ _edit: ~, _delete: ~ }`

L'opzione `object_actions` definisce l'elenco delle azioni che possono essere eseguite
su ciascun oggetto dell'elenco.

Se non viene definita una azione `action`, il generatore di admin cercherà per un metodo
chiamato con il nome avente la prima lettera maiuscola preceduto da `executeList`.

>**TIP**
>Il funzionamento delle azioni dell'oggetto può essere disabilitato impostando l'opzione a un
>array vuoto: `{}`

### ~`actions`~

*Valore predefinito*: `{ _new: ~ }`

L'opzione `actions` definisce azioni che non prendono oggetti, come la creazione
di un nuovo oggetto.

Se non viene definita una `action`,  il generatore di admin cercherà per un metodo
chiamato con il nome avente la prima lettera maiuscola preceduto da `executeList`.

>**TIP**
>Il funzionamento delle azioni dell'oggetto può essere disabilitato impostando l'opzione a un
>array vuoto: `{}`

### ~`peer_method`~

*Valore predefinito*: `doSelect`

L'opzione `peer_method` definisce un metodo da chiamare per recuperare gli oggetti
da visualizzare nell'elenco.

>**CAUTION**
>Questa opzione esiste unicamente per Propel. Per Doctrine, usare l'opzione
>`table_method`.

### ~`table_method`~

*Valore predefinito*: `doSelect`

L'opzione `table_method` definisce il metodo da chiamare per recuperare gli oggetti
da visualizzare nell'elenco.

>**CAUTION**
>Questa opzione esiste solo per Doctrine. Per Propel, usare l'opzione
>`peer_method`.

### ~`peer_count_method`~

*Valore predefinito*: `doCount`

L'opzione `peer_count_method` definisce il metodo da chiamare per calcolare il
numero di oggetti per il filtro corrente.

>**CAUTION**
>Questa opzione esiste solo per Propel. Per Doctrine, usare
>l'opzione `table_count_method`.

### ~`table_count_method`~

*Valore predefinito*: `doCount`

L'opzione `table_count_method` definisce il metodo da chiamare per calcolare il
numero di oggetti per il filtro corrente.

>**CAUTION**
>Questa opzione esiste solo per Doctrine. Per Propel, usare
>l'opzione `peer_count_method`.

`filter`
--------

La sezione `filter` definisce la configurazione per il form del filtro
visualizzato nella pagina elenco.

### ~`display`~

*Valore predefinito*: Tutti i campi definiti nella classe del filtro del form, nell'ordine
con il quale sono stati definiti

L'opzione `display` definisce l'elenco ordinato dei campi da visualizzare.

>**TIP**
>Essendo che i campi dei filtri sono sempre opzionali, non c'è la necessità di sovrascrivere la
>classe del filtro del form per configurare i campi che devono essere visualizzati.

### ~`class`~

*Valore predefinito*: Il nome della classe del modello preceduto da `FormFilter`

L'opzione `class` definisce la classe form da usare per il form `filter`.

>**TIP**
>Per rimuovere completamente la funzionalità del filtro, assegnare `class` a `false`.

`form`
------

La sezione `form` esiste unicamente come fallback per le sezioni `edit` e `new`
(vedere le regole di ereditarietà nell'introduzione).

>**NOTE**
>Per le sezioni form (`form`, `edit` e `new`), le opzioni `label` e `help`
>sovrascrivono quelle definite nelle classi del form.

### ~`display`~

*Valore predefinito*: Tutti i campi definiti nella classe del form, nell'ordine con
il quale sono stati definiti

L'opzione `display` definisce l'elenco ordinato dei campi da visualizzare.

Questa opzione può anche essere usata per organizzare i campi in gruppi:

    [yml]
    # apps/backend/modules/model/config/generator.yml
    config:
      form:
        display:
          Content: [title, body, author]
          Admin:   [is_published, expires_at]

La configurazione riportata qui sopra definisce due gruppi (`Content` e `Admin`), ciascuno
contenente un sottoinsieme di campi del form.

>**CAUTION**
>Tutti i campi definiti nel modello del form devono essere presenti nell'opzione
>`display`. Se così non fosse, questo potrebbe portare ad inaspettati errori di validazione

### ~`class`~

*Valore predefinito*: Il nome della classe del modello anteposto da `Form`

L'opzione `class` definisce la classe form da usare per le pagine
`edit` e `new`.

>**TIP**
>Anche se è possibile definire una opzione `class` in entrambe le sezioni
>`new` e `edit`, è meglio utilizzare una classe e prendersi cura delle differenze
>utilizzando la logica condizionale.

`edit`
------

La sezione `edit` utilizza le stesse opzioni della sezione `form`.

### ~`title`~

*Predefinito*: "Edit " anteposto dal nome della classe del modello umanizzato

L'opzione `title` definisce la voce titolo della pagina di modifica. Può contenere
segnaposti a oggetti del modello.

### ~`actions`~

*Valore predefinito*: `{ _delete: ~, _list: ~, _save: ~ }`

L'opzione `actions` definisce le azioni disponibili quando viene inviato il form.

`new`
-----

La sezione `new` utilizza le stesse opzioni della sezione `form`.

### ~`title`~

*Predefinito*: "New " anteposto dal nome della classe del modello umanizzato

L'opzione `title` definisce il titolo della nuova pagina. Può contenere
segnaposti a oggetti del modello.

>**TIP**
>Anche se l'oggetto è nuovo, può avere valori predefiniti che si vogliono
>mostrare come parte del titolo.

### ~`actions`~

*Valore predefinito*: `{ _delete: ~, _list: ~, _save: ~, _save_and_add: ~ }`

L'opzione `actions` definisce le azioni disponibili quando viene inviato il form.
