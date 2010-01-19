Tirer Profit de la Ligne de Commande
====================================

*Par Geoffrey Bachelet*

Symfony 1.1 a introduit un système d'exécution de tâches en ligne de commande moderne, puissant et flexible en remplacement de l'ancien système basé sur pake. De version en version, le système de tâches de symfony s'est enrichi afin d'être ce qu'il est aujourd'hui. 

De nombreux développeurs ne perçoivent pas toute la valeur ajoutée des tâches. Bien souvent, ces développeurs ne réalisent pas aussi la puissance de la ligne de commande. Ce chapitre plonge le lecteur dans l'univers des tâches automatiques, de leur usage le plus simple au plus avancé, en démontrant à la fois combien cet outil aide le développeur au quotidien, et de quelle manière en tirer le mieux profit.

Introduction
------------

Une tâche est une partie du code qui s'exécute depuis une interface en ligne de commande en utilisant le script php `symfony` présent à la racine du projet. N'importe quel développeur symfony a déjà utilisé les tâches de symfony en exécutant par exemple la si connue tâche `cache:clear` dans un shell. Cette tâche est aussi bien connue pour sa forme raccourcie `cc`.

    $ php symfony cc

Symfony fournit nativement un large choix de jeux de tâches automatiques pour une grande variété d'usages. Il est possible d'obtenir une liste complète de toutes les tâches disponibles en exécutant le script `symfony` sans lui fournir la moindre option ou argument.

    $ php symfony

La sortie générée dans la console ressemblera à celle ci-après. Le contenu a été tronqué :

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

Le lecteur aura certainement remarqué que les tâches sont groupées. Les groupes de tâches sont appelés des espaces de nom. Les noms des tâches sont généralement composés d'un espace de nom et d'un nom, séparés par un caractère ":". Certaines commandes spéciales telles que `help` et `list` dérogent à la règle car elles sont exemptes d'espace de nom.

Ce schéma de nommage permet de catégoriser facilement les tâches les unes par rapport aux autres, et il s'agira plus tard de choisir un espace de nom pertinent pour chaque nouvelle tâche développée.

Ecrire ses Propres Tâches
-------------------------

Débuter avec l'écriture de tâches symfony n'est qu'une question de minutes. En effet, les seules choses à faire sont de créer une classe de tâche, puis de nommer la tâche avec un nom et un espace de nom, et enfin de lui ajouter un peu de logique. C'est tout ce dont il est nécessaire afin d'exécuter une première commande personnalisée. Le code ci-dessous déclare un exemple de tâche simple *Hello World!* dans un fichier `lib/task/sayHelloTask.class.php`.

    [php]
    // lib/task/sayHelloTask.class.php
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

Il ne reste alors plus qu'à exécuter cette nouvelle tâche à l'aide de la commande suivante.

    $ php symfony say:hello

Le seul but de cette tâche est d'imprimer la chaîne *Hello, World!* dans la console. C'est déjà un bon point de départ ! Bien sûr, les tâches ne servent pas seulement à afficher du contenu dans la console directement avec les fonctions `echo` ou `print`.

Etendre la classe abstraite `sfBaseTask` permet ainsi au développeur de bénéficier d'autres méthodes pratiques, telles que `log()` qui remplit exactement le même besoin, à savoir afficher du contenu.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log('Hello, World!');
    }

Un même appel à une tâche peut conduire à différents contenus en sortie, c'est pourquoi il convient généralement d'utiliser la méthode `logSection()`.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, World!');
    }

A ce stade du chapitre, le lecteur aura probablement remarqué la présence des deux arguments transmis à la méthode `execute()`, `$arguments` et `$options`. Ces deux variables servent à contenir tous les arguments et options passés à la tâche à l'exécution. Les notions d'arguments et d'options seront décrites en détail plus loin. Pour l'instant, il s'agit d'ajouter un peu plus d'interactivité à la tâche en permettant à l'utilisateur de spécifier à qui il souhaite dire bonjour.

    [php]
    public function configure()
    {
      $this->addArgument('who', sfCommandArgument::OPTIONAL, 'Who to say hello to?', 'World');
    }

    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', 'Hello, '.$arguments['who'].'!');
    }

La commande suivante :

    $ php symfony say:hello Geoffrey

Devrait ainsi produire le résultat ci-dessous.

    >> say       Hello, Geoffrey!

C'est facile n'est-ce pas ? Par la même occasion, il convient d'ajouter quelques métadonnées supplémentaires à la commande décrivant par exemple la fonction qu'elle remplit. Pour ce faire, il suffit de définir une valeur pour les deux propriétés `briedDescription` et `description`.

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

Comme le montre cet exemple, il est possible d'utiliser un jeu basique de balises pour décorer la description. La commande symfony `help` permet de contrôler le rendu des métadonnées de la tâche:

    $ php symfony help say:hello

Le Système d'Options
--------------------

Dans les tâches symfony, les options sont organisées en deux ensembles distincts : les options et les arguments.

### Les Options

Les options sont les données transmises à l'aide de traits d'union. Elles peuvent être ajoutées à la ligne de commande dans un ordre totalement arbitraire. 

Les options acceptent ou non une valeur selon qu'elles agissent comme des valeurs booléennes ou non. Très souvent, les options possèdent une forme courte et longue. La forme la plus longue est généralement invoquée en spécifiant deux traits d'union tandis que sa forme courte en requiert seulement un.

Il existe aussi des options récurrentes dans le système de tâches de symfony. C'est le cas par exemple des options d'aide (`--help` ou `-h`), de verbosité (`--quiet` ou `-q`) ou bien de version (`--version` ou `-V`).

>**NOTE**
>Les options sont définies à l'aide de la classe `sfCommandOption` et stockées 
>dans une classe `sfCommandOptionSet`.

### Les Arguments

Les arguments constituent une suite de données ajoutées à la ligne de commande. Ils doivent obligatoirement être spécifiés dans l'ordre dans lequel ils ont été définis, et doivent être entourés de guillemets dans le cas où leur valeur contient un espace (les espaces peuvent être échappés). 

Il existe deux types d'arguments : les obligatoires et les facultatifs. Tous les arguments déclarés comme étant facultatifs doivent accueillir une valeur par défaut.

>**NOTE**
>Les arguments sont évidemment définis à l'aide d'une classe `sfCommandArgument` 
>et stockés dans un objet `sfCommandArgumentSet`.

### Les Arguments et Options par Défaut

Chaque tâche symfony accueille un jeu d'options et d'arguments par défaut :

  * `--help` (-`H`) affiche un message d'aide ;
  * `--quiet` (`-q`) n'affiche aucun message sur la sortie standard ;
  * `--trace` `(-t`) active la trace d'exécution en incluant la pile 
  d'exceptions complète ;
  * `--version` (`-V`) affiche la version du programme ;
  * `--color` force une sortie avec les couleurs ANSI.

### Options Spéciales

Le système de tâches de symfony est capable de reconnaître deux options très spéciales, `application` et `env`.

L'option `application` est nécessaire lorsqu'il s'agit d'accéder à une instance de la classe `sfApplicationConfiguration` plutôt qu'une instance de  `sfProjectConfiguration`. C'est le cas, par exemple, lorsque le développeur souhaite générer des URLs depuis le système de routage. Or, ce dernier est généralement associé à une instance d'une application spécifique.

Lorsqu'une option `application` est passée à la tâche, symfony la détecte automatiquement et crée l'objet `sfApplicationConfiguration` correspondant au lieu de l'objet `sfProjectConfiguration` par défaut. Il est intéressant de noter qu'il est possible de définir un jeu de valeurs par défaut pour cette option, ce qui permet ainsi de s'éviter de passer une application à la main à chaque exécution de la tâche.

L'option `env` contrôle bien évidemment l'environnement dans lequel la tâche est exécutée. Si aucun environnement n'est passé, c'est l'environnement de `test` qui est sélectionné par défaut. De la même manière qu'avec l'option `application`, il est possible de définir une valeur par défaut pour l'option `env` qui sera ensuite utilisée par symfony.

Comme les options `application` et `env` ne sont pas incluses par défaut dans le jeu d'options, elles doivent être ajoutées manuellement dans la classe de la tâche.

    [php]
    public function configure()
    {
      $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      ));
    }

Dans cet exemple, l'application `frontend` sera utilisée automatiquement, et à moins qu'un autre environnement ne soit spécifié, la tâche s'exécutera dans le contexte de l'environnement `dev`.

Accéder à la Base de Données
----------------------------

Avoir accès à la base de données depuis l'intérieur d'une tâche symfony implique de disposer d'une instance de la classe `sfDatabaseManager`.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
    }

L'objet de connexion de l'ORM peut également être accédé directement comme le montre l'exemple de code ci-dessous.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase()->getConnection();
    }

Mais qu'en est-il des projets pour lesquels plusieurs connexions sont définies dans le fichier `databases.yml` ? Il convient par exemple d'ajouter une option `connection` à la tâche pour satisfaire ce besoin.

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

Comme d'habitude, une valeur par défaut peut être définie pour cette option. Tous les objets de modèle de la base de données sont à présent manipulables comme s'ils se trouvaient dans un contexte d'application symfony traditionnelle.

>**NOTE**
>Attention lorsqu'il s'agit de manipuler des objets de modèle d'ORM en masse
>dans les tâches. En effet, les deux ORMs Propel et Doctrine souffrent d'un bug
>très connu de PHP relatif aux références cycliques et au ramasse miettes 
>(garbage collector). Ce bug provoque une fuite de mémoire et affecte toutes les 
>versions strictement inférieures à la 5.3.

Envoyer des Emails
------------------

L'un des usages les plus répandus des tâches concerne l'envoi d'emails. En effet, cette tâche n'était pas si aisée avant symfony 1.3. Heureusement, les choses ont changé et symfony s'accompagne à présent d'une intégration complète de la librairie open-source PHP [Swift Mailer](http://swiftmailer.org/). Pourquoi ne pas en profiter pour la mettre en oeuvre dès à présent.

Le système de tâches de symfony expose un objet d'envoi d'email, le `mailer`, par l'intermédiaire de la méthode `sfCommandApplicationTask::getMailer()`. De cette manière, l'accès à cet objet simplifie l'envoi d'emails.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $mailer  = $this->getMailer();
      $mailer->composeAndSend($from, $recipient, $subject, $messageBody);
    }

>**NOTE**
>Comme la configuration du gestionnaire d'envoi d'emails est lue depuis la 
>configuration de l'application, la tâche doit obligatoirement accueillir une 
>option `application`.

-

>**NOTE**
>Si la stratégie de spool est configurée pour gérer l'envoi des emails dans un 
>projet, alors ces derniers ne seront envoyés qu'après l'exécution de la tâche 
>`project:send-emails`.

Dans la plupart des cas, le contenu du message ne se trouvera pas par magie dans la variable `$messageBody`, et devra donc être généré. Il n'existe pas de solution miracle pour générer le contenu destiné à alimenter des emails. En revanche, les développeurs ont la possibilité de s'appuyer sur quelques astuces en vue de se faciliter la vie.

### Déléguer la Génération du Contenu

La génération d'un contenu d'email peut être déléguée simplement à une méthode protégée de la classe. Cette dernière se charge de générer puis de retourner ce contenu pour l'email à expédier.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->getMailer()->composeAndsend($from, $recipient, $subject, $this->getMessageBody());
    }

    protected function getMessageBody()
    {
      return 'Hello, World';
    }

### Utiliser le Plugin de Décoration de Swift Mailer

Swift Mailer délivre un plugin intitulé [`Decorator`](http://swiftmailer.org/docs/decorator-plugin). Il s'agit d'un moteur de templating basique à la fois simple et efficace qui accepte des chaînes de remplacement clé / valeurs. Ces dernières sont applicables spécifiquement à chaque destinataire quels que soient les emails à expédier.

Le lecteur est inviter à consulter la [documentation officielle de Swift Mailer](http://swiftmailer.org/docs/) pour plus d'informations à ce sujet.

### Utiliser une Librairie Externe de Template

Intégrer une librairie tierce de rendu de template est relativement facile. Il suffit par exemple de s'appuyer sur le nouveau composant de templating intégré au projet Symfony Components.

Pour ce faire, les fichiers sources du composant doivent être téléchargés et  déposés quelque part dans le projet. Le répertoire `lib/vendor/templating` est un excellent candidat pour cela. Enfin, il ne reste plus qu'à ajouter le code suivant à la tâche.

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

### Obtenir le Meilleur des Deux Mondes

Il existe d'autres issues supplémentaires à découvrir pour obtenir un système parfait. Le plugin `Decorator` de Swift Mailer est particulièrement efficace lorsqu'il s'agit de gérer des remplacements par expéditeur.

En effet, ce plugin permet de définir des remplacements pour chacun des destinataires afin que Swift Mailer puisse remplacer les jetons par les bonnes valeurs en s'appuyant sur le destinataire du mail à expédier. Le code ci-dessous explique comment intégrer ce fonctionnement à l'aide du composant de templating.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $message = Swift_Message::newInstance();

      // fetches a list of users
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

Le fichier `apps/frontend/templates/emails/user_specific_data.php` embarque le contenu suivant.

    Hi {username}!

    We just wanted to let you know your specific data:

    {specific_data}

Et c'est tout ! A présent, l'application bénéficie d'un moteur de templates entièrement fonctionnel et dédié à la génération de contenus d'email.

Générer des URLs
----------------

Composer des emails implique généralement de générer des URLs basées sur la configuration du routage. Heureusement, la génération des URLs a été simplifiée dans symfony 1.3 depuis qu'il est possible d'accéder au routage de l'application courante à l'intérieur de la tâche. Cette dernière expose en effet la méthode `sfCommandApplicationTask::getRouting()` prévue à cet effet.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $routing = $this->getRouting();
    }

>**NOTE**
>Dans la mesure où le routage est dépendant de l'application, il convient de 
>s'assurer que l'application dispose d'une configuration d'application 
>disponible ; autrement il sera impossible de générer des URLs en utilisant le 
>routage.
>
>Consultez la section concernant les *Options Spéciales* pour savoir comment 
>définir automatiquement une configuration d'application dans la tâche.

Maintenant que la tâche possède une instance du routage, la génération des URLs s'en voit simplifiée grâce à la méthode `generate()`.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('default', array('module' => 'foo', 'action' => 'bar'));
    }

Le premier argument est le nom de la route et le second un tableau des paramètres pour celle-ci. A ce stade, symfony génère une URL relative, et c'est malheureusement ce dont on n'a pas besoin.

En effet, la génération des URLs absolues depuis une tâche ne fonctionnera pas dans la mesure où il n'existe pas d'objet `sfWebRequest` sur qui compter pour récupérer l'hôte HTTP.

Une manière triviale pour résoudre ce problème consiste à définir l'hôte HTTP en dur dans le fichier de configuration `factories.yml`.

    [yml]
    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true
          context:
            host: example.org

Il s'agit ici de remarquer le paramètre de configuration `context_host`. C'est à partir de celui-ci que l'objet de routage est capable de générer une URL absolue.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('my_route', array(), true);
    }

Accéder au Système d'Internationalisation - I18N
------------------------------------------------

Toutes les factories ne sont pas aussi facilement accessibles que le gestionnaire d'envoi d'email ou le routage. Lorsqu'il s'agit d'utiliser l'une d'entre elles, il n'est finalement pas si compliqué de les instancier à la main directement dans la tâche.

Par exemple, si le développeur souhaite internationaliser les tâches, alors il devra accéder au système d'i18n de symfony. Pour ce faire, il convient de s'aider de la classe `sfFactoryConfigHandler`.

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

Que se passe-t-il ici ? Tout d'abord, le code emploie une technique simple de manipulation du cache dans le but d'éviter la reconstruction de l'objet i18n à chaque appel. Ensuite, l'objet `sfFactoryConfigHandler` se charge de retrouver la configuration du composant afin de l'instancier avant de terminer par la définition de la culture. Désormais, la tâche est capable d'accéder à l'internationalisation comme le montre l'exemple suivant.

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log($this->getI18N('fr')->__('some translated text!'));
    }

Bien sûr, passer à chaque fois la culture à la méthode n'est pas très pratique, surtout quand il s'agit de changer fréquemment la culture dans la tâche. La section suivante explique comment arranger cela.

Remanier les Tâches
-------------------

La génération des contenus d'emails, l'expédition des ces derniers et la génération d'URLs sont des fonctionnalités communes à d'autres besoins. Par conséquent, il s'avère judicieux de créer une classe de base dans laquelle stocker ces fonctionnalités afin de les rendre disponibles aux classes dérivées. L'implémentation technique est quant à elle triviale puisqu'il suffit de créer une nouvelle classe de base dans un fichier `lib/task/sfBaseEmailTask.class.php` du projet et de lui inclure le contenu suivant.

    [php]
    // lib/task/sfBaseEmailTask.class.php
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

Tant qu'à faire, il est aussi pertinent d'automatiser la configuration des options de la tâche en ajoutant ces méthodes à la classe `sfBaseEmailTask`.

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    }

    protected function generateUrl($route, $params = array())
    {
      return $this->getRouting()->generate($route, $params, true);
    }

Ce code utilise la méthode `configure()` pour ajouter des options communes à toutes les tâches dérivées. Malheureusement, toutes les classes dérivées de `sfBaseEmailTask` devront appeler `parent::configure()` dans leur propre méthode `configure()`. Cependant, il s'agit d'une gêne infime au regard de la véritable valeur ajoutée du code.

Il convient à présent de remanier le code d'accès à l'objet I18N de la section précédente.

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

Il existe un nouveau problème à résoudre à ce stade. En effet, il est impossible d'accéder aux valeurs des arguments et des options en dehors du périmètre de la méthode `execute()`.

Pour corriger cela, la méthode `process()` doit être surchargée afin de rattacher le gestionnaire d'options à la classe. Le gestionnaire d'options gère, comme son nom l'indique, les arguments et les options pour la classe courante. Par exemple, les valeurs des options peuvent être accédées à partir de la méthode `getOptionValue()`.

Exécuter une Tâche dans une Autre
---------------------------------

Une manière alternative de factoriser les tâches consiste à embarquer une tâche dans une autre. Cette pratique est d'autant plus facilitée grâce aux méthodes `sfCommandApplicationTask::createTask()` et 
`sfCommandApplicationTask::runTask()`.

La méthode `createTask()` instanciera la classe à la place du développeur en lui passant le nom de la tâche, de la même manière que la ligne de commande. En retour, cette méthode renverra une instance de la classe désirée prête à être exécutée.

    [php]
    $task = $this->createTask('cache:clear');
    $task->run();

Mais finalement, pour les plus paresseux, la tâche `runTask()` se charge de faire tout le travail en une seule passe.

    [php]
    $this->runTask('cache:clear');

Il est bien sûr possible de passer des arguments et des options à la tâche en respectant l'ordre suivant.

    [php]
    $this->runTask('plugin:install', array('sfGuardPlugin'), array('install_deps' => true));

Embarquer des tâches est aussi très utile pour composer des tâches plus puissantes à partir de tâches simples. Par exemple, la combinaison de plusieurs tâches dans une seule `project:clean` permettrait ainsi d'exécuter un ensemble d'opérations juste après le déploiement de l'application sur le serveur de production.

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

Manipuler le Système de Fichiers
--------------------------------

Symfony est livré par défaut avec une abstraction simple du système de fichiers (`sfFilesystem`) qui autorise l'exécution d'opérations simples sur les fichiers et les répertoires. Elle est accessible à l'intérieur de chaque tâche à l'aide de `$this->getFilesystem()`. Cette abstraction expose les méthodes suivantes :

  * `sfFilesystem::copy()` copie un fichier ;
  * `sfFilesystem::mkdirs()` crée une arborescence de répertoires récursivement ;
  * `sfFilesystem::touch()` crée un fichier ;
  * `sfFilesystem::remove()` supprime un fichier ou un répertoire ;
  * `sfFilesystem::chmod()` change les permissions d'un fichier ou d'un répertoire ;
  * `sfFilesystem::rename()` renomme un fichier ou un répertoire ;
  * `sfFilesystem::symlink()` crée un lien symbolique ;
  * `sfFilesystem::relativeSymlink()` crée un lien symbolique relatif à un répertoire ;
  * `sfFilesystem::mirror()` duplique une arborescence complète ;
  * `sfFilesystem::execute()` exécute une ligne de commande shell arbitraire.

Elle expose également une méthode très pratique étudiée dans la prochaine section de ce chapitre : `replaceTokens()`.

Utiliser des Squelettes pour Générer des Fichiers
-------------------------------------------------

Un autre usage courant des tâches consiste à générer des fichiers. La génération de fichiers peut être réalisée à partir de squelettes et la méthode mentionnée juste avant : `sfFilesystem::replaceTokens()`. Comme son nom l'évoque, cette méthode remplace des jetons à l'intérieur d'un jeu de fichiers. Concrètement, il suffit de lui fournir un tableau de fichiers ainsi qu'une liste de jetons afin qu'elle puisse remplacer toutes les occurrences de chaque jeton par sa valeur respective dans chaque fichier.

Pour mieux comprendre tout l'intérêt de cette méthode, le prochain exemple réécrira partiellement une tâche existante : `generate:module`. Par souci de clarté et de sobriété, il s'agit seulement de regarder de plus près une partie de la méthode `execute()` de cette tâche, en supposant qu'elle a été correctement configurée avec les options nécessaires. La validation est quant à elle totalement ignorée.

Avant de démarrer l'écriture de la tâche, il est nécessaire de créer un squelette pour les répertoires et les fichiers que la tâche devra générer, puis de les stocker quelque part. Par exemple dans `data/skeleton/` :

    data/skeleton/
      module/
        actions/
          actions.class.php
        templates/

Le squelette du fichier `actions.class.php` pourrait ressembler à celui ci-dessous :

    [php]
    class %moduleName%Actions extends %baseActionsClass%
    {
    }

La première étape de cette tâche consiste à dupliquer l'arborescence de fichiers au bon endroit.

    [php]
    $moduleDir = sfConfig::get('sf_app_module_dir').$options['module'];
    $finder    = sfFinder::type('any');
    $this->getFilesystem()->mirror(sfConfig::get('sf_data_dir').'/skeleton/module', $moduleDir, $finder);

A présent, il convient de remplacer les jetons du fichier `actions.class.php` :

    [php]
    $tokens = array(
      'moduleName'       => $options['module'],
      'baseActionsClass' => $options['base-class'],
    );

    $finder = sfFinder::type('file');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '%', '%', $tokens);

Et c'est finalement tout ce dont a besoin le corps de la commande pour générer le nouveau module en utilisant le remplacement de jetons.

>**NOTE**
>La tâche native actuelle `generate:module` recherche dans `data/skeleton/` 
>d'autres squelettes alternatifs à utiliser au lieu de ceux par défaut. Donc 
>faites attention !

Utiliser une Option Dry-Run
---------------------------

Il arrive souvent que l'on veuille prévisualiser le résultat final de l'exécution d'une tâche avant même de l'avoir réellement lancée. Les lignes suivantes donnent quelques pistes et astuces sur la manière de procéder.

Tout d'abord, il convient d'utiliser un nom standard, tel que `dry-run`, pour la nouvelle option. Tout le monde sera ainsi capable de reconnaître de quoi il s'agit. Avant symfony 1.3, la classe `sfCommandApplication` *ajoutait* une option `dry-run` par défaut. Cependant, celle-ci est désormais supprimée et doit être ajoutée à la main. Cette option trouve typiquement sa place dans une classe de base, comme cela a été expliqué plus haut.

    [php]
    $this->addOption(new sfCommandOption('dry-run', null, sfCommandOption::PARAMETER_NONE, 'Executes a dry run');

Ensuite, la tâche doit être appelée de la sorte :

    ./symfony my:task --dry-run

L'option `dry-run` indique que la tâche ne devrait faire aucun changement.

*Ne devrait faire aucun changement* sont les mots-clés importants dont il faut se souvenir. Lorsque la tâche est en cours d'exécution en mode `dry-run`, elle doit laisser l'environnement exactement dans le même état initial, y compris (mais ce n'est pas limité) :

  * La base de données : ne pas insérer, ni ne mettre à jour, ni ne supprimer des enregistrements des tables. Une bonne pratique consiste à exécuter ce type d'opérations à l'intérieur d'une transaction qui sera annulée à la fin.
  * Le système de fichiers : ne pas créer, ni ne modifier, ni ne supprimer des fichiers sur le système de fichiers.
  * L'envoi d'emails : ne pas envoyer les emails, ou bien les expédier à une seule et même adresse de maintenance.

Le code suivant illustre le fonctionnement du mode `dry-run` avec une base de données.

    [php]
    $connection->beginTransaction();

    // modify your database

    if ($options['dry-run'])
    {
      $connection->rollBack();
    }
    else
    {
      $connection->commit();
    }

Ecrire des Tests Unitaires
--------------------------

Dans la mesure où les tâches peuvent accomplir une variété de buts différents, les tester unitairement n'est pas une mince affaire non plus. En l'état, il n'existe pas de manière commune et uniforme de tester des tâches, mais il y'a pourtant quelques principes à suivre pour aider à rendre les classes de tests plus facilement testables.

Tout d'abord, il est important de penser les tâches comme des contrôleurs. A cette occasion, il est bon de rappeler la règle d'or à propos des contrôleurs. 

*Des contrôleurs légers et fins mais des modèles lourds et chargés*. C'est tout ! Par conséquent, il convient de déplacer toute la logique métier à l'intérieur des classes de modèle. De cette manière, ce seront les classes de modèle qui devront être testées en priorité sur les classes de tâches, ce qui facilite les choses.

Lorsqu'il n'est plus possible de déplacer davantage de logique métier à l'intérieur des modèles, une bonne pratique consiste à découper la méthode `execute()` en petites unités de code testable, chacune résidant dans sa propre méthode accessible (comprendre publiquement). Découper le code a plusieurs avantages :

  1. La méthode `execute()` de la tâche en devient plus lisible ;
  1. La tâche est quant à elle plus facilement testable ;
  1. La tâche est enfin plus ouverte à d'éventuelles extensions.

Il ne faut pas hésiter à être créatif et à se construire son propre petit environnement de tests pour ses besoins. Et si finalement, dans le cas où il n'existe aucun moyen de tester une tâche fraîchement développée, c'est qu'il existe deux issues différentes.

La première c'est que certainement la tâche a été mal écrite tandis que la seconde consiste à demander son opinion à quelqu'un d'autre. Bien évidemment, il est aussi recommandé de ne pas hésiter à se plonger soi-même dans le code de quelqu'un d'autre afin d'étudier comment les choses ont été testées. C'est le cas par exemple des tâches de symfony et des générateurs qui sont relativement bien testés.

Methodes Helper : Logging
-------------------------

Le système de tâches de symfony essaie autant que possible de rendre la vie du développeur plus facile, en lui fournissant de nombreuses méthodes "helper" pratiques. Ces dernières sont responsables de la réalisation d'opérations communes telles que l'enregistrement de logs et les interactions avec l'utilisateur.

L'une d'elles peut facilement enregistrer des messages vers `STDOUT` en utilisant les méthodes de la famille `log` :

  * `log()` accepte un tableau de messages ;
  * `logSection()` est un peu plus élaborée puisqu'elle permet de formater les messages à l'aide d'un préfixe (premier argument) et un type de message (quatrième argument). Quand il s'agit d'enregistrer quelque chose de trop long, comme un nom de fichier, la méthode `logSection()` tronquera le message, ce qui peut s'avérer contraignant. Le troisième argument permet de définir la longueur maximale autorisée du message ;
  * `logBlock()` est le style de log utilisé pour les exceptions. Une fois de plus il est possible de passer un style de formatage particulier.

Les formats de logs disponibles sont `ERROR`, `INFO`, `COMMENT` et `QUESTION`, et il ne faut surtout pas se priver de les essayer tous afin d'étudier à quoi ils servent.

Exemple d'utilisation :

    [php]
    $this->logSection('file+', $aVeryLongFileName, $this->strlen($aVeryLongFileName));

    $this->logBlock('Congratulations! You ran the task successfuly!', 'INFO');

Méthodes Helper : Interaction avec l'Utilisateur
------------------------------------------------

Il existe trois autres helpers qui sont fournis pour faciliter les interactions avec l'utilisateur :

  * `ask()` imprime simplement une question et retourne l'entrée saisie par l'utilisateur ;

  * `askConfirmation()` est similaire à `ask()` à la différence qu'une confirmation est demandée à l'utilisateur en plus, incluant les réponses `y` (`yes`, oui) et `n` (`no`, non) en guise d'aide à la saisie ;

  * `askAndValidate()` est une méthode très utile qui imprime une question et valide la saisie de l'utilisateur à l'aide d'un validateur `sfValidator` passé en second argument. Le troisième argument est un tableau des options dans lequel peuvent être passées une valeur par défaut (`value`), un nombre maximum d'essais (`attempts`) ainsi qu'un style de formatage (`style`).

Par exemple, il est possible de demander à l'utilisateur de saisir son adresse email et de la valider à la volée.

    [php]
    $email = $this->askAndValidate('What is your email address?', new sfValidatorEmail());

Bonus : Utiliser les Tâches avec une Crontab
--------------------------------------------

La plupart des systèmes UNIX et GNU / Linux supportent la planification de tâches à travers un mécanisme connu sous le nom de *cron*. Le *cron* vérifie un fichier de configuration (un *crontab*) pour les commandes à exécuter à un certain temps ou bien à une certaine période. Les tâches de symfony peuvent facilement être intégrées dans un crontab, et la tâche `project:send-emails` est le candidat parfait pour un exemple de ce type.

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails

Cette configuration indique au *cron* d'exécuter la tâche `project:send-emails` tous les jours à trois heures du matin et d'envoyer toutes les sorties possibles (ici les logs, erreurs, etc) à l'adresse email *you@example.org*.

>**NOTE**
>Pour plus d'informations sur le format du fichier de configuration de la 
>crontab, il suffit de taper `man 5 crontab` dans un terminal de commandes.

Il est aussi possible, et ça devrait être le cas ici, de passer des arguments et des options.

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails --env=prod --application=frontend

>**NOTE**
>La valeur `/usr/bin/php` est à remplacer par le chemin absolu vers le binaire 
>PHP CLI. Si vous ne trouvez pas cette information, vous pouvez essayer la 
>commande `which php` sur les systèmes Linux ou bien `whereis php` sur la 
>plupart des autres systèmes UNIX.

Bonus : Utiliser STDIN
----------------------

Dans la mesure où les tâches sont exécutées dans un environnement en ligne de commande, alors le flux de l'entrée standard (STDIN) peut être atteint. La ligne de commande UNIX permet aux applications d'interagir entre elles par une variété de moyens, dont l'une d'elles est le *pipe*, symbolisé par le caractère *|*.

Le *pipe* permet de passer la sortie d'une application (connue sous le nom de *STDOUT*) à une entrée standard d'une autre application (connue sous le nom de *STDIN*). Ces deux valeurs sont accessibles dans les tâches à travers les deux constantes de PHP `STDIN` et `STDOUT`. Il existe également un troisième flux standard, *STDERR*, accessible depuis *STDERR*, et qui vise à porter les messages d'erreur d'une application.

Qu'est-il possible de faire exactement avec l'entrée standard ? Eh bien, il s'agit d'imaginer qu'il existe une application en cours d'exécution sur le serveur qui souhaiterait communiquer avec l'application symfony. Une méthode consiste bien sûr à les faire communiquer à travers HTTP, mais un moyen plus efficace serait de rediriger sa sortie vers une tâche symfony.

On peut supposer par exemple qu'une application soit capable d'envoyer des données structurées (par exemple, un tableau PHP sérialisé), décrivant des objets de nom de domaine. On souhaite ensuite insérer ces derniers en base de données. Par conséquent, il s'agirait d'écrire la tâche suivante.

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

Il ne resterait alors plus qu'à l'utiliser de la manière suivante.

    /usr/bin/data_provider | ./symfony data:import

La chaîne `data_provider` consiste en l'application qui fournit les nouveaux objets de nom de domaine, tandis que `data:import` est la tâche qui vient tout juste d'être écrite.

Conclusion
----------

Toutes les tâches à accomplir ne sont finalement limitées que par l'imagination du développeur. Le système de tâches de symfony est à la fois puissant et suffisamment flexible, ce qui permet à n'importe quel développeur de réaliser presque tout ce dont il a besoin. Ajouter à cela la puissance d'un shell UNIX, et les tâches ne seront plus qu'un jeu d'enfant.