Manipuler le Cache de Configuration de symfony
==============================================

*Par Kris Wallsmith*

Un de mes principaux intérêts en tant que développeur symfony est de suivre le travail de la communauté autant que possible quel que soit le type de projet. Bien que je connaisse sur le bout des doigts le code source interne de symfony, ce n'est pas nécessairement une obligation pour tous les développeurs. 

Heureusement, symfony fournit des solutions capables d'isoler chaque composant d'une application, permettant ainsi à n'importe qui d'effectuer des modifications sans difficulté.

Les Chaînes de Caractères dans les Formulaires
----------------------------------------------

Un excellent exemple pour illustrer ces propos est le framework de formulaires. Le framework de formulaires est un puissant outil de symfony qui transforme le rendu et la validation d'un formulaire en objets PHP, dans le but de donner aux développeurs davantage de contrôle sur leur gestion. Le travail des développeurs en est ainsi largement simplifié.

En effet, ces derniers peuvent ainsi encapsuler une logique complexe dans une seule classe de formulaire, étendre cette dernière et la réutiliser à différents endroits du code.

Cependant, pour un intégrateur, cette abstraction de rendu du formulaire peut  être quelque peu troublante. Il suffit d'étudier l'implémentation du formulaire suivant pour s'en convaincre.

![Etat par défaut d'un formulaire](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_default.png)

La classe qui configure ce formulaire est de la forme suivante :

    [php]
    // lib/form/CommentForm.class.php
    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
      }
    }

Le formulaire est ensuite rendu grâce au template PHP suivant :

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

L'intégrateur a certes la possibilité de modifier le rendu du formulaire. Il peut par exemple modifier les intitulés par défaut.

    <?php echo $form['body']->renderLabel('Please enter your comment') ?>

Une classe CSS peut également être ajoutée lors du rendu des champs.

    <?php echo $form['body']->render(array('class' => 'comment')) ?>

Ces modifications sont intuitives et faciles. Mais qu'en est-il s'il doit 
modifier des messages d'erreur ?

![Gestion des erreurs par défaut dans les formulaires](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_error.png)

La méthode ~`renderError()`~ n'accepte aucun argument. La seule solution actuelle pour l'intégrateur consiste à ouvrir la classe relative au formulaire, puis de trouver la méthode correspondant à la validation afin d'en modifier les paramètres. Dans l'exemple précédent, les modifications suivantes seraient nécessaires.

    [php]
    // before
    $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));

    // after
    $this->setValidator('body', new sfValidatorString(array('min_length' => 12), array(
      'min_length' => 'You haven't written enough',
    )));

Où est l'intrus ? Ici c'est une apostrophe dans une chaine de caractères entourée de guillemets simples qui a été utilisée. Un développeur avisé ne ferait jamais une pareille erreur, mais qu'en est-il d'un intégrateur qui doit plonger dans une classe de formulaire ? Il ne le fera pas.

La question qui se pose alors est la suivante. Faut-il sérieusement attendre d'un intégrateur de connaître suffisamment bien le framework de formulaires au point de trouver à quel endroit se définit un message d'erreur ? Est-ce que quelqu'un d'habitué à modifier des templates doit connaître la signature d'un constructeur de validateur ?

La réponse à ces questions est clairement non. Les intégrateurs réalisent déjà beaucoup de travail et il serait complètement déraisonnable de penser que quelqu'un qui n'a pas l'habitude d'écrire du code puisse apprendre les rouages du framework de formulaires de symfony.

Une Solution : le YAML
----------------------

Afin de simplifier l'édition de ces chaines de caractères, une couche de  configuration en YAML sera développée dans le but d'améliorer chaque objet de formulaire passé à la vue. Le fichier de configuration prendra la forme suivante :

    [yml]
    # config/forms.yml
    CommentForm:
      body:
        label:        Please enter your comment
        attributes:   { class: comment }
        errors:
          min_length: You haven't written enough

C'est tout de même beaucoup plus simple. La configuration parle d'elle même, et résout le problème précédent de guillemets. Il s'agit maintenant d'écrire le code nécessaire à l'intégration de ce YAML.

Filtrer les Variables de Template
---------------------------------

La première difficulté consiste à trouver un hameçon dans symfony qui permette de filtrer chaque variable de formulaire passée au template par le fichier de configuration. La solution consiste à utiliser l'événement `template.filter_parameters` qui est appelé par le coeur de symfony juste avant
de rendre un template ou un partiel.

    [php]
    // lib/form/sfFormYamlEnhancer.class.php
    class sfFormYamlEnhancer
    {
      public function connect(sfEventDispatcher $dispatcher)
      {
        $dispatcher->connect('template.filter_parameters', array($this, 'filterParameters'));
      }

      public function filterParameters(sfEvent $event, $parameters)
      {
        foreach ($parameters as $name => $parameter)
        {
          if ($parameter instanceof sfForm && !$parameter->getOption('is_enhanced'))
          {
            $this->enhance($parameter);
            $parameter->setOption('is_enhanced', true);
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
>Ce code vérifie si une option `is_enhanced` existe pour chaque objet de >formulaire avant de le modifier. Ceci afin d'éviter que les formulaires qui 
>sont chargés depuis un partiel soient modifiés deux fois.

Cette classe doit maintenant être chargée depuis le fichier de configuration de l'application.

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

Désormais les variables de formulaire peuvent être isolées juste avant d'être passées au template ou au partiel. Tous les outils nécessaires à ce fonctionnement sont en plus disponibles. La dernière étape consiste enfin à appliquer ce qui a été configuré dans le YAML.

Charger le YAML
---------------

La manière la plus simple d'appliquer le YAML à chaque formulaire est de le 
charger dans un tableau et d'itérer dessus pour chaque configuration.

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
                foreach ($fieldConfig['errors'] as $errorCode => $errorMessage)
                {
                  $form->getValidator($fieldName)->setMessage($errorCode, $errorMessage);
                }
              }
            }
          }
        }
      }
    }

Cependant, cette implémentation a de nombreux défauts. Tout d'abord, le YAML est lu depuis le système de fichiers et chargé dans l'objet `sfYaml` à chaque appel. Lire depuis le système de fichiers de cette manière doit être évité pour des raisons évidentes de performances.

Ensuite, il existe plusieurs niveaux de boucles imbriquées et beaucoup trop de conditions qui ralentissent inutilement l'exécution de l'application. La solution pour résoudre ces soucis réside dans la gestion du cache de configuration de symfony.

Le Cache de Configuration
-------------------------

Derrière le cache de configuration se trouve une collection de classes qui optimisent l'utilisation du YAML en le transformant en code PHP et en le stockant dans le dossier de cache avant exécution. Ce mécanisme élimine la nécessité de charger le contenu de la configuration dans `sfYaml` avant de pouvoir en utiliser les valeurs.

L'étape suivante consiste à implémenter ce système pour la classe de formulaire. Au lieu de charger le fichier `forms.yml` dans `sfYaml`, il s'agit de demander, au système de configuration une version pré-chargée en objet PHP. Pour ce faire, la classe `sfFormYamlEnhancer` aura besoin d'accéder au cache de configuration, et c'est pour cette raison que cet objet sera passé dans le constructeur.

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml', 'sfSimpleYamlConfigHandler');
      }

      // ...
    }

Le cache de configuration a besoin de savoir ce qu'il doit faire lorsqu'un fichier de configuration est appelé par l'application. Pour l'instant, il utilise la classe `sfSimpleYamlConfigHandler` pour charger le fichier `forms.yml`. Le YAML est donc analysé puis transformé en un tableau PHP, juste avant d'être mis en cache. A ce stade, la configuration est en place et prête à être chargée. Elle peut être appelée de la manière suivante à la place de `sfYaml`.

    [php]
    public function enhance(sfForm $form)
    {
      $config = include $this->configCache->checkConfig('config/forms.yml');

      // ...
    }

C'est déjà beaucoup mieux. Non seulement la contrainte de devoir analyser le YAML à chaque requête a été éliminée, mais le code fait usage de la fonction native `include()` de PHP qui favorise la mise en cache du code.

>**SIDEBAR**
>Développement vs. Environnement de production
>
>L'utilisation de ~`checkConfig()`~ diffère selon que le mode debug est 
>activé ou pas. Dans l'environnement de production, quand le mode debug est 
>désactivé, cette méthode fonctionne comme décrit ci-dessous :
>
>  * Vérification de l'existence d'un fichier caché pour le fichier demandé
>    * S'il existe, retourner le chemin du fichier caché
>    * S'il n'existe pas :
>      * Convertir le fichier de configuration ;
>      * Sauvegarder le code résultant dans le cache ;
>      * Retourner le chemin du nouveau fichier caché.
>
>Cette méthode fonctionne différemment lorsque le mode debug est activé. Les 
>fichiers de configuration étant modifiés au cours du développement, la méthode
>`checkConfig()` compare les fichiers originaux et ceux mis en cache pour 
>s'assurer d'avoir la dernière version. Ce processus inclut quelques 
>vérifications :
>
>  * Vérification d'une version cachée du fichier demandé
>    * Si elle n'existe pas :
>      * Traiter le fichier de configuration ;
>      * Sauvegarder le code résultant dans le cache.
>    * Si elle existe :
>      * Comparer les dernières modifications de la configuration et des fichiers cachés ;
>      * Si le fichier de configuration a été modifié récemment :
>        * Traiter le fichier de configuration ;
>        * Sauvegarder le code résultant dans le cache.
>  * Retourner le chemin du fichier caché

Intégration dans le Code par les Tests
--------------------------------------

Avant d'aller plus loin dans les développements, il convient d'écrire quelques tests unitaires pour valider le fonctionnement de la classe `sfFormYamlEnhancer`.

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

    $t->like($form['body']->renderLabel(), '/Please enter your comment/', '->enhance() enhances labels');
    $t->like($form['body']->render(), '/class="comment"/', '->enhance() enhances widgets');
    $t->like($form['body']->renderError(), '/You haven\'t written enough/', '->enhance() enhances error messages');

L'exécution de cette suite de tests sur la version actuelle de la classe `sfFormYamlEnhancer` réussit et valide la conformité du code.

![Suite de tests positive](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_3_ok.png)

Le code est désormais prêt à être modifié. Les tests unitaires avertiront le développeur si la moindre pièce est cassée dans le code.

Les Gestionnaires de Configuration Personnalisés
------------------------------------------------

Dans le code ci-dessous, chaque variable de formulaire passée au template itèrera sur chaque classe de formulaire configurée dans le fichier `forms.yml`. Cette méthode fonctionne mais lorsqu'il s'agit de passer plusieurs objets de formulaire au template, ou bien une longue liste de formulaires configurés en  YAML, un impact sur les performances de l'application se fera ressentir. C'est donc une excellente opportunité pour écrire un gestionnaire de configuration personnalisé qui optimisera ces performances.

>**SIDEBAR**
>Pourquoi personnaliser ?
>
>Ecrire un gestionnaire de configuration personnalisé n'est pas des plus aisés. 
>Tous les développeurs sont sujets à faire des erreurs et la testabilité de ces 
>objets n'est pas chose facile non plus. Néanmoins, les bénéfices en seront 
>substantiels. L'avantage de créer une logique personnalisée permet de 
>bénéficier de la flexibilité du YAML et de la faible surcharge
>du code PHP natif. En ajoutant un cache d'opcodes (tel que 
>[APC](http://pecl.php.net/apc) ou [XCache](http://xcache.lighttpd.net/) à tout 
>cela, le gestionnaire de configuration sera difficile à battre en termes de 
>facilité d'utilisation et de performances.

L'essentiel de la magie de ces gestionnaires se passe en coulisses. Toute la logique est mise en cache avant d'exécuter n'importe quel gestionnaire de configuration. Par conséquent, le développeur a tout le loisir de se concentrer sur l'écriture du code nécessaire à la mise en oeuvre de la configuration YAML de l'application.

Chaque gestionnaire doit implémenter les deux méthodes suivantes :

 * `static public function getConfiguration(array $configFiles)`
 * `public function execute($configFiles)`

La première méthode statique `getConfiguration()` reçoit comme paramètre un tableau contenant le chemin des fichiers. Elle se charge ensuite de les analyser et de regrouper leur contenu en une seule valeur. Dans la classe `sfSimpleYamlConfigHandler` utilisée précédemment, cette méthode contient seulement une ligne.

    [php]
    static public function getConfiguration(array $configFiles)
    {
      return self::parseYamls($configFiles);
    }

La classe `sfSimpleYamlConfigHandler` étend `sfYamlConfigHandler` qui inclut un certain nombre de méthodes servant au traitement du fichier de configuration YAML :

 * `::parseYamls($configFiles)`
 * `::parseYaml($configFile)`
 * `::flattenConfiguration($config)`
 * `::flattenConfigurationWithEnvironment($config)`

Les deux premières méthodes implémentent le principe de  
[configuration en cascade de symfony](http://www.symfony-project.org/reference/1_2/fr/03-Configuration-Files-Principles#chapter_03_configuration_en_cascade). Les deux suivantes implémentent la [sensibilisation à l'environnement](http://www.symfony-project.org/reference/1_2/fr/03-Configuration-Files-Principles#chapter_03_sensibilisation_a_l_environnement).

La méthode statique `getConfiguration()` du gestionnaire aura besoin d'une méthode personnalisée afin de regrouper les configurations des classes dont elle hérite. Par conséquent, il convient d'écrire une méthode `applyInheritance()` qui appliquera cette logique.

    [php]
    // lib/config/sfFormYamlEnhancementsConfigHander.class.php
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $config = self::getConfiguration($configFiles);

        // compile data
        $retval = "<?php\n".
                  "// auto-generated by %s\n".
                  "// date: %s\nreturn %s;\n";
        $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'), var_export($config, true));

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
              $merged[$class] = sfToolkit::arrayDeepMerge($config[$parent], $merged[$class]);
            }
          }
        }

        return $merged;
      }
    }

A présent, on dispose d'un tableau dont les valeurs ont été rassemblées en fonction de la classe héritée. Le besoin de devoir analyser la configuration en entier a été éliminée via un appel à `instanceof` pour vérifier le type de chaque objet.

De plus, cette opération est effectuée dans le gestionnaire de configuration et  ne sera donc exécutée qu'une fois avant la mise en cache. Ce tableau peut ainsi être passé à l'objet de formulaire de la sorte.

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml', 'sfFormYamlEnhancementsConfigHander');
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
        else if ($overlap = array_intersect(class_parents($class), array_keys($config)))
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

Avant de relancer la suite de tests unitaires, il convient d'ajouter quelques lignes pour la nouvelle logique de classe.

    [yml]
    # config/forms.yml

    # ...

    BaseForm:
      body:
        errors:
          min_length: A base min_length message
          required:   A base required message

Il s'agit ici de vérifier que le nouveau message `required` est appliqué dans le test, et de confirmer que les enfants du formulaire recevront les améliorations de la classe parente.

    [php]
    $t = new lime_test(5);

    // ...

    $form = new CommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderError(), '/A base required message/', '->enhance() considers inheritance');

    class SpecialCommentForm extends CommentForm { }
    $form = new SpecialCommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderLabel(), '/Please enter your comment/', '->enhance() applies parent config');

L'exécution de la nouvelle mise à jour du test confirme que les modifications apportées au formulaire fonctionnent comme prévu.

![Résultat positif d'exécution de la suite de tests](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_5_ok.png)

Jouer avec les Formulaires Imbriqués
------------------------------------

Il existe une fonctionnalité importante dans le framework de formulaires de symfony qui n'a pas encore été discutée jusqu'ici : les formulaires imbriqués. Si une instance de `CommentForm` est imbriquée dans un autre formulaire, les améliorations apportées dans le fichier `forms.yml` ne fonctionneront plus. Un simple test unitaire suffit pour le démontrer.

    [php]
    $t = new lime_test(6);

    // ...

    $form = new BaseForm();
    $form->embedForm('comment', new CommentForm());
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['comment']['body']->renderLabel(), '/Please enter your comment/', '->enhance() enhances embedded forms');

Ces quelques lignes prouvent que les formulaires imbriqués ne sont pas gérés.

![Echec d'exécution de la suite de tests unitaires](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_not_ok.png)

Pour que le test fonctionne de nouveau, il faut développer une version plus avancée du gestionnaire de configuration. Il convient de trouver une solution pour implémenter les spécifications configurées dans le fichier `forms.yml` d'une manière plus modulaire afin de prendre en compte les formulaires imbriqués.

Pour ce faire, une version personnalisée doit être écrite pour chaque méthode de chaque classe. Ces méthodes seront générées par le gestionnaire de configuration personnalisé dans une nouvelle classe métier.

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
            $code[] = sprintf('  $fields[%s]->getWidget()->setLabel(%s);', var_export($config['label'], true));
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
            $code[] = sprintf('  if ($error = $fields[%s]->getError())', var_export($field, true));
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

Il est important de remarquer ici que le tableau de configuration est vérifié pour certaines clés lors de la génération du code plutôt qu'à l'exécution afin de bénéficier d'un léger gain de performances.

>**TIP**
>De manière générale, la logique qui vérifie les conditions de la configuration 
>devrait être exécutée dans le gestionnaire de configuration, et non dans le 
>code généré. La logique qui vérifie les conditions d'exécution, comme la nature 
>de l'objet de formulaire, doit être appelée au moment de l'exécution du code.

Le code généré est ensuite placé dans une définition de classe sauvegardée dans le répertoire de cache.

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

La classe `sfFormYamlEnhancer` reportera la classe métier générée afin de gérer le traitement des objets de formulaire, mais elle doit maintenant prendre en compte la récursivité des formulaires imbriqués.

Pour ce faire, il s'agit de traiter le schéma des champs de formulaire (sur lequel on peut itérer récursivement) et les objets de formulaire (y compris les formulaires imbriqués) en parallèle.

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
        else if ($overlap = array_intersect(class_parents($class), sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.current($overlap));
        }
      }
    }

>**NOTE**
>Une fois imbriqués, les champs d'un objet de formulaire ne devraient pas être 
>modifiés. Les formulaires imbriqués sont déclarés dans le formulaire parent 
>afin de faciliter le traitement, mais ils n'ont pas d'incidence sur le rendu 
>de ce dernier.

A ce stade les formulaires imbriqués sont enfin gérés et les tests devraient 
s'exécuter sans aucun souci comme le montre la capture d'écran ci-dessous.

![Résultat positif d'exécution de la suite de tests](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_ok.png)

Qu'en est-il des Performances ?
-------------------------------

Afin de s'assurer que tout le temps passé jusqu'à présent n'a pas été dépensé inutilement, une suite de tests de performance peut être exécutée. Quelques classes supplémentaires peuvent être ajoutées au fichier `forms.yml` grâce à une boucle PHP afin de rendre les résultats plus intéressants.

    [yml]
    # <?php for ($i = 0; $i < 100; $i++): ?> #
    Form<?php echo $i ?>: ~
    # <?php endfor; ?> #

C'est le morceau de code ci-dessous qui a pour rôle de générer toutes ces classes.

    [php]
    mkdir($dir = sfConfig::get('sf_lib_dir').'/form/test_fixtures');
    for ($i = 0; $i < 100; $i++)
    {
      file_put_contents($dir.'/Form'.$i.'.class.php', '<?php class Form'.$i.' extends BaseForm { }');
    }

Le benchmark est enfin prêt à être exécuter. Pour obtenir les résultats ci-dessous, la commande [Apache](http://httpd.apache.org/docs/2.0/programs/ab.html) suivante a été exécutée sur un Macbook à plusieurs reprises jusqu'à obtenir un écart standard de moins de 2 ms. 

    $ ab -t 60 -n 20 http://localhost/config_cache/web/index.php

Le premier benchmark de base ci-dessous exécute l'application par défaut sans les améliorations apportées. Il convient tout d'abord de commenter l'appel de `sfFormYamlEnhancer` dans le fichier `frontendConfiguration`, puis de relancer le test.

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.5     63      69
    Waiting:       62   63   1.5     63      69
    Total:         62   63   1.5     63      69

A présent, il s'agit de copier la première version de `sfFormYamlEnhancer::enhance()` qui appelait directement `sfYaml` avant de relancer l'exécution du benchmark.

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    87   88   1.6     88      93
    Waiting:       87   88   1.6     88      93
    Total:         87   88   1.7     88      94

Ces tests montrent un ralentissement de 25 ms en moyenne à chaque requête, soit une augmentation du temps d'exécution de près de 40%. Maintenant, il s'agit de modifier ces changements afin d'appeler la méthode `enhance()` pour que le 
gestionnaire de configuration personnalisé soit appelé.

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.6     63      70
    Waiting:       62   63   1.6     63      70
    Total:         62   64   1.6     63      70

On constate ici que le temps de traitement par défaut a été restauré en utilisant le gestionnaire de configuration par défaut.

Bonus : Embarquer le Gestionnaire de Configuration dans un Plugin
-----------------------------------------------------------------

Maintenant que cet excellent système d'amélioration des objets de formulaire via une configuration YAML est développé, pourquoi ne pas l'embarquer dans un plugin, puis le partager avec la communauté.

Cela peut paraître intimidant pour ceux qui n'ont jamais publié de plugin mais les quelques lignes qui suivent dissiperont ces craintes. Le plugin aura la structure suivante.

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

Quelques modifications sont nécessaires afin de faciliter le processus d'installation du plugin. La création et la connexion de l'objet optimisé doivent être encapsulées dans la classe de configuration du plugin.

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

Le script de test doit aussi être mis à jour afin de prendre en compte le chemin relatif vers le script d'amorçage du projet.

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    // ...

Enfin, le plugin doit être activé dans la classe `ProjectConfiguration`.

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfFormYamlEnhancementsPlugin');
      }
    }

Pour exécuter les tests depuis le plugin, il suffit de connecter ces derniers depuis la classe de configuration `ProjectConfiguration`.

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function setupPlugins()
      {
        $this->pluginConfigurations['sfFormYamlEnhancementsPlugin']->connectTests();
      }
    }

Les tests doivent maintenant s'exécuter correctement lorsqu'ils sont appelés à l'aide des commandes `test:*`.

![Exécution des tests des plugins](http://www.symfony-project.org/images/more-with-symfony/config_cache_plugin_tests.png)

Toutes les classes sont maintenant rangées dans la structure du plugin bien qu'un autre problème subsiste. Le script de test cherche toujours ces fichiers au niveau de l'arborescence du projet. Il faut donc isoler le code dans la classe spécialisée qui appelle la configuration du cache afin de surcharger la méthode dans le script de tests, et utiliser le fichier `forms.yml`.

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

La méthode `loadWorker()` peut alors être surchargée afin d'appeler le gestionnaire de configuration personnalisé. La classe `CommentForm` doit aussi être déplacée dans le script de test et le fichier `forms.yml` dans la structure `test/fixtures` du plugin.

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

Enfin, la création du package du plugin est facilitée grâce au plugin  `sfTaskExtraPlugin` qui délivre une tâche `plugin:package`. Après exécution de cette dernière et quelques questions posées dans la console, le plugin sera enfin prêt.

    $ php symfony plugin:package sfFormYamlEnhancementsPlugin

>**NOTE**
>Le code de cet article a été publié dans un plugin, et est disponible en 
>téléchargement sur le site de symfony :
>
>    http://symfony-project.org/plugins/sfFormYamlEnhancementsPlugin
>
>Ce plugin inclut tout ce qui a été abordé dans ce chapitre et bien plus encore. 
>Il fournit un support pour les fichiers `widgets.yml` et `validators.yml` ainsi 
>qu'une intégration avec la tâche `i18n:extract` afin de fournir une 
>internationalisation plus aisée des formulaires.

Conclusion
----------

Ce chapitre a permis de se rendre compte, grâce aux benchmarks exécutés, que la gestion du cache de configuration de symfony rend possible l'utilisation de fichiers YAML tout en préservant un impact limité sur les performances.