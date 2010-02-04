Symfony-Installation
====================

Projekt-Verzeichnis initialisieren
----------------------------------

Vor der Installation von symfony müssen Sie zuerst ein Verzeichnis erstellen, 
das alle Dateien zu Ihrem Projekt beinhalten wird:

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Oder mit Windows:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Windows-Anwender sind gut beraten, den Betrieb von symfony und das Aufsetzen 
>ihres neuen Projekts in einem Pfad durchzuführen, der keine Leerzeichen 
>enthält. Vermeiden Sie das `Dokumente und Einstellungen`-Verzeichnis, inklusive 
>Allem unter `Eigene Dateien`.

-

>**TIP**
>Wenn Sie das symfony Projekt-Verzeichnis im Webroot-Verzeichnis erstellen, 
>müssen Sie Ihren Web-Server nicht konfigurieren. Für Produktionsumgebungen 
>raten wir natürlich dringend zur Konfiguration Ihres Web-Servers, so wie im 
>Kapitel Webserver-Konfiguration beschrieben.

Die symfony-Version auswählen
-----------------------------

Jetzt müssen Sie symfony installieren. Da das symfony-Framework mehrere stabile 
Versionen hat, müssen Sie die auswählen, die Sie installieren möchten. Lesen Sie 
dazu die [Installations-Seite](http://www.symfony-project.org/installation) auf 
der symfony-Website.

Diese Anleitung geht davon aus, dass Sie die symfony-Version 1.4 installieren möchten.

Den Ort der symfony-Installation auswählen
------------------------------------------

Sie können symfony global auf Ihrem Rechner installieren, oder Sie betten 
symfony in jedes Ihrer Projekte ein. Die letztere Variante ist die 
empfehlenswertere, da die Projekte auf diese Weise absolut unabhängig 
voneinander sind. Das Upgraden einer solchen lokalen symfony-Installation führt 
dann nicht zu einer unerwarteten Beschädigung eines Ihrer anderen Projekte. 
D.h., dass Sie mehrere Projekte mit unterschiedlichen symfony-Versionen 
betreiben können, und Sie können eines nach dem anderen im geeigneten Moment 
upgraden.

Als bewährte Methode installieren viele Menschen das symfony-Framework in das 
Projekt-Verzeichnis `lib/vendor`. Also, erzeugen Sie jetzt zuerst diese 
Verzeichnis:

    $ mkdir -p lib/vendor

Symfony installieren
--------------------

### Installation aus einem Archiv

Der einfachste Weg symfony zu installieren besteht im Herunterladen des Archivs 
der Version, die Sie von der symfony-Website ausgesucht haben. Gehen Sie zur 
Installations-Seite der gewählten Version, z.B. symfony 
[1.4](http://www.symfony-project.org/installation/1_4).

Im Abschnitt "**Source Download**" finden Sie das Archiv im `.tgz` oder im `.zip` 
Format. Laden Sie das Archiv herunter, speichern es im frisch erstellten 
Verzeichnis `lib/vendor`, entpacken es und benennen das Verzeichnis um zu 
`symfony`:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.0.tgz
    $ mv symfony-1.4.0 symfony
    $ rm symfony-1.4.0.tgz

Mit Windows kann die zip-Datei mit dem Windows Explorer entpackt werden. Nachdem 
Sie das Verzeichnis zu `symfony` umbenannt haben, sollte die Verzeichnis-Struktur 
ähnlich aussehen wie: `c:\dev\sfproject\lib\vendor\symfony`.

### Installation aus Subversion (empfohlenes Verfahren)

Arbeiten Sie mit Subversion, dann ist es sogar besser, die 
`svn:externals`-Eigenschaft zu nutzen, um symfony in Ihr Projekt in das 
`lib/vendor/`-Verzeichnis einzubetten:

    $ svn pe svn:externals lib/vendor/

Wenn alles glatt läuft wird dieses Kommando Ihren bevorzugten Editor starten und 
Ihnen die Möglichkeit geben, die externen Subversion-Sourcen zu konfigurieren.

>**TIP**
>Mit Windows können Sie Tools wie z.B. [TortoiseSVN](http://tortoisesvn.net/) 
>einsetzen. Damit können Sie alles machen ohne die Konsole benutzen zu müssen.

Wenn Sie eher vorsichtig eingestellt sind, knüpfen Sie Ihr Projekt an eine 
bestimmte Release (ein Subversion-Tag):

    svn checkout http://svn.symfony-project.com/tags/RELEASE_1_4_0

Immer dann, wenn eine neue Release erscheint (angekündigt im symfony 
[Blog](http://www.symfony-project.org/blog/), müssen Sie die URL auf die neue 
Version setzen.

Wollen Sie den Weg an der vordersten Front gehen, dann verwenden Sie den 
1.4-Branch:

    svn checkout http://svn.symfony-project.com/branches/1.4/

Durch die Verwendung des Branch profitiert Ihr Projekt automatisch von den 
Bugfixes, sobald Sie ein `svn update` starten.

### Installation nachprüfen

Nachdem symfony installiert ist, überprüfen Sie ob alles läuft. Verwenden Sie 
dazu die symfony Kommandozeile, um die symfony-Version anzuzeigen (beachten Sie 
die Großschreibung von `V`):

    $ cd ../..
    $ php lib/vendor/symfony/data/bin/symfony -V

Mit Windows:

    c:\> cd ..\..
    c:\> php lib\vendor\symfony\data\bin\symfony -V

Die Option `-V` zeigt auch den Pfad zum Installationsverzeichnis von symfony, der 
unter `config/ProjectConfiguration.class.php` gespeichert ist.

Sollte der Pfad als absoluter Pfad gespeichert sein (was standardmäßig nicht der 
Fall sein sollte, wenn Sie den obigen Anweisungen gefolgt sind), dann sollten 
Sie ihn für eine bessere Portabilität ändern, so dass er so aussieht:

    [php]
    // config/ProjectConfiguration.class.php
    require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';

Auf diese Weise können Sie das Projekt-Verzeichnis auf Ihrem Rechner oder auf 
einen anderen verschieben, und es wird weiterhin funktionieren.

>**TIP**
>Wenn Sie neugierig sind, was dieses Kommandozeilen-Tool für Sie tun kann, geben 
>Sie `symfony` ein um alle verfügbaren Optionen und Aufgaben aufzulisten:
>
>     $ php lib/vendor/symfony/data/bin/symfony
>
>Mit Windows:
>
>     c:\> php lib\vendor\symfony\data\bin\symfony
>
>Die symfony Kommandozeile ist der beste Freund des Entwicklers. Sie stellt eine 
>Menge von Hilfen zur Verfügung, die Ihre Produktivität bei den täglichen 
>Aktivtäten steigert, wie z.B. Leeren des Cache, Code-Generierung u.v.m.