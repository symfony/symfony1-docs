Le fichier de configuration app.yml
==============================

Le framework symfony fournit un fichier de configuration intégré pour les paramètres
spécifiques de l'application, le fichier de configuration `app.yml`.

Ce fichier YAML peut contenir n'importe quel paramètre que vous voulez et qui semble raisonnable pour votre
application spécifique. Dans le code, ces paramètres sont disponibles via la
classe globale `sfConfig`, et les clés sont préfixés de la chaîne `app_` :

    [php]
    sfConfig::get('app_active_days');

Tous les paramètres sont préfixés par `app_` parce que la classe `sfConfig` donne également
accès aux [paramètres de symfony](#chapter_03_sub_parametres_de_configuration) et
aux [répertoires de projet](#chapter_03_sub_repertoires).

Comme indiqué dans l'introduction, le fichier `app.yml` est
[**sensible à l'environnement**](#chapter_03_sensibilisation_a_l_environnement), et bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade).

Le fichier de configuration `app.yml` est un endroit idéal pour définir les paramètres qui
changement selon l'environnement (une clé API par exemple), ou les paramètres
qui peuvent évoluer au fil du temps (une adresse email par exemple). Il est également le meilleur
endroit pour définir les paramètres qui doivent être modifiées par quelqu'un qui ne connait
pas nécessairement symfony ou PHP (un administrateur système par exemple).

>**TIP**
>Abstenez-vous d'utiliser `app.yml` pour grouper la logique d'application.

-

>**NOTE**
>Le fichier de configuration `app.yml` est mis en cache dans un fichier PHP, le processus est
>automatiquement géré par la [classe](#chapter_14_config_handlers_yml)
>~`sfDefineEnvironmentConfigHandler`~.
