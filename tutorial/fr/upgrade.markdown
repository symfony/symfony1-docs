Mise à Jour de Projets 1.2 vers 1.3/1.4
=======================================

Ce document décrit les changements réalisés dans symfony 1.3/1.4 et ce que vous avez besoin
pour accomplir la mise à jour vos projets symfony 1.2.

Si vous souhaitez plus d'informations concernant les ajouts et changements de symfony 1.3/1.4,
vous pouvez consulter le tutoriel [What's new?](http://www.symfony-project.org/tutorial/1_4/fr/whats-new).

>**CAUTION**
>symfony 1.3/1.4 est compatible avec PHP 5.2.4 ou ultérieurs.
>Il devrait aussi fonctionner pour des versions comprises entre PHP 5.2.0 et 5.2.3 mais ce n'est pas garanti.

Mise à niveau en symfony 1.4
----------------------------

Il n'y a aucune tâche de mise à jour dans symfony 1.4 car cette version est la même que symfony
1.3 (moins toutes les caractéristiques dépréciées). Pour mettre à jour en 1.4, vous devez d'abord
mettre à jour en 1.3 et changer ensuite vers la version 1.4.

Avant de passer en 1.4, vous pouvez également vérifier que votre projet n'utilise pas
de classe, de méthode, de fonction, de paramètres ou d'autres choses de dépréciés, en exécutant la
tâche `project:validate` :

    $ php symfony project:validate 

La tâche liste tous les fichiers que vous devez modifier avant de passer à symfony
1.4.

Soyez conscient que la tâche est une simple expression régulière et elle peut vous donner
beaucoup d'informations erronées. Aussi, elle ne peut pas tout détecter, c'est juste un outil
qui vous aide en identifiant des problèmes possibles, ce n'est pas un outil magique. Vous devez toujours
lire le tutoriel DEPRECATED soigneusement.

>**NOTE**
>`sfCompat10Plugin` et `sfProtoculousPlugin` ont été enlevés de la 1.4. Si
>vous les avez explicitement désactivées dans les fichiers de classe de configuration de votre projet,
>comme `config/ProjectConfiguration.class.php`, vous devez enlever toute mention de ces derniers
>dans ces fichiers.

Comment mettre à jour en symfony 1.3 ?
--------------------------------------

Pour mettre à jour un projet:

  * Vérifiez que tous les plugins utilisés par votre projet sont compatible
    avec symfony 1.3.

  * Si vous n'utilisez pas d'outil de SCM, veillez à faire une sauvegarde complète de votre projet.

  * Démarrez le processus de mise à jour vers symfony 1.3

  * Actualisez vos plugins vers leur version 1.3

  * Lancez la tâche `project:upgrade1.3` depuis le répertoire de votre projet 
    pour réaliser une mise à jour automatique :

        $ php symfony project:upgrade1.3

    Cette tâche peut être exécutée plusieurs fois sans risque d'effets de bord.
    Elle doit être exécutée chaque fois qu'une mise à jour vers une nouvelle
    version de symfony 1.3 beta / RC ou bien finale est réalisé.

  * Cette mise à jour implique de reconstruire vos classes de modèles et de formulaires
    suite à quelques changements décrits plus bas :

        # Doctrine
        $ php symfony doctrine:build --all-classes

        # Propel
        $ php symfony propel:build --all-classes

  * Videz le cache de Symfony :

        $ php symfony cache:clear

Les prochaines sections expliquent les principaux changements réalisés dans symfony 1.3 
qui nécessitent une mise à jour automatique ou manuelle.

Composants obsolètes
--------------------

Au cours du développement de symfony 1.3, nous avons rendu obsolètes et supprimés
quelques paramètres de configurations, des classes, des méthodes, des fonctions et tâches. Nous vous invitons à
vous référer au document [Composants obsolètes en 1.3](http://www.symfony-project.org/tutorial/1_3/fr/deprecated)
pour plus d'informations.

Autochargement des classes
--------------------------

Depuis symfony 1.3, les fichiers se trouvant sous le répertoire `lib/vendor/`
ne sont plus chargés automatiquement par défaut. Si vous souhaitez charger automatiquement
certains sous-répertoires de `lib/vendor/`, alors ajoutez une nouvelle entrée dans le fichier
de configuration `autoload.yml` de l'application :

    [yml]
    autoload:
      vendor_some_lib:
        name:      vendor_some_lib
        path:      %SF_LIB_DIR%/vendor/some_lib_dir
        recursive: on

Le chargement automatique du répertoire `lib/vendor` était problématique pour
plusieurs raisons :

  * Si vous déposez dans le répertoire `lib/vendor`, une nouvelle librairie qui dispose déjà de son propre
    système d'autochargement des classes, alors symfony réanalysera tous les fichiers et ajoutera un surplus
    d'informations inutiles dans le cache
    (voir #5893 - http://trac.symfony-project.org/ticket/5893).

  * Si votre répertoire symfony n'est pas exactement nommé `lib/vendor/symfony/`, le
    mécanisme d'autochargement réanalysera le répertoire entier de symfony et fera
    ainsi survenir de nouveaux problèmes
    (see #6064 - http://trac.symfony-project.org/ticket/6064).

L'autochargement dans symfony 1.3 est désormais insensible à la casse.

Le Routing
----------

Les méthodes `sfPatternRouting::setRoutes()`, `sfPatternRouting::prependRoutes()`,
`sfPatternRouting::insertRouteBefore()`, et `sfPatternRouting::connect()` ne 
retournent plus les routes sous forme de tableaux comme c'était le cas dans les pécédentes versions.

L'option `lazy_routes_deserialize` a été supprimée car elle n'est plus
nécessaire.

Depuis Symfony 1.3, le cache pour le routing est désactivé dans la mesure où il s'agit de la meilleure
otion pour la plupart des projets en ce qui concerne les performances. Ainsi, si vous n'avez
pas personnalisé le cache du routing, il sera automatiquement désactivé pour toutes vos
applications. Si, après une mise à jour vers symfony 1.3, votre projet semble plus lent, vous
pourrez alors envisager d'ajouter du cache au routing afin de vérifier si cela améliore les performances. Ceci
correspond à la configuration par défaut de Symfony 1.2 que vous pouvez dans votre fichier `factories.yml` :

    [yml]
    routing:
      param:
        cache: 
          class: sfFileCache 
          param: 
            automatic_cleaning_factor: 0 
            cache_dir:                 %SF_CONFIG_CACHE_DIR%/routing 
            lifetime:                  31556926 
            prefix:                    %SF_APP_DIR%/routing

JavaScripts et Feuilles de Styles
---------------------------------

### Suppression des Filtres Communs

Le filtre `sfCommonFilter` a été déprécié et il n'est plus utilisé désormais par défaut.
Il était utilisé pour injecter automatiquement des balises de code JavaScripts et des feuilles de styles
dans le contenu de la réponse. Vous devez désormais inclure manuellement ces ressources en
appelant explicitement les helpers `include_stylesheets()` et `include_javascripts()`
dans votre Layout :

    [php]
    <?php include_javascripts() ?>
    <?php include_stylesheets() ?>

Ce filtre a été supprimé pour plusieurs raisons :

 * Nous avons maintenant une meilleure solution, plus simple et plus flexible (les
   helpers `include_stylesheets()` et `include_javascripts()`).

 * Bien que le filtre puisse être facilement désactivé, ce n'est pas une tâche facile car vous
   devez d'abord connaître son existence et surtout comment il fonctionne "magicallement" en coulisses.

 * L'utilisation des helpers permet d'avoir un contrôle plus fin sur le positionnement
   des ressources inclus dans la mise en page (les feuilles de style dans le tag `head`,
   et les JavaScripts juste avant la fin de la balise `body` par exemple)

 * Il est toujours préférable d'être explicite plutôt qu'implicite (prenez le
   temps de lire la mailing-list des utilisateurs afin de découvrir de nombreuses
   plaintes au sujet de la magie et des comportements extravagants)

 * Cela apporte une légère amélioration des performances.

Comment mettre à jour ?

  * Le filtre `common` a besoin d'être supprimé de tous les fichiers
    de configuration `filters.yml`. Ceci est automatiquement réalisé
    par la tâche `project:upgrade1.3`.

  * Vous avez besoin d'ajouter les appels aux helpers `include_stylesheets()` et
    `include_javascripts()` dans vos layouts afin d'obtenir le même comportement
    qu'avant (ceci est automatiquement pris en charge par la tâche `project:upgrade1.3` 
    pour tous les layouts HTML situés dans le répertoire `templates/` de chaque
    application, à condition que ces derniers disposent d'un tag <head>. Tous les autres
    layouts ou pages qui nécessitent des fichiers JavaScripts ou des feuilles de styles
    doivent être mis à jour manuellement).

>**NOTE** 
>La classe 'sfCommonFilter' est toujours incluse avec symfony 1.3 et donc vous pouvez
>toujours l'utiliser dans votre `filters.yml` si vous en avez besoin.

Tâches
------

Les classes de tâches automatiques suivantes ont été renommées :

  symfony 1.2               | symfony 1.3
  ------------------------- | --------------
  `sfConfigureDatabaseTask` | `sfDoctrineConfigureDatabaseTask` ou `sfPropelConfigureDatabaseTask`
  `sfDoctrineLoadDataTask`  | `sfDoctrineDataLoadTask`
  `sfDoctrineDumpDataTask`  | `sfDoctrineDataDumpTask`
  `sfPropelLoadDataTask`    | `sfPropelDataLoadTask`
  `sfPropelDumpDataTask`    | `sfPropelDataDumpTask`

La signature de la tâche `*:data-load` a changé. Les répertoires ou les fichiers
spécifiques doivent désormais être passés en arguments. L'option `--dir` a été supprimée.

    $ php symfony doctrine:data-load data/fixtures/dev

### Les Formatters

Le troisième argument de la méthode `sfFormatter::format()` a été supprimé.

Echappement des Données
-----------------------

Le helper `esc_js_no_entities()` relatif à la constante `ESC_JS_NO_ENTITIES` a été mis à jour
afin de prendre en charge les caractères non ANSI. Avant ce changement, tous les caractères dont
la valeur ANSI est comprise entre `37` et `177` étaient échappés. Désormais,c'est seulement le caractère `\`, les apostrophes et 
guillemets (`'` & `"`) ainsi que les retours à la ligne (`\n` & `\r`). Cependant il est peu probable que vous ayez précédemment
compté sur ce mauvais comportement.

Intégration de l'ORM Doctrine
-----------------------------

### Version Minimale de Doctrine

Le lien externe de Doctrine a été mis à jour afin d'utiliser la toute dernière et incroyable
version 1.2 de Doctrine. Vous pouvez aussi consulter les dernières nouveautés de Doctrine 1.2 
[here](http://www.doctrine-project.org/upgrade/1_2).

### Suppression en Masse dans le Génération d'Administration

La suppression en masse du générateur d'administration a été modifié afin de récupérer les enregistrements, puis
d'appliquer la méthode `delete()` sur chaque objet au lieu de tous les supprimer avec une seule requête DQL.
Ainsi, les évènements rattachés aux objets seront invoqués au moment
de leur suppression.

### Redéfinition des Schémas de Données dans les Plugins

Vous pouvez désormais surcharger le model inclus dans le schéma de données YAML 
d'un plugin en définissant simplement ce même modèle dans votre schéma local. Par exemple, pour ajouter une
colonne "email" au modèle `sfGuardUser` de sfDoctrineGuardPlugin, ajoutez ceci à
`config/doctrine/schema.yml`:

    sfGuardUser:
      columns:
        email:
          type: string(255)

>**NOTE**
>L'option `package` est une nouveauté de Doctrine et utilisée pour les schémas des
>plugins Symfony. Cela ne veut pas dire que l'option `package` peut être utilisée indépendamment 
>pour paqueter vos modèles. Elle doit être utilisée directement et uniquement avec les plugins symfony.

### Enregistrement des Requêtes SQL

Doctrine enregistre les logs des requêtes exécutées à l'aide `sfEventDispatcher` au lie
d'accéder directement à l'objet logger. De plus, le sujet de
ces évènements est soit la connexion ou bien l'objet (statement) qui exécute la requête.
L'enregistrement est délégué à la nouvelle classe `sfDoctrineConnectionProfiler`, qui peut 
être atteinte depuis un objet `sfDoctrineDatabase`.

Les Plugins
-----------

Si avez recours à la méthode `enableAllPluginsExcept()` de votre classe
`ProjectConfiguration`pour gérer les plugins activés, alors gardez à l'esprit que tous les plugins sont désormais trié
par nom afin d'assurer une cohérence à travers les différentes plate-formes.

Les Widgets
-----------

La classe `sfWidgetFormInput` est maintenant déclarée abstraite. Les champs de type texte sont désormais
créés à partir de la classe `sfWidgetFormInputText`. Ce changement a été introduit afin de faciliter
l'introspection des classes de formulaire.

Le Gestionnaire d'Envoi d'E-Mails
---------------------------------

Symfony 1.3 dispose à présent d'un tout nouveau mécanisme d'envoi d'emails. Lorsqu'une nouvelle application est créée, le
fichier `factories.yml` se dote de nouveaux paramètres par défaut pour les environnements `dev` et `test`.
Mais si vous mettez à jour un projet existant, vous voudrez peut-être mettre à jour votre
fichier `factories.yml` avec la configuration suivante pour ces environnements:

    [yml]
    mailer:
      param:
        delivery_strategy: none

Avec la configuration précédente, les emails ne seront pas envoyés. Bien entendu, ils
resteront enregistrés, et le testeur `mailer` continuera de fonctionner dans 
vos tests fonctionnels.

Si vous souhaitez plutôt recevoir tous vos les emails à une même adresse, vous 
pouvez spécifier la valeur `single_address` en guise de stratégie d'envoi des 
emails (pour l`environnements `dev` par exemple):

    [yml]
    dev:
      mailer:
        param:
          delivery_strategy: single_address
          delivery_address:  foo@example.com

YAML
----

sfYAML est désormais davantage compatible avec les spécifications YAML 1.2. La partie suivante liste
les changements que vous devrez opérer dans vos fichiers de configuration :

 * Les valeurs Booléennes peuvent maintenant être représentées uniquement les mots `true` ou `false`. Si
vous utilisiez un ou plusieurs des mots alternatives suivants, vous devrez les
   remplacer par les valeurs `true` ou `false` correspondants :

    * `on`, `y`, `yes`, `+`
    * `off`, `n`, `no`, `-`

La tâche automatique `project:upgrade` vous informe des endroits où vous utilisez l'ancienne syntaxe mais ne les corrige pas
à votre place (afin d'éviter de perdre des commentaires par exemple). Vous devez les corriger vous même manuellement.

Si vous souhaitez vérifier tous vos fichiers YAML, vous pouvez forcer 
l'analyseur syntaxique YAML à utiliser les spécifications YAML 1.1 en 
utilisant la méthode `sfYaml::setSpecVersion()` :

    [php]
    sfYaml::setSpecVersion('1.1');

Propel
------

Les classes de constructeur personnalisé de Propel utilisées dans les versions précédentes de symfony ont
été remplacées par de nouvelles classes de comportement pour Propel 1.4. Pour profiter de cette
amélioration votre fichier `propel.ini` de votre projet doit être mis à jour.

Supprimer les anciennes classes de constructeur :

    ; builder settings
    propel.builder.peer.class              = plugins.sfPropelPlugin.lib.builder.SfPeerBuilder
    propel.builder.object.class            = plugins.sfPropelPlugin.lib.builder.SfObjectBuilder
    propel.builder.objectstub.class        = plugins.sfPropelPlugin.lib.builder.SfExtensionObjectBuilder
    propel.builder.peerstub.class          = plugins.sfPropelPlugin.lib.builder.SfExtensionPeerBuilder
    propel.builder.objectmultiextend.class = plugins.sfPropelPlugin.lib.builder.SfMultiExtendObjectBuilder
    propel.builder.mapbuilder.class        = plugins.sfPropelPlugin.lib.builder.SfMapBuilderBuilder

Et ajoutez les nouvelles classes de comportement :

    ; behaviors
    propel.behavior.default                        = symfony,symfony_i18n
    propel.behavior.symfony.class                  = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfony
    propel.behavior.symfony_i18n.class             = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18n
    propel.behavior.symfony_i18n_translation.class = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18nTranslation
    propel.behavior.symfony_behaviors.class        = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
    propel.behavior.symfony_timestampable.class    = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorTimestampable

La tâche `project:upgrade` tente de faire ce changement pour vous, mais peut-être pas,
si vous avez apporté des changements locaux sur `propel.ini`.

La classe `BaseFormFilterPropel` a été générée incorrectement dans
`lib/filter/base` de symfony 1.2. Cela a été corrigé dans symfony 1.3; la
classe est maintenant générée dans `lib/filter`. La tâche `project:upgrade`
déplacera ce fichier pour vous.

Tests
-----

Le fichier de test unitaire de démarrage, `test/bootstrap/unit.php`, a été amélioré afin
e mieux gérer le chargement automatique des fichiers de projet de classe. Les lignes suivantes doivent être
ajoutées à ce script :

    [php]
    $autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
    $autoload->loadConfiguration(sfFinder::type('file')->name('autoload.yml')->in(array(
      sfConfig::get('sf_symfony_lib_dir').'/config/config',
      sfConfig::get('sf_config_dir'),
    )));
    $autoload->register();

La tâche `project:upgrade` tente de faire ce changement pour vous, mais peut-être pas,
si vous avez apporté des changements locaux sur `test/bootstrap/unit.php`.