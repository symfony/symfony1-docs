Voraussetzungen
===============

Vor der Installation von symfony, sollten Sie sicherstellen, dass auf Ihrem 
Computer alles richtig installiert und konfiguriert ist. Nehmen Sie sich Zeit 
dieses Kapitel gewissenhaft durchzulesen und befolgen Sie alle notwendigen 
Schritte zur Überprüfung Ihrer Konfiguration - es kann Ihnen später den Tag 
retten.

Software anderer Hersteller
---------------------------

Zuallererst prüfen Sie, ob Ihr Computer eine arbeitsfreundliche Umgebung für die 
Web-Entwicklung darstellt. Sie benötigen mindestens einen Web-Server (z.B. 
Apache), eine Datenbank-Engine (mySQL, PostgreSQL, SQLite, oder eine andere 
[PDO](http://www.php.net/PDO)-kompatible Datenbank-Engine), sowie PHP 5.2.4. 
oder höher.

Kommandozeilen-Interface
------------------------

Das symfony-Framework beinhaltet ein Kommandozeilen-Tool, das Ihnen eine Menge 
Handarbeit automatisiert. Als Anwender eines Unix-ähnlichen Betriebssystems 
werden Sie sich gleich zu Hause fühlen. Wenn Sie ein Windows-System betreiben, 
wird alles ebenfalls gut funktionieren. Sie werden in diesem Fall nur ein paar 
Befehle auf dem Kommandozeilen-Prompt eintippen müssen.

>**Note**
>Unix shell-Kommandos können in einer Windows-Umgebung gelegen kommen. 
>Wenn Sie Tools wie `tar`, `gzip` oder `grep` verwenden wollen, können Sie 
>[Cygwin](http://cygwin.com/) installieren. Die Abenteuerlustigen möchten 
>vielleicht auch die 
>[Windows Services for Unix](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx) 
>von Microsoft ausprobieren.

PHP-Konfiguration
-----------------

Da PHP-Konfigurationen von Betriebssystem zu Betriebssystem, sogar innerhalb 
verschiedener Linux-Distributionen, sehr stark variieren können, müssen Sie 
prüfen, ob Ihre PHP-Konfiguration den Minimalanforderungen von symfony genügt.

Zuerst versichern Sie sich, dass Sie mindestens PHP 5.2.4 installiert haben, 
indem Sie die eingebaute Funktion `phpinfo()` starten, oder Sie starten 
`php -v` von der Kommandozeile aus. Beachten Sie, dass es Konfigurationen gibt, 
bei denen zwei verschiedene PHP-Versionen installiert sind: eine für die 
Kommandozeile und eine für das Web.

Anschließend downloaden Sie das symfony-Konfigurations-Prüfskript von dieser URL:

    http://sf-to.org/1.4/check.php

Speichern Sie das Skript irgendwo innerhalb Ihres aktuellen Webroot-Verzeichnisses.

Starten Sie das Konfigurations-Prüfskript von der Kommandozeile aus:

    $ php check_configuration.php

Sollte es ein Problem mit Ihrer PHP-Konfiguration geben, dann gibt Ihnen die 
Ausgabe des Skripts Hinweise darauf, was ausgebessert werden muss und wie die 
Ausbesserung auszusehen hat.

Sie sollten das Prüfskript auch von einem Browser aus ausführen und die 
gefundenen Punkte ausbessern. Dies deshalb, weil PHP für diese beiden Umgebungen 
unterschiedliche `php.ini`-Dateien mit unterschiedlichen Einstellungen haben kann.

>**NOTE**
>Vergessen Sie zum Schluss nicht, die Datei wieder aus Ihrem Webroot-Verzeichnis 
>zu entfernen.

-

>**NOTE**
>Sollten Sie vorhaben symfony für ein paar Stunden auszuprobieren, dann 
>installieren Sie die symfony-Sandbox wie im [Anhang A](A-The-Sandbox) 
>beschrieben. Wenn Sie ein echtes Projekt von Null an beginnen wollen, dann 
>lesen Sie im nächsten Kapitel weiter.