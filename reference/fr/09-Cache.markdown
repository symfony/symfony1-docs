Le fichier de configuration cache.yml
================================

Le fichier de configuration ~`cache.yml`~ décrit la configuration du cache pour la
couche de la vue. Ce fichier de configuration n'est actif que si le paramètre
[`cache`](#chapter_04_sub_cache) est activé dans `settings.yml`.

>**TIP**
>La configuration de la classe utilisée pour la mise en cache et
>sa configuration associée est faite dans les sections
>[`view_cache_manager`](#chapter_05_view_cache_manager) et
>[`view_cache`](#chapter_05_view_cache) du fichier de configuration
>de `factories.yml`

Lorsqu'une application est créée, symfony génère un fichier par défaut `cache.yml`
dans le répertoire `config/` de l'application qui décrit le cache pour toute
l'application (sous une clé par défaut). Par défaut, le cache est définie globalement
sur `false` :

    [yml]
    default:
      enabled:     false
      with_layout: false
      lifetime:    86400

>**TIP**
>Comme le paramètre `enabled` est définie sur `false` par défaut, vous devez
>activer le cache de manière sélective. Vous pouvez également travailler dans l'autre sens :
>activez le cache globalement, puis, le désactivez sur les pages spécifiques qui
>ne doivent pas être mis en cache. Votre approche doit prendre en compte ce qui représente
>le moins de travail pour votre application.

Comme indiqué dans l'introduction, le fichier `cache.yml` bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

>**NOTE**
>Le fichier de configuration `cache.yml` est mis en cache dans un fichier PHP; le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfSecurityConfigHandler`~.

La configuration de l'application par défaut peut être surchargée pour un module en
créant un fichier `cache.yml` dans le répertoire `config/` du module. Les clés
principales sont les noms d'action sans le préfixe `execute` (`index` pour la
méthode `executeIndex` par exemple). Un partial ou un component peut aussi être mis en cache
en utilisant son nom préfixé avec un underscore (`_`).

Pour déterminer si une action est mise en cache ou non, symfony regarde l'information
dans l'ordre suivant :

  * une configuration pour une action spécifique, un partial, or un component dans le
    fichier de configuration du module, si elle existe;

  * une configuration pour l'ensemble du module dans le fichier de configuration du module, si
    elle existe (sous la clé `all`);

  * la configuration par défaut de l'application (sous la clé `default`).

>**CAUTION**
>Une requête entrante avec les paramètres `GET` dans une chaine d'une query ou
>soumis avec la méthode `POST`, `PUT`, ou `DELETE` ne sera jamais mis
>en cache par symfony, quelque soit la configuration.

~`enabled`~
-----------

*Par défaut* : `false`

Le paramètre `enabled` active ou désactive le cache pour le périmètre courant.

~`with_layout`~
---------------

*Par défaut* : `false`

Le paramètre `with_layout` détermine si le cache doit être pour toute la
page (`true`), ou pour une action seulement (`false`).

>**NOTE**
>L'option `with_layout` n'est pas pris en compte pour la mise en cache du partial et
>du component car ils ne peuvent pas être décorés par une mise en page.

~`lifetime`~
------------

*Par défaut* : `86400`

Le paramètre  `lifetime` définit la durée de vie côté serveur du cache en
secondes (`86400` secondes, c'est égal à un jour).

~`client_lifetime`~
-------------------

*Par défaut* : Même valeur que `lifetime`

Le paramètre `client_lifetime` définit la durée de vie côté client du cache en
secondes.

Ce paramètre est utilisé pour définir automatiquement l'entête `Expires` et la
variable de contrôle du cache `max-cache`, à moins que `Last-Modified` ou l'entête `Expires`
a déjà été renseigné.

Vous pouvez désactiver la mise en cache côté client en définissant la valeur à `0`.

~`contextual`~
--------------

*Par défaut* : `false`

Le paramètre `contextual` détermine si le cache dépend du contexte de la page en cours
ou non. Le paramètre a donc du sens que lorsqu'il est utilisé pour
les partials et les components.

Lorsque l'affichage d'un partial est différent selon le template dans lequel il est
inclus, le partial est dit être contextuelle, et le paramètre `contextual`
doit être défini à `true`. Par défaut, le paramètre est défini à `false`, ce qui signifie
que l'affichage pour les partials et les components sont toujours les mêmes, partout où
ils sont inclus.

>**NOTE**
>Le cache est évidemment toujours différent pour un ensemble de paramètres différents.
