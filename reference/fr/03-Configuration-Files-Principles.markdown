Principes des fichiers de configuration
=============================

Les fichiers de configuration de symfony sont basés sur un ensemble commun de principes et partagent
quelques propriétés communes. Cette section les décrit en détail, et sert de
référence pour les autres sections décrivant les fichiers de configuration YAML.

Cache
-----

Tous les fichiers de configuration de symfony sont mis en cache dans des fichiers PHP par les classes
de gestionnaire de configuration. Quand le paramètre `is_debug` est mis à `false` (par exemple
pour l'environnement de `prod`), le fichier YAML est seulement consulté lors de la première
requête, le cache PHP est utilisé pour les requêtes suivantes. Cela signifie que
le "lourd" travail est fait qu'une seule fois, lorsque le fichier YAML est analysé et interprété
la première fois.

>**TIP**
>Dans l'environnement de `dev`, où `is_debug` est défini par défaut à `true`,
>la compilation se fait à chaque modification du fichier (symfony
>contrôle la date de modification du fichier).

L'analyse et la mise en cache de chaque fichier de configuration se fait par des classes spécialisées
de gestionnaire de configuration, configuré en
[`config_handler.yml`](#chapter_14_config_handlers_yml).

Dans les sections suivantes, lorsque nous parlons de "compilation", cela décrit
la première fois que le fichier YAML est converti en un fichier PHP et stocké dans le
cache.

>**TIP**
>Pour forcer le rechargement du cache de configuration, vous pouvez utiliser la tâche
>`cache:clear` :
>
>     $ php symfony cache:clear --type=config

Constantes
---------

*Les fichiers de configuration* : `core_compile.yml`, `factories.yml`, `generator.yml`,
`databases.yml`, `filters.yml`, `view.yml`, `autoload.yml`

Certains fichiers de configuration permettent l'utilisation de constantes pré-définies. Les constantes
sont déclarées avec des substituants en utilisant la notation `%XXX%` (où XXX est
une clé en majuscules) et sont remplacés par leur valeur réelle pendant la "compilation".

### Paramètres de configuration

Une constante peut être n'importe quel paramètre défini dans le fichier de configuration `settings.yml`.
La clé du substituant est le nom de la clé du paramètre en majuscule préfixé avec
`SF_`:

    [yml]
    logging: %SF_LOGGING_ENABLED%

Quand symfony compile le fichier de configuration, il remplace toutes les occurrences de substituant
`%SF_XXX%` par leur valeur définie dans `settings.yml`. Dans l'exmple ci-dessous,
il remplacera le substituant `SF_LOGGING_ENABLED` par la valeur du paramètre
`logging_enabled` définie dans `settings.yml`.

### Paramètres de l'application

Vous pouvez également utiliser les paramètres définis dans le fichier de configuration `app.yml` en
préfixant le nom de la clé avec `APP_`.

### Constantes spéciales

Par défaut, symfony definit quatre constantes selon le contrôleur
frontal actuel:

 | Constantes             | Description                             | Méthode de configuration |
 | ---------------------- | --------------------------------------- | ------------------------ |
 | ~`SF_APP`~             | L'actuel nom de l'application           | `getApplication()`       |
 | ~`SF_ENVIRONMENT`~     | L'actuel nom de environnement           | `getEnvironment()`       |
 | ~`SF_DEBUG`~           | Si le debug est activé ou non           | `isDebug()`              |
 | ~`SF_SYMFONY_LIB_DIR`~ | Le répertoire des librairies de symfony | `getSymfonyLibDir()`     |

### Répertoires

Les constantes sont également très utiles lorsque vous avez besoin pour faire référence à un répertoire ou un
chemin de fichier sans le coder en dur. Symfony définit un certain nombre de constantes pour les répertoires
communs du projet et de l'application.

La racine de la hiérarchie est la racine du répertoire du projet, `SF_ROOT_DIR`. Toutes
les autres constantes sont dérivées de ce répertoire racine.

La structure de répertoire du projet est défini comme suit :

 | Constantes         | Valeur par défaut    |
 | ------------------ | -------------------- |
 | ~`SF_APPS_DIR`~    | `SF_ROOT_DIR/apps`   |
 | ~`SF_CONFIG_DIR`~  | `SF_ROOT_DIR/config` |
 | ~`SF_CACHE_DIR`~   | `SF_ROOT_DIR/cache`  |
 | ~`SF_DATA_DIR`~    | `SF_ROOT_DIR/data`   |
 | ~`SF_DOC_DIR`~     | `SF_ROOT_DIR/doc`    |
 | ~`SF_LIB_DIR`~     | `SF_ROOT_DIR/lib`    |
 | ~`SF_LOG_DIR`~     | `SF_ROOT_DIR/log`    |
 | ~`SF_PLUGINS_DIR`~ | `SF_ROOT_DIR/plugins`|
 | ~`SF_TEST_DIR`~    | `SF_ROOT_DIR/test`   |
 | ~`SF_WEB_DIR`~     | `SF_ROOT_DIR/web`    |
 | ~`SF_UPLOAD_DIR`~  | `SF_WEB_DIR/uploads` |

La structure de répertoire de l'application est défini dans le
répertoire `SF_APPS_DIR/APP_NAME` :

 | Constantes              | Valeur par défaut      |
 | ----------------------- | ---------------------- |
 | ~`SF_APP_CONFIG_DIR`~   | `SF_APP_DIR/config`    |
 | ~`SF_APP_LIB_DIR`~      | `SF_APP_DIR/lib`       |
 | ~`SF_APP_MODULE_DIR`~   | `SF_APP_DIR/modules`   |
 | ~`SF_APP_TEMPLATE_DIR`~ | `SF_APP_DIR/templates` |
 | ~`SF_APP_I18N_DIR`~     | `SF_APP_DIR/i18n`      |

Enfin, la structure du répertoire du cache de l'application est défini comme suit :

 | Constantes                | Valeur par défaut                |
 | ------------------------- | -------------------------------- |
 | ~`SF_APP_BASE_CACHE_DIR`~ | `SF_CACHE_DIR/APP_NAME`          |
 | ~`SF_APP_CACHE_DIR`~      | `SF_CACHE_DIR/APP_NAME/ENV_NAME` |
 | ~`SF_TEMPLATE_CACHE_DIR`~ | `SF_APP_CACHE_DIR/template`      |
 | ~`SF_I18N_CACHE_DIR`~     | `SF_APP_CACHE_DIR/i18n`          |
 | ~`SF_CONFIG_CACHE_DIR`~   | `SF_APP_CACHE_DIR/config`        |
 | ~`SF_TEST_CACHE_DIR`~     | `SF_APP_CACHE_DIR/test`          |
 | ~`SF_MODULE_CACHE_DIR`~   | `SF_APP_CACHE_DIR/modules`       |

Sensibilisation à l'environnement
---------------------

*Les fichiers de configuration*: `settings.yml`, `factories.yml`, `databases.yml`,
`app.yml`

Certains fichiers de configuration de symfony sont sensible à l'environnement, leur interprétation
dépend de l'environnement symfony actuel. Ces fichiers ont des sections différentes qui définissent
une configuration différente pour chaque environnement. Lorsque
vous créez une nouvelle application, symfony crée une configuration sensible pour les
trois environnements par défaut de symfony : `prod`, `test`, et `dev` :

    [yml]
    prod:
      # Configuration pour l'environnement de `prod`

    test:
      # Configuration pour l'environnement de `test`

    dev:
      # Configuration pour l'environnement de `dev`

    all:
      # Configuration par défaut de tous les environnements

Lorsque symfony a besoin d'une valeur à partir d'un fichier de configuration, il fusionne
la configuration disponible dans la section de l'environnement actuel avec la configuration `all`.
La section spéciale `all` décrit la configuration par défaut pour tous
les environnements. Si la section de l'environnement n'est pas définie, symfony resdescend
sur la configuration de `all`.

Configuration en cascade
---------------------

*Les fichiers de configuration*: `core_compile.yml`, `autoload.yml`, `settings.yml`,
`factories.yml`, `databases.yml`, `security.yml`, `cache.yml`, `app.yml`,
`filters.yml`, `view.yml`

Certains fichiers de configuration peuvent être définis dans plusieurs sous-répertoires `config/`
contenus dans la structure des répertoires du projet.

Lorsque la configuration se compile, les valeurs des différents fichiers
sont fusionnés selon un ordre de priorité :

  * La configuration du module (`PROJECT_ROOT_DIR/apps/APP_NAME/modules/MODULE_NAME/config/XXX.yml`)
  * La configuration de l'application (`PROJECT_ROOT_DIR/apps/APP_NAME/config/XXX.yml`)
  * La configuration du projet (`PROJECT_ROOT_DIR/config/XXX.yml`)
  * La configuration défini dans les plugins (`PROJECT_ROOT_DIR/plugins/*/config/XXX.yml`)
  * La configuration par défaut défini dans les librairies de symfony (`SF_LIB_DIR/config/XXX.yml`)

Par exemple, le `settings.yml` défini dans un répertoire de l'application hérite
de la configuration définie dans le répertoire `config/` principal du projet, et
éventuellement de la configuration par défaut contenus dans le framework lui-même
(`lib/config/config/settings.yml`).

>**TIP**
>Quand un fichier de configuration est sensible à l'environnement, il peut être défini dans
>plusieurs répertoires, la liste des priorités suivante s'applique de la manière suivante :
>
> 1. Module
> 2. Application
> 3. Projet
> 4. Environnement spécifique
> 5. Tous les environnements
> 6. Par défault
