Die Umgebungen
==============

Wenn Sie einen Blick in das Verzeichnis `web/` werfen, dann finden Sie dort zwei 
PHP-Dateien: `index.php` und `frontend_dev.php`. Diese Dateien sind die so 
genannten **Front-Controller**; alle Anfragen an die Anwendung werden über sie 
durchgeführt. Aber warum haben wir für jede Anwendung zwei Front-Controller?

Beide Dateien verweisen auf dieselbe Anwendung, aber für verschiedene 
**Umgebungen**. Wenn Sie eine Anwendung entwickeln, und Sie entwickeln nicht 
direkt auf dem Produktions-Server, dann benötigen Sie verschieden Umgebungen:

  * Die **Entwicklungs-Umgebung**: Mit dieser Umgebung arbeiten die 
    **Web-Entwickler** wenn Sie an der Anwendung arbeiten, um neue Funktionen 
    einzubauen, Fehler zu bereinigen, ...

  * Die **Test-Umgebung**: Diese Umgebung wird für automatische Anwendungs-Tests 
    verwendet.

  * Die **Probe-Umgebung**: Diese Umgebung wird vom **Kunden** verwendet, um die 
    Anwendung zu testen und um Fehler oder fehlende Funktionalität zu melden.

  * Die **Produktions-Umgebung**: Dies ist die Umgebung, mit der der 
    **End-Anwender** interagiert.

Was unterscheidet eine Umgebung von der anderen? In der Entwicklungs-Umgebung 
zum Beispiel muss eine Anwendung zur Vereinfachung des Debuggens alle Details 
einer Anfrage protokollieren, das Cache-System aber muss abgeschaltet sein, 
damit alle Änderungen am Code sofort berücksichtigt werden. Das heißt, dass die 
Entwicklungs-Umgebung für den Entwickler optimiert werden muss. Das beste 
Beispiel ist sicher das Auftreten einer Exception. Um dem Entwickler beim 
Beheben des Problems schneller zu helfen, zeigt symfony die Exception direkt im 
Browser an mitsamt allen Informationen, die es über die laufende Anfrage hat:

![An exception in the dev environment](http://www.symfony-project.org/images/getting-started/1_4/exception_dev.png)

In der Produktions-Umgebung jedoch muss das Cache-System aktiviert sein, und die 
Anwendung muss natürlich eine angepasste Fehlermeldung statt der reinen 
Exception anzeigen. Die Produktions-Umgebung muss also hinsichtlich Performance 
und an die Erfahrung des Anwenders angepasst werden.

![An exception in the prod environment](http://www.symfony-project.org/images/getting-started/1_4/exception_prod.png)

>**TIP**
>Wenn Sie die Dateien der Front-Controller öffnen, sehen Sie, dass sie sich bis 
>auf das Setzen der Umgebung gleichen:
>
>     [php]
>     // web/index.php
>     <?php
>
>     require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
>
>     $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
>     sfContext::createInstance($configuration)->dispatch();

Die Webdebug-Toolbar ist ebenfalls ein gutes Beispiel für das Nutzen einer 
Umgebung. Sie ist auf allen Seiten in der Entwicklungs-Umgebung eingeblendet und 
gibt Ihnen beim Klicken auf die verschiedenen Tabs eine Fülle an Informationen: 
die laufende Anwendungs-Konfiguration, die Logs für die laufende Anfrage, die 
mit der Datenbank-Engine verarbeiteten SQL-Befehle, Informationen über 
Speichernutzung, sowie Informationen über die gebrauchte Zeit.