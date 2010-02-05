Email
======

*di Fabien Potencier*

L'invio di ~email~ con symfony è facile e potente, grazie all'uso della
libreria [SwiftMailer](http://www.swiftmailer.org/). Nonostante ~SwiftMailer~
renda semplice l'invio di email, symfony aggiungendo un piccolo wrapper sulla
libreria ne semplifica e potenzia le funzionalità. Questo capitolo spiegherà tutta
la potenza messa a disposizione dal framework.

>**NOTE**
>symfony 1.3 include la versione 4.1 di Swift Mailer.

Introduzione
------------

La gestione delle email in symfony è incentrata sull'oggetto mailer. E come per molti
altri oggetti del core di symfony, il mailer è una factory. Questo è inizializzato nel
file di configurazione `factories.yml` ed è sempre disponibile tramite le context instance:

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>Diversamente da altre factory, il mailer è inizializzato e caricato solo su richiesta. 
>Quindi se non usato non inficierà le performance in nessun modo.

Questo tutorial spiega l'integrazione di SwiftMailer in symfony. Se si vuole conoscere ogni
singolo dettaglio della libreria, si consiglia di fare riferimento alla
[documentazione ufficiale](http://www.swiftmailer.org/docs).

Inviare un'email da un'azione
-----------------------------

Recuperare, in un'azione, una istanza dell'oggetto mailer è facile grazie al metodo
di scorciatoia `getMailer()`:

    [php]
    $mailer = $this->getMailer();

### Il modo più veloce

Inviare un'email è reso semplice dall'uso del metodo ~`sfAction::composeAndSend()`~:

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

Il metodo `composeAndSend()` accetta i seguenti parametri:

 * l'indirizzo email del mittente (`from`);
 * il contenitore degli indizzi di destinazione (`to`);
 * il soggetto del messaggio;
 * il corpo del messaggio.

Ogni volta che un metodo accetta un indirizzo email come parametro, allora lo
accetterà nella forma di stringa o array:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

Inoltre, si può inviare un array di indirizzi email come secondo parametro del
metodo, per inviare l'email a più destinatari contemporaneamente;

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

### La via flessibile

Se si cerca la flessibilità, si può usare anche il metodo ~`sfAction::compose()`~
per creare un messaggio, personalizzarlo ed eventualmente spedirlo.
Questo metodo è molto utile quando è necessario aggiungere un ~allegato|email attachment~ 
così come mostrato nel codice successivo:

    [php]
    // crea un oggetto messaggio
    $messaggio = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // invio del messaggio
    $this->getMailer()->send($messaggio);

### La via espressiva

È anche possibile creare un oggetto message direttamente dalla classe sfMailerMessage per ottenere 
ulteriore flessibilità:

    [php]
    $messaggio = sfMailerMessage::newInstance()
      ->setFrom('from@example.com')
      ->setTo('to@example.com')
      ->setSubject('Subject')
      ->setBody('Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    $this->getMailer()->send($messaggio);

>**TIP**
>Le sezioni ["Creating Messages"](http://swiftmailer.org/docs/messages) e
>["Message Headers"](http://swiftmailer.org/docs/headers) della documentazione
>ufficiale di SwiftMailer descrivono tutto quello che è necessario sapere per
>la creazione dei messaggi.

### Usare le viste di Symfony

Inviare le email dalle azioni permetterà di sfruttare la flessibilità dei partial
e dei component abbastanza facilmente.

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

Configurazione
--------------

Come qualsiasi altro factory di symfony, la classe mailer può essere configurata
nel file di configurazione `factories.yml`. La configurazione predefinita è la seguente:

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

Quando viene creata una nuova applicazione, il file di configurazione `factories.yml` locale
sovrascrive quello predefinito modificando alcune variabili associate agli ambienti predefiniti 
`prod`, `env` e `test`:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

Le strategie di spedizione
--------------------------

Una delle funzionalità più utili derivate dall'integrazione di SwiftMailer in symfony
è la strategia di spedizione. La strategia di spedizione permette di specificare a symfony
come inviare le email ed è configurata tramite il parametro ~`delivery_strategy`~ del file
`factories.yml`. La strategia permette di cambiare il modo con il quale il metodo
~`send()`|`sfMailer::send()`~ agisce. Le strategie predefinite disponibili sono quattro
e possono sopperire a tutte le comuni necessità:

 * `realtime`:       I messaggi sono spediti in tempo reale.
 * `single_address`: I messaggi sono spediti ad un singolo indirizzo di posta elettronica.
 * `spool`:          I messaggi sono salvati in una lista.
 * `none`:           I messaggi sono semplicemente ignorati.

### La strategia ~`realtime`~ 

La strategia `realtime` è quella predefinita ed è la più semplice da configurare,
in quanto non c'è nulla da fare.

I messaggi di posta elettronica sono inviati tramite il transport configurato
nella sezione `transport` del file di configurazione `factories.yml` (guardare
la prossima sezione per maggiori informazioni su come configurare il transport dell'email).

### La strategia di ~`single_address`~

Con la strategia di `single_address`, tutti i messaggi sono inviati ad un unico indirizzo
di posta elettronica configurato tramite il parametro `delivery_address`.

Questa strategia è molto comoda nell'ambiente di sviluppo per evitare di inviare messaggi
ad utenti reali, ma permette ad uno sviluppatore di controllare comunque come viene
visualizzata in un client di posta elettronica.

>**TIP**
>Se bisogna verificare i campi originali `to`, `cc` e `bcc`, saranno  reperibili
>rispettivamente come valori dei seguenti header: `X-Swift-To`, `X-Swift-Cc` e
>`X-Swift-Bcc`.

I messaggi email sono inviati tramite lo stesso transport utilizzato per la strategia `realtime`.

### La strategia ~`spool`~

Nella strategia `spool`, i messaggi sono salvati in una coda.

Questa è la migliore strategia per l'ambiente di produzione, in quanto le richieste
web non devono aspettare affinché tutte le email siano inviate.

La classe di `spool` è configurata tramite il parametro ~`spool_class`~. Symfony espone
tre classi predefinite:

 * ~`Swift_FileSpool`~: I messaggi sono salvati sul filesystem.

 * ~`Swift_DoctrineSpool`~: I messaggi sono salvati in un modello Doctrine.

 * ~`Swift_PropelSpool`~: I messaggi sono salvate in un modello Propel.

Quando lo spool è istanziato, il parametro ~`spool_arguments`~ è utilizzato come
costruttore dei parametri. A seguire le opzioni disponibili per le classi predefinite
per creare code:

 * `Swift_FileSpool`:

    * Il percorso assoluto della cartella delle code (i messaggi sono salvati in questa cartella)

 * `Swift_DoctrineSpool`:

    * Il modello Doctrine da usare per salvare i messaggi (Il valore predefinito è `MailMessage`)

    * Il nome della colonna da usare per salvare il messaggio (Il valore predefinito è `message`)

    * Il nome del metodo da chiamare per recuperare i messaggi da spedire (opzionale).
      Riceve la coda di opzioni come parametro.

 * `Swift_PropelSpool`:

    * Il modello Propel da usare per salvare i messaggi (Il valore predefinito è `MailMessage`)

    * Il nome della colonna da usare per salvare il messaggio (Il valore predefinito è `message`)

    * Il nome del metodo da chiamare per recuperare i messaggi da spedire (opzionale).
      Riceve la coda di opzioni come parametro.

Qui di seguito una configurazione di esempio per uno spool con Doctrine:

    [yml]
    # Configurazione di uno schema in schema.yml
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: clob, notnull: true }

-

    [yml]
    # configurazione del file factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

E la stessa configurazione per lo spool con Propel:

    [yml]
    # Configurazione di uno schema in schema.yml
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~

-

    [yml]
    # configurazione del file factories.yml
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class:       Swift_PropelSpool
          spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Per inviare un messaggio salvato in coda bisogna usare il task ~`project:send-emails`~
(da notare che questo task è totalmente indipendente dal tipo di coda utilizzata e
dalle opzioni scelte):

    $ php symfony project:send-emails

>**NOTE**
>Il task `project:send-emails` accetta come parametri l'`application` e l'`env`.

Quando viene invocato il task `project:send-emails`, le email sono inviate con
lo stesso transport usato per la stategia `realtime`.

>**TIP**
>Notare che il task `project:send-emails` può essere lanciato su qualsiasi computer, non
>necessariamente sulla macchina che ha creato il messaggio. Il tutto funziona
>perché ogni cosa è memorizzata nell'oggetto del messaggio, anche i file in allegato.

-

>**NOTE**
>L'implementazione predefinita della gestione della coda è molto semplice. 
>Invia le email senza gestione degli errori, così come succederebbe nella
>strategia `realtime`. Ovviamente, le classi predefinite di gestione delle
>code possono essere estese per implementare logiche personalizzate e
>gestioni degli errori.

Il task `project:send-emails` accetta due parametri opzionali:

 * `message-limit`: Limita il numero dei messaggi da spedire

 * `time-limit`: Limita il tempo necessario a spedire i messaggi (in secondi).

Entrambi i parametri possono essere usati insieme:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

Il comando precedente bloccherà l'invio dei messaggio quando saranno spediti 
10 messaggi o saranno passati 20 secondi.

Usando la strategia `spool` potrebbe essere necessario inviare un messaggio immediatamente
senza salvarlo il lista di attesa. Questo è possibile usando il metodo speciale
`sendNextImmediately()` della classe mailer:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Nel precedente esempio il `$message` non sarà salvato in lista e sarà spedito immediatamente. 
Questo significa, che il metodo `sendNextImmediately()`, è utilizzato per spedire il solo
messaggio passato.

>**NOTE**
>Il metodo `sendNextImmediately()` non non effetti particolari quando il metodo di spedizione
> non è `spool`.

### La strategia ~`none`~

Questa strategia è utile nell'ambiente di sviluppo per evitare l'invio di email
a veri utenti. I messaggi sono disponibili all'interno della web debug toolbar
(maggiori informazioni nella sezione succesiva riguardante il pannello di
gestione del mailer nella web debug toolbar).

È anche la migliore strategia per gli altri ambienti, dove l'oggetto 
`sfTesterMailer` permette di analizzare il messaggio senza necessariamente
spedirlo (ulteriori informazioni saranno presenti nella sezione relativa ai test).

Il Mail Transport
------------------

I messaggi di posta sono spediti utilizzando un transport. Il transport è
configurato nel file di configurazione `factories.yml` e il valore predefinito
è un server SMTP sulla macchina locale:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer viene distribuito con tre differenti classi di transport:

  * ~`Swift_SmtpTransport`~: Usa un server SMTP per inviare i messaggi.

  * ~`Swift_SendmailTransport`~: Usa `sendmail` per inviare i messaggi.

  * ~`Swift_MailTransport`~: Usa la funzione nativa di PHP `mail()` per inviare i messaggi.

>**TIP**
>La sezione della documentazione ufficiale di swift Mailer sul ["tipo di
>Transport"](http://swiftmailer.org/docs/transport-types) descrive tutto
>quello che c'è da sapere a proposito delle classi transport predefinite
>e dei rispettivi parametri.

L'invio di email da un task
---------------------------

L'invio di una mail da un task è molto simile all'invio di una email da una
azione, in quanto il sistema del task prevede anche un metodo `getMailer()`.

Quando si crea il mailer, il sistema del task si basa sulla configurazione attuale.
Quindi, se si desidera utilizzare la configurazione di una specifica applicazione,
è necessario aggiungere l'opzione `--application` (si veda il capitolo sui task
per maggior informazioni su questo argomento).

Si noti che il task utilizza la stessa configurazione dei controller. Quindi, se
si desidera forzare la consegna quando è usata la strategia `spool`, usare
`sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Debug
-----

Tradizionalmente, il debug delle email è sempre stato un incubo. Con symfony,
invece, è molto semplice grazie alla ~web debug toolbar~.

Direttamente dal browser è possibile, semplicemente e velocemente, vedere come
i messaggi sono stati inviati dall'azione corrente:

![Email all'interno della Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Email all'interno della Web Debug Toolbar")

Dopo aver cliccato sull'icona dell'email, il messaggio spedito sarà visualizzato
nel pannello nel suo formato originale, così come mostrato
nell'immagine qui sotto.

![Email all'interno della Web Debug Toolbar - dettaglio](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Email all'interno della Web Debug Toolbar - dettaglio")

>**NOTE**
>Ogni volta che una email viene spedita, symfony aggiunge un messaggio nel log.

Test
----

Sicuramente, l'integrazione non sarebbe completa senza un modo per testare le
email. Symfony registra, automaticamente, un tester `mailer` (~`sfMailerTester`~)
per testare le email nei test funzionali.

Il metodo ~`hasSent()`~ controlla il numero di email inviate da una azione:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

Il codice precedente controlla che l'URL `/foo` invii solo una email.

Ogni email inviata può essere testata ulteriormente usando i metodi 
~`checkHeader()`~ e ~`checkBody()`~:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Il secondo parametro del metodo `checkHeader()` ed il primo parametro del metodo
`checkBody()` possono essere:

 * una stringa che corrisponda esattamente a quella restituita;

 * un'espressione regolare da confrontare con i risultati;

 * un'espressione regolare negativa (cioè una espressione regolare preceduta dal
   carattere `!`) per confermare che il risultato non corrisponda ai valori controllati.

I test, se non è stato specificato diversamente, controllano solo la prima email
inviata. Se sono state inviate diverse email, allora bisognerà utilizzare il
metodo ~`withMessage()`~ per scegliere su quale email discriminare:

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

Il metodo `withMessage()` accetta un destinatario come primo parametro. Inoltre
accetta un secondo parametro per indicare quali email controllare nel caso lo
stesso destinatario ne abbia ricevute diverse.

Per finire, il metodo ~`debug()`~ mostra il messaggio inviato per individuare
i problemi nel caso di fallimento di un test:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

Messaggi come classi
--------------------

Nell'introduzione di questo capitolo, abbiamo imparato come inviare email da una
azione. Questo è probabilmente il modo più semplice per inviare email in una
applicazione symfony e probabilmente il migliore quando si necessita di inviare
alcuni semplici messaggi.

Ma quando l'applicazione necessita di gestire un gran numero di email differenti,
bisogna utilizzare un approccio diverso.

>**NOTE**
>Come valore aggiuntivo, usare classi per i messaggi di posta elettronica
>significa che, la stessa email, può essere utilizzata in diverse applicazioni;
>per una istanza di frontend o di backend.

Siccome i messaggi sono semplici oggetti PHP, il miglior modo di organizzarli
è creando una classe per ognuno di loro:

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

Inviare un messaggio da una azione, o da ovunque sia necessario, è semplicemente
una questione di instanziare la giusta classe di messaggio:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Naturalmente, aggiungere una classe base dove centralizzare gli header condivisi,
come il `From`, o aggiungere una firma comune a tutte, è utile:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        // header specifici, allegati, ...
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

    Email inviata dal Mio App Bot
    EOF
        ;
        parent::__construct($subject, $body);

        // set all shared headers
        $this->setFrom(array('app@example.com' => 'My App Bot'));
      }
    }

Se un messaggio dipende da qualche modello, si può ovviamente passare questi
ultimi come parametri del costruttore:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($user)
      {
        parent::__construct('Confirmation for '.$user->getName(), 'Body');
      }
    }

Ricette
-------


### Inviare una email con ~Gmail~

Se non si ha a disposizione un server SMTP ma solo un account Gmail, 
è possibile usare quest'ultimo per inviare ed archiviare i messaggi:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   il_tuo_username_su_gmail_qui
        password:   la_tua_password_di_gmail_qui

Configurare le voci `username` e `password` con le credenziali di Gmail per
poterne utilizzare i server.

### Personalizzare l'oggetto Mailer

Se configurare il mailer tramite il file `factories.yml` non è abbastanza,
è necessario ascoltare l'evento ~`mailer.configure`~, per poi personalizzare
l'oggetto mailer.

È possibile connettersi a questo evento tramite la classe `ProjectConfiguration`
così come illustato qui di seguito:

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

        // fare qualcosa col mailer
      }
    }

La seguente sezione mostra un uso utile di questa tecnica.

### Usare i ~Plugin di Swift Mailer~

Per usare i plugin di Swift Mailer, bisogna innanzitutto ascoltare l'evento
`mailer.configure` (come spiegato precedentemente):

    [php]
    public function configureMailer(sfEvent $event)
    {
      $mailer = $event->getSubject();

      $plugin = new Swift_Plugins_ThrottlerPlugin(
        100, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
      );

      $mailer->registerPlugin($plugin);
    }

>**TIP**
>La sezione ["Plugin"](http://swiftmailer.org/docs/plugins) della documentazione
>ufficiale di Swift Mailer descrive tutto quello che è necessario sapere a proposito
>dei plugin predefiniti.

### Personalizzare il comportamento di Spool

L'implementazione predefinita degli spool è molto semplice. Ogni spool prende tutte
le email da una lista d'attesa in un ordine casuale e poi le spedisce.

È possibile configurare lo spool per limitare il tempo impiegato per spedire le email (in secondi),
o per limitare il numero di messaggi da spedire:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

In questa sezione si implementerà un sistema di priorità per le spedizioni. Questo darà le basi 
necessarie per implementare logiche personalizzate.

Per prima cosa, aggiungere una colonna `priority` allo schema:

    [yml]
    # per Propel
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # per Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: clob, notnull: true }
        priority: { type: integer }

Durante la spedizione dell'email, si imposterà l'header di priorità (dove 1
significa massima priorità):

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

Quindi, si sovrascriverà il metodo predefinito `setMessage()` per cambiare la
priorità dell'oggetto `MailMessage`:

    [php]
    // per Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->setPriority($msg->getPriority());

        return parent::setMessage($message);
      }
    }

    // per Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->priority = $msg->getPriority();

        return $this->_set('message', $message);
      }
    }

Bisogna notare che il messaggio è serializzato all'interno della lista, quindi
per recuperare il valore della priorità dovrà essere de-serializzato. Andrà
quindi creato un metodo per ordinare i messaggi in base alle rispettive priorità:

    [php]
    // per Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // per Doctrine
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

L'ultimo passo è quello di definire il metodo di recupero all'interno del file
di configurazione `factories.yml` per cambiare il comportamento predefinito
con il quale i messaggi sono ottenuti dalla lista d'attesa:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

A questo punto, ogni volta che sarà eseguito il task `project:send-emails`, ogni
email verrà spedita in base alla propria priorità.

>**SIDEBAR**
>Personalizzare lo Spool con un qualsiasi criterio
>
>Il precedente esempio usa un header standard dei messaggi, la priorità. Ma 
>se bisogna utilizzare un qualsiasi criterio, o se non si può
>modificare il messaggio inviato, si può anche salvare il criterio come header
>personalizzato, per poi rimuoverlo subito prima di inviare il messaggio.
>
>Per prima cosa si aggiunge l'header personalizzato al messaggio da spedire:
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
>Poi si ottiene il valore dall'header durante la fase di salvataggio in 
>lista d'attesa e si rimuove immediatamente:
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

