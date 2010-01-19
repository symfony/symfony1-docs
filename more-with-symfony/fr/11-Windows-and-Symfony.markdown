Windows et symfony
===================

*Par Laurent Bonnet*

Introduction
------------

Ce chapitre est un nouveau tutoriel qui couvre, étape par étape, l'installation, le déploiement et les tests fonctionnels du framework symfony sur une plateforme Windows Server 2008.

Afin de préparer le déploiement sur Internet, le tutoriel peut être exécuté sur l'environnement d'un serveur dédié hébergé sur Internet. Bien sûr, il est possible de compléter ce tutoriel sur un serveur local, ou bien sur une machine virtuelle installée sur le poste de travail du lecteur.

### Les Raisons d'un Nouveau Tutoriel

Aujourd'hui, il existe seulement deux sources d'informations en rapport à Microsoft Internet Information Server (IIS) sur le [site](http://trac.symfony-project.org/wiki/symfonyOnIIS) [officiel](http://www.symfony-project.org/cookbook/1_2/en/web_server_iis) de symfony. Cependant, ces deux sources se rapportent à des versions précédentes qui n'ont plus évolué avec les nouvelles versions des systèmes d'exploitation de Microsoft Windows, particulièrement avec Windows Server 2008 (sorti en février 2008), qui inclut de nombreux changements intéressants pour les développeurs PHP :

 * IIS version 7, la version embarquée dans Windows Server 2008, a été entièrement réécrite en vue d'une architecture complètement modulaire.

 * IIS 7 a prouvé qu'il était très fiable, avec quelques correctifs intégrés dans Windows Update depuis le lancement du produit.

 * IIS 7 intègre également l'accélérateur FastCGI, un pool d'applications multi-processus qui tire profit du modèle natif de processus léger des systèmes d'exploitation de Windows.

 * L'implémentation de FastCGI de PHP améliore de 5 à 10 fois les performances à l'exécution, sans cache, comparé aux déploiements traditionnels ISAPI ou CGI de PHP sur Windows et IIS.

 * Plus récemment, Microsoft a levé le voile sur un accélérateur PHP, qui est encore à l'état de Release Candidate à l'heure où ces lignes sont écrites.

>**SIDEBAR**
>Extension Prévue pour ce Tutoriel
>
>Une section supplémentaire de ce chapitre est en cours d'élaboration et sera 
>publiée rapidement sur le site officiel du projet symfony après la publication 
>de cet ouvrage. Elle couvre la connexion à MS SQL Server via PDO et Microsoft 
>prévoit à l'heure actuelle des améliorations à venir à ce sujet.
>
>      [PHP_PDO_MSSQL]
>      extension=php_pdo_mssql.dll
>
>Pour l'instant, les meilleures performances à l'exécution du code sont obtenues 
>en utilisant le connecteur PHP 5 natif de SQL Server, un connecteur open-source 
>disponible sur Windows dans sa version 1.1. Il est implémenté sous forme d'une 
>nouvelle extension DLL pour PHP:
>
>      [PHP_SQLSRV]
>      extension=php_sqlsrv.dll
>
>Il est possible d'utiliser aussi bien Microsoft SQL Server 2005 ou 2008 pour la 
>base de données. L'extension prévue pour ce tutoriel couvrira l'utilisation de 
>l'édition gratuite : SQL Server Express.

### Comment Faire Fonctionner ce Tutoriel sur Différents Systèmes Windows (32 bits inclus)

Ce document a été écrit spécifiquement pour les éditions 64 bits de Windows Server 2008. Néanmoins, le lecteur sera capable d'utiliser d'autres versions sans complication supplémentaire.

>**NOTE**
>La version exacte du système d'exploitation utilisé dans les captures d'écran 
>est Windows Server 2008 Enterprise Edition accompagnée d'un Service Pack 2 pour 
>du matériel 64 bits.

#### Version 32 bits de Windows

Ce tutoriel est aisément portable sur des versions 32 bits de Windows, en remplaçant les références suivantes dans le texte :

 * Sur les éditions 64-bits : `C:\Program Files (x86)\` et `C:\Windows\SysWOW64\`

 * Sur les éditions 32-bits : `C:\Program Files\` et `C:\Windows\System32\`

#### A Propos des Versions non Enterprise

De plus, si le serveur n'utilise pas une version Enterprise, ce n'est pas un problème. Ce document est directement portable pour d'autres versions de logiciel Windows Server : Windows Server 2008 Web, Standard ou Datacenter Windows Server 2008 Web, Standard ou Datacenter avec Service Pack 2 Windows Server 2008 R2 Web, Standard, Enterprise ou Datacenter.

>**NOTE**
>Il est important de noter que toutes les éditions de Windows Server 2008 RC2 
>sont seulement disponibles pour les systèmes d'exploitation 64 bits.

#### A Propos des Versions Internationales

Les paramètres de configuration régionale utilisés dans ces captures d'écran sont `en-US` mais un pack d'internationalisation pour le Français aurait pu aussi être installé.

Il est également possible d'exécuter ce tutoriel sur un système d'exploitation Windows client : Windows XP, Windows Vista et Windows Seven, en mode x64 ou x86.

### Serveur Web utilisé tout au long du Document

Le serveur web utilisé ici est Microsoft Internet Information Server dans sa version 7.0, qui est inclus comme un rôle dans toutes les versions de Windows Server 2008. Ce tutoriel s'appuie sur un serveur Windows Server 2008 entièrement fonctionnel tandis qu'IIS sera installé "from scratch".

Les étapes d'installation utilisent les choix par défaut bien que deux autres modules spécifiques seront ajoutés : **FastCGI** et **URL Rewrite**. Ces derniers interviennent notamment dans l'architecture modulaire de IIS 7.0.

### Bases de Données

SQLite est la base de données pré-configurée dans un bac à sable symfony. Sur Windows, il n'y a rien de plus à installer particulièrement. En effet, le support de SQLite est directement implémenté dans l'extension PHP PDO pour SQLite, qui est d'ailleurs installée par défaut avec PHP. Par conséquent, il n'est pas nécessaire de télécharger et d'exécuter une instance séparée de SQLITE.EXE.

      [PHP_PDO_SQLITE]
      extension=php_pdo_sqlite.dll

### Configuration de Windows Server

Il est conseillé d'utiliser une installation récente de Windows Server dans le but de faire correspondre les captures d'écran aux étapes de ce chapitre avec l'écran du lecteur.

Bien sûr, travailler directement sur une machine existante est concevable bien que des différences puissent subsister à cause du système d'exploitation installé, de l'exécution ou bien des configurations régionales.

Afin d'obtenir les mêmes copies d'écran présentées dans ce tutoriel, il est recommandé au lecteur de se procurer un Windows Server dédié sur un environnement virtuel, disponible gratuitement sur Internet pour une période de 30 jours.

>**SIDEBAR**
>Comment obtenir un essai gratuit à Windows Server?
>
>Il est bien sûr possible d'utiliser n'importe quel serveur dédié avec un accès 
>à Internet. Un serveur physique ou un serveur virtuel dédié (VDS) fera 
>largement l'affaire.
>
>Un serveur limité 30 jours avec Windows est disponible à l'essai grâce à 
>Ikoula, un hébergeur web français qui offre une liste complète de services 
>pour les développeurs et les graphistes. Cet essai gratuit démarre bien sûr à 0 
>€ par mois pour une machine virtuelle Windows qui tourne sur un environnement 
>Microsoft Hyper-V.
>Par ailleurs, une machine virtuelle entièrement fonctionnelle, intégrant une 
>version Windows Server 2008 Web, Standard, Entreprise ou Datacenter, peut être 
>mise à disposition gratuitement pendant une période de 30 jours.
>
>Pour commander, il suffit de se connecter au site 
>http://www.ikoula.com/flex_server et de cliquer sur le bouton "Testez 
>gratuitement".
>
>Afin d'obtenir les mêmes messages décrits dans ce document, le système 
>d'exploitation commandé avec le serveur Flex est : "Windows Server 2008 
>Enterprise Edition 64 bits". Il s'agit d'une distribution x64, livrée avec les 
>deux locales `fr-FR` et `en-US`. Il est donc très facile de passer de `fr-FR` à 
>`en-US` et vice-versa depuis le panneau de commande "Windows Control Panel". 
>Plus précisément, ce paramètre se trouve dans "Regional and Language Options", 
>situées sous l'onglet "Keyboards and Languages". Il suffit de cliquer sur 
>"Install/uninstall languages".

Il est impératif de posséder les accès Administrateur sur le serveur.

Si le lecteur travaille depuis une station de travail distante, il devra alors  exécuter les Remote Desktop Services, plus connus sous le nom de Terminal Server Client en anglais, et s'assurer qu'il dispose des droits Administrateur.

La distribution utilisée ici est Windows Server 2008 accompagnée du Service Pack 2.

![Vérification de l'environnement de démarrage avec la commande Winver, en anglais ici](http://www.symfony-project.org/images/more-with-symfony/windows_01.png)

Windows Server 2008 a été installé avec l'environnement graphique, qui correspond au thème de Windows Vista. Il est également possible d'utiliser la ligne de commande uniquement sur les versions de Windows Server 2008 intégrant les mêmes services dans le but de réduire le poids de la distribution (1.5 Go au lieu de 6.5 Go). Cela réduit aussi les attaques en surface et le nombre de correctifs Windows Update qui ont besoin d'être appliqués.

Vérifications Préliminaires - Serveur Dédiés sur Internet
---------------------------------------------------------

Maintenant que le serveur est directement accessible depuis Internet, c'est aussi une bonne idée de vérifier que le pare-feu de Windows fournit une protection résidente active. Les seules exceptions à vérifier sont les suivantes :

 * Core Networking
 * Remote Desktop, (si atteint à distance)
 * Secure World Wide Web Services (HTTPS)
 * World Wide Web Services (HTTP)

![Vérification des paramètres du pare-feu directement depuis le panneau de contrôle.](http://www.symfony-project.org/images/more-with-symfony/windows_02.png)

Ensuite, il est toujours bon d'exécuter Windows Update afin de s'assurer que toutes les parties du système d'exploitation sont à jour avec les derniers correctifs, patches et documentation.

![Vérification du statut Windows Update directement depuis le panneau de contrôle.](http://www.symfony-project.org/images/more-with-symfony/windows_03.png)

En guise de dernière étape de préparation, et dans un souci de supprimer toutes les paramètres conflictuels potentiels de la configuration existante de Windows ou de IIS, il est recommandé de désinstaller le rôle Web du serveur Windows s'il était précédemment installé.

![Suppression du rôle Web Server depuis le gestionnaire Web Server.](http://www.symfony-project.org/images/more-with-symfony/windows_04.png)

Installer PHP - Quelques Clics Suffisent
----------------------------------------

A présent, IIS et PHP peuvent être installés en une opération triviale.

PHP n'est PAS une partie de la distribution Windows Server 2008, par conséquent, il faut tout d'abord installer Microsoft Web Platform Installer 2.0, abrégé Web PI dans les prochaines sections.

Web PI prend le soin d'installer toutes les dépendances nécessaires pour exécuter PHP sur n'importe quel système Windows / IIS. Par conséquent, il déploie IIS avec les Services de Rôle (Role Service) minimaux pour le Serveur Web (Web Server), et fournit les options minimales pour l'exécution de PHP.

![http://www.microsoft.com/web - téléchargez-le maintenant.](http://www.symfony-project.org/images/more-with-symfony/windows_05.png)

L'installation de Microsoft Web Platform Installer 2.0 contient un analyseur de configuration qui vérifie les modules existants, propose des modules de mises à jour nécessaires, et qui permet aussi de bêta-tester des extensions non publiées de la plateforme Microsoft Web Platform.

![Web PI 2.0 - Première Vue.](http://www.symfony-project.org/images/more-with-symfony/windows_06.png)

Web PI 2.0 offre une installation de PHP en un seul clic. La sélection installe l'implémentation Win 32 "non-thread safe" de PHP, qui est la meilleure associée à IIS 7 et FastCGI. Il offre également le binaire PHP le plus récent testé : ici 5.2.11. Pour le trouver, il suffit de sélectionner l'onglet "Frameworks and Runtimes" sur la gauche.

![Web PI 2.0 - Frameworks and Runtimes tab.](http://www.symfony-project.org/images/more-with-symfony/windows_07.png)

Après avoir choisi PHP, Web PI 2.0 sélectionne automatiquement toutes les dépendances nécessaires au service des pages web `.php` stockées sur le serveur, y compris les services minimaux de rôles de IIS 7.0:

![Web PI 2.0 - Dépendances ajoutées automatiquement - 1/3.](http://www.symfony-project.org/images/more-with-symfony/windows_08.png)

![Web PI 2.0 - Dépendances ajoutées automatiquement - 2/3.](http://www.symfony-project.org/images/more-with-symfony/windows_09.png)

![Web PI 2.0 - Dépendances ajoutées automatiquement - 3/3.](http://www.symfony-project.org/images/more-with-symfony/windows_10.png)

Ensuite, il suffit de cliquer sur "Install" puis de sélectionner le bouton "I Accept". L'installation des composants IIS commence, tandis que, en parallèle, le [binaire de PHP](http://windows.php.net) est téléchargé et quelques modules sont mis à jour. Une mise à jour de IIS 7.0 FastCGI par exemple.

![Web PI 2.0 - Installation des composants IIS en parallèle du téléchargement des mises à jour depuis le web.](http://www.symfony-project.org/images/more-with-symfony/windows_11.png)

Enfin, le programme de configuration de PHP s'exécute, et, après quelques minutes devrait afficher un écran similaire à la capture ci-dessous :

![Web PI 2.0 - Installation complète de PHP.](http://www.symfony-project.org/images/more-with-symfony/windows_12.png)

Il ne reste alors plus qu'à cliquer sur "Finish". Windows Server écoute à présent et est capable de répondre sur le port 80. La capture d'écran ci-dessous atteste de son bon fonctionnement.

![Firefox - IIS 7.0 répond sur le port 80.](http://www.symfony-project.org/images/more-with-symfony/windows_13.png)

Maintenant, pour vérifier que PHP est correctement installé et disponible depuis IIS, il suffit de créer un petit fichier `phpinfo.php` accessible sur le port 80 du serveur web par défaut. Ce fichier doit être créé dans le répertoire `C:\inetpub\wwwroot`.

Avant de faire cela, il convient de s'assurer que toutes les extensions des fichiers sont visibles dans l'Explorateur de Windows. Pour ce faire, il suffit de sélectionner l'option "Unhide Extensions for Known Files Types" dans les paramètres de l'explorateur.

![Explorateur Windows - Démasquer les extensions pour les types de fichiers connus.](http://www.symfony-project.org/images/more-with-symfony/windows_14.png)

La nouvelle étape consiste à ouvrir l'explorateur Windows afin de se rendre dans le dossier `C:\inetpub\wwwroot`. Puis, un clic droit sur l'option "New Text Document" permet de créer un nouveau fichier et de le renommer avec le nom `phpinfo.php`, avant de copier l'appel à la fonction PHP `phpinfo()` en guise de contenu.

![Explorateur Windows - Créer le fichier phpinfo.php.](http://www.symfony-project.org/images/more-with-symfony/windows_15.png)

Ensuite, il ne reste plus qu'à vérifier l'exécution du fichier dans le navigateur en ajoutant `/phpinfo.php` à la fin de l'URL du serveur.

![Firefox - phpinfo.php Exécution réussie !](http://www.symfony-project.org/images/more-with-symfony/windows_16.png)

Enfin, pour s'assurer que symfony s'installera sans aucun problème, il suffit de télécharger le fichier [http://sf-to.org/1.3/check.php](`check_configuration.php`) et de l'exécuter.

![PHP - Où télécharger  check.php.](http://www.symfony-project.org/images/more-with-symfony/windows_17.png)

Ce fichier doit alors être copié dans le même répertoire que `phpinfo.php` (`C:\inetpub\wwwroot`) et renommé en `check_configuration.php` si nécessaire.

![PHP - Copier et renommer check_configuration.php.](http://www.symfony-project.org/images/more-with-symfony/windows_18.png)

Enfin, il ne reste plus qu'à réouvrir le navigateur une dernière fois et d'ajouter `/check_configuration.php` à la fin de l'URL du serveur comme le montre la capture d'écran ci-dessous.

![Firefox - check_configuration.php Exécution réussie !.](http://www.symfony-project.org/images/more-with-symfony/windows_19.png)

Exécuter PHP depuis l'Interface en Ligne de Commande (CLI)
----------------------------------------------------------

Afin de pouvoir exécuter plus tard des tâches symfony en ligne de commande, il est nécessaire de s'assurer que PHP.EXE est accessible depuis l'invité de commande et qu'il s'exécute correctement. Pour ce faire, il suffit d'ouvrir un invité de commande, de se positionner dans le répertoire `C:\inetpub\wwwroot` et enfin de taper :

    PHP phpinfo.php

Le message d'erreur suivant devrait apparaître à l'écran :

![PHP - MSVCR71.DLL n'a pas été trouvé.](http://www.symfony-project.org/images/more-with-symfony/windows_20.png)

Si rien n'est fait, l'exécution de PHP.EXE tiendra du fait de l'absence du fichier `MSVCR71.DLL`. Ce fichier DLL doit être récupéré puis installé au bon endroit.

Le fichier `MSVCR71.DLL` est une ancienne version de Microsoft Visual C++ qui date d'avant 2003. Il est donc intégré dans le paquet redistribuable du Framework .Net 1.1.

Le paquet redistribuable du Framework .Net 1.1 est téléchargeable depuis le site 
[MSDN](http://msdn.microsoft.com/en-us/netframework/aa569264.aspx)

Le fichier recherché est installé dans le répertoire suivant :

`C:\Windows\Microsoft.NET\Framework\v1.1.4322`

Il suffit de copier le fichier `MSVCR71.DLL` dans le répertoire ci-dessous :

 * sur les systèmes x64 : le répertoire `C:\windows\syswow64` ;
 * sur les systèmes x86 : le répertoire `C:\windows\system32`.

A présent, le Framework .Net 1.1 peut être désinstallé.

L'exécutable PHP.EXE peut quant à lui être exécuté depuis l'invité de commande sans erreur. Par exemple :

    PHP phpinfo.php
    PHP check_configuration.php

Plus tard dans ce chapitre, il s'agira de vérifier que le fichier SYMFONY.BAT -  correspondant à la syntaxe de la commande symfony - de la distribution "sandbox" provoque lui aussi la réponse attendue.

Installation et Usage de la Sandbox Symfony
-------------------------------------------

Le paragraphe suivant est un résumé du tutoriel officiel "Getting Started with symfony" au sujet de ["la  Sandbox"](http://www.symfony-project.org/getting-started/1_3/en/A-Sandbox) : La sandbox, bac à sable en français, est un projet symfony pré-configuré et très facile d'installation, qui fonctionne en l'état avec des paramètres de configuration prédéfinis par défaut. C'est un excellent moyen de tester symfony sans pour autant se soucier d'une installation propre qui respecte les bonnes pratiques web.

La sandbox est pré-configurée à être utilisée avec SQLite en guise de moteur de base de données. Sur Windows, il n'y a rien de particulier à installer puisque le support de SQLite est directement implémenté dans l'extension PDO de PHP pour SQLite. Cette extension PDO est installée par défaut au moment de l'installation de PHP. Cette tâche a d'ores et déjà été accomplie plus tôt lorsque l'environnement d'exécution PHP a été installé via l'outil Microsoft Web PI.

A ce stade, il convient de vérifier que l'extension SQLite est correctement installée et référencée dans le fichier PHP.INI, qui réside dans le répertoire `C:\Program Files (x86)\PHP`. De plus, il faut s'assurer que le support de SQLite est activé en vérifiant que le connecteur PDO DLL adéquat est bien défini à la valeur `C:\Program Files (x86)\PHP\ext\php_pdo_sqlite.dll`.

![PHP - Emplacement du Fichier de Configuration php.ini.](http://www.symfony-project.org/images/more-with-symfony/windows_21.png)

### Préparer le Terrain pour symfony

Le projet initialisé dans le bac à sable de symfony est "prêt à être installé et exécuté". Il se présente sous la forme d'une archive `.zip`. L'[archive](http://www.symfony-project.org/get/sf_sandbox_1_3.zip) doit tout d'abord être téléchargée puis extraite dans un emplacement temporaire, comme le répertoire "Downloads", disponible en lecture et écriture dans le répertoire `C:\Users\Administrator`.

![sandbox - Téléchargement et décompression de l'archive.](http://www.symfony-project.org/images/more-with-symfony/windows_22.png)

Il s'agit à présent de créer un répertoire pour la destination finale du bac à sable, comme le dossier `F:\dev\sfsandbox`.

![sandbox - Création d'un Répertoire sfsandbox.](http://www.symfony-project.org/images/more-with-symfony/windows_23.png)

Ensuite, tous les fichiers doivent être sélectionnés - `CTRL-A` - dans l'explorateur Windows depuis l'emplacement de téléchargement (source), puis copiés dans le répertoire `F:\dev\sfsandbox` nouvellement créé. Ce n'est pas moins de 2599 objets qui sont copiés dans le répertoire final.

![sandbox - Copie des 2599 fichiers.](http://www.symfony-project.org/images/more-with-symfony/windows_24.png)

### Test de l'Exécution

Il convient maintenant de tester que l'installation du projet a réussi. Pour ce faire, il suffit d'ouvrir un nouvel invité de commande, puis de se positionner dans le répertoire `F:\dev\sfsandbox` afin d'exécuter la commande suivante.

    PHP symfony -V

L'exécution de cette commande devrait retourner ceci :

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

Depuis le même terminal de commande, la commande suivante peut aussi être exécutée :

    SYMFONY.BAT -V

Cette commande retourne aussi le même résultat :

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

![sandbox - Test Réussi de la Ligne de Commande.](http://www.symfony-project.org/images/more-with-symfony/windows_25.png)

### Création d'une Application Web

La création d'une application web sur le serveur local nécessite l'utilisation du gestionnaire de IIS7, qui est panneau de contrôle utilisateur graphique pour toutes les activités relatives à IIS. Toutes les actions déclenchées depuis cette interface graphique sont pour le moment exécutées en coulisses via l'interface en ligne de commande.

La console d'administration de IIS est accessible depuis le menu `Start` dans `Programs`, `Administrative Tools`, `Internet Information Server (IIS) Manager`.

#### Reconfigurer le Site Web par Défaut pour Eviter les Conflits sur le Port 80

Il s'agit maintenant de s'assurer que seule la sandbox symfony est capable de répondre sur le port 80 (HTTP). Pour y parvenir, le paramètre "Default Web Site" existant doit être modifié afin d'écouter sur le port 8080.

![IIS Manager - Paramétrage de "Default Web Site".](http://www.symfony-project.org/images/more-with-symfony/windows_26.png)

Si le pare-feu de Windows est activé, alors une exception pour le port 8080 devra être créée manuellement afin que la requête puisse être capable d'atteindre le "Default Web Site". Pour ce faire, il suffit de se rendre dans le panneau de contrôle de Windows, puis de sélectionner le pare-feu Windows, de cliquer sur "Allow a program through Windows Firewall", et enfin de cliquer sur "Add port" pour autoriser cette exception. Il ne reste plus qu'à cocher la case pour l'activer après sa création.

![Pare-Feu Windows - Créer une Exception pour le Port 8080.](http://www.symfony-project.org/images/more-with-symfony/windows_27.png)

#### Ajouter un nouveau Site Web pour la Sandbox

L'étape suivante consiste à ouvrir IIS Manager depuis le menu "Administration Tools". Dans le panneau de gauche, il convient de sélectionner l'icône "Sites" puis de faire un clic droit dessus et de choisir "Add Web Site" dans le menu ouvert. A présent, un nom doit être spécifié pour le site, par exemple "Symfony Sandbox" ainsi qu'un chemin physique, `D:\dev\sfsandbox`. Les autres champs peuvent rester tels qu'ils sont. La boîte de dialogue devrait ressembler à celle de la capture ci-dessous.

![IIS Manager - Ajouter un nouveau Site Web.](http://www.symfony-project.org/images/more-with-symfony/windows_28.png)

Après un clic sur OK, si un petit `x` apparaît sur l'icône du site web (dans Feature View / Sites), alors il ne faut pas hésiter à cliquer sur "Restart" dans le panneau de droite pour le faire disparaître.

#### Vérifier si le Site Web Répond

Depuis IIS Manager, il s'agit maintenant de sélectionner le site "Symfony Sandbox", et, dans le panneau de droite, de cliquer sur "Browse *.80 (http)".

![IIS Manager - Clic sur Browse port 80.](http://www.symfony-project.org/images/more-with-symfony/windows_29.png)

Bien que cela ne soit pas prévu, un message d'erreur explicite devrait apparaître à l'écran : `HTTP Error 403.14 - Forbidden`. Le serveur web est par défaut configuré pour empêcher le listage du contenu d'un répertoire.

Il s'agit de la configuration par défaut du serveur web qui spécifie que le contenu de ce répertoire ne doit pas être listé. Comme aucun fichier `index.php` ou `index.html` n'existe dans le répertoire `D:\dev\sfsandbox`, le serveur retourne normalement le message d'erreur "Forbidden". Il n'y a donc aucune raison de s'inquiéter.

![Internet Explorer - Erreur Classique.](http://www.symfony-project.org/images/more-with-symfony/windows_30.png)

Maintenant, en remplaçant `http://localhost` par `http://localhost/web` dans la barre d'adresse du navigateur, ce dernier, Internet Explorer par défaut, devrait afficher "Symfony Project Created" comme le montre la capture ci-dessous.

![IIS Manager - Résultat de l'URL http://localhost/web dans le navigateur!](http://www.symfony-project.org/images/more-with-symfony/windows_31.png)

Par ailleurs, il se peut qu'une barre jaune clair apparaisse en haut de la fenêtre indiquant "Intranet settings are now turned off by default". Cette alerte indique seulement que les paramètres de configuration Intranet sont moins sécurisés que les paramètres Internet. Là encore, il n'y a aucune raison de s'inquiéter et les options de l'alerte peuvent être sélectionnées en toute sécurité.

Pour fermer cette alerte de manière permanente, il suffit de faire un clic droit dessus, puis de sélectionner l'option appropriée.

Cet écran confirme que le fichier `index.php` par défaut a été correctement chargé depuis le chemin `D:\dev\sfsandbox\web\index.php`, puis exécuté et que les bibliothèques de symfony sont normalement configurées.

Il ne reste maintenant plus qu'une dernière tâche à réaliser avant de pouvoir véritablement jouer avec la sandbox de symfony : configurer la page web de l'application front-end en important les règles de réécriture d'URL. Ces règles sont implémentées dans les fichiers `.htaccess` et peuvent être contrôlées en quelques clics depuis IIS Manager.

### Sandbox : Configuration Web du Front-End

Il s'agit maintenant de configurer l'application front-end de la sandbox dans le but de commencer à jouer avec les véritables fonctionnalités de symfony. Par défaut, la page front-end peut être atteinte et exécutée correctement lorsqu'elle est appelée depuis la machine locale (par exemple, avec le nom `localhost` ou l'adresse `127.0.0.1`).

![Internet Explorer - frontend_dev.php page est accessible depuis localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_32.png)

Pour s'assurer que la sandbox est complètement fonctionnelle sur Windows Server 2008, il est possible d'explorer la configuration, les logs et les horodateurs dans les panneaux de la barre de débogage en haut à droite de l'écran.

![Utilisation de la sandbox : configuration.](http://www.symfony-project.org/images/more-with-symfony/windows_33.png)

![Utilisation de la sandbox : logs.](http://www.symfony-project.org/images/more-with-symfony/windows_34.png)

![Utilisation de la sandbox : horodateurs.](http://www.symfony-project.org/images/more-with-symfony/windows_35.png)

Bien que l'application sandbox puisse être accessible depuis Internet ou bien depuis une adresse IP locale, la sandbox est principalement structurée comme un outil d'apprentissage du framework symfony sur une machine locale. Par conséquent, ce chapitre couvrira des détails relatifs à l'accès distant dans une prochaine section.

Création d'un nouveau Projet symfony
------------------------------------

Créer un environnement de projet symfony pour de réelles intentions de développement est presque aussi simple qu'une installation à partir de la sandbox. Les lignes qui suivent décrivent le processus d'installation complet à partir d'une procédure simplifiée, dans la mesure où elle est équivalente à l'installation et au déploiement d'une sandbox.

La différence réside dans le fait que, pour cette section du "projet", c'est la configuration de l'application web qui est concernée afin de la faire fonctionner depuis n'importe où sur Internet.

Comme la sandbox, le projet symfony est pré-configuré par défaut pour utiliser SQLite en guise de moteur de base de données. Tout ceci a été installé et configuré plus tôt dans ce chapitre.

### Télécharger, Créer un Répertoire et Copier les Fichiers

Chaque version de symfony peut être téléchargée sous forme d'une archive .zip et ensuite utilisée pour créer un projet depuis zéro. Il suffit, pour ce faire, de télécharger l'archive contenant les bibliothèques depuis le [site officiel de symfony](http://www.symfony-project.org/get/symfony-1.3.0.zip). Ensuite, le contenu de l'archive peut alors être extrait dans une destination temporaire, comme le répertoire "downloads".

![Windows Explorer - Téléchargement et décompression de l'archive du projet.](http://www.symfony-project.org/images/more-with-symfony/windows_37.png)

A ce stade, l'objectif est de créer une arborescence complète pour la destination finale du projet. Cette étape est un peu plus difficile qu'avec la sandbox.

### Définition de l'Arborescence du Projet

Cette nouvelle section s'intéresse à la création d'une arborescence de fichiers pour le projet. Pour commencer, il convient de se positionner dans le volume racine, `D:` par exemple. Puis, un nouveau répertoire `\dev` doit être créé sur `D:` dans lequel il faut également créer un sous-répertoire `sfproject`.

    D:
    MD dev
    CD dev
    MD sfproject
    CD sfproject

Le pointeur de fichier se situe maintenant dans le répertoire `D:\dev\sfproject`.

A partir de cet emplacement, les sous-répertoires `lib`, `vendor` et `symfony` peuvent être créés en cascade.

    MD lib
    CD lib
    MD vendor
    CD vendor
    MD symfony
    CD symfony

Le pointeur de fichier est à présent dans `D:\dev\sfproject\lib\vendor\symfony`.

![Windows Explorer - arborescence d'un répertoire de projet.](http://www.symfony-project.org/images/more-with-symfony/windows_38.png)

Tous les fichiers doivent maintenant être sélectionnés (`CTRL-A` dans l'explorateur Windows) depuis l'emplacement de téléchargement (source), puis copiés depuis Downloads dans `D:\dev\sfproject\lib\vendor\symfony`. Ce sont environ 3819 fichiers qui ont été ainsi copiés dans le répertoire de destination finale.

![Windows Explorer - Copie des 3819 fichiers.](http://www.symfony-project.org/images/more-with-symfony/windows_39.png)

### Création et Initialisation

Il est temps d'ouvrir un nouvel invité de commande, puis de se positionner dans le répertoire `D:\dev\sfproject` juste avant d'exécuter la commande suivante.

    PHP lib\vendor\symfony\data\bin\symfony -V

Celle-ci devrait retourner le résultat ci-dessous :

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

L'exécution de la commande PHP ci-après suffit à elle-même pour initialiser un nouveau projet :

    PHP lib\vendor\symfony\data\bin\symfony generate:project sfproject

Le résultat de son exécution provoque à l'écran l'affichage d'une suite d'opérations sur des fichiers comprenant quelques commandes `chmod 777`.

![Windows Explorer - Initialisation du projet réussie.](http://www.symfony-project.org/images/more-with-symfony/windows_40.png)

Toujours à l'intérieur de l'invité de commande, il s'agit maintenant de créer une nouvelle application symfony en exécutant la commande suivante.

    PHP lib\vendor\symfony\data\bin\symfony generate:app sfapp

Là encore, le résultat consiste en une liste d'opérations sur les fichiers incluant des commandes `chmod 777`. D'ici, au lieu de taper `PHP lib\vendor\symfony\data\bin\symfony` chaque fois que c'est nécessaire, il suffit de copier le fichier `symfony.bat` de son point d'origine dans le répertoire racine du projet.

    copy lib\vendor\symfony\data\bin\symfony.bat

Maintenant, il existe une commande plus commode à exécuter dans l'invité en ligne de commande depuis le répertoire `D:\dev\sfproject`. Toujours dans le répertoire `D:\dev\sfproject`, la nouvelle commande peut être exécutée.

    symfony -V

afin d'obtenir la réponse traditionnelle attendue :

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

### Création d'une Application Web

Dans les lignes suivantes, le lecteur est supposé avoir lu les étapes préliminaires de création d'une application web de sandbox pour reconfigurer le site web par défaut ("Default Web Site"), afin qu'il n'interfère pas avec le port 80.

#### Ajout d'un nouveau Site Web pour ce Projet

Il s'agit maintenant d'ouvrir IIS Manager depuis les outils d'administration (Administration Tools) de Windows. Dans le panneau de gauche, il convient de sélectionner l'icône "Sites" puis d'effectuer un clic droit afin de choisir la valeur "Add Web Site" dans la nouvelle fenêtre de menu. Ensuite, le lecteur est invité à saisir un nom de site, par exemple "Symfony Project", ainsi qu'un chemin physique absolu, `D:\dev\sfproject`. Les autres champs, quant à eux, peuvent rester tels quels et la boîte de dialogue ci-dessous devrait alors faire son apparition.

![IIS Manager - Ajout d'un Site Web.](http://www.symfony-project.org/images/more-with-symfony/windows_41.png)

Après avoir cliqué sur OK, si une petite croix, `x`, apparaît sur l'icône du site web (dans Features View / Sites). Il ne faut pas hésiter à cliquer sur "Restart" dans le panneau de droite pour la faire disparaître.

#### Vérifier si le Site Web Répond

Depuis IIS Manager, il s'agit de sélectionner le site "Symfony Project", et, dans le panneau de droite, de cliquer "Browse *.80 (http)". Le même message explicite obtenu au moment de la configuration de la sandbox devrait apparaître à l'écran :

    HTTP Error 403.14 - Forbidden

Le serveur Web est toujours configuré pour ne pas afficher le contenu du répertoire. En tapant `http://localhost/web` dans la barre d'adresse du navigateur, une nouvelle page indiquant "Symfony Project Created" devrait apparaître. Cette dernière est légèrement différente de celle précédemment obtenue avec la sandbox dans la mesure où elle ne contient aucune image.

![Internet Explorer - Projet Symfony Créé - Aucune Image.](http://www.symfony-project.org/images/more-with-symfony/windows_42.png)

Les images ne sont pas présentes pour le moment, bien qu'elles existent dans le répertoire `/sf` dans les librairies de symfony. Il est facile de les relier au répertoire `/web` en ajoutant un répertoire virtuel dans `/web`, intitulé `sf`, et pointant vers `D:\dev\sfproject\lib\vendor\symfony\data\web\sf`.

![IIS Manager - Ajout du répertoire virtuel sf.](http://www.symfony-project.org/images/more-with-symfony/windows_43.png)

Désormais, le projet dispose de sa propre page d'accueil par défaut "Symfony Project Created" avec toutes les images chargées.

![Internet Explorer - Symfony Project Created - avec les images.](http://www.symfony-project.org/images/more-with-symfony/windows_44.png)

Et enfin, l'application symfony entière fonctionne. Pour s'en convaincre, il suffit de taper l'url de l'application web, `http://localhost/web/sfapp_dev.php`, dans le navigateur web.

![Internet Explorer - La page sfapp_dev.php est OK depuis localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_45.png)

Il ne reste alors plus qu'à effectuer un test en local : vérifier la configuration, les logs et les horodateurs dans les panneaux de la barre de débogage en haut de l'écran pour s'assurer que le projet est entièrement fonctionnel.

![Internet Explorer - La page de logs est OK depuis localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_46.png)

### Configuration des Applications Prêtes pour Internet

Le projet symfony générique fonctionne désormais localement comme la sandbox depuis le serveur local, situé à l'adresse `http://localhost` ou à l'adresse IP `http://127.0.0.1`. Maintenant, il est temps de rendre l'application accessible au grand public sur Internet.

La configuration par défaut du projet protège l'application d'être exécutée depuis un emplacement distant, bien qu'en réalité, elle devrait être autorisée à accéder aux deux fichiers `index.php` et `sfapp_dev.php`. Il s'agit donc d'exécuter le projet depuis un serveur web, en utilisant l'adresse IP externe du serveur (par exemple `94.125.163.150`) et le FQDN du serveur dédié virtuel (par exemple `12543hpv163150.ikoula.com`). Il est également possible d'utiliser les deux adresses depuis l'intérieur du serveur, dans la mesure où elle ne sont pas reliées à la boucle locale `127.0.0.1`.

![Internet Explorer - Accéder à index.php depuis Internet est OK.](http://www.symfony-project.org/images/more-with-symfony/windows_47.png)

![Internet Explorer - Exécution de sfapp_dev.php depuis Internet n'est PAS  OK.](http://www.symfony-project.org/images/more-with-symfony/windows_48.png)

L'accès aux fichiers `index.php` et `sfapp_dev.php` depuis un emplacement distant est désormais fonctionnel comme il l'a été expliqué avant. En revanche, l'exécution du fichier `sfapp_dev.php` échoue dans la mesure où il n'est pas autorisé par défaut. Cela empêche des utilisateurs malicieux d'accéder à l'environnement de développement qui contient des informations potentiellement sensibles à propos du projet. Le fichier `sfapp_dev.php` peut être édité pour le faire fonctionner depuis un emplacement distant mais c'est très fortement déconseillé pour des raisons évidentes de sécurité. Ce fichier ne devrait d'ailleurs jamais être déployé sur un serveur de production.

Enfin, il ne reste plus qu'à simuler un véritable nom de domaine en éditant le fichier `hosts`. Ce fichier s'occupe de résoudre le nom local FQDN sans avoir besoin d'installer un serveur DNS sur Windows. Le service DNS est disponible sur toutes les versions de Windows Server 2008 R2, et aussi dans Windows Server 2008 Standard, Enterprise et Datacenter.

Sur les systèmes d'exploitation x64, le fichier `hosts` est situé par défaut dans le répertoire `C:\Windows\SysWOW64\Drivers\etc`. Le fichier `hosts` est pré-rempli afin de résoudre le nom de domaine local `localhost` vers l'adresse IP `127.0.0.1`, en IPv4 et `::1` en IPv6.

Pour finir, un faux nom de domaine, tel que `sfwebapp.local` et résolu localement peut être ajouté au fichier.

![Changements appliqués aux fichiers des hôtes.](http://www.symfony-project.org/images/more-with-symfony/windows_50.png)

Le projet symfony s'exécute à présent sur le Web, sans aucun DNS, depuis une session du navigateur web exécutée sur le serveur web.