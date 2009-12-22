Le fichier de configuration generator.yml
====================================

L'admin generator de symfony permet la création d'une interface backend pour
vos classes de modèle. Il fonctionne si vous utilisez votre ORM Propel ou Doctrine.

### Création

Les modules de l'admin generator sont créés par la tâche `propel:generate-admin` ou
la tâche `doctrine:generate-admin` :

    $ php symfony propel:generate-admin backend Article

    $ php symfony doctrine:generate-admin backend Article

La commande ci-dessus crée un module `article` de l'admin generator pour la
classe modèle `Article`.

>**NOTE**
>Le fichier de configuration `generator.yml` est mis en cache dans un fichier PHP, le
>processus est automatiquement géré par la [classe](#chapter_14_config_handlers_yml)
>~`sfGeneratorConfigHandler`~.

### Le fichier de configuration

La configuration d'un tel module peut être fait dans le
fichier `apps/backend/modules/model/article/generator.yml` :

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        # Un tableau de paramètres

Le fichier contient deux entrées principales : `class` et `param`. La classe est
`sfPropelGenerator` pour Propel et `sfDoctrineGenerator` pour Doctrine.

L'entrée `param` contient les options de configuration pour le module généré.
Le `model_class` définit la classe du modèle lié à ce module, et l'option
`theme` définit le thème par défaut à utiliser.

Mais la configuration principale est faite en dessous l'entrée `config`. Il est organisé
en sept sections :

  * `actions`: La configuration par défaut pour les actions trouvées dans la liste et dans les formulaires
  * `fields`:  La configuration par défaut pour les champs
  * `list`:    La configuration pour la liste
  * `filter`:  La configuration pour les filtres
  * `form`:    La configuration pour les formulaires ajout/modification
  * `edit`:    La configuration spécifique pour la page modification
  * `new`:     La configuration spécifique pour la page ajout

Lors de la première génération, toutes les sections sont définies à vide, car l'admin
generator définit les valeurs raisonnables par défaut pour toutes les options possibles :

    [yml]
    generator:
      param:
        config:
          actions: ~
          fields:  ~
          list:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Ce document décrit toutes les options possibles que vous pouvez utiliser pour personnaliser l'admin
generator dans l'entrée `config`.

>**NOTE**
>Toutes les options sont disponibles pour Propel et Doctrine et fonctionnent de
>la même manière sauf si c'est indiqué autrement.

### Les champs

Beaucoup d'options ont une liste de champs comme argument. Un champ peut être un nom de
colonne réelle ou virtuelle. Dans les deux cas, une méthode de lecture (getter) doit être définie dans
la classe du modèle (`get` suffixé par le nom du champs en CamelCase).

Basé sur le contexte, l'admin generator est assez intelligent pour savoir comment
rendre des champs. Pour personnaliser le rendu, vous pouvez créer un partial ou un
component. Par convention, les partials sont préfixés par un caractère de soulignement (`_`) et
les components par un tilde (`~`) :

    [yml]
    display: [_title, ~content]

Dans l'exemple ci-dessus, le champs `title` sera rendu par le partial `title`,
et le champs `content` par le component `content`.

L'admin generator passe certains paramètres aux partials et aux components :

  * Pour les pages `new` et `edit` :

    * `form`:       Le formulaire associé à l'objet du modèle courant
    * `attributes`: Un tableau d'attribut HTML à appliquer pour le widget

  * Pour la page `list` :

    * `type`:       `list`
    * `MODEL_NAME`: L'instance de l'objet courant, où `MODEL_NAME` est le
                    nom de la classe du modèle en minuscules.

Dans les pages `edit` ou `new`, si vous souhaitez conserver la mise en page à deux colonnes (libellé
du champ et widget), le template du partial ou du component doit suivre ce
template :

    [php]
    <div class="sf_admin_form_row">
      <label>
        <!-- le libellé où le contenu du champ à afficher dans la première colonne -->
      </label>
      <!-- le widget où le contenu du champ à afficher dans la deuxième colonne -->
    </div>

### Les espaces réservés

Certaines options peuvent prendre des espaces réservés des objets du modèle. Un espace réservé est une chaîne
qui suit ce schéma : `%%NAME%%`. La chaîne `NAME` peut être tout ce qui
peut être convertie en une méthode getter (`get` suffixé par la
chaîne `NAME` en CamelCase). Par exemple, `%%title%%` sera
remplacé par la valeur de `$article->getTitle()`. La valeur de l'espace réservé est
dynamiquement remplacée à l'exécution selon l'objet associé avec le
contexte courant.

>**TIP**
>Quand un modèle a une clé étrangère vers un autre modèle, Propel et Doctrine
>définissent un getter pour l'objet correspondant. Comme pour tout autre getter, il
>peut être utilisé comme un espace réservé si vous définissez une méthode significative `__toString()`
>qui convertit l'objet en une chaîne de caractère.

### L'héritage de la configuration

La configuration de l'admin generator est basée sur le principes de configuration
en cascade. Les règles d'héritages sont les suivantes :

 * `new` et `edit` hérite de `form` qui hérite de `fields`
 * `list` hérite de `fields`
 * `filter` hérite de `fields`

### ~Credentials~

Les actions dans l'admin generator (sur la liste et sur les formulaires) peuvent être cachés,
en utilisant l'option `credential` basée sur les informations d'identification des utilisateurs (voir ci-dessous).
Cependant, même si le lien ou le bouton n'apparaîssent pas, les actions doivent
toujours être correctement sécurisées d'un accès illicite. La gestion des autorisations dans
l'admin generator ne s'occupe que de l'affichage.

L'option `credential` peut aussi être utilisée pour masquer des colonnes d'une liste dans la page.

### Personnalisation des actions

Lorsque la configuration n'est pas suffisante, vous pouvez remplacer les méthodes générées :

 | Méthode                | Description
 | ---------------------- | -------------------------------------
 | `executeIndex()`       | L'action de la vue `list`
 | `executeFilter()`      | Met à jour les filtres
 | `executeNew()`         | L'action de la vue `new`
 | `executeCreate()`      | Crée un nouvel enregistrement
 | `executeEdit()`        | L'action de la vue `edit`
 | `executeUpdate()`      | Met à jour l'enregistrement
 | `executeDelete()`      | Supprime l'enregistrement
 | `executeBatch()`       | Exécute l'action batch
 | `executeBatchDelete()` | Exécute l'action batch `_delete`
 | `processForm()`        | Traite le formulaire de l'enregistrement
 | `getFilters()`         | Retourne les filtres en cours
 | `setFilters()`         | Définit les filtres
 | `getPager()`           | Retourne la liste du pager
 | `getPage()`            | Obtient la page du pager
 | `setPage()`            | Définit la page du pager
 | `buildCriteria()`      | Construit le `Criteria` pour la liste
 | `addSortCriteria()`    | Ajoute le tri à `Criteria` pour la liste
 | `getSort()`            | Retourne la colonne triée
 | `setSort()`            | Définit la colonne triée

### Personnalisation des templates

Chaque template généré peut être substitué :

 | Template                     | Description
 | ---------------------------- | -------------------------------------
 | `_assets.php`                | Rend le CSS et JS à utiliser pour les templates
 | `_filters.php`               | Rend la zone des filtres
 | `_filters_field.php`         | Rend un champ de filtre unique
 | `_flashes.php`               | Rend les messages flashes
 | `_form.php`                  | Affiche le formulaire
 | `_form_actions.php`          | Affiche le formulaire des actions
 | `_form_field.php`            | Affiche un champ de formulaire seul
 | `_form_fieldset.php`         | Affiche le formulaire fieldset
 | `_form_footer.php`           | Affiche le formulaire de pied de page
 | `_form_header.php`           | Affiche le formulaire d'entête
 | `_list.php`                  | Affiche la liste
 | `_list_actions.php`          | Affiche la liste des action
 | `_list_batch_actions.php`    | Affiche la liste des actions batch
 | `_list_field_boolean.php`    | Affiche un seul champ booléen dans la liste
 | `_list_footer.php`           | Affiche la liste du pied de page
 | `_list_header.php`           | Affiche la liste de l'entête
 | `_list_td_actions.php`       | Affiche l'objet des actions pour une ligne
 | `_list_td_batch_actions.php` | Affiche le checkbox pour une ligne
 | `_list_td_stacked.php`       | Affiche le stacked layout pour une ligne
 | `_list_td_tabular.php`       | Affiche un seul champ pour la liste
 | `_list_th_stacked.php`       | Affiche le nom d'une seule colonne pour l'entête
 | `_list_th_tabular.php`       | Affiche le nom d'une seule colonne pour l'entête
 | `_pagination.php`            | Affiche la pagination
 | `editSuccess.php`            | Affiche la vue `edit`
 | `indexSuccess.php`           | Affiche la vue `list`
 | `newSuccess.php`             | Affiche la vue `new`

### Personnaliser l'apparence

Le look de l'admin generator peut être modifié très facilement car les templates
générés définissent beaucoup d'attributs HTML `class` et `id`.

Dans la page `edit` ou `new`, chaque conteneur de champ HTML ont les classes
suivantes :

  * `sf_admin_form_row`
  * une classe en fonction du type de champs : `sf_admin_text`, `sf_admin_boolean`,
    `sf_admin_date`, `sf_admin_time`, ou `sf_admin_foreignkey`.
  * `sf_admin_form_field_COLUMN` où `COLUMN` est le nom de la colonne

Dans la page `list`, chaque conteneur de champs ont les classes suivantes :

  * une classe en fonction du type de champs : `sf_admin_text`, `sf_admin_boolean`,
    `sf_admin_date`, `sf_admin_time`, ou `sf_admin_foreignkey`.
  * `sf_admin_form_field_COLUMN` où `COLUMN` est le nom de la colonne

<div class="pagebreak"></div>

Options de configurations disponibles
-------------------------------

 * [`actions`](#chapter_06_actions)

   * [`label`](#chapter_06_sub_label)
   * [`action`](#chapter_06_sub_action)
   * [`credentials`](#chapter_06_sub_credentials)

 * [`fields`](#chapter_06_fields)

   * [`label`](#chapter_06_sub_label)
   * [`help`](#chapter_06_sub_help)
   * [`attributes`](#chapter_06_sub_attributes)
   * [`credentials`](#chapter_06_sub_credentials)
   * [`renderer`](#chapter_06_sub_renderer)
   * [`renderer_arguments`](#chapter_06_sub_renderer_arguments)
   * [`type`](#chapter_06_sub_type)
   * [`date_format`](#chapter_06_sub_date_format)

 * [`list`](#chapter_06_list)

   * [`title`](#chapter_06_sub_title)
   * [`display`](#chapter_06_sub_display)
   * [`hide`](#chapter_06_sub_hide)
   * [`layout`](#chapter_06_sub_layout)
   * [`params`](#chapter_06_sub_params)
   * [`sort`](#chapter_06_sub_sort)
   * [`max_per_page`](#chapter_06_sub_max_per_page)
   * [`pager_class`](#chapter_06_sub_pager_class)
   * [`batch_actions`](#chapter_06_sub_batch_actions)
   * [`object_actions`](#chapter_06_sub_object_actions)
   * [`actions`](#chapter_06_sub_actions)
   * [`peer_method`](#chapter_06_sub_peer_method)
   * [`peer_count_method`](#chapter_06_sub_peer_count_method)
   * [`table_method`](#chapter_06_sub_table_method)
   * [`table_count_method`](#chapter_06_sub_table_count_method)

 * [`filter`](#chapter_06_filter)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`form`](#chapter_06_form)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`edit`](#chapter_06_edit)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

 * [`new`](#chapter_06_new)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

<div class="pagebreak"></div>

`fields`
--------

La section `fields` définit la configuration par défaut de chaque champs. Cette
configuration est définie pour toutes les pages et peut être surchargé page par
page (`list`, `filter`, `form`, `edit`, e `new`).

### ~`label`~

*Par défaut* : Le nom de colonne humanisé

L'option `label` définit le label à utiliser pour le champs :

    [yml]
    config:
      fields:
        slug: { label: "URL shortcut" }

### ~`help`~

*Par défaut* : none

L'option `help` définit le texte d'aide à afficher pour le champs.

### ~`attributes`~

*Par défaut* : `array()`

L'option `attributes` définit l'attribut HTML à passer au widget :

    [yml]
    config:
      fields:
        slug: { attributes: { class: foo } }

### ~`credentials`~

*Par défaut* : none

L'option `credentials` définit l'identification des utilisateurs qui doivent
avoir l'affichage du champ. Les identifications sont seulement imposées pour la liste d'objet.

    [yml]
    config:
      fields:
        slug:      { credentials: [admin] }
        is_online: { credentials: [[admin, moderator]] }

>**NOTE**
>Les identifications doivent être définies avec les mêmes règles que dans
>le fichier de configuration `security.yml`.

### ~`renderer`~

*Par défaut* : none

L'option `renderer` définit le callback PHP à appeler pour rendre le champ. Si elle
est définie, elle remplace tout les autres drapeaux comme les partials ou les components.

Le callback est appelée avec la valeur du champs et les arguments définis
par l'option `renderer_arguments`.

### ~`renderer_arguments`~

*Par défaut* : `array()`

L'option `renderer_arguments` définit les arguments à passer au
callback PHP `renderer` lors du rendu du champs. Elle n'est utilisée que si l'option
`renderer` est définie.

### ~`type`~

Par défaut* : `Text` pour les colonnes virtuelles

L'option `type` définit le type de la colonne. Par défaut, symfony utilise le
type défini dans votre définition du modèle, mais si vous créez une colonne virtuelle, vous
pouvez surcharger le type par défaut `Text` par un autre type valide : 

  * `ForeignKey`
  * `Boolean`
  * `Date`
  * `Time`
  * `Text`
  * `Enum` (seulement disponible pour Doctrine)

### ~`date_format`~

Par défaut* : `f` 

L'option `date_format` définit le format à utiliser lors de l'affichage des dates. Il
peut être de plusieurs formats reconnus par la classe `sfDateFormat`. Cette option n'est pas
utilisée quand le champs est de type `Date`.

Les symboles suivants peuvent être utilisés pour le format :

 * `G`: Era 
 * `y`: year 
 * `M`: mon 
 * `d`: mday 
 * `h`: Hour12 
 * `H`: hours 
 * `m`: minutes 
 * `s`: seconds 
 * `E`: wday 
 * `D`: yday 
 * `F`: DayInMonth 
 * `w`: WeekInYear 
 * `W`: WeekInMonth 
 * `a`: AMPM 
 * `k`: HourInDay 
 * `K`: HourInAMPM 
 * `z`: TimeZone

`actions`
---------

Le framework définit plusieurs actions intégrées. Elles sont toutes préfixés par un
underscore (`_`). Chaque action peut être personnalisée avec les options décrites dans
cette section. Les mêmes options peuvent être utilisées pour définir une action dans
les entrées `list`, `edit`, ou `new`.

### ~`label`~

*Par défaut* : la clé de l'action

L'option `label` définit le libellé à utiliser pour l'action.

### ~`action`~

*Par défaut* : défini selon le nom de l'action

L'option `action` définit le nom de l'action à exécuter sans le préfixe
`execute`.

### ~`credentials`~

*Par défaut* : none

L'option `credentials` définit l'identification des utilisateurs qui doivent avoir l'affichage
de l'action.

>**NOTE**
>Les identifications doivent être définies avec les mêmes règles que dans
>le fichier de configuration `security.yml`.

`list`
------

### ~`title`~

*Par défaut* : Le nom humanisé de la classe du modèle suffixé avec "List"

L'option `title` définit le titre de la page de la liste.

### ~`display`~

*Par défaut* : Toutes les colonnes du modèle, dans l'ordre de leur définition dans
le fichier du schéma

L'option `display` définit un tableau de l'ordre d'affichage des colonnes dans
la liste.

Un signe égal (`=`) avant la colonne est une convention visant à convertir la chaîne en
un lien qui mène à la page `edit` de l'objet courant.

    [yml]
    config:
      list:
        display: [=name, slug]

>**NOTE**
>Voir également l'option `hide` permettant de cacher certaines colonnes.

### ~`hide`~

*Par défaut* : none

L'option `hide` définit les colonnes à cacher dans la liste. Au lieu de
spécifier les colonnes à afficher avec l'option `display`, il est
parfois plus rapide de cacher certaines colonnes :

    [php]
    config:
      list:
        hide: [created_at, updated_at]

>**NOTE**
>Si les deux options `display` et `hide` sont fournies, l'option
`hide` est ignorée.

### ~`layout`~

*Par défaut* : `tabular`

*Valeurs possibles*: ~`tabular`~ ou ~`stacked`~

L'option `layout` définit la mise en page à utiliser pour afficher la liste.

Avec la mise en page `tabular`, chaque valeur de colonne est dans sa propre colonne dans la table.

Avec la mise en page `stacked`, chaque objet est représenté par une unique chaine
qui est défini par l'option `params` (voir ci-dessous).

>**NOTE**
>L'option `display` est toujours nécessaire lorsque vous utilisez la mise en page `stacked` car
>elle définit les colonnes qui seront triables par l'utilisateur.

### ~`params`~

*Valeur par défaut* : none

L'option `params` est utilisée pour définir le modèle de chaîne HTML à utiliser lorsque vous
utilisez une mise en page `stacked`. Cette chaîne peut contenir des espaces réservés du modèle d'objet :

    [yml]
    config:
      list:
        params:  |
          %%title%% écrit par %%author%% et publié le %%published_at%%.

Un signe égal (`=`) avant la colonne est une convention visant à convertir la chaîne en
un lien qui mène à la page `edit` de l'objet courant.

### ~`sort`~

*Valeur par défaut* : none

L'option `sort` définit la colonne triée par défaut. C'est un tableau composé
de deux éléments : le nom de la colonne et l'ordre de tri : `asc` ou `desc` :

    [yml]
    config:
      list:
        sort: [published_at, desc]

### ~`max_per_page`~

*Valeur par défaut* : `20`

L'option `max_per_page` définit le nombre maximum d'objets à afficher sur
une page.

### ~`pager_class`~

*Valeur par défaut* : `sfPropelPager` pour Propel et `sfDoctrinePager` pour Doctrine

L'option `pager_class` définit la classe de pagination à utiliser lors de l'affichage
de la liste.

### ~`batch_actions`~

*Valeur par défaut* : `{ _delete: ~ }`

L'option `batch_actions` définit la liste des actions qui peuvent être exécutées
par la sélection d'un objet dans la liste.

Si vous ne définissez pas une `action`, l'admin generator cherchera une méthode
nommée dont le nom en CamelCase est préfixé par `executeBatch`.

La méthode exécutée a reçu les clés primaires des objets sélectionnés via le
paramètre de requête des `ids`.

>**TIP**
>La fonctionnalité des actions batch peut être désactivée en mettant dans l'option un
>tableau vide : `{}`

### ~`object_actions`~

*Valeur par défaut* : `{ _edit: ~, _delete: ~ }`

L'option `object_actions` définit la liste des actions qui peuvent être exécutées
sur chaque objet de la liste.

Si vous ne définissez pas une `action`, l'admin generator cherchera une méthode
nommée dont le nom en CamelCase est préfixé par `executeList`.

>**TIP**
>La fonctionnalité des actions batch peut être désactivée en mettant dans l'option un
>tableau vide : `{}`

### ~`actions`~

*Valeur par défaut* : `{ _new: ~ }`

L'option `actions` les actions qui ne prennent pas l'objet, comme la création
d'un nouvel objet.

Si vous ne définissez pas une `action`, l'admin generator cherchera une méthode
nommée dont le nom en CamelCase est préfixé par `executeList`.

>**TIP**
>La fonctionnalité des actions batch peut être désactivée en mettant dans l'option un
>tableau vide : `{}`

### ~`peer_method`~

*Valeur par défaut* : `doSelect`

L'option `peer_method` définit la méthode à appeler pour récupérer les objets à
afficher dans la liste.

>**CAUTION**
>Cette option existe seulement pour Propel. Pour Doctrine, utiliser l'option
>`table_method`.

### ~`table_method`~

*Valeur par défaut* : `doSelect`

L'option `table_method` définit la méthode à appeler pour récupérer les objets à
afficher dans la liste.

>**CAUTION**
>Cette option existe seulement pour Doctrine. Pour Propel, utiliser l'option
>`peer_method`.

### ~`peer_count_method`~

*Valeur par défaut* : `doCount`

L'option `peer_count_method` définit la méthode à appeler pour calculer le
nombre d'objets pour le filtre en cours.

>**CAUTION**
>Cette option existe seulement pour Propel. Pour Doctrine, utiliser
>l'option `table_count_method`.

### ~`table_count_method`~

*Valeur par défaut* : `doCount`

L'option `table_count_method` définit la méthode à appeler pour calculer le
nombre d'objets pour le filtre en cours.

>**CAUTION**
>Cette option existe seulement pour Doctrine. Pour Propel, utiliser
>l'option `peer_count_method`.

`filter`
--------

La section `filter` définit la configuration pour le formulaire de filtre
affiché sur la page liste.

### ~`display`~

*Valeur par défaut* : Tous les champs définis dans la classe du formulaire de filtre, dans l'ordre de
leur définition

L'option `display` définit la liste triée des champs à afficher.

>**TIP**
>Comme les champs du filtre sont toujours facultatifs, il n'y a pas besoin de surcharger
>la classe du formulaire de filtre pour configurer les champs à afficher.

### ~`class`~

*Valeur par défaut* : Le nom de la classe du modèle suffixé par `FormFilter`

L'option `class` définit la classe de formulaire à utiliser pour le formulaire `filter`.

>**TIP**
>Pour supprimer complètement la fonctionnalité de filtrage, définissez `class` à `false`.

`form`
------

La section `form` n'existe que comme solution de repli pour les sections `edit` et `new`
(voir les règles d'héritage dans l'introduction).

>**NOTE**
>Pour les sections du formulaire (`form`, `edit`, et `new`), les options `label` et `help`
>surchargent ceux définis dans les classes de formulaires.

### ~`display`~

*Valeur par défaut* : Tous les champs définis dans la classe du formulaire de filtre, dans l'ordre de
leur définition

L'option `display` définit la liste triée des champs à afficher.

Cette option peut également être utilisé pour organiser les champs en groupes :

    [yml]
    # apps/backend/modules/model/config/generator.yml
    config:
      form:
        display:
          Content: [title, body, author]
          Admin:   [is_published, expires_at]

La configuration ci-dessus définit deux groupes (`Content` et `Admin`), chacun
contenant un sous-ensemble des champs de formulaire.

>**CAUTION**
>Tous les champs définis dans le formulaire du modèle doivent être présents dans l'option
>`display`. Sinon, cela pourrait conduire à des erreurs de validation inattendue.

### ~`class`~

*Valeur par défaut* : Le nom de la classe du modèle suffixé par `Form`

L'option `class` définit la classe de formulaire à utiliser pour les pages `edit`
et `new`.

>**TIP**
>Même si vous pouvez définir une option `class` dans les sections `new` et `edit`,
>il est préférable d'utiliser une classe et prendre soin des différences
>en utilisant la logique conditionnelle.

`edit`
------

La section `edit` prend les mêmes options que la section `form`.

### ~`title`~

*Par défaut* : "Edit " suffixé par le nom humanisé de la classe du modèle

L'option `title` définit le titre d'entête de la page edit. Il peut contenir
des espaces réservés de l'objet du modèle.

### ~`actions`~

*Valeur par défaut* : `{ _delete: ~, _list: ~, _save: ~ }`

L'option `actions` définit les actions possibles lors de la soumission du formulaire.

`new`
-----

La section `new` prend les mêmes options que la section `form`.

### ~`title`~

*Par défaut* : "New " suffixé par le nom humanisé de la classe du modèle

L'option `title` définit le titre de la page new. Il peut contenir
des espaces réservés de l'objet du modèle.

>**TIP**
>Même si l'objet est nouveau, il peut avoir des valeurs par défaut que vous voulez
>afficher dans la partie du titre.

### ~`actions`~

*Valeur par défaut* : `{ _delete: ~, _list: ~, _save: ~, _save_and_add: ~ }`

L'option `actions` définit les actions possibles lors de la soumission du formulaire.
