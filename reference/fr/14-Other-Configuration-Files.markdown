Autres fichiers de configuration
=========================

Ce chapitre décrit d'autres fichiers de configuration de symfony, qui ont rarement besoin
d'être changée.

~`autoload.yml`~
----------------

La configuration `autoload.yml` détermine quels répertoires doivent être
chargés automatiquement par symfony. Chaque répertoire est scanné pour les classes PHP et
les interfaces.

Comme indiqué dans l'introduction, le fichier `autoload.yml` bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

>**NOTE**
>Le fichier de configuration `autoload.yml`  est mis en cache dans un fichier PHP, le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfFilterConfigHandler`~.

La configuration par défaut est très bien pour la plupart des projets :

    [yml]
    autoload:
      # project
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony]

      project_model:
        name:           project model
        path:           %SF_LIB_DIR%/model
        recursive:      true

      # application
      application:
        name:           application
        path:           %SF_APP_LIB_DIR%
        recursive:      true

      modules:
        name:           module
        path:           %SF_APP_DIR%/modules/*/lib
        prefix:         1
        recursive:      true

Chaque configuration possède un nom et doit être mis sous une clé avec ce nom. Il
permet pour la configuration par défaut d'être redéfinie.

>**TIP**
>Comme vous pouvez le voir, le répertoire `lib/vendor/symfony/` est exclus par défaut,
>car symfony utilise un mécanisme d'auto-chargement différents pour les classes du noyau.

Plusieurs clés peuvent être utilisées pour personnaliser le comportement d'auto-chargement :

 * `name`: Une description
 * `path`: Le chemin d'auto-chargement
 * `recursive`: Pour chercher les classes PHP dans les sous-répertoires
 * `exclude`: Un tableau de nom de répertoires à exclure de la recherche
 * `prefix`: Mettre à `true` si les classes trouvées dans le chemin ne doivent pas être chargées automatiquement pour un module donné (`false` par défaut)
 * `files`: Un tableau de fichier à analyser de façon explicite pour les classes PHP
 * `ext`: L'extension des classes php (`.php` par défaut)

Par exemple, si vous incorporez une grande bibliothèque au sein de votre projet sous le
répertoire `lib/`, et si elle prend déjà en charge le chargement automatique, vous pouvez l'exclure
du système par défaut d'auto-chargement de symfony pour bénéficier d'un gain de
performances en modifiant la configuration de chargement automatique de `project` :

    [yml]
    autoload:
      project:
        name:           project
        path:           %SF_LIB_DIR%
        recursive:      true
        exclude:        [model, symfony, vendor/large_lib]

~`config_handlers.yml`~
-----------------------

Le fichier de configuration `config_handlers.yml` décrit les classes du gestionnaire
de configuration utilisées pour analyser et interpréter tous les autres fichiers de configuration
YAML. Voici la configuration par défaut utilisée pour charger le fichier de
configuration `settings.yml` :

    [yml]
    config/settings.yml:
      class:    sfDefineEnvironmentConfigHandler
      param:
        prefix: sf_

Chaque fichier de configuration est défini par une classe (entrée `class`) et peut être
personnalisés en définissant certains paramètres dans la section `param`.

Le fichier par défaut `config_handlers.yml` définit les classes de l'analyseur comme suit :

 | Fichier de configuration | Classe du gestionnaire de config   |
 | ------------------------ | ---------------------------------- |
 | `autoload.yml`           | `sfAutoloadConfigHandler`          |
 | `databases.yml`          | `sfDatabaseConfigHandler`          |
 | `settings.yml`           | `sfDefineEnvironmentConfigHandler` |
 | `app.yml`                | `sfDefineEnvironmentConfigHandler` |
 | `factories.yml`          | `sfFactoryConfigHandler`           |
 | `core_compile.yml`       | `sfCompileConfigHandler`           |
 | `filters.yml`            | `sfFilterConfigHandler`            |
 | `routing.yml`            | `sfRoutingConfigHandler`           |
 | `generator.yml`          | `sfGeneratorConfigHandler`         |
 | `view.yml`               | `sfViewConfigHandler`              |
 | `security.yml`           | `sfSecurityConfigHandler`          |
 | `cache.yml`              | `sfCacheConfigHandler`             |
 | `module.yml`             | `sfDefineEnvironmentConfigHandler` |

~`core_compile.yml`~
--------------------

Le fichier de configuration `core_compile.yml` décrit les fichiers PHP qui sont
fusionnés dans un grand dossier dans l'environnement `prod`, afin accélérer le temps
qu'il faut pour que symfony charge. Par défaut, les classes principales du noyau symfony sont
définies dans ce fichier de configuration. Si votre application se fonde sur certaines classes
qui doivent être chargés pour chaque requête, vous pouvez créer un fichier de configuration
`core_compile.yml` dans votre projet ou votre application et les ajouter. Voici
un extrait de la configuration par défaut :

    [yml]
    - %SF_SYMFONY_LIB_DIR%/autoload/sfAutoload.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfComponent.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfAction.class.php
    - %SF_SYMFONY_LIB_DIR%/action/sfActions.class.php

Comme indiqué dans l'introduction, le fichier `core_compile.yml` bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

>**NOTE**
>Le fichier de configuration `core_compile.yml` est mis en cache dans un fichier PHP, le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfFilterConfigHandler`~.

~`module.yml`~
--------------

Le fichier de configuration `module.yml` permet la configuration d'un module. Ce
fichier de configuration est rarement utilisé et ne peut contenir que les entrées définies
ci-dessous.

Le fichier `module.yml` doit être stocké dans le sous-répertoire `config/` d'un
module pour être chargé par symfony. Le code suivant montre un contenu typique de
`module.yml` avec les valeurs par défaut pour tous les paramètres :

    [yml]
    all:
      enabled:            true
      view_class:         sfPHP
      partial_view_class: sf

Si le paramètre `enabled` est défini à `false`, toutes les actions d'un module sont
désactivés. Ils sont redirigés vers l'action
~[`module_disabled_module`](#chapter_04_the_actions_sub_section)~/~`module_disabled_action`~
(tel que défini dans [`settings.yml`](#chapter_04)).

Le paramètre `view_class` définit la classe de la vue utilisée par toutes les actions du
module (sans le suffixe `View`). Elle doit hériter de `sfView`.

Le paramètre `partial_view_class` définit la classe de la vue utilisée par les partials de
ce module (sans le suffixe `PartialView`). Elle doit hériter de
`sfPartialView`.
