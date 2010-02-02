Etendre la Web Debug Toolbar
============================

*Par Ryan Weaver*

La barre de débogage web, `web debug toolbar` (`WDT`), de symfony regroupe des outils qui permettent de déboguer et d'améliorer les performances d'une application. Elle est constituée d'outils appelés *panneaux de débogage web*, *web debug panels*, chacun donnant des informations relatives au cache, à la configuration, aux fichiers de logs, à la consommation mémoire, au temps d'exécution ou bien encore concernant la version de symfony. Symfony 1.3 introduit deux nouveaux panneaux, un pour la `vue` et l'autre pour la gestion des `emails`.

![Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/web_debug_01.png "La Web Debug Toolbar de symfony 1.3")

Depuis symfony 1.2 il est possible de créer et d'ajouter ses propres *panneaux de débogage web*. Tout au long de ce chapitre, il s'agira de créer un nouvel *onglet de débogage* en étudiant par la même occasion les outils et les options qui permettent de le personnaliser. Il est de plus possible de se référer au plugin [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin) qui contient de nombreux panneaux supplémentaires s'appuyant sur les 
techniques expliquées dans la suite de ce chapitre.

Créer un Nouveau Panneau de Débogage Web
----------------------------------------

Les composants de la barre de débogage web sont appelés *web debug panels*, ou bien *panneaux de débogage web* pour les puristes francophones. Ils étendent la classe ~`sfWebDebugPanel`~. Ajouter un nouveau panel est particulièrement simple puisqu'il s'agit tout d'abord de créer un nouveau fichier  `sfWebDebugPanelDocumentation.class.php` dans le dossier `lib/debug/` du projet. Le répertoire `lib/debug` doit être créé manuellement.

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    class acWebDebugPanelDocumentation extends sfWebDebugPanel
    {
      public function getTitle()
      {
        return '<img src="/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
      }

      public function getPanelTitle()
      {
        return 'Documentation';
      }
      
      public function getPanelContent()
      {
        $content = 'Placeholder Panel Content';
        
        return $content;
      }
    }

Tous les panneaux doivent implémenter au minimum les méthodes `getTitle()`, `getPanelTitle()` et `getPanelContent()`.

 * ~`sfWebDebugPanel::getTitle()`~ définit l'apparence du panneau dans la 
 barre de débogage. Il s'agit généralement d'un nom court accompagné d'une 
 icône.

 * ~`sfWebDebugPanel::getPanelTitle()`~ définit le nom du panneau qui est 
 affiché dans le tag `h1` de l'onglet ouvert. Il sert aussi d'attribut `title` 
 au lien de la barre de débogage. Cette méthode ne doit pas retourner de code 
 HTML.

 * ~`sfWebDebugPanel::getPanelContent()`~ génère le code HTML affiché lorsque 
 l'onglet est ouvert.

Pour finir cette implémentation, il ne reste plus qu'à informer l'application que l'on souhaite inclure ce nouvel onglet à la barre de débogage. Pour ce faire, il est nécessaire d'ajouter un nouvel écouteur sur l'événement `debug.web.load_panels`. Cet événement est notifié lorsque la barre de débogage recherche ses panneaux. Il suffit donc de modifier le contenu du fichier `config/ProjectConfiguration.class.php` afin de lui faire implémenter cet écouteur.

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      // ...

      $this->dispatcher->connect('debug.web.load_panels', array(
        'acWebDebugPanelDocumentation',
        'listenToLoadDebugWebPanelEvent'
      ));
    }

L'étape suivante consiste à ajouter la méthode `listenToLoadDebugWebPanelEvent()` à la classe `acWebDebugPanelDocumentation.class.php` afin d'ajouter le nouvel onglet dans la base d'outils. Cette méthode sera appelée lorsque l'évènement `debug.web.load_panels` sera notifié.

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->setPanel(
        'documentation',
        new self($event->getSubject())
      );
    }

Voilà ! Il ne reste plus qu'à rafraîchir le navigateur et apprécier le résultat.

![Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/web_debug_02.png "La web debug toolbar et son nouveau panneau")

>**TIP**
>Depuis symfony 1.3, un paramètre `sfWebDebugPanel` peut être ajouté à l'URL 
>d'une page pour charger automatiquement un panneau de débogage. Si l'on ajoute 
>par exemple la chaîne `?sfWebDebugPanel=documentation` à la fin de l'URL, le 
>nouveau panneau sera ajouté à la page. C'est particulièrement utile lorsqu'il 
>s'agit de développer des panneaux personnalisés.

Les Trois Types de Panneaux de Débogage Web
-------------------------------------------

En réalité, il existe trois différents types de panneaux de débogage web. Les lignes qui suivent les décrivent les uns après les autres afin de mieux comprendre les intérêts de chacun.

### Le Type *Icon-Only*

Ce type de panneau se contente d'afficher une icône et du texte dans la barre d'outil. C'est typiquement le cas du panneau `memory` qui affiche la mémoire consommée sans aucun lien supplémentaire. Pour créer un panel de type *icon-only*, la méthode `getPanelContent()` doit retourner une chaine vide. La seule sortie de l'onglet provient en réalité de la méthode `getTitle()`.

    [php]
    public function getTitle()
    {
      $totalMemory = sprintf('%.1f', (memory_get_peak_usage(true) / 1024));

      return '<img src="'.$this->webDebug->getOption('image_root_path').'/memory.png" alt="Memory" /> '.$totalMemory.' KB';
    }

    public function getPanelContent()
    {
      return;
    }

### Le Type *Link*

Au même titre que le panneau de type *Icon-Only*, l'onglet de type *link* n'a pas de contenu, mais dispose cependant d'un lien supplémentaire. L'URL de ce lien est défini par la méthode `getTitleUrl()`. 

Pour créer un panneau de type *link*, la méthode `getPanelContent()` doit retourner une chaine vide tandis que la méthode `getTitleUrl()` doit être ajoutée à la classe comme le montre l'exemple ci-dessous.

    [php]
    public function getTitleUrl()
    {
      // link to an external uri
      return 'http://www.symfony-project.org/api/1_3/';

      // or link to a route in your application
      return url_for('homepage');
    }

    public function getPanelContent()
    {
      return;
    }

### Le Type *Content*

Enfin, le panneau de type *content* est le plus courant de tous puisqu'il s'agit d'un onglet qui affiche un bloc de code HTML lorsque l'on clique dessus dans la barre de débogage web.

Pour créer ce type de panneau de contenu, la méthode `getPanelContent()` doit retourner autre chose qu'une chaine vide.

Personnaliser le Contenu d'un Onglet
------------------------------------

Maintenant que le nouveau panneau de contrôle a été ajouté à la barre de débogage de symfony, il convient de lui attribuer du contenu à l'aide de la méthode `getPannelContent()`. Symfony fournit plusieurs méthodes capables de rendre ce contenu à la fois riche et ergonomique.

### La Méthode ~`sfWebDebugPanel::setStatus()`~

La couleur de fond des onglets est grise par défaut. Elle peut néanmoins être modifiée par de l'orange ou bien par du rouge lorsqu'il s'agit d'attirer l'attention du développeur sur un élément précis du panneau.

![Visualisation d'une erreur dans la Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/web_debug_05.png "Visualisation d'une erreur dans la Web Debug Toolbar")

Pour changer la couleur de fond du panel, la solution consiste à utiliser la méthode `setStatus()`. Cette méthode accepte toutes les constantes `priority` de la classe [sfLogger](http://www.symfony-project.org/api/1_3/sfLogger). 

Il existe en particulier trois niveaux qui correspondent respectivement aux trois couleurs de fond que peut prendre le panel (gris, orange et rouge). La méthode `setStatus()` est généralement appelée depuis la méthode  `getPanelContent()` si certains événements nécessitent d'attirer l'attention du développeur.

    [php]
    public function getPanelContent()
    {
      // ...

      // set the background to gray (the default)
      $this->setStatus(sfLogger::INFO);

      // set the background to orange
      $this->setStatus(sfLogger::WARNING);

      // set the background to red
      $this->setStatus(sfLogger::ERR);
    }

### La Méthode ~`sfWebDebugPanel::getToggler()`~

Il existe une fonction fréquemment rencontrée dans les panneaux de contrôle. Il s'agit du `toggler`. C'est une croix qui affiche ou cache alternativement du contenu lorsque l'on clique dessus. On pourrait généraliser son comportement à celui d'un interrupteur domestique qui allume ou éteint une ampoule par exemple.

![Web Debug Toggler](http://www.symfony-project.org/images/more-with-symfony/web_debug_03.png "Le web debug toggler en action")

Le `toggler` peut être utilisé par le panneau de contrôle en invoquant la méthode `getToggler()`. Le code ci-dessous explique comment appliquer le `toggler` sur une liste d'éléments contenus dans l'onglet de contrôle.

    [php]
    public function getPanelContent()
    {
      $listContent = '<ul id="debug_documentation_list" style="display: none;">
        <li>List Item 1</li>
        <li>List Item 2</li>
      </ul>';

      $toggler = $this->getToggler('debug_documentation_list', 'Toggle list');

      return sprintf('<h3>List Items %s</h3>%s',  $toggler, $listContent);
    }

Cette méthode `getToggler()` accepte deux arguments : l'identifiant DOM, `id`, de l'élément sur lequel doit être appliqué le `toggler`, et une chaîne pour l'attribut `title` du lien du `toggler`.

### La Méthode ~`sfWebDebugPanel::getToggleableDebugStack()`~

Au même titre que la méthode `getToggler()`, la méthode  `getToggleableDebugStack()` crée une flèche cliquable qui affiche ou masque un élément du contenu. Cette méthode génère le contenu HTML d'une trace de débogage d'une pile d'appels de fonctions.

Cette fonction est particulièrement utile lorsqu'il s'agit d'afficher les logs d'une des classes du projet. Par exemple, si une classe `myCustomClass` a besoin d'enregistrer des informations dans les logs, alors ces derniers seraient créés de la manière suivante depuis l'intérieur de cette classe.

    [php]
    class myCustomClass
    {
      public function doSomething()
      {
        $dispatcher = sfApplicationConfiguration::getActive()
          ->getEventDispatcher();

        $dispatcher->notify(new sfEvent($this, 'application.log', array(
          'priority' => sfLogger::INFO,
          'Beginning execution of myCustomClass::doSomething()',
        )));
      }
    }

Pour l'exemple de ce chapitre, il s'agit d'afficher une liste des logs de la classe `myCustomClass` en accompagnant chacun d'eux par sa propre trace de débogage de la pile d'appels.

    [php]
    public function getPanelContent()
    {
      // retrieves all of the log messages for the current request
      $logs = $this->webDebug->getLogger()->getLogs();

      $logList = '';
      foreach ($logs as $log)
      {
        if ($log['type'] == 'myCustomClass')
        {
          $logList .= sprintf('<li>%s %s</li>',
            $log['message'],
            $this->getToggleableDebugStack($log['debug_backtrace'])
          );
        }
      }

      return sprintf('<ul>%s</ul>', $logList);
    }

![Web Debug Toggleable Debug](http://www.symfony-project.org/images/more-with-symfony/web_debug_04.png "La debug stack trace avec le toggler")

>**NOTE**
>Les logs de la classe `myCustomClass` se trouvent toujours dans le panneau 
>`Logs`. Utiliser un onglet personnalisé permet ainsi de les isoler et de les 
>présenter autrement.

### La Méthode ~`sfWebDebugPanel::formatFileLink()`~

Depuis symfony 1.3, il est possible d'ouvrir un fichier dans son éditeur favoris depuis la barre de débogage web. Davantage d'informations concernant cette fonctionnalité sont disponibles sur Internet, il suffit pour cela de se référer à l'article ["What's new"](http://www.symfony-project.org/tutorial/1_3/en/whats-new) de symfony 1.3.

Pour bénéficier de cette fonctionnalité, il faut impérativement faire appel à la méthode `formatFileLink()`. En plus du nom du fichier à ouvrir, cette méthode peut aussi recevoir comme second paramètre le numéro de la ligne incriminée à atteindre. L'exemple suivant montre comment atteindre la ligne 15 du fichier `config/ProjectConfiguration.class.php`:

    [php]
    public function getPanelContent()
    {
      $content = '';

      // ...

      $path = sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php';
      $content .= $this->formatFileLink($path, 15, 'Project Configuration');

      return $content;
    }

Les deuxième et troisième arguments, sont respectivement le numéro de la ligne  et le nom du lien, et sont également optionnels. Si le nom du lien n'est pas renseigné, c'est le chemin du fichier ciblé qui sera utilisé à la place.

>**NOTE**
>Avant de tester, il convient de s'assurer de bien avoir configuré la nouvelle 
>fonctionnalité de lien de fichier. Cela peut être réalisé à l'aide de l'option 
>`sf_file_link_format` dans le fichier `settings.yml` ou bien avec l'option 
>`file_link_format` de 
>[xdebug](http://xdebug.org/docs/stack_trace#file_link_format). Cette dernière 
>méthode permet aussi à votre projet de ne pas dépendre d'un IDE.

Autres Informations à Connaître au Sujet de la WDT
--------------------------------------------------

L'intérêt de la barre de débogage réside dans les informations qu'elle affiche. Il s'agit maintenant d'étudier les autres possibilités offertes par la barre de débogage web de symfony.

### Enlever les Onglets par Défaut

La barre de débogage charge par défaut plusieurs panneaux qui peuvent être enlevés en utilisant l'événement `debug.web.load_panels`. Il s'agit ici de faire appel à la même méthode écouteur déclarée plus haut, en remplaçant son contenu par la méthode `removePanel()`. Le code suivant supprime l'onglet `memory` de la barre de débogage web.

    [php]
    static public function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->removePanel('memory');
    }

### Accéder aux Paramètres de la Requête depuis un Onglet

Les paramètres de la requête sont souvent nécessaires dans les onglets. Il convient donc maintenant d'afficher des informations concernant un objet `Event` provenant de la base de données à partir d'un paramètre de requête `event_id`.

    [php]
    $parameters = $this->webDebug->getOption('request_parameters');
    if (isset($parameters['event_id']))
    {
      $event = Doctrine::getTable('Event')->find($parameters['event_id']);
    }

### Masquer le Panneau sous Certaines Conditions

Parfois, l'onglet ne dispose pas d'informations utiles à afficher. Dans ce cas, il est préférable de le masquer. Pour mieux comprendre comment mettre cela en place, le code suivant s'appuiera sur l'exemple précédent. Il s'agit de masquer le panneau de contrôle si aucun paramètre `event_id` n'a été transmis dans la requête. Pour ce faire, la méthode `getTitle()` doit retourner une chaine vide.

    [php]
    public function getTitle()
    {
      $parameters = $this->webDebug->getOption('request_parameters');

      if (!isset($parameters['event_id']))
      {
        return;
      }

      return '<img src="/acWebDebugPlugin/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
    }

Conclusion
----------

La barre de débogage de symfony est là pour faciliter la vie du développeur. Elle est aussi bien plus qu'un simple panneau d'informations passif puisqu'en lui ajoutant des onglets personnalisés, les fonctionnalités de la barre d'outils ne seront limitées que par l'imagination du développeur.

Le plugin [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin) ne contient qu'un aperçu des panneaux pouvant être créés, alors n'hésitez pas à ajouter les vôtres également.