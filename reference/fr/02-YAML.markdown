Le format YAML
===============

La plupart des fichiers de configuration dans symfony sont dans le format YAML. Selon le site
officiel [YAML](http://yaml.org/), YAML est "un être humain respectueux de la sérialisation standard
des données pour tous les langages de programmation".

YAML est un langage simple qui décrit les données. Comme PHP, il a une syntaxe pour
les types simples comme les chaînes, les booléens, les décimaux ou les entiers. Mais contrairement à PHP, il
fait une différence entre les tableaux (séries) et les hachages (mappings).

Cette section décrit l'ensemble minimum des dispositifs que vous devrez employer pour utilser YAML
comme format des fichiers de configuration dans symfony, bien que le format de YAML soit capable
de décrire des structures de données beaucoup plus complexes.

Les scalaires
-------

La syntaxe des scalaires est similaire à la syntaxe PHP.

### Les chaines

    [yml]
    Une chaine en YAML

-

    [yml]
    'Une chaine avec des simples guillemets en YAML'

>**TIP**
>Dans une chaine avec des simples guillemets, un guillemet simple `'` doit être doublé :
>
>     [yml]
>     'Une simple guillemet '' dans une chaine avec des simples guillemets'

    [yml]
    "Une chaine avec des doubles guillemets en YAML\n"

Une chaine avec des guillemets sont utiles quand une chaîne commence ou se termine par un
ou plusieurs espaces pertinents.

>**TIP**
>Le style double guillemet fournit un moyen d'exprimer des chaînes arbitraires, en
>utilisant `\` pour les séquences d'échappement. Il est très utile lorsque vous avez besoin d'incorporer un
>`\n` ou un caractère unicode dans une chaine.

Lorsqu'une chaîne contient des sauts de ligne, vous pouvez utiliser le style littéral, grâce à
un pipe (`|`), pour indiquer que la chaîne va s'étaler sur plusieurs lignes. Dans
les littéraux, les nouvelles lignes sont conservées :

    [yml]
    |
      \/ /| |\/| |
      / / | |  | |__

Alternativement, les chaînes peuvent être écrites avec le style plié, dénoté par `>`,
où chaque saut de ligne est remplacé par un espace :

    [yml]
    >
      Il s'agit d'une très longue phrase
      qui s'étend sur plusieurs lignes dans le fichier YAML
      mais qui seront rendus sous forme de chaîne 
      sans retour à la ligne.

>**NOTE**
>Remarquez les deux espaces devant chaque ligne dans les exemples précédents. Ils
>n'apparaîtront pas dans les chaînes de PHP qui en résulte.

### Nombres

    [yml]
    # un entier
    12

-

    [yml]
    # un octal
    014

-

    [yml]
    # un hexadécimal
    0xC

-

    [yml]
    # un décimal
    13.4

-

    [yml]
    # un exposant
    1.2e+34

-

    [yml]
    # infini
    .inf

### Null

Les valeur Null en YAML peut être exprimé avec `null` ou `~`.

### Booléens

Les booléens en YAML sont exprimés avec `true` et `false`.

### Dates

YAML utilise le standard ISO-8601 pour exprimer les dates :

    [yml]
    2001-12-14t21:59:43.10-05:00

-

    [yml]
    # une simple date
    2002-12-14

Collections
-----------

Un fichier YAML est rarement utilisé pour décrire un simple scalaire. La plupart du temps, il
décrit une collection. Une collection peut être soit une série ou un mapping
d'élément. Les séries et les mappings sont tous les deux convertis en tableaux PHP.

Les séries utilisent le tiret suivi d'un espace (`- `) :

    [yml]
    - PHP
    - Perl
    - Python

L'équivalent en code PHP est le suivant :

    [php]
    array('PHP', 'Perl', 'Python');

Les mappings utilisent les deux-points suivi d'un espace (`: `) pour marquer chaque paire clé/valeur :

    [yml]
    PHP: 5.2
    MySQL: 5.1
    Apache: 2.2.20

Qui est équivalent au code PHP suivant :

    [php]
    array('PHP' => 5.2, 'MySQL' => 5.1, 'Apache' => '2.2.20');

>**NOTE**
>Dans un mapping, une clé peut être n'importe quel scalaire YAML valide.

Le nombre d'espace entre les deux-points et la valeur n'a pas d'importance, tant
qu'il y en a au moins un :

    [yml]
    PHP:    5.2
    MySQL:  5.1
    Apache: 2.2.20

YAML utilise l'indentation avec un ou plusieurs espaces pour décrire les collections imbriquées :

    [yml]
    "symfony 1.0":
      PHP:    5.0
      Propel: 1.2
    "symfony 1.2":
      PHP:    5.2
      Propel: 1.3

Ce YAML est équivalent au code PHP suivant :

    [php]
    array(
      'symfony 1.0' => array(
        'PHP'    => 5.0,
        'Propel' => 1.2,
      ),
      'symfony 1.2' => array(
        'PHP'    => 5.2,
        'Propel' => 1.3,
      ),
    );

Il y a une chose importante que vous devez vous rappeler lorsqu'on utilise l'indentation dans un
fichier YAML : *une indentation doit être faite avec un ou plusieurs espaces, mais jamais avec
les tabulations*.

Vous pouvez inclure des mappings dans des séries si vous le souhaitez ou vous pouvez inclure des séries
dans des mapping comme ceci :

    [yml]
    'Chapitre 1':
      - Introduction
      - Types
    'Chapitre 2':
      - Introduction
      - Helpers

YAML peut aussi utiliser des styles de flux pour des collections, en utilisant des indicateurs explicites
plutôt que l'identation pour dénoter le périmètre.

Une série peut être écrite comme une liste séparée par des virgules entre crochets
(`[]`) :

    [yml]
    [PHP, Perl, Python]

Un mapping peut être écrit comme une liste clés/valeurs séparée par des virgules au sein
d'accolades (`{}`) :

    [yml]
    { PHP: 5.2, MySQL: 5.1, Apache: 2.2.20 }

Vous pouvez également mélanger les styles pour parvenir à une meilleure lisibilité :

    [yml]
    'Chapter 1': [Introduction, Event Types]
    'Chapter 2': [Introduction, Helpers]

-

    [yml]
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 }
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

Commentaires
--------

Les commentaires peuvent être ajoutés en YAML en les préfixant avec un signe dièse (`#`) :

    [yml]
    # Commentaire sur une ligne
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 } # Commentaire en fin de ligne
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

>**NOTE**
>Les commentaires sont tout simplement ignorés par le parseur YAML et n'ont pas besoin d'être
>indenté en fonction du niveau actuel d'inclusion dans une collection.

Fichiers YAML dynamiques
------------------

Dans symfony, un fichier YAML peut contenir du code PHP qui est évaluée juste avant
que le parsing soit lancé :

    [php]
    1.0:
      version: <?php echo file_get_contents('1.0/VERSION')."\n" ?>
    1.1:
      version: "<?php echo file_get_contents('1.1/VERSION') ?>"

Faites attention à ne pas casser l'indentation. Gardez à l'esprit les conseils
suivants lors de l'ajout du code PHP dans un fichier YAML :

 * Les déclarations `<?php ?>` doivent toujours commencer une ligne ou être intégré à
   une valeur.

 * Si une déclaration `<?php ?>` finit une ligne, vous devez explicitement produire une nouvelle
   ligne ("\n").

<div class="pagebreak"></div>

Un long exemple complet
---------------------

L'exemple suivant illustre la syntaxe YAML expliqué dans cette section :

    [yml]
    "symfony 1.0":
      end_of_maintainance: 2010-01-01
      is_stable:           true
      release_manager:     "Gregoire Hubert"
      description: >
        This stable version is the right choice for projects
        that need to be maintained for a long period of time.
      latest_beta:         ~
      latest_minor:        1.0.20
      supported_orms:      [Propel]
      archives:            { source: [zip, tgz], sandbox: [zip, tgz] }

    "symfony 1.2":
      end_of_maintainance: 2008-11-01
      is_stable:           true
      release_manager:     'Fabian Lange'
      description: >
        This stable version is the right choice
        if you start a new project today.
      latest_beta:         null
      latest_minor:        1.2.5
      supported_orms:
        - Propel
        - Doctrine
      archives:
        source:
          - zip
          - tgz
        sandbox:
          - zip
          - tgz
