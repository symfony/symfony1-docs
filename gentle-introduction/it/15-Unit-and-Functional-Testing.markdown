Capitolo 15 - Test funzionali e unitari
=======================================

L'automazione dei test è uno dei più grandi passi in avanti dalla quando si è passati alla programmazione a oggetti
Particolarmente favorevole per sviluppo di applicazioni web, i test possono garantire la qualità di un applicazione anche se i rilasci sono numerosi. Symfony fornisce una varietà di strumenti per facilitare l'automazione dei test, che saranno introdotti in questo capitolo.

Automazione dei test
--------------------

Qualsiasi sviluppatore con esperienza nello sviluppo di applicazioni web è ben consapevole del tempo necessario per fare correttamente dei test. Scrivere test, avviarne l'esecuzione e analizzare i risultati è un lavoro noioso. In più, i requisiti delle applicazioni web tendono a cambiare continuamente, il ché porta a un flusso continuo di rilasci e alla necessità continua di rifattorizzare il codice. In questo contesto, nuovi errori escono fuori facilmente.

Ecco perché i test automatizzati, anche se non obbligatori, fanno parte di un'ambiente di sviluppo di successo. Un insieme di test può garantire che un'applicazione faccia esattamente quello che ci si aspetti che faccia. Anche se le parti interne sono spesso rielaborate, l'automazione dei test previene regressioni accidentali. Inoltre essi, costringono gli sviluppatori a scrivere i test in forma standardizzata, rigida e in grado di essere compresa da un framework di test.

I test automatizzati possono, a volte, sostituire la documentazione di sviluppo poiché  sono in grado di illustrare cosa un'applicazione si propone di fare. Un buon insieme di test mostra che output ci si aspetta dato un insieme di input e questo è il miglior modo per spiegare l'obiettivo di  un metodo.

Il framework symfony applica questi principi anche a se stesso. Le parti interne del framework sono validate con test automatizzati. Tali test, unitari e funzionali, non sono compresi nei pacchetti PEAR, ma si possono reperire sul repository SVN o all'indirizzo [online](http://trac.symfony-project.org/browser/branches/1.4/test).

### Test unitari e funzionali

I test unitari confermano che un componente di codice unitario fornisce l'output corretto per un dato input. Verificano che metodi e funzioni si comportino correttamente in ogni caso. I test unitari lavorano con un caso alla volta, quindi per esempio un singolo metodo più avere molteplici test unitari se si comporta diversamente in alcune situazioni.

I test funzionali non validano una conversione da input-output, ma una caratteristica completa. Per esempio, il sistema di cache può essere validato solo da un test funzionale, poiché è composto da più di uno step. La prima volta che una pagina è richiesta, è resa; la seconda volta, è presa dalla cache. Quindi i test funzionali validano un processo e necessitano di uno scenario per farlo. In symfony bisognerebbe scrivere un test funzionale per tutte le azioni

Per le interazioni più complesse, questi due tipi di test possono risultare inefficaci. Per esempio le interazioni Ajax richiedono un browser web per eseguire JavaScript, quindi per automatizzare questo tipo di test sono necessari strumenti di terze parti. Inoltre, gli effetti visivi possono essere validati solo da un essere umano.

Se si necessita di un approccio completo all'automazione dei test, probabilmente si avrà bisogno di usare in combinazione tutti i metodi messi a disposizione. Come linea guida, ricordarsi di mantenere i test semplici e leggibili.

>**NOTA**
>I test automatici funzionano basandosi sulla comparazione di un risultato con un output atteso. In altre parole valutano le asserzioni (espressioni come `$a == 2`). Il valore di un asserzione è o`vero` o `falso` e stabilisce se il test passa o fallisce. La parola asserzione è normalmente utilizzata quando si fa riferimento a tecniche di testing

### Test-Driven Development

Nella metodologia test-driven development (TDD), i test sono scritti prima del codice. Scrivere i test prima aiuta a concentrarsi sull'attività che una funzione dovrebbe svolgere prima ancora di averla sviluppata. È una buona pratica che anche altre metodologie, come l'Extreme Programming(XP), raccomandano. Inoltre prende in considerazione il fatto innegabile che se non si scrivono i test unitari in primo luogo, non si scriveranno mai.

Per esempio, immaginate di dover sviluppare una funzione che lavora su una stringa di testo (strip). La funzione toglie gli spazi all'inizio e alla fine della stringa, sostituisce caratteri non alfabetici con trattini bassi e trasforma tutte le lettere maiuscole in minuscole. Nel test-driven-development, si dovrebbe prima porre l'attenzione su tutti i casi possibili e fornire degli esempi di input e il risultato atteso per ognuno, come mostrato in tabellla 15-1 

Tabella 15-1 - Una lista di casi di test per una funzione di rimozione di testo

Input                 | Output Atteso
--------------------- | ---------------------
`" foo "`             | `"foo"`
`"foo bar"`           | `"foo_bar"`
`"-)foo:..=bar?"`     | `"__foo____bar_"`
`"FooBar"`            | `"foobar`"
`"Don't foo-bar me!"` | `"don_t_foo_bar_me_"`

Bisognerebbe scrivere i test unitari, eseguirli e vedere il loro fallimento. Successivamente aggiungere il codice necessario per gestire il primo caso, eseguirlo e vedere il primo test passare e andare avanti in questo modo. Alla fine quando tutti i test passano, la funzione è corretta. 

In un'applicazione costruita con la metodologia test-driven, la quantità di codice dedicato ai test raggiunge quasi il codice dell'applicazione vera e propria. Poiché non si vuole spendere tempo nelle operazioni debugging dei test è bene mantenere il loro codice semplice.

>**NOTA**
>Rifattorizzare un metodo può creare nuovi bug che non erano apparsi prima. Questo è il motivo per il quale è sempre una buona pratica eseguire tutti i test prima di rilasciare una nuova caratteristica dell'applicazione in produzione: questo tipo di test è chiamato test di regressione. 

### Il framework di test Lime

Ci sono molti framework di test unitari nel mondo del PHP, il più conosciuto è PHPUnit. Symfony ha il suo, si chiama lime. È basato sulla libreria Perl `Test::More` ed è conforme al TAP, che significa che il risultato dei test è mostrato come specificato nel protocollo Test Anything Protocol, disegnato per una migliore leggibilità degli output dei test.

Lime supporta i test unitari. È molto leggero in confronto agli altri framework di test in PHP e ha diversi vantaggi:

  * Lancia file di test in una sandbox, per evitare strani effetti collaterali tra un test e l'altro. Non tutti i framework di test sono in grado di garantire un ambiente pulito per ogni test.
  * I test di lime sono molto leggibili, così come l'output. Su sistemi compatibili, lime utilizza output colorato, in modo da distinguere le informazioni importanti. 
  * Symfony stesso usa lime per i test di regressione, quindi molti esempi di test unitari e funzionali possono essere trovati nel codice sorgente di symfony.
  * Lo stesso nucleo di Lime è validato con test unitari
  * È scritto in php, è veloce, scritto bene e non ha dipendenze.
  
I vari test descritti di seguito usano la sintassi di lime. Funzionano in ogni installazione di symfony.

>**NOTA**
>Non si suppone che test unitari e funzionali siano avviati in produzione. Sono degli strumenti di sviluppo e come tali devono essere avviati nei computer degli sviluppatori e non nei server host.

Test unitari
----------

I test di symfony sono semplici file PHP che finiscono con `Test.php` e sono posizionati nella directory `test/unit/` dell'applicazione. Seguono una semplice e leggibile sintassi. 

### Cosa dovrebbero fare i test unitari?

Il listato 15-1 mostra un tipico insieme di test unitari per la funzione `strtolower()`. Inizia con l'istanza di un oggetto `lime_test` (non preoccupiamoci dei parametri per adesso). Ogni test unitario è una chiamata a un metodo dell'istanza `lime_test`. L'ultimo parametro di questi metodi è sempre una stringa opzionale che ha funzioni di output.

Listato 15-1 - Esempio di file di un test unitario in `test/unit/strtolowerTest.php`

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    $t = new lime_test(7);

    // strtolower()
    $t->diag('strtolower()');
    $t->isa_ok(strtolower('Foo'), 'string',
        'strtolower() returns a string');
    $t->is(strtolower('FOO'), 'foo',
        'strtolower() transforms the input to lowercase');
    $t->is(strtolower('foo'), 'foo',
        'strtolower() leaves lowercase characters unchanged');
    $t->is(strtolower('12#?@~'), '12#?@~',
        'strtolower() leaves non alphabetical characters unchanged');
    $t->is(strtolower('FOO BAR'), 'foo bar',
        'strtolower() leaves blanks alone');
    $t->is(strtolower('FoO bAr'), 'foo bar',
        'strtolower() deals with mixed case input');
    $t->is(strtolower(''), 'foo',
        'strtolower() transforms empty strings into foo');

Lanciamo l'insieme di test dalla linea di comando con il task `test:unit`. L'output della linea di comando è molto esplicito e aiuta a capire quale test è fallito e quale è passato. Vediamo l'output dei test del listato 15-2

Listato 15-2 - Lanciamo un singolo test unitario dalla linea di comando:

    $ php symfony test:unit strtolower

    1..7
    # strtolower()
    ok 1 - strtolower() returns a string
    ok 2 - strtolower() transforms the input to lowercase
    ok 3 - strtolower() leaves lowercase characters unchanged
    ok 4 - strtolower() leaves non alphabetical characters unchanged
    ok 5 - strtolower() leaves blanks alone
    ok 6 - strtolower() deals with mixed case input
    not ok 7 - strtolower() transforms empty strings into foo
    #     Failed test (.\batch\test.php at line 21)
    #            got: ''
    #       expected: 'foo'
    # Looks like you failed 1 tests of 7.

>**Suggerimento**
>La dichiarazione `include` all'inizio del listato 15-1 è opzionale. Il suo utilizzo rende lo script PHP indipendente in modo che possa essere eseguito anche senza gli strumenti da linea di comando messi a disposizione da symfony, chiamando `php test/unit/strtolowerTest.php`.


### Metodi del test unitario

L'oggetto `lime_test` è fornito con un ampio numero di metodi di test, come mostrato nella Tabella 15-2

Tabella 15-2 - Metodi dell'oggetto `lime_test` per i test unitari

Metodo                                        | Descrizione
--------------------------------------------- | -------------------------------------------------------------
`diag($msg)`                                  | Restituisce un messaggio ma non esegue test
`ok($test[, $msg])`                           | Verifica una condizione che passa solo se è vera
`is($value1, $value2[, $msg])`                | Confronta due valori e passa solo se sono uguali (`==`)
`isnt($value1, $value2[, $msg])`              | Confronta due valori e passa solo se non sono uguali
`like($string, $regexp[, $msg])`              | Verifica che una stringa aderisca a una espressione regolare
`unlike($string, $regexp[, $msg])`            | Verifica che una stringa non aderisca a una espressione regolare
`cmp_ok($value1, $operator, $value2[, $msg])` | Confronta due valori con un operatore
`isa_ok($variable, $type[, $msg])`            | Verifica il tipo di un parametro
`isa_ok($object, $class[, $msg])`             | Verifica la classe di un oggetto
`can_ok($object, $method[, $msg])`            | Verifica la disponibilità di un metodo per un oggetto o una classe
`is_deeply($array1, $array2[, $msg])`         | Verifica che due array abbiano gli stessi valori
`include_ok($file[, $msg])`                   | Verifica che un file esista e sia stato correttamente incluso
`fail([$msg])`                                | Fallisce sempre--comodo per testare le eccezioni
`pass([$msg])`                                | Passa sempre--comodo per testare le eccezioni
`skip([$msg, $nb_tests])`                     | Conta come `$nb_tests` test (utile per i test condizionali)
`todo([$msg])`                                | Conta come test (utile per i test ancora da scrivere)
`comment($msg)`                               | Restituisce un commento ma non esegue test
`error($msg)`                                 | Restituisce un messaggio di errore ma non esegue test
`info($msg)`                                  | Restituisce un messaggio informativo ma non esegue test

La sintassi è molto semplice; notare che la maggior parte dei metodi fornisce un messaggio come ultimo parametro. Questo messaggio è mostrato nell'output quando il test passa. Attualmente, il miglior modo per apprendere questi metodo è quello di utilizzarli, quindi vediamo il listato 15-3, che li usa tutti.

Listato 15-3 - Testiamo i metodi dell'oggetto `lime_test`, in `test/unit/exampleTest.php`

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    // Oggetti e funzioni finti per scopi di test
    class myObject
    {
      public function myMethod()
      {
      }
    }

    function throw_an_exception()
    {
      throw new Exception('exception thrown');
    }

    // Inizializza l'oggetto test
    $t = new lime_test(16);

    $t->diag('hello world');
    $t->ok(1 == '1', 'the equal operator ignores type');
    $t->is(1, '1', 'a string is converted to a number for comparison');
    $t->isnt(0, 1, 'zero and one are not equal');
    $t->like('test01', '/test\d+/', 'test01 follows the test numbering pattern');
    $t->unlike('tests01', '/test\d+/', 'tests01 does not follow the pattern');
    $t->cmp_ok(1, '<', 2, 'one is inferior to two');
    $t->cmp_ok(1, '!==', true, 'one and true are not identical');
    $t->isa_ok('foobar', 'string', '\'foobar\' is a string');
    $t->isa_ok(new myObject(), 'myObject', 'new creates object of the right class');
    $t->can_ok(new myObject(), 'myMethod', 'objects of class myObject do have a myMethod method');
    $array1 = array(1, 2, array(1 => 'foo', 'a' => '4'));
    $t->is_deeply($array1, array(1, 2, array(1 => 'foo', 'a' => '4')),
        'the first and the second array are the same');
    $t->include_ok('./fooBar.php', 'the fooBar.php file was properly included');

    try
    {
      throw_an_exception();
      $t->fail('no code should be executed after throwing an exception');
    }
    catch (Exception $e)
    {
      $t->pass('exception caught successfully');
    }

    if (!isset($foobar))
    {
      $t->skip('skipping one test to keep the test count exact in the condition', 1);
    }
    else
    {
      $t->ok($foobar, 'foobar');
    }

    $t->todo('one test left to do');

Si troveranno molti altri esempi dell'utilizzo di questi metodi nei test unitari di symfony.

>**SUGGERIMENTO**
>Ci si potrebbe chiedere perché si una un `is()` al posto di `ok()`. Il messaggio di errore mostrato da `is()` è molto più esplicito; Mostra entrambi i membri del test, mentre `ok()` dice solamente che la condizione fallisce.

### Parametri di test

L'inizializzazione dell'oggetto `lime_test` prende come primo parametro il numero di test che dovranno essere eseguiti. Se il numero dei test eseguiti alla fine differisce da questo numero, lime ci fornirà un avviso a tal riguardo. Per esempio i test del listato 15-3 forniscono il risultato del listato 15-4. L'inizializzazione stabilisce che saranno eseguiti 16 test, ma solo 15 sono eseguiti, quindi saremo avvisati a tal riguardo. 

Listato 15-4 - Il conteggio dei test da eseguire aiuta nella pianificazione dei test
    $ php symfony test:unit example

    1..16
    # hello world
    ok 1 - the equal operator ignores type
    ok 2 - a string is converted to a number for comparison
    ok 3 - zero and one are not equal
    ok 4 - test01 follows the test numbering pattern
    ok 5 - tests01 does not follow the pattern
    ok 6 - one is inferior to two
    ok 7 - one and true are not identical
    ok 8 - 'foobar' is a string
    ok 9 - new creates object of the right class
    ok 10 - objects of class myObject do have a myMethod method
    ok 11 - the first and the second array are the same
    not ok 12 - the fooBar.php file was properly included
    #     Failed test (.\test\unit\testTest.php at line 27)
    #       Tried to include './fooBar.php'
    ok 13 - exception catched successfully
    ok 14 # SKIP skipping one test to keep the test count exact in the condition
    ok 15 # TODO one test left to do
    # Looks like you planned 16 tests but only ran 15.
    # Looks like you failed 1 tests of 16.

Il metodo `diag()` non viene conteggiato come un test. Usarlo per mostrare commenti, così da mantenere i test organizzati e leggibili. D'altra parte, i metodi `todo()` e `skip()` contano come test effettivi. Il rapporto `pass()`/`fail()` all'interno dei blocchi `try`/`catch` conta come un singolo test.

Una buona strategia di test deve prevedere un numero definito di test. Si troverà molto utile per validare i propri file di test - specialmente nei casi complessi nei quali i test sono avviati all'interno di condizioni o di eccezioni. E, se i test falliscono a un certo punto, si vedrà velocemente che il numero dei test stabiliti non combacia con quello dei test effettuati.

### Il task test:unit

Il task `test:unit`, che lancia i test unitari da linea di comando, si aspetta una lista di nomi o schemi di file. Vedere il listato 15-5 per maggiori dettagli.

Listato 15-5 - Avviare i test unitari

    // Test directory structure
    test/
      unit/
        myFunctionTest.php
        mySecondFunctionTest.php
        foo/
          barTest.php

    $ php symfony test:unit myFunction                   ## Avvia myFunctionTest.php
    $ php symfony test:unit myFunction mySecondFunction  ## Avvia entrambi i test
    $ php symfony test:unit foo/*                        ## Avvia barTest.php
    $ php symfony test:unit *                            ## Avvia tutti i test (ricorsivo)

### Stubs, Fixtures e autocaricamento

In un test unitario la caratteristica dell'autocaricamento non è abilitato di default. Ogni classe che si usa in un test deve essere definita in un file di test o richiesta come dipendenza esterna. È il motivo per il quale molti file di test cominciano con una serie di inclusione di file, come mostrato nel listato 15-6

Listato 15-6 - Includere classi nei test unitari

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';
    require_once sfConfig::get('sf_symfony_lib_dir').'/util/sfToolkit.class.php';

    $t = new lime_test(7);

    // isPathAbsolute()
    $t->diag('isPathAbsolute()');
    $t->is(sfToolkit::isPathAbsolute('/test'), true,
        'isPathAbsolute() returns true if path is absolute');
    $t->is(sfToolkit::isPathAbsolute('\\test'), true,
        'isPathAbsolute() returns true if path is absolute');
    $t->is(sfToolkit::isPathAbsolute('C:\\test'), true,
        'isPathAbsolute() returns true if path is absolute');
    $t->is(sfToolkit::isPathAbsolute('d:/test'), true,
        'isPathAbsolute() returns true if path is absolute');
    $t->is(sfToolkit::isPathAbsolute('test'), false,
        'isPathAbsolute() returns false if path is relative');
    $t->is(sfToolkit::isPathAbsolute('../test'), false,
        'isPathAbsolute() returns false if path is relative');
    $t->is(sfToolkit::isPathAbsolute('..\\test'), false,
        'isPathAbsolute() returns false if path is relative');

Nei test unitari è necessario non solo istanziare gli oggetti che stai testando, ma anche gli oggetti che dipendono da questi. Poiché i test devono rimanere unitari, le dipendenze da altre classi possono fare in modo che un test fallisca se una di queste è rotta. In aggiunta, configurare oggetti reali può essere dispendioso sia in termini di linee di codice che di tempi di esecuzione. Bisogna tenere a mente che la velocità è cruciale nei test unitari perché gli sviluppatori si stufano facilmente dei processi lenti.

Ogni volta che si cominciano a includere molti script per un test unitario, può essere necessario un sistema di autocaricamento semplificato. Per questo scopo, la classe `sfSimpleAutoload` (che deve essere inclusa manualmente) prevede il metodo `addDirectory()` che prende come parametro un percorso assoluto e può essere richiamato più volte nel caso si debbano includere diverse directory nel percorso di ricerca.  Tutte le classi che si trovano in questo percorso saranno caricate automaticamente. Per esempio, se si desidera avere autocaricate tutte le classi situate sotto `sfConfig::get('sf_symfony_lib_dir')/util/` iniziare lo script come segue:

    [php]
    require_once sfConfig::get('sf_symfony_lib_dir').'/autoload/sfSimpleAutoload.class.php';
    $autoload = sfSimpleAutoload::getInstance();
    $autoload->addDirectory(sfConfig::get('sf_symfony_lib_dir').'/util');
    $autoload->register();

Un'altra buona soluzione per le questioni dell'autocarimento è l'utilizzo degli stub. Uno stub è un'implementazione alternativa di una classe in cui vengono sostituiti i metodi reali con dati fittizi. Simula il comportamento della classe reale ma senza i suoi costi. Un un buon esempio di stub è la connessione al database o l'interfaccia adun web service. Nel listato 15-7, i test unitari per la mappatura di API relative alla classe `WebService`.

Listato 15-7 - Utilizzo di stub nei test unitari

    [php]
    require_once dirname(__FILE__).'/../../lib/WebService.class.php';
    require_once dirname(__FILE__).'/../../lib/MapAPI.class.php';

    class testWebService extends WebService
    {
      public static function fetch()
      {
        return file_get_contents(dirname(__FILE__).'/fixtures/data/fake_web_service.xml');
      }
    }

    $myMap = new MapAPI();

    $t = new lime_test(1, new lime_output_color());

    $t->is($myMap->getMapSize(testWebService::fetch(), 100));

I dati di prova possono essere più complessi di una stringa o una di una chiamata a un metodo. Test data complessi sono spesso definiti come fixture. Per maggiore chiarezza nel codice, spesso è meglio tenere le fixture il file separati, soprattutto se sono utilizzati da più di un file di test unitari. Inoltre, non bisogna dimenticare che symfony può trasformare facilmente un file YAML in un array con il metodo `sfYaml::load ()`. Ciò significa che invece di scrivere un'ampio array PHP in, è possibile scrivere i test data in un file YAML, come mostrato nel listato 15-8.

Listato 15-8 - Usare file di fixture nei test unitari

    [php]
    // In fixtures.yml:
    -
      input:   '/test'
      output:  true
      comment: isPathAbsolute() returns true if path is absolute
    -
      input:   '\\test'
      output:  true
      comment: isPathAbsolute() returns true if path is absolute
    -
      input:   'C:\\test'
      output:  true
      comment: isPathAbsolute() returns true if path is absolute
    -
      input:   'd:/test'
      output:  true
      comment: isPathAbsolute() returns true if path is absolute
    -
      input:   'test'
      output:  false
      comment: isPathAbsolute() returns false if path is relative
    -
      input:   '../test'
      output:  false
      comment: isPathAbsolute() returns false if path is relative
    -
      input:   '..\\test'
      output:  false
      comment: isPathAbsolute() returns false if path is relative

    // In testTest.php
    <?php

    include(dirname(__FILE__).'/../bootstrap/unit.php');
    require_once sfConfig::get('sf_symfony_lib_dir').'/util/sfToolkit.class.php';
    require_once sfConfig::get('sf_symfony_lib_dir').'/yaml/sfYaml.class.php';

    $testCases = sfYaml::load(dirname(__FILE__).'/fixtures.yml');

    $t = new lime_test(count($testCases), new lime_output_color());

    // isPathAbsolute()
    $t->diag('isPathAbsolute()');
    foreach ($testCases as $case)
    {
      $t->is(sfToolkit::isPathAbsolute($case['input']), $case['output'],$case['comment']);
    }

### Test unitari per le classi dell'ORM

Testare le classi di Propel o di Doctrine è un po più articolato poiché gli oggetti generati hanno una lunga serie di dipendenze con altre classi. Inoltre è necessario fornire una connessione al database valida anche per caricare i dati di prova.

Per fortuna, è abbastanza facile, perché symfony fornisce già tutto il necessario:

  * Per ottenere caricamento automatico, è necessario inizializzare un oggetto di configurazione
  * Per ottenere una connessione al database, è necessario inizializzare la classe `sfDatabaseManager`
  * Per caricare alcuni dati di prova, è possibile utilizzare la classe `sfPropelData`

Un tipico file di test di Propel mostrato nel listato 15-9

Listato 15-9 - Testare le classi di Propel

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    new sfDatabaseManager($configuration);
    $loader = new sfPropelData();
    $loader->loadData(sfConfig::get('sf_data_dir').'/fixtures');

    $t = new lime_test(1, new lime_output_color());

    // begin testing your model class
    $t->diag('->retrieveByUsername()');
    $user = UserPeer::retrieveByUsername('fabien');
    $t->is($user->getLastName(), 'Potencier', '->retrieveByUsername() returns the User for the given username');


Un tipico file di test di Doctrine mostrato nel listato 15-9.
    
Listato 15-10 - Testare le classi di Doctrine

    [php]
    <?php

    include dirname(__FILE__).'/../bootstrap/unit.php';

    new sfDatabaseManager($configuration);
    Doctrine_Core::loadData(sfConfig::get('sf_data_dir').'/fixtures');

    $t = new lime_test(1, new lime_output_color());

    // begin testing your model class
    $t->diag('->retrieveByUsername()');
    $user = Doctrine::getTable('User')->findOneByUsername('fabien');
    $t->is($user->getLastName(), 'Potencier', '->findOneByUsername() returns the User for the given username');

Test funzionali
----------------

I test funzionali validano pezzi delle applicazioni. Sono in grado di simulare le sessioni di navigazione, fare richieste e controllare elementi della risposta proprio come si farebbe manualmente per per validare che un azione faccia effettivamente quello che ci si aspetta. Nei test funzionali si esegue uno scenario corrispondente a un caso d'uso.

### Come dovrebbero essere i test funzionali ?

Si potrebbero eseguire test funzionali con un browser testuale e molte espressioni regolari di verifica, ma sarebbe un grande spreco di tempo. Symfony mette a disposizione un oggetto speciale, chiamato `sfBrowser`, che si comporta come un browser connesso all'applicazione symfony senza la reale necessità di un server- e senza il rallentamento dello scambio dati dell'HTTP. Fornisce l'accesso a tutti i principali oggetti di ogni richiesta ( la richiesta, la sessione, il context e l'oggetto risposta). Symfony mette a disposizione anche l'oggetto `sfTestFunctional`, progettato appositamente per i test funzionali. Esso prende l'oggetto `sfBrowser` e aggiunge alcuni metodi di asserzione.

Tipicamente un test funzionale comincia con l'inizializzazione di un oggetto browser. Questo oggetto fa una richiesta a una azione e verifica che alcuni elementi siano presenti nella risposta.

Per esempio, ogni volta che si genera un modulo dello skeleton con il comando `generate:module` o con `propel:generate-module`, symfony crea un test funzionale di base per questo modulo. Il test fa una richiesta all'azione di default del modulo e verifica il codice di stato della risposta, il modulo e l'azione calcolati dal sistema di routing, la presenza di alcuni  elementi nel contenuto della risposta. Per il modulo `foobar`, il file generato `foobarActionsTest.php` è simile al listato !5-11.

Listato 15-11 - Test funzionale di dafault per un nuovo modulo, in `tests/functional/frontend/foobarActionsTest.php`

    [php]
    <?php

    include dirname(__FILE__).'/../../bootstrap/functional.php';

    $browser = new sfTestFunctional(new sfBrowser());

    $browser->
      get('/foobar/index')->

      with('request')->begin()->
        isParameter('module', 'foobar')->
        isParameter('action', 'index')->
      end()->

      with('response')->begin()->
        isStatusCode(200)->
        checkElement('body', '!/This is a temporary page/')->
      end()
    ;

>**SUGGERIMENTO**
>I metodi del browser restituiscono un oggetto `sfTestFunctional` quindi è possibile concatenare i metodi da chiamare per aumentare la leggibilità dei file. Questa modalità viene chiamata interfaccia fluida dell'oggetto, perché nulla ferma il flusso delle chiamate ai metodi.

Un test funzionale può contenere differenti richieste e asserzioni più complesse. Vedremo di scoprire tutte le possibilità nelle prossime sezioni.

Per lanciare un test funzionale è necessario utilizzare il task dal linea di comando `test:functional, come mostrato nel listato 15-12. Tale comando prende come parametri il nome dell'applicazione e il nome del test (Omettere il suffisso `Test.php` ).

Listato 15-12 - Esecuzione di un singolo test funzionale da linea di comando

    $ php symfony test:functional frontend foobarActions

    # get /comment/index
    ok 1 - status code is 200
    ok 2 - request parameter module is foobar
    ok 3 - request parameter action is index
    not ok 4 - response selector body does not match regex /This is a temporary page/
    # Looks like you failed 1 tests of 4.
    1..4

Il test funzionale generato automaticamente alla creazione di un nuovo modulo fallisce di default. Questo perché in un nuovo modulo l'azione `index` inoltra alla pagina delle congratulazioni (inclusa nel modulo `default` di symfony), il test per questo modulo fallirà e questo garantisce che non si avranno tutti i test verdi senza aver completato tutti i moduli.

>**NOTA**
>Nei test funzionali, l'autocaricamento è attivato, quindi non è necessario aggiungere alcun file a mano.

### Navigare con l'oggetto `sfBrowser`

Il browser è in grado di eseguire richieste GET e POST. In entrambi i casi, viene utilizzato un URI reale come parametro. Il listato 15-13 mostra come scrivere una chiamata all'oggetto `sfBrowser` per simulare queste richieste.

Listato 15-13 - Simulare le richieste con l'oggetto `sfBrowser`

    [php]
    include dirname(__FILE__).'/../../bootstrap/functional.php';

    // Create a new browser
    $b = new sfBrowser();

    $b->get('/foobar/show/id/1');                   // GET request
    $b->post('/foobar/show', array('id' => 1));     // POST request

    // The get() and post() methods are shortcuts to the call() method
    $b->call('/foobar/show/id/1', 'get');
    $b->call('/foobar/show', 'post', array('id' => 1));

    // The call() method can simulate requests with any method
    $b->call('/foobar/show/id/1', 'head');
    $b->call('/foobar/add/id/1', 'put');
    $b->call('/foobar/delete/id/1', 'delete');

Una tipica sessione di navigazione non contiene sono delle richieste a delle specifiche azioni, ma anche dei click a dei link o a dei bottoni della pagina. Come mostrato nel listato 15-14, l'oggetto `sfBrowser` è anche capace di simulare queste cose.

Listato 15-14 - Simulazione della navigazione con l'oggetto `sfBrowser`

    [php]
    $b->get('/');                  // Request to the home page
    $b->get('/foobar/show/id/1');
    $b->back();                    // Back to one page in history
    $b->forward();                 // Forward one page in history
    $b->reload();                  // Reload current page
    $b->click('go');               // Look for a 'go' link or button and click it

Il browser gestisce lo stack delle chiamate, in questo modo i metodi `back()` e `forward()` lavorano come lo farebbero in un browser reale.

>**SUGGERIMENTO**
>Il browser ha i suoi meccanismi per gestire le sessioni (`sfTestStorage`) e i Cookie

Tra le interazioni che necessitano di essere maggiormente testate ci sono probabilmente per prime quelle relative hai form. Per simulare la compilazione di un form e il suo invio, si hanno a disposizione tre scelte. È possibile fare una richiesta post con i parametri che si desidera inviare, chiamare il metodo `click()` con i parametri del form passati come array, oppure riempire i campi uno per uno e cliccare il bottone di invio. Tutti si equivalgono nella stessa richiesta POST. Mostriamo nel listato 15-15 un esempio

Listato 15-15 - Simulare gli input del form con l'oggetto `sfBrowser`

    [php]
    // Example template in modules/foobar/templates/editSuccess.php
    <?php echo form_tag('foobar/update') ?>
      <input type="hidden" name="id" value="<?php echo $sf_params->get('id') ?>" />
      <input type="text" name="name" value="foo" />
      <input type="submit" value="go" />
      <textarea name="text1">foo</textarea>
      <textarea name="text2">bar</textarea>
    </form>

    // Example functional test for this form
    $b = new sfBrowser();
    $b->get('/foobar/edit/id/1');

    // Option 1: POST request
    $b->post('/foobar/update', array('id' => 1, 'name' => 'dummy', 'commit' => 'go'));

    // Option 2: Click the submit button with parameters
    $b->click('go', array('name' => 'dummy'));

    // Option 3: Enter the form values field by field name then click the submit button
    $b->setField('name', 'dummy')->
        click('go');

>**NOTA**
>Con la seconda e la terza opzione, i valori di default del form sono automaticamente inclusi nell'invio del form e non è necessario stabilire il target del form.

Quando un azione si conclude con il `redirect()`, il browser non è automaticamente in grado di seguire il redirezionamento; è necessario seguirlo manualmente con il metodo `followRedirect()`, come mostrato nel listato 15-16.

Listato 15-16 - Il Browser non segue automaticamente i redirezionamenti

    [php]
    // Example action in modules/foobar/actions/actions.class.php
    public function executeUpdate($request)
    {
      // ...

      $this->redirect('foobar/show?id='.$request->getParameter('id'));
    }

    // Example functional test for this action
    $b = new sfBrowser();
    $b->get('/foobar/edit?id=1')->
        click('go', array('name' => 'dummy'))->
        followRedirect();    // Manually follow the redirection

Uno degli ultimi metodi che bisognerebbe conoscere per la sua utilità è `restart()`. Tale metodo reinizializza la cronologia della navigazione, le sessioni i cookie, come se si riavviasse il browser.

Una volta che è stata fatta la prima richiesta l'oggetto `sfBrowser` può fornire accesso alla richiesta, al context e all'oggetto risposta. Questo significa che si possono verificare molte cose spaziando dal contenuto della risposta fino agli header, ai parametri e alla configurazione:

    [php]
    $request  = $b->getRequest();
    $context  = $b->getContext();
    $response = $b->getResponse();

### Usare le asserzioni

Poiché l'oggetto `sfBrowser` ha accesso alla risposta e a altre componenti della richiesta, questi componenti possono essere testati. Bisognerebbe creare un nuovo `lime_test` per questo obiettivo ma, fortunatamente `sfTestFunctional` propone un metodo `test()` che restituisce un oggetto `lime_test` sul quale si possono chiamare i metodi di asserzione unitari descritti precedentemente. Verificare nel listato 15-17 come utilizzare questi metodi attraverso il `sfTestFunctional`.

Listato 15-17 - Il Browser fornisce abilità aggiuntive attraverso il metodo `test()`

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1');
    $request  = $b->getRequest();
    $context  = $b->getContext();
    $response = $b->getResponse();

    // Get access to the lime_test methods via the test() method
    $b->test()->is($request->getParameter('id'), 1);
    $b->test()->is($response->getStatuscode(), 200);
    $b->test()->is($response->getHttpHeader('content-type'), 'text/html;charset=utf-8');
    $b->test()->like($response->getContent(), '/edit/');

>**NOTA**
>I metodi `getResponse()`, `getContext()`, `getRequest()` e`test()` non restituiscono un oggetto `sfBrowser` pertanto non è possibile concatenare altri metodi del `sfBrowser` dopo di loro.

Si possono verificare i cookie entranti e uscenti facilmente attraverso gli oggetti di richiesta e risposta, come mostrato nel listato 15-16

Listato 15-16 - Testare i Cookie con `sfBrowser`

    [php]
    $b->test()->is($request->getCookie('foo'), 'bar');     // Incoming cookie
    $cookies = $response->getCookies();
    $b->test()->is($cookies['foo'], 'foo=bar');            // Outgoing cookie

Usare il metodo `test()` per testare l'elemento richiesta sarebbe molto pesante. Fortunatamente, `sfTestFunctional` contiene alcuni metodi proxy che aiutano a mantenere i test funzionali leggibili e leggeri- in aggiunta restituiscono un oggetto `sfTestFunctional`. Per esempio è possbile riscrivere il listato 15-15 in modo più veloce, come mostrato nel listato 15-18.

Listato 15-18 - Testare direttamente con `sfTestFunctional`

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1')->
    with('request')->isParameter('id', 1)->
    with('response')->begin()->
      isStatusCode()->
      isHeader('content-type', 'text/html; charset=utf-8')->
      matches('/edit/')->
    end()
    ;

Ogni metodo proxy fa parte del gruppo dei tester. L'utilizzo di un gruppo di tester è abilitato utilizzando le chiamate tra i metodi `with()` e `end()`. Il metodo `with()` stabilisce il tester del gruppo ( come `request` e `response`  ).

Il codice di ritorno 200 è quello di default che viene utilizzato con il metodo `isStatusCode()` quando viene chiamato senza parametri.

Un altro vantaggio dei metodi proxy è che non è necessario specificare un testo di output come si farebbe con i metodi di `lime_test`. I messaggi sono generati automaticamente dai metodi proxy e il testo di output è chiaro e leggibile.

    # get /foobar/edit/id/1
    ok 1 - request parameter "id" is "1"
    ok 2 - status code is "200"
    ok 3 - response header "content-type" is "text/html"
    ok 4 - response matches "/edit/"
    1..4

In pratica, i metodi proxy del listato 15-17 coprono la maggior parte dei test che si usano normalmente, quindi il metodo `test()` dell'oggetto  `sfTestFunctional` si userà raramente

Il listato 15-15 mostrava che `sfBrowser` non segue automaticamente i reindirizzamenti. Questo ha un vantaggio: è possibile testare la redirezione. Per esempio, il listato 15-19 mostra come testare la risposta del listato 15-14.

Listato 15-19 - Testare il redirezionamento con `sfTestFunctional`

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->
      get('/foobar/edit/id/1')->
      click('go', array('name' => 'dummy'))->
      with('request')->begin()->
        isParameter('module', 'foobar')->
        isParameter('action', 'update')->
      end()->
      with('response')->begin()->
        isStatusCode(200)->
        isRedirected()->      // Check that the response is a redirect
      end()->

      followRedirect()->    // Manually follow the redirection

      with('request')->begin()->
        isRequestParameter('module', 'foobar')->
        isRequestParameter('action', 'show')->
      end()->
      with('response')->isStatusCode(200)
    ;

### Usare i selettori CSS

Molti dei test funzionali validano la correttezza di una pagina controllando la presenza di un testo nel contenuto. Con l'aiuto delle espressioni regolari nel metodo `matches()` si può controllare il testo mostrato, gli attributi dei tag o i valori. Ma non appena si desidera controllare qualcosa di più profondo nel DOM della risposta, le espressioni regolari non sono ideali. 

Motivo per il quale l'oggetto `sfTestFunctional` supporta il metodo `getResponseDom()`. Restituisce un oggetto libXML2 DOM che è molto più facilmente interpretabile dal parser e dai test rispetto a un testo piatto. Fare riferimento al listato 15-20 per un esempio di utilizzo di questo metodo. 

Listato 15-20 - Il test Browser fornisce accesso al Contenuto della risposta come oggetto DOM

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1');
    $dom = $b->getResponseDom();
    $b->test()->is($dom->getElementsByTagName('input')->item(1)->getAttribute('type'),'text');

Ma il parsing di un documento HTML con il metodo DOM del php non è ancora molto veloce e facile. Se si ha famigliarità con i selettori CSS, si è consci del fatto che possono essere una via rapida per ottenere elementi da un documento HTML. Symfony mette a disposizione un metodo chiamato `sfDomCssSelector` che si aspetta un documento DOM come parametro per il costruttore. Questo oggetto fornisce il metodo `getValues()` il quale restituisce un array di stringhe in accordo il con selettore CSS e il metodo `getValues()` che restituisce un array di elementi DOM. Vediamo un esempio nel listato 15-20.

Listato 15-21 - Il Test Browser fornisce accesso al contenuto della risposta come un oggetto `sfDomCssSelector`

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit/id/1');
    $c = new sfDomCssSelector($b->getResponseDom())
    $b->test()->is($c->getValues('form input[type="hidden"][value="1"]'), array('');
    $b->test()->is($c->getValues('form textarea[name="text1"]'), array('foo'));
    $b->test()->is($c->getValues('form input[type="submit"]'), array(''));

Nella sua costante ricerca della praticità e chiarezza, symfony mette a disposizione una via più rapida: il metodo proxy `checkElement()` del gruppo di tester `response`. Nel listato 15-22 viene mostrato come sarebbe il listato 15-21 usando questo metodo.

Listato 15-22 - Il Browser test fornisce acceso agli elementi della risposta con i selettori CSS

    [php]
    $b = new sfTestFunctional(new sfBrowser());a
    $b->get('/foobar/edit/id/1')->
      with('response')->begin()->
        checkElement('form input[type="hidden"][value="1"]', true)->
        checkElement('form textarea[name="text1"]', 'foo')->
        checkElement('form input[type="submit"]', 1)->
      end()
    ;

Il comportamento del metodo `checkElement()` dipendono dal tipo del secondo parametro, che può essere:

  * Se si tratta di un booleano, controlla che esista un corrispondente selettore CSS.
  * Se si tratta di un intero, verifica che il selettore CSS restituisca il numero dei risultati.
  * Se si tratta di una espressione regolare, verifica che il primo elemento trovato dal selettore CSS sia il suo corrispondente.
  Se si tratta di una espressione regolare preceduta da `!`, verifica che il prima elemento non corrisponda allo schema cercato.
  * Per altri casi, confronta le stringhe tra il primo elemento trovato dal selettore CSS e il secondo parametro.

Il metodo accetta un terzo parametro opzionale: un array associativo. Permette di effettuare test non sul primo elemento trovato dal selettore, ma su l'elemento che si trova in una specifica posizione (indicata nell'array), come mostrato nel listato 15-23

Listato 15-23 - Utilizzo dell'opzione posizione per verificare la corrispondenza con l'oggetto in una determinata posizione

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit?id=1')->
      with('response')->begin()->
        checkElement('form textarea', 'foo')->
        checkElement('form textarea', 'bar', array('position' => 1))->
      end()
    ;

L'array passato nel parametro opzionale può anche essere usato per eseguire due test contemporaneamente. Si può testare che ci sia l'elemento corrispondente al selettore e quanti ce ne sono, come mostrato nel listato 15-24

Listato 15-24 - Utilizzo dell'opzione Count per contare il numero di oggetti corrispondenti

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->get('/foobar/edit?id=1')->
      with('response')->checkElement('form input', true, array('count' => 3));

Lo strumento selettore è molto potente. Accetta molti dei selettori CSS 3 e si possono usare per query complesse come mostrato nel listato 15-25.

Listato 15-25 - Esempi di selettori CSS accettati da `checkElement()`

    [php]
    ->checkElement('ul#list li a[href]', 'click me');
    ->checkElement('ul > li', 'click me');
    ->checkElement('ul + li', 'click me');
    ->checkElement('h1, h2', 'click me');
    ->checkElement('a[class$="foo"][href*="bar.html"]', 'my link');
    ->checkElement('p:last ul:nth-child(2) li:contains("Some text")');

### Testare gli errori

A volte, le azioni o il modello generano eccezioni apposite (ad esempio per visualizzare una pagina 404). Anche se è possibile utilizzare un selettore CSS per controllare uno specifico messaggio di errore nel codice HTML generato, è meglio utilizzare `throwsException` per verificare che l'eccezione è stata generata come mostrato in listato 15-26.

Listato 15-26 - Testare le eccezioni

    [php]
    $b = new sfTestFunctional(new sfBrowser());
    $b->
      get('/foobar/edit/id/1')->
      click('go', array('name' => 'dummy'))->
      throwsException()->                   // Verifica che l'ultima richiesta ha generato un eccezione
      throwsException('RuntimeException')-> // Verifica la classe dell'eccezione
      throwsException(null, '/error/');     // Verifica che il messaggio dell'eccezione corrisponda con l'espressione regolare

### Lavorare nell'ambiente di test

L'oggetto `sfTestFunctional`  utilizza un front controller speciale impostato sull'ambiente `test`. La configurazione di default per questo ambiente appare nel listato 15-27.

Listato 15-27 - Configurazione di default dell'ambiente di test in `frontend/config/settings.yml`

    test:
      .settings:
        error_reporting:        <?php echo ((E_ALL | E_STRICT) ^ E_NOTICE)."\n" ?>
        cache:                  false
        web_debug:              false
        no_script_name:         false
        etag:                   false

In questo ambiente la cache e la web debug toolbar sono impostate a `false`. Tuttavia, l'esecuzione del codice lascia ancora tracce in un file di log, diverso dai file di log `dev` e `prod`, in modo da poter controllare in modo indipendente l'esecuzione (`myproject / log / frontend_test.log»). In questo ambiente le eccezioni non interrompono l'esecuzione degli script - in modo che sia possibile eseguire tutta una serie di test anche se uno non riesce. Si possono avere delle specifiche impostazioni di connessione per il database, per esempio, per usare un altro database che contenga i dati di test.

Prima di utilizzare l'oggetto `sfBrowser` è necessario inizializzarlo. Se necessario, è possibile specificare un hostname per l'applicazione e un indirizzo IP per il client - nel caso l'applicazione dovesse fare controlli su questi due parametri. Vediamo come si fa nel listato 15-28.

Listato 15-28 - Impostare il browser con un hostname e un IP

    [php]
    $b = new sfBrowser('myapp.example.com', '123.456.789.123');

### Il task `test:functional`

Il task `test: functional`  può eseguire uno o più test funzionali a seconda del numero di parametri che riceve. Le regole sono molto simili a quelle del task `test: unit`, salvo che i task del test funzionale si aspetta sempre il nome di un'applicazione come primo parametro, come mostrato nella listato 15-29.

Listato 15-29 - Sintassi del task dei test funzionali

    // Test directory structure
    test/
      functional/
        frontend/
          myModuleActionsTest.php
          myScenarioTest.php
        backend/
          myOtherScenarioTest.php

    ## Lancia tutti i test funzionali di un'applicazione ricorsivamente
    $ php symfony test:functional frontend

    ## Lancia il test funzionale dato
    $ php symfony test:functional frontend myScenario

    ## Lancia tutti i test funzionali che appartengono allo schema dato
    $ php symfony test:functional frontend my*

Buone pratiche per la nomenclatura dei test
-------------------------------------------

Questa sezione mostra alcune buone pratiche per mantenere i test organizzati e facili da mantenere. I suggerimenti riguardano l'organizzazione dei file dei test unitari e di quelli funzionali.

Per quanto riguarda la struttura dei file, è necessario che che il nome del file del test unitario utilizzi il nome della classe che si ha intenzione di testare e il nome del file del test funzionale abbia il nome del modulo o dello scenario che si vuole testare. Vedere nel listato 15-30 per un esempio. La cartella `test/` conterrà molti file e se non si seguono queste linee guida potrebbe risultare difficile trovare un test

Listato 15-30 - Esempio di nomenclatura dei test

    test/
      unit/
        myFunctionTest.php
        mySecondFunctionTest.php
        foo/
          barTest.php
      functional/
        frontend/
          myModuleActionsTest.php
          myScenarioTest.php
        backend/
          myOtherScenarioTest.php

Per i test unitari una buona pratica è quella di raggruppare i test per funzione o metodo e iniziare ogni gruppo di test con la chiamata `diag()`. Il messaggio di ogni test unitario dovrebbe contenere il nome della funzione o del metodo da testare seguito da un verbo e una proprietà in modo che il risultato dei test sembri una frase che descrive una proprietà di un oggetto. Il listato 15-31 mostra un esempio

Listato 15-31 - Esempio di nomenclatura corretta dei test unitari

    [php]
    // strtolower()
    $t->diag('strtolower()');
    $t->isa_ok(strtolower('Foo'), 'string', 'strtolower() returns a string');
    $t->is(strtolower('FOO'), 'foo', 'strtolower() transforms the input to lowercase');

    # strtolower()
    ok 1 - strtolower() returns a string
    ok 2 - strtolower() transforms the input to lowercase

I test funzionali dovrebbero essere raggruppati per pagina e iniziare con una richiesta. Il listato 15-32 mostra questa pratica.

Listato 15-32 - Esempio di nomenclatura corretta dei test funzionali

    [php]
    $browser->
      get('/foobar/index')->
      with('request')->begin()->
        isParameter('module', 'foobar')->
        isParameter('action', 'index')->
      end()->
      with('response')->begin()->
        isStatusCode(200)->
        checkElement('body', '/foobar/')->
      end()
    ;

    # get /comment/index
    ok 1 - status code is 200
    ok 2 - request parameter module is foobar
    ok 3 - request parameter action is index
    ok 4 - response selector body matches regex /foobar/

Seguendo questa convenzione il risultato dei test sarà chiaro tanto da poter essere usato come documentazione per i sviluppatori del progetto. A volte i test posso essere esplicativi, tanto  da rendere inutile la documentazione.

Necessità speciali dei test
---------------------------

I strumenti messi a disposizione da symfony per i test funzionali e per i test unitari dovrebbero essere sufficienti nella maggior parte dei casi. Alcune tecniche addizionali sono mostrate per risolvere alcuni problemi comuni nell'automazione dei test: avviare test in un ambiente isolato, accedere a un database dai test, testare la cache e testare le interazioni con la parte client.

### Lanciare un insieme di test

I task `test: unit` e `test: functional` possono eseguire un test singolo o una serie di test. Ma se si chiamano questi task senza alcun parametro si lanciano tutti i test unitari e funzionali che si trovano nella cartella `test /` . Attraverso un particolare meccanismo viene garantito che ogni file di test venga eseguito in un ambiente isolato al fine di evitare possibili combinazioni tra i test.
Inoltre poiché non avrebbe senso tenere lo stesso tipo di output, come nel test di un singolo file (l'output sarebbe lungo migliaia di linee), il risultato è compresso in una vista sintetica. Ecco perché l'esecuzione di un gran numero di file di test utilizza un framework di test con speciali caratteristiche. L'insieme di test è legato a un componente del framework lime chiamato `lime_harness`, il quale mostra lo stato un test file per file. Mostra, inoltre, un resoconto sul numero dei test falliti sul totale dei test come mostrato nel listato 15-33.

Listato 15-33 - Lanciare un insieme di test

    $ php symfony test:all

    unit/myFunctionTest.php................ok
    unit/mySecondFunctionTest.php..........ok
    unit/foo/barTest.php...................not ok

    Failed Test                     Stat  Total   Fail  List of Failed
    ------------------------------------------------------------------
    unit/foo/barTest.php               0      2      2  62 63
    Failed 1/3 test scripts, 66.66% okay. 2/53 subtests failed, 96.22% okay.

I test sono eseguiti allo stesso modo di quando sono chiamati singolarmente, solo l'output è sintetico per maggiore utilità. In particolare, il resoconto finale focalizza su i test che hanno fallito e aiuta a trovarli.

Si possono lanciare tutti i test con una sola chiamata utilizzando il task `test:all` come mostrato nel listato 15-34. Questa attività andrebbe fatta ogni volta che si trasferiscono i file all'ambiente di produzione per garantire che non ci siano comparsi errori di regressione dopo l'ultima release

Listato 15-34 - Lanciare tutti i test del progetto

    $ php symfony test:all

### Accesso al database

I test unitari spesso hanno necessità di accedere al database. La connessione al database è automaticamente instanziato quando si chiama `sfBrowser::get()` per la prima volta. Comunque, se si vuole l'accesso al database anche prima dell'utilizzo di `sfBrowser` è necessario inizializzare manualmente l'oggetto `sfDabataseManager` come mostrato nel listato 15-35

Listato 15-35 - Inizializzazione del database in un test

    [php]
    $databaseManager = new sfDatabaseManager($configuration);
    $databaseManager->loadConfiguration();

    // Optionally, you can retrieve the current database connection
    $con = Propel::getConnection();

Si consiglia di popolare il database con le fixture prima di iniziare i test. Tale procedura può essere fatta con l'oggetto `sfPropelData`. Quest'ultimo può caricare dati da un file proprio come il task `propel:data-load` oppure da un array, come mostrato nel listato 15-36

Listato 15-36 - Popolare il database dal file di test

    [php]
    $data = new sfPropelData();

    // Loading data from file
    $data->loadData(sfConfig::get('sf_data_dir').'/fixtures/test_data.yml');

    // Loading data from array
    $fixtures = array(
      'Article' => array(
        'article_1' => array(
          'title'      => 'foo title',
          'body'       => 'bar body',
          'created_at' => time(),
        ),
        'article_2'    => array(
          'title'      => 'foo foo title',
          'body'       => 'bar bar body',
          'created_at' => time(),
        ),
      ),
    );
    $data->loadDataFromArray($fixtures);

Quindi, utilizzare gli oggetti Propel come si farebbe in una normale applicazione, in base alle proprie esigenze di test. Ricordarsi di includere i propri file nei test unitari (si può usare la classe `sfSimpleAutoload` per automatizzare questo processo come mostrato nella sezione "Stub, fixture e autocaricamento" di questo capitolo) mentre gli oggetti propel sono caricati automaticamente nei test funzionali.

### Testare la cache

Quando si attiva la cache per un'applicazione i test funzionali dovrebbero verificare che le azioni della cache funzionino come ci si aspetti.

La prima cosa da fare consiste nell'abilitare la cache per l'ambiente di test (nel file `settings.yml`). Poi, se si vuole verificare se una pagina deriva dalla cache oppure se è stata generata bisogna utilizzare il metodo `isCached()` del gruppo di test `view_cache`. Il listato 15-37 mostra come usare questo metodo.


Listato 15-37 - Testare la cache con il metodo `isCached()`

    [php]
    <?php

    include dirname(__FILE__).'/../../bootstrap/functional.php';

    // Create a new test browser
    $b = new sfTestFunctional(new sfBrowser());

    $b->get('/mymodule');
    $b->with('view_cache')->isCached(true);       // Verifica che la risposta provenga dalla cache
    $b->with('view_cache')->isCached(true, true); // Verifica che la risposta in cache abbia il layout
    $b->with('view_cache')->isCached(false);      // Verifica che la risposta non provenga dalla cache

>**NOTA**
>Non è necessario cancellare la cache all'inizio di un test funzionale, lo script di bootstrap lo fa automaticamente.

### Testare le interazioni con il client

Lo svantaggio principale delle tecniche descritte in precedenza è che non possono simulare il codice JavaScript. Per le interazioni molto complesse, come quelle Ajax, per esempio, è necessario essere in grado di riprodurre esattamente l'input del mouse e della tastiera che un utente avrebbe fatto ed eseguire il corrispondente script lato client. Di solito, queste prove sono fatte a mano, ma sono molto lunghe in termini di tempo e particolarmente soggette a errori.

La soluzione si chiama [Selenium](http://seleniumhq.org/), un framework di test scritto interamente in JavaScript. Esegue una serie di azioni su una pagina proprio come farebbe un normale utente. Il vantaggio rispetto all'oggetto `sfBrowser` è che Selenium è in grado di eseguire il codice JavaScript in modo da poter testare anche le interazioni Ajax che avvengono con la pagina.

Selenium non è distribuito di default con symfony. Per installarlo, è necessario creare un nuova cartella  `Selenium/` dentro alla cartella  `web/` e decomprimere il contenuto del pacchetto in questa cartella [archive](http://seleniumhq.org/download/). Questo è perché Selenium si basa su JavaScript e le impostazioni standard di sicurezza  della maggior parte dei browser prevede di non far girare codice che non sia disponibile nello stesso host.

>**ATTENZIONE**
>Attenzione a non trasferire la cartella `selenium/` nel server di produzione, poiché permetterà l'accesso come root a tutti i documenti web attraverso il browser.

I test di Selenium sono scritti in HTML e si trovano nella cartella `web/selenium/tests/`. Per esempio, il listato 15-38 mostra un test funzionale nel quale la pagina è caricata, il link "click me" è cliccato e il testo "Hello, World" è mostrato come risposta. Notare bene che, per poter accedere all'applicazione in ambiente `test`, si deve specificare il controller `frontend_test.php`.

Listato 15-38 - Un semplice test con Selenium, in `web/selenium/test/testIndex.html`

    [php]
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
      <meta content="text/html; charset=UTF-8" http-equiv="content-type">
      <title>Index tests</title>
    </head>
    <body>
    <table cellspacing="0">
    <tbody>
      <tr><td colspan="3">First step</td></tr>
      <tr><td>open</td>              <td>/frontend_test.php/</td> <td>&nbsp;</td></tr>
      <tr><td>clickAndWait</td>      <td>link=click me</td>    <td>&nbsp;</td></tr>
      <tr><td>assertTextPresent</td> <td>Hello, World!</td>    <td>&nbsp;</td></tr>
    </tbody>
    </table>
    </body>
    </html>

Un caso di test è rappresentato da un documento HTML contenente un tabella con tre colonne: comando, obiettivo e valore. Non tutti i comandi prendono un valore e in quel caso si deve lasciare la colonna vuota o usare `&nbsp;`. Consultare la guida di Selenium sul sito di riferimento per maggiori comandi.

È inoltre necessario aggiungere questo test nel insieme dei test inserendo una nuova riga nella tabella nel file `TestSuite.html` posizionato nella stessa cartella. Il listato 15-39 mostra come.

Listato 15-39 - Adding a Test File to the Test Suite, in `web/selenium/test/TestSuite.html`

    ...
    <tr><td><a href='./testIndex.html'>My First Test</a></td></tr>
    ...

Per avviare i test posizionarsi semplicemente con il browser sulla pagina

    http://myapp.example.com/selenium/index.html

Dopo aver selezionato il test cliccare sul bottone corrispondente per avviare i test correlati. Il browser riprodurrà passo passo tutti i comandi che gli sono impartiti dai test.

>**NOTA**
>Poiché i test di Selenium sono lanciati all'interno di un browser reale ciò permette anche di testare le inconsistente che si possono verificare con i diversi browser. Quindi creare il proprio test e verificarlo su tutti i browser nei quali si presuppone che l'applicazione sia utilizzata. 

Il fatto che i test di Selenium siano scritti in HTML potrebbe rendere la loro scrittura un seccatura. Fortunatamente grazie all'[estensione Selenium per Firefox](http://seleniumhq.org/projects/ide/), per creare un test è sufficiente far partire la registrazione della sessione ed effettuare le azioni normalmente sul browser. In più nel menù contestuale che si ottiene con il click destro si possono avere dei comandi avanzati come la verifica dell'esistenza di un particolare testo.

È possibile salvare i test in un file HTML per creare un insieme di test per la propria applicazione. L'estensione di Firefox permette anche di ri-eseguire test registrati in precedenza.

>**NOTA**
>Attenzione a non dimenticare di re-inizializzare i dati di test prima di lanciare i test di Selenium

Riepilogo
-------

I test automatici includono test unitari per validare metodi o funzioni e test funzionali per validare funzionalità. Symfony si basa sul framework di testing lime per i test unitari e fornisce le classi `sfBrowser` e `sfTestFunctional` per i test funzionali. Questi mettono a disposizione molti metodi di asserzione dai più semplici ai più avanzati, come i selettori CSS. Inoltre viene fornita la possibilità di lanciare i test di symfony da riga di comando, sia uno per uno (con il task `test: unit` e `test: functional`) o tutti insieme (con il task `test: all`). Relativamente ai dati i test automatici utilizzano fixture e stub che sono facilmente gestiti con symfony nei test unitari.

Se si è sicuri di scrivere test unitari a sufficienza per coprire la maggior parte della propria applicazione (magari utilizzando la metodologia TDD) le operazioni di rifattorizzazione o di aggiunta di nuove funzionalità saranno fatte con più sicurezza. Inoltre si guadagna anche del tempo, perché, come abbiamo visto, i test sono una buona alternativa alla documentazione.
