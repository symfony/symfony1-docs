Emails
======

*von Fabien Potencier*

Das Versenden von Emails mit symfony ist dank der 
[Swift Mailer](http://www.swiftmailer.org/)-Bibliothek gleichermaßen einfach 
wie mächtig. Obwohl das Versenden über Swift Mailer schon relativ einfach ist, 
stellt symfony einen Wrapper bereit, der das Versenden der Emails noch 
flexibler und mächtiger macht. Dieses Kapitel soll zeigen, wie man diese 
mächtigen Funktionen optimal nutzen kann.

>**NOTE**
>symfony 1.3 nutzt die Swift Mailer-Version 4.1.

Einführung
----------

Die Email-Verwaltung in symfony geschieht grundsätzlich in einem Mailer-Objekt.
Genau wie viele andere symfony-Kernobjekte auch ist das Mailer-Objekt eine 
Factory. Das Objekt wird über die `factories.yml` konfiguriert und ist 
von überall aus über die Kontext-Instanz erreichbar: 

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIPP**
>Im Gegensatz zu anderen Factorys wird der Mailer nur bei Bedarf geladen und 
>initialisiert. Wird er nicht benötigt, hat das auch keine Auswirkungen auf 
>die Performance. 

Dieses Tutorial zeigt die Integration des Swift Mailers in symfony. Wenn
Sie tiefere Einblicke in die Swift Mailer-Bibliothek bekommen möchten, verweise 
ich auf die Swift Mailer-[Dokumentation](http://www.swiftmailer.org/docs).

Emails aus einer Action heraus abschicken
-----------------------------------------

Um in einer Action an die Mailer-Instanz zu kommen, nutzen man einfach die 
`getMailer()` Methode:

    [php]
    $mailer = $this->getMailer();

### Der schnellste Weg

Das Abschicken der Email wird dann über die ~`sfAction::composeAndSend()` 
Methode durchgeführt:

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

Der `composeAndSend()` Methode werden vier Argumente übergeben:

 * die Email-Adresse des Absenders;
 * die Email-Adresse(n) des Empfängers;
 * der Email-Betreff;
 * Der Email-Body.

An jeder Stelle, an der eine Email-Adresse als Methoden-Parameter übergeben 
wird, kann sowohl ein String als auch ein Array übergeben werden:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

Natürlich kann die Email an mehrere Personen auf einmal verschickt werden, 
indem der Methode als zweites Argument ein Array übergeben wird:

    [php]
    $to = array(
      'foo@example.com',
      'bar@example.com',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

    $to = array(
      'foo@example.com' => 'Mr Foo',
      'bar@example.com' => 'Miss Bar',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

### Der flexible Weg

Für mehr Flexibilität kann auch die ~`sfAction::compose()`~-Methode genutzt 
werden, um eine Nachricht zu erstellen, diese den eigenen Wünschen entsprechend 
anzupassen und letztendlich zu verschicken. Das könnte zum Beispiel hilfreich sein, 
wenn man der Email eine Datei anhängen möchte:

    [php]
    // create a message object
    $message = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // send the message
    $this->getMailer()->send($message);

### Der überzeugendste Weg

Zu guter Letzt kann das Nachrichten-Objekt auch direkt erstellt werden, um noch 
flexibler zu sein:

    [php]
    $message = Swift_Message::newInstance()
      ->setFrom('from@example.com')
      ->setTo('to@example.com')
      ->setSubject('Subject')
      ->setBody('Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    $this->getMailer()->send($message);

>**TIPP**
>Die Kapitel 
>["Creating Messages/Nachrichten erstellen"](http://swiftmailer.org/docs/messages) 
>und ["Message Headers/Nachrichten-Header"](http://swiftmailer.org/docs/headers) der 
>offiziellen Swift Mailer-Dokumentation beinhaltet alle Informationen, die benötigt  
>werden, um Nachrichten zu erstellen.

### Verwendung der symfony View

Durch das Abschicken der Emails aus einer Action kann problemlos auf 
Partials und Komponenten zugegriffen werden.

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

Konfiguration
-------------

Genau wie jede andere symfony-Factory wird der Mailer in der `factories.yml` 
Konfigurationsdatei konfiguriert. Die Standardkonfiguration sieht wie folgt 
aus:

    [yml]
    mailer:
      class: sfMailer
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        delivery_strategy: realtime
        transport:
          class: Swift_SmtpTransport
          param:
            host:       localhost
            port:       25
            encryption: ~
            username:   ~
            password:   ~

Durch das Erstellen einer neuen Applikation überschreibt die lokale 
`factories.yml` Konfigurationsdatei die Standardkonfiguration mit bestimmten 
Werten für die `prod`-, `env`- und `test`-Umgebung:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

Die Versandmethode
----------------------

Eines der mächtigsten Features der Swift Mailer-Integration in symfony ist die 
Versandmethode. Über die Versandmethode kann man symfony mitteilen, 
wie die Email-Nachrichten zugestellt werden sollen. Konfiguriert wird dies über 
die ~`delivery_strategy`~ Einstellung der `factories.yml`. Die Versandmethode 
bestimmt das Verhalten der ~`send()`|`sfMailer::send()`~ Methode:

 * `realtime`:       Nachrichten werden in Echtzeit verschickt.
 * `single_address`: Nachrichten werden an eine einzelne Adresse verschickt.
 * `spool`:          Nachrichten werden in einer Warteschlange vorgehalten.
 * `none`:           Nachrichten werden einfach ignoriert.

### Die ~`Echtzeit`~ Methode

Die `Echtzeit`-Methode ist die Standard-Versandmethode und ist am 
einfachsten zu konfigurieren, da nichts Spezielles getan werden muss.

Email-Nachrichten werden über den Transport, welcher im `transport`- 
Abschnitt der `factories.yml`-Konfigurationsdatei konfiguriert wird, 
versendet (Im nächsten Kapitel erfahren Sie mehr über die 
Konfiguration des Email-Transports).

### Die ~`Einzel_Adressen`~ Methode

Bei der `Einzel_Adressen` Methode werden alle Nachrichten an eine einzelne 
Adresse geschickt. Konfiguriert wird dies über die `delivery_address` 
Einstellung.

Diese Methode ist vor allem in der Entwicklungsumgebung äußerst hilfreich, 
um einerseits das Verschicken von Nachrichten an echte User zu verhindern. 
Andererseits kann der Entwickler dadurch trotzdem die gerenderte Nachricht 
überprüfen.

>**TIPP**
>Falls die originalen `to`-, `cc`- und `bcc`-Empfänger verifiziert werden 
>müssen, können die Werte in den folgenden Headern gefunden werden: 
>`X-Swift-To`, `X-Swift-Cc` und `X-Swift-Bcc`

Die Email-Nachrichten werden über den gleichen Transportweg verschickt wie bei 
der `Echtzeit`-Methode.

### Die ~`Spulen`~ Methode

Bei der `Spulen`-Methode werden die Nachrichten in einer Queue gespeichert.

Für die Produktionsumgebung ist das die beste Methode, da Web-Requests nicht 
warten, bis die Emails versendet wurden.

Die `Spulen`-Klasse wird über die ~`spool_class`~ Einstellung konfiguriert.
Symfony hat folgende Einstellungen standardmäßig gesetzt:

 * ~`Swift_FileSpool`~: Nachrichten werden im Dateisystem gespeichert.

 * ~`Swift_DoctrineSpool`~: Nachrichten werden in einem Doctrine-Modell 
   gespeichert.

 * ~`Swift_PropelSpool`~: Nachrichten werden in einem Propel-Modell 
   gespeichert.

Beim Instanziieren der Spule werden die ~`spool_arguments`~ Einstellungen 
als Konstruktor-Argumente verwendet. Folgendes sind die möglichen Optionen 
für die Queue-Klassen:

 * `Swift_FileSpool`:

    * Der absolute Pfad des Queue-Verzeichnisses (Nachrichten werden in 
      diesem Verzeichnis gespeichert)

 * `Swift_DoctrineSpool`:

    * Das Doctrine-Modell, um die Nachrichten zu speichern 
      (standardmäßig `MailMessage`)

    * Das Feld, in dem die Nachricht gespeichert wird (standardmäßig 
      `message`)

    * Die Methode, die aufgerufen werden muss, um die Nachrichten zu 
      verschicken (optional). Die Methode bekommt die Queue-Optionen 
      als Argumente zurück.
      
 * `Swift_PropelSpool`:

    * Das Propel-Modell, um die Nachrichten zu speichern 
      (standardmäßig `MailMessage`)

    * Das Feld, in dem die Nachricht gespeichert wird (standardmäßig 
      `message`)

    * Die Methode, die aufgerufen werden muss, um die Nachrichten zu 
      verschicken (optional). Die Methode bekommt die Queue-Optionen 
      als Argumente zurück.
      
Folgende ist die klassische Konfiguration für eine Doctrine-Spule:

    [yml]
    # Schema configuration in schema.yml
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: clob, notnull: true }

-

    [yml]
    # configuration in factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Und die gleiche Konfiguration für eine Propel-Spule:

    [yml]
    # Schema configuration in schema.yml
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~

-

    [yml]
    # configuration in factories.yml
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class:       Swift_PropelSpool
          spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Über den ~`project:send-emails`~ Task werden die in einer Queue gespeicherten 
Nachrichten verschickt (dabei ist zu beachten, dass dieser Task komplett 
unabhängig von der Queue-Impementiereung und deren Optionen läuft):

    $ php symfony project:send-emails

>**BEMERKUNG**
>Der `project:send-emails`-Task benötigt eine `application`- und `env`-Option.

Beim `project:send-emails` Task werden die Email-Nachrichten über den gleichen 
Transportweg versendet wie bei der `Echtzeit`-Methode.

>**TIPP**
>Der `project:send-emails`-Task kann auf jeder Maschine ausgeführt werden und 
>nicht nur auf der Maschine, auf der die Nachricht erstellt wurde. Das liegt 
>daran, dass alles im Nachrichten-Objekt gespeichert wird - sogar die 
>Dateianhänge.

-

>**BEMERKUNG**
>Die Implementierung der Queues ist sehr einfach. Nachrichten werden ohne 
>jegliches Fehler-Management verschickt, so als ob die `Echtzeit`-Methode 
>verwendet würde. Natürlich können die Standard-Queue-Klassen erweitert 
>werden, um eigene Logik und Fehler-Management zu implementieren.

Der `project:send-emails`-Task nutzt zwei optionale Argumente:

 * `message-limit`: Limitiert die Anzahl der zu verschickenden Nachrichten.

 * `time-limit`: Limitiert die Zeit, die für das Verschicken der Nachrichten 
   aufgebracht werden soll (in Sekunden).

Beide Optionen können wie folgt kombiniert werden:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

Obiger Befehl endet, sobald entweder 10 Nachrichten verschickt wurden oder 
aber 20 Sekunden abgelaufen sind.

Trotzdem kann es auch bei der `Spulen`-Methode vorkommen, dass eine 
Nachricht direkt versendet werden muss ohne dass die Nachricht in der 
Queue gespeichert wird. Die `sendNextImmediately()`-Methode des Mailers 
ermöglicht dies:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Im vorigen Beispiel wird `$message` nicht in der Queue gespeichert, sondern 
direkt verschickt. Wie der Name schon impliziert, verschickt die 
`sendNextImmediately()`-Methode lediglich die nächste Nachricht.

>**BEMERKUNG**
>Die `sendNextImmediately()`-Methode zeigt keine Wirkung, wenn die Sendemethode 
>nicht die `Spule` ist. 

### Die ~`keine`~ Methode

Diese Methode ist optimal geeignet für die Entwicklungsumgebung, um das 
Versenden von Emails an echte User zu verhindern. In der Web Debug-Toolbar sind 
die Nachrichten trotzdem verfügbar (Der Abschnitt unten enthält weitere 
Informationen über das Mailer Panel).

Für die Testumgebung ist diese Methode ebenfalls die beste. Das 
`sfTesterMailer`-Objekt ermöglicht das Testen ohne die Nachrichten wirklich 
zu versenden (Der Abschnitt unten enthält weitere Informationen über das 
Testen).

Der Email-Versand
-----------------

Email-Nachrichten werden über den `Transport` versendet. Dieser wird in der 
`factories.yml`-Datei konfiguriert. Die Standard-Einstellung nutzt den 
SMTP-Server der lokalen Maschine:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Der Swift Mailer beinhaltet standardmäßig drei unterschiedliche 
`transport`-Klassen:

  * ~`Swift_SmtpTransport`~: Nutzt einen SMTP-Server, um Nachrichten zu 
    versenden

  * ~`Swift_SendmailTransport`~: Nutzt `sendmail`, um Nachrichten zu 
    versenden

  * ~`Swift_MailTransport`~: Nutzt die native PHP `mail()`-Funktion, um 
    Nachrichten zu versenden

>**TIPP**
>Der ["Transporttypen"](http://swiftmailer.org/docs/transport-types)-
>Abschnitt der offiziellen Swift Mailer-Dokumentation enthält alle 
>Informationen über die verfügbaren `transport`-Klassen und ihre 
>einzelnen Parameter

Emails über einen Task verschicken
----------------------------------

Das Versenden einer Email über einen Task ähnelt dem Versand über eine 
Action. Das Task-System stellt ebenfalls eine `getMailer()`-Methode bereit.

Beim Erstellen des Mailers nutzt das Task-System die aktuelle Konfiguration. 
Möchte man also die Konfiguration einer bestimmten Applikation verwenden, 
so kann die `--application`-Option genutzt werden (Weitere Informationen zu 
diesem Thema sind im Abschnitt Tasks zu finden).

Zu beachten ist noch, dass der Task die gleiche Konfiguration nutzt wie die 
Controller. Um also den Versand bei der `Spulen`-Methode zu erzwingen, nutzt 
man `sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Debuggen
--------

Bisher war das Debuggen von Emails meist ein Alptraum. Dank der symfony 
~web debug toolbar~ ist das nun ganz einfach.

Schnell und komfortabel zeigt sie dem User im Browser an, wie viele 
Nachrichten von der aktuellen Action verschickt wurden.

![Emails in der Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Emails in der Web Debug Toolbar")

Durch Klicken auf das Email-Icon werden die verschickten Nachrichten im 
Panel angezeigt.

![Emails in der Web Debug Toolbar - Details](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Emails in der Web Debug Toolbar - Details")

>**BEMERKUNG**
>Nach jeder verschickten Email, fügt symfony einen Eintrag in die Log-Datei ein.

Testen
-------

Natürlich wäre die Integration des Swift Mailers nicht komplett, wenn es nicht 
einen ordentlichen Weg gäbe, ihn zu testen. Hierzu erstellt symfony 
standardmäßig einen `mailer`-Tester (~`sfMailerTester`~), um das Versenden 
von Emails in funktionalen Tests zu ermöglichen.

Die ~`hasSent()`~ Methode testet die Anzahl der im aktuellen Request verschickten 
Nachrichten:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

Der vorige Code überprüft, ob die `/foo`-URL exakt eine Email verschickt.

Bei jeder verschickten Email können durch die ~`checkHeader()`~ und 
~`checkBody()`~ Methoden auch noch weitere Details getestet werden:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Das zweite Argument der `checkHeader()`- und das erste Argument der 
`checkBody()`-Methode können wie folgt aussehen:

 * ein String, um den exakten Wert zu testen;

 * ein regulärer Ausdruck, der den Wert überprüft;

 * ein negativer regulärer Ausdruck (Regulärer Ausdruck, der mit `!` startet), 
   der überprüft, ob der Wert nicht übereinstimmt.
   
Normalerweise werden die Prüfungen innerhalb der ersten verschickten Nachricht 
vollzogen. Werden mehrere Nachrichten versendet, kann man über die 
~`withMessage()`~ Methode bestimmen, welche Nachricht getestet werden soll:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(2)->
        withMessage('foo@example.com')->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Die `withMessage()`-Methode bekommt den Empfänger als erstes Argument. Als 
zweites Argument zeigt welche Nachricht getestet werden soll, wenn mehrere 
Nachrichten versendet werden.

Zu guter Letzt zeigt die ~`debug()`~ Methode einen Dump der verschickten 
Nachrichten an, um Probleme beim Versenden der Nachrichten zu debuggen:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

Email-Nachrichten als Klassen
-----------------------------

In der Einführung dieses Kapitels wurde gezeigt, wie Emails aus einer 
Action heraus verschickt werden. Wahrscheinlich ist das auch der 
einfachste und beste Weg, um mit symfony eine geringe Anzahl an Emails 
zu verschicken. 

Wenn die Applikation allerdings eine Großzahl an unterschiedlichen Emails 
verschicken soll, gibt es eine bessere Lösung.

>**BEMERKUNG**
>Als zusätzlichen Bonus können Klassen für Email-Nachrichten in verschiedenen 
>Applikationen genutzt werden - beispielsweise im Frontend und im Backend.

Da Nachrichten reine PHP-Objekte sind, ist es offensichtlich, dass für jede 
Nachricht eine Klasse erstellt werden sollte:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends Swift_Message
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        $this
          ->setFrom(array('app@example.com' => 'My App Bot'))
          ->attach('...')
        ;
      }
    }

Ob eine Nachricht nun aus einer Action heraus oder sonst wo verschickt 
wird, kommt lediglich darauf an, welche Klasse initialisiert wird:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Natürlich ist es trotzdem praktisch bestimmte Dinge in einer 
Basis-Klasse zu zentralisieren. Beispielsweise die gemeinsamen 
Header - wie der `From`-Header - oder eine gemeinsame Signatur:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        // specific headers, attachments, ...
        $this->attach('...');
      }
    }

    // lib/email/ProjectBaseMessage.class.php
    class ProjectBaseMessage extends Swift_Message
    {
      public function __construct($subject, $body)
      {
        $body .= <<<EOF
    --

    Email sent by My App Bot
    EOF
        ;
        parent::__construct($subject, $body);

        // set all shared headers
        $this->setFrom(array('app@example.com' => 'My App Bot'));
      }
    }

Wenn eine Nachricht abhängig von irgendwelchen Model-Objecten ist, können 
diese natürlich als Argumente an den Konstruktor übergeben werden:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($user)
      {
        parent::__construct('Confirmation for '.$user->getName(), 'Body');
      }
    }

Rezepte
-------

### Emails via ~Gmail~ versenden

Ist kein SMTP-Server vorhanden, stattdessen aber ein Gmail-Account, so kann 
die folgende Konfiguration genutzt werden, um Nachrichten über die 
Google-Server zu verschicken und archivieren:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   your_gmail_username_goes_here
        password:   your_gmail_password_goes_here

Ersetzen sie `username` und `password` mit ihren Gmail-Zugangsdaten und 
fertig.

### Anpassen des Mailer-Objekts

Sollte die Konfigurierung des Mailers über die `factories.yml` nicht 
genügen, so kann der Mailer durch einen Listener auf den 
~`mailer.configure`~ Event weiter angepasst werden.

Der Event kann auch über die `ProjectConfiguration`-Klasse verbunden werden:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->dispatcher->connect(
          'mailer.configure',
          array($this, 'configureMailer')
        );
      }

      public function configureMailer(sfEvent $event)
      {
        $mailer = $event->getSubject();

        // do something with the mailer
      }
    }

Der folgende Abschnitt zeigt einige schlagkräftige Beispiele für 
den Gebrauch dieser Technik.

### Benutztung des ~Swift Mailer Plugins~

Um Swift Mailer-Plugins zu nutzen, muss ein Listener auf den 
`mailer.configure`-Event erstellt werden (s. im folgenden Abschnitt):

    [php]
    public function configureMailer(sfEvent $event)
    {
      $mailer = $event->getSubject();

      $plugin = new Swift_Plugins_ThrottlerPlugin(
        100, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
      );

      $mailer->registerPlugin($plugin);
    }

>**TIPP**
>Der ["Plugins"](http://swiftmailer.org/docs/plugins)-Abschnitt der 
>offiziellen Swift Mailer-Dokumentation beinhalten alle Informationen, die 
>man für die Plugins benötigt.

### Anpassen des Spulen-Verhaltens

Die Standard-Implementierung der Spule ist sehr einfach. Jede einzelne Spule 
nimmt die Emails in einer zufälligen Reihenfolge aus der Queue und verschickt 
diese dann.

Eine Spule kann so konfiguriert werden, das entweder die Zeit (in Sekunden), 
die für den Sendevorgang aufgebracht wird, oder aber die Anzahl der zu 
verschickenden Nachrichten limitiert wird:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

Dieser Abschnitt zeigt, wie man ein Prioritäts-System für die Abarbeitung 
der Queue implementieren kann. Es wird gezeigt wie man seine eigene Logik 
implementieren kann.

Zuerst muss ein `priority`-Feld im Schema hinzugefügt werden:

    [yml]
    # for Propel
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # for Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: clob, notnull: true }
        priority: { type: integer }

Beim Versenden der Email muss der Prioritäts-Header gesetzt werden (1 steht 
für die höchste/wichtigste Priorität):

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

Als nächstes muss die Standard-`setMessage()`-Methode überlagert werden, um 
die Priorität des `MailMessage`-Objekts zu ändern:

    [php]
    // for Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->setPriority($msg->getPriority());

        return parent::setMessage($message);
      }
    }

    // for Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->priority = $msg->getPriority();

        return $this->_set('message', $message);
      }
    }

Zu beachten ist, dass die Nachricht in der Queue serialisiert wird. Das heißt, 
dass sie deserialisiert werden muss, bevor sie den Prioritäts-Wert bekommen. 
Nun muss eine Methode erstellt werden, die die Nachrichten nach Priorität 
ordnet:

    [php]
    // for Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // for Doctrine
    class MailMessageTable extends Doctrine_Table
    {
      public function getSpooledMessages()
      {
        return $this->createQuery('m')
          ->orderBy('m.priority')
          ->execute()
        ;
      }

      // ...
    }

Der letzte Schritt ist das Definieren einer Abfrage-Methode in der 
`factories.yml`-Konfiguration, um den üblichen Weg zu ändern, wie 
Nachrichten aus der Queue geholt werden:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

Das ist alles, was getan werden muss. Von nun an, wird jede Email abhängig 
von ihrer Priorität versendet, wenn der `project:send-emails`-Task 
aufgerufen wird.

>**SIDEBAR**
>Anpassen der Spule mit beliebigen Criterias
>Das vorige Beispiel nutzt einen Standard-Nachrichten-Header - die Priorität. 
>Möchte man allerdings ein anderes Criteria nutzen oder die verschickte 
>Nachricht nicht ändern, so kann das Criteria als Standard-Header gespeichert 
>und vor dem Versendne der Email entfernt werden. 
>
>Zuerst muss der Nachricht, die verschickt werden soll, ein Standard-Header 
>hinzugefügt werden:
>
>     [php]
>     public function executeIndex()
>     {
>       $message = $this->getMailer()
>         ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
>       ;
>     
>       $message->getHeaders()->addTextHeader('X-Queue-Criteria', 'foo');
>     
>       $this->getMailer()->send($message);
>     }
>
>Dann muss der Wert des Headers geholt werden, wenn die Nachricht in der 
>Queue gespeichert wird. Danach wird er direkt gelöscht:
>
>     [php]
>     public function setMessage($message)
>     {
>       $msg = unserialize($message);
>     
>       $headers = $msg->getHeaders();
>       $criteria = $headers->get('X-Queue-Criteria')->getFieldBody();
>       $this->setCriteria($criteria);
>       $headers->remove('X-Queue-Criteria');
>     
>       return parent::_set('message', serialize($msg));
>     }
