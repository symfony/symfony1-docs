L'utilizzo avanzato dei form
============================

*di Ryan Weaver, Fabien Potencier*

Il framework dei form di symfony viene in aiuto allo sviluppatore con strumenti
necessari a visualizzare e validare i form in modo semplice e orientato agli oggetti.
Grazie alle classi ~`sfFormDoctrine`~ e ~`sfFormPropel`~ disponibili per ciascun ORM,
il framework dei form può facilmente visualizzare e salvare form che fanno
riferimento allo strato dei dati.

Le situazioni del mondo reale, tuttavia, spesso richiedono agli sviluppatori di
personalizzare ed estendere i form. In questo capitolo vengono presentati e risolti
alcuni tra i problemi più comuni, ma impegnativi, sui form.
Si vedrà anche il funzionamento dell'oggetto ~`sfForm`~, svelando alcuni
dei suoi misteri.

Mini-Progetto: Prodotti e foto
-------------------------------

Il primo problema ruota attorno alla possibilità di gestire un singolo prodotto
e un numero illimitato di foto per tale prodotto. L'utente deve essere in grado di
modificare sia il prodotto che le foto sulla stessa form. Si avrà anche la necessità
di permettere all'utente di caricare fino a due foto del nuovo prodotto nello stesso
momento. Ecco un possibile schema:

    [yml]
    Product:
      columns:
        name:           { type: string(255), notnull: true }
        price:          { type: decimal, notnull: true }

    ProductPhoto:
      columns:
        product_id:     { type: integer }
        filename:       { type: string(255) }
        caption:        { type: string(255), notnull: true }
      relations:
        Product:
          alias:        Product
          foreignType:  many
          foreignAlias: Photos
          onDelete:     cascade

Quando terminato, il form apparirà simile a questo:

![Form del prodotto e delle foto](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_01.png "Form del prodotto e delle foto")

Imparare di più facendo esempi
--------------------------------

Il modo migliore per imparare le tecniche avanzate è quello di seguire e testare
gli esempi passo passo. Grazie alla funzionalità `--installer` di [symfony](#chapter_03),
viene fornito un modo semplice per creare un progetto funzionante, con un database
SQLite pronto per essere usato, lo schema del database di Doctrine, alcune fixture,
una applicazione `frontend` e un modulo `product` con cui lavorare. 
Scaricare lo script di installazione
[script](http://www.symfony-project.org/images/more-with-symfony/advanced_form_installer.php.src)
ed eseguire il seguente comando per creare il progetto symfony:

    $ php symfony generate:project advanced_form --installer=/path/to/advanced_form_installer.php

Questo comando crea un progetto pienamente funzionante, con lo schema per il database
che è stato introdotto nel precedente paragrafo.

>**NOTE**
>In questo capitolo, i percorsi dei file sono quelli di un progetto symfony che viene
>eseguito con Doctrine, così come è stato generato dal precedente task.

Configurazione di base del form
-------------------------------

Poiché i requisiti comportano modifiche a due diversi modelli (`Product`
e `ProductPhoto`), la soluzione richiederà di incorporare due differenti form di
symfony (`ProductForm` e `ProductPhotoForm`). Per fortuna, il framework dei form
può facilmene combinare form multipli in un unico form, attraverso ~`sfForm::embedForm()`~.
In primo luogo, impostare `ProductPhotoForm` in modo indipendente. In questo esempio,
si usa il campo `filename` come campo per l'upload dei file:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setWidget('filename', new sfWidgetFormInputFile());
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
      )));
    }

Per questo form, entrambi i campi `caption` e `filename` sono automaticamente
richiesti, ma per motivi diversi. Il campo `caption` è richiesto perché la
colonna correlata nello schema del database è stata definita con una proprietà
`notnull` impostata a `true`. Il campo `filename` è richiesto per impostazione
predefinita, perché il default di un oggetto validatore è `true` per una opzione
`required`.

>**NOTE**
>~`sfForm::useFields()`~ è una nuova funzione di symfony 1.3 che consente allo
>sviluppatore di specificare esattamente quali campi dovrebbe usare il form e
>in quale ordine devono essere visualizzati. Tutti gli altri campi non hidden
>vengono rimossi dal form.

Finora non si è fatto niente di più che una normale configurazione del form.
Ora verranno combinati i form in uno unico.

Unire i form
------------

Utilizzando ~`sfForm::embedForm()`~, i form indipendenti `ProductForm` e
`ProductPhotoForms` possono essere uniti con molto poco sforzo. Il lavoro viene
sempre fatto nel form *principale*, che in questo caso è `ProductForm`. Le
richieste sono la possibilità di caricare sul server fino a due foto del
prodotto in una sola volta. Per fare questo, bisogna unire due oggetti
`ProductPhotoForm` in `ProductForm`:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $subForm = new sfForm();
      for ($i = 0; $i < 2; $i++)
      {
        $productPhoto = new ProductPhoto();
        $productPhoto->Product = $this->getObject();

        $form = new ProductPhotoForm($productPhoto);

        $subForm->embedForm($i, $form);
      }
      $this->embedForm('newPhotos', $subForm);
    }

Se si punta il browser al modulo `product`, si avrà la possibilità di caricare
due `ProductPhoto` così come modificare l'oggetto stesso `Product`.
Symfony salva automaticamente i nuovi oggetti `ProductPhoto` e li collega al
corrispondente oggetto `Product`. Anche l'upload dei file, definito in 
`ProductPhotoForm`, viene eseguito normalmente.

Verificare che i record siano salvati correttamente nel database:

    $ php symfony doctrine:dql --table "FROM Product"
    $ php symfony doctrine:dql --table "FROM ProductPhoto"

Nella tabella `ProductPhoto` si potranno trovare i nomi dei file delle foto.
Tutto funziona come previsto, perché nel database si possono trovare i file con gli
stessi nomi di quelli presenti nella cartella `web/uploads/products/`.

>**NOTE**
>Poiché i campi `filename` e `caption` sono obbligatori in `ProductPhotoForm`,
>la validazione del form principale fallirà sempre, a meno che l'utente non
>carichi due nuove foto. Continuare la lettura per capire come risolvere questo problema

Rifattorizzazione
-----------------

Anche se il form precedente funziona come previsto, sarebbe meglio rifattorizzare
il codice, per facilitare i test e per permettere al codice di essere
facilmente riutilizzato.

Prima di tutto, creare un nuovo form che rappresenta un insieme di
`ProductPhotoForm`, basato sul codice che è già stato scritto:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    class ProductPhotoCollectionForm extends sfForm
    {
      public function configure()
      {
        if (!$product = $this->getOption('product'))
        {
          throw new InvalidArgumentException('You must provide a product object.');
        }

        for ($i = 0; $i < $this->getOption('size', 2); $i++)
        {
          $productPhoto = new ProductPhoto();
          $productPhoto->Product = $product;

          $form = new ProductPhotoForm($productPhoto);

          $this->embedForm($i, $form);
        }
      }
    }

Questo form ha bisogno di due opzioni:

 * `product`: Il prodotto per il quale creare una collezione di
   `ProductPhotoForm`;

 * `size`: Il numero di `ProductPhotoForm` da creare (predefinito a due).

È ora possibile modificare il metodo di configurazione di `ProductForm` come segue:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $form = new ProductPhotoCollectionForm(null, array(
        'product' => $this->getObject(),
        'size'    => 2,
      ));

      $this->embedForm('newPhotos', $form);
    }

Uno sguardo all'interno dell'oggetto sfForm
-------------------------------------------

Al livello più elementare, un form web è un insieme di campi che sono visualizzati
ed inviati al server. Sotto questa luce, l'oggetto ~`sfForm`~ è
essenzialmente un array di *campi* di form. Mentre ~`sfForm`~ gestisce il processo,
i singoli campi sono responsabili nel definire come ciascuno verrà visualizzato
e validato.

In symfony, ciascun *campo* del form è definito da due diversi oggetti:

  * Un *widget* che mostra i campi del form con il markup XHTML;

  * Un *validator* che pulisce e valida i dati del campo inviato.

>**TIP**
>In symfony, un *widget* è definito come un qualunque oggetto, il cui unico
>compito è quello di mostrare in output un codice XHTML. Anche se in genere è
>utilizzato con i form, un oggetto widget potrebbe essere creato per riprodurre
>qualsiasi codice.

### Un form è un array

Bisogna ricordare che l'oggetto ~`sfForm`~ è "essenzialmente un array di *campi*
di form". Per essere più precisi, `sfForm` utilizza sia un array di widget che
un array di validatori per tutti i campi del form. Questi due array, chiamati
`widgetSchema` e `validatorSchema` sono proprietà della classe `sfForm`.
Per aggiungere un campo a un form, bisogna semplicemente aggiungere il
widget dei campi all'array `widgetSchema` e il validatore dei campi all'array
`validatorSchema`. Per esempio, il seguente codice aggiungerà un campo `email`
a un form:

    [php]
    public function configure()
    {
      $this->widgetSchema['email'] = new sfWidgetFormInputText();
      $this->validatorSchema['email'] = new sfValidatorEmail();
    }

>**NOTE**
>Gli array `widgetSchema` e `validatorSchema` in realtà sono classi speciali 
>chiamate ~`sfWidgetFormSchema`~ e ~`sfValidatorSchema`~, che implementano
>l'interfaccia `ArrayAccess`.

### Uno sguardo all'interno di `ProductForm`

Poiché la classe `ProductForm` alla fine estende `sfForm`, essa ospita anche
tutti i suoi widget e validatori negli array `widgetSchema` e `validatorSchema`.
Si può vedere, nell'oggetto `ProductForm`, come ciascun array è organizzato.

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
      ),
    )

>**TIP**
>Proprio come `widgetSchema` e `validatorSchema` sono in realtà oggetti che si
>comportano come array, gli array di cui sopra, definiti dalle chiavi `newPhotos`,
>`0` e `1`, sono anche oggetti `sfWidgetSchema` e `sfValidatorSchema`.

Come previsto, i campi di base (`id`, `name` e `price`) sono rappresentati nel
primo livello di ciascun array. In un form che non incorpora altri form, entrambi
gli array `widgetSchema` e `validatorSchema` hanno solo un livello, che rappresenta
i campi di base del form. Come visto sopra, i widget e i validatori di eventuali
form incorporati sono rappresentati come array figli in `widgetSchema` e
`validatorSchema`. Il metodo che gestisce questo processo è spiegato più avanti.

### Dietro a ~`sfForm::embedForm()`~

Bisogna sempre ricordare che un form è composto da una serie di widget e da una
serie di validatori. Incorporare un form in un altro, in sostanza, vuol dire
che gli array dei widget e dei validatori di un form sono aggiunti agli array
dei widget e dei validatori del form principale. Questa procedura è interamente
realizzata da `sfForm::embedForm()`. Il risultato, come visto sopra, è sempre
una aggiunta multi-dimensionale agli array `widgetSchema` e `validatorSchema`.

Di seguito si vedrà la configurazione di `ProductPhotoCollectionForm`, che lega
i singoli oggetti `ProductPhotoForm` con sé stesso. Questo form intermedio agisce
come "wrapper" e aiuta nell'organizzazione complessiva del form. Si può iniziare con
il seguente codice da `ProductPhotoCollectionForm::configure()`:

    [php]
    $form = new ProductPhotoForm($productPhoto);
    $this->embedForm($i, $form);

Il form `ProductPhotoCollectionForm` stesso inizia come un nuovo oggetto `sfForm`.
In quanto tale, gli array `widgetSchema` e `validatorSchema` sono vuoti.

    [php]
    widgetSchema    => array()
    validatorSchema => array()

Ogni `ProductPhotoForm`, tuttavia, è già pronto con tre campi (`id`, `filename`,
e `caption`) e tre corrispondenti oggetti negli array `widgetSchema` e
`validatorSchema`.

    [php]
    widgetSchema    => array
    (
      [id]            => sfWidgetFormInputHidden,
      [filename]      => sfWidgetFormInputFile,
      [caption]       => sfWidgetFormInputText,
    )

    validatorSchema => array
    (
      [id]            => sfValidatorDoctrineChoice,
      [filename]      => sfValidatorFile,
      [caption]       => sfValidatorString,
    )

Il metodo ~`sfForm::embedForm()`~ aggiunge semplicemente gli array `widgetSchema`
e `validatorSchema` di ogni `ProductPhotoForm` agli array `widgetSchema` e
`validatorSchema` di un oggetto vuoto `ProductPhotoCollectionForm`.

Una volta terminato, gli array `widgetSchema` e `validatorSchema` del form wrapper
(`ProductPhotoCollectionForm`) sono array multi-livello che contengono i widget
e i validatori di entrambi i `ProductPhotoForm`.

    [php]
    widgetSchema    => array
    (
      [0]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
      [1]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
    )

    validatorSchema => array
    (
      [0]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
      [1]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
    )

Nella fase finale del processo, il form wrapper `ProductPhotoCollectionForm`
risultante è inserito direttamente nel `ProductForm`. Questo avviene all'interno
di `ProductForm::configure()`, che sfrutta tutto il lavoro che è stato fatto
dentro a `ProductPhotoCollectionForm`:

    [php]
    $form = new ProductPhotoCollectionForm(null, array(
      'product' => $this->getObject(),
      'size'    => 2,
    ));

    $this->embedForm('newPhotos', $form);

Questo fornisce la struttura finale degli array `widgetSchema` e `validatorSchema`
vista sopra. Si noti che il metodo `embedForm()` è molto simile a quello che si
avrebbe combinando manualmente gli array `widgetSchema` e `validatorSchema`:

    [php]
    $this->widgetSchema['newPhotos'] = $form->getWidgetSchema();
    $this->validatorSchema['newPhotos'] = $form->getValidatorSchema();

Visualizzare l'unione di form nella vista
-----------------------------------------

Il template corrente `_form.php` del modulo `product`, è simile al
seguente:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <!-- ... -->

    <tbody>
      <?php echo $form ?>
    </tbody>

    <!-- ... -->

L'istruzione `<?php echo $form ?>` è il modo più semplice per visualizzare dei form,
anche i più complessi. È di grande aiuto per partire, ma, appena si desidera
modificare il layout è necessario sostituirlo con la propria logica di visualizzazione.
Rimuovere questa riga, perché verrà sostituita in questa sezione.

La cosa più importante da capire quando si visualizzano form incorporati nella
vista è l'organizzazione dell'array multi livello `widgetSchema`, spiegato nel
paragrafo precedente. Per questo esempio, si può iniziare visualizzando nella
vista i campi di base `name` e `price` del `ProductForm`: 

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php echo $form['name']->renderRow() ?>

    <?php echo $form['price']->renderRow() ?>

    <?php echo $form->renderHiddenFields() ?>

Come suggerisce il nome, `renderHiddenFields()` visualizza tutti i campi hidden
del form.

>**NOTE**
>Il codice delle azioni non è stato volutamente mostrato qui, perché ha bisogno
>di un'attenzione particolare. Si dia un'occhiata al file con le azioni
>`apps/frontend/modules/product/actions/actions.class.php`. Assomiglia ad un
>normale CRUD e può essere generato automaticamente attraverso il task 
>`doctrine:generate-module`.

Come si è già imparato, la classe `sfForm` ospita gli array `widgetSchema` e
`validatorSchema` che definiscono i campi. Inoltre, la classe `sfForm` implementa
l'interfaccia nativa di PHP 5 `ArrayAccess`, il che significa che si può accedere
direttamente ai campi del form, utilizzando la sintassi per le chiave degli array
vista sopra.

Per visualizzare i campi, si può accederci direttamente e chiamare il metodo 
`renderRow()`. Ma che tipo di oggetto è `$form['name']`? Anche se ci si potrebbe
aspettare di rispondere che per il campo `name` possa essere il widget `sfWidgetFormInputText`,
la risposta è in realtà qualcosa di leggermente diverso. 

### Visualizzare ciascun campo del form con ~`sfFormField`~

Usando gli array `widgetSchema` e `validatorSchema` definiti in ogni classe del form,
`sfForm` genera automaticamente un terzo array chiamato `sfFormFieldSchema`.
Questo array contiene un oggetto speciale per ciascun campo, che agisce come una
classe helper responsabile per la visualizzazione dei campi. L'oggetto, di tipo
~`sfFormField`~, è una combinazione di widget validator di ogni campo ed è
creato automaticamente.

    [php]
    <?php echo $form['name']->renderRow() ?>

Nel frammento di codice sovrastante, `$form['name']` è un oggetto `sfFormField`, che ospita
il metodo `renderRow()` insieme a molte altre utili funzioni per la visualizzazione.

### I metodi di visualizzazione con sfFormField

Ciascun oggetto `sfFormField` può essere usato per visualizzare facilmente ogni
aspetto del campo che esso rappresenta (ad esempio il campo stesso, l'etichetta,
i messaggi di errore, ecc.). Alcuni degli utili metodi all'interno di `sfFormField`
sono i seguenti. Altri si possono trovare guardando le [API di symfony 1.3](http://www.symfony-project.org/api/1_3/sfFormField).

 * `sfFormField->render()`: Visualizza il campo del form (es. `input`, `select`)
   con il corretto valore, utilizzando l'oggetto widget del campo stesso.

 * `sfFormField->renderError()`: Visualizza sul campo gli eventuali errori di validazione,
   usando l'oggetto validator del campo stesso.

 * `sfFormField->renderRow()`: Onnicomprensivo: visualizza l'etichetta, il campo
   del form, l'errore e il messaggio di aiuto, dentro ad un codice wrapper XHTML.

>**NOTE**
>In realtà, ogni funzione di visualizzazione della classe `sfFormField`, utilizza
>anche le informazioni della proprietà `widgetSchema` del form (l'oggetto `sfWidgetFormSchema`
>che ospita tutti i widget del form). Questa classe assiste nella generazione di
>ogni attributo `name` e `id` del campo, tiene traccia dell'etichetta per ciascun
>campo e definisce il codice XHTML utilizzato con `renderRow()`.

Una cosa importante da notare è che l'array `formFieldSchema` rispecchia sempre
la struttura degli array `widgetSchema` e `validatorSchema` del form.
Ad esempio, l'array `formFieldSchema` del `ProductForm`, avrebbe la seguente
struttura, che è la chiave per visualizzare ogni campo nella vista:

    [php]
    formFieldSchema    => array
    (
      [id]          => sfFormField
      [name]        => sfFormField,
      [price]       => sfFormField,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
        [1]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
      ),
    )

### Visualizzare il nuovo ProductForm

Utilizzando l'array di cui sopra come se fosse una mappa, si possono visualizzare
facilmente nella vista i campi di `ProductPhotoForm` che sono stati uniti,
posizionando e visualizzando i relativi oggetti `sfFormField`:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['newPhotos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow() ?>
    <?php endforeach; ?>

Il blocco sopra cicla due volte: una per i campi del form dell'array `0`
e una per i campi del form dell'array `1`. Come si è visto nello schema precedente,
gli oggetti di base di ogni array sono oggetti `sfFormField`, che si possono
visualizzare come ogni altro campo.

Salvare gli oggetti dei form
----------------------------

In molte circostanze, un form si riferisce direttamente ad una o più tabelle di
un database ed esegue le modifiche ai dati di tali tabelle, in base ai valori
inviati. Symfony genera automaticamente, per ciascun modello dello schema,
un oggetto form, che estende `sfFormDoctrine` o `sfFormPropel` a seconda dell'ORM.
Ogni classe dei form è simile e in fin dei conti permette ai valori inviati di
essere facilmente gestiti nel database.

>**NOTE**
>~`sfFormObject`~ è una nuova classe aggiunta in symfony 1.3 per gestire tutti i
>task comuni di `sfFormDoctrine` e `sfFormPropel`. Ogni classe estende `sfFormObject`,
>che ora gestisce parte del processo di memorizzazione del form descritto di seguito.

### Il processo di memorizzazione del form

Nell'esempio, symfony salva automaticamente sia le informazioni di `Product` che
quelle di `ProductPhoto`, senza alcuno sforzo supplementare da parte dello sviluppatore.
Il metodo che innesca la magia, ~`sfFormObject::save()`~, esegue dietro le quinte
una serie di metodi. La comprensione del suo funzionamento è la chiave per estendere
il processo in situazioni più avanzate.

Il processo di memorizzazione del form, consiste di una serie di  metodi eseguiti
internamente, che vengono lanciati dopo la chiamata di ~`sfFormObject::save()`~.
La maggior parte del lavoro è svolto nel metodo ~`sfFormObject::updateObject()`~,
che è chiamato ricorsivamente su tutti i form incorporati.

![Il processo di memorizzazione del form](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_06.png "Processo dettagliato di salvataggio del form")

>**NOTE**
>La maggior parte del processo di salvataggio avviene all'interno del metodo
>~`sfFormObject::doSave()`~, che è chiamato da `sfFormObject::save()` ed esegue
>una transazione nel database. Se c'è la necessità di modificare il processo di
>salvataggio stesso, generalmente `sfFormObject::doSave()` è il posto migliore
>per farlo.

Ignorare le unioni di form
--------------------------

L'attuale implementazione di `ProductForm` ha un importante problema. Poiché
i campi `filename` e `caption` in `ProductPhotoForm` sono obbligatori, la
validazione del form principale fallirà sempre, a meno che l'utente non stia
caricando due nuove foto. In altre parole, l'utente non può cambiare semplicemente
il prezzo di `Product` senza dover caricare due nuove foto.

![La validazione delle foto fallisce nel form Product](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_04.png "La validazione delle foto fallisce nel form Product")

Ora verranno ridefiniti i requisiti, per poter aggiungerne di nuovi. Se l'utente
lascia tutti i campi di un `ProductPhotoForm` vuoti, il form deve essere
completamente ignorato. Invece, se almeno un campo contiene dei dati (per esempio
`caption` o `filename`), il form deve validare e salvare in modo normale.
Per realizzare ciò, si impiega una tecnica avanzata che comporta l'uso di un
post validatore personalizzato.

Il primo passo però, è quello di modificare il form `ProductPhotoForm` per
rendere facoltativi i campi `caption` e `filename`:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

Nel codice di cui sopra, si è impostata l'opzione `required` a `false`, quando
è stato sovrascritto il validatore predefinito per il campo `filename`. Inoltre,
l'opzione `required` del campo `caption` è stata impostata esplicitamente a `false`.

Ora si può aggiungere il post validatore a `ProductPhotoCollectionForm`:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    public function configure()
    {
      // ...

      $this->mergePostValidator(new ProductPhotoValidatorSchema());
    }

Un post-validatore è uno speciale tipo di validatore che valida tutti i
valori inviati (l'opposto della validazione del valore di un singolo campo).
Uno dei post-validatori più comuni è `sfValidatorSchemaCompare` che verifica,
per esempio, se un campo è inferiore a un altro campo.

### Creare un validatore personalizzato

Per fortuna, la creazione di un validatore personalizzato è in realtà abbastanza
semplice. Creare un nuovo file, `ProductPhotoValidatorSchema.class.php` e metterlo
nella cartella `lib/validator/` (è necessario creare la cartella):

    [php]
    // lib/validator/ProductPhotoValidatorSchema.class.php
    class ProductPhotoValidatorSchema extends sfValidatorSchema
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addMessage('caption', 'The caption is required.');
        $this->addMessage('filename', 'The filename is required.');
      }

      protected function doClean($values)
      {
        $errorSchema = new sfValidatorErrorSchema($this);

        foreach($values as $key => $value)
        {
          $errorSchemaLocal = new sfValidatorErrorSchema($this);

          // c'è il nome del file, ma non la didascalia
          if ($value['filename'] && !$value['caption'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'caption');
          }

          // c'è la didascalia caption, ma non il nome del file
          if ($value['caption'] && !$value['filename'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'filename');
          }

          // né didascalia né nome del file, rimuovere i valori vuoti
          if (!$value['filename'] && !$value['caption'])
          {
            unset($values[$key]);
          }

          // ci sono errori per il form incluso
          if (count($errorSchemaLocal))
          {
            $errorSchema->addError($errorSchemaLocal, (string) $key);
          }
        }

        // invia l'errore per il form principale
        if (count($errorSchema))
        {
          throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
      }
    }

>**TIP**
>Tutti i validatori estendono `sfValidatorBase` e richiedono solo il metodo
>`doClean()`. Il metodo `configure()` può essere utilizzato anche per aggiungere
>opzioni o messaggi al validatore. In questo caso, vengono aggiunti due messaggi
>al validatore. Allo stesso modo, possono essere aggiunte le opzioni tramite il
>metodo `addOption()`.

Il metodo `doClean()` si occupa della pulizia e della validazione dei relativi
valori. La logica del validatore stesso è abbastanza semplice:

 * Se una foto è inviata con solo il nome del file o della didascalia, viene lanciato
   un errore (`sfValidatorErrorSchema`) con il messaggio appropriato;

 * Se una foto è inviata senza nome del file e senza didascalia, si eliminano
   tutti i valori, in modo da evitare di salvare una foto vuota;

 * Se non si verificano errori di validazione, il metodo restituisce l'array con
   i valori puliti.

>**TIP**
>Poiché il validatore personalizzato in questa situazione è destinato ad essere
>utilizzato come post-validatore, il metodo `doClean()` si aspetta un array dei
>valori uniti e restituisce un array di valori puliti. I validatori personalizzati,
>tuttavia, possono essere facilmente creati per singoli campi. In tal caso, il
>metodo `doClean()` si aspetta un solo valore (il valore del campo inviato) e
>restituirà un solo valore.

L'ultimo passo è quello di sovrascrivere il metodo `saveEmbeddedForms()` di
`ProductForm`, per cancellare i form di foto vuoti ed evitare di salvare una foto
vuota nel database (altrimenti dovrebbe lanciare una eccezione, dal momento che
la colonna `caption` è obbligatoria):

    [php]
    public function saveEmbeddedForms($con = null, $forms = null)
    {
      if (null === $forms)
      {
        $photos = $this->getValue('newPhotos');
        $forms = $this->embeddedForms;
        foreach ($this->embeddedForms['newPhotos'] as $name => $form)
        {
          if (!isset($photos[$name]))
          {
            unset($forms['newPhotos'][$name]);
          }
        }
      }

      return parent::saveEmbeddedForms($con, $forms);
    }

Unire facilmente i form relativi a Doctrine
-------------------------------------------

Una novità in symfony 1.3 è la funzione ~`sfFormDoctrine::embedRelation()`~, che
consente allo sviluppatore di unire automaticamente relazioni n-a-molti in un
form. Si supponga, per esempio, che oltre a permettere all'utente di
caricare due nuovi `ProductPhotos`, si voglia anche consentire all'utente di
modificare gli oggetti di questo `Product` relativi a `ProductPhoto`.

Allo scopo, si può usare il metodo `embedRelation()` per aggiungere un ulteriore
oggetto `ProductPhotoForm` a ciascun esistente oggetto `ProductPhoto`:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      // ...

      $this->embedRelation('Photos');
    }

Internamente, ~`sfFormDoctrine::embedRelation()`~ fa quasi esattamente ciò che
è stato fatto manualmente per incorporare i due nuovi oggetti `ProductPhotoForm`.
Se esistono già le due relazioni `ProductPhoto`, allora i risultanti `widgetSchema`
e `validatorSchema` del form, prendono la seguente forma:

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
      ),
    )

![Form Product con 2 foto esistenti](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_03.png "Form Product con 2 foto esistenti")

Il passo successivo è quello di aggiungere codice alla vista, che permetterà
di visualizzare i nuovi form *Photo* incorporati:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['Photos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow(array('width' => 100)) ?>
    <?php endforeach; ?>

Questo frammento di codice è esattamente quello che è stato usato in precedenza
per incorporare i nuovi form delle foto.

L'ultimo passo è quello di convertire il campo con il file da caricare, in uno che
permetta all'utente di vedere la foto corrente e di cambiarla con una nuova
(`sfWidgetFormInputFileEditable`):

    [php]
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->setWidget('filename', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/products/'.$this->getObject()->filename,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

Eventi dei form
---------------

Una novità di symfony 1.3 sono gli eventi dei form, che possono essere usati per
estendere un qualsiasi oggetto form in qualunque posto del progetto. Symfony
espone i seguenti quattro eventi per i form:

 * `form.post_configure`: Questo evento è notificato dopo che ciascun form è configurato
 * `form.filter_values`: Questo evento filtra i parametri fusi e modificati e gli array dei file appena prima di essere uniti
 * `form.validation_error`: Questo evento è notificato se fallisce qualunque validazione sul form
 * `form.method_not_found`: Questo evento è notificato ogni volta che si chiama un metodo sconosciuto

### Personalizzazione dei log attraverso `form.validation_error`

Utilizzando gli eventi dei form, è possibile aggiungere log personalizzati
per errori di validazione su qualunque form del progetto. Questo potrebbe
essere utile se si vuole tenere traccia di quali form e campi stanno causando
confusione agli utenti. 

Si può iniziare la registrazione di un ascoltatore con il dispatcher di eventi, per
l'evento `form.validation_error`. Aggiungere il codice seguente al metodo `setup()`
di `ProjectConfiguration` che è presente nella cartella `config`:

    [php]
    public function setup()
    {
      // ...

      $this->getEventDispatcher()->connect(
        'form.validation_error',
        array('BaseForm', 'listenToValidationError')
      );
    }

`BaseForm`, presente in `lib/form`, è una speciale classe form che estende
tutte le classi dei form. In sostanza, `BaseForm` è una classe in cui può
essere inserito il codice, diventando accessibile da tutti gli oggetti form del
progetto. Per abilitare il log degli errori di validazione, basta aggiungere
il seguente codice alla classe `BaseForm`:

    [php]
    public static function listenToValidationError($event)
    {
      foreach ($event['error'] as $key => $error)
      {
        self::getEventDispatcher()->notify(new sfEvent(
          $event->getSubject(),
          'application.log',
          array (
            'priority' => sfLogger::NOTICE,
            sprintf('Validation Error: %s: %s', $key, (string) $error)
          )
        ));
      }
    }

![Il log degli errori di validazione](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_05.png "Web debug toolbar con errori di validazione")

Personalizzazione dello stile grafico quando un elemento del form ha un errore
------------------------------------------------------------------------------

Come esercizio conclusivo, si può passare ad un argomento un po' più leggero,
relativo alla grafica degli elementi di un form. Si supponga, per esempio, che
la grafica per la pagina `Product` includa uno stile speciale per i campi che
che falliscono la validazione.

![Il form Product con errori](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_02.png "Form Product con stili per gli errori")

Si supponga che il grafico abbia già implementato il foglio di stile che applicherà
la grafica per l'errore ad ogni campo `input` dentro a un `div`, con la classe
`form_error_row`. Come si può aggiungere facilmente la classe `form_row_error`
ai campi con errori?

La risposta si trova in uno speciale oggetto chiamato *formattatore di schema
dei form*. Ogni form di symfony utilizza un *formattatore di schema dei form*,
per determinare l'esatta formattazione HTML da utilizzare quando vengono visualizzati
gli elementi di un form. Per impostazione predefinita, symfony utilizza un
formattatore di form che usa i tag HTML table.

Prima di tutto, bisogna creare una nuova classe formattatore per lo schema dei
form, che utilizzi un codice leggero quando visualizza il form. Creare un nuovo
file chiamato `sfWidgetFormSchemaFormatterAc2009.class.php` e metterlo nella
cartella `lib/widget/` (è necessario creare questa cartella):

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class='form_row'>
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        $errorRowFormat  = "<div>%errors%</div>",
        $helpFormat      = '<div class="form_help">%help%</div>',
        $decoratorFormat = "<div>\n  %content%</div>";
    }

Anche se il formato di questa classe è strano, l'idea generale è che il metodo
`renderRow()` utilizzerà il codice `$rowFormat` per organizzare il suo output.
Un formattatore di schema dei form offre molte altre opzioni di formattazione, che
in questa sede non vengono mostrare in dettaglio. Per maggiori informazioni, consultare
le [API di symfony 1.3](http://www.symfony-project.org/api/1_3/sfWidgetFormSchemaFormatter).

Per usare il nuovo formattatore di schema dei form in tutti gli oggetti form del
progetto, aggiungere il seguente codice a `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        sfWidgetFormSchema::setDefaultFormFormatterName('ac2009');
      }
    }

L'obiettivo è quello di aggiungere una classe `form_row_error` all'elemento div
`form_row` solo se un campo fallisce la validazione. Aggiungere un token `%row_class%`
alla proprietà `$rowFormat` e sovrascrivere il metodo ~`sfWidgetFormSchemaFormatter::formatRow()`~
come segue:

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class='form_row%row_class%'>
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        // ...

      public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
      {
        $row = parent::formatRow(
          $label,
          $field,
          $errors,
          $help,
          $hiddenFields
        );

        return strtr($row, array(
          '%row_class%' => (count($errors) > 0) ? ' form_row_error' : '',
        ));
      }
    }

Con questa aggiunta, ogni elemento che è visualizzato attraverso il metodo
`renderRow()`, sarà automaticamente circondato da un `form_row_error` `div` se
il campo ha fallito la validazione.

Considerazioni finali
---------------------

Il framework dei form è contemporaneamente uno dei più potenti e più
complessi componenti all'interno di symfony. Il prezzo da pagare per la validazione
stretta dei form, la protezione CSRF e gli oggetti dei form è che estendere il
framework può diventare un compito arduo. Acquisire una più profonda
comprensione del sistema dei form, tuttavia, è la chiave per sbloccare il suo
potenziale. Speriamo che questo capitolo sia stato un passo di avvicinamento in
tal senso.

I prossimi sviluppi del framework dei form si concentreranno sul mantenimento
della potenza, diminuendo la complessità e fornendo maggiore flessibilità allo
sviluppatore. Il framework dei form, attualmente, è solo nella sua infanzia.
