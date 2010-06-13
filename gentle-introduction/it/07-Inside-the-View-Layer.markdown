Capitolo 7 - All'interno dello strato vista
===========================================

La vista è la responsabile per la visualizzazione dell'output relativo ad una particolare azione. In symfony, la vista è costituita da più parti, di cui ogni singolo elemento è stato progettato per essere facilmente modificato da chi di solito lavora con esso.

* I web designer in genere lavorano sui template (la presentazione dei dati dell'azione corrente) e sul layout (che contiene il codice comune a tutte le pagine). Questi sono scritti in HTML con l'inclusione di piccoli pezzi in PHP, che sono per lo più chiamate ad helper.
* Gli sviluppatori, avendo in mente la riutilizzabilità, di solito inseriscono i frammenti di codice in partial o component. Usano gli slot per agire su più di una zona del layout. Anche i web designer possono lavorare su questi frammenti di template.
* L'attenzione degli sviluppatori va anche sul file di configurazione view in YAML (impostando le proprietà della risposta e degli altri elementi dell'interfaccia) e sull'oggetto response. Quando si ha a che fare con variabili nei template, i rischi di cross-site scripting non devono essere ignorati e una buona comprensione delle tecniche di escapizzazione dell'output è richiesta per trattare in modo sicuro i dati dell'utente.

Ma qualunque sia il proprio ruolo è, si troveranno utili strumenti per accelerare il noioso lavoro della visualizzazione dei risultati dell'azione. Questo capitolo comprende tutti questi strumenti.


I template
----------

Il Listato 7-1 mostra un classico template di symfony. Contiene del codice HTML e un po' di codice PHP semplice, generalmente chiamate a variabili definite nell'azione (attraverso `$this->nome = 'foo';`) ed helper.

Listato 7-1 - Un template di esempio indexSuccess.php

    [php]
    <h1>Benvenuto</h1>
    <p>Bentornato, <?php echo $nome ?>!</p>
    <h2>Cosa ti piacerebbe fare?</h2>
    <ul>
      <li><?php echo link_to('Leggere gli ultimi articoli', 'articolo/read') ?></li>
      <li><?php echo link_to('Iniziare a scriverne uno nuovo', 'articolo/write') ?></li>
    </ul>

Come spiegato nel capitolo 4, nei template è preferibile la sintassi alternativa di PHP, in modo da renderli più leggibili anche per chi non è uno sviluppatore di PHP. Nei template si consiglia di utilizzare il minimo indispensabile di codice PHP, essendo che questi file sono quelli usati per progettare la grafica dell'applicazione e spesso sono creati e mantenuti da un altro gruppo, specializzato nella presentazione, ma non nella logica dell'applicazione. Mantenere la logica all'interno dell'azione rende anche più facile avere template diversi per una singola azione, senza alcuna duplicazione del codice.

### Gli helper

Gli helper sono funzioni PHP che restituiscono codice HTML e possono essere utilizzate nei template. Nel Listato 7-1, la funzione `link_to()` è un helper. A volte gli helper servono solo per risparmiare tempo, impacchettando frammenti di codice utilizzati frequentemente nei template. Per esempio, si può facilmente immaginare la definizione della funzione di questo helper:

    [php]
    <?php echo image_tag('photo.jpg') ?>
     => <img src="/images/photo.jpg" />

Dovrebbe essere simile a quella del Listato 7-2.

Listato 7-2 - Esempio di definizione di un helper

    [php]
    function image_tag($source)
    {
      return '<img src="/images/'.$source.'" />';
    }

In realtà la funzione `image_tag()` presente in symfony è un po' più complicata d iqeusta, dal momento che accetta un secondo parametro per aggiungere altri attributi al tag `<img>`. Si può vedere la sintassi completa e le opzioni nella [Documentazione con le API](http://www.symfony-project.org/api/1_4/) presente online.

Molte volte, gli helper sono intelligenti e fanno risparmiare codice:

    [php]
    <?php echo auto_link_text('Visitate il nostro sito web www.example.com') ?>
     => Visitate il nostro sito web <a href="http://www.example.com">www.example.com</a>

Gli helper facilitano il processo di scrittura dei template e generano il miglior codice HTML possibile in quanto a prestazioni e accessibilità. Si può sempre utilizzare il semplice HTML, ma gli helper in genere sono più veloci da scrivere.

>**TIP**
>Ci si potrebbe chiedere perché gli helper sono nominati utilizzando la convenzione dell'underscore piuttosto che quella camelCase, utilizzata ovunque in symfony. Il motivo è che gli helper sono funzioni e tutte le funzioni PHP del core usano la convenzione dell'underscore.

#### Dichiarare gli helper

I file di symfony contenenti le dichiarazioni degli helper non sono caricati in automatico (questo perché contengono funzioni, non classi). Gli helper sono raggruppati per scopo. Ad esempio, tutte le funzioni degli helper inerenti i testi sono definite in un file chiamato `TextHelper.php` che contiene il gruppo di helper chiamato `Text`. Se si ha bisogno di usare un helper in un template, bisogna prima caricare il relativo gruppo di helper tramite la funzione `use_helper()`. Il Listato 7-3 mostra un template usando l'helper `auto_link_text()` che fa parte del gruppo di helper `Text`.

Listato 7-3 - Dichiarare l'utilizzo di un helper

    [php]
    // Usare uno specifico gruppo di helper in questo template
    <?php use_helper('Text') ?>
    ...
    <h1>Descrizione</h1>
    <p><?php echo auto_link_text($description) ?></p>

>**TIP**
>Quando si ha necessità di dichiarare più di un gruppo di helper, aggiungere più argomenti alla chiamata `use_helper()`. Ad esempio, per caricare entrambi i gruppi di helper `Text` e `Javascript` in un template, chiamare `<?php use_helper('Text', 'Javascript') ?>`.

Alcuni helper sono disponibili per impostazione predefinita su ogni template, senza la necessità di dichiararli. Sono quelli che appartengono ai seguenti gruppi di helper:

  * `Helper`: Richiesto dall'helper di inclusione (infatti la funzione `use_helper()` è a sua volta un helper)
  * `Tag`: Helper di base per i tag, usato in quasi tutti gli helper
  * `Url`: Helper per i link e la gestione delle URL
  * `Asset`: Helper per popolare la sezione HTML `<head>` e fornire facilmente link a risorse esterne (file di immagini, JavaScript e fogli di stile)
  * `Partial`: Helper che permettono l'inclusione di frammenti di template
  * `Cache`: Manipolazione di frammenti di codice presenti nella cache

L'elenco degli helper standard, caricato in modalità predefinita per ogni template, è configurabile nel file `settings.yml`. Quindi, ad esempio se non si dovessero utilizzare gli helper del gruppo `Cache`, o se si dovessero sempre utilizzare quelli del gruppo `Text`, si può modificare di conseguenza l'impostazione `standard_helpers`. Ciò permetterà di accelerare leggermente l'applicazione. Non è possibile rimuovere i primi quattro gruppi di helper della lista precedente (`Helper`, `Tag`, `Url` e `Asset`), perché essi sono obbligatori affichè il motore di template funzioni correttamente. Di conseguenza, essi non compaiono neppure nella lista degli helper standard. 

>**TIP**
>Se si dovesse utilizzare un helper fuori dal template, è possibile caricare un gruppo di helper da qualsiasi parte chiamando `sfProjectConfiguration::getActive()->loadHelpers($helpers)`, dove `$helpers` è il nome del gruppo di helper o un di nomi di gruppi di helper. Ad esempio, se si vuole usare `auto_link_text()` in una azione, bisogna prima chiamare `sfProjectConfiguration::getActive()->loadHelpers('Text')`.

#### Helper utilizzati frequentemente

Nei capitoli successivi verranno mostrati in dettaglio alcuni helper, in relazione con le caratteristiche prese in esame. Il Listato 7-4 dà un breve elenco degli helper predefiniti che vengono utilizzati di frequente, assieme al codice HTML che ritornano.

Listato 7-4 - Gli helper predefiniti più comuni

    [php]
    // Gruppo Helper
    <?php use_helper('NomeHelper') ?>
    <?php use_helper('NomeHelper1', 'NomeHelper2', 'NomeHelper3') ?>

    // Gruppo Url
    <?php echo link_to('clicca', 'miomodulo/miaazione') ?>
    => <a href="/rotta/all/miaazione">clicca</a>  // Dipende dalla configurazione delle rotte

    // Gruppo Asset
    <?php echo image_tag('miaimmagine', 'alt=foo size=200x100') ?>
     => <img src="/images/miaimmagine.png" alt="foo" width="200" height="100"/>
    <?php echo javascript_include_tag('mioscript') ?>
     => <script language="JavaScript" type="text/javascript" src="/js/mioscript.js"></script>
    <?php echo stylesheet_tag('style') ?>
     => <link href="/stylesheets/style.css" media="screen" rel="stylesheet"type="text/css" />

Ci sono molti altri helper in symfony, ci vorrebbe un libro intero per descriverli tutti. Il migliore riferimento per gli helper è la [documentazione delle API](http:// www.symfony-project.org/api/1_4/) online, dove sono ben documentati tutti gli helper, con la loro sintassi, le opzioni e gli esempi.

#### Aggiungere i proprio helper

Symfony ha molti helper per vari diversi utilizzi, ma se non si trova quello di cui si ha bisogno nella documentazione delle API documentation, probabilmente si vorrà crearne uno nuovo. Questo è un compito molto semplice.

Le funzioni per gli helper (normali funzioni PHP che restituiscono codice HTML) devono essere salvate in un file chiamato `FooBarHelper.php`, dove `FooBar` è il nome del gruppo di helper. Salvare il file nella cartella `apps/frontend/lib/helper/` (o in qualunque cartella `helper/`  creata dentro una delle cartelle `lib/` del progetto) in modo che possa essere trovata automaticamente dall'helper per l'inclusione `use_helper('FooBar')`.

>**TIP**
>Questo sistema permette anche di sovrascrivere gli helper esistenti di symfony. Ad esempio, per ridefinire tutti gli helper del gruppo `Text`, basta creare un file `TextHelper.php` nella cartella `apps/frontend/lib/helper/`. Ogni volta che verrà chiamato `use_helper('Text')`, symfony userà il vostro gruppo di helperal posto del suo. Ma attenzione: essendo che il file originale non viene caricato, è necessario ridefinire tutte le funzioni di un gruppo di helper per sovrascriverlo; in caso contrario, alcuni degli helper originali non verranno resi disponibili.

### Layout della pagina

Il template mostrato nel Listato 7-1 non è un documento XHTML valido. Mancano la definizione del `DOCTYPE` ed i tag `<html>` e `<body>`. Questo perché vengono memorizzati in un'altra parte dell'applicazione, in un file chiamato `layout.php`, che contiene il layout della pagina. Questo file, chiamato anche template globale, memorizza il codice HTML che è comune a tutte le pagine dell'applicazione per evitare di ripeterlo per ciascun template. Il contenuto del template è integrato nel layout, o, cambiando il punto di vista, il layout "decora" il template. Questa è una applicazione del decorator design pattern, mostrata in Figura 7-1.

>**TIP**
>Per maggiori informazioni sul decorator e gli altri design pattern, vedere *Patterns of Enterprise Application Architecture* di Martin Fowler (Addison-Wesley, ISBN: 0-32112-742-0).

Figura 7-1 - Decorare un template con un layout

![Decorare un template con un layout](http://www.symfony-project.org/images/book/1_4/F0701.png "Decorare un template con un layout")

Listato 7-5 mostra la pagina predefinita del layout, presente nella cartella dell'applicazione `templates/`.

Listatog 7-5 - Layout predefinito, in `myproject/apps/frontend/templates/layout.php`

    [php]
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
      <head>
        <?php include_javascripts() ?>
        <?php include_stylesheets() ?>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
      </head>
      <body>
        <?php echo $sf_content ?>
      </body>
    </html>

Gli helper chiamati nella sezione `<head>` recuperano le informazioni dall'oggetto response e dalla configurazione view. Il tag `<body>` mostra il risultato del template. Con questo layout, la configurazione predefinita e il template di esempionel Listato 7-1 la view elaborata è simile a quanto si vede nel Listato 7-6.

Listato 7-6 - Il Layout, la configurazione View e il template assemblato.

    [php]
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
      <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="title" content="symfony project" />
        <meta name="robots" content="index, follow" />
        <meta name="description" content="symfony project" />
        <meta name="keywords" content="symfony, project" />
        <title>symfony project</title>
        <link rel="stylesheet" type="text/css" href="/css/main.css" />
        <link rel="shortcut icon" href="/favicon.ico">
      </head>
      <body>
        <h1>Benvenuto</h1>
        <p>Bentornato, <?php echo $nome ?>!</p>
        <h2>Cosa vuoi fare?</h2>
        <ul>
          <li><?php echo link_to('Leggere gli ultimi articoli', 'articolo/read') ?></li>
          <li><?php echo link_to('Scriverne uno nuovo', 'articolo/write') ?></li>
        </ul>
      </body>
    </html>

Il template globale può essere completamente personalizzato per ciascuna applicazione. Aggiungerlo in ogni codice HTML in cui se ne ha bisogno. Questo layout spesso è usato per mantenere la navigazione del sito, i logo e altro. Si può anche avere più di un layout e decidere quale layout dovrebbe essere usato per ciascuna azione. Per ora non preoccuparsi delle inclusioni di JavaScript e fogli di stile; La sezione "Configurazione della View" mostrerà più avanti come ottenere ciò.

### Scorciatoie nei template

Nei template, alcune variabili symfony sono sempre disponibili. Queste scorciatoie forniscono l'accesso alla maggior parte delle informazioni necessarie nei template, attraverso oggetti del core di symfony:

  * `$sf_context`: L'intero oggetto context (`istanza di sfContext`)
  * `$sf_request`: L'oggetto request(`istanza di sfRequest`)
  * `$sf_params` : I parametri dell'oggetto request
  * `$sf_user`   : L'oggetto corrente di sessione utente (`istanza di sfUser`)

Il capitolo precedente ha mostrato utili metodi per gli oggetti `sfRequest` e `sfUser`. Si possono chiamare questi metodi nei template attraverso le variabili `$sf_request` e `$sf_user`. Ad esempio, se la request include un parametro `total`, il suo valore è disponibile nel template tramite:

    [php]
    // Versione lunga
    <?php echo $sf_request->getParameter('total') ?>

    // Versione breve
    <?php echo $sf_params->get('total') ?>

    // Equivalente al seguente codice dell'azione
    echo $request->getParameter('total')

Frammenti di codice
-------------------

Spesso c'è la necessità di includere del codice HTML o PHP su più pagine. Per evitare la ripetizione del codice, la maggior parte delle volte è sufficiente il comando `include()`.

Per esempio, se molti template dell'applicazione hanno bisogno di utilizzare lo stesso frammento di codice, salvarlo in un file chiamato `mioFrammento.php` nella cartella globale dei template (`mioprogetto/apps/frontend/templates/`) e includerlo nei template come segue:

    [php]
    <?php include(sfConfig::get('sf_app_template_dir').'/mioFrammento.php') ?>

Ma questo non è un modo molto pulito per gestire un frammento, soprattutto perché possono esserci nomi di variabili differenti tra il frammento e i vari template che lo includono. Inoltre, il sistema per la cache di symfony (descritto nel Capitolo 12) non ha modo di rilevare un include, quindi il frammento non può essere messo in cache indipendentemente dal template. Symfony fornisce tre tipi alternativi di frammenti di codice per sostituire gli `include`:

  * Se la logica è leggera, basterà includere un file template che ha accesso ad alcuni dati che gli vengono passati. Per questo si userà un partial.
  * Se la logica è più pesante (ad esempio, se è necessario accedere al modello dei dati e/o modificare il contenuto in base alla sessione), è preferibile separare la presentazione dalla logica. Per questo, si userà un component.
  * Se il frammento ha lo scopo di sostituire una parte specifica del layout, per il quale può già esistere un contenuto predefinito, si userà uno slot.
	
L'inserimento di questi frammenti si ottiene con gli helper del gruppo `Partial`. Questi helper sono disponibili da ogni template symfony, senza aver bisogno della dichiarazione iniziale.

### Partial

Un partial è un pezzo di codice del template riutilizzabile. Ad esempio, in una applicazione per pubblicare articoli, il codice del template che mostra un articolo è usato nella pagina con l'articolo completo, nell'elenco dei migliori articoli e nell'elenco degli ultimi articoli. Questo codice è un candidato perfetto per un partial, come mostrato nella Figura 7-2.

Figura 7-2 - Il riutilizzo dei partial nei template

![Il riutilizzo dei partial nei template](http://www.symfony-project.org/images/book/1_4/F0702.png "Il riutilizzo dei partial nei template")

Proprio come i template, i partial sono file che si trovano nella cartella `templates/` e contengono codice HTML con del codice PHP. Il nome file di un partial inizia sempre con un carattere di sottolineatura (`_`) e questo aiuta a distinguere i partial dai template, poiché sono situati nelle stesse cartelle `templates/`.

Un template può includere partial se è nello stesso modulo , in un altro modulo, o nella cartella `templates/` globale. Includere un partial usando un helper `include_partial()` e specificare il nome del modulo e del partial come parametro (omettendo la sottolineatura e il `.php` finale), come descritto nel Listato 7-7.

Listing 7-7 - Includere un partial in un template del modulo `miomodulo`

    [php]
    // Include il partial frontend/modules/miomodulo/templates/_miopartial1.php 
    // Essendo che il template e il partial sono nello stesso modulo,
    // si può omettere il nome del modulo
    <?php include_partial('miopartial1') ?>

    // Include il partial frontend/modules/foobar/templates/_mypartial2.php
    // In questo caso il nome del modulo è obbligatorio
    <?php include_partial('foobar/miopartial2') ?>

    // Include il partial frontend/templates/_mypartial3.php
    // E' considerato parte del modulo 'global'
    <?php include_partial('global/miopartial3') ?>

I partial hanno accesso ai normali helper di symfony e alle scorciatoie dei template. Ma poiché  i partial possono essere chiamati da qualsiasi punto dell'applicazione, non hanno accesso automatico alle variabili definite nelle azioni che chiamano i template stessi, a meno che non siano esplicitamente passate come argomento. Ad esempio, se si vuole che un partial abbia accesso ad una variabile `$totale`, l'azione deve passarla ai template e poi il template  all'helper come secondo argomento della chiamata `include_partial()`, come mostrato nei Listati 7-8, 7-9 e 7-10.

Listato 7-8 - L'azione definisce una variabile, in `miomodulo/actions/actions.class.php`

    [php]
    class mymoduleActions extends sfActions
    {
      public function executeIndex()
      {
        $this->totale = 100;
      }
    }

Listato 7-9 - Il template passa la variabile al partial, in `miomodulo/templates/indexSuccess.php`

    [php]
    <p>Buongiorno, mondo!</p>
    <?php include_partial('miopartial', array('miototale' => $totale)) ?>

Listato 7-10 - Il partial ora può usare la variabile, in `miomodulo/templates/_miopartial.php`

    [php]
    <p>Totale: <?php echo $miototale ?></p>

>**TIP**
>Tutti gli helper fin'ora sono stati chiamati da `<?php echo nomeFunzione() ?>`. L'helper partial, però, è chiamato semplicemente da `<?php include_partial() ?>`, senza `echo`, in modo che abbia un comportamento simile al normale comando PHP `include()`. Se si ha bisogno di una funzione che restituisca il contenuto di un partial senza visualizzarlo, utilizzare `get_partial()`. Tutti gli helper `include_` descritti in questo capitolo hanno una controparte `get_` che può essere chiamata insieme al comando `echo`.

>**TIP**
>Invece di visualizzare un template, una azione può restituire un partial o un component. I metodi `renderPartial()` e `renderComponent()` della classe dell'azione promuovono la riusabilità del codice. Inoltre sfruttano la possibilità dei partial di essere messi in cache (vedere il Capitolo 12). Le variabili definite nell'azioneverranno passate automaticamente al partial/component, a meno che non si definisca un array associativo di varibili come secondo paramentro del metodo.
>
>     [php]
>     public function executeFoo()
>     {
>       // do things
>       $this->foo = 1234;
>       $this->bar = 4567;
>
>       return $this->renderPartial('miomodulo/miopartial');
>     }
>
>In questo esempio, il partial avrà accesso a `$foo` e `$bar`. Se l'azione termina con le seguenti righe:
>
>       return $this->renderPartial('miomodulo/miopartial', array('foo' => $this->foo));
>
>allora il partial ha solo accesso a `$foo`.

### Component

Nel Capitolo 2, il primo script di esempio è stato spezzato in due parti per separare la logica dalla presentazione. Proprio come il pattern MVC si applica alle azioni e ai template, può essere necessario dividere un partial in una parte di logica e in una parte di presentazione. In tal caso, è necessario utilizzare un componente. 

Un componente è come una azione, salvo il fatto che è molto più veloce. La logica di un componente è all'interno di una classe che eredita da `sfComponents`, situata in un file `actions/components.class.php`.  La sua presentazione è è messa in un partial. I metodi di una classe `sfComponents` iniziano con la parola `execute`, proprio come le azioni e possono passare variabili ai loro controparti della presentazione nello stesso modo con cui le azioni passano variabili. I partial che vengono utilizzati come presentazione per componenti sono nominati con lo stesso nome del componente (senza l'`execute`, ma con una sottolinatura iniziale). La Tabella 7-1 compara le convenzioni per i nomi per azioni e componenti.

Table 7-1 - Convenzioni per i nomi di azioni e componenti

Convenzione                          |  Azioni                |  Componenti
------------------------------------ | ---------------------- | ----------------------
File con la logica                   | `actions.class.php`    | `components.class.php`
La classe con la logica estende      | `sfActions`            | `sfComponents`
Nomi per i metodi                    | `executeMiaAzione()`   | `executeMioComponente()`
Nomi dei file con la presentazione   | `miaAzioneSuccess.php` | `_mioComponente.php`

>**TIP**
>Così come è possibile separare i file delle azioni, la classe `sfComponents` ha una controparte `sfComponent` che permette ai singoli file dei componenti lo stesso tipo di sintassi. 

Per esempio, supponiamo di avere una barra laterale che mostra le ultime notizie di un dato soggetto, a seconda del profilo dell'utente, che viene riutilizzato in diverse pagine. Le query necessarie a ottenere le notizie sono troppo complesse per apparire in una semplice partial, quindi hanno bisogno di essere spostate in un qualcosa simile ad una azione, un componente. La figura 7-3 mostra questo esempio

Per questo esempio, mostrato nei Listati 7-11 e 7-12, il componente verrà tenuto nel proprio modulo (chiamato `novita`), ma si possono mischiare componenti e azioni in un singolo modulo se questo ha senso da un punto di vista funzionale.

Figura 7-3 - Usare i componenti nei template

![Usare i componenti nei template](http://www.symfony-project.org/images/book/1_4/F0703.png "Usare i componenti nei template")

Listato 7-11 - La classe Components, in `modules/novita/actions/components.class.php`

    [php]
    <?php

    class novitaComponents extends sfComponents
    {
      public function executeHeadlines()
      {
        // Propel
        $c = new Criteria();
        $c->addDescendingOrderByColumn(NewsPeer::PUBLISHED_AT);
        $c->setLimit(5);
        $this->novita = NewsPeer::doSelect($c);

        // Doctrine
        $query = Doctrine::getTable('Novita')
                  ->createQuery()
                  ->orderBy('pubblicato_il DESC')
                  ->limit(5);

        $this->novita = $query->execute();
      }
    }

Listato 7-12 - Il partial, in `modules/novita/templates/_headlines.php`

    [php]
    <div>
      <h1>Latest news</h1>
      <ul>
      <?php foreach($news as $headline): ?>
        <li>
          <?php echo $headline->getPublishedAt() ?>
          <?php echo link_to($headline->getTitle(),'news/show?id='.$headline->getId()) ?>
        </li>
      <?php endforeach ?>
      </ul>
    </div>

Ora ogni volta che si avrà bisogno del componente in un template, basterà chiamare:

    [php]
    <?php include_component('novita', 'headlines') ?>

Come i partial, i componenti accettano parametri aggiuntivi nella forma di un array associativo. I parametri sono disponibili al partial con il loro nome e nel componente attraverso l'oggetto `$this`. Per un esempio, vedere il Listato 7-13.

Listato 7-13 - Passare i parametri a un componente e ai suoi template

    [php]
    // Chiamare il componente
    <?php include_component('novita', 'headlines', array('foo' => 'bar')) ?>

    // Nel componente stesso
    echo $this->foo;
     => 'bar'

    // Nel partial _headlines.php
    echo $foo;
     => 'bar'

Si possono includere componenti in componenti, o nel layout globale, così come in ogni normale template. Come nelle azioni, i metodi `execute` dei componenti possono passare variabili ai relativi partial e avere accesso alle stesse scorciatoie. Ma le similitudini si fermano qua. Un componente non gestisce la sicurezza o la validazione, non può essere chiamato da Internet (solo dall'applicazione stessa) e non ha le varie possibilità di return. Questo è il motivo per cui un componente nell'esecuzione è più veloce di un'azione. 

### Gli slot

I partial e i componenti sono ottimi per la riusabilità. Ma in molti casi i frammenti di codice devono andare a comporre un layout con più di una zona dinamica. Ad esempio, supponiamo che si desideri aggiungere alcuni tag personalizzati nella sezione `<head>` del layout, in base al contenuto di una azione. Oppure supponiamo che il layout ha una zona dinamica principale, che è composta dal risultato di una azione, più a numerose altre azioni più piccole, che hanno un contenuto predefinito presente nel layout che però può essere sovrascritto a livello del template.

Per queste situazioni, la soluzione è uno slot. In sostanza, uno slot è un segnaposto che si può mettere in qualsiasi degli elementi della vista (layout, template o partial). Riempire questo segnaposto è come l'impostazione di una variabile. Il codice di riempimento è memorizzato nella risposta a livello globale, in modo da poterlo definire ovunque (nel layout, template o partial). Basta fare in modo di definire uno slot prima di includerlo e ricordate che il layout viene eseguito dopo il template (questo è il processo di decorazione) e che i partial vengono eseguiti quando sono chiamati in un template. Il tutto sembra un po' troppo astratto? Vediamo un esempio.

Immaginiamo un layout con una zona per il template e due slot: uno per la barra laterale e l'altro per il piè di pagina. I valori per lo slot sono definiti nel template. Durante il processo di decorazione, il codice del layout avvolge il codice del template e gli slot vengono riempiti con i valori definiti precedentemente, così come è illustrato nella figura 7-4. La barra laterale e il piè di pagina possono essere contestuali all'azione principale. È come avere un layout con più di un "buco".

Figura 7-4 - Slot layout definiti in un template

![Slot layout definiti in un template](http://www.symfony-project.org/images/book/1_4/F0704.png "Slot layout definiti in un template")

Vedere un po' di codice chiarirà ulteriormente le cose. Per includere uno slot, usare l'helper `include_slot()`. L'helper `has_slot()` restituisce `true` se lo slot è stato definito in precedenza, fornendo come bonus un meccanismo di fallback. Ad esempio, definire un segnaposto per uno slot `'sidebar'` nel layout e i suoi contenuti predefiniti come mostrato nel Listato 7-14.

Listato 7-14 - Inclusione di uno slot `'sidebar'` nel layout

    [php]
    <div id="sidebar">
    <?php if (has_slot('sidebar')): ?>
      <?php include_slot('sidebar') ?>
    <?php else: ?>
      <!-- default sidebar code -->
      <h1>Contextual zone</h1>
      <p>Questa zona contiene link e informazioni
      relative al contenuto principale della pagina.</p>
    <?php endif; ?>
    </div>

Capita abbastanza frequentemente di dover mostrare dei contenuti predefiniti se uno slot non è definito e per questo scopo l'helper `include_slot` restituisce un valore booleano che indica se lo slot è stato definito. Il Listato 7-15 mostra come utilizzare questo valore in modo da semplificare il codice.

Listato 7-15 - Inclusione di uno slot `'sidebar'` nel layout

    [php]
    <div id="sidebar">
    <?php if (!include_slot('sidebar')): ?>
      <!-- default sidebar code -->
      <h1>Contextual zone</h1>
      <p>Questa zona contiene link e informazioni
      relative al contenuto principale della pagina.</p>
    <?php endif; ?>
    </div>

Ciascun template ha la possibilità di definire i contenuti di uno slot (in realtà anche i partial sono in grado di farlo). Essendo che gli slot sono destinati a contenere codice HTML, symfony offre un modo conveniente per definirli: basta scrivere il codice dello slot tra l'helper `slot()` e `end_slot()`, come mostrato nel Listato 7-16.

Listato 7-16 - Sovrascrivere il contenuto dello slot `'sidebar'` in un template

    [php]
    // ...

    <?php slot('sidebar') ?>
      <!-- codice per la barra laterale personalizzata per il template corrente-->
      <h1>Dettagli dell'utente</h1>
      <p>nome:  <?php echo $user->getNome() ?></p>
      <p>email: <?php echo $user->getEmail() ?></p>
    <?php end_slot() ?>

Il codice tra gli helper slot è eseguito nel contesto del template, quindi ha accesso a tutte le variabili che sono state definite nell'azione. Symfony metterà automaticamente il risultato dell'esecuzione del codice nell'oggetto response. Non verrà visualizzato nel template, ma reso disponibile per future chiamate `include_slot()` come quella mostrata nel Listato 7-14.

Gli slot sono molto utili per definire zone che devono mostrare dei contenuti contestuali. Possono anche essere usati per aggiungere codice HTML al layout solo per certe azioni. Ad esempio, un template che mostra l'elenco delle ultime news potrebbe volere aggiungere un link a un feed RSS nella zona `<head>` del layout. Questo si può ottenere semplicemente aggiungendo uno slot 'feed'` nel layout e sovrascrivendolo nel template dell'elenco.

Se il contenuto dello slot è molto corto, per esempio come nel caso di uno slot `titolo`, si può semplicemente passare il contenuto come secondo argomento del metodo `slot()` come mostrato nel Listato 7-17.

Listato 7-17 - Usare lo `slot()` per definire un contenuto corto

    [php]
    <?php slot('titolo', 'Il contenuto del titolo') ?>

>**SIDEBAR**
>Dove cercare i frammenti dei template
>
>Le persone che lavorano sui template in genere sono dei web designer che possono non conoscere symfony molto bene e possono avere difficoltà a trovare i frammenti dei template, dal momento che possono essere sparsi in tutta l'applicazione. Queste brevi linee guida, renderanno più comodo il dover lavorare con il sistema dei template di symfony.
>
>Prima di tutto, anche se un progetto symfony contiene molte cartelle, tutti i file dei layout, dei template e dei frammenti di template risiedono in cartelle chiamate `templates/`. Quindi per quello che può interessare ad un web designer, la struttura di un progetto può essere ridotta a qualcosa di questo tipo:
>
>
>     mioprogetto/
>       apps/
>         applicazione1/
>           templates/       # Layout per l'applicazione 1
>           modules/
>             modulo1/
>               templates/   # Template e partial per il modulo 1
>             modulo2/
>               templates/   # Template e partial per il modulo 2
>             modulo3/
>               templates/   # Template e partial per il modulo 3
>
>
>Tutte le altre cartelle possono essere ignorate.
>
>Quando si trova un `include_partial()`, i web designer devono sapere che solo il primo argomento è importante. Il pattern di questo argomento è `nome_modulo/nome_partial` e ciò significa che il codice di presentazione si trova in `modules/nome_modulo/templates/_nome_partial.php`.
>
>Per l'helper `include_component()`, il nome del modulo e il nome del partial sono i primi due argomenti. Per il resto, un'idea generale su cosa sono gli helper e su quali helper sono più utili nei template dovrebbe essere sufficiente per iniziare a progettare template per le applicazioni symfony.

Configurazione della vista
--------------------------

In symfony, una vista consiste di due parti distinte:

  * La presentazione HTML del risultato dell'azione (memorizzata nel template, nel layout e nei frammenti di template)
  * Tutto il resto, comprese le seguenti cose:

    * Meta dichiarazioni: keywords, description, o durata della cache.
    * Tag title della pagina: non solo aiuta gli utenti con numerose finestre aperte del browser a trovare la vostra, ma è anche molto importante per l'indicizzazione nei motori di ricerca.
    * Inclusione di file: JavaScript e fogli di stile.
    * Layout: alcune azioni richiedono un layout personalizzato (pop-up, ads, ecc.) oppure anche nessun layout (ad esempio le azioni Ajax).

Nella vista, tutto quello che non è HTML è chiamato configurazione della vista e symfony fornisce due modi per gestirla. Il modo principale è attraverso il file di configurazione `view.yml`. Può essere utilizzato ogni volta che i valori non dipendono dal contesto o per le query del database. Quando è necessario impostare valori dinamici, il metodo alternativo è quello di impostare la configurazione della vista attraverso gl iattributi dell'oggetto `sfResponse` direttamente nell'azione.

>**NOTE**
>Se si imposta un certo parametro della configurazione della vista sia attraverso l'oggetto `sfResponse` che attraverso il file `view.yml`, la definizione di `sfResponse` ha la precedenza.

### Il file `view.yml`

Ogni modulo può avere un file `view.yml` per definire la configurazione delle sue viste. Questo permette di definire le impostazioni di visualizzazione per un intero modulo e vista per vista in un singolo file. Le chiavi di primo livello del file `view.yml` sono i nomi dei moduli della vista. Il Listato 7-18 mostra un esempio della configurazione della vista

Listato 7-18 - Esempio Livello-Modulo `view.yml`

    editSuccess:
      metas:
        title: Modifica il tuo profilo

    editError:
      metas:
        title: Errore nella modifica del profilo

    all:
      stylesheets: [mio_style]
      metas:
        title: Il mio sito web

>**CAUTION**
>Bisogna tenere presente che le chiavi principali del file `view.yml` sono nomi di viste, non nomi di azioni. Inoltre bisogna ricordarsi che il nome di una vista è composta dal nome di una azione e dalla terminazione dell'azione. Ad esempio, se l'azione `edit` restituisce `sfView::SUCCESS` (o non restituisce nulla, dal momento che è la terminazione predefinita in una azione), allora il nome della vista è `editSuccess`.

Le impostazioni predefinite per il modulo sono definite sotto la chiave `all:` nel modulo `view.yml`. Le impostazioni predefinite per tutte le viste dell'applicazione sono definite in `view.yml`. Anche qua vale il principio della configurazione a cascata:

  * In `apps/frontend/modules/miomodulo/config/view.yml`, le definizioni per-view si applicano solo a una vista e sovrascrivono le definizioni a livello di modulo.
  * In `apps/frontend/modules/miomodulo/config/view.yml`, le definizioni `all:` si applicano a tutte le azioni del modulo e sovrascrivono le definizioni a livello di applicazione.
  * In `apps/frontend/config/view.yml`, le definizioni `default:` si applicano a tutti i moduli e a tutte le azioni dell'applicazione.

>**TIP**
>I file `view.yml` a livello di modulo non esistono nella modalità predefinita. La prima volta che si ha necessità di modificare un parametro della configurazione della vista per un modulo, bisogna creare un file `view.yml` vuoto nella cartella `config/` del modulo stesso.

Dopo aver visto il template predefinito nel Listato 7-5 e un esempio di una response finale nel Listato 7-6, c isi potrebbe chiedere da dove provengono i valori dell'header. La risposta è che sono le impostazioni predefinite per la vista, definite nel `view.yml` dell'applicazione e mostrate nel Listato 7-19.

Listato 7-19 - Configurazione predefinita della vista a livello di applicazione, in `apps/frontend/config/view.yml`

    default:
      http_metas:
        content-type: text/html

      metas:
        #title:        symfony project
        #description:  symfony project
        #keywords:     symfony, project
        #language:     en
        #robots:       index, follow

      stylesheets:    [main]

      javascripts:    []

      has_layout:     true
      layout:         layout

Ciascuna di queste impostazioni verrà descritta in dettaglio nella sezione "Impostazione della configurazione della vista".

### L'oggetto Response

Sebbene faccia parte dello strato vista, l'oggetto response viene spesso modificato dall'azione. Le azioni possono accedere all'oggetto response di symfony, chiamato `sfResponse`, attraverso il metodo `getResponse()`. Il Listato 7-20 elenca alcuni dei metodi di `sfResponse` spesso utilizzati all'interno di un'azione.

Listato 7-20 - Le azioni hanno accesso ai metodi dell'oggetto `sfResponse`

    [php]
    class miomoduloActions extends sfActions
    {
      public function executeIndex()
      {
        $response = $this->getResponse();

        // Header HTTP
        $response->setContentType('text/xml');
        $response->setHttpHeader('Content-Language', 'en');
        $response->setStatusCode(403);
        $response->addVaryHttpHeader('Accept-Language');
        $response->addCacheControlHttpHeader('no-cache');

        // Cookie
        $response->setCookie($name, $content, $expire, $path, $domain);

        // Meta e header della pagina
        $response->addMeta('robots', 'NONE');
        $response->addMeta('keywords', 'foo bar');
        $response->setTitle('La mia pagina FooBar');
        $response->addStyleSheet('custom_style');
        $response->addJavaScript('custom_behavior');
      }
    }

Oltre ai metodi setter che sono stati mostrati, la classe `sfResponse` ha getter che ritornano il valore corrente degli attributi della response.

I setter dell'header sono molto potenti in symfony. Gli header sono inviati il più tardi possibile (in `sfRenderingFilter`), in modo che possano venire modificati come si vuole. Forniscono anche utili scorciatoie. Ad esempio, se non si specifica un charset quando si chiama `setContentType()`, symfony aggiunge automaticamente il charset predefinito, definito nel file `settings.yml`.

    [php]
    $response->setContentType('text/xml');
    echo $response->getContentType();
     => 'text/xml; charset=utf-8'

Il codice di stato delle risposte di symfony è compatibile con la specifica HTTP. Le eccezioni restituiscono uno stato 500, le pagine non trovate restituiscono 404, le pagine normali restituiscono uno satto 200, le pagine non modificate possono essere ridotte ad un semplice header con il codice di stato 304 (vedere il Capitolo 12 per maggiori dettagli) e così via. Ma è possibile sovrascrivere questi valori predefiniti impostando il proprio codice di stato nell'azione con il metodo response `setStatusCode()`.  È possibile specificare un codice personalizzato e un messaggio personalizzato, o semplicemente un codice personalizzato, nel qual caso, symfony aggiunge un messaggio generico per questo codice.

    [php]
    $response->setStatusCode(404, 'Questa pagina non esiste');

>**TIP**
>Prima di inviare gli header, symfony normalizza i loro nomi. Quindi non è il caso di preoccuparsi se si scrive `content-language` invece di `Content-Language` in una chiamata a `setHttpHeader()`, perché symfony coomprenderà il primo e l otrasformerà automaticamente nel secondo.

### Impostare la configurazione per la vista

Si sarà notato che ci sono due tipi di impostazioni per la configurazione della vista:

  * Quella che ha un unico valore (il valore è una stringa nel file `view.yml` e la response usa un metodo `set` per qeusta)
  * Quella con valori multipli (per le quali `view.yml` usa array e la response usa un metodo `add`)

Ricordarsi che la configurazione a cascata cancella l'impostazione del valore unico ma mantiene le impostazioni con valori multipli. Questo diventerà più evidente proseguendo la lettura del capitolo.

#### Configurare i meta tag

Le informazioni scritte nei tag `<meta>` nella response non sono visualizzate nel browser ma sono utili per i robot e i motori di ricerca. Gestiscono anche le impostazioni della cache di tutte le pagine. Definire questi tag sotto le chiavi `http_metas:` e `metas:`, come mostrato nel listato 7-21, o con i metodi di response `addHttpMeta()` e `addMeta()` nell'azione, come mostrato nel Listato 7-22.

Listato 7-21 - Definizione meta come chiave: coppie di valori in `view.yml`

    http_metas:
      cache-control: public

    metas:
      description:   Finanza in Italia
      keywords:      finanza, Italia

Listato 7-22 - Definizione meta come impostazioni della Response nell'azione

    [php]
    $this->getResponse()->addHttpMeta('cache-control', 'public');
    $this->getResponse()->addMeta('description', 'Finanza in Italia');
    $this->getResponse()->addMeta('keywords', 'finanza, Italia');

L'aggiunta di una chiave esistente sostituirà il contenuto corrente predefinito. Per i meta tag HTTP, si può aggiungere un terzo parametro e impostarlo a `false` per fare si che il metodo `addHttpMeta()` (oltre a `setHttpHeader()`) aggiunga il valore a quello esistente, invece che sostituirlo.

    [php]
    $this->getResponse()->addHttpMeta('accept-language', 'en');
    $this->getResponse()->addHttpMeta('accept-language', 'fr', false);
    echo $this->getResponse()->getHttpHeader('accept-language');
     => 'en, fr'

Al fine di far si che questi meta tag compaiano nella pagina finale, gli helper `include_http_metas()` e `include_metas()` devono essere chiamati nella sezione `<head>` (questo è il caso del layout predefinito; vedere il Listato 7-5). Symfony aggrega automaticamente le impostazioni di tutti i file `view.yml` (compreso quello predefinito mostrato nel Listato 7-18) e dell'attributo response per visualizzare i corretti `<meta>` tag. L'esempio del Listato 7-21 finisce come mostrato nel Listato 7-23.

Listato 7-23 - Visualizzazione dei meta tag nella pagina finale

    [php]
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="cache-control" content="public" />
    <meta name="robots" content="index, follow" />
    <meta name="description" content="Finanza in Italia" />
    <meta name="keywords" content="finanza, Italia" />

Come bonus, l'header HTTP della response è anche gestito dall'impostazione `http-metas:` anche se non si hanno helper `include_http_metas()` nel layout, o se non esiste alcun layout. Ad esempio, se bisogna inviare una pagina come testo normale, definire il seguente `view.yml`:

    http_metas:
      content-type: text/plain

    has_layout: false

#### Configurazione del title

Il titolo della pagina è una parte chiave per l'indicizzazione dei motori di ricerca. E' anche molto utile con i moderni browser che forniscono la navigazione con i tab. In HTML, il titolo è sia un tag che una informazione meta della pagina, quindi il file `view.yml` vede la chiave `title:` come un figlio della chiave `metas:`. Il Listato 7-24 mostra la definizione di title in `view.yml` e il Listato 7-25 mostra la definizione nell'azione.

Listato 7-24 - La definizione di title in `view.yml`

    indexSuccess:
      metas:
        title: Tre piccoli porcellini

Listato 7-25 - La definizione di title nell'azione (permette la creazione titoli dinamici)

    [php]
    $this->getResponse()->setTitle(sprintf('%d piccoli porcellini', $number));

Nella sezione `<head>` della pagina finale, la definizione del title imposta il tag `<meta name="title">` se l'helper `include_metas()` è presente e il tag `<title>` se l'helper `include_title()` è presente. Se sono presenti entrambi (come nel layout predefinito del Listato 7-5), il titolo compare due volte nel sorgente della pagina (vedere il Listato 7-6), ma questo non crea nessun problema.

>**TIP**
>Un altro modo per gestire la definizione del title è usare gli slot, come discusso sopra. Questo metodo permette di mantenere una migliore separazione tra i controllori e i template : il titolo appartiene alla vista, non al controllore.

#### Configurazione dell'inclusione dei file

Aggiungere un certo foglio di stile o un file JavaScript alla vista è semplice, come mostrato nel Listato 7-26.

Listato 7-26 - Inclusione dei file

    [yml]
    // In view.yml
    indexSuccess:
      stylesheets: [mystyle1, mystyle2]
      javascripts: [myscript]
      
-

    [php]
    // In una azione
    $this->getResponse()->addStylesheet('miostile1');
    $this->getResponse()->addStylesheet('miostile2');
    $this->getResponse()->addJavascript('mioscript');

    // In un template
    <?php use_stylesheet('miostile1') ?>
    <?php use_stylesheet('miostile2') ?>
    <?php use_javascript('mioscript') ?>

In ogni caso, l'argomento è un nome di un file. Se il file ha una estensione logica ((`.css` per un foglio di stile e `.js` per un file JavaScript), si può ometterla. Se il file ha una collocazione logica (`/css/` per un foglio di stile e `/js/` per un file JavaScript), si può omettere anche quella. Symfony è abbastanza intelligente da aggiungere la corretta estensione o locazione.

Come le difinizioni di meta e title, le definizioni per includere i file richiedono l'utilizzo degli helper `include_javascripts()` e `include_stylesheets()` nel template o nel layout dove devono essere inclusi. Quest osignifica che le precedenti impostazioni visualizzeranno il codice HTML del Listato 7-27.

Listato 7-27 - Risultato dell'inclusione dei file

    [php]
    <head>
    ...
    <link rel="stylesheet" type="text/css" media="screen" href="/css/miostile1.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/miostile2.css" />
    <script language="javascript" type="text/javascript" src="/js/mioscript.js">
    </script>
    </head>

Ricordarsi che vengono applicati i principi di configurazione a cascata, quindi qualunque inclusione di file definita nel `view.yml` dell'applicazione verrà applicata in ogni pagina dell'applicazione. I Listati 7-28, 7-29 e 7-30 mostrano questo principio.

Listato 7-28 - Esempio di `view.yml` nell'applicazione

    default:
      stylesheets: [main]

Listing 7-29 - Sample Module `view.yml`

    indexSuccess:
      stylesheets: [special]

    all:
      stylesheets: [additional]

Listato 7-30 - Visualizzazione della vista `indexSuccess`

    [php]
    <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/additional.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="/css/special.css" />

Quando si ha bisogno di rimuovere un file definito ad un livello più alto, basta aggiungere un segno meno (`-`) davanti al nome del file nella definizione di livello inferiore, come mostrato nel Listato 7-31.

Listato 7-31 - Esempio con `view.yml` nel modulo. Viene rimossa un file definito nel livello applicazione

    indexSuccess:
      stylesheets: [-main, special]

    all:
      stylesheets: [additional]

Per rimuovere tutti i fogli di stile o i file JavaScript ,usare `-*` come nome del file, come mostrato nel Listato 7-32.

Listato 7-32 - Esempio con `view.yml` nel modulo. Vengono rimossi tutti i file definiti nel livello applicazione

    indexSuccess:
      stylesheets: [-*]
      javascripts: [-*]

Si può lavorare con maggiore precisione e definire un parametro ulteriore per forzare  la posizione in cui includere il file (prima o ultima posizione), come mostrato nel Listato 7-33. Questo funziona sia per i fogli di stile che per i JavaScript.

Listato 7-33 - Definire la posizione di un file incluso

    [yml]
    // In view.yml
    indexSuccess:
      stylesheets: [special: { position: first }]

-
    
    [php]
    // In una azione
    $this->getResponse()->addStylesheet('special', 'first');
    
    // In un template
    <?php use_stylesheet('special', 'first') ?>

Si può anche decidere di ignorare la trasformazione dei nomi dei file, in modo che i risultanti tag `<link>` o `<script>` vengano riferiti alla esatta locazione specificata, come mostrato nel Listato 7-34.

Listato 7-34 - Inclusione di un foglio di stile con Style Sheet con nome inalterato

    [yml]
    // In view.yml
    indexSuccess:
      stylesheets: [main, paper: { raw_name: true }]

-
    
    [php]
    // In una azione
    $this->getResponse()->addStylesheet('main', '', array('raw_name' => true));
    
    // In un template
    <?php use_stylesheet('main', '', array('raw_name' => true)) ?>
    
    // La vista risultante
    <link rel="stylesheet" type="text/css" href="main" />

Per specificare il media relativo ad una inclusione di un foglio di stile, si possono cambiare le opzioni predefinite del tag per i fogli di stile, come mostrato nel Listato 7-35.

Listato 7-35 - Inclusione di un foglio di stile specificando il media

    [yml]
    // In the view.yml
    indexSuccess:
      stylesheets: [main, paper: { media: print }]

-
    
    [php]
    // In una azione
    $this->getResponse()->addStylesheet('paper', '', array('media' => 'print'));
    
    // In un template
    <?php use_stylesheet('paper', '', array('media' => 'print')) ?>
    
    // La vista risultante
    <link rel="stylesheet" type="text/css" media="print" href="/css/paper.css" />

>**SIDEBAR**
>Nota relativa all'inclusione di file usando il file view.yml
>
>La pratica migliore è quella di definire i file predefiniti per i fogli di stile e i javascript nel file view.yml del progetto e includere specifici fogli di stile o file javascript nei template, utilizzando gli appositi helper. In questo modo, non è necessario rimuovere o sostituire file già inclusi, cosa che in alcuni casi può diventare un problema.

#### Configurazione del layout

In base a come è progettato un sito web, è possibile avere più layout. I siti web classici ne hanno almeno due: il layout predefinito e il layout per il pop-up.

Si è già visto che il layout predefinito è `mioprogetto/apps/frontend/templates/layout.php`. I layout devono essere aggiunti nella stessa cartella globale `templates/`. Se si vuole che una vista utilizzi il file `frontend/templates/mio_layout.php`, usare la sintassi mostrata nel Listato 7-36.

Listato 7-36 - Definizione di un layout

    [yml]
    // In view.yml
    indexSuccess:
      layout: mio_layout

-

    [php]
    // In una azione
    $this->setLayout('mio_layout');
    
    // In un template
    <?php decorate_with('mio_layout') ?>

Alcune viste non necessita di layout (ad esempio, pagine con testo semplice o feed RSS=. In questo caso, impostare `has_layout` a `false`, come mostrato nel Listato 7-37.

Listato 7-37 - Rimozione del layout

    [yml]
    // In `view.yml`
    indexSuccess:
      has_layout: false
    
-

    [php]
    // In una azione
    $this->setLayout(false);
    
    // In un template
    <?php decorate_with(false) ?>

>**NOTE**
>Le viste per le azioni Ajax non hanno layout per impostazione predefinita.

Escapizzazione dell'output
--------------------------

Quando vengono inseriti dati dinamici in un template, bisogna essere sicuri dell'integrità dei dati. Per esempio, se i dati provengono da form compilati da utenti anonimi, c'è il rischio che possano includere script maligni che hanno lo scopo di lanciare attacchi di tipo cross-site scripting (XSS). 

When you insert dynamic data in a template, you must be sure about the data integrity. For instance, if data comes from forms filled in by anonymous users, there is a risk that it may include malicious scripts intended to launch cross-site scripting (XSS) attacks. Bisogna essere in grado di fare l'escape dei dati visualizzati, in modo che qualunque tag HTML possa contenere diventi inerte.

Come esempio, supponiamo che un utente compili un campo input con il seguente valore:

    [php]
    <script>alert(document.cookie)</script>

Se si fa un echo di questo valore senza precauzioni, su ogni browser verrà eseguito il JavaScript e permetterà attacchi ben più pericolosi rispetto alla visualizzazione di un messaggio di alert. Questo è il motivo per il quale è necessario fare l'escape del valore prima della sua visualizzazione, in modo che diventi un qualcosa del genere:

    [php]
    &lt;script&gt;alert(document.cookie)&lt;/script&gt;

Si può eseguire l'escape dell'output manualmente racchiudendo ogni valore insicuro con la chiamata a `htmlspecialchars()`, ma questo approccio può diventare molto ripetitivo e soggetto ad errori. Quindi symfony fornisce un sistema speciale, chiamato escapizzazione dell'output, che escapizza automaticamente ogni variabile in output nel template. É attivato in modalità predefinita nel `settings.yml` dell'applicazione.

### Attivazione dell'escapizzazione dell'output

L'escapizzazione dell'output è configurata globalmente per una applicazione nel file `settings.yml`. Due parametri controllano il modo con il quale lavoro l'escapizzatore dell'output: la strategia stabilisce come le variabili vengono rese disponibili alla vista e il metodo è la funzione di escapizzazione predefinita applicata ai dati.

In sostanza, tutto quelllo che bisogna fare per attivare l'escapizzazione dell'output è impostare il parametro `escaping_strategy` a `true` (che è il valore predefinito), come mostrato nel Listato 7-38.

Listato 7-38 - Attivazione dell'escapizzazione dell'output, in `frontend/config/settings.yml`

    all:
      .settings:
        escaping_strategy: true
        escaping_method:   ESC_SPECIALCHARS

Questo, per impostazione predefinita, aggiungerà `htmlspecialchars()` nell'output di tutte le variabili. Ad esempio, supponiamo di definire una variabile `test` in una azione:

    [php]
    $this->test = '<script>alert(document.cookie)</script>';

Con l'escapizzazione dell'output a on, facendo l'echo di questa variabile nel template verranno mostrati i dati escapizzati:

    [php]
    echo $test;
     => &lt;script&gt;alert(document.cookie)&lt;/script&gt;

Inoltre, ogni template ha accesso ad una variabile $sf_data` che è un contenitore di oggetti che fa riferimento a tutte le variabili escapizzate. Quindi si può anche visualizzare la variabile test in questo modo:

    [php]
    echo $sf_data->get('test');
    => &lt;script&gt;alert(document.cookie)&lt;/script&gt;

>**TIP**
>L'oggetto $sf_data object implementa l'interfaccia Array, quindi invece di usare `$sf_data->get('miavariabile')`, si possono recuperare i valori escapizzati chiamando `$sf_data['myvariable']`. Ma non è un array reale, quindi le funzioni tipo `print_r()` non funzioneranno come ci si attende.

`$sf_data` fornisce anche l'accesso ai dati non escapizzati, detti anche raw. Questo è utile quando una variabile memorizza codice HTML che deve essere interpretato dal browser, a condizione che vi "fidiate" di questa variabile. Chiamare il metodo `getRaw()` quando si vogliono visualizzare dati raw.

    [php]
    echo $sf_data->getRaw('test');
     => <script>alert(document.cookie)</script>

Si vorrà accedere ai dati raw tutte le volte che si ha bisogno che le variabili contenenti HTML siano realmente interpretate come HTML.

Quando `escaping_strategy` è `false`, `$sf_data` è comunque disponibile, ma restituisce sempre dati raw.

### Helper per l'escape

Gli helper per l'escaping sono funzioni che restituiscono una versione escapizzata del loro input. Possono essere forniti come predefiniti `escaping_method` nel file `settings.yml` o per specificare un metodo di escape per uno specifico valore nella vista. Sono disponibili i seguenti helper per l'escape:

  * `ESC_RAW`: Non fa l'escape del valore.
  * `ESC_SPECIALCHARS`: Applica la funzione PHP `htmlspecialchars()` all'input.
  * `ESC_ENTITIES`: Applica la funzione PHP `htmlentities()` all'input con `ENT_QUOTES` come stile per le quote.
  * `ESC_JS`: Fa l'escape di un valore che deve essere inserito in una stringa JavaScript che deve essere usata come HTML. É utile per fare l'escape di cose dove l'HTML deve essere cambiato dinamicamente usando il JavaScript.
  * `ESC_JS_NO_ENTITIES`: Fa l'escape di un valore che deve essere messo in una stringa JavaScript ma non aggiunge entità. É utile se il valore deve essere visualizzato usando una dialog box (ad esempio, per una variabile `miaStringa` usata in `javascript:alert(miaStringa);`).

### Fare l'escape di array e oggetti

L'escape dell'output funziona non solo per le stringhe, ma anche per gli array e gli oggetti. Tutti i valori che sono oggetti o array passeranno il loro stato escapizzato ai loro figli. Assumendo che la strategia sia impostata a `true`, il Listato 7-39 mostra l'escapizzazione a cascata.

Listato 7-39 - L'escapizzazione funziona anche per gli array e gli oggetti

    [php]
    // Definizione della classe
    class miaClasse
    {
      public function testSpecialChars($value = '')
      {
        return '<'.$value.'>';
      }
    }

    // In una azione
    $this->test_array = array('&', '<', '>');
    $this->test_array_of_arrays = array(array('&'));
    $this->test_object = new myClass();

    // In un template
    <?php foreach($test_array as $value): ?>
      <?php echo $value ?>
    <?php endforeach; ?>
     => &amp; &lt; &gt;
    <?php echo $test_array_of_arrays[0][0] ?>
     => &amp;
    <?php echo $test_object->testSpecialChars('&') ?>
     => &lt;&amp;&gt;

È un dato di fatto, che le variabili nel template non sono del tipo ci si potrebbe aspettare. Il sistema di escapizzazione dell'output le "decora" e le trasforma in oggetti speciali:

    [php]
    <?php echo get_class($test_array) ?>
     => sfOutputEscaperArrayDecorator
    <?php echo get_class($test_object) ?>
     => sfOutputEscaperObjectDecorator

Questo spiega perché alcune normali funzioni PHP (come `array_shift()`, `print_r()` e altre) non funzionano più con gli array esacpizzati. Ma questi possono essere acceduti utilizzando `[]`, essere attraversati usando `foreach` e restituire il corretto risultato con `count()`. E in ogni caso nei template i dati dovrebbero essere a sola lettura, quindi la maggior parte degli accessi verrà fatta utilizzando metodi che funzionano correttamente.
	 
C'è ancora un modo per recuperare i dati raw attraverso l'oggetto `$sf_data`. Inoltre, i metodi di oggetti escapizzati vengono alterati per accettare un parametro aggiuntivo: un metodo di escape. Così si può scegliere un metodo alternativo per fare l'escape ogni volta che si visualizza una variabile in un template, oppure optare per l'helper `ESC_RAW` per disattivare l'escape. Vedere il Listato 7-40 come esempio.

Listato 7-40 - I metodi degli oggetti escapizzati accettano un parametro aggiuntivo

    [php]
    <?php echo $test_object->testSpecialChars('&') ?>
    => &lt;&amp;&gt;
    // The three following lines return the same value
    <?php echo $test_object->testSpecialChars('&', ESC_RAW) ?>
    <?php echo $sf_data->getRaw('test_object')->testSpecialChars('&') ?>
    <?php echo $sf_data->get('test_object', ESC_RAW)->testSpecialChars('&') ?>
     => <&>

Se si a a che fare con oggetti nei template, sarà necessario usare spesso il trucco del parametro aggiuntivo, dal momento che è il modo più veloce per ottenere i dati grezzi in una chiamata al metodo.

>**CAUTION**
>Anche le normali varibili di symfony sono escapizzate quando si imposta ad on l'escapizzazione dell'output. Quindi bisogna tener presente che `$sf_user`, `$sf_request`, `$sf_param` e `$sf_context` funzionano ancora, ma i loro metodi restituiscono dati escapizzati, a meno che non si aggiunga `ESC_RAW` come argomento finale alle loro chiamate di metodi.

-

>**TIP**
>Anche se l'XSS è uno degli exploit più comuni nei siti web, non è l'unico. Anche il CSRF è molto popolare e symfony fornisce una protezione automatica per i form. Si può scoprire come funziona questa protezione di sicurezza nel capitolo 10..

Riepilogo
---------

Sono disponibili molti tipi di strumenti per manipolare il livello di presentazione. I template vengono generati in pochi secondi, grazie agli helper. I layout, i partial e i componenti sono utili per la modularità e la riusabilità. La configurazione della vista sfrutta la velocità dello YAML per gestire (soprattutto) gli header delle pagine. La configurazione a cascata esime dal definire una impostazione per ciascuna vista. Per ogni modifica della presentazione che dipende da dati dinamici, l'azione ha accesso all'oggetto `sfResponse`. La vista è al sicuro da attacchi XSS, grazie al sistema di escapizzazione dell'output.
