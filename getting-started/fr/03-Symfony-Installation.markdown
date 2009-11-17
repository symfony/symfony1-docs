Installation de symfony
====================

L'initialisation du répertoire du projet
----------------------------------

Avant d'installer symfony, vous devez d'abord créer un répertoire qui sera l'hôte
de tous les fichiers liés à votre projet :

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Ou sur Windows:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Les utilisateurs de Windows sont invités pour exécuter symfony, à mettre en place leur nouveau
>projet dans un chemin qui ne contient pas d'espaces.
>Evitez d'utiliser le répertoire `Documents and Settings`, y compris n'importe où
>sous `Mes Documents`.

-

>**TIP**
>Si vous créez le répertoire du projet symfony sous le répertoire racine
>web, vous n'aurez pas besoin de configurer votre serveur web. Bien sûr, pour
>les environnements de production, nous vous conseillons fortement de configurer votre serveur web,
>comme expliqué dans la section configuration du serveur web.

Choisir la version de symfony
----------------------------

Maintenant, vous devez installer symfony. Comme le framework symfony dispose de plusieurs versions
stables, vous devez choisir celle que vous souhaitez installer en lisant la
[page d'installation](http://www.symfony-project.org/installation) sur le
site de symfony.

Ce tutoriel suppose que vous voulez installer symfony 1.3.

Choisir le lieu d'installation de symfony
-------------------------------------------

Vous pouvez installer symfony globalement sur votre machine, ou l'intégrer à chacun de
vos projets. Cette dernière est celle qui est recommandée car les projets seront alors
totalement indépendants les uns des autres. En mettant à niveau votre installation de symfony localement,
cela ne cassera pas vos projets de manière inattendue. Cela signifie que vous pouvez
avoir des projets avec différentes versions de symfony, et les mettre à jour les un après les autres
comme bon vous semble.

La meilleur pratique : beaucoup de gens installent les fichiers du framework symfony dans le
répertoire `lib/vendor` du projet. Donc, premièrement, créez ce répertoire :

    $ mkdir -p lib/vendor

Installation de symfony
------------------

### Installation à partir d'une archive

Le moyen le plus simple d'installer symfony est de télécharger l'archive pour la version
que vous avez choisie sur le site de symfony. Aller à la page d'installation de la
version que vous venez de choisir, symfony
[1.3](http://www.symfony-project.org/installation/1_3) par exemple.

Sous la section "**Source Download**", vous trouverez l'archive sous le format `.tgz`
ou `.zip`. Téléchargez l'archive, puis mettez-la sous le nouveau répertoire créé
`lib/vendor/`, décompressez la, et renommez le répertoire en `symfony` :

    $ cd lib/vendor
    $ tar zxpf symfony-1.3.0.tgz
    $ mv symfony-1.3.0 symfony
    $ rm symfony-1.3.0.tgz

Sous Windows, décompressez le fichier zip en utilisant l'Explorateur Windows.
Après avoir renommé le répertoire en symfony, vous devriez y avoir une structure
de répertoire similaire à `c:\dev\sfproject\lib\vendor\symfony`.

### Installation à partir de Subversion (recommandée)

Si vous utilisez Subversion, il est même préférable d'utiliser la propriété `svn:externals`
pour bien intégrer symfony dans votre projet dans le répertoire `lib/vendor/` :

    $ svn pe svn:externals lib/vendor/

Si tout va bien, cette commande va lancer votre éditeur favori pour vous donner
la possibilité de configurer les sources extérieures Subversion.

>**TIP**
>Sur Windows, vous pouvez utiliser des outils comme [TortoiseSVN](http://tortoisesvn.net/)
>pour tout faire sans avoir besoin d'utiliser la console.

Si vous êtes conservateur, liez votre projet à une version release (un tag de
subversion) :

    symfony http://svn.symfony-project.com/tags/RELEASE_1_3_0 

Chaque fois qu'une nouvelle version sort (comme annoncé sur le
[blog](http://www.symfony-project.org/blog/) de Symfony), vous devrez modifier l'URL
vers la nouvelle version.

Si vous voulez aller sur la route de l'avant-garde, utilisez la branche 1.3 :

    symfony http://svn.symfony-project.com/branches/1.3/

Grâce à l'utilisation de cette branche, votre projet bénéficie des corrections de bogues automatiquement
chaque fois que vous exécutez `svn update`.

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
