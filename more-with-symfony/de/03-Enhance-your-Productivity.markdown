Gesteigerte Produktivität
=========================

*von Fabien Potencier*

Das Nutzen von Symfony selbst ist eine gute Wahl, um als Web Entwickler 
produktiver zu arbeiten. Natürlich weiß bereits jeder, wie einen Symfony 
durch die Webdebug-Toolbar und die ausführlichen Fehlermeldungen beim 
Entwickeln unterstützt. Dieses Kapitel wird einige neue oder weniger 
bekannte Funktionen von Symfony vorstellen, die euch bei der täglichen 
Arbeit unterstützen werden.

Schnellstart: Individuelle Projekterstellung
--------------------------------------------

Dank der Funktionen in der der Kommandozeile lässt sich ein neues Projekt 
schnell und ohne Aufwand erstellen:

    $ php /path/to/symfony generate:project foo --orm=Doctrine

Der `generate:project` Task erstellt die Verzeichnisstruktur und 
Konfigurationsdateien mit den empfohlenen Voreinstellungen. Mit weiteren 
Befehlen lassen sich dann u.a. Applikationen erstellen, Plugins installieren 
und die Model Klassen generieren.

In der Regel sind die Schritte, die man beim Erstellen eines neuen Projekts 
durchführt, immer ähnlich: Ersellen der Applikation, Installation von Plugins,
das Anpassen der Standardkonfiguration und so weiter.

Seit Symfony 1.3 lässt sich das Erstellen eines Projekts automatisieren.

>**NOTE**
>Da alle Symfony Tasks Klassen sind, ist es leicht diese zu modifizieren und
>zu erweitern. Einzig der `generate:project` Task kann nicht ohne weiteres 
>modifiziert werden, da zum Ausführungszeitpung noch kein Projekt existiert.


Der `generate:project` Task akzeptiert den Parameter `--installer`. So lässt 
sich ein PHP-Skript beim Initialisieren des Projekts ausführen:

    $ php /path/to/symfony generate:project --installer=/irgendwo/mein_installer.php

Das `/irgendwo/mein_installer.php` Skript wird im Kontext der `sfGenerateProjectTask`
Instanz ausgeführt, und kann somit auf dessen Methoden über das Objekt `$this` 
zugreifen. Der folgende Abschnitt beschreibt alle Methoden die uns zur 
Verfügung stehen, um in das Erstellen von Projekten einzugreifen.

>**TIP**
>Falls der URL Dateizugriff für die `include()` Funktion in der `php.ini`
>aktiviert ist, kann man sogar eine URL als Parameter übergeben. Es versteht sich
>von selbst, dass man mit unbekannten Skripten vorsichtig umgehen sollte:
>
>      $ symfony generate:project
>      --installer=http://example.com/sf_installer.php

### `installDir()`

Die `installDir()` Methode spiegelt ein Verzeichnis (Unterverzeichnise und Dateien)
in das neue Projekt:

    [php]
    $this->installDir(dirname(__FILE__).'/skeleton');

### `runTask()`

Die `runTask()` Methode führt einen Task aus. Sie erwartet den Namen des Tasks und
einen String, der die Argumente und Optionen enthält, welche dem Task übergeben 
werden.

    [php]
    $this->runTask('configure:author', "'Fabien Potencier'");

Argumente und Optionen können auch als Array übergeben werden:

    [php]
    $this->runTask('configure:author', array('author' => 'Fabien Potencier'));

>**TIP**
>Die Task-Kürzel können ebenso verwendet werden:
>
>     [php]
>     $this->runTask('cc');

Natürlich können auch Plugins mit einem Task installiert werden:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin');

Um eine bestimmte Version eines Plugins zu installieren, reicht es die entsprechenden Parameter zu übergeben:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin', array('release' => '10.0.0', 'stability' => beta'));

>**TIP**
>Um einen Task eines gerade installierten Plugins auszuführen, müssen die Tasks erst neu 
>initialisiert werden:
>
>     [php]
>     $this->reloadTasks();

Um eine neue Applikation zu erstellen und Tasks ausführen zu können, die explizit eine 
Applikation voraussetzen - wie z.B. `generate:module` - muss die Konfiguration angepasst
werden.

    [php]
    $this->setConfiguration($this->createConfiguration('frontend', 'dev'));

### Loggers

Während dem Ausführen des Installationsskriptes können Meldungen ganz leicht ausgegeben 
werden:

    [php]
    // eine einfache Ausgabe
    $this->log('etwas einleitender Text');

    // einen Block ausgeben
    $this->logBlock('Fabien\'s verrückter Installer', 'ERROR_LARGE');

    // in einem Abschnitt
    $this->logSection('install', 'installiere verrücktes Zeug');

### User Interaction

Die `askConfirmation()`, `askAndValidate()`, und `ask()` Methoden erlauben es Eingaben 
abzufragen und den Installations Prozess dynamisch zu gestalten

Wenn nur eine Bestätigung benötigt wird, verwendet man die Methode `askConfirmation()`:

    [php]
    if (!$this->askConfirmation('Sind Sie sicher, dass Sie den verrückten Installer ausführen möchten?'))
    {
      $this->logSection('install', 'Sie haben die richtige Wahl getroffen!');

      return;
    }

Mit der Methode `ask()` lässt sich die Eingabe des Benutzers als String abfragen:

    [php]
    $secret = $this->ask('Bitte ein einzigartige Zeichenfolge für das CSRF-Token eintragen :');

Mit der Methode `askAndValidate()` lässt sich die Eingabe zusätzlich überprüfen:

    [php]
    $validator = new sfValidatorEmail(array(), array('invalid' => 'hmmm, das ist keine valide E-Mail Adresse'));
    $email = $this->askAndValidate('Bitte tragen Sie Ihre E-Mail Adresse ein:', $validator);

### Filesystem Operations

Wenn Änderungen am Dateisystem vorgenommen werden sollen, kann hierfür das Symfony-Filesystem-Objekt 
verwendet werden:

    [php]
    $this->getFilesystem()->...();

>**SIDEBAR**
>The Sandbox Creation Process
>
>Die Symfony Sandbox ist ein vorbereitetes Projekt und mit einer einsatzfähigen SQLite Datenbannk ausgestattet.
>Mit dem Installationsskript kann man sich seine eigene Sandbox erstellen:
>
>     $ php symfony generate:project --installer=/path/to/symfony/data/bin/sandbox_installer.php
>
>Das Skript findet man im Ordner `symfony/data/bin/sandbox_installer.php` und ist eine Vorlage für
>ein lauffähiges Installationsskript.

Ein Installationsskript ist eine gewöhnliche PHP Datei und kann daher nach belieben an die
Bedürfnisse angepasst werden. Anstatt die gleichen Tasks beim Erstellen eines neuen Projekts 
wieder und wieder auszuführen, kann man sich ein eigenes Installationsskript für diese Aufgaben erstellen.
Man ist schneller und läuft nicht Gefahr einen Task zu vergessen, wenn man seine Projekte mit einem 
Installationsskript initialisiert. Man kann die Skripte natürlich mit anderen Entwicklern tauschen.

>**TIP**
>Im [Kapitel 6](#chapter_06), wird ein angepasstes Installationsskript verwendet. Den Code hierfür findet
>man im [Anhang B](#chapter_b).

Schneller Entwickeln
--------------------

Vom PHP Code bis zu den CLI Tasks, beim Programmieren hat man viel zu tippen. Als nächstes lernen wir,
wie sich das auf ein Minimum reduzieren lässt.

### Die Wahl der Entwicklungsumgebung (IDE)

Die Verwendung einer IDE steigert die Produktivität eines Entwicklers in mehreren Aspekten.

Zuerst, die meisten modernen IDEs bringen für PHP eine Code-Autovervollständigung mit.
Das bedeutet, dass es meistens reicht die ersten paar Buchstaben eines Methoden Namens einzutippen.
Außerdem unterstützt einen das System, falls man nicht den genauen Namen der Methode kennt. Anstatt
in der API nachlesen zu müssen, führt die IDE alle Methoden des aktuellen Objekts auf.

Zusätzlich bieten manche IDEs wie PHPEdit oder Netbeans eine umfassendere Integration von Symfony 
in den Projekten.

>**SIDEBAR**
>Text Editors
>
>Manche Nutzer bevorzugen die Verwendung eines Text Editor beim Programmieren, da diese 
>schneller als jede IDE sind. Natürlich bieten die Text Editoren nur wenige der Funktionen, wie 
>wir sie von den IDEs kennen. Jedoch bieten die meisten Editoren Plugins bzw. Erweiterungen an,
>die den Editor an die Bedüfnisse anpassen und die Arbeit mit PHP und Symfony erleichtern.
>
>Unter Linux Nutzern ist VIM sehr verbreitet und wird für nahezu alle Aufgaben verwendet.
>Für diese Entwickler könnte die [vim-symfony](http://github.com/geoffrey/vim-symfony)
>Erweiterung interessant sein. VIM-symfony ist eine Sammlung von VIM Skripten, welche 
>Symfony in den Editor integrieren. Mit vim-symfony kann man Macros und Befehle erstellen, um
>eine bei der Entwicklung mit Symfony zu unterstützen. Enthalten ist bereits eine Sammlung an
>Befehlen, welche die gängigen Konfigurationsdateien (Schema, Routing, etc.) erstellen und es einem 
>erlauben, zwischen einer Action und dem Template zu wechseln.
>
>Einige MacOS X Nutzer verwenden TextMate. Für diese Entwickler steht das Symfony
>[bundle](http://github.com/denderello/symfony-tmbundle) bereit, welches eine Vielzahl an
>zeitsparenden Macros und Tastenkürzeln für die tägliche Arbeit ergänzt.

#### Using an IDE that supports symfony

Einige IDEs, wie [PHPEdit 3.4](http://www.phpedit.com/en/presentation/extensions/symfony)
und [NetBeans 6.8](http://www.netbeans.org/community/releases/68/), bieten eine native
symfony Unterstützung und somit eine umfangreiche Integration des Frameworks.
Ein Blick in die Dokumentation der IDEs gibt Aufschluss darüber, wie symfony mit dem Editor
interagiert und wie er einen bei seiner Arbeit unterstützen kann.

#### Helping the IDE

Die PHP Autovervollständigung in IDEs funktioniert nur, wenn die Methoden explizit im PHP Code 
definiert sind. Falls im Code so genannte "Magic Methods" wie `__call()` oder `__get()` verwendet werden,
haben die IDEs keine Möglichkeit die verfügbaren Methoden und Eigenschaften vorzuschlagen. Den meisten IDEs
kann man aber unter die Arme greifen, indem die Methoden und/oder Eigenschaften mittels PHPDoc definiert 
werden (per `@method` bzw. `@property`).

Angenommen wir haben die Klasse `Message` mit einer dynamischen Eigenschaft (`message`) und einer dynamischen
Methode (`getMessage()`). Der folgende Code zeigt, wie man einer IDE über deren Existenz informiert, ohne dass 
sie im Code definiert sind:

    [php]
    /**
     * @property clob $message
     *
     * @method clob getMessage() Returns the current message value
     */
    class Message
    {
      public function __get()
      {
        // ...
      }

      public function __call()
      {
        // ...
      }
    }

Selbst wenn die `getMessage()` nicht existiert, wird sie dank der `@method` Notation durch die IDE erkannt.
Das gleiche gilt für die Eigenschaft `message` durch die Verwendung von `@property`.

Diese Technik wird beim `doctrine:build-model` Task verwendet. Zum Beispiel, eine Doctrine
Klasse `MailMessage` mit zwei Spalten (`message` und `priority`) schaut folgendermaßen aus:

    [php]
    /**
     * BaseMailMessage
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @property clob $message
     * @property integer $priority
     *
     * @method clob        getMessage()  Returns the current record's "message" value
     * @method integer     getPriority() Returns the current record's "priority" value
     * @method MailMessage setMessage()  Sets the current record's "message" value
     * @method MailMessage setPriority() Sets the current record's "priority" value
     *
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    abstract class BaseMailMessage extends sfDoctrineRecord
    {
        public function setTableDefinition()
        {
            $this->setTableName('mail_message');
            $this->hasColumn('message', 'clob', null, array(
                 'type' => 'clob',
                 'notnull' => true,
                 ));
            $this->hasColumn('priority', 'integer', null, array(
                 'type' => 'integer',
                 ));
        }

        public function setUp()
        {
            parent::setUp();
            $timestampable0 = new Doctrine_Template_Timestampable();
            $this->actAs($timestampable0);
        }
    }

Dokumentation schneller finden
------------------------------

Da es sich bei symfony um einen umfangreichen Framework mit einer Vielzahl an
Features handelt ist es nicht immer leicht alle Klassen, Methoden und Konfigurationsmöglichkeiten
im Kopf zu behalten. Wie wir bereits gesehen haben, sind die IDEs durch die
automatische Codevervollständigung eine große Hilfe. Als nächstes lernen wir,
wie man am schnellsten die Antwort auf eine Frage findet.

### Online API

Der schnellste Weg zur Dokumentation von Klassen und Methoden ist die Online 
[API](http://www.symfony-project.org/api/1_3/).

Sehr hilfreich ist dabei die bereitgestellte Suche, welche einem durch wenige
Tastendrücke hilft die gesuchten Klassen oder Methoden zu finden.
Schon durch die Eingabe weniger Buchstaben in das Suchfeld, werden einem in
Echtzeit passende Treffer vorgeschlagen.

Suchen lässt sich, indem man den Anfang eines Klassen Namens einträgt:

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_1.png "API Search")

oder einen Methoden Namen:

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_2.png "API Search")

oder einem Klassen Namen gefolgt von `::` um alle verfügbaren Methoden anzuzeigen:

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_3.png "API Search")

oder durch Eintragen des Anfangs eines Methoden Namens, um die Suche weiter zu verfeinern:

![API Search](http://www.symfony-project.org/images/more-with-symfony/api_search_4.png "API Search")

Um alle Klassen eines Packets aufzuzeigen, reicht es dessen Namen einzutragen und die Suche zu starten.

Die API-Suche lässt sich auch in den Browser integrieren, somit spart man sich das
Aufrufen der symfony Webseite um etwas zu suchen. Dies wird möglich durch die Unterstützung
von [OpenSearch](http://www.opensearch.org/) für die symfony API.

Bei dem Firefox Browser wird die Such-Erweiterung automatisch in der Suchbox angezeigt. 
Ansonsten lässt sie sich durch einen Klick auf den Link "API OpenSearch" in der API Dokumentation
zu dem verwendeten Browser hinzufügen.

>**NOTE**
>Dieses kurze Video-Tutorial im symfony 
[Blog](http://www.symfony-project.org/blog/2009/02/24/opensearch-support-for-the-symfony-api) zeigt, wie die
>Suche in den Firefox Browser integriert werden kann.

### Cheat Sheets

Für die zentralen Bestandteile des Frameworks existieren so genannte [cheat sheets](http://trac.symfony-project.org/wiki/CheatSheets),
um sich einen schnellen Überblick zu verschaffen:

 * [Directory Structure and CLI](http://andreiabohner.files.wordpress.com/2007/03/cheatsheetsymfony001_enus.pdf)
 * [View](http://andreiabohner.files.wordpress.com/2007/08/sfviewfirstpartrefcard.pdf)
 * [View: Partials, Components, Slots and Component Slots](http://andreiabohner.files.wordpress.com/2007/08/sfviewsecondpartrefcard.pdf)
 * [Lime Unit & Functional Testing](http://trac.symfony-project.com/attachment/wiki/LimeTestingFramework/lime-cheat.pdf?format=raw)
 * [ORM](http://andreiabohner.files.wordpress.com/2007/08/sform_enus.pdf)
 * [Propel](http://andreiabohner.files.wordpress.com/2007/08/sfmodelfirstpartrefcard.pdf)
 * [Propel Schema](http://andreiabohner.files.wordpress.com/2007/09/sfmodelsecondpartrefcard.pdf)
 * [Doctrine](http://www.phpdoctrine.org/Doctrine-Cheat-Sheet.pdf)

>**NOTE**
>Die Dokumente sind auf Englisch und noch nicht alle wurden für die aktuelle symfony Version 1.3 überarbeitet.

### Offline Dokumentation

Fragen zur Konfiguration werden am Besten im symfony Referenz-Handbuch beantwortet, welches man
während der Entwicklung immer griffbereit haben sollte. Dank des umfangreichen Inhaltsverzeichnis,
dem Stichwortverzeichnis, Querverweisen und zahlreichen Tabellen ist das Buch ist die schnellste Art 
jede verfügbare Konfiguration einzusehen.

Dieses Buch kann 
[online](http://www.symfony-project.org/reference/1_3/en/), als 
[gedruckte](http://books.sensiolabs.com/book/the-symfony-1-3-reference-guide)
Version oder als 
[PDF](http://www.symfony-project.org/get/pdf/reference-1.3-en.pdf) zum Download gelesen werden.

### Online Tools

Wie schon am Anfang des Kapitels erwähnt, bietet symfony eine umfangreiche Sammlung an
Hilfsmitteln um einem die Arbeit zu erleichtern. Wenn das Projekt abgeschlossen ist, wird
es Zeit es zu veröffentlichen.

Um sicherzugehen, dass das Projekt dafür bereit ist lohnt sich ein Blick auf folgende
[Checkliste](http://symfony-check.org/). Diese Seite führt alle Punkte auf, die man beachten 
sollte wenn man ein Projekt im produktiv einsetzen will.

Schnelleres Debuging
--------------------

Wenn in der Entwicklungs Umgebung ein Fehler auftritt, zeigt symfony eine Fehlerseite
mit nützlichen Informationen wie z.B. einen `stack !!!!  trace` und die zuvor aufgerufenen 
Dateien. Wenn in der `settings.yml` die Einstellung  ~`sf_file_link_format`~ konfiguriert
ist, lässt sich die entsprechende Datei mit einem Klick direkt im bevorzugten Editor öffnen.
Dies ist ein weiteres Beispiel für ein kleines Feature welches eine enorme Zeitersparnis 
bei der Fehlerbehebung bietet.

>**NOTE**
>Die Log und View Konsole in der Webdebug Toolbar zeigen ebenfalls die Dateinamen an
>(sofern xDebug aktiviert ist) die klickbar werden, sobald die `sf_file_link_format` Einstellung
>gesetzt ist.

Standardmäßig ist `sf_file_link_format` leer und symfony verwendet stattdessen den Wert 
[`xdebug.file_link_format`](http://xdebug.org/docs/all_settings#file_link_format) aus der PHP
Konfiguration, falls dieser gesetzt ist (das definieren des `xdebug.file_link_format` in der
`php.ini` erlaubt es den aktuellen Versionen von xDebug alle Dateinamen in Links umzuwandeln).

Der zu setzende Wert von `sf_file_link_format` hängt von der verwendeten IDE und dem Betriebssystem ab.
Zum Beispiel, wenn die Dateien in  ~TextMate~ geöffnet werden sollen, wählt man den folgenden Wert:

    [yml]
    dev:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

Der Platzhalter `%f` wird von symfony durch den absoluten Dateipfad der Datei ersetzt, der `%l`
Platzhalter durch die Zeilennummer.

Bei VIM ist die Konfiguration etwas umfangreicher und online abrufbar für 
[symfony](http://geekblog.over-blog.com/article-symfony-open-exceptions-files-in-remote-vim-sessions-37895120.html)
und [XDebug](http://www.koch.ro/blog/index.php?/archives/77-Firefox,-VIM,-Xdebug-Jumping-to-the-error-line.html).

>**NOTE**
>Für die Konfiguration weiterer IDEs liefern die bekannten Suchmaschinen eine Vielzahl an
>Anleitungen. Man kann nach der Konfiguration für `sf_file_link_format` oder `xdebug.file_link_format`
>suchen, da beide identisch funktionieren.

Schneller Testen
----------------

### Funktionale Tests aufzeichnen

Mit funktionellen Tests kann man Interaktionen durch Benutzer simulieren und somit
sicherstellen, dass alle Teile des Projekts korrekt zusammenarbeitenn.
Das Schreiben von funktionellen Tests ist leicht, aber zeitaufwendig. Da jedoch jeder
Test ein Szenario darstellt, worin ein User sich die Webseite betrachtet, und das Betrachten
der Seiten schneller ist als PHP Code zu schreiben - was wäre wenn man die Browsersession 
aufzeichnen könnte und diese automatisch in entsprechenden PHP Code konvertiert würde?
Zum Glück bietet symfony solch ein Plugin. Es heisst 
[swFunctionalTestGenerationPlugin](http://www.symfony-project.org/plugins/swFunctionalTestGenerationPlugin)
und generiert aus der Browsersession eine Vorlage für einen funktionellen Test. Es ist nur noch ein
geringer Aufwand nötig um diese Vorlage soweit anzupassen, dass sie in die Test-Suite aufgenommen werden kann.

Das Plugin fügt einen weiteren Filter hinzu, der alle Requests aufzeichnet und daraus den entsprechenden
Code für einen funktionellen Test generiert. Nach der Installation des Plugins muss dessen Filter noch in 
der `filters.yml` hinzugefügt werden:

    [php]
    functional_test:
      class: swFilterFunctionalTest

Als nächstes wird das Plugin in der  `ProjectConfiguration` Klasse aktiviert:

    [php]
    // config/ProjectConfiguration.class.php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->enablePlugin('swFunctionalTestGenerationPlugin');
      }
    }

	
As the plugin uses the web debug toolbar as its main user interface, be sure
to have it enabled (which the case in the development environment by default).
When enabled, a new menu named "Functional Test" is made available. In this
panel, you can start recording a session by clicking on the "Activate" link,
and reset the current session by clicking on "Reset". When you are done, copy
and paste the code from the textarea to a test file and start customizing it.

### Run your Test Suite faster

When you have a large suite of tests, it can be very time consuming to launch
all tests every time you make a change, especially if some tests fail. Each
time you fix a test, you should run the whole test suite again to ensure
that you have not broken other tests. But until the failed tests are fixed,
there is no point in re-executing all the other tests. To speed up this process,
the `test:all` task has an `--only-failed` (`-f` as a shortcut) option that forces
the task to only re-execute tests that failed during the previous run:

    $ php symfony test:all --only-failed

Bei der ersten Ausführung werden wie gewohnt alle Tests ausgeführt. Jedoch werden bei den weiteren Durchläufen nur diejenigen
ausgeführt, welche zuletzt fehlschlugen. Durch das beheben der Fehler werden weitere Tests erfolgreich durchlaufen und 
daher im nächsten Durchgang auch übergangen. Sobald alle Tests fehlerfrei durchlaufen, wird wieder die komplette Test-Suite 
ausgeführt... diese Schleife fährt man dann wieder und wieder.