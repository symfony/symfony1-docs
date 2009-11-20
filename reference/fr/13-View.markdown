Le fichier de configuration view.yml
===============================

La mise en page de la Vue peut être configurée en éditant le fichier de
configuration ~`view.yml`~.

Comme indiqué dans l'introduction, le fichier `view.yml` bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

>**CAUTION**
>Ce fichier de configuration est la plupart du temps déprécié en faveur des helpers utilisés
>directement dans les templates ou dans les méthodes appelées par des actions.

Le fichier de configuration `view.yml` contient une liste de configurations de vue :

    [yml]
    VIEW_NAME_1:
      # configuration

    VIEW_NAME_2:
      # configuration

    # ...

>**NOTE**
>Le fichier de configuration `view.yml` est mis en cache dans un fichier PHP, le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfFilterConfigHandler`~.

Layout
------

*Configuration par défaut* :

    [yml]
    default:
      has_layout: true
      layout:     layout

Le fichier de configuration `view.yml` définit le ~layout~ par défaut utilisé par
l'application. Par défaut, le nom est `layout`, et ainsi symfony décore chaque
page avec le fichier `layout.php`, trouvé dans le répertoire de l'application
`templates/`. Vous pouvez également désactiver le processus de décoration tout en définissant
l'entrée `~has_layout~` à `false`.

>**TIP**
>La mise en page est automatiquement désactivée pour les requêtes XML HTTP et les types de contenu
>non-HTML, sauf s'il est explicitement défini pour la vue.

Stylesheets
-----------

*Configuration par défaut* :

    [yml]
    default:
      stylesheets: [main.css]

L'entrée `stylesheets` définit un tableau de feuilles de style à utiliser pour la vue
actuelle.

>**NOTE**
>L'inclusion des feuilles de style définies dans `view.yml` peut se faire soit
>manuellement avec le helper `include_stylesheets()`.

Si plusieurs fichiers sont définis, symfony va les inclure dans le même ordre que
la définition :

    [yml]
    stylesheets: [main.css, foo.css, bar.css]

Vous pouvez également modifier l'attribut `media` ou omettre le suffixe `.css` :

    [yml]
    stylesheets: [main, foo.css, bar.css, print.css: { media: print }]

Ce paramètre est *déprécié* en faveur du helper `use_stylesheet()` :

    [php]
    <?php use_stylesheet('main.css') ?>

>**NOTE**
>Dans le fichier de configuration par défaut `view.yml`, le fichier référencé est
>`main.css`, et pas `/css/main.css`. En fait, les deux définitions
>sont équivalentes car symfony préfixe les chemins relatifs avec `/css/`.

JavaScripts
-----------

*Configuration par défaut* :

    [yml]
    default:
      javascripts: []

L'entrée `javascripts` définit un tableau de fichiers JavaScript à utiliser pour
la vue actuelle.

>**NOTE**
>L'inclusion des fichiers Javascript définis dans `view.yml` peut se faire soit
>manuellement avec le helper `include_javascripts()`.

Si plusieurs fichiers sont définis, symfony va les inclure dans le même ordre que
la définition :

    [yml]
    javascripts: [foo.js, bar.js]

Vous pouvez également omettre le suffixe `.js` :

    [yml]
    javascripts: [foo, bar]

Ce paramètre est *déprécié* en faveur du helper `use_javascript()` :

    [php]
    <?php use_javascript('foo.js') ?>

>**NOTE**
>Lors de l'utilisation des chemins relatifs, comme `foo.js`, symfony le préfixe avec
>`/js/`.

Metas et Metas HTTP
--------------------

*Configuration par défaut* :

    [yml]
    default:
      http_metas:
        content-type: text/html

      metas:
        #title:        symfony project
        #description:  symfony project
        #keywords:     symfony, project
        #language:     en
        #robots:       index, follow

Les paramètres `http_metas` et `metas` permettent la définition des tags métas inclus
dans la mise en page.

>**NOTE**
>L'inclusion des tags métas définis dans `view.yml` peut se faire manuellement
>avec les helpers `include_metas()` et `include_http_metas()`.

Ce paramètre est *déprécié* en faveur du pur HTML dans la mise en page pour les métas
statiques (comme les types de contenu), ou en faveur du slot pour les métas dynamique (comme
le titre ou la description).

>**TIP**
>Quand cela a du sens, le méta HTTP `content-type` est automatiquement modifiée
>pour inclure le jeu de caractères défini dans le
>[fichier de configuration `settings.yml`](#chapter_04_sub_charset) s'il n'est pas déjà présent.
