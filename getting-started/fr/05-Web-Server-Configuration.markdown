Configuration du serveur Web
========================

La pire méthode
------------

Dans les chapitres précédents, vous avez créé un répertoire qui héberge le projet.
Si vous l'avez créé quelque part dans le répertoire racine web de votre serveur
web, vous pouvez déjà accéder au projet dans un navigateur Web.

Bien sûr, comme il n'y a pas de configuration, il est très rapide à mettre en place, mais essayez
d'accéder au fichier `config/databases.yml` dans votre navigateur pour comprendre les
conséquences négatives d'une telle attitude paresseuse. Si l'utilisateur sait que votre site est
développé avec symfony, il aura accès à un grand nombre de fichiers sensibles.

**N'utilisez jamais cette configuration sur un serveur de production**, et lisez la section
suivante pour savoir comment configurer votre serveur web correctement

La méthode sécurisée
--------------

Une bonne pratique web est de mettre sous le répertoire racine du site Web que
les fichiers qui doivent être accessibles par un navigateur web, comme les feuilles de style, des Javascripts et
des images. Par défaut, nous vous recommandons de stocker ces fichiers sous le répertoire `web/`
du projet symfony et ses sous-répertoires.

Si vous jetez un oeil sur ce répertoire, vous trouverez plusiuers sous-répertoires pour
les ressources web (`css/` et `images/`) et deux fichiers de contrôlleur frontal. Les
contrôleurs frontaux sont seulement des fichiers PHP qui doivent être sous le répertoire
racine web. Tous les autres fichiers PHP peuvent être cachés au navigateur, c'est une bonne
idée en matière de sécurité.

### Configuration du serveur Web

Maintenant il est temps de changer votre configuration d'Apache, pour rendre le nouveau projet
accessible au monde.

Localisez et ouvrez le fichier de configuration `httpd.conf` et ajoutez la configuration
suivante à la fin :

    # Be sure to only have this line once in your configuration
    NameVirtualHost 127.0.0.1:8080

    # This is the configuration for your project
    Listen 127.0.0.1:8080

    <VirtualHost 127.0.0.1:8080>
      DocumentRoot "/home/sfproject/web"
      DirectoryIndex index.php
      <Directory "/home/sfproject/web">
        AllowOverride All
        Allow from All
      </Directory>

      Alias /sf /home/sfproject/lib/vendor/symfony/data/web/sf
      <Directory "/home/sfproject/lib/vendor/symfony/data/web/sf">
        AllowOverride All
        Allow from All
      </Directory>
    </VirtualHost>

>**NOTE**
>L'alias `/sf` vous donne accès à des images et des fichiers JavaScript nécessaire
>pour afficher correctement les pages Symfony par défaut et la barre d'outils web de débogage.
>
>Sur Windows, vous devez remplacer la ligne `Alias` avec quelque chose comme :
>
>     Alias /sf "c:\dev\sfproject\lib\vendor\symfony\data\web\sf"
>
>et `/home/sfproject/web` doit être remplacé par :
>
>     c:\dev\sfproject\web

Cette configuration permet Apache d'écouter le port 8080 sur votre machine, de sorte que
le site web sera accessible à l'adresse suivante:

    http://localhost:8080/

Vous pouvez changer `8080` par un autre nombre, mais favorisez les nombres plus grand que `1024` car
ils ne nécessitent pas de droits administrateur..

>**SIDEBAR**
>Configurer un nom de domaine dédié
>
>Si vous êtes un administrateur sur votre machine, il est préférable de configurer
>des serveurs virtuels plutôt que d'ajouter un nouveau port à chaque fois que vous démarrez un nouveau
>projet. Au lieu de choisir un port et d'ajouter une déclaration Listen,
>choisissez un nom de domaine (par exemple le nom du domaine réel avec
>`.localhost` ajouté à la fin) et ajouter une déclaration `ServerName` :
>
>     # This is the configuration for your project
>     <VirtualHost 127.0.0.1:80>
>       ServerName www.myproject.com.localhost
>       <!-- same configuration as before -->
>     </VirtualHost>
>
>Le nom du domaine `www.myproject.com.localhost` utilisé dans la configuration d'Apache
> doit être déclaré localement. Si vous utilisez un système Linux, il doit être
>fait dans le fichier `/etc/hosts`. 
>done in the `/etc/hosts` file. Si vous exécutez Windows XP, ce fichier
>se trouve dans le répertoire `C:\WINDOWS\system32\drivers\etc\`.
>
>Ajoutez la ligne suivante:
>
>     127.0.0.1 www.myproject.com.localhost

### Tester la nouvelle configuration

Redémarrez Apache, et vérifiez que vous avez maintenant accès à la nouvelle application en
ouvrant un navigateur et en tapant `http://localhost:8080/index.php/`, or
`http://www.myproject.com.localhost/index.php/` en fonction de la configuration d'Apache que
vous avez choisi dans la section précédente.

![Félicitations](http://www.symfony-project.org/images/getting-started/1_3/congratulations.png)

>**TIP**
>Si vous avez le module Apache `mod_rewrite` installé, vous pouvez retirer
>une partie de l'URL : `index.php/`. Ceci est possible grâce à la
>régle de reroutage configuré dans le fichier `web/.htaccess`.

Vous devriez également essayer d'accéder à l'application dans l'environnement de développement
(voir la section suivante pour plus d'informations sur les environnements). Tapez
l'adresse URL suivante:

    http://www.myproject.com.localhost/frontend_dev.php/

La barre d'outils web de débogage devrait apparaître dans le coin supérieur droit, incluant
de petites icônes, prouvant que la configuration de votre alias `sf/` est correct.

![La barre d'outils web de débogage](http://www.symfony-project.org/images/getting-started/1_3/web_debug_toolbar.png)

>**Note**
>La configuration est un peu différente si vous voulez faire tourner Symfony sur un serveur IIS dans
>un environnement de Windows. Vous trouverez la façon de le configurer dans le 
>[tutoriel dédié](http://www.symfony-project.com/cookbook/1_0/web_server_iis).
