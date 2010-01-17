Anhang A - Die Sandbox
======================

Sollten Sie vorhaben symfony für ein paar Stunden auszuprobieren, dann lesen Sie 
dieses Kapitel weiter, da wir Ihnen den schnellsten Weg zeigen werden, Sie 
starten zu lassen. Wenn Sie ein echtes Projekt von Null an beginnen, dann 
sollten Sie zum Kapitel [Installation](03-Symfony-Installation#chapter_03) 
springen.

Der schnellste Weg zum Experimentieren mit symfony ist die Installation der 
symfony Sandbox. Bei der Sandbox handelt es sich um ein kinderleicht zu 
installierendes, vorgefertigtes symfony-Projekt, das bereits mit einigen 
sinnvollen Standardwerten konfiguriert ist. Sie ist ein toller Weg, den Umgang 
mit symfony einzuüben, ohne den Ärger mit einer ordnungsgemäßen Installation zu 
haben, die die bewährten Methoden des Webs berücksichtigen muss.

>**CAUTION**
>Da die Sandbox zur Verwendung von SQLite als Datenbank-Engine vorkonfiguriert 
>ist, müssen Sie prüfen, ob Ihr PHP SQLite unterstützt (s. Kapitel 
>[Voraussetzungen](02-Prerequisites#chapter_02)). Sie können auch den Abschnitt 
>[Datenbank konfigurieren](04-Project-Setup#chapter_04_configuring_the_database) 
>lesen und lernen, wie man die von der Sandbox zu verwendende Datenbank ändert.

Sie können die symfony-Sandbox im `.tgz` oder `.zip` Format von der 
symfony-[Installationsseite](http://www.symfony-project.org/installation/1_4) 
oder von diesen URLs laden:

    http://www.symfony-project.org/get/sf_sandbox_1_4.tgz

    http://www.symfony-project.org/get/sf_sandbox_1_4.zip

Entpacken Sie die Dateien innerhalb Ihres Webroot-Verzeichnisses - und fertig. 
Ihr symfony-Projekt ist nun erreichbar durch Aufruf des Skripts `web/index.php` 
aus dem Browser heraus.

>**CAUTION**
>Alle symfony-Dateien im Webroot-Verzeichnis zu haben ist gut für das Testen von 
>symfony auf Ihrem lokalen Computer. Für einen Produktions-Server ist das aber 
>eine ziemlich schlechte Idee, weil es dem Anwender möglicherweise alle Internas 
>Ihrer Anwendung sichtbar macht.

Sie können nun die Installation abschließen, indem Sie die Kapitel 
[Webserver-Konfiguration](05-Web-Server-Configuration#chapter_05) und 
[Die Umgebungen](06-Environments#chapter_06) durchlesen.

>**NOTE**
>Da die Sandbox ein ganz normales symfony-Projekt ist, bei dem einige Aufgaben 
>schon für Sie durchgeführt wurden, und bei dem einige Konfigurations-Parameter 
>geändert wurden, ist es ganz einfach, sie als Startpunkt für ein neues Projekt 
>zu verwenden. Denken Sie aber daran, dass Sie die Konfiguration wahrscheinlich 
>werden anpassen müssen, z.B. die Änderung sicherheitsrelevanter Einstellungen 
>(s. die Konfiguration von XSS und CSRF in diesem Tutorial).