Erweiterung der Web Debug Werkzeugleiste
===============================

*von Ryan Weaver*

Standartmäßig beinhaltet symfonys Web Debug Werkzeugleiste eine Vielfalt von Werkzeugen
die beim Fehlersuchen, der Leistunsverbesserung und vielem mehr helfen. Die Web Debug
Werkzeugleiste besteht aus mehreren Werkzeugen, genannt *Web Debug Paneele*, die
sich auf den Zwischenspeicher, die Protokollierung, die symfony Version und die
Verarbeitungszeit beziehen. Zusätzlich führt symfony 1.3 zwei weitere *Web Debug Paneele*
für `Ansichts`informationen und `Mail` Debugging ein.

![Web Debug Werkzeugleiste](http://www.symfony-project.org/images/more-with-symfony/web_debug_01.png "Die Web Debug Werkzeugleiste mit den Standard Widgets von symfony 1.3")

Seit symfony 1.2 können Entwickler einfach ihre eigenen *Web Debug Paneele* erzeugen und
sie der Web Debug Werkzeugleiste hinzufügen. In diesem Kapitel werden wir ein neues *Web
Debug Paneel* erstellen und dann mit verschiedenen Werkzeugen und Anpassungen
experimentieren. Zusätzlich beinhaltet das [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
mehrere nützliche und interessante Debug Paneele, die einige Techniken aus diesem Kapitel
verwenden.

Erstellen eines neuen Web Debug Panels
------------------------------

Die verschiedenen Komponenten der Web Debug Werkzeugleiste sind als *Web Debug Paneele*
bekannt und sind spezielle Klassen, die die  ~`sfWebDebugPanel`~ Klasse erweitern. Das Erstellen
eines neuen Web Debug Panels ist eigentlich ziemlich einfach. Erstellen Sie eine Datei namens `sfWebDebugPanelDocumentation.class.php` im `lib/debug/` Verzeichnis Ihres Projekts. (Sie müssen
dieses Verzeichnis erstellen.)

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    class acWebDebugPanelDocumentation extends sfWebDebugPanel
    {
      public function getTitle()
      {
        return '<img src="/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
      }

      public function getPanelTitle()
      {
        return 'Documentation';
      }

      public function getPanelContent()
      {
        $content = 'Placeholder Panel Content';

        return $content;
      }
    }

Alle Debug Paneele müssen mindestens die  `getTitle()`, `getPanelTitle()` und `getPanelContent()` Methoden implementieren.

 * ~`sfWebDebugPanel::getTitle()`~: Bestimmt wie das Paneel in der
   Werkzeugleiste erscheint. Wie die meisten Paneele beinhaltet unser Paneel
   eine kleines Symbol und einen kurzen Namen für das Paneel.

 * ~`sfWebDebugPanel::getPanelTitle()`~: Wird als Text für das `h1` Tag verwendet, dass
  am oberen Rand des Panelinhalts erscheint. Dieser Text wird auch für das `title` Attribute
   der Verknüpfung um das Symbol in der Werkzeugleiste verwendet und als solcher darf er *keinen*
   HTML Code.

 * ~`sfWebDebugPanel::getPanelContent()`~: Erstellt den rohen HTML Inhalt, der angezeigt wird,
   wenn Sie auf das Paneel Symbol klicken.

Der ein zigste verbleibende Schritt ist es, die Anwendung davon zu unterrichten, dass Sie ein
neues Paneel in die Werkzeugleiste integrieren wollen. Um dies zu Bewerkstelligen, fügen Sie
ein Listener zu dem `debug.web.load_panels` Event hinzu. Dieses Event wird verwendet, wenn
die Web Debug Werkzeugleiste die möglichen Paneele sammelt. Bearbeiten Sie als erstes die
`config/ProjectConfiguration.class.php` Datei um auf das Event zu achten:

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      // ...

      $this->dispatcher->connect('debug.web.load_panels', array(
        'acWebDebugPanelDocumentation',
        'listenToLoadDebugWebPanelEvent'
      ));
    }

Jetzt fügen wir die `listenToLoadDebugWebPanelEvent()` Listener Funktion zur
`acWebDebugPanelDocumentation.class.php` Datei um das Paneel in die
Werkzeugleiste hinzuzufügen:

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->setPanel(
        'documentation',
        new self($event->getSubject())
      );
    }

Das wars! Aktualisieren Sie Ihren Browser und Sie werden sofort das Ergebnis sehen.

![Web Debug Werkzeugleiste](http://www.symfony-project.org/images/more-with-symfony/web_debug_02.png "Die Web Debug Werkzeugleiste mit einem neuen, benutzerdefinierten Paneel")

>**TIP**
> Seit symfony 1.3 kann ein `sfWebDebugPanel` URL Parameter verwendet werden
> um ein spezielles Web Debug Paneel beim Laden der Seite zu laden. Um zum
> Beispiel das neue Dokumentationspanel automatisch zu öffnen, muss an die URL
>`?sfWebDebugPanel=documentation` angehängt werden. Dies kann sehr nützlich
> sein während man benutzerdefinierte Paneele erstellt.

Die drei Typen von Web Debug Paneelen
-----------------------------------

Hinter den Kulissen gibt es eigentlich drei verschiedene Typen von Web Debug Paneelen.

### Der *nur-Symbol* Paneel Typ

Der grundlegendste Typ von Paneelen ist der, welche ein Symbol,
einen Text und nichts anderes in der Werkzeugleiste zeigt. Das
klassische Beispiel ist das `Arbeitsspeicher` Paneel, welches den Speicherverbrauch
anzeigt aber nichts tut, wenn man es anklickt. Um eine *nur-Symbol* Paneel zu
erstellen, geben Sie in der `getPanelContent()` einfach einen leeren String
zurück. Die ein zigste Ausgabe des Panels kommt von der `getTitle()` Methode:

    [php]
    public function getTitle()
    {
      $totalMemory = sprintf('%.1f', (memory_get_peak_usage(true) / 1024));

      return '<img src="'.$this->webDebug->getOption('image_root_path').'/memory.png" alt="Memory" /> '.$totalMemory.' KB';
    }

    public function getPanelContent()
    {
      return;
    }

### Der *Link* Paneel Typ

Wie das *nur-Symbol* Paneel hat das *Link* Paneel keinen Inhalt. Anders als das
*nur-Symbol* Paneel, laden Sie durch das Klicken auf das *Link* Paneel die
URL die von der `getTitleUrl()` Methode des Panels zurückgegeben wird.
Um ein *Link* Paneel zu erstellen, geben Sie in der `getPanelContent()`
einfach einen leeren String zurück und fügen Sie der Klasse eine `getTitleUrl()` Methode hinzu.

    [php]
    public function getTitleUrl()
    {
      // link to an external uri
      return 'http://www.symfony-project.org/api/1_3/';

      // or link to a route in your application
      return url_for('homepage');
    }

    public function getPanelContent()
    {
      return;
    }

### Der *Inhalt* Paneel Typ

Der bei weitem häufigsten Typ Paneele ist das *Inhalts* Paneel. Dieses
Paneel hat einen kompletten HTML Inhalt der angezeigt wird, wenn Sie
auf das Paneel in der Debug Werkzeugleiste klicken. Um ein Paneel
von diesem Typ zu erstellen, stellen Sie sicher, das die `getPanelContent()`
Methode mehr als nur einen leeren String zurückgibt.

Anpassungen des Paneel Inhalts
-------------------------

Jetzt da Sie Ihr benutzerdefiniertes Web Debug Paneel erstellt und der
Werkzeugleiste hinzugefügt haben, kann einfach durch die `getPanelContent()`
Methode Inhalt hinzugefügt werden. Symfony stellt mehrere Methoden zur Verfügung,
die Ihnen dabei assistieren, den Inhalt reich und nutzbar zu machen.

### ~`sfWebDebugPanel::setStatus()`~

Standartmäßig wird jedes Paneel der Web Debug Werkzeugleiste mit dem
grauen Standardhintergrund dargestellt. Dies kann jedoch auf Orange oder Rot geändert
werden, wenn der Inhalt im Paneel spezielle Aufmerksamkeit erfordert.

![Web Debug Werkzeugleiste mit Fehler](http://www.symfony-project.org/images/more-with-symfony/web_debug_05.png "Die Web Debug Werkzeugleiste wie sie einen fehlerhaften Status im Protokoll zeigt")

Um die Hintergrundfarbe des Panels zu ändern, wird die `setStatus()` Methode verwendet.
Diese Methode akzeptiert jede `Prioitätskonstante` aus der
[sfLogger](http://www.symfony-project.org/api/1_3/sfLogger) Klasse.
Insbesondere gibt es drei verschiedene Statuslevels die drei verschiedenen
Hintergrundfarben (grau, orange und rot) für das Paneel entsprechen. Meistens
wird die `setStatus()` Methode von innerhalb der `getPanelContent()` Methode
aufgerufen, wenn ein Zustand eingetreten ist, der besondere Aufmerksamkeit erfordert.

    [php]
    public function getPanelContent()
    {
      // ...

      // setzt die Hintergrundfarbe auf Grau (der Standard)
      $this->setStatus(sfLogger::INFO);

      // setzt die Hintergrundfarbe auf Orange
      $this->setStatus(sfLogger::WARNING);

      // setzt die Hintergrundfarbe auf Rot
      $this->setStatus(sfLogger::ERR);
    }

### ~`sfWebDebugPanel::getToggler()`~

Eins der häufigsten gemeinsamen Merkmale von bestehenden Web Debug Paneelen
ist ein Umschalter: Ein optisches Pfeilelement, dass Inhalt
zeigt bzw. versteckt, wenn es angeklickt wird.

![Web Debug Umschalter](http://www.symfony-project.org/images/more-with-symfony/web_debug_03.png "Der Web Debug Umschalter in Aktion")

Diese Funktionalität kann einfach über die `getToggler()` Funktion
in benutzerdefinierten Web Debug Paneelen verwendet werden. Angenommen
wir wollen zum Beispiel eine Liste mit Inhalt im Paneel umschalten:

    [php]
    public function getPanelContent()
    {
      $listContent = '<ul id="debug_documentation_list" style="display: none;">
        <li>List Item 1</li>
        <li>List Item 2</li>
      </ul>';

      $toggler = $this->getToggler('debug_documentation_list', 'Toggle list');

      return sprintf('<h3>List Items %s</h3>%s',  $toggler, $listContent);
    }

Die `getToggler()` Funktion nimmt zwei Argumente entgegen: die DOM `id` des
Elements das umgeschaltet werden soll und einen `Titel` Attribut für den Umschaltlink.
Es ist Ihre Aufgabe das DOM Element mit dem richtigen `id` Attribut anzulegen. Des
weiteren müssen Sie die Beschreibung (z.B. "Listenelemente") für den Umschalter
anlegen.


### ~`sfWebDebugPanel::getToggleableDebugStack()`~

Ähnlich zu `getToggler()` rendert `getToggleableDebugStack()` einen anklickbaren Pfeil,
der die Anzeige von Inhalt umschaltet. In diesem Fall ist der Inhalt ein Debug Stacktrace.
Diese Funktion ist nützlich, wenn Protokolle für eine benutzerdefinierte Klasse anzeigen
werden müssen. Angenommen wir wollen zum Beispiel in der Klasse `myCustomClass`
etwas benutzerdefiniertes protokolieren:

    [php]
    class myCustomClass
    {
      public function doSomething()
      {
        $dispatcher = sfApplicationConfiguration::getActive()
          ->getEventDispatcher();

        $dispatcher->notify(new sfEvent($this, 'application.log', array(
          'priority' => sfLogger::INFO,
          'Anfang der Ausführung von myCustomClass::doSomething()',
        )));
      }
    }

Als ein Beispiel wird eine Liste mit `myCustomClass` Protokollnachrichten zusammen
mit den zugehörigen Debug Stacktrace angezeigt.

    [php]
    public function getPanelContent()
    {
      // holt alle Protokollnachrichten für die aktuelle Anfrage
      $logs = $this->webDebug->getLogger()->getLogs();

      $logList = '';
      foreach ($logs as $log)
      {
        if ($log['type'] == 'myCustomClass')
        {
          $logList .= sprintf('<li>%s %s</li>',
            $log['message'],
            $this->getToggleableDebugStack($log['debug_backtrace'])
          );
        }
      }

      return sprintf('<ul>%s</ul>', $logList);
    }

![Web Debug Umschaltbarer Debugstack](http://www.symfony-project.org/images/more-with-symfony/web_debug_04.png "Der Web Debug Umschaltbarer Debugstack in Aktion")

>**NOTE**
>Auch ohne das Erstellen eines benutzerdefinierten Panels, würden diese
>Protokollnachrichten für `myCustomClass` im `Protokoll` Paneel angezeigt.
>Der Vorteil hier ist, dass diese Gruppe von Protokollnachrichten an einer
>Stelle gruppiert werden und ihr erscheinen kontrolliert werden kann.

### ~`sfWebDebugPanel::formatFileLink()`~

Die Möglichkeit auf Dateien in der Web Debug Werkzeugleiste zu klicken und
diese im bevorzugten Texteditor geöffnet zu bekommen ist neu in symfony 1.3.
Mehr Informationen finden sie im ["Was ist neu"](http://www.symfony-project.org/tutorial/1_3/de/whats-new)
Artikel von symfony 1.3.

Um diese Funktion für eine spezielle Datei zu aktivieren, wird die `formatFileLink()`
verwendet. Zusätzlich zur Datei selber kann optional eine genaue Zeile angegeben
werden. Der folgende Quellcode würde auf Zeile 15 von `config/ProjectConfiguration.class.php`
verweisen:

    [php]
    public function getPanelContent()
    {
      $content = '';

      // ...

      $path = sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php';
      $content .= $this->formatFileLink($path, 15, 'Project Configuration');

      return $content;
    }

Sowohl das zweite (die Zeilennummer) als auch das dritte Argument (der Verknüpfungstext)
sind optional. Wenn kein "Verknüpfungstext" Argument angegeben wurde, wird der
Dateipfad als Text für die Verknüpfung angezeigt.

>**NOTE**
>Stellen sie sicher, dass Sie die neue Dateiverküpfung vor dem Testen
>konfiguriert haben. Diese Funktion wird durch den `sf_file_link_format` Schlüssel
>in `settings.yml` oder durch den `file_link_format` Schlüssel in
>[xdebug](http://xdebug.org/docs/stack_trace#file_link_format) konfiguriert.
>Die letztere Methode stellt sicher, dass das Projekt nicht an eine spezielle
>Entwicklungsumgebung gebunden ist.

Weitere Tricks mit der Web Debug Werkzeugleiste
---------------------------------------

Meistens wird das Spezielle in Ihrem Web Debug Paneel im Inhalt und der Information
die Sie darstellen liegen. Es gibt allerdings noch ein paar weitere Tricks, die es
wert sind erwähnt zu werden.

### Entfernen von Standard Paneelen

Standartmäßig fügt symfony automatisch mehrere Web Debug Paneele in die
Web Debug Werkzeugleiste ein. Durch Verwendung des `debug.web.load_panels`
Event, können diese Standardpanele einfach entfernt werden. Verwenden Sie
dieselbe Listener Funktion, die Sie oben schon deklariert haben, aber ersetzen
Sie den Funktionsinhalt mit der `removePanel()` Funktion. Der folgende
Quellcode entfernt das `Arbeitsspeicher` Paneel von der Werkzeugleiste:

    [php]
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->removePanel('memory');
    }

### Zugreifen auf die Request Parameter innerhalb eines Panels

Eine der am Meisten gebrauchten Sachen innerhalb eines Web Debug Panels sind
die Request Parameter. Wollen Sie zum Beispiel Informationen aus einer Datenbank
über ein `Event` Objekt der Datenbank anzeigen, dass über ein `event_id` Request Parameter
zugeordnet wird:

    [php]
    $parameters = $this->webDebug->getOption('request_parameters');
    if (isset($parameters['event_id']))
    {
      $event = Doctrine::getTable('Event')->find($parameters['event_id']);
    }

### bedingtes Verstecken eines Panels

Manchmal wird Ihr Paneel keine nützliche Information für die aktuelle Anfrage
zum Anzeigen haben. In diesen Situationen können Sie Ihr Paneel komplett ausblenden.
Angenommen das benutzerdefinierte Paneel aus dem
vorherigen Beispiel zeigt keine Informationen außer ein `event_id`
Parameter wurde übergeben. Um das Paneel auszublenden, geben Sie in der
`getTitle()` Methode keinen Inhalt zurück:

    [php]
    public function getTitle()
    {
      $parameters = $this->webDebug->getOption('request_parameters');
      if (!isset($parameters['event_id']))
      {
        return;
      }

      return '<img src="/acWebDebugPlugin/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
    }

Abschließende Worte
--------------

Die Web Debug Werkzeugleiste existiert um das Leben des Entwicklers
leichter zu machen, aber es ist mehr als ein passives Informationsdisplay.
Durch das hinzufügen von benutzerdefinierte Paneelen ist das Potential
der Web Debug Werkzeugleiste nur durch den Ideenreichtum des Developers
begrenzt. Das  [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
beinhaltet einige Paneel die erstellt werden können. Nutzen Sie die Möglichkeit
ihre eigenen zu erstellen.
