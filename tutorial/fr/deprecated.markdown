Composants supprimés ou rendus obsolètes en 1.3
===============================================

Ce document liste tous les paramètres de configuration, classes, méthodes, 
fonctions et tâches devenus obsolètes ou bien supprimés dans Symfony 1.3.

Plugins internes à Symfony
---------------------------

Les plugins internes suivants sont désormais dépréciés dans Symfony 1.3 et 
seront supprimés dans Symfony 1.4.

  * `sfCompat10Plugin` : En rendant ce plugin obsolète, tous les autres éléments 
    du framework qui dépendent de celui-ci pour fonctionner (générateur d'administration 1.0, 
    et système de formulaires 1.0) ont également été rendus obsolètes. Il inclut aussi le thème par défaut
    de l'admin generator 1.0 situé dans
    `lib/plugins/sfPropelPlugin/data/generator/sfPropelAdmin`.

  * `sfProtoculousPlugin` : Les helpers fournis par ce plugin ne produisent pas de code Javascript 
    non intrusif. Par conséquent, ils ne devraient plus être utilisés.

Méthodes et fonctions
---------------------

Les méthodes et fonctions suivantes sont désormais dépréciées dans Symfony 1.3 
ou versions antérieures, et seront retirées dans Symfony 1.4:

  * `sfToolkit::getTmpDir()` : Vous pouvez remplacer toutes les occurences de cette 
    méthode par `sys_get_temp_dir()`
 
  * `sfToolkit::removeArrayValueForPath()`, 
    `sfToolkit::hasArrayValueForPath()`, et `getArrayValueForPathByRef()`

  * `sfValidatorBase::setInvalidMessage()` : Vous pouvez remplacer tous les appels à cette méthode 
    par un appel à la nouvelle méthode `sfValidatorBase::setDefaultMessage()`

  * `sfValidatorBase::setRequiredMessage()` : Vous pouvez remplacer tous les appels à cette méthode 
    par un appel à la nouvelle méthode `sfValidatorBase::setDefaultMessage()`

  * `sfTesterResponse::contains()` : Vous pouvez maintenant utiliser la méthode plus performante 
    `matches()`

  * `sfTestFunctionalBase` les méthodes suivantes : `isRedirected()`,
    `isStatusCode()`, `responseContains()`, `isRequestParameter()`,
    `isResponseHeader()`, `isUserCulture()`, `isRequestFormat()`, et
    `checkResponseElement()`: Toutes ces méthodes ont été dépréciées depuis la version 1.2,
    et remplacées par les classes de tests.
 
  * `sfTestFunctional` les méthodes suivantes : `isCached()`, `isUriCached()` : Ces
    méthodes ont été dépréciées depuis la version 1.2, et remplacées par les classes
    de tests.

  * `sfFilesystem::sh()` : Vous pouvez remplacer toutes les occurences de cette méthode par des 
    appels à la nouvelle méthode `sfFilesystem::execute()`. Soyez attentifs à la valeur renvoyée 
    par cette méthode. Il s'agit d'un tableau composé des sorties `stdout`
    et `stderr`.

  * `sfAction::getDefaultView()`, `sfAction::handleError()`,
    `sfAction::validate()` : Ces méthodes ont été dépréciées dans symfony 1.1,
    et ne sont plus réellement utiles à présent. A partir de Symfony 1.1, elles nécessitent que le paramètre 
    `compat_10` soit à la valeur `on` pour fonctionner.

  * `sfComponent::debugMessage()` : Utilisez le helper `log_message()` à la place.

  * `sfApplicationConfiguration::loadPluginConfig()` : Utilisez la méthode
    `initializePlugins()` à la place.

  * `sfLoader::getHelperDirs()` et `sfLoader::loadHelpers()` : Utilisez les mêmes
    méthodes de l'objet `sfApplicationConfiguration`. Comme toutes les méthodes
    de la classe `sfLoader` sont dépréciées, la classe `sfLoader` sera supprimée
    de Symfony 1.4.

  * `sfController::sendEmail()` : Utilisez le nouvel outil mailer de symfony 1.3
    à la place.

  * `sfGeneratorManager::initialize()` : Ne fait rien.

  * `debug_message()` : Utilisez le helper `log_message()` à la place.

  * `sfWebRequest::getMethodName()` : Utilisez la méthode `getMethod()` à la place.

  * `sfDomCssSelector::getTexts()`: Utilisez `matchAll()->getValues()`

  * `sfDomCssSelector::getElements()`: Utilisez `matchAll()`

  * `sfVarLogger::getXDebugStack()`: Utilisez la méthode `sfVarLogger::getDebugBacktrace()`
    à la place.

  * `sfVarLogger` : La valeur enregistrée de `debug_stack` est désormais dépréciée en faveur de
    la valeur de `debug_backtrace`.

  * `sfContext::retrieveObjects()` : Cette méthode est uniquement utilisée par ObjectHelper,
    qui est déprécié.

Les fonctions et méthodes suivantes ont été retirées dans symfony 1.3:

  * `sfApplicationConfiguration::checkSymfonyVersion()` : voir plus bas pour les raisons 
    (paramètre `check_symfony_version`)

Classes
-------

Les classes suivantes ont été rendues obsolètes dans symfony 1.3 et seront supprimées
dans symfony 1.4 :

  * `sfDoctrineLogger` : Utilisez la classe `sfDoctrineConnectionProfiler` à la place.

  * `sfNoRouting` et `sfPathInfoRouting`

  * `sfRichTextEditor`, `sfRichTextEditorFCK`, et `sfRichTextEditorTinyMCE` :
    Ces classes ont été remplacées par le système de widgets (voir la section "Helpers"
    plus bas)

  * `sfCrudGenerator`, `sfAdminGenerator`, `sfPropelCrudGenerator`,
    `sfPropelAdminGenerator` : Ces classes étaient utilisées par le générateurs
     d'administration 1.0

  * `sfPropelUniqueValidator`, `sfDoctrineUniqueValidator` : Ces classes étaient
    utilisées par le système de formulaires en 1.0

  * `sfLoader` : voir la section "Méthodes et Fonctions"

  * `sfConsoleRequest`, `sfConsoleResponse`, `sfConsoleController`

  * `sfDoctrineDataRetriever`, `sfPropelDataRetriever` : Ces classes sont seulement
    utilisées par ObjectHelper, qui est déprécié

  * `sfWidgetFormI18nSelectLanguage`, `sfWidgetFormI18nSelectCurrency`, et
    `sfWidgetFormI18nSelectCountry` : Utilisez les widgets `Choice` correspondants
    (`sfWidgetFormI18nChoiceLanguage`, `sfWidgetFormI18nChoiceCurrency`, et
    `sfWidgetFormI18nChoiceCountry` respectivement) car ils agissent exactement
    de la même manière, sauf qu'ils ont plus de possibilités de personnalisation

  * `sfWidgetFormChoiceMany`, `sfWidgetFormPropelChoiceMany`,
    `sfWidgetFormDoctrineChoiceMany`, `sfValidatorChoiceMany`,
    `sfValidatorPropelChoiceMany`, `sfValidatorPropelDoctrineMany` : Utilisez les
    mêmes classes mais sans `Many` à la fin, et mettez l'option `multiple`
    à `true`

  * `SfExtensionObjectBuilder`, `SfExtensionPeerBuilder`,
    `SfMultiExtendObjectBuilder`, `SfNestedSetBuilder`,
    `SfNestedSetPeerBuilder`, `SfObjectBuilder`, `SfPeerBuilder`: Les classes
    de constructeur personnalisé de Propel ont été portés pour le nouveau système de comportement
    de Propel 1.4.

Les classes suivantes ont été supprimées dans Symfony 1.3:

  * `sfCommonFilter` : voir la section "Suppression des filtres communs" du
    fichier UPGRADE_TO_1_3 pour plus d'informations au sujet des conséquences et de
   la migration de votre code.

Helpers
-------

Les groupes de helpers suivants ont été dépréciés dans symfony 1.3 et seront
retirés dans symfony 1.4 :

  * Tous les helpers relatifs au système de formulaires 1.0 fournis par
    le plugin `sfCompat10Plugin` : `DateForm`, `Form`, `ObjectAdmin`, `Object`
    et `Validation`

Settings
--------

Les paramètres suivants (gérés à travers le fichier de configuration `settings.yml`) ont
été retirés de symfony 1.3 :

  * `check_symfony_version` : Ce paramètre a été introduit il y'a plusieurs années pour permettre
    le nettoyage automatique du cache en cas de changement de version de Symfony. C'était
    particulièrement utile sur les configurations de serveurs mutualisés où la version de
    symfony est partagée par tous les clients. Ainsi, il s'agit d'une mauvaise pratique depuis
    symfony 1.1 (il est nécessaire d'embarquer la version de Symfony dans chaque
    projet), ce paramètre n'a plus de raison d'être aujourd'hui. De plus, lorsque
    ce paramètre est fixé à la valeur `on`, la vérification engendre un léger surcoût de calcul pour chaque requête,
    dans la mesure où il faut récupérer le contenu d'un fichier.

  * `max_forwards` : Ce paramètre contrôle le nombre de redirections permises
     avant que symfony ne lève une exception. Le rendre configurable n'a finalement aucun intérêt.
     Si vous avez besoin de plus de 5 redirections, c'est qu'il y'a très certainement un défaut de conception et
     de performance.

  * `sf_lazy_cache_key` : Ce paramètre a été introduit comme une réelle amélioration des performances
     dans symfony 1.2.6, il permettait d'activer la génération des clés de cache à la demande pour
     le cache des pages HTML. Alors que nous pensons que le faire à la demande était la meilleure
     idée, d'autres personnes invoquaient la méthode `sfViewCacheManager::isCacheable()`
     même si l'action elle-même n'était pas cachable. A partir de symfony
     1.3, la mise en cache des pages se comporte comme si le paramètre `sf_lazy_cache_key` avait été fixé à la valeur `true`.

  * `strip_comments` : Le paramètre `strip_comments` a été introduit pour permettre de désactiver
    la suppression des commentaires en raison de certains bugs dans le tokenizer de
    quelques versions 5.0.x de PHP. Il était également utilisé pour éviter une consommation trop importante de mémoire
    lorsque l'extension Tokenizer n'était pas compilée avec PHP. Le
    premier problème n'est plus pertinent désormais depuis que la version minimale requise de PHP
    est la 5.2. Le second problème a quant à lui été corrigé en supprimant l'expression
    régulière qui simulait la suppression des commentaires.

  * `lazy_routes_deserialize` : Cette option n'est plus nécessaire.

Les paramètres suivants ont été dépréciés dans symfony 1.3 et seront retirés
 dans symfony 1.4 :

  * `calendar_web_dir`, `rich_text_js_dir` : Ces paramètres sont utilisés par
     le groupe de helper Form, qui est lui-même déprécié dans symfony 1.3.

  * `validation_error_prefix`, `validation_error_suffix`,
    `validation_error_class`, `validation_error_id_prefix` : Ces paramètres sont utilisés
    par les helpers du groupe Validation, qui est lui-même déprécié dans symfony 1.3.

  * `is_internal` (dans `module.yml`): Le flag `is_internal` était utilisé pour
    empêcher des actions d'être appelées d'un navigateur. Cela a été ajouté pour
    protéger le courrier électronique envoyé dans symfony 1.0. Comme le support de l'email n'exige
    plus cette ruse, ce flag sera enlevé et désormais ne sera pas vérifié dans
    le noyau du code symfony.

Tâches automatiques
-------------------

Les tâches automatiques suivantes ont été supprimée de symfony 1.3:

  * `project:freeze` et `project:unfreeze` : Ces tâches embarquaient la version
    de symfony du projet à l'intérieur même de ce dernier. Elles ne sont plus
    nécessaires à présent dans la mesure où la meilleure pratique consistait à embarquer Symfony dans le
    projet pour une longue période. De plus, passer d'une version de
    symfony à une autre est particulièrement simple puisqu'il suffit de changer le
    chemin dans la classe `ProjectConfiguration`. Embarquer symfony manuellement est
    également très simple puisqu'il suffit de copier tout le répertoire de symfony
    ailleurs dans le projet (`lib/vendor/symfony/`est le répertoire recommandé par défaut).

Les tâches suivantes sont dépréciées dans symfony 1.3 et seront retirées dans
symfony 1.4 :

  * Tous les alias de tâches symfony 1.0.

  * `propel:init-admin`: Cette tâche génére les modules de l'admin generator pour
    symfony 1.0.

Les tâches Doctrine suivantes ont été fusionnées dans la tâche `doctrine:build` et 
seront supprimées dans symfony 1.4 :

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

Divers
------

Les comportements suivants sont dépréciés dans symfony 1.3 et seront supprimés
dans symfony 1.4 :

  * Le support de la notion tableau (`[]`) des méthodes `sfParameterHolder::get()`, `sfParameterHolder::has()`,
    `sfParameterHolder::remove()`, `sfNamespacedParameterHolder::get()`,
    `sfNamespacedParameterHolder::has()`, et
    `sfNamespacedParameterHolder::remove()` est déprécié et ne
    sera plus disponible dans symfony 1.4
    (plus performant).

L'interface en ligne de commande de Symfony (CLI) n'accepte plus l'option globale `--dry-run` 
dans la mesure où elle n'était utilisée par aucune des tâches natives de symfony. Si l'une de vos tâches automatiques nécessite
cette option, vous pouvez simplement l'ajouter comme une nouvelle option locale dans la classe qui décrit votre commande.

Les templates Propel du générateur d'administration 1.0 et des CRUD 1.0 seront supprimés
dans symfony 1.4
(`plugins/sfPropelPlugin/data/generator/sfPropelAdmin/`).

Le calendrier dynamique "Dynarch calendar" (situé dans le répertoire data/web/calendar/) sera retiré de
symfony 1.4 dans la mesure où il est seulement utilisé par le groupe de helpers Form, qui lui-même sera
supprimé de Symfony 1.4.

A partir de symfony 1.3, la page indisponible sera seulement visible dans
les répertoires `%SF_APP_CONFIG_DIR%/` et `%SF_CONFIG_DIR%/`. Si vous l'avez encore
stockée dans `%SF_WEB_DIR%/errors/`, vous devez la déplacer avant de migrer vers
symfony 1.4.
 
Le répertoire `doc/` à la racine d'un projet n'est plus généré, car il
n'est même pas utilisé par symfony. Ainsi, le sf_doc_dir relatif à ce répertoire a été
également supprimé.
