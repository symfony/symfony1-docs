Sfruttare la potenza della linea di comando
===========================================

*di Geoffrey Bachelet*

Symfony 1.1 ha introdotto un moderno, potente e flessibile sistema da linea di
comando in sostituzione del vecchio sistema dei task basato su pake. Di versione
in versione il sistema dei task è stato migliorato fino ad arrivare a ciò che è
oggi.

Molti sviluppatori web non vedono il valore aggiunto dei task. Spesso, questi 
sviluppatori, non comprendono la potenza della linea di comando. In questo
capitolo approfondiremo l'argomento task, dalle basi all'uso più avanzato, 
osservando come possono aiutare il lavoro di tutti i giorni e come ottenere
il massimo dai task.

Task al primo sguardo
---------------------

Un task è una porzione di codice eseguito dalla linea di comando utilizzando lo
script `symfony` nella directory radice del progetto. Avrete di sicuro già 
incontrato i task attraverso il ben famoso `cache:clear` (conosciuto anche come
`cc`) lanciandolo nel terminale:

    $ php symfony cc

Symfony mette a disposizione un insieme di task preconfezionati e di uso generico
per molti scopi. Utilizzando lo script `symfony` senza nessun parametro o opzione
si ottiene la lista dei task disponibili:

    $ php symfony

L'output avrà un aspetto simile al seguente (contenuto troncato):

    Usage:
      symfony [options] task_name [arguments]

    Options:
      --help        -H  Display this help message.
      --quiet       -q  Do not log messages to standard output.
      --trace       -t  Turn on invoke/execute tracing, enable full backtrace.
      --version     -V  Display the program version.
      --color           Forces ANSI color output.
      --xml             To output help as XML

    Available tasks:
      :help                        Displays help for a task (h)
      :list                        Lists tasks
    app
      :routes                      Displays current routes for an application
    cache
      :clear                       Clears the cache (cc, clear-cache)

Forse avrete già notato che i task sono raggruppati. Gruppi di task sono chiamati 
namespace e i nomi dei task sono generalmente composti da un namespace e da un
nome del task (eccezion fatta per i task `help` e `list` che non hanno un 
namespace). Questo schema di denominazione permette una semplice categorizzazione 
dei task ed è consigliato scegliere oculatamente i namespace per ognuno dei propri
task.

Scrivere un proprio task
------------------------

Iniziare a scrivere task con symfony è questione di pochi minuti. Tutto ciò che 
c'è da fare è creare il task, dargli un nome, aggiungerci della logica, e voilà
siete pronti ad eseguire il vostro primo task personalizzato. Creiamo un 
semplicissimo task *Hello, World!*, per esempio in `lib/task/sayHelloTask.class.php`:

    [php]
    class sayHelloTask extends sfBaseTask
    {
      public function configure()
      {
        $this->namespace = 'say';
        $this->name      = 'hello';
      }

      public function execute($arguments = array(), $options = array())
      {
        echo 'Hello, World!';
      }
    }

Ora va eseguito con il seguente comando:

    $ php symfony say:hello

Questo task si limita a mostrare *Hello, World!*, ma è solo l'inizio! I
task non sono propriamente pensati per fornire dell'output direttamente attraverso
i comandi `echo` o `print`. Estendendo `sfBaseTask` è possibile utilizzare 
una moltitudine di utili metodi, incluso il metodo `log()` che fa esattamente 
quello che vogliamo, output di contenuti:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log('Hello, World!');
    }

Dato che l'esecuzione di un singolo task può tramutarsi in diversi task che 
producono contenuto in output, è preferibile utilizzare il metodo `logSection()`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, World!');
    }

Ora, probabilmente avrete già notato i due parametri passati al metodo `execute()`, 
`$arguments` e `$options`. Questi si occupano di contenere tutti i parametri e
le opzioni passate al task al momento dell'esecuzione. Ci occuperemo di parametri
e di opzioni in modo approfondito in seguito. Per adesso aggiungiamo un po' di
interattività al task permettendo all'utente di specificare chi vogliamo salutare:

    [php]
    public function configure()
    {
      $this->addArgument('who', sfCommandArgument::OPTIONAL, 'Who to say hello to?', 'World');
    }

    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, '.$arguments['who'].'!');
    }

A questo punto il seguente comando:

    $ php symfony say:hello Geoffrey

Dovrebbe produrre il seguente risultato:

    >> say       Hello, Geoffrey!

Wow, è stato facile.

A proposito, potreste voler inserire ancora un po' di metadati nel task come per
esempio ciò di cui si occupa. Per fare questo basta impostare le proprietà 
`briefDescription` e `description`:

    [php]
    public function configure()
    {
      $this->namespace           = 'say';
      $this->name                = 'hello';
      $this->briefDescription    = 'Simple hello world';

      $this->detailedDescription = <<<EOF
    The [say:hello|INFO] task is an implementation of the classical
    Hello World example using symfony's task system.

      [./symfony say:hello|INFO]

    Use this task to greet yourself, or somebody else using
    the [--who|COMMENT] argument.
    EOF;

      $this->addArgument('who', sfCommandArgument::OPTIONAL, 'Who to say hello to?', 'World');
    }

Come potete vedere è possibile utilizzare un semplice insieme di markup per
decorare la descrizione inserita. Per verificare la visualizzazione utilizzare
il sistema di aiuto per i task di symfony:

    $ php symfony help say:hello

Il sistema delle opzioni
------------------------

Le opzioni nei task di symfony sono organizzate in due insiemi distinti, opzioni
e parametri.

### Opzioni

Le opzioni sono quelle passate utilizzando il trattino. È possibile aggiungerle
alla linea di comando in qualsiasi ordine. Possono avere o meno un valore, nel
secondo caso si comportano come booleani. Molto spesso le opzioni hanno entrambe
i formati lungo e corto. Il formato lungo è solitamente invocato utilizzando due
trattini mente quello corto ne richiede uno solo.

Esempio di opzioni comuni sono il selettore dell'help (`--help` or `-h`), il 
selettore della verbosità (`--quiet` or `-q`) o quello della versione (`--version` 
or `-V`).

>**NOTE**
>Le opzioni sono definite con una classe `sfCommandOption` e memorizzare in una
>classe `sfCommandOptionSet`.

### Argomenti

I parametri sono porzioni di dati che vengono aggiunte alla riga di comando.
Devono essere specificati nello stesso ordine con il quale sono stati definiti, 
vanno racchiusi tra apici se si ha la necessità di includere uno spazio in essi
(oppure va eseguito l'escape degli spazi). I parametri possono essere obbligatori
o opzionali, nel qual caso sarà necessario specificare un valore di default
nella definizione del parametro.

>**NOTE**
>Ovviamente i parametri sono definiti nella classe `sfCommandArgument` e
>memorizzati nella classe `sfCommandArgumentSet`.

### Impostazioni di default

Ogni task di symfony ha un insieme di opzioni e parametri di default:

  * `--help` (-`H`): Displays this help message.
  * `--quiet` (`-q`): Do not log messages to standard output.
  * `--trace` `(-t`): Turns on invoke/execute tracing, enable full backtrace.
  * `--version` (`-V`): Displays the program version.
  * `--color`: Forces ANSI color output.

### Opzioni speciali

Il sistema dei task di symfony comprende due opzioni molto speciali quali 
`application` e `env`. L'opzione `application` è necessaria quando di ha bisogno
di accedere ad un'istanza di `sfApplicationConfiguration` oltre che al solo
`sfProjectConfiguration`. Questo è il caso, per esempio, di quando si vogliono
generare URLs dato che il routing è generalmente associato ad una specifica 
applicazione.

Quando un'opzione `application` viene passata ad un task, symfony la riconosce
automaticamente e crea l'oggetto `sfApplicationConfiguration` corrispondente 
invece che l'oggetto di default `sfProjectConfiguration`. Ricordate che è 
possibile impostare un valore di default per questa opzione, salvandovi quindi
dal dover passare manualmente l'applicazione corrispondente ogni volta che 
il task viene eseguito.

L'opzione `env` controlla, ovviamente, l'ambiente nel quale il task viene eseguito.
Quando non viene specificato l'ambiente, `test` viene utilizzato come default. 
Come per l'opzione `application`, è possibile impostare un valore di default per 
l'opzione `env` che verrà utilizzato automaticamente da symfony.

Dato che `application` e `env` non sono incluse nell'insieme delle opzioni di 
default è necessario aggiungerle a mano nel task:

    [php]
    public function configure()
    {
      $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      ));
    }

In questo esempio l'applicazione `frontend` verrà usata automaticamente e, a 
meno di non specificare un ambiente, il task verrà eseguito nell'ambiente `dev`.

Accedere al database
--------------------

Accedere al database direttamente da un task di symfony è solo questione di 
istanziare `sfDatabaseManager`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
    }

È anche possibile accedere all'oggetto di connessione dell'ORM in modo diretto:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase()->getConnection();
    }

Ma cosa succede quando si hanno diverse connessione definite del file 
`databases.yml`? È possibile, per esempio, aggiungere un'opzione `connection`
al task:

    [php]
    public function configure()
    {
      $this->addOption('connection', sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine');
    }

    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase(isset($options['connection']) ? $options['connection'] : null)->getConnection();
    }

Come al solito è possibile impostare un valore di default per questa opzione.

Voilà! Ora è possibile manipolare i propri modelli proprio come se si stesse
lavorando all'interno di un'applicazione symfony.

>**NOTE**
>Fate molta attenzione quando eseguite processi batch utilizzando gli oggetti
>del vostro ORM preferito. Sia Propel che Doctrine soffrono di un ben noto bug
>di PHP legato alle referenze cicliche e al garbage collector che si manifestano
>con una perdita di memoria. Questo è stato parzialmente risolto in PHP 5.3.

Inviare email
-------------

Uno degli utilizzi più comuni dei task è l'invio di email. Prima di symfony 1.3 
inviare email non era un processo così immediato. Ma i tempi sono cambiati: 
symfony ora propone la completa integraziene di [Swift Mailer](http://swiftmailer.org/),
una libreria PHP molto completa per l'invio delle email. Usiamola quindi!

Il sistema dei task di symfony espone l'oggetto mailer attraverso il metodo
`sfCommandApplicationTask::getMailer()`. In questo modo è possibile ottenere
l'accesso al mailer e inviare email facilmente:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $mailer  = $this->getMailer();
      $mailer->composeAndSend($from, $recipient, $subject, $messageBody);
    }

>**NOTE**
>Visto che la configurazione del mailer viene letta dalla configurazione 
>dell'applicazione, il task deve accettare un'opzione per l'applicazione per
>essere in grado di utilizzare il mailer.

-

>**NOTE**
>Se utilizzate la strategia dello spool, le email vengono inviate solamente quando
>viene eseguito il task `project:send-emails`.

In molti casi non vorrete avere il contenuto del messaggio in una variabile 
`$messageBody` solo in attesa di essere inviato, vorrete generarlo in qualche 
modo. Non esiste un modo preferibile in symfony per generare il contenuto delle
proprie email, tuttavia abbiamo un paio di suggerimenti da seguire per avere
una vita più facile:

### Delegare la generazione del contenuto

Per esempio, creare un metodo protetto per il task che restituisce il contenuto
per la mail che si sta per inviare:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->getMailer()->composeAndsend($from, $recipient, $subject, $this->getMessageBody());
    }

    protected function getMessageBody()
    {
      return 'Hello, World';
    }

### Utilizzare il decorator plugin di Swift Mailer

Swift Mailer mette a disposizione un plugin conosciuto come 
[`Decorator`](http://swiftmailer.org/docs/decorator-plugin), che non è altro che
un semplicissimo, quanto efficiente, motore di template, che può prendere delle coppie
di valori recipiente-rimpiazzo da utilizzare e applicarli a tutte le email da inviare.

Leggere la [documentazione di Swift Mailer](http://swiftmailer.org/docs/) per
ulteriori informazioni.

### Utilizzare una libreria di template esterna

Integrare una libreria di template di terze parti è semplice. Per esempio si
potrebbe utilizzare il nuovissimo templating component rilasciato come parte
del progetto Symfony Components. Basta copiare il codice del component da qualche
parte nel progetto (`lib/vendor/templating/` potrebbe essere un buon posto) ed
aggiungere il seguente codice nel task:

    [php]
    protected function getMessageBody($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine()
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_dir').'/templates/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

### Ottenere il meglio da entrambe le soluzioni

C'è ancora qualcosa che si può fare. Il plugin `Decorator` di Swift Mailer è
molto utile visto che può gestire le sostituzioni in base al destinatario utilizzato.
Questo significa che basta definire un insieme di sostituzioni per ogni destinatario
e Swift Mailer di occuperà di rimpiazzare gli elementi con il giusto valore,
in base al destinatario dell'email da inviare. Vediamo come poterlo integrare
coi template:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $message = Swift_Message::newInstance();

      // recupera una lista di utenti
      foreach($users as $user)
      {
        $replacements[$user->getEmail()] = array(
          '{username}'      => $user->getEmail(),
          '{specific_data}' => $user->getSomeUserSpecificData(),
        );

        $message->addTo($user->getEmail());
      }

      $this->registerDecorator($replacements);

      $message
        ->setSubject('User specific data for {username}!')
        ->setBody($this->getMessageBody('user_specific_data'));

      $this->getMailer()->send($message);
    }

    protected function registerDecorator($replacements)
    {
      $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
    }

    protected function getMessageBody($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine($replacements = array())
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

Con `apps/frontend/templates/emails/user_specific_data.php` che contiene il
seguente codice:

    Hi {username}!

    We just wanted to let you know your specific data:

    {specific_data}

Ecco fatto! Ora avete a disposizione un completo sistema di template per generare
il contenuto delle vostre email.

Generazione di URL
------------------

La scrittura di email solitamente richiede di generare degli URL basati sulla
configurazione delle rotte. Per fortuna, la generazione degli URL è stata
semplificata in symfony 1.3, a partire dal quale si può accedere direttamente
al routing dell'applicazione corrente da dentro un task, usando il metodo
`sfCommandApplicationTask::getRouting()`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $routing = $this->getRouting();
    }

>**NOTE**
>Poiché le rotte dipendono dall'applicazione, occorre accertarsi che l'applicazione
>disponga di una configurazione, altrimenti non sarà possibile generare URL
>usando le rotte.
>
>Si veda la sezione *Opzioni Speciali* per imparare come ottenere automaticamente
>una configurazione per l'applicazione in un task.

Ora che abbiamo un'istanza del routing, è abbastanza semplice generare un URL,
usando il metodo `generate()`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('default', array('module' => 'foo', 'action' => 'bar'));
    }

Il primo parametro è il nome della rotta e il secondo è un array di parametri
per la rotta. A questo punto, abbiamo generato un URL relativo, che molto
probabilmente non fa al caso nostro. Sfortunatamente, la generazione di URL
assoluti in un task non funziona, perché non si dispone di un oggetto
`sfWebRequest` a cui appoggiarsi per recuperare l'host HTTP.

Un semplice modo per risolvere il problema consiste nell'impostare l'host HTTP
nel file di configurazione `factories.yml`:

    [yml]
    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true
          context:
            host: example.org

Vedete l'impostazione `context_host`? È quella che sarà usata dal routing per
generare un URL assoluto:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('my_route', array(), true);
    }

Accedere al Sistema I18N
------------------------

Non tutti i factory sono così facilmente accessibili come il mailer e il
routing. Se occorre accedere ad uno di essi, non è molto difficile
istanziarli. Ad esempio, se si desidera internazionalizzare i task,
si avrà bisogno di accedere al sottosistema i18n di symfony. Lo si può
fare facilmente usando `sfFactoryConfigHandler`:

    [php]
    protected function getI18N($culture = 'en')
    {
      if (!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class  = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);
      }

      $this->i18n->setCulture($culture);

      return $this->i18n;
    }

Vediamo che sta succedendo. Innanzitutto, stiamo usando una semplice tecnica
di cache, per evitare di ricostruire il componente i18n ad ogni chiamata.
Poi, usando `sfFactoryConfigHandler`, recuperiamo la configurazione del
componente per poterlo istanziare. Infine impostiamo la configurazione della
cultura. Il task ora ha accesso all'internazionalizzazione:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log($this->getI18N('fr')->__('some translated text!'));
    }

Ovviamente, passare ogni volta la cultura non è molto maneggevole,
specialmente se non si ha bisogno di cambiare cultura molto spesso nel
task. Vedremo come sistemare la cosa nella prossima sezione.

Rifattorizzare i Task
---------------------

Poiché l'invio di email (compresa la generazione del loro contenuto) e la
generazione degli URL sono due compiti molto comuni, potrebbe essere
una buona idea quella di creare un task base che fornisca tali
funzionalità per gli altri task. Lo si può fare facilmente. Basta
creare una classe base nel progetto, ad esempio
`lib/task/sfBaseEmailTask.class.php`.

    [php]
    class sfBaseEmailTask extends sfBaseTask
    {
      protected function registerDecorator($replacements)
      {
        $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
      }

      protected function getMessageBody($template, $vars = array())
      {
        $engine = $this->getTemplateEngine();
        return $engine->render($template, $vars);
      }

      protected function getTemplateEngine($replacements = array())
      {
        if (is_null($this->templateEngine))
        {
          $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/templates/emails/%s.php');
          $this->templateEngine = new sfTemplateEngine($loader);
        }

        return $this->templateEngine;
      }
    }

Già che ci siamo, automatizziamo la configurazione delle opzioni del task.
Aggiungiamo i seguenti metodi alla classe `sfBaseEmailTask`:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    }

    protected function generateUrl($route, $params = array())
    {
      return $this->getRouting()->generate($route, $params, true);
    }

Usiamo il metodo `configure()` per aggiungere opzioni comuni a tutti i task.
Sfortunatamente, ogni classe che estende `sfBaseEmailTask` dovrà richiamare
`parent::configure` nel proprio metodo `configure()`, ma è una piccola
noia per ottenere un grosso vantaggio.

Ora, rifattorizziamo il codice che accede all'I18N, visto nella precedente
sezione:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
      $this->addOption('culture', null, sfCommandOption::PARAMETER_REQUIRED, 'The culture', 'en');
    }

    protected function getI18N()
    {
      if (!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class  = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);

        $this->i18n->setCulture($this->commandManager->getOptionValue('culture'));
      }

      return $this->i18n;
    }

    protected function changeCulture($culture)
    {
      $this->getI18N()->setCulture($culture);
    }

    protected function process(sfCommandManager $commandManager, $options)
    {
      parent::process($commandManager, $options);
      $this->commandManager = $commandManager;
    }

Dobbiamo risolvere un problema: non è possibile accedere ai parametri
e alle opzioni fuori da `execute()`. Per risolverlo, ridefiniamo il
metodo `process()` per passare il gestore di opzioni alla classe.
Il gestore di opzioni, come dice il nome stesso, gestisce i parametri
e le opzioni per il task corrente. Ad esempio, si può accedere ai
valori delle opzioni usando il metodo `getOptionValue()`.

Eseguire un Task dentro un Task
-------------------------------

Un modo alternativo per rifattorizzare i task è quello di includere un
task dentro un altro task. Si può fare molto facilmente tramite i
metodi `sfCommandApplicationTask::createTask()` e
`sfCommandApplicationTask::runTask()`.

Il metodo `createTask()` creerà per noi un'istanza di un task. Basta
passargli il nome di un task, come se fossimo in linea di comando, e
restituirà un'istanza del task scelto, pronta per essere eseguita:

    [php]
    $task = $this->createTask('cache:clear');
    $task->run();

Ma sicomme siamo pigri, `runTask` farà tutto al posto nostro:

    [php]
    $this->runTask('cache:clear');

Ovviamente, si possono passare parametri e opzioni (in questo ordine):

    [php]
    $this->runTask('plugin:install', array('sfGuardPlugin'), array('install_deps' => true));

Includere task è utile per comporre potenti task, a partire da task più semplici.
Ad esempio, si possono combinare diversi task in un task `project:clean` che
si potrebbe eseguire dopo ogni deployment:

    [php]
    $tasks = array(
      'cache:clear',
      'project:permissions',
      'log:rotate',
      'plugin:publish-assets',
      'doctrine:build-model',
      'doctrine:build-forms',
      'doctrine:build-filters',
      'project:optimize',
      'project:enable',
    );

    foreach($tasks as $task)
    {
      $this->run($task);
    }

Manipolare il Filesystem
------------------------

Symfony possiede una semplice astrazione per il filesystem (`sfFilesystem`),
che consente di eseguire semplici operazioni su file e cartelle. È
accessibile da un task tramite `$this->getFilesystem()`. Tale
astrazione espone i seguenti metodi:

* `sfFilesystem::copy()`, per copiare un file
* `sfFilesystem::mkdirs()`, crea ricorsivamente cartelle
* `sfFilesystem::touch()`, per creare un file
* `sfFilesystem::remove()`, per cancellare un file o una cartella
* `sfFilesystem::chmod()`, per cambiare i permessi di un file o di una cartella
* `sfFilesystem::rename()`, per rinominare un file o una cartella
* `sfFilesystem::symlink()`, per creare un collegamento ad una cartella
* `sfFilesystem::relativeSymlink()`, per creare un collegamento relativo ad una cartella
* `sfFilesystem::mirror()`, per fare il mirror di un intero albero di cartelle
* `sfFilesystem::execute()`, per eseguire un comando arbitrario

Espone anche un metodo molto utile, che analizzeremo nella prossima sezione:
`replaceTokens()`.

Usare degli Scheletri per generare i File
-----------------------------------------

Un altro uso comune dei task è quello di generare file. I file possono essere
generati facilmente, usando gli scheletri e il summenzionato metodo
`sfFilesystem::replaceTokens()`. Come il nome suggerisce, questo metodo sostituisce
dei token dentro un insieme di file. Quindi, si passa un array di file, una lista
di token ed esso sostituisce ogni occorrenza di ogni token col valore assegnato,
in ogni file dell'array.

Per capire meglio la sua utilità, riscriveremo in parte un task esistente:
`generate:module`. A scopo di chiarezza e brevità, vedremo solo la parte
`execute` del task, ipotizzando che sia stato configurato propriamente
con tutte le opzioni necessarie. Inoltre, salteremo la validazione.

Prima di iniziare a scrivere il task, dobbiamo creare uno scheletro
per le cartelle e i file che stiamo per creare e salvarlo da qualche parte,
come `data/skeleton/`:

    data/skeleton/
      module/
        actions/
          actions.class.php
        templates/

Lo scheletro `actions.class.php` potrebbe essere qualcosa come questa:

    [php]
    class %moduleName%Actions extends %baseActionsClass%
    {
    }

Il primo passo del nostro task sarà copiare l'albero dei file nella posizione
adeguata:

    [php]
    $moduleDir = sfConfig::get('sf_app_module_dir').$options['module'];
    $finder    = sfFinder::type('any');
    $this->getFilesystem()->mirror(sfConfig::get('sf_data_dir').'/skeleton/module', $moduleDir, $finder);

Ora sostituiamo i tokens in `actions.class.php`:

    [php]
    $tokens = array(
      'moduleName'       => $options['module'],
      'baseActionsClass' => $options['base-class'],
    );

    $finder = sfFinder::type('file');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '%', '%', $tokens);

Ecco fatto, generiamo il nostro nuovo modulo, usando la sostituzione dei token
per personalizzarlo.

>**NOTE**
>Il task predefinito `generate:module` in realtà cerca in `data/skeleton/` degli
>scheletri alternativi, da usare al posto di quelli standard, quindi fate
>attenzione!

Usare l'Opzione dry-run
-----------------------

Spesso si ha bisogno di poter vedere un'anteprima del risultato di un task,
prima di eseguirlo effettivamente. Ecco un paio di trucchi per farlo.

Innanzitutto, si dovrebbe usare un nome standard, come `dry-run`.
Tutti lo riconoscono per quello che è. Fino a symfony 1.3, `sfCommandApplication`
*aggiungeva* un'opzione predefinita `dry-run`, ma ora va aggiunta a mano
(eventualmente in una classe base, come dimostrato sopra):

    [php]
    $this->addOption(new sfCommandOption('dry-run', null, sfCommandOption::PARAMETER_NONE, 'Executes a dry run');

Quindi si potrà richiamare il task in questo modo:

    ./symfony my:task --dry-run

L'opzione `dry-run` indica che il task non dovrebbe eseguire cambiamenti.

*Non dovrebbe eseguire cambiamenti*, ricordatelo, sono parole chiave. In
modalità `dry-run`, il task deve lasciare l'ambiente esattamente com'era
prima, incluso (ma non limitato a):

* Il database: non inserire, aggiornare o cancellare righe dalle tabelle. Si
  può usare una transazione per raggiungere lo scopo.
* Il filesystem: non creare, modificare o cancellare file dal filesystem.
* Invio di email: non inviare email né inviarle a indirizzi di debug.

Ecco un semplice esempio di uso dell'opzione `dry-run`:

    [php]
    $connection->beginTransaction();

    // modifica il database

    if ($options['dry-run'])
    {
      $connection->rollBack();
    }
    else
    {
      $connection->commit();
    }

Scrivere Test Unitari
---------------------

Poiché i task possono raggiungere diversi scopi, testarli non è facile.
Quindi, non c'è un unico modo per testare i task, ma ci sono alcuni
principi da seguire, che posso aiutare a rendere i task più testabili.

Innanzitutto, pensare ai task come a un controllore. Ricordate la regola
sui controllori? *Controllori snelli, modelli grassi*. Quindi, spostare
tutta la logica di business dentro i modelli, in modo da poter testare
i modelli invece del task, che è molto più facile.

Una volta che non si può spostare ulteriore logica nei modelli, dividere
il metodo `execute()` in pezzi di codice facilmente testabili, ognuno dei
quali si appoggi su metodi propri facilmente accessibili (si legga: pubblici).
Dividere il codice ha diversi benefici:

  1. rende l'`execute` del task più leggibile
  2. rende il task più testabile
  3. rende il task più estensibile

Siate creativi, non esitate a costruire un piccolo ambiente specifico per
le vostre esigenze di test. E se non trovate un modo per testare quel
task stupendo che avete appena scritto, ci sono due possibilità: o
l'avete scritto male, o dovreste chiedere l'opinione di qualcun altro.
Inoltre, si può sempre analizzare il codice altrui, per vedere come
gli altri testano le cose (i task di symfony, ad esempio, sono molto
ben testati, anche i generatori).

Metodi di Aiuto: Log
--------------------

Il sistema dei task di symfony prova fortemente a rendere la vita dello
sviluppatore più facile, fornendo dei metodi di aiuto per operazioni
comuni, come scrivere nei log e interagire con l'utente.

Si possono facilmente mandare in output dei messaggi, usando la famiglia
dei metodi `log`:

  * `log`, accetta un array di messaggi
  * `logSection`, un po' più elaborato, formatta il messaggio con un prefisso
    (primo parametro) e un tipo di messaggio (quarto parametro). Quando si
    inserisce un messaggio troppo lungo, come il percorso di un file, 
    `logSection` solitamente lo accorcia, il che potrebbe risultare
    fastidioso. Usare il terzo parametro per specificare una dimensione
    massima per il proprio messaggio.
  * `logBlock` è lo stile di messaggi usato per le eccezioni. Anche qui, si
    può passare uno stile di formattazione

I formati di log disponibili sono `ERROR`, `INFO`, `COMMENT` e `QUESTION`.
Usateli pure per vedere in che modo si comportano.

Esempio di utilizzo:


    [php]
    $this->logSection('file+', $aVeryLongFileName, $this->strlen($aVeryLongFileName));

    $this->logBlock('Congratulations! You ran the task successfuly!', 'INFO');

Metodi di Aiuto: Interazione con l'Utente
-----------------------------------------

Altri tre metodi d'aiuto facilitano l'interazione con l'utente:

  * `ask()` scrive semplicemente una domanda e restituisce la risposta dell'utente

  * `askConfirmation()` chiede una conferma all'utente, consentendo `y` (sì) e
    `n` (no) come risposta

  * `askAndValidate()`, molto utile, scrive una domanda e valida la risposta
    dell'utente tramite un `sfValidator`, passato come secondo parametro. Il
    terzo parametro è un array di opzioni in cui si può passare un valore
    predefinito (`value`), un numero massimo di tentativi (`attempts`) e uno
    stile di formattazione (`style`).

Ad esempio, si può chiedere all'utente il suo indirizzo email e validarlo al volo:

    [php]
    $email = $this->askAndValidate('What is your email address?', new sfValidatorEmail());

Bonus: Usare i Task con Crontab
-------------------------------

Quasi tutti i sistemi UNIX e GNU/Linux consentono la pianificazione di task,
tramite un meccanismo noto come *cron*. Il *cron* controlla un file di
configurazione (chiamato *crontab*), che contiene comandi da eseguire a
determinati orari. I task di symfony possono essere facilmente integrati
nel crontab e il task `project:send-emails` è un perfetto candidato come
esempio:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails

Questa configurazione dice a *cron* di eseguire `project:send-emails` ogni giorno
alle 3 del mattino e di inviare ogni possibile output (log, errori, ecc.)
all'indirizzo *you@example.org*.

>**NOTE**
>Per maggiori informazioni sulla configurazione di crontab, si può scrivere
>`man 5 crontab` in un terminale.

Si può, e in realtà si dovrebbe, passare parametri e opzioni:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails --env=prod --application=frontend

>**NOTE**
>Si dovrebbe sostituire `/usr/bin/php` con il percorso del binario PHP CLI.
>Se non di dispone di questa informazione, si provi `which php` su Linux o
>`whereis php` sulla maggior parte degli altri UNIX.

Bonus: Usare STDIN
------------------------

Essendo i task eseguiti in un ambiente a linea di comando, si può accedere
allo standard input stream (STDIN). La linea di comando UNIX consente alle
applicazioni di interagire tra di loro in molti modi, uno dei quali è il
*pipe*, identificato dal carattere *|*. Il *pipe* consente di passare
l'output di un'applicazione (noto come *STDOUT*) allo standard input di
un'altra applicazione (noto come *STDIN*). Entrambi sono accessibili
dai task tramite le costanti speciali di PHP `STDIN` e `STDOUT`.
C'è anche un terzo flusso standard, *STDERR*, accessibile tramite
`STDERR`, che si occupa dei messaggi di errore di un'applicazione.

Dunque, cosa possiamo fare esattamente con lo standard input? Be',
immaginate di avere un'applicazione che gira sul server, che vorrebbe
comunicare con un'applicazione symfony. Si potrebbe ovviamente farla
comunicare tramite HTTP, ma un modo più efficiente sarebbe quello
di concatenare tramite pipe il suo output con un task symfony.
Supponiamo che l'applicazione possa inviare dati strutturati (ad
esempio un array PHP serializzato) che descrive degli oggetti di
dominio che si vuole includere nel database. Si potrebbe scrivere
il seguente task:

    [php]
    while ($content = trim(fgets(STDIN)))
    {
      if ($data = unserialize($content) !== false)
      {
        $object = new Object();
        $object->fromArray($data);
        $object->save();
      }
    }

E lo si potrebbe usare in questo modo:

    /usr/bin/data_provider | ./symfony data:import

dove `data_provider` è l'applicazione che fornisce nuovi oggetti
di dominio e `data:import` il task appena scritto.

Considerazioni Finali
---------------------

Quello che si può ottenere coi task è limitato solo dalla propria
immaginazione. Il sistema dei task di symfony è abbastanza potente
e flessibile da poter fare quasi ogni cosa vi possa venire in mente.
Aggiungete la potenza del terminale UNIX e amerete veramente i task.
