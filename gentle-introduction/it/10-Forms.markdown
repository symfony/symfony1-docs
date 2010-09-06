Chapter 10 - I form
===================

La visualizzazione degli input di un form, la validazione dei dati inseriti in un form e tutta la casistica particolare del trattamento dei form è uno dei compiti più complessi nello sviluppo web. Fortunatamente, symfony fornisce un'interfaccia semplice verso un potente sottosistema dedicato ai form, e facilita la creazione e la manipolazione con poche linee di codice di form di qualsiasi livello di complessità.

Visualizzazione di un form
--------------------------

Un semplice form di contatto con dei campi nome, email, oggetto e messaggio tipicamente appare come segue:

![Form di contatto](http://www.symfony-project.org/images/forms_book/en/01_07.png)

In symfony un form è un oggetto definito nell'azione e passato al template. Prima di visualizzare un form, si devono definire i campi che esso contiene, ossia quelli che con la terminologia propria di symfony vengono chiamati "widget". Il modo più semplice per farlo è di creare un nuovo oggetto `sfForm` nell'opportuno metodo dell'action.

    [php]
    // in modules/pippo/actions/actions.class.php
    public function executeContact($request)
    {
      $this->form = new sfForm();
      $this->form->setWidgets(array(
        'name'    => new sfWidgetFormInputText(),
        'email'   => new sfWidgetFormInputText(array('default' => 'me@example.com')),
        'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
        'message' => new sfWidgetFormTextarea(),
      ));
    }

`sfForm::setWidgets()` si aspetta un array associativo di nomi di widtget / oggetti widget. `sfWidgetFormInputText`, `sfWidgetFormChoice` e `sfWidgetFormTextarea` sono alcune delle numerose classi di widget offerte da symfony; se ne può trovare una lista completa più avanti in questo capitolo.

L'esempio precedente mostra due opzioni dei widget che è possibile usare: `default` imposta il valore da assegnare al widget ed è disponibile per tutti i tipi di widget. `choices`è invece un'opzione specifica dei widget di tipo `choice` (che viene visualizzato come una lista a discesa): essa definisce le opzioni selezionabili dall'utente.

Dunque l'azione `pippo/contact` definisce un oggetto form, che rende disponibile al template `contactSuccess` in una variabile `$form`.  Il template può usare questo oggetto per generare le varie parti del form in HTML. Il modo più semplice per farlo è una chiamata del tipo `echo $form`, che costruisce tutti i campi come controlli del form con etichette. Si può anche usare l'oggetto form per generare il tag form:

    [php]
    // in modules/pippo/templates/contactSuccess.php
    <?php echo $form->renderFormTag('pippo/contact') ?>
      <table>
        <?php echo $form ?>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

Con i parametri passati a setWidgets(), symfony ha informazioni sufficienti per mostrare il form correttamente. L'HTML risultante, corrispondente allo screenshot visto in precedenza, appare come segue:

    [php]
    <form action="/frontend_dev.php/pippo/contact" method="POST">
      <table>
        <tr>
          <th><label for="name">Name</label></th>
          <td><input type="text" name="name" id="name" /></td>
        </tr>
        <tr>
          <th><label for="email">Email</label></th>
          <td><input type="text" name="email" id="email" value="me@example.com" /></td>
        </tr>
        <tr>
          <th><label for="subject">Subject</label></th>
          <td>
            <select name="subject" id="subject">
              <option value="0">Subject A</option>
              <option value="1">Subject B</option>
              <option value="2">Subject C</option>
            </select>
          </td>
        </tr>
        <tr>
          <th><label for="message">Message</label></th>
          <td><textarea rows="4" cols="30" name="message" id="message"></textarea></td>
        </tr>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

Ogni widget viene convertito nella riga di una tabella contenente un tag <label> e un tag di input del form. Symfony deduce il nome della label dal nome del widget, convertendo l'iniziale in maiuscolo (il nome del widget `subject` dà la label `Subject`). Per quanto riguarda il tag di input, esso dipende dal tipo di widget. Symfony aggiunge un attributo `id` a ogni widget, basandolo sul suo nome. Infine, la resa del form è sempre conforme alle specifiche XHTML.

Personalizzare la visualizzazione del form
------------------------------------------

L'uso di `echo $form` è ottimo per la prototipazione, ma probabilmente si desidera controllare esattamente il codice HTML risultante. L'oggetto form contiene un array di campi, e la chiamata `echo $form` di fatto itera attraverso i campi e li genera uno per uno. Per avere un controllo ulteriore, è possibile iterare manualmente attraverso i campi, e richiamare `renderRow()` per ogni campo. Il listato che segue produce esattamente lo stesso codice HTML del precedente, ma il template scrive ogni campo individualmente:

    [php]
    // in modules/pippo/templates/contactSuccess.php
    <?php echo $form->renderFormTag('pippo/contact') ?>
      <table>
        <?php echo $form['name']->renderRow() ?>
        <?php echo $form['email']->renderRow() ?>
        <?php echo $form['subject']->renderRow() ?>
        <?php echo $form['message']->renderRow() ?>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

Generare i campi uno alla volta permette di cambiare l'ordine nel quale essi sono visualizzati, e inoltre di personalizzare il loro aspetto. `renderRow()` si aspetta una lista di attributi HTML come primo parametro, così è possibile definire una classe, un id o l'handler JavaScript di un evento per ogni istanza. Il secondo parametro di `render Row()` è una label opzionale che sovrascrive quella dedotta dal nome del widget. Segue un esempio di personalizzazione del form di contatto:

    [php]
    // in modules/pippo/templates/contactSuccess.php
    <?php echo $form->renderFormTag('pippo/contact') ?>
      <table>
        <?php echo $form['name']->renderRow(array('size' => 25, 'class' => 'pippo'), 'Your Name') ?>
        <?php echo $form['email']->renderRow(array('onclick' => 'this.value = "";'), 'Your Email') ?>
        <?php echo $form['message']->renderRow() ?>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

A volte può essere necessario produrre le label e l'input di ogni campo in una lista con tag `<li>` anziché in una tabella con tag `<tr>`. Un campo "riga" è costituito da una label, un messaggio di errore opzionale (aggiunto dal sistema di validazione spiegato nel seguito di questo capitolo), un testo di help e un widget (si noti che il widget può consistere di più di un controllo form). Così com'è possibile produrre i vari campi di un form uno per uno, è anche possibile rendere le varie parti di un form indipendentemente. Anziché usare renderRow(), si può scegliere di usare render() (per il widget), `renderError()`, `renderLabel` e `renderHelp()`. Ad esempio, se si desidera generare tutto il form con tag `<li>`, si scriva il template come segue:

    [php]
    // in modules/pippo/templates/contactSuccess.php
    <?php echo $form->renderFormTag('pippo/contact') ?>
      <ul>
        <?php foreach ($form as $field): ?>
        <li>
          <?php echo $field->renderLabel() ?>
          <?php echo $field->render() ?>
        </li>
        <?php endforeach; ?>
        <li>
          <input type="submit" />
        </li>
      </ul>
    </form>

L'HTML generato è il seguente:

    [php]
    <form action="/frontend_dev.php/pippo/contact" method="post">
      <ul>
        <li>
          <label for="name">Name</label>
          <input type="text" name="name" id="name" />
        </li>
        <li>
          <label for="email">Email</label>
          <input type="text" name="email" id="email" />
        </li>
        <li>
          <label for="subject">Subject</label>
          <select name="subject" id="subject">
            <option value="0">Subject A</option>
            <option value="1">Subject B</option>
            <option value="2">Subject C</option>
          </select>
        </li>
        <li>
          <label for="message">Message</label>
          <textarea rows="4" cols="30" name="message" id="message"></textarea>
        </li>
        <li>
          <input type="submit" />
        </li>
      </ul>
    </form>

>**TIP**
>Il campo di una riga è la rappresentazione di tutti gli elementi di un campo form (label, messaggio di errore, testo di help, form input) usando un formattatore. Per default, symfony usa un formattatore "tabella", e questo è il motivo per cui `renderRow()` restituisce un insieme di tag `<tr>`, `<th>` e `<td>`. Alternativamente, è possibile ottenere lo stesso codice HTML di cui sopra semplicemente specificando il formattatore alternativo "list" per il form, come segue:

    [php]
    // in modules/pippo/templates/contactSuccess.php
    <?php echo $form->renderFormTag('pippo/contact') ?>
      <ul>
        <?php echo $form->renderUsing('list') ?>
        <li>
          <input type="submit" />
        </li>
      </ul>
    </form>

-

>**TIP**
>Fare riferimento alla documentazione delle API per la classe `sfWidgetFormSchemaFormatter` per imparare come creare un proprio formattatore.

I widget dei form
-----------------

Ci sono molti widget di form disponibili per comporre i propri form. Tutti i widget accettano come minimo l'opzione `default`.

Per un widget è anche possibile definire la label e tutti gli attributi HTML, quando si crea il form:

    [php]
    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'name'    => new sfWidgetFormInput(array('label' => 'Your Name'), array('size' => 25, 'class' => 'pippo')),
      'email'   => new sfWidgetFormInput(array('default' => 'me@example.com', 'label' => 'Your Email'), array('onclick' => 'this.value = "";')),
      'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
      'message' => new sfWidgetFormTextarea(array(), array('rows' => '20', 'cols' => 5)),
    ));

Symfony usa questi parametri per mostrare il widget, ma è ancora possibile sovrascriverli passando dei parametri personalizzati a `renderRow()` nel template.

>**TIP**: Come alternativa alla chiamata di `setWidgets()` con un array associativo, è possibile chiamare più volte `setWidget($nome, $widget)`.

### I widget standard

Nel seguito viene presentata una lista dei tipi di widget disponibili, e di come essi vengono tradotti in HTML attraverso `renderRow()`:

    [php]
    // Text input
    $form->setWidget('full_name', new sfWidgetFormInput(array('default' => 'John Doe')));
      <label for="full_name">Full Name</label>
      <input type="text" name="full_name" id="full_name" value="John Doe" />

    // Textarea
    $form->setWidget('address', new sfWidgetFormTextarea(array('default' => 'Enter your address here'), array('cols' => 20, 'rows' => 5)));
      <label for="address">Address</label>
      <textarea name="address" id="address" cols="20" rows="5">Enter your address here</textarea>

    // Password input
    // Note that 'password' type widgets don't take a 'default' parameter for security reasons
    Notare che il tipo di widget'password'
    $form->setWidget('pwd', new sfWidgetFormInputPassword());
      <label for="pwd">Pwd</label>
      <input type="password" name="pwd" id="pwd" />

    // Hidden input
    $form->setWidget('id', new sfWidgetFormInputHidden(array('default' => 1234)));
      <input type="hidden" name="id" id="id" value="1234" />

    // Checkbox
    $form->setWidget('single', new sfWidgetFormInputCheckbox(array('value_attribute_value' => 'single', 'default' => true)));
      <label for="single">Single</label>
      <input type="checkbox" name="single" id="single" value="true" checked="checked" />

Ci sono altre opzioni disponibili per ogni widget: fare riferimento alla documentazione delle API per una descrizione completa dei parametri che ogni widget si aspetta e di come esso genera l'HTML.

### Widget di tipo lista

Ogni volta che gli utenti devono fare una scelta tra una lista di valori, e se essi possono selezionare una o più opzioni in questa lista, un singolo widget risponde a tutte le esigenze: il widget `choice`. 
In base alle impostazioni di due parametri opzionali (`multiple` e `expanded`), questo widget genera l'HTML in maniera differente:

                      | multiple=false        | multiple=true
                      | (default)             |
      ----------------|-----------------------|---------------------
      expanded=false  |    Dropdown list      |    Dropdown box
      (default)       |    (`<select>`)       | (`<select multiple>`)
      ----------------|-----------------------|----------------------
      expanded=true   | Lista di Radiobuttons | Lista di checkboxes
                      |                       |

Il widget `choice` si attende come minimo un parametro `choices` costituito da un array associativo che definisca il valore e il testo di ogni opzione. Segue un esempio per ogni sintassi:

    [php]
    // Dropdown list (select)
    $form->setWidget('country', new sfWidgetFormChoice(array(
      'choices'   => array('' => 'Seleziona dalla lista', 'us' => 'USA', 'ca' => 'Canada', 'uk' => 'UK', 'altro'),
      'default'   => 'uk'
    )));
    // symfony rende il widget in HTML come segue:
    <label for="country">Country</label>
    <select id="country" name="country">
      <option value="">Seleziona dalla lista</option>
      <option value="us">USA</option>
      <option value="ca">Canada</option>
      <option value="uk" selected="selected">UK</option>
      <option value="0">altro</option>
    </select>
    
    // Dropdown box a scelta multipla
    $form->setWidget('languages', new sfWidgetFormChoice(array(
      'multiple' => 'true',
      'choices'  => array('en' => 'English', 'fr' => 'French', 'other'),
      'default'  => array('en', 0)
    )));
    // symfony render il widget in HTML come segue:
    <label for="languages">Language</label>
    <select id="languages" multiple="multiple" name="languages[]">
      <option value="en" selected="selected">English</option>
      <option value="fr">French</option>
      <option value="0" selected="selected">other</option>
    </select>

    // Lista di Radiobuttons
    $form->setWidget('gender', new sfWidgetFormChoice(array(
      'expanded' => 'true,
      'choices'  => array('m' => 'Maschile', 'f' => 'Femminile'),
      'class'    => 'gender_list'
    )));
    // symfony renders the widget in HTML as
    <label for="gender">Gender</label>
    <ul class="gender_list">
      <li><input type="radio" name="gender" id="gender_m" value="m"><label for="gender_m">Maschile</label></li>
      <li><input type="radio" name="gender" id="gender_f" value="f"><label for="gender_f">Femminile</label></li>
    </ul>
    
    // Lista di checkboxes
    $form->setWidget('interests', new sfWidgetFormChoice(array(
      'multiple' => 'true',
      'expanded' => true,
      'choices' => array('Programmazione', 'Altro')
    )));
    // symfony renders the widget in HTML as
    <label for="interests">Interests</label>
    <ul class="interests_list">
      <li><input type="checkbox" name="interests[]" id="interests_0" value="0"><label for="interests_0">Programmazione</label></li>
      <li><input type="checkbox" name="interests[]" id="interests_1" value="1"><label for="interests_1">Altro</label></li>
    </ul>

>**Tip**: Si noti che symfony definisce automaticamente un attributo `id` per ogni input del form, basato su una combinazione del nome e del valore del widget. È possibile sovrascrivere l'attributo `id` widget per widget, o alternativamente impostare una regola globale per l'intero form usando il metodo 'setIdFormat()':

    [php]
    // in modules/pippo/actions/actions.class.php
    $this->form = new sfForm();
    $this->form->setIdFormat('my_form_%s');

### I widget per le Foreign Keys

Quando si modificano gli oggetti del modello attraverso un form, si presenta sempre una particolare lista di scelte: la lista di oggetti che possono essere messi in relazione con quello attuale. Questo accade quando i modelli sono in relazione uno-a-molti o molti-a-molti. Fortunatamente, il plugin `sfPropelPlugin` distribuito insieme a symfony, offre un widget `sfWidgetFormPropelChoice` utile proprio in questi casi (e naturalmente `sfDoctrinePlugin` offre un analogo widget `sfWidgetFormDoctrineChoice`).

Ad esempio, se una `Section` ha molti `Articles`, si dovrebbe essere in grado di scelte una sezione tra quelle esistenti quando si edita un articolo. Per fare questo, un `ArticleForm` dovrebbe usare il widget `sfWidgetFormPropelChoice`:

    [php]
    $articleForm = new sfForm();
    $articleForm->setWidgets(array(
      'id'        => sfWidgetFormInputHidden(),
      'title'     => sfWidgetFormInputText(),
      'section_id' => sfWidgetFormPropelChoice(array(
        'model'  => 'Section',
        'column' => 'name'
      )
    )));

Questo mostra una lista delle sezioni esistenti... purché si sia definito un metodo `__toString()` nella classe del modello `Section`. Questo perché symfony prima richiama gli oggetti `Section` disponibili, e popola un widget `choice` con essi, tentando di convertirli in stringa con `__toString()`. Il modello `Section` dunque dovrebbe definire almeno il seguente metodo:

    [php]
    // in lib/model/Section.php
    public function __toString()
    {
      return $this->getName();
    }

Il widget `sfWidgetFormPropelChoice` è un'estensione del widget `sfWidgetFormChoice`, così è possibile usare l'opzione 'multiple' per trattare le relazioni molti-a-molti, e l'opzione 'expanded' per cambiare il modo in cui il widget è reso.

Se si desidera ordinare la lista di scelte in un modo particolare o filtrarle in modo da mostrare solo una porzione delle scelte disponibili, è possibile usare l'opzione `criteria` per passare un oggetto `Criteria` al widget. Doctrine supporta lo stesso tipo di personalizzazione: è possibile passare un oggetto `Doctrine_Query` al widget con l'opzione `query`.

### I widget per le date

I widget per data e ora restituiscono un insieme di liste drop-down, popolate con i valori disponibili per il giorno, il mese, l'anno, l'ora o il minuto.

    [php]
    // Data
    $years = range(1950, 1990);
    $form->setWidget('ddn', new sfWidgetFormDate(array(
      'label'   => 'Data di nascita',
      'default' => '01/01/1950',  // può essere un timestamp o una stringa comprensibile da strtotime()
      'years'   => array_combine($years, $years)
    )));
    // symfony rende il widget in HTML come segue:
    <label for="ddn">Data di nascita</label>
    <select id="ddn_month" name="ddn[month]">
      <option value=""/>
      <option selected="selected" value="1">01</option>
      <option value="2">02</option>
      ...
      <option value="12">12</option>
    </select> /
    <select id="ddn_day" name="ddn[day]">
      <option value=""/>
      <option selected="selected" value="1">01</option>
      <option value="2">02</option>
      ...
      <option value="31">31</option>
    </select> /
    <select id="ddn_year" name="ddn[year]">
      <option value=""/>
      <option selected="selected" value="1950">1950</option>
      <option value="1951">1951</option>
      ...
      <option value="1990">1990</option>
    </select>
    
    // Ora
    $form->setWidget('start', new sfWidgetFormTime(array('default' => '12:00')));
    // symfony rende il widget in HTML come segue:
    <label for="start">Start</label>
    <select id="start_hour" name="start[hour]">
      <option value=""/>
      <option value="0">00</option>
      ...
      <option selected="selected" value="12">12</option>
      ...
      <option value="23">23</option>
    </select> :
    <select id="start_minute" name="start[minute]">
      <option value=""/>
      <option selected="selected" value="0">00</option>
      <option value="1">01</option>
      ...
      <option value="59">59</option>
    </select>

    // Data e ora
    $form->setWidget('end', new sfWidgetFormDateTime(array('default' => '01/01/2008 12:00')));
    // symfony rende il widget in HTML come 5 liste dropdown per mese, giorno,anno, ora e minuto

Naturalmente, si può personalizzare il formato della data per mostrarla in stile Europeo anziché Internazionale (`%day%/%month%/%year%` invece di `%month%/%day%/%year%`), si può scegliere l'orario a 12 ore anziché 24, si possono definire valori personalizzati per la prima opzione di ogni dropdown box, e si possono definire limiti per i possibili valori. Ancora una volta, si rimanda alla documentazione delle API per maggiori dettagli riguardo le opzioni di questi widget.

I widget data sono un buon esempio della potenza dei widget in symfony. Un widget non è semplicemente l'input di un form. Esso può essere una combinazione di più input, che symfony può rendere e leggere in maniera trasparente.

### I widget I18n

Nelle applicazioni multilingua, le date devono essere mostrare in un formato che si accordi con la cultura dell'utente (si veda il Capitolo 13 per dettagli riguardo cultura e localizzazione). Per facilitare questa localizzazione nei form, symfony offre un widget `sfWidgetFormI18nDate`, che si basa sulla `culture` dell'utente per stabilire i parametri di formattazione delle date. È anche possibile specificare un `month_format` per visualizzare una lista drop-down con i nomi dei mesi (nella lingua dell'utente) invece dei numeri.

    [php]
    // Data
    $years = range(1950, 1990);
    $form->setWidget('dob', new sfWidgetFormI18nDate(array(
      'culture'      => $this->getUser()->getCulture(),
      'month_format' => 'name',   // A scelta tra 'name' (default), 'short_name', e 'number' 
      'label'        => 'Date of birth',
      'default'      => '01/01/1950',
      'years'        => array_combine($years, $years)
    )));
    // Per un utente di lingua inglese, symfony rende il widget come segue:
    <label for="dob">Date of birth</label>
    <select id="dob_month" name="dob[month]">
      <option value=""/>
      <option selected="selected" value="1">January</option>
      <option value="2">February</option>
      ...
      <option value="12">December</option>
    </select> /
    <select id="dob_day" name="dob[day]">...</select> /
    <select id="dob_year" name="dob[year]">...</select>
    // Per un utente di lingua francese, symfony rende il widget come segue:
    <label for="dob">Date of birth</label>
    <select id="dob_day" name="dob[day]">...</select> /
    <select id="dob_month" name="dob[month]">
      <option value=""/>
      <option selected="selected" value="1">Janvier</option>
      <option value="2">Février</option>
      ...
      <option value="12">Décembre</option>
    </select> /
    <select id="dob_year" name="dob[year]">...</select>

Dei widget simili esistono per ora (`sfWidgetFormI18nTime`) e data/ora `sfWidgetFormI18nDateTime`).

Ci sono due liste drop-down che appaiono in molti form e che dipendono anch'essi dalla cultura: i selettori di paese e di lingua. Symfony fornisce due widget specifici per questi scopi. Non sarà necessario definire le 'choices' in questi widget, in quanto symfony le popolerà con una lista di nazioni e lingue nella lingua dell'utente (purché l'utente parli una delle 250 lingue supportate da symfony).

    [php]
    // Elenco di paesi
    $form->setWidget('country', new sfWidgetFormI18nCountryChoice(array('default' => 'UK')));
    // Per un utente di lingua inglese, symfony rende il widget in HTML come segue:
    <label for="country">Country</label>
    <select id="country" name="country">
      <option value=""/>
      <option value="AD">Andorra</option>
      <option value="AE">United Arab Emirates</option>
      ...
      <option value="ZWD">Zimbabwe</option>
    </select>

    // Elenco di lingue
    $form->setWidget('language', new sfWidgetFormI18nLanguageChoice(array(
      'languages' => array('en', 'fr', 'de'),  // optional restricted list of languages
      'default'   => 'en'
    )));
    // Per un utente di lingua inglese, symfony rende il widget in HTML come segue:
    <label for="language">Language</label>
    <select id="language" name="language">
      <option value=""/>
      <option value="de">German</option>
      <option value="en" selected="selected">English</option>
      <option value="fr">French</option>
    </select>

### I widget di tipo file

Trattare con gli input di tipo file non è più complicato che trattare con gli altri widget:

    [php]
    // Input file
    $form->setWidget('picture', new sfWidgetFormInputFile());
    // symfony rende il widget in HTML come segue:
    <label for="picture">Picture</label>
    <input id="picture" type="file" name="picture"/>
    // Ogni volta che un form contiene un file widget, renderFormTag() restituisce un tag <form> con l'opzione multipart
    
    // Input file modificabile
    $form->setWidget('picture', new sfWidgetFormInputFileEditable(array('default' => '/images/pippo.png')));
    // symfony rende il widget in HTML come un file input tag, insieme a una preview del file corrente

>**TIP**: Esistono molti widget addizionali forniti da plugin di terze parti. È possibile trovare un widget editor visuale, un widget calendario o altri widget 'rich UI' per varie librerie JavaScript. Consultare l'elenco dei [plugin](http://www.symfony-project.org/plugins/) per ulteriori dettagli.

Gestire le richieste dei form
-----------------------------

Quando gli utenti riempiono un form e lo inviano, il server deve recuperare i dati dalla richiesta e fare alcune cose con essi. La classe `sfForm` fornisce tutti i metodi necessari per fare questo in un paio di linee di codice. 

### Gestione semplice dei form

Dato che i widget restituiscono dei campi form HTML regolari, ricevere il loro valore nell'azione che tratta l'invio dei form è semplice: basta testare i relativi parametri della richiesta. Per il form di contatto di esempio, l'azione potrebbe essere scritta come segue:

    [php]
    // in modules/pippo/actions/actions.class.php
    public function executeContact($request)
    {
      // Definizione del form
      $this->form = new sfForm();
      $this->form->setWidgets(array(
        'nome'    => new sfWidgetFormInputText(),
        'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
        'oggetto' => new sfWidgetFormChoice(array('choices' => array('Oggetto A', 'Oggetto B', 'Oggetto C'))),
        'messaggio' => new sfWidgetFormTextarea(),
      ));

      // Gestione della richiesta
      if ($request->isMethod('post'))
      {
        // Gestisce l'invio del form
        $name = $request->getParameter('nome');
        // Fa quello che deve fare...
        // ...
        $this->redirect('pippo/bar');
      }
    }

Se il metodo della richiesta è `GET`, questa azione termina con un `sfView::SUCCESS`, quindi rende il template `contactSuccess` per mostrare il form. Se il metodo della richiesta è `POST`, l'azione gestisce l'invio del form e reindirizza a un'altra azione. Perché questo funzioni, l'azione target del tag `<form>` deve essere la stessa che sta mostrando il form. Questo spiega perché negli esempi precedenti è stato usato `pippo/contact` come target del form:

    [php]
    // in modules/pippo/templates/contactSuccess.php
    <?php echo $form->renderFormTag('pippo/contact') ?>
    ...

### Gestione dei form con validazione dei dati

In pratica, gestire l'invio dei form non si riduce a ricevere i valori inseriti dall'utente. In molti casi il controller dell'applicazione deve:

 1. Controllare che i dati siano conformi a un insieme di regole predefinite (campi richiesti, formato delle email, ecc.)
 2. Opzionalmente trasformare alcuni dati di input per renderli comprensibili (togliere gli spazi bianchi, convertire le date in formato PHP, ecc.)
 3. Se i dati non sono validi, mostrare nuovamente il form, con messaggi di errore dove necessario
 4. Se i dati sono corretti, fare quanto serve con essi, quindi rinviare a un'altra azione.

Symfony fornisce un modo automatico di validare i dati inseriti confrontandoli con un insieme di regole predefinite. Prima definisce un insieme di validatori per ogni campo. Quindi, quando il form è inviato, collega l'oggetto form con i valori inseriti dall'utente (ad esempio, richiama i valori inseriti e li inserisce nel form). Infine, chiede al form di controllare che i dati siano validi. L'esempio che segue mostra come verificare che il valore recuperato dal widget `email` sia in effetti un indirizzo email, e che `message` abbia una dimensione minima di 4 caratteri:

    [php]
    // in modules/pippo/actions/actions.class.php
    public function executeContact($request)
    {
      // Define the form
      $this->form = new sfForm();
      $this->form->setWidgets(array(
        'name'    => new sfWidgetFormInputText(),
        'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
        'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
        'message' => new sfWidgetFormTextarea(),
      ));
      $this->form->setValidators(array(
        'name'    => new sfValidatorString(),
        'email'   => new sfValidatorEmail(),
        'subject' => new sfValidatorString(),
        'message' => new sfValidatorString(array('min_length' => 4))
      ));

      // Gestione della richiesta
      if ($request->isMethod('post'))
      {
        $this->form->bind(/* user submitted data */);
        if ($this->form->isValid())
        {
          // Gestisce l'invio del form
          // ...

          $this->redirect('pippo/bar');
        }
      }
    }

`setValidators()` usa una sintassi simile al metodo `setWidgets()`. `sfValidatorEmail` e `sfValidatorString` sono due delle numerose classi di validazione di symfony, elencate più avanti in questo capitolo. Naturalmente, `sfForm` fornisce anche un metodo `setValidator()` per aggiungere dei validatori uno per uno.

Per inserire i dati della richiesta nel form e collegarli, si usa il metodo 'sfForm::bind()'. Un form deve essere collegato con qualche dato per controllare la loro validità.

'isValid()' controlla che tutti i validatori registrati siano superati. In questo caso, `isValid()` restituisce `true`, e l'azione può procedere con la submission del form. Se il form non è valido, l'azione termina con il default `sfView::SUCCESS` e mostra nuovamente il fom. Il form però non viene mostrato con i valori di default come la prima volta: gli input del form sono riempiti con i dati inseriti in precedenza dall'utente, e dei messaggi di errori appaiono dove i validatori non sono stati superati.

![Form non valido](http://www.symfony-project.org/images/forms_book/en/02_01.png)

>**TIP**: Il processo di validazione non si ferma quando il form incontra un campo non valido. `isValid()` processa tutti i dati del form e controlla tutti i campi alla ricerca di errori, per evitare di mostrare nuovi messaggi di errori quando l'errore corregge i suoi errori e invia il form nuovamente.

### Utilizzo di dati form puliti

Nell'elenco precedente, non abbiamo definito i dati della richiesta ricevuti dal form durante il processo di bind. Il problema è che la richiesta non contiene solo i dati del form. Essa contiene anche header, cookie, parametri passati come argomenti `GET`, e tutto questo potrebbe inquinare il processo di bind. Una buona pratica è di passare solo i dati del form al metodo `bind()`.

Fortunatamente, symfony offre un modo per denominare tutti gli input del form usando un array. Si può definire il formato dell'attributo nome con il metodo `setNameFormat()` nell'azione quando si definisce il form, come segue:

    [php]
    // in modules/pippo/actions/actions.class.php
    // Definizione del form
    $this->form->setNameFormat('contact[%s]');

In questo modo tutti gli input vengono generati con un nome del tipo `form[NOME_WIDGET]` anziché semplicemente `NOME_WIDGET`:

    [php]
    <label for="contact_name">Name</label>
    <input type="text" name="contact[name]" id="contact_name" />
    ...
    <label for="contact_email">Email</label>
    <input type="text" name="contact[email]" id="contact_email" value="me@example.com" />
    ...
    <label for="contact_subject">Subject</label>
    <select name="contact[subject]" id="contact_subject">
      <option value="0">Subject A</option>
      <option value="1">Subject B</option>
      <option value="2">Subject C</option>
    </select>
    ...
    <label for="contact_message">Message</label>
    <textarea rows="4" cols="30" name="contact[message]" id="contact_message"></textarea>

L'azione può adesso recuperare il parametro `contact` della richiesta in una singola variabile:

    [php]
    // in modules/pippo/actions/actions.class.php
    // Gestione della richiesta
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('contact'));
      if ($this->form->isValid())
      {
        // Handle the form submission
        $contact = $this->form->getValues();
        $name = $contact['name'];

        // Or to get a specific value
        $name = $this->form->getValue('name');

        // Fa qualcosa...
        // ...
        $this->redirect('pippo/bar');
      }
    }

Quando il metodo `bind()` riceve un array di parametri, symfony automaticamente evita di inserire dei campi addizionali dal lato client. Questa caratteristica di sicurezza fa sì che la validazione del form fallisca se l'array dei parametri `contact` contiene un campo che non appare nella definizione originale del form.

Si noterà un'ulteriore differenza nel codice dell'azione appena vista rispetto a quello visto precedentemente. L'azione usa l'array di valori passato dall'oggetto form (`$form->getValues()`) piuttosto di quelli provenienti dalla richiesta. Questo perché i validatori hanno la capacità di filtrare l'input e pulirlo, cosicché è sempre meglio fare affidamento sui dati recuperati dall'oggetto form (attraverso `getValues()` o `getValue()` piuttosto che su quelli provenienti direttamente dalla richiesta. Per campi compositi (come quelli dei widget data), i dati restituiti da `getValues()` sono già ricomposti nei nomi originali:

    [php]
    // I controlli form di un widget data...
    <label for="contact_dob">Date of birth</label>
    <select id="contact_dob_month" name="contact[dob][month]">...</select> /
    <select id="contact_dob_day" name="contact[dob][day]">...</select> /
    <select id="contact_dob_year" name="contact[dob][year]">...</select>
    // ...risultano nell'azione in tre parametri request:
    $contact = $request->getParameter('contact');
    $month = $contact['dob']['month'];
    $day = $contact['dob']['day'];
    $year = $contact['dob']['year'];
    $dateOfBirth = mktime(0, 0, 0, $month, $day, $year);
    // Ma se si usa getValues(), è possibile ricavare direttamente la data corretta
    $contact = $this->form->getValues();
    $dateOfBirth = $contact['dob'];

Dunque è meglio prendere l'abitudine di usare sempre una sintassi di tipo array per i propri campi form (usando `setNameFormat()`) e di usare sempre l'output pulito del form (usando `getValues()`).

### Personalizzare la visualizzazione dei messaggi di errore

Da dove vengono i messaggi di errore mostrati nella schermata precedente? Ebbene, un widget è fatto di quattro componenti e il messaggio di errore è uno di questi. Infatti il formatter predefinito (table) rende la riga di un campo come segue:

    [php]
    <?php if ($field->hasError()): ?>
    <tr>
      <td colspan="2">
        <?php echo $field->renderError() ?>           // Lista di errori
      </td>
    </tr>
    <?php endif; ?>
    <tr>
      <th><?php echo $field->renderLabel() ?></th>    // Label
      <td>
        <?php echo $field->render() ?>                // Widget
        <?php if ($field->hasHelp()): ?>
        <br /><?php echo $field->renderHelp() ?>      // Help
        <?php endif; ?>
      </td>
    </tr>

Usando uno qualsiasi dei metodi visti sopra, si può personalizzare dove e come il messaggio di errore appare per ogni campo. In aggiunta, si può mostrare un messaggio di errore globale sopra il form se esso non è valido:

    [php]
    <?php if ($form->hasErrors()): ?>
      Il form ha alcuni errori da correggere.
    <?php endif; ?>

### Personalizzare i validatori

In un form tutti i campi devono avere un validatore e per default tutti i campi sono richiesti. Se si deve impostare un campo come opzionale, bisogna passare l'opzione `required` al validatore impostandola su 'false'. Ad esempio, la lista che segue mostra come rendere il campo `name` richiesto e il campo 'email' opzionale:

    [php]
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorEmail(array('required' => false)),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4))
    ));

Si può applicare più di un validatore a un singolo campo. Ad esempio, si può voler controllare che il campo `email` soddisfi sia il validatore `sfValidatorEmail` che quello `sfValidatorString` con una dimensione minima di 4 caratteri. In tal caso, si usa il validatore `sfValidatorAnd` per combinare i due validatori, passandogli come argomenti i due validatori `sfValidatorEmail` e `sfValidatorString`:

    [php]
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorAnd(array(
        new sfValidatorEmail(),
        new sfValidatorString(array('min_length' => 4)),
      ), array('required' => false)),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4))
    ));

Se entrambi i validatori sono validi, il campo 'email' viene dichiarato valido. Similmente, si può usare il validatore `sfValidatorOr` per combinare più validatori. È sufficiente che uno di essi sia valido perché il campo sia dichiarato valido.

Ogni validatore invalido risulta in un messaggio di errore nel campo. Questi messaggi di errore sono in inglese, ma è possibile usare gli helper di internazionalizzazione di symfony. Se un progetto usa altri linguaggi, è possibile tradurre facilmente i messaggi di errore con un dizionario i18n. Alternativamente, ogni validatore prevede un terzo parametro per personalizzare i suoi messaggi di errore. Ogni validatore ha almeno due messaggi di errore: il messaggio `required` e quello `invalid`. Alcuni validatori possono mostrare messaggi di errore per uno scopo differente, e supportano sempre l'override dei messaggi di errore attraverso il loro terzo parametro:

    [php]
    // in modules/pippo/actions/actions.class.php
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorEmail(array(), array(
        'required'   => 'Please provide an email',
        'invalid'    => 'Please provide a valid email address (me@example.com)'
      )),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4), array(
        'required'   => 'Please provide a message',
        'min_length' => 'Please provide a longer message (at least 4 characters)'
      ))
    ));

Naturalmente, questi messaggi personalizzati vengono resi nei template attraverso gli helper i18n, così le applicazioni multilingua possono anche tradurre i messaggi di errore personalizzati in un dizionario (si veda il Capitolo 13 per ulteriori dettagli).

### Applicare un validatore a più campi

La sintassi usata sopra per definire i validatori in un form non consente di verificare che due campi siamo validi *contemporaneamente*. Ad esempio, in un form di registrazione, ci sono spesso due campi `password` che devono corrispondere, altrimenti la registrazione viene rifiutata. Ogni campo password non è valido per sè stesso, ma solo se associato con l'altro campo. 

Questo spiega perché sia possibile impostare un validatore 'multiplo' attraverso `setPostValidator()` per impostare i validatori che lavorano su diversi valori. Il post-validatore è eseguito dopo tutti gli altri validatori e riceve un array di valori ripuliti. Se si necessita di validare i dati grezzi provenienti dagli input del form, si può invece usare il metodo `setPreValidator()`.

Una tipica definizione di un form di registrazione potrebbe apparire come segue:

    [php]
    // in modules/pippo/actions/actions.class.php
    // Definizione del form
    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'login'     => new sfWidgetFormInputText(),
      'password1' => new sfWidgetFormInputText(),
      'password2' => new sfWidgetFormInputText()
    );
    $this->form->setValidators(array(
      'login'     => new sfValidatorString(), // login è richiesto
      'password1' => new sfValidatorString(), // password1 è richiesta
      'password2' => new sfValidatorString(), // password2 è richiesta
    ));
    $this->form->setPostValidators(new sfValidatorSchemaCompare('password1', '==', 'password2'));

Il validatore `sfValidatorSchemaCompare` è uno speciale validatore multiplo che riceve tutti i valori ripuliti e può prendere due di essi per una comparazione. Naturalmente è possibile definire più di un post-validatore usando i validatori `sfValidatorAnd` e `sfValidatorOr`.

Validatori
----------

Symfony un gran numero di validatori. Si ricordi che ogni validatore accetta un array di opzioni e un array di errori come argomenti, dei quali è necessario personalizzare almeno i messaggi di errore `required` e `invalid`.

    [php]
    // validatore stringa
    $form->setValidator('message', new sfValidatorString(array(
      'min_length' => 4,
      'max_length' => 50,
    ),
    array(
      'min_length' => 'Inserire un messaggio più lungo',
      'max_length' => 'Inserire un messaggio più sintetico',
    )));
    
    // validatore numero
    $form->setValidator('age', new sfValidatorNumber(array( // usare 'sfValidatorInteger' se si desiderano solo valori interi
      'min'  => 18,
      'max'  => 99.99,
    ),
    array(
      'min' => 'È necessario avere almeno 18 anni per accedere a questo servizio',
      'max' => 'Ti stai prendendo gioco di me? Le persone sopra i 100 anni non usano Internet',
    )));
    
    // validatore email
    $form->setValidator('email', new sfValidatorEmail());
    
    // validatore URL
    $form->setValidator('website', new sfValidatorUrl());
    
    // validatore di espressioni regolari
    $form->setValidator('IP', new sfValidatorRegex(array(
      'pattern' => '^[0-9]{3}\.[0-9]{3}\.[0-9]{2}\.[0-9]{3}$'
    )));

Sebbene alcuni controlli form (come le liste drop-down, i checkbox, i gruppi di radio-button) restringano le scelte possibili, un utente malevolo può sempre tentare di intaccare i form manipolando la pagina con Firebug o inviando un'interrogazione con un linguaggio di scripting. Di conseguenza si dovrebbero validare anche i campi che accettano un array limitato di valori:

    [php]
    // validatore booleano
    $form->setValidator('has_signed_terms_of_service', new sfValidatorBoolean());
    
    // validatore scelta (per restringere i valori a una lista)
    $form->setValidator('subject', new sfValidatorChoice(array(
      'choices' => array('Subject A', 'Subject B', 'Subject C')
    )));
    
    // validatore scelta multipla
    $form->setValidator('languages', new sfValidatorChoice(array(
      'multiple' => true,
      'choices' => array('en' => 'English', 'fr' => 'French', 'other')
    )));

Esistono dei validatori per scelte I18n delle liste di paesi (`sfValidatorI18nChoiceCountry`) e di lingue `sfValidatorI18nChoiceLanguage`). Questi validatori accettano una lista ristretta di paesi e lingue, se si desidera limitare le opzioni possibili.

Il validatore `sfValidatorChoice` è usato spesso per validare un widget `sfWidgetFormChoice`. Dato che è possibile usare il widget `sfWidgetFormChoice` per chiavi esterne, symfony fornisce anche un validatore per controllare che il valore della chiave esista nella tabella collegata:

    [php]
    // Validatore Propel
    $form->setValidator('section_id', new sfValidatorPropelChoice(array(
      'model'  => 'Section',
      'column' => 'name'
    )));
    
    // Validatore Doctrine
    $form->setValidator('section_id', new sfValidatorDoctrineChoice(array(
      'model'  => 'Section',
      'column' => 'name'
    )));

Un altro utile validatore legato al Modello è `sfValidatorPropelUnique`, che controlla che un nuovo valore inserito tramite un form non sia in conflitto con un valore esistente in una colonna del database con indice `unique`. Ad esempio, due utenti non possono avere lo stesso `login`, così modificando un oggetto `User` con un form, bisogna aggiungere un validatore `sfValidatorPropelUnique` su questa colonna:

    [php]
    // Validatore Propel unique
    $form->setValidator('nickname', new sfValidatorPropelUnique(array(
      'model'  => 'User', 
      'column' => 'login'
    )));
    
    $form->setValidator('nickname', new sfValidatorDoctrineUnique(array(
      'model'  => 'User', 
      'column' => 'login'
    )));


Per rendere i propri form ancora più sicuri ed evitare attacchi [Cross-Site Request Forgery](http://en.wikipedia.org/wiki/Cross-site_request_forgery), si può abilitare la protezione CSRF:

    [php]
    // CSRF protection - impostare la chiave segreta a una stringa casuale che nessuno conosca
    $form->addCSRFProtection('flkd445rvvrGV34G');

>**TIP**: È possibile impostare la chiave CSRF per l'intero sito nel file 'settings.yml':

    [yml]
    # in apps/myapp/config/settings.yml
    all:
      .settings:
        # Form security secret (CSRF protection)
        csrf_secret:       ##CSRF_SECRET##     # false per disabilitarla

I validatori multipli operano sull'intero form, anziché su un singolo input. Segue una lista dei validatori multipli disponibili:

    [php]
    // validatore compare - confronta due campi 
    $form->setPostValidator(new sfValidatorSchemaCompare('password1', '==', 'password2'));
    
    // Extra field validator: cerca altri campi nella richiesta non presenti nel form
    $form->setOption('allow_extra_fields', false);
    $form->setOption('filter_extra_fields', true);

Modi alternativi di usare un form
---------------------------------

### Classi form

Con tutte le opzioni dei widget, i validatori e i parametri dei form, la definizione del form dei contatti scritta nella classe delle azioni appare piuttosto confusa:

    [php]
    // in modules/pippo/actions/actions.class.php
    // Definizione del form
    $this->form = new sfForm();
    $this->form->setNameFormat('contact[%s]');
    $this->form->setIdFormat('my_form_%s');

    $this->form->setWidgets(array(
      'name'    => new sfWidgetFormInputText(),
      'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
      'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
      'message' => new sfWidgetFormTextarea(),
    ));
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorEmail(),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4))
    ));

La migliore prassi consiste nel creare una classe form con le stesse proprietà e istanziarla in tutte le azioni. Ad esempio, ecco come creare una classe per il form dei contatti:

    [php]
    // in lib/form/ContactForm.class.php
    class ContactForm extends sfForm
    {
      protected static $subjects = array('Subject A', 'Subject B', 'Subject C');

      public function configure()
      {
        $this->setNameFormat('contact[%s]');
        $this->setIdFormat('my_form_%s');
        $this->setWidgets(array(
          'name'    => new sfWidgetFormInputText(),
          'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
          'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
          'message' => new sfWidgetFormTextarea(),
        ));
        $this->setValidators(array(
          'name'    => new sfValidatorString(),
          'email'   => new sfValidatorEmail(),
          'subject' => new sfValidatorString(),
          'message' => new sfValidatorString(array('min_length' => 4))
        ));
        $this->setDefaults(array(
          'email' => 'me@example.com'
        ));
      }
    }

Ora definire un oggetto per il form dei contatti nell'azione è molto semplice:

    [php]
    // in modules/pippo/actions/actions.class.php
    // Definizione del form
    $this->form = new ContactForm();

### Alterare un oggetto form

Quando si usa la definizione di una classe form, il form è definito al di fuori dell'azione. Questo rende l'assegnamento dinamico dei valori predefiniti abbastanza difficoltoso. Ecco perché l'oggetto form riceve un array di valori predefiniti come primo parametro:

    [php]
    // in modules/pippo/actions/actions.class.php
    // Definizione del form
    $this->form = new ContactForm(array('email' => 'me@example.com'));

È anche possibile sovrascrivere i widget esistenti o le impostazioni dei validatori richiamando `setWidget()` o `setValidator()` su un nome di campo esistente.

Del resto i widget e i validatori sono oggetti in symfony, e offrono un'API molto pulita per modificare le loro proprietà:

    [php]
    // in modules/pippo/actions/actions.class.php
    // Definizione del form
    $this->form = new ContactForm();

    // Permettere la selezione più lingue
    $form->getWidget('language')->setOption('multiple', true);
    // Aggiungere un widget 'genere'
    $form->setWidget('genere', new sfWidgetFormChoice(array('expanded' => true, 'choices' => array('m' => 'Male', 'f' => 'Female')), array('class' => 'gender_list')));
    // Cambiare gli attributi HTML del widget 'subject'
    $form->getWidget('subject')->setAttribute('disabled', 'disabled');
    // Rimuovere il campo 'subject'
    unset($form['subject'])
    // Nota: non è possibile rimuovere solo il widget. Rimuovendo il widget vengono rimossi anche i validatori collegati.

    // Cambiare l'errore 'min_length' nel validatore 'message'
    $form->getValidator('message')->setMessage('min_length', 'Messaggio troppo corto');
    // Rendere il campo 'name' opzionale
    $form->getValidator('name')->setOption('required', false);

Classi widget e validator personalizzate
----------------------------------------

Un widget personalizzato è semplicemente una classe che estende `sfWidgetForm`, e fornisce dei metodi `configure()` e `render()`. Si scorra il codice delle classi widget esistenti per una comprensione approfondita del sistema dei widget. Il listato che segue mostra il codice del widget `sfWidgetFormInput` per illustrare la struttura del widget:

    [php]
    class sfWidgetFormInputText extends sfWidgetForm
    {
      /**
       * Configures the current widget.
       * This method allows each widget to add options or HTML attributes during widget creation.
       * Available options:
       *  * type: The widget type (text by default)
       *
       * @param array $options     An array of options
       * @param array $attributes  An array of default HTML attributes
       * @see sfWidgetForm
       */
      protected function configure($options = array(), $attributes = array())
      {
        $this->addOption('type', 'text');
        $this->setOption('is_hidden', false);
      }

      /**
       * Renders the widget as HTML
       *
       * @param  string $name        The element name
       * @param  string $value       The value displayed in this widget
       * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
       * @param  array  $errors      An array of errors for the field
       * @return string An HTML tag string
       * @see sfWidgetForm
       */
      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        return $this->renderTag('input', array_merge(
          array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value), 
          $attributes
        ));
      }
    }

Una classe validator estende `sfValidatorBase` e fornisce dei metodi `configure()` e `doClean()`. Perché `doClean()` e non `validate()`? Perché i validatori fanno due cose: essi controllano che l'input soddisfi un insieme di regole, e opzionalmente puliscono l'input (ad esempio forzando il tipo, eseguendo un trim, convertendo date da stringhe a timestamp, ecc.). Così il metodo `doClean()` deve restituire l'input pulito, o sollevare un'eccezione `sfValidatorError` se l'input non soddisfa una qualsiasi delle regole del validatore. Di seguito viene mostrato questo concetto, con il codice del validatore `sfValidatorInteger`.

    [php]
    class sfValidatorInteger extends sfValidatorBase
    {
      /**
       * Configures the current validator.
       * This method allows each validator to add options and error messages during validator creation.
       * Available options:
       *  * max: The maximum value allowed
       *  * min: The minimum value allowed
       * Available error codes:
       *  * max
       *  * min
       *
       * @param array $options   An array of options
       * @param array $messages  An array of error messages
       * @see sfValidatorBase
       */
      protected function configure($options = array(), $messages = array())
      {
        $this->addOption('min');
        $this->addOption('max');
        $this->addMessage('max', '"%value%" must be less than %max%.');
        $this->addMessage('min', '"%value%" must be greater than %min%.');
        $this->setMessage('invalid', '"%value%" is not an integer.');
      }

      /**
       * Cleans the input value.
       *
       * @param  mixed $value  The input value
       * @return mixed The cleaned value
       * @throws sfValidatorError
       */
      protected function doClean($value)
      {
        $clean = intval($value);
        if (strval($clean) != $value)
        {
          throw new sfValidatorError($this, 'invalid', array('value' => $value));
        }
        if ($this->hasOption('max') && $clean > $this->getOption('max'))
        {
          throw new sfValidatorError($this, 'max', array('value' => $value, 'max' => $this->getOption('max')));
        }
        if ($this->hasOption('min') && $clean < $this->getOption('min'))
        {
          throw new sfValidatorError($this, 'min', array('value' => $value, 'min' => $this->getOption('min')));
        }

        return $clean;
      }
    }

Si veda la documentazione dell'API di symfony per i nomi e la sintassi delle classi widget e validator.

>**SIDEBAR**
>Usare le opzioni per passare parametri alla classe form
>
>Un problema comune coi form è di usare i parametri dell'applicazione, come ad esempio la cultura dell'utente. La via più veloce, ma peggiore, è richiamare l'instanza dell'utente attraverso l'instanza `sfContext`, usando il metodo `sfContext::getInstance()->getUser()`. Tuttavia questa soluzione lega fortemente il form al context, rendendo i test e la riusabilità più difficoltosi. Per evitare questo problema, si può usare semplicemente l'opzione di passare il valore `culture` al form:

>
>     // da un'azione
>     public function executeContact(sfWebRequest $request)
>     {
>       $this->form = new ContactForm(array(), array('culture' => $this->getUser()->getCulture()));
>     }
>
>     // da un test unitario
>     $form = new ContactForm(array(), array('culture' => 'en'));
>
>     class ContactForm extends sfForm
>     {
>       public function configure()
>       {
>         /* ... */
>         $this->setWidget('country', new sfWidgetFormI18NCountry(array('culture' => $this->getOption('culture'))));
>         /* ... */
>       }
>     }
>

Form basati su un Modello
-------------------------

I form sono il modo principale per modificare i record di un database nelle applicazioni web. Molti form nelle applicazioni symfony permettono di editare un oggetto del Modello. Del resto le informazioni necessarie per costruire un form per editare un modello esistono già: sono nello schema. Così symfony fornisce un generatore di form per gli oggetti del Modello, che rende la creazione di form un gioco da ragazzi.

>**Note**: Caratteristiche simili a quelle descritte nel seguito esistono per Doctrine.

### Generare i form dal Modello

Symfony può dedurre i tipi di widget e i validatori da usare per un form, basandosi sullo schema. Si prenda ad esempio lo schema seguente, con l'ORM Propel:

    [yml]
    // config/schema.yml
    propel:
      article:
        id:           ~
        title:        { type: varchar(255), required: true }
        slug:         { type: varchar(255), required: true, index: unique }
        content:      longvarchar
        is_published: { type: boolean, required: true }
        author_id:    { type: integer, required: true, foreignTable: author, foreignReference: id, OnDelete: cascade }
        created_at:   ~

      author:
        id:           ~
        first_name:   varchar(20)
        last_name:    varchar(20)
        email:        { type: varchar(255), required: true, index: unique }
        active:       boolean

Un form per editare un oggetto `Article` dovrebbe usare un widget nascosto per l'id, un widget testo per `title`, un validatore stringa per `title`, ecc. Symfony genera il form automaticamente, semplicemente richiamando il task `propel:build-forms`:

    // propel
    $ php symfony propel:build-forms
    
    // doctrine
    $ php symfony doctrine:build-forms

Per ogni tabella nel modello, questo comando crea due file nella cartella `lib/form/`: una classe `BaseXXXForm`, sovrascritta ogni volta che si richiama il task `propel:build-form`, a una classe `XXXForm` vuota, che estende la precedente. È lo stesso sistema usato dalla generazione delle classi modello di Propel.

Il file `lib/form/base/BaseArticleForm.class.php` generato contiene la traduzione in widget e validatori delle colonne definite per la tabella `article` in `schema.yml`:

    [php]
    class BaseArticleForm extends BaseFormPropel
    {
      public function setup()
      {
        $this->setWidgets(array(
          'id'           => new sfWidgetFormInputHidden(),
          'title'        => new sfWidgetFormInputText(),
          'slug'         => new sfWidgetFormInputText(),
          'content'      => new sfWidgetFormTextarea(),
          'is_published' => new sfWidgetFormInputCheckbox(),
          'author_id'    => new sfWidgetFormPropelChoice(array('model' => 'Author', 'add_empty' => false)),
          'created_at'   => new sfWidgetFormDatetime(),
        ));
        $this->setValidators(array(
          'id'           => new sfValidatorPropelChoice(array('model' => 'Article', 'column' => 'id', 'required' => false)),
          'title'        => new sfValidatorString(array('max_length' => 255)),
          'slug'         => new sfValidatorString(array('max_length' => 255)),
          'content'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
          'is_published' => new sfValidatorBoolean(),
          'author_id'    => new sfValidatorPropelChoice(array('model' => 'Author', 'column' => 'id')),
          'created_at'   => new sfValidatorDatetime(array('required' => false)),
        ));
        $this->setPostValidator(
          new sfValidatorPropelUnique(array('model' => 'Article', 'column' => array('slug')))
        );
        $this->setNameFormat('article[%s]');
        parent::setup();
      }

      public function getModelName()
      {
        return 'Article';
      }
    }

Si noti che, anche se la colonna `id` è un intero, symfony controlla che l'id presentato esista nella tabella usando un validatore `sfValidatorPropelChoice`. Il generatore di form imposta sempre le regole di validazione più restrittive, per assicurare i dati più puliti al database.

### Usare i form del Modello

È possibile personalizzare le classi dei form generati per l'intero progetto aggiungendo del codice al metodo `ArticleForm::configure()`, inizialmente vuoto.

Segue un esempio di manipolazione del model form in un'azione. In questo form, il validatore `slug` è modificato per rendere il campo opzionale, e il widget `author_id` è personalizzato per mostrare solo un sottoinsieme degli autori: solo quelli attivi.

    [php]
    // in lib/form/ArticleForm.class.php
    public function configure()
    {
      $this->getWidget('author_id')->setOption('criteria', $this->getOption('criteria'));
      $this->getValidator('slug')->setOption('required', false);
    }

    // in modules/pippo/actions/actions.class.php
    public function executeEditArticle($request)
    {
      $c = new Criteria();
      $c->add(AuthorPeer::ACTIVE, true);
      
      $this->form = new ArticleForm(
        ArticlePeer::retrieveByPk($request->getParameter('id')),
        array('criteria' => $c)
      );
      
      if ($request->isMethod('post'))
      {
        $this->form->bind($request->getParameter('article'));
        if ($this->form->isValid())
        {
          $article = $this->form->save();

          $this->redirect('article/edit?id='.$author->getId());
        }
      }
    }

Invece di impostare dei valori predefiniti attraverso un array associativo, i form del Modello usano un oggetto del Modello per inizializzare i valori del widget. Per mostrare un form vuoto, è sufficiente passare un nuovo oggetto del Modello.

La gestione dell'invio dei form è grandemente semplificata dal fatto che l'oggetto form contiene un oggetto del Modello incapsulato. Richiamando `$this->form->save()` in un form valido, l'oggetto `Article` incapsulato viene aggiornato con i valori puliti e il suo metodo `save()` viene innescato, purché l'oggetto relativo esista.

>**TIP**: Il codice per l'azione richiesto per trattare con un form è praticamente sempre lo stesso, ma questa non è una ragione per copiarlo da un modulo all'altro. Symfony fornisce un generatore di moduli che crea tutto il codice per le azioni e i template per manipolare un oggetto del Modello attraverso i form di symfony.

Conclusione
-----------

Il componente form di symfony è in sé già un intero framework. Esso facilita la visualizzazione dei form nella Vista attraverso i widget, facilita la validazione e la manipolazione dei form nel Controller attraverso i validatori, a facilita la modifica degli oggetti del Modello attraverso i form del Modello. Nonostante sia progettato con una chiara separazione MVC, il sub-framework dei form è sempre facile da usare. Nella maggior parte dei casi, la generazione del codice riduce la personalizzazione dei propri form a poche linee di codice.

C'è molto di più nelle classi form di symfony rispetto a quanto questo capitolo ha esposto. Infatti esiste un [intero libro](http://www.symfony-project.org/book/forms/1_4/it/) che descrive le loro caratteristiche con l'uso di esempi. E se il form framework non offre i widget o i validatori di cui si ha bisogno, esso è progettato in modo da essere estensibile con la scrittura di una singola classe per fare esattamente quello che serve.
