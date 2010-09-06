Capitolo 11 - Email
===================

L'invio di email con symfony è un'operazione semplice e potente, grazie all'utilizzo
della libreria [Swift Mailer](http://www.swiftmailer.org/). Sebbene Swift Mailer
faciliti già di suo l'invio di email, symfony fornisce un leggero wrapper su di essa
in modo da rendere l'invio di email ancora più potente e flessibile. In questo capitolo verrà mostrato
tutta la potenza messa a disposizione.

>**NOTE**
>symfony 1.3 contiene la versione 4.1 di Swift Mailer.

Introduzione
------------

La gestione delle email in symfony ruota intorno all'oggetto mailer. Come 
altri oggetti del nocciolo di symfony, il mailer è una factory. Esso è configurato nel
file di configurazione `factories.yml` ed è sempre accessibile attraverso l'istanza del contesto:

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>A differenza delle altre factory, il mailer viene caricato e inizializzato su richiesta. Se
> non venisse utilizzato, non si avranno impatti sulle performance o altri effetti collaterali.

Questo tutotial spiega come Swift Mailer sia integrato all'interno di symfony. Se si volesse
approfondire tutti i particolari della libreria Swfit Mailer, ci si deve riferire alla
[documentazione](http://www.swiftmailer.org/docs) dedicata.

Invio di un'Email dalla Action
-----------------------------

Per ottenere l'istanza del mailer nella action basta semplicemente invocare il metodo scorciatoia
`getMailer()`:

    [php]
    $mailer = $this->getMailer();

### Modalità veloce

L'invio di un'email è semplice utilizzando il metodo ~`sfAction::composeAndSend()`~:

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

Il metodo `composeAndSend()` richiede quattro argomenti:

 * l'indirizzo del mittente (`from`);
 * l'indirizzo del/i destinatario/i (`to`);
 * l'oggetto del messaggio;
 * il corpo del messaggio.

Ogni volta che un metodo richiede un indirizzo email come parametro, è possibile passargli una stringa
o un array:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

Ovviamente è possibile inviare un'email a diverse persone in una volta sola passando un array
di email come secondo argomento del metodo:

    [php]
    $to = array(
      'foo@example.com',
      'bar@example.com',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

    $to = array(
      'foo@example.com' => 'Mr Foò,
      'bar@example.com' => 'Miss Bar',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

### Modalità flessibile

Se si desiderasse maggior flessibilità è possibile utilizzare il metodo ~`sfAction::compose()`~
per creare il messaggio, personalizzarlo nella maniera più appropiata e eventualmente inviarlo
Ciò è utile, per esempio, qualora si avesse bisogno di aggiungere un allegato come mostrato di seguito
~attachment|email attachment~ as shown below:

    [php]
    //creazione dell'oggetto messaggio
    $message = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // invio del messaggio
    $this->getMailer()->send($message);

### Modalità più raffinata

E' possibile anche creare un messaggio direttamente per aver maggior flessibilità:

    [php]
    $message = Swift_Message::newInstance()
      ->setFrom('from@example.com')
      ->setTo('to@example.com')
      ->setSubject('Subject')
      ->setBody('Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    $this->getMailer()->send($message);

>**TIP**
>La sezione ["Creating Messages"](http://swiftmailer.org/docs/messages)
>["Message Headers"](http://swiftmailer.org/docs/headers) della documentazione 
>ufficiale mostra tutto quello che è necessario sapere
>per la creazione dei messaggi.

### Utilizzo della vista di symfony

L'invio delle email dalla action permette di far leva in modo piuttosto semplice sulla potenza
dei partial e component:

    [php]
    $message->setBody($this->getPartial('partial_namè, $arguments));

Configurazione
-------------

Come gli altri oggetti factory, il mailer può essere configurato in
nel file di configurazione `factories.yml'. La configurazione predefinita è la seguente:

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

Quando viene creata una nuova applicazione, il file di configurazione locale `factories.yml`
sovrascrive la configurazione predefinita con dei valore predefiniti in base
agli ambienti `prod`, `env` e `test`:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

Strategia di Invio
---------------------

Una delle più utili caratteristiche dell'integrazione di Swift Mailer con symfony è
la strategia d'invio. Essa permette di indicare a symfony
come inviare i messaggi email ed è configurata tramite il il parametro ~`delivery_strategy`~
del file `factories.yml`. La strategia cambia il comportamento ' 
del metodo ~`send()`|`sfMailer::send()`~ . Sono disponibili quattro diverse strategie predefinite
che dovrebbero soddisfare esigenze comuni:

 * `realtime`:       I messaggi vengono inviati in tempo reale.
 * `single_address`: I messaggi vengono inviati ad un singolo indirizzo.
 * `spool`:          I messaggi vengono collocati in una coda.
 * `none`:           I messaggi vengono semplicemente ignorati.

### Strategia ~`realtime`~

La strategia `realtime` è la strategia predefinita, ed è la modalità più semplice
da configurare in quanto non c'è bisogno di nessuno intervento da parte dello sviluppatore.

I messaggi email vengono inviati attraverso il mezzo di trasporto configurato nella sezione `transport`
del file di configurazione `factories.yml` (vedere la prossima sezione per avere più informazioni
riguardanti la configurazione del mezzo di trasporto delle mail).

### Strategia ~`single_address`~

Con la strategia ~`single_address`~ , tutti i messaggi vengono inviati ad un singolo indirizzo 
configurato tramite il parametro `delivery_address`.

Questa strategia risulta veramente utile in un ambiente di sviluppo per evitare l'invio
di messaggi a utenti reali, permettendo comunque allo sviluppatore di controllare
come la mail appaia effettivamente in un client email.

>**TIP**
>Se si avesse bisogno di verificare i destinatari `to`, `cc`, e `bcc` essi sono
>disponibili come valori dei seguenti header: `X-Swift-To`, `X-Swift-Cc`, e `X-Swift-Bcc`.

I messaggi email vengono inviati con lo stesso mezzo di trasporto utilizzato nella
strategia `realtime`.


### Strategia ~`spool`~

Con la strategia `spool` i messaggi vengono memorizzati in una coda.

Questa è la miglior strategia per un ambiente di produzione, in quanto una richiesta web
non deve attendere che la mail sia inviata.

La class di `spool` viene configurata attraverso il parametro ~`spool_class`~. Symfony propone tre diverse alternative:

 * ~`Swift_FileSpool`~: I messaggi vengono memorizzati su filesystem.

 * ~`Swift_DoctrineSpool`~: I messaggi vengono memorizzati in un modello Doctrine.

 * ~`Swift_PropelSpool`~: I messaggi vengono memorizzati in un modello Propel.

Quando viene istanziata la classe di spool, i valori della voce ~`spool_arguments`~ 
vengono passati al costruttore della classe stessa. Di seguito vengono mostrate le opzioni disponibili:

 * `Swift_FileSpool`:

    * Percorso assoluto della directory nella quale viene memorizzata la coda dei messaggi.

 * `Swift_DoctrineSpool`:

    * Il modello di Doctrine utilizzato per memorizzare i messaggi (`MailMessage` è quello predefinito)

    * Il nome della colonna utilizzata per memorizzare il messaggio (`message` come valore predefinito)

    * Il metodo da chiamare per ottenere il messaggio da inviare (opzionale). 
      Riceve le opzione della coda come argomento.

 * `Swift_PropelSpool`:

    * Il modello Propel da utilizzare per memorizzare il messaggio (`MailMessage` come predefinito)

    * Il nome della colonna da utilizzare per memorizzare  il messaggio (`message` come predefinito)

    * Il metodo da chiamare per ottenere il messaggio da inviare (opzionale). 
      Riceve le opzione della coda come argomento.
    
Di seguito una classica configurazione per Doctrine:

    [yml]
    # schema.yml
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: clob, notnull: true }

-

    [yml]
    # factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Medesima configurazione per Propel:

    [yml]
    # schema.yml
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~

-

    [yml]
    # factories.yml
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class:       Swift_PropelSpool
          spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Per inviare i messaggi memorizzati in una coda è possibile utilizzare il task ~`project:send-emails`~
(da notare che è totalmente indipendente dall'implementazione della coda e dalle opzioni che riceve):

    $ php symfony project:send-emails

>**NOTE**
>Il task `project:send-emails` richiede le opzioni `application` e `env`.

Invocando il task `project:send-emails`, i messaggi email vengono inviati
con lo stesso mezzo di trasporto utilizzato dalla strategia `realtime`.

>**TIP**
>Il task `project:send-emails` può essere invocato su qualsiasi macchina,
>non neccesariamente dalla macchina che ha creato il messaggio. Ciò funziona perchè
>tutto viene memorizzato nell'oggetto messaggio, anche gli eventuali file allegati.

-

>**NOTE**
>L'implementazione delle code fornite sono molto semplici. L'invio di email avviene
>senza la gestione di alcun errore, come se fossero state inviate se si utilizzasse
>la strategia `realtime`. Ovviamente le classi di code predefinite posso essere estese
>per implementare una logica e una gestione degli errori personalizzata.

Il task `project:send-emails` può ricevere due parametri opzionali:

 * `message-limit`: Numero limite dei messaggi da inviare.

 * `time-limit`: Tempo limite utile per l'invio dei messaggi (in secondi).

Entrambe le opzioni possono essere utilizate contemporaneamente:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

Il commando appena mostrato smetterà di inviare messaggi dopo che ne avrà inviati 10
e dopo 20 secondi.

Anche quando viene utilizzata la strategia `spool` potrebbe essere utile
inviare un messaggio immediatamente senza memorzzarlo in una coda. Questo è possibile
utilizzando il metodo speciale del mailer `sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Nell'esempio appena mostrato `$message` non verrà memorizzato in una coda e verrà
inviato immediatamente. Come si può dedurre dal nome, il metodo `sendNextImmediately()`
avrà effetto soltanto sul successivo messaggio da inviare.

>**NOTE**
>Il metodo `sendNextImmediately()` non ha nessun effetto quando
>la strategia d'invio è diversa da `spool`.

### Strategia ~`none`~

Questa strategia è utile in un ambiente di sviluppo in modo da evitare che le 
mail vengano inviate agli utenti reali. I messaggi sono disponibili nella web debug toolbar
(maggiori informazione nella sezione sottostante nella quale viene spiegato il panello mailer della 
web debug toolbar).

E' anche la miglior strategia per l'ambiente di test, dove
l'oggetto `sfTesterMailer` permette di poter effettuare un'introspezione dei messaggi
senza doverli effettivamente inviare (maggiori informazioni nella sezione sottostante dedicata al testing).

Il mezzo di trasporto delle Mail
------------------

I messaggi mail sono inviati da un mezzo di trasporto. Esso può essere configurato
nel file di configurazione `factories.yml`, e i valori predefiniti utilizzano
l'SMTP server della macchina locale:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer fornisce tre diversi mezzi di trasporto:

  * ~`Swift_SmtpTransport`~: Utilizza un SMTP server per l'invio di messaggi.

  * ~`Swift_SendmailTransport`~: Utilizza `sendmail` per l'invio di messaggi.

  * ~`Swift_MailTransport`~: Utilizza la funzione PHP nativa `mail()` per l'invio di messaggi 

>**TIP**
>La sezione ["Transport Types"](http://swiftmailer.org/docs/transport-types)
>della documentazione ufficale di Swift Mailer descrive tutto quello che c'è da sapere
>riguardo alle classi di trasporto e i diversi parametri.

Invio di un Email da un Task
----------------------------

Inviare email da un task è simile all'invio di una mail da una action
in quanto anche il sistema alla base dei task mette a disposizione un metodo `getMailer()`.

Nel momento in cui viene creato il mailer, il sistema dei task si appoggia alla configurazione corrente
Quindi se si volesse utilizzare una configurazione di una pecifica applicazione
si deve specificare l'opzione `--application` (riferirsi al capitolo sui task per maggiori informazioni).

Da notare che il task utilizza la stessa configurazione dei controllori. Quindi se
si volesse forzare l'invio quando viene utilizzata la strategia `spool` si deve utilizzare `sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Debugging
---------

Solitamente eseguire il debug dell'invio delle mail è sempre stato un incubo. Con symfony invece tale 
operazione è molto semplice, grazie alla ~web debug toolbar~.

E' possibile controllare direttamente dal browser in modo rapido e semplice 
quanti messaggi sono stati inviati dalla action corrente:

![Emails in the Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Emails in the Web Debug Toolbar")

Se si clicca sull'icona della mail vengono mostrati i messaggi inviati nella loro forma grezza:

![Emails in the Web Debug Toolbar - details](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Emails in the Web Debug Toolbar - details")

>**NOTE**
>Tutte le volte che un'email viene inviata, symfony aggiunge un messaggio nei log.

Testing
-------

L'integrazione non sarebbe completa senza una modalità di testing dei messaggi email.
Symfony registra in modo predefinito un mailer tester
(~`sfMailerTester`~)  per facilitare il test delle mail nei test funzionali.

Il metodo ~`hasSent()`~ testa il numero di messaggi inviati durante la richiesta corrente:

    [php]
    $browser->
      get('/foò)->
      with('mailer')->
        hasSent(1)
    ;

Il codice appena mostrato controlla che l'URL `/foo` invii soltanto un'email.

Ogni email inviata può essere testata con l'aiuto dei metodi ~`checkHeader()`~ 
e ~`checkBody()`~ :

    [php]
    $browser->
      get('/foò)->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Il secondo argomento di `checkHeader()` e il primo argomento di `checkBody()`
può essere uno dei seguenti:

 * una stringa per contollare un esatta corrispondenza;
 
 * un'espressione regolare per controllarne i valori;

 * un'espressione regolare negativa (un'espressione regolare che inizi con `!`)
   per controllare che il valore non corrisponda.

Come impostazione predefinita i controlli vengono fatti sul primo messaggio inviato. Se venissero inviati
ulteriori messaggi è possibile decidere quale messaggio debba essere testato con 
il metodo ~`withMessage()`~ :

    [php]
    $browser->
      get('/foò)->
      with('mailer')->begin()->
        hasSent(2)->
        withMessage('foo@example.com')->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Il metodo `withMessage()` accetta un destinatario come primo argomento. Inoltre accetta
un secondo argomento per indicare quale messaggio si vuole testare qualora ci fossero diversi
messaggi inviati allo stesso destinatario.

Infine il metodo ~`debug()`~ fornisce un dump dei messaggi inviati per poter analizzare 
eventuali problemi se un test fallisse:

    [php]
    $browser->
      get('/foò)->
      with('mailer')->
      debug()
    ;

Messaggi Email come Classi
-------------------------

Nell'introduzione di questo capitolo è stato mostrato come inviare email
da una action. Questo è probabilmente la maniera più semplice di inviare email in 
un'applicazione symfony e probabilmente la migliore quando si vuole inviare pochi e semplici messaggi.

Ma quando l'applicazione neccesita di poter gestire un gran numero di differenti messaggi email,
si dovrebbe utilizzare una strategia diversa.

>**NOTE**
>Utilizzare classi per i messaggi email significa che la stessa mail
>può essere utilizzata nelle diverse applicazioni; per esempio sia nel frontend sia nel backend.

Dato che i messaggi sono semplici oggetti PHP, l'ovvia modalità per gestire i messaggi 
è creare una classe per ognuno di essi:

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

L'invio di messaggi da una action, o da altre punti dell'applicativo, comporta 
semplicemente l'inizializzazione  della appropiata classe del messaggio

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Ovviamente può risultare conveniente aggiungenre una classe base per centralizzare gli header condivisi come
`From` o aggiungere una firma comune:

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

    Email inviata dal mio Bot
    EOF
        ;
        parent::__construct($subject, $body);

        // impostazione header condivisi
        $this->setFrom(array('app@example.com' => 'My App Bot'));
      }
    }

Qualora un messaggio dipendesse da oggetti del modello, è possibile passarli
come argomenti al costruttore:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($user)
      {
        parent::__construct('Confirmation for '.$user->getName(), 'Body');
      }
    }

Destinatari
-------

### Invio di Email con ~Gmail~

Se non si avesse a disposizione un server SMTP ma si disponesse di un account Gmail, è possibile
impiegare la seguente configurazione per utilizzare i server di Google per l'invio e archiviazione dei messaggi:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   your_gmail_username_goes_here
        password:   your_gmail_password_goes_here

E' sufficiente sostituire `username` e `password` con le proprie credenziale di Gmail e il gioco è fatto.

### Personalizzazione dell'oggetto Mailer

Se non bastasse la configurazione del mailer attraverso il file di configurazione `factories.yml`
è possibile utilizzare e mettersi in ascolto dell'evento  ~`mailer.configure`~ e
personalizzare di conseguenza il mailer.



    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->dispatcher->connect(
          'mailer.configurè,
          array($this, 'configureMailer')
        );
      }

      public function configureMailer(sfEvent $event)
      {
        $mailer = $event->getSubject();

        // do something with the mailer
      }
    }

La sezione seguente mostra come utilizzare in maniere proficua questa tecnica.

### Utilizzo di ~Swift Mailer Plugins~

Per utilizzare i plugin forniti da Swift Mailer, è sufficiente mettersi in ascolto dell'evento `mailer.configure`
(vedere la sezione precedente):

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
>La sezione dei ["Plugin"](http://swiftmailer.org/docs/plugins)
>della documentazione ufficiale di Swift Mailer descrive tutto quello che c'è da sapere
>sui plugin interni forniti dalla libreria.

### Personalizzare il comportamento dello Spool

L'implementazione degli spool fornita è molto semplice. Ogni spool
recupera tutte le email dalla coda in ordine casuale e le invia.

E' possibile configurare uno spool in modo tale da limitare il tempo speso nell'invio delle email (in secondi),
o limitare il numero di messaggi da inviare:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

Nella sezione corrente verrà illustrato come implementare un sistema proritario per la coda.
Verrano fornite tutte le informazioni necessarie per l'implementazione di una logica persanalizzata.

Per prima cosa aggiungere una colonna `priority` allo schema:

    [yml]
    # Propel
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: clob, notnull: true }
        priority: { type: integer }

Durante l'invio di un'email è necessario impostare l'header di prorità (1 significa priorità massima)

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

Dopodichè sovrascrivere il metodo predefinito `setMessage()`  in modo tale da cambiare la priorità
dell'oggetto `MailMessage stesso: 

    [php]
    // Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->setPriority($msg->getPriority());

        parent::setMessage($message);
      }
    }

    // Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->priority = $msg->getPriority();

        $this->_set('messagè, $message);
      }
    }

Da notare che il messaggio viene serializzato dalla coda, deve essere quindi deserializzato
prima di ottener il valore della priorità. Successivamente è necessario creare un metodo
che ordini i messaggi in base alla priorità:

    [php]
    // Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // Doctrine
    class MailMessageTable extends Doctrine_Table
    {
      public function getSpooledMessages()
      {
        return $this->createQuery('m')
          ->orderBy('m.priority')
        ;
      }

      // ...
    }

L'ultimo passo da eseguire consiste nel definire il metodo di recupero nel 
file di configurazione `factories.yml` in modo tale da modificare il comportamento 
predefinito con il quale vengono recuperati i messaggi dalla coda:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

Questi sono gli unici passi da eseguire per ottenere il comportamento voluto.
In questo modo ogni volta che viene invocato il task `project:send-emails`
ogni email verrà inviata in base alla priorità attribuita.

>**SIDEBAR**
>Personalizzare lo Spool con diversi criteri
>
>L'esempio precedente utilizza un header standard del messaggio, la priorità. Ma se si volesse
>utilizzare dei criteri diversi oppure se non si volesse alterare i messaggi inviati,
>è possibile memorizzare il criterio con un header personalizzato e rimuoverlo prima
>che l'email venga inviata.
>
>Per prima cosa aggiungere un header personalizzato al messaggio che deve essere spedito:
>
>     [php]
>     public function executeIndex()
>     {
>       $message = $this->getMailer()
>         ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
>       ;
>     
>       $message->getHeaders()->addTextHeader('X-Queue-Criteria', 'foò);
>     
>       $this->getMailer()->send($message);
>     }
>
>Successivamente recuperare il valore dall'header nel momento che il messaggio
>viene aggiunto alla coda e rimuoverlo immediatamente:
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
>       parent::setMessage($message);
>     }
