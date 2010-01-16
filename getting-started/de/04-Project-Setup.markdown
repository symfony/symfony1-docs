Projekt einrichten
==================

In symfony werden **Anwendungen**, die auf dasselbe Datenmodell zugreifen, 
innerhalb von **Projekten** gruppiert. Bei den meisten Projekten werden Sie zwei 
verschiedene Anwendungen haben: ein Frontend und ein Backend.

Projekt erstellen
-----------------

Vom Verzeichnis `sfproject/` aus starten Sie den symfony-Task 
`generate:project` um das eigentliche symfony-Projekt zu erstellen:

    $ php lib/vendor/symfony/data/bin/symfony generate:project PROJECT_NAME

Mit Windows:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project PROJECT_NAME

Der `generate:project`-Task erstellt die Standard-Struktur aus Verzeichnissen und 
Dateien, die ein symfony-Projekt braucht:

 | Verzeichnis | Beschreibung
 | ----------- | --------------------------------------------------
 | `apps/`     | Enthält alle Anwendungen des Projekts
 | `cache/`    | Die Dateien, die vom Framework gecached werden
 | `config/`   | Die Dateien der Projekt-Konfiguration
 | `data/`     | Datendateien wie z.B. die Anfangs-Ausstattung
 | `lib/`      | Die Projekt-Bibliotheken und -Klassen
 | `log/`      | Die Log-Dateien des Frameworks
 | `plugins/`  | Die installierten Plugins
 | `test/`     | Die Dateien der Element-Tests und Funktions-Tests
 | `web/`      | Das Webroot-Verzeichnis (s. unten)

>**NOTE**
>Warum generiert symfony so viele Dateien? Einer der Hauptvorteile der 
>Verwendung eines voll ausgestatteten Frameworks besteht in der Standardisierung 
>Ihrer Entwicklungen. Dank symfonys Standardstruktur aus Dateien und 
>Verzeichnissen kann jeder Entwickler mit etwas symfony-Kenntnissen die Pflege 
>eines symfony-Projekts übernehmen. Innerhalb von Minuten wird es ihm möglich 
>sein, in den Code einzutauchen, Fehler zu beheben und neue Features hinzuzufügen.

Der `generate:project`-Task hat ebenfalls in Ihrem Projekt-Hauptverzeichnis eine 
Abkürzung erstellt, damit Sie nicht so viele Zeichen eintippen müssen, um einen 
symfony-Task zu starten.

Von jetzt an also können Sie statt des vollständigen Pfades zum symfony-Programm 
die `symfony`-Abkürzung verwenden.

Die Datenbank konfigurieren
---------------------------

Das symfony-Framework unterstützt von vorneherein alle Datenbanken, die 
[PDO](http://www.php.net/PDO) unterstützen (MySQL, PostgreSQL, SQLite, Oracle, 
MSSQL, ...). Symfony vefügt über zwei ORM-Tools, die auf PDO aufsetzen: Propel 
und Doctrine.

Beim Erstellen eines neuen Projekts ist Doctrine standardmäßig aktiviert. Die 
Konfiguration der von Doctrine verwendeten Datenbank erfolgt ganz einfach mit 
dem Task `configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=dbname" root mYsEcret

Der `configure:database`-Task erhält drei Argumente: die 
[~PDO DSN~](http://www.php.net/manual/en/pdo.drivers.php), den Usernamen und das 
Passwort für den Zugang zur Datenbank. Wenn Sie auf Ihrem Entwicklungs-Server 
kein Zugangs-Passwort zur Datenbank brauchen, lassen Sie das dritte Argument 
einfach weg.

>**TIP**
>Wenn Sie Propel statt Doctrine einsetzen möchten, dann fügen Sie `--orm=Propel` 
>hinzu, wenn Sie das Projekt mit dem Task `generate:project` anlegen. Wollen Sie 
>gar kein ORM verwenden, dann geben Sie `--orm=none` ein.

Anwendung erstellen
-------------------

Nun erstellen Sie die Frontend-Anwendung durch starten des Tasks `generate:app`:

    $ php symfony generate:app frontend

>**TIP**
>Da die symfony-Abkürzung eine ausführbare Datei ist, können alle Unix-Anwender 
>von jetzt an '`./symfony`' statt '`php symfony`'  eingeben.
>
>Bei Windows können Sie die Datei '`symfony.bat`' in Ihr Projekt kopieren und 
>'`symfony`' statt '`php symfony`' verwenden:
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

Abhängig vom Anwendungsnamen, der als *Argument* angegeben wurde, erzeugt der 
Task `generate:app` die für die Anwendung notwendige Standard-Verzeichnisstruktur 
innerhalb des Verzeichnisses `apps/frontend/`:

 | Verzeichnis  | Beschreibung
 | ------------ | -------------------------------------
 | `config/`    | Die Dateien der Anwendungs-Konfiguration
 | `lib/`       | Die Bibliotheken und Klassen der Anwendung
 | `modules/`   | Der Code der Anwendung (MVC)
 | `templates/` | Die globalen Template-Dateien

>**SIDEBAR**
>Sicherheit
>
>Standardmäßig hat der Task `generate:app` unsere Anwendung gegen die zwei am 
>meisten verbreiteten Schwachstellen im Web abgesichert. Sie haben richtig 
>gelesen, symfony übernimmt für Sie automatisch Maßnahmen für die
>~sicherheit|Sicherheit~.
>
>Zur Verhütung von ~XSS~-Attacken ist das Output-Escaping aktiviert worden; und 
>zur Verhütung von ~CSRF~-Attacken ist ein zufälliges geheimes CSRF generiert 
>worden.
>
>Natürlich können Sie diese Einstellungen durch die folgenden *Optionen* 
>optimieren:
>
>  * `--escaping-strategy`: Aktiviert oder deaktiviert das Output-Escaping
>  * `--csrf-secret`: Aktiviert Session-Tokens in Formularen
>
>Sollten Sie nichts über 
>[XSS](http://de.wikipedia.org/wiki/Cross-Site_Scripting) oder 
>[CSRF](http://de.wikipedia.org/wiki/CSRF) wissen, dann nehmen Sie sich die Zeit, 
>mehr über diese Sicherheits-Schwachstellen zu lernen.

Verzeichnisstruktur-Rechte
--------------------------

Bevor Sie auf Ihr neu erstelltes Projekt zugreifen, müssen Sie die Schreibrechte 
für die Verzeichnisse `cache/` und `log/` auf die geeignete Stufe setzen, damit 
der Webserver darauf schreiben kann:

    $ chmod 777 cache/ log/

>**SIDEBAR**
>Tipp für Leute, die ein SCM-Tool (Software Configuration Management) verwenden
>
>Symfony schreibt überhaupt nur in zwei Verzeichnisse eines symfony-Projektes, 
>`cache/` und `log/`. Der Inhalt dieser Verzeichnisse sollte von Ihrem SCM 
>ignoriert werden. (Wenn Sie z.B. Subversion verwenden durch Anpassen der 
>`svn:ignore`-Eigenschaft.)