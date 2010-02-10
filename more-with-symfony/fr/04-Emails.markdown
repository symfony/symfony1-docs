Les Emails
==========

*Par Fabien Potencier*

Envoyer des ~emails~ avec symfony devient à la fois simple et plus efficace, grâce à l'utilisation de la bibliothèque [Swift Mailer](http://www.swiftmailer.org/). Bien que ~Swift Mailer~ facilite l'envoi des emails, symfony apporte quant à lui une couche supplémentaire afin de rendre l'envoi d'emails encore plus flexible et puissant. Ce chapitre explique comment les développeurs peuvent tirer parti de toute cette puissance.

>**NOTE**
>symfony 1.3 embarque la version 4.1 de Swift Mailer.

Introduction
------------

La gestion des emails dans symfony est centralisée autour d'un objet de gestion d'envoi d'email, le `mailer`. Comme pour la plupart des autres objets qui composent le coeur de symfony, l'objet `mailer` est lui aussi une factory. De ce fait, il est configuré par l'intermédiaire du fichier de configuration `factories.yml`, et toujours exposé à travers l'instance du contexte.

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>Contrairement aux autres factories, le gestionnaire d'envoi d'emails est chargé 
>et initialisé à la demande. Par conséquent, s'il n'est pas utilisé, il n'y aura 
>aucun impact sur les performances.

Ce tutoriel explique comment est intégrée la librairie Swift Mailer dans symfony. Les lecteurs qui souhaitent en savoir davantage sur les détails importants de Swift Mailer sont invités à se référer à la [documentation](http://www.swiftmailer.org/docs) officielle en ligne.

Envoyer des Emails depuis une Action
------------------------------------

Depuis une action, la récupération du gestionnaire d'envoi d'emails a été facilitée grâce à la méthode raccourcie `getMailer()` :

    [php]
    $mailer = $this->getMailer();

### La Méthode Rapide

Envoyer un email est alors aussi simple que d'utiliser la méthode ~`sfAction::composeAndSend()` :

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

La méthode `composeAndSend()` accepte quatre arguments :

 * L'adresse email de l'expéditeur (`from`) ;
 * La / les adresse(s) email du / des destinataire(s) (`to`) ;
 * Le sujet du message ;
 * Le corps du message.

Toutes les méthodes qui accueillent une adresse e-mail en guise de paramètre peuvent en fait accepter aussi bien une chaîne de caractères comme un tableau.

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

Bien sûr, le tableau peut contenir plusieurs adresses email afin d'expédier le message à plusieurs destinataires simultanément.

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

### La Méthode Flexible

Bien qu'elle soit simple et rapide à mettre en place, la première méthode peut s'avérer moins flexible. La méthode `sfAction::compose()` accroît la flexibilité du développeur car elle permet de créer le message, de le personnaliser à volonté et éventuellement de l'envoyer. C'est d'autant plus pratique lorsqu'il s'agit d'ajouter une pièce jointe au message.

Si vous avez besoin de plus de flexibilité, vous pouvez aussi utiliser la 
méthode `sfAction::compose()` pour créer un message, le personnaliser de la 
manière que vous voulez, et éventuellement l'envoyer. C'est, par exemple, très 
pratique lorsque vous avez besoin d'ajouter une pièce jointe (~attachment|email attachment~) au message comme le montre l'exemple ci-dessous.

    [php]
    // create a message object
    $message = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // send the message
    $this->getMailer()->send($message);

### La Méthode Efficace

Une autre méthode consiste à créer l'objet du message à la main directement afin de bénéficier d'encore plus de flexibilité. Le code ci-dessous témoigne de cette flexibilité accrue.

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
>Les sections ["Creating Messages"](http://swiftmailer.org/docs/messages) et
>["Message Headers"](http://swiftmailer.org/docs/headers) de la documentation 
>officielle de Swift Mailer décrivent tout ce dont il faut savoir à propos de la 
>création de messages.

### Coupler l'Envoi d'Emails avec la Vue de symfony

Envoyer vos emails depuis les actions permet de profiter en toute aisance de la puissance des vues partielles et des composants pour assigner un contenu au message.

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

La Configuration
----------------

Le gestionnaire d'envoi d'emails peut être configuré dans le fichier de configuration `factories.yml` de la même manière que toute autre ~`factory`~ de symfony. La configuration par défaut du gestionnaire est la suivante.

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

A la création d'une nouvelle application, le fichier de configuration local `factories.yml` surcharge la configuration par défaut, en spécifiant quelques ajustements spécifiques aux environnements de production (`prod`), de développement (`dev`) et de test (`test`).

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

La Stratégie de Distribution
----------------------------

L'une des principales fonctionnalités utiles de l'intégration de Swift Mailer dans symfony est la stratégie de distribution des emails. La stratégie de distribution permet d'indiquer à symfony de quelle manière le framework doit 
envoyer les emails. Elle est configurée à partir du paramètre de configuration 
`delivery_strategy` du fichier `factories.yml`.

La stratégie change la manière dont la méthode ~`send()`|`sfMailer::send()`~ se comporte. Quatre stratégies de distribution sont disponibles par défaut, ce qui devrait convenir à la majorité des besoins :

 * `realtime` : les messages sont envoyés en temps réel ;
 * `single_address` : les messages sont envoyés à une adresse unique ;
 * `spool` : les messages sont stockés dans une file d'attente ;
 * `none` : les messages sont simplement ignorés.

### La Stratégie ~`realtime`~

La stratégie `realtime` est la stratégie de distribution par défaut car c'est  aussi la plus facile à configurer dans la mesure où il n'y a finalement rien de spécial à faire.

Les emails sont expédiés à l'aide d'un objet de transport configuré dans la section `transport` du fichier de configuration `factories.yml`. La prochaine section donne davantage d'informations à propos de la configuration de l'objet  de transport.

### La Stratégie ~`single_address`~

Avec la stratégie `single_address`, tous les messages sont envoyés à une unique adresse email. Cette stratégie est configurée au paramètre de configuration `delivery_strategy`.

La stratégie `single_address` est particulièrement utile en environnement de développement afin d'éviter d'envoyer des emails aux utilisateurs finaux réels. Le développeur garde néanmoins une grande flexibilité dans la mesure où il peut toujours consulter le rendu du message dans un client mail.

>**TIP**
>Le développeur peut avoir besoin de vérifier les valeurs des destinataires 
>originaux dans les en-têtes `to`, `cc` et `bcc`. Ces valeurs sont disponibles 
>dans les entêtes respectives suivantes : `X-Swift-To`, `X-Swift-Cc` et 
>`X-Swift-Bcc`.

Les emails sont expédiés avec le même transport d'email que celui utilisé pour la stratégie de distribution `realtime`.

### La Stratégie ~`spool`~

Avec la stratégie de `spool`, les messages sont sauvegardés dans une file d'attente. C'est sans aucun doute la meilleure stratégie pour l'environnement de production dans la mesure où les requêtes web n'ont pas à attendre que les emails ont bien été envoyés.

La classe de `spool` est configurée dans le paramètre de configuration ~`spool_class`~ du fichier `factories.yml`, et symfony inclut trois de ces stratégies par défaut :

 * ~`Swift_FileSpool`~ : les messages sont stockés sur le système de fichiers ;
 
 * ~`Swift_DoctrineSpool`~ : les messages sont stockés dans un modèle Doctrine ;

 * ~`Swift_PropelSpool`~ : les messages sont stockés dans un modèle Propel.

Lorsque la classe de spool est instanciée, les valeurs définies dans le paramètre de configuration ~`spool_arguments`~ sont utilisées comme arguments du constructeur. Les options de configuration disponibles pour les classes de file d'attente natives sont listées ci-dessous :

 * `Swift_FileSpool` :

    * Le chemin absolu du répertoire de la file d'attente (les messages sont 
      stockés dans ce répertoire).

 * `Swift_DoctrineSpool` :

    * Le modèle Doctrine à utiliser pour sauvegarder les messages (`MailMessage` 
      par défaut).

    * Le nom de la colonne à utiliser pour le stockage du message (`message` par
      défaut).

    * La méthode à appeler pour retrouver le message à envoyer (optionnel). Elle 
      reçoit les options de la file d'attente comme argument.

 * `Swift_PropelSpool` :

    * Le modèle Propel à utiliser pour sauvegarder les messages (`MailMessage` 
      par défaut).

    * Le nom de la colonne à utiliser pour le stockage du message (`message` par
      défaut).

    * La méthode à appeler pour retrouver le message à envoyer (optionnel). Elle 
      reçoit les options de la file d'attente comme argument.

Le listing ci-dessous décrit une configuration typique du spool Doctrine :

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

Le code ci-après décrit la même configuration pour le spool Propel :

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

Pour envoyer un message sauvegardé dans la file d'attente, il suffit d'utiliser la tâche ~`project:send-emails`~. Il est important de noter que cette commande est complètement indépendante de l'implémentation de la file d'attente, et des options qu'elle accepte.

    $ php symfony project:send-emails

>**NOTE**
>La tâche `project:send-emails` accepte aussi les options `application` et 
>`env`.

Lorsque la tâche `project:send-emails` est invoquée, les emails sont envoyés à l'aide du même objet de transport que celui défini pour la stratégie `realtime`.

>**TIP**
>La tâche `project:send-emails` est exécutable sur n'importe quelle machine, et 
>pas nécessairement sur la machine qui a créé le message. Cela fonctionne en 
>effet parce que tout est sauvegardé dans l'objet du message, y compris les 
>fichiers attachés.

-

>**NOTE**
>Les implémentations des files d'attente par défaut sont particulièrement 
>triviales. Elles envoient les emails sans aucune gestion d'erreur, comme si 
>elles avaient été envoyées avec la stratégie `realtime`. Bien sûr, les classes 
>de files d'attente par défaut peuvent être étendues afin d'implémenter une 
>logique métier et une gestion des erreurs personnalisées.

Il arrive parfois qu'il faille envoyer un message immédiatement sans avoir à le sauvegarder dans la file d'attente, bien que l'application soit configurée avec la stratégie de `spool`. Heureusement, symfony fournit la méthode spéciale `sendNextImmediately()` de l'objet mailer pour satisfaire ce besoin.

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Dans l'exemple précédent, l'objet `$message` ne sera pas sauvegardé dans la file d'attente et sera immédiatement expédié. Comme son nom l'indique, la méthode `sendNextImmediately()` affecte seulement le tout prochain message à être envoyé.

>**NOTE**
>La méthode `sendNextImmediately()` n'a aucun effet particulier lorsque la 
>stratégie de distribution n'est pas définie à la valeur `spool`.

### La Stratégie ~`none`~

La stratégie ~`none`~ est utile en environnement de développement dans la mesure où elle empêche tout email d'être envoyé aux destinataires finaux. Néanmoins, les messages restent disponibles dans la barre de débogage. La section suivante donne davantage d'informations au sujet du panneau de gestion des emails de la barre d'outils.

Cette stratégie est également la meilleure pour l'environnement de test. En effet, le testeur `stTesterMailer` offre la possibilité d'introspecter les messages sans avoir le besoin de les envoyer réellement. Les tests sur les messages envoyés sont décrits dans la section suivante.

Le Transport des Emails
-----------------------

Les emails sont actuellement expédiés à l'aide d'un objet de transport. Le transport est configuré dans le fichier de configuration `factories.yml`, et sa configuration par défaut force une connexion au serveur SMTP de la machine locale :

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer embarque nativement trois classes de transport différentes :

  * ~`Swift_SmtpTransport`~ utilise un serveur SMTP pour envoyer les messages ;

  * ~`Swift_SendmailTransport`~ utilise le binaire `sendmail` pour envoyer les 
    messages ;

  * ~`Swift_MailTransport`~ utilise la fonction native `mail()` de PHP pour 
    envoyer les emails.

>**TIP**
>La section ["Transport Types"](http://swiftmailer.org/docs/transport-types) de 
>la documentation officielle de Swift Mailer décrit tout ce dont il faut savoir 
>à propos des classes de transport natives et leurs différents paramètres.

Envoyer un Email depuis une Tâche
---------------------------------

Envoyer un email depuis une tâche est pratiquement similaire à envoyer un email depuis une action dans la mesure où le mécanisme des tâches expose une méthode `getMailer()`.

Le système de tâches dépend de la configuration courante au moment de la création de l'objet `mailer`. Par conséquent, si la tâche a besoin de la configuration d'une application spécifique, alors la commande doit obligatoirement accepter l'option `--application`. Le chapitre sur les tâches donne davantage d'explications à ce sujet.

Il est important de remarquer que la tâche utilise la même configuration que les contrôleurs. Par conséquent, pour forcer la distribution du message, bien que ce soit la stratégie de `spool` qui soit utilisée, alors il suffit d'utiliser la méthode `sendNextImmediately()` :

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Le Débogage
-----------

Depuis toujours, le débogage des emails a toujours été un véritable cauchemar. Avec symfony, c'est beaucoup plus simple grâce au nouveau panneau email de la ~web debug toolbar~. Avec tout le confort du navigateur web, il est désormais possible de savoir facilement et rapidement combien de messages ont été expédiés dans l'action courante.

![Les emails dans la Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Les emails dans la Web Debug Toolbar")

Un clic sur l'icône des emails donne accès à tous les messages envoyés, affichés sous forme brute comme l'atteste la capture d'écran ci-dessous.

![Les emails dans la Web Debug Toolbar - details](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Les emails dans la Web Debug Toolbar - details")

>**NOTE**
>Chaque fois qu'un email est envoyé, symfony ajoute au passage un message dans 
>le log.

Tester les Emails
-----------------

Bien sûr, l'intégration des emails n'aurait pas été aussi complète sans un moyen 
de tester les messages. Par défaut, symfony enregistre un nouveau testeur `mailer` (~`sfMailerTester`~) afin de faciliter les tests fonctionnels sur les emails envoyés. La méthode ~`hasSent()`~, par exemple, teste le nombre de messages envoyés au cours de la requête courante.

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

Le code précédent vérifie que l'url `/foo` envoie seulement un email. De plus, les spécificités de chaque email envoyé peuvent être testées à l'aide des méthodes ~`checkHeader()`~ et ~`checkBody()`~.

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Le second argument de la méthode `checkHeader()` et le premier paramètre de 
`checkBody()` peuvent être l'une des valeurs suivantes.

 * une chaîne pour vérifier une correspondance exacte ;
 
 * une expression régulière pour contrôler la correspondance de la valeur avec
   elle ;

 * une expression régulière négative (une expression régulière qui débute par un 
   `!`) pour vérifier que la valeur ne correspond pas.

Par défaut, les vérifications sont réalisées sur le premier message envoyé. Si 
plusieurs messages ont été expédiés, la méthode ~`withMessage()`~ offre la possibilité de choisir sur quel message appliquer les tests.

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

La méthode `withMessage()` accepte une adresse email de destinataire en guise de premier argument. Elle accueille également un second paramètre pour indiquer quel message tester si plusieurs emails ont été adressés à la même personne. Enfin, la méthode ~`debug()`~ expose les messages envoyés afin de déceler les 
problèmes lorsqu'un test échoue.

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

Les Messages Electroniques sous forme de Classes
------------------------------------------------

L'introduction de ce chapitre a montré comment envoyer des emails depuis une action. C'est sans doute la manière la plus simple pour expédier des messages dans une application symfony, et probablement la meilleure lorsqu'il s'agit  seulement d'envoyer quelques emails simples.

Néanmoins, plus l'application a besoin de gérer un nombre important de messages, et plus le risque d'adopter une stratégie différente augmente. Comme tous les messages sont des objets PHP purs, la manière évidente d'organiser les messages consiste à créer une classe pour chacun d'eux.

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

Envoyer un message depuis une action, ou bien depuis n'importe où dans ce cas est aussi simple que d'instancier la classe du message correspondant.

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Bien sûr, il est plus pratique d'ajouter une classe de base pour centraliser les 
en-têtes partagés tels que l'en-tête `From`, ou bien pour inclure une signature 
commune.

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

Si un message dépend de certains objets du modèle, ce dernier peut alors bien entendu être transmis comme paramètre du constructeur.

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($user)
      {
        parent::__construct('Confirmation for '.$user->getName(), 'Body');
      }
    }

Quelques Recettes
-----------------

### Envoyer des Emails avec ~Gmail~

Les lecteurs qui ne possèdent pas de serveur SMTP mais qui disposent d'un compte Gmail peuvent s'appuyer sur la configuration suivante afin d'utiliser les 
serveurs de Google comme moyen d'expédition et d'archivage des messages.

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   your_gmail_username_goes_here
        password:   your_gmail_password_goes_here

Remplacer les valeurs des paramètres `username` et `password` par celles du compte Gmail adéquat suffisent à configurer l'objet mailer.

### Personnaliser l'Objet Mailer

Si configurer le mailer par le fichier `factories.yml` n'est pas suffisant, l'évènement ~`mailer.configure`~ peut alors être écouté afin de personnaliser davantage le mailer. Pour ce faire, il suffit de se connecter à l'évènement depuis la classe de configuration `ProjectConfiguration` comme le montre l'exemple ci-dessous.

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

La section suivante illustre un usage avancé et pratique de cette technique.

### Utiliser des ~Plugins Swift Mailer~

L'utilisation des plugins de Swift Mailer s'effectue en écoutant l'évènement `mailer.configure` (voir la section ci-dessus).

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
>La section ["Plugins"](http://swiftmailer.org/docs/plugins) de la documentation 
>officielle de Swift Mailer décrit ce qu'il faut avoir à propos des plugins 
>natifs.

### Personnaliser le Comportement de Spool

L'implémentation native des spools est particulièrement simple. Chaque spool récupère tous les emails depuis une file d'attente en ordre aléatoire, avant de les envoyer un par un.

Dans cette section, il s'agit d'apprendre comment implémenter un système de priorité pour la file d'attente afin de donner toutes les informations nécessaires à l'implémentation d'une logique personnalisée. Tout d'abord, il convient d'ajouter une nouvelle colonne `priority` au modèle de données existant.

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

Lorsqu'un email est envoyé, l'en-tête de priorité de celui-ci doit être fixé. La 
valeur `1` représente la priorité la plus élevée.

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

Ensuite, la méthode `setMessage()` par défaut doit être surchargée afin de  modifier la priorité de l'objet `MailMessage` lui-même.

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

Dans cet exemple, le message est linéarisé par la file d'attente. Par conséquent, il doit d'abord être délinéarisé afin d'être capable de récupérer la valeur de la priorité. Il ne reste maintenant plus qu'à ajouter une méthode qui ordonne les messages par priorité.

    [php]
    // for Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages()
      {
        $c = new Criteria();
        $c->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($c);
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
        ;
      }

      // ...
    }

La dernière étape consiste à définir la méthode de récupération des messages dans le fichier de configuration `factories.yml` afin de changer la manière dont les messages sont obtenus par défaut depuis la file d'attente.

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

C'est tout ce qu'il y'a à faire. Maintenant, chaque fois que la tâche `project:send-emails` sera exécutée, chaque email sera expédié en fonction de sa priorité.

>**SIDEBAR**
>Personnaliser le Spool avec un Critère
>
>L'exemple précédent utilise un en-tête standard de message : la priorité. En 
>revanche, si l'on souhaite utiliser n'importe quel critère ou bien ne pas 
>altérer le message envoyé, il convient de stocker ce critère comme un en-tête 
>personnalisé. Il ne restera plus qu'à le retirer juste avant d'envoyer l'email.
>
>Il suffit tout d'abord d'ajouter un en-tête personnalisé au message à envoyer.
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
>Enfin, il ne reste plus qu'à récupérer la valeur de cet en-tête au moment de 
>stocker le message dans la file d'attente et supprimer le message 
>immédiatement.
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