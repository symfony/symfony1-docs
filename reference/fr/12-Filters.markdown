Le fichier de configuration filters.yml
==================================

Le fichier de configuration ~`filters.yml`~ décrit la chaine de filtre à
exécuter pour chaque requête.

Le fichier principal de configuration `filters.yml` pour une application peut être trouvé dans
le répertoire `apps/APP_NAME/config/`.

Comme indiqué dans l'introduction, le fichier `filters.yml` bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

Le fichier de configuration `filters.yml` contient une liste de définitions de
filtre nommé :

    [yml]
    FILTER_1:
      # Définition du filtre 1

    FILTER_2:
      # Définition du filtre 2

    # ...

Lorsque le contrôleur initialise la chaîne de filtre pour une requête, il lit le
fichier `filters.yml` et enregistre les filtres en recherchant le nom de la classe du
filtre (`class`) et les paramètres (`param`) utilisés pour configurer l'objet
du filtre :

    [yml]
    FILTER_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

Les filtres sont exécutés dans le même ordre où elles apparaissent dans le fichier
de configuration. Comme symfony exécute les filtres comme une chaîne, le premier filtre enregistré
est exécuté en premier et en dernier.

Le nom de `class` étend la classe de base `sfFilter`.

Si la classe filtre ne peut pas être chargées automatiquement, un chemin `file` peut être défini
et sera automatiquement inclus avant la création de l'objet du filtre :

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

Lorsque vous surchargez le fichier `filters.yml`, vous devez conserver tous les filtres
du fichier de configuration hérité :

    [yml]
    rendering: ~
    security:  ~
    cache:     ~
    execution: ~

Pour supprimer un filtre, vous devez le désactiver en réglant la clé `enabled` sur
`false` :

    [yml]
    FACTORY_NAME:
      enabled: false

Il y a deux noms spéciaux pour les filtres : `rendering` et `execution`. Ils sont
obligatoires et sont identifiés par le paramètre `type`. Le filtre `rendering`
doit toujours être identifié comme premier filtre et le filtre `execution`
devrait être le dernier :

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

    # ...

    execution:
      class:  sfExecutionFilter
      param:
        type: execution

>**NOTE**
>Le fichier de configuration `filters.yml` est mis en cache dans un fichier PHP, le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfFilterConfigHandler`~.

<div class="pagebreak"></div>

Filters
-------

 * [`rendering`](#chapter_12_rendering)
 * [`security`](#chapter_12_security)
 * [`cache`](#chapter_12_cache)
 * [`execution`](#chapter_12_execution)

`rendering`
-----------

*Configuration par défaut* :

    [yml]
    rendering:
      class: sfRenderingFilter
      param:
        type: rendering

Le filtre `rendering` est responsable de la sortie de la réponse au
navigateur. Comme il est doit être le premier filtre déclaré, il est aussi le dernier
à avoir une chance de gérer la requête.

`security`
----------

*Configuration par défaut* :

    [yml]
     security:
       class: sfBasicSecurityFilter
       param:
         type: security

Le filtre `security` vérifie la sécurité en appelant la méthode `getCredential()`
de l'action. Une fois que le credential a été acquis, il vérifie que
l'utilisateur a le même credential en appelant la méthode `hasCredential()` de
l'objet utilisateur.

Le filtre `security` doit avoir un type `security`.

Une configuration plus fine du filtre de sécurité est faite via
le [fichier](#chapter_08) de configuration `security.yml`.

>**TIP**
>Si l'action demandée n'est pas configuré comme sécurisée dans `security.yml`, le
>filtre de sécurité ne sera pas exécuté.

`cache`
-------

*Configuration par défaut* :

    [yml]
    cache:
      class: sfCacheFilter
      param:
        condition: %SF_CACHE%

Le filtre `cache` gère la mise en cache pour les actions et les pages.  Il est également
responsable de l'ajout des entêtes cache HTTP nécessaires à la réponse
(`Last-Modified`, `ETag`, `Cache-Control`, `Expires`, ...).

`execution`
-----------

*Configuration par défaut* :

    [yml]
    execution:
      class:  sfExecutionFilter
      param:
        type: execution

Le filtre `execution` est au centre de la chaîne des filtres et fait tout l'action
et l'exécution de vue.

Le filtre `execution` doit être le filtre déclaré en dernier.
