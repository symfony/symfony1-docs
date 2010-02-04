Installation du Projet
=============

Dans symfony, les **applications** partageant le même modèle de données sont regroupés dans des
**projets**. Pour la plupart des projets, vous avez deux applications différentes : un
frontend et un backend.

Création du projet
----------------

Depuis le répertoire `sfproject/`, exécuter la tâche symfony `generate:project` pour
créer le projet symfony:

    $ php lib/vendor/symfony/data/bin/symfony generate:project PROJECT_NAME

Sur Windows:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project PROJECT_NAME

La tâche `generate:project` génére la structure par défaut des répertoires et
les fichiers nécessaires pour un projet symfony :

 | Répertoire  | Description
 | ----------- | ----------------------------------
 | `apps/`     | Accueille toutes les applications du projet
 | `cache/`    | Les fichiers mis en cache par le framework
 | `config/`   | Les fichiers de configuration du projet
 | `data/`     | Les fichiers de données comme les jeux de données initiales
 | `lib/`      | Les bibliothèque et les classes du projet
 | `log/`      | Les fichiers log du framework
 | `plugins/`  | Les plugins installés
 | `test/`     | Les fichiers de test unitaire et fonctionnel
 | `web/`      | Le répertoire racine Web (voir ci-dessous)

>**NOTE**
>Pourquoi symfony génère autant de dossiers ? L'un des principaux avantages d'un
>framework full-stack consiste à normaliser les développements. Grâce à
>la structure par défaut des fichiers et des répertoires de symfony, tout développeur ayant
>une certaine connaissance de symfony peut prendre en charge la maintenance d'un projet symfony.
>En quelques minutes, il sera capable de parcourir le code, de corriger des
>bugs et d'ajouter de nouvelles fonctionnalités.

La tâche `generate:project` a également créé un raccourci `symfony` dans le
répertoire racine du projet pour diminuer le nombre de caractères que vous allez écrire
lors de l'exécution d'une tâche.

Ainsi, à partir de maintenant, au lieu d'utiliser le chemin complet pour le programme
symfony, vous pouvez utiliser le raccourci `symfony`.

### Vérification de l'installation

Maintenant que symfony est correctement installé, vérifiez la en utilisant la
ligne de commande de symfony pour afficher la version de symfony (notez que le `V` est en majuscule) :

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Sur Windows:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

L'option `-V` affiche également le chemin vers le répertoire d'installation de symfony,
qui est stocké dans `config/ProjectConfiguration.class.php`.

Si le chemin de symfony est absolue (ce qui ne devrait pas l'être par défaut si vous
suivez les instructions ci-dessus), changez-le de sorte qu'il soit lu comme l'exemple suivant
pour une meilleure portabilité :

    [php] 
    // config/ProjectConfiguration.class.php 
    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php'; 

De cette façon, vous pouvez déplacer le répertoire du projet n'importe où sur votre machine ou
une autre, et cela fonctionnera bien.

>**TIP**
>Si vous êtes curieux de savoir ce que cet outil en ligne de commande peut faire pour vous, tapez
>`symfony` pour lister les options et les tâches disponibles :
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Sur Windows:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>La ligne de commande symfony est le meilleur ami du développeur. Il fournit de nombreux
>utilitaires qui permettent d'améliorer votre productivité sur les activités quotidiennes comme
>le vidage du cache, la génération de code, et bien plus encore.

Configuration de la base de données
------------------------

Le framework Symfony supporte toutes les [PDO](http://www.php.net/PDO)-soutenus
par des bases de données (MySQL, PostgreSQL, SQLite, Oracle, MSSQL, ...) hors de la boîte. Au
sommet de PDO, symfony est livré avec deux outils ORM: Propel et Doctrine.

Lorsque vous créez un nouveau projet, Doctrine est activé par défaut. La configuration
de la base de données employée par Doctrine est aussi simple en utilisant la tâche `configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=dbname" root mYsEcret

La tâche `configure:database` comporte 3 arguments: le
[~PDO DSN~](http://www.php.net/manual/en/pdo.drivers.php), le nom de l'utilisateur, et
le mot de passe pour accéder à la base de données. Si vous n'avez pas besoin d'un mot de passe pour
accéder à votre base de donnée sur le serveur de développement, omettez simplement le troisième argument.

>**TIP**
>Si vous souhaitez utiliser Propel au lieu de Doctrine, ajoutez --orm=Propel lors de la création
>du projet avec la tâche `generate:project`. Et si vous ne voulez pas utiliser un
>ORM, passer juste --orm=none.

Création de l'application
--------------------

Maintenant, créez l'application frontend en exécutant la tâche `generate:app` :

    $ php symfony generate:app frontend

>**TIP**
>Parce que le raccourci symfony est exécutable, les utilisateurs Unix peuvent remplacer toutes
>les occurrences de '`php symfony`' par '`./symfony`' à partir de maintenant.
>
>Sur Windows vous pouvez copier le fichier '`symfony.bat`' vers votre projet et utilisez
>'`symfony`' à la place de '`php symfony`' :
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

Basé sur le nom de l'application donné en *argument*, la tâche `generate:app`
crée par défaut la structure du répertoire nécessaire à l'application sous le
répertoire `apps/frontend/` :

 | Répertoire   | Description
 | ------------ | -------------------------------------
 | `config/`    | Les fichiers de configuration de l'application
 | `lib/`       | Les bibliothèques et les classes de l'application
 | `modules/`   | Le code de l'application (MVC)
 | `templates/` | Les fichiers template globaux

>**SIDEBAR**
>Securité
>
>Par défaut, la tâche `generate:app` a sécurisé notre application sur les deux vulnérabilités
>les plus répandues que l'on trouve sur le web. C'est vrai, symfony
>prend automatiquement des mesures de ~sécurité|Sécurité~ à notre place.
>
>Pour prévenir des attaques ~XSS~, l'output escaping a été activé; et pour prévenir
>des attaques ~CSRF~, un secret CSRF a été créé aléatoirement.
>
>Bien sûr, vous pouvez modifier ces paramètres grâce aux *options* suivantes :
>
>  * `--escaping-strategy`: Active ou désactive l'output escaping
>  * `--csrf-secret`: active les jetons de session dans les formulaires
>
>Si vous ne savez rien sur
>[XSS](http://fr.wikipedia.org/wiki/Cross-site_scripting) ou
>[CSRF](http://fr.wikipedia.org/wiki/Cross-site_request_forgery), prenez le temps d'en
>apprendre d'avantage sur ces failles de sécurité.

Droits sur les répertoires structurés
--------------------------

Avant d'essayer d'accéder à votre projet nouvellement créé, vous devez configurer l'écriture
sur les répertoires `cache/` et `log/` à des niveaux appropriés,
afin que votre serveur web puisse écrire dedans :

    $ chmod 777 cache/ log/

>**SIDEBAR**
>Conseils pour les personnes utilisant un outil de SCM
>
>symfony écrit seulement dans deux répertoires du projet symfony :
>`cache/` et `log/`. Le contenu de ces répertoires peut-être ignoré
>par votre SCM (En utilisant la propriété `svn:ignore`, si vous utilisez Subversion
>par exemple).