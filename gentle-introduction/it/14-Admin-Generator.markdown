Capitolo 14 - Admin Generator
=============================

Molte applicazioni sono basate su dati memorizzati in un database e offrono un'interfaccia
per accedervi. Symfony automatizza il processo ripetitivo della creazione di un modulo
che fornisca capacità di manipolazione dei dati basati su Propel o Doctrine. Se l'oggetto
del modello è definito in modo appropriato, symfony può addirittura generare un intero
sito di amministrazione automaticamente. Questo capitolo descrive l'utilizzo dell'admin
generator, distribuito con i plugin di Propel e Doctrine. Esso si basa su uno speciale
file di configurazione con una sintassi completa, quindi la maggior parte di questo
capitolo ne descrive le varie possibilità.

Generazione del codice basato sul modello
-----------------------------------------

In un'applicazione web, le operazioni di accesso ai dati possono essere categorizzate
come segue:

  * Creazione di una riga
  * Recupero di una o più righe
  * Aggiornamento di una riga (e modifica delle sue colonne)
  * Eliminazione di una riga

Queste operazioni sono così comuni che hanno un acronimo: CRUD (dalle iniziali inglesi di
Create, Replace, Update, Delete). Molte pagine possono essere ridotte a una di esse.
Per esempio, in un'applicazione forum, la lista degli ultimi messaggi è un'operazione di
recupero, mentre la risposta è un'operazione di creazione.

Le azioni e i template di base che implementano le operazioni CRUD per una data tabella
sono create ripetutamente nelle applicazioni web. In symfony, il livello del modello
contiene sufficienti informazioni per consentire la generazione del codice per le
operazioni CRUD, in modo da accelerare la parte iniziale delle interfacce di backend.

### Esempio di modello di dati

Lungo questo capitolo, i listati dimostreranno le capacità dell'admin generator di symfony
basandosi su un semplice esempio, che ricorderà il capitolo 8. Questo è il ben noto
esempio dell'applicazione blog, contenente le due classi `BlogArticle` e `BlogComment`.
Il listato 14-1 mostra lo schema, illustrato in figura 14-1.

Listato 14-1 - Schema Propel dell'esempio di applicazione blog

    [yml]
    propel:
      blog_category:
        id:               ~
        name:             varchar(255)
      blog_author:
        id:               ~
        name:             varchar(255)
      blog_article:
        id:               ~
        title:            varchar(255)
        content:          longvarchar
        blog_author_id:   ~
        blog_category_id: ~
        is_published:     boolean
        created_at:       ~
      blog_comment:
        id:               ~
        blog_article_id:  ~
        author:           varchar(255)
        content:          longvarchar
        created_at:       ~

Figura 14-1 - Esempio di modello dei dati

![Esempio di modello dei dati](http://www.symfony-project.org/images/book/1_4/F1401.png "Esempio di modello dei dati")

Non c'è una regola particolare da seguire durante lo schema, per consentire la generazione
del codice. Symfony userà lo schema così com'è e interpreterà i suoi attributi per
generare l'amministrazione.

>**TIP**
>Per ottenere il massimo da questo capitolo, occorre implementare effettivamente gli
>esempi. Si otterrà una migliore comprensione di cosa symfony genera e di cosa può essere
>realizzato col codice generate, se si ha una visione di ogni passo descritto nei listati.
>L'inizializzazione del modello è semplice: basta richiamare il task `propel:build`
>(o `doctrine:build`):
>
>      $ php symfony propel:build --all --no-confirmation

Poiché l'interfaccia di amministrazione si basa su alcuni metodi magici per facilitare
i compiti, occorre creare un metodo `__toString()` in ogni classe del modello.

    [php]
    class BlogAuthor extends BaseBlogAuthor
    {
      public function __toString()
      {
        return $this->getName();
      }
    }

    class BlogCategory extends BaseBlogCategory
    {
      public function __toString()
      {
        return $this->getName();
      }
    }

    class BlogArticle extends BaseBlogArticle
    {
      public function __toString()
      {
        return $this->getTitle();
      }
    }

>**TIP**
>Si può fare in modo che Propel aggiunga automaticamente il metodo magico `__toString()`.
>Basta aggiungere la proprietà `primaryString: true` alla colonna desiderata.
>Doctrine invece lo aggiunge automaticamente alla prima colonna di testo disponibile.

Amministrazione
---------------

Symfony può generare moduli del backend, basati sulle definizioni delle classi del modello trovate nel
file `schema.yml`. Si può creare un intero sito di amministrazione, composto interamente
da moduli di amministrazione generati. Gli esempi di questa sezione descriveranno i moduli
di amministrazione aggiunti a un'applicazione chiamata `backend`. Se il progetto non ha
ancora un'applicazione con questo nome, occorre crearla richiamando il seguente task:

    $ php symfony generate:app backend

I moduli di amministrazione interpretano il modello tramite uno speciale file di
configurazione chiamato `generator.yml`, che può essere modificato per estendere tutte le
componenti generate e l'apparenza del modulo stesso. Tali moduli beneficiano del consueto
meccanismo descritto nei capitoli precedenti (layout, rotte, configurazione
personalizzata, caricamento automatico, eccetera). Si possono anche sovrascrivere azioni e
template generati, per poter integrare le caratteristiche desiderate nell'amministrazione
generate, ma `generator.yml` dovrebbe soddisfare la maggior parte delle esigenze comuni e
restringere l'uso del codice PHP a quelle veramente specifiche.

>**NOTE**
>Anche se la maggior parte delle esigenze è coperta dal file di configurazione
>`generator.yml`, si può anche configurare un modulo di amministrazione tramite una classe
>di configurazione, come vedremo più avanti in questo capitolo.

### Inizializzare un modulo di amministrazione

Con symonfy, si può costruire un'amministrazione basandosi sui modelli. Un modulo viene
generato in base a un oggetto Propel o Doctrine, usando uno dei seguenti task:

    // Propel
    $ php symfony propel:generate-admin backend BlogArticle --module=article
    
    // Doctrine
    $ php symfony doctrine:generate-admin backend BlogArticle --module=article

>**NOTE**
>I moduli di amministrazione sono basati su un'architettura REST. Il task
>`propel:generate-admin` aggiunge automaticamente al file di configuraizone `routing.yml`
>una rotta di questo tipo:
>
>     [yml]
>     # apps/backend/config/routing.yml
>     article:
>       class: sfPropelRouteCollection
>       options:
>         model:                BlogArticle
>         module:               article
>         with_wildcard_routes: true
>
>Si può anche creare una propria rotta e passare il nome come parametro del task, al posto
>del nome della classe del modello:
>
>     $ php symfony propel:generate-admin backend article --module=article

Questa chiamata genera un modulo `article` nell'applicazione `backend`, basato sulla
definizione della classe `BlogArticle`, accessibile da:

    http://localhost/backend_dev.php/article

L'aspetto del modulo generato, illustrato nelle figure 14-2 e 14-3, è abbastanza
sofisticato da renderlo subito usabile per un'applicazione commerciale.

>**TIP**
>Se non si vede questo aspetto (mancano stili e immagini), occorre installare i file
>necessari nel progetto, eseguendo il task `plugin:publish-assets` task:
>
>     $ php symfony plugin:publish-assets

Figura 14-2 - Vista `list` del modulo `article` nell'applicazione `backend`

![vista list del modulo article nell'applicazione backend](http://www.symfony-project.org/images/book/1_4/F1402.png "vista list del modulo article nell'applicazione backend")

Figura 14-3 - Vista `edit` del modulo `article` nell'applicazione `backend`

![vista edit del modulo article nell'applicazione backend](http://www.symfony-project.org/images/book/1_4/F1403.png "vista edit del modulo article nell'applicazione backend")

### Uno sguardo al codice generato

Il codice del modulo amministrativo `article`, nella cartella
`apps/backend/modules/article/`, sembra vuoto perché è stato solo inizializzato. Il modo
migliore per controllare il codice generato è quello di interagire tramite il browser,
quindi andare a guardare nella cartella `cache/`. Il listato 14-2 elenca le azioni e i
template generati nella cache.

Listato 14-2 - Elementi amministrativi generati, in `cache/backend/ENV/modules/autoArticle/`

    // Azioni in actions/actions.class.php
    index            // Mostra l'elenco delle righe della tabelle
    filter           // Aggiorna i filtri usati dalla lista
    new              // Mostra il form per inserire una nuova riga
    create           // Crea una nuova riga
    edit             // Mostra un form per modificare una riga
    update           // Aggiorna una riga esistente
    delete           // Elimina una riga
    batch            // Esegue un'azione su una lista di righe scelte
    batchDelete      // Esegue un'eliminazione su una lista di righe scelte

    // In templates/
    _assets.php
    _filters.php
    _filters_field.php
    _flashes.php
    _form.php
    _form_actions.php
    _form_field.php
    _form_fieldset.php
    _form_footer.php
    _form_header.php
    _list.php
    _list_actions.php
    _list_batch_actions.php
    _list_field_boolean.php
    _list_footer.php
    _list_header.php
    _list_td_actions.php
    _list_td_batch_actions.php
    _list_td_stacked.php
    _list_td_tabular.php
    _list_th_stacked.php
    _list_th_tabular.php
    _pagination.php
    editSuccess.php
    indexSuccess.php
    newSuccess.php

Questo mostra che un modulo amministrativo generato è composto principalmente da tre viste,
`list`, `new` e `edit`. Se si guarda nel codice, lo si troverà molto modulare, leggibile
ed estensibile.

### Introduzione al file di configurazione `generator.yml`

I moduli amministrativi generati si basano su paremetri trovati nel file di configurazione
`generator.yml`. Per vedere la configurazione predefinita di un nuovo modulo
amministrativo, aprire il file `generator.yml`, che si trova nella cartella
`backend/modules/article/config/generator.yml`, riprodotto nel listato 14-3.

Listato 14-3 - Configurazione predefinta del generatore, in `backend/modules/article/config/generator.yml`

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        model_class:           BlogArticle
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              BlogArticle
        plural:                BlogArticles
        route_prefix:          blog_article
        with_propel_route:     1
        actions_base_class:    sfActions

        config:
          actions: ~
          fields:  ~
          list:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Questa configurazione è sufficiente per generare l'amministrazione di base. Ogni
personalizzazione va aggiunta sotto la chiave `config`. Il listato 14-4 mostra un tipico
file `generator.yml` personalizzato.

Listato 14-4 - Configurazione tipica completa del generatore

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        model_class:           BlogArticle
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              BlogArticle
        plural:                BlogArticles
        route_prefix:          blog_article
        with_propel_route:     1
        actions_base_class:    sfActions

        config:
          actions:
            _new: { label: "Crea un nuovo articolo" }

          fields:
            author_id:    { label: Article author }
            published_on: { credentials: editor }

          list:
            title:          Articoli
            display:        [title, blog_author, blog_category]
            fields:
              published_on: { date_format: dd/MM/yy }
            layout:         stacked
            params:         |
              %%is_published%%<strong>%%=title%%</strong><br /><em>da %%blog_author%%
              in %%blog_category%% (%%created_at%%)</em><p>%%content%%</p>
            max_per_page:   2
            sort:           [title, asc]

          filter:
            display: [title, blog_category_id, blog_author_id, is_published]

          form:
            display:
              "Post":       [title, blog_category_id, content]
              "Workflow":   [blog_author_id, is_published, created_at]
            fields:
              published_at: { help: "Data di pubblicazione" }
              title:        { attributes: { style: "width: 350px" } }

          new:
            title: Nuovo articolo

          edit:
            title: Modifica articolo "%%title%%"

In questa configurazione ci sono sei sezioni. Quattro di esse rappresentano le viste
(`list`, `filter`, `new` e `edit`) e due di esse sono "virtuali" (`fields` e `form`)
ed esistono solo per scopi di configurazione.

Le sezioni seguenti spiegano in dettaglio tutti i parametri che possono essere usati nel
file di configurazione.

Configurazione del generatore
-----------------------------

Il file di configurazione del generatore è molto potente e consente di alterare la
generazione in molti modi. Ma queste capacità hanno un prezzo: la descrizione globale
della sintassi è lunga da leggere e da imparare, rendendo questo capitolo il più lungo
di questo libro.

Gli esempi di questa sezione modificheranno il modulo `article` e il modulo `comment`,
basato sulla definizione della classe `BlogComment`. Per creare ques'ultimo, basta
lanciare il comando:

    $ php symfony propel:generate-admin backend BlogComment --module=comment

Figura 14-4 - Cheat sheet dell'administration generator

![cheat sheet dell'administration generator](http://www.symfony-project.org/images/book/1_4/F1404.png "Cheat sheet dell'administration generator")

### Campi

Le colonne predefinite della vista `list` sono quelle definite in `schema.yml`. I campi
delle viste `new` e `edit` sono quelli definiti nel form associato al modello
(`BlogArticleForm`). Nel file `generator.yml` si può scegliere quali campi mostrare,
quali nascondere e aggiungere campi nuovi, anche se non hanno una corrispondenza diretta
con l'oggetto del modello.

#### Impostazioni dei campi

Il generatore dell'amministrazione crea un campo per ogni colonna nel file `schema.yml`.
Sotto la chiave `fields` si può modificare il modo in cui i campi sono mostrati,
formattati, ecc. Per esempio, le impostazioni dei campi mostrate nel listato 14-5
definiscono una classe personalizzata per la label del campo `title` e una label e un
testo di aiuto per il campo `content`. Le sezioni seguenti descrivono nel dettaglio il
funzionamento di ogni parametro.

Listato 14-5 - Impostare una label personalizzata per un evento

    [yml]
    config:
      fields:
        title:
          label: Titolo articolo
          attributes: { class: foo }
        content: { label: Body, help: Inserire il corpo dell'articolo }

Oltre alla definizione per tutte le viste, si possono configurare le impostazioni solo
per una vista specifica (`list`, `filter`, `form`, `new` o `edit`), come mostrato nel
listato 14-6.

Listato 14-6 - Impostazioni globali vista per vista

    [yml]
    config:
      fields:
        title:     { label: Titolo articolo }
        content:   { label: Corpo }

      list:
        fields:
          title:   { label: Titolo }

      form:
        fields:
          content: { label: Corpo articolo }

C'è un principio generale: ogni impostazione inserita sotto la chiave `fields` può
essere sovrascritta nelle aree specifiche delle viste. Le regole di sovrascrittura sono
le seguenti:

  * `new` e `edit` ereditano da `form`, che eredita da `fields`
  * `list` eredita da `fields`
  * `filter` eredita da `fields`

#### Aggiungere dei campi

I campi definiti nella sezione `fields` possono essere mostrati, nascosti, ordinati e
raggruppati in vari modi per ogni vista. La chiave `display` è usata per questo scopo.
Per esempio, per riordinare i campi del modulo `comment`, usare il codice del listato 14-7.

Listato 14-7 - Scegliere i campi da mostrare, in `modules/comment/config/generator.yml`

    [yml]
    config:
      fields:
        article_id: { label: Articolo }
        created_at: { label: Data pubblicazione }
        content:    { label: Corpo }

      list:
        display:    [id, blog_article_id, content]

      form:
        display:
          NONE:     [blog_article_id]
          Editable: [author, content, created_at]

La vista `list` mostrerà tre colonne, come mostrato in figura 14-5, mentre le viste `new`
e `edit` mostreranno quattro campi, divisi in due gruppi, come in figura 14-6.

Figura 14-5 - Impostazioni personalizzate delle colonne nella vista `list`

![Impostazioni personalizzate delle colonne nella vista list](http://www.symfony-project.org/images/book/1_4/F1405.png "Impostazioni personalizzate delle colonne nella vista list")

Figura 14-6 - Campi raggruppati nella vista `edit`

![Campi raggruppati nella vista edit](http://www.symfony-project.org/images/book/1_4/F1406.png "Campi raggruppati nella vista edit")

Dunque si possono usare le impostazioni `display` in due modi:

  * Per la vista `list`: inserire i campi in un array per scegliere le colonne da mostrare e l'ordine in cui appaiono.
  * Per le viste `form`, `new` e `edit`: usare un array associativo per raggruppare i campi col nome del gruppo come chiave, oppure `NONE` per un gruppo senza nome. I valori sono ancora un array di colonne ordinate. Si faccia attenzione a elencare tutti i campi obbligatori del form o si potrebbero avere problemi con la validazione (vedere capitolo 10).

#### Campi personalizzati

Di fatto, i campi configurati in `generator.yml` non necessitano di colonne reali
corrispondenti nello schema. Se la classe relativa offre un getter, può essere usato come
un campo per la vista `list`. Se non ci sono getter o setter, si può comunque usare nella
vista `edit`. Per esempio, si può estendere il modello `BlogArticle` con un metodo
`getNbComments()` simile a quello nel listato 14-8.

Listato 14-8 - Aggiungere un getter personalizzato nel modello, in `lib/model/BlogArticle.php`

    [php]
    public function getNbComments()
    {
      return $this->countBlogComments();
    }

In questo modo `nb_comments` è disponibile come campo nel modulo generato (si noti che
il getter usa una versione camelCase del nome del campo), come nel listato 14-9.

Listato 14-9 - I getter personalizzati forniscono colonne aggiuntive, in `backend/modules/article/config/generator.yml`

    [yml]
    config:
      list:
        display:  [title, blog_author, blog_category, nb_comments]

La vista `list` dell'articolo `article` è mostrata in figura 14-07.

Figura 14-07 - Campo personalizzato nella vista `list`

![Campo personalizzato nella vista list](http://www.symfony-project.org/images/book/1_4/F1407.png "Campo personalizzato nella vista list")

#### Campi con partial

Il codice che si trova nel modello deve essere indipendente dalla presentazione. L'esempio
del metodo `getArticleLink()` visto prima non rispecchia questo principio di separazione
tra i livelli, perché del codice della vista sta nel livello del modello. Di fatto, se si
prova a usare questa configurazione, ci si troverà con un collegamento mostrato come un
tag `<a>`, perché sottoposto a escape. Per raggiungere lo stesso scopo in modo corretto,
si dovrebbe inserire il codice che mostra HTML per un campo personalizzato in un partial.
Fortunatamente, il generatore di amministrazione consente di dichiarare un campo con un
trattino basso come prefisso. In questo caso, il file `generator.yml` del listato 14-11 va
modificato come nel listato 14-12.

Listato 14-12 - Partial usato come colonna aggiuntiva. Usare il prefisso `_`

    [yml]
    config:
      list:
        display: [id, _article_link, created_at]

Per funzionare, un partial `_article_link.php` deve essere creato nella cartella
`modules/comment/templates/`, come nel listato 14-13.

Listato 14-13 - Esempio di partial per la vista `list`, in `modules/comment/templates/_article_link.php`

    [php]
    <?php echo link_to($BlogComment->getBlogArticle()->getTitle(), 'blog_article_edit', $BlogComment->getBlogArticle()) ?> 

Si noti che il template del partial ha accesso all'oggetto corrente tramite una variabile
chiamata come la classe (in questo esempio, `$BlogComment`).

Figura 14-08 - Campo partial nella vista `list`

![Campo partial nella vista list](http://www.symfony-project.org/images/book/1_4/F1408.png "Campo partial nella vista list")

La separazione dei livelli è stata rispettata. Se ci si abitua a questa seprazione, ci si
ritroverà con applicazioni più facili da mantenere.

Se occorre personalizzare le proprietà di un campo partial, si può fare come per un campo
normale, sotto la chiave `fields`. Basta non includere il prefisso (`_`) nella chiave.
Vedere come esempio il listato 14-14.

Listato 14-14 - Proprietà di un campo partial personalizzate sotto la chiave `fields`

    [yml]
    config:
      fields:
        article_link: { label: Articolo }

Se un partial si affolla con troppa logica, probabilmente andrebbe sostituito con un
component. Basta cambiare il prefisso `_` in `~`, come si può vedere nel listato 14-15.

Listato 14-15 - I component possono essere usati come colonne aggiuntive. Usare il prefisso `~`

    [yml]
    config:
      list:
        display: [id, ~article_link, created_at]

Nel template generato, questo risulterà in una chiamata al componente `articleLink` del
modulo corrente.

>**NOTE**
>I campi personalizzati e i campi partial possono essere usati nelle viste `list`, `new`,
`edit` e `filter`. Se si usa lo stesso partial per viste diverse, il contesto (`list`,
`new`, `edit` o `filter`) viene memorizzato nella variabile `$type`.

### Personalizzazione della vista

Per cambiare l'aspetto delle viste `new`, `edit` e `list`, si potrebbe essere tentati di
modificare i template. Ma siccome sono generati automaticamente, non è una buona idea.
Invece, si dovrebbe usare il file `generator.yml`, che può fare quasi tutto senza
sacrificare la modularità.

#### Cambiare il titolo di una vista

Oltre a un insieme personalizzato di campi, le pagine `list`, `new` e `edit` possono
avere un titolo personalizzato. Per esempio, se si vuole personalizzare il titolo delle
viste `article`, basta fare come nel listato 14-16. La vista `edit` risultante è
illustrata in figura 14-09.

Listato 14-16 - Impostare un titolo per ogni vista, in `backend/modules/article/config/generator.yml`

    [yml]
    config:
      list:
        title: Lista di articoli

      new:
        title: Nuovo articolo

      edit:
        title: Modifica articolo %%title%% (%%id%%)

Figura 14-09 - Titolo personalizzato nella vista `edit`

![Titolo personalizzato nella vista edit](http://www.symfony-project.org/images/book/1_4/F1409.png "Titolo personalizzato nella vista edit")

Poiché i titoli predefiniti usano il nome della classe, spesso vanno bene, a patto di
aver usato dei nomi di classi espliciti.

>**TIP**
>Nei valori stringa di `generator.yml`, si può accedere al valore di un campo tramite il
>nome del campo stesso, preceduto e seguito da `%%`.

#### Aggiungere degli aiuti

Nelle viste `list`, `new`, `edit` e `filter` si possono aggiungere dei testi di aiuto, per
descrivere meglio i campi mostrati. Per esempio, per aggiungere un aiuto al campo
`blog_article_id` della vista `edit` del modulo `comment`, aggiungere una proprietà `help`
sotto la chiave `fields`, come nel listato 14-17. Il risultato è mostrato in figura 14-10.

Listato 14-17 - Impostare un testo di aiuto nella vista `edit`, in `modules/comment/config/generator.yml`

    [yml]
    config:
      edit:
        fields:
          blog_article_id: { help: Il commento correlato a questo articolo }

Figura 14-10 - Testo di aiuto nella vista `edit`

![Testo di aiuto nella vista edit](http://www.symfony-project.org/images/book/1_4/F1410.png "Testo di aiuto nella vista edit")

Nella vista `list`, i testi di aiuto sono mostrati nell'intestazione della colonna, mentre
nelle viste `new`, `edit` e `filter` sono mostrati sotto ciascun campo.

#### Modificare il formato delle date

Si possono mostrare le date con un formato personalizzato, usando l'opzione `date_format`,
come nel listato 14-18.

Listato 14-18 - Formattare una data nella vista `list`

    [yml]
    config:
      list:
        fields:
          created_at: { label: Pubblicato, date_format: dd/MM }

Accetta gli stessi parametri dell'helper `format_date()`, descritto nel capitolo
precedente.

>**SIDEBAR**
>I template dell'amministrazione sono pronti per i18N
>
>I moduli generati sono fatti di stringhe di interfaccia (nomi di azioni, paginazione,
>ecc.) e di stringhe personalizzate (titoli, label, messaggi di aiuto, ecc.).
>
>Le traduzioni delle stringhe di interfaccia sono distribuite con symfony per moltissime
>lingue. Ma si possono anche aggiungere i propri o sovrascrivere quelli predefiniti,
>creando un file XLIFF per il catalogo `sf_admin` nella cartella `i18n`
>(`apps/backend/i18n/sf_admin.XX.xml`, dove `XX` è il codice ISO della lingua).
>
>Tutte le stringhe personalizzate trovate nei template generati sono anche automaticamente
>internazionalizzate, (cioè incluse in una chiamata all'helper `__()`). Questo vuol dire
>che si possono facilmente tradurre, aggiungendo le traduzioni delle frasi in un file
>XLIFF, nella cartella `apps/backend/i18n/`, come spiegato nel capitolo precedente.
>
>Si può modificare il catalogo predefinito da usare per le stringhe personalizzate,
>specificando il parametro `i18n_catalogue`:
>
>     [yml]
>     generator:
>       class: sfPropelGenerator
>       param:
>         i18n_catalogue: admin

### Personalizzazioni specifiche della vista `list`

La vista `list` può mostrare i dettagli di una riga in modo tabulare o impilato. Contiene
inoltre filtri, paginazione e opzioni di ordinamento. Queste caratteristiche possono
essere modificate dalla configurazione, come descritto nelle sezioni seguenti.

#### Cambiare il layout

I collegamenti predefiniti tra la vista `list` e la vista `edit` avviene nella colonna
della chiave primaria. Tornando alla figura 14-08, si vede che la colonna `id` nella lista
dei commenti non solo mostra la chiave primaria di ogni commento, ma anche un collegamento
che consente agli utenti di accedere alla vista `edit`.

Se si preferisce che il collegamento ai dettagli di una riga appaiono su una colonna
diversa, basta aggiungere un prefisso `=` al nome della colonna, sotto la chiave `display`.
Il listato 14-19  mostra come rimuovere `id` dai campi mostrati da `list` e come inserire
il collegamento nel campo `content`. Vedere la figura 14-11 per una schermata.

Listato 14-19 - Spostare il collegamento per la vista `edit` nella vista `list`, in `modules/comment/config/generator.yml`

    [yml]
    config:
      list:
        display: [_article_link, =content]

Figura 14-11 - Spostare il collegamento per la vista `edit` su un'altra colonna

![Spostare il collegamento per la vista edit su un'altra colonna](http://www.symfony-project.org/images/book/1_4/F1411.png "Spostare il collegamento per la vista edit su un'altra colonna")

La vista `list` usa il layout predefinito `tabular`, in cui i campi appaiono come colonne,
come mostrato in precedenza. Si può usare anche il layout `stacked`, in cui i campi sono
concatenati in una singola stringa, che si espande per tutta la larghezza della tabella.
Se si sceglie il layout `stacked`, bisogna impostare nella chiave `params` lo schema che
definisce il valore di ciascuna linea della lista. Per esempio, il listato 14-20 definisce
un layout `stacked` per la vista `list` del modulo `comment`. Il risultato appare in
figura 14-12.

Listato 14-20 - Usare un layout `stacked` nella vista `list`, in `modules/comment/config/generator.yml`

    [yml]
    config:
      list:
        layout:  stacked
        params:  |
          %%=content%%<br />
          (inviato da %%author%% in data %%created_at%% riguardo %%_article_link%%)
        display:  [created_at, author, content]

Figura 14-12 - Layout `stacked` nella vista `list`

![Layout stacked nella vista list](http://www.symfony-project.org/images/book/1_4/F1412.png "Layout stacked nella vista list")

Si noti che un layout `tabular` si aspetta un array di campi sotto la chiave `display`,
mentre un layout `stacked` usa la chiave `params` per la generazione del codice HTML di
ciascuna riga. Tuttavia, l'array `display` è usato ugualmente nel layout `stacked`, per
determinare quali intestazioni delle colonne sono disponibili, ai fini dell'ordinamento.

#### Filtrare i risultati

In una vista `list` si possono aggiungere un insieme di filtri interattivi. Con questi
filtri gli utenti possono visualizzare meno risultati e trovare più velocemente quelli
che cercano. Si possono configurare i filtri sotto la chiave `filter`, con un array di
nomi di campi. Per esempio, aggiungere un filtro sui campi `blog_article_id`, `author` e
`created_at` della vista `list` del modulo `comment`, come nel listato 14-21, per mostrare
un riquadro di filtri simile a quello in figura 14-13.

Listato 14-21 - Impostare i filtri nella vista `list`, in `modules/comment/config/generator.yml`

    [yml]
    config:
      list:
        layout:  stacked
        params:  |
          %%=content%% <br />
          (inviato da %%author%% in data %%created_at%% riguardo %%_article_link%%)
        display:  [created_at, author, content]

      filter:
        display: [blog_article_id, author, created_at]

Figura 14-13 - Filtri nella vista `list`

![Filtri nella vista list](http://www.symfony-project.org/images/book/1_4/F1413.png "Filtri nella vista list")

I filtri mostrati da symfony dipendono dal tipo di colonna definito nello schema e possono
essere personalizzati nella classe del form del filtro:

  * Per le colonne di testo (come il campo `author` nel modulo `comment`), il filtro è un input text, che consente ricerche basate su testi (aggiungendo automaticamente i caratteri jolly).
  * Per le chiavi esterne (come il campo `blog_article_id` nel modulo `comment`), il filtro è un elenco a tendina delle righe della tabella correlata. Le opzioni sono quelle restituite dal metodo `__toString()` della classe correlata.
  * Per le colonne data (come il campo `created_at` nel modulo `comment`), il filtro è una coppia di date, che consentono di scegliere un intervallo di tempo.
  * Per le colonne booleane, il filtro è un elenco a tendina con i valori `true`, `false` e `true or false`, con l'ultimo come predefinito.

Come le viste `new` e `edit` sono legate a classi di form, i filtri usano le classi di
form di filtri associate col modello (per esempio `BlogArticleFormFilter`per il modello
`BlogArticle`). Si possono personalizzare i campi dei filtri definendo una classe per il
form dei filtri, sfruttando la potenza del framework dei form e usando tutti i widget dei
filtri disponibili. È molto facile, basta definire una classe `class` sotto la chiave
`filter`, come mostrato nel listato 14-22.

Listato 14-22 - Personalizzare la classe del form per i filtri

    [yml]
    config:
      filter:
        class: BackendArticleFormFilter

>**TIP**
>Per disabilitare completamente i filtri, si può specificare `false` come valore di
`class`.

Si possono anche usare dei partial per implementare una logica personalizzata nei filtri.
Ogni partial riceve il form `form` e gli attributi HTML `attributes`, da usare per la resa
dell'elemento del form. Il listato 14-23 mostra un esempio di implementazione di un
partial.

Listato 14-23 - Uso di un partial nel filtro

    [php]
    // Definire il partial, in templates/_state.php
    <?php echo $form[$name]->render($attributes->getRawValue()) ?>

--

    [yml]
    # Aggiungere il partial alla lista dei filtri, in config/generator.yml
    config:
      filter: [created_at, _state]

#### Ordinare la lista

In una vista `list`, le intestazioni della tabelle sono collegamenti che possono essere
usati per riordinare la lista, come mostrato in figura 14-18. Questa intestazioni sono
mostrate in entrambi i layout, `tabular` e `stacked`. Cliccando questi collegamenti, la
pagina viene ricaricata con un parametro `sort`, che riordina la lista di conseguenza.

Figura 14-14 - Le intestazioni della tabella della vista `list` sono controlli di ordinamento

![Le intestazioni della tabella della vista list sono controlli di ordinamento](http://www.symfony-project.org/images/book/1_4/F1414.png "Le intestazioni della tabella della vista list sono controlli di ordinamento")

Si può riutilizzare la sintassi per puntare a una lista direttamente ordinata per una
colonna:

    [php]
    <?php echo link_to('Lista dei commenti per data', '@blog_comment?sort=created_at&sort_type=desc' ) ?>

Si può anche definire un ordinamento predefinito per la vista `list` direttamente nel
file `generator.yml`. La sintassi segue l'esempio nel listato 14-24.

Listato 14-24 - Impostare un ordinamento predefinito su un campo nella vista `list`

    [yml]
    config:
      list:
        sort:   created_at
        # Sintassi alternativa per specificare un verso di ordinamento
        sort:   [created_at, desc]

>**NOTE**
>Solo i campi corrispondenti a vere colonne possono essere ordinabili, non le colonne
>personalizzate o partial.

#### Personalizzare la paginazione

L'amministrazione generata gestisce efficacemente grandi tabelle, perché la vista `list`
usa la paginazione. Quando il numero di righe in una tabella supera il numero massimo di
righe per pagina, compaiono i controlli per la paginazione in fondo alla lista. Per
esempio, la figura 14-19 mostra la lista dei commenti con sei commenti di prova nella
tabella e un limite di cinque commenti a pagina. La paginazione assicura buone
prestazioni, perché solo le righe mostrate sono estratte effettivamente dal database, e
una buona usabilità, perché anche tabelle con milioni di righe sono gestibili.

Figura 14-15 - Controlli di paginazione per liste lunghe

![Controlli di paginazione per liste lunghe](http://www.symfony-project.org/images/book/1_4/F1415.png "Controlli di paginazione per liste lunghe")

Si può personalizzare il numero di righe mostrate in ogni pagina col parametro
`max_per_page`:

    [yml]
    config:
      list:
        max_per_page:   5

#### Usare una join per accelerare le pagine

Il generatore di amministrazione usa un semplice `doSelect` per recuperare una lista di
righe. Ma se si usano oggetto correlati nella lista, il numero di query richieste per
mostrare la lista potrebbe aumentare rapidamente. Per esempio, se si vuole mostrare il
nome dell'articolo in una lista di commenti, è necessaria una query in più per ogni riga
della lista, per poter recuperare l'oggetto `BlogArticle` correlato. Ma si può forzare
il paginatore a usare un metodo `doSelectJoinXXX()` per ottimizzare il numero di query,
usando il parametro `peer_method` (`table_method` per Doctrine).

    [yml]
    config:
      list:
        peer_method: doSelectJoinBlogArticle

Il capitolo 18 spiega il concetto di join più approfonditamente.

### Personalizzazioni specifiche per le viste `new` e `edit`

In una vista `new` o `edit`, l'utente può modificare il valore di ogni colonna di un
record. Il form predefinito usato dall'admin generator è il form associato col modello:
`BlogArticleForm` per il modello `BlogArticle`. Si può personalizzare la classe da usare,
definendo una voce `class` sotto `form`, come mostrato nel listato 14-25.

Listato 14-25 - Personalizzare la classe del form per le viste `new` e `edit`

    [yml]
    config:
      form:
        class: BackendBlogArticleForm

L'uso di una classe personalizzata consente la personalizzazione di tutti i widget e di
tutti i validatori usati dall'admin generator. La classe predefinita del form può quindi
essere usata e personalizzata specificatamente per l'applicazione di frontend.

Si possono anche personalizzare label, messaggi di aiuto e l'aspetto del form,
direttamente nel file di configurazione `generator.yml`, come mostrato nel listato 14-26.

Listato 14-26 - Personalizzare l'aspetto del form

    [yml]
    config:
      form:
        display:
          NONE:     [article_id]
          Editable: [author, content, created_at]
        fields:
          content:  { label: corpo, help: "Il contenuto può essere in formato Markdown" }

#### Gestire i campi partial

I campi partial possono essere usati nelle viste `new` e `edit`, proprio come nelle viste
`list`.

### Gestire le chiavi esterne

Se lo schema definisce relazioni tra tabelle, i moduli di amministrazione generati se ne
possono avvantaggiare e offrire maggiori controlli automatici, semplificando quindi di
molto la gestione delle relazioni.

#### Relazioni uno a molti

Le relazioni 1-n sono prese in considerazione dal generatore di amministrazione. Come
illustrato precedentemente in figura 14-1, la tabella `blog_comment` è legata alla tabella
`blog_article` tramite un campo `blog_article_id`. Se si inizializza il modulo della
classe `BlogComment` col generatore di amministrazione, la vista `edit` mostrerà
automaticamente `blog_article_id` come un elenco a tendina, con i valori degli ID delle
righe della tabella `blog_article` (si veda ancora la figura 14-9 per un'illustrazione).

Lo stesso accade nella lista dei commenti legati a un articolo, nel modulo `article`
(relazione n-1).

#### Relazioni molti a molti

Symfony gestisce automaticamente anche le relazioni n-n, come mostrato in figura 14-16.

Figura 14-16 - Relazioni molti a molti

![Relazioni molti a molti](http://www.symfony-project.org/images/book/1_4/F1416.png "Relazioni molti a molti")

Personalizzando il widget usato per rendere la relazione, si può anche modificare la resa
del campo (illustrata in figura 14-17):

Figura 14-17 - Controlli disponibili per relazioni molti a molti

![Controlli disponibili per relazioni molti a molti](http://www.symfony-project.org/images/book/1_4/F1417.png "Controlli disponibili per relazioni molti a molti")

### Aggiungere interazioni

I moduli di amministrazione consentono agli utenti di eseguire le normali operazioni CRUD,
ma si possono anche aggiungere le proprie interazioni o restringere le possibili
interazioni per una vista. Per esempio, la definizione di interazione mostrata nel
listato 14-27 dà accesso a tutte le azioni CRUD predefinite nel modulo `article`.

Listato 14-27 - Definire le interazioni per ogni vista, in `backend/modules/article/config/generator.yml`

    [yml]
    config:
      list:
        title:          Lista di articoli
        object_actions:
          _edit:         ~
          _delete:       ~
        batch_actions:
          _delete:       ~
        actions:
          _new:          ~

      edit:
        title:          Corpo dell'articolo %%title%%
        actions:
          _delete:       ~
          _list:         ~
          _save:         ~
          _save_and_add: ~

In una vista `list`, ci sono tre impostazioni di azioni: le azioni disponibili per ogni
oggetto (`object_actions`), le azioni disponibili per una selezione di oggetti
(`batch_actions`) e le azioni disponibili per l'intera pagina (`actions`). La lista delle
interazioni definite nel listato 14-27 è resa come in figura 14-18. Ogni linea mostra un
bottone per modificare la riga e uno per cancellarla, più una spunta per cancellare una
selezione di righe. In fondo alla lista, un bottone consente la creazione di una nuova
riga.

Figura 14-18 - Interazioni nella vista `list`

![Interazioni nella vista list](http://www.symfony-project.org/images/book/1_4/F1418.png "Interazioni nella vista list")

Nelle viste `new` e `edit`, essendoci una sola riga alla volta, c'è un solo insieme di
azioni da definire (sotto `actions`). Le interazioni `edit` definite nel listato 14-27
rendono come in figura 14-23. Le azioni `save` e `save_and_add` salvano la riga corrente,
con la differenza che `save` mostra ancora la vista `edit` dopo il salvataggio, mentre
`save_and_add` mostra una vista `new` per aggiungere un'altra riga. L'azione
`save_and_add` è una scorciatoia molto utile per aggiungere diverse righe in successione.
Come per la posizione dell'azione `delete`, è separata dagli altri bottoni in modo da
evitare che sia cliccata per sbaglio.

I nomi delle interazioni che iniziano con un trattino basso (`_`) dicono a symfony di
usare l'icona e l'azione corrispondenti a tali interazioni. Il generatore di
amministrazione accetta `_edit`, `_delete`, `_new`, `_list`, `_save`, `_save_and_add` e
`_create`.

Figura 14-19 - Interazioni nella vista `edit`

![Interazioni nella vista edit](http://www.symfony-project.org/images/book/1_4/F1419.png "Interazioni nella vista edit")

Ma si possono anche aggiungere interazioni personalizzate, nel qual caso occorre
specificare un nome che non inizi con un trattino basso e un'azione del modulo corrente,
come nel listato 14-28.

Listato 14-28 - Definire un'interazione personalizzata

    [yml]
    list:
      title:          Lista di articoli
      object_actions:
        _edit:        -
        _delete:      -
        addcomment:   { label: Aggiungi un commento, action: addComment }

Ogni articolo nella lista ora mostrerà un collegamento `Aggiungi un commento`, come
mostrato in figura 14-20. Cliccandoci, si attiverà l'azione `addComment` del modulo
corrente. La chiave primaria dell'oggetto è aggiunta automaticamente ai parametri
della richiesta.

Figura 14-20 - Interazioni personalizzate nella vista `list`

![Interazioni personalizzate nella vista list](http://www.symfony-project.org/images/book/1_4/F1420.png "Interazioni personalizzate nella vista list")

L'azione `addComment` può essere implementata come nel listato 14-29.

Listato 14-29 - Implementare l'azione di interazione personalizzata, in `actions/actions.class.php`

    [php]
    public function executeAddComment($request)
    {
      $comment = new BlogComment();
      $comment->setArticleId($request->getParameter('id'));
      $comment->save();

      $this->redirect('blog_comment_edit', $comment);
    }

Le azioni batch ricevono un array di chiavi primarie delle righe selezionate, nel
parametro della richiesta `ids`.

Un'ultima nota sulle azioni: se si vogliono rimuovere completamente le azioni per una
categoria, usare una lista vuota, come nel listato 14-30.

Listato 14-30 - Rimuovere tutte le azioni nella vista `list`

    [yml]
    config:
      list:
        title:   Lista di articoli
        actions: {}

### Validazione di form

La validazione è presa in carico automaticamente dal form usato nelle viste `new` e `edit`.
Si può personalizzare modificando le corrispondenti classi dei form.

### Limitare le azioni usando le credenziali

Per un dato modulo di amministrazione, i campi e le interazioni disponibili possono
variare a seconda delle credenziali dell'utente autenticato (fare riferimento al capitolo
6 per una descrizione delle caratteristiche di sicurezza di symfony).

I campi nel generatore accettano un parametro `credentials`, per apparire solo agli utenti
con le credenziali giuste. Questo per la voce `list`. Inoltre, il generatore può
nascondere delle interazioni, a seconda delle credenziali. Il listato 14-31 dimostra
queste caratteristiche.

Listato 14-31 - Uso delle credenziali in `generator.yml`

    [yml]
    config:
      # La colonna id viene mostrata solo agli utenti con credenziale admin
      list:
        title:          Lista di articoli
        display:        [id, =title, content, nb_comments]
        fields:
          id:           { credentials: [admin] }

      # L'interazione addcomment è ristretta agli utenti con credenziale admin
      actions:
        addcomment: { credentials: [admin] }

Modificare la presentazione dei moduli generati
-----------------------------------------------

Si può modificare la presentazione dei moduli generati, in modo che rispondano a schemi
grafici esistenti, non solo applicando un foglio di stile personalizzato, ma anche
sovrascrivendo i template predefiniti.

### Usare un foglio di stile personalizzato

Poiché il codice HTML generato è un contenuto strutturato, si può fare quasi tutto con la
presentazione.

Si può definire un CSS alternativo da usare per un modulo di amministrazione, al posto di
quello predefinito, aggiungendo un parametro `css` alla configurazione del generatore,
come nel listato 14-32.

Listato 14-32 - Usare un foglio di stile personalizzato al posto di quello predefinito

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        model_class:           BlogArticle
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              BlogArticle
        plural:                BlogArticles
        route_prefix:          blog_article
        with_propel_route:     1
        actions_base_class:    sfActions
        css:                   mystylesheet

In alternativa, si può usare il meccanismo fornito da `view.yml` del modulo, per
sovrascrivere gli stili in base alle viste.

### Creare header e footer personalizzati

Le viste `list`, `new` e `edit` includono automaticamente un partial per header e footer.
Questi partial non sono definiti nella cartella `templates/` del modulo, ma basta
aggiungerne uno con uno dei nomi seguenti, per vederli automaticamente inclusi:

    _list_header.php
    _list_footer.php
    _form_header.php
    _form_footer.php

Per esempio, se si vuole aggiungere un header personalizzato nella vista `article/edit`,
basta creare un file chiamato `_form_header.php`, come nel listato 14-33. Non sono
necessarie ulteriori configurazioni.

Listato 14-33 - Esempio di partial header `edit`, in `modules/article/templates/_form_header.php`

    [php]
    <?php if ($blog_article->getNbComments() > 0): ?>
      <h2>Questo articolo ha <?php echo $blog_article->getNbComments() ?> commenti.</h2>
    <?php endif; ?>

Si noti che un partial in `edit` ha sempre accesso all'oggetto corrente, tramite una
variabile con lo stesso nome della classe, e che un partial in `list` ha sempre accesso
al paginatore corrente, tramite la variabile `$pager`.

### Personalizzazione del tema

Ci sono altri partial ereditati dal framework che possono essere sovrascritti nella
cartella `templates/` del modulo, per soddisfare delle esigenze personali.

I template del generatore sono spezzati in piccole parti, che possono essere
sovrascritte indipendentemente, e le azioni possono essere cambiate una per una.

Tuttavia, se si vogliono sovrascrivere tanti moduli nello stesso modo, probabilmente
conviene creare un tema riusabile. Un tema è un sottoinsieme di template e di azioni che
possono essere usati in un modulo di amministrazione, se specificato nel valore di `theme`
all'inizio di `generator.yml`. Col tema predefinito, symfony usa i file definiti in
`sfConfig::get('sf_symfony_lib_dir')/plugins/sfPropelPlugin/data/generator/sfPropelModule/admin/`.

I file del tema devono trovarsi in una struttura alberata del progetto, in una cartella
dal nome `data/generator/sfPropelModule/[nome_tema]/`. Si può creare un nuovo tema
copiando i file da quello predefinito:

    // Partial, in [nome_tema]/template/templates/
    _assets.php
    _filters.php
    _filters_field.php
    _flashes.php
    _form.php
    _form_actions.php
    _form_field.php
    _form_fieldset.php
    _form_footer.php
    _form_header.php
    _list.php
    _list_actions.php
    _list_batch_actions.php
    _list_field_boolean.php
    _list_footer.php
    _list_header.php
    _list_td_actions.php
    _list_td_batch_actions.php
    _list_td_stacked.php
    _list_td_tabular.php
    _list_th_stacked.php
    _list_th_tabular.php
    _pagination.php
    editSuccess.php
    indexSuccess.php
    newSuccess.php

    // Azioni, in [nome_tema]/parts
    actionsConfiguration.php
    batchAction.php
    configuration.php
    createAction.php
    deleteAction.php
    editAction.php
    fieldsConfiguration.php
    filterAction.php
    filtersAction.php
    filtersConfiguration.php
    indexAction.php
    newAction.php
    paginationAction.php
    paginationConfiguration.php
    processFormAction.php
    sortingAction.php
    sortingConfiguration.php
    updateAction.php

Si faccia attenzione, perché i file dei template sono in realtà template di template,
cioè sono dei file PHP che saranno analizzati da un programma speciale per generare dei
template basati sulle impostazioni del generatore (questo processo è chiamato fase di
compilazione). I template generati devono comunque contenere codice PHP da eseguire
durante la navigazione, quindi i template di template usano una sintassi alternativa per
mantenere il codice PHP ineseguito durante il primo passo. Il listato 14-34 mostra un
estratto di un template di template predefinito.

Listato 14-34 - Sintassi dei template di template

    [php]
    <h1>[?php echo <?php echo $this->getI18NString('edit.title') ?> ?]</h1>

    [?php include_partial('<?php echo $this->getModuleName() ?>/flashes') ?]

In questo listato, il codice PHP introdotto da `<?` viene eseguito immediatamente (in fase
di compilazione), mentre quello introdotto da `[?` viene eseguito solo durante l'esecuzione
vera e propria, ma il motore di template trasforma i tag `[?` in tag `<?`, in modo che il
template risultante appaia come questo:

    [php]
    <h1><?php echo __('Lista di tutti gli articoli') ?></h1>

    <?php include_partial('article/flashes') ?>

Non è facile gestire i template di template, quindi il consiglio migliore per chi volesse
creare un tema è quello di partire dal tema `admin`, modificarlo a piccoli passi e
testarlo in modo intensivo.

>**TIP**
>Si può anche impacchettare un tema per il generatore in un plugin, il che lo rende
>ancora più riusabile e facile da rilasciare in applicazioni multiple. Si faccia
>riferimento al capitolo 17 per maggiori informazioni.

-

>**SIDEBAR**
>Costruire un generatore personalizzato
>
>Il generatore di amministrazione usa un insieme di componenti interni di symfony, che
>automatizzano la creazione di azioni e template generati nella cache, l'uso di temi e
>l'analisi dei template di template.
>
>Questo vuol dire che symfony fornisce tutti gli strumenti per costruire un generatore
>personalizzato, che può assomigliare a quelli esistenti o essere completamente diverso.
>La generazione di un modulo è gestita dal metodo `generate()` della classe
>`sfGeneratorManager`. Per esempio, per generare un'amministrazione, symfony richiama
>internamente:
>
>     [php]
>     $manager = new sfGeneratorManager();
>     $data = $manager->generate('sfPropelGenerator', $parameters);
>
>Se si vuole costruire un generatore, si dovrebbe dare un'occhiata alla documentazione
>delle API delle classi `sfGeneratorManager` e `sfGenerator` e prendere come esempi le
>classi `sfModelGenerator` e `sfPropelGenerator`.

Riepilogo
---------

Per generare automaticamente le applicazioni di backend, le basi sono uno schema ben
definito e il modello degli oggetti. La personalizzazione dei moduli di amministrazione
generati andrebbe fatta tramite la configurazione.

Il file `generator.yml` è il cuore della programmazione dei backend generati. Esso consente
una personalizzazione completa di contenuti, caratteristiche e aspetto delle viste `list`,
`new` e `edit`. Si possono gestire label, testi di aiuto, filtri, ordinamento, dimensione
della pagine, tipi di input, relazioni esterne, interazioni personalizzate e credenziali
direttamente in YAML, senza una singola riga di codice PHP.

Se il generatore di amministrazione non supporta nativamente una caratteristica richiesta,
i campi partial e la possibilità di sovrascrivere le azioni forniscono una completa
estensibilità. Inoltre, si possono riusare le personalizzazioni fatte sul generatore,
grazie al meccanismo dei temi.
