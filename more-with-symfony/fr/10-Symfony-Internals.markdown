Plonger dans les Entrailles de Symfony
======================================

*Par Geoffrey Bachelet*

Vous-êtes vous déjà posé la question de savoir ce qui arrive à une requête HTTP
lorsqu'elle atteint une application symfony ? Si oui, vous êtes au bon 
endroit.

Ce chapitre expliquera en profondeur comment symfony traite chaque requête pour créer et retourner une réponse. Bien sur, il ne décrira pas seulement le processus car cela manquerait d'intérêt. Par conséquent, ce chapitre s'intéressa à étudier ce qu'il est possible de réaliser et à quels endroits le développeur peut interagir au cours de ce processus.

L'Amorçage : le Bootstrap
-------------------------

Tout commence dans le contrôleur de l'application. En considérant que le projet est configuré de manière traditionnelle avec un contrôleur `frontend` et un environnement de développement `dev`, il en résulte donc qu'un contrôleur frontal pour cette configuration est présent dans le fichier [`web/frontend_dev.php`](http://trac.symfony-project.org/browser/branches/1.3/lib/task/generator/skeleton/app/web/index.php).

Que se passe-t-il exactement dans ce fichier ? En quelques lignes de code, 
symfony récupère la configuration de l'application puis crée une instance de la classe `sfContext`, qui se charge de l'expédition de la requête. La configuration de l'application est nécessaire lors de la création de l'objet `sfContext`, qui demeure également le moteur dont dépend l'application.

>**TIP**
>Symfony donne déjà un peu de contrôle au développeur sur ce qu'il se passe en 
>lui permettant de passer un répertoire racine personnalisé en quatrième 
>argument de ~`ProjectConfiguration::getApplicationConfiguration()`~, ainsi 
>qu'une classe de contexte personnalisée dans le troisième (et dernier) argument 
>de [`sfContext::createInstance()`](http://www.symfony-project.org/api/1_3/sfContext#method_createinstance). Cette classe doit bien évidemment étendre la classe 
>originale `sfContext`.

La récupération de la configuration de l'application est une étape très 
importante. Tout d'abord, l'objet `sfProjectConfiguration` s'occupe de deviner la classe de configuration de l'application, qui se trouve plus généralement dans la classe `${application}Configuration` du fichier 
`apps/${application}/config/${application}Configuration.class.php`.

La classe `sfApplicationConfiguration` étend en réalité la classe  `ProjectConfiguration`, ce qui signifie que toute méthode définie dans  `ProjectConfiguration` est ainsi partagée entre toutes les applications. Cela signifie aussi que la classe `sfApplicationConfiguration` partage son constructeur avec les classes `ProjectConfiguration` et `sfProjectConfiguration`. C'est un avantage car la plupart du projet est configurée dans le constructeur de `sfProjectConfiguration`.

Dans un premier temps, plusieurs valeurs utiles seront calculées et stockées. Ces dernières contiennent par exemple les variables de configuration du  répertoire parent de l'application ainsi que le répertoire dans lequel se trouvent les librairies de symfony. L'objet `sfProjectConfiguration` crée aussi un nouvel objet d'expédition d'évènement, "l'event dispatcher", de type `sfEventDispatcher` ; à moins qu'un autre événement ait été fourni comme cinquième argument de la méthode statique `ProjectConfiguration::getApplicationConfiguration()` du contrôleur frontal.

Juste après cela, le développeur a la possibilité d'interagir avec le processus de configuration en redéfinissant la méthode `setup()` de l'objet `ProjectConfiguration`. C'est en effet le meilleur endroit pour activer ou bien désactiver des plugins en utilisant au choix les méthodes d'instance 
[`sfProjectConfiguration::setPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setplugins),
[`sfProjectConfiguration::enablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableplugins),
[`sfProjectConfiguration::disablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_disableplugins) or
[`sfProjectConfiguration::enableAllPluginsExcept()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableallpluginsexcept).

Ensuite, les plugins sont chargés par l'intermédiaire de la méthode  [`sfProjectConfiguration::loadPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_loadplugins), et le développeur peut alors prendre le contrôle sur ce processus via la méthode [`sfProjectConfiguration::setupPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setupplugins) qui peut aussi être redéfinie.

L'initialisation des plugins est assez simple. Pour chaque plugin, symfony 
vérifie l'existence d'une classe `${plugin}Configuration`, par exemple `sfGuardPluginConfiguration`, qu'il instancie si elle existe. Sinon, la classe `sfPluginConfigurationGeneric` est utilisée. Il est possible d'intervenir dans la configuration d'un plugin à travers deux méthodes. 

 * `${plugin}Configuration::configure()`, avant que l'autochargement de classes soit réalisé,
 * `${plugin}Configuration::initialize()`, après l'autochargement de classes.

Puis, l'objet `sfApplicationConfiguration` exécute sa méthode `configure()`, qui 
peut alors être utilisée pour personnaliser chaque configuration d'application avant que l'essentiel du processus d'initialisation de configuration interne ne commence réellement dans [`sfApplicationConfiguration::initConfiguration()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_initconfiguration).

Cette partie du processus de configuration de symony s'occupe de plein de 
choses et il y a plusieurs points d'entrées pour lesquels le développeur a la capacité d'agir au cours du processus. Par exemple, il peut interagir avec la configuration de l'autochargeur de classes en le connectant à l'évènement `autoload.filter_config`.

Puis, plusieurs fichiers de configuration importants sont chargés, dont les fichiers `settings.yml` and `app.yml`. Enfin, un dernier morceau de configuration est disponible à travers chaque fichier `config/config.php` ou alors à partir de la méthode `initialize()` de la classe de configuration. 

Si le paramètre `sf_check_lock` est activé, symfony cherchera un fichier de verrouillage, qui aura été créé par l'exécution de la commande `project:disable` par exemple. Si ce fichier existe, les fichiers suivants sont recherchés, et le premier qui est disponible est alors inclus. A ce moment là, le script se termine immédiatement.

 1. `apps/${application}/config/unavailable.php`,
 1. `config/unavailable.php`,
 1. `web/errors/unavailable.php`,
 1. `lib/vendor/symfony/lib/exception/data/unavailable.php`,

Enfin, le développeur dispose encore d'une dernière chance de personnaliser l'initialisation de l'application à travers la méthode ~`sfApplicationConfiguration::initialize()`~.

### Procédure d'Amorçage et Résumé de la Configuration

 * Récupération de la configuration de l'application,
  * `ProjectConfiguration::setup()` (définition des plugins ici),
  * Chargement des plugins,
   * `${plugin}Configuration::configure()`,
   * `${plugin}Configuration::initialize()`,
  * `ProjectConfiguration::setupPlugins()` (installation des plugins ici),
  * `${application}Configuration::configure()`,
  * `autoload.filter_config` est notifié,
  * Chargement des fichiers `settings.yml` et `app.yml`,
  * `${application}Configuration::initialize()`,
 * Création d'une instance de `sfContext`.

L'Objet `sfContext` et les Factories
------------------------------------

Avant de plonger dans le processus d'expédition, il convient de parler d'un 
point vital du flux de traitement de symfony : les factories. 

Dans symfony, les factories sont un ensemble de composants ou de classes sur lesquels se base l'application. `logger`, `i18n` sont des exemples de ces factories, et chacune d'elles est configurée dans le fichier `factories.yml`. Ce dernier est ensuite compilé par un gestionnaire de configuration (il sera évoqué plus tard) et converti en code PHP brut qui instancie les objets factory. Le code compilé est disponible dans le cache du projet dans le fichier `cache/frontend/dev/config/config_factories.yml.php`.

>**NOTE**
>Le chargement des factories intervient après l'initialisation de `sfContext`.
>Pour plus d'informations, veuillez consulter 
>[`sfContext::initialize()`](http://www.symfony-project.org/api/1_3/sfContext#method_initialize)
>et [`sfContext::loadFactories()`](http://www.symfony-project.org/api/1_3/sfContext#method_loadfactories)

Au point où nous en sommes, il est déjà possible de personnaliser une grande partie du comportement de symfony en éditant le fichier de configuration `factories.yml`. Les classes factory de symfony peuvent d'ailleurs même être remplacées par des classes personnalisées.

>**NOTE**
>Pour en savoir plus au sujet des factories, [Le Livre de Référence de  symfony](http://www.symfony-project.org/reference/1_3/en/05-Factories)
>et le fichier
>[`factories.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/factories.yml) sont des ressources inestimables.

En regardant de plus près le fichier `config_factories.yml.php` généré, on s'aperçoit que les factories sont instanciées dans un ordre précis. Ce dernier est très important car certaines factories sont dépendantes des autres. C'est le cas, par exemple, du composant `routing` qui a bien évidemment besoin du composant `request` pour retrouver les informations dont il a besoin. 

Qu'en est-il par exemple de la factory `request` ? Par défaut, la classe 
`sfWebRequest` représente l'objet de la requête de l'utilisateur (`request`). A l'instanciation, [`sfWebRequest::initialize()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_initialize) est appelée, et collecte diverses informations pertinentes telles que les paramètres GET / POST et la méthode HTTP utilisée. Des traitements complémentaires sur cet objet peuvent être réalisés à l'aide de l'évènement `request.filter_parameters`.

### Utiliser l'Evénement `request.filter_parameter`

En supposant par exemple un site qui propose une API publique aux utilisateurs, celle-ci sera rendue disponible à travers le protocole HTTP, et chaque utilisateur désirant en faire usage devra fournir une clé valide dans l'en-tête de la requête (par exemple `X_API_KEY`) afin d'être autorisé par l'application. Ce mécanisme peut être mis en place très facilement en utilisant l'événement `request.filter_parameter`.

    [php]
    class apiConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('request.filter_parameters', array(
          $this, 'requestFilterParameters'
        ));
      }

      public function requestFilterParameters(sfEvent $event, $parameters)
      {
        $request = $event->getSubject();

        $api_key = $request->getHttpHeader('X_API_KEY');

        if (null === $api_key || false === $api_user = Doctrine_Core::getTable('ApiUser')->findOneByToken($api_key))
        {
          throw new RuntimeException(sprintf('Invalid api key "%s"', $api_key));
        }

        $request->setParameter('api_user', $api_user);

        return $parameters;
      }
    }

La valeur de la clé utilisateur de l'API publique sera donc accessible depuis l'objet de requête de symfony.

    [php]
    public function executeFoobar(sfWebRequest $request)
    {
      $api_user = $request->getParameter('api_user');
    }

Cette technique peut servir entre autres à valider des appels sur des services web.

>**NOTE**
>L'événement `request.filter_parameters` intègre de nombreuses informations
>sur la requête. L'API de la méthode [`sfWebRequest::getRequestContext()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_getrequestcontext) est disponible en ligne pour plus 
>d'informations.

La factory suivante importante concerne le routage. L'initialisation du routage est relativement simple et consiste principalement en la récupération et le  paramétrage de certaines options. Des interactions complémentaires avec le processus sont disponibles au travers de l'événement `routing.load_configuration`.

>**NOTE**
>L'événement `routing.load_configuration` donne accès à l'instance de l'objet 
>de routing courant Par défaut, il s'agit de l'objet  
>[`sfPatternRouting`](http://trac.symfony-project.org/browser/branches/1.3/lib/routing/sfPatternRouting.class.php)). Les routes enregistrées sont alors 
>manipulables de plusieurs manières.

### Exemple d'Utilisation de l'Evénement `routing.load_configuration`

Par exemple, il est possible d'ajouter une route très facilement de manière programmatique.

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('routing.load_configuration', array(
        $this, 'listenToRoutingLoadConfiguration'
      ));
    }

    public function listenToRoutingLoadConfiguration(sfEvent $event)
    {
      $routing = $event->getSubject();

      if (!$routing->hasRouteName('my_route'))
      {
        $routing->prependRoute('my_route', new sfRoute(
          '/my_route', array('module' => 'default', 'action' => 'foo')
        ));
      }
    }

L'analyse de l'URL intervient après l'initialisation, via la méthode 
[`sfPatternRouting::parse()`](http://www.symfony-project.org/api/1_3/sfPatternRouting#method_parse). Plusieurs méthodes sont impliquées dans ce processus, mais il suffit de dire que lorsque l'exécution de la méthode `parse()` arrive à sa fin, la route correspondante a été trouvée, instanciée et liée à des paramètres pertinents.

>**NOTE**
>Pour plus d'informations au sujet du routing, le lecteur est invité à se 
>reporter au chapitre `Techniques Avancées de Routage` de cet ouvrage.

Lorsque toutes les factories ont été chargées et correctement initialisées, l'événement `context.load_factories` est alors déclenché. Cet événement est important car c'est celui, au niveau du framework, qui intervient le plus tôt et pour lequel le développeur peut avoir accès à tous les objets factory de base de symfony (requête, réponse, utilisateur, identification, base de données, etc.).

Il est aussi temps de se connecter à un autre événement très utile : l'événement `template.filter_parameters` qui se produit chaque fois qu'un fichier est rendu par l'objet  [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php). Il permet notamment au développeur de contrôler les paramètres envoyés au template. L'objet `sfContext` utilise cet événement pour ajouter des paramètres utiles à chaque template tels que les variables `$sf_context`, `$sf_request`, `$sf_params`, `$sf_response` et `$sf_user`. Se connecter à l'événement `template.filter_parameters` peut ainsi permettre de passer des paramètres globaux additionnels aux templates.

### Tirer Profit de l'Evénement `template.filter_parameters`

Il arrive parfois que tous les templates d'une même application aient besoin d'avoir accès à un objet particulier supplémentaire : un objet helper par exemple. Grâce à l'évènement `template.filter_parameters`, le développeur est capable de transmettre à tous les templates de l'application des paramètres supplémentaires de manière élégante en surchargeant la classe `ProjectConfiguration` du projet.

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('template.filter_parameters', array(
        $this, 'templateFilterParameters'
      ));
    }

    public function templateFilterParameters(sfEvent $event, $parameters)
    {
      $parameters['my_helper_object'] = new MyHelperObject();

      return $parameters;
    }

A présent, chaque template a accès à une instance de la classe `MyHelperObject` à travers la variable `$my_helper_object`.

### Résumé de `sfContext`

1. Initialisation de `sfContext`
1. Chargement des factories
1. Notifications des événements suivants :
 1. [request.filter_parameters](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_request_filter_parameters)
 1. [routing.load_configuration](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_routing_load_configuration)
 1. [context.load_factories](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_context_load_factories)
1. Ajout des paramètres globaux aux templates

Quelques Mots à propos des Gestionnaires de Configuration
---------------------------------------------------------

Les gestionnaires de configuration, appelés aussi ~`config handlers`~ dans symfony, sont au coeur du système de configuration du framework. Un gestionnaire de configuration a pour objectif de *comprendre* ce qui se cache derrière un fichier de configuration.

Chaque gestionnaire de configuration est une classe qui est utilisée pour convertir un ensemble de fichiers de configuration YAML en un bloc de code PHP qui peut être exécuté au besoin. Chaque fichier de configuration est affecté à un gestionnaire de configuration spécifique dans le 
[fichier `config_handlers.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/config_handlers.yml).

Pour être plus précis, la responsabilité d'un gestionnaire de configuration n'est pas d'analyser les fichiers YAML car cette tâche est déjà remplie par la classe `sfYaml`. Chaque gestionnaire de configuration crée un ensemble de directives PHP basées sur les informations du YAML et sauvegarde ces dernières dans un fichier PHP qui pourra être chargé plus tard. La version *compilée* de chaque fichier de configuration YAML peut être récupérée dans le répertoire de cache.

Le gestionnaire de configuration utilisé le plus fréquemment est très
certainement 
[`sfDefineEnvironmentConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfDefineEnvironmentConfigHandler.class.php),
qui permet de configurer des données en fonction de l'environnement d'exécution. Ce gestionnaire de configuration s'occupe de récupérer uniquement les paramètres
de l'environnement courant.

Toujours pas convaincu ? Les quelques lignes suivantes expliquent ce que réalise le gestionnaire de configuration  
[`sfFactoryConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfFactoryConfigHandler.class.php). Ce gestionnaire de configuration est utilisé pour compiler le fichier `factories.yml`, qui est l'un des fichiers de configuration les plus importants dans symfony. Il est particulier dans la mesure où il convertit un fichier de configuration YAML en un code PHP qui se chargera à son tour d'instancier les factories. C'est ce dont il a été question un peu plus haut dans ce chapitre. Cette classe n'est donc pas n'importe quel gestionnaire de configuration, n'est-ce pas ?

L'Expédition et l'Exécution de la Requête
-----------------------------------------

Assez parlé des factories, il s'agit à présent d'aborder le processus d'expédition de la requête, le `dispatching`. Une fois l'initialisation de l'objet `sfContext` terminée, la dernière étape consiste à appeler la méthode `dispatch()` du contrôleur [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch).

Le processus d'expédition de symfony est très simple. En réalité,
`sfFrontWebController::dispatch()`, prend les nom du module et de l'action
depuis les paramètres de la requête, puis les transmet à l'application via la méthode [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward).

>**NOTE**
>A ce stade, si le routage n'a pas pu récupérer un nom de module ou un nom 
>d'action depuis l'url courante, une exception [`sfError404Exception`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfError404Exception.class.php) est levée, ce qui conduit 
>automatiquement au transfert de la requête vers le module qui s'occupe de 
>traiter les erreurs 404 (voir [`sf_error_404_module` et `sf_error_404_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_error_404)). Il est important de noter qu'il suffit de lever ce 
>type d'exception depuis n'importe où dans l'application afin de reproduire ce 
>résultat.

La méthode `forward` est responsable d'une multitude de vérifications de 
pré-exécution, mais également de la préparation et de la configuration des données de l'action qui sera exécutée.

Tout d'abord, le contrôleur vérifie la présence d'un fichier 
[`generator.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/generator.yml)
pour le module courant. Cette vérification est effectuée en premier (après
un bref nettoyage des noms du module et de l'action) car le fichier de
configuration `generator.yml`, s'il existe, s'occupe de la génération de la
classe d'actions de base pour le module (à travers son 
[gestionnaire de configuration, `sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php)).
C'est d'autant plus nécessaire pour l'étape suivante du fait de la vérification de l'existence du module et de l'action. C'est le contrôleur qui s'en occupe, à travers la méthode 
[`sfController::actionExists()`](http://www.symfony-project.org/api/1_3/sfController#method_actionexists),
qui appelle en retour la méthode 
[`sfController::controllerExists()`](http://www.symfony-project.org/api/1_3/sfController#method_controllerexists)
Ici encore, si la méthode `actionExists()` renvoie faux, une exception `sfError404Exception` est levée.

>**NOTE**
>L'objet 
>[`sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php) est
>un gestionnaire de configuration spécial qui s'occupe d'instancier la bonne 
>classe de génération pour le module avant de l'exécuter. Pour plus
>d'informations sur les gestionnaires de configuration, référez-vous à la 
>section *Quelques mots à propos des gestionnaires de configuration* de ce 
>chapitre. Par ailleurs, pour en savoir plus sur le fichier `generator.yml`, 
>veuillez consulter le [chapitre 6 du livre de référence de symfony](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

Il n'est pas possible de faire plus à ce stade, si ce n'est de surcharger la méthode [`sfApplicationConfiguration::getControllerDirs()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_getcontrollerdirs) dans la classe de configuration de l'application. Cette méthode retourne un tableau de répertoires dans lesquels se trouvent les fichiers du contrôleur, avec un paramètre additionnel pour spécifier à symfony s'il doit vérifier ou non l'activation des contrôleurs via l'option de configuration `sf_enabled_modules` de `settings.yml`. Par exemple, `getControllerDirs()` pourrait ressembler à quelque chose de similaire au code ci-dessous.

    [php]
    /**
     * Controllers in /tmp/myControllers won't need to be enabled
     * to be detected
     */
    public function getControllerDirs($moduleName)
    {
      return array_merge(parent::getControllerDirs($moduleName), array(
        '/tmp/myControllers/'.$moduleName => false
      ));
    }

>**NOTE**
>Si l'action n'existe pas, une exception `sfError404Exception` est levée.

La prochaine étape consiste à récupérer une instance du contrôleur contenant
l'action. Cette étape est gérée par la méthode 
[`sfController::getAction()`](http://www.symfony-project.org/api/1_3/sfController#method_getaction)
qui, comme la méthode `actionExists()`, est une façade pour la méthode 
[`sfController::getController()`](http://www.symfony-project.org/api/1_3/sfController#method_getcontroller). Finalement, l'instance du contrôleur est ajoutée à `la pile d'appel des actions` : `action stack`.

>**NOTE**
>La pile d'appel des actions est une pile de type FIFO (First In First Out) qui 
>contient toutes les actions exécutées pendant la requête courante. Chaque 
>élément de la pile est encapsulé dans un objet `sfActionStackEntry`. La pile 
>est quant à elle disponible à partie de la méthode 
>`sfContext::getInstance()->getActionStack()` ou bien avec le code 
>`$this->getController()->getActionStack()` depuis une action.

Après quelques chargements de configuration supplémentaires, l'application sera prête à exécuter l'action demandée. La configuration spécifique au module doit toujours être chargée, et peut être trouvée à deux endroits différents. Tout d'abord, symfony cherchera après le fichier `module.yml`, normalement présent dans `apps/frontend/modules/yourModule/config/module.yml`.

Ce dernier étant un fichier de configuration YAML, il est par défaut stocké dans le cache. De plus, ce fichier de configuration peut déclarer un module comme étant interne (*internal*), en spécifiant le paramètre `mod_yourModule_is_internal`. Par conséquent, cela entraîne l'échec d'une requête car un module interne ne peut pas être appelé publiquement.

>**NOTE**
>Les modules internes étaient utilisés à l'origine pour générer du contenu de 
>mail, grâce à la méthode `getPresentationFor()` par exemple. D'autres 
>techniques existent aujourd'hui pour cela comme le rendu d'un template partiel 
>à l'aide de la méthode `renderPartial()` d'une classe d'actions.

Maintenant que le fichier `module.yml` est chargé, il est temps de vérifier une
deuxième fois que le module courant est activé. En effet, à ce stade, il est possible de modifier la valeur de `mod_$moduleName_enabled` à `false` afin de désactiver le module.

>**NOTE**
>Comme il l'a été précisé, il y'a deux manières d'activer ou de désactiver un
>module. La différence est le résultat si le module est désactivé. Dans le
>premier cas, quand le paramètre `sf_enabled_modules` est vérifié, un module 
>désactivé entrainera la levée d'une exception 
>[`sfConfigurationException`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfConfigurationException.class.php).
>Cette manière devrait être utilisée pour désactiver un module durablement.
>Dans le second cas, via le paramètre `mod_$moduleName_enabled`, un module
>désactivé entrainera un transfert de l'application vers le module désactivé.
>(voir [le paramètre
>`sf_module_disabled_module` et
>`sf_module_disabled_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_module_disabled)
>). Il est vivement recommandé d'utiliser cette manière lorsqu'il s'agit de  >désactiver un module temporairement.

La dernière opportunité de configurer un module réside dans le fichier
`config.php` (`apps/frontend/modules/yourModule/config/config.php`) dans lequel du code PHP arbitraire peut être déposé avant son exécution dans le contexte de la méthode [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward). L'instance de la classe `sfControler` est disponible dans la variable `$this` dans la mesure où le code est exécuté dans la classe `sfController`.

### Résumé du Processus d'Expédition

1. [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) est appelée
1. [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) est appelée
1. Vérification de l'existence du fichier `generator.yml`
1. Vérification de l'existence du module et de l'action
1. Récupération d'une liste de répertoires de contrôleurs 
1. Récupération d'une instance de l'action
1. Chargement du module de configuration d'après le fichier `module.yml` et / ou `config.php`.

La Chaîne de Filtres
--------------------

Maintenant que toute la configuration est prête, il est temps de commencer le
véritable travail. Il s'agit, dans ce cas particulier, de l'exécution de la
chaîne de filtrage.

>**NOTE**
>La chaine de filtres de symfony implémente un motif de conception connu sous le 
>nom de [chaîne de responsabilités](http://en.wikipedia.org/wiki/Chain-of-responsibility_pattern). 
>Il s'agit d'un motif simple mais puissant qui permet de déterminer si 
>l'exécution de la chaîne doit continuer ou non pour chaque maillon de celle-ci. 
>Chaque maillon de la chaîne est aussi capable de s'exécuter avant et après le 
>reste de la chaîne d'exécution.

La configuration de la chaîne de filtrage est tirée du fichier 
[`filters.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/filters.yml) du module courant, ce qui explique pourquoi une instance de l'action est nécessaire. L'ensemble des filtres de la chaîne et l'ordre dans lequel ils sont appelés, peuvent être modifiés. Il ne faut pas pas oublier que le filtre de rendu doit toujours être le premier dans la liste ; les explications sont données plus loin. La configuration des filtres est la suivante par défaut.

* [`rendering`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfRenderingFilter.class.php)
* [`security`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfSecurityFilter.class.php)
* [`cache`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfCacheFilter.class.php)
* [`execution`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfExecutionFilter.class.php)

* [`rendering`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfRenderingFilter.class.php)
* [`security`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfSecurityFilter.class.php)
* [`cache`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfCacheFilter.class.php)
* [`execution`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfExecutionFilter.class.php)

>**NOTE**
>Il est fortement recommandé d'ajouter des filtres personnalisés entre les >filtres `security` et `cache`.

### Le Filtre de Sécurité

Etant donné que le filtre de rendu attend que les autres filtres se terminent 
avant de réaliser quoi que soit, le premier filtre exécuté en réalité est le
filtre de sécurité. Ce filtre s'assure que tout est correct en fonction de ce
qui a été écrit dans le fichier de configuration
[`security.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/security.yml). Plus précisément, le filtre se charge de rediriger un utilisateur non identifié vers le module et l'action de `login`.

En revanche, si l'utilisateur n'a pas des droits d'accès suffisants pour le module et l'action demandés, le filtre transférera l'utilisateur vers le module `secure`. Il n'est pas inintéressant de remarquer que ce filtre est exécuté si la sécurité est activée pour l'action donnée.

### Le Filtre du Cache

Le filtre de cache intervient ensuite. Ce filtre a la possibilité d'empêcher
l'exécution de filtres futurs. En effet, si le cache est activé, et que les données nécessaires sont déjà disponibles, pourquoi vouloir chercher à exécuter l'action quand même ? Bien sûr, ceci ne fonctionnera que pour des pages qui peuvent être chargées en cache intégralement, ce qui n'est pas le cas de la majorité des pages.

Ce filtre a toutefois une partie de code qui s'exécute après le filtre d'exécution et juste avant le filtre de rendu. Ce code permet de paramétrer les bons en-têtes de cache HTTP, et de placer les pages en cache si nécessaire, grâce à la méthode [`sfViewCacheManager::setPageCache()`](http://www.symfony-project.org/api/1_3/sfViewCacheManager#method_setpagecache)

### Le Filtre d'Exécution

Enfin, le filtre d'exécution prend en charge l'exécution de la logique métier et gérera la vue associée. Tout commence quand le filtre vérifie le cache pour l'action courante. Bien sûr, si quelque chose se trouve dans le cache, l'exécution de l'action actuelle sera annulée et la vue `Success` sera quant à elle exécutée.

Si l'action n'est pas trouvée dans le cache, alors il est temps d'exécuter la méthode `preExecute()` du contrôleur, et d'exécuter enfin l'action elle-même. Ceci est accompli par une d'instance de la classe d'action via un appel à
[`sfActions::execute()`](http://www.symfony-project.org/api/1_3/sfActions#method_execute). Cette méthode ne fait que très peu de choses car elle vérifie simplement que l'action peut être appelée, puis elle l'appelle. De retour dans le filtre, la méthode `postExecute()` de l'action est maintenant exécutée.

>**NOTE**
>La valeur de retour de l'action est très importante car elle déterminera
>la vue à exécuter. Par défaut, si aucune valeur de retour n'est trouvée, c'est 
>la valeur `sfView::SUCCESS` qui sera retournée par défaut, ce qui correspond à 
>la valeur `Success` et donc au template `indexSuccess.php`.

Il reste une dernière étape avant la génération de la vue. Le filtre vérifie si deux valeurs de retour spéciales ont été retournées `sfView::HEADER_ONLY`
et `sfView::NONE`. Chacune de ces deux valeurs réalise ce que leur nom suggère de faire : envoi des en-têtes HTTP seulement (géré par 
[`sfWebResponse::setHeaderOnly()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_setheaderonly)) ou bien désactivation complète de la génération du template.

>**NOTE**
>Bien que les noms des vues par défaut de symfony soient `ALERT`, `ERROR`, 
>`INPUT`, `NONE` et `SUCCESS`, le développeur a la possibilité de retourner 
>n'importe quelle autre valeur qui lui convient.

A partir du moment où l'on sait ce que l'on souhaite précisément obtenir comme rendu, alors c'est que l'on est prêt à plonger dans la dernière étape du processus : l'exécution de la vue courante.

La première chose à faire consiste à récupérer un objet [`sfView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfView.class.php) à l'aide de la méthode [`sfController::getView()`](http://www.symfony-project.org/api/1_3/sfController#method_getview). Cet objet peut provenir de deux endroits différents. Tout d'abord, il faut savoir que pour chaque action spécifique, un objet de vue personnalisée peut être configuré dans une classe nommée `actionSuccessView` ou bien `module_actionSuccessView`, et présente dans un fichier appelé `apps/frontend/modules/module/view/actionSuccessView.class.php`. On considère ici que le module et l'action demandés se nomment respectivement `module` et `action`. Sinon, c'est la classe définie dans la directive de configuration `mod_module_view_class` qui sera utilisée, alors que sa valeur par défaut est [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php).

>**TIP**
>Utiliser une classe de vue personnalisée permet d'exécuter du code spécifique à >la vue dans la méthode [`sfView::execute()`](http://www.symfony-project.org/api/1_3/sfView#method_execute). Par exemple, c'est typiquement à cet endroit précis que l'on peut imaginer 
>l'initialisation d'un moteur de templates si l'on souhaite en utiliser un.

Il existe trois modes de rendu possibles pour rendre la vue :

1. `sfView::RENDER_NONE`" : c'est l'équivalent de `sfView::NONE`, il annule toute tentative de rendu.
1. `sfView::RENDER_VAR` : peuple la présentation de l'action, qui est alors accessible depuis la méthode [`sfActionStackEntry::getPresentation()`](http://www.symfony-project.org/api/1_3/sfActionStackEntry#method_getpresentation) correspondante à son entrée dans la pile des appels.
1. `sfView::RENDER_CLIENT`, c'est le mode par défaut qui rend la vue et alimente le contenu de la réponse.

>**NOTE**
>En effet, le mode rendu est utilisé uniquement à travers la méthode 
>[`sfController::getPresentationFor()`](http://www.symfony-project.org/api/1_3/sfController#method_getpresentationfor) qui retourne le rendu pour un module et 
>une action donnés.

### Le Filtre de Rendu

Ce chapitre touche presque à sa fin, mais il reste encore une dernière étape à franchir. A ce stade, la chaîne de filtrage a pratiquement terminé son exécution. Or, il ne faut pas oublier que le comportement de ce filtre est un peu particulier. En effet, il a attendu depuis le début que tous les filtres de la chaîne de filtres finissent leur tâche respective avant de pouvoir commencer la sienne. Le travail du filtre de rendu consiste à envoyer le contenu de la réponse au navigateur en utilisant la méthode [`sfWebResponse::send()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_send).

### Résumé de l'Exécution de la Chaîne de Filtrage

1. La chaîne de filtrage est instanciée avec sa configuration contenue dans le fichier `filters.yml`
1. Le filtre de sécurité vérifie les autorisations et les droits d'accès
1. Le filtre de cache gère le cache pour la page courante
1. Le filtre d'exécution exécute l'action
1. Le filtre de rendu envoie la réponse générée à travers l'objet `sfWebResponse`

Résumé général
--------------

1. Récupération de la configuration de l'application
1. Création d'une instance de `sfContext`
1. Initialisation de `sfContext`
1. Chargement des Factories
1. Notification des événements :
 1. ~`request.filter_parameters`~
 1. ~`routing.load_configuration`~
 1. ~`context.load_factories`~
1. Ajout des paramètres globaux des templates
1. Appel de la méthode [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch)
1. Appel de la méthode [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
1. Vérification de l'existence fichier `generator.yml`
1. Vérification de l'existence du module et de l'action
1. Récupération d'une liste des répertoires des contrôleurs
1. Récupération d'une instance de l'action
1. Chargement du module de configuration à travers le fichier `module.yml` et / ou `config.php`
1. La chaîne de filtres est instanciée avec sa configuration contenue dans le fichier `filters.yml`
1. Le filtre de sécurité vérifie les autorisations et les droits d'accès
1. Le filtre de cache gère le cache pour la page courante
1. Le filtre d'exécution exécute l'action
1. Le filtre de rendu envoie la réponse via l'objet `sfWebResponse`

Conclusion
----------

C'est fini ! Le cycle de traitement d'une requête a été entièrement géré. La boucle a été bouclée et nous sommes maintenant prêt à affronter toutes les suivantes.

Bien sûr, ce chapitre n'est qu'un aperçu de ce traitement, mais nous pourrions néanmoins écrire un livre entier sur les processus internes de symfony. Nous vous invitons d'ores et déjà à explorer le code source par vous-même - c'est et ce sera toujours le meilleur moyen d'apprendre les mécanismes internes d'un framework ou d'une librairie.