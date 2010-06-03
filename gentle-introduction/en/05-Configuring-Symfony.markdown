Capitolo 5 - Configurare Symfony
===============================

Per essere semplice da usare, symfony definisce alcune convenzioni, che devono soddisfare i requisiti più comuni nello sviluppo web senza bisogno di modifiche. Comunque, utilizzando un insieme di semplici e potenti file di configurazione, è possibile personalizzare il modo in cui il framework e la tua applicazione interagiscono fra loro. Con questi file di configurazione, potrai anche aggiungere parametri speciali alla tua applicazione.

Questo capitolo spiega come funziona il sistema di configurazione:

  * La configurazione di symfony è memorizzata in file scritti in YAML, anche se è sempre possibile scegliere un altro formato.
  * Nella struttura di cartelle del progetto, i file di configurazione si trovano ai livelli progetto, applicazione e modulo.
  * Puoi definire diversi insiemi di impostazioni; in symfony, un insieme di configurazioni è chiamato ambiente.
  * I valori definiti nei file di configurazione sono disponibili al codice PHP della tua applicazione.
  * Inoltre, symfony permette l'utilizzo di codice PHP e altri trucchi all'interno dei file di configurazione YAML, per rendere il sistema di configurazione ancora più flessibile.

Il Sistema di Configurazione
------------------------

A parte lo scopo, la maggior parte delle applicazioni web condivide un insieme di caratteristiche comuni. Ad esempio, qualche sezione puo' avere accesso ristretto ad un certo insieme di utenti, oppure le pagine possono essere decorate da un layout, o ancora la possibilità di avere le form già riempite dopo una validazione fallita. Un framework definisce una struttura per simulare queste caratteristiche, e gli sviluppatori possono ulteriormente modificarle cambiando i file di configurazione. Questa strategia fa risparmiare molto tempo durante lo sviluppo, dato che molti cambiamenti non necessitano di alcuna linea di codice, anche se ce n'è molto dietro. Questo sistema è anche molto efficiente, perché assicura che queste informazioni siano reperibili sempre in un punto unico e facile da trovare.

Questo approccio ha però due seri svantaggi:

  * Gli sviluppatori di solito finiscono per scrivere complessi file XML senza fine.
  * In un'architettura PHP, ogni richiesta necessita di più tempo per essere eseguita.

Tenendo conto di questi svantaggi, symfony utilizza file di configurazione solo dove sono veramente necessari. L'ambizione del sistema di configurazione di symfony è di essere:

  * Potente: ogni aspetto che possa essere gestito tramite file di configurazione lo è veramente.
  * Semplice: diversi parametri di configurazione non sono mostrati in una normale applicazione, in quanto raramente necessitano di essere modificati.
  * Facile: gli sviluppatori troveranno facile leggere, creare e modificare file di configurazione.
  * Personalizzabile: il linguaggio di configurazione di default è YAML, ma puo' essere INI, XML, o qualsiasi altro formato lo sviluppatore preferisca.
  * Veloce: i file di configurazione non vengono processati dall'applicazione ma dal sistema di configurazione, che li compila velocemente in parti di codice PHP sul server.

### Sintassi YAML e convenzioni di Symfony

Per la propria configurazione, symfony utilizza il formato YAML, invece dei più tradizionali INI e XML. YAML mostra la struttura tramite indentazione ed è veloce da scrivere. I vantaggi e le regole di base sono già state mostrate nel Capitolo 1. Comunque, devi tenere a mente qualche convenzione quando vuoi scrivere file di YAML. Questa sezione introduce diverse convenzioni tra le più importanti. Per approfondimenti visita il sito web di YAML [website](http://www.yaml.org/).

Prima di tutto non sono ammessi caratteri di tabulazione in YAML; occorre usare spazi bianchi. I parser YAML non capiscono le tabulazioni, per cui utilizza spazi bianchi per l'indentazione (la convenzione in symfony è di due spazi bianchi), come mostrato nel Listato 5-1.

Listato 5-1 - YAML vieta l'utilizzo delle tabulazioni

    # Mai usare tabs
    all:
    -> mail:
    -> -> webmaster:  webmaster@example.com

    # Usare solo spazi
    all:
      mail:
        webmaster: webmaster@example.com


Se i tuoi parametri sono stringhe che cominciano o finiscono con spazi vuoti, contengono caratteri speciali (come # o ,), o sono parole chiave come "true, false" (intese come una stringa), devi incapsulare il valore tra singoli apici, come mostrato nel Listato 5-2.

Listato 5-2 - Stringhe non standard dovrebbero essere incapsulate tra singoli apici

    error1: This field is compulsory
    error2: '  This field is compulsory  '
    error3: 'Don''t leave this field blank' # i singoli apici devono essere raddoppiati
    error4: 'Enter a # symbol to define an extension number'
    i18n:   'false' # se togliamo gli apici, viene restituito un valore booleano false


Puoi definire stringhe lunghe in più righe, ed anche stringhe con più di una linea, con gli header speciali di stringa (> e |) più una indentazione addizionale. Il Listato 5-3 illustra questa convenzione.

Listato 5-3 - Definizione di stringhe lunghe e su più righe

    # Folded style, introdotto da >
    # Ogni line break è convertito in uno spazio
    # Questo rende YAML più leggibile.
    accomplishment: >
      Mark set a major league
      home run record in 1998.

    # Literal style, introdotto da |
    # Vengono mantenuti i line break
    # L'indentazione non appare nella stringa risultante
    stats: |
      65 Home Runs
      0.278 Batting Average

Per definire un valore come array, racchiudi gli elementi tra parentesi quadre, oppure usa la sintassi estesa con i trattini, come mostrato nel Listato 5-4.

Listato 5-4 - Sintassi di array in YAML

    # Sintassi abbreviata per gli array
    players: [ Mark McGwire, Sammy Sosa, Ken Griffey ]

    # Sintassi espansa per gli array
    players:
      - Mark McGwire
      - Sammy Sosa
      - Ken Griffey

Per definire un valore come array associativo, o hash, racchiudi gli elementi tra parentesi graffe e inserisci sempre uno spazio tra la chiave ed il valore nella coppia 'key: value', e separa gli elementi della lista con delle virgole. Puoi anche utilizzare la sintassi estesa, aggiungendo indentazione e ritorno a capo per ogni chiave, come mostrato nel Listato 5-5.

Listato 5-5 - Array associativi in YAML

    # Sintassi errata: mancano gli spazi dopo i duepunti e la virgola
    mail: {webmaster:webmaster@example.com,contact:contact@example.com}

    # Sintassi abbreviata corretta per gli array associativi
    mail: { webmaster: webmaster@example.com, contact: contact@example.com }

    # Sintassi estesa per gli array associativi
    mail:
      webmaster: webmaster@example.com
      contact: contact@example.com


Per assegnare un valore booleano, utilizza i valori 'false' e 'true'
senza apici.

Listato 5-6 - Sintassi YAML per valori booleani

    true_value: true
    false_value: false

Non esitare ad aggiungere commenti (che devono cominciare con il cancelletto #) e spazi aggiuntivi, per rendere ancora più leggibili tuoi file YAML, come mostrato nel Listato 5-7.

Listing 5-7 - Sintassi dei commenti e allineamento dei valori in YAML

    # Questa è una linea di commento
    mail:
      webmaster: webmaster@example.com
      contact:   contact@example.com
      admin:     admin@example.com # gli spazi aggiuntivi permettono un migliore allineamento dei valori

In qualche file di configurazione di symfony, ti capiterà di trovare delle linee che cominciano con un cancelletto (per cui ignorate dal parser YAML) e che assomigliano a normali linee di impostazioni. Questa è una convenzione di symfony: la configurazione di default, ereditata da altri file YAML che si trovano nel core, è ripetuta in linee commentate nella tua applicazione per pura informazione. Se vuoi cambiare uno di tali parametri, devi innanzitutto decommentare la linea, come mostrato nel Listato 5-8.

Listato 5-8 - La configurazione di default è mostrata commentata

    # La cache è false per default
    settings:
    # cache: false

    # Se vuoi cambiare questa impostazioni, decommenta la linea
    settings:
      cache: true

Qualche volta symfony raggruppa le definizioni dei parametri in categorie. Tutte le impostazioni relative ad una data categoria appaiono indentati sotto il suo header. Strutturare lunghe liste di coppie chiave: valore raggruppandole in categorie aumenta la leggibilità della configurazione. Gli header di categoria cominciano con un punto (.). Il Listato 5-9 mostra un esempio di categorie.

Listato 5-9 - Gli header di categoria sembrano chiavi, ma cominciano con un un punto

    all:
        .general:
          tax:    19.6

          mail:
            webmaster: webmaster@example.com

In questo esempio, mail è una chiave e general è solo un header di categoria. Tutto funziona come se l'header non esistesse, come mostrato nel Listato 5-10. Il parametro tax è effettivamente un figlio diretto della chiave all. Tuttavia l'uso delle categorie aiuta symfony a trattare con gli array che sono sotto la chiave all.

Listing 5-10 - Gli header di categoria esistono solo per una questione di leggibilità, e sono in effetti ignorati

    all:
      tax: 19.6

    mail:
      webmaster: webmaster@example.com

>**SIDEBAR**
>E se non ti piace YAML...
>
>YAML è solo un'interfaccia per definire impostazioni utilizzate da PHP, per cui le configurazioni YAML finiscono per essere trasformate in PHP. Dopo aver navigato la tua applicazione, controllane la configurazione in cache (ad esempio in cache/frontend/dev/config/). Vedrai file PHP corrispondenti alle configurazioni YAML. Imparerai di più sulla cache di configurazione più avanti in questo capitolo.
>
>La buona notizia è che se non vuoi usare YAML, puoi fare la stessa cosa a mano in PHP o con altri formati (come XML o INI). Nel corso di questo libro, incontrerai modi alternativi per definire configurazioni senza YAML, ed imparerai anche come sostituire il gestore di configurazioni di symfony (nel Capitolo 19). Se li usi largamente, questi trucchi ti permetteranno di bypassare i file di configurazione o definire il tuo personale formato di configurazione.

### Aiuto, un file YAML ha ucciso la mia applicazione!

I file YAML sono parsati in array o hash PHP, quindi i valori sono usati in varie parti dell'applicazione per modificare il comportamento delle viste, del controller o del modello. Molte volte, quando c'è un errore in un file YAML, esso non viene riconosciuto fino a che il valore non è effettivamente necessario. Ancora più spesso, l'eccezione che viene generata non si riferisce chiaramente al file YAML.

Se la tua applicazione smette improvvisamente di funzionare dopo un cambio di configurazione, devi controllare di non aver fatto qualcuno dei più comuni errori di disattenzione con YAML:

  * Ti manca uno spazio tra una chiave ed il suo valore:

        key1:value1 # Manca uno spazio bianco dopo :

  * Le chiavi in una sequenza non sono indentate nella stessa maniera:

        all:
          key1: value1
           key2: value2 # L'indentazione è diversa da quella degli altri  membri della sequenza
	  key3: value3

  * C'è un carattere riservato in una chiave o un valore, senza delimitatori di stringa:

        message: tell him: go way   # :, [, ], { and } sono riservate in YAML
        message: 'tell him: go way' # sintassi corretta

  * Stai modificando una linea commentata:

        # key: value # Questa linea è ignorata perché comincia con #

  * Imposti dei valori con la stessa chiave allo stesso livello:

        key1: value1
        key2: value2
        key1: value3 # key1 è definita due volte, il valore è l'ultimo inserito

  * Pensi che un valore sia un tipo speciale, mentre resta una stringa fino a che non sarà convertita:

        income: 12,345 # Ancora una stringa, fino a che non sarà convertita

Riepilogo sui file di configurazione
-----------------------------------

La configurazione è suddivisa in file, per oggetto. Questi file contengono definizioni di parametri, o impostazioni. Alcuni di tali parametri possono essere sovrascritti a diversi livelli (progetto, applicazione e modulo); altri sono specifici di un certo livello. I prossimi capitoli prenderanno in esame le configurazioni relativamente alle loro finalità principali, mentre il Capitolo 19 esaminerà le configurazioni avanzate.

### Configurazione di progetto

Ci sono per default pochi file di configurazione per progetto. Di seguito quelli che si trovano nella cartella myproject/config/:

  * `ProjectConfiguration.class.php`: Questo è assolutamente il primo file incluso da ogni richiesta o comando. Contiene i percorsi ai file del framework, e può essere cambiato per usare un'installazione diversa. Vedi il Capitolo 19 per usi avanzati di questo file.
  * `databases.yml`: Qui è dove definisci l'accesso e la connessione al database (host, login, password, nome del database, e così via). Imparerai di più su questo nel Capitolo 8. Può essere sovrascritto a livello applicazione.
  * `properties.ini`: Questo file gestisce parametri utilizzati a linea di comando, inclusi il nome del progetto e le impostazioni di connessione a server remoti. Vedi il Capitolo 16 per un sommario delle caratteristiche di utilizzo di questo file.
  * `rsync_exclude.txt`: Questo file specifica quali cartelle e file devono essere esclusi dalla sincronizzazione tra server. È discusso nel Capitolo 16.
  * `schema.yml`: Si tratta del file di configurazione per l'accesso ai dati usato da Propel e Doctrine (il livello ORM di symfony). Esso è usato per far funzionare le librerie dell'ORM con le classi di symfony e i dati del tuo progetto. schema.yml contiene una rappresentazione del modello relazionale del progetto.

Questi file sono usati per lo più da componenti esterni o dalla linea di comando, o devono essere processati prima che il framework carichi il programma di parsing YAML. Ecco perché alcuni di essi non usano il formato YAML.

### Configurazione dell'applicazione

La maggior parte della configurazione è occupata dall'applicazione. È definita nel front controller (nella cartella web/) per la configurazione principale, in file YAML nella cartella config/ dell'applicazione, in i18n/ per l'internazionalizzazione, ed infine nei file del framework per una invisibile (ma sempre utile) configurazione addizionale dell'applicazione.

#### Configurazione del front controller

La prima configurazione dell'applicazione in assoluto si trova nel front controller; si tratta del primo script eseguito da una richiesta. Dai un'occhiata al codice di web/index.php mostrato nel Listato 5-11.

Listato 5-11 - Il front controller di default 

    [php]
    <?php
    require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    sfContext::createInstance($configuration)->dispatch();

After defining the name of the application (`frontend`), the environment (`prod`), and the debug mode (`false`), the application configuration is called before creating a context and dispatching. So a few useful methods are available in the application configuration class:

  * `$configuration->getRootDir()`: Project root directory (normally, should remain at its default value, unless you change the file structure).
  * `$configuration->getApplication()`: Application name in the project. Necessary to compute file paths.
  * `$configuration->getEnvironment()`: Environment name (`prod`, `dev`, `test`, or any other project-specific environment that you define). Will determine which configuration settings are to be used. Environments are explained later in this chapter.
  * `$configuration->isDebug()`: Activation of the debug mode (see Chapter 16 for details).

If you want to change one of these values, you probably need an additional front controller. The next chapter will tell you more about front controllers and how to create a new one.

#### Main Application Configuration

The main application configuration is stored in files located in the `myproject/apps/frontend/config/` directory:

  * `app.yml`: This file should contain the application-specific configuration; that is, global variables defining business or applicative logic specific to an application, which don't need to be stored in a database. Tax rates, shipping fares, and e-mail addresses are often stored in this file. It is empty by default.
  * `frontendConfiguration.class.php`: This class bootstraps the application, which means that it does all the very basic initializations to allow the application to start. This is where you can customize your directory structure or add application-specific constants (Chapter 19 provides more details). It inherits from the `sfApplicationConfiguration` class.
  * `factories.yml`: Symfony defines its own class to handle the view, the request, the response, the session, and so on. If you want to use your own classes instead, this is where you can specify them. Chapter 17 provides more information.
  * `filters.yml`: Filters are portions of code executed for every request. This file is where you define which filters are to be processed, and it can be overridden for each module. Chapter 6 discusses filters in more detail.
  * `routing.yml`: The routing rules, which allow transforming unreadable and unbookmarkable URLs into "smart" and explicit ones, are stored in this file. For new applications, a few default rules exist. Chapter 9 is all about links and routing.
  * `settings.yml`: The main settings of a symfony application are defined in this file. This is where you specify if your application has internationalization, its default language, the request timeout and whether caching is turned on. With a one-line change in this file, you can shut down the application so you can perform maintenance or upgrade one of its components. The common settings and their use are described in Chapter 19.
  * `view.yml`: The structure of the default view (name of the layout, default style sheets and JavaScript files to be included, default content-type, and so on) is set in this file. Chapter 7 will tell you more about this file. These settings can be overridden for each module.

>**TIP**
>All the symfony configuration files are described in great details in the [symfony reference book](http://www.symfony-project.org/reference/1_4/en/).

#### Internationalization Configuration

Internationalized applications can display pages in several languages. This requires specific configuration. There are two configuration places for internationalization:

  * The `factories.yml` of the application `config/` directory: This file defines the i18n factory and general translation options, such as the default culture for the translation, whether the translations come from files or a database, and their format.
  * Translation files in the application `i18n/` directory: These are basically dictionaries, giving a translation for each of the phrases used in the application templates so that the pages show translated text when the user switches language.

Note that the activation of the i18n features is set in the `settings.yml` file. You will find more information about these features in Chapter 13.

#### Additional Application Configuration

A second set of configuration files is in the symfony installation directory (in `sfConfig::get('sf_symfony_lib_dir')/config/config/`) and doesn't appear in the configuration directory of your applications. The settings defined there are defaults that seldom need to be modified, or that are global to all projects. However, if you need to modify them, just create an empty file with the same name in your `myproject/apps/frontend/config/` directory, and override the settings you want to change. The settings defined in an application always have precedence over the ones defined in the framework. The following are the configuration files in the symfony installation `config/` directory:

  * `autoload.yml`: This file contains the settings of the autoloading feature. This feature exempts you from requiring custom classes in your code if they are located in specific directories. It is described in detail in Chapter 19.
  * `core_compile.yml`: These are lists of classes to be included to start an application. These classes are actually concatenated into an optimized PHP file without comments, which will accelerate the execution by minimizing the file access operations (one file is loaded instead of more than forty for each request). This is especially useful if you don't use a PHP accelerator. Optimization techniques are described in Chapter 18.
  * `config_handlers.yml`: This is where you can add or modify the handlers used to process each configuration file. Chapter 19 provides more details.

### Module Configuration

By default, a module has no specific configuration. But, if required, you can override some application-level settings for a given module. For instance, you might do this to include a specific JavaScript file for all actions of a module. You can also choose to add new parameters restricted to a specific module to preserve encapsulation.

As you may have guessed, module configuration files must be located in a `myproject/apps/frontend/modules/mymodule/config/` directory. These files are as follows:

  * `generator.yml`: For modules generated according to a database table (scaffoldings and administrations), this file defines how the interface displays rows and fields, and which interactions are proposed to the user (filters, sorting, buttons, and so on). Chapter 14 will tell you more about it.
  * `module.yml`: This file contains custom parameters specific to a module and action configuration. Chapter 6 provides more details.
  * `security.yml`: This file sets access restrictions for actions. This is where you specify that a page can be viewed only by registered users or by a subset of registered users with special permissions. Chapter 6 will tell you more about it.
  * `view.yml`: This file contains configuration for the views of one or all of the actions of a module. It overrides the application `view.yml` and is described in Chapter 7.

Most module configuration files offer the ability to define parameters for all the views or all the actions of a module, or for a subset of them.

>**SIDEBAR**
>Too many files?
>
>You might be overwhelmed by the number of configuration files present in the application. But please keep the following in mind:
>
>Most of the time, you don't need to change the configuration, since the default conventions match the most common requirements. Each configuration file is related to a particular feature, and the next chapters will detail their use one by one. When you focus on a single file, you can see clearly what it does and how it is organized. For professional web development, the default configuration is often not completely adapted. The configuration files allow for an easy modification of the symfony mechanisms without code. Imagine the amount of PHP code necessary to achieve the same amount of control. If all the configuration were located in one file, not only would the file be completely unreadable, but you could not redefine configuration at several levels (see the "Configuration Cascade" section later in this chapter).
>
>The configuration system is one of the great strengths of symfony, because it makes symfony usable for almost every kind of web application, and not only for the ones for which the framework was originally designed.

Environments
------------

During the course of application development, you will probably need to keep several sets of configuration in parallel. For instance, you will need to have the connection settings for your tests database available during development, and the ones for your real data available for production. To answer the need of concurrent configurations, symfony offers different environments.

### What Is an Environment?

An application can run in various environments. The different environments share the same PHP code (apart from the front controller), but can have completely different configurations. For each application, symfony provides three default environments: production (`prod`), test (`test`), and development (`dev`). You're also free to add as many custom environments as you wish.

So basically, environments and configuration are synonyms. For instance, a test environment will log alerts and errors, while a `prod` environment will only log errors. Cache acceleration is often deactivated in the `dev` environment, but activated in the `test` and `prod` environments. The `dev` and `test` environments may need test data, stored in a database distinct from the one used in the production environment. So the database configuration will be different between the two environments. All environments can live together on the same machine, although a production server generally contains only the `prod` environment.

In the `dev` environment, the logging and debugging settings are all enabled, since maintenance is more important than performance. On the contrary, the `prod` environment has settings optimized for performance by default, so the production configuration turns off many features. A good rule of thumb is to navigate in the development environment until you are satisfied with the feature you are working on, and then switch to the production environment to check its speed.

The `test` environment differs from the `dev` and `prod` environment in other ways. You interact with this environment solely through the command line for the purpose of functional testing and batch scripting. Consequently, the `test` environment is close to the production one, but it is not accessed through a web browser. It simulates the use of cookies and other HTTP specific components.

To change the environment in which you're browsing your application, just change the front controller. Until now, you have seen only the development environment, since the URLs used in the example called the development front controller:

    http://localhost/frontend_dev.php/mymodule/index

However, if you want to see how the application reacts in production, call the production front controller instead:

    http://localhost/index.php/mymodule/index

If your web server has `mod_rewrite` enabled, you can even use the custom symfony rewriting rules, written in `web/.htaccess`. They define the production front controller as the default execution script and allow for URLs like this:

    http://localhost/mymodule/index

>**SIDEBAR**
>Environments and Servers
>
>Don't mix up the notions of environment and server. In symfony, different environments are different configurations, and correspond to a front controller (the script that executes the request). Different servers correspond to different domain names in the URL.
>
>     http://localhost/frontend_dev.php/mymodule/index
>            _________ _______________
>             server     environment
>
>Usually, developers work on applications in a development server, disconnected from the Internet and where all the server and PHP configuration can be changed at will. When the time comes for releasing the application to production, the application files are transferred to the production server and made accessible to the end users.
>
>This means that many environments are available on each server. For instance, you can run in the production environment even on your development server. However, most of the time, only the production environment should be accessible in the production server, to avoid public visibility of server configuration and security risks. To prevent accidental exposure of the non-production controllers on the production system, symfony adds a basic IP check to these front controllers, which will allow access only from localhost. If you want to have them accessible you can remove that, but think about the risk of having this accessible by anyone, as malicious users could guess the default `frontend_dev.php` and get access to a lot of sensitive information.
>
>To add a new environment, you don't need to create a directory or to use the symfony CLI. Simply create a new front controller and change the environment name definition in it. This environment inherits all the default configuration plus the settings that are common to all environments. The next chapter will show you how to do this.

### Configuration Cascade

The same setting can be defined more than once, in different places. For instance, you may want to set the mime-type of your pages to `text/html` for all of the application, except for the pages of an `rss` module, which will need a `text/xml` mime-type. Symfony gives you the ability to write the first setting in `frontend/config/view.yml` and the second in `frontend/modules/rss/config/view.yml`. The configuration system knows that a setting defined at the module level must override a setting defined at the application level.

In fact, there are several configuration levels in symfony:

  * Granularity levels:
    * The default configuration located in the framework
    * The global configuration for the whole project (in `myproject/config/`)
    * The local configuration for an application of the project (in `myproject/apps/frontend/config/`)
    * The local configuration restricted to a module (in `myproject/apps/frontend/modules/mymodule/config/`)
  * Environment levels:
    * Specific to one environment
    * For all environments

Of all the properties that can be customized, many are environment-dependent. Consequently, many YAML configuration files are divided by environment, plus a tail section for all environments. The result is that typical symfony configuration looks like Listing 5-12.

Listing 5-12 - The Structure of Symfony Configuration Files

    # Production environment settings
    prod:
      ...

    # Development environment settings
    dev:
      ...

    # Test environment settings
    test:
      ...

    # Custom environment settings
    myenv:
      ...

    # Settings for all environments
    all:
      ...

In addition, the framework itself defines default values in files that are not located in the project tree structure, but in the `sfConfig::get('sf_symfony_lib_dir')/config/config/` directory of your symfony installation. The default configuration is set in these files as shown in Listing 5-13. These settings are inherited by all applications.

Listing 5-13 - The Default Configuration, in `sfConfig::get('sf_symfony_lib_dir')/config/config/settings.yml`

     # Default settings:
     default:
      .actions:
         default_module:         default
         default_action:         index
         ...

These default definitions are repeated in the project, application, and module configuration files as comments, as shown in Listing 5-14, so that you know that some parameters are defined by default and that they can be modified.

Listing 5-14 - The Default Configuration, Repeated for Information, in `frontend/config/settings.yml`

    #all:
    #  .actions:
    #    default_module:         default
    #    default_action:         index
    #    ...

This means that a property can be defined several times, and the actual value results from a definition cascade. A parameter definition in a named environment has precedence over the same parameter definition for all environments, which has precedence over a definition in the default configuration. A parameter definition at the module level has precedence over the same parameter definition at the application level, which has precedence over a definition at the project level. This can be wrapped up in the following priority list:

  1. Module
  2. Application
  3. Project
  4. Specific environment
  5. All environments
  6. Default

The Configuration Cache
-----------------------

Parsing YAML and dealing with the configuration cascade at runtime represent a significant overhead for each request. Symfony has a built-in configuration cache mechanism designed to speed up requests.

The configuration files, whatever their format, are processed by some special classes, called handlers, that transform them into fast-processing PHP code. In the development environment, the handlers check the configuration for changes at each request, to promote interactivity. They parse the recently modified files so that you can see a change in a YAML file immediately. But in the production environment, the processing occurs once during the first request, and then the processed PHP code is stored in the cache for subsequent requests. The performance is guaranteed, since every request in production will just execute some well-optimized PHP code.

For instance, if the `app.yml` file contains this:

    all:                   # Setting for all environments
      mail:
        webmaster:         webmaster@example.com

then the file `config_app.yml.php`, located in the `cache/` folder of your project, will contain this:

    [php]
    <?php

    sfConfig::add(array(
      'app_mail_webmaster' => 'webmaster@example.com',
    ));

As a consequence, most of the time, the YAML files aren't even parsed by the framework, which relies on the configuration cache instead. However, in the development environment, symfony will systematically compare the dates of modification of the YAML files and the cached files, and reprocess only the ones that have changed since the previous request.

This presents a major advantage over many PHP frameworks, where configuration files are compiled at every request, even in production. Unlike Java, PHP doesn't share an execution context between requests. For other PHP frameworks, keeping the flexibility of XML configuration files requires a major performance hit to process all the configuration at every request. This is not the case in symfony. Thanks to the cache system, the overhead caused by configuration is very low.

There is an important consequence of this mechanism. If you change the configuration in the production environment, you need to force the reparsing of all the configuration files for your modification to be taken into account. For that, you just need to clear the cache, either by deleting the content of the `cache/` directory or, more easily, by calling the `cache:clear` task:

    $ php symfony cache:clear

Accessing the Configuration from Code
-------------------------------------

All the configuration files are eventually transformed into PHP, and many of the settings they contain are automatically used by the framework, without further intervention. However, you sometimes need to access some of the settings defined in the configuration files from your code (in actions, templates, custom classes, and so on). The settings defined in `settings.yml`, `app.yml`, and `module.yml` are available through a special class called `sfConfig`.

### The `sfConfig` Class

You can access settings from within the application code through the `sfConfig` class. It is a registry for configuration parameters, with a simple getter class method, accessible from every part of the code:

    [php]
    // Retrieve a setting
    $parameter = sfConfig::get('param_name', $default_value);

Note that you can also define, or override, a setting from within PHP code:

    [php]
    // Define a setting
    sfConfig::set('param_name', $value);

The parameter name is the concatenation of several elements, separated by underscores, in this order:

  * A prefix related to the configuration file name (`sf_` for `settings.yml`, `app_` for `app.yml`, `mod_` for `module.yml`)
  * The parent keys (if defined), in lowercase
  * The name of the key, in lowercase

The environment is not included, since your PHP code will have access only to the values defined for the environment in which it's executed.

For instance, if you need to access the values defined in the `app.yml` file shown in Listing 5-15, you will need the code shown in Listing 5-16.

Listing 5-15 - Sample `app.yml` Configuration

    all:
      .general:
        tax:          19.6
      default_user:
        name:         John Doe
      mail:
        webmaster:    webmaster@example.com
        contact:      contact@example.com
    dev:
      mail:
        webmaster:    dummy@example.com
        contact:      dummy@example.com

Listing 5-16 - Accessing Configuration Settings in PHP in the `dev` Environment

    [php]
    echo sfConfig::get('app_tax');   // Remember that category headers are ignored
     => '19.6'
    echo sfConfig::get('app_default_user_name');
     => 'John Doe'
    echo sfConfig::get('app_mail_webmaster');
     => 'dummy@example.com'
    echo sfConfig::get('app_mail_contact');
     => 'dummy@example.com'

So symfony configuration settings have all the advantages of PHP constants, but without the disadvantages, since the value can be changed.

On that account, the `settings.yml` file, where you can set the framework settings for an application, is the equivalent to a list of `sfConfig::set()` calls. Listing 5-17 is interpreted as shown in Listing 5-18.

Listing 5-17 - Extract of `settings.yml`

    all:
      .settings:
        csrf_secret:       FooBar
        escaping_strategy: true
        escaping_method:   ESC_SPECIALCHARS

Listing 5-18 - What Symfony Does When Parsing `settings.yml`

    [php]
    sfConfig::add(array(
      'sf_csrf_secret' => 'FooBar',
      'sf_escaping_strategy' => true,
      'sf_escaping_method' => 'ESC_SPECIALCHARS',
    ));

Refer to Chapter 19 for the meanings of the settings found in the `settings.yml` file.

### Custom Application Settings and `app.yml`

Most of the settings related to the features of an application should be stored in the `app.yml` file, located in the `myproject/apps/frontend/config/` directory. This file is environment-dependent and empty by default. Put in every setting that you want to be easily changed, and use the `sfConfig` class to access these settings from your code. Listing 5-19 shows an example.

Listing 5-19 - Sample `app.yml` to Define Credit Card Operators Accepted for a Given Site

    all:
      creditcards:
        fake:             false
        visa:             true
        americanexpress:  true

    dev:
      creditcards:
        fake:             true

To know if the `fake` credit cards are accepted in the current environment, get the value of:

    [php]
    sfConfig::get('app_creditcards_fake');

>**NOTE**
>When you should require an PHP array directly beneath the `all` key you need to use a category header, otherwise symfony will make the values separately available as shown above.
>
>     all:
>       .array:
>         creditcards:
>           fake:             false
>           visa:             true
>           americanexpress:  true
>
>     [php]
>     print_r(sfConfig::get('app_creditcards'));
>
>     Array(
>       [fake] => false
>       [visa] => true
>       [americanexpress] => true
>     )

>**TIP**
>Each time you are tempted to define a constant or a setting in one of your scripts, think about if it would be better located in the `app.yml` file. This is a very convenient place to store all application settings.

When your need for custom parameters becomes hard to handle with the `app.yml` syntax, you may need to define a syntax of your own. In that case, you can store the configuration in a new file, interpreted by a new configuration handler. Refer to Chapter 19 for more information about configuration handlers.

Suggerimenti per ottenere di più dai file di configurazione
-----------------------------------------------------------

C'è qualche trucco ancora da imparare prima di scrivere i tuoi file YAML. Ti servirà per evitare duplicazione della configurazione e per gestire i tuoi file YAML.

### Utilizzare costanti nei file YAML

Certi parametri di configurazione si basano sul valore di altri. Per evitare di duplicare i valori, symfony supporta le costanti in YAML. Quando il gestore della configurazione incontra un parametro (accessibile tramite tramite `sfConfig::get()`) in maiuscolo e racchiuso tra %, esso lo sostituisce con il suo valore effettivo. Il Listato 5-20 ne mostra un esempio.

Listato 5-20 - Utilizzo delle costanti in YAML, esempio da `autoload.yml`

    autoload:
      symfony:
        name:           symfony
        path:           %SF_SYMFONY_LIB_DIR%
        recursive:      on
        exclude:        [vendor]

Il parametro path verrà sostituito con il valore di `sfConfig::get('sf_symfony_lib_dir')`. Se vuoi che un file di configurazione si basi su di un altro, devi essere certo che quello su cui si basa venga parsato per primo (controlla i sorgenti di symfony per verificare l'ordine in cui i file di configurazione vengono parsati). `app.yml` è uno degli ultimi file di cui viene fatto il parsing, per cui si può basare su altri.

Tutte le costanti disponibili sono descritte in  [symfony reference book](http://www.symfony-project.org/reference/1_4/en/).

### Utilizzare codice nella configurazione

Potrebbe succedere che la tua configurazione si debba basare su parametri esterni (database od un altro file di configurazione). Per gestire questi casi particolari, viene fatto il parsing dei file di configurazione di symfony da PHP prima che essi vengano passi al parser YAML. Questo significa che i file YAML possono contenere codice PHP, come mostrato nel Listato 5-21.

Listato 5-21 - I file YAML possono contenere codice PHP

    all:
      translation:
        format: <?php echo (sfConfig::get('sf_i18n') === true ? 'xliff' : null)."\n" ?>

Ma fai attenzione al fatto che il parsing di questi file viene eseguito molto presto durante il ciclo di vita di una richiesta, per cui non avrai a tua disposizione i metodi o funzioni di symfony.

Inoltre, siccome il costrutto `echo` non aggiunge un ritorno a capo di default, è necessario aggiungere un "\n" oppure utilizzare l'helper `echoln` per mantenere valido il formato YAML.

    all:
      translation:
        format:  <?php echoln(sfConfig::get('sf_i18n') == true ? 'xliff' : 'none') ?>

>**CAUTION**
>Nell'ambiente di produzione la configurazione è in cache, per cui il parsing (e l'esecuzione) dei file di configurazione avviene esclusivamente dopo che la cache è stata pulita.

### Navigare i tuoi file YAML personali

Ogni qualvolta tu voglia leggere un file YAML direttamente, puoi usare la classe `sfYaml`. Si tratta di un parser YAML che ne trasforma i file in array associativi di PHP. Il Listato 5-22 presenta un file YAML di esempio, mentre il Listato 5-23 mostra come farne il parsing.

Listato 5-22 - File di esempio `test.yml`

    house:
      family:
        name:     Doe
        parents:  [John, Jane]
        children: [Paul, Mark, Simone]
      address:
        number:   34
        street:   Main Street
        city:     Nowheretown
        zipcode:  12345

Listato 5-23 - Utilizzo della classe `sfYaml` per trasformare il file precedente in array associativo

    [php]
    $test = sfYaml::load('/path/to/test.yml');
    print_r($test);

    Array(
      [house] => Array(
        [family] => Array(
          [name] => Doe
          [parents] => Array(
            [0] => John
            [1] => Jane
          )
          [children] => Array(
            [0] => Paul
            [1] => Mark
            [2] => Simone
          )
        )
        [address] => Array(
          [number] => 34
          [street] => Main Street
          [city] => Nowheretown
          [zipcode] => 12345
        )
      )
    )

Riepilogo
---------

Il sistema di configurazione di symfony utilizza il linguaggio YAML per poter essere semplice e leggibile. La capacità di gestire ambienti multipli e di definire insiemi di parametri tramite cascata offre grande versatilità allo sviluppatore. Alcuni dei parametri sono accessibili via codice tramite l'oggetto `sfConfig`, specialmente quelli specifici dell'applicazione memorizzati nel file `app.yml`.

È vero, symfony ha molti file di configurazione, ma questo approccio lo rende molto adattabile. Ricorda che non hai bisogno di annoiarti con essi se non hai bisogno di un alto livello di personalizzazione.