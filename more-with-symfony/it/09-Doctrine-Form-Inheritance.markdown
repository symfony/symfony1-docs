Sfruttare l'ereditarietà delle tabelle di Doctrine
==================================================

*Di Hugo Hamon*

Le API di ~Doctrine~ diventano con symfony 1.3 la libreria ORM predefinita ufficiale, 
mentre lo sviluppo di Propel ha subito un rallentamento negli ultimi mesi. Il progetto
~Propel~ continua ad essere supportato e ad essere migliorato grazie allo sforzo
dei membri della comunità di symfony.

Il progetto Doctrine 1.2 è il nuovo ORM predefinito di riferimento per symfony
perché è più facile da usare di Propel e perché include molte caratteristiche
interessanti come i comportamenti (behavior), la facilità delle query DQL, le
migrazioni... e l'ereditarietà della tabelle.

Questo nuovo capitolo descrive cos'è l'~ereditarietà delle tabelle~ e come sono 
pienamente integrate con symfony 1.3. Grazie a un esempio di utilizzo reale, verrà spiegato
come sfruttare l'ereditarietà delle tabelle di Doctrine per rendere il codice più flessibile 
e meglio organizzato.

L'ereditarietà delle tabelle di Doctrine
----------------------------------------

Anche se non è molto conosciuta e utilizzata dagli sviluppatori, l'ereditarietà delle tabelle è
probabilmente una delle caratteristiche più interessanti di Doctrine. Consente
agli sviluppatori di rendere le tabelle SQL ereditabili le une dalle altre allo stesso modo
in cui le classi ereditano le une dalle altre in un linguaggio di programmazione orientato
agli oggetti. L'ereditarietà delle tabelle fornisce un modo semplice per condividere i dati tra
due o più tabelle, in una singola super tabella. Vedere il diagramma qua sotto per capire meglio
il principio di ereditarietà delle tabelle.

![Schema di ereditarietà delle tabelle di Doctrine](http://www.symfony-project.org/images/more-with-symfony/01_table_inheritance.png "Principio di ereditarietà delle tabelle di Doctrine")

Per gestire l'ereditarietà delle tabelle, Doctrine fornisce tre differenti strategie  
a seconda delle esigenze dell'applicazione (prestazioni, atomicità, semplicità, ...) : 
l'ereditarietà delle tabelle __semplice__, con __aggregazione delle colonne__, o __concreta__.
Mentre tutte  queste strategie sono descritte nel libro di Doctrine, alcune
spiegazioni aggiuntive possono aiutare a capire meglio che cosa sono e in quali
circostanze sono utili.

### La strategia Doctrine di ereditarietà semplice delle tabelle

La strategia di ~ereditarietà semplice delle tabelle~ è la più semplice di tutte,
perché memorizza tutte le colonne, comprese le colonne delle tabelle figlie,
nella super tabella genitrice. Se lo schema del modello è quello del seguente
codice YAML, Doctrine genererà un'unica tabella `Person`, che comprende sia le
colonne della tabella `Professor` che quelle della tabella `Student`.

    [yaml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true
        

Con la strategia di ereditarietà semplice, le colonne extra `specialty`, `graduation` 
e `promotion` sono memorizzate automaticamente al livello superiore nel modello `Person`, 
anche se Doctrine genera una classe del modello per entrambe le tabelle `Student` e 
`Professor`.

![Schema di ereditarietà semplice delle tabelle](http://www.symfony-project.org/images/more-with-symfony/02_simple_tables_inheritance.png "Doctrine simple inheritance principle")

Questa strategia ha un importante svantaggio perché la tabella `Person` super genitrice non
fornisce nessuna colonna per identificare il tipo dei record. Quindi non c'è nessuna possibilità
di recuperare solo gli oggetti `Professor` o `Student`. La seguente istruzione 
di Doctrine restituisce un `Doctrine_Collection` di tutti i record della tabella (record di
studenti e professori).

    [php]
    $professors = Doctrine_Core::getTable('Professor')->findAll();

La strategia di ereditarietà semplice delle tabelle non è molto utile nell'utilizzo reale,
perché generalmente c'è la necessità di selezionare e idratare oggetti tipizzati.
Di conseguenza, non sarà più utilizzata in questo capitolo.

### La strategia di ereditarietà delle tabelle con aggregazione delle colonne

L'~ereditarietà delle tabelle con aggregazione delle colonne~ è simile alla strategia di ereditarietà
semplice, salvo che la prima comprende una colonna `type` per identificare i differenti
tipi di record. Di conseguenza, quando un record viene memorizzato nel database, viene collegato
ad esso un valore con il tipo, in modo da sapere a quale classe appartiene.

    [yaml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         1
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         2
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

Nello schema YAML sopra, il tipo di ereditarietà è stato cambiato in 
~`column_aggregation`~ e sono stati aggiunti due nuovi attributi. Il primo
attributo, `keyField`, specifica la colonna che sarà creata per memorizzare il 
tipo di informazione per ciascun record. `keyField` è una colonna obbligatoria di tipo integer 
chiamata `type`, che è il nome predefinito della colonna se non è stato specificato `keyField`. 
Il secondo attributo definisce per ciascun record il valore del tipo, che appartiene alle classi
`Professor` o `Student`.

![Schema dell'elereditarietà di tabelle con aggregazione delle colonne](http://www.symfony-project.org/images/more-with-symfony/03_columns_aggregation_tables_inheritance.png "Doctrine column aggregation inheritance principle")

La strategia di aggregazione delle colonne, è un buon metodo per l'ereditarietà delle tabelle, perché 
crea una singola tabella (`Person`) contente tutti i campi definiti, più il campo `type`. 
Di conseguenza, non c'è bisogno di creare più tabelle e unirle con delle join nelle
query SQL.

Di seguito sono riportati alcuni esempi su come interrogare le tabelle e su quale tipo di
risultati verranno restituiti:

    [php]
    // Restituisce un Doctrine_Collection di oggetti Professor
    $professors = Doctrine_Core::getTable('Professor')->findAll();
    
    // Restituisce un Doctrine_Collection di oggetti Student
    $students = Doctrine_Core::getTable('Student')->findAll();

    // Restituisce un oggetto Professor
    $professor = Doctrine_Core::getTable('Professor')->findOneBySpeciality('physics');

    // Restituisce un oggetto Student
    $student = Doctrine_Core::getTable('Student')->find(42);

    // Restituisce un oggetto Student
    $student = Doctrine_Core::getTable('Person')->findOneByIdAndType(array(42, 2));

Quando si effettua un recupero di dati da una sottoclasse (`Professor`, `Student`), 
Doctrine aggiungerà automaticamente alla query il codice SQL `WHERE` più la clausola 
della colonna `type` con il corrispondente valore.

Tuttavia in alcuni casi, ci sono alcuni svantaggi nell'usare la strategia
di aggregazione delle colonne. Primo, l'aggregazione delle colonne forza ciascun campo
delle sottotabelle a non essere obbligatorio. A seconda di quanti sono i campi,
la tabella `Person` può contenere record con diversi valori vuoti.

Il secondo svantaggio è relativo al numero di sottotabelle e campi. Se lo schema 
dichiara molte sottotabelle, che a sua volta dichiarano un sacco di campi, allora
la super tabella finale sarà composta da un numero molto elevato di colonne. Di conseguenza,
la tabella può essere più difficile da mantenere.

### La strategia concreta di ereditarietà delle tabelle

La strategia ~concreta di ereditarietà delle tabelle~ è un buon compromesso tra la strategia 
di aggregazione delle colonne, le prestazioni e la mantenibilità. Infatti questa strategia
crea tabelle indipendenti per ciascuna sottoclasse, contenenti tutte le colonne: sia le
colonne condivise che le colonne indipendenti del modello.

    [yaml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

Così, per lo schema precedente, la tabella `Professor` generata conterrà
i seguenti campi : `id`, `first_name`, `last_name` e `specialty`.

![Schema di ereditarietà concreta delle tabelle](http://www.symfony-project.org/images/more-with-symfony/04_concrete_tables_inheritance.png "Doctrine concrete inheritance principle")

Questo approccio ha diversi vantaggi nei confronti delle strategie precedenti. Il primo 
è che tutte le tabelle sono isolate e rimangono indipendenti dalle altre. 
Inoltre non ci sono più campi vuoti e la colonna extra `type` non è
inclusa. Il risultato è che ogni tabella è più leggera e isolata dalle altre.

>**NOTE**
>Il fatto che i campi condivisi siano duplicati nelle sottotabelle è un bene per
>prestazioni e scalabilità, perché in questo modo Doctrine non ha bisogno di fare una join SQL 
>automatica su una super tabella, per recuperare i dati condivisi appartenenti a un record 
>di una sottotabella.

Gli unici due svantaggi per la strategia concreta di ereditarietà delle tabelle, sono
la duplicazione dei campi condivisi (ma la duplicazione in generale è la chiave per ottenere
maggiori prestazioni) e la super tabella generata, che rimarrà sempre vuota. Infatti, Doctrine 
ha generato la tabella `Person` anche se non sarà riempita o referenziata da nessuna
query. Nessuna query verrà eseguita su questa tabella, dal momento che tutto è memorizzato
in sottotabelle.

Sono state introdotte le tre strategie di ereditarietà delle tabelle di Doctrine,
ma non sono state provate su un esempio reale con symfony.
La seguente parte del capitolo spiega come trarre vantaggio in symfony 1.3, dalla 
~ereditarietà delle tabelle~ di Doctrine, in particolare dentro al modello e al 
framework dei form.

Integrazione con symfony dell'ereditarietà delle tabelle
--------------------------------------------------------

Prima di symfony 1.3, l'~ereditarietà delle tabelle~ con Doctrine non era pienamente supportata dal 
framework, perché le classi dei filtri e dei form non ereditavano dalla classe base. 
Di conseguenza, gli sviluppatori che avevano bisogno di usare l'ereditarietà erano
forzati a modificare form e filtri ed erano costretti a sovrascrivere molti metodi
per ottenere il comportamento dell'ereditarietà.

Grazie al feedback della comunità, il team del core di symfony ha migliorato form e
filtri in symfony 1.3, in modo da supportare facilmente e integralmente
l'ereditarietà delle tabelle di Doctrine.

Il resto del capitolo spiegherà come usare l'ereditarietà delle tabelle di Doctrine
e come trarne vantaggio in diverse situazioni in modelli, form, filtri e generatori
di amministrazione. Esempi di studi reali aiuteranno a capire meglio come l'ereditarietà
funziona con symfony, in modo che sia possibile usarla per le proprie esigenze.

### Introduzione allo studio di un caso reale

In questo capitolo saranno presentati diversi studi di casi reali per
mostrare i vantaggi dell'approccio dell'ereditarietà delle tabelle di Doctrine
nei diversi livelli: `modelli`, `form`, `filtri` e `generatore di amministrazione`.

Il primo esempio viene da un'applicazione intranet sviluppata da Sensio
per una ben nota società francese. Essa mostra come l'ereditarietà delle tabelle di
Doctrine sia una buona soluzione per gestire una dozzina di identici insiemi referenziali,
al fine di condividere metodi e proprietà ed evitare la duplicazione del codice.

Il secondo esempio mostra come trarre vantaggio dalla strategia di ~ereditarietà
concreta delle tabelle~ con i form, attravero la creazione di un semplice modello
per gestire file binari.

Infine il terzo esempio mostrerà come utilizzare l'ereditarietà delle tabelle
con il generatore di amministrazione e come renderlo più flessibile. Lo studio del caso
mostrato, sarà basato sul primo esempio.


### Ereditarietà delle tabelle nel livello del modello

Similmente al concetto di programmazione orientata agli oggetti, ~l'ereditarietà delle tabelle~ 
incoraggia la condivisione dei dati. Di conseguenza, essa consente la condivisione di proprietà
e metodi quando si ha a che fare con i modelli generati. Usare l'ereditarietà delle tabelle
di Doctrine è un buon modo per condividere e sovrascrivere azioni richiamabili su oggetti ereditati.
Spieghiamo questo concetto con un esempio del mondo reale.

#### Il problema ####

Molte applicazioni web sono vincolate da dati "referenziali" su cui lavorare. 
Generalmente, un referenziale è un piccolo insieme di dati rappresentati da una semplice
tabella contenente almeno due campi (ad esempio `id` e `label`). In alcuni casi,
il referenziale contiene dati aggiuntivi, come un flag `is_active` o `is_default`.
Recentemente, in Sensio, questo è stato il caso di una applicazione per un cliente.

Il cliente voleva gestire una grossa quantità di dati da utilizzare per le
principali form e viste dell'applicazione. Tutte queste tabelle referenziali
sono state costruite intorno allo stesso modello di base: `id`, `label`, `position`
e `is_default`. Il campo `position` consente di classificare i record grazie a
una funzionalità ajax drag and drop. Il campo `is_default` rappresenta un flag
che indica se un record, quando è utilizzato con un select html, deve essere
impostato come "selezionato" per impostazione predefinita.

#### La soluzione ####

La gestione di più di due tabelle uguali è uno dei migliori problemi che si possono risolvere con
l'~ereditarietà delle tabelle~. Nel problema di cui sopra, si è deciso di utilizzare
l'~ereditarietà concreta delle tabelle~ per soddisfare le esigenze e per condividere
metodi di oggetti in una singola classe. Vediamo il seguente schema semplificato, che
illustra il problema.

    [yaml]
    sfReferential:
      columns:
        id:
          type:        integer(2)
          notnull:     true
        label:
          type:        string(45)
          notnull:     true
        position:
          type:        integer(2)
          notnull:     true
        is_default:
          type:        boolean
          notnull:     true
          default:     false

    sfReferentialContractType:
      inheritance:
        type:          concrete
        extends:       sfReferential
    
    sfReferentialProductType:
      inheritance:
        type:          concrete
        extends:       sfReferential

Qui l'ereditarietà concreta delle tabelle funziona perfettamente, perché
fornisce le tabelle separate e isolate e perché il campo `position` deve essere
gestito per record che condividono lo stesso tipo.

Costruiamo il modello e vediamo cosa succede. Doctrine e symfony hanno generato
tre tabelle SQL e sei classi di modelli nella cartella `lib/model/doctrine`:

  * `sfReferential`: gestisce i record della tabella `sf_referential`,
  * `sfReferentialTable`: gestisce la tabella `sf_referential`,
  * `sfReferentialContractType`: gestisce i record della tabella
    `sf_referential_contract_type`.
  * `sfReferentialContractTypeTable`: gestisce la tabella  
    `sf_referential_contract_type`.
  * `sfReferentialProductType`: gestisce i record della tabella
    `sf_referential_product_type`.
  * `sfReferentialProductTypeTable`: gestisce la tabella
    `sf_referential_product_type`.

L'eplorazione del codice generato mostra che entrambe le classi base delle
classi dei modelli `sfReferentialContractType` e `sfReferentialProductType`
ereditano dalla classe `sfReferential`. Così, tutti i metodi protetti e pubblici
(includendo le proprietà) inseriti nella classe `sfReferential` saranno condivisi
tra le due sottoclassi e, se necessario, potranno essere sovrascritti.

Questo è esattamente l'obiettivo previsto. Ora la classe `sfReferential` può
contenere metodi per gestire tutti i dati referenziali, per esempio:

    [php]
    <?php

    // lib/model/doctrine/sfReferential.class.php
    class sfReferential extends BasesfReferential
    {
      public function promote()
      {
        // sposta la riga in su
      }

      public function demote()
      {
        // sposta la riga in giù
      }

      public function moveToFirstPosition()
      {
        // sposta la riga in prima posizione
      }

      public function moveToLastPosition()
      {
        // sposta la riga in ultima posizione
      }

      public function moveToPosition($position)
      {
        // sposta la riga in una specifica posizione
      }

      public function makeDefault($forceSave = true, $conn = null)
      {
        $this->setIsDefault(true);

        if ($forceSave)
        {
          $this->save($conn);
        }
      }
    }

Grazie alla ~ereditarietà concreta delle tabelle~ di Doctrine, tutto il codice è condiviso 
nello stesso posto. Il codice diventa più facile per debug, mantenimento, miglioramento e test unitari.

Questo è il primo vero vantaggio quando si parla di ereditarietà di tabelle.
Grazie a questo approccio, gli oggetti del modello possono essere usati come
illustrato di seguito, dove `sfBaseReferentialActions` è una classe di una azione
speciale ereditata da ogni classe di azione che gestisce un modello referenziale.

    [php]
    <?php

    // lib/actions/sfBaseReferentialActions
    class sfBaseReferentialActions extends sfActions
    {
      /**
       * Azione ajax che salva la nuova posizione a seguito di un
       * drag and drop di un utente nella vista elenco.
       *
       * Questa azione è collegata grazie ad un ~sfDoctrineRoute~ che
       * facilita il recupero dell'oggetto dall'unica referenziazione.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveToPosition(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());

        $referential = $this->getRoute()->getObject();

        $referential->moveToPosition($request->getParameter('position', 1));

        return sfView::NONE;
      }
    }

Che cosa accadrebbe se lo schema non utilizzasse l'ereditarietà di tabelle? Il codice dovrebbe
essere duplicato in ogni sottoclasse referenziale. Questo approccio non sarebbe DRY,
specialmente con una applicazione avente una dozzina di tabelle referenziali.

### Ereditarietà delle tabelle nel livello dei form ###

Continuiamo la visita guidata dei vantaggi dell'ereditarietà delle tabelle di Doctrine.
La sezione precedente ha dimostrato come questa caratteristica può essere molto utile per
condividere metodi e proprietà tra i diversi modelli ereditati. Diamo un'occhiata a come
si comporta quando si tratta di form generati da symfony.

#### Il caso di studio del modello ####

Lo schema YAML qui sotto descrive un modello per la gestione dei documenti binari. L'obiettivo è
quello di memorizzare informazioni generiche nella tabella `File` e dati specifici in sottotabelle
come  `Video` e `PDF`.

    [yaml]
    File:
      columns:
        filename:
          type:            string(50)
          notnull:         true
        mime_type:
          type:            string(50)
          notnull:         true
        description:
          type:            clob
          notnull:         true
        size:
          type:            integer(8)
          notnull:         true
          default:         0

    Video:
      inheritance:
        type:              concrete
        extends:           File
      columns:
        format:
          type:            string(30)
          notnull:         true
        duration:
          type:            integer(8)
          notnull:         true
          default:         0
        encoding:
          type:            string(50)

    PDF:
      tableName:           pdf
      inheritance:
        type:              concrete
        extends:           File
      columns:
        pages:
          type:            integer(8)
          notnull:         true
          default:         0
        paper_size:
          type:            string(30)
        orientation:
          type:            enum
          default:         portrait
          values:          [portrait, landscape]
        is_encrypted:
          type:            boolean
          default:         false
          notnull:         true

Entrambe le tabelle `PDF` e `video` condividono la stessa tabella `File`, che contiene le
informazioni globali sui file binari. Il modello `video` contiene i dati relativi agli
oggetti video come formato (4/3, 16/9, ...) o durata, mentre il modello `PDF` contiene
il numero di pagine o l'orientamento del documento. Costruiamo questo modello
e generiamo i form corrispondenti.

    $ php symfony doctrine:build --all

La sezione seguente descrive come sfruttare al meglio l'eredità delle tabelle
in classi di form grazie al nuovo metodo ~setupInheritance()~.

#### Alla scoperta del metodo ~setupInheritance()~ ###

	
Come previsto, Doctrine ha generato sei classi dei form nelle cartelle
`lib/form/doctrine` e `lib/form/doctrine/base`:

  * `BaseFileForm`
  * `BaseVideoForm`
  * `BasePDFForm`

  * `FileForm`
  * `VideoForm`
  * `PDFForm`

Apriamo le tre classi `Base` dei form e scopriamo qualcosa di nuovo nel
metodo ~`setup()`~. Un nuovo metodo ~`setupInheritance()`~ è stato aggiunto
da symfony 1.3. Questo metodo rimane vuoto per impostazione predefinita.

La cosa più importante da notare è che l'eredità dei form è preservata dal
momento che entrambe `BaseVideoForm` e `BasePDFForm` estendono le classi `FileForm`
e `BaseFileForm`. Di conseguenza, essi ereditano dalla classe di proprietà `File` e
possono condividere gli stessi metodi di base.

Il seguente listato sovrascrive il metodo `setupInheritance()` e configura la classe
`FileForm` in modo che possa essere usata più efficacemente in entrambi i subform.

    [php]
    <?php

    // lib/form/doctrine/FileForm.class.php
    class FileForm extends BaseFileForm
    {
      protected function setupInheritance()
      {
        parent::setupInheritance();

        $this->useFields(array('filename', 'description'));

        $this->widgetSchema['filename']    = new sfWidgetFormInputFile();
        $this->validatorSchema['filename'] = new sfValidatorFile(array(
          'path' => sfConfig::get('sf_upload_dir')
        ));
      }
    }

Il metodo `setupInheritance()`, che viene chiamato da entrambe le sottoclassi 
`VideoForm` e `PDFForm`, rimuove tutti i campi eccetto `filename` e `description`. 
Il campo del widget `filename` è stato trasformato in un widget di file e il suo
corrispondente validatore è stato cambiato in un validatore ~`sfValidatorFile`~.
In questo modo, l'utente sarà in grado di caricare un file e salvarlo sul server.

![Personalizzare form ereditati con  il metodo setupInheritance()](http://www.symfony-project.org/images/more-with-symfony/05_table_inheritance_forms.png "Doctrine table inheritance with forms")

#### Impostare la dimensione e il mime type del file corrente

Ora tutti i form sono pronti per essere personalizzati. C'è ancora una cosa da configurare
per essere in grado di usarli. Siccome i campi `mime_type` e `size` sono stati
rimossi dall'oggetto `FileForm`, essi devono essere impostati a livello di codice.
Il posto migliore per farlo è in un nuovo metodo `generateFilenameFilename()`
nella classe `File`.

    [php]
    <?php

    // lib/model/doctrine/File.class.php
    class File extends BaseFile
    {
      /**
       * Genera un nome file per l'oggetto corrente del file.
       *
       * @param sfValidatedFile $file
       * @return string
       */
      public function generateFilenameFilename(sfValidatedFile $file)
      {
        $this->setMimeType($file->getType());
        $this->setSize($file->getSize());

        return $file->generateFilename();
      }
    }

Questo nuovo metodo ha lo scopo di generare un nome personalizzato per il file
da memorizzare sul file system. Il metodo `generateFilenameFilename()`
nella modalità predefinita restituisce un nome file auto generato, imposta al volo
le proprietà del tipo mime e la dimensione grazie all'oggetto ~`sfValidatedFile`~ passato
come primo parametro.

Siccome symfony 1.3 supporta pienamente l'ereditarietà delle tabelle di Doctrine, ora i
form sono in grado di salvare un oggetto e i suoi valori ereditati. Il supporto nativo
all'ereditarietà aiuta a scrivere form potenti e funzionali con pochi blocchi di
codice personalizzato.

L'esempio di cui sopra potrebbero essere ampiamente e facilmente migliorato, grazie
all'ereditarietà delle classi. Per esempio, entrambe le classi `VideoForm` and `PDFForm`
possono sovrascrivere il validatore `filename` per avere più validatori specifici  
e personalizzati come `sfValidatorVideo` o `sfValidatorPDF`.

### Ereditarietà delle tabelle a livello di filtri ###

Siccome i filtri sono anche form, anche loro ereditano metodi e proprietà
dei filtri form genitrici. Di conseguenza gli oggetti `VideoFormFilter` e `PDFFormFilter`
estendono la classe `FileFormFilter` e possono essere personalizzati utilizzando
il metodo ~`setupInheritance()`~.

Allo stesso modo, sia `VideoFormFilter` che `PDFFormFilter` possono condividere
gli stessi metodi personalizzati nella classe `FileFormFilter`.

### Ereditarietà di tabelle a livello di generatore di amministrazione ###

É giunto il momento di scoprire come sfruttare l'ereditarietà delle tabelle di Doctrine
e una delle nuove funzionalità del generatore di amministrazione: la definizione della
__classe base delle azioni__. Il generatore di amministrazione è una delle funzionalità più
apprezzate di symfony a partire dalla versione 1.0.

Nel novembre 2008, symfony ha introdotto il nuovo sistema di generatore dell'amministrazione
inserendolo nella versione 1.2 di symfony. Questo strumento è dotato di molte funzionalità
out of the box, come operazioni CRUD di base, elenchi filtrati e paginati, cancellazione in batch
e così via... Il generatore di amministativo è un potente strumento per ogni sviluppatore
perché facilita e accelera la generazione di backend e la loro personalizzazione.

#### Un esempio pratico introduttivo

L'obiettivo della parte finale di questo capitolo è quello di mostrare come sfruttare al meglio
l'eredità delle tabelle di Doctrine in coppia con lo strumento di generatore dell'amministrazione.
Per raggiungere lo scopo, sarà spiegato come costruire una semplice area di backend che gestisce
due tabelle, che possono entrambe contenere dati da ordinare tra di loro.

Siccome il mantra di symfony è quello di non dover ogni volta reinventare la ruota, il modello
di Doctrine userà il [csDoctrineActAsSortablePlugin](http://www.symfony-project.org/plugins/csDoctrineActAsSortablePlugin "csDoctrineActAsSortablePlugin plugin page")
per fornire tutte le api necessarie a ordinare gli oggetti tra di loro. Il
plugin ~`csDoctrineActAsSortablePlugin`~ è sviluppato e gestito da Centre{source},
una delle società più attive nell'ecosistema symfony.

Il modello dei dati è abbastanza semplice. Ci sono tre classi di modelli `sfItem`,
`sfTodoItem` e `sfShoppingItem`, che contribuiscono a gestire una elenco delle cose da fare e
un elenco degli acquisti. Ogni voce di entrambe le liste è ordinabile in base a quelle dell'altra,
in modo ad esempio da concentrarsi sulla priorità.

    [yml]
    sfItem:
      columns:
        name:
          type:          string(50)
          notnull:       true

    sfTodoItem:
      actAs:             [Sortable, Timestampable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        priority:
          type:          string(20)
          notnull:       true
          default:       minor
        assigned_to:
          type:          string(30)
          notnull:       true
          default:       me

    sfShoppingItem:
      actAs:             [Sortable, Timestampable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        quantity:
          type:          integer(3)
          notnull:       true
          default:       1

Lo schema sopra descrive il modello dei dati diviso in tre classi di modelli. Le due
classi figlie (`sfTodoItem`, `sfShoppingItem`) hanno entrambe i comportamenti _sortable_
e _timestampable_. Il comportamento dell'ordinamento è fornito dal plugin
`csDoctrineActAsSortablePlugin` e aggiunge una colonna `position` di tipo integer a
ciascuna tabella. Entrambe le classi estendono la classe base `sfItem`. Questa classe
contiene le colonne `id` e `name`.

Aggiungiamo alcune fixture di dati da inserire in entrambe le tabelle per avere un po' di
dati per i test da utilizzare per i due backend generati. I dati fixture sono, come al solito,
nel file `data/fixtures.yml` del progetto symfony.

    [yml]
    sfTodoItem:
      sfTodoItem_1:
        name:           "Scrivere un nuovo libro su symfony"
        priority:       "medium"
        assigned_to:    "Fabien Potencier"
      sfTodoItem_2:
        name:           "Rilasciare Doctrine 2.0"
        priority:       "minor"
        assigned_to:    "Jonathan Wage"
      sfTodoItem_3:
        name:           "Rilasciare symfony 1.4"
        priority:       "major"
        assigned_to:    "Kris Wallsmith"
      sfTodoItem_4:
        name:           "Scrivere la documentazione per le API del core di Lime 2"
        priority:       "medium"
        assigned_to:    "Bernard Schussek"

    sfShoppingItem:
      sfShoppingItem_1:
        name:           "Apple MacBook Pro 15.4 pollici"
        quantity:       3
      sfShoppingItem_2:
        name:           "Disco rigido esterno da 320 GB"
        quantity:       5
      sfShoppingItem_3:
        name:           "Tastiera USB"
        quantity:       2
      sfShoppingItem_4:
        name:           "Stampante Laser"
        quantity:       1

Una volta che il plugin `csDoctrineActAsSortablePlugin` è installato e il modello dei dati
è pronto, il nuovo plugin, per essere caricato e utilizzato, deve essere attivato nella
classe ~`ProjectConfiguration`~, presente nel file `config/ProjectConfiguration.class.php`:

    [php]
    <?php

    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
    sfCoreAutoload::register();

    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins(array(
          'sfDoctrinePlugin',
          'csDoctrineActAsSortablePlugin'
        ));
      }
    }

Ora il database, il modello, le classi dei form e dei filtri possono essere generate e le
fixture caricate nel database per riempire le nuove tabelle create. Tutte queste cose
possono essere realizzate in una volta grazie al task ~`doctrine:build-all-reload`~.

    $ php symfony doctrine:build-all-reload --no-confirmation

Per terminare il processo, la cache di symfony deve essere pulita  e le risorse del plugin
devono essere copiate sotto la cartella `web/`:

    $ php symfony cache:clear
    $ php symfony plugin:publish-assets

La parte seguente spiega come costruire tutti i moduli di backend, grazie agli
strumenti del generatore di amministrazione e come beneficiare di una nuova funzionalità
integrata.

#### Configurare il backend

	
Questa sezione descrive il processo necessario per la creazione di una nuova
applicazione backend contenente due moduli generati per la gestione sia degli acquisti
che dell'elenco delle cose da fare.
Di conseguenza, il primo passo è quello di generare una applicazione `backend`
che possa contenere i moduli che creeremo:

    $ php symfony generate:app backend

Anche se il generatore di amministrazione è un buon strumento, lo sviluppatore è
sempre stato costretto a duplicare il codice in comune tra i due moduli generati.
Grazie a symfony 1.3 il task ~`doctrine:generate-admin`~ ora introduce una nuova opzione
~`--actions-base-class`~ che permette di definire la classe del modulo base delle azioni.

Siccome i due moduli sono abbastanza simili, avranno certamente da condividere del
codice generico per le azioni. Questo codice può essere posizionato in una super classe
delle azioni, posizionata nella cartella `lib/actions` come mostrato nel codice sotto:

    [php]
    <?php

    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {

    }

Una volta che la nuova classe `sfSortableModuleActions` viene creata e la cache pulita, i
due moduli possono essere generati nell'applicazione backend:

    $ php symfony doctrine:generate-admin --module=shopping --actions-base-class=sfSortableModuleActions backend sfShoppingItem
    $ php symfony doctrine:generate-admin --module=todo --actions-base-class=sfSortableModuleActions backend sfTodoItem

Il generatore di amministrazione genera i moduli in due cartelle separate. La prima
è ovviamente `apps/backend/modules`, ma la maggioranza dei file dei moduli generati
sono posizionati nella cartella `cache/backend/dev/modules`. I file posizionati
in questo posto sono rigenerati ogni volta che la cache viene cancellata o quando
cambia la configurazione del modulo.

>**Note**
>L'esplorazione dei file memorizzati nella cache è una buona pratica per capire come
>symfony e il generatore di amministrazione lavorano sotto il cofano. Di conseguenza, 
>la nuova classe ereditata `sfSortableModuleActions` può essere trovata nei file
>`cache/backend/dev/modules/autoShopping/actions/actions.class.php`
>e `cache/backend/dev/modules/autoTodo/actions/actions.class.php`. Per
>impostazione predefinita symfony dovrebbe creare entrambe le classi ereditate da ~`sfActions`~.

![Backend predefinito con l'elenco delle cose da fare](http://www.symfony-project.org/images/more-with-symfony/06_table_inheritance_backoffice_todo_1.png "Todo list default backend")

![Backend predefinito con l'elenco delle cose da acquistare](http://www.symfony-project.org/images/more-with-symfony/07_table_inheritance_backoffice_shopping_1.png "Shopping list default backend")

I due moduli di backend sono pronti per essere utilizzati e personalizzati. Non è l'obiettivo
di questo capitolo imparare a configurare un modulo auto generato. Un sacco di documentazione
(Jobeet, La guida definitiva a symfony, ecc.) spiegano come raggiungere lo scopo in pochi minuti.

#### Cambiare la posizione di un elemento

La sezione precedente ha descritto come configurare due moduli di backend pienamente funzionali,
che ereditano entrambi dalla stessa classe di azioni. Il nuovo obiettivo è quello di creare una
azione condivisa, che consenta allo sviluppatore di ordinare tra di loro oggetti da una lista.
Questo è abbastanza facile visto che il plugin installato fornisce una API per realizzare questo,
su una classe del modello.

Il primo passo è quello di due nuove rotte in grado di spostare un record in alto o in basso
nell'elenco. Dal momento che il generatore di amministrazione usa ~`sfDoctrineRouteCollection`~,
le nuove rotte possono facilmente essere dichiarate e attaccate ad esso nel file `config/generator.yml`
di entrambi i moduli:

    [yml]
    # apps/backend/modules/shopping/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfShoppingItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_shopping_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      999
            sort:              [position, asc]
            display:           [position, name, quantity]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Ripetere le modifiche per il modulo `todo`:

    [yml]
    # apps/backend/modules/todo/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfTodoItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_todo_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      999
            sort:              [position, asc]
            display:           [position, name, priority, assigned_to]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

I due file YAML descrivono la configurazione per entrambi i moduli `shopping`
e `todo`. Ciascuno di questi è stato personalizzato per adattarsi alle esigenze
dell'utente finale. In primo luogo, la vista elenco è ordinata per la colonna
`position` con un ordine `ascendant`. Quindi, il numero massimo di elementi per
pagina è stato aumentato a 999 per evitare l'impaginazione.

Infine, il numero di colonne visualizzate è stato ridotto alle colonne
`position`, `name`, `priority`, `assigned_to` e `quantity`; tutte ora hanno  
due nuove azioni: `moveUp` e `moveDown`. La resa finale dovrebbe essere simile alle
seguenti schermate.

![Backend personalizzato con l'elenco delle cose da fare](http://www.symfony-project.org/images/more-with-symfony/09_table_inheritance_backoffice_todo_2.png "Todo list custom backend")

![Backend personalizzato con l'elenco delle cose da acquistare](http://www.symfony-project.org/images/more-with-symfony/08_table_inheritance_backoffice_shopping_2.png "Shopping list custom backend")

Queste due nuove azioni per ora sono solo dichiarate e non fanno nulla. Devono
essere create nella classe condivisa delle azioni, `sfSortableModuleActions` come
descritto sotto. Il plugin ~`csDoctrineActAsSortablePlugin`~ fornisce due utili
metodi extra sulle classi di modelli `sfShoppingItem` e `sfTodoItem`: `promote()`
e `demote()`. Entrambi sono utilizzati per costruire le azioni `moveUp` e `moveDown`.

    [php]
    <?php

    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Sposta un oggetto della lista in su
       *
       * @param sfWebRequest $request
       */
      public function executeMoveUp(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->promote();

        $this->redirect($this->getModuleName());
      }

      /**
       * Sposta un oggetto della lista in giù
       *
       * @param sfWebRequest $request
       */
      public function executeMoveDown(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->demote();

        $this->redirect($this->getModuleName());
      }
    }

Grazie a queste due semplici azioni condivise, sia l'elenco delle cose da fare che
l'elenco della spesa sono ordinabili. Inoltre sono facili da mantenere e da testare
con i test funzionali. Sentitevi liberi di migliorare l'estetica e l'usabilità di
entrambi i moduli, sovrascrivendo gli oggetti template delle azioni per rimuovere
il primo collegamento `sposta in su` e l'ultimo collegamento `sposta in giù`.

#### Regalo speciale: migliorare l'esperienza dell'utente

Prima di terminare, rifiniamo i due elenchi per migliorare l'esperienza dell'utente. 
Tutti concordano sul fatto che spostando i record in su (o in giù) cliccando su un link
non è proprio intuitivo per l'utente finale. Un approccio migliore è quello di introdurre
comportamenti JavaScript ajax. In questo caso, tutte le righe HTML delle tabelle potranno
utilizzare il drag & drop grazie al plugin jQuery ~`tableDnD`~. Una chiamata ajax
sarà eseguita quando l'utente si fermerà di muovere una riga nella tabella HTML.

Per prima cosa, scaricare e installare il framework jQuery sotto la cartella `web/js` e poi
ripetere l'operazione per il plugin `tableDnD`, il cui codice sorgente è ospitato su un
repository di [Google Code](http://code.google.com/p/tablednd/).

Per lavorare, il visualizzatore dell'elenco di ogni modulo deve includere il frammento
JavaScript ed entrambe le tabelle hanno bisogno di un attributo `id`. Come tutti i
template del generatore di amministrazione. Siccome nel generatore di amministrazione
i template e i partial possono essere sovrascritti, il file `_list.php`, presente
nella cache per impostazione predefinita, deve essere copiato in entrambi i moduli.

Ma ragioniamo un attimo... copiare il file `_list.php` sotto la cartella `templates/` di entrambi i
moduli non è molto DRY. Basta copiare il file `cache/backend/dev/modules/autoShopping/templates/_list.php`
sotto la cartella `apps/backend/templates/` e rinominarlo `_table.php`.
Sostituire quindi il suo contenuto attuale con il seguente codice:

    [php]
    <div class="sf_admin_list">
      <?php if (!$pager->getNbResults()): ?>
        <p><?php echo __('No result', array(), 'sf_admin') ?></p>
      <?php else: ?>
        <table cellspacing="0" id="sf_item_table">
          <thead>
            <tr>
              <th id="sf_admin_list_batch_actions"><input id="sf_admin_list_batch_checkbox" type="checkbox" onclick="checkAll();" /></th>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_th_tabular',
                array('sort' => $sort)
              ) ?>
              <th id="sf_admin_list_th_actions">
                <?php echo __('Actions', array(), 'sf_admin') ?>
              </th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th colspan="<?php echo $colspan ?>">
                <?php if ($pager->haveToPaginate()): ?>
                  <?php include_partial(
                    $sf_request->getParameter('module').'/pagination',
                    array('pager' => $pager)
                  ) ?>
                <?php endif; ?>
                <?php echo format_number_choice(
                  '[0] no result|[1] 1 result|(1,+Inf] %1% results', 
                  array('%1%' => $pager->getNbResults()),
                  $pager->getNbResults(), 'sf_admin'
                ) ?>
                <?php if ($pager->haveToPaginate()): ?>
                  <?php echo __('(page %%page%%/%%nb_pages%%)', array(
                    '%%page%%' => $pager->getPage(), 
                    '%%nb_pages%%' => $pager->getLastPage()), 
                    'sf_admin'
                  ) ?>
                <?php endif; ?>
              </th>
            </tr>
          </tfoot>
          <tbody>
          <?php foreach ($pager->getResults() as $i => $item): ?>
            <?php $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
            <tr class="sf_admin_row <?php echo $odd ?>">
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_batch_actions',
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item,
                  'helper' => $helper
              )) ?>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_tabular', 
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item
              )) ?>
                <?php include_partial(
                  $sf_request->getParameter('module').'/list_td_actions',
                  array(
                    'sf_'. $sf_request->getParameter('module') .'_item' => $item, 
                    'helper' => $helper
                )) ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      </div>
      <script type="text/javascript">
        /* <![CDATA[ */
        function checkAll() {
          var boxes = document.getElementsByTagName('input'); 
          for (var index = 0; index < boxes.length; index++) { 
            box = boxes[index]; 
            if (
              box.type == 'checkbox' 
              && 
              box.className == 'sf_admin_batch_checkbox'
            ) 
            box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked 
          }
          return true;
        }
        /* ]]> */
      </script>

Infine, creare nella cartella `templates/` di ciascun modulo i file `_list.php` ,
che contengano entrambi il seguente codice:

    [php]
    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 5
    )) ?>

    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 8
    )) ?>

Per modificare la posizione di una riga, entrambi i moduli necessitano di implementare
una nuova azione che elabori la richiesta ajax entrante. Come visto in precedenza, la
nuova azione condivisa `executeMove()` sarà inserita nella classe delle azioni
`sfSortableModuleActions`:

    [php]
    <?php

    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Esegue la richiesta ajax, sposta l'oggetto in una nuova posizione.
       *
       * @param sfWebRequest $request
       */
      public function executeMove(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());
        $this->forward404Unless($item = Doctrine_Core::getTable($this->configuration->getModel())->find($request->getParameter('id')));

        $item->moveToPosition((int) $request->getParameter('rank', 1));

        return sfView::NONE;
      }
    }

L'azione `executeMove()` richiede un metodo `getModel()` nella configurazione
dell'oggetto. Implementare questo nuovo metodo in entrambe le classi 
`todoGeneratorConfiguration` e `shoppingGeneratorConfiguration`, come mostrato sotto:

    [php]
    <?php

    // apps/backend/modules/shopping/lib/shoppingGeneratorConfiguration.class.php
    class shoppingGeneratorConfiguration extends BaseShoppingGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfShoppingItem';
      }
    }

    // apps/backend/modules/todo/lib/todoGeneratorConfiguration.class.php
    class todoGeneratorConfiguration extends BaseTodoGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfTodoItem';
      }
    }

C'è ancora un'ultima funzionalità mancante da realizzare. Per ora, le righe delle
tabelle non sono trascinabili e inoltre non è eseguita nessuna chiamata ajax quando una
riga spostata viene rilasciata. Per realizzare questo, entrambi i moduli hanno bisogno
di una rotta specifica per accedere alle loro corrispondenti azioni `move`. Di
conseguenza, il file `apps/backend/config/routing.yml` può accogliere le due nuove
rotte, come mostrato sotto:

    [php]
    <?php foreach (array('shopping', 'todo') as $module) : ?>

    <?php echo $module ?>_move:
      class: sfRequestRoute
      url: /<?php echo $module ?>/move
      param:
        module: "<?php echo $module ?>"
        action: move
      requirements:
        sf_method: [get]

    <?php endforeach ?>

Per evitare una duplicazione del codice, le due rotte sono generate dentro
al costrutto `foreach` e sono basate sul nome del modulo, per recuperarlo facilmente
nella vista. Infine, `apps/backend/templates/_table.php` deve implementare il
frammento JavaScript per gestire la caratteristica del drag and drop e la richiesta
ajax:

    [php]
    <script type="text/javascript" charset="utf-8">
      $().ready(function() {
        $("#sf_item_table").tableDnD({
          onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;

            // prende l'ide dell'oggetto spostato
            var movedId = $(row).find('td input:checkbox').val();

            // calcola la nuova posizione della riga
            var pos = 1;
            for (var i = 0; i<rows.length; i++) {
              var cells = rows[i].childNodes;
              // esegue la richiesta ajax per la nuova posizione
              if (movedId == $(cells[1]).find('input:checkbox').val()) {
                $.ajax({
                  url:"<?php echo url_for('@'. $sf_request->getParameter('module').'_move') ?>?id="+ movedId +"&rank="+ pos,
                  type:"GET"
                });
                break;
              }
              pos++;
            }
          },
        });
      });
    </script>

La tabella HTML è ora pienamente funzionante. Le righe implementano il drag & drop
e la nuova posizione di una riga è automaticamente salvata grazie ad una chiamata ajax.
Con pochi pezzi di codice, l'usabilità del backend è stata ampiamente migliorata per
offrire all'utente finale una migliore esperienza. Il generatore di amministrazione è
abbastanza flessibile per essere esteso e personalizzato e inoltre lavora perfettamente
con l'ereditarietà delle tabelle di Doctrine.

Sentitevi liberi di migliorare i due backend rimuovendo le vecchie azioni `moveUp` e
`moveDown` e cercando di personalizzarli a vostro piacimento.

Conclusioni
-----------

Questo capitolo ha descritto come l'~ereditarietà delle tabelle~ di Doctrine
sia una caratteristica potente, che consente agli sviluppatori di scrivere codice
più velocemente e migliorare l'organizzazione del codice. Questa funzionalità di
Doctrine è pienamente integrata a diversi livelli in symfony.
Gli sviluppatori ora sono realmente incoraggiati ad approfittarne per migliorare
il codice in termini di efficienza e di organizzazione.
