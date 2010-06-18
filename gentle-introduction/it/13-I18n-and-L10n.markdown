Capitolo 13 - i18n e l10n
=========================

Se qualcuno ha mai sviluppato un'applicazione internazionale, sicuramente sa quale
incubo possa essere avere a che fare con ogni aspetto della traduzione dei testi,
standard locali, contenuti localizzati. Fortunatamente, symfony automatizza in modo
nativo tutti gli aspetti dell'internazionalizzazione.

Essendo una parola lunga, spesso gli sviluppatori fanno riferimento all'internazionalizzazione
come i18n (contando le lettere della parola inglese). Analogamente, la localizzazione è
abbreviata in l10n. Questi due argomenti coprono diversi aspetti delle applicazioni web
multilingua.

Un'applicazione internazionalizzata contiene diverse versioni dello stesso contenuto, in
varie lingue o formati. Ad esempio, un'interfaccia web per la posta elettronica può
offrire lo stesso servizio in diverse lingue: solo l'interfaccia cambia.

Un'applicazione localizzata contiene informazioni distinte, in base al paese dal quale
viene acceduta. Si pensi ai contenuti di un portale di notizie: se vi si accede dagli
Stati Uniti, mostrerà le ultime informazioni dagli Stati Uniti, ma se vi si accede dalla
Francia, le informazioni saranno relative alla Francia. Quindi, un'applicazione l10n non
fornisce solo traduzione dei contenuti, ma anche i contenuti stessi possono essere
diversi da una versione localizzata all'altra.

Tutto considerato, avere a che fare con i18n e l10n vuol dire che l'applicazione può
occuparsi dei seguenti aspetti:

  * Traduzione di testi (interfaccia, elementi, contenuti)
  * Misure e formati (date, standard, numeri, ecc.)
  * Contenuti localizzati (più versioni di un dato oggetto, secondo il paese)

Questo capitolo copre i modi in cui symfony tratta questi elementi e come lo si può
usare per sviluppare applicazioni internazionalizzate e localizzate.

Cultura dell'utente
-------------------

Tutte le caratteristiche i18n predefinite di symfony sono basate su un parametro nella
sessione, chiamato cultura. La cultura è la combinazione del paese e della lingua
dell'utente e determina il modo in cui testi e informazioni dipendenti dalla cultura
sono visualizzati. Poiché è serializzata nella sessione, la cultura è persistente tra
le pagine.

### Impostare la cultura predefinita

Per impostazione predefinita, la cultura dei nuovi utenti è `default_culture`. Si può
modificare questa impostazione nel file di configurazione `settings.yml`, come mostrato
nel Listato 13-1.

Listato 13-1 - Impostare la cultura predefinita, in `frontend/config/settings.yml`

    all:
      .settings:
        default_culture: it_IT

>**NOTE**
>Durante lo sviluppo, ci si potrebbe stupire perché la modifica della cultura in
>`settings.yml` non cambia la cultura nel browser. Questo perché la sessione ha già
>una cultura impostata. Se si vuole che l'applicazione usi la nuova cultura, occorre
>cancellare i cookie di dominio o riavviare il browser.

Mantenere sia la lingua che il paese è necessario, perché si potrebbero avere diverse
traduzioni italiane per utenti dell'Italia o della Svizzera, e diversi contenuti
spagnoli per gli utenti dalla Spagna o dal Messico. La lingua è codificata in due
caratteri minuscoli, secondo lo standard ISO 639-1 (ad esempio, `en` per inglese). Il
paese è codificato in due caratteri maiuscoli, secondo lo standard ISO 3166-1 (ad esempio,
`GB` per la Gran Bretagna).

### Cambiare la cultura di un utente

La cultura di un utente può essere cambiata durante la sessione, ad esempio quando un
utente decide di passare dalla versione inglese a quella italiana dell'applicazione,
oppure quando un utente entra nell'applicazione e usa la lingua impostata nelle sue
preferenze. È per questo che la classe `sfUser` offre dei metodi getter e setter per la
cultura. Il Listato 13-2 mostra come usare questi metodi in un'azione.

Listato 13-2 - Impostare e recuperare la cultura in un'azione

    [php]
    // Setter
    $this->getUser()->setCulture('en_US');

    // Getter
    $culture = $this->getUser()->getCulture();
     => en_US

>**SIDEBAR**
>Cultura nell'URL
>
>Quando si usano le caratteristiche di localizzazione e internazionalizzazione di
>symfony, le pagine tendono ad avere diverse versioni per uno stesso URL: dipende tutto
>dalla sessione. Questo previene dal mettere in cache o indicizzare le pagine in un
>motore di ricerca.
>
>Una possibile soluzione consiste nel far apparire la cultura in ogni URL, in modo che
>le pagine tradotte possano essere viste con diversi URL dall'esterno. Per poterlo fare,
>aggiungere il token `:sf_culture` in ogni regola all'interno di `routing.yml`:
>
>     page:
>       url: /:sf_culture/:page
>       param: ...
>       requirements: { sf_culture: (?:fr|en|de) }
>
>     article:
>       url: /:sf_culture/:year/:month/:day/:slug
>       param: ...
>       requirements: { sf_culture: (?:fr|en|de) }
>
>Per evitare di dover impostare il parametro `sf_culture` in ogni `link_to()`, symfony
>aggiunge automaticamente la cultura ai parametri predefiniti del routing. Funziona anche
>perché symfony cambia automaticamente la cultura, se trova il parametro `sf_culture`
>nell'URL.

### Determinare automaticamente la cultura

In molte applicazioni, la cultura è definita nella prima richiesta, basandosi sulle
preferenze del browser. Gli utenti possono definire una lista di lingue accettate nel
loro browser e questi dati sono inviati al server a ogni richiesta, nell'header HTTP
`Accept-Language`. Possono essere recuperati tramite l'oggetto `sfWebRequest`. Ad esempio,
per ottenere la lista delle lingue preferite in un'azione, usare questo:

    [php]
    $languages = $request->getLanguages();

L'header HTTP è una stringa, ma symfony lo converte automaticamente in un array. Quindi,
la lingua preferita è accessibile nel precedente esempio in `$languages[0]`.

Può essere utile impostare automaticamente la cultura dell'utente alla sua lingua
preferita nella pagina iniziale di un sito oppure in un filtro per tutte le pagine.
Ma poiché il sito probabilmente supporterà un numero limitato di lingue, è meglio usare
il metodo `getPreferredCulture()`. Esso restituisce la lingua migliore, confrontando
le lingue preferite e quelle supportate:

    [php]
    $language = $request->getPreferredCulture(array('en', 'it')); // il sito è disponibile in inglese e in italiano

Se non c'è corrispondenza, il metodo restituisce la prima lingua supportata (`en`,
nell'esempio precedente).

>**CAUTION**
>L'header HTTP `Accept-Language` non è un'informazione molto attendibile, perché raramente
>gli utenti la modificano nel browser. La maggior parte delle volte, la lingua preferita
>del browser è quella dell'interfaccia e i browser non sono disponibili in tutte le
>lingue. Se si decide di impostare automaticamente la cultura in base alla lingua
>preferita del browser, assicurarsi di fornire un modo all'utente di scegliere una lingua
>alternativa.

Standard e formati
------------------

Le parti interne di un'applicazione web non si curano delle particolarità culturali.
I database, ad esempio, usano standard internazionali per memorizzare date, cifre, ecc.
Ma quando i dati sono inviati o recuperati da utenti, occorre eseguire una conversione.
Gli utenti non capirebbero i timestamp e preferirebbero dichiarare la propria madrelingua
come "italiano" piuttosto che "Italian". Quindi, occorre un'assistenza per eseguire
automaticamente la conversione, basata sulla cultura dell'utente.

### Mostrare i dati nella cultura dell'utente

Una volta definita la cultura, gli helper che dipendono da essa avranno automaticamente
l'output adeguato. Ad esempio, l'helper `format_number()` mostra automaticamente un
numero nel formato familiare all'utente, secondo la sua cultura, come mostrato nel
Listato 13-3.

Listato 13-3 - Mostrare un numero per la cultura dell'utente

    [php]
    <?php use_helper('Number') ?>

    <?php $sf_user->setCulture('en_US') ?>
    <?php echo format_number(12000.10) ?>
     => '12,000.10'

    <?php $sf_user->setCulture('it_IT') ?>
    <?php echo format_number(12000.10) ?>
     => '12000,10'

Non occorre passare esplicitamente la cultura agli helper. Essi la cercano nell'oggetto
sessione corrente. Il Listato 13-4 elenca gli helper che tengono conto della cultura
dell'utente nel loro output.

Listato 13-4 - Helper dipendenti dalla cultura

    [php]
    <?php use_helper('Date') ?>

    <?php echo format_date(time()) ?>
     => '9/14/10'

    <?php echo format_datetime(time()) ?>
     => 'September 14, 2010 6:11:07 PM CEST'

    <?php use_helper('Number') ?>

    <?php echo format_number(12000.10) ?>
     => '12,000.10'

    <?php echo format_currency(1350, 'USD') ?>
     => '$1,350.00'

    <?php use_helper('I18N') ?>

    <?php echo format_country('US') ?>
     => 'United States'

    <?php format_language('en') ?>
     => 'English'

    <?php use_helper('Form') ?>

    <?php echo input_date_tag('birth_date', mktime(0, 0, 0, 9, 14, 2010)) ?>
     => input type="text" name="birth_date" id="birth_date" value="9/14/10" size="11" />

    <?php echo select_country_tag('country', 'US') ?>
     => <select name="country" id="country"><option value="AF">Afghanistan</option>
          ...
          <option value="GB">United Kingdom</option>
          <option value="US" selected="selected">United States</option>
          <option value="UM">United States Minor Outlying Islands</option>
          <option value="UY">Uruguay</option>
          ...
        </select>

Gli helper delle date possono accettare un parametro addizionale `format`, per forzare
una visualizzazione indipendente dalla cultura, ma non andrebbe fatto in
un'applicazione internazionalizzata.

### Ottenere dati da un input localizzato

Se occorre mostrare dati nella cultura dell'utente, oppure recuperare dei dati, si
dovrebbe indurre, per quanto possibile, gli utenti a inserirli già in formato
internazionalizzato. Questo approccio risparmierà dal tentare di capire in che modo
convertire i dati nei vari formati. Ad esempio, chi potrebbe inserire un valore
monetario con la virgola come separatore, in un input?

Si può forzare il formato di inserimento o nascondendo i veri dati (come in
`select_country_tag()`) o separando i componenti di dati complessi in diversi
input semplici.

Per le date, tuttavia, spesso non è possibile. Gli utenti sono abituati a inserire le
date nel loro formato culturale e occorre convertire queste date in un formato interno
(e internazionale). Qui si applica la classe `sfI18N`. Il Listato 13-5 mostra come usare
questa classe.

Listato 13-5 - Ottenere una data da un formato localizzato in un'azione

    [php]
    $date= $request->getParameter('birth_date');
    $user_culture = $this->getUser()->getCulture();

    // Ottenere un timestamp
    $timestamp = $this->getContext()->getI18N()->getTimestampForCulture($date, $user_culture);

    // Ottenere una data strutturata
    list($d, $m, $y) = $this->getContext()->getI18N()->getDateForCulture($date, $user_culture);

Informazione testuale nel database
----------------------------------

Un'applicazione localizzata offre diversi contenuti a seconda della cultura dell'utente.
Ad esempio, un negozio online può offrire prodotti in tutto il mondo allo stesso prezzo,
ma con una descrizione personalizzata per ogni paese. Questo vuol dire che il database
deve poter memorizzare diverse versioni di un dato pezzo di dato e, per questo, occorre
definire lo schema in un modo particolare e usare la cultura ogni volta che si
manipolano oggetti localizzati.

### Creare uno schema localizzato

Ogni tabella che contiene dati localizzati deve essere separata in due parti: una tabella
senza alcuna colonna i18n e un'altra con le sole colonne i18n. Le due tabelle sono legate
da una relazione uno-a-uno. Questa configurazione consente di aggiungere altre lingue
senza dover cambiare il modello. Consideriamo un esempio, usando una tabella `Product`.

Innanzitutto, creare le tabelle nel file `schema.yml`, come mostrato nel Listato 13-6.

Listato 13-6 - Schema di esempio per dati i18n Data con Propel, in `config/schema.yml`

    my_connection:
      my_product:
        _attributes: { phpName: Product, isI18N: true, i18nTable: my_product_i18n }
        id:          { type: integer, required: true, primaryKey: true, autoincrement: true }
        price:       { type: float }

      my_product_i18n:
        _attributes: { phpName: ProductI18n }
        id:          { type: integer, required: true, primaryKey: true, foreignTable: my_product, foreignReference: id }
        culture:     { isCulture: true, type: varchar, size: 7, required: true, primaryKey: true }
        name:        { type: varchar, size: 50 }

Si notino gli attributi `isI18N` e `i18nTable` nella prima tabella e la colonna speciale
`culture` nella seconda. Tutti questi sono caratteristiche specifiche di Propel.

Listato 13-7 - Schema di esempio per dati i18n Data con Doctrine, in `config/doctrine/schema.yml`

    Product:
      actAs:
        I18n:
          fields: [name]
      columns:
        price: { type: float }
        name: { type: string(50) }


Gli automatismi di symfony consentono di rendere tutto più veloce da scrivere. Se la
tabella che contiene i dati internazionalizzati ha lo stesso nome della tabella
principale con `_i18n` come suffisso e se sono collegate con una colonna `id` in entrambe,
si possono omettere le colonne `id` e `culture` nella tabella `_i18n` e gli attributi
specifici nella tabella principale: symfony li inferirà. Questo vuol dire che symfony
vedrà lo schema nel Listato 13-8 come quello nel Listato 13-6.

Listato 13-8 - Schema di esempio per dati i18n, versione breve, in `config/schema.yml`

    my_connection:
      my_product:
        _attributes: { phpName: Product }
        id:
        price:       float
      my_product_i18n:
        _attributes: { phpName: ProductI18n }
        name:        varchar(50)

### Usare gli oggetti i18m generati

Una volta costruiti i corrispondente oggetti del modello (non dimenticare di richiamare
il task `propel:build --model` dopo ogni modifica a `schema.yml`), si può usare la classe
`Product` con supporto i18n, come se fosse una sola tabella, come mostrato nel Listato
13-9.

Listato 13-9 - Trattare gli oggetti i18n

    [php]
    $product = ProductPeer::retrieveByPk(1);
    $product->setName('Nom du produit'); // la cultura dell'utente è quella predefinita
    $product->save();

    echo $product->getName();
     => 'Nom du produit'

    $product->setName('Product name', 'en'); // cambiare il valore per la cultura 'en'
    $product->save();

    echo $product->getName('en');
     => 'Product name'

Come per le query con oggetti peer, si possono restringere i risultati a oggetti che
abbiamo la traduzione per la cultura corrente, usando il metodo `doSelectWithI18n`,
invece del solito `doSelect`, come mostrato nel Listato 13-10. Inoltre, esso creerà gli
oggetti i18n correlati contemporaneamente a quelli normali, riducendo così il numero di
query necessarie per ottenere tutti i contenuti (fare riferimento al capitolo 18 per
maggiori informazioni sugli impatti positivi di questo metodo sulle prestazioni).

Listato 13-10 - Recuperare oggetti con `Criteria` i18n

    [php]
    $c = new Criteria();
    $c->add(ProductPeer::PRICE, 100, Criteria::LESS_THAN);
    $products = ProductPeer::doSelectWithI18n($c, $culture);
    // Il parametro $culture è opzionale
    // Se non passato, è usata la cultura dell'utente

Quindi, fondamentalmente, non si ha mai bisogno di trattare direttamente gli oggetti i18n,
ma invece basta passare la cultura al modello (o lasciare che la ricavi) ogni volta che
si esegue una query con gli oggetti normali.

Traduzione dell'interfaccia
---------------------------

L'interfaccia utente ha bisogno di essere adattata alle applicazioni i18n. I template
devono poter mostrare etichette, messaggi e navigazione in diverse lingue, ma con la
stessa presentazione. Symfony raccomanda di costruire i template nella lingua
predefinita e poi fornire una traduzione per le frasi usate nei template, in un file
dizionario. In questo modo, non occorre modificare i template ogni volta che si modifica,
aggiunge o rimuove una traduzione.

### Configurare la traduzione

I template non sono tradotti in modo predefinito, il che vuol dire che occorre attivare
la traduzione dei template nel file `settings.yml` prima di tutto, come mostrato nel
Listato 13-11.

Listato 13-11 - Attivare l'interfaccia di traduzione, in `frontend/config/settings.yml`

    all:
      .settings:
        i18n: true

### Usare l'helper della traduzione

Supponiamo di voler creare un sito in inglese e italiano, con l'inglese come lingua
predefinita. Prima ancora di pensare di avere il sito tradotto, probabilmente si
scriverà nei template qualcosa come l'esempio mostrato nel Listato 13-12.

Listato 13-12 - Un template mono-lingua

    [php]
    Welcome to our website. Today's date is <?php echo format_date(date()) ?>

Per fare in modo che symfony possa tradurre le frasi di un template, queste devono
essere identificate come testo da tradurre. Questo è lo scopo dell'helper `__()` (due
trattini bassi), un membro del gruppo di helper I18N. Quindi, tutti i template hanno
bisogno di racchiudere le frasi da tradurre in chiamate a questa funzione. Il Listato
13-11, ad esempio, può essere modificato come nel Listato 13-13 (come si vedrà nella
sezione successiva "Gestire esigenze complesse di traduzione", c'è un modo migliore per
richiamare l'helper di traduzione in questo esempio).

Listato 13-13 - Un template pronto per il multilingua

    [php]
    <?php use_helper('I18N') ?>

    <?php echo __('Welcome to our website.') ?>
    <?php echo __("Today's date is ") ?>
    <?php echo format_date(date()) ?>

>**TIP**
>Se l'applicazione usa il gruppo di helper I18N per ogni pagina, probabilmente è una buona
>idea includerlo nell'impostazione `standard_helpers` in `settings.yml`, in modo da
>evitare di ripetere `use_helper('I18N')` per ogni template.

### Usare i file dizionario

Ogni volta che la funzione `__()` è richiamata, symfony cerca una traduzione del suo
parametro nel dizionario della cultura corrente dell'utente. Se trova una frase
corrispondente, invia e mostra la traduzione nella risposta. Quindi, la traduzione
dell'interfaccia utente si basa sui file dizionario.

I file dizionario sono scritti in XLIFF (XML Localization Interchange File Format), con
un nome che segue lo schema `messages.[codice lingua].xml` e memorizzati nella cartella
`i18n/` dell'applicazione.

XLIFF è un formato standard basato su XML. Essendo ben noto, si possono usare strumenti
di traduzione di terze parti per tradurre tutti i testi del sito. Le aziende che si
occupano di traduzione gestiscono normalmente questi file e traducono interi siti,
creando una nuova traduzione XLIFF.

>**TIP**
>Oltre allo standard XLIFF, symfony supporta anche diversi altri sistemi di traduzioni
>per dizionari: gettext, MySQL e SQLite. Si faccia riferimento alla documentazione delle
>API per maggiori informazioni sulla configurazione di questi sistemi.

Il Listato 13-14 mostra un esempio di sintassi XLIFF col file `messages.it.xml`
necessario per tradurre il Listato 13-14 in italiano.

Listato 13-14 - Un dizionario XLIFF, in `frontend/i18n/messages.it.xml`

    [xml]
    <?xml version="1.0" encoding="UTF-8" ?>
    <!DOCTYPE xliff PUBLIC "-//XLIFF//DTD XLIFF//EN" "http://www.oasis-open.org/committees/xliff/documents/xliff.dtd">
    <xliff version="1.0">
      <file original="global" source-language="en_US" datatype="plaintext">
        <body>
          <trans-unit id="1">
            <source>Welcome to our website.</source>
            <target>Bevenuto nel nostro sito.</target>
          </trans-unit>
          <trans-unit id="2">
            <source>Today's date is </source>
            <target>La data di oggi è </target>
          </trans-unit>
        </body>
      </file>
    </xliff>

L'attributo `source-language` deve sempre contenere il codice completo ISO della cultura
predefinita. Ogni traduzione è scritta in un tag `trans-unit` con un attributo univoco
`id`.

Con la cultura predefinita (`en_US`), le frasi non sono tradotte e sono mostrati i
parametri delle chiamate `__()`, così come sono. Il risultato del Listato 13-12 è allora
simile al Listato 13-11. Se tuttavia si cambia cultura in `it_IT` o `it_CH`, sono invece
mostrate le traduzioni del file `messages.it.xml` e il risultato appare come nel Listato
13-15.

Listato 13-15 - Un template tradotto

    [php]
    Benvenuto nel nostro sito. La data di oggi è
    <?php echo format_date(date()) ?>

Se occorrono ulteriori traduzioni, basta aggiungere un file di traduzione `messages.XX.xml`
nella stessa cartella.

>**TIP**
>Poiché la ricerca dei file dizionario, la sua analisi e l'estrazione della traduzione
>corretta di una data stringa potrebbe richiedere un po' di tempo, symfony usa una cache
>interna per accelerare il processo. Per impostazione predefinita, questa cache usa il
>filesystem. Si può configurare la modalità di funzionamento della cache i18n (ad esempio,
>per condividere la cache tra server diversi) in `factories.yml` (vedere Capitolo 19).

### Gestire i dizionari

Se i file `messages.XX.xml` diventano troppo lunghi da leggere, possono essere divisi
in diversi file, con nomi opportuni. Ad esempio, si può dividere il file `messages.it.xml`
in questi tre file, nella cartella `i18n/` dell'applicazione:

  * `navigation.it.xml`
  * `terms_of_service.it.xml`
  * `search.it.xml`

Si noti che, poiché la traduzione non si trova nel file predefinito `messages.XX.xml`,
occorre dichiarare quel dizionario va usato, a ogni chiamata all'helper `__()`, usando il
suo terzo parametro. Ad esempio, per mostrare una stringa tradotta nel dizionario
`navigation.it.xml`, va scritto:

    [php]
    <?php echo __('Welcome to our website', null, 'navigation') ?>

Un altro modo per organizzare i dizionari è quello di dividerli per modulo. Invece di
scrivere un singolo file `messages.XX.xml` per l'intera applicazione, se ne può scrivere
uno in ogni cartella `modules/[nome_modulo]/i18n/`. Questo rende i moduli più
indipendenti dall'applicazione, il che è necessario se si vuole riutilizzarli, come in
un plugin (vedere Capitolo 17).

Poiché l'aggiornamento manuale dei dizionari è spesso soggetto a errori, symfony fornisce
un task per automatizzare il processo. Il task `i18n:extract` analizza un'applicazione
symfony ed estrae tutte le stringhe che necessitano di traduzione. Accetta come parametri
un'applicazione e una cultura.

    $ php symfony i18n:extract frontend en

Per impostazione predefinita, il task non modifica i dizionari, ma mostra solo i numeri
di nuove e vecchie stringhe i18n. Per aggiungere le nuove stringhe al dizionario,
si può passare l'opzione `--auto-save`:

    $ php symfony i18n:extract --auto-save frontend en

Si possono anche cancellare le vecchie stringhe automaticamente, passando l'opzione
`--auto-delete`:

    $ php symfony i18n:extract --auto-save --auto-delete frontend en

>**NOTE**
>Il task ha alcune limitazioni. Funziona solo col dizionario predefinito `messages` e per
>i sistemi basati su file (`XLIFF` or `gettext`), salva e cancella solo stringhe nel file
>principale `apps/frontend/i18n/messages.XX.xml`.

### Gestire altri elementi che richiedono traduzione

I seguenti sono altri elementi che potrebbero richiedere traduzione:

  * Immagini, documenti di testo o altri tipi di risorse che potrebbero variare con la
    cultura dell'utente. L'esempio migliore è un pezzo di testo con un carattere
    tipografico particolare, che in realtà è un'immagine. Per questo, si possono creare
    delle sottocartelle con nomi che dipendono da `culture`:
    
        [php]
        <?php echo image_tag($sf_user->getCulture().'/myText.gif') ?>

  * I messaggi di errori di file di validazione dei form passano automaticamente per
    `__()`, quindi basta aggiungere le loro traduzioni a un dizionario per averli
    tradotti.
  * Le pagine predefinite di symfony (pagina non trovata, errore interno del server,
    accesso vietato, ecc.) sono in inglese e devono essere riscritte in una applicazione
    i18n. Probabilmente si vorrà creare un proprio modulo `default` nell'applicazione e
    usare `__()` nei suoi template. Si faccia riferimento al Capitolo 19 per vedere come
    personalizzare queste pagine.

### Gestire esigenze complesse di traduzione

Le traduzioni hanno senso solo se il parametro di `__()` è una frase completa. Tuttavia,
se occorrono delle formattazioni o delle variabili mescolate con parole, si può essere
tentati di dividere le frasi in pezzi, quindi richiamare l'helper su frasi senza senso.
Fortunatamente, l'helper `__()` offre una soluzione basata su token, che aiutano a
mantenere un dizionario sensato, che è più facile da gestire per i traduttori.
Anche la formattazione HTML può essere lasciata nell'helper. Il Listato 13-16 mosta un
esempio.

Listato 13-16 - Traduzione di frasi che contengono codice

    [php]
    // Esempio di base
    Welcome to all the <b>new</b> users.<br />
    There are <?php echo count_logged() ?> persons logged.

    // Modo inappropriato di abilitare la traduzione
    <?php echo __('Welcome to all the') ?>
    <b><?php echo __('new') ?></b>
    <?php echo __('users') ?>.<br />
    <?php echo __('There are') ?>
    <?php echo count_logged() ?>
    <?php echo __('persons logged') ?>

    // Modo appropriato di abilitare la traduzione
    <?php echo __('Welcome to all the <b>new</b> users') ?> <br />
    <?php echo __('There are %1% persons logged', array('%1%' => count_logged())) ?>

In questo esempio, il token è `%1%`, ma può essere qualsiasi cosa, perché la funzione di
sostituzione usata dall'helper di traduzione è `strtr()`.

Uno dei problemi più comuni con la traduzione è l'uso di forme plurali. A seconda del
numero di risultati, il testo cambia, ma non nello stesso modo in ogni lingua. Ad esempio,
l'ultima frase del Listato 13-15 è sbagliata, se `count_logged()` restituisce 0 o 1. Si
potrebbe aggiungere una condizione sul valore restituito dalla funzione e scegliere la
frase giusta da usare, ma richiederebbe molto codice. Inoltre, lingue diverse hanno regole
grammatiche diverse e quindi le regole di declinazione del plurale potrebbero essere
molto complesse. Essendo il problema molto comune, symfony fornisce un helper per
risolverlo, chiamato `format_number_choice()`. Il Listato 13-17 mostra come usare questo
helper.

Listato 13-17 - Traduzione di frasi in base al valore dei parametri

    [php]
    <?php echo format_number_choice(
      '[0]Nobody is logged|[1]There is 1 person logged|(1,+Inf]There are %1% persons logged', array('%1%' => count_logged()), count_logged()) ?>

Il primo parametro rappresenta le molteplici possibilità del testo. Il secondo parametro è
lo schema di sostituzione (come con l'helper `__()`) ed è opzionale. Il terzo parametro è
il numero in base a cui fare il controllo per determinare quale testo usare.

Il messaggio o la stringa scelte sono separati da un carattere di barra verticale (`|`) 
seguito da un array di valori accettabili, secondo la seguente sintassi:

  * `[1,2]`: Accetta valori tra 1 e 2, inclusi
  * `(1,2)`: Accetta valori tra 1 e 2, esclusi 1 e 2
  * `{1,2,3,4}`: Accetta solo i valori elencati
  * `[-Inf,0)`: Accetta valori maggiori o uguali a meno infinito e minori di zero
  * `{n: n % 10 > 1 && n % 10 < 5} pliki`: Accetta numeri come 2, 3, 4, 22, 23, 24 
    (utile per lingue come il polacco o il russo) 

Qualsiasi combinazione non vuota di delimitatori con parentesi tonde e quadre è
accettabile.

Il messaggio deve comparire esplicitamente nel file XLIFF per poter avere la giusta
traduzione. Il Listato 13-18 mostra un esempio.

Listato 13-18 - Dizionario XLIFF per `format_number_choice()`

    ...
    <trans-unit id="3">
      <source>[0]Nobody is logged|[1]There is 1 person logged|(1,+Inf]There are %1% persons logged</source>
      <target>[0]Nessun utente connesso|[1]Un utente connesso|(1,+Inf]Ci sono %1% utenti connessi</target>
    </trans-unit>
    ...

>**SIDEBAR**
>Un accenno ai charset
>
>La gestione dei contenuti internazionalizzati spesso porta a problemi con i charset. Se si
>usa un charset localizzato, occorrerà cambiarlo ogni volta che un utente cambia cultura.
>Inoltre, i template scritti un certo charset potrebbero non visualizzare correttamente
>i caratteri di un altro charset.
>
>Per questo, quando si ha a che fare con più di una cultura, tutti i template devono
>essere salvati in UTF-8 e il layout deve dichiarare tale charset. Non si avranno
>spiacevoli sorprese lavorando sempre con UTF-8 e ci si risparmieranno molti mal di testa.
>
>Le applicazioni symfony si basano su un'impostazione centralizzata del charset, nel file
>`settings.yml`. La modifica di questo parametro cambierà l'header `content-type` di tutte
>le risposte.
>
>     all:
>       .settings:
>         charset: utf-8

### Richiamare l'helper di traduzione fuori da un template

Non tutti il testo mostrato in una pagina deriva da template. Per questo spesso occorre
richiamare l'helper `__()` in altre parti dell'applicazione: azioni, filtri, classi del
modello, ecc. Il Listato 13-19 mostra come richiamare l'helper in un'azione, recuperando
l'istanza corrente dell'oggetti `I18N` attraverso il contesto.

Listato 13-19 - Richiamare `__()` in un'azione

    [php]
    $this->getContext()->getI18N()->__($text, $args, 'messages');

Riepilogo
---------

la gestione dell'internazionalizzazione e della localizzazione nelle applicazioni web è
indolore, se si sa come gestire la cultura dell'utente. Gli helper si occupano
automaticamente di mostrare correttamente i dati formattati e i contenuti localizzati del
database sono visti come se fossero parte di una semplice tabella. Come per l'interfaccia
di traduzione, l'helper `__()` e i dizionari XLIFF assicurano la massima versatilità col
minimo sforzo.
