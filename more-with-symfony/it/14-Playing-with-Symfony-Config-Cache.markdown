Lavorare con la cache della configurazione di symfony
=====================================================

*di Kris Wallsmith*

Uno dei miei obiettivi personali come sviluppatore di symfony, è quello di
snellire il più possibile il flusso di lavoro su un determinato progetto.
Anche se io posso conoscere sia dentro che fuori la base del nostro codice,
questa non è una aspettativa ragionevole per tutto il team. Fortunatamente
symfony fornisce meccanismi per isolare e centralizzare le funzionalità
all'interno di un progetto, rendendo facile per gli altri fare delle modifiche
con il minimo della fatica.  

Le stringhe nei form
--------------------

Un eccellente esempio di questo, è il framework dei form di symfony. Il framework
dei form è un componente potente di symfony, che dà un grande controllo sui form,
spostando la loro visualizzazione e convalida, in oggetti PHP. Questa è una manna
per gli sviluppatori di applicazioni, perché significa che si possono incapsulare
logiche complesse in una sola classe di form, ed estenderla / riutilizzarla in
posti diversi.

Tuttavia, dal punto di vista di chi sviluppa la grafica, questa astrazione
su come visualizzare un form, potrebbe essere problematica. Dare un'occhiata al
seguente form:

![Form nel suo stato predefinito](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_default.png)

La classe che configura il form, assomiglia a questa:

    [php]
    // lib/form/CommentForm.class.php
    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array(
          'min_length' => 12,
        )));
      }
    }

Il form è quindi visualizzato in un template PHP tipo questo:

    <!-- apps/frontend/modules/main/templates/indexSuccess.php -->
    <form action="#" method="post">
      <ul>
        <li>
          <?php echo $form['body']->renderLabel() ?>
          <?php echo $form['body'] ?>
          <?php echo $form['body']->renderError() ?>
        </li>
      </ul>
      <p><button type="submit">Post your comment now</button></p>
    </form>

Chi sviluppa il template ha un po 'di controllo su come questo form viene
visualizzato. Può cambiare le etichette predefinite in modo che siano un po'
più amichevoli: 

    <?php echo $form['body']->renderLabel('Please enter your comment') ?>

Può aggiungere una classe ai campi in input:

    <?php echo $form['body']->render(array('class' => 'comment')) ?>

Queste modifiche sono facili e intuitive. Ma se c'è la necessità di modificare
un messaggio di errore?

![Il form in uno stato di errore](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_error.png)

Il metodo `->renderError()` non accetta parametri, quindi l'unica possibilità
per chi sviluppa i template è quella di aprire il file con la classe del
form, trovare il codice che crea il validatore in questione e modificare il suo
costruttore, in modo che il nuovo messaggio di errore sia associato con
l'appropriato codice di errore.

Nel nostro esempio, chi sviluppa il template avrebbe dovuto apportare la
seguente modifica:

    [php]
    // prima
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    )));

    // dopo
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    ), array(
      'min_length' => 'You haven't written enough',
    )));

C'è un problema? Oops, si! Si è usato un apostrofo dentro una stringa racchiusa
da un apice singolo. Naturalmente noi non avremmo mai fatto un errore simile, ma
che cosa si può dire a chi sviluppa il template e si perde dentro alla classe
di un form?

In tutta serietà, ci si può aspettare che chi sviluppa i template conosca
abbastanza bene il framework dei form di symfony, da individuare esattamente
dove è definito un messaggio di errore? Ci si aspetta che qualcuno che lavora
sulla vista conosca la signature per il costruttore di un validatore?

Si può essere tutti d'accordo sul fatto che la risposta a queste domande sia
no. Chi sviluppatori i template fa un sacco di lavoro prezioso, ma è
semplicemente irragionevole aspettarsi che qualcuno che non scrive codice
per applicazioni, imparare il funzionamento interno del framework dei form di
symfony.

YAML: Una soluzione
-------------------

Per semplificare il processo di modifica delle stringhe di un form, si andrà ad
aggiungere un livello di configurazione YAML che migliora ogni oggetto dei form
quando viene passato alla vista. Il file di configurazione sarà simile a questo:

    [yml]
    # config/forms.yml
    CommentForm:
      body:
        label:        Inserisci il tuo commento
        attributes:   { class: comment }
        errors:
          min_length: Non hai scritto abbastanza

Questo metodo è molto più semplice! La configurazione si spiega da sola e il problema
dell'apostrofo che si è incontrato in precedenza, ora è scomparso. Bene, procediamo!

Filtraggio delle variabili del template
---------------------------------------

La prima sfida è quella di trovare un punto di aggancio in symfony, che
permetta di filtrare ogni variabile del form passata al template, attraverso
questa configurazione. Per fare questo, si usa l'evento `template.filter_parameters`,
che è chiamato dal core di symfony, appena prima di visualizzare un template o
il partial di un template.

    [php]
    // lib/form/sfFormYamlEnhancer.class.php
    class sfFormYamlEnhancer
    {
      public function connect(sfEventDispatcher $dispatcher)
      {
        $dispatcher->connect('template.filter_parameters',
          array($this, 'filterParameters'));
      }

      public function filterParameters(sfEvent $event, $parameters)
      {
        foreach ($parameters as $name => $param)
        {
          if ($param instanceof sfForm && !$param->getOption('is_enhanced'))
          {
            $this->enhance($param);
            $param->setOption('is_enhanced', true);
          }
        }

        return $parameters;
      }

      public function enhance(sfForm $form)
      {
        // ...
      }
    }

>**NOTE**
>Si noti che questo codice controlla una opzione `is_enhanced` su ogni oggetto
>del form, prima di migliorarlo. Questo serve a prevenire che i form vengano
>passati dai template ai partial, per essere migliorati per due volte.

Questa classe di miglioramento ha bisogno di essere collegata nella configurazione
dell'applicazione:

    [php]
    // apps/frontend/config/frontendConfiguration.class.php
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function initialize()
      {
        $enhancer = new sfFormYamlEnhancer($this->getConfigCache());
        $enhancer->connect($this->dispatcher);
      }
    }

Ora che si è in grado di isolare le variabili dei form poco prima che vengano
passate ad un template o a un partial, si ha tutto il necessario per terminare
il lavoro. Il compito finale è quello di applicare ciò che è stato configurato
nello YAML.

Utilizzare lo YAML
------------------

Il modo più semplice per utilizzare questa configurazione YAML in ogni form, è
caricarla in un array e ciclare attraverso ciascuna configurazione:

    [php]
    public function enhance(sfForm $form)
    {
      $config = sfYaml::load(sfConfig::get('sf_config_dir').'/forms.yml');

      foreach ($config as $class => $fieldConfigs)
      {
        if ($form instanceof $class)
        {
          foreach ($fieldConfigs as $fieldName => $fieldConfig)
          {
            if (isset($form[$fieldName]))
            {
              if (isset($fieldConfig['label']))
              {
                $form->getWidget($fieldName)->setLabel($fieldConfig['label']);
              }

              if (isset($fieldConfig['attributes']))
              {
                $form->getWidget($fieldName)->setAttributes(array_merge(
                  $form->getWidget($fieldName)->getAttributes(),
                  $fieldConfig['attributes']
                ));
              }

              if (isset($fieldConfig['errors']))
              {
                foreach ($fieldConfig['errors'] as $code => $msg)
                {
                  $form->getValidator($fieldName)->setMessage($code, $msg);
                }
              }
            }
          }
        }
      }
    }

Ci sono una serie di problemi con questa implementazione. In primo luogo, il
file YAML viene letto dal file system e caricato in `sfYaml` ogni volta che un
form viene migliorato. Questo modo di accedere al file system dovrebbe essere
evitato. In secondo luogo, vi sono più livelli di cicli nidificati e una serie
di condizionali che servono solo a rallentare l'applicazione. La soluzione per
entrambi i problemi si trova nella configurazione della cache di symfony.

La configurazione della cache
-----------------------------

La configurazione della cache è costituita da un insieme di classi che ottimizzano
l'utilizzo dei file di configurazione YAML, automatizzando la loro traduzione in
codice PHP e scrivendo questo codice nella cartella della cache per l'esecuzione.
Questo meccanismo elimina il sovraccarico necessario per caricare il contenuto
del nostro file di configurazione in `sfYaml`, prima di applicare i suoi valori.

Si vuole implementare una configurazione della cache per il nostro form migliorato.
Invece di caricare `forms.yml` in `sfYaml`, viene chiesta una versione pre-compilata
alla configurazione corrente della cache dell'applicazione.

Per fare questo la classe `sfFormYamlEnhancer` avrà bisogno di accedere alla
configurazione corrente della cache dell'applicazione, quindi verrà aggiunta
al costruttore.

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfSimpleYamlConfigHandler');
      }

      // ...
    }

La configurazione della cache ha bisogno di sapere cosa fare quando dall'applicazione
viene richiesto un certo file di configurazione. Per ora è stato detto al
configuratore della cache di usare `sfSimpleYamlConfigHandler` per processare
`forms.yml`. Questo gestore di configurazione, semplicemente analizza lo YAML
in un array e lo mette in cache come codice PHP.

Con la configurazione della cache messa a posto e un gestore della configurazione
registrato per `forms.yml`, si può chiamarlo al posto di `sfYaml`:

    [php]
    public function enhance(sfForm $form)
    {
      $config = include $this->configCache->checkConfig('config/forms.yml');

      // ...
    }

Questo è molto meglio. Non solo si è eliminato il sovraccarico delle ripetute
analisi dello YAML dopo la prima richiesta, ma si è anche passati all'utilizzo
di `include`, che espone questa lettura al bonus della cache nell'op-code.

>**SIDEBAR**
>Sviluppo contro ambienti di produzione
>
>Il funzionamento interno di `->checkConfig()` cambia a seconda che la modalità
>di debug dell'applicazione sia su on o su off. Nell'ambiente `prod`, quando la
>modalità di debug è su off, questo metodo funziona come descritto di seguito:
>
>  * Verifica la presenza di una versione cache del file richiesto
>    * Se esiste, restituisce il percorso di questo file in cache
>    * Se non esiste:
>      * Elabora il file di configurazione
>      * Salva il codice risultante nella cache
>      * Restituisce il percorso del file in cache
>
>Questo metodo funziona in modo diverso quando la modalità debug è su on. Essendo
>che i file di configurazione sono modificati durante lo sviluppo, `->checkConfig()` 
>verifica quando sono stati modificati i file originali e quelli della cache,
>per essere sicuri di utilizzare l'ultima versione. Questo aggiunge alcuni passi
>in più all'interno del metodo, rispetto a quando la modalità debug è su off:
>
>  * Verifica la presenza di una versione in cache del file richiesto
>    * Se non esiste:
>      * Elabora il file di configurazione
>      * Salva il codice risultante nella cache
>    * Se esiste:
>      * Verifica quando sono stati modificati per l'ultima volta il file config e quello in cache
>      * Se il file config è quello modificato più di recente:
>        * Elabora il file di configurazione
>        * Salva il codice risultante nella cache
>  * Restituisce il percorso del file in cache

Copritemi di test!
-----------------------

Prima di andare avanti, verranno scritti alcuni test. Si può iniziare con questo
script di base:

    [php]
    // test/unit/form/sfFormYamlEnhancerTest.php
    include dirname(__FILE__).'/../../bootstrap/unit.php';

    $t = new lime_test(3);

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());
    sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

    $enhancer = new sfFormYamlEnhancer($configuration->getConfigCache());

    // ->enhance()
    $t->diag('->enhance()');

    $form = new CommentForm();
    $form->bind(array('body' => '+1'));

    $enhancer->enhance($form);

    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() enhances labels');
    $t->like($form['body']->render(), '/class="comment"/',
      '->enhance() enhances widgets');
    $t->like($form['body']->renderError(), '/You haven\'t written enough/',
      '->enhance() enhances error messages');

Eseguendo questo test sull'attuale `sfFormYamlEnhancer`, si verifica che tutto
stia funzionando correttamente:

![Test che passano](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_3_ok.png)

Ora si può fare del refactoring, sapendo che i test ci diranno se si stanno
facendo delle modifiche che pregiudicano il corretto funzionamento.

Gestori di configurazione personalizzati
----------------------------------------

Nel miglioramento di cui sopra, ogni variabile form passata a un template
ciclerà su ogni classe del form, configurata in `forms.yml`. Il lavoro viene
eseguito, ma se si passano al template oggetti con più form, o si ha un lungo
elenco di form configurati nello YAML, si inizierà a vedere un impatto nelle
prestazioni. Questa è una buona occasione per scrivere un gestore personalizzato
di configurazione, che possa ottimizzare il processo.

>**SIDEBAR**
>Perché personalizzare?
>
>La scrittura di un gestore personalizzato di configurazione non è per i deboli
>di cuore. Come con qualsiasi creazione di codice, i gestori di configurazione
>possono essere soggetti a errori e difficili da testare, ma i benefici sono
>diversi. Creare al volo la logica "hard-coded", ha i vantaggi sia della
>flessibilità dello YAML, che del basso overhead del codice PHP. Con la cache
>op-code aggiunta al mix (come ad esempio [APC](http://pecl.php.net/apc) o
>[XCache](http://xcache.lighttpd.net/)), i gestori di configurazione sono
>difficili da battere per facilità d'uso e prestazioni.

La maggior parte della magia dei gestori di configurazione, avviene dietro le quinte.
La cache di configurazione si prende cura della logica di cache prima di eseguire
ogni particolare gestore di configurazione, in modo da potersi concentrare solo
sulla generazione del codice necessario per applicare la configurazione YAML.

Ogni gestore di configurazione deve implementare i seguenti due metodi:

 * `static public function getConfiguration(array $configFiles)`
 * `public function execute($configFiles)`

Al primo metodo, `::getConfiguration()`, viene passato un array di percorsi di
file, li analizza e fonde i contenuti in un singolo valore. Nella classe
`sfSimpleYamlConfigHandler` usata sopra, questo metodo include solo una linea:

    [php]
    static public function getConfiguration(array $configFiles)
    {
      return self::parseYamls($configFiles);
    }

La classe `sfSimpleYamlConfigHandler` estende quella astratta
`sfYamlConfigHandler` che comprende una serie di metodi helper per elaborare i
file di configurazione YAML:

 * `::parseYamls($configFiles)`
 * `::parseYaml($configFile)`
 * `::flattenConfiguration($config)`
 * `::flattenConfigurationWithEnvironment($config)`

I primi due metodi implementano la
[configurazione a cascata](http://www.symfony-project.org/reference/1_4/it/03-Configuration-Files-Principles#chapter_03_configurazione_a_cascata) di symfony.
Gli altri due implementano la
[consapevolezza dell'ambiente](http://www.symfony-project.org/reference/1_4/it/03-Configuration-Files-Principles#chapter_03_consapevolezza_dell_ambiente).

Il metodo `::getConfiguration()` del nostro gestore di configurazione, avrà
bisogno di un metodo personalizzato per unire le configurazioni basate
sull'ereditarietà delle classi. Creare un metodo `::applyInheritance()` che
incapsuli questa logica:

    [php]
    // lib/config/sfFormYamlEnhancementsConfigHander.class.php
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $config = self::getConfiguration($configFiles);

        // compila i dati
        $retval = "<?php\n".
                  "// auto-generated by %s\n".
                  "// date: %s\nreturn %s;\n";
        $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'),
          var_export($config, true));

        return $retval;
      }

      static public function getConfiguration(array $configFiles)
      {
        return self::applyInheritance(self::parseYamls($configFiles));
      }

      static public function applyInheritance($config)
      {
        $classes = array_keys($config);

        $merged = array();
        foreach ($classes as $class)
        {
          if (class_exists($class))
          {
            $merged[$class] = $config[$class];
            foreach (array_intersect(class_parents($class), $classes) as $parent)
            {
              $merged[$class] = sfToolkit::arrayDeepMerge(
                $config[$parent],
                $merged[$class]
              );
            }
          }
        }

        return $merged;
      }
    }

Ora si ha un array i cui valori sono stati fusi secondo l'ereditarietà delle
classi. Ciò elimina la necessità di filtrare l'intera configurazione tramite
`instanceof` per ogni oggetto del form. In più, questa fusione è fatta nel
gestore di configurazione, in modo che possa avvenire solo una volta e poi
venga memorizzata nella cache.

Ora si può applicare questa fusione di array all'oggetto form, con un po' di
semplice logica di ricerca:

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfFormYamlEnhancementsConfigHander');
      }

      // ...

      public function enhance(sfForm $form)
      {
        $config = include $this->configCache->checkConfig('config/forms.yml');

        $class = get_class($form);
        if (isset($config[$class]))
        {
          $fieldConfigs = $config[$class];
        }
        else if ($overlap = array_intersect(class_parents($class),
          array_keys($config)))
        {
          $fieldConfigs = $config[current($overlap)];
        }
        else
        {
          return;
        }

        foreach ($fieldConfigs as $fieldName => $fieldConfig)
        {
          // ...
        }
      }
    }

Prima di eseguire nuovamente lo script di test, aggiungiamo l'asserzione per la
nuova classe, con la logica di ereditarietà.

    [yml]
    # config/forms.yml

    # ...

    BaseForm:
      body:
        errors:
          min_length: A base min_length message
          required:   A base required message

Nello script del test, si può verificare che il nuovo messaggio `required`
venga applicato e verificare che i form figli riceveranno quelli ereditati,
anche se non sono configurati per la classe figlio.

    [php]
    $t = new lime_test(5);

    // ...

    $form = new CommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderError(), '/A base required message/',
      '->enhance() considers inheritance');

    class SpecialCommentForm extends CommentForm { }
    $form = new SpecialCommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() applies parent config');

Eseguire lo script di prova aggiornato, per verificare se il form migliorato
sta lavorando come ci si aspetta.

![I test che passano](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_5_ok.png)

Giocare con l'inclusione dei form
---------------------------------

C'è una caratteristica importante nel framework dei form di symfony, che non è
stata ancora considerata: l'inclusione dei form. Se un'istanza di `CommentForm`
è incorporata in un altro form, i miglioramenti che sono stati fatti su
`forms.yml`, non verranno applicati. Questo fatto è abbastanza facile da
verificare con questo script di test:

    [php]
    $t = new lime_test(6);

    // ...

    $form = new BaseForm();
    $form->embedForm('comment', new CommentForm());
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['comment']['body']->renderLabel(),
      '/Please enter your comment/',
      '->enhance() enhances embedded forms');

Questa nuova asserzione dimostra che i form incorporati non sono stati "migliorati":

![I test falliscono](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_not_ok.png)

Mettere a posto questo test, comporta la realizzazione di un gestore di configurazione
più avanzato. Bisogna essere in grado di applicare i miglioramenti configurati
in `forms.yml` in un modo più modulare per i form incorporati, quindi si andrà
a generare un metodo potenziato su misura per ciascuna classe configurata di
form. Questi metodi saranno generati dal nostro gestore di configurazione
personalizzato, in una nuova classe "worker".

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      // ...

      protected function getEnhancerCode($fields)
      {
        $code = array();
        foreach ($fields as $field => $config)
        {
          $code[] = sprintf('if (isset($fields[%s]))', var_export($field, true));
          $code[] = '{';

          if (isset($config['label']))
          {
            $code[] = sprintf('  $fields[%s]->getWidget()->setLabel(%s);',
              var_export($config['label'], true));
          }

          if (isset($config['attributes']))
          {
            $code[] = '  $fields[%s]->getWidget()->setAttributes(array_merge(';
            $code[] = '    $fields[%s]->getWidget()->getAttributes(),';
            $code[] = '    '.var_export($config['attributes'], true);
            $code[] = '  ));';
          }

          if (isset($config['errors']))
          {
            $code[] = sprintf('  if ($error = $fields[%s]->getError())',
              var_export($field, true));
            $code[] = '  {';
            $code[] = '    $error->getValidator()->setMessages(array_merge(';
            $code[] = '      $error->getValidator()->getMessages(),';
            $code[] = '      '.var_export($config['errors'], true);
            $code[] = '    ));';
            $code[] = '  }';
          }

          $code[] = '}';
        }

        return implode(PHP_EOL.'    ', $code);
      }
    }

Notare che l'array di configurazione viene controllato per determinare certe
chiavi, quando il codice viene generato, piuttosto che in fase di runtime.
Questo fornirà un piccolo aumento delle prestazioni.

>**TIP**
>Come regola generale, la logica che controlla le condizioni della configurazione
>deve essere eseguita nel gestore di configurazione, non nel codice generato.
>La logica che controlla le condizioni di esecuzione, come la natura dell'oggetto
>form "migliorato", deve essere eseguita in fase di runtime.

Il codice generato viene inserito all'interno di una definizione di classe,
che viene quindi salvata nella cartella della cache.

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $forms = self::getConfiguration($configFiles);

        $code = array();
        $code[] = '<?php';
        $code[] = '// auto-generated by '.__CLASS__;
        $code[] = '// date: '.date('Y/m/d H:is');
        $code[] = 'class sfFormYamlEnhancementsWorker';
        $code[] = '{';
        $code[] = '  static public $enhancable = '.var_export(array_keys($forms), true).';';

        foreach ($forms as $class => $fields)
        {
          $code[] = '  static public function enhance'.$class.'(sfFormFieldSchema $fields)';
          $code[] = '  {';
          $code[] = '    '.$this->getEnhancerCode($fields);
          $code[] = '  }';
        }

        $code[] = '}';

        return implode(PHP_EOL, $code);
      }

      // ...
    }

La classe `sfFormYamlEnhancer` rinvia alla classe worker generata, per
gestire la manipolazione di oggetti form, ma ora deve tener conto della ricorsione
attraverso l'inclusione dei form. Per fare questo, bisogna processare lo schema
con i campi dei form (che possono essere iterati ricorsivamente) e l'oggetto
form (che comprende i moduli inclusi) in parallelo.

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      protected function doEnhance(sfFormFieldSchema $fieldSchema, sfForm $form)
      {
        if ($enhancer = $this->getEnhancer(get_class($form)))
        {
          call_user_func($enhancer, $fieldSchema);
        }

        foreach ($form->getEmbeddedForms() as $name => $form)
        {
          if (isset($fieldSchema[$name]))
          {
            $this->doEnhance($fieldSchema[$name], $form);
          }
        }
      }

      public function getEnhancer($class)
      {
        if (in_array($class, sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.$class);
        }
        else if ($overlap = array_intersect(class_parents($class),
          sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.current($overlap));
        }
      }
    }

>**NOTE**
>I campi degli oggetti di form inclusi, non devono essere modificati dopo
>che sono stati incorporati. I form inclusi sono memorizzati per l'elaborazione
>nel form genitore, ma non hanno alcun effetto su come il form padre viene reso.

Con la realizzazione del supporto ai form incorporati, i test ora dovrebbero
passare.  Eseguire lo script per scoprirlo:

![I test passano](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_ok.png)

Cosa abbiamo fatto?
-------------------

Lanciamo alcuni benchmark per essere sicuri di non avere perso tempo. Per
rendere interessanti i risultati, aggiungere alcune classi form a `forms.yml`
utilizzando un ciclo PHP.

    [yml]
    # <?php for ($i = 0; $i < 100; $i++): ?> #
    Form<?php echo $i ?>: ~
    # <?php endfor; ?> #

Creare tutte le classi, eseguendo il seguente frammento di codice:

    [php]
    mkdir($dir = sfConfig::get('sf_lib_dir').'/form/test_fixtures');
    for ($i = 0; $i < 100; $i++)
    {
      file_put_contents($dir.'/Form'.$i.'.class.php',
        '<?php class Form'.$i.' extends BaseForm { }');
    }

Ora si è pronti per lanciare alcuni benchmarks. Per i risultati che trovate cui
sotto, è stato più volte eseguito il seguente comando [Apache](http://httpd.apache.org/docs/2.0/programs/ab.html)
su un MacBook, finché non si è avuta una deviazione standard inferiore a 2ms.

    $ ab -t 60 -n 20 http://localhost/config_cache/web/index.php

Si inizia con un benchmark di base per lanciare l'applicazione senza il
miglioramento collegato al tutto. Commentare `sfFormYamlEnhancer` in
`frontendConfiguration` e lanciare il benchmark:

    Tempo di connessione (ms)
                    min  media[+/-sd] mediana   max
    Connessione:      0    0   0.0       0       0
    Elaborazione:    62   63   1.5      63      69
    Attesa:          62   63   1.5      63      69
    Totale:          62   63   1.5      63      69

Ora incollare la prima versione di `sfFormYamlEnhancer::enhance()`, che chiama
nella classe direttamente `sfYaml` e lancia il benchmark:

    Tempo di connessione (ms)
                    min  media[+/-sd] mediana   max
    Connessione:      0    0   0.0       0       0
    Elaborazione:    87   88   1.6      88      93
    Attesa:          87   88   1.6      88      93
    Totale:          87   88   1.7      88      94

Si può vedere che si è aggiunta una media di 25ms per ogni richiesta, con un
incremento di quasi il 40%. Ora, annullare la modifica che si è appena fatta a
`->enhance()`, in modo che il gestore di configurazione personalizzato sia
ripristinato e lanciare nuovamente il benchmark:

    Tempo di connessione (ms)
                    min  media[+/-sd] mediana   max
    Connessione:      0    0   0.0       0       0
    Elaborazione:    62   63   1.6      63      70
    Attesa:          62   63   1.6      63      70
    Totale:          62   64   1.6      63      70

Come si può vedere, con la creazione di un gestore di configurazione
personalizzato, è stato ridotto il tempo di elaborazione riportandolo al
valore di riferimento.

Solo per divertirsi: creare un plugin
-------------------------------------

Ora che è stato realizzato questo sistema per migliorare gli oggetti dei form
con un semplice file YAML di configurazione, perché non inserirlo in un plugin
e condividerlo con la comunità? Questa può sembrare una intimidazione per coloro
che in passato non hanno pubblicato plugin; vediamo di scacciare ogni possibile
paura!

Questo plugin avrà la seguente struttura di file:

    sfFormYamlEnhancementsPlugin/
      config/
        sfFormYamlEnhancementsPluginConfiguration.class.php
      lib/
        config/
          sfFormYamlEnhancementsConfigHander.class.php
        form/
          sfFormYamlEnhancer.class.php
      test/
        unit/
          form/
            sfFormYamlEnhancerTest.php

C'è bisogno di fare alcune modifiche per facilitare il processo di installazione
del plugin. La creazione e il collegamento dell'oggetto "migliorato" può essere
incapsulato nella classe di configurazione del plugin:

    [php]
    class sfFormYamlEnhancementsPluginConfiguration extends sfPluginConfiguration
    {
      public function initialize()
      {
        if ($this->configuration instanceof sfApplicationConfiguration)
        {
          $enhancer = new sfFormYamlEnhancer($this->configuration->getConfigCache());
          $enhancer->connect($this->dispatcher);
        }
      }
    }

Lo script di test dovrà essere aggiornato per fare riferimento allo script di
avvio del progetto:

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    // ...

In ultimo, abilitare il plugin in `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfFormYamlEnhancementsPlugin');
      }
    }

Se si vogliono eseguire i test dal plugin, collegarli in
`ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function setupPlugins()
      {
        $this->pluginConfigurations['sfFormYamlEnhancementsPlugin']->connectTests();
      }
    }

Ora, quando si chiama qualunque task `test:*`, i test vengono eseguiti dal plugin.

![I test del plugin](http://www.symfony-project.org/images/more-with-symfony/config_cache_plugin_tests.png)

Tutte le classi sono collocate nella nuova cartella del plugin, ma c'è un
problema: lo script di test si basa sui file che si trovano ancora nel
progetto. Questo significa che chiunque possa volere eseguire questi test, non
ne sarà in grado, a meno che non abbiano nel loro progetto gli stessi file.

Per risolvere questo problema, c'è bisogno di isolare il codice nella classe
enhancer che chiama la configurazione della cache, in modo che si possa
sovrascriverla nello script di test e utilizzare al suo posto una fixture
`forms.yml`.

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        $this->loadWorker();
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      public function loadWorker()
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
      }

      // ...
    }

Si può quindi sovrascrivere il metodo `->loadWorker()` nello script di test, per
chiamare direttamente il gestore personalizzato della configurazione. La classe
`CommentForm` deve essere spostata nello script di test e il file `forms.yml`
spostato nella cartella `test/fixtures` del plugin.

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    $t = new lime_test(6);

    class sfFormYamlEnhancerTest extends sfFormYamlEnhancer
    {
      public function loadWorker()
      {
        if (!class_exists('sfFormYamlEnhancementsWorker', false))
        {
          $configHandler = new sfFormYamlEnhancementsConfigHander();
          $code = $configHandler->execute(array(dirname(__FILE__).'/../../fixtures/forms.yml'));

          $file = tempnam(sys_get_temp_dir(), 'sfFormYamlEnhancementsWorker');
          file_put_contents($file, $code);

          require $file;
        }
      }
    }

    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
      }
    }

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());

    $enhancer = new sfFormYamlEnhancerTest($configuration->getConfigCache());

    // ...

Impacchettare il plugin è facile avendo installato `sfTaskExtraPlugin`. Basta
lanciare il task `plugin:package` e dopo un paio di richieste interattive verrà
creato un package.

    $ php symfony plugin:package sfFormYamlEnhancementsPlugin

>**NOTE**
>Il codice presente in questo articolo è stato pubblicato come plugin ed è
>disponibile per il download dal sito con i plugin di symfony:
>
>    http://symfony-project.org/plugins/sfFormYamlEnhancementsPlugin
>
>Questo plugin include quello che abbiamo visto qua e molto altro, incluso
>il supporto per i file `widgets.yml` e `validators.yml`, così come
>l'integrazione con il task `i18n:extract` per internazionalizzare facilmente
>i form.

Considerazioni finali
---------------------

Come si può vedere dal benchmark fatto in questo capitolo, la configurazione
della cache di symfony permette di utilizzare la semplicità dei file di
configurazione YAML, senza avere in pratica alcun impatto sulle prestazioni.
