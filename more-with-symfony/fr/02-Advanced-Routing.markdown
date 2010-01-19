Techniques Avancées de Routage
==============================

*Par Ryan Weaver*

En son noyau, le framework de routage est en réalité une carte qui relie chaque URL à une destination spécifique à l'intérieur d'un projet symfony et vice versa. Cet outil permet de créer des URLs propres et élégantes, et qui restent complètement indépendantes de la logique applicative. Ces améliorations ont été réalisées depuis les versions récentes de symfony, et le framework de routing continue encore d'aller plus loin aujourd'hui.

Ce chapitre décrira comment créer une application web simple pour laquelle chaque client utilise un sous-domaine séparé, par exemple `client1.mydomain.com` et `client2.mydomain.com`. C'est en étendant le framework de routage que cette tâche devient très facile à réaliser.

>**NOTE**
>Ce chapitre nécessite l'usage de Doctrine en guise de couche d'ORM pour le
>projet symfony.

Initialisation du Projet : Un CMS pour Plusieurs Clients
--------------------------------------------------------

Dans ce chapitre, une société fictive - Sympal Builder - désire mettre en place un CMS permettant à ses clients de construire leur propre site web et l'héberger sur un sous-domaine de `sympalbuilder.com`. L'objectif consiste ainsi à rendre le site grand public accessible à l'adresse `xxx.sympalbuilder.com` pour le client XXX, et l'interface d'administration correspondante à l'adresse `xxx.sympalbuilder.com/backend.php`.

>**NOTE**
>Le nom `Sympal` est emprunté du nom de l'application
>[Sympal](http://www.sympalphp.org/) développée par Jonathan Wage. Sympal est un 
>framework de gestion de contenu bâti sur un socle technique symfony et 
>Doctrine.

Ce projet dispose de deux besoins fonctionnels basiques et obligatoires :

  * Les utilisateurs doivent être capables de créer des pages web et spécifier 
    pour chacune d'entre elles un titre, un contenu et une URL correspondante.

  * L'application entière doit être construite à l'intérieur d'un seul projet  
    symfony qui gère à la fois les applications `frontend` et `backend` de tous 
    les clients du site. La détermination du client est basée sur le 
    sous-domaine demandé et afin de permettre le chargement des bonnes 
    informations dans l'application.

>**NOTE**
>Pour créer cette application, le serveur web aura besoin de rediriger toutes 
>les requêtes des sous-domaines `*.sympalbuilder.com` vers le même document 
>racine, le répertoire web du projet symfony.

### Le Modèle de Données et les Données

La base de données du projet se compose de deux tables `Client` et `Page`. Chaque `Client` est représenté par un site (et donc un sous-domaine) qui contient plusieurs objets de type `Page`.

    [yml]
    # config/doctrine/schema.yml
    Client:
      columns:
        name:       string(255)
        subdomain:  string(50)
      indexes:
        subdomain_index:
          fields:   [subdomain]
          type:     unique

    Page:
      columns:
        title:      string(255)
        slug:       string(255)
        content:    clob
        client_id:  integer
      relations:
        Client:
          alias:        Client
          foreignAlias: Pages
          onDelete:     CASCADE
      indexes:
        slug_index:
          fields:   [slug, client_id]
          type:     unique

>**NOTE**
>Bien que les indexes sur chaque table ne soient pas nécessaires, il est tout de 
>même important de les définir dans la mesure où la base de données sera 
>fréquemment interrogée sur ces colonnes.

Pour commencer à donner naissance au projet, il est nécessaire d'avoir dès le départ quelques jeux de données de test dans le fichier `data/fixtures/fixtures.yml` :

    [yml]
    # data/fixtures/fixtures.yml
    Client:
      client_pete:
        name:      Pete's Pet Shop
        subdomain: pete
      client_pub:
        name:      City Pub and Grill
        subdomain: citypub

    Page:
      page_pete_location_hours:
        title:     Location and Hours | Pete's Pet Shop
        content:   We're open Mon - Sat, 8 am - 7pm
        slug:      location
        Client:    client_pete
      page_pub_menu:
        title:     City Pub And Grill | Menu
        content:   Our menu consists of fish, Steak, salads, and more.
        slug:      menu
        Client:    client_pub

Ce jeu de données de test déclare initialement deux sites web constitués chacun d'une seule page. L'url complète vers chaque page est définie à l'aide des deux colonnes `subdomain` et `slug` de l'objet de modèle `Page`.

    http://pete.sympalbuilder.com/location
    http://citypub.sympalbuilder.com/menu

### Le Routage

Chaque page d'un site Sympal Builder correspond directement à un objet de modèle `Page` pour lequel sont définis un titre et un contenu à afficher. L'étape suivante consiste à relier une URL à un objet `Page` de l'application. Pour ce faire, il suffit de créer une route d'objet de type `sfDoctrineRoute` qui s'appuie sur la colonne `slug`. Ainsi, le code suivant recherchera automatiquement un objet `Page` dans la base de données, pour lequel la valeur du champ `slug` correspond à celui transmis dans l'url :

    [yml]
    # apps/frontend/config/routing.yml
    page_show:
      url:        /:slug
      class:      sfDoctrineRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show

La route ci-dessus correspondra automatiquement à l'url `http://pete.sympalbuilder.com/location` ainsi qu'à son objet `Page` associé. Malheureusement, cette route correspondra *aussi* à l'url `http://pete.sympalbuilder.com/menu`, ce qui signifie que le menu du restaurant sera affiché sur le site de Pete ! Par conséquent, la route est encore incapable de faire la différence entre les sous-domaines des différents clients.

Pour concrétiser cette application, la route a besoin d'être plus intelligente. Elle devrait en effet correspondre à une `Page` en se basant à la fois sur la valeur de la colonne `slug` *et* sur celle de la colonne `client_id`. En d'autres termes, il s'agit de faire correspondre l'hôte (par exemple `pete.sympalbuilder.com`) avec la colonne `subdomain` du modèle `Client`. Pour y parvenir, il suffit d'étendre le framework de routage en créant une classe personnalisée de route. Néanmoins, avant de démarrer, il convient de rappeler quelques principes de base au sujet du fonctionnement du système de routage de symfony.

Mécanismes Internes du Framework de Routage
-------------------------------------------

Dans symfony, une **route** est un objet de type ~`sfRoute`~ qui dispose de deux rôles importants :

 * Générer une URL : par exemple, si un paramètre `slug` est passé à une règle  
   `page_show`, alors la route doit être capable de générer une véritable URL 
   correspondante (`/location` par exemple).

 * Reconnaître une URL entrante : en lui fournissant l'url d'une requête 
   entrante, chaque route doit être capable de déterminer si l'URL correspond 
   aux contraintes de la route.

Les informations de chaque route individuelle sont généralement déclarées à l'intérieur de chaque répertoire `config/` d'une application, et plus précisément dans le fichier `app/yourappname/config/routing.yml`. Il est important de se rappeler que chaque route est *un objet de type `sfRoute`*. Par conséquent, comment ces quelques données YAML simples deviennent de véritables  objets `sfRoute` ?

### Le Gestionnaire de Configuration du Cache du Routage

En dépit du fait que la plupart des routes sont définies dans un fichier YAML, chaque entrée de ce fichier est en réalité transformée en un objet au traitement de la requête.

Cette transformation est assurée à l'aide d'une classe spéciale plus communément intitulée gestionnaire de configuration de cache. A l'issue de la conversion, il en résulte du code PHP représentant toutes les routes de l'application. Bien que les spécificités de ce mécanisme dépassent le périmètre de ce chapitre, il n'en demeure pas moins intéressant de tirer quelques informations de la version compilée de la route `page_show`.

Le code compilé se trouve en effet dans le fichier `cache/yourappname/envname/config/config_routing.yml.php` pour chaque application et pour chaque environnement. Le listing ci-dessous est une version simplifiée de ce à quoi ressemble la route `page_show` :

    [php]
    new sfDoctrineRoute('/:slug', array (
      'module' => 'page',
      'action' => 'show',
    ), array (
      'slug' => '[^/\\.]+',
    ), array (
      'model' => 'Page',
      'type' => 'object',
    ));

>**TIP**
>Le nom de la classe de chaque route est défini grâce à la clé `class` du 
>fichier `routing.yml`. Si aucune clé `class` n'est spécifiée, la route par 
>défaut sera issue de la classe `sfRoute`. Une autre classe spécifique plus 
>commune est `sfRequestRoute` qui offre au développeur la possibilité de créer 
>des routes dites RESTful. Une liste complète des classes de route et des 
>options disponibles existe dans le [guide de référence de symfony](http://www.symfony-project.org/reference/1_3/en/10-Routing).

### Faire Correspondre une Requête Entrante à une Route Spécifique

L'un des principaux rôle du framework de routage consiste à faire correspondre chaque URL entrante avec le bon objet route. C'est la classe  ~`sfPatternRouting`~ qui représente le moteur du noyau du routage et qui est dédiée à cette tâche. En dépit de son importance, un développeur n'interagira que très rarement avec `sfPatternRouting`.

Pour faire correspondre la bonne route, la classe `sfPatternRouting` itère sur chaque objet `sfRoute` et demande à la route si elle répond à l'url entrante. Intérieurement, cela signifie que `sfPatternRouting` appelle la méthode `sfMethod::matchesUrl()` sur chaque objet route. Cette méthode retourne simplement `false` si la route ne correspond pas à l'url entrante.

A contrario, si la route *répond parfaitement* à l'URL entrante, la méthode `sfRoute::matchesUrl()` ne renverra pas seulement la valeur `true`. Bien au contraire, la route retourne un tableau des paramètres qui sont ensuite fusionnés à l'intérieur de l'objet représentant la requête. Par exemple, l'url `http://pete.sympalbuilder.com/location` répond à la route `page_show`, dont la méthode `matchesUrl()` retourne alors le tableau suivant :

    [php]
    array('slug' => 'location')

Cette information est ensuite fusionnée dans l'objet de requête, et c'est pourquoi les paramètres de la route (`slug` par exemple) sont accessibles depuis le fichier d'actions.

    [php]
    $this->slug = $request->getParameter('slug');

Comme on peut s'en douter, surcharger ou bien redéfinir complètement la méthode `sfRoute::matchesUrl()` est un excellent moyen d'étendre et de personnaliser une route afin de réaliser presque tout ce que l'on souhaite.

Créer une Classe de Route Personnalisée
---------------------------------------

L'étape suivante consiste à présent à créer une nouvelle classe de route personnalisée dans le but d'étendre la route `page_show` actuelle afin qu'elle puisse se baser sur le sous-domaine des objets `Client`. Pour ce faire, il suffit de créer un nouveau fichier nommé `acClientObjectRoute.class.php` et de le placer dans le répertoire `lib/routing` du projet en ayant pris le soin de créer ce répertoire juste avant :

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        return $parameters;
      }
    }

Après avoir vidé le cache de symfony, il ne reste plus qu'à indiquer à la route `page_show` d'utiliser cette classe de route en modifiant la valeur de la clé `class` de la définition de la route dans le fichier `routing.yml` :

    [yml]
    # apps/fo/config/routing.yml
    page_show:
      url:        /:slug
      class:      acClientObjectRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show

Pour l'instant, la classe `acClientObjectRoute` n'apporte aucune fonctionnalité supplémentaire mais toutes les pièces du puzzle sont désormais en place. La section suivante explique quels sont les deux rôles spécifiques de la méthode `matchesUrl()` et donne la démarche à suivre pas à pas pour redéfinir la logique de cette dernière.

### Ajouter de la Logique à la Route Personnalisée

Pour ajouter la fonctionnalité requise à la classe de route personnalisée, il suffit de remplacer le contenu du fichier `acClientObjectRoute.class.php` par le code ci-dessous.

    [php]
    class acClientObjectRoute extends sfDoctrineRoute
    {
      protected $baseHost = '.sympalbuilder.com';

      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        // return false if the baseHost isn't found
        if (strpos($context['host'], $this->baseHost) === false)
        {
          return false;
        }

        $subdomain = str_replace($this->baseHost, '', $context['host']);

        $client = Doctrine_Core::getTable('Client')
          ->findOneBySubdomain($subdomain)
        ;

        if (!$client)
        {
          return false;
        }

        return array_merge(array('client_id' => $client->id), $parameters);
      }
    }

Le premier appel à la méthode `parent::matchesUrl()` est important puisqu'il exécute le processus classique d'analyse de la route. Dans cet exemple, tant  que l'URL `/location` répond à la route `page_show`, la méthode `parent::matchesUrl()` doit retourner un tableau contenant le paramètre `slug` correspondant.

    [php]
    array('slug' => 'location')

En d'autres termes, tout le travail complexe d'analyse de la route a été réalisé par le framework, ce qui permet au reste de la méthode de se concentrer sur l'analyse de la correspondance avec le sous-domaine du `Client`.

    [php]
    public function matchesUrl($url, $context = array())
    {
      // ...

      $subdomain = str_replace($this->baseHost, '', $context['host']);

      $client = Doctrine_Core::getTable('Client')
        ->findOneBySubdomain($subdomain)
      ;

      if (!$client)
      {
        return false;
      }

      return array_merge(array('client_id' => $client->id), $parameters);
    }

En exécutant un simple remplacement de chaine, il est possible d'isoler la portion de l'hôte issu du sous-domaine, afin d'interroger ensuite la base de données pour vérifier si un objet `Client` a ce sous-domaine. Si aucun client ne possède ce sous-domaine, alors la méthode retourne `false` afin d'indiquer que la requête entrante ne correspond pas à la route.

En revanche, si un objet `Client` répondant au sous-domaine courant existe dans la base de données, alors un tableau contenant un paramètre supplémentaire `client_id` est fusionné avec le tableau original retourné.

>**TIP**
>Le tableau `$context` passé à la méthode `matchesUrl()` est prérempli 
>d'informations utiles concernant la requête courante, incluant l'hôte (`host`), 
>un booléen `is_secure`, le `request_uri`, la méthode (`method`) HTTP et bien 
>plus encore.

Ceci étant fait, on peut se demander ce que la nouvelle classe de route personnalisée a vraiment accompli. La classe `acClientObjectRoute` réalise désormais les tâches suivantes :

 * La variable entrante `$url` correspondra à la route uniquement si l'hôte 
   contient un sous-domaine appartenant à l'un des objets `Client` du modèle.

 * Si la route répond, alors un paramètre additionnel `client_id` pour l'objet 
   de modèle correspondant est retourné dans le tableau, qui sera ensuite 
   fusionné dans les paramètres de la requête.

### Profiter de la Route Personnalisée

Maintenant que le bon paramètre `client_id` est retourné par la classe `acClientObjectRoute`, il devient alors naturellement accessible via l'objet de la requête. Par exemple, l'action `page/show` peut ainsi utiliser le paramètre `client_id` pour retrouver l'objet `Page` correct :

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = Doctrine_Core::getTable('Page')->findOneBySlugAndClientId(
        $request->getParameter('slug'),
        $request->getParameter('client_id')
      );

      $this->forward404Unless($this->page);
    }

>**NOTE**
>La méthode `findOneBySlugAndClientId()` est un nouveau type de [finders  magiques](http://www.doctrine-project.org/upgrade/1_2#Expanded%20Magic%20Finder>s%20to%20Multiple%20Fields) introduit dans Doctrine 1.2 qui permet d'interroger 
>une table en s'appuyant sur plusieurs champs.

Aussi simple que cela puisse paraître, le framework de routage propose une solution encore plus élégante. Il suffit dans un premier temps d'ajouter la méthode ci-dessous à la classe `acClientObjectRoute`.

    [php]
    protected function getRealVariables()
    {
      return array_merge(array('client_id'), parent::getRealVariables());
    }

Avec cette dernière pièce du puzzle, l'action peut s'appuyer entièrement sur la route pour retourner un objet `Page` correspondant. L'action `page/show` peut ainsi être réduite à seulement une ligne.

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = $this->getRoute()->getObject();
    }

Sans aucun effort additionnel, le code ci-dessus interrogera la base de données pour récupérer un objet `Page` en s'appuyant sur les deux colonnes `slug` *et* `client_id`. De plus, comme toutes les routes d'objet, l'action est automatiquement redirigée vers une page d'erreur 404 si aucun objet correspondant n'a été trouvé.

Mais comment tout cela fonctionne-t-il ? Les routes d'objet comme `sfDoctrineRoute`, dont la classe `acClientObjectRoute` hérite, interrogent automatiquement la base de données afin de récupérer l'objet correspondant aux valeurs des variables spécifiées à la clé `url` de la route. Par exemple, la route `page_show`, qui contient la variable `slug` dans son `url`, demande à la base de données de lui donner l'objet `Page` en s'aidant de la colonne `slug`.

Or dans cette application, la route `page_show` doit également s'appuyer sur la valeur de la colonne `client_id` afin de récupérer les objets correspondants. Pour ce faire, il aura seulement fallu surcharger la méthode ~`sfObjectRoute::getRealVariables()`~ qui est automatiquement appelée à l'intérieur de l'objet pour déterminer sur quelles colonnes la requête SQL doit être exécutée. En ajoutant le champ `client_id` au tableau, l'objet `acClientObjectRoute` interrogera la base de données en se basant sur ces deux colonnes `slug` et `client_id`.

>**NOTE**
>Les routes d'objets ignorent automatiquement toutes les variables qui ne 
>correspondent pas à des colonnes réelles. Par exemple, si la clé `url` contient 
>une variable `:page` tandis qu'aucune colonne `page` n'existe dans la table 
>associée, alors cette variable sera ignorée.

En l'état actuel des choses, la classe de route personnalisée accomplit tout ce qui est nécessaire sans trop d'effort. Cette nouvelle route sera réutilisée dans les prochaines sections pour créer une interface d'administration spécifique à chaque client.

### Générer la Bonne Route

Un seul petit problème subsiste avec la manière dont est générée l'url. Pour comprendre, il suffit de regarder l'exemple suivant qui décrit la création d'un lien vers une page.

    [php]
    <?php echo link_to('Locations', 'page_show', $page) ?>

-

    Url générée: /location?client_id=1

Comme on peut le constater, le paramètre `client_id` a été automatiquement ajouté à la fin de l'url. Cela se produit en effet parce que la route essaie d'utiliser toutes les variables pour générer l'url. Comme la route sait qu'elle doit utiliser les deux paramètres `slug` et `client_id`, alors elle les ajoute à l'url qu'elle génère. Pour fixer cette petite contrariété, il suffit d'ajouter la méthode suivante à la classe `acClientObjectRoute`.

    [php]
    protected function doConvertObjectToArray($object)
    {
      $parameters = parent::doConvertObjectToArray($object);

      unset($parameters['client_id']);

      return $parameters;
    }

Lorsqu'une route d'objet est générée, elle s'attend à retrouver toutes les informations nécessaires en appelant la méthode `doConvertObjectToArray()`. Par défaut, le paramètre `client_id` est retourné dans le tableau `$parameters`. Désormais, en supprimant ce paramètre du tableau, cela permet ainsi d'éviter de le voir réapparaître dans l'url générée. Il est important de se souvenir que la route d'objet peut s'offrir ce luxe dans la mesure où l'information du `Client` est contenue dans le sous-domaine lui-même.

>**TIP**
>Le traitement de la méthode `doConvertObjectToArray()` peut entièrement être 
>redéfini et géré par les soins du développeur en ajoutant une méthode 
>`toParams()` à la classe de modèle. Cette méthode se doit de retourner un 
>tableau des paramètres qui doivent figurer au moment de la génération de la 
>route.

Les Collections de Routes
-------------------------

Pour en finir avec l'application Sympal Builder, une interface d'administration doit être créée dans laquelle chaque `Client` individuel sera capable de gérer ses propres `Pages`. Pour ce faire, l'application a besoin d'un jeu d'actions pour lister, créer, éditer et supprimer des objets `Page`.

Comme tous ces types de modules sont sensiblement génériques, symfony est capable de les générer automatiquement. Il suffit pour ce faire d'exécuter la tâche suivante depuis la ligne de commande afin de générer un module `pageAdmin` à l'intérieur d'une application `backend` qui aura été créée juste avant.

    $ php symfony doctrine:generate-module backend pageAdmin Page --with-doctrine-route --with-show

La tâche ci-dessus génère un module avec un fichier d'actions et ses vues associées capables de réaliser toutes les modifications nécessaires sur n'importe quel objet `Page`. De nombreuses personnalisations de ce module CRUD généré peuvent être réalisées mais cela sort du cadre de ce chapitre.

Alors que la commande ci-dessus prépare le module pour le développeur, il reste encore une route à créer pour chaque action. En passant l'option `--with-doctrine-route` à la commande, chaque action a été générée pour fonctionner avec une route d'objet. Cela réduit considérablement la taille du code dans chaque action. Par exemple, l'action `edit` contient à présent seulement une simple ligne.

    [php]
    public function executeEdit(sfWebRequest $request)
    {
      $this->form = new PageForm($this->getRoute()->getObject());
    }

Au total, l'application d'administration de Sympal Builder contient seulement les actions `index`, `new`, `create`, `edit`, `update`, et `delete`. Dans un schéma classique de développement, créer ces routes en vue d'une utilisation  [RESTful](http://en.wikipedia.org/wiki/Representational_State_Transfer) aurait obligé le développeur à davantage de configuration dans le fichier `routing.yml`.

    [yml]
    pageAdmin:
      url:         /pages
      class:       sfDoctrineRoute
      options:     { model: Page, type: list }
      params:      { module: page, action: index }
      requirements:
        sf_method: [get]
    pageAdmin_new:
      url:        /pages/new
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: new }
      requirements:
        sf_method: [get]
    pageAdmin_create:
      url:        /pages
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: create }
      requirements:
        sf_method: [post]
    pageAdmin_edit:
      url:        /pages/:id/edit
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: edit }
      requirements:
        sf_method: [get]
    pageAdmin_update:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: update }
      requirements:
        sf_method: [put]
    pageAdmin_delete:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: delete }
      requirements:
        sf_method: [delete]
    pageAdmin_show:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: show }
      requirements:
        sf_method: [get]

Enfin, pour connaître et visualiser la configuration de toutes les routes déclarées dans une application, il suffit d'exécuter la tâche `app:routes` dans la console afin d'obtenir un résumé de la définition de chaque route.

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages
    pageAdmin_new    GET    /pages/new
    pageAdmin_create POST   /pages
    pageAdmin_edit   GET    /pages/:id/edit
    pageAdmin_update PUT    /pages/:id
    pageAdmin_delete DELETE /pages/:id
    pageAdmin_show   GET    /pages/:id

### Remplacer les Routes par une Collection de Routes

Heureusement, symfony fournit une manière bien plus simple pour spécifier toutes les routes qui appartiennent à un CRUD traditionnel. Par conséquent, tout le contenu actuel du fichier `routing.yml` se résume à une seule et unique route.

    [yml]
    pageAdmin:
      class:          sfDoctrineRouteCollection
      options:
        model:        Page
        prefix_path:  /pages
        module:       pageAdmin

Une fois encore, l'exécution de la tâche `app:routes` permet de visualiser toutes les routes définies. Comme on peut le constater, les sept routes précédentes existent toujours.

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages.:sf_format
    pageAdmin_new    GET    /pages/new.:sf_format
    pageAdmin_create POST   /pages.:sf_format
    pageAdmin_edit   GET    /pages/:id/edit.:sf_format
    pageAdmin_update PUT    /pages/:id.:sf_format
    pageAdmin_delete DELETE /pages/:id.:sf_format
    pageAdmin_show   GET    /pages/:id.:sf_format

Les collections de routes sont un type particulier de routes d'objet qui représentent intérieurement plus d'une seule route. La route ~`sfDoctrineRouteCollection`~, par exemple, génère automatiquement les sept routes les plus fréquentes nécessaires à un CRUD. En coulisses, la classe  `sfDoctrineRouteCollection` ne fait rien de plus que créer les sept même routes définies précédemment dans le fichier `routing.yml`. Les collections de route existent de base comme raccourci pour créer un groupe de routes communes.

Créer une Classe Personnalisée de Collection de Routes
------------------------------------------------------

A partir de maintenant, chaque `Client` sera capable de modifier ses propres objets `Page` par l'intermédiaire d'un CRUD interne fonctionnel et accessible depuis l'URL `/pages`. Malheureusement, chaque `Client` peut pour le moment voir et modifier *tous* les objets `Page` de la base de données. Par exemple, l'URL `http://pete.sympalbuilder.com/backend.php/pages` génèrera une liste de *deux* objets `Page` issus des jeux de données de test : la page `location` de la boutique Pete's Pet Shop et le `menu` de la page `Menu` de City Pub.

Ce problème peut être corrigé en réutilisant la classe `acClientObjectRoute` créée précédemment pour le frontend. La classe `sfDoctrineRouteCollection` génère un groupe d'objets `sfDoctrineRoute` mais il s'agit ici de générer un groupe d'objets `acClientObjectRoute` à la place.

Pour y parvenir, il sera nécessaire d'avoir recours à une classe personnalisée de collection de routes. Il suffit alors de créer le nouveau fichier `acClientObjectRouteCollection.class.php` dans le répertoire `lib/routing` du projet avant de lui attribuer le contenu ci-dessous. On remarque au passage que ce code est particulièrement concis.

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    class acClientObjectRouteCollection extends sfObjectRouteCollection
    {
      protected
        $routeClass = 'acClientObjectRoute';
    }

La propriété `$routeClass` définit la classe utilisée pour identifier chaque route sous-jacente. C'est tout ! Chaque route sous-jacente est désormais un objet de type `acClientObjectRoute`. Ainsi, la page `http://pete.sympalbuilder.com/backend.php/pages` affichera uniquement *une seule* page : la page `location` du magasin animalier Pet's Pet Shop.

Grâce à cette nouvelle classe de route personnalisée, l'action `index` retourne uniquement les objets `Page` liés au `Client` correspondant, en se basant toujours sur le sous-domaine de la requête. Avec seulement quelques lignes de code, un module entier d'administration des objets `Page` a été créé, et ce celui-ci peut désormais être utilisé en toute sécurité par les multiples clients.

### La Pièce Manquante : Créer une Nouvelle Page

Pour le moment, le formulaire de création ou d'édition d'une page affiche une liste déroulante de tous les objets `Client` de la base de données. Pour des raisons évidentes de sécurité et d'ergonomie, il n'est pas question de laisser l'utilisateur choisir cette donnée.

Il s'agit donc à présent de découvrir comment affecter automatiquement l'objet `Client` à la page en cours en se basant toujours sur le sous-domaine de la requête. Pour ce faire, il suffit de mettre à jour l'objet `PageForm` situé dans le fichier `lib/form/PageForm.class.php`.

    [php]
    public function configure()
    {
      $this->useFields(array(
        'title',
        'content',
      ));
    }

La liste déroulante a été retirée de tous les formulaires `Page` comme cela était souhaité. Néanmoins, lorsque de nouveaux objets `Page` sont créés, le champ `client_id` reste quant à lui vide. Pour corriger ce défaut, il convient d'associer manuellement l'objet `Client` correspondant à l'objet `Page` dans les deux actions `new` et `create`.

    [php]
    public function executeNew(sfWebRequest $request)
    {
      $page = new Page();
      $page->Client = $this->getRoute()->getClient();
      $this->form = new PageForm($page);
    }

On remarque ici l'introduction d'une nouvelle méthode `getClient()` qui n'existe pas encore dans la classe `acClientObjectRoute`. Le code suivant présente l'implémentation de cette nouvelle méthode en réalisant seulement quelques modifications.

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      // ...

      protected $client = null;

      public function matchesUrl($url, $context = array())
      {
        // ...

        $this->client = $client;

        return array_merge(array('client_id' => $client->id), $parameters);
      }

      public function getClient()
      {
        return $this->client;
      }
    }

En ajoutant une propriété `$client` à la classe et en la définissant dans la méthode `matchesUrl()`, l'objet `Client` correspondant peut alors facilement être rendu accessible grâce à la route. La colonne `client_id` des nouveaux objets `Page` sera automatiquement et correctement définie d'après le sous-domaine de l'hôte courant.

Personnaliser la Collection de Routes d'Objet
---------------------------------------------

En utilisant le framework de routage, les problématiques soulevées par le cahier des charges de l'application Sympal Builder ont toutes été résolues. A mesure que l'application grandit, le développeur sera capable de réutiliser les routes personnalisées pour d'autres modules dans l'interface d'administration. Par exemple, chaque `Client` pourra gérer ses propres galeries de photos.

Une autre raison récurrente qui encourage la création d'une collection de routes personnalisée est l'ajout de routes fréquemment utilisées. On peut par exemple imaginer un projet qui emploie plusieurs modèles, et que chacun d'eux dispose d'une colonne `is_active` dans leur table respective. Dans l'interface d'administration, cela permettrait ainsi de définir une manière aisée pour activer la valeur de la colonne `is_active` pour n'importe quel objet. Il s'agit donc tout d'abord de modifier la classe `acClientObjectRouteCollection` et de lui indiquer d'ajouter une nouvelle route dans sa collection.

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    protected function generateRoutes()
    {
      parent::generateRoutes();

      if (isset($this->options['with_is_active']) && $this->options['with_is_active'])
      {
        $routeName = $this->options['name'].'_toggleActive';

        $this->routes[$routeName] = $this->getRouteForToggleActive();
      }
    }

La méthode ~`sfObjectRouteCollection::generateRoutes()`~ est appelée lorsque la classe de collection de routes est instanciée, et est aussi responsable de la création de toutes les routes nécessaires et de leur ajout dans le tableau de la propriété `$routes`. Dans ce cas, la création actuelle de la route est déléguée à une nouvelle méthode protégée appelée `getRouteForToggleActive()`.

    [php]
    protected function getRouteForToggleActive()
    {
      $url = sprintf(
        '%s/:%s/toggleActive.:sf_format',
        $this->options['prefix_path'],
        $this->options['column']
      );

      $params = array(
        'module' => $this->options['module'],
        'action' => 'toggleActive',
        'sf_format' => 'html'
      );

      $requirements = array('sf_method' => 'put');

      $options = array(
        'model' => $this->options['model'],
        'type' => 'object',
        'method' => $this->options['model_methods']['object']
      );

      return new $this->routeClass(
        $url,
        $params,
        $requirements,
        $options
      );
    }

La dernière étape restante consiste à configurer la collection de routes dans le fichier `routing.yml`. On remarque au passage que la méthode `generateRoutes()` cherche après une option intitulée `with_is_active` avant d'ajouter la nouvelle route. Ajouter cette logique à la classe donne davantage de contrôle au développeur dans le cas où il souhaiterait utiliser la classe `acClientObjectRouteCollection` autre part dans le futur, sans pour autant avoir besoin de la route `toggleActive`.

    [yml]
    # apps/frontend/config/routing.yml
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        model:          Page
        prefix_path:    /pages
        module:         pageAdmin
        with_is_active: true

L'exécution de la tâche `app:routes` permet de vérifier que la nouvelle route `toggleActive` est présente. La dernière pièce du puzzle manquante est la création de l'action qui s'occupera de cette fonctionnalité. Par ailleurs, si cette collection de routes est amenée à être réutilisée à travers d'autres modules, il suffit simplement de créer un nouveau fichier `backendActions.php` dans le répertoire `apps/backend/lib/actions` du projet. Le répertoire `actions` doit être créé manuellement.

    [php]
    # apps/backend/lib/action/backendActions.class.php
    class backendActions extends sfActions
    {
      public function executeToggleActive(sfWebRequest $request)
      {
        $obj = $this->getRoute()->getObject();

        $obj->is_active = !$obj->is_active;

        $obj->save();

        $this->redirect($this->getModuleName().'/index');
      }
    }

Enfin, il ne reste plus qu'à changer la classe de base de la classe `pageAdminActions` afin que cette dernière hérite de la nouvelle classe `backendActions`.

    [php]
    class pageAdminActions extends backendActions
    {
      // ...
    }

Quelles sont les étapes accomplies ? En ajoutant une route à la collection de routes ainsi qu'une action associée dans une classe d'actions de base ; n'importe quel module est désormais capable d'utiliser automatiquement cette fonctionnalité en utilisant une route `acClientObjectRouteCollection` et en étendant la classe `backendActions`. Par conséquent, une fonctionnalité commune peut être facilement mutualisée et capitalisée à travers plusieurs modules.

Options d'une Collection de Routes
----------------------------------

Les collections de routes d'objet contiennent une série d'options qui assurent une personnalisation pointue. En de nombreuses circonstances, un développeur peut ainsi utiliser ces options afin de configurer la collection, sans avoir besoin de créer une nouvelle classe personnalisée de collection de routes. Une liste détaillée des options des collections de routes est disponible dans [le Guide de Référence de symfony](http://www.symfony-project.org/reference/1_3/en/10-Routing#chapter_10_sfobjectroutecollection).

### Les Routes d'Actions

Chaque collection de routes d'objet accepte trois options différentes qui déterminent les routes exactes générées dans la collection. Sans pour autant entrer profondément dans le détail, la collection suivante génère les sept routes par défaut, auxquelles s'ajoutent une route de collection d'objets et une route d'objet.

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        actions:      [list, new, create, edit, update, delete, show]
        collection_actions:
          indexAlt:   [get]
        object_actions:
          toggle:     [put]

### Changer la Colonne Discriminante

Par défaut, le framework de routage de symfony utilise la clé primaire d'un modèle lorsqu'il s'agit d'interroger la base de données pour récupérer des objets. Cette information peut bien évidemment être modifiée facilement. Par exemple, le code ci-dessous s'appuiera sur la colonne `slug` pour récupérer un objet au lieu de sa clé primaire.

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        column: slug

### Modifier les Méthodes du Modèle

La route récupère par défaut tous les objets en relation pour une route de la collection et interroge la base de données sur la colonne (`column`) spécifiée lorsqu'il s'agit des routes d'objet. Une fois de plus, ce comportement peut être modifié et surchargé en ajoutant l'option `model_methods` à la route. Dans l'exemple ci-dessous, les méthodes `fetchAll()` et `findForRoute()` devront être définies dans la classe `PageTable`. Elles reçoivent toutes les deux un tableau de paramètres de l'objet de requête en guise d'argument.

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        model_methods:
          list:       fetchAll
          object:     findForRoute

### Modifier les Paramètres par Défaut

Enfin, il arrive parfois qu'il faille rendre un paramètre spécifique disponible dans l'objet de requête pour chaque route de la collection. Toutes les collections de route bénéficient d'une option supplémentaire `default_params` qui permet d'y parvenir facilement.

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        default_params:
          foo:   bar

Conclusion
----------

La principale responsabilité du framework de routage de symfony était à l'origine de faire correspondre URLs et de générer des URLs. Cependant, il a finalement très vite évolué vers un système entièrement personnalisable et capable de s'adapter à la plupart des besoins d'URLs complexes dans un projet. 

En prenant le contrôle sur les objets de route, la structure spéciale d'une URL peut ainsi être abstraite en dehors de la logique métier, et conservée entièrement à l'intérieur de la route à laquelle elle appartient. Il en résulte alors davantage de contrôle, de flexibilité et un code beaucoup plus maniable.