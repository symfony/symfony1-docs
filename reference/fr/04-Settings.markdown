Le fichier de configuration settings.yml
========================================

La plupart des aspects de symfony peuvent être configurés via un fichier de configuration
écrit en YAML, ou avec du simple PHP. Dans cette section, `settings.yml`, le principal fichier
de configuration est décrit.

Le fichier principal de configuration `settings.yml` pour une application peut être trouvé dans
le répertoire `apps/APP_NAME/config/`.

Comme indiqué dans l'introduction, le fichier `settings.yml` est
[**sensible à l'environnement**](#chapter_03_sensibilisation_a_l_environnement), et bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade).

Chaque section d'un environnement comprend deux sous-sections : `.actions` et `.settings`. Toutes
les directives de configuration passent par la sous-section `.settings`, à l'exception des
actions par défaut pour restituer certaines pages communes.

>**NOTE**
>Le fichier de configuration `settings.yml` est mis en cache dans un fichier PHP, le processus est
>automatiquement géré par la [classe](#chapter_14_config_handlers_yml)
>~`sfDefineEnvironmentConfigHandler`~.

<div class="pagebreak"></div>

Paramètres
--------

  * `.actions`

    * [`error_404`](#chapter_04_sub_error_404)
    * [`login`](#chapter_04_sub_login)
    * [`secure`](#chapter_04_sub_secure)
    * [`module_disabled`](#chapter_04_sub_module_disabled)

  * `.settings`

    * [`cache`](#chapter_04_sub_cache)
    * [`charset`](#chapter_04_sub_charset)
    * [`check_lock`](#chapter_04_sub_check_lock)
    * [`compressed`](#chapter_04_sub_compressed)
    * [`csrf_secret`](#chapter_04_sub_csrf_secret)
    * [`default_culture`](#chapter_04_sub_default_culture)
    * [`default_timezone`](#chapter_04_sub_default_timezone)
    * [`enabled_modules`](#chapter_04_sub_enabled_modules)
    * [`error_reporting`](#chapter_04_sub_error_reporting)
    * [`escaping_strategy`](#chapter_04_sub_escaping_strategy)
    * [`escaping_method`](#chapter_04_sub_escaping_method)
    * [`etag`](#chapter_04_sub_etag)
    * [`i18n`](#chapter_04_sub_i18n)
    * [`lazy_cache_key`](#chapter_04_sub_lazy_cache_key)
    * [`file_link_format`](#chapter_04_sub_file_link_format)
    * [`logging_enabled`](#chapter_04_sub_logging_enabled)
    * [`no_script_name`](#chapter_04_sub_no_script_name)
    * [`standard_helpers`](#chapter_04_sub_standard_helpers)
    * [`use_database`](#chapter_04_sub_use_database)
    * [`web_debug`](#chapter_04_sub_web_debug)
    * [`web_debug_web_dir`](#chapter_04_sub_web_debug_web_dir)

<div class="pagebreak"></div>

La sous-section `.actions`
--------------------------

*Configuration par défaut* :

    [yml]
    default:
      .actions:
        error_404_module:       default
        error_404_action:       error404

        login_module:           default
        login_action:           login

        secure_module:          default
        secure_action:          secure

        module_disabled_module: default
        module_disabled_action: disabled

La sous-section `.actions` définit l'action à exécuter lorsqu'une page commune
doit être restituée. Chaque définition comporte deux éléments : l'un pour le module
(suffixé par `_module`) et l'autre pour l'action (suffixé par `_action`).

### ~`error_404`~

L'action `error_404` est exécutée quand une page 404 doit être restituée.

### ~`login`~

L'action `login` est exécutée quand un utilisateur non-identifié essaie d'accéder à
une page sécurisée.

### ~`secure`~

L'action `secure` est exécutée quand un utilisateur n'a pas les pouvoirs
requis.

### ~`module_disabled`~

L'action `module_disabled` est exécuté quand l'utilisateur demande un module
désactivé.

La sous-section `.settings`
---------------------------

La sous-section `.settings` est l'endroit où  se réalise la configuration du framework. Les
paragraphes qui suivent décrivent tous les paramètres possibles et sont à peu près classé par ordre
d'importance.

Tous les paramètres définis dans la section `.settings` sont disponibles n'importe où dans le
code en utilisant l'objet `sfConfig` et en préfixant le paramètre avec `sf_`. Par
exemple, pour obtenir la valeur du paramètre `charset`, utilisez :

    [php]
    sfConfig::get('sf_charset');

### ~`escaping_strategy`~

*Par défaut* : `true`

Le paramètre `escaping_strategy` est un paramètre booléen qui détermine si
l'échappement de sortie du sous-framework est activé ou non. Lorsqu'il est activé, toutes les variables
disponibles dans les Templates sont automatiquement échappées en appelant le Helper
défini par le paramètre `escaping_method` (voir ci-dessous).

Faites bien attention à l'`escaping_method`, le Helper par défaut utilisé par symfony,
mais cela peut être remplacé au cas par cas, par exemple, en sortant une variable
d'une balise d'un script JavaScript.

L'échappement de sortie du sous-framework utilise le paramètre `charset` pour échapper.

Il est fortement recommandé de laisser la valeur par défaut à `true`.

>**TIP**
>Ce réglage peut être activé lorsque vous créez une application avec la tâche
>`generate:app` en utilisant l'option `--escaping-strategy`.

### ~`escaping_method`~

*Par défaut* : `ESC_SPECIALCHARS`

L'`escaping_method` definit la fonction par défaut utilisée pour échapper
les variables dans les Templates (Voir l'`escaping_strategy` ci-dessus).

 Vous pouvez choisir l'une des valeurs prédéfinies : ~`ESC_SPECIALCHARS`~, ~`ESC_RAW`~,
~`ESC_ENTITIES`~, ~`ESC_JS`~, ~`ESC_JS_NO_ENTITIES`~, et
~`ESC_SPECIALCHARS`~, ou créer votre propre fonction.

La plupart du temps, la valeur par défaut est très bien. Le Helper ESC_ENTITIES peut
également être utilisé, surtout si vous travaillez uniquement avec les langues anglaises
ou européennes.

### ~`csrf_secret`~

*Par défaut* : a généré de façon aléatoire un secret

Le `csrf_secret` est un secret unique pour votre application. S'il n'est pas défini à
`false`, il permet la protection CSRF pour toutes les formulaires définis avec le formulaire
du framework. Ce paramètre est également utilisé par le Helper `link_to()` quand il a besoin
de convertir un lien vers un formulaire (pour simuler une méthode HTTP `DELETE` par exemple).

Il est fortement recommandé de changer la valeur par défaut par un secret unique
de votre choix.

>**TIP**
>Ce paramètre peut être activé lorsque vous créez une application avec la tâche
>`generate:app` en utilisant l'option `--csrf-secret`.

### ~`charset`~

*Par défaut* : `utf-8`

Le paramètre `charset` est le jeu de caractères qui sera utilisé partout dans
le framework : de la réponse du `Content-Type` dans le header, à la fonctionnalité
d'échappement de sortie.

La plupart du temps, la valeur par défaut est très bien.

>**WARNING**
>Ce paramètre est utilisé dans de nombreux endroits différents dans le framework,
>et donc sa valeur est mise en cache à plusieurs endroits. Après l'avoir modifié,
le cache de configuration doit être vidé, même dans l'environnement de
développement.

### ~`enabled_modules`~

*Par défaut* : `[default]`

L'`enabled_modules` est un tableau de nom de module à activer pour cette
application. Les modules définis dans un plugin ou dans le noyau de symfony ne sont pas activés
par défaut, et vous devez les lister dans ce paramètre pour qu'ils soient accessibles.

L'ajout d'un module est très simple en l'ajoutant à la liste (l'ordre des modules
n'a pas d'importance) :

    [yml]
    enabled_modules: [default, sfGuardAuth]

Le module `default` défini dans le framework contient toutes les actions par défaut
définies dans la sous-section `.actions` de `settings.yml`. Il est recommandé de
personnaliser le tout, puis de retirer le module `default` de ce
paramètre.

### ~`default_timezone`~

*Par défaut* : aucun

Le paramètre `default_timezone` définit le fuseau horaire par défaut utilisé par PHP. Il
peut avoir n'importe quel [fuseau horaire](http://www.php.net/manual/en/class.datetimezone.php)
reconnu par PHP. 

>**NOTE**
>Si vous ne définissez pas de fuseau horaire, il est conseillé d'en définir un dans le
>fichier `php.ini`. Sinon, symfony va essayer de deviner le meilleur fuseau horaire
>en appelant la fonction 
>[`date_default_timezone_get()`](http://www.php.net/date_default_timezone_get)
>de PHP.

### ~`cache`~

*Par défaut* : `false`

Le paramètre `cache` active ou désactive le modèle de mise en cache.

>**TIP**
> La configuration générale du système de cache est fait dans
>les sections [`view_cache_manager`](#chapter_05_view_cache_manager) et
>[`view_cache`](#chapter_05_view_cache) du fichier de configuration
> `factories.yml`. La configuration la plus fine est faite dans
>le fichier de configuration [`cache.yml`](#chapter_09).

### ~`etag`~

*Par défaut* : `true` par défaut sauf pour les environnements de `dev` et `test`

Le paramètre `etag` active ou désactive la génération automatique d'en-têtes `ETag` HTTP.
Le ETag généré par symfony est un simple MD5 du contenu des
réponses.

### ~`i18n`~

*Par défaut* : `false`

Le paramètre `i18n` est un booléen qui active ou désactive le sous framework
i18n. Si votre application est internationalisée, réglez-le à `true`.

>**TIP**
>La configuration générale du système i18n est à faire dans la section
>[`i18n`](#chapter_05_i18n) du fichier de configuration
>`factories.yml`.

### ~`default_culture`~

*Par défaut* : `en`

Le paramètre `default_culture`  définit la culture par défaut utilisé par le sous-framework
i18n. Il peut avoir n'importe quelle culture valide.

### ~`standard_helpers`~

*Par défaut* : `[Partial, Cache]`

Le paramètre `standard_helpers` est un tableau de groupes de Helper permettant de charger tous les
Templates (nom du groupe de Helper sans le suffixe `Helper`).

### ~`no_script_name`~

*Par défaut* : `true` pour l'environnement de `prod` de la première application créée,
`false` pour les autres

Le paramètre `no_script_name` détermine si le nom du script du contrôleur frontal
est ajouté ou non dans l'URL générée. Par défaut il est réglé sur `true` par
la tâche `generate:app` pour l'environnement de `prod` de la première application
créée. 

De toute évidence, un seul couple application/environnement peut avoir le paramètre à
`true`, dans le cas où tous les contrôleurs frontaux sont dans le même répertoire (`web/`). Si vous voulez
plus d'une application avec `no_script_name` à `true`, déplacez le(s) contrôleur(s)
correspondant(s) dans un sous-répertoire du répertoire racine
web.

### ~`lazy_cache_key`~

*Par défaut* : `true` pour les nouveaux projets, `false` pour des projets mis à niveau

Lorsqu'il est activé, le paramètre `lazy_cache_key` retarde la création d'une clé cache
jusqu'à ce que la vérification de la mise en cache d'une action ou d'un partial soit terminée. Cela peut
avoir pour résultat une grande amélioration des performances, en fonction de votre utilisation des Templates
de Partials.

### ~`file_link_format`~

*Par défaut* : aucun

Dans le message du débug, les chemins de fichiers sont des liens cliquables si
`sf_file_link_format` ou la valeur de configuration PHP de `xdebug.file_link_format`
est définie.

Par exemple, si vous voulez ouvrir des fichiers dans TextMate, vous pouvez utiliser la valeur
suivante :

    [yml]
    txmt://open?url=file://%f&line=%l

Le `%f` sera remplacé par le chemin absolue du fichier et le `%l`
sera remplacé par le numéro de ligne.

### ~`logging_enabled`~

*Par défaut* : `true` pour tous les environnements sauf `prod`

Le paramètre `logging_enabled` active la journalisation du sous-framework. Mettez cette option à
`false` et il contourne le mécanisme de journalisation et cela fournit un petit
gain de performance.

>**TIP**
>La configuration la plus fine de la journalisation est faite dans le fichier de configuration
>`factories.yml`.

### ~`web_debug`~

*Par défaut* : `false` pour tous les environnements sauf `dev`

Le paramètre `web_debug` active la barre d'outil de déboggage web. Elle
est incluse dans une page si le contenu de la réponse est du HTML.

### ~`error_reporting`~

*Par défaut* :

  * `prod`:  E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR
  * `dev`:   E_ALL | E_STRICT
  * `test`:  (E_ALL | E_STRICT) ^ E_NOTICE
  * default: E_PARSE | E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_USER_ERROR

Le paramètre `error_reporting` contrôle le niveau de rapport d'erreurs PHP (qui sera
affiché dans le navigateur et écrit dans les journaux).

>**TIP**
>Le site PHP a quelques informations sur la façon d'utiliser les
>[opérateurs sur les bits](http://www.php.net/language.operators.bitwise).

La configuration par défaut est la plus sensible, et ne devrait pas être modifiée.

>**NOTE**
>L'affichage des erreurs dans le navigateur est automatiquement désactivé
>pour les contrôleurs frontaux qui ont le `debug` désactivé, c'est le cas par défaut
>pour l'environnement de `prod`.

### ~`compressed`~

*Par défaut* : `false`

Le paramètre `compressed` permet la compression native de la réponse PHP. S'il est à
`true`, symfony utilisera [`ob_gzhandler`](http://www.php.net/ob_gzhandler), une fonction
callback de `ob_start()`.

Il est recommandé de le laisser à `false` et utiliser le mécanisme de compression
natif de votre serveur web à la place.

### ~`use_database`~

*Par défaut* : `true`

Le `use_database` détermine si l'application utilise une base de données ou non.

### ~`check_lock`~

*Par défaut* : `false`

Le paramètre `check_lock` active ou désactive le système de verrouillage de l'application
déclenchée par des tâches telles que `cache:clear` et `project:disable`.

S'il est défini à `true`, toutes les requêtes vers des applications désactivées seront automatiquement
redirigées vers la page du noyau symfony `lib/exception/data/unavailable.php`.

>**TIP**
>Vous pouvez remplacer le Template par défaut disponible en ajoutant
>un fichier `config/unavailable.php` à votre projet ou à votre application.

### ~`web_debug_web_dir`~

*Par défaut* : `/sf/sf_web_debug`

Le `web_debug_web_dir` définit le chemin web pour les ressources de la barre d'outil de déboggage
(images, feuilles de style, et les fichiers JavaScript).
