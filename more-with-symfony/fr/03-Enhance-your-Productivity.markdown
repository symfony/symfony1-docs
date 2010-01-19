Accroître la Productivité
=========================

*Par Fabien Potencier*

Utiliser symfony en tant que tel est, pour un développeur web, un excellent moyen d'améliorer sa productivité. En effet, tout le monde s'accorde à affirmer combien les exceptions détaillées de symfony ainsi que la barre de débogage (web debug toolbar) sont d'excellents outils qui participent à l'amélioration de la productivité.

Ce chapitre a pour objectif de présenter quelques trucs et astuces afin de parfaire davantage la productivité en utilisant quelques unes des fonctionnalités bien connues de symfony, ainsi que d'autres qui le sont moins.

Automatiser le Processus de Création de Projet
----------------------------------------------

Grâce à l'outil en ligne de commande, symfony fournit un moyen simple, rapide et efficace pour initier un nouveau projet.

    $ php /path/to/symfony generate-project foo --orm=Doctrine

La tâche `generate:project` génère la structure de base des répertoires par défaut du nouveau projet, et crée les fichiers de configuration en leur spécifiant certaines valeurs par défaut. Le framework symfony s'accompagne d'autres tâches pour créer des applications, installer des plugins, configurer le modèle de données et bien plus encore.

Cependant, les toutes premières étapes de création d'un nouveau projet sont généralement toujours les mêmes : création d'une application principale, installation de quelques plugins, personnalisation de directives de configuration par défaut, etc.

Depuis symfony 1.3, le processus de création d'un projet peut être entièrement  personnalisé et automatisé.

>**NOTE**
>Comme toutes les tâches de symfony sont des classes, il est particulièrement 
>facile de les personnaliser et de les étendre. En revanche, la tâche 
>`generate:project` est plus difficilement personnalisable car il n'existe pas 
>encore de projet lorsqu'elle est exécutée.

La tâche `generate:project` accepte une option `--installer` correspondant à un chemin absolu vers un script PHP qui sera exécuté au cours du processus de création du projet.

    $ php /path/to/symfony generate:project --installer=/somewhere/my_installer.php

Le script `/somewhere/my_installer.php` est exécuté dans le contexte de l'instance `sfGeneratProjectTask` afin de bénéficier des méthodes de la tâche en utilisant l'objet `$this`. Les sections suivantes décrivent toutes les méthodes disponibles pour personnaliser le processus de création d'un projet.

>**TIP**
>Si l'import de fichiers grâce aux URLs est configuré dans le fichier de 
>`php.ini` pour la fonction native `include()`, alors le chemin du script 
>d'installation peut être remplacé par une URL. Néanmoins, il convient de rester 
>très prudent en utilisant cette méthode, en particulier quand le contenu du 
>script d'installation est peu, voire pas du tout, connu.
>
>      $ symfony generate:project
>      --installer=http://example.com/sf_installer.php

### La Méthode `installDir()`

La méthode `installDir()` duplique une structure de répertoires, composée de sous-répertoires et de fichiers, dans le nouveau projet créé.

    [php]
    $this->installDir(dirname(__FILE__).'/skeleton');

### La Méthode `runTask()`

La méthode `runTask()` exécute une tâche. Elle prend en paramètres le nom de la tâche ainsi qu'une chaîne représentant les arguments et les options à passer à la tâche.

    [php]
    $this->runTask('configure:author', "'Fabien Potencier'");

Les arguments et les options peuvent aussi être passés sous la forme de  tableaux associatifs.

    [php]
    $this->runTask('configure:author', array('author' => 'Fabien Potencier'));

>**TIP**
>Les noms raccourcis d'une tâche fonctionnent également :
>
>     [php]
>     $this->runTask('cc');

Cette méthode peut bien sûr être utilisée pour installer des plugins.

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin');

Pour installer une version spécifique d'un plugin, il suffit simplement de lui fournir les options nécessaires.

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin', array('release' => '10.0.0', 'stability' => beta'));

>**TIP**
>L'exécution d'une tâche comprise dans un plugin nouvellement installé est 
>possible à condition de recharger la liste des tâches avant de l'utiliser.
>
>     [php]
>     $this->reloadTasks();

Il existe des tâches qui dépendent d'une application spécifique pour être exécuter. C'est le cas par exemple de la tâche `generate:module`. Par conséquent, il est nécessaire de changer le contexte de la configuration pour pouvoir l'utiliser.

    [php]
    $this->setConfiguration($this->createConfiguration('frontend', 'dev'));

### Les Loggers

Afin de fournir le maximum d'informations au développeur sur l'exécution du script d'installation, certaines informations peuvent être enregistrées en cours d'exécution.

    [php]
    // a simple log
    $this->log('some installation message');

    // log a block
    $this->logBlock(array('', 'Fabien\'s Crazy Installer', ''), 'ERROR');

    // log in a section
    $this->logSection('install', 'install some crazy files');

### Les Interactions avec l'Utilisateur

Les méthodes `askConfirmation()`, `askAndValidate()` et `ask()` offrent un moyen de rendre le processus d'installation configurable et dynamique par l'intermédiaire de questions posées à l'utilisateur dans la console.

Par exemple, si la question nécessite une confirmation en guise de réponse, alors il suffit d'utiliser la méthode `askConfirmation()` comme le montre le code ci-dessous.

    [php]
    if (!$this->askConfirmation('Are you sure you want to run this crazy installer?'))
    {
      $this->logSection('install', 'You made the right choice!');

      return;
    }

De la même manière, il est possible de poser n'importe quelle question à l'utilisateur, et obtenir sa saisie en guise de réponse grâce à la méthode `ask()`. Cette méthode retourne la réponse de l'utilisateur sous forme d'une chaîne de caractères.

    [php]
    $secret = $this->ask('Give a unique string for the CSRF secret:');

Enfin, la méthode `askAndValidate()` est, quant à elle, responsable de la récupération de la réponse de l'utilisateur, et de sa validation à la volée à l'aide d'un validateur.

    [php]
    $validator = new sfValidatorEmail(array(), array('invalid' => 'hmmm, it does not look like an email!'));
    $email = $this->askAndValidate('Please, give me your email:', $validator);

### Les Opérations sur le Système de Fichiers

Le script d'installation est également capable de réaliser des modifications sur le système de fichiers en accédant à l'objet `sfFilesystem` de symfony.

    [php]
    $this->getFilesystem()->...();

>**SIDEBAR**
>Le Processus de Création de la Sandbox
>
>Le bac à sable de symfony est un projet préfabriqué, incluant une application 
>prête à l'emploi et une base de données SQLite préconfigurée. Son processus de 
>création est réalisé à partir d'un script d'installation que quiconque peut 
>utiliser pour générer sa propre sandbox comme l'illustre le code ci-dessous.
>
>     $ php symfony generate:project --installer=/path/to/symfony/data/bin/sandbox_installer.php
>
>Etudier le contenu du fichier `symfony/data/bin/sandbox_installer.php` permet 
>d'avoir un bon exemple du fonctionnement des scripts d'installation.

Le script d'installation est un pur fichier PHP. Par conséquent, il peut réaliser à peu près tout ce dont désire le développeur. Les scripts d'installation sont un moyen sûr et rapide de personnaliser et d'industrialiser la création de projets symfony, tout en protégeant le développeur des étapes manquantes. Ces fichiers peuvent également être partagés avec les autres membres de la communauté symfony.

Développer plus Vite
--------------------

Du code PHP aux tâches en ligne de commande, programmer signifie aussi taper souvent sur son clavier. La suite de ce chapitre présente comment symfony aide le développeur à réduire cette tâche à son plus strict minimum.

### Choisir un Environnement de Développement Intégré

Utiliser un EDI (IDE pour l'acronyme anglais) aide le développeur à être toujours plus productif dans différents domaines. Pour commencer, il faut savoir que tous les EDIs modernes fournissent par défaut un mécanisme d'autocomplétion du code PHP qui facilite l'écriture de code lorsque l'on ne connait pas le nom complet d'une méthode recherchée par exemple. Ainsi, il n'est plus nécessaire de parcourir la documentation de l'API dans la mesure où l'EDI est capable de suggérer toutes les méthodes disponibles pour l'objet courant.

Enfin, il faut savoir que certains EDIs comme PHPEdit ou bien Netbeans en savent beaucoup plus sur symfony car ils fournissent une intégration spécifique et plus poussée des projets symfony.

>**SIDEBAR**
>Les Editeurs de Texte
>
>Certains utilisateurs préfèrent utiliser un éditeur de texte pour développer, 
>principalement parce que les éditeurs de texte sont plus rapides que n'importe 
>quel autre EDI. Cependant, les éditeurs de texte offrent bien moins de 
>fonctionnalités par rapport aux EDIs. La plupart des éditeurs offrent toutefois 
>des plugins / extensions qui peuvent être utilisés pour améliorer l'expérience 
>utilisateur et rendre le logiciel plus efficace avec PHP et les projets 
>symfony.
>
>Par exemple, de nombreux utilisateurs de Linux tendent à utiliser VIM pour 
>toutes leurs tâches de travail. Pour ces développeurs, une extension est 
>disponible : [vim-symfony](http://github.com/geoffrey/vim-symfony). VIM-symfony 
>est un jeu de scripts qui intègrent symfony dans l'éditeur. En utilisant 
>vim-symfony, il est ainsi possible de créer facilement des macros et des 
>commandes vim pour rationaliser les développements symfony. Cet outil embarque 
>de plus un jeu de commandes par défaut qui mettent un certain nombre de 
>fichiers de configuration à portée de main du développeur (schéma, routage, 
>etc) ainsi qu'une manière de passer simplement des actions aux vues 
>(templates).
>
>Certains développeurs Mac OS X préfèrent utiliser TextMate et peuvent donc 
>ainsi installer le [bundle](http://github.com/denderello/symfony-tmbundle) 
>symfony, qui offre un certain nombre de macros et de raccourcis qui aident 
>économiser du temps au quotidien.

#### Utiliser un EDI qui supporte symfony

Certains EDIs comme [PHPEdit 3.4](http://www.phpedit.com/en/presentation/extensions/symfony) et [NetBeans 6.8](http://www.netbeans.org/community/releases/68/) disposent d'un support natif de symfony, et fournissent ainsi une intégration très pointue du framework. Il suffit de parcourir leur documentation respective pour en savoir davantage à propos de leur support spécifique de symfony et ainsi déterminer dans quelle mesure ils participent à l'amélioration de la productivité.

#### Aider l'EDI

L'autocomplétion de PHP dans les EDIs fonctionne uniquement pour les méthodes qui sont explicitement déclarées dans le code PHP. En revanche, si le code fait usage de méthodes "magiques" `__call()` ou `__get()` par exemple, les EDIs n'auront aucun moyen de deviner les méthodes et propriétés disponibles.

Heureusement, la bonne nouvelle c'est que le développeur a la capacité d'aider l'EDI en lui fournissant des informations sur les méthodes et propriétés du fichier à l'aide de blocs de PHPDoc. Il suffit pour cela d'utiliser respectivement les annotations `@method` et `@property`.

Le code ci-dessous fait état d'une classe `Message` dans laquelle se trouvent une propriété (`message`) et une méthode (`getMessage()`) dynamiques. Il montre comment l'EDI peut avoir connaissance de ces propriété et méthode, bien qu'il n'y ait aucune définition explicite dans le code PHP.

    [php]
    /**
     * @property clob $message
     *
     * @method clob getMessage() Returns the current message value
     */
    class Message
    {
      public function __get()
      {
        // ...
      }

      public function __call()
      {
        // ...
      }
    }

Même si la méthode `getMessage()` n'existe pas, elle sera quand même reconnue par l'EDI grâce à l'annotation `@method`. Il en va de même pour la propriété `message` du fait de la présence de l'annotation `@property`.

Cette technique est notamment utilisée par la tâche `doctrine:build-model`. Par exemple, une classe Doctrine `MailMessage` avec deux colonnes (`message` et `priority`) ressemblera au code ci-dessous.

    [php]
    /**
     * BaseMailMessage
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @property clob $message
     * @property integer $priority
     *
     * @method clob        getMessage()  Returns the current record's "message" value
     * @method integer     getPriority() Returns the current record's "priority" value
     * @method MailMessage setMessage()  Sets the current record's "message" value
     * @method MailMessage setPriority() Sets the current record's "priority" value
     *
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    abstract class BaseMailMessage extends sfDoctrineRecord
    {
        public function setTableDefinition()
        {
            $this->setTableName('mail_message');
            $this->hasColumn('message', 'clob', null, array(
                 'type' => 'clob',
                 'notnull' => true,
                 ));
            $this->hasColumn('priority', 'integer', null, array(
                 'type' => 'integer',
                 ));
        }

        public function setUp()
        {
            parent::setUp();
            $timestampable0 = new Doctrine_Template_Timestampable();
            $this->actAs($timestampable0);
        }
    }

Trouver de la Documentation Rapidement
--------------------------------------

Symfony est un framework riche en fonctionnalités, et de ce fait, il n'est pas toujours évident de se rappeler toutes les possibilités de configuration, ou bien toutes les classes et les méthodes mise à disposition. La précédente section a montré qu'utiliser un EDI est un moyen efficace pour profiter de l'autocomplétion. Il est désormais temps de découvrir les autres outils existant qui peuvent être employés pour trouver des réponses aussi vite que possible.

### API en Ligne

La manière la plus rapide de trouver de la documentation à propos d'une classe 
ou d'une méthode de symfony consiste à parcourir l'[API](http://www.symfony-project.org/api/1_3/) en ligne.

Le moteur de recherche intégré à l'API en ligne est encore plus intéressant. La recherche permet de trouver rapidement une classe ou bien une méthode avec seulement quelques frappes de clavier. Après seulement quelques lettres saisies dans la boîte de recherche, une boîte de résultats apparaîtra aussi vite avec davantage de suggestions très pratiques.

La recherche s'effectue en tapant le début d'un nom de classe.

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_1.png "API Search")

Ou bien en saisissant le nom de méthode.

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_2.png "API Search")

Ou encore en tapant le nom d'une classe suivi par `::` afin de lister toutes les méthodes disponibles.

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_3.png "API Search")

Ou le début d'un nom de méthode afin d'affiner les possibilités.

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_4.png "API Search")

Si l'on souhaite lister toutes les classes d'un paquetage, il suffit de saisir le nom du paquet, puis de soumettre la recherche. Il est également possible d'intégrer l'API de recherche de symfony dans le navigateur du client. De cette manière, il n'est plus nécessaire de naviguer sur le site de symfony lorsqu'il s'agit de rechercher une information. C'est en effet possible puisque l'API en ligne de symfony bénéficie de la recherche native [OpenSearch](http://www.opensearch.org/).

Pour les utilisateurs de Firefox, le moteur de recherche de l'API en ligne de symfony apparaîtra automatiquement dans la barre des moteurs de recherches du navigateur. Un clic sur le lien "API OpenSearch" depuis la documentation de l'API en ligne permet de l'ajouter à la boîte de recherche du navigateur.

>**NOTE**
>Un billet a été rédigé sur le [blog](http://www.symfony-project.org/blog/2009/02/24/opensearch-support-for-the-symfony-api) de symfony qui présente comment le moteur de recherche de l'API 
>s'intègre à Firefox à l'aide d'un screencast.

### Les Feuilles de Triche

Il existe aujourd'hui une vaste collection de [feuilles de triche (cheat sheets)](http://trac.symfony-project.org/wiki/CheatSheets) qui permettent d'accéder directement aux informations des majeures parties du framework :

 * [Structure des Répertoires et CLI](http://andreiabohner.files.wordpress.com/2007/03/cheatsheetsymfony001_enus.pdf)
 * [La Vue](http://andreiabohner.files.wordpress.com/2007/08/sfviewfirstpartrefcard.pdf)
 * [La Vue: Partials, Components, Slots et Component Slots](http://andreiabohner.files.wordpress.com/2007/08/sfviewsecondpartrefcard.pdf)
 * [Tests Unitaires et Fonctionnels avec Lime](http://trac.symfony-project.com/attachment/wiki/LimeTestingFramework/lime-cheat.pdf?format=raw)
 * [ORM](http://andreiabohner.files.wordpress.com/2007/08/sform_enus.pdf)
 * [Propel](http://andreiabohner.files.wordpress.com/2007/08/sfmodelfirstpartrefcard.pdf)
 * [Propel Schema](http://andreiabohner.files.wordpress.com/2007/09/sfmodelsecondpartrefcard.pdf)
 * [Doctrine](http://www.phpdoctrine.org/Doctrine-Cheat-Sheet.pdf)

>**NOTE**
>Certaines de ces feuilles de triche n'ont pas encore été mises à jour pour >symfony 1.3.

### Documentation Hors Ligne

Les réponses aux questions de configuration se trouvent principalement dans le guide de référence de symfony. C'est le livre que tout développeur symfony se doit de garder près de lui car il est le moyen le plus rapide de trouver n'importe quelle configuration disponible à partir d'une table des matières  détaillée, un index des mots-clés et des références croisées à l'intérieur des chapitres, tableaux et plus encore.

Le livre est consultable gratuitement [en ligne](http://www.symfony-project.org/reference/1_3/en/), disponible à l'achat pour une copie [papier](http://books.sensiolabs.com/book/the-symfony-1-3-reference-guide) ou bien encore de le télécharger gratuitement en version [PDF](http://www.symfony-project.org/get/pdf/reference-1.3-en.pdf).

### Outils en Ligne

Le début de ce chapitre a montré que symfony fournit un large éventail d'outils pour aider le développeur à démarrer rapidement. Arrivé au terme de son développement, un projet symfony doit alors être déployé sur le serveur de production. Pour s'assurer qu'un projet est prêt au déploiement, la [checklist](http://symfony-check.org/) en ligne du déploiement couvre les principaux points importants à vérifier avant de basculer l'application 
en production.

Déboguer Plus Vite
------------------

Lorsqu'une erreur se produit en environnement de développement, symfony affiche une page d'exception explicite contenant de nombreuses informations utiles. Il est possible, par exemple, d'analyser la trace de la pile d'appels des méthodes (et fonctions) et des fichiers qui ont été exécutés.

De plus, en définissant le paramètre ~`sf_file_link_format`~ du fichier de configuration `settings.yml` (voir ci-dessous), les noms des fichiers deviennent automatiquement cliquables dans la trace de débogage de symfony. Ces derniers s'ouvriront ensuite dans l'éditeur ou EDI configuré, et le curseur sera automatiquement positionné à la ligne où l'erreur a été générée. C'est un excellent exemple d'une toute petite fonctionnalité qui peut faire économiser un temps précieux aux développeurs lorsqu'ils font face à un problème.

>**NOTE**
>Les panneaux dédiés à la vue et aux logs de la barre de débogage affichent eux 
>aussi les noms de fichiers (particulièrement lorsque l'extension xDebug est 
>activée) qui deviennent cliquables lorsque le paramètre `sf_file_link_format` 
>est défini.

Par défaut, le paramètre `sf_file_link_format` n'est pas configuré et symfony utilise la valeur de la directive de configuration  [`xdebug.file_link_format`](http://xdebug.org/docs/all_settings#file_link_format) si elle existe. Le paramètre `xdebug.file_link_format` du `php.ini` permet à des versions récentes de xDebug d'ajouter des liens pour tous les noms de fichiers présents dans la pile des appels.

La valeur du paramètre `sf_file_link_format` dépend à la fois de l'EDI et du système d'exploitation. Par exemple, pour les utilisateurs de ~TextMate~, il suffit d'ajouter la configuration suivante dans le fichier `settings.yml`:

    [yml]
    dev:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

Le jeton `%f` est remplacé par symfony par le chemin absolu vers le fichier tandis que la chaîne `%l` est remplacée par le numéro de la ligne concernée. Pour les utilisateurs de VIM, la configuration est un peu plus évoluée et décrite en ligne pour [symfony](http://geekblog.over-blog.com/article-symfony-open-exceptions-files-in-remote-vim-sessions-37895120.html) et [XDebug](http://www.koch.ro/blog/index.php?/archives/77-Firefox,-VIM,-Xdebug-Jumping-to-the-error-line.html).

>**NOTE**
>N'hésitez pas à utiliser votre moteur de recherche favoris afin d'apprendre 
>comment configurer votre EDI. Vous pouvez ainsi rechercher la configuration 
>des paramètres `sf_file_link_format` et `sf_file_link_format` dans la mesure où 
>ils fonctionnent de la même manière.

Tester plus Vite
----------------

### Enregistrer des Tests Fonctionnels

Les tests fonctionnels simulent les interactions de l'utilisateur afin de tester globalement l'intégration de toutes les parties de l'application entre elles. Ecrire des tests fonctionnels est particulièrement simple mais aussi chronophage.

Néanmoins, comme chaque fichier de tests fonctionnels est un scénario qui simule un utilisateur naviguant sur le site, et parce que naviguer réellement sur une application est plus rapide que d'écrire du code PHP, pourquoi ne pas enregistrer directement le scénario en naviguant sur l'application avant de le convertir à la volée en code PHP ?

Heureusement, symfony dispose d'un plugin pour remplir ce besoin. Il s'agit du plugin [swFunctionalTestGenerationPlugin](http://www.symfony-project.org/plugins/swFunctionalTestGenerationPlugin) qui permet de générer des squelettes de tests prêts à être personnalisés en quelques minutes. Il est bien évidemment du rôle du développeur d'ajouter ses propres appels aux différents testeurs afin de rendre le test utile et pertinent ; mais ce plugin reste cependant un excellent moyen d'économiser du temps.

Le greffon `swFunctionalTestGenerationPlugin` fonctionne par l'intermédiaire d'un filtre symfony qui intercepte toutes les requêtes qu'il convertit à la volée en code de tests fonctionnels. Après avoir installé le plugin de manière traditionnelle, il est nécessaire de l'activer en ajoutant les lignes suivantes après la ligne commentée du fichier `filters.yml` :

    [php]
    functional_test:
      class: swFilterFunctionalTest

Ensuite, le plugin doit être activé dans la classe `ProjectConfiguration` du projet :

    [php]
    // config/ProjectConfiguration.class.php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->enablePlugin('swFunctionalTestGenerationPlugin');
      }
    }

Comme le plugin utilise la barre de débogage en guise d'interface utilisateur principale, il faut alors s'assurer qu'elle est bien activée (ce qui est déjà le cas par défaut en environnement de test).

Une fois activée, un nouveau menu intitulé "Functional Test" apparaît dans la barre de débogage. Ce nouveau panneau permet entre autres de démarrer l'enregistrement d'une session en cliquant sur le lien "Activate" ou bien de réinitialiser la session courante en cliquant sur "Reset".

A la fin de l'enregistrement, il ne reste plus qu'à copier et coller le code du champ multiligne dans un fichier de tests fonctionnels, juste avant de démarrer sa personnalisation.

### Exécuter des Suites de Tests plus Rapidement

Lorsque le projet contient une longue suite de tests, cette dernière peut s'avérer particulièrement chronophage si les tests sont exécutés après chaque changement. Cela est d'autant plus vrai quand seulement quelques tests échouent parmi un large jeu de tests.

A chaque fois que le développeur corrige un test, une nouvelle exécution de toute la suite de tests devrait être lancée pour s'assurer que les autres tests n'ont pas été cassés. Or, tant que les tests échoués ne sont pas tous fixés, il n'existe pas nécessairement de raison de relancer tous les autres tests.

L'objectif consiste à accélérer ce processus. La tâche `test:all` inclut désormais une nouvelle option `--only-failed` (raccourci `-f`) qui force la tâche à ne relancer que les tests qui ont échoué à l'exécution précédente.

    $ php symfony test:all --only-failed

A la première exécution, tous les tests sont exécutés tandis que les suivantes n'exécuteront que ceux qui ont échoué au passage précédent. Une fois le code et les tests corrigés, ces derniers passeront donc puis seront supprimés des les exécutions suivantes. Lorsque tous les tests passent à nouveau, la suite de tests complète est exécutée...