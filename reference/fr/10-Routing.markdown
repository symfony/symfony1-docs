Le fichier de configuration routing.yml
==================================

Le fichier de configuration `routing.yml` permet la définition des routes.

Le fichier de configuration principal `routing.yml` peut être trouvé dans
le répertoire `apps/APP_NAME/config/`.

Le fichier de configuration `routing.yml` contient une liste de définitions de route
nommées :

    [yml]
    ROUTE_1:
      # definition de la route 1

    ROUTE_2:
      # definition de la route 2

    # ...

Quand une requête arrive, le système de routage essaie de faire correspondre une route à une
URL entrante. La première route qui correspond gagne, ainsi l'ordre dans lequel les routes
sont définies dans le fichier de configuration est important.

Lorsque le fichier de configuration `routing.yml` est lu, chaque route est convertie en
un objet de la classe `class` :

    [yml]
    ROUTE_NAME:
      class: CLASS_NAME
      # configuration si la route

Le nom `class` doit étendre la classe de base `sfRoute`. S'il n'est pas fourni, la
classe de base `sfRoute` est utilisée comme solution de repli.

>**NOTE**
>Le fichier de configuration `routing.yml` est mis en cache dans un fichier PHP; le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfSecurityConfigHandler`~.

<div class="pagebreak"></div>

Les classes de la route
-------------

 * [Configuration principale](#chapter_10_configuration_de_la_route)

   * [`class`](#chapter_10_sub_class)
   * [`options`](#chapter_10_sub_options)
   * [`param`](#chapter_10_sub_param)
   * [`params`](#chapter_10_sub_params)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`type`](#chapter_10_sub_type)
   * [`url`](#chapter_10_sub_url)

 * [`sfRoute`](#chapter_10_sfroute)
 * [`sfRequestRoute`](#chapter_10_sfrequestroute)

   * [`sf_method`](#chapter_10_sub_sf_method)

 * [`sfObjectRoute`](#chapter_10_sfobjectroute)

   * [`allow_empty`](#chapter_10_sub_allow_empty)
   * [`convert`](#chapter_10_sub_convert)
   * [`method`](#chapter_10_sub_method)
   * [`model`](#chapter_10_sub_model)
   * [`type`](#chapter_10_sub_type)

 * [`sfPropelRoute`](#chapter_10_sfpropelroute)

   * [`method_for_criteria`](#chapter_10_sub_method_for_criteria)

 * [`sfDoctrineRoute`](#chapter_10_sfdoctrineroute)

   * [`method_for_query`](#chapter_10_sub_method_for_query)

 * [`sfRouteCollection`](#chapter_10_sfroutecollection)
 * [`sfObjectRouteCollection`](#chapter_10_sfobjectroutecollection)

   * [`actions`](#chapter_10_sub_actions)
   * [`collection_actions`](#chapter_10_sub_collection_actions)
   * [`column`](#chapter_10_sub_column)
   * [`model`](#chapter_10_sub_model)
   * [`model_methods`](#chapter_10_sub_model_methods)
   * [`module`](#chapter_10_sub_module)
   * [`object_actions`](#chapter_10_sub_object_actions)
   * [`prefix_path`](#chapter_10_sub_prefix_path)
   * [`requirements`](#chapter_10_sub_requirements)
   * [`route_class`](#chapter_10_sub_route_class)
   * [`segment_names`](#chapter_10_sub_segment_names)
   * [`with_show`](#chapter_10_sub_with_show)
   * [`with_wildcard_routes`](#chapter_10_sub_with_wildcard_routes)

 * [`sfPropelRouteCollection`](#chapter_10_sfpropelroutecollection)
 * [`sfDoctrineRouteCollection`](#chapter_10_sfdoctrineroutecollection)

<div class="pagebreak"></div>

Configuration de la route
-------------------

Le fichier de configuration `routing.yml` supporte plusieurs paramètres pour configurer
d'avantages les routes. Ces paramètres sont utilisé par la classe
`sfRoutingConfigHandler` pour convertir chaque route en un objet.

### ~`class`~

*Par défaut* : `sfRoute` (ou `sfRouteCollection` si `type` est à `collection`, voir ci-dessous)

Le paramètre `class` permet de modifier la classe de la route pour utiliser la route.

### ~`url`~

*Par défaut* : `/`

Le paramètre `url` est le modèle qui doit correspondre à une URL entrante pour la route
pour être utilisée par la requête courante.

Le modèle est constitué de segments :

 * variables (un mot préfixé avec [deux points `:`](#chapter_05_sub_variable_prefixes))
 * constantes
 * un astérisque (`*`) pour correspondre à une séquence de paire clé/valeur

Chaque segment doit être séparé par un des séparateurs prédéfinis
([`/` ou `.` par défaut](#chapter_05_sub_segment_separators)).

### ~`params`~

*Par défaut* : Un tableau vide

Le paramètre `params` définit un tableau de paramètres associé avec la route.
Ils peuvent être des valeurs par défaut pour les variables contenues dans `url`,  ou toute
autre variable pertinente pour cette route.

### ~`param`~

*Par défaut* : Un tableau vide

Ce paramètre est équivalent au paramètre `params`.

### ~`options`~

*Par défaut* : Un tableau vide

Le paramètre `options` est un tableau d'options qui seront transmis à l'objet route
pour personnaliser encore plus son comportement. Les sections suivantes décrivent les
options disponibles pour chaque classe route.

### ~`requirements`~

*Par défaut* : Un tableau vide

Le paramètre `requirements` est un tableau de conditions qui doivent être satisfaits par
les variables de `url`. Les clés sont les variables d'url et les valeurs sont des
expressions régulières dont les valeurs des variables doivent correspondre.

>**TIP**
>L'expression régulière sera inclus dans une autre expression
>régulière, et de ce fait, vous n'avez pas besoin de les envelopper entre
>des séparateurs, ni les lier avec `^` ou `$` pour correspondre à la
>valeur complète.

### ~`type`~

*Par défaut* : `null`

Si sa valeur est `collection`, la route sera lue comme une collection de route.

>**NOTE**
>Ce paramètre est automatiquement réglé sur `collection` par la classe de gestionnaire
>configuration si le nom de `class` contient le mot `Collection`. Cela signifie que la
>plupart du temps, vous n'avez pas besoin d'utiliser ce paramètre.

~`sfRoute`~
-----------

Toutes les classes de route étendent la classe de base `sfRoute`, qui fournit les
paramètres requis pour configurer une route.

~`sfRequestRoute`~
------------------

### ~`sf_method`~

*Par défaut* : `get`

L'option `sf_method` est utilisée dans le tableau `requirements`. Il fait appliquer
la requête HTTP dans la route correspondante au processus.

~`sfObjectRoute`~
-----------------

Toutes les options suivantes de `sfObjectRoute` doivent être utilisées dans le paramètre
`options` du fichier de configuration `routing.yml`.

### ~`model`~

L'option `model` est obligatoire et elle est le nom de la classe du modèle pour être
associée avec la route courante.

### ~`type`~

L'option `type` est obligatoire et elle est le type de route que vous voulez pour votre
modèle. Il peut être soit `object` ou `list`. Une route de type `object`
réprésente un unique objet du modèle, et une route de type `list` représente une
collection d'objets du modèle.

### ~`method`~

L'option `method` est obligatoire. C'est la méthode qui fait appel à la classe du modèle pour
retourner le(s) objet(s) associé(s) avec la route. Cela doit être une méthode
'static'. La méthode est appelée avec les paramètres de la route analysée comme
argument.

### ~`allow_empty`~

*Par défaut* : `true`

Si l'option `allow_empty` est à `false`, la route lèvera une exception
404 si aucun objet n'est retourné par l'appel de la `method` du `model`.

### ~`convert`

*Par défaut* : `toParams`

L'option `convert` est une méthode à appeler pour convertir un objet du modèle vers un tableau
de paramètres appropriés pour générer une route en fonction de cet objet du modèle. Elle
doit retournée un tableau avec au moins les paramètres nécessaires de la structure
de la route (comme défini par le paramètre `url`).

~`sfPropelRoute`~
-----------------

### ~`method_for_criteria`~

*Par défaut* : `doSelect` pour les collections, `doSelectOne` pour les objets unique

L'option `method_for_criteria` définit la méthode appelée sur la classe Peer
du modèle pour récupérer le(s) objet(s) associé(s) à la requête courante.
La méthode est appelée avec les paramètres de la route analysée comme un argument.

~`sfDoctrineRoute`~
-------------------

### ~`method_for_query`~

*Par défaut* : none

L'option `method_for_query` définit la méthode appelée sur la classe Peer
du modèle pour récupérer le(s) objet(s) associé(s) à la requête courante. L'objet de
la query courante est passé comme un argument.

Si l'option n'est pas définie, la requête est simplement «exécutée» par la méthode
`execute()`.

~`sfRouteCollection`~
---------------------

La classe de base `sfRouteCollection` représente une collection de routes.

~`sfObjectRouteCollection`~
---------------------------

### ~`model`~

L'option `model` est obligatoire et elle a le nom de la classe modèle pour être
associée avec la route courante.

### ~`actions`~

*Par défaut* : `false`

L'option `actions` définit un tableau d'actions autorisés pour le route. Les
actions doivent être un sous-ensemble de toutes les actions disponibles : `list`, `new`, `create`,
`edit`, `update`, `delete`, et `show`.

Si l'option est définie à `false`, la valeur par défaut, toutes les actions seront disponibles,
sauf pour celle de `show` si l'option `with_show` est définie à `false` (voir
ci-dessous). 

### ~`module`~

*Par défaut* : Le nom de la route

L'option `module` définit le nom du module.

### ~`prefix_path`~

*Par défaut* : `/` suivi par le nom de la route

L'option `prefix_path` définit un préfixe à ajouter avant toutes les modèles d'`url`. Cela
peut être n'importe quel modèle valable et peut contenir des variables et plusieurs segments.

### ~`column`~

*Par défaut* : `id`

L'option `column` définit la colonne du modèle à utiliser comme unique
identifiant pour l'objet du modèle.

### ~`with_show`~

*Par défaut* : `true`

L'option `with_show` est utilisée lorsque l'option `actions` est à `false` pour
déterminer si l'action `show` doit être incluse dans la liste des actions
autorisées pour la route.

### ~`segment_names`~

*Par défaut* : array('edit' => 'edit', 'new' => 'new'),

Le `segment_names` définit les mots à utiliser dans les modèles `url` pour
les actions `edit` et `new`.

### ~`model_methods`~

*Par défaut* : Un tableau vide

L'option `model_methods` définit les méthodes à appeler pour récupérer le(s)
objet(s) à partir du modèle (voir l'option de `method` de `sfObjectRoute`). Ceci est
en réalité un tableau définissant les méthodes `list` et `object`.

    [yml]
    model_methods:
      list:   getObjects
      object: getObject

### ~`requirements`~

*Par défaut* : `\d+` pour `column`

L'option `requirements` définit un tableau de conditions à appliquer aux
variables de route.

### ~`with_wildcard_routes`~

*Par défaut* : `false`

L'option `with_wildcard_routes` permet à toute action d'être accessible par deux
routes : une pour un seul objet, et une autre pour les collections d'objets.

### ~`route_class`~

*Par défaut* : `sfObjectRoute`

L'option `route_class` peut surchargée l'objet par défaut de la route utilisé pour une
collection.

### ~`collection_actions`~

*Par défaut* : Un tableau vide

L'option `collection_actions` définit un tableau d'actions supplémentaires
disponibles pour les routes de collection.

### ~`object_actions`~

*Par défaut* : Un tableau vide

L'option `object_actions` définit un tableau d'actions supplémentaires disponibles
pour les routes d'objet.

~`sfPropelRouteCollection`~
---------------------------

La classe de route `sfPropelRouteCollection` étend `sfRouteCollection`, et
change la classe de route par défaut par `sfPropelRoute` (voir l'option `route_class`
ci-dessus).

~`sfDoctrineRouteCollection`~
-----------------------------

La classe de route `sfDoctrineRouteCollection` étend `sfRouteCollection`,
et change la classe de route par défaut par `sfDoctrineRoute` (voir
l'option `route_class` ci-dessus).
