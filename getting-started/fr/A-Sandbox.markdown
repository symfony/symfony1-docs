Annexe A - Le bac à sable
========================

Si votre objectif est d'essayer symfony pendant quelques heures, continuez à lire ce
chapitre, nous allons vous montrer la méthode la plus rapide pour démarrer. Si vous voulez
amorcer un projet du monde réel, vous pouvez sans risque passer ce chapitre, et
[rejoindre](04-Symfony-Installation#chapter_04) tout de suite le prochain.

Le moyen le plus rapide pour expérimenter symfony est d'installer le bac à sable symfony.
Le bac à sable est une installation facile préemballée pour un projet symfony, il est déjà
configuré avec certaines valeurs par défaut. C'est une excellente façon de s'entraîner en utilisant
symfony sans les tracas d'une installation appropriée qui respecte les meilleures pratiques du Web.

>**CAUTION**
>Comme le bac à sable est pré-configuré pour utiliser SQLite
>comme moteur de base de données, vous devez vérifier que votre PHP supporte SQLite (voir le
>chapitre [Prérequis](02-Prerequisites#chapter_02)). Vous pouvez aussi
>lire la section [Configuration de la base de données](05-Project-Setup#chapter_05_sub_configuring_the_database)
>pour savoir comment modifier la base de données utilisée par le bac à sable.

Vous pouvez télécharger le bac à sable sous les formats `.tgz` ou `.zip` sur la
[page d'installation](http://www.symfony-project.org/installation/1_4) symfony
ou aux adresses suivantes:

    http://www.symfony-project.org/get/sf_sandbox_1_4.tgz

    http://www.symfony-project.org/get/sf_sandbox_1_4.zip

Décompresser les fichiers quelque part sous votre répertoire racine Web, et vous avez
terminé. Votre projet symfony est désormais accessible en lançant le script `web/index.php`
à partir d'un navigateur.

>**CAUTION**
>Avoir tous les fichiers symfony sous le répertoire racine web est très bien pour
>tester symfony sur votre ordinateur local, mais c'est une très mauvaise idée pour
>un serveur de production, car il permet potentiellement de voir toutes les entrailles de votre
>application par les utilisateurs finaux.

Vous pouvez finir votre installation en lisant les chapitres
[Configuration du serveur Web](06-Web-Server-Configuration#chapter_06)
et les [Environments](07-Environments#chapter_07).

>**NOTE**
>Comme le bac à sable est un projet symfony normal où certaines tâches ont
>été exécutées pour vous et où il y a peu de configuration à changer, il est assez
>facile de l'utiliser comme point de départ pour un nouveau projet. Cependant, gardez à l'esprit
>que vous aurez probablement besoin d'adapter la configuration : par exemple
>changer les paramètres liés à la sécurité (voir la configuration des attaques XSS
>et CSRF plus loin dans ce tutoriel).
