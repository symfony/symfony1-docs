Prérequis
=============

Avant d'installer symfony, vous devez vérifier que tout est installé et configuré
correctement sur votre ordinateur. Prenez le temps de lire consciencieusement ce
chapitre et de suivre toutes les étapes nécessaires pour vérifier votre configuration, car cela
peut vous faire gagner du temps pour la suite des événements.

Logiciels tiers
--------------------

Tout d'abord, vous devez vérifier que votre ordinateur dispose d'un environnement
de travail convivial pour le développement web. Au minimum, vous avez besoin d'un serveur web (Apache,
par exemple), un moteur de base de données (MySQL, PostgreSQL, SQLite, ou tout
[PDO](http://www.php.net/PDO) compatible au moteur de base de données), et PHP 5.2.4 ou
plus.

Interface en ligne de commande
----------------------

Le framework Symfony est livré avec un outil en ligne de commande qui permet d'automatiser
beaucoup de travail pour vous. Si vous êtes un utilisateur d'un OS de type Unix,  vous vous sentirez
comme chez vous. Si vous utilisez un système Windows, il fonctionne également très bien, mais il vous
fraudra juste taper quelques commandes à l'invite de `cmd`.

>**Note**
>Les commandes shell Unix peuvent être pratique dans un environnement Windows.
>Si vous souhaitez utiliser des outils comme `tar`, `gzip` ou `grep` sous Windows, vous
>pouvez installer [Cygwin](http://cygwin.com/).
>Les aventureux peuvent aussi essayer
>[Windows Services for Unix](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx) de Microsoft.

Configuration PHP
-----------------

Comme les configurations PHP peuvent beaucoup varier d'un OS à un autre, ou même entre
les différentes distributions Linux, vous devez vérifier que votre configuration de PHP
est satisfaisante aux exigences minimales de symfony.

Premièrement, assurez-vous qu'au minimum PHP 5.2.4 est installé en utilisant la
fonction intégrée `phpinfo()` ou en exécutant `php -v` en ligne de commande. Soyez
conscient que sur certaines configurations, vous pouvez avoir deux versions différentes de PHP
installées : l'une pour la ligne de commande, et une autre pour le web.

Ensuite, téléchargez le script de contrôle de la configuration de symfony à l'adresse suivante :

    http://sf-to.org/1.4/check.php

Enregistrez le script quelque part sur la racine de votre répertoire Web actuel.

Lancez le script de contrôle de configuration en ligne de commande :

    $ php check_configuration.php

S'il y a un problème avec votre configuration PHP, l'affichage de la commande
va vous donner des conseils sur ce qu'il faut corriger et comment y remédier.

Vous devez également exécuter le contrôle à partir d'un navigateur et corriger les problèmes
qu'il pourrait découvrir. C'est parce que PHP peut avoir un fichier de configuration `php.ini` distinct
sur ces deux environnements, avec des réglages différents.

>**NOTE**
>N'oubliez pas de supprimer le fichier de votre répertoire racine Web
>par la suite.

-

>**NOTE**
>Si votre objectif est d'essayer symfony pendant quelques heures, vous pouvez installer
>le bac à sable symfony comme décrit dans l'[Annexe A](A-The-Sandbox). Si
>vous voulez initialiser un projet du monde réel ou que vous voulez en savoir plus
>sur symfony, continuez à lire.
