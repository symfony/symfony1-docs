Webserver-Konfiguration
=======================

Der hässliche Weg
-----------------

In den vorangegangenen Kapiteln haben Sie ein Verzeichnis angelegt, welches das 
Projekt enthält. Wenn Sie es irgendwo innerhalb des Webroot-Verzeichnisses Ihres 
Web-Servers angelegt haben, können Sie nun das Projekt in einem Webbrowser 
aufrufen.

Da noch nichts konfiguriert wurde, geht das Einrichten natürlich sehr schnell - 
versuchen Sie aber mal die Datei `config/databases.yml` in Ihrem Browser 
aufzurufen, dann werden Sie die üblen Konsequenzen einer derart nachlässigen 
Herangehensweise verstehen. Wenn der User weiß, dass Ihre Website mit symfony 
entwickelt wurde, wird er Zugriff auf viele heikle Dateien haben.

**Verwenden Sie dieses Setup niemals auf einem Produktionsserver**, und lesen 
Sie den nächsten Abschnitt um zu lernen, wie Sie Ihren Web-Server korrekt 
konfigurieren.

Der sichere Weg
---------------

Eine gute Praxis im Web ist es, nur die Dateien in das Webroot-Verzeichnis zu 
stellen, auf die vom Browser aus zugegriffen werden muss, so z.B. Stylesheets, 
Java-Skripte und Grafiken. Als Standard empfehlen wir, diese Dateien im 
Unterverzeichnis `web/` eines symfony-Projekts abzulegen.

Wenn Sie einen Blick auf dieses Verzeichnis werfen, dann finden Sie einige 
Unterverzeichnisse mit Web-Dateien (`css/` und `images/`), sowie die Dateien 
für die beiden Front-Controller. Die Front-Controller sind die einzigen 
PHP-Dateien, die im Webroot-Verzeichnis stehen müssen. Alle anderen PHP-Dateien 
können vor dem Browser versteckt werden, was immer eine gute Idee ist, insofern 
Sicherheit eine Rolle spielt.

### Webserver-Konfiguration

Nun ist es an der Zeit, Ihren Apache so zu konfigurieren, dass Ihr Projekt der 
Welt zur Verfügung gestellt werden kann.

Lokalisieren und öffnen Sie die Datei `httpd.conf`, und fügen Sie die folgenden 
Einträge an das Ende an:

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
>Der Alias-Name `/sf` erlaubt Ihnen den Zugriff auf Grafik- und 
>JavaSkript-Dateien für die korrekte Darstellung der Default symfony-Seiten 
>und der Webdebug-Toolbar.
>
>Mit Windows müssen Sie die `Alias`-Zeile so (oder so ähnlich) ersetzen:
>
>     Alias /sf "c:\dev\sfproject\lib\vendor\symfony\data\web\sf"
>
>Und `/home/sfproject/web` sollte so ersetzt werden:
>
>     c:\dev\sfproject\web

Diese Konfiguration führt dazu, dass Apache den Port `8080` auf Ihrem Rechner 
abhört, die Website kann also über diese URL aufgerufen werden:

    http://localhost:8080/

Sie können statt `8080` jeden anderen Wert verwenden, bevorzugen Sie aber Werte 
größer als `1024`, da diese keine Administratorrechte erfordern.

>**SIDEBAR**
>Konfiguration eines bestimmten Domain-Namens
>
>Wenn Sie Administrator Ihres Rechners sind ist es besser, einen virtuellen Host 
>einzurichten, als für jedes Projekt einen neuen Port hinzuzufügen. Anstatt 
>einen Port und einen `Listen`-Befehl einzutragen, wählen Sie einen Domain-Namen 
>(z.B. den richtigen Domain-Namen gefolgt von `.localhost`) und fügen einen 
>`ServerName`-Befehl hinzu:
>
>     # This is the configuration for your project
>     <VirtualHost 127.0.0.1:80>
>       ServerName www.myproject.com.localhost
>       <!-- same configuration as before -->
>     </VirtualHost>
>
>Der Domain-Name `www.myproject.com.localhost` in der Apache-Konfiguration muss 
>lokal deklariert werden. Verwenden Sie ein Linux-System, machen Sie das in der 
>Datei `etc/hosts`. Bei Windows XP finden Sie diese Datei im Verzeichnis 
>`C:\WINDOWS\system32\drivers\etc\`.
>
>Fügen Sie diese Zeile ein:
>
>     127.0.0.1 www.myproject.com.localhost

### Test der neuen Konfiguration

Starten Sie Apache neu und prüfen Sie, ob Sie jetzt Zugriff auf die neue 
Anwendung haben, indem Sie einen Browser öffnen und - je nachdem welche 
Apache-Konfiguration Sie im vorigen Abschnitt verwendet haben - 
`http://localhost:8080/index.php/` oder 
`http://www.myproject.com.localhost/index.php/` eintippen.

![Congratulations](http://www.symfony-project.org/images/getting-started/1_4/congratulations.png)

>**TIP**
>Sollten Sie das Apache-Module `mod_rewrite` installiert haben, können Sie den 
>Teil `index.php/` in der URL weglassen. Durch die Umschreibe-Regel, die in der 
>Datei `web/.htaccess` konfiguriert wird, ist das möglich.

Sie sollten ebenso versuchen, die Anwendung in der Entwicklungsumgebung zu 
starten (s. nächstes Kapitel für mehr Informationen zum Thema Umgebungen). Geben 
Sie folgende URL ein:

    http://www.myproject.com.localhost/frontend_dev.php/

Die Webdebug-Toolbar sollte in der oberen rechten Ecke eingeblendet sein, 
einschließlich kleiner Icons, die belegen, dass Ihre Konfiguration des 
`sf/`-Alias korrekt ist.

![web debug toolbar](http://www.symfony-project.org/images/getting-started/1_4/web_debug_toolbar.png)

>**Note**
>Wenn Sie symfony auf einem IIS-Server in einer Windows-Umgebung betreiben 
>möchten, dann weicht das Setup leicht ab. Wie diese Konfiguration erfolgt finden 
>Sie im [entsprechenden Tutorial](http://www.symfony-project.com/cookbook/1_0/web_server_iis).