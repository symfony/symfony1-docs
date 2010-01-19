Widget e validatori personalizzati
==================================

*di Thomas Rabaix*

Questo capitolo spiega come costruire widget e validatori personalizzati da usare
nel framework dei form. Verrà spiegata la struttura interna di `sfWidgetForm` e
`sfValidator`, così come il modo in cui si costruiscono widget semplici e complessi.

Funzionamento interno di widget e validatori
--------------------------------------------

### Funzionamento interno di `sfWidgetForm`

Un oggetto della classe ~`sfWidgetForm`~ rappresenta la realizzazione visiva di come
i relativi dati potranno essere modificati. Un valore stringa, per esempio, potrebbe
essere modificato con una semplice casella di testo, o con un avanzato editor di tipo
WYSIWYG. La classe `sfWidgetForm`, allo scopo di poter essere completamente configurabile,
ha due importanti proprietà: `options` e `attributes`.

 * `options`: usato per configurare il widget (ad esempio le query al database, 
   da usare quando si crea un elenco per un menu a tendina)

 * `attributes`: attributi HTML aggiunti all'elemento che deve essere visualizzato

Inoltre, la classe `sfWidgetForm` implementa due importanti metodi:

 * `configure()`: definisce quali opzioni sono *facoltative* o *obbligatorie*.
   Anche se non è una buona pratica sovrascrivere il costruttore, il metodo
   `configure()` può essere tranquillamente ignorato.

 * `render()`: visualizza l'HTML per il widget. Il metodo ha un primo parametro
   obbligatorio, il nome HTML del widget e un secondo parametro opzionale,
   il valore.

>**NOTE**
>Un oggetto `sfWidgetForm` non sa nulla sul suo nome o il suo valore.
>Il componente è responsabile solo della visualizzazione del widget. Il nome e
>il valore sono gestiti da un oggetto `sfFormFieldSchema`, che è il collegamento
>tra i dati e i widget.

### Funzionamento interno di sfValidatorBase

La classe ~`sfValidatorBase`~ è la classe base di ogni validatore. Il
metodo ~`sfValidatorBase::clean()`~ è il più importante di questa classe,
perché controlla se il valore è valido in base alle opzioni fornite.

Internamente, il metodo `clean()` esegue diverse azioni:

 * toglie, dal valore di input, gli spazi iniziali e finali
   per i valori di tipo stringa (se specificato tramite l'opzione `trim`)
 * controlla se il valore è vuoto
 * chiama il metodo del validatore `doClean()`.

Il metodo `doClean()`, è il metodo che implementa la logica principale di
convalida. Non è buona pratica sovrascrivere il metodo `clean()`. È meglio
gestire ogni logica personalizzata attraverso il metodo `doClean()`.

Un validatore può anche essere usato come un componente a se stante per controllare
l'integrità di un input. Ad esempio, il validatore `sfValidatorEmail` controlla
se una email è valida:

    [php]
    $v = new sfValidatorEmail();

    try
    {
      $v->clean($request->getParameter("email"));
    }
    catch(sfValidatorError $e)
    {
      $this->forward404();
    }

>**NOTE**
>Quando un form viene legato ai valori della request, l'oggetto `sfForm` mantiene
>i riferimenti al valore originale ("sporco") e al valore validato ("pulito").
>I valori originali sono usati quando il form viene ridisegnato, mentre i valori
>puliti sono usati dall'applicazione (ad esempio, per salvare l'oggetto).

### L'attributo `options`

Entrambi gli oggetti `sfWidgetForm` e `sfValidatorBase` hanno numerose opzioni:
alcune sono facoltative, mentre altre sono obbligatorie. Queste opzioni sono definite
all'interno di ogni metodo `configure()` della classe, attraverso: 

 * `addOption($name, $value)`: definisce un'opzione con un nome e un valore predefinito
 * `addRequiredOption($name)`: definisce un'opzione obbligatoria

Questi due metodi sono molto utili, in quanto garantiscono che i valori di
dipendenza siano correttamente passati al validatore o al widget.

Creare un semplice widget e un validatore
-----------------------------------------

Questa sezione spiega come creare un semplice widget. Questo particolare widget
sarà chiamato "Trilean". Il widget visualizzerà un menu a tendina con tre scelte:
`No`, `Si` e `Null`.

    [php]
    class sfWidgetFormTrilean extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
      {

        $this->addOption('choices', array(
          0 => 'No',
          1 => 'Yes',
          'null' => 'Null'
        ));
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        $value = $value === null ? 'null' : $value;

        $options = array();
        foreach ($this->getOption('choices') as $key => $option)
        {
          $attributes = array('value' => self::escapeOnce($key));
          if ($key == $value)
          {
            $attributes['selected'] = 'selected';
          }

          $options[] = $this->renderContentTag(
            'option',
            self::escapeOnce($option),
            $attributes
          );
        }

        return $this->renderContentTag(
          'select',
          "\n".implode("\n", $options)."\n",
          array_merge(array('name' => $name), $attributes
        ));
      }
    }

Il metodo `configure()` definisce i valori delle opzioni per l'elenco, attraverso
l'opzione `choices`. Questo array può essere ridefinito (ad esempio per cambiare
l'etichetta associata a ogni valore). Non vi è alcun limite al numero di opzioni
che un widget può definire. La classe base del widget, tuttavia, dichiara alcune
opzioni standard che quindi è come se fossero opzioni riservate:

 * `id_format`: il formato id, il predefinito è '%s'

 * `is_hidden`: valore booleano per definire se il widget è un campo nascosto (usato
   da `sfForm::renderHiddenFields()` per rendere tutti i campi nascosti in una sola volta)

 * `needs_multipart`: valore booleano per definire se il tag del form deve includere
   l'opzione multipart (ad esempio per l'upload dei file)

 * `default`: Il valore predefinito che deve essere utilizzato per rendere il widget
   se non è fornito nessun valore 

 * `label`: L'etichetta predefinita del widget

Il metodo `render()` genera il codice HTML corrispondente ad un menu a tendina. Il
metodo chiama la funzione predefinita `renderContentTag()` che viene in aiuto nella
visualizzazione dei tag HTML.

Ora il widget è completo. Si può creare il validatore corrispondente:

    [php]
    class sfValidatorTrilean extends sfValidatorBase
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addOption('true_values', array('true', 't', 'yes', 'y', 'on', '1'));
        $this->addOption('false_values', array('false', 'f', 'no', 'n', 'off', '0'));
        $this->addOption('null_values', array('null', null));
      }

      protected function doClean($value)
      {
        if (in_array($value, $this->getOption('true_values')))
        {
          return true;
        }

        if (in_array($value, $this->getOption('false_values')))
        {
          return false;
        }

        if (in_array($value, $this->getOption('null_values')))
        {
          return null;
        }

        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }

      public function isEmpty($value)
      {
        return false;
      }
    }

Il validatore `sfValidatorTrilean` definisce tre opzioni nel metodo `configure()`.
Ogni opzione è un insieme di valori validi. Siccome questi sono definiti come
opzioni, lo sviluppatore può personalizzare i valori a seconda delle specifiche.

Il metodo `doClean()` verifica se il valore corrisponde ad un insieme di valori
validi e restituisce il valore "pulito". Se il valore non viene trovato, il metodo
lancerà un `sfValidatorError`, che è l'errore standard di validazione nel
framework dei form.

L'ultimo metodo `isEmpty()` è sovrascrivibile, perché il comportamento predefinito
di questo metodo è di restituire `true` se il valore è `null`.

>**Note**:
> Se `isEmpty()` restituisce true, il metodo `doClean()` non verrà mai chiamato.

Anche se questo widget è abbastanza semplice, ha introdotto alcune importanti
caratteristiche di base che saranno necessarie andando avanti. Nella sezione
successiva verrà creato un widget più complesso, con più campi e una interazione
JavaScript.

Il widget per le mappe di Google
--------------------------------

In questa sezione, si andrà a costruire un widget più complesso. Saranno
introdotti nuovi metodi e il widget avrà anche qualche interazione JavaScript.
Il widget sarà chiamato "GMAW": "Google Map Address Widget".

Cosa si vuole ottenere? Il widget dovrebbe fornire un metodo semplice rivolto
all'utente finale, per aggiungere un indirizzo. Utilizzando un campo input di testo
e con il servizio mappe di Google, si può raggiungere questo obiettivo.



![Mashup "Google Map Address Widget"](http://www.symfony-project.org/images/more-with-symfony/widgets-figure-01.png ""Google Map Address Widget" mashup")

Caso d'uso 1:

 * L'utente digita un indirizzo.
 * L'utente clicca il bottone "lookup".
 * I campi nascosti di latitudine e longitudine vengono aggiornati e sulla mappa
   viene creato un nuovo marcatore. Il marcatore è posizionato in corrispondenza
   della posizione dell'indirizzo. Se il servizio di Google Geocoding non riesce
   a trovare l'indirizzo, viene mostrato un popup con un messaggio di errore.

Caso d'uso 2:

 * L'utente clicca sulla mappa.
 * I campi nascosti di latitudine e longitudine vengono aggiornati.
 * Viene utilizzata una ricerca al contrario, per trovare l'indirizzo.

*I seguenti campi devono essere inviati e gestiti dal form:*

 * `latitude`: float, tra 90 e -90
 * `longitude`: float, tra 180 e -180
 * `address`: string, solo testo

Le specifiche funzionali del widget sono state appena definite, ora si possono
definire gli strumenti tecnici e i loro ambiti:

 * Servizi Google map e Geocoding: visualizzano la mappa e recuperano le informazioni sull'indirizzo
 * jQuery: aggiunge interazioni JavaScript tra il form e il campo
 * sfForm: richiama il widget e convalida gli input

### Il widget `sfWidgetFormGMapAddress`

Siccome un widget è la rappresentazione visiva dei dati, il metodo `configure()`
del widget deve avere diverse opzioni per modificare la mappa di Google o
modificare gli stili di ogni elemento. Una delle opzioni più importanti è quella
di `template.html`, che definisce il modo in cui tutti gli elementi vengono ordinati.
Quando si costruisce un widget, è molto importante pensare alla riusabilità ed
all'estensibilità.

Un'altra cosa importante è la definizione delle risorse esterne. Una classe
`sfWidgetForm` può implementare due metodi specifici:

 * `getJavascripts()` deve restituire un array di file JavaScript;

 * `getStylesheets()` deve restituire un array di file di fogli di stile
   (dove la chiave è il percorso e il valore è il nome del media).

Il widget attuale richiede poco JavaScript per poter funzionare, quindi non è
necessario nessun foglio di stile. In questo caso, tuttavia, il widget non
consente di gestire l'inizializzazione del JavaScript di Google, anche se il
widget si avvarrà del geocoding e dei servizi per le mappe di Google. Quindi,
sarà responsabilità dello sviluppatore inserirlo nella pagina. La ragione che
sta dietro a questo, è che i servizi di Google possono essere utilizzati da
altri elementi della pagina e non solo dal widget.

Ecco il codice:

    [php]
    class sfWidgetFormGMapAddress extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
      {
        $this->addOption('address.options', array('style' => 'width:400px'));

        $this->setOption('default', array(
          'address' => '',
          'longitude' => '2.294359',
          'latitude' => '48.858205'
        ));

        $this->addOption('div.class', 'sf-gmap-widget');
        $this->addOption('map.height', '300px');
        $this->addOption('map.width', '500px');
        $this->addOption('map.style', "");
        $this->addOption('lookup.name', "Lookup");

        $this->addOption('template.html', '
          <div id="{div.id}" class="{div.class}">
            {input.search} <input type="submit" value="{input.lookup.name}"  id="{input.lookup.id}" /> <br />
            {input.longitude}
            {input.latitude}
            <div id="{map.id}" style="width:{map.width};height:{map.height};{map.style}"></div>
          </div>
        ');

         $this->addOption('template.javascript', '
          <script type="text/javascript">
            jQuery(window).bind("load", function() {
              new sfGmapWidgetWidget({
                longitude: "{input.longitude.id}",
                latitude: "{input.latitude.id}",
                address: "{input.address.id}",
                lookup: "{input.lookup.id}",
                map: "{map.id}"
              });
            })
          </script>
        ');
      }

      public function getJavascripts()
      {
        return array(
          '/sfFormExtraPlugin/js/sf_widget_gmap_address.js'
        );
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        // definisce le variabili principali del template
        $template_vars = array(
          '{div.id}'             => $this->generateId($name),
          '{div.class}'          => $this->getOption('div.class'),
          '{map.id}'             => $this->generateId($name.'[map]'),
          '{map.style}'          => $this->getOption('map.style'),
          '{map.height}'         => $this->getOption('map.height'),
          '{map.width}'          => $this->getOption('map.width'),
          '{input.lookup.id}'    => $this->generateId($name.'[lookup]'),
          '{input.lookup.name}'  => $this->getOption('lookup.name'),
          '{input.address.id}'   => $this->generateId($name.'[address]'),
          '{input.latitude.id}'  => $this->generateId($name.'[latitude]'),
          '{input.longitude.id}' => $this->generateId($name.'[longitude]'),
        );

        // evita errori per formati non validi di $value
        $value = !is_array($value) ? array() : $value;
        $value['address']   = isset($value['address'])   ? $value['address'] : '';
        $value['longitude'] = isset($value['longitude']) ? $value['longitude'] : '';
        $value['latitude']  = isset($value['latitude'])  ? $value['latitude'] : '';

        // definisce il widget per l'indirizzo
        $address = new sfWidgetFormInputText(array(), $this->getOption('address.options'));
        $template_vars['{input.search}'] = $address->render($name.'[address]', $value['address']);

        // definisce i campi per longitudine e latitudine
        $hidden = new sfWidgetFormInputHidden;
        $template_vars['{input.longitude}'] = $hidden->render($name.'[longitude]', $value['longitude']);
        $template_vars['{input.latitude}']  = $hidden->render($name.'[latitude]', $value['latitude']);

        // fonde template e variabili
        return strtr(
          $this->getOption('template.html').$this->getOption('template.javascript'),
          $template_vars
        );
      }
    }

Il widget usa il metodo `generateId()` per generare l'`id` di ciascun elemento.
La variabile `$name` è definita da `sfFormFieldSchema`, quindi la variabile `$name`
è composta dal nome del form, eventuali nomi nidificati nello schema del widget
e dal nome del widget così come definito nel `configure()` del form.

>**NOTE**
>Per esempio, se il nome del modulo è `user`, il nome dello schema annidato è `location`
>e il nome del  widget è `address`, il `name` finale sarà `user[location][address]`
>e l'`id` sarà `user_location_address`. In altre parole,
>`$this->generateId($name.'[latitude]')` genererà un valido e unico
>`id` per il campo `latitude`.

I diversi elementi degli attributi `id` sono molto importanti in quanto vengono
passati al blocco JavaScript (attraverso la variabile `template.js`), in modo
che il codice JavaScript sia in grado di gestire correttamente i vari elementi.

Il metodo `render()` istanzia anche due widget interni: un widget `sfWidgetFormInputText`,
che è usato per rendere il campo indirizzo e un widget `sfWidgetFormInputHidden`,
che è usato per rendere i campi nascosti.

Il widget può essere testato rapidamente con questo piccolo pezzo di codice:

    [php]
    $widget = new sfWidgetFormGMapAddress();
    echo $widget->render('user[location][address]', array(
      'address' => '151 Rue montmartre, 75002 Paris',
      'longitude' => '2.294359',
      'latitude' => '48.858205'
    ));

Il risultato in output è:

    [html]
    <div id="user_location_address" class="sf-gmap-widget">
      <input style="width:400px" type="text" name="user[location][address][address]" value="151 Rue montmartre, 75002 Paris" id="user_location_address_address" />
      <input type="submit" value="Lookup"  id="user_location_address_lookup" /> <br />
      <input type="hidden" name="user[location][address][longitude]" value="2.294359" id="user_location_address_longitude" />
      <input type="hidden" name="user[location][address][latitude]" value="48.858205" id="user_location_address_latitude" />
      <div id="user_location_address_map" style="width:500px;height:300px;"></div>
    </div>

    <script type="text/javascript">
      jQuery(window).bind("load", function() {
        new sfGmapWidgetWidget({
          longitude: "user_location_address_longitude",
          latitude: "user_location_address_latitude",
          address: "user_location_address_address",
          lookup: "user_location_address_lookup",
          map: "user_location_address_map"
        });
      })
    </script>

La parte JavaScript del widget prende i differenti attributi `id` e li lega
a degli ascoltatori jQuery, in modo che venga richiamato un certo JavaScript
quando vengono eseguite delle azioni. Il JavaScript aggiorna i campi nascosti
con la longitudine e la latitudine forniti dal servizio di geocoding di Google.

L'oggetto JavaScript ha alcuni metodi interessanti:

 * `init()`: il metodo in cui sono inizializzate tutte le variabili e gli eventi
   vengono legati ad input diversi

 * `lookupCallback()`: un metodo *statico* usato dal metodo geocoder per
   cercare l'indirizzo fornito dall'utente

 * `reverseLookupCallback()`: è un altro metodo *statico* usato dal geocoder
   per convertire le longitudini e latitudini fornite, in un indirizzo valido.

Il codice javascript finale si può vedere nell'Appendice A.

Si prega di fare riferimento alla documentazione di Google map per maggiori
dettagli sulle funzionalità delle [API](http://code.google.com/apis/maps/).

### Il validatore `sfValidatorGMapAddress`

La classe `sfValidatorGMapAddress` estende `sfValidatorBase` che già
esegue una validazione: nello specifico, se il campo è impostato come required allora
il valore non può essere `null`. Pertanto, `sfValidatorGMapAddress` ha bisogno
di validare solamente i valori diversi: `latitude`, `longitude` e `address`. La
variabile `$value` dovrebbe essere un array, ma siccome l'input dell'utente
non è affidabile, il validatore verifica la presenza di tutte le chiavi, in modo
che i validatori interni possano essere passati come valori validi.

    [php]
    class sfValidatorGMapAddress extends sfValidatorBase
    {
      protected function doClean($value)
      {
        if (!is_array($value))
        {
          throw new sfValidatorError($this, 'invalid');
        }

        try
        {
          $latitude = new sfValidatorNumber(array( 'min' => -90, 'max' => 90, 'required' => true ));
          $value['latitude'] = $latitude->clean(isset($value['latitude']) ? $value['latitude'] : null);

          $longitude = new sfValidatorNumber(array( 'min' => -180, 'max' => 180, 'required' => true ));
          $value['longitude'] = $longitude->clean(isset($value['longitude']) ? $value['longitude'] : null);

          $address = new sfValidatorString(array( 'min_length' => 10, 'max_length' => 255, 'required' => true ));
          $value['address'] = $address->clean(isset($value['address']) ? $value['address'] : null);
        }
        catch(sfValidatorError $e)
        {
          throw new sfValidatorError($this, 'invalid');
        }

        return $value;
      }
    }

>**NOTE**
>Un validatore solleva sempre l'eccezione `sfValidatorError` quando un valore non
>è valido. Questo è il motivo per cui la validazione è circondata da un blocco
>`try/catch`. In questo validatore, il validatore rilancia una nuova eccezione
>`invalid`, il che equivale a un errore di validazione `invalid` sul validatore 
>`sfValidatorGMapAddress`.

### I test

Perché è importante fare i test? Il validatore è il collante tra l'input dell'utente
e l'applicazione. Se il validatore è mal fatto, l'applicazione è vulnerabile.
Per fortuna, symfony ha `lime`, che è una libreria per i test molto facile
da usare.

Come si può testare il validatore? Come già detto, un validatore solleva
un'eccezione su un errore di convalida. Il test può inviare valori validi e invalidi
al validatore e verificare che l'eccezione viene lanciata nelle circostanze corrette.

    [php]
    $t = new lime_test(7, new lime_output_color());

    $tests = array(
      array(false, '', 'empty value'),
      array(false, 'string value', 'string value'),
      array(false, array(), 'empty array'),
      array(false, array('address' => 'my awesome address'), 'incomplete address'),
      array(false, array('address' => 'my awesome address', 'latitude' => 'String', 'longitude' => 23), 'invalid values'),
      array(false, array('address' => 'my awesome address', 'latitude' => 200, 'longitude' => 23), 'invalid values'),
      array(true, array('address' => 'my awesome address', 'latitude' => '2.294359', 'longitude' => '48.858205'), 'valid value')
    );

    $v = new sfValidatorGMapAddress;

    $t->diag("Testing sfValidatorGMapAddress");

    foreach($tests as $test)
    {
      list($validity, $value, $message) = $test;

      try
      {
        $v->clean($value);
        $catched = false;
      }
      catch(sfValidatorError $e)
      {
        $catched = true;
      }

      $t->ok($validity != $catched, '::clean() '.$message);
    }

Quando viene chiamato il metodo `sfForm::bind()`, il form esegue il metodo
`clean()` di ciascun validatore. Il test riproduce questo comportamento
istanziando direttamente il validatore `sfValidatorGMapAddress` e testando valori
diversi.

Considerazioni finali
---------------------

L'errore più comune quando si crea un widget è quello di essere troppo concentrati
su come le informazioni saranno memorizzate nel database. Il framework dei form
è semplicemente un contenitore di dati e un framework di validazione. Pertanto,
un widget deve gestire solamente le proprie informazioni. Se i dati sono
validi, i diversi valori "puliti" possono poi essere utilizzati dal modello o
dal controllore.
