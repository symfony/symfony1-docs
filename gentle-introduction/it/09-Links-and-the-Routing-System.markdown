Capitolo 9 - Link e il sistema di routing
=========================================

Link e URL meritano particolare attenzione in un framework per applicazioni web. Questo avviene perché
l'unico entry point dell'applicazione (il front controller) e l'utilizzo degli helper all'interno dei
template permettono una completa separazione tra il modo in cui gli URL funzionano e la loro rappresentazione.
Questo è chiamato routing. Più di un semplice gadget il routing è un utile strumento per rendere le
applicazioni web ancora più user-friendly e sicure. Questo capitolo mostrerà quello che è necessario
sapere per gestire gli URL in una applicazione symfony:

  * Cos'è il sistema di routing e come funziona
  * Come utilizzare gli helper dei link nei template e abilitare il routing di URL uscenti
  * Come configurare le regole di routing per modificare la rappresentazione degli url

Come tocco finale verranno mostrati alcuni trucchi per gestire le performance del sistema di routing.


Che cos'è il routing?
---------------------

Il routing è un meccanismo che riscrive gli URL per renderli più user-friendly. Per capire perché questa cosa è importante è necessario riflettere qualche minuto su cosa sia in effetti un URL

### URL come comandi per il Server

Gli URL portano informazioni dal browser al server richiesto affinché questo svolga una azione come desiderato dall'utente.
Per esempio, un URL tradizionale contiene il percorso a uno script e alcuni parametri necessari a completare la richiesta, come in questo esempio:
      http://www.example.com/web/controller/article.php?id=123456&format_code=6532

Questo URL espone informazioni sull'architettura dell'applicazione e il database. Gli sviluppatori 
di solito nascondono l'infrastuttura dell'applicazione nell'interfaccia (es esempio, scegliendo come 
titoli di pagina "Profilo" piuttosto che "QZ7.65"). Rivelare i dettagli del funzionamento interno dell'
applicazione nel URL va contro questo sforzo e ha diversi svantaggi:

  * I dati contenuti nel URL creano potenziali falle di sicurezza. Nell'esempio precedente, cosa accade se
  un utente malintenzionato cambia il valore del parametro `id`? Significa che l'applicazione offre un interfaccia
  direttamente verso il database? Cosa accade se l'utente prova a utilizzare un diverso nome per lo script, come 
  `admin.php` per divertimento? In conclusione, gli URL "raw" offrono un modo facile per hackerare una applicazione
  e gestire la sicurezza con quest'ultimi è quasi impossibile.
  * L'incomprensibilità degli URL li rendono ingombranti ovunque appaiono e smorzano l'impatto del contenuto che li
  circonda. Oggi gli URL non compaiono solo nella barra degli indirizzi. Sono visibili quando un utente 
  passa il mouse sopra un link così come nei risultati di una ricerca. Quando un utente cerca informazioni bisogna cercare 
  di fornirgli indizi facilmente comprensibili riguardo quello che ha trovato invece di un URL confuso come quello
  mostrato in figura 9-1.

Figura 9-1 - Gli URL compaiono in molti posti, come nei risultati della ricerca

![Gli URL compaiono in molti posti, come nei risultati della ricerca](http://www.symfony-project.org/images/book/1_4/F0901.png "Gli URL compaiono in molti posti, come nei risultati della ricerca")

  * Se è necessario modificare un URL (ad esempio perché il nome dello script o uno dei suoi parameteri viene modificato),
  ogni link a questo URL deve essere cambiato allo stesso modo. Questo significa che le modifiche alla struttura del controller
  sono pesanti e costose, che non è l'ideale nello sviluppo agile.

Potrebbe essere molto peggio se symfony non usasse il pattern front controller, ovvero se l'applicazione contenesse molti script
accessibili da Internet, in diverse cartelle, come questi:

    http://www.example.com/web/gallery/album.php?name=my%20holidays
    http://www.example.com/web/weblog/public/post/list.php
    http://www.example.com/web/general/content/page.php?name=about%20us

In questo caso gli sviluppatori sarebbero obbligati a vincolare la struttura degli URL con quella del filesystem, 
rendendo il mantenimento un incubo al cambiare della struttura.

### URL come parte dell'interfaccia

L'idea dietro il routing è considerare gli URL come parte dell'interfaccia. L'applicazione può formattare un URL
per dare carte informazioni all'utente e l'utente può utilizzare l'URL per accedere alle risorse dell'applicazione.

Questo è possibile nelle applicazioni symfony, perché gli URL presentati all'utente non sono correlati alle 
istruzioni necessarie al server per eseguire la richiesta. Al contrario sono correlati alla risorsa richiesta 
e possono essere formattati liberamente. Symfony ad esempio è in grado di comprendere il seguente URL 
e mostrare la stessa pagina del primo URL mostrato in questo capitolo:

    http://www.example.com/articles/finance/2010/activity-breakdown.html

I benefici sono immensi:

  * Le URL assumono effettivamente un significato ben preciso e possono aiutare l'utente a comprendere se la pagina ottenuta da un determinato link contiene ciò che ci si aspetta. 
  Un link può contenere maggiori dettagli riguardanti la risorsa alla quale esso è legato. Questo è particolarmente utile per i risultati forniti dai motori di ricerca. 
  Inoltre, può capitare che gli URL appaiano senza alcun riferimento al titolo della pagina (si pensi a quando si copia un link da spedire via e-mail) 
  e, in tale caso, devono assumere un significato ben preciso. La figura 9-2 mostra un esempio di URL user-friendly.
  
   * L'implementazione tecnica è nascosta all'utente: non viene reso noto quale script venga utilizzato, non è possibile cercare di indovinare un id o parametri simili: l'applicazione è meno vulnerabile a potenziale attacchi.
    Inoltre è possibile modificare quello che avviene "dietro le quinte", senza che gli utenti ne risentano (non ci sarà un 404 o un redirect permanente) 
Figura 9-2 - Gli URL possono contenere informazioni aggiuntive alla pagina, ad esempio la data di pubblicazione

![Gli URL possono contenere informazioni aggiuntive alla pagina, ad esempio la data di pubblicazione](http://www.symfony-project.org/images/book/1_1/F0902.png "Gli URL possono contenere informazioni aggiuntive alla pagina, ad esempio la data di pubblicazione")

  * Gli URL scritti su documenti di carta sono più facili da scrivere e ricordare. Se una azienda compare su biglietti da visita come `http://www.example.com/controller/web/index.jsp?id=ERD4`, probabilmente non riceverà molte visite.
  * Gli URL possono diventare un modo di eseguire comandi, per recuperare informazioni in modo intuitivo. Le applicazioni che offrono tale possibilità sono più veloci da utilizzare per utenti avanzati.

        // Lista di risultati: aggiungi un nuovo tag per raffinare il risultato
        http://del.icio.us/tag/symfony+ajax
        // Pagina profilo utente: cambia il nome per vedere il profilo di un altro utente
        http://www.askeet.com/user/francois

  * È possibile cambiare la formattazione degli URL e il nome/parametri dell'azione in modo indipendente, con una singola modifica. Significa che è possibile prima sviluppare e alla fine formattare gli URL senza creare confusione.
  * Anche quando si riorganizza l'applicazione, gli URL possono rimanere le stesse per il mondo esterno. Ciò rende gli URL persistenti, che è vitale in quanto rende possibile i bookmark di pagine dinamiche.
  * I motori di ricerca tendono a evitare pagine dinamiche (che terminano con `.php`, `.asp` e così via) quando indicizzano i siti. È così possibile formattare gli URL in modo che i motori di ricerca pensino di stare navigando su un sito statico, 
    anche quando incontrano pagine dinamiche, in modo da migliorare il ranking delle pagine stesse.
  * È più sicuro. Un URL non riconosciuto verrà redirezionato a una pagina specificata dallo sviluppatore e gli utenti non possono navigare la struttura radice provando indirizzi a caso. Il nome effettivo dello script, così come i suoi parametri, sono nascosti.
  
La corrispondenza tra l'URL presentato all'utente e il nome effettivo dello script e i suoi parametri viene conseguita dal sistema di routing, basato su schemi che possono essere modificati tramite configurazione.

>**NOTE**
>E le risorse? Fortunatamente, gli URL delle risorse (immagini, fogli di stile e script JavaScript) non compaiono frequentemente durante la navigazione, per cui non c'è un grande bisogno del motore di routing. 
>In symfony, tutte le risorse sono situate all'interno della cartella `web/` e il loro URL coincidono con le loro posizioni nel filesystem. Comunque, è possibile gestire risorse dinamiche (dalle azioni) utilizzando URL generati nei relativi helper. Ad esempio, per visualizzare un'immagine generata dinamicamente, è sufficiente utilizzare `image_tag(url_for('captcha/image?key='.$key))`.

### Come funziona

Symfony scollega gli URL esterni dai propri URI interni. La corrispondenza tra le due viene eseguita dal sistema di routing. Per facilitare le cose, symfony utilizza per le URI interne una sintassi molto simile a quella degli URL normali. Il listato 9-1 ne mostra un esempio.

Listato 9-1 - URL esterni e URI interni

    // Sintassi URI interne
    <module>/<action>[?param1=value1][&param2=value2][&param3=value3]...

    // Esempio di URI interna, non compare mai all'utente finale
    article/permalink?year=2006&subject=finance&title=activity-breakdown

    // Esempio di URL interna, che compare all'utente finale
    http://www.example.com/articles/finance/2006/activity-breakdown.html

Il sistema di routing utilizza un file di configurazione speciale, chiamato `routing.yml`, nel quale è possibile definire le regole. Si consideri la regola mostrata nel listato 9-2. Definisce uno schema come `articles/*/*/*` e dà un nome alle parti di codice che coincidono con i caratteri jolly.

Listato 9-2 - Esempio di regola di routing

    article_by_title:
      url:    articles/:subject/:year/:title.html
      param:  { module: article, action: permalink }

Ogni richiesta per un'applicazione symfony viene prima di tutto analizzata dal sistema di routing (la qual cosa risulta piuttosto semplice, in quanto ogni richiesta viene gestita da un unico front controller). Il sistema di routing cerca una corrispondenza tra l'URL della richiesta e gli schemi definiti nelle regole di routing. Se una viene trovata, i caratteri jolly diventano parametri di richiesta e vengono uniti a quelli definiti nella chiave `param:`. Il listato 9-3 ne mostra il funzionamento.

Listato 9-3 - Il sistema di routing interpreta gli URL della richiesta

    // L'utente scrive (o clicca su) questo URL esterno
    http://www.example.com/articles/finance/2006/activity-breakdown.html

    // Il front controller trova una corrispondenza con la regola article_by_title
    // Il sistema di routing crea i parametri seguenti
      'module'  => 'article'
      'action'  => 'permalink'
      'subject' => 'finance'
      'year'    => '2006'
      'title'   => 'activity-breakdown'
      
La richiesta viene quindi passata all'azione `permalink` del modulo `article`, il quale in questo modo ha tutti i parametri necessari a mostrare l'articolo richiesto.

Ma questo meccanismo deve anche funzionare in senso opposto. Dato che l'applicazione deve mostrare gli URL esterni nei propri link, devi fornire al sistema di routing abbastanza informazioni affinché esso possa comprendere quale regola applicare.
Inoltre non si deve assolutamente scrivere nei template i link con i tag `<a>`, direttamente, perché in tal modo non verrebbe ignorato completamente il sistema di routing; si deve invece utilizzare un helper speciale, come mostrato nel listato 9-4.

Listato 9-4 - Il sistema di routing formatta gli URL nei template

    [php]
    // L'helper url_for() trasforma un URI interno in un URL esterno
    <a href="<?php echo url_for('article/permalink?subject=finance&year=2006&title=activity-breakdown') ?>">click here</a>

    // L'helper riconosce che l'URI soddisfa la regola article_by_title
    // Per cui il sistema di routing crea l'URL
     => <a href="http://www.example.com/articles/finance/2006/activity-breakdown.html">click here</a>

    // L'helper link_to() restituisce un link, evitando di mischiare PHP con HTML
    <?php echo link_to(
      'click here',
      'article/permalink?subject=finance&year=2006&title=activity-breakdown'
    ) ?>

    // Internamente, link_to() chiamerà url_for() in modo che il risultato sia lo stesso
    => <a href="http://www.example.com/articles/finance/2006/activity-breakdown.html">click here</a>

Quindi il routing è un meccanismo che funziona in due direzioni e funziona solo se si utilizza l'helper `link_to()` per formattare i link.

URL Rewrite
-----------

Prima di approfondire il sistema di routing, c'è un'altra questione che va chiarita. 
Negli esempi precedenti non è stata fatta menzione del front controller (`index.php` o `frontend_dev.php`) nelle URI interne. 
Il front controller decide l'ambiente, non gli elementi dell'applicazione. Per cui tutti i link devono essere indipendenti dall'ambiente e il nome del front controller non deve mai apparire negli URI interni.

Negli esempi non c'è traccia del nome dello script nemmeno negli URL. Questo perché per default nell'ambiente di produzione gli URL generati non contengono il nome dello script. Il parametro `no_script_name` nel file `settings.yml` controlla esattamente questo comportamento; impostandolo su `false`, come nel listato 9-5, ogni URL stampata tramite gli helper dei link conterrà il nome dello script del front controller.

Listato 9-5 - Mostrare il nome del front controller negli URL, in `apps/frontend/settings.yml`

    prod:
      .settings:
        no_script_name:  false

Così facendo gli URL generati appariranno nel modo seguente:

    http://www.example.com/index.php/articles/finance/2006/activity-breakdown.html

In tutti gli ambienti diversi da quello di produzione, il parametro `no_script_name` è impostato su `false` come impostazione predefinita. Per cui quando si naviga l'applicazione nell'ambiente di sviluppo, il nome del front controller risulta sempre negli URL.

    http://www.example.com/frontend_dev.php/articles/finance/2006/activity-breakdown.html

In produzione, `no_script_name` è impostato su `on`, così gli URL mostreranno solo le informazioni di routing e risulteranno più user-friendly. Non apparirà alcuna informazione tecnica.

    http://www.example.com/articles/finance/2006/activity-breakdown.html

Ma come fa l'applicazione a sapere quale front controller chiamare? Qui è dove entra in gioco l'URL rewrite. Il web server può essere configurato per invocare un determinato script qualora non venga specificato nell'URL.

In Apache, ciò è possibile solo dopo aver attivato l'estensione `mod_rewrite`. Ogni progetto symfony è dotato di un file `.htaccess`, che aggiunge alcune impostazioni `mod_rewrite` per la cartella `web/` alla configurazione del server. Il contenuto predefinito di tale file è mostrato nel listato 9-6.

Listato 9-6 - Regole di rewrite predefinite per Apache, in `myproject/web/.htaccess`

    <IfModule mod_rewrite.c>
      RewriteEngine On

      # salta tutti i file che inizia con un punto
      RewriteCond %{REQUEST_URI} \..+$
      RewriteCond %{REQUEST_URI} !\.html$
      RewriteRule .* - [L]

      # controllo se esiste la versione .html (caching)
      RewriteRule ^$ index.html [QSA]
      RewriteRule ^([^.]+)$ $1.html [QSA]
      RewriteCond %{REQUEST_FILENAME} !-f

      # se no, redirige al nostro front web controller
      RewriteRule ^(.*)$ index.php [QSA,L]
    </IfModule>

Il web server controlla gli URL che riceve. Se l'URL non contiene un suffisso e se non c'è già una versione disponibile in cache della pagina (consultare il capitolo 12 riguardante la cache), allora la richiesta viene gestita dallo script `index.php`.

Comunque, la cartella `web/` di un progetto symfony è condivisa da tutte le applicazioni e da tutti gli ambienti del progetto. Ciò significa che spesso esisterà più di un front controller in tale cartella. Ad esempio, un progetto che abbia un'applicazione di `frontend` e una di `backend`, 
un ambiente `dev` e uno `prod` conterrà quattro script per i front controller nella cartella `web/`:

    index.php         // frontend in prod
    frontend_dev.php  // frontend in dev
    backend.php       // backend in prod
    backend_dev.php   // backend in dev

Le impostazioni mod_rewrite possono specificare il nome di un solo front controller di default. Se si imposta `no_script_name` a `true` per tutte le applicazioni e tutti gli ambienti, tutti gli URL saranno interpretati come richieste per l'applicazione `frontend` nell'ambiente `prod`. Ecco perché, per ogni progetto, si può avere solo una applicazione e un ambiente che sfruttino il vantaggio dell'URL rewrite.

>**TIP**
>In effetti c'è un modo per avere più di un'applicazione senza script name. È sufficiente creare sottocartelle nella cartella web e collocarci i vari front controller. È necessario cambiare il percorso del file `ProjectConfiguration` di conseguenza e creare le configurazioni `.htaccess` necessarie a ogni applicazione.

Helper Link 
-----------

Per trarre maggior vantaggio dal sistema di routing, si dovrebbe utilizzare gli helper dei link invece dei tag `<a>` nei template. Non bisogna pensare a ciò come uno svantaggio, bensì come a un modo per mantenere l'applicazione pulita e facile da manutenere. Inoltre, questi helper offrono qualche scorciatoia molto utile.

### Link, pulsanti e form

È stato già precedentemente mostrato l'helper `link_to()`. Esso restituisce un link che rispetta la sintassi XHTML e si aspetta due parametri: l'elemento che deve essere cliccato e l'URI interno. Se invece di un link si volesse un pulsante, è sufficiente utilizzare l'helper `button_to()`. 
Anche i form sono provvisti di un helper per gestire il valore dell'attributo `action`. Maggiori informazioni sui form nel prossimo capitolo. Il listato 9-7 mostra alcuni esempi di helper per i link.

Listato 9-7 - Alcuni esempi di helper per i tag `<a>, <input>, e <form>`

    [php]
    // Link su una stringa
    <?php echo link_to('my article', 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France">my article</a>

    // Link su un'immagine
    <?php echo link_to(image_tag('read.gif'), 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France"><img src="/images/read.gif" /></a>

    // Pulsante
    <?php echo button_to('my article', 'article/read?title=Finance_in_France') ?>
     => <input value="my article" type="button"onclick="document.location.href='/routed/url/to/Finance_in_France';" />

    // Form
    <?php echo form_tag('article/read?title=Finance_in_France') ?>
     => <form method="post" action="/routed/url/to/Finance_in_France" />

Tali helper accettano sia URI interni che URL assoluti (che cominciano con `http://`, e vengono ignorate dal sistema di routing) e ancore. Da notare che in applicazioni reali, gli URI interni vengono costruiti con parametri dinamici. Il listato 9-8 mostra un esempio di tali casi.

Listato 9-8 - URL accettate dagli helper dei link

    [php]
    // URI interne
    <?php echo link_to('my article', 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France">my article</a>

    // URI interne con parametri dinamici
    <?php echo link_to('my article', 'article/read?title='.$article->getTitle()) ?>

    // URI interne con anchor
    <?php echo link_to('my article', 'article/read?title=Finance_in_France#foo') ?>
     => <a href="/routed/url/to/Finance_in_France#foo">my article</a>

    // URL assolute
    <?php echo link_to('my article', 'http://www.example.com/foobar.html') ?>
     => <a href="http://www.example.com/foobar.html">my article</a>

### Opzioni degli helper dei link

Come spiegato precedentemente nel capitolo 7, gli helper accettano un ulteriore parametro opzionale, che può essere un array associativo o una stringa. Questo è vero anche per gli helper dei link, come mostrato nel listato 9-9.

Listato 9-9 - Gli helper dei link accettano un parametro addizionale

    [php]
    // Opzione aggiuntiva come array associativo
    <?php echo link_to('my article', 'article/read?title=Finance_in_France', array(
      'class'  => 'foobar',
      'target' => '_blank'
    )) ?>

    // Opzione aggiuntiva come stringa (stesso risultato)
    <?php echo link_to('my article', 'article/read?title=Finance_in_France','class=foobar target=_blank') ?>
     => <a href="/routed/url/to/Finance_in_France" class="foobar" target="_blank">my article</a>

È possibile anche aggiungere una delle opzioni specifiche di symfony, per gli helper dei link: `confirm` e `popup`. La prima mostra una finestra di dialogo di conferma JavaScript che appare quando si clicca sul link, mentre la seconda apre il link in una nuova finestra, come mostrato nel listato 9-10.

Listato 9-10 - Le opzioni '`confirm`' e '`popup`' per gli helper dei link

    [php]
    <?php echo link_to('delete item', 'item/delete?id=123', 'confirm=Are you sure?') ?>
     => <a onclick="return confirm('Are you sure?');"
           href="/routed/url/to/delete/123.html">add to cart</a>

    <?php echo link_to('add to cart', 'shoppingCart/add?id=100', 'popup=true') ?>
     => <a onclick="window.open(this.href);return false;"
           href="/fo_dev.php/shoppingCart/add/id/100.html">add to cart</a>

    <?php echo link_to('add to cart', 'shoppingCart/add?id=100', array(
      'popup' => array('Window title', 'width=310,height=400,left=320,top=0')
    )) ?>
     => <a onclick="window.open(this.href,'Window title','width=310,height=400,left=320,top=0');return false;"
           href="/fo_dev.php/shoppingCart/add/id/100.html">add to cart</a>

Tali opzioni possono essere anche usate insieme.

### Opzioni GET e POST non reali

Può capitare a volte che gli sviluppatori web utilizzino richieste GET per utilizzarle in POST. Ad esempio, si consideri la seguente URL:

    http://www.example.com/index.php/shopping_cart/add/id/100

Questa richiesta cambierà i dati contenuti nell'applicazione, aggiungendo un oggetto al carrello, memorizzato in sessione o nel database. Questo URL potrebbe essere messo nei bookmark, in cache e indicizzato dai motori di ricerca. 
Si immagini tutti gli effetti poco puliti o chiari che potrebbero accadere al database od alla metrica di un sito utilizzando questa tecnica. In effetti, questa richiesta dovrebbe essere considerata come una POST, in quanto i motori di ricerca non indicizzano tali richieste.

Symfony fornisce un modo per trasformare effettivamente una chiamata agli helper `link_to()` o `button_to()` in una POST. Aggiungendo semplicemente un'opzione `post=true`, come mostrato nel listato 9-11.

Listato 9-11 - Trasformare un link in una richiesta in POST

    [php]
    <?php echo link_to('go to shopping cart', 'shoppingCart/add?id=100', 'post=true') ?>
     => <a onclick="f = document.createElement('form'); document.body.appendChild(f);
                    f.method = 'POST'; f.action = this.href; f.submit();return false;"
           href="/shoppingCart/add/id/100.html">go to shopping cart</a>

Il tag `<a>` generato dal codice appena mostrato possiede un attributo `href` e i browser senza supporto a JavaScript, come i robot dei motori di ricerca, seguiranno il link come una chiamata in GET. 
È quindi necessario che l'azione risponda solo a comandi in POST, ad esempio aggiungendo qualcosa come:

    [php]
    $this->forward404Unless($request->getRequest()->isMethod('post'));

all'inizio dell'azione. È sufficiente essere sicuri di non utilizzare questi link all'interno di form, in quanto essi generano il proprio tag `<form>`.

È buona abitudine trasformare in POST tutte le chiamate che in effetti spediscono dati.

### Forzare parametri di richiesta come variabili GET

A seconda delle regole di routing impostate, le variabili passate come parametri a `link_to()` sono trasformate in schemi. Se nessuna regola del file `routing.yml` coincide con l'URI interno, la regola predefinita trasforma `module/action?key=value` in `/module/action/key/value`, come mostrato nel listato 9-12.

Listato 9-12 - Regola di routing predefinita

    [php]
    <?php echo link_to('my article', 'article/read?title=Finance_in_France') ?>
    => <a href="/article/read/title/Finance_in_France">my article</a>

Se si avesse bisogno effettivamente di mantenere la sintassi GET, per avere parametri di richiesta nella forma `?key=value`, si devono forzare tali variabili fuori dall'URL, nell'opzione `query_string`. 
Dato che ciò andrebbe in conflitto con un'ancora nell'URL, lo si devi collocare nell'opzione dell'ancora invece che prependerlo all'URI interno. Tutti gli helper dei link accettano questa opzione, come dimostrato nel listato 9-13.

Listato 9-13 - Forzare parametri in GET con l'opzione `query_string`

    [php]
    <?php echo link_to('my article', 'article/read', array(
      'query_string' => 'title=Finanza_in_Francia',
      'anchor'       => 'pippo',
    )) ?>
    => <a href="/article/read?title=Finanza_in_Francia#pippo">il mio articolo</a>

Un URL con parametri di richiesta in GET può essere interpretata da uno script lato client e dalle variabili $_GET e $_POST lato server.

>**SIDEBAR**
>Helper per le risorse
>
>Il capitolo 7 ha introdotto gli helper `image_tag()`, `stylesheet_tag()` e `javascript_include_ tag()`, che permettono di includere un'immagine, un foglio di stile od uno script JavaScript nella risposta. 
>I percorsi di tali asset non vengono processati dal sistema di routing, in quanto puntano a risorse situate nella cartella web pubblica.
>
>Non c'è bisogno di menzionare l'estensione per un asset. Symfony aggiungerà automaticamente `.png`, `.js` o `.css` a un'immagine, JavaScript o foglio di stile. 
>Inoltre, symfony cercherà automaticamente le risorse nelle cartelle `web/images/`, `web/js/` e `web/css/`. Ovviamente, se si volesse inserire un file situato in una particolare cartella, è possibile utilizzare come parametro il percorso completo. 
>E non bisogna preoccuparsi di specificare l'attributo `alt` se l'immagine ha un nome esplicito, in quanto ci penserà symfony stesso.
>
>     [php]
>     <?php echo image_tag('test') ?>
>     <?php echo image_tag('test.gif') ?>
>     <?php echo image_tag('/my_images/test.gif') ?>
>      => <img href="/images/test.png" alt="Test" />
>         <img href="/images/test.gif" alt="Test" />
>         <img href="/my_images/test.gif" alt="Test" />
>
>Per fissare la dimensione di un'immagine, è sufficiente utilizzare l'attributo `size`. Esso si aspetta un'altezza e una larghezza in pixel, separate da una `x`.
>
>     [php]
>     <?php echo image_tag('test', 'size=100x20')) ?>
>      => <img href="/images/test.png" alt="Test" width="100" height="20"/>
>
>Se si volesse che l'inclusione della risorsa avvenga all'interno della sezione `</head>` (per fogli di stile e JavaScript) basta utilizzare nei template gli helper `use_stylesheet()` e `use_javascript()`, invece delle rispettive versioni `*_tag()` del layout. 
>Essi aggiungono le risorse alla risposta, e tali risorse vengono incluse prima che la chiusura della sezione `</head>` sia generata e spedita al browser.

### Utilizzare path assoluti

Gli helper dei link e delle risorse per default generano link relativi. Per forzarli assoluti, si deve utilizzare l'opzione `absolute` impostandola a `true`, come mostrato nel listato 9-14.

Listato 9-14 - Link assoluti invece di relativi

    [php]
    <?php echo url_for('article/read?title=Finance_in_France') ?>
     => '/routed/url/to/Finance_in_France'
    <?php echo url_for('article/read?title=Finance_in_France', true) ?>
     => 'http://www.example.com/routed/url/to/Finance_in_France'

    <?php echo link_to('finance', 'article/read?title=Finance_in_France') ?>
     => <a href="/routed/url/to/Finance_in_France">finance</a>
    <?php echo link_to('finance', 'article/read?title=Finance_in_France','absolute=true') ?>
     => <a href=" http://www.example.com/routed/url/to/Finance_in_France">finance</a>

    // Lo stesso funziona per gli asset
    <?php echo image_tag('test', 'absolute=true') ?>
    <?php echo javascript_include_tag('myscript', 'absolute=true') ?>

>**SIDEBAR**
>L'helper Mail
>
> Quotidianamente i robot raccolgono indirizzi e-mail e invadono la rete, e non si può lasciare tranquillamente l'indirizzo della propria applicazione web senza diventare vittima dello spam entro pochi giorni. Per tale motivo symfony mette a disposizione un helper `mail_to()`.
>
>L'helper `mail_to()` accetta due parametri: l'indirizzo e-mail effettivo e la stringa che deve essere visualizzata. Opzioni aggiuntive accettano un parametro `encode` per stampare codice non leggibile dai robot ma comprensibile dal browser.
>
>     [php]
>     <?php echo mail_to('myaddress@mydomain.com', 'contact') ?>
>      => <a href="mailto:myaddress@mydomain.com'>contact</a>
>     <?php echo mail_to('myaddress@mydomain.com', 'contact', 'encode=true') ?>
>      => <a href="&#109;&#x61;... &#111;&#x6d;">&#x63;&#x74;... e&#115;&#x73;</a>
>
>Tali messaggi e-mail sono composti da caratteri trasformati da un encoder decimale/esadecimale casuale. Questo trucco ferma la maggior parte degli spambot attuali, ma bisogna comunque porre attenzione al fatto che essi evolvono rapidamente.

Configurazione del routing
--------------------------

Il sistema di routing si preoccupa di eseguire due cose:

  * Interpreta l'URL esterno di una richiesta e lo trasforma in un URI interno per capire quale modulo/azione chiamare e i suoi parametri.
  * Formatta gli URI interni usati nei link in URL esterni (se si usano gli helper).

La conversione avviene sulla base di una serie di regole di routing. Tali regole sono memorizzate nel file di configurazione `routing.yml` dentro la cartella `config/` dell'applicazione. Il listato 9-15 mostra le regole di routing di default, incluse in ogni progetto symfony.

Listato 9-15 - Regole di routing di default, in `frontend/config/routing.yml`

    # regole di default
    homepage:
      url:   /
      param: { module: default, action: index }

    default_symfony:
      url:   /symfony/:action/*
      param: { module: default }

    default_index:
      url:   /:module
      param: { action: index }

    default:
      url:   /:module/:action/*
      

### Regole e schemi

Le regole di routing sono associazioni biiettive tra URI interni e URL esterni. Una regola tipica è composta da:

  * Una label unica, presente per questioni di velocità e leggibilità, e può essere usata dagli helper dei link
  * Uno schema a cui corrispondere (chiave `url`)
  * Un array di parametri di richiesta (chiave `param`)

Gli schemi possono contenere caratteri jolly (rappresentati da un asterisco, `*`), anche con nomi (che cominciano con i due punti, `:`). Una coincidenza con un carattere jolly con nome diventa il valore di un parametro di richiesta. Ad esempio, la regola `default` definita nel listato 9-15 corrisponde a ogni URL tipo `/foo/bar` e imposta il parametro `module` a `foo` e il parametro `action` a `bar`. Nella regola `default_symfony`, `symfony` è una parola chiave e `action` un carattere jolly con nome.

>**NOTE**
> I caratteri jolly possono essere separati da una barra o da un punto, quindi è possibile scrivere uno schema come questo:
>
>    my_rule:
>      url:   /foo/:bar.:format
>      param: { module: mymodule, action: myaction }
>
>In questo modo, un URL esterno come 'foo/12.xml' corrisponderà a `my_rule` ed eseguirà `mymodule/myaction` con due parametri: `$bar=12` e `$format=xml`. Si può aggiungere più separatori cambiandi il parametro `segment_separators` nella configurazione del factory `sfPatternRouting` (si veda il capitolo 19).

Il sistema di routing analizza il file `routing.yml` dall'inizio alla fine e si ferma alla prima corrispondenza trovata. Per tale motivo si dovrebbero aggiungere le proprie regole all'inizio, prima di quelle predefinite. Ad esempio, l'URL `/foo/123` corrisponde a entrambe le regole definite nel listato 9-16, ma symfony testa prima `my_rule:` e, dato che questa corrisponde, non prova nemmeno ad andare avanti. La richiesta viene gestita dall'azione `mymodule/myaction` con il parametro `bar` impostato a `123` (e non dall'azione `foo/123`).

Listato 9-16 - Analisi delle regole procede dall'inizio alla fine

    my_rule:
      url:   /foo/:bar
      param: { module: mymodule, action: myaction }

    # default rules
    default:
      url:   /:module/:action/*

>**NOTE**
>La creazione di una nuova azione non implica solamente che si debba creare anche una corrispondente regola di routing. Lo schema predefinito modulo/azione funziona, per cui si può evitare di pensare al file `routing.yml`. 
>Comunque, qualora si volesse personalizzare gli URL esterni delle azioni, è necessario aggiungere le nuove regole prima di quelle predefinite.

Il listato 9-17 mostra il processo di modifica del formato dell'URL esterno per un'azione `article/read`.

Listato 9-17 - Cambiare il formato dell'URL esterna per un'azione `article/read`

    [php]
    <?php echo url_for('my article', 'article/read?id=123) ?>
     => /article/read/id/123       // Formato predefinito

    // Per cambiare in /article/123 aggiungere una nuova regola all'inizio
    // del file routing.yml
    article_by_id:
      url:   /article/:id
      param: { module: article, action: read }
      
Il problema è che la regola `article_by_id` del listato 9-17 interrompe il routing di default per tutte le altre azioni del modulo `article`. 
Infatti, un URL tipo `article/delete` corrisponderà anch'essa a questa regola, invece che a quella predefinita, e chiamerà l'azione `read` con il parametro `id` con valore `delete`, invece dell'azione `delete`. Per evitare ciò, si deve aggiungere un vincolo in modo che la regola `article_by_id` coincida solo con URL in cui il carattere jolly `id` sia un intero.

### Vincoli di schema

Quando un URL può corrispondere a più regole, si devono raffinare le regole, aggiungendo vincoli o requisiti allo schema. Un requisito è un insieme di espressioni regolari, a cui i caratteri jolly devono corrispondere perché tutta la regola coincida.

Ad esempio, per modificare la regola `article_by_id` in modo che coincida solo con URL che abbiano il parametro `id` intero, bisogna aggiungere una linea come mostrato nel listato 9-18.

Listato 9-18 - Aggiungere un requisito a una regola di routing

    article_by_id:
      url:   /article/:id
      param: { module: article, action: read }
      requirements: { id: \d+ }

In questo modo un URL `article/delete` non può più corrispondere alla regola `article_by_id`, perché la stringa `delete` non soddisfa il requisito. Perciò il sistema di routing continuerà a cercare una regola adatta e troverà così la `default`.

>**SIDEBAR**
>Permalink
>
>Una buona linea guida per la sicurezza del routing è quella di nascondere le chiavi primarie il più possibile e sostituirle con stringhe significative.
>E se si volesse mostrare gli articoli tramite il titolo invece che l'ID? Implicherebbe URL esterni nel seguente formato:
>
>     http://www.example.com/article/Finance_in_France
>
>Per fare ciò, bisogna creare una nuova azione `permalink`, che utilizzerà un parametro `slug` invece di un `id`, e aggiungere una nuova regola di routing:
>
>     article_by_id:
>       url:          /article/:id
>       param:        { module: article, action: read }
>       requirements: { id: \d+ }
>
>     article_by_slug:
>       url:          /article/:slug
>       param:        { module: article, action: permalink }
>
>L'azione `permalink` ha bisogno di determinare l'articolo richiesto dal titolo, per cui il modello associato deve fornire un metodo appropriato:
>
>     [php]
>     public function executePermalink($reques)
>     {
>       $article = ArticlePeer::retrieveBySlug($request->getParameter('slug');
>       $this->forward404Unless($article);  // Mostra un 404 se non trova alcun articolo corrispondente a slug
>       $this->article = $article;          // Passa l'oggetto al template
>     }
>
>Si deve anche sostituire i link all'azione `read` nei template con quelli alla `permalink`, per abilitare la formattazione degli URI interni.
>
>     [php]
>     // Sostituire
>     <?php echo link_to('my article', 'article/read?id='.$article->getId()) ?>
>
>     // con
>     <?php echo link_to('my article', 'article/permalink?slug='.$article->getSlug()) ?>
>Grazie alla linea `requirements`, un URL esterno come `/article/Finance_in_France` corrisponderà alla regola `article_by_slug`, anche se la regola `article_by_id` appare prima.
>
>Da notare che dato che gli articoli verranno recuperati tramite slug, si dovrà aggiungere un indice alla colonna `slug` della tabella `Article` per ottimizzare le performance del database.

### Impostare valori predefiniti

È possibile assegnare ai parametri dei valori predefiniti, in modo che la regola funzioni anche se il parametro non è definito, impostando tali valori nell'array `param:`.

Ad esempio, la regola `article_by_id` non coincide se il parametro `id` non è definito. Lo si può forzare come mostrato nel listato 9-19.

Listato 9-19 - Impostare valori di default per wildcard

    article_by_id:
      url:          /article/:id
      param:        { module: article, action: read, id: 1 }

Nel listato 9-20, il parametro `display` assume il valore `true` anche se non è presente nell'URL.

Listato 9-20 - Impostare un valore di default per un parametro di rihiesta

    article_by_id:
      url:          /article/:id
      param:        { module: article, action: read, id: 1, display: true }

Se si guarda attentamente, si può notare che anche `article` e `read` sono valori di default per le variabili `module` e `action` non presenti nello schema.

>**TIP**
>Si Può definire un parametro predefinito per tutte le regole di routing chiamando il metodo `sfRouting::setDefaultParameter()`. Ad esempio, se si volesse che tutti gli URL abbiano per default un parametro `theme` impostato a `default` come parametro predefinito, basta aggiungere la linea `$this->context->getRouting()->setDefaultParameter('theme', 'default');` a uno dei filtri globali.

### Velocizzare il routing utilizzando i nomi delle regole

Gli helper dei link accettano un'etichetta invece di una coppia modulo/azione se tale etichetta è preceduta dalla chiocciola (@), come mostrato nel listato 9-21.

Listato 9-21 - Utilizzare label invece di modulo/azione

    [php]
    <?php echo link_to('my article', 'article/read?id='.$article->getId()) ?>

    // può anche essere scritto
    <?php echo link_to('my article', '@article_by_id?id='.$article->getId()) ?>

Ci sono pro e contro nella scelta di tal metodo. I vantaggi sono i seguenti:

  * La formattazione di URI interne avviene più velocemente, in quanto symfony non dovrà cercare tutte le regole per trovare quella che corrisponde al link. In pagine in cui il numero di link è elevato, sarà possibile notare la differenza di velocità utilizzando label al posto di coppie modulo/azione.
  * Usare le label aiuta ad astrarre la logica dietro un'azione. Se si decidesse di cambiare il nome dell'azione ma non l'URL associata, infatti, sarà sufficiente una piccola modifica del file `routing.yml`. Tutte le chiamate `link_to()` continueranno a funzionare senza ulteriori cambiamenti.
  * La logica della chiamata è più evidente se si utilizzasse una label. Anche se i moduli e azioni possiedono nomi espliciti, spesso è più evidente richiamare `@display_article_by_slug` di `article/display`.
  * Si sa esattamente quali azioni sono abilitate, leggendo il file routing.yml

D'altra parte, uno svantaggio è che aggiungendo nuovi link è meno intuitivo, dato che si deve sempre controllare il file `routing.yml` per controllare il nome della label.
In un progetto di grandi dimensioni si avranno sicuramente un gran numero di regole di routing e risulterà leggermente complesso mantenerle.
In quest'ultimo caso si dovrebbe pacchettizzare l'applicazione in diversi plugin, ognuno con limitato e preciso set di funzionalità.

La scelta di quale metodo utilizzare dipende dal progetto, ma è stato constatato che alla lunga la miglior scelta è quella di utilizzare delle etichette.

>**TIP**
>Se si volesse controllare nel browser quale regola di routing è stata utilizzata per una data richiesta, è sufficiente controllare la sezione "logs" della web debug toolbar e cercare la riga "matched route XXX".
>Maggiori informazioni riguardanti la modalità web debug si possono trovare nel capitolo 16.

### Creare regole senza routing.yml

Come per la maggior parte dei file di configurazione, `routing.yml` è la soluzione per definire regole di routing, ma non l'unica. È possibile definire regole scritte in PHP, sia nel file `config.php` dell'applicazione che nello script del front controller, ma prima della chiamata a `dispatch()`, perché tale metodo determinerà l'azione da eseguire secondo le regole di routing correnti. Definire regole in PHP permette di creare regole dinamiche, dipendenti dalla configurazione o dai parametri.

L'oggetto che gestisce le regole di routing è il factory `sfPatternRouting`. Essa è disponibile in qualsiasi punto dell'applicazione tramite `sfRouting::getInstance()`. Il suo metodo `prependRoute()` aggiunge una nuova regola prima di tutte quelle definite in `routing.yml`. Si aspetta quattro parametri, che sono gli stessi per le regole di routing: etichette, schemi, array associativo di valori di default e array associativo per i requisiti. Ad esempio, la definizione delle regole del listato 9-18 è equivalente al codice PHP del listato 9-24.

>**NOTE**
>La classe di routing è configurabile nel file di configurazione `factories.yml` (per cambiare la classe di routing di default si consulti il capitolo 17). Questo capitolo illustra la classe `sfPatternRouting`, che è la classe di routing predefinita.

Listato 9-24 - Definire una regola in PHP

    [php]
    sfContext::getInstance()->getRouting()->prependRoute(
      'article_by_id',                                  // Nome rotta
      '/article/:id',                                   // Schema rotta
      array('module' => 'article', 'action' => 'read'), // Valori di default
      array('id' => '\d+'),                             // Requisiti
    );

Il costruttore della classe `sfRoute` richiede tre parametri: uno schema, un array associativo di valori predefiniti e un altro array associativo per i requisiti.
La classe `sfPatternRouting` possiede altri metodi per la gestione manuale del routing: `clearRoutes()`, `hasRoutes()` e così via. 
Per maggiori informazioni in merito puoi consultare le [API](http://www.symfony-project.org/api/1_4/).

>**TIP**
>Una volta compresi a fondo i concetti presentati in questa guida, si può aumentare la comprensione del framework consultando le API o, ancora meglio, i sorgenti di symfony. Non tutti i parametri e i trucchi di symfony possono essere spiegati in questa guida. In ogni caso la documentazione online è illimitata.

-

>**NOTE**
>La classe di routing è configurabile attraverso il file di configurazione `factories.yml` (per modificare la classe di routing predefinita si consulti il capitolo 17). Questo capitolo mostra la classe `sfPatternRouting`, che è la classe predefinita.

Gestire le rotte nelle azioni
-----------------------------

Se si avesse bisogno di avere informazioni sulla rotta corrente, ad esempio in preparazione di un futuro link "Torna alla pagina XXX", si dovrebbe usare i metodi dell'oggetto `sfPatternRouting`. 
Gli URI restituiti dal metodo `getCurrentInternalUri()` possono essere utilizzati in una chiamata `link_to()`, come mostrato nel listato 9-25.

Listato 9-25 - Utilizzare `sfRouting` per avere informazioni sulla route corrente

    [php]
    // Se serve un URL come
    http://myapp.example.com/article/21

    $routing = sfContext::getInstance()->getRouting();

    // Usare il codice seguente nell'azione in article/read
    $uri = $routing->getCurrentInternalUri();
     => article/read?id=21

    $uri = $routing->getCurrentInternalUri(true);
     => @article_by_id?id=21

    $rule = $routing->getCurrentRouteName();
     => article_by_id

    // Se servono semplicemente i nomi del modulo/azione corrente,
    // si ricordi che essi sono parametri di richiesta effettivi
    $module = $request->getParameter('module');
    $action = $request->getParameter('action');

Se si avesse bisogno di trasformare un URI in un URL esterno in un'azione, come avviene con `url_for()` nei template, bisogna usare il metodo `genUrl()` dell'oggetto sfController, come mostrato nel listato 9-26.

Listato 9-26 - Utilizzare `sfController` per trasformare un URI interno

    [php]
    $uri = 'article/read?id=21';

    $url = $this->getController()->genUrl($uri);
     => /article/21

    $url = $this->getController()->genUrl($uri, true);
    => http://myapp.example.com/article/21

Sommario
--------

Il routing è un meccanismo bidirezionale pensato per permettere la formattazione di URL esterni in modo che siano più comprensibili e intuitive. 
La riscrittura degli URL è necessaria per permettere l'omissione del nome del front controller nell'URL di una delle applicazioni di ogni progetto. 
Si deve utilizzare gli helper dei link ogni qualvolta si avesse bisogno di stampare un URL in un template, se si vuole che il sistema di routing funzioni in entrambe le direzioni. 
Il file `routing.yml` configura le regole del sistema di routing e utilizza un ordine di precedenza e requisiti. 
Il file `settings.yml` contiene impostazioni addizionali riguardanti la presenza del nome del front controller e possibili suffissi in URL esterni.
