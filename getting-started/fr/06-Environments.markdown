Les environnements
================

Si vous regarder le répertoire `web/`, vous trouverez deux fichiers PHP :
`index.php` et `frontend_dev.php`. Ces fichiers sont appelé **contrôleurs
frontaux**; toutes les requêtes de l'application sont font par leur intermédiaire. Mais pourquoi
nous avons deux contrôleurs frontaux pour chaque application ?

Les deux fichiers pointent sur la même application mais pour des **environnements** différents.
Lorsque vous développez une application, sauf si vous développez directement sur le
serveur de production, vous avez besoin de plusieurs environnements :

  * L'**environnement de développement** : C'est l'environnement utilisé par les **développeurs
    Web** quand ils travaillent sur l'application pour ajouter de nouvelles fonctionnalités, corriger
    des bugs, ...

  * L'**environnement de test** : Cet environnement est utilisé pour tester automatiquement
    l'application.

  * L'**environnement de qualité** : Cet environnement est utilisé par le **client**
    pour tester l'application et et les bogues ou les fonctionnalités manquantes.

  * L'**environnement de production** : C'est l'environnement où interagissent les
    **utilisateurs finaux**

Qu'est ce qui rend un environnement unique ?  Dans l'environnement de développement par exemple,
l'application doit se connecter à tous les détails d'une requête afin de faciliter
le débogage, mais le système de cache doit être désactivé de façon que tous les changements apportés
au code soient pris en compte sans tarder. Ainsi, l'environnement de développement
doit être optimisé pour le développeur. Le meilleur exemple est certainement lorsqu'une
exception se produit. Pour aider le développeur à déboguer le problème plus rapidement, symfony
affiche l'exception avec toutes les informations qu'elle a sur la requête
courante dans le navigateur :

![Une exception dans l'environnement de dev](http://www.symfony-project.org/images/getting-started/1_4/exception_dev.png)

Par contre sur l'environnement de production, la couche du cache doit être activé, et
bien entendu, l'application doit afficher les messages d'erreurs à la place des
exceptions. Ainsi, l'environnement de production doit être optimisé pour la performance
et l'expérience utilisateur.

![Une exception dans l'environnement de prod](http://www.symfony-project.org/images/getting-started/1_4/exception_prod.png)

>**TIP**
>Si vous ouvrez les fichiers des contrôleurs frontaux, vous verrez que leur contenu est
>le même, sauf pour la configuration de l'environnement :
>
>     [php]
>     // web/index.php
>     <?php
>
>     require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
>
>     $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
>     sfContext::createInstance($configuration)->dispatch();

La barre d'outils de débogage Web est également un excellent exemple de l'utilisation de l'environnement. Elle
est présente sur toutes les pages dans l'environnement de développement et vous donne accès à
un grand nombre d'informations en cliquant sur les différents onglets : la configuration
de l'application actuelle, les journaux de la requête actuelle, les instructions
SQL exécutées sur le moteur de base de données, des informations sur la mémoire, et
l'heure.
